
// -- WORLD PLAYER METHODS -- //

// Quick function for getting a given player's name span already styled
function getPlayerNameSpan(playerToken, customText){
    //console.log('%c' + 'mmrpgWorldMap.getPlayerNameSpan(playerToken:' + playerToken + ', customText:' + customText + ')', 'color: magenta;');
    let _self = this;
    let _indexes = _self.indexes;
    let _mmrpgPlayersIndex = _indexes.players;
    let playerInfo = _mmrpgPlayersIndex[playerToken] || false;
    let playerName = playerInfo ? playerInfo.name : 'Player';
    let playerType1 = playerInfo.type || '';
    let playerType2 = playerInfo.type2 || '';
    let spanTypes = playerType1 !== '' ? playerType1 : (playerType2 !== '' ? playerType2 : 'none');
    return '<span class="type ' + spanTypes + '">' + (customText || playerName) + '</span>';
    };

// Define a quick method for getting the current items (displayed) quantity in the player's inventory
function getPlayerItemQuantity(itemToken, includeEquipped){
    //console.log('%c' + 'mmrpgWorldMap.getPlayerItemQuantity(itemToken:' + itemToken + ', includeEquipped:' + includeEquipped + ')', 'color: magenta;');
    if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('getPlayerItemQuantity() missing required itemToken!'); return 0; }
    if (typeof includeEquipped !== 'boolean'){ includeEquipped = false; }
    if (itemToken.indexOf('__') !== -1){ itemToken = itemToken.split('__')[0]; }
    let _self = this;
    let parsedItemQuantities = _self.getPlayerItemQuantities(true, !includeEquipped);
    let thisItemQuantity = typeof parsedItemQuantities[itemToken] === 'number' ? parsedItemQuantities[itemToken] : 0;
    //console.log('-> itemToken =', itemToken);
    //console.log('-> parsedItemQuantities =', parsedItemQuantities);
    //console.log('-> thisItemQuantity =', thisItemQuantity);
    return thisItemQuantity;
    }
