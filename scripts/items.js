// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisItem = false;
var thisItemData = {itemTotal:0,itemQuantities:{},allowEdit:true};
var thisScrollbarSettings = {wheelSpeed:0.3,suppressScrollX:true,scrollYMarginOffset:6};
var resizePlayerWrapper = function(){};
$(document).ready(function(){

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);
    thisItem = $('#item', thisBody);

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the items menu
    var thisContext = $('#item');
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

        // ITEM INVENTORY LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#console .event .item_name[data-click-tooltip]', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        /*
        $('#console .event .item_name[data-click-tooltip]', thisContext).live('click', function(){
            // [tooltip takes care of this one]
            });
        */

        }

    // -- PRIMARY SCRIPT FUNCTIONALITY -- //

    // Attach tab events to any item tabs so that we can switch between selling/buying
    $('.item_tabs_links .tab_link[data-tab]', gameConsole).live('click', function(e){
        e.preventDefault();
        if (!thisItemData.allowEdit){ return false; }
        var thisTab = $(this);
        var thisTabToken = thisTab.attr('data-tab');
        var tabLinkBlock = thisTab.parent();
        var eventContainer = tabLinkBlock.parent();
        var tabContainerBlock = $('.item_tabs_containers', eventContainer);
        var thiContainer = $('.tab_container[data-tab='+thisTabToken+']', eventContainer);
        $('.item_tabs_links .tab_link[data-tab]', gameConsole).removeClass('tab_link_active');
        thisTab.addClass('tab_link_active');
        $('.item_tabs_containers .tab_container[data-tab]', gameConsole).removeClass('tab_container_active');
        thiContainer.addClass('tab_container_active');
        var thisConfirmCell = thiContainer.find('.item_cell_confirm');
        thisConfirmCell.attr('data-kind', '').attr('data-action', '').attr('data-token', '').attr('data-price', '').attr('data-quantity', '');
        thisConfirmCell.empty().html('<div class="placeholder">&hellip;</div>');
        //console.log('updating perfect scrollbar 2');
        $('#console .scroll_wrapper', thisItem).perfectScrollbar('update');
        return true;
        });

    // Append the markup after load to prevent halting display and waiting items
    $('#console #items').append(itemConsoleMarkup);

    // Attach the scrollbar to the battle events container
    $('#console .scroll_wrapper', thisItem).perfectScrollbar(thisScrollbarSettings);

    //console.log('updating perfect scrollbar 3');
    $('#console .scroll_wrapper', thisItem).perfectScrollbar('update');

    // Automatically click the first item link
    $('.item_tabs_links .tab_link[data-tab]', gameConsole).first().trigger('click');

    // Make sure we auto-click the portait sprite whenever a cell is clicked
    let $consoleSprite = $('.event .this_sprite', gameConsole);
    let consoleSpriteTimeout = null;
    $('.event tbody td[data-kind!=""]', gameConsole).bind('click', function(){
        $consoleSprite.addClass('hovered');
        if (consoleSpriteTimeout){ clearTimeout(consoleSpriteTimeout); }
        consoleSpriteTimeout = setTimeout(function(){
            $consoleSprite.removeClass('hovered');
            }, 1000);
        });

    // Make sure hover classes are only ever on one cell at a time
    $('.event tbody td[data-kind]', gameConsole).bind('mouseenter', function(){
        let $thisCell = $(this);
        let $parentBody = $thisCell.closest('tbody');
        $('.event tbody td[data-kind]', gameConsole).removeClass('hovered');
        $thisCell.addClass('hovered');
        });
    $('.event tbody td[data-kind]', gameConsole).bind('mouseleave', function(){
        let $thisCell = $(this);
        $thisCell.removeClass('hovered');
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

    // Fade in the leaderboard screen slowly
    thisBody.waitForImages(function(){
        var tempTimeout = setTimeout(function(){
            if (gameSettings.fadeIn){ thisBody.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, 800, 'swing'); }
            else { thisBody.removeClass('hidden').css({opacity:1}); }
            //console.log('updating perfect scrollbar 4');
            $('#console .scroll_wrapper', thisItem).perfectScrollbar('update');
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

// Define a item-specific function to call when polling user input variables
function checkUserInputsForItemsFrame(kind, event, activeInputs, userInputs){
    //console.log('%c' + 'prototypeReady.checkUserInputsForItemsFrame()', 'color: magenta;');
    let _self = this;
    let $thisPrototype = $mmrpgElements.thisPrototype;
    let playSoundEffect = mmrpgPrototype.playSoundEffect;
    //console.log('-> playSoundEffect:', typeof playSoundEffect, playSoundEffect);
    //console.log('-> $thisPrototype:', typeof $thisPrototype, $thisPrototype);

    // Collect references to available panels, tabs, and buttons before starting
    let $thisItems = $('#item', $thisPrototype);
    let $thisItemsConsole = $('#console', $thisItems);
    //let $availablePanels = $('#links .wrapper[data-item]', $thisItemsCanvas);
    //let $availableTabs = $('#items .event_visible .tab_link', $thisItemsConsole);
    //console.log('-> $thisItems:', ($thisItems ? $thisItems.length : 0), typeof $thisItems, $thisItems);
    //console.log('-> $thisItemsConsole:', ($thisItemsConsole ? $thisItemsConsole.length : 0), typeof $thisItemsConsole, $thisItemsConsole);
    //console.log('-> $availablePanels:', ($availablePanels ? $availablePanels.length : 0), typeof $availablePanels, $availablePanels);
    //console.log('-> $availableTabs:', ($availableTabs ? $availableTabs.length : 0), typeof $availableTabs, $availableTabs);

    // COMMON CONTROLS: Try to keep these consistent!

    /*
    // If the user pressed the X button, we should scroll through visible panels
    // ITEMS NOTE: nothing to click, there's only one visible panel!!!
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
    // ITEMS NOTE: nothing to click, there's only one visible tab!!!
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

    // ITEM CONTROLS: These controls only apply to the item menu

    // Collect refs to important elements we'll be checking below
    let $activePanel, $activeTab, activePanelToken, activeTabToken;
    $activePanel = $('#items .event_visible[data-token]', $thisItemsConsole);
    $activeTab = $('.item_tabs_containers .tab_container_active', $activePanel);
    activePanelToken = $activePanel && $activePanel.length ? $activePanel.attr('data-token') : false;
    activeTabToken = $activeTab && $activeTab.length ? $activeTab.attr('data-tab') : false;
    //console.log('-> $activePanel:', ($activePanel ? $activePanel.length : 0), typeof $activePanel, $activePanel);
    //console.log('-> $activeTab:', ($activeTab ? $activeTab.length : 0), typeof $activeTab, $activeTab);
    //console.log('-> activePanelToken:', typeof activePanelToken, activePanelToken);
    //console.log('-> activeTabToken:', typeof activeTabToken, activeTabToken);
    let $availableItemCells, $activeItemCell;
    $availableItemCells = $('.item_cell:not([data-kind=""])', $activeTab); if (!$availableItemCells || !$availableItemCells.length){ $availableItemCells = null; }
    $activeItemCell = $availableItemCells ? $availableItemCells.filter('.hovered') : null; if (!$activeItemCell || !$activeItemCell.length){ $activeItemCell = null; }
    //console.log('-> $availableItemCells:', ($availableItemCells ? $availableItemCells.length : 0), typeof $availableItemCells, $availableItemCells);
    //console.log('-> $activeItemCell:', ($activeItemCell ? $activeItemCell.length : 0), typeof $activeItemCell, $activeItemCell);

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
        // up/down/left/right navigate 2 columns of item cells
        closeTooltipFunction();
        let $nextItemCell;
        if (!$activeItemCell || !$activeItemCell.length){
            if (whichDirection === 'down' || whichDirection === 'right'){ $nextItemCell = $availableItemCells.first(); }
            else if (whichDirection === 'up' || whichDirection === 'left'){ $nextItemCell = $availableItemCells.last(); }
            } else {
            let activeItemCellIndex = $availableItemCells.index($activeItemCell);
            let maxItemCellIndex = $availableItemCells.length - 1;
            //console.log('activeItemCellIndex =', activeItemCellIndex);
            //console.log('maxItemCellIndex =', maxItemCellIndex);
            let nextItemCellIndex = activeItemCellIndex;
            let verticalSkipAmount = gameSettings.wideModeActive ? 4 : 2;
            if (whichDirection === 'right'){ nextItemCellIndex += 1; }
            else if (whichDirection === 'left'){ nextItemCellIndex -= 1; }
            else if (whichDirection === 'down'){ nextItemCellIndex += verticalSkipAmount; }
            else if (whichDirection === 'up'){ nextItemCellIndex -= verticalSkipAmount; }
            //console.log('nextItemCellIndex(A) =', nextItemCellIndex);
            if (nextItemCellIndex < 0){ nextItemCellIndex = maxItemCellIndex; }
            else if (nextItemCellIndex > maxItemCellIndex){ nextItemCellIndex = 0;}
            //console.log('nextItemCellIndex(B) =', nextItemCellIndex);
            $nextItemCell = $availableItemCells.eq(nextItemCellIndex);
            }
        if ($nextItemCell && $nextItemCell.length){
            $availableItemCells.removeClass('hovered');
            $nextItemCell.addClass('hovered');
            let $scrollWrapper = $nextItemCell.closest('.scroll_wrapper');
            if ($scrollWrapper.length){
                let containerTop = $scrollWrapper.offset().top;
                let containerBottom = containerTop + $scrollWrapper.height();
                let elemTop = $nextItemCell.offset().top;
                let elemBottom = elemTop + $nextItemCell.outerHeight();
                if (elemTop < containerTop){ $scrollWrapper.scrollTop($scrollWrapper.scrollTop() - (containerTop - elemTop)); }
                else if (elemBottom > containerBottom){ $scrollWrapper.scrollTop($scrollWrapper.scrollTop() + (elemBottom - containerBottom)); }
                if (typeof $scrollWrapper.perfectScrollbar === 'function'){ $scrollWrapper.perfectScrollbar('update'); }
                return;
                }
            }
        return;
        }

    // Define a quick function for hovering + clicking a given item cell button
    let hoverClickCell = function($cell, mouseLeave){
        mouseLeave = typeof mouseLeave === 'boolean' ? mouseLeave : true;
        let $parent = $cell.closest('table');
        $('td[data-kind]', $parent).removeClass('clicked');
        $cell.addClass('clicked');
        $cell.trigger('mouseenter');
        $cell.trigger('click');
        if (typeof userInputs.hoverClickCellTimeout !== 'undefined'){ clearTimeout(userInputs.hoverClickCellTimeout); }
        userInputs.hoverClickCellTimeout = setTimeout(function(){
            $('td[data-kind]', $parent).removeClass('clicked');
            if (mouseLeave){ $cell.trigger('mouseleave'); }
            $cell.removeClass('clicked');
            }, 100);
        };

    // Define a quick function for hovering + clicking a given item cell button
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

    // If the user pressed the Y button, we should try to click any tooltip buttons
    if (activeInputs.Y){
        //console.log('%c' + 'Y button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        //console.log('we are in browsing mode!');
        let $tooltipButton = $activeItemCell ? $('[data-click-tooltip]', $activeItemCell) : null;
        //console.log('-> $tooltipButton:', ($tooltipButton ? $tooltipButton.length : 0), typeof $tooltipButton, $tooltipButton);
        if (!$tooltipButton || !$tooltipButton.length){ return; }
        hoverClickCellButton($tooltipButton, false);
        return;
        }

    // If the user pressed the A button, we should click the cell directly
    if (activeInputs.A){
        //console.log('%c' + 'Y button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        //console.log('we are in browsing mode!');
        let $tooltipCell = $activeItemCell ? $activeItemCell : null;
        //console.log('-> $tooltipCell:', ($tooltipCell ? $tooltipCell.length : 0), typeof $tooltipCell, $tooltipCell);
        if (!$tooltipCell || !$tooltipCell.length){ return; }
        hoverClickCell($tooltipCell, false);
        return;
        }

}