
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
    let _mapEventSymbols = _config.mapEventSymbols;
    let _mapEventsIndex = _config.mapEventsIndex;
    let _mapItemSymbols = _config.mapItemSymbols;
    let _mapItemsIndex = _config.mapItemsIndex;
    let _mapBattlesIndex = _config.mapBattlesIndex;
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
    let $thisCanvas = _elements.canvas;
    let $canvasMap = _elements.map;
    let $worldCursor = _elements.worldCursor;
    let $teamSprites = _elements.teamSprites;
    let $actionDropdown = _elements.actionDropdown;
    let $sideButtons = _elements.sideButtons;
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

    // Define reusable functions for applying/removing the hover state to given element
    if (!hoverOverviewObject){
        hoverOverviewObject = function(e, sfx){
            let $object = $(this);
            if (_self.worldIsBusy()){ return; }
            if ($object.is('.disabled')){ return; }
            if ($object.closest('.listing').is('.disabled')){ return; }
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
        return true;
        }

    // Update the "last" variables for next time
    _selfRef.lastPosition = cursorPosition;
    _selfRef.lastDirection = cursorDirection;

    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;

    let playerActiveRobots = 0;
    for (var i = 0; i < _worldPlayerRobotsKeys.length; i++){
        let token = _worldPlayerRobotsKeys[i];
        let info = _worldPlayerRobots[token] || false;
        if (!info || info.disabled){ continue; }
        playerActiveRobots++;
        }

    let zoomTimeoutDuration = 2000 * timeoutMultiplier;
    let teamReadyDuration = 1800 * timeoutMultiplier;
    let teamRushDuration = 300 * timeoutMultiplier;
    if (teamRushDuration < 1){ teamRushDuration = 100; }

    let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap);
    if (!$spritesLayer || !$spritesLayer.length){
        console.error('updateMapPosition() missing required $spritesLayer!');
        return false;
        }

    // Make sure we empty and hide the action dropdown if it's been shown by previous move
    if (!sameAsLastPosition){
        _self.hideActionDropdown(false);
        }

    let eventsAtPosition = _self.getEventsAtPosition(cursorPosition, cursorDirection);
    if (!eventsAtPosition || !eventsAtPosition.length){
        return;
        }

    let firstEvent = eventsAtPosition[0];
    let firstEventType = firstEvent.kind;

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

    if (standingOnEvent
        && standingOnEventKey !== null
        && standingOnEventType === 'hazard'){
        delete eventsAtPosition[standingOnEventKey];
        eventsAtPosition.unshift(standingOnEvent);
        eventsAtPosition = Object.values(eventsAtPosition);
        standingOnHazardEvent = true;
        }

    let gatesNearPosition = [];
    for (let key = 0; key < eventsAtPosition.length; key++){
        let eventAtPosition = eventsAtPosition[key];
        if (eventAtPosition.kind !== 'gate'){ continue; }
        let eventBackup = _self.getClonedObject(eventsAtPosition[key]);
        eventBackup.key = key;
        gatesNearPosition.push(eventBackup);
        }

    let starsNearPosition = [];
    for (let key = 0; key < eventsAtPosition.length; key++){
        let eventAtPosition = eventsAtPosition[key];
        if (eventAtPosition.token.indexOf('-star') === -1){ continue; }
        let eventBackup = _self.getClonedObject(eventsAtPosition[key]);
        eventBackup.key = key;
        starsNearPosition.push(eventBackup);
        }

    let standingAtPosition = _worldCursor.position;
    let lookingAtPosition = _self.getRelativePositionByDirection(_worldCursor.position, _worldCursor.direction);

    firstEvent = eventsAtPosition[0];
    firstEventType = firstEvent.kind;

    if (firstEventType !== 'battle'){
        eventsAtPosition = eventsAtPosition.slice(0, 1);
        } else {
        for (let i = 1; i < eventsAtPosition.length; i++){
            if (eventsAtPosition[i].kind !== 'battle'){
                delete eventsAtPosition[i];
                }
            }
        }
    eventsAtPosition = Object.values(eventsAtPosition);
    firstEvent = eventsAtPosition[0];

    // Action Area/Effect Setup Flags
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

    // Helper function to safely merge actionData without overwriting defaults with falsy values
    let applyActionData = function(actionData){
        if (!actionData) return;
        if (typeof actionData.showActionArea !== 'undefined') showActionArea = actionData.showActionArea;
        if (actionData.actionAreaMarkup) actionAreaMarkup += actionData.actionAreaMarkup;
        if (actionData.sideButtonsMarkup) sideButtonsMarkup += actionData.sideButtonsMarkup;
        if (actionData.showActionAreaType) showActionAreaType = actionData.showActionAreaType;
        if (actionData.zoomTimeoutDuration) zoomTimeoutDuration = actionData.zoomTimeoutDuration;
        if (typeof actionData.triggerEffect !== 'undefined') triggerEffect = actionData.triggerEffect;
        if (typeof actionData.readyTeamSprites !== 'undefined') readyTeamSprites = actionData.readyTeamSprites;
        if (actionData.teamReadyDuration) teamReadyDuration = actionData.teamReadyDuration;
        if (actionData.triggerEffectFunction) triggerEffectFunction = actionData.triggerEffectFunction;
        if (typeof actionData.readyTeamSpritesAnyway !== 'undefined') readyTeamSpritesAnyway = actionData.readyTeamSpritesAnyway;
        if (actionData.readyTeamPlayerFrames) readyTeamPlayerFrames = actionData.readyTeamPlayerFrames;
        if (actionData.readyTeamRobotFrames) readyTeamRobotFrames = actionData.readyTeamRobotFrames;
        };

    // --- EVENT DATA PARSING SWITCHBOARD ---
    if (firstEventType === 'event'){
        let actionData = _self.prepareCustomEventOptions(firstEvent, { stillAtPosition: stillAtPosition, otherMenusActiveNow: otherMenusActiveNow });
        applyActionData(actionData);
        }
    else if (firstEventType === 'item'){
        let actionData = _self.prepareItemEventOptions(firstEvent, { stillAtPosition: stillAtPosition, otherMenusActiveNow: otherMenusActiveNow });
        applyActionData(actionData);
        }
    else if (firstEventType === 'ability'){
        let actionData = _self.prepareAbilityEventOptions(firstEvent, { stillAtPosition: stillAtPosition, otherMenusActiveNow: otherMenusActiveNow });
        applyActionData(actionData);
        }
    else if (firstEventType === 'portal'){
        let $portalEvent = $(firstEvent.sprite);
        let dataLabel = $portalEvent.attr('data-label');
        let dataPortal = $portalEvent.attr('data-portal');
        let portalInfo = dataPortal ? (_config.mapPortalsIndex[dataPortal] || false) : false;
        let goToDestination = false;
        let goToWorld, goToMap, goToPosition;
        let goToSameWorld, goToSameMap, goToSamePosition;

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
                if (gtm.length >= 3){ goToWorld = gtm[0]; goToMap = gtm[1]; goToPosition = gtm[2]; }
                else if (gtm.length >= 2){ goToMap = gtm[0]; goToPosition = gtm[1]; }
                }
            }
        goToSameWorld = goToWorld === _config.mapWorld ? true : false;
        goToSameMap = goToSameWorld && goToMap === _config.mapToken ? true : false;
        goToSamePosition = goToSameWorld && goToSameMap && goToPosition === _worldCursor.position ? true : false;

        if (portalInfo && goToDestination){
            showActionArea = true;
            if (!dataLabel){ dataLabel = 'Portal Options'; }
            actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
            if (dataPortal === 'exit'){ sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Return Home</span></a>'; }
            else { sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Enter Teleport</span></a>'; }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'portal';
            zoomTimeoutDuration = 500;
            if (_worldCursor.moved && !portalInfo.confirm){
                if (dataPortal === 'spawn'){
                    console.warn('-> spawn portals not yet implemented yet');
                    }
                else if (dataPortal === 'exit'){
                    autoRedirect = true;
                    showActionArea = false;
                    autoRedirectURL = 'prototype.php';
                    _world.isBusyWith.redirectToLocation = true;
                    }
                else {
                    if (goToSameWorld && goToSameMap && !goToSamePosition){
                        triggerEffect = true;
                        showActionArea = false;
                        _world.isBusyWith.teleporting = true;
                        triggerEffectFunction = function(){
                            if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                            _config.allowWorldEvents = false;
                            if (triggerEffectSound){ _self.playSoundEffect(triggerEffectSound); }
                            _self.moveToPosition(goToPosition, function(){
                                _config.allowWorldEvents = true;
                                return true;
                                });
                            };
                        triggerEffectSound = 'bounce-sound';
                        readyTeamSprites = true;
                        }
                    else {
                        autoRedirect = true;
                        showActionArea = false;
                        autoRedirectURL = 'world.php?world=' + goToWorld;
                        if (goToMap){ autoRedirectURL += '&map='+goToMap; }
                        if (goToPosition){ autoRedirectURL += '&position='+goToPosition; }
                        _world.isBusyWith.redirectToLocation = true;
                        readyTeamSprites = true;
                        readyTeamSpritesAnyway = true;
                        teamReadyDuration = 600;
                        let isExitPortal = portalInfo.direction ? true : false;
                        let isTelePortal = !portalInfo.direction ? true : false;
                        let isSubTelePortal = goToMap.indexOf('-area-0') !== -1 ? true : false;
                        autoRedirectSound = isExitPortal ? 'lets-go-robots' : 'intense-growing-sound';
                        autoRedirectEffect = isExitPortal ? 'leaving' : 'glowing';
                        if (isExitPortal){ autoRedirectAnimation = 'slide-' + portalInfo.direction; }
                        else if (isTelePortal){ autoRedirectAnimation = isSubTelePortal ? 'descend-downward' : 'ascend-upward'; }
                        }
                    }
                }
            else if (!portalInfo.confirm){ showActionArea = false; }
            }
        }
    else if (firstEventType === 'button'){
        let $buttonEvent = $(firstEvent.sprite);
        let dataLabel = $buttonEvent.attr('data-label');
        let dataButton = $buttonEvent.attr('data-button');
        let dataColour = $buttonEvent.attr('data-colour');
        let dataState = $buttonEvent.attr('data-state');
        if (dataButton && dataState === 'up'){
            showActionArea = true;
            if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
            sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="push-button" data-button="'+dataButton+'"><span><sup>Push The</sup> ' + (dataColour[0].toUpperCase() + dataColour.slice(1)) + ' Button</span></a>';
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'button';
            zoomTimeoutDuration = 500;
            }
        }
    else if (firstEventType === 'switch'){
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
            zoomTimeoutDuration = 500;
            }
        }
    else if (firstEventType === 'gate'){
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
        if (dataGate && gateInfo && !playerIsCursor && (isSamePosition || isFacingPosition)){
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Open The</sup> ' + (gatePrice ? '&times; ' + gatePrice + ' ' : '') + toUpperCaseWords(gateKind2.replace('-', ' ')) + ' ?</span></strong>';
            let gateCurrency = 'whatevers';
            if (gateKind2 === 'boss-door'){ gateCurrency = 'none'; }
            else if (gateKind2 === 'star-gate'){ gateCurrency = 'stars'; }
            let playerHasNow = 0;
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
            zoomTimeoutDuration = 300;
            }
        }
    else if (firstEventType === 'lock'){
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
        let lockCurrency = lockInfo.currency ? lockInfo.currency : '';
        let lockPrice = lockInfo.price ? lockInfo.price : 0;
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
        let $blockEvent = $(firstEvent.sprite);
        let dataLabel = $blockEvent.attr('data-label');
        let dataBlock = $blockEvent.attr('data-block');
        let dataType = $blockEvent.attr('data-type') || 'none';
        let blockInfo = _config.mapBlocksIndex[dataBlock] || false;
        let blockKind = firstEvent.kind;
        let blockKind2 = firstEvent.kind2;
        let blockWeaknesses = blockInfo.weaknesses ? blockInfo.weaknesses : [];
        if (dataBlock && blockInfo && !playerIsCursor && blockWeaknesses.length){
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + ' ?</span></strong>';
            if (blockWeaknesses.length){
                let robotsIndex = _indexes.robots;
                let abilitiesIndex = _indexes.abilities;
                let robotStringsUsed = [];
                for (let i = 0; i < blockWeaknesses.length; i++){
                        let weaknessType = blockWeaknesses[i];
                        let buttonColour = weaknessType;
                        let robotsWithAbilityType = _self.getPlayerRobotsWithAbilityType(weaknessType);
                        robotsWithAbilityType = robotsWithAbilityType ? robotsWithAbilityType.filter(function(robot){
                            let robotString = robot[0], robotData = _worldPlayerRobots[robotString];
                            if (robotStringsUsed.indexOf(robotString) !== -1){ return false; }
                            if (!robotData || !robotData.weapons){ return false; }
                            if (robotData.weapons < 1){ return false; }
                            return true;
                            }) : false;
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
                            buttonColour = abilityInfo.type + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '');
                            }
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
                }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'block';
            zoomTimeoutDuration = 300;
            }
        }
    else if (firstEventType === 'hazard'){
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
        if (dataHazard && hazardInfo && !preventStartupHazard && !playerIsCursor){
            showActionArea = true;
            if (!standingOnHazardEvent){ showActionAreaAnyway = true; }
            if (dataLabel){ actionAreaMarkup += '<strong class="label'+(dataColour ? ' type '+dataColour : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }
            sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + ' ?</span></strong>';
            if (hazardWeaknesses.length){
                let robotsIndex = _indexes.robots;
                let abilitiesIndex = _indexes.abilities;
                let robotStringsUsed = [];
                for (let i = 0; i < hazardWeaknesses.length; i++){
                        let weaknessType = hazardWeaknesses[i];
                        let buttonColour = weaknessType;
                        let robotsWithAbilityType = _self.getPlayerRobotsWithAbilityType(weaknessType);
                        robotsWithAbilityType = robotsWithAbilityType ? robotsWithAbilityType.filter(function(robot){
                            let robotString = robot[0], robotData = _worldPlayerRobots[robotString];
                            if (robotStringsUsed.indexOf(robotString) !== -1){ return false; }
                            if (!robotData || !robotData.weapons){ return false; }
                            if (robotData.weapons < 1){ return false; }
                            return true;
                            }) : false;
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
                            buttonColour = abilityInfo.type + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '');
                            }
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
                }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'hazard';
            zoomTimeoutDuration = 300;
            if (standingOnHazardEvent
                && hazardInfo.action === 'trigger-effects'){
                let hazardToken = hazardInfo.sprite;
                let hazardEffect = hazardInfo.effects;
                let hazardTriggered = 0;
                let hazardEffectFunction = function(){
                    if (_selfRef.hazardTrapTimeout){ clearTimeout(_selfRef.hazardTrapTimeout); }
                    $hazardEvent.addClass('always-zoom');
                    _world.isBusyWith.hazardEffectFunction = true;
                    let cancelHazardTrap = function(){
                        delete _world.isBusyWith.hazardEffectFunction;
                        $hazardEvent.removeClass('always-zoom');
                        $canvasMap.removeClass('shake-once');
                        };
                    if (!stillAtPosition()){ cancelHazardTrap(); return; }
                    _selfRef.hazardTrapTimeout = setTimeout(function(){ cancelHazardTrap(); }, 1600);
                    let effect = '', range = '', kind = '';
                    if (!hazardEffect || !hazardEffect.length){ return; }
                    let hazardEffectTokens = hazardEffect[0] ? (hazardEffect[0].split('-')) : [];
                    let hazardEffectValue = hazardEffect[1] ? parseInt(hazardEffect[1]) : 0;
                    if (hazardEffectTokens[0]){ effect = hazardEffectTokens[0]; }
                    if (hazardEffectTokens[1]){ range = hazardEffectTokens[1]; }
                    if (hazardEffectTokens[2]){ kind = hazardEffectTokens[2]; }
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
                        _mapMessagesConfig.nextQueueStagger = 0;
                        if (typeof triggerFunction === 'function'){
                            effectTriggered = triggerFunction.call(_self, teamRobotString, hazardEffectValue, playSound, playAnimation, showMessage);
                            }
                        if (effectTriggered){ effectsTriggered++; }
                        if (range !== 'team'){ break; }
                        }
                    hazardTriggered++;
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
        let actionData = _self.prepareBattleEventOptions(firstEvent, eventsAtPosition, starsNearPosition, { stillAtPosition: stillAtPosition, otherMenusActiveNow: otherMenusActiveNow });
        applyActionData(actionData);
        }
    else if (firstEventType === 'actor'){
        let eventInfo = firstEvent;
        let actorInfo = _mapActorsIndex[eventInfo.token];
        let actorSymbol = _mapActorSymbols[eventInfo.position];
        let $actorEvent = $(eventInfo.sprite);
        let dataActor = $actorEvent.attr('data-actor');
        let dataLabel = $actorEvent.attr('data-label');
        let dataColour = $actorEvent.attr('data-colour');
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
            if (actorInfo.actions && actorInfo.actions.length){
                for (let i = 0; i < actorInfo.actions.length; i++){
                    let action = actorInfo.actions[i];
                    let label = typeof action[0] !== 'undefined' && action[0] ? action[0] : false;
                    let token = typeof action[1] !== 'undefined' && action[1] ? action[1] : false;
                    let colour = typeof action[2] !== 'undefined' && action[2] ? action[2] : false;
                    if (!label || !token){ continue; }
                    if (token === 'reset-world-pickups'){
                        let allItemSymbols = Object.values(_config.mapItemSymbols).join(',');
                        let hasRandomItems = allItemSymbols.indexOf('random-') !== -1;
                        if (!hasRandomItems){ continue; }
                        }
                    else if (token === 'reset-world-encounters'){
                        let allBattleSymbols = Object.values(_config.mapBattleSymbols).join(',');
                        let hasRandomBattles = allBattleSymbols.indexOf('random-') !== -1;
                        if (!hasRandomBattles){ continue; }
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
                    if (enabled){ sideButtonsMarkup += '<a class="button big-button'+(colour ? ' type '+colour : '')+'" data-actor="'+dataActor+'" data-action="'+token+'">' + innerHtml + '</a>'; }
                    else { sideButtonsMarkup += '<a class="button big-button'+(colour ? ' type '+colour : '')+' disabled">' + innerHtml + '</a>'; }
                    }
                }
            sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            showActionAreaType = 'actor';
            zoomTimeoutDuration = 500;
            }
        }

    // --- EVENT TRIGGERS AND DROPDOWNS ---

    // If there's no dropdown to show, we can return early
    if (!showActionArea && !autoRedirect && !triggerEffect){ return; }

    // Make the cursor shake so it trembles a bit before the encounter using our new base method
    if (_selfRef.teamSpritesTimeout){ clearTimeout(_selfRef.teamSpritesTimeout); }
    if (readyTeamSprites){
        _selfRef.teamSpritesTimeout = setTimeout(function(){
            _self.readyTeamSprites({
                firstEvent: firstEvent,
                readyTeamSpritesAnyway: readyTeamSpritesAnyway,
                autoRedirectAnimation: autoRedirectAnimation,
                autoRedirectEffect: autoRedirectEffect,
                readyTeamPlayerFrames: readyTeamPlayerFrames,
                readyTeamRobotFrames: readyTeamRobotFrames,
                teamRushDuration: teamRushDuration,
                cursorPosition: cursorPosition
            });
        }, teamReadyDuration);
    }

    // If an effect is being triggered, run it and then exit here
    if (triggerEffect){
        if (_selfRef.zoomEffectTimeout){ clearTimeout(_selfRef.zoomEffectTimeout); }
        _selfRef.zoomEffectTimeout = setTimeout(triggerEffectFunction, (zoomTimeoutDuration * timeoutMultiplier));
        if (!autoRedirect && !showActionArea){ return true; }
        }

    // If a redirect was requested, trigger the base redirect method
    if (autoRedirect){
        if (_selfRef.zoomRedirectTimeout){ clearTimeout(_selfRef.zoomRedirectTimeout); }
        _selfRef.zoomRedirectTimeout = setTimeout(function(){
            _self.triggerAutoRedirect({
                autoRedirectURL: autoRedirectURL,
                autoRedirectSound: autoRedirectSound,
                stillAtPosition: stillAtPosition,
                otherMenusActiveNow: otherMenusActiveNow
            });
        }, (zoomTimeoutDuration * timeoutMultiplier));
        if (!showActionArea){ return true; }
        }

    // Otherwise we can actually trigger the dropdown and zoom in on the events
    if (showActionArea){
        if (_selfRef.zoomDropdownTimeout){ clearTimeout(_selfRef.zoomDropdownTimeout); }
        _selfRef.zoomDropdownTimeout = setTimeout(function(){
            if (_self.worldIsBusy() && !showActionAreaAnyway){ return; }
            if (!stillAtPosition() || otherMenusActiveNow()){ return; }

            // Elevate the event sprite(s) to the zoom layer and add a zoom class to it so it's more visible
            let cursorPositionXY = cursorPosition.split('-');
            for (var i = 0; i < eventsAtPosition.length; i++){
                let eventData = eventsAtPosition[i];
                let $eventSprite = $(eventData.sprite);
                let eventPosition = $eventSprite.attr('data-pos');
                if ($eventSprite.hasClass('tile')){ continue; }
                let isRobot = $eventSprite.hasClass('robot');
                let isMecha = $eventSprite.hasClass('vs-mecha');
                let isMaster = $eventSprite.hasClass('vs-master');
                let isBoss = $eventSprite.hasClass('vs-boss');
                let isRescue = $eventSprite.hasClass('vs-rescue');
                let isActor = $eventSprite.hasClass('vs-actor');
                let dataSize = $eventSprite.attr('data-size') || 40;
                let $eventLayer = $eventSprite.closest('.layer');
                let eventLayer = $eventLayer.attr('data-layer');
                let relativePosition = _self.getPositionRelative(cursorPosition, eventPosition);
                let newDirection = false;
                if (relativePosition[0] < 0){ newDirection = 'right'; }
                else if (relativePosition[0] > 0){ newDirection = 'left'; }
                else if (cursorDirection.indexOf('right') !== -1){ newDirection = 'left'; }
                else if (cursorDirection.indexOf('left') !== -1){ newDirection = 'right'; }
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

            // Call the base method to construct and display the dropdown menu
            _self.showActionDropdown({
                actionAreaMarkup: actionAreaMarkup,
                sideButtonsMarkup: sideButtonsMarkup,
                showActionAreaType: showActionAreaType,
                showActionAreaSound: showActionAreaSound,
                cursorPosition: cursorPosition,
                cursorDirection: cursorDirection
            });

            // Define the event to run when clicking one of these new action buttons
            let onActionButtonClick = function(e){
                e.preventDefault();
                let $button = $(this);
                let action = $button.attr('data-action') || false;
                if ($button.is('.disabled') || $button.is('[disabled]')){ return false; }
                if (!action){ console.error('-> no action found on button, skipping!'); return false; }
                let isCustomEvent = action === 'trigger-event';
                let isBattle = action.indexOf('-battle') !== -1 || action.indexOf('battle-') !== -1 || action === 'use-mecha-whistle';
                let isPortal = action.indexOf('-portal') !== -1 || action.indexOf('portal-') !== -1;
                let isButton = action.indexOf('-button') !== -1 || action.indexOf('button-') !== -1;
                let isSwitch = action.indexOf('-switch') !== -1 || action.indexOf('switch-') !== -1;
                let isGate = action.indexOf('-gate') !== -1 || action.indexOf('gate-') !== -1;
                let isLock = action.indexOf('-lock') !== -1 || action.indexOf('lock-') !== -1;
                let isItem = action.indexOf('-item') !== -1 || action.indexOf('item-') !== -1;
                let isAbility = action.indexOf('-ability') !== -1 || action.indexOf('ability-') !== -1;
                let isBlock = action.indexOf('-block') !== -1 || action.indexOf('block-') !== -1;
                let isHazard = action.indexOf('-hazard') !== -1 || action.indexOf('hazard-') !== -1;
                let isShop = action.indexOf('-shop') !== -1 || action.indexOf('shop-') !== -1;
                let isAdvice = action.indexOf('-advice') !== -1 || action.indexOf('advice-') !== -1;
                let isDismiss = action === 'dismiss';

                if (!isDismiss){ $button.addClass('clicked'); }
                if ($sideButtons.is('.busy') && !isDismiss){ return false; }

                _self.disableActionDropdown();

                if (isCustomEvent){
                    return _self.handleCustomEventActionClick(action, $button, firstEvent);
                    }
                if (isBattle){
                    return _self.handleBattleActionClick(action, $button, firstEvent);
                    }
                else if (isPortal){
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
                        let finalGoToMap = false;
                        if (portalName === 'spawn'){
                            portalHref = 'prototype.php';
                            } else if (portalName === 'exit'){
                            portalHref = 'prototype.php';
                            } else if (portalName.indexOf('goto__') !== -1){
                            let worldToken, mapToken;
                            let goToPath = portalName.replace(/^goto__/i, '').split('__');
                            if (goToPath[1]){ worldToken = goToPath[0]; mapToken = goToPath[1]; }
                            else { worldToken = _config.mapWorld; mapToken = goToPath[0]; }
                            finalGoToMap = mapToken;
                            portalHref = 'world.php?world=' + worldToken + '&map=' + mapToken;
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
                        if (isSameMapTeleport && goToPosition){
                            _self.hideActionDropdown(false);
                            _self.playSoundEffect('bounce-sound');
                            _config.allowWorldEvents = false;
                            _self.moveToPosition(goToPosition, function(){
                                _config.allowWorldEvents = true;
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
                    }
                else if (isButton){
                    let buttonsIndex = _config.mapButtonsIndex;
                    let buttonStates = _world.buttons;
                    let buttonName = $button.attr('data-button') || false;
                    let buttonInfo = buttonName && (buttonsIndex && buttonsIndex[buttonName]) ? buttonsIndex[buttonName] : false;
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('> .sprite', $eventSprite) : false;
                    if (!buttonName || !buttonInfo){ console.error('-> button name or info not found, cannot push button!'); return false; }
                    if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot push button!'); return false; }
                    if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot push button!'); return false; }
                    _self.hideActionDropdown(false);
                    buttonInfo.state = 'down';
                    buttonsIndex[buttonName] = buttonInfo;
                    buttonStates[buttonName] = 'down';
                    $eventSprite.attr('data-state', 'down');
                    $innerSprite.removeClass('up').addClass('down');
                    $eventSprite.removeClass('glow');
                    _self.playSoundEffect('button-click');
                    _self.playSoundEffect('hyper-stomp-sound', {delay: 200});
                    $canvasMap.addClass('shake-once');
                    setTimeout(function(){ $canvasMap.removeClass('shake-once'); }, 1000);
                    (function(buttonInfo){
                        if (!buttonInfo.action){ return false; }
                        let buttonAction = buttonInfo.action;
                        let buttonData = buttonInfo.data || {};
                        if (buttonAction === 'set-group-terrain'){
                            let groupName = buttonData[0] || false;
                            let terrainName = buttonData[1] || false;
                            if (!groupName){ console.error('-> groupName not provided, cannot set group terrain!'); return false; }
                            if (!terrainName){ console.error('-> terrainName not provided, cannot set group terrain!'); return false; }
                            let terrainTilesIndex = _world.layerTilesIndex['terrain_0'] || false;
                            if (!terrainTilesIndex){ console.error('-> terrain_0 not found in _world.layerTilesIndex, cannot set terrain!'); return false; }
                            let groupsIndex = _config.mapGroupsIndex;
                            let groupTiles = groupsIndex[groupName] || false;
                            if (!groupsIndex || !groupTiles){ console.error('-> groupsIndex not found, cannot set terrain!'); return false; }
                            for (let i = 0; i < groupTiles.length; i++){
                                let tileKey = groupTiles[i];
                                let tileData = terrainTilesIndex[tileKey];
                                if (!tileData){
                                    console.error('-> tile data not found for tile', tileKey, ', cannot set terrain!');
                                    continue;
                                    }
                                let targetTerrainBase = terrainName.replace(/-[0-9]+$/, '');
                                tileData.sprite[1] = targetTerrainBase;
                                }
                            _self.refreshTerrainEdges(groupTiles);
                            _self.calculateWalkableMapTiles(true);
                            }
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
                                blocksIndex[targetBlockName] = blockInfo;
                                blockRemovals[targetBlockName] = new Date().getTime();
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
                    let switchesIndex = _config.mapSwitchesIndex;
                    let switchStates = _world.switches;
                    let switchName = $button.attr('data-switch') || false;
                    let switchInfo = switchName && (switchesIndex && switchesIndex[switchName]) ? switchesIndex[switchName] : false;
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('> .sprite', $eventSprite) : false;
                    if (!switchName || !switchInfo){ console.error('-> switch name or info not found, cannot toggle switch!'); return false; }
                    if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot toggle switch!'); return false; }
                    if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot toggle switch!'); return false; }
                    _self.hideActionDropdown(false);
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
                        if (switchAction === 'toggle-group-terrain'){
                            let groupName = switchData[0] || false;
                            let terrainUp = switchData[1] || false;
                            let terrainDown = switchData[2] || false;
                            if (!groupName){ console.error('-> groupName not provided, cannot set group terrain!'); return false; }
                            if (!terrainUp || !terrainDown){ console.error('-> terrainUp or terrainDown not provided, cannot toggle group terrain!'); return false; }
                            let terrainTilesIndex = _world.layerTilesIndex['terrain_0'] || false;
                            if (!terrainTilesIndex){ console.error('-> terrain_0 not found in _world.layerTilesIndex, cannot toggle group terrain!'); return false; }
                            let groupsIndex = _config.mapGroupsIndex;
                            let groupTiles = groupsIndex[groupName] || false;
                            if (!groupTiles){ console.error('-> groupsIndex not found for groupName "' + groupName + '"!'); return false; }
                            for (let i = 0; i < groupTiles.length; i++){
                                let tileKey = groupTiles[i];
                                let tileData = terrainTilesIndex[tileKey];
                                if (!tileData) continue;
                                let currentTerrain = tileData.sprite[1];
                                let currentTerrainBase = currentTerrain.replace(/-[0-9]+$/, '');
                                let targetTerrainBase = currentTerrainBase;
                                if (currentTerrain === terrainUp || currentTerrainBase === terrainUp){ targetTerrainBase = terrainDown; }
                                else if (currentTerrain === terrainDown || currentTerrainBase === terrainDown){ targetTerrainBase = terrainUp; }
                                tileData.sprite[1] = targetTerrainBase;
                                }
                            _self.refreshTerrainEdges(groupTiles);
                            _self.calculateWalkableMapTiles(true);
                            }
                        })(switchInfo, newState);
                    }
                else if (isGate){
                    let gateSymbols = _config.mapGateSymbols;
                    let gatesIndex = _config.mapGatesIndex;
                    let gateRemovals = _world.gates;
                    let baseGateName = $button.attr('data-gate') || false;
                    let baseGateInfo = baseGateName && (gatesIndex && gatesIndex[baseGateName]) ? gatesIndex[baseGateName] : false;
                    if (!baseGateName || !baseGateInfo){ console.error('-> gate name or info not found, cannot remove gate!'); return false; }
                    let gatesToRemove = [];
                    let visitedPositions = new Set();
                    let positionsToProcess = [baseGateInfo.pos];
                    visitedPositions.add(baseGateInfo.pos);
                    let targetGateSprite = baseGateInfo.sprite;
                    while (positionsToProcess.length > 0){
                        let currentPos = positionsToProcess.shift();
                        let currentGateName = gateSymbols[currentPos] || false;
                        if (currentGateName){
                            let currentGateInfo = gatesIndex[currentGateName] || false;
                            if (currentGateInfo && currentGateInfo.sprite === targetGateSprite) {
                                let $currentSprite = (currentGateName === baseGateName)
                                    ? $(firstEvent.sprite)
                                    : $('.sprite[data-gate="' + currentGateName + '"]', $canvasMap);
                                gatesToRemove.push({
                                    name: currentGateName,
                                    info: currentGateInfo,
                                    $sprite: $currentSprite.length ? $currentSprite : null,
                                    position: currentPos
                                    });
                                let coords = currentPos.split('-');
                                let col = parseInt(coords[0]);
                                let row = parseInt(coords[1]);
                                let neighbors = [
                                    col + '-' + (row - 1),
                                    col + '-' + (row + 1),
                                    (col - 1) + '-' + row,
                                    (col + 1) + '-' + row
                                    ];
                                for (let i = 0; i < neighbors.length; i++){
                                    if (!visitedPositions.has(neighbors[i])){
                                        visitedPositions.add(neighbors[i]);
                                        positionsToProcess.push(neighbors[i]);
                                        }
                                    }
                                }
                            }
                        }
                    _self.hideActionDropdown(false);
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
                    let lockSymbols = _config.mapLockSymbols;
                    let locksIndex = _config.mapLocksIndex;
                    let lockStates = _world.locks;
                    let lockName = $button.attr('data-lock') || false;
                    let lockInfo = lockName && (locksIndex && locksIndex[lockName]) ? locksIndex[lockName] : false;
                    if (!lockName || !lockInfo){ console.error('-> lock name or info not found, cannot remove lock!'); return false; }
                    else if (!lockInfo.locked){ console.error('-> lock is already opened, cannot open again!'); return false; }
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                    let lockKind = lockInfo.sprite ? lockInfo.sprite : false;
                    let lockKind2 = lockInfo.image ? lockInfo.image : false;
                    let lockPosition = lockInfo.pos ? lockInfo.pos : false;
                    let lockType = lockInfo.type ? lockInfo.type : '';
                    let lockCurrency = lockInfo.currency ? lockInfo.currency : '';
                    let lockPrice = lockInfo.price ? lockInfo.price : 0;
                    let currencyKind = lockCurrency.indexOf(':') !== -1 ? lockCurrency.split(':')[0] : lockCurrency;
                    let currencySubKind = lockCurrency.indexOf(':') !== -1 ? lockCurrency.split(':')[1] : false;
                    _self.hideActionDropdown(false);
                    lockInfo.locked = false;
                    locksIndex[lockName] = lockInfo;
                    lockStates[lockName] = new Date().getTime();
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
                    if (currencyKind === 'stars'){
                        }
                    else if (currencyKind === 'zenny'){
                        _worldPlayer.zenny -= lockPrice;
                        if (_worldPlayer.zenny < 0){ _worldPlayer.zenny = 0; }
                        }
                    else if (currencyKind === 'items'){
                        if (currencySubKind.length && typeof _worldPlayer.items[currencySubKind] !== 'undefined'){
                            _worldPlayer.items[currencySubKind] -= lockPrice;
                            if (_worldPlayer.items[currencySubKind] < 0){ _worldPlayer.items[currencySubKind] = 0; }
                            }
                        }
                    if (typeof _config.mapPortalSymbols[lockPosition] !== 'undefined'){
                        let portalSymbols = _config.mapPortalSymbols;
                        let portalsIndex = _config.mapPortalsIndex;
                        let portalName = portalSymbols[lockPosition];
                        let portalInfo = portalName ? portalsIndex[portalName] : false;
                        let $portalEvent = portalName ? $('.sprite[data-portal="' + portalName + '"]', $canvasMap) : false;
                        if (portalInfo && portalInfo.locked){ portalInfo.locked = false; portalsIndex[portalName] = portalInfo; }
                        if ($portalEvent && $portalEvent.length && $portalEvent.is('.locked')){ $portalEvent.removeClass('locked'); }
                        _self.calculateWalkableMapTiles(true);
                        }
                    _self.saveWorldState();
                    }
                else if (isItem){
                    return _self.handleItemActionClick(action, $button, firstEvent);
                    }
                else if (isAbility){
                    return _self.handleAbilityActionClick(action, $button, firstEvent);
                    }
                else if (isBlock){
                    let blockSymbols = _config.mapBlockSymbols;
                    let blocksIndex = _config.mapBlocksIndex;
                    let blockRemovals = _world.blocks;
                    let blockName = $button.attr('data-block') || false;
                    let blockRobot = $button.attr('data-block-robot') || false;
                    let blockAbility = $button.attr('data-block-ability') || false;
                    let blockInfo = blockName && (blocksIndex && blocksIndex[blockName]) ? blocksIndex[blockName] : false;
                    if (!blockName || !blockInfo){ console.error('-> block name or info not found, cannot remove block!'); return false; }
                    if (!blockRobot || !blockAbility){ console.error('-> block robot and name are both required, cannot remove block!'); return false; }
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                    let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + blockRobot + '"]', $canvasMap);
                    let blockKind = blockInfo.sprite ? blockInfo.sprite : false;
                    let blockPosition = blockInfo.pos ? blockInfo.pos : false;
                    let blockType = blockInfo.type ? blockInfo.type : '';
                    let blockWeaknesses = blockInfo.weaknesses ? blockInfo.weaknesses : [];
                    let blockEffects = blockInfo.effects ? blockInfo.effects : [];
                    _self.hideActionDropdown(false);
                    blockInfo.removed = true;
                    delete blockSymbols[blockInfo.pos];
                    blocksIndex[blockName] = blockInfo;
                    blockRemovals[blockName] = new Date().getTime();
                    $eventSprite.attr('data-state', 'removed');
                    $eventSprite.removeClass('glow');
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
                    let hazardSymbols = _config.mapHazardSymbols;
                    let hazardsIndex = _config.mapHazardsIndex;
                    let hazardRemovals = _world.hazards;
                    let hazardName = $button.attr('data-hazard') || false;
                    let hazardRobot = $button.attr('data-hazard-robot') || false;
                    let hazardAbility = $button.attr('data-hazard-ability') || false;
                    let hazardInfo = hazardName && (hazardsIndex && hazardsIndex[hazardName]) ? hazardsIndex[hazardName] : false;
                    if (!hazardName || !hazardInfo){ console.error('-> hazard name or info not found, cannot remove hazard!'); return false; }
                    if (!hazardRobot || !hazardAbility){ console.error('-> hazard robot and name are both required, cannot remove hazard!'); return false; }
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                    let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + hazardRobot + '"]', $canvasMap);
                    let hazardKind = hazardInfo.sprite ? hazardInfo.sprite : false;
                    let hazardPosition = hazardInfo.pos ? hazardInfo.pos : false;
                    let hazardType = hazardInfo.type ? hazardInfo.type : '';
                    let hazardWeaknesses = hazardInfo.weaknesses ? hazardInfo.weaknesses : [];
                    let hazardEffects = hazardInfo.effects ? hazardInfo.effects : [];
                    _self.hideActionDropdown(false);
                    hazardInfo.removed = true;
                    delete hazardSymbols[hazardInfo.pos];
                    hazardsIndex[hazardName] = hazardInfo;
                    hazardRemovals[hazardName] = new Date().getTime();
                    $eventSprite.attr('data-state', 'removed');
                    $eventSprite.removeClass('glow');
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
                else if (isShop){
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
                            let itemInfo = _mmrpgItemsIndex[itemToken];
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

                    let frameSource = '';
                    frameSource = '/frames/shop.php?world=true&shop=' + shopToken + '&player=' + _playerToken;
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
                    if (actorNameKey && typeof _mapActorsIndex[actorNameKey] !== 'undefined'){ actorInfo = _mapActorsIndex[actorNameKey]; }
                    let possibleMessages = [];
                    if (actorInfo
                        && typeof actorInfo['advice'] !== 'undefined'
                        && actorInfo['advice'].length > 0){
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
                    $sideButtons.find('.text').html(selectedMessageText);
                    _self.enableActionDropdown();
                    $sideButtons.find('.clicked').removeClass('clicked').addClass('hovered maybe');
                    }
                else if (action === 'use-mecha-whistle'){
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
                    let isRescueMecha = $button.attr('data-is-rescue') === 'true';
                    _self.playSoundEffect('get-weird-item');
                    if (isRescueMecha){ _self.showWorldMessage(playerNameSpan + ' calls out to the friendly mecha!', delayMessageFor); }
                    else { _self.showWorldMessage(playerNameSpan + ' uses a ' + itemNameSpan + '!', delayMessageFor); }
                    if (true){
                        _self.showWorldMessage('...the mecha responded!', delayDismissFor);
                        if (!isRescueMecha){ _self.decrementItemQuantity('mecha-whistle'); }
                        setTimeout(function(){ _self.hideActionDropdown(false); }, delayDismissFor);
                        setTimeout(function(){
                            let $eventSprite = $('.sprite.battle[data-battle="' + battleTarget + '"]');
                            $eventSprite.attr('data-frame', '02').addClass('zoom');
                            let recruitHref = 'world.php?recruit=' + battleTarget;
                            if (recruitHref){
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
                    let actorSymbols = _config.mapActorSymbols;
                    let actorsIndex = _config.mapActorsIndex;
                    let actorStates = _world.actors;
                    let actorName = $button.attr('data-actor') || false;
                    let actorInfo = actorName && (actorsIndex && actorsIndex[actorName]) ? actorsIndex[actorName] : false;
                    if (!actorName || !actorInfo){ console.error('-> actor name or info not found, cannot trigger actions!'); return false; }
                    let actionList = typeof actorInfo.actions === 'object' && actorInfo.actions.length ? actorInfo.actions : false;
                    if (!actionList){ console.error('-> actor actionlist is empty, nothing to trigger!'); return false; }
                    let actionListKeys = (function(list){ let keys = []; for (let i = 0; i < list.length; i++){ keys.push(list[i][1]); } return keys; })(actionList);
                    let actionInfo = actionListKeys.indexOf(action) !== -1 ? actionList[actionListKeys.indexOf(action)] : false;
                    if (!actionInfo){ console.error('-> actor action info could not be found!'); return false; }
                    let currency = actionInfo[3], price = actionInfo[4];
                    let currencyCheck = _self.checkMenuButtonCurrency(currency, price);
                    let currencyKind = currencyCheck.kind, currencySubKind = currencyCheck.subKind;
                    let playerHasEnough = currencyCheck.hasEnough;
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
                else if (isDismiss){
                    _self.hideActionDropdown(true);
                    }
                else {
                    _self.enableActionDropdown();
                    return false;
                    }
                return true;
                };

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

            }, (200 * timeoutMultiplier));
        return true;
        }

    return true;
    }

// Define a quick function for parsing a custom event on the map and returning action dropdown options
function prepareCustomEventOptions(firstEvent, options){
    //console.log('%c' + 'mmrpgWorldMap.prepareCustomEventOptions()', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _worldCursor = _world.cursor;

    options = options || {};
    let stillAtPosition = typeof options.stillAtPosition === 'function' ? options.stillAtPosition : function(){ return true; };
    let otherMenusActiveNow = typeof options.otherMenusActiveNow === 'function' ? options.otherMenusActiveNow : function(){ return false; };

    let $customEvent = $(firstEvent.sprite);
    let dataLabel = $customEvent.attr('data-label');
    let dataEvent = $customEvent.attr('data-event');
    let eventInfo = _config.mapEventsIndex[dataEvent] || false;

    let actionData = {
        showActionArea: false,
        actionAreaMarkup: '',
        sideButtonsMarkup: '',
        showActionAreaType: '',
        triggerEffect: false,
        readyTeamSprites: false,
        triggerEffectFunction: function(){}
    };

    if (eventInfo && _worldCursor.moved){
        //console.log('-> found eventInfo for ' + dataEvent + ':', eventInfo);
        let eventFilter = eventInfo['filter'] || false;
        let eventAction = eventInfo['action'] || false;
        let eventData = eventInfo['data'] || false;
        let eventAllowed = eventFilter === 'any' ? true : false; // TODO: implement player and/or robot-specific filter logic

        if (eventAllowed){
            if (eventAction !== false){
                //console.log('-> eventAction is "', eventAction, '" so defer it to triggerEffectFunction()');
                actionData.triggerEffect = true;
                actionData.readyTeamSprites = true;
                actionData.readyTeamSpritesAnyway = true; // Ensures the poses trigger even while the map is "busy" executing the effect
                actionData.zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                // We purposefully leave teamReadyDuration undefined so it inherits the parent default

                actionData.triggerEffectFunction = function(){
                    if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                    //console.log('-> running triggerEffectFunction for eventAction "' + eventAction + '" with eventData:', eventData);
                    _self.triggerWorldEvent(eventAction, eventData, $customEvent);
                    };
                } else {
                //console.log('-> eventAction is false, so prepare dropdown instead');
                actionData.showActionArea = true;
                if (!dataLabel){ dataLabel = 'Event Options'; }
                actionData.actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
                actionData.sideButtonsMarkup += '<a class="button big-button" data-action="trigger-event" data-event="'+dataEvent+'"><span><sup>Ready To</sup> Trigger Event</span></a>';
                actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                actionData.showActionAreaType = 'event';
                }
            } else {
            //console.log('-> event not allowed based on filter "' + eventFilter + '"');
            }
        } else if (eventInfo && !_worldCursor.moved) {
        //console.log('-> eventInfo found for ' + dataEvent + ', but cursor not moved yet, skipping');
        } else {
        //console.log('-> no eventInfo found for ' + dataEvent + ', skipping');
        }

    return actionData;
    }

// Define a quick function for handling manual custom event interactions via the dropdown
function handleCustomEventActionClick(action, $button, firstEvent){
    //console.log('%c' + 'mmrpgWorldMap.handleCustomEventActionClick(action:' + action + ')', 'color: magenta;');
    let _self = this;
    let _config = _self.config;

    let dataEvent = $button.attr('data-event') || false;
    let eventInfo = dataEvent ? _config.mapEventsIndex[dataEvent] : false;
    let $customEvent = $(firstEvent.sprite);

    if (!dataEvent || !eventInfo){ console.error('-> custom event name or info not found!'); return false; }
    _self.hideActionDropdown(false);

    if (action === 'trigger-event'){
        let eventAction = eventInfo['action'] || false;
        let eventData = eventInfo['data'] || false;
        if (eventAction !== false){
            _self.triggerWorldEvent(eventAction, eventData, $customEvent);
            } else {
            console.warn('-> trigger-event clicked, but eventAction is false. Nothing to trigger!');
            }
        }

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

mmrpgWorldMap.prototype.prepareCustomEventOptions = prepareCustomEventOptions;
mmrpgWorldMap.prototype.handleCustomEventActionClick = handleCustomEventActionClick;
