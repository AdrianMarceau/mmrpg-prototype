// Generate the document ready events for this page
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisShop = false;
var thisShopData = {shopTotal:0,zennyCounter:0,itemQuantities:{},itemPrices:{},allowEdit:true,unlockedPlayers:{}};
var resizePlayerWrapper = function(){};
$(document).ready(function(){

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);
  thisShop = $('#shop', thisBody);
  
  // Update the player and player count by counting elements
  thisShopData.shopTotal = $('#canvas .wrapper[data-shop]', thisShop).length;
  //console.log('thisShopData', thisShopData);
  
  //console.log(thisShopData);
    
  // Trigger the resize wrapper on load
  
  //alert('I, the shop, have a wap setting of '+(gameSettings.wapFlag ? 'true' : 'false')+'?! and my body has a class of '+$('body').attr('class')+'!');
  
  // Start playing the appropriate stage music
  //top.mmrpg_music_load('misc/data-base');
 
  
  // Create the click event for canvas sprites
  //$('.sprite[data-token]', gameCanvas).live('click', function(e){
  $('.wrapper', gameCanvas).live('click', function(e){
    e.preventDefault();
    if (!thisShopData.allowEdit){ return false; }
    var dataParent = $(this);
    var dataSprite = $(this).find('.sprite[data-token]');    
    var dataSelect = dataParent.attr('data-select');
    var dataToken = dataSprite.attr('data-token');
    var dataShop = dataSprite.attr('data-shop');
    var dataSelectorCurrent = '#'+dataSelect+' .event_visible';
    var dataSelectorNext = '#'+dataSelect+' .event[data-token='+dataToken+']';
    //console.log('.sprite[data-token] clicked!', {dataToken:dataToken,dataShop:dataShop,dataSelectorCurrent:dataSelectorCurrent,dataSelectorNext:dataSelectorNext});
    $('.wrapper_active', gameCanvas).removeClass('wrapper_active');
    $('.sprite_shop_current', gameCanvas).removeClass('sprite_shop_current');
    //console.log('updating perfect scrollbar 1');
    $('#console .scroll_wrapper', thisShop).perfectScrollbar('update');
    dataParent.addClass('wrapper_active').css({display:'block'});
    dataSprite.addClass('sprite_shop_current');
    if ($(dataSelectorCurrent, gameConsole).length){
      $(dataSelectorCurrent, gameConsole).stop().animate({opacity:0},250,'swing',function(){
        $(this).removeClass('event_visible').addClass('event_hidden').css({opacity:1});
        $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
        $(dataSelectorNext, gameConsole).find('.tab_link').first().trigger('click');
        });
      } else {
        $(dataSelectorNext, gameConsole).css({opacity:0}).removeClass('event_hidden').addClass('event_visible').animate({opacity:1.0},250,'swing');
        $(dataSelectorNext, gameConsole).find('.tab_link').first().trigger('click');
      }

    });
  
  // Attach tab events to any shop tabs so that we can switch between selling/buying
  $('.shop_tabs_links .tab_link[data-tab]', gameConsole).live('click', function(e){
    e.preventDefault();
    if (!thisShopData.allowEdit){ return false; }
    var thisTab = $(this);
    var thisTabToken = thisTab.attr('data-tab');
    var thisTabType = thisTab.attr('data-tab-type');
    //console.log('clicked .tab_link[data-tab='+thisTabToken+'][data-tab-type='+thisTabType+']');
    var tabLinkBlock = thisTab.parent();
    var eventContainer = tabLinkBlock.parent();
    var tabContainerBlock = $('.shop_tabs_containers', eventContainer);
    var thiContainer = $('.tab_container[data-tab='+thisTabToken+'][data-tab-type='+thisTabType+']', eventContainer);
    $('.shop_tabs_links .tab_link[data-tab]', gameConsole).removeClass('tab_link_active');
    thisTab.addClass('tab_link_active');
    $('.shop_tabs_containers .tab_container[data-tab]', gameConsole).removeClass('tab_container_active');
    thiContainer.addClass('tab_container_active');
    var thisConfirmCell = thiContainer.find('.item_cell_confirm');
    thisConfirmCell.attr('data-kind', '').attr('data-action', '').attr('data-token', '').attr('data-price', '').attr('data-quantity', '');
    thisConfirmCell.empty().html('<div class="placeholder">&hellip;</div>');
    //console.log('updating perfect scrollbar 2');
    $('#console .scroll_wrapper', thisShop).perfectScrollbar('update');
    return true;
    });
  
  // Functionality to the sell links for all shops
  $('.item_cell[data-token][data-action=sell] .sell_button', gameConsole).live('click', function(e){
    e.preventDefault();
    if (!thisShopData.allowEdit){ return false; }
    var thisButton = $(this); 
    var thisCell = thisButton.parent(); 
    var thisSeller = thisButton.parents('.event[data-token]').attr('data-token');
    var thisTab = thisCell.parents('.tab_container[data-tab]');
    var thisKind = thisCell.attr('data-kind');
    var thisAction = thisCell.attr('data-action'); 
    var thisToken = thisCell.attr('data-token'); 
    var thisQuantity = thisCell.find('.item_quantity').attr('data-quantity') || 0;
    var thisPrice = thisCell.find('.item_price').attr('data-price') || 0;
    thisQuantity = parseInt(thisQuantity);
    thisPrice = parseInt(thisPrice);
    var sellQuantity = 1;
    var sellPrice = thisPrice;    
    var thisDisabled = thisCell.hasClass('item_cell_disabled') ? true : false;
    if (thisDisabled){ return false; }
    var thisItemName = thisCell.find('.item_name').clone();    
    //console.log(thisSeller+' / '+thisKind+' / '+thisAction+' / '+thisToken+' / x'+thisQuantity+' / '+thisPrice+'z');    
    var itemCellConfirm = $('.item_cell_confirm', thisTab);    
    if (itemCellConfirm.attr('data-token') == thisToken){      
      var sellQuantity = parseInt(itemCellConfirm.attr('data-quantity')) + 1;
      if (sellQuantity > thisQuantity){ sellQuantity = thisQuantity; }
      var sellPrice = sellQuantity * parseInt(thisPrice);
      itemCellConfirm.attr('data-quantity', sellQuantity).attr('data-price', sellPrice).attr('data-shop', thisSeller);
      itemCellConfirm.find('.item_quantity').attr('data-quantity', sellQuantity).html('x '+sellQuantity);
      itemCellConfirm.find('.item_price').attr('data-price', sellPrice).html('&hellip; '+printNumberWithCommas(sellPrice)+'z');      
      } else {      
      itemCellConfirm.empty();
      itemCellConfirm.attr('data-kind', thisKind).attr('data-action', thisAction).attr('data-token', thisToken).attr('data-price', thisPrice).attr('data-quantity', sellQuantity).attr('data-shop', thisSeller);
      itemCellConfirm.append('<a class="cancel_button ability_type ability_type_attack" href="#">Cancel</a>');
      itemCellConfirm.append('<a class="confirm_button ability_type ability_type_energy" href="#">Confirm</a>');
      itemCellConfirm.append('<label class="item_price" data-price="'+sellPrice+'">&hellip; '+printNumberWithCommas(sellPrice)+'z</label>');
      itemCellConfirm.append('<label class="item_quantity" data-quantity="'+sellQuantity+'">x '+sellQuantity+'</label>');
      itemCellConfirm.append(thisItemName);              
      }        
    return true;
    });
  
  /*
  // Functionality to the player toggles on hover
  $('.item_cell_confirm .player_button', gameConsole).live('mouseenter', function(e){
    
    //e.preventDefault();
    var thisButton = $(this);
    var thisStat = thisButton.attr('data-stat');
    var thisPlayer = thisButton.attr('data-player');
    if (thisButton.hasClass('player_button_active')){ return false; }
    else if (thisButton.hasClass('player_button_disabled')){ return false; }
    thisButton.addClass('ability_type_'+thisStat);    
    //console.log('hover in player button '+thisStat+' '+thisPlayer);
    return true;
    
    }).live('mouseleave', function(e){
      
    //e.preventDefault();
    var thisButton = $(this);
    var thisStat = thisButton.attr('data-stat');
    var thisPlayer = thisButton.attr('data-player');
    if (thisButton.hasClass('player_button_active')){ return false; }
    else if (thisButton.hasClass('player_button_disabled')){ return false; }
    thisButton.removeClass('ability_type_'+thisStat);      
    //console.log('hover out player button '+thisStat+' '+thisPlayer);
    return true;
    
    });
  
  // Functionality to the player toggles on hover
  $('.item_cell_confirm .player_button', gameConsole).live('click', function(e){
    
    e.preventDefault();
    var thisButton = $(this);
    var thisCell = thisButton.parent(); 
    var thisTab = thisCell.parents('.tab_container[data-tab]');
    var thisStat = thisButton.attr('data-stat');
    var thisPlayer = thisButton.attr('data-player');
    var itemCellConfirm = $('.item_cell_confirm', thisTab);
    if (thisButton.hasClass('player_button_disabled')){ return false; }
    //console.log('click on player button '+thisStat+' '+thisPlayer);
    $('.item_cell_confirm .player_button_active', gameConsole).removeClass('player_button_active').removeClass(function(){ return 'ability_type_'+$(this).attr('data-stat'); });
    thisButton.addClass('ability_type_'+thisStat).addClass('player_button_active'); 
    itemCellConfirm.attr('data-player', thisPlayer);
    $('.item_cell_confirm .confirm_button', gameConsole).removeClass('confirm_button_disabled');
    
    return true;
    
    
    });
  
  */
  
  // Functionality to the buy links for all shops
  $('.item_cell[data-token][data-action=buy] .buy_button', gameConsole).live('click', function(e){
    e.preventDefault();
    if (!thisShopData.allowEdit){ return false; }
    var thisButton = $(this); 
    var thisCell = thisButton.parent(); 
    var thisBuyer = thisButton.parents('.event[data-token]').attr('data-token');
    var thisTab = thisCell.parents('.tab_container[data-tab]');
    var thisKind = thisCell.attr('data-kind');
    var thisAction = thisCell.attr('data-action'); 
    var thisToken = thisCell.attr('data-token'); 
    var thisQuantity = thisCell.find('.item_quantity').attr('data-quantity') || 0;
    var thisPrice = thisCell.find('.item_price').attr('data-price') || 0;
    thisQuantity = parseInt(thisQuantity);
    thisPrice = parseInt(thisPrice);
    var buyQuantity = 1;
    var buyPrice = thisPrice;    
    var thisDisabled = thisCell.hasClass('item_cell_disabled') ? true : false;
    if (thisDisabled){ return false; }
    var thisItemName = thisCell.find('.item_name').clone();    
    //console.log(thisBuyer+' / '+thisKind+' / '+thisAction+' / '+thisToken+' / '+thisPrice+'z');    
    var itemCellConfirm = $('.item_cell_confirm', thisTab);    
    if (thisKind == 'item' && itemCellConfirm.attr('data-token') == thisToken){   
      
      buyQuantity = parseInt(itemCellConfirm.attr('data-quantity')) + 1;
      //console.log('A) '+thisBuyer+' / '+thisKind+' / '+thisAction+' / '+thisToken+' / x'+buyQuantity+' / '+thisPrice+'z');
      buyPrice = buyQuantity * parseInt(thisPrice);
      if (buyQuantity + thisQuantity > 99){ 
        //console.log('buyQuantity + thisQuantity > 99 | '+buyQuantity+' + '+thisQuantity+' > 99');
        buyQuantity = 99 - thisQuantity; 
        buyPrice = buyQuantity * parseInt(thisPrice); 
        } else if (buyPrice > thisShopData.zennyCounter){
        //console.log('buyPrice > thisShopData.zennyCounter | '+buyPrice+' > '+thisShopData.zennyCounter+' ');
        buyQuantity = buyQuantity - 1; 
        buyPrice = buyQuantity * parseInt(thisPrice); 
        }      
      itemCellConfirm.attr('data-quantity', buyQuantity).attr('data-price', buyPrice).attr('data-shop', thisBuyer);
      itemCellConfirm.find('.item_quantity').attr('data-quantity', buyQuantity).html('x '+buyQuantity);
      itemCellConfirm.find('.item_price').attr('data-price', buyPrice).html('&hellip; '+printNumberWithCommas(buyPrice)+'z');  
      
    } else if (thisKind == 'ability') {   
      
      var actualQuantity = 1; //3 - thisQuantity;
      var actualPrice = buyPrice * actualQuantity;
      var thisUnlocked = thisCell.attr('data-unlocked').split(',');
      //console.log('thisUnlocked', thisUnlocked);
      //console.log('B) '+thisBuyer+' / '+thisKind+' / '+thisAction+' / '+thisToken+' / x'+actualQuantity+' / '+thisPrice+'z');
      itemCellConfirm.empty();
      itemCellConfirm.attr('data-kind', thisKind).attr('data-action', thisAction).attr('data-token', thisToken).attr('data-price', thisPrice).attr('data-quantity', buyQuantity).attr('data-shop', thisBuyer).attr('data-player', 'all');
      itemCellConfirm.append('<a class="cancel_button ability_type ability_type_attack" href="#">Cancel</a>');
      //itemCellConfirm.append('<a class="confirm_button confirm_button_disabled ability_type ability_type_energy" href="#">Confirm</a>');      
      itemCellConfirm.append('<a class="confirm_button ability_type ability_type_energy" href="#">Confirm</a>');
      
      var buttonCounter = 0;
      var buttonMarkup = [];
      
      /*
      if (jQuery.inArray('dr-light', thisShopData.unlockedPlayers) != -1){      
        if (jQuery.inArray('dr-light', thisUnlocked) != -1){  
          buttonCounter++;
          //console.log('jQuery.inArray(\'dr-light\', thisUnlocked) == TRUE');
          buttonMarkup.push('<a class="player_button player_button_1 player_button_disabled ability_type ability_type_defense" data-stat="defense" data-player="dr-light" data-tooltip="Dr. Light" data-tooltip-type="ability_type_defense">L</a>'); 
          } else {  
          buttonCounter++;
          //console.log('jQuery.inArray(\'dr-light\', thisUnlocked) == FALSE');
          buttonMarkup.push('<a class="player_button player_button_1 ability_type" data-stat="defense" data-player="dr-light" href="#" data-tooltip="Dr. Light" data-tooltip-type="ability_type_defense">L</a>'); 
          }                
        }
      
      if (jQuery.inArray('dr-wily', thisShopData.unlockedPlayers) != -1){      
        if (jQuery.inArray('dr-wily', thisUnlocked) != -1){  
          buttonCounter++;
          //console.log('jQuery.inArray(\'dr-wily\', thisUnlocked) == TRUE');
          buttonMarkup.push('<a class="player_button player_button_2 player_button_disabled ability_type ability_type_attack" data-stat="attack" data-player="dr-wily" data-tooltip="Dr. Wily" data-tooltip-type="ability_type_attack">W</a>'); 
          } else {  
          buttonCounter++;
          //console.log('jQuery.inArray(\'dr-wily\', thisUnlocked) == FALSE');
          buttonMarkup.push('<a class="player_button player_button_2 ability_type" data-stat="attack" data-player="dr-wily" href="#" data-tooltip="Dr. Wily" data-tooltip-type="ability_type_attack">W</a>'); 
          }                
        }
      
      if (jQuery.inArray('dr-cossack', thisShopData.unlockedPlayers) != -1){      
        if (jQuery.inArray('dr-cossack', thisUnlocked) != -1){ 
          buttonCounter++;
          //console.log('jQuery.inArray(\'dr-cossack\', thisUnlocked) == TRUE');
          buttonMarkup.push('<a class="player_button player_button_3 player_button_disabled ability_type ability_type_speed" data-stat="speed" data-player="dr-cossack" data-tooltip="Dr. Cossack" data-tooltip-type="ability_type_speed">C</a>'); 
          } else {  
          buttonCounter++;
          //console.log('jQuery.inArray(\'dr-cossack\', thisUnlocked) == FALSE');
          buttonMarkup.push('<a class="player_button player_button_3 ability_type" data-stat="speed" data-player="dr-cossack" href="#"  data-tooltip="Dr. Cossack" data-tooltip-type="ability_type_speed">C</a>'); 
          }        
        }
      */
      
      buttonMarkup.reverse();
      for (i in buttonMarkup){ 
        var newButton = $(buttonMarkup[i]);
        if (i == (buttonMarkup.length - 1)){ newButton.css({marginLeft:'20px'}); }
        newButton.appendTo(itemCellConfirm); 
        }
      
      itemCellConfirm.append('<label class="item_price" data-price="'+actualPrice+'">&hellip; '+printNumberWithCommas(actualPrice)+'z</label>');
      //itemCellConfirm.append('<label class="item_quantity" data-quantity="1">&#10004;</label>');
      itemCellConfirm.append('<label class="item_quantity" data-quantity="'+buyQuantity+'">x '+buyQuantity+'</label>');
      itemCellConfirm.append(thisItemName);    
      
      } else {
        
      //console.log('C) '+thisBuyer+' / '+thisKind+' / '+thisAction+' / '+thisToken+' / x'+buyQuantity+' / '+thisPrice+'z');            
      itemCellConfirm.empty();
      itemCellConfirm.attr('data-kind', thisKind).attr('data-action', thisAction).attr('data-token', thisToken).attr('data-price', thisPrice).attr('data-quantity', buyQuantity).attr('data-shop', thisBuyer);
      itemCellConfirm.append('<a class="cancel_button ability_type ability_type_attack" href="#">Cancel</a>');
      itemCellConfirm.append('<a class="confirm_button ability_type ability_type_energy" href="#">Confirm</a>');
      itemCellConfirm.append('<label class="item_price" data-price="'+buyPrice+'">&hellip; '+printNumberWithCommas(buyPrice)+'z</label>');
      itemCellConfirm.append('<label class="item_quantity" data-quantity="'+buyQuantity+'">x '+buyQuantity+'</label>');
      itemCellConfirm.append(thisItemName);                 
      
      }         
    return true;
    });
  
  // Functionality to the cancel links for all shops
  $('.item_cell_confirm .cancel_button', gameConsole).live('click', function(e){
    e.preventDefault();
    if (!thisShopData.allowEdit){ return false; }
    var thisButton = $(this);
    var thisConfirmCell = thisButton.parent();
    thisConfirmCell.attr('data-kind', '').attr('data-action', '').attr('data-token', '').attr('data-price', '').attr('data-quantity', '').attr('data-player', '');
    thisConfirmCell.empty().html('<div class="placeholder">&hellip;</div>');
    //console.log('cancel_button:click');
    return true;    
    });
  
  // Functionality to the confirm links for all shops
  $('.item_cell_confirm .confirm_button', gameConsole).live('click', function(e){
    e.preventDefault();
    if (!thisShopData.allowEdit){ return false; }
    var thisButton = $(this);
    var thisConfirmCell = thisButton.parent();
    var thisKeeper = thisConfirmCell.attr('data-shop');
    var thisKind = thisConfirmCell.attr('data-kind');
    var thisAction = thisConfirmCell.attr('data-action'); 
    var thisToken = thisConfirmCell.attr('data-token'); 
    var thisQuantity = thisConfirmCell.find('.item_quantity').attr('data-quantity') || 0;
    var thisPrice = thisConfirmCell.find('.item_price').attr('data-price') || 0; 
    var thisPlayer = thisConfirmCell.attr('data-player') || '';
    if (thisButton.hasClass('confirm_button_disabled')){ return false; }
    var thisTab = thisButton.parents('.tab_container');
    var thisItemCell = $('.item_cell[data-token='+thisToken+']', thisTab);
    //console.log('confirm_button: click / '+thisKeeper+' / '+thisKind+' / '+thisAction+' / '+thisToken+' / x'+thisQuantity+' / '+thisPrice+'z / '+thisPlayer+'');    
    
    // Define the post options for the ajax call
    var postData = {shop:thisKeeper,kind:thisKind,action:thisAction,token:thisToken,quantity:thisQuantity,price:thisPrice,player:thisPlayer};
    thisConfirmCell.css({opacity:0.3});
    thisShopData.allowEdit = false;
    
    // Post the sort request to the server
    $.ajax({
      type: 'POST',
      url: 'frames/shop.php',
      data: postData,
      success: function(data, status){
        
        // Break apart the response into parts
        var data = data.split('|');
        var dataStatus = data[0] != undefined ? data[0] : false;
        var dataMessage = data[1] != undefined ? data[1] : false;
        
        // Check the status of the response and respond
        if (dataStatus == 'error'){
          //console.log('error');
          //console.log(data);
          
          thisShopData.allowEdit = true;
          return false;
          
          } else if (dataStatus == 'success'){            
          //console.log('success');
          //console.log(data);
          
          // Update this item's global quantity
          var newItemCount = data[2] != undefined ? parseInt(data[2]) : false;
          var newZennyTotal = data[3] != undefined ? parseInt(data[3]) : false;
          //console.log({newItemCount:newItemCount,newZennyTotal:newZennyTotal});
          
          // Define the change text
          if (thisAction == 'buy'){ var thisChangeText = '<span class="zenny" style="color: #C35E5E;">-'+printNumberWithCommas(thisPrice)+'z</span>'; }
          else if (thisAction == 'sell'){ var thisChangeText = '<span class="zenny" style="color: #8CEB80;">+'+printNumberWithCommas(thisPrice)+'z</span>'; }
          
          // Empty then unhide the parent cell
          thisConfirmCell.empty().attr('data-kind', '').attr('data-action', '').attr('data-token', '').attr('data-quantity', '').attr('data-price', '');
          thisConfirmCell.append('<div class="success">Success! '+thisChangeText+'</div>');
          
          thisShopData.itemQuantities[thisToken] = newItemCount;
          thisShopData.zennyCounter = newZennyTotal;
          
          /*
          if (thisKind == 'ability' && thisAction == 'buy'){ 
            //thisShopData.itemQuantities[thisAction][thisToken] += 1; 
            var unlockedPlayers = thisItemCell.attr('data-unlocked');
            //console.log('successful buy of ability data unlocked for '+thisToken); //+' / '+unlockedPlayers);
            unlockedPlayers = unlockedPlayers.split(',');
            unlockedPlayers.push(thisPlayer);
            unlockedPlayers = unlockedPlayers.join(',');
            thisItemCell.attr('data-unlocked', unlockedPlayers);
            }
            */
          
          var thisZennyFormatted = printNumberWithCommas(newZennyTotal);
          $('#zenny_counter', thisBody).css({color:'#8CEB80'}).html(thisZennyFormatted);
          if (window.self !== window.parent){ parent.prototype_update_zenny(thisZennyFormatted+' z'); }
          
          updateItemCells();
          
          thisConfirmCell.stop().animate({opacity:1.0},300,'swing',function(){ 
            thisConfirmCell.animate({opacity:0.3},600,'swing',function(){ 
              $(this).css({opacity:1.0}).empty().append('<div class="placeholder">&hellip;</div>');  
              $('#zenny_counter', thisBody).css({color:''});  
              thisShopData.allowEdit = true;
              });
            });
          
          return true;
          
          } else {
            
          //console.log('ummmm');
          //console.log(data);
          
          
          thisShopData.allowEdit = true;
          return false;
          
          }
        
        // DEBUG
        //alert('dataStatus = '+dataStatus+', dataMessage = '+dataMessage+', dataContent = '+dataContent+'; ');  
        
        }
      });
    
    return true;    
    });

  // Append the markup after load to prevent halting display and waiting shops
  $('#console #shops').append(shopConsoleMarkup);
  $('#canvas #links').append(shopCanvasMarkup);
  
  // Attach the scrollbar to the battle events container
  $('#console .scroll_wrapper', thisShop).perfectScrollbar({suppressScrollX: true, scrollYMarginOffset: 6});
  
  // Automatically click the first shop link
  $('#canvas #links .sprite[data-token]').first().trigger('click');  
  //console.log('updating perfect scrollbar 3');
  $('#console .scroll_wrapper', thisShop).perfectScrollbar('update');
  
  // Update all the item cells automatically
  updateItemCells();
  

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
      //console.log('updating perfect scrollbar 4');
      $('#console .scroll_wrapper', thisShop).perfectScrollbar('update');
      // Let the parent window know the menu has loaded
      parent.prototype_menu_loaded();
      }, 1000);
    }, false, true); 
  
});

