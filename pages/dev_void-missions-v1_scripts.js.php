(function(){

    // Predefine some object arrays to hold our information
    gameSettings.customIndex.contentIndex = {};
    var mmrpgIndex = gameSettings.customIndex.contentIndex;
    var mmrpgQueue = {};
    var mmrpgMission = {};

    // Add the void mission data to the global object
    mmrpgIndex.types = <?= json_encode($mmrpg_index_types) ?>;
    //mmrpgIndex.players = <?= json_encode($mmrpg_index_players) ?>;
    mmrpgIndex.robots = <?= json_encode($mmrpg_index_robots) ?>;
    //mmrpgIndex.abilities = <?= json_encode($mmrpg_index_abilities) ?>;
    mmrpgIndex.items = <?= json_encode($mmrpg_index_items) ?>;
    mmrpgIndex.fields = <?= json_encode($mmrpg_index_fields) ?>;
    console.log('mmrpgIndex:', typeof mmrpgIndex, mmrpgIndex);

    // Predefine the item groups to be used in the void cauldron item palette
    let voidRecipeItemGroups = <?= json_encode($void_item_groups_index, JSON_NUMERIC_CHECK) ?>;
    let voidRecipeItemsDisabled = <?= json_encode($void_items_disabled) ?>;
    let voidRecipeItemsQuantities = <?= json_encode($void_items_quantities) ?>;
    //console.log('+++ voidRecipeItemGroups:', voidRecipeItemGroups);
    //console.log('+++ voidRecipeItemsDisabled:', voidRecipeItemsDisabled);
    //console.log('+++ voidRecipeItemsQuantities:', voidRecipeItemsQuantities);

    // Check to see if the Void Recipe calculator is available
    let $voidRecipeWizard = $('#void-recipe');
    if ($voidRecipeWizard.length > 0){
        (function(){

            //console.log('voidRecipeWizard:', $voidRecipeWizard);

            // Create a VOID RECIPE WIZARD so we can easily add/remove and recalculate on-the-stop
            var voidRecipeWizard = {
                init: function($container, customData){
                    console.log('%c' + 'voidRecipeWizard.init()', 'color: magenta;');
                    //console.log('-> w/ $container:', typeof $container, $container.length, $container);
                    //console.log('-> w/ customData:', typeof customData, customData);
                    const _self = this;
                    _self.name = 'voidRecipeWizard';
                    _self.version = '1.0.0';
                    _self.config = {
                        firstStep: 1,
                        maxItems: 10,
                        maxTargets: 8,
                        maxArrows: 5,
                        maxLevel: 100,
                        maxForte: 10,
                        realMaxLevel: 999,
                        realMaxForte: 99,
                        currentItemMix: '',
                        minQuantaPerClass: {mecha: 25, master: 100, boss: 500},
                        voidPowersRequired: ['delta', 'spread', 'quanta', 'level', 'forte'],
                        };
                    _self.reset(false);
                    _self.setup($container, customData);
                    _self.calculatePowers();
                    _self.generateMission();
                    _self.refreshUI();
                    _self.done();
                    //console.log('voidRecipeWizard is ' + ('%c' + 'ready'), 'color: lime;');
                    //console.log('=> voidRecipeWizard:', _self);
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
                setup: function($container, customData){
                    console.log('%c' + 'voidRecipeWizard.setup()', 'color: magenta;');
                    //console.log('-> w/ $container:', typeof $container, $container.length, $container);
                    //console.log('-> w/ customData:', typeof customData, customData);

                    // Backup a reference to the parent object
                    const _self = this;
                    const config = _self.config;

                    // Predefine some parent variables for the class
                    _self.xrefs = {};
                    _self.items = {};
                    _self.powers = {};
                    _self.flows = {};
                    _self.mission = {};
                    _self.history = [];
                    _self.indexes = {};
                    _self.cache = {};

                    // Update relevant flags to show the wizard is loading and not ready
                    _self.nowReady(false);
                    _self.nowLoading(true);

                    // Catalogue all of the content indexes into easy-to-use reference formats
                    let indexes = _self.indexes;
                    indexes.types = customData.types || {};
                    indexes.robots = customData.robots || {};
                    indexes.items = customData.items || {};
                    indexes.itemsGroups = customData.itemsGroups || {};
                    indexes.itemsQuantities = customData.itemsQuantities || {};
                    indexes.itemsDisabled = customData.itemsDisabled || {};
                    indexes.fields = customData.fields || {};
                    _self.catalogIndexes();

                    // Set some default values in the cache
                    let cache = _self.cache;
                    cache.totalItems = 0;
                    cache.totalItemsGroups = 0;

                    // Generate a renderer for the void powers
                    _self.voidPowersRenderer = _self.getPowerRenderer();

                    // Generate the void cauldron markup given a container
                    _self.generateMarkup($container);

                    // Bind events to the interactive elements
                    _self.bindEvents();

                    // end of voidRecipeWizard.setup()
                    },
                done: function(){
                    console.log('%c' + 'voidRecipeWizard.done()', 'color: magenta;');
                    const _self = this;
                    _self.nowLoading(false);
                    _self.nowReady(true);
                    // end of voidRecipeWizard.done()
                    },
                catalogIndexes: function(){
                    console.log('%c' + 'voidRecipeWizard.catalogIndexes()', 'color: magenta;');
                    const _self = this;
                    const config = _self.config;
                    const indexes = _self.indexes;

                    // Pre-define a list of item tokens we can use later
                    const mmrpgItems = indexes.items;
                    let mmrpgItemTokens = Object.keys(mmrpgItems);
                    indexes.itemTokens = mmrpgItemTokens;
                    //console.log('mmrpgItemTokens:', mmrpgItemTokens);

                    // Pre-define a list of stat tokens we can use later
                    let mmrpgStats = ['energy', 'weapons', 'attack', 'defense', 'speed'];
                    indexes.statTokens = mmrpgStats;
                    //console.log('mmrpgStats:', mmrpgStats);

                    // Pre-collect a list of type tokens we can use later
                    const mmrpgTypes = indexes.types;
                    if (mmrpgTypes['none']){ mmrpgTypes['none']['type_name'] = 'Neutral'; } // because it drives me crazy
                    let mmrpgTypeTokens = Object.keys(mmrpgTypes);
                    mmrpgTypeTokens = mmrpgTypeTokens.filter(function(token){
                        let info = mmrpgTypes[token];
                        if (token === 'none'){ return true; }
                        else if (info.type_class === 'normal'){ return true; }
                        return false;
                        });
                    indexes.typeTokens = mmrpgTypeTokens;
                    //console.log('mmrpgTypeTokens:', mmrpgTypeTokens);

                    // Pre-collect a list of robot tokens that we can use later
                    const mmrpgRobots = indexes.robots;
                    let mmrpgRobotTokens = Object.keys(mmrpgRobots);
                    mmrpgRobotTokens = mmrpgRobotTokens.filter(function(token){
                        let info = mmrpgRobots[token];
                        //console.log('checking info for ', token, ' | info:', info);
                        if (!info.robot_flag_published){ return false; }
                        else if (!info.robot_flag_complete){ return false; }
                        else if (info.robot_flag_hidden){ return false; }
                        else if (info.robot_class === 'system'){ return false; }
                        return true;
                        });
                    indexes.robotTokens = mmrpgRobotTokens;
                    //console.log('mmrpgRobotTokens:', mmrpgRobotTokens);

                    // Create sub-lists of robot tokens for each class for later
                    const filterToClass = function(tokens, className){
                        return tokens.filter(function(token){
                            var info = mmrpgRobots[token];
                            if (info.robot_class === className){ return true; }
                            return false;
                            });
                        };
                    var mmrpgRobotMechaTokens = filterToClass(mmrpgRobotTokens, 'mecha');
                    var mmrpgRobotMasterTokens = filterToClass(mmrpgRobotTokens, 'master');
                    var mmrpgRobotBossTokens = filterToClass(mmrpgRobotTokens, 'boss');
                    indexes.robotMechaTokens = mmrpgRobotMechaTokens;
                    indexes.robotMasterTokens = mmrpgRobotMasterTokens;
                    indexes.robotBossTokens = mmrpgRobotBossTokens;
                    //console.log('mmrpgRobotMechaTokens:', mmrpgRobotMechaTokens);
                    //console.log('mmrpgRobotMasterTokens:', mmrpgRobotMasterTokens);
                    //console.log('mmrpgRobotBossTokens:', mmrpgRobotBossTokens);

                    // Loop through every single robot and categorize them into quanta tiers
                    //let baseQuanta = 50, roundUpTo = 25, roundVariance = 5;
                    let baseQuanta = 100, roundUpTo = 25, roundVariance = 5;
                    let voidTiers = {};
                    let voidTierTypes = [];
                    let voidTierValues = [];
                    let voidTierRanks = {};
                    for (let i = 0; i < mmrpgRobotTokens.length; i++){
                        let robotToken = mmrpgRobotTokens[i];
                        let robotInfo = mmrpgRobots[robotToken];
                        let robotClass = robotInfo.robot_class || 'mecha';
                        let robotCoreType = robotInfo.robot_core || 'none';
                        let robotStatTotal = (robotInfo.robot_energy
                            //+ robotInfo.robot_weapons
                            + (robotInfo.robot_weapons * 10)
                            + robotInfo.robot_attack
                            + robotInfo.robot_defense
                            + robotInfo.robot_speed
                            );
                        let roundedStatTotal = Math.round((robotStatTotal + roundVariance) / roundUpTo) * roundUpTo;
                        //let robotTierVal = baseQuanta + roundedStatTotal;
                        let robotTierVal = robotStatTotal;
                        if (robotInfo.robot_core === 'empty'){
                            if (robotClass === 'mecha'){ robotTierVal = 1; }
                            else if (robotClass === 'master'){ robotTierVal = 10; }
                            else if (robotClass === 'boss'){ robotTierVal = 100; }
                            else { robotTierVal = 0; }
                            }
                        let thisVoidTier = voidTiers[robotCoreType] || { type: robotCoreType, thresholds: [], queues: {} };
                        if (!thisVoidTier.queues[robotTierVal]){ thisVoidTier.queues[robotTierVal] = []; }
                        thisVoidTier.queues[robotTierVal].push(robotToken);
                        voidTiers[robotCoreType] = thisVoidTier;
                        if (voidTierTypes.indexOf(robotCoreType) < 0){ voidTierTypes.push(robotCoreType); }
                        if (voidTierValues.indexOf(robotTierVal) < 0){ voidTierValues.push(robotTierVal); }
                        }
                    for (let i = 0; i < voidTierTypes.length; i++){
                        let tierType = voidTierTypes[i];
                        let thisVoidTier = voidTiers[tierType];
                        thisVoidTier.thresholds = Object.keys(thisVoidTier.queues || {}).map(Number).sort((a, b) => b - a);
                        //console.log('thisVoidTier['+tierType+']', '\n' + '-> type:', thisVoidTier.type, '\n' + '-> thresholds:', thisVoidTier.thresholds, '\n' + '-> queues:', thisVoidTier.queues, '\n' + '-> {raw}:', thisVoidTier);
                        }
                    voidTierValues = Object.values(voidTierValues).map(Number).sort((a, b) => a - b);
                    for (let i = 0; i < voidTierValues.length; i++){
                        let tierVal = voidTierValues[i], tierRank = 1 + i;
                        voidTierRanks[tierVal] = tierRank;
                        }
                    //console.log('voidTiers:', voidTiers);
                    //console.log('voidTierTypes:', voidTierTypes);
                    //console.log('voidTierValues:', voidTierValues);
                    //console.log('voidTierRanks:', voidTierRanks);
                    indexes.voidTiers = voidTiers;
                    indexes.voidTierTypes = voidTierTypes;
                    indexes.voidTierValues = voidTierValues;
                    indexes.voidTierRanks = voidTierRanks;

                    // end of voidRecipeWizard.catalogIndexes()
                    },
                generateMarkup: function($container){
                    console.log('%c' + 'voidRecipeWizard.generateMarkup()', 'color: magenta;');
                    //console.log('-> w/ $container:', typeof $container, $container.length, $container);
                    const _self = this;
                    const config = _self.config;
                    const indexes = _self.indexes;
                    const cache = _self.cache;

                    // Collect references to key and parent elements on the page
                    let $parentDiv = $container;
                    let $upperDeck = $('#vcr_upper', $parentDiv);
                    let $lowerDeck = $('#vcr_lower', $parentDiv);
                    let $titleDiv = $('#vcr_title', $parentDiv);
                    let $selectionDiv = $('#vcr_selection', $parentDiv);
                    let $paletteDiv = $('#vcr_palette', $parentDiv);
                    let $effectsDiv = $('#vcr_effects', $parentDiv);
                    let $missionTargets = $('#vcr_targets', $parentDiv);
                    let $missionDetails = $('#vcr_details', $parentDiv);
                    let $battleField = $('#vcr_field', $parentDiv);
                    let $debugDiv = $('#vcr_debug');

                    // Generate the item button markup to use in palette
                    let itemButtonMarkup = _self.getItemButtonMarkup();

                    // Update the palette are with the full item list
                    $paletteDiv.empty();
                    var firstStep = config.firstStep || 1;
                    var totalItems = cache.totalItems || 0;
                    var totalGroups = cache.totalItemsGroups || 0;
                    let itemPaletteMarkup = '<div class="item-list" data-count="' + totalItems + '" data-step="' + firstStep + '" data-select="*">' + itemButtonMarkup + '</div>';
                    $paletteDiv.append(itemPaletteMarkup);
                    let $itemsPalette = $('.item-list', $paletteDiv);

                    // Generate the selection area to hold items that have been added
                    $selectionDiv.empty();
                    let itemSelectMarkup = '<div class="item-list" data-count="0"><div class="wrapper float-left"><span class="loading">&hellip;</span></div></div>';
                    let resetButtonMarkup = '<a class="button reset"><i class="fa fas fa-undo"></i></a>';
                    let codeButtonMarkup = '<a class="button code"><i class="fa fas fa-code"></i></a>';
                    $selectionDiv.append(itemSelectMarkup);
                    $selectionDiv.append(resetButtonMarkup);
                    $selectionDiv.append(codeButtonMarkup);
                    let $itemsSelected = $('.item-list', $selectionDiv);
                    let $resetButton = $('.button.reset', $selectionDiv);
                    let $codeButton = $('.button.code', $selectionDiv);

                    // Ensure a debug div is there else set false
                    if ($debugDiv.length === 0){ $debugDiv = false; }

                    // Save the references to the object for later use
                    var xrefs = _self.xrefs;
                    xrefs.parentDiv = $parentDiv;
                    xrefs.upperDeck = $upperDeck;
                    xrefs.lowerDeck = $lowerDeck;
                    xrefs.titleDiv = $titleDiv;
                    xrefs.selectionDiv = $selectionDiv;
                    xrefs.paletteDiv = $paletteDiv;
                    xrefs.effectsDiv = $effectsDiv;
                    xrefs.missionTargets = $missionTargets;
                    xrefs.missionDetails = $missionDetails;
                    xrefs.battleField = $battleField;
                    xrefs.itemsPalette = $itemsPalette;
                    xrefs.itemsSelected = $itemsSelected;
                    xrefs.resetButton = $resetButton;
                    xrefs.codeButton = $codeButton;
                    xrefs.debugDiv = $debugDiv;
                    //console.log('xrefs:', xrefs);

                    // DEBUG DEBUG DEBUG
                    //console.log('CHECK FOR DEBUG', '\n', 'xrefs.debugDiv:', typeof xrefs.debugDiv, xrefs.debugDiv);
                    if (xrefs.debugDiv){
                        //console.log('SHOWING DEBUG ON PAGE');
                        let $debugDiv = xrefs.debugDiv;
                        $debugDiv.find('.debug-tier-costs').remove();
                        let voidTiers = indexes.voidTiers;
                        let orderedTypes = Object.values(indexes.typeTokens);
                        let classIcons = {master: 'robot', mecha: 'ghost', boss: 'skull'};
                        let coreReqThreshold = 500;
                        let numColumns = 4;
                        let blocksPerColumn = Math.ceil(orderedTypes.length / numColumns);
                        let lastColumn = false;
                        let currentColumn = 0;
                        let tierCostsMarkup = '';
                        let tierCostsMarkupByCol = {};
                        for (let i = 0; i < orderedTypes.length; i++){
                            let tierType = orderedTypes[i];
                            if (!voidTiers[tierType]){ continue; }
                            let thisVoidTier = voidTiers[tierType];
                            let tierTypeInfo = indexes.types[tierType];
                            let tierThresholds = thisVoidTier.thresholds;
                            let reversedTierThresholds = tierThresholds.slice(0).reverse();
                            let thisMarkup = '';
                                thisMarkup += '<div class="block tier-block">';
                                    thisMarkup += '<strong class="name type ' + tierType + '">' + tierTypeInfo.type_name + '</strong>\n';
                                    thisMarkup += '<ul class="list">\n';
                                        for (let j = 0; j < reversedTierThresholds.length; j++){
                                            let tierThreshold = reversedTierThresholds[j];
                                            let tierRobots = thisVoidTier.queues[tierThreshold];
                                            let tierCoreReq = (tierThreshold / coreReqThreshold);
                                            thisMarkup += '<li class="item">\n';
                                                thisMarkup += '<label class="name type empty">';
                                                    //thisMarkup += '<span class="req"><i class="fa fa-fire-alt"></i> >= ' + tierCoreReq + '</span>';
                                                    thisMarkup += '<span class="req"><i class="fa fa-atom"></i> >= ' + tierThreshold + '</span>';
                                                thisMarkup += '</label>\n';
                                                thisMarkup += '<ul class="list">\n';
                                                    for (let k = 0; k < tierRobots.length; k++){
                                                        let robotToken = tierRobots[k];
                                                        let robotOrder = (k + 1), robotOrderText = robotOrder + _self.getOrdinalSuffix(robotOrder);
                                                        let robotInfo = indexes.robots[robotToken];
                                                        let robotClass = robotInfo.robot_class || 'mecha';
                                                        let robotIcon = classIcons[robotClass] || 'bug';
                                                        let robotName = robotInfo.robot_name;
                                                        let robotType = robotInfo.robot_core || 'none';
                                                        let robotImage = robotInfo.robot_image || robotToken;
                                                        thisMarkup += '<li class="item">';
                                                            thisMarkup += '<span class="order">' + robotOrderText + '</span>';
                                                            thisMarkup += '<strong class="robot '+robotClass+'">';
                                                                thisMarkup += '<span class="r-icon type '+robotType+'"><i class="fa fa-'+robotIcon+'"></i></span>';
                                                                thisMarkup += '<span class="r-name type '+robotType+'">' + robotName + '</span>\n';
                                                            thisMarkup += '</strong>\n';
                                                        thisMarkup += '</li>\n';
                                                        }
                                                thisMarkup += '</ul>\n';
                                            thisMarkup += '</li>\n';
                                            }
                                    thisMarkup += '</ul>\n';
                                thisMarkup += '</div>';
                            currentColumn++;
                            if (currentColumn > numColumns){ currentColumn = 1; }
                            if (!tierCostsMarkupByCol[currentColumn]){ tierCostsMarkupByCol[currentColumn] = ''; }
                            tierCostsMarkupByCol[currentColumn] += thisMarkup;
                            lastColumn = currentColumn;
                            }
                        //tierCostsMarkup += '</div>';
                        //console.log('tierCostsMarkupByCol:', tierCostsMarkupByCol);
                        for (let i = 0; i <= numColumns; i++){
                            if (!tierCostsMarkupByCol[i]){ continue; }
                            let currentColumn = (i + 1);
                            let thisMarkup = tierCostsMarkupByCol[i];
                            thisMarkup = '<div class="col no-'+currentColumn+' of-'+numColumns+'">' + thisMarkup + '</div>';
                            tierCostsMarkup += thisMarkup;
                            }
                        tierCostsMarkup = '<div class="section debug-tier-costs">' + tierCostsMarkup + '</div>';
                        $debugDiv.append(tierCostsMarkup);
                        }
                    // DEBUG DEBUG DEBUG



                    // Return true on success
                    return true;

                    // end of voidRecipeWizard.generateMarkup()
                    },
                getItemButtonMarkup: function(){
                    console.log('%c' + 'voidRecipeWizard.getItemButtonMarkup()', 'color: magenta;');
                    const _self = this;
                    const config = _self.config;
                    const indexes = _self.indexes;
                    const cache = _self.cache;

                    // Collect references to the indexes we need to generate the markup
                    const mmrpgItems = indexes.items;
                    const voidItemGroups = indexes.itemsGroups;
                    const voidItemQuantities = indexes.itemsQuantities;
                    const voidItemDisabled = indexes.itemsDisabled;

                    // Generate the markup for the items themselves first before grouping
                    let groupMarkupByStep = {};
                    let numItemsTotal = 0;
                    let firstStepNum = -1;
                    let currItemRowline = 0;
                    let zIndex = Object.keys(voidItemGroups).length + 11;
                    Object.keys(voidItemGroups).forEach((stepKey) => {
                        const stepInfo = voidItemGroups[stepKey];
                        const stepNum = parseInt(stepKey) + 1;
                        if (stepNum > firstStepNum){ firstStepNum = stepNum; }
                        const stepGroups = stepInfo.groups;
                        if (!stepGroups || Object.keys(stepGroups).length === 0) return;
                        groupMarkupByStep[stepKey] = [];
                        Object.keys(stepGroups).forEach((groupToken) => {
                            const groupInfo = stepGroups[groupToken];
                            const groupItems = groupInfo.items;
                            const groupRowline = groupInfo.rowline;
                            const groupColspan = groupInfo.colspan;
                            if (!groupItems || groupItems.length === 0) return;
                            const groupItemsMarkup = [];
                            groupItems.forEach((itemToken, itemKey) => {
                                if (!mmrpgItems[itemToken]) return;
                                const itemInfo = mmrpgItems[itemToken];
                                const itemName = itemInfo.item_name;
                                const itemNameBr = itemName.replace(/ /g, '<br />');
                                const itemIsOneline = !itemName.includes(' ');
                                const itemIsDisabled = voidItemDisabled.includes(itemToken);
                                const itemQuantity = voidItemQuantities[itemToken] || 0;
                                const itemImage = itemInfo.item_image || itemToken;
                                const iconUrl = '/images/items/' + itemImage + '/icon_right_40x40.png?' + gameSettings.cacheDate;
                                let itemMarkup = '';
                                itemMarkup += '<div class="item' + (itemIsDisabled ? ' disabled' : '') + '" ';
                                        itemMarkup += 'data-key="' + itemKey + '" ';
                                        itemMarkup += 'data-token="' + itemToken + '" ';
                                        itemMarkup += 'data-group="' + groupToken + '" ';
                                        itemMarkup += 'data-quantity="' + itemQuantity + '" ';
                                        itemMarkup += 'style="z-index: 0;" ';
                                        itemMarkup += '>';
                                    itemMarkup += '<div class="icon"><img class="has_pixels" src="' + iconUrl + '" alt="' + itemName + '"></div>';
                                    itemMarkup += '<div class="name ' + (itemIsOneline ? 'one-line' : '') + '">' + itemNameBr + '</div>';
                                    itemMarkup += '<div class="quantity">' + itemQuantity + '</div>';
                                itemMarkup += '</div>';
                                groupItemsMarkup.push(itemMarkup);
                                });
                            if (groupItemsMarkup.length === 0) return;
                            const addNewline = groupRowline !== currItemRowline && groupMarkupByStep[stepKey].length >= 1;
                            const groupMarkup = groupItemsMarkup.join('\n');
                            const groupMarkupClass = 'group ' + groupToken + ' type ' + groupInfo.color;
                            let groupMarkupAttrs = 'data-group="' + groupToken + '" data-count="' + groupItems.length + '"';
                            groupMarkupAttrs += ' data-rowline="' + groupRowline + '" data-colspan="' + groupColspan + '"';
                            let wrappedGroupMarkup = '<div class="' + groupMarkupClass + '" ' + groupMarkupAttrs + '>\n' + groupMarkup + '\n</div>';
                            if (addNewline) wrappedGroupMarkup = '<div class="clear"></div>\n' + wrappedGroupMarkup;
                            groupMarkupByStep[stepKey].push(wrappedGroupMarkup);
                            numItemsTotal += groupItems.length;
                            currItemRowline = groupRowline;
                            });
                        });


                    // Now that we have item markup generated and grouped-together, we can wrap them in parent elements
                    let itemButtonsMarkup = '';
                    let sideKeys = {middle: 0, left: 0, right: 0};
                    Object.keys(groupMarkupByStep).forEach((stepKey) => {
                        const wrappedGroupMarkup = groupMarkupByStep[stepKey].join('\n');
                        const stepInfo = voidItemGroups[stepKey];
                        const stepNum = parseInt(stepKey) + 1;
                        const stepToken = stepInfo.step || stepNum;
                        const stepSide = stepInfo.side || (stepNum < Math.ceil(Object.keys(voidItemGroups).length / 2) ? 'left' : (stepNum > Math.ceil(Object.keys(voidItemGroups).length / 2) ? 'right' : 'middle'));
                        const stepSideKey = sideKeys[stepSide]++;
                        const stepLayer = stepSide === 'middle' ? 1 : (2 + stepSideKey);
                        const stepIsActive = stepNum === 1;
                        let wrapperMarkup = '';
                        wrapperMarkup += '<div class="wrapper' + (stepIsActive ? ' active' : '') + '" ';
                                wrapperMarkup += 'data-step="' + stepNum + '" ';
                                wrapperMarkup += 'data-token="' + stepToken + '" ';
                                wrapperMarkup += 'data-side="' + stepSide + '" ';
                                wrapperMarkup += 'data-sidekey="' + stepSideKey + '" ';
                                wrapperMarkup += 'data-layer="' + stepLayer + '"';
                                wrapperMarkup += '>';
                            wrapperMarkup += '<div class="label">';
                            wrapperMarkup += '<strong>' + stepInfo.name + ' (' + stepInfo.label + ')</strong>';
                            wrapperMarkup += '</div>';
                            wrapperMarkup += '<div class="groups">' + wrappedGroupMarkup + '</div>';
                        wrapperMarkup += '</div>';
                        itemButtonsMarkup += wrapperMarkup;
                        });

                    // Update the void index with any calculated values
                    cache.totalItems = numItemsTotal;
                    cache.totalItemsGroups = Object.keys(voidItemGroups).length;

                    // Return the generated button markup
                    return itemButtonsMarkup;

                    // end of voidRecipeWizard.getItemButtonMarkup()
                    },
                getPowerRenderer: function(){
                    console.log('%c' + 'voidRecipeWizard.getPowerRenderer()', 'color: magenta;');
                    const _self = this;
                    const config = _self.config;
                    return {
                        generatePowerElement: function({ token, name, value, maxValue, isPercent, isPlusMinus,
                                iconClass, typeClass, extraClasses, extraStyles,
                                hasCode, hasArrows, numBoosts, numBreaks,
                                spanOrder, spanPadding, blurSpans, hideSpans,
                                isDisabled,
                                }){
                            //console.log('-> generating power element:', {token, name, value, maxValue, isPercent, iconClass, typeClass, extraClasses, extraStyles, hasCode, hasArrows, numBoosts, numBreaks, spanOrder, spanPadding, blurSpans});
                            token = typeof token === 'string' ? token : '';
                            name = name || '['+token+']';
                            value = typeof value !== 'undefined' ? value : 0;
                            iconClass = iconClass || false;
                            maxValue = maxValue || null;
                            typeClass = typeClass || '';
                            extraClasses = extraClasses || '';
                            extraStyles = extraStyles || '';
                            isPercent = isPercent || false;
                            isPlusMinus = isPlusMinus || false;
                            hasCode = hasCode || false;
                            hasArrows = hasArrows || false;
                            numBoosts = numBoosts || 0;
                            numBreaks = numBreaks || 0;
                            blurSpans = blurSpans || [];
                            hideSpans = hideSpans || [];
                            spanPadding = spanPadding || 0;
                            spanOrder = spanOrder || [];
                            isDisabled = isDisabled || false;
                            isInert = value === 0 || isDisabled;
                            let arrowClasses = 'value arrows';
                            let iconClasses = 'icon';
                            let nameClasses = 'name';
                            let valueClasses = 'value';
                            let codeClasses = 'code';
                            if (typeof blurSpans !== 'object'){ blurSpans = []; }
                            if (blurSpans.indexOf('arrows') !== -1){ arrowClasses += ' blur'; }
                            if (blurSpans.indexOf('icon') !== -1){ iconClasses += ' blur'; }
                            if (blurSpans.indexOf('name') !== -1){ nameClasses += ' blur'; }
                            if (blurSpans.indexOf('value') !== -1){ valueClasses += ' blur'; }
                            if (blurSpans.indexOf('code') !== -1){ codeClasses += ' blur'; }
                            //console.log('-> classes:', {arrowClasses, iconClasses, nameClasses, valueClasses});
                            var nameMarkup = '';
                            var valueMarkup = '';
                            var iconMarkup = '';
                            var arrowsMarkup = '';
                            var codeMarkup = '';
                            if (name && !hideSpans.includes('name')){
                                nameMarkup += '<span class="'+nameClasses+'"><strong>'+name+'</strong></span>';
                                }
                            if (typeof value !== 'undefined' && !hideSpans.includes('value')){
                                var roundedValue = Math.round(value * 10) / 10;
                                valueMarkup += '<span class="'+valueClasses+'">';
                                    //valueMarkup += '<data>'+ ((isPlusMinus && roundedValue !== 0) ? (roundedValue > 0 ? '+' : '-') : '') + roundedValue + (isPercent ? '%' : '') + '</data>';
                                    valueMarkup += ((isPlusMinus && roundedValue !== 0) ? (roundedValue > 0 ? '+' : '-') : '');
                                    valueMarkup += '<data>' + roundedValue + '</data>';
                                    valueMarkup += (isPercent ? '%' : '');
                                    if (maxValue){ valueMarkup += '<sub>/ '+maxValue+'</sub>'; }
                                valueMarkup += '</span>';
                                }
                            if (iconClass && !hideSpans.includes('icon')){
                                iconMarkup += '<span class="'+iconClasses+'"><i class="fa fas fa-'+iconClass+'"></i></span>';
                                }
                            if (hasArrows && (numBoosts || numBreaks) && !hideSpans.includes('arrows')){
                                arrowsMarkup += '<span class="'+arrowClasses+'">';
                                for (let i = 0; i < numBoosts; i++){ arrowsMarkup += '<span class="arrow boost"><i class="fas fa-caret-up"></i></span>'; }
                                for (let i = 0; i < numBreaks; i++){ arrowsMarkup += '<span class="arrow break"><i class="fas fa-caret-down"></i></span>'; }
                                arrowsMarkup += '</span>';
                                if (numBoosts >= config.maxArrows){ extraClasses += ' max'; }
                                if (numBreaks >= config.maxArrows){ extraClasses += ' min'; }
                                }
                            if (hasCode && !hideSpans.includes('code')){
                                var powerCode = token.substring(0, 2).toUpperCase();
                                if (token === 'energy'){ powerCode = 'LE'; }
                                if (token === 'weapons'){ powerCode = 'WE'; }
                                if (token === 'attack'){ powerCode = 'AT'; }
                                if (token === 'defense'){ powerCode = 'DF'; }
                                if (token === 'speed'){ powerCode = 'SP'; }
                                codeMarkup += '<span class="'+codeClasses+'"><code>'+powerCode+'</code></span>';
                                }
                            if (!Array.isArray(spanOrder)){ spanOrder = []; }
                            if (spanOrder.indexOf('arrows') === -1){ spanOrder.push('arrows'); }
                            if (spanOrder.indexOf('icon') === -1){ spanOrder.push('icon'); }
                            if (spanOrder.indexOf('name') === -1){ spanOrder.push('name'); }
                            if (spanOrder.indexOf('value') === -1){ spanOrder.push('value'); }
                            if (spanOrder.indexOf('code') === -1){ spanOrder.push('code'); }
                            var elementClasses = '';
                            elementClasses += 'power ' + typeClass;
                            if (isInert){ elementClasses += ' inert'; }
                            if (isDisabled){ elementClasses += ' disabled'; }
                            if (extraClasses.length){ elementClasses += ' ' + extraClasses; }
                            var elementStyles = '';
                            if (spanPadding){
                                let left = 0, right = 0;
                                if (typeof spanPadding === 'number'){ left = right = spanPadding; }
                                else if (typeof spanPadding === 'object' && spanPadding.left){ left = spanPadding.left; }
                                else if (typeof spanPadding === 'object' && spanPadding.right){ right = spanPadding.right; }
                                if (left){ elementStyles += ' padding-left: ' + Math.round(left) + 'px;'; }
                                if (right){ elementStyles += ' padding-right: ' + Math.round(right) + 'px;'; }
                                }
                            if (extraStyles.length){ elementStyles += ' ' + extraStyles; }
                            elementClasses = elementClasses.trim().replace(/\s+/g, ' ');
                            elementStyles = elementStyles.trim().replace(/\s+/g, ' ');
                            let classAttr = elementClasses ? ' class="' + elementClasses + '"' : '';
                            let stylesAttr = elementStyles ? ' style="' + elementStyles + '"' : '';
                            let markup = '<div data-power="'+token+'" ' + classAttr + stylesAttr + '>';
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
                            let icons = {quanta: 'atom', spread: 'code-branch', focus: 'compress', delta: 'delta'};
                            let types = {quanta: 'water', spread: 'laser', focus: 'time', delta: 'space_empty'};
                            let markup = '<div class="void-powers ltr bgo cts base-powers">';
                                for (const [token, value] of Object.entries(basePowersValues)) {
                                    //if (value === 0){ continue; }
                                    let name = token.charAt(0).toUpperCase() + token.slice(1);
                                    let icon = token === 'quanta' ? 'atom' : 'code-branch';
                                    var disabled = (value === 0) ? true : false;
                                    const config = {
                                        token, name, value,
                                        iconClass: icons[token],
                                        typeClass: ('base type ' + types[token]),
                                        blurSpans: ['name'],
                                        isPlusMinus: (token === 'focus' ? true : false),
                                        isDisabled: disabled,
                                        hideSpans: (disabled ? ['name', 'value'] : []),
                                        };
                                    markup += this.generatePowerElement(config);
                                    }
                            markup += '</div>';
                            $missionDetails.append(markup);
                            },
                        renderRankPowers: function($missionDetails, rankPowersValues, rankPowersMaxValues){
                            let markup = '<div class="void-powers rtl bgo cts rank-powers">';
                                for (const [token, value] of Object.entries(rankPowersValues)) {
                                    let name = token.charAt(0).toUpperCase() + token.slice(1);
                                    let maxValue = rankPowersMaxValues[token] || 0;
                                    const config = {
                                        token, name, value, maxValue,
                                        iconClass: (token === 'level' ? 'star' : 'fist-raised'),
                                        typeClass: ('rank type ' + (token === 'level' ? 'electric' : 'shield')),
                                        blurSpans: ['name'],
                                        };
                                    markup += this.generatePowerElement(config);
                                    }
                            markup += '</div>';
                            $missionDetails.append(markup);
                            },
                        renderFlowPowers: function($missionDetails, flowPowersValues){
                            console.log('VoidPowersRenderer.renderFlowPowers($missionDetails, flowPowersValues) w/', {flowPowersValues});
                            let flowPowersTokens = Object.keys(flowPowersValues);
                            //console.log('-> rendering flowPowersTokens [' + flowPowersTokens.join(', ', ) + ']');
                            let flowValuesSum = 0 + (flowPowersTokens.length ? (function(){ var keys = flowPowersTokens, vals = flowPowersValues, sum = 0; for (var i = 0; i < keys.length; i++){ var key = keys[i]; sum += Math.abs(vals[key]); } return sum; })() : 0);
                            //if (!flowPowersTokens.length || flowValuesSum === 0){ return; }
                            if (!flowPowersTokens.length || flowValuesSum === 0){
                                flowPowersTokens = ['empty'];
                                flowPowersValues = {empty: 0};
                                }
                            //console.log('-> rendering group:', {'typeFlows', flowPowersValues, flowPowersTokens, flowValuesSum});
                            let groupIcon = 'fire-alt'; //'microchip'; //'bullseye';
                            let groupName = 'Flow (Types)';
                            let groupPaddMod = 15;
                            let groupPaddMax = 80;
                            let markup = '<div class="void-powers rtl bgi cts flow-powers">';
                                markup += '<div class="power label type space_empty">';
                                    markup += '<span class="icon"><i class="fa fas fa-' + groupIcon + '"></i></span>';
                                    markup += '<span class="name blur"><strong>' + groupName + '</strong></span>';
                                    markup += '<span class="icon"><i class="fa fas fa-sort"></i></span>';
                                markup += '</div>';
                                let sortedTokens = _self.sortTokensByItemOrder(flowPowersTokens);
                                let numSortedTokens = sortedTokens.length;
                                let maxLeftPadding = Math.min((flowValuesSum * groupPaddMod), groupPaddMax);
                                markup += '<div class="flow">';
                                    sortedTokens.forEach((token, index) => {
                                        let name = token.charAt(0).toUpperCase() + token.slice(1);
                                        var value = flowPowersValues[token];
                                        var absGroupValue = Math.abs(value);
                                        var paddingValue = Math.round((absGroupValue / flowValuesSum) * maxLeftPadding);
                                        var isDisabled = (value === 0) ? true : false;
                                        const config = {
                                            token, name, value,
                                            typeClass: ('sort type ' + token),
                                            spanOrder: ['value', 'name'],
                                            blurSpans: ['name'],
                                            extraClasses: (value ? (value > 0 ? 'plus' : 'minus') : ''),
                                            spanPadding: (value !== 0 ? (paddingValue / 2)  : 0),
                                            isDisabled: isDisabled,
                                            hideSpans: (isDisabled ? ['name'] : []),
                                            };
                                        markup += this.generatePowerElement(config);
                                        });
                                markup += '</div>';
                            markup += '</div>';
                            $missionDetails.append(markup);
                            },
                        renderStatPowers: function($missionDetails, statPowersValues){
                            // sort the rank powers by value to display them in order of energy, weapons, attack, defense, speed
                            let statOrder = _self.indexes.statTokens;
                            let markup = '';
                                for (var i = 0; i < statOrder.length; i++){
                                    let token = statOrder[i];
                                    let name = token.charAt(0).toUpperCase() + token.slice(1);
                                    if (!statPowersValues[token]){ continue; }
                                    var values = statPowersValues[token] || { value: 0, boosts: 0, breaks: 0 };
                                    if (!values['value']){ values['value'] = 0; }
                                    //if (values['value'] === 0){ continue; }
                                    let value = values['value'];
                                    var iconClass = false;
                                    var isDisabled = (value === 0) ? true : false;
                                    switch (token){
                                        case 'attack': iconClass = 'sword'; break;
                                        case 'defense': iconClass = 'shield-alt'; break;
                                        case 'speed': iconClass = 'running'; break;
                                        }
                                    const config = {
                                        token, name, value,
                                        iconClass: iconClass,
                                        typeClass: ('stat type ' + token),
                                        blurSpans: ['name', 'value', 'code'],
                                        spanOrder: ['arrows', 'value', 'name', 'code'],
                                        hasArrows: true,
                                        numBoosts: values['boosts'],
                                        numBreaks: values['breaks'],
                                        isDisabled: isDisabled,
                                        hideSpans: (isDisabled ? ['name', 'value'] : []),
                                        };
                                    markup += this.generatePowerElement(config);
                                    }
                            if (!markup.length){ return; }
                            markup = '<div class="void-powers rtl bgo cts stat-powers">' + markup + '</div>';
                            $missionDetails.append(markup);
                            }
                        };
                    // end of voidRecipeWizard.getPowerRenderer()
                    },
                bindEvents: function(){
                    console.log('%c' + 'voidRecipeWizard.bindEvents()', 'color: magenta;');
                    const _self = this;
                    const config = _self.config;

                    // Pull references to objects we'll be binding events to
                    var xrefs = _self.xrefs;
                    $parentDiv = xrefs.parentDiv;
                    $selectionDiv = xrefs.selectionDiv;
                    $paletteDiv = xrefs.paletteDiv;
                    $effectsDiv = xrefs.effectsDiv;
                    $missionTargets = xrefs.missionTargets;
                    $missionDetails = xrefs.missionDetails;
                    $battleField = xrefs.battleField;
                    $itemsPalette = xrefs.itemsPalette;
                    $itemsSelected = xrefs.itemsSelected;
                    $resetButton = xrefs.resetButton;
                    $codeButton = xrefs.codeButton;

                    //console.log('let us check to see the data type of all the above refs');
                    //console.log('-> $parentDiv:', typeof $parentDiv, $parentDiv.length, $parentDiv);
                    //console.log('-> $selectionDiv:', typeof $selectionDiv, $selectionDiv.length, $selectionDiv);
                    //console.log('-> $paletteDiv:', typeof $paletteDiv, $paletteDiv.length, $paletteDiv);
                    //console.log('-> $effectsDiv:', typeof $effectsDiv, $effectsDiv.length, $effectsDiv);
                    //console.log('-> $missionTargets:', typeof $missionTargets, $missionTargets.length, $missionTargets);
                    //console.log('-> $missionDetails:', typeof $missionDetails, $missionDetails.length, $missionDetails);
                    //console.log('-> $battleField:', typeof $battleField, $battleField.length, $battleField);
                    //console.log('-> $itemsPalette:', typeof $itemsPalette, $itemsPalette.length, $itemsPalette);
                    //console.log('-> $itemsSelected:', typeof $itemsSelected, $itemsSelected.length, $itemsSelected);
                    //console.log('-> $resetButton:', typeof $resetButton, $resetButton.length, $resetButton);
                    //console.log('-> $codeButton:', typeof $codeButton, $codeButton.length, $codeButton);

                    // Backup every item's base quantity so we can do dynamic calulations in realt-time
                    $('.item[data-quantity]:not([data-base-quantity])', $parentDiv).each(function(){
                        var $item = $(this);
                        var quantity = parseInt($item.attr('data-quantity'));
                        $item.attr('data-base-quantity', quantity);
                        });

                    // Bind ADD ITEM click events to the palette area's item list buttons
                    let $paletteItems = $('.item[data-token]', $itemsPalette);
                    //console.log('$paletteItems', {typeof: typeof $paletteItems, length: $paletteItems.length, element: $paletteItems});
                    $paletteItems.live('click', function(e){
                        //console.log('palette button clicked! \n-> add-item:', $(this).attr('data-token'));
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
                        //console.log('section button clicked! \n-> remove-item:', $(this).attr('data-token'));
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
                        //console.log('reset button clicked! \n-> reset-items');
                        e.preventDefault();
                        _self.reset();
                        });

                    // Bind ITEM MIX ENTRY click events to the selection area's code button
                    $codeButton.live('click', function(e){
                        //console.log('code button clicked! \n-> parse-item-mix');
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
                    let wrapTokens = [];
                    $('.wrapper[data-step]', $itemsPalette).each(function(){ wrapTokens.push($(this).attr('data-token')); });
                    if (wrapTokens.length < 5){ throw new Error('Invalid number of step wrappers found! [' + wrapTokens.length + '] w/ wrapTokens:', wrapTokens); }
                    let wrapDisplayOrders = {
                        middle: [ wrapTokens[0], wrapTokens[1], wrapTokens[3], wrapTokens[2], wrapTokens[4] ],
                        left: [ wrapTokens[2], wrapTokens[1], wrapTokens[0], wrapTokens[3], wrapTokens[4] ],
                        right: [ wrapTokens[4], wrapTokens[3], wrapTokens[0], wrapTokens[1], wrapTokens[2] ],
                        };
                    $('.wrapper[data-step]', $itemsPalette).live('click', function(e){
                        //console.log('step wrapper clicked! \n-> select-step:', $(this).attr('data-step'));
                        e.preventDefault();
                        let $wrap = $(this), $siblings = $wrap.siblings(), $parent = $wrap.parent();
                        var stepNum = parseInt($wrap.attr('data-step')), stepSide = $wrap.attr('data-side'), stepLayer = 1;
                        $itemsPalette.attr('data-step', stepNum);
                        $siblings.removeClass('active').attr('data-layer', 0);
                        $wrap.addClass('active').attr('data-layer', stepLayer++);
                        var displayOrder = wrapDisplayOrders[stepSide] || [];
                        for (var i = 0; i < displayOrder.length; i++){
                            let $wrap = $siblings.filter('.wrapper[data-token="' + displayOrder[i] + '"]');
                            if (!$wrap.length){ continue; }
                            $wrap.attr('data-layer', stepLayer++);
                            }
                        });

                    // Check to see if there is already a recipe in the URL hash
                    window.addEventListener('load', () => {
                        if (_self.hashUpdatedByApp){ return; }
                        //console.log('%c' + 'window.load() triggered!', 'color: orange;');
                        const params = _self.getHashParams();
                        if (!Object.keys(params).length){ return; }
                        if (!params.mix || !params.mix.length){ return; }
                        //console.log('-> OnLoad || Mix parameters found:', params.mix);
                        _self.parseItemMix(params.mix);
                        });
                    window.addEventListener('hashchange', () => {
                        if (_self.hashUpdatedByApp){ return; }
                        //console.log('%c' + 'window.hashchange() triggered!', 'color: orange;');
                        const params = _self.getHashParams();
                        if (!Object.keys(params).length){ return; }
                        if (!params.mix || !params.mix.length){ return; }
                        //console.log('OnHashChange || Mix parameters found:', params.mix);
                        _self.parseItemMix(params.mix);
                        });

                    // TEMP TEMP TEMP
                    // DEBUG DEBUG DEBUG
                    // Make it so clicking the titlebar prints the current void powers to the console
                    $('#vcr_title', $parentDiv).live('click', function(){
                        _self.showDebug('powers');
                        });
                    // DEBUG DEBUG DEBUG
                    // TEMP TEMP TEMP

                    // end of voidRecipeWizard.bindEvents()
                    },
                addItem: function(item, refresh){
                    console.log('%c' + 'voidRecipeWizard.addItem() w/ ' + item.token, 'color: magenta;');
                    //console.log('-> w/ item:', item);
                    const _self = this;
                    const config = _self.config;
                    var token = item.token;
                    var quantity = item.quantity || 1;
                    var existing = Object.keys(_self.items).length;
                    var exists = Object.keys(_self.items).indexOf(token) >= 0;
                    if (!exists && existing >= config.maxItems){ return; }
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

                    // Check to see if this fits into specific kind-based categories
                    var itemIsScrew = itemSuffix === 'screw';
                    var itemIsCore = itemSuffix === 'core';
                    var itemIsShard = itemSuffix === 'shard';
                    var itemIsPellet = itemSuffix === 'pellet';
                    var itemIsCapsule = itemSuffix === 'capsule';
                    var itemIsTank = itemSuffix === 'tank';
                    var itemIsUpgrade = itemSuffix === 'upgrade';
                    var itemIsBooster = itemSuffix === 'booster';
                    var itemIsDiverter = itemSuffix === 'diverter';
                    var itemIsCircuit = itemSuffix === 'circuit';
                    var itemIsModule = itemSuffix === 'module';

                    // Check to see if this item fits into any specific stat-based categories
                    var itemIsEnergy = itemPrefix === 'energy';
                    var itemIsWeapons = itemPrefix === 'weapon';
                    var itemIsAttack = itemPrefix === 'attack';
                    var itemIsDefense = itemPrefix === 'defense';
                    var itemIsSpeed = itemPrefix === 'speed';
                    var itemIsSuper = itemPrefix === 'super';

                    // Check to see if this item fits into any specific purpose-based categories
                    var itemGivesQuanta = itemIsScrew ? true : false;
                    var itemModsQuanta = ['charge-module'].indexOf(itemToken) !== -1;
                    var itemGivesSpread = itemIsCore ? true : false;
                    var itemModsSpread = ['spreader-module', 'target-module'].indexOf(itemToken) !== -1;
                    var itemGivesLevel = itemIsEnergy && (itemIsPellet || itemIsCapsule || itemIsTank) ? true : false;
                    var itemModsLevel = ['energy-upgrade'].indexOf(itemToken) !== -1; // itemIsEnergy && itemIsUpgrade;
                    var itemGivesForte = itemIsWeapons && (itemIsPellet || itemIsCapsule || itemIsTank) ? true : false;
                    var itemModsForte = ['weapon-upgrade'].indexOf(itemToken) !== -1; // itemIsWeapons && itemIsUpgrade;
                    var itemGivesRankStats = (itemGivesLevel || itemGivesForte);
                    var itemModsRankStats = (itemModsLevel || itemModsForte);
                    var itemGivesTriStats = (itemIsAttack || itemIsDefense || itemIsSpeed || itemIsSuper);
                    var itemModsTriStats = (itemGivesTriStats && itemIsBooster) || (itemGivesTriStats && itemIsDiverter);
                    var itemRotatesQueues = itemSuffix === 'rotator';
                    if (itemToken === 'mecha-whistle'){ itemRotatesQueues = true; }
                    if (itemToken === 'extra-life'){ itemRotatesQueues = true; }
                    if (itemToken === 'yashichi'){ itemRotatesQueues = true; }

                    // Increase the delta by one, always, for each item added
                    powers.incPower('delta', 1 * quantity);

                    // Check to see which group the item belongs to and then parse its values

                    /* QUANTA ITEMS & MODULES */

                    // ELEMENTAL CORES (NATURE, FLAME, WATER, etc.)
                    // Effects: +FLOW
                    if (itemIsCore){
                        var typeToken = itemPrefix;
                        var typeValue = 1.0;
                        powers.incFlow(typeToken, typeValue * quantity);
                        if (powers.getPower('quanta') < 1){ powers.setPower('quanta', 1); }
                        if (powers.getPower('spread') < 1){ powers.setPower('spread', 1); }
                        return;
                        }

                    // METAL SCREWS (SMALL, LARGE, HYPER)
                    // Effects: +QUANTA
                    if (itemIsScrew){
                        var itemIsSmall = itemPrefix === 'small';
                        var itemIsLarge = itemPrefix === 'large';
                        var itemIsHyper = itemPrefix === 'hyper';
                        var quantaValue = 1.0;
                        if (itemIsSmall){ quantaValue = 5.0; }
                        else if (itemIsLarge){ quantaValue = 10.0; }
                        else if (itemIsHyper){ quantaValue = 100.0; }
                        powers.incPower('quanta', quantaValue * quantity);
                        if (powers.getPower('spread') < 1){ powers.setPower('spread', 1); }
                        return;
                        }

                    // CHARGE MODULE
                    // Effects: xQUANTA
                    if (itemIsModule && itemPrefix === 'charge'){
                        var quantaBoost = Math.ceil(powers.getPower('quanta') * 0.5);
                        powers.incPower('quanta', quantaBoost * quantity);
                        return;
                        }

                    // SPREADER MODULE
                    // Effects: +SPREAD
                    if (itemIsModule && itemPrefix === 'spreader'){
                        var spreadBoost = 1;
                        powers.incPower('spread', spreadBoost * quantity);
                        return;
                        }

                    // TARGET MODULE
                    // Effects: +FOCUS
                    if (itemIsModule && itemPrefix === 'target'){
                        var focusBoost = 1;
                        powers.incPower('focus', focusBoost * quantity);
                        return;
                        }

                    /* RANK-STAT ITEMS & MODULES */

                    // -- RANK-STAT ITEMS (ENERGY[LEVEL], WEAPONS[FORTE]) --
                    if (itemGivesRankStats || itemModsRankStats){

                        // Collect the stat token and base value
                        var statToken = itemIsEnergy ? 'level' : 'forte';
                        var statBase = 1 * quantity;

                        // LEVEL INCREASERS (ENERGY PELLET, CAPSULE, TANK, UPGRADE)
                        // Effects: +LEVEL/MAX && +STAT-FLOW
                        if (itemIsEnergy){
                            if (itemIsPellet || itemIsCapsule || itemIsTank){
                                var powerBoost = statBase * ((itemIsPellet ? 1 : 0) + (itemIsCapsule ? 5 : 0) + (itemIsTank ? 10 : 0));
                                powers.incPower(statToken, powerBoost);
                                return;
                                }
                            else if (itemIsUpgrade){
                                var powerBoost = statBase * (100);
                                powers.incPower(statToken+'Max', powerBoost);
                                return;
                                }
                            }

                        // FORTE INCREASERS (WEAPON PELLET, CAPSULE, TANK, UPGRADE)
                        // Effects: +FORTE/MAX && +STAT-FLOW
                        if (itemIsWeapons){
                            if (itemIsPellet || itemIsCapsule || itemIsTank){
                                var powerBoost = statBase * ((itemIsPellet ? 1 : 0) + (itemIsCapsule ? 3 : 0) + (itemIsTank ? 5 : 0));
                                powers.incPower(statToken, powerBoost);
                                return;
                                }
                            else if (itemIsUpgrade){
                                var powerBoost = statBase * (10);
                                powers.incPower(statToken+'Max', powerBoost);
                                return;
                                }
                            }

                        }

                    /* TRI-STAT ITEMS & MODULES */

                    // -- TRI-STAT ITEMS (ATTACK, DEFENSE, SPEED) --
                    if (itemGivesTriStats || itemModsTriStats){

                        // Collect the stat token and base value
                        var statToken = itemPrefix;
                        var statBase = 1 * quantity;

                        // STAT INCREASERS (PELLETS, CAPSULES, BOOSTERS)
                        // Effects: +STATS && +STAT-FLOW
                        if (itemIsPellet
                            || itemIsCapsule
                            || itemIsBooster){
                            var powerBoost = statBase * ((itemIsPellet ? 1 : 0) + (itemIsCapsule ? 2 : 0) + (itemIsBooster ? 3 : 0));
                            if (itemIsAttack || itemIsDefense || itemIsSpeed){
                                powers.incPower(statToken, powerBoost);
                                return;
                                }
                            else if (itemIsSuper){
                                var superTokens = ['attack', 'defense', 'speed'];
                                powerBoost /= superTokens.length;
                                for (var i = 0; i < superTokens.length; i++){
                                    var superToken = superTokens[i];
                                    powers.incPower(superToken, Math.floor(powerBoost));
                                    }
                                return;
                                }
                            }

                        // STAT MODIFIERS (DIVERTERS)
                        // Effects: ~STATS && ~STAT-FLOW
                        if (itemIsDiverter){
                            var divertFrom = statToken, divertTo = [], divertToNum = 0;
                            if (statToken !== 'attack'){ divertTo.push('attack'); }
                            if (statToken !== 'defense'){ divertTo.push('defense'); }
                            if (statToken !== 'speed'){ divertTo.push('speed'); }
                            divertToNum = divertTo.length;
                            var divertAmount = Math.round(statBase * divertToNum), receiveAmount = Math.round(divertAmount / divertToNum);
                            var flowBreak = divertAmount * 3, powerBreak = divertAmount * 1;
                            powers.decFlow(divertFrom, flowBreak);
                            powers.decPower(divertFrom, powerBreak);
                            for (var i = 0; i < divertTo.length; i++){
                                var divertToToken = divertTo[i];
                                var powerBoost = receiveAmount * 1;
                                powers.incPower(divertToToken, powerBoost);
                                }
                            return;
                            }

                        }

                    // Otherwise, this item is undefined
                    return;

                    /*
                    // UNDEFINED ITEM [SKIP]
                    if (itemToken === ''){
                        //return;
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
                        if (itemPrefix === 'growth'){
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
                    else if (itemRotatesQueues){
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
                        else if (itemPrefix === 'field'){
                            // field boost is special and also boosts shift power
                            var fieldPower = Math.floor(quantity / 10); //quantity > 0 ? (Math.floor(quantity / 10) + 1) : 0;
                            var shiftPower = quantity * 1;
                            powers.incPower('field', fieldPower);
                            powers.incPower('shift', shiftPower);
                            }
                        }
                    */

                    // end of voidRecipeWizard.parseItem()
                    },
                parseItemMix: function(mix){
                    console.log('%c' + 'voidRecipeWizard.parseItemMix()', 'color: magenta;');
                    //console.log('-> w/ ' + mix, 'color: magenta;');
                    if (typeof mix !== 'string'){ console.warn('-> mix is not a string!'); return; }
                    else if (!mix.length){ console.warn('-> mix is an empty string!'); return; }
                    else if (mix === '-'){ return; }

                    // Backup a reference to the parent object
                    const _self = this;
                    const config = _self.config;

                    // If this mix is already active, do nothing here
                    console.log('-> mix:', mix, '\n' + '-> currentItemMix:', config.currentItemMix);
                    if (mix === config.currentItemMix){ return; }

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
            filterFlowPowers: function(flows, sort){
                sort = typeof sort === 'undefined' ? true : sort;
                console.log('%c' + 'voidRecipeWizard.filterFlowPowers()', 'color: magenta;');
                //console.log('-> w/ flows:', flows, 'sort:', sort);
                // parse out flows that represent types and then order them highest first
                const _self = this;
                var voidItems = _self.items;
                var voidItemsTokens = Object.keys(voidItems);
                //console.log('=> voidItemsTokens:', voidItemsTokens);
                var mmrpgTypes = _self.indexes.typeTokens;
                var sortFlows = {};
                for (var i = 0; i < mmrpgTypes.length; i++){
                    var typeToken = mmrpgTypes[i];
                    var typeValue = flows[typeToken] || 0;
                    if (typeValue !== 0){ sortFlows[typeToken] = typeValue; }
                    }
                //console.log('=> sortFlows:', sortFlows);
                if (!sort){ return sortFlows; }
                // re-sort the sort flows based on their values w/ highest first
                var sortFlowTokens = Object.keys(sortFlows);
                sortFlowTokens.sort(function(a, b){
                    return sortFlows[b] - sortFlows[a];
                    });
                var sortFlowTokensSorted = sortFlowTokens.slice().sort(function(a, b){
                    var aVal = sortFlows[a];
                    var bVal = sortFlows[b];
                    var aIndex = voidItemsTokens.indexOf(a+'-core');
                    var bIndex = voidItemsTokens.indexOf(b+'-core');
                    if (aVal > bVal){ return -1; }
                    if (aVal < bVal){ return 1; }
                    if (aIndex < bIndex){ return -1; }
                    if (aIndex > bIndex){ return 1; }
                    return 0;
                    });
                //console.log('-> sortFlowTokens:', sortFlowTokens);
                //console.log('-> sortFlowTokensSorted:', sortFlowTokensSorted);
                var sortedSortPowers = {};
                for (var i = 0; i < sortFlowTokensSorted.length; i++){
                    var sortToken = sortFlowTokensSorted[i];
                    var sortValue = sortFlows[sortToken];
                    sortedSortPowers[sortToken] = sortValue;
                    }
                //console.log('=> sortedSortPowers:', sortedSortPowers);
                return sortedSortPowers;
                // end of voidRecipeWizard.filterFlowPowers()
                },
                sortTokensByItemOrder: function(unsortedTokens){
                    console.log('%c' + 'voidRecipeWizard.sortTokensByItemOrder()', 'color: magenta;');
                    //console.log('-> w/ unsortedTokens:', unsortedTokens);
                    const _self = this;
                    let voidItemListString = Object.keys(_self.items).join('|');
                    let sortedTokens = Object.values(unsortedTokens);
                    sortedTokens.sort((a, b) => voidItemListString.indexOf(a) - voidItemListString.indexOf(b));
                    return sortedTokens;
                    // end of voidRecipeWizard.sortTokensByItemOrder()
                    },
                generateTargetQueue: function(robots, types, stats){
                    console.log('%c' + 'voidRecipeWizard.generateTargetQueue()', 'color: magenta;');
                    //console.log('-> w/ robots:', robots, 'types:', types, 'stats:', stats);
                    // Collect important refs and indexes for processing
                    const _self = this;
                    const mmrpgIndexRobots = mmrpgIndex.robots;
                    const mmrpgIndexRobotsTokens = Object.keys(mmrpgIndexRobots);
                    var typeFlows = types;
                    var statFlows = stats;
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
                    const config = _self.config;

                    // Collect a reference to the void values object and reset
                    var voidItems = _self.items;
                    var voidItemsTokens = Object.keys(voidItems);


                    // Update the current mix string with whatever we have added
                    config.currentItemMix = _self.getMixString();

                    // Define a variable to hold the calculated powers of all the items
                    var voidPowers = {};
                    voidPowers.powers = {};
                    voidPowers.flows = {};
                    voidPowers.flags = {};
                    voidPowers.getPowers = function(){ return voidPowers.powers; };
                    voidPowers.getPower = function(token, fallback){ return voidPowers.powers[token] || fallback || 0; };
                    voidPowers.setPower = function(token, value){ voidPowers.powers[token] = Math.round(value * 100) / 100; };
                    voidPowers.incPower = function(token, value){ voidPowers.setPower(token, voidPowers.getPower(token) + value); };
                    voidPowers.decPower = function(token, value){ voidPowers.setPower(token, voidPowers.getPower(token) - value); };
                    voidPowers.modPower = function(token, value, fallback){ voidPowers.setPower(token, voidPowers.getPower(token, fallback) * value); };
                    voidPowers.getFlows = function(){ return voidPowers.flows; };
                    voidPowers.getFlow = function(token, fallback){ return voidPowers.flows[token] || fallback || 0; };
                    voidPowers.setFlow = function(token, value){ voidPowers.flows[token] = Math.round(value * 100) / 100; };
                    voidPowers.incFlow = function(token, value){ voidPowers.setFlow(token, voidPowers.getFlow(token) + value); };
                    voidPowers.decFlow = function(token, value){ voidPowers.setFlow(token, voidPowers.getFlow(token) - value); };
                    voidPowers.modFlow = function(token, value, fallback){ voidPowers.setFlow(token, voidPowers.getFlow(token, fallback) * value); };
                    voidPowers.resetAll = function(){
                        voidPowers.powers = {};
                        voidPowers.flows = {};
                        voidPowers.flags = {};
                        };
                    voidPowers.powers.delta = 0;
                    voidPowers.powers.quanta = 0;
                    voidPowers.powers.spread = 0;
                    voidPowers.powers.focus = 0;
                    voidPowers.powers.level = 0;
                    voidPowers.powers.forte = 0;
                    voidPowers.powers.effort = 0;
                    voidPowers.powers.reward = 0;
                    voidPowers.powers.levelMax = 100;
                    voidPowers.powers.forteMax = 10;
                    voidPowers.flags.guard = false;
                    voidPowers.flags.reverse = false;
                    voidPowers.flags.extreme = false;

                    // As long as there are items present, we can pre-boost certain base and rank powers to one
                    if (voidItemsTokens.length){
                        voidPowers.powers.quanta = 1;
                        voidPowers.powers.spread = 1;
                        voidPowers.powers.level = 1;
                        voidPowers.powers.forte = 1;
                        }

                    // Loop through all the items, one-by-one, and parse their intrinsic values
                    for (var i = 0; i < voidItemsTokens.length; i++){
                        var itemToken = voidItemsTokens[i];
                        var itemQuantity = voidItems[itemToken];
                        _self.parseItem({token: itemToken}, itemQuantity, voidPowers);
                        }

                    // As long as items are present, we should make keep certain values in scope
                    if (voidItemsTokens.length){
                        // Ensure certain values (quanta, spread, etc.) are rounded-up to one if they're above zero
                        // but otherwise rounded-down to prevent min-values from stacking up and overflowing
                        let roundedWithCare = ['quanta', 'spread', 'level', 'forte'];
                        let roundedWithMaxes = {'level': 999, 'forte': 99};
                        for (var i = 0; i < roundedWithCare.length; i++){
                            var powerToken = roundedWithCare[i];
                            var powerValue = voidPowers.powers[powerToken] || 0;
                            if (powerValue <= 0){ powerValue = 0; }
                            else if (powerValue > 0 && powerValue < 1){ powerValue = 1; }
                            else { powerValue = Math.floor(powerValue); }
                            voidPowers.powers[powerToken] = powerValue;
                            if (roundedWithMaxes[powerToken]){
                                var maxPowerToken = powerToken + 'Max';
                                var maxPowerValue = voidPowers.powers[maxPowerToken] || 0;
                                var maxPowerLimit = roundedWithMaxes[powerToken];
                                if (maxPowerValue <= 0){ maxPowerValue = 0; }
                                else if (maxPowerValue > 0 && maxPowerValue < 1){ maxPowerValue = 1; }
                                else if (maxPowerValue > maxPowerLimit){ maxPowerValue = maxPowerLimit; }
                                else { maxPowerValue = Math.floor(maxPowerValue); }
                                voidPowers.powers[maxPowerToken] = maxPowerValue;
                                if (powerValue > maxPowerValue){
                                    powerValue = maxPowerValue;
                                    voidPowers.powers[powerToken] = powerValue;
                                    }
                                }
                            }
                        }

                    // Make sure the spread never goes above max values
                    var maxSpreadPower = config.maxTargets;
                    if (voidPowers.powers.spread > maxSpreadPower){ voidPowers.powers.spread = maxSpreadPower; }

                    // Always copy the calculated powers/flows to the parent so they're easily accessible
                    //console.log('voidPowers and voidFlows have been updated!');
                    _self.powers = {};
                    var voidPowersList = voidPowers.getPowers();
                    var voidPowerKeys = Object.keys(voidPowersList);
                    var voidPowersRequired = config.voidPowersRequired;
                    for (var i = 0; i < voidPowerKeys.length; i++){
                        var powerToken = voidPowerKeys[i];
                        var powerValue = voidPowersList[powerToken];
                        if (powerValue === 0 && voidPowersRequired.indexOf(powerToken) === -1){ continue; }
                        _self.powers[powerToken] = powerValue;
                        //console.log('-> voidPowers.' + powerToken + ' =', powerValue);
                        }
                    _self.flows = {};
                    var voidFlowsList = voidPowers.getFlows();
                    var voidFlowKeys = Object.keys(voidFlowsList);
                    var voidFlowsRequired = []; //_self.voidFlowsRequired;
                    for (var i = 0; i < voidFlowKeys.length; i++){
                        var flowToken = voidFlowKeys[i];
                        var flowValue = voidFlowsList[flowToken];
                        if (flowValue === 0 && voidFlowsRequired.indexOf(flowToken) === -1){ continue; }
                        _self.flows[flowToken] = flowValue;
                        //console.log('-> voidFlows.' + flowToken + ' =', flowValue);
                        }

                    // end of voidRecipeWizard.calculatePowers()
                    },
                generateMission: function(){
                    console.log('%c' + 'voidRecipeWizard.generateMission()', 'color: magenta;');

                    // Backup a reference to the parent object
                    const _self = this;
                    const config = _self.config;

                    // Clear the existing mission if one is already there
                    _self.mission = {};

                    // Collect reference to the void items + powers so we can reference them
                    var voidItemsTokens = Object.keys(_self.items);
                    var voidPowersList = _self.powers;
                    var voidFlowsList = _self.flows;
                    var voidPowersKeys = Object.keys(voidPowersList);
                    var voidFlowsKeys = Object.keys(voidFlowsList);

                    // If we don't have any powers, we can't generate anything
                    if (!voidPowersKeys.length){
                        console.log('%c' + '-> no powers to generate from!', 'color: orange;');
                        return;
                        }

                    // Collect tier information so we can assign queues based on how much quanta
                    const indexes = _self.indexes;
                    const voidTiers = indexes.voidTiers;
                    const voidTierTypes = indexes.voidTierTypes;
                    const voidTierValues = indexes.voidTierValues;
                    const voidTierRanks = indexes.voidTierRanks;
                    console.log('-> voidTiers:', voidTiers, '\n' + '-> voidTierValues:', voidTierValues, '\n' + '-> voidTierRanks:', voidTierRanks);

                    // Collect the base amounts of quanta and spread for later reference
                    var baseQuanta = voidPowersList['quanta'] || 0;
                    var baseSpread = voidPowersList['spread'] || 0;
                    //console.log('-> baseQuanta:', baseQuanta, 'baseSpread:', baseSpread);

                    // If we have neither quanta material nor a defined spread limit, we can't generate either
                    if (baseQuanta < 1 && baseSpread < 1){
                        //console.log('%c' + '-> no quanta materia nor spread limit to generate from!', 'color: red;');
                        return;
                        }

                    // Pull a filtered list of stat powers and type powers for easier looping
                    var statPowersList = _self.filterStatPowers(voidPowersList);
                    var typeFlowsList = _self.filterFlowPowers(voidFlowsList);
                    //console.log('-> statPowersList:', statPowersList);
                    //console.log('-> typeFlowsList:', typeFlowsList);

                    // Pre-calculate the effective quanta and spread we're working with
                    let effectiveQuanta = baseQuanta;
                    let effectiveSpread = baseSpread >= config.maxTargets ? config.maxTargets : (baseSpread < 1 ? 1 : Math.trunc(baseSpread));
                    let effectiveOffset = 0;
                    console.log('-> effectiveQuanta:', effectiveQuanta, '\n' + '-> effectiveSpread:', effectiveSpread, '\n' + '-> effectiveOffset:', effectiveOffset);

                    // Define variables to hold the slot templates with distributed quanta material and elemental energy
                    let numTargetSlots = 0;
                    let quantaAvailable = 0;
                    let typeFlowAvailable = {};
                    let typeFlowRemaining = {};
                    let typeFlowPriority = [];
                    let targetSlotTemplates = [];
                    let battleFieldConfig = {};
                    (function(quantaPower, spreadPower, typePowers){
                        console.log('%c' + 'voidRecipeWizard.generateMission.targetSlotTemplates(~)', 'color: green;');

                        // Collect the number of slots and quanta available from effective values
                        quantaAvailable = quantaPower;
                        numTargetSlots = spreadPower;
                        console.log('-> quantaAvailable:', quantaAvailable);
                        console.log('-> numTargetSlots:', numTargetSlots);

                        // Collect the elemental types available and sort them by priority (we already have quanta from above)
                        typeFlowAvailable = Object.assign({}, typePowers);
                        typeFlowRemaining = Object.assign({}, typePowers);
                        typeFlowPriority = Object.keys(typeFlowAvailable).slice().sort(function(a, b){
                            var aIndex = voidItemsTokens.indexOf(a+'-core');
                            var bIndex = voidItemsTokens.indexOf(b+'-core');
                            return aIndex - bIndex;
                            });
                        console.log('-> typeFlowAvailable:', typeFlowAvailable);
                        console.log('-> typeFlowRemaining:', typeFlowRemaining);
                        console.log('-> typeFlowPriority:', typeFlowPriority);

                        // VOID POWER: Check for focus power and use it to calculate the shift from bench to active
                        let focusPowerValue = voidPowersList['focus'] || 0;
                        let focusPercentMax = 90, focusPercentPower = 5, focusPercentValue = 0;
                        let quantaShiftLimit = (quantaAvailable - (numTargetSlots - 1));
                        let quantaShiftAmount = 0, quantaShiftDirection = '';
                        if (focusPowerValue > 0){
                            console.log('%c' + '-> Oh! The `focus` voidPower was detected!', 'color: #ff9800;');
                            focusPercentValue = Math.min(100, Math.max(0, (focusPowerValue * focusPercentPower)));
                            quantaShiftAmount = Math.floor(quantaAvailable * (focusPercentValue / 100));
                            quantaShiftDirection = 'active';
                            console.log('--> VOID POWER [FOCUS]:', '\n' + '-> w/ focusPowerValue:', focusPowerValue, '\n' + '-> focusPercentValue:', focusPercentValue, '\n' + '-> quantaShiftAmount:', quantaShiftAmount, '\n' + '-> quantaShiftDirection:', quantaShiftDirection);
                            }

                        // Loop through the target slots and assign quanta and elemental types to each
                        for (var i = 0; i < numTargetSlots; i++){
                            console.log('%c' + '--> generating slotTemplate for [i='+i+'] w/ [numTargetSlots:'+numTargetSlots+']', 'color: lime;');

                            // Define the key and position for later
                            let targetKey = i;
                            let targetPosition = targetKey === 0 ? 'active' : 'bench';

                            // Create a new template object for the current slot so we can assign it the quanta and type
                            let slotTemplate = {
                                type: '',
                                tier: 0,
                                level: 0,
                                forte: 0,
                                quanta: 0,
                                queue: [],
                                };

                            // If quanta is available, take an equal portion unless there are special effects at play
                            let targetQuanta = 0, shiftedQuanta = 0;
                            if (quantaAvailable > 0){
                                targetQuanta = Math.floor(quantaAvailable / (numTargetSlots - i));
                                quantaAvailable -= targetQuanta;
                                slotTemplate.quanta = targetQuanta;
                                // If a shift amount was defined and this target's position was a benefactor,
                                // apply that shift amount to the target's quanta and reduce the available quanta
                                //
                                if (quantaShiftAmount > 0){
                                    if (quantaShiftDirection === targetPosition){
                                        targetQuanta += quantaShiftAmount;
                                        quantaAvailable -= quantaShiftAmount;
                                        slotTemplate.quanta = targetQuanta;
                                        shiftedQuanta = quantaShiftAmount;
                                        } else {
                                        shiftedQuanta = -1 * Math.floor(quantaShiftAmount / (numTargetSlots - 1));
                                        }
                                    }
                                }
                            console.log('-> targetQuanta:', targetQuanta, 'shiftedQuanta: ~', shiftedQuanta);

                            // Loop through available elemental types in priority order and assign slots first-come-first-serve
                            let targetType = '';
                            if (typeFlowPriority.length > 0){
                                for (var j = 0; j < typeFlowPriority.length; j++){
                                    // TODO: add special void power effects here (?)
                                    let typeToken = typeFlowPriority[j];
                                    let typeValue = typeFlowAvailable[typeToken];
                                    if (typeValue === 0){ continue; }
                                    targetType = typeToken;
                                    typeFlowRemaining[targetType] -= 1;
                                    slotTemplate.type = targetType;
                                    break;
                                    }
                                }
                            console.log('-> targetType:', targetType);
                            console.log('-> typeFlow['+targetType+'](Remaining/Available):', typeFlowRemaining[targetType] + '/' + typeFlowAvailable[targetType]);

                            // Generate a robot-queue given available quanta, type, and defined void tiers
                            let targetQueueType = targetType || 'empty';
                            let targetRobotQueue = [];
                            if (voidTiers[targetQueueType]){
                                // TODO: add special void power effects here (?)
                                let tierInfo = voidTiers[targetQueueType] || {};
                                let tierQueues = tierInfo.queues || {};
                                let tierThresholds = tierInfo.thresholds || [];
                                for (var j = 0; j < tierThresholds.length; j++){
                                    let thresholdValue = tierThresholds[j];
                                    if (slotTemplate.quanta < thresholdValue){ continue; }
                                    if (!tierQueues[thresholdValue]){ continue; }
                                    targetRobotQueue = Object.values(tierQueues[thresholdValue]);
                                    slotTemplate.queue = targetRobotQueue;
                                    break;
                                    }
                                }
                            console.log('-> targetQueueType:', targetQueueType);
                            console.log('-> targetRobotQueue:', targetRobotQueue);

                            // As long as this target has at least one quanta, set start level and forte values
                            if (slotTemplate.quanta > 0){
                                slotTemplate.level = 1;
                                slotTemplate.forte = 1;
                                }

                            // Assign the quanta and type to the current slot
                            targetSlotTemplates.push(slotTemplate);

                            }

                        // Use the remaining type flow energy, if any, to update the battle field
                        if (typeFlowRemaining){
                            for (var j = 0; j < typeFlowPriority.length; j++){
                                let typeToken = typeFlowPriority[j];
                                let typeValue = typeFlowRemaining[typeToken];
                                if (typeValue === 0){ continue; }
                                battleFieldConfig.type = typeToken;
                                break;
                                }
                            }

                        // We're done populating the array so we can return from this scop
                        console.log('-> targetSlotTemplates:', targetSlotTemplates);
                        console.log('-> battleFieldConfig:', battleFieldConfig);
                        return;

                        })(effectiveQuanta, effectiveSpread, typeFlowsList);

                    // Use calculated quanta-per-target to set-up the different target slots
                    let missionTargets = [];
                    let numTargets = numTargetSlots;
                    if (numTargets > 0){
                        console.log('%c' + 'voidRecipeWizard.generateMission.targetSlotTemplates(~)', 'color: green;');

                        // Loop through the target slots templates w/ queues and use them to generate actual mission targets
                        for (var slotKey = 0; slotKey < numTargets; slotKey++){
                            var slotTemplate = targetSlotTemplates[slotKey];
                            console.log('--> calculating slotKey:', slotKey, 'w/ slotTemplate:', slotTemplate);
                            let targetRobot = {};
                            var targetType = slotTemplate.type;
                            var targetQuanta = slotTemplate.quanta;
                            var targetQueue = slotTemplate.queue;
                            targetRobot.token = '';
                            targetRobot.class = '';
                            targetRobot.level = 1;
                            targetRobot.type = targetType;
                            targetRobot.quanta = targetQuanta;
                            // If there's at least one token in the queue, collect the target token
                            if (targetQueue.length){
                                var nextRobotToken = targetQueue[0];
                                var nextRobotInfo = mmrpgIndex.robots[nextRobotToken];
                                var nextRobotClass = nextRobotInfo.robot_class;
                                targetRobot.class = nextRobotClass;
                                targetRobot.token = nextRobotToken;
                                }
                            // If a token for this slot count not be found, default to a dark frag
                            if (!targetRobot.token.length){
                                targetRobot.token = 'dark-frag';
                                }
                            // Add the target robot to the mission targets list
                            missionTargets.push(targetRobot);
                            //console.log('--> pushed new target!', '\n-> targetRobot:', targetRobot);
                            }

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
                    const config = _self.config;

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
                        config.currentItemMix = thisMixString;
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
                    const config = _self.config;

                    // Collect reference to relevant void elements and values
                    var $upperDeck = _self.xrefs.upperDeck;
                    var $itemsSelected = _self.xrefs.itemsSelected;
                    var $itemsPalette = _self.xrefs.itemsPalette;
                    var $resetButton = _self.xrefs.resetButton;
                    var $codeButton = _self.xrefs.codeButton;
                    var $missionDetails = _self.xrefs.missionDetails;
                    var $targetList = _self.xrefs.missionTargets;
                    var $battleField = _self.xrefs.battleField;

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

                    // Remove any existing loaders before we add a new one
                    _self.hasContent(false);
                    $upperDeck.find('.loading').remove();

                    // Remove any mission details, targets, battle fields, etc. before we add new ones
                    $missionDetails.html('');
                    $targetList.html('');
                    //$battleField.html(''); /* TODO: don't clear field until we can regenerate */

                    // Clear the item selection area and then rebuild it with the new items
                    var $selectedWrapper = $('.wrapper', $itemsSelected);
                    var $paletteWrappers = $('.wrapper', $itemsPalette);
                    var $paletteItems = $('.item[data-token]', $itemsPalette);
                    var numSlotsAvailable = config.maxItems;
                    var numSlotsUsed = voidItemsTokens.length;
                    $selectedWrapper.html('');
                    $paletteItems.removeClass('active');
                    if (voidItemsTokens.length > 0){
                        const mmrpgItems = mmrpgIndex.items;
                        for (var i = 0; i < voidItemsTokens.length; i++){
                            // Generate the markup for the item then add to the selection area
                            var itemToken = voidItemsTokens[i];
                            var itemInfo = mmrpgItems[itemToken];
                            var itemName = itemInfo.item_name;
                            var itemNameBr = itemName.replace(' ', '<br />');
                            var itemQuantity = voidItems[itemToken] || 0;
                            var itemImage = itemInfo.item_image || itemToken;
                            var itemClass = 'item' + (itemToken === lastItemToken ? ' recent' : '');
                            var itemIcon = '/images/items/'+itemImage+'/icon_right_40x40.png?' + gameSettings.cacheDate;
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
                        const mmrpgItems = mmrpgIndex.items;
                        for (var i = 0; i < itemsToUpdate.length; i++){
                            var itemToken = itemsToUpdate[i];
                            var itemInfo = mmrpgItems[itemToken];
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

                    // Collect the list of void powers and keys so we can re-sort in the next step
                    var voidPowers = _self.powers;
                    var voidPowersKeys = Object.keys(voidPowers);
                    var voidPowersValSum = 0 + (voidPowersKeys.length ? (function(){ var sum = 0; for (var i = 0; i < voidPowersKeys.length; i++){ var key = voidPowersKeys[i]; if (key.substr(-3, 3) === 'Max'){ continue; } sum += voidPowers[key]; } return sum; })() : 0);
                    if (voidPowersValSum === 0){
                        //console.log('%c' + '-> we have NO powers to generate content from!', 'color: amber;');
                        $upperDeck.append('<span class="loading">&hellip;</span>');
                        //return;
                        } else {
                        //console.log('%c' + '-> we DO have powers to generate content from!', 'color: amber;');
                        _self.hasContent(true);
                        }
                    //console.log('voidPowersValSum:', voidPowersValSum);
                    //console.log('voidPowersKeys(raw):', '\n-> [' + voidPowersKeys.join(', ') + ']');

                    // Also collect the list of void flows and keys so we can re-sort in the next step
                    var voidFlows = _self.flows;
                    var voidFlowsKeys = Object.keys(voidFlows);
                    var voidFlowsValSum = 0 + (voidFlowsKeys.length ? (function(){ var sum = 0; for (var i = 0; i < voidFlowsKeys.length; i++){ var key = voidFlowsKeys[i]; if (key.substr(-3, 3) === 'Max'){ continue; } sum += voidFlows[key]; } return sum; })() : 0);
                    //console.log('voidFlowsValSum:', voidFlowsValSum);
                    //console.log('voidFlowsKeys(raw):', '\n-> [' + voidFlowsKeys.join(', ') + ']');

                    // First, sort the power/flow tokens by their values going highest to lowest,
                    // then sort all the keys pertaining to stats first, all keys pertaining to
                    // elemental types second, and anything else can come after that at the end
                    var statTokens = mmrpgStats;
                    var typeTokens = mmrpgTypes;
                    var tempValueSort = function(kind){
                        let values = {};
                        if (kind === 'powers'){ values = voidPowers; }
                        if (kind === 'flows'){ values = voidFlows; }
                        return function(a, b){
                            var aPower = values[a] || 0;
                            var bPower = values[b] || 0;
                            if (aPower > bPower){ return -1; }
                            if (aPower < bPower){ return 1; }
                            return 0;
                            };
                        };
                    var tempValueSort2 = function(pk1, pk2){
                        var pk1StatIndex = statTokens.indexOf(pk1);
                        var pk1TypeIndex = typeTokens.indexOf(pk1);
                        var pk1IsStat = pk1StatIndex !== -1;
                        var pk1IsType = pk1TypeIndex !== -1;
                        var pk2StatIndex = statTokens.indexOf(pk2);
                        var pk2TypeIndex = typeTokens.indexOf(pk2);
                        var pk2IsStat = pk2StatIndex !== -1;
                        var pk2IsType = pk2TypeIndex !== -1;
                        if (pk1IsStat && !pk2IsStat){ return -1; }
                        if (!pk1IsStat && pk2IsStat){ return 1; }
                        if (pk1IsType && !pk2IsType){ return -1; }
                        if (!pk1IsType && pk2IsType){ return 1; }
                        return 0;
                        };
                    voidPowersKeys.sort(tempValueSort('powers'));
                    voidFlowsKeys.sort(tempValueSort('flows'));
                    //console.log('voidPowersKeys(power-sorted):', '\n-> [' + voidPowersKeys.join(', ') + ']');
                    //console.log('voidFlowsKeys(power-sorted):', '\n-> [' + voidFlowsKeys.join(', ') + ']');
                    voidPowersKeys.sort(tempValueSort2);
                    voidFlowsKeys.sort(tempValueSort2);
                    //console.log('voidPowersKeys(stat-and-type-sorted):', '\n-> [' + voidPowersKeys.join(', ') + ']');
                    //console.log('voidFlowsKeys(stat-and-type-sorted):', '\n-> [' + voidFlowsKeys.join(', ') + ']');

                    // Then we can collect the ordered list of required power tokens and
                    // use that to sort any required power tokens to the top of the list
                    var voidPowersRequired = config.voidPowersRequired;
                    voidPowersKeys.sort(function(a, b){
                        var aIndex = voidPowersRequired.indexOf(a);
                        var bIndex = voidPowersRequired.indexOf(b);
                        if (aIndex !== -1 && bIndex !== -1){ return aIndex - bIndex; }
                        if (aIndex !== -1){ return -1; }
                        if (bIndex !== -1){ return 1; }
                        return 0;
                        });
                    //console.log('voidPowersKeys(required-first):', '\n-> [' + voidPowersKeys.join(', ') + ']');

                    // Now we update the list of void powers in the UI to show any changes
                    console.log('%c' + 'we shall render powers', 'background-color: black; color: cyan; padding: 0 6px;');
                    console.log('voidPowers:', voidPowers);
                    console.log('voidPowersKeys:', voidPowersKeys);
                    console.log('voidPowersValSum:', voidPowersValSum);
                    console.log('voidFlows:', voidFlows);
                    console.log('voidFlowsKeys:', voidFlowsKeys);
                    console.log('voidFlowsValSum:', voidFlowsValSum);
                    console.log('%c' + 'they have been rendered', 'background-color: black; color: cyan; padding: 0 6px;');
                    if (true){  // (always render the UI even if there are no powers yet)

                        // Pull in the power renderer to make things easier
                        var VoidPowersRenderer = _self.voidPowersRenderer;

                        // Define object variables to hold the different kinds of powers we display
                        var basePowersValues = {delta: 0, quanta: 0, spread: 0, focus: 0};
                        var rankPowersValues = {level: 0, forte: 0};
                        var rankPowersValuesMax = {level: 100, forte: 10};
                        var flowPowersValues = {};
                        var statPowersValues = {};
                        for (var i = 0, stats = ['attack', 'defense', 'speed']; i < stats.length; i++){
                            statPowersValues[stats[i]] = { value: 0, boosts: 0, breaks: 0 };
                            }

                        // Pull in current values for the base powers we'll be displaying
                        if (voidPowers.delta){ basePowersValues.delta = voidPowers.delta; }
                        if (voidPowers.quanta){ basePowersValues.quanta = voidPowers.quanta; }
                        if (voidPowers.spread){ basePowersValues.spread = voidPowers.spread; }
                        if (voidPowers.focus){ basePowersValues.focus = voidPowers.focus; }

                        // Pull in current values for the rank powers we'll be displaying
                        if (voidPowers.level){ rankPowersValues.level = voidPowers.level; }
                        if (voidPowers.forte){ rankPowersValues.forte = voidPowers.forte; }
                        if (voidPowers.levelMax){ rankPowersValuesMax.level = voidPowers.levelMax; }
                        if (voidPowers.forteMax){ rankPowersValuesMax.forte = voidPowers.forteMax; }

                        // Pull in current values for the sort powers we'll be displaying
                        var typeFlows = _self.filterFlowPowers(voidFlows);
                        if (typeFlows){ flowPowersValues = Object.assign({}, flowPowersValues, typeFlows); }
                        console.log('FLOW DEBUG:', {voidFlows, typeFlows, flowPowersValues});

                        // Pull in current values for the stat powers we'll be displaying
                        let rawStatPowers = _self.filterStatPowers(voidPowers);
                        let rawStatPowersKeys = Object.keys(rawStatPowers);
                        for (var i = 0; i < rawStatPowersKeys.length; i++){
                            let statToken = rawStatPowersKeys[i];
                            let statValue = rawStatPowers[statToken] || 0;
                            let statValuePower = Math.abs(statValue);
                            let statValueSpread = basePowersValues.spread || 1;
                            let statValueArrows = Math.min(5, Math.floor(statValuePower / statValueSpread));
                            let statValueBoosts = statValue < 0 ? 0 : statValueArrows;
                            let statValueBreaks = statValue > 0 ? 0 : statValueArrows;
                            let parsedStatPower = {value: statValue, boosts: statValueBoosts, breaks: statValueBreaks};
                            statPowersValues[statToken] = parsedStatPower;
                            }
                        //console.log('STAT DEBUG:', {voidPowers, rawStatPowers, statPowersValues});

                        // Render the base powers (quanta and spread) to display the appropriate markup
                        VoidPowersRenderer.renderBasePowers($missionDetails, basePowersValues);

                        // Render the rank powers (level and forte) to display appropriate markup
                        VoidPowersRenderer.renderRankPowers($missionDetails, rankPowersValues, rankPowersValuesMax);

                        // Render the flow powers (elemental types) to display appropriate markup
                        VoidPowersRenderer.renderFlowPowers($missionDetails, flowPowersValues);

                        // Render for relative stat powers (attack, defense, speed) and display appropriate markup
                        VoidPowersRenderer.renderStatPowers($missionDetails, statPowersValues);

                        }

                    // Update the list of target robots in the panel if any have been generated
                    var missionInfo = _self.mission;
                    var missionTargets = missionInfo.targets || [];
                    if (missionTargets.length){
                        console.log('Updating mission target display using new data...', '\n-> missionInfo:', missionInfo, '\n-> missionTargets:', missionTargets);
                        const mmrpgIndexRobots = mmrpgIndex.robots;
                        const frameTokenByKey = {0: 'base', 1: 'defense', 2: 'base2', 3: 'defend', 4: 'base', 5: 'defend', 6: 'base2', 7: 'defend'};
                        var targetListRobotMarkup = '';
                        var targetListRobotCount = 0;
                        for (var i = 0; i < missionTargets.length; i++){
                            var targetKey = i;
                            var targetLayer = config.maxTargets - i;
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
                            var targetRobotTypes = [];
                            if (targetRobotInfo['robot_core']){ targetRobotTypes.push(targetRobotInfo['robot_core']); }
                            if (targetRobotInfo['robot_core2']){ targetRobotTypes.push(targetRobotInfo['robot_core2']); }
                            var targetTypeClasses = targetRobotTypes.length ? targetRobotTypes.join('_') : 'none';
                            //var targetTypeCode = targetRobotTypes[0].substr(0, 2).toUpperCase();
                            //var targetTypeCode = targetRobotTypes[0].substr(0, 3).toUpperCase();
                            var targetTypeCode = targetRobotTypes[0].substr(0, 1).toUpperCase() + targetRobotTypes[0].substr(1, 1).toLowerCase();
                            //var targetTypeCode = targetRobotTypes[0].substr(0, 1).toUpperCase() + targetRobotTypes[0].substr(1, 2).toLowerCase();
                            //var targetTypeCode = targetRobotTypes[0].substr(0, 1).toUpperCase() + targetRobotTypes[0].substr(1, 2).toLowerCase();
                            var targetRobotImageSize = targetRobotInfo['robot_image_size'] || 40;
                            var targetRobotImageSizeX = targetRobotImageSize + 'x' + targetRobotImageSize;
                            var targetRobotFrame = frameTokenByKey[targetKey] || '00';
                            var targetRobotSprite = '/images/robots/'+targetRobotImage+'/sprite_left_'+targetRobotImageSizeX+'.png?'+gameSettings.cacheTime;
                            var targetRobotMarkup = '<div class="target" style="z-index: '+targetLayer+';">';
                                targetRobotMarkup += '<i class="portal type '+targetRobotSlotType+'"></i>';
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
                                    //targetRobotMarkup += '<span class="type '+targetTypeClasses+'">'+targetRobotTypes.join(', ')+'</span>';
                                    targetRobotMarkup += '<span class="type '+targetTypeClasses+'">'+targetTypeCode+'</span>';
                                    targetRobotMarkup += '<span class="quanta">';
                                        targetRobotMarkup += '<i class="fa fa-atom"></i>';
                                        targetRobotMarkup += '<strong>'+targetRobotQuanta+'</sub></strong>';
                                    targetRobotMarkup += '</span>';
                                targetRobotMarkup += '</div>';
                            targetRobotMarkup += '</div>';
                            targetListRobotMarkup += targetRobotMarkup;
                            targetListRobotCount += 1;
                            }
                        $targetList.append('<div class="wrapper has-'+targetListRobotCount+' '+(targetListRobotCount % 2 === 0 ? 'has-even' : 'has-odd')+'">' + targetListRobotMarkup + '</div>');
                        }

                    // end of voidRecipeWizard.refreshUI()
                    },
                setStatus: function(status, value, prefix){
                    console.log('%c' + 'voidRecipeWizard.setStatus()', 'color: magenta;');
                    const _self = this;
                    const config = _self.config;
                    const xrefs = _self.xrefs;
                    if (typeof status === 'undefined' || !status){ return false; }
                    if (typeof value === 'undefined'){ return false; }
                    if (typeof prefix === 'undefined'){ prefix = 'is'; }
                    const htmlCls = (prefix + '-' + status);
                    if (value){ _self[status] = true; }
                    else { _self[status] = false; }
                    if (typeof xrefs.parentDiv === 'undefined' || !xrefs.parentDiv){ return; }
                    const jsCls = (prefix + status.charAt(0).toUpperCase() + status.slice(1));
                    if (value){ $parentDiv.addClass(htmlCls); }
                    else { $parentDiv.removeClass(htmlCls); }
                    // end of voidRecipeWizard.setStatus()
                    },
                nowReady: function(value){
                    console.log('%c' + 'voidRecipeWizard.nowReady()', 'color: magenta;');
                    const _self = this; return _self.setStatus('ready', value, 'is');
                    // end of voidRecipeWizard.nowReady()
                    },
                nowLoading: function(value){
                    console.log('%c' + 'voidRecipeWizard.nowLoading()', 'color: magenta;');
                    const _self = this; return _self.setStatus('loading', value, 'is');
                    // end of voidRecipeWizard.nowLoading()
                    },
                hasContent: function(value){
                    console.log('%c' + 'voidRecipeWizard.hasContent()', 'color: magenta;');
                    const _self = this; return _self.setStatus('content', value, 'has');
                    // end of voidRecipeWizard.hasContent()
                    },
                getOrdinalSuffix: function(num){
                    //console.log('%c' + 'voidRecipeWizard.getOrdinalSuffix()', 'color: magenta;');
                    const suffixes = ["th", "st", "nd", "rd"];
                    const value = num % 100;
                    return suffixes[(value - 20) % 10] || suffixes[value] || suffixes[0];
                    // end of voidRecipeWizard.getOrdinalSuffix()
                    },
                showDebug: function(kind){
                    console.log('%c' + 'voidRecipeWizard.showDebug()', 'color: magenta;');
                    kind = kind || 'all';
                    const _self = this;
                    if (kind === 'powers' || kind === 'all'){
                        console.log('%c' + 'VOID POWERS/FLOWS:', 'background-color: #242131; color: #fff; font-weight: bold;');
                        //console.log('_self.powers =', _self.powers);
                        var powerDebug = '';
                        var powerKeys = Object.keys(_self.powers);
                        for (var i = 0; i < powerKeys.length; i++){
                            var powerKey = powerKeys[i];
                            var powerValue = _self.powers[powerKey];
                            powerDebug += '(ꝑ)-> ' + powerKey + ': ' + powerValue + '\n';
                            }
                        //console.log('_self.flows =', _self.flows);
                        var flowDebug = '';
                        var flowKeys = Object.keys(_self.flows);
                        for (var i = 0; i < flowKeys.length; i++){
                            var flowKey = flowKeys[i];
                            var flowValue = _self.flows[flowKey];
                            flowDebug += '(𝑓)-> ' + flowKey + ': ' + flowValue + '\n';
                            }
                        console.log('%c' + powerDebug, 'background-color: #242131; color: #fff; font-weight: bold;');
                        console.log('%c' + flowDebug, 'background-color: #242131; color: #fff; font-weight: bold;');
                        console.log('via _self.powers:', _self.powers);
                        console.log('via _self.flows:', _self.flows);
                        }
                    // end of voidRecipeWizard.showDebug()
                    },
                };

            // Initialize the void recipe calculator
            console.log('%c' + 'Initializing the voidRecipeWizard()', 'color: orange;');
            voidRecipeWizard.init($voidRecipeWizard, {
                types: mmrpgIndex.types,
                robots: mmrpgIndex.robots,
                items: mmrpgIndex.items,
                itemsGroups: voidRecipeItemGroups,
                itemsDisabled: voidRecipeItemsDisabled,
                itemsQuantities: voidRecipeItemsQuantities,
                fields: mmrpgIndex.fields,
                });

            })();
        }

})();
