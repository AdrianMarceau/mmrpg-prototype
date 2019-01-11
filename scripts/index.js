// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisHeaderBanner = false;
var thisHeaderMenu = false;
var websiteSettings = {};
var thisScrollbarSettings = {wheelSpeed:0.3};
$(document).ready(function(){

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisIndex = $('#window', thisBody);
    thisWindow = $(window);
    thisHeaderBanner = $('.banner', thisIndex);
    thisHeaderMenu = $('.menu', thisIndex);

    // Create the window resize events to ensure scrolling works
    /*
    thisWindow.resize(function(){ windowResizePage(); });
    setTimeout(function(){ windowResizePage(); }, 1000);
    windowResizePage();
    */

    /*
     * ANCHOR LINK EVENTS
     */

    // Capture the "topscroll" mobile button and ensure it functions correctly
    $('#topscroll').live('click', function(e){
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 'fast');
        });

    // Capture any "top" links and ensure they function correctly
    $('a[href^="#"],a[data-href^="#"]', thisIndex).live('click', function(e){
        var thisLink = $(this);
        if (thisLink.is('[data-href^="#"]')){ var thisHref = thisLink.attr('data-href'); }
        else { var thisHref = thisLink.attr('href'); }
        if (thisHref == '#'){ return false; }
        e.preventDefault();
        var thisElement = $(thisHref);
        if (thisHref == '#top' || !thisElement.length){ var scrollTop = 0; }
        else { var scrollTop = thisElement.offset().top; }
        //console.log('thisHref = '+thisHref+' | thisElement = '+thisElement.length+' | scrollTop = '+scrollTop+' | thisElementClass = '+thisElement.attr('class'));
        $('html, body').animate({scrollTop: scrollTop}, 'fast');
        });

    // Capture any "top" links and ensure they function correctly
    $('*[data-anchor]', thisIndex).live('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        var thisAnchorToken = $(this).attr('data-anchor').replace(/^#/, '');
        //console.log('a[data-anchor=#'+thisAnchorToken+']');
        var thisAnchorOffset = $('#'+thisAnchorToken).offset();
        $('html, body').animate({scrollTop: (thisAnchorOffset.top - 5)+'px'}, 'fast');
        return false;
        });


    /* -- SCROLL EVENTS -- */

    // Define a function for checking scroll state when the user scrolls
    var checkScrollState = function(e){
        var scrollTop = $(window).scrollTop();
        if (scrollTop > 0){ $('body').addClass('scrolled'); }
        else { $('body').removeClass('scrolled'); }
        };

    // Attach the scroll state function to the window scroll event
    $(window).scroll(function(){
        return checkScrollState();
        });


    /*
     * MAIN MENU EVENTS
     */

    // Capture and clicks to the main menu expand toggle
    $('.userinfo .expand', thisHeaderBanner).click(function(e){
        //console.log('expand clicked!');
        e.preventDefault();
        var menuButton = $(this);
        var menuContainer = $('.main', thisHeaderMenu);
        if (!menuContainer.hasClass('expanded')){

            menuContainer.addClass('expanded');
            //menuButton.find('span').html('×');
            menuButton.addClass('expanded');

            } else {

                menuContainer.removeClass('expanded');
                //menuButton.find('span').html('+');
                menuButton.removeClass('expanded');

            }


        });


    /*
     * GALLERY LINK EVENTS
     */

    // Capture any gallery links and ensure they function with the colorbox
    if ($('.gallery', thisIndex).length){
        var windowWidth = thisWindow.width();
        if (windowWidth > 320){
            $('.gallery .screenshot', thisIndex).colorbox({
                rel:'screenshots',
                maxWidth:'800px',
                maxHeight:'600px',
                current:'Screenshot {current} of {total}',
                title:function(){ return $(this).find('.title').html()+' <span style="padding-left: 20px; opacity: 0.50; font-size: 80%;">('+$(this).find('.date').html().replace(/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/g, ' $1 / $2 / $3 ')+')</span>'; }
                });
            }
        }


    /*
     * GENREAL PAGE EVENTS
     */

    // Capture any header objects and move them around the document for better visual understanding
    if ($('.page', thisIndex).length){

        /*
        // Find any counter and move them to the header area visually
        if ($('.count_header', thisIndex).length){
            var tempHeader = $('h1.header', thisIndex);
            var tempCounter = $('span.count_header', thisIndex);
            $('.header_wrapper', tempHeader).append(tempCounter);
            }
        // Find any hideme classes in the headers and... hide them
        $('.header', thisIndex).find('.hideme').css({display:'none'});
        */

        // Find any link-blocks and ensure clicking anywhere in them leads to the link
        /*
        if ($('.thread_linkblock', thisIndex).length){
            $('.thread_linkblock', thisIndex).click(function(e){
                e.preventDefault();
                var thisLink = $(this).find('a').attr('href');
                window.location = thisLink.match() ? thisLink : gameSettings.baseHref+thisLink;
                });
            }
        */

        }


    /*
     * DATABASE EVENTS
     */

    // Create a reference to this form
    var thisDatabase = $('.page_database');
    var thisDatabaseLinks = $('.subbody_databaselinks', thisDatabase).not('.subbody_databaselinks_noajax');
    var thisBaseTitle = thisDatabaseLinks.attr('data-basetitle');
    // Ensure there is actually a community page wrapper to work with
    if (thisDatabase.length){

        // -- DATABASE SPRITE LINKS -- //

        // Create a function that generates database link events
        refreshDatabaseEvents(thisDatabase);

        // Loop through all the database link clusters
        thisDatabaseLinks.each(function(){

            // Collect the class for this cluster and a reference to it
            var thisCluster = $(this);
            var thisClass = thisCluster.attr('data-class');
            var thisClassSingle = thisCluster.attr('data-class-single');
            var thisClassText = thisClassSingle.charAt(0).toUpperCase() + thisClassSingle.slice(1);
            var thisContainer = $('.database_container', thisDatabase);

            // Prepend this cluster to the database container
            thisCluster.prependTo(thisContainer.parent());

            // Create a toggle event for the float link menu
            var thisClusterToggleLink = $('.link_toggle', thisCluster);
            var thisClusterToggleBody = $('.toggle_body', thisCluster);
            thisClusterToggleLink.click(function(e){
                e.preventDefault();
                var thisState = $(this).attr('data-state');
                if (thisState == 'expanded'){
                        thisClusterToggleBody.css({display:'none'});
                        thisClusterToggleLink.html('+ Show '+thisClassText+' Index +');
                        thisClusterToggleLink.attr('data-state', 'collapsed');
                    } else if (thisState == 'collapsed'){
                        thisClusterToggleBody.css({display:''});
                        thisClusterToggleLink.html('+ Hide '+thisClassText+' Index +');
                        thisClusterToggleLink.attr('data-state', 'expanded');
                    }
                });

            /*
            // Loop through this cluster's float links and attach events
            $('.float_link[data-token]', thisCluster).each(function(){

                // Collect the token for this link and attach the event
                var thisLink = $(this);
                var thisToken = thisLink.attr('data-token');
                var thisTitle = thisLink.attr('title');
                thisLink.click(function(e){

                    // Prevent the default click action
                    e.preventDefault();

                    // Update the window location hash
                    //window.location.hash = thisToken+'/';

                    // Collect the current token from the parent cluster
                    var currentToken = thisCluster.attr('data-current');
                    var currentBody = $('.database_'+thisClassSingle+'_container[data-token!='+thisToken+']', thisDatabase);

                    // If the current and new token are the same, return true
                    if (thisToken == currentToken){ return true; }

                    // Attempt to collect new markup from the database script
                    $.ajax({
                        type: 'POST',
                        url: 'scripts/database.php',
                        data: {'class':thisClass,'token':thisToken},
                        success: function(markup, status){
                            thisCluster.attr('data-current', thisToken);
                            var newRobotBody = $(markup);
                            newRobotBody.css({opacity:0});
                            var newRobotFunction = function(){
                                $(this).remove();
                                newRobotBody.waitForImages(function(){
                                    thisCluster.after(newRobotBody);
                                    newRobotBody.animate({opacity:1},200,'swing');
                                    if (thisBaseTitle != undefined){ document.title = thisTitle+' | '+thisBaseTitle; }
                                    $('.float_link[data-token]', thisCluster).removeClass('float_link_active').addClass('float_link_inactive');
                                    thisLink.removeClass('float_link_inactive').addClass('float_link_active');
                                    $('.database_'+thisClassSingle+'_container[data-token!='+thisToken+']', thisDatabase).remove();
                                    });
                                }
                            if (currentBody.length){ currentBody.animate({opacity:0},200,'swing',function(){ newRobotFunction(); }); }
                            else { newRobotFunction(); }
                            return true;
                            },
                        error: function(markup, status){
                            alert(status+' : '+markup);
                            return false;
                            }
                        });


                    //alert(thisClass+' : '+thisToken);

                    // Return true on success
                    return true;

                    });


                });

            // If this cluster does not have a token specified
            var currentToken = thisCluster.attr('data-current');
            if (currentToken == undefined || !currentToken.length){
                if (window.location.hash != undefined && window.location.hash.length){
                    var hashToken = window.location.hash;
                    if (hashToken.match(/^#/)){ hashToken = hashToken.slice(1, hashToken.length); }
                    if (hashToken.match(/\/$/)){ hashToken = hashToken.slice(0, -1); }
                    var firstLink = $('.float_link[data-token='+hashToken+']', thisCluster);
                    } else {
                    var firstLink = $('.float_link[data-token]', thisCluster).first();
                    }
                firstLink.trigger('click');
                }
            */


            });

            // Attach hover events to the field sprites if they exist
            $('#sprite_container .sprite_foreground', thisDatabase).live('mouseenter', function(){
                //console.log('mouseover');
                $(this).stop().css({cursor:'pointer'}).animate({opacity:0}, 600, 'swing');
                }).live('mouseleave', function(){
                //console.log('mouseout');
                $(this).stop().animate({opacity:1}, 600, 'swing');
                });


            // -- DATABASE TYPE CHARTS -- //

            // Create a reference to the type chart page
            var typeChart = $('.type_chart', thisDatabase);
            if (typeChart.length && typeof typeChartData != undefined){

                var chartCanvases = $(".chart_canvas");
                chartCanvases.each(function(){
                    var thisCanvas = $(this);
                    var thisCanvasKind = thisCanvas.attr('data-kind');
                    var thisCanvasData = typeChartData[thisCanvasKind];
                    if (typeof thisCanvasData == undefined){ return true; }
                    var thisChart = new Chart(thisCanvas, thisCanvasData);
                    });

                }

        }


    /*
     * COMMUNITY EVENTS
     */

    // Create a reference to any community container
    var thisCommunity = $('.community');
    // Ensure there is a community block to work with
    if (thisCommunity.length){

        // Define a toggle event for any community formatter wrappers
        var formattingToggleFirstClick = true;
        var formattingToggleFunction = function(e){
            e.preventDefault();

            //console.log('formattingToggleFunction');

            // Collect a reference to this link and the parent and wrapper
            var thisLink = $(this);
            var thisParent = thisLink.parents('.formatting');
            var thisWrapper = $('.wrapper', thisParent);

            // If this is the first time the element was clicked
            if (formattingToggleFirstClick){
                //console.log('formattingToggleFunction first click, let\'s hide');
                thisParent.removeClass('formatting_expanded');
                thisWrapper.css({display:'none',height:'',opacity:1});
                thisLink.html('+ Show Formatting Options');
                formattingToggleFirstClick = false;
                return true;
                }

            // If the wrapper is already expanded, let's collapse it
            if (thisParent.hasClass('formatting_expanded')){
                //console.log('formattingToggleFunction is expanded, closing now... to 0 height');
                thisWrapper.stop().animate({height:0,opacity:0},600,'swing',function(){
                    thisParent.removeClass('formatting_expanded');
                    thisLink.html('+ Show Formatting Options');
                    thisWrapper.css({display:'none',height:'',opacity:1});
                    return true;
                    });
                }
            // Otherwise if the wrapper is not yet expanded, let's do so now
            else {
                var maxHeight = thisWrapper.attr('data-maxheight');
                //console.log('formattingToggleFunction is collapsed, opening now... to '+maxHeight+' height');
                thisWrapper.css({display:'',height:0,opacity:0});
                thisWrapper.stop().animate({height:maxHeight+'px',opacity:1},600,'swing',function(){
                    thisParent.addClass('formatting_expanded');
                    thisLink.html('- Hide Formatting Options');
                    thisWrapper.css({height:''});
                    return true;
                    });

                }

            };

        // Backup the maximum height for the formatting wrapper for use later
        $('.formatting .wrapper', thisCommunity).attr('data-maxheight', $(this).height());

        // Attach the toggle function to the link's click event
        $('.formatting .toggle', thisCommunity).click(formattingToggleFunction);
        $('.formatting .toggle', thisCommunity).trigger('click');

        }

    // Create a reference to this form
    var thisCommunity = $('.page_community');
    var thisForm = $('.form', thisCommunity);
    // Ensure there is actually a community page wrapper to work with
    if (thisCommunity.length){

        // Append a button to the form for submitting
        var submitText = $('.buttons_active', thisForm).attr('data-submit');
        $('.buttons_active', thisForm).prepend('<input class="button button_submit" type="submit" value="'+submitText+'" />');

        // Prevent more than the max character count and update the visible counter
        var thisMaxLength = parseInt($('.counter .maximum', thisForm).html());
        var thisCurrent = $('.counter .current', thisForm);
        var thisButton = $('.button_submit', thisForm);
        $('.textarea', thisForm).keydown(function(event){

            // Prevent line breaks (update: internet says don't do it! D:)
            /*if(event.keyCode == 13) {
                event.preventDefault();
                return false;
                }*/

            // Collect the length for the textarea
            var thisContent = $(this).val();
            var thisLength = thisContent.length || 0;

            // Update the counter and return true
            thisCurrent.html(thisLength);

            // Check to ensure the counter is within limits
            if (thisLength <= thisMaxLength){

                // Change the colour back to default
                thisCurrent.css({color:''});
                // Enable the button again
                thisButton.css({opacity:1.00}).removeAttr('disabled').prop('disabled', false);

                } else if (thisLength > thisMaxLength){

                // Change the colour to red to indicate overage
                thisCurrent.css({color:'red'});
                // Disable the button
                thisButton.css({opacity:0.60}).attr('disabled', true).prop('disabled', true);

                }

            // Return true on success
            return true;

            }).trigger('keydown');

            // Creare click events for the frame scrollers
            var thisSelector = $('.avatar_selector', thisForm);
            var thisSprite = $('.sprite', thisSelector);
            var thisSelectorFrames = thisSprite.length ? thisSprite.attr('data-frames').split(',') : [];
            var thisSelectorFramesMinKey = 0;
            var thisSelectorFramesMaxKey = thisSelectorFrames.length - 1;
            var thisSelectorType = $('input[name=thread_frame]', thisForm).val() != undefined ? 'thread' : 'post';
            $('.back', thisSelector).click(function(){
                //alert('back');
                var currentFrame = thisSelectorType == 'thread' ?  $('input[name=thread_frame]', thisForm).val() : $('input[name=post_frame]', thisForm).val();
                var currentKey = thisSelectorFrames.indexOf(currentFrame);
                if (currentKey > thisSelectorFramesMinKey){ var newKey = currentKey - 1; }
                else { var newKey = thisSelectorFramesMaxKey; }
                var newFrame = thisSelectorFrames[newKey];
                if (thisSprite.hasClass('sprite_80x80')){ thisSprite.removeClass('sprite_80x80_'+currentFrame).addClass('sprite_80x80_'+newFrame); }
                else { thisSprite.removeClass('sprite_160x160_'+currentFrame).addClass('sprite_160x160_'+newFrame); }
                $('input[name='+(thisSelectorType == 'thread' ? 'thread_frame' : 'post_frame')+']', thisForm).val(newFrame);
                });
            $('.next', thisSelector).click(function(){
                //alert('next');
                var currentFrame = thisSelectorType == 'thread' ?  $('input[name=thread_frame]', thisForm).val() : $('input[name=post_frame]', thisForm).val();
                var currentKey = thisSelectorFrames.indexOf(currentFrame);
                if (currentKey < thisSelectorFramesMaxKey){ var newKey = currentKey + 1; }
                else { var newKey = thisSelectorFramesMinKey; }
                var newFrame = thisSelectorFrames[newKey];
                if (thisSprite.hasClass('sprite_80x80')){ thisSprite.removeClass('sprite_80x80_'+currentFrame).addClass('sprite_80x80_'+newFrame); }
                else { thisSprite.removeClass('sprite_160x160_'+currentFrame).addClass('sprite_160x160_'+newFrame); }
                $('input[name='+(thisSelectorType == 'thread' ? 'thread_frame' : 'post_frame')+']', thisForm).val(newFrame);
                });

            // Add click events to any delete buttons
            $('.options .delete', thisCommunity).click(function(e){
                e.preventDefault();
                var thisHref = $(this).attr('data-href');
                if (confirm('Are you sure you want to delete this comment?')){
                    window.location.href = thisHref;
                    return true;
                    } else {
                    return false;
                    }
                });

            // Append toggle buttons to each date group on the page
            $('.category_date_group', thisCommunity).each(function(){
                var newButton = $('<a class="toggle toggle_expanded">-</a>');
                $(this).append(newButton);
                });
            //  Create click events for the expand/collapse toggles
            $('.category_date_group', thisCommunity).click(function(e){
                e.preventDefault();
                $(this).find('.toggle').trigger('click');
                });
            $('.category_date_group .toggle', thisCommunity).toggle(
                function(e){
                    e.stopPropagation();
                    var thisDateGroup = $(this).parent().attr('data-group');
                    $('.thread_subbody[data-group='+thisDateGroup+']', thisCommunity).addClass('thread_subbody_hidden');
                    $(this).removeClass('toggle_expanded').addClass('toggle_collapsed').html('+');
                    },
                function(e){
                    e.stopPropagation();
                    var thisDateGroup = $(this).parent().attr('data-group');
                    $('.thread_subbody[data-group='+thisDateGroup+']', thisCommunity).removeClass('thread_subbody_hidden');
                    $(this).removeClass('toggle_collapsed').addClass('toggle_expanded').html('-');
                    });
             // Auto click every toggle that's locked
             $('.subheader[data-group=locked]', thisCommunity).find('.toggle').trigger('click');
             // Auto click every toggle after the first
             //$('.category_date_group .toggle:gt(0)', thisCommunity).trigger('click');

        // Collect the comment form if it exists
        var commentForm = $('.thread_posts_form form', thisCommunity);
        var commentTextarea = $('textarea[name=post_body]', commentForm);
        //if (commentForm.length){ alert('commentForm exists!'); commentTextarea.css({borderColor:'red'}); }

        // Define the function to call when a postreply is clicked
        var postReplyFunction = function(name, colour){
            //console.log({name:name,colour:colour});
            window.location.hash = '#comment-form';
            $('html, body').animate({scrollTop:commentForm.offset().top}, 1000, 'swing');
            commentTextarea.trigger('focus');
            var currentValue = commentTextarea.val();
            var newValue = currentValue;
            if (newValue.length){ newValue += '\n\n'; }
            newValue += '@['+name+']{'+colour+'} : ';
            commentTextarea.val(newValue);
            };

        // Define functionality for the @Reply buttons if possible
        if (commentForm.length){
            $('a.postreply', thisCommunity).click(function(e){
                e.preventDefault();
                var thisLink = $(this);
                var thisHref = thisLink.attr('href');
                var thisHash = thisHref.split('#').pop();
                var thisParams = thisHash.split(':');
                //console.log({thisHref:thisHref,thisHash:thisHash,thisParams:thisParams});
                return postReplyFunction(thisParams[1], thisParams[2]);
                });
            }

        // Now that we have everything set up, check if this page already has a hash
        if (window.location.hash.length){
            var thisHash = window.location.hash;
            //console.log({thisHash:thisHash});
            if (thisHash.match(/^#comment-form\:/i)){
                var thisParams = thisHash.split(':');
                //console.log({thisHash:thisHash,thisParams:thisParams});
                return postReplyFunction(thisParams[1], thisParams[2]);
                }
            }

        }


    /*
     * FILE EVENTS
     */

    // Create a reference to this form
    var gameButtons = $('#mmrpg #game_buttons');
    var gameFrames = $('#mmrpg #game_frames');

    // Ensure there is actually a community page wrapper to work with
    if (gameButtons.length){

        // Define the click action for the game buttons
        if (gameFrames.length){
            $('a[data-token]', gameButtons).click(function(e){
                e.preventDefault();

                var thisButton = $(this);
                if (thisButton.hasClass('link_button_active')){ return false; }

                var thisToken = thisButton.attr('data-token');
                var thisFrame = $('iframe[name='+thisToken+']', gameFrames);

                $('.link_button_active', gameButtons).removeClass('link_button_active');
                thisButton.addClass('link_button_active');

                var frameIsReady = true;
                if (thisFrame.attr('src') == 'blank.php'){
                    thisFrame.attr('src', thisFrame.attr('data-src'));
                    frameIsReady = false;
                    }

                gameFrames.css({height:'413px'});
                $('iframe[name!='+thisToken+']', gameFrames).fadeOut('slow', function(){
                    $(this).css({display:'none'});
                    showThisElement = thisFrame; //.css({display:'block'});
                    if (frameIsReady){ prototype_menu_loaded(); }
                    });

                });
            }

        }

    // Attach scrollable events to the game frames if exist
    if (gameFrames.length){

        // Trigger perfect scrollbars on the frame containers
        gameFrames.perfectScrollbar(thisScrollbarSettings);
        $(window).resize(function(){ gameFrames.perfectScrollbar('update'); });

        }


    /*
     * LEADERBOARD EVENTS
     */

    // Capture any gallery links and ensure they function with the colorbox
    var $thisLeaderboard = $('.page_leaderboard', thisIndex);
    if ($thisLeaderboard.length){

        // Define events for the leaderboard "Points" sub-page
        var $pointsTable = $('.view_points', $thisLeaderboard);
        if ($pointsTable.length){
            // Define click events for the individual toggle buttons on the row details
            $('tbody a.toggle', $pointsTable).bind('click', function(e){
                e.preventDefault();
                var $link = $(this);
                var $topRow = $link.closest('tr');
                var $nextRow = $topRow.next();
                var $details = $nextRow.find('.details ul');
                if ($nextRow.hasClass('hidden')){
                    // Expand the next row, change the icon to a "-"
                    $nextRow.removeClass('hidden');
                    $link.addClass('expanded').html('<span>-</span>');
                    $details.perfectScrollbar('update');
                    //$('html, body').animate({scrollTop: ($link.offset().top - 20)}, 'fast');
                    $('html, body').animate({scrollTop: '+='+$details.outerHeight()+'px'}, 'slow');
                    } else {
                    // Collapse the next row, change the icon to a "+"
                    $nextRow.addClass('hidden');
                    $link.removeClass('expanded').html('<span>+</span>');
                    }
                });
            $pointsTable.find('.details ul').css({overflow:'hidden'}).perfectScrollbar(thisScrollbarSettings);
            $(window).resize(function(){ $pointsTable.find('.details ul').perfectScrollbar('update'); });
            // Define a click event for the global expand/collapse all button of all rows
            $('thead a.toggle', $pointsTable).bind('click', function(e){
                e.preventDefault();
                var $link = $(this);
                var $tbody = $pointsTable.find('tbody');
                var $allHidden = $tbody.find('tr.details.hidden');
                if ($allHidden.length){
                    // Some are hidden so we should expand all
                    $allHidden.removeClass('hidden').find('.details ul').perfectScrollbar('update');
                    } else {
                    // None are hidden so we should collapse all
                    $tbody.find('tr.details').addClass('hidden');
                    }
                });
            }

        }

});

