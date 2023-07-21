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
     * FIELD EVENTS
     */

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