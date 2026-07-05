// Initialize the MMRPG global variables
var mmrpgBody = false;
var gameWindow = false;
var gameEngine = false;
var gameConnect = false;
var gameCanvas = false;
var gameConsole = false;
var gameActions = false;
var gameMusic = false;
var gameSettings = {};

// Initialize browser detection variables
var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
var isFirefox = typeof InstallTrigger !== 'undefined';   // Firefox 1.0+
var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
var isChrome = !!window.chrome && !isOpera;              // Chrome 1+
var isIE = /*@cc_on!@*/false || document.documentMode;   // At least IE6

// Define the MMRPG global settings variables
gameSettings.cacheTime = '00000000-00'; // the timestamp of when this game was last updated
gameSettings.baseHref = 'http://localhost/'; // the base href where this game is running
gameSettings.wapFlag = false; // whether or not this game is running in mobile mode
gameSettings.wapFlagIphone = false; // whether or not this game is running in mobile iphone mode
gameSettings.wapFlagIpad = false; // whether or not this game is running in mobile iphone mode
gameSettings.eventTimeout = 600; // default animation frame base internal
gameSettings.eventTimeoutDefault = 600; // default animation frame base internal
gameSettings.eventTimeoutThreshold = 250; // timeout theshold for when frames stop cross-fading
gameSettings.eventAutoPlay = true; // whether or not to automatically advance events
gameSettings.eventCrossFade = true; // whether or not to canvas events have crossfade animation
gameSettings.eventCameraShift = true; // whether or not to canvas events have camera shifts
gameSettings.eventSoundEffects = true; // whether or not to use sound effects for battle events
gameSettings.eventHooks = []; // default to empty but may be filled at runtime and used later
gameSettings.gameHasLoaded = false; // default to false so we can only set to true when ready
gameSettings.gameHasStarted = false; // default to false so we can only set to true when ready
gameSettings.idleAnimation = true; // default to allow idle animations
gameSettings.indexLoaded = false; // default to false until the index is loaded
gameSettings.currentGameState = {}; // default to empty but may be filled at runtime and used later
gameSettings.currentActionPanel = 'loading'; // default to loading until changed elsewhere
gameSettings.autoScrollTop = false; // default to true to prevent too much scrolling
gameSettings.autoResizeWidth = true; // allow auto reszing of the game window width
gameSettings.autoResizeHeight = true; // allow auto reszing of the game window height
gameSettings.currentBodyWidth = 0; // collect the current window width and update when necessary
gameSettings.currentBodyHeight = 0; // collect the current window width and update when necessary
gameSettings.allowEditing = true; // default to true to allow all editing unless otherwise stated
gameSettings.audioBaseHref = ''; // the base href where audio comes from (empty if same as baseHref)
gameSettings.onGameStart = []; // define an array to hold  events that have to wait until game start
gameSettings.customValues = {}; // define an object to hold miscelaneous custom values during runtime
gameSettings.customCounters = {}; // define an object to hold miscelaneous custom counters during runtime
gameSettings.customFlags = {}; // define an object to hold miscelaneous custom flags during runtime
gameSettings.customIndex = {}; // default to empty but may be filled at runtime and used later
gameSettings.customIndex.musicIndex = {} // predefine to be filled later so we don't get errors
gameSettings.customIndex.soundsIndex = {} // predefine to be filled later so we don't get errors
gameSettings.customIndex.soundsAliasesIndex = {} // predefine to be filled later so we don't get errors

// Define the customizable MMRPG settings variables
gameSettings.maxVolume = 1.0; // max volume for the game that cannot be exceeded (because that would be rude)
gameSettings.masterVolume = 0.5; // master volume for the game that is exactly in the middle of the road
gameSettings.musicVolume = 0.4; // music volume for the game, relative to master, slightly lower than effects
gameSettings.effectVolume = 0.6; // effect volume for the game, relative to master, slightly higher than e
gameSettings.menuEffectVolume = 0.6; // menu effect volume modifier, relative effect volume, may be unique later
gameSettings.musicVolumeEnabled = true; // default to true to allow music unless otherwise stated
gameSettings.musicTrackSpeed = 1.0; // the speed at which music tracks should be played (1.0 = normal)
gameSettings.effectVolumeEnabled = true; // default to true to allow music unless otherwise stated
gameSettings.audioBalanceConfig = {}; // default to empty but can hold custom overrides for above
gameSettings.battleButtonMode = 'default'; // the button mode we should be using for missions
gameSettings.enableGameMusic = true; // default to true to turn off on unsupported devices
gameSettings.enableSoundEffects = true; // default to true to turn off on unsupported devices
gameSettings.playVictorySound = true; // default to true to play the victory sound at the end of battles
gameSettings.playVictoryMusic = true; // default to true to play the victory music at the end of battles
gameSettings.playDefeatSound = true; // default to true to play the defeat sound at the end of battles
gameSettings.playDefeatMusic = true; // default to true to play the defeat music at the end of battles

// Define the perfect scrollbar settings
gameSettings.scrollbarSettings = {
    wheelSpeed: 0.3,
    useBothWheelAxes: false,
    suppressScrollX: true
    };

// Define an object to hold change events for settings when/if they happen
let gameSettingsChangeEvents = {};

// Create the game engine submit timer
var gameEngineSubmitTimeout = false;
var gameEngineSubmitReturn = false;
// Create a function for when the game engine is submit
function gameEngineSubmitFunction(){
    clearTimeout(gameEngineSubmitTimeout);
    gameEngineSubmitTimeout = false;
    //console.log('...it\'s been thirty seconds since gameEngine.submit()');
    var battleStatus = $('input[name=this_battle_status]', gameEngine).val();
    if (gameEngineSubmitReturn == false && battleStatus != 'complete'){
        //console.log('...and the server still has not responded.');
        var confirmRetry = confirm('The server has not responded for some time... \nWould you like to try sending the request again?');
        if (confirmRetry){
            //console.log('Resubmitting form.');
            gameEngine.submit();
            } else {
            //console.log('Resetting timeout.');
            requestAnimationFrame(function(){
                gameEngineSubmitTimeout = setTimeout(gameEngineSubmitFunction, 120000);
                });
            }
        return false;
        } else {
        //console.log('...and everything seems to have worked out. We\'re all good.');
        return true;
        }
}

// Initialize document ready events
$(document).ready(function(){

    // Define the MMRPG global context variables
    mmrpgBody = $('#mmrpg');
    gameWindow = $('#window');
    gameEngine = $('#engine');
    gameConnect = $('#connect');
    gameCanvas = $('#canvas');
    gameConsole = $('#console');
    gameActions = $('#actions');
    gameMusic = $('#music');
    gameAnimate = $('#animate');
    gameBattle = $('#battle');
    gamePrototype = $('#prototype');

    // Check if iPhone or iPad detected
    gameSettings.wapFlagiOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    gameSettings.wapFlagIphone = (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) ? true : false;
    gameSettings.wapFlagIpad = navigator.userAgent.match(/iPad/i) ? true : false;

    // If we're on either iPhone or iPad, we can't handle both music and sfx
    if (gameSettings.wapFlagiOS){ gameSettings.enableSoundEffects = false; }

    // If this window is not running as top, we need to overwrite some variables and functions
    if (window.self !== window.top){

        // Collect a reference to the topmost window
        var selfWindow = window.self;
        var topWindow = window.top;

        // Update the gameHasStarted variable by asking the parent for its setting
        if (typeof topWindow.gameSettings !== 'undefined'
            && typeof topWindow.gameSettings.gameHasStarted !== 'undefined'){
            gameSettings.gameHasStarted = topWindow.gameSettings.gameHasStarted;
        }

    }


    /*
     * INDEX EVENTS
     */

    if (mmrpgBody.length){

        // Start off with a loading class attached for css
        $('#mmrpg').addClass('loading');

        // Update the tooltip reference dimensions
        //console.log('Update the tooltip reference dimensions');
        gameSettings.currentBodyWidth = mmrpgBody.outerWidth();
        gameSettings.currentBodyHeight = mmrpgBody.outerHeight();
        //gameSettings.currentBodyWidth = $(document).width();
        //gameSettings.currentBodyHeight = $(document).height();
        //console.log('mmrpgBody.width() =', mmrpgBody.width());
        //console.log('mmrpgBody.height() =', mmrpgBody.height());
        //console.log('mmrpgBody.outerWidth() =', mmrpgBody.outerWidth());
        //console.log('mmrpgBody.outerHeight() =', mmrpgBody.outerHeight());
        //console.log('gameSettings.currentBodyWidth =', gameSettings.currentBodyWidth);
        //console.log('gameSettings.currentBodyHeight =', gameSettings.currentBodyHeight);

        // Tooltip only Text
        //console.log('assigning event for '+document.URL+';\n gameSettings.currentBodyWidth = '+gameSettings.currentBodyWidth+';\n gameSettings.currentBodyHeight = '+gameSettings.currentBodyHeight+'; ');

        // Only attach hover tooltips if NOT in mobile mode
        //if (!gameSettings.wapFlag && !gameSettings.wapFlagIphone && !gameSettings.wapFlagIpad){
        // Attempt to attach tooltips regardless of device
        if (true){
            //console.log('deciding to attach tooltips');

            let tooltipDelay = 1200; //600;
            let tooltipTimeout = false;
            let tooltipShowing = false;
            let tooltipInitiator = false;

            // Define the function for showing the tooltip
            let $lastTooltipElement = false;
            let showTooltipFunction = function(element, event){
                //console.log('showTooltipFunction() w/ element:', element, ' and event:', event);
                let $thisElement = $(element);
                let $tooltip = $('#mmrpg-tooltip', mmrpgBody);
                //console.log('-> checking $tooltip:', $tooltip);
                //console.log('-> comparing $thisElement:', $thisElement, ' to $lastTooltipElement:', $lastTooltipElement);
                if ($tooltip && $tooltip.length && $thisElement[0] === $lastTooltipElement[0]){
                    //console.log('same element, closing existing tooltip');
                    $lastTooltipElement = false;
                    $tooltip.removeClass('active');
                    $tooltip.empty();
                    return;
                    } else {
                    //console.log('different element, continuing to show new tooltip');
                    $lastTooltipElement = $thisElement;
                    }
                //var thisDate = new Date();
                //var thisTime = thisDate.getTime();
                //console.log('starting the tooltip at '+thisTime);
                var thisClassList = $thisElement.attr('class') != undefined ? $thisElement.attr('class').split(/\s+/) : '';
                var thisTitle = $thisElement.attr('data-backup-title') != undefined ? $thisElement.attr('data-backup-title') : ($thisElement.attr('title') != undefined ? $thisElement.attr('title') : '');
                var thisTooltip = $thisElement.attr('data-tooltip') != undefined ? $thisElement.attr('data-tooltip') : '';
                if (!thisTooltip.length && $thisElement.attr('data-click-tooltip') != undefined){ thisTooltip = $thisElement.attr('data-click-tooltip'); }
                if (!thisTitle.length && !thisTooltip.length){ return false; }
                else if (thisTitle.length && !thisTooltip.length){ thisTooltip = thisTitle; }
                thisTooltip = thisTooltip.replace(/\n/g, '<br />').replace(/\|\|/g, '<br />').replace(/\s?\/\/\s?/g, '<br />');
                thisTooltip = thisTooltip.replace(/\|/g, '<span class="pipe">|</span>');
                thisTooltip = thisTooltip.replace(/\*\*([^\[\]]+)\*\*/ig, '<strong>$1</strong>');
                thisTooltip = thisTooltip.replace(/\[\[([^\[\]]+)\]\]/ig, '<span class="subtext">$1</span>');
                var thisTooltipAlign = $thisElement.attr('data-tooltip-align') != undefined ? $thisElement.attr('data-tooltip-align') : 'left';
                var thisTooltipType = $thisElement.attr('data-tooltip-type') != undefined ? $thisElement.attr('data-tooltip-type') : '';
                var thisTooltipClass = $thisElement.attr('data-tooltip-class') != undefined ? $thisElement.attr('data-tooltip-class') : '';
                if (!thisTooltipType.length){
                    for (i in thisClassList){
                        var tempClass = thisClassList[i] != undefined ? thisClassList[i].toString() : '';
                        //console.log('tempClass = '+tempClass);
                        if (tempClass.match(/^(field_|player_|robot_|ability_|item_)?type$/) || tempClass.match(/^(field_|player_|robot_|ability_|item_)?type_/) || tempClass.match(/^(energy|weapons|attack|defense|speed|light|cossack|wily|experience|level|damage|recovery|none|cutter|impact|freeze|explode|flame|electric|time|earth|wind|water|swift|nature|missile|crystal|shadow|space|shield|laser|copy)(_|$)/)){
                            //console.log('tempClass match!');
                            thisTooltipType += tempClass+' ';
                            }
                        //console.log('thisTooltipType = '+thisTooltipType);
                        }
                    }
                if (!thisTooltipType.length){
                    thisTooltipType = 'type none';
                    }
                thisTooltipClass += ' '+thisTooltipType;
                //console.log('thisTitle : '+thisTitle);
                //console.log('append and trigger animation at '+thisTime);
                $thisElement.attr('data-backup-title', thisTitle).removeAttr('title');
                let messageMarkup = '<span class="message" style="text-align:'+thisTooltipAlign+';">'+thisTooltip+'</span>';
                if (!$tooltip.length){
                    $('<div id="mmrpg-tooltip" class="tooltip '+thisTooltipClass+'">' + messageMarkup + '</div>').appendTo(mmrpgBody);
                    $tooltip = $('#mmrpg-tooltip', mmrpgBody);
                    } else {
                    $tooltip.removeClass().addClass('tooltip '+thisTooltipClass).empty().html(messageMarkup);
                    }
                $tooltip.addClass('active').fadeIn('fast');
                // collect the position of the button that spawned the tooltip in the first place
                let spawnPosition = $thisElement.offset();
                //console.log('spawnPosition =', spawnPosition);
                alignTooltipFunction.call(this, event);
                tooltipShowing = true;
                if (typeof top.mmrpg_play_sound_effect !== 'undefined'){
                    top.mmrpg_play_sound_effect('tooltip-text');
                    }
                };
            window.mmrpgShowTooltipFunction = showTooltipFunction;

            // Define the function for positioning the tooltip (v2)
            let alignTooltipFunction = function(event){
                //console.log('alignTooltipFunction() w/ event:', event);
                let $tooltip = $('#mmrpg-tooltip', mmrpgBody);
                let targetX, targetY;
                if (typeof event.pageX !== 'undefined'
                    && typeof event.pageY !== 'undefined'){
                    //console.log('aligning to mouse position');
                    targetX = event.pageX;
                    targetY = event.pageY;
                    } else if (typeof $lastTooltipElement !== 'undefined'
                    && $lastTooltipElement.length){
                    //console.log('aligning to last element position', $lastTooltipElement);
                    let targetOffset = $lastTooltipElement.offset();
                    targetX = targetOffset.left + ($lastTooltipElement.outerWidth() / 2);
                    targetY = targetOffset.top + ($lastTooltipElement.outerHeight() / 2);
                    //let $target = $lastTooltipElement;
                    //targetX = $target.offsetLeft + ($target.offsetWidth / 2);
                    //targetY = $target.offsetTop + ($target.offsetHeight / 2);
                    } else {
                    //console.log('aligning to center of screen');
                    targetX = Math.floor(gameSettings.currentBodyWidth / 2);
                    targetY = Math.floor(gameSettings.currentBodyHeight / 2);
                    }
                return mmrpg_align_element_to_target($tooltip, targetX, targetY);
                };
            window.mmrpgAlignTooltipFunction = alignTooltipFunction;

            // Define the function for closing the tooltip
            let closeTooltipFunction = function(event){
                $('#mmrpg-tooltip', mmrpgBody).empty();
                clearTimeout(tooltipTimeout);
                tooltipTimeout = false;
                tooltipShowing = false;
                };
            window.mmrpgCloseTooltipFunction = closeTooltipFunction;

            // Create a variable to hold the tooltip selector
            let tooltipSelector;

            // If we're on the main website, we can use the standard hover events
            if (mmrpgBody.is('.index')){
                //console.log('we are on the website');

                // Define the live MOUSEENTER events for any elements with a title tag (which should be many)
                tooltipSelector = '*[title],*[data-backup-title]:not([data-click-tooltip]),*[data-tooltip]';
                $(tooltipSelector, mmrpgBody).live('mouseenter', function(event){
                    event.preventDefault();
                    if (tooltipTimeout == false){
                        var element = this;
                        tooltipInitiator = element;
                        requestAnimationFrame(function(){
                            tooltipTimeout = setTimeout(function(){
                                tooltipShowing = true;
                                showTooltipFunction(element, event);
                                }, tooltipDelay);
                            });
                        var $thisElement = $(this);
                        if ($thisElement.attr('title')){
                            $thisElement.attr('data-backup-title', $thisElement.attr('title'));
                            $thisElement.removeAttr('title');
                            }
                        }
                    });

            }
            // Otherwise, we should only use click events as those are more accessible
            else {
                //console.log('we are in the game somewhere');

                // Define the live CLICK events for any elements with a click-title tag (which should be a few)
                tooltipSelector = '*[data-click-tooltip]';
                $(tooltipSelector, mmrpgBody).live('click', function(event){
                    //console.log('tooltip click event!');
                    event.preventDefault();
                    event.stopPropagation();
                    var element = this;
                    requestAnimationFrame(function(){
                        showTooltipFunction(element, event);
                        if (typeof top.mmrpg_play_sound_effect !== 'undefined'){
                            top.mmrpg_play_sound_effect('tooltip-open');
                            }
                        });
                    });

                // If any extra input is detected, dismiss the tooltip immediately
                //document.addEventListener('keydown', hideTooltip);
                //document.addEventListener('mousewheel', hideTooltip);
                //document.addEventListener('gamepadinput', hideTooltip);

            }

            // Define the live MOUSEMOVE events for any elements with a title tag (which should be many)
            $(tooltipSelector, mmrpgBody).live('mousemove', function(e){
                if (!tooltipShowing){ return false; }
                alignTooltipFunction.call(this, e);
                });

            // Define the live MOUSELEAVE events for any elements with a title tag (which should be many)
            $(tooltipSelector, mmrpgBody).live('mouseleave', function(e){
                e.preventDefault();
                closeTooltipFunction.call(this, e);
                /*
                $('#mmrpg-tooltip', mmrpgBody).empty();
                clearTimeout(tooltipTimeout);
                tooltipTimeout = false;
                tooltipShowing = false;
                */
                });

            // If the user clicks somewhere in the body, immediately remove the tooltip
            $('*', mmrpgBody).click(function(e){
                if (e.target === tooltipInitiator){ return; }
                closeTooltipFunction.call(this, e);
                /*
                $('#mmrpg-tooltip', mmrpgBody).empty();
                clearTimeout(tooltipTimeout);
                tooltipTimeout = false;
                tooltipShowing = false;
                */
                });

            }

        // Now that everything is set up, wait for all images before we actually display
        $('#mmrpg').waitForImages(function(){
        $   ('#mmrpg').removeClass('loading');
            });

    }

    // -- GAME AUDIO SETUP -- //

    // Only run audio setup on the top-most layer of windows
    if (window.top === window.self){
        //console.log('%c' + 'Loading Game Audio ... ', 'color: magenta;');

        // Require interaction from the user before allowing other clicks/hovers
        /* (function(){
            let $mmrpg = $('#mmrpg');
            let userHasClicked = false, updateUserHasClicked = function(e){
                //console.log('...click detected...');
                if (typeof e.originalEvent === 'undefined'){ return; }
                //console.log('User has clicked!');
                $mmrpg.unbind('click', updateUserHasClicked);
                $mmrpg.removeClass('first-focus-required');
                userHasClicked = true;
                };
            $mmrpg.addClass('first-focus-required');
            $mmrpg.bind('click', updateUserHasClicked);
            })(); */

        // Autmatically load the music index in json format via ajax into memory for later if not there
        if (typeof gameSettings.customIndex.musicIndex === 'undefined'
            || !Object.keys(gameSettings.customIndex.musicIndex).length){
            gameSettings.customIndex.musicIndex = {};
            //console.log('gameSettings.customIndex.musicIndex =', gameSettings.customIndex.musicIndex);
            if (!Object.keys(gameSettings.customIndex.musicIndex).length){
                //console.log('loading the music index!');
                $.ajax({
                    url: 'api/v2/music/index',
                    dataType: 'json',
                    success: function(response){
                        //console.log('api/v2/music/index returned ', response);
                        if (typeof response.data !== 'undefined'
                            && typeof response.data.music !== 'undefined'){
                            gameSettings.customIndex.musicIndex = response.data.music;
                            //console.log('gameSettings.customIndex.musicIndex =', gameSettings.customIndex.musicIndex);
                            }
                        }
                    });
                }
            }

        // Automatically define the sounds index to we don't get errors if it hasn't been defined
        if (typeof gameSettings.customIndex.soundsIndex === 'undefined'
            || !Object.keys(gameSettings.customIndex.soundsIndex).length){
            gameSettings.customIndex.soundsIndex = {};
            }

        // If a sounds index exists, use it to populate the internal effect sources and sprites index
        //console.log('gameSettings.customIndex.soundsIndex =', gameSettings.customIndex.soundsIndex);
        if (typeof gameSettings.customIndex.soundsIndex !== 'undefined'
            && Object.keys(gameSettings.customIndex.soundsIndex).length){
            var soundsIndexIndex = gameSettings.customIndex.soundsIndex;
            var rawSoundSources = soundsIndexIndex.src;
            var soundSources = [];
            for (var i = 0; i < rawSoundSources.length; i++){
                var sourcePath = 'sounds/'+rawSoundSources[i];
                var sourcePathFull = gameSettings.audioBaseHref+sourcePath;
                soundSources.push(sourcePathFull);
                }
            var rawSoundSprites = soundsIndexIndex.sprite;
            var soundSprites = {};
            var soundSpritesTokens = [];
            soundSpritesTokens = Object.keys(rawSoundSprites);
            for (var i = 0; i < soundSpritesTokens.length; i++){
                var spriteToken = soundSpritesTokens[i];
                var spriteData = rawSoundSprites[spriteToken];
                soundSprites[spriteToken] = [spriteData['start'], spriteData['duration'], spriteData['loop']];
                }
            gameSettings.soundEffectSources = soundSources;
            gameSettings.soundEffectSprites = soundSprites;
            //console.log('rawSoundSources = ', rawSoundSources);
            //console.log('rawSoundSprites = ', rawSoundSprites);
            //console.log('soundSources = ', soundSources);
            //console.log('soundSprites = ', soundSprites);
            //console.log('gameSettings.soundEffectSources = ', gameSettings.soundEffectSources);
            //console.log('gameSettings.soundEffectSprites = ', gameSettings.soundEffectSprites);
            }

        }

    // Ensure this is the battle document
    if (gameWindow.length){

        // -- GAME MUSIC & AUDIO FUNCTIONS -- //

        // Set up the game music options
        if (gameMusic.length){

            // Automatically load the title screen music
            mmrpg_music_load('misc/player-select', true, false);

            // Add the click-events to the music toggle button
            $('a.toggle', gameMusic).bind('click touch', function(e){
                e.preventDefault();
                if (gameSettings.indexLoaded){
                    if ($('iframe', gameWindow).hasClass('loading')){ $('iframe', gameWindow).css({opacity:0}).removeClass('loading').animate({opacity:1}, 1000, 'swing'); } // DEBUG
                    if (gameMusic.hasClass('onload')){
                        // THIS is the GAME START button
                        gameMusic.removeClass('onload');
                        gameMusic.find('.start').remove();
                        mmrpg_music_toggle();
                        setTimeout(function(){ top.mmrpg_play_sound_effect('game-start'); }, 100);
                        setTimeout(function(){ gameSettings.gameHasStarted = true; }, 200);
                        if (gameSettings.onGameStart.length){
                            //console.log('gameSettings.onGameStart =', gameSettings.onGameStart);
                            while (gameSettings.onGameStart.length){
                                var onGameStart = gameSettings.onGameStart.shift();
                                if (typeof onGameStart === 'function'){ onGameStart.call(); }
                                }
                            }
                        } else {
                        // THIS is just a simple MUSIC TOGGLE
                        mmrpg_music_toggle();
                        }
                    return true;
                    } else {
                    return false;
                    }
                });

            }

    }

    /*
     * BATTLE EVENTS
     */

    // Ensure this is the battle document
    if (gameEngine.length){

        // Define a change event for whenever this game setting is altered
        gameSettingsChangeEvents['eventTimeout'] = function(newValue){
            //console.log('setting eventTimeout to ', newValue, typeof newValue);
            updateCameraShiftTransitionDuration();
            var $actionButton = $('.button.action_option[data-panel="settings_eventTimeout"]', gameActions);
            if ($actionButton.length){
                var newValueTitle = '(1f/'+newValue+'ms)';
                if (typeof gameSettings.customIndex.gameSpeeds !== 'undefined'){
                    var gameSpeedIndex = gameSettings.customIndex.gameSpeeds;
                    var defaultGameSpeed = gameSettings.eventTimeoutDefault;
                    if (typeof gameSpeedIndex[newValue] !== 'undefined'){
                        newValueTitle = gameSpeedIndex[newValue]['name'];
                        } else {
                        newValueTitle = gameSpeedIndex[defaultGameSpeed]['name'];
                        gameSettings.eventTimeout = defaultGameSpeed;
                        }
                    }
                $actionButton.find('.value').html(newValueTitle);
                }
            };
        gameSettingsChangeEvents['eventTimeout'](gameSettings.eventTimeout);

        // Define a change event for whenever this game setting is altered
        gameSettingsChangeEvents['eventCrossFade'] = function(newValue){
            //console.log('setting eventCrossFade to ', newValue, ' w/ timeout at ', gameSettings.eventTimeout);
            updateCameraShiftTransitionDuration();
            };
        gameSettingsChangeEvents['eventCrossFade'](gameSettings.eventCrossFade);

        // Define a change event for whenever this game setting is altered
        gameSettingsChangeEvents['eventCameraShift'] = function(newValue){
            //console.log('setting eventCameraShift to ', newValue);
            updateCameraShiftTransitionDuration(0);
            if (newValue === false){
                mmrpg_canvas_camera_shift();
                }
            else if (newValue === true
                && gameSettings.currentActionPanel !== 'loading'
                && !mmrpgEvents.length){
                canvasAnimationCameraShift = {
                    shift: 'left',
                    focus: 'active',
                    depth: 0,
                    depthInc: 1,
                    offset: 0,
                    };
                }
            updateCameraShiftTransitionDuration();
            };
        gameSettingsChangeEvents['eventCameraShift'](gameSettings.eventCameraShift);

        // Auto-highlight settings buttons that are "active"
        var settingsWithActiveStates = ['eventTimeout', 'eventCrossFade'];
        for (var i = 0; i < settingsWithActiveStates.length; i++){
            var settingsKey = settingsWithActiveStates[i];
            var settingsValue = gameSettings[settingsKey];
            if (typeof settingsValue === 'undefined'){ continue; }
            if (typeof settingsValue === 'boolean'){ settingsValue = settingsValue ? 'true' : 'false'; }
            var settingsButtonWrapper = $('.wrapper.actions_settings_'+settingsKey, gameActions);
            var activeSettingsButton = settingsButtonWrapper.find('a[data-action="settings_'+settingsKey+'_'+settingsValue+'"]');
            settingsButtonWrapper.find('a[data-action]').removeClass('active');
            activeSettingsButton.addClass('active');
            }

        // Attach a submit event for tracking timestaps
        gameEngine.submit(function(){
            //console.log('gameEngine.submit() triggered, setting timeout');
            clearTimeout(gameEngineSubmitTimeout);
            gameEngineSubmitTimeout = false;
            canvasAnimationCameraTimer = 0;
            requestAnimationFrame(function(){
                gameEngineSubmitTimeout = setTimeout(gameEngineSubmitFunction, 120000);
                });
            });

        // Add click-events to the hidden resend command
        $('#actions .actions_resend', mmrpgBody).live('click', function(e){
            e.preventDefault();
            //console.log('actions_resend clicked');
            var loadingDisplay = $('#actions_loading', mmrpgBody).css('display');
            if (loadingDisplay == 'none'){ return false; }
            var confirmText = 'Would you like to resubmit your last action?\nThis can have unpredicable results on your battle...\nResend anyway?';
            if (confirm(confirmText)){
                // Switch to the loading screen
                //console.log('switch to loading panel');
                mmrpg_action_panel('loading');
                //console.log('Resubmitting form.');
                gameEngine.submit();
                }
            });

        // Set up rge game animate options
        if (true){

            // Add the click-events to the animate toggle button
            $('a.toggle', gameAnimate).bind('click touch', function(e){
                e.preventDefault();
                mmrpg_toggle_animation();
                return true;
                });
            // Automatically start the animation sequences
            //mmrpg_start_animation();

            }

        // Add a click event to the gameActions panel buttons
        $('a[data-panel]', gameActions).live('click', function(e){
            var thisPanel = $(this).attr('data-panel');
            mmrpg_action_panel(thisPanel);
            });

        // Add a click event to the gameActions action buttons
        $('a[data-action]', gameActions).live('click', function(e){
            // Collect the action and preload, if set
            var thisAction = $(this).attr('data-action');
            var thisPreload = $(this).attr('data-preload') !== undefined ? $(this).attr('data-preload') : false;
            var thisTarget = $(this).attr('data-target') !== undefined ? $(this).attr('data-target') : false;
            //var thisPanel = $(this).parent().parent().attr('id');
            var thisPanel = $(this).closest('.wrapper').attr('id');
            thisPanel = thisPanel.replace(/^actions_/i, '');
            //alert(thisPanel);
            // If this is a destructive action, make sure we confirm first
            if (thisAction === 'withdraw'
                && !confirm('Are you sure you want to withdraw from this mission?\n'
                    + 'Unsaved progress will be lost and you will have to start over.\n'
                    + 'Clear progress and withdraw from the mission anyway?'
                    )){
                return false;
                }
            // Trigger the requested action and return the result
            //console.log('triggering action '+thisAction+' with preload '+thisPreload+' and target '+thisTarget+' from panel '+thisPanel);
            return mmrpg_action_trigger(thisAction, thisPreload, thisTarget, thisPanel);
            });

        // Add a hover event to all the gameAction sprites
        $('.sprite[data-action]', gameCanvas)
            .live('mouseenter', function(){
                if ($('#actions_scan', gameActions).is(':visible')){
                    $(this).css({cursor:'pointer'});
                    if ($(this).hasClass('sprite_40x40')){ var thisSize = 40; }
                    else if ($(this).hasClass('sprite_80x80')){ var thisSize = 80; }
                    else if ($(this).hasClass('sprite_160x160')){ var thisSize = 160; }
                    $(this).addClass('sprite_'+thisSize+'x'+thisSize+'_focus');
                    var thisOffset = parseInt($(this).css('z-index'));
                    $('.event', gameCanvas).append('<div class="scan_overlay" style="z-index: '+(thisOffset-1)+';">&nbsp;</div>');
                    } else {
                    $(this).css({cursor:'default'});
                    return false;
                    }
                })
            .live('mouseleave', function(){
                if ($(this).hasClass('sprite_40x40')){ var thisSize = 40; }
                else if ($(this).hasClass('sprite_80x80')){ var thisSize = 80; }
                else if ($(this).hasClass('sprite_160x160')){ var thisSize = 160; }
                $(this).removeClass('sprite_'+thisSize+'x'+thisSize+'_focus');
                $('.scan_overlay', gameCanvas).remove();
                });

        // Add scan functionality to all on-screen robot sprites
        $('.sprite[data-action]', gameCanvas).live('click', function(){
            if ($('#actions_scan', gameActions).is(':visible')){
                $(this).css({cursor:'pointer'});
                // Collect the action and preload, if set
                var thisAction = $(this).attr('data-action');
                var thisPreload = $(this).attr('data-preload')  !== undefined ? $(this).attr('data-preload') : false;
                var thisTarget = $(this).attr('data-target')  !== undefined ? $(this).attr('data-target') : false;
                // Remove the focus class now clicked
                if ($(this).hasClass('sprite_40x40')){ var thisSize = 40; }
                else if ($(this).hasClass('sprite_80x80')){ var thisSize = 80; }
                else if ($(this).hasClass('sprite_160x160')){ var thisSize = 160; }
                $(this).removeClass('sprite_'+thisSize+'x'+thisSize+'_focus');
                // Trigger the requested action and return the result
                return mmrpg_action_trigger(thisAction, thisPreload, thisTarget);
                } else {
                $(this).css({cursor:'default'});
                return false;
                }
            });

        // Add a specialized click event for the gameActions continue button
        $('a[data-action=continue]', gameActions).live('click', function(e){
            mmrpg_events();
            });

        // Start animating the canvas randomly
        //mmrpg_canvas_animate();

        // Trigger the panel switch to the "next" action
        var nextAction = $('input[name=next_action]', gameEngine).val();
        if (nextAction.length){ mmrpg_action_panel(nextAction); }

    }

    /*
     * WINDOW RESIZE EVENTS
     */

    // Remove the hard-coded heights for the main iframe
    $('iframe', gameWindow).removeAttr('width').removeAttr('height');

    // Trigger the windowResizeUpdate function automatically
    windowResizeUpdate('startup');
    window.onresize = function(){ return windowResizeUpdate('onresize'); }


    /*
     * MOBILE EVENTS
     */

    // Check if we're running the game in mobile mode
    if (gameSettings.wapFlag){

        //alert('wapFlag');

        // Remove the hard-coded heights for the main iframe
        $('iframe', gameWindow).removeAttr('width').removeAttr('height');

        // Let the user know about the full-screen option for mobile browsers
        if (('standalone' in window.navigator) && !window.navigator.standalone){
            //alert('Please use "Add to Home Screen" option for best view! :)');
            } else if (('standalone' in window.navigator) && window.navigator.standalone){
            //alert('launched from full-screen ready browser, and in full screen!');
            $('body').addClass('mobileFlag_fullScreen');
            } else {
            //alert('launched from a regular old browser...');
            }

    }

    // Create the variable functions for Pausing/Unpausing the game
    var mmrpgPauseFunction = function(){
        //gameSettings.eventAutoPlay = false;
        //clearTimeout(canvasAnimationTimeout);
        clearInterval(canvasAnimationTimeout);
        //mmrpg_music_stop();
        };
    var mmrpgPlayFunction = function(){
        //gameSettings.eventAutoPlay = true;
        if (gameSettings.eventAutoPlay && !mmrpgEvents.length){ mmrpg_canvas_animate(); }
        //mmrpg_music_play();
        };

    // Attach blur/focus events to the window to automatically pause/play
    if (gameBattle.length){
        //console.log('gameBattle.length = '+gameBattle.length+';');
        $(window).blur(function(){
            //console.log('mmrpgPauseFunction();');
            mmrpgPauseFunction();
            }).focus(function(){
            //console.log('mmrpgPlayFunction();');
            mmrpgPlayFunction();
            });
    }


});

