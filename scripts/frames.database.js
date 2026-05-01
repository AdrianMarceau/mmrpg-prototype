
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

        // Update the session variable with the current page link number
        $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,current_database_robot_token,'+dataToken});

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
        // Update the session variable with the current page link number
        $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,current_database_page_key,'+dataGame});
        // Return true on succes
        return true;
        });
    // Click the first game link, whatever it is
    if ($('.game_link_active[data-game]', gameCanvas).length){ var tempFirstLink = $('.game_link_active[data-game]', gameCanvas); }
    else { var tempFirstLink = $('.game_link[data-game]', gameCanvas).first(); }
    tempFirstLink.triggerSilentClick();

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
