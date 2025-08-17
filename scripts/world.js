
// Define global variables
let $thisPrototype = false;
let $thisWorld = false;
let $thisCanvas = false;

// Expand the game settings object with a variable world specific data
gameSettings.worldElements = {
    mmrpg: null,
    world: null,
    canvas: null,
    map: null,
    layers: null,
    cursor: null,
    };
gameSettings.worldConfig = {
    userId: 0,
    playerId: 0,
    playerToken: 'player',
    playerRobots: ['0_robot'],
    playerRobotsIndex: {},
    playerMobility: 1, // default only
    mapWorld: 'undefined',
    mapToken: 'undefined',
    mapName: 'Undefined Map',
    mapImage: 'undefined.png',
    mapSize: [10, 10],
    mapTileSize: [80, 80],
    mapTileSizeOffset: [0, 0],
    mapSpriteSize: [80, 80],
    mapSpriteSizeOffset: [0, 0],
    mapCols: 10, // default only
    mapRows: 10, // default only
    mapWidth: 400, // 10 tiles * 40px per tile, will be dynamic later
    mapHeight: 400, // 10 tiles * 40px per tile, will be dynamic later
    mapEffects: {
        activeTimeout: 600, // milliseconds
        focusTimeout: 600, // milliseconds
        hoverTimeout: 600, // milliseconds
        moveTimeout: 300, // milliseconds
        moveTravel: 100, // milliseconds
        usePerspective: false, // make it easy to toggle this during dev
        },
    mapTilesIndex: {},
    mapGroupsIndex: {},
    mapSpritesIndex: {},
    mapEventSymbols: {},
    mapEventsIndex: {},
    mapPortalSymbols: {},
    mapPortalsIndex: {},
    mapButtonSymbols: {},
    mapButtonsIndex: {},
    mapSwitchSymbols: {},
    mapSwitchesIndex: {},
    mapBattleSymbols: {},
    mapBattlesIndex: {},
    mapRivalSymbols: {},
    mapRivalsIndex: {},
    windowWidth: 1024, // default only
    widthHeight: 768, // default only
    mmrpgWidth: 800, // default only
    mmrpgHeight: 600, // default only
    worldWidth: 800, // default only
    worldHeight: 600, // default only
    canvasWidth: 800, // default only
    canvasHeight: 600, // default only
    backButtonURL: '#', // populated on init
    homeButtonURL: '#', // populated on init
    resetButtonURL: '#', // populated on init
    allowWorldEvents: false, // default until user interaction
    robotStorageSlotsVisible: 8, // probably wont change as it's what fits
    };
gameSettings.worldState = {
    cursor: {
        position: '0-0',
        positionXY: [0, 0],
        direction: '',
        moving: false,
        moved: false,
        busy: false,
        col: 0,
        row: 0,
        },
    player: {
        token: 'player',
        position: '0-0',
        direction: '',
        robots: {},
        },
    buttons: {},
    switches: {},
    layersIndex: {},
    layerTilesIndex: {},
    baseMapTileKeys: [], // base array of tile keys that are part of the map
    walkableMapTileKeys: [], // array of tile keys that are specifically walkable
    zoomLevel: 1.0, // default zoom level,
    userZoomLevel: 1.0, // current zoom level set by user
    mapIsHidden: false, // map is full visible by default
    allowHovers: true, // allow hover effects on tiles
    allowClicks: true, // allow click events on tiles
    };
gameSettings.worldHasLoaded = false;

// Create the mmrpgWorldMap class object for this mode
class mmrpgWorldMap {

    // Constructor function for the world map
    constructor($mmrpg){
        //console.log('%c' + 'mmrpgWorldMap() constructor', 'color: green;');
        let _self = this;
        _self.config = gameSettings.worldConfig;
        _self.elements = gameSettings.worldElements;
        _self.state = gameSettings.worldState;
        _self.initWorld($mmrpg);
        }

    // Quick function for checking if the world is "already busy" doing something (either explicitly or by some action like moving)
    worldIsBusy(){
        //console.log('%c' + 'mmrpgWorldMap.worldIsBusy()', 'color: green;');
        let _self = this;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        return _worldCursor.busy || _worldCursor.moving;
        }

    // Quick function for checking if the world map specifically is busy doing something (either busy because world, or because hidden)
    worldMapIsHidden(){
        //console.log('%c' + 'mmrpgWorldMap.worldMapIsHidden()', 'color: green;');
        let _self = this;
        let _world = _self.state;
        return _world.mapIsHidden;
        }

    // Quick function to initialize world map variables
    initWorld($mmrpg){
        //console.log('%c' + 'mmrpgWorldMap.initWorld()', 'color: green;');
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let $thisPrototype = $mmrpg;
        let $thisWorld = $('#world', $thisPrototype);
        let $thisCanvas = $('#canvas', $thisWorld);
        let $canvasMap = $('#map', $thisCanvas);
        let $mapLayers = $('.layer[data-layer]', $canvasMap);
        let $backButton = $('#back-button', $thisWorld);
        let $homeButton = $('#home-button', $thisWorld);
        let $resetButton = $('#reset-button', $thisWorld);
        let $positionDisplay = $('#position-display', $thisWorld);
        let $playerSwitcher = $('#player-switcher', $thisWorld);
        let $robotsOverview = $('#robots-overview', $thisWorld);
        let $sideButtons = $('#side-buttons', $thisWorld);
        let $actionDropdown = $('#action-dropdown', $thisWorld);
        let $clickOverlay = $('#click-overlay', $thisWorld);
        let $worldCursor = $('.sprite[data-sprite="team-cursor"]', $canvasMap);
        let $teamSprites = $('.sprite[data-sprite^="team-"]', $canvasMap);
        _elements.mmrpg = $thisPrototype;
        _elements.world = $thisWorld;
        _elements.canvas = $thisCanvas;
        _elements.map = $canvasMap;
        _elements.layers = $mapLayers;
        _elements.backButton = $backButton;
        _elements.homeButton = $homeButton;
        _elements.resetButton = $resetButton;
        _elements.positionDisplay = $positionDisplay;
        _elements.playerSwitcher = $playerSwitcher;
        _elements.robotsOverview = $robotsOverview;
        _elements.sideButtons = $sideButtons;
        _elements.actionDropdown = $actionDropdown;
        _elements.clickOverlay = $clickOverlay;
        _elements.worldCursor = $worldCursor;
        _elements.teamSprites = $teamSprites;
        if ($canvasMap.length && $mapLayers.length){
            //console.log('%c' + 'World map canvas found with ' + $mapLayers.length + ' layers...', 'color: orange;');
            // Initialize the world map with the provided canvas and layers
            _self.initWorldMap($canvasMap, $mapLayers, function(){
                //console.log('%c' + 'initWorldMap() complete!', 'color: green;');
                });
            }
        return true;
        }

