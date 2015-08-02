// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisEditor = false;
var thisEditorData = {playerTotal:0,playerTotal:0};
var resizePlayerWrapper = function(){};
$(document).ready(function(){

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);
  thisEditor = $('#edit', thisBody);
  
  // Update the player and player count by counting elements
  //thisEditorData.playerTotal = $('#canvas .wrapper[data-player]', thisEditor).length;
  //thisEditorData.playerTotal = $('#canvas .sprite[data-player]', thisEditor).length;
  
  //console.log(thisEditorData);
  
  // Define a function for resizing player wrappers based on player count
  /*
  resizePlayerWrapper = function(){
    $('#canvas .wrapper[data-player]', thisEditor).each(function(){
      var tempPlayerWrapper = $(this);
      var tempPlayerCell = tempPlayerWrapper.parent();
      var tempPlayerToken = tempPlayerWrapper.attr('data-player');
      var tempRobotCount = $('.sprite[data-player]', tempPlayerWrapper).length;
      var tempRobotPercent = Math.ceil((tempRobotCount / thisEditorData.playerTotal) * 100);
      tempPlayerCell.css({width:tempRobotPercent+'%'});
      if (tempRobotCount < 2){ tempPlayerWrapper.find('.sort').css({display:'none'}); }
      else { tempPlayerWrapper.find('.sort').css({display:''}); }
      //console.log(thisEditorData);
      //console.log(tempPlayerToken+' has '+tempRobotCount+' players which is '+tempRobotPercent+'% of the total');
      });
    };
  */
    
  // Trigger the resize wrapper on load
  //resizePlayerWrapper();
  
  //alert('I, the edit, have a wap setting of '+(gameSettings.wapFlag ? 'true' : 'false')+'?! and my body has a class of '+$('body').attr('class')+'!');
  
  // Start playing the appropriate stage music
  //top.mmrpg_music_load('misc/data-base');
  
  /*

  // Create the click event for canvas sort button
  $('.wrapper .sort', gameCanvas).live('click', function(e){
    var thisSortButton = $(this);
    var thisSortPlayer = thisSortButton.attr('data-player');
    var thisPlayerContainer = $('.wrapper[data-player='+thisSortPlayer+']', gameCanvas);
    var thisPlayerRobots = $('.sprite[data-token]', thisPlayerContainer);
    var thisPlayerRobotsTokens = [];
    thisPlayerRobots.each(function(){ thisPlayerRobotsTokens.push($(this).attr('data-player')); });
    thisPlayerRobotsTokens.join(',');
    //console.log('clicked sort; player tokens = '+thisPlayerRobotsTokens);
    // Define the post options for the ajax call
    var postData = {action:'sort',player:thisSortPlayer};    
    // Post the sort request to the server
    $.ajax({
      type: 'POST',
      url: 'frames/edit_players.php',
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
            var playerToken1 = $(a).attr('data-player');
            var playerToken2 = $(b).attr('data-player');    
            var playerToken1Position = myArrayOrder.indexOf(playerToken1);
            var playerToken2Position = myArrayOrder.indexOf(playerToken2);
            //console.log('playerToken1('+playerToken1+') = playerToken1Position('+playerToken1Position+');\n');
            //console.log('playerToken2('+playerToken2+') = playerToken2Position('+playerToken2Position+');\n ');
            // compare
            if (playerToken1Position > playerToken2Position) { return 1; } 
            else if (playerToken1Position < playerToken2Position) { return -1; } 
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
 
  */
  
  
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
    $('.sprite[data-token]', gameCanvas).removeClass('sprite_player_current').removeClass('sprite_player_dr-light_current sprite_player_dr-wily_current sprite_player_dr-cossack_current');
    dataSprite.addClass('sprite_player_current').addClass('sprite_player_current').addClass('sprite_player_'+dataPlayer+'_current');
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
  
  /*

  // Create the change event for the player selectors
  $('select.player_name', gameConsole).live('change', function(e){
    // Prevent the default action
    e.preventDefault(); 
    // Collect a reference to this select object
    var thisPlayerSelect = $(this);
    var thisPlayerLink = thisPlayerSelect.parent();
    var newPlayerOption = $('option:selected', thisPlayerSelect);
    // Collect the current player name and token
    var thisRobotToken = thisPlayerSelect.attr('data-player');
    var thisRobotName = $('a[data-player='+thisRobotToken+']', gameCanvas).attr('title');
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
    var postData = {action:'player',player:thisRobotToken,player1:currentPlayerToken,player2:newPlayerToken};
    // Trigger a transfer of this player to the requested player
    var confirmTransfer = true; //confirm('Transfer ownership of '+thisRobotName+' from '+currentPlayerLabel+' to '+newPlayerLabel+'?');
    if (confirmTransfer){
      $('#edit_overlay', thisPrototype).css({display:'block'});
      // Post the transfer request to the server
      $.ajax({
        type: 'POST',
        url: 'frames/edit_players.php',
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
            //console.log('success! now let\'s move the player...');
            //console.log(data);
            var newData = data.slice(2);
            newData = newData.join('|');
            $('#console #players').append(newData);
            // Collect the container and token references and prepare the move
            var canvasButton = $('.sprite[data-player='+thisRobotToken+']', gameCanvas);
            var consoleEvent = $('.event[data-token='+currentPlayerToken+'_'+thisRobotToken+']', gameConsole);
            var consolePlayerSelect = $('.player_select_block', consoleEvent);
            //if (!consolePlayerSelect.length){ //console.log('player select block not found'); }
            consolePlayerSelect.css({backgroundColor:'blue !important'});
            var newCanvasWrapper = $('.wrapper_'+newPlayerToken+'[data-select=players]', gameCanvas);
            var newConsoleToken = newPlayerToken+'_'+thisRobotToken;
            if (newPlayerToken == 'dr-light'){ var newPlayerName = 'Dr. Light'; }
            else if (newPlayerToken == 'dr-wily'){ var newPlayerName = 'Dr. Wily'; }
            else if (newPlayerToken == 'dr-cossack'){ var newPlayerName = 'Dr. Cossack'; }
            // Remove this player from the console
            consoleEvent.remove();            
            // Move this player's button to the new wrapper and update their data token
            //$('.current_player', consolePlayerSelect).removeClass('current_player_'+currentPlayerToken).addClass('current_player_'+newPlayerToken);
            //$('.player_name label', consolePlayerSelect).html(newPlayerName);
            //$('.player_name select', consolePlayerSelect).attr('data-player', newPlayerToken);   
            //$('select.field_name', consoleEvent).attr('data-player', newPlayerToken);
            canvasButton.removeClass('sprite_player_'+currentPlayerToken).removeClass('sprite_player_'+currentPlayerToken+'_current');
            canvasButton.addClass('sprite_player_'+newPlayerToken).addClass('sprite_player_'+newPlayerToken+'_current');    
            canvasButton.attr('data-player', newPlayerToken);
            canvasButton.attr('data-token', newConsoleToken);
            canvasButton.appendTo(newCanvasWrapper);
            //consoleEvent.attr('data-token', newConsoleToken); 
            // Reload the current page and return true  
            //window.location = window.location.href;
            // Trigger the wrapper resize function based on the new player amount
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
  
  */
  
  
  /*
   * FIELD EVENTS
   */
  
  // Prevent clicks if the parent container is disabled
  $('.tool[data-tool]', gameConsole).live('click', function(e){
    
    // Prevent the default action
    e.preventDefault();
    // Collect the global reference objects
    var thisLink = $(this);
    var thisContainer = thisLink.parent().parent();
    var thisContainerStatus = thisContainer.attr('data-status') != undefined ? thisContainer.attr('data-status') : 'enabled';
    var thisSelect = $('select.field_name', thisContainer).eq(0);
    var thisLabel = 'tools';
    var dataKey = 0;
    var dataPlayer = thisLink.attr('data-player');
    var optionFieldToken = thisLink.attr('data-tool');
    var optionSelected = $('option[value='+optionFieldToken+']', thisSelect);
    var postData = {action:'field',key:dataKey,player:dataPlayer};
    if (optionFieldToken.length){ postData.field = optionFieldToken; } 
    else { postData.field = ''; }

    // Ensure the parent container is enabled before sending any AJAX, else just update text
    if (thisContainerStatus == 'enabled'){
      
      // Change the body cursor to wait
      $('body').css('cursor', 'wait !important');
      // Temporarily disable the window while we update stuff
      thisContainer.css({opacity:0.25}).attr('data-status', 'disabled');
      // Loop through all this field links for this player and disable
      $('select.field_name', thisContainer).each(function(key, value){
        $(this).attr('disabled', 'disabled').prop('disabled', true);
        });
      
      // Post this change back to the server
      $.ajax({
        type: 'POST',
        url: 'frames/edit_players.php',
        data: postData,
        success: function(data, status){
          
          // DEBUG
          //console.log(data);
          
          // Break apart the response into parts
          var data = data.split('|');
          var dataStatus = data[0] != undefined ? data[0] : false;
          var dataMessage = data[1] != undefined ? data[1] : false;
          var dataContent = data[2] != undefined ? data[2] : false;
          var dataExtra = data[3] != undefined ? data[3] : false;
          
          // DEBUG
          //console.log('$(.tool[data-tool], gameConsole).live(click); \n dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; dataExtra = '+dataExtra+';');
          
          // If the field change was a success, flash the box green
          if (dataStatus == 'success'){
            
            /*
            
            // Make the clicked link flash green to show success
            thisLink.css({borderColor:'green !important'});
            var tempTimeout = setTimeout(function(){ thisLink.css({borderColor:''}); }, 1000);
            
            // Update the link attributes based on if the field was empty
            if (optionFieldToken.length){
              //console.log('Updating '+optionFieldToken+' on line 327 ---------------------');
              var optionFieldLabel = optionSelected.attr('data-label');
              var optionFieldTitle = optionSelected.attr('title');
              var optionFieldTooltip = optionSelected.attr('data-tooltip');
              var optionFieldImage = 'images/fields/'+optionFieldToken+'/battle-field_preview.png?'+gameSettings.cacheTime;
              var optionFieldType = optionSelected.attr('data-type');
              var optionFieldType2 = optionSelected.attr('data-type2');
              thisLink.attr('data-field', optionFieldToken).attr('title', optionFieldTitle).attr('data-tooltip', optionFieldTooltip);
              thisLink.css({backgroundImage:'url('+optionFieldImage+')'});
              //console.log('335: backgroundImage:url('+optionFieldImage+');');
              thisLabel.removeClass().addClass('field_type field_type_'+optionFieldType+(optionFieldType2.length ? '_'+optionFieldType2 : '')+'');
              thisSelect.attr('data-field', optionFieldToken);
              thisLabel.html(optionFieldLabel);                
              } else {
              //console.log('Updating empty on line 340 -------------------');
              thisLink.attr('data-field', '').attr('title', '-').attr('data-tooltip', '-');
              thisLabel.removeClass().addClass('field_type field_type_none');
              thisSelect.attr('data-field', '').val('');
              thisLabel.html('-');
              thisLabel.css({backgroundImage:'none !important'});
              }  
            
            */
            
            // Create the empty field count variable
            var emptyFieldCount = 0;
            // If the new field list was provided, break it apart
            if (dataContent.length){ var newFieldList = dataContent.split(','); }
            else { var newFieldList = []; }
            
            // Loop through all this field links for this player and update
            $('a.field_name', thisContainer).each(function(key, value){
              
              // Collect the approriate reference variables
              var tempLink = $(this);
              var tempSelect = $('select', tempLink);
              var tempOption = $('option:selected', tempSelect);
              var tempLabel = $('label', tempLink);
              var tempField = tempOption.val();
              var tempCurrentField = tempLink.attr('data-field');
              
              // If a new field list was provided, update this link
              if (newFieldList.length){
                
                // Collect the new field from this position in the list
                var newField = newFieldList[key] != undefined ? newFieldList[key]: '';
                
                // Update the select box with the new field and recollect it's value
                tempSelect.val(newField);
                tempOption = $('option:selected', tempSelect);
                tempField = tempOption.val();
                                                
                // Update the link attributes based on if the new field was empty
                if (newField.length){
                  //console.log('Updating ['+tempCurrentField+' -> '+tempField+' -> '+newField+'] on line 380 ----------------');
                  var newFieldLabel = tempOption.attr('data-label');
                  var newFieldTitle = tempOption.attr('title');
                  var newFieldTooltip = tempOption.attr('data-tooltip');
                  var newFieldImage = 'images/fields/'+newField+'/battle-field_preview.png?'+gameSettings.cacheTime;
                  var newFieldType = tempOption.attr('data-type');
                  var newFieldType2 = tempOption.attr('data-type2');
                  tempLink.attr('data-field', newField).attr('title', newFieldTitle).attr('data-tooltip', newFieldTooltip);
                  //console.log('before_background-image: '+tempLink.css('background-image'));
                  //tempLink.attr('style', '');
                  //console.log('389 : tempLink.css(\'background-image\', \'url('+newFieldImage+')\'); ');
                  tempLink.css('background-image', 'url('+newFieldImage+')');          
                  //tempLink.attr('style', 'url("'+newFieldImage+'") !important; ');
                  //console.log('after_background-image: '+tempLink.css('background-image'));
                  tempLink.removeClass().addClass('field_name field_type field_type_'+newFieldType+(newFieldType2.length ? '_'+newFieldType2 : ''));
                  tempLabel.removeClass().addClass('field_type field_type_'+newFieldType+(newFieldType2.length ? '_'+newFieldType2 : ''));
                  tempSelect.attr('data-field', newField);
                  tempSelect.find('option').prop('disabled', false);
                  tempSelect.find('option[value='+newField+']').prop('disabled', true);
                  tempLabel.html(newFieldLabel);                  
                  } else {
                  //console.log('Updating empty on line 399');
                  tempLink.attr('data-field', '').attr('title', '-').attr('data-tooltip', '-');
                  tempLabel.removeClass().addClass('field_type field_type_none');
                  tempSelect.attr('data-field', '').val('');
                  tempSelect.find('option').prop('disabled', false);
                  tempLabel.html('-');
                  tempLink.css({backgroundImage:'none !important'});
                  }                 
                
                // DEBUG
                //console.log('...but should be ['+newField+']!');
                                
                }            
              
              // Disable any overflow field containers
              if (!tempField.length){ emptyFieldCount++; }
              if (emptyFieldCount >= 2){ tempLink.css({opacity:0.25}); tempSelect.attr('disabled', 'disabled'); }
              else { tempLink.css({opacity:1.0}); tempSelect.removeAttr('disabled'); }
              
              }); 
              
              // Update the field and fusion star count for this player
              if (dataExtra != false){
                
                // Collect the star counts from the extra data
                var starCounts = dataExtra.split(',');
                // Collect a reference to the star count container
                var starCountsContainer = $('.field_stars', thisContainer);
                // Update the values in the two containers
                $('.star_field', starCountsContainer).html(starCounts[0]+' field');
                $('.star_fusion', starCountsContainer).html(starCounts[1]+' fusion');
                
                }   
  
            }
          
            // Change the body cursor back to default
            $('body').css('cursor', '');
            // Enable the conatiner again now that we're done
            thisContainer.css({opacity:1.0}).attr('data-status', 'enabled');      
            // Loop through all this field links for this player and disable
            $('select.field_name', thisContainer).each(function(key, value){
              $(this).removeAttr('disabled').prop('disabled', false);
              });          
                   
          }
        });      
      
      } else {
        
      // Update the link attributes based on if the field was empty
      if (optionFieldToken.length){
        //console.log('446: optionFieldToken.length ---------------');
        var optionFieldLabel = optionSelected.attr('data-label');
        var optionFieldImage = 'images/fields/'+optionFieldToken+'/battle-field_preview.png?'+gameSettings.cacheTime;
        var optionFieldTitle = optionSelected.attr('title');
        var optionFieldTooltip = optionSelected.attr('data-tooltip');
        var optionFieldType = optionSelected.attr('data-type');
        thisLink.attr('data-field', optionFieldToken).attr('title', optionFieldTitle).attr('data-tooltip', optionFieldTooltip);
        thisLabel.removeClass().addClass('field_type field_type_'+optionFieldType);
        thisSelect.attr('data-field', optionFieldToken);
        thisLabel.html(optionFieldLabel);
        thisLink.css({backgroundImage:'url('+optionFieldImage+')'});
        } else {
        //console.log('457: !optionFieldToken.length ---------------');
        thisLink.attr('data-field', '').attr('title', '-').attr('data-tooltip', '-');
        thisLabel.removeClass().addClass('field_type field_type_none');
        thisSelect.attr('data-field', '').val('');
        thisLabel.html('-');
        thisLink.css({backgroundImage:'none'});
        }
        
      }
    
    });
  
  // Prevent clicks if the parent container is disabled
  $('select.field_name', gameConsole).live('click', function(e){
    var thisSelect = $(this);
    var thisLink = thisSelect.parent();
    var thisContainer = thisLink.parent();
    var thisContainerStatus = thisContainer.attr('data-status') != undefined ? thisContainer.attr('data-status') : 'enabled';
    if (thisContainerStatus == 'disabled'){
      e.preventDefault();
      return false;
      }
    });
  
  // Create the change event for the field selectors
  $('select.field_name', gameConsole).live('change', function(e){
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
    var optionSelected = $('option:selected', thisSelect);
    var optionFieldToken = optionSelected.val();
    var postData = {action:'field',key:dataKey,player:dataPlayer};
    //if (dataKey == 0 && !optionFieldToken.length){ alert('first option cannot be empty!'); return false; }
    if (optionFieldToken.length){ postData.field = optionFieldToken; } 
    else { postData.field = ''; }

    // Ensure the parent container is enabled before sending any AJAX, else just update text
    if (thisContainerStatus == 'enabled'){
      
      // Change the body cursor to wait
      $('body').css('cursor', 'wait !important');
      // Temporarily disable the window while we update stuff
      thisContainer.css({opacity:0.25}).attr('data-status', 'disabled');
      // Loop through all this field links for this player and disable
      $('select.field_name', thisContainer).each(function(key, value){
        $(this).attr('disabled', 'disabled').prop('disabled', true);
        });
      
      // Post this change back to the server
      $.ajax({
        type: 'POST',
        url: 'frames/edit_players.php',
        data: postData,
        success: function(data, status){
          
          // DEBUG
          //console.log(data);
          
          // Break apart the response into parts
          var data = data.split('|');
          var dataStatus = data[0] != undefined ? data[0] : false;
          var dataMessage = data[1] != undefined ? data[1] : false;
          var dataContent = data[2] != undefined ? data[2] : false;
          var dataExtra = data[3] != undefined ? data[3] : false;
          
          // DEBUG
          //console.log('$(select.field_name, gameConsole).live(click); \n dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; dataExtra = '+dataExtra+';');
          
          // If the field change was a success, flash the box green
          if (dataStatus == 'success'){
            
            // Make the clicked link flash green to show success
            thisLink.css({borderColor:'green !important'});
            var tempTimeout = setTimeout(function(){ thisLink.css({borderColor:''}); }, 1000);                      
            
            // Update the link attributes based on if the field was empty
            if (optionFieldToken.length){
              //console.log('Updating '+optionFieldToken+' on line 327 ---------------------');
              var optionFieldLabel = optionSelected.attr('data-label');
              var optionFieldTitle = optionSelected.attr('title');
              var optionFieldTooltip = optionSelected.attr('data-tooltip');
              var optionFieldImage = 'images/fields/'+optionFieldToken+'/battle-field_preview.png?'+gameSettings.cacheTime;
              var optionFieldType = optionSelected.attr('data-type');
              var optionFieldType2 = optionSelected.attr('data-type2');
              thisLink.attr('data-field', optionFieldToken).attr('title', optionFieldTitle).attr('data-tooltip', optionFieldTooltip);
              thisLink.css({backgroundImage:'url('+optionFieldImage+')'});
              //console.log('335: backgroundImage:url('+optionFieldImage+');');
              thisLabel.removeClass().addClass('field_type field_type_'+optionFieldType+(optionFieldType2.length ? '_'+optionFieldType2 : '')+'');
              thisSelect.attr('data-field', optionFieldToken);
              thisLabel.html(optionFieldLabel);                
              } else {
              //console.log('Updating empty on line 340 -------------------');
              thisLink.attr('data-field', '').attr('title', '-').attr('data-tooltip', '-');
              thisLabel.removeClass().addClass('field_type field_type_none');
              thisSelect.attr('data-field', '').val('');
              thisLabel.html('-');
              thisLabel.css({backgroundImage:'none !important'});
              }  
            
            
            // Create the empty field count variable
            var emptyFieldCount = 0;
            // If the new field list was provided, break it apart
            if (dataContent.length){ var newFieldList = dataContent.split(','); }
            else { var newFieldList = []; }
            
            // Loop through all this field links for this player and update
            $('a.field_name', thisContainer).each(function(key, value){
              
              // Collect the approriate reference variables
              var tempLink = $(this);
              var tempSelect = $('select', tempLink);
              var tempOption = $('option:selected', tempSelect);
              var tempLabel = $('label', tempLink);
              var tempField = tempOption.val();
              var tempCurrentField = tempLink.attr('data-field');
              
              // If a new field list was provided, update this link
              if (newFieldList.length){
                
                // Collect the new field from this position in the list
                var newField = newFieldList[key] != undefined ? newFieldList[key]: '';
                
                // Update the select box with the new field and recollect it's value
                tempSelect.val(newField);
                tempOption = $('option:selected', tempSelect);
                tempField = tempOption.val();
                                                
                // Update the link attributes based on if the new field was empty
                if (newField.length){
                  //console.log('Updating ['+tempCurrentField+' -> '+tempField+' -> '+newField+'] on line 380 ----------------');
                  var newFieldLabel = tempOption.attr('data-label');
                  var newFieldTitle = tempOption.attr('title');
                  var newFieldTooltip = tempOption.attr('data-tooltip');
                  var newFieldImage = 'images/fields/'+newField+'/battle-field_preview.png?'+gameSettings.cacheTime;
                  var newFieldType = tempOption.attr('data-type');
                  var newFieldType2 = tempOption.attr('data-type2');
                  tempLink.attr('data-field', newField).attr('title', newFieldTitle).attr('data-tooltip', newFieldTooltip);
                  //console.log('before_background-image: '+tempLink.css('background-image'));
                  //tempLink.attr('style', '');
                  //console.log('389 : tempLink.css(\'background-image\', \'url('+newFieldImage+')\'); ');
                  tempLink.css('background-image', 'url('+newFieldImage+')');          
                  //tempLink.attr('style', 'url("'+newFieldImage+'") !important; ');
                  //console.log('after_background-image: '+tempLink.css('background-image'));
                  tempLink.removeClass().addClass('field_name field_type field_type_'+newFieldType+(newFieldType2.length ? '_'+newFieldType2 : ''));
                  tempLabel.removeClass().addClass('field_type field_type_'+newFieldType+(newFieldType2.length ? '_'+newFieldType2 : ''));
                  tempSelect.attr('data-field', newField);
                  tempSelect.find('option').prop('disabled', false);
                  tempSelect.find('option[value='+newField+']').prop('disabled', true);
                  tempLabel.html(newFieldLabel);                  
                  } else {
                  //console.log('Updating empty on line 399');
                  tempLink.attr('data-field', '').attr('title', '-').attr('data-tooltip', '-');
                  tempLabel.removeClass().addClass('field_type field_type_none');
                  tempSelect.attr('data-field', '').val('');
                  tempSelect.find('option').prop('disabled', false);
                  tempLabel.html('-');
                  tempLink.css({backgroundImage:'none !important'});
                  }                 
                
                // DEBUG
                //console.log('...but should be ['+newField+']!');
                                
                }            
              
              // Disable any overflow field containers
              if (!tempField.length){ emptyFieldCount++; }
              if (emptyFieldCount >= 2){ tempLink.css({opacity:0.25}); tempSelect.attr('disabled', 'disabled'); }
              else { tempLink.css({opacity:1.0}); tempSelect.removeAttr('disabled'); }
              
              }); 
              
              // Update the field and fusion star count for this player
              if (dataExtra != false){
                
                // Collect the star counts from the extra data
                var starCounts = dataExtra.split(',');
                // Collect a reference to the star count container
                var starCountsContainer = $('.field_stars', thisContainer);
                // Update the values in the two containers
                $('.star_field', starCountsContainer).html(starCounts[0]+' field');
                $('.star_fusion', starCountsContainer).html(starCounts[1]+' fusion');
                
                }   
  
            }
          
            // Change the body cursor back to default
            $('body').css('cursor', '');
            // Enable the conatiner again now that we're done
            thisContainer.css({opacity:1.0}).attr('data-status', 'enabled');      
            // Loop through all this field links for this player and disable
            $('select.field_name', thisContainer).each(function(key, value){
              $(this).removeAttr('disabled').prop('disabled', false);
              });          
                   
          }
        });      
      
      } else {
        
      // Update the link attributes based on if the field was empty
      if (optionFieldToken.length){
        //console.log('446: optionFieldToken.length ---------------');
        var optionFieldLabel = optionSelected.attr('data-label');
        var optionFieldImage = 'images/fields/'+optionFieldToken+'/battle-field_preview.png?'+gameSettings.cacheTime;
        var optionFieldTitle = optionSelected.attr('title');
        var optionFieldTooltip = optionSelected.attr('data-tooltip');
        var optionFieldType = optionSelected.attr('data-type');
        thisLink.attr('data-field', optionFieldToken).attr('title', optionFieldTitle).attr('data-tooltip', optionFieldTooltip);
        thisLabel.removeClass().addClass('field_type field_type_'+optionFieldType);
        thisSelect.attr('data-field', optionFieldToken);
        thisLabel.html(optionFieldLabel);
        thisLink.css({backgroundImage:'url('+optionFieldImage+')'});
        } else {
        //console.log('457: !optionFieldToken.length ---------------');
        thisLink.attr('data-field', '').attr('title', '-').attr('data-tooltip', '-');
        thisLabel.removeClass().addClass('field_type field_type_none');
        thisSelect.attr('data-field', '').val('');
        thisLabel.html('-');
        thisLink.css({backgroundImage:'none'});
        }
        
      }
    
    });//.trigger('change');
  
 
  
  /*
   * ITEM EVENTS
   */
  
  /*
  
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
    var optionSelected = $('option:selected', thisSelect);
    var optionItemToken = optionSelected.val();
    var postData = {action:'item',key:dataKey,player:dataPlayer};
    //if (dataKey == 0 && !optionItemToken.length){ alert('first option cannot be empty!'); return false; }
    if (optionItemToken.length){ postData.item = optionItemToken; } 
    else { postData.item = ''; }

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
        url: 'frames/edit_players.php',
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
          //console.log('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');
          
          // If the ability change was a success, flash the box green
          if (dataStatus == 'success'){
            
            // Make the clicked link flash green to show success
            thisLink.css({borderColor:'green !important'});
            var tempTimeout = setTimeout(function(){ thisLink.css({borderColor:''}); }, 1000);          
            
            // Update the link attributes based on if the ability was empty
            if (optionItemToken.length){
              var optionItemLabel = optionSelected.attr('data-label');
              var optionItemTitle = optionSelected.attr('title');
              var optionItemTooltip = optionSelected.attr('data-tooltip');
              var optionItemImage = 'images/abilities/'+optionItemToken+'/icon_left_40x40.png?';
              var optionItemType = optionSelected.attr('data-type');
              var optionItemType2 = optionSelected.attr('data-type2');
              thisLink.attr('data-item', optionItemToken).attr('title', optionItemTitle).attr('data-tooltip', optionItemTooltip);
              thisLink.removeClass().addClass('ability_name ability_type ability_type_'+optionItemType+(optionItemType2.length ? '_'+optionItemType2 : '')+'');
              thisSelect.attr('data-item', optionItemToken);
              thisLabel.html(optionItemLabel).css({backgroundImage:'url('+optionItemImage+')'});
              } else {
              thisLink.attr('data-item', '').attr('title', '-').attr('data-tooltip', '-');
              thisLink.removeClass().addClass('ability_name ability_type ability_type_none');
              thisSelect.attr('data-item', '').val('');
              thisLabel.html('-').css({backgroundImage:'none'});
              }         
            
            // Create the empty ability count variable
            var emptyAbilityCount = 0;
            // If the new ability list was provided, break it apart
            if (dataContent.length){ var newItemList = dataContent.split(','); }
            else { var newItemList = []; }
            
            // Loop through all this ability links for this robot and update
            $('a.ability_name', thisContainer).each(function(key, value){
              
              // Collect the approriate reference variables
              var tempLink = $(this);
              var tempSelect = $('select', tempLink);
              var tempOption = $('option:selected', tempSelect);
              var tempLabel = $('label', tempLink);
              var tempAbility = tempOption.val();
              
              // If a new ability list was provided, update this link
              if (newItemList.length){
                
                // Collect the new ability from this position in the list
                var newItem = newItemList[key] != undefined ? newItemList[key]: '';
                
                // DEBUG
                //console.log('current item at position '+key+' is ['+tempAbility+']...');
                
                // Update the select box with the new ability and recollect it's value
                tempSelect.val(newItem);
                tempOption = $('option:selected', tempSelect);
                tempAbility = tempOption.val();
                
                // Update the link attributes based on if the new ability was empty
                if (newItem.length){
                  var newItemLabel = tempOption.attr('data-label');
                  var newItemTitle = tempOption.attr('title');
                  var newItemTooltip = tempOption.attr('data-tooltip');
                  var newItemImage = 'images/abilities/'+newItem+'/icon_left_40x40.png?';
                  var newItemType = tempOption.attr('data-type');
                  var newItemType2 = tempOption.attr('data-type2');
                  tempLink.attr('data-item', newItem).attr('title', newItemTitle).attr('data-tooltip', newItemTooltip);
                  tempLink.removeClass().addClass('ability_name ability_type ability_type_'+newItemType+(newItemType2.length ? '_'+newItemType2 : ''));
                  tempSelect.attr('data-item', newItem);
                  tempSelect.find('option').prop('disabled', false);
                  tempSelect.find('option[value='+newItem+']').prop('disabled', true);
                  tempLabel.html(newItemLabel).css({backgroundImage:'url('+newItemImage+')'});
                  } else {
                  tempLink.attr('data-item', '').attr('title', '-').attr('data-tooltip', '-');
                  tempLink.removeClass().addClass('ability_name ability_type ability_type_none');
                  tempSelect.attr('data-item', '').val('');
                  tempSelect.find('option').prop('disabled', false);
                  tempLabel.html('-').css({backgroundImage:'none'});
                  }                 
                
                // DEBUG
                //console.log('...but should be ['+newItem+']!');
                                
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
      if (optionItemToken.length){
        var optionItemLabel = optionSelected.attr('data-label');
        var optionItemImage = 'images/abilities/'+optionItemToken+'/icon_left_40x40.png?';
        var optionItemTitle = optionSelected.attr('title');
        var optionItemTooltip = optionSelected.attr('data-tooltip');
        var optionItemType = optionSelected.attr('data-type');
        thisLink.attr('data-item', optionItemToken).attr('title', optionItemTitle).attr('data-tooltip', optionItemTooltip);
        thisLink.removeClass().addClass('ability_name ability_type ability_type_'+optionItemType);
        thisSelect.attr('data-item', optionItemToken);
        thisLabel.html(optionItemLabel).css({backgroundImage:'url('+optionItemImage+')'});
        } else {
        thisLink.attr('data-item', '').attr('title', '-').attr('data-tooltip', '-');
        thisLink.removeClass().addClass('ability_name ability_type ability_type_none');
        thisSelect.attr('data-item', '').val('');
        thisLabel.html('-').css({backgroundImage:'none'});
        }
        
      }       
    
    });//.trigger('change');  
  
   */
  
  
  
  
  
  /*
   * OTHER STUFF
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