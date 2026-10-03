
// -- WORLD MECHANISMS METHODS -- //

// Define a quick function for parsing a mechanism event (button/switch) on the map and returning action dropdown options
function prepareMechanismEventOptions(firstEvent, options){
    //console.log('%c' + 'mmrpgWorldMap.prepareMechanismEventOptions()', 'color: magenta;');
    let _self = this;
    let _config = _self.config;

    options = options || {};

    let eventKind = firstEvent.kind;
    let $mechanismEvent = $(firstEvent.sprite);
    let dataLabel = $mechanismEvent.attr('data-label');
    let dataColour = $mechanismEvent.attr('data-colour');
    let dataState = $mechanismEvent.attr('data-state');

    let actionData = {
        showActionArea: false,
        actionAreaMarkup: '',
        sideButtonsMarkup: '',
        showActionAreaType: '',
        zoomTimeoutDuration: 0
    };

    if (eventKind === 'button'){
        let dataButton = $mechanismEvent.attr('data-button');
        if (dataButton && dataState === 'up'){
            actionData.showActionArea = true;
            if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
            actionData.sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="push-button" data-button="'+dataButton+'"><span><sup>Push The</sup> ' + (dataColour[0].toUpperCase() + dataColour.slice(1)) + ' Button</span></a>';
            actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            actionData.showActionAreaType = 'button';
            actionData.zoomTimeoutDuration = 500; // if we show a button dropdown, we want to zoom in quickly
            }
        }
    else if (eventKind === 'switch'){
        let dataSwitch = $mechanismEvent.attr('data-switch');
        if (dataSwitch){
            actionData.showActionArea = true;
            if (dataLabel){ actionData.actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
            actionData.sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="toggle-switch" data-switch="'+dataSwitch+'"><span><sup>Toggle The</sup> ' + dataColour.split('-').map(function(colour){ return colour[0].toUpperCase() + colour.slice(1); }).join(' & ') + ' Switch</span></a>';
            actionData.sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
            actionData.showActionAreaType = 'switch';
            actionData.zoomTimeoutDuration = 500; // if we show a switch dropdown, we want to zoom in quickly
            }
        }

    return actionData;
    }

// Define a quick function for handling mechanism interactions (buttons and switches) via the dropdown
function handleMechanismActionClick(action, $button, firstEvent){
    //console.log('%c' + 'mmrpgWorldMap.handleMechanismActionClick(action:' + action + ')', 'color: magenta;');
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _elements = _self.elements;
    let $canvasMap = _elements.map;

    _self.hideActionDropdown(false);

    let $eventSprite = $(firstEvent.sprite);
    let $innerSprite = $eventSprite ? $('> .sprite', $eventSprite) : false;

    if (action === 'push-button'){
        let buttonsIndex = _config.mapButtonsIndex;
        let buttonStates = _world.buttons;
        let buttonName = $button.attr('data-button') || false;
        let buttonInfo = buttonName && (buttonsIndex && buttonsIndex[buttonName]) ? buttonsIndex[buttonName] : false;

        if (!buttonName || !buttonInfo){ console.error('-> button name or info not found, cannot push button!'); return false; }
        if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot push button!'); return false; }
        if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot push button!'); return false; }

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
    else if (action === 'toggle-switch'){
        let switchesIndex = _config.mapSwitchesIndex;
        let switchStates = _world.switches;
        let switchName = $button.attr('data-switch') || false;
        let switchInfo = switchName && (switchesIndex && switchesIndex[switchName]) ? switchesIndex[switchName] : false;

        if (!switchName || !switchInfo){ console.error('-> switch name or info not found, cannot toggle switch!'); return false; }
        if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot toggle switch!'); return false; }
        if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot toggle switch!'); return false; }

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

    return true;
    }

// Assign the sub-functions to the main class's prototype
mmrpgWorldMap.prototype.prepareMechanismEventOptions = prepareMechanismEventOptions;
mmrpgWorldMap.prototype.handleMechanismActionClick = handleMechanismActionClick;
