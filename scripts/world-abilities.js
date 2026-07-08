
// -- WORLD ABILITY METHODS -- //

// Define a quick function for triggering a live ability pickup on the field (and any effects that may have
function triggerAbilityPickup(abilityEvent, zoomDelay, playSound){
    //console.log('%c' + 'mmrpgWorldMap.triggerAbilityPickup()', 'color: magenta;');
    //console.log('--> abilityEvent =', abilityEvent);
    if (!abilityEvent || typeof abilityEvent !== 'object'){ console.error('triggerAbilityPickup() missing required abilityEvent!'); return false; }
    if (typeof abilityEvent.sprite === 'undefined'){ console.error('triggerAbilityPickup() missing required abilityEvent.sprite!'); return false; }
    if (abilityEvent.claimed === true){ console.warn('triggerAbilityPickup() called for ability that has already been claimed!'); return false; }
    playSound = typeof playSound === 'boolean' ? playSound : true; // default to true if not provided

    // Collect local references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerRobots = _worldPlayer.robots;
    let _worldAbilityStates = _world.abilities;
    let _mapAbilitiesIndex = _config.mapAbilitiesIndex;
    let $teamSprites = _elements.teamSprites;
    // Collect as much info about the ability as we can from the event data
    let $abilityEventSprite = $(abilityEvent.sprite);
    let $abilityEventLayer = $abilityEventSprite.closest('.layer');
    let abilityEventToken = abilityEvent.token;
    let abilityEventInfo = _mapAbilitiesIndex[abilityEventToken];
    let abilityToken = abilityEvent.kind2;
    //console.log('--> abilityEventToken =', abilityEventToken);
    //console.log('--> abilityEventInfo =', abilityEventInfo);
    //console.log('--> abilityToken =', abilityToken);
    // First zoom the ability sprite into the zoom layer so it's more visible to the player
    //console.log('-> zooming ability sprite make it more visible');
    zoomDelay = typeof zoomDelay === 'number' ? zoomDelay : 1200; // default to sync with standard use-case
    setTimeout(function(){
        $abilityEventSprite.addClass('zoom');
        $abilityEventLayer.addClass('has-zoom');
        }, Math.ceil(zoomDelay / 3));
    // Define a variable to hold the pickup action function
    let pickupFunction = function(onComplete, afterDelay){
        if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
        if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
        // Check to make sure the ability token was not empty
        if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){
            console.error('triggerAbilityPickup() called for ability with empty token!');
            return false;
            }
        // If a sound was requested, play it now
        if (playSound){ _self.playSoundEffect('pickup-sound'); }
        // Create an array to hold notification message markup for this event
        let messageMarkup = [];
        let abilityNameTextSpan = _self.getAbilityNameSpan(abilityToken);
        messageMarkup.push('Discovered the ' + abilityNameTextSpan + ' ability!');
        // We can add this ability directly to the player's collection
        if (_self.addAbilityToCollection(abilityToken)){
            // only remove if collection function returns true, though it should never technically be false
            abilityEvent.claimed = true;
            messageMarkup.push('Added ' + abilityNameTextSpan + ' to ability collection!');
            }
        // Show the world notification message for this pickup event now
        _self.showWorldMessage(messageMarkup);
        // Update the real copy with any changes to claimed flag
        if (abilityEvent.claimed){
            let claimTime = new Date().getTime();
            abilityEventInfo.claimed = true;
            _worldAbilityStates[abilityEventToken] = claimTime; // update world ability states w/ claim time
            }
        // If an onComplete function was provided, call it now (with delay if requested)
        if (onComplete){
            if (!afterDelay){ onComplete.call(_self); }
            else { setTimeout(function(){ onComplete.call(_self); }, afterDelay); }
            }
        };
    // Now we can remove the zoom and delete the ability sprite from the events layer
    // TODO: we need to actually save the ability to the player's inventory and save the event to permanently remove it
    //console.log('-> zooming and queueing pickup function for ability sprite on map');
    _self.incZoomLevel();
    if (playSound){ _self.playSoundEffect('pickup-sound'); }
    _world.isBusyWith.abilityPickup = true;
    setTimeout(function(){
        // call the pickup function to apply the ability effects
        pickupFunction(function(){
            //console.log('--> resetting zoom level and team sprite classes');
            //console.log('--> $teamSprites =', $teamSprites);
            _self.resetZoomLevel();
            $abilityEventLayer.removeClass('has-zoom');
            $teamSprites.removeClass('shake');
            $teamSprites.filter(':not(.disabled):not(.frame-lock)').attr('data-frame', '00');
            //console.log('--> removing ability sprite from the map');
            $abilityEventSprite.animate({opacity: 0, filter: 'brightness(2)'}, zoomDelay, function(){ $abilityEventSprite.remove(); });
            // Trigger a save of the world state to persist this change
            delete _world.isBusyWith.abilityPickup;
            _self.saveWorldState();
            });
        }, (zoomDelay * 2));
    // Return true on success
    return true;
    }
