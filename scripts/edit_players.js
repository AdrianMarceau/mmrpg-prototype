// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisEditor = false;
var thisEditorData = {playerTotal:0,playerTotal:0};
var resizePlayerWrapper = function(){};
var showPlayerInReadyRoom = function(){};
$(document).ready(function(){

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);
    thisEditor = $('#edit', thisBody);

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the items menu
    var thisContext = $('#edit');
    var playSoundEffect = function(){}
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

        // PLAYER SIDEBAR LINKS

        // Add hover and click sounds to the buttons in the player sidebar menu
        $('#canvas .sprite_player', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'link-hover', {volume: 0.5});
            });
        $('#canvas .sprite_player', thisContext).live('click', function(){
            playSoundEffect.call(this, 'link-click', {volume: 1.0});
            });

        // PLAYER PANEL BUTTONS

        // Add hover and click sounds to the player alt image sprite in the editor panel
        $('#console .event .event_player_images a.player_image_alts', thisContext).live('mouseenter', function(){
            if ($(this).is('[data-alt-index="base"]')){ return; }
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#console .event .event_player_images a.player_image_alts', thisContext).live('click', function(){
            if ($(this).is('[data-alt-index="base"]')){ return; }
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            setTimeout(function(){ playSoundEffect.call(this, 'link-click-action', {volume: 1.0}); }, 800);
            });

        // CHALLENGE BOARD LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#console .event a.challenge_name.challenge_battle', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#console .event a.challenge_name.challenge_battle', thisContext).live('click', function(){
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });
        $('#console .event a.challenge_name.challenge_battle select', thisContext).live('change', function(){
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });

        // MISSION CUSTOMIZER LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#console .event a.field_name', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('#console .event a.field_name', thisContext).live('click', function(){
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });
        $('#console .event a.field_name select', thisContext).live('change', function(){
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });

        }

    // -- PRIMARY SCRIPT FUNCTIONALITY -- //

    // Define a function for showing/highlighting a player in the above ready room when possible
    showPlayerInReadyRoom = function(playerToken){
        //console.log('showPlayerInReadyRoom(playerToken:', playerToken, ')');
        // We should add the new player to the parent ready room
        if (typeof window.parent.mmrpgReadyRoom !== 'undefined'
            && typeof window.parent.mmrpgReadyRoom.updatePlayer !== 'undefined'){
            // If the extra data in dataExtra was not empty and is JSON, parse it into playerInfo
            var readyRoom = window.parent.mmrpgReadyRoom;
            var spriteBounces = readyRoom.config.spriteBounds;
            readyRoom.updatePlayer('all', {frame: 'base', position: [null, (spriteBounces.maxY - 2)]});
            readyRoom.updateRobot('all', {frame: 'base', position: [null, '>=20']});
            readyRoom.updatePlayer(playerToken, {frame: 'victory', direction: 'right', position: [44, (spriteBounces.minY - 2)]});
            }
        };

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
                showPlayerInReadyRoom(dataPlayer);
                });
            } else {
                $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
                showPlayerInReadyRoom(dataPlayer);
            }
        });


    // PROCESS FIELD CHANGE ACTIONS

    // Define events for the toolbar actions (shuffle, randomize, etc.)
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

                                // DEBUG DEBUG DEBUG
                                //if (tempCurrentField == newField){ return true; }

                                // DEBUG
                                //console.log('current field at position '+key+' is ['+tempField+']...');

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

                            if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){
                                playSoundEffect('link-click-action', {volume: 1.0});
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

    // Prevent clicks if the parent field or mission container is disabled
    $('select.field_name, select.challenge_name', gameConsole).live('click', function(e){
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
    $('.field_container select.field_name', gameConsole).live('change', function(e){
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

                                // DEBUG DEBUG DEBUG
                                //if (tempCurrentField == newField){ return true; }

                                // DEBUG
                                //console.log('current field at position '+key+' is ['+tempField+']...');

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

                            if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){
                                playSoundEffect('link-click-action', {volume: 1.0});
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


    // PROCESS CHALLENGE CHANGE ACTIONS

    // Create the change event for the mission selectors
    $('.challenge_container select.challenge_name', gameConsole).live('change', function(e){
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
        var optionChallengeID = optionSelected.val();
        var postData = {action:'challenge',key:dataKey,player:dataPlayer};
        //if (dataKey == 0 && !optionChallengeID.length){ alert('first option cannot be empty!'); return false; }
        if (optionChallengeID.length){ postData.challenge = optionChallengeID; }
        else { postData.challenge = ''; }

        // Ensure the parent container is enabled before sending any AJAX, else just update text
        if (thisContainerStatus == 'enabled'){

            // Change the body cursor to wait
            $('body').css('cursor', 'wait !important');
            // Temporarily disable the window while we update stuff
            thisContainer.css({opacity:0.25}).attr('data-status', 'disabled');
            // Loop through all this field links for this player and disable
            $('select.challenge_name', thisContainer).each(function(key, value){
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
                    //console.log('$(select.challenge_name, gameConsole).live(click); \n dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; dataExtra = '+dataExtra+';');

                    // If the field change was a success, flash the box green
                    if (dataStatus == 'success'){

                        // Make the clicked link flash green to show success
                        thisLink.css({borderColor:'green !important'});
                        var tempTimeout = setTimeout(function(){ thisLink.css({borderColor:''}); }, 1000);

                        // Update the link attributes based on if the field was empty
                        if (optionChallengeID.length){
                            //console.log('Updating '+optionChallengeID+' on line 771 ---------------------');
                            var optionChallengeLabel = optionSelected.attr('data-label');
                            var optionChallengeTitle = optionSelected.attr('title');
                            var optionChallengeTooltip = optionSelected.attr('data-tooltip');
                            var optionChallengeTooltipType = optionSelected.attr('data-tooltip-type');
                            var optionChallengeBackground = optionSelected.attr('data-background');
                            var optionChallengeForeground = optionSelected.attr('data-foreground');
                            var optionChallengeImage = 'images/fields/'+optionChallengeBackground+'/battle-field_preview.png?'+gameSettings.cacheTime;
                            var optionChallengeType = optionSelected.attr('data-type');
                            var optionChallengeType2 = optionSelected.attr('data-type2');
                            thisLink.attr('data-challenge', optionChallengeID).attr('title', optionChallengeTitle).attr('data-tooltip', optionChallengeTooltip).attr('data-tooltip-type', optionChallengeTooltipType);
                            thisLink.css({backgroundImage:'url('+optionChallengeImage+')'});
                            //console.log('783: backgroundImage:url('+optionChallengeImage+');');
                            thisLabel.removeClass().addClass('field_type field_type_'+optionChallengeType+(optionChallengeType2.length ? '_'+optionChallengeType2 : '')+'');
                            thisSelect.attr('data-challenge', optionChallengeID);
                            thisLabel.html(optionChallengeLabel);
                            } else {
                            //console.log('Updating empty on line 788 -------------------');
                            thisLink.attr('data-challenge', '').attr('title', '-').attr('data-tooltip', '-').attr('data-tooltip-type', '');
                            thisLabel.removeClass().addClass('field_type field_type_none');
                            thisSelect.attr('data-challenge', '').val('');
                            thisLabel.html('-');
                            thisLabel.css({backgroundImage:'none !important'});
                            }


                        // Create the empty field count variable
                        var emptyFieldCount = 0;
                        // If the new field list was provided, break it apart
                        if (dataContent.length){ var newChallengeList = dataContent.split(','); }
                        else { var newChallengeList = []; }
                        //console.log('802: newChallengeList = ', newChallengeList);

                        // Loop through all this field links for this player and update
                        $('a.challenge_name', thisContainer).each(function(key, value){

                            // Collect the approriate reference variables
                            var tempLink = $(this);
                            var tempSelect = $('select', tempLink);
                            var tempOption = $('option:selected', tempSelect);
                            var tempLabel = $('label', tempLink);
                            var tempChallenge = tempOption.val();
                            var tempCurrentField = tempLink.attr('data-challenge');

                            // If a new field list was provided, update this link
                            if (newChallengeList.length){

                                // Collect the new field from this position in the list
                                var newChallenge = newChallengeList[key] != undefined ? newChallengeList[key]: '';

                                // DEBUG DEBUG DEBUG
                                if (tempCurrentField == newChallenge){ return true; }

                                // DEBUG
                                //console.log('current field at position '+key+' is ['+tempChallenge+']...');

                                // Update the select box with the new field and recollect it's value
                                tempSelect.val(newChallenge);
                                tempOption = $('option:selected', tempSelect);
                                tempChallenge = tempOption.val();

                                // Update the link attributes based on if the new field was empty
                                if (newChallenge.length){
                                    //console.log('Updating ['+tempCurrentField+' -> '+tempChallenge+' -> '+newChallenge+'] on line 380 ----------------');
                                    var newChallengeLabel = tempOption.attr('data-label');
                                    var newChallengeTitle = tempOption.attr('title');
                                    var newChallengeTooltip = tempOption.attr('data-tooltip');
                                    var newChallengeTooltipType = tempOption.attr('data-tooltip-type');
                                    var newChallengeBackground = tempOption.attr('data-background');
                                    var newChallengeForeground = tempOption.attr('data-foreground');
                                    var newChallengeImage = 'images/fields/'+newChallengeBackground+'/battle-field_preview.png?'+gameSettings.cacheTime;
                                    var newChallengeType = tempOption.attr('data-type');
                                    var newChallengeType2 = tempOption.attr('data-type2');
                                    tempLink.attr('data-challenge', newChallenge).attr('title', newChallengeTitle).attr('data-tooltip', newChallengeTooltip).attr('data-tooltip-type', newChallengeTooltipType);
                                    //console.log('before_background-image: '+tempLink.css('background-image'));
                                    //tempLink.attr('style', '');
                                    //console.log('389 : tempLink.css(\'background-image\', \'url('+newChallengeImage+')\'); ');
                                    tempLink.css('background-image', 'url('+newChallengeImage+')');
                                    //console.log('after_background-image: '+tempLink.css('background-image'));
                                    tempLink.removeClass().addClass('challenge_name challenge_battle field_type field_type_'+newChallengeType);
                                    tempLabel.removeClass().addClass('field_type field_type_'+newChallengeType+(newChallengeType2.length ? '_'+newChallengeType2 : ''));
                                    tempSelect.attr('data-challenge', newChallenge);
                                    tempSelect.find('option').prop('disabled', false);
                                    tempSelect.find('option[value='+newChallenge+']').prop('disabled', true);
                                    tempLabel.html(newChallengeLabel);
                                    } else {
                                    //console.log('Updating empty on line 399');
                                    tempLink.attr('data-challenge', '').attr('title', '-').attr('data-tooltip', '-').attr('data-tooltip-type', '');
                                    tempLabel.removeClass().addClass('field_type field_type_none');
                                    tempSelect.attr('data-challenge', '').val('');
                                    tempSelect.find('option').prop('disabled', false);
                                    tempLabel.html('-');
                                    tempLink.css({backgroundImage:'none !important'});
                                    }

                                // DEBUG
                                //console.log('...but should be ['+newChallenge+']!');

                                }

                            // Disable any overflow field containers
                            if (!tempChallenge.length){ emptyFieldCount++; }
                            if (emptyFieldCount >= 2){ tempLink.css({opacity:0.25}); tempSelect.attr('disabled', 'disabled'); }
                            else { tempLink.css({opacity:1.0}); tempSelect.removeAttr('disabled'); }

                            });

                            if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){
                                playSoundEffect('link-click-action', {volume: 1.0});
                                }

                        }

                        // Change the body cursor back to default
                        $('body').css('cursor', '');
                        // Enable the conatiner again now that we're done
                        thisContainer.css({opacity:1.0}).attr('data-status', 'enabled');
                        // Loop through all this field links for this player and disable
                        $('select.challenge_name', thisContainer).each(function(key, value){
                            $(this).removeAttr('disabled').prop('disabled', false);
                            });

                    }
                });

            } else {

            // Update the link attributes based on if the field was empty
            if (optionChallengeID.length){
                //console.log('446: optionChallengeID.length ---------------');
                var optionChallengeLabel = optionSelected.attr('data-label');
                var optionChallengeBackground = optionSelected.attr('data-background');
                var optionChallengeForeground = optionSelected.attr('data-foreground');
                var optionChallengeImage = 'images/fields/'+optionChallengeBackground+'/battle-field_preview.png?'+gameSettings.cacheTime;
                var optionChallengeTitle = optionSelected.attr('title');
                var optionChallengeTooltip = optionSelected.attr('data-tooltip');
                var optionChallengeTooltipType = optionSelected.attr('data-tooltip-type');
                var optionChallengeType = optionSelected.attr('data-type');
                thisLink.attr('data-challenge', optionChallengeID).attr('title', optionChallengeTitle).attr('data-tooltip', optionChallengeTooltip).attr('data-tooltip-type', optionChallengeTooltipType);
                thisLabel.removeClass().addClass('field_type field_type_'+optionChallengeType);
                thisSelect.attr('data-challenge', optionChallengeID);
                thisLabel.html(optionChallengeLabel);
                thisLink.css({backgroundImage:'url('+optionChallengeImage+')'});
                } else {
                //console.log('457: !optionChallengeID.length ---------------');
                thisLink.attr('data-challenge', '').attr('title', '-').attr('data-tooltip', '-').attr('data-tooltip-type', '');
                thisLabel.removeClass().addClass('field_type field_type_none');
                thisSelect.attr('data-challenge', '').val('');
                thisLabel.html('-');
                thisLink.css({backgroundImage:'none'});
                }

            }

        });


    // PROCESS ALT CHANGE ACTION

    // Collect the base href from the header
    var thisBaseHref = $('head base').attr('href');

    // Define a function for hovering over the image alt link
    $('.player_image_alts', gameConsole).live({
        mouseenter: function (){
            //console.log('mousein player image alt');
            var altSprite = $(this).find('.sprite_player');
            if (altSprite.is(':animated')){ return false; }
            var altFrame = $(this).is('a') ? 'taunt' : 'defend';
            updateSpriteFrame(altSprite, altFrame);
            return true;
            },
        mouseleave: function (){
            //console.log('mousein player image alt');
            var altSprite = $(this).find('.sprite_player');
            if (altSprite.is(':animated')){ return false; }
            updateSpriteFrame(altSprite, 'base');
            return true;
            }
        });

    // Attach a click event to the sprite image switcher
    $('a.player_image_alts', gameConsole).live('click', function(e){
        e.preventDefault();
        if (thisBody.hasClass('loading')){ return false; }
        if (!gameSettings.allowEditing){ return false; }

        // Collect references to the editor objects and player/player tokens
        var thisLink = $(this);
        var thisSprite = thisLink.find('.sprite_player');
        var thisPlayerToken = thisLink.attr('data-player');
        var thisPlayerToken = thisLink.attr('data-player');

        // If we're already animating, return false
        if (thisSprite.is(':animated')){ return false; }

        // Collect the alternate skin/image index for this player, break it down, and find our positions
        var playerImageIndex = thisLink.attr('data-alt-index') != undefined ? thisLink.attr('data-alt-index') : 'base';
        playerImageIndex = playerImageIndex.match(',') ? playerImageIndex.split(',') : [playerImageIndex];
        var thisCurrentImageToken = thisLink.attr('data-alt-current') != undefined ? thisLink.attr('data-alt-current') : 'base';
        var thisCurrentImageIndex = playerImageIndex.indexOf(thisCurrentImageToken);

        // Generate the index key and file path for the skin/image we'll be switching to
        var newImageIndex = thisCurrentImageIndex + 1;
        if (newImageIndex >= playerImageIndex.length){ newImageIndex = 0; }
        var newImageToken = playerImageIndex[newImageIndex];

        return updatePlayerImageAlt(thisPlayerToken, thisPlayerToken, newImageToken);

        });


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


//Define a function for changing a player's image (to an alt, for example)
var updatePlayerImageAltTimeout = false;
function updatePlayerImageAlt(thisPlayerToken, thisPlayerToken, newImageToken){
    //console.log('updatePlayerImageAlt('+thisPlayerToken+', '+thisPlayerToken+', '+newImageToken+');');

    // Collect references to the editor objects and player/player tokens
    var thisLink = $('.player_image_alts[data-player='+thisPlayerToken+'][data-player='+thisPlayerToken+']', gameConsole);
    var thisSprite = thisLink.find('.sprite_player');

    // If we're already animating, return false
    if (thisSprite.is(':animated')){ return false; }

    // Collect all relevant sprites based on the above info from both the console and canvas areas
    var thisConsoleSprites = $('.event_visible[data-token='+thisPlayerToken+'] .sprite_player', gameConsole);
    var thisCanvasSprites = $('#links .sprite_player[data-token='+thisPlayerToken+']', gameCanvas);
    //console.log('thisCanvasSprites = ', thisCanvasSprites.length, thisCanvasSprites);

    // DEBUG
    //console.log('player image alt switch!  :D');

    // Collect the size of the clicked player sprite and use it to generate classes
    var playerSize = thisSprite.hasClass('.sprite_80x80') ? 80 : 40;
    var playerSizeText = playerSize+'x'+playerSize;
    var playerSizeClass = '.sprite_'+playerSizeText;

    // Collect the alternate skin/image index for this player, break it down, and find our positions
    var playerImageIndex = thisLink.attr('data-alt-index') != undefined ? thisLink.attr('data-alt-index') : 'base';
    playerImageIndex = playerImageIndex.match(',') ? playerImageIndex.split(',') : [playerImageIndex];
    var thisCurrentImageToken = thisLink.attr('data-alt-current') != undefined ? thisLink.attr('data-alt-current') : 'base';
    var thisCurrentImageIndex = playerImageIndex.indexOf(thisCurrentImageToken);
    var thisCurrentFilePath = '/'+thisPlayerToken+(thisCurrentImageToken != 'base' ? '_'+thisCurrentImageToken : '')+'/';

    // Generate the index key and file path for the skin/image we'll be switching to
    var newImageIndex = playerImageIndex.indexOf(newImageToken);
    var newFilePath = '/'+thisPlayerToken+(newImageToken != 'base' ? '_'+newImageToken : '')+'/';

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

    // Update this player to its victory frame so it's ready for switching
    updateSpriteFrame(thisSprite, 'victory');

    // We should also update the image in the ready room so it looks nice for the player
    if (typeof window.parent.mmrpgReadyRoom !== 'undefined'
        && typeof window.parent.mmrpgReadyRoom.updatePlayer !== 'undefined'){
        // If the extra data in dataExtra was not empty and is JSON, parse it into playerInfo
        if (updatePlayerImageAltTimeout !== false){ clearTimeout(updatePlayerImageAltTimeout); }
        var readyRoom = window.parent.mmrpgReadyRoom;
        var newPlayerToken = thisPlayerToken;
        var newPlayerInfo = {frame: 'base2'};
        readyRoom.updatePlayer(newPlayerToken, newPlayerInfo, 10);
        updatePlayerImageAltTimeout = setTimeout(function(){
            clearTimeout(updatePlayerImageAltTimeout);
            newPlayerInfo = {frame: 'victory', image: thisPlayerToken+(newImageToken !== 'base' ? '_'+newImageToken : '')};
            readyRoom.updatePlayer(newPlayerToken, newPlayerInfo, 10);
            updatePlayerImageAltTimeout = setTimeout(function(){
                clearTimeout(updatePlayerImageAltTimeout);
                newPlayerInfo = {frame: 'base'};
                readyRoom.updatePlayer(newPlayerToken, newPlayerInfo, 1);
                }, 900);
            }, 900);
        }


    // DEBUG
    //console.log( {playerSize:playerSize,playerSizeText:playerSizeText,playerSizeClass:playerSizeClass});
    //console.log( {playerImageIndex:playerImageIndex,thisCurrentImageToken:thisCurrentImageToken,thisCurrentImageIndex:thisCurrentImageIndex,thisCurrentFilePath:thisCurrentFilePath});
    //console.log( {newImageIndex:newImageIndex,newImageToken:newImageToken,newFilePath:newFilePath});
    //console.log( {thisCurrentBackgroundImage:thisCurrentBackgroundImage,newBackgroundImage:newBackgroundImage});

    // Define a function for when all the background sprites have been updated
    var afterBackgroundUpdateComplete = function(nextGroup){
        //console.log('backgrounds have finished switching');
        if (typeof nextGroup !== 'undefined'){

            // We still have to update the mugshot images and tokens
            //console.log('nextGroup provided,', nextGroup, ', updating tokens and then mugshot background images');
            if (newImageIndex != -1){
                $('.token', thisLink).removeClass('token_active');
                $('.token', thisLink).eq(newImageIndex).addClass('token_active');
                }
            nextGroup.each(function(){ updateBackgroundImageFunction($(this)); });

            } else {
            //console.log('nextGroup undefined, updating server with new choice');

            // Post this change back to the server
            var postData = {action:'altimage',player:thisPlayerToken,player:thisPlayerToken,image:newImageToken};
            $.ajax({
                type: 'POST',
                url: 'frames/edit_players.php',
                data: postData,
                success: function(data, status){

                    // If the `data` is multi-line, immediately break off anything after the first for later into a `dataExtra` var
                    //console.log('data =', data);
                    var newlineIndex = data.indexOf("\n");
                    var dataExtra = newlineIndex !== -1 ? data.substr(newlineIndex + 1) : false;
                    data = newlineIndex !== -1 ? data.substr(0, newlineIndex) : data;
                    //console.log('data (after) =', data);
                    //console.log('dataExtra =', dataExtra);

                    // DEBUG
                    //alert(data);
                    // Break apart the response into parts
                    var data = data.split('|');
                    var dataStatus = data[0] != undefined ? data[0] : false;
                    var dataMessage = data[1] != undefined ? data[1] : false;
                    var dataContent = data[2] != undefined ? data[2] : false;
                    // DEBUG
                    //console.log('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+',\n dataContent = '+dataContent+'; ');
                    //console.log( data);
                    //console.log('dataStatus:'+dataStatus);
                    //console.log('dataMessage:'+dataMessage);
                    //console.log('dataContent:'+dataContent);

                    // If the alt change was a success, flash the box green
                    if (dataStatus == 'success'){
                        //console.log('success! this player alt image has been updated');
                        //console.log(data);
                        return true;
                        }


                    // Hide the overlay to allow using the player again
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
        //console.log( {thisCurrentBackgroundImage:thisCurrentBackgroundImage,newBackgroundImage:newBackgroundImage});

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

}

//Define a function for swapping the frame of a sprite
function updateSpriteFrame(thisSprite, newFrame){
    //console.log('updateSpriteFrame(thisSprite, '+newFrame+')');
    thisSprite.attr('class', function(index,classes){
     //console.log('thisSprite.attr(class, function('+index+','+classes+')');
     var newClasses = classes.replace(/(^|\s)(sprite_[0-9]+x[0-9]+_)([a-z0-9]+)(\s|$)/i, '$1$2'+newFrame+'$4');
     //console.log('classes.replace($1$2'+newFrame+'$4) | newClasses =  '+newClasses);
     return newClasses;
     });
}

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