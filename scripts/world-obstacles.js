
// -- WORLD OBSTACLES METHODS -- //

// Define a quick function for parsing an obstacle event (gate, lock, block, hazard) on the map and returning action dropdown options
function prepareObstacleEventOptions(firstEvent, options){
    //console.log('%c' + 'mmrpgWorldMap.prepareObstacleEventOptions()', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _indexes = _self.indexes;
    let _elements = _self.elements;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;

    options = options || {};

    let eventKind = firstEvent.kind;
    let $obstacleEvent = $(firstEvent.sprite);
    let dataLabel = $obstacleEvent.attr('data-label');

    let standingAtPosition = _worldCursor.position;
    let lookingAtPosition = _self.getRelativePositionByDirection(_worldCursor.position, _worldCursor.direction);
    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
    let standingOnHazardEvent = eventKind === 'hazard' && firstEvent.position === standingAtPosition ? true : false;

    let isSamePosition = firstEvent.position === standingAtPosition;
    let isFacingPosition = firstEvent.position === lookingAtPosition;

    let actionData = {
        showActionArea: false,
        showActionAreaAnyway: false,
        actionAreaMarkup: '',
        sideButtonsMarkup: '',
        showActionAreaType: '',
        zoomTimeoutDuration: 0,
        triggerEffect: false,
        readyTeamSprites: false,
        readyTeamSpritesAnyway: false,
        teamReadyDuration: 0,
        triggerEffectFunction: function(){},
        triggerEffectSound: '',
        readyTeamPlayerFrames: [],
        readyTeamRobotFrames: []
    };

    if (eventKind === 'gate'){
        let dataGate = $obstacleEvent.attr('data-gate');
        let dataType = $obstacleEvent.attr('data-type') || 'none';
        let gateInfo = _config.mapGatesIndex[dataGate] || false;
        let gateKind2 = firstEvent.kind2;
        let gatePrice = gateInfo.price ? gateInfo.price : 0;

        if (dataGate && gateInfo && !playerIsCursor && (isSamePosition || isFacingPosition)){
            actionData.showActionArea = true;
            if (!standingOnHazardEvent){ actionData.showActionAreaAnyway = true; }
            if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }

            actionData.sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Open The</sup> ' + (gatePrice ? '&times; ' + gatePrice + ' ' : '') + toUpperCaseWords(gateKind2.replace('-', ' ')) + ' ?</span></strong>';

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
            actionData.sideButtonsMarkup += '<a '
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
            actionData.sideButtonsMarkup += '<span class="has-sprite' + (!gatePrice ? ' one-row' : '') + '">';
            if (!gatePrice){
                actionData.sideButtonsMarkup += '<strong>' + openTheGateLabel + '</strong> ';
                actionData.sideButtonsMarkup += currencySpriteMarkup;
                } else if (playerHasEnough){
                actionData.sideButtonsMarkup += '<strong>' + openTheGateLabel + '</strong> ';
                actionData.sideButtonsMarkup += '<br /><sup class="no-strike">' + playerCountLabel + '</sup> ';
                actionData.sideButtonsMarkup += currencySpriteMarkup;
                } else {
                actionData.sideButtonsMarkup += '<strong>' + openTheGateLabel + '</strong> ';
                actionData.sideButtonsMarkup += '<br /><sup class="no-strike">' + playerCountLabel + '</sup> ';
                actionData.sideButtonsMarkup += currencySpriteMarkup;
                }
            actionData.sideButtonsMarkup += '</span></a>';
            actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            actionData.showActionAreaType = 'gate';
            actionData.zoomTimeoutDuration = 300;
            }
        }
    else if (eventKind === 'lock'){
        let dataLock = $obstacleEvent.attr('data-lock');
        let dataType = $obstacleEvent.attr('data-type') || 'none';
        let lockInfo = _config.mapLocksIndex[dataLock] || false;
        let lockKind2 = firstEvent.kind2;
        let lockCurrency = lockInfo.currency ? lockInfo.currency : '';
        let lockPrice = lockInfo.price ? lockInfo.price : 0;

        if (dataLock && lockInfo && !playerIsCursor && (isSamePosition || isFacingPosition) && lockInfo.locked){
            actionData.showActionArea = true;
            if (!standingOnHazardEvent){ actionData.showActionAreaAnyway = true; }
            if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }

            let sideButtonLabel = lockKind2 === 'portal-flower' ? 'Feed The' : 'Open The';
            actionData.sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>' + sideButtonLabel + '</sup> ' + toUpperCaseWords(lockKind2.replace('-', ' ')) + ' ?</span></strong>';

            let currencyCheck = _self.checkMenuButtonCurrency(lockCurrency, lockPrice);
            let playerHasEnough = currencyCheck.hasEnough;
            let openTheLockLabel = 'Give ' + toUpperCaseWords(currencyCheck.kind);

            actionData.sideButtonsMarkup += '<a '
                + ('class="button big-button inner-strike'
                    + (lockKind2 ? ' '+lockKind2 : '')
                    + (dataType ? ' type '+dataType : '')
                    + (!playerHasEnough ? ' disabled' : '')
                    + '"')
                + (playerHasEnough ? ' data-action="open-lock" data-lock="'+dataLock+'"' : '')
                + '>';
            actionData.sideButtonsMarkup += '<span class="has-sprite' + (!lockPrice ? ' one-row' : '') + '">';
            if (!lockPrice){
                actionData.sideButtonsMarkup += '<strong>' + openTheLockLabel + '</strong> ';
                actionData.sideButtonsMarkup += currencyCheck.spriteMarkup;
                }
            else {
                actionData.sideButtonsMarkup += '<strong>' + openTheLockLabel + '</strong> ';
                actionData.sideButtonsMarkup += '<br /><sup class="no-strike">' + currencyCheck.countLabel + '</sup> ';
                actionData.sideButtonsMarkup += currencyCheck.spriteMarkup;
                }
            actionData.sideButtonsMarkup += '</span></a>';
            actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            actionData.showActionAreaType = 'lock';
            actionData.zoomTimeoutDuration = 300;
            }
        }
    else if (eventKind === 'block'){
        let dataBlock = $obstacleEvent.attr('data-block');
        let dataType = $obstacleEvent.attr('data-type') || 'none';
        let blockInfo = _config.mapBlocksIndex[dataBlock] || false;
        let blockKind2 = firstEvent.kind2;
        let blockWeaknesses = blockInfo.weaknesses ? blockInfo.weaknesses : [];

        if (dataBlock && blockInfo && !playerIsCursor && blockWeaknesses.length){
            actionData.showActionArea = true;
            if (!standingOnHazardEvent){ actionData.showActionAreaAnyway = true; }
            if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label'+(dataType ? ' type '+dataType : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }

            actionData.sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Remove The</sup> ' + toUpperCaseWords(blockKind2.replace('-', ' ')) + ' ?</span></strong>';

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
                let playerRobotString = '';
                let robotSpriteMarkup = null;

                if (playerRobotAvailable){ robotSpriteMarkup = _self.getRobotSpriteMarkup(firstRobotWithAbilityType[0], {dir: 'left', frame: '10'}); }
                if (playerRobotAvailable && robotSpriteMarkup){
                    playerRobotString = firstRobotWithAbilityType[0];
                    let $playerRobotSprite = $(robotSpriteMarkup);
                    $playerRobotSprite.addClass('team bounce');
                    $playerRobotSprite.attr('data-sprite', 'team-robot').attr('data-robot', playerRobotString).attr('data-frame', '10');
                    robotSpriteMarkup = $playerRobotSprite[0].outerHTML;
                    let abilityInfo = abilitiesIndex[firstRobotWithAbilityType[1]];
                    buttonColour = abilityInfo.type + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '');
                    }

                actionData.sideButtonsMarkup += '<a '
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
                actionData.sideButtonsMarkup += (playerRobotAvailable
                    ? '<span class="has-sprite"><sup>Use</sup> ' + toUpperCaseWords(firstRobotWithAbilityType[1].replace('-', ' ')) + robotSpriteMarkup + '</span>'
                    : '<span>Use ' + toUpperCaseWords(weaknessType.replace('-', ' ')) + '</span>'
                    );
                actionData.sideButtonsMarkup += '</a>';
                }
            actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            actionData.showActionAreaType = 'block';
            actionData.zoomTimeoutDuration = 300;
            }
        }
    else if (eventKind === 'hazard'){
        let dataHazard = $obstacleEvent.attr('data-hazard');
        let dataColour = $obstacleEvent.attr('data-colour') || 'none';
        let hazardInfo = _config.mapHazardsIndex[dataHazard] || false;
        let hazardKind2 = firstEvent.kind2;
        let hazardWeaknesses = hazardInfo.weaknesses ? hazardInfo.weaknesses : [];
        let preventStartupHazard = !_worldCursor.moved && standingOnHazardEvent ? true : false;

        if (dataHazard && hazardInfo && !preventStartupHazard && !playerIsCursor){
            actionData.showActionArea = true;
            if (!standingOnHazardEvent){ actionData.showActionAreaAnyway = true; }
            if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label'+(dataColour ? ' type '+dataColour : '')+'"><span class="inner">' + dataLabel + '</span></strong>'; }

            actionData.sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Remove The</sup> ' + toUpperCaseWords(hazardKind2.replace('-', ' ')) + ' ?</span></strong>';

            if (hazardWeaknesses.length){
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
                        let playerRobotString = '';
                        let robotSpriteMarkup = null;

                        if (playerRobotAvailable){ robotSpriteMarkup = _self.getRobotSpriteMarkup(firstRobotWithAbilityType[0], {dir: 'left', frame: '10'}); }
                        if (playerRobotAvailable && robotSpriteMarkup){
                            playerRobotString = firstRobotWithAbilityType[0];
                            let $playerRobotSprite = $(robotSpriteMarkup);
                            $playerRobotSprite.addClass('team bounce');
                            $playerRobotSprite.attr('data-sprite', 'team-robot').attr('data-robot', playerRobotString).attr('data-frame', '10');
                            robotSpriteMarkup = $playerRobotSprite[0].outerHTML;
                            let abilityInfo = abilitiesIndex[firstRobotWithAbilityType[1]];
                            buttonColour = abilityInfo.type + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '');
                            }

                        actionData.sideButtonsMarkup += '<a '
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
                        actionData.sideButtonsMarkup += (playerRobotAvailable
                            ? '<span class="has-sprite"><sup>Use</sup> ' + toUpperCaseWords(firstRobotWithAbilityType[1].replace('-', ' ')) + robotSpriteMarkup + '</span>'
                            : '<span>Use ' + toUpperCaseWords(weaknessType.replace('-', ' ')) + '</span>'
                            );
                        actionData.sideButtonsMarkup += '</a>';
                    }
                }
            actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            actionData.showActionAreaType = 'hazard';
            actionData.zoomTimeoutDuration = 300;

            // if we're standing on the hazard, make sure we also trigger the relevant effect
            if (standingOnHazardEvent && hazardInfo.action === 'trigger-effects'){
                let hazardEffect = hazardInfo.effects;
                let hazardEffectFunction = function(){
                    if (_self.refreshMapPositionEvents.hazardTrapTimeout){ clearTimeout(_self.refreshMapPositionEvents.hazardTrapTimeout); }
                    $obstacleEvent.addClass('always-zoom');
                    _world.isBusyWith.hazardEffectFunction = true;

                    let cancelHazardTrap = function(){
                        delete _world.isBusyWith.hazardEffectFunction;
                        $obstacleEvent.removeClass('always-zoom');
                        _elements.map.removeClass('shake-once');
                        };

                    if (_worldCursor.position !== firstEvent.position){ cancelHazardTrap(); return; }

                    _self.refreshMapPositionEvents.hazardTrapTimeout = setTimeout(function(){ cancelHazardTrap(); }, 1600);

                    let effect = '', range = '', kind = '';
                    if (!hazardEffect || !hazardEffect.length){ return; }
                    let hazardEffectTokens = hazardEffect[0] ? (hazardEffect[0].split('-')) : [];
                    let hazardEffectValue = hazardEffect[1] ? parseInt(hazardEffect[1]) : 0;

                    if (hazardEffectTokens[0]){ effect = hazardEffectTokens[0]; }
                    if (hazardEffectTokens[1]){ range = hazardEffectTokens[1]; }
                    if (hazardEffectTokens[2]){ kind = hazardEffectTokens[2]; }
                    if (!effect || !range || !kind){ return; }

                    let _mapMessagesConfig = _config.mapMessages;
                    let playSound = true, playAnimation = true, showMessage = true;
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

                    for (let teamRobotKey = 0; teamRobotKey < _worldPlayer.team.length; teamRobotKey++){
                        let teamRobotString = _worldPlayer.team[teamRobotKey];
                        let effectTriggered = false;
                        _mapMessagesConfig.nextQueueStagger = 0;
                        if (typeof triggerFunction === 'function'){
                            effectTriggered = triggerFunction.call(_self, teamRobotString, hazardEffectValue, playSound, playAnimation, showMessage);
                            }
                        if (effectTriggered){ effectsTriggered++; }
                        if (range !== 'team'){ break; }
                        }

                    if (effectsTriggered){
                        _elements.map.addClass('shake-once');
                        } else {
                        cancelHazardTrap();
                        clearTimeout(_self.refreshMapPositionEvents.hazardTrapTimeout);
                        clearTimeout(_self.refreshMapPositionEvents.hazardEffectTimeout);
                        _elements.map.removeClass('shake-once');
                        }
                    };

                actionData.triggerEffect = true;
                actionData.readyTeamSprites = true;
                actionData.readyTeamSpritesAnyway = true;
                actionData.readyTeamPlayerFrames = ['05'];
                actionData.readyTeamRobotFrames = ['08'];
                actionData.teamReadyDuration = 300;
                actionData.triggerEffectFunction = function(){ hazardEffectFunction.call(_self); };
                actionData.triggerEffectSound = 'traintrack-sound';
                }
            }
        }

    return actionData;
    }

