
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
    mapBattleIndex: {},
    mapBattleSymbols: {},
    windowWidth: 1024, // default only
    widthHeight: 768, // default only
    mmrpgWidth: 800, // default only
    mmrpgHeight: 600, // default only
    worldWidth: 800, // default only
    worldHeight: 600, // default only
    canvasWidth: 800, // default only
    canvasHeight: 600, // default only
    };
gameSettings.worldElements = {
    mmrpg: null,
    world: null,
    canvas: null,
    map: null,
    layers: null,
    cursor: null,
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

// Create the mmrpgWorldMap class object for this mode
class mmrpgWorldMap {

    // Constructor function for the world map
    constructor($mmrpg){
        //console.log('%c' + 'mmrpgWorldMap() constructor called!', 'color: green;');
        let _self = this;
        _self.config = gameSettings.worldConfig;
        _self.elements = gameSettings.worldElements;
        _self.state = gameSettings.worldState;
        _self.initWorld($mmrpg);
        }

    // Quick function to initialize world map variables
    initWorld($mmrpg){
        //console.log('%c' + 'mmrpgWorldMap.initWorld() called!', 'color: green;');
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let $thisPrototype = $mmrpg;
        let $thisWorld = $('#world', $thisPrototype);
        let $thisCanvas = $('#canvas', $thisWorld);
        let $canvasMap = $('#map', $thisCanvas);
        let $mapLayers = $('.layer', $canvasMap);
        let $worldCursor = $('.sprite.cursor', $canvasMap);
        let $homeButton = $('#home-button', $thisWorld);
        let $resetButton = $('#reset-button', $thisWorld);
        let $playerSwitcher = $('#player-switcher', $thisWorld);
        let $sideButtons = $('#side-buttons', $thisWorld);
        let $actionDropdown = $('#action-dropdown', $thisWorld);
        _elements.mmrpg = $thisPrototype;
        _elements.world = $thisWorld;
        _elements.canvas = $thisCanvas;
        _elements.map = $canvasMap;
        _elements.layers = $mapLayers;
        _elements.cursor = $worldCursor;
        _elements.homeButton = $homeButton;
        _elements.resetButton = $resetButton;
        _elements.playerSwitcher = $playerSwitcher;
        _elements.sideButtons = $sideButtons;
        _elements.actionDropdown = $actionDropdown;
        if ($canvasMap.length && $mapLayers.length){
            //console.log('%c' + 'World map canvas found with ' + $mapLayers.length + ' layers...', 'color: orange;');
            // Initialize the world map with the provided canvas and layers
            _self.initWorldMap($canvasMap, $mapLayers, function(){
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
            }
        return true;
        }

    // Quick function for parsing the canvas data in a map layer
    initWorldMap($canvasMap, $mapLayers, onComplete){
        //console.log('%c' + 'initWorldMap($canvasMap:' + typeof $canvasMap + ', $mapLayers:' + typeof $mapLayers + ')', 'color: magenta;');
        if (!$canvasMap || !$canvasMap.length){ console.error('initWorldMap() missing required $canvasMap!'); return false; }
        if (!$mapLayers || !$mapLayers.length){ console.error('initWorldMap() missing required $mapLayers!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let $thisPrototype = _elements.mmrpg;
        let $thisWorld = _elements.world;
        // Collect the main json object for this world map and then parse it into the appropriate config values for the game
        let $mapJson = $('script[data-json]', $canvasMap).first(), mapJson = $mapJson.html(), mapData = mapJson ? JSON.parse(mapJson) : false;
        if (!mapData || typeof mapData !== 'object' || !Object.keys(mapData).length){ console.error('initWorldMap() unable to parse mapData!'); return false; }
        let mapToken = mapData.map_token || false;
        let mapImage = mapData.map_image || false;
        let mapSize = mapData.map_size || false;
        let tileSize = mapData.tile_size || false;
        let tilesIndex = mapData.tiles_index || false;
        let spritesIndex = mapData.sprites_index || false;
        let portalsIndex = mapData.portals_index || false;
        let startPosition = mapData.start_position || false;
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
        _config.mapStartPosition = startPosition; // default to the top-left corner
        _config.windowWidth = $(window).width();
        _config.windowHeight = $(window).height();
        _config.mmrpgWidth = _elements.mmrpg.outerWidth();
        _config.mmrpgHeight = _elements.mmrpg.outerHeight();
        _config.worldWidth = _elements.world.outerWidth();
        _config.worldHeight = _elements.world.outerHeight();
        _config.canvasWidth = _elements.canvas.outerWidth();
        _config.canvasHeight = _elements.canvas.outerHeight();
        // Define the function to run when everything is done loading
        let onWorldLoaded = function(){
            _self.bindEventsToCanvas($canvasMap);
            _self.bindEventsToWorld($thisWorld);
            let startPosition = '1-1';
            if (_config.mapStartPosition){ startPosition = _config.mapStartPosition; }
            else if (portalsIndex['spawn']){ startPosition = portalsIndex['spawn'].join('-'); }
            _self.moveToPosition(startPosition, null, true, false);
            setTimeout(function(){
                $thisWorld.removeClass('hidden');
                $thisWorld.addClass('ready');
                $canvasMap.addClass('ready');
                }, 100);
            };
        // Define the function for run when each layer is done being rendered
        let layersPending = $mapLayers.length;
        let reduceLayersPending = function(){
            layersPending--;
            if (!layersPending){ return onWorldLoaded(); }
            else { return true; }
            };
        // Loop through each map layer and generate graphics and/or interactivity
        $mapLayers.each(function(index, element){
            //console.log('-> checking layer #' + index + '...');
            let $thisLayer = $(element);
            let layerToken = $thisLayer.attr('data-layer') || false;
            let $jsonScripts = $('script[data-json]', $thisLayer);
            let onLayerReady = function(){
                $thisLayer.addClass('ready');
                reduceLayersPending();
                return true;
                };
            if (layerToken === 'terrain'){
                _self.initMapLayerCanvas($thisLayer, onLayerReady);
                return true;
                }
            if ($jsonScripts.length > 0){
                //console.log('---> found ' + $jsonScripts.length + ' [data-json] scripts in layer #' + index + '!');
                $jsonScripts.each(function(index){
                    //console.log('---> processing script[data-json] #' + index + '...');
                    let $thisJson = $(this);
                    let jsonKind = $thisJson.attr('data-json'), jsonData = $thisJson.html(), jsonObject = jsonData ? JSON.parse(jsonData) : false;
                    if (!jsonData || !jsonData.length){ console.warn('---> JSON data for layer ' + layerToken + ' (script[data-json="'+jsonKind+'"]) was empty!'); return true; }
                    if (!jsonObject || typeof jsonObject !== 'object' || !Object.keys(jsonObject).length){ console.error('---> unable to parse JSON data for layer ' + layerToken + ' (script[data-json="'+jsonKind+'"])!'); return true; }
                    //console.log('---> parsed json layer data for ' + layerToken + ' (script[data-json="'+jsonKind+'"]) !!! jsonObject =', jsonObject);
                    let configName = 'map' + jsonKind[0].toUpperCase() + jsonKind.slice(1);
                    //console.log('---> setting _config.' + configName + ' =', jsonObject);
                    _config[configName] = jsonObject;
                    return true;
                    });
                }
            onLayerReady();
            return true;
            });
        // Return true on success or run the oncomplete callback
        if (typeof onComplete === 'function'){ return onComplete(); }
        else { return true; }
        }

    // Quick function for parsing the canvas data in a map layer
    initMapLayerCanvas($thisLayer, onComplete){
        //console.log('%c' + 'initMapLayerCanvas($thisLayer:' + typeof $thisLayer + ')', 'color: magenta;');
        if (!$thisLayer || !$thisLayer.length){ console.error('initMapLayerCanvas() missing required $thisLayer!'); return false; }
        if (!$('canvas', $thisLayer).length){ console.error('initMapLayerCanvas() $thisLayer missing required <canvas>!'); return false; }
        if (!$('script[data-json]', $thisLayer).length){ console.error('initMapLayerCanvas() $thisLayer missing required <script data-json>!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
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
            _self.indexCanvasTileData(layerToken, spriteSheet, canvasTiles);
            _self.drawTilesToCanvas(layerToken);
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
    indexCanvasTileData(layerToken, spriteSheet, canvasTiles){
        //console.log('%c' + '~indexCanvasTileData(layerToken:' + layerToken + ', spriteSheet:' + typeof spriteSheet + ', canvasTiles:' + typeof canvasTiles + ')', 'color: magenta;');
        if (!layerToken || !spriteSheet || !canvasTiles){ console.error('indexCanvasTileData() missing required parameters!', {layerToken, spriteSheet, canvasTiles}); return false; }
        if (typeof canvasTiles !== 'object' || !Object.keys(canvasTiles).length){ console.error('indexCanvasTileData() required canvasTiles missing or malformed!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
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
            let tileSpriteWalkable = true;
            if (tileSpriteToken === 'void' || tileSpriteToken.indexOf('void') !== -1){
                tileSpriteEffects.grid = false; // no grid for void/+
                tileSpriteWalkable = false; // void/+ tiles are not walkable
                }
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
    drawTilesToCanvas(layerToken){
        //console.log('%c' + '~drawTilesToCanvas(layerToken:' + layerToken + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string'){ console.error('drawTilesToCanvas() missing required layerToken!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let $thisWorld = _elements.world;
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
            let tilesIndexData = _self.getLayerTileIndexData(layerToken, tileKey);
            _self.drawTileToCanvas(layerToken, ctx, thisLayerSheet, tileKey, tilesIndexData);
            }
        return true;
        }

    // Quick function for calculating the column and row of a tile at a given pixel position
    getTileAtPosition($overlay, xPos, yPos, applyOffset){
        //console.log('%c' + 'getTileAtPosition(' + xPos + ', ' + yPos + ')', 'color: magenta;');
        applyOffset = typeof applyOffset === 'boolean' ? applyOffset : true;
        xPos = xPos > 0 ? parseInt(xPos) : 0, yPos = yPos > 0 ? parseInt(yPos) : 0;
        let _self = this;
        let _config = _self.config;
        let size = _config.mapTileSize;
        let width = $overlay.width(), height = $overlay.height(), offset = $overlay.offset();
        if (applyOffset){ xPos -= offset.left; yPos -= offset.top; }
        if (xPos < 0){ xPos = 0; } if (yPos < 0){ yPos = 0; }
        let thisCol = Math.floor(xPos / size[0]) + 1;
        let thisRow = Math.floor(yPos / size[1]) + 1;
        let thisPos = thisCol + '-' + thisRow;
        return thisPos;
        }

    // Quick function for calculating the relative difference between two positions
    getPositionRelative(position1, position2){
        //console.log('%c' + 'getPositionRelative(position1:' + position1 + ', position2:' + position2 + ')', 'color: magenta;');
        if (typeof position1 !== 'string' && !Array.isArray(position1)){ console.error('getPositionRelative() missing required position1!'); return false; }
        if (typeof position2 !== 'string' && !Array.isArray(position2)){ console.error('getPositionRelative() missing required position2!'); return false; }
        position1 = typeof position1 === 'string' ? position1.split('-') : position1;
        position2 = typeof position2 === 'string' ? position2.split('-') : position2;
        let positionRelative = [position2[0] - position1[0], position2[1] - position1[1]];
        return positionRelative;
        }

    // Quick function for getting a given layer tile's index data provided the layer token and tile key
    getLayerTileIndexData(layerToken, tileKey){
        //console.log('%c' + '~getLayerTileIndexData(layerToken:' + layerToken + ', tileKey:' + tileKey + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ console.error('getLayerTileIndexData() missing required layerToken!'); return false; }
        if (!tileKey || typeof tileKey !== 'string' || !tileKey.length){ console.error('getLayerTileIndexData() missing required tileKey!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let layerTilesIndex = _world.layerTilesIndex;
        let thisLayerTiles = layerTilesIndex[layerToken] || false;
        let thisTileData = thisLayerTiles[tileKey] || false;
        if (!thisLayerTiles || typeof thisLayerTiles !== 'object' || !Object.keys(thisLayerTiles).length){ console.error('getLayerTileIndexData() cannot find required thisLayerTiles @ layerTilesIndex['+layerToken+']!'); return false; }
        if (!thisTileData || typeof thisTileData !== 'object'){ console.error('getLayerTileIndexData() cannot find required thisTileData @ layerTilesIndex['+layerToken+']['+tileKey+']!'); return false; }
        layerTilesIndex[layerToken][tileKey] = thisTileData;
        return thisTileData;
        }

    // Quick function for getting a given layer tile's sprite data (the one with the offset, size, etc.) provided the layer token and tile token
    getLayerTileSpriteData(layerToken, tileToken){
        //console.log('%c' + '~getLayerTileSpriteData(layerToken:' + layerToken + ', tileToken:' + tileToken + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ console.error('getLayerTileSpriteData() missing required layerToken!'); return false; }
        if (!tileToken || typeof tileToken !== 'string' || !tileToken.length){ console.error('getLayerTileSpriteData() missing required tileToken!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
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
    getTileData(tileToken){
        //console.log('%c' + '~getTileData(tileToken:' + tileToken + ')', 'color: magenta;');
        if (!tileToken || typeof tileToken !== 'string' || !tileToken.length){ console.error('getTileData() missing required tileToken!'); return false; }
        let _self = this;
        let _config = _self.config;
        let mapTilesIndex = _config.mapTilesIndex;
        let tileInfo = mapTilesIndex[tileToken] || false;
        if (!tileInfo){ console.error('getTileData() missing required entry "' + tileToken + '" in mapTilesIndex!'); return false; }
        return tileInfo;
        }

    // Quick function for getting a given sprite's data (the one with the offset, size, etc.) provided the sprite token
    getSpriteData(spriteToken){
        //console.log('%c' + '~getSpriteData(spriteToken:' + spriteToken + ')', 'color: magenta;');
        if (!spriteToken || typeof spriteToken !== 'string' || !spriteToken.length){ console.error('getSpriteData() missing required spriteToken!'); return false; }
        let _self = this;
        let _config = _self.config;
        let mapSpritesIndex = _config.mapSpritesIndex;
        let spriteInfo = mapSpritesIndex[spriteToken] || false;
        if (!spriteInfo){ console.error('getSpriteData() missing required entry "' + spriteToken + '" in mapSpritesIndex!'); return false; }
        return spriteInfo;
        }

    // Quick function for getting a given portal's data (the one with the offset, size, etc.) provided the portal token
    getPortalData(portalToken){
        //console.log('%c' + '~getPortalData(portalToken:' + portalToken + ')', 'color: magenta;');
        if (!portalToken || typeof portalToken !== 'string' || !portalToken.length){ console.error('getPortalData() missing required portalToken!'); return false; }
        let _self = this;
        let _config = _self.config;
        let mapPortalsIndex = _config.mapPortalsIndex;
        let portalInfo = mapPortalsIndex[portalToken] || false;
        if (!portalInfo){ console.error('getPortalData() missing required entry "' + portalToken + '" in mapPortalsIndex!'); return false; }
        return portalInfo;
        }

    // Quick function for drawing a single tile to a given layer's canvas object given data
    drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileData){
        //console.log('%c' + '~drawTileToCanvas(layerToken:' + layerToken + ', ctx, spriteSheet, tileKey:' + tileKey + ', tileData:' + typeof tileData + ')', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
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
        let gridSpriteData = _self.getSpriteData('grid');
        let hoverSpriteData = _self.getSpriteData('hover');
        let focusSpriteData = _self.getSpriteData('focus');
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
    refreshCanvasTiles(layerToken){
        //console.log('%c' + '~refreshCanvasTiles(layerToken:' + layerToken + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string'){ console.error('refreshCanvasTiles() missing required layerToken!'); return false; }
        let _self = this;
        if (_self.refreshCanvasTiles._scheduled){ return; }
        _self.refreshCanvasTiles._scheduled = true;
        requestAnimationFrame(() => {
            _self.refreshCanvasTiles._scheduled = false;
            _self.refreshCanvasTilesForReal(layerToken);
            });
        return;
        }
    refreshCanvasTilesForReal(layerToken) {
        //console.log('%c' + '~refreshCanvasTilesForReal(layerToken:' + layerToken + ')', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let $thisWorld = _elements.world;
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
            _self.drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileData);
            tileData.dirty = false; // reset the dirty flag
            }
        return true;
        }

    // Quick function for applying a "focus" effect to a given layer tile
    focusLayerTile(layerToken, tilePosition){
        //console.log('%c' + 'focusLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
        if (!thisTileData){ console.error('focusLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
        thisTileData.effects.focus = true;
        thisTileData.dirty = true;
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick function for renmoving a "focus" effect from a given layer tile
    unfocusLayerTile(layerToken, tilePosition){
        //console.log('%c' + 'unfocusLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
        let _self = this;
        let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
        if (!thisTileData){ console.error('focusLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
        thisTileData.effects.focus = false;
        thisTileData.dirty = true;
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick function for applying a "hover" effect to a given layer tile
    hoverLayerTile(layerToken, tilePosition){
        //console.log('%c' + 'hoverLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
        let _self = this;
        let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
        if (!thisTileData){ console.error('hoverLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
        thisTileData.effects.hover = true;
        thisTileData.dirty = true;
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick function for removing a "hover" effect from a given layer tile
    unhoverLayerTile(layerToken, tilePosition){
        //console.log('%c' + 'unhoverLayerTile(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
        let _self = this;
        let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
        if (!thisTileData){ console.error('unhoverLayerTile() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
        thisTileData.effects.hover = false;
        thisTileData.dirty = true;
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick function for binding events to a given layer's canvas object
    bindEventsToCanvas($canvasMap){
        //console.log('%c' + '~bindEventsToCanvas($canvasMap:' + typeof $canvasMap + ')', 'color: magenta;');
        if (!$canvasMap || !$canvasMap.length){ console.error('bindEventsToCanvas() missing required $canvasMap!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _cursor = _world.cursor;
        let layerToken = 'terrain'; // TODO: make this dynamic maybe?
        let $clickOverlay = $('#click-overlay', $canvasMap);
        let focusTimeouts = {}, focusTimeoutDuration = _config.mapEffects.focusTimeout;
        let hoverTimeouts = {}, hoverTimeoutDuration = _config.mapEffects.hoverTimeout, hoverTiles = [];
        let lastMouseClick, lastMouseOver;
        $clickOverlay.bind('click', function(e){
            if (_cursor.moving){ return false; }
            //console.log('%c' + 'Map overlay click event!', 'color: cyan;');
            let oldPos = _cursor.position;
            let thisPos = _self.getTileAtPosition($clickOverlay, e.pageX, e.pageY);
            let tileData = _self.getLayerTileIndexData(layerToken, thisPos);
            let battleSymbols = _config.mapBattleSymbols;
            //console.log('-> checking battleSymbols =', battleSymbols);
            let battleAtPosition = Object.keys(battleSymbols).indexOf(thisPos) !== -1;
            if (thisPos === oldPos || thisPos === lastMouseClick){ return; }
            if (!tileData.walkable || battleAtPosition){ return; }
            //console.log('%c' + 'Mouse click event triggered for position ' + thisPos + '!', 'color: orange;');
            lastMouseClick = thisPos;
            _self.focusLayerTile(layerToken, thisPos);
            if (focusTimeouts[oldPos]){ clearTimeout(focusTimeouts[oldPos]); }
            focusTimeouts[thisPos] = setTimeout(function(){
                _self.moveToPosition(thisPos, function(){
                    _self.unfocusLayerTile(layerToken, oldPos);
                    }, true);
                }, focusTimeoutDuration);
            });
        $clickOverlay.bind('mousemove', function(e){
            if (_cursor.moving){ return false; }
            //console.log('%c' + 'Map overlay mousemove event!', 'color: cyan;');
            let thisPos = _self.getTileAtPosition($clickOverlay, e.pageX, e.pageY);
            let tileData = _self.getLayerTileIndexData(layerToken, thisPos);
            let battleSymbols = _config.mapBattleSymbols;
            //console.log('-> checking battleSymbols =', battleSymbols);
            let battleAtPosition = Object.keys(battleSymbols).indexOf(thisPos) !== -1;
            let showPointer = thisPos !== _cursor.position && tileData.walkable && !battleAtPosition;
            $clickOverlay.css({cursor: showPointer ? 'pointer' : 'default'});
            if (hoverTiles.length){
                for (var i = 0; i < hoverTiles.length; i++){
                    let hoverPos = hoverTiles[i];
                    if (hoverPos === thisPos){ continue; }
                    delete hoverTimeouts[hoverPos];
                    _self.unhoverLayerTile(layerToken, hoverPos);
                    }
                }
            if (thisPos === lastMouseOver){ return; }
            if (!tileData.walkable || battleAtPosition){ return; }
            //console.log('%c' + 'Mouse move event triggered at position ' + thisPos + '!', 'color: orange;');
            lastMouseOver = thisPos;
            _self.hoverLayerTile(layerToken, thisPos);
            hoverTiles.push(thisPos);
            /*
            if (hoverTimeouts[thisPos]){ clearTimeout(hoverTimeouts[thisPos]); }
            hoverTimeouts[thisPos] = setTimeout(function(){
                _self.unhoverLayerTile(layerToken, thisPos);
                }, hoverTimeoutDuration);
            */
            });
        // Return true on success
        return true;
        }

    // Quick function for binding events to the main world object
    bindEventsToWorld($thisWorld){
        //console.log('%c' + '~bindEventsToWorld($thisWorld:' + typeof $thisWorld + ')', 'color: magenta;');
        if (!$thisWorld || !$thisWorld.length){ console.error('bindEventsToWorld() missing required $thisWorld!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        // Bind a click event to the home button in the header that'll bring us to prototype menu
        let $homeButton = _elements.homeButton;
        if ($homeButton && $homeButton.length){
            $homeButton.bind('click', function(e){
                e.preventDefault();
                //console.log('%c' + 'Home button clicked!', 'color: cyan;');
                if (!confirm('Are you sure you want to leave the world map?')){ return; }
                $thisWorld.addClass('hidden');
                let homeMenuURL = $homeButton.attr('data-home-url') || 'prototype.php';
                window.location.href = homeMenuURL;
                return true;
                });
            }
        // Bind a click event to the reset button in the header that'll clear world data to start over (dev/debug only)
        let $resetButton = _elements.resetButton;
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
        let $playerSwitcher = _elements.playerSwitcher;
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
        // Return true on success
        return true;
        }

    // Quick function for moving cursor to a given map position
    moveToPosition(newPosition, onComplete, forceMove, animateMove){
        //console.log('%c' + 'moveToPosition(' + newPosition + ')', 'color: magenta;');
        if (!newPosition || typeof newPosition === 'undefined'){ console.error('newPosition is undefined!'); return false; }
        else if (typeof newPosition !== 'string' || !newPosition.match(/^[0-9]+\-[0-9]+$/)){ console.error('newPosition is invalid!', newPosition); return false; }
        else { newPosition = newPosition.split('-'); }
        forceMove = typeof forceMove === 'boolean' ? forceMove : false;
        animateMove = typeof animateMove === 'boolean' ? animateMove : true;
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $sideButtons = _elements.sideButtons;
        let $actionDropdown = _elements.actionDropdown;
        let $backgroundLayer = $('.layer.background', $canvasMap);
        var $tilesLayer = $('.layer.tiles', $canvasMap);
        let $objectsLayer = $('.layer.objects', $canvasMap);
        let $eventsLayers = $('.layer.events', $canvasMap);
        let $cursorSprite = $('.sprite.cursor', $objectsLayer);
        if (!$tilesLayer || !$tilesLayer.length){ console.error('$tilesLayer does not exist!'); return false; }
        if (!$objectsLayer || !$objectsLayer.length){ console.error('$objectsLayer does not exist!'); return false; }
        if (!$eventsLayers || !$eventsLayers.length){ console.error('$eventsLayers do not exist!'); return false; }
        if (!$cursorSprite || !$cursorSprite.length){ console.error('$cursorSprite not found!'); return false; }
        if (!$actionDropdown || !$actionDropdown.length){ console.error('$actionDropdown not found!'); return false; }
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
        $actionDropdown.removeClass('active');
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
            _self.updateMapPosition();
            _self.saveWorldState();
            _self.focusLayerTile('terrain', _worldCursor.position);
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
        let worldWidth = $thisWorld.outerWidth();
        let worldHeight = $thisWorld.outerHeight();
        let mapWidth = $canvasMap.outerWidth();
        let mapHeight = $canvasMap.outerHeight();
        let targetX = tileOffsetX + (_mapTileSize[0] / 2) - (_mapTileSizeOffset[0] / 2);
        let targetY = tileOffsetY + (_mapTileSize[1] / 2) - (_mapTileSizeOffset[1] / 2);
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
        let subTranslateX = Math.round(-1 * (translateX * 0.9));
        let subTranslateY = Math.round(-1 * (translateY * 0.9));
        // Apply the new translate values to the map container
        $canvasMap.css({ transform: 'translate(' + translateX + 'px, ' + translateY + 'px)' });
        $backgroundLayer.css({ transform: 'translate(' + subTranslateX + 'px, ' + subTranslateY + 'px)' });
        return true;
        }

    // Quick function for running post-update checks and actions after moving the cursor
    async updateMapPosition(){
        //console.log('%c' + 'updateMapPosition()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let _userId = _config.userId;
        let _playerId = _config.playerId;
        let _playerToken = _config.playerToken;
        let _playerRobots = _config.playerRobots;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $worldCursor = _elements.cursor;
        let cursorDirection = _worldCursor.direction;
        let cursorPosition = _worldCursor.position;
        let newPosition = cursorPosition.split('-');
        let thisNewCol = parseInt(newPosition[0]);
        let thisNewRow = parseInt(newPosition[1]);

        // Define the default zoom timeout for after movement ends
        let zoomTimeoutDuration = 2000;

        // First we update the cursor sprite position and attributes
        let $positionDisplay = $('#position-display', $thisWorld);
        let $positionDisplayWrapper = $('> .wrapper', $positionDisplay);
        $positionDisplayWrapper.text('X:' + thisNewCol + ' Y:' + thisNewRow);

        // Make sure we empty and hide the action dropdown if it's been shown by previous move
        let $actionDropdown = _elements.actionDropdown;
        let $actionDropdownWrapper = $('> .wrapper', $actionDropdown);
        $actionDropdown.removeClass('active').css({left: '', top: ''}).removeAttr('data-dir');
        $actionDropdownWrapper.empty();

        // Make sure we also empty the sidebar buttons in case any were added by previous move
        let $sideButtons = _elements.sideButtons;
        let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
        $sideButtons.removeClass('active');
        $sideButtonsWrapper.empty();

        // Collect references to the required event layers and the zoom display layer
        let $eventsLayers = $('.layer.events', $canvasMap);
        let $zoomLayer = $('.layer.zoom', $canvasMap);
        if (!$eventsLayers || !$eventsLayers.length){ console.error('updateMapPosition() missing required $eventsLayers!'); return false; }
        if (!$zoomLayer || !$zoomLayer.length){ console.error('updateMapPosition() missing required $zoomLayer!'); return false; }
        //console.log('-> $eventsLayer found, checking for events...');

        // Make sure we move any existing zoom layer sprites back to their original layers
        $worldCursor.removeClass('busy');
        $eventsLayers.removeClass('has-zoom');
        $('.sprite', $zoomLayer).each(function(){
            let $sprite = $(this), layer = $sprite.attr('data-layer'), $layer = $('.layer[data-layer="'+layer+'"]', $canvasMap);
            $sprite.appendTo($layer).removeAttr('data-layer');
            //console.log('-> moving sprite back to layer', layer, 'from zoom layer');
            });
        setTimeout(function(){ $('.sprite', $eventsLayers).removeClass('zoom'); }, 100);
        //$('.sprite', $zoomLayer).removeClass('zoom');

        // Search for events at the new position so we can show the action dropdown if needed
        //console.log('-> checking if there are any events for this position...');
        let $eventsAtPosition = _self.getEventsAtPosition(newPosition);
        //console.log('-> found ' + $eventsAtPosition.length + ' events at position', '\n--> $eventsAtPosition:', $eventsAtPosition);
        if (!$eventsAtPosition || !$eventsAtPosition.length){
            //console.log('-> no events found at position', cursorPosition, 'skipping dropdown display');
            return;
            }

        // Now that we have an event, check its data to see if we should show a dropdown
        // for either a battle, a portal, or any other compatible event-type for the tile
        var showDropdown = false;
        var showDropdownType = '';
        var dropdownMarkup = '';
        var dropdownButtons = '';
        var autoRedirect = false;
        var autoRedirectURL = '';
        if ($eventsAtPosition[0].is('[data-portal]')){
            // If the cursor is literally on a portal, only one event sprite matters right now
            let $eventAtPosition = $eventsAtPosition[0];
            var dataLabel = $eventAtPosition.attr('data-label');
            var dataPortal = $eventAtPosition.attr('data-portal');
            if (dataPortal && dataPortal.indexOf('goto__') !== -1){
                showDropdown = true;
                if (!dataLabel){ dataLabel = 'Portal Options'; }
                dropdownMarkup += '<strong class="label">' + dataLabel + '</strong>';
                if (dataPortal.indexOf('goto__') !== -1){ dropdownButtons += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span>Warp to Area</span></a>'; }
                else if (dataPortal === 'exit'){ dropdownButtons += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span>Return Home</span></a>'; }
                dropdownButtons += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showDropdownType = 'portal';
                zoomTimeoutDuration = 500; // if we show a portal dropdown, we want to zoom in quickly
                // Automatically redirect to this portal (temp maybe?) TODO: review this in the future
                console.log('-> entering portal with name ' + dataPortal + '!');
                if (dataPortal === 'spawn'){
                    // TODO: make the spawn actually go somewhere specific ?
                    } else if (dataPortal === 'exit'){
                    // TODO: make the exit actually go somewhere specific ?
                    autoRedirect = true;
                    showDropdown = false;
                    autoRedirectURL = 'prototype.php';
                    } else if (dataPortal.indexOf('goto__') !== -1){
                    // Make the portal token a world token for the redirect
                    autoRedirect = true;
                    showDropdown = false;
                    let worldToken = dataPortal.replace(/^goto__/i, '');
                    autoRedirectURL = 'world.php?world=' + worldToken;
                    }
                }
            }
        else {
            // Otherwise we can/should check all the posiitons for any battles to round-up and trigger
            let dataLabels = [], dataBattles = [];
            for (var i = 0; i < $eventsAtPosition.length; i++){
                let $eventAtPosition = $eventsAtPosition[i];
                if ($eventAtPosition.is('[data-portal]')){ continue; } // skip portals, we already handled them above
                let dataPosition = $eventAtPosition.attr('data-pos');
                var dataLabel = $eventAtPosition.attr('data-label');
                var dataBattle = $eventAtPosition.attr('data-battle');
                //console.log('-> checking event sprite', $eventAtPosition, 'for data-battle:', dataBattle);
                if (dataBattle){
                    dataLabels.push([dataLabel, dataPosition]);
                    dataBattles.push(dataBattle);
                    }
                }
            if (dataLabels.length && dataBattles.length){
                showDropdown = true;
                //console.log('-> showing dropdown with battles:', dataBattles);
                let dataBattlesJoined = dataBattles.join(',');
                let dataLabelsJoined = (function(labels){
                    for (var i = 0, markup = []; i < labels.length; i++){
                        let label = labels[i][0], labelPosition = labels[i][1];
                        let positionRelative = _self.getPositionRelative(cursorPosition, labelPosition);
                        let labelText = label;
                        let labelClasses = 'label sublabel';
                        labelClasses += ' ' + (positionRelative[0] === 0 ? 'center' : (positionRelative[0] < 0 ? 'left' : 'right'));
                        labelClasses += ' ' + (positionRelative[1] === 0 ? 'middle' : (positionRelative[1] < 0 ? 'up' : 'down'));
                        markup.push('<strong class="'+labelClasses+'" data-pos="' + labelPosition + '" data-rel="' + positionRelative + '">' + labelText + '</strong>');
                        } return markup;
                    })(dataLabels).join('');
                dropdownMarkup += dataLabelsJoined;
                if (_playerRobots.length){  dropdownButtons += '<a class="button big-button" data-action="start-battle" data-battle="'+dataBattlesJoined+'"><span>Start Battle</span></a>'; }
                else { dropdownButtons += '<a class="button big-button disabled" data-battle="'+dataBattlesJoined+'"><span>Start Battle</span></a>'; }
                dropdownButtons += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showDropdownType = 'battle';
                zoomTimeoutDuration = 1500; // otherwise if this is a battle we wait a moment
                }
            }

        // If there's no dropdown to show, we can return early
        if (!showDropdown && !autoRedirect){ return; }

        // Define an inline function to mark the cursor as busy for dramatic effect
        let markCursorAsBusy = function(){

            // Add the busy class to the cursor so it hides behind the player
            $worldCursor.addClass('busy');

            };

        // Define an inline function to redirect to the portal if needed
        let redirectToLocation = function(){
            console.log('%c' + 'redirectToLocation() called!', 'color: cyan;');
            $thisWorld.addClass('hidden');
            window.location.href = autoRedirectURL;
            return true;
            };

        // Define an inline function to zoom and show the dropdown which we'll call after a timeout
        let zoomAndShowDropdown = function(){

            // Elevate the event sprite(s) to the zoom layer and add a zoom class to it so it's more visible
            let cursorPositionXY = cursorPosition.split('-');
            for (var i = 0; i < $eventsAtPosition.length; i++){
                let $eventSprite = $eventsAtPosition[i];
                let eventPosition = $eventSprite.attr('data-pos');
                //let eventPositionXY = eventPosition.split('-');
                if ($eventSprite.hasClass('tile')){ continue; } // skip tiles
                let $eventLayer = $eventSprite.closest('.layer.events');
                let eventLayer = $eventLayer.attr('data-layer');
                $eventLayer.addClass('has-zoom');
                //let relativePosition = [eventPositionXY[0] - cursorPositionXY[0], eventPositionXY[1] - cursorPositionXY[1]];
                let relativePosition = _self.getPositionRelative(cursorPosition, eventPosition);
                if (relativePosition[0] < 0){ $eventSprite.attr('data-dir', 'right'); }
                else if (relativePosition[0] > 0){ $eventSprite.attr('data-dir', 'left'); }
                else if (cursorDirection.indexOf('right') !== -1){ $eventSprite.attr('data-dir', 'left'); }
                else if (cursorDirection.indexOf('left') !== -1){ $eventSprite.attr('data-dir', 'right'); }
                $eventSprite.appendTo($zoomLayer);
                $eventSprite.attr('data-layer', eventLayer);
                //console.log('-> moving event sprite to zoom layer', eventLayer, 'from events layer');
                setTimeout(function(){ $eventSprite.addClass('zoom'); }, 100);
                }

            // Move the action dropdown to the correct position, add the markup, and show it
            $actionDropdown.css({
                left: ((thisNewCol - 1) * _mapTileSize[0] + _mapTileSizeOffset[0]) + 'px',
                top: ((thisNewRow - 1) * _mapTileSize[1] + _mapTileSizeOffset[1]) + 'px',
                }).attr('data-dir', _worldCursor.direction).attr('data-type', showDropdownType).attr('data-align', 'center');
            $actionDropdownWrapper.html(dropdownMarkup); // dropdownButtons

            // Add the buttons to the sidebar area so that they are out-of-the-way
            $sideButtonsWrapper.html(dropdownButtons);

            // Define the event to fun when clicking one of these new action buttons
            let onActionButtonClick = function(e){
                //console.log('%c' + 'Action button clicked!', 'color: cyan;');
                e.preventDefault();
                let $button = $(this);
                let action = $button.attr('data-action') || false;
                let isBattle = action.indexOf('battle') !== -1;
                let isPortal = action.indexOf('portal') !== -1;
                let isDismiss = action === 'dismiss';
                //console.log('-> action =', action);
                if (isBattle){
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
                else if (isPortal){
                    let portalName = $button.attr('data-portal') || false;
                    //console.log('-> portalName =', portalName);
                    if (action === 'portal-info'){
                        //console.log('-> showing portal info for ID ' + portalName + '!');
                        alert('Portal Name: ' + portalName + '\n\nThis is where you would show portal details.');
                        }
                    else if (action === 'enter-portal'){
                        //console.log('-> entering portal with name ' + portalName + '!');
                        let portalHref = false;
                        if (portalName === 'spawn'){
                            portalHref = 'prototype.php'; // TODO: make the spawn actually go somewhere specific
                            } else if (portalName === 'exit'){
                            portalHref = 'prototype.php'; // TOPO: make the exit actually go somewhere specific
                            } else if (portalName.indexOf('goto__') !== -1){
                            let worldToken = portalName.replace(/^goto__/i, '');
                            portalHref = 'world.php?world=' + worldToken;
                            }
                        if (portalHref){
                            $thisWorld.addClass('hidden');
                            window.location.href = portalHref;
                            }
                        }
                    }
                else if (isDismiss){
                    //console.log('-> dismissing action dropdown!');
                    $actionDropdown.removeClass('active');
                    $actionDropdownWrapper.empty();
                    $sideButtons.removeClass('active');
                    $sideButtonsWrapper.empty();
                    $worldCursor.removeClass('busy');
                    $eventsLayers.removeClass('has-zoom');
                    $('.sprite.zoom', $eventsLayers).removeClass('zoom');
                    $('.sprite', $zoomLayer).each(function(){
                        let $sprite = $(this), layer = $sprite.attr('data-layer'), $layer = $('.layer[data-layer="'+layer+'"]', $canvasMap);
                        $sprite.appendTo($layer).removeAttr('data-layer').removeClass('zoom');
                        });
                    }
                else {
                    // no compatible action found, do nothing
                    return false;
                    }
                };

            // Bind click events to the newly created action buttons in the dropdown
            //$('.button[data-action]', $actionDropdown).bind('click', onActionButtonClick);
            $('.button[data-action]', $sideButtons).bind('click', onActionButtonClick);

            // Wait a moment for visual flow and then show the dropdown (adjusting alignment as needed)
            setTimeout(function(){
                // Add the active class so it shows and has a box model
                $actionDropdown.addClass('active');
                $sideButtons.addClass('active');
                // Collect parent and child sizes and offsets to determine alignment
                let dropdownParentSize = [_config.worldWidth, _config.worldHeight];
                let dropdownOffset = $actionDropdown.offset();
                let dropdownOffsets = [dropdownOffset.left, dropdownOffset.top];
                let dropdownPositionDelta = [];
                dropdownPositionDelta.push(1 - (dropdownParentSize[0] - dropdownOffsets[0]) / dropdownParentSize[0]);
                dropdownPositionDelta.push(1 - (dropdownParentSize[1] - dropdownOffsets[1]) / dropdownParentSize[1]);
                let dropdownAlign = dropdownPositionDelta[1] >= 0.5 ? 'above' : 'below';
                if (dropdownPositionDelta[0] <= 0.25){ dropdownAlign += '-right'; }
                if (dropdownPositionDelta[0] >= 0.75){ dropdownAlign += '-left'; }
                // Apply the alignment to the dropdown
                $actionDropdown.attr('data-align', dropdownAlign);
                }, 200);

            };

        // First we mark the cursor as busy so it trembles a bit before the encounter
        let _selfRef = _self.updateMapPosition;
        if (_selfRef.zoomCursorTimeout){ clearTimeout(_selfRef.zoomCursorTimeout); }
        _selfRef.zoomCursorTimeout = setTimeout(markCursorAsBusy, Math.ceil(zoomTimeoutDuration / 2));

        // If a redirect was requested, this is where we exit actually
        if (autoRedirect){
            if (_selfRef.zoomRedirectTimeout){ clearTimeout(_selfRef.zoomRedirectTimeout); }
            _selfRef.zoomRedirectTimeout = setTimeout(redirectToLocation, zoomTimeoutDuration);
            return true;
            }

        // Otherwise we can actually trigger the dropdown and zoom in on the events
        if (showDropdown){
            if (_selfRef.zoomDropdownTimeout){ clearTimeout(_selfRef.zoomDropdownTimeout); }
            _selfRef.zoomDropdownTimeout = setTimeout(zoomAndShowDropdown, zoomTimeoutDuration);
            return true;
            }

        // Return true on success
        return true;
        }

    // Quick function that, given a column and row returns any events on or around that position on the map
    getEventsAtPosition(searchPosition, searchRadius){
        //console.log('%c' + 'getEventsAtPosition(searchPosition:' + searchPosition + ', searchRadius:' + searchRadius + ')', 'color: magenta;');
        if (!searchPosition || (typeof searchPosition !== 'string' && !Array.isArray(searchPosition))){ console.error('getEventsAtPosition() missing or invalid searchPosition!'); return false; }
        searchPosition = typeof searchPosition !== 'string' ? searchPosition.join('-') : searchPosition; // join if provided as array
        searchRadius = typeof searchRadius === 'number' ? searchRadius : 1; // default to one if not provided
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let layerTilesIndex = _world.layerTilesIndex;
        let mapBattleSymbols = _config.mapBattleSymbols;
        let $canvasMap = _elements.map;
        let $eventLayers = $('.layer.events', $canvasMap);
        let $eventsAtPosition = [];
        let positionsToCheck = [];
        positionsToCheck.push(searchPosition); // always check the exact position first
        // If a search radius is provided, add the surrounding positions to check
        // including diagonal positions
        if (searchRadius > 0){
            let searchCol = parseInt(searchPosition.split('-')[0]);
            let searchRow = parseInt(searchPosition.split('-')[1]);
            for (let colOffset = -searchRadius; colOffset <= searchRadius; colOffset++){
                for (let rowOffset = -searchRadius; rowOffset <= searchRadius; rowOffset++){
                    if (colOffset === 0 && rowOffset === 0){ continue; } // skip the center position
                    let newCol = searchCol + colOffset;
                    let newRow = searchRow + rowOffset;
                    if (newCol < 1 || newRow < 1){ continue; } // skip invalid positions
                    positionsToCheck.push(newCol + '-' + newRow);
                    }
                }
            }
        //console.log('-> positionsToCheck =', positionsToCheck);
        for (let i = 0; i < positionsToCheck.length; i++){
            let checkPosition = positionsToCheck[i];
            let eventPosition = checkPosition.split('-');
            let $eventAtPosition = $('.sprite[data-col="' + eventPosition[0] + '"][data-row="' + eventPosition[1] + '"]', $eventLayers);
            if (!$eventAtPosition || !$eventAtPosition.length){ continue; }
            let eventIsPortal = $eventAtPosition.is('[data-portal]');
            if (eventIsPortal && checkPosition !== searchPosition){ continue; } // skip portals unless it's the exact position
            $eventsAtPosition.push($eventAtPosition);
            if (eventIsPortal){ break; }
            }
        // Return the found events
        return $eventsAtPosition;
        }

    // Quick function for sending a snapshot of persistent world values back to the server for saving
    saveWorldState(){
        //console.log('%c' + 'saveWorldState()', 'color: magenta;');
        let _self = this;
        if (_self.saveWorldState._scheduled){ return; }
        _self.saveWorldState._scheduled = true;
        setTimeout(function(){
            _self.saveWorldState._scheduled = false;
            _self.saveWorldStateForReal();
            }, 1000);
        return;
        }
    saveWorldStateForReal(){
        //console.log('%c' + 'saveWorldStateForReal()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
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
        return;
        }

    // Quick function for intentionally waiting for a given amount of time (in milliseconds)
    wait(ms){
        return new Promise(resolve => setTimeout(resolve, ms));
        }

}

// Create the document ready events
$(document).ready(function(){
    //console.log('%c' + 'World map canvas ready!', 'color: green;');
    let $mmrpg = $('#mmrpg');
    if ($mmrpg.length){
        //console.log('%c' + 'Creating new mmrpgWorldMap object...', 'color: green;');
        let worldMapObject = new mmrpgWorldMap($mmrpg);
        gameSettings.worldMapObject = worldMapObject;
        window.worldMapObject = worldMapObject;
        }
});