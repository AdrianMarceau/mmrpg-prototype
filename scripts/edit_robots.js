// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisRobotCanvas = false;
var thisAbilityCanvas = false;
var thisItemCanvas = false;
var thisEditor = false;
var thisEditorData = {playerTotal:0,robotTotal:0};
var resizePlayerWrapper = function(){};
var loadConsoleRobotMarkup = function(thisSprite, index, complete){};
var loadCanvasAbilitiesMarkup = function(){};
var loadCanvasItemsMarkup = function(){};
var loadedConsoleRobotTokens = [];
$(document).ready(function(){

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);
  thisEditor = $('#edit', thisBody);
  thisConsole = $('#console', thisEditor);
  thisCanvas = $('#canvas', thisEditor);
  thisRobotCanvas = $('div[data-canvas=robots]', gameCanvas);
  thisAbilityCanvas = $('div[data-canvas=abilities]', gameCanvas);
  thisItemCanvas = $('div[data-canvas=items]', gameCanvas);
  
  // Update the player and robot count by counting elements
  //thisEditorData.playerTotal = $('.robot_canvas .wrapper[data-player]', thisCanvas).length;
  //thisEditorData.robotTotal = $('.robot_canvas .sprite[data-robot]', thisCanvas).length;
  
  // Automatically hide the console and canvas areas until we're ready
  thisConsole.css({height:0,minHeight:0});
  //thisCanvas.css({height:0,minHeight:0});
  
  //console.log(thisEditorData);
  
  // Define a function for resizing player wrappers based on robot count
  resizePlayerWrapper = function(){
    $('.robot_canvas .wrapper[data-player]', thisCanvas).each(function(){
      var tempPlayerWrapper = $(this);
      var tempPlayerCell = tempPlayerWrapper.parent();
      var tempPlayerToken = tempPlayerWrapper.attr('data-player');
      var tempRobotCount = $('.sprite[data-robot]', tempPlayerWrapper).length;
      var tempRobotPercent = parseFloat(Math.round(((tempRobotCount / thisEditorData.robotTotal) * 100) * 100) / 100).toFixed(2);
      //tempPlayerCell.css({width:tempRobotPercent+'%'});
      tempPlayerCell.css({width:''});
      if (tempRobotCount < 2){ tempPlayerWrapper.find('.sort_wrapper').css({display:'none'}); }
      else { tempPlayerWrapper.find('.sort_wrapper').css({display:''}); }
      //console.log(thisEditorData);
      //console.log(tempPlayerToken+' has '+tempRobotCount+' robots which is '+tempRobotPercent+'% of the total');
      });
    };

  // Define the batch function for loading robot data in a loop
  loadConsoleRobotMarkup = function(thisSprite, index, complete){
    
    var thisPlayerToken = thisSprite.attr('data-player');
    var thisRobotToken = thisSprite.attr('data-robot');
    if (index == undefined){ index = 0; }
    if (complete == undefined){ complete = function(){}; }
    //console.log('sending request for console data of '+thisPlayerToken+' '+thisRobotToken);

    $.post('frames/edit_robots.php', {
      action: 'console_markup',
      wap: gameSettings.wapFlag,
      edit: gameSettings.allowEditing ? 'true' : 'false',
      user_id: gameSettings.userNumber,
      player: thisPlayerToken,
      robot: thisRobotToken
      }, function(data, status){

      //console.log('console data received, appending '+thisPlayerToken+' '+thisRobotToken+'...');

      if (index == 0){
        thisConsole.animate({height:'230px',opacity:1},600,'swing',function(){
          //console.log('console animation complete');
          $(this).css({height:'auto'});
          });
        }

      $('#console #robots').append(data);
      thisSprite.animate({opacity:0.3},{duration:300,queue:false,easing:'swing',complete:function(){
        //console.log(thisPlayerToken+' '+thisRobotToken+' sprite animation complete');
        $(this).removeClass('notloaded').css({opacity:''});
        complete();
        }});

      countRobotsLoaded++;
      loadedConsoleRobotTokens.push(thisRobotToken);
      //$('.header', thisEditor).html('Robot Editor ('+countRobotsLoaded+' Robots)')

      });

    };

  // Define the batch function for loading all unlocked ability data
  loadCanvasAbilitiesMarkup = function(onComplete){  
    
    console.log('calling the loadCanvasAbilitiesMarkup() function'); 
    
    if (onComplete == undefined){ 
      onComplete = function(){
        var thisContainer = $(this);
        thisContainer.attr('data-player', '');
        thisContainer.attr('data-robot', '');
        thisContainer.attr('data-key', '');
        }; 
      }

    // If the links have not been loaded, do so now
    if ($('#canvas .ability_canvas .links').is(':empty')){
      $.post('frames/edit_robots.php', {
        action: 'canvas_abilities_markup',
        wap: gameSettings.wapFlag,
        edit: gameSettings.allowEditing ? 'true' : 'false',
        user_id: gameSettings.userNumber
        }, function(data, status){
        console.log('console ability data received, appending...');
        //console.log(data);
        // Append the link markup to the canvas
        $('#canvas .ability_canvas .links').empty().append(data);
        // Trigger scrollbars on any overflow containers
        $('#canvas .ability_canvas .wrapper_overflow', thisEditor).perfectScrollbar();        
        // Trigger the on complete function
        onComplete.call($('#canvas .ability_canvas'));
        });      
      } else {      
        // Fix scrollbars on any overflow containers
        $('#canvas .ability_canvas .wrapper_overflow', thisEditor).scrollTop(0).perfectScrollbar('update');        
        // Trigger the on complete function
        onComplete.call($('#canvas .ability_canvas'));        
      }

    };

  // Define the batch function for loading all unlocked item data
  loadCanvasItemsMarkup = function(onComplete){  
    
    console.log('calling the loadCanvasItemsMarkup() function'); 
    
    if (onComplete == undefined){ 
      onComplete = function(){
        var thisContainer = $(this);
        thisContainer.attr('data-player', '');
        thisContainer.attr('data-robot', '');
        thisContainer.attr('data-key', '');
        }; 
      }

    // If the links have not been loaded, do so now
    if ($('#canvas .item_canvas .links').is(':empty')){
      $.post('frames/edit_robots.php', {
        action: 'canvas_items_markup',
        wap: gameSettings.wapFlag,
        edit: gameSettings.allowEditing ? 'true' : 'false',
        user_id: gameSettings.userNumber
        }, function(data, status){
        console.log('console item data received, appending...');
        //console.log(data);
        // Append the link markup to the canvas
        $('#canvas .item_canvas .links').empty().append(data);
        // Trigger scrollbars on any overflow containers
        $('#canvas .item_canvas .wrapper_overflow', thisEditor).perfectScrollbar();        
        // Trigger the on complete function
        onComplete.call($('#canvas .item_canvas'));
        });      
      } else {      
        // Fix scrollbars on any overflow containers
        $('#canvas .item_canvas .wrapper_overflow', thisEditor).scrollTop(0).perfectScrollbar('update');        
        // Trigger the on complete function
        onComplete.call($('#canvas .item_canvas'));        
      }

    };
    
  // Create the click event for canvas sort button
  $('div[data-canvas=robots] .sort', gameCanvas).live('click', function(e){
    
    var thisSortButton = $(this);
    var thisSortToken = thisSortButton.attr('data-sort');
    var thisSortOrder = thisSortButton.attr('data-order');
    var thisSortPlayer = thisSortButton.attr('data-player');
    var thisPlayerContainer = $('.wrapper[data-player='+thisSortPlayer+']', gameCanvas);
    var thisPlayerRobots = $('.sprite[data-token]', thisPlayerContainer);
    var thisPlayerRobotsTokens = [];
    thisPlayerRobots.each(function(){ thisPlayerRobotsTokens.push($(this).attr('data-robot')); });
    thisPlayerRobotsTokens.join(',');
    console.log('clicked sort; robot tokens = '+thisPlayerRobotsTokens);
    // Define the post options for the ajax call
    var postData = {action:'sort',token:thisSortToken,order:thisSortOrder,player:thisSortPlayer};    
    //console.log('postData', postData);
    // Reverse the sort direction for next click
    if (thisSortOrder == 'asc'){ thisSortButton.attr('data-order', 'desc'); }
    else if (thisSortOrder == 'desc'){ thisSortButton.attr('data-order', 'asc'); }
    // Post the sort request to the server
    $.ajax({
      type: 'POST',
      url: 'frames/edit_robots.php',
      data: postData,
      success: function(data, status){
        
        // DEBUG
        //alert(data);
        
        // Break apart the response into parts
        var data = data.split('|');
        var dataStatus = data[0] != undefined ? data[0] : false;
        var dataMessage = data[1] != undefined ? data[1] : false;
        var dataContent = data[2] != undefined ? data[2] : false;
        
        // If there was an error, reset select, otherwise, refresh the page
        if (dataStatus == 'error'){
          //console.log('error');
          //console.log(data);
          return false;
          } else if (dataStatus == 'success'){            
          //console.log('success');
          //console.log(data); 
          // check if the array is the same
          //if (dataContent == thisPlayerRobotsTokens){ //console.log('dataContent == thisPlayerRobotsTokens'); }
          //else { //console.log('dataContent != thisPlayerRobotsTokens'); }
          // split the new order
          var myArrayOrder = dataContent.split(',');
          //console.log(myArrayOrder);
          // get array of elements
          var myArray = thisPlayerRobots;
          // sort based on timestamp attribute
          myArray.sort(function (a, b){
            // convert to integers from strings
            var robotToken1 = $(a).attr('data-robot');
            var robotToken2 = $(b).attr('data-robot');    
            var robotToken1Position = myArrayOrder.indexOf(robotToken1);
            var robotToken2Position = myArrayOrder.indexOf(robotToken2);
            //console.log('robotToken1('+robotToken1+') = robotToken1Position('+robotToken1Position+');\n');
            //console.log('robotToken2('+robotToken2+') = robotToken2Position('+robotToken2Position+');\n ');
            // compare
            if (robotToken1Position > robotToken2Position) { return 1; } 
            else if (robotToken1Position < robotToken2Position) { return -1; } 
            else { return 0; }
            });     
          // put sorted results back on page
          thisPlayerContainer.find('.sprite[data-token]').remove();
          thisPlayerContainer.append(myArray);
          return true;
          } else {
          //console.log('ummmm');
          //console.log(data);
          return false;
          }
        
        // DEBUG
        //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');  
        
        }
      });
 
    });
 
  
  // Create the click event for canvas sprites
  $('.robot_canvas .sprite[data-token]', gameCanvas).live('click', function(e){
    e.preventDefault();
    
    var dataSprite = $(this);
    var dataSpriteIndex = dataSprite.index();      
    var dataParent = dataSprite.closest('.wrapper')
    var dataSelect = dataParent.attr('data-select');
    var dataToken = dataSprite.attr('data-token');
    var dataRobot = dataSprite.attr('data-robot');
    var dataPlayer = dataSprite.attr('data-player');
    if (dataSprite.hasClass('loading')){ return false; }
    dataSprite.addClass('loading');
    
    $('.robot_canvas .sprite[data-token]', gameCanvas).css({opacity:''});
    
    // Define the show function for this sprite
    var showFunction = function(){
      var dataSelectorCurrent = '#'+dataSelect+' .event_visible';
      var dataSelectorNext = '#'+dataSelect+' .event[data-token='+dataToken+']';
      $('.sprite[data-token]', gameCanvas).removeClass('sprite_robot_current').removeClass('sprite_robot_dr-light_current sprite_robot_dr-wily_current sprite_robot_dr-cossack_current');
      dataSprite.addClass('sprite_robot_current').addClass('sprite_robot_current').addClass('sprite_robot_'+dataPlayer+'_current');
      dataParent.css({display:'block'});
      if ($(dataSelectorCurrent, gameConsole).length){
        $(dataSelectorCurrent, gameConsole).stop().animate({opacity:0},250,'swing',function(){
          $(this).removeClass('event_visible').addClass('event_hidden').css({opacity:1});
          $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
          });
        } else {
          $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
        } 
      dataSprite.removeClass('loading');
      }    
    
    // Load the robot data if not already loaded
    if (loadedConsoleRobotTokens.indexOf(dataRobot) == -1){
      loadConsoleRobotMarkup(dataSprite, dataSpriteIndex, showFunction); 
      } else {
      showFunction();
      }
    
    });  
  
  // Create the change event for the player selectors
  $('select.player_name', gameConsole).live('change', function(e){
    
    // Prevent the default action
    e.preventDefault(); 
    
    // Collect a reference to this select object
    var thisPlayerSelect = $(this);
    var thisPlayerLink = thisPlayerSelect.parent();
    var newPlayerOption = $('option:selected', thisPlayerSelect);
    // Collect the current robot name and token
    var thisRobotToken = thisPlayerSelect.attr('data-robot');
    var thisRobotName = $('a[data-robot='+thisRobotToken+']', gameCanvas).attr('title');
    // Collect the current and target player tokens
    var currentPlayerToken = thisPlayerSelect.attr('data-player');
    var currentPlayerLabel = $('label', thisPlayerLink).html();
    var newPlayerToken = newPlayerOption.val();
    var newPlayerLabel = newPlayerOption.html();
    // Count the number of other options for this player
    var thisParentWrapper = $('.wrapper_'+currentPlayerToken, gameCanvas);
    var countRobotOptions = $('a[data-token]', thisParentWrapper).length;    
    if (countRobotOptions < 2){ thisPlayerSelect.val(currentPlayerToken); return false; }
    
    // Call the trasfer robot function to take care of the rest
    return transferRobotToPlayer(thisRobotToken, currentPlayerToken, newPlayerToken, true, true);
    
    });

  // Prevent clicks if the parent container is disabled
  $('a.ability_name', gameConsole).live('click', function(e){
    e.preventDefault();
    var thisLink = $(this);
    var thisContainer = thisLink.parent();
    var thisContainerStatus = thisContainer.attr('data-status') != undefined ? thisContainer.attr('data-status') : 'enabled';
    var thisLinkStatus = thisLink.attr('data-status') != undefined ? thisLink.attr('data-status') : 'enabled';
    var thisRobotEvent = thisLink.parents('.event[data-player][data-robot]');
    console.log('thisContainerStatus = '+thisContainerStatus+', thisLinkStatus = '+thisLinkStatus);
    if (thisContainerStatus == 'disabled'){

      //console.log('container is disabled');
      
      return false;
      
      } else if (thisLinkStatus == 'pending'){

      //console.log('ability already selected, revert to normal menu');
      
      $('a.player_name', thisRobotEvent).css({opacity:''}).removeAttr('data-status');
      $('a.item_name', thisRobotEvent).css({opacity:''}).removeAttr('data-status');
      $('a.ability_name', thisRobotEvent).css({opacity:''}).removeAttr('data-status');
      
      thisAbilityCanvas.attr('data-player', '');
      thisAbilityCanvas.attr('data-robot', '');
      thisAbilityCanvas.attr('data-key', '');
      
      thisItemCanvas.addClass('hidden');
      thisAbilityCanvas.addClass('hidden');
      thisAbilityCanvas.find('.wrapper_header').html('Select Ability');      
      
      thisRobotCanvas.removeClass('hidden');
      
      return true;
      
      } else {
        
      var thisPlayerToken = thisRobotEvent.attr('data-player');
      var thisRobotToken = thisRobotEvent.attr('data-robot');
      var thisRobotName = thisRobotEvent.find('.header .title').html();
      var thisAbilityKey = parseInt(thisLink.attr('data-key'));
      var thisAbilityID = parseInt(thisLink.attr('data-id'));
      
      var thisAbilityCompatible = thisRobotEvent.find('.ability_container').attr('data-compatible');
      thisAbilityCompatible = thisAbilityCompatible.split(',');
      var thisAbilityEquipped = [];
      $('.ability_container .ability_name[data-id]', thisRobotEvent).each(function(index,element){
        var thisID = $(this).attr('data-id');
        thisAbilityEquipped.push(thisID);
        });
      
      var thisRobotTypes = thisRobotEvent.attr('data-types');
        
      thisLink.css({opacity:''});
      thisLink.attr('data-status', 'pending');
      $('a.ability_name', thisRobotEvent).not(thisLink).css({opacity:0.3}).attr('data-status', 'disabled');
      $('a.player_name', thisRobotEvent).css({opacity:0.3}).attr('data-status', 'disabled');
      $('a.item_name', thisRobotEvent).css({opacity:0.3}).attr('data-status', 'disabled');
      
      loadCanvasAbilitiesMarkup(function(){
        
        //console.log('custom complete function!');
        
        thisAbilityCanvas.attr('data-player', thisPlayerToken);
        thisAbilityCanvas.attr('data-robot', thisRobotToken);
        thisAbilityCanvas.attr('data-key', thisAbilityKey);
        
        thisItemCanvas.addClass('hidden');
        thisRobotCanvas.addClass('hidden');
        thisAbilityCanvas.find('.wrapper_header').html('Select Ability for '+thisRobotName).attr('class', 'wrapper_header ability_type type_'+thisRobotTypes);
        thisAbilityCanvas.removeClass('hidden');
        
        $('.wrapper_overflow', thisAbilityCanvas).scrollTop(0).perfectScrollbar('update');
        
        $('.ability_name[data-id]', thisAbilityCanvas).each(function(index, element){
          var thisID = $(this).attr('data-id');
          if (thisAbilityCompatible.indexOf(thisID) == -1){               
            //console.log('ID '+thisID+' is NOT compatible'); 
            $(this).attr('data-status', 'disabled').css({display:'none',opacity:''});            
            } else {               
            //console.log('ID '+thisID+' is compatible');   
            if (thisID == thisAbilityID){ $(this).attr('data-status', 'disabled').css({display:''}); }
            else if (thisAbilityEquipped.indexOf(thisID) != -1){ $(this).attr('data-status', 'disabled').css({display:''}); }
            else { $(this).removeAttr('data-status').css({display:''});  }                        
            }
          });
      
        });  
      
      }
    });

  // Prevent clicks if the parent container is disabled
  $('a.item_name', gameConsole).live('click', function(e){
    e.preventDefault();
    var thisLink = $(this);
    var thisContainer = thisLink.parent();
    var thisContainerStatus = thisContainer.attr('data-status') != undefined ? thisContainer.attr('data-status') : 'enabled';
    var thisLinkStatus = thisLink.attr('data-status') != undefined ? thisLink.attr('data-status') : 'enabled';
    var thisRobotEvent = thisLink.parents('.event[data-player][data-robot]');
    console.log('thisContainerStatus = '+thisContainerStatus+', thisLinkStatus = '+thisLinkStatus);
    if (thisContainerStatus == 'disabled'){

      //console.log('container is disabled');
      
      return false;
      
      } else if (thisLinkStatus == 'pending'){

      //console.log('item already selected, revert to normal menu');
      
      $('a.player_name', thisRobotEvent).css({opacity:''}).removeAttr('data-status');
      $('a.item_name', thisRobotEvent).css({opacity:''}).removeAttr('data-status');
      $('a.ability_name', thisRobotEvent).css({opacity:''}).removeAttr('data-status');
      
      thisItemCanvas.attr('data-player', '');
      thisItemCanvas.attr('data-robot', '');

      thisAbilityCanvas.addClass('hidden');
      thisItemCanvas.addClass('hidden');
      thisItemCanvas.find('.wrapper_header').html('Select Item');      
      
      thisRobotCanvas.removeClass('hidden');
      
      return true;
      
      } else {
        
      var thisPlayerToken = thisRobotEvent.attr('data-player');
      var thisRobotToken = thisRobotEvent.attr('data-robot');
      var thisRobotName = thisRobotEvent.find('.header .title').html();
        
      thisLink.css({opacity:''});
      thisLink.attr('data-status', 'pending');
      $('a.item_name', thisRobotEvent).not(thisLink).css({opacity:0.3}).attr('data-status', 'disabled');
      $('a.player_name', thisRobotEvent).css({opacity:0.3}).attr('data-status', 'disabled');
      $('a.ability_name', thisRobotEvent).css({opacity:0.3}).attr('data-status', 'disabled');
      
      loadCanvasItemsMarkup(function(){
        
        //console.log('custom complete function!');
        
        thisItemCanvas.attr('data-player', thisPlayerToken);
        thisItemCanvas.attr('data-robot', thisRobotToken);

        thisAbilityCanvas.addClass('hidden');
        thisRobotCanvas.addClass('hidden');
        thisItemCanvas.find('.wrapper_header').html('Select Hold Item for '+thisRobotName);
        thisItemCanvas.removeClass('hidden');
        
        $('.wrapper_overflow', thisItemCanvas).scrollTop(0).perfectScrollbar('update');
        
        $('.item_name[data-count]', thisItemCanvas).each(function(index, element){
          var thisCount = parseInt($(this).attr('data-count'));
          if (thisCount < 1){               
            //console.log('Count '+thisCount+' is less than one'); 
            $(this).attr('data-status', 'disabled').css({display:'none'});            
            } else {               
            //console.log('thisCount '+thisCount+' is more than one'); 
            $(this).removeAttr('data-status').css({display:''});            
            }
          });
      
        });  
      
      }
    });
  
  // Prevent clicks if the parent container is disabled
  $('select.ability_name', gameConsole).live('click', function(e){
    var thisSelect = $(this);
    var thisLink = thisSelect.parent();
    var thisContainer = thisLink.parent();
    var thisContainerStatus = thisContainer.attr('data-status') != undefined ? thisContainer.attr('data-status') : 'enabled';
    if (thisContainerStatus == 'disabled'){
      e.preventDefault();
      return false;
      }
    });
  
  // Create the change event for the ability selectors
  $('select.ability_name', gameConsole).live('change', function(e){
    // Prevent the default action
    e.preventDefault();
    // Collect the global reference objects
    var thisSelect = $(this);
    var thisLink = thisSelect.parent();
    var thisContainer = thisLink.parent();
    var thisContainerStatus = thisContainer.attr('data-status') != undefined ? thisContainer.attr('data-status') : 'enabled';
    var thisLabel = $('label', thisLink);
    var dataKey = thisSelect.attr('data-key');
    var dataPlayer = thisSelect.attr('data-player');
    var dataRobot = thisSelect.attr('data-robot');
    var optionSelected = $('option:selected', thisSelect);
    var optionAbilityToken = optionSelected.val();
    var postData = {action:'ability',key:dataKey,player:dataPlayer,robot:dataRobot};
    //if (dataKey == 0 && !optionAbilityToken.length){ alert('first option cannot be empty!'); return false; }
    if (optionAbilityToken.length){ postData.ability = optionAbilityToken; } 
    else { postData.ability = ''; }

    // Ensure the parent container is enabled before sending any AJAX, else just update text
    if (thisContainerStatus == 'enabled'){
      
      // Change the body cursor to wait
      $('body').css('cursor', 'wait !important');
      // Temporarily disable the window while we update stuff
      thisContainer.css({opacity:0.25}).attr('data-status', 'disabled');
      // Loop through all this ability links for this robot and disable
      $('select.ability_name', thisContainer).each(function(key, value){
        $(this).attr('disabled', 'disabled').prop('disabled', true);
        });
      
      
      
      // Post this change back to the server
      $.ajax({
        type: 'POST',
        url: 'frames/edit_robots.php',
        data: postData,
        success: function(data, status){
          
          // DEBUG
          //alert(data);
          
          // Break apart the response into parts
          var data = data.split('|');
          var dataStatus = data[0] != undefined ? data[0] : false;
          var dataMessage = data[1] != undefined ? data[1] : false;
          var dataContent = data[2] != undefined ? data[2] : false;
          
          // DEBUG
          //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');
          
          // If the ability change was a success, flash the box green
          if (dataStatus == 'success'){
            
            // Make the clicked link flash green to show success
            thisLink.css({borderColor:'green !important'});
            var tempTimeout = setTimeout(function(){ thisLink.css({borderColor:''}); }, 1000);          
            
            // Update the link attributes based on if the ability was empty
            if (optionAbilityToken.length){
              var optionAbilityLabel = optionSelected.attr('data-label');
              var optionAbilityTitle = optionSelected.attr('title');
              var optionAbilityTooltip = optionSelected.attr('data-tooltip');
              var optionAbilityImage = 'images/abilities/'+optionAbilityToken+'/icon_left_40x40.png?';
              var optionAbilityType = optionSelected.attr('data-type');
              var optionAbilityType2 = optionSelected.attr('data-type2');
              thisLink.attr('data-ability', optionAbilityToken).attr('title', optionAbilityTitle).attr('data-tooltip', optionAbilityTooltip);
              thisLink.removeClass().addClass('ability_name ability_type ability_type_'+optionAbilityType+(optionAbilityType2.length ? '_'+optionAbilityType2 : '')+'');
              thisSelect.attr('data-ability', optionAbilityToken);
              thisLabel.html(optionAbilityLabel).css({backgroundImage:'url('+optionAbilityImage+')'});
              } else {
              thisLink.attr('data-ability', '').attr('title', '-').attr('data-tooltip', '-');
              thisLink.removeClass().addClass('ability_name ability_type ability_type_none');
              thisSelect.attr('data-ability', '').val('');
              thisLabel.html('-').css({backgroundImage:'none'});
              }         
            
            // Create the empty ability count variable
            var emptyAbilityCount = 0;
            // If the new ability list was provided, break it apart
            if (dataContent.length){ var newAbilityList = dataContent.split(','); }
            else { var newAbilityList = []; }
            
            // Loop through all this ability links for this robot and update
            $('a.ability_name', thisContainer).each(function(key, value){
              
              // Collect the approriate reference variables
              var tempLink = $(this);
              var tempSelect = $('select', tempLink);
              var tempOption = $('option:selected', tempSelect);
              var tempLabel = $('label', tempLink);
              var tempAbility = tempOption.val();
              
              // If a new ability list was provided, update this link
              if (newAbilityList.length){
                
                // Collect the new ability from this position in the list
                var newAbility = newAbilityList[key] != undefined ? newAbilityList[key]: '';
                
                // DEBUG
                //alert('current ability at position '+key+' is ['+tempAbility+']...');
                
                // Update the select box with the new ability and recollect it's value
                tempSelect.val(newAbility);
                tempOption = $('option:selected', tempSelect);
                tempAbility = tempOption.val();
                
                // Update the link attributes based on if the new ability was empty
                if (newAbility.length){
                  var newAbilityLabel = tempOption.attr('data-label');
                  var newAbilityTitle = tempOption.attr('title');
                  var newAbilityTooltip = tempOption.attr('data-tooltip');
                  var newAbilityImage = 'images/abilities/'+newAbility+'/icon_left_40x40.png?';
                  var newAbilityType = tempOption.attr('data-type');
                  var newAbilityType2 = tempOption.attr('data-type2');
                  tempLink.attr('data-ability', newAbility).attr('title', newAbilityTitle).attr('data-tooltip', newAbilityTooltip);
                  tempLink.removeClass().addClass('ability_name ability_type ability_type_'+newAbilityType+(newAbilityType2.length ? '_'+newAbilityType2 : ''));
                  tempSelect.attr('data-ability', newAbility);
                  tempSelect.find('option').prop('disabled', false);
                  tempSelect.find('option[value='+newAbility+']').prop('disabled', true);
                  tempLabel.html(newAbilityLabel).css({backgroundImage:'url('+newAbilityImage+')'});
                  } else {
                  tempLink.attr('data-ability', '').attr('title', '-').attr('data-tooltip', '-');
                  tempLink.removeClass().addClass('ability_name ability_type ability_type_none');
                  tempSelect.attr('data-ability', '').val('');
                  tempSelect.find('option').prop('disabled', false);
                  tempLabel.html('-').css({backgroundImage:'none'});
                  }                 
                
                // DEBUG
                //alert('...but should be ['+newAbility+']!');
                                
                }            
              
              // Disable any overflow ability containers
              if (!tempAbility.length){ emptyAbilityCount++; }
              if (emptyAbilityCount >= 2){ tempLink.css({opacity:0.25}); tempSelect.attr('disabled', 'disabled'); }
              else { tempLink.css({opacity:1.0}); tempSelect.removeAttr('disabled'); }
              
              });           
  
            }
          
            // Change the body cursor back to default
            $('body').css('cursor', '');
            // Enable the conatiner again now that we're done
            thisContainer.css({opacity:1.0}).attr('data-status', 'enabled');      
            // Loop through all this ability links for this robot and disable
            $('select.ability_name', thisContainer).each(function(key, value){
              $(this).removeAttr('disabled').prop('disabled', false);
              });          
                   
          }
        });      
      
      } else {
        
      // Update the link attributes based on if the ability was empty
      if (optionAbilityToken.length){
        var optionAbilityLabel = optionSelected.attr('data-label');
        var optionAbilityImage = 'images/abilities/'+optionAbilityToken+'/icon_left_40x40.png?';
        var optionAbilityTitle = optionSelected.attr('title');
        var optionAbilityTooltip = optionSelected.attr('data-tooltip');
        var optionAbilityType = optionSelected.attr('data-type');
        thisLink.attr('data-ability', optionAbilityToken).attr('title', optionAbilityTitle).attr('data-tooltip', optionAbilityTooltip);
        thisLink.removeClass().addClass('ability_name ability_type ability_type_'+optionAbilityType);
        thisSelect.attr('data-ability', optionAbilityToken);
        thisLabel.html(optionAbilityLabel).css({backgroundImage:'url('+optionAbilityImage+')'});
        } else {
        thisLink.attr('data-ability', '').attr('title', '-').attr('data-tooltip', '-');
        thisLink.removeClass().addClass('ability_name ability_type ability_type_none');
        thisSelect.attr('data-ability', '').val('');
        thisLabel.html('-').css({backgroundImage:'none'});
        }
        
      }
    
    
    
    
    
    });//.trigger('change');
  
  
  // PROCESS ALT CHANGE ACTION
  
  // Collect the base href from the header
  var thisBaseHref = $('head base').attr('href'); 
  
  // Define a function for hovering over the image alt link
  $('.robot_image_alts', gameConsole).live({
    mouseenter: function (){
      //console.log('mousein robot image alt');
      var altSprite = $(this).find('.sprite_robot');
      if (altSprite.is(':animated')){ return false; }
      var altFrame = $(this).is('a') ? 'taunt' : 'defend';
      updateSpriteFrame(altSprite, altFrame);
      return true;
      },
    mouseleave: function (){
      //console.log('mousein robot image alt');
      var altSprite = $(this).find('.sprite_robot');
      if (altSprite.is(':animated')){ return false; }
      updateSpriteFrame(altSprite, 'base');
      return true;
      }
    });
  
  // Attach a click event to the sprite image switcher
  $('a.robot_image_alts', gameConsole).live('click', function(e){
    e.preventDefault();
    
    // If we're not supposed to be editing, return false
    if (!gameSettings.allowEditing){ return false; }        
    
    // Collect references to the editor objects and player/robot tokens
    var thisLink = $(this);
    var thisSprite = thisLink.find('.sprite_robot');
    var thisPlayerToken = thisLink.attr('data-player');
    var thisRobotToken = thisLink.attr('data-robot');
    
    // If we're already animating, return false
    if (thisSprite.is(':animated')){ return false; }
    
    // Collect all relevant sprites based on the above info from both the console and canvas areas
    var thisConsoleSprites = $('.event_visible[data-token='+thisPlayerToken+'_'+thisRobotToken+'] .sprite_robot', gameConsole);    
    var thisCanvasSprites = $('.robot_canvas .sprite_robot[data-token='+thisPlayerToken+'_'+thisRobotToken+']', gameCanvas);
    
    // DEBUG
    //console.log('robot image alt switch!  :D');
    
    // Collect the size of the clicked robot sprite and use it to generate classes
    var robotSize = thisSprite.hasClass('.sprite_80x80') ? 80 : 40;
    var robotSizeText = robotSize+'x'+robotSize;
    var robotSizeClass = '.sprite_'+robotSizeText;
    
    // Collect the alternate skin/image index for this robot, break it down, and find our positions
    var robotImageIndex = thisLink.attr('data-alt-index') != undefined ? thisLink.attr('data-alt-index') : 'base';
    robotImageIndex = robotImageIndex.match(',') ? robotImageIndex.split(',') : [robotImageIndex];
    var thisCurrentImageToken = thisLink.attr('data-alt-current') != undefined ? thisLink.attr('data-alt-current') : 'base';
    var thisCurrentImageIndex = robotImageIndex.indexOf(thisCurrentImageToken);
    var thisCurrentFilePath = '/'+thisRobotToken+(thisCurrentImageToken != 'base' ? '_'+thisCurrentImageToken : '')+'/';
    
    // Generate the index key and file path for the skin/image we'll be switching to
    var newImageIndex = thisCurrentImageIndex + 1;
    if (newImageIndex >= robotImageIndex.length){ newImageIndex = 0; }
    var newImageToken = robotImageIndex[newImageIndex];
    var newFilePath = '/'+thisRobotToken+(newImageToken != 'base' ? '_'+newImageToken : '')+'/';
    
    // Collect the background image for this sprite and generate the new path
    var thisCurrentBackgroundImage = thisSprite.css('background-image');
    var newBackgroundImage = thisCurrentBackgroundImage.replace(thisCurrentFilePath, newFilePath);
    // Start preloading the new sprite sheet and mugshot images
    var preloadImages = {sprite:false,mugshot:false};
    var newSpritePath = newBackgroundImage.replace(/^url\("?([^\)\(]+)"?\)$/i, '$1');
    preloadImages.sprite = new Image();
    preloadImages.sprite.src = newSpritePath;
    var newMugPath = newSpritePath.replace('sprite_', 'mug_');
    preloadImages.mugshot = new Image();
    preloadImages.mugshot.src = newMugPath;
    
    // Update this robot to its victory frame so it's ready for switching
    updateSpriteFrame(thisSprite, 'victory');    

    // DEBUG
    //console.log({robotSize:robotSize,robotSizeText:robotSizeText,robotSizeClass:robotSizeClass});
    //console.log({robotImageIndex:robotImageIndex,thisCurrentImageToken:thisCurrentImageToken,thisCurrentImageIndex:thisCurrentImageIndex,thisCurrentFilePath:thisCurrentFilePath});
    //console.log({newImageIndex:newImageIndex,newImageToken:newImageToken,newFilePath:newFilePath});
    //console.log({thisCurrentBackgroundImage:thisCurrentBackgroundImage,newBackgroundImage:newBackgroundImage});       
    
    // Define a function for when all the background sprites have been updates
    var afterBackgroundUpdateComplete = function(nextGroup){
      //console.log('backgrounds have finished switching');                        
      if (nextGroup != undefined){
        // We still have to update the mugshot images and tokens
        //console.log('nextGroup provided, updating tokens and then mugshot background images');
        $('.token', thisLink).removeClass('token_active');
        $('.token', thisLink).eq(newImageIndex).addClass('token_active');
        nextGroup.each(function(){ updateBackgroundImageFunction($(this)); });           
        } else {
        //console.log('nextGroup undefined, updating server with new choice');
  
        // Post this change back to the server
        var postData = {action:'altimage',robot:thisRobotToken,player:thisPlayerToken,image:newImageToken};
        $.ajax({
          type: 'POST',
          url: 'frames/edit_robots.php',
          data: postData,
          success: function(data, status){        
            // DEBUG
            //alert(data);            
            // Break apart the response into parts
            var data = data.split('|');
            var dataStatus = data[0] != undefined ? data[0] : false;
            var dataMessage = data[1] != undefined ? data[1] : false;
            var dataContent = data[2] != undefined ? data[2] : false;            
            // DEBUG
            //console.log('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+',\n dataContent = '+dataContent+'; ');
            //console.log(data);
            //console.log('dataStatus:'+dataStatus);
            //console.log('dataMessage:'+dataMessage);   
            //console.log('dataContent:'+dataContent);            
            // If the ability change was a success, flash the box green
            if (dataStatus == 'success'){                            
              //console.log('success! this robot alt image has been updated');
              //console.log(data);                 
              return true;              
              }   
            
    
            // Hide the overlay to allow using the robot again
            return true;
            
            }
          });        
        
        
        }  
      };
    
    // Define a function for updating the backgrounds images of all relevant sprites
    var updateBackgroundTimeout = false;
    var updateBackgroundImageFunction = function(thisSprite, nextGroup){
      var thisParent = thisSprite.parent();
      var thisCurrentBackgroundImage = thisSprite.css('background-image');
      var newBackgroundImage = thisCurrentBackgroundImage.replace(thisCurrentFilePath, newFilePath);
      //console.log({thisCurrentBackgroundImage:thisCurrentBackgroundImage,newBackgroundImage:newBackgroundImage});      
      
      // If this sprite's parent element was a wrapper
      if (thisParent.is('.sprite_wrapper')){
        //console.log('parent wrapper was a sprite link');
        thisSprite.css({zIndex:1});
        var cloneSprite = thisSprite.clone();
        cloneSprite.css({backgroundImage:newBackgroundImage,opacity:0,zIndex:100});
        cloneSprite.appendTo(thisParent);
        cloneSprite.animate({opacity:1},{duration:1000,easing:'swing',queue:false,complete:function(){ 
          //console.log('animation complete');
          thisSprite.remove();          
          updateSpriteFrame(cloneSprite, 'base');
          }});
        }
      // Otherwise, just swap the image
      else {
        //console.log('parent wrapper was something else '+thisParent.attr('class'));
        thisSprite.css({backgroundImage:newBackgroundImage});
        updateSpriteFrame(thisSprite);  
        }        
                  
      clearTimeout(updateBackgroundTimeout);
      updateBackgroundTimeout = setTimeout(function(){ afterBackgroundUpdateComplete(nextGroup); }, 1100);
      
      
      };
      
    thisLink.attr('data-alt-current', newImageToken);
    thisConsoleSprites.each(function(){ return updateBackgroundImageFunction($(this), thisCanvasSprites); });
    
    return true;
    
    });
  
  
  // PROCESS FAVOURITE ACTION
  
  $('a.robot_favourite', gameConsole).live('click', function(e){
    
    if (!gameSettings.allowEditing){ return false; }
    
    var thisLink = $(this);
    var thisPlayerToken = thisLink.attr('data-player');
    var thisRobotToken = thisLink.attr('data-robot');
    
    e.preventDefault();
        
    //console.log('robot favourite clicked!'); 
          
    if (!thisLink.hasClass('robot_favourite_active')){ thisLink.addClass('robot_favourite_active'); }
    else { thisLink.removeClass('robot_favourite_active'); }
    
    // Show the overlay to prevent double-clicking
    //$('#edit_overlay', thisPrototype).css({display:'block'});     
    
    // Post this change back to the server
    var postData = {action:'favourite',robot:thisRobotToken,player:thisPlayerToken};
    $.ajax({
      type: 'POST',
      url: 'frames/edit_robots.php',
      data: postData,
      success: function(data, status){
      
        // DEBUG
        //alert(data);
        
        // Break apart the response into parts
        var data = data.split('|');
        var dataStatus = data[0] != undefined ? data[0] : false;
        var dataMessage = data[1] != undefined ? data[1] : false;
        var dataContent = data[2] != undefined ? data[2] : false;
        
        // DEBUG
        //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');
        //console.log(data);
        //console.log('dataStatus:'+dataStatus);
        //console.log('dataMessage:'+dataMessage);   
        //console.log('dataContent:'+dataContent);
        
        // If the ability change was a success, flash the box green
        if (dataStatus == 'success'){
          
          
          //console.log('success! now let\'s update the robot favourite marker...');
          //console.log(data);   
          
          if (dataContent == 'added'){ thisLink.addClass('robot_favourite_active'); }
          else if (dataContent == 'removed'){ thisLink.removeClass('robot_favourite_active'); }
          
          //$('#edit_overlay', thisPrototype).css({display:'none'});
          return true;
          
          }   
        

        // Hide the overlay to allow using the robot again
        //$('#edit_overlay', thisPrototype).css({display:'none'});     
        return true;
        
        }
      });          
    
    
    
    });  
  
    
  /*
  
  // Create the change event for the player selectors
  $('a.robot_level_reset', gameConsole).live('click', function(e){
    var thisLink = $(this);
    var thisRobotToken = thisLink.attr('data-robot');
    var thisRobotLevel = thisLink.attr('data-level');
    var thisPlayerToken = thisLink.attr('data-player');
    var thisConsoleEvent = $('.event[data-token='+thisPlayerToken+'_'+thisRobotToken+']', gameConsole);
    var thisRobotName = $('.robot_name', thisConsoleEvent).html();
    var thisPlayerName = $('.player_name label', thisConsoleEvent).html();
    
    // Ensure the parent container is enabled before sending any AJAX, else just update text
    if (true){       
      
      // Confirm with the user that they want to reset this robot to level 1
      var thisConfirm = confirm(
          (thisRobotLevel >= 100 
              ? 'Congratulations! '+thisRobotName+' has reached maximum power at Level 100! In reaching Level 100, all of '+thisRobotName+'\'s base stats are at their absolute limits and '+(thisRobotToken == 'roll' || thisRobotToken == 'disco' || thisRobotToken == 'rhythm' ? 'she' : 'he')+' has certainly become a powerful member of your team. Unfortunately, this also means '+(thisRobotToken == 'roll' || thisRobotToken == 'disco' ? 'she' : 'he')+' can no longer gain experience points or stat bonuses in battle. \n\n': 
                thisRobotName+' has only reached Level '+thisRobotLevel+', but '+(thisRobotToken == 'roll' || thisRobotToken == 'disco' ? 'she' : 'he')+' can be reboot any number of times during the course of the game.  Rebooting may make it easier to gain experience points and stat bonuses in battle, but the tradeoff may not be worth it until '+thisRobotName+' has reached Level 100... \n\n')+
          'Do you wish to reboot '+(thisRobotLevel >= 100 ? 'this robot' : thisRobotName)+' and start over from Level 1? All abilities will be retained as well as any stat bonuses '+(thisRobotToken == 'roll' || thisRobotToken == 'disco' ? 'she' : 'he')+' has earned during previous battles, but all base stats will be reset to their initial values and the robot will start over from the first level. \n\n'+
          'Continue with the reboot?'
          );
      // Make sure the user confirmed the reboot before proceeding
      if (thisConfirm){
        
        // Double-check in case it was an accident
        if (confirm('Are you REALLY sure?\nThis action cannot be undone!\nContinue?')){
          
          // Show the overlay to prevent double-clicking
          $('#edit_overlay', thisPrototype).css({display:'block'});     
          
          // Post this change back to the server
          var postData = {action:'level',player:thisPlayerToken,robot:thisRobotToken};
          $.ajax({
            type: 'POST',
            url: 'frames/edit_robots.php',
            data: postData,
            success: function(data, status){
            
              // DEBUG
              //alert(data);
              
              // Break apart the response into parts
              var data = data.split('|');
              var dataStatus = data[0] != undefined ? data[0] : false;
              var dataMessage = data[1] != undefined ? data[1] : false;
              var dataContent = data[2] != undefined ? data[2] : false;
              
              // DEBUG
              //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');
              //console.log(data);
              //console.log('dataStatus:'+dataStatus);
              //console.log('dataMessage:'+dataMessage);   
              //console.log('dataContent:'+dataContent);
              
              // If the ability change was a success, flash the box green
              if (dataStatus == 'success'){
                
                
                //console.log('success! now let\'s move the robot...');
                //console.log(data);              
                // Collect the container and token references and prepare the move
                var consoleEvent = $('.event[data-token='+thisPlayerToken+'_'+thisRobotToken+']', gameConsole);
                // Remove this robot from the console
                consoleEvent.remove();            
                // And add the new one into view
                var newData = data.slice(2);
                newData = newData.join('|');
                $('#console #robots').append(newData);
                // Reload the current page and return true  
                $('#edit_overlay', thisPrototype).css({display:'none'});
                return true;
                
                }   
              
  
              // Hide the overlay to allow using the robot again
              $('#edit_overlay', thisPrototype).css({display:'none'});     
              return true;
              
              }
            });          
          
          }
        
        }  
      
      
      }
    
    });
    
  */

  // Attach resize events to the window
  thisWindow.resize(function(){ windowResizeFrame(); });
  setTimeout(function(){ windowResizeFrame(); }, 1000);
  windowResizeFrame();

  var windowHeight = $(window).height();
  var htmlHeight = $('html').height();
  var htmlScroll = $('html').scrollTop();
  //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

  // Fade in the leaderboard screen slowly
  thisBody.waitForImages(function(){
    var tempTimeout = setTimeout(function(){
      if (gameSettings.fadeIn){ thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
      else { thisBody.removeClass('hidden').css({opacity:1}); }
      // Let the parent window know the menu has loaded
      parent.prototype_menu_loaded();
      }, 1000);
    }, false, true);
  
  // Initialize the canvas markup
  robotEditorCanvasInit();
  
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

// Define a function for swapping the frame of a sprite
function updateSpriteFrame(thisSprite, newFrame){
  //console.log('updateSpriteFrame(thisSprite, '+newFrame+')');  
  thisSprite.attr('class', function(index,classes){
    //console.log('thisSprite.attr(class, function('+index+','+classes+')');
    var newClasses = classes.replace(/(^|\s)(sprite_[0-9]+x[0-9]+_)([a-z0-9]+)(\s|$)/i, '$1$2'+newFrame+'$4'); 
    //console.log('classes.replace($1$2'+newFrame+'$4) | newClasses =  '+newClasses);
    return newClasses;
    });  
}

// Define an initialization function to run when document ready
function robotEditorCanvasInit(){

  var robotCanvas = $('.robot_canvas', gameCanvas);
  var robotCanvasPlayerWrappers = false;
  
  robotCanvas.css({height:0,opacity:0});
  
  $.post('frames/edit_robots.php', {
    action: 'canvas_markup',
    wap: gameSettings.wapFlag,
    edit: gameSettings.allowEditing ? 'true' : 'false',
    user_id: gameSettings.userNumber
    }, function(data, status){

      //console.log('canvas data received, appending...');
      $('.links', robotCanvas).append(data);
      robotCanvas.animate({height:'154px',opacity:1},2000,'swing',function(){
        //console.log('canvas animation complete');
        $(this).css({height:'auto'});
        });
      thisEditorData.playerTotal = $('.wrapper[data-player]', robotCanvas).length;
      thisEditorData.robotTotal = $('.sprite[data-robot]', robotCanvas).length;
      
      robotCanvasPlayerWrappers = $('.wrapper[data-player]', robotCanvas);

      // Append the CONSOLE markup after load to prevent halting display and waiting players
      //console.log('sending request for console');
      gameConsole.css({height:0,opacity:0});

      // Loop through all the robot links in the canvas and load their data
      countRobotLinks = $('.sprite[data-robot]', robotCanvas).length;
      countRobotsTriggered = 0;
      countRobotsLoaded = 0;
      countWrapperLoop = 0;
      //$('.robot_canvas .sprite[data-player][data-robot]', gameCanvas).css({opacity:0.2});
      $('.sprite[data-robot]', robotCanvas).addClass('notloaded');
      while (countRobotsTriggered < countRobotLinks){

        $('.wrapper[data-player]', robotCanvas).each(function(index){
          var tempWrapper = $(this);
          var tempPlayer = $(this).attr('data-player');
          var tempRobot = $('.sprite[data-robot]', tempWrapper).eq(countWrapperLoop);
          if (!tempRobot.length || tempRobot == undefined){ return false; }
          else { loadConsoleRobotMarkup(tempRobot, index); }

          });

        countRobotsTriggered++;
        countWrapperLoop++;
        break;

        }
      
      robotCanvas.sortable({
        items: '.sprite[data-robot]',
        cancel: '.sprite:only-of-type',
        containment: robotCanvas, //'parent',
        connectWidth: robotCanvasPlayerWrappers, //'.wrapper[data-player]',
        opacity: 0.7,
        //axis: 'y',
        //handle: '.sprite',
        update: function(event, ui) { 
          return completeDragRobot(event, ui); 
          }
        });

      // Resize the player wrapper when done
      resizePlayerWrapper();
      
      // Trigger scrollbars on any overflow containers
      $('.wrapper_overflow', thisEditor).perfectScrollbar();

      });
  

  // Load the ability canvas in the background
  thisAbilityCanvas.addClass('hidden');
  loadCanvasAbilitiesMarkup();
  

  // Load the item canvas in the background
  thisItemCanvas.addClass('hidden');
  loadCanvasItemsMarkup();
  
}

// Define a function to trigger when a robot has been dragged around
function completeDragRobot(event, ui){
  
  var thisSortRobot = ui.item;
  var thisSortRobotToken = thisSortRobot.attr('data-robot');
  var thisSortRobotPlayer = thisSortRobot.attr('data-player');
  var thisSortParentPlayer = thisSortRobot.parents('.wrapper').attr('data-player');
  var thisSortRobotActive = thisSortRobot.hasClass('sprite_robot_current') ? true : false;

  //var data = thisSortRobot.attr('class'); //sortable('serialize');
  //console.log(data)
  
  // -- SORT ROBOT WITHIN SAME PARENT -- //
  
  // If the robot player and parent player are the same, this is a simple sort
  if (thisSortRobotPlayer == thisSortParentPlayer){

    updatePlayerSortOrder(thisSortRobotPlayer);
    
  }
  
  // -- TRANSFER ROBOT THEN SORT BOTH PARENTS -- //
  
  // Else if the robot player and parent player are not the same, we've got some work cut out...
  else if (thisSortRobotPlayer != thisSortParentPlayer){

    var showInConsole = thisSortRobotActive ? true : false;
    var updateInCanvas = false;
    transferRobotToPlayer(thisSortRobotToken, thisSortRobotPlayer, thisSortParentPlayer, showInConsole, updateInCanvas, function(){
      updatePlayerSortOrder(thisSortRobotPlayer);
      updatePlayerSortOrder(thisSortParentPlayer);      
      });
    
  }
  
}

//Define a function to call when a player wrapper needs to have it's sort updated
function updatePlayerSortOrder(sortPlayer){

  console.log('updatePlayerSortOrder(sortPlayer = '+sortPlayer+')');
  
  var thisSortToken = 'manual';
  var thisSortOrder = 'auto';
  var thisSortPlayer = sortPlayer; //thisSortRobot.attr('data-player');
  var thisPlayerContainer = $('.wrapper[data-player='+thisSortPlayer+']', gameCanvas);
  var thisPlayerRobots = $('.sprite[data-token]', thisPlayerContainer);
  var thisPlayerRobotsTokens = [];
  thisPlayerRobots.each(function(){ thisPlayerRobotsTokens.push($(this).attr('data-robot')); });
  thisPlayerRobotsTokens = thisPlayerRobotsTokens.join(',');
  console.log('manual for '+thisSortPlayer+' sort; robot tokens = '+thisPlayerRobotsTokens);
  
  // Define the post options for the ajax call
  var postData = {action:'sort',token:thisSortToken,order:thisSortOrder,player:thisSortPlayer,robots:thisPlayerRobotsTokens};    
  //console.log('postData', postData);
  
  // Reverse the sort direction for next click
  if (thisSortOrder == 'asc'){ thisSortButton.attr('data-order', 'desc'); }
  else if (thisSortOrder == 'desc'){ thisSortButton.attr('data-order', 'asc'); }
  // Post the sort request to the server
  $.ajax({
   type: 'POST',
   url: 'frames/edit_robots.php',
   data: postData,
   success: function(data, status){
     
     // DEBUG
     //alert(data);
     
     // Break apart the response into parts
     var data = data.split('|');
     var dataStatus = data[0] != undefined ? data[0] : false;
     var dataMessage = data[1] != undefined ? data[1] : false;
     var dataContent = data[2] != undefined ? data[2] : false;
     
     // If there was an error, reset select, otherwise, refresh the page
     if (dataStatus == 'error'){
       //console.log('error');
       //console.log(data);
       return false;
       } else if (dataStatus == 'success'){            
       //console.log('success');
       //console.log(data); 
       // check if the array is the same
       //if (dataContent == thisPlayerRobotsTokens){ //console.log('dataContent == thisPlayerRobotsTokens'); }
       //else { //console.log('dataContent != thisPlayerRobotsTokens'); }
       // split the new order
       var myArrayOrder = dataContent.split(',');
       //console.log(myArrayOrder);
       // get array of elements
       var myArray = thisPlayerRobots;
       // sort based on timestamp attribute
       myArray.sort(function (a, b){
         // convert to integers from strings
         var robotToken1 = $(a).attr('data-robot');
         var robotToken2 = $(b).attr('data-robot');    
         var robotToken1Position = myArrayOrder.indexOf(robotToken1);
         var robotToken2Position = myArrayOrder.indexOf(robotToken2);
         //console.log('robotToken1('+robotToken1+') = robotToken1Position('+robotToken1Position+');\n');
         //console.log('robotToken2('+robotToken2+') = robotToken2Position('+robotToken2Position+');\n ');
         // compare
         if (robotToken1Position > robotToken2Position) { return 1; } 
         else if (robotToken1Position < robotToken2Position) { return -1; } 
         else { return 0; }
         });     
       // put sorted results back on page
       thisPlayerContainer.find('.sprite[data-token]').remove();
       thisPlayerContainer.find('.wrapper_overflow').append(myArray);
       return true;
       } else {
       //console.log('ummmm');
       //console.log(data);
       return false;
       }
     
     // DEBUG
     //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');  
     
     }
   });

}

// Define a function to call when a player wrapper needs to have it's sort updated
function transferRobotToPlayer(thisRobotToken, currentPlayerToken, newPlayerToken, showInConsole, updateInCanvas, onComplete){
  
  console.log('transferRobotToPlayer(thisRobotToken = '+thisRobotToken+', currentPlayerToken = '+currentPlayerToken+', newPlayerToken = '+newPlayerToken+')');
  
  // Define the oncomplete if not defined
  if (onComplete == undefined){ onComplete = function(){ return true; }; }
  
  // Collect a reference to this select object
  var thisRobotConsole = $('.event[data-robot='+thisRobotToken+']', gameConsole);
  var thisPlayerSelect = $('select.player_name', thisRobotConsole);
  var thisPlayerLink = thisPlayerSelect.parent();
  var newPlayerOption = $('option[value='+newPlayerToken+']', thisPlayerSelect);
  thisPlayerSelect.val(newPlayerToken);
  // Collect the current robot name and token
  var thisRobotName = $('a[data-robot='+thisRobotToken+']', gameCanvas).attr('title');
  // Collect the current and target player tokens
  var currentPlayerLabel = $('label', thisPlayerLink).html();
  var newPlayerLabel = newPlayerOption.html();
  // Count the number of other options for this player
  var thisParentWrapper = $('.wrapper_'+currentPlayerToken, gameCanvas);
  var countRobotOptions = $('a[data-token]', thisParentWrapper).length;    
  if (countRobotOptions < 2){ thisPlayerSelect.val(currentPlayerToken); return false; }
  
  // Define the post options for the ajax call
  var postData = {action:'player',robot:thisRobotToken,player1:currentPlayerToken,player2:newPlayerToken};
  // Trigger a transfer of this robot to the requested player
  var confirmTransfer = true; //confirm('Transfer ownership of '+thisRobotName+' from '+currentPlayerLabel+' to '+newPlayerLabel+'?');
  if (confirmTransfer){
    $('#edit_overlay', thisPrototype).css({display:'block'});
    // Post the transfer request to the server
    $.ajax({
      type: 'POST',
      url: 'frames/edit_robots.php',
      data: postData,
      success: function(data, status){
        
        // DEBUG
        //alert(data);
        
        // Break apart the response into parts
        var data = data.split('|');
        var dataStatus = data[0] != undefined ? data[0] : false;
        var dataMessage = data[1] != undefined ? data[1] : false;
        var dataContent = data[2] != undefined ? data[2] : false;
        
        // If there was an error, reset select, otherwise, refresh the page
        if (dataStatus == 'error'){
          
          // -- POST STATUS ERROR -- //
          
          // Reset the select button's position and return false
          thisPlayerSelect.val(currentPlayerToken);
          //console.log(data);
          $('#edit_overlay', thisPrototype).css({display:'none'});
          return false;
          
          } else if (dataStatus == 'success'){
            
          // -- POST STATUS SUCCESS -- //
            
          //console.log('success! now let\'s move the robot...');
          //console.log(data);
          var newData = data.slice(2);
          newData = newData.join('|');
          if (showInConsole){ $('#console #robots').append(newData); }
          // Collect the container and token references and prepare the move
          var canvasButton = $('.sprite[data-robot='+thisRobotToken+']', gameCanvas);
          var consoleEvent = $('.event[data-token='+currentPlayerToken+'_'+thisRobotToken+']', gameConsole);
          var consolePlayerSelect = $('.player_select_block', consoleEvent);
          //if (!consolePlayerSelect.length){ //console.log('player select block not found'); }
          //consolePlayerSelect.css({backgroundColor:'blue !important'});
          var newCanvasWrapper = $('.wrapper_'+newPlayerToken+'[data-select=robots] .wrapper_overflow', gameCanvas);
          var newConsoleToken = newPlayerToken+'_'+thisRobotToken;
          if (newPlayerToken == 'dr-light'){ var newPlayerName = 'Dr. Light'; }
          else if (newPlayerToken == 'dr-wily'){ var newPlayerName = 'Dr. Wily'; }
          else if (newPlayerToken == 'dr-cossack'){ var newPlayerName = 'Dr. Cossack'; }
          // Remove this robot from the console
          if (showInConsole){ consoleEvent.remove(); }       
          // Move this robot's button to the new wrapper and update their data token
          //$('.current_player', consolePlayerSelect).removeClass('current_player_'+currentPlayerToken).addClass('current_player_'+newPlayerToken);
          //$('.player_name label', consolePlayerSelect).html(newPlayerName);
          //$('.player_name select', consolePlayerSelect).attr('data-player', newPlayerToken);   
          //$('select.ability_name', consoleEvent).attr('data-player', newPlayerToken);
          canvasButton.removeClass('sprite_robot_'+currentPlayerToken).removeClass('sprite_robot_'+currentPlayerToken+'_current');
          canvasButton.addClass('sprite_robot_'+newPlayerToken).addClass('sprite_robot_'+newPlayerToken+'_current');    
          canvasButton.attr('data-player', newPlayerToken);
          canvasButton.attr('data-token', newConsoleToken);
          if (updateInCanvas){ canvasButton.appendTo(newCanvasWrapper); }
          //consoleEvent.attr('data-token', newConsoleToken); 
          // Reload the current page and return true  
          //window.location = window.location.href;
          // Trigger the wrapper resize function based on the new robot amount
          resizePlayerWrapper();
          $('#edit_overlay', thisPrototype).css({display:'none'});
          return onComplete();
          //return true;
          
          } else {
            
          // -- POST STATUS UNKNOWN -- //
            
          //console.log('ummmm');
          //console.log(data);
          $('#edit_overlay', thisPrototype).css({display:'none'});
          return false;
          
          }
        
        // DEBUG
        //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');
        
        }
      });
    
    }
      
  return false;  

}