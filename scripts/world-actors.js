
// -- WORLD ACTORS METHODS -- //

// Define a quick function for parsing an actor event on the map and returning action dropdown options
function prepareActorEventOptions(firstEvent, options){
    //console.log('%c' + 'mmrpgWorldMap.prepareActorEventOptions()', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _indexes = _self.indexes;

    let _playerIndexInfo = _indexes.players[_worldPlayer.token];

    let firstRobotToken = _worldPlayer.team[0];
    let _worldPlayerRobots = _worldPlayer.robots;
    let _firstPlayerRobot = _worldPlayerRobots[firstRobotToken];
    let _firstPlayerRobotIndexInfo = _indexes.robots[_firstPlayerRobot.token];

    options = options || {};

    let eventInfo = firstEvent;
    let actorInfo = _config.mapActorsIndex[eventInfo.token];
    let $actorEvent = $(firstEvent.sprite);
    let dataActor = $actorEvent.attr('data-actor');
    let dataLabel = $actorEvent.attr('data-label');
    let dataColour = $actorEvent.attr('data-colour');

    let actionData = {
        showActionArea: false,
        actionAreaMarkup: '',
        sideButtonsMarkup: '',
        showActionAreaType: '',
        zoomTimeoutDuration: 0
    };

    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;

    if (dataActor && !actorInfo.removed && !playerIsCursor){
        actionData.showActionArea = true;
        actionData.showActionAreaSound = 'tooltip-text';
        if (!dataLabel){ dataLabel = 'Denizen'; }
        if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }

        if (actorInfo.messages){
            let avatarSprite = '&hellip;';
            if (actorInfo.kind === 'player'){ avatarSprite = _self.getPlayerSpriteMarkup(actorInfo.sprite, {alt: actorInfo.image, kind: 'mug'}); }
            else if (actorInfo.kind === 'robot'){ avatarSprite = _self.getRobotSpriteMarkup(actorInfo.sprite, {alt: actorInfo.image, kind: 'mug'}); }
            // Join the array strings directly with HTML linebreaks
            let messagesText = typeof actorInfo.messages === 'object' ? actorInfo.messages.join('<br />') : actorInfo.messages;
            messagesText = messagesText.replaceAll('{player_name}', _playerIndexInfo['name']);
            messagesText = messagesText.replaceAll('{robot_name}', _firstPlayerRobotIndexInfo['name']);
            actionData.sideButtonsMarkup += '<div class="button-title big-button-title'+(dataColour ? ' type '+dataColour : '')+'">';
                actionData.sideButtonsMarkup += '<i class="icon">' + avatarSprite + '</i>';
                actionData.sideButtonsMarkup += '<p class="text typewriter" style="opacity: 0;">' + messagesText + '</p>';
            actionData.sideButtonsMarkup += '</div>';
            }

        if (actorInfo.actions && actorInfo.actions.length){
            for (let i = 0; i < actorInfo.actions.length; i++){
                let action = actorInfo.actions[i];
                let label = typeof action[0] !== 'undefined' && action[0] ? action[0] : false;
                let token = typeof action[1] !== 'undefined' && action[1] ? action[1] : false;
                let colour = typeof action[2] !== 'undefined' && action[2] ? action[2] : false;

                if (!label || !token){ continue; }

                if (token === 'reset-world-pickups'){
                    // Check if we allow reset-world-pickups ...
                    if (!_config.mapHasPickups){ continue; }
                    }
                else if (token === 'reset-world-encounters'){
                    // Check if we allow reset-world-encounters ...
                    if (!_config.mapHasEncounters){ continue; }
                    }

                let enabled = true;
                let currency = typeof action[3] !== 'undefined' && action[3] ? action[3] : false;
                let price = typeof action[4] !== 'undefined' && action[4] ? action[4] : false;

                // We extract our new adjustable flag from the action array (index 5)
                let adjustable = typeof action[5] !== 'undefined' && action[5] === true ? true : false;

                let innerHtml = '<span>' + label + '</span>';

                // Check if we need to disable the button because our lead robot is already maxed
                if (token.indexOf('boost-robot-') !== -1){
                    let statToken = token.replace('boost-robot-', '');
                    let statBonusKey = statToken + 'Bonus';
                    let currentBonus = _firstPlayerRobot[statBonusKey] || 0;
                    let baseToken = _firstPlayerRobot.personaActive ? _firstPlayerRobot.persona : _firstPlayerRobot.token;
                    let baseIndexInfo = _indexes.robots[baseToken];
                    let robotBaseStats = {
                        token: baseToken,
                        energy: baseIndexInfo.energy || 0,
                        weapons: baseIndexInfo.weapons || 0,
                        attack: baseIndexInfo.attack || 0,
                        defense: baseIndexInfo.defense || 0,
                        speed: baseIndexInfo.speed || 0,
                        };
                    // Map the 'Bonus' variables to their raw stat keys for the calculator
                    let robotBonusStats = {
                        token: _firstPlayerRobot.token,
                        energy: _firstPlayerRobot.energyBonus || 0,
                        weapons: _firstPlayerRobot.weaponsBonus || 0,
                        attack: _firstPlayerRobot.attackBonus || 0,
                        defense: _firstPlayerRobot.defenseBonus || 0,
                        speed: _firstPlayerRobot.speedBonus || 0,
                        };
                    // Run a dry calculation to extract our strict bonusMax threshold
                    let robotStatsPreview = _self.calculateStatValues(
                        _firstPlayerRobot.level,
                        robotBaseStats,
                        robotBonusStats,
                        false,
                        _firstPlayerRobot.personaActive ? 'copy' : (_firstPlayerRobot.core || ''),
                        _worldPlayer.starForce || {}
                        );
                    if (currentBonus >= robotStatsPreview[statToken].bonusMax) {
                        enabled = false;
                        label += ' (Maxed)';
                        }
                    }

                /*
                if (currency && price){
                    let currencyCheck = _self.checkMenuButtonCurrency(currency, price);
                    // We only disable the button if we cannot afford the minimum price
                    if (!currencyCheck.hasEnough){ enabled = false; }
                    innerHtml = '<span class="has-sprite">'
                        + '<strong>' + label + '</strong> '
                        + '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> '
                        + currencyCheck.spriteMarkup
                        + '</span>';
                    }

                // We construct our button classes and data attributes
                let buttonClasses = 'button big-button' + (colour ? ' type ' + colour : '') + (!enabled ? ' disabled' : '');
                if (adjustable){ buttonClasses += ' has-inner-buttons'; }
                let buttonAttrs = ' data-actor="' + dataActor + '" data-action="' + token + '"';
                if (adjustable){ buttonAttrs += ' data-price="' + price + '"'; }

                // We build the final anchor tag, injecting our arrow spans if adjustable is true
                let buttonMarkup = '<a class="' + buttonClasses + '"' + buttonAttrs + '>';
                if (adjustable){ buttonMarkup += '<span class="inner-button decrement"><i class="fa fas fa-caret-left"></i></span>'; }
                buttonMarkup += innerHtml;
                if (adjustable){ buttonMarkup += '<span class="inner-button increment"><i class="fa fas fa-caret-right"></i></span>'; }
                buttonMarkup += '</a>';
                actionData.sideButtonsMarkup += buttonMarkup;
                */

                let isTrade = token.indexOf('trade-for-') !== -1;

                if (currency && price){
                    let currencyCheck = _self.checkMenuButtonCurrency(currency, price);
                    // We only disable the button if we cannot afford the minimum price
                    if (!currencyCheck.hasEnough){ enabled = false; }

                    // Check if this is a trade or not before generating anything
                    if (isTrade){
                        let receiveItemToken = token.replace('trade-for-', '');
                        // Generate the sprite for the item we are receiving
                        let receiveSpriteMarkup = _self.getItemSpriteMarkup(receiveItemToken, {dir: 'right', frame: '00'});
                        innerHtml = '<span class="has-sprite has-double-sprites">'
                            + '<strong>' + label + '</strong> '
                            + '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> '
                            + '<div class="sprite-wrapper trade-sprites">'
                                + currencyCheck.spriteMarkup
                                + '<i class="fa fas fa-caret-right trade-arrow"></i>'
                                + receiveSpriteMarkup
                            + '</div>'
                            + '</span>';
                        } else {
                        innerHtml = '<span class="has-sprite">'
                            + '<strong>' + label + '</strong> '
                            + '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> '
                            + currencyCheck.spriteMarkup
                            + '</span>';
                        }
                    }

                // We construct our button classes and data attributes
                let buttonClasses = 'button big-button' + (colour ? ' type ' + colour : '') + (!enabled ? ' disabled' : '');
                if (adjustable){ buttonClasses += ' has-inner-buttons'; }
                if (isTrade){ buttonClasses += ' is-trade-action'; }

                let buttonAttrs = ' data-actor="' + dataActor + '" data-action="' + token + '"';
                if (adjustable){ buttonAttrs += ' data-price="' + price + '"'; }

                // We build the final anchor tag, injecting our arrow spans if adjustable is true
                let buttonMarkup = '<a class="' + buttonClasses + '"' + buttonAttrs + '>';
                if (adjustable){ buttonMarkup += '<span class="inner-button decrement"><i class="fa fas fa-caret-left"></i></span>'; }
                buttonMarkup += innerHtml;
                if (adjustable){ buttonMarkup += '<span class="inner-button increment"><i class="fa fas fa-caret-right"></i></span>'; }
                buttonMarkup += '</a>';

                actionData.sideButtonsMarkup += buttonMarkup;

                }
            }

        actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
        actionData.showActionAreaType = 'actor';
        actionData.zoomTimeoutDuration = 500;
        }

    // If the dialogue is about to be shown, start a 2-second timeout to track the interaction
    if (actionData.showActionArea && dataActor){
        let hasInteracted = typeof _world.actors[dataActor] !== 'undefined' || actorInfo.interacted ? true : false;
        if (!hasInteracted){
            // Clear any existing dialogue timeout just in case
            if (_self.actorInteractTimeout){ clearTimeout(_self.actorInteractTimeout); }
            _self.actorInteractTimeout = setTimeout(function(){
                let $activeDropdown = _self.elements.actionDropdown;
                // Verify the dropdown is still visible and showing an actor interaction
                if ($activeDropdown && $activeDropdown.is('.active') && $activeDropdown.attr('data-type') === 'actor'){
                    _world.actors[dataActor] = new Date().getTime();
                    _self.saveWorldState();
                    }
                }, 2000);
            }
        }

    return actionData;
    }

