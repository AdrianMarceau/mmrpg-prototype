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
     * WEBSITE POPUPS
     */

    // Define functionality for showing custom popups on the website
    (function(){

        var $window = $('#window');

        var $popupOverlay = false;
        var $popupWrapper = false;
        var $popupBody = false;
        var $popupContent = false;

        var popupVisible = false;

        // Define a function for initializing the popup markup/events
        var initPopups = function(){

            // Generate the basic skelton markup for the popup overlay
            $popupOverlay = $('<div class="popup_overlay"></div>');
            $popupWrapper = $('<div class="popup_wrapper"></div>');
            $popupBody = $('<div class="popup_body"></div>');
            $popupContent = $('<div class="popup_content"></div>');
            $popupContent.appendTo($popupBody);
            $popupBody.appendTo($popupWrapper);
            $popupWrapper.appendTo($popupOverlay);
            $popupOverlay.appendTo($window);

            // Append a close button to the body for closing the popup
            var $closeButton = $('<a class="button close"><i class="fa fas fa-window-close"></i></a>');
            $closeButton.bind('click', function(e){
                e.preventDefault();
                hidePopup();
                }).appendTo($popupBody);

            // Make sure scrolling the window keeps the popup anchored
            $(window).bind('scroll', function(){
                if (popupVisible){
                    var wrapTop = (getWinPos().top + 50) + 'px';
                    $popupWrapper.css({top:wrapTop});
                    }
                });

            };

        // Define a function for quickly getting the window scroll position
        var getWinPos = function(){
            var doc = document.documentElement;
            var left = (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0);
            var top = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);
            var winPos = {left:left,top:top};
            return winPos;
            };

        // Define a function for actually showing a popup
        var showPopup = function(markup, options){
            if (typeof markup !== 'string'){ return false; }
            if (typeof options !== 'object'){ options = {}; }
            var wrapTop = (getWinPos().top + 50) + 'px';
            $popupOverlay.css({opacity:0});
            $popupWrapper.css({top:wrapTop});
            $popupContent.empty().append(markup);
            $popupOverlay.addClass('visible').animate({opacity:1},300,'swing',function(){
                popupVisible = true;
                });
            };

        // Define a function for hiding an existing popup
        var hidePopup = function(){
            $popupOverlay.css({opacity:1});
            $popupOverlay.animate({opacity:0},300,'swing',function(){
                $popupOverlay.removeClass('visible');
                popupVisible = false;
                });
            };

        // Automatically init the popup engine
        initPopups();

        // Explose the show/hide popup methods globally
        window.mmrpgShowPopup = showPopup;
        window.mmrpgHidePopup = hidePopup;

        })();


    /*
     * COMMUNITY EVENTS
     */

    // Define a quick function for showing a formatting-based popup
    var showFormattingPopup = function(markup){
        markup = '<div class="subbody">' + markup + '</div>';
        markup = '<div class="body">' + markup + '</div>';
        markup = '<div class="page">' + markup + '</div>';
        mmrpgShowPopup(markup);
        };

    // Regardless of current page, make sure community formatting popup works
    $formattingPopupLinks = $('a[data-popup="community-formatting-help"]');
    if ($formattingPopupLinks.length){
        (function(){
            // Collect the formatting markup into a variable for later
            var formattingMarkup = '';
            var markupScriptURL = 'scripts/get-markup.php?kind=community-formatting-help';
            $.ajax({
                url: markupScriptURL,
                success: function(result){
                    formattingMarkup = result;
                    }
                });
            // Bind a click event to the actual button for showing the guide
            $formattingPopupLinks.bind('click', function(e){
                e.preventDefault();
                if (!formattingMarkup.length){ return false; }
                showFormattingPopup(formattingMarkup);
                });
            })();
        }

    // Regardless of current page, make sure community formatting popup works
    $formattingPopupLinks = $('a[data-popup="community-formatting-preview"]');
    if ($formattingPopupLinks.length){
        (function(){
            // Define the URL we'll be posting preview requests to
            var markupScriptURL = 'scripts/get-markup.php?kind=community-formatting-preview';
            // Define a function for getting a post/thread textarea given a preview button
            var getPostTextarea = function($button){
                var $form = $button.closest('form');
                var $textarea = $form.find('textarea[name$="_body"]');
                return $textarea;
                };
            // Loop through each of the preview buttons on-page and define events for 'em
            $formattingPopupLinks.each(function(){
                // Collect a ref to the button and the textarea
                var $button = $(this);
                var $textarea = getPostTextarea($button);
                // Bind a keyup event to the textarea to show/hide the button
                $textarea.bind('keyup', function(){
                    var rawMarkup = $textarea.val();
                    if (rawMarkup.length){ $button.removeClass('hidden'); }
                    else { $button.addClass('hidden'); }
                    }).trigger('keyup');
                // Bind a click event to the button that generates the post preview
                $button.bind('click', function(e){
                    e.preventDefault();
                    var rawMarkup = $textarea.val();
                    if (!rawMarkup.length){ return false; }
                    $.post(markupScriptURL, {
                        rawMarkup:rawMarkup
                        }, function(result){
                        showFormattingPopup(result);
                        });
                    });
                });

            })();
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
                var $details = $nextRow.find('.details ul:not(.autoheight)');
                if ($nextRow.hasClass('hidden')){
                    // Expand the next row, change the icon to a "-"
                    $nextRow.removeClass('hidden');
                    $link.addClass('expanded').html('<span>-</span>');
                    $details.perfectScrollbar('update');
                    //$('html, body').animate({scrollTop: ($link.offset().top - 20)}, 'fast');
                    var newScrollTop = $details.outerHeight();
                    //console.log('newScrollTop = ', newScrollTop);
                    if (newScrollTop){ $('html, body').animate({scrollTop: '+='+newScrollTop+'px'}, 'slow'); }
                    } else {
                    // Collapse the next row, change the icon to a "+"
                    $nextRow.addClass('hidden');
                    $link.removeClass('expanded').html('<span>+</span>');
                    }
                });
            $pointsTable.find('.details ul:not(.autoheight)').css({overflow:'hidden'}).perfectScrollbar(thisScrollbarSettings);
            $(window).resize(function(){ $pointsTable.find('.details ul:not(.autoheight)').perfectScrollbar('update'); });
            // Define a click event for the global expand/collapse all button of all rows
            $('thead a.toggle', $pointsTable).bind('click', function(e){
                e.preventDefault();
                var $link = $(this);
                var $tbody = $pointsTable.find('tbody');
                var $allHidden = $tbody.find('tr.details.hidden');
                if ($allHidden.length){
                    // Some are hidden so we should expand all
                    $allHidden.removeClass('hidden').find('.details ul:not(.autoheight)').perfectScrollbar('update');
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