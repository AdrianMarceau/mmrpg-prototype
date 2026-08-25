
// -- WORLD EVENT METHODS -- //

// Quick function for triggering a world event (lol) and any effects that may occur
function triggerWorldEvent(eventAction, eventData, $eventSprite){
    //console.log('%c' + 'mmrpgWorldMap.triggerWorldEvent(' + eventAction + ', ' + eventData + ', $eventSprite:' + typeof $eventSprite + ')', 'color: magenta;');
    if (!eventAction || typeof eventAction !== 'string' || !eventAction.length){ console.error('triggerWorldEvent() missing required eventAction!'); return false; }
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _mapMessagesConfig = _config.mapMessages;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let $canvasMap = _elements.map;
    let $teamSprites = _elements.teamSprites;
    // If the cursor hasn't moved yet, we shouldn't be processing anything
    if (!_worldCursor.moved){
        console.warn('triggerWorldEvent() called but cursor has not moved yet!');
        return false;
        }
    // Check to see if the event is valid
    let allowedActions = ['trigger-effects'];
    if (allowedActions.indexOf(eventAction) === -1){
        console.error('triggerWorldEvent() invalid eventAction provided: ' + eventAction);
        return false;
        }
    // Define an inline function for processing the different event actions possible
    let actionsCompleted = 0;
    let processEventAction = function(eventAction, onComplete, afterDelay){
        if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
        if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
        // Process the event action based on it's token
        if (eventAction === 'trigger-effects'){
            //console.log('-> triggering effects for event with data:', eventData);
            _self.playSoundEffect('use-recovery-item');
            //let _playerRobots = _config.playerRobots || [];
            //let _allPlayerRobots = Object.keys(_worldPlayer.robots) || [];
            let _playerRobots = _worldPlayer.robots || {};
            let _playerRobotsIndex = _config.playerRobotsIndex || {};
            //let _allPlayerRobots = Object.keys(_playerRobots) + Object.keys(_playerRobotsIndex);
            let _allPlayerRobots = [...new Set([...Object.keys(_playerRobots), ...Object.keys(_playerRobotsIndex)])];
            let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
            let eventEffects = Object.values(eventData);
            for (let i = 0; i < eventEffects.length; i++){
                let effect = eventEffects[i];
                //console.log('-> effect =', effect);
                if (!effect){ continue; }
                // If this is a team-wide effect, we're going to have to loop
                if (effect.indexOf('-team-') !== -1){
                    // If this is a cursor player, we don't have a team
                    if (playerIsCursor){ continue; }
                    //console.log('%c' + '-> team-effect via event panel: ' + effect, 'color: lime;');
                    for (let j = 0; j < _allPlayerRobots.length; j++){
                        let robotString = _allPlayerRobots[j];
                        let robotInfo = _playerRobots[robotString] ? _playerRobots[robotString] : _playerRobotsIndex[robotString];
                        //console.log('-> checking if ', robotString, ' needs ' + effect + ' w/ robotInfo =', robotInfo);
                        _mapMessagesConfig.nextQueueStagger = Math.ceil(_mapMessagesConfig.queueStagger / _allPlayerRobots.length);
                        // If this is a RESTORE TEAM ENERGY effect, let's process that now
                        if (effect === 'restore-team-energy'
                            && robotInfo.energyPercent !== 100){
                            //console.log('%c' + '-> restoring energy for ' + robotString + ' via event panel', 'color: #64a455;');
                            _self.restoreRobotEnergy(robotString, true);
                            _self.playSoundEffect('recovery-energy');
                            actionsCompleted++;
                            }
                        // If this is a RESTORE TEAM WEAPONS effect, let's process that now
                        if (effect === 'restore-team-weapons'
                            && robotInfo.weaponsPercent !== 100){
                            //console.log('%c' + '-> restoring weapons for ' + robotString + ' via event panel', 'color: #3d7cbe;');
                            _self.restoreRobotWeapons(robotString, true);
                            _self.playSoundEffect('recovery-weapons');
                            actionsCompleted++;
                            }
                        // If this is a RESET TEAM ATTACK effect, let's process that now
                        if (effect === 'reset-team-attack'
                            && robotInfo.attackMods !== 0){
                            //console.log('%c' + '-> resetting attack for ' + robotString + ' via event panel', 'color: #8b5050;');
                            _self.resetRobotAttack(robotString, false);
                            _self.playSoundEffect('small-buff-received');
                            actionsCompleted++;
                            }
                        // If this is a RESET TEAM DEFENSE effect, let's process that now
                        if (effect === 'reset-team-defense'
                            && robotInfo.defenseMods !== 0){
                            //console.log('%c' + '-> resetting ' + robotString + ' defense for event panel', 'color: #50638a;');
                            _self.resetRobotDefense(robotString, false);
                            _self.playSoundEffect('small-buff-received');
                            actionsCompleted++;
                            }
                        // If this is a RESET TEAM SPEED effect, let's process that now
                        if (effect === 'reset-team-speed'
                            && robotInfo.speedMods !== 0){
                            //console.log('%c' + '-> resetting ' + robotString + ' speed for event panel', 'color: #8b739b;');
                            _self.resetRobotSpeed(robotString, false);
                            _self.playSoundEffect('small-buff-received');
                            actionsCompleted++;
                            }
                        }
                    }
                }
            }
        // If an onComplete function was provided, call it now (with delay if requested)
        if (onComplete){
            if (!afterDelay){ onComplete.call(_self, eventAction, eventData, $eventSprite); }
            else { setTimeout(function(){ onComplete.call(_self, eventAction, eventData, $eventSprite); }, afterDelay); }
            }
        };
    // Now process the event given the action and data provided after some visual fluff
    let delayTime = 1000;
    if (actionsCompleted){ _self.incZoomLevel(); }
    setTimeout(function(){
        processEventAction(eventAction, function(){
            if (actionsCompleted){ _self.resetZoomLevel(); }
            $teamSprites.removeClass('shake');
            $teamSprites.filter(':not(.disabled):not(.frame-lock)').attr('data-frame', '00');
            _self.triggerWindowEventsPull();
            }, delayTime);
        }, delayTime);
    // Return true on success
    return true;
    }

// Quick function for triggering a drop zone event (if available) when an item, ability, or other compatible item is dropped there
function triggerDropZoneEvent(eventName, eventInfo, objectKind, objectName, objectInfo){
    //console.log('%c' + 'mmrpgWorldMap.triggerDropZoneEvent(' + eventName + ', ' + objectKind + ', ' + objectName + ')', 'color: magenta;');
    if (!eventName || typeof eventName !== 'string' || !eventName.length){ console.error('triggerDropZoneEvent() missing required eventName!'); return false; }
    if (!eventInfo || typeof eventInfo !== 'object' || !Object.keys(eventInfo).length){ console.error('triggerDropZoneEvent() missing required eventInfo!'); return false; }
    if (!objectKind || typeof objectKind !== 'string' || !objectKind.length){ console.error('triggerDropZoneEvent() missing required objectKind!'); return false; }
    else if (objectKind !== 'item' && objectKind !== 'ability'){ console.error('triggerDropZoneEvent() invalid objectKind provided: ' + objectKind); return false; }
    if (!objectName || typeof objectName !== 'string' || !objectName.length){ console.error('triggerDropZoneEvent() missing required objectName!'); return false; }
    if (!objectInfo || typeof objectInfo !== 'object' || !Object.keys(objectInfo).length){ console.error('triggerDropZoneEvent() missing required objectInfo!'); return false; }
    if (!objectInfo.token || typeof objectInfo.token !== 'string' || !objectInfo.token.length){ console.error('triggerDropZoneEvent() missing required objectInfo.token!'); return false; }
    let rawObjectToken = objectInfo.token;
    let objectToken = rawObjectToken.indexOf('__') !== -1 ? rawObjectToken.split('__')[0] : rawObjectToken;
    //console.log('-> eventName =', eventName);
    //console.log('-> eventInfo =', eventInfo);
    //console.log('-> objectKind =', objectKind);
    //console.log('-> objectName =', objectName);
    //console.log('-> objectInfo =', objectInfo);
    //console.log('-> objectToken =', objectToken);
    //console.log('-> rawObjectToken =', rawObjectToken);
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let $canvasMap = _elements.map;
    let $eventSprite = $('.sprite[data-event="' + eventName + '"]', $canvasMap);
    if (!$eventSprite || !$eventSprite.length){ console.error('triggerDropZoneEvent() could not find event sprite for eventName: ' + eventName); return false; }
    // Collect the parameters from the event data
    let dropFilter = eventInfo.data[0] || false;
    let dropAction = eventInfo.data[1] || false;
    //console.log('-> dropFilter =', dropFilter);
    //console.log('-> dropAction =', dropAction);
    if (!dropFilter){ console.error('triggerDropZoneEvent() invalid dropFilter provided in eventInfo.data[0]: ' + dropFilter); return false; }
    if (!dropAction){ console.error('triggerDropZoneEvent() invalid dropAction provided in eventInfo.data[1]: ' + dropAction); return false; }
    dropFilter = dropFilter.indexOf(':') !== -1 ? dropFilter.split(':') : [dropFilter];
    let dropFilterKind = (dropFilter[0] || '');
    let dropFilterValues = (dropFilter[1] || '').split(',');
    //console.log('-> dropFilterKind =', dropFilterKind);
    //console.log('-> dropFilterValues =', dropFilterValues);
    //console.log('-> vs. objectKind =', objectKind);
    //console.log('-> vs. objectToken =', objectToken);
    // If the filter kind was not empty, and the provided objected does not match it, return now
    if (dropFilterKind.length){
        if (dropFilterKind !== objectKind){
            console.warn('triggerDropZoneEvent() exiting early because objectKind does not match dropFilterKind!');
            return false;
            }
        if (dropFilterValues.length && dropFilterValues.indexOf(objectToken) === -1){
            console.warn('triggerDropZoneEvent() exiting early because objectInfo.token does not match dropFilterValues!');
            return false;
            }
        }
    //console.log('-> drop action is valid, processing ...');
    // Define an inline function for processing the different drop actions possible
    let actionsCompleted = 0;
    let processDropAction = function(dropAction, onComplete, afterDelay){
        if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
        if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
        // Process the drop action based on it's token
        if (dropAction === 'activate-player-platform'){
            //console.log('-> this drop zone is a player-platform awaiting activation...');
            //console.log('-> activating player platform for player', _worldPlayer.token, 'at position', _worldPlayer.position);
            $eventSprite.addClass('active');
            eventInfo.active = true;
            objectInfo.locked = true;
            _self.refreshPlayerPlatforms();
            actionsCompleted++;
            }
        // If an onComplete function was provided, call it now (with delay if requested)
        if (onComplete){
            if (!afterDelay){ onComplete.call(_self, dropAction, eventInfo, objectKind, objectName, objectInfo); }
            else { setTimeout(function(){ onComplete.call(_self, dropAction, eventInfo, objectKind, objectName, objectInfo); }, afterDelay); }
            }
        };
    // Now process the drop action given the action and data provided after some visual fluff
    let delayTime = 1000;
    if (actionsCompleted){ _self.incZoomLevel(); }
    setTimeout(function(){
        processDropAction(dropAction, function(){
            if (actionsCompleted){ _self.resetZoomLevel(); }
            _self.triggerWindowEventsPull();
            }, delayTime);
        }, delayTime);
    // Return true on success
    return true;
    }

// Quick function for triggering a drop zone empty event (if necessary) when an item, ability, or other item is removed from this location
function triggerDropZoneEmpty(eventName, eventInfo){
    //console.log('%c' + 'mmrpgWorldMap.triggerDropZoneEmpty(' + eventName + ')', 'color: magenta;');
    if (!eventName || typeof eventName !== 'string' || !eventName.length){ console.error('triggerDropZoneEmpty() missing required eventName!'); return false; }
    if (!eventInfo || typeof eventInfo !== 'object' || !Object.keys(eventInfo).length){ console.error('triggerDropZoneEmpty() missing required eventInfo!'); return false; }
    let rawObjectToken = eventInfo.data[0] || false;
    let objectToken = rawObjectToken && rawObjectToken.indexOf('__') !== -1 ? rawObjectToken.split('__')[0] : rawObjectToken;
    //console.log('-> eventName =', eventName);
    //console.log('-> eventInfo =', eventInfo);
    //console.log('-> objectToken =', objectToken);
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let $canvasMap = _elements.map;
    let $eventSprite = $('.sprite[data-event="' + eventName + '"]', $canvasMap);
    if (!$eventSprite || !$eventSprite.length){ console.error('triggerDropZoneEvent() could not find event sprite for eventName: ' + eventName); return false; }
    // Collect the parameters from the event data
    let dropFilter = eventInfo.data[0] || false;
    let dropAction = eventInfo.data[1] || false;
    //console.log('-> dropFilter =', dropFilter);
    //console.log('-> dropAction =', dropAction);
    if (!dropFilter){ console.error('triggerDropZoneEvent() invalid dropFilter provided in eventInfo.data[0]: ' + dropFilter); return false; }
    if (!dropAction){ console.error('triggerDropZoneEvent() invalid dropAction provided in eventInfo.data[1]: ' + dropAction); return false; }
    // We don't really care about the filter, but we do care about the action so let's reverse it if it exists
    //console.log('-> drop action is valid, reverting ...');
    // Define an inline function for reverting the different drop actions possible
    let actionsReverted = 0;
    let processDropRevert = function(dropAction, onComplete, afterDelay){
        if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
        if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
        // Process the drop action based on it's token
        if (dropAction === 'activate-player-platform'){
            //console.log('-> this drop zone is a player-platform awaiting deactivation...');
            //console.log('-> deactivating player platform for player', _worldPlayer.token, 'at position', _worldPlayer.position);
            $eventSprite.removeClass('active');
            eventInfo.active = false;
            _self.refreshPlayerPlatforms();
            actionsReverted++;
            }
        // If an onComplete function was provided, call it now (with delay if requested)
        if (onComplete){
            if (!afterDelay){ onComplete.call(_self, dropAction, eventInfo); }
            else { setTimeout(function(){ onComplete.call(_self, dropAction, eventInfo); }, afterDelay); }
            }
        };
    // Now process the drop action given the action and data provided after some visual fluff
    let delayTime = 1000;
    if (actionsReverted){ _self.incZoomLevel(); }
    setTimeout(function(){
        processDropRevert(dropAction, function(){
            if (actionsReverted){ _self.resetZoomLevel(); }
            _self.triggerWindowEventsPull();
            }, delayTime);
        }, delayTime);
    // Return true on success
    return true;
    }

// Define a quick functino for polling the server for new events (but only if we can actually show them)
function triggerWindowEventsPull(afterDelay){
    //console.log('%c' + 'mmrpgWorldMap.triggerWindowEventsPull()', 'color: magenta;');
    afterDelay = typeof afterDelay === 'number' ? afterDelay : 1000; // default to zero if not provided
    //console.log('queuing the windowEventsPull event (via world)');
    if (typeof window.top.mmrpg_queue_for_game_start !== 'undefined'){
        //console.log('i guess we wait for the game to start via parent.mmrpg_queue_for_game_start()', parent.mmrpg_queue_for_game_start);
        window.top.mmrpg_queue_for_game_start(function(){
            //console.log('i guess the game has started');
            setTimeout(function(){
                //console.log('attempting to pull window events via parent.windowEventsPull()', parent.windowEventsPull);
                let result = parent.windowEventsPull(true);
                if (result < 0){ console.error('windowEventsPull returned an error code: ' + result); }
                //else { console.log('windowEventsPull returned successfully: ' + result); }
                }, afterDelay);
            });
        }
    else if (typeof window.top.windowEventsPull !== 'undefined'){
        //console.log('i guess we pull events manually via parent.windowEventsPull()', parent.windowEventsPull);
        setTimeout(function(){
            let result = parent.windowEventsPull(true);
            if (result < 0){ console.error('windowEventsPull returned an error code: ' + result); }
            //else { console.log('windowEventsPull returned successfully: ' + result); }
            }, afterDelay);
        }
    else {
        //console.warn('could not find a way to pull window events from the parent window!');
        }
    // Return no specific result
    return;
    }

