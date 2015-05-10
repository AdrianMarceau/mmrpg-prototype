// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisEditor = false;
var thisEditorData = {playerTotal:0,robotTotal:0};
var resizePlayerWrapper = function(){};
$(document).ready(function(){

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);
  thisEditor = $('#edit', thisBody);
  
  // Update the player and robot count by counting elements
  //thisEditorData.playerTotal = $('#canvas .wrapper[data-player]', thisEditor).length;
  //thisEditorData.robotTotal = $('#canvas .sprite[data-robot]', thisEditor).length;
  
  //console.log(thisEditorData);
  
  // Define a function for resizing player wrappers based on robot count
  resizePlayerWrapper = function(){
    $('#canvas .wrapper[data-player]', thisEditor).each(function(){
      var tempPlayerWrapper = $(this);
      var tempPlayerCell = tempPlayerWrapper.parent();
      var tempPlayerToken = tempPlayerWrapper.attr('data-player');
      var tempRobotCount = $('.sprite[data-robot]', tempPlayerWrapper).length;
      var tempRobotPercent = parseFloat(Math.round(((tempRobotCount / thisEditorData.robotTotal) * 100) * 100) / 100).toFixed(2);
      tempPlayerCell.css({width:tempRobotPercent+'%'});
      if (tempRobotCount < 2){ tempPlayerWrapper.find('.sort_wrapper').css({display:'none'}); }
      else { tempPlayerWrapper.find('.sort_wrapper').css({display:''}); }
      //console.log(thisEditorData);
      //console.log(tempPlayerToken+' has '+tempRobotCount+' robots which is '+tempRobotPercent+'% of the total');
      });
    };
    
  // Trigger the resize wrapper on load
  //resizePlayerWrapper();
  
  //alert('I, the edit, have a wap setting of '+(gameSettings.wapFlag ? 'true' : 'false')+'?! and my body has a class of '+$('body').attr('class')+'!');
  
  // Start playing the appropriate stage music
  //top.mmrpg_music_load('misc/data-base');
  
    //console.log('ummm');
    
  /*
  // If editing is not allowed, remove the sort button
  if (!gameSettings.allowEditing){ 
    //console.log('do not allow editing');
    $('.wrapper .sort', gameCanvas).remove(); 
    } else {
    //console.log('allow editing');  
    }
  */
    
  // Create the click event for canvas sort button
  $('.wrapper .sort', gameCanvas).live('click', function(e){    
    var thisSortButton = $(this);
    var thisSortToken = thisSortButton.attr('data-sort');
    var thisSortOrder = thisSortButton.attr('data-order');
    var thisSortPlayer = thisSortButton.attr('data-player');
    var thisPlayerContainer = $('.wrapper[data-player='+thisSortPlayer+']', gameCanvas);
    var thisPlayerRobots = $('.sprite[data-token]', thisPlayerContainer);
    var thisPlayerRobotsTokens = [];
    thisPlayerRobots.each(function(){ thisPlayerRobotsTokens.push($(this).attr('data-robot')); });
    thisPlayerRobotsTokens.join(',');
    //console.log('clicked sort; robot tokens = '+thisPlayerRobotsTokens);
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
  $('.sprite[data-token]', gameCanvas).live('click', function(e){
    e.preventDefault();
    var dataSprite = $(this);
    var dataParent = $(this).closest('.wrapper')
    var dataSelect = dataParent.attr('data-select');
    var dataToken = $(this).attr('data-token');
    var dataPlayer = $(this).attr('data-player');
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
            // Reset the select button's position and return false
            thisPlayerSelect.val(currentPlayerToken);
            //console.log(data);
            $('#edit_overlay', thisPrototype).css({display:'none'});
            return false;
            } else if (dataStatus == 'success'){
            //console.log('success! now let\'s move the robot...');
            //console.log(data);
            var newData = data.slice(2);
            newData = newData.join('|');
            $('#console #robots').append(newData);
            // Collect the container and token references and prepare the move
            var canvasButton = $('.sprite[data-robot='+thisRobotToken+']', gameCanvas);
            var consoleEvent = $('.event[data-token='+currentPlayerToken+'_'+thisRobotToken+']', gameConsole);
            var consolePlayerSelect = $('.player_select_block', consoleEvent);
            //if (!consolePlayerSelect.length){ //console.log('player select block not found'); }
            consolePlayerSelect.css({backgroundColor:'blue !important'});
            var newCanvasWrapper = $('.wrapper_'+newPlayerToken+'[data-select=robots]', gameCanvas);
            var newConsoleToken = newPlayerToken+'_'+thisRobotToken;
            if (newPlayerToken == 'dr-light'){ var newPlayerName = 'Dr. Light'; }
            else if (newPlayerToken == 'dr-wily'){ var newPlayerName = 'Dr. Wily'; }
            else if (newPlayerToken == 'dr-cossack'){ var newPlayerName = 'Dr. Cossack'; }
            // Remove this robot from the console
            consoleEvent.remove();            
            // Move this robot's button to the new wrapper and update their data token
            //$('.current_player', consolePlayerSelect).removeClass('current_player_'+currentPlayerToken).addClass('current_player_'+newPlayerToken);
            //$('.player_name label', consolePlayerSelect).html(newPlayerName);
            //$('.player_name select', consolePlayerSelect).attr('data-player', newPlayerToken);   
            //$('select.ability_name', consoleEvent).attr('data-player', newPlayerToken);
            canvasButton.removeClass('sprite_robot_'+currentPlayerToken).removeClass('sprite_robot_'+currentPlayerToken+'_current');
            canvasButton.addClass('sprite_robot_'+newPlayerToken).addClass('sprite_robot_'+newPlayerToken+'_current');    
            canvasButton.attr('data-player', newPlayerToken);
            canvasButton.attr('data-token', newConsoleToken);
            canvasButton.appendTo(newCanvasWrapper);
            //consoleEvent.attr('data-token', newConsoleToken); 
            // Reload the current page and return true  
            //window.location = window.location.href;
            // Trigger the wrapper resize function based on the new robot amount
            resizePlayerWrapper();
            $('#edit_overlay', thisPrototype).css({display:'none'});
            return true;
            } else {
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