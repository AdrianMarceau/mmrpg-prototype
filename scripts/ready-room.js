
// -- PROTOTYPE READY ROOM FUNCTIONALITY -- //

// "Mega Man RPG Ready Room Widget"
// This widget lets you have little robots bounce around and do stuff in a room
(function(){

    // Define a global namespace for the ready room functionality
    var thisReadyRoom = function(){};
    var thisReadyRoomConfig = {};
    window.mmrpgReadyRoom = thisReadyRoom;
    window.mmrpgReadyRoom.config = thisReadyRoomConfig;

    // Predefine ready room config settings we can work with later
    thisReadyRoomConfig.parentElement = false;
    thisReadyRoomConfig.animateEnabled = false;
    thisReadyRoomConfig.animateLastUpdate = 0;
    thisReadyRoomConfig.animateFrameTotal = 0;
    thisReadyRoomConfig.animateThreshold = 1000;
    thisReadyRoomConfig.animateChargeUps = {};
    thisReadyRoomConfig.framesPerSecond = 10;
    thisReadyRoomConfig.spriteGrid = {};
    thisReadyRoomConfig.spriteBounds = {minX: 10, maxX: 90, minY: 13, maxY: 36};
    thisReadyRoomConfig.spritesIndex = {};
    thisReadyRoomConfig.charactersIndex = {};
    thisReadyRoomConfig.charactersIndexKinds = [];
    thisReadyRoomConfig.isFiltered = false;
    thisReadyRoomConfig.isReady = false;

    // Define a function for initializing the ready room with unlocked robots
    thisReadyRoom.init = function($thisBanner, onComplete){
        //console.log('thisReadyRoom.init()');
        //console.log('thisReadyRoomConfig.charactersIndex =', thisReadyRoomConfig.charactersIndex);

        // If there's no player index to work with, we can't display the ready room
        if (typeof thisReadyRoomConfig.charactersIndex['player'] === 'undefined'
            || !Object.keys(thisReadyRoomConfig.charactersIndex['player']).length){
            return false;
            }

        // If there's no robot index to work with, we can't display the ready room
        if (typeof thisReadyRoomConfig.charactersIndex['robot'] === 'undefined'
            || !Object.keys(thisReadyRoomConfig.charactersIndex['robot']).length){
            return false;
            }

        // Compensate for missing onComplete function
        if (typeof onComplete !== 'function'){ onComplete = function(){}; }

        // If the ready room has not been created yet do so now, else collect references
        if (!$('.ready_room', $thisBanner).length){
            var $readyRoom = $('<div class="ready_room"></div>');
            var $readyRoomWrapper = $('<div class="wrapper inner"></div>');
            var $readyRoomWrapper2 = $('<div class="wrapper outer"></div>');
            var $readyRoomScene = $('<div class="scene"></div>');
            var $readyRoomTeam = $('<div class="team"></div>');
            var $readyRoomClicker = $('<div class="clicker"></div>');
            $readyRoomScene.appendTo($readyRoomWrapper);
            $readyRoomTeam.appendTo($readyRoomWrapper);
            $readyRoomClicker.appendTo($readyRoomWrapper);
            $readyRoomWrapper.appendTo($readyRoomWrapper2);
            $readyRoomWrapper2.appendTo($readyRoom);
            $readyRoomScene.append('<div class="sprite" data-kind="background" data-token="light-laboratory" style="background-image: url(images/fields/light-laboratory/battle-field_background_base.gif?'+gameSettings.cacheTime+'); z-index: 1;"></div>');
            $readyRoomScene.append('<div class="sprite" data-kind="foreground" data-token="light-laboratory" style="background-image: url(images/fields/light-laboratory/battle-field_foreground_base.png?'+gameSettings.cacheTime+'); z-index: 2;"></div>');
            $readyRoom.css({opacity: 0});
            $readyRoom.appendTo($thisBanner);
            // TEMP TEMP TEMP (for now, hard-code light-laboratory as the background)
        } else {
            var $readyRoom = $('.ready_room', $thisBanner);
            var $readyRoomScene = $('.scene', $readyRoom);
            var $readyRoomTeam = $('.team', $readyRoom);
            var $readyRoomClicker = $('.clicker', $readyRoom);
        }

        // Make sure this ready room has a refernce in the game settings
        thisReadyRoomConfig.parentElement = $readyRoom;

        // Collect the unlocked robot index and tokens for looping through momentarily
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        var thisPlayersIndex = thisReadyRoomConfig.charactersIndex['player'];
        var thisRobotsIndex = thisReadyRoomConfig.charactersIndex['robot'];
        var thisPlayersTokens = Object.keys(thisPlayersIndex);
        var thisRobotsTokens = Object.keys(thisRobotsIndex);
        //console.log('thisRobotsTokens = ', thisRobotsTokens.length, thisRobotsTokens);
        //console.log('thisRobotsIndex = ', typeof thisRobotsIndex, thisRobotsIndex);
        //console.log('thisPlayersTokens = ', thisPlayersTokens.length, thisPlayersTokens);
        //console.log('thisPlayersIndex = ', typeof thisPlayersIndex, thisPlayersIndex);
        // Empty the ready room of any existing sprites
        $readyRoomTeam.find('.sprite').remove();

        // Define the min and max values for the X and Y offsets
        var spriteBounds = thisReadyRoomConfig.spriteBounds;
        var spriteGrid = thisReadyRoomConfig.spriteGrid;

        // Update the sprite bounds if there aren't that many robots
        var readyRoomShrinkFactor = 30;
        if (gameSettings.totalRobotOptions < readyRoomShrinkFactor){
            var missingRobots = readyRoomShrinkFactor - gameSettings.totalRobotOptions;
            var shiftLimit = readyRoomShrinkFactor / 2;
            var xOffsetShift = Math.ceil(missingRobots * 2);
            if (xOffsetShift > shiftLimit){ xOffsetShift = shiftLimit; }
            spriteBounds.minX += xOffsetShift;
            spriteBounds.maxX -= xOffsetShift;
            //spriteBounds.maxX -= Math.ceil(xOffsetShift * 1.2);
            //console.log('modded spriteBounds =', spriteBounds);
            }

        // Using the above, define offset ranges mimicking 8 columns and 8 rows for later
        spriteGrid.colMax = 8;
        spriteGrid.rowMax = 8;
        spriteGrid.colWidth = Math.floor((spriteBounds.maxX - spriteBounds.minX) / spriteGrid.colMax);
        spriteGrid.rowHeight = Math.floor((spriteBounds.maxY - spriteBounds.minY) / spriteGrid.rowMax);
        spriteGrid.columnOffsets = {};
        spriteGrid.rowOffsets = {};
        for (var i = 0; i < spriteGrid.colMax; i++){ spriteGrid.columnOffsets[i] = Math.floor(spriteBounds.minX + ((spriteBounds.maxX - spriteBounds.minX) / (spriteGrid.colMax - 1)) * i); }
        for (var i = 0; i < spriteGrid.rowMax; i++){ spriteGrid.rowOffsets[i] = Math.floor(spriteBounds.minY + ((spriteBounds.maxY - spriteBounds.minY) / (spriteGrid.rowMax - 1)) * i); }
        //console.log('spriteGrid.colMax =', spriteGrid.colMax, 'spriteGrid.rowMax =', spriteGrid.rowMax);
        //console.log('spriteGrid.colWidth =', spriteGrid.colWidth, 'spriteGrid.rowHeight =', spriteGrid.rowHeight);
        //console.log('spriteGrid.columnOffsets =', spriteGrid.columnOffsets, 'spriteGrid.rowOffsets =', spriteGrid.rowOffsets);

        // Define an array for keeping track of how many sprites are in each row/column as they're populated
        spriteGrid.gridCounts = {};
        spriteGrid.columnCounts = {};
        spriteGrid.rowCounts = {};

        // Loop through unlocked players and add them to the team div as "sprite" elements
        for (var i = 0; i < thisPlayersTokens.length; i++){
            var playerToken = thisPlayersTokens[i];
            var unlockedPlayer = thisPlayersIndex[playerToken];
            thisReadyRoom.addPlayerSprite(playerToken, unlockedPlayer);
            }

        // Loop through unlocked robots and add them to the team div as "sprite" elements
        var newEntranceTimeout = 1000;
        var newEntranceOffsetX = 90; //spriteBounds.maxX;
        var newEntranceOffsetY = spriteBounds.minY;
        for (var i = 0; i < thisRobotsTokens.length; i++){
            var robotToken = thisRobotsTokens[i];
            var robotInfo = thisRobotsIndex[robotToken];
            if (typeof robotInfo.flags !== 'undefined'
                && robotInfo.flags.indexOf('is_newly_unlocked') !== -1){
                newEntranceTimeout += 1000;
                newEntranceOffsetX -= 6;
                newEntranceOffsetY -= 1;
                (function(robotToken, robotInfo, newEntranceTimeout, newEntranceOffsetX, newEntranceOffsetY){
                    //console.log('queue entrance animation for ', robotInfo.token);
                    //console.log('robotInfo =', robotInfo);
                    thisReadyRoom.addRobotSprite(robotToken, robotInfo, {position: [110, (spriteBounds.maxY + 6)], direction: 'left', frame: 'slide'});
                    setTimeout(function(){
                        //console.log('slide-in triggered for ', robotInfo.token);
                        thisReadyRoom.updateRobot(robotToken, {position: [newEntranceOffsetX, newEntranceOffsetY], direction: 'left', frame: 'slide'});
                        setTimeout(function(){
                            //console.log('taunt triggered for ', robotInfo.token);
                            thisReadyRoom.updateRobot(robotToken, {frame: 'taunt'});
                            robotInfo.flags.splice(robotInfo.flags.indexOf('is_newly_unlocked'), 1);
                            }, 900);
                        }, newEntranceTimeout);
                    })(robotToken, robotInfo, newEntranceTimeout, newEntranceOffsetX, newEntranceOffsetY);
                } else {
                thisReadyRoom.addRobotSprite(robotToken, robotInfo);
                }
            }

        // We can fade-in the ready room now
        $readyRoom.css({opacity: 1});

        //console.log('spriteGrid.gridCounts =', spriteGrid.gridCounts);
        //console.log('spriteGrid.columnCounts =', spriteGrid.columnCounts);
        //console.log('spriteGrid.rowCounts =', spriteGrid.rowCounts);
        //console.log('readyRoomSpritesIndex =', readyRoomSpritesIndex);

        // Define click events for the clicker element now that everything is set up
        var clickTimeout = false;
        var clickHandler = function(e){
            e.preventDefault();
            //console.log('%cCLICKED the ready room clicker!', 'color: green;');
            if (clickTimeout !== false){ return false; }
            // get the click position of the element as a percentage of the element's width/height
            var clickX = e.pageX - $readyRoom.offset().left;
            var clickY = $readyRoom.height() - (e.pageY - $readyRoom.offset().top);
            var clickXPercent = Math.ceil((clickX / $readyRoom.width()) * 100);
            var clickYPercent = Math.ceil((clickY / $readyRoom.height()) * 100);
            //console.log('clickX =', clickX, 'clickY =', clickY);
            //console.log('clickXPercent =', clickXPercent, 'clickYPercent =', clickYPercent);
            // using the above information, check if there are any nearby sprites to this location
            var searchRadius = 30;
            var filterProperties = false;
            var pseudoSprite = {token: 'user', position: [clickXPercent, clickYPercent]};
            var nearbySprites = thisReadyRoom.getNearbySprites(pseudoSprite, searchRadius, filterProperties);
            //console.log('nearbySprites =', nearbySprites.length, nearbySprites);
            //console.log('readyRoomSpritesIndex =', thisReadyRoomConfig.spritesIndex);
            // loop through the nearby sprites and update them all to be facing the click
            for (var i = 0; i < nearbySprites.length; i++){
                //console.log('for (i = ', i, '){ ... }');
                var nearbySpriteData = nearbySprites[i];
                var nearbySprite = nearbySpriteData.sprite;
                var nearbyDistance = nearbySpriteData.distance;
                //console.log('nearbySprite @', nearbyDistance, 'away w/', nearbySprite);
                var newDirection = nearbySprite.position[0] > clickXPercent ? 'left' : 'right';
                thisReadyRoom.updateCharacter(nearbySprite.kind, nearbySprite.token, {direction: newDirection});
                }
            $readyRoom.addClass('clicked');
            if (clickTimeout !== false){ clearTimeout(clickTimeout); }
            clickTimeout = setTimeout(function(){
                $readyRoom.removeClass('clicked');
                clearTimeout(clickTimeout);
                clickTimeout = false;
                }, 100);
            };
        $readyRoomClicker.bind('click', clickHandler);
        $readyRoomClicker.bind('touchstart', clickHandler);
        $readyRoomClicker.bind('touchend', clickHandler);

        // Update the ready flag for the ready room
        thisReadyRoomConfig.isReady = true;

        // Run the onComplete function now that we're done
        onComplete();

    }

    // Define a function for loading new type of character index into the ready room
    thisReadyRoom.preloadCharacterIndex = function(characterKind, characterIndex){
        thisReadyRoomConfig.charactersIndex[characterKind] = characterIndex;
        thisReadyRoomConfig.charactersIndexKinds.push(characterKind);
    };

    // Define a function for refreshing the ready room with unlocked robots, optionally filtering by player token
    thisReadyRoom.refresh = function(filterByPlayerToken) {
        //console.log('thisReadyRoom.refresh(', filterByPlayerToken, ')');
        if (!thisReadyRoomConfig.isReady){ return false; }
        if (typeof filterByPlayerToken !== 'string') { filterByPlayerToken = false; }
        //console.log('filterByPlayerToken =', filterByPlayerToken);
        var $readyRoom = thisReadyRoomConfig.parentElement;
        var $readyRoomTeam = $('.team', $readyRoom);
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        var readyRoomSpritesIndexTokens = Object.keys(readyRoomSpritesIndex);
        //console.log('readyRoomSpritesIndexTokens =', readyRoomSpritesIndexTokens.length, readyRoomSpritesIndexTokens);
        //console.log('readyRoomSpritesIndex =', typeof readyRoomSpritesIndex, readyRoomSpritesIndex);
        if (filterByPlayerToken === false){ thisReadyRoomConfig.isFiltered = false; }
        else { thisReadyRoomConfig.isFiltered = true; }
        for (var i = 0; i < readyRoomSpritesIndexTokens.length; i++){
            var spriteToken = readyRoomSpritesIndexTokens[i];
            var spriteData = readyRoomSpritesIndex[spriteToken];
            var $thisSprite = spriteData.sprite;
            if (filterByPlayerToken === false) {
                spriteData.opacity = 1;
                $thisSprite.css({opacity: 1});
                }
            else {
                if (typeof spriteData.player !== 'undefined'
                    && spriteData.player !== filterByPlayerToken) {
                    spriteData.opacity = 0;
                    $thisSprite.css({opacity: 0});
                    } else {
                    spriteData.opacity = 1;
                    $thisSprite.css({opacity: 1});
                    }
                }
            }
    }

    // Define a function for animating the prototype ready room sprites = 0;
    thisReadyRoom.animate = function() {
        if (!thisReadyRoomConfig.isReady){ return false; }
        if (!thisReadyRoomConfig.animateEnabled){ return false; }
        //console.log('thisReadyRoom.animate()');

        // Collect references to important elements relevant to the ready-room
        var $readyRoom = thisReadyRoomConfig.parentElement;
        var $readyRoomScene = $('.scene', $readyRoom);
        var $readyRoomTeam = $('.team', $readyRoom);

        // Get all robot sprites currently in the ready room w/ the index
        var $allPlayerSprites = $('.sprite[data-kind="player"]', $readyRoomTeam);
        var $allRobotSprites = $('.sprite[data-kind="robot"]', $readyRoomTeam);
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;

        // Preset the last update time if not already set
        if (thisReadyRoomConfig.animateLastUpdate === 0){ new Date().getTime(); }

        // Collect the current timestamp and the previous update timestamp for comparrison
        var thisUpdateTime = new Date().getTime();
        var lastUpdateTime = thisReadyRoomConfig.animateLastUpdate;
        var diffUpdateTime = thisUpdateTime - lastUpdateTime;
        var minUpdateDiff = (1000 / thisReadyRoomConfig.framesPerSecond);
        //console.log('thisUpdateTime =', thisUpdateTime);
        //console.log('lastUpdateTime =', lastUpdateTime);
        //console.log('diffUpdateTime =', diffUpdateTime);
        //console.log('minUpdateDiff =', minUpdateDiff);

        // Prevent animations from happening more than the defined fps value thisReadyRoomConfig.framesPerSecond
        if (diffUpdateTime < minUpdateDiff) {
            //console.log('diffUpdateTime:', diffUpdateTime, ' < minUpdateDiff:', minUpdateDiff);
            window.requestAnimationFrame(thisReadyRoom.animate);
            return false;
            }

        // Otherwise update the last-update time to right now for future reference
        thisReadyRoomConfig.animateLastUpdate = thisUpdateTime;
        thisReadyRoomConfig.animateFrameTotal++;
        //console.log('thisReadyRoomConfig.animateFrameTotal =', thisReadyRoomConfig.animateFrameTotal);

        // Define a list variable to hold which sprites we should animate this round
        var spritesToAnimate = {};

        // Loop through players and append to the list of ones we should animate
        var thisPlayersIndex = thisReadyRoomConfig.charactersIndex['player'];
        var thisPlayersIndexTokens = Object.keys(thisPlayersIndex);
        for (var i = 0; i < thisPlayersIndexTokens.length; i++){
            var thisPlayerToken = thisPlayersIndexTokens[i];
            var thisPlayerInfo = thisPlayersIndex[thisPlayerToken];
            //console.log('thisPlayerToken/Info =', thisPlayerToken, thisPlayerInfo);
            if (!thisReadyRoom.animateSpeedCheck(thisPlayerInfo)){ continue; }
            var thisSpriteToken = thisPlayerToken;
            var thisSpriteData = {kind: 'player', token: thisPlayerToken, info: thisPlayerInfo}
            spritesToAnimate[thisSpriteToken] = thisSpriteData;
            //console.log('approved! =', thisSpriteToken, thisSpriteData);
        }

        // Loop through robots and append to the list of ones we should animate
        var thisRobotsIndex = thisReadyRoomConfig.charactersIndex['robot'];
        var thisRobotsIndexTokens = Object.keys(thisRobotsIndex);
        for (var i = 0; i < thisRobotsIndexTokens.length; i++){
            var thisRobotToken = thisRobotsIndexTokens[i];
            var thisRobotInfo = thisRobotsIndex[thisRobotToken];
            //console.log('thisRobotToken/Info =', thisRobotToken, thisRobotInfo);
            if (!thisReadyRoom.animateSpeedCheck(thisRobotInfo)){ continue; }
            var thisSpriteToken = thisRobotToken;
            var thisSpriteData = {kind: 'robot', token: thisRobotToken, info: thisRobotInfo}
            spritesToAnimate[thisSpriteToken] = thisSpriteData;
            //console.log('approved! =', thisSpriteToken, thisSpriteData);
        }

        // Loop through unlocked robots one-by-one and check if we should animate
        var spritesToAnimateTokens = Object.keys(spritesToAnimate);
        for (var i = 0; i < spritesToAnimateTokens.length; i++){

            var thisSpriteToken = spritesToAnimateTokens[i];
            var thisSpriteData = spritesToAnimate[thisSpriteToken];

            var thisCharacterToken = thisSpriteData.token;
            var thisCharacterKind = thisSpriteData.kind;
            var thisCharacterInfo = thisSpriteData.info;

            //console.log('Animating character ', thisCharacterToken, thisCharacterKind, thisCharacterInfo);

            // Collect refererences to the character's sprite and sprite inner elements now that we know we can animate
            //if (typeof readyRoomSpritesIndex[thisSpriteToken] === 'undefined'){ continue; }
            var thisSprite = readyRoomSpritesIndex[thisSpriteToken];
            var $thisSprite = thisSprite.sprite;
            var $thisSpriteInner = thisSprite.spriteInner;
            var oldSpriteProperties = {frame: thisSprite.frame, direction: thisSprite.direction, position: Object.values(thisSprite.position)};
            var newSpriteProperties = {};
            //console.log('thisSprite =', thisSprite);
            //console.log('oldSpriteProperties =', oldSpriteProperties);

            // If this sprite is hidden there's nothing to animate
            if (!thisSprite.opacity){ continue; }

            // If a sprite is currently in a non-base frame, priority one is to change it back
            if (oldSpriteProperties.frame !== 0){

                // Set the new sprite frame to zero
                newSpriteProperties.frame = 0;

                // Define the cooldown so we don't have them go too crazy
                var baseCooldownValue = thisReadyRoomConfig.framesPerSecond * 4;
                var newCooldownValue = Math.floor(baseCooldownValue * thisSprite.haste);
                thisSprite.cooldown = newCooldownValue;

                }
            // Otherwise, we can decide whether or not to trigger a frame and position change
            else {

                // Define possible actions we can take and ratio of each happenings
                var possibleDiceRolls = 20;
                var possibleTransitions = [];
                possibleTransitions.push({name: 'frame', chances: [1, 2, 3, 4, 5]});
                possibleTransitions.push({name: 'position', chances: [6, 8, 10]});
                possibleTransitions.push({name: 'direction', chances: [14, 18]});
                possibleTransitions.push({name: 'depth', chances: [20]});
                //console.log('possibleDiceRolls =', possibleDiceRolls);
                //console.log('possibleTransitions =', possibleTransitions);

                // Using the above transitions and their chances of each happening, select a random one considering their ratios in the process
                var randomDiceRoll = Math.floor(Math.random() * possibleDiceRolls) + 1;
                var randomTransition = (function(roll){
                    for (var i = 0; i < possibleTransitions.length; i++){
                        var transition = possibleTransitions[i];
                        var chances = transition.chances;
                        if (chances.indexOf(roll) !== -1){
                            return transition.name;
                            }
                        }
                    return false;
                    })(randomDiceRoll);
                //console.log('randomDiceRoll =', randomDiceRoll);
                //console.log('randomTransition =', randomTransition);

                // If the sprite was too close to the edge, we should force a direction change
                var spriteBounds = thisReadyRoomConfig.spriteBounds;
                var spriteAxisScale = 0.5; // the Y-axis is visually squished and we want to adjust the distance calculation to account for that
                if ((oldSpriteProperties.position[0] <= spriteBounds.minX && oldSpriteProperties.direction !== 'right')
                    || (oldSpriteProperties.position[0] >= spriteBounds.maxX && oldSpriteProperties.direction !== 'left')){
                    randomTransition = 'direction';
                    }

                // If a transition was decided, we should apply it now
                if (randomTransition !== false
                    && randomTransition.length){
                    //console.log('randomTransition =', randomTransition);

                    // Every once and a while, play it "smart" and react based on surroundings
                    var allowSmartTransition = true;
                    var allowFrameAdjustments = true;
                    var allowDirectionAdjustments = true;
                    var allowOffsetAdjustments = true;
                    if (thisCharacterInfo.flags.indexOf('is_newly_unlocked') !== -1){
                        allowSmartTransition = false;
                        allowFrameAdjustments = false;
                        allowDirectionAdjustments = false;
                        allowOffsetAdjustments = false;
                        }
                    var smartDiceRollMax = gameSettings.totalRobotOptions >= 10 ? gameSettings.totalRobotOptions : 10;
                    var smartDiceRollValue = Math.floor(Math.random() * smartDiceRollMax) + 1;
                    //console.log('smartDiceRollMax = ', smartDiceRollMax);
                    //console.log('smartDiceRollValue = ', smartDiceRollValue);
                    //console.log('allowSmartTransition = ', allowSmartTransition);
                    if (allowSmartTransition && smartDiceRollValue <= 7){
                        //console.log('LUCKY smartDiceRollValue(', smartDiceRollValue, ') <= 7 || (max:', smartDiceRollMax, ')');
                        //console.log('-> smart-transition requested for', thisCharacterToken, ' w/ info =', thisCharacterInfo);

                        // Calculate which sprites are nearby this robot for artificial intelligence purposes
                        var searchRadius = 30 - Math.floor(30 * (gameSettings.totalRobotOptions / 100));
                        var filterProperties = false;
                        var nearbySprites = thisReadyRoom.getNearbySprites(thisSprite, searchRadius, filterProperties);
                        //console.log('searchRadius =', searchRadius);
                        //console.log('nearbySprites =', nearbySprites.length, nearbySprites);
                        if (nearbySprites.length){

                            // Given each nearby sprite in format {sprite: object, distance: number}, sort by closest distance
                            nearbySprites.sort(function(a, b){ return a.distance - b.distance; });

                            // Precollect this character's weaknesses and affinities
                            var thisCharacterAttractions = typeof thisCharacterInfo.typeAffinities !== 'undefined' ? thisCharacterInfo.typeAffinities : [];
                            var thisCharacterRepellents = typeof thisCharacterInfo.typeWeaknesses !== 'undefined' ? thisCharacterInfo.typeWeaknesses : [];
                            if (typeof thisCharacterInfo.relationships !== 'undefined'){
                                var relationships = thisCharacterInfo.relationships;
                                if (typeof relationships.positive !== 'undefined'){ thisCharacterAttractions = thisCharacterAttractions.concat(relationships.positive); }
                                if (typeof relationships.negative !== 'undefined'){ thisCharacterRepellents = thisCharacterRepellents.concat(relationships.negative); }
                                }

                            // Now loop through the sprites in that order looking for some kind of relationship between characters
                            for (var j = 0; j < nearbySprites.length; j++){
                                var nearbySprite = nearbySprites[j].sprite;
                                var nearbySpriteDistance = nearbySprites[j].distance;
                                var nearbySpriteToken = nearbySprite.token;
                                var nearbySpriteKind = nearbySprite.kind;
                                var facingNearbySprite = false;
                                if (thisSprite.direction === 'right' && thisSprite.position[0] < nearbySprite.position[0]){ facingNearbySprite = true; }
                                else if (thisSprite.direction === 'left' && thisSprite.position[0] > nearbySprite.position[0]){ facingNearbySprite = true; }
                                //console.log('nearbySprite =', nearbySprite);
                                //console.log('nearbySpriteToken =', nearbySpriteToken);
                                //console.log('nearbySpriteKind =', nearbySpriteKind);
                                //console.log('facingNearbySprite =', facingNearbySprite);
                                //console.log('nearbySpriteDistance =', nearbySpriteDistance);
                                if (!facingNearbySprite){ continue; }
                                var nearbySpriteVibeMeter = 0;
                                // If the nearby sprite is a player, we can do some interesting things
                                if (nearbySpriteKind === 'player'){
                                    //console.log('nearbySprite is a player!');
                                    var nearbyPlayerInfo = thisPlayersIndex[nearbySpriteToken];
                                    //console.log('nearbyPlayerInfo =', typeof nearbyPlayerInfo, nearbyPlayerInfo);
                                    // Does the current sprite have an ATTRACTION TO the nearby player?
                                    if (typeof thisCharacterInfo.originalPlayer !== 'undefined' && thisCharacterInfo.originalPlayer === nearbyPlayerInfo.token){ nearbySpriteVibeMeter += 2; }
                                    else if (typeof thisCharacterInfo.currentPlayer !== 'undefined' && thisCharacterInfo.currentPlayer === nearbyPlayerInfo.token){ nearbySpriteVibeMeter += 1; }
                                    if (thisCharacterAttractions.indexOf(nearbyPlayerInfo.token) !== -1){ nearbySpriteVibeMeter += 2; }
                                    // Is the current sprite REPELLENT BY the nearby player?
                                    if (thisCharacterRepellents.indexOf(nearbyPlayerInfo.token) !== -1){ nearbySpriteVibeMeter -= 2; }
                                    }
                                else if (nearbySpriteKind === 'robot'){
                                    //console.log('nearbySprite is a robot!');
                                    var nearbyRobotInfo = thisRobotsIndex[nearbySpriteToken];
                                    //console.log('nearbyRobotInfo =', typeof nearbyRobotInfo, nearbyRobotInfo);
                                    // Does the current sprite have an ATTRACTION TO the nearby robot?
                                    if (thisCharacterInfo.type === nearbyRobotInfo.type){ nearbySpriteVibeMeter += 2; }
                                    if (thisCharacterAttractions.indexOf(nearbyRobotInfo.type) !== -1){ nearbySpriteVibeMeter += 1; }
                                    if (thisCharacterAttractions.indexOf(nearbyRobotInfo.type2) !== -1){ nearbySpriteVibeMeter += 1; }
                                    if (thisCharacterAttractions.indexOf(nearbyRobotInfo.token) !== -1){ nearbySpriteVibeMeter += 2; }
                                    // Is the current sprite REPELLENT BY the nearby robot?
                                    if (thisCharacterRepellents.indexOf(nearbyRobotInfo.type) !== -1){ nearbySpriteVibeMeter -= 1; }
                                    if (thisCharacterRepellents.indexOf(nearbyRobotInfo.type2) !== -1){ nearbySpriteVibeMeter -= 1; }
                                    if (thisCharacterRepellents.indexOf(nearbyRobotInfo.token) !== -1){ nearbySpriteVibeMeter -= 2; }
                                    }
                                //console.log('nearbySpriteVibeMeter for ', thisCharacterToken, ' vs ', nearbySpriteToken, ' =', nearbySpriteVibeMeter);

                                // If the other robot is just too close, we should give them some space
                                if (nearbySpriteDistance < (searchRadius / 3)) {
                                    randomTransition = 'position';
                                    //console.log('vibeCheck: ' + thisCharacterToken + ' feels ' + nearbySpriteToken + ' is too close and backs away');
                                    newSpriteProperties.frame = thisCharacterKind === 'player' ? 'running' : 'defend';
                                    newSpriteProperties.position = thisReadyRoom.getRelativePositionChange(thisSprite, nearbySprite, 'farther', 0.10, spriteBounds, spriteAxisScale);
                                    }

                                // Else if the current sprite has a positive vibe meter, we should do a positive action
                                else if (nearbySpriteVibeMeter > 0
                                    && nearbySpriteDistance > (searchRadius / 2)) {
                                    randomTransition = nearbySpriteDistance > (searchRadius / 3) ? 'position' : 'depth';
                                    //console.log('vibeCheck: ' + thisCharacterToken + ' inches toward ' + nearbySpriteToken + ' on positive vibes');
                                    newSpriteProperties.position = thisReadyRoom.getRelativePositionChange(thisSprite, nearbySprite, 'closer', 0.25, spriteBounds, spriteAxisScale);
                                    }

                                // Else if the current sprite has a negative vibe meter, we should do a negative action
                                else if (nearbySpriteVibeMeter < 0
                                    && nearbySpriteDistance < (searchRadius / 2)) {
                                    randomTransition = 'direction';
                                    //console.log('vibeCheck: ' + thisCharacterToken + ' runs away from ' + nearbySpriteToken + ' on negative vibes');
                                    newSpriteProperties.frame = thisCharacterKind === 'player' ? 'running' : 'slide';
                                    newSpriteProperties.position = thisReadyRoom.getRelativePositionChange(thisSprite, nearbySprite, 'farther', 0.50, spriteBounds, spriteAxisScale);
                                    if (thisSprite.position[0] < nearbySprite.position[0]) {
                                        thisSprite.direction = 'left';
                                        } else {
                                        thisSprite.direction = 'right';
                                        }
                                    }

                                }

                            }

                        }

                    // If a simple frame change was decided, process that
                    if (allowFrameAdjustments
                        && randomTransition === 'frame'
                        && typeof newSpriteProperties.frame === 'undefined'){

                        // Define the allowed frames we can transition to then pick one at random
                        var randInt = Math.floor(Math.random() * 10) + 1;
                        var possibleRandomFrames = [];
                        if (thisCharacterKind === 'player'){
                            possibleRandomFrames.push('taunt'); // taunt
                            possibleRandomFrames.push('command'); // defend
                            possibleRandomFrames.push('base2'); // base2
                            if (randInt >= 10){ possibleRandomFrames.push('victory'); } // victory
                            }
                        else if (thisCharacterKind === 'robot'){
                            possibleRandomFrames.push('taunt'); // taunt
                            possibleRandomFrames.push('defend'); // defend
                            possibleRandomFrames.push('base2'); // base2
                            if (randInt >= 10){ possibleRandomFrames.push('shoot'); } // shoot
                            if (randInt >= 8){ possibleRandomFrames.push('throw'); } // throw
                            if (randInt >= 6){ possibleRandomFrames.push('summon'); } // summon
                            }
                        else if (thisCharacterKind === 'shop'){
                            possibleRandomFrames.push('base2'); // base2
                            }
                        newSpriteProperties.frame = possibleRandomFrames[Math.floor(Math.random() * possibleRandomFrames.length)];

                        }

                    // If a directional change was directed
                    if (allowDirectionAdjustments
                        && randomTransition === 'direction'
                        && typeof newSpriteProperties.direction === 'undefined'){

                        // Flip the direction from whatever it is now
                        newSpriteProperties.direction = (oldSpriteProperties.direction !== 'left') ? 'left' : 'right';

                        // Define the cooldown so we don't have them go too crazy
                        var baseCooldownValue = thisReadyRoomConfig.framesPerSecond * 2;
                        var newCooldownValue = baseCooldownValue * thisSprite.haste;
                        thisSprite.cooldown = newCooldownValue;

                        }

                    // If a positional change was directed
                    if (randomTransition === 'position'
                        || randomTransition === 'depth'){

                        // Set the sprite to it's running or slide frames first (where available)
                        if (typeof newSpriteProperties.frame === 'undefined'){
                            if (thisCharacterKind === 'player'){ newSpriteProperties.frame = oldSpriteProperties.direction === 'left' ? 'running' : 'running3'; }
                            else if (thisCharacterKind === 'robot'){ newSpriteProperties.frame = 'slide'; }
                            }

                        // Only redefine position variables if they've not already been defined
                        if (typeof newSpriteProperties.position === 'undefined'){
                            // Then move the sprite in the direction they're facing
                            var oldPosition = oldSpriteProperties.position;
                            var newXPosition = oldPosition[0];
                            var moveDirection = oldSpriteProperties.direction === 'right' ? 'right' : 'left';
                            if (allowOffsetAdjustments && oldPosition[0] >= spriteBounds.maxX){ moveDirection = 'left'; }
                            else if (allowOffsetAdjustments && oldPosition[0] <= spriteBounds.minX){ moveDirection = 'right'; }

                            // Define the basic shift about for the robot given base vs haste (speed) value
                            //console.log('oldPosition =', oldPosition);
                            var shiftBase = 10;
                            var shiftVal = (shiftBase - Math.floor(shiftBase * thisSprite.haste));
                            newXPosition += shiftVal * (moveDirection === 'right' ? 1 : -1);
                            // If the character's position is someout out of bounds, it's okay to move them back into bounds abruptly now
                            if (allowOffsetAdjustments && newXPosition < spriteBounds.minX){ newXPosition = spriteBounds.minX + 2; }
                            else if (allowOffsetAdjustments && newXPosition > spriteBounds.maxX){ newXPosition = spriteBounds.maxX - 2; }
                            //console.log('shiftBase =', shiftBase, 'shiftVal =', shiftVal);
                            //console.log('newXPosition =', newXPosition);

                            // Update the properites with the new X positon
                            if (typeof newSpriteProperties.position === 'undefined'){ newSpriteProperties.position = oldPosition; }
                            newSpriteProperties.position[0] = newXPosition;
                            }

                        // Define the cooldown so we don't have them go too crazy
                        var baseCooldownValue = thisReadyRoomConfig.framesPerSecond * 1;
                        var newCooldownValue = baseCooldownValue * thisSprite.haste;
                        thisSprite.cooldown = newCooldownValue;

                        }

                    // If an depthal change was directed
                    if (randomTransition === 'depth'){
                        //console.log('depth transition triggered for ', thisSpriteToken);

                        // Only redefine position variables if they've not already been defined
                        if (typeof newSpriteProperties.position === 'undefined'){// Move the sprite up or down depending on where they are
                            var oldPosition = oldSpriteProperties.position;
                            var newYPosition = oldPosition[1];
                            var newZPosition = oldPosition[2];
                            var moveDirection = Math.floor(Math.random() * 2) ? 'up' : 'down';
                            if (oldPosition[1] >= spriteBounds.maxY){ moveDirection = 'down'; }
                            else if (oldPosition[1] <= spriteBounds.minY){ moveDirection = 'up'; }
                            //console.log('oldPosition =', oldPosition);
                            var shiftBase = 5;
                            var shiftVal = Math.floor(Math.random() * shiftBase) + 1;
                            newYPosition += shiftVal * (moveDirection === 'up' ? 1 : -1);
                            newZPosition = Math.floor(100 - newYPosition);
                            //console.log('shiftBase =', shiftBase, 'shiftVal =', shiftVal);
                            //console.log('newYPosition =', newYPosition, 'newZPosition =', newZPosition);
                            if (typeof newSpriteProperties.position === 'undefined'){ newSpriteProperties.position = oldPosition; }
                            newSpriteProperties.position[1] = newYPosition;
                            newSpriteProperties.position[2] = newZPosition;
                            }

                        }


                    }

                }
            //console.log('newSpriteProperties =', newSpriteProperties);

            if (Object.keys(newSpriteProperties).length){
                //console.log('ANIMATE ME!!! (', thisSpriteToken, ')');
                thisReadyRoom.animateCharacter(thisCharacterKind, thisCharacterToken, newSpriteProperties);
                }

            }

        // Request the next animation frame when ready
        requestAnimationFrame(thisReadyRoom.animate);

    }

    // Define a function for actually animating a given ready room character in some way
    thisReadyRoom.animateCharacter = function(kind, characterToken, newValues, onComplete){
        //console.log('thisReadyRoom.animateCharacter(characterToken:', characterToken, ', newValues:', newValues, ', onComplete:', typeof onComplete, ')');

        if (!thisReadyRoomConfig.isReady){ return false; }
        //if (!thisReadyRoomConfig.animateEnabled){ return false; }
        if (typeof characterToken !== 'string' || !characterToken.length){ return false; }
        if (typeof newValues !== 'object'){ newValues = {}; }
        if (typeof onComplete !== 'function'){ onComplete = function(){ /* ... */ }; }

        // Collect this character's info from the unlock index for later
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        var loadedCharactersIndex = {};
        if (kind === 'player'){ loadedCharactersIndex = thisReadyRoomConfig.charactersIndex['player']; }
        else if (kind === 'robot'){ loadedCharactersIndex = thisReadyRoomConfig.charactersIndex['robot']; }
        //else if (kind === 'shop'){ loadedCharactersIndex = gameSettings.customIndex.unlockedShopsIndex; }
        else { return false; }
        var characterIndexInfo = loadedCharactersIndex[characterToken];

        // Pull the sprite data and element reference from the index
        var thisSprite = readyRoomSpritesIndex[characterToken];
        var $thisSprite = thisSprite.sprite;
        var $thisSpriteInner = thisSprite.spriteInner;

        // Collect refererences to the character's sprite and sprite inner elements now that we know we can animate
        var thisSpriteSize = thisSprite.size;
        var thisSpriteFrame = thisSprite.frame;
        var thisSpriteDirection = thisSprite.direction;
        //console.log('OKAY I WILL ANIMATE YOU!!! (', characterToken, ' to frame ', newValues.frame, ')');

        // If a frame change was requested, we can process that now
        if (typeof newValues.frame !== 'undefined'){
            var newSpriteFrame = newValues.frame;
            if (typeof newSpriteFrame === 'string'){
                var spriteFrameTokens = ['base'];
                if (kind === 'player'){ spriteFrameTokens = ['base', 'taunt', 'victory', 'defeat', 'command', 'damage', 'base2', 'running', 'running2', 'running3']; }
                else if (kind === 'robot'){ spriteFrameTokens = ['base', 'taunt', 'victory', 'defeat', 'shoot', 'throw', 'summon', 'slide', 'defend', 'damage', 'base2']; }
                else if (kind === 'shop'){ spriteFrameTokens = ['base', 'base2']; }
                if (spriteFrameTokens.indexOf(newSpriteFrame) !== -1){ newSpriteFrame = spriteFrameTokens.indexOf(newSpriteFrame); }
                else { newSpriteFrame = 0; }
                }
            var newBackgroundOffset = -1 * (thisSpriteSize * newSpriteFrame);
            thisSprite.frame = newSpriteFrame;
            $thisSpriteInner.attr('data-frame', newSpriteFrame);
            $thisSpriteInner.css({'background-position': newBackgroundOffset+'px 0'});
            }

        // If a direction change was requested, we can process that now
        if (typeof newValues.direction !== 'undefined'){
            var newSpriteDirection = newValues.direction;
            thisSprite.direction = newSpriteDirection;
            $thisSpriteInner.attr('data-direction', newSpriteDirection);
            $thisSprite.css({'transform': 'scale('+(thisSprite.direction !== thisSprite.imageDirection ? -2 : 2)+', 2)'});
            }

        // If an opacity change was requested, we can process that now
        if (typeof newValues.opacity !== 'undefined'){
            var newSpriteOpacity = newValues.opacity;
            if (newSpriteOpacity > 1){ newSpriteOpacity = 1; }
            else if (newSpriteOpacity < 0){ newSpriteOpacity = 0; }
            thisSprite.opacity = newSpriteOpacity;
            $thisSprite.css({'opacity': newSpriteOpacity});
            }

        // If a position change was requested, we can process that now
        if (typeof newValues.position !== 'undefined'){
            //console.log('position change requested w/ newValues.position =', newValues.position);
            //var newSpritePosition = newValues.position;
            if (typeof newValues.position[0] === 'undefined'){ newValues.position[0] = null; }
            if (typeof newValues.position[1] === 'undefined'){ newValues.position[1] = null; }
            var parsePositionValue = function(newValue, oldValue){
                //console.log('parsePositionValue(newValue:', newValue, ', oldValue:', oldValue, ')');
                if (typeof newValue === 'undefined'){ return oldValue; }
                else if (typeof newValue === 'number'){ return newValue; }
                else if (typeof newValue !== 'string'){ return oldValue; }
                var modValue = oldValue;
                if (newValue.indexOf('+=') !== -1){ return modValue + parseInt(newValue.replace('+=', '')); }
                else if (newValue.indexOf('-=') !== -1){ return modValue - parseInt(newValue.replace('-=', '')); }
                else if (newValue.indexOf('*=') !== -1){ return modValue * parseInt(newValue.replace('*=', '')); }
                else if (newValue.indexOf('/=') !== -1){ return modValue / parseInt(newValue.replace('/=', '')); }
                else if (newValue.indexOf('%=') !== -1){ return modValue % parseInt(newValue.replace('%=', '')); }
                else if (newValue.indexOf('++') !== -1){ return modValue + 1; }
                else { return parseInt(newValue); }
                };
            var newSpritePosition = [];
            newSpritePosition.push(parsePositionValue(newValues.position[0], thisSprite.position[0]));
            newSpritePosition.push(parsePositionValue(newValues.position[1], thisSprite.position[1]));
            newSpritePosition.push(100 - newSpritePosition[1]);
            //console.log('newSpritePosition =', newSpritePosition);
            var brightExponent = 1 + (gameSettings.totalRobotOptions / 100);
            var newSpriteBrightness = Math.pow((newSpritePosition[2] / 100), brightExponent);
            thisSprite.position = newSpritePosition;
            var newCSS = {
                'left': newSpritePosition[0]+'%',
                'bottom': newSpritePosition[1]+'%',
                'z-index': newSpritePosition[2],
                'filter': 'brightness('+newSpriteBrightness+')'
                };
            //console.log('updating sprite position for ', characterToken, ' to ', newCSS);
            $thisSprite.css(newCSS);
            }

    }

    // Define a function for actually animating a given ready room player in some way
    thisReadyRoom.animatePlayer = function(playerToken, newValues, onComplete){
        //console.log('thisReadyRoom.animatePlayer(playerToken:', playerToken, ', newValues:', newValues, ', onComplete:', typeof onComplete, ')');
        return thisReadyRoom.animateCharacter('player', playerToken, newValues, onComplete);
    }

    // Define a function for actually animating a given ready room robot in some way
    thisReadyRoom.animateRobot = function(robotToken, newValues, onComplete){
        //console.log('thisReadyRoom.animateRobot(robotToken:', robotToken, ', newValues:', newValues, ', onComplete:', typeof onComplete, ')');
        return thisReadyRoom.animateCharacter('robot', robotToken, newValues, onComplete);
    }

    // Define a function for actually animating a given ready room shop in some way
    thisReadyRoom.animateShop = function(shopToken, newValues, onComplete){
        //console.log('thisReadyRoom.animateShop(shopToken:', shopToken, ', newValues:', newValues, ', onComplete:', typeof onComplete, ')');
        return thisReadyRoom.animateCharacter('shop', shopToken, newValues, onComplete);
    }

    // Define a function for determining whether a sprite should animate based on its speed
    thisReadyRoom.animateSpeedCheck = function(characterInfo){
        //console.log('thisReadyRoom.animateSpeedCheck(characterInfo:', characterInfo.token, ')');
        // Collect the character's key details to make this easier
        var characterToken = characterInfo.token;
        //console.log('characterToken/Info =', characterToken, characterInfo);
        // Collect a reference to the sprite's entry in the animation index
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        var thisSprite = readyRoomSpritesIndex[characterToken];
        //console.log('thisSprite =', typeof thisSprite, thisSprite);
        // If this character has a cooldown we gotta decease and wait
        //console.log('thisSprite.cooldown =', thisSprite.cooldown);
        if (thisSprite.cooldown > 0){ thisSprite.cooldown--; return; }
        else { thisSprite.cooldown = 0; }
        // Increase the cooldown value by the character's speed value
        thisSprite.charge += characterInfo.speedBase;
        //console.log(characterToken, '\n +characterSpeedBase(', characterInfo.speedBase, ')\n characterCharge(', thisSprite.charge, ')\n animateThreshold(', thisReadyRoomConfig.animateThreshold, ')');
        // If the cooldown value is less than the character's speed stat, we're not ready to animate yet
        if (thisSprite.charge < thisReadyRoomConfig.animateThreshold) { return false; }
        // Otherwise we're ready to animate and we need to reset the cooldown value
        thisSprite.charge = thisSprite.charge % thisReadyRoomConfig.animateThreshold;
        return true;

    }

    // Define a function for calculating the css animation duration for a given character sprite
    thisReadyRoom.getAnimationDuration = function(characterInfo){
        if (typeof characterInfo === 'undefined'){ return false; }
        var this_character_attack = typeof characterInfo.attackBase !== 'undefined' ? characterInfo.attackBase : 100;
        var this_character_defense = typeof characterInfo.defenseBase !== 'undefined' ? characterInfo.defenseBase : 100;
        var this_character_speed = typeof characterInfo.speedBase !== 'undefined' ? characterInfo.speedBase : 100;
        var character_animation_duration = 1.2;
        character_animation_duration -= character_animation_duration * (this_character_speed / (this_character_attack + this_character_defense + this_character_speed));
        if (character_animation_duration < 0.1){ character_animation_duration = 0.1; }
        return character_animation_duration;
    }

    // Define a function for abruptly stopping the ready room animation
    thisReadyRoom.startAnimation = function(){
        //console.log('thisReadyRoom.startAnimation()');
        thisReadyRoomConfig.animateEnabled = true;
        thisReadyRoom.animate();
        return;
    }

    // Define a function for abruptly stopping the ready room animation
    thisReadyRoom.stopAnimation = function(){
        //console.log('thisReadyRoom.stopAnimation()');
        thisReadyRoomConfig.animateEnabled = false;
        return;
    }

    // Define a function for showing the prototype ready room element
    thisReadyRoom.show = function(){
        //console.log('thisReadyRoom.show()');
        if (!thisReadyRoomConfig.isReady){ return false; }
        var $readyRoom = thisReadyRoomConfig.parentElement;
        $readyRoom.removeClass('hidden');
        $readyRoom.css({opacity: 1});
    }

    // Define a function for hiding the prototype ready room element
    thisReadyRoom.hide = function(){
        //console.log('thisReadyRoom.hide()');
        if (!thisReadyRoomConfig.isReady){ return false; }
        var $readyRoom = thisReadyRoomConfig.parentElement;
        $readyRoom.css({opacity: 0});
        $readyRoom.addClass('hidden');
    }

    // Define a function for adding a new robot to the unlocked robot index
    thisReadyRoom.addRobot = function(robotToken, robotInfo, focusRobot){
        //console.log('thisReadyRoom.addRobot(robotToken:', robotToken, ', robotInfo:', robotInfo, ', focusRobot:', focusRobot, ')');
        if (typeof focusRobot !== 'boolean'){ focusRobot = false; }
        // Collect the unlocked robots index
        var thisRobotsIndex = thisReadyRoomConfig.charactersIndex['robot'];
        //console.log('thisRobotsIndex =', thisRobotsIndex);
        // If the robot is already in the index, we don't need to do anything
        if (typeof thisRobotsIndex[robotToken] !== 'undefined'){ return false; }
        // Otherwise we need to add the robot to the index
        thisRobotsIndex[robotToken] = robotInfo;
        //console.log('thisRobotsIndex =', thisRobotsIndex);
        // Now we need to update the robot's sprite in the ready room
        thisReadyRoom.addRobotSprite(robotToken, robotInfo, {position: [110,15]});
        // Immediately update this robot's sprite after a short timeout
        var focusUpdateTimeout = setTimeout(function(){
            thisReadyRoom.updateRobot(robotToken, {frame: 'slide', direction: 'left', position: [80,5]});
            clearTimeout(focusUpdateTimeout);
            focusUpdateTimeout = setTimeout(function(){
                thisReadyRoom.updateRobot(robotToken, {frame: 'taunt'});
                }, 600);
            }, 100);
    }

    // Define a function for updating an existing sprite in the ready room given values
    thisReadyRoom.updateCharacter = function(characterKind, characterToken, newSpriteProperties){
        //console.log('thisReadyRoom.updateCharacter(characterKind:', characterKind, ', characterToken:', characterToken, ', newSpriteProperties:', newSpriteProperties, ')');
        // Collect the unlocked characters index
        var loadedCharactersIndex = {};
        if (characterKind === 'player'){ loadedCharactersIndex = thisReadyRoomConfig.charactersIndex['player']; }
        else if (characterKind === 'robot'){ loadedCharactersIndex = thisReadyRoomConfig.charactersIndex['robot']; }
        //else if (characterKind === 'shop'){ loadedCharactersIndex = gameSettings.customIndex.unlockedShopsIndex; }
        else { return false; }
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        //console.log('loadedCharactersIndex =', loadedCharactersIndex);
        //console.log('readyRoomSpritesIndex =', readyRoomSpritesIndex);
        // Abstract the characterToken in case the user has provided the "all" option
        var requiredCharacters = [];
        if (characterToken === 'all'
            || characterToken === 'most'
            || characterToken === 'some'){
            requiredCharacters = Object.keys(loadedCharactersIndex);
            if (characterToken !== 'all'){
                // shuffle and slice the characters
                var sliceToPercent = characterToken === 'most' ? 50 : 25;
                var sliceToCount = Math.floor(requiredCharacters.length * (sliceToPercent / 100));
                shuffleArray(requiredCharacters);
                requiredCharacters = requiredCharacters.slice(0, sliceToCount);
                }
            }
        else if (typeof characterToken === 'function'){
            var characterTokenFunction = characterToken;
            var unlockedCharactersTokens = Object.keys(loadedCharactersIndex);
            for (var i = 0; i < unlockedCharactersTokens.length; i++){
                var characterToken = unlockedCharactersTokens[i];
                var characterInfo = loadedCharactersIndex[characterToken];
                if (!characterTokenFunction(characterToken, characterInfo)){ continue; }
                requiredCharacters.push(characterToken);
                }
            }
        else {
            requiredCharacters.push(characterToken);
            }
        // Loop through required characters and apply the changes to all of them
        for (var i = 0; i < requiredCharacters.length; i++){
            var characterToken = requiredCharacters[i];
            // If the character is doesn't exist in the index, we can't do anything to it
            if (typeof loadedCharactersIndex[characterToken] === 'undefined'){ return false; }
            if (typeof readyRoomSpritesIndex[characterToken] === 'undefined'){ return false; }
            // Otherwise we can collect info about the character
            var characterInfo = loadedCharactersIndex[characterToken];
            var spriteInfo = readyRoomSpritesIndex[characterToken];
            //console.log('characterInfo =', characterInfo);
            //console.log('spriteInfo =', spriteInfo);
            // Trigger the animate function with the provided new values
            thisReadyRoom.animateCharacter(characterKind, characterToken, newSpriteProperties);
        }
    }

    // Define a function for updating a existing player sprite(s) in the ready room given values
    thisReadyRoom.updatePlayer = function(playerToken, newSpriteProperties){
        //console.log('thisReadyRoom.updatePlayer(playerToken:', playerToken, ', newSpriteProperties:', newSpriteProperties, ')');
        return thisReadyRoom.updateCharacter('player', playerToken, newSpriteProperties);
    }

    // Define a function for updating existing robot sprite(s) in the ready room given values
    thisReadyRoom.updateRobot = function(robotToken, newSpriteProperties){
        //console.log('thisReadyRoom.updateRobot(robotToken:', robotToken, ', newSpriteProperties:', newSpriteProperties, ')');
        return thisReadyRoom.updateCharacter('robot', robotToken, newSpriteProperties);
    }

    // Define a function for updating existing shop sprite(s) in the ready room given values
    thisReadyRoom.updateShop = function(shopToken, newSpriteProperties){
        //console.log('thisReadyRoom.updateShop(shopToken:', shopToken, ', newSpriteProperties:', newSpriteProperties, ')');
        return thisReadyRoom.updateCharacter('shop', shopToken, newSpriteProperties);
    }

    // Define a function for adding a new character sprite to the ready room given info
    thisReadyRoom.addCharacterSprite = function(kind, characterToken, characterInfo, spriteProperties){
        //console.log('thisReadyRoom.addCharacterSprite(kind:', kind, ', characterToken:', characterToken, ', characterInfo:', characterInfo, ', spriteProperties:', spriteProperties, ')');

        // Initial setup for both player and robot
        var spriteGrid = thisReadyRoomConfig.spriteGrid;
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        var $readyRoom = thisReadyRoomConfig.parentElement;
        var $readyRoomTeam = $('.team', $readyRoom);
        var thisSpriteSize = characterInfo.imageSize;
        var thisSpriteSizeX = thisSpriteSize + 'x' + thisSpriteSize;
        //console.log('spriteGrid =', spriteGrid);
        //console.log('readyRoomSpritesIndex =', readyRoomSpritesIndex);
        //console.log('$readyRoom =', typeof $readyRoom, $readyRoom);
        //console.log('$readyRoomTeam =', typeof $readyRoomTeam, $readyRoomTeam);
        //console.log('thisSpriteSize =', thisSpriteSize);
        //console.log('thisSpriteSizeX =', thisSpriteSizeX);

        // Differences scoped by kind of character sprite
        var thisToken, thisPlayerToken, thisRobotToken, thisShopToken, spriteImagePathPrefix;
        if (kind === 'player') {
            thisToken = thisPlayerToken = characterInfo.token;
            spriteImagePathPrefix = 'players';
        } else if (kind === 'robot') {
            thisPlayerToken = characterInfo.currentPlayer;
            thisToken = thisRobotToken = characterInfo.token;
            spriteImagePathPrefix = 'robots';
        } else if (kind === 'shop'){
            thisPlayerToken = characterInfo.currentPlayer;
            thisToken = thisShopToken = characterInfo.token;
            spriteImagePathPrefix = 'shops';
        }
        //console.log('{} =', {thisToken: thisToken, thisPlayerToken: thisPlayerToken, thisRobotToken: thisRobotToken, thisShopToken: thisShopToken});
        //console.log('spriteImagePathPrefix =', spriteImagePathPrefix);

        // Common logic continues
        if (typeof spriteProperties !== 'object') { spriteProperties = {}; }
        var spriteDirection = Math.floor(Math.random() * 2) ? 'left' : 'right';
        var spriteFrame = 0;
        var thisSpriteImage = typeof characterInfo.image !== 'undefined' && characterInfo.image.length ? characterInfo.image : characterInfo.token;
        var thisSpriteImageDirection = 'right';
        var thisSpriteImagePath = 'images/' + spriteImagePathPrefix + '/' + thisSpriteImage + '/sprite_' + thisSpriteImageDirection + '_' + thisSpriteSizeX + '.png';
        //console.log('spriteDirection =', spriteDirection);
        //console.log('spriteFrame =', spriteFrame);
        //console.log('thisSpriteImage =', thisSpriteImage);
        //console.log('thisSpriteImageDirection =', thisSpriteImageDirection);
        //console.log('spriteImagePathPrefix =', spriteImagePathPrefix);

        // pick a random column and row for this robot to start off in
        var randColRow = thisReadyRoom.getRandColRow(1);
        var randColRowOffsets = thisReadyRoom.getColRowCenter(randColRow[0], randColRow[1]);
        //console.log('randColRow =', randColRow);
        //console.log('randColRowOffsets =', randColRowOffsets);
        if (typeof spriteProperties.position !== 'undefined'
            && typeof spriteProperties.position[0] !== 'undefined'){
            var spriteOffsetX = parseInt(spriteProperties.position[0]);
            } else {
            var spriteOffsetX = randColRowOffsets[0];
            if (spriteDirection === 'right'){ spriteOffsetX -= Math.floor(Math.random() * spriteGrid.colWidth); }
            else { spriteOffsetX += Math.floor(Math.random() * spriteGrid.colWidth); }
            }
        if (typeof spriteProperties.position !== 'undefined'
            && typeof spriteProperties.position[1] !== 'undefined'){
            var spriteOffsetY = parseInt(spriteProperties.position[1]);
            } else {
            var spriteOffsetY = randColRowOffsets[1];
            }
        var spriteOffsetZ = 100 - spriteOffsetY;
        var brightExponent = 1 + (gameSettings.totalRobotOptions / 100);
        var spriteBrightness = Math.pow((spriteOffsetZ / 100), brightExponent);
        var spriteFilterValue = 'brightness('+spriteBrightness+')';
        //console.log('spriteOffsetX =', spriteOffsetX);
        //console.log('spriteOffsetY =', spriteOffsetY);
        //console.log('spriteOffsetZ =', spriteOffsetZ);
        var spriteAnimationDuration = thisReadyRoom.getAnimationDuration(characterInfo);
        //console.log('spriteAnimationDuration(C) =', spriteAnimationDuration);

        // Generate the markup for the sprite and the inner sprite
        var $sprite = $('<div class="sprite" data-kind="' + kind + '" data-player="' + thisPlayerToken + '"></div>');
        if (kind === 'robot'){ $sprite.attr('data-robot', thisRobotToken); }
        if (kind === 'shop'){ $sprite.attr('data-shop', thisShopToken); }
        $sprite.css({'left': spriteOffsetX+'%', 'bottom': spriteOffsetY+'%', 'z-index': spriteOffsetZ});
        $sprite.css({'filter': spriteFilterValue});
        $sprite.css({'transform': 'scale('+(spriteDirection !== thisSpriteImageDirection ? -2 : 2)+', 2)'});
        var $spriteInner = $('<div class="sprite" data-size="'+thisSpriteSize+'" data-direction="'+spriteDirection+'" data-frame="'+spriteFrame+'"></div>');
        $spriteInner.css('background-image', 'url('+thisSpriteImagePath+'?'+gameSettings.cacheTime+')');
        $spriteInner.css({'animation-duration': spriteAnimationDuration+'s'});
        $sprite.append($spriteInner);

        // Generate the sprite data object and then add it to the index
        var spriteToken = thisToken;
        var spriteData = {
            sprite: $sprite,
            spriteInner: $spriteInner,
            image: thisSpriteImage,
            imagePath: thisSpriteImagePath,
            imageDirection: thisSpriteImageDirection,
            size: thisSpriteSize,
            direction: spriteDirection,
            frame: spriteFrame,
            position: [spriteOffsetX, spriteOffsetY, spriteOffsetZ],
            opacity: 1,
            animate: true,
            haste: spriteAnimationDuration,
            charge: 0,
            cooldown: 0,
            kind: kind,
            token: thisToken,
            player: thisPlayerToken
            };
        if (kind === 'robot'){ spriteData.robot = thisRobotToken; }
        else if (kind === 'shop'){ spriteData.shop = thisShopToken; }

        // Add the newly generates sprite data to the ready room
        //console.log('$readyRoomTeam.append($sprite); // $sprite =', $sprite);
        //console.log('readyRoomSpritesIndex['+thisToken+'] = spriteData; // spriteData =', spriteData);
        $sprite.appendTo($readyRoomTeam);
        readyRoomSpritesIndex[thisToken] = spriteData;

    }

    // Define a function for adding a new player sprite to the ready room given info
    thisReadyRoom.addPlayerSprite = function(playerToken, playerInfo, spriteProperties){
        //console.log('thisReadyRoom.addPlayerSprite(playerToken:', playerToken, ', playerInfo:', playerInfo, ', spriteProperties:', spriteProperties, ')');
        return thisReadyRoom.addCharacterSprite('player', playerToken, playerInfo, spriteProperties);
    }

    // Define a function for adding a new robot sprite to the ready room given info
    thisReadyRoom.addRobotSprite = function(robotToken, robotInfo, spriteProperties){
        //console.log('thisReadyRoom.addRobotSprite(robotToken:', robotToken, ', robotInfo:', robotInfo, ', spriteProperties:', spriteProperties, ')');
        return thisReadyRoom.addCharacterSprite('robot', robotToken, robotInfo, spriteProperties);
    }

    // Define a function for adding a new shop sprite to the ready room given info
    thisReadyRoom.addShopSprite = function(shopToken, shopInfo, spriteProperties){
        //console.log('thisReadyRoom.addShopSprite(shopToken:', shopToken, ', shopInfo:', shopInfo, ', spriteProperties:', spriteProperties, ')');
        return thisReadyRoom.addCharacterSprite('shop', shopToken, shopInfo, spriteProperties);
    }

    // Define a function for getting a random column and row within that the above offsets
    thisReadyRoom.getRandColRow = function(limitPerCell){
        var spriteGrid = thisReadyRoomConfig.spriteGrid;
        if (typeof limitPerCell !== 'number'){ limitPerCell = 4; }
        var randomColumn = Math.floor(Math.random() * spriteGrid.colMax);
        var randomRow = Math.floor(Math.random() * spriteGrid.rowMax);
        var randomCell = randomColumn+'-'+randomRow;
        var columnCount = typeof spriteGrid.columnCounts[randomColumn] !== 'undefined' ? spriteGrid.columnCounts[randomColumn] : 0;
        var rowCount = typeof spriteGrid.rowCounts[randomRow] !== 'undefined' ? spriteGrid.rowCounts[randomRow] : 0;
        var cellSpriteCount = typeof spriteGrid.gridCounts[randomCell] !== 'undefined' ? spriteGrid.gridCounts[randomCell] : 0;
        //console.log('randomColumn =', randomColumn);
        //console.log('randomRow =', randomRow);
        //console.log('randomCell =', randomCell);
        //console.log('columnCount =', columnCount);
        //console.log('rowCount =', rowCount);
        //console.log('cellSpriteCount =', cellSpriteCount);
        if (cellSpriteCount < limitPerCell){
            spriteGrid.columnCounts[randomColumn] = columnCount + 1;
            spriteGrid.rowCounts[randomRow] = rowCount + 1;
            spriteGrid.gridCounts[randomCell] = cellSpriteCount + 1;
            return [randomColumn, randomRow];
        } else {
            return thisReadyRoom.getRandColRow(limitPerCell * 2);
        }
    }

    // Define a function for getting the offset values for a given column and row given defined offsets in columnOffsets and rowOffsets
    thisReadyRoom.getColRowCenter = function(thisColumn, thisRow){
        var spriteGrid = thisReadyRoomConfig.spriteGrid;
        var thisColumnOffset = spriteGrid.columnOffsets[thisColumn];
        var thisRowOffset = spriteGrid.rowOffsets[thisRow];
        var thisColumnOffsetCenter = thisColumnOffset - (spriteGrid.colWidth / 2);
        var thisRowOffsetCenter = thisRowOffset - (spriteGrid.rowHeight / 2);
        return [thisColumnOffsetCenter, thisRowOffsetCenter];
    }


    // Define a function that will return a new position for the current sprite based on the nearby sprite
    thisReadyRoom.getRelativePositionChange = function(sourceSprite, targetSprite, direction, distancePercent, spriteBounds, axisFactor) {
        if (!spriteBounds || typeof spriteBounds === 'undefined'){ spriteBounds = { minX: 0, maxX: 100, minY: 0, maxY: 100 }; }
        if (!axisFactor || typeof axisFactor === 'undefined'){ axisFactor = 0.5; }

        var newPosition = sourceSprite.position.slice();
        var xDiff = targetSprite.position[0] - sourceSprite.position[0];
        var yDiff = (targetSprite.position[1] - sourceSprite.position[1]) * axisFactor;  // Scale the Y-axis distance

        var newX = direction === 'closer' ? xDiff : -xDiff;
        var newY = direction === 'closer' ? yDiff : -yDiff;

        // Calculate tentative new position
        var tentativeX = newPosition[0] + Math.ceil(newX * distancePercent);
        var tentativeY = newPosition[1] + Math.ceil(newY * distancePercent);

        // Respect sprite boundaries for X-axis
        newPosition[0] = Math.min(Math.max(tentativeX, spriteBounds.minX), spriteBounds.maxX);

        // Respect sprite boundaries for Y-axis
        newPosition[1] = Math.min(Math.max(tentativeY, spriteBounds.minY), spriteBounds.maxY);

        return newPosition;
    }

    // Define a function to find ready room sprites within a given radius that match some criteria
    thisReadyRoom.getNearbySprites = function(targetSprite, searchRadius, filterProperties){
        //console.log('thisReadyRoom.getNearbySprites(targetSprite:', typeof targetSprite, targetSprite, ', searchRadius:', typeof searchRadius, searchRadius, ', filterProperties:', typeof filterProperties, filterProperties, ')');
        if (typeof filterProperties !== 'object'){ filterProperties = false; }
        var nearbySprites = [];
        var targetX = targetSprite.position[0];
        var targetY = targetSprite.position[1];
        var readyRoomSpritesIndex = thisReadyRoomConfig.spritesIndex;
        var readyRoomSpritesIndexTokens = Object.keys(readyRoomSpritesIndex);
        //console.log('nearbySprites =', typeof nearbySprites, nearbySprites);
        //console.log('targetX =', typeof targetX, targetX);
        //console.log('targetY =', typeof targetY, targetY);
        //console.log('readyRoomSpritesIndex =', typeof readyRoomSpritesIndex, readyRoomSpritesIndex);
        //console.log('readyRoomSpritesIndexTokens =', typeof readyRoomSpritesIndexTokens, readyRoomSpritesIndexTokens);
        for (i = 0; i < readyRoomSpritesIndexTokens.length; i++) {
            //console.log('for (i = ', i, ';){ ... }');
            var spriteToken = readyRoomSpritesIndexTokens[i];
            var sprite = readyRoomSpritesIndex[spriteToken];
            //console.log('spriteToken =', typeof spriteToken, spriteToken);
            //console.log('sprite =', typeof sprite, sprite);
            if (!sprite.opacity){ continue; }
            //console.log('targetX =', typeof targetX, targetX);
            //console.log('targetY =', typeof targetY, targetY);
            // Skip the target sprite
            //console.log('check spriteToken(', spriteToken, ') === targetSprite.token(', targetSprite.token, ')');
            if (spriteToken === targetSprite.token){ continue; }
            var spriteX = sprite.position[0];
            var spriteY = sprite.position[1];
            //console.log('spriteX =', typeof spriteX, spriteX);
            //console.log('spriteY =', typeof spriteY, spriteY);
            // Calculate the distance
            var distance = calculateDistance(targetX, targetY, spriteX, spriteY);
            //console.log('distance =', typeof distance, distance);
            // Check if within radius and meets other criteria
            var meetsCriteria = false;
            //console.log('var meetsCriteria;');
            if (distance <= searchRadius){
                //console.log('passed check: distance <= searchRadius');
                var otherCriteria = false;
                if (filterProperties === false){
                    //console.log('bypass filters via: filterProperties === false');
                    meetsCriteria = true;
                    }
                else if (otherCriteria){ // check filterProperties here
                    //console.log('passed check: otherCriteria ??? ');
                    meetsCriteria = true;
                    }
                }
            //console.log('meetsCriteria =', typeof meetsCriteria, meetsCriteria);
            // If this sprite met the criteria, add it to the list
            if (meetsCriteria) {
                //console.log('nearbySprites.push(sprite);');
                nearbySprites.push({
                    sprite: sprite,
                    distance: distance
                    });
                }
            }
        //console.log('nearbySprites =', typeof nearbySprites, nearbySprites);
        return nearbySprites;
    }

    // Define a function for synthetically determining a given robot's affinity towards certain animals
    thisReadyRoom.getAnimalAffinities = function(robotInfo, animalTokens){

        // If the animal affinity wasn't provided, defined them all
        if (!animalTokens || typeof animalTokens !== 'array'){ animalTokens = ['dog', 'cat', 'bird', 'rodent']; }

        // Extract the stats from the robotInfo object
        var energyBase = robotInfo.energyBase;
        var attackBase = robotInfo.attackBase;
        var defenseBase = robotInfo.defenseBase;
        var speedBase = robotInfo.speedBase;
        var totalBase = energyBase + attackBase + defenseBase + speedBase;

        // Define an array to hold ranked animal tokens and the index of scores
        var animalTokensRanked = [];
        var animalScoresIndex = {'dog': 1, 'cat': 1, 'bird': 1, 'rodent': 1};
        var animalScoresIndexTokens = Object.keys(animalScoresIndex);

        // Use the existing stats to craft more abstract values we can use for pet preferences
        var baseHardyValue = (energyBase + defenseBase) / 2;
        var baseHunterValue = (attackBase + speedBase) / 2;
        var baseCombatValue = (attackBase + defenseBase) / 2;
        var baseTravelValue = (energyBase + speedBase) / 2;

        // Compare the abstract values to grant positive animal affinity points for each animal
        if (baseHardyValue > baseHunterValue){ animalScoresIndex['dog'] += (baseHardyValue / totalBase); }
        if (baseHunterValue > baseHardyValue){ animalScoresIndex['cat'] += (baseHunterValue / totalBase); }
        if (baseCombatValue > baseTravelValue){ animalScoresIndex['bird'] += (baseCombatValue / totalBase); }
        if (baseTravelValue > baseCombatValue){ animalScoresIndex['rodent'] += (baseTravelValue / totalBase); }

        // Compare the abstract values again in a different way to grant negative animal affinity points for each animal type
        if (baseHunterValue > baseHardyValue){ animalScoresIndex['dog'] -= (baseHunterValue / totalBase / totalBase); }
        if (baseHardyValue > baseHunterValue){ animalScoresIndex['cat'] -= (baseHardyValue / totalBase / totalBase); }
        if (baseTravelValue > baseCombatValue){ animalScoresIndex['bird'] -= (baseTravelValue / totalBase / totalBase); }
        if (baseCombatValue > baseTravelValue){ animalScoresIndex['rodent'] -= (baseCombatValue / totalBase / totalBase); }


        // Extract the animal tokens and then sort them by their score values above
        animalTokensRanked = Object.keys(animalScoresIndex);
        animalTokensRanked = animalTokensRanked.sort(function(a,b){
            if (animalScoresIndex[b] === animalScoresIndex[a]){ return 0; }
            return animalScoresIndex[b] < animalScoresIndex[a] ? -1 : 1;
            });

        // Determine the best ranked and the worst ranked given above (in cases of duplicates, use the order defined in animalScoresIndexTokens)
        var bestMatch = animalTokensRanked[0];
        var worstMatch = animalTokensRanked[animalTokensRanked.length - 1];

        // Return the index of animal affinity scores
        var animalAffinities = {
            ranked: animalTokensRanked,
            scores: animalScoresIndex,
            best: bestMatch,
            worst: worstMatch
            };
        //console.log('robotInfo.token =', robotInfo.token, robotInfo);
        //console.log('animalAffinities =', animalAffinities);
        //console.log('animalTokensRanked =', animalTokensRanked);
        //console.log('animalScoresIndex =', animalScoresIndex);
        return animalAffinities;

    }

})();