// Define a function for refreshing all items
function updateItemCells(){
  var itemCells = $('.item_cell[data-token]', gameConsole); 
  //console.log('updateItems('+itemCells.length+')');
  //console.log('thisShopData', thisShopData);
  itemCells.each(function(index, value){ $(this).removeClass('item_cell_disabled'); });
  updateItemQuantities();
  updateItemPrices();
}

// Define a function for updating item quantities
function updateItemQuantities(){
  //console.log('updateItemQuantities()');
  for (var itemToken in thisShopData.itemQuantities){
    var itemQuantity = thisShopData.itemQuantities[itemToken];
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


// Define a function for updating item prices
function updateItemPrices(){
  //console.log('updateItemPrices()');
  for (var itemAction in thisShopData.itemPrices){
    //console.log('var itemListArray = thisShopData.itemPrices['+itemAction+'];');
    var itemListArray = thisShopData.itemPrices[itemAction];
    for (var itemToken in itemListArray){
      //console.log('var itemPrice = thisShopData.itemPrices['+itemAction+']['+itemToken+'];');
      var itemPrice = thisShopData.itemPrices[itemAction][itemToken];
      updateItemPrice(itemAction, itemToken, itemPrice);
    }     
  } 
}
// Define a function for updating a single item's price
function updateItemPrice(itemAction, itemToken, itemPrice){
  var itemCells = $('.item_cell[data-action='+itemAction+'][data-token='+itemToken+']', gameConsole); 
  itemCells.each(function(index, value){
    var thisCell = $(this);
    var thisKind = thisCell.attr('data-kind');
    var thisAction = thisCell.attr('data-action');
    //console.log('updateItemPrice('+thisKind+' / '+thisAction+' / '+itemToken+' / '+itemPrice+' / '+(thisCell.hasClass('item_cell_disabled') ? 'item_cell_disabled' : 'item_cell_enabled')+')');        
    var thisLabel = thisCell.find('label[data-price]');
    if (thisLabel != undefined){ thisLabel.attr('data-price', itemPrice).html('&hellip; '+printNumberWithCommas(itemPrice)+'z'); }            
    if (thisAction == 'buy' && itemPrice > thisShopData.zennyCounter){ thisCell.addClass('item_cell_disabled');  }
    else if (thisAction == 'sell' && itemPrice <= 0){ thisCell.addClass('item_cell_disabled');  }
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