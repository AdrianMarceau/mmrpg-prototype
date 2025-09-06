
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
    playerAbilities: ['buster-shot'],
    playerRobotsIndex: {},
    playerItemsIndex: {},
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
        moveTimeout: 200, // milliseconds
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
    mapItemSymbols: {},
    mapItemsIndex: {},
    mapAbilitySymbols: {},
    mapAbilitiesIndex: {},
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
    robotStatModMax: 5, // match the battle system
    robotStatModMin: -5, // match the battle system
    itemInventoryMax: 99, // match the battle system
    };
gameSettings.worldState = {
    cursor: {
        position: '0-0',
        positionXY: [0, 0],
        direction: '',
        holding: '',
        loading: false,
        moving: false,
        busy: false,
        moved: false, // moved positions (or tried to)
        othered: false, // did any other action (besides move)
        clicked: false, // included in above 'othered'
        hovered: false, // included in above 'othered'
        pressed: false, // included in above 'othered'
        col: 0,
        row: 0,
        },
    player: {
        token: 'player',
        position: '0-0',
        direction: '',
        robots: {},
        items: {},
        abilities: {},
        },
    items: {},
    abilities: {},
    buttons: {},
    switches: {},
    symbols: {},
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
        return _worldCursor.loading || _worldCursor.busy || _worldCursor.moving;
        }

    // Quick function for checking if the world map specifically is busy doing something (either busy because world, or because hidden)
    worldMapIsHidden(){
        //console.log('%c' + 'mmrpgWorldMap.worldMapIsHidden()', 'color: green;');
        let _self = this;
        let _world = _self.state;
        let activeWindowEvent = gameSettings.activeWindowEvent ? true : false;
        return _world.mapIsHidden || activeWindowEvent;
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
        let $cursorPalette = $('#cursor-palette', $thisWorld);
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
        _elements.cursorPalette = $cursorPalette;
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
        // If player robots were defined [list + index] in the predefined config, copy them over to the state
        let _playerRobots = _config.playerRobots;
        let _playerRobotsIndex = _config.playerRobotsIndex;
        if (_playerRobots.length && Object.keys(_playerRobotsIndex).length){
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
        // If player abilities were defined [list] in the predefined config, copy them over to the state
        let _playerAbilities = _config.playerAbilities;
        if (_playerAbilities.length){
            //console.log('---> initWorldMap() found ' + _playerAbilities.length + ' player abilities to initialize!');
            let livePlayerAbilities = JSON.parse(JSON.stringify(_playerAbilities));
            //console.log('---> adding abilities to player state:', livePlayerAbilities);
            _worldPlayer.abilities = livePlayerAbilities;
            //console.log('---> initWorldMap() livePlayerAbilities =', livePlayerAbilities);
            }
        // If player items were defined [index] in the predefined config, copy them over to the state
        let _playerItemsIndex = _config.playerItemsIndex;
        if (Object.keys(_playerItemsIndex).length){
            //console.log('---> initWorldMap() found ' + Object.keys(_playerItemsIndex).length + ' player items to initialize!');
            let livePlayerItems = JSON.parse(JSON.stringify(_playerItemsIndex));
            //console.log('---> adding items to player state:', livePlayerItems);
            _worldPlayer.items = livePlayerItems;
            //console.log('---> initWorldMap() livePlayerItems =', livePlayerItems);
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
                console.log('MMRPG WORLD LOADED & READY!');
                gameSettings.gameHasStarted = true;
                $thisWorld.removeClass('hidden');
                $thisWorld.addClass('ready');
                $canvasMap.addClass('ready');
                _self.startIdleAnimation();
                _self.triggerWindowEventsPull();
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
        let $sideButtons = _elements.sideButtons;
        let $robotsOverview = _elements.robotsOverview;
        $clickOverlay.bind('click', function(e){
            e.preventDefault();
            if (_self.worldMapIsHidden()){
                $('.button[data-action="dismiss"]', $sideButtons).trigger('click');
                if ($robotsOverview.is('.expanded')){ $robotsOverview.find('.team-switch').trigger('click'); }
                return false;
                }
            if (_self.worldIsBusy()){ return false; }
            if (!_world.allowClicks){ return false; }
            //console.log('%c' + 'Map overlay click event!', 'color: cyan;');
            //console.log('-> w/ e =', e);
            _worldCursor.othered = true;
            _worldCursor.clicked = true;
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
            if ($sideButtons.is('.active')){ $sideButtons.find('.button[data-action="dismiss"]').trigger('click'); }
            else if (sameAsCurrent){ _self.refreshMapPositionEvents(0); }
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
            _worldCursor.othered = true;
            _worldCursor.hovered = true;
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
        let _selfRef = _self.bindEventsToWorld;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldPlayerRobots = _worldPlayer.robots;
        let $sideButtons = _elements.sideButtons;
        let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
        let $actionDropdown = _elements.actionDropdown;
        let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
        // Bind a click event to the back button in the header that'll bring us to prototype menu
        let $backButton = _elements.backButton;
        if ($backButton && $backButton.length){
            $backButton.bind('click', function(e){
                e.preventDefault();
                if ($(this).is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
                if ($playerSwitcher.is('.disabled')){ return false; }
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
                if ($(this).is('.disabled')){ return false; }
                if ($playerSwitcher.is('.disabled')){ return false; }
                let $option = $(this);
                $('.sprite.player > .sprite', $option).attr('data-frame', '01'); // taunt
                _self.playSoundEffect('icon-hover');
                });
            $('.team-player[data-player]', $playerSwitcher).bind('mouseleave', function(e){
                //console.log('%c' + 'Player switcher mouseleave!', 'color: cyan;');
                e.preventDefault();
                if ($(this).is('.disabled')){ return false; }
                if ($playerSwitcher.is('.disabled')){ return false; }
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
                // Define a function for making the storage bullets
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
                // Define a function for making the storage pages
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
                // Define a function for disabling incompatible or distraction UI elements
                let disableOtherElements = function(){
                    //console.log('%c' + 'disableOtherElements() called!', 'color: magenta;');
                    $homeButton.addClass('disabled');
                    $backButton.addClass('disabled');
                    $resetButton.addClass('disabled');
                    $playerSwitcher.addClass('disabled');
                    };
                // Define a function for enabling the incompatible or distraction UI elements again
                let enableOtherElements = function(){
                    //console.log('%c' + 'enableOtherElements() called!', 'color: magenta;');
                    $homeButton.removeClass('disabled');
                    $backButton.removeClass('disabled');
                    $resetButton.removeClass('disabled');
                    $playerSwitcher.removeClass('disabled');
                    };
                // expand/collapse the robot storage tray by clicking the switch button
                $switchButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    //console.log('%c' + 'Team switch button clicked!', 'color: cyan;');
                    // First we start by either toggling the expanded class on the overview panel itself
                    $robotsOverview.toggleClass('expanded');
                    $teamRobotsInOverview.removeClass('selected');
                    let isExpandedNow = $robotsOverview.is('.expanded');
                    // if we're not expanded, run some cleanup then we're done
                    if (!isExpandedNow){
                        enableOtherElements();
                        _world.mapIsHidden = false;
                        $('.pages', $storageRobotsDiv).remove();
                        $('.bullets', $storageRobotsDiv).remove();
                        $teamRobotsDiv.removeClass('focused');
                        $storageRobotsDiv.removeClass('focused');
                        return;  // if we're not expanded, then we're done here
                        }
                    // otherwise if we're expanded we need to run some setup
                    _world.mapIsHidden = true; // set the map hidden state
                    // Disable the outside UI buttons to prevent bad-clicks and visual clutter
                    disableOtherElements();
                    // Remake the storage bullets nad pages now
                    makeStorageBullets();
                    makeStoragePages();
                    goToStoragePage(1);
                    // Mark the team-robots side as the focused one
                    $teamRobotsDiv.addClass('focused');
                    // Make the first robot in the overview as selected via class
                    let $firstOverviewRobot = $teamRobotsInOverview.first();
                    $firstOverviewRobot.addClass('selected');
                    if ($sideButtons.is('.active')){
                        //console.log('-> side buttons active, make sure we dismiss!');
                        let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                        $sideButtons.removeClass('maybe');
                        $dismissButton.trigger('click');
                        }
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
                    // Empty the side-button area before starting
                    $sideButtons.removeClass('active');
                    $sideButtonsWrapper.empty();
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
                    $switchButton.addClass('disabled'); // do not allow going back until either saving or reverting
                    // Generate the save/cancel buttons and append them to the side-buttons panel
                    let $saveButton = $('<a class="button big-button narrow" data-action="save-reload"><span>Save &amp Reload</span></a>');
                    let $cancelButton = $('<a class="button sub-button narrow" data-action="dismiss"><span>Cancel</span></a>');
                    $sideButtonsWrapper.append($saveButton).append($cancelButton);
                    $sideButtons.addClass('active');
                    // Define the save/cancel actions to bind to the buttons
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
                    let cancelAction = function(){
                        //console.log('%c' + '-> robot-storage cancelAction() triggered', 'color: magenta;');
                        // First we revert the robots in the overview back to the backup copy we made earlier
                        $teamRobotsDiv.empty().prepend($teamRobotsInOverviewBackup);
                        $storageRobotsDiv.empty().prepend($storageRobotsInOverviewBackup);
                        $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
                        $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
                        // Then we remove the save/cancel button set from the overview panel
                        $saveButton.remove();
                        $cancelButton.remove();
                        $switchButton.removeClass('disabled');
                        $sideButtons.removeClass('active');
                        $sideButtonsWrapper.empty();
                        // Return true on success
                        return true;
                        };
                    // Bind events to the save button that'll save the changes we've made
                    $saveButton.bind('click', function(e){
                        //console.log('%c' + 'Robot swap save button clicked!', 'color: cyan;');
                        e.preventDefault();
                        saveAction();
                        });
                    // Bind events to the cancel button that'll revert the changes we've mapStartDirection made
                    $cancelButton.bind('click', function(e){
                        //console.log('%c' + 'Robot swap cancel button clicked!', 'color: cyan;');
                        e.preventDefault();
                        return cancelAction();
                        });
                    // Return true on success
                    return true;
                    });
                // if the storage tray is open, clicking a page-button in the storage-list scrolls through selected team-robots
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

        // Define a function to run each time user inputs are updated so we can react
        let listenForInput = true;
        let ignoreTimeout = null;
        let ignoreInputFor = function(delay){
            delay = typeof delay === 'number' ? delay : 250;
            if (delay < 1){ listenForInput = true; return; }
            listenForInput = false;
            if (ignoreTimeout){ clearTimeout(ignoreTimeout); }
            ignoreTimeout = setTimeout(function(){ listenForInput = true; }, delay);
            };
        let userInputVars = {};
        let checkUserInputs = function(kind, event, activeInputs, userInputs){
            //console.log('%c' + 'mmrpgWorldMap.checkUserInputs(kind:' + kind + ', event, activeInputs, userInputs)', 'color: cyan;');
            //event.preventDefault();
            //event.stopPropagation();
            //console.log('-> event:', e);
            if (!listenForInput){ return false; }
            if (_self.worldIsBusy()){ return false; }
            if (!Object.keys(activeInputs).length){ return false; } // nothing pressed, ignore
            _worldCursor.othered = true;
            _worldCursor.pressed = true;
            //console.log('-> activeInputs:', activeInputs);
            ignoreInputFor();
            // Collect references and checks on certain key elements
            let worldMapIsHidden = _self.worldMapIsHidden();
            let sideButtonsActive = $sideButtons.is('.active') ? true : false;
            let playerSwitcherFocused = $playerSwitcher.is('.focused') ? true : false;
            let robotStorageIsActive = $robotsOverview.is('.expanded') ? true : false;
            // If the player switcher is currently focused, we should listen for a confirmation button
            if (playerSwitcherFocused){
                // If the player has pressed the A button, we can simple click whichever team-player is currently "hovered"
                if (activeInputs.A){
                    //console.log('%c' + 'Confirm player switch!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let $hoveredPlayer = $('.team-player.hovered', $playerSwitcher).first();
                    if (!$hoveredPlayer || !$hoveredPlayer.length){ return false; }
                    //console.log('Triggering click on hovered player:', $hoveredPlayer);
                    $hoveredPlayer.trigger('click');
                    $playerSwitcher.removeClass('focused');
                    $('.team-player', $playerSwitcher).removeClass('hovered');
                    ignoreInputFor(1200);
                    return true;
                    }
                }
            // If the robot storage area is currently open, process those actions too
            if (robotStorageIsActive){
                // Collect references to key elements within the robots overview
                let $teamRobotsDiv = $('.team-robots', $robotsOverview);
                let $storageRobotsDiv = $('.storage-robots', $robotsOverview);
                let $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
                let $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
                let focusedPanel = $storageRobotsDiv.is('.focused') ? 'storage' : 'team';
                let $focusedDiv = focusedPanel === 'storage' ? $storageRobotsDiv : $teamRobotsDiv;
                let $robotsInFocusedDiv = focusedPanel === 'storage' ? $storageRobotsInOverview : $teamRobotsInOverview;
                //console.log('-> robotStorageIsActive =', robotStorageIsActive);
                //console.log('-> focusedPanel =', focusedPanel);
                //console.log('-> sideButtonsActive =', sideButtonsActive);
                // If the side buttons are active, then we use Start and B to control them specifically
                if (sideButtonsActive){
                    // If the side buttons are available, we can use Start to click the save button and B to click cancel
                    // (make sure we do the usual requirement of adding the maybe class first THEN clicking if already there)
                    let $saveButton = $('.button[data-action="save-reload"]', $sideButtons);
                    let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                    if (activeInputs.Start && $saveButton.length && !$saveButton.is('.disabled')){
                        //console.log('%c' + 'Start key pressed!', 'color: orange;');
                        if (event){ event.preventDefault(); }
                        if (!$saveButton.is('.maybe')){ $saveButton.addClass('maybe'); }
                        else {
                            $saveButton.removeClass('maybe').addClass('clicked');
                            $saveButton.trigger('click');
                            setTimeout(function(){ $saveButton.removeClass('clicked'); }, 600);
                            ignoreInputFor(1200);
                            return true;
                            }
                        }
                    if (activeInputs.B && $dismissButton.length && !$dismissButton.is('.disabled')){
                        //console.log('%c' + 'B key pressed!', 'color: orange;');
                        if (event){ event.preventDefault(); }
                        if (!$dismissButton.is('.maybe')){ $dismissButton.addClass('maybe'); }
                        else {
                            $dismissButton.removeClass('maybe').addClass('clicked');
                            $dismissButton.trigger('click');
                            setTimeout(function(){ $dismissButton.removeClass('clicked'); }, 600);
                            ignoreInputFor(1200);
                            return true;
                            }
                        }
                    }
                // Otherwise if no side buttons yet, then we use either Start or B to close the panel instead
                else {
                    // If the player has pressed the start button again, attempt to close the storage area via the same button
                    // (allow dismissing with the B button as well for convenience)
                    if (activeInputs.Start || activeInputs.B){
                        //console.log('%c' + 'Start key pressed!', 'color: orange;');
                        if (event){ event.preventDefault(); }
                        let $switchButton = $('.team-switch', $robotsOverview);
                        if ($switchButton.length
                            && $switchButton.is(':visible')
                            && !$switchButton.is('.disabled')){
                            $switchButton.addClass('clicked');
                            $switchButton.trigger('click');
                            setTimeout(function(){ $switchButton.removeClass('clicked'); }, 600);
                            ignoreInputFor(900);
                            return true;
                            }
                        }
                    }
                // If the player has pressed the A button, we can simple click whichever team-robot is currently "hovered"
                if (activeInputs.A){
                    //console.log('%c' + 'Confirm robot swap!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let $focusedPanel = $storageRobotsDiv.is('.focused') ? $storageRobotsDiv : ($teamRobotsDiv.is('.focused') ? $teamRobotsDiv : false);
                    if (!$focusedPanel || !$focusedPanel.length){ return false; }
                    let $otherPanel = $focusedPanel.is($storageRobotsDiv) ? $teamRobotsDiv : $storageRobotsDiv;
                    let $hoveredRobot = $('.team-robot.hovered', $focusedPanel).first();
                    if (!$hoveredRobot || !$hoveredRobot.length){ return false; }
                    //console.log('Triggering click on hovered robot:', $hoveredRobot);
                    $hoveredRobot.trigger('click');
                    $focusedPanel.removeClass('focused');
                    $('.team-robot', $focusedPanel).removeClass('hovered');
                    $otherPanel.addClass('focused');
                    $('.team-robot', $otherPanel).removeClass('hovered');
                    $('.team-robot', $otherPanel).first().addClass('hovered');
                    ignoreInputFor(900);
                    return true;
                    }
                // If the player has pressed an arrow key, move the "hover" class accordingly in the appropriate of the two columns
                // Left/Right directional inputs switch which panel is "focused" between team-robots (left) and storage-robots (right)
                // Up/Down directional inputs then move the "hovered" class up and down within the currently active panel (selected for left, hover for right)
                if (activeInputs.Up || activeInputs.Down || activeInputs.Left || activeInputs.Right){
                    //console.log('%c' + 'Arrow key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (activeInputs.Left || activeInputs.Right){
                        //console.log('-> switching focused panel to ' + (focusedPanel === 'team' ? 'storage' : 'team'));
                        let $newPanel = focusedPanel === 'team' ? $storageRobotsDiv : $teamRobotsDiv;
                        let $oldPanel = focusedPanel === 'team' ? $teamRobotsDiv : $storageRobotsDiv;
                        $oldPanel.removeClass('focused');
                        $newPanel.addClass('focused');
                        $('.team-robot', $oldPanel).removeClass('hovered');
                        $('.team-robot', $newPanel).removeClass('hovered');
                        $('.team-robot', $newPanel).first().addClass('hovered');
                        return true;
                        }
                    let $nextRobot = false;
                    let $hoveredRobot = $robotsInFocusedDiv.filter('.hovered').first();
                    if (!$hoveredRobot.length){ $hoveredRobot = $robotsInFocusedDiv.first(); }
                    $robotsInFocusedDiv.removeClass('hovered');
                    $hoveredRobot.addClass('hovered');
                    if (activeInputs.Up){
                        if ($hoveredRobot && $hoveredRobot.length){
                            $nextRobot = $hoveredRobot.prevAll('.team-robot:not(.hidden)').first();
                            }
                        if (!$nextRobot || !$nextRobot.length){
                            $nextRobot = $robotsInFocusedDiv.filter('.team-robot:not(.hidden)').last();
                            }
                        } else if (activeInputs.Down){
                        if ($hoveredRobot && $hoveredRobot.length){
                            $nextRobot = $hoveredRobot.nextAll('.team-robot:not(.hidden)').first();
                            }
                        if (!$nextRobot || !$nextRobot.length){
                            $nextRobot = $robotsInFocusedDiv.filter('.team-robot:not(.hidden)').first();
                            }
                        }
                    if ($nextRobot && $nextRobot.length){
                        $hoveredRobot.removeClass('hovered');
                        $nextRobot.addClass('hovered');
                        }
                    return true;
                    }
                return;
                }
            // If the side buttons panel is currently open, process those actions too
            if (sideButtonsActive){
                // If the player has pressed the space or enter keys, let's confirm the side-button action if it's open
                if (activeInputs.A){
                    //console.log('%c' + 'Confirm action popup!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!$sideButtons.is('.active')){ return false; }
                    let $confirmButton = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons).first();
                    if (!$confirmButton || !$confirmButton.length){ /* console.error('bindEventsToWorld() unable to find confirm button!'); */ return false; }
                    if ($confirmButton.is('.clicked')){ return }
                    if (!$confirmButton.is('.maybe')){ $confirmButton.addClass('maybe'); return; }
                    $confirmButton.removeClass('maybe');
                    //console.log('Triggering click on confirm button:', $confirmButton);
                    $confirmButton.trigger('click');
                    return true;
                    }
                // Else if the player has pressed the backspace or escape keys, let's close the side-button action if it's open
                else if (activeInputs.B){
                    //console.log('%c' + 'Dismiss action popup!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!$sideButtons.is('.active')){ return false; }
                    let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                    if (!$dismissButton || !$dismissButton.length){ console.error('bindEventsToWorld() unable to find dismiss button!'); return false; }
                    $sideButtons.removeClass('maybe');
                    $dismissButton.trigger('click');
                    return true;
                    }
                // Else if the player has just pressed shift, make sure we add the hover class to the action-dropdown
                else if (activeInputs.Y){
                    //console.log('%c' + 'Shift key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!$sideButtons.is('.active')){ return false; }
                    $actionDropdown.toggleClass('hover');
                    return true;
                    }
                }
            // Otherwise if the world map is NOT hidden, so the arrow keys must be controlling the player
            if (!worldMapIsHidden){
                // If the player has pressed the select button, try to click the team-rotate button if exists/not-disabled
                if (activeInputs.Select){
                    //console.log('%c' + 'Select key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let $rotateButton = $('.team-rotate', $robotsOverview);
                    if ($rotateButton.length
                        && $rotateButton.is(':visible')
                        && !$rotateButton.is('.disabled')){
                        $rotateButton.addClass('clicked');
                        $rotateButton.trigger('click');
                        setTimeout(function(){ $rotateButton.removeClass('clicked'); }, 300);
                        ignoreInputFor(900);
                        return true;
                        }
                    }
                // If the player has pressed the start button, try to click the team-switch button if exists/not-disabled
                if (activeInputs.Start){
                    //console.log('%c' + 'Start key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let $switchButton = $('.team-switch', $robotsOverview);
                    if ($switchButton.length
                        && $switchButton.is(':visible')
                        && !$switchButton.is('.disabled')){
                        $switchButton.addClass('clicked');
                        $switchButton.trigger('click');
                        setTimeout(function(){ $switchButton.removeClass('clicked'); }, 200);
                        ignoreInputFor(1000);
                        return true;
                        }
                    }
                // If the player has pressed the A button without any menus open, perhaps they're trying to re-init nearby events
                if (activeInputs.A){
                    //console.log('%c' + 'A key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    // Only allow this if the map is not currently animating and the player is not currently moving
                    if (_world.mapIsAnimating || _world.playerIsMoving){ return false; }
                    // If there are any nearby events, re-init them now
                    _self.refreshMapPositionEvents(0);
                    ignoreInputFor(100);
                    }
                // If the player has pressed either of the bumpers we should let them scroll within the player-switcher
                if (activeInputs.L1 || activeInputs.R1){
                    //console.log('%c' + 'Bumper key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let $playerButtons = $('.team-player', $playerSwitcher);
                    if ($playerButtons.length < 2){ return true; } // nothing to switch to, ignore
                    let $activePlayer = $playerButtons.filter('.active').first();
                    let $hoveredPlayer = $playerButtons.filter('.hovered').first();
                    if (!$playerSwitcher.is('.focused')
                        || !$hoveredPlayer.length){
                        $playerSwitcher.addClass('focused');
                        $playerButtons.removeClass('hovered');
                        $activePlayer.addClass('hovered');
                        ignoreInputFor(300);
                        return true;
                        } else {
                        $playerButtons.removeClass('hovered');
                        if (activeInputs.L1){
                            let $prevPlayer = $hoveredPlayer.prevAll('.team-player').first();
                            if (!$prevPlayer || !$prevPlayer.length){ $prevPlayer = $playerButtons.last(); }
                            if ($prevPlayer.length){ $prevPlayer.addClass('hovered'); }
                            }
                        else if (activeInputs.R1){
                            let $nextPlayer = $hoveredPlayer.nextAll('.team-player').first();
                            if (!$nextPlayer || !$nextPlayer.length){ $nextPlayer = $playerButtons.first(); }
                            if ($nextPlayer.length){ $nextPlayer.addClass('hovered'); }
                            }
                        ignoreInputFor(300);
                        return true;
                        }
                    let focusTimeout = _selfRef._playerSwitcherTimeout;
                    if (focusTimeout){ clearTimeout(focusTimeout); }
                    focusTimeout = setTimeout(function(){
                        $playerSwitcher.removeClass('focused');
                        $playerButtons.removeClass('hovered');
                        }, 2000);
                    _selfRef._playerSwitcherTimeout = focusTimeout;
                    return true;
                    }
                // If the player has pressed either of the triggers we should zoom/unzoom the map
                if (activeInputs.L2 || activeInputs.R2){
                    //console.log('%c' + 'Trigger key pressed!', 'color: orange;');
                    //console.log('-> activeInputs: ', Object.keys(activeInputs).length ? activeInputs : 'none');
                    if (event){ event.preventDefault(); }
                    if (activeInputs.L2 && activeInputs.R2){
                        //console.log('%c' + 'Both triggers held, reset zoom!', 'color: orange;');
                        // when both are held, we reset the zoom
                        let oldZoom = _world.zoomLevel || 1;
                        _self.updateZoomLevel(1, true);
                        let newZoom = _world.zoomLevel || 1;
                        if (newZoom !== oldZoom){
                            _self.playSoundEffect('spawn-sound');
                            ignoreInputFor(1000);
                            }
                        return true;
                        } else {
                        //console.log('%c' + (activeInputs.L2 ? 'L2' : 'R2') + ' held, zooming ' + (activeInputs.L2 ? 'out' : 'in') + '!', 'color: orange;');
                        // otherwise we use L1 to zoom out and R1 to zoom in
                        let zoomDir = false;
                        if (activeInputs.L2){ zoomDir = 'out'; }
                        if (activeInputs.R2){ zoomDir = 'in'; }
                        let oldZoom = _world.zoomLevel || 1;
                        if (zoomDir === 'out'){ _self.decZoomLevel(null, true); }
                        else { _self.incZoomLevel(null, true); }
                        let newZoom = _world.zoomLevel || 1;
                        if (newZoom !== oldZoom){
                            _self.playSoundEffect('spawn-sound');
                            ignoreInputFor(1000);
                            }
                        return true;
                        }
                    }
                // If the player has pressed any of the arrow keys, let's update the position accordingly
                if (activeInputs.Left || activeInputs.Right || activeInputs.Up || activeInputs.Down){
                    //console.log('%c' + 'Arrow key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let oldPos = _worldCursor.position, curPos = oldPos;
                    let thisPos = oldPos.split('-');
                    let thisCol = parseInt(thisPos[0]);
                    let thisRow = parseInt(thisPos[1]);
                    let newCol = thisCol, newRow = thisRow;
                    //console.log('%c' + 'Current position: ' + oldPos, 'color: orange;');
                    if (activeInputs.Left){ newCol--; }
                    else if (activeInputs.Right){ newCol++; }
                    if (activeInputs.Up){ newRow--; }
                    else if (activeInputs.Down){ newRow++; }
                    let newPos = newCol + '-' + newRow;
                    // Always set this just in case the player gets stuck somewhere
                    let thisHorDir = (newCol > thisCol) ? 'right' : (newCol < thisCol) ? 'left' : false;
                    let thisVerDir = (newRow > thisRow) ? 'down' : (newRow < thisRow) ? 'up' : false;
                    let thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join('-'); })(thisHorDir, thisVerDir);
                    _worldCursor.moved = true; // represents them at least trying to move
                    _worldCursor.direction = thisShiftDir; // the direction they are trying to move
                    //console.log('%c' + 'New position: ' + newPos, 'color: orange;');
                    // Check if the new position is the same as the old position
                    if (newCol === thisCol && newRow === thisRow){
                        //console.log('%c' + 'New position is the same as the old position!', 'color: orange;');
                        return false;
                        }
                    // Otherwise, let's pull the list of walkable tiles and see if this new position is valid
                    //console.log('%c' + 'Checking if new position is walkable...', 'color: orange;');
                    let playerMobility = _config.playerMobility;
                    let walkableTiles = _self.getWalkableMapTiles();
                    let tilesWithinRange = playerMobility > 0 ? _self.getWalkableMapTilesByProximity(oldPos, playerMobility) : walkableTiles;
                    if (walkableTiles.indexOf(newPos) === -1 && tilesWithinRange.indexOf(newPos) === -1){
                        //console.log('%c' + 'New position is not walkable!', 'color: orange;');
                        //_self.playSoundEffect('glass-klink');
                        if (!playerIsCursor){
                            //console.log('%c' + 'Player is human, can only walk to adjacent tiles!', 'color: red;');
                            _self.refreshMapPositionEvents(0);
                            return false;
                            } else {
                            //console.log('%c' + 'Player is cursor, can cross voids if walkable tiles on other side!', 'color: green;');
                            // If the player is the cursor, we can allow crossing voids if there's a walkable tile on the other side
                            // So, let's loop through, adding +1 to the direction they were travelling, and trying to find a walkable tile
                            // if none are found we return false, but if we find one in the direction they were going, and there are only void-tiles in-between, we can move them there
                            let checkCol = thisCol, checkRow = thisRow;
                            let foundWalkableTile = false;
                            let maxChecks = 10; // arbitrary limit to avoid infinite loops
                            let numChecks = 0;
                            while (!foundWalkableTile && numChecks < maxChecks){
                                numChecks++;
                                if (activeInputs.Left){ checkCol--; }
                                else if (activeInputs.Right){ checkCol++; }
                                if (activeInputs.Up){ checkRow--; }
                                else if (activeInputs.Down){ checkRow++; }
                                let checkPos = checkCol + '-' + checkRow;
                                if (walkableTiles.indexOf(checkPos) !== -1){
                                    foundWalkableTile = true;
                                    newPos = checkPos;
                                    //console.log('%c' + 'Found walkable tile at ' + newPos + ', allowing move!', 'color: green;');
                                    } else if (tilesWithinRange.indexOf(checkPos) !== -1){
                                    foundWalkableTile = true;
                                    newPos = checkPos;
                                    //console.log('%c' + 'Found walkable tile within range at ' + newPos + ', allowing move!', 'color: green;');
                                    }
                                }
                            if (!foundWalkableTile){
                                //console.log('%c' + 'No walkable tile found in that direction!', 'color: orange;');
                                _self.refreshMapPositionEvents(0);
                                return false;
                                }
                            }
                        }
                    // Otherwise, let's move the cursor to the new position
                    _self.makeLayerTileActive(newPos);
                    _self.playSoundEffect('no-effect');
                    _self.moveToPosition(newPos, function(){
                        _self.makeLayerTileInactive(oldPos);
                        //ignoreInputFor(0);
                        });
                    }
                // If the user has pressed the X button, we need to implement some nuanced functionality
                // -> if it's a simple press, it's for the "menu" (not implemented yet, so just show a console.warn message)
                // -> else if it's a long-press (user is holding button) then add the "focused" class to the home button, wait an appropriate amount of time, then click it
                // The important thing here is to zoom the map in closer and closer as the user holds the button until we reach a threshold, and THEN click it, but if the user stops pressing the button then the focused class is removed and the zoom is reset and the whole thing is cancelled
                if (typeof userInputVars.xTimeout === 'undefined'){ userInputVars.xTimeout = false; }
                if (typeof userInputVars.xWasPressedAt === 'undefined'){ userInputVars.xWasPressedAt = null; }
                if (typeof userInputVars.xWasPressedFor === 'undefined'){ userInputVars.xWasPressedFor = 0; }
                if (activeInputs.X){
                    //console.log('%c' + 'X key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!userInputVars.xWasPressedAt){ userInputVars.xWasPressedAt = Date.now(); }
                    // throttle the zooming action to every 300ms
                    let timeThreshold = 300;
                    let timeToIgnoreAfter = 3000;
                    let timeoutRefresh = 1200;
                    let timeSincePress = Date.now() - userInputVars.xWasPressedAt;
                    // If it's been a while since the last press, we know this is a home-button request
                    if (timeSincePress >= timeThreshold){
                        $homeButton.addClass('focused');
                        userInputVars.xWasPressedAt = Date.now();
                        userInputVars.xWasPressedFor++;
                        let newZoomLevel = 1.00 + (0.25 * (userInputVars.xWasPressedFor - 1));
                        //console.log('%c' + 'X key has been pressed for ' + userInputVars.xWasPressedFor + 'x times!', 'color: orange;');
                        _self.updateZoomLevel(newZoomLevel);
                        let $teamSprites = _elements.teamSprites;
                        let $cursorSprite = $teamSprites.filter('.cursor');
                        let $otherSprites = $teamSprites.filter(':not(.cursor)');
                        $cursorSprite.addClass('shake');
                        $otherSprites.filter(':not([data-frame="06"])').first().attr('data-frame', '06'); // summon
                        if (userInputVars.xTimeout){ clearTimeout(userInputVars.xTimeout);  }
                        if (userInputVars.xWasPressedFor >= 5){
                            //console.log('%c' + 'X key held long enough, triggering home button!', 'color: orange;');
                            $homeButton.trigger('click');
                            userInputVars.xWasPressedAt = null;
                            userInputVars.xWasPressedFor = 0;
                            ignoreInputFor(timeToIgnoreAfter);
                            } else {
                            userInputVars.xTimeout = setTimeout(function(){
                                //console.log('%c' + 'X key timeout!', 'color: orange;');
                                userInputVars.xWasPressedAt = null;
                                userInputVars.xWasPressedFor = 0;
                                $homeButton.removeClass('focused');
                                _self.resetZoomLevel();
                                $otherSprites.attr('data-frame', '00');
                                $cursorSprite.removeClass('shake');
                                }, timeoutRefresh);
                            }
                        }
                    // Otherwise if this is a fresh press, the user must be trying to open the main menu
                    else {
                        //console.log('%c' + 'X key fresh press, open menu (not implemented yet)!', 'color: orange;');
                        if (userInputVars.xTimeout){ clearTimeout(userInputVars.xTimeout);  }
                        userInputVars.xTimeout = setTimeout(function(){
                            console.warn('Menu functionality not implemented yet!');
                            }, (timeThreshold * 2));
                        }
                    }
                }
            };

        // Start the user input watcher and collect reference to active inputs
        let userInputWatcher = new mmrpgUserInputWatcher();
        userInputWatcher.onUserInput(checkUserInputs);
        userInputWatcher.startWatching();

        // Bind an event to the window resize so we can check devicePixelRatio and adjust rendering if needed
        $(window).bind('resize', function(e){
            //console.log('%c' + 'World map window resize event!', 'color: cyan;');
            //e.preventDefault();
            //e.stopPropagation();
            //console.log('-> event:', e);
            //console.log('-> window.devicePixelRatio:', window.devicePixelRatio);
            let pixelRatio = window.devicePixelRatio || 1;
            let imageRendering = pixelRatio === 1 || pixelRatio % 2 === 0 ? 'pixelated' : 'auto';
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
        let $trackingCursor = $('.sprite.tracking-cursor', $canvasMap);
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
        $('.sprite[data-frame]:not(.disabled):not(.frame-lock)', $canvasMap).attr('data-frame', '00');
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
        let newCursorStyles = { left: tileOffsetX + 'px', top: tileOffsetY + 'px', zIndex: tileOffsetZ };
        let newCursorTrackerStyles = { left: (tileOffsetX + 10) + 'px', top: (tileOffsetY + 5) + 'px', zIndex: (tileOffsetZ - 1) };
        if (animateMove){
            $cursorSprite.stop(true, true).animate(newCursorStyles, travelDuration, 'linear', onMoveComplete);
            if ($trackingCursor.length){ $trackingCursor.stop(true, true).animate(newCursorTrackerStyles, travelDuration, 'linear'); }
            } else {
            $cursorSprite.css(newCursorStyles); onMoveComplete();
            if ($trackingCursor.length){ $trackingCursor.css(newCursorTrackerStyles); }
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
            let teamOffsetZ = tileOffsetZ + 1;
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
                let newFrame = false;
                if (!$thisSprite.is('.disabled')){
                    newFrame = $thisSprite.is('.player') ? '09' : $thisSprite.is('.robot') ? '07' : '00'; // run for players, slide for robots
                    $thisSprite.attr('data-frame', newFrame);
                    onTeamMoveComplete = function(){ $thisSprite.attr('data-frame', '00'); };
                    }
                $thisSprite.prop('worldX', teamOffsetX);
                $thisSprite.prop('worldY', teamOffsetY);
                $thisSprite.prop('worldZ', teamOffsetZ);
                if (animateMove){
                    $thisSprite.stop(true, true).animate({
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
        _world.zoomLevel = newZoomLevel;
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
    incZoomLevel(boostAmount, updateUserZoom){ return this.modZoomLevel((boostAmount || 0.25), updateUserZoom); }
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

    // Quick function that starts an "animation" whereby the map zooms a bit at a time from current
    // to a max value given a delay between each zoom increment. Make sure we use the above functions!
    async animateZoomToMax(maxZoomLevel, zoomIncrement, zoomDelay){
        //console.log('%c' + 'mmrpgWorldMap.animateZoomToMax(maxZoomLevel:' + maxZoomLevel + ', zoomIncrement:' + zoomIncrement + ', zoomDelay:' + zoomDelay + ')', 'color: magenta;');
        if (!maxZoomLevel || typeof maxZoomLevel !== 'number' || maxZoomLevel <= 0){
            console.error('animateZoomToMax() requires a valid maxZoomLevel!');
            return false;
            }
        if (!zoomIncrement || typeof zoomIncrement !== 'number' || zoomIncrement <= 0){
            console.error('animateZoomToMax() requires a valid zoomIncrement!');
            return false;
            }
        if (!zoomDelay || typeof zoomDelay !== 'number' || zoomDelay <= 0){
            console.error('animateZoomToMax() requires a valid zoomDelay!');
            return false;
            }
        let _self = this;
        let _elements = _self.elements;
        let _world = _self.state;
        let currentZoomLevel = _world.zoomLevel || 1;
        if (currentZoomLevel >= maxZoomLevel){ return true; }
        let newZoomLevel = currentZoomLevel + zoomIncrement;
        if (newZoomLevel > maxZoomLevel){ newZoomLevel = maxZoomLevel; }
        _self.updateZoomLevel(newZoomLevel, true);
        await new Promise(resolve => setTimeout(resolve, zoomDelay));
        return _self.animateZoomToMax(maxZoomLevel, zoomIncrement, zoomDelay);
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
    async refreshMapPositionEvents(timeoutMultiplier){
        //console.log('%c' + 'mmrpgWorldMap.refreshMapPositionEvents()', 'color: magenta;');
        timeoutMultiplier = typeof timeoutMultiplier === 'number' ? timeoutMultiplier : 1;

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
        let _worldSymbols = _world.symbols;
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let _mapSpriteSizeOffset = _config.mapSpriteSizeOffset;
        let _mapEventSymbols = _config.mapEventSymbols;
        let _mapEventsIndex = _config.mapEventsIndex;
        let _mapItemSymbols = _config.mapItemSymbols;
        let _mapItemsIndex = _config.mapItemsIndex;
        let _mapAbilitySymbols = _config.mapAbilitySymbols;
        let _mapAbilitiesIndex = _config.mapAbilitiesIndex;
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
        let stillAtPosition = function(){ return (_worldCursor.position === cursorPosition) ? true : false; };
        let otherMenusActiveNow = function(){ return (_elements.robotsOverview.is('.expanded') || _elements.sideButtons.is('.active')) ? true : false; };

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
        let zoomTimeoutDuration = 2000 * timeoutMultiplier;
        let teamReadyDuration = 1800 * timeoutMultiplier;
        let teamRushDuration = 300 * timeoutMultiplier;

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
            //console.log('-> no events found at position', cursorPosition, 'skipping further processing');
            return;
            }
        //console.log('-> found ' + eventsAtPosition.length + ' events at position');
        //console.log('-> eventsAtPosition =', eventsAtPosition);
        //console.log('-> found ' + eventsAtPosition.length + ' eventsAtPosition =', eventsAtPosition);

        // Check to see what the very first event type is
        let firstEvent = eventsAtPosition[0];
        let firstEventType = firstEvent.kind;
        //console.log('-> firstEvent =', JSON.parse(JSON.stringify(firstEvent)));
        //console.log('-> firstEventType =', firstEventType);

        // Sort the events at this position by priority with sanctuaries > portals > battles > everything-else
        eventsAtPosition = eventsAtPosition.sort(function(a, b){
            if (a.kind2 === 'sanctuary' && b.kind2 !== 'sanctuary'){ return -1; } // a is sanctuary, b is not
            else if (a.kind2 !== 'sanctuary' && b.kind2 === 'sanctuary'){ return 1; } // a is not sanctuary, but b is
            else if (a.kind === 'portal' && b.kind !== 'portal'){ return -1; } // a is portal, b is not
            else if (a.kind !== 'portal' && b.kind === 'portal'){ return 1; } // a is not portal, but b is
            else if (a.kind === 'battle' && b.kind !== 'battle'){ return -1; } // a is battle, b is not
            else if (a.kind !== 'battle' && b.kind === 'battle'){ return 1; } // a is not battle, but b is
            else { return 0; } // both are same or of irrelevant kind
            });
        //console.log('-> eventsAtPosition(after-sort) = ', JSON.parse(JSON.stringify(eventsAtPosition)));

        // Refresh the first event variables in case they've changed
        firstEvent = eventsAtPosition[0];
        firstEventType = firstEvent.kind;
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
        let showActionArea = false;
        let showActionAreaType = '';
        let showActionAreaSound = '';
        let actionAreaMarkup = '';
        let sideButtonsMarkup = '';
        let readyTeamSprites = false;
        if (firstEventType === 'event'){
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
                        //teamReadyDuration = 600; // for event panels we want to zoom in quickly
                        zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                        triggerEffectFunction = function(){
                            if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                            //console.log('-> running triggerEffectFunction for eventAction "' + eventAction + '" with eventData:', eventData);
                            _self.triggerWorldEvent(eventAction, eventData, $customEvent);
                            };
                        } else {
                        //console.log('-> eventAction is false, so prepare dropdown instead');
                        showActionArea = true;
                        if (!dataLabel){ dataLabel = 'Event Options'; }
                        actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
                        sideButtonsMarkup += '<a class="button big-button" data-action="trigger-event" data-event="'+dataEvent+'"><span><sup>Ready To</sup> Trigger Event</span></a>';
                        sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                        showActionAreaType = 'event';
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
                showActionArea = true;
                if (!dataLabel){ dataLabel = 'Portal Options'; }
                actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
                if (dataPortal.indexOf('goto__') !== -1){ sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Enter Teleport</span></a>'; }
                else if (dataPortal === 'exit'){ sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Return Home</span></a>'; }
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showActionAreaType = 'portal';
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
                        showActionArea = false;
                        autoRedirectURL = 'prototype.php';
                        } else if (dataPortal.indexOf('goto__') !== -1){
                        // GOTO PORTAL - use the portal token as worldmap token for redirect
                        autoRedirect = true;
                        showActionArea = false;
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
                    showActionArea = false;
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
                showActionArea = true;
                //var buttonName = (dataColour ? (dataColour[0].toUpperCase() + dataColour.slice(1) + ' ') : '') + 'Button';
                //if (!dataLabel){ dataLabel = 'Button Options'; }
                if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' '+dataColour : '')+'" data-action="push-button" data-button="'+dataButton+'"><span><sup>Push The</sup> ' + (dataColour[0].toUpperCase() + dataColour.slice(1)) + ' Button</span></a>';
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showActionAreaType = 'button';
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
                showActionArea = true;
                readyTeamSprites = true;
                //console.log('-> showing dropdown with battles:', dataBattles);
                //let dataBattlesJoined = dataBattles.join(',');
                let dataBattlesJoined = (function(battles){
                    for (var i = 0, list = []; i < battles.length; i++){
                        let battle = battles[i][0], battlePosition = battles[i][1];
                        list.push(battle);
                        } return list;
                    })(dataBattles).join(',');
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
                actionAreaMarkup += dataLabelsJoined;
                if (playerActiveRobots >= 1){  sideButtonsMarkup += '<a class="button big-button" data-action="start-battle" data-battle="'+dataBattlesJoined+'"><span><sup>Ready To</sup> Start Battle</span></a>'; }
                else { sideButtonsMarkup += '<a class="button big-button disabled" data-battle="'+dataBattlesJoined+'"><span><sup>Ready To</sup> Start Battle</span></a>'; }
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showActionAreaType = 'battle';
                //showActionAreaSound = 'background-spawn';
                showActionAreaSound = 'mecha-taunt-sound' + (dataBattles.length > 1 ? '*'+dataBattles.length : '');
                zoomTimeoutDuration = 1500; // otherwise if this is a battle we wait a moment
                }
            }
        else if (firstEventType === 'item'){
            //console.log('-> event at position is an item, preparing either dropdown or pickup');
            // If the cursor is literally on a button, only one event sprite matters right now
            let eventInfo = firstEvent;
            let itemInfo = _mapItemsIndex[eventInfo.token];
            let $itemEvent = $(firstEvent.sprite);
            //console.log('-> eventInfo =', eventInfo, '| itemInfo =', itemInfo, '| $itemEvent =', $itemEvent);
            let dataLabel = $itemEvent.attr('data-label');
            let dataItem = $itemEvent.attr('data-item');
            let dataItemToken = $itemEvent.attr('data-token');
            let dataColour = $itemEvent.attr('data-colour');
            //console.log('-> dataItem =', dataItem, '| dataItemToken =', dataItemToken, '| dataColour =', dataColour);
            if (!dataLabel){ dataLabel = dataItemToken; }
            if (!dataColour){ dataColour = 'none'; }
            let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
            //console.log('-> playerIsCursor =', playerIsCursor);
            if (dataItem && dataItemToken){
                // If the player is the cursor player, we should show the item pickup dropdown
                if (playerIsCursor){
                    //console.log('-> player is cursor, preparing item pickup dropdown');
                    showActionArea = true;
                    if (!_worldCursor.holding){
                        // Normal item pickup, not already holding anything
                        //if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="pickup-item" data-item="'+dataItem+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                        } else if (_worldCursor.holding && _worldCursor.holding === 'item/'+dataItem){
                        // We're holding something and this is that item, we should allow dropping it
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="drop-item" data-item="'+dataItem+'"><span><sup>Put Down</sup> ' + dataLabel + '</span></a>';
                        } else if (_worldCursor.holding){
                        // Otherwise we're holding something else, so we should not allow picking up this item
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-item="'+dataItem+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                        }
                    sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                    showActionAreaType = 'item';
                    zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                    }
                // Otherwise if this is a human player, we should trigger the auto-pickup functionality instead
                else {
                    //console.log('-> player is human player, trying auto-pickup for item...');
                    // If this item is NOT anchored, we can pick it up normally
                    if (!itemInfo.anchored){
                        //console.log('-> item is not anchored so we can add run normal pickup function');
                        triggerEffect = true;
                        readyTeamSprites = true;
                        teamReadyDuration = 600; // for event panels we want to zoom in quickly
                        zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                        triggerEffectFunction = function(){
                            if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                            //console.log('-> running');
                            // If the first event in the list (before sorting) is an item, we should defer it to the pickup function
                            //console.log('-> first event is an item, deferring to pickup function');
                            _self.triggerItemPickup(firstEvent, 600);
                            };
                        }
                    // Otherwise we just display a disabled "Pick Up" menu hinting the player should switch to cursor
                    else {
                        //console.log('-> item is anchored so we cannot pick it up, showing disabled dropdown');
                        showActionArea = true;
                        if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-item="'+dataItem+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                        sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                        showActionAreaType = 'item';
                        zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                        }
                    }
                }
            }
        else if (firstEventType === 'ability'){
            //console.log('-> event at position is an ability, preparing either dropdown or pickup');
            // If the cursor is literally on a button, only one event sprite matters right now
            let eventInfo = firstEvent;
            let abilityInfo = _mapAbilitiesIndex[eventInfo.token];
            let $abilityEvent = $(firstEvent.sprite);
            //console.log('-> eventInfo =', eventInfo, '| abilityInfo =', abilityInfo, '| $abilityEvent =', $abilityEvent);
            let dataLabel = $abilityEvent.attr('data-label');
            let dataAbility = $abilityEvent.attr('data-ability');
            let dataAbilityToken = $abilityEvent.attr('data-token');
            let dataColour = $abilityEvent.attr('data-colour');
            //console.log('-> dataAbility =', dataAbility, '| dataAbilityToken =', dataAbilityToken, '| dataColour =', dataColour);
            if (!dataLabel){ dataLabel = dataAbilityToken; }
            if (!dataColour){ dataColour = 'none'; }
            let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
            //console.log('-> playerIsCursor =', playerIsCursor);
            if (dataAbility && dataAbilityToken){
                // If the player is the cursor player, we should show the ability pickup dropdown
                if (playerIsCursor){
                    //console.log('-> player is cursor, preparing ability pickup dropdown');
                    showActionArea = true;
                    if (!_worldCursor.holding){
                        // Normal ability pickup, not already holding anything
                        //if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="pickup-ability" data-ability="'+dataAbility+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                        } else if (_worldCursor.holding && _worldCursor.holding === 'ability/'+dataAbility){
                        // We're holding something and this is that ability, we should allow dropping it
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+'" data-action="drop-ability" data-ability="'+dataAbility+'"><span><sup>Put Down</sup> ' + dataLabel + '</span></a>';
                        } else if (_worldCursor.holding){
                        // Otherwise we're holding something else, so we should not allow picking up this ability
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-ability="'+dataAbility+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                        }
                    sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                    showActionAreaType = 'button';
                    zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                    }
                // Otherwise if this is a human player, we should trigger the auto-pickup functionality instead
                else {
                    //console.log('-> player is human player, trying auto-pickup for ability...');
                    // If this ability is NOT anchored, we can pick it up normally
                    if (!abilityInfo.anchored){
                        //console.log('-> ability is not anchored so we can add run normal pickup function');
                        triggerEffect = true;
                        readyTeamSprites = true;
                        teamReadyDuration = 600; // for event panels we want to zoom in quickly
                        zoomTimeoutDuration = 600; // for event panels we want to zoom in quickly
                        triggerEffectFunction = function(){
                            if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                            //console.log('-> running');
                            // If the first event in the list (before sorting) is an ability, we should defer it to the pickup function
                            //console.log('-> first event is an ability, deferring to pickup function');
                            _self.triggerAbilityPickup(firstEvent, 600);
                            };
                        }
                    // Otherwise we just display a disabled "Pick Up" menu hinting the player should switch to cursor
                    else {
                        //console.log('-> ability is anchored so we cannot pick it up, showing disabled dropdown');
                        showActionArea = true;
                        if (dataLabel){ actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>'; }
                        sideButtonsMarkup += '<a class="button big-button'+(dataColour ? ' type '+dataColour : '')+' disabled" data-ability="'+dataAbility+'"><span><sup>Pick Up</sup> ' + dataLabel + '</span></a>';
                        sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                        showActionAreaType = 'ability';
                        zoomTimeoutDuration = 750; // if we show a pick-up dropdown, we want to zoom in faster
                        }
                    }
                }
            }

        // If there's no dropdown to show, we can return early
        if (!showActionArea && !autoRedirect && !triggerEffect){ return; }

        // Define an inline function to put the team into their battle-ready poses
        let getTeamSpritesReady = function(){
            if (_self.worldIsBusy()){ return; }
            if (!stillAtPosition() || otherMenusActiveNow()){ return; }

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
            if (!stillAtPosition() || otherMenusActiveNow()){ return; }
            $thisWorld.addClass('hidden');
            if (autoRedirectSound){
                _self.playSoundEffect(autoRedirectSound);
                }
            if (autoRedirectURL){
                _self.incZoomLevel();
                _self.saveWorldState(function(){
                    if (_self.worldIsBusy()){ return; }
                    if (!stillAtPosition() || otherMenusActiveNow()){ return; }
                    else { _self.resetZoomLevel(); }
                    _self.incZoomLevel();
                    window.location.href = autoRedirectURL;
                    _self.incZoomLevel();
                    }, true, false);
                }
            return true;
            };

        // Define an inline function to zoom and show the dropdown which we'll call after a timeout
        let zoomAndShowDropdown = function(){
            //console.log('%c' + 'zoomAndShowDropdown()', 'color: cyan;');
            if (_self.worldIsBusy()){ return; }
            if (!stillAtPosition() || otherMenusActiveNow()){ return; }

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
                let isRescue = $eventSprite.hasClass('vs-rescue');
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
                    $eventSprite.filter(':not(.vs-rescue)').addClass('zoom');
                    $eventLayer.addClass('has-zoom');
                    if (newDirection){ $eventSprite.attr('data-dir', newDirection); }
                    if (isRobot){
                        if (isMecha){ $eventSprite.attr('data-frame', '04'); }
                        else if (isMaster){ $eventSprite.attr('data-frame', '01'); }
                        else if (isBoss){ $eventSprite.attr('data-frame', '06'); }
                        else if (isRescue){ $eventSprite.attr('data-frame', '08'); }
                        }
                    }, 100);
                }

            // Move the action dropdown to the correct position, add the markup, and show it
            if (actionAreaMarkup.length){
                $actionDropdown.css({
                    left: ((thisNewCol - 1) * _mapTileSize[0] + _mapTileSizeOffset[0]) + 'px',
                    top: ((thisNewRow - 1) * _mapTileSize[1] + _mapTileSizeOffset[1]) + 'px',
                    }).attr('data-dir', _worldCursor.direction).attr('data-type', showActionAreaType).attr('data-align', 'center');
                $actionDropdownWrapper.html(actionAreaMarkup);
                }

            // Add the buttons to the sidebar area so that they are out-of-the-way
            $sideButtonsWrapper.html(sideButtonsMarkup);

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
                $('.sprite[data-frame]:not(.disabled):not(.frame-lock)', $canvasMap).attr('data-frame', '00');
                };

            // Define the event to run when clicking one of these new action buttons
            let onActionButtonClick = function(e){
                //console.log('%c' + 'Action button clicked!', 'color: cyan;');
                //console.log('-> data-action =', $(this).attr('data-action'));
                e.preventDefault();
                let $button = $(this);
                let action = $button.attr('data-action') || false;
                if (!action){ console.error('-> no action found on button, skipping!'); return false; }
                let isBattle = action.indexOf('battle') !== -1;
                let isPortal = action.indexOf('portal') !== -1;
                let isButton = action.indexOf('button') !== -1;
                let isItem = action.indexOf('item') !== -1;
                let isAbility = action.indexOf('ability') !== -1;
                let isDismiss = action === 'dismiss';
                if (!isDismiss){ $button.addClass('clicked'); }
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
                        _self.incZoomLevel();
                        _self.saveWorldState(function(){
                            _self.incZoomLevel();
                            window.location.href = battleHref;
                            _self.incZoomLevel();
                            }, true, false);
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
                                }, true, false);
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
                        })(buttonInfo);
                    }
                else if (isItem){
                    //console.log('-> item button clicked with action:', action);
                    let itemSymbols = _config.mapItemSymbols;
                    let itemsIndex = _config.mapItemsIndex;
                    let itemClaims = _world.items;
                    let itemName = $button.attr('data-item') || false;
                    let itemInfo = itemName && (itemsIndex && itemsIndex[itemName]) ? itemsIndex[itemName] : false;
                    let itemPosition = itemInfo ? itemInfo.pos : false;
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                    //console.log('-> itemName =', itemName);
                    //console.log('-> itemInfo =', itemInfo);
                    //console.log('-> $eventSprite =', $eventSprite);
                    //console.log('-> $innerSprite =', $innerSprite);
                    if (!itemName || !itemInfo){ console.error('-> item name or info not found, cannot pick up item!'); return false; }
                    // dismiss the dropdown and side buttons first
                    dismissDropdown(false);
                    // collect refs to the cursor palette and temp item slots
                    let $cursorPalette = _elements.cursorPalette;
                    let $tempItemSlots = $('.slots.temp', $cursorPalette);
                    // if this is a pickup action, process normally
                    if (action === 'pickup-item'){
                        //console.log('-> picking up item:', itemName);
                        // check to make sure we have an open even slot to drop the item into
                        let $firstOpenTempSlot = $('.slot:not(.active)', $tempItemSlots).first();
                        if (!$firstOpenTempSlot.length){
                            console.error('-> no open temp slots found in palette, cannot pick up item!');
                            return false;
                            }
                        $cursorPalette.addClass('active');
                        $worldCursor.addClass('pickup');
                        // clone the item to the cursor palette to show it being picked up
                        let newTop = _config.worldHeight + 200;
                        let newLeft = (parseInt($eventSprite.css('left')) || 0) - 200;
                        let $clonedEventSprite = $eventSprite.clone();
                        setTimeout(function(){
                            $clonedEventSprite.css({top:'',left:'',zIndex:''}); // reset the event sprite position
                            $clonedEventSprite.appendTo($firstOpenTempSlot).addClass('new');; // move the event sprite to the temp item slot
                            $firstOpenTempSlot.addClass('active').attr('data-item', itemName);
                            setTimeout(function(){
                                $worldCursor.removeClass('pickup');
                                $clonedEventSprite.removeClass('new');
                                }, 3000);
                            }, 600);
                        // make the event sprite track the cursor's movement until we put it down
                        itemInfo.beingHeld = true; // set the item info tracking state to cursor
                        _worldCursor.holding = 'item/'+itemName; //{kind: 'item', name: itemName, item: itemInfo};
                        $eventSprite.addClass('tracking-cursor');
                        let targetLeft = parseInt($worldCursor.css('left') || 0) + 10;
                        let targetTop = parseInt($worldCursor.css('top') || 0) + 5;
                        let targetZ = parseInt($worldCursor.css('zIndex') || 0) - 1;
                        $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                        if ($eventSprite.is('.always-zoom')){ $eventSprite.addClass('not-always-zoom').removeClass('always-zoom'); }
                        // check to see if this spot below this was a drop-zone
                        //console.log('-> checking for event under picked-up item position:', itemPosition);
                        if (_mapEventSymbols[itemPosition]){
                            let eventUnderPosition = _mapEventSymbols[itemPosition];
                            let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                            //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                            if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                                //console.log('-> item picked-up from drop-zone event, triggering drop-zone empty action');
                                _self.triggerDropZoneEmpty(eventUnderPosition, eventUnderInfo);
                                }
                            }
                        }
                    // else if this is a drop action, we need to do a bit more work
                    else if (action === 'drop-item'){
                        //console.log('-> dropping item:', itemName);
                        // check to make sure we have an active temp item slot holding an item
                        let $firstActiveTempSlot = $('.slot.active', $tempItemSlots).first();
                        if (!$firstActiveTempSlot.length){
                            console.error('-> no active temp slots found in palette, cannot drop item!');
                            return false;
                            }
                        _worldCursor.busy = true; // mark the cursor as busy while we drop the item
                        $cursorPalette.removeClass('active');
                        $worldCursor.addClass('drop');
                        // remove the item from the cursor pallet first and formost
                        let $clonedEventSprite = $firstActiveTempSlot.find('.sprite');
                        $clonedEventSprite.addClass('dropped');
                        setTimeout(function(){
                            $firstActiveTempSlot.removeClass('active').attr('data-item', '');
                            setTimeout(function(){
                                $worldCursor.removeClass('drop');
                                $clonedEventSprite.remove();
                                }, 3000);
                            }, 600);
                        // detach the event sprite from the cursor's movement so that it's actually put down
                        _worldCursor.holding = '';
                        $eventSprite.removeClass('tracking-cursor');
                        let newItemPosition = _worldCursor.position;
                        let newItemPositionXY = newItemPosition.split('-');
                        let targetLeft = ((newItemPositionXY[0] - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0];
                        let targetTop = ((newItemPositionXY[1] - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1];
                        let targetZ = targetTop + 1;
                        $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                        if ($eventSprite.is('.not-always-zoom')){ $eventSprite.addClass('always-zoom').removeClass('not-always-zoom'); }
                        // update the event sprite position to the new position
                        let oldItemPosition = itemInfo.pos;
                        itemInfo.pos = newItemPosition; // update the item position in the info
                        itemInfo.position = newItemPositionXY; // update the item position in the info
                        itemInfo.col = parseInt(newItemPositionXY[0]) || 1; // update the item column in the info
                        itemInfo.row = parseInt(newItemPositionXY[1]) || 1; // update the item row in the info
                        itemInfo.beingHeld = false; // set the item info tracking state to not being held
                        delete itemSymbols[oldItemPosition];
                        itemSymbols[newItemPosition] = itemName; // update the event symbols with the new position
                        // save these changes to the world state
                        //console.log('-> saving item relocation to the world state!');
                        //console.log('-> ', itemName, ' moved from', oldItemPosition, 'to', newItemPosition);
                        if (typeof _worldSymbols.items === 'undefined'){ _worldSymbols.items = {}; }
                        _worldSymbols.items[itemName] = newItemPosition; // create a redirect pointer for the new item position
                        // check to see if this spot below this was a drop-zone
                        //console.log('-> checking for event under new item position:', newItemPosition);
                        if (_mapEventSymbols[newItemPosition]){
                            let eventUnderPosition = _mapEventSymbols[newItemPosition];
                            let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                            //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                            if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                                //console.log('-> item dropped onto drop-zone event, triggering drop-zone action for item:', itemName);
                                _self.triggerDropZoneEvent(eventUnderPosition, eventUnderInfo, 'item', itemName, itemInfo);
                                }
                            }
                        // save all these changes to the world state now
                        _self.saveWorldState(function(){
                            _worldCursor.busy = false;
                            });
                        }
                    }
                else if (isAbility){
                    //console.log('-> ability button clicked with action:', action);
                    let abilitySymbols = _config.mapAbilitySymbols;
                    let abilitiesIndex = _config.mapAbilitiesIndex;
                    let abilityClaims = _world.abilities;
                    let abilityName = $button.attr('data-ability') || false;
                    let abilityInfo = abilityName && (abilitiesIndex && abilitiesIndex[abilityName]) ? abilitiesIndex[abilityName] : false;
                    let abilityPosition = abilityInfo ? abilityInfo.pos : false;
                    let $eventSprite = $(firstEvent.sprite);
                    let $innerSprite = $eventSprite ? $('.sprite', $eventSprite) : false;
                    //console.log('-> abilityName =', abilityName);
                    //console.log('-> abilityInfo =', abilityInfo);
                    //console.log('-> $eventSprite =', $eventSprite);
                    //console.log('-> $innerSprite =', $innerSprite);
                    if (!abilityName || !abilityInfo){ console.error('-> ability name or info not found, cannot pick up ability!'); return false; }
                    // dismiss the dropdown and side buttons first
                    dismissDropdown(false);
                    // collect refs to the cursor palette and temp ability slots
                    let $cursorPalette = _elements.cursorPalette;
                    let $tempAbilitySlots = $('.slots.temp', $cursorPalette);
                    // if this is a pickup action, process normally
                    if (action === 'pickup-ability'){
                        //console.log('-> picking up ability:', abilityName);
                        // check to make sure we have an open even slot to drop the ability into
                        let $firstOpenTempSlot = $('.slot:not(.active)', $tempAbilitySlots).first();
                        if (!$firstOpenTempSlot.length){
                            console.error('-> no open temp slots found in palette, cannot pick up ability!');
                            return false;
                            }
                        $cursorPalette.addClass('active');
                        $worldCursor.addClass('pickup');
                        // clone the ability to the cursor palette to show it being picked up
                        let newTop = _config.worldHeight + 200;
                        let newLeft = (parseInt($eventSprite.css('left')) || 0) - 200;
                        let $clonedEventSprite = $eventSprite.clone();
                        setTimeout(function(){
                            $clonedEventSprite.css({top:'',left:'',zIndex:''}); // reset the event sprite position
                            $clonedEventSprite.appendTo($firstOpenTempSlot).addClass('new');; // move the event sprite to the temp ability slot
                            $firstOpenTempSlot.addClass('active').attr('data-ability', abilityName);
                            setTimeout(function(){
                                $worldCursor.removeClass('pickup');
                                $clonedEventSprite.removeClass('new');
                                }, 3000);
                            }, 600);
                        // make the event sprite track the cursor's movement until we put it down
                        abilityInfo.beingHeld = true; // set the ability info tracking state to cursor
                        _worldCursor.holding = 'ability/'+abilityName; //{kind: 'ability', name: abilityName, ability: abilityInfo};
                        $eventSprite.addClass('tracking-cursor');
                        let targetLeft = parseInt($worldCursor.css('left') || 0) + 10;
                        let targetTop = parseInt($worldCursor.css('top') || 0) + 5;
                        let targetZ = parseInt($worldCursor.css('zIndex') || 0) - 1;
                        $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                        if ($eventSprite.is('.always-zoom')){ $eventSprite.addClass('not-always-zoom').removeClass('always-zoom'); }
                        // check to see if this spot below this was a drop-zone
                        //console.log('-> checking for event under picked-up ability position:', abilityPosition);
                        if (_mapEventSymbols[abilityPosition]){
                            let eventUnderPosition = _mapEventSymbols[abilityPosition];
                            let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                            //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                            if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                                //console.log('-> ability picked-up from drop-zone event, triggering drop-zone empty action');
                                _self.triggerDropZoneEmpty(eventUnderPosition, eventUnderInfo);
                                }
                            }
                        }
                    // else if this is a drop action, we need to do a bit more work
                    else if (action === 'drop-ability'){
                        //console.log('-> dropping ability:', abilityName);
                        // check to make sure we have an active temp ability slot holding an ability
                        let $firstActiveTempSlot = $('.slot.active', $tempAbilitySlots).first();
                        if (!$firstActiveTempSlot.length){
                            console.error('-> no active temp slots found in palette, cannot drop ability!');
                            return false;
                            }
                        $cursorPalette.removeClass('active');
                        $worldCursor.addClass('drop');
                        // remove the ability from the cursor pallet first and formost
                        let $clonedEventSprite = $firstActiveTempSlot.find('.sprite');
                        $clonedEventSprite.addClass('dropped');
                        setTimeout(function(){
                            $firstActiveTempSlot.removeClass('active').attr('data-ability', '');
                            setTimeout(function(){
                                $worldCursor.removeClass('drop');
                                $clonedEventSprite.remove();
                                }, 3000);
                            }, 600);
                        // detach the event sprite from the cursor's movement so that it's actually put down
                        _worldCursor.holding = '';
                        $eventSprite.removeClass('tracking-cursor');
                        let newAbilityPosition = _worldCursor.position;
                        let newAbilityPositionXY = newAbilityPosition.split('-');
                        let targetLeft = ((newAbilityPositionXY[0] - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0];
                        let targetTop = ((newAbilityPositionXY[1] - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1];
                        let targetZ = targetTop + 1;
                        $eventSprite.css({left: targetLeft + 'px', top: targetTop + 'px', zIndex: targetZ});
                        if ($eventSprite.is('.not-always-zoom')){ $eventSprite.addClass('always-zoom').removeClass('not-always-zoom'); }
                        // update the event sprite position to the new position
                        let oldAbilityPosition = abilityInfo.pos;
                        abilityInfo.pos = newAbilityPosition; // update the ability position in the info
                        abilityInfo.position = newAbilityPositionXY; // update the ability position in the info
                        abilityInfo.col = parseInt(newAbilityPositionXY[0]) || 1; // update the ability column in the info
                        abilityInfo.row = parseInt(newAbilityPositionXY[1]) || 1; // update the ability row in the info
                        abilityInfo.beingHeld = false; // set the ability info tracking state to not being held
                        delete abilitySymbols[oldAbilityPosition];
                        abilitySymbols[newAbilityPosition] = abilityName; // update the event symbols with the new position
                        // save these changes to the world state
                        //console.log('-> saving ability relocation to the world state!');
                        //console.log('-> ', abilityName, ' moved from', oldAbilityPosition, 'to', newAbilityPosition);
                        if (typeof _worldSymbols.abilities === 'undefined'){ _worldSymbols.abilities = {}; }
                        _worldSymbols.abilities[abilityName] = newAbilityPosition; // create a redirect pointer for the new ability position
                        // check to see if this spot below this was a drop-zone
                        //console.log('-> checking for event under new ability position:', newAbilityPosition);
                        if (_mapEventSymbols[newAbilityPosition]){
                            let eventUnderPosition = _mapEventSymbols[newAbilityPosition];
                            let eventUnderInfo = _mapEventsIndex[eventUnderPosition];
                            //console.log('-> eventUnderPosition =', eventUnderPosition, '| eventUnderInfo =', eventUnderInfo);
                            if (eventUnderInfo && eventUnderInfo.action === 'drop-zone'){
                                //console.log('-> ability dropped onto drop-zone event, triggering drop-zone action for ability:', abilityName);
                                _self.triggerDropZoneEvent(eventUnderPosition, eventUnderInfo, 'ability', abilityName, abilityInfo);
                                }
                            }
                        // save all these changes to the world state now
                        _self.saveWorldState();
                        }
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
                if (showActionAreaSound){
                    let sound = showActionAreaSound, repeat = 1, delay = 0;
                    if (sound.indexOf('*') !== -1){ var parts = sound.split('*'); sound = parts[0]; repeat = parseInt(parts[1]); }
                    for (var i = 0; i < repeat; i++){
                        if (!delay){ _self.playSoundEffect(sound); delay += 50; }
                        else { setTimeout(function(){ _self.playSoundEffect(sound); }, delay); delay *= 2; }
                        }
                    }
                }, (200 * timeoutMultiplier));

            };

        // Make the cursor shake so it trembles a bit before the encounter
        if (_selfRef.teamSpritesTimeout){ clearTimeout(_selfRef.teamSpritesTimeout); }
        if (readyTeamSprites){ _selfRef.teamSpritesTimeout = setTimeout(getTeamSpritesReady, teamReadyDuration); }

        // If an effect is being triggered, run it and then exit here
        if (triggerEffect){
            //console.log('%c' + 'triggerEffectFunction()', 'color: cyan;');
            if (_selfRef.zoomEffectTimeout){ clearTimeout(_selfRef.zoomEffectTimeout); }
            _selfRef.zoomEffectTimeout = setTimeout(triggerEffectFunction, (zoomTimeoutDuration * timeoutMultiplier));
            return true;
            }

        // If a redirect was requested, this is where we exit actually
        if (autoRedirect){
            if (_selfRef.zoomRedirectTimeout){ clearTimeout(_selfRef.zoomRedirectTimeout); }
            _selfRef.zoomRedirectTimeout = setTimeout(redirectToLocation, (zoomTimeoutDuration * timeoutMultiplier));
            return true;
            }

        // Otherwise we can actually trigger the dropdown and zoom in on the events
        if (showActionArea){
            if (_selfRef.zoomDropdownTimeout){ clearTimeout(_selfRef.zoomDropdownTimeout); }
            _selfRef.zoomDropdownTimeout = setTimeout(zoomAndShowDropdown, (zoomTimeoutDuration * timeoutMultiplier));
            return true;
            }

        // Return true on success
        return true;
        }

    // Quick function that, given a column and row returns any events on or around that position on the map
    getEventsAtPosition(searchPosition, searchRadius, includeLocked){
        //console.log('%c' + 'mmrpgWorldMap.getEventsAtPosition(searchPosition:' + searchPosition + ', searchRadius:' + searchRadius + ')', 'color: magenta;');
        if (!searchPosition || (typeof searchPosition !== 'string' && !Array.isArray(searchPosition))){ console.error('getEventsAtPosition() missing or invalid searchPosition!'); return false; }
        searchPosition = typeof searchPosition !== 'string' ? searchPosition.join('-') : searchPosition; // join if provided as array
        searchRadius = typeof searchRadius === 'number' ? searchRadius : 1; // default to one if not provided
        includeLocked = typeof includeLocked === 'boolean' ? includeLocked : false; // default to false if not provided
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let $canvasMap = _elements.map;
        // Define an array to hold the events at this position
        let eventsAtPosition = [];
        // If we haven't actually moved/othered yet, don't return anything yet
        if (!_worldCursor.moved && !_worldCursor.othered){ return eventsAtPosition; }
        // Define an array to hold positions and always check the exact position first
        let positionsToCheck = [];
        positionsToCheck.push(searchPosition);
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
        //console.log('-> _worldCursor.moved =', _worldCursor.moved);
        //console.log('-> _worldCursor.othered =', _worldCursor.othered);
        let eventKinds = ['event', 'portal', 'button', 'battle', 'item', 'ability'];
        for (let e = 0; e < eventKinds.length; e++){
            let eventKind = eventKinds[e];
            let eventKindPlural = eventKind + 's';
            eventKindPlural = eventKindPlural.replace(/ys/i, 'ies'); // fix pluralization issues
            eventKindPlural = eventKindPlural.replace(/ss/i, 'ses'); // fix pluralization issues
            //console.log('checking for ' + eventKind+'s at: ' + positionsToCheck.join(', '));
            // ie: mapKindSymbols
            let symbolsKey = 'map' + (eventKind[0].toUpperCase() + eventKind.slice(1)) + 'Symbols';
            let indexKey = 'map' + (eventKindPlural[0].toUpperCase() + eventKindPlural.slice(1)) + 'Index';
            let eventSymbols = _config.hasOwnProperty(symbolsKey) ? _config[symbolsKey] : false;
            let eventsIndex = _config.hasOwnProperty(indexKey) ? _config[indexKey] : false;
            let eventKeys = eventSymbols ? Object.keys(eventSymbols) : [];
            //console.log('-> symbolsKey =', symbolsKey);
            //console.log('-> indexKey =', indexKey);
            //console.log('-> eventSymbols =', eventSymbols);
            //console.log('-> eventsIndex =', eventsIndex);
            //console.log('-> eventKeys =', eventKeys);
            //console.log('-> ' + symbolsKey + ' =', eventSymbols);
            //console.log('-> ' + symbolsKey + ' =', eventKeys);
            if (!eventKeys.length){ continue; }
            // Loop through the event symbols and check if any of them are at the positions to check
            for (let i = 0; i < positionsToCheck.length; i++){
                let eventPosition = positionsToCheck[i];
                let eventPositionXY = eventPosition.split('-');
                //console.log('-> checking ' + symbolsKey + ' for ' + eventPosition);
                if (!eventSymbols[eventPosition]){ continue; } // skip if no event symbols at this position
                let eventToken = eventSymbols[eventPosition];
                let eventInfo = eventsIndex[eventToken];
                if (!eventToken || !eventInfo){ console.warn('-> no event token or info found for ' + eventKind + ' at position ' + eventPosition + ', skipping!'); continue; }
                //console.log('-> found ' + eventKind + ' at position ' + eventPosition + ' with token ' + eventToken, eventInfo);
                if (eventInfo.disabled){ continue; }
                if (eventInfo.beingHeld){ continue; }
                if (eventInfo.locked && !includeLocked){ continue; }
                if (eventInfo.action === 'drop-zone'){ continue; }
                let $eventSprite = $('.sprite[data-' + eventKind + '="'+eventToken+'"]', $canvasMap);
                let eventLabel = $eventSprite.length ? $eventSprite.attr('data-label') : '';
                if ($eventSprite && $eventSprite.length){ $eventSprite = $eventSprite.first().get(0); }
                let eventKind2 = eventKind;
                if (eventKind === 'battle'){
                    // make sure we take the secondary type (mecha/master/boss/rescue) as the second "kind"
                    eventKind2 = eventInfo.kind2;
                    }
                else if (eventKind === 'event'){
                    // treat healpads and resetpads as sanctuary events
                    if (eventInfo.sprite === 'healpad' || eventInfo.sprite === 'resetpad'){ eventKind2 = 'sanctuary'; }
                    // otherwise it's just a generic custom event tile
                    else { eventKind2 = 'custom'; }
                    }
                else if (eventKind === 'button'){
                    // if the button has already been pushed (state:down), just continue
                    //console.log('-> eventPosition: ', eventPosition);
                    //console.log('-> eventToken: ', eventToken);
                    //console.log('-> eventInfo: ', eventInfo);
                    //console.log('-> eventsIndex: ', eventsIndex);
                    if (eventInfo.state === 'down'){ continue; }
                    }
                else if (eventKind === 'portal'){
                    // if we haven't moved, never trigger a portal
                    if (!_worldCursor.moved){ continue; }
                    // spawns are usually hidden behind other portals, never interactable directly
                    if (eventToken === 'spawn'){ continue; }
                    // if this portal has an assosiated direction, only trigger if player is facing that way
                    if (eventInfo.direction
                        && typeof eventInfo.direction === 'string'
                        && eventInfo.direction !== _worldCursor.direction){
                        continue;
                        }
                    }
                else if (eventKind === 'item' || eventKind === 'ability'){
                    // skip if already claimed by the player
                    //console.log('-> eventPosition: ', eventPosition);
                    //console.log('-> eventToken: ', eventToken);
                    //console.log('-> eventInfo: ', eventInfo);
                    //console.log('-> eventsIndex: ', eventsIndex);
                    if (eventInfo.claimed){ continue; }
                    // collect the token as the second "kind"
                    eventKind2 = eventInfo.token;
                    }
                let eventAtPosition = {kind: eventKind, kind2: eventKind2, position: eventPosition, token: eventToken, sprite: $eventSprite, label: eventLabel};
                if (eventKind === 'item' || eventKind === 'ability'){ eventAtPosition.claimed = eventInfo.claimed; }
                //console.log('%c' + '> found valid '+ eventKind + '/'+ eventKind2 + ' at position ' + eventPosition, 'color: lime;');
                //console.log('--> eventToken =', eventToken);
                //console.log('--> eventInfo =', eventInfo);
                //console.log('--> eventAtPosition =', eventAtPosition);
                // skip portals unless it's the exact position
                let eventIsCustom = eventKind === 'event';
                let eventIsPortal = eventKind === 'portal';
                let eventIsSanctuary = eventKind2 === 'sanctuary';
                let eventIsPickup = eventKind === 'item' || eventKind === 'ability';
                //console.log('--> eventIsCustom =', eventIsCustom, '| eventIsPortal =', eventIsPortal, '| eventIsSanctuary =', eventIsSanctuary, '| eventIsPickup =', eventIsPickup);
                if (eventPosition !== searchPosition
                    && (eventIsCustom || eventIsPortal || eventIsSanctuary || eventIsPickup)){
                    // skip custom unless it's the exact position
                    //console.log('----> skipping ' + eventKind + ' at ' + eventPosition + ' because it is not the exact position');
                    continue;
                    }
                // otherwise we are fine to add to the events array
                //console.log('--> adding ' + eventKind + ' at ' + eventPosition + ' to eventsAtPosition array', '\n--> w/ eventAtPosition = ', eventAtPosition);
                eventsAtPosition.push(eventAtPosition);
                }
            // Check to see if the cursor is holding any events, if so they are "at this position"
            // (putting this last so that the cursor can't drop items where another event already is)
            //console.log('-> checking cursor holding for ' + eventKind + ' at position ' + searchPosition);
            //console.log('-> but only if no events found in eventsAtPosition =', eventsAtPosition.length, eventsAtPosition);
            if (!eventsAtPosition.length && _worldCursor.holding && _worldCursor.holding.indexOf(eventKind + '/') === 0){
                //console.log('-> cursor is holding an event of kind ' + eventKind + ', adding to eventsAtPosition array');
                let eventToken = _worldCursor.holding.split('/')[1] || false;
                let eventInfo = eventsIndex[eventToken] || false;
                //console.log('-> eventToken =', eventToken);
                //console.log('-> eventInfo =', eventInfo);
                let $eventSprite = $('.sprite[data-' + eventKind + '="'+eventToken+'"]', $canvasMap);
                let eventLabel = $eventSprite.length ? $eventSprite.attr('data-label') : '';
                if ($eventSprite && $eventSprite.length){ $eventSprite = $eventSprite.first().get(0); }
                let eventKind2 = eventKind;
                if (eventKind === 'battle'){
                    // make sure we take the secondary type (mecha/master/boss/rescue) as the second "kind"
                    eventKind2 = eventInfo.kind2;
                    }
                else if (eventKind === 'event'){
                    // treat healpads and resetpads as sanctuary events
                    if (eventInfo.sprite === 'healpad' || eventInfo.sprite === 'resetpad'){ eventKind2 = 'sanctuary'; }
                    // otherwise it's just a generic custom event tile
                    else { eventKind2 = 'custom'; }
                    }
                else if (eventKind === 'item' || eventKind === 'ability'){
                    // skip if already claimed by the player
                    if (eventInfo.claimed){ continue; }
                    // collect the token as the second "kind"
                    eventKind2 = eventInfo.token;
                    }
                let eventAtPosition = {kind: eventKind, kind2: eventKind2, position: searchPosition, token: eventToken, sprite: $eventSprite, label: eventLabel};
                if (eventKind === 'item' || eventKind === 'ability'){ eventAtPosition.claimed = eventInfo.claimed; }
                eventsAtPosition.push(eventAtPosition);
                continue; // skip the rest of this loop
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
        let $vsRescues = $('.sprite.vs-rescue', $canvasMap);
        $vsMechas.addClass('march');
        $vsBosses.addClass('march');
        $vsRescues.addClass('shake');
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
            $vsRescues = $('.sprite.vs-rescue:not(.zoom)', $canvasMap);
            $vsRescues.each(function(){
                let $rescue = $(this);
                if ($rescue.data('cooldown') && $rescue.data('cooldown') > 0){
                    $rescue.data('cooldown', $rescue.data('cooldown') - 1);
                    return true;
                    }
                let curDir = $rescue.attr('data-dir') || 'right';
                let newDir = curDir === 'right' ? 'left' : 'right';
                //let curFrame = $rescue.attr('data-frame') || '09';
                //let newFrame = curFrame !== '08' ? '08' : '09';
                let changed = false;
                let changeDirection = Math.random() > 0.5 ? true : false;
                let changeFrame = Math.random() > 0.5 ? true : false;
                if (changeDirection){ $rescue.attr('data-dir', newDir); changed = true; }
                //if (changeFrame){ $rescue.attr('data-frame', newFrame); changed = true; }
                if (!changed){ return true; }
                let randCooldown = 4 + Math.ceil(Math.random() * 6);
                $rescue.data('cooldown', randCooldown);
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
    saveWorldState(callback, delay, pullEvents){
        //console.log('%c' + 'mmrpgWorldMap.saveWorldState(callback:' + (callback ? typeof callback : 'false') + ', delay:' + (delay ? delay : typeof delay) + ', pullEvents:' + (pullEvents ? 'true' : 'false') + ')', 'color: magenta;');
        callback = (typeof callback === 'function' ? callback : false);
        delay = (typeof delay === 'number' ? delay : 1) * 1000; // default to one second if not provided/invalid
        pullEvents = typeof pullEvents === 'boolean' ? pullEvents : true; // default to true if not provided/invalid
        let _self = this;
        let _selfRef = _self.saveWorldState;
        if (typeof _selfRef._scheduled === 'undefined'){ _selfRef._scheduled = null; }
        if (typeof _selfRef._callbacks === 'undefined'){ _selfRef._callbacks = []; }
        if (_selfRef._scheduled){ clearTimeout(_selfRef._scheduled); }
        if (callback){ _selfRef._callbacks.push(callback); }
        //console.log('-> scheduling world state save in ' + delay + 'ms');
        _selfRef._scheduled = setTimeout(function(){
            if (_selfRef._busy){
                // if busy, try again in one second
                //console.log('%c' + '--> save in progress, calling saveWorldState() again in ' + delay + 'ms ...', 'color: orange;');
                _self.saveWorldState(false, delay, pullEvents);
                } else {
                // not busy so we can save for real now
                _self.saveWorldStateForReal(pullEvents);
                }
            }, delay);
        return;
        }
    saveWorldStateForReal(pullEvents){
        //console.log('%c' + 'mmrpgWorldMap.saveWorldStateForReal(pullEvents:' + (pullEvents ? 'true' : 'false') + ')', 'color: magenta;');
        let _self = this;
        let _selfRef = _self.saveWorldState;
        let _config = _self.config;
        let _userId = _config.userId;
        let _world = _self.state;
        //let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldButtons = _world.buttons;
        let _worldSwitches = _world.switches;
        let _worldItems = _world.items;
        let _worldAbilities = _world.abilities;
        let _worldSymbols = _world.symbols;
        let lastPlayer = _worldPlayer.token;
        let lastPlayerRobots = _worldPlayer.robots;
        let lastPlayerAbilities = _worldPlayer.abilities;
        let lastPlayerItems = _worldPlayer.items;
        let lastPlayerWorld = _config.mapWorld;
        let lastPlayerWorldMap = _config.mapWorld + '__' + _config.mapToken;
        let lastPlayerPosition = _worldPlayer.position;
        let lastPlayerDirection = _worldPlayer.direction;
        let lastWorldButtons = {}; lastWorldButtons[lastPlayerWorldMap] = _worldButtons;
        let lastWorldSwitches = {}; lastWorldSwitches[lastPlayerWorldMap] = _worldSwitches;
        let lastWorldItems = {}; lastWorldItems[lastPlayerWorldMap] = _worldItems;
        let lastWorldAbilities = {}; lastWorldAbilities[lastPlayerWorldMap] = _worldAbilities;
        let lastWorldSymbols = {}; lastWorldSymbols[lastPlayerWorldMap] = _worldSymbols;
        let worldData = {
            lastPlayer,
            lastPlayerRobots,
            lastPlayerAbilities,
            lastPlayerItems,
            lastPlayerWorld,
            lastPlayerWorldMap,
            lastPlayerPosition,
            lastPlayerDirection,
            lastWorldButtons,
            lastWorldSwitches,
            lastWorldItems,
            lastWorldAbilities,
            lastWorldSymbols,
            };
        let callbackQueue = _selfRef._callbacks;
        let callbackReturn = [];
        let triggerSaveCallbacks = function(returnData){
            if (!callbackQueue.length){ return []; }
            do {
                //console.log('-> executing saved callback #' + (callbackQueue.length) + ' of ' + (callbackQueue.length));
                let callback = callbackQueue.shift();
                callbackReturn.push(callback.call(_self, 'success', returnData));
                //console.log('-> remaining callbackQueue:', callbackQueue.length);
                } while (callbackQueue.length);
            return callbackReturn;
            };
        //console.log('%c' + 'Saving World State ...', 'color: orange;');
        //console.log('w/ pullEvents:', pullEvents);
        //console.log('w/ worldData:', worldData);
        //console.log('w/ callbackQueue:', callbackQueue);
        _selfRef._busy = true;
        $.ajax({
            url: 'world.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'save', world_data: worldData },
            success: function(response){
                //console.log('%c' + '... World State Saved!', 'color: green;');
                //console.log('saveWorldState() returned successfully! w/', '\n-> response:', response);
                _selfRef._busy = false;
                triggerSaveCallbacks({response});
                if (pullEvents){ _self.triggerWindowEventsPull(0); }
                },
            error: function(xhr, status, error){
                //console.log('%c' + '... World State Not Saved!', 'color: red;');
                console.error('saveWorldState() failed to save world state! w/', '\n-> status:', status, '\n-> error:', error);
                _selfRef._busy = false;
                triggerSaveCallbacks({xhr, status, error});
                if (pullEvents){ _self.triggerWindowEventsPull(0); }
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
        let _worldPlayer = _world.player;
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
        let actionsCompleted = 0;
        let processEventAction = function(eventAction, onComplete, afterDelay){
            if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
            if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
            // Process the event action based on it's token
            if (eventAction === 'trigger-effects'){
                //console.log('-> triggering effects for event with data:', eventData);
                _self.playSoundEffect('use-recovery-item');
                let _playerRobots = _config.playerRobots || [];
                let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
                let eventEffects = Object.values(eventData);
                for (let i = 0; i < eventEffects.length; i++){
                    let effect = eventEffects[i];
                    //console.log('-> effect =', effect);
                    if (!effect){ continue; }
                    // If this is a team-wide effect, we're going to have to loop
                    if (effect.indexOf('-team-') !== -1){
                        // If this is a cursor player, we don't have a team
                        if (playerIsCursor){ continue; }
                        //console.log('%c' + '-> team-effect via event panel: ' + effect, 'color: lime;');
                        for (let j = 0; j < _playerRobots.length; j++){
                            let robot = _playerRobots[j];
                            // If this is a RESTORE TEAM ENERGY effect, let's process that now
                            if (effect === 'restore-team-energy'){
                                //console.log('%c' + '-> restoring energy for ' + robot + ' via event panel', 'color: #64a455;');
                                _self.restoreRobotEnergy(robot, true);
                                _self.playSoundEffect('recovery-energy');
                                actionsCompleted++;
                                }
                            // If this is a RESTORE TEAM WEAPONS effect, let's process that now
                            if (effect === 'restore-team-weapons'){
                                //console.log('%c' + '-> restoring weapons for ' + robot + ' via event panel', 'color: #3d7cbe;');
                                _self.restoreRobotWeapons(robot, true);
                                _self.playSoundEffect('recovery-weapons');
                                actionsCompleted++;
                                }
                            // If this is a RESET TEAM ATTACK effect, let's process that now
                            if (effect === 'reset-team-attack'){
                                //console.log('%c' + '-> resetting attack for ' + robot + ' via event panel', 'color: #8b5050;');
                                _self.resetRobotAttack(robot, false);
                                _self.playSoundEffect('small-buff-received');
                                actionsCompleted++;
                                }
                            // If this is a RESET TEAM DEFENSE effect, let's process that now
                            if (effect === 'reset-team-defense'){
                                //console.log('%c' + '-> resetting ' + robot + ' defense for event panel', 'color: #50638a;');
                                _self.resetRobotDefense(robot, false);
                                _self.playSoundEffect('small-buff-received');
                                actionsCompleted++;
                                }
                            // If this is a RESET TEAM SPEED effect, let's process that now
                            if (effect === 'reset-team-speed'){
                                //console.log('%c' + '-> resetting ' + robot + ' speed for event panel', 'color: #8b739b;');
                                _self.resetRobotSpeed(robot, false);
                                _self.playSoundEffect('small-buff-received');
                                actionsCompleted++;
                                }
                            }
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
        if (actionsCompleted){ _self.incZoomLevel(); }
        setTimeout(function(){
            processEventAction(eventAction, function(){
                if (actionsCompleted){ _self.resetZoomLevel(); }
                $teamSprites.removeClass('shake');
                $teamSprites.filter(':not(.disabled):not(.frame-lock)').attr('data-frame', '00');
                _self.triggerWindowEventsPull();
                }, delayTime);
            }, delayTime);
        // Return true on success
        return true;
        }

    // Quick function for triggering a drop zone event (if available) when an item, ability, or other compatible item is dropped there
    triggerDropZoneEvent(eventName, eventInfo, objectKind, objectName, objectInfo){
        //console.log('%c' + 'mmrpgWorldMap.triggerDropZoneEvent(' + eventName + ', ' + objectKind + ', ' + objectName + ')', 'color: magenta;');
        if (!eventName || typeof eventName !== 'string' || !eventName.length){ console.error('triggerDropZoneEvent() missing required eventName!'); return false; }
        if (!eventInfo || typeof eventInfo !== 'object' || !Object.keys(eventInfo).length){ console.error('triggerDropZoneEvent() missing required eventInfo!'); return false; }
        if (!objectKind || typeof objectKind !== 'string' || !objectKind.length){ console.error('triggerDropZoneEvent() missing required objectKind!'); return false; }
        else if (objectKind !== 'item' && objectKind !== 'ability'){ console.error('triggerDropZoneEvent() invalid objectKind provided: ' + objectKind); return false; }
        if (!objectName || typeof objectName !== 'string' || !objectName.length){ console.error('triggerDropZoneEvent() missing required objectName!'); return false; }
        if (!objectInfo || typeof objectInfo !== 'object' || !Object.keys(objectInfo).length){ console.error('triggerDropZoneEvent() missing required objectInfo!'); return false; }
        if (!objectInfo.token || typeof objectInfo.token !== 'string' || !objectInfo.token.length){ console.error('triggerDropZoneEvent() missing required objectInfo.token!'); return false; }
        let rawObjectToken = objectInfo.token;
        let objectToken = rawObjectToken.indexOf('__') !== -1 ? rawObjectToken.split('__')[0] : rawObjectToken;
        //console.log('-> eventName =', eventName);
        //console.log('-> eventInfo =', eventInfo);
        //console.log('-> objectKind =', objectKind);
        //console.log('-> objectName =', objectName);
        //console.log('-> objectInfo =', objectInfo);
        //console.log('-> objectToken =', objectToken);
        //console.log('-> rawObjectToken =', rawObjectToken);
        // Collect references to world objects
        let _self = this;
        let _selfRef = _self.triggerDropZoneEvent;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let $canvasMap = _elements.map;
        let $eventSprite = $('.sprite[data-event="' + eventName + '"]', $canvasMap);
        if (!$eventSprite || !$eventSprite.length){ console.error('triggerDropZoneEvent() could not find event sprite for eventName: ' + eventName); return false; }
        // Collect the parameters from the event data
        let dropFilter = eventInfo.data[0] || false;
        let dropAction = eventInfo.data[1] || false;
        //console.log('-> dropFilter =', dropFilter);
        //console.log('-> dropAction =', dropAction);
        if (!dropFilter){ console.error('triggerDropZoneEvent() invalid dropFilter provided in eventInfo.data[0]: ' + dropFilter); return false; }
        if (!dropAction){ console.error('triggerDropZoneEvent() invalid dropAction provided in eventInfo.data[1]: ' + dropAction); return false; }
        dropFilter = dropFilter.indexOf(':') !== -1 ? dropFilter.split(':') : [dropFilter];
        let dropFilterKind = (dropFilter[0] || '');
        let dropFilterValues = (dropFilter[1] || '').split(',');
        //console.log('-> dropFilterKind =', dropFilterKind);
        //console.log('-> dropFilterValues =', dropFilterValues);
        //console.log('-> vs. objectKind =', objectKind);
        //console.log('-> vs. objectToken =', objectToken);
        // If the filter kind was not empty, and the provided objected does not match it, return now
        if (dropFilterKind.length){
            if (dropFilterKind !== objectKind){
                console.warn('triggerDropZoneEvent() exiting early because objectKind does not match dropFilterKind!');
                return false;
                }
            if (dropFilterValues.length && dropFilterValues.indexOf(objectToken) === -1){
                console.warn('triggerDropZoneEvent() exiting early because objectInfo.token does not match dropFilterValues!');
                return false;
                }
            }
        //console.log('-> drop action is valid, processing ...');
        // Define an inline function for processing the different drop actions possible
        let actionsCompleted = 0;
        let processDropAction = function(dropAction, onComplete, afterDelay){
            if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
            if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
            // Process the drop action based on it's token
            if (dropAction === 'activate-player-platform'){
                //console.log('-> this drop zone is a player-platform awaiting activation...');
                //console.log('-> activating player platform for player', _worldPlayer.token, 'at position', _worldPlayer.position);
                $eventSprite.addClass('active');
                eventInfo.active = true;
                objectInfo.locked = true;
                _self.refreshPlayerPlatforms();
                actionsCompleted++;
                }
            // If an onComplete function was provided, call it now (with delay if requested)
            if (onComplete){
                if (!afterDelay){ onComplete.call(_self, dropAction, eventInfo, objectKind, objectName, objectInfo); }
                else { setTimeout(function(){ onComplete.call(_self, dropAction, eventInfo, objectKind, objectName, objectInfo); }, afterDelay); }
                }
            };
        // Now process the drop action given the action and data provided after some visual fluff
        let delayTime = 1000;
        if (actionsCompleted){ _self.incZoomLevel(); }
        setTimeout(function(){
            processDropAction(dropAction, function(){
                if (actionsCompleted){ _self.resetZoomLevel(); }
                _self.triggerWindowEventsPull();
                }, delayTime);
            }, delayTime);
        // Return true on success
        return true;
        }

    // Quick function for triggering a drop zone empty event (if necessary) when an item, ability, or other item is removed from this location
    triggerDropZoneEmpty(eventName, eventInfo){
        //console.log('%c' + 'mmrpgWorldMap.triggerDropZoneEmpty(' + eventName + ')', 'color: magenta;');
        if (!eventName || typeof eventName !== 'string' || !eventName.length){ console.error('triggerDropZoneEmpty() missing required eventName!'); return false; }
        if (!eventInfo || typeof eventInfo !== 'object' || !Object.keys(eventInfo).length){ console.error('triggerDropZoneEmpty() missing required eventInfo!'); return false; }
        let rawObjectToken = eventInfo.data[0] || false;
        let objectToken = rawObjectToken && rawObjectToken.indexOf('__') !== -1 ? rawObjectToken.split('__')[0] : rawObjectToken;
        //console.log('-> eventName =', eventName);
        //console.log('-> eventInfo =', eventInfo);
        //console.log('-> objectToken =', objectToken);
        // Collect references to world objects
        let _self = this;
        let _selfRef = _self.triggerDropZoneEmpty;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let $canvasMap = _elements.map;
        let $eventSprite = $('.sprite[data-event="' + eventName + '"]', $canvasMap);
        if (!$eventSprite || !$eventSprite.length){ console.error('triggerDropZoneEvent() could not find event sprite for eventName: ' + eventName); return false; }
        // Collect the parameters from the event data
        let dropFilter = eventInfo.data[0] || false;
        let dropAction = eventInfo.data[1] || false;
        //console.log('-> dropFilter =', dropFilter);
        //console.log('-> dropAction =', dropAction);
        if (!dropFilter){ console.error('triggerDropZoneEvent() invalid dropFilter provided in eventInfo.data[0]: ' + dropFilter); return false; }
        if (!dropAction){ console.error('triggerDropZoneEvent() invalid dropAction provided in eventInfo.data[1]: ' + dropAction); return false; }
        // We don't really care about the filter, but we do care about the action so let's reverse it if it exists
        //console.log('-> drop action is valid, reverting ...');
        // Define an inline function for reverting the different drop actions possible
        let actionsReverted = 0;
        let processDropRevert = function(dropAction, onComplete, afterDelay){
            if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
            if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
            // Process the drop action based on it's token
            if (dropAction === 'activate-player-platform'){
                //console.log('-> this drop zone is a player-platform awaiting deactivation...');
                //console.log('-> deactivating player platform for player', _worldPlayer.token, 'at position', _worldPlayer.position);
                $eventSprite.removeClass('active');
                eventInfo.active = false;
                _self.refreshPlayerPlatforms();
                actionsReverted++;
                }
            // If an onComplete function was provided, call it now (with delay if requested)
            if (onComplete){
                if (!afterDelay){ onComplete.call(_self, dropAction, eventInfo); }
                else { setTimeout(function(){ onComplete.call(_self, dropAction, eventInfo); }, afterDelay); }
                }
            };
        // Now process the drop action given the action and data provided after some visual fluff
        let delayTime = 1000;
        if (actionsReverted){ _self.incZoomLevel(); }
        setTimeout(function(){
            processDropRevert(dropAction, function(){
                if (actionsReverted){ _self.resetZoomLevel(); }
                _self.triggerWindowEventsPull();
                }, delayTime);
            }, delayTime);
        // Return true on success
        return true;
        }

    // Quick function for checking if any player platforms are on this map and their drop-status
    // Basically, we check each one to see if all parts of "active" status and if so, that means
    // the platform has been activated and that player can be unlocked (we just need to reload)
    async refreshPlayerPlatforms(){
        //console.log('%c' + 'mmrpgWorldMap.refreshPlayerPlatforms()', 'color: magenta;');
        // Collect references to world objects
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _mapEventSymbols = _config.mapEventSymbols;
        let _mapEventsIndex = _config.mapEventsIndex;
        let _mapEventsIndexKeys = Object.keys(_mapEventsIndex);
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        //console.log('-> _mapEventSymbols =', _mapEventSymbols);
        //console.log('-> _mapEventsIndex =', _mapEventsIndex);
        if (!_mapEventSymbols || !Object.keys(_mapEventSymbols).length){ return; }
        if (!_mapEventsIndex || !Object.keys(_mapEventsIndex).length){ return; }
        // First scan to see if we have any player platforms to review
        let hasPlatforms = false;
        let playerPlatforms = {};
        //console.log('-> scanning ' + _mapEventsIndexKeys.length + ' map events for player platforms ...');
        for (var i = 0; i < _mapEventsIndexKeys.length; i++){
            //console.log('-- refreshPlayerPlatforms checking event index ' + i + ' of ' + _mapEventsIndexKeys.length);
            let eventKey = _mapEventsIndexKeys[i];
            let eventInfo = _mapEventsIndex[eventKey];
            let eventPosition = eventInfo.pos;
            let eventActive = eventInfo.active;
            //console.log('-> eventKey =', eventKey);
            //console.log('-> eventInfo =', eventInfo);
            //console.log('-> eventPosition =', eventPosition);
            //console.log('-> eventActive =', eventActive);
            if (!eventInfo.action || eventInfo.action !== 'drop-zone'){ continue; }
            if (!eventInfo.sprite || !eventInfo.sprite.match(/^(light|wily|cossack|lalinde)pad-/i)){ continue; }
            //console.log('-> found player platform event:', eventInfo);
            hasPlatforms = true;
            let platformKind = eventInfo.sprite.split('-')[0]
            let playerToken = 'dr-' + platformKind.substring(0, -3);
            //console.log('-> platformKind =', platformKind);
            //console.log('-> playerToken =', playerToken);
            if (typeof playerPlatforms[playerToken] === 'undefined'){ playerPlatforms[playerToken] = {}; }
            playerPlatforms[playerToken][eventPosition] = eventActive ? 1 : 0;
            }
        //console.log('-> hasPlatforms =', hasPlatforms);
        //console.log('-> playerPlatforms =', playerPlatforms);
        if (hasPlatforms && Object.keys(playerPlatforms).length){
            let playerPlatformsKeys = Object.keys(playerPlatforms);
            for (var j = 0; j < playerPlatformsKeys.length; j++){
                //console.log('-- refreshPlayerPlatforms checking player platforms ' + j + ' of ' + playerPlatformsKeys.length);
                let playerToken = playerPlatformsKeys[j];
                let platformParts = Object.values(playerPlatforms[playerToken]);
                let platformActive = platformParts.length && platformParts.indexOf(0) === -1 ? true : false;
                //console.log('-> playerToken =', playerToken);
                //console.log('-> platformParts =', platformParts);
                //console.log('-> platformActive =', platformActive);
                if (!platformActive){ continue; }
                // If we made it here, this player's platform is active and they can be unlocked
                //console.log('%c' + '-> player ' + playerToken + ' has an active platform and can be unlocked!', 'color: lime;');
                _worldCursor.busy = true;
                _worldCursor.loading = true;
                $thisWorld.addClass('busy');
                _self.incZoomLevel();
                _self.saveWorldState(function(){
                    //_self.incZoomLevel();
                    _self.resetZoomLevel();
                    _self.animateZoomToMax(4, 0.25, 600);
                    $thisWorld.addClass('hidden');
                    window.location.reload();
                    }, true, false);
                }
            }

        // Return true on success
        return true;
        }

    // Quick function for running a callback (first arg) after a condition (second arg, also a callback) is met (returns true)
    dontRunUntil(onReady, checkCondition, checkInterval, maxWait){
        //console.log('%c' + 'mmrpgWorldMap.dontRunUntil(onReady, checkCondition, checkInterval, maxWait)', 'color: magenta;');
        if (!onReady || typeof onReady !== 'function'){ console.error('dontRunUntil() missing required onReady callback!'); return false; }
        if (!checkCondition || typeof checkCondition !== 'function'){ console.error('dontRunUntil() missing required checkCondition callback!'); return false; }
        checkInterval = (typeof checkInterval === 'number' && checkInterval > 0 ? checkInterval : 100); // default to 100ms if not provided/invalid
        maxWait = (typeof maxWait === 'number' && maxWait > 0 ? maxWait : 10000); // default to 10 seconds if not provided/invalid
        let _self = this;
        let _selfRef = _self.dontRunUntil;
        let timeWaited = 0;
        if (_selfRef._checking){ clearInterval(_selfRef._checking); }
        //console.log('-> checking condition every ' + checkInterval + 'ms for up to ' + maxWait + 'ms ...');
        _selfRef._checking = setInterval(function(){
            timeWaited += checkInterval;
            //console.log('-> checking condition, timeWaited = ' + timeWaited + 'ms ...');
            if (checkCondition.call(_self)){
                //console.log('%c' + '--> condition met, running onReady callback now ...', 'color: green;');
                clearInterval(_selfRef._checking);
                _selfRef._checking = false;
                return onReady.call(_self);
                }
            else if (timeWaited >= maxWait){
                //console.log('%c' + '--> maxWait reached, stopping checks and not running onReady callback!', 'color: red;');
                clearInterval(_selfRef._checking);
                _selfRef._checking = false;
                return false;
                }
            }, checkInterval);
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
        $('> i', $robotEnergyGuage).css({width: robotInfo.energyPercent + '%'}).removeClass().addClass(robotInfo.energyRating);
        // Add a restored class to this robot to show it being effected by the action
        if (playSound){ _self.playSoundEffect('recovery-energy'); }
        $robotOverview.addClass('energy-restored life-energy-restored');
        setTimeout(function(){ $robotOverview.removeClass('life-energy-restored'); }, 2000);
        setTimeout(function(){ $robotOverview.removeClass('energy-restored'); }, 3000);
        // Trigger a save of the world state to persist this change
        _self.saveWorldState();
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
        $robotOverview.addClass('energy-restored weapon-energy-restored');
        setTimeout(function(){ $robotOverview.removeClass('weapon-energy-restored'); }, 2000);
        setTimeout(function(){ $robotOverview.removeClass('energy-restored'); }, 3000);
        // Trigger a save of the world state to persist this change
        _self.saveWorldState();
        // Return true on success
        return true;
        }

    // Quick function for resetting a robot's stat mods for a given stat back to zero
    resetRobotStat(robotString, statToken, playSound){
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
    resetRobotAttack(robotString, playSound){
        //console.log('%c' + 'mmrpgWorldMap.resetRobotAttack(robot:' + robotString + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.resetRobotStat(robotString, 'attack', playSound);
        }
    resetRobotDefense(robotString, playSound){
        //console.log('%c' + 'mmrpgWorldMap.resetRobotDefense(robot:' + robotString + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.resetRobotStat(robotString, 'defense', playSound);
        }
    resetRobotSpeed(robotString, playSound){
        //console.log('%c' + 'mmrpgWorldMap.resetRobotSpeed(robot:' + robotString + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.resetRobotStat(robotString, 'speed', playSound);
        }

    // Quick function for boosting (incrementing) a given robots stat by a specific amount (up to max of +5)
    boostRobotStat(robotString, statToken, boostAmount, playSound){
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
        if (robotInfo[statModKey] && robotInfo[statModKey] >= statModMax){ return true; }
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
    boostRobotAttack(robotString, boostAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.boostRobotAttack(robot:' + robotString + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.boostRobotStat(robotString, 'attack', boostAmount, playSound);
        }
    boostRobotDefense(robotString, boostAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.boostRobotDefense(robot:' + robotString + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.boostRobotStat(robotString, 'defense', boostAmount, playSound);
        }
    boostRobotSpeed(robotString, boostAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.boostRobotSpeed(robot:' + robotString + ', amount:' + boostAmount + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.boostRobotStat(robotString, 'speed', boostAmount, playSound);
        }

    // Quick function for breaking (decrementing) a given robots stat by a specific amount (down to min of -5)
    breakRobotStat(robotString, statToken, breakAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.breakRobotStat(robot:' + robotString + ', stat:' + statToken + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
        if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('breakRobotStat() missing required robotString!'); return false; }
        if (!statToken || typeof statToken !== 'string' || !statToken.length){ console.error('breakRobotStat() missing required statToken!'); return false; }
        if (typeof breakAmount !== 'number' || isNaN(breakAmount) || breakAmount < 1){ console.error('breakRobotStat() missing or invalid breakAmount!'); return false; }
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
        if (!robotInfo){ console.error('breakRobotStat() could not find robot info for robot ' + robotString + '!'); return false; }
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
        if (robotInfo[statModKey] && robotInfo[statModKey] <= statModMin){ return true; }
        //console.log('-> looks like we can break ' + statToken + ' for ' + robotToken + '!');
        let robotHasMods = function(){ return (parseInt(robotInfo[statModKeys[0]]) + parseInt(robotInfo[statModKeys[1]]) + parseInt(robotInfo[statModKeys[2]])) !== 0 ? true : false; };
        //console.log('-> robotId =', robotId);
        //console.log('-> robotToken =', robotToken);
        //console.log('-> robotInfo =', robotInfo);
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
    breakRobotAttack(robotString, breakAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.breakRobotAttack(robot:' + robotString + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.breakRobotStat(robotString, 'attack', breakAmount, playSound);
        }
    breakRobotDefense(robotString, breakAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.breakRobotDefense(robot:' + robotString + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.breakRobotStat(robotString, 'defense', breakAmount, playSound);
        }
    breakRobotSpeed(robotString, breakAmount, playSound){
        //console.log('%c' + 'mmrpgWorldMap.breakRobotSpeed(robot:' + robotString + ', amount:' + breakAmount + ', sound:' + playSound + ')', 'color: magenta;');
        let _self = this; return _self.breakRobotStat(robotString, 'speed', breakAmount, playSound);
        }

    // Define a quick function for triggering a live item pickup on the field (and any effects that may have
    triggerItemPickup(itemEvent, zoomDelay){
        //console.log('%c' + 'mmrpgWorldMap.triggerItemPickup()', 'color: magenta;');
        //console.log('--> itemEvent =', itemEvent);
        if (!itemEvent || typeof itemEvent !== 'object'){ console.error('triggerItemPickup() missing required itemEvent!'); return false; }
        if (typeof itemEvent.sprite === 'undefined'){ console.error('triggerItemPickup() missing required itemEvent.sprite!'); return false; }
        if (itemEvent.claimed === true){ console.warn('triggerItemPickup() called for item that has already been claimed!'); return false; }
        // Collect local references to world objects
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldPlayerRobots = _worldPlayer.robots;
        let _worldItemStates = _world.items;
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
        // If the quantity is somehow less than one, return early
        if (!itemEventQuantity || itemEventQuantity < 1){ console.error('triggerItemPickup() called for item with quantity less than one!'); return false; }
        // Collect some information about the player too
        let numPlayerRobots = Object.keys(_worldPlayerRobots).length;
        if (!numPlayerRobots || numPlayerRobots < 1){ console.error('triggerItemPickup() could not find any player robots!'); return false; }
        // First zoom the item sprite into the zoom layer so it's more visible to the player
        //console.log('-> zooming item sprite make it more visible');
        zoomDelay = typeof zoomDelay === 'number' ? zoomDelay : 1200; // default to sync with standard use-case
        setTimeout(function(){
            $itemEventSprite.addClass('zoom');
            $itemEventLayer.addClass('has-zoom');
            }, Math.ceil(zoomDelay / 3));
        // Define a variable to hold the pickup action function
        let pickupFunction = function(onComplete, afterDelay){
            if (!onComplete || typeof onComplete !== 'function'){ onComplete = false; }
            if (!afterDelay || typeof afterDelay !== 'number'){ afterDelay = 0; }
            // Check to make sure the item token was not empty
            if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){
                console.error('triggerItemPickup() called for item with empty token!');
                return false;
                }
            // Check if the item was a consumable health or weapon energy item
            // and apply it to the first robot that needs it, else pocket it
            else if (itemToken.match(/^(energy|weapon)-(pellet|capsule|tank)$/i)){
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
                    if (!itemEventQuantity){ break; } // exit the loop early if none left
                    }
                }
            // If the item has not been claimed it, it means we should (try to) add it to the inventory instead
            if (!itemEvent.claimed){
                //console.log('-> no robots needed this item, so we will add it to the inventory instead');
                if (_self.addItemToInventory(itemToken, itemEventQuantity)){
                    // only remove if inventory function returns true, that way full-stock players leave it behind
                    itemEvent.claimed = true;
                    itemEventQuantity--;
                    }
                }
            // Update the real copy with any changes to claimed flag
            if (itemEvent.claimed){
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
                    $itemEventSprite.animate({opacity: 0, filter: 'brightness(2)'}, zoomDelay, function(){ $itemEventSprite.remove(); });
                    }
                // Trigger a save of the world state to persist this change
                _self.saveWorldState();
                });
            }, (zoomDelay * 2));
        // Return true on success
        return true;
        }

    // Define a quick function for triggering a live ability pickup on the field (and any effects that may have
    triggerAbilityPickup(abilityEvent, zoomDelay){
        //console.log('%c' + 'mmrpgWorldMap.triggerAbilityPickup()', 'color: magenta;');
        //console.log('--> abilityEvent =', abilityEvent);
        if (!abilityEvent || typeof abilityEvent !== 'object'){ console.error('triggerAbilityPickup() missing required abilityEvent!'); return false; }
        if (typeof abilityEvent.sprite === 'undefined'){ console.error('triggerAbilityPickup() missing required abilityEvent.sprite!'); return false; }
        if (abilityEvent.claimed === true){ console.warn('triggerAbilityPickup() called for ability that has already been claimed!'); return false; }
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
            // We can add this ability directly to the player's collection
            if (_self.addAbilityToCollection(abilityToken)){
                // only remove if collection function returns true, though it should never technically be false
                abilityEvent.claimed = true;
                }
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
                _self.saveWorldState();
                });
            }, (zoomDelay * 2));
        // Return true on success
        return true;
        }

    // Define a quick function for adding an item to the player's inventory if there's room for it
    addItemToInventory(itemToken, itemQuantity, animatePickup, playSound){
        //console.log('%c' + 'mmrpgWorldMap.addItemToInventory(item:' + itemToken + ', quantity:' + itemQuantity + ')', 'color: magenta;');
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('addItemToInventory() missing required itemToken!'); return false; }
        if (typeof itemQuantity !== 'number' || isNaN(itemQuantity) || itemQuantity < 1){ itemQuantity = 1; }
        if (typeof animatePickup !== 'boolean'){ animatePickup = true; } // default to true if not provided
        if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
        //console.log('--> itemToken =', itemToken);
        //console.log('--> itemQuantity =', itemQuantity);
        //console.log('--> animatePickup =', animatePickup);
        //console.log('--> playSound =', playSound);
        // Collect references to world objects
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldPlayerItems = _worldPlayer.items;
        let itemInventoryMax = _config.playerInventoryMax; // 99;
        // Create an entry in the items index if it does not already exist
        if (typeof _worldPlayerItems[itemToken] === 'undefined'){ _worldPlayerItems[itemToken] = 0; }
        // Check to see if there's room for the item in the inventory
        let currentItemQuantity = _worldPlayerItems[itemToken];
        //console.log('--> currentItemQuantity =', currentItemQuantity);
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
        //console.log('--> updated _worldPlayerItems['+itemToken+'] => ', _worldPlayerItems[itemToken]);
        // If an animation was requested, make sure we show it above the player's head
        if (animatePickup){
            // TODO: write this animation code later
            console.warn('addItemToInventory() would animate the item pickup now...'); // TODO: read the message
            }
        // If a sound was requested, play it now
        if (playSound){ _self.playSoundEffect('get-item'); }
        // Trigger a save of the world state to persist this change
        //console.log('-> checking if we should reload the world on save');
        let reloadWorldOnSave = false;
        //console.log('-> itemToken =', itemToken);
        if (itemToken.indexOf('-heart') !== -1){ reloadWorldOnSave = true; } // limit hearts always reload the world
        //console.log('-> reloadWorldOnSave =', reloadWorldOnSave);
        _self.saveWorldState(function(){
            //console.log('saveWorldState (via addItemToInventory) complete!');
            //console.log('-> reloadWorldOnSave =', reloadWorldOnSave);
            // maybe reload the page to update the inventory display
            if (reloadWorldOnSave){
                //console.log('-> reloading the world now...');
                window.location.reload();
                }
            }, true, !reloadWorldOnSave);
        // Return true on success
        return true;
        }

    // Define a quick function for adding an ability to the player's collection if they don't already have it
    addAbilityToCollection(abilityToken, animatePickup, playSound){
        //console.log('%c' + 'mmrpgWorldMap.addAbilityToCollection(ability:' + abilityToken + ')', 'color: magenta;');
        if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('addAbilityToCollection() missing required abilityToken!'); return false; }
        if (typeof animatePickup !== 'boolean'){ animatePickup = true; } // default to true if not provided
        if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
        //console.log('--> abilityToken =', abilityToken);
        //console.log('--> animatePickup =', animatePickup);
        //console.log('--> playSound =', playSound);
        // Collect references to world objects
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldPlayerAbilities = _worldPlayer.abilities;
        let abilityInventoryMax = _config.playerInventoryMax; // 99;
        // Check to see if there's room for the ability in the collection
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
        // Trigger a save of the world state to persist this change
        _self.saveWorldState();
        // Return true on success
        return true;
        }


    // Define a quick functino for polling the server for new events (but only if we can actually show them)
    triggerWindowEventsPull(afterDelay){
        //console.log('%c' + 'mmrpgWorldMap.triggerWindowEventsPull()', 'color: magenta;');
        afterDelay = typeof afterDelay === 'number' ? afterDelay : 1000; // default to zero if not provided
        //console.log('queuing the windowEventsPull event (via world)');
        if (typeof window.top.mmrpg_queue_for_game_start !== 'undefined'){
            //console.log('i guess we wait for the game to start via parent.mmrpg_queue_for_game_start()', parent.mmrpg_queue_for_game_start);
            window.top.mmrpg_queue_for_game_start(function(){
                //console.log('i guess the game has started');
                setTimeout(function(){
                    //console.log('attempting to pull window events via parent.windowEventsPull()', parent.windowEventsPull);
                    let result = parent.windowEventsPull(true);
                    if (result < 0){ console.error('windowEventsPull returned an error code: ' + result); }
                    //else { console.log('windowEventsPull returned successfully: ' + result); }
                    }, afterDelay);
                });
            }
        else if (typeof window.top.windowEventsPull !== 'undefined'){
            //console.log('i guess we pull events manually via parent.windowEventsPull()', parent.windowEventsPull);
            setTimeout(function(){
                let result = parent.windowEventsPull(true);
                if (result < 0){ console.error('windowEventsPull returned an error code: ' + result); }
                //else { console.log('windowEventsPull returned successfully: ' + result); }
                }, afterDelay);
            }
        else {
            //console.warn('could not find a way to pull window events from the parent window!');
            }
        // Return no specific result
        return;
        }

}