    // Quick function for parsing the canvas data in a map layer
    initWorldMap($canvasMap, $mapLayers, onComplete){
        //console.log('%c' + 'mmrpgWorldMap.initWorldMap($canvasMap:' + typeof $canvasMap + ', $mapLayers:' + typeof $mapLayers + ')', 'color: magenta;');
        if (!$canvasMap || !$canvasMap.length){ console.error('initWorldMap() missing required $canvasMap!'); return false; }
        if (!$mapLayers || !$mapLayers.length){ console.error('initWorldMap() missing required $mapLayers!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let $thisPrototype = _elements.mmrpg;
        let $thisWorld = _elements.world;
        // Collect the main json object for this world map and then parse it into the appropriate config values for the game
        let $mapJson = $('script[data-json]', $canvasMap).first(), mapJson = $mapJson.html(), mapData = mapJson ? JSON.parse(mapJson) : false;
        if (!mapData || typeof mapData !== 'object' || !Object.keys(mapData).length){ console.error('initWorldMap() unable to parse mapData!'); return false; }
        let mapWorld = mapData.map_world || false;
        let mapToken = mapData.map_token || false;
        let mapName = mapData.map_name || false;
        let mapImage = mapData.map_image || false;
        let mapSize = mapData.map_size || false;
        let tileSize = mapData.tile_size || false;
        let tilesIndex = mapData.tiles_index || false;
        let groupsIndex = mapData.groups_index || false;
        let spritesIndex = mapData.sprites_index || false;
        //let portalsIndex = mapData.portals_index || {};
        let startPosition = mapData.start_position || false;
        let startDirection = mapData.start_direction || false;
        if (!mapToken || !mapImage || !mapSize || !tileSize){ console.error('initWorldMap() missing required properties!', {mapToken, mapImage, mapSize, tileSize}); return false; }
        if (!tilesIndex  || !spritesIndex){ console.error('initWorldMap() missing required indexes!', {tilesIndex, spritesIndex}); return false; }
        if (!Array.isArray(mapSize) || mapSize.length < 2){ console.error('initWorldMap() mapSize must be an array of at least two values!'); return false; }
        if (!Array.isArray(tileSize) || tileSize.length < 2){ console.error('initWorldMap() tileSize must be an array of at least two values!'); return false; }
        let defaultMapName = mapToken.replace(/-/g, ' ').replace(/ AREA /g, ' Area ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
        let defaultMapSize = [_config.mapSize[0], _config.mapSize[1]];
        let defaultMapTileSize = [_config.mapTileSize[0], _config.mapTileSize[1]];
        _config.mapWorld = mapWorld;
        _config.mapToken = mapToken;
        _config.mapName = mapName || defaultMapName;
        _config.mapImage = mapImage;
        _config.mapSize = [parseInt(mapSize[0]), parseInt(mapSize[1])];
        _config.mapTileSize = [parseInt(tileSize[0]), parseInt(tileSize[1])];
        _config.mapTileSizeOffset = [0, 0]; // default values
        _config.mapSpriteSize = [40, 40]; // hard-coded from mmrpg-defaults
        _config.mapSpriteSizeOffset = [0, 0]; // default values
        _config.mapCols = _config.mapSize[0];
        _config.mapRows = _config.mapSize[1];
        _config.mapWidth = _config.mapSize[0] * _config.mapTileSize[0];
        _config.mapHeight = _config.mapSize[1] * _config.mapTileSize[1];
        if (_config.mapTileSize[0] > defaultMapTileSize[0]){ _config.mapTileSizeOffset[0] = Math.floor((_config.mapTileSize[0] - defaultMapTileSize[0]) / 2); }
        if (_config.mapTileSize[1] > defaultMapTileSize[1]){ _config.mapTileSizeOffset[1] = Math.floor((_config.mapTileSize[1] - defaultMapTileSize[1]) / 2); }
        if (_config.mapSpriteSize[0] > _config.mapTileSize[0]){ _config.mapSpriteSizeOffset[0] = Math.floor((_config.mapSpriteSize[0] - _config.mapTileSize[0]) / 2); }
        else if (_config.mapSpriteSize[0] < _config.mapTileSize[0]){ _config.mapSpriteSizeOffset[0] = Math.ceil((_config.mapTileSize[0] - _config.mapSpriteSize[0]) / 2); }
        if (_config.mapSpriteSize[1] > _config.mapTileSize[1]){ _config.mapSpriteSizeOffset[1] = Math.floor((_config.mapSpriteSize[1] - _config.mapTileSize[1]) / 2); }
        else if (_config.mapSpriteSize[1] < _config.mapTileSize[1]){ _config.mapSpriteSizeOffset[1] = Math.ceil((_config.mapTileSize[1] - _config.mapSpriteSize[1]) / 2); }
        _config.mapTilesIndex = tilesIndex || {};
        _config.mapGroupsIndex = groupsIndex || {};
        _config.mapSpritesIndex = spritesIndex || {};
        _config.mapStartPosition = startPosition;
        _config.mapStartDirection = startDirection;
        _config.windowWidth = $(window).width();
        _config.windowHeight = $(window).height();
        _config.mmrpgWidth = _elements.mmrpg.outerWidth();
        _config.mmrpgHeight = _elements.mmrpg.outerHeight();
        _config.worldWidth = _elements.world.outerWidth();
        _config.worldHeight = _elements.world.outerHeight();
        _config.canvasWidth = _elements.canvas.outerWidth();
        _config.canvasHeight = _elements.canvas.outerHeight();
        if (startPosition){ _worldCursor.position = startPosition; }
        if (startDirection){ _worldCursor.direction = startDirection; }
        _worldPlayer.token = _config.playerToken || 'player';
        _worldPlayer.position = _worldCursor.position || '0-0';
        _worldPlayer.direction = _worldCursor.direction || 'down-right';
        // If player robots were defined in the predefined index, copy them over to the state
        let _playerRobots = _config.playerRobots;
        let _playerRobotsIndex = _config.playerRobotsIndex;
        if (_playerRobots.length
            && Object.keys(_playerRobotsIndex).length){
            //console.log('---> initWorldMap() found ' + _playerRobots.length + ' player robots to initialize!');
            let livePlayerRobots = {};
            for (var i = 0; i < _playerRobots.length; i++){
                let robotString = _playerRobots[i];
                let robotInfo = _playerRobotsIndex[robotString] || false;
                if (!robotInfo || typeof robotInfo !== 'object' || !Object.keys(robotInfo).length){
                    //console.warn('initWorldMap() missing robotInfo for robotString:', robotString);
                    continue;
                    }
                //console.log('---> adding robot #' + i + ' w/ robotString = ' + robotString);
                let liveRobotInfo = JSON.parse(JSON.stringify(robotInfo)); // clone the info object
                livePlayerRobots[robotString] = liveRobotInfo;
                }
            _worldPlayer.robots = livePlayerRobots;
            //console.log('---> initWorldMap() livePlayerRobots =', livePlayerRobots);
            }
        // Define the function to run when everything is done loading
        let onWorldLoaded = function(){
            _self.bindEventsToCanvas($canvasMap);
            _self.bindEventsToWorld($thisWorld);
            _self.calculateWalkableMapTiles();
            let startPosition = '1-1';
            let startDirection = 'down-right';
            if (_config.mapStartPosition){ startPosition = _config.mapStartPosition; }
            else if (portalsIndex['spawn']){ startPosition = portalsIndex['spawn'].join('-'); }
            if (_config.mapStartDirection){ startDirection = _config.mapStartDirection; }
            let fakeOldPosition = startPosition.split('-').map(function(val){ return parseInt(val.trim()); });
            if (startDirection.indexOf('right') !== -1){ fakeOldPosition[0] = fakeOldPosition[0] - 1; }
            else if (startDirection.indexOf('left') !== -1){ fakeOldPosition[0] = fakeOldPosition[0] + 1; }
            if (startDirection.indexOf('down') !== -1){ fakeOldPosition[1] = fakeOldPosition[1] - 1; }
            else if (startDirection.indexOf('up') !== -1){ fakeOldPosition[1] = fakeOldPosition[1] + 1; }
            _config.allowWorldEvents = true;
            _self.playSoundEffect('teleport-in');
            _self.moveToPosition(startPosition, null, true, false, fakeOldPosition);
            setTimeout(function(){
                $thisWorld.removeClass('hidden');
                $thisWorld.addClass('ready');
                $canvasMap.addClass('ready');
                _self.startIdleAnimation();
                }, 100);
            };
        // Define the function for run when each layer is done being rendered
        let layersPending = $mapLayers.length;
        let reduceLayersPending = function(){
            layersPending--;
            if (!layersPending){ return onLayersLoaded(); }
            else { return true; }
            };
        let onLayersLoaded = function(){
            let $terrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
            if ($terrainLayer.length){
                let dropdownRequired = !_elements.actionDropdown || !_elements.actionDropdown.length;
                let overlayRequired = !_elements.clickOverlay || !_elements.clickOverlay.length;
                if (dropdownRequired){
                    let $actionDropdown = $('<div id="action-dropdown"><div class="wrapper"></div></div>');
                    $terrainLayer.append($actionDropdown);
                    _elements.actionDropdown = $actionDropdown;
                    }
                if (overlayRequired){
                    let $clickOverlay = $('<div id="click-overlay"><div class="wrapper"></div></div>');
                    $terrainLayer.append($clickOverlay);
                    _elements.clickOverlay = $clickOverlay;
                    }
                }
            return onWorldLoaded();
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
            // If there are any blocks of json inside this layer, parse them now
            if ($jsonScripts.length > 0){
                //console.log('---> found ' + $jsonScripts.length + ' [data-json] scripts in layer #' + index + '!');
                $jsonScripts.each(function(index){
                    //console.log('---> processing script[data-json] #' + index + '...');
                    let $thisJson = $(this);
                    let jsonKind = $thisJson.attr('data-json'), jsonData = $thisJson.html(), jsonObject = jsonData ? JSON.parse(jsonData) : false;
                    if (!jsonData || !jsonData.length){ console.warn('---> JSON data for layer ' + layerToken + ' (script[data-json="'+jsonKind+'"]) was empty!'); return true; }
                    if (!jsonObject || typeof jsonObject !== 'object'){ console.error('---> unable to parse JSON data for layer ' + layerToken + ' (script[data-json="'+jsonKind+'"])!', '\n---> jsonData was', jsonData); return true; }
                    //console.log('---> parsed json layer data for ' + layerToken + ' (script[data-json="'+jsonKind+'"]) !!! jsonObject =', jsonObject);
                    let configName = 'map' + jsonKind[0].toUpperCase() + jsonKind.slice(1);
                    //console.log('---> setting _config.' + configName + ' =', jsonObject);
                    _config[configName] = jsonObject;
                    return true;
                    });
                }
            // If this is a terrain layer, we need to initialize the canvas and draw the tiles
            if (layerToken === 'terrain'){
                _self.initMapLayerCanvas($thisLayer, onLayerReady);
                return true;
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
        //console.log('%c' + 'mmrpgWorldMap.initMapLayerCanvas($thisLayer:' + typeof $thisLayer + ')', 'color: magenta;');
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
            _self.drawTilesToCanvas(layerToken);
            if (typeof onComplete === 'function'){ onComplete(); }
            return true;
            };
        spriteSheet.onerror = function(){ return onLoadError(); }
        spriteSheet.onload = function(){ return onLoadSuccess(); };
        //console.log('%c' + '---> loading sprite sheet image from ' + mapImage, 'color: cyan;');
        _self.indexCanvasTileData(layerToken, spriteSheet, canvasTiles);
        spriteSheet.src = mapImage;
        return true;
        }

    // Quick function for drawing tiles to a given canvas object
    indexCanvasTileData(layerToken, spriteSheet, canvasTiles){
        //console.log('%c' + 'mmrpgWorldMap.indexCanvasTileData(layerToken:' + layerToken + ', spriteSheet:' + typeof spriteSheet + ', canvasTiles:' + typeof canvasTiles + ')', 'color: magenta;');
        if (!layerToken || !spriteSheet || !canvasTiles){ console.error('indexCanvasTileData() missing required parameters!', {layerToken, spriteSheet, canvasTiles}); return false; }
        if (typeof canvasTiles !== 'object' || !Object.keys(canvasTiles).length){ console.error('indexCanvasTileData() required canvasTiles missing or malformed!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let tileSize = _config.mapTileSize;
        let tilesIndex = _config.mapTilesIndex;
        let tileDataKeys = Object.keys(canvasTiles);
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
            let tileSpriteKey = tileKey;
            let tileSpriteToken = tileValue;
            let tileSpriteInfo = tilesIndex[tileSpriteToken];
            if (!tileSpriteInfo){ console.error('indexCanvasTileData() missing tileSpriteInfo for tileKey:', tileKey, 'and tileValue:', tileValue); continue; }
            let tileSpriteOffset = [tileSpriteInfo[0] || 0, tileSpriteInfo[1] || 0];
            let tileSpriteSize = [tileSpriteInfo[2] || tileSize[0], tileSpriteInfo[3] || tileSize[1]];
            let tileSpritePosition = [tilePos[0], tilePos[1], ((tilePos[0] - 1) * tileSpriteSize[0]), ((tilePos[1] - 1) * tileSpriteSize[1])];
            let tileSpriteEffects = {grid: true, hover: false, outline: false, focus: false, active: false}; // default values
            let tileIsVoid = tileSpriteToken === 'void' || tileSpriteToken.indexOf('void') !== -1 ? true : false;
            let tileIsWater = tileSpriteToken === 'water' || tileSpriteToken.indexOf('water') !== -1 ? true : false;
            let tileSpriteWalkable = true;
            if (tileIsVoid){ tileSpriteEffects.grid = false; tileSpriteWalkable = false; } // no grid or walk for void tiles
            if (tileIsWater){ tileSpriteWalkable = false; } // no walk for water tiles
            //console.log('---> tileSpriteKey =', tileSpriteKey);
            //console.log('---> tileSpriteToken =', tileSpriteToken);
            //console.log('---> tileSpriteInfo =', tileSpriteInfo);
            let tilesIndexData = typeof thisLayerTiles[tileKey] !== 'undefined' ? thisLayerTiles[tileKey] : {};
            tilesIndexData.position = tileSpritePosition;
            tilesIndexData.effects = tileSpriteEffects;
            tilesIndexData.sprite = [tileSpriteKey, tileSpriteToken, tileSpriteOffset, tileSpriteSize];
            tilesIndexData.walkable = tileSpriteWalkable;
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
        //console.log('%c' + 'mmrpgWorldMap.drawTilesToCanvas(layerToken:' + layerToken + ')', 'color: magenta;');
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

    // Quick function for drawing a single tile to a given layer's canvas object given data
    drawTileToCanvas(layerToken, ctx, spriteSheet, tileKey, tileData){
        //console.log('%c' + 'mmrpgWorldMap.drawTileToCanvas(layerToken:' + layerToken + ', ctx, spriteSheet, tileKey:' + tileKey + ', tileData:' + typeof tileData + ')', 'color: magenta;');
        let _self = this;
        let _selfRef = _self.drawTileToCanvas;
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
        // define some internal methods we can use for effect-drawingoptimization
        let _saveDrawRestore = function(callback){
            ctx.save(); callback.call(this); ctx.restore();
            };
        let _drawSpriteFromData = function(spriteData, alpha){ //, composite
            if (!spriteData){ return; }
            _saveDrawRestore(function(){
                ctx.globalAlpha = typeof alpha === 'number' ? alpha : 1.0;
                ctx.globalCompositeOperation = 'source-atop';
                //ctx.globalCompositeOperation = typeof composite === 'string' ? composite : 'source-atop';
                //ctx.globalCompositeOperation = 'source-atop'; // TEMP TEMP TEMP
                //console.log('-> drawing sprite[' + layerToken + '/' + tileKey + '] w/', '\n-> globalAlpha = ', ctx.globalAlpha, '\n-> globalCompositeOperation =', ctx.globalCompositeOperation);
                ctx.drawImage(spriteSheet,
                    spriteData[0], spriteData[1], // source offset
                    tileSpriteSize[0], tileSpriteSize[1], // source size
                    tilePosition[2], tilePosition[3], // destination offset
                    tileSpriteSize[0], tileSpriteSize[1] // destination size
                    );
                });
            };
        let drawSpriteFromData = function(spriteData, alpha){ //, composite
            return _drawSpriteFromData(spriteData, alpha); //, composite
            };
        // check if the tile is focused or hovered and apply the effects
        let tileHasGrid = tileEffects.grid;
        let tileIsHovered = tileEffects.hover;
        let tileIsOutlined = tileEffects.outline;
        let tileIsFocused = tileEffects.focus;
        let tileIsActive = tileEffects.active;
        // check if this tile falls into any oft-used categories
        let tileIsVoid = tileSpriteToken === 'void' || tileSpriteToken.indexOf('void') !== -1 ? true : false;
        let tileIsGrass = tileSpriteToken === 'grass' || tileSpriteToken.indexOf('grass') !== -1 ? true : false;
        let tileIsWater = tileSpriteToken === 'water' || tileSpriteToken.indexOf('water') !== -1 ? true : false;
        // clear a rect at the exact position and no larger
        ctx.clearRect(tilePosition[2], tilePosition[3], tileSpriteSize[0], tileSpriteSize[1]);
        // void tiles have no sprite, so we skip drawing them
        if (tileIsVoid){ return false; }
        //console.log('---> tile[' + layerToken + '/' + tileKey + '/' + tileSpriteToken + '] tileData =', JSON.stringify(tileData));
        //console.log('---> tile[' + layerToken + '/' + tileKey + ']::tileIsVoid =', tileIsVoid);
        // sprite: draw the main tile sprite at the correct position
        if (tileIsWater){ ctx.globalAlpha = 0.7; }
        ctx.drawImage(spriteSheet,
            tileSpriteOffset[0], tileSpriteOffset[1], // source offset
            tileSpriteSize[0], tileSpriteSize[1], // source size
            tilePosition[2], tilePosition[3], // destination offset
            tileSpriteSize[0], tileSpriteSize[1] // destination size
            );
        ctx.globalAlpha = 1.0;
        // gradient-overlay: draw a slice of the gradient overlay on top of this tile for aesthetic purposes
        var gradBuf = _self.getOverlayGradientBuffer(layerToken, ctx);
        _self.applyOverlayToRect(ctx, gradBuf,
            tilePosition[2], tilePosition[3],
            tileSpriteSize[0], tileSpriteSize[1],
            'overlay'
            );
        // grid/hover/outline/focus/active: collect sprite data for known effects
        // then we draw any overlay images as defined in the effects on top
        let gridSpriteData = _self.getSpriteData('grid');
        let hoverSpriteData = _self.getSpriteData('hover');
        let outlineSpriteData = _self.getSpriteData('outline');
        let focusSpriteData = _self.getSpriteData('focus');
        let activeSpriteData = _self.getSpriteData('active');
        if (!gridSpriteData){ console.warn('drawTileToCanvas() unable to find grid sprite data for layer ' + layerToken + '!'); }
        if (!hoverSpriteData){ console.warn('drawTileToCanvas() unable to find hover sprite data for layer ' + layerToken + '!'); }
        if (!outlineSpriteData){ console.warn('drawTileToCanvas() unable to find outline sprite data for layer ' + layerToken + '!'); }
        if (!focusSpriteData){ console.warn('drawTileToCanvas() unable to find focus sprite data for layer ' + layerToken + '!'); }
        if (!activeSpriteData){ console.warn('drawTileToCanvas() unable to find active sprite data for layer ' + layerToken + '!'); }
        // [GRID]: draw another image at the same positon but w/ the grid sprite
        if (tileHasGrid && gridSpriteData){
            var gridSpriteAlpha = 0.3;
            if (tileIsGrass){ gridSpriteAlpha += 0.2; }
            drawSpriteFromData(gridSpriteData, gridSpriteAlpha);
            }
        // [HOVER]: draw another image at the same positon but w/ the border sprite
        if (tileIsHovered && hoverSpriteData){
            var hoverSpriteAlpha = 0.3;
            if (tileIsGrass){ hoverSpriteAlpha += 0.2; }
            if (tileIsFocused){ hoverSpriteAlpha += 0.5; }
            drawSpriteFromData(hoverSpriteData, hoverSpriteAlpha);
            }
        // [OUTLINE]: draw another image at the same positon but w/ the border sprite
        if (tileIsOutlined && outlineSpriteData){
            drawSpriteFromData(outlineSpriteData);
            }
        // [FOCUS]: draw another image at the same positon but w/ the border sprite
        if (tileIsFocused && focusSpriteData){
            var focusSpriteAlpha = 0.25;
            if (tileIsGrass){ focusSpriteAlpha += 0.10; }
            if (tileIsHovered){ focusSpriteAlpha += 0.25; }
            drawSpriteFromData(focusSpriteData, focusSpriteAlpha);
            if (outlineSpriteData){
                var outlineSpriteAlpha = 1.00;
                if (tileIsWater){ outlineSpriteAlpha -= 0.30; }
                drawSpriteFromData(outlineSpriteData);
                }
            }
        // [ACTIVE]: draw another image at the same positon but w/ the border sprite
        if (tileIsActive && activeSpriteData){
            drawSpriteFromData(activeSpriteData);
            }
        // Return true on success
        return true;
        }

    // === GRADIENT OVERLAY HELPERS ===
    // Define a quick function for getting an offscreen canvas
    _getOffscreen(w, h){
        if ('OffscreenCanvas' in window) {
            const c = new OffscreenCanvas(w, h);
            return c;
            }
        const c = document.createElement('canvas');
        c.width = w; c.height = h;
        return c;
        }
    // Define a function for making the gradient used in the overlay
    _makeOverlayGradient(bctx, w, h){
        const g = bctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(0, 0, 0, 0.1)');
        g.addColorStop(1, 'rgba(0, 0, 0, 0.3)');
        return g;
        }
    // Build (or reuse) the full-canvas gradient buffer for this layer.
    // Cache is invalidated automatically if canvas size changes.
    getOverlayGradientBuffer(layerToken, ctx){
        let _self = this;
        let _selfRef = self;
        _selfRef._overlayCache = _selfRef._overlayCache || { gradients: {}, sliceBuf: null };
        const cache = _selfRef._overlayCache.gradients;
        const key = layerToken;
        const cw = ctx.canvas.width, ch = ctx.canvas.height;
        let entry = cache[key];
        if (!entry || entry.w !== cw || entry.h !== ch){
            const buf = _self._getOffscreen(cw, ch);
            const bctx = buf.getContext('2d');
            bctx.clearRect(0, 0, cw, ch);
            const g = _self._makeOverlayGradient(bctx, cw, ch);
            bctx.fillStyle = g;
            bctx.fillRect(0, 0, cw, ch);
            entry = cache[key] = { buf, w: cw, h: ch };
            }
        return entry.buf;
        }
    // Apply the overlay to a single rect without stacking.
    // Slices the gradient buffer, masks with current scene alpha in that rect, then blends back.
    applyOverlayToRect(ctx, gradBuf, x, y, w, h, blend){
        let _self = this;
        let _selfRef = self;
        _selfRef._overlayCache = _selfRef._overlayCache || { gradients: {}, sliceBuf: null };
        let slice = _selfRef._overlayCache.sliceBuf;
        if (!slice){ slice = _selfRef._overlayCache.sliceBuf = _self._getOffscreen(w, h); }
        // Resize if needed (clears content)
        if (slice.width !== w || slice.height !== h){ slice.width = w; slice.height = h; }
        const sctx = slice.getContext('2d');
        sctx.globalCompositeOperation = 'source-over';
        sctx.clearRect(0, 0, w, h);
        // 1) draw the relevant gradient slice
        sctx.drawImage(gradBuf, x, y, w, h, 0, 0, w, h);
        // 2) keep only where pixels exist in the scene
        sctx.globalCompositeOperation = 'destination-in';
        sctx.drawImage(ctx.canvas, x, y, w, h, 0, 0, w, h);
        // 3) blend it back
        ctx.save();
        ctx.globalAlpha = 0.8;
        ctx.globalCompositeOperation = blend || 'multiply';
        ctx.drawImage(slice, x, y);
        ctx.globalAlpha = 1.0;
        ctx.restore();
        }
    // === END GRADIENT OVERLAY HELPERS ===

    // Quick function for generating the currently walkable map tiles
    getWalkableMapTiles(){
        //console.log('%c' + 'mmrpgWorldMap.getWalkableMapTiles()', 'color: magenta;');
        let _self = this;
        let _world = _self.state;
        if (!_world.walkableMapTileKeys){ _self.calculateWalkableMapTiles(); }
        return _world.walkableMapTileKeys;
        }

    // Quick function for generating the currently walkable map tiles within range of a target position
    getWalkableMapTilesByProximity(targetPosition, filterRange){
        //console.log('%c' + 'mmrpgWorldMap.getWalkableMapTilesByProximity(targetPosition:' + targetPosition + ', filterRange:' + filterRange + ')', 'color: magenta;');
        let _self = this;
        let walkableTiles = _self.getWalkableMapTiles();
        if (!walkableTiles || !Array.isArray(walkableTiles) || !walkableTiles.length){ console.error('getWalkableMapTilesByProximity() missing required walkable tiles!'); return false; }
        if (!targetPosition || typeof targetPosition !== 'string' || !targetPosition.length){ console.error('getWalkableMapTilesByProximity() missing required targetPosition!'); return false; }
        filterRange = typeof filterRange === 'number' && filterRange > 0 ? parseInt(filterRange) : 1;
        return _self.filterTilesByProximity(walkableTiles, targetPosition, filterRange);
        }

    // Quick function to calculate all the walkable tile positions for this map given
    // its base properties (size, terrain) and current conditions (player, enemy placement)
    calculateWalkableMapTiles(refresh, exclude){
        //console.log('%c' + 'mmrpgWorldMap.calculateWalkableMapTiles()', 'color: magenta;');

        // Collect the parameters and set defaults
        refresh = typeof refresh === 'boolean' ? refresh : false;
        exclude = typeof exclude === 'object' ? exclude : {};
        exclude.terrain = typeof exclude.terrain === 'boolean' ? exclude.terrain : true;
        exclude.obstacles = typeof exclude.obstacles === 'boolean' ? exclude.obstacles : true;
        exclude.portals = typeof exclude.portals === 'boolean' ? exclude.portals : true;
        exclude.buttons = typeof exclude.buttons === 'boolean' ? exclude.buttons : true;
        exclude.switches = typeof exclude.switches === 'boolean' ? exclude.switches : true;
        exclude.battles = typeof exclude.battles === 'boolean' ? exclude.battles : true;
        exclude.players = typeof exclude.players === 'boolean' ? exclude.players : true;
        exclude.rivals = typeof exclude.rivals === 'boolean' ? exclude.rivals : true;
        exclude.cursor = typeof exclude.cursor === 'boolean' ? exclude.cursor : true;

        // Collect refs to class variables
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;

        // If refresh was not requested and walkable tiles already exists, return that array now
        let walkableMapTiles = _world.walkableMapTileKeys || [];
        if (walkableMapTiles.length && !refresh){ return true; }
        if (!walkableMapTiles.length || refresh){ walkableMapTiles = []; }
        //console.log('-> walkableMapTiles (start) =', walkableMapTiles);

        // Otherwise collect the map properties and indexes so we can generate the tiles
        let mapCols = _config.mapCols;
        let mapRows = _config.mapRows;
        let mapTileSize = _config.mapTileSize;
        let mapTilesIndex = _config.mapTilesIndex;
        let layerTilesIndex = _world.layerTilesIndex;
        let layerTilesKeys = Object.keys(layerTilesIndex);
        //console.log('---> mapCols =', mapCols);
        //console.log('---> mapRows =', mapRows);
        //console.log('---> mapTileSize =', mapTileSize);
        //console.log('---> mapTilesIndex =', mapTilesIndex);
        //console.log('---> layerTilesIndex =', layerTilesIndex);
        //console.log('---> layerTilesKeys =', layerTilesKeys);

        // Collect references to other indexes we'll need to review tile properties
        let portalsIndex = _config.mapPortalsIndex;
        let battlesIndex = _config.mapBattlesIndex;
        let battleSymbols = _config.mapBattleSymbols;
        let rivalSymbols = _config.mapRivalSymbols;
        let portalSymbols = _config.mapPortalSymbols;
        let buttonSymbols = _config.mapButtonSymbols;
        let switchSymbols = _config.mapSwitchSymbols;
        //console.log('---> portalsIndex =', portalsIndex);
        //console.log('---> battlesIndex =', battlesIndex);
        //console.log('---> battleSymbols =', battleSymbols);
        //console.log('---> rivalSymbols =', rivalSymbols);
        //console.log('---> portalSymbols =', portalSymbols);
        //console.log('---> buttonSymbols =', buttonSymbols);
        //console.log('---> switchSymbols =', switchSymbols);

        // Loop through each column and row to generate the base tile positions
        //console.log('collecting the base map tiles ...');
        let baseMapTiles = _world.baseMapTileKeys || [];
        if (!baseMapTiles.length){
            //console.log('... baseMapTiles is empty, generating now');
            for (let col = 1; col <= mapCols; col++){
                for (let row = 1; row <= mapRows; row++){
                    let tileKey = col + '-' + row;
                    baseMapTiles.push(tileKey);
                    }
                }
            //console.log('---> baseMapTiles (generated) =', baseMapTiles);
            _world.baseMapTileKeys = baseMapTiles;
            }

        // To start, populate the walkableMapTiles array with all the base map tiles
        //console.log('assigning the base map tiles to walkableMapTiles ...');
        walkableMapTiles = Object.values(baseMapTiles);
        //console.log('-> walkableMapTiles (base) =', walkableMapTiles);

        // Now filter out the ones that have their walkable flag set to false on the terrain layer
        //console.log('filtering walkableMapTiles by terrain layer ...');
        let terrainTilesIndex = layerTilesIndex['terrain'];
        if (exclude.terrain && terrainTilesIndex){
            //console.log('... checking terrainTilesIndex =', terrainTilesIndex);
            let allowedTerrain = [];
            for (let i = 0; i < walkableMapTiles.length; i++){
                let tileKey = walkableMapTiles[i];
                let tileData = terrainTilesIndex[tileKey] || false;
                //console.log('---> checking tileKey:', tileKey, 'w/ tileData:', tileData);
                if (!tileData || !tileData.walkable){ continue; }
                allowedTerrain.push(tileKey);
                }
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                return allowedTerrain.includes(tileKey);
                }));
            //console.log('--> walkableMapTiles (post-terrain) =', walkableMapTiles);
            }

        /*
        // If we are to exclude any players, make sure we remove those positions too (???)
        //console.log('filtering walkableMapTiles by player position ...');
        let playerPosition = _config.mapStartPosition || (portalsIndex['spawn'] ? portalsIndex['spawn'].join('-') : '') || '1-1';
        if (exclude.players && playerPosition){
            //console.log('---> checking playerPosition =', playerPosition);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                return tileKey !== playerPosition;
                }));
            //console.log('---> walkableMapTiles (post-player) =', walkableMapTiles);
            }
        */

        // If we are to exclude the battles, make sure we remove those positions
        // battleSymbols is object like { "1-1":"battle-token-foo","2-2":"battle-token-bar", [...] }
        let battleSymbolKeys = Object.keys(battleSymbols);
        if (exclude.battles && battleSymbols){
            //console.log('---> checking battleSymbolKeys =', battleSymbolKeys);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                return !battleSymbolKeys.includes(tileKey);
                }));
            //console.log('---> walkableMapTiles (post-battles) =', walkableMapTiles);
            }

        // If we are to exclude the rivals, make sure we remove those positions
        // rivalSymbols is object like { "1-1":"rival-token-foo","2-2":"rival-token-bar", [...] }
        let rivalSymbolKeys = Object.keys(rivalSymbols);
        if (exclude.rivals && rivalSymbols){
            //console.log('---> checking rivalSymbolKeys =', rivalSymbolKeys);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                return !rivalSymbolKeys.includes(tileKey);
                }));
            //console.log('---> walkableMapTiles (post-rivals) =', walkableMapTiles);
            }

        // If we are to exclude portals, make sure we remove those positions (only when locked though)
        let battlePortalKeys = Object.keys(portalSymbols);
        if (exclude.portals && portalSymbols){
            //console.log('---> checking portalSymbolKeys =', battlePortalKeys);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                //console.log('---> checking tileKey:', tileKey, 'against portalSymbolKeys:', battlePortalKeys);
                if (battlePortalKeys.includes(tileKey)){
                    //console.log('---> tileKey:', tileKey, 'is a portal, checking if locked...');
                    let portalInfo = portalsIndex[portalSymbols[tileKey]] || false;
                    //console.log('---> portalInfo =', portalInfo);
                    if (portalInfo.locked){
                        //console.log('---> portal at ' + tileKey + ' is locked, removing from walkableMapTiles');
                        return false; // remove this tile
                        } else {
                        //console.log('---> portal at ' + tileKey + ' is open, keeping in walkableMapTiles');
                        }
                    }
                return true; // keep this tile
                }));
            //console.log('---> walkableMapTiles (post-portals) =', walkableMapTiles);
            }

        // If we are to exclude buttons, make sure we remove those positions
        let battleButtonKeys = Object.keys(buttonSymbols);
        if (exclude.buttons && buttonSymbols){
            //console.log('---> checking battleButtonKeys =', battleButtonKeys);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                //console.log('---> checking tileKey:', tileKey, 'against buttonSymbols:', battleButtonKeys);
                if (battleButtonKeys.includes(tileKey)){
                    //console.log('---> tileKey:', tileKey, 'is a button, removing from walkableMapTiles');
                    return false; // remove this tile
                    } else {
                    //console.log('---> tileKey:', tileKey, 'is not a button, keeping in walkableMapTiles');
                    }
                return true; // keep this tile
                }));
            //console.log('---> walkableMapTiles (post-buttons) =', walkableMapTiles);
            }

        // If we are to exclude the cursor, make sure we remove that position too
        //console.log('filtering walkableMapTiles by cursor position ...');
        let cursorPosition = _world.cursor.position;
        if (exclude.cursor && cursorPosition){
            //console.log('---> checking cursorPosition =', cursorPosition);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                return tileKey !== cursorPosition;
                }));
            //console.log('---> walkableMapTiles (post-cursor) =', walkableMapTiles);
            }

        // Assign the walkableMapTiles array to the world state
        _world.walkableMapTileKeys = walkableMapTiles;

        // Return true on success
        return true;

        }

    // Quick function for narrowing down a list of walkable map tiles to only those that
    // fall within the given range of the supplied target position
    filterTilesByProximity(walkableTiles, targetPosition, filterRange){
        //console.log('%c' + 'mmrpgWorldMap.filterTilesByProximity(walkableTiles:' + typeof walkableTiles + ', targetPosition:' + targetPosition + ', filterRange:' + filterRange + ')', 'color: magenta;');
        if (!Array.isArray(walkableTiles) || !walkableTiles.length){ console.error('filterTilesByProximity() missing required walkableTiles!'); return false; }
        if (!targetPosition || typeof targetPosition !== 'string' || !targetPosition.length){ console.error('filterTilesByProximity() missing required targetPosition!'); return false; }
        filterRange = typeof filterRange === 'number' && filterRange > 0 ? parseInt(filterRange) : 1;
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let targetPositionXY = targetPosition.split('-');
        targetPositionXY = targetPositionXY.map(function(val){ return parseInt(val.trim()); });
        let filteredTiles = [];
        for (let i = 0; i < walkableTiles.length; i++){
            let tilePosition = walkableTiles[i].split('-');
            tilePosition = tilePosition.map(function(val){ return parseInt(val.trim()); });
            let positionRelative = _self.getPositionRelative(targetPositionXY, tilePosition);
            if (Math.abs(positionRelative[0]) <= filterRange && Math.abs(positionRelative[1]) <= filterRange){
                filteredTiles.push(walkableTiles[i]);
                }
            }
        //console.log('---> filteredTiles =', filteredTiles);
        // Otherwise return the filtered tiles
        return filteredTiles;
        }

    // Quick function for calculating the column and row of a tile at a given pixel position
    getTileAtPosition($overlay, xPos, yPos, applyOffset){
        //console.log('%c' + 'mmrpgWorldMap.getTileAtPosition(' + xPos + ', ' + yPos + ')', 'color: magenta;');
        applyOffset = typeof applyOffset === 'boolean' ? applyOffset : true;
        xPos = xPos > 0 ? parseInt(xPos) : 0, yPos = yPos > 0 ? parseInt(yPos) : 0;
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let size = _config.mapTileSize;
        let sizeX = size[0], sizeY = size[1];
        let zoom = _world.zoomLevel;
        let width = $overlay.width(), height = $overlay.height(), offset = $overlay.offset();
        if (applyOffset){ xPos -= offset.left; yPos -= offset.top; }
        if (xPos < 0){ xPos = 0; } if (yPos < 0){ yPos = 0; }
        let thisCol = Math.floor(xPos / sizeX) + 1;
        let thisRow = Math.floor(yPos / sizeY) + 1;
        let thisPos = thisCol + '-' + thisRow;
        return thisPos;
        }

    // Quick function for calculating the relative difference between two positions
    getPositionRelative(position1, position2){
        //console.log('%c' + 'mmrpgWorldMap.getPositionRelative(position1:' + position1 + ', position2:' + position2 + ')', 'color: magenta;');
        if (typeof position1 !== 'string' && !Array.isArray(position1)){ console.error('getPositionRelative() missing required position1!'); return false; }
        if (typeof position2 !== 'string' && !Array.isArray(position2)){ console.error('getPositionRelative() missing required position2!'); return false; }
        position1 = typeof position1 === 'string' ? position1.split('-') : position1;
        position2 = typeof position2 === 'string' ? position2.split('-') : position2;
        let positionRelative = [position2[0] - position1[0], position2[1] - position1[1]];
        return positionRelative;
        }

    // Quick function for getting a given layer tile's index data provided the layer token and tile key
    getLayerTileIndexData(layerToken, tileKey){
        //console.log('%c' + 'mmrpgWorldMap.getLayerTileIndexData(layerToken:' + layerToken + ', tileKey:' + tileKey + ')', 'color: magenta;');
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
        //console.log('%c' + 'mmrpgWorldMap.getLayerTileSpriteData(layerToken:' + layerToken + ', tileToken:' + tileToken + ')', 'color: magenta;');
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
        //console.log('%c' + 'mmrpgWorldMap.getTileData(tileToken:' + tileToken + ')', 'color: magenta;');
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
        //console.log('%c' + 'mmrpgWorldMap.getSpriteData(spriteToken:' + spriteToken + ')', 'color: magenta;');
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
        //console.log('%c' + 'mmrpgWorldMap.getPortalData(portalToken:' + portalToken + ')', 'color: magenta;');
        if (!portalToken || typeof portalToken !== 'string' || !portalToken.length){ console.error('getPortalData() missing required portalToken!'); return false; }
        let _self = this;
        let _config = _self.config;
        let mapPortalsIndex = _config.mapPortalsIndex;
        let portalInfo = mapPortalsIndex[portalToken] || false;
        if (!portalInfo){ console.error('getPortalData() missing required entry "' + portalToken + '" in mapPortalsIndex!'); return false; }
        return portalInfo;
        }

    // Quick functions for updating any canvas map layer tiles that have changed properties
    refreshCanvasTiles(layerToken){
        //console.log('%c' + 'mmrpgWorldMap.refreshCanvasTiles(layerToken:' + layerToken + ')', 'color: magenta;');
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
        //console.log('%c' + 'mmrpgWorldMap.refreshCanvasTilesForReal(layerToken:' + layerToken + ')', 'color: magenta;');
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
            thisLayerTiles[tileKey] = tileData; // reassign the tile data
            }
        layerTilesIndex[layerToken] = thisLayerTiles; // reassign the layer tiles index
        return true;
        }

    // Quick function for applying a given effect to a given layer tile
    applyLayerTileEffect(layerToken, tilePosition, effectName){
        //console.log('%c' + 'mmrpgWorldMap.applyLayerTileEffect(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ', effectName:' + effectName + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
        if (!effectName || typeof effectName !== 'string' || !effectName.length){ return false; }
        let _self = this;
        let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
        if (!thisTileData){ console.error('applyLayerTileEffect() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
        thisTileData.effects[effectName] = true;
        thisTileData.dirty = true;
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick function for removing a given effect from a given layer tile
    removeLayerTileEffect(layerToken, tilePosition, effectName){
        //console.log('%c' + 'mmrpgWorldMap.removeLayerTileEffect(layerToken:' + layerToken + ', tilePosition:' + tilePosition + ', effectName:' + effectName + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!tilePosition || typeof tilePosition !== 'string' || !tilePosition.length){ return false; }
        if (!effectName || typeof effectName !== 'string' || !effectName.length){ return false; }
        let _self = this;
        let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
        if (!thisTileData){ console.error('removeLayerTileEffect() unable to find tile data for layer ' + layerToken + ' and position ' + tilePosition + '!'); return false; }
        thisTileData.effects[effectName] = false;
        thisTileData.dirty = true;
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick function for bulk-removing a given effect for any layer tiles that have it
    bulkRemoveLayerTileEffect(layerToken, effectName){
        //console.log('%c' + 'mmrpgWorldMap.bulkRemoveLayerTileEffect(layerToken:' + layerToken + ', effectName:' + effectName + ')', 'color: magenta;');
        if (!layerToken || typeof layerToken !== 'string' || !layerToken.length){ return false; }
        if (!effectName || typeof effectName !== 'string' || !effectName.length){ return false; }
        let _self = this;
        let _world = _self.state;
        let layerTilesIndex = _world.layerTilesIndex;
        let thisLayerTiles = layerTilesIndex[layerToken] || false;
        if (!thisLayerTiles || typeof thisLayerTiles !== 'object'){ console.error('bulkRemoveLayerTileEffect() missing required _world.layerTilesIndex[' + layerToken + ']!'); return false; }
        for (let tilePosition in thisLayerTiles){
            //let thisTileData = thisLayerTiles[tilePosition];
            let thisTileData = _self.getLayerTileIndexData(layerToken, tilePosition);
            if (thisTileData.effects[effectName]){
                thisTileData.effects[effectName] = false;
                thisTileData.dirty = true;
                }
            }
        _self.refreshCanvasTiles(layerToken);
        return true;
        }

    // Quick alias functions for applying and removing tile effects as per above
    hoverLayerTile(tilePosition){ return this.applyLayerTileEffect('terrain', tilePosition, 'hover'); }
    unhoverLayerTile(tilePosition){ return this.removeLayerTileEffect('terrain', tilePosition, 'hover'); }
    unhoverLayerTiles(){ return this.bulkRemoveLayerTileEffect('terrain', 'hover'); }
    focusLayerTile(tilePosition){ return this.applyLayerTileEffect('terrain', tilePosition, 'focus'); }
    unfocusLayerTile(tilePosition){ return this.removeLayerTileEffect('terrain', tilePosition, 'focus'); }
    unfocusLayerTiles(){ return this.bulkRemoveLayerTileEffect('terrain', 'focus'); }
    outlineLayerTile(tilePosition){ return this.applyLayerTileEffect('terrain', tilePosition, 'outline'); }
    unoutlineLayerTile(tilePosition){ return this.removeLayerTileEffect('terrain', tilePosition, 'outline'); }
    unoutlineLayerTiles(){ return this.bulkRemoveLayerTileEffect('terrain', 'outline'); }
    makeLayerTileActive(tilePosition){ return this.applyLayerTileEffect('terrain', tilePosition, 'active'); }
    makeLayerTileInactive(tilePosition){ return this.removeLayerTileEffect('terrain', tilePosition, 'active'); }
    makeLayerTilesInactive(){ return this.bulkRemoveLayerTileEffect('terrain', 'active'); }

    // Quick function for binding events to a given layer's canvas object
    bindEventsToCanvas($canvasMap){
        //console.log('%c' + 'mmrpgWorldMap.bindEventsToCanvas($canvasMap:' + typeof $canvasMap + ')', 'color: magenta;');
        if (!$canvasMap || !$canvasMap.length){ console.error('bindEventsToCanvas() missing required $canvasMap!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let layerToken = 'terrain'; // TODO: make this dynamic maybe?
        let playerMobility = _config.playerMobility || 1;
        let activeTimeouts = {}, activeTimeoutDuration = _config.mapEffects.activeTimeout;
        let focusTimeouts = {}, focusTimeoutDuration = _config.mapEffects.focusTimeout;
        let hoverTimeouts = {}, hoverTimeoutDuration = _config.mapEffects.hoverTimeout, hoverTiles = [];
        let lastMouseClick, lastMouseOver;
        let $clickOverlay = _elements.clickOverlay;
        $clickOverlay.bind('click', function(e){
            e.preventDefault();
            if (_self.worldMapIsHidden()){ return false; }
            if (_self.worldIsBusy()){ return false; }
            if (!_world.allowClicks){ return false; }
            //console.log('%c' + 'Map overlay click event!', 'color: cyan;');
            //console.log('-> w/ e =', e);
            let oldPos = _worldCursor.position, curPos = oldPos;
            let thisPos = _self.getTileAtPosition($clickOverlay, e.offsetX, e.offsetY, false);
            let sameAsLast = thisPos === lastMouseClick;
            let sameAsCurrent = thisPos === oldPos;
            let tileData = _self.getLayerTileIndexData(layerToken, thisPos);
            let walkableTiles = _self.getWalkableMapTiles();
            let tilesWithinRange = playerMobility > 0 ? _self.getWalkableMapTilesByProximity(curPos, playerMobility) : walkableTiles;
            let tileIsWalkable = walkableTiles.indexOf(thisPos) !== -1 ? true : false;
            let tileIsWithinRange = tilesWithinRange.indexOf(thisPos) !== -1 ? true : false;
            lastMouseClick = thisPos;
            if (!tileIsWithinRange || sameAsLast || sameAsCurrent){ return false; }
            //console.log('%c' + 'Mouse click event triggered for position ' + thisPos + '!', 'color: orange;');
            if (!sameAsLast){ _self.playSoundEffect('link-click'); }
            _self.makeLayerTileActive(thisPos);
            if (activeTimeouts[oldPos]){ clearTimeout(activeTimeouts[oldPos]); }
            activeTimeouts[thisPos] = setTimeout(function(){
                _self.moveToPosition(thisPos, function(){
                    _self.makeLayerTileInactive(oldPos);
                    }, true);
                }, activeTimeoutDuration);
            });
        $clickOverlay.bind('mousemove', function(e){
            e.preventDefault();
            if (_self.worldMapIsHidden()){ return false; }
            if (!_world.allowHovers){ return false; }
            //if (_self.worldIsBusy()){ return false; }
            //console.log('%c' + 'Map overlay mousemove event!', 'color: cyan;');
            //console.log('-> w/ e =', e);
            let curPos = _worldCursor.position;
            let thisPos = _self.getTileAtPosition($clickOverlay, e.offsetX, e.offsetY, false);
            let thisPosXY = thisPos.split('-');
            let sameAsLast = thisPos === lastMouseOver;
            if (sameAsLast){ return; }
            if (thisPosXY[0] < 1 || thisPosXY[0] > _config.mapCols){ return; }
            if (thisPosXY[1] < 1 || thisPosXY[1] > _config.mapRows){ return; }
            $clickOverlay.attr('title', ('X' + thisPosXY[0] + '-Y' + thisPosXY[1]));
            let tileData = _self.getLayerTileIndexData(layerToken, thisPos);
            let walkableTiles = _self.getWalkableMapTiles();
            let tilesWithinRange = playerMobility > 0 ? _self.getWalkableMapTilesByProximity(curPos, playerMobility) : walkableTiles;
            //console.log('-> walkableTiles:', walkableTiles);
            //console.log('-> tilesWithinRange:', tilesWithinRange);
            let tileIsWalkable = walkableTiles.indexOf(thisPos) !== -1 ? true : false;
            let tileIsWithinRange = tilesWithinRange.indexOf(thisPos) !== -1 ? true : false;
            //console.log('-> tileIsWalkable:', tileIsWalkable);
            //console.log('-> tileIsWithinRange:', tileIsWithinRange);
            let showPointer = thisPos !== curPos && tileIsWithinRange ? true : false;
            $clickOverlay.css({cursor: showPointer ? 'pointer' : 'default'});
            if (hoverTiles.length){
                for (var i = 0; i < hoverTiles.length; i++){
                    let hoverPos = hoverTiles[i];
                    if (hoverPos === thisPos){ continue; }
                    delete hoverTimeouts[hoverPos];
                    _self.unhoverLayerTile(hoverPos);
                    }
                hoverTiles = [];
                }
            lastMouseOver = thisPos;
            if (!tileIsWalkable){ return; }
            _self.hoverLayerTile(thisPos);
            hoverTiles.push(thisPos);
            if (!sameAsLast){  _self.playSoundEffect('icon-hover'); }
            });
        $clickOverlay.addClass('active');
        // Return true on success
        return true;
        }

    // Quick function for binding events to the main world object
    bindEventsToWorld($thisWorld){
        //console.log('%c' + 'mmrpgWorldMap.bindEventsToWorld($thisWorld:' + typeof $thisWorld + ')', 'color: magenta;');
        if (!$thisWorld || !$thisWorld.length){ console.error('bindEventsToWorld() missing required $thisWorld!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldPlayerRobots = _worldPlayer.robots;
        let $sideButtons = _elements.sideButtons;
        let $actionDropdown = _elements.actionDropdown;
        // Bind a click event to the back button in the header that'll bring us to prototype menu
        let $backButton = _elements.backButton;
        if ($backButton && $backButton.length){
            $backButton.bind('click', function(e){
                e.preventDefault();
                //console.log('%c' + 'Back button clicked!', 'color: cyan;');
                //if (!confirm('Are you sure you want to leave the world map?')){ return; }
                _self.playSoundEffect('bounce-sound');
                let backButtonURL = $backButton.attr('data-url') || _config.backButtonURL;
                _self.decZoomLevel();
                _self.saveWorldState(function(){
                    _self.decZoomLevel(0.5);
                    window.location.href = backButtonURL;
                    });
                $thisWorld.animate({opacity: 0}, 900, function(){
                    $thisWorld.addClass('hidden').addClass('busy');
                    });
                return true;
                });
            $backButton.bind('mouseenter', function(e){
                //e.preventDefault();
                //console.log('%c' + 'Back button hovered!', 'color: cyan;');
                _self.playSoundEffect('icon-hover');
                return true;
                });
            }
        // Bind a click event to the home button in the header that'll bring us to prototype menu
        let $homeButton = _elements.homeButton;
        if ($homeButton && $homeButton.length){
            $homeButton.bind('click', function(e){
                e.preventDefault();
                //console.log('%c' + 'Home button clicked!', 'color: cyan;');
                //if (!confirm('Are you sure you want to return to the home area?')){ return; }
                _self.playSoundEffect('bounce-sound');
                let homeButtonURL = $homeButton.attr('data-url') || _config.homeButtonURL;
                _self.decZoomLevel();
                _self.saveWorldState(function(){
                    _self.decZoomLevel();
                    window.location.href = homeButtonURL;
                    });
                $thisWorld.animate({opacity: 0}, 600, function(){
                    $thisWorld.addClass('hidden').addClass('busy');
                    });
                return true;
                });
            $homeButton.bind('mouseenter', function(e){
                //e.preventDefault();
                //console.log('%c' + 'Home button hovered!', 'color: cyan;');
                _self.playSoundEffect('icon-hover');
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
                _self.playSoundEffect('destroyed-sound');
                _self.loadMusicTrack('current-track', true);
                let resetButtonURL = $resetButton.attr('data-url') || _config.resetButtonURL;
                _self.decZoomLevel(0.5);
                _self.saveWorldState(function(){
                    _self.decZoomLevel(1.0);
                    window.location.href = resetButtonURL;
                    });
                $thisWorld.animate({opacity: 0}, 1200, function(){
                    $thisWorld.addClass('hidden').addClass('busy');
                    });
                return true;
                });
            $resetButton.bind('mouseenter', function(e){
                e.preventDefault();
                //console.log('%c' + 'Reset button hovered!', 'color: cyan;');
                _self.playSoundEffect('icon-hover');
                });
            }
        // Bind click events to the player switcher options in the world map header
        let $playerSwitcher = _elements.playerSwitcher;
        if ($playerSwitcher && $playerSwitcher.length){
            $('.team-player[data-player]', $playerSwitcher).bind('click', function(e){
                //console.log('%c' + 'Player switcher clicked for ' + playerToken + '!', 'color: cyan;');
                e.preventDefault();
                if (_self.worldIsBusy()){ return false; }
                $('.team-player', $playerSwitcher).removeClass('active');
                let $option = $(this);
                let playerToken = $option.attr('data-player') || false;
                $option.addClass('active');
                _self.playSoundEffect('switch-in');
                $thisWorld.addClass('loading');
                let worldReloadURL = 'world.php?player=' + playerToken;
                _self.incZoomLevel();
                _self.saveWorldState(function(){
                    _self.incZoomLevel();
                    $thisWorld.addClass('hidden').removeClass('busy');
                    window.location.href = worldReloadURL;
                    _self.incZoomLevel();
                    });
                return true;
                });
            $('.team-player[data-player]', $playerSwitcher).bind('mouseenter', function(e){
                //console.log('%c' + 'Player switcher hovered!', 'color: cyan;');
                e.preventDefault();
                let $option = $(this);
                $('.sprite.player > .sprite', $option).attr('data-frame', '01'); // taunt
                _self.playSoundEffect('icon-hover');
                });
            $('.team-player[data-player]', $playerSwitcher).bind('mouseleave', function(e){
                //console.log('%c' + 'Player switcher mouseleave!', 'color: cyan;');
                e.preventDefault();
                let $option = $(this);
                $('.sprite.player > .sprite', $option).attr('data-frame', '00'); // base
                });
            }
        // Check to make sure the robotsOverview exists, and then bind events to its elements
        let $robotsOverview = _elements.robotsOverview;
        if ($robotsOverview && $robotsOverview.length){
            let $teamSprites = _elements.teamSprites;
            let $robotsOnMap = $teamSprites.filter('.robot:not(.cursor)');
            let $teamRobotsDiv = $('.team-robots', $robotsOverview);
            let $storageRobotsDiv = $('.storage-robots', $robotsOverview);
            let $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
            let $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
            let listOfRobotsInOverview = $teamRobotsInOverview.map(function(){ return $(this).attr('data-robot'); }).get();
            //console.log('-> $teamRobotsInOverview = ', $teamRobotsInOverview.length, $teamRobotsInOverview);
            //console.log('-> $storageRobotsInOverview = ', $storageRobotsInOverview.length, $storageRobotsInOverview);
            //console.log('-> listOfRobotsInOverview = ', listOfRobotsInOverview);
            let storageSlotsVisible = _config.robotStorageSlotsVisible;
            let storageRobotsWaiting = $storageRobotsInOverview.length;
            let numStoragePagesRequired = Math.ceil(storageRobotsWaiting / storageSlotsVisible);
            let currentStoragePageNum = 0;
            //console.log('-> storageSlotsVisible = ', storageSlotsVisible);
            //console.log('-> storageRobotsWaiting = ', storageRobotsWaiting);
            //console.log('-> numStoragePagesRequired = ', numStoragePagesRequired);
            //console.log('-> currentStoragePageNum = ', currentStoragePageNum);
            // Bind a click event to the team-rotate button in the robots overview
            let $rotateButton = $('.team-rotate', $robotsOverview);
            if ($rotateButton && $rotateButton.length){
                $rotateButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return false; }
                    if (_self.worldMapIsHidden()){ return false; }
                    //console.log('%c' + 'Team rotate button clicked!', 'color: cyan;');
                    // First we rotate the actual robot data in the world state by one position (if allowed)
                    let playerRobotKeys = Object.keys(_worldPlayerRobots);
                    //console.log('-> playerRobotKeys =', playerRobotKeys);
                    if (playerRobotKeys.length < 2){ return; } // nothing to rotate
                    //console.log('-> _worldPlayerRobots(keys)(before) =', playerRobotKeys);
                    _self.playSoundEffect('switch-in');
                    let firstRobotKey = playerRobotKeys[0];
                    let firstPlayerRobot = _worldPlayerRobots[firstRobotKey];
                    //console.log('-> firstRobotKey =', firstRobotKey);
                    //console.log('-> firstPlayerRobot =', firstPlayerRobot);
                    delete _worldPlayerRobots[firstRobotKey];
                    _worldPlayerRobots[firstRobotKey] = firstPlayerRobot;
                    playerRobotKeys = Object.keys(_worldPlayerRobots);
                    //console.log('-> _worldPlayerRobots(keys)(after) =', playerRobotKeys);
                    // Now we rotate the robots in the overview by moving the first robot to the end of the list
                    $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
                    let $firstOverviewRobot = $teamRobotsInOverview.first();
                    //console.log('-> $firstOverviewRobot =', $firstOverviewRobot);
                    $firstOverviewRobot.appendTo($('.team-robots', $robotsOverview));
                    // And then finally we need to reposition the robots on the world map too by indexing all their current positions,
                    // then removing the first robot from the map and appending it to the end of the list, then repositioning all the robots
                    let teamPositionsByKey = [];
                    $robotsOnMap.each(function(index, robot){
                        let $robot = $(robot);
                        let key = parseInt($robot.attr('data-key'));
                        let xPos = parseInt($robot.css('left')) || 0;
                        let yPos = parseInt($robot.css('top')) || 0;
                        teamPositionsByKey[key] = [xPos, yPos];
                        });
                    let firstKey = parseInt(Object.keys(teamPositionsByKey)[0]) || false;
                    let lastKey = parseInt(Object.keys(teamPositionsByKey).slice(-1)[0]) || false;
                    //console.log('---> teamPositionsByKey =', teamPositionsByKey);
                    //console.log('---> firstKey =', firstKey);
                    //console.log('---> lastKey =', lastKey);
                    $robotsOnMap.each(function(){
                        let $robot = $(this);
                        let key = parseInt($robot.attr('data-key'));
                        let newKey = key - 1;
                        if (newKey < firstKey){ newKey = lastKey; }
                        $robot.attr('data-key', newKey);
                        let newPosition = teamPositionsByKey[newKey] || [0, 0];
                        let newPositionZ = newPosition[1] + 1;
                        $robot.css({
                            left: newPosition[0] + 'px',
                            top: newPosition[1] + 'px',
                            zIndex: newPositionZ
                            });
                        });
                    // and then save the world state with the new robot order
                    _self.saveWorldState();
                    // Return true on success
                    return true;
                    });
                }
            // Bind a click event to the team-switch button in the robots overview
            let $switchButton = $('.team-switch', $robotsOverview);
            if ($switchButton && $switchButton.length){
                // expand/collapse the robot storage tray by clicking the switch button
                $switchButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    //console.log('%c' + 'Team switch button clicked!', 'color: cyan;');
                    // First we start by either toggling the expanded class on the overview panel itself
                    $robotsOverview.toggleClass('expanded');
                    $teamRobotsInOverview.removeClass('selected');
                    let isExpandedNow = $robotsOverview.is('.expanded');
                    //_worldCursor.busy = isExpandedNow ? true : false; // set the cursor busy state
                    _world.mapIsHidden = isExpandedNow ? true : false; // set the map hidden state
                    if (!isExpandedNow){ return; } // if we're not expanded, then we're done here
                    // Make the first robot in the overview as selected via class
                    let $firstOverviewRobot = $teamRobotsInOverview.first();
                    $firstOverviewRobot.addClass('selected');
                    if ($sideButtons.is('.active')){
                        //console.log('-> side buttons active, make sure we dismiss!');
                        let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                        $sideButtons.removeClass('maybe');
                        $dismissButton.trigger('click');
                        }
                    // Refresh the page buttons after opening the storage drawer just-in-case
                    //makeStoragePages();
                    // Return true on success
                    return true;
                    });
                // if the storage tray is open, clicking a robot in the team-list marks it as selected
                $teamRobotsDiv.delegate('.team-robot[data-robot]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Team robot clicked!', 'color: cyan;');
                    // First we remove the selected class from any robots that already have it
                    $teamRobotsInOverview.removeClass('selected');
                    // Then add it to the clicked robot instead
                    $(this).addClass('selected');
                    // Return true on success
                    return true;
                    });
                // if the storage tray is open, clicking a robot in the storage-list swaps it with selected team-robot
                $storageRobotsDiv.delegate('.team-robot[data-robot]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage robot clicked!', 'color: cyan;');
                    // First we collect references to the selected team-robot and clicked storage-robot
                    let $selectedTeamRobot = $teamRobotsInOverview.filter('.selected').first();
                    if (!$selectedTeamRobot || !$selectedTeamRobot.length){ return; } // if no robot is selected, ignore clicks
                    let $clickedStorageRobot = $(this);
                    if (!$clickedStorageRobot || !$clickedStorageRobot.length){ return; } // if no robot is clicked, ignore clicks
                    //console.log('-> $selectedTeamRobot =', $selectedTeamRobot);
                    //console.log('-> swap for $clickedStorageRobot =', $clickedStorageRobot);
                    // Collect the robot tokens for each robot and make sure they're different
                    let selectedRobotToken = $selectedTeamRobot.attr('data-robot') || false;
                    let clickedRobotToken = $clickedStorageRobot.attr('data-robot') || false;
                    let clickedRobotSlot = $clickedStorageRobot.attr('data-slot') || false;
                    if (!selectedRobotToken || !clickedRobotToken || selectedRobotToken === clickedRobotToken){ return; }
                    if (!clickedRobotSlot || isNaN(parseInt(clickedRobotSlot))){ return; } // if no slot, ignore clicks
                    //console.log('-> selectedRobotToken =', selectedRobotToken);
                    //console.log('-> swap for clickedRobotToken =', clickedRobotToken);
                    // Clone the current spans so we can easily reset if we have to
                    let $teamRobotsInOverviewBackup = $teamRobotsInOverview.clone(true).detach(); // save for later
                    let $storageRobotsInOverviewBackup = $storageRobotsInOverview.clone(true).detach(); // save for later
                    // Now we swap the two elements at exactly the same position without their respective parent containers
                    $selectedTeamRobot.clone(true).insertAfter($clickedStorageRobot).removeClass('selected').attr('data-slot', clickedRobotSlot);
                    $clickedStorageRobot.clone(true).insertBefore($selectedTeamRobot).addClass('selected').removeAttr('data-slot');
                    $selectedTeamRobot.remove();
                    $clickedStorageRobot.remove();
                    $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
                    $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
                    // If this new list of robots in the overview does not match what's saved, add save button
                    let newListOfRobotsInOverview = $teamRobotsInOverview.map(function(){ return $(this).attr('data-robot'); }).get();
                    let listHasChanged = newListOfRobotsInOverview.join(',') !== listOfRobotsInOverview.join(',') ? true : false;
                    //console.log('-> newListOfRobotsInOverview = ', newListOfRobotsInOverview);
                    //console.log('-> listHasChanged = ', listHasChanged);
                    if (!listHasChanged){ return; }
                    // Add the save/cancel button set to the overview panel if it doesn't already exist
                    let $saveButton = $('<a href="#" class="button save">Save &amp Reload</a>');
                    let $cancelButton = $('<a href="#" class="button cancel">Cancel</a>');
                    $storageRobotsDiv.append($saveButton).append($cancelButton);
                    $switchButton.addClass('disabled');
                    // Define the save/cancel actions to bind to the buttons
                    let cancelAction = function(){
                        //console.log('%c' + '-> robot-storage cancelAction() triggered', 'color: magenta;');
                        // First we revert the robots in the overview back to the backup copy we made earlier
                        $teamRobotsDiv.empty().append($teamRobotsInOverviewBackup);
                        $storageRobotsDiv.empty().append($storageRobotsInOverviewBackup);
                        $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
                        $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
                        // Then we remove the save/cancel button set from the overview panel
                        $saveButton.remove();
                        $cancelButton.remove();
                        $switchButton.removeClass('disabled');
                        // Return true on success
                        return true;
                        };
                    let saveAction = function(){
                        //console.log('%c' + '-> robot-storage saveAction() triggered', 'color: magenta;');
                        // First we remove the save/cancel button set from the overview panel
                        $saveButton.remove();
                        $cancelButton.remove();
                        // Then we update the world player robots data to match the new order in the overview
                        let newPlayerRobotList = [];
                        $teamRobotsInOverview.each(function(index, robot){
                            //console.log('-> checking robot', index, robot);
                            let $robot = $(robot);
                            let robotString = $robot.attr('data-robot') || false;
                            if (!robotString || !robotString.length){ return; }
                            //console.log('-> robotString =', robotString);
                            newPlayerRobotList.push(robotString);
                            });
                        //console.log('-> newPlayerRobotList =', newPlayerRobotList);
                        // Make sure we have new robots selected, else abort the save
                        if (!newPlayerRobotList.length){ cancelAction(); return false; }
                        // Then we save the world state with the new robot order and reload the page to reflect changes
                        // NOTE: We don't need to actually swap robots on the map since we're reloading the page
                        //console.log('okay time to save the world state!');
                        _self.incZoomLevel();
                        _self.saveWorldState(function(){
                            _self.playSoundEffect('switch-in');
                            let worldReloadURL = 'world.php?robots=' + newPlayerRobotList.join(',');
                            //console.log('-> worldReloadURL =', worldReloadURL);
                            $thisWorld.addClass('hidden').removeClass('busy');
                            window.location.href = worldReloadURL;
                            });
                        // Return true on success
                        return true;
                        };
                    // Bind events to the cancel button that'll revert the changes we've mapStartDirection made
                    $cancelButton.bind('click', function(e){
                        //console.log('%c' + 'Robot swap cancel button clicked!', 'color: cyan;');
                        e.preventDefault();
                        return cancelAction();
                        });
                    // Bind events to the save button that'll save the changes we've made
                    $saveButton.bind('click', function(e){
                        //console.log('%c' + 'Robot swap save button clicked!', 'color: cyan;');
                        e.preventDefault();
                        saveAction();
                        });
                    // Return true on success
                    return true;
                    });
                // if the storage tray is open, clicking a page-button in the storage-list scrolls through selected team-robots
                let makeStorageBullets = function(){
                    //console.log('%c' + 'makeStorageBullets() called!', 'color: magenta;');
                    $('.bullets', $storageRobotsDiv).remove();
                    let listBulletsMarkup = '';
                    listBulletsMarkup += '<div class="bullets">';
                        for (var i = 0; i < storageSlotsVisible; i++){
                            let key = i;
                            let position = (i + 1);
                            let bulletMarkup = '<span class="bullet" data-key="' + key + '">' + position + '</span>';
                            listBulletsMarkup += bulletMarkup;
                            }
                    listBulletsMarkup += '</div>';
                    //console.log('-> appending listBulletsMarkup =', listBulletsMarkup);
                    $storageRobotsDiv.append(listBulletsMarkup);
                    return true;
                    };
                let makeStoragePages = function(){
                    //console.log('-> makeStoragePages() called!');
                    // (Re)count the number of robots present in the storage locker and paginate if necessary
                    storageSlotsVisible = _config.robotStorageSlotsVisible;
                    storageRobotsWaiting = $storageRobotsInOverview.length;
                    //console.log('-> storageSlotsVisible = ', storageSlotsVisible);
                    //console.log('-> storageRobotsWaiting = ', storageRobotsWaiting);
                    if (storageRobotsWaiting <= storageSlotsVisible){ return; }
                    // Add buttons for each of the available pages (we'll arrange them in the CSS)
                    numStoragePagesRequired = Math.ceil(storageRobotsWaiting / storageSlotsVisible);
                    //console.log('-> need to paginate storage robots across ' + numStoragePagesRequired + ' pages');
                    $('.pages', $storageRobotsDiv).remove();
                    let pageButtonMarkup = '';
                    pageButtonMarkup += '<div class="pages">';
                        pageButtonMarkup += '<a href="#" class="button page back" data-page="back"><i class="fa fas fa-caret-left"></i></a>';
                        for (var i = 0; i < numStoragePagesRequired; i++){
                            let pageNum = (i + 1);
                            let buttonMarkup = '<a href="#" class="button page' + (i === 0 ? ' active' : '') + '" data-page="' + pageNum + '">' + pageNum + '</a>';
                            pageButtonMarkup += buttonMarkup;
                            }
                        pageButtonMarkup += '<a href="#" class="button page next" data-page="next"><i class="fa fas fa-caret-right"></i></a>';
                    pageButtonMarkup += '</div>';
                    //console.log('-> appending pageButtonMarkup =', pageButtonMarkup);
                    $storageRobotsDiv.append(pageButtonMarkup);
                    };
                let goToStoragePage = function(pageNum){
                    //console.log('%c' + '-> goToStoragePage(' + pageNum + ') triggered', 'color: magenta;');
                    let storageSlotsVisible = _config.robotStorageSlotsVisible;
                    if (typeof pageNum !== 'number'){ pageNum = parseInt(pageNum); }
                    if (!pageNum || pageNum < 1){ pageNum = 1; }
                    let startIndex = (pageNum - 1) * storageSlotsVisible;
                    let endIndex = startIndex + storageSlotsVisible;
                    //console.log('-> goToStoragePage() for pageNum ' + pageNum + ' with startIndex ' + startIndex + ' and endIndex ' + endIndex);
                    $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
                    //console.log('-> $storageRobotsInOverview = ', $storageRobotsInOverview.length, $storageRobotsInOverview);
                    $('.bullet[data-key]', $storageRobotsDiv).text(''); // clear the bullets
                    $storageRobotsInOverview.removeAttr('data-slot');
                    $storageRobotsInOverview.addClass('hidden');
                    $storageRobotsInOverview.slice(startIndex, endIndex).removeClass('hidden').each(function(index){
                        //console.log('-> adding slot to robot at index ' + index + ' (data-slot will be ' + (index + 1) + ')');
                        let $robot = $(this);
                        let newSlot = (index + 1);
                        let overallPosition = (startIndex + index + 1);
                        $robot.attr('data-slot', newSlot);
                        $('.bullet[data-key="'+index+'"]', $storageRobotsDiv).text(overallPosition);
                        });
                    currentStoragePageNum = pageNum;
                    //console.log('-> currentStoragePageNum =', currentStoragePageNum);
                    //console.log('-> numStoragePagesRequired =', numStoragePagesRequired);
                    $('.button[data-page]', $storageRobotsDiv).removeClass('active').removeClass('disabled');
                    $('.button[data-page="' + pageNum + '"]', $storageRobotsDiv).addClass('active');
                    if (currentStoragePageNum === 1){
                        //console.log('-> $storageRobotsDiv buttons... ', $('.button', $storageRobotsDiv));
                        //console.log('-> disabling back button');
                        $('.button[data-page="back"]', $storageRobotsDiv).addClass('disabled');
                        }
                    if (currentStoragePageNum === numStoragePagesRequired){
                        //console.log('-> disabling next button');
                        $('.button[data-page="next"]', $storageRobotsDiv).addClass('disabled');
                        }
                    };
                $storageRobotsDiv.delegate('.button[data-page]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage page button clicked!', 'color: cyan;');
                    let $button = $(this);
                    if ($button.is('.active')){ return; } // already on this page, ignore clicks
                    let curNum = currentStoragePageNum;
                    let pageNum = $button.attr('data-page');
                    if (pageNum === 'back'){ pageNum = curNum - 1; }
                    else if (pageNum === 'next'){ pageNum = curNum + 1; }
                    else if (typeof pageNum === 'number'){ pageNum = parseInt(pageNum); }
                    if (!pageNum || pageNum < 1){ pageNum = 1; }
                    //console.log('-> pageNum =', pageNum);
                    if (pageNum === currentStoragePageNum){ return; } // already on this page, ignore clicks
                    //$('.button', $storageRobotsDiv).removeClass('active');
                    //$button.addClass('active');
                    goToStoragePage(pageNum);
                    // Return true on success
                    return true;
                    });
                // make sure we show the first page of storage robots by default
                makeStorageBullets();
                makeStoragePages();
                goToStoragePage(1); // initialize to page 1
                }
            }
        // Bind events to the scrolling of the user's mouse if detected to allow for zooming the map
        let busyZooming = false;
        $thisWorld.bind('mousewheel', function(e){
            //console.log('%c' + 'World map mousewheel event!', 'color: cyan;');
            if (_self.worldIsBusy()){ return; }
            if (_self.worldMapIsHidden()){ return; }
            e.preventDefault();
            e.stopPropagation();
            //console.log('-> event:', e);
            //console.log('-> wheelDelta:', e.wheelDelta);
            if (busyZooming){ return false; }
            if (!e.wheelDelta){ return false; }
            else if (e.wheelDelta > 0 && e.wheelDelta < 200){ return false; }
            else if (e.wheelDelta < 0 && e.wheelDelta > -200){ return false; }
            busyZooming = true;
            let wheelDir = e.wheelDelta > 0 ? 'up' : 'down';
            let oldZoom = _world.zoomLevel || 1;
            if (wheelDir === 'up'){ _self.incZoomLevel(null, true); }
            else { _self.decZoomLevel(null, true); }
            let newZoom = _world.zoomLevel || 1;
            if (newZoom === oldZoom){ busyZooming = false; return; }
            else { setTimeout(function(){ busyZooming = false; }, 1000); }
            _self.playSoundEffect('spawn-sound')
            return true;
            });
        // Bind events to the keyboard arrow keys if detected to allow for;
        // - moving the player up/down/left/right
        // - confirming an action popup via enter/space
        // - declining an action popup via escape/backspace
        let pressedKeys = {};
        document.addEventListener('keydown', (event) => { pressedKeys[event.key] = true; });
        document.addEventListener('keyup', (event) => { delete pressedKeys[event.key]; });
        $(document).bind('keydown', function(e){
            //console.log('%c' + 'World map keydown event!', 'color: cyan;');
            //e.preventDefault();
            //e.stopPropagation();
            //console.log('-> event:', e);
            if (_self.worldIsBusy()){ return false; }
            //console.log('-> pressedKeys:', pressedKeys);
            // Collect references and checks on certain key elements
            let sideButtonsActive = $sideButtons.is('.active') ? true : false;
            // If the player has pressed any of the arrow keys, let's update the position accordingly
            if (pressedKeys.ArrowLeft || pressedKeys.ArrowRight || pressedKeys.ArrowUp || pressedKeys.ArrowDown){
                //console.log('%c' + 'Arrow key pressed!', 'color: orange;');
                e.preventDefault();
                let worldMapIsHidden = _self.worldMapIsHidden();
                // World map is NOT hidden, so the arrow keys must be controlling the player
                if (!worldMapIsHidden){
                    let oldPos = _world.cursor.position, curPos = oldPos;
                    let thisPos = oldPos.split('-');
                    let thisCol = parseInt(thisPos[0]);
                    let thisRow = parseInt(thisPos[1]);
                    let newCol = thisCol, newRow = thisRow;
                    //console.log('%c' + 'Current position: ' + oldPos, 'color: orange;');
                    if (pressedKeys.ArrowLeft){ newCol--; }
                    else if (pressedKeys.ArrowRight){ newCol++; }
                    if (pressedKeys.ArrowUp){ newRow--; }
                    else if (pressedKeys.ArrowDown){ newRow++; }
                    let newPos = newCol + '-' + newRow;
                    //console.log('%c' + 'New position: ' + newPos, 'color: orange;');
                    // Check if the new position is the same as the old position
                    if (newCol === thisCol && newRow === thisRow){ return false; }
                    // Otherwise, let's pull the list of walkable tiles and see if this new position is valid
                    //console.log('%c' + 'Checking if new position is walkable...', 'color: orange;');
                    let playerMobility = _config.playerMobility;
                    let walkableTiles = _self.getWalkableMapTiles();
                    let tilesWithinRange = playerMobility > 0 ? _self.getWalkableMapTilesByProximity(oldPos, playerMobility) : walkableTiles;
                    if (walkableTiles.indexOf(newPos) === -1 && tilesWithinRange.indexOf(newPos) === -1){
                        //console.warn('%c' + 'New position is not walkable!', 'color: red;');
                        //_self.playSoundEffect('glass-klink');
                        return false;
                        }
                    // Otherwise, let's move the cursor to the new position
                    _self.makeLayerTileActive(newPos);
                    _self.playSoundEffect('no-effect');
                    _self.moveToPosition(newPos, function(){
                        _self.makeLayerTileInactive(oldPos);
                        });
                    }
                // Otherwise if world map IS HIDDEN, might mean we need to use arrow keys for something else
                else {

                    // TODO: add functionality for when player-switcher pallet is active
                    // TODO: add functionality to the team-switch drawer is open

                    }
                }
            // If the side buttons panel is currently open, process those actions too
            if (sideButtonsActive){
                // If the player has pressed the space or enter keys, let's confirm the side-button action if it's open
                if (pressedKeys.Space || pressedKeys.Enter || pressedKeys.NumpadEnter ){
                    //console.log('%c' + 'Confirm action popup!', 'color: orange;');
                    e.preventDefault();
                    if (!$sideButtons.is('.active')){ return false; }
                    let $confirmButton = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons).first();
                    if (!$confirmButton || !$confirmButton.length){ console.error('bindEventsToWorld() unable to find confirm button!'); return false; }
                    if ($confirmButton.is('.clicked')){ return }
                    if (!$confirmButton.is('.maybe')){ $confirmButton.addClass('maybe'); return; }
                    $confirmButton.removeClass('maybe');
                    //console.log('Triggering click on confirm button:', $confirmButton);
                    $confirmButton.trigger('click');
                    }
                // Else if the player has pressed the backspace or escape keys, let's close the side-button action if it's open
                else if (pressedKeys.Backspace || pressedKeys.Escape){
                    //console.log('%c' + 'Dismiss action popup!', 'color: orange;');
                    e.preventDefault();
                    if (!$sideButtons.is('.active')){ return false; }
                    let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                    if (!$dismissButton || !$dismissButton.length){ console.error('bindEventsToWorld() unable to find dismiss button!'); return false; }
                    $sideButtons.removeClass('maybe');
                    $dismissButton.trigger('click');
                    }
                // Else if the player has just pressed shift, make sure we add the hover class to the action-dropdown
                else if (pressedKeys.Shift){
                    //console.log('%c' + 'Shift key pressed!', 'color: orange;');
                    e.preventDefault();
                    if (!$sideButtons.is('.active')){ return false; }
                    $actionDropdown.toggleClass('hover');
                    }
                }

            });
        // Bind an event to the window resize so we can check devicePixelRatio and adjust rendering if needed
        $(window).bind('resize', function(e){
            //console.log('%c' + 'World map window resize event!', 'color: cyan;');
            //e.preventDefault();
            //e.stopPropagation();
            //console.log('-> event:', e);
            //console.log('-> window.devicePixelRatio:', window.devicePixelRatio);
            let pixelRatio = window.devicePixelRatio || 1;
            let imageRendering = pixelRatio === 1 || pixelRatio >= 2 ? 'pixelated' : 'auto';
            //console.log('-> pixelRatio:', pixelRatio, '\n', '-> imageRendering:', imageRendering);
            $thisWorld.attr('data-rendering', imageRendering);
            }).trigger('resize');

        // Return true on success
        return true;
        }

    // Quick function for moving cursor to a given map position
    moveToPosition(newPosition, onComplete, forceMove, animateMove, thisOldPos){
        //console.log('%c' + 'mmrpgWorldMap.moveToPosition(' + newPosition + ')', 'color: magenta;');
        if (!newPosition || typeof newPosition === 'undefined'){ console.error('newPosition is undefined!'); return false; }
        else if (typeof newPosition !== 'string' || !newPosition.match(/^[0-9]+\-[0-9]+$/)){ console.error('newPosition is invalid!', newPosition); return false; }
        else { newPosition = newPosition.split('-'); }
        forceMove = typeof forceMove === 'boolean' ? forceMove : false;
        animateMove = typeof animateMove === 'boolean' ? animateMove : true;
        let _self = this;
        let _selfRef = self;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let _mapSpriteSizeOffset = _config.mapSpriteSizeOffset;
        let _mapStartPosition = _config.mapStartPosition;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $sideButtons = _elements.sideButtons;
        let $actionDropdown = _elements.actionDropdown;
        let $teamSprites = _elements.teamSprites;
        let $backgroundLayer = $('.layer[data-layer="background"]', $canvasMap);
        let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap); // later: $('.layer[data-layer="sprites"]', $canvasMap);
        let $cursorSprite = $teamSprites.filter('.cursor');
        let $otherSprites = $teamSprites.filter(':not(.cursor)');
        if (!$spritesLayer || !$spritesLayer.length){ console.error('$spritesLayer do not exist!'); return false; }
        if (!$cursorSprite || !$cursorSprite.length){ console.error('$cursorSprite not found!'); return false; }
        if (!$actionDropdown || !$actionDropdown.length){ console.error('$actionDropdown not found!'); return false; }
        if (!thisOldPos){ thisOldPos = [_worldCursor.col, _worldCursor.row]; }
        let thisOldCol = thisOldPos[0]; //_worldCursor.col;
        let thisOldRow = thisOldPos[1]; //_worldCursor.row;
        let thisNewCol = parseInt(newPosition[0]);
        let thisNewRow = parseInt(newPosition[1]);
        if (thisNewCol === thisOldCol && thisNewRow === thisOldRow && !forceMove){ console.error('$cursorSprite already at position!'); return false; }
        let thisNewPos = thisNewCol + '-' + thisNewRow;
        let thisHorDir = (thisNewCol > thisOldCol) ? 'right' : (thisNewCol < thisOldCol) ? 'left' : false;
        let thisVerDir = (thisNewRow > thisOldRow) ? 'down' : (thisNewRow < thisOldRow) ? 'up' : false;
        let thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join(' and '); })(thisHorDir, thisVerDir);
        let thisShiftDist = Math.sqrt(Math.pow(thisNewCol - thisOldCol, 2) + Math.pow(thisNewRow - thisOldRow, 2));
        let tileOffsetX = ((thisNewCol - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0];
        let tileOffsetY = ((thisNewRow - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1];
        let tileOffsetZ = tileOffsetY + 1;
        let cursorHasMoved = _worldCursor.moved || thisNewPos !== _mapStartPosition ? true : false;
        _self.resetZoomLevel();
        $canvasMap.addClass('busy');
        _worldCursor.moving = true;
        $actionDropdown.removeClass('active');
        $spritesLayer.removeClass('has-zoom');
        $('.sprite.zoom', $spritesLayer).removeClass('zoom');
        $('.sprite[data-frame]:not(.disabled)', $canvasMap).attr('data-frame', '00');
        $('.sprite.idle', $spritesLayer).removeClass('idle');
        // Move the cursor to the new position first and foremost
        let moveTimeout;
        let timeoutDuration = _mapEffects.moveTimeout;
        let travelDuration = _mapEffects.moveTravel * thisShiftDist;
        let onMoveComplete = function(){
            _worldCursor.col = thisNewCol;
            _worldCursor.row = thisNewRow;
            _worldCursor.position = thisNewPos;
            _worldCursor.direction = thisShiftDir.replace(/ and /g, '-');
            _worldCursor.moved = cursorHasMoved;
            _worldPlayer.position = _worldCursor.position;
            _worldPlayer.direction = _worldCursor.direction;
            _worldCursor.positionXY = [tileOffsetX, tileOffsetY];
            //console.log('_worldCursor =', '\n-> col =', _worldCursor.col, '\n-> row =', _worldCursor.row, '\n-> position =', _worldCursor.position, '\n-> direction =', _worldCursor.direction, '\n-> moved =', _worldCursor.moved);
            $cursorSprite.attr('data-col', thisNewCol);
            $cursorSprite.attr('data-row', thisNewRow);
            $cursorSprite.attr('data-pos', _worldCursor.position);
            _self.resetZoomLevel();
            _self.updateMapPosition();
            _self.makeLayerTileActive(_worldCursor.position);
            if (moveTimeout){ clearTimeout(moveTimeout); }
            let delay = timeoutDuration - travelDuration;
            let doAfterDelay = function(){
                _worldCursor.moving = false;
                $canvasMap.removeClass('busy');
                if (typeof onComplete === 'function'){ onComplete(); }
                if (cursorHasMoved){ _self.saveWorldState(null, 6); }
                };
            if (delay > 0){ moveTimeout = setTimeout(doAfterDelay, delay); }
            else { doAfterDelay(); }
            };
        if (animateMove){
            $cursorSprite.animate({
                left: tileOffsetX + 'px',
                top: tileOffsetY + 'px',
                zIndex: tileOffsetZ,
                }, travelDuration, 'linear', onMoveComplete);
            } else {
            $cursorSprite.css({
                left: tileOffsetX + 'px',
                top: tileOffsetY + 'px',
                zIndex: tileOffsetZ,
                }); onMoveComplete();
            }
        // If there are any team sprites, move them as well (it's okay if they lay behind the cursor)
        if ($otherSprites && $otherSprites.length){
            let $otherSpritesInOrder = $(Array.from($otherSprites).sort(function(a, b){
                const aKey = parseInt($(a).attr('data-key'));
                const bKey = parseInt($(b).attr('data-key'));
                //console.log('-> aKey =', aKey, 'vs bKey =', bKey);
                if (aKey < bKey) return -1;
                if (aKey > bKey) return 1;
                return 0;
                }));
            //console.log('-> $otherSprites =', $otherSprites);
            //console.log('-> $otherSpritesInOrder =', $otherSpritesInOrder);
            let teamOffsetX = tileOffsetX;
            let teamOffsetY = tileOffsetY;
            let teamOffsetZ = tileOffsetZ;
            let teamTravelDuration = travelDuration;
            teamTravelDuration += 50;
            $otherSpritesInOrder.each(function(index, element){
                let $thisSprite = $(element);
                let $innerSprite = $('.sprite', $thisSprite);
                let imgSize = $thisSprite.attr('data-size') || 40;
                let imgSizeX = imgSize + 'x' + imgSize;
                teamTravelDuration += 50; // add a little extra time for the team sprites to move
                if (thisVerDir === 'up'){ teamOffsetY += 10; }
                else if (thisVerDir === 'down'){ teamOffsetY -= 20; }
                if (thisHorDir === 'left'){ teamOffsetX += 20; }
                else if (thisHorDir === 'right'){ teamOffsetX -= 20; }
                teamOffsetZ = teamOffsetY + 1;
                if (thisHorDir){ $thisSprite.attr('data-dir', thisHorDir); }
                let onTeamMoveComplete = function(){};
                if (!$thisSprite.is('.disabled')){
                    let newFrame = $thisSprite.is('.player') ? '09' : $thisSprite.is('.robot') ? '07' : '00'; // run for players, slide for robots
                    $thisSprite.attr('data-frame', newFrame);
                    onTeamMoveComplete = function(){ $thisSprite.attr('data-frame', '00'); };
                    }
                $thisSprite.prop('worldX', teamOffsetX);
                $thisSprite.prop('worldY', teamOffsetY);
                $thisSprite.prop('worldZ', teamOffsetZ);
                if (animateMove){
                    $thisSprite.animate({
                        left: teamOffsetX + 'px',
                        top: teamOffsetY + 'px',
                        zIndex: teamOffsetZ,
                        }, teamTravelDuration, 'swing', onTeamMoveComplete);
                    } else {
                    $thisSprite.css({
                        left: teamOffsetX + 'px',
                        top: teamOffsetY + 'px',
                        zIndex: teamOffsetZ,
                        }); onTeamMoveComplete();
                    }
                });
            }
        // Make sure we start the scroll to the new position
        _self.scrollMap(tileOffsetX, tileOffsetY);
        // Start the idle timeout (clearing if already exists) so we can run idle-actions
        if (_selfRef.idleTimeout){ clearTimeout(_selfRef.idleTimeout); }
        _selfRef.idleTimeout = setTimeout(function(){
            //console.log('%c' + 'Idle timeout triggered!', 'color: orange;');
            $cursorSprite.addClass('idle');
            }, 3000);
        return true;
        }

    // Quick function for re-centering the map on the player's position and the map's current zoom level
    scrollMap(scrollX, scrollY){
        //console.log('%c' + 'mmrpgWorldMap.scrollMap(scrollX:' + scrollX + ', scrollY:' + scrollY + ')', 'color: magenta;');
        // Collect references, indexes, and other variables we need to work with
        let _self = this;
        let _selfRef = self;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let usePerspective = _mapEffects.usePerspective;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $backgroundLayer = $('.layer[data-layer="background"]', $canvasMap);
        let $terrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
        if (typeof scrollX !== 'number'){ scrollX = _worldCursor.positionXY[0] || 0; }
        if (typeof scrollY !== 'number'){ scrollY = _worldCursor.positionXY[1] || 0; }
        // And now we should move the map itself so that the characters are always centered in the viewport
        if (!_selfRef.lastWorldZoom){ _selfRef.lastWorldZoom = _world.zoomLevel; }
        let worldZoom = _world.zoomLevel;
        let worldWidth = _config.worldWidth;
        let worldHeight = _config.worldHeight;
        let mapWidth = _config.mapWidth * worldZoom;
        let mapHeight = _config.mapHeight * worldZoom;
        let mapTileSizeX = _mapTileSize[0];
        let mapTileSizeY = _mapTileSize[1];
        let mapTileSizeOffsetX = _mapTileSizeOffset[0];
        let mapTileSizeOffsetY = _mapTileSizeOffset[1];
        let mapScrollX = scrollX;
        let mapScrollY = scrollY;
        let targetX = (mapScrollX + (mapTileSizeX / 2) - (mapTileSizeOffsetX / 2)) * worldZoom;
        let targetY = (mapScrollY + (mapTileSizeY / 2) - (mapTileSizeOffsetY / 2)) * worldZoom;
        // If perspective mode is currently on, we need to do some other pre-adjustments
        if (usePerspective){
            //console.log('-> _config.mapWidth:', _config.mapWidth, '_config.mapHeight:', _config.mapHeight);
            $canvasMap.addClass('has-perspective');
            $canvasMap.get(0).style.setProperty('--map-perspective-width', _config.mapWidth+'px');
            let newLayerRect = $terrainLayer[0].getBoundingClientRect();
            let newLayerWidth = newLayerRect.width, newLayerHeight = newLayerRect.height;
            //console.log('-> newLayerRect:', newLayerRect);
            //console.log('-> newLayerWidth:', newLayerWidth, 'newLayerHeight:', newLayerHeight);
            $canvasMap.css({ width: newLayerWidth + 'px', height: newLayerHeight + 'px' });
            mapWidth = newLayerWidth * worldZoom, mapHeight = newLayerHeight * worldZoom;
            }
        else {
            $canvasMap.removeClass('has-perspective');
            $canvasMap.get(0).style.setProperty('--map-perspective-width', '');
            $canvasMap.css({ width: _config.mapWidth + 'px', height: _config.mapHeight + 'px' });
            }
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
        let mapTranslateX = translateX;
        let mapTranslateY = translateY;
        let subTranslateX = Math.round(-1 * (translateX * 0.1));
        let subTranslateY = Math.round(-1 * (translateY * 0.1));
        // Apply the new translate values to the map container
        //$canvasMap.css({ transform: 'translate(' + mapTranslateX + 'px, ' + mapTranslateY + 'px)' });
        if (worldZoom !== _selfRef.lastWorldZoom){ _world.allowClicks = _world.allowHovers = false; }
        $canvasMap.attr('data-zoom', worldZoom);
        $canvasMap.css({ transformOrigin: 'left top', transform: 'translate(' + mapTranslateX + 'px, ' + mapTranslateY + 'px) scale(' + worldZoom + ')' });
        $backgroundLayer.css({ transformOrigin: 'left top', transform: 'translate(' + subTranslateX + 'px, ' + subTranslateY + 'px)' });
        if (worldZoom !== _selfRef.lastWorldZoom){ setTimeout(function(){ _world.allowClicks = _world.allowHovers = true; }, 1000); }
        // Return true on success
        return true;
        }

    // Quick function for updating the map's current zoom level
    updateZoomLevel(newZoomLevel, updateUserZoom){
        //console.log('%c' + 'mmrpgWorldMap.updateZoomLevel(newZoomLevel:' + newZoomLevel + ')', 'color: magenta;');
        if (!newZoomLevel || typeof newZoomLevel !== 'number' || newZoomLevel <= 0){
            console.error('updateZoomLevel() requires a valid zoom level!');
            return false;
            }
        let _self = this;
        let _elements = _self.elements;
        let _world = _self.state;
        updateUserZoom = typeof updateUserZoom === 'boolean' ? updateUserZoom : false;
        _world.zoomLevel = newZoomLevel
        if (updateUserZoom){ _world.userZoomLevel = newZoomLevel; }
        _self.scrollMap();
        return true;
        }

    // Quick function for increasing/decreasing the zoom level while respecting system min/max defaults
    modZoomLevel(modAmount, updateUserZoom){
        //console.log('%c' + 'mmrpgWorldMap.modZoomLevel(modAmount:' + modAmount + ')', 'color: magenta;');
        if (!modAmount || typeof modAmount !== 'number' || modAmount === 0){
            console.error('modZoomLevel() requires a valid modification amount!');
            return false;
            }
        let _self = this;
        let _elements = _self.elements;
        let _world = _self.state;
        let userZoomLevel = _world.userZoomLevel || 1;
        userZoomLevel += modAmount;
        if (userZoomLevel < 0.5){ userZoomLevel = 0.5; }
        else if (userZoomLevel > 2){ userZoomLevel = 2; }
        return _self.updateZoomLevel(userZoomLevel, updateUserZoom);
        }
    incZoomLevel(incAmount, updateUserZoom){ return this.modZoomLevel((incAmount || 0.25), updateUserZoom); }
    decZoomLevel(decAmount, updateUserZoom){ return this.modZoomLevel(-(decAmount || 0.25), updateUserZoom); }

    // Quick function for resetting the map's zoom level to the user's current setting
    resetZoomLevel(){
        //console.log('%c' + 'mmrpgWorldMap.resetZoomLevel()', 'color: magenta;');
        let _self = this;
        let _elements = _self.elements;
        let _world = _self.state;
        let userZoomLevel = _world.userZoomLevel || 1;
        if (userZoomLevel < 0.5){ userZoomLevel = 0.5; }
        else if (userZoomLevel > 2){ userZoomLevel = 2; }
        _self.updateZoomLevel(userZoomLevel, true);
        return true;
        }

    // Quick function for toggling perspective mode on/off and then re-scrolling the map to refresh
    togglePerspectiveMode(){
        //console.log('%c' + 'mmrpgWorldMap.togglePerspectiveMode()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _mapEffects = _config.mapEffects;
        if (!_mapEffects.usePerspective){ _mapEffects.usePerspective = true; }
        else { _mapEffects.usePerspective = false; }
        _self.scrollMap();
        return true;
        }

    // Quick function for updating the map interface and zoom/scroll after a position change
    async updateMapPosition(){
        //console.log('%c' + 'mmrpgWorldMap.updateMapPosition()', 'color: magenta;');

        // Collect references, indexes, and other variables we need to work with
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
        let $worldCursor = _elements.worldCursor;
        let cursorDirection = _worldCursor.direction;
        let cursorPosition = _worldCursor.position;
        let cursorPositionXY = _worldCursor.positionXY;
        let newPosition = cursorPosition.split('-');
        let thisNewCol = parseInt(newPosition[0]);
        let thisNewRow = parseInt(newPosition[1]);

        // First we update the cursor sprite position and attributes
        let $positionDisplay = $('#position-display', $thisWorld);
        let $positionDisplayWrapper = $('> .wrapper', $positionDisplay);
        let newPositionText = _config.mapName + ' | ' + ('X' + thisNewCol + '-Y' + thisNewRow);
        //$positionDisplayWrapper.text('X:' + thisNewCol + ' Y:' + thisNewRow);
        $positionDisplayWrapper.text(newPositionText);

        // Make sure we start the scroll to the new position
        _self.scrollMap(cursorPositionXY[0], cursorPositionXY[1]);

        // Update the walkable map tiles now that things have changed slightly
        _self.calculateWalkableMapTiles(true);

        // Collect the walkable map tiles so we can filter down to only those in proximity
        //console.log('-> player has moved to new position, refresh tile-focus to only those within range');
        let targetPosition = cursorPosition;
        let playerMobility = _config.playerMobility;
        let walkableTiles = _self.getWalkableMapTiles();
        let tilesWithinRange = playerMobility > 0 ? _self.getWalkableMapTilesByProximity(targetPosition, playerMobility) : walkableTiles;
        //console.log('-> new tilesWithinRange:', tilesWithinRange);
        _self.unfocusLayerTiles(); // unfocus all tiles first
        if (playerMobility > 0 && tilesWithinRange){ // then apply outlines to tiles within range
            for (let i = 0; i < tilesWithinRange.length; i++){
                let tilePosition = tilesWithinRange[i];
                _self.focusLayerTile(tilePosition);
                }
            //console.log('-> added focus to ' + tilesWithinRange.length + ' tiles within range of player position', targetPosition);
            }

        // If the player has not moved from their spawn position yet, we should not do anything further
        if (!_config.allowWorldEvents){ return true; }

        // Otherwise, we should refresh all the map position events given the new position
        _self.refreshMapPositionEvents();

        // Return true on success
        return true;
        }

    // Quick function for clearing out any existing map events then checking for new ones at new position
    async refreshMapPositionEvents(){
        //console.log('%c' + 'mmrpgWorldMap.refreshMapPositionEvents()', 'color: magenta;');

        // Collect references, indexes, and other variables we need to work with
        let _self = this;
        let _selfRef = _self.refreshMapPositionEvents;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldPlayerRobots = _worldPlayer.robots;
        let _worldPlayerRobotsKeys = Object.keys(_worldPlayerRobots);
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let _userId = _config.userId;
        let _playerId = _config.playerId;
        let _playerToken = _config.playerToken;
        let _playerRobots = _config.playerRobots;
        let _playerRobotsIndex = _config.playerRobotsIndex;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $worldCursor = _elements.worldCursor;
        let $teamSprites = _elements.teamSprites;
        let cursorDirection = _worldCursor.direction;
        let cursorPosition = _worldCursor.position;
        let newPosition = cursorPosition.split('-');
        let thisNewCol = parseInt(newPosition[0]);
        let thisNewRow = parseInt(newPosition[1]);

        // Before we do anything else, check to see if this player has any active robots
        //console.log('_worldPlayerRobots = ', _worldPlayerRobots);
        //console.log('_worldPlayerRobotsKeys = ', _worldPlayerRobotsKeys);
        let playerActiveRobots = 0;
        for (var i = 0; i < _worldPlayerRobotsKeys.length; i++){
            let token = _worldPlayerRobotsKeys[i];
            let info = _worldPlayerRobots[token] || false;
            if (!info || info.disabled){ continue; }
            playerActiveRobots++;
            }
        //console.log('playerActiveRobots = ', playerActiveRobots);

        // Define the default zoom timeout for after movement ends
        let zoomTimeoutDuration = 2000;
        let teamReadyDuration = 1800;
        let teamRushDuration = 300;

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

        // Collect references to the required sprites layer for adjustments
        let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap); // later: $('.layer[data-layer="sprites"]', $canvasMap);
        if (!$spritesLayer || !$spritesLayer.length){ console.error('updateMapPosition() missing required $spritesLayer!'); return false; }
        //console.log('-> $eventsLayer found, checking for events...');

        // Make sure we move any existing zoom layer sprites back to their original layers
        $worldCursor.removeClass('shake');
        $spritesLayer.removeClass('has-zoom');
        setTimeout(function(){ $('.sprite', $canvasMap).removeClass('zoom'); }, 100);

        // Search for events at the new position so we can show the action dropdown if needed
        //console.log('-> checking if there are any events for this position...');
        let eventsAtPosition = _self.getEventsAtPosition(newPosition);
        //console.log('-> eventsAtPosition =', eventsAtPosition);
        if (!eventsAtPosition || !eventsAtPosition.length){
            //console.log('-> no events found at position', cursorPosition, 'skipping dropdown display');
            return;
            }
        //console.log('-> found ' + eventsAtPosition.length + ' events at position');

        // Sort the events at this position by priority with portals > battles > everything-else
        eventsAtPosition = eventsAtPosition.sort(function(a, b){
            if (a.kind === 'portal' && b.kind !== 'portal'){ return -1; } // a is portal, b is not
            else if (a.kind !== 'portal' && b.kind === 'portal'){ return 1; } // a is not portal, but b is
            else if (a.kind === 'battle' && b.kind !== 'battle'){ return -1; } // a is battle, b is not
            else if (a.kind !== 'battle' && b.kind === 'battle'){ return 1; } // a is not battle, but b is
            else { return 0; } // both are same or of irrelevant kind
            });
        //console.log('-> eventsAtPosition(after-sort) = ', JSON.parse(JSON.stringify(eventsAtPosition)));

        // Check to see what the very first event type is
        let firstEvent = eventsAtPosition[0];
        let firstEventType = firstEvent.kind;
        //console.log('-> firstEvent =', JSON.parse(JSON.stringify(firstEvent)));
        //console.log('-> firstEventType =', firstEventType);

        // Unless this is a battle (where it's possible to fight many at once), we should
        // filter out all the other event types than the first so we only show one dropdown
        //console.log('-> before filtering, there are ' + eventsAtPosition.length + ' ' + firstEventType + ' events near cursorPosition', cursorPosition);
        if (firstEventType !== 'battle'){
            eventsAtPosition = eventsAtPosition.slice(0, 1); // only keep the first event
            } else {
            for (let i = 1; i < eventsAtPosition.length; i++){
                if (eventsAtPosition[i].kind !== 'battle'){
                    delete eventsAtPosition[i];
                    }
                }
            }
        eventsAtPosition = Object.values(eventsAtPosition); // re-index the array to avoid issues with gaps
        //console.log('-> after filtering, there are ' + eventsAtPosition.length + ' ' + firstEventType + ' events near cursorPosition', cursorPosition);
        firstEvent = eventsAtPosition[0]; // re-assign the first event after filtering

        // Now that we have an event, check its data to see if we should show a dropdown
        // for either a battle, a portal, or any other compatible event-type for the tile
        let triggerEffect = false;
        let triggerEffectFunction = function(){};
        let triggerEffectSound = '';
        let autoRedirect = false;
        let autoRedirectURL = '';
        let autoRedirectSound = '';
        let showDropdown = false;
        let showDropdownType = '';
        let showDropdownSound = '';
        let dropdownMarkup = '';
        let dropdownButtons = '';
        let readyTeamSprites = false;
        if (firstEventType === 'custom'){
            //console.log('-> event at position is custom, checking what comes next...');
            // If the cursor is literally on a event, only one event sprite matters right now
            let $customEvent = $(firstEvent.sprite);
            let dataLabel = $customEvent.attr('data-label');
            let dataEvent = $customEvent.attr('data-event');
            let eventInfo = _config.mapEventsIndex[dataEvent] || false;
            if (eventInfo && _worldCursor.moved){
                //console.log('-> found eventInfo for ' + dataEvent + ':', eventInfo);
                let eventFilter = eventInfo['filter'] || false;
                let eventAction = eventInfo['action'] || false;
                let eventData = eventInfo['data'] || false;
                let eventAllowed = eventFilter === 'any' ? true : false; // TODO: implement player and/or robot-specific filter logic
                //console.log('-> eventFilter =', eventFilter, '| eventAction =', eventAction, '| eventData =', eventData, '| eventAllowed =', eventAllowed);
                if (eventAllowed){
                    if (eventAction !== false){
                        //console.log('-> eventAction is "', eventAction, '" so defer it to triggerEffectFunction()');
                        triggerEffect = true;
                        readyTeamSprites = true;
                        triggerEffectFunction = function(){
                            //console.log('-> running triggerEffectFunction for eventAction "' + eventAction + '" with eventData:', eventData);
                            _self.triggerWorldEvent(eventAction, eventData, $customEvent);
                            };
                        } else {
                        //console.log('-> eventAction is false, so prepare dropdown instead');
                        showDropdown = true;
                        if (!dataLabel){ dataLabel = 'Event Options'; }
                        dropdownMarkup += '<strong class="label">' + dataLabel + '</strong>';
                        dropdownButtons += '<a class="button big-button" data-action="trigger-event" data-event="'+dataEvent+'"><span>Trigger Event</span></a>';
                        dropdownButtons += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                        showDropdownType = 'event';
                        }
                    } else {
                    //console.log('-> event not allowed based on filter "' + eventFilter + '"');
                    }
                } else if (eventInfo && !_worldCursor.moved) {
                //console.log('-> eventInfo found for ' + dataEvent + ', but cursor not moved yet, skipping');
                } else {
                //console.log('-> no eventInfo found for ' + dataEvent + ', skipping');
                }
            }
        else if (firstEventType === 'portal'){
            //console.log('-> event at position is a portal, preparing dropdown');
            // If the cursor is literally on a portal, only one event sprite matters right now
            let $portalEvent = $(firstEvent.sprite);
            let dataLabel = $portalEvent.attr('data-label');
            let dataPortal = $portalEvent.attr('data-portal');
            if (dataPortal && dataPortal.indexOf('goto__') !== -1){
                let portalInfo = _config.mapPortalsIndex[dataPortal] || false;
                //console.log('-> found portalInfo for ' + dataPortal + ':', portalInfo);
                showDropdown = true;
                if (!dataLabel){ dataLabel = 'Portal Options'; }
                dropdownMarkup += '<strong class="label">' + dataLabel + '</strong>';
                if (dataPortal.indexOf('goto__') !== -1){ dropdownButtons += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span>Use Teleport</span></a>'; }
                else if (dataPortal === 'exit'){ dropdownButtons += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span>Return Home</span></a>'; }
                dropdownButtons += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showDropdownType = 'portal';
                zoomTimeoutDuration = 500; // if we show a portal dropdown, we want to zoom in quickly
                // Automatically redirect to this portal if player has moved at least once
                if (_worldCursor.moved){
                    //console.log('-> entering portal with name ' + dataPortal + '!');
                    if (dataPortal === 'spawn'){
                        // TODO: SPAWN PORTAL - make the spawn actually go somewhere specific ?
                        console.warn('-> spawn portals not yet implemented yet');
                        } else if (dataPortal === 'exit'){
                        // TODO: EXIT PORTAL - make the exit actually go somewhere specific ?
                        autoRedirect = true;
                        showDropdown = false;
                        autoRedirectURL = 'prototype.php';
                        } else if (dataPortal.indexOf('goto__') !== -1){
                        // GOTO PORTAL - use the portal token as worldmap token for redirect
                        autoRedirect = true;
                        showDropdown = false;
                        let worldToken, mapToken;
                        let goToPath = dataPortal.replace(/^goto__/i, '').split('__');
                        if (goToPath[1]){ worldToken = goToPath[0]; mapToken = goToPath[1]; }
                        else { worldToken = _config.mapWorld; mapToken = goToPath[0]; }
                        autoRedirectURL = 'world.php?world=' + worldToken + '&map=' + mapToken;
                        if (portalInfo['dst']){ autoRedirectURL += '&position='+portalInfo['dst']; }
                        autoRedirectSound = 'bounce-sound';
                        readyTeamSprites = true;
                        }
                    } else {
                    //console.log('-> portal ' + dataPortal + ' disabled until cursor movement!');
                    showDropdown = false;
                    }
                }
            }
        else if (firstEventType === 'button'){
            //console.log('-> event at position is a button, preparing dropdown');
            // If the cursor is literally on a button, only one event sprite matters right now
            let $buttonEvent = $(firstEvent.sprite);
            let dataLabel = $buttonEvent.attr('data-label');
            let dataButton = $buttonEvent.attr('data-button');
            let dataColour = $buttonEvent.attr('data-colour');
            let dataState = $buttonEvent.attr('data-state');
            if (dataButton && dataState === 'up'){
                showDropdown = true;
                //var buttonName = (dataColour ? (dataColour[0].toUpperCase() + dataColour.slice(1) + ' ') : '') + 'Button';
                //if (!dataLabel){ dataLabel = 'Button Options'; }
                if (dataLabel){ dropdownMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                dropdownButtons += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="push-button" data-button="'+dataButton+'"><span>Push Button?</span></a>';
                dropdownButtons += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showDropdownType = 'button';
                zoomTimeoutDuration = 500; // if we show a button dropdown, we want to zoom in quickly
                }
            }
        else if (firstEventType === 'battle'){

            // Otherwise we can/should check all the posiitons for any battles to round-up and trigger
            let dataLabels = [], dataBattles = [];
            for (var i = 0; i < eventsAtPosition.length; i++){
                //console.log('-> parsing battleEvent from eventsAtPosition[i]', eventsAtPosition[i]);
                let battleEvent = eventsAtPosition[i];
                let dataPosition = battleEvent.position;
                var dataBattle = battleEvent.token;
                var dataLabel = battleEvent.label;
                if (dataBattle){
                    dataLabels.push([dataLabel, dataPosition]);
                    dataBattles.push([dataBattle, dataPosition]);
                    }
                }
            //console.log('-> dataLabels =', dataLabels);
            //console.log('-> dataBattles =', dataBattles);
            //console.log('check the direction the player is facing and sort the battle events accordingly');
            //console.log('-> cursorDirection =', cursorDirection);
            //console.log('-> cursorPosition =', cursorPosition);
            let standingAtPosition = (cursorPosition.split('-')).map(function(num){ return parseInt(num); });
            let lookingAtPosition = [standingAtPosition[0], standingAtPosition[1]];
            if (cursorDirection.indexOf('right') !== -1){ lookingAtPosition[0] = lookingAtPosition[0] + 1; }
            else if (cursorDirection.indexOf('left') !== -1){ lookingAtPosition[0] = lookingAtPosition[0] - 1; }
            if (cursorDirection.indexOf('down') !== -1){ lookingAtPosition[1] = lookingAtPosition[1] + 1; }
            else if (cursorDirection.indexOf('up') !== -1){ lookingAtPosition[1] = lookingAtPosition[1] - 1; }
            //standingAtPosition = standingAtPosition.join('-');
            //lookingAtPosition = lookingAtPosition.join('-');
            //console.log('-> standingAtPosition =', standingAtPosition);
            //console.log('-> lookingAtPosition =', lookingAtPosition);
            // populate an array with the positions around the user, clockwise, but make it start at the position they're looking at
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
            // rotate above so that whichever one matches lookingAtPosition is first but maintain order!!!
            do {
                let firstTile = tilesAroundPosition.shift();
                if (firstTile === lookingAtPosition){ tilesAroundPosition.push(firstTile); break; }
                else { tilesAroundPosition.push(firstTile); }
            } while (tilesAroundPosition[0] !== lookingAtPosition);
            //console.log('-> tilesAroundPosition =', tilesAroundPosition);
            // now, finally, we can sort the dataBattles by their index in the tilesAroundPosition array
            dataBattles.sort(function(a, b){
                let aPosition = a[1], bPosition = b[1];
                let aIndex = tilesAroundPosition.indexOf(aPosition);
                let bIndex = tilesAroundPosition.indexOf(bPosition);
                if (aIndex < bIndex){ return -1; } // a comes before b
                else if (aIndex > bIndex){ return 1; } // a comes after b
                else { return 0; } // a and b are equal
                });
            //console.log('-> dataBattles after sorting by position:', dataBattles.join('\n'));
            if (dataLabels.length && dataBattles.length){
                showDropdown = true;
                readyTeamSprites = true;
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
                if (playerActiveRobots >= 1){  dropdownButtons += '<a class="button big-button" data-action="start-battle" data-battle="'+dataBattlesJoined+'"><span>Start Battle</span></a>'; }
                else { dropdownButtons += '<a class="button big-button disabled" data-battle="'+dataBattlesJoined+'"><span>Start Battle</span></a>'; }
                dropdownButtons += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showDropdownType = 'battle';
                //showDropdownSound = 'background-spawn';
                showDropdownSound = 'mecha-taunt-sound' + (dataBattles.length > 1 ? '*'+dataBattles.length : '');
                zoomTimeoutDuration = 1500; // otherwise if this is a battle we wait a moment
                }
            }

        // If there's no dropdown to show, we can return early
        if (!showDropdown && !autoRedirect && !triggerEffect){ return; }

        // Define an inline function to put the team into their battle-ready poses
        let getTeamSpritesReady = function(){
            if (_self.worldIsBusy()){ return; }
            if (_worldCursor.position !== cursorPosition){ return; }

            // Add the shake class to the cursor so it hides behind the player
            $worldCursor.addClass('shake');

            // Zoom one or more of the team sprites (?)
            //$teamSprites.css('top', '+= 10px'); // move the team sprites up a little bit
            let goingUp = _worldCursor.direction.indexOf('up') !== -1 ? true : false;
            let goingDown = _worldCursor.direction.indexOf('down') !== -1 ? true : false;
            let goingLeft = _worldCursor.direction.indexOf('left') !== -1 ? true : false;
            let goingRight = _worldCursor.direction.indexOf('right') !== -1 ? true : false;
            let rushDistanceX = Math.ceil(_mapTileSize[0] / 4);
            let rushDistanceY = Math.ceil(_mapTileSize[1] / 4);
            let playerFrames = ['06', '01', '04'];
            let robotFrames = ['04', '08', '01', '06', '10', '00', '04', '01'];
            let $cursorSprite = $teamSprites.filter('.sprite.cursor');
            let $otherSprites = $teamSprites.filter('.sprite:not(.cursor)');
            let $playerSprites = $otherSprites.filter('.sprite.player');
            let $robotSprites = $otherSprites.filter('.sprite.robot');
            //console.log(('-> eventsAtPosition =', eventsAtPosition);
            //console.log(('-> goingRight =', goingRight, '| goingLeft =', goingLeft, '| goingUp =', goingUp, '| goingDown =', goingDown);
            //console.log(('-> rushDistanceX =', rushDistanceX, '| rushDistanceY =', rushDistanceY);
            $cursorSprite.attr('data-frame', '01');
            $otherSprites.each(function(index){
                let $sprite = $(this);
                let oldX = $sprite.prop('worldX') || parseInt($sprite.css('left')) || 0;
                let oldY = $sprite.prop('worldY') || parseInt($sprite.css('top')) || 0;
                let oldZ = $sprite.prop('worldZ') || parseInt($sprite.css('zIndex')) || 1;
                let newX = oldX + (goingRight ? rushDistanceX : goingLeft ? (-1 * rushDistanceX) : 0);
                let newY = oldY + (goingDown ? rushDistanceY : goingUp ? (-1 * rushDistanceY) : 0);
                let newZ = newY + 1;
                //console.log(('-> moving sprite', $sprite.attr('data-token'), 'from [', oldX, oldY, oldZ, '] to [', newX, newY, newZ, ']');
                $sprite.animate({left: newX + 'px', top: newY + 'px', zIndex: newZ }, teamRushDuration);
                });
            $playerSprites.each(function(index){
                let $sprite = $(this);
                if ($sprite.is('.disabled')){ return; }
                $sprite.attr('data-frame', playerFrames[index % playerFrames.length] || '00');
                });
            $robotSprites.each(function(index){
                let $sprite = $(this);
                if ($sprite.is('.disabled')){ return; }
                $sprite.attr('data-frame', robotFrames[index % robotFrames.length] || '00');
                });

            };

        // Define an inline function to redirect to the portal if needed
        let redirectToLocation = function(){
            //console.log('%c' + 'redirectToLocation()', 'color: cyan;');
            if (_self.worldIsBusy()){ return; }
            if (_worldCursor.position !== cursorPosition){ return; }
            $thisWorld.addClass('hidden');
            if (autoRedirectSound){
                _self.playSoundEffect(autoRedirectSound);
                }
            if (autoRedirectURL){
                _self.incZoomLevel();
                _self.saveWorldState(function(){
                    if (_self.worldIsBusy()){ return; }
                    if (_worldCursor.position !== cursorPosition){ return; }
                    else { _self.resetZoomLevel(); }
                    _self.incZoomLevel();
                    window.location.href = autoRedirectURL;
                    _self.incZoomLevel();
                    });
                }
            return true;
            };

        // Define an inline function to zoom and show the dropdown which we'll call after a timeout
        let zoomAndShowDropdown = function(){
            //console.log('%c' + 'zoomAndShowDropdown()', 'color: cyan;');
            if (_self.worldIsBusy()){ return; }
            if (_worldCursor.position !== cursorPosition){ return; }

            // Elevate the event sprite(s) to the zoom layer and add a zoom class to it so it's more visible
            let cursorPositionXY = cursorPosition.split('-');
            for (var i = 0; i < eventsAtPosition.length; i++){
                let eventData = eventsAtPosition[i];
                let $eventSprite = $(eventData.sprite);
                let eventPosition = $eventSprite.attr('data-pos');
                if ($eventSprite.hasClass('tile')){ continue; } // skip tiles
                let isRobot = $eventSprite.hasClass('robot');
                let isMecha = $eventSprite.hasClass('vs-mecha');
                let isMaster = $eventSprite.hasClass('vs-master');
                let isBoss = $eventSprite.hasClass('vs-boss');
                //console.log('-> eventsAtPosition['+i+'] / isRobot = ', isRobot);
                let dataSize = $eventSprite.attr('data-size') || 40;
                let $eventLayer = $eventSprite.closest('.layer');
                let eventLayer = $eventLayer.attr('data-layer');
                //let relativePosition = [eventPositionXY[0] - cursorPositionXY[0], eventPositionXY[1] - cursorPositionXY[1]];
                let relativePosition = _self.getPositionRelative(cursorPosition, eventPosition);
                let newDirection = false;
                if (relativePosition[0] < 0){ newDirection = 'right'; }
                else if (relativePosition[0] > 0){ newDirection = 'left'; }
                else if (cursorDirection.indexOf('right') !== -1){ newDirection = 'left'; }
                else if (cursorDirection.indexOf('left') !== -1){ newDirection = 'right'; }
                //$eventSprite.attr('data-layer', eventLayer);
                //console.log('-> moving event sprite to zoom layer', eventLayer, 'from events layer');
                setTimeout(function(){
                    $eventSprite.addClass('zoom');
                    $eventLayer.addClass('has-zoom');
                    if (newDirection){ $eventSprite.attr('data-dir', newDirection); }
                    if (isRobot){
                        if (isMecha){ $eventSprite.attr('data-frame', '08'); }
                        else if (isMaster){ $eventSprite.attr('data-frame', '01'); }
                        else if (isBoss){ $eventSprite.attr('data-frame', '06'); }
                        }
                    }, 100);
                }

            // Move the action dropdown to the correct position, add the markup, and show it
            if (dropdownMarkup.length){
                $actionDropdown.css({
                    left: ((thisNewCol - 1) * _mapTileSize[0] + _mapTileSizeOffset[0]) + 'px',
                    top: ((thisNewRow - 1) * _mapTileSize[1] + _mapTileSizeOffset[1]) + 'px',
                    }).attr('data-dir', _worldCursor.direction).attr('data-type', showDropdownType).attr('data-align', 'center');
                $actionDropdownWrapper.html(dropdownMarkup);
                }

            // Add the buttons to the sidebar area so that they are out-of-the-way
            $sideButtonsWrapper.html(dropdownButtons);

            // Define the function for dismissing the dropdown and side buttons
            let dismissDropdown = function(playSound){
                //console.log('-> dismissing action dropdown!');
                playSound = typeof playSound === 'boolean' ? playSound : true;
                if (playSound){ _self.playSoundEffect('back-click'); }
                $actionDropdown.removeClass('active');
                $actionDropdownWrapper.empty();
                $sideButtons.removeClass('active');
                $sideButtonsWrapper.empty();
                $worldCursor.removeClass('shake');
                $spritesLayer.removeClass('has-zoom');
                $('.sprite.zoom', $canvasMap).removeClass('zoom');
                $('.sprite[data-frame]:not(.disabled)', $canvasMap).attr('data-frame', '00');
                };

            // Define the event to run when clicking one of these new action buttons
            let onActionButtonClick = function(e){
                //console.log('%c' + 'Action button clicked!', 'color: cyan;');
                e.preventDefault();
                let $button = $(this);
                let action = $button.attr('data-action') || false;
                let isBattle = action.indexOf('battle') !== -1;
                let isPortal = action.indexOf('portal') !== -1;
                let isButton = action.indexOf('button') !== -1;
                let isDismiss = action === 'dismiss';
                if (!isDismiss){ $button.addClass('clicked'); }
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
                        _worldPlayerRobotsKeys = Object.keys(_worldPlayerRobots); // refresh in case changed
                        //console.log('_worldPlayerRobots = ', _worldPlayerRobots);
                        //console.log('_worldPlayerRobotsKeys = ', _worldPlayerRobotsKeys);
                        let activeRobots = [];
                        for (let i = 0; i < _worldPlayerRobotsKeys.length; i++){
                            let token = _worldPlayerRobotsKeys[i];
                            let info = _worldPlayerRobots[token] || false;
                            if (!info || info.disabled){ continue; }
                            activeRobots.push(token);
                            }
                        //console.log('activeRobots = ', activeRobots);
                        _self.playSoundEffect('lets-go-robots');
                        let battleVars = [];
                        battleVars.push('wap=false'); // i hate this
                        battleVars.push('flag_skip_fadein=true'); // its just faster
                        battleVars.push('this_user_id=' + _userId);
                        battleVars.push('this_player_id=' + _playerId);
                        battleVars.push('this_player_token=' + _playerToken);
                        battleVars.push('this_player_robots=' + activeRobots.join(','));
                        battleVars.push('this_battle_token=' + battleId);
                        let battleHref = 'battle.php?' + battleVars.join('&');
                        $thisWorld.addClass('hidden').addClass('busy');
                        _self.saveWorldState(function(){ window.location.href = battleHref; });
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
                        _self.playSoundEffect('bounce-sound');
                        let portalHref = false;
                        if (portalName === 'spawn'){
                            portalHref = 'prototype.php'; // TODO: make the spawn actually go somewhere specific
                            } else if (portalName === 'exit'){
                            portalHref = 'prototype.php'; // TOPO: make the exit actually go somewhere specific
                            } else if (portalName.indexOf('goto__') !== -1){
                            let worldToken, mapToken;
                            let goToPath = portalName.replace(/^goto__/i, '').split('__');
                            if (goToPath[1]){ worldToken = goToPath[0]; mapToken = goToPath[1]; }
                            else { worldToken = _config.mapWorld; mapToken = goToPath[0]; }
                            portalHref = 'world.php?world=' + worldToken + '&map=' + mapToken;
                            }
                        if (portalHref){
                            $thisWorld.addClass('hidden').addClass('busy');
                            _self.incZoomLevel();
                            _self.saveWorldState(function(){
                                _self.incZoomLevel();
                                window.location.href = portalHref;
                                _self.incZoomLevel();
                                });
                            }
                        }
                    }
                else if (isButton){
                    //console.log('-> world-button clicked with action:', action);
                    let buttonsIndex = _config.mapButtonsIndex;
                    let buttonStates = _world.buttons;
                    let buttonName = $button.attr('data-button') || false;
                    let buttonInfo = buttonName && (buttonsIndex && buttonsIndex[buttonName]) ? buttonsIndex[buttonName] : false;
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('> .sprite', $eventSprite) : false;
                    //console.log('-> buttonName =', buttonName);
                    //console.log('-> buttonInfo =', buttonInfo);
                    //console.log('-> $eventSprite =', $eventSprite);
                    //console.log('-> $innerSprite =', $innerSprite);
                    if (!buttonName || !buttonInfo){ console.error('-> button name or info not found, cannot push button!'); return false; }
                    if (!$eventSprite || !$eventSprite.length){ console.error('-> event sprite not found, cannot push button!'); return false; }
                    if (!$innerSprite || !$innerSprite.length){ console.error('-> inner sprite not found, cannot push button!'); return false; }
                    // dismiss the dropdown and side buttons first
                    dismissDropdown(false);
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

                        // ...

                        // event action SET-GROUP-TERRAIN for buttons, switches, etc. to use
                        if (buttonAction === 'set-group-terrain'){
                            //console.log('-> setting group terrain for button', buttonName);
                            let groupName = buttonData[0] || false;
                            let terrainName = buttonData[1] || false;
                            //console.log('-> groupName =', groupName, '\n', '-> terrainName =', terrainName);
                            if (!groupName){ console.error('-> groupName not provided, cannot set group terrain!'); return false; }
                            if (!terrainName){ console.error('-> terrainName not provided, cannot set group terrain!'); return false; }
                            let mapTilesIndex = _config.mapTilesIndex;
                            let layerTilesIndex = _world.layerTilesIndex;
                            let terrainTilesIndex = layerTilesIndex['terrain'] || false;
                            let terrainSpriteData = mapTilesIndex[terrainName] || false;
                            let terrainIsVoid = terrainName.indexOf('void') !== -1 ? true : false;
                            let terrainIsWater = terrainName.indexOf('water') !== -1 ? true : false;
                            let terrainIsWalkable = !terrainIsVoid && !terrainIsWater ? true : false;
                            //console.log('-> layerTilesIndex =', layerTilesIndex);
                            //console.log('-> terrainTilesIndex =', terrainTilesIndex);
                            //console.log('-> terrainSpriteData =', terrainSpriteData);
                            //console.log('-> terrainIsWalkable =', terrainIsWalkable);
                            if (!layerTilesIndex || !terrainTilesIndex){ console.error('-> layerTilesIndex or terrainTilesIndex not found, cannot set terrain!'); return false; }
                            if (!terrainSpriteData){ console.error('-> terrainSpriteData not found, cannot set terrain!'); return false; }
                            let groupsIndex = _config.mapGroupsIndex;
                            let groupTiles = groupsIndex[groupName] || false;
                            //console.log('-> groupsIndex =', groupsIndex);
                            //console.log('-> groupTiles =', groupTiles);
                            if (!groupsIndex || !groupTiles){ console.error('-> groupsIndex not found, cannot set terrain!'); return false; }
                            for (let i = 0; i < groupTiles.length; i++){
                                let tileKey = groupTiles[i];
                                let tileData = terrainTilesIndex[tileKey] || false;
                                if (!tileData){ console.error('-> tile data not found for tile', tileKey, ', cannot set terrain!'); continue; }
                                //console.log('-> setting terrain for tile', tileKey, 'to', terrainName, 'w/ tileData:', tileData);
                                tileData.sprite[1] = terrainName;
                                tileData.sprite[2] = [terrainSpriteData[0], terrainSpriteData[1]];
                                tileData.walkable = terrainIsWalkable;
                                tileData.effects.grid = terrainIsWalkable;
                                tileData.dirty = true;
                                terrainTilesIndex[tileKey] = tileData; // sync the tile data back to the index
                                }
                            layerTilesIndex['terrain'] = terrainTilesIndex; // sync the layer tiles index with the new terrain tiles index
                            _world.layerTilesIndex = layerTilesIndex; // sync the world state with the new layer tiles index
                            _self.refreshCanvasTiles('terrain'); // refresh the canvas tiles
                            _self.calculateWalkableMapTiles(true); // recalculate walkable tiles
                            _self.refreshMapPositionEvents(); // refresh the map position events
                            _self.saveWorldState();
                            }

                        // ...

                        })(buttonInfo);
                    // .......
                    // ...

                    }
                else if (isDismiss){
                    //console.log('-> dismissing action dropdown!');
                    dismissDropdown(true);
                    }
                else {
                    // no compatible action found, do nothing
                    return false;
                    }
                };

            // Define the event to run when hovering one of these new action buttons
            let onActionButtonHover = function(e){
                //e.preventDefault();
                _self.playSoundEffect('icon-hover');
                };

            // Bind click events to the newly created action buttons in the dropdown
            $('.button[data-action]', $sideButtons).bind('click', onActionButtonClick);
            $('.button[data-action]', $sideButtons).bind('mouseenter', onActionButtonHover);

            // Bind an event to the side-button area itself for showing/hiding action labels on hover
            $sideButtons.bind('mouseenter', function(e){ $actionDropdown.addClass('hover'); });
            $sideButtons.bind('mouseleave', function(e){ $actionDropdown.removeClass('hover'); });

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
                // If sound effect(s) have been defined play now
                if (showDropdownSound){
                    let sound = showDropdownSound, repeat = 1, delay = 0;
                    if (sound.indexOf('*') !== -1){ var parts = sound.split('*'); sound = parts[0]; repeat = parseInt(parts[1]); }
                    for (var i = 0; i < repeat; i++){
                        if (!delay){ _self.playSoundEffect(sound); delay += 50; }
                        else { setTimeout(function(){ _self.playSoundEffect(sound); }, delay); delay *= 2; }
                        }
                    }
                }, 200);

            };

        // Make the cursor shake so it trembles a bit before the encounter
        if (_selfRef.teamSpritesTimeout){ clearTimeout(_selfRef.teamSpritesTimeout); }
        if (readyTeamSprites){ _selfRef.teamSpritesTimeout = setTimeout(getTeamSpritesReady, teamReadyDuration); }

        // If an effect is being triggered, run it and then exit here
        if (triggerEffect){
            //console.log('%c' + 'triggerEffectFunction()', 'color: cyan;');
            if (_selfRef.zoomEffectTimeout){ clearTimeout(_selfRef.zoomEffectTimeout); }
            _selfRef.zoomEffectTimeout = setTimeout(triggerEffectFunction, zoomTimeoutDuration);
            return true;
            }

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
        //console.log('%c' + 'mmrpgWorldMap.getEventsAtPosition(searchPosition:' + searchPosition + ', searchRadius:' + searchRadius + ')', 'color: magenta;');
        if (!searchPosition || (typeof searchPosition !== 'string' && !Array.isArray(searchPosition))){ console.error('getEventsAtPosition() missing or invalid searchPosition!'); return false; }
        searchPosition = typeof searchPosition !== 'string' ? searchPosition.join('-') : searchPosition; // join if provided as array
        searchRadius = typeof searchRadius === 'number' ? searchRadius : 1; // default to one if not provided
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let $canvasMap = _elements.map;
        let eventsAtPosition = [];
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
        let eventKinds = ['event', 'portal', 'button', 'battle'];
        for (let e = 0; e < eventKinds.length; e++){
            let eventKind = eventKinds[e];
            //console.log('checking for ' + eventKind+'s at: ' + positionsToCheck.join(', '));
            // ie: mapKindSymbols
            let symbolsKey = 'map' + (eventKind[0].toUpperCase() + eventKind.slice(1)) + 'Symbols';
            let eventSymbols = _config.hasOwnProperty(symbolsKey) ? _config[symbolsKey] : false;
            let eventSymbolKeys = eventSymbols ? Object.keys(eventSymbols) : [];
            //console.log('-> symbolsKey =', symbolsKey);
            //console.log('-> eventSymbols =', eventSymbols);
            //console.log('-> eventSymbolKeys =', eventSymbolKeys);
            //console.log('-> ' + symbolsKey + ' =', eventSymbols);
            //console.log('-> ' + symbolsKey + ' =', eventSymbolKeys);
            if (!eventSymbolKeys.length){ continue; }
            for (let i = 0; i < positionsToCheck.length; i++){
                let eventPosition = positionsToCheck[i];
                let eventPositionXY = eventPosition.split('-');
                //console.log('-> checking ' + symbolsKey + ' for ' + eventPosition);
                if (!eventSymbols[eventPosition]){ continue; } // skip if no event symbols at this position
                let eventToken = eventSymbols[eventPosition];
                let $eventSprite = $('.sprite[data-' + eventKind + '="'+eventToken+'"]', $canvasMap);
                let eventLabel = $eventSprite.length ? $eventSprite.attr('data-label') : '';
                if ($eventSprite && $eventSprite.length){ $eventSprite = $eventSprite.first().get(0); }
                let eventKind2 = eventKind === 'event' ? 'custom' : eventKind;
                let eventAtPosition = {kind: eventKind2, position: eventPosition, token: eventToken, sprite: $eventSprite, label: eventLabel};
                //console.log('%c' + '--> found valid '+ eventKind + ' event at position ' + eventPosition, 'color: lime;');
                //console.log('----> eventToken =', eventToken);
                //console.log('----> eventAtPosition =', eventAtPosition);
                // skip portals unless it's the exact position
                let eventIsCustom = eventKind === 'event';
                let eventIsPortal = eventKind === 'portal';
                if (eventIsCustom && eventPosition !== searchPosition){ continue; } // skip custom unless it's the exact position
                if (eventIsPortal && eventPosition !== searchPosition){ continue; } // skip portals unless it's the exact position
                // otherwise we are fine to add to the events array
                //console.log('----> adding ' + eventKind + ' at ' + eventPosition + ' to eventsAtPosition array');
                eventsAtPosition.push(eventAtPosition);
                }
            }
        // Return the found events
        //console.log('-> Found ' + eventsAtPosition.length + ' events at position ' + searchPosition + ':', eventsAtPosition);
        return eventsAtPosition;
        }

    // Quick function that, given a column and row returns any event sprites on or around that position on the map
    getEventSpritesAtPosition(searchPosition, searchRadius){
        //console.log('%c' + 'mmrpgWorldMap.getEventSpritesAtPosition(searchPosition:' + searchPosition + ', searchRadius:' + searchRadius + ')', 'color: magenta;');
        if (!searchPosition || (typeof searchPosition !== 'string' && !Array.isArray(searchPosition))){ console.error('getEventSpritesAtPosition() missing or invalid searchPosition!'); return false; }
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
        let $eventSpritesAtPosition = [];
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
        let eventSpriteKinds = ['event', 'portal', 'button', 'battle'];
        for (let i = 0; i < positionsToCheck.length; i++){
            let checkPosition = positionsToCheck[i];
            let eventPosition = checkPosition.split('-');
            let $spritesAtPosition = $('.sprite[data-sprite][data-col="' + eventPosition[0] + '"][data-row="' + eventPosition[1] + '"]', $canvasMap);
            //console.log('-> checking position', checkPosition, 'for sprites...');
            //console.log('-> $spritesAtPosition = ', $spritesAtPosition);
            if (!$spritesAtPosition || !$spritesAtPosition.length){ continue; }
            $spritesAtPosition.each(function(){
                let $spriteAtPosition = $(this);
                let spriteKind = $spriteAtPosition.attr('data-sprite') || false, baseSpriteKind = spriteKind.indexOf('-') !== -1 ? spriteKind.split('-')[0] : spriteKind;
                //console.log('-> checking spriteKind =', spriteKind);
                //console.log('-> checking baseSpriteKind =', baseSpriteKind);
                // skip if not an event sprite
                if (!spriteKind || !baseSpriteKind || eventSpriteKinds.indexOf(baseSpriteKind) === -1){
                    //console.log('getEventSpritesAtPosition() skipping position', checkPosition, 'because it is not an event sprite:', $spriteAtPosition);
                    return;
                    }
                // collect sprite ref as we know its an event now
                let $eventAtPosition = $spriteAtPosition;
                // skip portals unless it's the exact position
                let eventIsCustom = spriteKind === 'event';
                let eventIsPortal = spriteKind === 'portal';
                if (eventIsCustom && checkPosition !== searchPosition){ return; } // skip custom unless it's the exact position
                if (eventIsPortal && checkPosition !== searchPosition){ return; } // skip portals unless it's the exact position
                // otherwise we are fine to add to the events array
                //console.log('%c' + '-> found valid '+ spriteKind + ' event at position ' + checkPosition, 'color: lime;');
                $eventSpritesAtPosition.push($eventAtPosition);
                });
            }
        // Return the found events
        //console.log('-> Found ' + $eventSpritesAtPosition.length + ' events at position ' + searchPosition + ':', $eventSpritesAtPosition);
        return $eventSpritesAtPosition;
        }

    // Quick function for starting an interval timer that animations on-screen encounter sprites in a while
    startIdleAnimation(){
        //console.log('%c' + 'mmrpgWorldMap.startIdleAnimation()', 'color: magenta;');
        let _self = this;
        let _selfRef = _self.startIdleAnimation;
        let _elements = _self.elements;
        let $canvasMap = _elements.map;
        let $vsMechas = $('.sprite.vs-mecha', $canvasMap);
        let $vsBosses = $('.sprite.vs-boss', $canvasMap);
        $vsMechas.addClass('march');
        $vsBosses.addClass('march');
        if (_selfRef._interval){ clearInterval(_selfRef._interval); }
        _selfRef._interval = setInterval(function(){
            $vsMechas = $('.sprite.vs-mecha:not(.zoom)', $canvasMap);
            $vsMechas.each(function(){
                let $mecha = $(this);
                if ($mecha.data('cooldown') && $mecha.data('cooldown') > 0){
                    $mecha.data('cooldown', $mecha.data('cooldown') - 1);
                    return true;
                    }
                let curDir = $mecha.attr('data-dir') || 'right';
                let newDir = curDir === 'right' ? 'left' : 'right';
                let curFrame = $mecha.attr('data-frame') || '00';
                let newFrame = curFrame !== '01' ? '01' : '00';
                let hasMarch = $mecha.hasClass('march');
                let changed = false;
                let changeDirection = Math.random() > 0.5 ? true : false;
                let changeFrame = Math.random() > 0.9 ? true : false;
                //let changeMarch = Math.random() > 0.5 ? true : false;
                if (changeDirection){ $mecha.attr('data-dir', newDir); changed = true; }
                if (changeFrame){ $mecha.attr('data-frame', newFrame); changed = true; }
                //if (changeMarch){ $mecha.addClass('march'); changed = true; }
                if (!changed){ return true; }
                let randCooldown = 4 + Math.ceil(Math.random() * 6);
                $mecha.data('cooldown', randCooldown);
                });
            $vsBosses = $('.sprite.vs-boss:not(.zoom)', $canvasMap);
            $vsBosses.each(function(){
                let $boss = $(this);
                if ($boss.data('cooldown') && $boss.data('cooldown') > 0){
                    $boss.data('cooldown', $boss.data('cooldown') - 1);
                    return true;
                    }
                let curDir = $boss.attr('data-dir') || 'right';
                let newDir = curDir === 'right' ? 'left' : 'right';
                let curFrame = $boss.attr('data-frame') || '00';
                let newFrame = curFrame !== '01' ? '01' : '00';
                let hasMarch = $boss.hasClass('march');
                let changed = false;
                let changeDirection = Math.random() > 0.5 ? true : false;
                let changeFrame = Math.random() > 0.5 ? true : false;
                if (changeDirection){ $boss.attr('data-dir', newDir); changed = true; }
                if (changeFrame){ $boss.attr('data-frame', newFrame); changed = true; }
                if (!changed){ return true; }
                let randCooldown = 4 + Math.ceil(Math.random() * 6);
                $boss.data('cooldown', randCooldown);
                });
            }, 500);
        // Return true on success
        return true;
    }

    // Quick function for playing a sound effect (if available)
    playSoundEffect(soundName, options){
        //console.log('%c' + 'mmrpgWorldMap.playSoundEffect(' + soundName + ')', 'color: green;');
        if (!soundName || typeof soundName !== 'string' || !soundName.length){ console.error('playSoundEffect() missing required soundName!'); return false; }
        if (typeof options !== 'object' || !options){ options = {}; }
        let _self = this;
        let _selfReference = _self.playSoundEffect;
        let _config = _self.config;
        let _world = _self.state;
        let _elements = _self.elements;
        let mmrpgPlaySoundEffect = _selfReference.mmrpgPlaySoundEffect;
        if (!mmrpgPlaySoundEffect){
            mmrpgPlaySoundEffect = function(soundName, options){
                if (this instanceof jQuery || this instanceof Element){
                    if ($(this).data('silentClick')){ return; }
                    if ($(this).is('.disabled')){ return; }
                    if ($(this).is('.button_disabled')){ return; }
                    }
                if (typeof top.mmrpg_play_sound_effect !== 'undefined'){
                    top.mmrpg_play_sound_effect(soundName, options);
                    } else {
                    console.warn('mmrpgWorldMap.playSoundEffect() unable to play sound effect "' + soundName + '" because top.mmrpg_play_sound_effect is not defined!');
                    }
                };
                _selfReference.mmrpgPlaySoundEffect = mmrpgPlaySoundEffect;
            }
        return mmrpgPlaySoundEffect.call(_selfReference, soundName, options);
        }

    // Quick function for loading a music track (if available)
    // abstraction for top.mmrpg_music_load(newTrack, resartTrack, playOnce, onendFunction)
    // much like above was abstraction for top.mmrpg_play_sound_effect(effectName, effectConfig, isMenuSound)
    loadMusicTrack(newTrack, restartTrack, playOnce, onendFunction){
        //console.log('%c' + 'mmrpgWorldMap.loadMusicTrack(' + newTrack + ')', 'color: green;');
        if (!newTrack || typeof newTrack !== 'string' || !newTrack.length){ console.error('loadMusicTrack() missing required newTrack!'); return false; }
        let _self = this;
        let _selfReference = _self.loadMusicTrack;
        let mmrpgLoadMusicTrack = _selfReference.mmrpgLoadMusicTrack;
        if (!mmrpgLoadMusicTrack){
            mmrpgLoadMusicTrack = function(newTrack, restartTrack, playOnce, onendFunction){
                if (typeof top.mmrpg_music_load !== 'undefined'){
                    top.mmrpg_music_load(newTrack, restartTrack, playOnce, onendFunction);
                    } else {
                    console.warn('mmrpgWorldMap.loadMusicTrack() unable to load music track "' + newTrack + '" because top.mmrpg_music_load is not defined!');
                    }
                };
                _selfReference.mmrpgLoadMusicTrack = mmrpgLoadMusicTrack;
            }
        return mmrpgLoadMusicTrack.call(_selfReference, newTrack, restartTrack, playOnce, onendFunction);
        }

    // Quick function for sending a snapshot of persistent world values back to the server for saving
    saveWorldState(callback, delay){
        //console.log('%c' + 'mmrpgWorldMap.saveWorldState(callback, delay)', 'color: magenta;');
        delay = (typeof delay === 'number' ? delay : 1) * 1000; // default to one second if not provided/invalid
        let _self = this;
        let _selfRef = _self.saveWorldState;
        if (_selfRef._scheduled){ clearTimeout(_selfRef._scheduled); }
        //console.log('-> scheduling world state save in ' + delay + 'ms');
        _selfRef._scheduled = setTimeout(function(){
            if (_selfRef._busy){
                // if busy, try again in one second
                //console.log('%c' + '--> save in progress, calling saveWorldState() again in ' + delay + 'ms ...', 'color: orange;');
                _self.saveWorldState(callback, delay);
                } else {
                // not busy so we can save for real now
                _self.saveWorldStateForReal(callback);
                }
            }, delay);
        return;
        }
    saveWorldStateForReal(callback){
        //console.log('%c' + 'mmrpgWorldMap.saveWorldStateForReal(callback)', 'color: magenta;');
        callback = typeof callback === 'function' ? callback : false; // default to no callback if not provided
        let _self = this;
        let _selfRef = _self.saveWorldState;
        let _config = _self.config;
        let _userId = _config.userId;
        let _world = _self.state;
        //let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldButtons = _world.buttons;
        let _worldSwitches = _world.switches;
        let lastPlayer = _worldPlayer.token;
        let lastPlayerRobots = _worldPlayer.robots;
        let lastPlayerWorld = _config.mapWorld;
        let lastPlayerWorldMap = _config.mapWorld + '__' + _config.mapToken;
        let lastPlayerPosition = _worldPlayer.position;
        let lastPlayerDirection = _worldPlayer.direction;
        let lastWorldButtons = {}; lastWorldButtons[lastPlayerWorldMap] = _worldButtons;
        let lastWorldSwitches = {}; lastWorldSwitches[lastPlayerWorldMap] = _worldSwitches;
        let worldData = {lastPlayer, lastPlayerRobots, lastPlayerWorld, lastPlayerWorldMap, lastPlayerPosition, lastPlayerDirection, lastWorldButtons, lastWorldSwitches};
        //console.log('%c' + 'Saving World State ...', 'color: cyan;');
        //console.log('w/ worldData:', worldData);
        _selfRef._busy = true;
        $.ajax({
            url: 'world.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'save', world_data: worldData },
            success: function(response){
                //console.log('---> save_world.php response:', response);
                //console.log('%c' + '... World State Saved!', 'color: green;');
                _selfRef._busy = false;
                if (callback){ return callback.call(_self, 'success', {response}); }
                else { return true; }
                },
            error: function(xhr, status, error){
                //console.error('saveWorldState() failed to save world state!', status, error);
                //console.log('%c' + '... World State Not Saved!', 'color: red;');
                _selfRef._busy = false;
                if (callback){ return callback.call(_self, 'error', {xhr, status, error}); }
                else { return false; }
                }
            });
        return;
        }

    // Quick function for intentionally waiting for a given amount of time (in milliseconds)
    wait(ms){
        return new Promise(resolve => setTimeout(resolve, ms));
        }

    // Quick function for triggering a world event (lol) and any effects that may occur
    triggerWorldEvent(eventAction, eventData, $eventSprite){
        //console.log('%c' + 'mmrpgWorldMap.triggerWorldEvent(' + eventAction + ', ' + eventData + ', $eventSprite:' + typeof $eventSprite + ')', 'color: magenta;');
        if (!eventAction || typeof eventAction !== 'string' || !eventAction.length){ console.error('triggerWorldEvent() missing required eventAction!'); return false; }
        // Collect references to world objects
        let _self = this;
        let _selfRef = _self.triggerWorldEvent;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let $canvasMap = _elements.map;
        let $teamSprites = _elements.teamSprites;
        // If the cursor hasn't moved yet, we shouldn't be processing anything
        if (!_worldCursor.moved){
            console.warn('triggerWorldEvent() called but cursor has not moved yet!');
            return false;
            }
        // Check to see if the event is valid
        let allowedActions = ['trigger-effects'];
        if (allowedActions.indexOf(eventAction) === -1){
            console.error('triggerWorldEvent() invalid eventAction provided: ' + eventAction);
            return false;
            }
        // Define an inline function for processing the different event actions possible
        let processEventAction = function(eventAction, onComplete, afterDelay){
            if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
            if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
            // Process the event action based on it's token
            if (eventAction === 'trigger-effects'){
                //console.log('-> triggering effects for event with data:', eventData);
                _self.playSoundEffect('use-recovery-item');
                let eventEffects = Object.values(eventData);
                for (let i = 0; i < eventEffects.length; i++){
                    let effect = eventEffects[i];
                    //console.log('-> effect =', effect);
                    if (!effect){ continue; }
                    // If this is a RESTORE TEAM ENERGY effect, let's process that now
                    else if (effect === 'restore-team-energy'){
                        //console.log('%c' + '-> restoring team energy via event panel', 'color: lime;');
                        let _playerRobots = _config.playerRobots || [];
                        for (let j = 0; j < _playerRobots.length; j++){ _self.restoreRobotEnergy(_playerRobots[j], true); }
                        //_self.playSoundEffect('recovery-energy');
                        }
                    // If this is a RESTORE TEAM WEAPONS effect, let's process that now
                    else if (effect === 'restore-team-weapons'){
                        //console.log('%c' + '-> restoring team weapons for event panel', 'color: cyan;');
                        let _playerRobots = _config.playerRobots || [];
                        for (let j = 0; j < _playerRobots.length; j++){ _self.restoreRobotWeapons(_playerRobots[j], true); }
                        //_self.playSoundEffect('recovery-weapons');
                        }
                    }
                }
            // If an onComplete function was provided, call it now (with delay if requested)
            if (onComplete){
                if (!afterDelay){ onComplete.call(_self, eventAction, eventData, $eventSprite); }
                else { setTimeout(function(){ onComplete.call(_self, eventAction, eventData, $eventSprite); }, afterDelay); }
                }
            };
        // Now process the event given the action and data provided after some visual fluff
        let delayTime = 1000;
        _self.incZoomLevel();
        setTimeout(function(){
            processEventAction(eventAction, function(){
                _self.resetZoomLevel();
                $teamSprites.removeClass('shake');
                $teamSprites.filter(':not(.disabled)').attr('data-frame', '00');
                }, delayTime);
            }, delayTime);
        // Return true on success
        return true;
        }

    // Quick function for getting a rating token given a percent value
    getRatingToken(percent){
        //console.log('%c' + 'mmrpgWorldMap.getRatingToken(' + percent + ')', 'color: magenta;');
        if (typeof percent !== 'number' || isNaN(percent) || percent < 0 || percent > 100){ console.error('getRatingToken() missing or invalid percent value!'); return false; }
        if (percent === 100){ return 'full'; }
        else if (percent >= 50){ return 'high'; }
        else if (percent >= 20){ return 'med'; }
        else if (percent >= 1){ return 'low'; }
        else { return 'no'; }
        }

    // Quick function for getting a robot energy frame given a rating token
    getRobotEnergyFrame(rating){
        //console.log('%c' + 'mmrpgWorldMap.getRobotEnergyFrame(' + rating + ')', 'color: magenta;');
        if (!rating || typeof rating !== 'string' || !rating.length){ console.error('getRobotEnergyFrame() missing required rating!'); return false; }
        if (rating === 'full'){ return '10'; } // base2
        else if (rating === 'high'){ return '01'; } // taunt
        else if (rating === 'med'){ return '00'; } // base
        else if (rating === 'low'){ return '08'; } // defend
        else { return '03'; } // defeat
        }

    // Quick function for restoring a robot's energy (if available) by a specific amount (or all if === true)
    restoreRobotEnergy(robotString, restoreAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.restoreRobotEnergy(' + robotString + ', ' + restoreAmount + ')', 'color: magenta;');
        if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('restoreRobotEnergy() missing required robotString!'); return false; }
        if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
        // If restoreAmount is true, restore all energy, otherwise restore the amount provided
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
        if (!robotInfo){ console.error('restoreRobotEnergy() could not find robot info for robot ' + robotString + '!'); return false; }
        //console.log('-> robotId =', robotId);
        //console.log('-> robotToken =', robotToken);
        //console.log('-> robotInfo =', robotInfo);
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
        //console.log('-> currentEnergy =', currentEnergy);
        //console.log('-> maxEnergy =', maxEnergy);
        //console.log('-> restoreAmount =', restoreAmount);
        // If restoreAmount is true, restore all energy, otherwise restore the amount provided
        let newEnergy = 0;
        if (restoreAmount === true){ newEnergy = maxEnergy; }
        else if (typeof restoreAmount === 'number' && restoreAmount > 0){ newEnergy = Math.min(currentEnergy + restoreAmount, maxEnergy); }
        //console.log('-> newEnergy =', newEnergy);
        // If the new and old energy values are the same, do nothing
        if (newEnergy === currentEnergy){
            //console.log('restoreRobotEnergy() called but energy values are the same, nothing changed!');
            return true;
            }
        // Update the robot info with the new energy value
        robotInfo.energy = newEnergy;
        robotInfo.energyPercent = Math.floor((robotInfo.energy / robotInfo.energyMax) * 100);
        robotInfo.energyRating = _self.getRatingToken(robotInfo.energyPercent);
        _worldPlayerRobots[robotString] = robotInfo; // sync the robot info with the index
        // Update the overview with any changes to the status
        if (robotInfo.energy > 0){ $robotOverview.removeClass('disabled'); }
        else { $robotOverview.addClass('disabled'); }
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
        $('> i', $robotEnergyGuage).css({width: robotInfo.energyPercent + '%'}).removeClass().addClass(robotInfo.energyRating);
        // Add a restored class to this robot to show it being effected by the action
        if (playSound){ _self.playSoundEffect('recovery-energy'); }
        $robotOverview.addClass('energy-restored');
        setTimeout(function(){ $robotOverview.removeClass('energy-restored'); }, 3000);
        // Trigger a save of the world state to persist this change
        //_self.saveWorldState(); // not yet
        // Return true on success
        return true;
    }

    // Quick function for restoring a robot's weapons (if available) by a specific amount (or all if === true)
    restoreRobotWeapons(robotString, restoreAmount, playSound){
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
        $robotOverview.addClass('weapons-restored');
        setTimeout(function(){ $robotOverview.removeClass('weapons-restored'); }, 3000);
        // Trigger a save of the world state to persist this change
        //_self.saveWorldState(); // not yet
        // Return true on success
        return true;
    }

}
