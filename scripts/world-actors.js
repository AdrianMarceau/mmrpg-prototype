
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
        if (!dataLabel){ dataLabel = 'Denizen'; }
        if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }

        if (actorInfo.messages){
            let avatarSprite = '&hellip;';
            if (actorInfo.kind === 'player'){ avatarSprite = _self.getPlayerSpriteMarkup(actorInfo.sprite, {alt: actorInfo.image, kind: 'mug'}); }
            else if (actorInfo.kind === 'robot'){ avatarSprite = _self.getRobotSpriteMarkup(actorInfo.sprite, {alt: actorInfo.image, kind: 'mug'}); }
            let messagesText = typeof actorInfo.messages === 'object' ? actorInfo.messages.join('\n') : actorInfo.messages;
            messagesText = messagesText.replaceAll('//', '<br />');
            messagesText = messagesText.replaceAll('{player_name}', _playerIndexInfo['name']);
            actionData.sideButtonsMarkup += '<div class="button-title big-button-title'+(dataColour ? ' type '+dataColour : '')+'">';
                actionData.sideButtonsMarkup += '<i class="icon">' + avatarSprite + '</i>';
                actionData.sideButtonsMarkup += '<p class="text">' + messagesText + '</p>';
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

        let currentKey = typeof _self.currentAdviceKey !== 'undefined' ? _self.currentAdviceKey : -1;
        let randomKey;
        if (possibleMessages.length === 1){
            randomKey = 0;
            } else {
            do { randomKey = Math.floor(Math.random() * possibleMessages.length) + 0; }
            while (randomKey === currentKey);
            }

        _self.currentAdviceKey = randomKey;
        let selectedMessageText = possibleMessages[randomKey];
        _elements.sideButtons.find('.text').html(selectedMessageText);
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
