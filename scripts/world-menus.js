
// -- WORLD MENUS METHODS -- //

// Define a quick function for hovering a canvas UI object
function hoverCanvasMenuObject(e, sfx){
    //console.log('%c' + 'mmrpgWorldMap.hoverCanvasMenuObject()', 'color: magenta;');
    let _self = this;
    let $object = $(this);
    if (_self.worldIsBusy()){ return; }
    if ($object.is('.disabled')){ return; }
    if ($object.closest('.chrome').is('.disabled')){ return; }
    $thisCanvas.find('.hovered').removeClass('hovered');
    if (sfx){ _self.playSoundEffect(sfx); }
    $object.addClass('hovered');
    return true;
    }
function unhoverCanvasMenuObject(e){
    //console.log('%c' + 'mmrpgWorldMap.unhoverCanvasMenuObject()', 'color: magenta;');
    let $object = $(this);
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
    $minimapOverview.bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
    $minimapOverview.bind('mouseleave', unhoverCanvasObject);
    $minimapOverview.find('.button').bind('mouseenter', function(e){ hoverCanvasObject.call(this, e, 'icon-hover'); });
    $minimapOverview.find('.button').bind('mouseleave', unhoverCanvasObject);
    return true;
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