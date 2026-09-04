
// -- WORLD ITEM METHODS -- //

// Define a quick function for checking if a given item (by token) is a consumable
function itemIsConsumable(itemToken){
    //console.log('%c' + 'mmrpgWorldMap.itemIsConsumable(' + itemToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('itemIsConsumable() missing required itemToken!'); return false; }
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgItemsIndex = _indexes.items;
    if (itemToken.indexOf('__') !== -1){ itemToken = itemToken.split('__')[0]; }
    if (typeof _mmrpgItemsIndex[itemToken] === 'undefined'){
        console.error('itemIsConsumable() could not find item in index for token ' + itemToken + '!');
        return false;
        }
    let itemInfo = _mmrpgItemsIndex[itemToken];
    if (itemInfo.subclass === 'consumable'){ return true;  }
    return false;
    }
// Define a quick function for checking if a given item (by token) is holdable
function itemIsHoldable(itemToken){
    //console.log('%c' + 'mmrpgWorldMap.itemIsHoldable(' + itemToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('itemIsHoldable() missing required itemToken!'); return false; }
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgItemsIndex = _indexes.items;
    if (itemToken.indexOf('__') !== -1){ itemToken = itemToken.split('__')[0]; }
    if (typeof _mmrpgItemsIndex[itemToken] === 'undefined'){
        console.error('itemIsHoldable() could not find item in index for token ' + itemToken + '!');
        return false;
        }
    let itemInfo = _mmrpgItemsIndex[itemToken];
    if (itemInfo.subclass === 'holdable'){ return true;  }
    else if (_self.itemIsConsumable(itemToken)){ return true;  }
    return false;
    }
// Define a quick function for checking if a given item (by token) is holdable
function itemIsStarforce(itemToken){
    //console.log('%c' + 'mmrpgWorldMap.itemIsStarforce(' + itemToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('itemIsHoldable() missing required itemToken!'); return false; }
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgItemsIndex = _indexes.items;
    if (itemToken.indexOf('__') !== -1){ itemToken = itemToken.split('__')[0]; }
    if (itemToken.match(/^([a-z]+)-star$/i)){ return true;  }
    return false;
    }
// Define a quick function for checking if the given item (by token) is an event item
function itemIsEvent(itemToken){
    //console.log('%c' + 'mmrpgWorldMap.itemIsEvent(' + itemToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('itemIsEvent() missing required itemToken!'); return false; }
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgItemsIndex = _indexes.items;
    if (itemToken.indexOf('__') !== -1){ itemToken = itemToken.split('__')[0]; }
    if (typeof _mmrpgItemsIndex[itemToken] === 'undefined'){
        console.error('itemIsEvent() could not find item in index for token ' + itemToken + '!');
        return false;
        }
    let itemInfo = _mmrpgItemsIndex[itemToken];
    if (itemInfo.subclass === 'event'){ return true;  }
    return false;
    }

