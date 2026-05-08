
// Define global variables
let $thisPrototype = false;
let $thisWorld = false;
let $thisCanvas = false;

// Expand the game settings object with a variable world specific data
gameSettings.worldConfig = {
    userId: 0,
    playerId: 0,
    playerToken: 'player',
    playerRobots: ['0_robot'],
    playerAbilities: ['buster-shot'],
    playerItemsIndex: {},
    playerRobotsIndex: {},
    playerRobotsLimit: -1,
    playerMobility: 1, // default only
    playerHistory: [], // list of prev-player tokens in rev-chron order
    mapWorld: 'undefined',
    mapToken: 'undefined',
    worldName: 'Undefined World',
    mapName: 'Undefined Map',
    mapImage: 'undefined.png',
    mapField: 'field',
    mapSize: [10, 10],
    mapTileSize: [80, 80],
    mapTileSizeOffset: [0, 0],
    mapSpriteSize: [40, 40],
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
    mapMessages: {
        staggerDelay: 600,  // time between sub-messages appearing (milliseconds)
        queueStagger: 900,  // time between queued messages appearing (milliseconds)
        holdDuration: 4000, // how long a message stays on screen (milliseconds)
        fadeDuration: 1000, // css transition time for fading out (milliseconds)
        maxConcurrent: 3,   // how many messages can be on screen at once
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
    minimapWorldTileSize: 14,
    minimapAreaTileSize: 6,
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
    maxRobotsPerPlayer: 8, // match the battle system b/c duh
    maxAbilitiesPerRobot: 8, // match the battle system b/c duh
    robotStorageSlotsVisible: 8, // probably wont change as it's what fits
    itemStorageSlotsVisible: 24, // probably wont change as it's what fits
    abilityStorageSlotsVisible: 24, // probably wont change as it's what fits
    robotStatModMax: 5, // match the battle system
    robotStatModMin: -5, // match the battle system
    itemInventoryMax: 99, // match the battle system
    //defaultZoomLevel: 1.0,  // normal
    defaultZoomLevel: 1.5, // slightly zoomed in
    //defaultZoomLevel: 0.5, // far out
    zoomIncrement: 0.5, // zoom in/out by this amount
    minZoomLevel: 0.5, // very zoomed out
    maxZoomLevel: 2.0, // very zoomed in,
    onReadyZoomIntoPlayer: false, // automatically zoom into player (from a distance) on ready
    onReadyShowLocationBanner: false, // automatically show the current location banner on ready
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
        team: [],
        robots: {},
        items: {},
        abilities: {},
        },
    items: {},
    abilities: {},
    buttons: {},
    switches: {},
    symbols: {},
    callbacks: {},
    layersIndex: {},
    layerTilesIndex: {},
    layerTileOffsets: {},
    baseMapTileKeys: [], // base array of tile keys that are part of the map
    walkableMapTileKeys: [], // array of tile keys that are specifically walkable
    zoomLevel: 1.0, // default zoom level,
    userZoomLevel: 1.0, // current zoom level set by user
    scrollPosition: [-1, -1], // current scroll XY position of visible map
    mapIsHidden: false, // map is full visible by default
    storageBoxesVisible: false, // map hidden by storage boxes capturing input
    actionModalVisible: false, // map hidden by action modal capturing input
    allowHovers: true, // allow hover effects on tiles
    allowClicks: true, // allow click events on tiles
    hasLoaded: false, // has finished loading
    isReady: false, // is ready for interaction
    isBusy: false, // is busy doing something,
    isBusyWith: {}, // for when multiple things happen at once
    currentScreen: 'world-map', // which screen is visible
    currentSubScreen: '', // if a subscreen is visible (like inventory, robot management, etc)
    autoApplyConsumables: false, // automatically apply consumable items to party robots on pickup
    autoEquipHoldables: false, // automatically hold equippable items to party robots on pickup
    };
gameSettings.worldIndexes = {
    types: {},
    players: {},
    robots: {},
    items: {},
    abilities: {},
    fields: {},
    };
gameSettings.worldElements = {
    mmrpg: null,
    world: null,
    canvas: null,
    map: null,
    layers: null,
    cursor: null,
    };
gameSettings.userInputWatcher = {
    config: null,
    events: null,
    userInputs: {},
    activeInputs: {},
    lastInputKind: null,
    lastInputEvent: null,
    };
gameSettings.worldLoaded = false;

// Create the mmrpgWorldMap class object for this mode
class mmrpgWorldMap {

    // Constructor function for the world map
    constructor($mmrpg, onReady, custConfig, custIndexes){
        //console.log('%c' + 'mmrpgWorldMap() constructor', 'color: green;');
        let _self = this;
        _self.config = gameSettings.worldConfig;
        _self.indexes = gameSettings.worldIndexes;
        _self.elements = gameSettings.worldElements;
        _self.state = gameSettings.worldState;
        _self.inputs = gameSettings.userInputWatcher;
        if (!$mmrpg || !$mmrpg.length){ return false; }
        if (!_self.checkIndexes()){ return false; }
        if (onReady){ _self.onWorldReady(onReady); }
        _self.initConfig(custConfig);
        _self.initIndexes(custIndexes);
        _self.initWorld($mmrpg);
        }

    // Quick function for checking if the world is "already busy" doing something (either explicitly or by some action like moving)
    worldIsBusy(){
        //console.log('%c' + 'mmrpgWorldMap.worldIsBusy()', 'color: green;');
        let _self = this;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let busyWithKeys = Object.keys(_world.isBusyWith);;
        return _world.isBusy || busyWithKeys.length > 0 || _worldCursor.loading || _worldCursor.busy || _worldCursor.moving;
        }

    // Quick function for checking if the world map specifically is busy doing something (either busy because world, or because hidden)
    worldMapIsHidden(){
        //console.log('%c' + 'mmrpgWorldMap.worldMapIsHidden()', 'color: green;');
        let _self = this;
        let _world = _self.state;
        let activeWindowEvent = gameSettings.activeWindowEvent ? true : false;
        return _world.mapIsHidden || activeWindowEvent;
        }

    // Quick function to initialize default settings
    initConfig(custConfig){
        //console.log('%c' + 'mmrpgWorldMap.initConfig()', 'color: green;');
        if (!custConfig || typeof custConfig !== 'object'){ custConfig = {}; }
        //console.log('custConfig =', custConfig);
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        // Collect custom location banner settings if they've been set
        let onReadyShowLocationBanner = custConfig.onReadyShowLocationBanner;
        if (typeof onReadyShowLocationBanner === 'boolean'){ _config.onReadyShowLocationBanner = onReadyShowLocationBanner; }
        // Set and/or collect custom zoom level settings if they exist
        _world.zoomLevel = _config.defaultZoomLevel;
        _world.userZoomLevel = _config.defaultZoomLevel; // TODO: remember this on reload
        let onReadyZoomIntoPlayer = custConfig.onReadyZoomIntoPlayer;
        if (typeof onReadyZoomIntoPlayer === 'boolean'){ _config.onReadyZoomIntoPlayer = onReadyZoomIntoPlayer; }
        if (onReadyZoomIntoPlayer){
            //console.log('setting zoom level to min...');
            _world.zoomLevel = _config.minZoomLevel;
            _world.userZoomLevel = _config.minZoomLevel;
            }
        //console.log('_config.onReadyZoomIntoPlayer =', _config.onReadyZoomIntoPlayer);
        //console.log('_config.onReadyShowLocationBanner =', _config.onReadyShowLocationBanner);
        //console.log('_world.zoomLevel =', _world.zoomLevel);
        // Return true on success
        return true;
        }

    // Quick function to intialize helper methods on indexes for easier lookups and references
    initIndexes(custIndexes){
        //console.log('%c' + 'mmrpgWorldMap.initIndexes()', 'color: orange;');
        if (!custIndexes || typeof custIndexes !== 'object'){ custIndexes = {}; }
        let _self = this;
        let _config = _self.config;
        let _indexes = _self.indexes;
        let _indexKeys = Object.keys(_indexes);
        // Define a quick inline function for generating an ID-to-token lookup
        let _getIDs = function(index){
            let tokens = Object.keys(index), ids = {};
            for (var j = 0; j < tokens.length; j++){
                let token = tokens[j], data = index[token];
                if (!data.id){ continue; }
                ids[data.id] = token;
                }
            return ids;
            };
        // Define a quick inline function for generating a list of all tokens in an index
        let _getTokens = function(index){
            let tokens = Object.keys(index), tokenList = [];
            for (var j = 0; j < tokens.length; j++){
                let token = tokens[j];
                tokenList.push(token);
                }
            return tokenList;
            };
        // Define the template function for actually getting the object data by ID
        let _getByID = function(id){
            let index = this;
            let token = index._indexIDs[id] || false;
            if (!token || typeof token !== 'string'){ return false; }
            if (!index[token] || typeof index[token] !== 'object'){ return false; }
            return index[token];
            };
        // Define a template function for getting the object data by token (just for consistency)
        let _getByToken = function(token){
            let index = this;
            if (!token || typeof token !== 'string'){ return false; }
            if (!index[token] || typeof index[token] !== 'object'){ return false; }
            return index[token];
            };
        // Grab the available indexes and loop through them to add the new methods
        for (var i = 0; i < _indexKeys.length; i++){
            let key = _indexKeys[i], index = _indexes[key];
            let _indexIDs = _getIDs(index);
            let _indexTokens = _getTokens(index);
            index._indexIDs = _indexIDs;
            index._indexTokens = _indexTokens;
            index.getByID = _getByID;
            index.getByToken = _getByToken;
            index.getIDs = function(){ return Object.values(_indexIDs); };
            index.getTokens = function(){ return Object.values(_indexTokens); };
            _indexes[key] = index;
            }
        // Reassign the updated indexes back to the class object
        _self.indexes = _indexes;
        // Return true on success
        return true;
        }

    // Quick function to check all indexes are loaded with data
    checkIndexes(){
        //console.log('%c' + 'mmrpgWorldMap.checkIndexes()', 'color: green;');
        let _self = this;
        let _config = _self.config;
        let _indexes = _self.indexes;
        let requiredIndexes = Object.keys(_indexes);
        let missingIndexes = [];
        //console.log('-> _indexes:', _indexes);
        //console.log('-> requiredIndexes:', requiredIndexes);
        for (var i = 0; i < requiredIndexes.length; i++){
            let indexName = requiredIndexes[i];
            let indexData = _indexes[indexName] || false;
            if (!indexData || typeof indexData !== 'object' || !Object.keys(indexData).length){
                missingIndexes.push(indexName);
                }
            }
        //console.log('-> missingIndexes:', missingIndexes);
        if (missingIndexes.length){
            console.error('checkIndexes() missing required indexes:', missingIndexes);
            return false;
            }
        return true;
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
        let $messageDisplay = $('#message-display', $thisWorld);
        let $positionDisplay = $('#position-display', $thisWorld);
        let $playerSwitcher = $('#player-switcher', $thisWorld);
        let $cursorPalette = $('#cursor-palette', $thisWorld);
        let $robotsOverview = $('#robots-overview', $thisWorld);
        let $minimapOverview = $('#minimap-overview', $thisPrototype);
        let $sideButtons = $('#side-buttons', $thisWorld);
        let $actionDropdown = $('#action-dropdown', $thisWorld);
        let $clickOverlay = $('#click-overlay', $thisWorld);
        let $titleBanner = $('#title-banner', $thisPrototype);
        let $actionModal = $('#action-modal', $thisPrototype);
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
        _elements.messageDisplay = $messageDisplay;
        _elements.positionDisplay = $positionDisplay;
        _elements.playerSwitcher = $playerSwitcher;
        _elements.cursorPalette = $cursorPalette;
        _elements.robotsOverview = $robotsOverview;
        _elements.minimapOverview = $minimapOverview;
        _elements.sideButtons = $sideButtons;
        _elements.actionDropdown = $actionDropdown;
        _elements.clickOverlay = $clickOverlay;
        _elements.titleBanner = $titleBanner;
        _elements.actionModal = $actionModal;
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
        let worldName = mapData.map_world_name || false;
        let mapToken = mapData.map_token || false;
        let mapName = mapData.map_name || false;
        let mapImage = mapData.map_image || false;
        let mapField = mapData.map_field || 'field';
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
        let defaultWorldName = mapWorld.replace(/-/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
        let defaultMapName = mapToken.replace(/-/g, ' ').replace(/ AREA /g, ' Area ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
        let defaultMapSize = [_config.mapSize[0], _config.mapSize[1]];
        let defaultMapTileSize = [_config.mapTileSize[0], _config.mapTileSize[1]];
        _world.isBusy = true;
        _config.mapWorld = mapWorld;
        _config.mapWorldName = worldName || defaultWorldName;
        _config.mapToken = mapToken;
        _config.mapName = mapName || defaultMapName;
        _config.mapImage = mapImage;
        _config.mapField = mapField;
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
            let livePlayerTeam = [];
            let livePlayerRobots = {};
            for (var i = 0; i < _playerRobots.length; i++){
                let robotString = _playerRobots[i];
                let robotInfo = _playerRobotsIndex[robotString] || false;
                if (!robotInfo || typeof robotInfo !== 'object' || !Object.keys(robotInfo).length){
                    //console.warn('initWorldMap() missing robotInfo for robotString:', robotString);
                    continue;
                    }
                //console.log('---> adding robot #' + i + ' w/ robotString = ' + robotString);
                let liveRobotInfo = _self.getClonedObject(robotInfo); // clone the info object
                livePlayerTeam.push(robotString);
                livePlayerRobots[robotString] = liveRobotInfo;
                }
            _worldPlayer.team = livePlayerTeam;
            _worldPlayer.robots = livePlayerRobots;
            //console.log('---> initWorldMap() livePlayerRobots =', livePlayerRobots);
            //console.log('---> initWorldMap() _worldPlayer.team =', _worldPlayer.team);
            //console.log('---> initWorldMap() _worldPlayer.robots =', _worldPlayer.robots);
            }
        // If player abilities were defined [list] in the predefined config, copy them over to the state
        let _playerAbilities = _config.playerAbilities;
        if (_playerAbilities.length){
            //console.log('---> initWorldMap() found ' + _playerAbilities.length + ' player abilities to initialize!');
            let livePlayerAbilities = _self.getClonedObject(_playerAbilities);
            //console.log('---> adding abilities to player state:', livePlayerAbilities);
            _worldPlayer.abilities = livePlayerAbilities;
            //console.log('---> initWorldMap() livePlayerAbilities =', livePlayerAbilities);
            }
        // If player items were defined [index] in the predefined config, copy them over to the state
        let _playerItemsIndex = _config.playerItemsIndex;
        if (Object.keys(_playerItemsIndex).length){
            //console.log('---> initWorldMap() found ' + Object.keys(_playerItemsIndex).length + ' player items to initialize!');
            let livePlayerItems = _self.getClonedObject(_playerItemsIndex);
            //console.log('---> adding items to player state:', livePlayerItems);
            _worldPlayer.items = livePlayerItems;
            //console.log('---> initWorldMap() livePlayerItems =', livePlayerItems);
            }
        // Define the function to run when everything is done loading
        let onWorldLoaded = function(){
            console.log('%c' + 'MMRPG WORLD HAS LOADED!', 'color: cyan;');
            gameSettings.worldLoaded = true;
            gameSettings.gameHasLoaded = true;
            $('#mmrpg').removeClass('loading');
            _world.hasLoaded = true;
            _self.bindEventsToCanvas($canvasMap);
            _self.bindEventsToWorld($thisWorld);
            _self.calculateWalkableMapTiles();
            _self.initMiniMap();
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
            //_self.togglePerspectiveMode(); // TEMP TEMP TEMP
            _self.moveToPosition(startPosition, null, true, false, fakeOldPosition);
            //_self.togglePerspectiveMode(); // TEMP TEMP TEMP
            setTimeout(function(){
                console.log('%c' + 'MMRPG WORLD IS READY!', 'color: lime;');
                _world.isReady = true;
                $thisWorld.removeClass('hidden');
                $thisWorld.addClass('ready');
                $canvasMap.addClass('ready');
                setTimeout(function(){
                    _self.triggerWindowEventsPull();
                    _self.triggerWorldReadyEvents();
                    _self.startIdleAnimation();
                    _world.isBusy = false;
                    gameSettings.gameHasStarted = true;
                    //_self.showWorldMessage('<span style="color: cyan;">triggerWorldReadyEvents()</span>');
                    }, 900);
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
            _self.refreshTerrainTileOverlay();
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
        let tilesIndexKeys = Object.keys(tilesIndex);
        for (var t = 0; t < tilesIndexKeys.length; t++){
            let tileToken = tilesIndexKeys[t];
            if (tileToken === 'keys'){ continue; }
            let tileInfo = tilesIndex[tileToken];
            if (!tileInfo){ console.error('indexCanvasTileData() missing tileInfo for tileToken:', tileToken); continue; }
            let tileInfoAttrs = {isWalkable: true, isVoid: false, isWater: false};
            //console.log('typeof tileInfo for tileToken "' + tileToken + '" =', typeof tileInfo, '\n-> w/ value:', tileInfo);
            (function(indexOfNotWalkable){
                if (indexOfNotWalkable < 0){ return; }
                tileInfoAttrs.isWalkable = false;
                tileInfo.splice(indexOfNotWalkable, 1);
                })(tileInfo.indexOf('not-walkable'));
            let tileInfoOffset = [tileInfo[0] || 0, tileInfo[1] || 0];
            let tileInfoSize = [tileInfo[2] || tileSize[0], tileInfo[3] || tileSize[1]];
            let newTileInfo = [tileInfoOffset, tileInfoSize, tileInfoAttrs];
            tilesIndex[tileToken] = newTileInfo;
            }
        let tileDataKeys = Object.keys(canvasTiles);
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
            let tileSpriteOffset = tileSpriteInfo[0], tileSpriteSize = tileSpriteInfo[1], tileSpriteAttrs = tileSpriteInfo[2];
            let tileSpritePosition = [tilePos[0], tilePos[1], ((tilePos[0] - 1) * tileSpriteSize[0]), ((tilePos[1] - 1) * tileSpriteSize[1])];
            let tileSpriteEffects = {grid: true, hover: false, outline: false, focus: false, active: false}; // default values
            let tileIsVoid = tileSpriteToken === 'void' || tileSpriteToken.indexOf('void') !== -1 ? true : false;
            let tileIsWater = tileSpriteToken === 'water' || tileSpriteToken.indexOf('water') !== -1 ? true : false;
            if (tileSpriteAttrs.isVoid){ tileSpriteEffects.grid = false; } // no grid or walk for void tiles
            //console.log('---> tileSpriteKey =', tileSpriteKey);
            //console.log('---> tileSpriteToken =', tileSpriteToken);
            //console.log('---> tileSpriteInfo =', tileSpriteInfo);
            let tilesIndexData = typeof thisLayerTiles[tileKey] !== 'undefined' ? thisLayerTiles[tileKey] : {};
            tilesIndexData.position = tileSpritePosition;
            tilesIndexData.effects = tileSpriteEffects;
            tilesIndexData.sprite = [tileSpriteKey, tileSpriteToken, tileSpriteOffset, tileSpriteSize];
            tilesIndexData.walkable = tileSpriteAttrs.isWalkable;
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
        //console.log('%c' + '----> finished drawing ' + thisLayerTilesKeys.length + ' tiles to ' + layerToken + ' layer canvas!', 'color: cyan;');
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
        if (tileIsWater){ ctx.globalAlpha = 0.3; }
        ctx.drawImage(spriteSheet,
            tileSpriteOffset[0], tileSpriteOffset[1], // source offset
            tileSpriteSize[0], tileSpriteSize[1], // source size
            tilePosition[2], tilePosition[3], // destination offset
            tileSpriteSize[0], tileSpriteSize[1] // destination size
            );
        ctx.globalAlpha = 1.0;
        // gradient-overlay: draw a slice of the gradient overlay on top of this tile for aesthetic purposes
        //console.log('tileData =', tileData);
        //console.log('tileSprite =', tileSprite);
        //console.log('tilePosition =', tilePosition);
        //console.log('tileSpriteSize =', tileSpriteSize);
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
        //let mapTilesIndex = _config.mapTilesIndex;
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
                if ($robotsOverview.is('.expanded')){ $robotsOverview.find('.team-close').trigger('click'); }
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
            else if (sameAsCurrent){ _self.refreshMapPositionEvents(0, true); }
            if (!tileIsWithinRange || sameAsLast || sameAsCurrent){ return false; }
            //console.log('%c' + 'Mouse click event triggered for position ' + thisPos + '!', 'color: orange;');
            if (!sameAsLast){ _self.playSoundEffect('link-click'); }
            _self.makeLayerTileActive(thisPos);
            if (activeTimeouts[oldPos]){ clearTimeout(activeTimeouts[oldPos]); }
            activeTimeouts[thisPos] = setTimeout(function(){
                _self.moveToPosition(thisPos, function(){
                    _self.makeLayerTileInactive(oldPos);
                    }, true);
                clearTimeout(activeTimeouts[thisPos]);
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
            //$clickOverlay.attr('title', ('X' + thisPosXY[0] + '-Y' + thisPosXY[1]));
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
        let _worldPlayerTeam = _worldPlayer.team;
        let _worldPlayerRobots = _worldPlayer.robots;
        let $thisCanvas = _elements.canvas;
        let $sideButtons = _elements.sideButtons;
        let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
        let $actionDropdown = _elements.actionDropdown;
        let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
        let hoverCanvasObject = function(e, sfx){
            let $object = $(this);
            if (_self.worldIsBusy()){ return; }
            if ($object.is('.disabled')){ return; }
            if ($object.closest('.chrome').is('.disabled')){ return; }
            $thisCanvas.find('.hovered').removeClass('hovered');
            if (sfx){ _self.playSoundEffect(sfx); }
            $object.addClass('hovered');
            return true;
            };
        let unhoverCanvasObject = function(e){
            let $object = $(this);
            $object.removeClass('hovered');
            return true;
            };
        // Bind a click event to the back button in the header that'll bring us to prototype menu
        let $backButton = _elements.backButton;
        if ($backButton && $backButton.length){
            $backButton.bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
            $backButton.bind('mouseleave', unhoverCanvasObject);
            $backButton.bind('click', function(e){
                e.preventDefault();
                if ($(this).is('.disabled')){ return false; }
                //console.log('%c' + 'Back button clicked!', 'color: cyan;');
                //if (!confirm('Are you sure you want to leave the world map?')){ return; }
                _self.playSoundEffect('bounce-sound');
                let backButtonURL = $backButton.attr('data-url') || _config.backButtonURL;
                _self.decZoomLevel();
                $thisWorld.addClass('busy');
                _self.saveWorldState(function(){
                    _self.decZoomLevel(0.5);
                    $thisWorld.addClass('loading');
                    window.location.href = backButtonURL;
                    });
                $thisWorld.animate({opacity: 0}, 900, function(){
                    $thisWorld.addClass('hidden');
                    });
                return true;
                });
            }
        // Bind a click event to the home button in the header that'll bring us to prototype menu
        let $homeButton = _elements.homeButton;
        if ($homeButton && $homeButton.length){
            $homeButton.bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
            $homeButton.bind('mouseleave', unhoverCanvasObject);
            $homeButton.bind('click', function(e){
                e.preventDefault();
                if ($(this).is('.disabled')){ return false; }
                //console.log('%c' + 'Home button clicked!', 'color: cyan;');
                //if (!confirm('Are you sure you want to return to the home area?')){ return; }
                _self.playSoundEffect('bounce-sound');
                let homeButtonURL = $homeButton.attr('data-url') || _config.homeButtonURL;
                _self.decZoomLevel();
                $thisWorld.addClass('busy');
                _self.saveWorldState(function(){
                    _self.decZoomLevel();
                    $thisWorld.addClass('loading');
                    window.location.href = homeButtonURL;
                    });
                $thisWorld.animate({opacity: 0}, 600, function(){
                    $thisWorld.addClass('hidden');
                    });
                return true;
                });
            }
        // Bind a click event to the reset button in the header that'll clear world data to start over (dev/debug only)
        let $resetButton = _elements.resetButton;
        if ($resetButton && $resetButton.length){
            $resetButton.bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
            $resetButton.bind('mouseleave', unhoverCanvasObject);
            $resetButton.bind('click', function(e){
                e.preventDefault();
                if ($(this).is('.disabled')){ return false; }
                //console.log('%c' + 'Reset button clicked!', 'color: cyan;');
                if (!confirm('Are you sure you want to reset the world map?')){ return; }
                _self.playSoundEffect('destroyed-sound');
                _self.loadMusicTrack('current-track', true);
                let resetButtonURL = $resetButton.attr('data-url') || _config.resetButtonURL;
                _self.decZoomLevel(0.5);
                $thisWorld.addClass('busy');
                _self.saveWorldState(function(){
                    _self.decZoomLevel(1.0);
                    $thisWorld.addClass('loading');
                    window.location.href = resetButtonURL;
                    });
                $thisWorld.animate({opacity: 0}, 1200, function(){
                    $thisWorld.addClass('hidden');
                    });
                return true;
                });
            }
        // Bind click events to the player switcher options in the world map header
        let $playerSwitcher = _elements.playerSwitcher;
        if ($playerSwitcher && $playerSwitcher.length){
            let $playerButtons = $('.team-player[data-player]', $playerSwitcher);
            $playerButtons.bind('mouseenter', function(e){ if (hoverCanvasObject.call(this, e, 'icon-hover')){ $(this).find('.sprite.player > .sprite').attr('data-frame', '01'); } }); // taunt
            $playerButtons.bind('mouseleave', function(e){ if (unhoverCanvasObject.call(this, e)){ $(this).find('.sprite.player > .sprite').attr('data-frame', '00'); } }); // base
            $playerButtons.bind('click', function(e){
                //console.log('%c' + 'Player switcher clicked!', 'color: cyan;');
                e.preventDefault();
                if ($(this).is('.disabled')){ return false; }
                if ($playerSwitcher.is('.disabled')){ return false; }
                if (_self.worldIsBusy()){ return false; }
                $('.team-player', $playerSwitcher).removeClass('active');
                let $option = $(this);
                let playerToken = $option.attr('data-player') || false;
                $option.addClass('active');
                _self.playSoundEffect('lets-go-robots');
                $thisWorld.addClass('loading');
                let worldReloadURL = 'world.php?player=' + playerToken + '&switch=true';
                _self.decZoomLevel();
                $thisWorld.addClass('busy');
                _self.saveWorldState(function(){
                    //_self.decZoomLevel();
                    _self.updateZoomLevel(_config.minZoomLevel);
                    $thisWorld.addClass('loading');
                    window.location.href = worldReloadURL;
                    //_self.decZoomLevel();
                    });
                return true;
                });
            }
        // Check to make sure the robotsOverview exists, and then bind events to its elements
        let $robotsOverview = _elements.robotsOverview;
        let robotsOverviewAPI = _self.robotsOverviewAPI || {};
        if ($robotsOverview && $robotsOverview.length){
            // Collect some commonly used elements and data for use below and pre-calculate some values
            let $overviewWrapper = $('> .wrapper', $robotsOverview);
            let $storageButtons = $('.storage-button', $robotsOverview);
            let $storageBoxes = $('.storage-box', $robotsOverview);
            let $teamSprites = _elements.teamSprites;
            let $robotsOnMap = $teamSprites.filter('.robot:not(.cursor)');
            let $teamRobotsDiv = $('.team-robots', $robotsOverview);
            let $storageBoxDivs = $('.storage-box', $robotsOverview);
            let $storageRobotsDiv = $('.storage-robots', $robotsOverview);
            let $storageItemsDiv = $('.storage-items', $robotsOverview);
            let $storageAbilitiesDiv = $('.storage-abilities', $robotsOverview);
            let $storageDetailsDiv = $('.storage-details', $robotsOverview);
            let $teamRobotsWrapper = $('> .wrapper', $teamRobotsDiv);
            let $storageRobotsWrapper = $('> .wrapper', $storageRobotsDiv);
            let $storageItemsWrapper = $('> .wrapper', $storageItemsDiv);
            let $storageAbilitiesWrapper = $('> .wrapper', $storageAbilitiesDiv);
            let $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
            let $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
            let $storageItemsInOverview = $('.team-item[data-item]', $storageItemsDiv);
            let $storageAbilitiesInOverview = $('.team-ability[data-ability]', $storageAbilitiesDiv);
            let listOfRobotsInOverview = $teamRobotsInOverview.map(function(){ return $(this).attr('data-robot'); }).get();
            let $teamRobotsInOverviewBackup = $teamRobotsInOverview.clone(true).detach(); // save for later
            let $storageRobotsInOverviewBackup = $storageRobotsInOverview.clone(true).detach(); // save for later
            //console.log('-> $teamRobotsInOverview = ', $teamRobotsInOverview.length, $teamRobotsInOverview);
            //console.log('-> $storageRobotsInOverview = ', $storageRobotsInOverview.length, $storageRobotsInOverview);
            //console.log('-> listOfRobotsInOverview = ', listOfRobotsInOverview);
            // Define reusable functions for applying/removing the hover state to given element
            let hoverOverviewObject = function(e, sfx){
                let $object = $(this);
                if (_self.worldIsBusy()){ return; }
                if ($object.is('.disabled')){ return; }
                if ($object.closest('.listing').is('.disabled')){ return; }
                $robotsOverview.find('.hovered').removeClass('hovered');
                if (sfx){ _self.playSoundEffect(sfx); }
                else { _self.playSoundEffect('icon-hover'); }
                $object.addClass('hovered');
                };
            let unhoverOverviewObject = function(e){
                $(this).removeClass('hovered');
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
            // Define a function for quickly clearing selections and incompatible states from elements
            let clearSelectionsAndIncompatible = function(){
                //console.log('%c' + 'clearSelectionsAndIncompatible() called!', 'color: magenta;');
                $storageBoxes.removeClass('has-selection');
                $storageBoxes.find('.selected').removeClass('selected');
                $storageBoxes.find('.hovered').removeClass('hovered');
                $storageBoxes.removeClass('focused').removeClass('unfocused');
                $storageBoxes.find('.incompatible').removeClass('incompatible');
                $storageBoxes.find('.equipped').removeClass('equipped');
                $teamRobotsDiv.removeClass('focused').removeClass('unfocused');
                $teamRobotsDiv.find('.incompatible', ).removeClass('incompatible');
                $teamRobotsDiv.find('.equipped', ).removeClass('equipped');
                $teamRobotsDiv.find('.selected:not(.keep-selected)').removeClass('selected');
                $teamRobotsDiv.find('.hovered:not(.keep-hovered)').removeClass('hovered');
                $robotsOverview.find('.storage-details').remove();
                return;
                };
            // Define a function for expanding the robots-overview panel and showing a specific view
            let overviewIsOpening = false;
            let showRobotsOverviewPanel = function(viewToken, onComplete, keepSelectedTeamRobot){
                //console.log('%c' + 'showRobotsOverviewPanel(viewToken:' + (viewToken ? viewToken : typeof viewToken) + ') called!', 'color: magenta;');
                // Otherwise we can expand (if not already) the panel and switch to this specific view
                // and then disable the outside UI buttons to prevent bad-clicks and visual clutter
                viewToken = viewToken && typeof viewToken === 'string' && viewToken.length ? viewToken : '';
                keepSelectedTeamRobot = typeof keepSelectedTeamRobot === 'boolean' ? keepSelectedTeamRobot : true;
                if (keepSelectedTeamRobot){ $teamRobotsDiv.find('.team-robot[data-robot].selected').addClass('keep-selected'); }
                //console.log('-> before doing anything, selectedRobotToken = ', selectedRobotToken);
                //console.log('_world.currentScreen(before) =', _world.currentScreen);
                let alreadyShowing = _world.currentScreen === 'robots-overview' ? true : false;
                if (!alreadyShowing){ overviewIsOpening = true; }
                //console.log('alreadyShowing =', alreadyShowing);
                let $thisStorageBox = $storageBoxes.filter('[data-storage="' + viewToken + '"]');
                let $thisStorageButton = $storageButtons.filter('[data-view="' + viewToken + '"]');
                $('.button', $storageBoxes).attr('disabled', 'disabled');
                $storageRobotsDiv.removeClass('unfocused');
                $teamRobotsDiv.addClass('focused').removeClass('unfocused');
                $storageBoxes.removeClass('disabled');
                $storageButtons.removeClass('active');
                $('.button', $thisStorageBox).removeAttr('disabled');
                $thisStorageButton.addClass('active');
                _world.mapIsHidden = true; // set the map hidden state
                _world.storageBoxesVisible = true; // set the storage to a visible state
                _world.currentScreen = 'robots-overview';
                _world.currentSubScreen = viewToken;
                clearSelectionsAndIncompatible();
                disableOtherElements();
                // If there are any side-buttons active, dismiss them first
                if ($sideButtons.is('.active')){
                    let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                    $sideButtons.removeClass('maybe');
                    $dismissButton.trigger('click');
                    }
                requestAnimationFrame(function(){
                    // Play a sound effect + an extra if this is a fresh open
                    _self.playSoundEffect('link-click');
                    if (!alreadyShowing){ _self.playSoundEffect('inventory-open'); }
                    // Update the main overview div with new view
                    $robotsOverview.addClass('expanded').attr('data-view', viewToken);
                    // Regenerate the storage bullets, pages, and go to the correct page for this view
                    if (viewToken === 'robots'){ refreshRobotBackups(); }
                    if (viewToken === 'robots'){ makeStorageBullets(); }
                    makeStoragePages(viewToken);
                    goToStoragePage(viewToken, parseInt($thisStorageBox.attr('data-page') || ''));
                    refreshRobotRefs();
                    $teamRobotsDiv.addClass('focused');
                    $teamRobotsInOverview.filter(':not(.keep-selected)').removeClass('selected');
                    if (viewToken === 'robots'){ refreshRobotsDiv(); }
                    if (viewToken === 'items'){ refreshItemsDiv(); }
                    if (viewToken === 'abilities'){ refreshAbilitiesDiv(); }
                    refreshDetailsPanel();
                    /// Run the complete callback if one was provided
                    if (typeof onComplete === 'function'){ onComplete.call(this); }
                    if (keepSelectedTeamRobot){ $teamRobotsDiv.find('.team-robot[data-robot].keep-selected').removeClass('keep-selected'); }
                    if (!alreadyShowing && viewToken === 'robots'){
                        //console.log('hovering first team robot');
                        $teamRobotsDiv.find('.team-robot[data-robot]').first().addClass('hovered');
                        }
                    });
                // Make sure we create a timeout to disable inactive panels after animation complete
                if (_selfRef.showOverviewTimeout){ clearTimeout(_selfRef.showOverviewTimeout); }
                _selfRef.showOverviewTimeout = setTimeout(function(){ overviewIsOpening = false; $storageBoxes.not($thisStorageBox).addClass('disabled'); }, 1200);
                return;
                };
            // Define a function for dismissing the whole robots-overview panel and all views at-once
            let disableRobotsOverview = function(){
                //console.log('%c' + 'disableRobotsOverview() called!', 'color: magenta;');
                clearSelectionsAndIncompatible();
                enableOtherElements();
                _world.mapIsHidden = false;
                _world.storageBoxesVisible = false;
                _world.currentScreen = 'world-map';
                _world.currentSubScreen = '';
                $robotsOverview.removeClass('expanded').attr('data-view', '');
                $storageButtons.removeClass('active').removeClass('new');
                $('.pages', $storageRobotsDiv).remove();
                $('.bullets', $storageRobotsDiv).remove();
                $('.button', $storageBoxDivs).attr('disabled', 'disabled');
                $('.new', $storageBoxDivs).removeClass('new');
                _self.playSoundEffect('back-click');
                _self.playSoundEffect('inventory-close');
                return;
                };
            // Define a function for calculating storage config refs/values for robots, items, or abilities
            let storageConfigCache = {};
            let calculateStorage = function(storageKind, forceRefresh){
                //console.log('%c' + '-> calculateStorage(storageKind:' + storageKind + ') called!', 'color: magenta;');
                forceRefresh = forceRefresh === true ? true : false;
                if (!forceRefresh
                    && typeof storageConfigCache[storageKind] !== 'undefined'){
                    return storageConfigCache[storageKind];
                    }
                let xKindToken = storageKind, kindToken = '';
                if (storageKind === 'robots'){ kindToken = 'robot'; }
                else if (storageKind === 'items'){ kindToken = 'item'; }
                else if (storageKind === 'abilities'){ kindToken = 'ability'; }
                else { return false; }
                let storageSlotsPerPage = 0;
                let storageObjectsTotal = 0;
                let storageObjectsVisible = 0;
                let storageObjectSelector = '';
                let storageObjectFilter = '';
                let storagePagesRequired = 0;
                let currentStoragePageNum = 0;
                let currentToggleStates = {};
                let $storageObjectsDiv = null;
                let $storageObjectsWrapper = null;
                let $storageObjectsInOverview = null;
                // collect and define config and selector values to start
                storageSlotsPerPage = _config[kindToken + 'StorageSlotsVisible'];
                storageObjectSelector = '.team-' + kindToken + '[data-' + kindToken + ']';
                //console.log('-> storageSlotsPerPage = ', storageSlotsPerPage);
                //console.log('-> storageObjectSelector = ', storageObjectSelector);
                // collect the ref to the correct storage div and its contents
                $storageObjectsDiv = $storageBoxDivs.filter('[data-storage="' + storageKind + '"]');
                $storageObjectsWrapper = $('> .wrapper', $storageObjectsDiv);
                if (!$storageObjectsDiv || !$storageObjectsDiv.length){ console.error('calculateStorage() unable to find $storageObjectsDiv for storageKind ' + storageKind + '!'); return false; }
                if (!$storageObjectsWrapper || !$storageObjectsWrapper.length){ console.error('calculateStorage() unable to find $storageObjectsWrapper for storageKind ' + storageKind + '!'); return false; }
                //console.log('-> $storageObjectsDiv = ', $storageObjectsDiv);
                //console.log('-> $storageObjectsWrapper = ', $storageObjectsWrapper);
                // count the total number of objects before any filters are applied
                $storageObjectsInOverview = $(storageObjectSelector, $storageObjectsDiv);
                storageObjectsTotal = $storageObjectsInOverview.length;
                storageObjectsVisible = storageObjectsTotal;
                //console.log('-> $storageObjectsInOverview (', $storageObjectsInOverview.length, ') = ', $storageObjectsInOverview);
                //console.log('-> $storageObjectsInOverview (', $storageObjectsInOverview.length, ')');
                //console.log('-> storageObjectsTotal = ', storageObjectsTotal);
                //console.log('-> storageObjectsVisible = ', storageObjectsVisible);
                // check for any filters and re-count objects if necessary to do so
                let toggleTokens = ['current', 'disabled', 'incompatible', 'outofstock'];
                (function(tokens){
                    //console.log('-> checking toggles for tokens = ', tokens);
                    for (var i = 0; i < tokens.length; i++){
                    let token = tokens[i];
                    let $toggle = $('.toggle[data-toggle="' + token + '"]', $storageObjectsDiv);
                    if (!$toggle || !$toggle.length || !$toggle.is('[data-state]')){ continue; }
                    let state = $toggle.attr('data-state') || '';
                    //console.log('-> $toggle(', $toggle.length, ') w/', '\n--> token:', token, '\n--> state:', state);
                    currentToggleStates[token] = state;
                    } })(toggleTokens);
                //console.log('-> toggleTokens = ', toggleTokens);
                //console.log('-> currentToggleStates = ', currentToggleStates);
                if (Object.keys(currentToggleStates).length){
                    //console.log('-> currentToggleStates = ', currentToggleStates);
                    if (currentToggleStates['current'] === 'hidden'){ storageObjectFilter += ':not(.current)'; }
                    if (currentToggleStates['disabled'] === 'hidden'){ storageObjectFilter += ':not(.disabled)'; }
                    if (currentToggleStates['incompatible'] === 'hidden'){ storageObjectFilter += ':not(.incompatible)'; }
                    if (currentToggleStates['outofstock'] === 'hidden'){ storageObjectFilter += ':not([data-quantity="0"])'; }
                    //console.log('-> (new) storageObjectFilter = ', storageObjectFilter);
                    $storageObjectsInOverview = $(storageObjectSelector+storageObjectFilter, $storageObjectsDiv);
                    storageObjectsVisible = $storageObjectsInOverview.length;
                    //console.log('-> (new) $storageObjectsInOverview (', $storageObjectsInOverview.length, ') = ', $storageObjectsInOverview);
                    //console.log('-> (new) $storageObjectsInOverview (', $storageObjectsInOverview.length, ')');
                    //console.log('-> (new) storageObjectsVisible = ', storageObjectsVisible);
                    }
                storagePagesRequired = storageObjectsVisible > storageSlotsPerPage ? Math.ceil(storageObjectsVisible / storageSlotsPerPage) : 1;
                currentStoragePageNum = $storageObjectsDiv.is('[data-page]') ? parseInt($storageObjectsDiv.attr('data-page')) : 0;
                //console.log('-> storagePagesRequired = ', storagePagesRequired);
                //console.log('-> currentStoragePageNum = ', currentStoragePageNum);
                let storageConfig = {
                    kind: kindToken,
                    xkind: xKindToken,
                    storageDiv: $storageObjectsDiv,
                    storageWrapper: $storageObjectsWrapper,
                    storageObjects: $storageObjectsInOverview,
                    objectSelector: storageObjectSelector,
                    objectFilter: storageObjectFilter,
                    numPages: storagePagesRequired,
                    numSlotsPerPage: storageSlotsPerPage,
                    numObjectsTotal: storageObjectsTotal,
                    numObjectsVisible: storageObjectsVisible,
                    toggleStates: currentToggleStates,
                    currentPage: currentStoragePageNum,
                    };
                //console.log('-> storageConfig = ', storageConfig);
                storageConfigCache[storageKind] = storageConfig;
                return storageConfig;
                };
            // Define a function for generating storage page buttons for a given kind where/if needed on-demand
            let makeStoragePages = function(storageKind){
                //console.log('-> makeStoragePages(' + storageKind + ') called!');
                if (!storageKind || typeof storageKind !== 'string'){ return false; }
                let storageConfig = calculateStorage(storageKind);
                if (!storageConfig){ return false; }
                //console.log('-> storageConfig = ', storageConfig);
                let $storageObjectsDiv = storageConfig.storageDiv;
                //let $storageObjectsWrapper = storageConfig.storageWrapper;
                //let $storageObjectsInOverview = storageConfig.storageObjects;
                //let storageObjectSelector = storageConfig.objectSelector;
                let storagePagesRequired = storageConfig.numPages;
                let storageSlotsPerPage = storageConfig.numSlotsPerPage;
                let storageObjectsTotal = storageConfig.numObjectsTotal;
                let storageObjectsVisible = storageConfig.numObjectsVisible;
                let currentStoragePageNum = storageConfig.currentPage;
                //console.log('-> $storageObjectsDiv = ', $storageObjectsDiv);
                //console.log('-> $storageObjectsWrapper = ', $storageObjectsWrapper);
                //console.log('-> $storageObjectsInOverview = ', $storageObjectsInOverview);
                //console.log('-> storageObjectSelector = ', storageObjectSelector);
                //console.log('-> storagePagesRequired = ', storagePagesRequired);
                //console.log('-> storageSlotsPerPage = ', storageSlotsPerPage);
                //console.log('-> storageObjectsTotal = ', storageObjectsTotal);
                //console.log('-> storageObjectsVisible = ', storageObjectsVisible);
                //console.log('-> currentStoragePageNum = ', currentStoragePageNum);
                if (storageObjectsVisible <= storageSlotsPerPage){ return; }
                $('.pages', $storageObjectsDiv).remove();
                $('.counter', $storageObjectsDiv).remove();
                let pageButtonMarkup = '';
                pageButtonMarkup += '<div class="pages">';
                    pageButtonMarkup += '<button type="button" class="button page back" data-page="back"><i class="fa fas fa-caret-left"></i></button>';
                    for (var i = 0; i < storagePagesRequired; i++){
                        let pageNum = (i + 1);
                        let buttonMarkup = '<button type="button" class="button page' + (i === 0 ? ' active' : '') + '" data-page="' + pageNum + '">' + pageNum + '</button>';
                        pageButtonMarkup += buttonMarkup;
                        }
                    pageButtonMarkup += '<button type="button" class="button page next" data-page="next"><i class="fa fas fa-caret-right"></i></button>';
                pageButtonMarkup += '</div>';
                let objectCounterMarkup = '';
                objectCounterMarkup += '<div class="counter">';
                    objectCounterMarkup += '<span class="visible">' + storageObjectsVisible + '</span>';
                    objectCounterMarkup += '<span class="total">' + storageObjectsTotal + '</span>';
                objectCounterMarkup += '</div>';
                //console.log('-> appending pageButtonMarkup =', pageButtonMarkup);
                $storageObjectsDiv.append(pageButtonMarkup).attr('data-page', currentStoragePageNum);
                $storageObjectsDiv.append(objectCounterMarkup);
                //console.log('-> binding click events to page buttons...');
                $('.button[data-page]', $storageObjectsDiv).bind('mouseenter', function(e){ _self.playSoundEffect('icon-hover'); });
                $('.button[data-page]', $storageObjectsDiv).bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage (' + storageKind + ') page button clicked!', 'color: cyan;');
                    let $button = $(this);
                    if ($button.is('.active')){ return; } // already on this page, ignore clicks
                    _self.playSoundEffect('icon-click-mini');
                    let curNum = parseInt($storageObjectsDiv.attr('data-page'));
                    let pageNum = $button.attr('data-page');
                    //console.log('-> curNum =', curNum);
                    //console.log('-> pageNum =', pageNum);
                    if (pageNum === 'back'){ pageNum = curNum - 1; }
                    else if (pageNum === 'next'){ pageNum = curNum + 1; }
                    else if (typeof pageNum === 'number'){ pageNum = parseInt(pageNum); }
                    if (!pageNum || pageNum < 1){ pageNum = 1; }
                    //console.log('-> pageNum =', pageNum);
                    if (pageNum === curNum){ return; } // already on this page, ignore clicks
                    //console.log('-> going to storage page ' + pageNum + ' for ' + storageKind);
                    goToStoragePage(storageKind, pageNum);
                    // Return true on success
                    return true;
                    });
                };
            // Define a function for navigating to a specific storage page of either robots, items, or abilities
            let goToStoragePage = function(storageKind, pageNum){
                //console.log('%c' + '-> goToStoragePage(' + storageKind + ', ' + pageNum + ') triggered', 'color: magenta;');
                if (!storageKind || typeof storageKind !== 'string'){ return false; }
                if (!pageNum || typeof pageNum !== 'number'){ pageNum = parseInt(pageNum) || 1; }
                let storageConfig = calculateStorage(storageKind);
                if (!storageConfig){ return false; }
                //console.log('-> storageConfig = ', storageConfig);
                let $storageObjectsDiv = storageConfig.storageDiv;
                //let $storageObjectsWrapper = storageConfig.storageWrapper;
                //let $storageObjectsInOverview = storageConfig.storageObjects;
                let storageObjectSelector = storageConfig.objectSelector;
                let storageObjectFilter = storageConfig.objectFilter;
                let storagePagesRequired = storageConfig.numPages;
                let storageSlotsPerPage = storageConfig.numSlotsPerPage;
                let currentStoragePageNum = storageConfig.currentPage;
                //console.log('-> $storageObjectsDiv = ', $storageObjectsDiv);
                //console.log('-> $storageObjectsWrapper = ', $storageObjectsWrapper);
                //console.log('-> $storageObjectsInOverview = ', $storageObjectsInOverview);
                //console.log('-> storageObjectSelector = ', storageObjectSelector);
                //console.log('-> storageSlotsPerPage = ', storageSlotsPerPage);
                //console.log('-> storagePagesRequired = ', storagePagesRequired);
                //console.log('-> currentStoragePageNum = ', currentStoragePageNum);
                if (!pageNum || pageNum < 1){ pageNum = 1; }
                else if (pageNum > storageConfig.numPages){ pageNum = storageConfig.numPages; }
                let startIndex = (pageNum - 1) * storageSlotsPerPage;
                let endIndex = startIndex + storageSlotsPerPage;
                //console.log('-> goToStoragePage() for pageNum ' + pageNum + ' with startIndex ' + startIndex + ' and endIndex ' + endIndex);
                //$storageObjectsInOverview = $(storageObjectSelector, $storageObjectsDiv);
                //console.log('-> $storageObjectsInOverview = ', $storageObjectsInOverview.length, $storageObjectsInOverview);
                let $objectsList = $(storageObjectSelector, $storageObjectsDiv);
                let $objectsListFiltered = $(storageObjectSelector+storageObjectFilter, $storageObjectsDiv);
                //console.log('-> $objectsList = ', $objectsList.length, $objectsList);
                //console.log('-> $objectsListFiltered = ', $objectsListFiltered.length, $objectsListFiltered);
                $('.bullet[data-key]', $storageObjectsDiv).text(''); // clear the bullets
                $objectsList.removeAttr('data-slot');
                $objectsList.addClass('hidden');
                $objectsListFiltered.slice(startIndex, endIndex).removeClass('hidden').each(function(index){
                    //console.log('-> adding slot to object at index ' + index + ' (data-slot will be ' + (index + 1) + ')');
                    let $object = $(this);
                    let newSlot = (index + 1);
                    let overallPosition = (startIndex + index + 1);
                    $object.attr('data-slot', newSlot);
                    $('.bullet[data-key="'+index+'"]', $storageObjectsDiv).text(overallPosition);
                    });
                $objectsList.filter('.hidden').removeClass('hovered selected');
                currentStoragePageNum = pageNum;
                $storageObjectsDiv.attr('data-page', currentStoragePageNum);
                //console.log('-> currentStoragePageNum =', currentStoragePageNum);
                //console.log('-> storagePagesRequired =', storagePagesRequired);
                $('.button[data-page]', $storageObjectsDiv).removeClass('active').removeClass('disabled');
                $('.button[data-page="' + pageNum + '"]', $storageObjectsDiv).addClass('active');
                if (currentStoragePageNum === 1){
                    //console.log('-> $storageObjectsDiv buttons... ', $('.button', $storageObjectsDiv));
                    //console.log('-> disabling back button');
                    $('.button[data-page="back"]', $storageObjectsDiv).addClass('disabled');
                    }
                if (currentStoragePageNum === storagePagesRequired){
                    //console.log('-> disabling next button');
                    $('.button[data-page="next"]', $storageObjectsDiv).addClass('disabled');
                    }
                // If there's an active selection that is now hidden, clear it and any details panel it invoked
                let $hiddenSelected = $objectsList.filter('.selected.hidden');
                //console.log('-> $hiddenSelected =', $hiddenSelected.length, $hiddenSelected);
                if ($hiddenSelected.length){
                    //console.log('-> hidden objects selected, so clear selection!', '\n --> $hiddenSelected =', $hiddenSelected);
                    $hiddenSelected.each(function(){
                        let $object = $(this);
                        let objectKind, objectToken;
                        if ($object.is('[data-ability]')){
                            objectKind = 'ability';
                            objectToken = $object.attr('data-ability');
                            //console.log('-> found hidden selected ability:', objectToken);
                            } else if ($object.is('[data-item]')){
                            objectKind = 'item';
                            objectToken = $object.attr('data-item');
                            //console.log('-> found hidden selected item:', objectToken);
                            } else if ($object.is('[data-robot]')){
                            objectKind = 'robot';
                            objectToken = $object.attr('data-robot');
                            //console.log('-> found hidden selected robot:', objectToken);
                            } else {
                            //console.log('-> could not determine kind of hidden selected object, skipping...');
                            return;
                            }
                        //console.log('-> clearing selection for hidden selected ' + objectKind + ' ' + objectToken);
                        $object.removeClass('selected');
                        $robotsOverview.find('.storage-details[data-' + objectKind + '="' + objectToken + '"]').remove();
                        });
                    //console.log('-> also remove any lingering incompatibility classes on the team robots');
                    $('.team-robot[data-robot].incompatible', $teamRobotsDiv).removeClass('incompatible');
                    $('.team-robot[data-robot].equipped', $teamRobotsDiv).removeClass('equipped');
                    }
                };
            // Define a function for sorting a given storage page of either robots, items, or abilities by a given sort-token
            let sortStoragePage = function(storageKind, sortToken, sortDirection, goToPageNum){
                //console.log('%c' + '-> sortStoragePage(' + storageKind + ', ' + sortToken + ', ' + sortDirection + ') triggered', 'color: magenta;');
                if (!storageKind || typeof storageKind !== 'string'){ return false; }
                if (!sortToken || typeof sortToken !== 'string'){ return false; }
                if (!sortDirection || (sortDirection !== 'up' && sortDirection !== 'down')){ sortDirection = 'down'; }
                if (!goToPageNum || typeof goToPageNum !== 'number'){ goToPageNum = 0; }
                let storageConfig = calculateStorage(storageKind);
                if (!storageConfig){ return false; }
                //console.log('-> storageConfig = ', storageConfig);
                let $storageObjectsDiv = storageConfig.storageDiv;
                let $storageObjectsWrapper = storageConfig.storageWrapper;
                let storageObjectSelector = storageConfig.objectSelector;
                let storageObjectFilter = storageConfig.objectFilter;
                let storageSlotsPerPage = storageConfig.numSlotsPerPage;
                let currentToggleStates = storageConfig.toggleStates;
                //console.log('-> $storageObjectsDiv = ', $storageObjectsDiv);
                //console.log('-> $storageObjectsWrapper = ', $storageObjectsWrapper);
                //console.log('-> storageObjectSelector = ', storageObjectSelector);
                //console.log('-> currentToggleStates = ', currentToggleStates);
                if (!sortToken || !sortDirection){ return false; }
                // update the parent container and buttons to reflect the current sort
                let $sortParent = $('.sorts', $storageObjectsDiv);
                $sortParent.attr('data-dir', sortDirection);
                $sortParent.find('.sort').removeClass('active');
                $sortParent.find('.sort[data-sort="' + sortToken + '"]').addClass('active');
                // sort the objects and then re-display the current page
                let $objectsList = $(storageObjectSelector, $storageObjectsDiv);
                let $objectsListFiltered = $(storageObjectSelector+storageObjectFilter, $storageObjectsDiv);
                let dataSortAttr = 'data-' + sortToken;
                let fallbackSortAttr = dataSortAttr !== 'data-index-key' ? 'data-index-key' : 'data-storage-key';
                let reverseSort = sortDirection === 'up' ? true : false;
                // check to see if we should push disabled/incompatible/outofstock to the bottom of the list
                let pushIncompatibleToBottom = false;
                if (typeof currentToggleStates['incompatible'] !== 'undefined'
                    && currentToggleStates['incompatible'] === 'hidden'){
                    pushIncompatibleToBottom = true;
                    }
                // and now finally we can sort the object list itself
                $objectsList.sort(function(a, b){
                    if (pushIncompatibleToBottom){
                        let aIncompatible = $(a).is('.incompatible') ? 1 : 0;
                        let bIncompatible = $(b).is('.incompatible') ? 1 : 0;
                        if (aIncompatible < bIncompatible){ return -1; }
                        if (aIncompatible > bIncompatible){ return 1; }
                        }
                    // check if the objects exist in the filtered list too
                    let aIndex = $objectsListFiltered.index(a);
                    let bIndex = $objectsListFiltered.index(b);
                    let aToken = parseFloat($(a).attr(dataSortAttr) || '0');
                    let bToken = parseFloat($(b).attr(dataSortAttr) || '0');
                    let aToken2 = parseFloat($(a).attr(fallbackSortAttr) || '0');
                    let bToken2 = parseFloat($(b).attr(fallbackSortAttr) || '0');
                    if (aIndex === -1 && bIndex !== -1){ return 1; }
                    if (aIndex !== -1 && bIndex === -1){ return -1; }
                    if (aToken < bToken){ return !reverseSort ? -1 : 1; }
                    if (aToken > bToken){ return !reverseSort ? 1 : -1; }
                    if (aToken2 < bToken2){ return -1; }
                    if (aToken2 > bToken2){ return 1; }
                    return 0;
                    });
                //console.log('-> storageConfig =', storageConfig);
                //console.log('-> $objectsList =', $objectsList);
                //console.log('-> dataSortAttr =', dataSortAttr);
                //console.log('-> reverseSort =', reverseSort);
                $storageObjectsWrapper.empty().prepend($objectsList);
                //console.log('%c' + 'Time to re-key the data-sort-page for all ' + $objectsList.length + ' storage objects', 'color: lime;');
                $objectsList.each(function(index){
                    let $object = $(this), page = Math.ceil((index + 1) / storageSlotsPerPage);
                    $object.attr('data-sort-page', page);
                    });
                // Re-display the current page to reflect the new sort order
                if (!goToPageNum){ goToPageNum = parseInt($storageObjectsDiv.attr('data-page') || '0'); }
                goToStoragePage(storageKind, goToPageNum);
                // Return true on success
                return true;
                };
            // Define a function for refreshing a given storage page either robots, items, or abilities
            // (basically, re-sorting by whatever current settings are and then going to whatever page we're already on)
            let refreshStoragePage = function(storageKind){
                //console.log('%c' + '-> refreshStoragePage(' + storageKind + ') triggered', 'color: magenta;');
                if (!storageKind || typeof storageKind !== 'string'){ return false; }
                let storageConfig = calculateStorage(storageKind, true);
                if (!storageConfig){ return false; }
                //console.log('-> storageConfig = ', storageConfig);
                let overviewIsExpanded = $robotsOverview.is('.expanded') ? true : false;
                let $storageObjectsDiv = storageConfig.storageDiv;
                let currentStoragePageNum = storageConfig.currentPage;
                //console.log('-> $storageObjectsDiv = ', $storageObjectsDiv);
                //console.log('-> currentStoragePageNum = ', currentStoragePageNum);
                let $sortParent = $('.sorts', $storageObjectsDiv);
                let $activeSortButton = $('.sort.active', $sortParent);
                let selectedSortToken = false;
                if ($activeSortButton && $activeSortButton.length){ selectedSortToken = $activeSortButton.attr('data-sort') || false; }
                let selectedSortDirection = $sortParent.is('[data-dir]') ? $sortParent.attr('data-dir') : false;
                //console.log('-> selectedSortToken =', selectedSortToken);
                //console.log('-> selectedSortDirection =', selectedSortDirection);
                if (!selectedSortToken || !selectedSortDirection){ return false; }
                //console.log('-> re-sorting by ' + selectedSortToken + ' (' + selectedSortDirection + ') and going to page ' + currentStoragePageNum);
                if (overviewIsExpanded){ makeStoragePages(storageKind); }
                sortStoragePage(storageKind, selectedSortToken, selectedSortDirection, currentStoragePageNum);
                if (overviewIsExpanded){ goToStoragePage(storageKind, currentStoragePageNum); }
                // Return true on success
                return true;
                };
            // Define a function for refreshing the object refs of the robots in the team and storage panels
            let refreshRobotRefs = function(){
                //console.log('%c' + '-> refreshRobotRefs() triggered', 'color: magenta;');
                $robotsOnMap = $teamSprites.filter('.robot:not(.cursor)');
                $teamRobotsInOverview = $('.team-robot[data-robot]', $teamRobotsDiv);
                $storageRobotsInOverview = $('.team-robot[data-robot]', $storageRobotsDiv);
                };
            // Define a function for refreshing the backup clones of the robots in the team and storage panels
            let refreshRobotBackups = function(refreshReferences){
                //console.log('%c' + '-> refreshRobotBackups() triggered', 'color: magenta;');
                $teamRobotsInOverviewBackup = $teamRobotsInOverview.clone(true).detach(); // save for later
                $storageRobotsInOverviewBackup = $storageRobotsInOverview.clone(true).detach(); // save for later
                };
            // Define a function for making the storage bullets to show up on certain storage boxes
            let makeStorageBullets = function(){
                //console.log('%c' + 'makeStorageBullets() called!', 'color: magenta;');
                let storageSlotsPerPage = _config.robotStorageSlotsVisible;
                $('.bullets', $storageRobotsDiv).remove();
                let listBulletsMarkup = '';
                listBulletsMarkup += '<div class="bullets">';
                    for (var i = 0; i < storageSlotsPerPage; i++){
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
            // Define a function for refreshing the robots div in the storage panel given new conditions
            let refreshRobotsDiv = function(){
                //console.log('%c' + '-> refreshRobotsDiv() triggered', 'color: magenta;');
                if ($robotsOverview.is('[data-view="abilities"]')){
                    let $currentTeamRobots = $('.team-robot[data-robot]', $teamRobotsDiv);
                    let $selectedAbility = $('.team-ability[data-ability].selected', $storageAbilitiesDiv);
                    let abilitySelected = $selectedAbility.length ? true : false;
                    let abilityToken = abilitySelected ? $selectedAbility.attr('data-ability') : '';
                    let abilityID = abilitySelected ? parseInt($selectedAbility.attr('data-ability-id') || '0') : 0;
                    //console.log('-> mark robots incompatibile and/or equipped w/ abilityToken =', abilityToken);
                    if (!abilitySelected){
                        $currentTeamRobots.removeClass('equipped incompatible');
                        } else {
                        $currentTeamRobots.each(function(){
                            let $robot = $(this);
                            let robotToken = $robot.attr('data-robot') || false;
                            let robotData = _worldPlayerRobots[robotToken] || false;
                            if (!robotToken || !robotData){ $robot.removeClass('equipped incompatible'); return; }
                            //console.log('-> ' + abilityToken + ' vs. ' + robotToken + ' ...');
                            //console.log('-> ' + abilityToken + ' vs. ' + robotToken + ' ...', '\n-> robotData =', robotData);
                            let robotAbilities = [];
                            let abilitiesEquipped = robotData.abilities;
                            let abilitiesCompatible = robotData.abilitiesCompatible;
                            let abilitiesViaItem = robotData.abilitiesViaItem;
                            if (typeof abilitiesCompatible !== 'undefined'){ robotAbilities = robotAbilities.concat(abilitiesCompatible); }
                            if (typeof abilitiesViaItem !== 'undefined'){ robotAbilities = robotAbilities.concat(abilitiesViaItem); }
                            let isEquipped = abilitiesEquipped.indexOf(abilityID) !== -1 ? true : false;
                            let isCompatible = robotAbilities.indexOf(abilityID) !== -1 ? true : false;
                            //console.log('--> isEquipped =', isEquipped);
                            //console.log('--> isCompatible =', isCompatible);
                            if (isEquipped){ $robot.addClass('equipped'); }
                            else { $robot.removeClass('equipped'); }
                            if (!isCompatible){ $robot.addClass('incompatible'); }
                            else { $robot.removeClass('incompatible'); }
                            });
                        }
                    }
                // return true on success
                return true;
                };
            // Define a function for refreshing the items div in the storage panel given new conditions
            let refreshItemsDiv = function(){
                //console.log('%c' + '-> refreshItemsDiv() triggered', 'color: magenta;');
                // check if there's a robot target for these items to or not
                refreshRobotRefs();
                let $selectedRobot = $teamRobotsInOverview.filter('.team-robot[data-robot].selected').first();
                let targetSelected = $selectedRobot && $selectedRobot.length ? true : false;
                // refresh items to make sure they're sorted
                refreshStoragePage('items');
                // if there's a details popup onscreen, make sure we refresh w/ targetSelected status
                let $detailsDiv = $robotsOverview.find('.storage-details[data-item]');
                if ($detailsDiv.length){
                    let itemToken = $detailsDiv.attr('data-item') || false;
                    //console.log('-> refreshing details for itemToken =', itemToken);
                    let itemDetails = _self.getItemDetailsForOverview(itemToken, targetSelected) || false;
                    if (itemDetails && Object.keys(itemDetails).length){ _self.replaceItemDetailsInOverview($detailsDiv, itemToken, itemDetails); }
                    }
                // return true on success
                return true;
                };
            // Define a function for refreshing the ability div in the storage panel given new conditions
            let refreshAbilitiesDiv = function(){
                //console.log('%c' + '-> refreshAbilitiesDiv() triggered', 'color: magenta;');
                // check if there's a robot to filter abilities to or not
                refreshRobotRefs();
                let $selectedRobot = $teamRobotsInOverview.filter('.team-robot[data-robot].selected').first();
                let targetSelected = $selectedRobot && $selectedRobot.length ? true : false;
                // filter abilities to selected robot if applicable
                if (targetSelected){ filterAbilitiesToSelected($selectedRobot); }
                else { filterAbilitiesToSelected(false); }
                // if there's a details popup onscreen, make sure we refresh w/ targetSelected status
                let $detailsDiv = $robotsOverview.find('.storage-details[data-ability]');
                if ($detailsDiv.length){
                    let abilityToken = $detailsDiv.attr('data-ability') || false;
                    //console.log('-> refreshing details for abilityToken =', abilityToken);
                    let abilityDetails = _self.getAbilityDetailsForOverview(abilityToken, targetSelected) || false;
                    if (abilityDetails && Object.keys(abilityDetails).length){ _self.replaceAbilityDetailsInOverview($detailsDiv, abilityToken, abilityDetails); }
                    }
                // collect the incompatibilty toggle and show/hide it based on whether something is selected or not
                let $incompatibleToggle = $('.toggle[data-toggle="incompatible"]', $storageAbilitiesDiv);
                if ($selectedRobot && $selectedRobot.length){ $incompatibleToggle.removeClass('disabled'); }
                else { $incompatibleToggle.addClass('disabled'); }
                // return true on success
                return true;
                };
            // Define a quick function for filtering abilities to the currently selected robot
            let filterAbilitiesToSelected = function($selectedRobot){
                //console.log('%c' + '-> filterAbilitiesToSelected($selectedRobot) triggered', 'color: magenta;');
                let $abilityObjectsInOverview = $('.team-ability[data-ability]', $storageAbilitiesDiv);
                //console.log('-> $abilityObjectsInOverview =', $abilityObjectsInOverview.length, $abilityObjectsInOverview);
                if (!$selectedRobot || !$selectedRobot.length){
                    $abilityObjectsInOverview.removeClass('incompatible equipped');
                    refreshStoragePage('abilities');
                    return false;
                    }
                let selectedRobotToken = $selectedRobot.attr('data-robot') || false;
                //console.log('-> selectedRobotToken =', selectedRobotToken);
                if (!selectedRobotToken || !selectedRobotToken.length){ return false; }
                let selectedRobotData = _worldPlayerRobots[selectedRobotToken] || false;
                //console.log('-> selectedRobotData =', selectedRobotData);
                if (!selectedRobotData){ return false; }
                let selectedRobotAbilities = [];
                let abilitiesEquipped = selectedRobotData.abilities || [];
                let abilitiesCompatible = selectedRobotData.abilitiesCompatible || [];
                let abilitiesViaItem = selectedRobotData.abilitiesViaItem || [];
                //console.log('-> abilitiesCompatible =', abilitiesCompatible);
                //console.log('-> abilitiesViaItem =', abilitiesViaItem);
                if (typeof abilitiesCompatible !== 'undefined'){ selectedRobotAbilities = selectedRobotAbilities.concat(abilitiesCompatible); }
                if (typeof abilitiesViaItem !== 'undefined'){ selectedRobotAbilities = selectedRobotAbilities.concat(abilitiesViaItem); }
                //console.log('-> selectedRobotAbilities =', selectedRobotAbilities);
                $abilityObjectsInOverview.each(function(){
                    let $ability = $(this);
                    let abilityID = parseInt($ability.attr('data-ability-id') || '0');
                    if (!abilityID){ return; }
                    let isEquipped = abilitiesEquipped.indexOf(abilityID) !== -1 ? true : false;
                    let isCompatible = selectedRobotAbilities.indexOf(abilityID) !== -1 ? true : false;
                    if (isEquipped){ $ability.addClass('equipped'); }
                    else { $ability.removeClass('equipped'); }
                    if (!isCompatible){ $ability.addClass('incompatible'); }
                    else { $ability.removeClass('incompatible'); }
                    });
                // If there's an active selection that is now incompatible, clear it and any details panel it invoked
                let $incompatibleSelected = $abilityObjectsInOverview.filter('.selected.incompatible');
                //console.log('-> $incompatibleSelected =', $incompatibleSelected.length, $incompatibleSelected);
                if ($incompatibleSelected.length){
                    //console.log('-> incompatible ability selected, so clear selection!');
                    $incompatibleSelected.each(function(){
                        let $ability = $(this);
                        let abilityToken = $ability.attr('data-ability');
                        //console.log('-> clearing selection for abilityToken =', abilityToken);
                        $ability.removeClass('selected');
                        $robotsOverview.find('.storage-details[data-ability="' + abilityToken + '"]').remove();
                        });
                    }
                // Refresh the ability storage page now that we've updated compatibility
                refreshStoragePage('abilities');
                };
            // Define a quick function for checking to see which, if any, objects are selected and showing the
            // relevant details panel for them or clearing it if none are selected, keeping priority in mind
            // when multiple are selected (storage-object > team-robot)
            let refreshDetailsPanel = function(){
                //console.log('%c' + '-> refreshDetailsPanel() triggered', 'color: magenta;');
                // Collect storage kind and config and make sure they're valid
                let storageKind = $robotsOverview.attr('data-view') || '';
                //console.log('-> storageKind = ', storageKind);
                if (!storageKind.length){ return false; }
                let storageConfig = calculateStorage(storageKind, true);
                //console.log('-> storageConfig = ', storageConfig);
                if (!storageConfig){ return false; }
                // Check if there's a team robot selected first
                let robotClass = '.team-robot[data-robot]';
                let $selectedTeamRobot = $teamRobotsDiv.find(robotClass + '.selected').first();
                let teamRobotSelected = $selectedTeamRobot && $selectedTeamRobot.length ? true : false;
                //console.log('-> robotClass = ', robotClass);
                //console.log('-> $selectedTeamRobot = ', $selectedTeamRobot.length, $selectedTeamRobot);
                // Now check if there is a storage object selected next
                let objectKind = storageConfig.kind, objectKindX = storageConfig.xkind;
                let objectClass = '.team-' + objectKind + '[data-' + objectKind + ']';
                let $storageObjectsDiv = storageConfig.storageDiv;
                let $selectedStorageObject = $storageObjectsDiv.find(objectClass + '.selected').first();
                let storageObjectSelected = $selectedStorageObject && $selectedStorageObject.length ? true : false;
                //console.log('-> objectKind = ', objectKind, objectKindX);
                //console.log('-> objectClass = ', objectClass);
                //console.log('-> $storageObjectsDiv = ', $storageObjectsDiv.length, $storageObjectsDiv);
                //console.log('-> $selectedStorageObject = ', $selectedStorageObject.length, $selectedStorageObject);
                // Update the reference to the storage details div (in case already exists)
                $storageDetailsDiv = $robotsOverview.find('.storage-details');
                // Check to see if a details div has already been added given above
                let $detailsDiv = $storageDetailsDiv;
                let detailsDivExists = $detailsDiv && $detailsDiv.length ? true : false;
                //let detailsDivSameKind = false; //detailsDivExists && $detailsDiv.is('[data-' + objectKind + ']') ? true : false;
                //console.log('-> $detailsDiv = ', $detailsDiv.length, $detailsDiv);
                //console.log('-> detailsDivExists = ', detailsDivExists);
                //console.log('-> detailsDivSameKind = ', detailsDivSameKind);
                // Make sure we pre-queue a removal of the wait class for animation purposes
                let delayAnimationsThen = function(callback){
                    //console.log('delayAnimationsThen()');
                    //console.log('overviewIsOpening =', overviewIsOpening);
                    if (overviewIsOpening){
                        let delayFor = 300;
                        $overviewWrapper.addClass('wait');
                        if (_selfRef.detailsTimeout){ clearTimeout(_selfRef.detailsTimeout); }
                        _selfRef.detailsTimeout = setTimeout(function(){ $overviewWrapper.removeClass('wait'); }, delayFor);
                        }
                    if (typeof callback === 'function'){ callback.call(this); }
                    };
                // Define some reusable inline functions for less code
                let showRobotIntroAnimation = function(){
                    //console.log('showing intro animation for robot!');
                    let $robotSprite = $detailsDiv.find('.image > .sprite');
                    if (_selfRef.onRobotIntro){ clearTimeout(_selfRef.onRobotIntro); }
                    if (_selfRef.onAfterRobotIntro){ clearTimeout(_selfRef.onAfterRobotIntro); }
                    _selfRef.onRobotIntro = setTimeout(function(){ $robotSprite.attr('data-frame', '01'); }, 0);
                    _selfRef.onAfterRobotIntro = setTimeout(function(){ if ($robotSprite.attr('data-frame') !== '01'){ return; } $robotSprite.attr('data-frame', '00'); }, 900);
                    };
                // If there isn't anything selected, we need to clear any existing details panel
                if (!teamRobotSelected && !storageObjectSelected){
                    //console.log('%c' + '-> no objects selected, so clear any existing details panel!', 'color: orange;');
                    if (detailsDivExists){ $detailsDiv.remove();}
                    return;
                    }
                // Else if there's a storage object selected, show the details for that object
                else if (storageObjectSelected){
                    //console.log('%c' + '-> storage object (' + objectKind + ') selected....', 'color: cyan;');
                    if (objectKind === 'robot'){
                        let robotToken = $selectedStorageObject.attr('data-robot') || false;
                        //console.log('%c' + '-> storage robot selected (' + robotToken + '), so show its details panel!', 'color: cyan;');
                        let detailsDivSameKind = detailsDivExists && $detailsDiv.is('[data-robot]') ? true : false;
                        let detailsDivSameRobot = detailsDivSameKind && $detailsDiv.attr('data-robot') === robotToken ? true : false;
                        let showIntroAnimation = !detailsDivSameKind || !detailsDivSameRobot ? true : false;
                        //console.log('detailsDivSameKind =', detailsDivSameKind);
                        //console.log('detailsDivSameRobot =', detailsDivSameRobot);
                        //console.log('showIntroAnimation =', showIntroAnimation);
                        if (!detailsDivExists || !detailsDivSameKind){
                            //console.log('-> populate new details div for robotToken =', robotToken);
                            if (detailsDivExists){ $detailsDiv.remove(); }
                            let robotMarkup = _self.getRobotDetailsMarkupForOverview(robotToken) || false;
                            if (!robotMarkup || !robotMarkup.length){ return false; }
                            delayAnimationsThen(function(){
                                $overviewWrapper.append(robotMarkup);
                                $detailsDiv = $robotsOverview.find('.storage-details');
                                });
                            } else {
                            //console.log('-> refresh existing details div for robotToken =', robotToken);
                            let robotDetails = _self.getRobotDetailsForOverview(robotToken) || false;
                            if (!robotDetails || !Object.keys(robotDetails).length){ return false; }
                            _self.replaceRobotDetailsInOverview($detailsDiv, robotToken, robotDetails);
                            }
                        if (showIntroAnimation){ showRobotIntroAnimation(); }
                        }
                    else if (objectKind === 'item'){
                        let itemToken = $selectedStorageObject.attr('data-item') || false;
                        //console.log('%c' + '-> storage object is an item (' + itemToken + '), so show its details panel!', 'color: cyan;');
                        let detailsDivSameKind = detailsDivExists && $detailsDiv.is('[data-item]') ? true : false;
                        if (!detailsDivExists || !detailsDivSameKind){
                            //console.log('-> populate new details div for itemToken =', itemToken);
                            if (detailsDivExists){ $detailsDiv.remove(); }
                            let itemMarkup = _self.getItemDetailsMarkupForOverview(itemToken, teamRobotSelected) || false;
                            if (!itemMarkup || !itemMarkup.length){ return false; }
                            delayAnimationsThen(function(){
                                $overviewWrapper.append(itemMarkup);
                                $detailsDiv = $robotsOverview.find('.storage-details');
                                });
                            } else {
                            //console.log('-> refresh existing details div for itemToken =', itemToken);
                            let itemDetails = _self.getItemDetailsForOverview(itemToken, teamRobotSelected) || false;
                            if (!itemDetails || !Object.keys(itemDetails).length){ return false; }
                            _self.replaceItemDetailsInOverview($detailsDiv, itemToken, itemDetails);
                            }
                        }
                    else if (objectKind === 'ability'){
                        let abilityToken = $selectedStorageObject.attr('data-ability') || false;
                        //console.log('%c' + '-> storage object is an ability (' + abilityToken + '), so show its details panel!', 'color: cyan;');
                        let detailsDivSameKind = detailsDivExists && $detailsDiv.is('[data-ability]') ? true : false;
                        if (!detailsDivExists || !detailsDivSameKind){
                            //console.log('-> populate new details div for abilityToken =', abilityToken);
                            if (detailsDivExists){ $detailsDiv.remove(); }
                            let abilityMarkup = _self.getAbilityDetailsMarkupForOverview(abilityToken, teamRobotSelected) || false;
                            if (!abilityMarkup || !abilityMarkup.length){ return false; }
                            delayAnimationsThen(function(){
                                $overviewWrapper.append(abilityMarkup);
                                $detailsDiv = $robotsOverview.find('.storage-details');
                                });
                            } else {
                            //console.log('-> populate existing details div for abilityToken =', abilityToken);
                            let abilityDetails = _self.getAbilityDetailsForOverview(abilityToken, teamRobotSelected) || false;
                            if (!abilityDetails || !Object.keys(abilityDetails).length){ return false; }
                            _self.replaceAbilityDetailsInOverview($detailsDiv, abilityToken, abilityDetails);
                            }
                        }
                    }
                // Otherwise, if a team robot is selected at least, show the details for that robot
                else if (teamRobotSelected){
                    let robotToken = $selectedTeamRobot.attr('data-robot') || false;
                    //console.log('%c' + '-> team robot selected (' + robotToken + '), so show its details panel!', 'color: cyan;');
                    let detailsDivSameKind = detailsDivExists && $detailsDiv.is('[data-robot]') ? true : false;
                    let detailsDivSameRobot = detailsDivSameKind && $detailsDiv.attr('data-robot') === robotToken ? true : false;
                    let showIntroAnimation = !detailsDivSameKind || !detailsDivSameRobot ? true : false;
                    //console.log('detailsDivSameKind =', detailsDivSameKind);
                    //console.log('detailsDivSameRobot =', detailsDivSameRobot);
                    //console.log('showIntroAnimation =', showIntroAnimation);
                    if (!detailsDivExists || !detailsDivSameKind){
                        //console.log('-> populate new details div for robotToken =', robotToken);
                        if (detailsDivExists){ $detailsDiv.remove(); }
                        let robotMarkup = _self.getRobotDetailsMarkupForOverview(robotToken) || false;
                        if (!robotMarkup || !robotMarkup.length){ return false; }
                        delayAnimationsThen(function(){
                            $overviewWrapper.append(robotMarkup);
                            $detailsDiv = $robotsOverview.find('.storage-details');
                            });
                        } else {
                        //console.log('-> refresh existing details div for robotToken =', robotToken);
                        let robotDetails = _self.getRobotDetailsForOverview(robotToken) || false;
                        if (!robotDetails || !Object.keys(robotDetails).length){ return false; }
                        _self.replaceRobotDetailsInOverview($detailsDiv, robotToken, robotDetails);
                        }
                    if (showIntroAnimation){ showRobotIntroAnimation(); }
                    }
                //console.warn('TODO: check selected objects and show/hide details panel as needed!');
                // Return true on success
                return true;
                };
            // Bind events to any sort buttons in the storage box divs
            $storageBoxDivs.delegate('.sort[data-sort]', 'click', function(e){
                e.preventDefault();
                if (_self.worldIsBusy()){ return; }
                if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                //console.log('%c' + 'sort button clicked!', 'color: cyan;');
                _self.playSoundEffect('icon-click-mini');
                let $sortButton = $(this);
                let $sortParent = $sortButton.closest('.sorts');
                let $storageBoxDiv = $sortButton.closest('.storage-box');
                let $storageBoxWrapper = $('> .wrapper', $storageBoxDiv);
                let storageKind = $storageBoxDiv.attr('data-storage');
                let storagePage = parseInt($storageBoxDiv.attr('data-page') || '0');
                let sortToken = $sortButton.attr('data-sort') || false;
                let sortDirection = $sortParent.is('[data-dir]') ? $sortParent.attr('data-dir') : 'down';
                if (!$sortButton.is('.active')){ sortDirection = 'down'; storagePage = 1; } // default to down if not active yet
                else { sortDirection = sortDirection === 'down' ? 'up' : 'down'; }  // reverse if already active
                //console.log('-> sortToken =', sortToken);
                //console.log('-> sortDirection =', sortDirection);
                sortStoragePage(storageKind, sortToken, sortDirection, storagePage);
                // Return true on success
                return true;
                });
            // Bind events to any toggle buttons in the storage box divs
            $storageBoxDivs.delegate('.toggle[data-toggle]', 'click', function(e){
                e.preventDefault();
                if (_self.worldIsBusy()){ return; }
                if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                //console.log('%c' + 'toggle button clicked!', 'color: cyan;');
                let $toggleButton = $(this);
                let $toggleParent = $toggleButton.closest('.toggles');
                let $storageBoxDiv = $toggleButton.closest('.storage-box');
                let $storageBoxWrapper = $('> .wrapper', $storageBoxDiv);
                let storageKind = $storageBoxDiv.attr('data-storage');
                let toggleToken = $toggleButton.attr('data-toggle');
                let toggleStates = $toggleButton.find('[data-state]').map(function(){ return $(this).attr('data-state') || ''; }).get();
                //console.log('-> toggleToken =', toggleToken);
                //console.log('-> toggleStates =', toggleStates);
                if (!toggleToken.length || !toggleStates.length){ return; }
                let currToggleState = $toggleButton.is('[data-state]') ? $toggleButton.attr('data-state') : '';
                let nextToggleState = toggleStates.length ? toggleStates[(toggleStates.indexOf(currToggleState) + 1) % toggleStates.length] : '';
                //console.log('-> currToggleState =', currToggleState);
                //console.log('-> nextToggleState =', nextToggleState);
                if (!currToggleState || !nextToggleState){ return; }
                $toggleButton.attr('data-state', nextToggleState);
                refreshStoragePage(storageKind);
                });
            // Bind hover events to the robot buttons so they show appropriate state
            $teamRobotsDiv.delegate('.team-robot[data-robot]', 'mouseenter', hoverOverviewObject);
            $teamRobotsDiv.delegate('.team-robot[data-robot]', 'mouseleave', unhoverOverviewObject);
            $teamRobotsDiv.delegate('.team-robot[data-robot]', 'click', function(e){
                e.preventDefault();
                if (_self.worldIsBusy()){ return; }
                let $thisRobot = $(this);
                if (!$robotsOverview.is('.expanded')){
                    _self.playSoundEffect('icon-click-mini');
                    return showRobotsOverviewPanel('robots', function(){
                        $thisRobot.trigger('click');
                        }, false); }
                if ($thisRobot.is('.incompatible')){ return; } // if robot was marked incompatible, ignore clicked
                //console.log('%c' + 'Team robot clicked!', 'color: cyan;');
                let alreadySelected = $thisRobot.is('.selected') ? true : false;
                //console.log('-> $thisRobot =', $thisRobot);
                //console.log('-> alreadySelected =', alreadySelected);
                $storageRobotsDiv.find('.team-robot[data-robot]').removeClass('selected');
                $teamRobotsDiv.find('.team-robot[data-robot]').not($thisRobot).removeClass('selected');
                if (!alreadySelected){ _self.playSoundEffect('icon-click-mini'); $thisRobot.addClass('selected'); }
                else { _self.playSoundEffect('back-click'); $thisRobot.removeClass('selected'); }
                if ($robotsOverview.is('[data-view="robots"]')){ refreshRobotsDiv(); }
                if ($robotsOverview.is('[data-view="items"]')){ refreshItemsDiv(); }
                if ($robotsOverview.is('[data-view="abilities"]')){ refreshAbilitiesDiv(); }
                refreshDetailsPanel();
                // Return true on success
                return true;
                });
            // Make sure hovering over the robot sprite shifts its pose for a bit of animation
            let updateRobotImageFrame = function(image, newFrame, onlyWhen){
                if (_self.worldIsBusy()){ return; }
                let $image = $(image), $sprite = $image.find('> .sprite'), frame = $sprite.attr('data-frame');
                if (Array.isArray(onlyWhen) && onlyWhen.indexOf(frame) === -1){ return; }
                $sprite.attr('data-frame', newFrame);
                };
            $robotsOverview.delegate('.storage-details[data-robot] > .image', 'click', function(e){ _self.playSoundEffect('link-click-robot'); updateRobotImageFrame(this, '02'); }); // victory on click
            $robotsOverview.delegate('.storage-details[data-robot] > .image', 'mouseenter', function(e){ hoverOverviewObject.call(this); updateRobotImageFrame(this, '08'); }); // defend on hover
            $robotsOverview.delegate('.storage-details[data-robot] > .image', 'mouseleave', function(e){ unhoverOverviewObject.call(this); updateRobotImageFrame(this, '00', ['02', '08']); }); // base on reset
            // Make sure clicking relevant areas in the robot details triggers relevant functionality
            // - make sure clicking the item slot (empty or not) quick-swaps to the items tab
            // - make sure clicking the support tab ?????? (for now just show console.warn of TODO)
            // - make sure clicking any of the ability slots (empty or not) quick-swaps to the abilities tab
            // [held-item]
            $robotsOverview.delegate('.storage-details[data-robot] .held-item .value', 'click', function(e){
                e.preventDefault();
                if (_self.worldIsBusy()){ return; }
                //console.log('%c' + 'robot equipped item value clicked!', 'color: cyan;');
                //console.log('_world.currentScreen = ', _world.currentScreen);
                //console.log('_world.currentSubScreen = ', _world.currentSubScreen);
                if (_world.currentScreen !== 'robots-overview'){ return; }
                let $itemValue = $(this);
                let itemValueID = $itemValue.attr('data-item-id') || false;
                let $itemInStorage = itemValueID ? $storageItemsDiv.find('.team-item[data-item-id="' + itemValueID + '"]').first() : false;
                if (!$itemInStorage || !$itemInStorage.length){ $itemInStorage = false; }
                let itemClickFunction = function(delay){
                    //console.log('itemClickFunction()');
                    delay = typeof delay === 'number' ? delay : 600;
                    //console.log('$itemInStorage.length =', $itemInStorage.length);
                    if (!$itemInStorage){ return; }
                    let callback = function(){
                        //console.log('itemClickFunction().callback()');
                        let itemStoragePageNum = $itemInStorage ? parseInt($itemInStorage.attr('data-sort-page')) : 1;
                        goToStoragePage('items', itemStoragePageNum);
                        $itemInStorage.trigger('click');
                        $itemInStorage.addClass('hovered');
                        };
                    if (!delay){ callback(); }
                    else { setTimeout(callback, delay); }
                    };
                $itemValue.addClass('selected');
                if (_world.currentSubScreen === 'items'){ itemClickFunction(0); }
                else { showRobotsOverviewPanel('items', function(){ itemClickFunction(); }); }
                // Return true on success
                return true;
                });
            $robotsOverview.delegate('.storage-details[data-robot] .held-item .value', 'mouseenter', hoverOverviewObject);
            $robotsOverview.delegate('.storage-details[data-robot] .held-item .value', 'mouseleave', unhoverOverviewObject);
            // [equipped-abilities]
            $robotsOverview.delegate('.storage-details[data-robot] .equipped-abilities .value', 'click', function(e){
                e.preventDefault();
                if (_self.worldIsBusy()){ return; }
                //console.log('%c' + 'robot equipped ability value clicked!', 'color: cyan;');
                //console.log('_world.currentScreen = ', _world.currentScreen);
                //console.log('_world.currentSubScreen = ', _world.currentSubScreen);
                if (_world.currentScreen !== 'robots-overview'){ return; }
                let $abilityValue = $(this);
                let abilityValueID = $abilityValue.attr('data-ability-id') || false;
                let $abilityInStorage = abilityValueID ? $storageAbilitiesDiv.find('.team-ability[data-ability-id="' + abilityValueID + '"]').first() : false;
                if (!$abilityInStorage || !$abilityInStorage.length){ $abilityInStorage = false; }
                let abilityClickFunction = function(delay){
                    //console.log('abilityClickFunction()');
                    delay = typeof delay === 'number' ? delay : 600;
                    //console.log('$abilityInStorage.length =', $abilityInStorage.length);
                    if (!$abilityInStorage){ return; }
                    let callback = function(){
                        //console.log('abilityClickFunction().callback()');
                        let abilityStoragePageNum = $abilityInStorage ? parseInt($abilityInStorage.attr('data-sort-page')) : 1;
                        goToStoragePage('abilities', abilityStoragePageNum);
                        $abilityInStorage.trigger('click');
                        };
                    if (!delay){ callback(); }
                    else { setTimeout(callback, delay); }
                    };
                $abilityValue.addClass('selected');
                if (_world.currentSubScreen === 'abilities'){ abilityClickFunction(0); }
                else { showRobotsOverviewPanel('abilities', function(){ abilityClickFunction(); }); }
                // Return true on success
                return true;
                });
            $robotsOverview.delegate('.storage-details[data-robot] .equipped-abilities .value', 'mouseenter', hoverOverviewObject);
            $robotsOverview.delegate('.storage-details[data-robot] .equipped-abilities .value', 'mouseleave', unhoverOverviewObject);
            // Auto-trigger a sort at least once on all the storage boxes to ensure a default order
            $storageBoxDivs.each(function(){
                let $storageBoxDiv = $(this);
                let $sortParent = $('.sorts', $storageBoxDiv);
                let storageKind = $storageBoxDiv.attr('data-storage');
                let $activeSortButton = $('.sort.active', $sortParent);
                let $firstSortButton = $('.sort', $sortParent).first();
                let selectedSortToken = false;
                if ($activeSortButton && $activeSortButton.length){ selectedSortToken = $activeSortButton.attr('data-sort') || false; }
                else if ($firstSortButton && $firstSortButton.length){ selectedSortToken = $firstSortButton.attr('data-sort') || false; }
                let selectedSortDirection = $sortParent.is('[data-dir]') ? $sortParent.attr('data-dir') : false;
                //console.log('-> auto-triggering storage-sort for ' + storageKind + ' w/ ' + selectedSortToken + ' (' + selectedSortDirection + ')');
                if (selectedSortToken){ sortStoragePage(storageKind, selectedSortToken, selectedSortDirection); }
                });
            // Auto-disable buttons in the storage boxes to prevent tabbing issues in the browser
            $('.button', $storageBoxes).attr('disabled', 'disabled');
            // Refresh the backups now that we're done sorting and whatnot
            refreshRobotBackups();
            // Add all these methods to the API in case future code needs to access them too
            robotsOverviewAPI.disableOtherElements = disableOtherElements;
            robotsOverviewAPI.enableOtherElements = enableOtherElements;
            robotsOverviewAPI.showRobotsOverviewPanel = showRobotsOverviewPanel;
            robotsOverviewAPI.disableRobotsOverview = disableRobotsOverview;
            robotsOverviewAPI.calculateStorage = calculateStorage;
            robotsOverviewAPI.makeStoragePages = makeStoragePages;
            robotsOverviewAPI.makeStorageBullets = makeStorageBullets;
            robotsOverviewAPI.goToStoragePage = goToStoragePage;
            robotsOverviewAPI.refreshStoragePage = refreshStoragePage;
            robotsOverviewAPI.refreshRobotBackups = refreshRobotBackups;
            robotsOverviewAPI.refreshRobotsDiv = refreshRobotsDiv;
            robotsOverviewAPI.refreshItemsDiv = refreshItemsDiv;
            robotsOverviewAPI.refreshAbilitiesDiv = refreshAbilitiesDiv;
            robotsOverviewAPI.filterAbilitiesToSelected = filterAbilitiesToSelected;
            robotsOverviewAPI.refreshDetailsPanel = refreshDetailsPanel;
            //console.log('-> storageSlotsPerPage = ', storageSlotsPerPage);
            //console.log('-> storageRobotsWaiting = ', storageRobotsWaiting);
            //console.log('-> storagePagesRequired = ', storagePagesRequired);
            //console.log('-> currentStoragePageNum = ', currentStoragePageNum);
            // Bind a click event to the team-close button in the robots overview
            let $closeButton = $('.team-close', $robotsOverview);
            if ($closeButton && $closeButton.length){
                $closeButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return false; }
                    //console.log('%c' + 'Team close button clicked!', 'color: cyan;');
                    disableRobotsOverview();
                    return true;
                    });
                }
            // Bind a click event to the team-rotate button in the robots overview
            let $rotateButton = $('.team-rotate', $robotsOverview);
            if ($rotateButton && $rotateButton.length){
                $rotateButton.bind('mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $rotateButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return false; }
                    //if (_self.worldMapIsHidden()){ return false; }
                    //console.log('%c' + 'Team rotate button clicked!', 'color: cyan;');
                    let _worldPlayerTeam = _worldPlayer.team;
                    if (_worldPlayerTeam.length < 2){ return; } // nothing to rotate
                    // First we rotate the actual robot data in the world state by one position (if allowed)
                    //console.log('-> _worldPlayerTeam(before) =', _worldPlayerTeam.join(', '));
                    let firstRobotKey = _worldPlayerTeam.shift();
                    _worldPlayerTeam.push(firstRobotKey);
                    //console.log('-> _worldPlayerTeam(after) =', _worldPlayerTeam);
                    //console.log('-> _worldPlayer.team(after) =', _worldPlayer.team);
                    // Now we rotate the robots in the overview by moving the first robot to the end of the list
                    refreshRobotRefs();
                    let $firstOverviewRobot = $teamRobotsInOverview.first();
                    //console.log('-> $firstOverviewRobot =', $firstOverviewRobot);
                    $firstOverviewRobot.appendTo($teamRobotsWrapper);
                    // Play a sound effect to go along with the adding
                    _self.playSoundEffect('switch-in');
                    // Re-collect the team robots in overview so we have the new order
                    refreshRobotRefs();
                    let newTeamRobotKeys = [];
                    $teamRobotsInOverview.each(function(){
                        let robotKey = $(this).attr('data-robot');
                        newTeamRobotKeys.push(robotKey);
                        });
                    //console.log('-> newTeamRobotKeys =', newTeamRobotKeys);
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
                    // make sure we do the same thing to these current robots in storage
                    for (var i = 0; i < newTeamRobotKeys.length; i++){
                        let robotKey = newTeamRobotKeys[i];
                        let $storageRobot = $('.team-robot[data-robot="' + robotKey + '"]', $storageRobotsDiv);
                        let storageKey = i;
                        $storageRobot.attr('data-storage-key', storageKey);
                        }
                    // Re-sort the storage page to ensure everything makes sense now
                    refreshStoragePage('robots');
                    // Update the team/robot robot backups w/ recent changes
                    refreshRobotBackups();
                    // Save the world state w/ these changes
                    _self.saveWorldState();
                    // Return true on success
                    return true;
                    });
                }
            // Bind a click event to the team-switch button in the robots overview
            let $switchButton = $('.team-switch', $robotsOverview);
            if ($switchButton && $switchButton.length){
                // expand/collapse the robot storage tray by clicking the switch button
                $switchButton.bind('mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $switchButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    //console.log('%c' + 'Team-switch (robot-storage) button clicked!', 'color: cyan;');
                    // Check if this panel is already active, and if so we collapse everything
                    let alreadyExpanded = $robotsOverview.is('.expanded[data-view="robots"]') ? true : false;
                    if (alreadyExpanded){ disableRobotsOverview(); return; }
                    // Expand the robot overview to the robot-storage view panel
                    showRobotsOverviewPanel('robots');
                    // Return true on success
                    return true;
                    });
                // if the storage tray is open, clicking a robot in the storage-list  highlights it for interaction
                $storageRobotsDiv.delegate('.team-robot[data-robot]', 'mouseenter', hoverOverviewObject);
                $storageRobotsDiv.delegate('.team-robot[data-robot]', 'mouseleave', unhoverOverviewObject);
                $storageRobotsDiv.delegate('.team-robot[data-robot]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage robot clicked!', 'color: cyan;');
                    // Collect reference to the click storage robot and its slot first
                    let $clickedStorageRobot = $(this);
                    let clickedStorageSlot = $clickedStorageRobot.is('[data-slot]') ? $clickedStorageRobot.attr('data-slot') : false;
                    let alreadySelected = $clickedStorageRobot.is('.selected') ? true : false;
                    //console.log('-> $clickedStorageRobot =', $clickedStorageRobot);
                    //console.log('-> clickedStorageSlot =', clickedStorageSlot);
                    if (!$clickedStorageRobot || !$clickedStorageRobot.length){ return false; } // if no robot is clicked, ignore clicks
                    if (!clickedStorageSlot || isNaN(parseInt(clickedStorageSlot))){ return false; } // if no slot, ignore clicks
                    if ($clickedStorageRobot.is('.current')){ return false; } // already on team, ignore clicks
                    // Refresh the current list of team robots in the overview and storage
                    refreshRobotRefs();
                    // Toggle the selected state of the clicked storage robot
                    $teamRobotsDiv.find('.team-robot[data-robot]').removeClass('selected');
                    $storageRobotsDiv.find('.team-robot[data-robot]').not($clickedStorageRobot).removeClass('selected');
                    if (!alreadySelected){ _self.playSoundEffect('icon-click-mini'); $clickedStorageRobot.addClass('selected'); }
                    else { _self.playSoundEffect('back-click'); $clickedStorageRobot.removeClass('selected'); }
                    // Refresh the details panel with the new changes and then return
                    refreshDetailsPanel();
                    // Return true on success
                    return true;
                    });
                // add functionality to the action buttons within robot details panels
                $robotsOverview.delegate('.storage-details[data-robot] .button[data-action]', 'mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $robotsOverview.delegate('.storage-details[data-robot] .button[data-action]', 'click', function(e){
                    //console.log('%c' + 'Storage robot details button clicked!', 'color: cyan;');
                    e.preventDefault();
                    let $actionButton = $(this);
                    let actionToken = $actionButton.attr('data-action');
                    //console.log('-> actionToken =', actionToken);
                    let $detailsDiv = $robotsOverview.find('.storage-details[data-robot]');
                    //console.log('-> $detailsDiv =', $detailsDiv.length, $detailsDiv);
                    if (!$detailsDiv.is('[data-robot]')){ return; }
                    let robotToken = $detailsDiv.attr('data-robot');
                    //console.log('-> robotToken =', robotToken);
                    let $targetRobot = $teamRobotsInOverview.filter('.team-robot[data-robot].selected').first();
                    let targetRobotToken = $targetRobot && $targetRobot.length ? $targetRobot.attr('data-robot') : false;
                    //console.log('-> targetRobotToken =', targetRobotToken);
                    let autoClickAction = function(){ $actionButton.addClass('clicked'); _self.playSoundEffect('icon-click'); };
                    let actionModalConfig = {onComplete: function(){ $actionButton.removeClass('clicked'); }};
                    if (actionToken === 'add-robot'){ autoClickAction(); _self.showAddRobotModal(robotToken, actionModalConfig); }
                    else if (actionToken === 'remove-robot'){ autoClickAction(); _self.showRemoveRobotModal(robotToken, actionModalConfig); }
                    else { console.warn('-> undefined robot action "', actionToken, '", ignoring input'); return false; }
                    // Return true on success
                    return true;
                    });
                }
            // Bind a click event to the team-items button in the robots overview
            let $itemsButton = $('.team-items', $robotsOverview);
            if ($itemsButton && $itemsButton.length){
                // expand/collapse the item storage tray by clicking the items button
                $itemsButton.bind('mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $itemsButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    //console.log('%c' + 'Team-items (inventory) button clicked!', 'color: cyan;');
                    // Check if this panel is already active, and if so we collapse everything
                    let alreadyExpanded = $robotsOverview.is('.expanded[data-view="items"]') ? true : false;
                    if (alreadyExpanded){ return disableRobotsOverview(); }
                    // Expand the robot overview to the item-storage view panel
                    showRobotsOverviewPanel('items');
                    // Return true on success
                    return true;
                    });
                // bind click events to the actual item buttons for showing their details in the side-panel
                $storageItemsDiv.delegate('.team-item[data-item]', 'mouseenter', hoverOverviewObject);
                $storageItemsDiv.delegate('.team-item[data-item]', 'mouseleave', unhoverOverviewObject);
                $storageItemsDiv.delegate('.team-item[data-item]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage item clicked!', 'color: cyan;');
                    let $item = $(this);
                    let itemToken = $item.attr('data-item') || false;
                    //console.log('-> itemToken =', itemToken);
                    if (!itemToken || !itemToken.length){ return false; }
                    let alreadySelected = $item.is('.selected') ? true : false;
                    $('.team-item[data-item].selected', $storageItemsDiv).removeClass('selected');
                    $storageItemsDiv.removeClass('has-selection');
                    if (!alreadySelected){ _self.playSoundEffect('icon-click-mini'); }
                    else if (alreadySelected){ _self.playSoundEffect('back-click');  refreshDetailsPanel(); return; }
                    $item.addClass('selected');
                    $storageItemsDiv.addClass('has-selection');
                    refreshDetailsPanel();
                    return true;
                    });
                // add functionality to the action buttons within item details panels
                $robotsOverview.delegate('.storage-details[data-item] .button[data-action]', 'mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $robotsOverview.delegate('.storage-details[data-item] .button[data-action]', 'click', function(e){
                    //console.log('%c' + 'Storage item details button clicked!', 'color: cyan;');
                    e.preventDefault();
                    let $actionButton = $(this);
                    let actionToken = $actionButton.attr('data-action');
                    //console.log('-> actionToken =', actionToken);
                    let $detailsDiv = $robotsOverview.find('.storage-details');
                    if (!$detailsDiv.is('[data-item]')){ return; }
                    let itemToken = $detailsDiv.attr('data-item');
                    //console.log('-> itemToken =', itemToken);
                    let $targetRobot = $teamRobotsInOverview.filter('.team-robot[data-robot].selected').first();
                    let targetRobotToken = $targetRobot && $targetRobot.length ? $targetRobot.attr('data-robot') : false;
                    //console.log('-> targetRobotToken =', targetRobotToken);
                    let autoClickAction = function(){ $actionButton.addClass('clicked'); _self.playSoundEffect('icon-click'); };
                    let actionModalConfig = {onComplete: function(){ $actionButton.removeClass('clicked'); }};
                    if (actionToken === 'use-item'){ autoClickAction(); _self.showUseItemModal(itemToken, targetRobotToken, actionModalConfig); }
                    else if (actionToken === 'give-item'){ autoClickAction(); _self.showGiveItemModal(itemToken, targetRobotToken, actionModalConfig); }
                    else if (actionToken === 'take-item'){ autoClickAction(); _self.showTakeItemModal(itemToken, targetRobotToken, actionModalConfig); }
                    else if (actionToken === 'drop-item'){ autoClickAction(); _self.showDropItemModal(itemToken, actionModalConfig); }
                    else { console.warn('-> undefined item action "', actionToken, '", ignoring input'); return false; }
                    // Return true on success
                    return true;
                    });
                // ...
                }
            // Bind a click event to the team-abilities button in the robots overview
            let $abilitiesButton = $('.team-abilities', $robotsOverview);
            if ($abilitiesButton && $abilitiesButton.length){
                // expand/collapse the ability storage tray by clicking the abilities button
                $abilitiesButton.bind('mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $abilitiesButton.bind('click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    //console.log('%c' + 'Team-abilities (weapons) button clicked!', 'color: cyan;');
                    // Check if this panel is already active, and if so we collapse everything
                    let alreadyExpanded = $robotsOverview.is('.expanded[data-view="abilities"]') ? true : false;
                    if (alreadyExpanded){ return disableRobotsOverview(); }
                    // Expand the robot overview to the ability-storage view panel
                    showRobotsOverviewPanel('abilities');
                    // Return true on success
                    return true;
                    });
                // make sure the abilities are re-filtered whenever the toggle button is clicked
                $storageAbilitiesDiv.delegate('.toggle[data-toggle]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage abilities toggle button clicked!', 'color: cyan;');
                    refreshAbilitiesDiv();
                    return true;
                    });
                // if the storage tray is open, hovering a robot in the storage-list  highlights it for interaction
                $storageAbilitiesDiv.delegate('.team-ability[data-ability]', 'mouseenter', hoverOverviewObject);
                $storageAbilitiesDiv.delegate('.team-ability[data-ability]', 'mouseleave', unhoverOverviewObject);
                // bind click events to the actual ability buttons for showing their details in the side-panel
                $storageAbilitiesDiv.delegate('.team-ability[data-ability]', 'click', function(e){
                    e.preventDefault();
                    if (_self.worldIsBusy()){ return; }
                    if (!$robotsOverview.is('.expanded')){ return; } // if we're not expanded, ignore clicks
                    //console.log('%c' + 'Storage ability clicked!', 'color: cyan;');
                    let $ability = $(this);
                    let abilityToken = $ability.attr('data-ability') || false;
                    //console.log('-> abilityToken =', abilityToken);
                    if (!abilityToken || !abilityToken.length){ return false; }
                    if ($ability.is('.incompatible')){ return false; }
                    let alreadySelected = $ability.is('.selected') ? true : false;
                    //console.log('-> alreadySelected =', alreadySelected);
                    $('.team-ability[data-ability].selected', $storageAbilitiesDiv).removeClass('selected');
                    $storageAbilitiesDiv.removeClass('has-selection');
                    if (!alreadySelected){
                        _self.playSoundEffect('icon-click-mini');
                        }
                    else if (alreadySelected){
                        _self.playSoundEffect('back-click');
                        $('.team-robot[data-robot].incompatible', $teamRobotsDiv).removeClass('incompatible');
                        $('.team-robot[data-robot].equipped', $teamRobotsDiv).removeClass('equipped');
                        refreshDetailsPanel();
                        return;
                        }
                    $ability.addClass('selected');
                    $storageAbilitiesDiv.addClass('has-selection');
                    refreshDetailsPanel();
                    refreshRobotsDiv();
                    return true;
                    });
                // add functionality to the action buttons within ability details panels
                $robotsOverview.delegate('.storage-details[data-ability] .button[data-action]', 'mouseenter', function(){ _self.playSoundEffect('icon-hover'); });
                $robotsOverview.delegate('.storage-details[data-ability] .button[data-action]', 'click', function(e){
                    //console.log('%c' + 'Storage ability details button clicked!', 'color: cyan;');
                    e.preventDefault();
                    let $actionButton = $(this);
                    let actionToken = $actionButton.attr('data-action');
                    //console.log('-> actionToken =', actionToken);
                    let $detailsDiv = $robotsOverview.find('.storage-details');
                    if (!$detailsDiv.is('[data-ability]')){ return; }
                    let abilityToken = $detailsDiv.attr('data-ability');
                    //console.log('-> abilityToken =', abilityToken);
                    let $targetRobot = $teamRobotsInOverview.filter('.team-robot[data-robot].selected').first();
                    let targetRobotToken = $targetRobot && $targetRobot.length ? $targetRobot.attr('data-robot') : false;
                    //console.log('-> targetRobotToken =', targetRobotToken);
                    // Launch a modal for the given action on the selected robot if applicable
                    //console.log('ability action ' + actionToken + ' modal functionality! w/', '\n-> actionToken =', actionToken, '\n-> abilityToken =', abilityToken, '\n-> targetRobotToken =', targetRobotToken);
                    let autoClickAction = function(){ $actionButton.addClass('clicked'); _self.playSoundEffect('icon-click'); };
                    let actionModalConfig = {onComplete: function(){ $actionButton.removeClass('clicked'); }};
                    if (actionToken === 'equip-ability'){ autoClickAction(); _self.showEquipAbilityModal(abilityToken, targetRobotToken, actionModalConfig); }
                    else if (actionToken === 'remove-ability'){ autoClickAction(); _self.showRemoveAbilityModal(abilityToken, targetRobotToken, actionModalConfig); }
                    else { console.warn('-> undefined ability action "', actionToken, '", ignoring input'); return false; }
                    // Return true on success
                    return true;
                    });
                }
            }
        _self.robotsOverviewAPI = robotsOverviewAPI;

        // Define a function to run each time user inputs are updated so we can react
        let listenForInput = function(){ return Date.now() >= nextInputAllowedTime; }, nextInputAllowedTime = 0;
        let ignoreInputFor = function(delay){ delay = typeof delay === 'number' ? delay : 200; nextInputAllowedTime = Date.now() + delay; };
        let userInputVars = {};
        let checkUserInputs = function(kind, event, activeInputs, userInputs){
            //console.log('%c' + 'mmrpgWorldMap.checkUserInputs(kind:' + kind + ', event, activeInputs, userInputs)', 'color: cyan;');
            //event.preventDefault();
            //event.stopPropagation();
            //console.log('-> event:', e);
            if (!listenForInput()){ return false; }
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
            let robotsOverviewIsExpanded = $robotsOverview.is('.expanded') ? true : false;
            let currentRobotsOverviewPanel = robotsOverviewIsExpanded ? $robotsOverview.attr('data-view') : false;
            let calculateRobotsOverviewStorage = robotsOverviewAPI.calculateStorage;
            let refreshRobotsOverviewDetailsPanel = robotsOverviewAPI.refreshDetailsPanel;
            //console.log('-> robotsOverviewIsExpanded =', robotsOverviewIsExpanded, '\n-> currentRobotsOverviewPanel =', currentRobotsOverviewPanel);
            // Define some quick actions that we may need to re-use a few times over
            let confirmSideButtonAction = function(){
                if (!sideButtonsActive){ return; }
                let $confirmButton = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons).first();
                if (!$confirmButton || !$confirmButton.length){ /* console.error('bindEventsToWorld() unable to find confirm button!'); */ return false; }
                if ($confirmButton.is('.clicked')){ return }
                if (!$confirmButton.is('.maybe')){ $confirmButton.addClass('maybe'); return; }
                $confirmButton.removeClass('maybe');
                //console.log('Triggering click on confirm button:', $confirmButton);
                $confirmButton.trigger('click');
                return true;
                };
            let dismissSideButtonAction = function(){
                if (!sideButtonsActive){ return; }
                let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
                if (!$dismissButton || !$dismissButton.length){ console.error('bindEventsToWorld() unable to find dismiss button!'); return false; }
                $sideButtons.removeClass('maybe');
                $dismissButton.trigger('click');
                return true;
                };
            // If the player switcher is currently focused, we should listen for a confirmation button
            if (playerSwitcherFocused){
                // Should not be open while player-switching is being used
                dismissSideButtonAction();
                // If the player has pressed the A button, we can simple click whichever team-player is currently "hovered"
                if (activeInputs.A || activeInputs.Start){
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
                // If the player has pressed the B button instead, we should dismiss the player switcher
                if (activeInputs.B){
                    //console.log('%c' + 'Dismiss player switch!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    $('.team-player', $playerSwitcher).removeClass('hovered');
                    $playerSwitcher.removeClass('focused');
                    ignoreInputFor(600);
                    return true;
                    }
                }
            // If the robots overview is open, make sure we respond to panel-agnostic inputs
            if (robotsOverviewIsExpanded){
                //console.log('-> robotsOverviewIsExpanded =', robotsOverviewIsExpanded);
                // Collect references to key elements within the robots overview
                let $teamRobotsDiv = $('.team-robots', $robotsOverview);
                let $storageBoxDivs = $('.storage-box', $robotsOverview);
                let $storageRobotsDiv = $('.storage-robots', $robotsOverview);
                let $storageItemsDiv = $('.storage-items', $robotsOverview);
                let $storageAbilitiesDiv = $('.storage-abilities', $robotsOverview);
                let $storageDetailsDiv = $('.storage-details', $robotsOverview);
                let $teamRobotsInOverview = $('.team-robot[data-robot]:not(.hidden)', $teamRobotsDiv);
                let $storageRobotsInOverview = $('.team-robot[data-robot]:not(.hidden)', $storageRobotsDiv);
                let $storageItemsInOverview = $('.team-item[data-item]:not(.hidden)', $storageItemsDiv);
                let $storageAbilitiesInOverview = $('.team-ability[data-ability]:not(.hidden)', $storageAbilitiesDiv);
                let $storageSortsInOverview = $('.storage-box:not(.disabled) .button[data-sort]:not(.hidden)', $robotsOverview);
                //console.log('--> $teamRobotsDiv =', $teamRobotsDiv.length); //, $teamRobotsDiv);
                //console.log('--> $storageRobotsDiv =', $storageRobotsDiv.length); //, $storageRobotsDiv);
                //console.log('--> $storageItemsDiv =', $storageItemsDiv.length); //, $storageItemsDiv);
                //console.log('--> $storageAbilitiesDiv =', $storageAbilitiesDiv.length); //, $storageAbilitiesDiv);
                //console.log('--> $storageDetailsDiv =', $storageDetailsDiv.length); //, $storageDetailsDiv);
                //console.log('--> $storageSortsInOverview =', $storageSortsInOverview.length, $storageSortsInOverview);
                let currentStorageView = $robotsOverview.is('[data-view]') ? $robotsOverview.attr('data-view') : '';
                //console.log('--> currentStorageView =', currentStorageView);
                let $storageObjectsDiv, $storageObjectsInOverview;
                if (currentStorageView === 'robots'){ $storageObjectsDiv = $storageRobotsDiv; $storageObjectsInOverview = $storageRobotsInOverview; }
                else if (currentStorageView === 'items'){ $storageObjectsDiv = $storageItemsDiv; $storageObjectsInOverview = $storageItemsInOverview; }
                else if (currentStorageView === 'abilities'){ $storageObjectsDiv = $storageAbilitiesDiv; $storageObjectsInOverview = $storageAbilitiesInOverview; }
                //console.log('--> $storageObjectsDiv(', currentStorageView, ') =', $storageObjectsDiv.length); //, $storageObjectsDiv);
                //console.log('--> $storageObjectsInOverview(', currentStorageView, ') =', $storageObjectsInOverview.length); //, $storageObjectsInOverview);
                let $actionButtonsInDetails = $('.action-button:not(.hidden)', $storageDetailsDiv);
                let $imageDiv = $('.image:not(.hidden)', $storageDetailsDiv).first();
                $actionButtonsInDetails = $actionButtonsInDetails.add($imageDiv);
                //console.log('--> $imageDiv =', $imageDiv.length); //, $imageDiv);
                if ($storageDetailsDiv.length && $storageDetailsDiv.is('[data-robot]')){
                    let $heldItem = $('.infoline.held-item .value:not(.hidden)', $storageDetailsDiv);
                    let $supportMecha = $('.infoline.support-mecha .value:not(.hidden)', $storageDetailsDiv);
                    let $equippedAbilities = $('.infoline.equipped-abilities .value:not(.hidden)', $storageDetailsDiv);
                    $actionButtonsInDetails = $actionButtonsInDetails.add($heldItem);
                    $actionButtonsInDetails = $actionButtonsInDetails.add($supportMecha);
                    $actionButtonsInDetails = $actionButtonsInDetails.add($equippedAbilities);
                    //console.log('--> $heldItem =', $heldItem.length); //, $heldItem);
                    //console.log('--> $supportMecha =', $supportMecha.length); //, $supportMecha);
                    //console.log('--> $equippedAbilities =', $equippedAbilities.length); //, $equippedAbilities);
                    }
                let $subActionButtons = $('.actions .button[data-action]:not(.hidden)', $storageDetailsDiv);
                $actionButtonsInDetails = $actionButtonsInDetails.add($subActionButtons);
                //console.log('--> $subActionButtons =', $subActionButtons.length); //, $subActionButtons);
                //console.log('--> $actionButtonsInDetails(', currentStorageView, ') =', $actionButtonsInDetails.length); //, $storageObjectsDiv);
                // Check to see if the action modal is visible before delegating events in case storage hidden
                let currentFocus = !_world.actionModalVisible ? 'storage-boxes' : 'action-modal';
                //console.log('_world.actionModalVisible = ', _world.actionModalVisible);
                //console.log('currentFocus =', currentFocus);
                if (currentFocus === 'storage-boxes'){
                    //console.log('delegate events relevant to storage boxes only');
                    let $hoveredButton = $('.team-robot.hovered, .team-item.hovered, .team-ability.hovered, .value.hovered, .button.hovered, .image.hovered', $robotsOverview);
                    let $selectedButton = $('.team-robot.selected', $teamRobotsDiv);
                    $selectedButton = $selectedButton.add('.team-robot.selected, .team-item.selected, .team-ability.selected', $storageBoxDivs);
                    //console.log('--> $hoveredButton =', $hoveredButton.length); //, $hoveredButton);
                    //console.log('--> $selectedButton =', $selectedButton.length); //, $selectedButton);
                    let hoverButton = function($button){ if (!$button){ return; } $('.hovered', $robotsOverview).removeClass('hovered'); $button.addClass('hovered'); $button.trigger('mouseenter'); };
                    let selectButton = function($button){ if (!$button){ return; } $('.selected', $robotsOverview).removeClass('selected'); $button.addClass('selected'); };
                    let clickButton = function($button){ if (!$button){ return; } $button.trigger('click'); };
                    let hoverFirstTeamRobot = function(){ hoverButton($teamRobotsInOverview.first()); };
                    let hoverLastTeamRobot = function(){ hoverButton($teamRobotsInOverview.last()); };
                    let hoverFirstStorageObject = function(){ hoverButton($storageObjectsInOverview.not('.hidden').first()); };
                    let hoverLastStorageObject = function(){ hoverButton($storageObjectsInOverview.not('.hidden').last()); };
                    let hoverFirstDetailsButton = function(){ hoverButton($actionButtonsInDetails.not('.hidden').first()); };
                    let hoverLastDetailsButton = function(){ hoverButton($actionButtonsInDetails.not('.hidden').last()); };
                    let closeRobotsOverviewPanel = function(){
                        //console.log('closeRobotsOverviewPanel() ...');
                        if (event){ event.preventDefault(); }
                        let $closeButton = $('.team-close', $robotsOverview);
                        if ($closeButton.length
                            && $closeButton.is(':visible')
                            && !$closeButton.is('.disabled')){
                            $closeButton.addClass('clicked');
                            $closeButton.trigger('click');
                            setTimeout(function(){ $closeButton.removeClass('clicked'); }, 600);
                            ignoreInputFor(900);
                            return true;
                            } else {
                            return false;
                            }
                        };
                    // If the player has pressed the start button again, attempt to close the storage area via the same button
                    if (activeInputs.Start){
                        // removed: || activeInputs.B  reason: we need B for other actions in-menu sorry
                        //console.log('%c' + 'Start key pressed!', 'color: orange;');
                        return closeRobotsOverviewPanel();
                        }
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
                            ignoreInputFor(600);
                            return true;
                            }
                        }
                    // If the player has pressed the A button, try to click whatever button is currently hovered
                    if (activeInputs.A){
                        //console.log('%c' + 'A key pressed!', 'color: orange;');
                        if ($hoveredButton
                            && $hoveredButton.length
                            && !$hoveredButton.is('.hidden')){
                            return clickButton($hoveredButton);
                            }
                        }
                    // If the player has pressed the B button, try to unclick whatever button is current selected
                    if (activeInputs.B){
                        //console.log('%c' + 'B key pressed!', 'color: orange;');
                        if ($selectedButton
                            && $selectedButton.length
                            && !$selectedButton.is('.disabled')){
                            let $lastSelected = $selectedButton.last();
                            clickButton($lastSelected);
                            return hoverButton($lastSelected);
                            } else {
                            return closeRobotsOverviewPanel();
                            }
                        }
                    // If the player has pressed the X button, and there's an element with a data-button value of X, try to click it
                    if (activeInputs.X){
                        //console.log('%c' + 'X key pressed!', 'color: orange;');
                        let $dataButtonX = $('.button[data-action][data-button="X"]:not(.disabled)', $robotsOverview);
                        //console.log('$dataButtonX =', $dataButtonX.length, $dataButtonX); /// CHECKPOINT 2026/03/06
                        if (!$dataButtonX || !$dataButtonX.length){ $dataButtonX = false; }
                        else { $dataButtonX = $dataButtonX.first(); }
                        if ($dataButtonX && $dataButtonX.is(':visible')){
                            $dataButtonX.addClass('clicked').trigger('click');
                            setTimeout(function(){ $dataButtonX.removeClass('clicked'); }, 600);
                            ignoreInputFor(300);
                            return true;
                            }
                        }
                    // If the player has pressed the Y button, and there's an element with a data-button value of Y, try to click it
                    if (activeInputs.Y){
                        //console.log('%c' + 'Y key pressed!', 'color: orange;');
                        let $dataButtonY = $('.button[data-action][data-button="Y"]:not(.disabled)', $robotsOverview);
                        if (!$dataButtonY || !$dataButtonY.length){ $dataButtonY = false; }
                        else { $dataButtonY = $dataButtonY.first(); }
                        if ($dataButtonY && $dataButtonY.is(':visible')){
                            $dataButtonY.addClass('clicked').trigger('click');
                            setTimeout(function(){ $dataButtonY.removeClass('clicked'); }, 600);
                            ignoreInputFor(300);
                            return true;
                            }
                        }
                    // If the player has pressed the L1 or R1 buttons, we should scroll between the storage pages (if available)
                    if (activeInputs.L1 || activeInputs.R1){
                        //console.log('%c' + 'L1 or R1 key pressed!', 'color: orange;');
                        if (event){ event.preventDefault(); }
                        let storageConfig = calculateRobotsOverviewStorage(currentRobotsOverviewPanel);
                        //console.log('-> storageConfig = ', storageConfig);
                        let $storageObjectsDiv = storageConfig.storageDiv;
                        let $backButton = $('.button[data-page="back"]:not(.disabled)', $storageObjectsDiv);
                        let $nextButton = $('.button[data-page="next"]:not(.disabled)', $storageObjectsDiv);
                        let clickButton = function($button){
                            $button.trigger('click');
                            //$button.addClass('clicked').trigger('click');
                            //setTimeout(function(){ $button.removeClass('clicked'); }, 600);
                            };
                        if (activeInputs.L1){
                            let $backOrLastButton;
                            if ($backButton.length){ $backOrLastButton = $backButton; }
                            else { $backOrLastButton = $('.button[data-page]:not(.disabled):not(.back):not(.next)', $storageObjectsDiv).last(); }
                            if ($backOrLastButton.length){ clickButton($backOrLastButton); }
                            ignoreInputFor(300);
                            return true;
                            }
                        else if (activeInputs.R1){
                            let $nextOrFirstButton;
                            if ($nextButton.length){ $nextOrFirstButton = $nextButton; }
                            else { $nextOrFirstButton = $('.button[data-page]:not(.disabled):not(.back):not(.next)', $storageObjectsDiv).first(); }
                            if ($nextOrFirstButton.length){ clickButton($nextOrFirstButton); }
                            ignoreInputFor(300);
                            return true;
                            }
                        return;
                        }
                    // If the player has pressed the L+R button, we should try to click any visible sort buttons in sequence
                    if (activeInputs.LR1){
                        //console.log('%c' + 'L1+R1 key pressed!', 'color: orange;');
                        if (event){ event.preventDefault(); }
                        let $sortContainer = $('.sorts[data-dir]', $storageObjectsDiv);
                        let $sortButtons = $('.button[data-sort]', $sortContainer);
                        //console.log('$sortContainer =', $sortContainer.length, $sortContainer);
                        //console.log('$sortButtons =', $sortButtons.length, $sortButtons);
                        if (($sortButtons && $sortButtons.length)
                            && ($sortContainer && $sortContainer.length)){
                            let $activeSort = $sortButtons.filter('.active');
                            let activeDir = $sortContainer.attr('data-dir');
                            //console.log('$activeSort =', $activeSort.length, $activeSort);
                            //console.log('activeDir =', activeDir);
                            let clickAgain = activeDir === 'down' ? true : false;
                            let clickNext = !clickAgain && $sortButtons.length > 1 ? true : false;
                            if (clickAgain){
                                //console.log('click the same button again!');
                                clickButton($activeSort);
                                return hoverButton($activeSort);
                                }
                            else if (clickNext){
                                //console.log('click the next button in sequence w/ rollover!');
                                let currIndex = $sortButtons.index($activeSort);
                                let nextIndex = (currIndex < $sortButtons.length - 1) ? (currIndex + 1) : 0;
                                let $nextButton = $sortButtons.eq(nextIndex);
                                clickButton($nextButton);
                                return hoverButton($nextButton);
                                }
                            }
                        }
                    // If the player has pressed the L2 or R2 buttons, we should switch to other available buttons (robots/abilities/items)
                    if (activeInputs.L2 || activeInputs.R2){
                        //console.log('%c' + 'L2 or R2 key pressed!', 'color: orange;');
                        if (event){ event.preventDefault(); }
                        let $switchButton = $('.team-switch', $robotsOverview);
                        let $abilitiesButton = $('.team-abilities', $robotsOverview);
                        let $itemsButton = $('.team-items', $robotsOverview);
                        //console.log('-> $switchButton =', $switchButton);
                        //console.log('-> $abilitiesButton =', $abilitiesButton);
                        //console.log('-> $itemsButton =', $itemsButton);
                        let $availableButtons = $();
                        if ($switchButton.length){ $availableButtons = $availableButtons.add($switchButton); }
                        if ($abilitiesButton.length){ $availableButtons = $availableButtons.add($abilitiesButton); }
                        if ($itemsButton.length){ $availableButtons = $availableButtons.add($itemsButton); }
                        //console.log('->$availableButtons =', $availableButtons);
                        if (!$availableButtons.length || $availableButtons.length < 2){ return false; }
                        let $currentButton = $availableButtons.filter('.active').first();
                        //console.log('-> $currentButton =', $currentButton);
                        if (!$currentButton || !$currentButton.length){ return false; }
                        let currentIndex = $availableButtons.index($currentButton);
                        //console.log('-> currentIndex =', currentIndex);
                        let newIndex = currentIndex;
                        if (activeInputs.L2){ newIndex--; }
                        else if (activeInputs.R2){ newIndex++; }
                        if (newIndex < 0){ newIndex = $availableButtons.length - 1; }
                        if (newIndex >= $availableButtons.length){ newIndex = 0; }
                        //console.log('-> newIndex =', newIndex);
                        let $newButton = $availableButtons.eq(newIndex);
                        //console.log('-> $newButton =', $newButton);
                        if ($newButton && $newButton.length && !$newButton.is($currentButton)){
                            //console.log('-> switching to new button:', $newButton);
                            $newButton.addClass('clicked');
                            $newButton.trigger('click');
                            setTimeout(function(){ $newButton.removeClass('clicked'); }, 600);
                            ignoreInputFor(900);
                            return true;
                            }
                        }
                    // If the player has pressed any of the directional inputs, we should navigate within the open panels
                    if (activeInputs.Up || activeInputs.Down || activeInputs.Left || activeInputs.Right){
                        //console.log('%c' + 'Up/Down/Left/Right key pressed!', 'color: orange;');
                        let $referenceButton = ($hoveredButton && $hoveredButton.length ? $hoveredButton : ($selectedButton && $selectedButton.length ? $selectedButton : false));
                        if (!$referenceButton || !$referenceButton.length){
                            //console.log('---> No hovered nor selected overview buttons found! Auto-hovering given input ...');
                            if (activeInputs.Down){ return hoverFirstTeamRobot(); }
                            else if (activeInputs.Up){ return hoverLastTeamRobot(); }
                            else if (activeInputs.Right){ return hoverFirstStorageObject(); }
                            else if (activeInputs.Left){ return hoverLastStorageObject(); }
                            }
                        else {
                            //console.log('---> Found ' + ($referenceButton.is($hoveredButton) ? 'hovered' : 'selected') + ' overview button ref! Moving hover given input ...');
                            let $currentGroup = [], currentIndex = -1;
                            let $buttonGroups = [$teamRobotsInOverview, $storageRobotsInOverview, $storageItemsInOverview, $storageAbilitiesInOverview, $storageSortsInOverview, $actionButtonsInDetails];
                            $buttonGroups.forEach(function($group){ let index = $group.index($referenceButton); if (index === -1){ return; } $currentGroup = $group; currentIndex = index; });
                            let panelOrder = ['team', 'storage'];
                            let startKey = -1, startPanel = '', nextKey = -1, nextPanel = '';
                            if ($actionButtonsInDetails.length){ panelOrder.push('details'); }
                            if ($referenceButton.is($teamRobotsInOverview)){ startPanel = 'team'; }
                            else if ($referenceButton.is($storageObjectsInOverview)){ startPanel = 'storage'; }
                            else if ($referenceButton.is($storageSortsInOverview)){ startPanel = 'storage'; }
                            else if ($referenceButton.is($actionButtonsInDetails)){ startPanel = 'details'; }
                            let isRobotButton = $referenceButton.is('[data-robot-id]');
                            let isAbilityButton = $referenceButton.is('[data-ability-id]');
                            let isItemButton = $referenceButton.is('[data-item-id]');
                            let prevIndex, realPrevIndex, nextIndex, realNextIndex, incIndexBy = 1;
                            if (startPanel === 'storage' && (activeInputs.Up || activeInputs.Down) && !isRobotButton){ incIndexBy = 2; }
                            else if (startPanel === 'details' && (activeInputs.Up || activeInputs.Down) && isAbilityButton){ incIndexBy = 4; }
                            prevIndex = currentIndex - incIndexBy, realPrevIndex = prevIndex; if (prevIndex < 0){ prevIndex = $currentGroup.length - 1; realPrevIndex = false; }
                            nextIndex = currentIndex + incIndexBy, realNextIndex = nextIndex; if (nextIndex > $currentGroup.length - 1){ nextIndex = 0; realNextIndex = false; }
                            //console.log('$referenceButton =', $referenceButton.length, $referenceButton);
                            //console.log('isRobotButton:', isRobotButton, 'isAbilityButton:', isAbilityButton, 'isItemButton:', isItemButton);
                            //console.log('$currentGroup =', $currentGroup.length, $currentGroup);
                            //console.log('currentIndex =', currentIndex, 'prevIndex =', prevIndex, 'nextIndex =', nextIndex);
                            if (activeInputs.Up || activeInputs.Down){
                                //console.log('----> Vertical direction clicked (' + (activeInputs.Up ? 'UP' : 'DOWN') + ')!');
                                if (!$currentGroup || !$currentGroup.length){ return hoverButton($teamRobotsInOverview.first()); }
                                else if ($currentGroup === $storageSortsInOverview){ return hoverButton($storageObjectsInOverview.first()); }
                                else if (activeInputs.Up){ return hoverButton($currentGroup.eq(prevIndex)); }
                                else if (activeInputs.Down){ return hoverButton($currentGroup.eq(nextIndex)); }
                                }
                            else if (activeInputs.Left || activeInputs.Right){
                                //console.log('--> Horizontal direction clicked (' + (activeInputs.Left ? 'LEFT' : 'RIGHT') + ')!');
                                if (startPanel){
                                    startKey = panelOrder.indexOf(startPanel), nextKey = 0;
                                    if (activeInputs.Left){ nextKey = startKey - 1; nextPanel = panelOrder[nextKey] || panelOrder[panelOrder.length - 1]; }
                                    else if (activeInputs.Right){ nextKey = startKey + 1; nextPanel = panelOrder[nextKey] || panelOrder[0]; }
                                    }
                                //console.log('---> panelOrder =', panelOrder);
                                //console.log('---> startPanel =', startPanel);
                                //console.log('---> startKey =', startKey);
                                if (startPanel === 'storage' && !isRobotButton){
                                    //console.log('... in storage, might navigate to within ...');
                                    //console.log('----> realPrevIndex =', realPrevIndex, 'realNextIndex =', realNextIndex);
                                    let dataSlot = parseInt($referenceButton.attr('data-slot'));
                                    let isEven = dataSlot % 2 === 0, isOdd = !isEven;
                                    //console.log('----> dataSlot =', dataSlot);
                                    if (isEven && activeInputs.Left && realPrevIndex !== false){ return hoverButton($currentGroup.eq(realPrevIndex)); }
                                    else if (isOdd && activeInputs.Right && realNextIndex !== false){ return hoverButton($currentGroup.eq(realNextIndex)); }
                                    }
                                if (startPanel === 'details'){
                                    //console.log('... in details, might navigate to within ...');
                                    //console.log('realPrevIndex =', realPrevIndex, 'realNextIndex =', realNextIndex);
                                    if (activeInputs.Left && realPrevIndex !== false){ return hoverButton($currentGroup.eq(realPrevIndex)); }
                                    else if (activeInputs.Right && realNextIndex !== false){ return hoverButton($currentGroup.eq(realNextIndex)); }
                                    }
                                //console.log('----> inner navigation not triggered, moving to nextPanel =', nextPanel);
                                if (nextPanel === 'team'){
                                    let $selected = $teamRobotsInOverview.filter('.selected');
                                    if ($selected.length){ return hoverButton($selected); }
                                    else { return hoverFirstTeamRobot(); }
                                    }
                                else if (nextPanel === 'storage'){
                                    let $selected = $storageObjectsInOverview.filter('.selected');
                                    if ($selected.length){ return hoverButton($selected); }
                                    else { return hoverFirstStorageObject(); }
                                    }
                                else if (nextPanel === 'details'){
                                    let $hovered = $actionButtonsInDetails.filter('.hovered');
                                    if (!$hovered.length){ return hoverFirstDetailsButton(); }
                                    }
                                }
                            }
                        }
                    }
                else if (currentFocus === 'action-modal'){
                    //console.log('delegate events relevant to action modal only');
                    let $actionModal = _elements.actionModal;
                    let currentAction = $actionModal.is('[data-action]') ? $actionModal.attr('data-action') : false;
                    let currentActionKind = currentAction && currentAction.indexOf('_') !== -1 ? currentAction.split('_')[0] : currentAction;
                    let currentActionToken = $actionModal.is('[data-action-token]') ? $actionModal.attr('data-action-token') : false;
                    let $actionModalContent = $('.content', $actionModal);
                    let $containerForObjects = $('.container.for-current', $actionModalContent);
                    let $containerForButtons = $('.buttons.actions', $actionModalContent);
                    let actionObjectClass = '.team-' + currentActionKind + '[data-' + currentActionKind + ']';
                    let actionButtonClass = '.button[data-action]';
                    let actionObjectsPerRow = 4;
                    let $actionObjectsInModal = $(actionObjectClass, $containerForObjects);
                    let $actionButtonsInModal = $(actionButtonClass, $containerForButtons);
                    //console.log('--> $actionModal =', $actionModal.length); //, $actionModal);
                    //console.log('--> currentAction =', currentAction);
                    //console.log('--> currentActionKind =', currentActionKind);
                    //console.log('--> currentActionToken =', currentActionToken);
                    //console.log('--> actionObjectClass =', actionObjectClass);
                    //console.log('--> $actionModalContent =', $actionModalContent.length); //, $actionModalContent);
                    //console.log('--> $containerForObjects =', $containerForObjects.length); //, $containerForObjects);
                    //console.log('--> $containerForButtons =', $containerForButtons.length); //, $containerForButtons);
                    //console.log('--> $actionButtonsInModal =', $actionButtonsInModal.length); //, $actionButtonsInModal);
                    //console.log('--> $actionObjectsInModal =', $actionObjectsInModal.length); //, $actionObjectsInModal);
                    let hoveredButtonClasses = [actionObjectClass, actionButtonClass].map((cls) => (cls + '.hovered')).join(', ');
                    let selectedButtonClasses = [actionObjectClass, actionButtonClass].map((cls) => (cls + '.selected')).join(', ');
                    let $hoveredButton = $(hoveredButtonClasses, $actionModalContent);
                    let $selectedButton = $(selectedButtonClasses, $actionModalContent);
                    //let $hoveredButton = $([actionObjectClass + '.hovered', actionButtonClass + '.hovered'].join(', '), $actionModalContent);
                    //let $selectedButton = $([actionObjectClass + '.selected', actionButtonClass + '.selected'].join(', '), $actionModalContent);
                    //console.log('--> hoveredButtonClasses =', hoveredButtonClasses);
                    //console.log('--> selectedButtonClasses =', selectedButtonClasses);
                    //console.log('--> $hoveredButton =', $hoveredButton.length); //, $hoveredButton);
                    //console.log('--> $selectedButton =', $selectedButton.length); //, $selectedButton);
                    let hoverButton = function($button, $context){ if (!$button){ return; } $(hoveredButtonClasses, ($context && $context.length ? $context : $actionModalContent)).removeClass('hovered'); $button.addClass('hovered'); $button.trigger('mouseenter'); };
                    let selectButton = function($button, $context){ if (!$button){ return; } $(selectedButtonClasses, ($context && $context.length ? $context : $actionModalContent)).removeClass('selected'); $button.addClass('selected'); };
                    let clickButton = function($button){ if (!$button){ return; } $button.trigger('click'); };
                    let hoverFirstActionObject = function(){ hoverButton($actionObjectsInModal.first()); };
                    let hoverLastActionObject = function(){ hoverButton($actionObjectsInModal.last()); };
                    let hoverSelectedActionObject = function(){ let $selected = $actionModalContent.find(actionObjectClass + '.selected'); if (!$selected || !$selected.length){ return false; } hoverButton($selected); return true; };
                    let hoverSelectedActionButton = function(){ let $selected = $actionModalContent.find(actionButtonClass + '.selected'); if (!$selected || !$selected.length){ return false; } hoverButton($selected); return true; };
                    let $confirmButton = $actionButtonsInModal.length ? $actionButtonsInModal.filter('[data-action="confirm"]') : false;
                    let $cancelButton = $actionButtonsInModal.length ? $actionButtonsInModal.filter('[data-action="cancel"]') : false;
                    let hoverConfirmButton = function(){ if (!$confirmButton){ return; } hoverButton($confirmButton); };
                    let hoverCancelButton = function(){ if (!$cancelButton){ return; } hoverButton($cancelButton); };
                    let dismissActionModal = function(){ let $overlay = $('> .overlay', $actionModal); if ($overlay.length){ $overlay.trigger('click'); } ignoreInputFor(200); };
                    // If the player has pressed the start button again, attempt to close the modal via the close button
                    if (activeInputs.Start){
                        //console.log('%c' + 'Start key pressed!', 'color: orange;');
                        return dismissActionModal();
                        }
                    // If the player has pressed the A button, try to click whatever button is currently hovered
                    if (activeInputs.A){
                        //console.log('%c' + 'A key pressed!', 'color: orange;');
                        if ($hoveredButton
                            && $hoveredButton.length
                            && !$hoveredButton.is('.disabled')){
                            return clickButton($hoveredButton);
                            }
                        }
                    // If the player has pressed the B button, try to unclick whatever button is current selected
                    if (activeInputs.B){
                        //console.log('%c' + 'B key pressed!', 'color: orange;');
                        if ($selectedButton
                            && $selectedButton.length
                            && !$selectedButton.is('.disabled')){
                            return clickButton($selectedButton.last());
                            } else {
                            return dismissActionModal();
                            }
                        }
                    // If the player has pressed any of the directional inputs, we should navigate within the open modal
                    if (activeInputs.Up || activeInputs.Down || activeInputs.Left || activeInputs.Right){
                        //console.log('%c' + [(activeInputs.Up ? 'Up' : ''), (activeInputs.Down ? 'Down' : ''), (activeInputs.Left ? 'Left' : ''), (activeInputs.Right ? 'Right' : '')].filter((s) => !!s).join(', ') + ' key(s) pressed!', 'color: orange;');
                        let $referenceButton = ($hoveredButton && $hoveredButton.length ? $hoveredButton : ($selectedButton && $selectedButton.length ? $selectedButton : false));
                        if (!$referenceButton || !$referenceButton.length){
                            //console.log('---> No hovered nor selected modal buttons found! Auto-hovering given input ...');
                            if (activeInputs.Down || activeInputs.Right){ hoverFirstActionObject(); }
                            else if (activeInputs.Up || activeInputs.Left){ hoverLastActionObject(); }
                            }
                        else {
                            //console.log('---> Found ' + ($referenceButton.is($hoveredButton) ? 'hovered' : 'selected') + ' modal button ref! Moving hover given input ...');
                            let $currentGroup = [], currentIndex = -1;
                            let $buttonGroups = [$actionObjectsInModal, $actionButtonsInModal];
                            $buttonGroups.forEach(function($group){ let index = $group.index($referenceButton); if (index === -1){ return; } $currentGroup = $group; currentIndex = index; });
                            let prevIndex = currentIndex - 1; if (prevIndex < 0){ prevIndex = $currentGroup.length - 1; }
                            let nextIndex = currentIndex + 1; if (nextIndex > $currentGroup.length - 1){ nextIndex = 0; }
                            let prevRowIndex = currentIndex - actionObjectsPerRow; if (prevIndex < 0){ prevRowIndex = false; }
                            let nextRowIndex = currentIndex + actionObjectsPerRow; if (nextRowIndex > $currentGroup.length - 1){ nextRowIndex = false; }
                            //console.log('$referenceButton =', $referenceButton.length, $referenceButton);
                            //console.log('$currentGroup =', $currentGroup.length, $currentGroup);
                            //console.log('currentIndex =', currentIndex, 'prevIndex =', prevIndex, 'nextIndex =', nextIndex);
                            if (activeInputs.Left || activeInputs.Right){
                                //console.log('----> Horizontal direction clicked (' + (activeInputs.Left ? 'LEFT' : 'RIGHT') + ')!');
                                if (activeInputs.Left){ hoverButton($currentGroup.eq(prevIndex)); }
                                else if (activeInputs.Right){ hoverButton($currentGroup.eq(nextIndex)); }
                                }
                            else if (activeInputs.Up || activeInputs.Down){
                                //console.log('----> Vertical direction clicked (' + (activeInputs.Up ? 'UP' : 'DOWN') + ')!');
                                if ($referenceButton.is($actionObjectsInModal)){
                                    //console.log('----> is action object ...');
                                    if (activeInputs.Up){
                                        if (prevRowIndex !== false){ hoverButton($currentGroup.eq(prevRowIndex)); }
                                        }
                                    else if (activeInputs.Down){
                                        if (nextRowIndex !== false){ hoverButton($currentGroup.eq(nextRowIndex)); }
                                        else if ($confirmButton && $confirmButton.is(':not(.disabled)')){ hoverConfirmButton(); }
                                        else if ($cancelButton && $cancelButton.is(':not(.disabled)')){ hoverCancelButton(); }
                                        }
                                    }
                                else if ($referenceButton.is($actionButtonsInModal)){
                                    //console.log('----> is action button ...');
                                    if (!hoverSelectedActionObject()){
                                        if (activeInputs.Up){ hoverLastActionObject(); }
                                        else if (activeInputs.Down){ hoverFirstActionObject(); }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            // If the side buttons panel is currently open, process those actions too
            if (sideButtonsActive){
                // If the player has pressed the space or enter keys, let's confirm the side-button action if it's open
                if (activeInputs.A || activeInputs.Start){
                    //console.log('%c' + 'Confirm action popup!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!$sideButtons.is('.active')){ return false; }
                    confirmSideButtonAction();
                    return true;
                    }
                // Else if the player has pressed the backspace or escape keys, let's close the side-button action if it's open
                else if (activeInputs.B){
                    //console.log('%c' + 'Dismiss action popup!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!$sideButtons.is('.active')){ return false; }
                    dismissSideButtonAction();
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
                        ignoreInputFor(600);
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
                        ignoreInputFor(300);
                        return true;
                        }
                    }
                // If the player has pressed the A button without any menus open, perhaps they're trying to re-init nearby events
                if (activeInputs.A){
                    //console.log('%c' + 'A key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    // Only allow this if the map is not currently animating and the player is not currently moving
                    if (_world.mapIsAnimating || _world.playerIsMoving){ return false; }
                    // Turn ON the auto-options in case there are pickups about to happen
                    _world.autoApplyConsumables = true;
                    _world.autoEquipHoldables = true;
                    // If there are any nearby events, re-init them now
                    //console.log('-> checking for nearby events to re-init...');
                    _self.refreshMapPositionEvents(0, true);
                    ignoreInputFor(100);
                    } else {
                    // Turn OFF the auto-options since A isn't being held anymore
                    _world.autoApplyConsumables = false;
                    _world.autoEquipHoldables = false;
                    }
                // If the player has pressed either of the triggers we should let them scroll within the player-switcher
                if (activeInputs.L2 || activeInputs.R2 || activeInputs.LR2){
                    //console.log('%c' + 'Bumper key pressed!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    let $playerButtons = $('.team-player', $playerSwitcher);
                    if ($playerButtons.length < 2){ return true; } // nothing to switch to, ignore
                    let $cursorPlayer = $playerButtons.filter('[data-player="player"]').first();
                    let $activePlayer = $playerButtons.filter('.active').first();
                    let $hoveredPlayer = $playerButtons.filter('.hovered').first();
                    // We must first focus the player switcher if not already focused
                    if (!$playerSwitcher.is('.focused') || !$hoveredPlayer.length){
                        //console.log('%c' + 'Focusing player switcher!', 'color: orange;');
                        $playerSwitcher.addClass('focused');
                        $playerButtons.removeClass('hovered');
                        if ((activeInputs.L2 && activeInputs.R2) || activeInputs.LR2){
                            let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
                            let playerHistory = _config.playerHistory || [];
                            if (!playerIsCursor){
                                $cursorPlayer.addClass('hovered');
                                }
                            else if (playerHistory.length > 1){
                                let lastPlayerToken = (function(a, t){ for (let i = 0; i < a.length; i++){ if (a[i] !== t){ return a[i]; } } })(playerHistory, _worldPlayer.token);
                                let $lastPlayerButton = lastPlayerToken.length ? $playerButtons.filter('[data-player="' + lastPlayerToken + '"]').first() : false;
                                if ($lastPlayerButton.length){ $lastPlayerButton.addClass('hovered'); }
                                }
                            }
                        else {
                            $activePlayer.addClass('hovered');
                            }
                        ignoreInputFor(300);
                        return true;
                        }
                    // Otherwise if already-focused, the bumpers scroll and/or switch between players
                    else {
                        // If the player has pressed both bumpers at the same-time, quick-switch to/from cursor
                        if ((activeInputs.L2 && activeInputs.R2) || activeInputs.LR2){
                            //console.log('%c' + 'Both bumpers held, quick-switch to...', 'color: orange;');
                            let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
                            let playerHistory = _config.playerHistory || [];
                            if (!playerIsCursor){
                                //console.log('%c' + '...cursor player!', 'color: orange;');
                                $cursorPlayer.trigger('click');
                                $playerSwitcher.removeClass('focused');
                                $('.team-player', $playerSwitcher).removeClass('hovered');
                                ignoreInputFor(1200);
                                return true;
                                }
                            else if (playerHistory.length > 1){
                                //console.log('%c' + '...last player!', 'color: orange;');
                                //console.log('-> playerHistory =', playerHistory);
                                // get the first token in the list (which is chronologically the most recent player token) AFTER this one
                                let lastPlayerToken = (function(a, t){ for (let i = 0; i < a.length; i++){ if (a[i] !== t){ return a[i]; } } })(playerHistory, _worldPlayer.token);
                                let $lastPlayerButton = lastPlayerToken.length ? $playerButtons.filter('[data-player="' + lastPlayerToken + '"]').first() : false;
                                //console.log('-> lastPlayerToken =', lastPlayerToken);
                                //console.log('-> $lastPlayerButton =', $lastPlayerButton);
                                if ($lastPlayerButton.length){ $lastPlayerButton.trigger('click'); }
                                ignoreInputFor(1200);
                                return true;
                                }
                            return;
                            }
                        // Otherwise if just one is being pressed, we scroll in that direction to the next player
                        else {
                            //console.log('%c' + (activeInputs.L2 ? 'L2' : 'R2') + ' held, switching to hovered player!', 'color: orange;');
                            $playerButtons.removeClass('hovered');
                            if (activeInputs.L2){
                                let $prevPlayer = $hoveredPlayer.prevAll('.team-player').first();
                                if (!$prevPlayer || !$prevPlayer.length){ $prevPlayer = $playerButtons.last(); }
                                if ($prevPlayer.length){ $prevPlayer.addClass('hovered'); }
                                }
                            else if (activeInputs.R2){
                                let $nextPlayer = $hoveredPlayer.nextAll('.team-player').first();
                                if (!$nextPlayer || !$nextPlayer.length){ $nextPlayer = $playerButtons.first(); }
                                if ($nextPlayer.length){ $nextPlayer.addClass('hovered'); }
                                }
                            ignoreInputFor(300);
                            return true;
                            }
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
                if (activeInputs.L1 || activeInputs.R1 || activeInputs.LR1){
                    //console.log('%c' + 'Trigger key pressed!', 'color: orange;');
                    //console.log('-> activeInputs: ', Object.keys(activeInputs).length ? activeInputs : 'none');
                    if (event){ event.preventDefault(); }
                    if ( (activeInputs.L1 && activeInputs.R1) || activeInputs.LR1 ){
                        //console.log('%c' + 'Both triggers held, reset zoom!', 'color: orange;');
                        // when both are held, we reset the zoom
                        let oldZoom = _world.zoomLevel || 1;
                        let newZoom = _config.defaultZoomLevel || 1;
                        _self.updateZoomLevel(newZoom, true);
                        if (newZoom !== oldZoom){
                            _self.playSoundEffect('spawn-sound');
                            ignoreInputFor(1000);
                            }
                        return true;
                        } else {
                        //console.log('%c' + (activeInputs.L1 ? 'L1' : 'R2') + ' held, zooming ' + (activeInputs.L1 ? 'out' : 'in') + '!', 'color: orange;');
                        // otherwise we use L1 to zoom out and R1 to zoom in
                        let zoomDir = false;
                        if (activeInputs.L1){ zoomDir = 'out'; }
                        if (activeInputs.R1){ zoomDir = 'in'; }
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
                    //console.log('_worldCursor.position = ' + _worldCursor.position);
                    //console.log('_worldCursor.direction = ' + _worldCursor.direction);
                    if (event){ event.preventDefault(); }
                    let oldPos = _worldCursor.position, curPos = oldPos;
                    let thisPos = oldPos.split('-');
                    let thisCol = parseInt(thisPos[0]);
                    let thisRow = parseInt(thisPos[1]);
                    let newCol = thisCol, newRow = thisRow;
                    let newDir = (function(a){
                        let d = [];
                        if (a.Left){ d.push('left'); } else if (a.Right){ d.push('right'); }
                        if (a.Up){ d.push('up'); } else if (a.Down){ d.push('down'); }
                        return d.join('-');
                        })(activeInputs);
                    let inPlaceMovement = activeInputs.B ? true : false;
                    //console.log('%c' + 'Current position: ' + oldPos, 'color: orange;');
                    if (!inPlaceMovement){
                        if (activeInputs.Left){ newCol--; }
                        else if (activeInputs.Right){ newCol++; }
                        if (activeInputs.Up){ newRow--; }
                        else if (activeInputs.Down){ newRow++; }
                        }
                    let newPos = newCol + '-' + newRow;
                    // Always set this just in case the player gets stuck somewhere
                    _worldCursor.moved = true; // represents them at least trying to move
                    let thisHorDir = (newCol > thisCol) ? 'right' : (newCol < thisCol) ? 'left' : false;
                    let thisVerDir = (newRow > thisRow) ? 'down' : (newRow < thisRow) ? 'up' : false;
                    if (!inPlaceMovement){
                        let thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join('-'); })(thisHorDir, thisVerDir);
                        _worldCursor.direction = thisShiftDir; // the direction they are trying to move
                        _worldPlayer.direction = _worldCursor.direction;
                        //console.log('-> _worldCursor.direction to thisShiftDir(', thisShiftDir, ')');
                        } else {
                        _worldCursor.direction = newDir;
                        _worldPlayer.direction = _worldCursor.direction;
                        //console.log('-> _worldCursor.direction to newDir(', newDir, ')');
                        }
                    //console.log('%c' + 'New position: ' + newPos, 'color: orange;');
                    // Check if the new position is the same as the old position
                    if (!inPlaceMovement
                        && newCol === thisCol
                        && newRow === thisRow){
                        //console.log('%c' + 'New position is the same as the old position!', 'color: orange;');
                        return false;
                        }
                    // Otherwise, let's pull the list of walkable tiles and see if this new position is valid
                    //console.log('%c' + 'Checking if new position is walkable...', 'color: orange;');
                    let playerMobility = _config.playerMobility;
                    let walkableTiles = _self.getWalkableMapTiles();
                    let tilesWithinRange = playerMobility > 0 ? _self.getWalkableMapTilesByProximity(oldPos, playerMobility) : walkableTiles;
                    if (!inPlaceMovement
                        && walkableTiles.indexOf(newPos) === -1
                        && tilesWithinRange.indexOf(newPos) === -1){
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
                    //console.log('%c' + 'New position is walkable, moving there now!', 'color: orange;');
                    let forceMove = inPlaceMovement ? true : false;
                    _self.makeLayerTileActive(newPos);
                    _self.playSoundEffect('land_mmv-gb');
                    _self.moveToPosition(newPos, function(){
                        _self.makeLayerTileInactive(oldPos);
                        if (thisHorDir && thisVerDir){ ignoreInputFor(); }
                        }, forceMove);
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
                            console.warn('X functionality not implemented yet!');
                            }, (timeThreshold * 2));
                        }
                    }
                /*
                // If the user is holding the B button, we need to implement some nuanced functionality
                // -> if it's a simple press, ignore it so that it can do its job in other contexts
                // -> else if it's a long-press (user is holding button) then we need to temporarily disable the auto-use functionality for item pickups
                // The important thing here is to disable the auto-use functionality as long as the user is holding the button down, then re-enable it when they let go (via timeout)
                if (typeof userInputVars.bTimeout === 'undefined'){ userInputVars.bTimeout = false; }
                if (typeof userInputVars.bWasPressedAt === 'undefined'){ userInputVars.bWasPressedAt = null; }
                if (typeof userInputVars.bTimeSincePress === 'undefined'){ userInputVars.bTimeSincePress = null; }
                if (typeof userInputVars.bCallbacksTriggered === 'undefined'){ userInputVars.bCallbacksTriggered = []; }
                if (activeInputs.B){
                    let holdTimeThreshold = 900, unpressTimeout = 300, allowRepeatCallbacks = false;
                    if (event){ event.preventDefault(); }
                    if (!userInputVars.bWasPressedAt){ userInputVars.bWasPressedAt = Date.now(); }
                    userInputVars.bTimeSincePress = Date.now() - userInputVars.bWasPressedAt;
                    //console.log('-> bWasPressedAt =', userInputVars.bWasPressedAt);
                    //console.log('-> bTimeSincePress =', userInputVars.bTimeSincePress);
                    let callbacksTriggered = userInputVars.bCallbacksTriggered;
                    //console.log('-> callbacksTriggered =', callbacksTriggered);
                    let pressButtonCallback = function(){
                        //console.log('%c' + 'B key pressButtonCallback()', 'color: green;');
                        };
                    let unpressButtonCallback = function(){
                        //console.log('%c' + 'B key unpressButtonCallback()', 'color: red;');
                        };
                    let holdButtonCallback = function(){
                        //console.log('%c' + 'B key holdButtonCallback()', 'color: magenta;');
                        //console.log('Toggling auto-pickup flags ...');
                        _world.autoApplyConsumables = !_world.autoApplyConsumables ? true : false;
                        _world.autoEquipHoldables = !_world.autoEquipHoldables ? true : false;
                        //console.log('_world.autoApplyConsumables =', _world.autoApplyConsumables);
                        //console.log('_world.autoEquipHoldables =', _world.autoEquipHoldables);
                        let messageMarkup = [], onFlagMarkup = _self.getCustomNameSpan('ON', 'nature'), offFlagMarkup = _self.getCustomNameSpan('OFF', 'flame');
                        messageMarkup.push('Toggling overworld pickup flags ...');
                        messageMarkup.push('Auto-Apply Consumables: ' + (_world.autoApplyConsumables ? onFlagMarkup : offFlagMarkup));
                        messageMarkup.push('Auto-Equip Holdables:  ' + (_world.autoEquipHoldables ? onFlagMarkup : offFlagMarkup));
                        _self.showWorldMessage(messageMarkup); //12345
                        };
                    if (userInputVars.bTimeSincePress < holdTimeThreshold){
                        //console.log('%c' + 'B key pressed!', 'color: orange;');
                        //console.log('-> bTimeSincePress(', userInputVars.bTimeSincePress, ') < holdTimeThreshold(', holdTimeThreshold, ')');
                        //console.log('callbacksTriggered.indexOf(\'pressed\') =', callbacksTriggered.indexOf('pressed'));
                        if (callbacksTriggered.indexOf('pressed') < 0 || allowRepeatCallbacks){ pressButtonCallback(); callbacksTriggered.push('pressed'); }
                        } else {
                        //console.log('%c' + 'B key holding!', 'color: orange;');
                        //console.log('--> bTimeSincePress(', userInputVars.bTimeSincePress, ') >= holdTimeThreshold(', holdTimeThreshold, ')');
                        if (callbacksTriggered.indexOf('holding') < 0 || allowRepeatCallbacks){ holdButtonCallback(); callbacksTriggered.push('holding'); }
                        }
                    if (userInputVars.bTimeout){ clearTimeout(userInputVars.bTimeout);  }
                    userInputVars.bTimeout = setTimeout(function(){
                        if (!activeInputs.B){
                            //console.log('%c' + 'B key unpressed!', 'color: orange;');
                            userInputVars.bWasPressedAt = 0;
                            userInputVars.bCallbacksTriggered = [];
                            unpressButtonCallback();
                            }
                        }, unpressTimeout);
                    }
                */
                }
            };

        // Start the user input watcher and collect reference to active inputs
        let userInputWatcher = new mmrpgUserInputWatcher({ autoStart: true, autoRunCallbacks: false });
        userInputWatcher.onUserInput(checkUserInputs);
        userInputWatcher.startWatching();
        let checkUserInputWatcher = function(){
            userInputWatcher.checkUserInputs();
            requestAnimationFrame(checkUserInputWatcher);
            };
        checkUserInputWatcher();
        _self.inputs = userInputWatcher;
        //console.log('check if loaded _self.inputs.userInputs:', _self.inputs.userInputs);

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

    // Quick function for refreshing the cursor's current map position
    refreshPosition(onComplete, forceMove, animateMove, thisOldPos){
        //console.log('%c' + 'mmrpgWorldMap.refreshPosition()', 'color: magenta;');
        let _self = this;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        forceMove = typeof forceMove === 'boolean' ? forceMove : true;
        animateMove = typeof animateMove === 'boolean' ? animateMove : false;
        if (!thisOldPos){ thisOldPos = [_worldCursor.col, _worldCursor.row]; }
        let newPosition = _worldCursor.col + '-' + _worldCursor.row;
        return _self.moveToPosition(newPosition, onComplete, forceMove, animateMove, thisOldPos);
        }

    // Quick function for moving cursor to a given map position
    moveToPosition(newPosition, onComplete, forceMove, animateMove, thisOldPos){
        //console.log('%c' + 'mmrpgWorldMap.moveToPosition(' + newPosition + ')', 'color: magenta;');
        if (!newPosition || typeof newPosition !== 'string'){ console.error('newPosition is undefined or invalid!'); return false; }
        forceMove = typeof forceMove === 'boolean' ? forceMove : false;
        animateMove = typeof animateMove === 'boolean' ? animateMove : true;
        let _self = this;
        let _selfRef = self;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldZoom = _world.zoomLevel;
        let _reverseWorldZoom = (1 / _worldZoom);
        let _mapEffects = _config.mapEffects;
        let _mapTileSize = _config.mapTileSize;
        let _mapTileSizeOffset = _config.mapTileSizeOffset;
        let _mapSpriteSizeOffset = _config.mapSpriteSizeOffset;
        let _mapStartPosition = _config.mapStartPosition;
        let _mapPortalSymbols = _config.mapPortalSymbols || [];
        let _mapPortalsIndex = _config.mapPortalsIndex || {};
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $sideButtons = _elements.sideButtons;
        let $actionDropdown = _elements.actionDropdown;
        let $teamSprites = _elements.teamSprites;
        let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap); // later: $('.layer[data-layer="sprites"]', $canvasMap);
        let $spriteObjectsLayer = $('.layer[data-layer="sprites/objects"]', $canvasMap);
        let $cursorSprite = $teamSprites.filter('.cursor');
        let $otherSprites = $teamSprites.filter(':not(.cursor)');
        let $trackingCursor = $('.sprite.tracking-cursor', $canvasMap);
        if (!$spritesLayer || !$spritesLayer.length){ console.error('$spritesLayer do not exist!'); return false; }
        if (!$cursorSprite || !$cursorSprite.length){ console.error('$cursorSprite not found!'); return false; }
        if (!$actionDropdown || !$actionDropdown.length){ console.error('$actionDropdown not found!'); return false; }
        if (!thisOldPos){ thisOldPos = [_worldCursor.col, _worldCursor.row]; }
        let thisOldCol = thisOldPos[0]; //_worldCursor.col;
        let thisOldRow = thisOldPos[1]; //_worldCursor.row;
        if (typeof _mapPortalsIndex[newPosition] !== 'undefined'){ newPosition = _mapPortalsIndex[newPosition].pos; }
        if (!newPosition || typeof newPosition !== 'string' || !newPosition.match(/^[0-9]+\-[0-9]+$/)){ console.error('newPosition was invalid!', newPosition); return false; }
        newPosition = newPosition.split('-');
        let thisNewCol = parseInt(newPosition[0]);
        let thisNewRow = parseInt(newPosition[1]);
        let colHasChanged = thisNewCol !== thisOldCol ? true : false;
        let rowHasChanged = thisNewRow !== thisOldRow ? true : false;
        let posHasChanged = colHasChanged || rowHasChanged ? true : false;
        if (!posHasChanged && !forceMove){ console.error('$cursorSprite already at position!'); return false; }
        let thisNewPos = thisNewCol + '-' + thisNewRow;
        let thisHorDir = false;
        let thisVerDir = false;
        let thisShiftDir = '';
        let thisShiftDist = 1;
        if (posHasChanged){
            thisHorDir = (thisNewCol > thisOldCol) ? 'right' : (thisNewCol < thisOldCol) ? 'left' : false;
            thisVerDir = (thisNewRow > thisOldRow) ? 'down' : (thisNewRow < thisOldRow) ? 'up' : false;
            thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join(' and '); })(thisHorDir, thisVerDir);
            thisShiftDist = Math.sqrt(Math.pow(thisNewCol - thisOldCol, 2) + Math.pow(thisNewRow - thisOldRow, 2));
            } else {
            thisShiftDir = _worldCursor.direction.replace('-', ' and ');
            thisShiftDist = 1;
            if (thisShiftDir.indexOf('left') !== -1){ thisHorDir = 'left'; }
            else if (thisShiftDir.indexOf('right') !== -1){ thisHorDir = 'right'; }
            if (thisShiftDir.indexOf('up') !== -1){ thisVerDir = 'up'; }
            else if (thisShiftDir.indexOf('down') !== -1){ thisVerDir = 'down'; }
            }
        //console.log('-> posHasChanged =', posHasChanged, '\n-> thisOldPos =', thisOldPos, '\n-> thisNewPos =', thisNewPos, '\n-> thisShiftDir =', thisShiftDir, '\n-> thisShiftDist =', thisShiftDist);
        let tileOffsetX; // = (((thisNewCol - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0]);
        let tileOffsetY; // = (((thisNewRow - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1]) - 10;
        let tileOffsetZ; // = tileOffsetY + 1;
        if (_mapEffects.usePerspective === true){
            let tileSpriteOffset = _self.getLayerTileSpriteOffset(thisNewCol, thisNewRow);
            tileOffsetX = tileSpriteOffset.left;
            tileOffsetY = tileSpriteOffset.top;
            tileOffsetZ = tileOffsetY + 1;
            } else {
            tileOffsetX = (((thisNewCol - 1) * _mapTileSize[0]) + _mapSpriteSizeOffset[0]);
            tileOffsetY = (((thisNewRow - 1) * _mapTileSize[1]) + _mapSpriteSizeOffset[1]) - 10;
            tileOffsetZ = tileOffsetY + 1;
            }
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
        //console.log('%c' + 'Moving cursor from ' + thisOldCol + '-' + thisOldRow + ' to ' + thisNewCol + '-' + thisNewRow + ' (' + thisShiftDir + ')', 'color: magenta;');
        let moveTimeout;
        let timeoutDuration = _mapEffects.moveTimeout;
        let travelDuration = _mapEffects.moveTravel * thisShiftDist;
        let onTeamMoveComplete = _selfRef.onTeamMoveComplete;
        if (typeof onTeamMoveComplete === 'undefined'){
            onTeamMoveComplete = function(index, callback, clearExisting){
                if (typeof index !== 'number' && typeof index !== 'string'){ return false; }
                if (typeof onTeamMoveComplete.queue === 'undefined'){ onTeamMoveComplete.queue = {}; }
                if (typeof onTeamMoveComplete.queue[index] === 'undefined'){ onTeamMoveComplete.queue[index] = []; }
                let queue = onTeamMoveComplete.queue[index]; if (clearExisting){ queue.length = 0; }
                if (callback){ if (typeof callback === 'function'){ queue.push(callback); } return true; }
                else { while (queue.length > 0){ callback = queue.shift(); callback.call(); } return true; }
                };
            }
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
            //console.log('moving other team sprites to thisVerDir:', thisVerDir, 'thisHorDir:', thisHorDir);
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
            let lastTeamSpriteKind = false;
            teamTravelDuration += 50;
            $otherSpritesInOrder.each(function(index, element){
                let $thisSprite = $(element);
                let spriteKind = $thisSprite.attr('data-sprite');
                let $innerSprite = $('.sprite', $thisSprite);
                let imgSize = $thisSprite.attr('data-size') || 40;
                let imgSizeX = imgSize + 'x' + imgSize;
                //console.log('checking index:', index, ' w/ spriteKind:', spriteKind, ' && imgSizeX:', imgSizeX);
                teamTravelDuration += 50; // add a little extra time for the team sprites to move
                if (!lastTeamSpriteKind){ lastTeamSpriteKind = spriteKind; }
                if (lastTeamSpriteKind !== spriteKind){
                    if (thisVerDir === 'up'){ teamOffsetY += !thisHorDir ? 12 : 6; }
                    else if (thisVerDir === 'down'){ teamOffsetY -= !thisHorDir ? 10 : 5; }
                    if (thisHorDir === 'left'){ teamOffsetX += !thisVerDir ? 6 : 3; }
                    else if (thisHorDir === 'right'){ teamOffsetX -= !thisVerDir ? 6 : 3; }
                    }
                if (thisVerDir === 'up'){ teamOffsetY += !thisHorDir ? 16 : 8; }
                else if (thisVerDir === 'down'){ teamOffsetY -= !thisHorDir ? 20 : 10; }
                else { teamOffsetY -= 1; }
                if (thisHorDir === 'left'){ teamOffsetX += !thisVerDir ? 20 : 10; }
                else if (thisHorDir === 'right'){ teamOffsetX -= !thisVerDir ? 20 : 10; }
                teamOffsetZ = teamOffsetY + 1;
                if (thisHorDir){ $thisSprite.attr('data-dir', thisHorDir); }
                let newFrame = false;
                let preFrameDelay = 0;
                if (animateMove && !$thisSprite.is('.disabled')){
                    $thisSprite.attr('data-frame', '00');
                    newFrame = $thisSprite.is('.player') ? '09' : $thisSprite.is('.robot') ? '07' : '00'; // run for players, slide for robots
                    if (newFrame !== '00'){
                        preFrameDelay = 50;
                        setTimeout(function(){ $thisSprite.attr('data-frame', newFrame); }, preFrameDelay);
                        onTeamMoveComplete(index, function(){ $thisSprite.attr('data-frame', '00'); }, true);
                        }
                    }
                $thisSprite.prop('worldX', teamOffsetX);
                $thisSprite.prop('worldY', teamOffsetY);
                $thisSprite.prop('worldZ', teamOffsetZ);
                if (animateMove){
                    $thisSprite.stop(true, false).animate({
                        left: teamOffsetX + 'px',
                        top: teamOffsetY + 'px',
                        zIndex: teamOffsetZ,
                        }, (teamTravelDuration + preFrameDelay), 'swing', function(){ onTeamMoveComplete(index); });
                    } else {
                    $thisSprite.css({
                        left: teamOffsetX + 'px',
                        top: teamOffsetY + 'px',
                        zIndex: teamOffsetZ,
                        });
                    }
                lastTeamSpriteKind = spriteKind;
                });
            }
        // Make sure we start the scroll to the new position
        _self.scrollMap(tileOffsetX, tileOffsetY);
        _self.scrollMiniMap(thisNewCol, thisNewRow);
        // Start the idle timeout (clearing if already exists) so we can run idle-actions
        if (_selfRef.idleTimeout){ clearTimeout(_selfRef.idleTimeout); }
        _selfRef.idleTimeout = setTimeout(function(){
            //console.log('%c' + 'Idle timeout triggered!', 'color: orange;');
            $cursorSprite.addClass('idle');
            }, 3000);
        return true;
        }

    // Quick function for re-centering the map on the player's position and the map's current zoom level
    scrollMap(scrollX, scrollY, forceRefresh){
        //console.log('%c' + 'mmrpgWorldMap.scrollMap(scrollX:' + scrollX + ', scrollY:' + scrollY + ', forceRefresh:' + forceRefresh + ')', 'color: magenta;');
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
        let $terrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
        if (typeof scrollX !== 'number'){ scrollX = _worldCursor.positionXY[0] || 0; }
        if (typeof scrollY !== 'number'){ scrollY = _worldCursor.positionXY[1] || 0; }
        if (typeof forceRefresh !== 'boolean'){ forceRefresh = false; }
        let worldScroll = _world.scrollPosition || [-1, -1];
        if (!forceRefresh && worldScroll[0] === scrollX && worldScroll[1] === scrollY){ return true; }
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
        let mapSkewSeed = Math.abs(mapTranslateX);
        let mapSkewCenter = Math.abs(worldWidth / 2);
        let mapSkewValue = mapSkewSeed !== mapSkewCenter ? ((mapSkewSeed - mapSkewCenter) / worldWidth) : 0;
        //console.log('mapSkewSeed =', mapSkewSeed, 'mapSkewCenter =', mapSkewCenter, 'mapSkewValue =', mapSkewValue);
        // Apply the new translate values to the map container
        //console.warn('applying the new translate values to the map container', '\n-> old:', worldScroll, '\n-> new:', [scrollX, scrollY]);
        _world.scrollPosition = [scrollX, scrollY];
        //$canvasMap.css({ transform: 'translate(' + mapTranslateX + 'px, ' + mapTranslateY + 'px)' });
        if (worldZoom !== _selfRef.lastWorldZoom){ _world.allowClicks = _world.allowHovers = false; }
        $canvasMap.attr('data-zoom', worldZoom);
        $canvasMap.css({ transformOrigin: 'left top', transform: 'translate(' + mapTranslateX + 'px, ' + mapTranslateY + 'px) scale(' + worldZoom + ')' });
        if (usePerspective){ $canvasMap.get(0).style.setProperty('--map-perspective-skew', mapSkewValue+'deg'); }
        else { $canvasMap.get(0).style.setProperty('--map-perspective-skew', '0deg'); }
        if (worldZoom !== _selfRef.lastWorldZoom){
            if (typeof _selfRef.allowClicksTimeout !== 'undefined'){ clearTimeout(_selfRef.allowClicksTimeout); }
            _selfRef.allowClicksTimeout = setTimeout(function(){ _world.allowClicks = _world.allowHovers = true; }, 1000);
            if (usePerspective){
                if (typeof _selfRef.refreshTerrainTimeout !== 'undefined'){ clearTimeout(_selfRef.refreshTerrainTimeout); }
                _selfRef.refreshTerrainTimeout = setTimeout(function(){ _self.refreshTerrainTileOverlay(); }, 100);
                }
            }
        // Return true on success
        return true;
        }

    // Quick function for re-centering the mini-map on a given column and row regardless of map zoom levels
    scrollMiniMap(newCol, newRow){
        //console.log('%c' + 'mmrpgWorldMap.scrollMiniMap(newCol:' + newCol + ', newRow:' + newRow + ')', 'color: magenta;');
        if (typeof newCol !== 'number' || typeof newRow !== 'number'){ console.error('newCol is invalid!'); return false; }
        if (newCol < 1 || newRow < 1){ console.error('newCol and newRow must be greater than zero!'); return false; }
        // Collect references, indexes, and other variables we need to work with
        let _self = this;
        let _selfRef = self;
        let _config = _self.config;
        let _elements = _self.elements;
        let $minimapOverview = _elements.minimapOverview;
        if (!$minimapOverview || !$minimapOverview.length){ console.error('$minimapOverview does not exist!'); return false; }
        //let $worldViewport = $('.viewport.world', $minimapOverview);
        //if (!$worldViewport || !$worldViewport.length){ console.error('$worldViewport does not exist!'); return false; }
        let $areaViewport = $('.viewport.area', $minimapOverview);
        if (!$areaViewport || !$areaViewport.length){ console.error('$areaViewport does not exist!'); return false; }
        let $areaViewportGrid = $('.grid', $areaViewport);
        let $areaViewportImage = $('.image', $areaViewport);
        //let minimapWorldTileSize = _config.minimapWorldTileSize;
        let minimapAreaTileSize = _config.minimapAreaTileSize;
        let areaTranslateX = -1 * (minimapAreaTileSize * (newCol - 1) + (minimapAreaTileSize / 2));
        let areaTranslateY = -1 * (minimapAreaTileSize * (newRow - 1) + (minimapAreaTileSize / 2));
        //console.log('$areaViewportImage =', $areaViewportImage.length, $areaViewportImage);
        //console.log('-> areaImageWidth =', areaImageWidth, 'areaImageHeight =', areaImageHeight);
        //console.log('-> newCol =', newCol, ' newRow =', newRow);
        //console.log('-> minimapAreaTileSize =', minimapAreaTileSize);
        //console.log('-> areaTranslateX =', areaTranslateX, 'areaTranslateY =', areaTranslateY);
        $areaViewportGrid.css({ transform: 'translate(' + areaTranslateX + 'px, ' + areaTranslateY + 'px)' });
        $areaViewportImage.css({ transform: 'translate(' + areaTranslateX + 'px, ' + areaTranslateY + 'px)' });
        // Return true on success
        return true;
        }

    // Quick function for updating the map's current zoom level
    updateZoomLevel(newZoomLevel, updateUserZoom, forceZoom){
        //console.log('%c' + 'mmrpgWorldMap.updateZoomLevel(newZoomLevel:' + newZoomLevel + ')', 'color: magenta;');
        if (!newZoomLevel || typeof newZoomLevel !== 'number' || newZoomLevel <= 0){
            console.error('updateZoomLevel() requires a valid zoom level!');
            return false;
            }
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        updateUserZoom = typeof updateUserZoom === 'boolean' ? updateUserZoom : false;
        forceZoom = typeof updateUserZoom === 'boolean' ? updateUserZoom : false;
        //console.log('-> newZoomLevel =', newZoomLevel, '-> _config.defaultZoomLevel =', _config.defaultZoomLevel, '\n-> _world.userZoomLevel =', _world.userZoomLevel);
        if (!forceZoom && newZoomLevel === _world.zoomLevel){ return true; } // we are already at the requested zoom level
        _world.zoomLevel = newZoomLevel;
        if (updateUserZoom){ _world.userZoomLevel = newZoomLevel; }
        //console.log('-> re-scrolling map to update zoom...');
        _self.scrollMap(null, null, true);
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
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let userZoomLevel = _world.userZoomLevel || _config.defaultZoomLevel;
        userZoomLevel += modAmount;
        if (userZoomLevel < _config.minZoomLevel){ userZoomLevel = _config.minZoomLevel; }
        else if (userZoomLevel > _config.maxZoomLevel){ userZoomLevel = _config.maxZoomLevel; }
        return _self.updateZoomLevel(userZoomLevel, updateUserZoom);
        }
    incZoomLevel(boostAmount, updateUserZoom){
        let _self = this;
        let _config = _self.config;
        return this.modZoomLevel((boostAmount || _config.zoomIncrement), updateUserZoom);
        }
    decZoomLevel(decAmount, updateUserZoom){
        let _self = this;
        let _config = _self.config;
        return this.modZoomLevel(-(decAmount || _config.zoomIncrement), updateUserZoom);
        }

    // Quick function for resetting the map's zoom level to the user's current setting
    resetZoomLevel(toDefault){
        //console.log('%c' + 'mmrpgWorldMap.resetZoomLevel()', 'color: magenta;');
        toDefault = typeof toDefault === 'boolean' ? toDefault : false;
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        if (_world.zoomLevel === _config.defaultZoomLevel && _world.userZoomLevel === _config.defaultZoomLevel){ return true; } // we are already at the requested zoom level
        let userZoomLevel = toDefault ? _config.defaultZoomLevel : (_world.userZoomLevel || _config.defaultZoomLevel);
        if (userZoomLevel < _config.minZoomLevel){ userZoomLevel = _config.minZoomLevel; }
        else if (userZoomLevel > _config.maxZoomLevel){ userZoomLevel = _config.maxZoomLevel; }
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
        let currentZoomLevel = _world.zoomLevel || _config.defaultZoomLevel;
        if (currentZoomLevel >= maxZoomLevel){ return true; }
        let newZoomLevel = currentZoomLevel + zoomIncrement;
        if (newZoomLevel > maxZoomLevel){ newZoomLevel = maxZoomLevel; }
        _self.updateZoomLevel(newZoomLevel, true);
        await new Promise(resolve => setTimeout(resolve, zoomDelay));
        return _self.animateZoomToMax(maxZoomLevel, zoomIncrement, zoomDelay);
        }

    // Quick function for (re)generating the terrain tile overlay and indexing the positions of the all the tiles
    refreshTerrainTileOverlay(){
        //console.log('%c' + 'mmrpgWorldMap.refreshTerrainTileOverlay()', 'color: pink;');
        let _self = this;
        if (_self.refreshTerrainTileOverlay._scheduled){ return; }
        _self.refreshTerrainTileOverlay._scheduled = true;
        requestAnimationFrame(() => {
            _self.refreshTerrainTileOverlay._scheduled = false;
            _self.refreshTerrainTileOverlayForReal();
            });
        return;
        }
    refreshTerrainTileOverlayForReal(){
        //console.log('%c' + 'mmrpgWorldMap.refreshTerrainTileOverlayForReal()', 'color: magenta;');
        let _self = this;
        let _selfRef = _self.refreshTerrainTileOverlay;
        let _config = _self.config;
        let _mapEffects = _config.mapEffects;
        let _mapSpriteSize = _config.mapSpriteSize;
        let _world = _self.state;
        //let _worldZoom = _world.zoomLevel;
        //let _reverseWorldZoom = (1 / _worldZoom);
        let _layerTileOffsets = _world.layerTileOffsets;
        let _elements = _self.elements;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $terrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
        let $terrainTileOverlay = $('svg[data-overlay="terrain"]', $terrainLayer);
        //let terrainTileOverlayRect = $terrainTileOverlay.length > 0 ? $terrainTileOverlay.get(0).getBoundingClientRect() : null;
        let $spriteObjectsLayer = $('.layer[data-layer="sprites/objects"]', $canvasMap);
        let $spriteOverlaysLayer = $('.layer[data-layer="sprites/overlays"]', $canvasMap);
        let $objectSprites = $('.sprite[data-sprite][data-col][data-row]', $spriteObjectsLayer);
        //console.log('$spriteObjectsLayer =', $spriteObjectsLayer.length, $spriteObjectsLayer);
        //console.log('$objectSprites =', $objectSprites.length, $objectSprites);
        //console.log('-> _worldZoom =', _worldZoom);
        //console.log('-> _reverseWorldZoom =', _reverseWorldZoom);
        // Make sure we add a transparent SVG to the terrain layer to use as reference when positioning sprites in either mode
        if (!$terrainTileOverlay || !$terrainTileOverlay.length){
            let tileOverlayMarkup = '';
            tileOverlayMarkup += '<svg data-overlay="terrain" width="100%" height="100%">';
                for (let row = 0; row < _config.mapRows; row++){
                    for (let col = 0; col < _config.mapCols; col++){
                        let tileX = (col * _config.mapTileSize[0]);
                        let tileY = (row * _config.mapTileSize[1]);
                        let tileCol = col + 1;
                        let tileRow = row + 1;
                        let tilePos = tileCol + '-' + tileRow;
                        let tileAttrs = '';
                        tileAttrs += ' data-pos="' + tilePos + '" data-col="' + tileCol + '" data-row="' + tileRow + '"';
                        tileAttrs += ' width="' + _config.mapTileSize[0] + '" height="' + _config.mapTileSize[1] + '"';
                        tileAttrs += ' x="' + tileX + '" y="' + tileY + '"';
                        tileAttrs += ' fill="transparent"';
                        tileOverlayMarkup += '<rect' + tileAttrs + '></rect>';
                        }
                    }
            tileOverlayMarkup += '</svg>';
            $terrainLayer.prepend(tileOverlayMarkup);
            $terrainTileOverlay = $('svg[data-overlay="terrain"]', $terrainLayer);
            //terrainTileOverlayRect = $terrainTileOverlay.get(0).getBoundingClientRect();
            }
        // Define a function for getting the bounding rect of a given element (layer, tile, or otherwise)
        let getBoundingRect = _selfRef.getBoundingRect;
        if (typeof getBoundingRect === 'undefined'){
            getBoundingRect = function($element){
                if (!$element || !$element.length){ return false; }
                let boundingRect = $element.get(0).getBoundingClientRect();
                return boundingRect;
                };
            _selfRef.getBoundingRect = getBoundingRect;
            }
        // Define a function for getting the reference tile for a given column and row in the terrain layer
        let $referenceTiles = _selfRef.referenceTiles;
        let getReferenceTile = _selfRef.getReferenceTile;
        if (typeof $referenceTiles === 'undefined'){
            $referenceTiles = {};
            _selfRef.referenceTiles = $referenceTiles;
            }
        if (typeof getReferenceTile === 'undefined'){
            getReferenceTile = function($layer, col, row){
                //console.log('%c' + '~ getReferenceTile($layer, col:' + col + ', row:' + row + ')', 'color: magenta;');
                let $tileRect, tileKey = col + '-' + row;
                if (typeof $referenceTiles[tileKey] !== 'undefined'){ $tileRect = $referenceTiles[tileKey]; }
                else { $tileRect = $('rect[data-col="' + col + '"][data-row="' + row + '"]', $layer); }
                if (!$tileRect || !$tileRect.length){ return false; }
                return $tileRect;
                };
            _selfRef.getReferenceTile = getReferenceTile;
            }
        // Define a function for getting the relative position of a theoretical element given a target and destination wrapper
        let getTilePositionRelative = _selfRef.getTilePositionRelative;
        if (typeof getTilePositionRelative === 'undefined'){
            getTilePositionRelative = function($target, $destinationWrapper){
                let targetRect = $target[0].getBoundingClientRect();
                let wrapperRect = $destinationWrapper[0].getBoundingClientRect();
                let wrapperLeft = wrapperRect.left / _world.zoomLevel, wrapperTop = wrapperRect.top / _world.zoomLevel;
                let targetLeft = ((targetRect.left - wrapperRect.left) / _world.zoomLevel), targetTop = ((targetRect.top - wrapperRect.top) / _world.zoomLevel);
                let targetWidth = targetRect.width / _world.zoomLevel, targetHeight = targetRect.height / _world.zoomLevel;
                let targetCenter = { x: targetLeft + (targetWidth / 2), y: targetTop + (targetHeight / 2) };
                let adjustedValues = {
                    left: Math.round(targetCenter.x * 100) / 100,
                    top: Math.round(targetCenter.y * 100) / 100,
                    width: Math.round(targetWidth * 100) / 100,
                    height: Math.round(targetHeight * 100) / 100,
                    };
                //console.log('wrapperRect: ', wrapperRect, {wrapperLeft, wrapperTop});
                //console.log('targetRect: ', targetRect, {targetLeft, targetTop, targetWidth, targetHeight}, '\n' + 'targetCenter: ', targetCenter);
                //console.log('return adjustedValues:', adjustedValues);
                return adjustedValues;
                };
            _selfRef.getTilePositionRelative = getTilePositionRelative;
            }
        // Define a function for getting the external tile offset for a given column and row for placing sprites
        let getLayerTileOffset = _selfRef.getLayerTileOffset;
        //console.log('gameSettings.gameHasLoaded =', gameSettings.gameHasLoaded);
        //console.log('gameSettings.gameHasStarted =', gameSettings.gameHasStarted);
        //console.log('_world.zoomLevel =', _world.zoomLevel);
        //console.log('_reverseWorldZoom =', _reverseWorldZoom);
        if (typeof getLayerTileOffset === 'undefined'){
            getLayerTileOffset = function($layer, col, row){
                //console.log('%c' + '~ getLayerTileOffset(col:' + col + ', row:' + row + ')', 'color: magenta;');
                let $refLayer = $layer;
                let $refTile = _selfRef.getReferenceTile($refLayer, col, row);
                let $targetLayer = $spriteObjectsLayer;
                let layerTileOffset = _selfRef.getTilePositionRelative($refTile, $targetLayer);
                //console.log('-> $refLayer =', $refLayer.length, $refLayer);
                //console.log('-> $refTile =', $refTile.length, $refTile);
                //console.log('-> refTileRect =', JSON.stringify(refTileRect, null, 2));
                //console.log('-> layerTileOffset =', JSON.stringify(layerTileOffset, null, 2));
                return layerTileOffset;
                };
            _selfRef.getLayerTileOffset = getLayerTileOffset;
            }

        // Refresh (or create) the terrain layer tile offsets for everything to reference
        _self.refreshTerrainTileOffsets();

        // Refresh (or create) the position overlays to ensure everything is working
        _self.refreshTerrainPositionOverlays();

        // Return true on success
        return true;
        }

    // Define a quick function for refreshing the index of terrain tile offsets for positioning purposes
    refreshTerrainTileOffsets(){
        //console.log('%c' + 'mmrpgWorldMap.refreshTerrainTileOffsets()', 'color: magenta;');
        let _self = this;
        let _selfRef = _self.refreshTerrainTileOverlay;
        let _config = _self.config;
        let _world = _self.state;
        let _layerTileOffsets = _world.layerTileOffsets;
        let _elements = _self.elements;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $terrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
        let $terrainTileOverlay = $('svg[data-overlay="terrain"]', $terrainLayer);
        let $spriteObjectsLayer = $('.layer[data-layer="sprites/objects"]', $canvasMap);
        let $objectSprites = $('.sprite[data-sprite][data-col][data-row]', $spriteObjectsLayer);
        //console.log('$spriteObjectsLayer =', $spriteObjectsLayer.length, $spriteObjectsLayer);
        //console.log('$objectSprites =', $objectSprites.length, $objectSprites);
        //console.log('-> _worldZoom =', _worldZoom);
        //console.log('-> _reverseWorldZoom =', _reverseWorldZoom);

        // Index the offet positions for these new reference tiles and save to the index
        for (let row = 1; row <= _config.mapRows; row++){
            for (let col = 1; col <= _config.mapCols; col++){
                let $refLayer = $terrainTileOverlay;
                let $refTile = _selfRef.getReferenceTile($refLayer, col, row);
                let layerTileOffset = _selfRef.getLayerTileOffset($refLayer, col, row);
                //console.log('(re) setting _layerTileOffsets[', (col + '-' + row), '] = ', JSON.stringify(layerTileOffset));
                _layerTileOffsets[col + '-' + row] = layerTileOffset;
                layerTileOffset.element = $refTile;
                }
            }
        _world.layerTileOffsets = _layerTileOffsets;
        //console.log('-> _layerTileOffsets =', _layerTileOffsets);
        //console.log('-> _world.layerTileOffsets =', JSON.stringify(_world.layerTileOffsets, null, 2));

        }

    // Quick function for adding position overlays to all tiles on the map
    refreshTerrainPositionOverlays(){
        //console.log('%c' + 'mmrpgWorldMap.refreshTerrainPositionOverlays()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        //let _worldZoom = _world.zoomLevel;
        //let _reverseWorldZoom = (1 / _worldZoom);
        //let _layerTileOffsets = _world.layerTileOffsets;
        let _elements = _self.elements;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $spriteOverlaysLayer = $('.layer[data-layer="sprites/overlays"]', $canvasMap);
        let positionOverlaysMarkup = '';
        for (let row = 1; row <= _config.mapRows; row++){
            for (let col = 1; col <= _config.mapCols; col++){
                let positionText = 'X' + col + '-' + 'Y' + row;
                let positionOffset = _world.layerTileOffsets[col + '-' + row];
                let positionLabelLeft = positionOffset.left - (positionOffset.width / 2);
                let positionLabelTop = positionOffset.top - (positionOffset.height / 2);
                let positionLabelOffset = {
                    left: Math.round(positionLabelLeft * 100) / 100,
                    top: Math.round(positionLabelTop * 100) / 100,
                    width: Math.round(positionOffset.width * 100) / 100,
                    height: Math.round(positionOffset.height * 100) / 100,
                    };
                let positionLabelStyles = 'top: ' + positionLabelOffset.top + 'px; left: ' + positionLabelOffset.left + 'px;';
                positionLabelStyles += 'width: ' + positionLabelOffset.width + 'px; height: ' + positionLabelOffset.height + 'px;';
                let positionLabelMarkup = '';
                positionLabelMarkup += '<div class="sprite overlay position-overlay" data-pos="' + (col + '-' + row) + '" style="' + positionLabelStyles + '">';
                    positionLabelMarkup += '<strong class="label position-label">' + positionText + '</strong>';
                positionLabelMarkup += '</div>';
                positionOverlaysMarkup += positionLabelMarkup;
            }
        }
        $spriteOverlaysLayer.find('.position-overlay').remove();
        $spriteOverlaysLayer.append(positionOverlaysMarkup);
        return true;
        }

    // Quick function for toggling perspective mode on/off and then re-scrolling the map to refresh
    togglePerspectiveMode(state){
        //console.log('%c' + 'mmrpgWorldMap.togglePerspectiveMode()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _mapEffects = _config.mapEffects;
        let _mapSpriteSize = _config.mapSpriteSize;
        let _world = _self.state;
        //let _worldZoom = _world.zoomLevel;
        //let _layerTileOffsets = _world.layerTileOffsets;
        //let _reverseWorldZoom = (1 / _worldZoom);
        let _elements = _self.elements;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.map;
        let $terrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
        //let $terrainTileOverlay = $('svg[data-overlay="terrain"]', $terrainLayer);
        //let terrainTileOverlayRect = $terrainTileOverlay.length > 0 ? $terrainTileOverlay.get(0).getBoundingClientRect() : null;
        let $spriteObjectsLayer = $('.layer[data-layer="sprites/objects"]', $canvasMap);
        let $objectSprites = $('.sprite[data-sprite][data-col][data-row]', $spriteObjectsLayer);
        //console.log('$spriteObjectsLayer =', $spriteObjectsLayer.length, $spriteObjectsLayer);
        //console.log('$objectSprites =', $objectSprites.length, $objectSprites);
        //console.log('-> _worldZoom =', _worldZoom);
        //console.log('-> _reverseWorldZoom =', _reverseWorldZoom);
        // Back-up the canvas width and height in case we end up changing it
        if (!_config.baseMapWidth){ _config.baseMapWidth = _config.mapWidth; }
        if (!_config.baseMapHeight){ _config.baseMapHeight = _config.mapHeight; }
        // If perspective is turned on, let's apply necessary styles and adjustments then reposition sprites
        let oldState = _mapEffects.usePerspective;
        let newState = typeof state === 'boolean' ? state : !_mapEffects.usePerspective;
        if (newState === true){
            _mapEffects.usePerspective = true;
            //console.log('-> enabling perspective mode!');
            //console.log('-> $canvasMap =', $canvasMap);
            //console.log('-> base values:');
            let baseCanvasWidth = _config.baseMapWidth;
            let baseCanvasHeight = _config.baseMapHeight;
            let basePerspectiveWidth = 4000; //_config.mapWidth;
            let basePerspectiveSkew = 0;
            //console.log('-> baseCanvasWidth:', baseCanvasWidth, 'baseCanvasHeight:', baseCanvasHeight);
            //console.log('-> basePerspectiveWidth:', basePerspectiveWidth);
            //console.log('-> _config.mapWidth:', _config.mapWidth, '_config.mapHeight:', _config.mapHeight);
            $canvasMap.addClass('has-perspective');
            $canvasMap.get(0).style.setProperty('--map-perspective-width', basePerspectiveWidth+'px');
            $canvasMap.get(0).style.setProperty('--map-perspective-skew', basePerspectiveSkew+'deg');
            // // Adjust the map size to account for the perspective transform scaling
            let newCanvasRect = $terrainLayer[0].getBoundingClientRect();
            let newCanvasWidth = newCanvasRect.width, newCanvasHeight = newCanvasRect.height;
            let newMapWidth = newCanvasWidth / _world.zoomLevel, newMapHeight = newCanvasHeight / _world.zoomLevel;
            //console.log('-> via getBoundingClientRect()');
            //console.log('-> newCanvasRect:', newCanvasRect);
            //console.log('-> newCanvasWidth:', newCanvasWidth, 'newCanvasHeight:', newCanvasHeight);
            //console.log('-> newMapWidth:', newMapWidth, 'newMapHeight:', newMapHeight);
            _config.mapWidth = newMapWidth, _config.mapHeight = newMapHeight;
            $canvasMap.css({ width: newMapWidth + 'px', height: newMapHeight + 'px' });
            }
        // Otherwise if perspective not enabled, make sure we put everything back to normal and reposition sprites
        else {
            _mapEffects.usePerspective = false;
            //console.log('-> disabling perspective mode!');
            $canvasMap.removeClass('has-perspective');
            $canvasMap.get(0).style.setProperty('--map-perspective-width', '');
            $canvasMap.get(0).style.setProperty('--map-perspective-skew', '');
            //console.log('-> revert to _config.mapWidth:', _config.mapWidth, '_config.mapHeight:', _config.mapHeight);
            let baseCanvasWidth = _config.baseMapWidth;
            let baseCanvasHeight = _config.baseMapHeight;
            _config.mapWidth = baseCanvasWidth, _config.mapHeight = baseCanvasHeight;
            $canvasMap.css({ width: baseCanvasWidth + 'px', height: baseCanvasHeight + 'px' });
            }

        // If the world is not ready, we shouldn't do anything yet
        //if (!_world.hasLoaded || !_world.isReady){ return; }

        // Define a method for aligning all objects sprites to the appropriate perspective position
        let alignSpritesToPositions = function(){
            // Define a function for aligning a given object sprite to a given column and row using the SVG tile reference we constructed
            let alignSpriteToMapPosition = function($sprite, col, row){
                //console.log('-----------------------------');
                //console.log('%c' + 'mmrpgWorldMap...alignSpriteToMapPosition($sprite, col:', col, ', row:', row, ')');
                if (!$sprite || !$sprite.length){ console.error('$sprite is invalid!'); return false; }
                if (typeof col !== 'number' || typeof row !== 'number'){ console.error('col and row must be numbers!'); return false; }
                if (col < 1 || row < 1){ console.error('col and row must be greater than zero!'); return false; }
                let refTileOffset = _self.getLayerTileOffset(col, row);
                let spriteTileOffset = _self.getLayerTileSpriteOffset(col, row);
                //let spriteTileOffset = _world.layerTileOffsets[col + '-' + row];
                //console.log('-> spriteTileOffset =', JSON.stringify(spriteTileOffset, null, 2));
                $sprite.css({left: spriteTileOffset.left + 'px', top: spriteTileOffset.top + 'px', zIndex: (spriteTileOffset.top + 1)});
                //console.log('-> $sprite =', $sprite.length, $sprite);
                };
            // Re-align all the object sprites now that we've toggled the perspective and adjusted sizing parameters
            $objectSprites.each(function(){
                let $thisSprite = $(this);
                let spriteCol = parseInt($thisSprite.attr('data-col')) || 1;
                let spriteRow = parseInt($thisSprite.attr('data-row')) || 1;
                //let spritePosition = spriteCol + '-' + spriteRow;
                alignSpriteToMapPosition($thisSprite, spriteCol, spriteRow);
                });
            // Return true on success
            return true;
            };

        // Update the scrollMap method so that it also re-aligns sprites
        if (typeof _self.__scrollMap === 'undefined'){ _self.__scrollMap = _self.scrollMap; }
        if (newState === true){
            _self.scrollMap = function(){
                alignSpritesToPositions();
                return _self.__scrollMap();
                };
            } else {
            _self.scrollMap = _self.__scrollMap;
            }

        // Re-Index the offet positions for these new reference tiles and save to the index
        _self.refreshTerrainTileOverlay();

        // Re-scoll the map so things are into view now that everything has been fully adjusted
        _self.onWorldReady(function(){

            // Refresh the camera position to the cursor
            _self.refreshPosition();
            _self.scrollMap();

            });

        // Return true on success
        return true;

        }

    // Define a quick function for getting the offset of a given layer tile on the map
    getLayerTileOffset(col, row){
        //console.log('%c' + 'mmrpgWorldMap.getLayerTileOffset(col:' + col + ', row:' + row + ')', 'color: magenta;');
        if (typeof col !== 'number' || typeof row !== 'number'){ console.error('col and row must be numbers!'); return false; }
        if (col < 1 || row < 1){ console.error('col and row must be greater than zero!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _mapEffects = _config.mapEffects;
        let _mapSpriteSize = _config.mapSpriteSize;
        let _world = _self.state;
        let _worldZoom = _world.zoomLevel;
        let _layerTileOffsets = _world.layerTileOffsets;
        let _reverseWorldZoom = (1 / _worldZoom);
        let refTilePosition = col + '-' + row;
        let refTileOffset = _layerTileOffsets[refTilePosition];
        if (!refTileOffset || typeof refTileOffset === 'undefined'){
            console.error('No tile offset found for position ' + refTilePosition + '!');
            //console.log('_layerTileOffsets =', JSON.stringify(_layerTileOffsets, null, 2));
            return false;
            }
        return refTileOffset;
        }

    // Define a quick function for getting the sprite offset of a given layer tile on the map
    getLayerTileSpriteOffset(col, row){
        //console.log('%c' + 'mmrpgWorldMap.getLayerTileSpriteOffset(col:' + col + ', row:' + row + ')', 'color: magenta;');
        if (typeof col !== 'number' || typeof row !== 'number'){ console.error('col and row must be numbers!'); return false; }
        if (col < 1 || row < 1){ console.error('col and row must be greater than zero!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _mapEffects = _config.mapEffects;
        let _mapSpriteSize = _config.mapSpriteSize;
        let refTileOffset = _self.getLayerTileOffset(col, row);
        if (!refTileOffset || typeof refTileOffset === 'undefined'){ return false; }
        let tileSpriteOffset = {left: 0, top: 0, width: 0, height: 0};
        tileSpriteOffset.left = refTileOffset.left - (_mapSpriteSize[0] / 2); //- (refTileOffset.width / 2); // - (_mapSpriteSize[0] / 2);
        tileSpriteOffset.top = refTileOffset.top - (refTileOffset.height / 2) - (_mapSpriteSize[1] / 2); // - 10; // why 10?
        tileSpriteOffset.width = refTileOffset.width;
        tileSpriteOffset.height = refTileOffset.height;
        //console.log('return tileSpriteOffset', tileSpriteOffset);
        return tileSpriteOffset;
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
        //let newPositionText = _config.mapName + ' | ' + ('X' + thisNewCol + '-Y' + thisNewRow);
        //$positionDisplayWrapper.text('X:' + thisNewCol + ' Y:' + thisNewRow);
        //$positionDisplayWrapper.text(newPositionText);
        let newPositionText = '';
        newPositionText += '<strong class="area">' + _config.mapName + '</strong>';
        newPositionText += '<data class="coords">' + ('X' + thisNewCol + '-Y' + thisNewRow) + '</data>';
        $positionDisplayWrapper.html(newPositionText);

        // Make sure we start the scroll to the new position if not already there
        let worldScroll = _world.scrollPosition || [-1, -1];
        if (worldScroll[0] !== cursorPositionXY[0]
            || worldScroll[1] !== cursorPositionXY[1]){

            // Then we actually scroll the map to the requested position on-screen
            _self.scrollMap(cursorPositionXY[0], cursorPositionXY[1]);

            }

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
    async refreshMapPositionEvents(timeoutMultiplier, forceRefresh){
        //console.log('%c' + 'mmrpgWorldMap.refreshMapPositionEvents()', 'color: magenta;');
        timeoutMultiplier = typeof timeoutMultiplier === 'number' ? timeoutMultiplier : 1;
        forceRefresh = typeof forceRefresh === 'boolean' ? forceRefresh : false;

        // Collect references, indexes, and other variables we need to work with
        let _self = this;
        let _selfRef = _self.refreshMapPositionEvents;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldCursor = _world.cursor;
        let _worldPlayer = _world.player;
        let _worldPlayerTeam = _worldPlayer.team;
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
        let lastPosition = _selfRef.lastPosition || false;
        let lastDirection = _selfRef.lastDirection || false;
        let cursorPosition = _worldCursor.position;
        let cursorDirection = _worldCursor.direction;
        let newPosition = cursorPosition.split('-');
        let thisNewCol = parseInt(newPosition[0]);
        let thisNewRow = parseInt(newPosition[1]);
        let sameAsLastPosition = lastPosition === cursorPosition ? true : false;
        let sameAsLastDirection = lastDirection === cursorDirection ? true : false;
        let stillAtPosition = function(){ return (_worldCursor.position === cursorPosition) ? true : false; };
        let otherMenusActiveNow = function(){ return ( _elements.robotsOverview.is('.expanded') ) ? true : false; };
        //let otherMenusActiveNow = function(){ return (_elements.robotsOverview.is('.expanded') || _elements.sideButtons.is('.active')) ? true : false; };
        //console.log('-> lastPosition (old):', lastPosition);
        //console.log('-> lastDirection (old):', lastDirection);
        //console.log('-> cursorPosition (new):', cursorPosition);
        //console.log('-> cursorDirection (new):', cursorDirection);
        //console.log('-> sameAsLastPosition:', sameAsLastPosition);
        //console.log('-> sameAsLastDirection:', sameAsLastDirection);

        // If nothing has changed, we should not do anything further
        if (sameAsLastPosition && sameAsLastDirection && !forceRefresh){
            //console.log('-> player has not changed position or direction, skipping further processing');
            return true;
            }

        // Update the "last" variables for next time
        _selfRef.lastPosition = cursorPosition;
        _selfRef.lastDirection = cursorDirection;

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
        if (teamRushDuration < 1){ teamRushDuration = 100; }
        //console.log('timeoutMultiplier = ', timeoutMultiplier);
        //console.log('teamRushDuration = ', teamRushDuration);

        // Collect references to the required sprites layer for adjustments
        let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap); // later: $('.layer[data-layer="sprites"]', $canvasMap);
        if (!$spritesLayer || !$spritesLayer.length){
            console.error('updateMapPosition() missing required $spritesLayer!');
            return false;
            }
        //console.log('-> $eventsLayer found, checking for events...');

        // Make sure we empty and hide the action dropdown if it's been shown by previous move
        let $actionDropdown = _elements.actionDropdown;
        let $actionDropdownWrapper = $('> .wrapper', $actionDropdown);
        if (!sameAsLastPosition){
            $actionDropdown.removeClass('active').css({left: '', top: ''}).removeAttr('data-dir');
            $actionDropdownWrapper.empty();
            }

        // Make sure we also empty the sidebar buttons in case any were added by previous move
        let $sideButtons = _elements.sideButtons;
        let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
        if (!sameAsLastPosition){
            $sideButtons.removeClass('active');
            $sideButtonsWrapper.empty();
            }

        // Make sure we move any existing zoom layer sprites back to their original layers
        if (!sameAsLastPosition){
            $worldCursor.removeClass('shake');
            $spritesLayer.removeClass('has-zoom');
            setTimeout(function(){ $('.sprite', $canvasMap).removeClass('zoom'); }, 100);
            }

        // Search for events at the new position so we can show the action dropdown if needed
        //console.log('-> checking if there are any events for this position...');
        let eventsAtPosition = _self.getEventsAtPosition(cursorPosition, cursorDirection);
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
        let autoRedirectEffect = '';
        let autoRedirectAnimation = '';
        let showActionArea = false;
        let showActionAreaType = '';
        let showActionAreaSound = '';
        let actionAreaMarkup = '';
        let sideButtonsMarkup = '';
        let readyTeamSprites = false;
        let readyTeamSpritesAnyway = false;
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
            //console.log('-> event at position is a portal, preparing redirect', '\n-> firstEvent:', firstEvent);
            // If the cursor is literally on a portal, only one event sprite matters right now
            let $portalEvent = $(firstEvent.sprite);
            let dataLabel = $portalEvent.attr('data-label');
            let dataPortal = $portalEvent.attr('data-portal');
            let portalInfo = dataPortal ? (_config.mapPortalsIndex[dataPortal] || false) : false;
            let goToDestination = false;
            let goToWorld, goToMap, goToPosition;
            let goToSameWorld, goToSameMap, goToSamePosition;
            //let goToDestination = portalInfo ? (portalInfo['dst'] || false) : false;
            if (dataPortal.indexOf('goto__') !== -1){
                goToDestination = true;
                goToWorld = _config.mapWorld;
                goToMap = dataPortal.replace(/^goto__/i, '');
                if (goToMap.indexOf('__') !== -1){ let gtm = goToMap.split('__'); goToWorld = gtm[0]; goToMap = gtm[1]; }
                if (portalInfo['dst']){ goToPosition = portalInfo['dst']; }
                } else if (portalInfo['dst']){
                goToDestination = true;
                goToWorld = _config.mapWorld;
                goToMap = portalInfo['dst'];
                if (goToMap.indexOf('__') !== -1){
                    let gtm = goToMap.split('__');
                    //console.log('-> gtm =', gtm);
                    if (gtm.length >= 3){ goToWorld = gtm[0]; goToMap = gtm[1]; goToPosition = gtm[2]; }
                    else if (gtm.length >= 2){ goToMap = gtm[0]; goToPosition = gtm[1]; }
                    }
                }
            goToSameWorld = goToWorld === _config.mapWorld ? true : false;
            goToSameMap = goToSameWorld && goToMap === _config.mapToken ? true : false;
            goToSamePosition = goToSameWorld && goToSameMap && goToPosition === _worldCursor.position ? true : false;
            //console.log('----------------------------------');
            //console.log('-> found portalInfo for ' + dataPortal + ':', portalInfo);
            //console.log('-> w/ goToDestination =', goToDestination, '\n-> w/ goToWorld =', goToWorld, '\n-> w/ goToMap =', goToMap, '\n-> w/ goToPosition =', goToPosition);
            //console.log('-> w/ goToSameWorld =', goToSameWorld, '\n-> w/ goToSameMap =', goToSameMap, '\n-> w/ goToSamePosition =', goToSamePosition);
            if (portalInfo && goToDestination){
                //console.log('-> portalInfo has a valid destination!');
                showActionArea = true;
                if (!dataLabel){ dataLabel = 'Portal Options'; }
                actionAreaMarkup += '<strong class="label">' + dataLabel + '</strong>';
                if (dataPortal === 'exit'){ sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Return Home</span></a>'; }
                else { sideButtonsMarkup += '<a class="button big-button" data-action="enter-portal" data-portal="'+dataPortal+'"><span><sup>Ready To</sup> Enter Teleport</span></a>'; }
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                showActionAreaType = 'portal';
                zoomTimeoutDuration = 500; // if we show a portal dropdown, we want to zoom in quickly
                // Automatically redirect to this portal if cursor has moved at least once
                if (_worldCursor.moved){
                    //console.log('-> entering portal with name ' + dataPortal + '!');
                    if (dataPortal === 'spawn'){
                        // TODO: SPAWN PORTAL - make the spawn actually go somewhere specific ?
                        console.warn('-> spawn portals not yet implemented yet');
                        }
                    else if (dataPortal === 'exit'){
                        // TODO: EXIT PORTAL - make the exit actually go somewhere specific ?
                        autoRedirect = true;
                        showActionArea = false;
                        autoRedirectURL = 'prototype.php';
                        }
                    else {
                        // GOTO PORTAL - use the world, map, and position info collected earlier to redirect
                        if (goToSameWorld && goToSameMap && !goToSamePosition){
                            //console.log('-> preparing auto-effect of moving to NEW POSITION on SAME MAP via portal...');
                            triggerEffect = true;
                            showActionArea = false;
                            triggerEffectFunction = function(){
                                if (!stillAtPosition() || otherMenusActiveNow()){ return false; }
                                //console.log('-> running triggerEffectFunction for portal to moveWorldCursorToPosition(' + goToPosition + ')');
                                _config.allowWorldEvents = false; // prevent re-triggering events during teleport
                                if (triggerEffectSound){ _self.playSoundEffect(triggerEffectSound); }
                                _self.moveToPosition(goToPosition, function(){
                                    //console.log('-> moveToPosition() complete via portal to new position ' + goToPosition);
                                    _config.allowWorldEvents = true; // re-allow world events after teleport complete
                                    return true;
                                    });
                                };
                            triggerEffectSound = 'bounce-sound';
                            readyTeamSprites = true;
                            }
                        else {
                            //console.log('-> preparing auto-redirect to NEW MAP via portal...');
                            //console.log('-> w/ portalInfo =', portalInfo);
                            //alert('portalInfo =' + JSON.stringify(portalInfo));
                            autoRedirect = true;
                            showActionArea = false;
                            autoRedirectURL = 'world.php?world=' + goToWorld;
                            if (goToMap){ autoRedirectURL += '&map='+goToMap; }
                            if (goToPosition){ autoRedirectURL += '&position='+goToPosition; }
                            //console.log('-> autoRedirectURL =', autoRedirectURL);
                            //if (!confirm('teleport to ' + autoRedirectURL + '?')){ autoRedirectURL = false; } // TEMP TEMP TEMP
                            readyTeamSprites = true;
                            readyTeamSpritesAnyway = true;
                            teamReadyDuration = 600; // we want the animation to start right away
                            let isExitPortal = portalInfo.direction ? true : false;
                            let isTelePortal = !portalInfo.direction ? true : false;
                            let isSubTelePortal = goToMap === 'debug-area-0' ? true : false;
                            autoRedirectSound = isExitPortal ? 'lets-go-robots' : 'intense-growing-sound'; //'bounce-sound';
                            autoRedirectEffect = isExitPortal ? 'leaving' : 'glowing';
                            if (isExitPortal){ autoRedirectAnimation = 'slide-' + portalInfo.direction; }
                            else if (isTelePortal){ autoRedirectAnimation = isSubTelePortal ? 'descend-downward' : 'ascend-upward'; }
                            }
                        }
                    }
                // Otherwise we can only prepare the dropdown details and wait
                else {
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
            //console.log('-> dataBattles after sorting by position:', dataBattles.join('\n'));
            if (dataLabels.length && dataBattles.length){
                showActionArea = true;
                readyTeamSprites = true;
                //console.log('showing dropdown with battles:', '\n->', dataBattles.join('\n-> '));
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
                //console.log('generated dataBattlesJoined:', '\n->', dataBattlesJoined.split(',').join('\n-> '));
                actionAreaMarkup += dataLabelsJoined;
                if (playerActiveRobots >= 1){  sideButtonsMarkup += '<a class="button big-button" data-action="start-battle" data-battle="'+dataBattlesJoined+'"><span><sup>Ready To</sup> Start Battle</span></a>'; }
                else { sideButtonsMarkup += '<a class="button big-button disabled" data-battle="'+dataBattlesJoined+'"><span><sup>Ready To</sup> Start Battle</span></a>'; }
                sideButtonsMarkup += '<a class="button sub-button" data-action="dismiss"><span>Dismiss</span></a>';
                //console.log('sideButtonsMarkup =', sideButtonsMarkup);
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
            //console.log('%c' + 'getTeamSpritesReady()', 'color: cyan;');
            if (_self.worldIsBusy() && !readyTeamSpritesAnyway){ return; }
            if (!stillAtPosition() || otherMenusActiveNow()){ return; }

            // Add the shake class to the cursor so it hides behind the player
            $worldCursor.addClass('shake');

            // Collect the position of the firstEvent (the one we're triggering) and use it to
            // decide which direction the cursor needs to face in order to "see" the first event
            //console.log('firstEvent = ', firstEvent);
            //console.log('team is facing firstEvent:', '\n-> token:', firstEvent.token, '\n-> sprite:', firstEvent.sprite);
            let cursorPosition = _worldCursor.position;
            let cursorPositionXY = cursorPosition.split('-').map(function(num){ return parseInt(num); });
            let cursorDirection = _worldCursor.direction;
            let firstEventPosition = firstEvent.position;
            let firstEventPositionXY = firstEventPosition.split('-').map(function(num){ return parseInt(num); });
            let firstEventDirection = [];
            if (firstEventPositionXY[0] > cursorPositionXY[0]){ firstEventDirection.push('right'); }
            else if (firstEventPositionXY[0] < cursorPositionXY[0]){ firstEventDirection.push('left'); }
            if (firstEventPositionXY[1] < cursorPositionXY[1]){ firstEventDirection.push('up'); }
            else if (firstEventPositionXY[1] > cursorPositionXY[1]){ firstEventDirection.push('down'); }
            firstEventDirection = firstEventDirection.join('-');
            //console.log('-> cursorPosition =', cursorPosition, cursorPositionXY);
            //console.log('-> cursorDirection =', cursorDirection);
            //console.log('-> firstEventPosition =', firstEventPosition, firstEventPositionXY);
            //console.log('-> firstEventDirection =', firstEventDirection);

            // Collect details about the world cursor direction so we know where we're looking
            //let goingUp = _worldCursor.direction.indexOf('up') !== -1 ? true : false;
            //let goingDown = _worldCursor.direction.indexOf('down') !== -1 ? true : false;
            //let goingLeft = _worldCursor.direction.indexOf('left') !== -1 ? true : false;
            //let goingRight = _worldCursor.direction.indexOf('right') !== -1 ? true : false;
            let goingUp = firstEventDirection.indexOf('up') !== -1 ? true : false;
            let goingDown = firstEventDirection.indexOf('down') !== -1 ? true : false;
            let goingLeft = firstEventDirection.indexOf('left') !== -1 ? true : false;
            let goingRight = firstEventDirection.indexOf('right') !== -1 ? true : false;
            let goingHorz = goingLeft || goingRight ? true : false;
            let goingVert = goingUp || goingDown ? true : false;

            // Collect all the team sprites and details about their current positions
            let rushDistanceX = Math.ceil(_mapTileSize[0] / 4);
            let rushDistanceY = Math.ceil(_mapTileSize[1] / 2); //Math.ceil(_mapTileSize[1] / 4);
            let playerFrames = ['06', '01', '04'];
            let robotFrames = ['04', '08', '01', '06', '10', '00', '04', '01'];
            let $cursorSprite = $teamSprites.filter('.sprite.cursor');
            let $otherSprites = $teamSprites.filter('.sprite:not(.cursor)');
            let $playerSprites = $otherSprites.filter('.sprite.player');
            let $robotSprites = $otherSprites.filter('.sprite.robot');
            //console.log(('-> eventsAtPosition =', eventsAtPosition);
            //console.log(('-> goingRight =', goingRight, '| goingLeft =', goingLeft, '| goingUp =', goingUp, '| goingDown =', goingDown);
            //console.log(('-> rushDistanceX =', rushDistanceX, '| rushDistanceY =', rushDistanceY);
            //console.log('-> $otherSprites =', $otherSprites);

            // If there are any effect to apply beforehand, do it now
            let willAscendUpward = autoRedirectAnimation === 'ascend-upward' ? true : false;
            let willDescendDownward = autoRedirectAnimation === 'descend-downward' ? true : false;
            let willFloatVertically = willAscendUpward || willDescendDownward ? true : false;
            let willSlideHorizontally = autoRedirectAnimation.indexOf('slide-') === 0 ? true : false;
            let willSlideDirection = willSlideHorizontally ? autoRedirectAnimation.replace('slide-', '') : false;
            //console.log('willSlideHorizontally =', willSlideHorizontally);
            //console.log('willSlideDirection =', willSlideDirection);
            let spriteClassBeforeMove = '', spriteClassAfterMove = '';
            if (autoRedirectEffect){
                if (autoRedirectEffect === 'glowing'){ spriteClassBeforeMove = 'glowing'; }
                else if (autoRedirectEffect === 'leaving'){ spriteClassBeforeMove = 'leaving'; }
                }
            if (autoRedirectAnimation){
                if (willAscendUpward){ spriteClassAfterMove = 'ascending'; }
                else if (willDescendDownward){ spriteClassAfterMove = 'descending'; }
                }
            let spriteBeforeMove = function(){
                if (!spriteClassBeforeMove){ return; }
                $(this).removeClass('shake bounce idle');
                $(this).addClass(spriteClassBeforeMove);
                };
            let spriteAfterMove = function(){
                if (!spriteClassAfterMove){ return; }
                $(this).addClass(spriteClassAfterMove);
                };

            // Change all team sprite frames and directions where needed
            $cursorSprite.attr('data-frame', '01');
            spriteBeforeMove.call($cursorSprite);
            $playerSprites.each(function(index){
                let $sprite = $(this);
                if ($sprite.is('.disabled')){ return; }
                let dataDir = $sprite.attr('data-dir') || 'right';
                if (goingLeft && dataDir !== 'left'){ $sprite.attr('data-dir', 'left'); }
                else if (goingRight && dataDir !== 'right'){ $sprite.attr('data-dir', 'right'); }
                let dataFrame = spriteClassAfterMove !== 'sliding' ? (playerFrames[index % playerFrames.length] || '00') : '07';
                $sprite.attr('data-frame', dataFrame);
                spriteBeforeMove.call($sprite);
                });
            $robotSprites.each(function(index){
                let $sprite = $(this);
                if ($sprite.is('.disabled')){ return; }
                let dataDir = $sprite.attr('data-dir') || 'right';
                if (goingLeft && dataDir !== 'left'){ $sprite.attr('data-dir', 'left'); }
                else if (goingRight && dataDir !== 'right'){ $sprite.attr('data-dir', 'right'); }
                let dataFrame = spriteClassAfterMove !== 'sliding' ? (robotFrames[index % robotFrames.length] || '00') : '07';
                $sprite.attr('data-frame', dataFrame);
                spriteBeforeMove.call($sprite);
                });

            // Move the team sprites in such a way that they point in the direction they're facing
            // We do this by first moving the player sprite(s) to the extreme edge of the panel (top/bottom/left/right/top-right/bottom-left/etc.)
            // Then we can loop through the others in order and position them relative to the player sprite (only align robot sprites to first player)
            let targetX, targetY, targetZ;
            if (_mapEffects.usePerspective === true){
                let targetOffset = _self.getLayerTileSpriteOffset(thisNewCol, thisNewRow);
                targetX = targetOffset.left;
                targetY = targetOffset.top;
                targetZ = targetY + 1;
                } else {
                targetX = (thisNewCol - 1) * _mapTileSize[0];
                targetY = (thisNewRow - 1) * _mapTileSize[1];
                targetZ = targetY + 1;
                }
            //console.log('-> targetX =', targetX, '\n-> targetY =', targetY, '\n-> targetZ =', targetZ);
            let spacingX = Math.ceil(_mapTileSize[0] / 4);
            let spacingY = Math.ceil(_mapTileSize[1] / 6);
            let playerPositions = [];
            $playerSprites.each(function(index){
                let $sprite = $(this);
                let spriteWidth = parseInt($sprite.attr('data-size')) || 40;
                let spriteHeight = parseInt($sprite.attr('data-size')) || 40;
                let posX = targetX;
                let posY = targetY;
                // use if instead of if/else to support combination directions
                if (goingRight){ posX = targetX + _mapTileSize[0] - spriteWidth - _mapTileSizeOffset[0]; } // align-right
                else if (goingLeft){ posX = targetX + _mapTileSizeOffset[0]; } // align-left
                else { posX = targetX + Math.ceil((_mapTileSize[0] - spriteWidth) / 2); } // align-center
                if (goingUp){ posY = (targetY + _mapTileSizeOffset[1]) - (_mapSpriteSizeOffset[1] / 1); } // align-top
                else if (goingDown){ posY = targetY + _mapTileSize[1] - spriteHeight - _mapTileSizeOffset[1]; } // align-bottom
                else { posY = targetY + Math.ceil((_mapTileSize[1] - spriteHeight) / 2) - (_mapSpriteSizeOffset[1] / 2); } // align-middle
                playerPositions.push([posX, posY]);
                $sprite.animate({left: posX + 'px', top: posY + 'px', zIndex: (posY + 10) }, teamRushDuration, spriteAfterMove);
                });
            let firstPlayerPosition = playerPositions[0] || [targetX, targetY];
            let robotSpriteKey = 0;
            $robotSprites.each(function(index){
                let $sprite = $(this);
                let spriteWidth = parseInt($sprite.attr('data-size')) || 40;
                let spriteHeight = parseInt($sprite.attr('data-size')) || 40;
                let posKey = robotSpriteKey++;
                let posX = firstPlayerPosition[0];
                let posY = firstPlayerPosition[1] - 4;
                let horzSpacing = Math.ceil(spacingX * (posKey + 1));
                let vertSpacing = Math.ceil(spacingY * (posKey + 1));
                if (goingHorz && goingVert){ horzSpacing = Math.ceil(horzSpacing / 2); }
                if (goingRight){ posX -= horzSpacing; } // align-right
                else if (goingLeft){ posX += horzSpacing; } // align-left
                if (goingUp){ posY += vertSpacing; } // align-top
                else if (goingDown){ posY -= vertSpacing; } // align-bottom
                $sprite.animate({left: posX + 'px', top: posY + 'px', zIndex: (posY + 10) }, teamRushDuration, spriteAfterMove);
                });
            // Make sure we put the cursor sprite lower (in z-index) than the team so it's like it's hiding
            //console.log('-> $otherSprites =', $otherSprites);
            let lowestOtherSpriteZ = (function($otherSprites){
                let lowestZ = null;
                $otherSprites.each(function(){
                    let $sprite = $(this);
                    let spriteZ = parseInt($sprite.css('zIndex'));
                    //console.log('-> $sprite(' + $sprite.attr('class') + ') zIndex =', spriteZ);
                    if (lowestZ === null || spriteZ < lowestZ){ lowestZ = spriteZ; }
                    });
                return lowestZ !== null ? lowestZ : 1;
                })($otherSprites);
            //console.log('-> lowestOtherSpriteZ =', lowestOtherSpriteZ);
            $cursorSprite.attr('data-frame', '01').css({zIndex:(lowestOtherSpriteZ - 1)});
            setTimeout(function(){ spriteAfterMove.call($cursorSprite); }, Math.ceil(teamRushDuration * 0.8));
            // If we need to do a slide animation as well, do it after everything else
            if (willSlideHorizontally){
                let rushDuration = Math.ceil(teamRushDuration * 1.6);
                setTimeout(function(){
                    let $sprites = $cursorSprite.add($playerSprites).add($robotSprites);
                    let offsetDst = (_mapTileSize[0] * 2);
                    let offsreenCss = {}; //{left: (willSlideDirection === 'left' ? (targetX - offsetDst) : (targetX + offsetDst)) + 'px'};
                    if (willSlideDirection === 'left'){ offsreenCss.left = (targetX - offsetDst); }
                    else if (willSlideDirection === 'right'){ offsreenCss.left = (targetX + offsetDst); }
                    else if (willSlideDirection === 'up'){ offsreenCss.top = (targetY - offsetDst); }
                    else if (willSlideDirection === 'down'){ offsreenCss.top = (targetY + offsetDst); }
                    if (offsreenCss.top){ offsreenCss.zIndex = offsreenCss.top - 1; }
                    $playerSprites.attr('data-frame', '07'); $robotSprites.attr('data-frame', '07');
                    //$sprites.animate(offsreenCss, Math.ceil(teamRushDuration * 3.2));
                    $sprites.each(function(){
                        rushDuration = Math.ceil(rushDuration * 1.2);
                        $(this).animate(offsreenCss, rushDuration);
                        });
                    }, rushDuration);
                }
            };

        // Define an inline function to redirect to the portal if needed
        let redirectToLocation = function(){
            //console.log('%c' + 'redirectToLocation()', 'color: cyan;');
            if (_self.worldIsBusy()){ return; }
            if (!stillAtPosition() || otherMenusActiveNow()){ return; }
            if (autoRedirectSound){ _self.playSoundEffect(autoRedirectSound); }
            //if (autoRedirectEffect){ applyTeamEffect(autoRedirectEffect); }
            //if (autoRedirectAnimation){ applyTeamAnimation(autoRedirectAnimation); }
            if (autoRedirectURL){
                _self.incZoomLevel();
                $thisWorld.addClass('busy');
                //console.log('we should block input now...');
                _world.isBusyWith.redirectToLocation = true;
                _self.saveWorldState(function(){
                    //if (_self.worldIsBusy()){ return; }
                    if (!stillAtPosition() || otherMenusActiveNow()){ return; }
                    else { _self.resetZoomLevel(); }
                    _self.incZoomLevel();
                    $thisWorld.addClass('loading');
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
                $actionDropdownWrapper.empty().html(actionAreaMarkup);
                }

            // Add the buttons to the sidebar area so that they are out-of-the-way
            $sideButtonsWrapper.empty().html(sideButtonsMarkup);

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
                        let activeRobots = Object.values(_worldPlayerTeam);
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
                        $thisWorld.addClass('busy');
                        _self.incZoomLevel();
                        _self.saveWorldState(function(){
                            _self.incZoomLevel();
                            $thisWorld.addClass('loading');
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
                            _self.incZoomLevel();
                            $thisWorld.addClass('busy');
                            _self.saveWorldState(function(){
                                _self.incZoomLevel();
                                $thisWorld.addClass('loading');
                                window.location.href = portalHref;
                                _self.incZoomLevel();
                                }, true, false);
                            $thisWorld.animate({opacity: 0}, 1200, function(){
                                $thisWorld.addClass('hidden');
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
                            if (!layerTilesIndex || !terrainTilesIndex){ console.error('-> layerTilesIndex or terrainTilesIndex not found, cannot set terrain!'); return false; }
                            let terrainSpriteData = mapTilesIndex[terrainName] || false;
                            if (!terrainSpriteData){ console.error('-> terrainSpriteData not found for terrain', terrainName, ', cannot set terrain!'); return false; }
                            //console.log('-> terrainSpriteData =', terrainSpriteData);
                            let terrainSpriteOffset = _self.getClonedObject(terrainSpriteData[0]); // clone the terrain sprite offset array
                            let terrainSpriteSize = _self.getClonedObject(terrainSpriteData[1]); // clone the terrain sprite size array
                            let terrainSpriteAttrs = _self.getClonedObject(terrainSpriteData[2]); // clone the terrain sprite attributes object
                            //console.log('-> terrainSpriteOffset =', terrainSpriteOffset);
                            //console.log('-> terrainSpriteSize =', terrainSpriteSize);
                            //console.log('-> terrainSpriteAttrs =', terrainSpriteAttrs);
                            //let terrainIsVoid = terrainSpriteAttrs.isVoid ? true : false;
                            //let terrainIsWater = terrainSpriteAttrs.isWater ? true : false;
                            let terrainIsWalkable = terrainSpriteAttrs.isWalkable ? true : false;
                            let terrainHasGrid = terrainIsWalkable ? true : false;
                            //console.log('-> layerTilesIndex =', layerTilesIndex);
                            //console.log('-> terrainTilesIndex =', terrainTilesIndex);
                            //console.log('-> terrainSpriteData =', terrainSpriteData);
                            //console.log('-> terrainIsWalkable =', terrainIsWalkable);
                            let groupsIndex = _config.mapGroupsIndex;
                            let groupTiles = groupsIndex[groupName] || false;
                            //console.log('-> groupsIndex =', groupsIndex);
                            //console.log('-> groupTiles =', groupTiles);
                            if (!groupsIndex || !groupTiles){ console.error('-> groupsIndex not found, cannot set terrain!'); return false; }
                            for (let i = 0; i < groupTiles.length; i++){
                                let tileKey = groupTiles[i];
                                let tileData = terrainTilesIndex[tileKey] || false;
                                if (typeof terrainTilesIndex[tileKey] === 'undefined'){ console.error('-> tile data not found for tile', tileKey, ', cannot set terrain!'); continue; }
                                //console.log('-> setting terrain for tile', tileKey, 'to', terrainName, '\n-> w/ original tileData:', _self.getClonedObject(tileData));
                                tileData.sprite[1] = terrainName;
                                tileData.sprite[2] = [terrainSpriteOffset[0], terrainSpriteOffset[1]];
                                tileData.sprite[3] = [terrainSpriteSize[0], terrainSpriteSize[1]];
                                tileData.walkable = terrainIsWalkable;
                                tileData.effects.grid = terrainHasGrid;
                                tileData.dirty = true;
                                terrainTilesIndex[tileKey] = tileData; // sync the tile data back to the index
                                //console.log('-> added terrainTilesIndex[' + tileKey + ']', '\n-> w/ new tileData:', tileData);
                                }
                            layerTilesIndex['terrain'] = terrainTilesIndex; // sync the layer tiles index with the new terrain tiles index
                            _world.layerTilesIndex = layerTilesIndex; // sync the world state with the new layer tiles index
                            //console.log('-> updated _world.layerTilesIndex =', _world.layerTilesIndex);
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
            let hoverActionButton = function(e){
                _self.playSoundEffect('icon-hover');
                hoverOverviewObject.call(this, e);
                };
            let unhoverActionButton = function(e){
                unhoverOverviewObject.call(this, e);
                };

            // Bind click events to the newly created action buttons in the dropdown
            $('.button[data-action]', $sideButtons).bind('mouseenter', hoverActionButton);
            $('.button[data-action]', $sideButtons).bind('mouseleave', unhoverActionButton);
            $('.button[data-action]', $sideButtons).bind('click', onActionButtonClick);

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
            if (!autoRedirect){ return true; }
            }

        // If a redirect was requested, this is where we exit actually
        if (autoRedirect){
            if (_selfRef.zoomRedirectTimeout){ clearTimeout(_selfRef.zoomRedirectTimeout); }
            _selfRef.zoomRedirectTimeout = setTimeout(redirectToLocation, (zoomTimeoutDuration * timeoutMultiplier));
            if (!showActionArea){ return true; }
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
    getEventsAtPosition(searchPosition, searchDirection, searchRadius, includeLocked){
        //console.log('%c' + 'mmrpgWorldMap.getEventsAtPosition(searchPosition:' + searchPosition + ', searchRadius:' + searchRadius + ')', 'color: magenta;');
        if (!searchPosition || (typeof searchPosition !== 'string' && !Array.isArray(searchPosition))){ console.error('getEventsAtPosition() missing or invalid searchPosition!'); return false; }
        if (!searchDirection || (typeof searchDirection !== 'string' && !Array.isArray(searchDirection))){ console.error('getEventsAtPosition() missing or invalid searchDirection!'); return false; }
        searchPosition = typeof searchPosition !== 'string' ? searchPosition.join('-') : searchPosition; // join if provided as array
        searchDirection = typeof searchDirection !== 'string' ? searchDirection.join('-') : searchDirection; // join if provided as array
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
        // Now that we have our events, we need to do some serious sorting given the direction we're facing
        // Most important is the first event, so decide which panel we're "facing" given direction and work out way outward
        let searchPositionXY = searchPosition.split('-').map(function(v){ return parseInt(v); });
        let searchDirectionXY = searchDirection.split('-').map(function(v){ return v.toLowerCase(); });
        let firstEventPositionXY = [];
        if (searchDirectionXY.indexOf('up') !== -1){ firstEventPositionXY[1] = searchPositionXY[1] - 1; }
        else if (searchDirectionXY.indexOf('down') !== -1){ firstEventPositionXY[1] = searchPositionXY[1] + 1; }
        else { firstEventPositionXY[1] = searchPositionXY[1]; }
        if (searchDirectionXY.indexOf('left') !== -1){ firstEventPositionXY[0] = searchPositionXY[0] - 1; }
        else if (searchDirectionXY.indexOf('right') !== -1){ firstEventPositionXY[0] = searchPositionXY[0] + 1; }
        else { firstEventPositionXY[0] = searchPositionXY[0]; }
        let firstEventPosition = firstEventPositionXY.join('-');
        //console.log('-> searchPosition =', searchPosition);
        //console.log('-> searchDirection =', searchDirection);
        //console.log('-> searchPositionXY =', searchPositionXY);
        //console.log('-> searchDirectionXY =', searchDirectionXY);
        //console.log('-> firstEventPositionXY =', firstEventPositionXY);
        //console.log('-> firstEventPosition =', firstEventPosition);
        // Now use the above (especially the firstEventPositionXY) to sort events by how close they are to that position
        eventsAtPosition.sort(function(a, b){
            let aPositionXY = a.position.split('-').map(function(v){ return parseInt(v); });
            let bPositionXY = b.position.split('-').map(function(v){ return parseInt(v); });
            let aDeltaX = Math.abs(aPositionXY[0] - firstEventPositionXY[0]);
            let aDeltaY = Math.abs(aPositionXY[1] - firstEventPositionXY[1]);
            let bDeltaX = Math.abs(bPositionXY[0] - firstEventPositionXY[0]);
            let bDeltaY = Math.abs(bPositionXY[1] - firstEventPositionXY[1]);
            let aDelta = aDeltaX + aDeltaY;
            let bDelta = bDeltaX + bDeltaY;
            return aDelta - bDelta;
            });
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
                let soundEffectFunction = null;
                if (typeof top.mmrpg_play_sound_effect !== 'undefined'){ soundEffectFunction = top.mmrpg_play_sound_effect; }
                else if (typeof self.mmrpg_play_sound_effect !== 'undefined'){ soundEffectFunction = self.mmrpg_play_sound_effect; }
                if (soundEffectFunction){ soundEffectFunction(soundName, options); }
                else { console.warn('mmrpgWorldMap.playSoundEffect() unable to play sound effect "' + soundName + '" because mmrpg_play_sound_effect is not defined!'); }
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
        let _elements = _self.elements;
        let _userId = _config.userId;
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldButtons = _world.buttons;
        let _worldSwitches = _world.switches;
        let _worldItems = _world.items;
        let _worldAbilities = _world.abilities;
        let _worldSymbols = _world.symbols;
        let $thisWorld = _elements.world;
        let lastPlayer = _worldPlayer.token;
        let lastPlayerTeam = _worldPlayer.team;
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
            lastPlayerTeam,
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
        // loop through all the world data fields and json encode them for transport
        //console.log('-> raw worldData:', worldData);
        let worldDataKeys = Object.keys(worldData);
        for (let i = 0; i < worldDataKeys.length; i++){
            let key = worldDataKeys[i];
            if (typeof worldData[key] === 'object'
                && worldData[key] !== null){
                worldData[key] = JSON.stringify(worldData[key]);
                }
            }
        //console.log('-> compressed worldData for saving:', worldData);
        // Now we can collect the callback and trigger the save etc.
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
        $thisWorld.addClass('saving');
        $.ajax({
            url: 'world.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'save', world_data: worldData },
            success: function(response){
                //console.log('%c' + '... World State Saved!', 'color: green;');
                //console.log('saveWorldState() returned successfully! w/', '\n-> response:', response);
                _selfRef._busy = false;
                //$thisWorld.removeClass('saving');
                triggerSaveCallbacks({response});
                if (pullEvents){ _self.triggerWindowEventsPull(0); }
                if (_selfRef._cleanup){ clearTimeout(_selfRef._cleanup); }
                _selfRef._cleanup = setTimeout(function(){ $thisWorld.removeClass('saving'); }, 1000);
                },
            error: function(xhr, status, error){
                //console.log('%c' + '... World State Not Saved!', 'color: red;');
                console.error('saveWorldState() failed to save world state! w/', '\n-> status:', status, '\n-> error:', error);
                _selfRef._busy = false;
                //$thisWorld.removeClass('saving');
                triggerSaveCallbacks({xhr, status, error});
                if (pullEvents){ _self.triggerWindowEventsPull(0); }
                if (_selfRef._cleanup){ clearTimeout(_selfRef._cleanup); }
                _selfRef._cleanup = setTimeout(function(){ $thisWorld.removeClass('saving'); }, 1000);
                }
            });
        return;
        }

    // Quick function for intentionally waiting for a given amount of time (in milliseconds)
    wait(ms){
        return new Promise(resolve => setTimeout(resolve, ms));
        }

    // Quick function for queuing a worldReady event to run when things are loaded
    onWorldReady(callback){
        //console.log('%c' + 'mmrpgWorldMap.onWorldReady(callback:' + (callback ? typeof callback : 'false') + ')', 'color: magenta;');
        if (!callback || typeof callback === 'undefined'){ console.error('onWorldReady() missing required callback function!'); return false; }
        else if (typeof callback !== 'function'){ console.error('onWorldReady() callback provided is not a function!'); return false; }
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let xRef = _self.onWorldReady;
        let xQueue = xRef._queue || [];
        xQueue.push(callback);
        xRef._queue = xQueue;
        _self.triggerWorldReadyEvents();
        return;
        }

    // Quick function for triggering any worldReady events that have been queued up
    triggerWorldReadyEvents(){
        //console.log('%c' + 'mmrpgWorldMap.triggerWorldReadyEvents()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        if (!_world.hasLoaded || !_world.isReady){ return false; }
        //console.log('%c' + '~mmrpgWorldMap.triggerWorldReadyEvents()', 'color: magenta;');
        let xRef = _self.onWorldReady;
        let xQueue = xRef._queue || [];
        if (!xQueue.length){ return false; }
        //console.log('-> triggering ' + xQueue.length + ' worldReady events ...');
        // execute one after another, passing _self as "this", and making sure first returns before next executes
        let returns = [];
        do {
            let callback = xQueue.shift();
            //console.log('-> executing worldReady event #' + (xRef._queue.length + 1) + ' of ' + (xRef._queue.length + 1));
            returns.push(callback.call(_self));
            //console.log('-> remaining worldReady events:', xRef._queue.length);
            } while (xRef._queue.length);
        return returns;
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
                _self.incZoomLevel();
                $thisWorld.addClass('busy');
                _self.saveWorldState(function(){
                    //_self.incZoomLevel();
                    _self.resetZoomLevel();
                    _self.animateZoomToMax(4, 0.25, 600);
                    $thisWorld.addClass('loading');
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

    // Quick function for scaling an array of values to a new min/max range
    scaleArrayToRange(values, newMin, newMax, rounded){
        //console.log('%c' + 'mmrpgWorldMap.scaleArrayToRange(values, ' + newMin + ', ' + newMax + ')', 'color: magenta;');
        if (!Array.isArray(values) || !values.length){ console.error('scaleArrayToRange() missing or invalid values array!'); return false; }
        if (typeof newMin !== 'number' || isNaN(newMin)){ console.error('scaleArrayToRange() missing or invalid newMin!'); return false; }
        if (typeof newMax !== 'number' || isNaN(newMax)){ console.error('scaleArrayToRange() missing or invalid newMax!'); return false; }
        rounded = (typeof rounded === 'boolean' ? rounded : false);
        const oldMin = Math.min(...values);
        const oldMax = Math.max(...values);
        const oldRange = oldMax - oldMin;
        const newRange = newMax - newMin;
        if (oldRange === 0) { return values.map(() => (newMin + newMax) / 2); }
        let range = values.map(value => { return newMin + (value - oldMin) / oldRange * newRange; });
        if (rounded){ range = range.map(value => { return Math.round(value); }); }
        return range;
        }

    // Quick function for normalizing an array of integer values at any min/max range into a 0.00 - 1.00 scale instead
    translateValueRange(baseValues, newMin, newMax, roundResults){
        //console.log('%c' + 'mmrpgWorldMap.translateValueRange(baseValues, ' + newMin + ', ' + newMax + ', ' + roundResults + ')', 'color: magenta;');
        if (!Array.isArray(baseValues) || !baseValues.length){ console.error('translateValueRange() missing or invalid baseValues array!'); return false; }
        if (typeof newMin !== 'number' || isNaN(newMin)){ console.error('translateValueRange() missing or invalid newMin!'); return false; }
        if (typeof newMax !== 'number' || isNaN(newMax)){ console.error('translateValueRange() missing or invalid newMax!'); return false; }
        roundResults = (typeof roundResults === 'boolean' ? roundResults : false);
        const baseSum = baseValues.reduce((a, b) => a + b, 0);
        const newRange = newMax - newMin;
        let basePercents = baseValues.map(value => { return (value / baseSum); });
        let translatedValues = basePercents.map(percent => { return (newMin + (percent * newRange)); });
        if (roundResults){ translatedValues = translatedValues.map(value => { return Math.round(value); }); }
        return translatedValues;
        }

    // Quick function for getting a robot's rounded energy percent value
    getRoundedPercent(baseValue, maxValue){
        let roundedPercent = Math.round((baseValue / maxValue) * 100);
        if (roundedPercent === 100 && baseValue < maxValue){ roundedPercent -= 1; }
        return roundedPercent;
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

    // Quick function for settings a robot's current energy amount to a specific value but without all the effects
    setRobotEnergy(robotString, newEnergy){
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

    // Quick function for settings a robot's current weapons amount to a specific value but without all the effects
    setRobotWeapons(robotString, newEnergy){
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

    // Define a function for calculating a robot's current stat value given its base and current mods/stages its been raised/lowered
    calculateRobotStat(baseValue, modValue){
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

    // Define a quick method for getting the array of player item quantities, optionally merging token groups into single values
    getPlayerItemQuantities(mergeGroups, excludeEquipped){
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

    // Define a quick method for getting the current items (displayed) quantity in the player's inventory
    getPlayerItemQuantity(itemToken, includeEquipped){
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
        };

    // Quick function for giving a given robot a new hold item and then optionally playing a sound effect
    giveRobotItem(robotString, itemToken, playSound, playAnimation){
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

    // Quick function for adding a given storage robot to the team and then optionally playing a sound effect
    addTeamRobot(robotString, playSound, playAnimation){
        //console.log('%c' + 'mmrpgWorldMap.addTeamRobot(robot:' + robotString + ', sound:' + playSound + ', animate:' + playAnimation + ')', 'color: magenta;');
        if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('addTeamRobot() missing required robotString!'); return false; }
        if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
        if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
        // Collect references to world objects
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldPlayerTeam = _worldPlayer.team;
        let _worldPlayerRobots = _worldPlayer.robots;
        let _worldCallbacks = _world.callbacks;
        // If this robot is already actually on the team, return now
        if (_worldPlayerTeam.indexOf(robotString) !== -1){
            console.warn('addTeamRobot() called but robot ' + robotString + ' is already on the team!');
            return false;
            }
        // Break the robot string into ID and token and collect its info
        let _indexes = _self.indexes;
        let _mmrpgRobotsIndex = _indexes.robots;
        let _playerRobotsIndex = _config.playerRobotsIndex;
        let robotId = parseInt(robotString.split('_')[0]) || false;
        let robotToken = robotString.split('_')[1] || false;
        let robotInfo = _mmrpgRobotsIndex[robotToken] || false;
        let robotData = _worldPlayerRobots[robotString] || false;
        if (!robotInfo){ console.error('addTeamRobot() could not find robot info for robot ' + robotToken + '!'); return false; }
        if (!robotData){ robotData = _self.getClonedObject(_playerRobotsIndex[robotString]); _worldPlayerRobots[robotString] = robotData; }
        //console.log('-> robotString =', robotString);
        //console.log('-> robotId =', robotId, '-> robotToken =', robotToken);
        //console.log('-> robotInfo =', robotInfo);
        //console.log('-> robotData =', robotData);
        // Add this robot's string to the team array first
        //console.log('-> old _worldPlayerTeam =', _worldPlayerTeam.join(', '));
        _worldPlayerTeam.push(robotString);
        _worldPlayerTeam = Object.values(_worldPlayerTeam);
        //console.log('-> new _worldPlayerTeam =', _worldPlayerTeam.join(', '));
        _worldPlayer.team = _worldPlayerTeam;
        //console.log('-> _worldPlayer.team =', _worldPlayer.team);
        // Collect a reference to this robot's element in the overview panel
        let $robotsOverview = _elements.robotsOverview;
        let $teamRobotsDiv = $robotsOverview.find('.team-robots');
        let $storageRobotsDiv = $robotsOverview.find('.storage-robots');
        let $selectedStorageRobot = $storageRobotsDiv.find('.team-robot[data-robot="' + robotString + '"]');
        if (!$selectedStorageRobot || !$selectedStorageRobot.length){ console.warn('addTeamRobot() could not find storage robot div for ' + robotString + '!'); return false; }
        let $robotDetailsDiv = $robotsOverview.find('.storage-details[data-robot="' + robotString + '"]');
        let $detailsImage = $robotDetailsDiv.find('.image');
        let $detailsImageSprite = $detailsImage.find('> .sprite.robot');
        // Add the robot to the team view so it's visible to the player
        let $newTeamRobot = $('<div class="team-robot">' + $selectedStorageRobot.html() + '</div>');
        $newTeamRobot.attr('data-robot', robotString).attr('data-status', robotData.energyRating);
        $teamRobotsDiv.attr('data-team-size', _worldPlayerTeam.length);
        $('> .wrapper', $teamRobotsDiv).append($newTeamRobot);
        // Re-collect a reference to make sure it was actually created properly
        $newTeamRobot = $teamRobotsDiv.find('.team-robot[data-robot="' + robotString + '"]');
        if (!$newTeamRobot || !$newTeamRobot.length){ console.warn('addTeamRobot() could not create team robot div for ' + robotString + '!'); return false; }
        // Add the current class from the storage robot and update the frame
        $newTeamRobot.addClass('selected');
        $newTeamRobot.find('.icon > .sprite.robot').attr('data-dir', 'right').attr('data-frame', '01');
        $selectedStorageRobot.addClass('current').removeClass('selected');
        $selectedStorageRobot.find('.icon > .sprite.robot').attr('data-dir', 'left').attr('data-frame', '01');
        // Add a robot-given class to this robot to show it being effected by the action
        if (playSound){ _self.playSoundEffect('bounce-sound'); }
        if (playAnimation){
            // Add the robot-added animation class for the avatar then auto-remove later
            $newTeamRobot.addClass('robot-added');
            setTimeout(function(){ $newTeamRobot.removeClass('robot-added'); }, 2000);
            // Now shift the background while making the robot appear to slide into the team view
            let _selfRef = _self.addTeamRobot, _relRef = _self.removeTeamRobot;
            if (_relRef.callback){ clearTimeout(_relRef.callback); delete _relRef.callback; }
            if (_selfRef.callback){ clearTimeout(_selfRef.callback); delete _selfRef.callback; }
            _selfRef.callback = setTimeout(function(){
                if (!_selfRef.callback){ return; }
                $detailsImage.addClass('animate').removeClass('inactive');
                $detailsImageSprite.attr('data-frame', '07');
                setTimeout(function(){
                    if (!_selfRef.callback){ return; }
                    $detailsImageSprite.attr('data-frame', '08');
                    setTimeout(function(){
                        if (!_selfRef.callback){ return; }
                        $detailsImageSprite.attr('data-frame', '00');
                        }, 1000);
                    }, 600);
                }, 10);
            }
        // Add the robot to the world map with the others on this team
        let $canvasMap = $('#map', $thisCanvas);
        let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap);
        let $spriteObjectsLayer = $('.layer[data-layer="sprites/objects"]', $canvasMap);
        let $teamPlayerSprite = $spriteObjectsLayer.find('.sprite[data-sprite="team-player"]').first();
        let $newRobotSprite = null, newSpriteMarkup = _self.getRobotSpriteMarkup(robotString);
        if (newSpriteMarkup){
            let newRobotOffsets = {
                top: (parseInt($teamPlayerSprite.css('top')) - 4) + 'px',
                left: (parseInt($teamPlayerSprite.css('left')) - 8) + 'px',
                zIndex: (parseInt($teamPlayerSprite.css('z-index')) - 4)
                };
            //console.log('newRobotOffsets =', newRobotOffsets);
            $newRobotSprite = $(newSpriteMarkup);
            $newRobotSprite.addClass('team bounce').css(newRobotOffsets);
            $newRobotSprite.attr('data-sprite', 'team-robot').attr('data-id', robotId).attr('data-token', robotToken);
            $spriteObjectsLayer.append($newRobotSprite);
            _elements.teamSprites = $('.sprite[data-sprite^="team-"]', $canvasMap);
            }
        // Trigger a save of the world state to persist this change
        _self.saveWorldState();
        // Return true on success
        return true;
        }

    // Quick function for removing a given robot from the team and then optionally playing a sound effect
    removeTeamRobot(robotString, playSound, playAnimation){
        //console.log('%c' + 'mmrpgWorldMap.removeTeamRobot(robot:' + robotString + ', sound:' + playSound + ', animate:' + playAnimation + ')', 'color: magenta;');
        if (!robotString || typeof robotString !== 'string' || !robotString.length){ console.error('removeTeamRobot() missing required robotString!'); return false; }
        if (typeof playSound !== 'boolean'){ playSound = true; } // default to true if not provided
        if (typeof playAnimation !== 'boolean'){ playAnimation = true; } // default to true if not provided
        // Collect references to world objects
        let _self = this;
        let _config = _self.config;
        let _elements = _self.elements;
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldPlayerTeam = _worldPlayer.team;
        let _worldPlayerRobots = _worldPlayer.robots;
        let _worldCallbacks = _world.callbacks;
        // If this robot isn't actually on the team, return now
        if (_worldPlayerTeam.indexOf(robotString) === -1){
            console.warn('removeTeamRobot() called but robot ' + robotString + ' is not on the team!');
            return false;
            }
        // Break the robot string into ID and token and collect its info
        let _indexes = _self.indexes;
        let _mmrpgRobotsIndex = _indexes.robots;
        let robotKey = _worldPlayerTeam.indexOf(robotString);
        let robotId = parseInt(robotString.split('_')[0]) || false;
        let robotToken = robotString.split('_')[1] || false;
        let robotInfo = _mmrpgRobotsIndex[robotToken] || false;
        let robotData = _worldPlayerRobots[robotString] || false;
        if (!robotInfo){ console.error('removeTeamRobot() could not find robot info for robot ' + robotToken + '!'); return false; }
        if (!robotData){ console.error('removeTeamRobot() could not find robot info for robot ' + robotData + '!'); return false; }
        //console.log('-> robotString =', robotString);
        //console.log('-> robotKey =', robotKey, '-> robotId =', robotId, '-> robotToken =', robotToken);
        //console.log('-> robotInfo =', robotInfo);
        //console.log('-> robotData =', robotData);
        // Remove this robot's string from the team array first
        //console.log('-> old _worldPlayerTeam =', _worldPlayerTeam.join(', '));
        delete _worldPlayerTeam[robotKey];
        _worldPlayerTeam = Object.values(_worldPlayerTeam);
        //console.log('-> new _worldPlayerTeam =', _worldPlayerTeam.join(', '));
        _worldPlayer.team = _worldPlayerTeam;
        //console.log('-> _worldPlayer.team =', _worldPlayer.team);
        // Collect a reference to this robot's element in the overview panel
        let $robotsOverview = _elements.robotsOverview;
        let $teamRobotsDiv = $robotsOverview.find('.team-robots');
        let $selectedTeamRobot = $teamRobotsDiv.find('.team-robot[data-robot="' + robotString + '"]');
        if (!$selectedTeamRobot || !$selectedTeamRobot.length){ console.warn('removeTeamRobot() could not find team robot div for ' + robotString + '!'); return false; }
        let $storageRobotsDiv = $robotsOverview.find('.storage-robots');
        let $selectedStorageRobot = $storageRobotsDiv.find('.team-robot[data-robot="' + robotString + '"]');
        if (!$selectedStorageRobot || !$selectedStorageRobot.length){ console.warn('removeTeamRobot() could not find storage robot div for ' + robotString + '!'); return false; }
        let $robotDetailsDiv = $robotsOverview.find('.storage-details[data-robot="' + robotString + '"]');
        let $detailsImage = $robotDetailsDiv.find('.image');
        let $detailsImageSprite = $detailsImage.find('> .sprite.robot');
        // Remove the robot from the team view as that's not needed anymore
        $selectedTeamRobot.remove();
        $teamRobotsDiv.attr('data-team-size', _worldPlayerTeam.length);
        // Remove the current class from the storage robot and update the frame
        $selectedStorageRobot.removeClass('current').addClass('selected');
        $selectedStorageRobot.find('.icon > .sprite.robot').attr('data-dir', 'left').attr('data-frame', '00');
        // Add a robot-given class to this robot to show it being effected by the action
        if (playSound){ _self.playSoundEffect('bounce-sound'); }
        if (playAnimation){
            // Add the robot-removed animation class for the avatar then auto-remove later
            $selectedStorageRobot.addClass('robot-removed');
            setTimeout(function(){ $selectedStorageRobot.removeClass('robot-removed'); }, 2000);
            // Now shift the background while making the robot appear to slide away from the team
            let _selfRef = _self.removeTeamRobot, _relRef = _self.addTeamRobot;
            if (_relRef.callback){ clearTimeout(_relRef.callback); delete _relRef.callback; }
            if (_selfRef.callback){ clearTimeout(_selfRef.callback); delete _selfRef.callback; }
            _selfRef.callback = setTimeout(function(){
                if (!_selfRef.callback){ return; }
                $detailsImage.addClass('animate').addClass('inactive');
                $detailsImageSprite.attr('data-dir', 'right').attr('data-frame', '07');
                setTimeout(function(){
                    if (!_selfRef.callback){ return; }
                    $detailsImageSprite.attr('data-frame', '08');
                    setTimeout(function(){
                        if (!_selfRef.callback){ return; }
                        $detailsImageSprite.attr('data-dir', 'left');
                        setTimeout(function(){
                            if (!_selfRef.callback){ return; }
                            $detailsImageSprite.attr('data-frame', '00');
                            }, 400);
                        }, 600);
                    }, 600);
                }, 10);
            }
        // Remove this robot from the world map as it's no longer on the team
        let $canvasMap = $('#map', $thisCanvas);
        let $spritesLayer = $('.layer.sprites[data-layer]', $canvasMap);
        let $spriteObjectsLayer = $('.layer[data-layer="sprites/objects"]', $canvasMap);
        let $selectedMapRobot = $spriteObjectsLayer.find('.sprite[data-sprite="team-robot"][data-id="' + robotId + '"][data-token="' + robotToken + '"]');
        $selectedMapRobot.animate({opacity: 0}, 300, function(){ $selectedMapRobot.remove(); });
        _elements.teamSprites = $('.sprite[data-sprite^="team-"]', $canvasMap);
        // Trigger a save of the world state to persist this change
        _self.saveWorldState();
        // Return true on success
        return true;
        }

    // Quick function for giving a given robot a new hold item and then optionally playing a sound effect
    takeRobotItem(robotString, playSound, playAnimation){
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

    // Define a quick function for checking if a given item (by token) is a consumable
    itemIsConsumable(itemToken){
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
    itemIsHoldable(itemToken){
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

    // Define a quick function for checking if the given item (by token) is an event item
    itemIsEvent(itemToken){
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
    triggerItemPickup(itemEvent, zoomDelay, playSound){
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
        let _worldItemStates = _world.items;
        let _mmrpgPlayersIndex = _indexes.players;
        let _mmrpgRobotsIndex = _indexes.robots;
        let _mmrpgItemsIndex = _indexes.items;
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
        if (!numPlayerRobots || numPlayerRobots < 1){ console.error('triggerItemPickup() could not find any player robots!'); return false; }

        // Zoom the item sprite into the zoom layer so it's more visible to the player
        //console.log('-> zooming item sprite make it more visible');
        zoomDelay = typeof zoomDelay === 'number' ? zoomDelay : 1200; // default to sync with standard use-case
        setTimeout(function(){
            $itemEventSprite.addClass('zoom');
            $itemEventLayer.addClass('has-zoom');
            }, Math.ceil(zoomDelay / 3));

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
                    $itemEventSprite.animate({opacity: 0, filter: 'brightness(2)'}, zoomDelay, function(){ $itemEventSprite.remove(); });
                    }
                // Trigger a save of the world state to persist this change
                delete _world.isBusyWith.itemPickup;
                _self.saveWorldState();
                });
            }, (zoomDelay * 2));
        // Return true on success
        return true;
        }

    // Define a quick function for triggering a live ability pickup on the field (and any effects that may have
    triggerAbilityPickup(abilityEvent, zoomDelay, playSound){
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
            console.warn('addItemToInventory() would animate the item pickup now...'); // TODO: read the message
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

    // Define a quick function for getting the overview details for a given robot in the user's inventory
    getRobotDetailsForOverview(robotToken){
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

    // Define a quick function for getting the overview details for a given item in the user's inventory
    getItemDetailsForOverview(itemToken, targetSelected){
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
        let _userInputs = _inputs.userInputs
        let aButtonIcon = _userInputs.A.icon, bButtonIcon = _userInputs.B.icon;
        let xButtonIcon = _userInputs.X.icon, yButtonIcon = _userInputs.Y.icon;
        itemDetailsObject.actions = [];
        let showUseItem = itemKind === 'consumable' ? true : false;
        let showGiveItem = (itemKind === 'consumable' || itemKind === 'holdable') ? true : false;
        let showTakeItem = showGiveItem && itemIsEquipped ? true : false; if (showTakeItem){ showGiveItem = false; }
        let showDropItem = itemKind !== 'event' && itemQuantity > 0 ? true : false;
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
    getItemDetailsMarkupForOverview(itemToken, targetSelected){
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

    // Define a function for getting the id-token for the currently selected overview robot, if any
    // TODO: we should store and retrieve this value somewhere local instead of grabbing it from the DOM every time
    getSelectedRobotInOverview(returnObject){
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

    // Define a quick function for getting the overview details for a given ability in the user's inventory
    getAbilityDetailsForOverview(abilityToken, targetSelected){
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
        let _userInputs = _inputs.userInputs
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
    getAbilityDetailsMarkupForOverview(abilityToken, targetSelected){
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

    // Define a quick function for getting the details markup for a given robot in the user's inventory
    getRobotDetailsMarkupForOverview(robotToken){
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
    replaceRobotDetailsInOverview($detailsDiv, robotToken, robotDetails){
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

    // Define a quick function for replacing the item details in an existing details div with new ones
    replaceItemDetailsInOverview($detailsDiv, itemToken, itemDetails){
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

    // Define a quick function for replacing the ability details in an existing details div with new ones
    replaceAbilityDetailsInOverview($detailsDiv, abilityToken, abilityDetails){
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

    // Define a quick function for grabbing the sprite markup of a robot given the token and optional sprite arguments
    getRobotSpriteMarkup(robotToken, spriteOptions){
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

    // Define a quick function for grabbing the sprite markup of an item given the token and optional sprite arguments
    getItemSpriteMarkup(itemToken, spriteOptions){
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

    // Define a quick function for grabbing the sprite markup of an ability given the token and optional sprite arguments
    getAbilitySpriteMarkup(abilityToken, spriteOptions){
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

    // Define some quick functions for getting type spans for certain object types
    getCustomNameSpan(customText, typeOrTypes){
        //console.log('%c' + 'mmrpgWorldMap.getCustomNameSpan(customText:' + customText + ', typeOrTypes:' + typeOrTypes + ')', 'color: magenta;');
        let spanTypes = 'none';
        if (typeof typeOrTypes === 'string' && typeOrTypes.length){ spanTypes = typeOrTypes; }
        else if (Array.isArray(typeOrTypes) && typeOrTypes.length){ spanTypes = typeOrTypes.join(' '); }
        return '<span class="type ' + spanTypes + '">' + (customText || 'Text') + '</span>';
        };
    getPlayerNameSpan(playerToken, customText){
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
    getRobotNameSpan(robotToken, customText){
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
        };
    getItemNameSpan(itemToken, customText){
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
    getAbilityNameSpan(abilityToken, customText){
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
        };

    // Define a quick function for queueing custom action-modal callbacks that should be auto-run after named events
    queueActionModalCallback(callbackName, callbackFunction){
        //console.log('%c' + 'mmrpgWorldMap.queueActionModalCallback(callbackName:' + callbackName + ', callbackFunction:' + typeof callbackFunction + ')', 'color: magenta;');
        if (!callbackName || typeof callbackName !== 'string' || !callbackName.length){ console.error('queueActionModalCallback() missing required callbackName!'); return; }
        if (!callbackFunction || typeof callbackFunction !== 'function'){ console.error('queueActionModalCallback() missing required callbackFunction!'); return; }

        // Collect references to world objects
        let _self = this;
        let _selfRef = _self.showActionModal;
        let _selfQueue = _selfRef.actionModalCallbacks || {};

        // Create the callback entry if not exists and then append the function
        if (typeof _selfQueue[callbackName] === 'undefined'){ _selfQueue[callbackName] = []; }
        _selfQueue[callbackName].push(callbackFunction);

        // Update references to world objects just-in-case
        _selfRef.actionModalCallbacks = _selfQueue;
        //console.log('_selfRef.actionModalCallbacks =', _selfRef.actionModalCallbacks);

        // Return true on success
        return true;
        }

    // Define a quick function for showing a generic action modal for items or abilities
    showActionModal(actionKind, actionToken, actionObjectToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showActionModal(actionKind:' + actionKind + ', actionToken:' + actionToken + ', actionObjectToken:' + actionObjectToken + ', targetRobotToken:' + targetRobotToken + ', configCustom: ' + typeof configCustom + ')', 'color: magenta;');
        if (!actionKind || typeof actionKind !== 'string' || !actionKind.length){ console.error('showActionModal() missing required actionKind!'); return; }
        if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showActionModal() missing required actionToken!'); return; }
        if (!actionObjectToken || typeof actionObjectToken !== 'string' || !actionObjectToken.length){ console.error('showActionModal() missing required actionObjectToken!'); return; }
        targetRobotToken = targetRobotToken && typeof targetRobotToken === 'string' && targetRobotToken.length ? targetRobotToken : null;
        configCustom = configCustom && typeof configCustom === 'object' && Object.keys(configCustom).length ? configCustom : {};
        //console.log('w/ configCustom =', configCustom);

        // Collect references to world objects
        let _self = this;
        let _selfRef = _self.showActionModal;
        let _config = _self.config;
        let _indexes = _self.indexes;
        let _elements = _self.elements;
        let $thisCanvas = _elements.canvas;
        let $canvasWrapper = $('> .wrapper', $thisCanvas);
        let $actionModal = _elements.actionModal;
        let $robotsOverview = _elements.robotsOverview;
        let $teamRobotsDiv = $robotsOverview.find('.team-robots');
        let $robotStorageBox = $robotsOverview.find('.storage-box[data-storage="robots"]');
        let $itemStorageBox = $robotsOverview.find('.storage-box[data-storage="items"]');
        let $abilityStorageBox = $robotsOverview.find('.storage-box[data-storage="abilities"]');
        let _world = _self.state;
        let _worldPlayer = _world.player;
        let _worldPlayerTeam = _worldPlayer.team;
        let _worldPlayerRobots = _worldPlayer.robots;
        let _worldPlayerItems = _worldPlayer.items;
        let _worldPlayerAbilities = _worldPlayer.abilities;
        let robotsOverviewAPI = _self.robotsOverviewAPI || {};
        let refreshDetailsPanel = robotsOverviewAPI.refreshDetailsPanel;
        let generateItemSelectButtonMarkup = _self.generateItemSelectButtonMarkup.bind(_self);
        let generateItemSelectPlaceholderMarkup = _self.generateItemSelectPlaceholderMarkup.bind(_self);
        let generateAbilitySelectButtonMarkup = _self.generateAbilitySelectButtonMarkup.bind(_self);
        let generateAbilitySelectPlaceholderMarkup = _self.generateAbilitySelectPlaceholderMarkup.bind(_self);

        // Initialize the queue if it has not been already
        let _selfQueue = _selfRef.actionModalCallbacks;
        if (!_selfQueue){ _selfQueue = {}; _selfRef.actionModalCallbacks = _selfQueue; }

        // Auto-queue any callbacks provided in the custom config before doing anything else
        let autoQueueCallbacks = ['onShow', 'onHide', 'onConfim', 'onCancel', 'onComplete'];
        for (var i = 0; i < autoQueueCallbacks.length; i++){
            let callbackName = autoQueueCallbacks[i], callbackFunction = configCustom[callbackName];
            if (typeof callbackFunction === 'function'){ _self.queueActionModalCallback(callbackName, callbackFunction); }
            }

        // Make a backup of values we need to be able to reset
        _selfRef.mapIsHiddenBackup = _world.mapIsHidden;

        // If a target robot token was provided, collect its info now
        let playerRobotInfo = null;
        let playerRobotName = '';
        if (targetRobotToken
            && typeof _worldPlayerRobots[targetRobotToken] !== 'undefined'){
            //console.log('--> target robot provided ...', targetRobotToken);
            playerRobotInfo = _worldPlayerRobots[targetRobotToken];
            //console.log('--> found playerRobotInfo =', playerRobotInfo);
            playerRobotName = playerRobotInfo.name || '[' + targetRobotToken + ']';
            }

        // Define template parameters for this modal to be updated as-needed
        let modalDetails = {};
        modalDetails.show = true;
        modalDetails.action = actionKind + '_' + actionToken;
        modalDetails.title = actionToken.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') + ' Action';
        modalDetails.subtitles = {};
        modalDetails.subtitles.forSelected = 'Selected ' + (actionKind === 'item' ? 'Item' : 'Ability') + (playerRobotName ? ' for <strong>' + playerRobotName + '</strong>' : '');
        modalDetails.subtitles.forCurrent = (playerRobotName ? '<strong>' + playerRobotName + '</strong>\'s ' : '') + 'Current ' + (actionKind === 'item' ? 'Items' : 'Abilities');
        modalDetails.subtitles.forTooltip = '';
        modalDetails.containers = {};
        modalDetails.containers.forSelected = '';
        modalDetails.containers.forCurrent = '';
        modalDetails.buttons = {};
        modalDetails.buttons.confirm = { action: 'confirm', text: 'Confirm', disabled: false, hidden: false };
        modalDetails.buttons.cancel = { action: 'cancel', text: 'Cancel', disabled: false, hidden: false };
        modalDetails.callbacks = {};
        modalDetails.callbacks.onShow = function(){};
        modalDetails.callbacks.onHide = function(){};
        modalDetails.callbacks.onConfirm = function(){};
        modalDetails.callbacks.onCancel = function(){};
        modalDetails.callbacks.onComplete = function(){};

        // Define a function for running a given callback if it exists
        let runModalCallback = function(callbackName, callbackArgs){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.runModalCallback(callbackName:' + callbackName + ', callbackArgs: ' + typeof callbackArgs + ')', 'color: magenta;');
            callbackArgs = typeof callbackArgs !== 'undefined' ? callbackArgs : null;
            //console.log('-> callbackArgs =', callbackArgs);
            let _modalCallbacks = typeof modalDetails.callbacks !== 'undefined' ? modalDetails.callbacks : {};
            let callbackFunction = _modalCallbacks[callbackName], callbackQueue = _selfQueue[callbackName], callbackReturns = 0;
            //console.log('-> _selfQueue =', _selfQueue);
            //console.log('-> _modalCallbacks =', _modalCallbacks);
            //console.log('-> callbackFunction =', callbackFunction);
            //console.log('-> callbackQueue =', callbackQueue);
            //console.log('-> callbackReturns =', callbackReturns);
            //if (callbackName === 'onConfirm'){ _self.playSoundEffect('link-click-action'); }
            //else if (callbackName === 'onCancel'){ _self.playSoundEffect('back-click'); }
            //else if (callbackName === 'onHide'){ _self.playSoundEffect('no-effect'); }
            if (typeof callbackFunction === 'function'){ callbackFunction(callbackArgs); callbackReturns++; }
            if (Array.isArray(callbackQueue)){
                for (var i = 0; i < callbackQueue.length; i++){
                    let queuedCallback = callbackQueue.shift();
                    //console.log('callbackQueue[', i, '] ... queuedCallback =', typeof queuedCallback, queuedCallback);
                    if (typeof queuedCallback === 'function'){ queuedCallback(callbackArgs); callbackReturns++; }
                    } }
            return callbackReturns;
            };

        // Define a reusable function for resetting the action modal
        let resetActionModal = function(){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.resetActionModal()', 'color: magenta;');
            let $content = $actionModal.find('.content');
            $actionModal.removeClass('active');
            $content.find('.title').html('');
            $content.find('.subtitle').html('');
            $content.find('.container').html('');
            $content.find('.button.clicked').removeClass('clicked');
            $content.find('.button.saving').removeClass('saving');
            return true;
            };

        // Define a reusable function for hiding the action modal
        let hideActionModal = function(alsoReset){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.hideActionModal()', 'color: magenta;');
            $actionModal.addClass('hidden');
            _world.mapIsHidden = _selfRef.mapIsHiddenBackup;
            _world.actionModalVisible = false;
            alsoReset = typeof alsoReset === 'boolean' ? alsoReset : false;
            if (alsoReset){ setTimeout(function(){ resetActionModal(); }, 600); }
            _self.playSoundEffect('no-effect');
            runModalCallback('onHide');
            return true;
            };

        // Define the action for the cancel button click
        let onCancelAction = function(action){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.onCancelAction(action:' + action + ')', 'color: magenta;');
            let $cancelButton = $actionModal.find('.content .actions .button[data-action="cancel"]');
            if ($cancelButton.length){ $cancelButton.addClass('clicked'); }
            hideActionModal(true);
            _self.playSoundEffect('back-click');
            runModalCallback('onCancel');
            return true;
            };

        // Define the action for the confirm button click
        let onConfirmAction = function(action){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.onConfirmAction(action:' + action + ')', 'color: magenta;');
            action = action.split('_');
            let actionKind = action[0];
            let actionToken = action[1];
            let $confirmButton = $actionModal.find('.content .actions .button[data-action="confirm"]');
            let $cancelButton = $actionModal.find('.content .actions .button[data-action="cancel"]');
            if ($confirmButton.length){ $confirmButton.html('<strong>Saving</strong>').addClass('clicked'); }
            if ($cancelButton.length){ $cancelButton.addClass('hidden'); }
            if (actionKind === 'robot'){ onConfirmRobotAction(actionToken); }
            else if (actionKind === 'item'){ onConfirmItemAction(actionToken); }
            else if (actionKind === 'ability'){ onConfirmAbilityAction(actionToken); }
            else { console.error('showActionModal.onConfirmAction() received invalid actionKind ' + actionKind + '!'); return false; }
            runModalCallback('onConfirm', {actionKind, actionToken});
            return true;
            };
        // Define the action and dependent functions for confirming an robot action
        let onConfirmRobotAction = function(action){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.onConfirmRobotAction(action:' + action + ')', 'color: magenta;');
            // TODO: maybe?
            return true;
            };
        // Define the action and dependent functions for confirming an item action
        let onConfirmItemAction = function(action){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.onConfirmItemAction(action:' + action + ')', 'color: magenta;');
            // TODO: maybe?
            return true;
            };
        // Define the action and dependent functions for confirming an ability action
        let clickTeamAbilityButton = function(button){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.clickTeamAbilityButton(button)', 'color: magenta;');
            let $button = $(button);
            let $container = $button.closest('.ability-list');
            //console.log('-> $button =', $button);
            //console.log('-> $container =', $container);
            if ($button.is('.disabled') || $button.is('[disabled]')){ return false; }
            let token = $button.attr('data-ability');
            //console.log('-> token =', token);
            if (!token || !token.length){ console.error('Ability select button clicked, but no ability token found!'); return false; }
            let isPlaceholder = token === 'ability' ? true : false;
            let index = _indexes.abilities;
            let info = !isPlaceholder ? index[token] : null;
            if (!isPlaceholder && (!info || typeof info === 'undefined')){ console.error('Ability select button clicked, but ability token ' + token + ' not found in index!'); return false; }
            //console.log('-> info =', info);
            if (!$button.is('.selected')){
                //console.log('--> change selected ability to', token);
                $container.addClass('has-selection');
                $('.team-ability[data-ability]', $container).removeClass('selected');
                $button.addClass('selected');
                } else {
                //console.log('--> deselect ability', token);
                $container.removeClass('has-selection');
                $('.team-ability[data-ability]', $container).removeClass('selected');
                }
            refreshAbilityModal();
            return true;
            };
        let onConfirmAbilityAction = function(action){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.onConfirmAbilityAction(action:' + action + ')', 'color: magenta;');
            $actionModal.addClass('busy');
            let abilitiesIndex = _indexes.abilities;
            let currentAbilities = playerRobotInfo.abilities;
            let maxAbilitiesPerRobot = _config.maxAbilitiesPerRobot;
            //console.log('--> currentAbilities =', currentAbilities);
            //console.log('--> maxAbilitiesPerRobot =', maxAbilitiesPerRobot);
            let $currentAbilitiesList = $actionModal.find('.container.for-current .ability-list.current');
            let $selectedAbilityButton = $currentAbilitiesList.find('.team-ability[data-ability].selected');
            let $abilitiesInStorage = $abilityStorageBox.find('.team-ability[data-ability]');
            let selectedAbilityToken = $selectedAbilityButton.length ? $selectedAbilityButton.attr('data-ability') : null;
            let selectedAbilitySlot = $selectedAbilityButton.length ? parseInt($selectedAbilityButton.attr('data-slot')) : null;
            let selectedAbilityID = selectedAbilityToken && abilitiesIndex[selectedAbilityToken] ? abilitiesIndex[selectedAbilityToken].id : null;
            //console.log('--> selectedAbilityToken =', selectedAbilityToken);
            //console.log('--> selectedAbilitySlot =', selectedAbilitySlot);
            //console.log('--> selectedAbilityID =', selectedAbilityID);
            let refreshEquippedAbilities = function(){
                //console.log('%c' + '--> refreshing equipped abilities in storage ...', 'color: magenta;');
                //console.log('----> currentAbilities =', currentAbilities);
                //console.log('----> $abilitiesInStorage =', $abilitiesInStorage.length, $abilitiesInStorage);
                $abilitiesInStorage.filter('.hidden').removeClass('equipped');
                $abilitiesInStorage.not('.hidden').each(function(){
                    let $abilityButton = $(this);
                    let abilityID = parseInt($abilityButton.attr('data-ability-id'));
                    let isEquipped = currentAbilities.includes(abilityID);
                    //console.log('--> abilityID =', abilityID, ', isEquipped =', isEquipped);
                    if (isEquipped){ $(this).addClass('equipped'); }
                    else { $(this).removeClass('equipped'); }
                    });
                };
            if (action === 'equip-ability'){
                //console.log('--> equipping new ability ...');
                let newAbilityToken = $actionModal.attr('data-action-token'); //actionObjectToken;
                let newAbilityID = abilitiesIndex[newAbilityToken] ? abilitiesIndex[newAbilityToken].id : null;
                let newAbilityMarkup = _self.generateAbilitySelectButtonMarkup(newAbilityToken, playerRobotInfo, {slot: selectedAbilitySlot});
                //console.log('--> newAbilityToken =', newAbilityToken);
                //console.log('--> newAbilityID =', newAbilityID);
                //console.log('--> currentAbilities(before) =', JSON.stringify(currentAbilities));
                // doesn't exist yet in ability list so we can just replace/insert at the selected slot
                if (currentAbilities.indexOf(newAbilityID) === -1){
                    currentAbilities[selectedAbilitySlot] = newAbilityID;
                    $selectedAbilityButton.replaceWith(newAbilityMarkup);
                    }
                // already there so user must want to swap positions of the two abilities
                else {
                    let existingAbilitySlot = currentAbilities.indexOf(newAbilityID);
                    currentAbilities[selectedAbilitySlot] = newAbilityID;
                    currentAbilities[existingAbilitySlot] = selectedAbilityID;
                    let $existingAbilityButton = $currentAbilitiesList.find('.team-ability[data-ability="' + newAbilityToken + '"]');
                    $existingAbilityButton.replaceWith(_self.generateAbilitySelectButtonMarkup(selectedAbilityToken, playerRobotInfo, {slot: existingAbilitySlot}));
                    $selectedAbilityButton.replaceWith(newAbilityMarkup);
                    }
                //console.log('--> currentAbilities(after) =', JSON.stringify(currentAbilities));
                let $newAbilityButton = $currentAbilitiesList.find('.team-ability[data-ability="' + newAbilityToken + '"]');
                let $abilityInStorage = $abilitiesInStorage.filter('[data-ability="' + newAbilityToken + '"].selected');
                $newAbilityButton.addClass('selected').addClass('saving');
                playerRobotInfo.abilities = currentAbilities;
                playerRobotInfo.abilitiesAdded = [newAbilityToken];
                _self.saveWorldState(function(){
                    _self.playSoundEffect('link-click-action');
                    setTimeout(function(){ $currentAbilitiesList.removeClass('has-selection'); }, 300);
                    setTimeout(function(){ $newAbilityButton.removeClass('selected'); }, 600);
                    setTimeout(function(){ hideActionModal(true); }, 900);
                    setTimeout(function(){ refreshEquippedAbilities(); $abilityInStorage.trigger('click'); }, 1000);
                    setTimeout(function(){ delete playerRobotInfo.abilitiesAdded; refreshDetailsPanel(); }, 7000);
                    });
                }
            else if (action === 'remove-ability'){
                //console.log('--> removing equipped ability ...');
                //console.log('--> currentAbilities(before) =', JSON.stringify(currentAbilities));
                delete currentAbilities[selectedAbilitySlot];
                currentAbilities = Object.values(currentAbilities);
                //console.log('--> currentAbilities(after) =', JSON.stringify(currentAbilities));
                $selectedAbilityButton.remove();
                let $remainingAbilityButtons = $('.team-ability[data-ability]', $currentAbilitiesList);
                $remainingAbilityButtons.each(function(index){ $(this).attr('data-slot', index); });
                for (var slotKey = $remainingAbilityButtons.length; slotKey < maxAbilitiesPerRobot; slotKey++){ $currentAbilitiesList.append(generateAbilitySelectPlaceholderMarkup(playerRobotInfo, {disabled: true, slot: slotKey})); }
                let $abilityInStorage = $abilitiesInStorage.filter('[data-ability="' + selectedAbilityToken + '"].selected');
                playerRobotInfo.abilities = currentAbilities;
                _self.saveWorldState(function(){
                    _self.playSoundEffect('link-click-action');
                    setTimeout(function(){ $currentAbilitiesList.removeClass('has-selection'); }, 300);
                    setTimeout(function(){ hideActionModal(true); }, 600);
                    setTimeout(function(){ refreshEquippedAbilities(); $abilityInStorage.trigger('click'); }, 900);
                    });
                }
            return true;
            };
        let refreshAbilityModal = function(actionKind){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.refreshAbilityModal()', 'color: magenta;');
            // either enable or disable the confirm button based on whether or not a selection has been made
            let $actionButtons = $actionModal.find('.content .buttons.actions');
            let $confirmButton = $actionButtons.find('.button[data-action="confirm"]');
            let $cancelButton = $actionButtons.find('.button[data-action="cancel"]');
            let $currentAbilities = $actionModal.find('.ability-list.current');
            let $selectedAbility = $currentAbilities.find('.team-ability[data-ability].selected');
            if ($selectedAbility && $selectedAbility.length){
                //console.log('--> an ability has been selected:', $selectedAbility.attr('data-ability'));
                $confirmButton.removeClass('disabled').removeAttr('disabled');
                } else {
                //console.log('--> an ability has not been selected yet');
                $confirmButton.addClass('disabled').attr('disabled', 'disabled');
                }
            return true;
            };
        let updateItemQuantityInStorage = function(itemToken){
            //console.log('%c' + '~mmrpgWorldMap.showActionModal.updateItemQuantityInStorage(itemToken: ' + itemToken + ')', 'color: magenta;');
            let $storageItem = $itemStorageBox.find('.team-item[data-item="' + itemToken + '"]');
            let $storageItemDetails = $itemStorageBox.find('> .storage-details[data-item="' + itemToken + '"]');
            let totalItemQuantity = _worldPlayerItems[itemToken] || 0;
            let equippedItemQuantity = _worldPlayerItems[itemToken + '__equipped'] || 0;
            let availableQuantity = totalItemQuantity - equippedItemQuantity;
            //console.log('-> totalItemQuantity =', totalItemQuantity);
            //console.log('-> equippedItemQuantity =', equippedItemQuantity);
            //console.log('-> availableQuantity =', availableQuantity);
            $storageItem.attr('data-quantity', availableQuantity);
            $storageItem.find('> .quantity').html('&times; ' + availableQuantity);
            $storageItemDetails.find('> .subtitle > .quantity').html('&times; ' + availableQuantity);
            if (availableQuantity === 0){ $storageItemDetails.find('.button[data-action]').addClass('disabled').attr('disabled', 'disabled'); }
            };

        // Collect required and necessary data for displaying this action modal
        if (actionKind === 'robot'){
            //console.log('--> actionKind is robot ...', actionObjectToken);
            let robotsIndex = _indexes.robots;
            //console.log('--> robotsIndex =', robotsIndex);
            let robotString = actionObjectToken;
            let robotID = robotString.split('_')[0];
            let robotToken = robotString.split('_')[1];
            //let robotInfo = typeof robotsIndex[robotToken] !== 'undefined' ? robotsIndex[robotToken] : null;
            //if (!robotToken || !robotInfo){ console.error('showActionModal() could not find robot info for token ' + robotToken + '!'); return; }
            //let robotData = typeof _worldPlayerRobots[robotString] !== 'undefined' ? _worldPlayerRobots[robotString] : null;
            //if (!robotToken || !robotData){ console.error('showActionModal() could not find player robot data for token ' + robotToken + '!'); return; }
            //console.log('--> robotID =', robotID);
            //console.log('--> robotToken =', robotToken);
            //console.log('--> robotInfo =', robotInfo);
            //console.log('--> robotData =', robotData);
            //let $robotOnTeam = $teamRobotsDiv.find('.team-robot[data-robot="' + robotString + '"]');
            //let $robotInStorage = $robotStorageBox.find('.team-robot[data-robot="' + robotString + '"]');
            let $robotDetailsDiv = $robotsOverview.find('.storage-details[data-robot="' + robotString + '"]');
            //console.log('--> $robotOnTeam =', $robotOnTeam.length, $robotOnTeam);
            //console.log('--> $robotInStorage =', $robotInStorage.length, $robotInStorage);
            //console.log('--> $robotDetailsDiv =', $robotDetailsDiv.length, $robotDetailsDiv);
            // If this is an ADD ROBOT request, we can add the robot to the player's team array and update
            if (actionToken === 'add-robot'){
                modalDetails.show = false;
                //console.log('--> preparing to ADD ROBOT (', robotString, ') to team (', _worldPlayerTeam.join(','), ') ...');
                let saveWorldState = false;
                if (_worldPlayerTeam.indexOf(robotString) === -1){
                    let teamRobotString = robotString;
                    //console.log('-> adding teamRobotString =', teamRobotString);
                    _self.addTeamRobot(teamRobotString);
                    $robotDetailsDiv.find('.button[data-action="add-robot"]').addClass('disabled hidden').removeClass('hovered').attr('disabled', 'disabled');
                    $robotDetailsDiv.find('.button[data-action="remove-robot"]').removeClass('disabled hidden').addClass('hovered').removeAttr('disabled');
                    saveWorldState = true;
                    }
                if (saveWorldState){ _self.saveWorldState(function(){ _self.playSoundEffect('link-click-action'); }); }
                }
            // Else if this is a REMOVE ROBOT request, we should remove the robot from the player's team array and update
            else if (actionToken === 'remove-robot'){
                modalDetails.show = false;
                //console.log('--> preparing to REMOVE ROBOT (', robotString, ') from team (', _worldPlayerTeam.join(','), ') ...');
                let saveWorldState = false;
                if (_worldPlayerTeam.indexOf(robotString) !== -1){
                    let teamRobotString = robotString;
                    //console.log('-> removing teamRobotString =', teamRobotString);
                    _self.removeTeamRobot(teamRobotString);
                    $robotDetailsDiv.find('.button[data-action="remove-robot"]').addClass('disabled hidden').removeClass('hovered').attr('disabled', 'disabled');
                    $robotDetailsDiv.find('.button[data-action="add-robot"]').removeClass('disabled hidden').addClass('hovered').removeAttr('disabled');
                    saveWorldState = true;
                    }
                if (saveWorldState){ _self.saveWorldState(function(){ _self.playSoundEffect('link-click-action'); }); }
                }
            }
        else if (actionKind === 'item'){
            //console.log('--> actionKind is item ...', actionObjectToken);
            let itemsIndex = _indexes.items;
            let itemToken = actionObjectToken;
            let itemInfo = typeof itemsIndex[itemToken] !== 'undefined' ? itemsIndex[itemToken] : null;
            if (!itemToken || !itemInfo){ console.error('showActionModal() could not find item info for token ' + itemToken + '!'); return; }
            //console.log('--> itemsIndex =', itemsIndex);
            //console.log('--> itemToken =', itemToken);
            //console.log('--> itemInfo =', itemInfo);
            let $itemInStorage = $itemStorageBox.find('.team-item[data-item="' + itemToken + '"]');
            let $itemInStorageDetails = $itemStorageBox.find('> .storage-details[data-item="' + itemToken + '"]');
            // If this is a USE ITEM request, we can apply the item to the selected robot directly
            if (actionToken === 'use-item'){
                modalDetails.show = false;
                //console.log('--> preparing to USE ITEM on robot (', targetRobotToken, ') ...');
                // Check to see which kind of item this is categorically
                let tokenFrags = itemToken.split('-');
                let itemToken1 = tokenFrags[0] || '';
                let itemToken2 = tokenFrags[1] || '';
                let isYashichi = itemToken === 'yashichi' ? true : false;
                let isExtraLife = itemToken === 'extra-life' ? true : false;
                let isPellet = itemToken2 === 'pellet' ? true : false;
                let isCapsule = itemToken2 === 'capsule' ? true : false;
                let isTank = itemToken2 === 'tank' ? true : false;
                let isEnergy = itemToken1 === 'energy' || isYashichi || isExtraLife ? true : false;
                let isWeapons = itemToken1 === 'weapon' || isYashichi || isExtraLife ? true : false;
                let isAttack = itemToken1 === 'attack' ? true : false;
                let isDefense = itemToken1 === 'defense' ? true : false;
                let isSpeed = itemToken1 === 'speed' ? true : false;
                let isSuper = itemToken1 === 'super' ? true : false;
                let isBasic = isPellet || isCapsule || isTank;
                let isStatItem = isAttack || isDefense || isSpeed || isSuper;
                let isRecoveryItem = isEnergy || isWeapons;
                let removeFromInventory = false;
                let disableFurtherUsage = false;
                //console.log('--> { isPellet:', isPellet, ', isCapsule:', isCapsule, ', isTank:', isTank, ', isEnergy:', isEnergy, ', isWeapons:', isWeapons, ', isAttack:', isAttack, ', isDefense:', isDefense, ', isSpeed:', isSpeed, ', isSuper:', isSuper, ', isBasic:', isBasic, ', isStatItem:', isStatItem, ', isRecoveryItem:', isRecoveryItem, ' }');
                // If this is a stat item (like an attack/defense/speed pellet or capsule) its effects are straightforward
                if (isBasic && isStatItem){
                    //console.log('--> using basic stat item ...');
                    let boostStats = [], boostedToMax = [];
                    if (isAttack || isSuper){ boostStats.push('attack'); }
                    if (isDefense || isSuper){ boostStats.push('defense'); }
                    if (isSpeed || isSuper){ boostStats.push('speed'); }
                    let boostAmount = Math.floor((itemInfo.recovery || 0) / boostStats.length);
                    //console.log('--> boosting stats:', boostStats.join('/'), 'by amount:', boostAmount);
                    if (boostStats.length && boostAmount > 0){
                        for (var i = 0; i < boostStats.length; i++){
                            let statToken = boostStats[i], statBoostFunction = _self.boostRobotStat.bind(_self), statMaxValue = _config.robotStatModMax;
                            let statBoosted = statBoostFunction(targetRobotToken, statToken, boostAmount, true);
                            //console.log('--> statToken =', statToken, 'statBoosted =', statBoosted);
                            if (statBoosted){ removeFromInventory = true; }
                            if (playerRobotInfo[statToken + 'Mods'] >= statMaxValue){ boostedToMax.push(statToken); }
                            }
                        if (boostedToMax.length >= boostStats.length){ disableFurtherUsage = true; }
                        }
                    }
                // Else if this is a recovery item (like an energy/weapon pellet, capsule, or tank) its effects are a bit more complex
                else if ((isBasic && isRecoveryItem) || isYashichi){
                    //console.log('--> using basic recovery item ...');
                    let restoreStats = [], restoredToMax = [];
                    if (isEnergy){ restoreStats.push('energy'); }
                    if (isWeapons){ restoreStats.push('weapons'); }
                    let restoreAmount = itemInfo.recovery || 0;
                    let restorePercent = itemInfo.recoveryPercent ? true : false;
                    //console.log('--> recovering stats:', restoreStats.join('/'), 'by amount:', restoreAmount, (restorePercent ? '%' : ''));
                    if (restoreStats.length && restoreAmount > 0){
                        for (var i = 0; i < restoreStats.length; i++){
                            let statToken = restoreStats[i], statRestoreFunction = null, statMaxValue = 0;
                            if (statToken === 'energy'){ statRestoreFunction = _self.restoreRobotEnergy.bind(_self); statMaxValue = playerRobotInfo.energyMax || 0; }
                            else if (statToken === 'weapons'){ statRestoreFunction = _self.restoreRobotWeapons.bind(_self); statMaxValue = playerRobotInfo.weaponsMax || 0; }
                            let realRestoreAmount = restorePercent ? Math.ceil((statMaxValue || 0) * (restoreAmount / 100)) : restoreAmount;
                            let statRestored = statRestoreFunction(targetRobotToken, realRestoreAmount, true);
                            //console.log('--> statToken =', statToken, 'statRestored =', statRestored);
                            if (statRestored){ removeFromInventory = true; }
                            if (playerRobotInfo[statToken] >= statMaxValue){ restoredToMax.push(statToken); }
                            }
                        if (restoredToMax.length >= restoreStats.length){ disableFurtherUsage = true; }
                        }
                    }
                // Else if this was an extra life specifically, make sure we revive the robot with appropriate resources
                else if (isExtraLife){
                    //console.log('--> using extra life item ...');
                    let energyMaxValue = playerRobotInfo.energyMax || 0;
                    let energyRestoreAmount = itemInfo.recovery || 0;
                    let energyRestorePercent = itemInfo.recoveryPercent ? true : false;
                    let realEnergyRestoreAmount = energyRestorePercent ? Math.ceil((energyMaxValue || 0) * (energyRestoreAmount / 100)) : energyRestoreAmount;
                    let weaponsMaxValue = playerRobotInfo.weaponsMax || 0;
                    let weaponsRestoreAmount = itemInfo.recovery2 || 0;
                    let weaponsRestorePercent = itemInfo.recovery2Percent ? true : false;
                    let realWeaponsRestoreAmount = weaponsRestorePercent ? Math.ceil((weaponsMaxValue || 0) * (weaponsRestoreAmount / 100)) : weaponsRestoreAmount;
                    //console.log('--> recovering energy by amount:', energyRestoreAmount, (energyRestorePercent ? '%' : ''), ' => (', realEnergyRestoreAmount, ' LE)');
                    //console.log('--> recovering weapons by amount:', weaponsRestoreAmount, (weaponsRestorePercent ? '%' : ''), ' => (', realWeaponsRestoreAmount, ' WE)');
                    if ((playerRobotInfo.disabled === true || playerRobotInfo.energy === 0)
                        && (realEnergyRestoreAmount > 0 || realWeaponsRestoreAmount > 0)){
                        //console.log('--> first flooring to zero...');
                        _self.setRobotEnergy(targetRobotToken, 0); // just to match below technically
                        _self.setRobotWeapons(targetRobotToken, 0); // so that we see it fill-up from zero
                        //console.log('--> now recoverying energy and weapons ...');
                        _self.restoreRobotEnergy(targetRobotToken, realEnergyRestoreAmount, true);
                        _self.restoreRobotWeapons(targetRobotToken, realWeaponsRestoreAmount, true);
                        //console.log('--> okay we should be revived now!');
                        removeFromInventory = true;
                        disableFurtherUsage = true;
                        }
                    }
                // Else If the item was successfully used, remove it from the player's inventory now
                if (removeFromInventory
                    && typeof _worldPlayerItems[itemToken] !== 'undefined'){
                    //console.log('_worldPlayerItems[itemToken] (before) =', _worldPlayerItems[itemToken]);
                    _worldPlayerItems[itemToken] -= 1;
                    if (_worldPlayerItems[itemToken] < 0){ _worldPlayerItems[itemToken] = 0; }
                    //console.log('_worldPlayerItems[itemToken] (after) =', _worldPlayerItems[itemToken]);
                    let totalItemQuantity = _worldPlayerItems[itemToken] || 0;
                    let equippedItemQuantity = _worldPlayerItems[itemToken + '__equipped'] || 0;
                    let availableQuantity = totalItemQuantity - equippedItemQuantity;
                    //console.log('-> totalItemQuantity =', totalItemQuantity);
                    //console.log('-> equippedItemQuantity =', equippedItemQuantity);
                    //console.log('-> availableQuantity =', availableQuantity);
                    $itemInStorage.attr('data-quantity', availableQuantity);
                    $itemInStorage.find('> .quantity').html('&times; ' + availableQuantity);
                    $itemInStorageDetails.find('> .subtitle > .quantity').html('&times; ' + availableQuantity);
                    if (availableQuantity === 0){ $itemInStorageDetails.find('.button[data-action]').addClass('disabled').attr('disabled', 'disabled'); }
                    _self.saveWorldState(function(){ _self.playSoundEffect('link-click-action'); });
                    }
                // If the item can no longer be used, disable its use button now (maybe we're already maxed)
                if (disableFurtherUsage){
                    //console.log('--> disabling further usage of this item ...');
                    $itemInStorageDetails.find('.button[data-action="use-item"]').addClass('disabled').attr('disabled', 'disabled');
                    }
                }
            // Else if this is a GIVE ITEM request, we should show the item equip modal now (showing old vs new item)
            else if (actionToken === 'give-item'){
                modalDetails.show = false;
                //console.log('--> preparing to GIVE ITEM to robot (', targetRobotToken, ') ...');
                let saveWorldState = false;
                if (playerRobotInfo.item){
                    let existingItemToken = playerRobotInfo.item;
                    //console.log('-> removing existingItemToken =', existingItemToken);
                    _self.takeRobotItem(targetRobotToken, false, false);
                    updateItemQuantityInStorage(existingItemToken);
                    saveWorldState = true;
                    }
                if (typeof _worldPlayerItems[itemToken] !== 'undefined'){
                    let newItemToken = itemToken;
                    //console.log('-> equipping newItemToken =', newItemToken);
                    _self.giveRobotItem(targetRobotToken, newItemToken);
                    $itemInStorageDetails.find('.button[data-action="give-item"]').addClass('disabled').attr('disabled', 'disabled');
                    $itemInStorageDetails.find('.button[data-action="take-item"]').removeClass('disabled').removeAttr('disabled');
                    updateItemQuantityInStorage(newItemToken);
                    saveWorldState = true;
                    }
                if (saveWorldState){ _self.saveWorldState(function(){ _self.playSoundEffect('link-click-action'); }); }
                }
            // Else if this is a TAKE ITEM request, we should show the item unequip modal now (showing item removal)
            else if (actionToken === 'take-item'){
                modalDetails.show = false;
                //console.log('--> preparing to TAKE ITEM from robot (', targetRobotToken, ') ...');
                let saveWorldState = false;
                if (playerRobotInfo.item){
                    let existingItemToken = playerRobotInfo.item;
                    //console.log('-> removing existingItemToken =', existingItemToken);
                    _self.takeRobotItem(targetRobotToken);
                    $itemInStorageDetails.find('.button[data-action="take-item"]').addClass('disabled').attr('disabled', 'disabled');
                    $itemInStorageDetails.find('.button[data-action="give-item"]').removeClass('disabled').removeAttr('disabled');
                    updateItemQuantityInStorage(existingItemToken);
                    saveWorldState = true;
                    }
                if (saveWorldState){ _self.saveWorldState(function(){ _self.playSoundEffect('link-click-action'); }); }
                }
            // Else if this is the DROP ITEM request, we should confirm the drop now w/ modal
            else if (actionToken === 'drop-item'){
                //console.log('--> preparing to DROP ITEM at current location ...');
                // TODO: ...
                }
            }
        else if (actionKind === 'ability'){
            //console.log('--> actionKind is ability ...', actionObjectToken);
            //console.log('--> ability actionToken is ...', actionToken);
            let abilitiesIndex = _indexes.abilities;
            let currentAbilities = playerRobotInfo.abilities;
            let maxAbilitiesPerRobot = _config.maxAbilitiesPerRobot;
            //console.log('--> abilitiesIndex =', abilitiesIndex);
            //console.log('--> currentAbilities =', currentAbilities);
            //console.log('--> maxAbilitiesPerRobot =', maxAbilitiesPerRobot);
            let selectedAbilityToken = actionObjectToken;
            let selectedAbilityInfo = typeof abilitiesIndex[selectedAbilityToken] !== 'undefined' ? abilitiesIndex[selectedAbilityToken] : null;
            if (!selectedAbilityToken || !selectedAbilityInfo){ console.error('showActionModal() could not find new ability info for token ' + selectedAbilityToken + '!'); return; }
            //console.log('--> selectedAbilityToken =', selectedAbilityToken);
            //console.log('--> selectedAbilityInfo =', selectedAbilityInfo);
            if (actionToken === 'equip-ability'){
                let selectedAbilityList = '' + generateAbilitySelectButtonMarkup(selectedAbilityToken, playerRobotInfo, {selected: false});
                modalDetails.subtitles.forTooltip = 'Select Ability To Replace';
                modalDetails.containers.forSelected = '<div class="ability-list selected">' + selectedAbilityList + '</div>';
                }
            else if (actionToken === 'remove-ability'){
                modalDetails.subtitles.forTooltip = 'Select Ability To Remove';
                modalDetails.subtitles.forSelected = '';
                modalDetails.containers.forSelected = '';
                }
            if (actionToken === 'equip-ability'
                || actionToken === 'remove-ability'){
                let currentAbilitiesList = '' + (function(current, selected){
                    for (var slotKey = 0, numEmpty = 0, listMarkup = ''; slotKey < maxAbilitiesPerRobot; slotKey++){
                        let currentAbilityID = typeof current[slotKey] !== 'undefined' ? current[slotKey] : null;
                        let currentAbilityInfo = currentAbilityID ? abilitiesIndex.getByID(currentAbilityID) : null;
                        let currentAbilityToken = currentAbilityID && currentAbilityInfo ? currentAbilityInfo.token : null;
                        let isDisabled = false, isSelected = false;
                        if (actionToken === 'equip-ability'){ isDisabled = (currentAbilityToken === selected) || (!currentAbilityToken && numEmpty > 0) ? true : false; }
                        else if (actionToken === 'remove-ability'){ isDisabled = !currentAbilityToken ? true : false; isSelected = (currentAbilityToken === selectedAbilityToken) ? true : false; }
                        let buttonOptions = {slot: slotKey, disabled: isDisabled, selected: isSelected};
                        if (currentAbilityID && currentAbilityToken && currentAbilityInfo){ listMarkup += generateAbilitySelectButtonMarkup(currentAbilityToken, playerRobotInfo, buttonOptions); }
                        else { numEmpty++; listMarkup += generateAbilitySelectPlaceholderMarkup(playerRobotInfo, buttonOptions); }
                        } return listMarkup;
                    })(currentAbilities, selectedAbilityToken);
                modalDetails.containers.forCurrent = '<div class="ability-list current">' + currentAbilitiesList + '</div>';
                }
            modalDetails.buttons.confirm.disabled = true;
            if (actionToken === 'equip-ability'
                && currentAbilities.indexOf(abilitiesIndex[selectedAbilityToken].id) === -1
                && currentAbilities.length < maxAbilitiesPerRobot){
                //console.log('--> robot has empty ability slots, auto-equipping new ability ...');
                modalDetails.buttons.cancel.hidden = true;
                //modalDetails.buttons.confirm.text = 'Saving';
                modalDetails.callbacks.onShow = function(){
                    //console.log('...this is where we would auto-click the first empty slot and then auto-click confirm');
                    let $currentAbilitiesList = $actionModal.find('.container.for-current .ability-list.current');
                    let $firstEmptySlot = $currentAbilitiesList.find('.team-ability[data-ability].placeholder').first();
                    setTimeout(function(){ clickTeamAbilityButton($firstEmptySlot); }, 900);
                    setTimeout(function(){ onConfirmAction('ability_equip-ability'); }, 900);
                    };
                }
            else if (actionToken === 'remove-ability'
                && currentAbilities.indexOf(abilitiesIndex[selectedAbilityToken].id) !== -1
                && currentAbilities.length >= 1){
                //console.log('--> robot has other abilities equipped, auto-removing selected ability ...');
                modalDetails.buttons.cancel.hidden = true;
                //modalDetails.buttons.confirm.text = 'Saving';
                modalDetails.callbacks.onShow = function(){
                    //console.log('...this is where we would auto-click confirm');
                    setTimeout(function(){ onConfirmAction('ability_remove-ability'); }, 900);
                    };
                }
            }
        else {
            console.error('showActionModal() received invalid actionKind ' + actionKind + '!');
            return;
            }

        //console.log('finished calculating modalDetails ...');
        //console.log('-> modalDetails = ', modalDetails);

        // If it was decided not to show the modal, we can be done here
        if (!modalDetails.show){
            //console.log('--> modalDetails.show is false, returning right away');
            setTimeout(function(){ runModalCallback('onComplete'); }, 900);
            return;
            }

        // Collect quick references to modal details
        let modalAction = modalDetails.action;
        let modalTitle = modalDetails.title;
        let modalSubtitles = modalDetails.subtitles;
        let modalContainers = modalDetails.containers;
        let modalButtons = modalDetails.buttons;

        // Check if the action modal already exists and create it if not
        if (!$actionModal || !$actionModal.length){
            //console.log('--> action modal does not exist yet, creating ...');

            // Generate the action modal markup bow that we've collected all necessary data
            let actionModalMarkup = '';
            actionModalMarkup += '<div id="action-modal" class="chrome active hidden">';
                actionModalMarkup += '<div class="overlay"></div>';
                actionModalMarkup += '<div class="wrapper">';
                    actionModalMarkup += '<div class="content">';
                        actionModalMarkup += '<h1 class="title">' + modalTitle + '</h1>';
                        actionModalMarkup += '<h2 class="subtitle for-selected">' + modalSubtitles.forSelected + '</h2>';
                        actionModalMarkup += '<div class="container for-selected">' + modalContainers.forSelected + '</div>';
                        actionModalMarkup += '<h2 class="subtitle for-current">' + modalSubtitles.forCurrent + '</h2>';
                        actionModalMarkup += '<h3 class="subtitle for-tooltip">' + modalSubtitles.forTooltip + '</h3>';
                        actionModalMarkup += '<div class="container for-current">' + modalContainers.forCurrent + '</div>';
                        actionModalMarkup += '<div class="buttons actions">';
                            if (modalButtons){
                                for (let buttonKey in modalButtons){
                                    let buttonInfo = modalButtons[buttonKey];
                                    actionModalMarkup += '<button type="button" class="button ' + buttonInfo.action + (buttonInfo.disabled ? ' disabled' : '') + (buttonInfo.hidden ? ' hidden' : '') + '" data-action="' + buttonInfo.action + '"' + (buttonInfo.disabled ? ' disabled="disabled"' : '') + '><strong>' + buttonInfo.text + '</strong></button>';
                                    }
                                }
                        actionModalMarkup += '</div>';
                    actionModalMarkup += '</div>';
                actionModalMarkup += '</div>';
            actionModalMarkup += '</div>';

            // Append the action modal to the canvas wrapper and update the reference
            $canvasWrapper.append(actionModalMarkup);
            $actionModal = $('#action-modal', $canvasWrapper);
            $actionModal.attr('data-action', modalAction);
            $actionModal.attr('data-action-token', actionObjectToken);
            _elements.actionModal = $actionModal;

            // Define reusable functions for applying/removing the hover state to given element
            let hoverModalObject = function(e){
                if (_self.worldIsBusy()){ return; }
                $actionModal.find('.hovered').removeClass('hovered');
                $(this).addClass('hovered');
                _self.playSoundEffect('icon-hover');
                };
            let unhoverModalObject = function(e){
                $(this).removeClass('hovered');
                };

            // Some quick functions for checking if the modal is busy or active
            let actionModalIsActive = function(){ return $actionModal.is('.active') ? true : false; };
            let actionModalIsHidden = function(){ return $actionModal.is('.hidden') ? true : false; };
            let actionModalIsBusy = function(){ return $actionModal.is('.busy') ? true : false; };
            let allowModalActions = function(){
                if (_self.worldIsBusy()){ return false; }
                if (!actionModalIsActive()){ return false; } // if we're not active, ignore clicks
                if (actionModalIsHidden()){ return false; } // if we're hidden, ignore clicks
                if (actionModalIsBusy()){ return false; } // if we're busy, ignore clicks
                return true;
                };

            // Make sure clicking the background automatically closes the modal
            $actionModal.delegate('.overlay', 'click', function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!allowModalActions()){ return; }
                //console.log('%c' + 'Action modal overlay clicked!', 'color: cyan;');
                return onCancelAction();
                });

            // Delegate events to the buttons in the action modal now that its markup is created/updated
            $actionModal.delegate('.button[data-action]', 'mouseenter', hoverModalObject);
            $actionModal.delegate('.button[data-action]', 'mouseleave', unhoverModalObject);
            $actionModal.delegate('.button[data-action]', 'click', function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!allowModalActions()){ return; }
                //console.log('%c' + 'Modal action button clicked!', 'color: cyan;');
                let $button = $(this);
                let modalAction = $actionModal.attr('data-action');
                let buttonAction = $button.attr('data-action');
                //console.log('-> $button =', $button);
                //console.log('-> modalAction =', modalAction);
                //console.log('-> buttonAction =', buttonAction);
                if ($button.is('.disabled') || $button.is('[disabled]')){ return false; }
                $button.addClass('clicked');
                if (buttonAction === 'confirm'){ _self.playSoundEffect('icon-click'); return onConfirmAction(modalAction); }
                else if (buttonAction === 'cancel'){ _self.playSoundEffect('back-click'); return onCancelAction(modalAction); }
                else { return false; }
                });

            // Delegate the actions for the team item buttons that can appear within the window
            $actionModal.delegate('.container.for-current .team-item[data-item]', 'mouseenter', hoverModalObject);
            $actionModal.delegate('.container.for-current .team-item[data-item]', 'mouseleave', unhoverModalObject);
            $actionModal.delegate('.container.for-current .team-item[data-item]', 'click', function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!allowModalActions()){ return; }
                //console.log('%c' + 'Item select button clicked!', 'color: cyan;');
                // TODO: define the click actions for item buttons in the action modal (???)
                _self.playSoundEffect('icon-click-mini');
                return true;
                });

            // Delegate the actions for the team item ability that can appear within the window
            $actionModal.delegate('.container.for-current .team-ability[data-ability]', 'mouseenter', hoverModalObject);
            $actionModal.delegate('.container.for-current .team-ability[data-ability]', 'mouseleave', unhoverModalObject);
            $actionModal.delegate('.container.for-current .team-ability[data-ability]', 'click', function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!allowModalActions()){ return; }
                //console.log('%c' + 'Ability select button clicked!', 'color: cyan;');
                _self.playSoundEffect('icon-click-mini');
                return clickTeamAbilityButton(this);
                });

            }
        else {
            //console.log('--> action modal already exists, updating ...');

            // Update the action modal title, subtitles, containers, and actions
            let $content = $actionModal.find('.content');
            $actionModal.removeClass('busy');
            $actionModal.addClass('active hidden');
            $actionModal.attr('data-action', modalAction);
            $actionModal.attr('data-action-token', actionObjectToken);
            let $title = $content.find('.title'); $title.html(modalTitle);
            let $subForSelected = $content.find('.subtitle.for-selected'); $subForSelected.html(modalSubtitles.forSelected);
            let $subForCurrent = $content.find('.subtitle.for-current'); $subForCurrent.html(modalSubtitles.forCurrent);
            let $subForTooltip = $content.find('.subtitle.for-tooltip'); $subForTooltip.html(modalSubtitles.forTooltip);
            let $contForSelected = $content.find('.container.for-selected'); $contForSelected.html(modalContainers.forSelected);
            let $contForCurrent = $content.find('.container.for-current'); $contForCurrent.html(modalContainers.forCurrent);
            let $buttonConfirm = $content.find('.button.confirm'); $buttonConfirm.html('<strong>' + modalButtons.confirm.text + '</strong>');
            let $buttonCancel = $content.find('.button.cancel'); $buttonCancel.html('<strong>' + modalButtons.cancel.text + '</strong>');
            if (modalButtons.confirm.disabled){ $buttonConfirm.addClass('disabled'); $buttonConfirm.attr('disabled', 'disabled'); }
            else { $buttonConfirm.removeClass('disabled'); $buttonConfirm.removeAttr('disabled');  }
            if (modalButtons.confirm.hidden){ $buttonConfirm.addClass('hidden'); }
            else { $buttonConfirm.removeClass('hidden'); }
            if (modalButtons.cancel.disabled){ $buttonCancel.addClass('disabled'); $buttonCancel.attr('disabled', 'disabled'); }
            else { $buttonCancel.removeClass('disabled'); $buttonCancel.removeAttr('disabled'); }
            if (modalButtons.cancel.hidden){ $buttonCancel.addClass('hidden'); }
            else { $buttonCancel.removeClass('hidden'); }

            }

        // Append the action modal to the window and show it (remove any existing one first)
        setTimeout(function(){
            //console.log('-> showing action modal ...');
            $actionModal.removeClass('hidden');
            _world.mapIsHidden = true;
            _world.actionModalVisible = true;
            runModalCallback('onShow');
            }, 100);

        // Return true on success
        setTimeout(function(){ runModalCallback('onComplete'); }, 900);
        return true;
        }

    // Define a quick function for showing an robot modal for some kind of robot-related action
    showRobotModal(actionToken, robotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showRobotModal(actionToken:' + actionToken + ', robotToken:' + robotToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showRobotModal() missing required actionToken!'); return; }
        if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('showRobotModal() missing required robotToken!'); return; }
        let _self = this;
        return _self.showActionModal('robot', actionToken, robotToken, null, configCustom);
        }

    // Define a quick function for showing an item modal for some kind of item-related action
    showItemModal(actionToken, itemToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showItemModal(actionToken:' + actionToken + ', itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showItemModal() missing required actionToken!'); return; }
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showItemModal() missing required itemToken!'); return; }
        let _self = this;
        return _self.showActionModal('item', actionToken, itemToken, targetRobotToken, configCustom);
        }

    // Define a quick function for showing an ability modal for some kind of ability-related action
    showAbilityModal(actionToken, abilityToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showAbilityModal(actionToken:' + actionToken + ', abilityToken:' + abilityToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!actionToken || typeof actionToken !== 'string' || !actionToken.length){ console.error('showAbilityModal() missing required actionToken!'); return; }
        if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('showAbilityModal() missing required abilityToken!'); return; }
        targetRobotToken = targetRobotToken && typeof targetRobotToken === 'string' && targetRobotToken.length ? targetRobotToken : null;
        let _self = this;
        return _self.showActionModal('ability', actionToken, abilityToken, targetRobotToken, configCustom);
        }

    // Define quick functions for showing the modal that adds a robot to the team from storage
    showAddRobotModal(robotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showAddRobotModal(robotToken:' + robotToken + ')', 'color: magenta;');
        if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('showAddRobotModal() missing required robotToken!'); return; }
        let _self = this;
        return _self.showRobotModal('add-robot', robotToken, configCustom);
        }

    // Define quick functions for showing the modal that removes a robot from the team to storage
    showRemoveRobotModal(robotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showRemoveRobotModal(robotToken:' + robotToken + ')', 'color: magenta;');
        if (!robotToken || typeof robotToken !== 'string' || !robotToken.length){ console.error('showRemoveRobotModal() missing required robotToken!'); return; }
        let _self = this;
        return _self.showRobotModal('remove-robot', robotToken, configCustom);
        }

    // Define quick functions for showing specific modals for items
    showUseItemModal(itemToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showUseItemModal(itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showUseItemModal() missing required itemToken!'); return; }
        if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showUseItemModal() missing required targetRobotToken!'); return; }
        let _self = this;
        return _self.showItemModal('use-item', itemToken, targetRobotToken, configCustom);
        }

    // Define a quick function for showing the give item modal
    showGiveItemModal(itemToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showGiveItemModal(itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showGiveItemModal() missing required itemToken!'); return; }
        if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showGiveItemModal() missing required targetRobotToken!'); return; }
        let _self = this;
        return _self.showItemModal('give-item', itemToken, targetRobotToken, configCustom);
        }

    // Define a quick function for showing the take item modal
    showTakeItemModal(itemToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showTakeItemModal(itemToken:' + itemToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showTakeItemModal() missing required itemToken!'); return; }
        if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showTakeItemModal() missing required targetRobotToken!'); return; }
        let _self = this;
        return _self.showItemModal('take-item', itemToken, targetRobotToken, configCustom);
        }

    // Define a quick function for showing the drop item modal
    showDropItemModal(itemToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showDropItemModal(itemToken:' + itemToken + ')', 'color: magenta;');
        if (!itemToken || typeof itemToken !== 'string' || !itemToken.length){ console.error('showDropItemModal() missing required itemToken!'); return; }
        let _self = this;
        return _self.showItemModal('drop-item', itemToken, null, configCustom);
        }

    // Define a quick function for showing the equip ability modal
    showEquipAbilityModal(abilityToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showEquipAbilityModal(abilityToken:' + abilityToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('showEquipAbilityModal() missing required abilityToken!'); return; }
        if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showEquipAbilityModal() missing required targetRobotToken!'); return; }
        let _self = this;
        return _self.showAbilityModal('equip-ability', abilityToken, targetRobotToken, configCustom);
        }

    // Define a quick function for showing the remove ability modal
    showRemoveAbilityModal(abilityToken, targetRobotToken, configCustom){
        //console.log('%c' + 'mmrpgWorldMap.showRemoveAbilityModal(abilityToken:' + abilityToken + ', targetRobotToken:' + targetRobotToken + ')', 'color: magenta;');
        if (!abilityToken || typeof abilityToken !== 'string' || !abilityToken.length){ console.error('showRemoveAbilityModal() missing required abilityToken!'); return; }
        if (!targetRobotToken || typeof targetRobotToken !== 'string' || !targetRobotToken.length){ console.error('showRemoveAbilityModal() missing required targetRobotToken!'); return; }
        let _self = this;
        return _self.showAbilityModal('remove-ability', abilityToken, targetRobotToken, configCustom);
        }

    // Define a quick function for generating the markup for an item select button given an item token, robot info, and/or optional settings
    generateItemSelectButtonMarkup(itemToken, playerRobotInfo, buttonOptions){
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
    generateItemSelectPlaceholderMarkup(playerRobotInfo, buttonOptions){
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

    // Define a quick function for generating the markup for an ability select button given an ability token, robot info, and/or optional settings
    generateAbilitySelectButtonMarkup(abilityToken, playerRobotInfo, buttonOptions){
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
    generateAbilitySelectPlaceholderMarkup(playerRobotInfo, buttonOptions){
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

    // Define a quick function for initializing the minimap HUD and its elements, focus, position, and any masking
    initMiniMap(){
        //console.log('%c' + 'mmrpgWorldMap.initMiniMap()', 'color: magenta;');

        // Collect references to world objects
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        let _elements = _self.elements;
        let $thisWorld = _elements.world;
        let $canvasMap = _elements.canvasMap;
        let $minimapOverview = _elements.minimapOverview;
        let $minimapButtons = $('.buttons', $minimapOverview);
        let $worldViewport = $('.viewport.world', $minimapOverview);
        let $areaViewport = $('.viewport.area', $minimapOverview);
        let $worldViewButton = $('.button[data-view="world"]', $minimapButtons);
        let $areaViewButton = $('.button[data-view="area"]', $minimapButtons);
        if (!$worldViewport || !$worldViewport.length){ console.error('initMiniMap() could not find world viewport element!'); return false; }
        if (!$areaViewport || !$areaViewport.length){ console.error('initMiniMap() could not find area viewport element!'); return false; }
        if (!$worldViewButton || !$worldViewButton.length){ console.error('initMiniMap() could not find world view button element!'); return false; }
        if (!$areaViewButton || !$areaViewButton.length){ console.error('initMiniMap() could not find area view button element!'); return false; }

        // Initialize the world viewport first within the mini-map (the one with the cols/rows and grid)
        let worldMiniMapReady = false;
        let initWorldMiniMap = function(onComplete){
            //console.log('%c' + '~initWorldMiniMap()', 'color: magenta;');

            // Collect references to viewport elements
            let $worldGrid = $('.grid', $worldViewport);
            let $worldImage = $('.image', $worldViewport);
            let $worldMarker = $('.marker', $worldViewport);

            // Collect viewport dimensions so we can more easily center the map on focused position later
            let viewportWidth = $worldViewport.width();
            let viewportHeight = $worldViewport.height();

            // Pull necessary data-attributes from the minimap elements
            let worldImage = $worldImage.is('[data-image]') ? $worldImage.attr('data-image') : '';
            let mapCols = $worldImage.is('[data-cols]') ? parseInt($worldImage.attr('data-cols')) : false;
            let mapRows = $worldImage.is('[data-rows]') ? parseInt($worldImage.attr('data-rows')) : false;
            let mapSize = $worldImage.is('[data-size]') ? $worldImage.attr('data-size') : false;
            let dataShow = $worldImage.is('[data-show]') ? $worldImage.attr('data-show') : '';
            let dataHide = $worldImage.is('[data-hide]') ? $worldImage.attr('data-hide') : '';
            let dataFocus = $worldImage.is('[data-focus]') ? $worldImage.attr('data-focus') : '';
            let dataZoom = $worldImage.is('[data-zoom]') ? parseFloat($worldImage.attr('data-zoom')) : 1.0;
            if (!worldImage || typeof worldImage !== 'string' || !worldImage.length){ console.error('initMiniMap() could not determine valid map image!'); return false; }
            if (!mapCols || typeof mapCols !== 'number' || mapCols <= 0){ console.error('initMiniMap() could not determine valid mapCols from map image!'); return false; }
            if (!mapRows || typeof mapRows !== 'number' || mapRows <= 0){ console.error('initMiniMap() could not determine valid mapRows from map image!'); return false; }
            if (!mapSize || typeof mapSize !== 'string' || !mapSize.length){ console.error('initMiniMap() could not determine valid mapSize from map image!'); return false; }
            if (dataShow && !dataHide){ dataHide = 'all'; }
            if (dataHide && !dataShow){ dataShow = 'all'; }
            //console.log('--> mapCols = ' + mapCols + '\n' + '--> mapRows = ' + mapRows + '\n' + '--> mapSize = ' + mapSize);
            //console.log('--> dataShow = ' + dataShow + '\n' + '--> dataHide = ' + dataHide + '\n' + '--> dataFocus = ' + dataFocus + '\n' + '--> dataZoom = ' + dataZoom);

            // Break down the map size string to get more detailed dimensions
            let mapConfig = {};
            mapConfig.cols = mapCols;
            mapConfig.rows = mapRows;
            mapConfig.width = 0;
            mapConfig.height = 0;
            mapConfig.padding = 0;
            mapConfig.tileWidth = 0;
            mapConfig.tileHeight = 0;
            mapConfig.realWidth = 0;
            mapConfig.realHeight = 0;
            mapConfig.imageSource = worldImage;
            // check for padding at the end ("^ 123") and crop it off + save it
            if (mapSize.length
                && mapSize.indexOf(' ^ ') !== -1){
                let sizeParts = mapSize.split(' ^ ');
                mapSize = sizeParts[0];
                mapConfig.padding = parseInt(sizeParts[1]);
                }
            // check for tilesize at the end ("@ 123 x 123") and crop it off + save it
            if (mapSize.length
                && mapSize.indexOf(' @ ') !== -1){
                let sizeParts = mapSize.split(' @ ');
                mapSize = sizeParts[0];
                let tileSizeParts = sizeParts[1].split(' x ');
                mapConfig.tileWidth = parseInt(tileSizeParts[0]);
                mapConfig.tileHeight = parseInt(tileSizeParts[1]);
                }
            // split the remaining part ("123 x 123") to get cols and rows to override
            if (mapSize.length
                && mapSize.indexOf(' x ') !== -1){
                let sizeParts = mapSize.split(' x ');
                mapConfig.cols = parseInt(sizeParts[0]);
                mapConfig.rows = parseInt(sizeParts[1]);
                }
            // if we're missing any critical information we should abort now
            if (!mapConfig.cols || typeof mapConfig.cols !== 'number' || mapConfig.cols <= 0){ console.error('initMiniMap() could not determine valid cols from map size!'); return false; }
            if (!mapConfig.rows || typeof mapConfig.rows !== 'number' || mapConfig.rows <= 0){ console.error('initMiniMap() could not determine valid rows from map size!'); return false; }
            if (!mapConfig.tileWidth || typeof mapConfig.tileWidth !== 'number' || mapConfig.tileWidth <= 0){ console.error('initMiniMap() could not determine valid tileWidth from map size!'); return false; }
            if (!mapConfig.tileHeight || typeof mapConfig.tileHeight !== 'number' || mapConfig.tileHeight <= 0){ console.error('initMiniMap() could not determine valid tileHeight from map size!'); return false; }
            // update values with new calculations
            mapConfig.width = mapConfig.cols * mapConfig.tileWidth;
            mapConfig.height = mapConfig.rows * mapConfig.tileHeight;
            mapConfig.realWidth = mapConfig.width + (mapConfig.padding * 2);
            mapConfig.realHeight = mapConfig.height + (mapConfig.padding * 2);
            //console.log('--> mapConfig = ', mapConfig);
            let allPositions = [];
            for (let row = 1; row <= mapConfig.rows; row++){
                for (let col = 1; col <= mapConfig.cols; col++){
                    allPositions.push(col + '-' + row);
                    }
                }
            //console.log('--> allPositions = ', allPositions);

            // Break apart the show/hide arrays of positions if provided and not-empty
            let showPositions = [];
            let hidePositions = [];
            if (dataShow === 'all'){
                showPositions = Object.values(allPositions);
                if (dataHide && dataHide !== 'all'){
                    hidePositions = dataHide.split(',');
                    showPositions = Object.values(showPositions.filter(function(pos){ return hidePositions.indexOf(pos) === -1; }));
                    }
                }
            if (dataHide === 'all'){
                hidePositions = Object.values(allPositions);
                if (dataShow && dataShow !== 'all'){
                    showPositions = dataShow.split(',');
                    hidePositions = Object.values(hidePositions.filter(function(pos){ return showPositions.indexOf(pos) === -1; }));
                    }
                }
            //console.log('--> showPositions = ', showPositions);
            //console.log('--> hidePositions = ', hidePositions);

            // Collect the source image itself (within the $worldImage div) and then generate a duplicate of it via canvas (so we can mask it)
            let $worldImageSource = $('> img.source', $worldImage);
            let $worldImageOverlay = $('> canvas.overlay', $worldImage);
            //console.log('--> $worldImageSource = ', $worldImageSource.length, $worldImageSource);
            //console.log('--> $worldImageOverlay = ', $worldImageOverlay.length, $worldImageOverlay);
            //if (!$worldImageSource || !$worldImageSource.length){ console.error('initMiniMap() could not find map image source!'); return false; }
            //if (!$worldImageOverlay || !$worldImageOverlay.length){ console.error('initMiniMap() could not find map image overlay canvas!'); return false; }
            if (!$worldImageSource || !$worldImageSource.length){
                //let worldImageSourceMarkup = '<img class="source" src="' + mapConfig.imageSource + '" width="' + mapConfig.realWidth + '" height="' + mapConfig.realHeight + '" />';
                let worldImageSourceMarkup = '<img class="source" width="' + mapConfig.realWidth + '" height="' + mapConfig.realHeight + '" />';
                $worldImage.append(worldImageSourceMarkup);
                $worldImageSource = $('> img.source', $worldImage);
                }
            if (!$worldImageOverlay || !$worldImageOverlay.length){
                let worldImageOverlayMarkup = '<canvas class="overlay" width="' + mapConfig.realWidth + '" height="' + mapConfig.realHeight + '" />';
                $worldImage.append(worldImageOverlayMarkup);
                $worldImageOverlay = $('> canvas.overlay', $worldImage);
                }
            //console.log('--> $worldImageSource = ', $worldImageSource.length, $worldImageSource);
            //console.log('--> $worldImageOverlay = ', $worldImageOverlay.length, $worldImageOverlay);
            // Now that we have both elements, we can proceed with loading the image source and drawing it to the overlay canvas
            let worldImageSource = $worldImageSource.get(0);
            let worldImageOverlay = $worldImageOverlay.get(0);
            let worldImageOverlayMask = document.createElement('canvas');
            worldImageSource.onload = function(){
                //console.log('--> map image source loaded ...');

                // Collect canvas context and start drawing the image to it
                let ctx = worldImageOverlay.getContext('2d');
                let ctx2 = worldImageOverlayMask.getContext('2d', { willReadFrequently: true });
                worldImageOverlay.width = mapConfig.realWidth;
                worldImageOverlay.height = mapConfig.realHeight;
                worldImageOverlayMask.width = mapConfig.realWidth;
                worldImageOverlayMask.height = mapConfig.realHeight;
                //console.log('--> drawing minimap image to canvas ...');
                ctx.clearRect(0, 0, worldImageOverlay.width, worldImageOverlay.height);
                ctx.drawImage(worldImageSource, 0, 0, mapConfig.realWidth, mapConfig.realHeight);
                ctx2.clearRect(0, 0, worldImageOverlayMask.width, worldImageOverlayMask.height);
                ctx2.drawImage(worldImageSource, 0, 0, mapConfig.realWidth, mapConfig.realHeight);
                //console.log('--> worldImageSource = ', worldImageSource);
                //console.log('--> worldImageOverlay = ', worldImageOverlay);
                //console.log('--> worldImageOverlayMask = ', worldImageOverlayMask);

                // Draw over the entire canvas with black to start with for the mask
                ctx2.save();
                ctx2.globalCompositeOperation = "source-in";
                //ctx2.fillStyle = "#303030";
                //ctx2.fillStyle = "#191919";
                ctx2.fillStyle = "rgba(0, 0, 0, 0.3)";
                ctx2.fillRect(0, 0, worldImageOverlay.width, worldImageOverlay.height);
                ctx2.restore();

                // Quick inline function for drawing a black overlay over a given position (considering all dimensions)
                let getPositionRect = function(position){
                    //console.log('%c' + 'mmrpgWorldMap.initMiniMap.getPositionRect(position:' + position + ')', 'color: orange;');
                    if (!position || typeof position !== 'string' || !position.length){ console.error('hideMiniMapPosition() missing required position!'); return false; }
                    let posXY = position.split('-');
                    let col = parseInt(posXY[0]);
                    let row = parseInt(posXY[1]);
                    let padd = mapConfig.padding;
                    let xPos = padd + ((col - 1) * mapConfig.tileWidth);
                    let yPos = padd + ((row - 1) * mapConfig.tileHeight);
                    let xWidth = mapConfig.tileWidth;
                    let yHeight = mapConfig.tileHeight;
                    let positionRect = { x: xPos, y: yPos, width: xWidth, height: yHeight  };
                    return positionRect;
                    };

                // Loop through the list of hidden positions and copy over masked pixels to the overlay where hidden
                for (let i = 0; i < hidePositions.length; i++){
                    let position = hidePositions[i];
                    let positionRect = getPositionRect(position);
                    //console.log('-> hiding position ' + position + ' ...');
                    //console.log('--> w/ positionRect = ', positionRect);
                    let imageData = ctx2.getImageData(positionRect.x, positionRect.y, positionRect.width, positionRect.height);
                    ctx.putImageData(imageData, positionRect.x, positionRect.y);
                    }
                // Destroy the other canvas as we don't need it anymore
                worldImageOverlayMask = null;
                ctx2 = null;

                // Given what we know about the viewport size, the (real) map size, and the currently focused position
                // we should adjust the transform on the image container within to ensure focus is in center of viewport
                if (dataFocus){
                    //console.log('--> focusing on position ' + dataFocus + ' ...');
                    let focusRect = getPositionRect(dataFocus);
                    //console.log('--> w/ focusRect = ', focusRect);
                    let focusX = focusRect.x + (focusRect.width / 2);
                    let focusY = focusRect.y + (focusRect.height / 2);
                    let offsetX = Math.round((viewportWidth / 2) - focusX);
                    let offsetY = Math.round((viewportHeight / 2) - focusY);
                    $worldImage.css({transform: 'translate(' + offsetX + 'px, ' + offsetY + 'px)' });
                    }

                // Mark the world mini map as ready and attempt to run the onComplete callback if provided
                setTimeout(function(){
                    worldMiniMapReady = true;
                    $worldViewport.addClass('ready');
                    if (onComplete && typeof onComplete === 'function'){ onComplete(); }
                    }, 100);

                };
            worldImageSource.src = mapConfig.imageSource;

            }

        // Intialize the area viewport next with a cloned version of the terrain (this one shows topography better)
        let areaMiniMapReady = false;
        let initAreaMiniMap = function(onComplete){
            //console.log('%c' + '~initAreaMiniMap()', 'color: magenta;');

            // Collect references to viewport elements
            let $areaGrid = $('.grid', $areaViewport);
            let $areaImage = $('.image', $areaViewport);
            let $areaMarker = $('.marker', $areaViewport);

            // Append the map terrain if it's not there already for visual reference
            let $areaViewportTerrain = $('> canvas.terrain', $areaImage);
            let $areaViewportOverlay = $('> canvas.overlay', $areaImage);
            if (!$areaViewportTerrain || !$areaViewportTerrain.length
                || !$areaViewportOverlay || !$areaViewportOverlay.length){

                // Collect the base terrain layer from the big canvas map to clone into the mini-map area view
                let $baseTerrainLayer = $('.layer[data-layer="terrain"]', $canvasMap);
                let $baseTerrainCanvas = $('canvas[data-canvas="terrain"]', $baseTerrainLayer);
                let baseTerrainCanvas = $baseTerrainCanvas.get(0);

                // Determine the scaling necessary to resize the base terrain to the mini-map area size
                let fullTileSize = _config.mapTileSize[0];
                let miniTileSize = _config.minimapAreaTileSize;
                let fromTileSize = fullTileSize;
                let toTileSize = miniTileSize;
                let baseTerrainWidth = baseTerrainCanvas.width;
                let baseTerrainHeight = baseTerrainCanvas.height;
                let miniTerrainWidth = Math.ceil((toTileSize / fromTileSize) * baseTerrainWidth);
                let miniTerrainHeight = Math.ceil((toTileSize / fromTileSize) * baseTerrainHeight);

                // Define a quick inline function for drawing portal, battle, etc. markers to the map overlay
                let drawAreaOverlayMarkers = function(ctx){
                    //console.log('%c' + 'mmrpgWorldMap.initMiniMap.drawAreaOverlayMarkers(ctx)', 'color: orange;');
                    // Define a quick inline function for drawing markers to this overlay
                    let drawOverlayMarker = function(col, row, shape, size, color){
                        //console.log('%c' + 'mmrpgWorldMap.initMiniMap.drawAreaOverlayMarkers.drawOverlayMarker(col:' + col + ', row:' + row + ', shape:' + shape + ', size:' + size + ', color:' + color + ')', 'color: orange;');
                        if (!col || typeof col !== 'number'){ return false; }
                        if (!row || typeof row !== 'number'){ return false; }
                        shape = typeof shape === 'string' && shape.length ? shape : 'square';
                        size = typeof size === 'number' && size > 0 ? size : 10;
                        color = typeof color === 'string' && color.length ? color : '#ffffff';
                        let x = Math.round(((col - 1) * miniTileSize) + (miniTileSize / 2));
                        let y = Math.round(((row - 1) * miniTileSize) + (miniTileSize / 2));
                        if (shape !== 'circle'){
                            x -= Math.round(size / 2);
                            y -= Math.round(size / 2);
                            }
                        if (shape === 'square'){
                            ctx.fillStyle = color;
                            ctx.fillRect(x, y, size, size);
                            }
                        if (shape === 'circle'){
                            ctx.fillStyle = color;
                            ctx.beginPath();
                            ctx.arc(x, y, size / 2, 0, Math.PI * 2, true);
                            ctx.fill();
                            }
                        return;
                        };
                    // Draw blue circles on portal locations
                    let portalSymbols = _config.mapPortalSymbols || [];
                    let portalsIndex = _config.mapPortalsIndex || {};
                    let portalSymbolsKeys = Object.keys(portalSymbols);
                    if (portalSymbolsKeys.length > 0){
                        let drawPortalMarker = function(col, row, kind){
                            let shape = 'square', size = 4, color = '#3f83c7';
                            if (kind === 'direction'){ color = '#cacaca'; }
                            return drawOverlayMarker(col, row, shape, size, color);
                            };
                        for (let i = 0; i < portalSymbolsKeys.length; i++){
                            let portalKey = portalSymbolsKeys[i];
                            let portalToken = portalSymbols[portalKey];
                            let portalInfo = portalsIndex[portalToken];
                            //console.log('--> drawing portal marker for ' + portalToken + ' at position key ' + portalKey + ' ...');
                            //console.log('--> w/ portalInfo =', portalInfo);
                            let portalPosition = portalKey.split('-').map(function(val){ return parseInt(val); });
                            let portalKind = portalInfo.direction ? 'direction' : 'teleport';
                            drawPortalMarker(portalPosition[0], portalPosition[1], portalKind);
                            }
                        }
                    // Draw red squares where enemy encounters
                    let battleSymbols = _config.mapBattleSymbols || [];
                    let battlesIndex = _config.mapBattlesIndex || {};
                    //console.log('--> battleSymbols = ', battleSymbols);
                    //console.log('--> battlesIndex = ', battlesIndex);
                    let battleSymbolsKeys = Object.keys(battleSymbols);
                    if (battleSymbolsKeys.length > 0){
                        let drawEnemyMarker = function(col, row, kind){
                            let shape = 'square', color = '#c73f3f', size = 1;
                            if (kind === 'mecha'){ size = 2; }
                            else if (kind === 'master'){ size = 3; }
                            else if (kind === 'boss'){ size = 4; }
                            return drawOverlayMarker(col, row, shape, size, color);
                            };
                        for (let i = 0; i < battleSymbolsKeys.length; i++){
                            let battleKey = battleSymbolsKeys[i];
                            let battleToken = battleSymbols[battleKey];
                            let battleInfo = battlesIndex[battleToken];
                            //console.log('--> drawing battle symbol for ' + battleToken + ' at position key ' + battleKey + ' ...');
                            //console.log('--> w/ battleInfo =', battleInfo);
                            let battlePosition = battleKey.split('-').map(function(val){ return parseInt(val); });
                            let battleKind = battleInfo.kind2 ? battleInfo.kind2 : battleInfo.kind;
                            drawEnemyMarker(battlePosition[0], battlePosition[1], battleKind);
                            }
                        }
                    // Return true now that we're done
                    return true;
                    };

                // Update the area grid and image containers with the newly calculated mini terrain dimensions
                $areaGrid.css({width: miniTerrainWidth + 'px', height: miniTerrainHeight + 'px', transform: 'translate(0, 0)'});
                $areaImage.css({width: miniTerrainWidth + 'px', height: miniTerrainHeight + 'px', transform: 'translate(0, 0)'});

                // Clone the actual terrain canvas into the area mini-map and resize it accordingly
                let $areaTerrainCanvas = $baseTerrainCanvas.clone(); // cloned to keep sizing consistent
                let areaTerrainContext = $areaTerrainCanvas.get(0).getContext('2d');
                $areaTerrainCanvas.removeAttr('data-canvas').removeAttr('style').addClass('terrain');
                areaTerrainContext.drawImage(baseTerrainCanvas, 0, 0);
                $areaImage.append($areaTerrainCanvas);
                $areaViewportTerrain = $('> canvas.terrain', $areaImage);

                // Create a secondary canvas for adding dots/markers/symbols on top of the terrain
                let $areaOverlayCanvas = $('<canvas class="overlay" width="' + miniTerrainWidth + '" height="' + miniTerrainHeight + '"></canvas>');
                let areaOverlayContext = $areaOverlayCanvas.get(0).getContext('2d');
                drawAreaOverlayMarkers(areaOverlayContext);
                $areaImage.append($areaOverlayCanvas);
                $areaViewportOverlay = $('> canvas.overlay', $areaImage);

                }

            // Mark the world mini map as ready and attempt to run the onComplete callback if provided
            setTimeout(function(){
                areaMiniMapReady = true;
                $areaViewport.addClass('ready');
                if (onComplete && typeof onComplete === 'function'){ onComplete(); }
                }, 100);

            }

        // Define a function for changing the map view when the buttons are clicked
        let updateMiniMapView = function(newView){
            //console.log('%c' + '~updateMiniMapView(newView:' + newView + ')', 'color: magenta;');
            if (!newView || typeof newView !== 'string' || !newView.length){ console.error('updateMiniMapView() missing required newView!'); return false; }
            let $viewport, $button;
            if (newView === 'world'){ $viewport = $worldViewport; $button = $worldViewButton; }
            else if (newView === 'area'){ $viewport = $areaViewport; $button = $areaViewButton; }
            else { console.error('updateMiniMapView() received invalid newView: ' + newView); return false; }
            if ($viewport.is('.active')){ return true; } // already active, no need to change
            let $marker = $('.marker', $viewport);
            $('.viewport', $minimapOverview).removeClass('active');
            $('.button', $minimapButtons).removeClass('active');
            $viewport.addClass('active');
            $button.addClass('active');
            return true;
            };

        // Define an onComplete callback to run once both viewports have been initialized
        let onComplete = function(){
            //console.log('%c' + '~initMiniMap.onComplete()', 'color: magenta;');
            if (!worldMiniMapReady || !areaMiniMapReady){ return false; }
            let isFastFade = $thisWorld.is('.fastfade') ? true : false;
            if (!isFastFade){
                setTimeout(function(){ updateMiniMapView('world', true); }, 100);
                setTimeout(function(){ updateMiniMapView('area'); }, 2900);
                } else {
                setTimeout(function(){ updateMiniMapView('area'); }, 100);
                }
            return true;
            };

        // Delegate click events to the two buttons in the mini-map for changing views
        $minimapButtons.delegate('.button[data-view]', 'click', function(e){
            e.preventDefault();
            e.stopPropagation();
            if (_self.worldIsBusy()){ return; }
            //console.log('%c' + 'Mini-map view button clicked!', 'color: cyan;');
            let $button = $(this);
            let newView = $button.attr('data-view');
            return updateMiniMapView(newView);
            });

        // Initialize the two mini maps and then call the onComplete callback
        initWorldMiniMap(onComplete);
        initAreaMiniMap(onComplete);

        // Return true on success
        return true;
        }

    // Define a quick event for showing the title banner w/ whatever title and subtitle text is provided w/ optional custom timeout for autohide
    showTitleBanner(titleText, subtitleText, showBreadcrumb, autoHideTimeout){
        //console.log('%c' + 'mmrpgWorldMap.showTitleBanner()', 'color: magenta;');
        if (!titleText || typeof titleText !== 'string' || !titleText.length){ console.error('showTitleBanner() missing required titleText!'); return; }
        if (subtitleText && typeof subtitleText !== 'string'){ console.error('showTitleBanner() received invalid subtitleText!'); return; }
        showBreadcrumb = typeof showBreadcrumb === 'boolean' ? showBreadcrumb : false; // default to false if not provided
        autoHideTimeout = typeof autoHideTimeout === 'number' ? autoHideTimeout : 3000; // default to 3 seconds if not provided
        let _self = this;
        let _elements = _self.elements;
        let $thisCanvas = _elements.canvas;
        let $canvasWrapper = $('> .wrapper', $thisCanvas);
        let $titleBanner = _elements.titleBanner;
        if (!$titleBanner || !$titleBanner.length){
            let titleBannerMarkup = '';
            titleBannerMarkup += '<div id="title-banner" class="chrome">';
                titleBannerMarkup += '<div class="wrap">';
                    titleBannerMarkup += '<h1 class="title">' + titleText + (showBreadcrumb ? ' &raquo;' : '') + '</h1>';
                    titleBannerMarkup += '<h2 class="subtitle">' + subtitleText + '</h2>';
                titleBannerMarkup += '</div>';
            titleBannerMarkup += '</div>';
            $canvasWrapper.append(titleBannerMarkup);
            $titleBanner = $('#title-banner', $thisCanvas);
            _elements.titleBanner = $titleBanner;
            } else {
            $titleBanner.removeClass('active');
            $titleBanner.find('.title').html(titleText + (showBreadcrumb ? ' &raquo;' : ''));
            $titleBanner.find('.subtitle').html(subtitleText);
            }
        setTimeout(function(){
            $titleBanner.removeClass('hidden');
            $titleBanner.addClass('active');
            }, 100);
        if (autoHideTimeout > 0){
            setTimeout(function(){
                $titleBanner.removeClass('active');
                setTimeout(function(){
                    $titleBanner.find('.title').html('');
                    $titleBanner.find('.subtitle').html('');
                    $titleBanner.addClass('hidden');
                    }, (autoHideTimeout * 2));
                }, autoHideTimeout);
            }
        // Return no specific result
        return;
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

    // -- MESSAGE HELPER METHODS -- //

    // Define a quick function for showing a message (perhaps for an item pickup) immediately in the map world UI
    // (first by  queuing a world message to be shown when next ready, rather than all at once)
    showWorldMessage(messageText, showAfter, $insertAfter){
        //console.log('%c' + 'mmrpgWorldMap.showWorldMessage()', 'color: magenta;');
        //console.log('w/ messageText = ', messageText, '\n' + 'w/ showAfter = ' + showAfter + '\n' + 'w/ $insertAfter = ', $insertAfter);
        showAfter = showAfter || 0;
        $insertAfter = $insertAfter || null;
        let _self = this;
        let _selfRef = _self.showWorldMessage;
        if (typeof _selfRef.messagesQueue === 'undefined'){ _selfRef.messagesQueue = []; }
        _selfRef.messagesQueue.push({
            messageText: messageText,
            showAfter: showAfter,
            $insertAfter: $insertAfter
            });
        _self.__showNextWorldMessage();
        }
    __showNextWorldMessage(){
        //console.log('%c' + 'mmrpgWorldMap.__showNextWorldMessage()', 'color: magenta;');
        let _self = this;
        let _selfRef = _self.showWorldMessage;
        let _config = _self.config.mapMessages;
        let maxConcurrent = _config.maxConcurrent || 1;
        let queueStagger = _config.queueStagger || 200;
        let staggerDelay = _config.staggerDelay || 900;
        if (typeof _selfRef.activeCount === 'undefined'){ _selfRef.activeCount = 0; }
        if (typeof _selfRef.isProcessing === 'undefined'){ _selfRef.isProcessing = false; }
        if (_selfRef.isProcessing === true) return;
        if (_selfRef.activeCount >= maxConcurrent) return;
        if (!_selfRef.messagesQueue || _selfRef.messagesQueue.length <= 0) return;
        let nextMessage = _selfRef.messagesQueue.shift();
        if (!nextMessage) return;
        let subCount = Array.isArray(nextMessage.messageText) ? nextMessage.messageText.length : 1;
        let timeUntilReadyForNext = (subCount > 1)
            ? ((subCount - 1) * staggerDelay) + queueStagger
            : queueStagger;
        _selfRef.isProcessing = true;
        setTimeout(function(){
            _selfRef.isProcessing = false;
            _self.__showNextWorldMessage();
            }, timeUntilReadyForNext);
        _selfRef.activeCount++;
        _selfRef.onMessagesComplete = function(){
            _selfRef.activeCount--;
            _self.__showNextWorldMessage();
            };
        _self.__actuallyShowWorldMessage(nextMessage);
        }
    __actuallyShowWorldMessage(messageData){
        //console.log('%c' + 'mmrpgWorldMap.__actuallyShowWorldMessage()', 'color: magenta;');
        let _self = this;
        let _config = _self.config.mapMessages;
        let _selfRef = _self.showWorldMessage;
        let $messageDisplay = _self.elements.messageDisplay;
        let $messageWrapper = $messageDisplay.find('.wrapper');
        let staggerDelay = _config.staggerDelay || 900;
        let holdDuration = _config.holdDuration || 4000;
        let fadeDuration = _config.fadeDuration || 1000;
        let rawText = messageData.messageText;
        let baseDelay = messageData.showAfter || 0;
        let $initialInsert = messageData.$insertAfter || null;
        let messages = Array.isArray(rawText) ? rawText : [rawText];
        let $lastBlock = $initialInsert;
        let batchKeys = []; // To track all IDs in this specific batch
        let lastRevealTime = 0;
        let batchTimestamp = Date.now();
        for (let i = 0; i < messages.length; i++){
            let text = messages[i];
            let messageKey = batchTimestamp + '_' + i;
            batchKeys.push(messageKey);
            let revealTime = baseDelay + (i * staggerDelay) + 100;
            if (revealTime > lastRevealTime) { lastRevealTime = revealTime; }
            let isSubtext = (i > 0) || ($lastBlock && $lastBlock.hasClass('message'));
            let messageClass = 'message pending' + (isSubtext ? ' subtext' : '');
            let messageMarkup = '<div class="' + messageClass + '" data-key="' + messageKey + '">' + text + '</div>';
            let $message = $(messageMarkup);
            if ($lastBlock){ $message.insertAfter($lastBlock); }
            else { $message.prependTo($messageWrapper); }
            $lastBlock = $message;
            (function(k, rt){
                setTimeout(function(){
                    $messageWrapper.find('.message[data-key="' + k + '"]').removeClass('pending');
                    }, rt);
                })(messageKey, revealTime);
            }
        let sharedHideTime = lastRevealTime + holdDuration;
        let sharedRemoveTime = sharedHideTime + fadeDuration;
        setTimeout(function(){
            for (let j = 0; j < batchKeys.length; j++){ $messageWrapper.find('.message[data-key="' + batchKeys[j] + '"]').addClass('hidden'); }
            }, sharedHideTime);
        setTimeout(function(){
            for (let j = 0; j < batchKeys.length; j++){ $messageWrapper.find('.message[data-key="' + batchKeys[j] + '"]').remove(); }
            }, sharedRemoveTime);
        setTimeout(function(){
            if (typeof _selfRef.onMessagesComplete === 'function'){ _selfRef.onMessagesComplete.call(_self); }
            }, sharedRemoveTime + 100);
        return true;
        }

    // -- MISC HELPER METHODS -- //

    // Define a quick reusable method for cloning object data via JSON serialization
    getClonedObject(obj){
        return JSON.parse(JSON.stringify(obj));
        }

}
