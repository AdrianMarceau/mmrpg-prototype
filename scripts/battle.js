// Define global variables
var $thisPrototype = false;
var $rogueStar = false;

// Create the document ready events
$(document).ready(function(){
    $thisPrototype = $('#mmrpg');

    // Start playing the appropriate stage music
    parent.mmrpg_music_load(gameSettings.fieldMusic, true, false);
    // Preload battle related image files
    mmrpg_preload_assets();
    // Fade in the battle screen slowly
    var thisContext = $('#battle');
    if (thisContext.hasClass('fastfade')){
        // Fade the battle in quickly, starting the action trigger ASAP
        thisContext.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, Math.ceil(gameSettings.eventTimeout * 3), 'swing');
        // Collect all the elements to be animated
        var canvasContext = $('#canvas', thisContext);
        thisContext.waitForImages(function(){
            $rogueStar.addClass('hidelabel');
            // Automatically send the start action to the data api
            $('#animate').css({opacity:1});
            $('#canvas .canvas_overlay_header').css({opacity:1}).removeClass('canvas_overlay_hidden');
            mmrpg_start_animation();
            mmrpg_action_trigger('start', false);
            }, false, true);
        } else {
        // Fade the battle in normally, one layer at a time before loading
        thisContext.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, Math.ceil(gameSettings.eventTimeout * 3), 'swing', function(){
            // Automatically trigger a click on the start button
            //$('#actions_start a[data-action=start]', thisContext).trigger('click');
            // Collect all the elements to be animated
            var canvasContext = $('#canvas', thisContext);
            thisContext.waitForImages(function(){
                $rogueStar.addClass('hidelabel');
                // Fade the battle canvas startup elements into view
                mmrpg_battle_fadein_background(canvasContext, Math.ceil(gameSettings.eventTimeout * 2), function(){
                    // Fade in the foreground now so it loads at the same time as the robots
                    mmrpg_battle_fadein_foreground(canvasContext, Math.ceil(gameSettings.eventTimeout * 1), function(){
                        // Automatically send the start action to the data api
                        $('#animate').css({opacity:1});
                        $('#canvas .canvas_overlay_header').animate({opacity:1}, Math.ceil(gameSettings.eventTimeout * 2), 'swing', function(){ $(this).removeClass('canvas_overlay_hidden'); });
                        mmrpg_start_animation();
                        mmrpg_action_trigger('start', false);
                        });
                    });
                }, false, true);
            });
        }

    // Collect a reference to the continue button
    var actionContinue = $('.action_continue', gameActions);

    // Create an event for the button hover
    $('.button', gameActions).live('hover', function(){
        //console.log('hover?');
        $('.button', gameActions).removeClass('button_hover');
        if (!$(this).hasClass('button_disabled')){
            $(this).addClass('button_hover');
            }
        });

    // Trigger a click on the continue button
    var confirmKeys = [32,13];
    var previousKeys = [37,38];
    var forwardKeys = [39,40];
    $(this).keydown(function(evt){
        //console.log('key-down '+evt.keyCode);
        // If the user has pressed the space bar
        if (confirmKeys.indexOf(evt.keyCode) != -1){ // space bar or enter key
            //console.log('space bar!');
            evt.preventDefault();
            var currentWrapper = $('.wrapper:visible', gameActions).first();
            var currentButtons = $('.button:not(.button_disabled)', currentWrapper);
            var currentButtonCount = currentButtons.length;
            var hoverButton = $('.button_hover', currentWrapper);
            var hoverButtonOrder = hoverButton.attr('data-order') != undefined ? parseInt(hoverButton.attr('data-order')) : 0;
            var firstButton = currentButtons.first();
            var firstButtonOrder = firstButton.attr('data-order') != undefined ? parseInt(firstButton.attr('data-order')) : 0;
            if (actionContinue.is(':visible')){
                actionContinue.trigger('click');
                } else if (hoverButton.length){
                hoverButton.trigger('click');
                } else if (firstButton.length){
                firstButton.trigger('click');
                }
            }
        // Else if the user has pressed a previous key
        else if (previousKeys.indexOf(evt.keyCode) != -1){ // left, up key
            //console.log('left, up key!');
            evt.preventDefault();
            if (!actionContinue.is(':visible')){
                var currentWrapper = $('.wrapper:visible', gameActions).first();
                var currentButtonCount = $('.button:not(.button_disabled)', currentWrapper).length;
                var totalButtonCount = $('.button', currentWrapper).length;
                var hoverButton = $('.button_hover', currentWrapper);
                var hoverButtonOrder = hoverButton.attr('data-order') != undefined ? parseInt(hoverButton.attr('data-order')) : 0;
                if (hoverButton.length){
                    hoverButton.removeClass('button_hover');
                    //console.log('hoverButtonOrder = '+hoverButtonOrder);
                    var previousAction = false;
                    var nextButtonOrder = hoverButtonOrder - 1;
                    // Loop through the previous buttons until we find an active one
                    while (!previousAction.length && nextButtonOrder > 0){
                        //console.log('nextButtonOrder (attempt) = '+nextButtonOrder);
                        previousAction = $('.button[data-order='+nextButtonOrder+']:not(.button_disabled)', currentWrapper);
                        if (!previousAction.length){ nextButtonOrder -= 1; }
                        }
                    // No no active button was found looping backwards, start from beginning
                    if (!previousAction.length){
                        // Start the counter at the last element and then start looping again
                        var nextButtonOrder = totalButtonCount;
                        while (!previousAction.length && nextButtonOrder > 0){
                            //console.log('nextButtonOrder (attempt) = '+nextButtonOrder);
                            previousAction = $('.button[data-order='+nextButtonOrder+']:not(.button_disabled)', currentWrapper);
                            if (!previousAction.length){ nextButtonOrder -= 1; }
                            }
                        // If we STILL haven't found a new button based on order data
                        if (!previousAction.length){
                            // If all else fails, simply first non-disabled button on the panel
                            //console.log('!previousAction.length .button:not(.button_disabled)');
                            previousAction = $('.button:not(.button_disabled)', currentWrapper);
                            } else {
                            //console.log('nextButtonOrder (final) = '+nextButtonOrder);
                            }
                        }
                    // Finally, add the hover class to the finalized element
                    previousAction.addClass('button_hover');
                    } else {
                    // If all else fails, simply last non-disabled button on the panel
                    //console.log('!previousAction.length .button[data-order='+totalButtonCount+']');
                    var previousAction = $('.button[data-order='+totalButtonCount+']', currentWrapper);
                    previousAction.addClass('button_hover');
                    }
                }
            }
        // Else if the user has pressed a forward key
        else if (forwardKeys.indexOf(evt.keyCode) != -1){ // right, down key
            //console.log('right, down key!');
            evt.preventDefault();
            if (!actionContinue.is(':visible')){
                var currentWrapper = $('.wrapper:visible', gameActions).first();
                var currentButtonCount = $('.button:not(.button_disabled)', currentWrapper).length;
                var totalButtonCount = $('.button', currentWrapper).length;
                var hoverButton = $('.button_hover', currentWrapper);
                var hoverButtonOrder = hoverButton.attr('data-order') != undefined ? parseInt(hoverButton.attr('data-order')) : 0;
                if (hoverButton.length){
                    hoverButton.removeClass('button_hover');
                    //console.log('hoverButtonOrder = '+hoverButtonOrder);
                    var forwardAction = false;
                    var nextButtonOrder = hoverButtonOrder + 1;
                    // Loop through the forward buttons until we find an active one
                    while (!forwardAction.length && nextButtonOrder <= totalButtonCount){
                        //console.log('nextButtonOrder (attempt) = '+nextButtonOrder);
                        forwardAction = $('.button[data-order='+nextButtonOrder+']:not(.button_disabled)', currentWrapper);
                        if (!forwardAction.length){ nextButtonOrder += 1; }
                        }
                    // No no active button was found looping backwards, start from beginning
                    if (!forwardAction.length){
                        // Start the counter at the last element and then start looping again
                        var nextButtonOrder = 1;
                        while (!forwardAction.length && nextButtonOrder <= totalButtonCount){
                            //console.log('nextButtonOrder (attempt) = '+nextButtonOrder);
                            forwardAction = $('.button[data-order='+nextButtonOrder+']:not(.button_disabled)', currentWrapper);
                            if (!forwardAction.length){ nextButtonOrder += 1; }
                            }
                        // If we STILL haven't found a new button based on order data
                        if (!forwardAction.length){
                            // If all else fails, simply first non-disabled button on the panel
                            //console.log('!forwardAction.length .button:not(.button_disabled)');
                            forwardAction = $('.button:not(.button_disabled)', currentWrapper);
                            } else {
                            //console.log('nextButtonOrder (final) = '+nextButtonOrder);
                            }
                        }
                    // Finally, add the hover class to the finalized element
                    forwardAction.addClass('button_hover');
                    } else {
                    // If all else fails, simply first non-disabled button on the panel
                    //console.log('!forwardAction.length .button[data-order=1]');
                    var forwardAction = $('.button[data-order=1]', currentWrapper);
                    forwardAction.addClass('button_hover');
                    }
                }
            }
        });

    // Define the live Rogue Star ticker functionality if present
    $rogueStar = $('#canvas .rogue_star', $thisPrototype);
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
        $rogueStar.removeClass('loading');

        }


});