// Define a quick function for triggering a live item pickup on the field (and any effects that may have
function triggerItemPickup(itemEvent, zoomDelay, playSound){
    //console.log('%c' + 'mmrpgWorldMap.triggerItemPickup()', 'color: magenta;');
    //console.log('--> itemEvent =', itemEvent);
    if (!itemEvent || typeof itemEvent !== 'object'){ console.error('triggerItemPickup() missing required itemEvent!'); return false; }
    if (typeof itemEvent.sprite === 'undefined'){ console.error('triggerItemPickup() missing required itemEvent.sprite!'); return false; }
    if (itemEvent.claimed === true){ console.warn('triggerItemPickup() called for item that has already been claimed!'); return false; }
    playSound = typeof playSound === 'boolean' ? playSound : true; // default to true if not provided

    // Collect local references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _worldPlayerItems = _worldPlayer.items;
    let _worldPlayerStars = _worldPlayer.stars;
    let _worldItemStates = _world.items;
    let _mmrpgPlayersIndex = _indexes.players;
    let _mmrpgRobotsIndex = _indexes.robots;
    let _mmrpgItemsIndex = _indexes.items;
    let _mmrpgStarsIndex = _indexes.stars;
    let _mapItemsIndex = _config.mapItemsIndex;
    let $teamSprites = _elements.teamSprites;

    // Collect as much info about the item as we can from the event data
    let $itemEventSprite = $(itemEvent.sprite);
    let $itemEventLayer = $itemEventSprite.closest('.layer');
    let itemEventToken = itemEvent.token;
    let itemEventInfo = _mapItemsIndex[itemEventToken];
    let itemEventQuantity = itemEventInfo.quantity;
    let itemToken = itemEvent.kind2;
    //console.log('--> itemEventToken =', itemEventToken);
    //console.log('--> itemEventQuantity =', itemEventQuantity);
    //console.log('--> itemEventInfo =', itemEventInfo);
    //console.log('--> itemToken =', itemToken);
    let itemIndexInfo = _mmrpgItemsIndex[itemToken];
    //console.log('--> itemIndexInfo =', itemIndexInfo);

    // If the quantity is somehow less than one, return early
    if (!itemEventQuantity || itemEventQuantity < 1){ console.error('triggerItemPickup() called for item with quantity less than one!'); return false; }

    // Collect some information about the player too
    let numPlayerRobots = Object.keys(_worldPlayerRobots).length;
    //if (!numPlayerRobots || numPlayerRobots < 1){ console.error('triggerItemPickup() could not find any player robots!'); return false; }

    // Zoom the item sprite into the zoom layer so it's more visible to the player
    //console.log('-> zooming item sprite make it more visible');
    zoomDelay = typeof zoomDelay === 'number' ? zoomDelay : 1200; // default to sync with standard use-case
    setTimeout(function(){
        $itemEventSprite.addClass('zoom');
        $itemEventLayer.addClass('has-zoom');
        }, Math.ceil(zoomDelay / 3));

    // Predefine a variable to a post-pickup function for timing-reasons
    let postPickupFunction = null;

    // Define a variable to hold the pickup action function
    let pickupFunction = function(onComplete, afterDelay){
        //console.log('-> executing pickupFunction() for item pickup (token:', itemToken, ')');
        if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
        if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }

        // Check to make sure the item token was not empty
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){
            console.error('triggerItemPickup() called for item with empty token!');
            return false;
            }

        // Create an array to hold notification message markup for this event
        let messageMarkup = [];
        let itemQtyTextSpan = _self.getCustomNameSpan('&times;' + itemEventQuantity, 'empty');
        let itemNameTextSpan = _self.getItemNameSpan(itemToken);
        messageMarkup.push('Found ' + itemQtyTextSpan + ' ' + itemNameTextSpan + '!');

        // First, check to see if this item is consumable so we can maybe apply it to a team robot
        if (!itemEvent.claimed
            && numPlayerRobots > 0
            && _self.itemIsConsumable(itemToken)
            && _world.autoApplyConsumables === true){

            // Check if the item was a consumable health or weapon energy item
            // and apply it to the first robot that needs it, else pocket it
            if (itemToken.match(/^(energy|weapon)-(pellet|capsule|tank)$/i)){
                //console.log('oh this is an restorative-recovery item, so let us apply it');
                let itemStat = itemToken.split('-')[0];
                let itemSize = itemToken.split('-')[1];
                let itemPower = (itemSize === 'tank' ? true : (itemSize === 'capsule' ? 50 : 25));
                if (itemStat === 'weapon'){ itemStat = 'weapons'; }
                //console.log('--> itemStat =', itemStat);
                //console.log('--> itemSize =', itemSize);
                //console.log('--> itemPower =', itemPower);
                // Loop through player robots and see if any of them "need" this item
                let playerRobotKeys = Object.keys(_worldPlayerRobots);
                for (let i = 0; i < playerRobotKeys.length; i++){
                    let robotString = playerRobotKeys[i];
                    let playerRobot = _worldPlayerRobots[robotString];
                    let statKey = itemStat;
                    let statMaxKey = itemStat + 'Max';
                    let robotEnergy = playerRobot[statKey];
                    let robotEnergyMax = playerRobot[statMaxKey];
                    //console.log('-> checking robot:', robotString, playerRobot);
                    //console.log('-> playerRobot[' + statKey + '] =', robotEnergy);
                    //console.log('-> playerRobot[' + statMaxKey + '] =', robotEnergyMax);
                    if (robotEnergy >= robotEnergyMax){
                        //console.log('-> skipping robot', robotString, 'b/c it already has max', itemStat);
                        continue; // skip this robot if it already has max of this energy
                        }
                    //console.log('-> found a robot (', playerRobot, ') that needs their', itemStat, 'stat restored...');
                    //console.log('-> giving them the item:', itemEventToken, itemEventInfo, itemToken, itemPower);
                    let recoveryPower = Math.ceil(((itemPower === true ? 100 : itemPower)/100) * robotEnergyMax);
                    //console.log('-> recoveryPower =', recoveryPower);
                    if (itemStat === 'energy'){ _self.restoreRobotEnergy(robotString, recoveryPower, true); }
                    else if (itemStat === 'weapons'){ _self.restoreRobotWeapons(robotString, recoveryPower, true); }
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    messageMarkup.push('Used ' + itemNameTextSpan + ' on team robot ' + _self.getRobotNameSpan(playerRobot.token) + '!');
                    if (!itemEventQuantity){ break; } // exit the loop early if none left
                    }
                }
            // Check if the item was a consumable attack, defense, or speed-stat item
            // and apply it to the first robot that can use a boost to that stat, else pocket it
            else if (itemToken.match(/^(attack|defense|speed)-(pellet|capsule)$/i)){
                //console.log('oh this is a stat-boost item, so let us apply it');
                let itemStat = itemToken.split('-')[0];
                let itemSize = itemToken.split('-')[1];
                let itemPower = itemSize === 'capsule' ? 3 : 2;
                //console.log('--> itemStat =', itemStat);
                //console.log('--> itemSize =', itemSize);
                //console.log('--> itemPower =', itemPower);
                // Loop through player robots and see if any of them "need" this item
                let playerRobotKeys = Object.keys(_worldPlayerRobots);
                let robotStatModMax = _config.robotStatModMax;
                for (let i = 0; i < playerRobotKeys.length; i++){
                    let robotString = playerRobotKeys[i];
                    let playerRobot = _worldPlayerRobots[robotString];
                    if (!playerRobot){ console.warn('-> skipping robot', robotString, 'b/c it is not defined'); continue; }
                    //console.log('-> checking robotString:', robotString, 'playerRobot:', playerRobot);
                    let statModKey = itemStat + 'Mods';
                    let currentModValue = playerRobot[statModKey] || 0;
                    //console.log('-> statModKey:', statModKey);
                    //console.log('-> currentModValue:', currentModValue);
                    //console.log('-> robotStatModMax:', robotStatModMax);
                    if (currentModValue >= robotStatModMax){
                        //console.log('-> skipping robot', robotString, 'b/c it already has max stat mods for', itemStat);
                        continue; // skip this robot if it already has max stat mods for this stat
                        }
                    //console.log('-> found a robot (', robotString, ') that we can boost ', itemStat, 'for...');
                    //console.log('-> giving them the item:', itemEventToken, itemEventInfo, itemToken, itemPower);
                    let boostPower = itemPower;
                    //console.log('-> boostPower =', boostPower);
                    _self.boostRobotStat(robotString, itemStat, boostPower, true);
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    messageMarkup.push('Used ' + itemNameTextSpan + ' on team robot ' + _self.getRobotNameSpan(playerRobot.token) + '!');
                    if (!itemEventQuantity){ break; } // exit the loop early if none left
                    }
                }
            // Check if the item was a consumable super-stat item (attack + defense + speed all-in-one)
            // and apply it to the first robot that can use a boost to any of those stats, else pocket it
            else if (itemToken.match(/^(super)-(pellet|capsule)$/i)){
                //console.log('oh this is a super-stat-boost item, so let us apply it');
                let itemStat = itemToken.split('-')[0];
                let itemSize = itemToken.split('-')[1];
                let itemPower = itemSize === 'capsule' ? 2 : 1; // to each stat
                //console.log('--> itemStat =', itemStat);
                //console.log('--> itemSize =', itemSize);
                //console.log('--> itemPower =', itemPower);
                // Loop through player robots and see if any of them "need" this item
                let playerRobotKeys = Object.keys(_worldPlayerRobots);
                let robotStatModMax = _config.robotStatModMax;
                for (let i = 0; i < playerRobotKeys.length; i++){
                    let robotString = playerRobotKeys[i];
                    let playerRobot = _worldPlayerRobots[robotString];
                    if (!playerRobot){ console.warn('-> skipping robot', robotString, 'b/c it is not defined'); continue; }
                    //console.log('-> checking robotString:', robotString, 'playerRobot:', playerRobot);
                    let statModKeys = ['attackMods', 'defenseMods', 'speedMods'];
                    let canBoostAnyStat = false;
                    for (let s = 0; s < statModKeys.length; s++){
                        let statModKey = statModKeys[s];
                        let currentModValue = playerRobot[statModKey] || 0;
                        //console.log('-> statModKey:', statModKey);
                        //console.log('-> currentModValue:', currentModValue);
                        //console.log('-> robotStatModMax:', robotStatModMax);
                        if (currentModValue < robotStatModMax){
                            canBoostAnyStat = true;
                            break;
                            }
                        }
                    if (!canBoostAnyStat){
                        //console.log('-> skipping robot', robotString, 'b/c it already has max stat mods for all stats');
                        continue; // skip this robot if it already has max stat mods for all stats
                        }
                    //console.log('-> found a robot (', robotString, ') that we can boost some stats for...');
                    //console.log('-> giving them the item:', itemEventToken, itemEventInfo, itemToken, itemPower);
                    _self.boostRobotAttack(robotString, itemPower, true);
                    _self.boostRobotDefense(robotString, itemPower, true);
                    _self.boostRobotSpeed(robotString, itemPower, true);
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    messageMarkup.push('Used ' + itemNameTextSpan + ' on team robot ' + _self.getRobotNameSpan(playerRobot.token) + '!');
                    if (!itemEventQuantity){ break; } // exit the loop early if none left
                    }
                }
            // Check if the item was a consumable yashichi item (fully restores health and weapon energy of one robot)
            // and apply it to the first robot that needs either stats restored, else pocket it
            else if (itemToken === 'yashichi'){
                //console.log('oh this is a yashichi item, so let us apply it');
                // Loop through player robots and see if any of them "need" this item
                let playerRobotKeys = Object.keys(_worldPlayerRobots);
                for (let i = 0; i < playerRobotKeys.length; i++){
                    let robotString = playerRobotKeys[i];
                    let playerRobot = _worldPlayerRobots[robotString];
                    let robotEnergy = playerRobot.energy || 0;
                    let robotEnergyMax = playerRobot.energyMax || 0;
                    let robotWeapons = playerRobot.weapons || 0;
                    let robotWeaponsMax = playerRobot.weaponsMax || 0;
                    let itemEnergyRecovery = itemIndexInfo.recovery || 0;
                    let itemWeaponsRecovery = itemIndexInfo.recovery2 || 0;
                    //console.log('-> checking robot:', robotString, playerRobot);
                    //console.log('-> playerRobot[energy] =', robotEnergy);
                    //console.log('-> playerRobot[energyMax] =', robotEnergyMax);
                    //console.log('-> playerRobot[weapons] =', robotWeapons);
                    //console.log('-> playerRobot[weaponsMax] =', robotWeaponsMax);
                    //console.log('-> itemEnergyRecovery =', itemEnergyRecovery);
                    //console.log('-> itemWeaponsRecovery =', itemWeaponsRecovery);
                    if (robotEnergy >= robotEnergyMax && robotWeapons >= robotWeaponsMax){
                        //console.log('-> skipping robot', robotString, 'b/c it already has max energy and weapons');
                        continue; // skip this robot if it already has max of both stats
                        }
                    //console.log('-> found a robot (', playerRobot, ') that needs their energy and/or weapons restored...');
                    //console.log('-> giving them the item:', itemEventToken, itemEventInfo, itemToken);
                    if (robotEnergy < robotEnergyMax){ _self.restoreRobotEnergy(robotString, itemEnergyRecovery); }
                    if (robotWeapons < robotWeaponsMax){ _self.restoreRobotWeapons(robotString, itemWeaponsRecovery); }
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    messageMarkup.push('Used ' + itemNameTextSpan + ' on team robot ' + _self.getRobotNameSpan(playerRobot.token) + '!');
                    if (!itemEventQuantity){ break; } // exit the loop early if none left
                    }
                }
            // Check if the item was an extra life item (revives fallen robot w/ life and weapon energy restored to half)
            // and apply it to the first robot that's been disabled (and/or life energy is zero), else pocket it
            else if (itemToken === 'extra-life'){
                //console.log('oh this is an extra-life item, so let us apply it');
                // Loop through player robots and see if any of them "need" this item
                let playerRobotKeys = Object.keys(_worldPlayerRobots);
                for (let i = 0; i < playerRobotKeys.length; i++){
                    let robotString = playerRobotKeys[i];
                    let playerRobot = _worldPlayerRobots[robotString];
                    let robotEnergy = playerRobot.energy || 0;
                    let robotEnergyMax = playerRobot.energyMax || 0;
                    let robotEnergyPercent = playerRobot.energyPercent || 0;
                    let robotWeapons = playerRobot.weapons || 0;
                    let robotWeaponsMax = playerRobot.weaponsMax || 0;
                    let robotWeaponsPercent = playerRobot.weaponsPercent || 0;
                    let itemEnergyRecovery = itemIndexInfo.recovery || 0;
                    let itemWeaponsRecovery = itemIndexInfo.recovery2 || 0;
                    //console.log('-> checking robot:', robotString, playerRobot);
                    //console.log('-> playerRobot[energy] =', robotEnergy);
                    //console.log('-> playerRobot[energyMax] =', robotEnergyMax);
                    //console.log('-> playerRobot[energyPercent] =', robotEnergyPercent);
                    //console.log('-> playerRobot[weapons] =', robotWeapons);
                    //console.log('-> playerRobot[weaponsMax] =', robotWeaponsMax);
                    //console.log('-> playerRobot[weaponsPercent] =', robotWeaponsPercent);
                    //console.log('-> playerRobot[disabled] =', playerRobot.disabled);
                    //console.log('-> itemEnergyRecovery =', itemEnergyRecovery);
                    //console.log('-> itemWeaponsRecovery =', itemWeaponsRecovery);
                    if (robotEnergy > 0 && playerRobot.disabled !== true){
                        //console.log('-> skipping robot', robotString, 'b/c it is not disabled and has some energy');
                        continue; // skip this robot if it is not disabled and has some energy
                        }
                    //console.log('-> found a robot (', playerRobot, ') that needs to be revived...');
                    //console.log('-> giving them the item:', itemEventToken, itemEventInfo, itemToken);
                    _self.setRobotEnergy(robotString, 0); // just to match below technically
                    _self.setRobotWeapons(robotString, 0); // so that we see it fill-up from zero
                    _self.restoreRobotEnergy(robotString, itemEnergyRecovery, true);
                    _self.restoreRobotWeapons(robotString, itemWeaponsRecovery, true);
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    messageMarkup.push('Used ' + itemNameTextSpan + ' on team robot ' + _self.getRobotNameSpan(playerRobot.token) + '!');
                    if (!itemEventQuantity){ break; } // exit the loop early if none left
                    }
                }

            }
        // As a fallback, check to see if this item can be held so we can maybe give it to a team robot
        else if (!itemEvent.claimed
            && numPlayerRobots > 0
            && _self.itemIsHoldable(itemToken)
            && _world.autoEquipHoldables === true){

            // Loop through the player's robots and try to find one that isn't holding anything yet
            let playerRobotKeys = Object.keys(_worldPlayerRobots);
            for (let i = 0; i < playerRobotKeys.length; i++){
                let robotString = playerRobotKeys[i];
                let playerRobot = _worldPlayerRobots[robotString];
                //console.log('-> checking ', robotString, 'for item...', '\n-> playerRobot:', playerRobot);
                if (playerRobot.item && playerRobot.item.length){
                    //console.log('-> skipping robot', robotString, 'b/c already holding ', playerRobot.item);
                    continue; // skip this robot if it is already holding an item
                    }
                //console.log('-> found a robot (', playerRobot, ') that can hold an item...');
                //console.log('-> giving them the item:', itemEventToken, itemEventInfo, itemToken);
                if (_self.giveRobotItem(robotString, itemToken)){
                    _self.addItemToInventory(itemToken, 1);
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    messageMarkup.push('Gave ' + itemNameTextSpan + ' to team robot ' + _self.getRobotNameSpan(playerRobot.token) + '!');
                    }
                if (!itemEventQuantity){ break; } // exit the loop early if none left
                }
            //console.warn('TEMP DISABLED (B) so we can program hold-item functionality');
            //return false;

            }

        // We should also check to see if this is a force star and collect it properly if so
        if (!itemEvent.claimed
            && _self.itemIsStarforce(itemToken)
            && typeof itemEventInfo['subtoken'] !== 'undefined'){
            //console.log('-> item is a force star, so skip adding to inventory and collect properly');
            let starToken = itemEventInfo['subtoken'] || false;
            let starInfo = _mmrpgStarsIndex[starToken] || false;
            let starClaimed = _worldPlayerStars[starToken] || false;
            //console.log('-> starToken = ', starToken);
            //console.log('-> starInfo = ', starInfo);
            //console.log('-> starClaimed = ', starClaimed);
            //console.log('-> collecting a star w/', '\n-> starToken =', starToken, '\n-> starInfo =', starInfo, '\n-> starClaimed =', starClaimed);
            let oldStarQuantity = 0, newStarQuantity = 0;
            oldStarQuantity = Object.keys(_worldPlayerStars).length;
            if (starToken && starInfo){
                let claimDate = Math.floor(Date.now() / 1000);
                //console.log('--> adding the star to the player\'s collect w/ claimDate =', claimDate);
                if (!starClaimed){
                    _worldPlayerStars[starToken] = claimDate;
                    //console.log('_worldPlayerStars[', starToken, '] = ', claimDate, ';');
                    newStarQuantity = Object.keys(_worldPlayerStars).length;
                    } else {
                    newStarQuantity = oldStarQuantity;
                    }
                itemEvent.claimed = true;
                itemEventQuantity--;
                let starNameTextSpan = _self.getCustomNameSpan(starInfo.name + ' Star', starInfo.type);
                let starCountTextSpan = _self.getCustomNameSpan(oldStarQuantity + ' &raquo; <b>' + newStarQuantity + '</b>', 'empty');
                //console.log('--> adding ' + starNameTextSpan + ' to the collection! (' + starCountTextSpan + ')');
                messageMarkup = [];
                messageMarkup.push('Found the ' + starNameTextSpan + '!');
                messageMarkup.push('Added ' + itemNameTextSpan + ' to collection! (' + starCountTextSpan + ')');
                postPickupFunction = function(){
                    console.warn('running the post-pickup function!');
                    let $progressTracker = _elements.progressTracker;
                    let $starCounter = $('.counter.stars[data-count]', $progressTracker);
                    if (!$starCounter || !$starCounter.length){
                        let starCounterMarkup = '<div class="counter stars" data-count="0">'
                                + '<span class="icon"><i class="fa fa-star"></i><i class="fa fa-times"></i></span>'
                                + '<strong class="count">0</strong>'
                            + '</div>';
                        $progressTracker.prepend(starCounterMarkup);
                        $starCounter = $('.counter.stars[data-count]', $progressTracker);
                        }
                    let $starCounterValue = $('.count', $starCounter);
                    $starCounterValue.text(newStarQuantity);
                    $starCounter.attr('data-count', );
                    $starCounterValue.text(newStarQuantity);
                    };
                }
            }

        // If the item has still not been claimed it, it means we should (try to) add it to the inventory instead
        if (!itemEvent.claimed){
            //console.log('-> no robots needed this item, so we will add it to the inventory instead');
            let oldItemQuantity = 0, newItemQuantity = 0;
            oldItemQuantity = _self.getPlayerItemQuantity(itemToken);
            if (_self.addItemToInventory(itemToken, itemEventQuantity)){
                newItemQuantity = _self.getPlayerItemQuantity(itemToken);
                // only remove if inventory function returns true, that way full-stock players leave it behind
                itemEvent.claimed = true;
                itemEventQuantity--;
                let itemCountTextSpan = _self.getCustomNameSpan(oldItemQuantity + ' &raquo; <b>' + newItemQuantity + '</b>', 'empty');
                messageMarkup.push('Added ' + itemNameTextSpan + ' to inventory! (' + itemCountTextSpan + ')');
                }
            }

        // Show the world notification message for this pickup event now
        _self.showWorldMessage(messageMarkup);

        // Update the real copy with any changes to claimed flag
        if (itemEvent.claimed){
            //console.log('updating _worldItemStates with claim time');
            let claimTime = new Date().getTime();
            itemEventInfo.claimed = true;
            _worldItemStates[itemEventToken] = claimTime; // update world item states w/ claim time
            }

        // If an onComplete function was provided, call it now (with delay if requested)
        if (onComplete){
            if (!afterDelay){ onComplete.call(_self); }
            else { setTimeout(function(){ onComplete.call(_self); }, afterDelay); }
            }

        };
    // Now we can remove the zoom and delete the item sprite from the events layer
    //console.log('-> zooming and queueing pickup function for item sprite on map');
    _self.incZoomLevel();
    if (playSound){
        _self.playSoundEffect('pickup-sound');
        let playAutoSound = false;
        if (_self.itemIsConsumable(itemToken) && _world.autoApplyConsumables === true){ playAutoSound = true; }
        else if (_self.itemIsHoldable(itemToken) && _world.autoEquipHoldables === true){ playAutoSound = true; }
        if (playAutoSound){ _self.playSoundEffect('confirm-sound'); }
        }
    _world.isBusyWith.itemPickup = true;
    setTimeout(function(){
        // call the pickup function to apply the item effects
        pickupFunction(function(){
            //console.log('--> resetting zoom level and team sprite classes');
            //console.log('--> $teamSprites =', $teamSprites);
            _self.resetZoomLevel();
            $itemEventLayer.removeClass('has-zoom');
            $teamSprites.removeClass('shake');
            $teamSprites.filter(':not(.disabled):not(.frame-lock)').attr('data-frame', '00');
            if (!itemEventQuantity){
                //console.log('--> removing item sprite from the map', '\n--> b/c itemEventQuantity =', itemEventQuantity);
                $itemEventSprite.animate({opacity: 0, filter: 'brightness(2)'}, zoomDelay, function(){
                    $itemEventSprite.remove();
                    if (typeof postPickupFunction === 'function'){ postPickupFunction.call(_self); }
                    });
                } else {
                if (typeof postPickupFunction === 'function'){ postPickupFunction.call(_self); }
                }
            // Trigger a save of the world state to persist this change
            delete _world.isBusyWith.itemPickup;
            _self.saveWorldState();
            });
        }, (zoomDelay * 2));
    // Return true on success
    return true;
    }
