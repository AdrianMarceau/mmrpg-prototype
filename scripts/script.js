// Initialize the MMRPG global variables
var mmrpgBody = mmrpgBody;
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
gameSettings.wapFlag = false; // whether or not this game is running in mobile mode
gameSettings.wapFlagIphone = false; // whether or not this game is running in mobile iphone mode
gameSettings.wapFlagIpad = false; // whether or not this game is running in mobile iphone mode
gameSettings.eventTimeout = 1250; // default animation frame base internal
gameSettings.eventAutoPlay = true; // whether or not to automatically advance events
gameSettings.eventCrossFade = true; // whether or not to canvas events have crossfade animation
gameSettings.idleAnimation = true; // default to allow idle animations
gameSettings.indexLoaded = false; // default to false until the index is loaded
gameSettings.autoScrollTop = true; // default to true to prevent too much scrolling
gameSettings.autoResizeWidth = true; // allow auto reszing of the game window width
gameSettings.autoResizeHeight = true; // allow auto reszing of the game window height
gameSettings.currentBodyWidth = 0; // collect the current window width and update when necessary
gameSettings.currentBodyHeight = 0; // collect the current window width and update when necessary
gameSettings.userNumber = 0; // default to zero so we pull demo info unless otherwise stated
gameSettings.allowEditing = true; // default to true to allow all editing unless otherwise stated
gameSettings.autoKeepAlive = false; // default to false unless necessary, keeps session alive
gameSettings.baseHref = ''; // default to empty as not to throw undefined errors

