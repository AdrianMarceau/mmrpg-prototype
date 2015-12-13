// Define global objects and variables for this script
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisContainer = false;
var thisTypeContainer = false;
var thisStarContainer = false;
var thisStarSprites = false;
var thisPageLinks = false;
var thisPageLinksTop = false;
var thisPageLinksSide = false;
var thisBrowserOrientation = false;
var thisStarSettings = {};
thisStarSettings.containerRows = 8;
thisStarSettings.containerColumns = 8;
thisStarSettings.containerLimit = thisStarSettings.containerRows * thisStarSettings.containerColumns;
thisStarSettings.containerPages = 0;
thisStarSettings.starCount = 0;
// Generate the document ready events for this page
$(document).ready(function(){
  // Start playing the data base music
  //top.mmrpg_music_load('misc/data-base');

  // Update global reference variables
  thisBody = $('#mmrpg');
  thisPrototype = $('#prototype', thisBody);
  thisWindow = $(window);
  thisContainer = $('.starforce', thisPrototype);
  thisTypeContainer = $('.types_container', thisPrototype);
  thisStarContainer = $('.stars_container', thisPrototype);
  thisPageLinks = $('.page_links', thisPrototype);
  thisPageLinksTop = $('.page_links.top_panel', thisPrototype);
  thisPageLinksSide = $('.page_links.side_panel', thisPrototype);
  thisBrowserOrientation = 'portrait';

  thisWindow.resize(function(){ windowResizeStarforce(); });
  setTimeout(function(){ windowResizeStarforce(); }, 1000);
  windowResizeStarforce();

  var windowHeight = $(window).height();
  var htmlHeight = $('html').height();
  var htmlScroll = $('html').scrollTop();
  //alert('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

  // Hijack any href links for ipad fixing
  $('a[href]', thisBody).click(function(e){
    e.preventDefault();
    if ($(this).attr('href') == '#'){ return false; }
    window.location.href = $(this).attr('href');
    });
  
  // Define a click function for the arrow links
  $('.arrow', thisPageLinks).click(function(e){
    e.preventDefault();
    
    var thisArrow = $(this);
    var thisArrowScroll = thisArrow.attr('data-scroll');
    var thisPanel = thisArrow.parents('.page_links');
    var thisPanelType = thisPanel.hasClass('top_panel') ? 'top' : 'side';
    var thisPanelKey = parseInt(thisPanel.attr('data-key'));
    var thisPanelMax = parseInt(thisPanel.attr('data-max'));
    var thisPanelTotal = thisPanel.find('.robot').length;
    
    //console.log('arrow | type:'+thisPanelType+' | scroll:'+thisArrowScroll+' | key:'+thisPanelKey+' | max:'+thisPanelMax+' | total = '+thisPanelTotal);
    
    if (thisPanelType == 'top' && thisArrowScroll == 'right'){
      
      // Scroll to the RIGHT / FORWARD and increment key by one      
      if ((thisPanelMax + thisPanelKey) >= thisPanelTotal){ return false; }      
      newPanelKey = thisPanelKey + 1;
      //console.log('scroll right / forward to '+newPanelKey);
      
      } else if (thisPanelType == 'top' && thisArrowScroll == 'left'){
      
      // Scroll to the LEFT / BACKWARD and decrement key by one
      if (thisPanelKey <= 0){ return false; }
      newPanelKey = thisPanelKey - 1;
      //console.log('scroll left / backward to '+newPanelKey);
      
      } else if (thisPanelType == 'side' && thisArrowScroll == 'down'){
      
      // Scroll to the DOWN / FORWARD and increment key by one
      if ((thisPanelMax + thisPanelKey) >= thisPanelTotal){ return false; }
      newPanelKey = thisPanelKey + 1;
      //console.log('scroll down / forward to '+newPanelKey);
      
      } else if (thisPanelType == 'side' && thisArrowScroll == 'up'){
      
      // Scroll to the UP / BACKWARD and increment key by one
      if (thisPanelKey <= 0){ return false; }
      newPanelKey = thisPanelKey - 1;
      //console.log('scroll up / backward to '+newPanelKey);
      
      }
    
    thisPanel.attr('data-key', newPanelKey);
    refreshStarforceContainers();
    
    });
  
  // Add mouse-wheel scrolling to the starforce container for easier browsing
  thisStarContainer.bind('mousewheel', function(e){
    e.preventDefault();
    //console.log('event.originalEvent.wheelDeltaX = '+e.originalEvent.wheelDeltaX+' | event.originalEvent.wheelDeltaY = '+e.originalEvent.wheelDeltaY);
    if ((e.originalEvent.wheelDeltaY / 120) > 0){
      //console.log('scrolling up !');
      $('.arrow[data-scroll="up"]', thisPageLinks).trigger('click');
      } else if ((e.originalEvent.wheelDeltaY / 120) < 0) {
      //console.log('scrolling down !');
      $('.arrow[data-scroll="down"]', thisPageLinks).trigger('click');
      } else if ((e.originalEvent.wheelDeltaX / 120) > 0){
      //console.log('scrolling left !');
      $('.arrow[data-scroll="left"]', thisPageLinks).trigger('click');
      } else if ((e.originalEvent.wheelDeltaX / 120) < 0) {
      //console.log('scrolling right !');
      $('.arrow[data-scroll="right"]', thisPageLinks).trigger('click');
      }
    });
    
  // Count the number of stars present and creat page links if necessary
  thisStarSprites = $('.sprite_star', thisStarContainer);
  thisStarSettings.starCount = thisStarSprites.length ? thisStarSprites.length : 0;
  if (thisStarSettings.starCount > thisStarSettings.containerLimit){
    
    // Calculate the number of pages based on count
    thisStarSettings.containerPages = Math.ceil(thisStarSettings.starCount / thisStarSettings.containerLimit);
    
    // Append the appropriate number of links to the container
    var thisPageLinksWrapper = $('.page_links .wrapper', thisContainer); //$('<div class="page_links"><label class="label">Page</label></div>');
    for (var i = 1; i <= thisStarSettings.containerPages; i++){ thisPageLinksWrapper.append('<a href="#" class="page'+(i == 1 ? ' page_active' : '')+'" data-page="'+i+'">'+i+'</a>');  }
    //$('.wrapper', thisContainer).append(thisPageLinks);
    
    // Create the click event for all these new page buttons
    $('.page', thisContainer).click(function(e){
      e.preventDefault();
      //console.log('page clicked!');
      var thisLink = $(this);
      var thisCurrentPage = $('.page_active', thisContainer);
      var thisNewPage = thisLink.attr('data-page');
      var thisNewKeyMax = (thisNewPage * thisStarSettings.containerLimit) - 1;
      var thisNewKeyMin = (thisNewKeyMax + 1) - thisStarSettings.containerLimit;
      thisCurrentPage.removeClass('page_active');
      thisLink.addClass('page_active');
      // Automatically hide all the stars that are out of range
      thisStarSprites.each(function(){
        var thisSprite = $(this);
        var thisKey = thisSprite.attr('data-key');
        if (thisKey > thisNewKeyMax || thisKey < thisNewKeyMin){ thisSprite.css({display:'none'}); }
        else { thisSprite.css({display:'block'}); }
        });
      });
    // Trigger a click on page one automatically
    $('.page', thisContainer).first().trigger('click');
    //console.log(thisStarSettings);
    
    }
  
  // Create the hover event for the star sprites
  thisStarSprites.hover(function(){
    var thisSprite = $(this);
    var thisSideKey = thisSprite.attr('data-side-key');
    var thisTopKey = thisSprite.attr('data-top-key');
    thisStarSprites.removeClass('highlight');
    thisStarSprites.filter('[data-side-key='+thisSideKey+']').addClass('highlight');
    thisStarSprites.filter('[data-top-key='+thisTopKey+']').addClass('highlight');
    }, function(){
    thisStarSprites.removeClass('highlight');      
    });

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
function windowResizeStarforce(){
  
  var windowWidth = thisWindow.width();
  var windowHeight = thisWindow.height();
  var headerHeight = $('.header', thisBody).outerHeight(true);
  //var thisTypeContainerWidth = thisTypeContainer.outerWidth(true);
  //var thisStarContainerWidth = thisStarContainer.outerWidth(true);
  //var thisPageLinksTopWidth = thisPageLinksTop.outerWidth(true);
  //var thisPageLinksSideWidth = thisPageLinksSide.outerWidth(true);
  
  // Update the browser orientation 
  var menuWidth = $('.menu', thisPrototype).width();
  if (menuWidth > 800){ thisBrowserOrientation = 'landscape'; thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
  else { thisBrowserOrientation = 'portrait'; thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
  //console.log('update orientation to '+thisBrowserOrientation);
  
  // Update the body and prototype to full height
  thisBody.css({height:windowHeight+'px'});
  thisPrototype.css({height:windowHeight+'px'});
  
  // Refresh the starforce elements
  thisPageLinksSide.attr('data-key', 0);
  thisPageLinksTop.attr('data-key', 0);
  refreshStarforceContainers();

}

// Define a function for updating the starforce menu elements
function refreshStarforceContainers(){
  //console.log('refreshStarforceContainers()');
  
  // Update the max links for the top panel based on orientation
  var newSideDataMax = 9;
  var newTopDataMax = 9;
  if (thisBrowserOrientation == 'landscape'){ newTopDataMax = 19; }
  else if (thisBrowserOrientation == 'portrait'){ newTopDataMax = 12; }
  //console.log('update page links top data max to '+newTopDataMax);
  thisPageLinksSide.attr('data-max', newSideDataMax);
  thisPageLinksTop.attr('data-max', newTopDataMax);
  
  // Define a variable to hold the allowed top and side keys
  var allowedTopKeys = [];
  var allowedSideKeys = [];
  
  // Loop through the link panels and limit robot display
  thisPageLinks.each(function(index, panel){
    
    // Collect robot limits from the panel and define the range
    var linkPanel = $(this);
    var linkPanelKind = linkPanel.hasClass('top_panel') ? 'top' : 'side';
    var displayMax = parseInt(linkPanel.attr('data-max'));
    var currentKey = parseInt(linkPanel.attr('data-key'));
    var firstVisibleKey = currentKey;
    var lastVisibleKey = currentKey + displayMax - 1;
    //console.log('parsing linkPanel '+linkPanel.attr('class'));
    //console.log('displayMax = '+displayMax+' currentKey = '+currentKey+' firstVisibleKey = '+firstVisibleKey+' lastVisibleKey = '+lastVisibleKey);
    
    // Hide all robots links in both menus, then show only up to max
    $('.robot', linkPanel).each(function(index2, robot){      
      var robotLink = $(this);
      if (index2 >= firstVisibleKey && index2 <= lastVisibleKey){ 
        robotLink.removeClass('hidden'); 
        if (linkPanelKind == 'top'){ allowedTopKeys.push(robotLink.attr('data-top-key')); }
        else if (linkPanelKind == 'side'){ allowedSideKeys.push(robotLink.attr('data-side-key')); }
        } else {
        robotLink.addClass('hidden');
        }
      });

    });
  
  //console.log('allowedTopKeys = ', allowedTopKeys);
  //console.log('allowedSideKeys = ', allowedSideKeys);
  
  // Loop through all the star containers and hide ones that are not relevant
  $('.sprite_star', thisStarContainer).each(function(){
    
    var thisStar = $(this);
    var thisTopKey = thisStar.attr('data-top-key');
    var thisSideKey = thisStar.attr('data-side-key');
    
    if (allowedTopKeys.indexOf(thisTopKey) != -1 && allowedSideKeys.indexOf(thisSideKey) != -1){  
      thisStar.removeClass('hidden');
      } else {
      thisStar.addClass('hidden');        
      }
    
    });
  
}