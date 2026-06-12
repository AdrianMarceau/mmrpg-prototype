
// -- WORLD ROBOT METHODS -- //

// Quick function for settings a robot's current energy amount to a specific value but without all the effects
function setRobotEnergy(robotString, newEnergy){
    //console.log('%c' + 'mmrpgWorldMap.setRobotEnergy(' + robotString + ', ' + newEnergy + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('setRobotEnergy() missing required robotString!'); return false; }
    if (typeof newEnergy !== 'number' || isNaN(newEnergy) || newEnergy < 0){ console.error('setRobotEnergy() missing or invalid newEnergy!'); return false; }
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('setRobotEnergy() could not find robot info for robot ' + robotString + '!'); return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('setRobotEnergy() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    let $robotEnergyGuage = $('.guage.energy', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('setRobotEnergy() could not find icon sprite for robot ' + robotString + '!'); return false; }
    if (!$robotEnergyGuage || !$robotEnergyGuage.length){ console.warn('setRobotEnergy() could not find energy guage for robot ' + robotString + '!'); return false; }
    // Collect the current energy value for this robot
    let wasDisabled = robotInfo.energy === 0 ? true : false; // was this robot disabled?
    let currentEnergy = robotInfo.energy || 0;
    let maxEnergy = robotInfo.energyMax || 0;
    //console.log('-> currentEnergy =', currentEnergy);
    //console.log('-> maxEnergy =', maxEnergy);
    //console.log('-> newEnergy =', newEnergy);
    // If the new and old energy values are the same, do nothing
    if (newEnergy === currentEnergy){
        //console.log('setRobotEnergy() called but energy values are the same, nothing changed!');
        return true;
        }
    // Update the robot info with the new energy value
    robotInfo.energy = Math.min(newEnergy, maxEnergy);
    robotInfo.energyPercent = _self.getRoundedPercent(robotInfo.energy, robotInfo.energyMax);
    robotInfo.energyRating = _self.getRatingToken(robotInfo.energyPercent);
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
    // Update the overview with any changes to the status
    if (robotInfo.energy > 0){
        robotInfo.disabled = false;
        $robotOverview.removeClass('disabled');
        } else {
        robotInfo.disabled = true;
        $robotOverview.addClass('disabled');
        }
    $robotOverview.attr('data-status', robotInfo.energyRating+'-energy');
    // Update this robot's sprite on the actual overworld too
    let $teamSprites = _elements.teamSprites;
    let $robotSprite = $teamSprites.filter('.sprite[data-token="' + robotToken + '"]');
    if (robotInfo.energy > 0){ $robotSprite.removeClass('disabled').attr('data-frame', '08'); }
    else { $robotSprite.addClass('disabled'); }
    // Update the robot's icon sprite with a new frame matching its new energy value
    let robotEnergyFrame = _self.getRobotEnergyFrame(robotInfo.energyRating);
    $robotIconSprite.attr('data-frame', robotEnergyFrame);
    // Update the energy guage title and bar within with the new energy value
    $robotEnergyGuage.attr('title', robotInfo.energy + '/' + maxEnergy + ' LE (' + robotInfo.energyPercent + '%)');
    $('> i', $robotEnergyGuage).css({width: robotInfo.energyPercent + '%'});
    // Return true on success
    return true;
    }
// Quick function for restoring a robot's energy (if available) by a specific amount (or all if === true)
function restoreRobotEnergy(robotString, restoreAmount, playSound, playAnimation, showMessage){
    //console.log('%c' + 'mmrpgWorldMap.restoreRobotEnergy(' + robotString + ', ' + restoreAmount + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('restoreRobotEnergy() missing required robotString!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
    if (typeof showMessage !== 'boolean'){ showMessage = true; } // default to true if not provided

    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;

    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('restoreRobotEnergy() could not find robot info for robot ' + robotString + '!'); return false; }
    let playerString = _worldPlayer.id + '_' + _worldPlayer.token;

    // Collect references to canvas map sprites for overworld animations
    let $canvasRobotSprite = $('.sprite[data-robot="' + robotString + '"]', _elements.canvasMap);
    let $canvasPlayerSprite = $('.sprite[data-player="' + playerString + '"]', _elements.canvasMap);

    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('restoreRobotEnergy() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    let $robotEnergyGuage = $('.guage.energy', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('restoreRobotEnergy() could not find icon sprite for robot ' + robotString + '!'); return false; }
    if (!$robotEnergyGuage || !$robotEnergyGuage.length){ console.warn('restoreRobotEnergy() could not find energy guage for robot ' + robotString + '!'); return false; }

    // Collect the current energy value for this robot
    let wasDisabled = robotInfo.energy === 0 ? true : false; // was this robot disabled?
    let currentEnergy = robotInfo.energy || 0;
    let maxEnergy = robotInfo.energyMax || 0;
    if (typeof restoreAmount === 'string' && restoreAmount.endsWith('%')) {
        restoreAmount = Math.ceil((parseFloat(restoreAmount) / 100) * maxEnergy);
    } else {
        restoreAmount = (typeof restoreAmount === 'number' ? restoreAmount : (restoreAmount === true ? true : 0));
    }
    //console.log('%c' + 'healing ' + restoreAmount + ' energy to ' + robotString + '(or ' + ((restoreAmount / maxEnergy) * 100) + ' percent)', 'color: green;');

    // If restoreAmount is true, restore all energy, otherwise restore the amount provided
    let newEnergy = 0;
    if (restoreAmount === true){ restoreAmount = (maxEnergy - currentEnergy); newEnergy = maxEnergy; }
    else if (typeof restoreAmount === 'number' && restoreAmount > 0){ newEnergy = Math.min(currentEnergy + restoreAmount, maxEnergy); }

    // If the new and old energy values are the same, do nothing
    if (newEnergy === currentEnergy){
        //console.log('restoreRobotEnergy() called but energy values are the same, nothing changed!');
        return false;
        }

    // Update state data immediately so memory registers the changes securely
    robotInfo.energy = newEnergy;
    robotInfo.energyPercent = _self.getRoundedPercent(robotInfo.energy, robotInfo.energyMax);
    robotInfo.energyRating = _self.getRatingToken(robotInfo.energyPercent);
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index

    // Sync UI, play audio, and execute canvas frame shifts after a 1-second delay (matches breakRobotStat timing)
    setTimeout(function(){
        // Update the overview with any changes to the status
        if (robotInfo.energy > 0){
            robotInfo.disabled = false;
            $robotOverview.removeClass('disabled');
            } else {
            robotInfo.disabled = true;
            $robotOverview.addClass('disabled');
            }
        $robotOverview.attr('data-status', robotInfo.energyRating+'-energy');

        // Update this robot's sprite on the actual overworld too
        let $teamSprites = _elements.teamSprites;
        let $robotSprite = $teamSprites.filter('.sprite[data-token="' + robotToken + '"]');
        if (robotInfo.energy > 0){ $robotSprite.removeClass('disabled').attr('data-frame', '08'); }
        else { $robotSprite.addClass('disabled'); }

        // Update the robot's icon sprite with a new frame matching its new energy value
        let robotEnergyFrame = _self.getRobotEnergyFrame(robotInfo.energyRating);
        $robotIconSprite.attr('data-frame', robotEnergyFrame);

        // Update the energy guage title and bar within with the new energy value
        $robotEnergyGuage.attr('title', newEnergy + '/' + maxEnergy + ' LE (' + robotInfo.energyPercent + '%)');
        $('> i', $robotEnergyGuage).removeClass().addClass(robotInfo.energyRating).css({width: robotInfo.energyPercent + '%'});

        // Trigger recovery sound effects and map canvas animations
        if (playSound){ _self.playSoundEffect('recovery-energy'); }
        if (playAnimation){
            $canvasRobotSprite.attr('data-frame', '07'); // robot cheer frame
            $canvasPlayerSprite.attr('data-frame', '04'); // player victory frame
            setTimeout(function(){
                $canvasRobotSprite.attr('data-frame', '00'); // reset to base idle
                $canvasPlayerSprite.attr('data-frame', '00'); // reset to base idle
                }, 600);
            }

        // Print a status message about the restore
        if (showMessage){
            let messageMarkup = [];
            let robotNameTextSpan = _self.getRobotNameSpan(robotToken);
            let restoreAmountTextSpan = _self.getCustomNameSpan(restoreAmount, 'none');
            messageMarkup.push(robotNameTextSpan + ' had ' + restoreAmountTextSpan + ' energy restored!');
            if (wasDisabled && !robotInfo.disabled){ messageMarkup.push(robotNameTextSpan + ' was revived!'); }
            _self.showWorldMessage(messageMarkup);
            }

    }, 1000);

    // Flash the sidebar panel immediately to give the player instant button-click/step responsive feedback
    $robotOverview.addClass('energy-restored life-energy-restored');
    setTimeout(function(){ $robotOverview.removeClass('life-energy-restored'); }, 2000);
    setTimeout(function(){ $robotOverview.removeClass('energy-restored'); }, 3000);

    // Trigger a save of the world state to persist this change
    _self.saveWorldState();

    // Return true on success
    return true;
    }
// Quick function for damaging a robot's energy (if available) by a specific amount (or all if === true)
function damageRobotEnergy(robotString, damageAmount, playSound, playAnimation, showMessage){
    //console.log('%c' + 'mmrpgWorldMap.damageRobotEnergy(' + robotString + ', ' + damageAmount + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('damageRobotEnergy() missing required robotString!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
    if (typeof showMessage !== 'boolean'){ showMessage = true; } // default to true if not provided

    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;

    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('damageRobotEnergy() could not find robot info for robot ' + robotString + '!'); return false; }
    let playerString = _worldPlayer.id + '_' + _worldPlayer.token;

    // Collect references to canvas map sprites for overworld animations
    let $canvasRobotSprite = $('.sprite[data-robot="' + robotString + '"]', _elements.canvasMap);
    let $canvasPlayerSprite = $('.sprite[data-player="' + playerString + '"]', _elements.canvasMap);

    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('damageRobotEnergy() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    let $robotEnergyGuage = $('.guage.energy', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('damageRobotEnergy() could not find icon sprite for robot ' + robotString + '!'); return false; }
    if (!$robotEnergyGuage || !$robotEnergyGuage.length){ console.warn('damageRobotEnergy() could not find energy guage for robot ' + robotString + '!'); return false; }

    // Collect the current energy value for this robot
    let wasDisabled = robotInfo.energy === 0 ? true : false; // was this robot disabled?
    let currentEnergy = robotInfo.energy || 0;
    let maxEnergy = robotInfo.energyMax || 0;
    if (typeof damageAmount === 'string' && damageAmount.endsWith('%')) {
        damageAmount = Math.ceil((parseFloat(damageAmount) / 100) * maxEnergy);
    } else {
        damageAmount = (typeof damageAmount === 'number' ? damageAmount : (damageAmount === true ? true : 0));
    }
    //console.log('%c' + 'dealing ' + damageAmount + ' damage to ' + robotString + '(or ' + ((damageAmount / maxEnergy) * 100) + ' percent)', 'color: red;');

    // If damageAmount is true, drop energy straight to 0, otherwise calculate floor
    let newEnergy = currentEnergy;
    if (damageAmount === true){ damageAmount = currentEnergy; newEnergy = 0; }
    else if (typeof damageAmount === 'number' && damageAmount > 0){ newEnergy = Math.max(0, currentEnergy - damageAmount); }

    // If the new and old energy values are the same, do nothing
    if (newEnergy === currentEnergy){
        //console.log('damageRobotEnergy() called but energy values are the same, nothing changed!');
        return false;
        }

    // Update state data immediately so memory registers the changes securely
    robotInfo.energy = newEnergy;
    robotInfo.energyPercent = _self.getRoundedPercent(robotInfo.energy, robotInfo.energyMax);
    robotInfo.energyRating = _self.getRatingToken(robotInfo.energyPercent);
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index

    // Sync UI, play audio, and execute canvas frame shifts after a 1-second delay (matches breakRobotStat timing)
    setTimeout(function(){

        // Update the overview with any changes to the status
        if (robotInfo.energy > 0){
            robotInfo.disabled = false;
            $robotOverview.removeClass('disabled');
            } else {
            robotInfo.disabled = true;
            $robotOverview.addClass('disabled');
            }
        $robotOverview.attr('data-status', robotInfo.energyRating+'-energy');

        // Update this robot's sprite on the actual overworld too
        let $teamSprites = _elements.teamSprites;
        let $robotSprite = $teamSprites.filter('.sprite[data-token="' + robotToken + '"]');
        if (robotInfo.energy > 0){ $robotSprite.removeClass('disabled').attr('data-frame', '08'); }
        else { $robotSprite.addClass('disabled'); }

        // Update the robot's icon sprite with a new frame matching its new energy value
        let robotEnergyFrame = _self.getRobotEnergyFrame(robotInfo.energyRating);
        $robotIconSprite.attr('data-frame', robotEnergyFrame);

        // Update the energy guage title and bar within with the new energy value
        $robotEnergyGuage.attr('title', newEnergy + '/' + maxEnergy + ' LE (' + robotInfo.energyPercent + '%)');
        $('> i', $robotEnergyGuage).removeClass().addClass(robotInfo.energyRating).css({width: robotInfo.energyPercent + '%'});

        // Trigger damage sound effects and map canvas animations
        if (playSound){ _self.playSoundEffect('damage-reverb'); }
        if (playAnimation){
            $canvasRobotSprite.attr('data-frame', '09'); // robot flinch frame
            $canvasPlayerSprite.attr('data-frame', '05'); // player flinch frame
            setTimeout(function(){
                $canvasRobotSprite.attr('data-frame', '00'); // reset to base idle
                $canvasPlayerSprite.attr('data-frame', '00'); // reset to base idle
                }, 600);
            }

        // Print a status message about the damage
        if (showMessage){
            let messageMarkup = [];
            let robotNameTextSpan = _self.getRobotNameSpan(robotToken);
            let damageAmountTextSpan = _self.getCustomNameSpan(damageAmount, 'none');
            messageMarkup.push(robotNameTextSpan + ' took ' + damageAmountTextSpan + ' energy damage!');
            if (!wasDisabled && robotInfo.disabled){ messageMarkup.push(robotNameTextSpan + ' was disabled!'); }
            _self.showWorldMessage(messageMarkup);
            }

    }, 1000);

    // Flash the sidebar panel immediately to give the player instant button-click/step responsive feedback
    $robotOverview.addClass('energy-damaged life-energy-damaged');
    setTimeout(function(){ $robotOverview.removeClass('life-energy-damaged'); }, 2000);
    setTimeout(function(){ $robotOverview.removeClass('energy-damaged'); }, 3000);

    // Trigger a save of the world state to persist this change
    _self.saveWorldState();

    // Return true on success
    return true;
    }

// Quick function for settings a robot's current weapons amount to a specific value but without all the effects
function setRobotWeapons(robotString, newEnergy){
    //console.log('%c' + 'mmrpgWorldMap.setRobotWeapons(' + robotString + ', ' + newEnergy + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('setRobotWeapons() missing required robotString!'); return false; }
    if (typeof newEnergy !== 'number' || isNaN(newEnergy) || newEnergy < 0){ console.error('setRobotWeapons() missing or invalid newEnergy!'); return false; }
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('setRobotWeapons() could not find robot info for robot ' + robotString + '!'); return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('setRobotWeapons() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    let $robotWeaponsGuage = $('.guage.weapons', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('setRobotWeapons() could not find icon sprite for robot ' + robotString + '!'); return false; }
    if (!$robotWeaponsGuage || !$robotWeaponsGuage.length){ console.warn('setRobotWeapons() could not find weapons guage for robot ' + robotString + '!'); return false; }
    // Collect the current weapons value for this robot
    let currentWeapons = robotInfo.weapons || 0;
    let maxWeapons = robotInfo.weaponsMax || 0;
    //console.log('-> currentWeapons =', currentWeapons);
    //console.log('-> maxWeapons =', maxWeapons);
    //console.log('-> newEnergy =', newEnergy);
    // If the new and old weapons values are the same, do nothing
    if (newEnergy === currentWeapons){
        //console.log('setRobotWeapons() called but weapons values are the same, nothing changed!');
        return true;
        }
    // Update the robot info with the new weapons value
    robotInfo.weapons = Math.min(newEnergy, maxWeapons);
    robotInfo.weaponsPercent = Math.floor((robotInfo.weapons / robotInfo.weaponsMax) * 100);
    robotInfo.weaponsRating = _self.getRatingToken(robotInfo.weaponsPercent);
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
    // Update the weapons guage title and bar within with the new weapons value
    $robotWeaponsGuage.attr('title', robotInfo.weapons + '/' + maxWeapons + ' WE (' + robotInfo.weaponsPercent + '%)');
    $('> i', $robotWeaponsGuage).css({width: robotInfo.weaponsPercent + '%'});
    // Return true on success
    return true;
    }
// Quick function for restoring a robot's weapons (if available) by a specific amount (or all if === true)
function restoreRobotWeapons(robotString, restoreAmount, playSound){
    //console.log('%c' + 'mmrpgWorldMap.restoreRobotWeapons(' + robotString + ', ' + restoreAmount + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('restoreRobotWeapons() missing required robotString!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    // If restoreAmount is true, restore all weapons, otherwise restore the amount provided
    restoreAmount = (typeof restoreAmount === 'number' ? restoreAmount : (restoreAmount === true ? true : 0));
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('restoreRobotWeapons() could not find robot info for robot ' + robotString + '!'); return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('restoreRobotEnergy() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    let $robotWeaponsGuage = $('.guage.weapons', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('restoreRobotWeapons() could not find icon sprite for robot ' + robotString + '!'); return false; }
    if (!$robotWeaponsGuage || !$robotWeaponsGuage.length){ console.warn('restoreRobotWeapons() could not find weapons guage for robot ' + robotString + '!'); return false; }
    // Collect the current weapons value for this robot
    let currentWeapons = robotInfo.weapons || 0;
    let maxWeapons = robotInfo.weaponsMax || 0;
    //console.log('-> currentWeapons =', currentWeapons);
    //console.log('-> maxWeapons =', maxWeapons);
    //console.log('-> restoreAmount =', restoreAmount);
    // If restoreAmount is true, restore all weapons, otherwise restore the amount provided
    let newWeapons = 0;
    if (restoreAmount === true){ newWeapons = maxWeapons; }
    else if (typeof restoreAmount === 'number' && restoreAmount > 0){ newWeapons = Math.min(currentWeapons + restoreAmount, maxWeapons); }
    //console.log('-> newWeapons =', newWeapons);
    // If the new and old weapons values are the same, do nothing
    if (newWeapons === currentWeapons){
        //console.log('restoreRobotWeapons() called but weapons values are the same, nothing changed!');
        return true;
        }
    // Update the robot info with the new weapons value
    robotInfo.weapons = newWeapons;
    robotInfo.weaponsPercent = Math.floor((robotInfo.weapons / robotInfo.weaponsMax) * 100);
    robotInfo.weaponsRating = _self.getRatingToken(robotInfo.weaponsPercent);
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
    // Update the weapons guage title and bar within with the new weapons value
    $robotWeaponsGuage.attr('title', newWeapons + '/' + maxWeapons + ' WE (' + robotInfo.weaponsPercent + '%)');
    $('> i', $robotWeaponsGuage).css({width: robotInfo.weaponsPercent + '%'}).removeClass().addClass(robotInfo.weaponsRating);
    // Add a restored class to this robot to show it being effected by the action
    if (playSound){ _self.playSoundEffect('recovery-weapons'); }
    $robotOverview.addClass('energy-restored weapon-energy-restored');
    setTimeout(function(){ $robotOverview.removeClass('weapon-energy-restored'); }, 2000);
    setTimeout(function(){ $robotOverview.removeClass('energy-restored'); }, 3000);
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }

// Quick function for resetting a robot's stat mods for a given stat back to zero
function resetRobotStat(robotString, statToken, playSound){
    //console.log('%c' + 'mmrpgWorldMap.resetRobotStat(robot:' + robotString + ', stat:' + statToken + ', sound:' + playSound + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('resetRobotStat() missing required robotString!'); return false; }
    if (!statToken || typeof statToken !== 'string' || !statToken.length){ console.error('resetRobotStat() missing required statToken!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('resetRobotStat() could not find robot info for robot ' + robotString + '!'); return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // If this robot does not have any relevant mods to reset, return now
    let statModKey = statToken + 'Mods';
    let statModKeys = ['attackMods', 'defenseMods', 'speedMods'];
    if (statModKeys.indexOf(statModKey) === -1){ return false; }
    if (!robotInfo[statModKey]){ return true; }
    //console.log('-> looks like we can reset');
    let robotHasMods = function(){ return (parseInt(robotInfo[statModKeys[0]]) + parseInt(robotInfo[statModKeys[1]]) + parseInt(robotInfo[statModKeys[2]])) > 0 ? true : false; };
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('resetRobotStat() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('resetRobotStat() could not find icon sprite for robot ' + robotString + '!'); return false; }
    let $robotStatMods = $('.statmods', $robotOverview);
    if (!$robotStatMods || !$robotStatMods.length){ console.warn('resetRobotStat() could not find statmods for robot ' + robotString + '!'); return false; }
    let $robotStatModDiv = $('.mod.'+statToken, $robotStatMods);
    if (!$robotStatModDiv || !$robotStatModDiv.length){ console.warn('resetRobotStat() could not find mod.'+statToken+' for robot ' + robotString + '!'); return false; }
    // Update the robot info with the new stat-mod value
    let hadModsThen = robotHasMods();
    robotInfo[statModKey] = 0;
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
    // Update the overview with any changes to the status
    setTimeout(function(){
        if (playSound){ _self.playSoundEffect('recovery-stats'); }
        $robotStatModDiv.remove();
        let hasModsNow = robotHasMods();
        if (hasModsNow){ $robotOverview.addClass('hasmods'); }
        else { $robotOverview.removeClass('hasmods'); $robotStatMods.remove(); }
        }, 1000); // minor delay to sync with animation
    // Add a reset class to this robot to show it being effected by the action
    $robotOverview.addClass('stat-reset ' + statToken + '-stat-reset');
    setTimeout(function(){ $robotOverview.removeClass(statToken + '-stat-reset'); }, 2000);
    setTimeout(function(){ $robotOverview.removeClass('stat-reset'); }, 3000);
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }
// Define some quick alias functions for the above (attack, defense, and speed varieties)
function resetRobotAttack(robotString, playSound){
    //console.log('%c' + 'mmrpgWorldMap.resetRobotAttack(robot:' + robotString + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.resetRobotStat(robotString, 'attack', playSound);
    }
function resetRobotDefense(robotString, playSound){
    //console.log('%c' + 'mmrpgWorldMap.resetRobotDefense(robot:' + robotString + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.resetRobotStat(robotString, 'defense', playSound);
    }
function resetRobotSpeed(robotString, playSound){
    //console.log('%c' + 'mmrpgWorldMap.resetRobotSpeed(robot:' + robotString + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.resetRobotStat(robotString, 'speed', playSound);
    }

// Quick function for boosting (incrementing) a given robots stat by a specific amount (up to max of +5)
function boostRobotStat(robotString, statToken, boostAmount, playSound){
    //console.log('%c' + 'mmrpgWorldMap.boostRobotStat(robot:' + robotString + ', stat:' + statToken + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('boostRobotStat() missing required robotString!'); return false; }
    if (!statToken || typeof statToken !== 'string' || !statToken.length){ console.error('boostRobotStat() missing required statToken!'); return false; }
    if (typeof boostAmount !== 'number' || isNaN(boostAmount) || boostAmount < 1){ console.error('boostRobotStat() missing or invalid boostAmount!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('boostRobotStat() could not find robot info for robot ' + robotString + '!'); return false; }
    if (robotInfo.disabled === true){ return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // If this robot is already at the max value for stat mods, return now
    let statDir = 'up';
    let statName = statToken[0].toUpperCase() + statToken.slice(1);
    let statBoostAmount = Math.abs(boostAmount);
    let statModMax = _config.robotStatModMax; //5;
    let statModKey = statToken + 'Mods';
    let statModKeys = ['attackMods', 'defenseMods', 'speedMods'];
    //console.log('-> statModKey =', statModKey);
    //console.log('-> robotInfo[statModKey] =', robotInfo[statModKey]);
    if (statModKeys.indexOf(statModKey) === -1){ return false; }
    if (robotInfo[statModKey] && robotInfo[statModKey] >= statModMax){ return false; }
    //console.log('-> looks like we can boost ' + statToken + ' for ' + robotToken + '!');
    let robotHasMods = function(){ return (parseInt(robotInfo[statModKeys[0]]) + parseInt(robotInfo[statModKeys[1]]) + parseInt(robotInfo[statModKeys[2]])) !== 0 ? true : false; };
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('boostRobotStat() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('boostRobotStat() could not find icon sprite for robot ' + robotString + '!'); return false; }
    let $robotStatMods = $('.statmods', $robotOverview);
    let $robotStatModDiv = $('.mod.'+statToken, $robotStatMods);
    // Update the robot info with the new stat-mod value
    let hadModsThen = robotHasMods();
    let newStatModValue = (robotInfo[statModKey] || 0) + statBoostAmount;
    if (newStatModValue > statModMax){ newStatModValue = statModMax; } // cap at max value
    robotInfo[statModKey] = newStatModValue;
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
    // Create the statmods container if it does not already exist then append to container
    if (!$robotStatMods || !$robotStatMods.length){
        // create the statmods container if it doesn't exist
        $robotOverview.append('<div class="statmods"></div>');
        $robotStatMods = $('.statmods', $robotOverview);
        }
    // Update the arrows for this stat mod or create them if they do not already exist
    let arrowMarkup = '';
    let arrowCount = Math.abs(newStatModValue);
    let arrowDir = newStatModValue > 0 ? 'up' : 'down';
    for (let i = 0; i < arrowCount; i++){ arrowMarkup += '<i class="fa fas fa-caret-' + arrowDir + '"></i>'; }
    if (!$robotStatModDiv || !$robotStatModDiv.length){
        $robotStatMods.append('<div class="mod color ' + statToken + ' ' + arrowDir + '" title="' + statName + ' Mods"></div>');
        $robotStatModDiv = $('.mod.'+statToken, $robotStatMods);
        }
    // Update the overview with any changes to the status and the arrows we just created
    setTimeout(function(){
        if (playSound){ _self.playSoundEffect('recovery-stats'); }
        $robotStatModDiv.empty().append(arrowMarkup);
        $robotStatModDiv.removeClass('up down').addClass(arrowDir);
        let hasModsNow = robotHasMods();
        if (hasModsNow){ $robotOverview.addClass('hasmods'); }
        else { $robotOverview.removeClass('hasmods'); $robotStatMods.remove(); }
        }, 1000); // minor delay to sync with animation
    // Add a boost class to this robot to show it being effected by the action
    $robotOverview.addClass('stat-boosted ' + statToken + '-stat-boosted');
    setTimeout(function(){ $robotOverview.removeClass(statToken + '-stat-boosted'); }, 2000);
    setTimeout(function(){ $robotOverview.removeClass('stat-boosted'); }, 3000);
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }
// Define some quick alias functions for the above (attack, defense, and speed varieties)
function boostRobotAttack(robotString, boostAmount, playSound){
    //console.log('%c' + 'mmrpgWorldMap.boostRobotAttack(robot:' + robotString + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.boostRobotStat(robotString, 'attack', boostAmount, playSound);
    }
function boostRobotDefense(robotString, boostAmount, playSound){
    //console.log('%c' + 'mmrpgWorldMap.boostRobotDefense(robot:' + robotString + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.boostRobotStat(robotString, 'defense', boostAmount, playSound);
    }
function boostRobotSpeed(robotString, boostAmount, playSound){
    //console.log('%c' + 'mmrpgWorldMap.boostRobotSpeed(robot:' + robotString + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.boostRobotStat(robotString, 'speed', boostAmount, playSound);
    }

// Quick function for breaking (decrementing) a given robots stat by a specific amount (down to min of -5)
function breakRobotStat(robotString, statToken, breakAmount, playSound, playAnimation){
    //console.log('%c' + 'mmrpgWorldMap.breakRobotStat(robot:' + robotString + ', stat:' + statToken + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('breakRobotStat() missing required robotString!'); return false; }
    if (!statToken || typeof statToken !== 'string' || !statToken.length){ console.error('breakRobotStat() missing required statToken!'); return false; }
    if (typeof breakAmount !== 'number' || isNaN(breakAmount) || breakAmount < 1){ console.error('breakRobotStat() missing or invalid breakAmount!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('breakRobotStat() could not find robot info for robot ' + robotString + '!'); return false; }
    if (robotInfo.disabled === true){ return false; }
    let playerString = _worldPlayer.id + '_' + _worldPlayer.token;
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // If this robot is already at the min value for stat mods, return now
    let statDir = 'down';
    let statName = statToken[0].toUpperCase() + statToken.slice(1);
    let statBreakAmount = Math.abs(breakAmount);
    let statModMin = _config.robotStatModMin; //-5;
    let statModKey = statToken + 'Mods';
    let statModKeys = ['attackMods', 'defenseMods', 'speedMods'];
    //console.log('-> statModKey =', statModKey);
    //console.log('-> robotInfo[statModKey] =', robotInfo[statModKey]);
    if (statModKeys.indexOf(statModKey) === -1){ return false; }
    if (robotInfo[statModKey] && robotInfo[statModKey] <= statModMin){ return false; }
    //console.log('-> looks like we can break ' + statToken + ' for ' + robotToken + '!');
    let robotHasMods = function(){ return (parseInt(robotInfo[statModKeys[0]]) + parseInt(robotInfo[statModKeys[1]]) + parseInt(robotInfo[statModKeys[2]])) !== 0 ? true : false; };
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // Collect a reference to this robot's canvas sprite for later
    let $canvasRobotSprite = $('.sprite[data-robot="' + robotString + '"]', _elements.canvasMap);
    let $canvasPlayerSprite = $('.sprite[data-player="' + playerString + '"]', _elements.canvasMap);
    //console.log('$canvasRobotSprite =', $canvasRobotSprite);
    //console.log('$canvasPlayerSprite =', $canvasPlayerSprite);
    // Collect a reference to this robot's element in the overview panel
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', _elements.robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('breakRobotStat() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('breakRobotStat() could not find icon sprite for robot ' + robotString + '!'); return false; }
    let $robotStatMods = $('.statmods', $robotOverview);
    let $robotStatModDiv = $('.mod.'+statToken, $robotStatMods);
    // Update the robot info with the new stat-mod value
    let hadModsThen = robotHasMods();
    let newStatModValue = (robotInfo[statModKey] || 0) - statBreakAmount;
    if (newStatModValue < statModMin){ newStatModValue = statModMin; } // cap at min value
    robotInfo[statModKey] = newStatModValue;
    _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
    // Create the statmods container if it does not already exist then append to container
    if (!$robotStatMods || !$robotStatMods.length){
        // create the statmods container if it doesn't exist
        //console.log('-> create the statmods container as it does not exist');
        $robotOverview.append('<div class="statmods"></div>');
        $robotStatMods = $('.statmods', $robotOverview);
        }
    // Update the arrows for this stat mod or create them if they do not already exist
    let arrowMarkup = '';
    let arrowCount = Math.abs(newStatModValue);
    let arrowDir = newStatModValue > 0 ? 'up' : 'down';
    //console.log('-> arrowCount =', arrowCount);
    //console.log('-> arrowDir =', arrowDir);
    for (let i = 0; i < arrowCount; i++){ arrowMarkup += '<i class="fa fas fa-caret-' + arrowDir + '"></i>'; }
    //console.log('-> generated arrowMarkup =', arrowMarkup);
    if (!$robotStatModDiv || !$robotStatModDiv.length){
        //console.log('-> create the statmod arrow div as it does not exist');
        $robotStatMods.append('<div class="mod color ' + statToken + ' ' + arrowDir + '" title="' + statName + ' Mods"></div>');
        $robotStatModDiv = $('.mod.'+statToken, $robotStatMods);
        }
    // Update the overview with any changes to the status and the arrows we just created
    setTimeout(function(){
        if (playSound){ _self.playSoundEffect('damage-stats'); }
        if (playAnimation){
            $canvasRobotSprite.attr('data-frame', '09'); // robot damage
            $canvasPlayerSprite.attr('data-frame', '05'); // player damage
            setTimeout(function(){
                $canvasRobotSprite.attr('data-frame', '00'); // reset to base
                $canvasPlayerSprite.attr('data-frame', '00'); // reset to base
                }, 600);
            }
        $robotStatModDiv.empty().append(arrowMarkup);
        $robotStatModDiv.removeClass('up down').addClass(arrowDir);
        let hasModsNow = robotHasMods();
        if (hasModsNow){ $robotOverview.addClass('hasmods'); }
        else { $robotOverview.removeClass('hasmods'); $robotStatMods.remove(); }
        }, 1000); // minor delay to sync with animation
    // Add a break class to this robot to show it being effected by the action
    $robotOverview.addClass('stat-breaked ' + statToken + '-stat-breaked');
    setTimeout(function(){ $robotOverview.removeClass(statToken + '-stat-breaked'); }, 2000);
    setTimeout(function(){ $robotOverview.removeClass('stat-breaked'); }, 3000);
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }
// Define some quick alias functions for the above (attack, defense, and speed varieties)
function breakRobotAttack(robotString, breakAmount, playSound, playAnimation){
    //console.log('%c' + 'mmrpgWorldMap.breakRobotAttack(robot:' + robotString + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.breakRobotStat(robotString, 'attack', breakAmount, playSound, playAnimation);
    }
function breakRobotDefense(robotString, breakAmount, playSound, playAnimation){
    //console.log('%c' + 'mmrpgWorldMap.breakRobotDefense(robot:' + robotString + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.breakRobotStat(robotString, 'defense', breakAmount, playSound, playAnimation);
    }
function breakRobotSpeed(robotString, breakAmount, playSound, playAnimation){
    //console.log('%c' + 'mmrpgWorldMap.breakRobotSpeed(robot:' + robotString + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
    let _self = this; return _self.breakRobotStat(robotString, 'speed', breakAmount, playSound, playAnimation);
    }

// Quick function for giving a given robot a new hold item and then optionally playing a sound effect
function giveRobotItem(robotString, itemToken, playSound, playAnimation){
    //console.log('%c' + 'mmrpgWorldMap.giveRobotItem(robot:' + robotString + ', item:' + itemToken + ', sound:' + playSound + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('giveRobotItem() missing required robotString!'); return false; }
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('giveRobotItem() missing required itemToken!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _worldPlayerItems = _worldPlayer.items;
    let _mmrpgItemsIndex = _indexes.items;
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('giveRobotItem() could not find robot info for robot ' + robotString + '!'); return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // If this robot already has a hold item, return now
    if (robotInfo.item && robotInfo.item.length){
        console.warn('giveRobotItem() called but robot ' + robotString + ' already has an item!');
        return false;
        }
    // If the item token provided is not valid, return now
    let itemInfo = _mmrpgItemsIndex[itemToken] || false;
    if (!itemInfo){ console.error('giveRobotItem() could not find item info for item ' + itemToken + '!'); return false; }
    //console.log('-> itemInfo =', itemInfo);
    // If the item provided is not holdable, return now
    if (!_self.itemIsHoldable(itemToken)){
        console.error('giveRobotItem() cannot give non-holdable item ' + itemToken + ' to robot ' + robotString + '!');
        return false;
        }
    // Update the robot info with the new hold item
    // then re-sync the robot info with the index
    robotInfo.item = itemToken;
    if (itemToken.indexOf('-core') !== -1){
        let coreType = itemInfo.type;
        //console.log('equipping a core to this robot! itemToken =', itemToken);
        if (coreType && coreType !== 'empty'){
            //console.log('-> update compatibility for coreType =', coreType);
            let abilitiesViaItem = (function(robot, type){
                let abilitiesCompatible = [];
                let abilitiesIndex = _mmrpgAbilitiesIndex;
                let indexTokens = abilitiesIndex._indexTokens, indexIDs = abilitiesIndex._indexIDs;
                for (var i = 0; i < indexTokens.length; i++){
                    let token = indexTokens[i], info = abilitiesIndex[token];
                    //console.log('--> checking ' + token + ' w/ info =', info);
                    if (!info.flagComplete || !info.flagPublished || !info.flagUnlockable){ continue; }
                    if (info.class !== 'master'){ continue; }
                    //console.log('--> checking if ' + token + ' is ' + coreType + ' type ...');
                    if (info.type !== coreType && info.type2 !== coreType){ continue; }
                    abilitiesCompatible.push(info.id);
                    }
                return abilitiesCompatible;
                })(robotInfo, coreType);
            //console.log('-> abilitiesViaItem =', abilitiesViaItem);
            robotInfo.abilitiesViaItem = abilitiesViaItem;
            robotInfo.abilities = (function(currentAbilities, robotInfo){
                let filteredAbilities = [];
                let abilitiesCompatible = robotInfo.abilitiesCompatible || [];
                let abilitiesViaItem = robotInfo.abilitiesViaItem || [];
                for (var i = 0; i < currentAbilities.length; i++){
                    let id = currentAbilities[i], compatible = false;
                    if (abilitiesCompatible.indexOf(id) !== -1){ compatible = true; }
                    else if (abilitiesViaItem.indexOf(id) !== -1){ compatible = true; }
                    if (compatible){ filteredAbilities.push(id); }
                    }
                return filteredAbilities;
                })(robotInfo.abilities, robotInfo);
            }
        }
    _worldPlayerRobots[robotString] = robotInfo;
    // Add this item to the player's equipped items list
    if (typeof _worldPlayerItems[itemToken + '__equipped'] === 'undefined'){ _worldPlayerItems[itemToken + '__equipped'] = 0; }
    _worldPlayerItems[itemToken + '__equipped'] += 1;
    // Collect a reference to this robot's element in the overview panel
    let $robotsOverview = _elements.robotsOverview;
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', $robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('giveRobotItem() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite.robot', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('giveRobotItem() could not find icon sprite for robot ' + robotString + '!'); return false; }
    // Remove any old item sprite(s) already inside this robot's icon container
    $('.icon > .sprite.item', $robotOverview).remove();
    // Create the new item sprite(s) with the appriate classes and attributes
    let randDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
    let itemSpriteClasses = 'sprite item holding';
    let itemSpriteStyles = 'animation-delay: ' + randDelay + 's; ';
    let itemSpriteAttrs = 'data-sprite="item" data-token="' + itemToken + '" data-size="' + itemInfo.imageSize + '" data-dir="right" data-frame="00"';
    let itemSpriteMarkup = '<div class="' + itemSpriteClasses + '" style="' + itemSpriteStyles + '" ' + itemSpriteAttrs + '><span class="wrap"><i class="sprite"></i></span></div>';
    $robotOverview.find('.icon').append(itemSpriteMarkup);
    let $robotItemSprite = $('.icon > .sprite.item', $robotOverview);
    // Add a item-given class to this robot to show it being effected by the action
    if (playSound){ _self.playSoundEffect('get-item'); }
    if (playAnimation){ $robotOverview.addClass('item-given'); setTimeout(function(){ $robotOverview.removeClass('item-given'); }, 2000); }
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }
// Quick function for giving a given robot a new hold item and then optionally playing a sound effect
function takeRobotItem(robotString, playSound, playAnimation){
    //console.log('%c' + 'mmrpgWorldMap.takeRobotItem(robot:' + robotString + ', sound:' + playSound + ', animate:' + playAnimation + ')', 'color: magenta;');
    if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('takeRobotItem() missing required robotString!'); return false; }
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _worldPlayerItems = _worldPlayer.items;
    let _mmrpgItemsIndex = _indexes.items;
    // Break the robot sprite into ID and token and collect its info
    let robotId = parseInt(robotString.split('_')[0]) || false;
    let robotToken = robotString.split('_')[1] || false;
    let robotInfo = _worldPlayerRobots[robotString] || false;
    if (!robotInfo){ console.error('takeRobotItem() could not find robot info for robot ' + robotString + '!'); return false; }
    //console.log('-> robotId =', robotId);
    //console.log('-> robotToken =', robotToken);
    //console.log('-> robotInfo =', robotInfo);
    // If this robot doesn't have a hold item, return now
    if (!robotInfo.item || !robotInfo.item.length){
        console.warn('takeRobotItem() called but robot ' + robotString + ' does not have an item!');
        return false;
        }
    // If the item token provided is not valid, return now
    let itemToken = robotInfo.item;
    let itemInfo = _mmrpgItemsIndex[itemToken] || false;
    if (!itemInfo){ console.error('takeRobotItem() could not find item info for item ' + itemToken + '!'); return false; }
    //console.log('-> itemInfo =', itemInfo);
    // Update the robot info to remove hold item
    // then re-sync the robot info with the index
    robotInfo.item = '';
    robotInfo.abilitiesViaItem = [];
    robotInfo.abilities = (function(currentAbilities, robotInfo){
        let filteredAbilities = [];
        let abilitiesCompatible = robotInfo.abilitiesCompatible || [];
        let abilitiesViaItem = robotInfo.abilitiesViaItem || [];
        for (var i = 0; i < currentAbilities.length; i++){
            let id = currentAbilities[i], compatible = false;
            if (abilitiesCompatible.indexOf(id) !== -1){ compatible = true; }
            else if (abilitiesViaItem.indexOf(id) !== -1){ compatible = true; }
            if (compatible){ filteredAbilities.push(id); }
            }
        return filteredAbilities;
        })(robotInfo.abilities, robotInfo);
    _worldPlayerRobots[robotString] = robotInfo;
    // Add this item to the player's equipped items list
    if (typeof _worldPlayerItems[itemToken + '__equipped'] === 'undefined'){ _worldPlayerItems[itemToken + '__equipped'] = 1; }
    else if (_worldPlayerItems[itemToken + '__equipped'] < 1){ _worldPlayerItems[itemToken + '__equipped'] = 1; }
    _worldPlayerItems[itemToken + '__equipped'] -= 1;
    // Collect a reference to this robot's element in the overview panel
    let $robotsOverview = _elements.robotsOverview;
    let $robotOverview = $('.team-robot[data-robot="' + robotString + '"]', $robotsOverview);
    if (!$robotOverview || !$robotOverview.length){ console.warn('takeRobotItem() could not find overview for robot ' + robotString + '!'); return false; }
    let $robotIconSprite = $('.icon > .sprite.robot', $robotOverview);
    if (!$robotIconSprite || !$robotIconSprite.length){ console.warn('takeRobotItem() could not find icon sprite for robot ' + robotString + '!'); return false; }
    // Remove any old item sprite(s) already inside this robot's icon container
    $('.icon > .sprite.item', $robotOverview).remove();
    // Add a item-given class to this robot to show it being effected by the action
    if (playSound){ _self.playSoundEffect('bounce-sound'); }
    if (playAnimation){ $robotOverview.addClass('item-given'); setTimeout(function(){ $robotOverview.removeClass('item-given'); }, 2000); }
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }

// Define a function for calculating a robot's current stat value given its base and current mods/stages its been raised/lowered
function calculateRobotStat(baseValue, modValue){
    //console.log('%c' + 'mmrpgWorldMap.calculateRobotStat(base:' + baseValue + ', mod:' + modValue + ')', 'color: magenta;');
    const numerator = 2;
    const denominator = 2;
    if (!baseValue || isNaN(baseValue) || baseValue <= 0){ return 0; }
    if (!modValue || isNaN(modValue) || modValue === 0){ return baseValue; }
    let newValue = baseValue;
    if (modValue > 0){
        let newNumerator = numerator + modValue;
        //console.log('-> boosting stat via baseValue * (' + newNumerator + ' / ' + denominator + ')');
        newValue = Math.ceil(baseValue * (newNumerator / denominator));
        }
    else if (modValue < 0){
        let newDenominator = denominator + (modValue * -1);
        //console.log('-> breaking stat via baseValue * (' + numerator + ' / ' + newDenominator + ')');
        newValue = Math.ceil(baseValue * (numerator / newDenominator));
        }
    //console.log('-> newValue =', newValue);
    return newValue;
    }

// Define a quick function for getting the overview details for a given robot in the user's inventory
function getRobotDetailsForOverview(robotToken){
    //console.log('%c' + 'mmrpgWorldMap.getRobotDetailsForOverview(robot:' + robotToken + ')', 'color: magenta;');
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('getRobotDetailsForOverview() missing required robotToken!'); return ''; }

    // parse out the player-specific robot token was provided alongside an ID
    let playerRobotToken = false;
    if (robotToken.indexOf('_') !== -1){
        playerRobotToken = robotToken;
        robotToken = robotToken.split('_')[1];
        }

    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _mmrpgTypesIndex = _indexes.types;
    let _mmrpgRobotsIndex = _indexes.robots;
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    let _mmrpgItemsIndex = _indexes.items;
    let _mmrpgFieldsIndex = _indexes.fields;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _playerRobotsIndex = _config.playerRobotsIndex;
    let playerRobotInfo = false;
    let playerRobotsCurrent = _worldPlayer.team;
    if (typeof _worldPlayerRobots[playerRobotToken] !== 'undefined'){
        playerRobotInfo = _worldPlayerRobots[playerRobotToken];
        } else if (typeof _playerRobotsIndex[playerRobotToken] !== 'undefined'){
        playerRobotInfo = _self.getClonedObject(_playerRobotsIndex[playerRobotToken]);
        //_worldPlayerRobots[playerRobotToken] = playerRobotInfo; // removed: no need to save unless we actually swap
        } else {
        console.error('getRobotDetailsForOverview() could not find player robot info for token ' + playerRobotToken + '!');
        return false;
        }
    //console.log('--> _worldPlayer =', _worldPlayer);
    //console.log('--> _worldPlayerRobots =', _worldPlayerRobots);
    //console.log('--> playerRobotToken =', playerRobotToken);
    //console.log('--> playerRobotInfo =', playerRobotInfo);
    //console.log('--> playerRobotsCurrent =', playerRobotsCurrent);
    //console.log('--> playerRobotInfo.persona =', (playerRobotInfo ? playerRobotInfo.persona : 'N/A'));
    if (typeof _mmrpgRobotsIndex[robotToken] === 'undefined'){ console.error('getRobotDetailsForOverview() could not find robot in index for token ' + robotToken + '!'); return false; }
    let baseRobotIndexInfo = _mmrpgRobotsIndex[robotToken];
    let robotIndexInfo = playerRobotInfo && playerRobotInfo.persona ? _mmrpgRobotsIndex[playerRobotInfo.persona] : baseRobotIndexInfo;
    //console.log('--> baseRobotIndexInfo =', baseRobotIndexInfo);
    //console.log('--> robotIndexInfo =', robotIndexInfo);

    // Generate the markup, classes, styles, etc. that will make up the robot details
    let robotTitle = 'Robot Details';
    let robotKind = robotIndexInfo.class;
    let robotName = robotIndexInfo.name;
    let robotDescription = robotIndexInfo.description;
    let robotCore1 = robotIndexInfo.core || 'none';
    let robotCore2 = robotIndexInfo.core2 || false;
    let robotCoreName1 = robotIndexInfo.core ? _mmrpgTypesIndex[robotIndexInfo.core].name : 'Neutral';
    let robotCoreName2 = robotIndexInfo.core2 ? _mmrpgTypesIndex[robotIndexInfo.core2].name : false;
    let robotImage = robotIndexInfo.image || robotToken;
    let robotImageSize = robotIndexInfo.imageSize;
    let robotImageAlt = '';
    if (playerRobotInfo.name){ robotName = playerRobotInfo.name; }
    if (playerRobotInfo.image){ robotImage = playerRobotInfo.image; }
    if (robotImage.indexOf('_') !== -1){ robotImage = robotImage.split('_'); robotImageAlt = robotImage[1]; robotImage = robotImage[0]; }
    //console.log('--> robotKind =', robotKind);
    //console.log('--> robotName =', robotName);
    //console.log('--> robotDescription =', robotDescription);
    //console.log('--> robotCore1 =', robotCore1);
    //console.log('--> robotCore2 =', robotCore2);
    //console.log('--> robotCoreName1 =', robotCoreName1);
    //console.log('--> robotCoreName2 =', robotCoreName2);
    //console.log('--> robotImage =', robotImage);
    //console.log('--> robotImageSize =', robotImageSize);
    //console.log('--> robotImageAlt =', robotImageAlt);

    // Determine the icon to use based on the robot kind and format the text for display
    let robotKindIcon = 'dot-circle';
    let robotKindName = robotKind.charAt(0).toUpperCase() + robotKind.slice(1);
    if (robotKind === 'master'){ robotKindIcon = 'robot'; }
    else if (robotKind === 'mecha'){ robotKindIcon = 'ghost'; robotTitle = 'Mecha Details'; }
    else if (robotKind === 'boss'){ robotKindIcon = 'skull'; robotTitle = 'Bosss Details'; }
    //console.log('--> robotTitle =', robotTitle);
    //console.log('--> robotKindName =', robotKindName);
    //console.log('--> robotKindIcon =', robotKindIcon);

    // Generate the robot sprite that will be used in the details
    let randDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
    let robotSpriteClasses = 'sprite robot icon';
    let robotSpriteStyles = 'animation-delay: ' + randDelay + 's; ';
    let robotSpriteAttrs = 'data-sprite="robot" data-token="' + robotImage + '" data-alt="' + robotImageAlt + '" data-size="' + robotImageSize + '" data-dir="left" data-frame="00"';
    let robotSprite = '<div class="' + robotSpriteClasses + '" style="' + robotSpriteStyles + '" ' + robotSpriteAttrs + '><span class="wrap"><i class="sprite"></i></span></div>';
    //console.log('--> robotSpriteClasses =', robotSpriteClasses);
    //console.log('--> robotSpriteStyles =', robotSpriteStyles);
    //console.log('--> robotSpriteAttrs =', robotSpriteAttrs);
    //console.log('--> robotSprite =', robotSprite);

    // Collect details about the power level of this robot (if relevant)
    let robotLevel = playerRobotInfo.level || 0;
    let robotExperience = playerRobotInfo.experience || 0;
    let robotEnergy = playerRobotInfo.energy || 0;
    let robotEnergyMax = playerRobotInfo.energyMax || 0;
    let robotEnergyPercent = playerRobotInfo.energyPercent || 0;
    let robotEnergyRating = playerRobotInfo.energyRating || 0;
    let robotWeapons = playerRobotInfo.weapons || 0;
    let robotWeaponsMax = playerRobotInfo.weaponsMax || 0;
    let robotWeaponsPercent = playerRobotInfo.weaponsPercent || 0;
    let robotWeaponsRating = playerRobotInfo.weaponsRating || 0;
    let robotAttack = playerRobotInfo.attack || 0;
    let robotAttackMods = playerRobotInfo.attackMods || 0;
    let robotDefense = playerRobotInfo.defense || 0;
    let robotDefenseMods = playerRobotInfo.defenseMods || 0;
    let robotSpeed = playerRobotInfo.speed || 0;
    let robotSpeedMods = playerRobotInfo.speedMods || 0;
    let robotDisabled = playerRobotInfo.disabled === true ? true : false;
    let robotIsCurrent = playerRobotsCurrent.indexOf(playerRobotToken) !== -1 ? true : false;
    let robotItem = playerRobotInfo.item || '';
    let robotItemInfo = robotItem && typeof _mmrpgItemsIndex[robotItem] !== 'undefined' ? _mmrpgItemsIndex[robotItem] : false;
    let robotSupport = playerRobotInfo.support ? playerRobotInfo.support : (robotIndexInfo.support ? robotIndexInfo.support : '');
    let robotSupportInfo = robotSupport && typeof _mmrpgRobotsIndex[robotSupport] !== 'undefined' ? _mmrpgRobotsIndex[robotSupport] : false;
    let robotSupportEquipped = (function(info){
        let abilities = _mmrpgAbilitiesIndex, equipped = info.abilities || [];
        let required = ['mecha-support', 'mecha-assault', 'mecha-party', 'friend-share'];
        for (var i = 0; i < required.length; i++){ if (equipped.indexOf(abilities[required[i]].id) !== -1){ return true; } }
        return false;
        })(playerRobotInfo);
    //console.log('--> robotLevel =', robotLevel);
    //console.log('--> robotExperience =', robotExperience);
    //console.log('--> robotEnergy =', robotEnergy, '/', robotEnergyMax, '(', robotEnergyPercent, '% )');
    //console.log('--> robotWeapons =', robotWeapons, '/', robotWeaponsMax, '(', robotWeaponsPercent, '% )');
    //console.log('--> robotAttack =', robotAttack, (robotAttackMods > 0 ? '+' + robotAttackMods : robotAttackMods));
    //console.log('--> robotDefense =', robotDefense, (robotDefenseMods > 0 ? '+' + robotDefenseMods : robotDefenseMods));
    //console.log('--> robotSpeed =', robotSpeed, (robotSpeedMods > 0 ? '+' + robotSpeedMods : robotSpeedMods));
    //console.log('--> robotItem =', robotItem);
    //console.log('--> robotItemInfo =', robotItemInfo);
    //console.log('--> robotSupport =', robotSupport);
    //console.log('--> robotSupportInfo =', robotSupportInfo);
    //console.log('--> robotSupportEquipped =', robotSupportEquipped);
    //console.log('--> robotDisabled =', robotDisabled);
    //console.log('--> robotIsCurrent =', robotIsCurrent);

    // Generate type-related markup and spans for use later
    let statTokens = ['attack', 'defense', 'speed'];
    let statTokenCodes = ['ATK', 'DEF', 'SPD'];
    let weaknessTokens = ['weaknesses', 'resistances', 'affinities', 'immunities'];
    let robotCoreName = (robotCoreName1 + (robotCoreName2 ? (' / ' + robotCoreName2) : '')) + ' Core';
    let robotCoreClasses = (robotCore2 && robotCore1 === 'none' ? robotCore2 : (robotCore1 + (robotCore2 ? '_' + robotCore2 : '')));
    if (!robotIsCurrent){ robotCoreClasses += ' inactive'; }
    //console.log('--> statTokens =', statTokens);
    //console.log('--> robotCoreName =', robotCoreName);
    //console.log('--> robotCoreClasses =', robotCoreClasses);

    // Start generating the robot details object for the overview
    let robotDetailsObject = {};
    robotDetailsObject.title = robotTitle;
    robotDetailsObject.image = robotSprite;
    robotDetailsObject.name = robotName;
    robotDetailsObject.level = robotLevel;
    robotDetailsObject.experience = robotExperience;
    robotDetailsObject.description = robotDescription;
    robotDetailsObject.disabled = robotDisabled;
    robotDetailsObject.classIcon = robotKindIcon;
    robotDetailsObject.typeName = robotCoreName;
    robotDetailsObject.typeClasses = robotCoreClasses;
    robotDetailsObject.infoLines = [];

    // LIFE ENERGY
    let lifeEnergyLine = { classes: 'life-energy', label: 'Life Energy:', guages: [], values: [] }; {
        lifeEnergyLine.guages.push({ type: 'energy', percent: robotEnergyPercent });
        lifeEnergyLine.values.push({ value: robotEnergy, valueClasses: 'current' });
        lifeEnergyLine.values.push({ value: '/', valueClasses: 'separator' });
        lifeEnergyLine.values.push({ value: robotEnergyMax, valueClasses: 'max' });
        lifeEnergyLine.values.push({ value: 'LE', valueClasses: 'unit' });
        lifeEnergyLine.values.push({ value: '(' + robotEnergyPercent + '%)', valueClasses: 'percent' });
        }
    robotDetailsObject.infoLines.push(lifeEnergyLine);

    // WEAPON ENERGY
    let weaponEnergyLine = { classes: 'weapon-energy', label: 'Weapon Energy:', guages: [], values: [] }; {
        weaponEnergyLine.guages.push({ type: 'weapons', percent: robotWeaponsPercent });
        weaponEnergyLine.values.push({ value: robotWeapons, valueClasses: 'current' });
        weaponEnergyLine.values.push({ value: '/', valueClasses: 'separator' });
        weaponEnergyLine.values.push({ value: robotWeaponsMax, valueClasses: 'max' });
        weaponEnergyLine.values.push({ value: 'WE', valueClasses: 'unit' });
        weaponEnergyLine.values.push({ value: '(' + robotWeaponsPercent + '%)', valueClasses: 'percent' });
        }
    robotDetailsObject.infoLines.push(weaponEnergyLine);

    // STATS (ATTACK / DEFENSE / SPEED)
    let statsLine = { classes: 'base-stats types', label: 'Stats:', values: [] }; {
        let statValues = [], liveStatValues = [], statValuesRange = [];
        let marginBase = 16, marginBaseMax = (statTokens.length * (marginBase - 1));
        for (let i = 0; i < statTokens.length; i++){
            let statToken = statTokens[i];
            let statMods = playerRobotInfo[statToken + 'Mods'] || 0;
            let statBaseValue = playerRobotInfo[statToken] || 0;
            let liveStatValue = _self.calculateRobotStat(statBaseValue, statMods);
            statValues.push(statBaseValue);
            liveStatValues.push(liveStatValue);
            }
        //let translatedValuePercents = _self.translateValueRange(liveStatValues, 1, 100, true);
        //let translatedValueMargins = _self.translateValueRange(liveStatValues, 1, marginBase, true);
        let scaledValueMargins = _self.scaleArrayToRange(liveStatValues, 1, marginBase, true);
        //console.log('--> marginBase =', marginBase);
        //console.log('--> marginBaseMax =', marginBaseMax);
        //console.log('--> statValues =', statValues);
        //console.log('--> liveStatValues =', liveStatValues);
        //console.log('--> translatedValuePercents =', translatedValuePercents);
        //console.log('--> translatedValueMargins =', translatedValueMargins);
        //console.log('--> scaledValueMargins =', scaledValueMargins);
        for (let i = 0; i < statTokens.length; i++){
            let statToken = statTokens[i];
            let statCode = statTokenCodes[i];
            let statMargin = marginBase - scaledValueMargins[i];
            //let statMargin = marginBase - translatedValueMargins[i];
            //let statRangeValue = statValuesRange[i];
            //let statRangeValue = translatedValueMargins[i];
            let statName = statToken.charAt(0).toUpperCase() + statToken.slice(1);
            let statMods = playerRobotInfo[statToken + 'Mods'] || 0;
            let statBaseValue = statValues[i]; //playerRobotInfo[statToken] || 0;
            let statRealValue = liveStatValues[i]; //_self.calculateRobotStat(statBaseValue, statMods);
            let statModArrows = '';
            if (statMods > 0){ statModArrows += ('&#9650;').repeat(statMods); }
            else if (statMods < 0){ statModArrows += ('&#9660;').repeat(Math.abs(statMods)); }
            statModArrows = statModArrows.length ? ('<sup class="arrows">' + statModArrows + '</sup>') : '';
            let statValueClasses = statToken;
            let statModClasses = !statMods ? '' : (statMods > 0 ? ' raised' : ' lowered');
            statsLine.values.push({
                //value: statRealValue + ' ' + statCode,
                value: statRealValue + (true ? '' : '') + ' ' + statCode + statModArrows,
                valueClasses: 'type ' + statValueClasses + statModClasses,
                valueStyles: 'margin-left: ' + statMargin + 'px;',
                });
            }
        }
    robotDetailsObject.infoLines.push(statsLine);

    // HELD ITEM
    let itemLine = { classes: 'held-item types', label: 'Item:', values: [] }; {
        let itemID, itemToken, itemName, itemSprite, itemTypes;
        if (robotItem && robotItemInfo){
            itemID = robotItemInfo.id;
            itemToken = robotItemInfo.token;
            itemName = robotItemInfo.name;
            itemSprite = _self.getItemSpriteMarkup(itemToken, {classes: 'icon'});
            itemTypes = (function(info){
                if (!info){ return ''; }
                else if (!info.type && !info.type2){ return 'none'; }
                else if (!info.type && info.type2){ return info.type2; }
                else { return info.type; }
                })(robotItemInfo);
            } else {
            itemID = 0;
            itemToken = '';
            itemName = 'None',
            itemSprite = '<span class="icon"><i class="fa fas fa-times"></i></span>';
            itemTypes = 'empty';
            }
        let itemNameSize = itemName.split(' ').length;
        let itemNameMarkup = (itemNameSize > 1 ? ('<b>' + itemName.replace(' ', '<br />') + '</b>') : ('<b><b>' + itemName + '</b></b>'));
        let itemSpriteMarkup = itemSprite;
        let itemTypeClasses = itemTypes;
        itemLine.values.push({
            value: itemNameMarkup + itemSpriteMarkup,
            valueClasses: 'type ' + itemTypeClasses,
            valueAttrs: {'item-id': itemID, 'item-token': itemToken},
            });
        }
    robotDetailsObject.infoLines.push(itemLine);

    // SUPPORT MECHA
    let supportLine = { classes: 'support-mecha types', label: 'Support:', values: [] }; {
        let supportToken, supportName, supportSprite, supportTypes;
        if (robotSupportEquipped){
            if (robotSupport && robotSupportInfo){
                supportToken = robotSupport;
                supportName = robotSupportInfo.name;
                supportSprite = _self.getRobotSpriteMarkup(supportToken);
                supportTypes = (function(info){
                    if (!info){ return ''; }
                    else if (!info.core && !info.core2){ return 'none'; }
                    else if (!info.core && info.core2){ return info.core2; }
                    else { return info.core; }
                    })(robotSupportInfo);
                } else {
                supportToken = '';
                supportName = '&hellip;',
                supportSprite = '<span class="icon"><i class="fa fas fa-question"></i></span>';
                supportTypes = 'empty';
                }
            } else {
            supportToken = '';
            supportName = 'None',
            supportSprite = '<span class="icon"><i class="fa fas fa-times"></i></span>';
            supportTypes = 'empty';
            }
        let supportNameSize = supportName.split(' ').length;
        let supportNameMarkup = (supportNameSize > 1 ? ('<b>' + supportName.replace(' ', '<br />') + '</b>') : ('<b><b>' + supportName + '</b></b>'));
        let supportSpriteMarkup = supportSprite;
        let supportTypeClasses = supportTypes;
        supportLine.values.push({
            value: supportNameMarkup + supportSpriteMarkup,
            valueClasses: 'type ' + supportTypeClasses,
            });
        }
    robotDetailsObject.infoLines.push(supportLine);

    // EQUIPPED ABILITIES
    let abilitiesLine = { classes: 'equipped-abilities types', label: 'Abilities:', values: [] }; {
        let playerRobotAbilities = playerRobotInfo.abilities || [];
        let newPlayerRobotAbilities = playerRobotInfo.abilitiesAdded || [];
        let maxAbilitiesPerRobot = _config.maxAbilitiesPerRobot;
        //console.log('--> playerRobotAbilities =', playerRobotAbilities);
        for (let i = 0; i < maxAbilitiesPerRobot; i++){
            let abilitySlotKey = i;
            let abilitySlotNum = abilitySlotKey + 1;
            let abilityID = typeof playerRobotAbilities[abilitySlotKey] !== 'undefined' ? playerRobotAbilities[abilitySlotKey] : 0;
            let abilityInfo = _mmrpgAbilitiesIndex.getByID(abilityID);
            let abilityToken, abilityName, abilityTypeClasses;
            if (abilityInfo){
                abilityToken = abilityInfo.token;
                abilityName = abilityInfo.name;
                abilityTypeClasses = (abilityInfo.type2 && !abilityInfo.type ? abilityInfo.type2 : ((abilityInfo.type ? abilityInfo.type : 'none') + (abilityInfo.type2 ? '_' + abilityInfo.type2 : '')));
                if (newPlayerRobotAbilities.indexOf(abilityToken) !== -1){ abilityTypeClasses += ' new'; }
                } else {
                abilityToken = '';
                abilityName = 'None';
                abilityTypeClasses = 'empty';
                }
            let abilityNameFormatted = '<span class="name"><strong>' + abilityName.replace(' ', '<br />') + '</strong></span>';;
            let abilityIconSprite = abilityToken ? _self.getAbilitySpriteMarkup(abilityToken, {classes: 'icon', showBack: true}) : '';
            let abilityValueMarkup = abilityIconSprite + abilityNameFormatted;
            //console.log('--> abilitySlotKey =', abilitySlotKey);
            //console.log('--> abilitySlotNum =', abilitySlotNum);
            //console.log('--> abilityID =', abilityID);
            //console.log('--> abilityToken =', abilityToken);
            //console.log('--> abilityInfo =', abilityInfo);
            abilitiesLine.values.push({
                value: abilityValueMarkup,
                valueClasses: 'type ' + abilityTypeClasses,
                valueAttrs: {'ability-id': abilityID, 'ability-token': abilityToken, 'ability-slot': abilitySlotNum},
                });
            }
        }
    robotDetailsObject.infoLines.push(abilitiesLine);

    // TEMP TEMP TEMP TEMP
    // TODO: show weaknesses, resistances, affinities, immunities on separate 'page' of details maybe?
    if (false){
        // WEAKNESSES / RESISTANCES / AFFINITIES / IMMUNITIES
        for (let i = 0; i < weaknessTokens.length; i++){
            let weaknessToken = weaknessTokens[i];
            let weaknessLine = { classes: weaknessToken + ' types', label: weaknessToken.charAt(0).toUpperCase() + weaknessToken.slice(1) + ':', values: [] };
            if (robotIndexInfo[weaknessToken] && robotIndexInfo[weaknessToken].length){
                for (let j = 0; j < robotIndexInfo[weaknessToken].length; j++){
                    let weaknessType = robotIndexInfo[weaknessToken][j];
                    weaknessLine.values.push({
                        value: _mmrpgTypesIndex[weaknessType].name,
                        valueClasses: 'type ' + weaknessType
                        });
                    }
                } else {
                weaknessLine.values.push({
                    value: 'None',
                    valueClasses: 'type empty'
                    });
                }
            robotDetailsObject.infoLines.push(weaknessLine);
            }
        }

    // Collect some details about the currently open storage menu
    let currentScreen = _world.currentScreen;
    let currentSubScreen = _world.currentSubScreen;

    // ACTION BUTTONS
    let _inputs = _self.inputs;
    let _userInputs = _inputs.userInputs
    let aButtonIcon = _userInputs.A.icon, bButtonIcon = _userInputs.B.icon;
    let xButtonIcon = _userInputs.X.icon, yButtonIcon = _userInputs.Y.icon;
    //console.log('_userInputs =', _userInputs);
    // withdraw/deposit,take-out/put-away,activate/bench,add-to-team/remove-from-team
    robotDetailsObject.actions = [];
    let showStorageButtons = (currentScreen === 'robots-overview' && currentSubScreen === 'robots') ? true : false;
    let showTeamAddButton = showStorageButtons && !robotIsCurrent ? true : false;
    let showTeamRemoveButton = showStorageButtons && robotIsCurrent ? true : false;
    let allowTeamAddButton = showTeamAddButton, allowTeamRemoveButton = showTeamRemoveButton;
    if (playerRobotsCurrent.length === 1){ allowTeamRemoveButton = false; }
    else if (playerRobotsCurrent.length >= _config.playerRobotsLimit){ allowTeamAddButton = false; }
    else if (playerRobotsCurrent.length >= _config.maxRobotsPerPlayer){ allowTeamAddButton = false; }
    robotDetailsObject.actions.push({ action: 'robot-info', text: yButtonIcon + ' Details', button: 'Y', robot: robotToken, disabled: false, hidden: !showStorageButtons });
    robotDetailsObject.actions.push({ action: 'add-robot', text: xButtonIcon + ' Summon', button: 'X', robot: robotToken, disabled: !allowTeamAddButton, hidden: !showStorageButtons || !showTeamAddButton });
    robotDetailsObject.actions.push({ action: 'remove-robot', text: xButtonIcon + ' Dismiss', button: 'X', robot: robotToken, disabled: !allowTeamRemoveButton, hidden: !showStorageButtons || !showTeamRemoveButton });
    // TODO (!!!) Add an "swap-in" option to storage robots when the player only has one robot and it's disabled

    // Pre-compile some of the HTML to make it easier for the other functions
    //robotDetailsObject.levelHTML = (robotDetailsObject.level >= 100 ? '<b>' : '') + 'Level ' + robotDetailsObject.level + (robotDetailsObject.level >= 100 ? '</b>' : '');
    //robotDetailsObject.levelHTML = (robotDetailsObject.level >= 100 ? '<i class="fas fa-star"></i> Lv. ' : 'Level ') + robotDetailsObject.level;}
    robotDetailsObject.levelHTML = 'Level ' + robotDetailsObject.level + (robotDetailsObject.level >= 100 ? ' <i class="fa fas fa-star color level"></i>' : '');
    robotDetailsObject.experienceHTML = (robotDetailsObject.level >= 100 ? '<i>&#8734;</i>' : robotDetailsObject.experience) + ' / 1000 Exp';
    robotDetailsObject.classIconHTML = '' + (robotDetailsObject.classIcon ? ('<i class="fa fas fa-' + robotDetailsObject.classIcon + '"></i>') : '');
    robotDetailsObject.infolinesHTML = '';
    for (let i = 0; i < robotDetailsObject.infoLines.length; i++){
        let infoLine = robotDetailsObject.infoLines[i];
        let infoClasses = infoLine.classes || '';
        if (infoLine.guages && infoLine.guages.length){ infoClasses += ' has-guage'; }
        robotDetailsObject.infolinesHTML += '<div class="infoline ' + infoClasses + '">';
            if (infoLine.guages){
                for (let j = 0; j < infoLine.guages.length; j++){
                    let guageInfo = infoLine.guages[j];
                    robotDetailsObject.infolinesHTML += '<span class="guage' + (guageInfo.guageClasses ? (' ' + guageInfo.guageClasses) : '') + '">'
                        + '<hr class="type current ' + guageInfo.type + '" style="width: ' + guageInfo.percent + '%;" />'
                        + '<hr class="type max ' + guageInfo.type + '" />'
                        + '</span>';
                    }
                }
            robotDetailsObject.infolinesHTML += '<strong class="label">' + infoLine.label + '</strong>';
            if (infoLine.values){
                for (let j = 0; j < infoLine.values.length; j++){
                    let valueInfo = infoLine.values[j];
                    let valueClasses = valueInfo.valueClasses || '';
                    let valueStyles = valueInfo.valueStyles || '';
                    let valueAttrs = valueInfo.valueAttrs || '';
                    if (valueAttrs && typeof valueAttrs === 'object'){
                        let k = 0, attrs = Object.keys(valueAttrs), vals = Object.values(valueAttrs);
                        for (valueAttrs = ''; k < attrs.length; k++){ valueAttrs += ' data-' + attrs[k] + '="' + vals[k] + '"'; }
                        }
                    robotDetailsObject.infolinesHTML += '<span class="value' + (valueClasses ? (' ' + valueClasses) : '') + '"' + (valueAttrs ? (' ' + valueAttrs) : '') + (valueStyles ? (' style="' + valueStyles + '"') : '') + '>' + valueInfo.value + '</span>';
                    if (valueInfo.icon){ robotDetailsObject.infolinesHTML += '<i class="fa fas fa-' + valueInfo.icon + '"></i>'; }
                    }
                } else {
                robotDetailsObject.infolinesHTML += '<span class="value' + (infoLine.valueClasses ? (' ' + infoLine.valueClasses) : '') + '">' + infoLine.value + '</span>';
                if (infoLine.icon){ robotDetailsObject.infolinesHTML += '<i class="fa fas fa-' + infoLine.icon + '"></i>'; }
                }
        robotDetailsObject.infolinesHTML += '</div>';
        }
    robotDetailsObject.actionsHTML = '';
    for (let i = 0; i < robotDetailsObject.actions.length; i++){
        let actionInfo = robotDetailsObject.actions[i];
        let actionButton = typeof actionInfo.button !== 'undefined' ? actionInfo.button : false;
        let actionDisabled = typeof actionInfo.disabled !== 'undefined' && actionInfo.disabled === true ? true : false;
        let actionHidden = typeof actionInfo.hidden !== 'undefined' && actionInfo.hidden === true ? true : false;
        let actionIcon = actionInfo.icon ? '<i class="fa fas fa-' + actionInfo.icon + '"></i> ' : '';
        robotDetailsObject.actionsHTML += '<button type="button" '
            + 'class="button ' + actionInfo.action + (actionDisabled ? ' disabled' : '') + (actionHidden ? ' hidden' : '') + '" '
            + 'data-action="' + actionInfo.action + '"'
            + (actionButton ? ' data-button="' + actionButton + '"' : '')
            + (actionDisabled ? ' disabled="disabled"' : '') +
            '>' + actionIcon + actionInfo.text + '</button>';
        }

    // Return the generated robot details object
    //console.log('--> robotDetailsObject =', robotDetailsObject);
    return robotDetailsObject;
    }
// Define a quick function for getting the details markup for a given robot in the user's inventory
function getRobotDetailsMarkupForOverview(robotToken){
    //console.log('%c' + 'mmrpgWorldMap.getRobotDetailsMarkupForOverview(robot:' + robotToken + ')', 'color: magenta;');
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('getRobotDetailsMarkupForOverview() missing required robotToken!'); return ''; }
    let _self = this;
    let robotDetailsObject = _self.getRobotDetailsForOverview(robotToken) || false;
    if (!robotDetailsObject || typeof robotDetailsObject !== 'object'){ console.error('getRobotDetailsMarkupForOverview() could not generate details object for token ' + robotToken + '!'); return ''; }
    let robotDetailsMarkup = '';
    robotDetailsMarkup += '<div class="storage-details" data-robot="' + robotToken + '">';
        robotDetailsMarkup += '<div class="title">' + robotDetailsObject.title + '</div>';
        robotDetailsMarkup += '<div class="image type ' + robotDetailsObject.typeClasses + '">' + robotDetailsObject.image + '</div>';
        robotDetailsMarkup += '<div class="subtitle">';
            robotDetailsMarkup += '<strong class="name">' + robotDetailsObject.name + '</strong>';
            robotDetailsMarkup += '<strong class="level">' + robotDetailsObject.levelHTML + '</strong>';
            robotDetailsMarkup += '<em class="experience">' + robotDetailsObject.experienceHTML + '</em>';
            robotDetailsMarkup += '<sub class="type ' + robotDetailsObject.typeClasses + '">' + robotDetailsObject.typeName + '</sub>';
            robotDetailsMarkup += '<hr class="type ' + robotDetailsObject.typeClasses + '">';
        robotDetailsMarkup += '</div>';
        //robotDetailsMarkup += '<div class="description"><p>' + (robotDetailsObject.classIconHTML ? (robotDetailsObject.classIconHTML + ' ') : '') + robotDetailsObject.description + '</p></div>';
        robotDetailsMarkup += '<div class="infolines">' + robotDetailsObject.infolinesHTML + '</div>';
        robotDetailsMarkup += '<div class="actions">' + robotDetailsObject.actionsHTML + '</div>';
    robotDetailsMarkup += '</div>';
    return robotDetailsMarkup;
    }
// Define a quick function for replacing the robot details in an existing details div with new ones
function replaceRobotDetailsInOverview($detailsDiv, robotToken, robotDetails){
    //console.log('%c' + 'mmrpgWorldMap.replaceRobotDetailsInOverview($detailsDiv, robotToken:' + robotToken + ', robotDetails)', 'color: magenta;');
    if (!$detailsDiv || !$detailsDiv.length){ console.error('replaceRobotDetailsInOverview() missing required $detailsDiv!'); return false; }
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('replaceRobotDetailsInOverview() missing required robotToken!'); return false; }
    if (!robotDetails || typeof robotDetails !== 'object'){ console.error('replaceRobotDetailsInOverview() missing required robotDetails!'); return false; }
    //console.log('-> robotDetails =', robotDetails);
    let $title = $detailsDiv.find('> .title'),
        $image = $detailsDiv.find('> .image'),
        $subtitle = $detailsDiv.find('> .subtitle'),
        $subtitleName = $subtitle.find('> .name'),
        $subtitleLevel = $subtitle.find('> .level'),
        $subtitleExp = $subtitle.find('> .experience'),
        $subtitleSubtext = $subtitle.find('> sub'),
        $subtitleSubline = $subtitle.find('> hr'),
        $infolines = $detailsDiv.find('> .infolines'),
        $description = $detailsDiv.find('> .description'),
        $actions = $detailsDiv.find('> .actions')
        ;
    $detailsDiv.attr('data-robot', robotToken);
    $title.html(robotDetails.title);
    $image.html(robotDetails.image);
    $image.removeClass().addClass('image type ' + robotDetails.typeClasses);
    $subtitleName.html(robotDetails.name);
    $subtitleLevel.html(robotDetails.levelHTML);
    $subtitleExp.html(robotDetails.experienceHTML);
    $subtitleSubtext.removeClass().addClass('type ' + robotDetails.typeClasses).html(robotDetails.typeName);
    $subtitleSubline.removeClass().addClass('type ' + robotDetails.typeClasses);
    $infolines.html(robotDetails.infolinesHTML);
    //$description.html((robotDetails.classIconHTML ? (robotDetails.classIconHTML + ' ') : '') + robotDetails.description);
    $description.remove();
    // manually update buttons so css transitions can occur properly
    for (let i = 0; i < robotDetails.actions.length; i++){
        let actionInfo = robotDetails.actions[i];
        let actionButton = typeof actionInfo.button !== 'undefined' ? actionInfo.button : false;
        let actionDisabled = typeof actionInfo.disabled !== 'undefined' && actionInfo.disabled === true ? true : false;
        let actionHidden = typeof actionInfo.hidden !== 'undefined' && actionInfo.hidden === true ? true : false;
        let $actionButton = $actions.find('.button.' + actionInfo.action);
        if ($actionButton && $actionButton.length){
            if (actionDisabled){ $actionButton.addClass('disabled'); $actionButton.attr('disabled', 'disabled'); }
            else { $actionButton.removeClass('disabled'); $actionButton.removeAttr('disabled'); }
            if (actionHidden){ $actionButton.addClass('hidden'); }
            else { $actionButton.removeClass('hidden'); }
            $actionButton.html(actionInfo.text);
            } else {
            let actionButtonMarkup = '<button type="button" '
                + 'class="button ' + actionInfo.action + (actionDisabled ? ' disabled' : '') + (actionHidden ? ' hidden' : '') + '" '
                + 'data-robot="' + actionInfo.robot + '"'
                + (actionButton ? ' data-button="' + actionButton + '"' : '')
                + (actionDisabled ? ' disabled="disabled"' : '')
                + '>' + actionInfo.text + '</button>';
            $actions.append(actionButtonMarkup);
            $actionButton = $actions.find('.button.' + actionInfo.action);
            }
        $actionButton.addClass('keep');
        }
    $actions.find('.button:not(.keep)').remove();
    $actions.find('.button.keep').removeClass('keep');
    return true;
    }
// Define a function for getting the id-token for the currently selected overview robot, if any
// TODO: we should store and retrieve this value somewhere local instead of grabbing it from the DOM every time
function getSelectedRobotInOverview(returnObject){
    //console.log('%c' + 'mmrpgWorldMap.getSelectedRobotInOverview()', 'color: magenta;');
    returnObject = typeof returnObject === 'boolean' ? returnObject : false;
    let _self = this;
    let _elements = _self.elements;
    let $robotsOverview = _elements.robotsOverview;
    //console.log('--> $robotsOverview =', $robotsOverview);
    let $teamRobotsDiv = $robotsOverview.find('.team-robots');
    let $selectedTeamRobot = $teamRobotsDiv.find('.team-robot[data-robot].selected').first();
    if ($selectedTeamRobot.length === 0){ return false; }
    let selectedRobot = $selectedTeamRobot.attr('data-robot');
    //console.log('--> selectedRobot =', selectedRobot);
    if (!selectedRobot){ console.error('getSelectedRobotInOverview() could not find selected robot token!'); return false; }
    if (!returnObject){ return selectedRobot; }
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _mmrpgRobotsIndex = _indexes.robots;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _playerRobotsIndex = _config.playerRobotsIndex;
    let frags = selectedRobot.indexOf('_') !== -1 ? selectedRobot.split('_') : [];
    let robotID = parseInt(frags[0]), robotToken = frags[1];
    //console.log('--> selectedRobot =', selectedRobot);
    //console.log('--> robotID =', robotID, 'robotToken =', robotToken);
    let selectedRobotData = false, selectedRobotInfo = false;
    if (typeof _mmrpgRobotsIndex[robotToken] === 'undefined'){ console.error('getRobotDetailsForOverview() could not find robot in index for token ' + robotToken + '!'); return false; }
    if (typeof _worldPlayerRobots[selectedRobot] === 'undefined'){ console.error('getRobotDetailsForOverview() could not find player robot info for token ' + selectedRobot + '!'); return false; }
    selectedRobotInfo = _mmrpgRobotsIndex[robotToken];
    selectedRobotData = _worldPlayerRobots[selectedRobot];
    //console.log('--> selectedRobotInfo =', selectedRobotInfo);
    //console.log('--> selectedRobotData =', selectedRobotData);
    return {key: selectedRobot, id: robotID, token: robotToken, info: selectedRobotInfo, data: selectedRobotData};
    }

// Quick function for getting a given robot's name span already styled
function getRobotNameSpan(robotToken, customText){
    //console.log('%c' + 'mmrpgWorldMap.getRobotNameSpan(robotToken:' + robotToken + ', customText:' + customText + ')', 'color: magenta;');
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgRobotsIndex = _indexes.robots;
    let robotInfo = _mmrpgRobotsIndex[robotToken] || false;
    let robotName = robotInfo ? robotInfo.name : 'Robot';
    let robotType1 = robotInfo.core || '';
    let robotType2 = robotInfo.core2 || '';
    let spanTypes = robotType1 !== '' ? robotType1 : (robotType2 !== '' ? robotType2 : 'none');
    return '<span class="type ' + spanTypes + '">' + (customText || robotName) + '</span>';
    }
// Quick function for getting a robot energy frame given a rating token
function getRobotEnergyFrame(rating){
    //console.log('%c' + 'mmrpgWorldMap.getRobotEnergyFrame(' + rating + ')', 'color: magenta;');
    if (!rating || typeof rating !== 'string' || !rating.length){ console.error('getRobotEnergyFrame() missing required rating!'); return false; }
    if (rating === 'full'){ return '10'; } // base2
    else if (rating === 'high'){ return '01'; } // taunt
    else if (rating === 'med'){ return '00'; } // base
    else if (rating === 'low'){ return '08'; } // defend
    else { return '03'; } // defeat
    }
// Define a quick function for grabbing the sprite markup of a robot given the token and optional sprite arguments
function getRobotSpriteMarkup(robotToken, spriteOptions){
    //console.log('%c' + 'mmrpgWorldMap.getRobotSpriteMarkup(robotToken:' + robotToken + ', spriteOptions:', + JSON.stringify(spriteOptions) + ')', 'color: magenta;');
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('getRobotSpriteMarkup() missing required robotToken!'); return ''; }
    let robotId = 0; if (robotToken.indexOf('_') !== -1){ robotId = robotToken.split('_')[0]; robotToken = robotToken.split('_')[1];  }
    //console.log('-> robotId =', robotId, '-> robotToken =', robotToken);
    spriteOptions = spriteOptions || {};
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let robotInfo = _indexes.robots.getByToken(robotToken);
    if (!robotInfo || typeof robotInfo !== 'object'){ console.error('getRobotSpriteMarkup() could not find robotInfo for token ' + robotToken + '!'); return ''; }
    // Collect or define the sprite options with defaults if not provided
    spriteOptions.alt = typeof spriteOptions.alt === 'string' && spriteOptions.alt.length > 1 ? spriteOptions.alt : '';
    spriteOptions.size = typeof spriteOptions.size === 'number' && spriteOptions.size > 0 ? spriteOptions.size : (robotInfo.imageSize || 40);
    spriteOptions.dir = typeof spriteOptions.dir === 'string' && spriteOptions.dir.length ? spriteOptions.dir : 'right';
    spriteOptions.frame = typeof spriteOptions.frame === 'string' && spriteOptions.frame.length > 0 ? spriteOptions.frame : '00';
    spriteOptions.delay = typeof spriteOptions.delay === 'number' && spriteOptions.delay !== 0 ? spriteOptions.delay : (-1 * ( Math.floor(Math.random() * 10) / 100 ));
    spriteOptions.classes = typeof spriteOptions.classes === 'string' && spriteOptions.classes.length > 1 ? spriteOptions.classes : '';
    spriteOptions.styles = typeof spriteOptions.styles === 'string' && spriteOptions.styles.length > 1 ? spriteOptions.styles : '';
    // Generate the robot sprite attributes and inner markup given the options
    let robotSpriteAttrs = '';
    let robotSpriteClass = 'sprite robot' + (spriteOptions.classes ? ' ' + spriteOptions.classes : '');
    let robotSpriteStyle = 'animation-delay: ' + spriteOptions.delay + 's;' + (spriteOptions.styles ? ' ' + spriteOptions.styles : '');
    robotSpriteAttrs += ' class="' + robotSpriteClass + '"';
    robotSpriteAttrs += ' data-sprite="robot"';
    robotSpriteAttrs += ' data-token="' + robotToken + '"';
    robotSpriteAttrs += ' data-alt="' + spriteOptions.alt + '"';
    robotSpriteAttrs += ' data-size="' + spriteOptions.size + '"';
    robotSpriteAttrs += ' data-dir="' + spriteOptions.dir + '"';
    robotSpriteAttrs += ' data-frame="' + spriteOptions.frame + '"';
    if (robotSpriteStyle.length){ robotSpriteAttrs += ' style="' + robotSpriteStyle + '"'; }
    let robotSpriteInner = '';
    robotSpriteInner += '<i class="sprite"></i>';
    // Put it all together to generate the final markup
    let robotSpriteMarkup = '';
    robotSpriteMarkup += '<span' + robotSpriteAttrs + '>';
        robotSpriteMarkup += '<span class="wrap">' + robotSpriteInner + '</span>';
    robotSpriteMarkup += '</span>';
    // Return the generated markup for the robot sprite
    return robotSpriteMarkup;
    }

// Define a quick function for showing an robot modal for some kind of robot-related action
function showRobotModal(actionToken, robotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showRobotModal(actionToken:' + actionToken + ', robotToken:' + robotToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showRobotModal() missing required actionToken!'); return; }
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('showRobotModal() missing required robotToken!'); return; }
    let _self = this;
    return _self.showActionModal('robot', actionToken, robotToken, null, configCustom);
    }
// Define quick functions for showing the modal that adds a robot to the team from storage
function showAddRobotModal(robotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showAddRobotModal(robotToken:' + robotToken + ')', 'color: magenta;');
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('showAddRobotModal() missing required robotToken!'); return; }
    let _self = this;
    return _self.showRobotModal('add-robot', robotToken, configCustom);
    }
// Define quick functions for showing the modal that removes a robot from the team to storage
function showRemoveRobotModal(robotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showRemoveRobotModal(robotToken:' + robotToken + ')', 'color: magenta;');
    if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('showRemoveRobotModal() missing required robotToken!'); return; }
    let _self = this;
    return _self.showRobotModal('remove-robot', robotToken, configCustom);
    }

// Quick function to check if the player has a certain ability type available, either via team robots or storage if requested
function getPlayerRobotsWithAbilityType(typeToken, includeStorage, includeDisabled){
    //console.log('%c' + 'mmrpgWorldMap.getPlayerRobotsWithAbilityType(typeToken: ' + typeToken + ')', 'color: magenta;');
    if (!typeToken || typeof typeToken !== 'string' || !typeToken.length){ console.error('getPlayerRobotsWithAbilityType() missing required typeToken!'); return false; }
    includeStorage = typeof includeStorage === 'boolean' ? includeStorage : false;
    includeDisabled = typeof includeDisabled === 'boolean' ? includeDisabled : false;
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _worldPlayerTeam = _worldPlayer.team;
    let returnData = [];
    for (let i = 0; i < _worldPlayerTeam.length; i++){
        let robotString = _worldPlayerTeam[i];
        let playerRobot = _worldPlayerRobots[robotString];
        //console.log('now checking', robotString, 'for a', typeToken, 'type ability \n -> playerRobot:', playerRobot);
        if (playerRobot.disabled === true && !includeDisabled){ continue; }
        let playerRobotAbilities = playerRobot.abilities || [];
        for (let j = 0; j < playerRobotAbilities.length; j++){
            let abilityID = playerRobotAbilities[j];
            let abilityInfo = _mmrpgAbilitiesIndex.getByID(abilityID) || {};
            let abilityToken = abilityInfo.token || false;
            //console.log('reviewing abilityID:', abilityID, ' w/ abilityToken:', abilityToken, '\n-> w/ abilityInfo:', abilityInfo);
            if (abilityInfo.type === ''){ continue; }
            if (abilityInfo.type === typeToken
                || abilityInfo.type2 === typeToken){
                returnData.push([robotString, abilityToken]);
                }
            }
        }
    return returnData.length ? returnData : false;
    }

// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.setRobotEnergy = setRobotEnergy;
mmrpgWorldMap.prototype.restoreRobotEnergy = restoreRobotEnergy;
mmrpgWorldMap.prototype.damageRobotEnergy = damageRobotEnergy;

mmrpgWorldMap.prototype.setRobotWeapons = setRobotWeapons;
mmrpgWorldMap.prototype.restoreRobotWeapons = restoreRobotWeapons;

mmrpgWorldMap.prototype.resetRobotStat = resetRobotStat;
mmrpgWorldMap.prototype.resetRobotAttack = resetRobotAttack;
mmrpgWorldMap.prototype.resetRobotDefense = resetRobotDefense;
mmrpgWorldMap.prototype.resetRobotSpeed = resetRobotSpeed;

mmrpgWorldMap.prototype.boostRobotStat = boostRobotStat;
mmrpgWorldMap.prototype.boostRobotAttack = boostRobotAttack;
mmrpgWorldMap.prototype.boostRobotDefense = boostRobotDefense;
mmrpgWorldMap.prototype.boostRobotSpeed = boostRobotSpeed;

mmrpgWorldMap.prototype.breakRobotStat = breakRobotStat;
mmrpgWorldMap.prototype.breakRobotAttack = breakRobotAttack;
mmrpgWorldMap.prototype.breakRobotDefense = breakRobotDefense;
mmrpgWorldMap.prototype.breakRobotSpeed = breakRobotSpeed;

mmrpgWorldMap.prototype.giveRobotItem = giveRobotItem;
mmrpgWorldMap.prototype.takeRobotItem = takeRobotItem;

mmrpgWorldMap.prototype.calculateRobotStat = calculateRobotStat;

mmrpgWorldMap.prototype.getRobotDetailsForOverview = getRobotDetailsForOverview;
mmrpgWorldMap.prototype.getRobotDetailsMarkupForOverview = getRobotDetailsMarkupForOverview;
mmrpgWorldMap.prototype.replaceRobotDetailsInOverview = replaceRobotDetailsInOverview;
mmrpgWorldMap.prototype.getSelectedRobotInOverview = getSelectedRobotInOverview;

mmrpgWorldMap.prototype.getRobotNameSpan = getRobotNameSpan;
mmrpgWorldMap.prototype.getRobotEnergyFrame = getRobotEnergyFrame;
mmrpgWorldMap.prototype.getRobotSpriteMarkup = getRobotSpriteMarkup;

mmrpgWorldMap.prototype.showRobotModal = showRobotModal;
mmrpgWorldMap.prototype.showAddRobotModal = showAddRobotModal;
mmrpgWorldMap.prototype.showRemoveRobotModal = showRemoveRobotModal;

mmrpgWorldMap.prototype.getPlayerRobotsWithAbilityType = getPlayerRobotsWithAbilityType;