// Define a quick function for adding an ability to the player's collection if they don't already have it
function addAbilityToCollection(abilityToken, animatePickup, playSound){
    //console.log('%c' + 'mmrpgWorldMap.addAbilityToCollection(ability:' + abilityToken + ')', 'color: magenta;');
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('addAbilityToCollection() missing required abilityToken!'); return false; }
    if (typeof animatePickup !== 'boolean'){ animatePickup = true; } // default to true if not provided
    if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
    //console.log('--> abilityToken =', abilityToken);
    //console.log('--> animatePickup =', animatePickup);
    //console.log('--> playSound =', playSound);
    // Collect references to world objects
    let _self = this;
    let _world = _self.state;
    let _config = _self.config;
    let _elements = _self.elements;
    let _indexes = _self.indexes;
    let _mmrpgTypesIndex = _indexes.types;
    let _mmrpgTypesIndexKeys = _mmrpgTypesIndex.getTokens();
    let _mmrpgTypesIndexKeysRevised = ['copy', 'none'].concat(_mmrpgTypesIndexKeys).filter(function(value, index, self){ return self.indexOf(value) === index; });
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    let _mmrpgAbilitiesIndexKeys = Object.keys(_mmrpgAbilitiesIndex);
    let abilityIndexInfo = _mmrpgAbilitiesIndex[abilityToken];
    if (typeof abilityIndexInfo === 'undefined'){ console.error('addAbilityToCollection() could not find ability in index for token ' + abilityToken + '!'); return false; }
    //console.log('--> _mmrpgTypesIndexKeys =', _mmrpgTypesIndexKeys);
    //console.log('--> _mmrpgTypesIndexKeysRevised =', _mmrpgTypesIndexKeysRevised);
    //console.log('--> _mmrpgAbilitiesIndexKeys =', _mmrpgAbilitiesIndexKeys);
    //console.log('--> abilityIndexInfo =', abilityIndexInfo);
    let _worldPlayer = _world.player;
    let _worldPlayerAbilities = _worldPlayer.abilities;
    let $thisWorld = _elements.world;
    let $robotsOverview = _elements.robotsOverview;
    let $storageAbilitiesButton = $('.storage-button[data-view="abilities"]', $robotsOverview);
    let $storageAbilitiesOverview = $('.storage-box[data-storage="abilities"]', $robotsOverview);
    let $storageAbilitiesWrapper = $('> .wrapper', $storageAbilitiesOverview);
    let $storageAbilityInOverview = $('.team-ability[data-ability="' + abilityToken + '"]', $storageAbilitiesWrapper);
    // Check to see if there's room for the ability in the collection
    let abilityInventoryMax = _config.playerInventoryMax; // 99;
    let abilityAlreadyUnlocked = _worldPlayerAbilities.indexOf(abilityToken) !== -1 ? true : false;
    //console.log('--> abilityAlreadyUnlocked =', abilityAlreadyUnlocked);
    if (abilityAlreadyUnlocked){
        console.warn('addAbilityToCollection() called for ability that is already unlocked!');
        // we're gonna "collect" it anyway though
        //return false;
        }
    // If it's not already there, add the ability to the collection
    if (!abilityAlreadyUnlocked){ _worldPlayerAbilities.push(abilityToken); }
    //console.log('--> updated _worldPlayerAbilities['+abilityToken+'] => ', _worldPlayerAbilities[abilityToken]);
    // If an animation was requested, make sure we show it above the player's head
    if (animatePickup){
        // TODO: write this animation code later
        console.warn('addAbilityToCollection() would animate the ability pickup now...'); // TODO: read the message
        }
    // If a sound was requested, play it now
    if (playSound){ _self.playSoundEffect('get-ability'); }
    // If this ability exists in the robot overview, update the display there as well, else create it
    if ($storageAbilityInOverview.length){
        // nothing to update here really since abilities are unique
        } else {
        let abilityName = abilityIndexInfo.name;
        let abilityImageSize = abilityIndexInfo.imageSize;
        let abilityTypes = [];
        if (abilityIndexInfo.type){ abilityTypes.push(abilityIndexInfo.type); }
        if (abilityIndexInfo.type2){ abilityTypes.push(abilityIndexInfo.type2); }
        let abilityTypeClasses = 'type ' + (abilityTypes.length ? abilityTypes.join(' ') : 'none');
        let animationDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
        let abilitySpriteStyles = 'animation-delay: ' + animationDelay + 's;';
        let abilityNameMarkup = abilityName.replace(' ', '<br />');
        let newIndexKey = _mmrpgAbilitiesIndexKeys.indexOf(abilityToken) || 0;
        let newTypeKey = _mmrpgTypesIndexKeysRevised.indexOf(abilityIndexInfo.type) || 0;
        let newStorageKey = $('.team-ability[data-ability]', $storageAbilitiesWrapper).length || 0;
        if (abilityIndexInfo.type2){ newTypeKey += (_mmrpgTypesIndexKeysRevised.indexOf(abilityIndexInfo.type2) / 100); }
        let abilityButtonConfig = {new: true, slot: 0, indexKey: newIndexKey, storageKey: newStorageKey, typeKey: newTypeKey};
        let storageAbilityMarkup = _self.generateAbilitySelectButtonMarkup(abilityToken, null, abilityButtonConfig);
        $storageAbilitiesWrapper.append(storageAbilityMarkup);
        $storageAbilityInOverview = $('.team-ability[data-ability="' + abilityToken + '"]', $storageAbilitiesWrapper);
        $storageAbilitiesButton.addClass('new');
        }
    // Trigger a save of the world state to persist this change
    _self.saveWorldState();
    // Return true on success
    return true;
    }

