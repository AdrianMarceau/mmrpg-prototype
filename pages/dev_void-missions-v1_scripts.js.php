(function(){

    // Predefine some object arrays to hold our information
    gameSettings.customIndex.contentIndex = {};
    var mmrpgIndex = gameSettings.customIndex.contentIndex;
    var mmrpgQueue = {};
    var mmrpgMission = {};

    // Add the void mission data to the global object
    mmrpgIndex.types = <?= json_encode($mmrpg_index_types) ?>;
    mmrpgIndex.players = <?= json_encode($mmrpg_index_players) ?>;
    mmrpgIndex.robots = <?= json_encode($mmrpg_index_robots) ?>;
    mmrpgIndex.abilities = <?= json_encode($mmrpg_index_abilities) ?>;
    mmrpgIndex.items = <?= json_encode($mmrpg_index_items) ?>;
    mmrpgIndex.fields = <?= json_encode($mmrpg_index_fields) ?>;
    console.log('mmrpgIndex:', typeof mmrpgIndex, mmrpgIndex);

    // Check to see if the Void Recipe calculator is available
    var $voidRecipeWizard = $('#void-recipe');
    if ($voidRecipeWizard.length > 0){
        (function(){

            //console.log('voidRecipeWizard:', $voidRecipeWizard);

            // Create a VOID RECIPE WIZARD so we can easily add/remove and recalculate on-the-stop
            var voidRecipeWizard = {
                init: function($container){
                    console.log('%c' + 'voidRecipeWizard.init()', 'color: magenta;');
                    //console.log('-> w/ $container:', typeof $container, $container.length, $container);
                    const _self = this;
                    _self.name = 'voidRecipeWizard';
                    _self.version = '1.0.0';
                    _self.maxItems = 10;
                    _self.maxTargets = 8;
                    _self.minQuantaPerClass = {'mecha': 25, 'master': 50, 'boss': 500};
                    _self.voidPowersRequired = ['delta', 'spread', 'quanta', 'level', 'forte'];
                    _self.isReady = false;
                    _self.reset(false);
                    _self.setup($container);
                    _self.calculatePowers();
                    _self.generateMission();
                    _self.refreshUI();
                    _self.isReady = true;
                    console.log('voidRecipeWizard is ' + ('%c' + 'ready'), 'color: lime;');
                    console.log('=> voidRecipeWizard:', _self);
                    // end of voidRecipeWizard.init()
                    },
                reset: function(refresh){
                    console.log('%c' + 'voidRecipeWizard.reset()', 'color: magenta;');
                    if (typeof refresh === 'undefined'){ refresh = true; }
                    const _self = this;
                    _self.items = {};
                    _self.powers = {};
                    _self.mission = {};
                    _self.history = [];
                    if (!refresh){ return; }
                    _self.calculatePowers();
                    _self.generateMission();
                    _self.refreshUI();
                    _self.refreshHash();
                    // end of voidRecipeWizard.reset()
                    },
                setup: function($container){
                    console.log('%c' + 'voidRecipeWizard.setup()', 'color: magenta;');
                    //console.log('-> w/ $container:', typeof $container, $container.length, $container);

                    // Backup a reference to the parent object
                    const _self = this;

                    // Predefine some parent variables for the class
                    _self.xrefs = {};
                    _self.items = {};
                    _self.powers = {};
                    _self.mission = {};
                    _self.history = [];
                    _self.indexes = {};

                    // Pre-define a list of item tokens we can use later
                    const mmrpgIndexItems = mmrpgIndex.items;
                    var mmrpgItemTokens = Object.keys(mmrpgIndexItems);
                    _self.indexes.itemTokens = mmrpgItemTokens;
                    //console.log('mmrpgItemTokens:', mmrpgItemTokens);

                    // Pre-define a list of stat tokens we can use later
                    var mmrpgStatTokens = ['energy', 'weapons', 'attack', 'defense', 'speed'];
                    _self.indexes.statTokens = mmrpgStatTokens;
                    //console.log('mmrpgStatTokens:', mmrpgStatTokens);

                    // Pre-collect a list of type tokens we can use later
                    const mmrpgIndexTypes = mmrpgIndex.types;
                    var mmrpgTypeTokens = Object.keys(mmrpgIndexTypes);
                    mmrpgTypeTokens = mmrpgTypeTokens.filter(function(token){
                        var info = mmrpgIndexTypes[token];
                        if (token === 'none'){ return true; }
                        else if (info.type_class === 'normal'){ return true; }
                        return false;
                        });
                    _self.indexes.typeTokens = mmrpgTypeTokens;
                    //console.log('mmrpgTypeTokens:', mmrpgTypeTokens);

                    // Pre-collect a list of robot tokens that we can use later
                    const mmrpgIndexRobots = mmrpgIndex.robots;
                    var mmrpgRobotTokens = Object.keys(mmrpgIndexRobots);
                    mmrpgRobotTokens = mmrpgRobotTokens.filter(function(token){
                        var info = mmrpgIndexRobots[token];
                        //console.log('checking info for ', token, ' | info:', info);
                        if (!info.robot_flag_published){ return false; }
                        else if (!info.robot_flag_complete){ return false; }
                        else if (info.robot_flag_hidden){ return false; }
                        else if (info.robot_class === 'system'){ return false; }
                        return true;
                        });
                    _self.indexes.robotTokens = mmrpgRobotTokens;
                    //console.log('mmrpgRobotTokens:', mmrpgRobotTokens);

                    // Create sub-lists of robot tokens for each class for later
                    var filterToClass = function(tokens, className){
                        return tokens.filter(function(token){
                            var info = mmrpgIndexRobots[token];
                            if (info.robot_class === className){ return true; }
                            return false;
                            });
                        };
                    var mmrpgRobotMechaTokens = filterToClass(mmrpgRobotTokens, 'mecha');
                    var mmrpgRobotMasterTokens = filterToClass(mmrpgRobotTokens, 'master');
                    var mmrpgRobotBossTokens = filterToClass(mmrpgRobotTokens, 'boss');
                    _self.indexes.robotMechaTokens = mmrpgRobotMechaTokens;
                    _self.indexes.robotMasterTokens = mmrpgRobotMasterTokens;
                    _self.indexes.robotBossTokens = mmrpgRobotBossTokens;
                    //console.log('mmrpgRobotMechaTokens:', mmrpgRobotMechaTokens);
                    //console.log('mmrpgRobotMasterTokens:', mmrpgRobotMasterTokens);
                    //console.log('mmrpgRobotBossTokens:', mmrpgRobotBossTokens);

                    // Collect references to key and parent elements on the page
                    var $parentDiv = $container;
                    var $missionTargets = $('.creation .target-list', $parentDiv);
                    var $missionDetails = $('.creation .mission-details', $parentDiv);
                    var $battleField = $('.creation .battle-field', $parentDiv);
                    var $itemsPalette = $('.palette .item-list', $parentDiv);
                    var $itemsSelected = $('.selection .item-list', $parentDiv);
                    var $resetButton = $('.selection .button.reset', $parentDiv);
                    var $codeButton = $('.selection .button.code', $parentDiv);

                    // Save the references to the object for later use
                    var xrefs = _self.xrefs;
                    xrefs.parentDiv = $parentDiv;
                    xrefs.missionTargets = $missionTargets;
                    xrefs.missionDetails = $missionDetails;
                    xrefs.battleField = $battleField;
                    xrefs.itemsPalette = $itemsPalette;
                    xrefs.itemsSelected = $itemsSelected;
                    xrefs.resetButton = $resetButton;
                    xrefs.codeButton = $codeButton;
                    //console.log('xrefs:', xrefs);

                    // Backup every item's base quantity so we can do dynamic calulations in realt-time
                    $('.item[data-quantity]:not([data-base-quantity])', $parentDiv).each(function(){
                        var $item = $(this);
                        var quantity = parseInt($item.attr('data-quantity'));
                        $item.attr('data-base-quantity', quantity);
                        });

                    // Bind ADD ITEM click events to the palette area's item list buttons
                    $('.item[data-token]', $itemsPalette).live('click', function(e){
                        console.log('palette button clicked! \n-> add-item:', $(this).attr('data-token'));
                        e.preventDefault();
                        //e.stopPropagation();
                        var $item = $(this);
                        var itemToken = $item.attr('data-token');
                        var itemGroup = $item.attr('data-group');
                        var itemQuantity = parseInt($item.attr('data-quantity'));
                        var itemIndex = parseInt($item.attr('data-key'));
                        var itemInfo = {token: itemToken, group: itemGroup, quantity: itemQuantity, index: itemIndex};
                        //console.log('item clicked:', $item);
                        //console.log('item details:', itemInfo);
                        if (itemQuantity <= 0){ return; }
                        _self.addItem({token: itemToken, quantity: 1});
                        });

                    // Bind REMOVE ITEM click events to the selection area's item list buttons
                    $('.item[data-token]', $itemsSelected).live('click', function(e){
                        console.log('section button clicked! \n-> remove-item:', $(this).attr('data-token'));
                        e.preventDefault();
                        var $item = $(this);
                        var itemToken = $item.attr('data-token');
                        var itemGroup = $item.attr('data-group');
                        var itemQuantity = parseInt($item.attr('data-quantity'));
                        var itemIndex = parseInt($item.attr('data-key'));
                        var itemInfo = {token: itemToken, group: itemGroup, quantity: itemQuantity, index: itemIndex};
                        var numItems = Object.keys(_self.items).length;
                        //console.log('item clicked:', $item);
                        //console.log('item details:', itemInfo);
                        _self.removeItem({token: itemToken, quantity: 1});
                        if (!numItems){ _self.reset(); }
                        });

                    // Bind RESET ITEMS click events to the selection area's reset button
                    $resetButton.live('click', function(e){
                        console.log('reset button clicked! \n-> reset-items');
                        e.preventDefault();
                        _self.reset();
                        });

                    // Bind ITEM MIX ENTRY click events to the selection area's code button
                    $codeButton.live('click', function(e){
                        console.log('code button clicked! \n-> parse-item-mix');
                        e.preventDefault();
                        var thisMixString = '';
                        // If there's already items, return a mix string to optionally copy/paste
                        if (Object.keys(_self.items).length){ thisMixString = _self.getMixString(); }
                        var rawMix = prompt('Please enter an item mix string:', thisMixString);
                        if (!rawMix){ return; }
                        if (!_self.parseItemMix(rawMix)){ return; }
                        return _self.refreshHash();
                        });

                    // Bind SELECT STEP click events to the group wrappers themselves
                    $('.wrapper[data-step]', $itemsPalette).live('click', function(e){
                        //console.log('step wrapper clicked! \n-> select-step:', $(this).attr('data-step'));
                        e.preventDefault();
                        var $wrapper = $(this);
                        var $siblings = $wrapper.siblings('.wrapper[data-step]');
                        var stepNum = parseInt($wrapper.attr('data-step'));
                        var stepTotal = $siblings.length + $wrapper.length;
                        var stepLayer = 1;
                        $itemsPalette.attr('data-step', stepNum);
                        $siblings.removeClass('active').attr('data-layer', 0);
                        $wrapper.addClass('active').attr('data-layer', stepLayer++);
                        if (stepNum > 1){
                            var prevNum = (stepNum - 1);
                            for (var num = prevNum; num >= 1; num--){
                                $siblings.filter('[data-step="'+num+'"]').attr('data-layer', stepLayer++);
                                }
                            }
                        if (stepNum < stepTotal){
                            var nextNum = (stepNum + 1);
                            for (var num = nextNum; num <= stepTotal; num++){
                                $siblings.filter('[data-step="'+num+'"]').attr('data-layer', stepLayer++);
                                }
                            }
                        });

                    // TEMP TEMP TEMP
                    // DEBUG DEBUG DEBUG
                    // Make it so clicking the titlebar prints the current void powers to the console
                    $('> .title', $parentDiv).live('click', function(){
                        console.log('%c' + 'VOID POWERS:', 'background-color: #242131; color: #fff; font-weight: bold;');
                        //console.log('_self.powers =', _self.powers);
                        var powerDebug = '';
                        var powerKeys = Object.keys(_self.powers);
                        for (var i = 0; i < powerKeys.length; i++){
                            var powerKey = powerKeys[i];
                            var powerValue = _self.powers[powerKey];
                            powerDebug += '-> ' + powerKey + ': ' + powerValue + '\n';
                            }
                        console.log('%c' + powerDebug, 'background-color: #242131; color: #fff; font-weight: bold;');
                        console.log('via `_self.powers`:', _self.powers);
                        });
                    // DEBUG DEBUG DEBUG
                    // TEMP TEMP TEMP

                    // Check to see if there is already a recipe in the URL hash
                    window.addEventListener('load', () => {
                        if (_self.hashUpdatedByApp){ return; }
                        console.log('%c' + 'window.load() triggered!', 'color: orange;');
                        const params = _self.getHashParams();
                        if (!Object.keys(params).length){ return; }
                        if (!params.mix || !params.mix.length){ return; }
                        //console.log('-> OnLoad || Mix parameters found:', params.mix);
                        _self.parseItemMix(params.mix);
                        });
                    window.addEventListener('hashchange', () => {
                        if (_self.hashUpdatedByApp){ return; }
                        console.log('%c' + 'window.hashchange() triggered!', 'color: orange;');
                        const params = _self.getHashParams();
                        if (!Object.keys(params).length){ return; }
                        if (!params.mix || !params.mix.length){ return; }
                        //console.log('OnHashChange || Mix parameters found:', params.mix);
                        _self.parseItemMix(params.mix);
                        });

                    // end of voidRecipeWizard.setup()
                    },
                addItem: function(item, refresh){
                    console.log('%c' + 'voidRecipeWizard.addItem() w/ ' + item.token, 'color: magenta;');
                    //console.log('-> w/ item:', item);
                    const _self = this;
                    var token = item.token;
                    var quantity = item.quantity || 1;
                    var existing = Object.keys(_self.items).length;
                    var exists = Object.keys(_self.items).indexOf(token) >= 0;
                    if (!exists && existing >= _self.maxItems){ return; }
                    if (!exists){ _self.items[token] = 0; }
                    _self.items[token] += quantity;
                    _self.history.push({ token: token, action: 'add', quantity: quantity });
                    refresh = (typeof refresh === 'undefined') ? true : refresh;
                    if (!refresh){ return; }
                    _self.calculatePowers();
                    _self.generateMission();
                    _self.refreshUI();
                    _self.refreshHash();
                    // end of voidRecipeWizard.addItem()
                    },
                removeItem: function(item, refresh){
                    console.log('%c' + 'voidRecipeWizard.removeItem() w/ ' + item.token, 'color: magenta;');
                    //console.log('-> w/ item:', item);
                    const _self = this;
                    var token = item.token;
                    var quantity = item.quantity || 1;
                    var exists = Object.keys(_self.items).indexOf(token) >= 0;
                    if (!exists){ return; }
                    _self.items[token] -= quantity;
                    if (_self.items[token] <= 0){ delete _self.items[token]; }
                    _self.history.push({ token: token, action: 'remove', quantity: quantity });
                    refresh = (typeof refresh === 'undefined') ? true : refresh;
                    if (!refresh){ return; }
                    _self.calculatePowers();
                    _self.generateMission();
                    _self.refreshUI();
                    _self.refreshHash();
                    // end of voidRecipeWizard.removeItem()
                    },
                parseItem: function(item, quantity, powers){
                    console.log('%c' + 'voidRecipeWizard.parseItem() w/ ' + item.token + ' x' + quantity, 'color: magenta;');
                    //console.log('-> w/ item:', item, 'quantity:', quantity, 'powers:', powers);

                    // Backup a reference to the parent object
                    const _self = this;

                    // Collect the item token and then also break it apart for reference
                    var itemToken = item.token;
                    var itemTokens = itemToken.split('-');
                    var itemPrefix = itemTokens[0] || '';
                    var itemSuffix = itemTokens[1] || '';
                    var itemIsSmall = itemPrefix === 'small';
                    var itemIsLarge = itemPrefix === 'large';
                    var itemIsHyper = itemPrefix === 'hyper';
                    var itemIsEnergy = itemPrefix === 'energy';
                    var itemIsWeapons = itemPrefix === 'weapons';
                    var itemIsAttack = itemPrefix === 'attack';
                    var itemIsDefense = itemPrefix === 'defense';
                    var itemIsSpeed = itemPrefix === 'speed';
                    var itemIsSuper = itemPrefix === 'super';
                    var itemIsScrew = itemSuffix === 'screw';
                    var itemIsShard = itemSuffix === 'shard';
                    var itemIsCore = itemSuffix === 'core';
                    var itemIsPellet = itemSuffix === 'pellet';
                    var itemIsCapsule = itemSuffix === 'capsule';
                    var itemIsTank = itemSuffix === 'tank';
                    var itemIsUpgrade = itemSuffix === 'upgrade';
                    var itemIsBooster = itemSuffix === 'booster';
                    var itemIsDiverter = itemSuffix === 'diverter';
                    var itemIsModule = itemSuffix === 'module';
                    var itemIsCircuit = itemSuffix === 'circuit';
                    var itemIsRotator = itemSuffix === 'rotator';
                    if (itemToken === 'mecha-whistle'){ itemIsRotator = true; }
                    if (itemToken === 'extra-life'){ itemIsRotator = true; }
                    if (itemToken === 'yashichi'){ itemIsRotator = true; }
                    if (itemToken === 'field-booster'){ itemIsRotator = true; }

                    // Increase the delta by one, always, for each item added
                    powers.incPower('delta', 1 * quantity);

                    // Check to see which group the item belongs to and then parse its values

                    // -- UNDEFINED SOMEHOW -----
                    if (itemToken === ''){
                        //return;
                        }
                    // -- ELEMENTAL CORES w/ +SPREAD [+ TYPES]
                    else if (itemIsCore){
                        var typeToken = itemPrefix;
                        var spreadValue = 1.0, typeValue = 5.0;
                        powers.incPower('spread', spreadValue * quantity);
                        powers.incPower(typeToken, typeValue * quantity);
                        }
                    // -- CYBER SCREWS w/ +QUANTA [+ TIERS]
                    else if (itemIsScrew){
                        var quantaValue = 0, tierToken = '';
                        if (itemIsSmall){ quantaValue = 5.0, tierToken = 'mecha'; }
                        else if (itemIsLarge){ quantaValue = 10.0, tierToken = 'master'; }
                        else if (itemIsHyper){ quantaValue = 100.0, tierToken = 'boss'; }
                        powers.incPower('quanta', quantaValue * quantity);
                        //powers.incPower('quanta_'+tierToken, 1 * quantity);
                        }
                    // -- PELLETS & CAPSULES w/ +STATS
                    else if (itemIsPellet || itemIsCapsule){
                        var statToken = itemPrefix;
                        if (!itemIsSuper){
                            var statValue = (itemIsPellet ? 2.0 : 0) + (itemIsCapsule ? 5.0 : 0);
                            powers.incPower(statToken, statValue * quantity);
                            } else {
                            var statTokens = !itemIsSuper ? [statToken] : ['attack', 'defense', 'speed'];
                            for (var j = 0; j < statTokens.length; j++){
                                var subStatToken = statTokens[j];
                                var subStatValue = (itemIsPellet ? 1.0 : 0) + (itemIsCapsule ? 2.5 : 0);
                                powers.incPower(subStatToken, subStatValue * quantity);
                                }
                            }
                        }
                    // -- TANKS & UPGRADES & MYTHICS w/ LEVEL + FORTE [+ ~STATS]
                    else if (itemIsTank || itemIsUpgrade){
                        var boostKind = itemIsEnergy ? 'level' : 'forte';
                        var boostPower = (itemIsTank ? 10 : 0) + (itemIsUpgrade ? 100 : 0);
                        powers.incPower(statToken, statPower * quantity);
                        powers.incPower(boostKind, boostPower * quantity);
                        }
                    // -- BOOSTERS & DIVERTERS w/ STATS [+ STAT-MODS]
                    else if (itemIsBooster && itemPrefix !== 'field'){
                        // otherwise, if normal booster, it simply adjusts stats
                        var boostKind = itemPrefix;
                        var boostPower = 6;
                        powers.incPower(boostKind, boostPower * quantity);
                        }
                    else if (itemIsDiverter){
                        var divertOrder = [], divertValues = [10, 5, 5];
                        if (itemIsAttack){ divertOrder = ['attack', 'defense', 'speed']; }
                        else if (itemIsDefense){ divertOrder = ['defense', 'attack', 'speed']; }
                        else if (itemIsSpeed){ divertOrder = ['speed', 'attack', 'defense']; }
                        powers.decPower(divertOrder[0], divertValues[0] * quantity);
                        powers.incPower(divertOrder[1], divertValues[1] * quantity);
                        powers.incPower(divertOrder[2], divertValues[2] * quantity);
                        }
                    // ELEMENTAL CIRCUITS w/ TYPES [+ TYPE-MODS]
                    else if (itemIsCircuit){
                        var opposingTypes = [], opposingValues = [10, 10];
                        if (itemPrefix === 'battery'){ opposingTypes = ['electric', 'nature']; }
                        else if (itemPrefix === 'sponge'){ opposingTypes = ['water', 'electric']; }
                        else if (itemPrefix === 'forge'){ opposingTypes = ['flame', 'water']; }
                        else if (itemPrefix === 'sapling'){ opposingTypes = ['nature', 'flame']; }
                        else if (itemPrefix === 'chrono'){ opposingTypes = ['time', 'space']; }
                        else if (itemPrefix === 'cosmo'){ opposingTypes = ['space', 'time']; }
                        powers.incPower(opposingTypes[0], opposingValues[0] * quantity);
                        powers.decPower(opposingTypes[1], opposingValues[1] * quantity);
                        }
                    // -- MODULE ITEMS w/ SPECIAL EFFECTS
                    else if (itemIsModule){
                        if (itemPrefix === 'charge'){
                            var quanta = powers.getPower('quanta') * 1.00;
                            powers.incPower('quanta', quanta * quantity);
                            }
                        else if (itemPrefix === 'target'){
                            var spread = 1.00;
                            powers.decPower('spread', spread * quantity);
                            }
                        else if (itemPrefix === 'spreader'){
                            var spread = 1.00;
                            powers.incPower('spread', spread * quantity);
                            }
                        else if (itemPrefix === 'growth'){
                            var effort = 1;
                            powers.incPower('effort', effort * quantity);
                            }
                        else if (itemPrefix === 'fortune'){
                            var reward = 1;
                            powers.incPower('reward', reward * quantity);
                            }
                        else if (itemPrefix === 'guard'){
                            powers.flags.guard = true;
                            }
                        else if (itemPrefix === 'reverse'){
                            powers.flags.reverse = true;
                            }
                        else if (itemPrefix === 'xtreme'){
                            powers.flags.extreme = true;
                            }
                        }
                    // -- MISC ROTATOR (SHIFT) ITEMS
                    else if (itemIsRotator){
                        if (itemToken === 'mecha-whistle'){
                            var shiftPower = quantity * 1;
                            powers.incPower('xmecha', shiftPower);
                            }
                        else if (itemToken === 'extra-life'){
                            var shiftPower = quantity * 1;
                            powers.incPower('xmaster', shiftPower);
                            }
                        else if (itemToken === 'yashichi'){
                            var shiftPower = quantity * 1;
                            powers.incPower('xboss', shiftPower);
                            }
                        else if (itemToken === 'field-booster'){
                            var shiftPower = quantity * 1;
                            powers.incPower('xfield', shiftPower);
                            }
                        /*
                        else if (itemPrefix === 'field'){
                            // field boost is special and also boosts shift power
                            var fieldPower = Math.floor(quantity / 10); //quantity > 0 ? (Math.floor(quantity / 10) + 1) : 0;
                            var shiftPower = quantity * 1;
                            powers.incPower('field', fieldPower);
                            powers.incPower('shift', shiftPower);
                            }
                            */
                        }

                    // end of voidRecipeWizard.parseItem()
                    },
                parseItemMix: function(mix){
                    console.log('%c' + 'voidRecipeWizard.parseItemMix() w/ ' + mix, 'color: magenta;');
                    if (typeof mix !== 'string'){ console.warn('-> mix is not a string!'); return; }
                    else if (!mix.length){ console.warn('-> mix is an empty string!'); return; }
                    else if (mix === '-'){ return; }

                    // Backup a reference to the parent object
                    const _self = this;

                    // Collect valid item tokens to prevent bugs
                    const mmrpgItemTokens = _self.indexes.itemTokens;

                    // Predefine some variables to hold the mix and then break it apart
                    var mixItems = [];
                    var mixString = mix.replace(',', '+').replace('|', '+');
                    var mixTokens = mix.split('+');
                    for (var i = 0; i < mixTokens.length; i++){
                        var itemTokens = mixTokens[i].split(':');
                        var itemToken = itemTokens[0];
                        var itemQuantity = parseInt(itemTokens[1]);
                        if (itemQuantity < 1 || mmrpgItemTokens.indexOf(itemToken) < 0){ continue; }
                        mixItems.push({
                            token: itemToken,
                            quantity: itemQuantity
                            });
                        }
                    //console.log('-> mixString:', mixString);
                    //console.log('-> mixTokens:', mixTokens);
                    //console.log('-> mixItems:', mixItems.length, JSON.stringify(mixItems));

                    // If the items list was not empty, we can apply it
                    _self.reset(false);
                    for (var i = 0; i < mixItems.length; i++){
                        var item = mixItems[i];
                        var itemToken = item.token;
                        var itemQuantity = item.quantity;
                        //console.log('-> adding item:', itemToken, 'x' + itemQuantity);
                        _self.addItem({
                            token: itemToken,
                            quantity: itemQuantity
                            }, false);
                        }
                    _self.calculatePowers();
                    _self.generateMission();
                    _self.refreshUI();

                    // Return true on success
                    return true;

                    // end of voidRecipeWizard.parseItemMix()
                    },
                getHashParams: function(){
                    const hash = window.location.hash.substring(1); // Remove the leading #
                    const params = {};
                    hash.split('&').forEach(pair => {
                        const [key, value] = pair.split('=');
                        if (key) params[decodeURIComponent(key)] = decodeURIComponent(value || '');
                        });
                    return params;
                    },
                filterStatPowers: function(powers, sort){
                    sort = typeof sort === 'undefined' ? true : sort;
                    console.log('%c' + 'voidRecipeWizard.filterStatPowers()', 'color: magenta;');
                    //console.log('-> w/ powers:', powers, 'sort:', sort);
                    // parse out powers that represent stats and then order them highest first
                    const _self = this;
                    var mmrpgStats = _self.indexes.statTokens;
                    var statPowers = {};
                    for (var i = 0; i < mmrpgStats.length; i++){
                        var statToken = mmrpgStats[i];
                        var statValue = powers[statToken] || 0;
                        if (statValue !== 0){ statPowers[statToken] = statValue; }
                        }
                    //console.log('=> statPowers:', statPowers);
                    if (!sort){ return statPowers; }
                    // re-sort the stat powers based on their values w/ highest first
                    var statPowersKeys = Object.keys(statPowers);
                    statPowersKeys.sort(function(a, b){ return statPowers[b] - statPowers[a]; });
                    var sortedStatPowers = {};
                    for (var i = 0; i < statPowersKeys.length; i++){
                        var statToken = statPowersKeys[i];
                        var statValue = statPowers[statToken];
                        sortedStatPowers[statToken] = statValue;
                        }
                    //console.log('=> sortedStatPowers:', sortedStatPowers);
                    return sortedStatPowers;
                    // end of voidRecipeWizard.filterStatPowers()
                    },
                filterTypePowers: function(powers, sort){
                    sort = typeof sort === 'undefined' ? true : sort;
                    console.log('%c' + 'voidRecipeWizard.filterTypePowers()', 'color: magenta;');
                    //console.log('-> w/ powers:', powers, 'sort:', sort);
                    // parse out powers that represent types and then order them highest first
                    const _self = this;
                    var mmrpgTypes = _self.indexes.typeTokens;
                    var typePowers = {};
                    for (var i = 0; i < mmrpgTypes.length; i++){
                        var typeToken = mmrpgTypes[i];
                        var typeValue = powers[typeToken] || 0;
                        if (typeValue !== 0){ typePowers[typeToken] = typeValue; }
                        }
                    //console.log('=> typePowers:', typePowers);
                    if (!sort){ return typePowers; }
                    // re-sort the type powers based on their values w/ highest first
                    var typePowersKeys = Object.keys(typePowers);
                    typePowersKeys.sort(function(a, b){ return typePowers[b] - typePowers[a]; });
                    var sortedTypePowers = {};
                    for (var i = 0; i < typePowersKeys.length; i++){
                        var typeToken = typePowersKeys[i];
                        var typeValue = typePowers[typeToken];
                        sortedTypePowers[typeToken] = typeValue;
                        }
                    //console.log('=> sortedTypePowers:', sortedTypePowers);
                    return sortedTypePowers;
                    // end of voidRecipeWizard.filterTypePowers()
                    },
                distributeQuanta: function(quanta, spread) {
                    console.log('%c' + 'voidRecipeWizard.distributeQuanta() w/ quanta: ' + quanta + ', spread: ' + spread, 'color: magenta;');
                    //console.log('-> w/ quanta:', quanta, 'spread:', spread);

                    // Define the main thresholds for primary slots
                    const _self = this;
                    const thresholds = { mecha: 25, master: 100, boss: 500 };
                    const tiers = Object.keys(thresholds);
                    const targets = [];

                    // Predefine variables to hold needed quanta and spread values
                    var numTargetSlots = spread;
                    var quantaAvailable = quanta;
                    var quantaRemaining = quantaAvailable;
                    //console.log('-> numTargetSlots:', numTargetSlots);
                    //console.log('-> quantaAvailable:', quantaAvailable);
                    //console.log('-> quantaRemaining:', quantaRemaining);

                    // We know the spread, so let's pre-populate with empty slots
                    //console.log('-> [step-1] populate targets array with placeholders!');
                    for (let i = 0; i < numTargetSlots; i++) {
                        targets.push({ tier: '', class: 'mecha', amount: 0 });
                        }
                    //console.log('-> step-1 // targets:', JSON.stringify(targets));
                    //console.log('-> step-1 // quantaAvailable:', quantaAvailable);
                    //console.log('-> step-1 // quantaRemaining:', quantaRemaining);

                    // Now let's loop through each tier, in order, and try to upgrade each slot
                    //console.log('-> [step-2] upgrade targets in array to upper tiers!');
                    for (let i = 0; i < tiers.length; i++){
                        let tier = tiers[i];
                        let threshold = thresholds[tier];
                        //console.log('-> processing tier:', tier, 'w/ threshold:', threshold);
                        for (let j = 0; j < targets.length; j++){
                            let target = targets[j];
                            let currentTier = target.tier;
                            let currentAmount = target.amount;
                            let needed = threshold - currentAmount;
                            //console.log('-> processing target:', target, 'w/ currentTier:', currentTier, 'currentAmount:', currentAmount);
                            //console.log('-> checking needed:', needed, 'vs. quantaRemaining:', quantaRemaining);
                            if (needed <= 0){ continue; }
                            if (quantaRemaining >= needed){
                                //console.log('-> quantaRemaining >= needed!');
                                quantaRemaining -= needed;
                                targets[j] = { tier: tier, class: tier, amount: threshold };
                                //console.log('-> updated target to tier:', targets[j].tier, 'class:', targets[j].class, 'amount:', targets[j].amount);
                                }
                            }
                        }
                    //console.log('-> step-2 // targets:', JSON.stringify(targets));
                    //console.log('-> step-2 // quantaAvailable:', quantaAvailable);
                    //console.log('-> step-2 // quantaRemaining:', quantaRemaining);

                    // If there's any remaining quanta, distribute it evenly across the slots
                    //console.log('-> [step-3] distribute remaining quanta evenly across slots!');
                    if (quantaRemaining > 0){
                        let quantaPerSlot = Math.floor(quantaRemaining / numTargetSlots);
                        let quantaOverflow = quantaRemaining % numTargetSlots;
                        //console.log('-> quantaPerSlot:', quantaPerSlot, 'quantaOverflow:', quantaOverflow);
                        for (let i = 0; i < targets.length; i++){
                            let target = targets[i];
                            let currentAmount = target.amount;
                            let newAmount = currentAmount + quantaPerSlot;
                            if (quantaOverflow > 0){
                                newAmount += 1;
                                quantaOverflow -= 1;
                                }
                            targets[i].amount = newAmount;
                            //console.log('-> updated target:', targets[i]);
                            }
                        }
                    //console.log('-> step-3 // targets:', JSON.stringify(targets));
                    //console.log('-> step-3 // quantaAvailable:', quantaAvailable);
                    //console.log('-> step-3 // quantaRemaining:', quantaRemaining);

                    // Return the list of generated targets
                    return targets;

                    // end of voidRecipeWizard.distributeQuanta()
                    },
                generateTargetQueue: function(robots, types, stats){
                    console.log('%c' + 'voidRecipeWizard.generateTargetQueue()', 'color: magenta;');
                    //console.log('-> w/ robots:', robots, 'types:', types, 'stats:', stats);
                    // Collect important refs and indexes for processing
                    const _self = this;
                    const mmrpgIndexRobots = mmrpgIndex.robots;
                    const mmrpgIndexRobotsTokens = Object.keys(mmrpgIndexRobots);
                    var typePowers = types;
                    var statPowers = stats;
                    var allowTypes = Object.keys(types);
                    var sortByStats = Object.keys(stats);
                    var sortByTypes = Object.keys(types);
                    var targetQueue = Object.values(robots);
                    //console.log('=> targetQueue (base):', targetQueue);
                    // First we filter-out any robots that don't have elemental energy
                    //console.log('~> filtering targetQueue by core types....');
                    targetQueue = targetQueue.filter(function(token){
                        var types = [];
                        var info = mmrpgIndexRobots[token];
                        if (info.robot_core !== ''){ types.push(info.robot_core); }
                        if (types.length && info.robot_core2 !== ''){ types.push(info.robot_core2); }
                        if (!types.length){ types.push('none'); }
                        return allowTypes.indexOf(types[0]) !== -1 || allowTypes.indexOf(types[1]) !== -1;
                        });
                    //console.log('=> targetQueue (filtered):', targetQueue);
                    // First we sort the queue based on database order just to make everything consistent
                    //console.log('~> sorting targetQueue by database order....');
                    targetQueue.sort(function(a, b){
                        var orderValueA = mmrpgIndexRobotsTokens.indexOf(a);
                        var orderValueB = mmrpgIndexRobotsTokens.indexOf(b);
                        //console.log('-> comparing', a, 'w/ order:', orderValueA, 'vs.', b, 'w/ order:', orderValueB);
                        if (orderValueA !== orderValueB){ return orderValueA - orderValueB; }
                        return 0;
                        });
                    //console.log('=> targetQueue (sorted-by-order):', targetQueue);
                    // Last we re-sort the queue based on each robot's stats given stat-order priority w/ type-power bonuses
                    if (sortByStats.length || sortByTypes.length){
                        //console.log('~> sorting targetQueue by stats and/or types....');
                        targetQueue.sort(function(a, b){
                            //console.log('--> comparing', a, 'vs.', b, '...');
                            var tokenA = a, robotA = mmrpgIndexRobots[a];
                            var tokenB = b, robotB = mmrpgIndexRobots[b];
                            var robotValueA = 0, robotValueB = 0;
                            //console.log('%c' + '---> START sort-compare for ' + (tokenA+'('+robotValueA+')') + ' vs. ' + (tokenB+'('+robotValueB+')'), 'color: cyan;');
                            if (sortByStats.length){
                                var numSortByStats = sortByStats.length;
                                //console.log('----> start stat-compare w/ sortByStats:', sortByStats);
                                for (var i = 0; i < sortByStats.length; i++){
                                    // Collect the stats for this robot so we can compare them
                                    var statToken = sortByStats[i];
                                    var statValue = numSortByStats - i;
                                    var robotA_statValue = robotA['robot_' + statToken] || 0;
                                    var robotB_statValue = robotB['robot_' + statToken] || 0;
                                    //console.log('----> comparing the '+statToken+'('+statValue+') for', tokenA, '('+robotA_statValue+')', 'vs.', tokenB, '('+robotB_statValue+')');
                                    if (robotA_statValue > robotB_statValue){ robotValueA += statValue; }
                                    else if (robotA_statValue < robotB_statValue){ robotValueB += statValue; }
                                    }
                                //console.log('----> after stat-compare:', tokenA, '('+robotValueA+')', 'vs.', tokenB, '('+robotValueB+')');
                                }
                            if (sortByTypes.length){
                                var numSortByTypes = sortByTypes.length;
                                //console.log('----> start type-compare w/ sortByTypes', sortByTypes);
                                for (var i = 0; i < sortByTypes.length; i++){
                                    // Then collect type value(s) for this robot so we can compare
                                    var typeToken = sortByTypes[i];
                                    var typeValue = numSortByTypes - i;
                                    var robotA_type1 = robotA['robot_core'] || 'none';
                                    var robotA_type2 = robotA['robot_core'] && robotA['robot_core2'] ? robotA['robot_core2'] : '';
                                    var robotB_type1 = robotB['robot_core'] || 'none';
                                    var robotB_type2 = robotB['robot_core'] && robotB['robot_core2'] ? robotB['robot_core2'] : '';
                                    //console.log('----> checking for '+typeToken+'('+typeValue+') in', tokenA, '('+robotA_type1+'/'+robotA_type2+')', 'vs.', tokenB, '('+robotB_type1+'/'+robotB_type2+')');
                                    if (robotA_type1 === typeToken || robotA_type2 === typeToken){ robotValueA += typeValue; }
                                    if (robotB_type1 === typeToken || robotB_type2 === typeToken){ robotValueB += typeValue; }
                                    }
                                //console.log('----> after type-compare:', tokenA, '('+robotValueA+')', 'vs.', tokenB, '('+robotValueB+')');
                                }
                            //console.log('%c' + '---> END sort-compare for ' + (tokenA+'('+robotValueA+')') + ' vs. ' + (tokenB+'('+robotValueB+')'), 'color: lime;');
                            if (robotValueA !== robotValueB){ return robotValueB - robotValueA; }
                            return 0;
                            });
                        //console.log('=> targetQueue (sorted-by-stats)[+type]:', targetQueue);
                        }
                    return targetQueue;
                    // end of voidRecipeWizard.generateTargetQueue()
                    },
                calculatePowers: function(){
                    console.log('%c' + 'voidRecipeWizard.calculatePowers()', 'color: magenta;');

                    // Backup a reference to the parent object
                    const _self = this;

                    // Collect a reference to the void values object and reset
                    var voidItems = _self.items;
                    var voidItemsTokens = Object.keys(voidItems);

                    // Define a variable to hold the calculated powers of all the items
                    var voidPowers = {};
                    voidPowers.powers = {};
                    voidPowers.flags = {};
                    voidPowers.getPowers = function(){ return voidPowers.powers; };
                    voidPowers.getPower = function(token, fallback){ return voidPowers.powers[token] || fallback || 0; };
                    voidPowers.setPower = function(token, value){ voidPowers.powers[token] = Math.round(value * 100) / 100; };
                    voidPowers.incPower = function(token, value){ voidPowers.setPower(token, voidPowers.getPower(token) + value); };
                    voidPowers.decPower = function(token, value){ voidPowers.setPower(token, voidPowers.getPower(token) - value); };
                    voidPowers.modPower = function(token, value, fallback){ voidPowers.setPower(token, voidPowers.getPower(token, fallback) * value); };
                    voidPowers.powers.delta = 0;
                    voidPowers.powers.spread = 0;
                    voidPowers.powers.quanta = 0;
                    voidPowers.powers.level = 0;
                    voidPowers.powers.forte = 0;
                    voidPowers.powers.effort = 0;
                    voidPowers.powers.reward = 0;
                    voidPowers.flags.guard = false;
                    voidPowers.flags.reverse = false;
                    voidPowers.flags.extreme = false;

                    // Loop through all the items, one-by-one, and parse their intrinsic values
                    for (var i = 0; i < voidItemsTokens.length; i++){
                        var itemToken = voidItemsTokens[i];
                        var itemQuantity = voidItems[itemToken];
                        _self.parseItem({token: itemToken}, itemQuantity, voidPowers);
                        //for (var j = 0; j < itemQuantity; j++){ }
                        }

                    // As long as items are present, we should make keep certain values in scope
                    if (voidItemsTokens.length){
                        // Ensure the quanta is always at least zero if there are items present
                        if (voidPowers.powers.quanta < 0){ voidPowers.powers.quanta = 0; }
                        // Ensure the spread always within range when there are items present
                        if (voidPowers.powers.spread < 0){ voidPowers.powers.spread = 0; }
                        // Ensure the level is always at least one if there are items present
                        if (voidPowers.powers.level < 1){ voidPowers.powers.level = 1; }
                        }

                    //console.log('voidPowers have been updated!');
                    _self.powers = {};
                    var voidPowersList = voidPowers.getPowers();
                    var voidPowerKeys = Object.keys(voidPowersList);
                    var voidPowersRequired = _self.voidPowersRequired;
                    for (var i = 0; i < voidPowerKeys.length; i++){
                        var powerToken = voidPowerKeys[i];
                        var powerValue = voidPowersList[powerToken];
                        if (powerValue === 0 && voidPowersRequired.indexOf(powerToken) === -1){ continue; }
                        _self.powers[powerToken] = powerValue;
                        //console.log('-> voidPowers.' + powerToken + ' =', powerValue);
                        }

                    // end of voidRecipeWizard.calculatePowers()
                    },
                generateMission: function(){
                    console.log('%c' + 'voidRecipeWizard.generateMission()', 'color: magenta;');

                    // Backup a reference to the parent object
                    const _self = this;

                    // Clear the existing mission if one is already there
                    _self.mission = {};

                    // Collect reference to the void items + powers so we can reference them
                    var voidItemsTokens = Object.keys(_self.items);
                    var voidPowersList = _self.powers;
                    var voidPowersKeys = Object.keys(voidPowersList);

                    // If we don't have any powers, we can't generate anything
                    if (!voidPowersKeys.length){
                        console.log('%c' + '-> no powers to generate from!', 'color: orange;');
                        return;
                        }

                    // Collect the base amounts of quanta and spread for later reference
                    var baseQuanta = voidPowersList['quanta'] || 0;
                    var baseSpread = voidPowersList['spread'] || 0;
                    //console.log('-> baseQuanta:', baseQuanta, 'baseSpread:', baseSpread);

                    // If we have neither quanta material nor a defined spread limit, we can't generate either
                    if (baseQuanta < 1 && baseSpread < 1){
                        //console.log('%c' + '-> no quanta materia nor spread limit to generate from!', 'color: red;');
                        return;
                        }

                    // First we set-up the different target slots given quanta vs spread
                    // using predefined thresholds to determine each target's class
                    var effectiveSpread = baseSpread >= _self.maxTargets ? _self.maxTargets : (baseSpread < 1 ? 1 : Math.trunc(baseSpread));
                    var distributedQuanta = _self.distributeQuanta(baseQuanta, effectiveSpread, true);
                    //console.log('-> effectiveSpread:', effectiveSpread, 'distributedQuanta:', distributedQuanta);

                    // Pull a filtered list of stat powers and type powers for easier looping
                    var statPowersList = _self.filterStatPowers(voidPowersList);
                    var typePowersList = _self.filterTypePowers(voidPowersList);
                    //console.log('-> statPowersList:', statPowersList);
                    //console.log('-> typePowersList:', typePowersList);

                    // Loop through and check to see which classes are represented
                    var maxTierLevel = 0;
                    for (var i = 0; i < distributedQuanta.length; i++){
                        if (!distributedQuanta[i].tier){ continue; }
                        var tier = distributedQuanta[i].tier;
                        if (tier === 'boss'){ maxTierLevel = Math.max(maxTierLevel, 3); }
                        if (tier === 'master'){ maxTierLevel = Math.max(maxTierLevel, 2); }
                        if (tier === 'mecha'){ maxTierLevel = Math.max(maxTierLevel, 1); }
                        }
                    //console.log('-> maxTierLevel:', maxTierLevel);

                    // Generate a queue of mechas, masters, and bosses given the powers available
                    var targetRobotQueue = {};
                    targetRobotQueue['mecha'] = maxTierLevel >= 1 ? _self.generateTargetQueue((_self.indexes.robotMechaTokens || []), typePowersList, statPowersList) : [];
                    targetRobotQueue['master'] = maxTierLevel >= 2 ? _self.generateTargetQueue((_self.indexes.robotMasterTokens || []), typePowersList, statPowersList) : [];
                    targetRobotQueue['boss'] = maxTierLevel >= 3 ? _self.generateTargetQueue((_self.indexes.robotBossTokens || []), typePowersList, statPowersList) : [];
                    //console.log('-> targetRobotQueue[mecha]:', targetRobotQueue['mecha']);
                    //console.log('-> targetRobotQueue[master]:', targetRobotQueue['master']);
                    //console.log('-> targetRobotQueue[boss]:', targetRobotQueue['boss']);

                    // Define which elemental types each slot should be
                    var typePowerTokens = Object.keys(typePowersList);
                    var typePowerTotal = (typePowerTokens.length ? typePowerTokens.reduce((acc, token) => acc + typePowersList[token], 0) : 0);
                    var typePowerTokensSorted = typePowerTokens.slice().sort(function(a, b){
                        var aIndex = voidItemsTokens.indexOf(a+'-core');
                        var bIndex = voidItemsTokens.indexOf(b+'-core');
                        console.log('-> comparing', a, 'w/ index:', aIndex, 'vs.', b, 'w/ index:', bIndex);
                        return aIndex - bIndex;
                        });
                    var distributedTypes = {};
                    var distributedTypeSlots = [];
                    for (var i = 0; i < typePowerTokensSorted.length; i++){
                        var typeToken = typePowerTokensSorted[i];
                        var typeValue = typePowersList[typeToken];
                        if (typeValue === 0){ continue; }
                        var typeSlots = Math.round((typeValue / typePowerTotal) * effectiveSpread);
                        distributedTypes[typeToken] = typeSlots;
                        // add the token to the slots array as many times as their are slots for it
                        for (var j = 0; j < typeSlots; j++){ distributedTypeSlots.push(typeToken); }
                        }
                    console.log('-> distributedTypes:', JSON.stringify(distributedTypes));
                    console.log('-> distributedTypeSlots:', JSON.stringify(distributedTypeSlots));

                    // Define a quick function for getting the first matching robot from a list and shifting it off
                    const mmrpgIndexRobots = mmrpgIndex.robots;
                    var firstMatchingType = function(queue, type, offset, rotate){
                        offset = typeof offset === 'number' && offset > 0 ? offset : 0;
                        rotate = typeof rotate !== 'undefined' ? (rotate ? true : false) : true;
                        if (offset > 0){ for (var i = 0; i < offset; i++){ queue.push(queue.shift()); } }
                        for (var i = 0; i < queue.length; i++){
                            var robotToken = queue[i];
                            var robotInfo = mmrpgIndexRobots[robotToken];
                            if (robotInfo.robot_core === type || robotInfo.robot_core2 === type){
                                if (rotate){ queue.push(queue.shift()); }
                                return robotToken;
                                }
                            }
                        return '';
                        };

                    // Use calculated quanta-per-target to set-up the different target slots
                    var missionTargets = [];
                    var numTargetSlots = effectiveSpread;
                    for (var slotKey = 0; slotKey < numTargetSlots; slotKey++){
                        var slotTemplate = distributedQuanta[slotKey];
                        //console.log('--> calculating slotKey:', slotKey, 'w/ slotTemplate:', slotTemplate);
                        var targetRobot = {};
                        var targetTier = slotTemplate.tier;
                        var targetClass = slotTemplate.class;
                        var targetQuanta = slotTemplate.amount;
                        targetRobot.token = '';
                        targetRobot.class = targetClass;
                        targetRobot.quanta = targetQuanta;
                        targetRobot.level = 1;
                        targetRobot.type = '';
                        if (distributedTypeSlots.length){
                            // decide which element this target will be
                            targetRobot.type = distributedTypeSlots.shift() || '';
                            distributedTypeSlots.push(targetRobot.type);
                            }
                        if (targetTier.length){
                            // decide which tier this target will be
                            var queueOrder = [];
                            if (targetTier === 'boss'){ queueOrder.push('boss', 'master', 'mecha'); }
                            if (targetTier === 'master'){ queueOrder.push('master', 'mecha'); }
                            if (targetTier === 'mecha'){ queueOrder.push('mecha'); }
                            // loop through and pull appropriate targets given above
                            for (var i = 0; i < queueOrder.length; i++){
                                var queueToken = queueOrder[i];
                                if (targetRobotQueue[queueToken].length){
                                    var offset = typeof voidPowersList['x'+queueToken] !== 'undefined' ? voidPowersList['x'+queueToken] : 0;
                                    var nextToken = firstMatchingType(targetRobotQueue[queueToken], targetRobot.type, offset, true);
                                    if (nextToken){
                                        targetRobot.token = nextToken;
                                        targetRobotQueue[queueToken].push(targetRobot.token);
                                        break;
                                        }
                                    }
                                }
                            }
                        // If a token for this slot count not be found, default to a dark frag
                        if (!targetRobot.token.length){
                            targetRobot.token = 'dark-frag';
                            }
                        // Add the target robot to the mission targets list
                        missionTargets.push(targetRobot);
                        //console.log('--> pushed new target!', '\n-> targetRobot:', targetRobot);
                        }

                    // Update the mission details with the new targets
                    _self.mission.targets = missionTargets;
                    console.log('--> generated new mission w/', '\n-> missionTargets:', missionTargets);

                    // end of voidRecipeWizard.generateMission()
                    },
                getMixString: function(){
                    console.log('%c' + 'voidRecipeWizard.getMixString()', 'color: magenta;');

                    // Backup a reference to the parent object
                    const _self = this;

                    // Collect the updated list of added items to the recipe for looping
                    var voidItems = _self.items;
                    var voidItemsTokens = Object.keys(voidItems);
                    //console.log('-> voidItems:', voidItems);
                    //console.log('-> voidItemsTokens:', voidItemsTokens);

                    // Generate a mix string based on the current list of items
                    var mixItems = [];
                    for (var i = 0; i < voidItemsTokens.length; i++){
                        var itemToken = voidItemsTokens[i];
                        var itemQuantity = voidItems[itemToken];
                        if (itemQuantity < 1){ continue; }
                        mixItems.push(itemToken + ':' + itemQuantity);
                        }
                    //console.log('-> mixItems:', mixItems);
                    var thisMixString = mixItems.length > 0 ? mixItems.join('+') : '';

                    // Return the generated mix string
                    return thisMixString;

                    // end of voidRecipeWizard.getMixString()
                    },
                refreshHash: function(){
                    console.log('%c' + 'voidRecipeWizard.refreshHash()', 'color: magenta;');

                    // Backup a reference to the parent object
                    const _self = this;

                    // Collect the updated list of added items to the recipe for looping
                    var voidItems = _self.items;
                    var voidItemsTokens = Object.keys(voidItems);
                    //console.log('-> voidItems:', voidItems);
                    //console.log('-> voidItemsTokens:', voidItemsTokens);

                    // We should also update the mix string in the URL hash with any changes
                    var thisMixString = _self.getMixString();
                    var currLocationHash = window.location.hash.replace(/^#/, '');
                    var newLocationHash = 'mix=' + (thisMixString.length ? thisMixString : '-');
                    //console.log('-> currLocationHash (', currLocationHash, ') vs. newLocationHash (', newLocationHash, ')');
                    if (currLocationHash !== newLocationHash){
                        //console.log('-> currLocationHash !== newLocationHash');
                        //console.log('-> adding/updating mix in URL:', newLocationHash);
                        _self.hashUpdatedByApp = true;
                        window.location.hash = newLocationHash;
                        if (_self.hashUpdateTimeout){ clearTimeout(_self.hashUpdateTimeout); }
                        _self.hashUpdateTimeout = setTimeout(function(){
                            _self.hashUpdatedByApp = false;
                            delete _self.hashUpdateTimeout;
                            }, 1000);
                        }

                    // end of voidRecipeWizard.refreshHash()
                    },
                refreshUI: function(){
                    console.log('%c' + 'voidRecipeWizard.refreshUI()', 'color: magenta;');

                    // Backup a reference to the parent object
                    const _self = this;

                    // Collect reference to relevant void elements and values
                    var $itemsSelected = _self.xrefs.itemsSelected;
                    var $itemsPalette = _self.xrefs.itemsPalette;
                    var $resetButton = _self.xrefs.resetButton;
                    var $codeButton = _self.xrefs.codeButton;
                    var $missionDetails = _self.xrefs.missionDetails;
                    var $targetList = _self.xrefs.missionTargets;

                    // Collect a reference to the list of defined elemental types and stats
                    var mmrpgStats = _self.indexes.statTokens;
                    var mmrpgTypes = _self.indexes.typeTokens;

                    // Collect the list of added items and any history
                    var voidItems = _self.items;
                    var voidItemsTokens = Object.keys(voidItems);
                    var voidHistory = _self.history;

                    // Check to see which was the last item token added
                    var lastItemToken = '';
                    if (voidHistory.length){
                        lastItemToken = voidHistory[voidHistory.length - 1].token;
                        }

                    // Clear the item selection area and then rebuild it with the new items
                    var $selectedWrapper = $('.wrapper', $itemsSelected);
                    var $paletteWrappers = $('.wrapper', $itemsPalette);
                    var $paletteItems = $('.item[data-token]', $itemsPalette);
                    var numSlotsAvailable = _self.maxItems;
                    var numSlotsUsed = voidItemsTokens.length;
                    $selectedWrapper.html('');
                    $paletteItems.removeClass('active');
                    if (voidItemsTokens.length > 0){
                        const mmrpgIndexItems = mmrpgIndex.items;
                        for (var i = 0; i < voidItemsTokens.length; i++){
                            // Generate the markup for the item then add to the selection area
                            var itemToken = voidItemsTokens[i];
                            var itemInfo = mmrpgIndexItems[itemToken];
                            var itemName = itemInfo.item_name;
                            var itemNameBr = itemName.replace(' ', '<br />');
                            var itemQuantity = voidItems[itemToken] || 0;
                            var itemImage = itemInfo.item_image || itemToken;
                            var itemClass = 'item' + (itemToken === lastItemToken ? ' recent' : '');
                            var itemIcon = '/images/items/'+itemImage+'/icon_right_40x40.png?'+gameSettings.cacheDate;
                            var itemMarkup = '<div class="'+itemClass+'" data-token="'+itemToken+'" data-quantity="'+itemQuantity+'">';
                                itemMarkup += '<div class="icon"><img class="has_pixels" src="'+itemIcon+'" alt="'+itemName+'"></div>';
                                itemMarkup += '<div class="name">'+itemNameBr+'</div>';
                                itemMarkup += '<div class="quantity">'+itemQuantity+'</div>';
                            itemMarkup += '</div>';
                            $selectedWrapper.append(itemMarkup);
                            // Update the parent button in the palette area to show that its active
                            $paletteItems.filter('.item[data-token="'+itemToken+'"]').addClass('active');
                            }
                        }

                    // Fill empty slots with item-placeholder elements for visual clarity,
                    // otherwise if all slots are full we should disable further selections
                    if (numSlotsUsed < numSlotsAvailable){
                        //console.log('there are empty slots!', (numSlotsAvailable - numSlotsUsed));
                        $itemsPalette.attr('data-select', '*');
                        var emptySlots = numSlotsAvailable - numSlotsUsed;
                        for (var i = 0; i < emptySlots; i++){
                            var placeholderMarkup = '<div class="item placeholder"></div>';
                            $selectedWrapper.append(placeholderMarkup);
                            }
                        } else {
                        //console.log('all slots are full!');
                        $itemsPalette.attr('data-select', 'active');
                        }

                    // Check and update the displayed quantities of any items visible in the palette
                    var itemsToUpdate = _self.indexes.itemTokens;
                    if (itemsToUpdate.length > 0){
                        const mmrpgIndexItems = mmrpgIndex.items;
                        for (var i = 0; i < itemsToUpdate.length; i++){
                            var itemToken = itemsToUpdate[i];
                            var itemInfo = mmrpgIndexItems[itemToken];
                            var $paletteButton = $('.item[data-token="'+itemToken+'"]', $itemsPalette);
                            var baseQuantity = parseInt($paletteButton.attr('data-base-quantity'));
                            var addedQuantity = voidItems[itemToken] || 0;
                            var newQuantity = baseQuantity - addedQuantity;
                            $paletteButton.attr('data-quantity', newQuantity);
                            $paletteButton.find('.quantity').text(newQuantity);
                            //console.log('updating', itemToken, 'button in palette w/', {baseQuantity: baseQuantity, addedQuantity: addedQuantity, newQuantity: newQuantity});
                            }
                        }

                    // Always show the code mix button as there doesn't seem to be a reason not-to
                    $codeButton.addClass('visible');

                    // Show or hide the reset button depending on whether or not there's a selection to reset
                    if (numSlotsUsed > 0){ $resetButton.addClass('visible'); }
                    else { $resetButton.removeClass('visible'); }

                    // Pre-clear the mission details, the target list, and the battle field
                    $missionDetails.html('');
                    $targetList.html('');
                    //var $battleField = _self.xrefs.battleField; // TODO

                    // Collect the list of void powers and keys so we can re-sort in the next step
                    var voidPowers = _self.powers;
                    var voidPowersKeys = Object.keys(voidPowers);
                    var voidPowersValSum = 0 + (voidPowersKeys.length ? (function(){ var sum = 0; for (var i = 0; i < voidPowersKeys.length; i++){ var key = voidPowersKeys[i]; sum += voidPowers[key]; } return sum; })() : 0);
                    if (voidPowersValSum === 0){
                        $missionDetails.append('<span class="loading">&hellip;</span>');
                        $targetList.append('<span class="loading">&hellip;</span>');
                        return;
                        }
                    console.log('voidPowersValSum:', voidPowersValSum);
                    console.log('voidPowersKeys(raw):', '\n-> [' + voidPowersKeys.join(', ') + ']');

                    // First, sort the power tokens by their values going highest to lowest,
                    // then sort all the keys pertaining to stats first, all keys pertaining to
                    // elemental types second, and anything else can come after that at the end
                    voidPowersKeys.sort(function(a, b){
                        var aPower = voidPowers[a] || 0;
                        var bPower = voidPowers[b] || 0;
                        if (aPower > bPower){ return -1; }
                        if (aPower < bPower){ return 1; }
                        return 0;
                        });
                    console.log('voidPowersKeys(power-sorted):', '\n-> [' + voidPowersKeys.join(', ') + ']');
                    var statPowerTokens = mmrpgStats;
                    var typePowerTokens = mmrpgTypes;
                    voidPowersKeys.sort(function(pk1, pk2){
                        var pk1StatIndex = statPowerTokens.indexOf(pk1);
                        var pk1TypeIndex = typePowerTokens.indexOf(pk1);
                        var pk1IsStat = pk1StatIndex !== -1;
                        var pk1IsType = pk1TypeIndex !== -1;
                        var pk2StatIndex = statPowerTokens.indexOf(pk2);
                        var pk2TypeIndex = typePowerTokens.indexOf(pk2);
                        var pk2IsStat = pk2StatIndex !== -1;
                        var pk2IsType = pk2TypeIndex !== -1;
                        if (pk1IsStat && !pk2IsStat){ return -1; }
                        if (!pk1IsStat && pk2IsStat){ return 1; }
                        if (pk1IsType && !pk2IsType){ return -1; }
                        if (!pk1IsType && pk2IsType){ return 1; }
                        return 0;
                        });
                    console.log('voidPowersKeys(stat-and-type-sorted):', '\n-> [' + voidPowersKeys.join(', ') + ']');

                    // Then we can collect the ordered list of required power tokens and
                    // use that to sort any required power tokens to the top of the list
                    var voidPowersRequired = _self.voidPowersRequired;
                    voidPowersKeys.sort(function(a, b){
                        var aIndex = voidPowersRequired.indexOf(a);
                        var bIndex = voidPowersRequired.indexOf(b);
                        if (aIndex !== -1 && bIndex !== -1){ return aIndex - bIndex; }
                        if (aIndex !== -1){ return -1; }
                        if (bIndex !== -1){ return 1; }
                        return 0;
                        });
                    console.log('voidPowersKeys(required-first):', '\n-> [' + voidPowersKeys.join(', ') + ']');

                    // Define a quick class (which we'll move later?) for rendering void powers
                    var VoidPowersRenderer = {
                        generatePowerElement: function({ token, name, icon, value, maxValue, typeClass, isPercent, extraClasses, hasCode, hasArrows, boosts, breaks, blur, spanOrder }){
                            //console.log('-> generating power element:', {name, icon, value, maxValue, typeClass, isPercent, extraClasses, hasArrows, boosts, breaks, blur});
                            let arrowClasses = 'value arrows';
                            let iconClasses = 'icon';
                            let nameClasses = 'name';
                            let valueClasses = 'value';
                            let codeClasses = 'code';
                            if (typeof blur !== 'object'){ blur = []; }
                            if (blur.indexOf('arrows') !== -1){ arrowClasses += ' blur'; }
                            if (blur.indexOf('icon') !== -1){ iconClasses += ' blur'; }
                            if (blur.indexOf('name') !== -1){ nameClasses += ' blur'; }
                            if (blur.indexOf('value') !== -1){ valueClasses += ' blur'; }
                            if (blur.indexOf('code') !== -1){ codeClasses += ' blur'; }
                            //console.log('-> classes:', {arrowClasses, iconClasses, nameClasses, valueClasses});
                            var arrowsMarkup = '';
                            var iconMarkup = '';
                            var nameMarkup = '';
                            var valueMarkup = '';
                            var codeMarkup = '';
                            if (hasArrows && (boosts || breaks)){
                                arrowsMarkup += '<span class="'+arrowClasses+'">';
                                for (let i = 0; i < boosts; i++){ arrowsMarkup += '<span class="arrow boost"><i class="fas fa-caret-up"></i></span>'; }
                                for (let i = 0; i < breaks; i++){ arrowsMarkup += '<span class="arrow break"><i class="fas fa-caret-down"></i></span>'; }
                                arrowsMarkup += '</span>';
                                }
                            if (icon){
                                iconMarkup += '<span class="'+iconClasses+'"><i class="fa fas fa-'+icon+'"></i></span>';
                                }
                            if (name){
                                nameMarkup += '<span class="'+nameClasses+'"><strong>'+name+'</strong></span>';
                                }
                            if (value !== undefined){
                                valueMarkup += '<span class="'+valueClasses+'"><data>'+ value + (isPercent ? '%' : '') + '</data>';
                                if (maxValue !== undefined){ valueMarkup += '<sub>/ '+maxValue+'</sub>'; }
                                valueMarkup += '</span>';
                                }
                            if (hasCode){
                                var powerCode = token.substring(0, 2).toUpperCase();
                                if (token === 'energy'){ powerCode = 'LE'; }
                                if (token === 'weapons'){ powerCode = 'WE'; }
                                if (token === 'attack'){ powerCode = 'AT'; }
                                if (token === 'defense'){ powerCode = 'DF'; }
                                if (token === 'speed'){ powerCode = 'SP'; }
                                codeMarkup += '<span class="'+codeClasses+'"><code>'+powerCode+'</code></span>';
                                }
                            //console.log('-> generated power elements:', {arrowsMarkup, iconMarkup, nameMarkup, valueMarkup, codeMarkup});
                            if (!Array.isArray(spanOrder)){ spanOrder = []; }
                            if (spanOrder.indexOf('arrows') === -1){ spanOrder.push('arrows'); }
                            if (spanOrder.indexOf('icon') === -1){ spanOrder.push('icon'); }
                            if (spanOrder.indexOf('name') === -1){ spanOrder.push('name'); }
                            if (spanOrder.indexOf('value') === -1){ spanOrder.push('value'); }
                            if (spanOrder.indexOf('code') === -1){ spanOrder.push('code'); }
                            let markup = '<div class="power ' + typeClass + ' ' + (extraClasses || '') + '">';
                                for (let i = 0; i < spanOrder.length; i++){
                                    let spanToken = spanOrder[i];
                                    if (spanToken === 'arrows'){ markup += arrowsMarkup; }
                                    if (spanToken === 'icon'){ markup += iconMarkup; }
                                    if (spanToken === 'name'){ markup += nameMarkup; }
                                    if (spanToken === 'value'){ markup += valueMarkup; }
                                    if (spanToken === 'code'){ markup += codeMarkup; }
                                    }
                            markup += '</div>';
                            return markup;
                            },
                        renderBasePowers: function($missionDetails, basePowersValues){
                            let markup = '<div class="void-powers ltr bgo base-powers">';
                                for (const [token, value] of Object.entries(basePowersValues)) {
                                    const config = {
                                        token: token,
                                        name: token === 'quanta' ? 'Quanta' : 'Spread',
                                        icon: token === 'quanta' ? 'atom' : 'code-branch',
                                        value,
                                        typeClass: 'base type ' + (token === 'quanta' ? 'water' : 'laser'),
                                        blur: ['name']
                                        };
                                    markup += this.generatePowerElement(config);
                                    }
                            markup += '</div>';
                            $missionDetails.append(markup);
                            },
                        renderRankPowers: function($missionDetails, rankPowersValues, rankPowersMaxValues){
                            let markup = '<div class="void-powers rtl bgo rank-powers">';
                                for (const [token, value] of Object.entries(rankPowersValues)) {
                                    const maxValue = rankPowersMaxValues[token] || 0;
                                    const config = {
                                        token: token,
                                        name: token === 'level' ? 'Level' : 'Forte',
                                        icon: token === 'level' ? 'star' : 'fist-raised',
                                        value,
                                        maxValue,
                                        typeClass: 'rank type ' + (token === 'level' ? 'electric' : 'shield'),
                                        //blur: token === 'forte' ? ['name'] : [],
                                        blur: ['name']
                                        };
                                    markup += this.generatePowerElement(config);
                                    }
                            markup += '</div>';
                            $missionDetails.append(markup);
                            },
                        renderSortPowers: function($missionDetails, sortPowersGrouped){
                            for (const [groupToken, groupValues] of Object.entries(sortPowersGrouped)){
                                let groupIcon = groupToken === 'stat' ? 'bullseye' : 'fire-alt';
                                let groupName = groupToken === 'stat' ? 'Flow (Stats)' : 'Flow (Types)';
                                let markup = '<div class="void-powers ltr bgi sort-powers ' + groupToken + '-sort-powers">';
                                    const groupValuesTokens = Object.keys(groupValues);
                                    let sortedTokens = Object.values(groupValuesTokens);
                                    sortedTokens.sort((a, b) => groupValues[b] - groupValues[a]);
                                    sortedTokens.forEach((token, index) => {
                                        const config = {
                                            token: token,
                                            icon: false,
                                            name: token.charAt(0).toUpperCase() + token.slice(1),
                                            value: groupValues[token],
                                            typeClass: ('sort type ' + token + ' ps'+(sortedTokens.length - index)),
                                            blur: ['name', 'value']
                                            };
                                        markup += this.generatePowerElement(config);
                                        });
                                    markup += '<div class="power label type space_empty">';
                                        markup += '<span class="icon"><i class="fa fas fa-' + groupIcon + '"></i></span>';
                                        markup += '<span class="name blur"><strong>' + groupName + '</strong></span>';
                                        markup += '<span class="icon"><i class="fa fas fa-sort"></i></span>';
                                    markup += '</div>';
                                markup += '</div>';
                                $missionDetails.append(markup);
                                }
                            },
                        renderStatPowers: function($missionDetails, statPowersValues){
                            // sort the rank powers by value to display them in order of energy, weapons, attack, defense, speed
                            let statOrder = _self.indexes.statTokens;
                            let markup = '<div class="void-powers rtl bgo stat-powers">';
                                for (var i = 0; i < statOrder.length; i++){
                                    var token = statOrder[i];
                                    if (!statPowersValues[token]){ continue; }
                                    var values = statPowersValues[token];
                                    const config = {
                                        token: token,
                                        name: token.charAt(0).toUpperCase() + token.slice(1),
                                        value: values['value'],
                                        typeClass: ('stat type ' + token),
                                        hasCode: true,
                                        hasArrows: true,
                                        boosts: values['boosts'],
                                        breaks: values['breaks'],
                                        blur: ['name', 'value'],
                                        spanOrder: ['arrows', 'value', 'name', 'code']
                                        };
                                    markup += this.generatePowerElement(config);
                                    }
                            markup += '</div>';
                            $missionDetails.append(markup);
                            }
                        };

                    // Now we update the list of void powers in the UI to show any changes
                    console.log('voidPowers:', voidPowers);
                    console.log('voidPowersKeys:', voidPowersKeys);
                    if (voidPowersKeys.length){

                        /*
                        // TEMP TEMP TEMP: HARD CODED BASE POWERS!!!
                        // Check the base powers (quanta and spread) to display the appropriate markup
                        var basePowersMarkup = '';
                        var basePowersValues = {quanta: 123, spread: 3};
                        var basePowersTokens = Object.keys(basePowersValues);
                        basePowersMarkup += '<div class="void-powers ltr bgo base-powers">';
                            for (var i = 0; i < basePowersTokens.length; i++){
                                var powerToken = basePowersTokens[i];
                                var powerName = powerToken === 'quanta' ? 'Quanta' : 'Spread';
                                var powerIcon = powerToken === 'quanta' ? 'atom' : 'code-branch'; //'cubes';
                                var powerValue = basePowersValues[powerToken];
                                var powerClass = 'base type ' + (powerToken === 'quanta' ? 'water' : 'laser');
                                // TEMP TEMP TEMP MAX-VAL TESTING TEMP TEMP TEMP //
                                powerValue = powerToken === 'quanta' ? 9999 : 8;
                                // TEMP TEMP TEMP MAX-VAL TESTING TEMP TEMP TEMP //
                                basePowersMarkup += '<div class="power '+powerClass+'">';
                                    basePowersMarkup += '<span class="icon"><i class="fa fas fa-'+powerIcon+'"></i></span>';
                                    basePowersMarkup += '<span class="name blur"><strong>'+powerName+'</strong></span>';
                                    basePowersMarkup += '<span class="value"><data>'+powerValue+'</data></span>';
                                basePowersMarkup += '</div>';
                                }
                        basePowersMarkup += '</div>';
                        $missionDetails.append(basePowersMarkup);
                        */

                        // Check the base powers (quanta and spread) to display the appropriate markup
                        var basePowersValues = {quanta: 123, spread: 3};
                        VoidPowersRenderer.renderBasePowers($missionDetails, basePowersValues);

                        /*
                        // TEMP TEMP TEMP: HARD CODED RANK POWERS!!!
                        // Check the rank powers (level and forte) to display appropriate markup
                        var rankPowersMarkup = '';
                        var rankPowersValues = {level: 123, forte: 45};
                        var rankPowersValuesMax = {level: 999, forte: 99};
                        var rankPowersTokens = Object.keys(rankPowersValues);
                        rankPowersMarkup += '<div class="void-powers rtl bgo rank-powers">';
                            for (var i = 0; i < rankPowersTokens.length; i++){
                                var powerToken = rankPowersTokens[i];
                                var powerName = powerToken === 'level' ? 'Level' : 'Forte';
                                var powerValue = rankPowersValues[powerToken] || 0;
                                var powerValueMax = rankPowersValuesMax[powerToken] || 0;
                                var powerIsPercent = powerToken === 'forte' ? true : false;
                                var powerNameBlurred = powerToken === 'level' ? false : true;
                                var powerClass = 'power rank type ' + (powerToken === 'level' ? 'electric' : 'shield');
                                var powerIcon = powerToken === 'level' ? 'star' : 'fist-raised';
                                rankPowersMarkup += '<div class="'+powerClass+'">';
                                    rankPowersMarkup += '<span class="icon"><i class="fa fas fa-'+powerIcon+'"></i></span>';
                                    rankPowersMarkup += '<span class="name'+(powerNameBlurred ? ' blur' : '')+'"><strong>'+powerName+'</strong></span>';
                                    rankPowersMarkup += '<span class="value">';
                                        rankPowersMarkup += '<data>'+powerValue+'</data>';
                                        rankPowersMarkup += '<sub>/ '+powerValueMax+'</sub>';
                                    rankPowersMarkup += '</span>';
                                rankPowersMarkup += '</div>';
                                }
                        rankPowersMarkup += '</div>';
                        $missionDetails.append(rankPowersMarkup);
                        */

                        // Check the rank powers (level and forte) to display appropriate markup
                        var rankPowersValues = {level: 123, forte: 45};
                        var rankPowersValuesMax = {level: 999, forte: 99};
                        VoidPowersRenderer.renderRankPowers($missionDetails, rankPowersValues, rankPowersValuesMax);

                        /*
                        // TEMP TEMP TEMP: HARD CODED SORT POWERS!!!
                        // Check the stat-sort powers to display appropriate markup
                        var sortPowersGrouped = {};
                        sortPowersGrouped.stat = {energy: 47, weapons: -4, attack: 3, defense: -25, speed: 2};
                        //sortPowersGrouped.type = {flame: 3, water: 1, electric: 28};
                        sortPowersGrouped.type = {flame: 3, water: 1, electric: 28, cutter: 7, crystal: 38, time: -34, nature: 2};
                        var sortPowersGroupedTokens = Object.keys(sortPowersGrouped);
                        for (var i = 0; i < sortPowersGroupedTokens.length; i++){
                            var groupToken = sortPowersGroupedTokens[i];
                            var groupPowerIcon = 'circle';
                            if (groupToken === 'stat'){ groupPowerIcon = 'bullseye'; }
                            else if (groupToken === 'type'){ groupPowerIcon = 'fire-alt'; }
                            else { groupPowerIcon = 'asterisk'; }
                            var sortPowersMarkup = '';
                            var sortPowersValues = sortPowersGrouped[groupToken];
                            var sortPowersTokens = Object.keys(sortPowersValues);
                            sortPowersMarkup += '<div class="void-powers ltr bgi sort-powers '+groupToken+'-sort-powers">';
                                for (var j = 0; j < sortPowersTokens.length; j++){
                                    var powerToken = sortPowersTokens[j];
                                    var powerName = powerToken.charAt(0).toUpperCase() + powerToken.slice(1);
                                    var powerValue = sortPowersValues[powerToken];
                                    var powerSize = (sortPowersTokens.length - j);
                                    var powerClass = 'power sort type ' + powerToken + ' ps' + powerSize;
                                    var powerValueClass = 'value';
                                    // TEMP TEMP TEMP MAX-VAL TESTING TEMP TEMP TEMP //
                                    powerValue = 999;
                                    // TEMP TEMP TEMP MAX-VAL TESTING TEMP TEMP TEMP //
                                    var powerValueText = (powerValue !== 0 ? (powerValue > 0 ? '+' : '-') : '') + Math.abs(powerValue);
                                    sortPowersMarkup += '<div class="power '+powerClass+'">';
                                        sortPowersMarkup += '<span class="name blur"><strong>'+powerName+'</strong></span>';
                                        sortPowersMarkup += '<span class="value blur"><data>'+powerValueText+'</data></span>';
                                    sortPowersMarkup += '</div>';
                                }
                                sortPowersMarkup += '<div class="label type space_empty">';
                                    sortPowersMarkup += '<span class="icon"><i class="fa fas fa-'+groupPowerIcon+'"></i></span>';
                                    sortPowersMarkup += '<span class="icon"><i class="fa fas fa-sort"></i></span>';
                                sortPowersMarkup += '</div>';
                            sortPowersMarkup += '</div>';
                            $missionDetails.append(sortPowersMarkup);
                            }
                        */

                        // Check the stat-sort powers to display appropriate markup
                        var sortPowersGrouped = {
                            stat: {energy: 47, weapons: -4, attack: 3, defense: -25, speed: 2},
                            type: {flame: 3, water: 1, electric: 28, cutter: 7, crystal: 38, time: -34, nature: 2}
                            };
                        VoidPowersRenderer.renderSortPowers($missionDetails, sortPowersGrouped);

                        /*
                        // TEMP TEMP TEMP: HARD CODED STAT POWERS!!!
                        // Check for relative stat levels and display appropriate markup
                        var statPowersMarkup = '';
                        var statPowersValues = {};
                        statPowersValues.attack = {value: 5, boosts: 5, breaks: 0};
                        statPowersValues.speed = {value: 3, boosts: 3, breaks: 0};
                        statPowersValues.defense = {value: -1, boosts: 0, breaks: 1};
                        var statPowersTokens = Object.keys(statPowersValues);
                        statPowersMarkup += '<div class="void-powers rtl bgo stat-powers">';
                            for (var i = 0; i < statPowersTokens.length; i++){
                                var statToken = statPowersTokens[i];
                                var statName = statToken.charAt(0).toUpperCase() + statToken.slice(1);
                                var statCode = statToken.substring(0, 2).toUpperCase();
                                if (statToken === 'attack'){ statCode = 'AT'; }
                                if (statToken === 'defense'){ statCode = 'DF'; }
                                if (statToken === 'speed'){ statCode = 'SP'; }
                                var statValues = statPowersValues[statToken] || false;
                                if (statValues === false){ continue; }
                                var statValue = statValues.value;
                                var statBoosts = statValues.boosts;
                                var statBreaks = statValues.breaks;
                                var statHasArrows = statBoosts > 0 || statBreaks > 0 ? true : false;
                                var statClass = 'power stat type ' + statToken + (statHasArrows ? ' has-arrows' : '');
                                statPowersMarkup += '<div class="'+statClass+'">';
                                    statPowersMarkup += '<span class="value arrows">';
                                        if (statBoosts){ for (var j = 0; j < statBoosts; j++){ statPowersMarkup += '<span class="arrow boost"><i class="fas fa-caret-up"></i></span>'; } }
                                        if (statBreaks){ for (var j = 0; j < statBreaks; j++){ statPowersMarkup += '<span class="arrow break"><i class="fas fa-caret-down"></i></span>'; } }
                                    statPowersMarkup += '</span>';
                                    statPowersMarkup += '<span class="name blur"><strong>'+statName+'</strong></span>';
                                    statPowersMarkup += '<span class="code"><code>'+statCode+'</code></span>';
                                statPowersMarkup += '</div>';
                            }
                        statPowersMarkup += '</div>';
                        $missionDetails.append(statPowersMarkup);
                        */

                        // Check for relative stat levels and display appropriate markup
                        var statPowersValues = {
                            attack: {value: 0.3, boosts: 0, breaks: 0},
                            //attack: {value: 5.3, boosts: 5, breaks: 0},
                            speed: {value: 3, boosts: 3, breaks: 0},
                            defense: {value: -1, boosts: 0, breaks: 1}
                            };
                        VoidPowersRenderer.renderStatPowers($missionDetails, statPowersValues);

                        } else {

                        // Put the loading div back into the frame as nothing has been added
                        $missionDetails.append('<span class="loading">&hellip;</span>');

                        }

                    // Update the list of target robots in the panel if any have been generated
                    var missionInfo = _self.mission;
                    var missionTargets = missionInfo.targets || [];
                    if (missionTargets.length){
                        console.log('updating mission target list!', '\n-> missionInfo:', missionInfo, '\n-> missionTargets:', missionTargets);
                        const mmrpgIndexRobots = mmrpgIndex.robots;
                        const frameTokenByKey = {0: 'base', 1: 'defense', 2: 'base2', 3: 'defend', 4: 'base', 5: 'defend', 6: 'base2', 7: 'defend'};
                        var targetListRobotMarkup = '';
                        for (var i = 0; i < missionTargets.length; i++){
                            var targetKey = i;
                            var targetRobot = missionTargets[i];
                            //console.log('-> targetRobot:', targetRobot);
                            var targetRobotToken = targetRobot.token;
                            var targetRobotSlotType = targetRobot.type || 'empty';
                            var targetRobotInfo = mmrpgIndexRobots[targetRobotToken] || false;
                            if (!targetRobotInfo){ continue; }
                            var targetRobotClass = targetRobot.class;
                            var targetRobotQuanta = targetRobot.quanta;
                            var targetRobotLevel = targetRobot.level;
                            var targetRobotName = targetRobotInfo['robot_name'] || targetRobotToken;
                            var targetRobotImage = targetRobotInfo['robot_image'] || targetRobotToken;
                            var targetRobotTypes = targetRobotInfo['robot_core'] || 'none';
                            if (targetRobotInfo['robot_core'] && targetRobotInfo['robot_core2']){ targetRobotTypes += '_'+targetRobotInfo['robot_core2']; }
                            var targetRobotImageSize = targetRobotInfo['robot_image_size'] || 40;
                            var targetRobotImageSizeX = targetRobotImageSize + 'x' + targetRobotImageSize;
                            var targetRobotFrame = frameTokenByKey[targetKey] || '00';
                            var targetRobotSprite = '/images/robots/'+targetRobotImage+'/sprite_left_'+targetRobotImageSizeX+'.png?'+gameSettings.cacheTime;
                            var targetRobotMarkup = '<div class="target">';
                                targetRobotMarkup += '<div class="image">';
                                    targetRobotMarkup += '<div '
                                        + 'class="sprite sprite_'+targetRobotImageSizeX+' sprite_'+targetRobotImageSizeX+'_'+targetRobotFrame+'" '
                                        + 'style="background-image: url('+targetRobotSprite+');" '
                                        + 'data-size="'+targetRobotSprite+'" '
                                        + 'data-frame="'+targetRobotFrame+'" '
                                        + '>'+targetRobotName+'</div>';
                                targetRobotMarkup += '</div>';
                                targetRobotMarkup += '<div class="label">';
                                    targetRobotMarkup += '<span class="name">'+targetRobotName+'</span>';
                                targetRobotMarkup += '</div>';
                                targetRobotMarkup += '<i class="type '+targetRobotSlotType+'"></i>';
                            targetRobotMarkup += '</div>';
                            targetListRobotMarkup += targetRobotMarkup;
                            }
                        $targetList.append('<div class="wrapper">' + targetListRobotMarkup + '</div>');
                        } else {
                        $targetList.append('<span class="loading">&hellip;</span>');
                        }

                    // end of voidRecipeWizard.refreshUI()
                    },
                };

            // Initialize the void recipe calculator
            console.log('%c' + 'Initializing the voidRecipeWizard()', 'color: orange;');
            voidRecipeWizard.init($voidRecipeWizard);

            })();
        }

})();
