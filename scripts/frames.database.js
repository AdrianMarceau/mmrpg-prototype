
// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
gameSettings.databaseCanvasMarkup = '';
gameSettings.databaseConsoleMarkup = '';
$(document).ready(function(){

    // Tint background color if not in frame
    if (window.top == window.self){ $('body').css({backgroundColor:'#262626'}); }

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the database menu
    var thisContext = $('#database');
    var playSoundEffect = function(){};
    if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){

        // Define a quick local function for routing sound effect plays to the parent
        playSoundEffect = function(soundName, options){
            if (this instanceof jQuery || this instanceof Element){
                if ($(this).data('silentClick')){ return; }
                if ($(this).is('.disabled')){ return; }
                if ($(this).is('.button_disabled')){ return; }
                }
            top.mmrpg_play_sound_effect(soundName, options);
            };

        // DATABASE PAGE LINKS

        // Add hover and click sounds to the buttons in the game-pages menu
        $('#canvas #robot_games .game_link', thisContext).live('mouseenter', function(){
            //console.log('hovering over database game page');
            if ($(this).is('.game_link_disabled')){ return; }
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#canvas #robot_games .game_link', thisContext).live('click', function(){
            //console.log('clicking database game page');
            if ($(this).is('.game_link_disabled')){ return; }
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });

        // DATABASE ICON LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#canvas .wrapper_robots .sprite_robot', thisContext).live('mouseenter', function(){
            //console.log('hovering over database robot icon');
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#canvas .wrapper_robots .sprite_robot', thisContext).live('click', function(){
            //console.log('clicking database robot icon');
            if ($(this).is('[data-token-locked]')){ return; }
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });

        // DATABASE PAGE SPANS

        // Add hover and click sounds to the buttons in the robot page
        $('#console .event .field_name[data-click-tooltip],'+
            '#console .event .skill_name[data-click-tooltip],'+
            '#console .event .ability_name[data-click-tooltip],'+
            '#console .event .record_name[data-click-tooltip]', thisContext).live('mouseenter', function(){
            //console.log('hovering over database robot icon');
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });

        }

    // -- PRIMARY SCRIPT FUNCTIONALITY -- //

    // Fade in the leaderboard screen slowly
    thisBody.waitForImages(function(){
        var tempTimeout = setTimeout(function(){
            if (gameSettings.fadeIn){ thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
            else { thisBody.removeClass('hidden').css({opacity:1}); }
            // Let the parent window know the menu has loaded
            parent.prototype_menu_loaded();
            }, 1000);
        }, false, true);

    // Append the canvas and console markup to the body now that we're ready
    if (gameSettings.databaseCanvasMarkup){ gameCanvas.append(gameSettings.databaseCanvasMarkup); }
    if (gameSettings.databaseConsoleMarkup){ gameConsole.append(gameSettings.databaseConsoleMarkup); }

    // Create the click event for canvas sprites
    $('.sprite_robot[data-token]', gameCanvas).live('click', function(){

        var dataSprite = $(this);
        var dataParent = dataSprite.closest('.wrapper');

        var dataToken = dataSprite.attr('data-token');
        var dataSelect = dataParent.attr('data-select');
        var dataSelectorCurrent = '#'+dataSelect+' .event_visible';
        var dataSelectorNext = '#'+dataSelect+' .event[data-token='+dataToken+']';

        var isAlreadyCurrent = dataSprite.hasClass('sprite_robot_current') ? true : false;
        $('.sprite_robot_current', gameCanvas).removeClass('sprite_robot_current');
        dataSprite.addClass('sprite_robot_current');
        dataParent.css({display:'block'});

        // Check if there is already robot event data on-screen, and either fade it out or skip to the new one
        if ($(dataSelectorCurrent, gameConsole).length && !isAlreadyCurrent){

            // Fade out the current visible events before manually removing them from view
            $(dataSelectorCurrent, gameConsole).stop().animate({opacity:0},250,'swing',function(){
                // Remove the visible class, add the hidden one, then reset the opacity to 1
                $(this).removeClass('event_visible').addClass('event_hidden').css({opacity:1});
                // Fade the new robot data into view by setting opacity to zero, switching classes, then animating back to 1
                $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
                });

            } else {

                // Fade the new robot data into view by setting opacity to zero, switching classes, then animating back to 1
                $(dataSelectorNext, gameConsole).removeClass('event_hidden').addClass('event_visible').css({opacity:1});

            }

        // Save the selected robot to local storage instead of pinging the server
        localStorage.setItem('mmrpgDatabase_lastSelectedRobot', dataToken);

        });
    // Trigger a click on the first robot
    //$('.sprite_robot[data-token]:first-child', gameCanvas).trigger('click');

    // Create the click event for canvas game links
    $('.game_link[data-game]', gameCanvas).live('click', function(e){
        // Collect references to the link object and properties
        e.preventDefault();
        var dataLink = $(this);
        var dataGame = dataLink.attr('data-game');
        // Remove the active link from the other link and add it to this one
        $('.game_link[data-game!='+dataGame+']', gameCanvas).removeClass('game_link_active');
        $('.game_link[data-game='+dataGame+']', gameCanvas).addClass('game_link_active');
        // Hide all robot links that are not from the selected game and show the ones that are
        $('.sprite_robot[data-game!='+dataGame+']', gameCanvas).addClass('sprite_robot_hidden');
        $('.sprite_robot[data-game='+dataGame+']', gameCanvas).removeClass('sprite_robot_hidden');
        // Count the number of master and mecha robots currently visible
        var visibleRobots = $('.sprite', gameCanvas).not('.sprite_robot_hidden');
        var visibleRobotsCount = visibleRobots.length;
        var visibleRobotMasters = visibleRobots.filter('.sprite_robot[data-kind=master]').length;
        var visibleRobotMechas = visibleRobots.filter('.sprite_robot[data-kind=mecha]').length;
        var visibleRobotBosses = visibleRobots.filter('.sprite_robot[data-kind=boss]').length;
        //console.log('Switched to '+dataGame+'! Total = '+visibleRobotsCount+'; Robot Masters = '+visibleRobotMasters+'; Mecha Support = '+visibleRobotMechas);
        // Hide or show the robot master container based on count
        if (visibleRobotMasters > 0){ $('.wrapper_header_masters, .wrapper_robots_masters', gameCanvas).css({display:'block'}); }
        else { $('.wrapper_header_masters, .wrapper_robots_masters', gameCanvas).css({display:'none'}); }
        // Hide or show the robot mecha container based on count
        if (visibleRobotMechas > 0){ $('.wrapper_header_mechas, .wrapper_robots_mechas', gameCanvas).css({display:'block'}); }
        else { $('.wrapper_header_mechas, .wrapper_robots_mechas', gameCanvas).css({display:'none'}); }
        // Hide or show the robot boss container based on count
        if (visibleRobotBosses > 0){ $('.wrapper_header_bosses, .wrapper_robots_bosses', gameCanvas).css({display:'block'}); }
        else { $('.wrapper_header_bosses, .wrapper_robots_bosses', gameCanvas).css({display:'none'}); }
        // Auto-click the first visible robot sprite in the canvas
        if (gameSettings.firstRobot !== false){
            var firstVisibleSprite = $('.sprite_robot[data-token='+gameSettings.firstRobot+']', gameCanvas);
            gameSettings.firstRobot = false;
            } else {
            var firstVisibleSprite = $('.sprite_robot[data-token][data-game='+dataGame+']', gameCanvas).first();
            }
        //console.log(firstVisibleSprite.text());
        firstVisibleSprite.triggerSilentClick();
        // Save the selected page/game tab to local storage instead of pinging the server
        localStorage.setItem('mmrpgDatabase_lastSelectedGame', dataGame);
        // Return true on succes
        return true;
        });

    // Check local storage for previous selections
    var lastSelectedGame = localStorage.getItem('mmrpgDatabase_lastSelectedGame');
    var lastSelectedRobot = localStorage.getItem('mmrpgDatabase_lastSelectedRobot');
    // Override the PHP-injected first robot if we have one in local storage
    if (lastSelectedRobot){
        gameSettings.firstRobot = lastSelectedRobot;
        // Instantly swap the console visibility classes to prevent the animation flash
        $('#console .event_visible').removeClass('event_visible').addClass('event_hidden');
        $('#console .event[data-token="' + lastSelectedRobot + '"]').removeClass('event_hidden').addClass('event_visible').css({opacity: 1});
        // Pre-apply the active class to the canvas sprite so the click handler skips the crossfade
        $('.sprite_robot[data-token="' + lastSelectedRobot + '"]', gameCanvas).addClass('sprite_robot_current');
        }
    // Find the correct game link to click based on local storage, falling back to defaults
    var tempFirstLink = false;
    if (lastSelectedGame){ tempFirstLink = $('.game_link[data-game="' + lastSelectedGame + '"]', gameCanvas); }
    // If no valid link was found in storage, fallback to the active class or the very first link
    if (!tempFirstLink || !tempFirstLink.length){
        if ($('.game_link_active[data-game]', gameCanvas).length){ tempFirstLink = $('.game_link_active[data-game]', gameCanvas); }
        else { tempFirstLink = $('.game_link[data-game]', gameCanvas).first(); }
        }
    // Trigger the initial click to load the tab and its robot
    if (tempFirstLink.length){ tempFirstLink.triggerSilentClick(); }

    // Create the click event for the back button
    $('a.back', gameCanvas).click(function(e){
        e.preventDefault();
        window.location = 'prototype.php';
        });

    // Attach resize events to the window
    thisWindow.resize(function(){ windowResizeFrame(); });
    setTimeout(function(){ windowResizeFrame(); }, 1000);
    windowResizeFrame();

    var windowHeight = $(window).height();
    var htmlHeight = $('html').height();
    var htmlScroll = $('html').scrollTop();
    //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

    // -- DATABASE SPRITE SHOWCASE -- //

    // Create a reference to all the sprite showcase containers
    var $spriteShowcases = $('.sprite_showcase', gameConsole);
    if ($spriteShowcases.length){
        //console.log('found '+$spriteShowcases.length+' sprite showcases!');

        // Define a function to call when we want to update a sprite showcase's frame (background offset)
        var updateSpriteFrame = function($showcase, frameKey){
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $showcaseButtons = $('.sprite_showcase_buttons', $showcaseParent);
            var $showcaseSprites = $('.sprite .sprite', $showcase);
            var dataToken = $showcaseParent.attr('data-token');
            var dataFrame = $('.frame[data-frame-key='+frameKey+']', $showcaseButtons).attr('data-frame');
            var dataImageSize = parseInt($showcase.attr('data-image-size'));
            var backgroundOffset = (frameKey * dataImageSize) * -1;
            var backgroundPosition = backgroundOffset+'px 0';
            $showcaseSprites.css({backgroundPosition:backgroundPosition});
            $('.frame', $showcaseButtons).removeClass('active');
            $('.frame[data-frame-key='+frameKey+']', $showcaseButtons).addClass('active');
            };

        // Define a function to call when we want to update a sprite showcase's alt (background image)
        var updateSpriteAlt = function($showcase, newImageToken){
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $showcaseButtons = $('.sprite_showcase_buttons', $showcaseParent);
            var $showcaseSprites = $('.sprite .sprite', $showcase);
            var dataToken = $showcaseParent.attr('data-token');
            var dataImage = $showcase.attr('data-image');
            var dataBaseImage = $showcase.attr('data-base-image') || dataImage;
            if (!$showcase.is('[data-base-image]')){ $showcase.attr('data-base-image', dataBaseImage); }
            var dataImageSize = parseInt($showcase.attr('data-image-size'));
            var dataImageSizeToken = dataImageSize+'x'+dataImageSize;
            //var oldBackgroundImage = $showcaseSprites.css('backgroundImage');
            //var newBackgroundImage = oldBackgroundImage.replace(dataImage, newImageToken);
            //console.log({oldBackgroundImage:oldBackgroundImage,newBackgroundImage:newBackgroundImage});
            var newBackgroundImage = 'images/robots/'+newImageToken+'/sprite_left_'+dataImageSizeToken+'.png?'+gameSettings.cacheTime;
            //console.log({newBackgroundImage:newBackgroundImage});
            $showcase.attr('data-image', newImageToken);
            $showcaseSprites.css({backgroundImage:'url('+newBackgroundImage+')'});
            };

        // Loop through each showcase and assign events
        $spriteShowcases.each(function(){
            var $showcase = $(this);
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $showcaseButtons = $('.sprite_showcase_buttons', $showcaseParent);
            var $showcaseSprites = $('.sprite .sprite', $showcase);
            //console.log({$showcase:$showcase,$showcaseParent:$showcaseParent,$showcaseButtons:$showcaseButtons});
            var dataToken = $showcaseParent.attr('data-token');
            //console.log('assigning events for '+dataToken+'!');
            $('.frame', $showcaseButtons).bind('mouseenter click', function(e){
                var dataFrame = $(this).attr('data-frame');
                var dataFrameKey = parseInt($(this).attr('data-frame-key'));
                //console.log('mouseenter/click for '+dataToken+'! dataFrame = '+dataFrame+'; dataFrameKey = '+dataFrameKey+';');
                updateSpriteFrame($showcase, dataFrameKey);
                e.stopPropagation();
                });
            $showcaseButtons.bind('mouseleave', function(e){
                //console.log('mouseleave for '+dataToken+'!');
                var dataFrame = 'base';
                var dataFrameKey = 0;
                updateSpriteFrame($showcase, dataFrameKey);
                e.stopPropagation();
                });
            });

        // Make sure we update the showcase images whenever a new alt is selected
        if ($spriteShowcases.length === 1){
            var $showcase = $spriteShowcases.first();
            var $showcaseParent = $showcase.closest('.event.has_sprite_showcase');
            var $spriteHeader = $('.header#sprites', $showcaseParent);
            var $spriteImageOptions = $spriteHeader.length ? $('.images a[data-image]', $spriteHeader) : [];
            if ($spriteImageOptions.length){
                //console.log('found '+$spriteHeader.length+' sprite headers!');
                //console.log('found '+$spriteImageOptions.length+' sprite header images!');
                $spriteImageOptions.each(function(){
                    var $option = $(this);
                    var dataImage = $option.attr('data-image');
                    $option.click(function(e){
                        e.preventDefault();
                        updateSpriteAlt($showcase, dataImage);
                        });
                    });
                }
            }

        }

    // Sync mouse hovering with keyboard/gamepad navigation
    $('.sprite_robot[data-token]', gameCanvas).live('mouseenter', function(){
        $('.sprite_robot[data-token]', gameCanvas).removeClass('hovered');
        $(this).addClass('hovered');
        });
    $('#canvas', thisPrototype).live('mouseleave', function(){
        var $allRobots = $('.sprite_robot:visible:not(.sprite_robot_hidden)', $(this));
        $allRobots.removeClass('hovered');
        $allRobots.filter('.sprite_robot_current').addClass('hovered');
        });

});