// Define a quick function for getting the overview details for a given ability in the user's inventory
function getAbilityDetailsForOverview(abilityToken, targetSelected){
    //console.log('%c' + 'mmrpgWorldMap.getAbilityDetailsForOverview(ability:' + abilityToken + ', targetSelected:' + targetSelected + ')', 'color: magenta;');
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('getAbilityDetailsForOverview() missing required abilityToken!'); return ''; }
    if (typeof targetSelected !== 'boolean'){ targetSelected = false; }
    // Collect references to world objects
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerAbilities = _worldPlayer.abilities;
    let _indexes = _self.indexes;
    let _mmrpgTypesIndex = _indexes.types;
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    if (typeof _mmrpgAbilitiesIndex[abilityToken] === 'undefined'){ console.error('getAbilityDetailsForOverview() could not find ability in index for token ' + abilityToken + '!'); return false; }
    let abilityIndexInfo = _mmrpgAbilitiesIndex[abilityToken];
    //console.log('--> abilityIndexInfo =', abilityIndexInfo);
    let selectedPlayerRobot = targetSelected ? _self.getSelectedRobotInOverview(true) : false;
    //console.log('--> selectedPlayerRobot =', selectedPlayerRobot);
    let abilityIsUnlocked = _worldPlayerAbilities.indexOf(abilityToken) !== -1 ? true : false;
    //console.log('--> abilityIsUnlocked =', abilityIsUnlocked);
    let playerRobotAbilities = selectedPlayerRobot && selectedPlayerRobot.data.abilities ? selectedPlayerRobot.data.abilities : [];
    let abilitySlotsAvailable = selectedPlayerRobot && _config.maxAbilitiesPerRobot ? (_config.maxAbilitiesPerRobot - playerRobotAbilities.length) : 0;
    let abilityIsEquipped = selectedPlayerRobot && playerRobotAbilities.includes(abilityIndexInfo.id) ? true : false;
    //console.log('--> abilityIsEquipped =', abilityIsEquipped);

    // Generate the markup, classes, styles, etc. that will make up the ability details
    let abilityTitle = 'Ability Details';
    let abilityName = abilityIndexInfo.name;
    let abilityDescription = abilityIndexInfo.description;
    let abilityType1 = abilityIndexInfo.type || 'none';
    let abilityType2 = abilityIndexInfo.type2 || false;
    let abilityCost = abilityIndexInfo.energy || 0;
    let abilityImageSize = abilityIndexInfo.imageSize;
    let abilityKind = abilityIndexInfo.subclass;
    let abilityKindName = abilityKind.charAt(0).toUpperCase() + abilityKind.slice(1);
    let abilityTarget = abilityIndexInfo.target || 'auto';
    let abilityTargetText;
    if (abilityTarget === 'select_target'){ abilityTargetText = 'Select'; }
    else if (abilityTarget === 'select_this_ally'){ abilityTargetText = 'Ally'; }
    else if (abilityTarget === 'select_this'){ abilityTargetText = 'Self or Ally'; }
    else if (abilityTarget === 'select_disabled'){ abilityTargetText = 'Disabled'; }
    else { abilityTargetText = abilityTarget.charAt(0).toUpperCase() + abilityTarget.slice(1); }
    //console.log('--> abilityName =', abilityName);
    //console.log('--> abilityDescription =', abilityDescription);
    //console.log('--> abilityType1 =', abilityType1);
    //console.log('--> abilityType2 =', abilityType2);
    //console.log('--> abilityCost =', abilityCost);
    //console.log('--> abilityImageSize =', abilityImageSize);
    //console.log('--> abilityKind =', abilityKind);
    //console.log('--> abilityKindName =', abilityKindName);
    //console.log('--> abilityTarget =', abilityTarget);
    //console.log('--> abilityTargetText =', abilityTargetText);
    //console.log('--> statTokens =', statTokens);

    // Generate type-related markup and spans for use later
    let statTokens = ['attack', 'defense', 'speed'];
    let abilityTypeClasses = (abilityType2 && abilityType1 === 'none' ? abilityType2 : (abilityType1 + (abilityType2 ? '_' + abilityType2 : '')));
    let isStatAbility = (statTokens.indexOf(abilityType1) !== -1 || statTokens.indexOf(abilityType2) !== -1 || statTokens.indexOf(abilityToken.split('-')[0]) !== -1 ? true : false);
    //console.log('--> abilityTypeClasses =', abilityTypeClasses);

    // Determine the icon to use based on the ability kind and format the text for display
    let abilityKindIcon = 'dot-circle';
    if (abilityKind === 'consumable'){ abilityKindIcon = 'apple-alt'; }
    else if (abilityKind === 'holdable'){ abilityKindIcon = 'briefcase'; }
    else if (abilityKind === 'collectable'){ abilityKindIcon = 'cubes'; }
    else if (abilityKind === 'event'){ abilityKindIcon = 'bookmark'; }
    else if (abilityKind === 'treasure'){ abilityKindIcon = 'gem'; }
    //console.log('--> abilityKindIcon =', abilityKindIcon);

    // Generate the ability sprite that will be used in the details
    let randDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
    let abilitySpriteClasses = 'sprite ability icon';
    let abilitySpriteStyles = 'animation-delay: ' + randDelay + 's; ';
    let abilitySpriteAttrs = 'data-sprite="ability" data-token="' + abilityToken + '" data-size="' + abilityImageSize + '" data-dir="right" data-frame="00"';
    let abilitySprite = '<div class="' + abilitySpriteClasses + '" style="' + abilitySpriteStyles + '" ' + abilitySpriteAttrs + '><span class="wrap"><i class="sprite"></i></span></div>';
    //console.log('--> abilitySpriteClasses =', abilitySpriteClasses);
    //console.log('--> abilitySpriteStyles =', abilitySpriteStyles);
    //console.log('--> abilitySpriteAttrs =', abilitySpriteAttrs);
    //console.log('--> abilitySprite =', abilitySprite);

    // Collect details about the power level of this ability (if relevant)
    let abilityDamage = abilityIndexInfo.damage || 0;
    let abilityDamagePercent = abilityIndexInfo.damagePercent || 0;
    let abilityRecovery = abilityIndexInfo.recovery || 0;
    let abilityRecoveryPercent = abilityIndexInfo.recoveryPercent || 0;
    let abilityDamage2 = abilityIndexInfo.damage2 || 0;
    let abilityDamage2Percent = abilityIndexInfo.damage2Percent || 0;
    let abilityRecovery2 = abilityIndexInfo.recovery2 || 0;
    let abilityRecovery2Percent = abilityIndexInfo.recovery2Percent || 0;
    //console.log('--> abilityDamage =', abilityDamage);
    //console.log('--> abilityDamagePercent =', abilityDamagePercent);
    //console.log('--> abilityRecovery =', abilityRecovery);
    //console.log('--> abilityRecoveryPercent =', abilityRecoveryPercent);
    //console.log('--> abilityDamage2 =', abilityDamage2);
    //console.log('--> abilityDamage2Percent =', abilityDamage2Percent);
    //console.log('--> abilityRecovery2 =', abilityRecovery2);
    //console.log('--> abilityRecovery2Percent =', abilityRecovery2Percent);

    // Parse any value tags in the description before display
    abilityDescription = abilityDescription.replace('{DAMAGE}', (abilityDamage ? abilityDamage : 0));
    abilityDescription = abilityDescription.replace('{RECOVERY}', (abilityRecovery ? abilityRecovery : 0));
    abilityDescription = abilityDescription.replace('{DAMAGE2}', (abilityDamage2 ? abilityDamage2 : 0));
    abilityDescription = abilityDescription.replace('{RECOVERY2}', (abilityRecovery2 ? abilityRecovery2 : 0));

    // Start generating the ability details object for the overview
    let abilityDetailsObject = {};
    abilityDetailsObject.title = abilityTitle;
    abilityDetailsObject.image = abilitySprite;
    abilityDetailsObject.name = abilityName;
    abilityDetailsObject.cost = abilityCost;
    abilityDetailsObject.typeClasses = abilityTypeClasses;
    abilityDetailsObject.infoLines = [];
    if (abilityType1 || abilityType2){
        let typeLine = { classes: 'types', label: 'Type:', values: [] };
        if (abilityType1){
            let typeValue1 = {};
            typeValue1.value = abilityType1 === 'none' ? 'Neutral' : _mmrpgTypesIndex[abilityType1].name;
            typeValue1.valueClasses = 'type ' + abilityType1;
            typeLine.values.push(typeValue1);
            }
        if (abilityType2){
            let typeValue2 = {};
            typeValue2.value = abilityType2 === 'none' ? 'Neutral' : _mmrpgTypesIndex[abilityType2].name;
            typeValue2.valueClasses = 'type ' + abilityType2;
            typeLine.values.push(typeValue2);
            }
        abilityDetailsObject.infoLines.push(typeLine);
        }
    if (abilityDamage || (isStatAbility && abilityDamage2)
        || abilityRecovery || (isStatAbility && abilityRecovery2)){
        let powerLine = { classes: 'power', label: 'Power:', values: [] };
        if (abilityDamage || (isStatAbility && abilityDamage2)){
            let damageValue = {};
            if (abilityDamage){ damageValue.value = (isStatAbility ? '-' : '') + abilityDamage + (abilityDamagePercent ? '%' : '') + ' ' +  (isStatAbility ? 'Break' : 'Damage'); }
            else if (isStatAbility && abilityDamage2){ damageValue.value = (isStatAbility ? '-' : '') + abilityDamage2 + (abilityDamage2Percent ? '%' : '') + ' ' +  (isStatAbility ? 'Break' : 'Damage'); }
            damageValue.valueClasses = 'damage';
            if (abilityType1 === 'weapons'){ damageValue.icon = 'battery-half'; }
            else if (isStatAbility){ damageValue.icon = 'caret-square-down'; }
            else { damageValue.icon = 'fist-raised'; }
            powerLine.values.push(damageValue);
            }
        if (abilityRecovery || (isStatAbility && abilityRecovery2)){
            let recoveryValue = {};
            if (abilityRecovery){ recoveryValue.value = (isStatAbility ? '+' : '') + abilityRecovery + (abilityRecoveryPercent ? '%' : '') + ' ' + (isStatAbility ? 'Boost' : 'Recovery'); }
            else if (isStatAbility && abilityRecovery2){ recoveryValue.value = (isStatAbility ? '+' : '') + abilityRecovery2 + (abilityRecovery2Percent ? '%' : '') + ' ' + (isStatAbility ? 'Boost' : 'Recovery'); }
            recoveryValue.valueClasses = 'recovery';
            if (abilityType1 === 'weapons'){ recoveryValue.icon = 'battery-full'; }
            else if (isStatAbility){ recoveryValue.icon = 'caret-square-up'; }
            else { recoveryValue.icon = 'heart'; }
            powerLine.values.push(recoveryValue);
            }
        abilityDetailsObject.infoLines.push(powerLine);
        }
    if (abilityTarget){
        abilityDetailsObject.infoLines.push({
            classes: 'target',
            label: 'Target:',
            value: abilityTargetText,
            valueClasses: abilityTarget === 'auto' ? 'auto' : 'select'
            });
        }
    let equipAbilityText = 'Equip';
    let removeAbilityText = 'Remove';
    if (selectedPlayerRobot && abilityIsEquipped){ equipAbilityText = 'Equipped'; }
    if (selectedPlayerRobot && !abilityIsEquipped && !abilitySlotsAvailable){ equipAbilityText = 'Replace'; }
    abilityDetailsObject.description = abilityDescription;

    // ACTION BUTTONS
    let _inputs = _self.inputs;
    let _userInputs = _inputs.getUserInputs();
    let aButtonIcon = _userInputs.A.icon, bButtonIcon = _userInputs.B.icon;
    let xButtonIcon = _userInputs.X.icon, yButtonIcon = _userInputs.Y.icon;
    abilityDetailsObject.actions = [];
    abilityDetailsObject.actions.push({ action: 'equip-ability', text: yButtonIcon + ' ' + equipAbilityText, button: 'Y', ability: abilityToken, disabled: !targetSelected });
    abilityDetailsObject.actions.push({ action: 'remove-ability', text: xButtonIcon + ' ' + removeAbilityText, button: 'X', ability: abilityToken, disabled: !abilityIsEquipped });

    // Pre-compile some of the HTML to make it easier for the other functions
    abilityDetailsObject.infolinesHTML = '';
    for (let i = 0; i < abilityDetailsObject.infoLines.length; i++){
        let infoLine = abilityDetailsObject.infoLines[i];
        abilityDetailsObject.infolinesHTML += '<div class="infoline ' + infoLine.classes + '">';
        abilityDetailsObject.infolinesHTML += '<strong class="label">' + infoLine.label + '</strong>';
        if (infoLine.values){
            for (let j = 0; j < infoLine.values.length; j++){
                let valueInfo = infoLine.values[j];
                let valueClasses = ('value' + (valueInfo.valueClasses ? (' ' + valueInfo.valueClasses) : '')).trim();
                let valueStyles = ('' + (valueInfo.valueStyles ? (' ' + valueInfo.valueStyles) : '')).trim();
                abilityDetailsObject.infolinesHTML += '<span' + (valueClasses ? ' class="' + valueClasses + '"' : '') + (valueStyles ? ' style="' + valueStyles + '"' : '') + '>' + valueInfo.value + '</span>';
                if (valueInfo.icon){ abilityDetailsObject.infolinesHTML += '<i class="fa fas fa-' + valueInfo.icon + '"></i>'; }
                }
            } else {
            abilityDetailsObject.infolinesHTML += '<span class="value ' + (infoLine.valueClasses ? infoLine.valueClasses : '') + '">' + infoLine.value + '</span>';
            if (infoLine.icon){ abilityDetailsObject.infolinesHTML += '<i class="fa fas fa-' + infoLine.icon + '"></i>'; }
            }
        abilityDetailsObject.infolinesHTML += '</div>';
        }
    abilityDetailsObject.actionsHTML = '';
    for (let i = 0; i < abilityDetailsObject.actions.length; i++){
        let actionInfo = abilityDetailsObject.actions[i];
        let actionButton = typeof actionInfo.button !== 'undefined' ? actionInfo.button : false;
        let actionDisabled = typeof actionInfo.disabled !== 'undefined' && actionInfo.disabled === true ? true : false;
        let actionHidden = typeof actionInfo.hidden !== 'undefined' && actionInfo.hidden === true ? true : false;
        abilityDetailsObject.actionsHTML += '<button type="button" '
            + 'class="button ' + actionInfo.action + (actionDisabled ? ' disabled' : '') + (actionHidden ? ' hidden' : '') + '" '
            + 'data-action="' + actionInfo.action + '"'
            + (actionButton ? ' data-button="' + actionButton + '"' : '')
            + (actionDisabled ? ' disabled="disabled"' : '')
            + '>' + actionInfo.text + '</button>';
        }

    // Return the generated ability details object
    //console.log('--> abilityDetailsObject =', abilityDetailsObject);
    return abilityDetailsObject;
    }
