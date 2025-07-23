
// Define global variables
let $thisPrototype = false;
let $thisWorld = false;
let $thisCanvas = false;

// Expand the game settings object with a variable world specific data
gameSettings.worldConfig = {
    userId: 0,
    playerId: 0,
    playerToken: 'player',
    playerRobots: ['robot'],
    mapToken: 'undefined',
    mapImage: 'undefined.png',
    mapSize: [10, 10],
    mapTileSize: [40, 40],
    mapTileSizeOffset: [0, 0],
    mapCols: 10, // default only
    mapRows: 10, // default only
    mapWidth: 400, // 10 tiles * 40px per tile, will be dynamic later
    mapHeight: 400, // 10 tiles * 40px per tile, will be dynamic later
    mapEffects: {
        focusTimeout: 600, // milliseconds
        hoverTimeout: 600, // milliseconds
        moveTimeout: 300, // milliseconds
        moveTravel: 100, // milliseconds
        },
    mapTilesIndex: {},
    mapSpritesIndex: {},
    mapPortalsIndex: {},
    };
gameSettings.worldElements = {
    mmrpg: null,
    world: null,
    canvas: null,
    map: null,
    layers: null,
    };
gameSettings.worldState = {
    cursor: {
        direction: '',
        moving: false,
        position: '0-0',
        col: 0,
        row: 0,
        },
    layersIndex: {},
    layerTilesIndex: {},
    };
gameSettings.worldHasLoaded = false;

