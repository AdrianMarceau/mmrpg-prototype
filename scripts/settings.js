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
        playSoundEffect = function(soundName, options, isMenuSound){
            if (this instanceof jQuery || this instanceof Element){
                if ($(this).data('silentClick')){ return; }
                if ($(this).is('.disabled')){ return; }
                if ($(this).is('.button_disabled')){ return; }
                }
            top.mmrpg_play_sound_effect(soundName, options, isMenuSound);
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


    // -- SETTINGS HELPER FUNCTIONS -- //

    // Define a function that updates the range labels for the game settings w/ current values
    var updateRangeLabelValue = function($input, $value, value){
        //console.log('updateRangeLabelValue() w/' + '\n$input:', $input, '\n$value:', $value, '\nvalue:', value);
        var text = value;
        if ($input.is('[min][max][data-percent]')){
            //console.log('input has a min, max, and percent');
            var min = parseFloat($input.attr('min'));
            var max = parseFloat($input.attr('max'));
            var percent = 0;
            if (min === 0 && max === 1){ percent = ((value / max) * 100); }
            text = percent.toFixed(0) + '%';
            //console.log('percent debug' + '\nvalue: ', typeof value, value, '\nmin: ', typeof min, min, '\nmax: ', typeof max, max, '\npercent: ', typeof percent, percent, '\ntext: ', typeof text, text);
        }
        if ($input.is('[max][data-max-text]')){
            //console.log('input has a max and max-text');
            var max = parseFloat($input.attr('max'));
            var maxText = $input.attr('data-max-text');
            if (value === max){ text = maxText; }
            //console.log('max debug' + '\nvalue: ', typeof value, value, '\nmax: ', typeof max, max, '\nmaxText: ', typeof maxText, maxText, '\ntext: ', typeof text, text);
            }
        if ($input.is('[min][data-min-text]')){
            //console.log('input has a min and min-text');
            var min = parseFloat($input.attr('min'));
            var minText = $input.attr('data-min-text');
            if (value === min){ text = minText; }
            //console.log('min debug' + '\nvalue: ', typeof value, value, '\nmin: ', typeof min, min, '\nminText: ', typeof minText, minText, '\ntext: ', typeof text, text);
            }
        if ($input.is('[data-show-sign]')){
            console.log('input has show-sign flag');
            if (value > 0){ text = '+' + text; }
            else if (value < 0){ text = '-' + text; }
            console.log('sign debug' + '\nvalue: ', typeof value, value, '\ntext: ', typeof text, text);
            }
        $value.text(text);
        };

    // Define a function that takes a given settings panel and binds relevant events to all range inputs
    var bindRangeInputEvents = function($settings){
        $('input[type="range"]', $settings).each(function(){
            var $input = $(this);
            var $parent = $input.closest('.subfield') || $input.closest('.field');
            var $label = $('label', $parent);
            if (!$label.length){ return; }
            var $value = $label.find('.value');
            if (!$value.length){ $value = $('<span class="value"></span>').appendTo($label); }
            var value = parseFloat($input.val());
            updateRangeLabelValue($input, $value, value);
            });
        $('input[type="range"]', $settings).bind('input change', function(e){
            //console.log('change event on a slider-based config field');
            var $input = $(this);
            var fieldName = $input.attr('name');
            var newValue = parseFloat($input.val());
            var $parent = $input.closest('.subfield') || $input.closest('.field');
            var $label = $('label', $parent);
            var $value = $label.find('.value');
            if (!$value.length){ return; }
            updateRangeLabelValue($input, $value, newValue);
            });
        };

    // Define a function that takes a given settings panel and binds relevant events to all radio inputs,
    // making it so clicking a radio button's container automatically triggers the radio button inside too
    var bindRadioInputEvents = function($settings){
        var $radioFields = $('.radiofield', $settings);
        $.each($radioFields, function(i, $radioField){
            var $radioFieldParent = $(this).closest('.subfield') || $(this).closest('.field');
            $('.radiofield', $radioFieldParent).bind('click', function(e){
                var $thisField = $(this);
                var $radioButton = $('input[type="radio"]', $thisField);
                $radioButton.prop('checked', true);
                $radioButton.trigger('change');
                });
            $('input[type="radio"]', $radioFieldParent).bind('change', function(e){
                e.stopPropagation();
                $radioFieldParent.find('.radiofield').removeClass('active');
                $('input[type="radio"]:checked', $radioFieldParent).closest('.radiofield').addClass('active');
                });
            });
        };


    // -- SETTINGS FUNCTIONALITY BY TAB -- //

    // Process complex player setting updates and pass them to the parent window
    var $proxySettings = $('.proxy-settings', $thisSettings);
    if ($proxySettings.length){
        //console.log('we have player settings specifically');

        // Collect a reference to the player avatar field so we can add events
        var $playerAvatarField = $('.field.player-avatar', $proxySettings);
        var $playerAvatarSelect = $('select', $playerAvatarField);
        var $playerAvatarPreview = $('.preview', $playerAvatarField);
        //console.log('$playerAvatarField =', $playerAvatarField.length, $playerAvatarField);
        //console.log('$playerAvatarSelect =', $playerAvatarSelect.length, $playerAvatarSelect);
        //console.log('$playerAvatarPreview =', $playerAvatarPreview.length, $playerAvatarPreview);

        // Define a function for uploading the player avatar preview
        function updatePlayerAvatarPreview(){
            var avatarName = $playerAvatarSelect.val();
            if (!avatarName || !avatarName.length){ avatarName = 'player'; }
            var avatarURL = 'images/players/'+avatarName+'/sprite_left_40x40.png?'+gameSettings.cacheTime;
            $playerAvatarPreview.find('.sprite').css({backgroundImage: 'url('+avatarURL+')'});
            }

        // Define onchange events for whenever the select changes
        $playerAvatarSelect.bind('change', function(e){
            updatePlayerAvatarPreview();
            });

        }

    // Process complex audio setting updates and pass them to the parent window
    var $audioSettings = $('.audio-settings', $thisSettings);
    if ($audioSettings.length){
        //console.log('we have game settings specifically');

        // Define change events for all the range elements so we can see their values
        bindRangeInputEvents($audioSettings);

        // Make it so radio button and their containers can be clicked interchangeably
        bindRadioInputEvents($audioSettings);

        // Collect references to the appropriate windows for updating
        var thisMusicWindow = window.top;
        var thisGameSettings = window.top.gameSettings;

        // ---

        // Collect references to the applicable form fields
        var $audioBalanceConfigField = $('.field[data-setting="audioBalanceConfig"]', $audioSettings);

        // Backup the user's audio changes in case we need to reset them
        var userAudioConfigBackup = {};
        userAudioConfigBackup = parseAudioBalanceConfig();

        // Define a function for parsing the audio balance config from the form
        function parseAudioBalanceConfig(){
            // collect refs to all three fields manually
            var $masterVolumeField = $('input[name="masterVolume"]', $audioBalanceConfigField);
            var $musicVolumeField = $('input[name="musicVolume"]', $audioBalanceConfigField);
            var $effectVolumeField = $('input[name="effectVolume"]', $audioBalanceConfigField);
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

        // Define a function for updating the audio balance config w/ form changes
        var prevMasterVolume = false;
        var prevMusicVolume = false;
        var prevEffectVolume = false;
        function updateAudioBalanceConfig(newConfig){
            //console.log('updateAudioBalanceConfig(newConfig) w/', newConfig);
            if (typeof newConfig !== 'object'){ return false; }
            var masterVolume = typeof newConfig.masterVolume === 'number' ? newConfig.masterVolume : thisGameSettings.masterVolume;
            var musicVolume = typeof newConfig.musicVolume === 'number' ? newConfig.musicVolume : thisGameSettings.musicVolume;
            var effectVolume = typeof newConfig.effectVolume === 'number' ? newConfig.effectVolume : thisGameSettings.effectVolume;
            if (masterVolume !== prevMasterVolume
                && typeof thisMusicWindow.mmrpg_master_volume !== 'undefined'){
                thisMusicWindow.mmrpg_master_volume(masterVolume);
                prevMasterVolume = masterVolume;
                }
            if (musicVolume !== prevMusicVolume
                && typeof thisMusicWindow.mmrpg_music_volume !== 'undefined'){
                thisMusicWindow.mmrpg_music_volume(musicVolume);
                prevMusicVolume = musicVolume;
                }
            if (effectVolume !== prevEffectVolume
                && typeof thisMusicWindow.mmrpg_sound_effect_volume !== 'undefined'){
                thisMusicWindow.mmrpg_sound_effect_volume(effectVolume, true);
                prevEffectVolume = effectVolume;
                }
            return true;
        }

        // Make it so that changing audio balance settings updates live w/ a sound effect
        $('input[type="range"]', $audioBalanceConfigField).bind('change', function(e){
            //console.log('change event on audio balance config field');
            var newConfig = parseAudioBalanceConfig();
            updateAudioBalanceConfig(newConfig);
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0}, true);
            });

        // ---

        // Reset back to backup values if the user switches windows without saving
        var resetGameSettings = function(){
            //console.log('resetGameSettings()');
            //console.log('userAudioConfigBackup = ', userAudioConfigBackup);
            updateAudioBalanceConfig(userAudioConfigBackup);
            };
        var applyGameSettings = function(){
            //console.log('applyGameSettings()');
            //console.log('parseAudioBalanceConfig() = ', parseAudioBalanceConfig());
            updateAudioBalanceConfig(parseAudioBalanceConfig());
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

        }

    // Process complex game setting updates and pass them to the parent window
    var $performanceSettings = $('.performance-settings', $thisSettings);
    if ($performanceSettings.length){
        //console.log('we have game settings specifically');

        // Define change events for all the range elements so we can see their values
        bindRangeInputEvents($performanceSettings);

        // Make it so radio button and their containers can be clicked interchange
        bindRadioInputEvents($performanceSettings);

        // Collect references to the appropriate windows for updating
        var thisMusicWindow = window.top;
        var thisSpriteWindow = window.self;
        var thisGameSettings = window.top.gameSettings;

        // ---

        // Collect references to the applicable form fields
        var $performanceTweaksField = $('.field[data-setting="performanceTweaks"]', $performanceSettings);

        // Backup this user's menu button sprite settings in case we need to reset them
        var userAllowMenuButtonSpritesBackup = false;
        var userMenuButtonSpriteMotionBackup = false;
        var userMenuButtonSpriteLimitBackup = 0;
        userAllowMenuButtonSpritesBackup = parseAllowMenuButtonSprites();
        userMenuButtonSpriteMotionBackup = parseMenuButtonSpriteMotion();
        userMenuButtonSpriteLimitBackup = parseMenuButtonSpriteLimit();

        // Define a function for parsing the value of the menu button sprite toggle
        function parseAllowMenuButtonSprites(){
            //console.log('parseAllowMenuButtonSprites()');
            var $checkedInput = $('input[type="radio"][name="allowMenuButtonSprites"]:checked', $performanceSettings);
            var checkedValue = parseInt($checkedInput.val()); // int-based boolean
            //console.log('checkedValue = ', checkedValue);
            return checkedValue;
            };

        // Define a function for parsing the value of the menu button sprite motion toggle
        function parseMenuButtonSpriteMotion(){
            //console.log('parseMenuButtonSpriteMotion()');
            var $checkedInput = $('input[type="radio"][name="menuButtonSpriteMotion"]:checked', $performanceSettings);
            var checkedValue = parseInt($checkedInput.val()); // int-based boolean
            //console.log('checkedValue = ', checkedValue);
            return checkedValue;
            };

        // Define a function for parsing the menu button sprite limit from the form
        function parseMenuButtonSpriteLimit(){
            //console.log('parseMenuButtonSpriteLimit()');
            var $sliderField = $('input[name="menuButtonSpriteLimit"]', $performanceTweaksField);
            var sliderValue = parseFloat($sliderField.val());
            //console.log('sliderValue = ', sliderValue);
            return sliderValue;
            };

        // Define a function for updating the menu button sprite toggle given a new value
        function updateAllowMenuButtonSprites(newToggle){
            //console.log('updateAllowMenuButtonSprites(newToggle) w/', newToggle);
            if (typeof newToggle !== 'number'){ return false; }
            var newToggleValue = newToggle;
            thisGameSettings.allowMenuButtonSprites = newToggleValue;
            if (newToggleValue){ $thisBody.addClass('allowMenuButtonSprites'); }
            else { $thisBody.removeClass('allowMenuButtonSprites'); }
            return true;
            };

        // Define a function for updating the menu button sprite motion toggle given a new value
        function updateMenuButtonSpriteMotion(newMotion){
            //console.log('updateMenuButtonSpriteMotion(newMotion) w/', newMotion);
            if (typeof newMotion !== 'number'){ return false; }
            var newMotionValue = newMotion;
            thisGameSettings.menuButtonSpriteMotion = newMotionValue;
            if (newMotionValue){ $thisBody.addClass('menuButtonSpriteMotion'); }
            else { $thisBody.removeClass('menuButtonSpriteMotion'); }
            return true;
            };

        // Define a function for updating the menu button sprite limit given a new value
        function updateMenuButtonSpriteLimit(newLimit){
            //console.log('updateMenuButtonSpriteLimit(newLimit) w/', newLimit);
            if (typeof newLimit !== 'number'){ return false; }
            var newSpriteLimit = newLimit;
            thisGameSettings.menuButtonSpriteLimit = newSpriteLimit;
            return true;
            };

        // Make sure any updates to these fields are correctly parsed and applied
        $('input[name="allowMenuButtonSprites"]', $performanceTweaksField).bind('change', function(e){
            updateAllowMenuButtonSprites(parseAllowMenuButtonSprites());
            });
        $('input[name="menuButtonSpriteMotion"]', $performanceTweaksField).bind('change', function(e){
            updateMenuButtonSpriteMotion(parseMenuButtonSpriteMotion());
            });
        $('input[name="menuButtonSpriteLimit"]', $performanceTweaksField).bind('change', function(e){
            updateMenuButtonSpriteLimit(parseMenuButtonSpriteLimit());
            });

        // ---

        // Collect references to the applicable form fields
        var $performanceTweaksField = $('.field[data-setting="performanceTweaks"]', $performanceSettings);

        // Backup the user's ready room sprite settings in case we need to reset them
        var userAllowReadyRoomSpritesBackup = false;
        var userReadyRoomSpriteMotionBackup = false;
        var userReadyRoomSpriteLimitBackup = 0;
        userAllowReadyRoomSpritesBackup = parseAllowReadyRoomSprites();
        userReadyRoomSpriteMotionBackup = parseReadyRoomSpriteMotion();
        userReadyRoomSpriteLimitBackup = parseReadyRoomSpriteLimit();

        // Define a function for parsing the value of the ready room sprite toggle
        function parseAllowReadyRoomSprites(){
            //console.log('parseAllowReadyRoomSprites()');
            var $checkedInput = $('input[type="radio"][name="allowReadyRoomSprites"]:checked', $performanceSettings);
            var checkedValue = parseInt($checkedInput.val()); // int-based boolean
            //console.log('checkedValue = ', checkedValue);
            return checkedValue;
            };

        // Define a function for parsing the value of the ready room sprite motion toggle
        function parseReadyRoomSpriteMotion(){
            //console.log('parseReadyRoomSpriteMotion()');
            var $checkedInput = $('input[type="radio"][name="readyRoomSpriteMotion"]:checked', $performanceSettings);
            var checkedValue = parseInt($checkedInput.val()); // int-based boolean
            //console.log('checkedValue = ', checkedValue);
            return checkedValue;
            };

        // Define a function for parsing the ready room sprite limit from the form
        function parseReadyRoomSpriteLimit(){
            //console.log('parseReadyRoomSpriteLimit()');
            var $sliderField = $('input[name="readyRoomSpriteLimit"]', $performanceTweaksField);
            var sliderValue = parseFloat($sliderField.val());
            //console.log('sliderValue = ', sliderValue);
            return sliderValue;
            };

        // Define a function for updating the ready room sprite toggle given a new value
        function updateAllowReadyRoomSprites(newToggle){
            //console.log('updateAllowReadyRoomSprites(newToggle) w/', newToggle);
            if (typeof newToggle !== 'number'){ return false; }
            var newToggleValue = newToggle;
            thisGameSettings.allowReadyRoomSprites = newToggleValue;
            if (newToggleValue){ $thisBody.addClass('allowReadyRoomSprites'); }
            else { $thisBody.removeClass('allowReadyRoomSprites'); }
            return true;
            };

        // Define a function for updating the ready room sprite motion toggle given a new value
        function updateReadyRoomSpriteMotion(newMotion){
            //console.log('updateReadyRoomSpriteMotion(newMotion) w/', newMotion);
            if (typeof newMotion !== 'number'){ return false; }
            var newMotionValue = newMotion;
            thisGameSettings.readyRoomSpriteMotion = newMotionValue;
            if (newMotionValue){ $thisBody.addClass('readyRoomSpriteMotion'); }
            else { $thisBody.removeClass('readyRoomSpriteMotion'); }
            return true;
            };

        // Define a function for updating the ready room sprite limit given a new value
        function updateReadyRoomSpriteLimit(newLimit){
            //console.log('updateReadyRoomSpriteLimit(newLimit) w/', newLimit);
            if (typeof newLimit !== 'number'){ return false; }
            var newSpriteLimit = newLimit;
            thisGameSettings.readyRoomSpriteLimit = newSpriteLimit;
            return true;
            };

        // Make sure any updates to these fields are correctly parsed and applied
        $('input[name="allowReadyRoomSprites"]', $performanceTweaksField).bind('change', function(e){
            updateAllowReadyRoomSprites(parseAllowReadyRoomSprites());
            });
        $('input[name="readyRoomSpriteMotion"]', $performanceTweaksField).bind('change', function(e){
            updateReadyRoomSpriteMotion(parseReadyRoomSpriteMotion());
            });
        $('input[name="readyRoomSpriteLimit"]', $performanceTweaksField).bind('change', function(e){
            updateReadyRoomSpriteLimit(parseReadyRoomSpriteLimit());
            });

        // ---

        // Collect references to the applicable form fields
        var $spriteRenderModeField = $('.field[data-setting="spriteRenderMode"]', $performanceSettings);

        // Backup the user's sprite render mode in case we need to reset it
        var userSpriteRenderModeBackup = '';
        userSpriteRenderModeBackup = parseSpriteRenderMode();

        // Define a function for parsing the sprite render mode setting from the form
        function parseSpriteRenderMode(){
            //console.log('parseSpriteRenderMode()');
            var $checkedInput = $('input[type="radio"]:checked', $spriteRenderModeField);
            var checkedValue = $checkedInput.val();
            //console.log('checkedValue = ', checkedValue);
            return checkedValue;
            };

        // Define a function for updating the sprite rendering mode w/ form changes
        function updateSpriteRenderMode(newMode){
            //console.log('updateSpriteRenderMode(newMode) w/', newMode);
            if (typeof newMode !== 'string'){ return false; }
            var oldRenderMode = thisGameSettings.spriteRenderMode;
            var newRenderMode = newMode.length ? newMode : thisGameSettings.spriteRenderMode;
            thisGameSettings.spriteRenderMode = newRenderMode;
            $thisBody.removeClassByRegex(/^spriteRenderMode_/);
            $thisBody.addClass('spriteRenderMode_'+newRenderMode);
            return true;
            };

        // Make sure any updates to these fields are correctly parsed and applied
        $('input[type="radio"]', $spriteRenderModeField).bind('change', function(e){
            //console.log('change event on spriteRenderMode field');
            updateSpriteRenderMode(parseSpriteRenderMode());
            });

        // ---

        // Reset back to backup values if the user switches windows without saving
        var resetGameSettings = function(){
            //console.log('resetGameSettings()');
            //console.log('userAllowMenuButtonSpritesBackup = ', userAllowMenuButtonSpritesBackup);
            //console.log('userMenuButtonSpriteMotionBackup = ', userMenuButtonSpriteMotionBackup);
            //console.log('userMenuButtonSpriteLimitBackup = ', userMenuButtonSpriteLimitBackup);
            //console.log('userAllowReadyRoomSpritesBackup = ', userAllowReadyRoomSpritesBackup);
            //console.log('userReadyRoomSpriteMotionBackup = ', userReadyRoomSpriteMotionBackup);
            //console.log('userReadyRoomSpriteLimitBackup = ', userReadyRoomSpriteLimitBackup);
            //console.log('userSpriteRenderModeBackup = ', userSpriteRenderModeBackup);
            updateAllowMenuButtonSprites(userAllowMenuButtonSpritesBackup);
            updateMenuButtonSpriteMotion(userMenuButtonSpriteMotionBackup);
            updateMenuButtonSpriteLimit(userMenuButtonSpriteLimitBackup);
            updateAllowReadyRoomSprites(userAllowReadyRoomSpritesBackup);
            updateReadyRoomSpriteMotion(userReadyRoomSpriteMotionBackup);
            updateReadyRoomSpriteLimit(userReadyRoomSpriteLimitBackup);
            updateSpriteRenderMode(userSpriteRenderModeBackup);
            };
        var applyGameSettings = function(){
            //console.log('applyGameSettings()');
            //console.log('parseAllowMenuButtonSprites() = ', parseAllowMenuButtonSprites());
            //console.log('parseMenuButtonSpriteMotion() = ', parseMenuButtonSpriteMotion());
            //console.log('parseMenuButtonSpriteLimit() = ', parseMenuButtonSpriteLimit());
            //console.log('parseAllowReadyRoomSprites() = ', parseAllowReadyRoomSprites());
            //console.log('parseReadyRoomSpriteMotion() = ', parseReadyRoomSpriteMotion());
            //console.log('parseReadyRoomSpriteLimit() = ', parseReadyRoomSpriteLimit());
            //console.log('parseSpriteRenderMode() = ', parseSpriteRenderMode());
            updateAllowMenuButtonSprites(parseAllowMenuButtonSprites());
            updateMenuButtonSpriteMotion(parseMenuButtonSpriteMotion());
            updateMenuButtonSpriteLimit(parseMenuButtonSpriteLimit());
            updateAllowReadyRoomSprites(parseAllowReadyRoomSprites());
            updateReadyRoomSpriteMotion(parseReadyRoomSpriteMotion());
            updateReadyRoomSpriteLimit(parseReadyRoomSpriteLimit());
            updateSpriteRenderMode(parseSpriteRenderMode());
            };
        window.addEventListener('message', function(event){
            //console.log('iframe received a message from', event.origin);
            // IMPORTANT: Check the origin of the data!
            if (event.origin.startsWith(performanceSettings.baseHref)){
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
        updateAllowMenuButtonSprites(parseAllowMenuButtonSprites());
        updateMenuButtonSpriteMotion(parseMenuButtonSpriteMotion());
        updateMenuButtonSpriteLimit(parseMenuButtonSpriteLimit());
        updateAllowReadyRoomSprites(parseAllowReadyRoomSprites());
        updateReadyRoomSpriteMotion(parseReadyRoomSpriteMotion());
        updateReadyRoomSpriteLimit(parseReadyRoomSpriteLimit());
        updateSpriteRenderMode(parseSpriteRenderMode());

        }

    // Process complex game setting updates and pass them to the parent window
    var $miscSettings = $('.misc-settings', $thisSettings);
    if ($miscSettings.length){
        //console.log('we have game settings specifically');

        // Define change events for all the range elements so we can see their values
        bindRangeInputEvents($miscSettings);

        // Make it so radio button and their containers can be clicked interchangeably
        bindRadioInputEvents($miscSettings);

        // Collect references to the appropriate windows for updating
        var thisSpriteWindow = window.self;
        var thisGameSettings = window.top.gameSettings;

        // ---

        // Collect references to the applicable form fields
        var $battleButtonModeField = $('.field[data-setting="battleButtonMode"]', $miscSettings);

        // Backup the user's battle button mode in case we need to reset it
        var userBattleButtonModeBackup = '';
        userBattleButtonModeBackup = parseBattleButtonMode();

        // Define a function for parsing the battle button mode setting from the form
        function parseBattleButtonMode(){
            var $checkedInput = $('input[type="radio"]:checked', $battleButtonModeField);
            var checkedValue = $checkedInput.val();
            return checkedValue;
            };

        // Define a function for updating the battle button mode w/ form changes
        function updateBattleButtonMode(newMode, saveChanges){
            //console.log('updateBattleButtonMode(newMode) w/', newMode);
            if (typeof newMode !== 'string'){ return false; }
            if (typeof saveChanges === 'undefined'){ saveChanges = false; }
            var oldButtonMode = thisGameSettings.battleButtonMode;
            var newButtonMode = newMode.length ? newMode : thisGameSettings.battleButtonMode;
            thisGameSettings.battleButtonMode = newButtonMode;
            $thisBody.removeClassByRegex(/^battleButtonMode_/);
            $thisBody.addClass('battleButtonMode_'+newButtonMode);
            if (typeof window.parent.prototype_update_game_settings !== 'undefined'){
                //console.log('sending update request to parent prototype_update_game_settings() w/ '+newButtonMode);
                window.parent.prototype_update_game_settings({'battleButtonMode': newButtonMode}, saveChanges);
                }
            return true;
            };

        // Make sure any updates to these fields are correctly parsed and applied
        $('input[type="radio"]', $battleButtonModeField).bind('change', function(e){
            console.log('change event on battleButtonMode field');
            updateBattleButtonMode(parseBattleButtonMode());
            });

        // ---

        // Reset back to backup values if the user switches windows without saving
        var resetGameSettings = function(){
            //console.log('resetGameSettings()');
            //console.log('userBattleButtonModeBackup = ', userBattleButtonModeBackup);
            updateBattleButtonMode(userBattleButtonModeBackup);
            };
        var applyGameSettings = function(){
            //console.log('applyGameSettings()');
            //console.log('parseBattleButtonMode() = ', parseBattleButtonMode());
            updateBattleButtonMode(parseBattleButtonMode());
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
        updateBattleButtonMode(parseBattleButtonMode(), true);

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