// Define a quick function for adding an item to the player's inventory if there's room for it
function addItemToInventory(itemToken, itemQuantity, animatePickup, playSound, customOptions){
    //console.log('%c' + 'mmrpgWorldMap.addItemToInventory(item:' + itemToken + ', quantity:' + itemQuantity + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('addItemToInventory() missing required itemToken!'); return false; }
    if (typeof itemQuantity !== 'number' || isNaN(itemQuantity) || itemQuantity < 1){ itemQuantity = 1; }
    if (typeof animatePickup !== 'boolean'){ animatePickup = true; } // default to true if not provided
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    if (typeof customOptions !== 'object'){ customOptions = {}; } // default to false if not provided
    //console.log('--> itemToken =', itemToken);
    //console.log('--> itemQuantity =', itemQuantity);
    //console.log('--> animatePickup =', animatePickup);
    //console.log('--> playSound =', playSound);
    //console.log('--> customOptions =', customOptions);
    // Collect references to world objects
    let _self = this;
    let _world = _self.state;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _mmrpgTypesIndex = _indexes.types;
    let _mmrpgTypesIndexKeys = _mmrpgTypesIndex.getTokens();
    let _mmrpgTypesIndexKeysRevised = ['none', 'copy', 'energy', 'weapons', 'attack', 'defense', 'speed'].concat(_mmrpgTypesIndexKeys).filter(function(value, index, self){ return self.indexOf(value) === index; });
    let _mmrpgItemsIndex = _indexes.items;
    let _mmrpgItemsIndexKeys = _mmrpgItemsIndex.getTokens();
    //console.log('--> _mmrpgTypesIndexKeys =', _mmrpgTypesIndexKeys);
    //console.log('--> _mmrpgTypesIndexKeysRevised =', _mmrpgTypesIndexKeysRevised);
    //console.log('--> _mmrpgItemsIndexKeys =', _mmrpgItemsIndexKeys);
    // Back up the real item token in case we need it later
    let realItemToken = itemToken;
    if (realItemToken.indexOf('__') !== -1){ realItemToken = realItemToken.split('__')[0]; }
    if (typeof _mmrpgItemsIndex[realItemToken] === 'undefined'){
        console.error('addItemToInventory() could not find item in index for token ' + itemToken + (itemToken !== realItemToken ? ('/' + realItemToken) : '') + '!');
        return false;
        }
    let itemIndexInfo = _mmrpgItemsIndex[realItemToken];
    if (typeof itemIndexInfo === 'undefined'){ console.error('addItemToCollection() could not find item in index for token ' + realItemToken + '!'); return false; }
    //console.log('--> itemIndexInfo =', itemIndexInfo);
    let $thisWorld = _elements.world;
    let $robotsOverview = _elements.robotsOverview;
    let $storageItemsButton = $('.storage-button[data-view="items"]', $robotsOverview);
    let $storageItemsOverview = $('.storage-box[data-storage="items"]', $robotsOverview);
    let $storageItemsWrapper = $('> .wrapper', $storageItemsOverview);
    let $storageItemInOverview = $('.team-item[data-item="' + realItemToken + '"]', $storageItemsWrapper);
    let _worldPlayer = _world.player;
    let _worldPlayerItems = _worldPlayer.items;
    let itemInventoryMax = _config.playerInventoryMax; // 99;
    // Create an entry in the items index if it does not already exist
    if (typeof _worldPlayerItems[itemToken] === 'undefined'){ _worldPlayerItems[itemToken] = 0; }
    // Check to see if there's room for the item in the inventory
    let currentItemQuantity = _worldPlayerItems[itemToken];
    let equippedItemQuantity = _worldPlayerItems[itemToken + '__equipped'];
    if (!currentItemQuantity){ currentItemQuantity = 0; }
    if (!equippedItemQuantity){ equippedItemQuantity = 0; }
    //console.log('--> currentItemQuantity =', currentItemQuantity);
    //console.log('--> equippedItemQuantity =', equippedItemQuantity);
    if (currentItemQuantity >= itemInventoryMax){
        console.warn('addItemToInventory() called for item that is already at max quantity!');
        return false; // no room in the inventory
        }
    // If there is room, add the item to the inventory
    let overflowQuantity = 0;
    let newItemQuantity = currentItemQuantity + itemQuantity;
    //console.log('--> newItemQuantity =', newItemQuantity);
    if (newItemQuantity > itemInventoryMax){
        console.warn('--> inventory for item', itemToken, 'is full, so capping at max value');
        overflowQuantity = newItemQuantity - itemInventoryMax; // calculate overflow
        newItemQuantity = itemInventoryMax; // cap at max value
        //console.log('--> overflowQuantity =', overflowQuantity);
        //console.log('--> newItemQuantity(adjusted) =', newItemQuantity);
        // TODO: do something with overflow later b/c right now we don't wanna
        }
    _worldPlayerItems[itemToken] = newItemQuantity;
    let displayedItemQuantity = newItemQuantity - equippedItemQuantity;
    //console.log('--> updated _worldPlayerItems['+itemToken+'] => ', _worldPlayerItems[itemToken]);
    // If an animation was requested, make sure we show it above the player's head
    if (animatePickup){
        // TODO: write this animation code later
        //console.warn('addItemToInventory() would animate the item pickup now...'); // TODO: read the message
        }
    // If a sound was requested, play it now
    if (playSound){ _self.playSoundEffect('get-item'); }
    // If this item exists in the robot overview, update the quantity there as well, else create it
    if ($storageItemInOverview.length){
        $storageItemInOverview.attr('data-quantity', displayedItemQuantity);
        $storageItemInOverview.find('.quantity').html('&times; ' + displayedItemQuantity);
        } else {
        let itemName = itemIndexInfo.name;
        let itemImageSize = itemIndexInfo.imageSize;
        let itemTypes = [];
        if (itemIndexInfo.type){ itemTypes.push(itemIndexInfo.type); }
        if (itemIndexInfo.type2){ itemTypes.push(itemIndexInfo.type2); }
        let itemTypeClasses = 'type ' + (itemTypes.length ? itemTypes.join(' ') : 'none');
        let animationDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
        let itemSpriteStyles = 'animation-delay: ' + animationDelay + 's;';
        let itemNameMarkup = itemName.replace(' ', '<br />');
        let newIndexKey = _mmrpgItemsIndexKeys.indexOf(itemToken) || 0;
        let newTypeKey = _mmrpgTypesIndexKeysRevised.indexOf(itemIndexInfo.type) || 0;
        let newStorageKey = $('.team-item[data-item]', $storageItemsWrapper).length || 0;
        if (itemIndexInfo.type2){ newTypeKey += (_mmrpgTypesIndexKeysRevised.indexOf(itemIndexInfo.type2) / 100); }
        let itemButtonConfig = {new: true, slot: 0, indexKey: newIndexKey, storageKey: newStorageKey, typeKey: newTypeKey};
        let storageItemMarkup = _self.generateItemSelectButtonMarkup(itemToken, null, itemButtonConfig);
        $storageItemsWrapper.append(storageItemMarkup);
        $storageItemInOverview = $('.team-item[data-item="' + realItemToken + '"]', $storageItemsWrapper);
        $storageItemsButton.addClass('new');
        }
    // Trigger a save of the world state to persist this change
    //console.log('-> checking if we should reload the world on save');
    let reloadWorldOnSave = false;
    //console.log('-> itemToken =', itemToken);
    if (realItemToken.indexOf('-heart') !== -1){ reloadWorldOnSave = true; } // limit hearts always reload the world
    //console.log('-> reloadWorldOnSave =', reloadWorldOnSave);
    if (reloadWorldOnSave){
        $thisWorld.addClass('busy');
        }
    _self.saveWorldState(function(){
        //console.log('saveWorldState (via addItemToInventory) complete!');
        //console.log('-> reloadWorldOnSave =', reloadWorldOnSave);
        // maybe reload the page to update the inventory display
        if (reloadWorldOnSave){
            //console.log('-> reloading the world now...');
            $thisWorld.addClass('loading');
            window.location.reload();
            }
        }, true, !reloadWorldOnSave);
    // Return true on success
    return true;
    }