// Create the document ready events
$(document).ready(function(){
    //console.log('%c' + 'World map canvas ready!', 'color: green;');
    let _config = gameSettings.worldConfig;
    let _elements = gameSettings.worldElements;
    let _world = gameSettings.worldState;
    let _worldCursor = _world.cursor;
    $thisPrototype = $('#mmrpg');
    $thisWorld = $('#world', $thisPrototype);
    $thisCanvas = $('#canvas', $thisWorld);
    _elements.mmrpg = $thisPrototype;
    _elements.world = $thisWorld;
    _elements.canvas = $thisCanvas;

    // -- ??????? -- //

    let $canvasMap = $('#map', $thisCanvas);
    let $mapLayers = $('.layer', $canvasMap);
    _elements.map = $canvasMap;
    _elements.layers = $mapLayers;
    if ($canvasMap.length && $mapLayers.length){

        //console.log('%c' + 'World map canvas found with ' + $mapLayers.length + ' layers...', 'color: orange;');
        initWorldMap($canvasMap, $mapLayers, function(){
            //console.log('%c' + 'initWorldMap() complete!', 'color: cyan;');
            //console.log('---> _config.mapToken =', _config.mapToken);
            //console.log('---> _config.mapSize =', _config.mapSize);
            //console.log('---> _config.mapTileSize =', _config.mapTileSize);
            //console.log('---> _config.mapTileSizeOffset =', _config.mapTileSizeOffset);
            //console.log('---> _config.mapCols =', _config.mapCols);
            //console.log('---> _config.mapRows =', _config.mapRows);
            //console.log('---> _config.mapWidth =', _config.mapWidth);
            //console.log('---> _config.mapHeight =', _config.mapHeight);
            });

        // Define the function to run when everything is done loading
        let onWorldLoaded = function(){
            bindEventsToCanvas($canvasMap);
            $canvasMap.addClass('ready');
            };

        // Loop through each map layer and add the appropriate interactivity
        let layersPending = $mapLayers.length;
        $mapLayers.each(function(index, element){
            //console.log('-> checking layer #' + index + '...');
            let $thisLayer = $(element);
            let layerToken = $thisLayer.attr('data-layer') || false;
            if (layerToken === 'terrain'){
                initMapLayerCanvas($thisLayer, function(){
                    $thisLayer.addClass('ready');
                    layersPending--;
                    if (!layersPending){ onWorldLoaded(); }
                    });
                return true;
                }
            layersPending--;
            return true;
            });

        // Quick function for parsing the canvas data in a map layer
        function initWorldMap($canvasMap, $mapLayers, onComplete){
            //console.log('%c' + 'initWorldMap($canvasMap:' + typeof $canvasMap + ', $mapLayers:' + typeof $mapLayers + ')', 'color: magenta;');
            if (!$canvasMap || !$canvasMap.length){ console.error('initWorldMap() missing required $canvasMap!'); return false; }
            if (!$mapLayers || !$mapLayers.length){ console.error('initWorldMap() missing required $mapLayers!'); return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let $mapJson = $('script[data-json]', $canvasMap).first(), mapJson = $mapJson.html(), mapData = mapJson ? JSON.parse(mapJson) : false;
            if (!mapData || typeof mapData !== 'object' || !Object.keys(mapData).length){ console.error('initWorldMap() unable to parse mapData!'); return false; }
            let mapToken = mapData.map_token || false;
            let mapImage = mapData.map_image || false;
            let mapSize = mapData.map_size || false;
            let tileSize = mapData.tile_size || false;
            let tilesIndex = mapData.tiles_index || false;
            let spritesIndex = mapData.sprites_index || false;
            let portalsIndex = mapData.portals_index || false;
            if (!mapToken || !mapImage || !mapSize || !tileSize){ console.error('initWorldMap() missing required properties!', {mapToken, mapImage, mapSize, tileSize}); return false; }
            if (!tilesIndex || !spritesIndex || !portalsIndex){ console.error('initWorldMap() missing required indexes!', {tilesIndex, spritesIndex, portalsIndex}); return false; }
            if (!Array.isArray(mapSize) || mapSize.length < 2){ console.error('initWorldMap() mapSize must be an array of at least two values!'); return false; }
            if (!Array.isArray(tileSize) || tileSize.length < 2){ console.error('initWorldMap() tileSize must be an array of at least two values!'); return false; }
            let defaultMapSize = [_config.mapSize[0], _config.mapSize[1]];
            let defaultMapTileSize = [_config.mapTileSize[0], _config.mapTileSize[1]];
            _config.mapToken = mapToken;
            _config.mapImage = mapImage;
            _config.mapSize = [parseInt(mapSize[0]), parseInt(mapSize[1])];
            _config.mapTileSize = [parseInt(tileSize[0]), parseInt(tileSize[1])];
            _config.mapTileSizeOffset = [0, 0]; // default values
            _config.mapCols = _config.mapSize[0];
            _config.mapRows = _config.mapSize[1];
            _config.mapWidth = _config.mapSize[0] * _config.mapTileSize[0];
            _config.mapHeight = _config.mapSize[1] * _config.mapTileSize[1];
            if (_config.mapTileSize[0] > defaultMapTileSize[0]){ _config.mapTileSizeOffset[0] = Math.floor((_config.mapTileSize[0] - defaultMapTileSize[0]) / 2); }
            if (_config.mapTileSize[1] > defaultMapTileSize[1]){ _config.mapTileSizeOffset[1] = Math.floor((_config.mapTileSize[1] - defaultMapTileSize[1]) / 2); }
            _config.mapTilesIndex = tilesIndex;
            _config.mapSpritesIndex = spritesIndex;
            _config.mapPortalsIndex = portalsIndex;
            // Return true on success or run the oncomplete callback
            if (typeof onComplete === 'function'){ return onComplete(); }
            else { return true; }
            }

        // Quick function for parsing the canvas data in a map layer
        function initMapLayerCanvas($thisLayer, onComplete){
            //console.log('%c' + 'initMapLayerCanvas($thisLayer:' + typeof $thisLayer + ')', 'color: magenta;');
            if (!$thisLayer || !$thisLayer.length){ console.error('initMapLayerCanvas() missing required $thisLayer!'); return false; }
            if (!$('canvas', $thisLayer).length){ console.error('initMapLayerCanvas() $thisLayer missing required <canvas>!'); return false; }
            if (!$('script[data-json]', $thisLayer).length){ console.error('initMapLayerCanvas() $thisLayer missing required <script data-json>!'); return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let layerToken = $thisLayer.attr('data-layer');
            let $canvas = $('canvas', $thisLayer), canvas = $canvas[0], ctx = canvas.getContext('2d');
            let $canvasJson = $('script[data-json]', $thisLayer).first(), canvasJson = $canvasJson.html(), canvasData = canvasJson ? JSON.parse(canvasJson) : false;
            if (!canvasData || typeof canvasData !== 'object' || !Object.keys(canvasData).length){ console.error('initMapLayerCanvas() unable to parse canvasData!'); return false; }
            let mapImage = _config.mapImage;
            let mapWidth = _config.mapWidth;
            let mapHeight = _config.mapHeight;
            let canvasTiles = canvasData.canvas_tiles;
            $canvas.css({top: 0, left: 0, width: mapWidth, height: mapHeight});
            $canvas.attr('width', mapWidth).attr('height', mapHeight);
            ctx.width = mapWidth, ctx.height = mapHeight;
            $thisLayer.empty().append($canvas);
            //console.log('---> time to load the sprite sheet for this map layer!');
            ctx.clearRect(0, 0, mapWidth, mapHeight);
            let spriteSheet = new Image();
            let onLoadError = function(){
                console.error('----> canvas image failed to load from ' + mapImage + '!');
                if (typeof onComplete === 'function'){ onComplete(); }
                return false;
                }
            let onLoadSuccess = function(){
                //console.log('%c' + '----> canvas image loaded successfully!', 'color: cyan;');
                indexCanvasTileData(layerToken, spriteSheet, canvasTiles);
                drawTilesToCanvas(layerToken);
                if (typeof onComplete === 'function'){ onComplete(); }
                return true;
                };
            spriteSheet.onerror = function(){ return onLoadError(); }
            spriteSheet.onload = function(){ return onLoadSuccess(); };
            //console.log('%c' + '---> loading sprite sheet image from ' + mapImage, 'color: cyan;');
            spriteSheet.src = mapImage;
            return true;
            }

        // Quick function for drawing tiles to a given canvas object
        function indexCanvasTileData(layerToken, spriteSheet, canvasTiles){
            //console.log('%c' + '~indexCanvasTileData(layerToken:' + layerToken + ', spriteSheet:' + typeof spriteSheet + ', canvasTiles:' + typeof canvasTiles + ')', 'color: magenta;');
            if (!layerToken || !spriteSheet || !canvasTiles){ console.error('indexCanvasTileData() missing required parameters!', {layerToken, spriteSheet, canvasTiles}); return false; }
            if (typeof canvasTiles !== 'object' || !Object.keys(canvasTiles).length){ console.error('indexCanvasTileData() required canvasTiles missing or malformed!'); return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let tileSize = _config.mapTileSize;
            let tilesIndex = _config.mapTilesIndex;
            let tilesIndexKeys = tilesIndex.keys;
            let tileDataKeys = Object.keys(canvasTiles);
            //console.log('---> loaded ', tilesIndexKeys.length, ' tile defs from index...');
            //console.log('---> found ', tileDataKeys.length, ' layer tiles in data...');
            //console.log('---> indexing ', tileDataKeys.length, ' tileDataKeys tiles for canvas...');
            let layersIndex = _world.layersIndex || {};
            let layerTilesIndex = _world.layerTilesIndex || {};
            let thisLayerData = layersIndex[layerToken] || {};
            let thisLayerTiles = layerTilesIndex[layerToken] || {};
            thisLayerData.token = layerToken;
            thisLayerData.sheet = spriteSheet;
            thisLayerData.tiles = canvasTiles;
            for (var i = 0; i < tileDataKeys.length; i++){
                let tileKey = tileDataKeys[i];
                let tileValue = canvasTiles[tileKey];
                let tilePos = tileKey.split('-').map(function(val){ return parseInt(val.trim()); });
                //console.log('---> processing tile #' + i + ' w/ tileKey = ' + tileKey + ' and tileValue = ' + tileValue);
                let tileSpriteKey = tileValue;
                let tileSpriteToken = tilesIndexKeys[tileSpriteKey];
                let tileSpriteInfo = tilesIndex[tileSpriteToken];
                let tileSpriteOffset = [tileSpriteInfo[0] || 0, tileSpriteInfo[1] || 0];
                let tileSpriteSize = [tileSpriteInfo[2] || tileSize[0], tileSpriteInfo[3] || tileSize[1]];
                let tileSpritePosition = [tilePos[0], tilePos[1], ((tilePos[0] - 1) * tileSpriteSize[0]), ((tilePos[1] - 1) * tileSpriteSize[1])];
                let tileSpriteEffects = {grid: true, hover: false, focus: false}; // default values
                //console.log('---> tileSpriteKey =', tileSpriteKey);
                //console.log('---> tileSpriteToken =', tileSpriteToken);
                //console.log('---> tileSpriteInfo =', tileSpriteInfo);
                let tilesIndexData = typeof thisLayerTiles[tileKey] !== 'undefined' ? thisLayerTiles[tileKey] : {};
                tilesIndexData.position = tileSpritePosition;
                tilesIndexData.effects = tileSpriteEffects;
                tilesIndexData.sprite = [tileSpriteKey, tileSpriteToken, tileSpriteOffset, tileSpriteSize];
                tilesIndexData.walkable = tileSpriteToken === 'void' ? false : true;
                tilesIndexData.dirty = false; // indicates if the tile has been changed since last draw
                //console.log('---> tilesIndexData =', tilesIndexData);
                thisLayerTiles[tileKey] = tilesIndexData;
                }
            layersIndex[layerToken] = thisLayerData;
            layerTilesIndex[layerToken] = thisLayerTiles;
            _world.layersIndex = layersIndex;
            _world.layerTilesIndex = layerTilesIndex;
            return true;
            }

        // Quick function for drawing tiles to a given layer's canvas object
        function drawTilesToCanvas(layerToken){
            //console.log('%c' + '~drawTilesToCanvas(layerToken:' + layerToken + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string'){ console.error('drawTilesToCanvas() missing required layerToken!'); return false; }
            let _config = gameSettings.worldConfig;
            let _elements = gameSettings.worldElements;
            let _world = gameSettings.worldState;
            let $worldDiv = _elements.world;
            let $canvasMap = _elements.map;
            let $thisLayer = $('.layer[data-layer="'+layerToken+'"]', $canvasMap);
            if (!$thisLayer || !$thisLayer.length){ console.error('drawTilesToCanvas() missing required $thisLayer!'); return false; }
            let layersIndex = _world.layersIndex;
            let layerTilesIndex = _world.layerTilesIndex;
            let thisLayerData = layersIndex[layerToken] || false;
            let thisLayerSheet = thisLayerData ? thisLayerData.sheet : false;
            let thisLayerTiles = layerTilesIndex[layerToken] || false;
            let thisLayerTilesKeys = thisLayerTiles ? Object.keys(thisLayerTiles) : false;
            if (!thisLayerData || !thisLayerSheet){ console.error('drawTilesToCanvas() missing required thisLayerData or thisLayerSheet!'); return false; }
            if (!thisLayerTiles || !thisLayerTilesKeys){ console.error('drawTilesToCanvas() missing required thisLayerTiles or thisLayerTilesKeys!'); return false; }
            //console.log('---> drawing ', thisLayerTilesKeys.length, ' thisLayerTilesKeys tiles to canvas...');
            let $canvas = $('canvas', $thisLayer), canvas = $canvas[0], ctx = canvas.getContext('2d');
            for (var i = 0; i < thisLayerTilesKeys.length; i++){
                let tileKey = thisLayerTilesKeys[i];
                let tilesIndexData = getLayerTileIndexData(layerToken, tileKey);
                drawTileToCanvas(layerToken, ctx, thisLayerSheet, tileKey, tilesIndexData);
                }
            return true;
            }

        // Quick function for calculating the column and row of a tile at a given pixel position
        function getTileAtPosition($overlay, xPos, yPos, applyOffset){
            //console.log('%c' + 'getTileAtPosition(' + xPos + ', ' + yPos + ')', 'color: magenta;');
            applyOffset = typeof applyOffset === 'boolean' ? applyOffset : true;
            xPos = xPos > 0 ? parseInt(xPos) : 0, yPos = yPos > 0 ? parseInt(yPos) : 0;
            let size = _config.mapTileSize;
            let width = $overlay.width(), height = $overlay.height(), offset = $overlay.offset();
            if (applyOffset){ xPos -= offset.left; yPos -= offset.top; }
            if (xPos < 0){ xPos = 0; } if (yPos < 0){ yPos = 0; }
            let thisCol = Math.floor(xPos / size[0]) + 1;
            let thisRow = Math.floor(yPos / size[1]) + 1;
            let thisPos = thisCol + '-' + thisRow;
            return thisPos;
            }

        // Quick function for getting a given layer tile's index data provided the layer token and tile key
        function getLayerTileIndexData(layerToken, tileKey){
            //console.log('%c' + '~getLayerTileIndexData(layerToken:' + layerToken + ', tileKey:' + tileKey + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ console.error('getLayerTileIndexData() missing required layerToken!'); return false; }
            if (!tileKey || typeof tileKey !== 'string' || !tileKey.length){ console.error('getLayerTileIndexData() missing required tileKey!'); return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let layerTilesIndex = _world.layerTilesIndex;
            let thisLayerTiles = layerTilesIndex[layerToken] || false;
            let thisTileData = thisLayerTiles[tileKey] || false;
            if (!thisLayerTiles || typeof thisLayerTiles !== 'object' || !Object.keys(thisLayerTiles).length){ console.error('getLayerTileIndexData() cannot find required thisLayerTiles @ layerTilesIndex['+layerToken+']!'); return false; }
            if (!thisTileData || typeof thisTileData !== 'object'){ console.error('getLayerTileIndexData() cannot find required thisTileData @ layerTilesIndex['+layerToken+']['+tileKey+']!'); return false; }
            layerTilesIndex[layerToken][tileKey] = thisTileData;
            return thisTileData;
            }

        // Quick function for getting a given layer tile's sprite data (the one with the offset, size, etc.) provided the layer token and tile token
        function getLayerTileSpriteData(layerToken, tileToken){
            //console.log('%c' + '~getLayerTileSpriteData(layerToken:' + layerToken + ', tileToken:' + tileToken + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ console.error('getLayerTileSpriteData() missing required layerToken!'); return false; }
            if (!tileToken || typeof tileToken !== 'string' || !tileToken.length){ console.error('getLayerTileSpriteData() missing required tileToken!'); return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let layerTilesIndex = _world.layerTilesIndex;
            if (!layerTilesIndex[layerToken]){ console.error('getLayerTileSpriteData() missing required layerTilesIndex[' + layerToken + ']!'); return false; }
            if (!layerTilesIndex[layerToken][tileToken]){ console.error('getLayerTileSpriteData() missing required layerTilesIndex[' + layerToken + '][' + tileToken + ']!'); return false; }
            if (!layerTilesIndex[layerToken][tileToken].sprite){ console.error('getLayerTileSpriteData() missing required layerTilesIndex[' + layerToken + '][' + tileToken + '].sprite!'); return false; }
            let layerTileData = layerTilesIndex[layerToken][tileToken];
            let spriteToken = layerTileData.sprite[1];
            let mapTilesIndex = _config.mapTilesIndex;
            if (!mapTilesIndex[spriteToken]){ console.error('getLayerTileSpriteData() missing required mapTilesIndex[' + spriteToken + ']!'); return false; }
            let tileSpriteInfo = mapTilesIndex[spriteToken];
            return tileSpriteInfo;
            }

        // Quick function for getting a given tile's data (the one with the offset, size, etc.) provided the tile token
        function getTileData(tileToken){
            //console.log('%c' + '~getTileData(tileToken:' + tileToken + ')', 'color: magenta;');
            if (!tileToken || typeof tileToken !== 'string' || !tileToken.length){ console.error('getTileData() missing required tileToken!'); return false; }
            let _config = gameSettings.worldConfig;
            let mapTilesIndex = _config.mapTilesIndex;
            let tileInfo = mapTilesIndex[tileToken] || false;
            if (!tileInfo){ console.error('getTileData() missing required entry "' + tileToken + '" in mapTilesIndex!'); return false; }
            return tileInfo;
            }

        // Quick function for getting a given sprite's data (the one with the offset, size, etc.) provided the sprite token
        function getSpriteData(spriteToken){
            //console.log('%c' + '~getSpriteData(spriteToken:' + spriteToken + ')', 'color: magenta;');
            if (!spriteToken || typeof spriteToken !== 'string' || !spriteToken.length){ console.error('getSpriteData() missing required spriteToken!'); return false; }
            let _config = gameSettings.worldConfig;
            let mapSpritesIndex = _config.mapSpritesIndex;
            let spriteInfo = mapSpritesIndex[spriteToken] || false;
            if (!spriteInfo){ console.error('getSpriteData() missing required entry "' + spriteToken + '" in mapSpritesIndex!'); return false; }
            return spriteInfo;
            }

        // Quick function for getting a given portal's data (the one with the offset, size, etc.) provided the portal token
        function getPortalData(portalToken){
            //console.log('%c' + '~getPortalData(portalToken:' + portalToken + ')', 'color: magenta;');
            if (!portalToken || typeof portalToken !== 'string' || !portalToken.length){ console.error('getPortalData() missing required portalToken!'); return false; }
            let _config = gameSettings.worldConfig;
            let mapPortalsIndex = _config.mapPortalsIndex;
            let portalInfo = mapPortalsIndex[portalToken] || false;
            if (!portalInfo){ console.error('getPortalData() missing required entry "' + portalToken + '" in mapPortalsIndex!'); return false; }
            return portalInfo;
            }

        // Quick function for drawing a single tile to a given layer's canvas object given data
        function drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileData){
            //console.log('%c' + '~drawTileToCanvas(layerToken:' + layerToken + ', ctx, spriteSheet, tileKey:' + tileKey + ', tileData:' + typeof tileData + ')', 'color: magenta;');
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            // collect the tile data from the index
            let tilePosition = tileData.position; // col, row, x, y
            let tileEffects = tileData.effects; // grid, hover, focus
            let tileSprite = tileData.sprite; // key, token, offset, size
            let tileSpriteKey = tileSprite[0];
            let tileSpriteToken = tileSprite[1];
            let tileSpriteOffset = tileSprite[2];
            let tileSpriteSize = tileSprite[3];
            // check if the tile is focused or hovered and apply the effects
            let tileHasGrid = tileEffects.grid;
            let tileIsFocused = tileEffects.focus;
            let tileIsHovered = tileEffects.hover;
            // sprite: draw the main tile sprite at the correct position
            ctx.drawImage(spriteSheet,
                tileSpriteOffset[0], tileSpriteOffset[1], // source offset
                tileSpriteSize[0], tileSpriteSize[1], // source size
                tilePosition[2], tilePosition[3], // destination offset
                tileSpriteSize[0], tileSpriteSize[1] // destination size
                );
            // grid/focus/hover: draw any overlay images as defined in the effects
            let gridSpriteData = getSpriteData('grid');
            let hoverSpriteData = getSpriteData('hover');
            let focusSpriteData = getSpriteData('focus');
            if (!gridSpriteData){ console.warn('drawTileToCanvas() unable to find grid sprite data for layer ' + layerToken + '!'); }
            if (!hoverSpriteData){ console.warn('drawTileToCanvas() unable to find hover sprite data for layer ' + layerToken + '!'); }
            if (!focusSpriteData){ console.warn('drawTileToCanvas() unable to find focus sprite data for layer ' + layerToken + '!'); }
            if (tileHasGrid && gridSpriteData){
                let gridSpriteOpacity = 0.3;
                if (tileSpriteToken === 'void'){ gridSpriteOpacity = 0.1; }
                else if (tileSpriteToken === 'grass'){ gridSpriteOpacity = 0.6; }
                ctx.globalAlpha = gridSpriteOpacity;
                ctx.globalCompositeOperation = 'overlay';
                ctx.drawImage(spriteSheet,
                    gridSpriteData[0], gridSpriteData[1], // source offset
                    tileSpriteSize[0], tileSpriteSize[1], // source size
                    tilePosition[2], tilePosition[3], // destination offset
                    tileSpriteSize[0], tileSpriteSize[1] // destination size
                    );
                ctx.globalAlpha = 1.0;
                ctx.globalCompositeOperation = 'normal';
                }
            // hover: draw another image at the same positon but w/ the border sprite
            if (tileIsHovered && hoverSpriteData){
                ctx.globalAlpha = 0.9;
                ctx.globalCompositeOperation = 'screen';
                ctx.drawImage(spriteSheet,
                    hoverSpriteData[0], hoverSpriteData[1], // source offset
                    tileSpriteSize[0], tileSpriteSize[1], // source size
                    tilePosition[2], tilePosition[3], // destination offset
                    tileSpriteSize[0], tileSpriteSize[1] // destination size
                    );
                ctx.globalAlpha = 1.0;
                ctx.globalCompositeOperation = 'normal';
                }
            // focus: draw another image at the same positon but w/ the border sprite
            if (tileIsFocused && focusSpriteData){
                ctx.drawImage(spriteSheet,
                    focusSpriteData[0], focusSpriteData[1], // source offset
                    tileSpriteSize[0], tileSpriteSize[1], // source size
                    tilePosition[2], tilePosition[3], // destination offset
                    tileSpriteSize[0], tileSpriteSize[1] // destination size
                    );
                }
            // Return true on success
            return true;
            };

        // Quick functions for updating any canvas map layer tiles that have changed properties
        function refreshCanvasTiles(layerToken){
            //console.log('%c' + '~refreshCanvasTiles(layerToken:' + layerToken + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string'){ console.error('refreshCanvasTiles() missing required layerToken!'); return false; }
            if (refreshCanvasTiles._scheduled){ return; }
            refreshCanvasTiles._scheduled = true;
            requestAnimationFrame(() => {
                refreshCanvasTiles._scheduled = false;
                refreshCanvasTilesForReal(layerToken);
                });
            return;
            }
        function refreshCanvasTilesForReal(layerToken) {
            //console.log('%c' + '~refreshCanvasTilesForReal(layerToken:' + layerToken + ')', 'color: magenta;');
            let _config = gameSettings.worldConfig;
            let _elements = gameSettings.worldElements;
            let _world = gameSettings.worldState;
            let $worldDiv = _elements.world;
            let $canvasMap = _elements.map;
            let $thisLayer = $('.layer[data-layer="'+layerToken+'"]', $canvasMap);
            if (!$thisLayer || !$thisLayer.length){ console.error('refreshCanvasTilesForReal() missing required $thisLayer!'); return false; }
            let layersIndex = _world.layersIndex;
            let thisLayerData = layersIndex[layerToken] || false;
            if (!thisLayerData || !thisLayerData.sheet || !thisLayerData.tiles){ console.error('refreshCanvasTilesForReal() missing required _world.layersIndex[' + layerToken + ']!'); return false; }
            let layerTilesIndex = _world.layerTilesIndex;
            let thisLayerTiles = layerTilesIndex[layerToken] || false;
            if (!thisLayerTiles || typeof thisLayerTiles !== 'object'){ console.error('refreshCanvasTiles() missing required _world.layerTilesIndex[' + layerToken + ']!'); return false; }
            let spriteSheet = thisLayerData.sheet;
            let layerTileKeys = Object.keys(thisLayerTiles);
            let $canvas = $('canvas', $thisLayer), canvas = $canvas[0], ctx = canvas.getContext('2d');
            for (var i = 0; i < layerTileKeys.length; i++){
                let tileKey = layerTileKeys[i];
                let tileData = thisLayerTiles[tileKey];
                if (!tileData.dirty){ continue; }
                drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileData);
                tileData.dirty = false; // reset the dirty flag
                }
            return true;
            }

        // Quick function for applying a "focus" effect to a given layer tile
        function focusLayerTile(layerToken, tilePosition){
            //console.log('%c' + 'focusLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
            if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let thisTileData = getLayerTileIndexData(layerToken, tilePosition);
            if (!thisTileData){ console.error('focusLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
            thisTileData.effects.focus = true;
            thisTileData.dirty = true;
            refreshCanvasTiles(layerToken);
            return true;
            }

        // Quick function for renmoving a "focus" effect from a given layer tile
        function unfocusLayerTile(layerToken, tilePosition){
            //console.log('%c' + 'unfocusLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
            if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
            let thisTileData = getLayerTileIndexData(layerToken, tilePosition);
            if (!thisTileData){ console.error('focusLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
            thisTileData.effects.focus = false;
            thisTileData.dirty = true;
            refreshCanvasTiles(layerToken);
            return true;
            }

        // Quick function for applying a "hover" effect to a given layer tile
        function hoverLayerTile(layerToken, tilePosition){
            //console.log('%c' + 'hoverLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
            if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
            let thisTileData = getLayerTileIndexData(layerToken, tilePosition);
            if (!thisTileData){ console.error('hoverLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
            thisTileData.effects.hover = true;
            thisTileData.dirty = true;
            refreshCanvasTiles(layerToken);
            return true;
            }

        // Quick function for removing a "hover" effect from a given layer tile
        function unhoverLayerTile(layerToken, tilePosition){
            //console.log('%c' + 'unhoverLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
            if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
            let thisTileData = getLayerTileIndexData(layerToken, tilePosition);
            if (!thisTileData){ console.error('unhoverLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
            thisTileData.effects.hover = false;
            thisTileData.dirty = true;
            refreshCanvasTiles(layerToken);
            return true;
            }

        // Quick function for binding events to a given layer's canvas object
        function bindEventsToCanvas($canvasMap){
            //console.log('%c' + '~bindEventsToCanvas($canvasMap:' + typeof $canvasMap + ')', 'color: magenta;');
            if (!$canvasMap || !$canvasMap.length){ console.error('bindEventsToCanvas() missing required $canvasMap!'); return false; }
            let _config = gameSettings.worldConfig;
            let _elements = gameSettings.worldElements;
            let _world = gameSettings.worldState;
            let _cursor = _world.cursor;
            let layerToken = 'terrain'; // TODO: make this dynamic maybe?
            let $clickOverlay = $('#click-overlay', $canvasMap);
            let focusTimeouts = {}, focusTimeoutDuration = _config.mapEffects.focusTimeout;
            let hoverTimeouts = {}, hoverTimeoutDuration = _config.mapEffects.hoverTimeout;
            let lastMouseClick, lastMouseOver;
            $clickOverlay.bind('click', function(e){
                if (_cursor.moving){ return false; }
                //console.log('%c' + 'Map overlay click event!', 'color: cyan;');
                let oldPos = _cursor.position;
                let thisPos = getTileAtPosition($clickOverlay, e.pageX, e.pageY);
                let tileData = getLayerTileIndexData(layerToken, thisPos);
                if (thisPos === oldPos || thisPos === lastMouseClick){ return; }
                if (!tileData.walkable){ return; }
                //console.log('%c' + 'Mouse click event triggered for position ' + thisPos + '!', 'color: orange;');
                lastMouseClick = thisPos;
                focusLayerTile(layerToken, thisPos);
                if (focusTimeouts[oldPos]){ clearTimeout(focusTimeouts[oldPos]); }
                focusTimeouts[thisPos] = setTimeout(function(){
                    moveToPosition(thisPos, function(){
                        unfocusLayerTile(layerToken, oldPos);
                        }, true);
                    }, focusTimeoutDuration);
                });
            $clickOverlay.bind('mousemove', function(e){
                if (_cursor.moving){ return false; }
                //console.log('%c' + 'Map overlay mousemove event!', 'color: cyan;');
                let thisPos = getTileAtPosition($clickOverlay, e.pageX, e.pageY);
                let tileData = getLayerTileIndexData(layerToken, thisPos);
                let showPointer = thisPos !== _cursor.position && tileData.walkable;
                $clickOverlay.css({cursor: showPointer ? 'pointer' : 'default'});
                if (thisPos === lastMouseOver){ return; }
                if (!tileData.walkable){ return; }
                //console.log('%c' + 'Mouse move event triggered at position ' + thisPos + '!', 'color: orange;');
                lastMouseOver = thisPos;
                hoverLayerTile(layerToken, thisPos);
                if (hoverTimeouts[thisPos]){ clearTimeout(hoverTimeouts[thisPos]); }
                hoverTimeouts[thisPos] = setTimeout(function(){
                    unhoverLayerTile(layerToken, thisPos);
                    }, hoverTimeoutDuration);
                });
            // Return true on success
            return true;
            }

        // Quick function for moving cursor to a given map position
        function moveToPosition(newPosition, onComplete, forceMove, animateMove){
            //console.log('%c' + 'moveToPosition(' + newPosition + ')', 'color: magenta;');
            if (!newPosition || typeof newPosition === 'undefined'){ console.error('newPosition is undefined!'); return false; }
            else if (typeof newPosition !== 'string' || !newPosition.match(/^[0-9]+\-[0-9]+$/)){ console.error('newPosition is invalid!', newPosition); return false; }
            else { newPosition = newPosition.split('-'); }
            forceMove = typeof forceMove === 'boolean' ? forceMove : false;
            animateMove = typeof animateMove === 'boolean' ? animateMove : true;
            let _config = gameSettings.worldConfig;
            let _elements = gameSettings.worldElements;
            let _world = gameSettings.worldState;
            let _worldCursor = _world.cursor;
            let _mapEffects = _config.mapEffects;
            let _mapTileSize = _config.mapTileSize;
            let _mapTileSizeOffset = _config.mapTileSizeOffset;
            let $worldDiv = _elements.world;
            let $canvasMap = _elements.map;
            var $tilesLayer = $('.layer.tiles', $canvasMap);
            let $objectsLayer = $('.layer.objects', $canvasMap);
            let $eventsLayers = $('.layer.events', $canvasMap);
            let $cursorSprite = $('.sprite.cursor', $objectsLayer);
            let $actionsDropdown = $('#action-dropdown', $worldDiv);
            if (!$tilesLayer || !$tilesLayer.length){ console.error('$tilesLayer does not exist!'); return false; }
            if (!$objectsLayer || !$objectsLayer.length){ console.error('$objectsLayer does not exist!'); return false; }
            if (!$eventsLayers || !$eventsLayers.length){ console.error('$eventsLayers do not exist!'); return false; }
            if (!$cursorSprite || !$cursorSprite.length){ console.error('$cursorSprite not found!'); return false; }
            if (!$actionsDropdown || !$actionsDropdown.length){ console.error('$actionsDropdown not found!'); return false; }
            let thisOldCol = _worldCursor.col;
            let thisOldRow = _worldCursor.row;
            let thisNewCol = parseInt(newPosition[0]);
            let thisNewRow = parseInt(newPosition[1]);
            if (thisNewCol === thisOldCol && thisNewRow === thisOldRow && !forceMove){ console.error('$cursorSprite already at position!'); return false; }
            let thisHorDir = (thisNewCol > thisOldCol) ? 'right' : (thisNewCol < thisOldCol) ? 'left' : false;
            let thisVerDir = (thisNewRow > thisOldRow) ? 'down' : (thisNewRow < thisOldRow) ? 'up' : false;
            let thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join(' and '); })(thisHorDir, thisVerDir);
            let thisShiftDist = Math.sqrt(Math.pow(thisNewCol - thisOldCol, 2) + Math.pow(thisNewRow - thisOldRow, 2));
            let tileOffsetX = ((thisNewCol - 1) * _mapTileSize[0]) + _mapTileSizeOffset[0];
            let tileOffsetY = ((thisNewRow - 1) * _mapTileSize[1]) + _mapTileSizeOffset[1];
            $canvasMap.addClass('busy');
            _worldCursor.moving = true;
            $actionsDropdown.removeClass('active');
            $eventsLayers.removeClass('has-zoom');
            $('.sprite.zoom', $eventsLayers).removeClass('zoom');
            // Move the cursor to the new position first and foremost
            let moveTimeout;
            let timeoutDuration = _mapEffects.moveTimeout;
            let travelDuration = _mapEffects.moveTravel * thisShiftDist;
            let onMoveComplete = function(){
                _worldCursor.col = thisNewCol;
                _worldCursor.row = thisNewRow;
                _worldCursor.position = thisNewCol + '-' + thisNewRow;
                _worldCursor.direction = thisShiftDir.replace(/ and /g, '-');
                $cursorSprite.attr('data-col', thisNewCol);
                $cursorSprite.attr('data-row', thisNewRow);
                $cursorSprite.attr('data-pos', _worldCursor.position);
                updateMapPosition();
                saveWorldState();
                focusLayerTile('terrain', _worldCursor.position);
                if (moveTimeout){ clearTimeout(moveTimeout); }
                moveTimeout = setTimeout(function(){
                    _worldCursor.moving = false;
                    $canvasMap.removeClass('busy');
                    if (typeof onComplete === 'function'){ onComplete(); }
                    }, timeoutDuration);
                };
            if (animateMove){
                $cursorSprite.animate({
                    left: tileOffsetX + 'px',
                    top: tileOffsetY + 'px',
                    }, travelDuration, 'linear', onMoveComplete);
                } else {
                $cursorSprite.css({
                    left: tileOffsetX + 'px',
                    top: tileOffsetY + 'px',
                    }); onMoveComplete();
                }
            // If there are any team sprites, move them as well (it's okay if they lay behind the cursor)
            let $teamSprites = $('.sprite.team', $objectsLayer);
            if ($teamSprites && $teamSprites.length){
                let teamOffsetX = tileOffsetX;
                let teamOffsetY = tileOffsetY;
                let teamTravelDuration = travelDuration;
                teamTravelDuration += 50;
                $teamSprites.each(function(index, element){
                    let $thisSprite = $(element);
                    let $innerSprite = $('.sprite', $thisSprite);
                    let imgSize = $thisSprite.attr('data-size') || 40;
                    let imgSizeX = imgSize + 'x' + imgSize;
                    teamTravelDuration += 30; // add a little extra time for the team sprites to move
                    if (thisVerDir === 'up'){ teamOffsetY += 10; }
                    else if (thisVerDir === 'down'){ teamOffsetY -= 10; }
                    if (thisHorDir === 'left'){ teamOffsetX += 20; }
                    else if (thisHorDir === 'right'){ teamOffsetX -= 20; }
                    $thisSprite.attr('data-dir', thisHorDir);
                    if ($thisSprite.is('.robot')){ $innerSprite.addClass('sprite_'+imgSizeX+'_07'); }
                    else if ($thisSprite.is('.player')){ $innerSprite.addClass('sprite_'+imgSizeX+'_09'); }
                    let onTeamMoveComplete = function(){
                        if ($thisSprite.is('.robot')){ $innerSprite.removeClass('sprite_'+imgSizeX+'_07'); }
                        else if ($thisSprite.is('.player')){ $innerSprite.removeClass('sprite_'+imgSizeX+'_09'); }
                        };
                    if (animateMove){
                        $thisSprite.animate({
                            top: teamOffsetY + 'px',
                            left: teamOffsetX + 'px'
                            }, teamTravelDuration, 'linear', onTeamMoveComplete);
                        } else {
                        $thisSprite.css({
                            top: teamOffsetY + 'px',
                            left: teamOffsetX + 'px'
                            }); onTeamMoveComplete();
                        }
                    });
                }
            // And now we should move the map itself so that the characters are always centered in the viewport
            let worldWidth = $worldDiv.width();
            let worldHeight = $worldDiv.height();
            let mapWidth = $canvasMap.width();
            let mapHeight = $canvasMap.height();
            let targetX = tileOffsetX;
            let targetY = tileOffsetY;
            // Now calculate the new translate values for the map container
            let translateX = 0, translateY = 0;
            if (mapWidth < worldWidth){ translateX = (worldWidth - mapWidth) / 2; }
            else if (targetX < (worldWidth / 2)){ translateX = 0; }
            else if (targetX > (mapWidth - (worldWidth / 2))){ translateX = -(mapWidth - worldWidth); }
            else { translateX = -(targetX - (worldWidth / 2)); }
            if (mapHeight < worldHeight){ translateY = (worldHeight - mapHeight) / 2; }
            else if (targetY < (worldHeight / 2)){ translateY = 0; }
            else if (targetY > (mapHeight - (worldHeight / 2))){ translateY = -(mapHeight - worldHeight); }
            else { translateY = -(targetY - (worldHeight / 2)); }
            // Apply the new translate values to the map container
            $canvasMap.css({ transform: 'translate(' + translateX + 'px, ' + translateY + 'px)' });
            return true;
            }

        // Quick function for running post-update checks and actions after moving the cursor
        function updateMapPosition(){
            //console.log('%c' + 'updateMapPosition()', 'color: magenta;');
            let _config = gameSettings.worldConfig;
            let _elements = gameSettings.worldElements;
            let _world = gameSettings.worldState;
            let _worldCursor = _world.cursor;
            let _mapEffects = _config.mapEffects;
            let _mapTileSize = _config.mapTileSize;
            let _mapTileSizeOffset = _config.mapTileSizeOffset;
            let _userId = _config.userId;
            let _playerId = _config.playerId;
            let _playerToken = _config.playerToken;
            let _playerRobots = _config.playerRobots;
            let $worldDiv = _elements.world;
            let $canvasMap = _elements.map;
            let cursorPosition = _worldCursor.position;
            let newPosition = cursorPosition.split('-');
            let thisNewCol = parseInt(newPosition[0]);
            let thisNewRow = parseInt(newPosition[1]);
            let $positionDisplay = $('#position-display > .wrapper', $worldDiv);
            $positionDisplay.text('X:' + thisNewCol + ' Y:' + thisNewRow);
            let $actionsDropdown = $('#action-dropdown', $worldDiv);
            let $actionsDropdownWrapper = $('> .wrapper', $actionsDropdown);
            $actionsDropdown.css({left: '', top: ''}).removeAttr('data-dir');
            $actionsDropdownWrapper.empty();
            //console.log('-> checking if there are any events for this position...');
            let $eventsLayers = $('.layer.events', $canvasMap);
            let $zoomLayer = $('.layer.zoom', $canvasMap);
            if (!$eventsLayers || !$eventsLayers.length){ console.error('updateMapPosition() missing required $eventsLayers!'); return false; }
            if (!$zoomLayer || !$zoomLayer.length){ console.error('updateMapPosition() missing required $zoomLayer!'); return false; }
            //console.log('-> $eventsLayer found, checking for events...');

            //$zoomLayer.empty();
            $eventsLayers.removeClass('has-zoom');
            $('.sprite', $zoomLayer).each(function(){
                let $sprite = $(this), layer = $sprite.attr('data-layer'), $layer = $('.layer[data-layer="'+layer+'"]', $canvasMap);
                $sprite.appendTo($layer).removeAttr('data-layer');
                });
            setTimeout(function(){ $('.sprite', $eventsLayers).removeClass('zoom'); }, 100);
            //$('.sprite', $zoomLayer).removeClass('zoom');

            let $eventAtPosition = $('.sprite[data-col="' + thisNewCol + '"][data-row="' + thisNewRow + '"]', $eventsLayers);
            if (!$eventAtPosition || !$eventAtPosition.length){ return; }
            let $eventLayer = $eventAtPosition.closest('.layer.events');
            let eventLayer = $eventLayer.attr('data-layer') || false;

            var showDropdown = false;
            var dropdownMarkup = '';
            var dataLabel = $eventAtPosition.attr('data-label');
            var dataBattle = $eventAtPosition.attr('data-battle');
            var dataPortal = $eventAtPosition.attr('data-portal');
            if (dataBattle){
                showDropdown = true;
                if (dataLabel){ dropdownMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                else { dropdownMarkup += '<strong class="label">Battle Options</strong>'; }
                //dropdownMarkup += '<a class="button" data-action="battle-info" data-battle="'+dataBattle+'"><span>View Details</span></a>';
                if (_playerRobots.length){  dropdownMarkup += '<a class="button start-battle" data-action="start-battle" data-battle="'+dataBattle+'"><span>Start Battle</span></a>'; }
                else { dropdownMarkup += '<a class="button start-battle disabled" data-battle="'+dataBattle+'"><span>Start Battle</span></a>'; }
                }
            if (dataPortal && dataPortal.indexOf('goto__') !== -1){
                showDropdown = true;
                dropdownMarkup += '<strong class="label">Portal Options</strong>';
                dropdownMarkup += '<a class="button" data-action="portal-info" data-portal="'+dataPortal+'"><span>View Details</span></a>';
                dropdownMarkup += '<a class="button" data-action="enter-portal" data-portal="'+dataPortal+'"><span>Enter Portal</span></a>';
                }
            if (!showDropdown){ return; }

            $actionsDropdown.css({
                left: ((thisNewCol - 1) * _mapTileSize[0] + _mapTileSizeOffset[0]) + 'px',
                top: ((thisNewRow - 1) * _mapTileSize[1] + _mapTileSizeOffset[1]) + 'px',
                }).attr('data-dir', _worldCursor.direction);
            $actionsDropdownWrapper.html(dropdownMarkup);
            $actionsDropdown.addClass('active');

            $eventLayer.addClass('has-zoom');
            $eventAtPosition.appendTo($zoomLayer).attr('data-layer', eventLayer);
            setTimeout(function(){ $eventAtPosition.addClass('zoom'); }, 100);

            $('.button', $actionsDropdown).bind('click', function(e){
                //console.log('%c' + 'Action button clicked!', 'color: cyan;');
                e.preventDefault();
                let $button = $(this);
                let action = $button.attr('data-action') || false;
                //console.log('-> action =', action);
                if (action === 'start-battle' || action === 'battle-info'){
                    let battleId = $button.attr('data-battle') || false;
                    //console.log('-> battleId =', battleId);
                    if (action === 'battle-info'){
                        //console.log('-> showing battle info for ID ' + battleId + '!');
                        alert('Battle ID: ' + battleId + '\n\nThis is where you would show battle details.');
                        }
                    else if (action === 'start-battle'){
                        //console.log('-> starting battle with ID ' + battleId + '!');
                        let battleVars = [];
                        battleVars.push('wap=false'); // i hate this
                        battleVars.push('this_user_id=' + _userId);
                        battleVars.push('this_player_id=' + _playerId);
                        battleVars.push('this_player_token=' + _playerToken);
                        battleVars.push('this_player_robots=' + _playerRobots.join(','));
                        battleVars.push('this_battle_token=' + battleId);
                        let battleHref = 'battle.php?' + battleVars.join('&');
                        $thisWorld.addClass('hidden');
                        window.location.href = battleHref;
                        }
                    }
                if (action === 'enter-portal' || action === 'portal-info'){
                    let portalName = $button.attr('data-portal') || false;
                    //console.log('-> portalName =', portalName);
                    if (action === 'portal-info'){
                        //console.log('-> showing portal info for ID ' + portalName + '!');
                        alert('Portal Name: ' + portalName + '\n\nThis is where you would show portal details.');
                        }
                    else if (action === 'enter-portal'){
                        //console.log('-> entering portal with name ' + portalName + '!');
                        let worldToken = portalName.replace(/^goto__/i, '');
                        let worldHref = 'world.php?world=' + worldToken;
                        $thisWorld.addClass('hidden');
                        window.location.href = worldHref;
                        }

                    }
                });
            return true;
            }

        // Quick function for sending a snapshot of persistent world values back to the server for saving
        function saveWorldState(){
            //console.log('%c' + 'saveWorldState()', 'color: magenta;');
            if (saveWorldState._scheduled){ return; }
            saveWorldState._scheduled = true;
            setTimeout(function(){
                saveWorldState._scheduled = false;
                saveWorldStateForReal();
                }, 1000);
            return;
            }
        function saveWorldStateForReal(){
            //console.log('%c' + 'saveWorldStateForReal()', 'color: magenta;');
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            let _worldCursor = _world.cursor;
            let _userId = _config.userId;
            let worldData = {
                lastWorld: _config.mapToken,
                lastPlayer: _config.playerToken,
                lastPosition: _worldCursor.position,
                lastDirection: _worldCursor.direction,
                };
            //console.log('---> saving world state:', worldData);
            $.ajax({
                url: 'world.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'save', world_data: worldData },
                success: function(response){
                    //console.log('---> save_world.php response:', response);
                    return true;
                    },
                error: function(xhr, status, error){
                    //console.error('saveWorldState() failed to save world state!', status, error);
                    return false;
                    }
                });
            }

        // Bind a click event to the home button in the header that'll bring us to prototype menu
        let $homeButton = $('#home-button', $thisWorld);
        if ($homeButton && $homeButton.length){
            $homeButton.bind('click', function(e){
                e.preventDefault();
                //console.log('%c' + 'Home button clicked!', 'color: cyan;');
                //if (!confirm('Are you sure you want to leave the world map?')){ return; }
                $thisWorld.addClass('hidden');
                let homeMenuURL = $homeButton.attr('data-home-url') || 'prototype.php';
                window.location.href = homeMenuURL;
                return true;
                });
            }

        // Bind a click event to the reset button in the header that'll clear world data to start over (dev/debug only)
        let $resetButton = $('#reset-button', $thisWorld);
        if ($resetButton && $resetButton.length){
            $resetButton.bind('click', function(e){
                e.preventDefault();
                //console.log('%c' + 'Reset button clicked!', 'color: cyan;');
                if (!confirm('Are you sure you want to reset the world map?')){ return; }
                $thisWorld.addClass('hidden');
                let resetMenuURL = $resetButton.attr('data-reset-url') || 'world.php?reset=world';
                window.location.href = resetMenuURL;
                return true;
                });
            }

        // Bind click events to the player switcher options in the world map header
        let $playerSwitcher = $('#player-switcher', $thisWorld);
        if ($playerSwitcher && $playerSwitcher.length){
            $('.option[data-player]', $playerSwitcher).bind('click', function(e){
                e.preventDefault();
                let playerToken = $(this).attr('data-player') || false;
                //console.log('%c' + 'Player switcher clicked for ' + playerToken + '!', 'color: cyan;');
                $thisWorld.addClass('hidden');
                let worldReloadURL = 'world.php?player=' + playerToken;
                window.location.href = worldReloadURL;
                return true;
                });
            }

        // Collect the map cursor element and automatically move it to the spawn position
        let $mapCursor = $mapLayers.filter('.objects').find('.sprite.cursor');
        if ($mapCursor && $mapCursor.length){
            let cursorPosition = $mapCursor.attr('data-pos');
            let autoMoveTimeout = setTimeout(function(){
                moveToPosition(cursorPosition, null, true, false);
                }, 300);
            }


        }

    // -- ??????? -- //


    // -- READY TO FADE-IN WORLD MAP -- //

    // If the window is hidden, make sure we unhide it (w/ fade if allowed)
    if ($thisWorld.hasClass('hidden')){ $thisWorld.removeClass('hidden'); }
    $thisWorld.addClass('ready');

});