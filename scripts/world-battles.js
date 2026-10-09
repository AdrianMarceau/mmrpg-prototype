// -- WORLD BATTLE METHODS -- //

// Define a quick function for parsing a battle event on the map and returning action dropdown options
function prepareBattleEventOptions(firstEvent, eventsAtPosition, starsNearPosition, options){
    //console.log('%c' + 'mmrpgWorldMap.prepareBattleEventOptions()', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldCursor = _world.cursor;
    let _worldToken = _config.mapWorld;
    let _mapToken = _config.mapToken;
    let _mapBattlesIndex = _config.mapBattlesIndex;
    let _mmrpgRobotsIndex = _self.indexes.robots;

    options = options || {};

    let actionData = {
        showActionArea: false,
        actionAreaMarkup: '',
        sideButtonsMarkup: '',
        showActionAreaType: '',
        showActionAreaSound: '',
        zoomTimeoutDuration: 0,
        readyTeamSprites: false
    };

    let firstBattleEvent = eventsAtPosition[0];
    let firstBattleInfo = _mapBattlesIndex[firstBattleEvent.token] || null;
    let firstBattleRobot = _mmrpgRobotsIndex[firstBattleInfo.token] || null;

    let dataLabels = [], dataBattles = [];
    for (var i = 0; i < eventsAtPosition.length; i++){
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
    if (starsNearPosition && starsNearPosition.length){
        for (var i = 0; i < starsNearPosition.length; i++){
            let starEvent = starsNearPosition[i];
            let dataPosition = starEvent.position;
            var dataStar = 'world-star_' + _worldToken + '_' + _mapToken + '_' + starEvent.token;
            if (dataStar){
                dataBattleStars.push([dataStar, dataPosition]);
                }
            }
        }

    let standingAtPosition = (_worldCursor.position.split('-')).map(function(num){ return parseInt(num); });
    let lookingAtPosition = [standingAtPosition[0], standingAtPosition[1]];
    if (_worldCursor.direction.indexOf('right') !== -1){ lookingAtPosition[0] = lookingAtPosition[0] + 1; }
    else if (_worldCursor.direction.indexOf('left') !== -1){ lookingAtPosition[0] = lookingAtPosition[0] - 1; }
    if (_worldCursor.direction.indexOf('down') !== -1){ lookingAtPosition[1] = lookingAtPosition[1] + 1; }
    else if (_worldCursor.direction.indexOf('up') !== -1){ lookingAtPosition[1] = lookingAtPosition[1] - 1; }

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

    do {
        let firstTile = tilesAroundPosition.shift();
        if (firstTile === lookingAtPosition){ tilesAroundPosition.push(firstTile); break; }
        else { tilesAroundPosition.push(firstTile); }
    } while (tilesAroundPosition[0] !== lookingAtPosition);

    if (dataLabels.length && dataBattles.length){
        actionData.showActionArea = true;
        actionData.readyTeamSprites = true;

        let dataBattlesJoined = (function(battles){
            for (var i = 0, list = []; i < battles.length; i++){
                let battle = battles[i][0];
                list.push(battle);
                } return list;
            })(dataBattles).join(',');

        let dataBattleStarsJoined = (function(stars){
            for (var i = 0, list = []; i < stars.length; i++){
                let star = stars[i][0];
                list.push(star);
                } return list;
            })(dataBattleStars).join(',');

        let dataLabelsJoined = (function(labels){
            for (var i = 0, markup = []; i < labels.length; i++){
                let label = labels[i][0], labelPosition = labels[i][1];
                let positionRelative = _self.getPositionRelative(_worldCursor.position, labelPosition);
                let labelText = label;
                let labelClasses = 'label sublabel';
                labelClasses += ' ' + (positionRelative[0] === 0 ? 'center' : (positionRelative[0] < 0 ? 'left' : 'right'));
                labelClasses += ' ' + (positionRelative[1] === 0 ? 'middle' : (positionRelative[1] < 0 ? 'up' : 'down'));
                markup.push('<strong class="'+labelClasses+'" data-pos="' + labelPosition + '" data-rel="' + positionRelative + '">' + labelText + '</strong>');
                } return markup;
            })(dataLabels).join('');

        let joinedDataAttrs = '';
        if (dataBattles.length){ joinedDataAttrs += ' data-battle="'+dataBattlesJoined+'"'; }
        if (dataBattleStars.length){ joinedDataAttrs += ' data-battle-star="'+dataBattleStarsJoined+'"'; }

        actionData.actionAreaMarkup += dataLabelsJoined;
        actionData.sideButtonsMarkup += '<strong class="button big-button-title type empty"><span><sup>Engage With</sup> ' + (dataBattles.length > 1 ? dataBattles.length+' ' : '') + 'Target' + (dataBattles.length > 1 ? 's' : '') + ' ?</span></strong>';

        let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
        let playerActiveRobots = 0;
        let _worldPlayerRobotsKeys = Object.keys(_worldPlayer.robots);
        for (var i = 0; i < _worldPlayerRobotsKeys.length; i++){
            let token = _worldPlayerRobotsKeys[i];
            let info = _worldPlayer.robots[token] || false;
            if (!info || info.disabled){ continue; }
            playerActiveRobots++;
            }

        let isSoloMechaBattle = dataBattles.length === 1 && firstBattleRobot.class === 'mecha' ? true : false;
        let isSoloMechaRescue = isSoloMechaBattle && firstBattleEvent.kind2 === 'rescue' ? true : false;

        let showBattleButton = true;
        let showRecruitButton = false;
        let showWhistleButton = false;
        let showPelletButton = false;
        let pelletStat = '';
        let pelletToken = '';
        let pelletType = '';

        if (!playerIsCursor && isSoloMechaBattle){
            if (isSoloMechaRescue){
                showBattleButton = false;
                showRecruitButton = true;
            } else {
                if (_worldPlayer.token === 'dr-lalinde'){
                    showWhistleButton = true;
                } else if (_worldPlayer.token === 'dr-light'){
                    showPelletButton = true;
                    pelletStat = 'Defense';
                    pelletToken = 'defense-pellet';
                    pelletType = 'defense';
                } else if (_worldPlayer.token === 'dr-wily'){
                    showPelletButton = true;
                    pelletStat = 'Attack';
                    pelletToken = 'attack-pellet';
                    pelletType = 'attack';
                } else if (_worldPlayer.token === 'dr-cossack'){
                    showPelletButton = true;
                    pelletStat = 'Speed';
                    pelletToken = 'speed-pellet';
                    pelletType = 'speed';
                }
            }
        }

        if (showBattleButton){
            if (playerActiveRobots >= 1){
                actionData.sideButtonsMarkup += '<a class="button big-button" data-action="start-battle"' + joinedDataAttrs + '><span><sup>Ready To</sup> Start Battle</span></a>';
                } else {
                actionData.sideButtonsMarkup += '<a class="button big-button disabled"' + joinedDataAttrs + '><span><sup>Ready To</sup> Start Battle</span></a>';
                }
            }

        if (showRecruitButton || showWhistleButton || showPelletButton){
            let playerHasTeamSlot = _worldPlayer.team.length < _config.maxRobotsPerPlayer ? true : false;
            let mechaBattleEvent = firstBattleEvent;
            let mechaTarget = mechaBattleEvent.token;

            if (showRecruitButton){
                let recruitActionToken = 'recruit-friendly-mecha';
                let recruitSpanLabel = '<sup>Recruit</sup> Friendly Mecha';
                if (playerHasTeamSlot){
                    actionData.sideButtonsMarkup += '<a class="button big-button type shield" data-action="' + recruitActionToken + '" data-target="' + mechaTarget + '" data-is-rescue="true">';
                    actionData.sideButtonsMarkup += '<span>' + recruitSpanLabel + '</span>';
                    actionData.sideButtonsMarkup += '</a>';
                    } else {
                    actionData.sideButtonsMarkup += '<a class="button big-button disabled">';
                    actionData.sideButtonsMarkup += '<span>' + recruitSpanLabel + '</span>';
                    actionData.sideButtonsMarkup += '</a>';
                    }
                }
            else if (showWhistleButton){
                let mechaWhistleQuantity = _self.getItemQuantity('mecha-whistle');
                let mechaWhistleSpriteMarkup = _self.getItemSpriteMarkup('mecha-whistle');
                let mechaWhistleSpanLabel = '<sup>Use</sup> Mecha Whistle <sub>&times; ' + mechaWhistleQuantity + '</sub>';
                if (playerHasTeamSlot && mechaWhistleQuantity > 0){
                    actionData.sideButtonsMarkup += '<a class="button big-button type shield" data-action="use-mecha-whistle" data-target="' + mechaTarget + '" data-is-rescue="false">';
                    actionData.sideButtonsMarkup += '<span class="has-sprite">' + mechaWhistleSpanLabel + mechaWhistleSpriteMarkup + '</span>';
                    actionData.sideButtonsMarkup += '</a>';
                    } else {
                    actionData.sideButtonsMarkup += '<a class="button big-button disabled">';
                    actionData.sideButtonsMarkup += '<span class="has-sprite">' + mechaWhistleSpanLabel + mechaWhistleSpriteMarkup + '</span>';
                    actionData.sideButtonsMarkup += '</a>';
                    }
                }
            else if (showPelletButton){
                let pelletQuantity = _self.getItemQuantity(pelletToken);
                let pelletSpriteMarkup = _self.getItemSpriteMarkup(pelletToken);
                let pelletSpanLabel = '<sup>Throw</sup> ' + pelletStat + ' Pellet <sub>&times; ' + pelletQuantity + '</sub>';
                if (playerHasTeamSlot && pelletQuantity > 0){
                    actionData.sideButtonsMarkup += '<a class="button big-button type ' + pelletType + '" data-action="throw-stat-pellet" data-target="' + mechaTarget + '" data-pellet="' + pelletToken + '">';
                    actionData.sideButtonsMarkup += '<span class="has-sprite">' + pelletSpanLabel + pelletSpriteMarkup + '</span>';
                    actionData.sideButtonsMarkup += '</a>';
                    } else {
                    actionData.sideButtonsMarkup += '<a class="button big-button disabled">';
                    actionData.sideButtonsMarkup += '<span class="has-sprite">' + pelletSpanLabel + pelletSpriteMarkup + '</span>';
                    actionData.sideButtonsMarkup += '</a>';
                    }
                }
            }

        /*
        let isSoloMechaBattle = dataBattles.length === 1 && firstBattleRobot.class === 'mecha' ? true : false;
        let isSoloMechaRescue = isSoloMechaBattle && firstBattleEvent.kind2 === 'rescue' ? true : false;
        let showBattleButton = true, showRecruitButton = false;
        if (!playerIsCursor && isSoloMechaBattle){ showRecruitButton = true; }
        if (showRecruitButton && isSoloMechaRescue){ showBattleButton = false; }
        if (typeof _worldPlayer.items['mecha-whistle'] === 'undefined'){ showRecruitButton = false; }

        if (showBattleButton){
            if (playerActiveRobots >= 1){
                actionData.sideButtonsMarkup += '<a class="button big-button" data-action="start-battle"' + joinedDataAttrs + '><span><sup>Ready To</sup> Start Battle</span></a>';
                } else {
                actionData.sideButtonsMarkup += '<a class="button big-button disabled"' + joinedDataAttrs + '><span><sup>Ready To</sup> Start Battle</span></a>';
                }
            }

        if (showRecruitButton){
            let playerHasTeamSlot = _worldPlayer.team.length < _config.maxRobotsPerPlayer ? true : false;
            let hasPermanentWhistle = false;
            let mechaBattleEvent = firstBattleEvent;
            let mechaWhistleQuantity = _self.getItemQuantity('mecha-whistle');
            let mechaWhistleSpriteMarkup = _self.getItemSpriteMarkup('mecha-whistle');
            let isRescueMecha = mechaBattleEvent.kind2 === 'rescue';
            let mechaWhistleSpanLabel = isRescueMecha ? '<sup>Recruit</sup> Friendly Mecha' : '<sup>Use</sup> Mecha Whistle';
            let mechaWhistleTarget = mechaBattleEvent.token;
            if (!hasPermanentWhistle && !isRescueMecha){ mechaWhistleSpanLabel += ' <sub>&times; ' + mechaWhistleQuantity + '</sub>' ; }
            let playerCanUseWhistle = (isRescueMecha || mechaWhistleQuantity > 0 || hasPermanentWhistle) && playerHasTeamSlot ? true : false;
            if (playerCanUseWhistle){
                let whistleActionToken = isRescueMecha ? 'recruit-friendly-mecha' : 'use-mecha-whistle';
                actionData.sideButtonsMarkup += '<a class="button big-button type shield" data-action="' + whistleActionToken + '" data-target="' + mechaWhistleTarget + '" data-is-rescue="' + (isRescueMecha ? 'true' : 'false') + '">';
                    if (!isRescueMecha){ actionData.sideButtonsMarkup += '<span class="has-sprite">' + mechaWhistleSpanLabel + mechaWhistleSpriteMarkup + '</span>'; }
                    else {actionData.sideButtonsMarkup += '<span>' + mechaWhistleSpanLabel + '</span>'; }
                actionData.sideButtonsMarkup += '</a>';
                }
            else {
                actionData.sideButtonsMarkup += '<a class="button big-button disabled">';
                    if (!isRescueMecha){ actionData.sideButtonsMarkup += '<span class="has-sprite">' + mechaWhistleSpanLabel + mechaWhistleSpriteMarkup + '</span>'; }
                    else { actionData.sideButtonsMarkup += '<span>' + mechaWhistleSpanLabel + '</span>'; }
                actionData.sideButtonsMarkup += '</a>';
                }
            }
        */

        actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
        actionData.showActionAreaType = 'battle';
        actionData.showActionAreaSound = 'mecha-taunt-sound' + (dataBattles.length > 1 ? '*'+dataBattles.length : '');
        actionData.zoomTimeoutDuration = 1000;
        }

    return actionData;
    }

// Define a quick function for handling battle and recruitment interactions via the dropdown
function handleBattleActionClick(action, $button, firstEvent){
    //console.log('%c' + 'mmrpgWorldMap.handleBattleActionClick(action:' + action + ')', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _elements = _self.elements;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _userId = _config.userId;
    let _playerId = _config.playerId;
    let _playerToken = _config.playerToken;
    let $thisWorld = _elements.world;

    if (action === 'battle-info'){
        _self.hideActionDropdown(false, false, false);
        }

    if (action === 'start-battle' || action === 'battle-info'){
        let battleId = $button.attr('data-battle') || false;
        let battleStarId = $button.attr('data-battle-star') || false;

        if (action === 'battle-info'){
            alert('Battle ID: ' + battleId + '\n\nThis is where you would show battle details.');
            }
        else if (action === 'start-battle'){
            let activeRobots = Object.values(_worldPlayerTeam);
            _self.playSoundEffect('lets-go-robots');
            let battleVars = [];
            battleVars.push('wap=false');
            battleVars.push('flag_skip_fadein=true');
            battleVars.push('this_user_id=' + _userId);
            battleVars.push('this_player_id=' + _playerId);
            battleVars.push('this_player_token=' + _playerToken);
            battleVars.push('this_player_robots=' + activeRobots.join(','));
            battleVars.push('this_battle_token=' + battleId);
            if (battleStarId){ battleVars.push('this_star_token=' + battleStarId); }
            let battleHref = 'battle.php?' + battleVars.join('&');
            let $teamSprites = _elements.teamSprites;
            $teamSprites.addClass('frame-lock');
            $thisWorld.addClass('busy');
            _self.incZoomLevel();
            _self.saveWorldState(function(){
                _self.incZoomLevel();
                $thisWorld.addClass('loading');
                window.location.href = battleHref;
                _self.incZoomLevel();
                }, true, false);
            }
        }
    else if (action === 'use-mecha-whistle' || action === 'recruit-friendly-mecha'){
        let delayMessageFor = 300;
        let delayDismissFor = 1200;
        let delayEffectFor = 2100;
        let playerToken = _worldPlayer.token;
        let playerNameSpan = _self.getPlayerNameSpan(playerToken);
        let itemToken = 'mecha-whistle';
        let itemNameSpan = _self.getItemNameSpan(itemToken);
        let battleTarget = $button.attr('data-target') || false;
        let isRescueMecha = $button.attr('data-is-rescue') === 'true';

        _self.playSoundEffect('get-weird-item');
        if (isRescueMecha){ _self.showWorldMessage(playerNameSpan + ' calls out to the friendly mecha!', delayMessageFor); }
        else { _self.showWorldMessage(playerNameSpan + ' uses a ' + itemNameSpan + '!', delayMessageFor); }

        if (true){
            _self.showWorldMessage('...the mecha responded!', delayDismissFor);
            if (action === 'use-mecha-whistle'){ _self.decrementItemQuantity('mecha-whistle'); }
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
    else if (action === 'throw-stat-pellet'){
        let delayMessageFor = 300;
        let delayDismissFor = 1200;
        let delayEffectFor = 2100;
        let playerToken = _worldPlayer.token;
        let playerNameSpan = _self.getPlayerNameSpan(playerToken);
        let battleTarget = $button.attr('data-target') || false;
        let pelletToken = $button.attr('data-pellet') || false;
        let itemNameSpan = _self.getItemNameSpan(pelletToken);
        console.log('Action: throw-stat-pellet | Target: ' + battleTarget + ' | Pellet: ' + pelletToken);
        _self.playSoundEffect('get-weird-item');
        _self.showWorldMessage(playerNameSpan + ' throws a ' + itemNameSpan + '!', delayMessageFor);
        if (true){
            _self.showWorldMessage('...the mecha accepted the bribe!', delayDismissFor);
            console.warn('disabled _self.decrementItemQuantity(pelletToken);');
            //_self.decrementItemQuantity(pelletToken);
            setTimeout(function(){ _self.hideActionDropdown(false); }, delayDismissFor);
            setTimeout(function(){
                let $eventSprite = $('.sprite.battle[data-battle="' + battleTarget + '"]');
                $eventSprite.attr('data-frame', '02').addClass('zoom');
                let bribeHref = 'world.php?bribe=' + battleTarget;
                if (bribeHref){
                    _self.playSoundEffect('get-big-item');
                    _self.incZoomLevel();
                    $thisWorld.addClass('busy');
                    _self.saveWorldState(function(){
                        _self.incZoomLevel();
                        $thisWorld.addClass('loading');
                        window.location.href = bribeHref;
                        _self.incZoomLevel();
                        }, true, false);
                    $thisWorld.animate({opacity: 0}, 1200, function(){
                        $thisWorld.addClass('hidden');
                        });
                    }
                }, delayEffectFor);
            } else {
            _self.showWorldMessage('...but the mecha ignored it.', delayDismissFor);
            _self.playSoundEffect('small-debuff-received');
            }
        }

    return true;
    }

// Assign the sub-functions to the main class's prototype
mmrpgWorldMap.prototype.prepareBattleEventOptions = prepareBattleEventOptions;
mmrpgWorldMap.prototype.handleBattleActionClick = handleBattleActionClick;