// Define the WEBSITE global settings variables
var websiteSettings = {};
websiteSettings.currentHref = ''; // default to empty
websiteSettings.currentPage = ''; // default to empty
websiteSettings.currentSub = ''; // default to empty
websiteSettings.currentCat = ''; // default to empty
websiteSettings.currentToken = ''; // default to empty
websiteSettings.currentNum = 0; // default to zero
websiteSettings.currentId = 0; // default to zero
    
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
      gameEngineSubmitTimeout = setTimeout(gameEngineSubmitFunction, 120000);    
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
  gameSettings.wapFlagIphone = (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) ? true : false;
  gameSettings.wapFlagIpad = navigator.userAgent.match(/iPad/i) ? true : false;
  
  // Update the decompressed array values
  decompressActionArray = [
    'sprite_right', 'sprite_left', 'player_right', 'player_left', 'ability_right', 'ability_left', 'attachment_right', 'attachment_left',
    'field_multiplier', 'field_type', 'player_type', 'robot_type', 'ability_type', 'attachment_type', 'mugshot_right', 'mugshot_left', 'icon_right', 'icon_left',
    'robot_level', 'robot_experience', 'robot_energy', 'robot_attack', 'robot_defense', 'robot_speed', 'action_ability', 'action_option', 'action_scan', 'action_target', 'action_item',
    'field_name', 'player_name', 'robot_name', 'ability_name', 'player_shadow', 'robot_shadow',
    'class="main_actions main_actions_hastitle', 'class="main_actions', 'class="sub_actions', 'class="canvas_overlay_footer', 'class="overlay_label', 'class="overlay_multiplier',
    'class="button', 'class="text', 'class="subtext', 'class="multi', 'class="type', 'class="number"', 'class="cross"',
    'class="level', 'class="experience', 'class="energy', 'class="attack', 'class="defense', 'class="speed',
    'class="sprite', 'class="field', 'class="player', 'class="robot', 'class="attachment', 'class="mugshot',
    'data-tooltip-align="', 'data-tooltip-type="', 'data-tooltip="', 'data-order="',
    'data-robotid="', 'data-playerid="', 'data-abilityid="', 'data-shadowid="', 'data-mugshotid="', 'data-detailsid="',
    'type="button"', 'data-key="', 'data-type="', 'data-panel="', 'data-action="', 'data-preload="', 'data-size="', 'data-frame="', 'data-scale="', 'data-direction="', 'data-position="', 'data-status="', 'data-target="',
    'images/players_shadows/', 'images/players/', 'images/robots_shadows/', 'images/robots/', 'images/abilities/item-', 'images/abilities/',
    'background-image: url(', 'background-position:', 'background-size:', 'border-color:', '-webkit-transform:', '-moz-transform:', 'transform:', ' translate(', ' rotate(',
    '</div><div ', '</span><span ', '<strong>', '</strong>', '</label>',
    'abilities', 'attachment', 'position', 'disabled', 'prototype', 'maintext',
    'experience', '-support', '-assault', 'buster-shot', 'dr-light', 'dr-cossack', 'light-buster', 'wily-buster', 'cossack-buster', '-support', '-capsule',
    'Active Position', 'Bench Position', 'Abilities', ' Accuracy', ' Weapons', ' Attack', ' Defense', ' Recovery', ' Experience', ' Support', 'Buster Shot', 'Dr. Light', 'Dr. Wily', 'Dr. Cossack', 'Light Buster', 'Wily Buster', 'Cossack Buster', 'Neutral ', ' Capsule',
    ' title="', ' class="', ' style="', 'sprite_40x40_', 'sprite_80x80_', 'sprite_160x160_', '_left_40x40', '_left_80x80', '_left_160x160',  '_right_40x40', '_right_80x80', '_right_160x160',
    '.png?'+gameSettings.cacheTime+');',
    ];   
  
  /*
   * INDEX EVENTS
   */
  
  if (mmrpgBody.length){

    // Update the dimensions
    gameSettings.currentBodyWidth = $(document).width(); //mmrpgBody.outerWidth();
    gameSettings.currentBodyHeight = $(document).height(); //mmrpgBody.outerHeight();
    
    // Tooltip only Text
    //console.log('assigning event for '+document.URL+';\n gameSettings.currentBodyWidth = '+gameSettings.currentBodyWidth+';\n gameSettings.currentBodyHeight = '+gameSettings.currentBodyHeight+'; ');

    // Only attach hover tooltips if NOT in mobile mode
    if (!gameSettings.wapFlag && !gameSettings.wapFlagIphone && !gameSettings.wapFlagIpad){
    
      // Define the function for showing the tooltip
      var showTooltipFunction = function(e){        
        var thisElement = $(this);
        $('.tooltip', mmrpgBody).empty();
        var thisDate = new Date();        
        var thisTime = thisDate.getTime();
        //console.log('starting the tooltip at '+thisTime);         
        var thisClassList = thisElement.attr('class') != undefined ? thisElement.attr('class').split(/\s+/) : '';        
        var thisTitle = thisElement.attr('data-backup-title') != undefined ? thisElement.attr('data-backup-title') : (thisElement.attr('title') != undefined ? thisElement.attr('title') : '');
        var thisTooltip = thisElement.attr('data-tooltip') != undefined ? thisElement.attr('data-tooltip') : '';  
        if (!thisTitle.length && !thisTooltip.length){ return false; }
        else if (thisTitle.length && !thisTooltip.length){ thisTooltip = thisTitle; }
        thisTooltip = thisTooltip.replace(/\n/g, '<br />').replace(/\|\|/g, '<br />').replace(/\|/g, '<span class="pipe">|</span>').replace(/\s?\/\/\s?/g, '<br />').replace(/\[\[([^\[\]]+)\]\]/ig, '<span class="subtext">$1</span>');   
        var thisTooltipAlign = thisElement.attr('data-tooltip-align') != undefined ? thisElement.attr('data-tooltip-align') : 'left';
        var thisTooltipType = thisElement.attr('data-tooltip-type') != undefined ? thisElement.attr('data-tooltip-type') : '';
        if (!thisTooltipType.length){        
          for (i in thisClassList){
            var tempClass = thisClassList[i] != undefined ? thisClassList[i].toString() : '';
            //console.log('tempClass = '+tempClass);
            if (tempClass.match(/^(field_|player_|robot_|ability_)?type$/) || tempClass.match(/^(field_|player_|robot_|ability_)?type_/) || tempClass.match(/^(energy|weapons|attack|defense|speed|light|cossack|wily|experience|level|damage|recovery|none|cutter|impact|freeze|explode|flame|electric|time|earth|wind|water|swift|nature|missile|crystal|shadow|space|shield|laser|copy)(_|$)/)){ 
              //console.log('tempClass match!');
              thisTooltipType += tempClass+' '; 
              }
            //console.log('thisTooltipType = '+thisTooltipType);
            }
          }
        if (!thisTooltipType.length){ 
          thisTooltipType = 'type none'; 
          }        
        //console.log('thisTitle : '+thisTitle);
        //console.log('append and trigger animation at '+thisTime);        
        thisElement.attr('data-backup-title', thisTitle).removeAttr('title');  
        if (!$('.tooltip', mmrpgBody).length){ $('<p class="tooltip '+thisTooltipType+'"></p>').html('<span class="message" style="text-align:'+thisTooltipAlign+';">'+thisTooltip+'</span>').appendTo(mmrpgBody).fadeIn('fast'); }
        else { $('.tooltip', mmrpgBody).removeClass().addClass('tooltip').addClass(thisTooltipType).html('<span class="message" style="text-align:'+thisTooltipAlign+';">'+thisTooltip+'</span>').fadeIn('fast'); }
        //$('.tooltip', mmrpgBody).css({width:''});
        //var toolwidth = $('.tooltip', mmrpgBody).outerWidth();
        //$('.tooltip', mmrpgBody).css({width:toolwidth+'px'});
        var thisDate = new Date();        
        var thisTime = thisDate.getTime();
        //console.log('animation should be done at '+thisTime);
        alignTooltipFunction.call(this, e);        
        };
        
      // Define the function for positioning the tooltip
      var alignTooltipFunction = function(e){
        var thisDate = new Date();        
        var thisTime = thisDate.getTime();
        //console.log('trigger the align function at '+thisTime);
        var mousex = e.pageX + 10;
        var mousey = e.pageY + 5;
        var toolwidth = $('.tooltip', mmrpgBody).outerWidth() + 20;
        var toolheight = $('.tooltip', mmrpgBody).outerHeight() + 10;
        //console.log('START '+
        //  'gameSettings.currentBodyWidth = '+gameSettings.currentBodyWidth+'; mousex = '+mousex+'; toolwidth = '+toolwidth+'; \n'+
        //  'gameSettings.currentBodyHeight = '+gameSettings.currentBodyHeight+'; mousey = '+mousex+'; toolheight = '+toolheight+'; \n'
        //  );
        if (gameSettings.currentBodyWidth - mousex < toolwidth){ mousex = gameSettings.currentBodyWidth - toolwidth; }
        if (gameSettings.currentBodyHeight - mousey < (toolheight + 50)){ mousey = mousey - (toolheight + 10); }
        //console.log('END '+
        //  'gameSettings.currentBodyWidth = '+gameSettings.currentBodyWidth+'; mousex = '+mousex+'; toolwidth = '+toolwidth+'; \n'+
        //  'gameSettings.currentBodyHeight = '+gameSettings.currentBodyHeight+'; mousey = '+mousex+'; toolheight = '+toolheight+'; \n'
        //  );
        $('.tooltip', mmrpgBody).css({ top: mousey, left: mousex });      
        };
      
      // Define the live MOUSEENTER events for any elements with a title tag (which should be many)
      var tooltipDelay = 1200; //600;
      var tooltipTimeout = false;
      var tooltipShowing = false;
      var tooltipSelector = '*[title],*[data-backup-title],*[data-tooltip]';
      $(tooltipSelector, mmrpgBody).live('mouseenter', function(e){
        e.preventDefault();
        if (tooltipTimeout == false){
          var thisDate = new Date();        
          var thisTime = thisDate.getTime();
          var thisObject = this;
          //console.log('set tooltip timeout for '+tooltipDelay+' at '+thisTime);
          tooltipTimeout = setTimeout(function(){
            tooltipShowing = true;
            showTooltipFunction.call(thisObject, e);
            }, tooltipDelay); 
          var thisElement = $(this);
          if (thisElement.attr('title')){ 
            thisElement.attr('data-backup-title', thisElement.attr('title')); 
            thisElement.removeAttr('title');
            }
          }
        });
      
      // Define the live MOUSEMOVE events for any elements with a title tag (which should be many)
      $(tooltipSelector, mmrpgBody).live('mousemove', function(e){
        if (!tooltipShowing){ return false; }        
        alignTooltipFunction.call(this, e);
        });
      
      // Define the live MOUSELEAVE events for any elements with a title tag (which should be many)
      $(tooltipSelector, mmrpgBody).live('mouseleave', function(e){
        e.preventDefault();
        var thisElement = $(this);
        $('.tooltip', mmrpgBody).empty();
        //thisElement.attr('title', thisElement.attr('data-backup-title'));
        var thisDate = new Date();        
        var thisTime = thisDate.getTime();
        //console.log('clear tooltip timeout at '+thisTime);
        clearTimeout(tooltipTimeout);
        tooltipTimeout = false;
        tooltipShowing = false;
        });
      
        //$('*', mmrpgBody).click(function(e){ $('.tooltip', mmrpgBody).remove(); });
        $('*', mmrpgBody).click(function(e){ $('.tooltip', mmrpgBody).empty(); });
      
      }
    
  }
  
  // Ensure this is the battle document
  if (gameWindow.length){
    
    // Add click-events to the debug panel links
    $('a.battle', gamePrototype).live('click', function(e){
      var windowFrame = $('iframe', gameWindow);
      var thisLink = $(this).attr('href');
      if (windowFrame.attr('src') != 'about:blank'){
        e.preventDefault();
        var thisConfirm = 'Are you sure you want to switch battles?  Progress will be lost and all robots will be reset.';
        if (confirm(thisConfirm)){ 
        //if (true){ 
          windowFrame.attr('src', thisLink);
          return true; 
          }        
        } else { 
        windowFrame.attr('src', thisLink);
        return false; 
        }
      });
    
    // Set up the game music options
    if (true){     
               
      // Add the click-events to the music toggle button      
      $('a.toggle', gameMusic).bind('click touch', function(e){
        e.preventDefault();
        if (gameSettings.indexLoaded){
          //$('body').prepend('<div style="background-color: red;">WTF</div>'); // DEBUG
          if ($('iframe', gameWindow).hasClass('loading')){ $('iframe', gameWindow).css({opacity:0}).removeClass('loading').animate({opacity:1}, 1000, 'swing'); } // DEBUG
          if (gameMusic.hasClass('onload')){ 
            gameMusic.removeClass('onload');
            gameMusic.find('.start').remove();
            if (gameSettings.wapFlag){ 
              mmrpg_music_toggle(); 
              } 
            } else {
            mmrpg_music_toggle();  
            }      
          return true;          
          } else {
          return false;  
          }        
        });
      // Automatically load the title screen music
      mmrpg_music_load('misc/player-select');
      
      }
        
    /*
    $('a.toggle span', gameMusic).css({opacity:0}).html('PLAY');
    var musicTimeout = setTimeout(function(){
      $('a.toggle span', gameMusic).animate({opacity:1}, 2000, 'swing');
      }, 1000);
    */
    // Automatically start playing the music on load
    //mmrpg_music_play();
    
  } 
  
  
  /*
   * BATTLE EVENTS
   */
  
  // Ensure this is the battle document
  if (gameEngine.length){
    
    // Attach a submit event for tracking timestaps
    gameEngine.submit(function(){
      //console.log('gameEngine.submit() triggered, setting timeout');
      clearTimeout(gameEngineSubmitTimeout);
      gameEngineSubmitTimeout = false;
      gameEngineSubmitTimeout = setTimeout(gameEngineSubmitFunction, 120000);
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
      var thisPreload = $(this).attr('data-preload')  !== undefined ? $(this).attr('data-preload') : false;
      var thisTarget = $(this).attr('data-target')  !== undefined ? $(this).attr('data-target') : false;
      var thisPanel = $(this).parent().parent().attr('id');
      thisPanel = thisPanel.replace(/^actions_/i, '');
      //alert(thisPanel);
      // Trigger the requested action and return the result
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
  
  if (!gameSettings.wapFlag){
    
    // Remove the hard-coded heights for the main iframe
    $('iframe', gameWindow).removeAttr('width').removeAttr('height');
    
    // Trigger the windowResizeUpdate function automatically
    windowResizeUpdate('startup');
    window.onresize = function(){ return windowResizeUpdate('onresize'); }
    
  }
  
  
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
    
    // Prevent scrolling as long as the exclusion class isn't present
    if (false){
      /*
      // Prevent window scrolling, or scrolling of any kind, for mobile views
      $(document).bind('touchmove', function(e){
        //alert('touchmove');
        //alert('touchmove etarget : '.$(e.target).attr('class'));
        e.preventDefault();
        });
      $('*').live('touchstart', function(e) {
        //alert('touchstart etarget : '.$(e.target).attr('class'));
        this.onclick = this.onclick || function () { };
        });      
      */
    }
    

    
    // Change the body's orientation flag classes
    orientationModeUpdate('startup');
    window.onorientationchange = function(){ return orientationModeUpdate('onorientationchange'); }
    window.onresize = function(){ return orientationModeUpdate('onresize'); }
    if (gameSettings.autoScrollTop === true){ window.onscroll = function(){ return orientationModeUpdate('onscroll'); } }
        
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
        
  // If the auto keep alive has been set, trigger it
  if (gameSettings.autoKeepAlive){ pingWebsite(); }  
  
});

// Define a function for sending a pink to the website and keeping the session alive
var pingDuration = 1000 * 60 * 3; // 3 minutes
var pingTimeout = false;
var pingCounter = 0;
function pingWebsite(){
  // Increment the ping counter, just so we know where we are
  pingCounter++;
  console.log('post to website '+pingCounter+' ('+(pingDuration * (pingCounter - 1))+'ms)');
  // Do not actually ping the server on the first run, we just loaded the page
  if (pingCounter > 0){
    // Generate the post fields for the upcoming ping request
    var pingField = pingCounter % 2 == 0 ? 'ping' : 'pong';
    var pingPage = websiteSettings.currentHref;
    $.ajax({
      type: 'POST',
      url: gameSettings.baseHref,
      data: {'ping':pingField,'page':pingPage},
      success: function(markup, status){
        console.log(status+' : '+markup);
        return true;
      },
      error: function(markup, status){
        console.log(status+' : '+markup);
        return false;
        }
      });
    }
  // Clear the existing timeout if it exists, and set a new one
  clearTimeout(pingTimeout);
  pingTimeout = setTimeout(function(){
    return pingWebsite();
    }, pingDuration);
}

// Define a function for updating the window sizes
function windowResizeUpdate(updateType){
  // Define the base values to resize from
  var canvasHeight = 267;
  var consoleHeight = 256;
  var consoleMessageHeight = 64;
  var actionsHeight = 225;
  //console.log('windowResizeUpdate('+updateType+');\n', {canvasHeight:canvasHeight,consoleHeight:consoleHeight,consoleMessageHeight:consoleMessageHeight,actionsHeight:actionsHeight});
  
  // Check if this is the main window or if it's a child
  if (window === window.top){
    // Collect this window's width and height
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var gameWidth = gameWindow.width();
    var gameHeight = gameWindow.height();
    } else {
    // Collect the parent window's width and height
    var windowWidth = $(parent.window).width();
    var windowHeight = $(parent.window).height();
    var gameWidth = parent.gameWindow.width();
    var gameHeight = parent.gameWindow.height();        
    }
  
  // Update the dimensions
  gameSettings.currentBodyWidth = $(window).width(); //$(document).width(); //mmrpgBody.outerWidth();
  gameSettings.currentBodyHeight = $(window).height(); //$(document).height(); //mmrpgBody.outerHeight();
  //console.log({windowWidth:windowWidth,windowHeight:windowHeight,gameWidth:gameWidth,gameHeight:gameHeight,gameSettings:gameSettings});
  
  // Check if the window is in landscape mode and update the session
  var thisRequestType = 'session';
  var thisRequestData = 'index_settings,windowFlag,';  
  if (windowWidth >= (1024 + 12)){ $('body').addClass('windowFlag_landscapeMode'); thisRequestData += 'landscapeMode'; } 
  else { $('body').removeClass('windowFlag_landscapeMode'); thisRequestData += 'portraitMode'; }
  $.post('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
  //console.log('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
  
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
    gameConsole.find('.wrapper').css({height:(gameConsole.height())+'px'})
    }
    
    // If height reszing is allowed, update the window height
    if (gameSettings.autoResizeHeight != false){  
      //console.log('gameSettings.autoResizeHeight != false;\ngameWindow.height('+newGameHeight+');');
      gameWindow.height(newGameHeight);
      $('iframe', gameWindow).height(newGameHeight - 6);    
      }    

  // Reset the window scroll to center elements properly
  if (gameSettings.autoScrollTop == true){
    //console.log('gameSettings.autoScrollTop == true;\nwindow.scrollTo(0, 1);');
    window.scrollTo(0, 1);
    if (window !== window.top){ parent.window.scrollTo(0, 1); }    
    }
  

  // Tooltip only Text
  //console.log('resizing event for '+document.URL+';\n gameSettings.currentBodyWidth = '+gameSettings.currentBodyWidth+';\n gameSettings.currentBodyHeight = '+gameSettings.currentBodyHeight+'; ');  
  
  // Return true on success
  return true;  
}




// Define a function for updating the orientation Mode
function orientationModeUpdate(updateType){
  // Check if this is the main window or if it's a child
  if (window === window.top){
    // If this is the main window, collect it's orientation variable
    if (!isNaN(window.orientation)){ var orientationMode = (window.orientation == 0 || window.orientation == 180) ? 'portrait' : 'landscape'; } 
    else { var orientationMode = ($(window).width() < 980) ? 'portrait' : 'landscape'; }    
    } else {
    // Otherwise, check the parent window's orientation variable
    window.top.testValue = true;
    if (!isNaN(window.top.orientation)){ var orientationMode = (window.top.orientation == 0 || window.top.orientation == 180) ? 'portrait' : 'landscape'; } 
    else { var orientationMode = ($(window.top).width() < 980) ? 'portrait' : 'landscape'; }      
    }
  // Determine if this user is running is non-fullscreen mode
  var notFullscreenMode = ('standalone' in window.navigator) && !window.navigator.standalone ? true : false;
  // Update the orientation variables on this window's body elements
  if (orientationMode == 'portrait'){ 
    $('body').removeClass('mobileFlag_landscapeMode').addClass('mobileFlag_portraitMode'); 
    if (notFullscreenMode){ $('body').removeClass('mobileFlag_landscapeMode_notFullscreen').addClass('mobileFlag_portraitMode_notFullscreen');  } 
    } else { 
    $('body').removeClass('mobileFlag_portraitMode').addClass('mobileFlag_landscapeMode'); 
    if (notFullscreenMode){ $('body').removeClass('mobileFlag_portraitMode_notFullscreen').addClass('mobileFlag_landscapeMode_notFullscreen');   }
    }
  // Reset the window scroll to center elements properly
  window.scrollTo(0, 1);
  if (window !== window.top){ parent.window.scrollTo(0, 1); }  
  //$('body').css('border', '10px solid red').animate({borderWidth : '0'}, 1000, 'swing');
  // DEBUG
  //alert('<body class="'+$('body').attr('class')+'">\n'+updateType+'\n</body>');
  // Check if this is a child frame and this is not a startup call
  if (window !== window.top){ 
    // Alert the user of the orientation change (used to fix a bug with iframe not updating)
    parent.window.location.hash = '#'+orientationMode;
    //alert('Screen orientation changed...\nGame display updated!'); 
    }  
  // Return the final orientation mode
  //console.log({orientationMode:orientationMode,notFullscreenMode:notFullscreenMode,bodyClass:$('body').attr('class')});
  return orientationMode;  
}

function localFunction(myMessage){
  alert(myMessage);
}

// Define a function for randomly animating canvas robots
var backgroundDirection = 'left';
var canvasAnimationTimeout = false;
function mmrpg_canvas_animate(){
  //console.log('mmrpg_canvas_animate();');
  //clearTimeout(canvasAnimationTimeout);
  clearInterval(canvasAnimationTimeout);
  if (!gameSettings.idleAnimation){  return false; }
  // Collect the current battle status and result
  var battleStatus = $('input[name=this_battle_status]', gameEngine).val();
  var battleResult = $('input[name=this_battle_result]', gameEngine).val();
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
      var spriteID = thisSprite.attr('data-'+spriteKind+'id');
      //alert('sprite kind is '+spriteKind+' and its ID is '+spriteID);
      var shadowSprite = $('.sprite[data-shadowid='+spriteID+']', gameCanvas);
      //var detailsSprite = $('.sprite[data-detailsid='+spriteID+']', gameCanvas);
      //var mugshotSprite = $('.sprite[data-mugshotid='+spriteID+']', gameCanvas);
      //alert('Shadowsprite '+(shadowSprite.length ? 'exists' : 'does not exist')+'!');
      thisSprite.stop(true, true).animate({opacity:0},1000,'linear',function(){ 
        $(this).remove(); 
        if (shadowSprite.length){ shadowSprite.stop(true, true).animate({opacity:0},1000,'linear',function(){ $(this).remove(); }); }
        //if (detailsSprite.length){ detailsSprite.animate({opacity:0},1000,'linear',function(){ $(this).remove(); }); }
        //if (mugshotSprite.length){ mugshotSprite.animate({opacity:0},1000,'linear',function(){ $(this).remove(); }); }
        });      
      }
    }
        
    });  
  
  
  // Loop through all players on the field
  $('.sprite[data-type=player]', gameCanvas).each(function(){
    
    // Collect a reference to the current player
    var thisPlayer = $(this);
    // Generate a random number
    var thisRandom = Math.floor(Math.random() * 100);
    // Default the new frame to base
    var newFrame = 'base';
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
          if (battleStatus == 'complete' && thisRandom >= 60){ 
            newFrame = relativeResult;
            } else if (thisRandom >= 30){ 
            newFrame = 'taunt'; 
            }
          } else {
          if (battleStatus == 'complete' && thisRandom >= 60){
            newFrame = relativeResult;
            } else if (thisRandom >= 30){ 
            newFrame = 'taunt'; 
            } 
          }       
        }      
      }
    // Trigger the player frame advancement
    mmrpg_canvas_player_frame(thisPlayer, newFrame);
    
    });   
  
  
  // Loop through all robots on the field
  $('.sprite[data-type=robot]', gameCanvas).each(function(){    
    
    // Collect a reference to the current robot
    var thisRobot = $(this);    
    // Ensure the robot has not been disabled
    if (thisRobot.attr('data-status') != 'disabled'){
      // Generate a random number
      var thisRandom = Math.floor(Math.random() * 100);
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
          newFrame = 'defeat'; 
          } else {
          // Only change to an action frame if currently base
          if (currentFrame == 'base'){
            // Animation freqency based on position
            if (thisRobot.attr('data-position') != 'active'){
              // Higher animation freqency if not active (BENCH)
              if (battleStatus == 'complete' && thisRandom >= 60){
                newFrame = relativeResult; 
                } else if (thisRandom >= 50){ 
                newFrame = 'taunt'; 
                } else if (thisRandom >= 40){ 
                newFrame = 'defend'; 
                }
              } else {
              // Lower animation freqency if active (ACTIVE)
              if (battleStatus == 'complete' && thisRandom >= 50){
                newFrame = relativeResult; 
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
      var spriteID = thisRobot.attr('data-'+spriteKind+'id');
      var shadowSprite = $('.sprite[data-shadowid='+spriteID+']', gameCanvas);
      if (shadowSprite.length){ mmrpg_canvas_robot_frame(shadowSprite, newFrame);  }
      
      } else if (thisRobot.attr('data-status') == 'disabled' && thisRobot.attr('data-direction') == 'right'){
      // Default the new frame to base
      //var newFrame = 'base';  
      // Trigger the robot frame advancement
      //mmrpg_canvas_robot_frame(thisRobot, newFrame);            
      } else {      
      //alert('robot is disabled');  
      // Fade this robot off-screen       
      var spriteKind = thisRobot.attr('data-type');
      var spriteID = thisRobot.attr('data-'+spriteKind+'id');
      //alert('sprite kind is '+spriteKind+' and its ID is '+spriteID);
      var shadowSprite = $('.sprite[data-shadowid='+spriteID+']', gameCanvas);
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
  //canvasAnimationTimeout = setTimeout(function(){
  //  mmrpg_canvas_animate(); // DEBUG PAUSE
  //  }, gameSettings.eventTimeout);
  // Reset the timeout event for another animation round
  if (canvasAnimationTimeout != false){ window.clearTimeout(canvasAnimationTimeout); }
  if (!canvasAnimationTimeout.length){    
    canvasAnimationTimeout = window.setTimeout(function(){
      //console.log('mmrpg_canvas_animate');
      mmrpg_canvas_animate(); // DEBUG PAUSE
      }, gameSettings.eventTimeout);
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
  if (gameSettings.eventCrossFade == true){
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
spriteFrameIndex.robots = ['base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage'];
function mmrpg_canvas_robot_frame(thisRobot, newFrame){
  // Collect this robot's data fields
  var thisSize = thisRobot.attr('data-size');
  var thisPosition = thisRobot.attr('data-position');
  var thisDirection = thisRobot.attr('data-direction');
  var thisStatus = thisRobot.attr('data-status');
  var thisFrame = thisRobot.attr('data-frame');
  var isShadow = thisRobot.attr('data-shadowid') != undefined ? true : false;
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
  if (gameSettings.eventCrossFade == true){
    // Create a clone object with the new class and crossfade it into view
    var cloneRobot = thisRobot.clone().css('z-index', '-=1').appendTo(thisRobot.parent());
    thisRobot.stop(true, true).css({opacity:0,backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
    thisRobot.stop(true, true).animate({opacity:1}, {duration:400,easing:'swing',queue:false});
    cloneRobot.stop(true, true).animate({opacity:0}, {duration:400,easing:'swing',queue:false,complete:function(){ $(this).remove(); }});      
    } else {
    // Update the existing sprite's frame without crossfade by swapping the classsa
    thisRobot.stop(true, true).css({backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
    }    
  // Return true on success
  return true;
}
// DEBUG
//function mmrpg_canvas_robot_frame_

// Define a function for updating a player's frame with animation
spriteFrameIndex.players = ['base','taunt','victory','defeat','command','damage'];
function mmrpg_canvas_player_frame(thisPlayer, newFrame){
  // Collect this player's data fields
  var thisSize = thisPlayer.attr('data-size');
  var thisPosition = thisPlayer.attr('data-position');
  var thisDirection = thisPlayer.attr('data-direction');
  var thisStatus = thisPlayer.attr('data-status');
  var thisFrame = thisPlayer.attr('data-frame');
  var newFramePosition = spriteFrameIndex.players.indexOf(newFrame) || 0;
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
  if (gameSettings.eventCrossFade == true){
    // Create a clone object with the new class and crossfade it into view
    var clonePlayer = thisPlayer.clone().css('z-index', '-=1').appendTo(thisPlayer.parent());
    thisPlayer.stop(true, true).css({opacity:0,backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
    thisPlayer.stop(true, true).animate({opacity:1}, {duration:400,easing:'swing',queue:false});
    clonePlayer.stop(true, true).animate({opacity:0}, {duration:400,easing:'swing',queue:false,complete:function(){ $(this).remove(); }});        
    } else {
    // Update the existing sprite's frame without crossfade by swapping the classsa
    thisPlayer.stop(true, true).css({backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).removeClass(currentClass).addClass(newClass);
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
    if ((thisPosition != 'background' && thisPosition != 'foreground') && gameSettings.eventCrossFade == true){
      // Create a clone object with the new class and crossfade it into view
      var cloneAttachment = thisAttachment.clone().css('z-index', '-=1').appendTo(thisAttachment.parent());
      thisAttachment.stop(true, true).css({opacity:0,backgroundPosition:backgroundOffset+'px 0'}).attr('data-frame', newFrame).attr('data-animate-index', newIndex).removeClass(currentClass).addClass(newClass);
      // If the frame's offsets have changed, update the css offsets
      if (thisAnimateFrameShift){ thisAttachment.stop(true, true).css(thisFloat, newFrameShiftX).css('bottom', newFrameShiftY); }
      // Fade this attachment back into view and fade the cloned attachment in the old frame out
      thisAttachment.stop(true, true).animate({opacity:1}, {duration:400,easing:'swing',queue:false});
      cloneAttachment.stop(true, true).animate({opacity:0}, {duration:400,easing:'swing',queue:false,complete:function(){ $(this).remove(); }});        
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
  //alert('thisAction : '+thisAction);
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
    } else if (thisTarget == 'select_target'){
      // Make sure the engine is not submit yet
      submitEngine = false;
      // Make sure the next panel is the target
      nextPanel = 'target_target';      
    }
    mmrpg_engine_update({this_action_token:thisAbility});
    thisAction = 'ability';    
    } else if (thisAction.match(/^switch_([-a-z0-9_]+)$/i)){
    // Parse the switch token and clean the main action token
    var thisSwitch = thisAction.replace(/^switch_([-a-z0-9_]+)$/i, '$1');
    mmrpg_engine_update({this_action_token:thisSwitch});
    thisAction = 'switch';
    } else if (thisAction.match(/^scan_([-a-z0-9_]+)$/i)){
    // Parse the scan token and clean the main action token
    var thisScan = thisAction.replace(/^scan_([-a-z0-9_]+)$/i, '$1');
    mmrpg_engine_update({this_action_token:thisScan});
    thisAction = 'scan';
    } else if (thisAction.match(/^target_([-a-z0-9_]+)$/i)){
    // Parse the target token and clean the main action token
    var thisTarget = thisAction.replace(/^target_([-a-z0-9_]+)$/i, '$1');
    //alert('thisTarget '+thisTarget);
    thisTarget = thisTarget.split('_');
    mmrpg_engine_update({target_robot_id:thisTarget[0]});
    mmrpg_engine_update({target_robot_token:thisTarget[1]});
    thisAction = '';
    } else if (thisAction.match(/^settings_([-a-z0-9]+)_([-a-z0-9_]+)$/i)){
    // Parse the settings token and value, then clean the action token
    var thisSettingToken = thisAction.replace(/^settings_([-a-z0-9]+)_([-a-z0-9_]+)$/i, '$1');
    var thisSettingValue = thisAction.replace(/^settings_([-a-z0-9]+)_([-a-z0-9_]+)$/i, '$2');
    gameSettings[thisSettingToken] = thisSettingValue;
    var thisRequestType = 'session';
    var thisRequestData = 'battle_settings,'+thisSettingToken+','+thisSettingValue;
    $.post('scripts/script.php',{requestType: 'session',requestData: 'battle_settings,'+thisSettingToken+','+thisSettingValue});
    thisAction = 'settings';
    var nextAction = $('input[name=next_action]', gameEngine).val();
    if (nextAction.length){ mmrpg_action_panel(nextAction, thisPanel); } 
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
  'images/assets/battle-scene_robot-details.gif',
  'images/assets/battle-scene_robot-results.gif',
  'images/abilities/ability-results/sprite_left_80x80.png',
  'images/abilities/ability-results/sprite_right_80x80.png'
  ];
function mmrpg_preload_assets(){
  // Loop through each of the asset images
  for (key in asset_sprite_images){
    // Define the sprite path value
    var sprite_path = asset_sprite_images[key];
    // Cache this image in the appropriate array
    var cacheImage = document.createElement('img');
    cacheImage.src = sprite_path;
    asset_sprite_cache.push(cacheImage);
  }
}

// Define a function for preloading field sprites
var field_sprite_cache = {};
var field_sprite_frames = ['base'];
var field_sprite_kinds = ['background', 'foreground'];
var field_sprite_type = 'png';
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
      var this_kind = fieldKind;
      var file_name = 'battle-field_'+this_kind+'_'+this_frame+'.'+field_sprite_type;
      // Cache this image in the apporiate array
      var cacheImage = document.createElement('img');
      cacheImage.src = sprite_path+file_name;
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
      var thisSpriteToken = thisSpriteType+'_'+thisRobotDirection+'_'+thisRobotSize+'x'+thisRobotSize;
      var thisSpriteFilename = thisSpriteToken+'.'+robotSpriteExtension;
      // Cache this image in the apporiate array
      var thisCacheImage = document.createElement('img');
      thisCacheImage.src = robotSpritePath+thisSpriteFilename;
      robotSpriteCache[thisCacheToken].push(thisCacheImage);
      //alert('thisCacheImage.src = '+robotSpritePath+thisSpriteFilename+';');      
      //alert(robotSpritePath+thisSpriteFilename);
    }
  }
  //alert('sprite cache '+sprite_cache[thisRobotToken].length);
}

// Define a function for updating the engine form
function mmrpg_engine_update(newValues){
  if (gameEngine.length){
    // Loop through the game engine values and update them
    for (var thisName in newValues){
      var thisValue = newValues[thisName];
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
    //console.log('We\'ve got links, batman!');
    // Assign events to any of the page links here
    $('.num', floatLinkContainer).click(function(e){
      e.preventDefault();      
      // Collect references to this link and number
      var thisLink = $(this);
      var thisNum = parseInt(thisLink.attr('href').replace(/^#/, ''));
      // If this this panel is disabled, prevent clicking but only the first link
      if (thisNum > 1 && mainActionsTitle.hasClass('main_actions_title_disabled')){ return false; }      
      //console.log('num link '+thisNum+' clicked!');
      // Remove the active class from other links and add to this one
      $('.num', floatLinkContainer).removeClass('active');
      thisLink.addClass('active');
      // Define the key of the first and last element to be shown
      var lastElementKey = thisNum * 8;
      var firstElementKey = lastElementKey - 8;
      //console.log('first key should be '+firstElementKey+' and last should be '+lastElementKey+'!');
      // Hide all item buttons in the current view and then show only relevant
      $('.action_ability', newWrapper).css({display:'none'});
      var activeButtons = $('.action_ability', newWrapper).slice(firstElementKey, lastElementKey); 
      //console.log('we have selected a total of '+activeButtons.length+' elements');
      activeButtons.css({display:'block'});
      // Loop through the active buttons and update their order values
      var tempOrder = 1;
      activeButtons.each(function(){ $(this).attr('data-order', tempOrder); tempOrder++; });
      $('.action_back', newWrapper).attr('data-order', tempOrder);
      // Update the session with the last page click      
      var thisRequestType = 'session';
      var thisRequestData = 'battle_settings,action_ability_page_num,'+thisNum;
      $.post('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
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
  var currentButtons = $('.button:not(.button_disabled)', newWrapper);
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
}

// Define a quick function for decompressing action panel markup
var decompressActionArray = [];
//gameSettings.cacheTime
function decompress_action_markup(thisMarkup){
  //console.log('decompress_action_markup(thisMarkup:before)', thisMarkup);
  var beforeLength = thisMarkup.length;
  //console.log('decompress_action_markup(thisMarkup:before:'+beforeLength+')');
  var arrayLength = decompressActionArray.length;
  for (var i = 0; i < arrayLength; i++){
    var thisSearch = '!'+i.toString(16)+'¡';
    var thisReplace = decompressActionArray[i];
    thisMarkup = thisMarkup.replaceAll(thisSearch, thisReplace);  
    //thisMarkup = thisMarkup.split(thisSearch).join(thisReplace);
  }
  //console.log('decompress_action_markup(thisMarkup:after)', thisMarkup);
  var afterLength = thisMarkup.length;
  //console.log('decompress_action_markup(thisMarkup:after:'+afterLength+':(+'+(afterLength - beforeLength)+'/+'+(100 - Math.ceil((beforeLength / afterLength) * 100))+'%))');
  return thisMarkup;
}

// Define a function for updating an action panel's markup
var actionPanelCache = [];
function mmrpg_action_panel_update(thisPanel, thisMarkup){
  // Update the requested panel with the supplied markup
  var thisActionPanel = $('#actions_'+thisPanel, gameActions);
  //thisActionPanel.empty().html(decompress_action_markup(thisMarkup));
  thisActionPanel.empty().html(thisMarkup);
  // Search for any sprites in this panel's markup
  $('.sprite', thisActionPanel).each(function(){
    var thisBackground = $(this).css('background-image').replace(/^url\((.*?)\)$/i, '$1');
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
    'event_functions' : function(){
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
        //mmrpg_canvas_event(decompress_action_markup(canvasMarkup)); //, flagsMarkup 
        mmrpg_canvas_event(canvasMarkup);
        }
      if (consoleMarkup.length){ 
        //mmrpg_console_event(decompress_action_markup(consoleMarkup));  //, flagsMarkup 
        mmrpg_console_event(consoleMarkup);
        }
      }, 
    'event_flags' : flagsMarkup //$.parseJSON(flagsMarkup)
      });
}
// Define a function for playing the events
function mmrpg_events(){
  //console.log('mmrpg_events()');
  //clearTimeout(canvasAnimationTimeout);
  clearInterval(canvasAnimationTimeout);
  
  var thisEvent = false;
  
  if (mmrpgEvents.length){
    // Switch to the events panel
    mmrpg_action_panel('event');     
    // Collect the topmost event and execute it
    thisEvent = mmrpgEvents.shift();
    thisEvent.event_functions();
    }
  
  if (gameConsole.length){ 
    $('.wrapper', gameConsole).perfectScrollbar('update'); 
    }
  
  if (mmrpgEvents.length < 1){
    
    // Switch to the specified "next" action
    var nextAction = $('input[name=next_action]', gameEngine).val();
    if (nextAction.length){ mmrpg_action_panel(nextAction); } 
    // Add the idle class to the robot details on-screen
    //console.log('adding robot details class....1');
    //$('.robot_details', gameCanvas).css('opacity', 0.9).addClass('robot_details_idle');
    // Start animating the canvas randomly
    mmrpg_canvas_animate();
    
    } else if (mmrpgEvents.length >= 1){
      
      if (gameSettings.eventAutoPlay && thisEvent.event_flags.autoplay != false){
        var autoClickTimer = setTimeout(function(){
          mmrpg_events();
          }, parseInt(gameSettings.eventTimeout));
        $('a[data-action="continue"]').addClass('button_disabled');
        } else {
        $('a[data-action="continue"]').removeClass('button_disabled');
        }
      $('a[data-action="continue"]').click(function(){
        clearTimeout(autoClickTimer);
        });
      
    }
  // Collect the current battle status and result
  var battleStatus = $('input[name=this_battle_status]', gameEngine).val();
  var battleResult = $('input[name=this_battle_result]', gameEngine).val();
  // Check for specific value triggers and execute events
  if (battleStatus == 'complete'){
    //console.log('checkpoint | battleStatus='+battleStatus+' battleResult='+battleResult);
    // Collect object referenences for the two sound objects
    //var musicController = document.getElementById('volumeControl_musicLevels');
    var musicController = $('#volumeControl_musicLevels');
    //var soundController = document.getElementById('volumeControl_soundLevels');
    var soundController = $('#volumeControl_soundLevels');
    // Collect the current volume of the background music
    var musicVolume = musicController.volume;
    // Based on the battle result, play the victory or defeat music
    if (battleResult == 'victory' && thisEvent.event_flags.victory != undefined && thisEvent.event_flags.victory != false){
      // Play the victory music
      //console.log('mmrpg_events() / Play the victory music');
      parent.mmrpg_music_load('misc/battle-victory', false, true);
      //var victoryTimeout = setTimeout(function(){ parent.mmrpg_music_load('last-track'); }, 5000);
      } else if (battleResult == 'defeat' && thisEvent.event_flags.defeat != undefined && thisEvent.event_flags.defeat != false){
      // Play the failure music
      //console.log('mmrpg_events() / Play the failure music');
      parent.mmrpg_music_load('misc/battle-defeat', false, true);
      //var defeatTimeout = setTimeout(function(){ parent.mmrpg_music_load('last-track'); }, 5000);
      }
    }
}

// Define a function for creating a new layer on the canvas
function mmrpg_canvas_event(thisMarkup){ //, flagsMarkup
  var thisContext = $('.wrapper', gameCanvas);
  if (thisContext.length){
    // Drop all the z-indexes to a single amount
    $('.event:not(.sticky)', thisContext).css({zIndex:500});
    // Calculate the top offset based on previous event height
    var eventTop = $('.event:not(.sticky):first-child', thisContext).outerHeight();
    // Prepend the event to the current stack but bring it to the front
    var thisEvent = $('<div class="event clearback">'+thisMarkup+'</div>');
    thisEvent.css({opacity:0.0,zIndex:600});
    thisContext.prepend(thisEvent);
    // Wait for all the event's assets to finish loading
    thisEvent.waitForImages(function(){
      // Animate a fade out of the other events
      $('.event:not(.sticky):gt(0)', thisContext).animate({opacity:0},{
        duration: 800,
        easing: 'linear',
        queue: false
        });
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
      // Find all the details in this event markup and move them to the sticky      
      $(this).find('.details').addClass('hidden').css({opacity:0}).appendTo('.event_details', gameCanvas);
      // Animate a fade in, and the remove the old images
      $(this).animate({opacity:1.0}, {
        duration: 400, //300,
        easing: 'linear',
        complete: function(){
          //$('.event:is(.sticky)', thisContext).find('.ability_damage,.ability_recovery').animate({opacity:0},400,'swing',function(){ $(this).remove(); });
          //var statMods = $('.event:not(.sticky):gt(0)', thisContext).find('.ability_damage,.ability_recovery').clone();
          //$('.event:is(.sticky)', thisContext).append(statMods);
          $('.details:not(.hidden)', thisContext).remove();
          $('.details', thisContext).css({opacity:1}).removeClass('hidden');
          $('.event:not(.sticky):gt(0)', thisContext).remove();
          $(this).css({zIndex:500});
          },
        queue: false
        });       
      });
    }
}


// Define a function for updating the graphics on the canvas
function mmrpg_canvas_update(thisBattle, thisPlayer, thisRobot, targetPlayer, targetRobot){
  // Preload all this robot's sprite image files if not already
  if (thisPlayer.player_side && thisRobot.robot_token){
    var thisRobotToken = thisRobot.robot_token;
    var thisRobotSide = thisPlayer.player_side == 'right' ? 'left' : 'right';
    mmrpg_preload_robot_sprites(thisRobotToken, thisRobotSide);  
    }
  // Preload all the target robot's sprite image files if not already
  if (targetPlayer.player_side && targetRobot.robot_token){
    var targetRobotToken = targetRobot.robot_token;
    var targetRobotSide = targetPlayer.player_side == 'right' ? 'left' : 'right';
    mmrpg_preload_robot_sprites(targetRobotToken, targetRobotSide);
    }
}


// Define a function for appending a event to the console window
function mmrpg_console_event(thisMarkup){ //, flagsMarkup
  var thisContext = $('.wrapper', gameConsole);
  if (thisContext.length){
    // Append the event to the current stack
    //thisContext.prepend('<div class="event" style="top: -100px;">'+thisMarkup+'</div>');
    thisContext.prepend(thisMarkup);
    gameConsole.find('.wrapper').scrollTop(0);
    $('.event:first-child', thisContext).css({top:-100});
    $('.event:first-child', thisContext).animate({top:0}, 400, 'swing');
    $('.event:eq(1)', thisContext).animate({opacity:0.90}, 100, 'swing');
    $('.event:eq(2)', thisContext).animate({opacity:0.80}, 100, 'swing');
    $('.event:eq(3)', thisContext).animate({opacity:0.70}, 100, 'swing');
    $('.event:eq(4)', thisContext).animate({opacity:0.65}, 100, 'swing');
    $('.event:eq(5)', thisContext).animate({opacity:0.60}, 100, 'swing');
    $('.event:eq(6)', thisContext).animate({opacity:0.55}, 100, 'swing');
    $('.event:eq(7)', thisContext).animate({opacity:0.50}, 100, 'swing');
    $('.event:eq(8)', thisContext).animate({opacity:0.45}, 100, 'swing');
    $('.event:gt(9)', thisContext).animate({opacity:0.40}, 100, 'swing');
    // Hide any leftover boxes from previous events over the limit
    //$('.event:gt(50)', thisContext).appendTo('#event_console_backup');
    
    // Remove any leftover boxes from previous events
    //$('.event:gt(10)', thisContext).remove();
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
  gameSettings.idleAnimation = false;
  gameSettings.eventAutoPlay = false;
  mmrpg_canvas_animate();  
  return gameSettings.idleAnimation;
}

// Define a function for toggling the music player
var mmrpgMusicInit = false;
var musicStreamObject = false;
var mmrpgMusicEndedDefault = function(){ /*console.log('default music ended event triggered, this.play()!');*/ musicStreamObject.play(); return false; };
var mmrpgMusicEnded = mmrpgMusicEndedDefault;
function mmrpg_music_toggle(){
  //alert('clicked');  
  var musicToggle = $('a.toggle', gameMusic);
  var musicStream = $('audio.stream', gameMusic);
  musicStreamObject = musicStream.get(0);
  if (musicStreamObject.paused){
    //alert('starting playback');
    musicStream.removeClass('paused').addClass('playing');
    musicStreamObject.volume = 1;
    musicStreamObject.play();
    musicToggle.html('&#9658;');
    musicToggle.removeClass('paused').addClass('playing');
    } else {
    //alert('stopping playback');
    musicStream.removeClass('playing').addClass('paused');
    musicStreamObject.volume = 0;
    musicStreamObject.pause();
    musicToggle.html('&#8226;');
    musicToggle.removeClass('playing').addClass('paused');
    }
  if (mmrpgMusicInit != true){
    //console.log('first music toggle!  init the music with the function caller');
    musicStreamObject.addEventListener('ended', function(){ return mmrpgMusicEnded(); }, true);
    mmrpgMusicInit = true;
  }  
}

// Define a function for playing the current music
function mmrpg_music_play(){
  var musicToggle = $('a.toggle', gameMusic);
  var musicStream = $('audio.stream', gameMusic);
  var musicStreamSource = $('source', musicStream).attr('src');
  //console.log('mmrpg_music_play('+musicStreamSource+')');
  if (musicStream.get(0).paused){
    //console.log('starting playback');
    musicStream.removeClass('paused').addClass('playing');
    musicStream.get(0).volume = 1;
    musicStream.get(0).addEventListener('canplay', function(){
      musicStream.get(0).play();
      musicToggle.html('&#9658;');
      musicToggle.removeClass('paused').addClass('playing');  
      this.removeEventListener('canplay', arguments.callee, false);
      });
    }
}

// Define a function for stopping the current music
function mmrpg_music_stop(){
  //alert('clicked');
  var musicToggle = $('a.toggle', gameMusic);
  var musicStream = $('audio.stream', gameMusic);
  if (musicStream.get(0) != undefined && !musicStream.get(0).paused){
    //alert('stopping playback');
    musicStream.removeClass('playing').addClass('paused');
    musicStream.get(0).volume = 0;
    musicStream.get(0).pause();
    musicToggle.html('PLAY');
    musicToggle.removeClass('playing').addClass('paused');
    }
}
// Define a function for stopping the current music
function mmrpg_music_onend(onendFunction){
  //alert('clicked');
  var musicToggle = $('a.toggle', gameMusic);
  var musicStream = $('audio.stream', gameMusic);
  if (musicStream.get(0) != undefined && !musicStream.get(0).paused){
    return onendFunction(musicToggle, musicStream);
    }
}
// Define a function for playing the current music
var mmrpgMusicNextTrack = false;
function mmrpg_music_load(newTrack, resartTrack, playOnce){
  //console.log('mmrpg_music_load(newTrack['+newTrack+'], resartTrack['+(resartTrack ? 'true' : 'false')+'], playOnce['+(playOnce ? 'true' : 'false')+'])');
  var musicStream = $('audio.stream', gameMusic);
  musicStreamObject = musicStream.get(0);
  var thisTrack = musicStream.attr('data-track');
  var isPaused = musicStreamObject == undefined || musicStreamObject.paused ? true : false;
  var isRestart = resartTrack === true ? true : false;
  var isPlayOnce = playOnce == true ? true : false;
  if (newTrack == 'last-track'){ 
    var lastTrack = musicStream.attr('data-last-track'); 
    if (lastTrack != 'misc/battle-victory' && lastTrack != 'misc/battle-defeat'){ newTrack = lastTrack; }
    else { return false; }
    }
  if (isRestart == false && newTrack == thisTrack){ return false; }
  if (thisTrack != newTrack || isRestart){
    if (thisTrack != newTrack){ 
      //console.log('loading new track '+newTrack); 
      } else if (isRestart){ 
      //console.log('restarting track '+newTrack);  
      }
    mmrpg_music_stop();  
    var newSourceMP3 = '<source src="sounds/'+newTrack+'/audio.mp3?'+gameSettings.cacheTime+'" type="audio/mp3" />';
    var newSourceOGG = '<source src="sounds/'+newTrack+'/audio.ogg?'+gameSettings.cacheTime+'" type="audio/ogg" />';
    musicStream.empty();
    if (isIE || isOpera || isSafari){ musicStream.append(newSourceMP3);  }
    else if (isChrome || isFirefox){ musicStream.append(newSourceOGG); }
    else { musicStream.append(newSourceMP3); }
    musicStream.attr('data-track', newTrack);
    if (musicStreamObject != undefined){ musicStreamObject.load(); }    
    if (!isPaused){ mmrpg_music_play(); }   
    musicStream.attr('data-last-track', thisTrack);  
    }
  
  // Only continue if the media stream object is not undefined
  if (musicStreamObject != undefined){

    // If the user requested this track to only play once, otherwise just repeat
    if (isPlayOnce == true){
      
      //console.log('isPlayOnce is true, creating new onended event to prevent from replaying');
      
      // Update the event listener that will prevent this from replaying
      mmrpgMusicEnded = function(){
        //console.log('onended event called...');

        // Decide what to do based on the requested music or sound effect
        if (newTrack == 'misc/battle-victory' || newTrack == 'misc/battle-defeat'){
          //console.log('new track was '+newTrack+' so we\'re going to leaderboard now that complete');
          mmrpg_music_load('misc/leader-board', true, false);        
          } else {       
          //console.log('new track was '+newTrack+' so we\'re simply playing last track now that complete');
          mmrpg_music_load('last-track', true, false);      
          }
        
        return false;
        
        };
      
      } else {
      
      //console.log('isPlayOnce is false, resetting onended event to default and replay track');
      
      // Create the event listener that will ensure this continues replaying
      //console.log('Update the event listener function with the default');
      mmrpgMusicEnded = mmrpgMusicEndedDefault;
      
      }
    
    }
    
}

// Define a function for preloading music files
var musicCache = [];
var cacheList = [];
function mmrpg_music_preload(newTrack){
  // Ensure the new track is not alrady in the list
  if (cacheList.indexOf(newTrack) === -1){
    // Define the two audio objects based on the track  
    var newAudioMP3 = '<audio src="sounds/'+newTrack+'/audio.mp3?'+gameSettings.cacheTime+'" preload></audio>';
    var newAudioOGG = '<audio src="sounds/'+newTrack+'/audio.ogg?'+gameSettings.cacheTime+'" preload></audio>';
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

// Define a function for updating the loaded status of the main index page
function mmrpg_toggle_index_loaded(toggleValue){
  //alert('game loaded!');
  if (toggleValue == true && gameSettings.indexLoaded != true){
    // Fade out the splash loader text, change it to PLAY, then flade it in
    $('a.toggle span', gameMusic).css({opacity:1}).animate({opacity:0}, 1000, 'swing', function(){
      $('a.toggle span', gameMusic).html('<div class="start"><div class="title">START</div><div class="subtitle">MEGA MAN RPG PROTOTYPE</div><div class="info">(Toggle music with &nbsp;&nbsp;)<div class="icon">&nbsp;</div></div></div>').animate({opacity:1}, 1000, 'swing', function(){
        // Remove the loading class from the iframe and fade it into view
        //$('iframe', gameWindow).css({opacity:0}).removeClass('loading').animate({opacity:1}, 1000, 'swing'); // DEBUG
        // Set the toggle loader flag to true
        gameSettings.indexLoaded = true;
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
  var newValueClass = 'value type ';
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
            var obj = $(this),
                allImgs = [];

            if (waitForAll) {
                // CSS properties which may contain an image.
                var hasImgProperties = $.waitForImages.hasImageProperties || [],
                    matchUrl = /url\((['"]?)(.*?)\1\)/g;
                
                // Get all elements, as any one of them could have a background image.
                obj.find('*').each(function() {
                    var element = $(this);

                    // If an `img` element, add it. But keep iterating in case it has a background image too.
                    if (element.is('img:uncached')) {
                        allImgs.push({
                            src: element.attr('src'),
                            element: element[0]
                        });
                    }

                    $.each(hasImgProperties, function(i, property) {
                        var propertyValue = element.css(property);
                        // If it doesn't contain this property, skip.
                        if ( ! propertyValue) {
                            return true;
                        }

                        // Get all url() of this element.
                        var match;
                        while (match = matchUrl.exec(propertyValue)) {
                            allImgs.push({
                                src: match[2],
                                element: element[0]
                            });
                        };
                    });
                });
            } else {
                // For images only, the task is simpler.
                obj
                 .find('img:uncached')
                 .each(function() {
                    allImgs.push({
                        src: this.src,
                        element: this
                    });
                });
            };

            var allImgsLength = allImgs.length,
                allImgsLoaded = 0;

            // If no images found, don't bother.
            if (allImgsLength == 0) {
                finishedCallback.call(obj[0]);
            };

            $.each(allImgs, function(i, img) {
                
                var image = new Image;
                
                // Handle the image loading and error with the same callback.
                $(image).bind('load.' + eventNamespace + ' error.' + eventNamespace, function(event) {
                    allImgsLoaded++;
                    
                    // If an error occurred with loading the image, set the third argument accordingly.
                    eachCallback.call(img.element, allImgsLoaded, allImgsLength, event.type == 'load');
                    
                    if (allImgsLoaded == allImgsLength) {
                        finishedCallback.call(obj[0]);
                        return false;
                    };
                    
                });

                image.src = img.src;
            });
        });
    };
})(jQuery);

// Fix the indexOf issue for IE8 and lower
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
        "use strict";
        if (this === void 0 || this === null) {
            throw new TypeError();
        }
        var t = Object(this);
        var len = t.length >>> 0;
        if (len === 0) {
            return -1;
        }
        var n = 0;
        if (arguments.length > 0) {
            n = Number(arguments[1]);
            if (n !== n) { // shortcut for verifying if it's NaN
                n = 0;
            } else if (n !== 0 && n !== Infinity && n !== -Infinity) {
                n = (n > 0 || -1) * Math.floor(Math.abs(n));
            }
        }
        if (n >= len) {
            return -1;
        }
        var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
        for (; k < len; k++) {
            if (k in t && t[k] === searchElement) {
                return k;
            }
        }
        return -1;
    }
}