// Define a quick function for getting the current quantity of an item in the player's inventory
function getItemQuantity(itemToken, excludeEquipped){
    //console.log('%c' + 'mmrpgWorldMap.getItemQuantity(item:' + itemToken + ', excludeEquipped:' + excludeEquipped + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('getItemQuantity() missing required itemToken!'); return 0; }
    if (typeof excludeEquipped !== 'boolean'){ excludeEquipped = true; } // default to true if not provided

    // Collect references to world objects
    let _self = this;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerItems = _worldPlayer.items;

    // Check to see how many of this item are currently in the inventory
    let currentItemQuantity = _worldPlayerItems[itemToken];
    let equippedItemQuantity = _worldPlayerItems[itemToken + '__equipped'];

    if (!currentItemQuantity){ currentItemQuantity = 0; }
    if (!equippedItemQuantity){ equippedItemQuantity = 0; }

    // Calculate the actual available quantity based on the exclude flag
    let finalItemQuantity = currentItemQuantity;
    if (excludeEquipped){
        finalItemQuantity = currentItemQuantity - equippedItemQuantity;
        }

    // Prevent returning negative values just in case
    if (finalItemQuantity < 0){ finalItemQuantity = 0; }

    // Return the calculated quantity
    return finalItemQuantity;
    }

// Define a core function for setting the base quantity of an item in the player's inventory
function setItemQuantity(itemToken, newQuantity){
    //console.log('%c' + 'mmrpgWorldMap.setItemQuantity(item:' + itemToken + ', newQuantity:' + newQuantity + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('setItemQuantity() missing required itemToken!'); return 0; }
    if (typeof newQuantity !== 'number' || isNaN(newQuantity)){ newQuantity = 0; }
    // Collect references to world objects
    let _self = this;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerItems = _worldPlayer.items;
    // Prevent negative inventory amounts
    if (newQuantity < 0){ newQuantity = 0; }
    // Update the inventory state
    _worldPlayerItems[itemToken] = newQuantity;
    // Return the newly updated quantity
    return _worldPlayerItems[itemToken];
}

// Helper function to increment an item's quantity
function incrementItemQuantity(itemToken, amount){
    //console.log('%c' + 'mmrpgWorldMap.incrementItemQuantity(item:' + itemToken + ', amount:' + amount + ')', 'color: magenta;');
    if (typeof amount !== 'number' || isNaN(amount) || amount <= 0){ amount = 1; }
    // Get total current quantity (without excluding equipped items)
    let currentTotal = this.getItemQuantity(itemToken, false);
    return this.setItemQuantity(itemToken, currentTotal + amount);
}

