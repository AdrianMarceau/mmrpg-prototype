
// -- WORLD BINDING METHODS -- //

// Quick function for binding events to a given layer's canvas object
function bindEventsToCanvas($canvasMap){
    //console.log('%c' + 'mmrpgWorldMap.bindEventsToCanvas($canvasMap:' + typeof $canvasMap + ')', 'color: magenta;');
    if (!$canvasMap || !$canvasMap.length){ console.error('bindEventsToCanvas() missing required $canvasMap!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let layerToken = 'terrain'; // TODO: make this dynamic maybe?
    let playerMobility = _config.playerMobility || 1;
    let activeTimeouts = {}, activeTimeoutDuration = _config.mapEffects.activeTimeout;
    let focusTimeouts = {}, focusTimeoutDuration = _config.mapEffects.focusTimeout;
    let hoverTimeouts = {}, hoverTimeoutDuration = _config.mapEffects.hoverTimeout, hoverTiles = [];
    let lastMouseClick, lastMouseOver;
    let $clickOverlay = _elements.clickOverlay;
    let $sideButtons = _elements.sideButtons;
    let $robotsOverview = _elements.robotsOverview;
    // Bind a click event to the overlap that lets us calculate where the user actually clicked below
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
        //if (!tileIsWithinRange || sameAsLast || sameAsCurrent){ return false; }
        // If the clicked tile is non-walkable or out of range, change direction to face it
        if (!tileIsWithinRange){
            if (!sameAsCurrent){
                // Calculate the difference between the clicked tile and the player's current tile
                let curPosXY = curPos.split('-');
                let thisPosXY = thisPos.split('-');
                let dx = parseInt(thisPosXY[0]) - parseInt(curPosXY[0]);
                let dy = parseInt(thisPosXY[1]) - parseInt(curPosXY[1]);
                // Determine X and Y direction strings
                let dirY = dy > 0 ? 'down' : (dy < 0 ? 'up' : '');
                let dirX = dx > 0 ? 'right' : (dx < 0 ? 'left' : '');
                // Combine them (e.g., 'down-right', 'up', 'left')
                let newDirection = (dirY && dirX) ? `${dirY}-${dirX}` : (dirY || dirX);
                // If the direction changed, update the cursor and force an in-place sprite update
                if (newDirection && _worldCursor.direction !== newDirection){
                    _worldCursor.direction = newDirection;
                    _worldPlayer.direction = newDirection;
                    // Calling moveToPosition on the current tile with forceMove = true mimics the B-button!
                    _self.playSoundEffect('land_mmv-gb');
                    _self.moveToPosition(curPos, null, true);
                    }
                }
            return false; // Exit out of the click handler since we aren't actually moving
            }
        // Normal exit for walkable tiles that we don't want to re-trigger
        if (sameAsLast || sameAsCurrent){ return false; }
        //console.log('%c' + 'Mouse click event triggered for position ' + thisPos + '!', 'color: orange;');
        if (!sameAsLast){
            _self.playSoundEffect('glass-klink', {volume:0.5});
            _self.playSoundEffect('land_mmv-gb', {delay:600});
            }
        //_self.makeLayerTileActive(thisPos);
        if (activeTimeouts[oldPos]){ clearTimeout(activeTimeouts[oldPos]); }
        activeTimeouts[thisPos] = setTimeout(function(){
            _self.moveToPosition(thisPos, function(){
                if (thisPos !== oldPos){ _self.makeLayerTileInactive(oldPos); }
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
        //$clickOverlay.css({cursor: showPointer ? 'pointer' : 'default'});
        $clickOverlay.css({cursor: showPointer ? 'cell' : 'crosshair'});
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
    // Prevent default Enter/Space events if the user is not explicitly inside a text input
    document.addEventListener('keydown', function(event){
        if (event.key === 'Enter' || event.key === ' '){
            if (event.target.tagName !== 'INPUT'
                && event.target.tagName !== 'TEXTAREA') {
                //console.log('Preventing default Enter or Space!');
                event.preventDefault();
                }
            }
        });
    // Return true on success
    return true;
    }

// Quick function for binding events to the main world object
function bindEventsToWorld($thisWorld){
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
    // Bind a click event to the minimap overview in the header that expands on mouseover to show more
    let $minimapOverview = _elements.minimapOverview;
    if ($minimapOverview && $minimapOverview.length){
        $minimapOverview.bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
        $minimapOverview.bind('mouseleave', unhoverCanvasObject);
        $minimapOverview.find('.button').bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
        $minimapOverview.find('.button').bind('mouseleave', unhoverCanvasObject);
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
        let _selfRef = this;
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
            let $bigButtons = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons);
            let $confirmButton = $bigButtons.filter('.maybe');
            if (!$confirmButton || !$confirmButton.length){ $confirmButton = $bigButtons.first(); }
            //let $confirmButton = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons).first();
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
        // If the back or home button is currently focused, we should listen for a confirmation button
        if (_selfRef.leftSideButtonHovered){
            //console.log('-> _selfRef.leftSideButtonHovered!');
            let $hoveredButton, hoveredButtonKind;
            let $possibleButtons = $('').add($backButton).add($homeButton);
            if ($backButton.is('.hovered')){ $hoveredButton = $backButton.first(); hoveredButtonKind = 'back'; }
            if ($homeButton.is('.hovered')){ $hoveredButton = $homeButton.first(); hoveredButtonKind = 'home'; }
            if ($hoveredButton && $hoveredButton.length){
                // Should not be open while back/home-switching is being used
                dismissSideButtonAction();
                // If the player has pressed the A button, we can simply click whichever button is currently "hovered"
                if (activeInputs.A || activeInputs.Start){
                    //console.log('%c' + 'Confirm ' + hoveredButtonKind + ' button!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    if (!$hoveredButton || !$hoveredButton.length){ return false; }
                    //console.log('Triggering click on hovered ', hoveredButtonKind, ' button:', $hoveredButton);
                    $hoveredButton.trigger('click');
                    $hoveredButton.addClass('clicked');
                    $hoveredButton.removeClass('hovered');
                    ignoreInputFor(1200);
                    return true;
                    }
                // If the player has pressed the B button instead, we should dismiss the player switcher
                if (activeInputs.B){
                    //console.log('%c' + 'Dismiss ' + hoveredButtonKind + ' button!', 'color: orange;');
                    if (event){ event.preventDefault(); }
                    $hoveredButton.removeClass('hovered');
                    ignoreInputFor(600);
                    return true;
                    }
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
            // If the player has pressed the A button, let's confirm the side-button action if it's open
            if (activeInputs.A || activeInputs.Start){
                //console.log('%c' + 'A key pressed! Confirm action popup!', 'color: orange;');
                if (event){ event.preventDefault(); }
                if (!$sideButtons.is('.active')){ return false; }
                confirmSideButtonAction();
                return true;
                }
            // Else if the player has pressed the B button, let's close the side-button action if it's open
            else if (activeInputs.B){
                //console.log('%c' + 'B key pressed! Dismiss action popup!', 'color: orange;');
                if (event){ event.preventDefault(); }
                if (!$sideButtons.is('.active')){ return false; }
                dismissSideButtonAction();
                return true;
                }
            // Else if the player has just pressed Y, make sure we add the hover class to the action-dropdown
            else if (activeInputs.Y){
                //console.log('%c' + 'Y key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                if (!$sideButtons.is('.active')){ return false; }
                $actionDropdown.toggleClass('hover');
                return true;
                }
            // Else if the player has pressed up/down trying to scroll to a different button
            else if (activeInputs.Up || activeInputs.Down){
                //console.log('----> Vertical direction clicked (' + (activeInputs.Up ? 'UP' : 'DOWN') + ')!');
                if (event){ event.preventDefault(); }
                let $bigButtons = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons);
                let numBigButtons = $bigButtons ? $bigButtons.length : 0;
                let maxBigButtonIndex = numBigButtons - 1;
                let hoverBigButton = function($button){ $bigButtons.removeClass('maybe'); $button.addClass('maybe'); };
                let hoverFirstBigButton = function(){ hoverBigButton($bigButtons.first()); };
                let hoverLastBigButton = function(){ hoverBigButton($bigButtons.last()); };
                //console.log('$sideButtons = ', $sideButtons.length, $sideButtons);
                //console.log('$bigButtons = ', $bigButtons.length, $bigButtons);
                //console.log('numBigButtons = ', numBigButtons, numBigButtons);
                if (numBigButtons > 1){
                    let $maybeButton = $bigButtons.filter('.maybe');
                    if (!$maybeButton || !$maybeButton.length){ $maybeButton = null; }
                    let maybeButtonIndex = $maybeButton ? $bigButtons.index($maybeButton) : -1;
                    if (activeInputs.Up){
                        if (!$maybeButton){ hoverLastBigButton(); return true; }
                        let prevButtonIndex = maybeButtonIndex - 1;
                        if (prevButtonIndex < 0){ prevButtonIndex = maxBigButtonIndex; }
                        hoverBigButton($bigButtons.eq(prevButtonIndex));
                        return true;
                        }
                    else if (activeInputs.Down){
                        if (!$maybeButton){ hoverFirstBigButton(); return true; }
                        let nextButtonIndex = maybeButtonIndex + 1;
                        if (nextButtonIndex > maxBigButtonIndex){ nextButtonIndex = 0; }
                        hoverBigButton($bigButtons.eq(nextButtonIndex));
                        return true;
                        }
                    }
                }
            }
        // Otherwise if the world map is NOT hidden, so the arrow keys must be controlling the player
        if (!worldMapIsHidden){
            // Collect reference to the minimap in case we need it for menu interactions
            let $minimapOverview = _elements.minimapOverview;
            // If the player has pressed the Select button, try to click one of the left-side exit/home-buttons if either exists/not-disabled
            if (activeInputs.Select){
                //console.log('%c' + 'Select key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                let $hoveredButton, hoveredButtonKind, nextButtonKind;
                let $possibleButtons = $('').add($backButton).add($homeButton);
                if ($backButton.is('.hovered')){ $hoveredButton = $backButton.first(); hoveredButtonKind = 'back'; }
                if ($homeButton.is('.hovered')){ $hoveredButton = $homeButton.first(); hoveredButtonKind = 'home'; }
                if (!hoveredButtonKind){ nextButtonKind = 'back'; }
                else if (hoveredButtonKind === 'back'){ nextButtonKind = 'home'; }
                else if (hoveredButtonKind === 'home'){ nextButtonKind = false; }
                $possibleButtons.removeClass('hovered');
                _selfRef.leftSideButtonHovered = false;
                if (nextButtonKind === 'back'){
                    //$backButton.addClass('hovered');
                    $backButton.trigger('mouseenter');
                    _selfRef.leftSideButtonHovered = true;
                    }
                else if (nextButtonKind === 'home'){
                    //$homeButton.addClass('hovered');
                    $homeButton.trigger('mouseenter');
                    _selfRef.leftSideButtonHovered = true;
                    }
                ignoreInputFor(600);
                return true;
                } else if (_selfRef.leftSideButtonHovered){
                let $possibleButtons = $('').add($backButton).add($homeButton);
                clearTimeout(_selfRef.leftSideButtonTimeout);
                _selfRef.leftSideButtonTimeout = setTimeout(function(){
                    $possibleButtons.removeClass('hovered');
                    _selfRef.leftSideButtonHovered = false;
                    }, 200);
                }
            // If the player has pressed the Start button, try to click the minimap-overview button if exists/not-disabled
            if (activeInputs.Start){
                //console.log('%c' + 'Start key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                if ($minimapOverview.length
                    && $minimapOverview.is(':visible')
                    && !$minimapOverview.is('.disabled')){
                    if (!_selfRef.minimapOverviewHovered){
                        //$minimapOverview.addClass('hovered');
                        $minimapOverview.trigger('mouseenter');
                        _selfRef.minimapOverviewHovered = true;
                        } else {
                        let $active = $minimapOverview.find('.button.active').first();
                        let $next = $minimapOverview.find('.button:not(.active)').first();
                        if ($active && $active.length){
                            let $maybeNext = $active.next('.button:not(.active)');
                            if ($maybeNext && $maybeNext.length){ $next = $maybeNext; }
                            }
                        if ($next && $next.length){ $next.trigger('click'); }
                        }
                    ignoreInputFor(300);
                    return true;
                    }
                } else if (_selfRef.minimapOverviewHovered){
                clearTimeout(_selfRef.minimapOverviewTimeout);
                _selfRef.minimapOverviewTimeout = setTimeout(function(){
                    $minimapOverview.trigger('mouseleave');
                    _selfRef.minimapOverviewHovered = false;
                    }, 200);
                }
            // If the player has pressed the X button, try to click the robots-overview button (team-switch) button if exists/not-disabled
            if (activeInputs.X){
                //console.log('%c' + 'X key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                let $switchButton = $('.team-switch', $robotsOverview);
                if ($switchButton.length
                    && $switchButton.is(':visible')
                    && !$switchButton.is('.disabled')){
                    //$switchButton.addClass('clicked');
                    $switchButton.trigger('click');
                    setTimeout(function(){ $switchButton.removeClass('clicked'); }, 200);
                    ignoreInputFor(300);
                    return true;
                    }
                }
            // If the player has pressed the Y button, ?????
            if (activeInputs.Y){
                //console.log('%c' + 'Y key pressed!', 'color: orange;');
                //if (event){ event.preventDefault(); }
                // ?????
                //return true;
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
            // If the player has pressed the B button, ?????
            if (activeInputs.B){
                //console.log('%c' + 'B key pressed!', 'color: orange;');
                //if (event){ event.preventDefault(); }
                // ?????
                //return true;
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
                            //$cursorPlayer.addClass('hovered');
                            $cursorPlayer.trigger('mouseenter');
                            }
                        else if (playerHistory.length > 1){
                            let lastPlayerToken = (function(a, t){ for (let i = 0; i < a.length; i++){ if (a[i] !== t){ return a[i]; } } })(playerHistory, _worldPlayer.token);
                            let $lastPlayerButton = lastPlayerToken.length ? $playerButtons.filter('[data-player="' + lastPlayerToken + '"]').first() : false;
                            if ($lastPlayerButton.length){
                                //$lastPlayerButton.addClass('hovered');
                                $lastPlayerButton.trigger('mouseenter');
                                }
                            }
                        }
                    else {
                        //$activePlayer.addClass('hovered');
                        $activePlayer.trigger('mouseenter');
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
                            if ($prevPlayer.length){
                                //$prevPlayer.addClass('hovered');
                                $prevPlayer.trigger('mouseenter');
                                }
                            }
                        else if (activeInputs.R2){
                            let $nextPlayer = $hoveredPlayer.nextAll('.team-player').first();
                            if (!$nextPlayer || !$nextPlayer.length){ $nextPlayer = $playerButtons.first(); }
                            if ($nextPlayer.length){
                                //$nextPlayer.addClass('hovered');
                                $nextPlayer.trigger('mouseenter');
                                }
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
                    if (a.Up){ d.push('up'); } else if (a.Down){ d.push('down'); }
                    if (a.Left){ d.push('left'); } else if (a.Right){ d.push('right'); }
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
                let thisVerDir = (newRow > thisRow) ? 'down' : (newRow < thisRow) ? 'up' : false;
                let thisHorDir = (newCol > thisCol) ? 'right' : (newCol < thisCol) ? 'left' : false;
                if (!inPlaceMovement){
                    let thisShiftDir = (function(h, v){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join('-'); })(thisVerDir, thisHorDir);
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
                if (newPos !== oldPos){ _self.makeLayerTileInactive(oldPos); }
                //_self.makeLayerTileActive(newPos);
                _self.playSoundEffect('land_mmv-gb');
                _self.moveToPosition(newPos, function(){
                    if (thisHorDir && thisVerDir){ ignoreInputFor(); }
                    }, forceMove);
                }
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

// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.bindEventsToCanvas = bindEventsToCanvas;
mmrpgWorldMap.prototype.bindEventsToWorld = bindEventsToWorld;

