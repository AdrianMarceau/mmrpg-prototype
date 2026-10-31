
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
                    //console.log('checking if reset-world-pickups allowed ...');
                    //console.log('_config.mapHasPickups =', _config.mapHasPickups);
                    if (!_config.mapHasPickups){ continue; }
                    }
                else if (token === 'reset-world-encounters'){
                    //console.log('checking if reset-world-encounters allowed ...');
                    //console.log('_config.mapHasEncounters =', _config.mapHasEncounters);
                    if (!_config.mapHasEncounters){ continue; }
                    }

                let enabled = true;
                let currency = typeof action[3] !== 'undefined' && action[3] ? action[3] : false;
                let price = typeof action[4] !== 'undefined' && action[4] ? action[4] : false;
                let innerHtml = '<span>' + label + '</span>';

                if (currency && price){
                    let currencyCheck = _self.checkMenuButtonCurrency(currency, price);
                    enabled = currencyCheck.hasEnough;
                    innerHtml = '<span class="has-sprite">'
                        + '<strong>' + label + '</strong> '
                        + '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> '
                        + currencyCheck.spriteMarkup
                        + '</span>';
                    }

                if (enabled){ actionData.sideButtonsMarkup += '<a class="button big-button'+(colour ? ' type '+colour : '')+'" data-actor="'+dataActor+'" data-action="'+token+'">' + innerHtml + '</a>'; }
                else { actionData.sideButtonsMarkup += '<a class="button big-button'+(colour ? ' type '+colour : '')+' disabled">' + innerHtml + '</a>'; }
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

    let isShop = action.indexOf('-shop') !== -1 || action.indexOf('shop-') !== -1;
    let isAdvice = action.indexOf('-advice') !== -1 || action.indexOf('advice-') !== -1;
    let isReset = action === 'reset-world-pickups' || action === 'reset-world-encounters';

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

    return true;
    }

// Assign the sub-functions to the main class's prototype
mmrpgWorldMap.prototype.prepareActorEventOptions = prepareActorEventOptions;
mmrpgWorldMap.prototype.handleActorActionClick = handleActorActionClick;