// Redefine the prototype menu loaded to prevent errors
var showThisElement = false;
function prototype_menu_loaded(){
    if (showThisElement != false){
        showThisElement.fadeIn('slow', function(){
            showThisElement = false;
            $(this).css({display:'block',opacity:1});
            });
    }
    return true;
}

// Create the windowResize event for this page
function windowResizePage(){

    var indexWidth = thisIndex.width();
    var indexHeight = thisIndex.height();
    var bannerHeight = $('.banner', thisBody).outerHeight(true);
    var menuHeight = $('.menu', thisBody).outerHeight(true);
    var headerHeight = $('.header', thisBody).outerHeight(true);

    var newIndexHeight = indexHeight;
    var newPageHeight = newIndexHeight - bannerHeight - menuHeight - headerHeight - 30;

    //alert('windowResizePage()! newIndexHeight = '+newIndexHeight+'; newPageHeight = '+newPageHeight+'; ');

    $('.body, .body_wrapper', thisIndex).css({overflow:'scroll',overflowX:'hidden',height:newPageHeight+'px'});

    //alert('windowWidth = '+windowWidth+'; windowHeight = '+windowHeight+'; bannerHeight = '+bannerHeight+'; ');

    // Update the dimensions
    gameSettings.currentBodyWidth = indexWidth; //$(document).width(); //mmrpgBody.outerWidth();
    gameSettings.currentBodyHeight = indexHeight; //$(document).height(); //mmrpgBody.outerHeight();

}

