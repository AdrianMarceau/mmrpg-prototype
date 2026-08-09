
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
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _worldPlayerRobots = _worldPlayer.robots;
    let thisCache = _self.getCachedObject('worldEvents');
    let $thisCanvas = _elements.canvas;
    let $sideButtons = _elements.sideButtons;
    let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
    let $actionDropdown = _elements.actionDropdown;
    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
    let hoverCanvasObject = _self.hoverCanvasMenuObject;
    let unhoverCanvasObject = _self.unhoverCanvasMenuObject;
    // Bind a click event to the back button in the header that'll bring us to prototype menu
    let $backButton = _elements.backButton;
    if ($backButton && $backButton.length){ _self.initMenuBackButton($thisWorld, $backButton); }
    // Bind a click event to the home button in the header that'll bring us to prototype menu
    let $homeButton = _elements.homeButton;
    if ($homeButton && $homeButton.length){ _self.initMenuHomeButton($thisWorld, $homeButton); }
    // Bind a click event to the reset button in the header that'll clear world data to start over (dev/debug only)
    let $resetButton = _elements.resetButton;
    if ($resetButton && $resetButton.length){ _self.initMenuResetButton($thisWorld, $resetButton); }
    // Bind click events to the player switcher options in the world map header
    let $playerSwitcher = _elements.playerSwitcher;
    if ($playerSwitcher && $playerSwitcher.length){ _self.initMenuPlayerSwitcher($thisWorld, $playerSwitcher); }
    // Bind a click event to the minimap overview in the header that expands on mouseover to show more
    let $minimapOverview = _elements.minimapOverview;
    if ($minimapOverview && $minimapOverview.length){ _self.initMenuMinimapOverview($thisWorld, $minimapOverview); }
    // Bind a click event to the zoom control buttons in the header for more easily zooming in or out
    let $zoomControls = _elements.zoomControls;
    if ($zoomControls && $zoomControls.length){ _self.initMenuZoomControls($thisWorld, $zoomControls); }
    // Check to make sure the robotsOverview exists, and then bind events to its elements
    let $robotsOverview = _elements.robotsOverview;
    if ($robotsOverview && $robotsOverview.length){
        _self.initRobotsOverviewAPI($thisWorld, $robotsOverview);
        _self.initMenuRobotsOverview($thisWorld, $robotsOverview);
        }

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

