// Create the globale context variables
var thisBody = false;
var thisPrototype = false;
var thisFalloff = false;
var thisWindow = false;
var thisReadyRoom = false;

// Create the prototype battle options object
gameSettings.fadeIn = false;
gameSettings.totalRobotLimit = 8;
gameSettings.totalMissionsComplete = 1;
gameSettings.totalPlayerOptions = 1;
gameSettings.totalRobotOptions = 1;
gameSettings.nextRobotLimit = 8;
gameSettings.nextStepName = 'home';
gameSettings.nextSlideDirection = 'left';
gameSettings.startLink = 'home';
gameSettings.skipPlayerSelect = false;
gameSettings.menuFramesSeen = [];
gameSettings.readyRoomUnlocked = false;
gameSettings.readyRoomActive = false;
var battleOptions = {};

// Define the perfect scrollbar settings
var thisScrollbarSettings = {wheelSpeed:0.3};

// When the document is ready, assign events
$(document).ready(function(){

    // Update the global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisFalloff = $('#falloff', thisBody);
    thisWindow = $(window);
    thisReadyRoom = typeof window.mmrpgReadyRoom !== 'undefined' ? window.mmrpgReadyRoom : false;

    // If we're not in an iframe, adjust background
    if (window.top == window.self){
        $('html').css({backgroundColor:'#262626'});
        }

    // Define the prototype context
    var thisContext = $('#prototype');
    if (thisContext.length){

        // Define the window resize event so we can adapt to changes
        thisWindow.resize(function(){ windowResizePrototype(); });
        setTimeout(function(){ windowResizePrototype(); }, 2000);
        //$('.banner .link', thisPrototype).live('click', function(){ windowResizePrototype(); });

        // -- SOUND EFFECT FUNCTIONALITY -- //

        // Define some interaction sound effects for the prototype main menu
        var playSoundEffect = function(){};
        if (typeof top.mmrpg_play_sound_effect !== 'undefined'){

            // Define a quick local function for routing sound effect plays to the parent
            playSoundEffect = function(soundName, options){
                if (this instanceof jQuery || this instanceof Element){
                    if ($(this).data('silentClick')){ return; }
                    if ($(this).is('.disabled')){ return; }
                    if ($(this).is('.option_disabled')){ return; }
                    }
                top.mmrpg_play_sound_effect(soundName, options);
                };

            // HOME LINKS

            // Add hover and click sounds to the links in the main menu
            $('.banner .options_fullmenu .link', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'link-hover');
                });
            $('.banner .options_fullmenu .link', thisContext).live('click', function(){
                if ($(this).is('.link_home')){ playSoundEffect.call(this, 'link-click-special'); }
                else { playSoundEffect.call(this, 'link-click'); }
                });

            // Add hover and click sounds to the user config button in the main menu
            $('.banner .options_userinfo[data-step]', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'link-hover');
                });
            $('.banner .options_userinfo[data-step]', thisContext).live('click', function(){
                playSoundEffect.call(this, 'link-click');
                });

            // Add hover and click sounds to the leaderboard button in the main menu
            $('.banner .points[data-step]', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'link-hover');
                });
            $('.banner .points[data-step]', thisContext).live('click', function(){
                playSoundEffect.call(this, 'link-click');
                });

            // CHAPTER SELECT

            // Add hover and click sounds to the chapter select buttons
            $('.menu .chapter_select .chapter_link', thisContext).live('mouseenter', function(){
                if ($(this).is('.chapter_link_disabled')){ return; }
                playSoundEffect.call(this, 'icon-hover');
                });
            $('.menu .chapter_select .chapter_link', thisContext).live('click', function(){
                if ($(this).is('.chapter_link_disabled')){ return; }
                playSoundEffect.call(this, 'link-click');
                });

            // PLAYER SELECT

            // Add hover and click sounds to the player select buttons
            $('.menu .option_this-player-select', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'link-hover');
                });
            $('.menu .option_this-player-select', thisContext).live('click', function(){
                playSoundEffect.call(this, 'lets-go');
                });

            // MISSION SELECT (BATTLE SELECT)

            // Add hover and click sounds to the battle select buttons
            $('.menu .option_this-battle-select', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'link-hover');
                });
            $('.menu .option_this-battle-select', thisContext).live('click', function(){
                playSoundEffect.call(this, 'lets-go');
                });

            // ROBOT SELECT

            // Add hover and click sounds to the robot select buttons
            $('.menu .option_this-robot-select', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'icon-hover');
                });
            $('.menu .option_this-robot-select', thisContext).live('click', function(){
                playSoundEffect.call(this, 'link-click-robot');
                });
            $('.menu .option_this-team-select', thisContext).live('click', function(){
                playSoundEffect.call(this, 'lets-go-robots');
                });

            // BACK BUTTON(s)

            // Add hover and click sounds to any back buttons
            $('.menu .option_back', thisContext).live('mouseenter', function(){
                playSoundEffect.call(this, 'back-hover');
                });
            $('.menu .option_back', thisContext).live('click', function(){
                playSoundEffect.call(this, 'back-click');
                setTimeout(function(){
                    playSoundEffect.call(this, 'back-click-loading');
                    }, 800);
                });

            // READY ROOM GLASS

            // Add a click sound to the ready-room glass if it exists
            $('.ready_room .wrapper.inner > .clicker', thisContext).live('click', function(){
                //console.log('testing 123');
                playSoundEffect.call(this, 'glass-klink');
                });


            }


        // -- MENU FUNCTIONALITY -- //

        $('.option_wrapper', thisContext).scroll(function(e){
            var scrollTop = $(this).scrollTop();
            var wrapperHeight = $(this).height();
            var scrollHeight = scrollTop + wrapperHeight;
            var contentHeight = parseInt($(this).attr('data-content'));
            if (scrollHeight >= contentHeight){ e.preventDefault(); return false; }
            windowResizePrototype();
            });

        // Define the chapter message click events for space-saving
        /*$('.option_message', thisContext).live('click', function(){
            // Create reference to the key elements
            var thisMessage = $(this);
            // Check if this message is already collapsed or not
            var thisCollapsed = thisMessage.hasClass('option_message_collapsed') ? true : false;
            if (!thisCollapsed){
                // This message has not been collapsed yet, so let's do so now
                //console.log('not collapsed, hiding content now');
                thisMessage.addClass('option_message_collapsed');
                var nextButton = thisMessage.next('.option');
                while (nextButton.length && !nextButton.hasClass('option_message') && !nextButton.hasClass('option_spacer')){
                    nextButton.css({display:'none'});
                    nextButton = nextButton.next('.option');
                    }
                } else {
                // This message was already collapsed when clicked, so let's open it
                //console.log('already collapsed, showing content now');
                thisMessage.removeClass('option_message_collapsed');
                var nextButton = thisMessage.next('.option');
                while (nextButton.length && !nextButton.hasClass('option_message') && !nextButton.hasClass('option_spacer')){
                    nextButton.css({display:''});
                    nextButton = nextButton.next('.option');
                    }
                }
            });*/

        // Define the action for the page link
        $('.banner .link[data-href]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the prototype redirect function
            prototype_trigger_redirect(thisContext, this);
            });

        // Create click events for the battle redirect links
        $('a[data-redirect]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the prototype redirect function
            prototype_trigger_redirect(thisContext, this);
            });

        // Define the action for the banner step links
        $('.banner .link[data-step]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the prototype step function
            prototype_menu_click_step(thisContext, this);
            });

        // Define the action for the banner step divs
        $('.banner div[data-step]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the prototype step function
            prototype_menu_click_step(thisContext, this);
            });

        // Define the confirmation event for the exit action
        $('.banner .link_exit', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the prototype exit function
            prototype_trigger_exit(thisContext, this);
            });

        // Define the confirmation event for the reset action
        $('.banner .link_reset', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the game reset function
            mmrpg_trigger_reset();
            });

        // Load content into any requested elements
        $('.menu[data-source]', thisContext).each(function(){
            // Trigger the prototype source function
            prototype_menu_preload_source(thisContext, this);
            });

        // Define the click event for menu items that require a reload
        $('.menu a[data-reload="true"]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Collect the parent variables
            var thisParentMenu = $(this).parents('.menu');
            var thisParentStep = thisParentMenu.attr('data-step');
            var thisParentSelect = thisParentMenu.attr('data-select');

            //console.log('thisParentStep = '+thisParentStep+' | thisParentSelect = '+thisParentSelect+' | ');

            // Trigger an ajax call for the appropriate markup
            $.ajax({
                url: 'scripts/prototype.php',
                data: {step:thisParentStep,select:thisParentSelect},
                success: function(markup, status){
                    alert('success:'+markup);
                    },
                error: function(markup, status){
                    alert('error:'+markup);
                    }
                });


            });

        // If this script is being loaded from within an iframe, let the parent prototype window know
        if (gameSettings.allowEditing){
            if (thisBody.hasClass('iframe')){
                var frameToken = thisBody.is('[data-frame]') ? thisBody.attr('data-frame') : 'unknown';
                //top.console.log('we have loaded '+frameToken+' from within an iframe');
                if (typeof parent.prototype_menu_frame_seen !== 'undefined'){ parent.prototype_menu_frame_seen(frameToken); }
                if (typeof parent.prototype_menu_links_refresh !== 'undefined'){ parent.prototype_menu_links_refresh(); }
                }
            // Otherwise, we can immediately trigger the menu links refresh
            else {
                prototype_menu_frame_seen('home');
                prototype_menu_links_refresh();
                }
            }

        // Define the click event for the chapter select menu links
        var thisChapterSelects = $('.option_wrapper_missions .chapter_select', thisContext);
        if (thisChapterSelects.length){
            // Attach a live click event to the chapter link buttons
            $('.chapter_link[data-chapter]', thisChapterSelects).live('click', function(e){
                // Prevent the default action of clicking
                e.preventDefault();
                // Collect the variables for this click calculation
                var thisChapterLink = $(this);
                var thisChapterSelect = thisChapterLink.parent();
                var thisChapterWrapper = thisChapterSelect.parent();
                var thisChapterPlayer = thisChapterSelect.attr('data-player');
                var thisChapterToken = thisChapterLink.attr('data-chapter');
                // Make this chapter link the active one
                $('.chapter_link_active', thisChapterSelect).removeClass('chapter_link_active');
                thisChapterLink.addClass('chapter_link_active');
                // Hide all the other chapter links from view
                $('.option[data-chapter!="'+thisChapterToken+'"]', thisChapterWrapper).addClass('hidden_chapter_option');
                $('.option[data-chapter="'+thisChapterToken+'"]', thisChapterWrapper).removeClass('hidden_chapter_option');
                // If there was music requested, start playing it
                var chapterMusic = typeof thisChapterLink.attr('data-music') !== 'undefined' ? thisChapterLink.attr('data-music') : false;
                if (chapterMusic.length){ parent.mmrpg_music_load(chapterMusic, false); }
                // Update the battle options with the last selected chapter
                $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,'+thisChapterPlayer+'_current_chapter,'+thisChapterToken});
                // DEBUG DEBUG
                //alert('clicked chapter '+thisChapterToken+'!');
                });
            // Check to see which one should be displayed first and autoclick it
            if ($('.chapter_link_active', thisChapterSelects).length){ var firstChapterLink = $('.chapter_link_active', thisChapterSelects); }
            else { var firstChapterLink = $('.chapter_link[data-chapter]:first-child', thisChapterSelects); }
            firstChapterLink.triggerSilentClick();
            }



        // Create the click events for the prototype menu option buttons
        $('.option[data-token]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            //alert('option clicked!');
            // If we're already loading another option click, return false
            if (typeof gameSettings.customValues.optionClicked !== 'undefined'){ return false; }
            // If this option is in the banner, return false
            if ($(this).parents('.banner').length == 1){ return false; }
            // Check if this option has been disabled or not
            if ($(this).hasClass('option_disabled')){
                if (!$(this).hasClass('option_disabled_clickable')){
                    return false;
                }
            }
            // Set a timeout just in case something weird happens
            gameSettings.customValues.optionClicked = setTimeout(function(){
                //console.log('setTimeout triggered for optionClicked');
                clearTimeout(gameSettings.customValues.optionClicked);
                delete gameSettings.customValues.optionClicked;
                }, 1000);
            // Trigger the prototype option function
            prototype_menu_click_option(thisContext, this, function(){
                //console.log('onComplete triggered for optionClicked');
                clearTimeout(gameSettings.customValues.optionClicked);
                delete gameSettings.customValues.optionClicked;
                });
            });

        // Create the click events for the prototype menu back button
        $('.option[data-back]', thisContext).live('click', function(e){
            // Prevent the default click action
            e.preventDefault();
            // Trigger the prototype back function
            prototype_menu_click_back(thisContext, this);
            });

        // Check if the player token has already been selected
        // and collect any data attributes for styling
        //console.log('first load of prototype > predefining selections and data attributes');
        //console.log('kanto');
        var dataStepName = 'home';
        var dataStepNumber = 1;
        if (typeof battleOptions['this_player_token'] !== 'undefined'
            && battleOptions['this_player_token'].length){

            //alert('player selected : '+battleOptions['this_player_token']);
            gameSettings.skipPlayerSelect = true;
            var thisMenu = $('.menu[data-select="this_player_token"]', thisContext);
            $('.option[data-token="'+battleOptions['this_player_token']+'"]', thisMenu).triggerSilentClick();

            if (dataStepNumber === 1){ dataStepNumber = 2; }

            }

        // Update the prototype element with the data attributes for styling
        thisPrototype.attr('data-step-name', dataStepName);
        thisPrototype.attr('data-step-number', dataStepNumber);
        //console.log('dataStepName =>', typeof dataStepName, dataStepName);
        //console.log('dataStepNumber =>', typeof dataStepNumber, dataStepNumber);

        // Attempt to define the top frame
        var topFrame = window.top;
        if (typeof topFrame.myFunction != 'function'){ topFrame = window.parent; }

        // Define the a fadein function for the page
        var thisFadeCallback = function(){
            //console.log('thisFadeCallback()');
            // Fade in the prototype screen slowly if allowed
            if (gameSettings.fadeIn == true){
                //alert('gameSettings.fadeIn == true? '+(gameSettings.fadeIn ? 'true' : 'false'));
                thisContext.waitForImages(function(){
                    var tempTimeout = setTimeout(function(){
                        thisContext.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing');
                        windowResizePrototype();
                        topFrame.mmrpg_toggle_index_loaded(true);
                        gameSettings.startLink = 'home';
                        if ((gameSettings.windowEventsCanvas != undefined && gameSettings.windowEventsCanvas.length)
                            || (gameSettings.windowEventsMessages != undefined && gameSettings.windowEventsMessages.length)){
                            //topFrame.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
                            //console.log('trying to topFrame.windowEventsPull() [A]');
                            topFrame.windowEventsPull();
                            }
                        }, 1000);
                    }, false, true);
                } else {
                //alert('gameSettings.fadeIn == false');
                // Trigger the prototype step function if not home
                thisContext.css({opacity:1}).removeClass('hidden');
                windowResizePrototype();
                topFrame.mmrpg_toggle_index_loaded(true);
                gameSettings.startLink = 'home';
                if ((gameSettings.windowEventsCanvas != undefined && gameSettings.windowEventsCanvas.length)
                    || (gameSettings.windowEventsMessages != undefined && gameSettings.windowEventsMessages.length)){
                    //topFrame.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
                    //console.log('trying to topFrame.windowEventsPull() [B]');
                    topFrame.windowEventsPull();
                    }
                }
            };

        /*
        // Define the event for the password prompt's click unlock sequence
        $('.banner .sprite_player', thisPrototype).live('click', function(){
            gameSettings.passwordUnlocked++;
            //console.log('counter = '+gameSettings.passwordUnlocked);
            if (gameSettings.passwordUnlocked >= 5){
                //console.log('omg you unlocked me!');
                var thisToken = $(this).html().toLowerCase();
                //thisToken = thisToken.replace('.', '-');
                thisToken = thisToken.replace(/[^-_a-z0-9]+/ig, '');
                if (thisToken == 'drlight'){ var thisPromptText = 'Oh, hello there! What can I help you with today?'; }
                else if (thisToken == 'drwily'){ var thisPromptText = 'Eh? You want something from me?'; }
                else if (thisToken == 'drcossack'){ var thisPromptText = 'Greetings.  How can I assist you today?'; }
                var thisPassword = prompt(thisPromptText);
                if (thisPassword != undefined && thisPassword.length){
                    thisPassword = thisPassword.toLowerCase().replace(/[^a-z0-9]+/ig, '');
                    //alert(thisToken+':'+thisPassword);
                    // Update the session with this password string
                    var thisRequestType = 'session';
                    var thisRequestData = 'flags,'+thisToken+'_password_'+thisPassword+',true';
                    $.post('scripts/script.php',{requestType:'session',requestData:thisRequestData},function(){
                        window.location.href = 'prototype.php?wap='+(gameSettings.wapFlag ? 'true' : 'false');
                        });
                    }
                }
            });
        */

        // Define the live Rogue Star ticker functionality if present
        var $rogueStar = $('.banner .rogue_star', thisPrototype);
        if ($rogueStar.length){

            // Collect the details of this rogue star
            var rogueStar = {};
            rogueStar.type = $rogueStar.attr('data-star-type');
            rogueStar.name = rogueStar.type.charAt(0).toUpperCase() + rogueStar.type.slice(1);
            rogueStar.fromDate = $rogueStar.attr('data-from-date');
            rogueStar.fromDateTime = $rogueStar.attr('data-from-date-time');
            rogueStar.toDate = $rogueStar.attr('data-to-date');
            rogueStar.toDateTime = $rogueStar.attr('data-to-date-time');
            rogueStar.power = parseInt($rogueStar.attr('data-star-power'));
            rogueStar.unixFromTime = Date.parse(rogueStar.fromDate+'T'+rogueStar.fromDateTime) / 1000;
            rogueStar.unixToTime = Date.parse(rogueStar.toDate+'T'+rogueStar.toDateTime) / 1000;
            rogueStar.unixDuration = rogueStar.unixToTime - rogueStar.unixFromTime;
            //console.log('A rogue star is in orbit! \n', rogueStar);

            // Define a function for refreshing the star's postition and text
            var refreshRogueStarInterval = false;
            var refreshRogueStar = function(){
                //console.log('refreshRogueStar()');
                var nowTime = Date.now() / 1000;
                var starTimeDuration = rogueStar.unixToTime - rogueStar.unixFromTime;
                var starTimeElapsed = nowTime - rogueStar.unixFromTime;
                var starTimeElapsedPercent = (starTimeElapsed / starTimeDuration) * 100;
                var starTimeRemaining = starTimeDuration - starTimeElapsed;
                var starPositionRight = (100 - starTimeElapsedPercent) + 1;
                var starMinutesLeft = (starTimeRemaining / 60);
                var starHoursLeft = (starMinutesLeft / 60);
                //console.log('Checking the star details... \n', {nowTime:nowTime, starTimeDuration:starTimeDuration, starTimeElapsed:starTimeElapsed, starTimeElapsedPercent:starTimeElapsedPercent, starTimeRemaining:starTimeRemaining, starPositionRight:starPositionRight, starMinutesLeft:starMinutesLeft, starHoursLeft:starHoursLeft});

                // If the star is still available, update the sprite, else remove it entirely
                if (starTimeRemaining > 0){
                    //console.log('The rogue star is refreshing! It\'s moved along a little!');
                    var starTooltip = '&raquo; Rogue Star Event! &laquo; || A ' + rogueStar.name + '-type Rogue Star has appeared! This star grants +' + rogueStar.power + ' ' + rogueStar.name + '-type Starforce for a limited time. Take advantage of its power before it\'s gone! ';
                    if (starHoursLeft >= 1){ starTooltip += 'You have less than ' + (starHoursLeft > 1 ? Math.ceil(starHoursLeft) + ' hours' : '1 hour') + ' remaining! '; }
                    else if (starHoursLeft < 1){ starTooltip += 'You have only ' + (starMinutesLeft > 1 ? Math.ceil(starMinutesLeft) + ' minutes' : '1 minute') + ' remaining! ';  }
                    $rogueStar.attr('data-tooltip', starTooltip);
                    $rogueStar.find('.trail').css({right:starPositionRight+'%'});
                    $rogueStar.find('.star').css({right:starPositionRight+'%'}).css({right:'calc('+starPositionRight+'% - 20px)'});
                    } else {
                    //console.log('Time is up!  The rogue star has been removed!');
                    if (refreshRogueStarInterval !== false){ clearInterval(refreshRogueStarInterval); }
                    $rogueStar.css({opacity:1}).animate({opacity:0},500,'swing',function(){ $rogueStar.remove(); });
                    return false;
                    }
                };

            // Automatically call the refresh function on an interval timer
            refreshRogueStarInterval = setInterval(refreshRogueStar, 9000);
            refreshRogueStar();

            }

        // Trigger the prototype step function if not home
        if (gameSettings.startLink !== 'home'){
            //gameSettings.skipPlayerSelect = true;
            var thisLink = $('.banner .link[data-step="'+gameSettings.startLink+'"]', thisContext);
            prototype_menu_click_step(thisContext, thisLink, thisFadeCallback, 10); //CHECKPOINT
            //prototype_menu_switch({stepName:gameSettings.startLink,onComplete:thisFadeCallback,slideDuration:600});
            } else {
            //gameSettings.skipPlayerSelect = true;
            thisFadeCallback();
            }

        // Make sure we always poll the server for popup events after loading
        //console.log('queuing the windowEventsPull event (via prototype)');
        if (typeof window.top.mmrpg_queue_for_game_start !== 'undefined'){
            window.top.mmrpg_queue_for_game_start(function(){
                //console.log('i guess the game has started');
                setTimeout(function(){ windowEventsPull(); }, 1000);
                });
            }


        }

    // Reset the animation back to normal
    //gameSettings.skipPlayerSelect = false;

    // -- READY ROOM INIT AND TRANSITIONS -- //

    // If we're on the actual prototype parent frame, load the ready room now
    if (!$('#mmrpg').hasClass('iframe')){
        // Only add the ready room to the banner after the player has unlocked their first homebase
        if (gameSettings.readyRoomUnlocked){

            // Initialize the ready room on prototype home page load
            var $thisPrototype = $('#prototype');
            var $thisBanner = $('.banner', $thisPrototype);
            var playersIndex = typeof gameSettings.customIndex.unlockedPlayersIndex !== 'undefined' ? gameSettings.customIndex.unlockedPlayersIndex : {};
            var robotsIndex = typeof gameSettings.customIndex.unlockedRobotsIndex !== 'undefined' ? gameSettings.customIndex.unlockedRobotsIndex : {};
            thisReadyRoom.preloadCharacterIndex('player', playersIndex);
            thisReadyRoom.preloadCharacterIndex('robot', robotsIndex);
            thisReadyRoom.init($thisBanner, function(){

                // Filter to only the current player if one has been set
                if (typeof battleOptions['this_player_token'] !== 'undefined'
                    && battleOptions['this_player_token'].length){
                    thisReadyRoom.refresh(battleOptions['this_player_token']);
                    }

                // Start the actual ready room animation when it's appropriate to do so
                thisReadyRoom.startAnimation();
                gameSettings.readyRoomActive = true;

                });
            }
        }


    // -- end of document ready markup -- //

});