// Quick function that, given a column and row returns any events on or around that position on the map
function getEventsAtPosition(searchPosition, searchDirection, searchRadius, includeLocked){
    //console.log('%c' + 'mmrpgWorldMap.getEventsAtPosition(searchPosition:' + searchPosition + ', searchRadius:' + searchRadius + ')', 'color: magenta;');
    if (!searchPosition || (typeof searchPosition !== 'string' && !Array.isArray(searchPosition))){ console.error('getEventsAtPosition() missing or invalid searchPosition!'); return false; }
    if (!searchDirection || (typeof searchDirection !== 'string' && !Array.isArray(searchDirection))){ console.error('getEventsAtPosition() missing or invalid searchDirection!'); return false; }
    searchPosition = typeof searchPosition !== 'string' ? searchPosition.join('-') : searchPosition; // join if provided as array
    searchDirection = typeof searchDirection !== 'string' ? searchDirection.join('-') : searchDirection; // join if provided as array
    searchRadius = typeof searchRadius === 'number' ? searchRadius : 1; // default to one if not provided
    includeLocked = typeof includeLocked === 'boolean' ? includeLocked : false; // default to false if not provided
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let $canvasMap = _elements.map;
    // Collect the player's position and direction then determine field of view
    let standingAtPosition = _worldCursor.position;
    let lookingInDirection = _worldCursor.direction;
    let lookingAtPosition = _self.getRelativePositionByDirection(standingAtPosition, lookingInDirection);
    // Define an array to hold the events at this position
    let eventsAtPosition = [];
    // If we haven't actually moved/othered yet, don't return anything yet
    if (!_worldCursor.moved && !_worldCursor.othered){ return eventsAtPosition; }
    // Define an array to hold positions and always check the exact position first
    let positionsToCheck = [];
    positionsToCheck.push(searchPosition);
    // If a search radius is provided, add the surrounding positions to check
    // including diagonal positions
    if (searchRadius > 0){
        let searchCol = parseInt(searchPosition.split('-')[0]);
        let searchRow = parseInt(searchPosition.split('-')[1]);
        for (let colOffset = -searchRadius; colOffset <= searchRadius; colOffset++){
            for (let rowOffset = -searchRadius; rowOffset <= searchRadius; rowOffset++){
                if (colOffset === 0 && rowOffset === 0){ continue; } // skip the center position
                let newCol = searchCol + colOffset;
                let newRow = searchRow + rowOffset;
                if (newCol < 1 || newRow < 1){ continue; } // skip invalid positions
                positionsToCheck.push(newCol + '-' + newRow);
                }
            }
        }
    //console.log('-> positionsToCheck =', positionsToCheck);
    //console.log('-> _worldCursor.moved =', _worldCursor.moved);
    //console.log('-> _worldCursor.othered =', _worldCursor.othered);
    let eventKinds = ['event', 'portal', 'button', 'switch', 'gate', 'lock', 'block', 'hazard', 'battle', 'actor', 'item', 'ability'];
    let eventKindsPlural = ['events', 'portals', 'buttons', 'switches', 'gates', 'locks', 'blocks', 'hazards', 'battles', 'actors', 'items', 'abilities'];
    for (let e = 0; e < eventKinds.length; e++){
        let eventKind = eventKinds[e];
        let eventKindPlural = eventKindsPlural[e];
        //console.log('checking for ' + eventKind + '/' + eventKindPlural + ' at: ' + positionsToCheck.join(', '));
        // ie: mapKindSymbols
        let symbolsKey = 'map' + (eventKind[0].toUpperCase() + eventKind.slice(1)) + 'Symbols';
        let indexKey = 'map' + (eventKindPlural[0].toUpperCase() + eventKindPlural.slice(1)) + 'Index';
        let eventSymbols = _config.hasOwnProperty(symbolsKey) ? _config[symbolsKey] : false;
        let eventsIndex = _config.hasOwnProperty(indexKey) ? _config[indexKey] : false;
        let eventKeys = eventSymbols ? Object.keys(eventSymbols) : [];
        //console.log('-> symbolsKey =', symbolsKey);
        //console.log('-> indexKey =', indexKey);
        //console.log('-> eventSymbols =', eventSymbols);
        //console.log('-> eventsIndex =', eventsIndex);
        //console.log('-> eventKeys =', eventKeys);
        //console.log('-> ' + symbolsKey + ' =', eventSymbols);
        //console.log('-> ' + symbolsKey + ' =', eventKeys);
        if (!eventKeys.length){ continue; }
        // Loop through the event symbols and check if any of them are at the positions to check
        for (let i = 0; i < positionsToCheck.length; i++){
            let eventKey = i;
            let eventPosition = positionsToCheck[i];
            let eventPositionXY = eventPosition.split('-');
            //console.log('-> checking ' + symbolsKey + ' for ' + eventPosition);
            if (!eventSymbols[eventPosition]){ continue; } // skip if no event symbols at this position
            let eventToken = eventSymbols[eventPosition];
            let eventInfo = eventsIndex[eventToken];
            let isSamePosition = eventPosition === standingAtPosition;
            let isFacingPosition = eventPosition === lookingAtPosition;
            //console.log('-> checking ' + eventKind + ' at position ' + eventPosition + ' for token ' + eventToken, ' and info ', eventInfo);
            if (!eventToken || !eventInfo){ console.warn('-> no event token or info found for ' + eventKind + ' at position ' + eventPosition + ', skipping!'); continue; }
            //console.log('-> found ' + eventKind + ' at position ' + eventPosition + ' with token ' + eventToken, eventInfo);
            if (eventInfo.disabled){ continue; }
            if (eventInfo.beingHeld){ continue; }
            if (eventInfo.locked && !includeLocked && eventKind !== 'lock'){ continue; }
            if (eventInfo.action === 'drop-zone'){ continue; }
            let $eventSprite = $('.sprite[data-' + eventKind + '="'+eventToken+'"]', $canvasMap);
            let eventLabel = $eventSprite.length ? $eventSprite.attr('data-label') : '';
            if ($eventSprite && $eventSprite.length){ $eventSprite = $eventSprite.first().get(0); }
            let eventKind2 = eventKind;
            if (eventKind === 'battle'){
                // make sure we take the secondary type (mecha/master/boss/rescue) as the second "kind"
                eventKind2 = eventInfo.kind2;
                }
            else if (eventKind === 'event'){
                // treat healpads and resetpads as sanctuary events
                if (eventInfo.sprite === 'healpad' || eventInfo.sprite === 'resetpad'){ eventKind2 = 'sanctuary'; }
                // otherwise it's just a generic custom event tile
                else { eventKind2 = 'custom'; }
                }
            else if (eventKind === 'portal'){
                // if we haven't moved, never trigger a portal
                if (!_worldCursor.moved){ continue; }
                // spawns are usually hidden behind other portals, never interactable directly
                if (eventToken === 'spawn' && eventKey > 0){ continue; }
                // if this portal has an assosiated direction, only trigger if player is facing that way
                if (eventInfo.direction
                    && typeof eventInfo.direction === 'string'
                    && eventInfo.direction !== _worldCursor.direction){
                    continue;
                    }
                }
            else if (eventKind === 'button'){
                // if the button has already been pushed (state:down), just continue
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (eventInfo.state === 'down'){ continue; }
                if (!isSamePosition && !isFacingPosition){ continue; }
                }
            else if (eventKind === 'switch'){
                // if the switch has already been pushed (state:down), that's okay
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (!isSamePosition && !isFacingPosition){ continue; }
                }
            else if (eventKind === 'gate'){
                // if the gate has already been removed, just continue
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (eventInfo.removed){ continue; }
                //if (!isSamePosition && !isFacingPosition){ continue; }
                // collect the sprite as the second "kind"
                eventKind2 = eventInfo.sprite;
                }
            else if (eventKind === 'lock'){
                // if the lock has been removed entirely, just continue
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (eventInfo.removed){ continue; }
                //if (!isSamePosition && !isFacingPosition){ continue; }
                // collect the sprite as the second "kind"
                eventKind2 = eventInfo.sprite;
                }
            else if (eventKind === 'block' || eventKind === 'hazard'){
                // skip if block/hazard already removed by the player
                //console.log('Found ' + eventKind + ' event kind!', eventToken, '@', eventPosition);
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (eventInfo.removed){ continue; }
                if (!isSamePosition && !isFacingPosition){ continue; }
                //console.log('--> Yay! Found ' + eventKind + ' event kind ' + (isSamePosition ? 'at' : isFacingPosition ? 'in front of' : 'around') + ' current position!');
                //console.log('--> eventPosition: ', eventPosition);
                //console.log('--> eventToken: ', eventToken);
                //console.log('--> eventInfo: ', eventInfo);
                // collect the sprite as the second "kind"
                eventKind2 = eventInfo.sprite;
                }
            else if (eventKind === 'actor'){
                // skip if actor already removed for some reason
                //console.log('Found ' + eventKind + ' event kind!', eventToken, '@', eventPosition);
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (eventInfo.removed){ continue; }
                if (!isSamePosition && !isFacingPosition){ continue; }
                //console.log('--> Yay! Found ' + eventKind + ' event kind ' + (isSamePosition ? 'at' : isFacingPosition ? 'in front of' : 'around') + ' current position!');
                //console.log('--> eventPosition: ', eventPosition);
                //console.log('--> eventToken: ', eventToken);
                //console.log('--> eventInfo: ', eventInfo);
                // collect the sprite as the second "kind"
                eventKind2 = eventInfo.kind + '/' + eventInfo.sprite;
                }
            else if (eventKind === 'item' || eventKind === 'ability'){
                // skip if already claimed by the player
                //console.log('-> eventPosition: ', eventPosition);
                //console.log('-> eventToken: ', eventToken);
                //console.log('-> eventInfo: ', eventInfo);
                //console.log('-> eventsIndex: ', eventsIndex);
                if (eventInfo.claimed){ continue; }
                // collect the token as the second "kind"
                eventKind2 = eventInfo.token;
                }
            let eventAtPosition = {kind: eventKind, kind2: eventKind2, position: eventPosition, token: eventToken, sprite: $eventSprite, label: eventLabel};
            if (eventKind === 'item' || eventKind === 'ability'){ eventAtPosition.claimed = eventInfo.claimed; }
            //console.log('%c' + '> found valid '+ eventKind + '/'+ eventKind2 + ' at position ' + eventPosition, 'color: lime;');
            //console.log('--> eventToken =', eventToken);
            //console.log('--> eventInfo =', eventInfo);
            //console.log('--> eventAtPosition =', eventAtPosition);
            // skip portals unless it's the exact position
            let eventIsCustom = eventKind === 'event';
            let eventIsPortal = eventKind === 'portal';
            let eventIsSanctuary = eventKind2 === 'sanctuary';
            let eventIsPickup = eventKind === 'item' || eventKind === 'ability';
            let eventIsStar = eventKind === 'item' && eventInfo.token.indexOf('-star') !== -1;
            let eventIsGate = eventKind === 'gate';
            let eventIsLock = eventKind === 'lock';
            //console.log('--> eventIsCustom =', eventIsCustom, '| eventIsPortal =', eventIsPortal, '| eventIsSanctuary =', eventIsSanctuary, '| eventIsPickup =', eventIsPickup, '| eventIsStar =', eventIsStar);
            if (eventPosition !== searchPosition
                && (eventIsCustom || eventIsPortal || eventIsSanctuary || eventIsPickup)
                && !eventIsStar){
                // skip custom unless it's the exact position
                //console.log('----> skipping ' + eventKind + ' at ' + eventPosition + ' (' + eventToken + ') because it is not the exact position', '\n-> eventInfo =', eventInfo);
                continue;
                }
            // otherwise we are fine to add to the events array
            //console.log('--> adding ' + eventKind + ' at ' + eventPosition + ' to eventsAtPosition array', '\n--> w/ eventAtPosition = ', eventAtPosition);
            eventsAtPosition.push(eventAtPosition);
            }
        // Check if there is a battle anywhere in the collected events
        let hasBattleNearby = eventsAtPosition.some(function(event){ return event.kind === 'battle'; });
        // Check to see if the cursor is holding any events, if so they are "at this position"
        // (putting this last so that the cursor can't drop items where another event already is)
        //console.log('-> checking cursor holding for ' + eventKind + ' at position ' + searchPosition);
        //console.log('-> but only if no events found in eventsAtPosition =', eventsAtPosition.length, eventsAtPosition);
        //if (!eventsAtPosition.length && _worldCursor.holding && _worldCursor.holding.indexOf(eventKind + '/') === 0){
        if ((!eventsAtPosition.length || !hasBattleNearby)
            && _worldCursor.holding && _worldCursor.holding.indexOf(eventKind + '/') === 0){
            //console.log('-> cursor is holding an event of kind ' + eventKind + ', adding to eventsAtPosition array');
            let eventToken = _worldCursor.holding.split('/')[1] || false;
            let eventInfo = eventsIndex[eventToken] || false;
            //console.log('-> eventToken =', eventToken);
            //console.log('-> eventInfo =', eventInfo);
            let $eventSprite = $('.sprite[data-' + eventKind + '="'+eventToken+'"]', $canvasMap);
            let eventLabel = $eventSprite.length ? $eventSprite.attr('data-label') : '';
            if ($eventSprite && $eventSprite.length){ $eventSprite = $eventSprite.first().get(0); }
            let eventKind2 = eventKind;
            if (eventKind === 'battle'){
                // make sure we take the secondary type (mecha/master/boss/rescue) as the second "kind"
                eventKind2 = eventInfo.kind2;
                }
            else if (eventKind === 'event'){
                // treat healpads and resetpads as sanctuary events
                if (eventInfo.sprite === 'healpad' || eventInfo.sprite === 'resetpad'){ eventKind2 = 'sanctuary'; }
                // otherwise it's just a generic custom event tile
                else { eventKind2 = 'custom'; }
                }
            else if (eventKind === 'item' || eventKind === 'ability'){
                // skip if already claimed by the player
                if (eventInfo.claimed){ continue; }
                // collect the token as the second "kind"
                eventKind2 = eventInfo.token;
                }
            let eventAtPosition = {kind: eventKind, kind2: eventKind2, position: searchPosition, token: eventToken, sprite: $eventSprite, label: eventLabel};
            if (eventKind === 'item' || eventKind === 'ability'){ eventAtPosition.claimed = eventInfo.claimed; }
            eventsAtPosition.push(eventAtPosition);
            continue; // skip the rest of this loop
            }
        }

    // Now that we have our events, we need to do some serious sorting given the direction we're facing
    // Most important is the first event, so decide which panel we're "facing" given direction and work out way outward
    let searchPositionXY = searchPosition.split('-').map(function(v){ return parseInt(v); });
    let searchDirectionXY = searchDirection.split('-').map(function(v){ return v.toLowerCase(); });
    let firstEventPositionXY = [];
    if (searchDirectionXY.indexOf('up') !== -1){ firstEventPositionXY[1] = searchPositionXY[1] - 1; }
    else if (searchDirectionXY.indexOf('down') !== -1){ firstEventPositionXY[1] = searchPositionXY[1] + 1; }
    else { firstEventPositionXY[1] = searchPositionXY[1]; }
    if (searchDirectionXY.indexOf('left') !== -1){ firstEventPositionXY[0] = searchPositionXY[0] - 1; }
    else if (searchDirectionXY.indexOf('right') !== -1){ firstEventPositionXY[0] = searchPositionXY[0] + 1; }
    else { firstEventPositionXY[0] = searchPositionXY[0]; }
    let firstEventPosition = firstEventPositionXY.join('-');
    //console.log('-> searchPosition =', searchPosition);
    //console.log('-> searchDirection =', searchDirection);
    //console.log('-> searchPositionXY =', searchPositionXY);
    //console.log('-> searchDirectionXY =', searchDirectionXY);
    //console.log('-> firstEventPositionXY =', firstEventPositionXY);
    //console.log('-> firstEventPosition =', firstEventPosition);
    // Now use the above (especially the firstEventPositionXY) to sort events by how close they are to that position
    eventsAtPosition.sort(function(a, b){
        let aPositionXY = a.position.split('-').map(function(v){ return parseInt(v); });
        let bPositionXY = b.position.split('-').map(function(v){ return parseInt(v); });
        let aDeltaX = Math.abs(aPositionXY[0] - firstEventPositionXY[0]);
        let aDeltaY = Math.abs(aPositionXY[1] - firstEventPositionXY[1]);
        let bDeltaX = Math.abs(bPositionXY[0] - firstEventPositionXY[0]);
        let bDeltaY = Math.abs(bPositionXY[1] - firstEventPositionXY[1]);
        let aDelta = aDeltaX + aDeltaY;
        let bDelta = bDeltaX + bDeltaY;
        return aDelta - bDelta;
        });
    // Check if there is a battle anywhere in the collected events
    let hasBattleNearby = eventsAtPosition.some(function(event){ return event.kind === 'battle'; });
    // If there are no battles nearby, filter out any stars that aren't on the exact position
    if (!hasBattleNearby){
        eventsAtPosition = eventsAtPosition.filter(function(event){
            let isStar = event.kind === 'item' && event.token.indexOf('-star') !== -1;
            let isAdjacent = event.position !== searchPosition;
            return !(isStar && isAdjacent);
            });
        eventsAtPosition = Object.values(eventsAtPosition);
        }
    // Return the found events
    //console.log('-> Found ' + eventsAtPosition.length + ' events at position ' + searchPosition + ':', JSON.parse(JSON.stringify(eventsAtPosition)));
    return eventsAtPosition;
    }

// Quick function for checking if any player platforms are on this map and their drop-status
// Basically, we check each one to see if all parts of "active" status and if so, that means
// the platform has been activated and that player can be unlocked (we just need to reload)
async function refreshPlayerPlatforms(){
    //console.log('%c' + 'mmrpgWorldMap.refreshPlayerPlatforms()', 'color: magenta;');
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _mapEventSymbols = _config.mapEventSymbols;
    let _mapEventsIndex = _config.mapEventsIndex;
    let _mapEventsIndexKeys = Object.keys(_mapEventsIndex);
    let $thisWorld = _elements.world;
    let $canvasMap = _elements.map;
    //console.log('-> _mapEventSymbols =', _mapEventSymbols);
    //console.log('-> _mapEventsIndex =', _mapEventsIndex);
    if (!_mapEventSymbols || !Object.keys(_mapEventSymbols).length){ return; }
    if (!_mapEventsIndex || !Object.keys(_mapEventsIndex).length){ return; }
    // First scan to see if we have any player platforms to review
    let hasPlatforms = false;
    let playerPlatforms = {};
    //console.log('-> scanning ' + _mapEventsIndexKeys.length + ' map events for player platforms ...');
    for (var i = 0; i < _mapEventsIndexKeys.length; i++){
        //console.log('-- refreshPlayerPlatforms checking event index ' + i + ' of ' + _mapEventsIndexKeys.length);
        let eventKey = _mapEventsIndexKeys[i];
        let eventInfo = _mapEventsIndex[eventKey];
        let eventPosition = eventInfo.pos;
        let eventActive = eventInfo.active;
        //console.log('-> eventKey =', eventKey);
        //console.log('-> eventInfo =', eventInfo);
        //console.log('-> eventPosition =', eventPosition);
        //console.log('-> eventActive =', eventActive);
        if (!eventInfo.action || eventInfo.action !== 'drop-zone'){ continue; }
        if (!eventInfo.sprite || !eventInfo.sprite.match(/^(light|wily|cossack|lalinde)pad-/i)){ continue; }
        //console.log('-> found player platform event:', eventInfo);
        hasPlatforms = true;
        let platformKind = eventInfo.sprite.split('-')[0]
        let playerToken = 'dr-' + platformKind.substring(0, -3);
        //console.log('-> platformKind =', platformKind);
        //console.log('-> playerToken =', playerToken);
        if (typeof playerPlatforms[playerToken] === 'undefined'){ playerPlatforms[playerToken] = {}; }
        playerPlatforms[playerToken][eventPosition] = eventActive ? 1 : 0;
        }
    //console.log('-> hasPlatforms =', hasPlatforms);
    //console.log('-> playerPlatforms =', playerPlatforms);
    if (hasPlatforms && Object.keys(playerPlatforms).length){
        let playerPlatformsKeys = Object.keys(playerPlatforms);
        for (var j = 0; j < playerPlatformsKeys.length; j++){
            //console.log('-- refreshPlayerPlatforms checking player platforms ' + j + ' of ' + playerPlatformsKeys.length);
            let playerToken = playerPlatformsKeys[j];
            let platformParts = Object.values(playerPlatforms[playerToken]);
            let platformActive = platformParts.length && platformParts.indexOf(0) === -1 ? true : false;
            //console.log('-> playerToken =', playerToken);
            //console.log('-> platformParts =', platformParts);
            //console.log('-> platformActive =', platformActive);
            if (!platformActive){ continue; }
            // If we made it here, this player's platform is active and they can be unlocked
            //console.log('%c' + '-> player ' + playerToken + ' has an active platform and can be unlocked!', 'color: lime;');
            _worldCursor.busy = true;
            _worldCursor.loading = true;
            _self.incZoomLevel();
            $thisWorld.addClass('busy');
            _self.saveWorldState(function(){
                //_self.incZoomLevel();
                _self.resetZoomLevel();
                _self.animateZoomToMax(4, 0.25, 600);
                $thisWorld.addClass('loading');
                window.location.reload();
                }, true, false);
            }
        }

    // Return true on success
    return true;
    }