// Define a quick function for getting the details markup for a given ability in the user's arsenal
function getAbilityDetailsMarkupForOverview(abilityToken, targetSelected){
    //console.log('%c' + 'mmrpgWorldMap.getAbilityDetailsMarkupForOverview(ability:' + abilityToken + ', targetSelected:' + targetSelected + ')', 'color: magenta;');
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('getAbilityDetailsMarkupForOverview() missing required abilityToken!'); return ''; }
    if (typeof targetSelected !== 'boolean'){ targetSelected = false; }
    let _self = this;
    let abilityDetailsObject = _self.getAbilityDetailsForOverview(abilityToken, targetSelected) || false;
    if (!abilityDetailsObject || typeof abilityDetailsObject !== 'object'){ console.error('getAbilityDetailsMarkupForOverview() could not generate details object for token ' + abilityToken + '!'); return ''; }
    let abilityDetailsMarkup = '';
    abilityDetailsMarkup += '<div class="storage-details" data-ability="' + abilityToken + '">';
        abilityDetailsMarkup += '<div class="title">' + abilityDetailsObject.title + '</div>';
        abilityDetailsMarkup += '<div class="image type ' + abilityDetailsObject.typeClasses + '">' + abilityDetailsObject.image + '</div>';
        abilityDetailsMarkup += '<div class="subtitle">';
            abilityDetailsMarkup += '<strong class="name">' + abilityDetailsObject.name + '</strong>';
            abilityDetailsMarkup += '<strong class="cost">' + abilityDetailsObject.cost + ' <i>WE</i></strong>';
            abilityDetailsMarkup += '<hr class="type ' + abilityDetailsObject.typeClasses + '">';
        abilityDetailsMarkup += '</div>';
        abilityDetailsMarkup += '<div class="infolines">' + abilityDetailsObject.infolinesHTML + '</div>';
        abilityDetailsMarkup += '<div class="description"><p>' + abilityDetailsObject.description + '</p></div>';
        abilityDetailsMarkup += '<div class="actions">' + abilityDetailsObject.actionsHTML + '</div>';
    abilityDetailsMarkup += '</div>';
    return abilityDetailsMarkup;
    }