// Create the windowResize event for this page
function windowResizePrototype(){

    //alert('windowResizePrototype()');

    var windowWidth = thisWindow.width();
    var windowHeight = thisWindow.height();
    var bodyInnerHeight = thisBody.innerHeight();

    //alert('windowWidth = '+windowWidth+' \nwindowHeight = '+windowHeight+' \nbodyInnerHeight = '+bodyInnerHeight);
    //alert('ummmmm');

    if (bodyInnerHeight < windowHeight){ windowHeight = bodyInnerHeight; }

    var bannerHeight = $('.banner', thisBody).outerHeight(true);
    var headerHeight = $('.menu .header', thisPrototype).height() + $('.menu .header', thisPrototype).outerHeight(true);

    var newBodyHeight = windowHeight;
    var newBodyWidth = windowWidth;
    var newFrameHeight = newBodyHeight - bannerHeight;
    var newWrapperHeight = newBodyHeight - bannerHeight - headerHeight;

    thisBody.css({height:newBodyHeight+'px'});
    thisPrototype.css({height:newBodyHeight+'px'});
    thisFalloff.css({width:newBodyWidth+'px'});
    $('iframe', thisPrototype).css({height:newFrameHeight+'px'}).attr('height', newFrameHeight);

}

// Define a function to trigger when resetting data
function mmrpg_trigger_reset(fullReset){
    // Define the confirmation text string
    var fullReset = typeof fullReset !== 'boolean' ? false : true;
    var confirmText = 'Are you sure you want to reset your entire game?\nAll progress will be lost and cannot be restored including any and all unlocked missions, robots, and abilities. Continue?';
    var confirmText2 = 'Let me repeat that one more time.\nIf you reset your game ALL unlocks and progress with be lost. \nEverything. \nReset anyway?';
    // Attempt to confirm with the user of they want to reset
    thisReadyRoom.updateRobot('all', {frame: 'damage'}); // damage
    thisReadyRoom.stopAnimation();
    if (confirm(confirmText) && confirm(confirmText2)){
        // Redirect the user to the prototype reset page
        var postURL = 'prototype.php?action=reset';
        if (fullReset){ postURL += '&full_reset=true'; }
        $.post(postURL, function(){
            //alert('reset complete!');
            thisReadyRoom.updateRobot('all', {frame: 'defeat'}); // defeat
            if (window.self != window.parent){
                window.location = 'prototype.php';
                } else {
                window.location = window.location.href;
                }
            });
        return true;
        } else {
        // Return false
        thisReadyRoom.startAnimation();
        return false;
        }
}