// Helper function to decrement an item's quantity
function decrementItemQuantity(itemToken, amount){
    //console.log('%c' + 'mmrpgWorldMap.decrementItemQuantity(item:' + itemToken + ', amount:' + amount + ')', 'color: magenta;');
    if (typeof amount !== 'number' || isNaN(amount) || amount <= 0){ amount = 1; }
    // Get total current quantity (without excluding equipped items)
    let currentTotal = this.getItemQuantity(itemToken, false);
    return this.setItemQuantity(itemToken, currentTotal - amount);
}

// Define a quick function for getting the overview details for a given item in the user's inventory
function getItemDetailsForOverview(itemToken, targetSelected){
    //console.log('%c' + 'mmrpgWorldMap.getItemDetailsForOverview(item:' + itemToken + ', targetSelected:' + targetSelected + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('getItemDetailsForOverview() missing required itemToken!'); return ''; }
    if (typeof targetSelected !== 'boolean'){ targetSelected = false; } // default to false if not provided

    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerItems = _worldPlayer.items;
    let _indexes = _self.indexes;
    let _mmrpgItemsIndex = _indexes.items;
    if (typeof _mmrpgItemsIndex[itemToken] === 'undefined'){ console.error('getItemDetailsForOverview() could not find item in index for token ' + itemToken + '!'); return false; }
    let itemIndexInfo = _mmrpgItemsIndex[itemToken];
    //console.log('--> itemIndexInfo =', itemIndexInfo);
    let selectedPlayerRobot = targetSelected ? _self.getSelectedRobotInOverview(true) : false;
    let selectedPlayerRobotData = selectedPlayerRobot && selectedPlayerRobot.data ? selectedPlayerRobot.data : false;
    let selectedPlayerRobotInfo = selectedPlayerRobot && selectedPlayerRobot.info ? selectedPlayerRobot.info : false;
    //console.log('--> selectedPlayerRobot =', selectedPlayerRobot);
    let itemIsUnlocked = typeof _worldPlayerItems[itemToken] !== 'undefined' ? true : false;
    //console.log('--> itemIsUnlocked =', itemIsUnlocked);
    let itemIsEquipped = selectedPlayerRobot && selectedPlayerRobotData.item === itemIndexInfo.token ? true : false;
    //console.log('--> itemIsEquipped =', itemIsEquipped);

    // Generate the markup, classes, styles, etc. that will make up the item details
    let itemTitle = 'Item Details';
    let itemName = itemIndexInfo.name;
    let itemDescription = itemIndexInfo.description;
    let itemType1 = itemIndexInfo.type || 'none';
    let itemType2 = itemIndexInfo.type2 || false;
    let currentItemQuantity = _worldPlayerItems[itemToken] || 0;
    let equippedItemQuantity = _worldPlayerItems[itemToken + '__equipped'] || 0;
    let itemQuantity = currentItemQuantity - equippedItemQuantity;
    let itemImageSize = itemIndexInfo.imageSize;
    let itemKind = itemIndexInfo.subclass;
    //console.log('--> itemName =', itemName);
    //console.log('--> itemDescription =', itemDescription);
    //console.log('--> itemType1 =', itemType1);
    //console.log('--> itemType2 =', itemType2);
    //console.log('--> currentItemQuantity =', currentItemQuantity);
    //console.log('--> equippedItemQuantity =', equippedItemQuantity);
    //console.log('--> itemQuantity =', itemQuantity);
    //console.log('--> itemImageSize =', itemImageSize);
    //console.log('--> itemKind =', itemKind);
    //console.log('--> statTokens =', statTokens);

    // Generate type-related markup and spans for use later
    let statTokens = ['attack', 'defense', 'speed'];
    let statConsumables = ['pellet', 'capsule', 'tank'];
    let tokenFrags = itemToken.indexOf('-') !== -1 ? itemToken.split('-') : [itemToken, itemToken];
    let itemTypeClasses = (itemType2 && itemType1 === 'none' ? itemType2 : (itemType1 + (itemType2 ? '_' + itemType2 : '')));
    let isStatItem = (statTokens.indexOf(itemType1) !== -1 || itemToken.indexOf('super-') === 0 ? true : false);
    let isSuperItem = (itemToken.indexOf('super-') === 0 ? true : false);
    let isStatConsumable = statConsumables.indexOf(tokenFrags[1]) !== -1 ? true : false;
    //console.log('--> itemTypeClasses =', itemTypeClasses);
    //console.log('tokenFrags =', tokenFrags);
    //console.log('isStatItem =', isStatItem);
    //console.log('isSuperItem =', isSuperItem);
    //console.log('isStatConsumable =', isStatConsumable);

    // Manually disable consumable items that cannot actually be used on the given robot for contextual reasons
    // TODO: figure out a better place for this maybe?
    let itemIsUnusable = !selectedPlayerRobot ? true : false;
    let itemIsUngivable = !selectedPlayerRobot ? true : false;
    if (itemKind === 'consumable'){
        //console.log('checking if this item should be unusable ...');
        let playerRobot = selectedPlayerRobot, robotData = selectedPlayerRobotData, robotInfo = selectedPlayerRobotInfo;
        let robotIsDisabled = robotData.disabled ? true : false;
        let robotHasFullEnergy = robotData.energy >= robotData.energyMax, robotHasFullWeapons = robotData.weapons >= robotData.weaponsMax;
        let robotHasMaxAttack = robotData.attackMods >= _config.robotStatModMax, robotHasMaxDefense = robotData.defenseMods >= _config.robotStatModMax, robotHasMaxSpeed = robotData.speedMods >= _config.robotStatModMax;
        //console.log('playerRobot =', playerRobot);
        //console.log('-> robotIsDisabled =', robotIsDisabled);
        //console.log('-> robotHasFullEnergy =', robotHasFullEnergy, ' | energy(', robotData.energy, ') vs energyMax(', robotData.energyMax, ')');
        //console.log('-> robotHasFullWeapons =', robotHasFullWeapons, ' | weapons(', robotData.weapons, ') vs weaponsMax(', robotData.weaponsMax, ')');
        //console.log('-> robotHasMaxAttack =', robotHasMaxAttack, ' | attackMods(', robotData.attackMods, ') vs robotStatModMax(', _config.robotStatModMax, ')');
        //console.log('-> robotHasMaxDefense =', robotHasMaxDefense, ' | defenseMods(', robotData.defenseMods, ') vs robotStatModMax(', _config.robotStatModMax, ')');
        //console.log('-> robotHasMaxSpeed =', robotHasMaxSpeed, ' | speedMods(', robotData.speedMods, ') vs robotStatModMax(', _config.robotStatModMax, ')');
        if (robotIsDisabled && itemToken !== 'extra-life'){ itemIsUnusable = true; }
        else if (itemToken === 'yashichi' && (robotHasFullEnergy && robotHasFullWeapons)){ itemIsUnusable = true; }
        else if (isStatConsumable){
            if (tokenFrags[0] === 'energy' && robotHasFullEnergy){ itemIsUnusable = true; }
            else if (tokenFrags[0] === 'weapon' && robotHasFullWeapons){ itemIsUnusable = true; }
            else if (tokenFrags[0] === 'attack' && robotHasMaxAttack){ itemIsUnusable = true; }
            else if (tokenFrags[0] === 'defense' && robotHasMaxDefense){ itemIsUnusable = true; }
            else if (tokenFrags[0] === 'speed' && robotHasMaxSpeed){ itemIsUnusable = true; }
            else if (tokenFrags[0] === 'super' && (robotHasMaxAttack && robotHasMaxDefense && robotHasMaxSpeed)){ itemIsUnusable = true; }
            }
        //console.log('itemIsUnusable =', itemIsUnusable);
        }

    // Determine the icon to use based on the item kind and format the text for display
    let itemKindIcon = 'dot-circle';
    if (itemKind === 'consumable'){ itemKindIcon = 'apple-alt'; }
    else if (itemKind === 'holdable'){ itemKindIcon = 'briefcase'; }
    else if (itemKind === 'collectable'){ itemKindIcon = 'cubes'; }
    else if (itemKind === 'event'){ itemKindIcon = 'bookmark'; }
    else if (itemKind === 'treasure'){ itemKindIcon = 'gem'; }
    let itemKindName = itemKind.charAt(0).toUpperCase() + itemKind.slice(1);
    //console.log('--> itemKindName =', itemKindName);
    //console.log('--> itemKindIcon =', itemKindIcon);

    // Generate the item sprite that will be used in the details
    let randDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
    let itemSpriteClasses = 'sprite item icon';
    let itemSpriteStyles = 'animation-delay: ' + randDelay + 's; ';
    let itemSpriteAttrs = 'data-sprite="item" data-token="' + itemToken + '" data-size="' + itemImageSize + '" data-dir="right" data-frame="00"';
    let itemSprite = '<div class="' + itemSpriteClasses + '" style="' + itemSpriteStyles + '" ' + itemSpriteAttrs + '><span class="wrap"><i class="sprite"></i></span></div>';
    //console.log('--> itemSpriteClasses =', itemSpriteClasses);
    //console.log('--> itemSpriteStyles =', itemSpriteStyles);
    //console.log('--> itemSpriteAttrs =', itemSpriteAttrs);
    //console.log('--> itemSprite =', itemSprite);

    // Collect details about the power level of this item (if relevant)
    let itemDamage = itemIndexInfo.damage || 0;
    let itemDamagePercent = itemIndexInfo.damagePercent || 0;
    let itemRecovery = itemIndexInfo.recovery || 0;
    let itemRecoveryPercent = itemIndexInfo.recoveryPercent || 0;
    let itemDamage2 = itemIndexInfo.damage2 || 0;
    let itemDamage2Percent = itemIndexInfo.damage2Percent || 0;
    let itemRecovery2 = itemIndexInfo.recovery2 || 0;
    let itemRecovery2Percent = itemIndexInfo.recovery2Percent || 0;
    //console.log('--> itemDamage =', itemDamage);
    //console.log('--> itemDamagePercent =', itemDamagePercent);
    //console.log('--> itemRecovery =', itemRecovery);
    //console.log('--> itemRecoveryPercent =', itemRecoveryPercent);
    //console.log('--> itemDamage2 =', itemDamage2);
    //console.log('--> itemDamage2Percent =', itemDamage2Percent);
    //console.log('--> itemRecovery2 =', itemRecovery2);
    //console.log('--> itemRecovery2Percent =', itemRecovery2Percent);

    // Parse any value tags in the description before display
    itemDescription = itemDescription.replace('{DAMAGE}', (itemDamage ? itemDamage : 0));
    itemDescription = itemDescription.replace('{RECOVERY}', (itemRecovery ? itemRecovery : 0));
    itemDescription = itemDescription.replace('{DAMAGE2}', (itemDamage2 ? itemDamage2 : 0));
    itemDescription = itemDescription.replace('{RECOVERY2}', (itemRecovery2 ? itemRecovery2 : 0));

    // Start generating the item details object for the overview
    let itemDetailsObject = {};
    itemDetailsObject.title = itemTitle;
    itemDetailsObject.image = itemSprite;
    itemDetailsObject.name = itemName;
    itemDetailsObject.quantity = itemQuantity;
    itemDetailsObject.typeClasses = itemTypeClasses;
    itemDetailsObject.infoLines = [];
    itemDetailsObject.infoLines.push({
        classes: 'kind',
        label: 'Kind:',
        value: itemKindName,
        valueClasses: itemKind,
        icon: itemKindIcon
        });
    if (itemDamage || itemRecovery){
        let powerLine = { classes: 'power', label: 'Power:', values: [] };
        if (itemDamage){
            let damageValue = {};
            if (!isSuperItem){ damageValue.value = (isStatItem ? '-' : '') + itemDamage + (itemDamagePercent ? '%' : '') + ' ' + (isStatItem ? 'Break' : 'Damage'); }
            else { damageValue.value = (isStatItem ? '-' : '') + (Math.ceil(itemDamage / statTokens.length) + '/').repeat(statTokens.length).replace(/\/$/, '') + (itemDamagePercent ? '%' : '') + ' ' + (isStatItem ? 'Break' : 'Damage'); }
            damageValue.valueClasses = 'damage';
            if (itemType1 === 'weapons'){ damageValue.icon = 'battery-half'; }
            else if (isStatItem){ damageValue.icon = 'caret-square-down'; }
            else { damageValue.icon = 'fist-raised'; }
            powerLine.values.push(damageValue);
            }
        if (itemRecovery){
            let recoveryValue = {};
            if (!isSuperItem){ recoveryValue.value = (isStatItem ? '+' : '') + itemRecovery + (itemRecoveryPercent ? '%' : '') + ' ' + (isStatItem ? 'Boost' : 'Recovery'); }
            else {  recoveryValue.value = (isStatItem ? '+' : '') + (Math.ceil(itemRecovery / statTokens.length) + '/').repeat(statTokens.length).replace(/\/$/, '') + (itemRecoveryPercent ? '%' : '') + ' ' + (isStatItem ? 'Boost' : 'Recovery'); }
            recoveryValue.valueClasses = 'recovery';
            if (itemType1 === 'weapons'){ recoveryValue.icon = 'battery-full'; }
            else if (isStatItem){ recoveryValue.icon = 'caret-square-up'; }
            else { recoveryValue.icon = 'heart'; }
            powerLine.values.push(recoveryValue);
            }
        itemDetailsObject.infoLines.push(powerLine);
        }
    itemDetailsObject.description = itemDescription;

    // ACTION BUTTONS
    let _inputs = _self.inputs;
    let _userInputs = _inputs.getUserInputs();
    let aButtonIcon = _userInputs.A.icon, bButtonIcon = _userInputs.B.icon;
    let xButtonIcon = _userInputs.X.icon, yButtonIcon = _userInputs.Y.icon;
    itemDetailsObject.actions = [];
    let showUseItem = itemKind === 'consumable' ? true : false;
    let showGiveItem = (itemKind === 'consumable' || itemKind === 'holdable') ? true : false;
    let showTakeItem = showGiveItem && itemIsEquipped ? true : false; if (showTakeItem){ showGiveItem = false; }
    let showDropItem = itemKind !== 'event' && itemQuantity > 0 ? true : false;
    if (typeof _worldPlayerItems['item-codes'] === 'undefined' || _worldPlayerItems['item-codes'] < 1){ showUseItem = false; }
    if (typeof _worldPlayerItems['equip-codes'] === 'undefined' || _worldPlayerItems['equip-codes'] < 1){ showGiveItem = false; }
    //console.log('selectedPlayerRobotData = ', selectedPlayerRobotData);
    //console.log('selectedPlayerRobotInfo = ', selectedPlayerRobotInfo);
    itemDetailsObject.actions.push({ action: 'use-item', text: yButtonIcon + ' Use', button: 'Y', item: itemToken, disabled: (!targetSelected || itemIsUnusable), hidden: !showUseItem });
    itemDetailsObject.actions.push({ action: 'give-item', text: xButtonIcon + ' Give', button: 'X', item: itemToken, disabled: (!targetSelected || itemIsEquipped), hidden: !showGiveItem });
    itemDetailsObject.actions.push({ action: 'take-item', text: xButtonIcon + ' Take', button: 'X', item: itemToken, disabled: (!targetSelected || !itemIsEquipped), hidden: !showTakeItem });
    //itemDetailsObject.actions.push({ action: 'drop-item', text: 'Drop', item: itemToken, disabled: targetSelected, hidden: !showDropItem });

    // Pre-compile some of the HTML to make it easier for the other functions
    itemDetailsObject.infolinesHTML = '';
    for (let i = 0; i < itemDetailsObject.infoLines.length; i++){
        let infoLine = itemDetailsObject.infoLines[i];
        itemDetailsObject.infolinesHTML += '<div class="infoline ' + infoLine.classes + '">';
        itemDetailsObject.infolinesHTML += '<strong class="label">' + infoLine.label + '</strong>';
        if (infoLine.values){
            for (let j = 0; j < infoLine.values.length; j++){
                let valueInfo = infoLine.values[j];
                let valueClasses = ('value' + (valueInfo.valueClasses ? (' ' + valueInfo.valueClasses) : '')).trim();
                let valueStyles = ('' + (valueInfo.valueStyles ? (' ' + valueInfo.valueStyles) : '')).trim();
                itemDetailsObject.infolinesHTML += '<span' + (valueClasses ? ' class="' + valueClasses + '"' : '') + (valueStyles ? ' style="' + valueStyles + '"' : '') + '>' + valueInfo.value + '</span>';
                if (valueInfo.icon){ itemDetailsObject.infolinesHTML += '<i class="fa fas fa-' + valueInfo.icon + '"></i>'; }
                }
            } else {
            itemDetailsObject.infolinesHTML += '<span class="value ' + (infoLine.valueClasses ? infoLine.valueClasses : '') + '">' + infoLine.value + '</span>';
            if (infoLine.icon){ itemDetailsObject.infolinesHTML += '<i class="fa fas fa-' + infoLine.icon + '"></i>'; }
            }
        itemDetailsObject.infolinesHTML += '</div>';
        }
    itemDetailsObject.actionsHTML = '';
    for (let i = 0; i < itemDetailsObject.actions.length; i++){
        let actionInfo = itemDetailsObject.actions[i];
        let actionButton = typeof actionInfo.button !== 'undefined' ? actionInfo.button : false;
        let actionDisabled = typeof actionInfo.disabled === 'boolean' && actionInfo.disabled === true ? true : false;
        let actionHidden = typeof actionInfo.hidden === 'boolean' && actionInfo.hidden === true ? true : false;
        itemDetailsObject.actionsHTML += '<button type="button" '
            + 'class="button ' + actionInfo.action + (actionDisabled ? ' disabled' : '') + (actionHidden ? ' hidden' : '') + '" '
            + 'data-action="' + actionInfo.action + '"'
            + (actionButton ? ' data-button="' + actionButton + '"' : '')
            + (actionDisabled ? ' disabled="disabled"' : '') +
            '>' + actionInfo.text + '</button>';
        }

    // Return the generated item details object
    //console.log('--> itemDetailsObject =', itemDetailsObject);
    return itemDetailsObject;
    }