// Define a quick function for replacing the ability details in an existing details div with new ones
function replaceAbilityDetailsInOverview($detailsDiv, abilityToken, abilityDetails){
    //console.log('%c' + 'mmrpgWorldMap.replaceAbilityDetailsInOverview($detailsDiv, abilityToken:' + abilityToken + ', abilityDetails)', 'color: magenta;');
    if (!$detailsDiv || !$detailsDiv.length){ console.error('replaceAbilityDetailsInOverview() missing required $detailsDiv!'); return false; }
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('replaceAbilityDetailsInOverview() missing required abilityToken!'); return false; }
    if (!abilityDetails || typeof abilityDetails !== 'object'){ console.error('replaceAbilityDetailsInOverview() missing required abilityDetails!'); return false; }
    //console.log('-> abilityDetails =', abilityDetails);
    let $title = $detailsDiv.find('> .title'),
        $image = $detailsDiv.find('> .image'),
        $subtitle = $detailsDiv.find('> .subtitle'),
        $subtitleName = $subtitle.find('> .name'),
        $subtitleCost = $subtitle.find('> .cost'),
        $subtitleSubline = $subtitle.find('> hr'),
        $infolines = $detailsDiv.find('> .infolines'),
        $description = $detailsDiv.find('> .description'),
        $actions = $detailsDiv.find('> .actions')
        ;
    $detailsDiv.attr('data-ability', abilityToken);
    $title.html(abilityDetails.title);
    $image.html(abilityDetails.image);
    $image.removeClass().addClass('image type ' + abilityDetails.typeClasses);
    $subtitleName.html(abilityDetails.name);
    $subtitleCost.html(abilityDetails.cost + ' WE');
    $subtitleSubline.removeClass().addClass('type ' + abilityDetails.typeClasses);
    $infolines.html(abilityDetails.infolinesHTML);
    $description.html(abilityDetails.description);
    //$actions.html(abilityDetails.actionsHTML);
    // manually update buttons so css transitions can occur properly
    for (let i = 0; i < abilityDetails.actions.length; i++){
        let actionInfo = abilityDetails.actions[i];
        let actionButton = typeof actionInfo.button !== 'undefined' ? actionInfo.button : false;
        let actionDisabled = typeof actionInfo.disabled !== 'undefined' && actionInfo.disabled === true ? true : false;
        let $actionButton = $actions.find('.button.' + actionInfo.action);
        if ($actionButton && $actionButton.length){
            if (actionDisabled){ $actionButton.addClass('disabled'); $actionButton.attr('disabled', 'disabled'); }
            else { $actionButton.removeClass('disabled'); $actionButton.removeAttr('disabled'); }
            $actionButton.html(actionInfo.text);
            } else {
            let actionButtonMarkup = '<button type="button" '
                + 'class="button ' + actionInfo.action + (actionDisabled ? ' disabled' : '') + '" '
                + 'data-ability="' + actionInfo.ability + '"'
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

// Quick function for getting a given ability's name span already styled
function getAbilityNameSpan(abilityToken, customText){
    //console.log('%c' + 'mmrpgWorldMap.getAbilityNameSpan(abilityToken:' + abilityToken + ', customText:' + customText + ')', 'color: magenta;');
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgAbilitiesIndex = _indexes.abilities;
    let abilityInfo = _mmrpgAbilitiesIndex[abilityToken] || false;
    let abilityName = abilityInfo ? abilityInfo.name : 'Ability';
    let abilityType1 = abilityInfo.type || '';
    let abilityType2 = abilityInfo.type2 || '';
    let spanTypes = abilityType1 !== '' ? abilityType1 : (abilityType2 !== '' ? abilityType2 : 'none');
    return '<span class="type ' + spanTypes + '">' + (customText || abilityName) + '</span>';
    }
// Define a quick function for grabbing the sprite markup of an ability given the token and optional sprite arguments
function getAbilitySpriteMarkup(abilityToken, spriteOptions){
    //console.log('%c' + 'mmrpgWorldMap.getAbilitySpriteMarkup(abilityToken:' + abilityToken + ', spriteOptions:', + JSON.stringify(spriteOptions) + ')', 'color: magenta;');
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('getAbilitySpriteMarkup() missing required abilityToken!'); return ''; }
    spriteOptions = spriteOptions || {};
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let abilityInfo = _indexes.abilities.getByToken(abilityToken);
    if (!abilityInfo || typeof abilityInfo !== 'object'){ console.error('getAbilitySpriteMarkup() could not find abilityInfo for token ' + abilityToken + '!'); return ''; }
    // Collect or define the sprite options with defaults if not provided
    spriteOptions.sheet = typeof spriteOptions.sheet === 'number' && spriteOptions.sheet > 1 ? spriteOptions.sheet : 1;
    spriteOptions.size = typeof spriteOptions.size === 'number' && spriteOptions.size > 0 ? spriteOptions.size : (abilityInfo.imageSize || 40);
    spriteOptions.dir = typeof spriteOptions.dir === 'string' && spriteOptions.dir.length ? spriteOptions.dir : 'right';
    spriteOptions.frame = typeof spriteOptions.frame === 'string' && spriteOptions.frame.length > 0 ? spriteOptions.frame : '00';
    spriteOptions.delay = typeof spriteOptions.delay === 'number' && spriteOptions.delay !== 0 ? spriteOptions.delay : (-1 * ( Math.floor(Math.random() * 10) / 100 ));
    spriteOptions.showBack = typeof spriteOptions.showBack === 'boolean' ? spriteOptions.showBack : false;
    spriteOptions.classes = typeof spriteOptions.classes === 'string' && spriteOptions.classes.length > 1 ? spriteOptions.classes : '';
    spriteOptions.styles = typeof spriteOptions.styles === 'string' && spriteOptions.styles.length > 1 ? spriteOptions.styles : '';
    // Generate the ability sprite attributes and inner markup given the options
    let abilitySpriteAttrs = '';
    let abilitySpriteClass = 'sprite ability' + (spriteOptions.classes ? ' ' + spriteOptions.classes : '');
    let abilitySpriteStyle = 'animation-delay: ' + spriteOptions.delay + 's;' + (spriteOptions.styles ? ' ' + spriteOptions.styles : '');
    let abilityTypeClasses = abilityInfo.type === '' ? 'none' : (abilityInfo.type + (abilityInfo.type2 !== '' ? '_' + abilityInfo.type2 : ''));
    abilitySpriteAttrs += ' class="' + abilitySpriteClass + '"';
    abilitySpriteAttrs += ' data-sprite="ability"';
    abilitySpriteAttrs += ' data-token="' + abilityToken + '"';
    abilitySpriteAttrs += ' data-sheet="' + spriteOptions.sheet + '"';
    abilitySpriteAttrs += ' data-size="' + spriteOptions.size + '"';
    abilitySpriteAttrs += ' data-dir="' + spriteOptions.dir + '"';
    abilitySpriteAttrs += ' data-frame="' + spriteOptions.frame + '"';
    if (abilitySpriteStyle.length){ abilitySpriteAttrs += ' style="' + abilitySpriteStyle + '"'; }
    let abilitySpriteInner = '<i class="sprite"></i>';
    if (spriteOptions.showBack){ abilitySpriteInner += '<i class="back type ' + abilityTypeClasses + '"></i>'; }
    // Put it all together to generate the final markup
    let abilitySpriteMarkup = '';
    abilitySpriteMarkup += '<span' + abilitySpriteAttrs + '>';
        abilitySpriteMarkup += '<span class="wrap">' + abilitySpriteInner + '</span>';
    abilitySpriteMarkup += '</span>';
    // Return the generated markup for the ability sprite
    return abilitySpriteMarkup;
    }

// Define a quick function for showing an ability modal for some kind of ability-related action
function showAbilityModal(actionToken, abilityToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showAbilityModal(actionToken:' + actionToken + ', abilityToken:' + abilityToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showAbilityModal() missing required actionToken!'); return; }
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('showAbilityModal() missing required abilityToken!'); return; }
    targetRobotToken = targetRobotToken && typeof targetRobotToken === 'string' && targetRobotToken.length ? targetRobotToken : null;
    let _self = this;
    return _self.showActionModal('ability', actionToken, abilityToken, targetRobotToken, configCustom);
    }