// Define a function to trigger when resetting player missions
function mmrpg_trigger_reset_missions(playerToken, playerName){
    // Define the confirmation text string
    var confirmText = 'Are you sure you want to reset all mission progress in '+playerName+'\'s game file? Unlocked robots and abilities will be untouched, but all completed missions will be reset and cannot be undone. Continue?';
    // Attempt to confirm with the user of they want to resey
    if (navigator.userAgent.match(/Android/i) != null || confirm(confirmText)){
        // Redirect the user to the prototype reset page
        var postURL = 'prototype.php?action=reset-missions&player='+playerToken;
        $.post(postURL, function(){
            window.location = 'prototype.php';
            });
        return true;
        } else {
        // Return false
        return false;
        }
}

// Define a function to trigger when resetting player robots
function mmrpg_trigger_reset_robots(playerToken, playerName){
    // Define the confirmation text string
    var confirmText = 'Are you sure you want to reset all unlocked robots in '+playerName+'\'s game file? All robots will be reset to level one and abilities reset to default. Continue?';
    // Attempt to confirm with the user of they want to resey
    if (navigator.userAgent.match(/Android/i) != null || confirm(confirmText)){
        // Redirect the user to the prototype reset page
        var postURL = 'prototype.php?action=reset-robots&player='+playerToken;
        $.post(postURL, function(){
            window.location = 'prototype.php';
            });
        return true;
        } else {
        // Return false
        return false;
        }
}

// Define a function for triggering the game's exit function
function prototype_trigger_exit(thisContext, thisLink){
    // Define the object references
    var thisLink = $(thisLink);
    // Define the confirmation text string
    var confirmText = 'Are you sure you want to exit your game?';
    // Attempt to confirm with the user of they want to resey
    if (navigator.userAgent.match(/Android/i) != null || confirm(confirmText)){
        // Redirect the user to the prototype reset page
        var postURL = 'prototype.php?action=exit';
        $.post(postURL, function(){
            window.location = 'prototype.php';
            });
        return true;
        } else {
        // Return false
        return false;
        }
}

// Define a function for triggering a prototype redirect link
function prototype_trigger_redirect(thisContext, thisLink){
    var thisLink = $(thisLink);
    var thisHref = thisLink.attr('data-href');
    var thisRedirect = thisLink.attr('data-redirect');
    var thisNewLocation = false;
    if (thisHref.length){ thisNewLocation = thisHref; }
    if (thisRedirect.length){ thisNewLocation = thisRedirect; }
    if (thisNewLocation){
        window.location.href = thisNewLocation;
        return true;
        } else {
        return false;
        }
}

// Define a function for automatically going to the next menu, if defined
function prototype_menu_loaded(){
    //console.log('prototype_menu_loaded()');
    // If the nextMenu value is not empty, switch to the next menu tab
    if (gameSettings.nextStepName.length
            && gameSettings.nextSlideDirection.length){
        // SWITCH TO NEXT MENU
        //console.log('SWITCH TO NEXT MENU '+gameSettings.nextStepName);
        var bannerOverlay = $('.banner_overlay', thisPrototype);
        prototype_menu_switch({stepName:gameSettings.nextStepName,slideDirection:gameSettings.nextSlideDirection,onComplete:function(){
            var animateReadyRoom = true;
            if (animateReadyRoom){
                //console.log('menu '+gameSettings.nextStepName+' has loaded');
                var newRobotFrame = 0;
                if (gameSettings.nextStepName === 'abilities'){ newRobotFrame = 'shoot'; } // shoot
                else if (gameSettings.nextStepName === 'items'){ newRobotFrame = 'summon'; } // summon
                else if (gameSettings.nextStepName === 'shop'){ newRobotFrame = 'throw'; } // throw (money away)
                else if (gameSettings.nextStepName === 'edit_robots'){ newRobotFrame = 'taunt'; } // taunt
                else if (gameSettings.nextStepName === 'edit_players'){ newRobotFrame = 'taunt'; } // taunt
                else if (gameSettings.nextStepName === 'database'){ newRobotFrame = 'base2'; } // base2
                else if (parseInt(gameSettings.nextStepName) > 0){ newRobotFrame = 'victory'; } // victory
                else { newRobotFrame = 'defend'; } // defend
                thisReadyRoom.updateRobot('most', {frame: newRobotFrame});
                }
            gameSettings.nextStepName = false;
            gameSettings.nextSlideDirection = false;
            // Fade out the overlay to prevent clicking other banner links
            $('.banner .points, .banner .subpoints, .banner .options, .banner .tooltip', thisPrototype).stop().animate({opacity:1},500,'swing');
            bannerOverlay.stop().css({opacity:0.33}).animate({opacity:0.0},{duration:1000,easing:'swing',queue:false,complete:function(){
                $(this).addClass('overlay_hidden');
                }});
            }});
        }
}

// Define a function for preloading any menus with source iframes
function prototype_menu_preload_source(thisContext, thisMenu){
    // Collect a reference to the current menu
    var thisMenu = $(thisMenu);
    var thisStep = thisMenu.attr('data-step');
    // Remove any padding on this menu for easier styling
    thisMenu.css({padding:'0'});
    // Calculate the iframe size based on container width
    var thisWidth = '100%';
    var thisHeight = 340;
    // Load the requested content inside this menu
    var thisFrame = $('<iframe name="'+thisStep+'" class="blank" src="blank.php" width="'+thisWidth+'" height="'+thisHeight+'" frameborder="1" scrolling="no"></iframe>');
    thisMenu.empty().append(thisFrame);
}

// Define a function for triggering a prototype step link
function prototype_menu_click_step(thisContext, thisLink, thisCallback, thisSlideDuration){
    //console.log('prototype_menu_click_step(thisContext:', thisContext, ', thisLink:', thisLink, ', thisCallback:', typeof thisCallback, ', thisSlideDuration:', thisSlideDuration, ')');

    // Collect information about the previous and current link
    var thisLink = $(thisLink);
    var currentActive = $('.banner .link_active', thisContext);
    var currentActiveIndex = parseInt(currentActive.attr('data-index'));
    var nextActive = thisLink;
    var nextActiveIndex = parseInt(nextActive.attr('data-index'));
    //var thisStep = $(this).attr('data-step'); //thisMenu.attr('data-step');
    // Return false if clicking self
    if (currentActiveIndex == nextActiveIndex){ return false; }
    // Remove all the other active classes and then make this one active
    $('.banner [data-step].link_active', thisContext).removeClass('link_active');
    thisLink.addClass('link_active');
    // Collect the requested step name
    var stepName = thisLink.attr('data-step');
    var stepMenu = $('.menu[data-step="'+stepName+'"]', thisContext);
    var slideDirection = currentActiveIndex > nextActiveIndex ? 'right' : 'left';
    //console.log('currentActiveIndex =', currentActiveIndex, '| nextActiveIndex =', nextActiveIndex);
    //console.log('slideDirection =', slideDirection);
    // Collect the requested music if set
    var stepMusic = thisLink.attr('data-music') != undefined ? thisLink.attr('data-music') : false;
    var stepSource = thisLink.attr('data-source') != undefined ? thisLink.attr('data-source') : false;

    // Only clear banner options if we're not in demo mode or there's only one player
    if (gameSettings.demo != true && gameSettings.totalPlayerOptions > 1){
        // Clear any select options from the banner
        $('.banner .option[data-select]', thisContext).animate({opacity:0},600,'swing',function(){
            var thisSelect = $(this).attr('data-select');
            $(this).remove();
            var remainingOptions = $('.banner .option', thisContext).length;
            if (remainingOptions < 1){
                $('.banner .is_shifted', thisContext).removeClass('is_shifted').animate({opacity:1.0},600,'swing');
                $('.menu .option_wrapper[data-condition]', thisContext).css({display:''});
                battleOptions[thisSelect] = undefined;
                }
            });
    }

    // If there was music requested, start playing it
    if (stepMusic.length){ parent.mmrpg_music_load(stepMusic, true); }

    // Define the loading switch duringation
    var switchTimeoutDuration = 1000;
    // Determine if this switch has an iframe
    var switchHasIframe = $('iframe', stepMenu).length ? true : false;

    // If there is a source attached to this link, preload it into the appropriate menu
    var hasBlank = $('iframe.blank', stepMenu).length ? true : false;
    if (stepSource.length){

        // Preload the source into the appropriate menu
        stepMenu.empty();
        var timeStamp = Math.round((new Date()).getTime() / 1000);
        stepSource += (stepSource.indexOf('?') != -1 ? '&1=1' : '?1=1')
        stepSource += '&wap='+(gameSettings.wapFlag ? 'true' : 'false');
        //stepSource += '&timestamp='+timeStamp;
        var thisWidth = '100%';
        var thisHeight = 340;
        var thisFrame = $('<iframe name="'+stepName+'" src="'+stepSource+'" width="'+thisWidth+'" height="'+thisHeight+'" frameborder="1" scrolling="no"></iframe>');
        stepMenu.append(thisFrame);
        if (hasBlank && stepName == 'leaderboard'){ switchTimeoutDuration = 3000; }
        else if (hasBlank && stepName == 'database'){ switchTimeoutDuration = 2000; }
        else if (hasBlank && stepName == 'help'){ switchTimeoutDuration = 1000; }
        else if (hasBlank && stepName == 'stars'){ switchTimeoutDuration = 1000; }

    }

    // Trigger the window resize function
    windowResizePrototype();

    // Update the game settings with the next menu's step name and slide direction
    gameSettings.nextStepName = stepName;
    gameSettings.nextSlideDirection = slideDirection;
    // Switch the direction of the robot loading sprite by using the slide direction
    var removeClass = 'sprite_40x40_'+(slideDirection)+'_00';
    var addClass = 'sprite_40x40_'+(slideDirection == 'left' ? 'right' : 'left')+'_00';
    $('.menu[data-step=loading]', thisPrototype).find('.sprite').removeClass(removeClass).addClass(addClass);
    // Fade in the overlay when moving from HOME to LOADING to prevent clicking other banner links
    if (gameSettings.startLink == 'home'){

        var bannerOverlay = $('.banner_overlay', thisPrototype);
        bannerOverlay.stop().css({opacity:0.00}).removeClass('overlay_hidden').animate({opacity:0.33},{duration:1000,easing:'swing',queue:false});
        var thisBanner = $('.banner', thisPrototype);
        $('.canvas_overlay_footer', thisBanner).remove();
        $('.points, .subpoints, .options, .tooltip', thisBanner).stop().animate({opacity:0},500,'swing');

    }
    // Switch to the loading menu, and wait for the next menu to finish loading
    if (thisCallback != undefined){ var onComplete = thisCallback; }
    else { var onComplete = !switchHasIframe ? function(){ var loadTimeout = setTimeout(function(){ prototype_menu_loaded(); }, switchTimeoutDuration); } : function(){}; }
    var tempSlideDuration = thisSlideDuration != undefined ? thisSlideDuration : 600;
    var tempStepName = gameSettings.startLink != 'home' ? gameSettings.startLink : 'loading';
    prototype_menu_switch({stepName:tempStepName,slideDirection:slideDirection,onComplete:onComplete,slideDuration:tempSlideDuration});

    /*
    prototype_menu_switch({stepName:'loading',slideDirection:slideDirection,
        onComplete:function(){
            //alert('Complete!');
            var tempTimeout = setTimeout(function(){
                //alert('Timeout!');
                prototype_menu_switch({stepName:stepName,slideDirection:slideDirection});
                }, switchTimeoutDuration);
            }
        });
    */

}

