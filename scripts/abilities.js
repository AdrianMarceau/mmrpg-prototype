// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisAbility = false;
var thisAbilityData = {abilityTotal:0,abilityQuantities:{},allowEdit:true};
var thisScrollbarSettings = {wheelSpeed:0.3,suppressScrollX:true,scrollYMarginOffset:6};
var resizePlayerWrapper = function(){};
$(document).ready(function(){

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);
    thisAbility = $('#ability', thisBody);

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the abilities menu
    var thisContext = $('#ability');
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

        // ABILITY INVENTORY LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#console .event .ability_name[data-click-tooltip],', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        /*
        $('#console .event .ability_name[data-click-tooltip]', thisContext).live('click', function(){
            // [tooltip takes care of this one]
            });
        */

        }

    // -- PRIMARY SCRIPT FUNCTIONALITY -- //

    // Attach tab events to any ability tabs so that we can switch between selling/buying
    $('.ability_tabs_links .tab_link[data-tab]', gameConsole).live('click', function(e){
        e.preventDefault();
        if (!thisAbilityData.allowEdit){ return false; }
        var thisTab = $(this);
        var thisTabToken = thisTab.attr('data-tab');
        var tabLinkBlock = thisTab.parent();
        var eventContainer = tabLinkBlock.parent();
        var tabContainerBlock = $('.ability_tabs_containers', eventContainer);
        var thiContainer = $('.tab_container[data-tab='+thisTabToken+']', eventContainer);
        $('.ability_tabs_links .tab_link[data-tab]', gameConsole).removeClass('tab_link_active');
        thisTab.addClass('tab_link_active');
        $('.ability_tabs_containers .tab_container[data-tab]', gameConsole).removeClass('tab_container_active');
        thiContainer.addClass('tab_container_active');
        var thisConfirmCell = thiContainer.find('.ability_cell_confirm');
        thisConfirmCell.attr('data-kind', '').attr('data-action', '').attr('data-token', '').attr('data-price', '').attr('data-quantity', '');
        thisConfirmCell.empty().html('<div class="placeholder">&hellip;</div>');
        //console.log('updating perfect scrollbar 2');
        $('#console .scroll_wrapper', thisAbility).perfectScrollbar('update');
        return true;
        });

    // Append the markup after load to prevent halting display and waiting abilities
    $('#console #abilities').append(abilityConsoleMarkup);

    // Attach the scrollbar to the battle events container
    $('#console .scroll_wrapper', thisAbility).perfectScrollbar(thisScrollbarSettings);

    //console.log('updating perfect scrollbar 3');
    $('#console .scroll_wrapper', thisAbility).perfectScrollbar('update');

    // Automatically click the first shop link
    $('.ability_tabs_links .tab_link[data-tab]', gameConsole).first().trigger('click');


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

    // Fade in the leaderboard screen slowly
    thisBody.waitForImages(function(){
        var tempTimeout = setTimeout(function(){
            if (gameSettings.fadeIn){ thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
            else { thisBody.removeClass('hidden').css({opacity:1}); }
            //console.log('updating perfect scrollbar 4');
            $('#console .scroll_wrapper', thisAbility).perfectScrollbar('update');
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
    var newScrollWrapperHeight = newFrameHeight - 142;

    if (windowWidth > 800){ thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
    else { thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }

    //console.log('windowWidth = '+windowWidth+'; parentWidth = '+parentWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}

// Define a function for printing a number with commas as thousands separators
function printNumberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Define a ability-specific function to call when polling user input variables
function checkUserInputsForAbilitiesFrame(kind, event, activeInputs, userInputs){
    //console.log('%c' + 'prototypeReady.checkUserInputsForAbilitiesFrame()', 'color: magenta;');
    let _self = this;
    let $thisPrototype = $mmrpgElements.thisPrototype;
    let playSoundEffect = mmrpgPrototype.playSoundEffect;
    //console.log('-> playSoundEffect:', typeof playSoundEffect, playSoundEffect);
    //console.log('-> $thisPrototype:', typeof $thisPrototype, $thisPrototype);

    // Collect references to available panels, tabs, and buttons before starting
    let $thisAbilities = $('#ability', $thisPrototype);
    let $thisAbilitiesConsole = $('#console', $thisAbilities);
    //let $availablePanels = $('#links .wrapper[data-ability]', $thisAbilitiesCanvas);
    //let $availableTabs = $('#abilities .event_visible .tab_link', $thisAbilitiesConsole);
    //console.log('-> $thisAbilities:', ($thisAbilities ? $thisAbilities.length : 0), typeof $thisAbilities, $thisAbilities);
    //console.log('-> $availablePanels:', ($availablePanels ? $availablePanels.length : 0), typeof $availablePanels, $availablePanels);
    //console.log('-> $availableTabs:', ($availableTabs ? $availableTabs.length : 0), typeof $availableTabs, $availableTabs);

    // COMMON CONTROLS: Try to keep these consistent!

    /*
    // If the user pressed the X button, we should scroll through visible panels
    if (activeInputs.X){
        //console.log('%c' + 'X button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        let $activePanel = $availablePanels.filter('.wrapper_active');
        let activePanelIndex = $activePanel && $activePanel.length ? $availablePanels.index($activePanel) : -1;
        let maxPanelIndex = $availablePanels.length - 1;
        let nextPanelIndex = activePanelIndex + 1;
        if (nextPanelIndex > maxPanelIndex){ nextPanelIndex = 0; }
        let $nextPanel = $availablePanels.eq(nextPanelIndex);
        if ($nextPanel && $nextPanel.length){
            $nextPanel.trigger('mouseenter');
            $nextPanel.trigger('click');
            }
        return;
        }
    */

    /*
    // If the user pressed the L2/R2 button2, we should scroll through visible tabs
    if (activeInputs.L2 || activeInputs.R2){
        //console.log('%c' + (activeInputs.L2 ? 'L2' : 'L1') + ' trigger button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        let $activeTab = $availableTabs.filter('.tab_link_active');
        let activeTabIndex = $activeTab && $activeTab.length ? $availableTabs.index($activeTab) : -1;
        let maxTabIndex = $availableTabs.length - 1;
        let nextTabIndex = activeTabIndex + (activeInputs.L2 ? -1 : 1);
        if (nextTabIndex > maxTabIndex){ nextTabIndex = 0; }
        let $nextTab = $availableTabs.eq(nextTabIndex);
        if ($nextTab && $nextTab.length){
            $nextTab.trigger('mouseenter');
            $nextTab.trigger('click');
            }
        return;
        }
    */

    // ABILITY CONTROLS: These controls only apply to the ability menu

    // Collect refs to important elements we'll be checking below
    let $activePanel, $activeTab, activePanelToken, activeTabToken;
    $activePanel = $('#abilities .event_visible[data-token]', $thisAbilitiesConsole);
    $activeTab = $('.ability_tabs_containers .tab_container_active', $activePanel);
    activePanelToken = $activePanel && $activePanel.length ? $activePanel.attr('data-token') : false;
    activeTabToken = $activeTab && $activeTab.length ? $activeTab.attr('data-tab') : false;
    //console.log('-> $activePanel:', ($activePanel ? $activePanel.length : 0), typeof $activePanel, $activePanel);
    //console.log('-> $activeTab:', ($activeTab ? $activeTab.length : 0), typeof $activeTab, $activeTab);
    //console.log('-> activePanelToken:', typeof activePanelToken, activePanelToken);
    //console.log('-> activeTabToken:', typeof activeTabToken, activeTabToken);
    let $availableAbilityCells, $activeAbilityCell;
    $availableAbilityCells = $('.ability_cell:not([data-kind=""])', $activeTab); if (!$availableAbilityCells || !$availableAbilityCells.length){ $availableAbilityCells = null; }
    $activeAbilityCell = $availableAbilityCells ? $availableAbilityCells.filter('.hovered') : null; if (!$activeAbilityCell || !$activeAbilityCell.length){ $activeAbilityCell = null; }
    //console.log('-> $availableAbilityCells:', ($availableAbilityCells ? $availableAbilityCells.length : 0), typeof $availableAbilityCells, $availableAbilityCells);
    //console.log('-> $activeAbilityCell:', ($activeAbilityCell ? $activeAbilityCell.length : 0), typeof $activeAbilityCell, $activeAbilityCell);

    let closeTooltipFunction;
    if (typeof window.mmrpgCloseTooltipFunction !== 'undefined'){ closeTooltipFunction = window.mmrpgCloseTooltipFunction; }
    else { closeTooltipFunction = function(){ $('#mmrpg-tooltip').empty(); }; }

    // If the user pressed a directional button, we should try to scroll through pages
    if (activeInputs.Up || activeInputs.Down
        || activeInputs.Left || activeInputs.Right){
        let whichDirection = activeInputs.Up ? 'up' : activeInputs.Down ? 'down' : activeInputs.Left ? 'left' : activeInputs.Right ? 'right' : '';
        //console.log('%c' + 'Directional button (' + whichDirection.toUpperCase() + ') pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        //console.log('we are in browsing mode!');
        // up/down/left/right navigate 2 columns of ability cells
        closeTooltipFunction();
        let $nextAbilityCell;
        if (!$activeAbilityCell || !$activeAbilityCell.length){
            if (whichDirection === 'down' || whichDirection === 'right'){ $nextAbilityCell = $availableAbilityCells.first(); }
            else if (whichDirection === 'up' || whichDirection === 'left'){ $nextAbilityCell = $availableAbilityCells.last(); }
            } else {
            let activeAbilityCellIndex = $availableAbilityCells.index($activeAbilityCell);
            let maxAbilityCellIndex = $availableAbilityCells.length - 1;
            //console.log('activeAbilityCellIndex =', activeAbilityCellIndex);
            //console.log('maxAbilityCellIndex =', maxAbilityCellIndex);
            let nextAbilityCellIndex = activeAbilityCellIndex;
            if (whichDirection === 'right'){ nextAbilityCellIndex += 1; }
            else if (whichDirection === 'left'){ nextAbilityCellIndex -= 1; }
            else if (whichDirection === 'down'){ nextAbilityCellIndex += 2; }
            else if (whichDirection === 'up'){ nextAbilityCellIndex -= 2; }
            //console.log('nextAbilityCellIndex(A) =', nextAbilityCellIndex);
            if (nextAbilityCellIndex < 0){ nextAbilityCellIndex = maxAbilityCellIndex; }
            else if (nextAbilityCellIndex > maxAbilityCellIndex){ nextAbilityCellIndex = 0;}
            //console.log('nextAbilityCellIndex(B) =', nextAbilityCellIndex);
            $nextAbilityCell = $availableAbilityCells.eq(nextAbilityCellIndex);
            }
        if ($nextAbilityCell && $nextAbilityCell.length){
            $availableAbilityCells.removeClass('hovered');
            $nextAbilityCell.addClass('hovered');
            let $scrollWrapper = $nextAbilityCell.closest('.scroll_wrapper');
            if ($scrollWrapper.length){
                let containerTop = $scrollWrapper.offset().top;
                let containerBottom = containerTop + $scrollWrapper.height();
                let elemTop = $nextAbilityCell.offset().top;
                let elemBottom = elemTop + $nextAbilityCell.outerHeight();
                if (elemTop < containerTop){ $scrollWrapper.scrollTop($scrollWrapper.scrollTop() - (containerTop - elemTop)); }
                else if (elemBottom > containerBottom){ $scrollWrapper.scrollTop($scrollWrapper.scrollTop() + (elemBottom - containerBottom)); }
                if (typeof $scrollWrapper.perfectScrollbar === 'function'){ $scrollWrapper.perfectScrollbar('update'); }
                return;
                }
            }

        return;
        }

    // Define a quick function for hovering + clicking a given ability cell button
    let hoverClickCellButton = function($button, mouseLeave){
        mouseLeave = typeof mouseLeave === 'boolean' ? mouseLeave : true;
        let $parent = $button.closest('td[data-kind]');
        $('.button', $parent).removeClass('hovered');
        $button.addClass('hovered');
        $button.trigger('mouseenter');
        $button.trigger('click');
        if (typeof userInputs.hoverClickButtonTimeout !== 'undefined'){ clearTimeout(userInputs.hoverClickButtonTimeout); }
        userInputs.hoverClickButtonTimeout = setTimeout(function(){
            $('.button', $parent).removeClass('hovered');
            if (mouseLeave){ $button.trigger('mouseleave'); }
            $button.removeClass('hovered');
            }, 100);
        };

    // If the user pressed the Y button, we should ?????
    if (activeInputs.Y){
        //console.log('%c' + 'Y button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        //console.log('we are in browsing mode!');
        let $tooltipButton = $activeAbilityCell ? $('span[data-click-tooltip]', $activeAbilityCell) : null;
        //console.log('-> $tooltipButton:', ($tooltipButton ? $tooltipButton.length : 0), typeof $tooltipButton, $tooltipButton);
        if (!$tooltipButton || !$tooltipButton.length){ return; }
        hoverClickCellButton($tooltipButton, false);
        return;
        }

}