// Define a function for updating the window sizes
var lastOrientation = false;
var defaultViewportSettings = false;
var defaultViewportUpdated = false;
var windowResizeUpdateTimeout = false;
function windowResizeUpdate(updateType){
    //console.log('windowResizeUpdate('+updateType+')');

    /*
    // Collect the actual screen sizes for reference
    var screenWidth = window.innerWidth
        || document.documentElement.clientWidth
        || document.body.clientWidth;
    var screenHeight = window.innerHeight
        || document.documentElement.clientHeight
        || document.body.clientHeight;
    //console.log('screenWidth =', screenWidth, '| screenHeight =', screenHeight);
    */

    // Collect viewport settings in case we have to adjust
    if (!defaultViewportSettings){
        var $metaViewport = $('head meta[name="viewport"]');
        var viewportContent = $metaViewport.attr('content');
        if (typeof viewportContent !== 'undefined'
            && viewportContent.length){
            viewportContent = viewportContent.replace(/\s+/g, '').split(',');
            //console.log('viewportContent =', viewportContent);
            defaultViewportSettings = {};
            for (var i = 0; i < viewportContent.length; i++){
                var setting = viewportContent[i].split('=');
                var settingName = setting[0];
                var settingValue = setting[1];
                if (settingValue.match(/^[0-9]+$/)){ settingValue = parseInt(settingValue); }
                defaultViewportSettings[settingName] = settingValue;
                }
            //console.log('defaultViewportSettings =', defaultViewportSettings);
            }
        }

    // Re-generate the viewport settings, skipping any unsupported settings
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var newOrientation = 'portrait';
    if (window === window.top && (updateType === 'startup' || lastOrientation !== newOrientation)){
        var newViewportSettings = $.extend(true,{},defaultViewportSettings);
        if (typeof newViewportSettings['min-width'] !== 'undefined'){
            var minWidth = newViewportSettings['min-width'];
            delete newViewportSettings['min-width'];
            if (windowWidth < minWidth){
                newViewportSettings['width'] = minWidth;
                newViewportSettings['initial-scale'] = windowWidth / minWidth;
                //if (typeof newViewportSettings['initial-scale'] !== 'undefined'){
                //    delete newViewportSettings['initial-scale'];
                //    }
                }
            }
        var newViewportContent = [];
        var newViewportSettingsKeys = Object.keys(newViewportSettings);
        for (var i = 0; i < newViewportSettingsKeys.length; i++){
            var settingName = newViewportSettingsKeys[i];
            var settingValue = newViewportSettings[settingName];
            newViewportContent.push(settingName+'='+settingValue);
            }
        //console.log('newViewportContent =', newViewportContent.join(', '));
        $('head meta[name="viewport"]').remove();
        var $metaViewport = $('<meta name="viewport" />');
        $metaViewport.attr('content', newViewportContent.join(', '));
        $metaViewport.appendTo('head');
        lastOrientation = newOrientation;
        }

    // Define the base values to resize from
    var canvasHeight = 267;
    var consoleHeight = 256;
    var consoleMessageHeight = 64;
    var actionsHeight = 225;
    //console.log('windowResizeUpdate('+updateType+');\n', {canvasHeight:canvasHeight,consoleHeight:consoleHeight,consoleMessageHeight:consoleMessageHeight,actionsHeight:actionsHeight});

    // Check if this is the main window or if it's a child
    if (window === window.top){
        // Collect this window's width and height
        var windowType = 'top';
        var windowWidth = $(window).width();
        var windowHeight = $(window).height();
        var gameWidth = gameWindow.width();
        var gameHeight = gameWindow.height();
        } else {
        // Collect the parent window's width and height
        var windowType = 'child';
        var windowWidth = $(top.window).width();
        var windowHeight = $(top.window).height();
        var gameWidth = top.gameWindow.width();
        var gameHeight = top.gameWindow.height();
        }

    var bodyInnerHeight = mmrpgBody.innerHeight();
    //console.log('windowType = '+windowType+' \nwindowWidth = '+windowWidth+' \nwindowHeight = '+windowHeight+' \nbodyInnerHeight = '+bodyInnerHeight);
    if (bodyInnerHeight < windowHeight){ windowHeight = bodyInnerHeight; }

    // Update the window resize dimensions
    //console.log('Update the window resize dimensions');
    //gameSettings.currentBodyWidth = windowWidth;
    //gameSettings.currentBodyHeight = windowHeight;
    //gameSettings.currentBodyWidth = $(document).width();
    //gameSettings.currentBodyHeight = $(document).height();
    gameSettings.currentBodyWidth = mmrpgBody.outerWidth();
    gameSettings.currentBodyHeight = mmrpgBody.outerHeight();
    //console.log('mmrpgBody.width() =', mmrpgBody.width());
    //console.log('mmrpgBody.height() =', mmrpgBody.height());
    //console.log('mmrpgBody.outerWidth() =', mmrpgBody.outerWidth());
    //console.log('mmrpgBody.outerHeight() =', mmrpgBody.outerHeight());
    //console.log('gameSettings.currentBodyWidth =', gameSettings.currentBodyWidth);
    //console.log('gameSettings.currentBodyHeight =', gameSettings.currentBodyHeight);

    //console.log({windowWidth:windowWidth,windowHeight:windowHeight,gameWidth:gameWidth,gameHeight:gameHeight,gameSettings:gameSettings});

    var windowModWidth = localStorage.getItem('mmrpg-window-width') || 'small';
    var windowModHeight = localStorage.getItem('mmrpg-window-height') || 'small';

    // Calculate the new game and console height values
    var newGameHeight = windowHeight - 25; //15;
    if (gameSettings.wapFlagIphone && newGameHeight > 924){ newGameHeight = 924; }
    var newConsoleHeight = newGameHeight - (canvasHeight + actionsHeight);
    //console.log({windowHeight:windowHeight,newGameHeight:newGameHeight,newConsoleHeight:newConsoleHeight});

    if ((newConsoleHeight - 3) < (consoleMessageHeight * 2)){
        var thisMinimum = consoleMessageHeight * 2;
        newGameHeight = newGameHeight + (thisMinimum - newConsoleHeight);
        newConsoleHeight = thisMinimum + 3;
        //console.log({thisMinimum:thisMinimum,newGameHeight:newGameHeight,newConsoleHeight:newConsoleHeight});
        } else if ((newConsoleHeight - 3) %  consoleMessageHeight != 0){
        var thisRemainer = (newConsoleHeight - 3) %  consoleMessageHeight;
        newGameHeight = newGameHeight - thisRemainer;
        newConsoleHeight = newConsoleHeight - thisRemainer;
        //console.log({thisRemainer:thisRemainer,newGameHeight:newGameHeight,newConsoleHeight:newConsoleHeight});
        }

    // If the console exists, resize it
    if (gameConsole.length && !gameConsole.hasClass('noresize')){
        //console.log('gameConsole.length && !gameConsole.hasClass(\'noresize\');\ngameConsole.height('+newConsoleHeight+' - 3); ');
        gameConsole.height(newConsoleHeight - 3);
        var gameConsoleWrapper = gameConsole.find('.wrapper');
        //gameConsoleWrapper.css({overflow:'scroll',width:(gameConsole.width() + 18)+'px',height:(gameConsole.height() + 18)+'px'});
        gameConsoleWrapper.css({width:(gameConsole.width() + 18)+'px',height:(gameConsole.height() + 0)+'px'});
        if (typeof $.fn.perfectScrollbar !== 'undefined'){ gameConsoleWrapper.perfectScrollbar(gameSettings.scrollbarSettings); }
        }

    // If height reszing is allowed, update the window height
    if (gameSettings.autoResizeHeight != false){
        //console.log('gameSettings.autoResizeHeight != false;\ngameWindow.height('+newGameHeight+');');
        gameWindow.height(newGameHeight);
        $('iframe', gameWindow).height(newGameHeight - 6);
        }

    // Reset the window scroll to center elements properly
    if (gameSettings.autoScrollTop === true && updateType != 'onscroll'){
        //console.log('gameSettings.autoScrollTop == true;\nwindow.scrollTo(0, 1);');
        window.scrollTo(0, 1);
        if (window !== window.top){ top.window.scrollTo(0, 1); }
        }


    // Tooltip only Text
    //console.log('resizing event for '+document.URL+';\n gameSettings.currentBodyWidth = '+gameSettings.currentBodyWidth+';\n gameSettings.currentBodyHeight = '+gameSettings.currentBodyHeight+'; ');

    // Return true on success
    return true;
}

function localFunction(myMessage){
    alert(myMessage);
}

// Define a function for quickly checking if cross-fade is currently enabled by the user
function mmrpg_cross_fade_enabled(){
    let eventCrossFadeEnabled = gameSettings.eventCrossFade === true ? true : false;
    let eventTimeoutAboveThreshold = gameSettings.eventTimeout > gameSettings.eventTimeoutThreshold ? true : false;
    if (!eventTimeoutAboveThreshold){ eventCrossFadeEnabled = false; }
    //console.log('gameSettings.eventCrossFade = ', gameSettings.eventCrossFade);
    //console.log('gameSettings.eventTimeout = ', gameSettings.eventTimeout);
    //console.log('gameSettings.eventTimeoutThreshold = ', gameSettings.eventTimeoutThreshold);
    //console.log('eventTimeoutAboveThreshold = ', eventTimeoutAboveThreshold);
    //console.log('eventCrossFadeEnabled = ', eventCrossFadeEnabled);
    return eventCrossFadeEnabled;
}