// Define a function for triggering a prototype option link
function prototype_menu_click_option(thisContext, thisOption, onComplete){
    //console.log('prototype_menu_click_option(thisContext:', thisContext, ', thisOption:', thisOption, ', onComplete:', onComplete, ')');

    // If this option is disabled, ignore its input
    if ($(this).hasClass('option_disabled')
        && !$(this).hasClass('option_disabled_clickable')
        ){ return false; }

    // Collect the parent menu and option fields
    var thisOption = $(thisOption);
    var thisParent = thisOption.closest('.menu[data-select]');
    //if (thisParent.is('.option_wrapper')){ thisParent = thisParent.parent(); }
    var thisStep = parseInt(thisParent.attr('data-step'));
    var thisSelect = thisParent.attr('data-select');
    var thisToken = thisOption.attr('data-token');
    var thisTokenID = thisOption.attr('data-token-id');
    var nextStep = $('.menu[data-step="'+(thisStep + 1)+'"]', thisContext);
    var nextFlag = true;
    var nextLimit = thisOption.attr('data-next-limit') || false;
    if (nextLimit){ nextLimit = parseInt(nextLimit); }
    var nextHref = thisOption.attr('data-next-href') || false;
    var onComplete = typeof onComplete !== 'undefined' ? onComplete : function(){};
    //var nextLimit = parseInt(thisOption.attr('data-next-limit'));

    // DEBUG
    //console.log('thisStep', thisStep);
    //console.log('thisSelect', thisSelect);
    //console.log('thisToken', thisToken);
    //console.log('thisTokenID', thisTokenID);
    //console.log('nextStep', nextStep, nextStep.length);
    //console.log('nextFlag', nextFlag);
    //console.log('nextLimit', nextLimit);
    //console.log('nextHref', nextHref);

    // If the token was empty, return false
    if (!thisToken.length){
        onComplete();
        return false;
    }

    // If the next limit was set, apply to the next step
    if (nextLimit){
        nextStep.attr('data-limit', nextLimit);
        gameSettings.nextRobotLimit = nextLimit;
        }

    // DEBUG
    //console.log('gameSettings', gameSettings);

    // If this is a child token, update the parent
    if (typeof thisOption.attr('data-child') !== 'undefined'){

        // Find the parent token container
        var tokenParent = $('.option[data-parent]', thisParent);
        var tokenParentLabel = $('label', tokenParent);
        var tokenParentLimit = thisParent.attr('data-limit');
        var tokenParentValue = tokenParent.attr('data-token');

        // Append this token to the parent's
        tokenParent.attr('data-token', tokenParentValue+(tokenParentValue.length ? ',' : '')+thisToken);
        tokenParentValue = tokenParent.attr('data-token');
        // Add the disabled class to this element
        thisOption.addClass('option_disabled');
        // Count the number of elements in the parent token
        var tokenParentCount = tokenParentValue.split(',').length;

        // Create a clone of this option's sprite element
        var tempSprite = $('.sprite.sprite_robot', thisOption.get(0));
        var tempSpriteInner = tempSprite.find('.sprite');
        if (tempSpriteInner.hasClass('sprite_40x40')){ var tempSize = 40; }
        else if (tempSpriteInner.hasClass('sprite_80x80')){ var tempSize = 80; }
        else if (tempSpriteInner.hasClass('sprite_160x160')){ var tempSize = 160; }
        var tempSizeSize = tempSize+'x'+tempSize;
        var tempSpriteKey = tokenParentCount - 1;
        var tempSpriteShift = parseInt($('.sprite[data-key="'+tempSpriteKey+'"]', tokenParent).css('right'));
        //if (tempSize == 80){ tempSpriteShift -= 20; }
        //console.log('tempSize = '+tempSize+'; tempSpriteKey = '+tempSpriteKey+'; tempSpriteShift = '+tempSpriteShift+'; ');

        var cloneSprite = $('<span class="sprite sprite_robot sprite_clone sprite_40x40"></span>');
        var cloneSpriteInner = $('<span class="sprite sprite_'+tempSizeSize+' sprite_'+tempSizeSize+'_base"></span>');
        var cloneSpriteCSS = {right:tempSpriteShift+'px',left:'auto',bottom:'6px',zIndex:'2'};
        var cloneSpriteInnerCSS = {backgroundImage: tempSpriteInner.css('background-image'), animationDuration: tempSpriteInner.css('animation-duration')};
        cloneSprite.css(cloneSpriteCSS);
        cloneSpriteInner.css(cloneSpriteInnerCSS);
        cloneSpriteInner.appendTo(cloneSprite);
        cloneSprite.appendTo(tokenParentLabel);

        // Hide the placeholder appropriate sprite
        $('.sprite_40x40_placeholder:eq('+(gameSettings.nextRobotLimit - tokenParentCount)+')', tokenParent).css({display:'none'});

        // Brighten the opacity of the parent element proportionately
        var newOpacity = 1.0; //0.2 + (0.8 * (tokenParentCount/tokenParentLimit));
        tokenParent.css({opacity:newOpacity});
        //tokenParent.find('.count').html((tokenParentCount >= tokenParentLimit) ? 'Start!' : (tokenParentCount+'/'+tokenParentLimit));
        tokenParent.find('.count').html((tokenParentCount >= 1) ? (tokenParentCount+'/'+gameSettings.nextRobotLimit)+' Start!' : (tokenParentCount+'/'+gameSettings.nextRobotLimit));
        tokenParent.find('.arrow').html('<i class="fas fa-play"></i>');

        // If robots have not been selected, hide the reselector
        if (tokenParentCount < 1){
            //alert('hide reselect '+tokenParentCount);
            $('.reselect', thisParent).css({opacity:0});
            } else {
            //alert('show reselect '+tokenParentCount);
            $('.reselect', thisParent).css({opacity:1});
            }

        /*
        // Check if we've reached the token limit
        if (tokenParentCount >= tokenParentLimit){
            // Disable all other child options and enable the parent
            $('.option[data-child]', thisParent).addClass('option_disabled');
            tokenParent.removeClass('option_disabled');
            }
        */

        // Check if we've reached the token limit
        //if (tokenParentCount >= tokenParentLimit){
        if (tokenParentCount >= 1){
            // Enable the parent option for clicking
            tokenParent.removeClass('option_disabled');
            // Disable all other child options if we're at the total limit
            if (tokenParentCount >= gameSettings.totalRobotLimit || tokenParentCount >= gameSettings.nextRobotLimit){
                $('.option[data-child]', thisParent).addClass('option_disabled');
                }
            }

        // Set the next flag to false to prevent menu switching
        onComplete();
        nextFlag = false;

        } else {

        // Update the battleOptions object with the current selection
        battleOptions[thisSelect] = thisToken;
        //console.log('battleOptions['+thisSelect+'] = '+thisToken+';');
        if (typeof thisTokenID !== 'undefined'){
            battleOptions[thisSelect.replace(/_token$/, '_id')] = thisTokenID;
            //console.log('battleOptions['+thisSelect.replace(/_token$/, '_id')+'] = '+thisTokenID+';');
            }

        }

    //console.log('thisSelect =', thisSelect);

    // Execute option-specific commands for special cases
    switch (thisSelect){
        case 'this_player_token': {

            // Prevent the player from fighting themselves in battle
            var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
            var tempMenu = $('.menu[data-select="this_battle_token"]', thisContext);
            var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
            var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
            tempHideOptionWrapper.addClass('option_wrapper_hidden').css({border:'5px none transparent',margin:''});
            tempShowOptionWrapper.removeClass('option_wrapper_hidden').css({border:'1px solid transparent',marginLeft:'-1px'});

            // Count the number of available missions right now
            var availableMissions = $('.option[data-token]', tempShowOptionWrapper);
            $('.header', tempMenu).find('.count').html('Mission Select ('+(availableMissions.length == 1 ? '1 Mission' : availableMissions.length+' Missions')+')');
            $('.menu[data-select="this_battle_token"] .header', thisContext).attr('data-player', battleOptions['this_player_token']);
            $('.menu[data-select="this_player_robots"] .header', thisContext).attr('data-player', battleOptions['this_player_token']);

            break;
            }
        case 'this_battle_token': {

            // Prevent the player from fighting themselves in battle
            var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
            var tempMenu = $('.menu[data-select="this_player_robots"]', thisContext);
            var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
            var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
            tempHideOptionWrapper.addClass('option_wrapper_hidden').css({border:'5px none transparent',margin:''});
            tempShowOptionWrapper.removeClass('option_wrapper_hidden').css({border:'1px solid transparent',marginLeft:'-1px'});

            // Find the parent token container
            var tempWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
            var tempInnerWrapper = tempWrapper.find('> .wrap');
            var tokenParent = $('.option[data-parent]', tempWrapper);
            var tokenParentLimit = tempMenu.attr('data-limit');
            //console.log('tempWrapper(A) =', tempWrapper, tempWrapper.html());

            // Count the number of available robots right now
            var requiredRobots = nextLimit;
            var availableRobots = $('.option[data-child]', tempWrapper);
            var selectedRobots = $('.option[data-parent]', tempWrapper);
            //console.log('Battle requires '+requiredRobots+' robots, you have '+availableRobots.length+'.', availableRobots);

            // Trigger perfect scrollbars on the frame containers
            //console.log('tempWrapper('+tempWrapper.length+') = ', tempWrapper);
            //console.log('tempWrapper = ', tempWrapper.html());

            // Apply the perfect scrollbar if an inner wrap exists
            if (tempInnerWrapper.length){ tempInnerWrapper.perfectScrollbar(thisScrollbarSettings); }

            // Update the start button's counter text
            $('.option[data-parent]', tempMenu).find('.count').html('0/'+gameSettings.nextRobotLimit+' Select');

            var tempMenuHeader = $('.header', tempMenu);
            tempMenuHeader.find('.count').html('Robot Select ('+(availableRobots.length == 1 ? '1 Robot' : availableRobots.length+' Robots')+')');
            if (!$('.reselect', tempMenuHeader).length){
                var tempReselect = $('<span class="reselect">&#215;</span>');
                tempReselect.click(function(){
                    //console.log('reselect!');
                    // Re-enable all robot options
                    $('.option_wrapper[data-condition]', tempMenu).css({display:''});
                    $('.option[data-child]', tempMenu).removeClass('option_disabled');
                    $('.option[data-parent]', tempMenu).addClass('option_disabled').attr('data-token', '').css({opacity:''}).find('.count').html('0/'+gameSettings.nextRobotLimit+' Select').end().find('.arrow').html('&nbsp;');
                    //$('.option[data-parent] label', tempMenu).css({paddingLeft:''});
                    $('.option[data-parent] .sprite:not(.sticky)', tempMenu).remove();
                    $('.sprite_40x40_placeholder', tempMenu).css({display:''});
                    delete battleOptions['this_player_robots'];
                    $(this).css({opacity:0});
                    return true;
                    });
                tempMenuHeader.append(tempReselect);
                }

            // If the user has less than the limit required
            if (requiredRobots > availableRobots.length){
                nextLimit = availableRobots.length;
                }

            // If robots have not been selected, hide the reselector
            if (battleOptions['this_player_robots'] === undefined || battleOptions['this_player_robots'].length < 1){
                //console.log('hide reselect');
                $('.reselect', tempMenuHeader).css({opacity:0});
                } else {
                //console.log('show reselect');
                $('.reselect', tempMenuHeader).css({opacity:1});
                }

            // Generate the placeholder sprite markup
            var iCounter = 1;
            var spriteMarkup = '';
            for (iCounter; iCounter <= gameSettings.nextRobotLimit; iCounter++){

                var tempOffset = 80 + ((tokenParentLimit * 40) - (iCounter * 40) + 40);

                var heartClass = 'sprite sprite_40x40 sprite_40x40_heartback sticky ';
                var heartStyle = 'right: '+(tempOffset - 2)+'px; ';
                spriteMarkup += '<span class="'+heartClass+'" style="'+heartStyle+'"><i class="fa fas fa-heart"></i></span>';

                var spriteClass = 'sprite sprite_40x40 sprite_40x40_base sprite_40x40_placeholder sticky ';
                var spriteStyle = 'bottom: 6px; right: '+tempOffset+'px; left: auto; z-index: 2; ';
                spriteMarkup += '<span data-key="'+(gameSettings.nextRobotLimit - iCounter)+'" class="'+spriteClass+'" style="'+spriteStyle+'">Select Robot</span>';

                }

            // Prepend the sprite to the parent's label value
            var labelPadding = ((tokenParentLimit * 40)+60)+'px';
            $('.sprite_40x40_heartback', tokenParent).remove();
            $('.sprite_40x40_placeholder', tokenParent).remove();
            $('label', tokenParent).prepend(spriteMarkup);

            //console.log('tempWrapper(B) =', tempWrapper, tempWrapper.html());

            break;

            }
        case '': {

            alert('there be problems, hunny...');

            break;
        }
        default: {

            break;
            }
        }

    // Only do banner events for non-child options
    if (typeof thisOption.attr('data-child') === 'undefined'){

        // Collect the context for the banner area and remove and foregrounds

        var thisBanner = $('.banner', thisContext);
        /*
        //console.log('get ready to fade credits to '+creditsOpacity+'!');
        var creditsOpacity = ((gameSettings.demo == true || (gameSettings.demo != true && gameSettings.totalPlayerOptions == 1 && thisSelect == 'this_player_token')) ? 1.0 : 0.0);
        $('.banner_credits', thisBanner).removeClass('is_shifted').animate({opacity:creditsOpacity},{duration:600,easing:'swing',sync:false,complete:function(){
            //console.log('credits have been animated to '+creditsOpacity);
            $(this).addClass('is_shifted');
            }});
        */
        $('.banner_foreground:not(.is_shifted)', thisBanner).css({opacity:0.6}).animate({opacity:1.0},{duration:600,easing:'swing',sync:false,complete:function(){
            $(this).addClass('is_shifted');
            }});

        // Count the number of other options in the banner
        var numOptions = $('.option', thisBanner).length;

        // Remove any options for the same select parent
        var previousOption = $('.option[data-select="'+thisSelect+'"]', thisBanner);
        if (previousOption.length){
            previousOption.animate({opacity:0},600,'swing',function(){
                $('.option:gt('+previousOption.eq()+')', thisBanner).remove();
                $(this).remove();
                });
            numOptions--;
            }

        // Determine the position of this new option block
        var thisPosition = numOptions + 1;

        // Only append to banner if not a team select option
        if (!thisOption.hasClass('option_this-team-select')){

            // Append this option object to the main banner window
            var cloneOption = thisOption.clone();
            var cloneWidth = ((numOptions * 8) + 42);
            if (thisSelect === 'this_player_token'){
                cloneWidth = 19 + battleOptions['this_player_token'].length;
                // player button / player icon / player select in banner
                }
            cloneOption.attr('data-select', thisSelect);
            cloneOption.removeClass('option_1x1 option_1x2 option_1x3 option_1x4').addClass('option_1x'+thisPosition);
            cloneOption.find('.arrow').css({right:0});
            cloneOption.find('.sprite_nobanner').remove();
            cloneOption.css({
                position:'absolute',
                zIndex:40,
                left: '-30px', //(gameSettings.demo == 1 && thisPosition > 1)  ? '-75px' : '-30px',
                //top:(10 + (78 * numOptions))+'px',
                top:(15 + (80 * numOptions))+'px',
                opacity:0,
                //marginLeft:'-'+(thisBanner.outerWidth() + 100)+'px',
                borderWidth:'1px',
                border:'1px solid rgba(0, 0, 0, 0.6)',
                width: cloneWidth+'%'
                });
            cloneOption.find('label').css({
                //marginRight:'15px',
                //width:'120px'
                margin: cloneOption.hasClass('option_this-team-select') ? '0 4px 0 260px' : '0 4px 0 20px',
                width: 'auto'
                });
            cloneOption.unbind('click');
            thisBanner.append(cloneOption);
            if (!gameSettings.skipPlayerSelect){ cloneOption.animate({opacity:1,marginLeft:'0'},600,'linear'); }
            else { cloneOption.css({opacity:1,marginLeft:'0'}); }

            }

        // If this was a mission select, update the banner background image
        if (thisSelect == 'this_battle_token'){
            // Change the background image based on the current option data
            var newBackgroundToken = thisOption.attr('data-background');
            var backgroundFileToken = 'battle-field_background_base';
            if (typeof thisOption.attr('data-background-variant') !== 'undefined'){ backgroundFileToken += '_'+thisOption.attr('data-background-variant'); }
            var newBackgroundImage = 'url(images/fields/'+newBackgroundToken+'/'+backgroundFileToken+'.gif?'+gameSettings.cacheTime+')';
            var oldBannerBackground = $('.banner_background', thisBanner);
            oldBannerBackground.stop();
            var newBannerBackground = $('<div class="sprite background banner_background" style="opacity: 0; z-index: 11; background-position: center -30px; background-image: '+newBackgroundImage+';">&nbsp;</div>');
            newBannerBackground.insertAfter(oldBannerBackground).animate({opacity:1.0},{duration:1000,easing:'swing',queue:false,complete:function(){ oldBannerBackground.remove(); $(this).css({zIndex:''}); }});
            // Change the foreground image based on the current option data
            var newForegroundToken = thisOption.attr('data-foreground');
            var foregroundFileToken = 'battle-field_foreground_base';
            if (typeof thisOption.attr('data-foreground-variant') !== 'undefined'){ foregroundFileToken += '_'+thisOption.attr('data-foreground-variant'); }
            var newForegroundImage = 'url(images/fields/'+newForegroundToken+'/'+foregroundFileToken+'.png?'+gameSettings.cacheTime+')';
            var oldBannerForeground = $('.banner_foreground', thisBanner);
            oldBannerForeground.stop();
            var newBannerForeground = $('<div class="sprite background banner_foreground" style="opacity: 0; z-index: 21; background-position: center -30px; background-image: '+newForegroundImage+';">&nbsp;</div>');
            newBannerForeground.insertAfter(oldBannerForeground).animate({opacity:1.0},{duration:1000,easing:'swing',queue:false,complete:function(){ oldBannerForeground.remove(); $(this).css({zIndex:''}); }});
            // Fade in the overlay to prevent clicking on banner links
            var bannerOverlay = $('.banner_overlay', thisBanner);
            //thisBanner.stop().removeClass('banner_compact').animate({height:'124px'},{duration:1000,easing:'swing',queue:false});
            bannerOverlay.stop().removeClass('overlay_hidden').animate({opacity:0.33},{duration:1000,easing:'swing',queue:false});
            $('.points, .subpoints, .options, .tooltip', thisBanner).stop().animate({opacity:0},{duration:500,easing:'swing',queue:false});
            // Add the canvas overlay footer to the canvas with multipliers
            var thisFieldName = thisOption.attr('data-field');
            var thisBattleDescription = thisOption.attr('data-description').replace('-', '&#8209;');
            var thisFieldMultipliers = thisOption.attr('data-multipliers');
            var thisFieldMultipliersLength = thisFieldMultipliers.length != undefined ? thisFieldMultipliers.length : 0;
            thisBanner.append('<div class="canvas_overlay_footer"><div class="overlay_title">'+thisFieldName+'</div><div class="overlay_description">'+thisBattleDescription+'</div></div>');
            if (thisFieldMultipliers.length){
                $('.canvas_overlay_footer', thisBanner).append('<div class="overlay_title" style="top: 4px; padding: 2px 10px 0; font-size: 8px; margin-bottom: -2px;">Field Multipliers</div>');
                $('.canvas_overlay_footer', thisBanner).append('<div class="overlay_multipliers"></div>');
                thisFieldMultipliers = thisFieldMultipliers.split('|');
                for (var i in thisFieldMultipliers){
                    var thisPair = thisFieldMultipliers[i].split('*');
                    var thisType = thisPair[0];
                    var thisMultiplier = parseFloat(thisPair[1]);
                    if (thisMultiplier === 1){ continue; }
                    var thisTypeName = thisType.charAt(0).toUpperCase() + thisType.slice(1);
                    $('.canvas_overlay_footer .overlay_multipliers', thisBanner).append('<span class="field_multiplier field_multiplier_'+thisType+' field_multiplier_count_'+thisFieldMultipliersLength+' field_type field_type_'+thisType+'"><span class="text">'+thisTypeName+' <span class="cross" style="">x</span> '+thisMultiplier+'</span></span>');
                    }
                } else {
                    //$('.canvas_overlay_footer', thisBanner).append('<span class="field_multiplier field_multiplier_none field_multiplier_count_0 field_type field_type_none"><span class="text">- none -</span></span>');
                }

            }

    }

    // Collect the preload image list, if provided
    var thisPreload = thisOption.attr('data-preload')  !== undefined ? thisOption.attr('data-preload') : false;

    // Collect all the redirect variables in case they're needed
    var thisRedirect = 'battle.php?wap='+(gameSettings.wapFlag ? 'true' : 'false');
    //var thisRedirect = 'battle.new.php?wap='+(gameSettings.wapFlag ? 'true' : 'false');
    for (var key in battleOptions){ thisRedirect += '&'+key+'='+battleOptions[key]; }

    // If this was a mission select, make sure we re-flow the robot select when ready
    if (thisSelect == 'this_battle_token'){
        var oldOnComplete = onComplete;
        var newOnComplete = function(){
            var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
            var $tempMenu = $('.menu[data-select="this_player_robots"]', thisContext);
            var $tempWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', $tempMenu);
            var $tempInnerWrapper = $tempWrapper.find('> .wrap');
            if ($tempInnerWrapper.length){
                $tempInnerWrapper.scrollTop(1);
                $tempInnerWrapper.perfectScrollbar(thisScrollbarSettings);
                }
            oldOnComplete();
            };
        onComplete = newOnComplete;
        }

    // If a "NEXT" link was provided, use it instead of the default
    if (nextHref){
        //console.log('Replace the usual redirect link with the next href instead');
        // Clear the usual next step data
        nextStep = '';
        // Replace the usual redirect link with the next href instead
        thisRedirect = nextHref;
        }

    // Check if image preloading was requested
    if (thisPreload.length){
        // Preload the requested image
        var thisPreloadImage = $(document.createElement('img'))
            .attr('src', thisPreload)
            .load(function(){
                // Check if there is another menu step to complete
                if (nextStep.length){
                    // Automatically switch to the next step in sequence
                    prototype_menu_switch({stepNumber:thisStep + 1,onComplete:onComplete});
                    } else {
                    // Redirect to the battle page
                    prototype_menu_switch({redirectLink:thisRedirect,onComplete:onComplete}); // checkpoint
                    }
                });
        } else if (nextFlag != false){
        // Check if there is another menu step to complete
        if (nextStep.length){
            // Automatically switch to the next step in sequence
            prototype_menu_switch({stepNumber:thisStep + 1,onComplete:onComplete});
            } else {
            // Redirect to the battle page
            prototype_menu_switch({redirectLink:thisRedirect,onComplete:onComplete});
            }
        }

    // Return true on success
    return true;

}

