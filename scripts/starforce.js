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

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the items menu
    var thisContext = $('#prototype .menu .stars');
    if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){

        // Define a quick local function for routing sound effect plays to the parent
        function playSoundEffect(soundName, options){
            if ($(this).is('.disabled')){ return; }
            if ($(this).is('.button_disabled')){ return; }
            if ($(this).data('silentClick')){ return; }
            //console.log('trying to play sound effect');
            top.mmrpg_play_sound_effect(soundName, options);
            };


        // STAR GRID ARROWS & AVATARS

        // Add hover and click sounds to the buttons in star grid
        $('.starchart .grouplist .arrow', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('.starchart .grouplist .group .robots .icon', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });

        // STAR CHART STARS

        // Add hover and click sounds to the stars in star grid
        $('.starchart .starlist .sprite_star', thisContext).live('mouseenter', function(){
            if ($(this).is('.empty_star')){ return; }
            playSoundEffect.call(this, 'cosmic-sound', {volume: 0.5, rate: 2});
            });


        // STAR FORCE CHART & BUTTONS

        // Add hover and click sounds to the button in the toolbar menu
        $('.starforce .size_toggle', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('.starforce .size_toggle', thisContext).live('click', function(){
            playSoundEffect.call(this, 'icon-click-mini', {volume: 1.0});
            });

        }

    thisWindow.resize(function(){ windowResizeStarforce(); });
    setTimeout(function(){ windowResizeStarforce(); }, 1000);
    windowResizeStarforce();
    refreshArrowButtons();

    var windowHeight = $(window).height();
    var htmlHeight = $('html').height();
    var htmlScroll = $('html').scrollTop();
    //console.log('windowHeight = '+windowHeight+'; htmlHeight = '+htmlHeight+'; htmlScroll = '+htmlScroll+'; ');

    // Hijack any href links for ipad fixing
    $('a[href]', thisBody).click(function(e){
        e.preventDefault();
        if ($(this).attr('href') == '#'){ return false; }
        window.location.href = $(this).attr('href');
        });

    // Define click events for the prev and next buttons
    var groupLists = $('.starchart .grouplist', thisBody);
    $('.arrow[data-dir]', groupLists).bind('click', function(e){
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
            if (!newGroupContainer.length){
                return false;
                newGroupContainer = thisGrouplist.find('.group[data-group]').last();
            }
        } else if (thisDirection == 'next'){
            var newGroupContainer = currentGroupContainer.next('.group');
            if (!newGroupContainer.length){
                return false;
                newGroupContainer = thisGrouplist.find('.group[data-group]').first();
            }
        }
        var newGroupToken = newGroupContainer.attr('data-group');

        // Swap the current group with the new one in the group list
        thisGrouplist.attr('data-current', newGroupToken);
        currentGroupContainer.removeClass('current');
        newGroupContainer.addClass('current');

        // Trigger a refresh of the buttons and visible stars
        playSoundEffect.call(this, 'icon-click', {volume: 1.0});
        refreshArrowButtons();
        refreshStarchart();
        return true;

        });

    // Loop through chart placeholders and initialize canvases
    var starForceChart = $('.starforce', thisBody);
    var starForceChartCanvases = [];
    if (starForceChart.length && typeof thisStarSettings.starData != 'undefined'){
        var chartCanvases = $(".chart_canvas");
        chartCanvases.empty();
        chartCanvases.each(function(){
            var thisCanvas = $(this);
            var thisCanvasSource = thisCanvas.attr('data-source');
            var thisCanvasChart = new Chart(thisCanvas, thisStarSettings[thisCanvasSource]);
            starForceChartCanvases.push(thisCanvasChart);
            });
        }

    // Define a click event for the full-screen toggle button
    var resizeTimeout = false;
    var xforceToggleFunction = function(e){
        if (typeof e != 'undefined'){ e.preventDefault(); }
        var expanded = thisContainer.hasClass('xforce') ? true : false;
        if (expanded){ thisContainer.removeClass('xforce'); }
        else { thisContainer.addClass('xforce'); }
        for (i in starForceChartCanvases){
            var thisCanvasChart = starForceChartCanvases[i];
            thisCanvasChart.resize();
            }
        };
    var xforceToggle = $('.starforce .size_toggle', thisContainer);
    xforceToggle.bind('click', function(e){ return xforceToggleFunction(e); });

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
    //console.log('windowResizeStarforce()');



    refreshStarchart();
    return true;

}

// Define a function for updating the star menu elements
function refreshStarchart(){
    //console.log('refreshStarchart()');

    // Collect reference to key starchart objects
    var thisStarchart = $('.starchart', thisBody);
    var currentGroups = $('.grouplist .group.current', thisStarchart);

    // Collect the current top and side groups
    var currentTopGroup = $('.grouplist.topbar', thisStarchart).attr('data-current');
    var currentSideGroup = $('.grouplist.sidebar', thisStarchart).attr('data-current');

    // Update the bullet containers with the new top and side
    $('.bullets .bull', thisStarchart).removeClass('current');
    $('.bullets.topbar .bull[data-group="'+currentTopGroup+'"]', thisStarchart).addClass('current');
    $('.bullets.sidebar .bull[data-group="'+currentSideGroup+'"]', thisStarchart).addClass('current');

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

    //console.log('visibleTopKeys', visibleTopKeys);
    //console.log('visibleSideKeys', visibleSideKeys);

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

// Define a function that checks each arrow button to see if it should be enabled or not
function refreshArrowButtons(){
    var $thisBody = $('#mmrpg');
    var $groupLists = $('.starchart .grouplist', $thisBody);
    var $arrowButtons = $('.arrow[data-dir]', $groupLists);
    //console.log('$groupLists =', $groupLists.length, $groupLists);
    //console.log('$arrowButtons =', $arrowButtons.length, $arrowButtons);
    $arrowButtons.each(function(){
        var $thisButton = $(this);
        var thisDirection = $thisButton.attr('data-dir');
        var thisGroupList = $thisButton.closest('.grouplist');
        var thisCurrentGroup = thisGroupList.attr('data-current');
        var thisCurrentGroupContainer = thisGroupList.find('.group[data-group="'+thisCurrentGroup+'"]');
        var thisNewGroupContainer = thisDirection == 'prev' ? thisCurrentGroupContainer.prev('.group') : thisCurrentGroupContainer.next('.group');
        var thisNewGroup = thisNewGroupContainer.attr('data-group');
        var thisButtonEnabled = thisNewGroupContainer.length ? true : false;
        if (thisButtonEnabled){ $thisButton.removeClass('disabled'); }
        else { $thisButton.addClass('disabled'); }
        });
}