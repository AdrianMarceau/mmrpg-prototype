
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
        }
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
        // Collect details about this map from the markup
        //let _config = gameSettings.worldConfig;
        //let _world = gameSettings.worldState;
        //let _worldCursor = _world.cursor;
        //let mapCols = parseInt($canvasMap.attr('data-cols'));
        //let mapRows = parseInt($canvasMap.attr('data-rows'));
        //let mapTileSize = _config.mapTileSize;
        //_config.mapCols = mapCols;
        //_config.mapRows = mapRows;
        //_config.mapWidth = mapCols * mapTileSize[0];
        //_config.mapHeight = mapRows * mapTileSize[1];
        let mapToken = $canvasMap.attr('data-token') || false;
        let dataSize = $canvasMap.attr('data-size') || false;
        //console.log('---> data-token =', mapToken);
        //console.log('---> data-size =', dataSize);
        _config.mapToken = mapToken || _config.mapToken;
        if (dataSize && dataSize.length){
            mapSize = dataSize.split('x').map(function(val){ return parseInt(val.trim()); });
            //console.log('---> mapSize =', mapSize);
            let defaultMapSize = [_config.mapSize[0], _config.mapSize[1]];
            let defaultMapTileSize = [_config.mapTileSize[0], _config.mapTileSize[1]];
            if (mapSize.length === 4){ _config.mapSize = [mapSize[0], mapSize[1]]; _config.mapTileSize = [mapSize[2], mapSize[3]]; }
            else if (mapSize.length === 3){ _config.mapSize = [mapSize[0], mapSize[1]]; _config.mapTileSize = [mapSize[2], mapSize[2]]; }
            else if (mapSize.length === 2){ _config.mapSize = [mapSize[0], mapSize[0]]; _config.mapTileSize = [mapSize[1], mapSize[1]]; }
            else if (mapSize.length === 1){ _config.mapSize = [mapSize[0], mapSize[0]]; _config.mapTileSize = [mapSize[0], mapSize[0]]; }
            _config.mapCols = _config.mapSize[0];
            _config.mapRows = _config.mapSize[1];
            _config.mapWidth = _config.mapSize[0] * _config.mapTileSize[0];
            _config.mapHeight = _config.mapSize[1] * _config.mapTileSize[1];
            if (_config.mapTileSize[0] > defaultMapTileSize[0]){ _config.mapTileSizeOffset[0] = Math.floor((_config.mapTileSize[0] - defaultMapTileSize[0]) / 2); }
            if (_config.mapTileSize[1] > defaultMapTileSize[1]){ _config.mapTileSizeOffset[1] = Math.floor((_config.mapTileSize[1] - defaultMapTileSize[1]) / 2); }
            }
        //console.log('---> _config.mapSize =', _config.mapSize);
        //console.log('---> _config.mapTileSize =', _config.mapTileSize);
        //console.log('---> _config.mapTileSizeOffset =', _config.mapTileSizeOffset);
        //console.log('---> _config.mapCols =', _config.mapCols);
        //console.log('---> _config.mapRows =', _config.mapRows);
        //console.log('---> _config.mapWidth =', _config.mapWidth);
        //console.log('---> _config.mapHeight =', _config.mapHeight);

        // Loop through each map and add the appropriate interactivity
        $mapLayers.each(function(index, element){
            //console.log('-> checking layer #' + index + '...');
            let _config = gameSettings.worldConfig;
            let $thisLayer = $(element);
            let layerToken = $thisLayer.attr('data-layer') || false;

            if (layerToken === 'terrain'){
                //console.log('%c' + '--> Generating tilemap for terrain layer #' + index + '!', 'color: orange;');
                initMapLayerCanvas($thisLayer, function(){
                    //console.log('%c' + '--> onComplete() for initMapLayerCanvas() reached!', 'color: green;');
                    $thisLayer.addClass('ready');
                    });
                return true;
                }

            });


        // Quick function for calculating the column and row of a tile at a given pixel position
        function getTileAtPosition($overlay, xPos, yPos, applyOffset){
            //console.log('%c' + 'getTileAtPosition(' + xPos + ', ' + yPos + ')', 'color: magenta;');
            applyOffset = typeof applyOffset === 'boolean' ? applyOffset : true;
            xPos = xPos > 0 ? parseInt(xPos) : 0, yPos = yPos > 0 ? parseInt(yPos) : 0;
            let size = _config.mapTileSize;
            let width = $overlay.width(), height = $overlay.height(), offset = $overlay.offset();
            if (applyOffset){ xPos -= offset.left; yPos -= offset.top; }
            if (xPos < 0){ xPos = 0; } if (yPos < 0){ yPos = 0; }
            //console.log('-> canvas(', width, ',', height, ')');
            //console.log('-> offset(', offset.left, ',', offset.top, ')');
            //console.log('-> pixel position(', xPos, ',', yPos, ')');
            //console.log('-> adjusted position', (applyOffset ? '(' + xPos + ',' + yPos + ')' : 'n/a'));
            let thisCol = Math.floor(xPos / size[0]) + 1;
            let thisRow = Math.floor(yPos / size[1]) + 1;
            let thisPos = thisCol + '-' + thisRow;
            //console.log('-> grid position(', thisCol, ',', thisRow, ')');
            //console.log('-> thisPos =', thisPos);
            return thisPos;
            }

        // Quick function for parsing the canvas data in a map layer
        function initMapLayerCanvas($thisLayer, onComplete){
            //console.log('%c' + 'initMapLayerCanvas($thisLayer:' + typeof $thisLayer + ')', 'color: magenta;');
            if (!$thisLayer || !$thisLayer.length){ console.error('initMapLayerCanvas() missing required $thisLayer!'); return false; }
            if (!$('canvas', $thisLayer).length){ console.error('initMapLayerCanvas() $thisLayer missing required <canvas>!'); return false; }
            if (!$('script[data-json]', $thisLayer).length){ console.error('initMapLayerCanvas() $thisLayer missing required <script data-json>!'); return false; }
            let layerToken = $thisLayer.attr('data-layer');
            let $canvas = $('canvas', $thisLayer), canvas = $canvas[0], ctx = canvas.getContext('2d');
            let $canvasJson = $('script[data-json]', $thisLayer).first(), canvasJson = $canvasJson.html(), canvasData = canvasJson ? JSON.parse(canvasJson) : false;
            if (!canvasData || typeof canvasData !== 'object' || !Object.keys(canvasData).length){ console.error('initMapLayerCanvas() unable to parse canvasData!'); return false; }
            let mapImage = canvasData.map_image || false;
            let mapSize = canvasData.map_size || false;
            let mapOffset = canvasData.map_offset || false;
            let tileSize = canvasData.tile_size || false;
            let tileIndex = canvasData.tile_index || false;
            let tileData = canvasData.tile_data || false;
            if (!mapImage || !mapSize || !mapOffset){ console.error('----> missing required properties!', {mapImage, mapSize, mapOffset}); return false; }
            if (!tileSize || !tileIndex || !tileData){ console.error('----> missing required properties!', {tileSize, tileIndex, tileData}); return false; }
            let canvasWidth = parseInt(mapSize[0]) || _config.mapWidth;
            let canvasHeight = parseInt(mapSize[1]) || _config.mapHeight;
            let canvasOffsetX = parseInt(mapOffset[0]) || 0;
            let canvasOffsetY = parseInt(mapOffset[1]) || 0;
            //console.log('---> setting canvas width and height to', canvasWidth, '×', canvasHeight);
            $canvas.css({top: canvasOffsetY + 'px', left: canvasOffsetX + 'px', width: canvasWidth, height: canvasHeight});
            $canvas.attr('width', canvasWidth).attr('height', canvasHeight);
            ctx.width = canvasWidth, ctx.height = canvasHeight;
            $thisLayer.empty().append($canvas);
            //console.log('---> ctx.size =', 'buffer:', canvas.width, '×', canvas.height, 'css:',    canvas.clientWidth, '×', canvas.clientHeight);
            // load the image into memory
            //console.log('---> time to generate tiles for this later!');
            ctx.clearRect(0, 0, canvasWidth, canvasHeight);
            let spriteSheet = new Image();
            spriteSheet.onerror = function(){
                console.error('----> canvas image failed to load from ' + mapImage + '!');
                return false;
                };
            spriteSheet.onload = function(){
                //console.log('%c' + '----> canvas image loaded successfully!', 'color: cyan;');
                indexCanvasTileData(layerToken, spriteSheet, canvasData);
                drawTilesToCanvas(layerToken);
                bindEventsToCanvasTiles(layerToken);
                if (typeof onComplete === 'function'){ onComplete(); }
                return true;
                };
            //console.log('%c' + '---> loading sprite sheet image from ' + mapImage, 'color: cyan;');
            spriteSheet.src = mapImage;
            return true;
            }

        // Quick function for drawing tiles to a given canvas object
        function indexCanvasTileData(layerToken, spriteSheet, canvasData){
            //console.log('%c' + '~indexCanvasTileData(layerToken:' + layerToken + ', spriteSheet:' + typeof spriteSheet + ', canvasData:' + typeof canvasData + ')', 'color: magenta;');
            if (!layerToken || !spriteSheet || !canvasData){ console.error('indexCanvasTileData() missing required parameters!', {layerToken, spriteSheet, canvasData}); return false; }
            if (typeof canvasData !== 'object' || !Object.keys(canvasData).length){ console.error('indexCanvasTileData() required canvasData missing or malformed!'); return false; }
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            //let mapImage = canvasData.map_image;
            //let mapSize = canvasData.map_size;
            //let mapOffset = canvasData.map_offset;
            let tileSize = canvasData.tile_size;
            let tileIndex = canvasData.tile_index;
            let tileIndexKeys = tileIndex.keys;
            let tileData = canvasData.tile_data;
            let tileDataKeys = Object.keys(tileData);
            //console.log('---> loaded ', tileIndexKeys.length, ' tile defs from index...');
            //console.log('---> found ', tileDataKeys.length, ' layer tiles in data...');
            //console.log('---> indexing ', tileDataKeys.length, ' tileDataKeys tiles for canvas...');
            let layersIndex = _world.layersIndex || {};
            let layerTilesIndex = _world.layerTilesIndex || {};
            let thisLayerData = layersIndex[layerToken] || {};
            let thisLayerTiles = layerTilesIndex[layerToken] || {};
            thisLayerData.token = layerToken;
            thisLayerData.sheet = spriteSheet;
            thisLayerData.data = canvasData;
            for (var i = 0; i < tileDataKeys.length; i++){
                let tileKey = tileDataKeys[i];
                let tileValue = tileData[tileKey];
                let tilePos = tileKey.split('-').map(function(val){ return parseInt(val.trim()); });
                //console.log('---> processing tile #' + i + ' w/ tileKey = ' + tileKey + ' and tileValue = ' + tileValue);
                let tileSpriteKey = tileValue;
                let tileSpriteToken = tileIndexKeys[tileSpriteKey];
                let tileSpriteInfo = tileIndex[tileSpriteToken];
                let tileSpriteOffset = [tileSpriteInfo[0] || 0, tileSpriteInfo[1] || 0];
                let tileSpriteSize = [tileSpriteInfo[2] || tileSize[0], tileSpriteInfo[3] || tileSize[1]];
                let tileSpritePosition = [tilePos[0], tilePos[1], ((tilePos[0] - 1) * tileSpriteSize[0]), ((tilePos[1] - 1) * tileSpriteSize[1])];
                let tileSpriteEffects = {grid: true, hover: false, focus: false}; // default values
                //console.log('---> tileSpriteKey =', tileSpriteKey);
                //console.log('---> tileSpriteToken =', tileSpriteToken);
                //console.log('---> tileSpriteInfo =', tileSpriteInfo);
                let tileIndexData = typeof thisLayerTiles[tileKey] !== 'undefined' ? thisLayerTiles[tileKey] : {};
                tileIndexData.position = tileSpritePosition;
                tileIndexData.effects = tileSpriteEffects;
                tileIndexData.sprite = [tileSpriteKey, tileSpriteToken, tileSpriteOffset, tileSpriteSize];
                tileIndexData.dirty = false; // indicates if the tile has been changed since last draw
                //console.log('---> tileIndexData =', tileIndexData);
                thisLayerTiles[tileKey] = tileIndexData;
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
            let thisLayerData = layersIndex[layerToken];
            let thisLayerTiles = layerTilesIndex[layerToken];
            if (!thisLayerData || !thisLayerData.sheet || !thisLayerData.data){ console.error('drawTilesToCanvas() missing required thisLayerData!'); return false; }
            if (!thisLayerTiles || typeof thisLayerTiles !== 'object' || !Object.keys(thisLayerTiles).length){ console.error('drawTilesToCanvas() missing required thisLayerTiles!'); return false; }
            let thisLayerTileKeys = Object.keys(thisLayerTiles);
            let spriteSheet = thisLayerData.sheet;
            let canvasData = thisLayerData.data;
            if (!spriteSheet || !(spriteSheet instanceof Image) || !canvasData || typeof canvasData !== 'object'){ console.error('drawTilesToCanvas() missing required spriteSheet or canvasData!'); return false; }
            if (!canvasData || !canvasData.tile_size || !canvasData.tile_index || !canvasData.tile_data){ console.error('drawTilesToCanvas() missing required tile_size, tile_index, or tile_data!'); return false; }
            //console.log('---> drawing ', thisLayerTileKeys.length, ' thisLayerTileKeys tiles to canvas...');
            let $canvas = $('canvas', $thisLayer), canvas = $canvas[0], ctx = canvas.getContext('2d');
            for (var i = 0; i < thisLayerTileKeys.length; i++){
                let tileKey = thisLayerTileKeys[i];
                //let tileIndexData = thisLayerTiles[tileKey];
                let tileIndexData = getLayerTileIndexData(layerToken, tileKey);
                drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileIndexData);
                }
            return true;
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
            //console.log('---> returning tileIndexData for tileKey ' + tileKey + ' on layer ' + layerToken + ':', thisTileData);
            // make sure the returned tile data object is actually in the parent now
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
            let layersIndex = _world.layersIndex;
            let thisLayerData = layersIndex[layerToken] || false;
            if (!thisLayerData || typeof thisLayerData !== 'object' || !thisLayerData.data){ console.error('getLayerTileSpriteData() missing required thisLayerData!'); return false; }
            let canvasData = thisLayerData.data;
            if (!canvasData.tile_index || typeof canvasData.tile_index !== 'object'){ console.error('getLayerTileSpriteData() missing required canvasData.tile_index!'); return false; }
            let tileIndex = canvasData.tile_index;
            let tileSpriteInfo = tileIndex[tileToken] || false;
            if (!tileSpriteInfo || typeof tileSpriteInfo !== 'object'){ console.error('getLayerTileSpriteData() missing required tileSpriteInfo!'); return false; }
            //console.log('---> returning tile sprite data for token ' + tileToken + ':', tileSpriteInfo);
            return tileSpriteInfo;
            }

        // Quick function for drawing a single tile to a given layer's canvas object given data
        function drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileIndexData){
            //console.log('%c' + '~drawTileToCanvas(layerToken:' + layerToken + ', ctx, spriteSheet, tileKey:' + tileKey + ', tileIndexData:' + typeof tileIndexData + ')', 'color: magenta;');
            let _config = gameSettings.worldConfig;
            let _world = gameSettings.worldState;
            // collect the tile data from the index
            let tilePosition = tileIndexData.position; // col, row, x, y
            let tileEffects = tileIndexData.effects; // grid, hover, focus
            let tileSprite = tileIndexData.sprite; // key, token, offset, size
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
            let gridSpriteData = getLayerTileSpriteData(layerToken, 'grid');
            let hoverSpriteData = getLayerTileSpriteData(layerToken, 'hover');
            let focusSpriteData = getLayerTileSpriteData(layerToken, 'focus');
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
            if (!$thisLayer || !$thisLayer.length){ console.error('refreshCanvasTiles() missing required $thisLayer!'); return false; }
            let layersIndex = _world.layersIndex;
            let layerTilesIndex = _world.layerTilesIndex;
            let thisLayerData = layersIndex[layerToken];
            let thisLayerTiles = layerTilesIndex[layerToken];
            let thisLayerTileKeys = Object.keys(thisLayerTiles);
            if (!thisLayerData || !thisLayerData.sheet || !thisLayerData.data){ console.error('refreshCanvasTiles() missing required thisLayerData!'); return false; }
            if (!thisLayerTiles || typeof thisLayerTiles !== 'object' || !Object.keys(thisLayerTiles).length){ console.error('refreshCanvasTiles() missing required thisLayerTiles!'); return false; }
            let spriteSheet = thisLayerData.sheet;
            let canvasData = thisLayerData.data;
            if (!spriteSheet || !(spriteSheet instanceof Image) || !canvasData || typeof canvasData !== 'object'){ console.error('refreshCanvasTiles() missing required spriteSheet or canvasData!'); return false; }
            if (!canvasData || !canvasData.tile_size || !canvasData.tile_index || !canvasData.tile_data){ console.error('refreshCanvasTiles() missing required tile_size, tile_index, or tile_data!'); return false; }
            //console.log('---> refreshing ', thisLayerTileKeys.length, ' thisLayerTileKeys tiles on canvas...');
            let $canvas = $('canvas', $thisLayer), canvas = $canvas[0], ctx = canvas.getContext('2d');
            for (var i = 0; i < thisLayerTileKeys.length; i++){
                let tileKey = thisLayerTileKeys[i];
                let tileIndexData = thisLayerTiles[tileKey];
                if (!tileIndexData.dirty){ continue; }
                drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileIndexData);
                tileIndexData.dirty = false; // reset the dirty flag
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
            //let layerTilesIndex = _world.layerTilesIndex;
            //let thisLayerTiles = layerTilesIndex[layerToken] || false;
            //let thisTileData = thisLayerTiles[tilePosition] || false;
            //if (!thisLayerTiles || !thisTileData){ return false; }
            let thisTileData = getLayerTileIndexData(layerToken, tilePosition);
            if (!thisTileData){ console.error('focusLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
            thisTileData.effects.focus = true;
            thisTileData.dirty = true;
            //console.log('---> focusing layer tile at position ' + tilePosition + '!', '\n----> thisTileData =', thisTileData);
            refreshCanvasTiles(layerToken);
            return true;
            }

        // Quick function for renmoving a "focus" effect from a given layer tile
        function unfocusLayerTile(layerToken, tilePosition){
            //console.log('%c' + 'unfocusLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
            if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
            //let _config = gameSettings.worldConfig;
            //let _world = gameSettings.worldState;
            //let layerTilesIndex = _world.layerTilesIndex;
            //let thisLayerTiles = layerTilesIndex[layerToken] || false;
            //let thisTileData = thisLayerTiles[tilePosition] || false;
            //if (!thisLayerTiles || !thisTileData){ return false; }
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
            //let _config = gameSettings.worldConfig;
            //let _world = gameSettings.worldState;
            //let layerTilesIndex = _world.layerTilesIndex;
            //let thisLayerTiles = layerTilesIndex[layerToken] || false;
            //let thisTileData = thisLayerTiles[tilePosition] || false;
            //if (!thisLayerTiles || !thisTileData){ return false; }
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
            //let _config = gameSettings.worldConfig;
            //let _world = gameSettings.worldState;
            //let layerTilesIndex = _world.layerTilesIndex;
            //let thisLayerTiles = layerTilesIndex[layerToken] || false;
            //let thisTileData = thisLayerTiles[tilePosition] || false;
            //if (!thisLayerTiles || !thisTileData){ return false; }
            let thisTileData = getLayerTileIndexData(layerToken, tilePosition);
            if (!thisTileData){ console.error('unhoverLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
            thisTileData.effects.hover = false;
            thisTileData.dirty = true;
            refreshCanvasTiles(layerToken);
            return true;
            }

        // Quick function for binding events to a given layer's canvas object
        function bindEventsToCanvasTiles(layerToken){
            //console.log('%c' + '~bindEventsToCanvasTiles(layerToken:' + layerToken + ')', 'color: magenta;');
            if (!layerToken || typeof layerToken !== 'string'){ console.error('bindEventsToCanvasTiles() missing required layerToken!'); return false; }
            let _config = gameSettings.worldConfig;
            let _elements = gameSettings.worldElements;
            let _world = gameSettings.worldState;
            let _cursor = _world.cursor;
            let $worldDiv = _elements.world;
            let $canvasMap = _elements.map;
            let layersIndex = _world.layersIndex;
            let layerTilesIndex = _world.layerTilesIndex;
            if (!layersIndex || !layerTilesIndex){ console.error('bindEventsToCanvasTiles() missing required layersIndex or layerTilesIndex!'); return false; }
            let $thisLayer = $('.layer[data-layer="'+layerToken+'"]', $canvasMap);
            let thisLayerData = layersIndex[layerToken];
            let thisLayerTiles = layerTilesIndex[layerToken];
            if (!$thisLayer || !$thisLayer.length){ console.error('bindEventsToCanvasTiles() missing required $thisLayer!'); return false; }
            if (!thisLayerData || !thisLayerData.sheet || !thisLayerData.data){ console.error('bindEventsToCanvasTiles() missing required thisLayerData!'); return false; }
            if (!thisLayerTiles || typeof thisLayerTiles !== 'object' || !Object.keys(thisLayerTiles).length){ console.error('bindEventsToCanvasTiles() missing required thisLayerTiles!'); return false; }
            let spriteSheet = thisLayerData.sheet;
            let canvasData = thisLayerData.data;
            if (!spriteSheet || !(spriteSheet instanceof Image) || !canvasData || typeof canvasData !== 'object'){ console.error('bindEventsToCanvasTiles() missing required spriteSheet or canvasData!'); return false; }
            if (!canvasData || !canvasData.tile_size || !canvasData.tile_index || !canvasData.tile_data){ console.error('bindEventsToCanvasTiles() missing required tile_size, tile_index, or tile_data!'); return false; }
            //console.log('---> binding events to canvas tiles for layer ' + layerToken + '...');
            let $clickOverlay = $('#click-overlay', $canvasMap);
            let focusTimeouts = {}, focusTimeoutDuration = _config.mapEffects.focusTimeout;
            let hoverTimeouts = {}, hoverTimeoutDuration = _config.mapEffects.hoverTimeout;
            let lastMouseClick, lastMouseOver;
            $clickOverlay.bind('click', function(e){
                if (_cursor.moving){ return false; }
                //console.log('%c' + 'Map overlay click event!', 'color: cyan;');
                let oldPos = _cursor.position;
                let thisPos = getTileAtPosition($clickOverlay, e.pageX, e.pageY);
                if (thisPos === oldPos || thisPos === lastMouseClick){ return; }
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
                if (thisPos === lastMouseOver){ return; }
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
            //let $currActiveTile = $('.tile.active', $tilesLayer);
            //let $tileAtPosition = $('.tile[data-col="' + thisNewCol + '"][data-row="' + thisNewRow + '"]', $tilesLayer);
            //if (!$tileAtPosition || !$tileAtPosition.length){ console.error('$tileAtPosition not found!'); return false; }
            let thisHorDir = (thisNewCol > thisOldCol) ? 'right' : (thisNewCol < thisOldCol) ? 'left' : false;
            let thisVerDir = (thisNewRow > thisOldRow) ? 'down' : (thisNewRow < thisOldRow) ? 'up' : false;
            let thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join(' and '); })(thisHorDir, thisVerDir);
            let thisShiftDist = Math.sqrt(Math.pow(thisNewCol - thisOldCol, 2) + Math.pow(thisNewRow - thisOldRow, 2));
            //console.log('-> new position (' + thisNewCol + '-' + thisNewRow + ') is ' + thisShiftDir + ' of old position (' + thisOldCol + '-' + thisOldRow + ')');
            //console.log('-> old position: (' + thisOldCol + '-' + thisOldRow + ')');
            //console.log('-> new position: (' + thisNewCol + '-' + thisNewRow + ')');
            //console.log('-> shift direction: (' + thisShiftDir + ')');
            //console.log('-> shift distance: (' + thisShiftDist + ' tiles)');
            let tileOffsetX = ((thisNewCol - 1) * _mapTileSize[0]) + _mapTileSizeOffset[0];
            let tileOffsetY = ((thisNewRow - 1) * _mapTileSize[1]) + _mapTileSizeOffset[0];
            //console.log('-> moving cursor to offset ' + tileOffsetX + 'x' + tileOffsetY + '...');
            $canvasMap.addClass('busy');
            _worldCursor.moving = true;
            $actionsDropdown.removeClass('active');
            //$currActiveTile.removeClass('active');
            //$tileAtPosition.addClass('active');
            $eventsLayers.removeClass('has-zoom');
            $('.sprite.zoom', $eventsLayers).removeClass('zoom');
            // Move the cursor to the new position first and foremost
            let moveTimeout;
            let timeoutDuration = _mapEffects.moveTimeout;
            let travelDuration = _mapEffects.moveTravel * thisShiftDist;
            let onMoveComplete = function(){
                //console.log('-> cursor moved to tile offset ' + tileOffsetX + 'x' + tileOffsetY + '!');
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
                    top: tileOffsetY + 'px',
                    left: tileOffsetX + 'px'
                    }, travelDuration, 'linear', onMoveComplete);
                } else {
                $cursorSprite.css({
                    top: tileOffsetY + 'px',
                    left: tileOffsetX + 'px'
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
            // (as much as possible anyway, without overscroll). This is done by moving the entire map container
            // using it's transform: translate() values.  Make sure we pre-calculate the current dimensions of
            // the map itself, its parent, and the position of the cursor sprite so we know where to center to
            let worldWidth = $worldDiv.width();
            let worldHeight = $worldDiv.height();
            let mapWidth = $canvasMap.width();
            let mapHeight = $canvasMap.height();
            let targetX = tileOffsetX;
            let targetY = tileOffsetY;
            //console.log('-> world size: ' + worldWidth + 'x' + worldHeight);
            //console.log('-> map size: ' + mapWidth + 'x' + mapHeight);
            //console.log('-> target position: ' + targetX + 'x' + targetY);
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
            //console.log('-> translateX =', translateX, '| translateY =', translateY);
            //if (animateMove){ $canvasMap.addClass('animate'); }
            //else { $canvasMap.removeClass('animate'); }
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
            //console.log('-> updating position display to (' + thisNewCol + '-' + thisNewRow + ') (from: ', newPosition, ')');
            let $positionDisplay = $('#position-display > .wrapper', $worldDiv);
            $positionDisplay.text('X:' + thisNewCol + ' Y:' + thisNewRow);
            //console.log('-> moving the actions dropdown to position (' + thisNewCol + '-' + thisNewRow + ')');
            //console.log('-> _worldCursor.direction =', _worldCursor.direction);
            let $actionsDropdown = $('#action-dropdown', $worldDiv);
            $actionsDropdown.css({
                top: ((thisNewRow - 1) * _mapTileSize[1]) + 'px',
                left: ((thisNewCol - 1) * _mapTileSize[0]) + 'px'
                }).attr('data-dir', _worldCursor.direction);
            //console.log('-> checking if there are any events for this position...');
            let $eventsLayers = $('.layer.events', $canvasMap);
            if ($eventsLayers && $eventsLayers.length){
                //console.log('-> $eventsLayer found, checking for events...');
                $eventsLayers.removeClass('has-zoom');
                $('.sprite', $eventsLayers).removeClass('zoom');
                let $eventAtPosition = $('.sprite[data-col="' + thisNewCol + '"][data-row="' + thisNewRow + '"]', $eventsLayers);
                if ($eventAtPosition && $eventAtPosition.length){
                    //console.log('-> event found at position ' + thisNewCol + '-' + thisNewRow + '!', $eventAtPosition);
                    let $eventLayer = $eventAtPosition.closest('.layer.events');
                    var showDropdown = false;
                    var dropdownMarkup = '';
                    var dataBattle = $eventAtPosition.attr('data-battle');
                    if (dataBattle){
                        showDropdown = true;
                        dropdownMarkup += '<strong class="label">Battle Options</strong>';
                        dropdownMarkup += '<a class="button" data-action="battle-info" data-battle="'+dataBattle+'"><span>View Details</span></a>';
                        if (_playerRobots.length){  dropdownMarkup += '<a class="button" data-action="battle" data-battle="'+dataBattle+'"><span>Start Battle</span></a>'; }
                        }
                    if (showDropdown){
                        $('> .wrapper', $actionsDropdown).html(dropdownMarkup);
                        $actionsDropdown.addClass('active');
                        $eventLayer.addClass('has-zoom');
                        $eventAtPosition.addClass('zoom');
                        $('.button', $actionsDropdown).bind('click', function(e){
                            //console.log('%c' + 'Action button clicked!', 'color: cyan;');
                            e.preventDefault();
                            let $button = $(this);
                            let action = $button.attr('data-action') || false;
                            let battleId = $button.attr('data-battle') || false;
                            //console.log('-> action =', action, '| battleId =', battleId);
                            if (action === 'battle-info'){
                                alert('Battle ID: ' + battleId + '\n\nThis is where you would show battle details.');
                                }
                            else if (action === 'battle'){
                                //console.log('-> starting battle with ID ' + battleId + '!');
                                // battle.php?wap=false&this_user_id=412&this_player_token=dr-light&this_player_id=3&this_battle_token=dr-light-phase0-met&this_player_robots=137_mega-man,203_roll,171_pirate-man
                                //console.log('-> gathering battle variables...');
                                //console.log('-> _userId =', _userId, '\n', '-> _playerId =', _playerId, '\n', '-> _playerToken =', _playerToken, '\n', '-> _config.playerRobots =', _config.playerRobots);
                                let battleVars = [];
                                battleVars.push('wap=false'); // i hate this
                                battleVars.push('this_user_id=' + _userId);
                                battleVars.push('this_player_id=' + _playerId);
                                battleVars.push('this_player_token=' + _playerToken);
                                battleVars.push('this_player_robots=' + _playerRobots.join(','));
                                battleVars.push('this_battle_token=' + battleId);
                                //battleVars.push('this_battle_token=dr-light-phase0-met'); // DEBUG
                                let battleHref = 'battle.php?' + battleVars.join('&');
                                $thisWorld.addClass('hidden');
                                window.location.href = battleHref;
                                /*if (confirm('Navigate to this URL?\n' + battleHref)){
                                    $thisWorld.addClass('hidden');
                                    window.location.href = battleHref;
                                    }*/
                                }
                            });
                        }
                    }
                }
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