// Define a function for triggering a prototype back link
function prototype_menu_click_back(thisContext, thisLink){
    // Collect the parent menu and option fields
    var thisLink = $(thisLink);
    var backStep = parseInt(thisLink.attr('data-back'));
    var backParent = $('.menu[data-step="'+(backStep)+'"]', thisContext);
    var backSelect = backParent.attr('data-select');
    // Clear the previous battleOption selection
    delete battleOptions[backSelect];
    // Define the switchOptions object
    var switchOptions = {stepNumber:backStep,autoSkip:'false',slideDirection:'right'};
    // Execute option-specific commands for special cases
    switch (backSelect){
        case 'this_player_token': {
            switchOptions.onComplete = function(){
                // Re-enable all battle options
                var tempMenu = $('.menu[data-select="this_battle_token"]', thisContext);
                $('.option_wrapper[data-condition]', tempMenu).css({display:''});
                delete battleOptions['this_battle_token'];
                }
            break;
            }
        case 'this_battle_token': {
            switchOptions.onComplete = function(){

                // Re-enable all battle options
                var tempMenu = $('.menu[data-select="this_player_robots"]', thisContext);
                $('.option_wrapper[data-condition]', tempMenu).css({display:''});
                $('.option[data-child]', tempMenu).removeClass('option_disabled');
                $('.option[data-parent]', tempMenu).addClass('option_disabled').attr('data-token', '').css({opacity:''}).find('.count').html('0/'+gameSettings.nextRobotLimit+' Select').end().find('.arrow').html('&nbsp;');
                $('.option[data-parent] label', tempMenu).css({paddingLeft:''});
                $('.option[data-parent] .sprite:not(.sticky)', tempMenu).remove();
                $('.sprite_40x40_placeholder', tempMenu).css({display:''});
                delete battleOptions['this_player_robots'];
                }
                // Change the background image back to the default
                var newBackgroundImage = 'url(images/menus/menu-banner_this-battle-select.png?'+gameSettings.cacheTime+')';
                var oldBannerBackground = $('.banner_background', thisBanner);
                oldBannerBackground.stop();
                var newBannerBackground = $('<div class="sprite background banner_background" style="opacity: 0; z-index: 11; background-image: '+newBackgroundImage+';">&nbsp;</div>');
                newBannerBackground.insertAfter(oldBannerBackground).animate({opacity:1.0},{duration:1000,easing:'swing',queue:false,complete:function(){ oldBannerBackground.remove(); $(this).css({zIndex:''}); }});
                // Change the foreground image back to the default
                //var newForegroundImage = 'url(images/menus/menu-banner_this-battle-select_prototype-4_cut-man.png?'+gameSettings.cacheTime+')';
                var numBackgroundOptions = gameSettings.prototypeBanners.length;
                var randomBackgroundKey = Math.floor(Math.random() * numBackgroundOptions);
                var newForegroundImage = 'url(images/menus/'+gameSettings.prototypeBanners[randomBackgroundKey]+'?'+gameSettings.cacheTime+')';
                //var newForegroundImage = 'url(images/menus/menu-banner_title-screen-01.png?'+gameSettings.cacheTime+')';
                var oldBannerForeground = $('.banner_foreground', thisBanner);
                oldBannerForeground.stop();
                var newBannerForeground = $('<div class="sprite background banner_foreground" style="opacity: 0; z-index: 21; background-position: center -10px; background-image: '+newForegroundImage+';">&nbsp;</div>');
                newBannerForeground.insertAfter(oldBannerForeground).animate({opacity:1.0},{duration:1000,easing:'swing',queue:false,complete:function(){ oldBannerForeground.remove(); $(this).css({zIndex:''}); }});
                // Fade out the overlay to allow clicking on banner links
                var bannerOverlay = $('.banner_overlay', thisBanner).stop().animate({opacity:0},{duration:1000,easing:'swing',queue:false,complete:function(){ $(this).addClass('overlay_hidden'); }});
                $('.points, .subpoints, .options, .tooltip', thisBanner).stop().animate({opacity:1},500,'swing');
                //alert(newBackgroundImage);
                // Remove the field details overlay
                $('.canvas_overlay_footer', thisBanner).remove();
            break;
            }
        default: {
            break;
            }
        }
    // Clear any of this select's options in the banner
    var thisBanner = $('.banner', thisContext);
    $('.option[data-select="'+backSelect+'"]', thisBanner).animate({opacity:0},600,'swing',function(){
        $(this).remove();
            var remainingOptions = $('.option', thisBanner).length;
            //alert(remainingOptions);
            if (remainingOptions < 1){
                //alert('no options');
                $('.is_shifted', thisBanner).removeClass('is_shifted').animate({opacity:1.0},600,'swing');
                }
        });
    // Trigger the menu switch for the new step
    prototype_menu_switch(switchOptions);
    // Return true on success
    return true;
}