// Define a quick function for getting the details markup for a given item in the user's inventory
function getItemDetailsMarkupForOverview(itemToken, targetSelected){
    //console.log('%c' + 'mmrpgWorldMap.getItemDetailsMarkupForOverview(item:' + itemToken + ', targetSelected:' + targetSelected + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('getItemDetailsMarkupForOverview() missing required itemToken!'); return ''; }
    if (typeof targetSelected !== 'boolean'){ targetSelected = false; }
    let _self = this;
    let itemDetailsObject = _self.getItemDetailsForOverview(itemToken, targetSelected) || false;
    if (!itemDetailsObject || typeof itemDetailsObject !== 'object'){ console.error('getItemDetailsMarkupForOverview() could not generate details object for token ' + itemToken + '!'); return ''; }
    let itemDetailsMarkup = '';
    itemDetailsMarkup += '<div class="storage-details" data-item="' + itemToken + '">';
        itemDetailsMarkup += '<div class="title">' + itemDetailsObject.title + '</div>';
        itemDetailsMarkup += '<div class="image type ' + itemDetailsObject.typeClasses + '">' + itemDetailsObject.image + '</div>';
        itemDetailsMarkup += '<div class="subtitle">';
            itemDetailsMarkup += '<strong class="name">' + itemDetailsObject.name + '</strong>';
            itemDetailsMarkup += '<strong class="quantity">&times; ' + itemDetailsObject.quantity + '</strong>';
            itemDetailsMarkup += '<hr class="type ' + itemDetailsObject.typeClasses + '">';
        itemDetailsMarkup += '</div>';
        itemDetailsMarkup += '<div class="infolines">' + itemDetailsObject.infolinesHTML + '</div>';
        itemDetailsMarkup += '<div class="description"><p>' + itemDetailsObject.description + '</p></div>';
        itemDetailsMarkup += '<div class="actions">' + itemDetailsObject.actionsHTML + '</div>';
    itemDetailsMarkup += '</div>';
    return itemDetailsMarkup;
    }