// Define a quick function for showing the equip ability modal
function showEquipAbilityModal(abilityToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showEquipAbilityModal(abilityToken:' + abilityToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('showEquipAbilityModal() missing required abilityToken!'); return; }
    if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showEquipAbilityModal() missing required targetRobotToken!'); return; }
    let _self = this;
    return _self.showAbilityModal('equip-ability', abilityToken, targetRobotToken, configCustom);
    }
// Define a quick function for showing the remove ability modal
function showRemoveAbilityModal(abilityToken, targetRobotToken, configCustom){
    //console.log('%c' + 'mmrpgWorldMap.showRemoveAbilityModal(abilityToken:' + abilityToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('showRemoveAbilityModal() missing required abilityToken!'); return; }
    if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showRemoveAbilityModal() missing required targetRobotToken!'); return; }
    let _self = this;
    return _self.showAbilityModal('remove-ability', abilityToken, targetRobotToken, configCustom);
    }

// Define a quick function for generating the markup for an ability select button given an ability token, robot info, and/or optional settings
function generateAbilitySelectButtonMarkup(abilityToken, playerRobotInfo, buttonOptions){
    //console.log('%c' + 'mmrpgWorldMap.generateAbilitySelectButtonMarkup(abilityToken:' + abilityToken + ', playerRobotInfo, buttonOptions)', 'color: magenta;');
    //console.log('-> w/ playerRobotInfo =', playerRobotInfo);
    //console.log('-> w/ buttonOptions =', buttonOptions);
    if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('generateAbilitySelectButtonMarkup() missing required abilityToken!'); return ''; }
    if (!playerRobotInfo || typeof playerRobotInfo !== 'object'){ playerRobotInfo = null; }
    if (!buttonOptions || typeof buttonOptions !== 'object'){ buttonOptions = {}; }
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let abilityInfo = _indexes.abilities.getByToken(abilityToken);
    if (!abilityInfo || typeof abilityInfo !== 'object'){ console.error('generateAbilitySelectButtonMarkup() could not find abilityInfo for token ' + abilityToken + '!'); return ''; }

    buttonOptions.indexKey = typeof buttonOptions.indexKey === 'number' ? buttonOptions.indexKey : false;
    buttonOptions.storageKey = typeof buttonOptions.storageKey === 'number' ? buttonOptions.storageKey : false;
    buttonOptions.typeKey = typeof buttonOptions.typeKey === 'number' ? buttonOptions.typeKey : false;
    buttonOptions.new = typeof buttonOptions.new !== 'undefined' ? buttonOptions.new : false;
    buttonOptions.selected = typeof buttonOptions.selected !== 'undefined' ? buttonOptions.selected : false;
    buttonOptions.disabled = typeof buttonOptions.disabled !== 'undefined' ? buttonOptions.disabled : false;
    buttonOptions.slot = typeof buttonOptions.slot === 'number' ? buttonOptions.slot : false;

    let abilityAnimationDelay = -1 * ( Math.floor(Math.random() * 10) / 100 );
    let abilityTypeClasses = abilityInfo.type === '' ? 'none' : (abilityInfo.type + (abilityInfo.type2 !== '' ? '_' + abilityInfo.type2 : ''));
    let abilityEnergyCost = abilityInfo.energy || 0;

    let abilityNameFormatted = abilityInfo.name.split(' ').join('<br />');
    let abilityCostFormatted = '<sup>' + abilityEnergyCost + '</sup><sub>WE</sub>';

    let buttonAttrs = '';
    let buttonClass = 'team-ability' + (buttonOptions.new ? ' new' : '') + (buttonOptions.selected ? ' selected' : '') + (buttonOptions.disabled ? ' disabled' : '');
    let buttonStyle = '';
    buttonAttrs += ' class="' + buttonClass + '"';
    buttonAttrs += ' data-ability="' + abilityToken + '"';
    buttonAttrs += ' data-ability-id="' + abilityInfo.id + '"';
    buttonAttrs += ' data-energy-cost="' + (abilityInfo.energy || 0) + '"';
    if (buttonOptions.indexKey !== false){ buttonAttrs += ' data-index-key="' + buttonOptions.indexKey + '"'; }
    if (buttonOptions.storageKey !== false){ buttonAttrs += ' data-storage-key="' + buttonOptions.storageKey + '"'; }
    if (buttonOptions.typeKey !== false){ buttonAttrs += ' data-type-key="' + buttonOptions.typeKey + '"'; }
    if (buttonOptions.slot !== false){ buttonAttrs += ' data-slot="' + buttonOptions.slot + '"'; }
    if (buttonStyle.length){ buttonAttrs += ' style="' + buttonStyle + '"'; }

    let buttonSpriteAttrs = '';
    let buttonSpriteClass = 'sprite ability icon';
    let buttonSpriteStyle = 'animation-delay: ' + abilityAnimationDelay + 's;';
    buttonSpriteAttrs += ' class="' + buttonSpriteClass + '"';
    buttonSpriteAttrs += ' data-sprite="ability"';
    buttonSpriteAttrs += ' data-token="' + abilityToken + '"';
    buttonSpriteAttrs += ' data-alt=""';
    buttonSpriteAttrs += ' data-size="40"';
    buttonSpriteAttrs += ' data-dir="right"';
    buttonSpriteAttrs += ' data-frame="00"';
    if (buttonSpriteStyle.length){ buttonSpriteAttrs += ' style="' + buttonSpriteStyle + '"'; }
    let buttonSpriteInner = '<span class="wrap"><i class="back type ' + abilityTypeClasses + '"></i><i class="sprite"></i></span>';

    let buttonMarkup = '';
    buttonMarkup += '<a' + buttonAttrs + '>';
        buttonMarkup += '<div class="image"><span' + buttonSpriteAttrs + '>' + buttonSpriteInner + '</span></div>';
        buttonMarkup += '<span class="tint type ' + abilityTypeClasses + '"></span>';
        buttonMarkup += '<strong class="name">' + abilityNameFormatted + '</strong>';
        buttonMarkup += '<span class="cost">' + abilityCostFormatted + '</span>';
    buttonMarkup += '</a>';

    return buttonMarkup;
    }
