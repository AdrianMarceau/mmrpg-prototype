// Generate the document ready events for this page
var $thisBody = false;
var $thisPrototype = false;
var $thisWindow = false;
var $thisSettings = false;
var $thisSettingsPanel = false;
var thisScrollbarSettings = {wheelSpeed:0.3,suppressScrollX:true,scrollYMarginOffset:6};
var resizeSettingsWrapper = function(){};
$(document).ready(function(){

    // Update global reference variables
    $thisBody = $('#mmrpg');
    $thisPrototype = $('#prototype', $thisBody);
    $thisWindow = $(window);
    $thisSettings = $('#settings', $thisBody);
    $thisSettingsPanel = $('.settings_panel', $thisSettings);

    // Check if this window is currently in an iframe
    var windowIsFrame = window.self != window.parent ? true : false;

    // Collect (or define) a function to run profile updates through
    var windowUpdateProfileSettings = function(){};
    if (typeof parent.window.prototype_update_profile_settings !== 'undefined'){
        windowUpdateProfileSettings = parent.window.prototype_update_profile_settings;
        }

    // Ensure a settings panels exists before trying to delegate events
    if ($thisSettingsPanel.length){

        // Attach the scrollbar to the battle events container
        $('.tab_sections', $thisSettingsPanel).perfectScrollbar(thisScrollbarSettings);
        $('.tab_sections', $thisSettingsPanel).perfectScrollbar('update');
        $thisWindow.resize(function(){ $('.tab_sections', $thisSettingsPanel).perfectScrollbar('update'); });

        // Define the tab-switching events for the settings panel
        var $tabLinks = $('.tab_links .link', $thisSettingsPanel);
        var $tabSections = $('.tab_sections .section', $thisSettingsPanel);
        var $tabButtons = $('.tab_buttons', $thisSettingsPanel);
        $tabLinks.bind('click', function(e){
            var $thisLink = $(this);
            var tabToken = $thisLink.attr('data-tab');
            var $thisSection = $tabSections.filter('.section[data-tab="'+tabToken+'"]');
            if ($thisLink.hasClass('hide_tab_buttons')){ $tabButtons.addClass('hidden'); }
            else { $tabButtons.removeClass('hidden'); }
            if ($thisLink.hasClass('active')){ return true; }
            $tabLinks.removeClass('active');
            $tabSections.removeClass('active');
            $thisLink.addClass('active');
            $thisSection.addClass('active');
            $('input[name="current_tab"]', $thisSettingsPanel).val(tabToken);
            });
        $tabLinks.filter('.active').trigger('click');

        // Define a basic validation function to prevent premature submissions
        var $requiredFields = $('[required="required"]', $tabSections);
        var $saveButton = $('.button.save', $tabButtons);
        var validateFunction = function(){
            var isValid = true;
            $requiredFields.each(function(){
                var $field = $(this);
                var name = $field.attr('name');
                var type = $field.attr('type');
                var value = $field.val().replace(/^\s+/, '').replace(/\s+$/, '');
                var valid = true;
                if (!value.length){ valid = false; }
                if (type === 'email' && !value.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)){ valid = false; }
                if (!valid){ $field.addClass('error'); isValid = false; }
                else { $field.removeClass('error'); }
                });
            if (!isValid){ $saveButton.attr('disabled', 'disabled'); }
            else { $saveButton.removeAttr('disabled'); }
            };
        $requiredFields.bind('keyup blur', validateFunction);
        validateFunction();

        // Define a function that clears any on-page messages after a few seconds
        var clearMessagesFunction = function(onComplete){
            if (typeof onComplete !== 'function'){ onComplete = function(){}; }
            var $messageList = $('.messages', $thisSettings);
            if ($messageList.length){
                var clearMessagesAfter = 4000;
                var clearMessagesTimeout = setTimeout(function(){
                    $('.message', $messageList).each(function(){
                        $(this).animate({opacity:0},500,'swing',function(){
                            $(this).remove();
                            if (!$('.message', $messageList).length){
                                $messageList.remove();
                                $('.tab_sections', $thisSettingsPanel).perfectScrollbar('update');
                                return onComplete();
                                }
                            });
                        });
                    }, clearMessagesAfter);
                } else {
                $('.tab_sections', $thisSettingsPanel).perfectScrollbar('update');
                return onComplete();
                }
            };


        // If there are messages on the page, automatcally fade them after a few seconds
        clearMessagesFunction();


        }

    // Check to see if we are in an iframe before delegating parent events
    if (windowIsFrame){

        // If profile settings were defined and we're in an iframe, we may need to update
        if (typeof window.profileSettings !== 'undefined'){
            //console.log('window.profileSettings = ', window.profileSettings);
            windowUpdateProfileSettings(window.profileSettings);
            }

        }


    /*
     * OTHER STUFF
     */

    // Attach resize events to the window
    $thisWindow.resize(function(){ windowResizeFrame(); });
    setTimeout(function(){ windowResizeFrame(); }, 1000);
    windowResizeFrame();

    var windowHeight = $(window).height();
    var htmlHeight = $('html').height();
    var htmlScroll = $('html').scrollTop();

    // Fade in the leaderboard screen slowly
    $thisBody.waitForImages(function(){
        var tempTimeout = setTimeout(function(){
            if (gameSettings.fadeIn){ $thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
            else { $thisBody.removeClass('hidden').css({opacity:1}); }
            //console.log('updating perfect scrollbar 4');
            $('#console .scroll_wrapper', $thisSettings).perfectScrollbar('update');
            // Let the parent window know the menu has loaded
            parent.prototype_menu_loaded();
            }, 1000);
        }, false, true);

});

// Create the windowResize event for this page
function windowResizeFrame(){

    var windowWidth = $thisWindow.width();
    var windowHeight = $thisWindow.height();
    var headerHeight = $('.header', $thisBody).outerHeight(true);

    var newBodyHeight = windowHeight;
    var newFrameHeight = newBodyHeight - headerHeight;
    var newScrollWrapperHeight = newFrameHeight - 142;

    if (windowWidth > 800){ $thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
    else { $thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

    //console.log('windowWidth = '+windowWidth+'; parentWidth = '+parentWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}

// Define a function for printing a number with commas as thousands separators
function printNumberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}