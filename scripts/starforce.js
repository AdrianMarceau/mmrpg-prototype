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
    thisContainer = $('.stars', thisPrototype);

    thisWindow.resize(function(){ windowResizeStarforce(); });
    setTimeout(function(){ windowResizeStarforce(); }, 1000);
    windowResizeStarforce();

    var windowHeight = $(window).height();
    var htmlHeight = $('html').height();
    var htmlScroll = $('html').scrollTop();
    console.log('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

    // Hijack any href links for ipad fixing
    $('a[href]', thisBody).click(function(e){
        e.preventDefault();
        if ($(this).attr('href') == '#'){ return false; }
        window.location.href = $(this).attr('href');
        });

    // Define click events for the prev and next buttons
    var groupLists = $('.starchart .grouplist', thisBody);
    $('.arrow[data-dir]', groupLists).click(function(e){
        e.preventDefault();

        // Collect key object references and values
        var thisArrow = $(this);
        var thisDirection = thisArrow.attr('data-dir');
        var thisGrouplist = thisArrow.closest('.grouplist');
        var currentGroupToken = thisGrouplist.attr('data-current');
        var currentGroupContainer = thisGrouplist.find('.group[data-group="'+currentGroupToken+'"]');

        // Generate the new prev/next object references and values
        if (thisDirection == 'prev'){
            var newGroupContainer = currentGroupContainer.prev('.group');
            if (!newGroupContainer.length){ newGroupContainer = thisGrouplist.find('.group[data-group]').last(); }
        } else if (thisDirection == 'next'){
            var newGroupContainer = currentGroupContainer.next('.group');
            if (!newGroupContainer.length){ newGroupContainer = thisGrouplist.find('.group[data-group]').first(); }
        }
        var newGroupToken = newGroupContainer.attr('data-group');

        // Swap the current group with the new one
        thisGrouplist.attr('data-current', newGroupToken);
        currentGroupContainer.removeClass('current');
        newGroupContainer.addClass('current');

        // Trigger a refresh of the visible stars
        return refreshStarchart();

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
    console.log('windowResizeStarforce()');



    refreshStarchart();
    return true;

}

// Define a function for updating the star menu elements
function refreshStarchart(){
    console.log('refreshStarchart()');

    // Collect reference to key starchart objects
    var thisStarchart = $('.starchart', thisBody);
    var currentGroups = $('.grouplist .group.current', thisStarchart);

    // Define arrays to hold visible keys
    var visibleTopKeys = [];
    var visibleSideKeys = [];

    // Loop through current groups and collect keys
    currentGroups.each(function(){
        var thisGroup = $(this);
        var thisGroupRobots = thisGroup.find('.robot');
        thisGroupRobots.each(function(){
            var thisRobot = $(this);
            var thisRobotIcon = thisRobot.find('.icon');
            if (thisRobotIcon.attr('data-top-key') != undefined){
                var topKey = parseInt(thisRobotIcon.attr('data-top-key'));
                visibleTopKeys.push(topKey);
            } else if (thisRobotIcon.attr('data-side-key') != undefined){
                var sideKey = parseInt(thisRobotIcon.attr('data-side-key'));
                visibleSideKeys.push(sideKey);
            }
            });

        });

    console.log('visibleTopKeys', visibleTopKeys);
    console.log('visibleSideKeys', visibleSideKeys);

    // Remove the visible class from all star containers
    var starSprites = $('.sprite_star', thisStarchart);
    starSprites.removeClass('visible');
    starSprites.each(function(){

        var thisStar = $(this);
        var thisStarTopKey = parseInt(thisStar.attr('data-top-key'));
        var thisStarSideKey = parseInt(thisStar.attr('data-side-key'));
        var thisStarVisible = visibleTopKeys.indexOf(thisStarTopKey) != -1 && visibleSideKeys.indexOf(thisStarSideKey) != -1 ? true : false;
        if (thisStarVisible){ thisStar.addClass('visible'); }

        });

    return true;
}