// Create a function for switching to a specific menu step
var menuSwitchTimeout = false;
function prototype_menu_switch(switchOptions){
    //console.log('prototype_menu_switch(switchOptions:', switchOptions, ')');

    // Clear any existing timeout and then set a new one a little bit in the future
    if (menuSwitchTimeout){ clearTimeout(menuSwitchTimeout); }
    menuSwitchTimeout = setTimeout(function(){ return prototype_menu_switch_action(switchOptions); }, 1000);

}

// Create a function for switching to a specific menu step
function prototype_menu_switch_action(switchOptions){
    //console.log('prototype_menu_switch_action(switchOptions:', switchOptions, ')');

    // Redefine the options array populating defaults
    switchOptions = {
        stepName: switchOptions.stepName || false,
        stepNumber: switchOptions.stepNumber || false,
        redirectLink: switchOptions.redirectLink || false,
        autoSkip: switchOptions.autoSkip || 'false',
        slideDirection: switchOptions.slideDirection || 'left',
        slideDuration: switchOptions.slideDuration || 600,
        onComplete: switchOptions.onComplete || function(){}
        };

    //console.log('prototype_menu_switch(switchOptions) w/', switchOptions);

    // Define the prototype context
    var thisContext = $('#prototype');
    var thisBanner = $('.banner', thisContext);

    // Collect the current step token
    var currentStepToken = $('.menu[data-step]:not(.menu_hide)', thisContext).attr('data-step');

    // Update the prototype element with data attributes for styling
    //console.log('johto');
    var dataStepName = switchOptions.stepName;
    var dataStepNumber = switchOptions.stepNumber;
    //console.log('battleOptions =>', typeof battleOptions, battleOptions);
    //console.log('dataStepName =>', typeof dataStepName, dataStepName);
    //console.log('dataStepNumber =>', typeof dataStepNumber, dataStepNumber);
    if (dataStepName === false){
        //console.log('dataStepName === false');
        dataStepName = 'home';
        }
    else if (parseInt(dataStepName) > 0){
        //console.log('parseInt(dataStepName) > 0');
        dataStepNumber = parseInt(dataStepName);
        dataStepName = 'home';
        }
    if (dataStepName === 'home'){
        //console.log('dataStepName === home');
        if (!dataStepNumber){
            //console.log('!dataStepNumber');
            dataStepNumber = 1;
            }
        if (dataStepNumber < 2
            && typeof battleOptions['this_player_token'] !== 'undefined'
            && battleOptions['this_player_token'].length){
            //console.log('dataStepNumber < 2 && this_player_token.length');
            dataStepNumber = 2;
            }
        if (dataStepNumber < 3
            && typeof battleOptions['this_player_robots'] !== 'undefined'
            && battleOptions['this_player_robots'].length){
            //console.log('dataStepNumber < 3 && this_player_robots.length');
            dataStepNumber = 3;
            }
        if (dataStepNumber < 4
            && typeof switchOptions.redirectLink !== 'undefined'
            && switchOptions.redirectLink.length){
            //console.log('dataStepNumber < 4 && redirectLink.length');
            dataStepNumber = 4;
            }
        }
    else if (dataStepName !== 'home'){
        dataStepNumber = 0;
        }
    thisPrototype.attr('data-step-name', dataStepName);
    thisPrototype.attr('data-step-number', dataStepNumber);
    //console.log('dataStepName =>', typeof switchOptions.stepName, switchOptions.stepName, '=>', typeof dataStepName, dataStepName);
    //console.log('dataStepNumber =>', typeof switchOptions.stepNumber, switchOptions.stepNumber, '=>', typeof dataStepNumber, dataStepNumber);
    // If we're on the mission select screen, let's shrink the spriteBounds a bit for the ready room, else revert to default
    if (thisReadyRoom){
        if (dataStepName === 'home'
            && dataStepNumber === 2){
            thisReadyRoom.updateSpriteBounds({minX: 20, maxX: 80});
            } else {
            thisReadyRoom.resetSpriteBounds();
            }
        }


    // Only proceed normally if the current start link is home
    if (gameSettings.startLink != 'home'){
        var stepToken = switchOptions.stepNumber || switchOptions.stepName;
        if (stepToken != gameSettings.startLink){ return false; }
        //else if (stepToken == currentStepToken){ return false; }
        //else if (stepToken == gameSettings.startLink){ gameSettings.startLink = 'home'; }
        //gameSettings.startLink = 'home';
        }

    // Prevent switching to the same menu twice
    if (switchOptions.stepNumber == currentStepToken || switchOptions.stepName == currentStepToken){
        //alert('they are the same');
        return switchOptions.onComplete();
        }

    // DEBUG
    //console.log('Switching from '+currentStepToken+' to '+(switchOptions.stepNumber || switchOptions.stepName || switchOptions.redirectLink)+'\nAuto Skip is '+(switchOptions.autoSkip == 'true' ? 'ON' : 'OFF'));
    //console.log(switchOptions);

    // If this is the LOADING screen, shrink the banner height
    if (switchOptions.stepName == 'loading'){
        var newHeight = 124;
        //console.log('Shrinking the banner height to '+newHeight);
        thisBanner.removeClass('fullsize').addClass('compact').animate({height:newHeight+'px'},{duration:500,easing:'swing',queue:false});
        //var thisLoadingMenu = $('.menu[data-step=loading]', thisPrototype);
        //thisLoadingMenu.css({height:'800px',border:'2px solid red'});
        //$('.option_wrapper', thisLoadingMenu).css({height:'800px',border:'2px solid blue'});
        //$('.banner_credits', thisBanner).animate({opacity:0},{duration:500,easing:'swing',queue:false,complete:function(){ $(this).css({display:'none'}); } });
        }

    // Else if this is the HOME screen, expand the banner height
    if ((switchOptions.stepName == '1' || switchOptions.stepNumber == 1)
        || (gameSettings.demo == true && (switchOptions.stepName == '2' || switchOptions.stepNumber == 2))
        || (gameSettings.demo != true && gameSettings.totalPlayerOptions == 1 && (switchOptions.stepName == '2' || switchOptions.stepNumber == 2))
        ){
        var newHeight = 184;
        //console.log('Expanding the banner height to '+newHeight);
        thisBanner.removeClass('compact').addClass('fullsize').animate({height:newHeight+'px'},{duration:500,easing:'swing',queue:false});
        //$('.banner_credits', thisBanner).removeClass('is_shifted').css({display:'block'}).animate({opacity:1},{duration:500,easing:'swing',queue:false});
        }

    // Change the background music to the appropriate file
    if (switchOptions.stepNumber == 1){
        parent.mmrpg_music_load('misc/player-select', true, false);
        } else if (switchOptions.stepNumber == 2){
        var newMusicToken = $('.select_this_player .option_this-player-select[data-token="'+battleOptions['this_player_token']+'"]', thisContext).attr('data-music-token');
        //console.log('newMusicToken = '+newMusicToken);
        var newMusicPath = newMusicToken.indexOf('/') === -1 ? 'misc/'+newMusicToken : newMusicToken;
        parent.mmrpg_music_load(newMusicPath, true, false);
        }

    // Define the prototype context events
    if (thisContext.length){

        //windowResizePrototype();

        // Define the animation properties
        var slideOutAnimation = {opacity:0};
        var slideInAnimation = {opacity:0};
        if (switchOptions.slideDirection == 'left'){
            slideOutAnimation.marginLeft = '-1000px';
            slideOutAnimation.marginRight = '1000px';
            slideInAnimation.marginLeft = '1000px';
            slideInAnimation.marginRight = '-1000px';
            } else if (switchOptions.slideDirection == 'right'){
            slideOutAnimation.marginRight = '-1000px';
            slideOutAnimation.marginLeft = '1000px';
            slideInAnimation.marginRight = '1000px';
            slideInAnimation.marginLeft = '-1000px';
            }



        // Collect a reference to the current menus
        var currentMenu = $('.menu[data-step="'+(switchOptions.stepNumber || switchOptions.stepName)+'"]', thisContext);
        var currentMenuTitle = currentMenu.attr('data-title');

        // Collect the step, select, and condition for this
        var currentMenuStep = currentMenu.attr('data-step');
        var currentMenuSelect = currentMenu.attr('data-select');
        var currentMenuCondition = 'true';
        if (currentMenuSelect == 'this_battle_token' || currentMenuSelect == 'this_player_robots'){
            currentMenuCondition = 'this_player_token='+battleOptions.this_player_token;
            }

        // Define a function for checking and managing the read room content
        //console.log('hoenn');
        var tempReadyRoomFunction = function(loadState){
            //console.log('tempReadyRoomFunction(loadState:', typeof loadState, loadState, ')');
            if (typeof loadState === 'undefined'){ loadState = ''; }

            // Define some quick filter functions for dealing with player-specific robots
            var filterPlayer = typeof battleOptions['this_player_token'] !== 'undefined' && battleOptions['this_player_token'].length ? battleOptions['this_player_token'] : '';
            var filterPlayerFunction = function(token, info){ return info.player === filterPlayer || info.currentPlayer === filterPlayer || info.currentPlayer === 'all'; };
            var filterOtherPlayersFunction = function(token, info){ return info.player !== filterPlayer && info.currentPlayer !== filterPlayer && info.currentPlayer !== 'all'; };

            // Update, show, or update the READY ROOM based on option-specific commands for special cases
            //console.log('switchOptions.stepNumber =', switchOptions.stepNumber);
            //console.log('currentMenuSelect =', currentMenuSelect);
            if (currentMenuSelect === 'this_battle_token'
                && typeof battleOptions['this_player_token'] !== 'undefined'
                && battleOptions['this_player_token'].length){
                thisReadyRoom.show();
                var spriteBounds = thisReadyRoom.config.spriteBounds;
                if (loadState === 'reload'){
                    // If not already filtered, animate the player's robots sliding into place
                    if (!thisReadyRoom.config.isFiltered){
                        thisReadyRoom.updatePlayer(function(token, info){ return info.player !== filterPlayer && info.currentPlayer !== filterPlayer; }, {frame: 'running', direction: 'left', position: ['-=4', null], opacity: 0});
                        thisReadyRoom.updatePlayer(filterPlayer, {frame: 'running', direction: 'right', position: [(spriteBounds.maxX / 2), (spriteBounds.minY - 1)], opacity: 1});
                        thisReadyRoom.updateRobot(filterOtherPlayersFunction, {frame: 'slide', direction: 'left', position: ['-=2', null], opacity: 0});
                        thisReadyRoom.updateRobot(filterPlayerFunction, {frame: 'slide', direction: 'right', position: ['+=1', '>=20'], opacity: 1});
                        var updateTimeout = setTimeout(function(){
                            thisReadyRoom.updatePlayer(filterPlayer, {frame: 'base'});
                            thisReadyRoom.updateRobot(filterPlayerFunction, {frame: 'base'});
                            clearTimeout(updateTimeout);
                            updateTimeout = setTimeout(function(){
                                thisReadyRoom.updateRobot('some', {direction: 'left'});
                                thisReadyRoom.updateRobot('most', {frame: 'taunt'});
                                clearTimeout(updateTimeout);
                                }, 900);
                            }, 1200);

                        }
                    // Otherwise if already filtered that means they changed their mind about a mission
                    else {
                        thisReadyRoom.updatePlayer(filterPlayer, {direction: 'left', frame: 'damage', position: ['-=1', null]});
                        thisReadyRoom.updateRobot(filterPlayerFunction, {frame: 'damage', position: ['-=1', null]});
                        var updateTimeout = setTimeout(function(){
                            thisReadyRoom.updateRobot(filterPlayerFunction, {frame: 'base'});
                            clearTimeout(updateTimeout);
                            updateTimeout = setTimeout(function(){
                                thisReadyRoom.updateRobot('some', {frame: 'slide', direction: 'left', position: ['<=60', null]});
                                clearTimeout(updateTimeout);
                                }, 1200);
                            }, 1200);
                        }
                    }
                else if (loadState === 'fadein'){
                    thisReadyRoom.refresh(battleOptions['this_player_token']);
                    }
                }
            else if (currentMenuSelect === 'this_player_token'){
                if (loadState === 'fadein'){
                    thisReadyRoom.refresh();
                    }
                }
            else if (currentMenuSelect === 'this_player_robots'){

                if (loadState === 'reload'){
                    thisReadyRoom.updatePlayer(filterPlayer, {direction: 'right', frame: 'command'});
                    }
                else if (loadState === 'fadein'){
                    thisReadyRoom.updatePlayer(filterPlayer, {direction: 'right', frame: 'running', position: ['+=10', null]});
                    thisReadyRoom.updateRobot(filterPlayerFunction, {direction: 'right', frame: 'slide', position: ['+=12', null]});
                    var updateTimeout = setTimeout(function(){
                        thisReadyRoom.hide();
                        clearTimeout(updateTimeout);
                        }, 100);

                    }

                }
            else if (typeof currentMenuSelect === 'undefined'){
                thisReadyRoom.refresh();
                }
            };

        // Define the function for reloading content
        var tempReloadFunction = function(tempCallbackFunction){
            //console.log('tempReloadFunction(tempCallbackFunction:', typeof tempCallbackFunction, ')');
            if (tempCallbackFunction == undefined){ tempCallbackFunction = function(){}; }
            if (thisReadyRoom){ tempReadyRoomFunction('reload'); }

            // DEBUG
            //console.log('tempReloadFunction triggered, switchOptions:');
            //console.log(switchOptions);

            // Only proceed if the menu select is not empty
            if (currentMenuSelect != undefined){

                // DEBUG
                //console.log('RELOAD TRIGGERED for "'+currentMenuTitle+'" with currentMenuStep = '+currentMenuStep+', currentMenuSelect = '+currentMenuSelect+', currentMenuCondition = '+currentMenuCondition+'!');

                // Check to see if there are conditional wrappers to populate
                if (!currentMenu.find('.option_wrapper').length){

                    // DEBUG
                    //console.log('AJAX POST to MENU-TOP :');
                    //console.log({step:currentMenuStep,select:currentMenuSelect,condition:currentMenuCondition});

                    // No options wrappers were found, so attempt to refresh the markup for this menu panel
                    $.ajax('scripts/prototype.php', {
                        type:  'POST',
                        data: {step:currentMenuStep,select:currentMenuSelect,condition:currentMenuCondition},
                        success: function(markup, status){

                            // DEBUG
                            //console.log('AJAX RETURN :');
                            //console.log({markup:markup,status:status});

                            // If the markup is not empty, replace this menu's options
                            if (markup.length){
                                currentMenu.find('.chapter_select').remove();
                                currentMenu.find('.option').not('.option_sticky').remove();
                                currentMenu.find('.header').after(markup);
                                $('.option_message:gt(0)', currentMenu).triggerSilentClick();
                                }

                            // Trigger the callback function
                            tempCallbackFunction();


                            }
                        });

                } else {

                    // Option wrappers were found, so loop through each and update markup
                    $('.option_wrapper', currentMenu).not('.option_wrapper_hidden').each(function(){

                        // Collect the condition for this particular wrapper
                        var tempMenuWrapper = $(this);
                        var tempMenuCondition = tempMenuWrapper.attr('data-condition');
                        tempMenuCondition = tempMenuCondition.replace('=', '%3d');

                        // DEBUG
                        //console.log('AJAX POST to MENU-WRAPPER :');
                        //console.log({step:currentMenuStep,select:currentMenuSelect,condition:tempMenuCondition});

                        // If the player select has been skipped, do not bother loading missions as they're already there
                        if (gameSettings.skipPlayerSelect){

                            // DEBUG
                            //console.log('SKIPPING AJAX :');

                            // Auto-click any option messages after the first
                            $('.option_message:gt(0)', tempMenuWrapper).triggerSilentClick();

                            // Trigger the callback function
                            tempCallbackFunction();

                            // Change the player select back to normal
                            gameSettings.skipPlayerSelect = false;

                            }
                        // Otherwise load the missions normally over ajax and replace the markup
                        else {

                            // Attempt to refresh the markup for this particular wrapper
                            $.ajax('scripts/prototype.php', {
                                type:  'POST',
                                data: {step:currentMenuStep,select:currentMenuSelect,condition:tempMenuCondition},
                                success: function(markup, status){

                                    // DEBUG
                                    //console.log('AJAX RETURN2 for currentMenuSelect['+currentMenuSelect+']:');
                                    //console.log({markup:markup,status:status});

                                    // If the markup is not empty, replace this menu's options
                                    if (markup.length){
                                        var innerMarkup = markup;
                                        innerMarkup = innerMarkup.replace(/^<div[^<>]+>/i, '');
                                        innerMarkup = innerMarkup.replace(/<\/div>$/i, '');
                                        tempMenuWrapper.find('.chapter_select').remove();
                                        tempMenuWrapper.find('.option').not('.option_sticky').remove();
                                        var innerMenuWrapper = tempMenuWrapper.find('.wrap');
                                        if (innerMenuWrapper.length){
                                            innerMenuWrapper.empty();
                                            innerMenuWrapper.append(innerMarkup);
                                            } else {
                                            tempMenuWrapper.append(innerMarkup);
                                            }
                                        $('.option_message:gt(0)', tempMenuWrapper).triggerSilentClick();
                                        }

                                    // If this was a mission select loading, auto-click the proper chapter
                                    if (currentMenuSelect == 'this_battle_token'){
                                        //console.log('auto-click battle option!');
                                        // Check to see which one should be displayed first and autoclick it
                                        if ($('.chapter_link_active', tempMenuWrapper).length){ var firstChapterLink = $('.chapter_link_active', tempMenuWrapper); }
                                        else { var firstChapterLink = $('.chapter_link[data-chapter]:first-child', tempMenuWrapper); }
                                        firstChapterLink.triggerSilentClick();
                                    }

                                    // Trigger the callback function
                                    tempCallbackFunction();

                                    /*
                                    // MAYBE TODO? AUTO-CLICK WHEN ONLY ONE ROBOT?
                                    // If this was a robot select loading, auto-click through if only one robot
                                    if (currentMenuSelect == 'this_player_robots'){
                                        //console.log('auto-click robot option?');
                                        // Check to see which one should be displayed first and autoclick it
                                        var $availableRobots = $('.option[data-token][data-child="true"]', tempMenuWrapper);
                                        var $confirmButton = $('.option[data-token][data-parent="true"]', tempMenuWrapper);
                                        var numRobotsAvailable = $availableRobots.length;
                                        //console.log('numRobotsAvailable =', numRobotsAvailable);
                                        if (numRobotsAvailable === 1){
                                            //console.log('Only one robot! AUTO CLICK IT!');
                                            $availableRobots.first().triggerSilentClick();
                                            $confirmButton.triggerSilentClick();
                                            }

                                        }
                                    */

                                    }
                                });

                            }

                        });

                }

            } else {

                // DEBUG
                //console.log('currentMenuSelect is undefined');

                // Trigger the callback function anyway
                tempCallbackFunction();

            }


            };

        // Create the temporary fadein function for this menu
        var tempFadeinFunction = function(tempCallbackFunction){
            //console.log('tempReloadFunction(tempCallbackFunction:', typeof tempCallbackFunction, ')');
            if (tempCallbackFunction == undefined){ tempCallbackFunction = function(){}; }
            if (thisReadyRoom){ tempReadyRoomFunction('fadein'); }

            // DEBUG
            //console.log('tempFadeinFunction triggered');

            // Create the function to be executed after the menu has faded out
            var tempFadeoutFunction = function(){

                // DEBUG
                //console.log('.menu[data-step]:not(.menu_hide) has completed animation');

                // Once the menu is faded, remove it from display
                $(this).addClass('menu_hide').css({opacity:0,marginLeft:'0',marginRight:'0'});

                // Attempt to send a message to the iframe that it's being hidden
                // var $iframe = $(this).find('iframe');
                // TODO find a way to send a message to the iframe so that it can revert unsaved audio changes


                // Check if the stepNumber is numeric or not
                if (switchOptions.stepNumber !== false){

                    // Collect the main banner title
                    var thisBanner = $('.banner', thisContext);
                    var thisBannerTitle = thisBanner.attr('title');
                    // Collect a reference to the current menus
                    var thisMenu = $('.menu[data-step="'+switchOptions.stepNumber+'"]', thisContext);
                    var thisMenuTitle = thisMenu.attr('data-title');

                    // Update the banner text with this menu subtitle
                    //$('.title', thisBanner).html(thisBannerTitle+' : '+thisMenuTitle);
                    // Check how many choices are available
                    var thisMenuChoices = $('.option[data-token]:not(.option_disabled):not(.option[data-parent])', thisMenu);
                    //console.log('Menu choices : '+thisMenuChoices.length);
                    // Check if there is only one menu-choice available and skip is enabled
                    if (switchOptions.autoSkip == 'true' && thisMenuChoices.length <= 1){
                        //console.log('Auto Skip triggered...');
                        // Secretly unhide the current menu and auto-click the only available option
                        thisMenu.css({opacity:0}).removeClass('menu_hide');
                        thisMenuChoices.eq(0).triggerSilentClick();
                        } else {
                        // Unhide the current menu so the user can pick
                        thisMenu.css(slideInAnimation).removeClass('menu_hide').animate({opacity:1.0,marginLeft:'0',marginRight:'0'}, 400, 'swing');
                        }

                    } else if (switchOptions.stepName !== false){

                    // Collect the main banner title
                    var thisBanner = $('.banner', thisContext);
                    var thisBannerTitle = thisBanner.attr('title');
                    // Collect a reference to the current menus
                    var thisMenu = $('.menu[data-step="'+switchOptions.stepName+'"]', thisContext);
                    var thisMenuTitle = thisMenu.attr('data-title');
                    // Update the banner text with this menu subtitle
                    //$('.title', thisBanner).html(thisBannerTitle+' : '+thisMenuTitle);
                    // Unhide the current menu so the user can pick
                    thisMenu.css(slideInAnimation).removeClass('menu_hide').animate({opacity:1.0,marginLeft:'0',marginRight:'0'}, 400, 'swing');

                    } else if (switchOptions.redirectLink !== false){

                    // DEBUG
                    //console.log('Triggering redirect to '+switchOptions.redirectLink);

                    // Fade the prototype out of view and redirect on completion
                    thisContext.animate({opacity:0}, 500, 'swing', function(){
                        // Loop through all battle options and generate request data
                        var requestType = 'session';
                        var requestData = '';
                        for (optionToken in battleOptions){
                            // Collect the option token and value
                            var thisOptionToken = optionToken;
                            var thisOptionValue = battleOptions[optionToken];
                            // Generate the request data and post it to the server
                            requestData += 'battle_settings,'+thisOptionToken+','+thisOptionValue+';';
                            }
                        // Post the generated request data to the server and wait for a reply
                        $.post('scripts/script.php',{requestType:requestType,requestData:requestData},function(data){
                            //alert(data);
                            // Execute the onComplete function
                            switchOptions.onComplete();
                            // Redirect to the string location passed as the stepNumber
                            window.location.href = switchOptions.redirectLink;
                            });
                        });
                    }

                    // Execute the onComplete function
                    switchOptions.onComplete();
                };

            // Automatically fade-out the previous menu screen
            $('.menu[data-step]:not(.menu_hide)', thisContext).animate(slideOutAnimation, switchOptions.slideDuration, 'swing', tempFadeoutFunction);

            // Remove any running classes from the player sprites in the banner until we know they're needed
            // (do not remove if the next step is undefined / aka we are entering a battle)
            if (switchOptions.stepNumber !== false){
                $('.banner .option_this-player-select', thisContext).removeClass('is_running');
                }

            // Execute option-specific commands for special cases
            //console.log('switchOptions.stepNumber =', switchOptions.stepNumber);
            //console.log('currentMenuSelect =', currentMenuSelect);
            switch (currentMenuSelect){
                case 'this_battle_token': {

                    // Prevent the player from fighting themselves in battle
                    var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
                    var tempMenu = $('.menu[data-select="this_battle_token"]', thisContext);
                    var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
                    var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
                    var availableMissions = $('.option[data-token]', tempShowOptionWrapper);
                    $('.header', tempMenu).find('.count').html('Mission Select ('+(availableMissions.length == 1 ? '1 Mission' : availableMissions.length+' Missions')+')');
                    $('.menu[data-select="this_battle_token"] .header', thisContext).attr('data-player', battleOptions['this_player_token']);
                    $('.menu[data-select="this_player_robots"] .header', thisContext).attr('data-player', battleOptions['this_player_token']);

                    // Break when done this case
                    break;

                    }
                case 'this_player_robots': {

                    // Update the player option in the banner to the "running" sprite
                    $('.banner .option_this-player-select', thisContext).addClass('is_running');

                    // Prevent the player from fighting themselves in battle
                    var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
                    var tempMenu = $('.menu[data-select="this_player_robots"]', thisContext);
                    var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
                    var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
                    var tempWrapper = $('.option_wrapper[data-condition="'+tempCondition.replace('=', '\\=')+'"]', tempMenu);
                    var availableRobots = $('.option[data-child]', tempWrapper);
                    var tempMenuHeader = $('.header', tempMenu);
                    tempMenuHeader.find('.count').html('Robot Select ('+(availableRobots.length == 1 ? '1 Robot' : availableRobots.length+' Robots')+')');

                    // Break when done this case
                    break;

                    }
                case '': {

                    //console.log('there be problems, hunny...');
                    break;

                }
                default: {

                    break;

                    }
                }

            // Trigger the prototype resize function now that data has been refreshed
            windowResizePrototype();

            // Trigger the callback function, whatever it is
            tempCallbackFunction();

            };

        // Trigger the reload function for this menu
        tempReloadFunction(tempFadeinFunction);


        }

}