// Define a quick function for replacing the item details in an existing details div with new ones
function replaceItemDetailsInOverview($detailsDiv, itemToken, itemDetails){
    //console.log('%c' + 'mmrpgWorldMap.replaceItemDetailsInOverview($detailsDiv, itemToken:' + itemToken + ', itemDetails)', 'color: magenta;');
    if (!$detailsDiv || !$detailsDiv.length){ console.error('replaceItemDetailsInOverview() missing required $detailsDiv!'); return false; }
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('replaceItemDetailsInOverview() missing required itemToken!'); return false; }
    if (!itemDetails || typeof itemDetails !== 'object'){ console.error('replaceItemDetailsInOverview() missing required itemDetails!'); return false; }
    //console.log('-> itemDetails =', itemDetails);
    let $title = $detailsDiv.find('> .title'),
        $image = $detailsDiv.find('> .image'),
        $subtitle = $detailsDiv.find('> .subtitle'),
        $subtitleName = $subtitle.find('> .name'),
        $subtitleQuantity = $subtitle.find('> .quantity'),
        $subtitleSubline = $subtitle.find('> hr'),
        $infolines = $detailsDiv.find('> .infolines'),
        $description = $detailsDiv.find('> .description'),
        $actions = $detailsDiv.find('> .actions')
        ;
    $detailsDiv.attr('data-item', itemToken);
    $title.html(itemDetails.title);
    $image.html(itemDetails.image);
    $image.removeClass().addClass('image type '+ itemDetails.typeClasses);
    $subtitleName.html(itemDetails.name);
    $subtitleQuantity.html('&times; ' + itemDetails.quantity);
    $subtitleSubline.removeClass().addClass('type ' + itemDetails.typeClasses);
    $infolines.html(itemDetails.infolinesHTML);
    $description.html(itemDetails.description);
    //$actions.html(itemDetails.actionsHTML);
    // manually update buttons so css transitions can occur properly
    for (let i = 0; i < itemDetails.actions.length; i++){
        let actionInfo = itemDetails.actions[i];
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
                + 'data-item="' + actionInfo.item + '"'
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

// Quick function for getting a given item's name span already styled
function getItemNameSpan(itemToken, customText){
    //console.log('%c' + 'mmrpgWorldMap.getItemNameSpan(itemToken:' + itemToken + ', customText:' + customText + ')', 'color: magenta;');
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgItemsIndex = _indexes.items;
    if (itemToken.indexOf('__') !== -1){ itemToken = itemToken.split('__')[0]; }
    let itemInfo = _mmrpgItemsIndex[itemToken] || false;
    let itemName = itemInfo ? itemInfo.name : 'Item';
    let itemType1 = itemInfo.type || '';
    let itemType2 = itemInfo.type2 || '';
    let spanTypes = itemType1 !== '' ? itemType1 : (itemType2 !== '' ? itemType2 : 'none');
    return '<span class="type ' + spanTypes + '">' + (customText || itemName) + '</span>';
    };
// Define a quick function for grabbing the sprite markup of an item given the token and optional sprite arguments
function getItemSpriteMarkup(itemToken, spriteOptions){
    //console.log('%c' + 'mmrpgWorldMap.getItemSpriteMarkup(itemToken:' + itemToken + ', spriteOptions:', + JSON.stringify(spriteOptions) + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('getItemSpriteMarkup() missing required itemToken!'); return ''; }
    spriteOptions = spriteOptions || {};
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let itemInfo = _indexes.items.getByToken(itemToken);
    if (!itemInfo || typeof itemInfo !== 'object'){ console.error('getItemSpriteMarkup() could not find itemInfo for token ' + itemToken + '!'); return ''; }
    // Collect or define the sprite options with defaults if not provided
    spriteOptions.sheet = typeof spriteOptions.sheet === 'number' && spriteOptions.sheet > 1 ? spriteOptions.sheet : 1;
    spriteOptions.size = typeof spriteOptions.size === 'number' && spriteOptions.size > 0 ? spriteOptions.size : (itemInfo.imageSize || 40);
    spriteOptions.dir = typeof spriteOptions.dir === 'string' && spriteOptions.dir.length ? spriteOptions.dir : 'right';
    spriteOptions.frame = typeof spriteOptions.frame === 'string' && spriteOptions.frame.length > 0 ? spriteOptions.frame : '00';
    spriteOptions.delay = typeof spriteOptions.delay === 'number' && spriteOptions.delay !== 0 ? spriteOptions.delay : (-1 * ( Math.floor(Math.random() * 10) / 100 ));
    spriteOptions.showBack = typeof spriteOptions.showBack === 'boolean' ? spriteOptions.showBack : false;
    spriteOptions.classes = typeof spriteOptions.classes === 'string' && spriteOptions.classes.length > 1 ? spriteOptions.classes : '';
    spriteOptions.styles = typeof spriteOptions.styles === 'string' && spriteOptions.styles.length > 1 ? spriteOptions.styles : '';
    // Generate the item sprite attributes and inner markup given the options
    let itemSpriteAttrs = '';
    let itemSpriteClass = 'sprite item' + (spriteOptions.classes ? ' ' + spriteOptions.classes : '');
    let itemSpriteStyle = 'animation-delay: ' + spriteOptions.delay + 's;' + (spriteOptions.styles ? ' ' + spriteOptions.styles : '');
    let itemTypeClasses = itemInfo.type === '' ? 'none' : (itemInfo.type + (itemInfo.type2 !== '' ? '_' + itemInfo.type2 : ''));
    itemSpriteAttrs += ' class="' + itemSpriteClass + '"';
    itemSpriteAttrs += ' data-sprite="item"';
    itemSpriteAttrs += ' data-token="' + itemToken + '"';
    itemSpriteAttrs += ' data-sheet="' + spriteOptions.sheet + '"';
    itemSpriteAttrs += ' data-size="' + spriteOptions.size + '"';
    itemSpriteAttrs += ' data-dir="' + spriteOptions.dir + '"';
    itemSpriteAttrs += ' data-frame="' + spriteOptions.frame + '"';
    if (itemSpriteStyle.length){ itemSpriteAttrs += ' style="' + itemSpriteStyle + '"'; }
    let itemSpriteInner = '';
    if (spriteOptions.showBack){ itemSpriteInner += '<i class="back type ' + itemTypeClasses + '"></i>'; }
    itemSpriteInner += '<i class="sprite"></i>';
    // Put it all together to generate the final markup
    let itemSpriteMarkup = '';
    itemSpriteMarkup += '<span' + itemSpriteAttrs + '>';
        itemSpriteMarkup += '<span class="wrap">' + itemSpriteInner + '</span>';
    itemSpriteMarkup += '</span>';
    // Return the generated markup for the item sprite
    return itemSpriteMarkup;
    }

// Define a quick function for showing an item modal for some kind of item-related action
function showItemModal(actionToken, itemToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showItemModal(actionToken:' + actionToken + ', itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showItemModal() missing required actionToken!'); return; }
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showItemModal() missing required itemToken!'); return; }
    let _self = this;
    return _self.showActionModal('item', actionToken, itemToken, targetRobotToken, configCustom);
    }
// Define quick functions for showing specific modals for items
function showUseItemModal(itemToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showUseItemModal(itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showUseItemModal() missing required itemToken!'); return; }
    if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showUseItemModal() missing required targetRobotToken!'); return; }
    let _self = this;
    return _self.showItemModal('use-item', itemToken, targetRobotToken, configCustom);
    }
// Define a quick function for showing the give item modal
function showGiveItemModal(itemToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showGiveItemModal(itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showGiveItemModal() missing required itemToken!'); return; }
    if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showGiveItemModal() missing required targetRobotToken!'); return; }
    let _self = this;
    return _self.showItemModal('give-item', itemToken, targetRobotToken, configCustom);
    }
// Define a quick function for showing the take item modal
function showTakeItemModal(itemToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showTakeItemModal(itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showTakeItemModal() missing required itemToken!'); return; }
    if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showTakeItemModal() missing required targetRobotToken!'); return; }
    let _self = this;
    return _self.showItemModal('take-item', itemToken, targetRobotToken, configCustom);
    }
// Define a quick function for showing the drop item modal
function showDropItemModal(itemToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showDropItemModal(itemToken:' + itemToken + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showDropItemModal() missing required itemToken!'); return; }
    let _self = this;
    return _self.showItemModal('drop-item', itemToken, null, configCustom);
    }

// Define a quick function for generating the markup for an item select button given an item token, robot info, and/or optional settings
function generateItemSelectButtonMarkup(itemToken, playerRobotInfo, buttonOptions){
    //console.log('%c' + 'mmrpgWorldMap.generateItemSelectButtonMarkup(itemToken:' + itemToken + ', playerRobotInfo, buttonOptions)', 'color: magenta;');
    //console.log('-> w/ playerRobotInfo =', playerRobotInfo);
    //console.log('-> w/ buttonOptions =', buttonOptions);
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('generateItemSelectButtonMarkup() missing required itemToken!'); return ''; }
    if (!playerRobotInfo || typeof playerRobotInfo !== 'object'){ playerRobotInfo = null; }
    if (!buttonOptions || typeof buttonOptions !== 'object'){ buttonOptions = {}; }
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerItems = _worldPlayer.items;
    let itemInfo = _indexes.items.getByToken(itemToken);
    if (!itemInfo || typeof itemInfo !== 'object'){ console.error('generateItemSelectButtonMarkup() could not find itemInfo for token ' + itemToken + '!'); return ''; }

    buttonOptions.indexKey = typeof buttonOptions.indexKey === 'number' ? buttonOptions.indexKey : false;
    buttonOptions.storageKey = typeof buttonOptions.storageKey === 'number' ? buttonOptions.storageKey : false;
    buttonOptions.typeKey = typeof buttonOptions.typeKey === 'number' ? buttonOptions.typeKey : false;
    buttonOptions.new = typeof buttonOptions.new !== 'undefined' ? buttonOptions.new : false;
    buttonOptions.selected = typeof buttonOptions.selected !== 'undefined' ? buttonOptions.selected : false;
    buttonOptions.disabled = typeof buttonOptions.disabled !== 'undefined' ? buttonOptions.disabled : false;
    buttonOptions.slot = typeof buttonOptions.slot === 'number' ? buttonOptions.slot : false;

    let itemAnimationDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
    let itemTypeClasses = (itemInfo.type2 && !itemInfo.type ? itemInfo.type2 : (itemInfo.type + (itemInfo.type2 ? '_' + itemInfo.type2 : '')));

    let currentItemQuantity = _worldPlayerItems[itemToken] || 0;
    let equippedItemQuantity = _worldPlayerItems[itemToken + '__equipped'] || 0;
    let itemQuantity = currentItemQuantity - equippedItemQuantity;

    let itemNameFormatted = itemInfo.name.split(' ').join('<br />');
    let itemQuantityFormatted = '&times; ' + itemQuantity;

    let buttonAttrs = '';
    let buttonClass = 'team-item' + (buttonOptions.new ? ' new' : '') + (buttonOptions.selected ? ' selected' : '') + (buttonOptions.disabled ? ' disabled' : '');
    let buttonStyle = '';
    buttonAttrs += ' class="' + buttonClass + '"';
    buttonAttrs += ' data-item="' + itemToken + '"';
    buttonAttrs += ' data-item-id="' + itemInfo.id + '"';
    buttonAttrs += ' data-energy-cost="' + (itemInfo.energy || 0) + '"';
    buttonAttrs += ' data-quantity="' + itemQuantity + '"';
    if (buttonOptions.indexKey !== false){ buttonAttrs += ' data-index-key="' + buttonOptions.indexKey + '"'; }
    if (buttonOptions.storageKey !== false){ buttonAttrs += ' data-storage-key="' + buttonOptions.storageKey + '"'; }
    if (buttonOptions.typeKey !== false){ buttonAttrs += ' data-type-key="' + buttonOptions.typeKey + '"'; }
    if (buttonOptions.slot !== false){ buttonAttrs += ' data-slot="' + buttonOptions.slot + '"'; }
    if (buttonStyle.length){ buttonAttrs += ' style="' + buttonStyle + '"'; }

    let buttonSpriteAttrs = '';
    let buttonSpriteClass = 'sprite item icon';
    let buttonSpriteStyle = 'animation-delay: ' + itemAnimationDelay + 's;';
    buttonSpriteAttrs += ' class="' + buttonSpriteClass + '"';
    buttonSpriteAttrs += ' data-sprite="item"';
    buttonSpriteAttrs += ' data-token="' + itemToken + '"';
    buttonSpriteAttrs += ' data-alt=""';
    buttonSpriteAttrs += ' data-size="40"';
    buttonSpriteAttrs += ' data-dir="right"';
    buttonSpriteAttrs += ' data-frame="00"';
    if (buttonSpriteStyle.length){ buttonSpriteAttrs += ' style="' + buttonSpriteStyle + '"'; }
    let buttonSpriteInner = '<span class="wrap"><i class="sprite"></i></span>';

    let buttonMarkup = '';
    buttonMarkup += '<a' + buttonAttrs + '>';
        buttonMarkup += '<div class="image type ' + itemTypeClasses + '">';
            buttonMarkup += '<span' + buttonSpriteAttrs + '>' + buttonSpriteInner + '</span>';
        buttonMarkup += '</div>';
        buttonMarkup += '<strong class="name">' + itemNameFormatted + '</strong>';
        buttonMarkup += '<span class="quantity">' + itemQuantityFormatted + '</span>';
    buttonMarkup += '</a>';

    return buttonMarkup;
    }
// Define a quick function for generating the markup for an item select placeholder given robot info, and/or optional settings
function generateItemSelectPlaceholderMarkup(playerRobotInfo, buttonOptions){
    //console.log('%c' + 'mmrpgWorldMap.generateItemSelectPlaceholderMarkup(playerRobotInfo, buttonOptions)', 'color: magenta;');
    //console.log('-> w/ playerRobotInfo =', playerRobotInfo);
    //console.log('-> w/ buttonOptions =', buttonOptions);
    if (!playerRobotInfo || typeof playerRobotInfo !== 'object'){ playerRobotInfo = null; }
    if (!buttonOptions || typeof buttonOptions !== 'object'){ buttonOptions = {}; }
    let _self = this;
    let _config = _self.config;
    buttonOptions.selected = typeof buttonOptions.selected !== 'undefined' ? buttonOptions.selected : false;
    buttonOptions.disabled = typeof buttonOptions.disabled !== 'undefined' ? buttonOptions.disabled : false;
    buttonOptions.slot = typeof buttonOptions.slot === 'number' ? buttonOptions.slot : false;
    let buttonAttrs = '';
    buttonAttrs += ' data-item="item"';
    let buttonClass = 'team-item placeholder' + (buttonOptions.selected ? ' selected' : '') + (buttonOptions.disabled ? ' disabled' : '');
    let buttonStyle = '';
    buttonAttrs += ' class="' + buttonClass + '"';
    if (buttonOptions.slot !== false){ buttonAttrs += ' data-slot="' + buttonOptions.slot + '"'; }
    if (buttonStyle.length){ buttonAttrs += ' style="' + buttonStyle + '"'; }
    let buttonMarkup = '';
    buttonMarkup += '<div' + buttonAttrs + '>';
        buttonMarkup += '<span class="tint type empty"></span>';
    buttonMarkup += '</div>';
    return buttonMarkup;
    }

// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.itemIsConsumable = itemIsConsumable;
mmrpgWorldMap.prototype.itemIsHoldable = itemIsHoldable;
mmrpgWorldMap.prototype.itemIsStarforce = itemIsStarforce;
mmrpgWorldMap.prototype.itemIsEvent = itemIsEvent;

mmrpgWorldMap.prototype.triggerItemPickup = triggerItemPickup;
mmrpgWorldMap.prototype.addItemToInventory = addItemToInventory;

mmrpgWorldMap.prototype.getItemQuantity = getItemQuantity;
mmrpgWorldMap.prototype.setItemQuantity = setItemQuantity;
mmrpgWorldMap.prototype.incrementItemQuantity = incrementItemQuantity;
mmrpgWorldMap.prototype.decrementItemQuantity = decrementItemQuantity;

mmrpgWorldMap.prototype.getItemDetailsForOverview = getItemDetailsForOverview;
mmrpgWorldMap.prototype.getItemDetailsMarkupForOverview = getItemDetailsMarkupForOverview;
mmrpgWorldMap.prototype.replaceItemDetailsInOverview = replaceItemDetailsInOverview;

mmrpgWorldMap.prototype.getItemNameSpan = getItemNameSpan;
mmrpgWorldMap.prototype.getItemSpriteMarkup = getItemSpriteMarkup;

mmrpgWorldMap.prototype.showItemModal = showItemModal;
mmrpgWorldMap.prototype.showUseItemModal = showUseItemModal;
mmrpgWorldMap.prototype.showGiveItemModal = showGiveItemModal;
mmrpgWorldMap.prototype.showTakeItemModal = showTakeItemModal;
mmrpgWorldMap.prototype.showDropItemModal = showDropItemModal;

mmrpgWorldMap.prototype.generateItemSelectButtonMarkup = generateItemSelectButtonMarkup;
mmrpgWorldMap.prototype.generateItemSelectPlaceholderMarkup = generateItemSelectPlaceholderMarkup;
