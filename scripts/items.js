// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisItem = false;
var thisItemData = {itemTotal:0,itemQuantities:{},allowEdit:true};
var resizePlayerWrapper = function(){};
$(document).ready(function(){

    // Update global reference variables
    thisBody = $('#mmrpg');
    thisPrototype = $('#prototype', thisBody);
    thisWindow = $(window);
    thisItem = $('#item', thisBody);

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
    $('#console .scroll_wrapper', thisItem).perfectScrollbar({suppressScrollX: true, scrollYMarginOffset: 6});

    //console.log('updating perfect scrollbar 3');
    $('#console .scroll_wrapper', thisItem).perfectScrollbar('update');

    // Automatically click the first shop link
    $('.item_tabs_links .tab_link[data-tab]', gameConsole).first().trigger('click');

    // Update all the item cells automatically
    //updateItemCells();


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

// Define a function for refreshing all items
function updateItemCells(){
    var itemCells = $('.item_cell[data-token]', gameConsole);
    //console.log('updateItems('+itemCells.length+')');
    //console.log('thisItemData', thisItemData);
    itemCells.each(function(index, value){ $(this).removeClass('item_cell_disabled'); });
    updateItemQuantities();
}

// Define a function for updating item quantities
function updateItemQuantities(){
    //console.log('updateItemQuantities()');
    for (var itemToken in thisItemData.itemQuantities){
        var itemQuantity = thisItemData.itemQuantities[itemToken];
        updateItemQuantity(itemToken, itemQuantity);
    }
}
// Define a function for updating a single item's quantity
function updateItemQuantity(itemToken, itemQuantity){
    var itemCells = $('.item_cell[data-token='+itemToken+']', gameConsole);
    itemCells.each(function(index, value){
        var thisCell = $(this);
        var thisKind = thisCell.attr('data-kind');
        var thisAction = thisCell.attr('data-action');
        //console.log('updateItemQuantity('+thisKind+' / '+thisAction+' / '+itemToken+' / '+itemQuantity+' / '+(thisCell.hasClass('item_cell_disabled') ? 'item_cell_disabled' : 'item_cell_enabled')+')');
        if (thisKind == 'item'){

            thisCell.find('label[data-quantity]').attr('data-quantity', itemQuantity).html('x '+itemQuantity);
            if (thisAction == 'buy' && itemQuantity >= 99){ thisCell.addClass('item_cell_disabled');  }
            else if (thisAction == 'sell' && itemQuantity <= 0){ thisCell.addClass('item_cell_disabled');  }

            } else if (thisKind == 'ability'){

            thisCell.find('label[data-quantity]').attr('data-quantity', itemQuantity).html('&nbsp;');
            if (itemQuantity < 0){ thisCell.addClass('item_cell_disabled').find('label[data-quantity]').html('&nbsp;'); }
            else if (itemQuantity >= 1){ thisCell.addClass('item_cell_disabled').find('label[data-quantity]').html('&#10004;'); }

            } else if (thisKind == 'field'){

            if (itemQuantity >= 1){ thisCell.addClass('item_cell_disabled');  }

            } else if (thisKind == 'star'){

            if (itemQuantity < 1){ thisCell.addClass('item_cell_disabled');  }

            }
        });
    return true;
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

// Define a function for printing a number with commas as thousands separators
function printNumberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}