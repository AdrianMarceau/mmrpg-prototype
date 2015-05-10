// Define global objects and variables for this script
var thisBody = false;
var thisPrototype = false;
var thisWindow = false;
var thisContainer = false;
var thisTypeContainer = false;
var thisStarContainer = false;
var thisStarSprites = false;
var thisStarSettings = {};
thisStarSettings.containerRows = 8;
thisStarSettings.containerColumns = 10;
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
    
  // Count the number of stars present and creat page links if necessary
  thisStarSprites = $('.sprite_star', thisStarContainer);
  thisStarSettings.starCount = thisStarSprites.length ? thisStarSprites.length : 0;
  if (thisStarSettings.starCount > thisStarSettings.containerLimit){
    
    // Calculate the number of pages based on count
    thisStarSettings.containerPages = Math.ceil(thisStarSettings.starCount / thisStarSettings.containerLimit);
    
    // Append the appropriate number of links to the container
    var thisPageLinks = $('<div class="page_links"><label class="label">Page</label></div>');
    for (var i = 1; i <= thisStarSettings.containerPages; i++){ thisPageLinks.append('<a href="#" class="page'+(i == 1 ? ' page_active' : '')+'" data-page="'+i+'">'+i+'</a>');  }
    $('.wrapper', thisContainer).append(thisPageLinks);
    
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
  var thisTypeContainerWidth = thisTypeContainer.outerWidth(true);
  var thisStarContainerWidth = thisStarContainer.outerWidth(true);
  
  var thisOrientation = 'portrait';
  if (windowWidth > 800){ thisOrientation = 'landscape'; thisBody.addClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
  else { thisOrientation = 'portrait'; thisBody.removeClass((gameSettings.wapFlag ? 'mobileFlag' : 'windowFlag')+'_landscapeMode'); }
  
  // Define the type and star container widths for resizing
  var newStarContainerWidth = windowWidth - thisTypeContainerWidth - 34;
  thisStarContainer.css({width:newStarContainerWidth+'px'});
  $('.page_links', thisContainer).css({width:newStarContainerWidth+'px'});
  //var newTypeContainerWidth = windowWidth - thisStarContainerWidth - 48;
  //thisTypeContainer.css({width:newTypeContainerWidth+'px'});
  //console.log('var newStarContainerWidth = thisOrientation == \'portrait\' ? 516 : 772;\nnewStarContainerWidth = '+newStarContainerWidth+';');
  //console.log('var newTypeContainerWidth = windowWidth - newStarContainerWidth - 30;\nnewTypeContainerWidth = '+newTypeContainerWidth+';');
  
  // Update the body and prototype to full height
  thisBody.css({height:windowHeight+'px'});
  thisPrototype.css({height:windowHeight+'px'});

  //console.log('windowWidth = '+windowWidth+'; thisTypeContainerWidth = '+thisTypeContainerWidth+'; thisStarContainerWidth = '+thisStarContainerWidth+'; ');

}