// Define a quick function for generating the markup for an ability select placeholder given robot info, and/or optional settings
function generateAbilitySelectPlaceholderMarkup(playerRobotInfo, buttonOptions){
    //console.log('%c' + 'mmrpgWorldMap.generateAbilitySelectPlaceholderMarkup(playerRobotInfo, buttonOptions)', 'color: magenta;');
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
    buttonAttrs += ' data-ability="ability"';
    let buttonClass = 'team-ability placeholder' + (buttonOptions.selected ? ' selected' : '') + (buttonOptions.disabled ? ' disabled' : '');
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

mmrpgWorldMap.prototype.triggerAbilityPickup = triggerAbilityPickup;
mmrpgWorldMap.prototype.addAbilityToCollection = addAbilityToCollection;

mmrpgWorldMap.prototype.getAbilityDetailsForOverview = getAbilityDetailsForOverview;
mmrpgWorldMap.prototype.getAbilityDetailsMarkupForOverview = getAbilityDetailsMarkupForOverview;
mmrpgWorldMap.prototype.replaceAbilityDetailsInOverview = replaceAbilityDetailsInOverview;

mmrpgWorldMap.prototype.getAbilityNameSpan = getAbilityNameSpan;
mmrpgWorldMap.prototype.getAbilitySpriteMarkup = getAbilitySpriteMarkup;

mmrpgWorldMap.prototype.showAbilityModal = showAbilityModal;
mmrpgWorldMap.prototype.showEquipAbilityModal = showEquipAbilityModal;
mmrpgWorldMap.prototype.showRemoveAbilityModal = showRemoveAbilityModal;

mmrpgWorldMap.prototype.generateAbilitySelectButtonMarkup = generateAbilitySelectButtonMarkup;
mmrpgWorldMap.prototype.generateAbilitySelectPlaceholderMarkup = generateAbilitySelectPlaceholderMarkup;