// Quick function for clearing out any existing map events then checking for new ones at
// the new position.  After we gather all the data and config, we either show the action
// dropdown or we trigger the appropriate effects on the world map / robots / etc.
async function refreshMapPositionEvents(timeoutMultiplier, forceRefresh){
    //console.log('%c' + 'mmrpgWorldMap.refreshMapPositionEvents()', 'color: magenta;');
    timeoutMultiplier = typeof timeoutMultiplier === 'number' ? timeoutMultiplier : 1;
    forceRefresh = typeof forceRefresh === 'boolean' ? forceRefresh : false;

    // Collect references, indexes, and other variables we need to work with
    let _self = this;
    let _selfRef = _self.refreshMapPositionEvents;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _worldPlayerRobotsKeys = Object.keys(_worldPlayerRobots);
    let _worldSymbols = _world.symbols;
    let _worldToken = _config.mapWorld;
    let _mapToken = _config.mapToken;
    let _mapEffects = _config.mapEffects;
    let _mapTileSize = _config.mapTileSize;
    let _mapTileSizeOffset = _config.mapTileSizeOffset;
    let _mapSpriteSizeOffset = _config.mapSpriteSizeOffset;
    let _mapEventSymbols = _config.mapEventSymbols;
    let _mapEventsIndex = _config.mapEventsIndex;
    let _mapItemSymbols = _config.mapItemSymbols;
    let _mapItemsIndex = _config.mapItemsIndex;
    let _mapAbilitySymbols = _config.mapAbilitySymbols;
    let _mapAbilitiesIndex = _config.mapAbilitiesIndex;
    let _mapActorSymbols = _config.mapActorSymbols;
    let _mapActorsIndex = _config.mapActorsIndex;
    let _mmrpgPlayersIndex = _indexes.players;
    let _mmrpgRobotsIndex = _indexes.robots;
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    let _mmrpgItemsIndex = _indexes.items;
    let _userId = _config.userId;
    let _playerId = _config.playerId;
    let _playerToken = _config.playerToken;
    let _playerRobots = _config.playerRobots;
    let _playerRobotsIndex = _config.playerRobotsIndex;
    let _playerIndexInfo = _mmrpgPlayersIndex[_playerToken];
    let $thisWorld = _elements.world;
    let $canvasMap = _elements.map;
    let $worldCursor = _elements.worldCursor;
    let $teamSprites = _elements.teamSprites;
    let lastPosition = _selfRef.lastPosition || false;
    let lastDirection = _selfRef.lastDirection || false;
    let cursorPosition = _worldCursor.position;
    let cursorDirection = _worldCursor.direction;
    let newPosition = cursorPosition.split('-');
    let thisNewCol = parseInt(newPosition[0]);
    let thisNewRow = parseInt(newPosition[1]);
    let sameAsLastPosition = lastPosition === cursorPosition ? true : false;
    let sameAsLastDirection = lastDirection === cursorDirection ? true : false;
    let stillAtPosition = function(){ return (_worldCursor.position === cursorPosition) ? true : false; };
    let otherMenusActiveNow = function(){ return ( _elements.robotsOverview.is('.expanded') ) ? true : false; };
    let robotsOverviewAPI = _self.robotsOverviewAPI || false;
    let hoverOverviewObject = robotsOverviewAPI ? robotsOverviewAPI.hoverOverviewObject : false;
    let unhoverOverviewObject = robotsOverviewAPI ? robotsOverviewAPI.unhoverOverviewObject : false;
    //let otherMenusActiveNow = function(){ return (_elements.robotsOverview.is('.expanded') || _elements.sideButtons.is('.active')) ? true : false; };
    //console.log('-> lastPosition (old):', lastPosition);
    //console.log('-> lastDirection (old):', lastDirection);
    //console.log('-> cursorPosition (new):', cursorPosition);
    //console.log('-> cursorDirection (new):', cursorDirection);
    //console.log('-> sameAsLastPosition:', sameAsLastPosition);
    //console.log('-> sameAsLastDirection:', sameAsLastDirection);

    // Define reusable functions for applying/removing the hover state to given element
    if (!hoverOverviewObject){
        hoverOverviewObject = function(e, sfx){
            let $object = $(this);
            if (_self.worldIsBusy()){ return; }
            if ($object.is('.disabled')){ return; }
            if ($object.closest('.listing').is('.disabled')){ return; }
            //$robotsOverview.find('.hovered').removeClass('hovered');
            if (sfx){ _self.playSoundEffect(sfx); }
            else { _self.playSoundEffect('icon-hover'); }
            $object.addClass('hovered');
            };
        }
    if (!unhoverOverviewObject){
        unhoverOverviewObject = function(e){
            $(this).removeClass('hovered');
            };
        }

    // If nothing has changed, we should not do anything further
    if (sameAsLastPosition && sameAsLastDirection && !forceRefresh){
        //console.log('-> player has not changed position or direction, skipping further processing');
        return true;
        }

    // Update the "last" variables for next time
    _selfRef.lastPosition = cursorPosition;
    _selfRef.lastDirection = cursorDirection;

    // Check if the current player is in-fact the cursor
    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;

    // Before we do anything else, check to see if this player has any active robots
    //console.log('_worldPlayerRobots = ', _worldPlayerRobots);
    //console.log('_worldPlayerRobotsKeys = ', _worldPlayerRobotsKeys);
    let playerActiveRobots = 0;
    for (var i = 0; i < _worldPlayerRobotsKeys.length; i++){
        let token = _worldPlayerRobotsKeys[i];
        let info = _worldPlayerRobots[token] || false;
        if (!info || info.disabled){ continue; }
        playerActiveRobots++;
        }
    //console.log('playerActiveRobots = ', playerActiveRobots);

    // Define the default zoom timeout for after movement ends
    let zoomTimeoutDuration = 2000 * timeoutMultiplier;
    let teamReadyDuration = 1800 * timeoutMultiplier;
    let teamRushDuration = 300 * timeoutMultiplier;
    if (teamRushDuration < 1){ teamRushDuration = 100; }
    //console.log('timeoutMultiplier = ', timeoutMultiplier);
    //console.log('teamRushDuration = ', teamRushDuration);

    // Collect references to the required sprites layer for adjustments
    let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap); // later: $('.layer[data-layer="sprites"]', $canvasMap);
    if (!$spritesLayer || !$spritesLayer.length){
        console.error('updateMapPosition() missing required $spritesLayer!');
        return false;
        }
    //console.log('-> $eventsLayer found, checking for events...');

    // Make sure we empty and hide the action dropdown if it's been shown by previous move
    let $actionDropdown = _elements.actionDropdown;
    let $actionDropdownWrapper = $('> .wrapper', $actionDropdown);
    if (!sameAsLastPosition){
        $actionDropdown.removeClass('active').css({left: '', top: ''}).removeAttr('data-dir');
        $actionDropdownWrapper.empty();
        }

    // Make sure we also empty the sidebar buttons in case any were added by previous move
    let $sideButtons = _elements.sideButtons;
    let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
    if (!sameAsLastPosition){
        $sideButtons.removeClass('active');
        $sideButtonsWrapper.empty();
        }

    // Make sure we move any existing zoom layer sprites back to their original layers
    if (!sameAsLastPosition){
        $worldCursor.removeClass('shake');
        $spritesLayer.removeClass('has-zoom');
        setTimeout(function(){ $('.sprite', $canvasMap).removeClass('zoom').removeClass('busy'); }, 100);
        }

    // Search for events at the new position so we can show the action dropdown if needed
    //console.log('-> checking if there are any events for this position...');
    let eventsAtPosition = _self.getEventsAtPosition(cursorPosition, cursorDirection);
    //console.log('-> eventsAtPosition =', eventsAtPosition);
    if (!eventsAtPosition || !eventsAtPosition.length){
        //console.log('-> no events found at position', cursorPosition, 'skipping further processing');
        return;
        }
    //console.log('-> found ' + eventsAtPosition.length + ' events at position');
    //console.log('-> eventsAtPosition =', eventsAtPosition);
    //console.log('-> found ' + eventsAtPosition.length + ' eventsAtPosition =', JSON.parse(JSON.stringify(eventsAtPosition)));

    // Check to see what the very first event type is
    let firstEvent = eventsAtPosition[0];
    let firstEventType = firstEvent.kind;
    //console.log('-> firstEvent =', JSON.parse(JSON.stringify(firstEvent)));
    //console.log('-> firstEventType =', firstEventType);

    // Sort the events at this position by priority with sanctuaries > portals > hazard > battles > everything-else
    let eventsPriority = ['sanctuary', 'portal', 'hazard', 'battle', 'item', 'ability', 'button', 'switch', 'gate', 'block'];
    eventsAtPosition = eventsAtPosition.sort(function(a, b){
        let aKind = a.kind2 === 'sanctuary' ? 'sanctuary' : a.kind;
        let bKind = b.kind2 === 'sanctuary' ? 'sanctuary' : b.kind;
        let aIndex = eventsPriority.indexOf(aKind); if (aIndex === -1){ aIndex = 99; }
        let bIndex = eventsPriority.indexOf(bKind); if (bIndex === -1){ bIndex = 99; }
        if (aIndex < bIndex){ return -1; }
        else if (aIndex > bIndex){ return 1; }
        else { return 0; }
        });
    //console.log('-> eventsAtPosition(after-sort) = ', JSON.parse(JSON.stringify(eventsAtPosition)));

    // Before we remove anything, check to see if we're standing on any events
    let standingOnEvent = null;
    let standingOnEventKey = null;
    let standingOnEventType = null;
    let standingOnHazardEvent = false;
    for (let key = 0; key < eventsAtPosition.length; key++){
        if (eventsAtPosition[key].position === cursorPosition){
            standingOnEventKey = key;
            standingOnEvent = eventsAtPosition[key];
            standingOnEventType = standingOnEvent.kind;
            break;
            }
        }
    //console.log('standingOnEvent =', standingOnEvent);
    //console.log('standingOnEventType =', standingOnEventType);
    // If we're standing on a hazard, move it to the front of the queue
    if (standingOnEvent
        && standingOnEventKey !== null
        && standingOnEventType === 'hazard'){
        //console.log('standing on HAZARD! moving it to front of queue ...');
        delete eventsAtPosition[standingOnEventKey];
        eventsAtPosition.unshift(standingOnEvent);
        eventsAtPosition = Object.values(eventsAtPosition);
        standingOnHazardEvent = true;
        }

    // Backup any gate data found in the event list in case we need to check for it later
    let gatesNearPosition = [];
    for (let key = 0; key < eventsAtPosition.length; key++){
        let eventAtPosition = eventsAtPosition[key];
        //console.log('eventsAtPosition[', key, '] =', eventAtPosition);
        if (eventAtPosition.kind !== 'gate'){ continue; }
        let eventBackup = _self.getClonedObject(eventsAtPosition[key]);
        eventBackup.key = key;
        gatesNearPosition.push(eventBackup);
        }
    //console.log('gatesNearPosition =', gatesNearPosition);

    // Backup any star data found in the event list in case we need to check for it later
    let starsNearPosition = [];
    for (let key = 0; key < eventsAtPosition.length; key++){
        let eventAtPosition = eventsAtPosition[key];
        //console.log('eventsAtPosition[', key, '] =', eventAtPosition);
        if (eventAtPosition.token.indexOf('-star') === -1){ continue; }
        let eventBackup = _self.getClonedObject(eventsAtPosition[key]);
        eventBackup.key = key;
        starsNearPosition.push(eventBackup);
        }
    //console.log('starsNearPosition =', starsNearPosition);

    // Check to see what position the player is looking at in case we need it later
    let standingAtPosition = _worldCursor.position;
    let lookingAtPosition = _self.getRelativePositionByDirection(_worldCursor.position, _worldCursor.direction);
    //let isSamePosition = eventPosition === standingAtPosition;
    //let isFacingPosition = eventPosition === lookingAtPosition;
    //console.log('_worldCursor.position =', _worldCursor.position);
    //console.log('_worldCursor.direction =', _worldCursor.direction);
    //console.log('standingAtPosition =', standingAtPosition);
    //console.log('lookingAtPosition =', lookingAtPosition);

    // Refresh the first event variables in case they've changed
    firstEvent = eventsAtPosition[0];
    firstEventType = firstEvent.kind;
    //console.log('-> firstEvent =', JSON.parse(JSON.stringify(firstEvent)));
    //console.log('-> firstEventType =', firstEventType);
    //console.log('-> firstEventInfo =', firstEventType);

    // Unless this is a battle (where it's possible to fight many at once), we should
    // filter out all the other event types than the first so we only show one dropdown
    //console.log('-> before filtering, there are ' + eventsAtPosition.length + ' ' + firstEventType + ' events near cursorPosition', cursorPosition);
    if (firstEventType !== 'battle'){
        eventsAtPosition = eventsAtPosition.slice(0, 1); // only keep the first event
        } else {
        for (let i = 1; i < eventsAtPosition.length; i++){
            if (eventsAtPosition[i].kind !== 'battle'){
                delete eventsAtPosition[i];
                }
            }
        }
    eventsAtPosition = Object.values(eventsAtPosition); // re-index the array to avoid issues with gaps
    //console.log('-> after filtering, there are ' + eventsAtPosition.length + ' ' + firstEventType + ' events near cursorPosition', cursorPosition);
    firstEvent = eventsAtPosition[0]; // re-assign the first event after filtering

    // Now that we have an event, check its data to see if we should show a dropdown
    // for either a battle, a portal, or any other compatible event-type for the tile
    let triggerEffect = false;
    let triggerEffectFunction = function(){};
    let triggerEffectSound = '';
    let autoRedirect = false;
    let autoRedirectURL = '';
    let autoRedirectSound = '';
    let autoRedirectEffect = '';
    let autoRedirectAnimation = '';
    let showActionArea = false;
    let showActionAreaType = '';
    let showActionAreaSound = '';
    let showActionAreaAnyway = false;
    let actionAreaMarkup = '';
    let sideButtonsMarkup = '';
    let readyTeamSprites = false;
    let readyTeamSpritesAnyway = false;
    let readyTeamPlayerFrames = [];
    let readyTeamRobotFrames = [];
    if (firstEventType === 'event'){
        //console.log('-> event at position is custom, checking what comes next...');
        // If the cursor is literally on a event, only one event sprite matters right now
        let $customEvent = $(firstEvent.sprite);
        let dataLabel = $customEvent.attr('data-label');
        let dataEvent = $customEvent.attr('data-event');
        let eventInfo = _config.mapEventsIndex[dataEvent] || false;
        if (eventInfo && _worldCursor.moved){
            //console.log('-> found eventInfo for ' + dataEvent + ':', eventInfo);
            let eventFilter = eventInfo['filter'] || false;
            let eventAction = eventInfo['action'] || false;
            let eventData = eventInfo['data'] || false;
            let eventAllowed = eventFilter === 'any' ? true : false; // TODO: implement player and/or robot-specific filter logic
            //console.log('-> eventFilter =', eventFilter, '| eventAction =', eventAction, '| eventData =', eventData, '| eventAllowed =', eventAllowed);
            if (eventAllowed){
                if (eventAction !== false){
                    //console.log('-> eventAction is "', eventAction, '" so defer it to triggerEffectFunction()');
                    triggerEffect = true;
                    readyTeamSprites = true;
                    //teamReadyDuration = 600; // for event panels we want to zoom in quickly
                    zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                    triggerEffectFunction = function(){
                        if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                        //console.log('-> running triggerEffectFunction for eventAction "' + eventAction + '" with eventData:', eventData);
                        _self.triggerWorldEvent(eventAction, eventData, $customEvent);
                        };
                    } else {
                    //console.log('-> eventAction is false, so prepare dropdown instead');
                    showActionArea = true;
                    if (!dataLabel){ dataLabel = 'Event Options'; }
                    actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
                    sideButtonsMarkup += '<a class="button big-button" data-action="trigger-event" data-event="'+dataEvent+'"><span><sup>Ready To</sup> Trigger Event</span></a>';
                    sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                    showActionAreaType = 'event';
                    }
                } else {
                //console.log('-> event not allowed based on filter "' + eventFilter + '"');
                }
            } else if (eventInfo && !_worldCursor.moved) {
            //console.log('-> eventInfo found for ' + dataEvent + ', but cursor not moved yet, skipping');
            } else {
            //console.log('-> no eventInfo found for ' + dataEvent + ', skipping');
            }
        }
    else if (firstEventType === 'portal'){
        //console.log('-> event at position is a portal, preparing redirect', '\n-> firstEvent:', firstEvent);
        // If the cursor is literally on a portal, only one event sprite matters right now
        let $portalEvent = $(firstEvent.sprite);
        let dataLabel = $portalEvent.attr('data-label');
        let dataPortal = $portalEvent.attr('data-portal');
        let portalInfo = dataPortal ? (_config.mapPortalsIndex[dataPortal] || false) : false;
        let goToDestination = false;
        let goToWorld, goToMap, goToPosition;
        let goToSameWorld, goToSameMap, goToSamePosition;
        //let goToDestination = portalInfo ? (portalInfo['dst'] || false) : false;
        if (dataPortal.indexOf('goto__') !== -1){
            goToDestination = true;
            goToWorld = _config.mapWorld;
            goToMap = dataPortal.replace(/^goto__/i, '');
            if (goToMap.indexOf('__') !== -1){ let gtm = goToMap.split('__'); goToWorld = gtm[0]; goToMap = gtm[1]; }
            if (portalInfo['dst']){ goToPosition = portalInfo['dst']; }
            } else if (portalInfo['dst']){
            goToDestination = true;
            goToWorld = _config.mapWorld;
            goToMap = portalInfo['dst'];
            if (goToMap.indexOf('__') !== -1){
                let gtm = goToMap.split('__');
                //console.log('-> gtm =', gtm);
                if (gtm.length >= 3){ goToWorld = gtm[0]; goToMap = gtm[1]; goToPosition = gtm[2]; }
                else if (gtm.length >= 2){ goToMap = gtm[0]; goToPosition = gtm[1]; }
                }
            }
        goToSameWorld = goToWorld === _config.mapWorld ? true : false;
        goToSameMap = goToSameWorld && goToMap === _config.mapToken ? true : false;
        goToSamePosition = goToSameWorld && goToSameMap && goToPosition === _worldCursor.position ? true : false;
        //console.log('----------------------------------');
        //console.log('-> found portalInfo for ' + dataPortal + ':', portalInfo);
        //console.log('-> w/ goToDestination =', goToDestination, '\n-> w/ goToWorld =', goToWorld, '\n-> w/ goToMap =', goToMap, '\n-> w/ goToPosition =', goToPosition);
        //console.log('-> w/ goToSameWorld =', goToSameWorld, '\n-> w/ goToSameMap =', goToSameMap, '\n-> w/ goToSamePosition =', goToSamePosition);
        if (portalInfo && goToDestination){
            //console.log('-> portalInfo has a valid destination!');
            showActionArea = true;
            if (!dataLabel){ dataLabel = 'Portal Options'; }
            actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
            if (dataPortal === 'exit'){ sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Return Home</span></a>'; }
            else { sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Enter Teleport</span></a>'; }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'portal';
            zoomTimeoutDuration = 500; // if we show a portal dropdown, we want to zoom in quickly
            // Automatically redirect to this portal if cursor has moved at least once
            if (_worldCursor.moved){
                //console.log('-> entering portal with name ' + dataPortal + '!');
                if (dataPortal === 'spawn'){
                    // TODO: SPAWN PORTAL - make the spawn actually go somewhere specific ?
                    console.warn('-> spawn portals not yet implemented yet');
                    }
                else if (dataPortal === 'exit'){
                    // TODO: EXIT PORTAL - make the exit actually go somewhere specific ?
                    autoRedirect = true;
                    showActionArea = false;
                    autoRedirectURL = 'prototype.php';
                    }
                else {
                    // GOTO PORTAL - use the world, map, and position info collected earlier to redirect
                    if (goToSameWorld && goToSameMap && !goToSamePosition){
                        //console.log('-> preparing auto-effect of moving to NEW POSITION on SAME MAP via portal...');
                        triggerEffect = true;
                        showActionArea = false;
                        triggerEffectFunction = function(){
                            if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                            //console.log('-> running triggerEffectFunction for portal to moveWorldCursorToPosition(' + goToPosition + ')');
                            _config.allowWorldEvents = false; // prevent re-triggering events during teleport
                            if (triggerEffectSound){ _self.playSoundEffect(triggerEffectSound); }
                            _self.moveToPosition(goToPosition, function(){
                                //console.log('-> moveToPosition() complete via portal to new position ' + goToPosition);
                                _config.allowWorldEvents = true; // re-allow world events after teleport complete
                                return true;
                                });
                            };
                        triggerEffectSound = 'bounce-sound';
                        readyTeamSprites = true;
                        }
                    else {
                        //console.log('-> preparing auto-redirect to NEW MAP via portal...');
                        //console.log('-> w/ portalInfo =', portalInfo);
                        //alert('portalInfo =' + JSON.stringify(portalInfo));
                        autoRedirect = true;
                        showActionArea = false;
                        autoRedirectURL = 'world.php?world=' + goToWorld;
                        if (goToMap){ autoRedirectURL += '&map='+goToMap; }
                        if (goToPosition){ autoRedirectURL += '&position='+goToPosition; }
                        //console.log('-> autoRedirectURL =', autoRedirectURL);
                        //if (!confirm('teleport to ' + autoRedirectURL + '?')){ autoRedirectURL = false; } // TEMP TEMP TEMP
                        readyTeamSprites = true;
                        readyTeamSpritesAnyway = true;
                        teamReadyDuration = 600; // we want the animation to start right away
                        let isExitPortal = portalInfo.direction ? true : false;
                        let isTelePortal = !portalInfo.direction ? true : false;
                        let isSubTelePortal = goToMap === 'debug-area-0' ? true : false;
                        autoRedirectSound = isExitPortal ? 'lets-go-robots' : 'intense-growing-sound'; //'bounce-sound';
                        autoRedirectEffect = isExitPortal ? 'leaving' : 'glowing';
                        if (isExitPortal){ autoRedirectAnimation = 'slide-' + portalInfo.direction; }
                        else if (isTelePortal){ autoRedirectAnimation = isSubTelePortal ? 'descend-downward' : 'ascend-upward'; }
                        }
                    }
                }
            // Otherwise we can only prepare the dropdown details and wait
            else {
                //console.log('-> portal ' + dataPortal + ' disabled until cursor movement!');
                showActionArea = false;
                }
            }
        }
    else if (firstEventType === 'button'){
        //console.log('-> event at position is a button, preparing dropdown');
        // If the cursor is literally on a button, only one event sprite matters right now
        let $buttonEvent = $(firstEvent.sprite);
        let dataLabel = $buttonEvent.attr('data-label');
        let dataButton = $buttonEvent.attr('data-button');
        let dataColour = $buttonEvent.attr('data-colour');
        let dataState = $buttonEvent.attr('data-state');
        if (dataButton && dataState === 'up'){
            showActionArea = true;
            //var buttonName = (dataColour ? (dataColour[0].toUpperCase() + dataColour.slice(1) + ' ') : '') + 'Button';
            //if (!dataLabel){ dataLabel = 'Button Options'; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
            sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="push-button" data-button="'+dataButton+'"><span><sup>Push The</sup> ' + (dataColour[0].toUpperCase() + dataColour.slice(1)) + ' Button</span></a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'button';
            zoomTimeoutDuration = 500; // if we show a button dropdown, we want to zoom in quickly
            }
        }
    else if (firstEventType === 'switch'){
        //console.log('-> event at position is a switch, preparing dropdown');
        // If the cursor is literally on a switch, only one event sprite matters right now
        let $switchEvent = $(firstEvent.sprite);
        let dataLabel = $switchEvent.attr('data-label');
        let dataSwitch = $switchEvent.attr('data-switch');
        let dataColour = $switchEvent.attr('data-colour');
        let dataState = $switchEvent.attr('data-state');
        if (dataSwitch){
            showActionArea = true;
            if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
            sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="toggle-switch" data-switch="'+dataSwitch+'"><span><sup>Toggle The</sup> ' + dataColour.split('-').map(function(colour){ return colour[0].toUpperCase() + colour.slice(1); }).join(' & ') + ' Switch</span></a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'switch';
            zoomTimeoutDuration = 500; // if we show a switch dropdown, we want to zoom in quickly
            }
        }
    else if (firstEventType === 'gate'){
        //console.log('-> event at position is a gate, preparing either popup or removal');
        let eventInfo = firstEvent;
        let eventPosition = eventInfo.position;
        let $eventSprite = $(eventInfo.sprite);
        let isSamePosition = eventPosition === standingAtPosition;
        let isFacingPosition = eventPosition === lookingAtPosition;
        let dataLabel = $eventSprite.attr('data-label');
        let dataGate = $eventSprite.attr('data-gate');
        let dataType = $eventSprite.attr('data-type') || 'none';
        let gateInfo = _config.mapGatesIndex[dataGate] || false;
        let gateKind = eventInfo.kind;
        let gateKind2 = eventInfo.kind2;
        let gateType = gateInfo.type ? gateInfo.type : '';
        let gatePrice = gateInfo.price ? gateInfo.price : 0;
        //console.log('-> eventInfo =', eventInfo);
        //console.log('-> dataLabel =', dataLabel);
        //console.log('-> dataGate =', dataGate);
        //console.log('-> dataType =', dataType);
        //console.log('-> gateInfo =', gateInfo);
        //console.log('-> gateKind =', gateKind);
        //console.log('-> gateKind2 =', gateKind2);
        //console.log('-> gateType =', gateType);
        //console.log('-> gatePrice =', gatePrice);
        if (dataGate && gateInfo && !playerIsCursor && (isSamePosition || isFacingPosition)){
            //console.log('-> found gateInfo for ' + dataGate + ':', gateInfo);
            //console.log('-> gateType:', gateType);
            //console.log('-> gatePrice:', gatePrice);
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Open The</sup> ' + (gatePrice ? '&times; ' + gatePrice + ' ' : '') + toUpperCaseWords(gateKind2.replace('-', ' ')) + ' ?</span></strong>';
            let gateCurrency = 'whatevers';
            if (gateKind2 === 'boss-door'){ gateCurrency = 'none'; }
            else if (gateKind2 === 'star-gate'){ gateCurrency = 'stars'; }
            let playerHasNow = 0; // TODO: make this dynamically pull the core/star/whatever count
            if (gateCurrency === 'none'){ playerHasNow = 0; }
            else if (gateCurrency === 'stars'){ playerHasNow = Object.keys(_worldPlayer.stars).length; }
            let playerHasEnough = playerHasNow >= gatePrice ? true : false;
            let playerCountLabel = (playerHasNow + ' / ' + gatePrice) + ' ' + toUpperCaseWords(gateCurrency);
            let currencySpriteMarkup;
            if (gateCurrency === 'screws'){
                currencySpriteMarkup = _self.getItemSpriteMarkup('hyper-screw', {dir: 'left', frame: '00'});
                } else if (gateCurrency === 'cores'){
                currencySpriteMarkup = _self.getItemSpriteMarkup('none-core', {dir: 'left', frame: '00'});
                } else if (gateCurrency === 'stars'){
                currencySpriteMarkup = _self.getItemSpriteMarkup('field-star', {dir: 'left', frame: '00'});
                } else {
                if (gateKind2 === 'boss-door'){ currencySpriteMarkup = '<span class="sprite" data-sprite="object" data-token="challenge-marker"><span class="wrap"><i class="sprite"></i></span></span>'; }
                else { currencySpriteMarkup = ''; }
                }
            let openTheGateLabel = 'Open the ' + toUpperCaseWords(gateKind2.split('-')[1]);
            if (playerHasEnough){
                //console.log('-> player has enough whatevers! allow them to lower the gate now');
                // ...
                } else {
                //console.log('-> player doesn\'t have enough whatevers! cannot remove the gate yet');
                // ...
                }
            sideButtonsMarkup += '<a '
                + ('class="button big-button inner-strike'
                    + (gateKind2 ? ' '+gateKind2 : '')
                    + (dataType ? ' type '+dataType : '')
                    + (!playerHasEnough ? ' disabled' : '')
                    + '"')
                + (playerHasEnough ?
                    ' data-action="open-gate"'
                    + ' data-gate="'+dataGate+'"'
                    : '')
                + '>';
                sideButtonsMarkup += '<span class="has-sprite' + (!gatePrice ? ' one-row' : '') + '">';
                if (!gatePrice){
                    sideButtonsMarkup += '<strong>' + openTheGateLabel + '</strong> ';
                    sideButtonsMarkup += currencySpriteMarkup;
                    } else if (playerHasEnough){
                    sideButtonsMarkup += '<strong>' + openTheGateLabel + '</strong> ';
                    sideButtonsMarkup += '<br /><sup class="no-strike">' + playerCountLabel + '</sup> ';
                    sideButtonsMarkup += currencySpriteMarkup;
                    } else {
                    sideButtonsMarkup += '<strong>' + openTheGateLabel + '</strong> ';
                    sideButtonsMarkup += '<br /><sup class="no-strike">' + playerCountLabel + '</sup> ';
                    sideButtonsMarkup += currencySpriteMarkup;
                    }
                sideButtonsMarkup += '</span>';
            sideButtonsMarkup += '</a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'gate';
            zoomTimeoutDuration = 300; // if we show a gate dropdown, we want to zoom in quickly
            }
        }
    else if (firstEventType === 'lock'){
        //console.log('-> event at position is a lock, preparing either popup or removal');
        let eventInfo = firstEvent;
        let eventPosition = eventInfo.position;
        let $eventSprite = $(eventInfo.sprite);
        let isSamePosition = eventPosition === standingAtPosition;
        let isFacingPosition = eventPosition === lookingAtPosition;
        let dataLabel = $eventSprite.attr('data-label');
        let dataLock = $eventSprite.attr('data-lock');
        let dataType = $eventSprite.attr('data-type') || 'none';
        let lockInfo = _config.mapLocksIndex[dataLock] || false;
        let lockKind = eventInfo.kind;
        let lockKind2 = eventInfo.kind2;
        let lockType = lockInfo.type ? lockInfo.type : '';
        let lockCurrency = lockInfo.currency ? lockInfo.currency : '';
        let lockPrice = lockInfo.price ? lockInfo.price : 0;
        //console.log('-> eventInfo =', eventInfo);
        //console.log('-> dataLabel =', dataLabel);
        //console.log('-> dataLock =', dataLock);
        //console.log('-> dataType =', dataType);
        //console.log('-> lockInfo =', lockInfo);
        //console.log('-> lockKind =', lockKind);
        //console.log('-> lockKind2 =', lockKind2);
        //console.log('-> lockType =', lockType);
        //console.log('-> lockCurrency =', lockCurrency);
        //console.log('-> lockPrice =', lockPrice);
        if (dataLock && lockInfo && !playerIsCursor && (isSamePosition || isFacingPosition) && lockInfo.locked){
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            let sideButtonLabel = lockKind2 === 'portal-flower' ? 'Feed The' : 'Open The';
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>' + sideButtonLabel + '</sup> ' + toUpperCaseWords(lockKind2.replace('-', ' ')) + ' ?</span></strong>';
            let currencyCheck = _self.checkMenuButtonCurrency(lockCurrency, lockPrice);
            let playerHasEnough = currencyCheck.hasEnough;
            let openTheLockLabel = 'Give ' + toUpperCaseWords(currencyCheck.kind);
            sideButtonsMarkup += '<a '
                + ('class="button big-button inner-strike'
                    + (lockKind2 ? ' '+lockKind2 : '')
                    + (dataType ? ' type '+dataType : '')
                    + (!playerHasEnough ? ' disabled' : '')
                    + '"')
                + (playerHasEnough ? ' data-action="open-lock" data-lock="'+dataLock+'"' : '')
                + '>';
            sideButtonsMarkup += '<span class="has-sprite' + (!lockPrice ? ' one-row' : '') + '">';
            if (!lockPrice){
                sideButtonsMarkup += '<strong>' + openTheLockLabel + '</strong> ';
                sideButtonsMarkup += currencyCheck.spriteMarkup;
                }
            else if (playerHasEnough){
                sideButtonsMarkup += '<strong>' + openTheLockLabel + '</strong> ';
                sideButtonsMarkup += '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> ';
                sideButtonsMarkup += currencyCheck.spriteMarkup;
                }
            else {
                sideButtonsMarkup += '<strong>' + openTheLockLabel + '</strong> ';
                sideButtonsMarkup += '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> ';
                sideButtonsMarkup += currencyCheck.spriteMarkup;
                }
            sideButtonsMarkup += '</span></a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'lock';
            zoomTimeoutDuration = 300;
            }
        }
    else if (firstEventType === 'block'){
        //console.log('-> event at position is a block, preparing dropdown');
        // If the cursor is literally on a block, only one event sprite matters right now
        let $blockEvent = $(firstEvent.sprite);
        let dataLabel = $blockEvent.attr('data-label');
        let dataBlock = $blockEvent.attr('data-block');
        let dataType = $blockEvent.attr('data-type') || 'none';
        let blockInfo = _config.mapBlocksIndex[dataBlock] || false;
        let blockKind = firstEvent.kind;
        let blockKind2 = firstEvent.kind2;
        let blockType = blockInfo.type ? blockInfo.type : '';
        let blockWeaknesses = blockInfo.weaknesses ? blockInfo.weaknesses : [];
        if (dataBlock && blockInfo && !playerIsCursor && blockWeaknesses.length){
            //console.log('-> found blockInfo for ' + dataBlock + ':', blockInfo);
            //console.log('-> blockType:', blockType);
            //console.log('-> blockWeaknesses:', blockWeaknesses);
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            //var blockName = (dataType ? (dataType[0].toUpperCase() + dataType.slice(1) + ' ') : '') + 'Button';
            //if (!dataLabel){ dataLabel = 'Button Options'; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            //sideButtonsMarkup += '<strong class="button big-title'+(blockKind2 ? ' '+blockKind2 : '')+''+(dataType ? ' type '+dataType : '')+'"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + ' ?</span></strong>';
            //sideButtonsMarkup += '<strong class="button big-button'+(blockKind2 ? ' '+blockKind2 : '')+' type empty disabled"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + ' ?</span></strong>';
            //sideButtonsMarkup += '<strong class="button big-button-title type empty'+(blockKind2 ? ' '+blockKind2 : '')+'"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + ' ?</span></strong>';
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + ' ?</span></strong>';
            if (blockWeaknesses.length){
                let robotsIndex = _indexes.robots;
                let abilitiesIndex = _indexes.abilities;
                let robotStringsUsed = [];
                for (let i = 0; i < blockWeaknesses.length; i++){
                        let weaknessType = blockWeaknesses[i];
                        let buttonColour = weaknessType;
                        //console.log('checking for weaknessType:' + weaknessType + ' ... ');
                        let robotsWithAbilityType = _self.getPlayerRobotsWithAbilityType(weaknessType);
                        robotsWithAbilityType = robotsWithAbilityType ? robotsWithAbilityType.filter(function(robot){
                            let robotString = robot[0], robotData = _worldPlayerRobots[robotString];
                            if (robotStringsUsed.indexOf(robotString) !== -1){ return false; }
                            if (!robotData || !robotData.weapons){ return false; }
                            if (robotData.weapons < 1){ return false; }
                            return true;
                            }) : false;
                        //console.log('robotsWithAbilityTypee(filtered) =', robotsWithAbilityType);
                        let firstRobotWithAbilityType = robotsWithAbilityType ? robotsWithAbilityType[0] : false;
                        if (firstRobotWithAbilityType){ robotStringsUsed.push(firstRobotWithAbilityType[0]); }
                        let playerRobotAvailable = firstRobotWithAbilityType ? true : false;
                        let playerRobotName = '', playerRobotString = '', playerRobotToken = '', playerRobotId = 0;
                        let $playerRobotSprite = null, robotSpriteMarkup = null;
                        if (playerRobotAvailable){ robotSpriteMarkup = _self.getRobotSpriteMarkup(firstRobotWithAbilityType[0], {dir: 'left', frame: '10'}); }
                        if (playerRobotAvailable && robotSpriteMarkup){
                            playerRobotString = firstRobotWithAbilityType[0];
                            playerRobotId = parseInt(playerRobotString.split('_')[0]);
                            playerRobotToken = playerRobotString.split('_')[1];
                            playerRobotName = toUpperCaseWords(playerRobotToken.replace('-', ' '));
                            $playerRobotSprite = $(robotSpriteMarkup);
                            $playerRobotSprite.addClass('team bounce');
                            $playerRobotSprite.attr('data-sprite', 'team-robot').attr('data-robot', playerRobotString).attr('data-frame', '10');
                            robotSpriteMarkup = $playerRobotSprite[0].outerHTML;
                            let abilityInfo = abilitiesIndex[firstRobotWithAbilityType[1]];
                            //console.log('abilitiesIndex =', abilitiesIndex);
                            //console.log('firstRobotWithAbilityType[1] =', firstRobotWithAbilityType[1]);
                            //console.log('abilityInfo =', abilityInfo);
                            buttonColour = abilityInfo.type + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '');
                            }
                        //console.log('-> robotsWithAbilityType:', robotsWithAbilityType, '\n-> playerRobotAvailable:', playerRobotAvailable);
                        //console.log('-> playerRobotName:', playerRobotName, '\n-> playerRobotId:', playerRobotId);
                        sideButtonsMarkup += '<a '
                            + ('class="button big-button'
                                + (blockKind2 ? ' '+blockKind2 : '')
                                + (buttonColour ? ' type '+buttonColour : '')
                                + (!playerRobotAvailable ? ' disabled' : '')
                                + '"')
                            + (playerRobotAvailable ?
                                ' data-action="remove-block"'
                                + ' data-block="'+dataBlock+'"'
                                + ' data-block-robot="'+firstRobotWithAbilityType[0]+'"'
                                + ' data-block-ability="'+firstRobotWithAbilityType[1]+'"'
                                : '')
                            + '>';
                        sideButtonsMarkup += (playerRobotAvailable
                            ? '<span class="has-sprite"><sup>Use</sup> ' + toUpperCaseWords(firstRobotWithAbilityType[1].replace('-', ' ')) + robotSpriteMarkup + '</span>'
                            : '<span>Use ' + toUpperCaseWords(weaknessType.replace('-', ' ')) + '</span>'
                            );
                        sideButtonsMarkup += '</a>';
                    }
                //console.log('robotStringsUsed =', robotStringsUsed);
                }
            //sideButtonsMarkup += '<a class="button big-button'+(blockKind2 ? ' '+blockKind2 : '')+''+(dataType ? ' type '+dataColour2 : '')+'" data-action="remove-block" data-block="'+dataBlock+'"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + '</span></a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'block';
            zoomTimeoutDuration = 300; // if we show a block dropdown, we want to zoom in quickly
            }
        }
    else if (firstEventType === 'hazard'){
        //console.log('-> event at position is a hazard, preparing dropdown');
        // If the cursor is literally on a hazard, only one event sprite matters right now
        let $hazardEvent = $(firstEvent.sprite);
        let dataLabel = $hazardEvent.attr('data-label');
        let dataHazard = $hazardEvent.attr('data-hazard');
        let dataColour = $hazardEvent.attr('data-colour') || 'none';
        let hazardInfo = _config.mapHazardsIndex[dataHazard] || false;
        let hazardKind = firstEvent.kind;
        let hazardKind2 = firstEvent.kind2;
        let hazardType = hazardInfo.type ? hazardInfo.type : '';
        let hazardWeaknesses = hazardInfo.weaknesses ? hazardInfo.weaknesses : [];
        let hazardEffects = hazardInfo.effects ? hazardInfo.effects : [];
        let preventStartupHazard = !_worldCursor.moved && standingOnHazardEvent ? true : false;
        //console.log('-> preventStartupHazard:', preventStartupHazard);
        if (dataHazard && hazardInfo && !preventStartupHazard && !playerIsCursor){
            //console.log('-> found hazardInfo for ' + dataHazard + ':', hazardInfo);
            //console.log('-> hazardType:', hazardType);
            //console.log('-> hazardWeaknesses:', hazardWeaknesses);
            //console.log('-> hazardEffects:', hazardEffects);
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            //var hazardName = (dataColour ? (dataColour[0].toUpperCase() + dataColour.slice(1) + ' ') : '') + 'Button';
            //if (!dataLabel){ dataLabel = 'Button Options'; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataColour ? ' type '+dataColour : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            //sideButtonsMarkup += '<strong class="button big-title'+(hazardKind2 ? ' '+hazardKind2 : '')+''+(dataColour ? ' type '+dataColour : '')+'"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + ' ?</span></strong>';
            //sideButtonsMarkup += '<strong class="button big-button'+(hazardKind2 ? ' '+hazardKind2 : '')+' type empty disabled"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + ' ?</span></strong>';
            //sideButtonsMarkup += '<strong class="button big-button-title type empty'+(hazardKind2 ? ' '+hazardKind2 : '')+'"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + ' ?</span></strong>';
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + ' ?</span></strong>';
            if (hazardWeaknesses.length){
                let robotsIndex = _indexes.robots;
                let abilitiesIndex = _indexes.abilities;
                let robotStringsUsed = [];
                for (let i = 0; i < hazardWeaknesses.length; i++){
                        let weaknessType = hazardWeaknesses[i];
                        let buttonColour = weaknessType;
                        //console.log('checking for weaknessType:' + weaknessType + ' ... ');
                        let robotsWithAbilityType = _self.getPlayerRobotsWithAbilityType(weaknessType);
                        robotsWithAbilityType = robotsWithAbilityType ? robotsWithAbilityType.filter(function(robot){
                            let robotString = robot[0], robotData = _worldPlayerRobots[robotString];
                            if (robotStringsUsed.indexOf(robotString) !== -1){ return false; }
                            if (!robotData || !robotData.weapons){ return false; }
                            if (robotData.weapons < 1){ return false; }
                            return true;
                            }) : false;
                        //console.log('robotsWithAbilityTypee(filtered) =', robotsWithAbilityType);
                        let firstRobotWithAbilityType = robotsWithAbilityType ? robotsWithAbilityType[0] : false;
                        if (firstRobotWithAbilityType){ robotStringsUsed.push(firstRobotWithAbilityType[0]); }
                        let playerRobotAvailable = firstRobotWithAbilityType ? true : false;
                        let playerRobotName = '', playerRobotString = '', playerRobotToken = '', playerRobotId = 0;
                        let $playerRobotSprite = null, robotSpriteMarkup = null;
                        if (playerRobotAvailable){ robotSpriteMarkup = _self.getRobotSpriteMarkup(firstRobotWithAbilityType[0], {dir: 'left', frame: '10'}); }
                        if (playerRobotAvailable && robotSpriteMarkup){
                            playerRobotString = firstRobotWithAbilityType[0];
                            playerRobotId = parseInt(playerRobotString.split('_')[0]);
                            playerRobotToken = playerRobotString.split('_')[1];
                            playerRobotName = toUpperCaseWords(playerRobotToken.replace('-', ' '));
                            $playerRobotSprite = $(robotSpriteMarkup);
                            $playerRobotSprite.addClass('team bounce');
                            $playerRobotSprite.attr('data-sprite', 'team-robot').attr('data-robot', playerRobotString).attr('data-frame', '10');
                            robotSpriteMarkup = $playerRobotSprite[0].outerHTML;
                            let abilityInfo = abilitiesIndex[firstRobotWithAbilityType[1]];
                            //console.log('abilitiesIndex =', abilitiesIndex);
                            //console.log('firstRobotWithAbilityType[1] =', firstRobotWithAbilityType[1]);
                            //console.log('abilityInfo =', abilityInfo);
                            buttonColour = abilityInfo.type + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '');
                            }
                        //console.log('-> robotsWithAbilityType:', robotsWithAbilityType, '\n-> playerRobotAvailable:', playerRobotAvailable);
                        //console.log('-> playerRobotName:', playerRobotName, '\n-> playerRobotId:', playerRobotId);
                        sideButtonsMarkup += '<a '
                            + ('class="button big-button'
                                + (hazardKind2 ? ' '+hazardKind2 : '')
                                + (buttonColour ? ' type '+buttonColour : '')
                                + (!playerRobotAvailable ? ' disabled' : '')
                                + '"')
                            + (playerRobotAvailable ?
                                ' data-action="remove-hazard"'
                                + ' data-hazard="'+dataHazard+'"'
                                + ' data-hazard-robot="'+firstRobotWithAbilityType[0]+'"'
                                + ' data-hazard-ability="'+firstRobotWithAbilityType[1]+'"'
                                : '')
                            + '>';
                        sideButtonsMarkup += (playerRobotAvailable
                            ? '<span class="has-sprite"><sup>Use</sup> ' + toUpperCaseWords(firstRobotWithAbilityType[1].replace('-', ' ')) + robotSpriteMarkup + '</span>'
                            : '<span>Use ' + toUpperCaseWords(weaknessType.replace('-', ' ')) + '</span>'
                            );
                        sideButtonsMarkup += '</a>';
                    }
                //console.log('robotStringsUsed =', robotStringsUsed);
                }
            //sideButtonsMarkup += '<a class="button big-button'+(hazardKind2 ? ' '+hazardKind2 : '')+''+(dataColour2 ? ' type '+dataColour2 : '')+'" data-action="remove-hazard" data-hazard="'+dataHazard+'"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + '</span></a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'hazard';
            zoomTimeoutDuration = 300; // if we show a hazard dropdown, we want to zoom in quickly
            // if we're standing on the hazard, make sure we also trigger the relevant effect
            if (standingOnHazardEvent
                && hazardInfo.action === 'trigger-effects'){
                //console.log('-> standing on a hazard "' + dataHazard + '" (' + hazardInfo.sprite + ')!!', '\n', 'defining trigger effects ...');
                let hazardToken = hazardInfo.sprite;
                let hazardEffect = hazardInfo.effects;
                let hazardTriggered = 0;
                let hazardEffectFunction = function(){
                    if (_selfRef.hazardTrapTimeout){ clearTimeout(_selfRef.hazardTrapTimeout); }
                    //if (_selfRef.hazardEffectTimeout){ clearTimeout(_selfRef.hazardEffectTimeout); }
                    $hazardEvent.addClass('always-zoom');
                    _world.isBusyWith.hazardEffectFunction = true;
                    let cancelHazardTrap = function(){
                        delete _world.isBusyWith.hazardEffectFunction;
                        $hazardEvent.removeClass('always-zoom');
                        $canvasMap.removeClass('shake-once');
                        };
                    if (!stillAtPosition()){ cancelHazardTrap(); return; }
                    _selfRef.hazardTrapTimeout = setTimeout(function(){ cancelHazardTrap(); }, 1600);
                    //_selfRef.hazardEffectTimeout = setTimeout(function(){ hazardEffectFunction.call(_self);  }, 3200);
                    //console.log('--> now executing trigger effect "', hazardEffect, '" !');
                    let effect = '', range = '', kind = '';
                    if (!hazardEffect || !hazardEffect.length){ return; }
                    let hazardEffectTokens = hazardEffect[0] ? (hazardEffect[0].split('-')) : [];
                    let hazardEffectValue = hazardEffect[1] ? parseInt(hazardEffect[1]) : 0;
                    if (hazardEffectTokens[0]){ effect = hazardEffectTokens[0]; }
                    if (hazardEffectTokens[1]){ range = hazardEffectTokens[1]; }
                    if (hazardEffectTokens[2]){ kind = hazardEffectTokens[2]; }
                    //console.log('breaking down to:', {effect, range, kind});
                    if (!effect || !range || !kind){ return; }
                    let _mapMessagesConfig = _config.mapMessages;
                    let kindIsEnergy = kind === 'energy';
                    let playSound = true;
                    let playAnimation = true;
                    let showMessage = true;
                    let effectsTriggered = 0;
                    let triggerFunction = null;
                    if (kind === 'energy'){
                        hazardEffectValue += '%';
                        if (effect === 'lower'){ triggerFunction = _self.damageRobotEnergy; }
                        else if (effect === 'raise') { triggerFunction = _self.restoreRobotEnergy; }
                        }
                    else if (kind === 'attack'){
                        if (effect === 'lower'){ triggerFunction = _self.breakRobotAttack; }
                        else if (effect === 'raise') { triggerFunction = _self.boostRobotAttack; }
                        }
                    else if (kind === 'defense'){
                        if (effect === 'lower'){ triggerFunction = _self.breakRobotDefense; }
                        else if (effect === 'raise') { triggerFunction = _self.boostRobotDefense; }
                        }
                    else if (kind === 'speed'){
                        if (effect === 'lower'){ triggerFunction = _self.breakRobotSpeed; }
                        else if (effect === 'raise') { triggerFunction = _self.boostRobotSpeed; }
                        }
                    for (let teamRobotKey = 0; teamRobotKey < _worldPlayerTeam.length; teamRobotKey++){
                        let teamRobotString = _worldPlayerTeam[teamRobotKey];
                        let effectTriggered = false;
                        _mapMessagesConfig.nextQueueStagger = 0; //Math.ceil(_mapMessagesConfig.queueStagger / _worldPlayerTeam.length);
                        //console.log('checking teamRobotString', teamRobotString, 'w/', {effect, range, kind});
                        if (typeof triggerFunction === 'function'){
                            effectTriggered = triggerFunction.call(_self, teamRobotString, hazardEffectValue, playSound, playAnimation, showMessage);
                            }
                        //console.log('effectTriggered =', effectTriggered);
                        if (effectTriggered){ effectsTriggered++; }
                        if (range !== 'team'){ break; }
                        // .... TODO: program the rest?
                        }
                    hazardTriggered++;
                    //console.log('effectsTriggered =', effectsTriggered);
                    //console.log('hazardTriggered =', hazardTriggered);
                    if (effectsTriggered){
                        $canvasMap.addClass('shake-once');
                        } else {
                        cancelHazardTrap();
                        clearTimeout(_selfRef.hazardTrapTimeout);
                        clearTimeout(_selfRef.hazardEffectTimeout);
                        $canvasMap.removeClass('shake-once');
                        }
                    };
                triggerEffect = true;
                readyTeamSprites = true;
                readyTeamSpritesAnyway = true;
                readyTeamPlayerFrames = ['05'];
                readyTeamRobotFrames = ['08'];
                teamReadyDuration = 300;
                triggerEffectFunction = function(){ hazardEffectFunction.call(_self); };
                triggerEffectSound = 'traintrack-sound';
                }
            }
        }
    else if (firstEventType === 'battle'){

        // Otherwise we can/should check all the posiitons for any battles to round-up and trigger
        let dataLabels = [], dataBattles = [];
        for (var i = 0; i < eventsAtPosition.length; i++){
            //console.log('-> parsing battleEvent from eventsAtPosition[i]', eventsAtPosition[i]);
            let battleEvent = eventsAtPosition[i];
            let dataPosition = battleEvent.position;
            var dataBattle = battleEvent.token;
            var dataLabel = battleEvent.label;
            if (dataBattle){
                dataLabels.push([dataLabel, dataPosition]);
                dataBattles.push([dataBattle, dataPosition]);
                }
            }
        let dataBattleStars = [];
        if (starsNearPosition.length){
            for (var i = 0; i < starsNearPosition.length; i++){
                //console.log('-> parsing starEvent from starsNearPosition[i]', starsNearPosition[i]);
                let starEvent = starsNearPosition[i];
                let dataPosition = starEvent.position;
                var dataStar = 'world-star_' + _worldToken + '_' + _mapToken + '_' + starEvent.token;
                //var dataLabel = starEvent.label;
                if (dataStar){
                    //dataLabels.push([dataLabel, dataPosition]);
                    dataBattleStars.push([dataStar, dataPosition]);
                    }
                }
            }
        //console.log('-> dataLabels =', dataLabels);
        //console.log('-> dataBattles =', dataBattles);
        //console.log('-> dataBattleStars =', dataBattleStars);
        //console.log('check the direction the player is facing and sort the battle events accordingly');
        //console.log('-> cursorDirection =', cursorDirection);
        //console.log('-> cursorPosition =', cursorPosition);
        let standingAtPosition = (cursorPosition.split('-')).map(function(num){ return parseInt(num); });
        let lookingAtPosition = [standingAtPosition[0], standingAtPosition[1]];
        if (cursorDirection.indexOf('right') !== -1){ lookingAtPosition[0] = lookingAtPosition[0] + 1; }
        else if (cursorDirection.indexOf('left') !== -1){ lookingAtPosition[0] = lookingAtPosition[0] - 1; }
        if (cursorDirection.indexOf('down') !== -1){ lookingAtPosition[1] = lookingAtPosition[1] + 1; }
        else if (cursorDirection.indexOf('up') !== -1){ lookingAtPosition[1] = lookingAtPosition[1] - 1; }
        //standingAtPosition = standingAtPosition.join('-');
        //lookingAtPosition = lookingAtPosition.join('-');
        //console.log('-> standingAtPosition =', standingAtPosition);
        //console.log('-> lookingAtPosition =', lookingAtPosition);
        // populate an array with the positions around the user, clockwise, but make it start at the position they're looking at
        let tilesAroundPosition = [];
        tilesAroundPosition.push((standingAtPosition[0] + 1) + '-' + (standingAtPosition[1] - 1)); // top-right
        tilesAroundPosition.push((standingAtPosition[0] + 1) + '-' + standingAtPosition[1]); // right
        tilesAroundPosition.push((standingAtPosition[0] + 1) + '-' + (standingAtPosition[1] + 1)); // bottom-right
        tilesAroundPosition.push(standingAtPosition[0] + '-' + (standingAtPosition[1] + 1)); // bottom
        tilesAroundPosition.push((standingAtPosition[0] - 1) + '-' + (standingAtPosition[1] + 1)); // bottom-left
        tilesAroundPosition.push((standingAtPosition[0] - 1) + '-' + standingAtPosition[1]); // left
        tilesAroundPosition.push((standingAtPosition[0] - 1) + '-' + (standingAtPosition[1] - 1)); // top-left
        tilesAroundPosition.push(standingAtPosition[0] + '-' + (standingAtPosition[1] - 1)); // top
        standingAtPosition = standingAtPosition.join('-');
        lookingAtPosition = lookingAtPosition.join('-');
        // rotate above so that whichever one matches lookingAtPosition is first but maintain order!!!
        do {
            let firstTile = tilesAroundPosition.shift();
            if (firstTile === lookingAtPosition){ tilesAroundPosition.push(firstTile); break; }
            else { tilesAroundPosition.push(firstTile); }
        } while (tilesAroundPosition[0] !== lookingAtPosition);
        //console.log('-> tilesAroundPosition =', tilesAroundPosition);
        //console.log('-> dataBattles after sorting by position:', dataBattles.join('\n'));
        if (dataLabels.length && dataBattles.length){
            showActionArea = true;
            readyTeamSprites = true;
            //console.log('showing dropdown with battles:', '\n->', dataBattles.join('\n-> '));
            let dataBattlesJoined = (function(battles){
                for (var i = 0, list = []; i < battles.length; i++){
                    let battle = battles[i][0], battlePosition = battles[i][1];
                    list.push(battle);
                    } return list;
                })(dataBattles).join(',');
            let dataBattleStarsJoined = (function(stars){
                for (var i = 0, list = []; i < stars.length; i++){
                    let star = stars[i][0], starPosition = stars[i][1];
                    list.push(star);
                    } return list;
                })(dataBattleStars).join(',');
            let dataLabelsJoined = (function(labels){
                for (var i = 0, markup = []; i < labels.length; i++){
                    let label = labels[i][0], labelPosition = labels[i][1];
                    let positionRelative = _self.getPositionRelative(cursorPosition, labelPosition);
                    let labelText = label;
                    let labelClasses = 'label sublabel';
                    labelClasses += ' ' + (positionRelative[0] === 0 ? 'center' : (positionRelative[0] < 0 ? 'left' : 'right'));
                    labelClasses += ' ' + (positionRelative[1] === 0 ? 'middle' : (positionRelative[1] < 0 ? 'up' : 'down'));
                    markup.push('<strong class="'+labelClasses+'" data-pos="' + labelPosition + '" data-rel="' + positionRelative + '">' + labelText + '</strong>');
                    } return markup;
                })(dataLabels).join('');
            //console.log('generated dataBattlesJoined:', '\n->', dataBattlesJoined.split(',').join('\n-> '));
            //console.log('generated dataBattleStarsJoined:', '\n->', dataBattleStarsJoined.split(',').join('\n-> '));
            let joinedDataAttrs = '';
            if (dataBattles.length){ joinedDataAttrs += ' data-battle="'+dataBattlesJoined+'"'; }
            if (dataBattleStars.length){ joinedDataAttrs += ' data-battle-star="'+dataBattleStarsJoined+'"'; }
            actionAreaMarkup += dataLabelsJoined;
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Engage With</sup> ' + (dataBattles.length > 1 ? dataBattles.length+' ' : '') + 'Target' + (dataBattles.length > 1 ? 's' : '') + ' ?</span></strong>';
            if (playerActiveRobots >= 1){
                sideButtonsMarkup += '<a class="button big-button" data-action="start-battle"' + joinedDataAttrs + '><span><sup>Ready To</sup> Start Battle</span></a>';
                //sideButtonsMarkup += '<a class="button big-button" data-action="start-battle"' + joinedDataAttrs + '><span>Start Battle!</span></a>';
                } else {
                sideButtonsMarkup += '<a class="button big-button disabled"' + joinedDataAttrs + '><span><sup>Ready To</sup> Start Battle</span></a>';
                }
            if (!playerIsCursor
                && dataBattles.length === 1
                && eventsAtPosition[0].kind2 === 'mecha'){
                //console.log('solo mecha event detected, generate the whistle button');
                let playerHasTeamSlot = _worldPlayerRobotsKeys.length < _config.maxRobotsPerPlayer ? true : false;
                let hasPermanentWhistle = false; //_playerToken === 'dr-lalinde';
                let mechaBattleEvent = eventsAtPosition[0];
                let mechaWhistleQuantity = _self.getItemQuantity('mecha-whistle');
                let mechaWhistleSpriteMarkup = _self.getItemSpriteMarkup('mecha-whistle');
                let mechaWhistleSpanLabel = '<sup>Use</sup> Mecha Whistle';
                let mechaWhistleTarget = mechaBattleEvent.token;
                //console.log('mechaBattleEvent =', mechaBattleEvent);
                //console.log('mechaWhistleQuantity =', mechaWhistleQuantity);
                //console.log('mechaWhistleTarget =', mechaWhistleTarget);
                if (!hasPermanentWhistle){ mechaWhistleSpanLabel += ' <sub>&times; ' + mechaWhistleQuantity + '</sub>' ; }
                let playerCanUseWhistle = (mechaWhistleQuantity > 0 || hasPermanentWhistle) && playerHasTeamSlot ? true : false;
                if (playerCanUseWhistle){ sideButtonsMarkup += '<a class="button big-button type shield" data-action="use-mecha-whistle" data-target="' + mechaWhistleTarget + '"><span class="has-sprite">' + mechaWhistleSpanLabel + mechaWhistleSpriteMarkup + '</span></a>'; }
                else { sideButtonsMarkup += '<a class="button big-button disabled"><span class="has-sprite">' + mechaWhistleSpanLabel + mechaWhistleSpriteMarkup + '</span></a>'; }
                }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            //console.log('sideButtonsMarkup =', sideButtonsMarkup);
            showActionAreaType = 'battle';
            //showActionAreaSound = 'background-spawn';
            showActionAreaSound = 'mecha-taunt-sound' + (dataBattles.length > 1 ? '*'+dataBattles.length : '');
            zoomTimeoutDuration = 1000; // otherwise if this is a battle we wait a moment
            }
        }
    else if (firstEventType === 'item'){
        //console.log('-> event at position is an item, preparing either dropdown or pickup');
        // If the cursor is literally on an item, only one event sprite matters right now
        let eventInfo = firstEvent;
        let itemInfo = _mapItemsIndex[eventInfo.token];
        let $itemEvent = $(firstEvent.sprite);
        //console.log('-> eventInfo =', eventInfo, '| itemInfo =', itemInfo, '| $itemEvent =', $itemEvent);
        let dataLabel = $itemEvent.attr('data-label');
        let dataItem = $itemEvent.attr('data-item');
        let dataItemToken = $itemEvent.attr('data-token');
        let dataColour = $itemEvent.attr('data-colour');
        //console.log('-> dataItem =', dataItem, '| dataItemToken =', dataItemToken, '| dataColour =', dataColour);
        if (!dataLabel){ dataLabel = dataItemToken; }
        if (!dataColour){ dataColour = 'none'; }
        let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
        //console.log('-> playerIsCursor =', playerIsCursor);
        if (dataItem && dataItemToken){
            // If the player is the cursor player, we should show the item pickup dropdown
            if (playerIsCursor){
                //console.log('-> player is cursor, preparing item pickup/putdown dropdown');
                showActionArea = true;
                if (!_worldCursor.holding){
                    // Normal item pickup, not already holding anything
                    //if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="pickup-item" data-item="'+dataItem+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                    } else if (_worldCursor.holding && _worldCursor.holding === 'item/'+dataItem){
                    // We're holding something and this is that item, we should allow dropping it
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="drop-item" data-item="'+dataItem+'"><span><sup>Put Down</sup> ' + dataLabel + '</span></a>';
                    } else if (_worldCursor.holding){
                    // Otherwise we're holding something else, so we should not allow picking up this item
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-item="'+dataItem+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                    }
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showActionAreaType = 'item';
                zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                }
            // Otherwise if this is a human player, we should trigger the auto-pickup functionality instead
            else {
                //console.log('-> player is human player, trying auto-pickup for item...');
                // If this item is NOT anchored, we can pick it up normally
                if (!itemInfo.anchored){
                    //console.log('-> item is not anchored so we can add run normal pickup function');
                    triggerEffect = true;
                    readyTeamSprites = true;
                    teamReadyDuration = 600; // for event panels we want to zoom in quickly
                    zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                    triggerEffectFunction = function(){
                        if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                        //console.log('-> running');
                        // If the first event in the list (before sorting) is an item, we should defer it to the pickup function
                        //console.log('-> first event is an item, deferring to pickup function');
                        _self.triggerItemPickup(firstEvent, 600);
                        };
                    }
                // Otherwise we just display a disabled "Pick Up" menu hinting the player should switch to cursor
                else {
                    //console.log('-> item is anchored so we cannot pick it up, showing disabled dropdown');
                    showActionArea = true;
                    if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-item="'+dataItem+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                    sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                    showActionAreaType = 'item';
                    zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                    }
                }
            }
        }
    else if (firstEventType === 'ability'){
        //console.log('-> event at position is an ability, preparing either dropdown or pickup');
        // If the cursor is literally on a button, only one event sprite matters right now
        let eventInfo = firstEvent;
        let abilityInfo = _mapAbilitiesIndex[eventInfo.token];
        let $abilityEvent = $(firstEvent.sprite);
        //console.log('-> eventInfo =', eventInfo, '| abilityInfo =', abilityInfo, '| $abilityEvent =', $abilityEvent);
        let dataLabel = $abilityEvent.attr('data-label');
        let dataAbility = $abilityEvent.attr('data-ability');
        let dataAbilityToken = $abilityEvent.attr('data-token');
        let dataColour = $abilityEvent.attr('data-colour');
        //console.log('-> dataAbility =', dataAbility, '| dataAbilityToken =', dataAbilityToken, '| dataColour =', dataColour);
        if (!dataLabel){ dataLabel = dataAbilityToken; }
        if (!dataColour){ dataColour = 'none'; }
        let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
        //console.log('-> playerIsCursor =', playerIsCursor);
        if (dataAbility && dataAbilityToken){
            // If the player is the cursor player, we should show the ability pickup dropdown
            if (playerIsCursor){
                //console.log('-> player is cursor, preparing ability pickup dropdown');
                showActionArea = true;
                if (!_worldCursor.holding){
                    // Normal ability pickup, not already holding anything
                    //if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="pickup-ability" data-ability="'+dataAbility+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                    } else if (_worldCursor.holding && _worldCursor.holding === 'ability/'+dataAbility){
                    // We're holding something and this is that ability, we should allow dropping it
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="drop-ability" data-ability="'+dataAbility+'"><span><sup>Put Down</sup> ' + dataLabel + '</span></a>';
                    } else if (_worldCursor.holding){
                    // Otherwise we're holding something else, so we should not allow picking up this ability
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-ability="'+dataAbility+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                    }
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showActionAreaType = 'button';
                zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                }
            // Otherwise if this is a human player, we should trigger the auto-pickup functionality instead
            else {
                //console.log('-> player is human player, trying auto-pickup for ability...');
                // If this ability is NOT anchored, we can pick it up normally
                if (!abilityInfo.anchored){
                    //console.log('-> ability is not anchored so we can add run normal pickup function');
                    triggerEffect = true;
                    readyTeamSprites = true;
                    teamReadyDuration = 600; // for event panels we want to zoom in quickly
                    zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                    triggerEffectFunction = function(){
                        if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                        //console.log('-> running');
                        // If the first event in the list (before sorting) is an ability, we should defer it to the pickup function
                        //console.log('-> first event is an ability, deferring to pickup function');
                        _self.triggerAbilityPickup(firstEvent, 600);
                        };
                    }
                // Otherwise we just display a disabled "Pick Up" menu hinting the player should switch to cursor
                else {
                    //console.log('-> ability is anchored so we cannot pick it up, showing disabled dropdown');
                    showActionArea = true;
                    if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                    sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-ability="'+dataAbility+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                    sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                    showActionAreaType = 'ability';
                    zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                    }
                }
            }
        }
    else if (firstEventType === 'actor'){
        //console.log('-> event at position is a actor, preparing dropdown');
        // If the cursor is literally on a actor, only one event sprite matters right now
        let eventInfo = firstEvent;
        let actorInfo = _mapActorsIndex[eventInfo.token];
        let actorSymbol = _mapActorSymbols[eventInfo.position];
        let $actorEvent = $(eventInfo.sprite);
        let dataActor = $actorEvent.attr('data-actor');
        let dataLabel = $actorEvent.attr('data-label');
        let dataColour = $actorEvent.attr('data-colour');
        //console.log('eventInfo =', eventInfo);
        //console.log('actorInfo =', actorInfo);
        //console.log('actorSymbol =', actorSymbol);
        //console.log('$actorEvent =', typeof $actorEvent, $actorEvent);
        //console.log('dataActor =', dataActor);
        //console.log('dataLabel =', dataLabel);
        //console.log('dataColour =', dataColour);
        //console.log('_playerIndexInfo =', _playerIndexInfo);
        if (dataActor && !actorInfo.removed && !playerIsCursor){
            showActionArea = true;
            if (!dataLabel){ dataLabel = 'Denizen'; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
            if (actorInfo.messages){
                let avatarSprite = '&hellip;';
                if (actorInfo.kind === 'player'){ avatarSprite = _self.getPlayerSpriteMarkup(actorInfo.sprite, {alt: actorInfo.image, kind: 'mug'}); }
                else if (actorInfo.kind === 'robot'){ avatarSprite = _self.getRobotSpriteMarkup(actorInfo.sprite, {alt: actorInfo.image, kind: 'mug'}); }
                let messagesText = typeof actorInfo.messages === 'object' ? actorInfo.messages.join('\n') : actorInfo.messages;
                messagesText = messagesText.replaceAll('//', '<br />');
                messagesText = messagesText.replaceAll('{player_name}', _playerIndexInfo['name']);
                sideButtonsMarkup += '<div class="button-title big-button-title'+(dataColour ? ' type '+dataColour : '')+'">';
                    sideButtonsMarkup += '<i class="icon">' + avatarSprite + '</i>';
                    sideButtonsMarkup += '<p class="text">' + messagesText + '</p>';
                sideButtonsMarkup += '</div>';
                }
            //sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="greet-actor" data-actor="'+dataActor+'"><span>Talk To ' + dataLabel + '</span></a>';
            if (actorInfo.actions && actorInfo.actions.length){
                for (let i = 0; i < actorInfo.actions.length; i++){
                    let action = actorInfo.actions[i];
                    let label = typeof action[0] !== 'undefined' && action[0] ? action[0] : false;
                    let token = typeof action[1] !== 'undefined' && action[1] ? action[1] : false;
                    let colour = typeof action[2] !== 'undefined' && action[2] ? action[2] : false;
                    if (!label || !token){ continue; }
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
                    if (enabled){ sideButtonsMarkup += '<a class="button big-button'+(colour ? ' type '+colour : '')+'" data-actor="'+dataActor+'" data-action="'+token+'">' + innerHtml + '</a>'; }
                    else { sideButtonsMarkup += '<a class="button big-button'+(colour ? ' type '+colour : '')+' disabled">' + innerHtml + '</a>'; }
                    }
                }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'actor';
            zoomTimeoutDuration = 500; // if we show an actor dropdown, we want to zoom in quickly
            }
        }

    // If there's no dropdown to show, we can return early
    if (!showActionArea && !autoRedirect && !triggerEffect){ return; }

    // Define an inline function to put the team into their battle-ready poses
    let getTeamSpritesReady = function(){
        //console.log('%c' + 'getTeamSpritesReady()', 'color: cyan;');
        if (_self.worldIsBusy() && !readyTeamSpritesAnyway){ return; }
        if (!stillAtPosition() || otherMenusActiveNow()){ return; }

        // Add the shake class to the cursor so it hides behind the player
        $worldCursor.addClass('shake');

        // Collect the position of the firstEvent (the one we're triggering) and use it to
        // decide which direction the cursor needs to face in order to "see" the first event
        //console.log('firstEvent = ', firstEvent);
        //console.log('team is facing firstEvent:', '\n-> token:', firstEvent.token, '\n-> sprite:', firstEvent.sprite);
        let cursorPosition = _worldCursor.position;
        let cursorPositionXY = cursorPosition.split('-').map(function(num){ return parseInt(num); });
        let cursorDirection = _worldCursor.direction;
        let firstEventPosition = firstEvent.position;
        let firstEventPositionXY = firstEventPosition.split('-').map(function(num){ return parseInt(num); });
        let firstEventDirection = [];
        if (firstEventPositionXY[0] > cursorPositionXY[0]){ firstEventDirection.push('right'); }
        else if (firstEventPositionXY[0] < cursorPositionXY[0]){ firstEventDirection.push('left'); }
        if (firstEventPositionXY[1] < cursorPositionXY[1]){ firstEventDirection.push('up'); }
        else if (firstEventPositionXY[1] > cursorPositionXY[1]){ firstEventDirection.push('down'); }
        firstEventDirection = firstEventDirection.join('-');
        //console.log('-> cursorPosition =', cursorPosition, cursorPositionXY);
        //console.log('-> cursorDirection =', cursorDirection);
        //console.log('-> firstEventPosition =', firstEventPosition, firstEventPositionXY);
        //console.log('-> firstEventDirection =', firstEventDirection);

        // Collect details about the world cursor direction so we know where we're looking
        //let goingUp = _worldCursor.direction.indexOf('up') !== -1 ? true : false;
        //let goingDown = _worldCursor.direction.indexOf('down') !== -1 ? true : false;
        //let goingLeft = _worldCursor.direction.indexOf('left') !== -1 ? true : false;
        //let goingRight = _worldCursor.direction.indexOf('right') !== -1 ? true : false;
        let goingUp = firstEventDirection.indexOf('up') !== -1 ? true : false;
        let goingDown = firstEventDirection.indexOf('down') !== -1 ? true : false;
        let goingLeft = firstEventDirection.indexOf('left') !== -1 ? true : false;
        let goingRight = firstEventDirection.indexOf('right') !== -1 ? true : false;
        let goingHorz = goingLeft || goingRight ? true : false;
        let goingVert = goingUp || goingDown ? true : false;

        // Collect all the team sprites and details about their current positions
        let rushDistanceX = Math.ceil(_mapTileSize[0] / 4);
        let rushDistanceY = Math.ceil(_mapTileSize[1] / 2); //Math.ceil(_mapTileSize[1] / 4);
        let playerFrames = readyTeamPlayerFrames.length ? readyTeamPlayerFrames : ['06', '01', '04'];
        let robotFrames = readyTeamRobotFrames.length ? readyTeamRobotFrames : ['04', '08', '01', '06', '10', '00', '04', '01'];
        let $cursorSprite = $teamSprites.filter('.sprite.cursor');
        let $otherSprites = $teamSprites.filter('.sprite:not(.cursor)');
        let $playerSprites = $otherSprites.filter('.sprite.player');
        let $robotSprites = $otherSprites.filter('.sprite.robot');
        //console.log(('-> eventsAtPosition =', eventsAtPosition);
        //console.log(('-> goingRight =', goingRight, '| goingLeft =', goingLeft, '| goingUp =', goingUp, '| goingDown =', goingDown);
        //console.log(('-> rushDistanceX =', rushDistanceX, '| rushDistanceY =', rushDistanceY);
        //console.log('-> $otherSprites =', $otherSprites);

        // If there are any effect to apply beforehand, do it now
        let willAscendUpward = autoRedirectAnimation === 'ascend-upward' ? true : false;
        let willDescendDownward = autoRedirectAnimation === 'descend-downward' ? true : false;
        let willFloatVertically = willAscendUpward || willDescendDownward ? true : false;
        let willSlideHorizontally = autoRedirectAnimation.indexOf('slide-') === 0 ? true : false;
        let willSlideDirection = willSlideHorizontally ? autoRedirectAnimation.replace('slide-', '') : false;
        //console.log('willSlideHorizontally =', willSlideHorizontally);
        //console.log('willSlideDirection =', willSlideDirection);
        let spriteClassBeforeMove = '', spriteClassAfterMove = '';
        if (autoRedirectEffect){
            if (autoRedirectEffect === 'glowing'){ spriteClassBeforeMove = 'glowing'; }
            else if (autoRedirectEffect === 'leaving'){ spriteClassBeforeMove = 'leaving'; }
            }
        if (autoRedirectAnimation){
            if (willAscendUpward){ spriteClassAfterMove = 'ascending'; }
            else if (willDescendDownward){ spriteClassAfterMove = 'descending'; }
            }
        let spriteBeforeMove = function(){
            if (!spriteClassBeforeMove){ return; }
            $(this).removeClass('shake bounce idle');
            $(this).addClass(spriteClassBeforeMove);
            };
        let spriteAfterMove = function(){
            if (!spriteClassAfterMove){ return; }
            $(this).addClass(spriteClassAfterMove);
            };

        // Change all team sprite frames and directions where needed
        $cursorSprite.attr('data-frame', '01');
        spriteBeforeMove.call($cursorSprite);
        $playerSprites.each(function(index){
            let $sprite = $(this);
            if ($sprite.is('.disabled')){ return; }
            let dataDir = $sprite.attr('data-dir') || 'right';
            if (goingLeft && dataDir !== 'left'){ $sprite.attr('data-dir', 'left'); }
            else if (goingRight && dataDir !== 'right'){ $sprite.attr('data-dir', 'right'); }
            let dataFrame = spriteClassAfterMove !== 'sliding' ? (playerFrames[index % playerFrames.length] || '00') : '07';
            $sprite.attr('data-frame', dataFrame);
            spriteBeforeMove.call($sprite);
            });
        $robotSprites.each(function(index){
            let $sprite = $(this);
            if ($sprite.is('.disabled')){ return; }
            let dataDir = $sprite.attr('data-dir') || 'right';
            if (goingLeft && dataDir !== 'left'){ $sprite.attr('data-dir', 'left'); }
            else if (goingRight && dataDir !== 'right'){ $sprite.attr('data-dir', 'right'); }
            let dataFrame = spriteClassAfterMove !== 'sliding' ? (robotFrames[index % robotFrames.length] || '00') : '07';
            $sprite.attr('data-frame', dataFrame);
            spriteBeforeMove.call($sprite);
            });

        // Move the team sprites in such a way that they point in the direction they're facing
        // We do this by first moving the player sprite(s) to the extreme edge of the panel (top/bottom/left/right/top-right/bottom-left/etc.)
        // Then we can loop through the others in order and position them relative to the player sprite (only align robot sprites to first player)
        let targetX, targetY, targetZ;
        if (_mapEffects.usePerspective === true){
            let targetOffset = _self.getLayerTileSpriteOffset(thisNewCol, thisNewRow);
            targetX = targetOffset.left;
            targetY = targetOffset.top;
            targetZ = targetY + 1;
            } else {
            targetX = (thisNewCol - 1) * _mapTileSize[0];
            targetY = (thisNewRow - 1) * _mapTileSize[1];
            targetZ = targetY + 1;
            }
        //console.log('-> targetX =', targetX, '\n-> targetY =', targetY, '\n-> targetZ =', targetZ);
        let spacingX = Math.ceil(_mapTileSize[0] / 4);
        let spacingY = Math.ceil(_mapTileSize[1] / 6);
        let playerPositions = [];
        $playerSprites.each(function(index){
            let $sprite = $(this);
            let spriteWidth = parseInt($sprite.attr('data-size')) || 40;
            let spriteHeight = parseInt($sprite.attr('data-size')) || 40;
            let posX = targetX;
            let posY = targetY;
            // use if instead of if/else to support combination directions
            if (goingRight){ posX = targetX + _mapTileSize[0] - spriteWidth - _mapTileSizeOffset[0]; } // align-right
            else if (goingLeft){ posX = targetX + _mapTileSizeOffset[0]; } // align-left
            else { posX = targetX + Math.ceil((_mapTileSize[0] - spriteWidth) / 2); } // align-center
            if (goingUp){ posY = (targetY + _mapTileSizeOffset[1]) - (_mapSpriteSizeOffset[1] / 1); } // align-top
            else if (goingDown){ posY = targetY + _mapTileSize[1] - spriteHeight - _mapTileSizeOffset[1]; } // align-bottom
            else { posY = targetY + Math.ceil((_mapTileSize[1] - spriteHeight) / 2) - (_mapSpriteSizeOffset[1] / 2); } // align-middle
            playerPositions.push([posX, posY]);
            $sprite.animate({left: posX + 'px', top: posY + 'px', zIndex: (posY + 10) }, teamRushDuration, spriteAfterMove);
            });
        let firstPlayerPosition = playerPositions[0] || [targetX, targetY];
        let robotSpriteKey = 0;
        $robotSprites.each(function(index){
            let $sprite = $(this);
            let spriteWidth = parseInt($sprite.attr('data-size')) || 40;
            let spriteHeight = parseInt($sprite.attr('data-size')) || 40;
            let posKey = robotSpriteKey++;
            let posX = firstPlayerPosition[0];
            let posY = firstPlayerPosition[1] - 4;
            let horzSpacing = Math.ceil(spacingX * (posKey + 1));
            let vertSpacing = Math.ceil(spacingY * (posKey + 1));
            if (goingHorz && goingVert){ horzSpacing = Math.ceil(horzSpacing / 2); }
            if (goingRight){ posX -= horzSpacing; } // align-right
            else if (goingLeft){ posX += horzSpacing; } // align-left
            if (goingUp){ posY += vertSpacing; } // align-top
            else if (goingDown){ posY -= vertSpacing; } // align-bottom
            $sprite.animate({left: posX + 'px', top: posY + 'px', zIndex: (posY + 10) }, teamRushDuration, spriteAfterMove);
            });
        // Make sure we put the cursor sprite lower (in z-index) than the team so it's like it's hiding
        //console.log('-> $otherSprites =', $otherSprites);
        let lowestOtherSpriteZ = (function($otherSprites){
            let lowestZ = null;
            $otherSprites.each(function(){
                let $sprite = $(this);
                let spriteZ = parseInt($sprite.css('zIndex'));
                //console.log('-> $sprite(' + $sprite.attr('class') + ') zIndex =', spriteZ);
                if (lowestZ === null || spriteZ < lowestZ){ lowestZ = spriteZ; }
                });
            return lowestZ !== null ? lowestZ : 1;
            })($otherSprites);
        //console.log('-> lowestOtherSpriteZ =', lowestOtherSpriteZ);
        $cursorSprite.attr('data-frame', '01').css({zIndex:(lowestOtherSpriteZ - 1)});
        setTimeout(function(){ spriteAfterMove.call($cursorSprite); }, Math.ceil(teamRushDuration * 0.8));
        // If we need to do a slide animation as well, do it after everything else
        if (willSlideHorizontally){
            let rushDuration = Math.ceil(teamRushDuration * 1.6);
            setTimeout(function(){
                let $sprites = $cursorSprite.add($playerSprites).add($robotSprites);
                let offsetDst = (_mapTileSize[0] * 2);
                let offsreenCss = {}; //{left: (willSlideDirection === 'left' ? (targetX - offsetDst) : (targetX + offsetDst)) + 'px'};
                if (willSlideDirection === 'left'){ offsreenCss.left = (targetX - offsetDst); }
                else if (willSlideDirection === 'right'){ offsreenCss.left = (targetX + offsetDst); }
                else if (willSlideDirection === 'up'){ offsreenCss.top = (targetY - offsetDst); }
                else if (willSlideDirection === 'down'){ offsreenCss.top = (targetY + offsetDst); }
                if (offsreenCss.top){ offsreenCss.zIndex = offsreenCss.top - 1; }
                $playerSprites.attr('data-frame', '07'); $robotSprites.attr('data-frame', '07');
                //$sprites.animate(offsreenCss, Math.ceil(teamRushDuration * 3.2));
                $sprites.each(function(){
                    rushDuration = Math.ceil(rushDuration * 1.2);
                    $(this).animate(offsreenCss, rushDuration);
                    });
                }, rushDuration);
            }
        };

    // Define an inline function to redirect to the portal if needed
    let redirectToLocation = function(){
        //console.log('%c' + 'redirectToLocation()', 'color: cyan;');
        if (_self.worldIsBusy()){ return; }
        if (!stillAtPosition() || otherMenusActiveNow()){ return; }
        if (autoRedirectSound){ _self.playSoundEffect(autoRedirectSound); }
        //if (autoRedirectEffect){ applyTeamEffect(autoRedirectEffect); }
        //if (autoRedirectAnimation){ applyTeamAnimation(autoRedirectAnimation); }
        if (autoRedirectURL){
            _self.incZoomLevel();
            $thisWorld.addClass('busy');
            //console.log('we should block input now...');
            _world.isBusyWith.redirectToLocation = true;
            _self.saveWorldState(function(){
                //if (_self.worldIsBusy()){ return; }
                if (!stillAtPosition() || otherMenusActiveNow()){ return; }
                else { _self.resetZoomLevel(); }
                _self.incZoomLevel();
                $thisWorld.addClass('loading');
                window.location.href = autoRedirectURL;
                _self.incZoomLevel();
                }, true, false);
            }
        return true;
        };

    // Define an inline function to zoom and show the dropdown which we'll call after a timeout
    let zoomAndShowDropdown = function(){
        //console.log('%c' + 'zoomAndShowDropdown()', 'color: cyan;');
        if (_self.worldIsBusy() && !showActionAreaAnyway){ return; }
        if (!stillAtPosition() || otherMenusActiveNow()){ return; }

        // Elevate the event sprite(s) to the zoom layer and add a zoom class to it so it's more visible
        let cursorPositionXY = cursorPosition.split('-');
        for (var i = 0; i < eventsAtPosition.length; i++){
            let eventData = eventsAtPosition[i];
            let $eventSprite = $(eventData.sprite);
            let eventPosition = $eventSprite.attr('data-pos');
            if ($eventSprite.hasClass('tile')){ continue; } // skip tiles
            let isRobot = $eventSprite.hasClass('robot');
            let isMecha = $eventSprite.hasClass('vs-mecha');
            let isMaster = $eventSprite.hasClass('vs-master');
            let isBoss = $eventSprite.hasClass('vs-boss');
            let isRescue = $eventSprite.hasClass('vs-rescue');
            let isActor = $eventSprite.hasClass('vs-actor');
            //console.log('-> eventsAtPosition['+i+'] / isRobot = ', isRobot);
            let dataSize = $eventSprite.attr('data-size') || 40;
            let $eventLayer = $eventSprite.closest('.layer');
            let eventLayer = $eventLayer.attr('data-layer');
            //let relativePosition = [eventPositionXY[0] - cursorPositionXY[0], eventPositionXY[1] - cursorPositionXY[1]];
            let relativePosition = _self.getPositionRelative(cursorPosition, eventPosition);
            let newDirection = false;
            if (relativePosition[0] < 0){ newDirection = 'right'; }
            else if (relativePosition[0] > 0){ newDirection = 'left'; }
            else if (cursorDirection.indexOf('right') !== -1){ newDirection = 'left'; }
            else if (cursorDirection.indexOf('left') !== -1){ newDirection = 'right'; }
            //$eventSprite.attr('data-layer', eventLayer);
            //console.log('-> moving event sprite to zoom layer', eventLayer, 'from events layer');
            setTimeout(function(){
                $eventSprite.filter(':not(.vs-rescue):not(.vs-actor)').addClass('zoom');
                $eventSprite.filter('.vs-rescue').addClass('busy');
                $eventSprite.filter('.vs-actor').addClass('busy');
                $eventLayer.addClass('has-zoom');
                if (newDirection){ $eventSprite.attr('data-dir', newDirection); }
                if (isRobot){
                    if (isMecha){ $eventSprite.attr('data-frame', '04'); }
                    else if (isMaster){ $eventSprite.attr('data-frame', '01'); }
                    else if (isBoss){ $eventSprite.attr('data-frame', '06'); }
                    else if (isRescue){ $eventSprite.attr('data-frame', '08'); }
                    else if (isActor){ $eventSprite.attr('data-frame', '01'); }
                    }
                }, 100);
            }

        // Move the action dropdown to the correct position, add the markup, and show it
        if (actionAreaMarkup.length){
            $actionDropdown.css({
                left: ((thisNewCol - 1) * _mapTileSize[0] + _mapTileSizeOffset[0]) + 'px',
                top: ((thisNewRow - 1) * _mapTileSize[1] + _mapTileSizeOffset[1]) + 'px',
                }).attr('data-dir', _worldCursor.direction).attr('data-type', showActionAreaType).attr('data-align', 'center');
            $actionDropdownWrapper.empty().html(actionAreaMarkup);
            }

        // Add the buttons to the sidebar area so that they are out-of-the-way
        $sideButtonsWrapper.empty().html(sideButtonsMarkup);

        // Define the function for dismissing the dropdown and side buttons
        let dismissDropdown = function(playSound){
            //console.log('-> dismissing action dropdown!');
            playSound = typeof playSound === 'boolean' ? playSound : true;
            if (playSound){ _self.playSoundEffect('back-click'); }
            enableDropdownButtons();
            $actionDropdown.removeClass('active');
            $actionDropdownWrapper.empty();
            $sideButtons.removeClass('active');
            $sideButtonsWrapper.empty();
            $worldCursor.removeClass('shake');
            $spritesLayer.removeClass('has-zoom');
            $('.sprite.zoom', $canvasMap).removeClass('zoom').removeClass('busy');
            $('.sprite[data-frame]:not(.disabled):not(.frame-lock)', $canvasMap).attr('data-frame', '00');
            };

        // Define a function for disabling a dropdown after an option's been clicked
        let disableDropdownButtons = function(){
            //console.log('disableDropdownButtons');
            $sideButtons.addClass('busy');
            $('.big-button', $sideButtons).attr('disabled', 'disabled');
            };

        // Define a function for re-enabling a dropdown after an option's done running
        let enableDropdownButtons = function(){
            //console.log('enableDropdownButtons');
            $sideButtons.removeClass('busy');
            $('.big-button', $sideButtons).removeAttr('disabled');
            };

        // Define the event to run when clicking one of these new action buttons
        let onActionButtonClick = function(e){
            //console.log('%c' + 'Action button clicked!', 'color: cyan;');
            //console.log('-> data-action =', $(this).attr('data-action'));
            e.preventDefault();
            let $button = $(this);
            let action = $button.attr('data-action') || false;
            if ($button.is('.disabled') || $button.is('[disabled]')){ return false; }
            if (!action){ console.error('-> no action found on button, skipping!'); return false; }
            let isBattle = action.indexOf('-battle') !== -1 || action.indexOf('battle-') !== -1;
            let isPortal = action.indexOf('-portal') !== -1 || action.indexOf('portal-') !== -1;
            let isButton = action.indexOf('-button') !== -1 || action.indexOf('button-') !== -1;
            let isSwitch = action.indexOf('-switch') !== -1 || action.indexOf('switch-') !== -1;
            let isGate = action.indexOf('-gate') !== -1 || action.indexOf('gate-') !== -1;
            let isLock = action.indexOf('-lock') !== -1 || action.indexOf('lock-') !== -1;
            let isItem = action.indexOf('-item') !== -1 || action.indexOf('item-') !== -1;
            let isAbility = action.indexOf('-ability') !== -1 || action.indexOf('ability-') !== -1;
            let isBlock = action.indexOf('-block') !== -1 || action.indexOf('block-') !== -1;
            let isHazard = action.indexOf('-hazard') !== -1 || action.indexOf('hazard-') !== -1;
            let isDismiss = action === 'dismiss';
            if (!isDismiss){ $button.addClass('clicked'); }
            if ($sideButtons.is('.busy') && !isDismiss){ return false; }
            disableDropdownButtons();
            if (isBattle){
                let battleId = $button.attr('data-battle') || false;
                let battleStarId = $button.attr('data-battle-star') || false;
                //console.log('-> battleId =', battleId);
                if (action === 'battle-info'){
                    //console.log('-> showing battle info for ID ' + battleId + '!');
                    alert('Battle ID: ' + battleId + '\n\nThis is where you would show battle details.');
                    }
                else if (action === 'start-battle'){
                    let activeRobots = Object.values(_worldPlayerTeam);
                    //console.log('activeRobots = ', activeRobots);
                    _self.playSoundEffect('lets-go-robots');
                    let battleVars = [];
                    battleVars.push('wap=false'); // i hate this
                    battleVars.push('flag_skip_fadein=true'); // its just faster
                    battleVars.push('this_user_id=' + _userId);
                    battleVars.push('this_player_id=' + _playerId);
                    battleVars.push('this_player_token=' + _playerToken);
                    battleVars.push('this_player_robots=' + activeRobots.join(','));
                    battleVars.push('this_battle_token=' + battleId);
                    if (battleStarId){ battleVars.push('this_star_token=' + battleStarId); }
                    let battleHref = 'battle.php?' + battleVars.join('&');
                    if (true){
                        $thisWorld.addClass('busy');
                        _self.incZoomLevel();
                        _self.saveWorldState(function(){
                            _self.incZoomLevel();
                            $thisWorld.addClass('loading');
                            window.location.href = battleHref;
                            _self.incZoomLevel();
                            }, true, false);
                        } else {
                        console.warn('battles disabled for testing!');
                        console.warn('-> battleHref =', battleHref);
                        }
                    }
                }
            else if (isPortal){
                let portalName = $button.attr('data-portal') || false;
                //console.log('-> portalName =', portalName);
                if (action === 'portal-info'){
                    //console.log('-> showing portal info for ID ' + portalName + '!');
                    alert('Portal Name: ' + portalName + '\n\nThis is where you would show portal details.');
                    }
                else if (action === 'enter-portal'){
                    //console.log('-> entering portal with name ' + portalName + '!');
                    _self.playSoundEffect('bounce-sound');
                    let portalHref = false;
                    if (portalName === 'spawn'){
                        portalHref = 'prototype.php'; // TODO: make the spawn actually go somewhere specific
                        } else if (portalName === 'exit'){
                        portalHref = 'prototype.php'; // TOPO: make the exit actually go somewhere specific
                        } else if (portalName.indexOf('goto__') !== -1){
                        let worldToken, mapToken;
                        let goToPath = portalName.replace(/^goto__/i, '').split('__');
                        if (goToPath[1]){ worldToken = goToPath[0]; mapToken = goToPath[1]; }
                        else { worldToken = _config.mapWorld; mapToken = goToPath[0]; }
                        portalHref = 'world.php?world=' + worldToken + '&map=' + mapToken;
                        }
                    if (portalHref){
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
                }
            else if (isButton){
                // TODO: action will always equal "push-button" but we should verify
                //console.log('-> world-button clicked with action:', action);
                let buttonsIndex = _config.mapButtonsIndex;
                let buttonStates = _world.buttons;
                let buttonName = $button.attr('data-button') || false;
                let buttonInfo = buttonName && (buttonsIndex && buttonsIndex[buttonName]) ? buttonsIndex[buttonName] : false;
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('> .sprite', $eventSprite) : false;
                if (!buttonName || !buttonInfo){ console.error('-> button name or info not found, cannot push button!'); return false; }
                if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot push button!'); return false; }
                if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot push button!'); return false; }
                // dismiss the dropdown and side buttons first
                dismissDropdown(false);
                // change the internal state of the button and update
                buttonInfo.state = 'down'; // change the button state to down
                buttonsIndex[buttonName] = buttonInfo; // sync button info with index
                buttonStates[buttonName] = 'down'; // sync button state with world state
                $eventSprite.attr('data-state', 'down'); // update the event sprite state
                // update the visual state of the button on-screen
                $innerSprite.removeClass('up').addClass('down'); // change the inner sprite to down state
                $eventSprite.removeClass('glow');
                // play sounds to indicate button has been pushed
                _self.playSoundEffect('button-click');
                _self.playSoundEffect('hyper-stomp-sound', {delay: 200});
                // shake the map briefly to indicate button has been pushed
                $canvasMap.addClass('shake-once');
                setTimeout(function(){ $canvasMap.removeClass('shake-once'); }, 1000);
                // If the button has a callback function, run it now
                (function(buttonInfo){
                    if (!buttonInfo.action){ return false; }
                    let buttonAction = buttonInfo.action;
                    let buttonData = buttonInfo.data || {};

                    // event action SET-GROUP-TERRAIN for buttons, switches, etc. to use
                    if (buttonAction === 'set-group-terrain'){
                        let groupName = buttonData[0] || false;
                        let terrainName = buttonData[1] || false;
                        if (!groupName){ console.error('-> groupName not provided, cannot set group terrain!'); return false; }
                        if (!terrainName){ console.error('-> terrainName not provided, cannot set group terrain!'); return false; }
                        // MULTI-LAYER UPDATE: Target layer 0 explicitly
                        let terrainTilesIndex = _world.layerTilesIndex['terrain_0'] || false;
                        if (!terrainTilesIndex){ console.error('-> terrain_0 not found in _world.layerTilesIndex, cannot set terrain!'); return false; }
                        let groupsIndex = _config.mapGroupsIndex;
                        let groupTiles = groupsIndex[groupName] || false;
                        if (!groupsIndex || !groupTiles){ console.error('-> groupsIndex not found, cannot set terrain!'); return false; }
                        // --- PASS 1: Set the new base terrain strings ---
                        for (let i = 0; i < groupTiles.length; i++){
                            let tileKey = groupTiles[i];
                            let tileData = terrainTilesIndex[tileKey];
                            if (!tileData){
                                console.error('-> tile data not found for tile', tileKey, ', cannot set terrain!');
                                continue;
                                }
                            // Ensure we are assigning just the base terrain name (strip any bitmasks if accidentally provided in config)
                            let targetTerrainBase = terrainName.split('-')[0];
                            tileData.sprite[1] = targetTerrainBase;
                            }
                        // --- PASS 2: Hand off to the helper to calculate edges and refresh the map! ---
                        _self.refreshTerrainEdges(groupTiles);
                        _self.calculateWalkableMapTiles(true);
                        }

                    // event action REMOVE-GROUP-BLOCKS for buttons, switches, etc. to use
                    if (buttonAction === 'remove-group-blocks'){
                        let groupName = buttonData[0] || false;
                        let blockFilter = buttonData[1] || false;
                        if (!groupName){ console.error('-> groupName not provided, cannot remove group blocks!'); return false; }
                        let groupsIndex = _config.mapGroupsIndex;
                        let groupTiles = groupsIndex[groupName] || false;
                        if (!groupsIndex || !groupTiles){ console.error('-> groupsIndex not found, cannot remove group blocks!'); return false; }
                        let blockSymbols = _config.mapBlockSymbols;
                        let blocksIndex = _config.mapBlocksIndex;
                        let blockRemovals = _world.blocks;
                        let blocksRemoved = 0;
                        for (let i = 0; i < groupTiles.length; i++){
                            let tilePosition = groupTiles[i];
                            let targetBlockName = blockSymbols[tilePosition] || false;
                            if (!targetBlockName){ continue; }
                            let blockInfo = blocksIndex[targetBlockName] || false;
                            if (!blockInfo || blockInfo.removed){ continue; }
                            if (blockFilter && blockFilter !== 'any' && blockInfo.sprite !== blockFilter){ continue; }
                            blockInfo.removed = true;
                            delete blockSymbols[tilePosition];
                            blocksIndex[targetBlockName] = blockInfo; // Sync block info back to the config index
                            blockRemovals[targetBlockName] = new Date().getTime(); // Sync claim timestamp with world state
                            let $blockSprite = $('.sprite.block[data-block="' + targetBlockName + '"]', $canvasMap);
                            if ($blockSprite.length){
                                $blockSprite.attr('data-state', 'removed').removeClass('glow');
                                $blockSprite.animate({opacity: 0, filter: 'brightness(2)'}, 600, function(){
                                    $(this).remove();
                                    });
                                blocksRemoved++;
                                }
                            }
                        if (blocksRemoved > 0){
                            _self.playSoundEffect('block-destroyed-sound', {delay: 200});
                            setTimeout(function(){
                                _self.calculateWalkableMapTiles(true);
                                _self.refreshMapPositionEvents();
                                }, 600);
                            _self.saveWorldState();
                            }
                        }
                    })(buttonInfo);
                }
            else if (isSwitch){
                // TODO: action will always equal "toggle-switch" but we should verify
                let switchesIndex = _config.mapSwitchesIndex;
                let switchStates = _world.switches;
                let switchName = $button.attr('data-switch') || false;
                let switchInfo = switchName && (switchesIndex && switchesIndex[switchName]) ? switchesIndex[switchName] : false;
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('> .sprite', $eventSprite) : false;
                if (!switchName || !switchInfo){ console.error('-> switch name or info not found, cannot toggle switch!'); return false; }
                if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot toggle switch!'); return false; }
                if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot toggle switch!'); return false; }
                dismissDropdown(false);
                let oldState = switchInfo.state || 'up';
                let newState = oldState === 'up' ? 'down' : 'up';
                switchInfo.state = newState;
                switchesIndex[switchName] = switchInfo;
                switchStates[switchName] = newState;
                $eventSprite.attr('data-state', newState);
                $innerSprite.removeClass(oldState).addClass(newState);
                $eventSprite.removeClass('glow');
                _self.playSoundEffect('button-click');
                _self.playSoundEffect('hyper-stomp-sound', {delay: 200});
                $canvasMap.addClass('shake-once');
                setTimeout(function(){ $canvasMap.removeClass('shake-once'); }, 1000);
                (function(switchInfo, newState){
                    if (!switchInfo.action){ return false; }
                    let switchAction = switchInfo.action;
                    let switchData = switchInfo.data || {};

                    // event action TOGGLE-GROUP-TERRAIN for toggling two terrain types
                    if (switchAction === 'toggle-group-terrain'){
                        let groupName = switchData[0] || false;
                        let terrainUp = switchData[1] || false;
                        let terrainDown = switchData[2] || false;
                        if (!groupName){ console.error('-> groupName not provided, cannot set group terrain!'); return false; }
                        if (!terrainUp || !terrainDown){ console.error('-> terrainUp or terrainDown not provided, cannot toggle group terrain!'); return false; }
                        // MULTI-LAYER UPDATE: Target layer 0 explicitly
                        let terrainTilesIndex = _world.layerTilesIndex['terrain_0'] || false;
                        if (!terrainTilesIndex){ console.error('-> terrain_0 not found in _world.layerTilesIndex, cannot toggle group terrain!'); return false; }
                        let groupsIndex = _config.mapGroupsIndex;
                        let groupTiles = groupsIndex[groupName] || false;
                        if (!groupTiles){ console.error('-> groupsIndex not found for groupName "' + groupName + '"!'); return false; }
                        // --- PASS 1: Only change the base strings ---
                        for (let i = 0; i < groupTiles.length; i++){
                            let tileKey = groupTiles[i];
                            let tileData = terrainTilesIndex[tileKey];
                            if (!tileData) continue;
                            let currentTerrain = tileData.sprite[1];
                            let currentTerrainBase = currentTerrain.split('-')[0];
                            let targetTerrainBase = currentTerrainBase;
                            if (currentTerrain === terrainUp || currentTerrainBase === terrainUp){ targetTerrainBase = terrainDown; }
                            else if (currentTerrain === terrainDown || currentTerrainBase === terrainDown){ targetTerrainBase = terrainUp; }
                            // Temporarily update just the base string in the index
                            tileData.sprite[1] = targetTerrainBase;
                            }
                        // --- PASS 2: Hand off to the helper to do the rest! ---
                        _self.refreshTerrainEdges(groupTiles);
                        _self.calculateWalkableMapTiles(true);
                        }

                    })(switchInfo, newState);
                }
            else if (isGate){
                // TODO: action will always equal "open-gate" but we should verify
                //console.log('-> gate-related action button clicked with action:', action);
                //console.log('--> gatesNearPosition =', gatesNearPosition);
                let gateSymbols = _config.mapGateSymbols;
                let gatesIndex = _config.mapGatesIndex;
                let gateRemovals = _world.gates;
                let baseGateName = $button.attr('data-gate') || false;
                let baseGateInfo = baseGateName && (gatesIndex && gatesIndex[baseGateName]) ? gatesIndex[baseGateName] : false;
                //console.log('-> baseGateName =', baseGateName);
                //console.log('-> baseGateInfo =', baseGateInfo);
                if (!baseGateName || !baseGateInfo){ console.error('-> gate name or info not found, cannot remove gate!'); return false; }
                let gatesToRemove = [];
                gatesToRemove.push({
                    name: baseGateName,
                    info: baseGateInfo,
                    $sprite: $(firstEvent.sprite),
                    position: baseGateInfo.pos
                    });
                for (let i = 0; i < gatesNearPosition.length; i++){
                    let adjGateInfo = gatesNearPosition[i];
                    let adjGatePosition = adjGateInfo.position;
                    let adjGateName = adjGatePosition && (gateSymbols && gateSymbols[adjGatePosition]) ? gateSymbols[adjGatePosition] : false;
                    if (adjGateName === baseGateName){ continue; }
                    //console.log('-> adjGateInfo =', adjGateInfo);
                    //console.log('-> adjGatePosition =', adjGatePosition);
                    //console.log('-> adjGateName =', adjGateName);
                    if (adjGateName && adjGateInfo && adjGateInfo.kind2 === baseGateInfo.sprite){
                        let $adjSprite = $('.sprite[data-gate="' + adjGateName + '"]', $canvasMap);
                        gatesToRemove.push({
                            name: adjGateName,
                            info: adjGateInfo,
                            $sprite: $adjSprite.length ? $adjSprite : null,
                            position: adjGateInfo.position
                            });
                        }
                    }
                //console.log('-> gatesToRemove =', gatesToRemove.length, gatesToRemove);
                dismissDropdown(false);
                let $allSprites = $();
                for (let i = 0; i < gatesToRemove.length; i++) {
                    let gName = gatesToRemove[i].name;
                    let gInfo = gatesToRemove[i].info;
                    let $sprite = gatesToRemove[i].$sprite;
                    let gPosition = gatesToRemove[i].position;
                    gInfo.removed = true;
                    delete gateSymbols[gPosition];
                    gatesIndex[gName] = gInfo;
                    gateRemovals[gName] = new Date().getTime();
                    if ($sprite) {
                        $sprite.attr('data-state', 'removed');
                        $sprite.removeClass('animate');
                        $allSprites = $allSprites.add($sprite);
                        }
                    }
                $canvasMap.addClass('shake-once');
                _self.playSoundEffect('gate-destroyed-sound', {delay: 100});
                $allSprites.css({transition:'none'});
                let animateInSteps = 4;
                $({stepTracker: 0}).animate({stepTracker: animateInSteps}, {duration: 400, easing: "linear", step: function(now){
                    let currentStep = Math.floor(now), progressFactor = currentStep / animateInSteps;
                    $allSprites.css({opacity: 1 - progressFactor, filter: 'brightness(' + (1 + progressFactor) + ')'});
                    }}).promise().done(function(){
                    $allSprites.remove();
                    $canvasMap.removeClass('shake-once');
                    _self.calculateWalkableMapTiles(true);
                    _self.refreshMapPositionEvents();
                    });
                _self.saveWorldState();
                }
            else if (isLock){
                // TODO: action will always equal "open-lock" but we should verify
                //console.log('-> lock-related action button clicked with action:', action);
                let lockSymbols = _config.mapLockSymbols;
                let locksIndex = _config.mapLocksIndex;
                let lockStates = _world.locks;
                let lockName = $button.attr('data-lock') || false;
                let lockInfo = lockName && (locksIndex && locksIndex[lockName]) ? locksIndex[lockName] : false;
                //console.log('-> lockName =', lockName);
                //console.log('-> lockInfo =', lockInfo);
                if (!lockName || !lockInfo){ console.error('-> lock name or info not found, cannot remove lock!'); return false; }
                else if (!lockInfo.locked){ console.error('-> lock is already opened, cannot open again!'); return false; }
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                //console.log('-> $eventSprite =', $eventSprite);
                //console.log('-> $innerSprite =', $innerSprite);
                let lockKind = lockInfo.sprite ? lockInfo.sprite : false;
                let lockKind2 = lockInfo.image ? lockInfo.image : false;
                let lockPosition = lockInfo.pos ? lockInfo.pos : false;
                //console.log('-> lockKind =', lockKind);
                //console.log('-> lockKind2 =', lockKind2);
                //console.log('-> lockPosition =', lockPosition);
                let lockType = lockInfo.type ? lockInfo.type : '';
                let lockCurrency = lockInfo.currency ? lockInfo.currency : '';
                let lockPrice = lockInfo.price ? lockInfo.price : 0;
                let currencyKind = lockCurrency.indexOf(':') !== -1 ? lockCurrency.split(':')[0] : lockCurrency;
                let currencySubKind = lockCurrency.indexOf(':') !== -1 ? lockCurrency.split(':')[1] : false;
                //console.log('-> lockType =', lockType);
                //console.log('-> lockCurrency =', lockCurrency);
                //console.log('-> lockPrice =', lockPrice);
                //console.log('-> currencyKind =', currencyKind);
                //console.log('-> currencySubKind =', currencySubKind);
                dismissDropdown(false);
                lockInfo.locked = false;
                locksIndex[lockName] = lockInfo; // Sync lock info back to the config index
                lockStates[lockName] = new Date().getTime(); // Sync lock claim/removal timestamp with world state
                _self.playSoundEffect('lock-opening-sound', {delay: 200});
                $canvasMap.addClass('shake-once');
                $eventSprite.css({filter: 'brightness(1)'});
                $eventSprite.animate({filter: 'brightness(2)'}, 600, function(){
                    $eventSprite.removeClass('locked');
                    $canvasMap.removeClass('shake-once');
                    $eventSprite.animate({filter: 'brightness(1)'}, 600);
                    if (lockKind === 'portal-flower'){ _self.playSoundEffect('lock-activated-sound', {delay: 200}); }
                    else { _self.playSoundEffect('lock-destroyed-sound', {delay: 200}); }
                    _self.calculateWalkableMapTiles(true);
                    _self.refreshMapPositionEvents();
                    });
                // Deduct the required currency from the player's inventory (maybe)
                if (currencyKind === 'stars'){
                    // Stars are special and aren't "taken away" when used, they are just shown
                    //console.log('-> stars open locks without being consumed');
                    }
                else if (currencyKind === 'zenny'){
                    // Deduct zenny from the player
                    //console.log('-> deducting ', lockPrice, ' zenny from player, new zenny = ', (_worldPlayer.zenny - lockPrice));
                    _worldPlayer.zenny -= lockPrice;
                    if (_worldPlayer.zenny < 0){ _worldPlayer.zenny = 0; }
                    }
                else if (currencyKind === 'items'){
                    // Deduct the specific item from the player
                    //console.log('-> checking player has ', currencySubKind, ' in inventory ...');
                    if (currencySubKind.length && typeof _worldPlayer.items[currencySubKind] !== 'undefined'){
                        //console.log('-> deducting ', lockPrice, currencySubKind, ' from player, new amount = ', (_worldPlayer.items[currencySubKind] - lockPrice));
                        _worldPlayer.items[currencySubKind] -= lockPrice;
                        if (_worldPlayer.items[currencySubKind] < 0){ _worldPlayer.items[currencySubKind] = 0; }
                        }
                    }
                // Check if there's a portal below to be unlocked with this change
                if (typeof _config.mapPortalSymbols[lockPosition] !== 'undefined'){
                    //console.log('-> portal to unlock at position ', lockPosition, '!');
                    let portalSymbols = _config.mapPortalSymbols;
                    let portalsIndex = _config.mapPortalsIndex;
                    let portalName = portalSymbols[lockPosition];
                    let portalInfo = portalName ? portalsIndex[portalName] : false;
                    let $portalEvent = portalName ? $('.sprite[data-portal="' + portalName + '"]', $canvasMap) : false;
                    if (portalInfo && portalInfo.locked){ portalInfo.locked = false; portalsIndex[portalName] = portalInfo; } // sync to parent
                    if ($portalEvent && $portalEvent.length && $portalEvent.is('.locked')){ $portalEvent.removeClass('locked'); } // update the map
                    _self.calculateWalkableMapTiles(true);
                    }
                // Now we can save the game proper
                _self.saveWorldState();
                }
            else if (isItem){
                //console.log('-> item button clicked with action:', action);
                let itemSymbols = _config.mapItemSymbols;
                let itemsIndex = _config.mapItemsIndex;
                let itemClaims = _world.items;
                let itemName = $button.attr('data-item') || false;
                let itemInfo = itemName && (itemsIndex && itemsIndex[itemName]) ? itemsIndex[itemName] : false;
                let itemPosition = itemInfo ? itemInfo.pos : false;
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                //console.log('-> itemName =', itemName);
                //console.log('-> itemInfo =', itemInfo);
                //console.log('-> $eventSprite =', $eventSprite);
                //console.log('-> $innerSprite =', $innerSprite);
                if (!itemName || !itemInfo){ console.error('-> item name or info not found, cannot pick up item!'); return false; }
                // dismiss the dropdown and side buttons first
                dismissDropdown(false);
                // collect refs to the cursor palette and temp item slots
                let $cursorPalette = _elements.cursorPalette;
                let $tempItemSlots = $('.slots.temp', $cursorPalette);
                // if this is a pickup action, process normally
                if (action === 'pickup-item'){
                    //console.log('-> picking up item:', itemName);
                    // check to make sure we have an open even slot to drop the item into
                    let $firstOpenTempSlot = $('.slot:not(.active)', $tempItemSlots).first();
                    if (!$firstOpenTempSlot.length){
                        console.error('-> no open temp slots found in palette, cannot pick up item!');
                        return false;
                        }
                    $cursorPalette.addClass('active');
                    $worldCursor.addClass('pickup');
                    // clone the item to the cursor palette to show it being picked up
                    let newTop = _config.worldHeight + 200;
                    let newLeft = (parseInt($eventSprite.css('left')) || 0) - 200;
                    let $clonedEventSprite = $eventSprite.clone();
                    _self.playSoundEffect('upward-impact');
                    setTimeout(function(){
                        _self.playSoundEffect('suck-sound');
                        $clonedEventSprite.css({top:'',left:'',zIndex:''}); // reset the event sprite position
                        $clonedEventSprite.appendTo($firstOpenTempSlot).addClass('new');; // move the event sprite to the temp item slot
                        $firstOpenTempSlot.addClass('active').attr('data-item', itemName);
                        setTimeout(function(){
                            $worldCursor.removeClass('pickup');
                            $clonedEventSprite.removeClass('new');
                            }, 3000);
                        }, 600);
                    // make the event sprite track the cursor's movement until we put it down
                    itemInfo.beingHeld = true; // set the item info tracking state to cursor
                    _worldCursor.holding = 'item/'+itemName; //{kind: 'item', name: itemName, item: itemInfo};
                    $eventSprite.addClass('tracking-cursor');
                    let targetLeft = parseInt($worldCursor.css('left') || 0) + 10;
                    let targetTop = parseInt($worldCursor.css('top') || 0) + 5;
                    let targetZ = parseInt($worldCursor.css('zIndex') || 0) - 1;
                    $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                    if ($eventSprite.is('.always-zoom')){ $eventSprite.addClass('not-always-zoom').removeClass('always-zoom'); }
                    // check to see if this spot below this was a drop-zone
                    //console.log('-> checking for event under picked-up item position:', itemPosition);
                    if (_mapEventSymbols[itemPosition]){
                        let eventUnderPosition = _mapEventSymbols[itemPosition];
                        let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                        //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                        if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                            //console.log('-> item picked-up from drop-zone event, triggering drop-zone empty action');
                            _self.triggerDropZoneEmpty(eventUnderPosition, eventUnderInfo);
                            }
                        }
                    }
                // else if this is a drop action, we need to do a bit more work
                else if (action === 'drop-item'){
                    //console.log('-> dropping item:', itemName);
                    // check to make sure we have an active temp item slot holding an item
                    let $firstActiveTempSlot = $('.slot.active', $tempItemSlots).first();
                    if (!$firstActiveTempSlot.length){
                        console.error('-> no active temp slots found in palette, cannot drop item!');
                        return false;
                        }
                    _worldCursor.busy = true; // mark the cursor as busy while we drop the item
                    $cursorPalette.removeClass('active');
                    $worldCursor.addClass('drop');
                    // remove the item from the cursor pallet first and formost
                    let $clonedEventSprite = $firstActiveTempSlot.find('.sprite');
                    $clonedEventSprite.addClass('dropped');
                    _self.playSoundEffect('suck-sound');
                    _self.playSoundEffect('downward-impact', {delay:400});
                    setTimeout(function(){
                        $firstActiveTempSlot.removeClass('active').attr('data-item', '');
                        setTimeout(function(){
                            $worldCursor.removeClass('drop');
                            $clonedEventSprite.remove();
                            }, 3000);
                        }, 600);
                    // detach the event sprite from the cursor's movement so that it's actually put down
                    _worldCursor.holding = '';
                    $eventSprite.removeClass('tracking-cursor');
                    let newItemPosition = _worldCursor.position;
                    let newItemPositionXY = newItemPosition.split('-');
                    let targetLeft = ((newItemPositionXY[0] - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0];
                    let targetTop = ((newItemPositionXY[1] - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1];
                    let targetZ = targetTop + 1;
                    $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                    if ($eventSprite.is('.not-always-zoom')){ $eventSprite.addClass('always-zoom').removeClass('not-always-zoom'); }
                    // update the event sprite position to the new position
                    let oldItemPosition = itemInfo.pos;
                    itemInfo.pos = newItemPosition; // update the item position in the info
                    itemInfo.position = newItemPositionXY; // update the item position in the info
                    itemInfo.col = parseInt(newItemPositionXY[0]) || 1; // update the item column in the info
                    itemInfo.row = parseInt(newItemPositionXY[1]) || 1; // update the item row in the info
                    itemInfo.beingHeld = false; // set the item info tracking state to not being held
                    delete itemSymbols[oldItemPosition];
                    itemSymbols[newItemPosition] = itemName; // update the event symbols with the new position
                    // save these changes to the world state
                    //console.log('-> saving item relocation to the world state!');
                    //console.log('-> ', itemName, ' moved from', oldItemPosition, 'to', newItemPosition);
                    if (typeof _worldSymbols.items === 'undefined'){ _worldSymbols.items = {}; }
                    _worldSymbols.items[itemName] = newItemPosition; // create a redirect pointer for the new item position
                    // check to see if this spot below this was a drop-zone
                    //console.log('-> checking for event under new item position:', newItemPosition);
                    if (_mapEventSymbols[newItemPosition]){
                        let eventUnderPosition = _mapEventSymbols[newItemPosition];
                        let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                        //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                        if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                            //console.log('-> item dropped onto drop-zone event, triggering drop-zone action for item:', itemName);
                            _self.triggerDropZoneEvent(eventUnderPosition, eventUnderInfo, 'item', itemName, itemInfo);
                            }
                        }
                    // save all these changes to the world state now
                    _self.saveWorldState(function(){
                        _worldCursor.busy = false;
                        });
                    }
                }
            else if (isAbility){
                //console.log('-> ability button clicked with action:', action);
                let abilitySymbols = _config.mapAbilitySymbols;
                let abilitiesIndex = _config.mapAbilitiesIndex;
                let abilityClaims = _world.abilities;
                let abilityName = $button.attr('data-ability') || false;
                let abilityInfo = abilityName && (abilitiesIndex && abilitiesIndex[abilityName]) ? abilitiesIndex[abilityName] : false;
                let abilityPosition = abilityInfo ? abilityInfo.pos : false;
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                //console.log('-> abilityName =', abilityName);
                //console.log('-> abilityInfo =', abilityInfo);
                //console.log('-> $eventSprite =', $eventSprite);
                //console.log('-> $innerSprite =', $innerSprite);
                if (!abilityName || !abilityInfo){ console.error('-> ability name or info not found, cannot pick up ability!'); return false; }
                // dismiss the dropdown and side buttons first
                dismissDropdown(false);
                // collect refs to the cursor palette and temp ability slots
                let $cursorPalette = _elements.cursorPalette;
                let $tempAbilitySlots = $('.slots.temp', $cursorPalette);
                // if this is a pickup action, process normally
                if (action === 'pickup-ability'){
                    //console.log('-> picking up ability:', abilityName);
                    // check to make sure we have an open even slot to drop the ability into
                    let $firstOpenTempSlot = $('.slot:not(.active)', $tempAbilitySlots).first();
                    if (!$firstOpenTempSlot.length){
                        console.error('-> no open temp slots found in palette, cannot pick up ability!');
                        return false;
                        }
                    $cursorPalette.addClass('active');
                    $worldCursor.addClass('pickup');
                    // clone the ability to the cursor palette to show it being picked up
                    let newTop = _config.worldHeight + 200;
                    let newLeft = (parseInt($eventSprite.css('left')) || 0) - 200;
                    let $clonedEventSprite = $eventSprite.clone();
                    setTimeout(function(){
                        $clonedEventSprite.css({top:'',left:'',zIndex:''}); // reset the event sprite position
                        $clonedEventSprite.appendTo($firstOpenTempSlot).addClass('new');; // move the event sprite to the temp ability slot
                        $firstOpenTempSlot.addClass('active').attr('data-ability', abilityName);
                        setTimeout(function(){
                            $worldCursor.removeClass('pickup');
                            $clonedEventSprite.removeClass('new');
                            }, 3000);
                        }, 600);
                    // make the event sprite track the cursor's movement until we put it down
                    abilityInfo.beingHeld = true; // set the ability info tracking state to cursor
                    _worldCursor.holding = 'ability/'+abilityName; //{kind: 'ability', name: abilityName, ability: abilityInfo};
                    $eventSprite.addClass('tracking-cursor');
                    let targetLeft = parseInt($worldCursor.css('left') || 0) + 10;
                    let targetTop = parseInt($worldCursor.css('top') || 0) + 5;
                    let targetZ = parseInt($worldCursor.css('zIndex') || 0) - 1;
                    $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                    if ($eventSprite.is('.always-zoom')){ $eventSprite.addClass('not-always-zoom').removeClass('always-zoom'); }
                    // check to see if this spot below this was a drop-zone
                    //console.log('-> checking for event under picked-up ability position:', abilityPosition);
                    if (_mapEventSymbols[abilityPosition]){
                        let eventUnderPosition = _mapEventSymbols[abilityPosition];
                        let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                        //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                        if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                            //console.log('-> ability picked-up from drop-zone event, triggering drop-zone empty action');
                            _self.triggerDropZoneEmpty(eventUnderPosition, eventUnderInfo);
                            }
                        }
                    }
                // else if this is a drop action, we need to do a bit more work
                else if (action === 'drop-ability'){
                    //console.log('-> dropping ability:', abilityName);
                    // check to make sure we have an active temp ability slot holding an ability
                    let $firstActiveTempSlot = $('.slot.active', $tempAbilitySlots).first();
                    if (!$firstActiveTempSlot.length){
                        console.error('-> no active temp slots found in palette, cannot drop ability!');
                        return false;
                        }
                    $cursorPalette.removeClass('active');
                    $worldCursor.addClass('drop');
                    // remove the ability from the cursor pallet first and formost
                    let $clonedEventSprite = $firstActiveTempSlot.find('.sprite');
                    $clonedEventSprite.addClass('dropped');
                    setTimeout(function(){
                        $firstActiveTempSlot.removeClass('active').attr('data-ability', '');
                        setTimeout(function(){
                            $worldCursor.removeClass('drop');
                            $clonedEventSprite.remove();
                            }, 3000);
                        }, 600);
                    // detach the event sprite from the cursor's movement so that it's actually put down
                    _worldCursor.holding = '';
                    $eventSprite.removeClass('tracking-cursor');
                    let newAbilityPosition = _worldCursor.position;
                    let newAbilityPositionXY = newAbilityPosition.split('-');
                    let targetLeft = ((newAbilityPositionXY[0] - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0];
                    let targetTop = ((newAbilityPositionXY[1] - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1];
                    let targetZ = targetTop + 1;
                    $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                    if ($eventSprite.is('.not-always-zoom')){ $eventSprite.addClass('always-zoom').removeClass('not-always-zoom'); }
                    // update the event sprite position to the new position
                    let oldAbilityPosition = abilityInfo.pos;
                    abilityInfo.pos = newAbilityPosition; // update the ability position in the info
                    abilityInfo.position = newAbilityPositionXY; // update the ability position in the info
                    abilityInfo.col = parseInt(newAbilityPositionXY[0]) || 1; // update the ability column in the info
                    abilityInfo.row = parseInt(newAbilityPositionXY[1]) || 1; // update the ability row in the info
                    abilityInfo.beingHeld = false; // set the ability info tracking state to not being held
                    delete abilitySymbols[oldAbilityPosition];
                    abilitySymbols[newAbilityPosition] = abilityName; // update the event symbols with the new position
                    // save these changes to the world state
                    //console.log('-> saving ability relocation to the world state!');
                    //console.log('-> ', abilityName, ' moved from', oldAbilityPosition, 'to', newAbilityPosition);
                    if (typeof _worldSymbols.abilities === 'undefined'){ _worldSymbols.abilities = {}; }
                    _worldSymbols.abilities[abilityName] = newAbilityPosition; // create a redirect pointer for the new ability position
                    // check to see if this spot below this was a drop-zone
                    //console.log('-> checking for event under new ability position:', newAbilityPosition);
                    if (_mapEventSymbols[newAbilityPosition]){
                        let eventUnderPosition = _mapEventSymbols[newAbilityPosition];
                        let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                        //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                        if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                            //console.log('-> ability dropped onto drop-zone event, triggering drop-zone action for ability:', abilityName);
                            _self.triggerDropZoneEvent(eventUnderPosition, eventUnderInfo, 'ability', abilityName, abilityInfo);
                            }
                        }
                    // save all these changes to the world state now
                    _self.saveWorldState();
                    }
                }
            else if (isBlock){
                // TODO: action will always equal 'remove-block' but we should verify
                //console.log('-> block-related action button clicked with action:', action);
                let blockSymbols = _config.mapBlockSymbols;
                let blocksIndex = _config.mapBlocksIndex;
                let blockRemovals = _world.blocks;
                let blockName = $button.attr('data-block') || false;
                let blockRobot = $button.attr('data-block-robot') || false;
                let blockAbility = $button.attr('data-block-ability') || false;
                let blockInfo = blockName && (blocksIndex && blocksIndex[blockName]) ? blocksIndex[blockName] : false;
                //console.log('-> blockName =', blockName);
                //console.log('-> blockRobot =', blockRobot);
                //console.log('-> blockAbility =', blockAbility);
                //console.log('-> blockInfo =', blockInfo);
                if (!blockName || !blockInfo){ console.error('-> block name or info not found, cannot remove block!'); return false; }
                if (!blockRobot || !blockAbility){ console.error('-> block robot and name are both required, cannot remove block!'); return false; }
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + blockRobot + '"]', $canvasMap);
                //console.log('-> $eventSprite =', $eventSprite);
                //console.log('-> $innerSprite =', $innerSprite);
                let blockKind = blockInfo.sprite ? blockInfo.sprite : false;
                let blockPosition = blockInfo.pos ? blockInfo.pos : false;
                //console.log('-> blockKind =', blockKind);
                //console.log('-> blockPosition =', blockPosition);
                let blockType = blockInfo.type ? blockInfo.type : '';
                let blockWeaknesses = blockInfo.weaknesses ? blockInfo.weaknesses : [];
                let blockEffects = blockInfo.effects ? blockInfo.effects : [];
                //console.log('-> blockType =', blockType);
                //console.log('-> blockWeaknesses =', blockWeaknesses);
                //console.log('-> blockEffects =', blockEffects);
                //let blockRobotInfo = _mmrpgRobotsIndex[blockRobot.split('_')[1]];
                //let blockAbilityInfo = _mmrpgAbilitiesIndex[blockAbility];
                //console.log('-> blockRobotInfo =', blockRobotInfo);
                //console.log('-> blockAbilityInfo =', blockAbilityInfo);
                dismissDropdown(false);
                blockInfo.removed = true;
                delete blockSymbols[blockInfo.pos];
                blocksIndex[blockName] = blockInfo; // Sync block info back to the config index
                blockRemovals[blockName] = new Date().getTime(); // Sync block claim/removal timestamp with world state
                $eventSprite.attr('data-state', 'removed');
                $eventSprite.removeClass('glow');
                //_self.playSoundEffect('icon-click');
                _self.reduceRobotWeapons(blockRobot, 1, false);
                _self.playSoundEffect('block-destroyed-sound', {delay: 200});
                $canvasMap.addClass('shake-once');
                $robotSprite.attr('data-frame', '01');
                $robotSprite.addClass('always-zoom');
                let robotNameSpan = _self.getRobotNameSpan(blockRobot);
                let abilityNameSpan = _self.getAbilityNameSpan(blockAbility);
                let blockNameSpan = _self.getCustomNameSpan(blockInfo.sprite.replace('-', ' '), blockInfo.colour);
                let wasOrWere = blockInfo.sprite.substr(-1, 1) === 's' ? 'were' : 'was';
                _self.showWorldMessage([
                    robotNameSpan + ' used ' + abilityNameSpan,
                    'The ' + blockNameSpan + ' ' + wasOrWere + ' destroyed!'
                    ]);
                $eventSprite.animate({opacity: 0, filter: 'brightness(2)'}, 600, function(){
                    $eventSprite.remove();
                    $robotSprite.removeClass('always-zoom');
                    $robotSprite.attr('data-frame', '00');
                    $canvasMap.removeClass('shake-once');
                    _self.calculateWalkableMapTiles(true);
                    _self.refreshMapPositionEvents();
                    });
                _self.saveWorldState();
                }
            else if (isHazard){
                // TODO: action will always equal 'remove-hazard' but we should verify
                //console.log('-> hazard-related action button clicked with action:', action);
                let hazardSymbols = _config.mapHazardSymbols;
                let hazardsIndex = _config.mapHazardsIndex;
                let hazardRemovals = _world.hazards;
                let hazardName = $button.attr('data-hazard') || false;
                let hazardRobot = $button.attr('data-hazard-robot') || false;
                let hazardAbility = $button.attr('data-hazard-ability') || false;
                let hazardInfo = hazardName && (hazardsIndex && hazardsIndex[hazardName]) ? hazardsIndex[hazardName] : false;
                //console.log('-> hazardName =', hazardName);
                //console.log('-> hazardRobot =', hazardRobot);
                //console.log('-> hazardAbility =', hazardAbility);
                //console.log('-> hazardInfo =', hazardInfo);
                if (!hazardName || !hazardInfo){ console.error('-> hazard name or info not found, cannot remove hazard!'); return false; }
                if (!hazardRobot || !hazardAbility){ console.error('-> hazard robot and name are both required, cannot remove hazard!'); return false; }
                let $eventSprite = $(firstEvent.sprite);
                let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + hazardRobot + '"]', $canvasMap);
                //console.log('-> $eventSprite =', $eventSprite);
                //console.log('-> $innerSprite =', $innerSprite);
                let hazardKind = hazardInfo.sprite ? hazardInfo.sprite : false;
                let hazardPosition = hazardInfo.pos ? hazardInfo.pos : false;
                //console.log('-> hazardKind =', hazardKind);
                //console.log('-> hazardPosition =', hazardPosition);
                let hazardType = hazardInfo.type ? hazardInfo.type : '';
                let hazardWeaknesses = hazardInfo.weaknesses ? hazardInfo.weaknesses : [];
                let hazardEffects = hazardInfo.effects ? hazardInfo.effects : [];
                //console.log('-> hazardType =', hazardType);
                //console.log('-> hazardWeaknesses =', hazardWeaknesses);
                //console.log('-> hazardEffects =', hazardEffects);
                dismissDropdown(false);
                hazardInfo.removed = true;
                delete hazardSymbols[hazardInfo.pos];
                hazardsIndex[hazardName] = hazardInfo; // Sync hazard info back to the config index
                hazardRemovals[hazardName] = new Date().getTime(); // Sync hazard claim/removal timestamp with world state
                $eventSprite.attr('data-state', 'removed');
                $eventSprite.removeClass('glow');
                //_self.playSoundEffect('icon-click');
                _self.reduceRobotWeapons(hazardRobot, 1, false);
                _self.playSoundEffect('hazard-destroyed-sound', {delay: 200});
                $canvasMap.addClass('shake-once');
                $robotSprite.attr('data-frame', '01');
                $robotSprite.addClass('always-zoom');
                let robotNameSpan = _self.getRobotNameSpan(hazardRobot);
                let abilityNameSpan = _self.getAbilityNameSpan(hazardAbility);
                let hazardNameSpan = _self.getCustomNameSpan(hazardInfo.sprite.replace('-', ' '), hazardInfo.colour);
                let wasOrWere = hazardInfo.sprite.substr(-1, 1) === 's' ? 'were' : 'was';
                _self.showWorldMessage([
                    robotNameSpan + ' used ' + abilityNameSpan,
                    'The ' + hazardNameSpan + ' ' + wasOrWere + ' removed!'
                    ]);
                $eventSprite.animate({opacity: 0, filter: 'brightness(2)'}, 600, function(){
                    $eventSprite.remove();
                    $robotSprite.removeClass('always-zoom');
                    $robotSprite.attr('data-frame', '00');
                    $canvasMap.removeClass('shake-once');
                    _self.calculateWalkableMapTiles(true);
                    _self.refreshMapPositionEvents();
                    });
                _self.saveWorldState();
                }
            else if (action === 'shop-with-auto'){
                //console.log('-> special action "', action, '", time to open auto\'s shop');


                }
            else if (action === 'advice-from-auto'){
                //console.log('-> special action "', action, '", time to give some advice');

                }
            else if (action === 'use-mecha-whistle'){
                //console.log('-> special action "', action, '", time to use the mecha whistle');
                let delayMessageFor = 300;
                let delayDismissFor = 1200;
                let delayEffectFor = 2100;
                let playerToken = _worldPlayer.token;
                let playerNameSpan = _self.getPlayerNameSpan(playerToken);
                let itemToken = 'mecha-whistle';
                let itemNameSpan = _self.getItemNameSpan(itemToken);
                let battleSymbols = _config.mapBattleSymbols;
                let battlesIndex = _config.mapBattlesIndex;
                let battleTarget = $button.attr('data-target') || false;
                let battleInfo = battleTarget && (battlesIndex && battlesIndex[battleTarget]) ? battlesIndex[battleTarget] : false;
                //console.log('-> playerToken =', playerToken);
                //console.log('-> itemToken =', itemToken);
                //console.log('-> battleSymbols =', battleSymbols);
                //console.log('-> battlesIndex =', battlesIndex);
                //console.log('-> battleTarget =', battleTarget);
                //console.log('-> battleInfo =', battleInfo);
                _self.playSoundEffect('get-weird-item');
                _self.showWorldMessage(playerNameSpan + ' uses a ' + itemNameSpan + '!', delayMessageFor);
                // Right now this always works, but wrap it in a function in case we wanna add conditions later
                if (true){
                    _self.showWorldMessage('...the mecha responded!', delayDismissFor);
                    _self.decrementItemQuantity('mecha-whistle');
                    setTimeout(function(){ dismissDropdown(false); }, delayDismissFor);
                    setTimeout(function(){
                        //console.log('-> redirect to auto-recruitment URL now');
                        // Shift the target into a different frame to show it heard the whistle
                        let $eventSprite = $('.sprite.battle[data-battle="' + battleTarget + '"]');
                        $eventSprite.attr('data-frame', '02').addClass('zoom');
                        // Now we redirect to the same page but w/ this battle as a auto-recruitment source
                        let recruitHref = 'world.php?recruit=' + battleTarget;
                        if (recruitHref){
                            //console.log('recruiting w/ recruitHref =', recruitHref);
                            _self.playSoundEffect('get-big-item');
                            _self.incZoomLevel();
                            $thisWorld.addClass('busy');
                            _self.saveWorldState(function(){
                                _self.incZoomLevel();
                                $thisWorld.addClass('loading');
                                window.location.href = recruitHref;
                                _self.incZoomLevel();
                                }, true, false);
                            $thisWorld.animate({opacity: 0}, 1200, function(){
                                $thisWorld.addClass('hidden');
                                });
                            }
                        }, delayEffectFor);
                    } else {
                    _self.showWorldMessage('...but nothing happened.', delayDismissFor);
                    _self.playSoundEffect('small-debuff-received');
                    }
                }
            else if (action === 'reset-world-pickups' || action === 'reset-world-encounters'){
                //console.log('-> special action "', action, '", time to reset the ', action.split('-')[2]);
                let actorSymbols = _config.mapActorSymbols;
                let actorsIndex = _config.mapActorsIndex;
                let actorStates = _world.actors;
                let actorName = $button.attr('data-actor') || false;
                let actorInfo = actorName && (actorsIndex && actorsIndex[actorName]) ? actorsIndex[actorName] : false;
                //console.log('-> actorName =', actorName);
                //console.log('-> actorInfo =', actorInfo);
                if (!actorName || !actorInfo){ console.error('-> actor name or info not found, cannot trigger actions!'); return false; }
                let actionList = typeof actorInfo.actions === 'object' && actorInfo.actions.length ? actorInfo.actions : false;
                if (!actionList){ console.error('-> actor actionlist is empty, nothing to trigger!'); return false; }
                let actionListKeys = (function(list){ let keys = []; for (let i = 0; i < list.length; i++){ keys.push(list[i][1]); } return keys; })(actionList);
                let actionInfo = actionListKeys.indexOf(action) !== -1 ? actionList[actionListKeys.indexOf(action)] : false;
                //console.log('-> actionList =', actionList);
                //console.log('-> actionListKeys =', actionListKeys);
                //console.log('-> actionInfo =', actionInfo);
                if (!actionInfo){ console.error('-> actor action info could not be found!'); return false; }
                let currency = actionInfo[3], price = actionInfo[4];
                let currencyCheck = _self.checkMenuButtonCurrency(currency, price);
                let currencyKind = currencyCheck.kind, currencySubKind = currencyCheck.subKind;
                let playerHasEnough = currencyCheck.hasEnough;
                //console.log('-> currencyCheck =', currencyCheck);
                //console.log('-> playerHasEnough =', playerHasEnough);
                if (currencyKind === 'stars'){
                    /* do nothing, stars are only ever shown */
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
                    //console.log('resetting w/ resetHref =', resetHref);
                    _self.playSoundEffect('bounce-sound');
                    _self.incZoomLevel();
                    $thisWorld.addClass('busy');
                    _self.saveWorldState(function(){
                        _self.incZoomLevel();
                        $thisWorld.addClass('loading');
                        //console.log('let\'s go!');
                        window.location.href = resetHref;
                        _self.incZoomLevel();
                        }, true, false);
                    $thisWorld.animate({opacity: 0}, 1200, function(){
                        $thisWorld.addClass('hidden');
                        });
                    }
                }
            else if (isDismiss){
                //console.log('-> dismissing action dropdown!');
                dismissDropdown(true);
                }
            else {
                // no compatible action found, do nothing
                enableDropdownButtons();
                return false;
                }
            // compatible action complete, return true
            return true;
            };

        // Define the event to run when hovering one of these new action buttons
        let hoverActionButton = function(e){
            if ($sideButtons.is('.busy')){ return; }
            _self.playSoundEffect('icon-hover');
            hoverOverviewObject.call(this, e);
            };
        let unhoverActionButton = function(e){
            unhoverOverviewObject.call(this, e);
            };

        // Bind click events to the newly created action buttons in the dropdown
        $('.button[data-action]', $sideButtons).bind('mouseenter', hoverActionButton);
        $('.button[data-action]', $sideButtons).bind('mouseleave', unhoverActionButton);
        $('.button[data-action]', $sideButtons).bind('click', onActionButtonClick);

        // Bind an event to the side-button area itself for showing/hiding action labels on hover
        $sideButtons.bind('mouseenter', function(e){ $actionDropdown.addClass('hover'); });
        $sideButtons.bind('mouseleave', function(e){ $actionDropdown.removeClass('hover'); });

        // Wait a moment for visual flow and then show the dropdown (adjusting alignment as needed)
        setTimeout(function(){
            // Add the active class so it shows and has a box model
            $actionDropdown.addClass('active');
            $sideButtons.addClass('active');
            // Collect parent and child sizes and offsets to determine alignment
            let dropdownParentSize = [_config.worldWidth, _config.worldHeight];
            let dropdownOffset = $actionDropdown.offset();
            let dropdownOffsets = [dropdownOffset.left, dropdownOffset.top];
            let dropdownPositionDelta = [];
            dropdownPositionDelta.push(1 - (dropdownParentSize[0] - dropdownOffsets[0]) / dropdownParentSize[0]);
            dropdownPositionDelta.push(1 - (dropdownParentSize[1] - dropdownOffsets[1]) / dropdownParentSize[1]);
            let dropdownAlign = dropdownPositionDelta[1] >= 0.5 ? 'above' : 'below';
            if (dropdownPositionDelta[0] <= 0.25){ dropdownAlign += '-right'; }
            if (dropdownPositionDelta[0] >= 0.75){ dropdownAlign += '-left'; }
            // Apply the alignment to the dropdown
            $actionDropdown.attr('data-align', dropdownAlign);
            // If sound effect(s) have been defined play now
            if (showActionAreaSound){
                let sound = showActionAreaSound, repeat = 1, delay = 0;
                if (sound.indexOf('*') !== -1){ var parts = sound.split('*'); sound = parts[0]; repeat = parseInt(parts[1]); }
                for (var i = 0; i < repeat; i++){
                    if (!delay){ _self.playSoundEffect(sound); delay += 50; }
                    else { setTimeout(function(){ _self.playSoundEffect(sound); }, delay); delay *= 2; }
                    }
                }
            }, (200 * timeoutMultiplier));

        };

    // Make the cursor shake so it trembles a bit before the encounter
    if (_selfRef.teamSpritesTimeout){ clearTimeout(_selfRef.teamSpritesTimeout); }
    if (readyTeamSprites){
        //console.log('%c' + 'readyTeamSpritesFunction()', 'color: cyan;');
        _selfRef.teamSpritesTimeout = setTimeout(getTeamSpritesReady, teamReadyDuration);
        }

    // If an effect is being triggered, run it and then exit here
    if (triggerEffect){
        //console.log('%c' + 'triggerEffectFunction()', 'color: cyan;');
        if (_selfRef.zoomEffectTimeout){ clearTimeout(_selfRef.zoomEffectTimeout); }
        _selfRef.zoomEffectTimeout = setTimeout(triggerEffectFunction, (zoomTimeoutDuration * timeoutMultiplier));
        if (!autoRedirect && !showActionArea){ return true; }
        }

    // If a redirect was requested, this is where we exit actually
    if (autoRedirect){
        //console.log('%c' + 'autoRedirectFunction()', 'color: cyan;');
        if (_selfRef.zoomRedirectTimeout){ clearTimeout(_selfRef.zoomRedirectTimeout); }
        _selfRef.zoomRedirectTimeout = setTimeout(redirectToLocation, (zoomTimeoutDuration * timeoutMultiplier));
        if (!showActionArea){ return true; }
        }

    // Otherwise we can actually trigger the dropdown and zoom in on the events
    if (showActionArea){
        //console.log('%c' + 'showActionAreaFunction()', 'color: cyan;');
        if (_selfRef.zoomDropdownTimeout){ clearTimeout(_selfRef.zoomDropdownTimeout); }
        _selfRef.zoomDropdownTimeout = setTimeout(zoomAndShowDropdown, (zoomTimeoutDuration * timeoutMultiplier));
        return true;
        }

    // Return true on success
    return true;
    }


// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.triggerWorldEvent = triggerWorldEvent;
mmrpgWorldMap.prototype.triggerDropZoneEvent = triggerDropZoneEvent;
mmrpgWorldMap.prototype.triggerDropZoneEmpty = triggerDropZoneEmpty;

mmrpgWorldMap.prototype.triggerWindowEventsPull = triggerWindowEventsPull;

mmrpgWorldMap.prototype.getEventsAtPosition = getEventsAtPosition;

mmrpgWorldMap.prototype.refreshPlayerPlatforms = refreshPlayerPlatforms;
mmrpgWorldMap.prototype.refreshMapPositionEvents = refreshMapPositionEvents;