// Define a quick function for handling actor interactions (shops, advice, resets) via the dropdown
function handleActorActionClick(action, $button, firstEvent){
    //console.log('%c' + 'mmrpgWorldMap.handleActorActionClick(action:' + action + ')', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _elements = _self.elements;
    let _worldPlayer = _world.player;
    let _playerToken = _config.playerToken;
    let $thisWorld = _elements.world;
    let $thisCanvas = _elements.canvas;
    let $canvasMap = _elements.map;

    let isShop = action.indexOf('-shop') !== -1 || action.indexOf('shop-') !== -1;
    let isAdvice = action.indexOf('-advice') !== -1 || action.indexOf('advice-') !== -1;
    let isReset = action === 'reset-world-pickups' || action === 'reset-world-encounters';
    let isBoost = action === 'boost-robot-attack' || action === 'boost-robot-defense' || action === 'boost-robot-speed';
    let isTrade = action.indexOf('trade-for-') !== -1;

    let $actorEvent = $(firstEvent.sprite);
    let dataActor = $actorEvent.attr('data-actor') || false;
    let actorInfo = dataActor && _config.mapActorsIndex[dataActor] ? _config.mapActorsIndex[dataActor] : false;
    let hasInteracted = dataActor && (typeof _world.actors[dataActor] !== 'undefined' || (actorInfo && actorInfo.interacted)) ? true : false;

    // If an action was taken (other than dismiss) and they haven't been tracked yet, save immediately
    if (action !== 'dismiss' && dataActor && !hasInteracted){
        if (_self.actorInteractTimeout){ clearTimeout(_self.actorInteractTimeout); }
        _world.actors[dataActor] = new Date().getTime();
        _self.saveWorldState();
        }

    if (isShop){
        let shopToken = action.replace(/^shop-with-/, '');
        let $popupFrame, $popupCloseButton;
        let $canvasWrapper = $('> .wrapper', $thisCanvas);
        let $progressTracker = $('#progress-tracker', $thisCanvas);
        let $zennyCounter = $('.counter.zenny', $progressTracker);
        let $robotsOverview = _elements.robotsOverview;

        let onShopReady = function(){
            _self.hideActionDropdown(false);
            $popupFrame.removeClass('hidden');
            _self.playSoundEffect('inventory-open', {delay: 300});
            };

        let onUpdateZenny = function(newAmount){
            $('.count', $zennyCounter).html(newAmount);
            let newIntAmount = parseInt(newAmount.replace(',', ''));
            _worldPlayer.zenny = newIntAmount;
            };

        let onUpdateQuantities = function(newQuantities){
            let itemTokens = Object.keys(newQuantities);
            for (let i = 0; i < itemTokens.length; i++){
                let itemToken = itemTokens[i];
                let itemQuantity = newQuantities[itemToken];
                let itemInfo = _self.indexes.items.getByToken(itemToken);
                if (!itemInfo){ continue; }
                if (itemQuantity > 0 && typeof _worldPlayer.items[itemToken] === 'undefined'){
                    _self.addItemToInventory(itemToken, itemQuantity, false, false);
                    } else {
                    _worldPlayer.items[itemToken] = itemQuantity;
                    let $itemInStorage = $('.storage-items .team-item[data-item="' + itemToken + '"]', $robotsOverview);
                    if ($itemInStorage && $itemInStorage.length){
                        $itemInStorage.attr('data-quantity', itemQuantity);
                        $itemInStorage.find('.quantity').html('&times; ' + itemQuantity);
                        }
                    }
                }
            };

        let frameSource = 'frames/shop.php?world=true&shop=' + shopToken + '&player=' + _playerToken;
        let popupMarkup = '<div id="popup-iframe" class="chrome active hidden">'
                + '<div class="overlay"></div>'
                + '<div class="wrapper">'
                    + '<iframe frameborder="0" scrolling="no"></iframe>'
                + '</div>'
                + '<a class="close"><i class="fa fas fa-plus"></i><strong>Close Shop</strong></a>'
            + '</div>';

        $popupFrame = $(popupMarkup);
        $popupFrame.appendTo($canvasWrapper);
        _self.playSoundEffect('link-click');

        $popupCloseButton = $('> .close', $popupFrame);
        $popupCloseButton.bind('click', function(e){
            e.preventDefault();
            gameSettings.activeWindowEvent = false;
            $popupFrame.addClass('hidden');
            $thisWorld.removeClass('has_iframe');
            _self.playSoundEffect('back-click');
            _self.triggerWindowEventsPull();
            setTimeout(function(){
                $popupFrame.remove();
                }, 1000);
            if (_world.refreshRequired){
                _self.incZoomLevel();
                $thisWorld.addClass('loading');
                window.location.href = window.location.href;
                _world.refreshRequired = false;
                return;
                }
            });

        window.prototype_menu_loaded = function(){ onShopReady(); };
        window.prototype_update_zenny = function(newZenny){ onUpdateZenny(newZenny.replace(/\s[^0-9]+$/, '')); };
        window.prototype_update_item_quantities = function(newQuantities){ onUpdateQuantities(newQuantities);  };
        window.prototype_update_battle_points = function(newPoints){ /* ... */ };
        window.prototype_update_leaderboard_rank = function(newRank){ /* ... */ };

        $thisWorld.addClass('has_iframe');
        gameSettings.activeWindowEvent = true;
        $('iframe', $popupFrame).attr('src', frameSource);
        }
    else if (isAdvice){
        let actorToken, actorNameKey, actorInfo;
        actorToken = action.replace(/^advice-from-/, '');
        if (actorToken === 'auto'){ actorNameKey = 'good-guy-auto'; }
        else if (actorToken === 'reggae'){ actorNameKey = 'weird-bird-reggae'; }
        else if (actorToken === 'kalinka'){ actorNameKey = 'star-gazer-kalinka'; }

        if (actorNameKey && typeof _config.mapActorsIndex[actorNameKey] !== 'undefined'){ actorInfo = _config.mapActorsIndex[actorNameKey]; }

        let possibleMessages = [];
        if (actorInfo && typeof actorInfo['advice'] !== 'undefined' && actorInfo['advice'].length > 0){
            possibleMessages = Object.values(actorInfo['advice']);
            } else {
            possibleMessages.push('I don\'t have any advice to share right now, sorry.');
            }

        // Initialize an object to track advice queues for different actors if it doesn't exist yet
        _self.adviceQueues = _self.adviceQueues || {};
        let queueKey = actorNameKey || actorToken;

        // If the queue for this actor is missing or empty, build and shuffle a new deck of indices
        if (!_self.adviceQueues[queueKey] || _self.adviceQueues[queueKey].length === 0){
            let newQueue = [];
            for (let i = 0; i < possibleMessages.length; i++){
                newQueue.push(i);
                }
            for (let i = newQueue.length - 1; i > 0; i--){
                let j = Math.floor(Math.random() * (i + 1));
                [newQueue[i], newQueue[j]] = [newQueue[j], newQueue[i]];
                }
            _self.adviceQueues[queueKey] = newQueue;
            }

        // Pop the next message index off the front of the queue
        let selectedMessageIndex = _self.adviceQueues[queueKey].shift();
        let selectedMessageText = possibleMessages[selectedMessageIndex];

        // Target the text element, update its raw text cache, and run the typewriter
        let $textElement = _elements.sideButtons.find('.text');
        $textElement.data('raw-text', selectedMessageText);
        _self.playTypewriterEffect($textElement);

        _self.enableActionDropdown();
        _elements.sideButtons.find('.clicked').removeClass('clicked').addClass('hovered maybe');
        }
    else if (isReset){
        let actorsIndex = _config.mapActorsIndex;
        let actorName = $button.attr('data-actor') || false;
        let actorInfo = actorName && (actorsIndex && actorsIndex[actorName]) ? actorsIndex[actorName] : false;
        if (!actorName || !actorInfo){ console.error('-> actor name or info not found, cannot trigger actions!'); return false; }

        let actionList = typeof actorInfo.actions === 'object' && actorInfo.actions.length ? actorInfo.actions : false;
        if (!actionList){ console.error('-> actor actionlist is empty, nothing to trigger!'); return false; }

        let actionListKeys = (function(list){ let keys = []; for (let i = 0; i < list.length; i++){ keys.push(list[i][1]); } return keys; })(actionList);
        let actionInfoData = actionListKeys.indexOf(action) !== -1 ? actionList[actionListKeys.indexOf(action)] : false;
        if (!actionInfoData){ console.error('-> actor action info could not be found!'); return false; }

        let currency = actionInfoData[3], price = actionInfoData[4];
        let currencyCheck = _self.checkMenuButtonCurrency(currency, price);
        let currencyKind = currencyCheck.kind, currencySubKind = currencyCheck.subKind;

        if (currencyKind === 'stars'){
            /* do nothing */
            }
        else if (currencyKind === 'zenny'){
            _self.playSoundEffect('zenny-spent');
            _worldPlayer.zenny -= price;
            if (_worldPlayer.zenny < 0){ _worldPlayer.zenny = 0; }
            }
        else if (currencyKind === 'items'){
            if (currencySubKind.length && typeof _worldPlayer.items[currencySubKind] !== 'undefined'){
                _self.playSoundEffect('zenny-spent');
                _worldPlayer.items[currencySubKind] -= price;
                if (_worldPlayer.items[currencySubKind] < 0){ _worldPlayer.items[currencySubKind] = 0; }
                }
            }

        let resetHref = false;
        if (action === 'reset-world-pickups'){ resetHref = 'world.php?reset=pickups'; }
        else if (action === 'reset-world-encounters'){ resetHref = 'world.php?reset=encounters'; }

        if (resetHref){
            _self.playSoundEffect('bounce-sound');
            _self.incZoomLevel();
            $thisWorld.addClass('busy');
            _self.saveWorldState(function(){
                _self.incZoomLevel();
                $thisWorld.addClass('loading');
                window.location.href = resetHref;
                _self.incZoomLevel();
                }, true, false);
            $thisWorld.animate({opacity: 0}, 1200, function(){
                $thisWorld.addClass('hidden');
                });
            }
        }
    else if (isBoost){
        let actorsIndex = _config.mapActorsIndex;
        let actorName = $button.attr('data-actor') || false;
        let actorInfo = actorName && (actorsIndex && actorsIndex[actorName]) ? actorsIndex[actorName] : false;
        if (!actorName || !actorInfo){ console.error('-> actor name or info not found, cannot trigger actions!'); return false; }

        let actionList = typeof actorInfo.actions === 'object' && actorInfo.actions.length ? actorInfo.actions : false;
        if (!actionList){ console.error('-> actor actionlist is empty, nothing to trigger!'); return false; }

        let actionListKeys = (function(list){ let keys = []; for (let i = 0; i < list.length; i++){ keys.push(list[i][1]); } return keys; })(actionList);
        let actionInfoData = actionListKeys.indexOf(action) !== -1 ? actionList[actionListKeys.indexOf(action)] : false;
        if (!actionInfoData){ console.error('-> actor action info could not be found!'); return false; }

        // Determine which robot and which stat we are modifying
        let leadRobotString = _worldPlayer.team[0];
        let leadRobotData = _worldPlayer.robots[leadRobotString];
        let statToken = action.replace('boost-robot-', '');
        let statBonusKey = statToken + 'Bonus';

        // Gather base stats for the calculator
        let baseToken = leadRobotData.personaActive ? leadRobotData.persona : leadRobotData.token;
        let baseIndexInfo = _self.indexes.robots[baseToken];
        let robotBaseStats = {
            token: baseToken,
            energy: baseIndexInfo.energy || 0,
            weapons: baseIndexInfo.weapons || 0,
            attack: baseIndexInfo.attack || 0,
            defense: baseIndexInfo.defense || 0,
            speed: baseIndexInfo.speed || 0,
            };

        // Map the 'Bonus' variables to their raw stat keys for the calculator
        let robotBonusStats = {
            token: leadRobotData.token,
            energy: leadRobotData.energyBonus || 0,
            weapons: leadRobotData.weaponsBonus || 0,
            attack: leadRobotData.attackBonus || 0,
            defense: leadRobotData.defenseBonus || 0,
            speed: leadRobotData.speedBonus || 0,
            };

        let robotStarforce = _worldPlayer.starForce || {};

        // Calculate our stats to extract the hard ceiling
        let newRobotStats = _self.calculateStatValues(
            leadRobotData.level,
            robotBaseStats,
            robotBonusStats,
            true,
            leadRobotData.personaActive ? 'copy' : (leadRobotData.core || ''),
            robotStarforce
            );

        // Fallback: If we are already at or above our max bonus, abort entirely
        let currentBonus = leadRobotData[statBonusKey] || 0;
        let robotNameTextSpan = _self.getRobotNameSpan(leadRobotData.token, leadRobotData.name);

        if (currentBonus >= newRobotStats[statToken].bonusMax){
            _self.playSoundEffect('error-sound');
            _self.showWorldMessage([robotNameTextSpan + '&#39;s ' + statToken + ' is already at maximum!']);
            return false;
            }

        // Retrieve our currency, base price, and the newly added multiplier
        let currency = actionInfoData[3], basePrice = actionInfoData[4];
        let multiplier = parseInt($button.attr('data-multiplier') || '1');

        // We determine how many multipliers are actually needed to max the stat
        let spaceRemaining = newRobotStats[statToken].bonusMax - currentBonus;
        let maxUsableMultiplier = Math.ceil(spaceRemaining / (basePrice * 10));

        // We clamp the multiplier so the user doesn't accidentally waste capsules over the limit
        if (multiplier > maxUsableMultiplier) {
            multiplier = maxUsableMultiplier;
            }

        // Calculate the effective price and verify we can afford it
        let effectivePrice = basePrice * multiplier;
        let currencyCheck = _self.checkMenuButtonCurrency(currency, effectivePrice);

        if (!currencyCheck.hasEnough){
            _self.playSoundEffect('error-sound');
            return false;
            }

        // Deduct our capsules
        let currencyKind = currencyCheck.kind, currencySubKind = currencyCheck.subKind;
        if (currencyKind === 'items' && currencySubKind && typeof _worldPlayer.items[currencySubKind] !== 'undefined'){
            _worldPlayer.items[currencySubKind] -= effectivePrice;
            if (_worldPlayer.items[currencySubKind] < 0){ _worldPlayer.items[currencySubKind] = 0; }
            }

        // Capture our old stat before we do any recalculations
        let oldStatValue = leadRobotData[statToken] || 0;

        // Apply our stat boost (multiplied by our clamped multiplier)
        let boostAmount = effectivePrice * 10;
        leadRobotData[statBonusKey] = currentBonus + boostAmount;

        // Re-run the limit-capped calculation to ensure we do not overshoot our bonusMax
        robotBonusStats[statToken] = leadRobotData[statBonusKey];

        newRobotStats = _self.calculateStatValues(
            leadRobotData.level,
            robotBaseStats,
            robotBonusStats,
            true,
            leadRobotData.personaActive ? 'copy' : (leadRobotData.core || ''),
            robotStarforce
            );

        leadRobotData[statToken] = newRobotStats[statToken].current;
        leadRobotData[statBonusKey] = newRobotStats[statToken].bonus; // Sync back the correctly clamped bonus

        // Re-apply our player type boosts
        let playerIndexInfo = _self.indexes.players[_worldPlayer.token];
        if (playerIndexInfo && playerIndexInfo.player_type === statToken){
            leadRobotData[statToken] = Math.ceil(leadRobotData[statToken] * 1.25);
            }

        if (_self.robotsOverviewAPI && typeof _self.robotsOverviewAPI.refreshDetailsPanel === 'function'){
            _self.robotsOverviewAPI.refreshDetailsPanel();
            _self.robotsOverviewAPI.refreshRobotsDiv();
            }

        // Grab a reference to our lead robot's map sprite so we can animate it
        let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + leadRobotString + '"]', $canvasMap);

        // Apply our celebration frame and zoom class immediately upon clicking
        $robotSprite.attr('data-frame', '01');
        $robotSprite.addClass('zoom');

        // Auto-dismiss the action dropdown after a short delay so the button has time to flash
        setTimeout(function(){
            _self.hideActionDropdown(false);
            _self.playSoundEffect('recovery-stats');
            _self.showWorldMessage([
                robotNameTextSpan + '&#39;s base ' + statToken + ' grew by ' + boostAmount + '!',
                oldStatValue + ' &rarr; ' + leadRobotData[statToken]
                ]);
            // Revert our animation frame and remove the zoom classes once the message fires
            $robotSprite.attr('data-frame', '00');
            $robotSprite.removeClass('zoom');
            }, 1800);

        _self.saveWorldState();
        }
    else if (isTrade){
        let actorsIndex = _config.mapActorsIndex;
        let actorName = $button.attr('data-actor') || false;
        let actorInfo = actorName && (actorsIndex && actorsIndex[actorName]) ? actorsIndex[actorName] : false;
        if (!actorName || !actorInfo){ console.error('-> actor name or info not found, cannot trigger actions!'); return false; }

        let actionList = typeof actorInfo.actions === 'object' && actorInfo.actions.length ? actorInfo.actions : false;
        if (!actionList){ console.error('-> actor actionlist is empty, nothing to trigger!'); return false; }

        let actionListKeys = (function(list){ let keys = []; for (let i = 0; i < list.length; i++){ keys.push(list[i][1]); } return keys; })(actionList);
        let actionInfoData = actionListKeys.indexOf(action) !== -1 ? actionList[actionListKeys.indexOf(action)] : false;
        if (!actionInfoData){ console.error('-> actor action info could not be found!'); return false; }

        // Retrieve currency requirements and handle our multiplier
        let currency = actionInfoData[3], basePrice = actionInfoData[4];
        let multiplier = parseInt($button.attr('data-multiplier') || '1');
        let effectivePrice = basePrice * multiplier;
        let receiveQuantity = 1 * multiplier;

        let currencyCheck = _self.checkMenuButtonCurrency(currency, effectivePrice);

        if (!currencyCheck.hasEnough){
            _self.playSoundEffect('error-sound');
            return false;
            }

        // Determine what item we are receiving
        let receiveItemToken = action.replace('trade-for-', '');
        let receiveItemInfo = _self.indexes.items.getByToken(receiveItemToken);
        let receiveItemName = receiveItemInfo && receiveItemInfo.name ? receiveItemInfo.name : receiveItemToken;

        // Deduct the required currency
        let currencyKind = currencyCheck.kind, currencySubKind = currencyCheck.subKind;
        if (currencyKind === 'items' && currencySubKind && typeof _worldPlayer.items[currencySubKind] !== 'undefined'){
            _worldPlayer.items[currencySubKind] -= effectivePrice;
            if (_worldPlayer.items[currencySubKind] < 0){ _worldPlayer.items[currencySubKind] = 0; }
            } else if (currencyKind === 'zenny') {
            _worldPlayer.zenny -= effectivePrice;
            if (_worldPlayer.zenny < 0) { _worldPlayer.zenny = 0; }
            let $progressTracker = $('#progress-tracker', $thisCanvas);
            let $zennyCounter = $('.counter.zenny', $progressTracker);
            $('.count', $zennyCounter).html(_worldPlayer.zenny.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }

        // Add the received items to our inventory
        if (typeof _worldPlayer.items[receiveItemToken] === 'undefined'){
            if (typeof _self.addItemToInventory === 'function') {
                _self.addItemToInventory(receiveItemToken, receiveQuantity, false, false);
                } else {
                _worldPlayer.items[receiveItemToken] = receiveQuantity;
                }
            } else {
            _worldPlayer.items[receiveItemToken] += receiveQuantity;
            }

        // Update the storage UI if the overview is currently expanded
        let $robotsOverview = _elements.robotsOverview;
        let updateStorageItemQty = function(itemToken) {
            let $itemInStorage = $('.storage-items .team-item[data-item="' + itemToken + '"]', $robotsOverview);
            if ($itemInStorage && $itemInStorage.length){
                let currentQuantity = _worldPlayer.items[itemToken] || 0;
                $itemInStorage.attr('data-quantity', currentQuantity);
                $itemInStorage.find('.quantity').html('&times; ' + currentQuantity);
                }
            };

        if (currencySubKind) { updateStorageItemQty(currencySubKind); }
        updateStorageItemQty(receiveItemToken);

        if (_self.robotsOverviewAPI && typeof _self.robotsOverviewAPI.refreshDetailsPanel === 'function'){
            _self.robotsOverviewAPI.refreshDetailsPanel();
            }

        // Format our read-out message
        let msgReceive = receiveQuantity + ' ' + receiveItemName + (receiveQuantity > 1 && !receiveItemName.endsWith('s') ? 's' : '');
        let msgCost = effectivePrice + ' ' + (currencySubKind ? currencySubKind.replace(/-/g, ' ') : currencyKind);
        msgCost = msgCost.replace(/\b\w/g, l => l.toUpperCase()) + (effectivePrice > 1 && !msgCost.endsWith('s') ? 's' : '');

        // Auto-dismiss the action dropdown after a short delay so the button has time to flash
        setTimeout(function(){
            _self.hideActionDropdown(false);
            _self.playSoundEffect('get-item');
            _self.showWorldMessage([
                'Trade successful!',
                'Traded ' + msgCost + ' for ' + msgReceive + '.'
                ]);
            }, 600);

        _self.saveWorldState();
        }

    return true;
    }

// Assign the sub-functions to the main class's prototype
mmrpgWorldMap.prototype.prepareActorEventOptions = prepareActorEventOptions;
mmrpgWorldMap.prototype.handleActorActionClick = handleActorActionClick;