// Define a function for randomly animating canvas robots (idle animation, background animation, more)
var backgroundDirection = 'left';
var canvasAnimationTimeout = false;
var canvasAnimationCameraShift = false;
var canvasAnimationCameraLastShift = false;
var canvasAnimationCameraTimer = 0;
var canvasAnimationCameraDelay = 5;
function mmrpg_canvas_animate(){
    //console.log('mmrpg_canvas_animate();');
    //console.log('gameSettings.idleAnimation:', gameSettings.idleAnimation);
    //console.log('canvasAnimationCameraTimer:', canvasAnimationCameraTimer, 'canvasAnimationCameraDelay:', canvasAnimationCameraDelay);
    //clearTimeout(canvasAnimationTimeout);
    clearInterval(canvasAnimationTimeout);
    if (!gameSettings.idleAnimation){  return false; }

    // Collect the current battle status and result
    var battleStatus = $('input[name=this_battle_status]', gameEngine).val();
    var battleResult = $('input[name=this_battle_result]', gameEngine).val();


    // Check to see if we should skip camera animations for any reason
    var skipCameraShift = false;
    var currentGameState = gameSettings.currentGameState;
    var currentAction = currentGameState['this_action'];
    var currentActionPanel = gameSettings.currentActionPanel;
    //console.log({eventCameraShift:gameSettings.eventCameraShift,currentActionPanel:gameSettings.currentActionPanel,mmrpgEventsLength:mmrpgEvents.length});
    //console.log('currentGameState:', currentGameState);
    //console.log('currentAction:', currentAction);
    //console.log('currentActionPanel:', currentActionPanel);
    if (currentActionPanel === 'loading' && currentAction === 'start'){ skipCameraShift = true; }
    //console.log('skipCameraShift:', skipCameraShift);

    // If the camera is not yet shifted, checked to see if we randomly should
    if (!skipCameraShift
        && gameSettings.eventCameraShift
        && !mmrpgEvents.length){

        // Increment the camera shift timer and, when ready, trigger some motion
        //console.log('canvasAnimationCameraTimer = ', canvasAnimationCameraTimer);
        var canvasAnimationCameraTimerMax = canvasAnimationCameraDelay;
        if (battleStatus == 'complete'){ canvasAnimationCameraTimerMax = Math.ceil(canvasAnimationCameraTimerMax / 2); }
        if (!canvasAnimationCameraShift
            && canvasAnimationCameraTimer >= canvasAnimationCameraTimerMax){
            var shiftRandom = Math.floor(Math.random() * 100);
            if (shiftRandom <= 33){
                var lastShift = canvasAnimationCameraLastShift;
                if (typeof lastShift.shift !== 'undefined'){ var shiftDirection = lastShift.shift !== 'left' ? 'left' : 'right'; }
                else { var shiftDirection = (shiftRandom % 2 === 0 ? 'left' : 'right'); }
                var focusRandom = Math.floor(Math.random() * 100);
                var depthRandom = Math.floor(Math.random() * 100);
                canvasAnimationCameraShift = {
                    shift: shiftDirection,
                    focus: (focusRandom % 3 === 0 ? 'bench' : 'active'),
                    depth: (depthRandom % 2 === 0 ? 0 : 8),
                    depthInc: (depthRandom % 2 === 0 ? 1 : -1),
                    offset: 0,
                    };
                if (battleStatus == 'complete'){
                    if (battleResult == 'victory'){ canvasAnimationCameraShift.shift = 'left'; }
                    else if (battleResult == 'defeat'){ canvasAnimationCameraShift.shift = 'right'; }
                    }
                canvasAnimationCameraTimer = 0;
                canvasAnimationCameraLastShift = canvasAnimationCameraShift;
                //console.log('canvasAnimationCameraShift:', canvasAnimationCameraShift);
                }
            }

        // If the camera is currently being shifted, we need to animate that
        if (canvasAnimationCameraShift){
            canvasAnimationCameraShift.depth += canvasAnimationCameraShift.depthInc;
            if (canvasAnimationCameraShift.depth > 8
                || canvasAnimationCameraShift.depth < 1){
                canvasAnimationCameraShift = false;
                updateCameraShiftTransitionTiming();
                updateCameraShiftTransitionDuration();
                mmrpg_canvas_camera_shift();
                } else {
                mmrpg_canvas_camera_shift(
                    canvasAnimationCameraShift.shift,
                    canvasAnimationCameraShift.focus,
                    canvasAnimationCameraShift.depth,
                    canvasAnimationCameraShift.offset
                    );
                updateCameraShiftTransitionTiming('linear');
                updateCameraShiftTransitionDuration(1);
                }
            } else {
            canvasAnimationCameraTimer++;
            }

        } else {

        // We should not be animating now
        canvasAnimationCameraShift = false;

        }

    // Loop through all field layers on the canvas
    $('.background[data-animate],.foreground[data-animate]', gameCanvas).each(function(){
        // Trigger an animation frame change for this field
        var thisField = $(this);
        mmrpg_canvas_field_frame(thisField, '');
        });

    // Loop through all field sprites on the canvas
    $('.sprite[data-animate]', gameCanvas).each(function(){
        // Trigger an animation frame change for this field
        var thisSprite = $(this);
        var thisType = thisSprite.attr('data-type');
        // Call the animation function based on sprite type
        if (thisType == 'attachment'){
            if (thisSprite.attr('data-status') != 'disabled' || thisSprite.attr('data-direction') == 'right'){
                mmrpg_canvas_attachment_frame(thisSprite, '');
            } else {
            //alert('sprite is disabled');
            // Fade this sprite off-screen
            //thisSprite.animate({opacity:0},1000,'linear',function(){ $(this).remove(); });
            var spriteKind = thisSprite.attr('data-type');
            var spriteID = thisSprite.attr('data-'+spriteKind+'-id');
            //alert('sprite kind is '+spriteKind+' and its ID is '+spriteID);
            var shadowSprite = $('.sprite[data-shadow-id='+spriteID+']', gameCanvas);
            //var detailsSprite = $('.sprite[data-detailsid='+spriteID+']', gameCanvas);
            //var mugshotSprite = $('.sprite[data-mugshotid='+spriteID+']', gameCanvas);
            //alert('Shadowsprite '+(shadowSprite.length ? 'exists' : 'does not exist')+'!');
            if (mmrpg_cross_fade_enabled()){
                //console.log('normal animation');
                // We're at a normal speed, so we can animate normally
                let fadeDuration = gameSettings.eventTimeoutThreshold; //Math.ceil(gameSettings.eventTimeout / 2);
                thisSprite.stop(true, true).animate({opacity:0},fadeDuration,'linear',function(){ $(this).remove(); });
                if (shadowSprite.length){ shadowSprite.stop(true, true).animate({opacity:0},fadeDuration,'linear',function(){ $(this).remove(); }); }
                } else {
                //console.log('speedy animation');
                // We're at a super-fast speed, so we should NOT cross-fade
                thisSprite.stop(true, true).remove();
                if (shadowSprite.length){ shadowSprite.stop(true, true).remove(); }
                }
            }
        }

        });

    // Loop through all players on the field
    $('.sprite[data-type="player"]', gameCanvas).each(function(){

        // Collect a reference to the current player
        var thisPlayer = $(this);
        // Generate a random number
        var thisRandom = Math.floor(Math.random() * 100);
        // Default the new frame to base
        var newFrame = 'base';
        var extraStyles = {};
        // Define the relative battle result
        var relativeResult = 'pending';
        if (battleStatus == 'complete'){
            relativeResult = thisPlayer.attr('data-direction') == 'right' ? (battleResult) : (battleResult == 'victory' ? 'defeat' : 'victory');
            }
        // If the there are no more events to display
        if (!mmrpgEvents.length){
            // If the player has been defeated, only show one frame, otherwise randomize
            if (relativeResult == 'defeat'){
                // Defeault to the defeat frame
                newFrame = 'defeat';
                } else {
                // Higher animation freqency if not active
                if (thisPlayer.attr('data-position') != 'active'){
                    if (battleStatus == 'complete' && thisRandom >= 50){
                        newFrame = relativeResult;
                        } else if (thisRandom >= 80){
                        newFrame = 'taunt';
                        } else if (thisRandom >= 60){
                        newFrame = 'base2';
                        }
                    } else {
                    if (battleStatus == 'complete' && thisRandom >= 50){
                        newFrame = relativeResult;
                        } else if (thisRandom >= 80){
                        newFrame = 'taunt';
                        } else if (thisRandom >= 60){
                        newFrame = 'base2';
                        }
                    }
                }
            // Check to see if we should be applying any extra styles
            //console.log('gameSettings.currentActionPanel =', gameSettings.currentActionPanel);
            if (typeof gameSettings.customFlags.isSwitching === 'undefined'){
                gameSettings.customFlags.isSwitching = false;
                }
            if (gameSettings.currentActionPanel === 'switch'){
                gameSettings.customFlags.isSwitching = true;
                extraStyles = {transform: 'scaleX(-1) translateX(-10%)'};
                }
            else if (gameSettings.customFlags.isSwitching === true) {
                gameSettings.customFlags.isSwitching = false;
                extraStyles = {transform: ''};
                }
            else {
                if (newFrame !== 'base' && thisRandom % 11 === 0){ extraStyles = {transform: 'scaleX(-1) translateX(-10%)'}; }
                else if (thisRandom % 3 === 0){ extraStyles = {transform: ''}; }
                }
            }

        // Trigger the player frame advancement
        //console.log('thisRandom:', thisRandom, 'newFrame:', newFrame, 'extraStyles:', extraStyles);
        mmrpg_canvas_player_frame(thisPlayer, newFrame, extraStyles);

        });


    // Loop through all robots on the field
    $('.sprite[data-type="robot"]', gameCanvas).each(function(){

        // Collect a reference to the current robot
        let thisRobot = $(this);
        let robotIsRescue = thisRobot.is('.rescue') ? true : false;
        // Ensure the robot has not been disabled
        if (thisRobot.attr('data-status') != 'disabled'){
            // Generate a random number
            var shiftChance = Math.floor(Math.random() * 100);
            var thisRandom = Math.floor(Math.random() * 100);
            //console.log('shiftChance =', shiftChance);
            // Default the new frame to base
            var newFrame = 'base';
            var currentFrame = thisRobot.attr('data-frame');
            // Define the relative battle result
            var relativeResult = 'pending';
            if (battleStatus == 'complete'){
                relativeResult = thisRobot.attr('data-direction') == 'right' ? (battleResult) : (battleResult == 'victory' ? 'defeat' : 'victory');
                }
            // If the there are no more events to display
            if (!mmrpgEvents.length){
                // If the player has been defeated, only show one frame, otherwise randomize
                if (relativeResult == 'defeat'){
                    // Defeault to the defeat frame
                    newFrame = robotIsRescue ? 'victory' : 'defeat';
                    } else {
                    // Special defense-only animations for rescue robots
                    if (robotIsRescue){
                        if (currentFrame === 'defend' && thisRandom >= 90){
                            newFrame = 'base';
                            } else {
                            newFrame = 'defend';
                            }
                        }
                    // Else only change to an action frame if currently base
                    else if (currentFrame == 'base'){
                        // Animation freqency based on position
                        if (thisRobot.attr('data-position') != 'active'){
                            // Higher animation freqency if not active (BENCH)
                            if (battleStatus == 'complete' && shiftChance >= 90){
                                newFrame = relativeResult;
                                } else if (thisRandom >= 80){
                                newFrame = 'base2';
                                } else if (thisRandom >= 50){
                                newFrame = 'taunt';
                                } else if (thisRandom >= 40){
                                newFrame = 'defend';
                                }
                            } else {
                            // Lower animation freqency if active (ACTIVE)
                            if (battleStatus == 'complete' && shiftChance >= 80){
                                newFrame = relativeResult;
                                } else if (thisRandom >= 90){
                                newFrame = 'base2';
                                } else if (thisRandom >= 30){
                                newFrame = 'defend';
                                } else if (thisRandom >= 20){
                                newFrame = 'taunt';
                                }
                            }
                        }
                    }
                }
            // Trigger the robot frame advancement
            mmrpg_canvas_robot_frame(thisRobot, newFrame);
            var spriteKind = thisRobot.attr('data-type');
            var spriteID = thisRobot.attr('data-'+spriteKind+'-id');
            var shadowSprite = $('.sprite[data-shadow-id='+spriteID+']', gameCanvas);
            if (shadowSprite.length){ mmrpg_canvas_robot_frame(shadowSprite, newFrame);  }

            }
        else if (thisRobot.attr('data-status') == 'disabled' && thisRobot.attr('data-direction') == 'right'){

            // Default the new frame to base
            //var newFrame = 'base';
            // Trigger the robot frame advancement
            //mmrpg_canvas_robot_frame(thisRobot, newFrame);

            }
        else {

            //alert('robot is disabled');
            // Fade this robot off-screen
            var spriteKind = thisRobot.attr('data-type');
            var spriteID = thisRobot.attr('data-'+spriteKind+'-id');
            //alert('sprite kind is '+spriteKind+' and its ID is '+spriteID);
            var shadowSprite = $('.sprite[data-shadow-id='+spriteID+']', gameCanvas);
            var detailsSprite = $('.sprite[data-detailsid='+spriteID+']', gameCanvas);
            var mugshotSprite = $('.sprite[data-mugshotid='+spriteID+']', gameCanvas);
            //alert('Shadowsprite '+(shadowSprite.length ? 'exists' : 'does not exist')+'!');
            thisRobot.stop(true, true).animate({opacity:0},1000,'linear',function(){
                $(this).remove();
                if (shadowSprite.length){ shadowSprite.stop(true, true).animate({opacity:0},1000,'linear',function(){ $(this).remove(); }); }
                if (detailsSprite.length){ detailsSprite.stop(true, true).animate({opacity:0},1000,'linear',function(){ $(this).remove(); }); }
                if (mugshotSprite.length){ mugshotSprite.stop(true, true).animate({opacity:0},1000,'linear',function(){ $(this).remove(); }); }
                });

            }

        });

    // Reset the timeout event for another animation round
    if (canvasAnimationTimeout != false){ window.clearTimeout(canvasAnimationTimeout); }
    if (!canvasAnimationTimeout.length){
        requestAnimationFrame(function(){
            canvasAnimationTimeout = window.setTimeout(function(){
                //console.log('mmrpg_canvas_animate');
                mmrpg_canvas_animate(); // DEBUG PAUSE
                }, gameSettings.eventTimeout);
            });
        }
    // Return true for good measure
    return true;
}

