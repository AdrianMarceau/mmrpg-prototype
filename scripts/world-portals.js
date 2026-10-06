// -- WORLD PORTAL METHODS -- //

// Define a quick function for parsing a portal event on the map and returning action dropdown options
function preparePortalEventOptions(firstEvent, options){
    //console.log('%c' + 'mmrpgWorldMap.preparePortalEventOptions()', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _worldCursor = _world.cursor;

    options = options || {};
    let stillAtPosition = typeof options.stillAtPosition === 'function' ? options.stillAtPosition : function(){ return true; };
    let otherMenusActiveNow = typeof options.otherMenusActiveNow === 'function' ? options.otherMenusActiveNow : function(){ return false; };

    let $portalEvent = $(firstEvent.sprite);
    let dataLabel = $portalEvent.attr('data-label');
    let dataPortal = $portalEvent.attr('data-portal');
    let portalInfo = dataPortal ? (_config.mapPortalsIndex[dataPortal] || false) : false;

    let actionData = {
        showActionArea: false,
        actionAreaMarkup: '',
        sideButtonsMarkup: '',
        showActionAreaType: '',
        zoomTimeoutDuration: 0,
        triggerEffect: false,
        triggerEffectSound: '',
        readyTeamSprites: false,
        readyTeamSpritesAnyway: false,
        teamReadyDuration: 0,
        triggerEffectFunction: function(){},
        autoRedirect: false,
        autoRedirectURL: '',
        autoRedirectSound: '',
        autoRedirectEffect: '',
        autoRedirectAnimation: ''
    };

    let goToDestination = false;
    let goToWorld, goToMap, goToPosition;
    let goToSameWorld, goToSameMap, goToSamePosition;

    if (dataPortal.indexOf('goto__') !== -1){
        goToDestination = true;
        goToWorld = _config.mapWorld;
        goToMap = dataPortal.replace(/^goto__/i, '');
        if (goToMap.indexOf('__') !== -1){
            let gtm = goToMap.split('__');
            if (gtm.length >= 3){ goToWorld = gtm[0]; goToMap = gtm[1]; goToPosition = gtm[2]; }
            else if (gtm.length >= 2){ goToWorld = gtm[0]; goToMap = gtm[1]; }
            }
        if (portalInfo && portalInfo['dst']){ goToPosition = portalInfo['dst']; }
        } else if (portalInfo['dst']){
        goToDestination = true;
        goToWorld = _config.mapWorld;
        goToMap = portalInfo['dst'];
        if (goToMap.indexOf('__') !== -1){
            let gtm = goToMap.split('__');
            if (gtm.length >= 3){ goToWorld = gtm[0]; goToMap = gtm[1]; goToPosition = gtm[2]; }
            else if (gtm.length >= 2){ goToMap = gtm[0]; goToPosition = gtm[1]; }
            }
        }

    goToSameWorld = goToWorld === _config.mapWorld ? true : false;
    goToSameMap = goToSameWorld && goToMap === _config.mapToken ? true : false;
    goToSamePosition = goToSameWorld && goToSameMap && goToPosition === _worldCursor.position ? true : false;

    if (portalInfo && goToDestination){
        actionData.showActionArea = true;
        if (!dataLabel){ dataLabel = 'Portal Options'; }
        actionData.actionAreaMarkup = '<strong class="label">' + dataLabel + '</strong>';
        if (dataPortal === 'exit'){ actionData.sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Return Home</span></a>'; }
        else { actionData.sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Enter Teleport</span></a>'; }
        actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
        actionData.showActionAreaType = 'portal';
        actionData.zoomTimeoutDuration = 500;

        // Automatically redirect to this portal if cursor has moved at least once
        if (_worldCursor.moved && !portalInfo.confirm){
            if (dataPortal === 'spawn'){
                console.warn('-> spawn portals not yet implemented yet');
                }
            else if (dataPortal === 'exit'){
                actionData.autoRedirect = true;
                actionData.showActionArea = false;
                actionData.autoRedirectURL = 'prototype.php';
                _world.isBusyWith.redirectToLocation = true;
                }
            else {
                // GOTO PORTAL - use the world, map, and position info collected earlier to redirect
                if (goToSameWorld && goToSameMap && !goToSamePosition){
                    actionData.triggerEffect = true;
                    actionData.showActionArea = false;
                    _world.isBusyWith.teleporting = true;
                    actionData.triggerEffectFunction = function(){
                        if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                        _config.allowWorldEvents = false; // prevent re-triggering events during teleport
                        if (actionData.triggerEffectSound){ _self.playSoundEffect(actionData.triggerEffectSound); }
                        _self.moveToPosition(goToPosition, function(){
                            _config.allowWorldEvents = true; // re-allow world events after teleport complete
                            delete _world.isBusyWith.teleporting; // delete the teleporing flag to allow movement
                            return true;
                            });
                        };
                    actionData.triggerEffectSound = 'bounce-sound';
                    actionData.readyTeamSprites = true;
                    }
                else {
                    actionData.autoRedirect = true;
                    actionData.showActionArea = false;
                    actionData.autoRedirectURL = 'world.php?world=' + goToWorld;
                    if (goToMap){ actionData.autoRedirectURL += '&map='+goToMap; }
                    if (goToPosition){ actionData.autoRedirectURL += '&position='+goToPosition; }
                    _world.isBusyWith.redirectToLocation = true;
                    actionData.readyTeamSprites = true;
                    actionData.readyTeamSpritesAnyway = true;
                    actionData.teamReadyDuration = 600; 
                    let isExitPortal = portalInfo.direction ? true : false;
                    let isTelePortal = !portalInfo.direction ? true : false;
                    let isSubTelePortal = goToMap && goToMap.indexOf('-area-0') !== -1 ? true : false;
                    actionData.autoRedirectSound = isExitPortal ? 'lets-go-robots' : 'intense-growing-sound';
                    actionData.autoRedirectEffect = isExitPortal ? 'leaving' : 'glowing';
                    if (isExitPortal){ actionData.autoRedirectAnimation = 'slide-' + portalInfo.direction; }
                    else if (isTelePortal){ actionData.autoRedirectAnimation = isSubTelePortal ? 'descend-downward' : 'ascend-upward'; }
                    }
                }
            }
        else if (!portalInfo.confirm){
            actionData.showActionArea = false;
            }
        }

    return actionData;
    }

// Define a quick function for handling portal interactions via the dropdown
function handlePortalActionClick(action, $button, firstEvent){
    //console.log('%c' + 'mmrpgWorldMap.handlePortalActionClick(action:' + action + ')', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let $thisWorld = _elements.world;
    let $teamSprites = _elements.teamSprites;

    let portalName = $button.attr('data-portal') || false;

    if (action === 'portal-info'){
        alert('Portal Name: ' + portalName + '\n\nThis is where you would show portal details.');
        }
    else if (action === 'enter-portal'){
        let portalsIndex = _config.mapPortalsIndex;
        let portalInfo = portalsIndex[portalName] || false;
        let portalHref = false;
        let isSameMapTeleport = false;
        let goToPosition = false;
        let finalGoToMap = false; // Track this for sub-teleportal animations

        if (portalName === 'spawn'){
            portalHref = 'prototype.php'; 
            } else if (portalName === 'exit'){
            portalHref = 'prototype.php'; 
            } else if (portalName.indexOf('goto__') !== -1){
            let worldToken, mapToken, posToken;
            let goToPath = portalName.replace(/^goto__/i, '').split('__');
            if (goToPath.length >= 3){ worldToken = goToPath[0]; mapToken = goToPath[1]; posToken = goToPath[2]; }
            else if (goToPath.length >= 2){ worldToken = goToPath[0]; mapToken = goToPath[1]; }
            else { worldToken = _config.mapWorld; mapToken = goToPath[0]; }
            finalGoToMap = mapToken;
            portalHref = 'world.php?world=' + worldToken + '&map=' + mapToken;
            if (posToken){ portalHref += '&position=' + posToken; }
            } else if (portalInfo && portalInfo.dst){
            let goToWorld = _config.mapWorld;
            let goToMap = portalInfo.dst;
            if (goToMap.indexOf('__') !== -1){
                let gtm = goToMap.split('__');
                if (gtm.length >= 3){ goToWorld = gtm[0]; goToMap = gtm[1]; goToPosition = gtm[2]; }
                else if (gtm.length >= 2){ goToMap = gtm[0]; goToPosition = gtm[1]; }
                }
            finalGoToMap = goToMap;
            let goToSameWorld = goToWorld === _config.mapWorld;
            let goToSameMap = goToSameWorld && goToMap === _config.mapToken;
            if (goToSameWorld && goToSameMap){
                isSameMapTeleport = true;
                } else {
                portalHref = 'world.php?world=' + goToWorld + '&map=' + goToMap;
                if (goToPosition){ portalHref += '&position=' + goToPosition; }
                }
            }

        // Route the player using either same-map movement or a full page redirect
        if (isSameMapTeleport && goToPosition){
            _self.hideActionDropdown(false);
            _self.playSoundEffect('bounce-sound');
            _config.allowWorldEvents = false; // prevent re-triggering events during teleport
            _self.moveToPosition(goToPosition, function(){
                _config.allowWorldEvents = true; // re-allow world events after teleport complete
                delete _world.isBusyWith.teleporting; // delete the teleport flag to allow movement
                return true;
                });
            } else if (portalHref){
            let isExitPortal = portalInfo && portalInfo.direction ? true : false;
            let isTelePortal = portalInfo && !portalInfo.direction ? true : false;
            let isSubTelePortal = finalGoToMap && finalGoToMap.indexOf('-area-0') !== -1 ? true : false;
            
            let autoRedirectSound = isExitPortal ? 'lets-go-robots' : 'intense-growing-sound';
            let autoRedirectEffect = isExitPortal ? 'leaving' : 'glowing';
            let autoRedirectAnimation = '';
            if (isTelePortal){ autoRedirectAnimation = isSubTelePortal ? 'descending' : 'ascending'; }
            
            _self.playSoundEffect(autoRedirectSound);
            $teamSprites.removeClass('shake bounce idle').addClass(autoRedirectEffect);
            if (autoRedirectAnimation){ $teamSprites.addClass(autoRedirectAnimation); }
            
            _self.incZoomLevel();
            $thisWorld.addClass('busy');
            _self.saveWorldState(function(){
                _self.incZoomLevel();
                $thisWorld.addClass('loading');
                window.location.href = portalHref;
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
mmrpgWorldMap.prototype.preparePortalEventOptions = preparePortalEventOptions;
mmrpgWorldMap.prototype.handlePortalActionClick = handlePortalActionClick;