// Define a quick function for handling obstacle interactions (gates, locks, blocks, hazards) via the dropdown
function handleObstacleActionClick(action, $button, firstEvent){
    //console.log('%c' + 'mmrpgWorldMap.handleObstacleActionClick(action:' + action + ')', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _elements = _self.elements;
    let _worldPlayer = _world.player;
    let $canvasMap = _elements.map;

    _self.hideActionDropdown(false);
    let $eventSprite = $(firstEvent.sprite);

    if (action === 'open-gate'){
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
                        ? $eventSprite
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
    else if (action === 'open-lock'){
        let lockSymbols = _config.mapLockSymbols;
        let locksIndex = _config.mapLocksIndex;
        let lockStates = _world.locks;
        let lockName = $button.attr('data-lock') || false;
        let lockInfo = lockName && (locksIndex && locksIndex[lockName]) ? locksIndex[lockName] : false;

        if (!lockName || !lockInfo){ console.error('-> lock name or info not found, cannot remove lock!'); return false; }
        else if (!lockInfo.locked){ console.error('-> lock is already opened, cannot open again!'); return false; }

        let lockKind = lockInfo.sprite ? lockInfo.sprite : false;
        let lockPosition = lockInfo.pos ? lockInfo.pos : false;
        let lockCurrency = lockInfo.currency ? lockInfo.currency : '';
        let lockPrice = lockInfo.price ? lockInfo.price : 0;
        let currencyKind = lockCurrency.indexOf(':') !== -1 ? lockCurrency.split(':')[0] : lockCurrency;
        let currencySubKind = lockCurrency.indexOf(':') !== -1 ? lockCurrency.split(':')[1] : false;

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
            /* stars are not consumed */
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
    else if (action === 'remove-block'){
        let blockSymbols = _config.mapBlockSymbols;
        let blocksIndex = _config.mapBlocksIndex;
        let blockRemovals = _world.blocks;
        let blockName = $button.attr('data-block') || false;
        let blockRobot = $button.attr('data-block-robot') || false;
        let blockAbility = $button.attr('data-block-ability') || false;
        let blockInfo = blockName && (blocksIndex && blocksIndex[blockName]) ? blocksIndex[blockName] : false;

        if (!blockName || !blockInfo){ console.error('-> block name or info not found, cannot remove block!'); return false; }
        if (!blockRobot || !blockAbility){ console.error('-> block robot and name are both required, cannot remove block!'); return false; }

        let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + blockRobot + '"]', $canvasMap);

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
    else if (action === 'remove-hazard'){
        let hazardSymbols = _config.mapHazardSymbols;
        let hazardsIndex = _config.mapHazardsIndex;
        let hazardRemovals = _world.hazards;
        let hazardName = $button.attr('data-hazard') || false;
        let hazardRobot = $button.attr('data-hazard-robot') || false;
        let hazardAbility = $button.attr('data-hazard-ability') || false;
        let hazardInfo = hazardName && (hazardsIndex && hazardsIndex[hazardName]) ? hazardsIndex[hazardName] : false;

        if (!hazardName || !hazardInfo){ console.error('-> hazard name or info not found, cannot remove hazard!'); return false; }
        if (!hazardRobot || !hazardAbility){ console.error('-> hazard robot and name are both required, cannot remove hazard!'); return false; }

        let $robotSprite = $('.sprite[data-sprite="team-robot"][data-robot="' + hazardRobot + '"]', $canvasMap);

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

    return true;
    }

// Assign the sub-functions to the main class's prototype
mmrpgWorldMap.prototype.prepareObstacleEventOptions = prepareObstacleEventOptions;
mmrpgWorldMap.prototype.handleObstacleActionClick = handleObstacleActionClick;