// Create the windowResize event for this page
function windowResizeFrame(){

    var windowWidth = thisWindow.width();
    var windowHeight = thisWindow.height();
    var headerHeight = $('.header', thisBody).outerHeight(true);

    var newBodyHeight = windowHeight;
    var newFrameHeight = newBodyHeight - headerHeight;

    if (windowWidth > 800){ thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
    else { thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

    thisBody.css({height:newBodyHeight+'px'});
    thisPrototype.css({height:newBodyHeight+'px'});

    //console.log('windowWidth = '+windowWidth+'; parentWidth = '+parentWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}

// Define a database-specific function to call when polling user input variables
function checkUserInputsForDatabaseFrame(kind, event, activeInputs, userInputs){
    let _self = this;
    let $thisPrototype = $mmrpgElements.thisPrototype;
    let playSoundEffect = mmrpgPrototype.playSoundEffect;

    let $databaseCanvas = $('#canvas', $thisPrototype);
    if (!$databaseCanvas.is(':visible')){ return; }

    let $gameLinks = $('#robot_games .game_link', $databaseCanvas);

    // Grab all visible robots, explicitly excluding the ones hidden by the active game tab
    let $visibleRobots = $('.wrapper_robots:visible .sprite_robot:not(.sprite_robot_hidden)', $databaseCanvas);

    // Find the currently hovered robot, or default to the active (current) one
    let $activeRobot = $visibleRobots.filter('.sprite_robot_current');
    let $hoveredRobot = $visibleRobots.filter('.hovered');
    if (!$hoveredRobot.length) { $hoveredRobot = $activeRobot; }
    if (!$hoveredRobot.length) { $hoveredRobot = $visibleRobots.first(); }
    if (!$hoveredRobot.length) { return; }

    let currentIndex = $visibleRobots.index($hoveredRobot);

    // -- L2/R2 BUTTONS: SWITCH GAME PAGES -- //
    if (activeInputs.L2 || activeInputs.R2){
        if (event){ event.preventDefault(); }
        let shiftDirection = activeInputs.L2 ? -1 : 1;
        let $activeLink = $gameLinks.filter('.game_link_active');
        let linkIndex = $gameLinks.index($activeLink);
        let nextLinkIndex = linkIndex;
        // Loop to find the next available tab that isn't disabled
        let loopCount = 0;
        while(loopCount < $gameLinks.length){
            nextLinkIndex += shiftDirection;
            if (nextLinkIndex >= $gameLinks.length) { nextLinkIndex = 0; }
            else if (nextLinkIndex < 0) { nextLinkIndex = $gameLinks.length - 1; }
            let $nextLink = $gameLinks.eq(nextLinkIndex);
            if (!$nextLink.hasClass('game_link_disabled')){
                $nextLink.trigger('click');
                break;
                }
            loopCount++;
            }
        return;
        }

    // -- DIRECTIONAL BUTTONS: GRID NAVIGATION -- //
    if (activeInputs.Up || activeInputs.Down || activeInputs.Left || activeInputs.Right){
        if (event){ event.preventDefault(); }
        let nextIndex = currentIndex;
        // Calculate columns dynamically
        let columns = 1;
        if ($visibleRobots.length > 0){
            let firstTop = $visibleRobots.eq(0).offset().top;
            for (let i = 1; i < $visibleRobots.length; i++){
                if ($visibleRobots.eq(i).offset().top > firstTop){
                    columns = i;
                    break;
                    }
                }
            }
        if (activeInputs.Left){
            nextIndex = currentIndex - 1;
            if (nextIndex < 0){ nextIndex = $visibleRobots.length - 1; }
            }
        else if (activeInputs.Right){
            nextIndex = currentIndex + 1;
            if (nextIndex >= $visibleRobots.length){ nextIndex = 0; }
            }
        else if (activeInputs.Up){
            nextIndex = currentIndex - columns;
            if (nextIndex < 0){
                while (nextIndex + columns < $visibleRobots.length){
                    nextIndex += columns;
                    }
                }
            }
        else if (activeInputs.Down){
            nextIndex = currentIndex + columns;
            if (nextIndex >= $visibleRobots.length){
                nextIndex = currentIndex % columns;
                }
            }
        let $nextRobot = $visibleRobots.eq(nextIndex);
        if ($nextRobot.length && nextIndex !== currentIndex){
            $visibleRobots.removeClass('hovered');
            $nextRobot.addClass('hovered');
            // Handle scrolling natively within the database canvas
            let containerScrollTop = $databaseCanvas.scrollTop();
            let containerHeight = $databaseCanvas.height();
            let elementTop = $nextRobot.position().top;
            let elementHeight = $nextRobot.outerHeight(true);
            if (elementTop < 0){
                $databaseCanvas.scrollTop(containerScrollTop + elementTop);
                } else if (elementTop + elementHeight > containerHeight){
                $databaseCanvas.scrollTop(containerScrollTop + elementTop + elementHeight - containerHeight);
                }
            playSoundEffect('icon-hover', {volume: 0.5});
        }
        return;
    }

    // -- X BUTTON: CYCLE SPRITE FRAMES -- //
    if (activeInputs.X){
        if (event){ event.preventDefault(); }
        let $visibleEvent = $('#console .event_visible');
        let $showcaseButtons = $('.sprite_showcase_buttons', $visibleEvent);
        if ($showcaseButtons.length){
            let $frames = $('.frame', $showcaseButtons);
            let $activeFrame = $frames.filter('.active');
            let currentIndex = 0; // Default to base frame (index 0)
            if ($activeFrame.length){
                currentIndex = $frames.index($activeFrame);
                }
            // Advance to the next frame, skipping index 0 (base) which is normally hidden/default
            let nextIndex = currentIndex + 1;
            if (nextIndex >= $frames.length){
                nextIndex = 1;
                }
            // Trigger the existing mouseenter event to activate the frame
            $frames.eq(nextIndex).trigger('mouseenter');
            playSoundEffect('icon-hover', {volume: 0.5});
            // Clear any existing timeout to prevent the reset from firing too early if mashing X
            if ($showcaseButtons.data('x-timeout')){
                clearTimeout($showcaseButtons.data('x-timeout'));
                }
            // Set a new timeout to revert to the base sprite after 1 second by triggering your existing mouseleave event
            let timeoutId = setTimeout(function(){
                $showcaseButtons.trigger('mouseleave');
                }, 1000);
            $showcaseButtons.data('x-timeout', timeoutId);
            }
        return;
        }

    // -- A BUTTON: SELECT HIGHLIGHTED ROBOT -- //
    if (activeInputs.A){
        if (event){ event.preventDefault(); }
        // Click the hovered robot if it isn't already the selected one
        if ($hoveredRobot.length && !$hoveredRobot.hasClass('sprite_robot_current')){
            $hoveredRobot.trigger('click');
            }
        return;
        }

}