// Quick function for binding events to the user's keyboard/gamepad inputs
function bindEventsToInputs($thisWorld){
    //console.log('%c' + 'mmrpgWorldMap.bindEventsToInputs($thisWorld:' + typeof $thisWorld + ')', 'color: magenta;');
    if (!$thisWorld || !$thisWorld.length){ console.error('bindEventsToInputs() missing required $thisWorld!'); return false; }
    let _self = this;
    let _config = _self.config;
    let _elements = _self.elements;
    let _world = _self.state;
    let _worldCursor = _world.cursor;
    let _worldPlayer = _world.player;
    let _worldPlayerTeam = _worldPlayer.team;
    let _worldPlayerRobots = _worldPlayer.robots;
    let thisCache = _self.getCachedObject('worldEvents');
    let $thisCanvas = _elements.canvas;
    let $sideButtons = _elements.sideButtons;
    let $sideButtonsWrapper = $('> .wrapper', $sideButtons);
    let $actionDropdown = _elements.actionDropdown;
    let playerIsCursor = _worldPlayer.token === 'player' ? true : false;
    let hoverCanvasObject = function(e, sfx){
        let $object = $(this);
        if (_self.worldIsBusy()){ return; }
        if ($object.is('.disabled')){ return; }
        if ($object.is('[disabled]')){ return; }
        if ($object.closest('.chrome').is('.disabled')){ return; }
        if ($object.closest('.chrome').is('.busy')){ return; }
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
    let $backButton = _elements.backButton;
    let $homeButton = _elements.homeButton;
    let $resetButton = _elements.resetButton;
    let $playerSwitcher = _elements.playerSwitcher;
    let $minimapOverview = _elements.minimapOverview;
    let $robotsOverview = _elements.robotsOverview;
    let robotsOverviewAPI = _self.robotsOverviewAPI;

    // Define a function to run each time user inputs are updated so we can react
    let listenForInput = function(){ return Date.now() >= nextInputAllowedTime; }, nextInputAllowedTime = 0;
    let ignoreInputFor = function(delay){ delay = typeof delay === 'number' ? delay : 250; nextInputAllowedTime = Date.now() + delay; };
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
        //console.log('-------------------');
        ignoreInputFor();
        // Collect references and checks on certain key elements
        let _selfRef = this;
        let worldMapIsHidden = _self.worldMapIsHidden();
        let sideButtonsActive = $sideButtons.is('.active') ? true : false;
        let playerSwitcherFocused = $playerSwitcher.is('.focused') ? true : false;
        let robotsOverviewIsExpanded = $robotsOverview && $robotsOverview.length && $robotsOverview.is('.expanded') ? true : false;
        let currentRobotsOverviewPanel = $robotsOverview && $robotsOverview.length && robotsOverviewIsExpanded ? $robotsOverview.attr('data-view') : false;
        let calculateRobotsOverviewStorage = robotsOverviewAPI && typeof robotsOverviewAPI.calculateStorage !== 'undefined' ? robotsOverviewAPI.calculateStorage : function(){ return null; };
        let refreshRobotsOverviewDetailsPanel = robotsOverviewAPI && typeof robotsOverviewAPI.refreshDetailsPanel !== 'undefined' ?  robotsOverviewAPI.refreshDetailsPanel : function(){ return null; };
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
            $sideButtons.removeClass('maybe');
            let $dismissButton = $('.button[data-action="dismiss"]', $sideButtons);
            if (!$dismissButton || !$dismissButton.length){ return; }
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
        if (thisCache.leftSideButtonHovered){
            //console.log('-> thisCache.leftSideButtonHovered!');
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
            if (activeInputs.A){
                //console.log('%c' + 'A key pressed! Confirm action popup!', 'color: orange;');
                if (event){ event.preventDefault(); }
                if (!$sideButtons.is('.active')){ return false; }
                if ($sideButtons.is('.busy')){ return false; }
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
                if ($sideButtons.is('.busy')){ return false; }
                let $bigButtons = $('.button[data-action]:not([data-action="dismiss"])', $sideButtons);
                let numBigButtons = $bigButtons ? $bigButtons.length : 0;
                let maxBigButtonIndex = numBigButtons - 1;
                let hoverBigButton = function($button){
                    $bigButtons.removeClass('maybe');
                    $bigButtons.trigger('mouseleave');
                    $button.addClass('maybe');
                    $button.trigger('mouseenter');
                    };
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
            // Else if the player has pressed left/right, we do nothing if the panel is busy
            else if (activeInputs.Left || activeInputs.Right){
                if (event){ event.preventDefault(); }
                if ($sideButtons.is('.busy')){ return false; }
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
                thisCache.leftSideButtonHovered = false;
                if (nextButtonKind === 'back'){
                    //$backButton.addClass('hovered');
                    $backButton.trigger('mouseenter');
                    thisCache.leftSideButtonHovered = true;
                    }
                else if (nextButtonKind === 'home'){
                    //$homeButton.addClass('hovered');
                    $homeButton.trigger('mouseenter');
                    thisCache.leftSideButtonHovered = true;
                    }
                ignoreInputFor(600);
                return true;
                } else if (thisCache.leftSideButtonHovered){
                let $possibleButtons = $('').add($backButton).add($homeButton);
                clearTimeout(thisCache.leftSideButtonTimeout);
                thisCache.leftSideButtonTimeout = setTimeout(function(){
                    $possibleButtons.removeClass('hovered');
                    thisCache.leftSideButtonHovered = false;
                    }, 200);
                }
            // If the player has pressed the Start button, try to click the minimap-overview button if exists/not-disabled
            if (activeInputs.Start){
                //console.log('%c' + 'Start key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                if ($minimapOverview.length
                    && $minimapOverview.is(':visible')
                    && !$minimapOverview.is('.disabled')){
                    if (!thisCache.minimapOverviewHovered){
                        //$minimapOverview.addClass('hovered');
                        $minimapOverview.trigger('mouseenter');
                        thisCache.minimapOverviewHovered = true;
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
                } else if (thisCache.minimapOverviewHovered){
                clearTimeout(thisCache.minimapOverviewTimeout);
                thisCache.minimapOverviewTimeout = setTimeout(function(){
                    $minimapOverview.trigger('mouseleave');
                    thisCache.minimapOverviewHovered = false;
                    }, 200);
                }
            // If the player has pressed the X button, try to click the robots-overview button (team-switch) button if exists/not-disabled
            if (activeInputs.X){
                //console.log('%c' + 'X key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                //let $switchButton = $('.team-switch', $robotsOverview);
                let $storageButtons = $('.storage-button', $robotsOverview);
                let $firstButton = $storageButtons && $storageButtons.length ? $storageButtons.first() : false;
                if ($firstButton
                    && $firstButton.length
                    && $firstButton.is(':visible')
                    && !$firstButton.is('.disabled')){
                    //$switchButton.addClass('clicked');
                    $firstButton.trigger('click');
                    setTimeout(function(){ $firstButton.removeClass('clicked'); }, 200);
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
            if (activeInputs.L1 || activeInputs.R1 || activeInputs.LR1){
                //console.log('%c' + 'Bumper key pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                let $playerButtons = $('.team-player', $playerSwitcher);
                if ($playerButtons.length < 2){ return true; } // nothing to switch to, ignore
                let $cursorPlayer = $playerButtons.filter('[data-player="player"]').first();
                let $activePlayer = $playerButtons.filter('.active').first();
                let $hoveredPlayer = $playerButtons.filter('.hovered').first();
                if (!$hoveredPlayer || !$hoveredPlayer.length){ $hoveredPlayer = $activePlayer; }
                // We must first focus the player switcher if not already focused
                if (!$playerSwitcher.is('.focused')){
                    //console.log('%c' + 'Focusing player switcher!', 'color: orange;');
                    $playerSwitcher.addClass('focused');
                    $playerButtons.removeClass('hovered');
                    if ((activeInputs.L1 && activeInputs.R1) || activeInputs.LR1){
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
                    if ((activeInputs.L1 && activeInputs.R1) || activeInputs.LR1){
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
                        //console.log('%c' + (activeInputs.L1 ? 'L1' : 'R1') + ' held, switching to hovered player!', 'color: orange;');
                        $playerButtons.removeClass('hovered');
                        if (activeInputs.L1){
                            let $prevPlayer = $hoveredPlayer.prevAll('.team-player').first();
                            if (!$prevPlayer || !$prevPlayer.length){ $prevPlayer = $playerButtons.last(); }
                            //console.log('-> trying to switch to $prevPlayer', $prevPlayer);
                            if ($prevPlayer.length){
                                //$prevPlayer.addClass('hovered');
                                $prevPlayer.trigger('mouseenter');
                                //console.log('-> triggered mouseenter on $prevPlayer');
                                } else {
                                $activePlayer.trigger('mouseenter');
                                //console.log('-> triggered mouseenter on $activePlayer instead of prev');
                                }
                            }
                        else if (activeInputs.R1){
                            let $nextPlayer = $hoveredPlayer.nextAll('.team-player').first();
                            if (!$nextPlayer || !$nextPlayer.length){ $nextPlayer = $playerButtons.first(); }
                            //console.log('-> trying to switch to $nextPlayer', $nextPlayer);
                            if ($nextPlayer.length){
                                //$nextPlayer.addClass('hovered');
                                $nextPlayer.trigger('mouseenter');
                                //console.log('-> triggered mouseenter on $nextPlayer');
                                } else {
                                $activePlayer.trigger('mouseenter');
                                //console.log('-> triggered mouseenter on $activePlayer instead of next');
                                }
                            }
                        ignoreInputFor(300);
                        return true;
                        }
                    }
                let focusTimeout = thisCache._playerSwitcherTimeout;
                if (focusTimeout){ clearTimeout(focusTimeout); }
                focusTimeout = setTimeout(function(){
                    $playerSwitcher.removeClass('focused');
                    $playerButtons.removeClass('hovered');
                    }, 2000);
                thisCache._playerSwitcherTimeout = focusTimeout;
                return true;
                }
            // If the player has pressed either of the triggers we should zoom/unzoom the map
            if (activeInputs.L2 || activeInputs.R2 || activeInputs.LR2){
                //console.log('%c' + 'Trigger key pressed!', 'color: orange;');
                //console.log('-> activeInputs: ', Object.keys(activeInputs).length ? activeInputs : 'none');
                if (event){ event.preventDefault(); }
                if ( (activeInputs.L2 && activeInputs.R2) || activeInputs.LR2){
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
                    //console.log('%c' + (activeInputs.L2 ? 'L2' : 'R2') + ' held, zooming ' + (activeInputs.L2 ? 'out' : 'in') + '!', 'color: orange;');
                    // otherwise we use L2 to zoom out and R1 to zoom in
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
                //console.log('_worldCursor.position = ' + _worldCursor.position);
                //console.log('_worldCursor.direction = ' + _worldCursor.direction);
                if (event){ event.preventDefault(); }
                if (sideButtonsActive){ dismissSideButtonAction(); }
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
                    let thisShiftDir = (function(v, h){ var s = []; if (v){ s.push(v); } if (h){ s.push(h); } return s.join('-'); })(thisVerDir, thisHorDir);
                    _worldCursor.direction = thisShiftDir; // the direction they are trying to move
                    _worldPlayer.direction = thisShiftDir;
                    //console.log('-> _worldCursor.direction to thisShiftDir(', thisShiftDir, ')', '\n--> (via thisVerDir = ', thisVerDir, '& thisHorDir =', thisHorDir, ')');
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
                        //console.log('%c' + 'Player is human, can only walk to adjacent tiles! (going ' + _worldCursor.direction + ')', 'color: red;');
                        _self.moveToPosition(oldPos, function(){
                            _self.refreshMapPositionEvents(0);
                            }, true);
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
                            _self.moveToPosition(oldPos, function(){
                                _self.refreshMapPositionEvents(0);
                                }, true);
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
    let gamepadLayout = null; // TODO: pull this from settings later
    let userInputWatcher = new mmrpgUserInputWatcher({
        autoStart: true,
        autoRunCallbacks: false,
        autoButtonMapping: true,
        gamepadLayout: gamepadLayout,
        });
    userInputWatcher.onUserInput(checkUserInputs);
    userInputWatcher.startWatching();
    let checkUserInputWatcher = function(){
        userInputWatcher.checkUserInputs();
        requestAnimationFrame(checkUserInputWatcher);
        };
    checkUserInputWatcher();
    _self.inputs = userInputWatcher;
    //console.log('check if loaded _self.inputs.userInputs:', _self.inputs.userInputs);

    // Return true on success
    return true;
    }

// Assign the sub-functions to the main class's prototype

mmrpgWorldMap.prototype.bindEventsToCanvas = bindEventsToCanvas;
mmrpgWorldMap.prototype.bindEventsToWorld = bindEventsToWorld;
mmrpgWorldMap.prototype.bindEventsToInputs = bindEventsToInputs;

