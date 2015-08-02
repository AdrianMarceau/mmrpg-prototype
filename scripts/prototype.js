// Create the globale context variables
var thisBody = false;
var thisPrototype = false;
var thisFalloff = false;
var thisWindow = false;
// Create the prototype battle options object
gameSettings.fadeIn = false;
gameSettings.totalRobotLimit = 8;
gameSettings.totalPlayerOptions = 1;
gameSettings.nextRobotLimit = 8;
gameSettings.nextStepName = 'home';
gameSettings.nextSlideDirection = 'left';
gameSettings.startLink = 'home';
gameSettings.skipPlayerSelect = false;
gameSettings.passwordUnlocked = 0;
gameSettings.pointsUnlocked = 0;
var battleOptions = {};
// When the document is ready, assign events
$(document).ready(function(){
  
  // Update the global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisFalloff = $('#falloff', thisBody);
  thisWindow = $(window);
  
  // Define the prototype context
  var thisContext = $('#prototype');
  if (thisContext.length){
    
    thisWindow.resize(function(){ windowResizePrototype(); });
    setTimeout(function(){ windowResizePrototype(); }, 2000);
    //$('.banner .link', thisPrototype).live('click', function(){ windowResizePrototype(); });
    
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
    
    
    // DEBUG
    $('.menu a[data-reload=true]', thisContext).live('click', function(e){
      // Prevent the default click action
      e.preventDefault();
      // Collect the parent variables
      var thisParentMenu = $(this).parents('.menu');
      var thisParentStep = thisParentMenu.attr('data-step');
      var thisParentSelect = thisParentMenu.attr('data-select');
      
      alert('thisParentStep = '+thisParentStep+' | thisParentSelect = '+thisParentSelect+' | ');
      
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
        $('.option[data-chapter!='+thisChapterToken+']', thisChapterWrapper).addClass('hidden_chapter_option');
        $('.option[data-chapter='+thisChapterToken+']', thisChapterWrapper).removeClass('hidden_chapter_option');
        // Update the battle options with the last selected chapter
        var postData = {requestType:'session',requestData:'battle_settings,'+thisChapterPlayer+'_current_chapter,'+thisChapterToken};
        //console.log('scripts/script.php', postData);
        $.post('scripts/script.php', postData);
        // DEBUG DEBUG
        //alert('clicked chapter '+thisChapterToken+'!');
        });
      // Check to see which one should be displayed first and autoclick it
      if ($('.chapter_link_active', thisChapterSelects).length){ var firstChapterLink = $('.chapter_link_active', thisChapterSelects); }
      else { var firstChapterLink = $('.chapter_link[data-chapter]:first-child', thisChapterSelects); }
      firstChapterLink.trigger('click');      
      }
    
    
      
    // Create the click events for the prototype menu option buttons
    $('.option[data-token]', thisContext).live('click', function(e){      
      // Prevent the default click action
      e.preventDefault();
      //alert('option clicked!');
      // If this option is in the banner, return false
      if ($(this).parents('.banner').length == 1){ return false; }
      // Check if this option has been disabled or not
      if ($(this).hasClass('option_disabled')){
        if (!$(this).hasClass('option_disabled_clickable')){
          return false;
        }        
      }            
      // Trigger the prototype option function
      prototype_menu_click_option(thisContext, this);      
      });
    
    // Create the click events for the prototype menu back button
    $('.option[data-back]', thisContext).live('click', function(e){
      // Prevent the default click action
      e.preventDefault();
      // Trigger the prototype back function
      prototype_menu_click_back(thisContext, this);      
      });
    
    // Check if the player token has already been selected
    if (battleOptions['this_player_token'] != undefined){
      //alert('player selected : '+battleOptions['this_player_token']);
      gameSettings.skipPlayerSelect = true;
      var thisMenu = $('.menu[data-select="this_player_token"]', thisContext);      
      $('.option[data-token="'+battleOptions['this_player_token']+'"]', thisMenu).trigger('click');
    }
  
    // Attempt to define the top frame
    var topFrame = window.top;
    if (typeof topFrame.myFunction != 'function'){ topFrame = window.parent; }    
    
    // Define the a fadein function for the page
    var thisFadeCallback = function(){
      //alert('fadeCallback');        
      // Fade in the prototype screen slowly if allowed
      if (gameSettings.fadeIn == true){
        //alert('gameSettings.fadeIn == true? '+(gameSettings.fadeIn ? 'true' : 'false'));
        thisContext.waitForImages(function(){
          var tempTimeout = setTimeout(function(){
            thisContext.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing');
            windowResizePrototype();
            topFrame.mmrpg_toggle_index_loaded(true);
            gameSettings.startLink = 'home';
            if ((gameSettings.windowEventsCanvas != undefined && gameSettings.windowEventsCanvas.length) || (gameSettings.windowEventsMessages != undefined && gameSettings.windowEventsMessages.length)){
              topFrame.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
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
        if ((gameSettings.windowEventsCanvas != undefined && gameSettings.windowEventsCanvas.length) || (gameSettings.windowEventsMessages != undefined && gameSettings.windowEventsMessages.length)){
          topFrame.windowEventCreate(gameSettings.windowEventsCanvas, gameSettings.windowEventsMessages);
          }
        }        
      };
    
  // Define the event for the password prompt's click unlock sequence
  $('.banner .sprite_player', thisPrototype).live('click', function(){
    gameSettings.passwordUnlocked++;
    //console.log('gameSettings.passwordUnlocked = '+gameSettings.passwordUnlocked+'; gameSettings.pointsUnlocked = '+gameSettings.pointsUnlocked);
    if (gameSettings.passwordUnlocked >= 5 && gameSettings.pointsUnlocked > 0){
      //console.log('omg you unlocked me!');
      var thisToken = $(this).html().toLowerCase();
      var thisPlayer = thisToken.replace('. ', '-');
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
        var thisRequestData = 'values,battle_passwords,'+thisPlayer+','+thisPassword+',true';
        var thisPostData = {requestType:'session',requestData:thisRequestData};
        //console.log('scripts/script.php', thisPostData);
        $.post('scripts/script.php', thisPostData, function(){ 
          window.location.href = 'prototype.php?wap='+(gameSettings.wapFlag ? 'true' : 'false'); 
          });
        }
      }
    });      
      
    // Trigger the prototype step function if not home
    if (gameSettings.startLink != 'home'){
      //gameSettings.skipPlayerSelect = true;
      var thisLink = $('.banner .link[data-step='+gameSettings.startLink+']', thisContext);
      prototype_menu_click_step(thisContext, thisLink, thisFadeCallback, 10); //CHECKPOINT
      //prototype_menu_switch({stepName:gameSettings.startLink,onComplete:thisFadeCallback,slideDuration:600}); 
      } else {
      //gameSettings.skipPlayerSelect = true;
      thisFadeCallback();
      }       
      
    }
  
  // Reset the animation back to normal
  //gameSettings.skipPlayerSelect = false;
  
  
  
});

// Create the windowResize event for this page
function windowResizePrototype(){
  
  //alert('windowResizePrototype()');
  
  var windowWidth = thisWindow.width();
  var windowHeight = thisWindow.height();
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
  //$('.option_wrapper', thisPrototype).css({height:newWrapperHeight+'px'});
  $('.option_wrapper', thisPrototype).each(function(){
    var thisWrapper = $(this);
    thisWrapper.addClass('option_wrapper_full'); //.css({height:'auto',overflow:'hidden'});
    var totalHeight = thisWrapper.height();
    var tempNewWrapperHeight = newWrapperHeight;
    var excludeElement = $('.option_this-team-select', thisWrapper);
    var backElement = $('.option_back', thisWrapper.parent());
    if (excludeElement.length){
      //var excludeHeight = excludeElement.height() + excludeElement.outerHeight(true);
      //excludeHeight += backElement.height() + backElement.outerHeight(true);
      //excludeHeight += 13;
      //var excludeHeight = 140;
      //var excludeHeight = 40;
      var excludeHeight = 134;
      //alert('exclude '+excludeHeight);
      tempNewWrapperHeight -= excludeHeight;
      }
    thisWrapper.removeClass('option_wrapper_full');
    thisWrapper.css({height:tempNewWrapperHeight+'px'});
    thisWrapper.attr('data-content',totalHeight);
    //thisWrapper.scrollTop(totalHeight);
    //if (thisWrapper.hasClass('option_wrapper_noscroll')){  }
    });

}

// Define a function to trigger when resetting data
function mmrpg_trigger_reset(){
  // Define the confirmation text string
  var confirmText = 'Are you sure you want to reset your entire game?\nAll progress will be lost and cannot be restored including any and all unlocked missions, robots, and abilities. Continue?';
  // Attempt to confirm with the user of they want to resey
  if (navigator.userAgent.match(/Android/i) != null || confirm(confirmText)){
    // Redirect the user to the prototype reset page
    var postURL = 'prototype.php?action=reset';
    $.post(postURL, function(){ 
      window.location = 'prototype.php';  
      });        
    return true;
    } else {
    // Return false
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
  var confirmText = 'Are you sure you want to exit your game?\nAll unsaved progress will be lost and cannot be restored.';
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
  // If the nextMenu value is not empty, switch to the next menu tab
  if (gameSettings.nextStepName.length 
      && gameSettings.nextSlideDirection.length){    
    var bannerOverlay = $('.banner_overlay', thisPrototype);
    prototype_menu_switch({stepName:gameSettings.nextStepName,slideDirection:gameSettings.nextSlideDirection,onComplete:function(){
      gameSettings.nextStepName = false;
      gameSettings.nextSlideDirection = false;
      // Fade out the overlay to prevent clicking other banner links      
      bannerOverlay.stop().css({opacity:0.75}).animate({opacity:0.0},{duration:1000,easing:'swing',queue:false,complete:function(){ $(this).addClass('overlay_hidden'); }});
      $('.banner .points, .banner .zenny, .banner .options, .banner .tooltip', thisPrototype).stop().animate({opacity:1},500,'swing');
      }});    
    }
}

// Define a function for triggering a banner click
function prototype_menu_banner_link(thisStep){
  // Define the prototype context
  var thisContext = $('#prototype');
  if (thisContext.length){
    $('.banner .link[data-step='+thisStep+']', thisContext).trigger('click');
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
  $('.banner .link[data-step]', thisContext).removeClass('link_active');
  thisLink.addClass('link_active');
  // Collect the requested step name
  var stepName = thisLink.attr('data-step');
  var stepMenu = $('.menu[data-step='+stepName+']', thisContext);
  var slideDirection = currentActiveIndex > nextActiveIndex ? 'right' : 'left';
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
    else if (hasBlank && stepName == 'starforce'){ switchTimeoutDuration = 1000; } 
    
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
    bannerOverlay.stop().css({opacity:0.00}).removeClass('overlay_hidden').animate({opacity:0.75},{duration:1000,easing:'swing',queue:false});    
    var thisBanner = $('.banner', thisPrototype);
    $('.canvas_overlay_footer', thisBanner).remove();
    $('.points, .zenny, .options, .tooltip', thisBanner).stop().animate({opacity:0},500,'swing');
    
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
function prototype_menu_click_option(thisContext, thisOption){
  
  // If this option is disabled, ignore its input
  if ($(this).hasClass('option_disabled')
    && !$(this).hasClass('option_disabled_clickable')
    ){ return false; }
    
  // Collect the parent menu and option fields
  var thisOption = $(thisOption);
  var thisParent = thisOption.parent();
  if (thisParent.is('.option_wrapper')){ thisParent = thisParent.parent(); }
  var thisStep = parseInt(thisParent.attr('data-step'));
  var thisSelect = thisParent.attr('data-select');
  var thisToken = thisOption.attr('data-token');
  var thisComplete = function(){};
  var nextStep = $('.menu[data-step='+(thisStep + 1)+']', thisContext);
  var nextFlag = true;
  var nextLimit = thisOption.attr('data-next-limit');
  if (nextLimit != undefined){ nextLimit = parseInt(nextLimit); }
  //var nextLimit = parseInt(thisOption.attr('data-next-limit'));
  
  // DEBUG
  //console.log('thisStep', thisStep);
  //console.log('thisSelect', thisSelect);
  //console.log('thisToken', thisToken);
  //console.log('nextStep', nextStep);
  //console.log('nextFlag', nextFlag);
  //console.log('nextLimit', nextLimit);
  
  // If the token was empty, return false
  if (!thisToken.length){ return false; }
  
  // If the next limit was set, apply to the next step
  if (nextLimit != undefined){
    nextStep.attr('data-limit', nextLimit);
    gameSettings.nextRobotLimit = nextLimit;
    }
  
  // DEBUG
  //console.log('gameSettings', gameSettings);
  
  // If this is a child token, update the parent
  if (thisOption.attr('data-child') != undefined){
    
    // Find the parent token container
    var tokenParent = $('.option[data-parent]', thisOption.parent());
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
    var tempSprite = $('.sprite', thisOption.get(0));
    if (tempSprite.hasClass('sprite_40x40')){ var tempSize = 40; }
    else if (tempSprite.hasClass('sprite_80x80')){ var tempSize = 80; }
    else if (tempSprite.hasClass('sprite_160x160')){ var tempSize = 160; }   
    var tempSpriteKey = tokenParentCount - 1;
    var tempSpriteShift = parseInt($('.sprite[data-key='+tempSpriteKey+']', tokenParent).css('right'));
    if (tempSize == 80){ tempSpriteShift -= 20; }
    //console.log('tempSize = '+tempSize+'; tempSpriteKey = '+tempSpriteKey+'; tempSpriteShift = '+tempSpriteShift+'; ');
    var cloneShift = tempSpriteShift+'px'; //someValue+'px';
    var cloneSprite = tempSprite.clone().addClass('sprite_clone').css({opacity:1,right:cloneShift,left:'auto',bottom:'6px'});
    
    // Prepend the sprite to the parent's label value
    $('label', tokenParent).append(cloneSprite);
    
    // Hide the placeholder appropriate sprite
    $('.sprite_40x40_placeholder:eq('+(gameSettings.totalRobotLimit - tokenParentCount)+')', tokenParent).css({display:'none'});
    
    // Brighten the opacity of the parent element proportionately
    //var newOpacity = 1.0; //0.2 + (0.8 * (tokenParentCount/tokenParentLimit));
    var newOpacity = 0.8 + (0.2 * (tokenParentCount/tokenParentLimit));
    if (newOpacity > 1){ newOpacity = 1; }
    tokenParent.css({opacity:newOpacity});
    //tokenParent.find('.count').html((tokenParentCount >= tokenParentLimit) ? 'Start!' : (tokenParentCount+'/'+tokenParentLimit));
    tokenParent.find('.count').html((tokenParentCount >= 1) ? (tokenParentCount+'/'+gameSettings.nextRobotLimit)+' Start!' : (tokenParentCount+'/'+gameSettings.nextRobotLimit));
    tokenParent.find('.arrow').html('&#9658;');

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
      if (tokenParentCount >= gameSettings.totalRobotLimit){
        $('.option[data-child]', thisParent).addClass('option_disabled');  
        }          
      }
    
    // Set the next flag to false to prevent menu switching
    nextFlag = false;
    
    } else {
    
    // Update the battleOptions object with the current selection
    battleOptions[thisSelect] = thisToken;
    //alert('battleOptions['+thisSelect+'] = '+thisToken+';');
    
    }
  
  //alert(thisSelect);
  
  // Execute option-specific commands for special cases
  switch (thisSelect){
    case 'this_player_token': {
      
      // Prevent the player from fighting themselves in battle
      var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
      var tempMenu = $('.menu[data-select=this_battle_token]', thisContext);
      var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition+'"]', tempMenu);
      var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition+'"]', tempMenu);
      tempHideOptionWrapper.addClass('option_wrapper_hidden').css({border:'5px none transparent',margin:''});
      tempShowOptionWrapper.removeClass('option_wrapper_hidden').css({border:'1px solid transparent',marginLeft:'-1px'});          
      
      // Count the number of available missions right now
      var availableMissions = $('.option[data-token]', tempShowOptionWrapper);
      $('.header', tempMenu).find('.count').html('Mission Select ('+(availableMissions.length == 1 ? '1 Mission' : availableMissions.length+' Missions')+')');
      $('.header', tempMenu).removeClass('header_dr-light header_dr-wily header_dr-cossack').addClass('header_'+battleOptions['this_player_token']);
      
      break;
      }
    case 'this_battle_token': {         
      
      // Prevent the player from fighting themselves in battle
      var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
      var tempMenu = $('.menu[data-select=this_player_robots]', thisContext);
      var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition+'"]', tempMenu);
      var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition+'"]', tempMenu);
      tempHideOptionWrapper.addClass('option_wrapper_hidden').css({border:'5px none transparent',margin:''});
      tempShowOptionWrapper.removeClass('option_wrapper_hidden').css({border:'1px solid transparent',marginLeft:'-1px'});
      
      // Find the parent token container
      var tempWrapper = $('.option_wrapper[data-condition="'+tempCondition+'"]', tempMenu);
      var tokenParent = $('.option[data-parent]', tempWrapper);
      var tokenParentLimit = gameSettings.totalRobotLimit; //tempMenu.attr('data-limit');
      
      // Count the number of available robots right now
      var requiredRobots = nextLimit;
      var availableRobots = $('.option[data-child]', tempWrapper);
      var selectedRobots = $('.option[data-parent]', tempWrapper);
      //alert('Battle requires '+requiredRobots+' robots, you have '+availableRobots.length+'.');
      
      // Update the start button's counter text
      $('.option[data-parent]', tempMenu).find('.count').html('0/'+gameSettings.nextRobotLimit+' Select');
      
      var tempMenuHeader = $('.header', tempMenu);
      tempMenuHeader.find('.count').html('Robot Select ('+(availableRobots.length == 1 ? '1 Robot' : availableRobots.length+' Robots')+')');
      tempMenuHeader.removeClass('header_dr-light header_dr-wily header_dr-cossack').addClass('header_'+battleOptions['this_player_token']);
      if (!$('.reselect', tempMenuHeader).length){
        var tempReselect = $('<span class="reselect">&#215;</span>');
        tempReselect.click(function(){          
          //alert('reselect!');
          // If there are no robots selected, do nothing
          //if (battleOptions['this_player_robots'] == undefined){ alert('undefined?'); return; }
          // Re-enable all robot options
          $('.option_wrapper[data-condition]', tempMenu).css({display:''});
          $('.option[data-child]', tempMenu).removeClass('option_disabled');
          $('.option[data-parent]', tempMenu).addClass('option_disabled').attr('data-token', '').css({opacity:''}).find('.count').html('0/'+gameSettings.nextRobotLimit+' Select').end().find('.arrow').html('&nbsp;');
          //$('.option[data-parent] label', tempMenu).css({paddingLeft:''});
          $('.option[data-parent] .sprite:not(.sprite_40x40_placeholder)', tempMenu).remove();
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
      if (battleOptions['this_player_robots'] == undefined || battleOptions['this_player_robots'].length < 1){
        //alert('hide reselect');
        $('.reselect', tempMenuHeader).css({opacity:0});
        } else {
        //alert('show reselect');
        $('.reselect', tempMenuHeader).css({opacity:1});  
        }
      
      // Generate the placeholder sprite markup
      var iCounter = 1;
      var spriteMarkup = '';
      //for (iCounter; iCounter <= nextLimit; iCounter++){
      for (iCounter; iCounter <= gameSettings.totalRobotLimit; iCounter++){
        var someValue = 80 + ((tokenParentLimit * 40) - (iCounter * 40) + 40);
        //var someValue = (gameSettings.totalRobotLimit * 40) - (iCounter * 40) + 40;
        var spriteClass = 'sprite sprite_40x40 sprite_40x40_defend sprite_40x40_placeholder ';
        //var spriteStyle = 'background-image: url(images/robots/robot/sprite_right_40x40.png?'+gameSettings.cacheTime+'); bottom: 6px; left: '+someValue+'px; right: auto; opacity: 0.8; ';
        var spriteStyle = 'background-image: url(images/robots/robot/sprite_right_40x40.png?'+gameSettings.cacheTime+'); bottom: 6px; right: '+someValue+'px; left: auto; opacity: 0.8; ';
        spriteMarkup += '<span data-key="'+(gameSettings.totalRobotLimit - iCounter)+'" class="'+spriteClass+'" style="'+spriteStyle+'">Select Robot</span>';
        }
      
      // Prepend the sprite to the parent's label value
      var labelPadding = ((tokenParentLimit * 40)+60)+'px';
      //var labelPadding = ((gameSettings.totalRobotLimit * 40)+60)+'px';
      $('.sprite_40x40_placeholder', tokenParent).remove();
      $('label', tokenParent).prepend(spriteMarkup); //.css({paddingLeft:labelPadding}),marginLeft:'260px'
      
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
  if (thisOption.attr('data-child') == undefined){
    
    // Collect the context for the banner area and remove and foregrounds
    
    var thisBanner = $('.banner', thisContext);
    //.credits:not(.is_shifted)
    //|| gameSettings.demo != true && gameSettings.totalPlayerOptions == 1
    var creditsOpacity = ((gameSettings.demo == true || (gameSettings.demo != true && gameSettings.totalPlayerOptions == 1 && thisSelect == 'this_player_token')) ? 1.0 : 0.0);
    //console.log('get ready to fade credits to '+creditsOpacity+'!');
    $('.banner_credits', thisBanner).removeClass('is_shifted').animate({opacity:creditsOpacity},{duration:600,easing:'swing',sync:false,complete:function(){ 
      //console.log('credits have been animated to '+creditsOpacity);
      $(this).addClass('is_shifted');       
      }});
    $('.banner_foreground:not(.is_shifted)', thisBanner).animate({opacity:0.75},{duration:600,easing:'swing',sync:false,complete:function(){
      $(this).addClass('is_shifted');
      }});
    
    // Count the number of other options in the banner
    var numOptions = $('.option', thisBanner).length;
    
    // Remove any options for the same select parent
    var previousOption = $('.option[data-select='+thisSelect+']', thisBanner);
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
        width:((numOptions * 8) + 42)+'%'
        });
      cloneOption.find('label').css({
        //marginRight:'15px',
        //width:'120px'
        margin: cloneOption.hasClass('option_this-team-select') ? '0 4px 0 260px' : '0 4px 0 20px',
        width: 'auto'
        });
      cloneOption.find('.subtext').css({
        fontSize:'8px'
        });
      cloneOption.unbind('click');
      thisBanner.append(cloneOption);
      if (!gameSettings.skipPlayerSelect){ cloneOption.animate({opacity:1,marginLeft:'0'},600,'linear'); }
      else { cloneOption.css({opacity:1,marginLeft:'0'}); }            
      
      }
    
    // If this was a mission select, update the banner background image
    if (thisSelect == 'this_battle_token'){      
      // Change the background image based on the current option data
      var newBackgroundImage = 'url(images/fields/'+thisOption.attr('data-background')+'/battle-field_background_base.gif?'+gameSettings.cacheTime+')';
      var oldBannerBackground = $('.banner_background', thisBanner);
      oldBannerBackground.stop();
      var newBannerBackground = $('<div class="sprite background banner_background" style="opacity: 0; z-index: 11; background-position: center -30px; background-image: '+newBackgroundImage+';">&nbsp;</div>');
      newBannerBackground.insertAfter(oldBannerBackground).animate({opacity:1.0},{duration:1000,easing:'swing',queue:false,complete:function(){ oldBannerBackground.remove(); $(this).css({zIndex:''}); }});
      // Change the foreground image based on the current option data
      var newForegroundImage = 'url(images/fields/'+thisOption.attr('data-foreground')+'/battle-field_foreground_base.png?'+gameSettings.cacheTime+')';
      var oldBannerForeground = $('.banner_foreground', thisBanner);
      oldBannerForeground.stop();
      var newBannerForeground = $('<div class="sprite background banner_foreground" style="opacity: 0; z-index: 21; background-position: center -30px; background-image: '+newForegroundImage+';">&nbsp;</div>');
      newBannerForeground.insertAfter(oldBannerForeground).animate({opacity:1.0},{duration:1000,easing:'swing',queue:false,complete:function(){ oldBannerForeground.remove(); $(this).css({zIndex:''}); }});
      // Fade in the overlay to prevent clicking on banner links
      var bannerOverlay = $('.banner_overlay', thisBanner);
      //thisBanner.stop().removeClass('banner_compact').animate({height:'124px'},{duration:1000,easing:'swing',queue:false});
      bannerOverlay.stop().removeClass('overlay_hidden').animate({opacity:0.75},{duration:1000,easing:'swing',queue:false});      
      $('.points, .zenny, .options, .tooltip', thisBanner).stop().animate({opacity:0},{duration:500,easing:'swing',queue:false});
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
          var thisMultiplier = thisPair[1];
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
  
  // Check if image preloading was requested
  if (thisPreload.length){
    // Preload the requested image
    var thisPreloadImage = $(document.createElement('img'))
      .attr('src', thisPreload)
      .load(function(){
        // Check if there is another menu step to complete
        if (nextStep.length){
          // Automatically switch to the next step in sequence
          prototype_menu_switch({stepNumber:thisStep + 1,onComplete:thisComplete});
          } else {
          // Redirect to the battle page
          prototype_menu_switch({redirectLink:thisRedirect,onComplete:thisComplete}); // checkpoint
          }
        });
    } else if (nextFlag != false){
    // Check if there is another menu step to complete
    if (nextStep.length){
      // Automatically switch to the next step in sequence
      prototype_menu_switch({stepNumber:thisStep + 1,onComplete:thisComplete});
      } else {
      // Redirect to the battle page
      prototype_menu_switch({redirectLink:thisRedirect,onComplete:thisComplete});
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
  var backParent = $('.menu[data-step='+(backStep)+']', thisContext);
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
        var tempMenu = $('.menu[data-select=this_battle_token]', thisContext);
        $('.option_wrapper[data-condition]', tempMenu).css({display:''});
        delete battleOptions['this_battle_token'];
        }
      break;
      }
    case 'this_battle_token': {
      switchOptions.onComplete = function(){
        
        // Re-enable all battle options
        var tempMenu = $('.menu[data-select=this_player_robots]', thisContext);
        $('.option_wrapper[data-condition]', tempMenu).css({display:''});
        $('.option[data-child]', tempMenu).removeClass('option_disabled');
        $('.option[data-parent]', tempMenu).addClass('option_disabled').attr('data-token', '').css({opacity:''}).find('.count').html('0/'+gameSettings.nextRobotLimit+' Select').end().find('.arrow').html('&nbsp;');
        $('.option[data-parent] label', tempMenu).css({paddingLeft:''});
        $('.option[data-parent] .sprite:not(.sprite_40x40_placeholder)', tempMenu).remove();
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
        $('.points, .zenny, .options, .tooltip', thisBanner).stop().animate({opacity:1},500,'swing');
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
  $('.option[data-select='+backSelect+']', thisBanner).animate({opacity:0},600,'swing',function(){
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
function prototype_menu_switch(switchOptions){
  
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
  
  // Define the prototype context
  var thisContext = $('#prototype');
  var thisBanner = $('.banner', thisContext);
  
  // Collect the current step token
  var currentStepToken = $('.menu[data-step]:not(.menu_hide)', thisContext).attr('data-step');
  
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
    thisBanner.animate({height:newHeight+'px'},{duration:500,easing:'swing',queue:false});  
    //var thisLoadingMenu = $('.menu[data-step=loading]', thisPrototype);
    //thisLoadingMenu.css({height:'800px',border:'2px solid red'});
    //$('.option_wrapper', thisLoadingMenu).css({height:'800px',border:'2px solid blue'});
    $('.banner_credits', thisBanner).animate({opacity:0},{duration:500,easing:'swing',queue:false,complete:function(){ $(this).css({display:'none'}); } });    
  }
  
  // Else if this is the HOME screen, expand the banner height
  if ((switchOptions.stepName == '1' || switchOptions.stepNumber == 1) 
      || (gameSettings.demo == true && (switchOptions.stepName == '2' || switchOptions.stepNumber == 2))
      || (gameSettings.demo != true && gameSettings.totalPlayerOptions == 1 && (switchOptions.stepName == '2' || switchOptions.stepNumber == 2))      
      ){
    var newHeight = 184;
    //console.log('Expanding the banner height to '+newHeight);
    thisBanner.animate({height:newHeight+'px'},{duration:500,easing:'swing',queue:false});    
    $('.banner_credits', thisBanner).removeClass('is_shifted').css({display:'block'}).animate({opacity:1},{duration:500,easing:'swing',queue:false});
  }
  
  // Change the background music to the appropriate file  
  if (switchOptions.stepNumber == 1){ 
    parent.mmrpg_music_load('misc/player-select', true, false); 
    } else if (switchOptions.stepNumber == 2){
    var newMusicToken = $('.select_this_player .option_this-player-select[data-token='+battleOptions['this_player_token']+']', thisContext).attr('data-music-token');
    //console.log('newMusicToken = '+newMusicToken);
    parent.mmrpg_music_load('misc/'+newMusicToken, true, false); 
    }
  //
  
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
    var currentMenu = $('.menu[data-step='+(switchOptions.stepNumber || switchOptions.stepName)+']', thisContext);
    var currentMenuTitle = currentMenu.attr('data-title');
    
    // Collect the step, select, and condition for this 
    var currentMenuStep = currentMenu.attr('data-step');
    var currentMenuSelect = currentMenu.attr('data-select');
    var currentMenuCondition = 'true';  
    if (currentMenuSelect == 'this_battle_token' || currentMenuSelect == 'this_player_robots'){ 
      currentMenuCondition = 'this_player_token='+battleOptions.this_player_token; 
      }
    
    // Define the function for reloading content
    var tempReloadFunction = function(tempCallbackFunction){
      
      if (tempCallbackFunction == undefined){ tempCallbackFunction = function(){}; }
      
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
                currentMenu.find('.option').not('.option_sticky').remove();
                currentMenu.find('.header').after(markup);
                $('.option_message:gt(0)', currentMenu).trigger('click');
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
            
            // If the player select has been skipped, do not bother loading missions
            if (gameSettings.skipPlayerSelect){
              
              // DEBUG
              //console.log('SKIPPING AJAX :');
                  
              // Auto-click any option messages after the first
              $('.option_message:gt(0)', tempMenuWrapper).trigger('click');
              
              // Trigger the callback function
              tempCallbackFunction();   
              
              // Change the player select back to normal
              gameSettings.skipPlayerSelect = false;
              
              }
            // Otherwise load the missions normally
            else {
              
              // Attempt to refresh the markup for this particular wrapper
              $.ajax('scripts/prototype.php', {
                type:  'POST',
                data: {step:currentMenuStep,select:currentMenuSelect,condition:tempMenuCondition},
                success: function(markup, status){
                
                  // DEBUG
                  //console.log('AJAX RETURN2 :');
                  //console.log({markup:markup,status:status});
                  
                  // If the markup is not empty, replace this menu's options
                  if (markup.length){
                    var tempMarkup = $(markup); 
                    tempMenuWrapper.find('.option,.chapter_select').not('.option_sticky').remove();
                    tempMenuWrapper.append(tempMarkup.html());
                    $('.option_message:gt(0)', tempMenuWrapper).trigger('click');
                    }
                  
                  // If this was a mission select loading, auto-click the proper chapter
                  if (currentMenuSelect == 'this_battle_token'){
                    //console.log('auto-click battle option!');
                    // Check to see which one should be displayed first and autoclick it
                    if ($('.chapter_link_active', tempMenuWrapper).length){ var firstChapterLink = $('.chapter_link_active', tempMenuWrapper); }
                    else { var firstChapterLink = $('.chapter_link[data-chapter]:first-child', tempMenuWrapper); }
                    firstChapterLink.trigger('click');                     
                  }
                  
                  // Trigger the callback function
                  tempCallbackFunction();        
                  
                  }
                });              
              
              }
            
            });
          
        }          
        
        } else {
          
        // Trigger the callback function anyway
        tempCallbackFunction();          
          
        }      
      
      
      };
      
    // Create the temporary fadein function for this menu
    var tempFadeinFunction = function(tempCallbackFunction){
      
      if (tempCallbackFunction == undefined){ tempCallbackFunction = function(){}; }
      
      // DEBUG
      //console.log('tempFadeinFunction triggered');
      
      // Create the function to be executed after the menu has faded out
      var tempFadeoutFadeout = function(){
        
        // DEBUG
        //console.log('.menu[data-step]:not(.menu_hide) has completed animation');
        
        // Once the menu is faded, remove it from display
        $(this).addClass('menu_hide').css({opacity:0,marginLeft:'0',marginRight:'0'});
        
        // Check if the stepNumber is numeric or not
        if (switchOptions.stepNumber !== false){
          
          // Collect the main banner title
          var thisBanner = $('.banner', thisContext);
          var thisBannerTitle = thisBanner.attr('title');
          // Collect a reference to the current menus
          var thisMenu = $('.menu[data-step='+switchOptions.stepNumber+']', thisContext);
          var thisMenuTitle = thisMenu.attr('data-title');        
          
          // Update the banner text with this menu subtitle
          $('.title', thisBanner).html(thisBannerTitle+' : '+thisMenuTitle);
          // Check how many choices are available
          var thisMenuChoices = $('.option[data-token]:not(.option_disabled):not(.option[data-parent])', thisMenu);
          //alert('Menu choices : '+thisMenuChoices.length);
          // Check if there is only one menu-choice available and skip is enabled
          if (switchOptions.autoSkip == 'true' && thisMenuChoices.length <= 1){
            //alert('Auto Skip triggered...');
            // Secretly unhide the current menu and auto-click the only available option
            thisMenu.css({opacity:0}).removeClass('menu_hide');
            thisMenuChoices.eq(0).trigger('click');
            } else {
            // Unhide the current menu so the user can pick
            thisMenu.css(slideInAnimation).removeClass('menu_hide').animate({opacity:1.0,marginLeft:'0',marginRight:'0'}, 400, 'swing');
            }
          
          } else if (switchOptions.stepName !== false){
          
          // Collect the main banner title
          var thisBanner = $('.banner', thisContext);
          var thisBannerTitle = thisBanner.attr('title');
          // Collect a reference to the current menus
          var thisMenu = $('.menu[data-step='+switchOptions.stepName+']', thisContext);
          var thisMenuTitle = thisMenu.attr('data-title');
          // Update the banner text with this menu subtitle
          $('.title', thisBanner).html(thisBannerTitle+' : '+thisMenuTitle);
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
              // Skip if this is an invalid option
              if (optionToken == 'this_player_robots'){ continue; }
              // Collect the option token and value
              var thisOptionToken = optionToken;
              var thisOptionValue = battleOptions[optionToken];
              // Generate the request data and post it to the server
              requestData += 'battle_settings,'+thisOptionToken+','+thisOptionValue+';';
              }
            // Post the generated request data to the server and wait for a reply
            var thisPostData = {requestType:requestType,requestData:requestData};
            //console.log('scripts/script.php', thisPostData);
            $.post('scripts/script.php',thisPostData,function(data){
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
      $('.menu[data-step]:not(.menu_hide)', thisContext).animate(slideOutAnimation, switchOptions.slideDuration, 'swing', tempFadeoutFadeout);
      
      // Execute option-specific commands for special cases
      switch (currentMenuSelect){
        case 'this_battle_token': {  
          
          // Prevent the player from fighting themselves in battle
          var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
          var tempMenu = $('.menu[data-select=this_battle_token]', thisContext);
          var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition+'"]', tempMenu);
          var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition+'"]', tempMenu);
          var availableMissions = $('.option[data-token]', tempShowOptionWrapper);
          $('.header', tempMenu).find('.count').html('Mission Select ('+(availableMissions.length == 1 ? '1 Mission' : availableMissions.length+' Missions')+')');                
          break;
          
          }
        case 'this_player_robots': { 
          
          // Prevent the player from fighting themselves in battle
          var tempCondition = 'this_player_token='+battleOptions['this_player_token'];
          var tempMenu = $('.menu[data-select=this_player_robots]', thisContext);
          var tempHideOptionWrapper = $('.option_wrapper[data-condition!="'+tempCondition+'"]', tempMenu);
          var tempShowOptionWrapper = $('.option_wrapper[data-condition="'+tempCondition+'"]', tempMenu);
          var tempWrapper = $('.option_wrapper[data-condition="'+tempCondition+'"]', tempMenu);
          var availableRobots = $('.option[data-child]', tempWrapper);        
          var tempMenuHeader = $('.header', tempMenu);
          tempMenuHeader.find('.count').html('Robot Select ('+(availableRobots.length == 1 ? '1 Robot' : availableRobots.length+' Robots')+')');                
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
    //tempFadeinFunction(tempReloadFunction);    
    
    
    }
}

// Define a function for updating the zenny amount in the menu
function prototype_update_zenny(newZenny){
  var thisZennyContainer = $('.banner .zenny .amount', thisPrototype);
  thisZennyContainer.html(newZenny);  
}