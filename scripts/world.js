
// Expand the game settings object with a variable world specific data
gameSettings.worldConfig = {
    userId: 0,
    playerId: 0,
    playerToken: 'player',
    playerRobots: ['0_robot'],
    playerAbilities: ['buster-shot'],
    playerItemsIndex: {},
    playerStarsIndex: {},
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
        maxConcurrent: 8,   // how many messages can be on screen at once
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
    mapBlockSymbols: {},
    mapBlocksIndex: {},
    mapHazardSymbols: {},
    mapHazardsIndex: {},
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
        id: 0,
        token: 'player',
        position: '0-0',
        direction: '',
        team: [],
        robots: {},
        items: {},
        abilities: {},
        stars: {},
        },
    items: {},
    stars: {},
    abilities: {},
    buttons: {},
    switches: {},
    blocks: {},
    hazards: {},
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
        let $progressTracker = $('#progress-tracker', $thisWorld);
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
        _elements.progressTracker = $progressTracker;
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
        // If player stars were defined [index] in the predefined config, copy them over to the state
        let _playerStarsIndex = _config.playerStarsIndex;
        if (Object.keys(_playerStarsIndex).length){
            //console.log('---> initWorldMap() found ' + Object.keys(_playerStarsIndex).length + ' player stars to initialize!');
            let livePlayerStars = _self.getClonedObject(_playerStarsIndex);
            //console.log('---> adding stars to player state:', livePlayerStars);
            _worldPlayer.stars = livePlayerStars;
            //console.log('---> initWorldMap() livePlayerStars =', livePlayerStars);
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
            _self.moveToPosition(startPosition, function(){
                console.log('%c' + 'MMRPG WORLD IS READY!', 'color: lime;');
                _world.isReady = true;
                //_self.togglePerspectiveMode(true); // TEMP TEMP TEMP
                $thisWorld.removeClass('hidden');
                $thisWorld.addClass('ready');
                $canvasMap.addClass('ready');
                setTimeout(function(){
                    _self.triggerWindowEventsPull();
                    _self.triggerOnWorldReady();
                    _self.startIdleAnimation();
                    _world.isBusy = false;
                    gameSettings.gameHasStarted = true;
                    //_self.showWorldMessage('<span style="color: cyan;">triggerOnWorldReady()</span>');
                    }, 900);
                }, true, false, fakeOldPosition);
            setTimeout(function(){
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
        let activeSpriteDirections = ['up', 'up-right', 'right', 'down-right', 'down', 'down-left', 'left', 'up-left'];
        let activeSpriteDirectionsData = (function(directions){
            let data = {};
            for (let key = 0; key < directions.length; key++){
                let dir = directions[key];
                data[dir] = _self.getSpriteData('active-' + dir);
                if (!data[dir]){ console.warn('drawTileToCanvas() unable to find active-' + dir + ' sprite data for layer ' + layerToken + '!'); }
                }
            return data;
            })(activeSpriteDirections);
        //console.log('activeSpriteDirections = ', activeSpriteDirections);
        //console.log('activeSpriteDirectionsData = ', activeSpriteDirectionsData);
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
            //drawSpriteFromData(activeSpriteData);
            let playerDirection = _world.player.direction;
            drawSpriteFromData(activeSpriteDirectionsData[playerDirection]);
            //console.log('playerDirection =', playerDirection);
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
        exclude.blocks = typeof exclude.blocks === 'boolean' ? exclude.blocks : true;
        exclude.hazards = typeof exclude.hazards === 'boolean' ? exclude.hazards : true;
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
        let blocksIndex = _config.mapBlocksIndex;
        let hazardsIndex = _config.mapHazardsIndex;
        let battlesIndex = _config.mapBattlesIndex;
        let battleSymbols = _config.mapBattleSymbols;
        let rivalSymbols = _config.mapRivalSymbols;
        let portalSymbols = _config.mapPortalSymbols;
        let blockSymbols = _config.mapBlockSymbols;
        let hazardSymbols = _config.mapHazardSymbols;
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

        // If we are to exclude blocks, make sure we remove those positions
        let battleBlockKeys = Object.keys(blockSymbols);
        if (exclude.blocks && blockSymbols){
            //console.log('---> checking battleBlockKeys =', battleBlockKeys);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                //console.log('---> checking tileKey:', tileKey, 'against blockSymbols:', battleBlockKeys);
                if (battleBlockKeys.includes(tileKey)){
                    //console.log('---> tileKey:', tileKey, 'is a block, removing from walkableMapTiles');
                    return false; // remove this tile
                    } else {
                    //console.log('---> tileKey:', tileKey, 'is not a block, keeping in walkableMapTiles');
                    }
                return true; // keep this tile
                }));
            //console.log('---> walkableMapTiles (post-blocks) =', walkableMapTiles);
            }

        // If we are to exclude hazards, make sure we remove those positions (only when not removed though)
        let battleHazardKeys = Object.keys(hazardSymbols);
        if (exclude.hazards && hazardSymbols){
            //console.log('---> checking hazardSymbolKeys =', battleHazardKeys);
            walkableMapTiles = Object.values(walkableMapTiles.filter(function(tileKey){
                //console.log('---> checking tileKey:', tileKey, 'against hazardSymbolKeys:', battleHazardKeys);
                if (battleHazardKeys.includes(tileKey)){
                    //console.log('---> tileKey:', tileKey, 'is a hazard, checking if locked...');
                    let hazardInfo = hazardsIndex[hazardSymbols[tileKey]] || false;
                    //console.log('---> hazardInfo =', hazardInfo);
                    if (hazardInfo.locked){
                        //console.log('---> hazard at ' + tileKey + ' is locked, excluding from walkableMapTiles');
                        return false; // remove this tile
                        } else {
                        //console.log('---> hazard at ' + tileKey + ' is not locked, keeping in walkableMapTiles');
                        }
                    }
                return true; // keep this tile
                }));
            //console.log('---> walkableMapTiles (post-hazards) =', walkableMapTiles);
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

    // Quick function for getting the position a given player/cursor/etc. is facing given current position, direction facing, and range
    getRelativePositionByDirection(position, direction, range){
        //console.log('%c' + 'mmrpgWorldMap.getRelativePositionByDirection(position:' + position + ', direction:' + direction + ', range:' + range + ')', 'color: magenta;');
        if (!position || typeof position !== 'string'){ console.warn('position should be non-empty string!'); return false; }
        if (!direction || typeof direction !== 'string'){ console.warn('direction should be non-empty string!'); return false; }
        range = typeof range === 'number' ? range : 1;
        let curPos = position.split('-').map(function(n){ return parseInt(n); });
        let relDir = direction.split('-');
        let newPos = Object.values(curPos);
        newPos[0] += relDir.includes('left') ? (range * -1) : relDir.includes('right') ? range : 0;
        newPos[1] += relDir.includes('up') ? (range * -1) : relDir.includes('down') ? range : 0;
        newPos = newPos.join('-');
        //console.log('-> curPos: ', curPos);
        //console.log('-> relDir: ', relDir);
        //console.log('-> newPos: ', newPos);
        return newPos;
        };

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
        let thisNewPos = thisNewCol + '-' + thisNewRow;
        let thisNewDir = thisShiftDir.replace(/ and /g, '-');
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
            _worldCursor.direction = thisNewDir;
            _worldCursor.moved = cursorHasMoved;
            _worldPlayer.position = _worldCursor.position;
            _worldPlayer.direction = _worldCursor.direction;
            _worldCursor.positionXY = [tileOffsetX, tileOffsetY];
            //console.log('_worldCursor =', '\n-> col =', _worldCursor.col, '\n-> row =', _worldCursor.row, '\n-> position =', _worldCursor.position, '\n-> direction =', _worldCursor.direction, '\n-> moved =', _worldCursor.moved);
            $cursorSprite.attr('data-col', _worldCursor.col);
            $cursorSprite.attr('data-row', _worldCursor.row);
            $cursorSprite.attr('data-dir', _worldCursor.direction);
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
        $canvasMap.css({ transformOrigin: 'left top', transform: 'translate(' + mapTranslateX + 'px, ' + mapTranslateY + 'px) translateZ(0) scale(' + worldZoom + ')' });
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
            setTimeout(function(){
                _self.refreshPosition(null, true, false);
                _self.scrollMap(null, null, true);
                }, 100);

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

    // Quick function for starting an interval timer that animations on-screen encounter sprites in a while
    async startIdleAnimation(){
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
    async playSoundEffect(soundName, options){
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
        let _worldBlocks = _world.blocks;
        let _worldHazards = _world.hazards;
        let $thisWorld = _elements.world;
        let lastPlayer = _worldPlayer.token;
        let lastPlayerTeam = _worldPlayer.team;
        let lastPlayerRobots = _worldPlayer.robots;
        let lastPlayerAbilities = _worldPlayer.abilities;
        let lastPlayerItems = _worldPlayer.items;
        let lastPlayerStars = _worldPlayer.stars;
        let lastPlayerWorld = _config.mapWorld;
        let lastPlayerWorldMap = _config.mapWorld + '__' + _config.mapToken;
        let lastPlayerPosition = _worldPlayer.position;
        let lastPlayerDirection = _worldPlayer.direction;
        let lastWorldButtons = {}; lastWorldButtons[lastPlayerWorldMap] = _worldButtons;
        let lastWorldSwitches = {}; lastWorldSwitches[lastPlayerWorldMap] = _worldSwitches;
        let lastWorldItems = {}; lastWorldItems[lastPlayerWorldMap] = _worldItems;
        let lastWorldAbilities = {}; lastWorldAbilities[lastPlayerWorldMap] = _worldAbilities;
        let lastWorldSymbols = {}; lastWorldSymbols[lastPlayerWorldMap] = _worldSymbols;
        let lastWorldBlocks = {}; lastWorldBlocks[lastPlayerWorldMap] = _worldBlocks;
        let lastWorldHazards = {}; lastWorldHazards[lastPlayerWorldMap] = _worldHazards;
        let worldData = {
            lastPlayer,
            lastPlayerTeam,
            lastPlayerRobots,
            lastPlayerAbilities,
            lastPlayerItems,
            lastPlayerStars,
            lastPlayerWorld,
            lastPlayerWorldMap,
            lastPlayerPosition,
            lastPlayerDirection,
            lastWorldButtons,
            lastWorldSwitches,
            lastWorldItems,
            lastWorldAbilities,
            lastWorldSymbols,
            lastWorldBlocks,
            lastWorldHazards,
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
        _self.triggerOnWorldReady();
        return;
        }

    // Quick function for triggering any worldReady events that have been queued up
    triggerOnWorldReady(){
        //console.log('%c' + 'mmrpgWorldMap.triggerOnWorldReady()', 'color: magenta;');
        let _self = this;
        let _config = _self.config;
        let _world = _self.state;
        if (!_world.hasLoaded || !_world.isReady){ return false; }
        //console.log('%c' + '~mmrpgWorldMap.triggerOnWorldReady()', 'color: magenta;');
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

    // Define some quick functions for getting type spans for certain object types
    getCustomNameSpan(customText, typeOrTypes){
        //console.log('%c' + 'mmrpgWorldMap.getCustomNameSpan(customText:' + customText + ', typeOrTypes:' + typeOrTypes + ')', 'color: magenta;');
        let spanTypes = 'none';
        if (typeof typeOrTypes === 'string' && typeOrTypes.length){ spanTypes = typeOrTypes; }
        else if (Array.isArray(typeOrTypes) && typeOrTypes.length){ spanTypes = typeOrTypes.join(' '); }
        return '<span class="type ' + spanTypes + '">' + (customText || 'Text') + '</span>';
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
            _self.playSoundEffect('icon-click-mini');
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

    // -- MISC HELPER METHODS -- //

    // Define a quick reusable method for cloning object data via JSON serialization
    getClonedObject(obj){
        return JSON.parse(JSON.stringify(obj));
        }

}