// Define a function for animation the canvas background startup elements
function mmrpg_battle_fadein_background(animateCanvas, animateDuration, onComplete){
    // Collect or define the onComplete function
    var onComplete = onComplete != undefined ? onComplete : function(){};
    // Collect the background canvas and event elements
    var animateBackgroundCanvas = $('.animate_fadein', animateCanvas).filter('.background_canvas');
    var animateBackgroundEvent = $('.animate_fadein', animateCanvas).filter('.background_event');
    // Fade the foreground into view and upward into place
    if (animateBackgroundCanvas.length){
        animateBackgroundCanvas.css({opacity:0,left:'auto',right:0,width:'1124px'}).removeClass('animate_fadein').animate({opacity:1,width:'100%'}, animateDuration, 'swing', function(){
            $(this).css({left:0,right:'auto'});
            if (animateBackgroundEvent.length){
                animateBackgroundEvent.css({opacity:0}).removeClass('animate_fadein').animate({opacity:1}, animateDuration, 'swing', onComplete);
                } else {
                onComplete();
                }
            });
        } else {
        onComplete();
        }
}

// Define a function for animation the canvas foreground startup elements
function mmrpg_battle_fadein_foreground(animateCanvas, animateDuration, onComplete){
    // Collect or define the onComplete function
    var onComplete = onComplete != undefined ? onComplete : function(){};
    // Collect the foreground canvas and event elements
    var animateForegroundCanvas = $('.animate_fadein', animateCanvas).filter('.foreground_canvas');
    var animateForegroundEvent = $('.animate_fadein', animateCanvas).filter('.foreground_event');
    // Fade the foreground into view and upward into place
    if (animateForegroundCanvas.length){
        //animateForegroundCanvas.css({opacity:0,left:'0',right:'auto',width:'1124px'}).removeClass('animate_fadein').animate({opacity:1,width:'100%'}, animateDuration, 'swing', function(){
        animateForegroundCanvas.css({opacity:0,top:'100px'}).removeClass('animate_fadein').animate({opacity:1,top:0}, animateDuration, 'swing', function(){
            //$(this).css({left:'auto',right:0});
            if (animateForegroundEvent.length){
                animateForegroundEvent.css({opacity:0}).removeClass('animate_fadein').animate({opacity:1}, animateDuration, 'swing', onComplete);
                } else {
                onComplete();
                }
            });
        } else {
        onComplete();
        }
}