// Define a function for updating the zenny amount in the prototype banner
function prototype_update_zenny(newZenny){
    var thisZennyContainer = $('.banner .subpoints .amount.zenny', thisPrototype);
    thisZennyContainer.html(newZenny);
}

// Define a function for updating the battle point amount in the prototype banner
function prototype_update_battle_points(newPoints){
    var thisPointsContainer = $('.banner .points .amount .value', thisPrototype);
    thisPointsContainer.html(newPoints);
}

// Define a function for updating the ranking position in the prototype banner
function prototype_update_leaderboard_rank(newRank){
    var thisRankContainer = $('.banner .points .amount .place', thisPrototype);
    thisRankContainer.html(newRank);
}

// Define a function for updating profile settings (name, avatar, etc.) in the prototype banner
function prototype_update_profile_settings(newSettings){
    if (typeof newSettings === 'undefined'){ return false; }
    //console.log('prototype_update_profile_settings(newSettings)', newSettings);
    // Collect references to profile-affected elements in the prototype banner
    var $protoBanner = $('.banner', thisPrototype);
    var $userInfo = $('.options_userinfo', $protoBanner);
    var $userNameDisplay = $('.info_username label', $userInfo);
    var $userImageSprite = $('.sprite_wrapper .sprite.base', $userInfo);
    var $userImageShadow = $('.sprite_wrapper .sprite.shadow', $userInfo);
    var $fieldTypeElements = $('.field_type', $protoBanner);
    var $headerTypeElements = $('.menu .header.header_types', thisPrototype);
    //console.log('$userInfo.length = ', $userInfo.length);
    //console.log('$userNameDisplay.length = ', $userNameDisplay.length);
    //console.log('$userImageSprite.length = ', $userImageSprite.length);
    //console.log('$userImageShadow.length = ', $userImageShadow.length);
    //console.log('$fieldTypeElements.length = ', $fieldTypeElements.length);
    //console.log('$headerTypeElements.length = ', $headerTypeElements.length);
    // If a new display name was provided, update it in the banner
    if (typeof newSettings['user_name_display'] !== 'undefined'
        && newSettings['user_name_display'].length
        && $userNameDisplay.length){
        var displayName = newSettings['user_name_display'];
        $userNameDisplay.html(displayName);
        }
    // If a new avatar image was provided, update it in the banner
    if (typeof newSettings['user_image_path'] !== 'undefined'
        && newSettings['user_image_path'].length
        && $userImageSprite.length
        && $userImageShadow.length){
        var imageParts = newSettings['user_image_path'].split('/');
        var imageSize = imageParts[2] + 'x' + imageParts[2];
        var imagePath = 'images/' + imageParts[0] + '/' + imageParts[1] + '/';
        var imageName = 'sprite_left_' + imageSize + '.png';
        var spriteMarkup = '<span class="sprite sprite_'+imageSize+' sprite_'+imageSize+'_00" style="background-image: url('+imagePath+imageName+'?'+gameSettings.cacheTime+');"></span>';
        $userImageSprite.empty().append(spriteMarkup);
        $userImageShadow.empty().append(spriteMarkup);
        }
    // If new profile colour/colours were provided, update them in the banner
    if (typeof newSettings['user_colour_token'] !== 'undefined'
        && $fieldTypeElements.length){
        var newColourToken = newSettings['user_colour_token'];
        if (newColourToken.length){
            if (typeof newSettings['user_colour_token2'] !== 'undefined'
                && newSettings['user_colour_token2'].length){
                newColourToken += '_'+newSettings['user_colour_token2'];
                }
            } else {
            newColourToken = 'none';
            }
        $fieldTypeElements.each(function(){
            var $thisElement = $(this);
            $thisElement.attr('class', function(i, c){
                return c.replace(/(^|\s)field_type_[-_a-z]+($|\s)+/g, '$1$2');
                }).addClass('field_type_'+newColourToken);
            });
        $headerTypeElements.each(function(){
            var $thisElement = $(this);
            $thisElement.attr('class', function(i, c){
                return c.replace(/(^|\s)type_[-_a-z]+($|\s)+/g, '$1$2');
                }).addClass('type_'+newColourToken);
            });
        }
    // Return true on success
    return true;
}