// Define a function for updating a fields's frame with animation
function mmrpg_canvas_field_frame(thisField, newFrame){
    // Generate a new frame if one was not provided
    if (newFrame == ''){
        // Collect a reference to the current field data
        var thisFieldFrame = thisField.attr('data-frame');
        var thisAnimateFrame = thisField.attr('data-animate').split(',');
        var thisAnimateFrameCount = thisAnimateFrame.length;
        // Default the new frame to base
        if (thisAnimateFrameCount > 1){
            var thisIndex = thisAnimateFrame.indexOf(thisFieldFrame);
            if ((thisIndex + 1) < thisAnimateFrameCount){
                var newFrame = thisAnimateFrame[thisIndex + 1];
                } else {
                var newFrame = thisAnimateFrame[0];
                }
        } else {
            var newFrame = thisAnimateFrame[0];
        }
    }
    // Collect this field's data fields (hehe)
    var thisFrame = thisField.attr('data-frame');
    // If the new frame is the same as the current, return
    if (thisFrame == newFrame || thisField.is(':animated')){ return false; }
    // Define the current class (based on data) and the new class
    var fieldLayer = thisField.hasClass('background') ? 'background' : 'foreground';
    var currentClass = fieldLayer+'_'+thisFrame;
    var newClass = fieldLayer+'_'+newFrame;
    // Check to make sure event crossfade is enabled
    if (mmrpg_cross_fade_enabled()){
        // Create a clone object with the new class and crossfade it into view
        var cloneField = thisField.clone().css('z-index', '10').appendTo(thisField.parent());
        thisField.stop(true, true).css({opacity:0}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
        thisField.stop(true, true).animate({opacity:1}, {duration:Math.ceil(gameSettings.eventTimeout * 0.5),easing:'swing',queue:false});
        cloneField.stop(true, true).animate({opacity:1}, {duration:Math.ceil(gameSettings.eventTimeout * 0.5),easing:'swing',queue:false,complete:function(){ $(this).remove(); }});
        } else {
        // Update the existing sprite's frame without crossfade by swapping the classsa
        thisField.attr('data-frame', newFrame).stop(true, true).removeClass(currentClass).addClass(newClass);
        }
    // Return true on success
    return true;
}

// Define the sprite frame index
var spriteFrameIndex = {};

// Define a function for updating a robot's frame with animation
spriteFrameIndex.robots = ['base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage','base2'];
function mmrpg_canvas_robot_frame(thisRobot, newFrame){
    // Collect this robot's data fields
    var thisSize = thisRobot.attr('data-size');
    var thisPosition = thisRobot.attr('data-position');
    var thisDirection = thisRobot.attr('data-direction');
    var thisStatus = thisRobot.attr('data-status');
    var thisKey = parseInt(thisRobot.attr('data-key'));
    var thisFrame = thisRobot.attr('data-frame');
    var isShadow = thisRobot.attr('data-shadow-id') != undefined ? true : false;
    var newFramePosition = spriteFrameIndex.robots.indexOf(newFrame) || 0;
    // If the new frame is the same as the current, return
    if (thisFrame == newFrame){ return false; }
    // If this robot is disabled, do not animate
    if (thisStatus == 'disabled'){
        //thisRobot.animate({opacity:0},1000,'swing',function(){ $(this).remove(); });
        return false;
        }
    // Define the current class (based on data) and the new class
    var currentClass = 'sprite_'+thisSize+'x'+thisSize+'_'+thisFrame;
    var newClass = 'sprite_'+thisSize+'x'+thisSize+'_'+newFrame;
    // Define the new background offset for the frame
    var backgroundOffset = -1 * Math.ceil(newFramePosition * thisSize);
    //alert('backgroundOffset = '+backgroundOffset);
    // Stop this robot from animating further
    thisRobot.stop(true, true);
    // Check to make sure event crossfade is enabled
    if (mmrpg_cross_fade_enabled()){
        // Create a clone object with the new class and crossfade it into view
        var cloneRobot = thisRobot.clone().css('z-index', '-=1').appendTo(thisRobot.parent());
        thisRobot.stop(true, true).css({opacity:0,backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
        thisRobot.stop(true, true).animate({opacity:1}, {duration:400,easing:'swing',queue:false});
        cloneRobot.stop(true, true).animate({opacity:0}, {duration:400,easing:'swing',queue:false,complete:function(){ $(this).remove(); }});
        /*
        // Maybe play a sound effect if allowed and frame is correct?
        // No I hate it now, this can't work until everyone's movements
        // are independent of the event timer and more organic sounding
        if (typeof top.mmrpg_play_sound_effect !== 'undefined'){
            if (newFrame === 'defend' || newFrame === 'taunt'){
                if (thisPosition === 'bench'){
                    var delay = 100 + (thisKey + 50);
                    setTimeout(function(){ top.mmrpg_play_sound_effect('defend-sound', {volume: 0.1}, false); }, delay);
                    } else {
                    top.mmrpg_play_sound_effect('defend-sound', {volume: 0.1}, false);
                    }
                }
            }
        */
        } else {
        // Update the existing sprite's frame without crossfade by swapping the classsa
        thisRobot.stop(true, true).css({backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
        }
    // Return true on success
    return true;
}

// Define a function for updating a player's frame with animation
spriteFrameIndex.players = ['base','taunt','victory','defeat','command','damage','base2'];
function mmrpg_canvas_player_frame(thisPlayer, newFrame, extraStyles){
    // Collect this player's data fields
    var thisSize = thisPlayer.attr('data-size');
    var thisPosition = thisPlayer.attr('data-position');
    var thisDirection = thisPlayer.attr('data-direction');
    var thisStatus = thisPlayer.attr('data-status');
    var thisFrame = thisPlayer.attr('data-frame');
    var newFramePosition = spriteFrameIndex.players.indexOf(newFrame) || 0;
    if (typeof extraStyles !== 'object' || !extraStyles){ extraStyles = false; }
    //if (true){ alert(newFrame+' : '+newFramePosition); }
    // If the new frame is the same as the current, return
    if (thisFrame == newFrame){ return false; }
    // If this player is disabled, do not animate
    if (thisStatus == 'disabled'){ return false; }
    // Define the current class (based on data) and the new class
    var currentClass = 'sprite_'+thisSize+'x'+thisSize+'_'+thisFrame;
    var newClass = 'sprite_'+thisSize+'x'+thisSize+'_'+newFrame;
    // Define the new background offset for the frame
    var backgroundOffset = -1 * Math.ceil(newFramePosition * thisSize);
    //if (backgroundOffset > 0){ alert('newFrame : '+newFrame+', newFramePosition : '+newFramePosition+', backgroundOffset : '+backgroundOffset+''); }
    // Check to make sure event crossfade is enabled
    if (mmrpg_cross_fade_enabled()){
        // Create a clone object with the new class and crossfade it into view
        var clonePlayer = thisPlayer.clone().css('z-index', '-=1').appendTo(thisPlayer.parent());
        thisPlayer.stop(true, true).css({opacity:0,backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
        if (extraStyles){ thisPlayer.css(extraStyles); }
        thisPlayer.stop(true, true).animate({opacity:1}, {duration:400,easing:'swing',queue:false});
        clonePlayer.stop(true, true).animate({opacity:0}, {duration:400,easing:'swing',queue:false,complete:function(){ $(this).remove(); }});
        } else {
        // Update the existing sprite's frame without crossfade by swapping the classsa
        thisPlayer.stop(true, true).css({backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
        if (extraStyles){ thisPlayer.css(extraStyles); }
        }
    // Return true on success
    return true;
}

// Define a function for updating an attachment's frame with animation
spriteFrameIndex.attachments = ['00','01','02','03','04','05','06','07','08','09', '10'];
function mmrpg_canvas_attachment_frame(thisAttachment, newFrame){
    // If the newFrame or newIndex are empty
    if (newFrame === ''){
        // Collect a reference to the current attachment properties
        var thisAttachmentFloat = thisAttachment.attr('data-direction') == 'left' ? 'right' : 'left';
        var thisAttachmentFrame = thisAttachment.attr('data-frame');
        var thisAnimateFrame = thisAttachment.attr('data-animate').split(',');
        var thisAnimateFrameShift = thisAttachment.attr('data-animate-shift') != undefined ? thisAttachment.attr('data-animate-shift').split('|') : false;
        var thisAnimateFrameIndex = thisAttachment.attr('data-animate-index') != undefined ? parseInt(thisAttachment.attr('data-animate-index')) : 0;
        var thisAnimateFrameCount = thisAnimateFrame.length;
        // Default the new frame to base
        var newIndex = 0;
        var newFrame = thisAnimateFrame[newIndex];
        if (thisAnimateFrameCount > 1 && (thisAnimateFrameIndex + 1) < thisAnimateFrameCount){
            newIndex = thisAnimateFrameIndex + 1;
            newFrame = thisAnimateFrame[newIndex];
            }
        var newFrameShift = thisAnimateFrameShift.length ? thisAnimateFrameShift[newIndex] : thisAttachment.css(thisAttachmentFloat)+','+thisAttachment.css('bottom');
        newFrameShift = newFrameShift.split(',');
        var newFrameShiftX = newFrameShift[0]+'px';
        var newFrameShiftY = newFrameShift[1]+'px';
        }

    // DEBUG
    // If there was a frame shift defined
    if (false && thisAnimateFrameShift){
        // DEBUG
        //console.log('ID = '+thisAttachment.attr('data-id'));
        //console.log('newIndex = '+newIndex);
        //console.log('newFrame = '+newFrame);
        //console.log('newFrameShiftX = '+newFrameShiftX);
        //console.log('newFrameShiftY = '+newFrameShiftY);
        }


    // Collect this robot's data fields
    var thisSize = thisAttachment.attr('data-size');
    //var thisPosition = thisAttachment.attr('data-position');
    var thisDirection = thisAttachment.attr('data-direction');
    var thisFloat = thisDirection == 'left' ? 'right' : 'left';
    var thisFrame = thisAttachment.attr('data-frame');
    var thisPosition = thisAttachment.attr('data-position');
    var thisIndex = thisAttachment.attr('data-animate-index');
    //console.log('checkpoint1');
    // If the new frame is the same as the current, return
    if (thisFrame == newFrame && thisIndex == newIndex && !thisAnimateFrameShift){ return false; }
    // Define the new frame position in the index
    var newFramePosition = spriteFrameIndex.attachments.indexOf(newFrame) || 0;
    // Define the new background offset for the frame
    var backgroundOffset = -1 * Math.ceil(newFramePosition * thisSize);
    // Define the current class (based on data) and the new class
    var currentClass = 'sprite_'+thisSize+'x'+thisSize+'_'+thisFrame;
    var newClass = 'sprite_'+thisSize+'x'+thisSize+'_'+newFrame;
    //console.log('checkpoint2');
    // If the frame has changed, animate to the next image, otherwise just update properties
    if (thisFrame != newFrame || thisAnimateFrameShift){
        //console.log('checkpoint3');
        // Check to make sure event crossfade is enabled
        if ((thisPosition !== 'background' && thisPosition !== 'foreground') && mmrpg_cross_fade_enabled()){
            // Create a clone object with the new class and crossfade it into view
            var cloneAttachment = thisAttachment.clone().css('z-index', '-=1').appendTo(thisAttachment.parent());
            thisAttachment.stop(true, true).css({opacity:0,backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).attr('data-animate-index', newIndex).removeClass(currentClass).addClass(newClass);
            // If the frame's offsets have changed, update the css offsets
            if (thisAnimateFrameShift){ thisAttachment.stop(true, true).css(thisFloat, newFrameShiftX).css('bottom', newFrameShiftY); }
            // Fade this attachment back into view and fade the cloned attachment in the old frame out
            thisAttachment.stop(true, true).animate({opacity:1}, {duration:Math.ceil(gameSettings.eventTimeout / 2),easing:'swing',queue:false});
            cloneAttachment.stop(true, true).animate({opacity:0}, {duration:Math.ceil(gameSettings.eventTimeout / 2),easing:'swing',queue:false,complete:function(){ $(this).remove(); }});
            } else {
            // If the frame's offsets have changed, update the css offsets
            if (thisAnimateFrameShift){ thisAttachment.stop(true, true).css(thisFloat, newFrameShiftX).css('bottom', newFrameShiftY); }
            // Update the existing sprite's frame without crossfade by swapping the classsa
            thisAttachment.stop(true, true).css({backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).attr('data-animate-index', newIndex).removeClass(currentClass).addClass(newClass);
            }
    }  else {
        // If the frame's offsets have changed, update the css offsets
        if (thisAnimateFrameShift){ thisAttachment.stop(true, true).css(thisFloat, newFrameShiftX).css('bottom', newFrameShiftY); }
        // Simply update the parameters on this sprite frame
        thisAttachment.attr('data-animate-index', newIndex);
    }
    // Return true on success
    return true;
}

// Define a function for triggering an action submit
function mmrpg_action_trigger(thisAction, thisPreload, thisTarget, thisPanel){
    //console.log('thisAction : '+thisAction);
    // Return false if this is a continue click
    if (thisAction == 'continue'){ return false; }
    if (thisTarget == undefined){ thisTarget = 'auto'; }
    if (thisPanel == undefined){ thisPanel = 'battle'; }
    // Set the submitEngine flag to true by default
    var submitEngine = true;
    var nextPanel = false;

    // Switch to the loading screen
    mmrpg_action_panel('loading');

    // If the target was set to auto, pull the data from the engine
    if (thisTarget == 'auto'){
        //var autoTargetID = $('target_robot_id', gameEngine).val();
        //var autoTargetToken = $('target_robot_token', gameEngine).val();
        //thisTarget = autoTargetID+'_'+autoTargetToken;
        }

    // Parse any actions with subtokens in their string
    if (thisAction.match(/^ability_([-a-z0-9_]+)$/i)){

        // Parse the ability token and clean the main action token
        var thisAbility = thisAction.replace(/^ability_([-a-z0-9_]+)$/i, '$1');
        // If this ability's target is not set to auto
        if (thisTarget == 'select_this'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_this';
            } else if (thisTarget == 'select_this_disabled'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_this_disabled';
            } else if (thisTarget == 'select_this_ally'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_this_ally';
            } else if (thisTarget == 'select_target'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_target';
            }
        mmrpg_engine_update({this_action_token:thisAbility});
        thisAction = 'ability';

        }
    else if (thisAction.match(/^item_([-a-z0-9_]+)$/i)){

        // Parse the item token and clean the main action token
        var thisItem = thisAction.replace(/^item_([-a-z0-9_]+)$/i, '$1');
        // If this item's target is not set to auto
        if (thisTarget == 'select_this'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_this';
            } else if (thisTarget == 'select_this_disabled'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_this_disabled';
            } else if (thisTarget == 'select_this_ally'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_this_ally';
            } else if (thisTarget == 'select_target'){
            // Make sure the engine is not submit yet
            submitEngine = false;
            // Make sure the next panel is the target
            nextPanel = 'target_target';
            }
        mmrpg_engine_update({this_action_token:thisItem});
        thisAction = 'item';

        }
    else if (thisAction.match(/^switch_([-a-z0-9_]+)$/i)){

        // Parse the switch token and clean the main action token
        var thisSwitch = thisAction.replace(/^switch_([-a-z0-9_]+)$/i, '$1');
        mmrpg_engine_update({this_action_token:thisSwitch});
        thisAction = 'switch';

        }
    else if (thisAction.match(/^scan_([-a-z0-9_]+)$/i)){

        // Parse the scan token and clean the main action token
        var thisScan = thisAction.replace(/^scan_([-a-z0-9_]+)$/i, '$1');
        mmrpg_engine_update({this_action_token:thisScan});
        thisAction = 'scan';

        }
    else if (thisAction.match(/^target_([-a-z0-9_]+)$/i)){

        // Parse the target token and clean the main action token
        var thisTarget = thisAction.replace(/^target_([-a-z0-9_]+)$/i, '$1');
        //alert('thisTarget '+thisTarget);
        thisTarget = thisTarget.split('_');
        mmrpg_engine_update({target_robot_id:thisTarget[0]});
        mmrpg_engine_update({target_robot_token:thisTarget[1]});
        thisAction = '';

        }
    else if (thisAction.match(/^settings_([-a-z0-9]+)_([-a-z0-9_]+)$/i)){

        // Parse the settings token and value, then clean the action token
        var thisSettingToken = thisAction.replace(/^settings_([-a-z0-9]+)_([-a-z0-9_]+)$/i, '$1');
        var thisSettingValue = thisAction.replace(/^settings_([-a-z0-9]+)_([-a-z0-9_]+)$/i, '$2');
        if (thisSettingValue === 'true'){ thisSettingValue = true; }
        else if (thisSettingValue === 'false'){ thisSettingValue = false; }
        gameSettings[thisSettingToken] = thisSettingValue;
        var thisRequestType = 'session';
        var thisRequestData = 'battle_settings,'+thisSettingToken+','+thisSettingValue;
        $.post('scripts/script.php',{requestType: 'session',requestData: 'battle_settings,'+thisSettingToken+','+thisSettingValue});
        if (typeof gameSettingsChangeEvents[thisSettingToken] === 'function'){ gameSettingsChangeEvents[thisSettingToken](thisSettingValue); }

        // Make sure this setting button has the "active" class, remove any others
        var thisActionButton = $('a[data-action="'+thisAction+'"]', gameActions);
        var thisActionButtonWrapper = thisActionButton.closest('.main_actions');
        thisActionButtonWrapper.find('a[data-action]').removeClass('active');
        thisActionButton.addClass('active');

        thisAction = 'settings';
        nextAction = 'settings_'+thisSettingToken;
        if (nextAction.length){ mmrpg_action_panel(nextAction); }

        //var nextAction = $('input[name=next_action]', gameEngine).val();
        //if (nextAction.length){ mmrpg_action_panel(nextAction, thisPanel); }

        return true;

        }

    // Check if image preloading was requested
    if (thisPreload.length){
        // Preload the requested image
        var thisPreloadImage = $(document.createElement('img'))
            .attr('src', thisPreload)
            .load(function(){
                // Update the engine and trigger a submit event
                if (thisAction.length){ mmrpg_engine_update({this_action:thisAction}); }
                if (submitEngine == true){ gameEngine.submit(); }
                if (nextPanel != false){ mmrpg_action_panel(nextPanel, thisPanel); }
                return true;
                });
        } else {
            // Update the engine and trigger a submit event
            if (thisAction.length){ mmrpg_engine_update({this_action:thisAction}); }
            if (submitEngine == true){ gameEngine.submit(); }
            if (nextPanel != false){ mmrpg_action_panel(nextPanel, thisPanel); }
            return true;
            }
}

// Define a function for preloading assets
var asset_sprite_cache = [];
var asset_sprite_images = [
    'images/assets/battle-scene_gridlines-2k23_under.png?20230616',
    'images/assets/battle-scene_gridlines-2k23_over.png?20230616',
    'images/assets/battle-scene_robot-details-5.png?20230609-6',
    'images/tiles/horizontal-gradient_energy-bar-large.png?20150719-01',
    'images/tiles/horizontal-gradient_weapons-bar-large.gif?20130805-02',
    'images/tiles/horizontal-gradient_experience-bar-large.gif?20130805-02',
    'images/assets/battle-scene_robot-details-5_item.png?20230609-3',
    'images/tiles/horizontal-gradient_energy-bar.gif',
    'images/assets/battle-scene_gridlines-resized_event-banner.png',
    'images/assets/battle-scene_robot-results_2k23.png?20230608-3',
    'images/abilities/_effects/stat-arrows/sprite_left_80x80.png??',
    'images/abilities/_effects/stat-arrows/sprite_right_80x80.png??',
    'images/objects/defeat-explosion/sprite_left_80x80.png??'
    ];
function mmrpg_preload_assets(){
    //console.log('mmrpg_preload_assets()');
    // Loop through each of the asset images
    for (key in asset_sprite_images){
        // Define the sprite path value
        var sprite_path = asset_sprite_images[key];
        // check if the last two characters of the path is a question mark, replace the last one with the gameSettings.cacheTime manually
        if (sprite_path.substr(-2) === '??'){ sprite_path = sprite_path.replace(/\?$/, gameSettings.cacheTime); }
        // Cache this image in the appropriate array
        var cacheImage = document.createElement('img');
        cacheImage.src = sprite_path;
        //console.log('cacheImage.src =', cacheImage.src)
        asset_sprite_cache.push(cacheImage);
    }
}

// Define a function for preloading field sprites
var field_sprite_cache = {};
var field_sprite_frames = ['base'];
var field_sprite_kinds = ['background', 'foreground'];
var field_sprite_types = ['gif', 'png'];
function mmrpg_preload_field_sprites(fieldKind, fieldToken){
    // If this sprite has not already been cached
    if (!field_sprite_cache[fieldToken]){
        //alert('creating sprite cache for '+fieldToken);
        // Define the container for this robot's cache
        field_sprite_cache[fieldToken] = [];
        // Define the sprite path and counter values
        var sprite_path = 'images/fields/'+fieldToken+'/';
        var num_frames = field_sprite_frames.length;
        var num_kinds = field_sprite_kinds.length;
        // Loop through all the sizes and frames
        for (var i = 0; i < num_frames; i++){
            // Collect the current frame, size, and filename
            var this_frame = field_sprite_frames[i];
            var this_type = field_sprite_types[field_sprite_kinds.indexOf(fieldKind)];
            var this_kind = fieldKind;
            var file_name = 'battle-field_'+this_kind+'_'+this_frame+'.'+this_type;
            // Cache this image in the apporiate array
            var cacheImage = document.createElement('img');
            cacheImage.src = sprite_path+file_name+'?'+gameSettings.cacheTime;
            field_sprite_cache[fieldToken].push(cacheImage);
            //alert(field_path+file_name);
        }
    }
    //alert('sprite cache '+field_sprite_cache[fieldToken].length);
}

// Define a function for preloading robot sprites
var robotSpriteCache = {};
var robotSpriteTypes = ['mug', 'sprite'];
var robotSpriteExtension = 'png';
function mmrpg_preload_robot_sprites(thisRobotToken, thisRobotDirection, thisRobotSize){
    //console.log('mmrpg_preload_robot_sprites(thisRobotToken:', thisRobotToken, ', thisRobotDirection:', thisRobotDirection, ', thisRobotSize:', thisRobotSize, ')');
    // If this sprite has not already been cached
    if (thisRobotToken == false || thisRobotToken == 0 || thisRobotToken == ''){ return false; }
    var thisCacheToken = thisRobotToken+'_'+thisRobotDirection+'_'+thisRobotSize;
    if (!robotSpriteCache[thisCacheToken]){
        //console.log('creating sprite cache for '+thisRobotToken);
        // Define the container for this robot's cache
        robotSpriteCache[thisCacheToken] = [];
        // Define the sprite path and counter values
        var robotSpritePath = 'images/robots/'+thisRobotToken+'/';
        var numRobotTypes = robotSpriteTypes.length;
        // Loop through all the sizes and frames
        for (var i = 0; i < numRobotTypes; i++){
            // Collect the current frame, size, and filename
            var thisSpriteType = robotSpriteTypes[i];
            var thisSpriteSizeAdjusted = thisSpriteType === 'mug' ? Math.ceil(thisRobotSize / 2) : thisRobotSize;
            var thisSpriteToken = thisSpriteType+'_'+thisRobotDirection+'_'+thisSpriteSizeAdjusted+'x'+thisSpriteSizeAdjusted;
            var thisSpriteFilename = thisSpriteToken+'.'+robotSpriteExtension;
            //console.log('thisSpriteFilename =', thisSpriteFilename, ';');
            // Cache this image in the apporiate array
            var thisCacheImage = document.createElement('img');
            thisCacheImage.src = robotSpritePath+thisSpriteFilename+'?'+gameSettings.cacheTime;
            robotSpriteCache[thisCacheToken].push(thisCacheImage);
            //console.log('thisCacheImage.src =', thisCacheImage.src, ';');
        }
    }
    //console.log('robotSpriteCache (', robotSpriteCache.length, ') =', robotSpriteCache);
}

// Using the same format as above, create a function for preloading any other type of image by partial URL
function mmrpg_preload_misc_image(thisImageURL, includeCacheTime){
    // Define default for optional arguments
    if (includeCacheTime == undefined){ includeCacheTime = false; }
    // If this sprite has not already been cached
    if (!robotSpriteCache[thisImageURL]){
        // Define the container for this robot's cache
        robotSpriteCache[thisImageURL] = [];
        // Cache this image in the apporiate array
        var thisCacheImage = document.createElement('img');
        thisCacheImage.src = thisImageURL+(includeCacheTime ? '?'+gameSettings.cacheTime : '');
        robotSpriteCache[thisImageURL].push(thisCacheImage);
    }
}

// Define a function for updating the engine form
function mmrpg_engine_update(newValues){
    if (gameEngine.length){
        // Loop through the game engine values and update them
        for (var thisName in newValues){
            var thisValue = newValues[thisName];
            // Update the value in the global settings object
            gameSettings.currentGameState[thisName] = thisValue;
            // And then also update it in the DOM for form submission
            if ($('input[name='+thisName+']', gameEngine).length){
                $('input[name='+thisName+']', gameEngine).val(thisValue);
                } else {
                gameEngine.append('<input type="hidden" class="hidden" name="'+thisName+'" value="'+thisValue+'" />');
                }
            }
        }
}

// Define a function for switching to a different action panel
function mmrpg_action_panel(thisPanel, currentPanel){

    // Update the current panel in the game settings for reference
    gameSettings.currentActionPanel = thisPanel;

    // Switch to the event actions panel
    $('.wrapper', gameActions).css({display:'none'});
    var newWrapper = $('#actions_'+thisPanel, gameActions);
    if (currentPanel != undefined){
        newWrapper.find('.action_back').attr('data-panel', currentPanel);
        var newWrapperTitle = newWrapper.find('.main_actions_title');
        if (newWrapperTitle.length){ newWrapperTitle.html(newWrapperTitle.html().replace('{thisPanel}', currentPanel)); }
        //alert('thisPanel = '+thisPanel+'; currentPanel = '+currentPanel);
        }

    // Unhide the new wrapper
    newWrapper.css({display:''});

    // If the new action panel has numbered links in the title
    var mainActionsTitle = $('.main_actions_title', newWrapper);
    var floatLinkContainer = $('.float_links', mainActionsTitle);
    if (floatLinkContainer.length){

        // Collect the parent wraper ID and generate the option class name
        var parentActionsWrapper = floatLinkContainer.closest('#actions > .wrapper');
        var actionWrapperID = parentActionsWrapper.attr('id');
        var optionClass = actionWrapperID.replace(/^actions_/, 'action_');

        // Assign events to any of the page links here
        $('.num[href]', floatLinkContainer).click(function(e){
            e.preventDefault();

            // Collect references to this link and number
            var thisLink = $(this);
            var thisNum = parseInt(thisLink.attr('href').replace(/^#/, ''));

            // If this this panel is disabled, prevent clicking but only the first link
            //if (thisNum > 1 && mainActionsTitle.hasClass('main_actions_title_disabled')){ return false; }
            //console.log('num link '+thisNum+' clicked!');

            // Remove the active class from other links and add to this one
            $('.num', floatLinkContainer).removeClass('active');
            thisLink.addClass('active');

            // Define the key of the first and last element to be shown
            var lastElementKey = thisNum * 8;
            var firstElementKey = lastElementKey - 8;
            //console.log('first key should be '+firstElementKey+' and last should be '+lastElementKey+'!');

            // Hide all item buttons in the current view and then show only relevant
            $('.'+optionClass, newWrapper).css({display:'none'});
            var activeButtons = $('.'+optionClass, newWrapper).slice(firstElementKey, lastElementKey);
            //console.log('we have selected a total of '+activeButtons.length+' elements');
            activeButtons.css({display:'block'});

            // Loop through the active buttons and update their order values
            var tempOrder = 1;
            activeButtons.each(function(){ $(this).attr('data-order', tempOrder); tempOrder++; });
            $('.action_back', newWrapper).attr('data-order', tempOrder);

            // Update the session with the last page click
            //var thisRequestType = 'session';
            //var thisRequestData = 'battle_settings,'+optionClass+'_page_num,'+thisNum;
            //$.post('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
            //(disabled for now)

            // Return true on success
            return true;

            });

        var activeLink = $('.active', floatLinkContainer);
        var firstLink = $('.num', floatLinkContainer).first();
        if (activeLink.length){ activeLink.trigger('click'); }
        else if (firstLink.length){ firstLink.trigger('click'); }

        }

    // If there are buttons in the new wrapper
    var hoverButton = $('.button_hover', newWrapper);
    var currentButtons = $('.button:not(.button_disabled)', newWrapper).not('.main_actions_title .button');
    var currentButtonCount = currentButtons.length;
    if (currentButtonCount > 0 && !hoverButton.length){
        var firstButton = currentButtons.first();
        var firstButtonOrder = firstButton.attr('data-order') != undefined ? parseInt(firstButton.attr('data-order')) : 0;
        if (firstButton.length){ firstButton.addClass('button_hover'); }
    }

}

// Define an extension of the string prototype to handle replace all
String.prototype.replaceAll = function(search, replace) {
        if (replace === undefined) { return this.toString(); }
        return this.replace(new RegExp(search, 'g'), replace);
        //return this.split(search).join(replace);
}

// Define a function for updating an action panel's markup
var actionPanelCache = [];
function mmrpg_action_panel_update(thisPanel, thisMarkup){
    // Update the requested panel with the supplied markup
    //console.log('mmrpg_action_panel_update('+thisPanel+', [thisMarkup])');
    var thisActionPanel = $('#actions_'+thisPanel, gameActions);
    thisActionPanel.empty().html(thisMarkup);
    // Search for any sprites in this panel's markup
    $('.sprite', thisActionPanel).each(function(){
        var thisBackground = $(this).css('background-image').replace(/^url\("?(.*?)"?\)$/i, '$1');
        if (thisBackground != 'none'){
            var cacheImage = document.createElement('img');
            cacheImage.src = thisBackground;
            actionPanelCache.push(cacheImage)
            }
        });
}

// Define a global variable for holding events
var mmrpgEvents = [];
// Define a function for queueing up an event
function mmrpg_event(flagsMarkup, dataMarkup, canvasMarkup, consoleMarkup){
    if (flagsMarkup.length){ flagsMarkup = $.parseJSON(flagsMarkup); }
    else { flagsMarkup = {}; }
    if (dataMarkup.length){ dataMarkup = $.parseJSON(dataMarkup); }
    else { dataMarkup = {}; }
    mmrpgEvents.push({
        'event_functions' : function(eventFlags){
            if (dataMarkup.length){
                //dataMarkup = $.parseJSON(dataMarkup);
                /*
                mmrpg_canvas_update(
                    dataMarkup.this_battle,
                    dataMarkup.this_field,
                    dataMarkup.this_player,
                    dataMarkup.this_robot,
                    dataMarkup.target_player,
                    dataMarkup.target_robot
                    );
                */
                }
            if (canvasMarkup.length){
                mmrpg_canvas_event(canvasMarkup, eventFlags); //, flagsMarkup
                }
            if (consoleMarkup.length){
                mmrpg_console_event(consoleMarkup, eventFlags);  //, flagsMarkup
                }
            },
        'event_flags' : flagsMarkup //$.parseJSON(flagsMarkup)
            });
    //console.log('mmrpgEvents.push() w/ new size', mmrpgEvents.length);
}
// Define a function for playing the events
var eventAlreadyQueued = false;
var battleResultsDisplayed = false;
function mmrpg_events(){

    if (eventAlreadyQueued){ return; }

    //console.log('mmrpg_events()');
    //clearTimeout(canvasAnimationTimeout);
    clearInterval(canvasAnimationTimeout);
    canvasAnimationCameraTimer = 0;
    updateCameraShiftTransitionTiming();
    updateCameraShiftTransitionDuration();

    var thisEvent = false;
    if (mmrpgEvents.length){
        //console.log('mmrpgEvents.length =', mmrpgEvents.length);
        // Switch to the events panel
        mmrpg_action_panel('event');
        // Collect the topmost event and execute it
        thisEvent = mmrpgEvents.shift();
        thisEvent.event_functions(thisEvent.event_flags);
        // Loop through eventhooks functions if there are any in gameSettings.eventHooks and process them with this event
        if (gameSettings.eventHooks.length){
            $.each(gameSettings.eventHooks, function(){
                var thisEventHook = this;
                if (typeof thisEventHook == 'function'){ thisEventHook(thisEvent.event_flags); }
                });
            }
        }

    if (mmrpgEvents.length < 1){
        // Assuming we're allowed to use camera stuff, reset the camera if it's not already
        if (gameSettings.eventCameraShift){
            //console.log('events are done, reset the camera');
            mmrpg_canvas_camera_shift();
        }
        // Switch to the specified "next" action
        var nextAction = $('input[name=next_action]', gameEngine).val();
        if (nextAction.length){ mmrpg_action_panel(nextAction); }
        // Add the idle class to the robot details on-screen
        //console.log('adding robot details class....1');
        //$('.robot_details', gameCanvas).css('opacity', 0.9).addClass('robot_details_idle');
        // Start animating the canvas randomly
        mmrpg_canvas_animate();
        } else if (mmrpgEvents.length >= 1){
            var autoClickTimer = false;
            if (gameSettings.eventAutoPlay && thisEvent.event_flags.autoplay != false){
                //console.log('queue next event');
                eventAlreadyQueued = true;
                clearTimeout(autoClickTimer);
                autoClickTimer = setTimeout(function(){
                    requestAnimationFrame(function(){
                        //console.log('fire next event');
                        eventAlreadyQueued = false;
                        mmrpg_events();
                        });
                    }, parseInt(gameSettings.eventTimeout));
                $('a[data-action="continue"]').addClass('button_disabled');
                } else {
                $('a[data-action="continue"]').removeClass('button_disabled');
                }
            $('a[data-action="continue"]').click(function(){
                if (autoClickTimer !== false){
                    clearTimeout(autoClickTimer);
                    autoClickTimer = false;
                    }
                });
        }

    // Collect the current battle status and result
    var battleStatus = $('input[name=this_battle_status]', gameEngine).val();
    var battleResult = $('input[name=this_battle_result]', gameEngine).val();

    // Check for specific value triggers and execute events
    if (battleStatus == 'complete'
        && battleResultsDisplayed === false){
        //console.log('checkpoint | battleStatus='+battleStatus+' battleResult='+battleResult);
        //console.log('thisEvent.event_flags =', thisEvent.event_flags);

        // Based on the battle result, play the victory or defeat music
        if (battleResult == 'victory'
            && typeof thisEvent.event_flags.victory !== 'undefined'
            && thisEvent.event_flags.victory === true){
            // Play the victory music
            //console.log('mmrpg_events() / Play the victory music');
            //top.mmrpg_play_sound_effect('battle-victory-sound');
            if (gameSettings.playVictorySound){
                let victorySound = 'battle-victory-sound';
                let victoryMusic = 'misc/leader-board';
                top.mmrpg_music_volume(0, false);
                top.mmrpg_play_sound_effect(victorySound);
                setTimeout(function(){
                    if (gameSettings.playVictoryMusic){
                        top.mmrpg_music_load(victoryMusic, true, false);
                        } else {
                        top.mmrpg_reset_music_volume();
                        }
                    }, 1000);
                } else if (gameSettings.playVictoryMusic){
                top.mmrpg_music_load(victoryMusic, true, false);
                }
            if (mmrpgEvents.length < canvasAnimationCameraDelay){ canvasAnimationCameraTimer = canvasAnimationCameraDelay - mmrpgEvents.length; }
            battleResultsDisplayed = true;
            }
        if (battleResult == 'defeat'
            && typeof thisEvent.event_flags.defeat !== 'undefined'
            && thisEvent.event_flags.defeat === true){
            // Play the failure music
            //console.log('mmrpg_events() / Play the failure music');
            if (gameSettings.playDefeatSound){
                let defeatSound = 'battle-defeat-sound';
                let defeatMusic = 'misc/leader-board';
                top.mmrpg_music_volume(0, false);
                top.mmrpg_play_sound_effect(defeatSound);
                setTimeout(function(){
                    if (gameSettings.playDefeatMusic){
                        top.mmrpg_music_load(defeatMusic, true, false);
                        } else {
                        top.mmrpg_reset_music_volume();
                        }
                    }, 1000);
                } else if (gameSettings.playDefeatMusic){
                top.mmrpg_music_load(defeatMusic, true, false);
                }
            if (mmrpgEvents.length < canvasAnimationCameraDelay){ canvasAnimationCameraTimer = canvasAnimationCameraDelay - mmrpgEvents.length; }
            battleResultsDisplayed = true;
            }

        }


}

// Define a function for creating a new layer on the canvas
function mmrpg_canvas_event(thisMarkup, eventFlags){ //, flagsMarkup
    var thisContext = $('.wrapper', gameCanvas);
    if (thisContext.length){
        //console.log('mmrpg_canvas_event(thisMarkup, eventFlags) | eventFlags =', eventFlags);
        //console.log('gameSettings.eventTimeout =', gameSettings.eventTimeout, 'gameSettings.eventTimeoutThreshold =', gameSettings.eventTimeoutThreshold);
        // Drop all the z-indexes to a single amount
        $('.event:not(.sticky)', thisContext).css({zIndex:500});
        // Calculate the top offset based on previous event height
        var eventTop = $('.event:not(.sticky):first-child', thisContext).outerHeight();
        // Prepend the event to the current stack but bring it to the front
        var thisEvent = $('<div class="event event_frame clearback">'+thisMarkup+'</div>');
        thisEvent.css({opacity:0.0,zIndex:600});
        thisContext.prepend(thisEvent);

        // Wait for all the event's assets to finish loading
        thisEvent.waitForImages(function(){

            // Find all the details in this event markup and move them to the sticky
            $(this).find('.details').addClass('hidden').css({opacity:0}).appendTo('.event_details', gameCanvas);

            // If camera shift settings are enabled, we can process them
            if (gameSettings.eventCameraShift){
                // If this event has any camera action going on, make sure we update the canvas
                var currentShift = thisContext.attr('data-camera-shift') || '';
                var currentFocus = thisContext.attr('data-camera-focus') || '';
                var currentDepth = thisContext.attr('data-camera-depth') || '';
                var currentOffset = thisContext.attr('data-camera-offset') || '';
                var newCameraShift = '';
                var newCameraFocus = '';
                var newCameraDepth = 0;
                var newCameraOffset = 0;
                // Check to see if camera shift settings were provided in the frame
                if (typeof eventFlags.camera !== 'undefined'
                    && eventFlags.camera !== false){
                    //console.log('we have camera action!', eventFlags.camera);
                    newCameraShift = eventFlags.camera.side;
                    newCameraFocus = eventFlags.camera.focus;
                    newCameraDepth = eventFlags.camera.depth;
                    newCameraOffset = eventFlags.camera.offset;
                }
                // If any of the shift values have changed, we need to update everything
                if (currentShift !== newCameraShift
                    || currentFocus !== newCameraFocus
                    || currentDepth !== newCameraDepth
                    || newCameraOffset !== newCameraOffset){
                    mmrpg_canvas_camera_shift(newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset);
                }
            }

            // If found effect settings are enabled, we can process them
            if (gameSettings.eventSoundEffects
                && typeof top.mmrpg_play_sound_effect !== 'undefined'){
                //console.log('we can react to sound effects!');
                //console.log('eventFlags =', eventFlags);
                // Check to see if camera shift settings were provided in the frame
                if (typeof eventFlags.sounds !== 'undefined'
                    && eventFlags.sounds !== false){
                    //console.log('we have sound effect(s)!', eventFlags.sounds);
                    for (var i = 0; i < eventFlags.sounds.length; i++){
                        var effectConfig = eventFlags.sounds[i];
                        var effectName = effectConfig.name;
                        //console.log('effectName =', effectName);
                        //console.log('effectConfig =', effectConfig);
                        if (typeof effectConfig.delay === 'number'){
                            setTimeout(function(){
                                top.mmrpg_play_sound_effect(effectName, effectConfig, false);
                                }, effectConfig.delay);
                            } else {
                            top.mmrpg_play_sound_effect(effectName, effectConfig, false);
                            }
                    }
                }
            }

            // If we're allowed to cross-fade transition the normal way, otherwise straight-up replace the event
            let $thisEvent = $(this);
            let $otherEvents = $('.event:not(.sticky):gt(0)', thisContext);
            //let eventCrossFade = Math.ceil(gameSettings.eventTimeout / 2);
            let eventCrossFadeOutDuration = 100;
            let eventCrossFadeInDuration = 50;
            if (mmrpg_cross_fade_enabled()){
                // Animate a fade out of the other events
                $otherEvents.css({zIndex:499}).removeClass('current');
                $otherEvents.animate({opacity:0},{
                    duration: eventCrossFadeOutDuration,
                    easing: 'linear',
                    queue: false
                    });
                // Animate a fade in, and the remove the old images
                $thisEvent.animate({opacity:1.0}, {
                    duration: eventCrossFadeInDuration,
                    easing: 'linear',
                    complete: function(){
                        $('.details:not(.hidden)', thisContext).remove();
                        $('.details', thisContext).css({opacity:1}).removeClass('hidden');
                        $otherEvents.remove();
                        $thisEvent.css({zIndex:500});
                        },
                    queue: false
                    });
                }
            else {
                    // Make sure the new event is visible then remove the old ones
                    $thisEvent.css({opacity:1.0,zIndex:500});
                    $otherEvents.css({opacity:0,zIndex:499});
                    $('.details:not(.hidden)', thisContext).remove();
                    $('.details', thisContext).css({opacity:1}).removeClass('hidden');
                    $otherEvents.remove();
            }

            // Loop through all field layers on the canvas and trigger animations
            $('.background[data-animate],.foreground[data-animate]', gameCanvas).each(function(){
                // Trigger an animation frame change for this field
                var thisField = $(this);
                mmrpg_canvas_field_frame(thisField, '');
                });

            // Loop through all field layers on the canvas and trigger animations
            $('.sprite[data-type=attachment][data-animate]', gameCanvas).each(function(){
                // Trigger an animation frame change for this field
                var thisAttachment = $(this);
                var thisPosition = thisAttachment.attr('data-position');
                if (thisPosition == 'background' || thisPosition == 'foreground'){
                    //console.log('mmrpg_canvas_attachment_frame('+thisAttachment.attr('data-id')+')');
                    mmrpg_canvas_attachment_frame(thisAttachment, '');
                    }
                });

            });
        }
}


// Define a function for updating the graphics on the canvas
function mmrpg_canvas_update(thisBattle, thisPlayer, thisRobot, targetPlayer, targetRobot){
    // Preload all this robot's sprite image files if not already
    if (thisPlayer.player_side && thisRobot.robot_token){
        var thisRobotToken = thisRobot.robot_token;
        var thisRobotDirection = thisPlayer.player_side == 'right' ? 'left' : 'right';
        mmrpg_preload_robot_sprites(thisRobotToken, thisRobotDirection);
        }
    // Preload all the target robot's sprite image files if not already
    if (targetPlayer.player_side && targetRobot.robot_token){
        var targetRobotToken = targetRobot.robot_token;
        var targetRobotDirection = targetPlayer.player_side == 'right' ? 'left' : 'right';
        mmrpg_preload_robot_sprites(targetRobotToken, targetRobotDirection);
        }
}


// Define a change event for whenever this game setting is altered
gameSettings.currentCameraShift = {shift:'',focus:'',depth:'',offset:''};
function mmrpg_canvas_camera_shift(newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset){
    //console.log('mmrpg_canvas_camera_shift() w/ ', newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset);

    if (typeof newCameraShift === 'undefined' || !newCameraShift){ newCameraShift = ''; }
    if (typeof newCameraFocus === 'undefined' || !newCameraFocus){ newCameraFocus = ''; }
    if (typeof newCameraDepth === 'undefined' || !newCameraDepth){ newCameraDepth = 0; }
    if (typeof newCameraOffset === 'undefined' || !newCameraOffset){ newCameraOffset = 0; }
    //console.log('mmrpg_canvas_camera_shift() w/ ', newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset);

    // Collect the canvas context and immediately return false if not exists
    var thisContext = $('.wrapper', gameCanvas);
    if (!thisContext.length){ return false; }

    // Collect current shift values for reference and updating
    var currentCameraShift = gameSettings.currentCameraShift;
    //console.log('currentCameraShift:', currentCameraShift);

    // If the values haven't changed at all, we should just return
    if (currentCameraShift.shift === newCameraShift
        && currentCameraShift.focus === newCameraFocus
        && currentCameraShift.depth === newCameraDepth
        && currentCameraShift.offset === newCameraOffset){
        return;
    }

    // Update the data attributes on the canvas wrapper
    currentCameraShift.shift = newCameraShift;
    currentCameraShift.focus = newCameraFocus;
    currentCameraShift.depth = newCameraDepth;
    currentCameraShift.offset = newCameraOffset;
    thisContext.attr('data-camera-shift', newCameraShift);
    thisContext.attr('data-camera-focus', newCameraFocus);
    thisContext.attr('data-camera-depth', newCameraDepth);
    thisContext.attr('data-camera-offset', newCameraOffset);
    var offsetCameraDepth = newCameraDepth + newCameraOffset;
    if (offsetCameraDepth < -8){ offsetCameraDepth - -8; }
    else if (offsetCameraDepth > 8){ offsetCameraDepth = 8; }

    // This first value is used for camera shifts on the bench
    if (offsetCameraDepth !== 0){
        var diffValue = ((Math.abs(offsetCameraDepth) - 1) * 0.1);
        var depthModValue = 1 - diffValue;
        if (offsetCameraDepth < 0){ depthModValue = depthModValue * -1; }
        updateCameraShiftVariable('depth-mod', depthModValue);
    } else {
        updateCameraShiftVariable('depth-mod', 1);
    }

    // This second value is used for camera shifts in the foreground
    if (offsetCameraDepth !== 0){
        var diffValue = ((Math.abs(offsetCameraDepth) - 1) * 0.1);
        var depthMod2Value = 1.8 - diffValue;
        if (offsetCameraDepth < 0){ depthMod2Value = depthMod2Value * -1; }
        updateCameraShiftVariable('depth-mod2', depthMod2Value);
    } else {
        updateCameraShiftVariable('depth-mod2', 1.8);
    }

}

// Define a function for easily updating camera-related CSS variables on the canvas
function updateCameraShiftVariable(varName, varValue){
    //console.log('updateCameraShiftVariable((varName:', varName, ', varValue:', varValue, ')');
    var cssVarName = '--camera-shift-'+varName;
    var cssVarValue = varValue;
    //console.log('setting '+cssVarName+' to:', cssVarValue);
    document.documentElement.style.setProperty(cssVarName, cssVarValue);
}

// Define a quick function for updating the camera shift transition timing variable
function updateCameraShiftTransitionTiming(newValue){
    //console.log('updateCameraShiftTransitionTiming(', newValue, ')');
    if (typeof newValue !== 'string' || !newValue){ newValue = 'ease'; }
    var transitionTimingValue = newValue;
    updateCameraShiftVariable('transition-timing', transitionTimingValue);
};

// Define a quick function for updating the camera shift transition duration variable
function updateCameraShiftTransitionDuration(newValue){
    //console.log('updateCameraShiftTransitionDuration(', typeof newValue, newValue, ')');
    if (typeof newValue !== 'number'){ newValue = 0.5; }
    var transitionDurationValue = (function(modValue){
        //console.log('transitionDurationValue(', modValue, ')');
        if (typeof modValue !== 'number'){ modValue = 1; }
        var duration = Math.ceil(gameSettings.eventTimeout * modValue);
        //if (!gameSettings.eventCrossFade){ duration = 0; }
        //else if (!gameSettings.eventCameraShift){ duration = 0; }
        //else if (gameSettings.eventTimeout <= gameSettings.eventTimeoutThreshold){ duration = 0; }
        if (!gameSettings.eventCameraShift){ duration = 0; }
        if (gameSettings.eventTimeout <= gameSettings.eventTimeoutThreshold){ duration = gameSettings.eventTimeoutThreshold; }
        var cssValue = duration > 0 ? (duration / 1000)+'s' : 'none';
        //console.log('duration:', duration, 'cssValue:', cssValue);
        return cssValue;
        })(newValue);
    updateCameraShiftVariable('transition-duration', transitionDurationValue);
};

// Define a function for appending a event to the console window
function mmrpg_console_event(thisMarkup, eventFlags){ //, flagsMarkup
    var thisContext = $('.wrapper', gameConsole);
    if (thisContext.length){
        //console.log('mmrpg_console_event(thisMarkup, eventFlags) | eventFlags =', eventFlags);
        // Append the event to the current stack
        //thisContext.prepend('<div class="event" style="top: -100px;">'+thisMarkup+'</div>');
        thisContext.prepend(thisMarkup);
        gameConsole.find('.wrapper').scrollTop(0);
        $('.event:first-child', thisContext).css({top:-100});
        if (mmrpg_cross_fade_enabled()){
            // We're at a normal speed, so we can animate normally
            $('.event:first-child', thisContext).animate({top:0}, 400, 'swing');
            } else {
            // We're at a super-fast speed, so we should NOT cross-fade
            $('.event:first-child', thisContext).css({top:0});
            }
        // Hide any leftover boxes from previous events over the limit
        $('.event:gt(50)', thisContext).appendTo('#event_console_backup');
        // Remove any leftover boxes from previous events
        //$('.event:gt(10)', thisContext).remove();
        }
}



// Define a function for toggling the canvas animation
gameSettings.screenshotMode = false;
function mmrpg_toggle_screenshot_mode(screenshotMode, element){
    //console.log('mmrpg_toggle_screenshot_mode(screenshotMode:', typeof screenshotMode, screenshotMode, ')');
    if (typeof screenshotMode === 'undefined'
        || screenshotMode === 'toggle'){
        screenshotMode = !gameSettings.screenshotMode ? true : false;
        }
    gameSettings.screenshotMode = screenshotMode;
    //console.log('screenshotMode:', screenshotMode);
    //console.log('gameSettings.screenshotMode:', gameSettings.screenshotMode);
    if (gameCanvas.length){
        if (gameSettings.screenshotMode){ gameCanvas.addClass('screenshot-mode'); }
        else { gameCanvas.removeClass('screenshot-mode'); }
        }
    if (gameWindow.length){
        if (gameSettings.screenshotMode){ gameWindow.addClass('screenshot-mode'); }
        else { gameWindow.removeClass('screenshot-mode'); }
        }
    if (window.self !== window.top
        && typeof window.top.mmrpg_toggle_screenshot_mode !== 'undefined'){
        window.top.mmrpg_toggle_screenshot_mode(screenshotMode);
    }
    if (typeof element !== 'undefined'
        && element !== false){
        // Collect the object references to the button and internal label
        var thisButton = $(element);
        var thisLabel = $('.multi', thisButton);
        // Pull the current value and use it to calculate new ones
        //console.log('thisButton.attr("data-setting-value"):', thisButton.attr('data-setting-value'));
        var newValue = gameSettings.screenshotMode ? 1 : 0;
        var newValueText = !gameSettings.screenshotMode ? 'ON' : 'OFF';
        var newValueClass = 'value type type_' + (!gameSettings.screenshotMode ? 'nature' : 'flame');
        // Update the button value and label text/colour
        thisButton.attr('data-setting-value', newValue);
        thisLabel.find('.value').html(newValueText).removeClass().addClass(newValueClass);
    }
}

// Define a function for toggling the canvas animation
function mmrpg_toggle_animation(){
    if (gameSettings.idleAnimation != false){ return mmrpg_stop_animation(); }
    else { return mmrpg_start_animation(); }
}

// Define a function for starting the canvas animation
function mmrpg_start_animation(){
    var animateToggle = $('a.toggle', gameAnimate);
    animateToggle.removeClass('paused').addClass('playing');
    animateToggle.html('<i class="fas fa-play"></i>');
    gameSettings.idleAnimation = true;
    gameSettings.eventAutoPlay = true;
    mmrpg_canvas_animate();
    if (mmrpgEvents.length){ mmrpg_events(); }
    return gameSettings.idleAnimation;
}

// Define a function for stopping the canvas animation
function mmrpg_stop_animation(){
    var animateToggle = $('a.toggle', gameAnimate);
    animateToggle.removeClass('playing').addClass('paused');
    animateToggle.html('<i class="fas fa-pause"></i>');
    gameSettings.idleAnimation = false;
    gameSettings.eventAutoPlay = false;
    mmrpg_canvas_animate();
    return gameSettings.idleAnimation;
}


// -- AUDIO FUNCTIONS -- //

// If our dependency, the Howler.js library, is not loaded, then we'll define a dummy function to prevent errors
if (typeof window.Howl === 'undefined'){
    var no = function(){ return false; };
    Howl = function(){
        return {
            error: 'window.Howl not loaded',
            play: no,
            playing: no,
            stop: no,
            pause: no,
            volume: no,
            state: no,
            once: no,
            fade: no,
            }
        };
    let scripts = [], sources = [], loaded = 0;
    sources.push('.libs/howler-js/howler.core.min.js', '.libs/howler-js/howler.min.js');
    let onLoadComplete = function(){ };
    for (var i = 0; i < sources.length; i++){
        let source = sources[i], script = document.createElement('script');
        script.onload = function(){ loaded++; if (loaded >= sources.length){ onLoadComplete(); } };;
        script.src = source; document.head.appendChild(script);
        }
}

// Define required music objects to handle audio playback and set up some defaults
var mmrpgMusicSound = false;
var mmrpgMusicConfig = {};
var mmrpgFanfareSound = false;
var mmrpgMusicEndedDefault = function(){ /* ... */ };
var mmrpgFanfareEndedDefault = function(){ /* ... */ };
var mmrpgMusicEnded = mmrpgMusicEndedDefault;
var mmrpgFanfareEnded = mmrpgFanfareEndedDefault;
var mmrpgMusicInit = false;

// Define a function for adjusting the master volume of basically everything
function mmrpg_master_volume(newMasterVolume, saveToSettings, updateMusic, updateSoundEffects){
    if (!mmrpgMusicSound){ return false; }
    if (!gameSettings.soundEffectPool){ return false; }
    //console.log('%cmmrpg_master_volume', 'color: green;', '(newMasterVolume:', newMasterVolume, ', saveToSettings:', saveToSettings, ', updateMusic:', updateMusic, ', updateSoundEffects:', updateSoundEffects, ')');
    //console.log('mmrpg_master_volume // gameSettings.masterVolume =', gameSettings.masterVolume);
    //console.log('mmrpg_master_volume // gameSettings.musicVolume =', gameSettings.musicVolume);
    //console.log('mmrpg_master_volume // gameSettings.effectVolume =', gameSettings.effectVolume);
    if (typeof saveToSettings !== 'boolean'){ saveToSettings = true; }
    if (typeof updateMusic !== 'boolean'){ updateMusic = true; }
    if (typeof updateSoundEffects !== 'boolean'){ updateSoundEffects = true; }
    if (newMasterVolume < 0){ newMasterVolume = 0; }
    if (newMasterVolume > 1){ newMasterVolume = 1; }
    var currentMasterVolume = gameSettings.masterVolume;
    //console.log('mmrpg_master_volume // adjusted currentMasterVolume =', currentMasterVolume);
    //console.log('mmrpg_master_volume // adjusted newMasterVolume =', newMasterVolume);
    gameSettings.masterVolume = newMasterVolume;
    if (updateMusic){ mmrpg_music_volume(gameSettings.musicVolume, saveToSettings, 0); }
    if (updateSoundEffects){ mmrpg_sound_effect_volume(gameSettings.effectVolume, saveToSettings); }
    if (!saveToSettings){ gameSettings.masterVolume = currentMasterVolume; }
}
// Define a function for adjusting the currently playing music's volume
function mmrpg_music_volume(newVolume, saveToSettings, fadeDuration){
    if (!mmrpgMusicSound){ return false; }
    //console.log('%cmmrpg_music_volume', 'color: green;', '(newVolume:', newVolume, ', saveToSettings:', saveToSettings, ', fadeDuration:', fadeDuration, ')');
    //console.log('mmrpg_music_volume // gameSettings.masterVolume =', gameSettings.masterVolume);
    //console.log('mmrpg_music_volume // gameSettings.musicVolume =', gameSettings.musicVolume);
    //console.log('mmrpg_music_volume // gameSettings.effectVolume =', gameSettings.effectVolume);
    if (typeof saveToSettings !== 'boolean'){ saveToSettings = true; }
    if (typeof fadeDuration !== 'number'){ fadeDuration = 500; }
    if (newVolume < 0){ newVolume = 0; }
    if (newVolume > 1){ newVolume = 1; }
    if (saveToSettings){ gameSettings.musicVolume = newVolume; }
    //console.log('mmrpg_music_volume // adjusted newVolume =', newVolume);
    var currentMusicVolume = gameSettings.musicVolume * gameSettings.masterVolume;
    var relativeMusicVolume = newVolume * gameSettings.masterVolume;
    //console.log('mmrpg_music_volume // currentMusicVolume =', currentMusicVolume);
    //console.log('mmrpg_music_volume // relativeMusicVolume =', relativeMusicVolume);
    if (fadeDuration > 0){ mmrpgMusicSound.fade(currentMusicVolume, relativeMusicVolume, fadeDuration);  }
    else { mmrpgMusicSound.volume(relativeMusicVolume); }
}
// Define a function for resetting the currently playing music's volume
function mmrpg_reset_music_volume(fadeDuration){
    if (!mmrpgMusicSound){ return false; }
    //console.log('mmrpg_reset_music_volume(fadeDuration:', fadeDuration, ')');
    //console.log('mmrpg_reset_music_volume // gameSettings.masterVolume =', gameSettings.masterVolume);
    //console.log('mmrpg_reset_music_volume // gameSettings.musicVolume =', gameSettings.musicVolume);
    //console.log('mmrpg_reset_music_volume // gameSettings.effectVolume =', gameSettings.effectVolume);
    if (typeof fadeDuration !== 'number'){ fadeDuration = 500; }
    var resetToVolume = gameSettings.musicVolume;
    if (resetToVolume < 0){ resetToVolume = 0; }
    if (resetToVolume > 1){ resetToVolume = 1; }
    //console.log('mmrpg_reset_music_volume // calculated resetToVolume =', resetToVolume);
    var currentMusicVolume = mmrpgMusicSound.volume();
    var relativeMusicVolume = resetToVolume * gameSettings.masterVolume;
    //console.log('mmrpg_reset_music_volume // currentMusicVolume =', currentMusicVolume);
    //console.log('mmrpg_reset_music_volume // relativeMusicVolume =', relativeMusicVolume);
    if (fadeDuration > 0){ mmrpgMusicSound.fade(currentMusicVolume, relativeMusicVolume, fadeDuration);  }
    else { mmrpgMusicSound.volume(relativeMusicVolume); }
}
// Define a function for adjusting the volume if in-game sound effects
function mmrpg_sound_effect_volume(newVolume, saveToSettings){
    if (!gameSettings.soundEffectPool){ return false; }
    //console.log('%cmmrpg_sound_effect_volume', 'color: green', '(newVolume:', newVolume, 'saveToSettings:', saveToSettings, ')');
    if (typeof saveToSettings !== 'boolean'){ saveToSettings = true; }
    if (newVolume < 0){ newVolume = 0; }
    if (newVolume > 1){ newVolume = 1; }
    if (saveToSettings){ gameSettings.effectVolume = newVolume; }
    //console.log('mmrpg_sound_effect_volume // adjusted newVolume =', newVolume);
    var relativeEffectVolume = newVolume * gameSettings.masterVolume;
    var currentEffectVolume = gameSettings.effectVolume * gameSettings.masterVolume;
    //console.log('mmrpg_sound_effect_volume // relativeEffectVolume =', relativeEffectVolume);
    if (gameSettings.soundEffectPool.length){
        var soundIDs = Object.keys(gameSettings.soundEffectPool);
        //console.log('gameSettings.soundEffectPool =', gameSettings.soundEffectPool);
        //console.log('soundIDs =', soundIDs);
        for (var i = 0; i < soundIDs.length; i++){
            var soundID = soundIDs[i];
            var soundObj = gameSettings.soundEffectPool[soundID];
            //console.log('soundID =', soundID, '| soundObj =', soundObj);
            soundObj.sound.volume(relativeEffectVolume);
        }
    }
}

// Define a function for toggling the music player
function mmrpg_music_toggle(){
    //console.log('mmrpg_music_toggle()');
    var musicToggle = $('a.toggle', gameMusic);
    if (!mmrpgMusicSound.playing()){
        gameSettings.musicVolumeEnabled = true;
        gameSettings.effectVolumeEnabled = true;
        mmrpg_reset_music_volume();
        mmrpg_music_play();
        musicToggle.html('<i class="fas fa-volume"></i>');
        musicToggle.removeClass('paused').addClass('playing');
    } else {
        gameSettings.musicVolumeEnabled = false;
        gameSettings.effectVolumeEnabled = false;
        mmrpg_music_volume(0, false);
        mmrpgMusicSound.pause();
        musicToggle.html('<i class="fas fa-volume-mute"></i>');
        musicToggle.removeClass('playing').addClass('paused');
    }
    if (!mmrpgMusicInit){
        mmrpgMusicSound.on('end', mmrpgMusicEnded);
        mmrpgMusicInit = true;
    }
}

// Define a function for playing the current music
function mmrpg_music_play(){
    var musicToggle = $('a.toggle', gameMusic);
    var musicStream = $('.audio-stream.music', gameMusic);
    var musicStreamSource = $('source', musicStream).attr('src');
    // Define local function for playing sprite music
    var playSpriteMusic = function(){
        if (typeof mmrpgMusicConfig.sprite !== 'undefined'
            && typeof mmrpgMusicConfig.sprite.intro !== 'undefined'
            && typeof mmrpgMusicConfig.sprite.loop !== 'undefined'){
            //console.log('playing music with a loop');
            mmrpgMusicSound.once('end', function(){
                //console.log('music intro complete, playing loop now');
                mmrpgMusicSound.stop();
                mmrpgMusicSound.play('loop');
                });
            //console.log('music intro starting now');
            mmrpgMusicSound.play('intro');
            }
        else {
            //console.log('playing music without any loop');
            mmrpgMusicSound.once('end', function(){ /* ... */ });
            mmrpgMusicSound.play();
            }
        };
    if (!mmrpgMusicSound.playing()){
        mmrpg_reset_music_volume();
        if (typeof mmrpgMusicConfig.sprite !== 'undefined'
            && typeof mmrpgMusicConfig.sprite.intro !== 'undefined'
            && typeof mmrpgMusicConfig.sprite.loop !== 'undefined'){
            if (mmrpgMusicSound.state() === 'loaded'){
                playSpriteMusic();
                } else {
                mmrpgMusicSound.once('load', playSpriteMusic);
                }
            }
        else {
            if (mmrpgMusicSound.state() === 'loaded'){
                //mmrpgMusicSound.play();
                playSpriteMusic();
                } else {
                mmrpgMusicSound.once('load', function(){
                    //mmrpgMusicSound.play();
                    playSpriteMusic();
                    });
                }
            }
        musicToggle.html('<i class="fas fa-volume"></i>');
        musicToggle.removeClass('paused').addClass('playing');
        }
}

// Define a function for stopping the current music
function mmrpg_music_stop(){
    //console.log('mmrpg_music_stop()');
    //console.log('gameSettings.indexLoaded =', gameSettings.indexLoaded);
    //console.log('mmrpgMusicSound =', typeof mmrpgMusicSound, mmrpgMusicSound);
    var musicToggle = $('a.toggle', gameMusic);
    if (mmrpgMusicSound && mmrpgMusicSound.playing()){
        //console.log('updating the sound and toggle');
        mmrpg_music_volume(0, false);
        mmrpgMusicSound.stop();
        musicToggle.find('span').html('PLAY');
        musicToggle.removeClass('playing').addClass('paused');
    }
}
// Define a function for stopping the current music
function mmrpg_music_onend(onendFunction){
    var musicToggle = $('a.toggle', gameMusic);
    var musicStream = $('.audio-stream.music', gameMusic);
    if (mmrpgMusicSound && mmrpgMusicSound.playing()){
        return onendFunction(musicToggle, musicStream);
    }
}
// Define a function for playing the current music
function mmrpg_music_load(newTrack, resartTrack, playOnce, onendFunction){
    //console.log('%cmmrpg_music_load', 'color: magenta;', '(newTrack:', newTrack, ', resartTrack:', resartTrack, ', playOnce:', playOnce, ', onendFunction:', typeof onendFunction, ')');
    var musicStream = $('.audio-stream.music', gameMusic);
    var musicToggle = $('a.toggle', gameMusic);
    var thisTrack = musicStream.attr('data-track');
    var isPaused = !mmrpgMusicSound || !mmrpgMusicSound.playing();
    var isRestart = typeof resartTrack === 'boolean' ? resartTrack : true;
    var isPlayOnce = typeof playOnce === 'boolean' ? playOnce : false;
    var onplayFunction = function(){ musicToggle.removeClass('paused').addClass('playing'); };
    var onendFunction = typeof onendFunction === 'function' ? onendFunction : mmrpgMusicEndedDefault;
    if (newTrack == 'last-track'){
        var lastTrack = musicStream.attr('data-last-track');
        if (lastTrack && lastTrack.length){ newTrack = lastTrack; }
        }
    else if (newTrack == 'current-track'){
        let currentTrack = musicStream.attr('data-track');
        if (currentTrack && currentTrack.length){ newTrack = currentTrack; }
        }
    if (isRestart == false && newTrack == thisTrack){
        return false;
        }
    var waitTime = mmrpgMusicSound && mmrpgMusicSound.playing() ? 500 : 0;
    var musicMeta = typeof gameSettings.customIndex.musicIndex[newTrack] === 'object' ? gameSettings.customIndex.musicIndex[newTrack] : false;
    var musicBaseVolume = gameSettings.musicVolume * gameSettings.masterVolume;
    //console.log('music object created with gameSettings.musicVolume:', gameSettings.musicVolume, ' * gameSettings.masterVolume:', gameSettings.masterVolume, ' = musicBaseVolume:', musicBaseVolume);
    if (!gameSettings.musicVolumeEnabled){ musicBaseVolume = 0; }
    var audioConfig = {
        src: [gameSettings.audioBaseHref+'sounds/'+newTrack+'/audio.mp3?'+gameSettings.cacheTime,
              gameSettings.audioBaseHref+'sounds/'+newTrack+'/audio.ogg?'+gameSettings.cacheTime],
        autoplay: !isPaused,
        volume: musicBaseVolume,
        loop: isPlayOnce ? false : true,
        onplay: onplayFunction,
        onend: onendFunction,
        html5: false,
        };
    //console.log('musicMeta =', musicMeta);
    if (musicMeta !== false
        && typeof musicMeta.loop !== 'undefined'
        && typeof musicMeta.loop.start === 'number'
        && typeof musicMeta.loop.end === 'number'){
        //console.log('musicMeta.loop is defined');
        var milliFrame = Math.ceil(1000 / 60);
        var introStart = 0;
        var introDuration = musicMeta.loop.start - (milliFrame * 10);
        var loopStart = musicMeta.loop.start + (milliFrame * 2);
        var loopDuration = musicMeta.loop.end - musicMeta.loop.start;
        audioConfig.loop = false;
        audioConfig.sprite = {
            intro: [introStart, introDuration, false],
            loop: [loopStart, loopDuration, true]
            };
        }
    if (gameSettings.musicTrackSpeed){
        //console.log('gameSettings.musicTrackSpeed =', gameSettings.musicTrackSpeed);
        audioConfig.rate = gameSettings.musicTrackSpeed;
        }
    if (waitTime > 0){ audioConfig.autoplay = false; }
    //console.log('audioConfig =', audioConfig);
    mmrpg_music_volume(0, false);
    mmrpg_music_stop();
    musicStream.attr('data-track', newTrack);
    musicStream.attr('data-last-track', thisTrack);
    // Create a new Howl object and load the new track
    mmrpgMusicSound = new Howl(audioConfig);
    mmrpgMusicConfig = audioConfig;
    if (waitTime > 0){
        var loadTimeout = setTimeout(function(){
            mmrpg_music_play();
            }, waitTime);
        }
}

// Define a function for adjusting the speed of the currently playing music track
function mmrpg_music_speed(newSpeed, fadeMusic){
    //console.log('mmrpg_music_speed(newSpeed:', newSpeed, ', fadeMusic:', fadeMusic, ')');
    if (typeof newSpeed !== 'number' || newSpeed < 0.1){ newSpeed = 1; }
    if (typeof fadeMusic !== 'boolean'){ fadeMusic = true; }
    gameSettings.musicTrackSpeed = newSpeed;
    if (!mmrpgMusicSound || !mmrpgMusicSound.playing()){ return false; }
    //console.log('newSpeed =', newSpeed);
    if (fadeMusic){ mmrpg_music_volume(0, false, 300); }
    mmrpgMusicSound.rate(gameSettings.musicTrackSpeed);
    if (fadeMusic){ mmrpg_reset_music_volume(); }
}

// Define a function for preloading music files
var musicCache = [];
var cacheList = [];
function mmrpg_music_preload(newTrack){
    // Ensure the new track is not alrady in the list
    if (cacheList.indexOf(newTrack) === -1){
        // Define the two audio objects based on the track
        var newAudioMP3 = '<audio src="'+gameSettings.audioBaseHref+'sounds/'+newTrack+'/audio.mp3?'+gameSettings.cacheTime+'" preload></audio>';
        var newAudioOGG = '<audio src="'+gameSettings.audioBaseHref+'sounds/'+newTrack+'/audio.ogg?'+gameSettings.cacheTime+'" preload></audio>';
        cacheList.push(newTrack);
        if (isIE || isOpera || isSafari){ musicCache.push($(newAudioMP3));  }
        else if (isChrome || isFirefox){ musicCache.push($(newAudioOGG)); }
        else { musicCache.push($(newAudioMP3)); }
        return true;
        } else {
        // Does not need to be preloaded
        return false;
        }
}

// Define a function for updating the current context of the music player
function mmrpg_music_context(newContext){
    //console.log('mmrpg_music_context(newContext:', newContext, ')');
    if (!gameMusic || !gameMusic.length){ return false; }
    if (typeof newContext !== 'string'){ newContext = ''; }
    gameMusic.attr('data-context', newContext);
}

// Define variables related to sound effects for game runtime
gameSettings.soundEffectSources = [];
gameSettings.soundEffectSprites = {};
gameSettings.soundEffectPool = [];
gameSettings.soundEffectPoolKey = 0; // -1;
gameSettings.soundEffectPoolLimit = 10;
// Define a list of sound effect aliases we can use in the code to abstract a bit
gameSettings.customIndex.soundsIndex = {};
gameSettings.customIndex.soundsAliasesIndex = {};
// Define a function to play sound effects during game runtime
async function mmrpg_play_sound_effect(effectName, effectConfig, isMenuSound){
    if (typeof effectConfig !== 'object'){ effectConfig = {}; }
    if (typeof isMenuSound !== 'boolean'){ isMenuSound = true; }
    //console.log('%cmmrpg_play_sound_effect', 'color: cyan;', '(effectName:', effectName, 'effectConfig:', typeof effectConfig, effectConfig, 'isMenuSound:', isMenuSound, ')');
    //console.log('gameSettings.soundEffectPool =', Object.keys(gameSettings.soundEffectPool).length, gameSettings.soundEffectPool);
    //console.log('gameSettings.soundEffectSources =', gameSettings.soundEffectSources.length, gameSettings.soundEffectSources);
    //console.log('gameSettings.soundEffectSprites =', Object.keys(gameSettings.soundEffectSprites).length, gameSettings.soundEffectSprites);

    // If the game hasn't loaded we shoudln't be playing anything
    //let somethingHasLoaded = gameSettings.indexLoaded  || gameSettings.worldLoaded || gameSettings.battleLoaded ? true : false;
    //if (!somethingHasLoaded){ console.warn('aaa'); return false; }
    if (!gameSettings.gameHasLoaded){ console.warn('aaa', effectName, gameSettings); return false; }
    if (gameSettings.enableSoundEffects === false){ console.warn('bbb', effectName, gameSettings); return false; }
    if (gameSettings.indexLoaded){
        if (typeof gameSettings.musicHasStarted === 'undefined'){ gameSettings.musicHasStarted = false; }
        if (!gameSettings.musicHasStarted && mmrpgMusicSound.playing()){ gameSettings.musicHasStarted = true; }
        if (!gameSettings.musicVolumeEnabled){ console.warn('ccc', effectName, gameSettings); return false; }
        if (mmrpgMusicSound === false){ console.warn('ddd(1)', effectName, gameSettings); return false; }
        else if (gameSettings.gameHasStarted && !gameSettings.musicHasStarted){ console.error('ddd(2)', effectName, gameSettings); return false; }
        }

    // If we don't have sound effect sounces or sprites loaded, we can't do anything
    if (!gameSettings.soundEffectSources.length){ console.warn('eee', effectName, gameSettings); return false; }
    if (gameSettings.soundEffectSprites === {}){ console.warn('fff', effectName, gameSettings); return false; }

    // Otherwise, define a base volume for these sound effects to use
    var baseEffectVolume = gameSettings.effectVolume * gameSettings.masterVolume;

    // Replace the effect name if we're using an alias at the moment
    // TODO:  Make sure this effectName actually exists in the index of sound effect sprites
    if (typeof gameSettings.customIndex.soundsAliasesIndex !== 'undefined'
        && typeof gameSettings.customIndex.soundsAliasesIndex[effectName] !== 'undefined'){
        // Pull the actual effect name from the index based on the alias provided
        effectName = gameSettings.customIndex.soundsAliasesIndex[effectName];
        //console.log('alias triggered // new effectName =', effectName);
        } else if (typeof gameSettings.customIndex.soundsIndex !== 'undefined'
        && typeof gameSettings.customIndex.soundsIndex.sprite[effectName] !== 'undefined'){
        //console.log('using RAW name // effectName =', effectName);
        // We should be using aliases but the effect name is technically fine as-is
        } else {
        // Immediately return as this isn't real and might cause audio bugs
        //console.log('not using UNKNOWN name // effectName =', effectName);
        return false;
        }

    // Collect this effect's volume, rate factor, and loop boolean for use
    var effectVolume = baseEffectVolume;
    var effectRate = 1.0;
    var effectLoop = false;
    var effectDelay = 0;
    if (isMenuSound === true){ effectVolume *= gameSettings.menuEffectVolume; }
    if (typeof effectConfig.volume === 'number'){ effectVolume *= effectConfig.volume; }
    if (typeof effectConfig.rate === 'number'){ effectRate = effectConfig.rate; }
    if (typeof effectConfig.loop === 'boolean'){ effectLoop = effectConfig.loop; }
    if (typeof effectConfig.delay === 'number'){ effectDelay = effectConfig.delay; }
    if (!gameSettings.effectVolumeEnabled){ effectVolume = 0; }
    if (effectVolume < 0){ effectVolume = 0; }
    if (effectVolume > 1){ effectVolume = 1; }
    effectVolume = (Math.round(effectVolume * 1000) / 1000);
    //console.log('mmrpg_play_sound_effect // effectName:', effectName, 'effectVolume:', effectVolume, 'effectRate:', effectRate, 'effectLoop:', effectLoop);

    // Get the next sound object from the pool
    let sound;
    //gameSettings.soundEffectPoolKey++;
    //if (gameSettings.soundEffectPoolKey >= gameSettings.soundEffectPoolLimit){ gameSettings.soundEffectPoolKey = 0; }
    let soundEffectPoolKey = gameSettings.soundEffectPoolKey;
    if (typeof gameSettings.soundEffectPool[soundEffectPoolKey] === 'undefined'
        || typeof gameSettings.soundEffectPool[soundEffectPoolKey].sound === 'undefined'){

        // We must create a new sound object before we can use it
        sound = new Howl({
            src: gameSettings.soundEffectSources,
            sprite: gameSettings.soundEffectSprites,
            pool: gameSettings.soundEffectPoolLimit,
            autoplay: false,
            volume: 1.0,
            rate: 1.0,
            loop: false,
            html5: false,
            //html5PoolSize: 3,
            });
        gameSettings.soundEffectPool[soundEffectPoolKey] = {
            key: soundEffectPoolKey,
            name: effectName,
            sound: sound,
            time: Date.now()
            };

        }

    // We can pull an existing sound object to use from the pool
    let effect = gameSettings.soundEffectPool[soundEffectPoolKey];
    effect.time = Date.now();
    sound = effect.sound;
    //console.log('sound =', sound);
    //console.log('sound._volume', sound._volume);
    //console.log('sound.volume() =', sound.volume());
    //console.log('sound._sprite['+effectName+'] =', (sound._sprite &&  sound._sprite[effectName] ? sound._sprite[effectName] : undefined));

    // Stop any currently playing sound (?)
    //sound.stop();

    // Play the sound when ready using a function that checks load status
    let playSoundWhenReady = function(effectName, effectVolume, effectRate){
        let playSound = function(sound){
            sound.stop();
            //console.log('set rate to ', effectRate);
            sound.rate(effectRate, effect.id);
            sound.volume(effectVolume);
            sound.play(effectName);
            };
        if (sound.state() !== 'loaded'){
            /* sound.once('play', function(){
                //console.log('sound on play w/ effectVolume:', effectVolume, '&& effectRate:', effectRate);
                this.rate(effectRate);
                this.volume(effectVolume);
                this.loop(effectLoop);
                }); */
            sound.once('load', function(){
                //console.log('sound on loaded w/ effectVolume:', effectVolume, '&& effectRate:', effectRate);
                playSound(sound);
                //this.stop();
                //this.rate(effectRate);
                //this.volume(effectVolume);
                //this.play(effectName);
                });
            } else {
            //console.log('sound immediate invoke w/ effectVolume:', effectVolume, '&& effectRate:', effectRate);
            playSound(sound);
            //sound.stop();
            //sound.rate(effectRate);
            //sound.volume(effectVolume);
            //sound.play(effectName);
            }
        return true;
        };
    if (effectDelay){ setTimeout(function(){ playSoundWhenReady(effectName, effectVolume, effectRate); }, effectDelay); }
    else { playSoundWhenReady(effectName, effectVolume, effectRate); }


    /*
    // Now that the sound is actually playing we can do cleanup
    // If the sound effect pool is full, we need to remove the oldest sound
    var effectPoolSizeCurrent = Object.keys(gameSettings.soundEffectPool).length;
    if (effectPoolSizeCurrent > gameSettings.soundEffectPoolLimit){
        //console.log('soundEffectPool is full (', effectPoolSizeCurrent, ' / ', gameSettings.soundEffectPoolLimit, '), removing oldest sound');
        var oldestSound = false;
        var oldestSoundTime = false;
        for (var soundName in gameSettings.soundEffectPool){
            sound = gameSettings.soundEffectPool[soundName];
            if (oldestSoundTime === false || sound.time < oldestSoundTime){
                oldestSound = sound;
                oldestSoundTime = sound.time;
                }
            }
        if (oldestSound !== false){
            //console.log('removing oldest sound:', oldestSound.name);
            oldestSound.sound.unload();
            delete gameSettings.soundEffectPool[oldestSound.name];
            }
        }
    */

    // Return now that we're done
    return true;

}

// Define a function for queueing something for when the game has started
function mmrpg_queue_for_game_start(onGameStart){
    gameSettings.onGameStart.push(onGameStart);
    if (!gameSettings.gameHasStarted){ return; }
    while (gameSettings.onGameStart.length){
        var onGameStart = gameSettings.onGameStart.shift();
        onGameStart.call();
        }
}


// -- POPUP WINDOW EVENT FUNCTIONS -- //

// Define a function that checks the server for any event popups to display
gameSettings.eventPullTimeout = false;
function windowEventsPull(forcePull, butForReal){
    //console.log('windowEventsPull()');
    if (!butForReal){
        if (gameSettings.eventPullTimeout){ clearTimeout(gameSettings.eventPullTimeout); }
        gameSettings.eventPullTimeout = setTimeout(function(){
            windowEventsPull(forcePull, true);
            }, 300);
        }
    // Do not pull events if we're currently in a sub-menu iframe
    forcePull = typeof forcePull === 'boolean' ? forcePull : false;
    var $mmrpg = $('#mmrpg');
    var $prototype = $('#prototype');
    if (!forcePull){
        if (!$mmrpg.length || $mmrpg.is('.iframe')){ return -1; }
        else if ($mmrpg.is('.iframe')){ return -2; }
        else if (!$prototype.length){ return -3; }
        }
    // Otherwise we can pull events from the server and display them
    $.ajax({
        url: 'scripts/get-events.php',
        dataType: 'json',
        success: function(response){
            //console.log('scripts/get-events.php returned ', response);
            if (typeof response.data !== 'undefined'
                && typeof response.data.events !== 'undefined'
                && typeof response.data.messages !== 'undefined'){
                //console.log('creating event');
                var eventsMarkup = response.data.events;
                var messagesMarkup = response.data.messages;
                if (eventsMarkup.length && messagesMarkup.length){
                    windowEventCreate(eventsMarkup, messagesMarkup, false);
                    windowEventDisplay();
                    }
                }
            }
        });
    // Return true to indicate that the pull was successful
    return true;
}

// Define a function for displaying event messages to the player
gameSettings.canvasMarkupArray = [];
gameSettings.messagesMarkupArray = [];
function windowEventCreate(canvasMarkupArray, messagesMarkupArray, autoDisplay){
    //console.log('windowEventCreate('+canvasMarkupArray+', '+messagesMarkupArray+')');
    if (typeof autoDisplay !== 'boolean'){ autoDisplay = true; }
    for (var i = 0; i < canvasMarkupArray.length; i++){ gameSettings.canvasMarkupArray.push(canvasMarkupArray[i]); }
    for (var i = 0; i < messagesMarkupArray.length; i++){ gameSettings.messagesMarkupArray.push(messagesMarkupArray[i]); }
    if (autoDisplay){
        if (!gameSettings.gameHasStarted){
            gameSettings.onGameStart.push(function(){ setTimeout(windowEventDisplay, 1000); });
            }
        else {
            windowEventDisplay();
            }
        }
}

// Define a function for displaying event messages to the player
gameSettings.activeWindowEvent = false;
function windowEventDisplay(){
    //console.log('windowEventDisplay()');
    if (gameSettings.activeWindowEvent){ return false; }
    gameSettings.activeWindowEvent = true;

    // Check if the event container exists and, if not, create it
    var $eventContainer = $('#events');
    if (!$eventContainer.length){

        // Define the markup for the event window dynamically
        $eventContainer = $(
            '<div id="events" class="hidden">'+
                '<div class="event_wrapper">'+
                    '<div class="event_container">'+
                        '<div id="headline" class="event_headline"></div>'+
                        '<div id="canvas" class="event_canvas"></div>'+
                        '<div id="messages" class="event_messages"></div>'+
                        '<div id="buttons" class="event_buttons"><a class="event_continue">Continue</a></div>'+
                    '</div>'+
                '</div>'+
            '</div>'
            );

        // Detect which parent window is available and then append the window to it
        var $eventContainerParent = false;
        if ($('#window').length){ $eventContainerParent = $('#window').first(); }
        else if ($('#prototype').length){ $eventContainerParent = $('#prototype').first(); }
        else if ($('#battle').length){ $eventContainerParent = $('#battle').first(); }
        else if ($('#mmrpg').length){ $eventContainerParent = $('#mmrpg').first(); }
        $eventContainerParent.append($eventContainer);

        // Define a click event for the event window continue button
        var $eventContinue = $('#buttons .event_continue', $eventContainer);
        $eventContinue.bind('click', function(e){
            e.preventDefault();
            //alert('clicked');
            if (typeof window.top.mmrpg_play_sound_effect !== 'undefined'){
                window.top.mmrpg_play_sound_effect('link-click');
                }
            windowEventDestroy();
            gameSettings.activeWindowEvent = false;
            if (gameSettings.canvasMarkupArray.length || gameSettings.messagesMarkupArray.length){
                windowEventDisplay();
                } else {
                let windowIframe = document.querySelector('#window iframe');
                if (windowIframe){ windowIframe.contentWindow.focus(); }
                }
            });

        // Define a function to run each time user inputs are updated so we can react
        let listenForInput = true;
        let eventsAreVisible = function(){ return $('#events').is(':visible:not(.hidden)') ? true : false; };
        let ignoreInputFor = function(delay){ delay = typeof delay === 'number' ? delay : 250; listenForInput = false; setTimeout(function(){ listenForInput = true; }, delay); };
        let checkUserInputs = function(kind, event, activeInputs, userInputs){
            //console.log('%c' + 'windowEventDisplay.checkUserInputs(kind:' + kind + ', event)', 'color: cyan;');
            if (!listenForInput){ return false; }
            if (!eventsAreVisible()){ return false; }
            if (!Object.keys(activeInputs).length){ return false; } // nothing pressed, ignore
            //console.log('-> activeInputs:', activeInputs);
            ignoreInputFor();
            // Collect refs to important elements
            let $eventContainer = $('#events');
            //console.log('-> $eventContainer:', $eventContainer);
            // If there's an event showing, then pressing the Start, A, or B will all dismiss to next
            if (activeInputs.A || activeInputs.B || activeInputs.Start){
                //console.log('%c' + 'Start/A/B button pressed!', 'color: orange;');
                if (event){ event.preventDefault(); }
                $eventContinue.trigger('click');
                ignoreInputFor(1000);
                return;
                }
            };

        // Start the user input watcher and collect reference to active inputs
        let userInputWatcher = new mmrpgUserInputWatcher();
        userInputWatcher.onUserInput(checkUserInputs);
        userInputWatcher.startWatching();

        }

    // Collect a reference to the inner event container within the parent one
    var $innerEventContainer = $('.event_container', $eventContainer);

    // Collect the canvas and message markup to be added to the event container
    var headlineMarkup = '';
    var canvasMarkup = gameSettings.canvasMarkupArray.length ? gameSettings.canvasMarkupArray.shift() : '';
    var messagesMarkup = gameSettings.messagesMarkupArray.length ? gameSettings.messagesMarkupArray.shift() : '';
    //console.log('canvasMarkup:', canvasMarkup, 'messagesMarkup:', messagesMarkup);

    if (messagesMarkup.indexOf('headline') !== -1){
        //console.log('headline class was found in the markup!');
        var $tempMessages = $('<div>'+messagesMarkup+'</div>');
        var $tempHeadline = $tempMessages.find('.headline');
        //console.log('$tempMessages =', $tempMessages);
        //console.log('$tempHeadline =', $tempHeadline);
        if ($tempHeadline.length){
            headlineMarkup = $tempHeadline.prop('outerHTML');
            messagesMarkup = messagesMarkup.replace(headlineMarkup, '');
            //console.log('new headlineMarkup =', headlineMarkup);
            //console.log('new messagesMarkup =', messagesMarkup);
        }
    }


    // Empty the canvas and messages of any leftover, fill them with new markup, then show 'em
    $('#headline', $eventContainer).empty().html(headlineMarkup);
    $('#canvas', $eventContainer).empty().html(canvasMarkup);
    $('#messages', $eventContainer).empty().html(messagesMarkup);
    $eventContainer.css({opacity:0}).removeClass('hidden');

    // Collect details about this event from the meta data if it exists
    var $metaData = $('meta[name][content]', $eventContainer);
    var metaData = {};
    if ($metaData.length){
        $metaData.each(function(){
            var $this = $(this);
            var name = $this.attr('name');
            var content = $this.attr('content');
            metaData[name] = content;
            });
        }
    if (typeof metaData['event_type'] === 'undefined'){ metaData['event_type'] = ''; }
    if (typeof metaData['player_token'] === 'undefined'){ metaData['player_token'] = ''; }
    //console.log('$metaData:', $metaData);
    //console.log('metaData:', metaData);

    // Check if this event is a story event based on the event type provided
    var isStoryEvent = false;
    var storyEventTypes = ['new-chapter', 'prototype-complete', 'prototype-postgame'];
    if (storyEventTypes.indexOf(metaData['event_type']) !== -1){ isStoryEvent = true; }

    // Update the event container with any visual changes as per the meta data
    if (metaData['event_type'].length){ $eventContainer.attr('data-type', metaData['event_type']); }
    else { $eventContainer.removeAttr('data-type'); }
    if (metaData['player_token'].length){ $eventContainer.attr('data-player', metaData['player_token']); }
    else { $eventContainer.removeAttr('data-player'); }

    // Now that everything is set up, wait for all images before we actually display
    $eventContainer.waitForImages(function(){

        // Animate the event container into view and re-add the animate class to ensure it players
        $innerEventContainer.removeClass('animate');
        $eventContainer.animate({opacity:1},300,'swing');
        if (typeof $.fn.perfectScrollbar !== 'undefined'){ $('#messages', $eventContainer).perfectScrollbar(gameSettings.scrollbarSettings); }
        setTimeout(function(){ $innerEventContainer.addClass('animate'); }, 250);
        $(window).focus();

        // Play the appropriate sound effect
        if (typeof window.top.mmrpg_play_sound_effect !== 'undefined'){
            //console.log('play sound effect');
            window.top.mmrpg_play_sound_effect('event-sound');
            }

        }, null, true);

}

// Define a function for displaying event messages to the player
function windowEventDestroy(){
    var $eventContainer = $('#events');
    //console.log('windowEventDestroy()');
    $('#canvas', $eventContainer).empty();
    $('#messages', $eventContainer).empty();
    $('.event_container', $eventContainer).removeClass('animate');
    $eventContainer.addClass('hidden');
    //alert(eventMarkup);
}

// Define a function for updating the loaded status of the main index page
function mmrpg_toggle_index_loaded(toggleValue){
    //console.log('game loaded!');
    if (toggleValue == true && gameSettings.indexLoaded != true){
        //console.log('unfade the splash loader');
        $('#mmrpg').removeClass('loading');
        // Fade out the splash loader text, change it to PLAY, then flade it in
        $('a.toggle span', gameMusic).css({opacity:1}).animate({opacity:0}, 1000, 'swing', function(){
            $('a.toggle', gameMusic).addClass('ready');
            $('a.toggle span', gameMusic).html('<div class="start"><div class="title">START</div><div class="subtitle">MEGA MAN RPG PROTOTYPE</div><div class="info">(Toggle music with &nbsp;&nbsp;)<div class="icon">&nbsp;</div></div></div>').animate({opacity:1}, 1000, 'swing', function(){
                // Remove the loading class from the iframe and fade it into view
                //$('iframe', gameWindow).css({opacity:0}).removeClass('loading').animate({opacity:1}, 1000, 'swing'); // DEBUG
                // Set the toggle loader flag to true
                gameSettings.indexLoaded = true;
                gameSettings.gameHasLoaded = true;
                });
            });
        }
}

// Define a function for updating the loaded status of the main index page
function mmrpg_toggle_debug_mode(element){
    // Collect the object references to the button and internal label
    var thisButton = $(element);
    var thisLabel = $('.multi', thisButton);
    // Pull the current value and use it to calculate new ones
    var thisValue = parseInt(thisButton.attr('data-value'));
    var newValue = thisValue != 1 ? 1 : 0;
    var newValueText = newValue == 1 ? 'ON' : 'OFF';
    var newValueClass = 'value type type_';
    newValueClass += newValue == 1 ? 'nature' : 'flame';
    //console.log('Toggle the debug mode!', {thisValue:thisValue,newValue:newValue,newValueText:newValueText,newValueClass:newValueClass});
    // Update the button value and label text/colour
    thisButton.attr('data-value', newValue);
    thisLabel.find('.value').html(newValueText).removeClass().addClass(newValueClass);
    // Send the new value to the server to update the session
    var thisRequestType = 'session';
    var thisRequestData = 'debug_mode,'+newValue;
    $.post('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
    return true;
}

// Define a function for updating the loaded status of the main index page
function mmrpg_toggle_settings_option(element){
    //console.log('mmrpg_toggle_settings_option()');

    // Collect the object references to the button and internal label
    var thisButton = $(element);
    var thisLabel = $('.multi', thisButton);

    // Parse the settings token and value, then clean the action token
    var thisSettingToken = thisButton.attr('data-setting-token');
    var thisSettingValue = parseInt(thisButton.attr('data-setting-value'));
    if (thisSettingValue === 1){ thisSettingValue = true; }
    else if (thisSettingValue === 0){ thisSettingValue = false; }
    //console.log('thisSettingToken =', thisSettingToken);
    //console.log('thisSettingValue =', thisSettingValue);

    // Pull the current value and use it to calculate new ones
    var newSettingValue = !thisSettingValue ? true : false;
    var newSettingValueText = newSettingValue ? 'ON' : 'OFF';
    var newSettingValueClass = 'value type type_';
    newSettingValueClass += (newSettingValue ? 'nature' : 'flame');
    //console.log('newSettingValue =', newSettingValue);

    // Update the local setting in case we need to work with it again
    gameSettings[thisSettingToken] = newSettingValue;
    //console.log('gameSettings[' + thisSettingToken + '] = ' + newSettingValue + ';');

    // Update the button value and label text/colour
    thisButton.attr('data-setting-value', (newSettingValue ? 1 : 0));
    thisLabel.find('.value').html(newSettingValueText).removeClass().addClass(newSettingValueClass);
    var thisRequestType = 'session';
    var thisRequestData = 'battle_settings,'+thisSettingToken+','+(newSettingValue ? 'true' : 'false');
    //console.log('thisRequestData =', thisRequestData);
    $.post('scripts/script.php',{requestType: thisRequestType, requestData: thisRequestData});
    if (typeof gameSettingsChangeEvents[thisSettingToken] === 'function'){ gameSettingsChangeEvents[thisSettingToken](newSettingValue); }

    return true;
}

// Define a function for updating the loaded status of the main index page
var windowTogglePerspectiveTimeout = false;
function mmrpg_toggle_perspective_mode(element){
    // Collect the object references to the button and internal label
    var thisButton = $(element);
    var thisLabel = $('.multi', thisButton);
    // Pull the current value and use it to calculate new ones
    var thisValue = parseInt(thisButton.attr('data-value'));
    var newValue = thisValue != 1 ? 1 : 0;
    var newValueText = newValue == 1 ? 'ON' : 'OFF';
    var newValueClass = 'value type type_';
    newValueClass += newValue == 1 ? 'nature' : 'flame';
    //console.log('Toggle the perspective mode!', {thisValue:thisValue,newValue:newValue,newValueText:newValueText,newValueClass:newValueClass});
    // Update the button value and label text/colour
    thisButton.attr('data-value', newValue);
    thisLabel.find('.value').html(newValueText).removeClass().addClass(newValueClass);
    // Send the new value to the server to update the session
    var thisRequestType = 'session';
    var thisRequestData = 'perspective_mode,'+newValue;
    if (windowTogglePerspectiveTimeout !== false){ clearTimeout(windowTogglePerspectiveTimeout); }
    windowTogglePerspectiveTimeout = setTimeout(function(){
        $.post('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
        }, 1000);
    return true;
}


// Define a function for keeping the session alive and auto-redirecting when it's over
function mmrpg_keep_session_alive(sessionUserID){
    //console.log('mmrpg_keep_session_alive()');

    var keepSessionAlive = true;
    var thisSessionUserID = sessionUserID;
    var sessionPingFrequency = 1000 * 60 * 5; // every 5 mins
    var sessionPingURL = gameSettings.baseHref + 'scripts/ping.php';
    var loginPageURL = gameSettings.baseHref + 'file/load/';

    // Define a function that "pings" the server to keep login status alive
    var extendGameSession = function(){
        //console.log('extendGameSession()');
        if (!keepSessionAlive){ return false; }
        $.post(sessionPingURL, function(data){
            //console.log('data =', data);
            if (typeof data.status !== 'undefined'
                && data.status === 'success'
                && data.user_id === thisSessionUserID){
                keepSessionAlive = true;
                } else {
                keepSessionAlive = false;
                redirectToLogin();
                }
            //console.log('keepSessionAlive =', keepSessionAlive);
            });
        };

    // Define a function that redirects to the login frame when logged out
    var redirectToLogin = function(){
        //console.log('redirectToLogin()');
        var confirmRedirect = confirm('MMRPG SESSION ERROR! \n'
            + 'Your session has expired or you logged out in another frame! \n'
            + 'The game cannot function in this state and must be restarted. \n'
            + 'You will now be redirected to the login page... '
            );
        if (!confirmRedirect){ return; }
        if (window.self !== window.top){
            window.top.location.href = loginPageURL;
            } else {
            window.location.href = loginPageURL;
            }
        };

    // Start the extend session interval to keep pinging every X minutes
    var extendSessionInterval = setInterval(function(){
        if (keepSessionAlive){ extendGameSession(); }
        else { clearInterval(extendSessionInterval); }
        }, sessionPingFrequency);

}

// Define a function that takes a given element and aligns it to a specific target X,Y
// while knowing the bounds of the window and making sure the tooltip
// is always fully visible.  This means making it center-bottom aligned
// to the target position by default, but adjusting in the following:
// -  when too far left to show entire tooltip, make left-aligned
// -  when too far right to show entire tooltip, make right-aligned
// -  when too far down to show entire tooltip, make bottom-aligned
// -  when too far up to show entire tooltip, make top-aligned
function mmrpg_align_element_to_target($element, targetX, targetY){
    //console.log('mmrpg_align_element_to_target() w/ targetX =', targetX, ' & targetY =', targetY);
    let $mmrpgBody = $('#mmrpg');
    if (!$element.length){ console.error('no element found!'); return false; }
    else if (!$element.is(':visible')){ console.error('element not visible!'); return false; }
    let elementWidth = $element.outerWidth();
    let elementHeight = $element.outerHeight();
    let currentBodyWidth = gameSettings.currentBodyWidth;
    let currentBodyHeight = gameSettings.currentBodyHeight;
    //console.log('-> elementWidth:', elementWidth, '\n', '-> elementHeight:', elementHeight, '\n', '-> currentBodyWidth:', currentBodyWidth, '\n', '-> currentBodyHeight:', currentBodyHeight);
    let newPosX = targetX - (elementWidth / 2);
    let newPosY = targetY - elementHeight - 10;
    let newPosRight = 'auto';
    let newPosBottom = 'auto';
    let newPosLeft = newPosX;
    let newPosTop = newPosY;
    // If the new X position is too far left, make it left-aligned
    if (newPosX < 10){
        newPosLeft = targetX + 10;
        }
    // If the new X position is too far right, make it right-aligned
    else if ((newPosX + elementWidth) > (currentBodyWidth - 10)){
        newPosLeft = 'auto';
        newPosRight = currentBodyWidth - targetX + 10;
        }
    // If the new Y position is too far up, make it top-aligned
    if (newPosY < 10){
        newPosTop = targetY + 10;
        newPosBottom = 'auto';
        }
    // If the new Y position is too far down, make it bottom-aligned
    else if ((newPosY + elementHeight) > (currentBodyHeight - 10)){
        newPosTop = 'auto';
        newPosBottom = currentBodyHeight - targetY + 10;
        }
    $element.css({left:newPosLeft, top:newPosTop, right:newPosRight, bottom:newPosBottom});
    return true;
}

// Define a reusable wait-for method and its sister functions
let mmrpgWaitForIt = function(){
    let _self = this;
    let waitingFor, waitFor, onWaitComplete, doneWaitingFor, checkWaitComplete, startWaiting;
    waitingFor = {};
    waitFor = function(name, callback){ /*console.log('waitFor(', name, ', callback)');*/ waitingFor[name] = callback; };
    onWaitComplete = function(callback){ /*console.log('onWaitComplete(callback)');*/ onWaitCompleteCallback = callback; };
    doneWaitingFor = function(name){ /*console.log('doneWaitingFor(', name, ')');*/ delete waitingFor[name]; checkWaitComplete(); };
    checkWaitComplete = function(){ /*console.log('checkWaitComplete()');*/ if (Object.keys(waitingFor).length < 1){ onWaitCompleteCallback(); } };
    startWaiting = function(){
        //console.log('startWaiting()');
        let waitingForKeys = Object.keys(waitingFor);
        //console.log('waitingForKeys =', waitingForKeys);
        if (waitingForKeys.length < 1){ return false; }
        for (var i = 0; i < waitingForKeys.length; i++){
            let key = waitingForKeys[i], callback = waitingFor[key];
            //console.log('running callback for key ', key);
            callback.call(_self);
            }
        };
    return {waitFor, onWaitComplete, doneWaitingFor, startWaiting};
    };

// Define a reusable object for watching user input and storing it button abstractions we can work with elsewhere
class mmrpgUserInputWatcher {
    constructor(config, callbacks){
        //console.log('%c' + 'mmrpgUserInputWatcher.constructor()', 'color: magenta;');
        config = typeof config === 'object' ? config : {};
        callbacks = typeof callbacks === 'object' ? callbacks : {};

        // Define the top-level object and its defaults
        let _self = this;
        _self.config = null;
        _self.events = null;
        _self.userInputs = {}; // all possible inputs
        _self.activeInputs = {}; // currently active inputs
        _self.activeTimeouts = {}; // currently active timeout
        _self.lastInputKind = null;
        _self.lastInputEvent = null;
        _self.lastInputKey = null;

        // Define the config object and its defaults
        let _config = {};
        _config.autoStart = typeof config.autoStart === 'boolean' ? config.autoStart : false;
        _config.inputTimeout = typeof config.inputTimeout === 'number' ? config.inputTimeout : (1000 / 30); // 30fps
        _config.wheelTimeout = typeof config.wheelTimeout === 'number' ?  config.wheelTimeout : _config.inputTimeout;
        _config.gamepadTimeout = typeof config.gamepadTimeout === 'number' ? config.gamepadTimeout : _config.inputTimeout;
        _config.autoRunCallbacks = typeof config.autoRunCallbacks === 'boolean' ? config.autoRunCallbacks : true;
        _config.bubbleIframeInputs = typeof config.bubbleIframeInputs === 'boolean' ? config.bubbleIframeInputs : true;
        _config.catchIframeInputs = typeof config.catchIframeInputs === 'boolean' ? config.catchIframeInputs : true;
        _config.stickDeadzone = typeof config.stickDeadzone === 'number' ? config.stickDeadzone : 0.25;
        _config.diagonalBias = typeof config.diagonalBias === 'number' ? config.diagonalBias : 0.4;
        _config.gamepadKind = typeof config.gamepadKind === 'number' ? config.gamepadKind : null;
        _self.config = _config;

        // Define the events object and its defaults
        let _events = {};
        _events.onUserInput = typeof callbacks.onUserInput === 'function'
            ? callbacks.onUserInput : function(kind, event, activeInputs, userInputs){
            // to-be-replaced by the calling function
            console.warn('%c' + 'default onUserInput() called!', 'color: orange;');
            console.warn('w/ -> kind:', kind, '\n', '-> event:', activeInputs, '\n', '-> activeInputs:', activeInputs, '\n', '-> userInputs:', userInputs);
            return true;
            };
        _self.events = _events;

        // Define an index of symbolic "userInputs" we can abstract actions behind, and then
        // worry about specific key-bindings and button-mappings later on to keep things clean
        let userInputs = {}; // below will be the default for now, but we'll allow customizing later
        userInputs.A = {name: 'A', icon: 'Ⓐ', keyboard: ['d', 'Space'], gamepad: [0], sonyIcon: '⨯', sonyName: 'Cross'};
        userInputs.B = {name: 'B', icon: 'Ⓑ', keyboard: ['s', 'Backspace'], gamepad: [1], sonyIcon: '◯', sonyName: 'Circle'};
        userInputs.X = {name: 'X', icon: 'Ⓧ', keyboard: ['f', 'Escape', '\\'], gamepad: [2], sonyIcon: '△', sonyName: 'Triangle'};
        userInputs.Y = {name: 'Y', icon: 'Ⓨ', keyboard: ['a', 'Tab'], gamepad: [3], sonyIcon: '□', sonyName: 'Square'};
        userInputs.L1 = {name: 'L1', icon: 'L1', keyboard: ['q', '['], gamepad: [4]};
        userInputs.R1 = {name: 'R1', icon: 'R1', keyboard: ['e', ']'], gamepad: [5]};
        userInputs.LR1 = {name: 'L1+R1', icon: 'L1+R1', keyboard: ['w'], gamepad: [4, 5], isCombo: true};
        userInputs.L2 = {name: 'L2', icon: 'L2', keyboard: ['z', '-'], gamepad: [6], nintendoIcon: 'ZL', nintendoName: 'ZL'};
        userInputs.R2 = {name: 'R2', icon: 'R2', keyboard: ['c', '='], gamepad: [7], nintendoIcon: 'ZR', nintendoName: 'ZR'};
        userInputs.LR2 = {name: 'L2+R2', icon: 'L2+R2', keyboard: ['x'], gamepad: [6, 7], isCombo: true, nintendoIcon: 'ZL+ZR', nintendoName: 'ZL+ZR'};
        userInputs.Start = {name: 'Start', icon: '+', keyboard: ['Enter', 'Home'], gamepad: [9], nintendoIcon: '+', nintendoName: 'Plus', sonyIcon: ']', sonyName: 'Option'};
        userInputs.Select = {name: 'Select', icon: '−', keyboard: ['Shift', 'End'], gamepad: [8], nintendoIcon: '-', nintendoName: 'Minus', sonyIcon: '[', sonyName: 'Share'};
        userInputs.Up = {name: 'Up', icon: '⏶', keyboard: ['ArrowUp'], gamepad: [12]};
        userInputs.Down = {name: 'Down', icon: '⏷', keyboard: ['ArrowDown'], gamepad: [13]};
        userInputs.Left = {name: 'Left', icon: '⏴', keyboard: ['ArrowLeft'], gamepad: [14]};
        userInputs.Right = {name: 'Right', icon: '⏵', keyboard: ['ArrowRight'], gamepad: [15]};
        _self.userInputs = userInputs;
        _self.baseUserInputs = JSON.parse(JSON.stringify(userInputs));

        // Define the abstraction method for handling user input events
        let onUserInput = function(kind, event){
            //console.log('mmrpgUserInputWatcher.onUserInput(kind:', kind, ', event:', event, ')');
            _self.lastInputKind = kind;
            _self.lastInputEvent = event;
            if (_config.bubbleIframeInputs && window !== window.parent){
                let isGamepadEvent = (kind === 'gamepadinput' || kind === 'gamepadconnected' || kind === 'gamepaddisconnected');
                if (!isGamepadEvent){
                    window.parent.postMessage({
                        action: 'bubbleUserInput',
                        kind: kind,
                        activeInputs: activeInputs
                        }, window.location.origin);
                    }
                }
            if (_config.autoRunCallbacks){
                _events.onUserInput.call(_self, kind, event, activeInputs, userInputs);
                }
            };

        // If toggled, make sure we allow left-stick input to count as directional-input
        let allowStickMovement = true; // TODO: make this customizable later
        if (allowStickMovement){
            // Grab the deadzone from config to use as our threshold
            let dz = _config.stickDeadzone;
            // axes[0] = Left Stick X, axes[1] = Left Stick Y
            // axes[2] = Right Stick X, axes[3] = Right Stick Y
            // Add an extra listener for gamepad axis to the directional inputs
            userInputs.Left.axis = [0, -dz];
            userInputs.Right.axis = [0, dz];
            userInputs.Up.axis = [1, -dz];
            userInputs.Down.axis = [1, dz];
            //console.log('userInputs =', userInputs);
            }

        // Define a quick function that takes a given keyboard press (mixed) and returns the user input key for it
        let getUserInputFromKeyboardEvent = function(keyCode){
            //console.log('%c' + 'getUserInputFromKeyboardEvent(keyCode:', keyCode, ') called!', 'color: magenta;');
            //console.log('-> keyCode =', keyCode);
            if (!keyCode){ return false; }
            let returnKey = false;
            Object.keys(userInputs).forEach(function(inputKey){
                let inputData = userInputs[inputKey];
                if (inputData.keyboard && inputData.keyboard.indexOf(keyCode) !== -1){
                    //console.log('-> inputKey =', inputKey);
                    returnKey = inputKey;
                    _self.lastInputKey = returnKey;
                    return;
                    }
                });
            return returnKey;
            };

        // Define a function for taking a scroll-wheel event and translating it into L1 + R1 button presses
        // (make sure we ignore deltas less than +/- threshold to avoid accidental button presses)
        // (ignore the use-case above, L1 and R1 might be used for other stuff too so be generic)
        let busyScrolling = false;
        let wheelThreshold = 150;
        let wheelTimeout = _config.wheelTimeout;
        let getUserInputFromWheelEvent = function(event){
            if (busyScrolling){ return false; }
            if (!event.wheelDelta){ return false; }
            //console.log('event.wheelDelta =', event.wheelDelta);
            if (event.wheelDelta > 0 && event.wheelDelta < wheelThreshold){ return false; }
            else if (event.wheelDelta < 0 && event.wheelDelta > (-1 * wheelThreshold)){ return false; }
            busyScrolling = true;
            let inputKey = event.wheelDelta < 0 ? 'L1' : 'R1';
            if (typeof activeInputs[inputKey] === 'undefined'){
                activeInputs[inputKey] = true;
                }
            setTimeout(function(){
                delete activeInputs[inputKey];
                busyScrolling = false;
                }, wheelTimeout);
            return inputKey;
            };

        // Define a function for determining the current controller type (for button icons) if possible
        let gamepadKind = null, gamepadKinds = {
            other: {id: 0, token: 'other', name: 'Generic/Other'},
            nintendo: {id: 1, token: 'nintendo', name: 'Nintendo'},
            sony: {id: 2, token: 'sony', name: 'PlayStation'},
            xbox: {id: 3, token: 'xbox', name: 'Xbox'}
            };
        let updateGamepadKind = function(gamepad){
            if (_config.gamepadKind && _config.gamepadKind !== null){ return _config.gamepadKind; }
            else if (gamepadKind && gamepadKind !== null){ return gamepadKind; }
            //console.log('updateGamepadKind() w/ gamepad:', gamepad);
            let gamepadID = gamepad.id, gamepadToken = 'other';
            //console.log('...and gamepadID:', gamepadID);
            if (gamepadID.includes("Nintendo") || gamepadID.includes("Joy-Con") || gamepadID.includes("Pro Controller")){ gamepadToken = 'nintendo'; }
            else if (gamepadID.includes("Sony") || gamepadID.includes("DualSense") || gamepadID.includes("DualShock")){ gamepadToken = 'sony'; }
            else if (gamepadID.includes("Xbox") || gamepadID.includes("X-Input")){ gamepadToken = 'xbox'; }
            //console.log('...gives gamepadToken:', gamepadToken);
            if (typeof gamepadKinds[gamepadToken] !== 'undefined'){ gamepadKind = gamepadToken; } else { gamepadKind = null; }
            //console.log('...resulting in gamepadKind =', gamepadKind);
            updateGamepadInputs(gamepadKind);
            return gamepadKind;
            };
        let updateGamepadInputs = function(gamepadKind){
            // Swap the A and B, X and Y buttons if we're on Nintendo, else default
            let userInputs = _self.userInputs;
            let baseUserInputs = _self.baseUserInputs, baseButtonKeys = {};
            baseButtonKeys.A = baseUserInputs.A.gamepad, baseButtonKeys.B = baseUserInputs.B.gamepad;
            baseButtonKeys.X = baseUserInputs.X.gamepad, baseButtonKeys.Y = baseUserInputs.Y.gamepad;
            if (gamepadKind === 'nintendo'){
                // nintendo controllers use original A/B and X/Y placement
                userInputs.A.gamepad = Object.values(baseButtonKeys.B);
                userInputs.B.gamepad = Object.values(baseButtonKeys.A);
                userInputs.X.gamepad = Object.values(baseButtonKeys.Y);
                userInputs.Y.gamepad = Object.values(baseButtonKeys.X);
                }
            else if (gamepadKind === 'xbox'){
                // xbox controllers have different icons, but button placement is consistent
                userInputs.A.gamepad = Object.values(baseButtonKeys.A);
                userInputs.B.gamepad = Object.values(baseButtonKeys.B);
                userInputs.X.gamepad = Object.values(baseButtonKeys.X);
                userInputs.Y.gamepad = Object.values(baseButtonKeys.Y);
                }
            else if (gamepadKind === 'sony'){
                // xbox controllers have different icons, but button placement is consistent
                userInputs.A.gamepad = Object.values(baseButtonKeys.A);
                userInputs.B.gamepad = Object.values(baseButtonKeys.B);
                userInputs.X.gamepad = Object.values(baseButtonKeys.Y);
                userInputs.Y.gamepad = Object.values(baseButtonKeys.X);
                }
            else {
                // otherwise use the default button values and just leave it be
                userInputs.A.gamepad = Object.values(baseButtonKeys.A);
                userInputs.B.gamepad = Object.values(baseButtonKeys.B);
                userInputs.X.gamepad = Object.values(baseButtonKeys.X);
                userInputs.Y.gamepad = Object.values(baseButtonKeys.Y);
                }
            // Also update the icons on a per-console basis in case they're different
            let userInputKeys = Object.keys(userInputs);
            let userInputDefaults = Object.values(_self.baseUserInputs);
            for (let i = 0; i < userInputKeys.length; i++){
                let inputKey = userInputKeys[i];
                let inputDefaults = userInputDefaults[i];
                let userInput = userInputs[inputKey];
                //console.log('checking inputKey', inputKey, 'w/ inputDefaults', inputDefaults);
                let inputIcon = inputDefaults.icon;
                //console.log('-> default is ', inputDefaults.icon, ', checking for console-specific icon ...');
                if (gamepadKind && inputDefaults[gamepadKind + 'Icon']){
                    //console.log('--> ', gamepadKind, 'gamepad connected, getting custom icon ...');
                    userInput.icon = inputDefaults[gamepadKind + 'Icon'];
                    } else {
                    //console.log('--> gamepad not connected, resetting to default ....');
                    userInput.icon = inputDefaults['icon'];
                    }
                //console.log('-> final icon is ', userInput.icon);
                }
            };

        // Define a function for watching gamepad inputs and updating the activeInputs object accordingly
        let connectedGamepad = null;
        let watchGamepadInputs = function(gamepad){
            if (gamepad === null){ connectedGamepad = null; gamepadKind = null; return; }
            else if (typeof gamepad !== 'undefined'){ connectedGamepad = gamepad; }
            if (!connectedGamepad || typeof connectedGamepad.index === 'undefined'){ return false; }
            let gp = navigator.getGamepads()[connectedGamepad.index];
            if (!gp){ return false; }
            else { updateGamepadKind(gp); }
            //console.log('watchGamepadInputs -> gamepadKind:', gamepadKind);
            let consumedButtons = new Set();
            let newActiveStates = {};
            let isBtnPressed = (idx) => gp.buttons[idx] && gp.buttons[idx].pressed;
            let isAxisPushed = (axisData) => {
                if (!allowStickMovement || !axisData){ return false; }
                let [axisIndex, threshold] = axisData;
                let val = gp.axes[axisIndex];
                let passesDeadzone = (threshold < 0 && val <= threshold) || (threshold > 0 && val >= threshold);
                if (!passesDeadzone){ return false; }
                let pairedAxisIndex = axisIndex % 2 === 0 ? axisIndex + 1 : axisIndex - 1;
                let pairedVal = gp.axes[pairedAxisIndex];
                if (Math.abs(val) < Math.abs(pairedVal) * _config.diagonalBias){ return false; }
                return true;
                };
            Object.keys(userInputs).forEach(key => { // PASS 1: Check Combos First
                let data = userInputs[key], gamepad = data.gamepad;
                if (!data.isCombo){ return; } // skip if not a checkable combo
                if (gamepad && gamepad.length && gamepad.every(isBtnPressed)) {
                    newActiveStates[key] = true;
                    gamepad.forEach(btn => consumedButtons.add(btn));
                    }
                });
            //console.log('consumedButtons =', consumedButtons);
            Object.keys(userInputs).forEach(key => { // PASS 2: Check Standard Inputs & Axes
                if (newActiveStates[key]){ return; } // Skip if already handled by Pass 1
                let data = userInputs[key], gamepad = data.gamepad, axis = data.axis;
                if (data.isCombo){ return; } // skip if already-checked combo
                let btnPressed = gamepad && gamepad.length && gamepad.some(btn => isBtnPressed(btn) && !consumedButtons.has(btn));
                let axisPressed = isAxisPushed(axis);
                if (btnPressed || axisPressed) {
                    newActiveStates[key] = true;
                    }
                });
            let nullfn = function(){};
            Object.keys(userInputs).forEach(key => { // PASS 3: Update State & Fire Events
                let isPressed = !!newActiveStates[key];
                let wasPressed = !!activeInputs[key];
                if (isPressed !== wasPressed) {
                    if (isPressed) { activeInputs[key] = true; }
                    else { delete activeInputs[key]; }
                    let event = new Event('gamepadinput', { bubbles: true, cancelable: true, preventDefault: nullfn, stopPropagation: nullfn });
                    document.dispatchEvent(event);
                    onUserInput('gamepadinput', event);
                    }
                });
            requestAnimationFrame(function(){ watchGamepadInputs(); });
            };

        // Define an object to hold all currently pressed keys individually or in combo
        let activeInputs = {};
        _self.activeInputs = activeInputs;

        // Define a quick object to hold all the listening objects (in case we need to remove them)
        let eventListeners = {};
        eventListeners.keydown = function(event){ let input = getUserInputFromKeyboardEvent(event.key); if (input){ activeInputs[input] = true; } onUserInput('keydown', event); };
        eventListeners.keyup = function(event){ let input = getUserInputFromKeyboardEvent(event.key); if (input){ delete activeInputs[input]; } onUserInput('keyup', event); };
        eventListeners.mousewheel = function(event){ getUserInputFromWheelEvent(event); onUserInput('mousewheel', event); };
        eventListeners.gamepadconnected = function(event){ watchGamepadInputs(event.gamepad); onUserInput('gamepadconnected', event); };
        eventListeners.gamepaddisconnected = function(event){ watchGamepadInputs(null); onUserInput('gamepaddisconnected', event); };
        eventListeners.message = function(messageEvent){
            let data = messageEvent.data;
            if (data && data.action === 'bubbleUserInput'){
                _self.lastInputKind = data.kind;
                _self.lastInputEvent = null;
                if (_config.autoRunCallbacks || _events.onUserInput){
                    _events.onUserInput.call(_self, data.kind, null, data.activeInputs, userInputs);
                    }
                }
            };

        // Define an event to call when we want to start watching all the inputs
        let startWatchingInputs = function(){
            //console.log('%c' + 'mmrpgUserInputWatcher.startWatchingInputs()', 'color: magenta;');

            // Bind events to the keyboard arrow keys if detected to allow for it
            document.addEventListener('keydown', eventListeners.keydown, { passive: false });
            document.addEventListener('keyup', eventListeners.keyup, { passive: false });

            // Bind events to the scrolling of the user's mouse if detected and map to L2 + R2 button inputs
            document.addEventListener('mousewheel', eventListeners.mousewheel, { passive: false });

            // Beind events to any connected gamepads to allow for the same
            // functionality as the keyboard arrow keys (mirror for easier coding)
            window.addEventListener("gamepadconnected", eventListeners.gamepadconnected, { passive: false });
            window.addEventListener("gamepaddisconnected", eventListeners.gamepaddisconnected, { passive: false });

            // Only listen for bubbled messages if we are the top-level parent window
            if (_config.catchIframeInputs && window === window.parent){
                window.addEventListener('message', eventListeners.message, { passive: false });
                }

            };

        // Define an event to call when we want to stop watching all the inputs
        let stopWatchingInputs = function(){
            //console.log('%c' + 'mmrpgUserInputWatcher.stopWatchingInputs()', 'color: magenta;');

            // Remove events from the keyboard arrow keys
            document.removeEventListener('keydown', eventListeners.keydown);
            document.removeEventListener('keyup', eventListeners.keyup);

            // Remove events from the scrolling of the user's mouse
            document.removeEventListener('mousewheel', eventListeners.mousewheel);

            // Remove events from any connected gamepads
            window.removeEventListener("gamepadconnected", eventListeners.gamepadconnected);
            window.removeEventListener("gamepaddisconnected", eventListeners.gamepaddisconnected);

            // Clean up the message listener as well
            if (_config.catchIframeInputs && window === window.parent){
                window.removeEventListener('message', eventListeners.message);
                }

            };

        // Start watching the inputs right away
        if (_config.autoStart){ startWatchingInputs(); }

        // Return a little API for accessing the userInputs and activeInputs objects
        return {
            config: _self.config,
            events: _self.events,
            userInputs: _self.userInputs,
            activeInputs: _self.activeInputs,
            lastInputKind: _self.lastInputKind,
            lastInputEvent: _self.lastInputEvent,
            startWatching: startWatchingInputs,
            stopWatching: stopWatchingInputs,
            onUserInput: function(callback){
                if (typeof callback !== 'function'){ return false; }
                _self.events.onUserInput = callback;
                return true;
                },
            checkUserInputs: function(){
                if (!_events.onUserInput){ return false; }
                let kind = _self.lastInputKind, event = _self.lastInputEvent;
                _events.onUserInput.call(_self, kind, event, activeInputs, userInputs);
                return true;
                }
            };

    }
}

/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 */
function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";

    if(typeof(arr) == 'object') { //Array/Hashes/Objects
        for(var item in arr) {
            var value = arr[item];

            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}

// Define a jQuery function for preloading images
(function($){
    var cache = [];
    // Arguments are image paths relative to the current page.
    $.preLoadImages = function(){
        var args_len = arguments.length;
        for (var i = args_len; i--;){
            var cacheImage = document.createElement('img');
            cacheImage.src = arguments[i];
            cache.push(cacheImage);
        }
    }
})(jQuery)

// Define a jQuery function for waiting for images
;(function($) {

        // Namespace all events.
        var eventNamespace = 'waitForImages';

        // CSS properties which contain references to images.
        $.waitForImages = {
            hasImageProperties: [
            'backgroundImage',
            'listStyleImage',
            'borderImage',
            'borderCornerImage'
            ]
        };

        // Custom selector to find `img` elements that have a valid `src` attribute and have not already loaded.
        $.expr[':'].uncached = function(obj) {
            // Ensure we are dealing with an `img` element with a valid `src` attribute.
            if ( ! $(obj).is('img[src!=""]')) {
                return false;
            }
            // Firefox's `complete` property will always be`true` even if the image has not been downloaded.
            // Doing it this way works in Firefox.
            var img = document.createElement('img');
            img.src = obj.src;
            return ! img.complete;
        };

        $.fn.waitForImages = function(finishedCallback, eachCallback, waitForAll) {
            // Handle options object.
            if ($.isPlainObject(arguments[0])) {
                eachCallback = finishedCallback.each;
                waitForAll = finishedCallback.waitForAll;
                finishedCallback = finishedCallback.finished;
            }
            // Handle missing callbacks.
            finishedCallback = finishedCallback || $.noop;
            eachCallback = eachCallback || $.noop;
            // Convert waitForAll to Boolean
            waitForAll = !! waitForAll;
            // Ensure callbacks are functions.
            if (!$.isFunction(finishedCallback) || !$.isFunction(eachCallback)) {
                throw new TypeError('An invalid callback was supplied.');
            };
            return this.each(function() {
                // Build a list of all imgs, dependent on what images will be considered.
                var obj = $(this);
                var allImgs = [];
                var processedImages = new Set();
                if (waitForAll){
                    // CSS properties which may contain an image.
                    var hasImgProperties = $.waitForImages.hasImageProperties || [];
                    var matchUrl = /url\((['"]?)(.*?)\1\)/g;
                    // Get all elements, as any one of them could have a background image.
                    obj.find('*').each(function(){
                        var element = $(this);
                        // If an `img` element, add it. But keep iterating in case it has a background image too.
                        if (element.is('img:uncached')
                            && !processedImages.has(element.attr('src'))){
                            allImgs.push({
                                src: element.attr('src'),
                                element: element[0]
                                });
                            processedImages.add(element.attr('src'));
                        }
                        $.each(hasImgProperties, function(i, property){
                            var propertyValue = element.css(property);
                            // If it doesn't contain this property, skip.
                            if (!propertyValue){
                                return true;
                                }
                            // Get all url() of this element.
                            var match;
                            while (match = matchUrl.exec(propertyValue)){
                                if (!processedImages.has(match[2])){
                                    allImgs.push({
                                        src: match[2],
                                        element: element[0]
                                        });
                                    processedImages.add(match[2]);
                                    }
                                };
                            });
                        });
                } else {
                    // For images only, the task is simpler.
                    obj.find('img:uncached').each(function(){
                            allImgs.push({
                                src: this.src,
                                element: this
                                });
                            });
                };
                var allImgsLength = allImgs.length;
                var allImgsLoaded = 0;
                // If no images found, don't bother.
                if (allImgsLength == 0){
                    finishedCallback.call(obj[0]);
                    };
                //console.log('allImgs =', allImgs, allImgs.length);
                $.each(allImgs, function(i, img) {
                    var image = new Image;
                    var loadedOrErrored = false;  // Add this line
                    // Update the callback
                    $(image).bind('load.' + eventNamespace + ' error.' + eventNamespace, function(event) {
                        // Only increment if this is the first event for this image
                        if (!loadedOrErrored) {
                            loadedOrErrored = true;
                            allImgsLoaded++;
                            // If an error occurred with loading the image, set the third argument accordingly.
                            eachCallback.call(img.element, allImgsLoaded, allImgsLength, event.type == 'load');
                            if (allImgsLoaded == allImgsLength) {
                                finishedCallback.call(obj[0]);
                                return false;
                            };
                        }
                    });
                    image.src = img.src;
                });
            });
        };

})(jQuery);

// Extend jQuery to offer a "triggerSilentClick" trigger so that we can do menu stuff in the background without
// triggering associated sound effects prematurely (as well as other helpful functionality, presumably)
(function($) {
    $.fn.triggerSilentClick = function() {
        return this.each(function() {
            var $this = $(this);

            // Your special functionality goes here.
            // For example, if you need to log some information
            //console.log('Pre-click special functionality executed!');

            // Add a data attribute to the element
            $this.data('silentClick', true);

            // Then, trigger the click event
            $this.trigger('click');

            // After the click event, remove the data attribute
            $this.removeData('silentClick');

        });
    };
})(jQuery);

// Extend jQuery to offer a "removeClassByRegex" function that removes classes matching a regex
// This allows us to remove classes that match a specific pattern without needing to know the exact class names
(function($) {
    $.fn.removeClassByRegex = function(regex) {
      return $(this).removeClass(function(index, classes) {
        return classes.split(/\s+/).filter(function(c) {
          return regex.test(c);
        }).join(' ');
      });
    };
})(jQuery);

/* Define a function to randomize an array in-place using Durstenfeld shuffle algorithm */
if (typeof window.shuffleArray === 'undefined'){
    function shuffleArray(array) {
        for (var i = array.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = array[i];
            array[i] = array[j];
            array[j] = temp;
            }
        }
    }

/* Define a function to calculate distance between two points */
if (typeof window.calcDistance === 'undefined'){
    function calculateDistance(x1, y1, x2, y2) {
        return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
        }
    }

if (typeof window.toUpperCaseWords === 'undefined'){
    function toUpperCaseWords(str){ return str.toLowerCase().replace(/\b[a-z]/g, (l) => l.toUpperCase()); }
    }

// Fix the indexOf issue for IE8 and lower
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
        "use strict";
        if (this === void 0 || this === null) { throw new TypeError(); }
        var t = Object(this);
        var len = t.length >>> 0;
        if (len === 0) { return -1; }
        var n = 0;
        if (arguments.length > 0) {
            n = Number(arguments[1]);
            if (n !== n) { n = 0; }
            else if (n !== 0 && n !== Infinity && n !== -Infinity) { n = (n > 0 || -1) * Math.floor(Math.abs(n)); }
            }
        if (n >= len) { return -1; }
        var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
        for (; k < len; k++) { if (k in t && t[k] === searchElement) { return k; } }
        return -1;
        }
    };

// Polyfill for requestAnimationFrame if not exists
window.requestAnimationFrame = window.requestAnimationFrame
|| window.mozRequestAnimationFrame
|| window.webkitRequestAnimationFrame
|| window.msRequestAnimationFrame
|| function(f){return setTimeout(f, 1000/60)};
window.cancelAnimationFrame = window.cancelAnimationFrame
    || window.mozCancelAnimationFrame
    || function(requestID){clearTimeout(requestID)};

