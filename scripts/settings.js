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

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the items menu
    var thisContext = $('#settings');
    var playSoundEffect = function(){};
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

        // SETTINGS MENU TABS

        // Add hover and click sounds to the buttons in the main menu
        $('.tab_links .link', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.8});
            });
        $('.tab_links .link', thisContext).live('click', function(){
            playSoundEffect.call(this, 'icon-click', {volume: 1.0});
            });


        // GAME SETTINGS RADIO FIELDS

        // Add hover and click sounds to the buttons in the main menu
        $('.field .radiofield', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.8});
            });
        $('.field .radiofield', thisContext).live('click', function(){
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });


        // GAME SETTINGS AUDIO SLIDERS

        // Add hover and click sounds to the buttons in the main menu
        $('.field .slider', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 1.0});
            });
        /*
        $('.field .slider', thisContext).live('click', function(){
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });
        $('.field .slider', thisContext).live('change', function(){
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });
            */

        // SAVE & DISCARD BUTTONS

        // Add hover and click sounds to the buttons in the main menu
        $('.tab_buttons .button', thisContext).live('mouseenter', function(){
            if ($(this).is('.reset')){
                playSoundEffect.call(this, 'back-hover', {volume: 1.0});
                }
            else {
                playSoundEffect.call(this, 'link-hover', {volume: 0.8});
                }
            });
        $('.tab_buttons .button', thisContext).live('click', function(){
            if ($(this).is('.button.save')){
                playSoundEffect.call(this, 'link-click-special', {volume: 1.0});
                }
            else if ($(this).is('.reset')){
                playSoundEffect.call(this, 'back-click', {volume: 1.0});
                setTimeout(function(){ playSoundEffect.call(this, 'back-click-loading', {volume: 1.0}); }, 300);
                }
            else {
                playSoundEffect.call(this, 'link-click', {volume: 1.0});
                }
            });

        }

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
        $tabLinks.filter('.active').triggerSilentClick();
        var $clickOnceButtons = $tabButtons.find('.button.clickonce');
        //console.log('$clickOnceButtons =', $clickOnceButtons.length, $clickOnceButtons);
        $clickOnceButtons.bind('click', function(e){
            $(this).addClass('clicked');
            $tabSections.css({opacity: 0.5, filter:'brightness(0.5)'});
            });

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


    // -- GAME SETTINGS -- //

    // Process complex game setting updates and pass them to the parent window
    var $gameSettings = $('.game-settings', $thisSettings);
    if ($gameSettings.length){
        //console.log('we have game settings specifically');

        // Collect references to the appropriate windows for updating
        var thisMusicWindow = window.top;
        var thisSpriteWindow = window.self;
        var thisGameSettings = window.top.gameSettings;

        // Collect references to the applicable form fields
        var $audoBalanceConfigField = $('.field[data-setting="audioBalanceConfig"]', $gameSettings);
        var $spriteRenderModeField = $('.field[data-setting="spriteRenderMode"]', $gameSettings);

        // Define a function for updating the audio balance config w/ form changes
        function updateAudioBalanceConfig(newConfig){
            //console.log('updateAudioBalanceConfig(newConfig) w/', newConfig);
            if (typeof newConfig !== 'object'){ return false; }
            var masterVolume = typeof newConfig.masterVolume === 'number' ? newConfig.masterVolume : thisGameSettings.masterVolume;
            var musicVolume = typeof newConfig.musicVolume === 'number' ? newConfig.musicVolume : thisGameSettings.musicVolume;
            var effectVolume = typeof newConfig.effectVolume === 'number' ? newConfig.effectVolume : thisGameSettings.effectVolume;
            //thisGameSettings.masterVolume = masterVolume;
            //thisGameSettings.musicVolume = musicVolume;
            //thisGameSettings.effectVolume = effectVolume;
            if (typeof thisMusicWindow.mmrpg_master_volume !== 'undefined'){ thisMusicWindow.mmrpg_master_volume(masterVolume); }
            if (typeof thisMusicWindow.mmrpg_music_volume !== 'undefined'){ thisMusicWindow.mmrpg_music_volume(musicVolume); }
            if (typeof thisMusicWindow.mmrpg_sound_effect_volume !== 'undefined'){ thisMusicWindow.mmrpg_sound_effect_volume(effectVolume); }
            return true;
        }

        // Define a function for updating the sprite rendering mode w/ form changes
        function updateSpriteRenderMode(newMode){
            //console.log('updateSpriteRenderMode(newMode) w/', newMode);
            if (typeof newMode !== 'string'){ return false; }
            var newRenderMode = newMode.length ? newMode : thisGameSettings.spriteRenderMode;
            thisGameSettings.spriteRenderMode = newRenderMode;
            $('#mmrpg').attr('data-render-mode', newRenderMode);
            return true;
        }

        // Define a function for parsing the audio balance config from the form
        function parseAudioBalanceConfig(){
            // collect refs to all three fields manually
            var $masterVolumeField = $('input[name="masterVolume"]', $audoBalanceConfigField);
            var $musicVolumeField = $('input[name="musicVolume"]', $audoBalanceConfigField);
            var $effectVolumeField = $('input[name="effectVolume"]', $audoBalanceConfigField);
            // collect the values from the three fields
            var masterVolume = parseFloat($masterVolumeField.val());
            var musicVolume = parseFloat($musicVolumeField.val());
            var effectVolume = parseFloat($effectVolumeField.val());
            // construct a new config object to update with
            var newConfig = {};
            newConfig.masterVolume = masterVolume;
            newConfig.musicVolume = musicVolume;
            newConfig.effectVolume = effectVolume;
            //console.log('newConfig = ', newConfig);
            return newConfig;
            };

        // Define a function for parsing the sprite render mode setting from the form
        function parseSpriteRenderMode(){
            var $checkedInput = $('input[type="radio"]:checked', $spriteRenderModeField);
            var checkedValue = $checkedInput.val();
            return checkedValue;
            };

        // Backup the user's audio changes in case we need to reset them
        var userAudioConfigBackup = {};
        userAudioConfigBackup = parseAudioBalanceConfig();

        // Backup the user's sprite render mode in case we need to reset it
        var userSpriteRenderModeBackup = '';
        userSpriteRenderModeBackup = parseSpriteRenderMode();

        // Define click events for the game settings form elements
        $('input[type="range"]', $audoBalanceConfigField).bind('change', function(e){
            //console.log('change event on audio balance config field');
            var newConfig = parseAudioBalanceConfig();
            updateAudioBalanceConfig(newConfig);
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });

        // Make it so when the user clicks on a radio button's container it automatically triggers the radio button inside
        $('.radiofield', $spriteRenderModeField).bind('click', function(e){
            //console.log('click event on sprite render mode field');
            var $thisField = $(this);
            var $radioButton = $('input[type="radio"]', $thisField);
            $radioButton.prop('checked', true);
            $radioButton.trigger('change');
            });
        $('input[type="radio"]', $spriteRenderModeField).bind('change', function(e){
            //console.log('change event on sprite render mode field');
            e.stopPropagation();
            var checkedValue = parseSpriteRenderMode();
            updateSpriteRenderMode(checkedValue);
            $spriteRenderModeField.find('.radiofield').removeClass('active');
            $('input[type="radio"]:checked', $spriteRenderModeField).closest('.radiofield').addClass('active');
            });

        // Reset back to backup values if the user switches windows without saving
        var resetGameSettings = function(){
            //console.log('resetGameSettings()');
            //console.log('userAudioConfigBackup = ', userAudioConfigBackup);
            //console.log('userSpriteRenderModeBackup = ', userSpriteRenderModeBackup);
            updateAudioBalanceConfig(userAudioConfigBackup);
            updateSpriteRenderMode(userSpriteRenderModeBackup);
            };
        var applyGameSettings = function(){
            //console.log('applyGameSettings()');
            //console.log('parseAudioBalanceConfig() = ', parseAudioBalanceConfig());
            //console.log('parseSpriteRenderMode() = ', parseSpriteRenderMode());
            updateAudioBalanceConfig(parseAudioBalanceConfig());
            updateSpriteRenderMode(parseSpriteRenderMode());
            };
        window.addEventListener('message', function(event){
            //console.log('iframe received a message from', event.origin);
            // IMPORTANT: Check the origin of the data!
            if (event.origin.startsWith(gameSettings.baseHref)){
                if (event.data === 'hidden'){
                    //console.log('The iframe was hidden!');
                    resetGameSettings();
                    }
                }
            });
        window.onblur = function(){
            //console.log('iframe has lost focus!');
            resetGameSettings();
            };
        window.onfocus = function(){
            //console.log('iframe has gained focus!');
            applyGameSettings();
            };

        // Automatically update saved game settings to be sure it's working
        updateAudioBalanceConfig(parseAudioBalanceConfig());
        updateSpriteRenderMode(parseSpriteRenderMode());

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