// Create a function that generates database link events
function refreshDatabaseEvents(thisDatabase){
    //console.log('refreshDatabaseEvents(thisDatabase)');

    // Collect a reference to the tabs container and make sure it exists
    var thisSpritesHeader = $('#tabs', thisDatabase);

    // Collect a reference to the link container and make sure it exists
    var thisSpritesHeader = $('#sprites', thisDatabase);
    var thisSpritesBody = $('#sprites_body', thisDatabase);
    if (thisSpritesHeader.length && thisSpritesBody.length){
        //console.log('database sprite links');

        // Collect a reference to the link container if it exists
        var thisLinkContainer = $('.image_link_container', thisSpritesHeader);
        if (thisLinkContainer.length){
        //console.log('database sprite links > image link container');

            // Define the click events for the direction links
            var directionLinks = $('.directions', thisLinkContainer);
            $('.link_direction', directionLinks).click(function(e){
                e.preventDefault();
                var thisLink = $(this);
                var thisDirection = thisLink.attr('data-direction');
                var thisImage = $('.images .link_active', thisLinkContainer).attr('data-image');
                //console.log('database sprite links > image link container > click direction '+thisDirection);
                $('.link', directionLinks).removeClass('link_active');
                thisLink.addClass('link_active');
                $('.frame_container', thisSpritesBody).css({display:'none'});
                $('.frame_container[data-image='+thisImage+'][data-direction='+thisDirection+']', thisSpritesBody).css({display:''});
                });
            // Auto-click the first link or whichever one has the active class
            var firstLink = $('.link_active', directionLinks).length ? $('.link_active', directionLinks) : $('.link_direction:first-child', directionLinks);
            firstLink.trigger('click');

            // Define the click events for the image links
            var imageLinks = $('.images', thisLinkContainer);
            $('.link_image', imageLinks).click(function(e){
                e.preventDefault();
                var thisLink = $(this);
                var thisImage = thisLink.attr('data-image');
                var thisDirection = $('.directions .link_active', thisLinkContainer).attr('data-direction');
                //console.log('database sprite links > image link container > click image '+thisImage);
                $('.link', imageLinks).removeClass('link_active');
                thisLink.addClass('link_active');
                $('.frame_container', thisSpritesBody).css({display:'none'});
                $('.frame_container[data-image='+thisImage+'][data-direction='+thisDirection+']', thisSpritesBody).css({display:''});
                });
            // Auto-click the first link or whichever one has the active class
            var firstLink = $('.link_active', imageLinks).length ? $('.link_active', imageLinks) : $('.link_image:first-child', imageLinks);
            firstLink.trigger('click');

            }

        }

    // Return true on success
    return true;

}