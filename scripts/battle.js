// Define globally accessible variables
var thisBattle = false;

// Create the document ready events
$(document).ready(function(){
  
  // Collect global object references
  var thisBattle = $('#battle');
  
  // Attach the scrollbar to the battle events container
  //console.log('adding perfect scrollbar to console wrapper');
  $('#console .wrapper', thisBattle).perfectScrollbar({suppressScrollX: true});  
  
  // Start playing the appropriate stage music
  parent.mmrpg_music_load(gameSettings.fieldMusic, true, false);
  // Preload battle related image files
  mmrpg_preload_assets();
  // Fade in the battle screen slowly
  thisBattle.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, Math.ceil(gameSettings.eventTimeout * 3), 'swing', function(){
    // Automatically trigger a click on the start button
    //$('#actions_start a[data-action=start]', thisBattle).trigger('click');
    // Collect all the elements to be animated
    var canvasContext = $('#canvas', thisBattle);
    thisBattle.waitForImages(function(){
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