
// -- WORLD MENUS METHODS -- //

// Define a quick function for hovering a canvas UI object
function hoverCanvasMenuObject(obj, e, sfx){
    //console.log('%c' + 'mmrpgWorldMap.hoverCanvasMenuObject()', 'color: magenta;');
    let _self = this;
    let _elements = _self.elements;
    let $thisCanvas = _elements.canvas;
    let $object = $(obj);
    if (_self.worldIsBusy()){ return; }
    if ($object.is('.disabled')){ return; }
    if ($object.closest('.chrome').is('.disabled')){ return; }
    $thisCanvas.find('.hovered').removeClass('hovered');
    if (sfx){ _self.playSoundEffect(sfx); }
    $object.addClass('hovered');
    return true;
    }
function unhoverCanvasMenuObject(obj, e){
    //console.log('%c' + 'mmrpgWorldMap.unhoverCanvasMenuObject()', 'color: magenta;');
    let _self = this;
    let $object = $(obj);
    $object.removeClass('hovered');
    return true;
    }

// Quick function for initializing the menu's BACK button w/ relevant click events
function initMenuBackButton($thisWorld, $backButton){
    //console.log('%c' + 'mmrpgWorldMap.initMenuBackButton($thisWorld: ' + typeof $thisWorld + ', $backButton: ' + typeof $backButton + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuBackButton() missing required $thisWorld!'); return false; }
    if (!$backButton || !$backButton.length){ console.error('initMenuBackButton() missing required $backButton!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    $backButton.bind('mouseenter', function(e){ hoverCanvasObject.call(_self, this, e, 'icon-hover'); });
    $backButton.bind('mouseleave', function(e){ unhoverCanvasObject.call(_self, this, e); });
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
    return true;
    }

// Quick function for initializing the menu's HOME button w/ relevant click events
function initMenuHomeButton($thisWorld, $homeButton){
    //console.log('%c' + 'mmrpgWorldMap.initMenuHomeButton($thisWorld: ' + typeof $thisWorld + ', $homeButton: ' + typeof $homeButton + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuHomeButton() missing required $thisWorld!'); return false; }
    if (!$homeButton || !$homeButton.length){ console.error('initMenuHomeButton() missing required $homeButton!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    $homeButton.bind('mouseenter', function(e){ hoverCanvasObject.call(_self, this, e, 'icon-hover'); });
    $homeButton.bind('mouseleave', function(e){ unhoverCanvasObject.call(_self, this, e); });
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
    return true;
    }

// Quick function for initializing the menu's RESET button w/ relevant click events
function initMenuResetButton($thisWorld, $resetButton){
    //console.log('%c' + 'mmrpgWorldMap.initMenuResetButton($thisWorld: ' + typeof $thisWorld + ', $resetButton: ' + typeof $resetButton + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuResetButton() missing required $thisWorld!'); return false; }
    if (!$resetButton || !$resetButton.length){ console.error('initMenuResetButton() missing required $resetButton!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    $resetButton.bind('mouseenter', function(e){ hoverCanvasObject.call(_self, this, e, 'icon-hover'); });
    $resetButton.bind('mouseleave', function(e){ unhoverCanvasObject.call(_self, this, e); });
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
    return true;
    }

// Quick function for initializing the menu's PLAYER SWITCHER w/ relevant click events
function initMenuPlayerSwitcher($thisWorld, $playerSwitcher){
    //console.log('%c' + 'mmrpgWorldMap.initMenuPlayerSwitcher($thisWorld: ' + typeof $thisWorld + ', $playerSwitcher: ' + typeof $playerSwitcher + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuPlayerSwitcher() missing required $thisWorld!'); return false; }
    if (!$playerSwitcher || !$playerSwitcher.length){ console.error('initMenuPlayerSwitcher() missing required $playerSwitcher!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    let $playerButtons = $('.team-player[data-player]', $playerSwitcher);
    $playerButtons.bind('mouseenter', function(e){ if (hoverCanvasObject.call(_self, this, e, 'icon-hover')){ $(this).find('.sprite.player > .sprite').attr('data-frame', '01'); } }); // taunt
    $playerButtons.bind('mouseleave', function(e){ if (unhoverCanvasObject.call(_self, this, e)){ $(this).find('.sprite.player > .sprite').attr('data-frame', '00'); } }); // base
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
    return true;
    }

// Quick function for initializing the menu's MINIMAP OVERVIEW button w/ relevant click events
function initMenuMinimapOverview($thisWorld, $minimapOverview){
    //console.log('%c' + 'mmrpgWorldMap.initMenuMinimapOverview($thisWorld: ' + typeof $thisWorld + ', $minimapOverview: ' + typeof $minimapOverview + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuMinimapOverview() missing required $thisWorld!'); return false; }
    if (!$minimapOverview || !$minimapOverview.length){ console.error('initMenuMinimapOverview() missing required $minimapOverview!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    $minimapOverview.bind('mouseenter', function(e){ hoverCanvasObject.call(_self, this, e, 'icon-hover'); });
    $minimapOverview.bind('mouseleave', function(e){ unhoverCanvasObject.call(_self, this, e); });
    $minimapOverview.find('.button').bind('mouseenter', function(e){ hoverCanvasObject.call(_self, this, e, 'icon-hover'); });
    $minimapOverview.find('.button').bind('mouseleave', function(e){ unhoverCanvasObject.call(_self, this, e); });
    return true;
    }

// Quick function for initializing the menu's ZOOM CONTROLS buttons w/ relevant click events
function initMenuZoomControls($thisWorld, $zoomControls){
    //console.log('%c' + 'mmrpgWorldMap.initMenuZoomControls($thisWorld: ' + typeof $thisWorld + ', $zoomControls: ' + typeof $zoomControls + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuZoomControls() missing required $thisWorld!'); return false; }
    if (!$zoomControls || !$zoomControls.length){ console.error('initMenuZoomControls() missing required $zoomControls!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    let $zoomButtons = $('.zoom-control', $zoomControls);
    let $zoomInButton = $zoomButtons.filter('.zoom-in');
    let $zoomOutButton = $zoomButtons.filter('.zoom-out');
    $zoomButtons.bind('mouseenter', function(e){ hoverCanvasObject.call(_self, this, e, 'icon-hover'); });
    $zoomButtons.bind('mouseleave', function(e){ unhoverCanvasObject.call(_self, this, e); });
    $zoomButtons.bind('click', function(e){
        //console.log('zoom button has been clicked');
        let $zoomButton = $(this);
        if ($zoomButton.is('.disabled')){ return false; }
        let zoomDir = false;
        if ($zoomButton.is('.zoom-out')){ zoomDir = 'out'; }
        else if ($zoomButton.is('.zoom-in')){ zoomDir = 'in'; }
        //console.log('-> zoomDir =', zoomDir);
        if (!zoomDir){ return false; }
        let maxZoom = _config.maxZoomLevel;
        let minZoom = _config.minZoomLevel;
        let oldZoom = _world.zoomLevel || 1;
        if (zoomDir === 'out'){ _self.decZoomLevel(null, true); }
        else if (zoomDir === 'in'){ _self.incZoomLevel(null, true); }
        let newZoom = _world.zoomLevel || 1;
        if (newZoom !== oldZoom){ _self.playSoundEffect('spawn-sound'); }
        if (newZoom >= maxZoom){ $zoomInButton.addClass('disabled'); }
        else { $zoomInButton.removeClass('disabled'); }
        if (newZoom <= minZoom){ $zoomOutButton.addClass('disabled'); }
        else { $zoomOutButton.removeClass('disabled'); }
        return true;
        });
    return true;
    }

// Complicated function for generating the ROBOTS OVERVIEW API so we can reuse it multiple times
function initMenuRobotsOverview($thisWorld, $robotsOverview){
    //console.log('%c' + 'mmrpgWorldMap.initMenuRobotsOverview($thisWorld: ' + typeof $thisWorld + ', $robotsOverview: ' + typeof $robotsOverview + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuRobotsOverview() missing required $thisWorld!'); return false; }
    if (!$robotsOverview || !$robotsOverview.length){ console.error('initMenuRobotsOverview() missing required $robotsOverview!'); return false; }
    if (typeof this.robotsOverviewAPI === 'undefined'){ console.error('initMenuRobotsOverview() missing required _self.robotsOverviewAPI!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _worldPlayerRobots = _worldPlayer.robots;
    let thisCache = _self.getCachedObject('robotsOverview');
    let $thisCanvas = _elements.canvas;
    let $sideButtons = _elements.sideButtons;
    let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
    let $actionDropdown = _elements.actionDropdown;
    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
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

    // Collect the object that holds the robots overview API
    let robotsOverviewAPI = _self.robotsOverviewAPI;

    // Extract indivisual functions from the API so we can use them here
    hoverOverviewObject = robotsOverviewAPI.hoverOverviewObject;
    unhoverOverviewObject = robotsOverviewAPI.unhoverOverviewObject;
    disableOtherElements = robotsOverviewAPI.disableOtherElements;
    enableOtherElements = robotsOverviewAPI.enableOtherElements;
    showRobotsOverviewPanel = robotsOverviewAPI.showRobotsOverviewPanel;
    disableRobotsOverview = robotsOverviewAPI.disableRobotsOverview;
    calculateStorage = robotsOverviewAPI.calculateStorage;
    makeStoragePages = robotsOverviewAPI.makeStoragePages;
    makeStorageBullets = robotsOverviewAPI.makeStorageBullets;
    goToStoragePage = robotsOverviewAPI.goToStoragePage;
    sortStoragePage = robotsOverviewAPI.sortStoragePage;
    refreshStoragePage = robotsOverviewAPI.refreshStoragePage;
    //refreshRobotRefs = robotsOverviewAPI.refreshRobotRefs;
    //refreshRobotBackups = robotsOverviewAPI.refreshRobotBackups;
    refreshRobotsDiv = robotsOverviewAPI.refreshRobotsDiv;
    refreshItemsDiv = robotsOverviewAPI.refreshItemsDiv;
    refreshAbilitiesDiv = robotsOverviewAPI.refreshAbilitiesDiv;
    filterAbilitiesToSelected = robotsOverviewAPI.filterAbilitiesToSelected;
    refreshDetailsPanel = robotsOverviewAPI.refreshDetailsPanel;
    updateRobotImageFrame = robotsOverviewAPI.updateRobotImageFrame;

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
        if ($abilityValue.is('.disabled')){ return; }
        else if ($abilityValue.is('.hidden')){ return; }
        else if ($abilityValue.is('.locked')){ return; }
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
        $abilityValue.siblings().removeClass('selected');
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
            let confirmClickAction = function(title, text, callback){ _self.showActionModal('confirm', title, text, targetRobotToken, { onConfirm: function() { callback.call(_self); } } ); };
            let actionModalConfig = {onComplete: function(){ $actionButton.removeClass('clicked'); }};
            if (actionToken === 'add-robot'){ autoClickAction(); _self.showAddRobotModal(robotToken, actionModalConfig); }
            else if (actionToken === 'remove-robot'){ autoClickAction(); _self.showRemoveRobotModal(robotToken, actionModalConfig); }
            else if (actionToken === 'release-robot'){ confirmClickAction('Release Robot', 'Are you sure?', function(){ autoClickAction(); _self.showReleaseRobotModal(robotToken, actionModalConfig); }); }
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
            refreshRobotRefs();
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
            robotsOverviewAPI.refreshRobotRefs();
            //let $targetRobot = $teamRobotsInOverview.filter('.team-robot[data-robot].selected').first();
            let $targetRobot = $('.team-robot[data-robot].selected', $teamRobotsDiv).first();
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

    // Return true on success
    return true;

}

// Complicated function for generating the ROBOTS OVERVIEW API so we can reuse it multiple times
function initRobotsOverviewAPI($thisWorld, $robotsOverview){
    //console.log('%c' + 'mmrpgWorldMap.initRobotsOverviewAPI($thisWorld: ' + typeof $thisWorld + ', $robotsOverview: ' + typeof $robotsOverview + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initRobotsOverviewAPI() missing required $thisWorld!'); return false; }
    if (!$robotsOverview || !$robotsOverview.length){ console.error('initRobotsOverviewAPI() missing required $robotsOverview!'); return false; }
    if (typeof this.robotsOverviewAPI !== 'undefined'){ return true; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _worldPlayerRobots = _worldPlayer.robots;
    let thisCache = _self.getCachedObject('robotsOverview');
    let $thisCanvas = _elements.canvas;
    let $sideButtons = _elements.sideButtons;
    let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
    let $actionDropdown = _elements.actionDropdown;
    let $backButton = _elements.backButton;
    let $homeButton = _elements.homeButton;
    let $resetButton = _elements.resetButton;
    let $playerSwitcher = _elements.playerSwitcher;
    let $minimapOverview = _elements.minimapOverview;
    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
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

    // Define an object to hold the robots overview API
    let robotsOverviewAPI = {};

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
        if (thisCache.showOverviewTimeout){ clearTimeout(thisCache.showOverviewTimeout); }
        thisCache.showOverviewTimeout = setTimeout(function(){ overviewIsOpening = false; $storageBoxes.not($thisStorageBox).addClass('disabled'); }, 1200);
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
                    let personaActive = robotData.personaActive;
                    let abilitiesEquipped = robotData.abilities;
                    let abilitiesCompatible = robotData.abilitiesCompatible;
                    let abilitiesViaPersona = robotData.abilitiesViaPersona;
                    let abilitiesViaItem = robotData.abilitiesViaItem;
                    if (!personaActive && typeof abilitiesCompatible !== 'undefined'){ robotAbilities = robotAbilities.concat(abilitiesCompatible); }
                    else if (personaActive && typeof abilitiesViaPersona !== 'undefined'){ robotAbilities = robotAbilities.concat(abilitiesViaPersona); }
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
        let personaActive = selectedRobotData.personaActive;
        let abilitiesEquipped = selectedRobotData.abilities || [];
        let abilitiesCompatible = selectedRobotData.abilitiesCompatible || [];
        let abilitiesViaPersona = selectedRobotData.abilitiesViaPersona || [];
        let abilitiesViaItem = selectedRobotData.abilitiesViaItem || [];
        //console.log('-> abilitiesCompatible =', abilitiesCompatible);
        //console.log('-> abilitiesViaItem =', abilitiesViaItem);
        if (!personaActive && typeof abilitiesCompatible !== 'undefined'){ selectedRobotAbilities = selectedRobotAbilities.concat(abilitiesCompatible); }
        else if (personaActive && typeof abilitiesViaPersona !== 'undefined'){ selectedRobotAbilities = selectedRobotAbilities.concat(abilitiesViaPersona); }
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
                if (thisCache.detailsTimeout){ clearTimeout(thisCache.detailsTimeout); }
                thisCache.detailsTimeout = setTimeout(function(){ $overviewWrapper.removeClass('wait'); }, delayFor);
                }
            if (typeof callback === 'function'){ callback.call(this); }
            };
        // Define some reusable inline functions for less code
        let showRobotIntroAnimation = function(){
            //console.log('showing intro animation for robot!');
            let $robotSprite = $detailsDiv.find('.image > .sprite');
            if (thisCache.onRobotIntro){ clearTimeout(thisCache.onRobotIntro); }
            if (thisCache.onAfterRobotIntro){ clearTimeout(thisCache.onAfterRobotIntro); }
            thisCache.onRobotIntro = setTimeout(function(){ $robotSprite.attr('data-frame', '01'); }, 0);
            thisCache.onAfterRobotIntro = setTimeout(function(){ if ($robotSprite.attr('data-frame') !== '01'){ return; } $robotSprite.attr('data-frame', '00'); }, 900);
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
    // Make sure hovering over the robot sprite shifts its pose for a bit of animation
    let updateRobotImageFrame = function(image, newFrame, onlyWhen){
        if (_self.worldIsBusy()){ return; }
        let $image = $(image), $sprite = $image.find('> .sprite'), frame = $sprite.attr('data-frame');
        if (Array.isArray(onlyWhen) && onlyWhen.indexOf(frame) === -1){ return; }
        $sprite.attr('data-frame', newFrame);
        };

    // Add all these methods to the API in case future code needs to access them too
    robotsOverviewAPI.hoverOverviewObject = hoverOverviewObject;
    robotsOverviewAPI.unhoverOverviewObject = unhoverOverviewObject;
    robotsOverviewAPI.disableOtherElements = disableOtherElements;
    robotsOverviewAPI.enableOtherElements = enableOtherElements;
    robotsOverviewAPI.showRobotsOverviewPanel = showRobotsOverviewPanel;
    robotsOverviewAPI.disableRobotsOverview = disableRobotsOverview;
    robotsOverviewAPI.calculateStorage = calculateStorage;
    robotsOverviewAPI.makeStoragePages = makeStoragePages;
    robotsOverviewAPI.makeStorageBullets = makeStorageBullets;
    robotsOverviewAPI.goToStoragePage = goToStoragePage;
    robotsOverviewAPI.sortStoragePage = sortStoragePage;
    robotsOverviewAPI.refreshStoragePage = refreshStoragePage;
    robotsOverviewAPI.refreshRobotRefs = refreshRobotRefs;
    robotsOverviewAPI.refreshRobotBackups = refreshRobotBackups;
    robotsOverviewAPI.refreshRobotsDiv = refreshRobotsDiv;
    robotsOverviewAPI.refreshItemsDiv = refreshItemsDiv;
    robotsOverviewAPI.refreshAbilitiesDiv = refreshAbilitiesDiv;
    robotsOverviewAPI.filterAbilitiesToSelected = filterAbilitiesToSelected;
    robotsOverviewAPI.refreshDetailsPanel = refreshDetailsPanel;
    robotsOverviewAPI.updateRobotImageFrame = updateRobotImageFrame;

    // Assign the generated robots overview API
    _self.robotsOverviewAPI = robotsOverviewAPI;

    // Return true on success
    return true;
}

// Add this to world.js or wherever your prototype injections live
function checkMenuButtonCurrency(currency, price){
    //console.log('%c' + 'mmrpgWorldMap.checkMenuButtonCurrency($thisWorld: ' + typeof $thisWorld + ', $robotsOverview: ' + typeof $robotsOverview + ')', 'color: magenta;');
    if (!currency || !currency.length){ console.error('checkMenuButtonCurrency() missing required currency!'); return false; }
    if (!price || typeof price !== 'number'){ console.error('checkMenuButtonCurrency() missing required price!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _worldPlayerRobots = _worldPlayer.robots;
    // Split apart the currency string
    let currencyKind = currency.indexOf(':') !== -1 ? currency.split(':')[0] : currency;
    let currencySubKind = currency.indexOf(':') !== -1 ? currency.split(':')[1] : false;
    let playerHasNow = 0;
    // Check player inventory
    if (currencyKind === 'stars'){ playerHasNow = Object.keys(_worldPlayer.stars).length; }
    else if (currencyKind === 'zenny'){ playerHasNow = _worldPlayer.zenny; }
    else if (currencyKind === 'items'){
        if (currencySubKind && typeof _worldPlayer.items[currencySubKind] !== 'undefined'){
            playerHasNow = _worldPlayer.items[currencySubKind];
            if (typeof _worldPlayer.items[currencySubKind + '__equipped'] !== 'undefined'){
                playerHasNow -= _worldPlayer.items[currencySubKind + '__equipped'];
                }
            }
        }
    let hasEnough = playerHasNow >= price;
    // Generate formatted labels
    let countLabelName = currencySubKind ? currencySubKind.replace('-', ' ') : currencyKind;
    let playerCountLabel = (playerHasNow + ' / ' + price) + ' ' + toUpperCaseWords(countLabelName);
    if (currencyKind !== 'zenny' && price > 1){ playerCountLabel += 's'; }
    // Generate sprite markup
    let currencySpriteMarkup = '';
    if (currencyKind === 'stars'){ currencySpriteMarkup = _self.getItemSpriteMarkup('field-star', {dir: 'left', frame: '00'}); }
    else if (currencyKind === 'screws'){ currencySpriteMarkup = _self.getItemSpriteMarkup('hyper-screw', {dir: 'left', frame: '00'}); }
    else if (currencyKind === 'items' && currencySubKind){ currencySpriteMarkup = _self.getItemSpriteMarkup(currencySubKind, {dir: 'left', frame: '00'}); }
    return {
        hasEnough: hasEnough,
        hasNow: playerHasNow,
        kind: currencyKind,
        subKind: currencySubKind,
        countLabel: playerCountLabel,
        spriteMarkup: currencySpriteMarkup
        };
}


/*

// Quick function for initializing the menu's HOME button w/ relevant click events
function initMenu____Button($thisWorld, $____Button){
    //console.log('%c' + 'mmrpgWorldMap.initMenuBackButton($thisWorld: ' + typeof $thisWorld + ', $____Button: ' + typeof $____Button + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('initMenuBackButton() missing required $thisWorld!'); return false; }
    if (!$____Button || !$____Button.length){ console.error('initMenuBackButton() missing required $____Button!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;


    }

mmrpgWorldMap.prototype.initMenu____Button = initMenu____Button;

*/

// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.hoverCanvasMenuObject = hoverCanvasMenuObject;
mmrpgWorldMap.prototype.unhoverCanvasMenuObject = unhoverCanvasMenuObject;

mmrpgWorldMap.prototype.initMenuBackButton = initMenuBackButton;
mmrpgWorldMap.prototype.initMenuHomeButton = initMenuHomeButton;
mmrpgWorldMap.prototype.initMenuResetButton = initMenuResetButton;
mmrpgWorldMap.prototype.initMenuPlayerSwitcher = initMenuPlayerSwitcher;
mmrpgWorldMap.prototype.initMenuMinimapOverview = initMenuMinimapOverview;
mmrpgWorldMap.prototype.initMenuZoomControls = initMenuZoomControls;
mmrpgWorldMap.prototype.initMenuRobotsOverview = initMenuRobotsOverview;

mmrpgWorldMap.prototype.initRobotsOverviewAPI = initRobotsOverviewAPI;

mmrpgWorldMap.prototype.checkMenuButtonCurrency = checkMenuButtonCurrency;