// Define a function for marking a prototype menu frame as having been seen already
var menuFrameSeenTimeout = false;
function prototype_menu_frame_seen(frameToken){
    //console.log('prototype_menu_frame_seen(frameToken:', frameToken, ')');
    //console.log('gameSettings.menuFramesSeen (before) =', gameSettings.menuFramesSeen);
    if (gameSettings.menuFramesSeen.indexOf(frameToken) === -1){
        gameSettings.menuFramesSeen.push(frameToken);
    }
    //console.log('gameSettings.menuFramesSeen (after) =', gameSettings.menuFramesSeen);
    if (menuFrameSeenTimeout !== false){ clearTimeout(menuFrameSeenTimeout); }
    menuFrameSeenTimeout = setTimeout(function(){
        //console.log('update server w/ gameSettings.menuFramesSeen =', gameSettings.menuFramesSeen);
        $.post('scripts/script.php',{requestType:'session',requestData:'battle_settings,menu_frames_seen,'+gameSettings.menuFramesSeen.join('|')});
        }, 1000);
}

// Define a function for updating the prototype menu given seen/unseen link status
function prototype_menu_links_refresh(){
    //console.log('prototype_menu_links_refresh()');
    //console.log('gameSettings.menuFramesSeen =', gameSettings.menuFramesSeen);
    var $thisPrototype = $('#prototype');
    if ($thisPrototype.length){
        var $bannerLinks = $('.banner .link[data-step]', $thisPrototype);
        $bannerLinks.each(function(){
            var $bannerLink = $(this);
            var stepToken = $bannerLink.attr('data-step');
            var stepIsNumeric = !isNaN(stepToken);
            $('i.new', $bannerLink).remove();
            if (!stepIsNumeric
                && gameSettings.menuFramesSeen.indexOf(stepToken) === -1){
                $bannerLink.append('<i class="new type electric"></i>');
                }
            });
        }
}

// Define a function for updating prototype game settings from anywhere
function prototype_update_game_settings(newSettings){
    //console.log('prototype_update_game_settings(newSettings) w/ newSettings:', newSettings);

    // If provided, update the spriteRenderMode in the prototype settings and markup
    if (typeof newSettings.spriteRenderMode !== 'undefined'){
        gameSettings.spriteRenderMode = newSettings.spriteRenderMode;
        $('#prototype').attr('data-render-mode', gameSettings.spriteRenderMode);
        }

    // If provided, update the battleButtonMode in the prototype settings and markup
    if (typeof newSettings.battleButtonMode !== 'undefined'){
        gameSettings.battleButtonMode = newSettings.battleButtonMode;
        $('#prototype').attr('data-button-mode', gameSettings.battleButtonMode);
        }

}