// Define a quick method for getting the array of player item quantities, optionally merging token groups into single values
function getPlayerItemQuantities(mergeGroups, excludeEquipped){
    //console.log('%c' + 'mmrpgWorldMap.getPlayerItemQuantities(mergeGroups:' + mergeGroups + ')', 'color: magenta;');
    if (typeof mergeGroups !== 'boolean'){ mergeGroups = true; }
    if (typeof excludeEquipped !== 'boolean'){ excludeEquipped = true; }
    let _self = this;
    let _world = _self.state;
    let _worldPlayer = _world.player;
    let _worldPlayerItems = _worldPlayer.items;
    let _worldPlayerItemsTokens = Object.keys(_worldPlayerItems || {});
    //console.log('-> _worldPlayerItems =', _worldPlayerItems);
    //console.log('-> _worldPlayerItemsTokens =', _worldPlayerItemsTokens);
    let itemQuantities = {};
    for (let i = 0; i < _worldPlayerItemsTokens.length; i++){
        let itemToken = _worldPlayerItemsTokens[i];
        let itemQuantity = typeof _worldPlayerItems[itemToken] === 'number' ? _worldPlayerItems[itemToken] : 0;
        if (itemToken.indexOf('__') !== -1){
            let tokenParts = itemToken.split('__');
            itemToken = tokenParts[0];
            if (tokenParts[1] === 'equipped'){
                if (excludeEquipped){ itemQuantity *= -1;  }
                else { continue; }
                } else {
                if (mergeGroups){ itemQuantity = 1; }
                else { continue; }
                }
            }
        if (typeof itemQuantities[itemToken] !== 'number'){ itemQuantities[itemToken] = 0; }
        itemQuantities[itemToken] += itemQuantity;
        }
    //console.log('-> itemQuantities =', itemQuantities);
    return itemQuantities;
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


// Define a quick function for grabbing the sprite markup of a player given the token and optional sprite arguments
function getPlayerSpriteMarkup(playerToken, spriteOptions){
    //console.log('%c' + 'mmrpgWorldMap.getPlayerSpriteMarkup(playerToken:' + playerToken + ', spriteOptions:', + JSON.stringify(spriteOptions) + ')', 'color: magenta;');
    if (!playerToken || typeof playerToken !== 'string' || !playerToken.length){ console.error('getPlayerSpriteMarkup() missing required playerToken!'); return ''; }
    let playerId = 0; if (playerToken.indexOf('_') !== -1){ playerId = playerToken.split('_')[0]; playerToken = playerToken.split('_')[1];  }
    //console.log('-> playerId =', playerId, '-> playerToken =', playerToken);
    spriteOptions = spriteOptions || {};
    let _self = this;
    let _config = _self.config;
    let _indexes = _self.indexes;
    let _world = _self.state;
    let playerInfo = _indexes.players.getByToken(playerToken);
    if (!playerInfo || typeof playerInfo !== 'object'){ console.error('getPlayerSpriteMarkup() could not find playerInfo for token ' + playerToken + '!'); return ''; }
    // Collect or define the sprite options with defaults if not provided
    spriteOptions.alt = typeof spriteOptions.alt === 'string' && spriteOptions.alt.length > 1 ? spriteOptions.alt : '';
    spriteOptions.size = typeof spriteOptions.size === 'number' && spriteOptions.size > 0 ? spriteOptions.size : (playerInfo.imageSize || 40);
    spriteOptions.dir = typeof spriteOptions.dir === 'string' && spriteOptions.dir.length ? spriteOptions.dir : 'right';
    spriteOptions.frame = typeof spriteOptions.frame === 'string' && spriteOptions.frame.length > 0 ? spriteOptions.frame : '00';
    spriteOptions.delay = typeof spriteOptions.delay === 'number' && spriteOptions.delay !== 0 ? spriteOptions.delay : (-1 * ( Math.floor(Math.random() * 10) / 100 ));
    spriteOptions.classes = typeof spriteOptions.classes === 'string' && spriteOptions.classes.length > 1 ? spriteOptions.classes : '';
    spriteOptions.styles = typeof spriteOptions.styles === 'string' && spriteOptions.styles.length > 1 ? spriteOptions.styles : '';
    spriteOptions.kind = typeof spriteOptions.kind === 'string' && spriteOptions.kind.length > 1 ? spriteOptions.kind : 'sprite';
    // Generate the player sprite attributes and inner markup given the options
    let playerSpriteAttrs = '';
    let playerSpriteClass = 'sprite player' + (spriteOptions.classes ? ' ' + spriteOptions.classes : '');
    let playerSpriteStyle = 'animation-delay: ' + spriteOptions.delay + 's;' + (spriteOptions.styles ? ' ' + spriteOptions.styles : '');
    playerSpriteAttrs += ' class="' + playerSpriteClass + '"';
    playerSpriteAttrs += ' data-sprite="player"';
    playerSpriteAttrs += ' data-kind="' + spriteOptions.kind + '"';
    playerSpriteAttrs += ' data-token="' + playerToken + '"';
    playerSpriteAttrs += ' data-alt="' + spriteOptions.alt + '"';
    playerSpriteAttrs += ' data-size="' + spriteOptions.size + '"';
    playerSpriteAttrs += ' data-dir="' + spriteOptions.dir + '"';
    playerSpriteAttrs += ' data-frame="' + spriteOptions.frame + '"';
    if (playerSpriteStyle.length){ playerSpriteAttrs += ' style="' + playerSpriteStyle + '"'; }
    let playerSpriteInner = '';
    playerSpriteInner += '<i class="sprite"></i>';
    // Put it all together to generate the final markup
    let playerSpriteMarkup = '';
    playerSpriteMarkup += '<span' + playerSpriteAttrs + '>';
        playerSpriteMarkup += '<span class="wrap">' + playerSpriteInner + '</span>';
    playerSpriteMarkup += '</span>';
    // Return the generated markup for the player sprite
    return playerSpriteMarkup;
    }

// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.getPlayerNameSpan = getPlayerNameSpan;

mmrpgWorldMap.prototype.getPlayerItemQuantity = getPlayerItemQuantity;
mmrpgWorldMap.prototype.getPlayerItemQuantities = getPlayerItemQuantities;

mmrpgWorldMap.prototype.getPlayerRobotsWithAbilityType = getPlayerRobotsWithAbilityType;

mmrpgWorldMap.prototype.getPlayerSpriteMarkup = getPlayerSpriteMarkup;