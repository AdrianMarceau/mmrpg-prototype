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

        // STAR GRID ARROWS & AVATARS

        // Add hover and click sounds to the buttons in star grid
        $('.starchart .grouplist .arrow', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'icon-hover', {volume: 0.5});
            });
        $('.starchart .grouplist .group .icon', thisContext).live('mouseenter', function(){
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

    // Define the click events for the view toggle buttons in the header
    let $viewToggle = $('.toggle[data-view]', thisPrototype);
    let $viewContent = $('.content[data-view]', thisPrototype);
    if ($viewToggle && $viewToggle.length
        && $viewContent && $viewContent.length){
        let switchContentView = function(newView){
            let currentView = $viewContent.attr('data-view');
            if (newView === currentView){ return; }
            $viewToggle.attr('data-view', newView);
            $viewContent.attr('data-view', newView);
            };
        $('.option[data-view]', $viewToggle).bind('click', function(){
            let $option = $(this);
            let view = $option.attr('data-view');
            switchContentView(view);
            });
        }

    // Expose the pagination function so the resize handler can call it
    window.showStarListPage = function(pageNum){
        $starListPages.attr('data-current-page', pageNum);
        $('a[data-page]', $starListPages).removeClass('active');
        $starListPages.find('a[data-page="' + pageNum + '"]').addClass('active');
        let numPerPage = parseInt($starListPages.attr('data-per-page')) || 32;
        let layoutMode = $starListPages.attr('data-layout-mode') || 'narrow';
        // Calculate the slice indexes for the current page
        let minIndex = (pageNum - 1) * numPerPage;
        let maxIndex = (pageNum * numPerPage) - 1;
        // Grab all robots, but filter out unknowns if we are in wide mode
        let $allRobots = $('.robot', $starListRobots);
        let $targetRobots = layoutMode === 'wide' ? $allRobots.not('.unknown') : $allRobots;
        // Hide absolutely everything first
        $allRobots.addClass('hidden');
        // Loop through our filtered targets and reveal only the ones that fall within our page slice
        $targetRobots.each(function(index){
            if (index >= minIndex && index <= maxIndex){
                $(this).removeClass('hidden');
                }
            });
        };

    // Define click events for the prev, next, and page buttons in the starlist
    let $starList = $('.starlist', thisBody);
    if (!$starList || !$starList.length){ $starList = null; }
    let $starListPages = $starList ? $('.pages', $starList) : null;
    if (!$starListPages || !$starListPages.length){ $starListPages = null; }
    let $starListRobots = $starList ? $('.robots', $starList) : null;
    if (!$starListRobots || !$starListRobots.length){ $starListRobots = null; }
    if ($starList && $starListPages){
        // Use .delegate() for low jquery version compatibility
        $starListPages.delegate('a[data-page]', 'click', function(e){
            e.preventDefault();
            let $pageLink = $(this);
            let pageNum = $pageLink.attr('data-page');
            if (pageNum === 'prev' || pageNum === 'next'){
                let shiftDirection = pageNum;
                let currentPageNum = $starListPages.is('[data-current-page]') ? parseInt($starListPages.attr('data-current-page')) : 1;
                // Dynamically check the current amount of page buttons
                let maxPageNum = $('a[data-page]:not(.arrow)', $starListPages).length || 1;
                let newPageNum = shiftDirection === 'prev' ? currentPageNum - 1 : currentPageNum + 1;
                if (newPageNum < 1){ newPageNum = maxPageNum; }
                else if (newPageNum > maxPageNum){ newPageNum = 1; }
                window.showStarListPage(newPageNum);
                } else {
                let newPageNum = parseInt(pageNum);
                window.showStarListPage(newPageNum);
                }
            });
        window.showStarListPage(1);
        }
    /*
    if ($starList && $starListPages){
        let showStarListPage = function(pageNum){
            //console.log('showStarListPage() w/ pageNum =', pageNum);
            $starListPages.attr('data-current-page', pageNum);
            $('a[data-page]', $starListPages).removeClass('active');
            $starListPages.find('a[data-page="' + pageNum + '"]').addClass('active');
            let numPerPage = $starListPages.is('[data-per-page]') ? parseInt($starListPages.attr('data-per-page')) : 1;
            let minKey = (pageNum - 1) * numPerPage;
            let maxKey = (pageNum * numPerPage) - 1;
            $('.robot[data-key]', $starListRobots).each(function(){
                let $robot = $(this);
                let key = parseInt($robot.attr('data-key'));
                if (key >= minKey && key <= maxKey){ $robot.removeClass('hidden'); }
                else { $robot.addClass('hidden'); }
                });
            };
        $('a[data-page]', $starListPages).bind('click', function(e){
            e.preventDefault();
            let $pageLink = $(this);
            let pageNum = $pageLink.attr('data-page');
            //console.log('starlist page clicked, pageNum =', pageNum);
            if (pageNum === 'prev' || pageNum === 'next'){
                let shiftDirection = pageNum;
                let currentPageNum = $starListPages.is('[data-current-page]') ? parseInt($starListPages.attr('data-current-page')) : 1;
                let maxPageNum = (function(){ let $realPages = $('a[data-page]:not(.arrow)', $starListPages); return $realPages ? $realPages.length : 0; })();
                let newPageNum = shiftDirection === 'prev' ? currentPageNum - 1 : currentPageNum + 1;
                if (newPageNum < 1){ newPageNum = maxPageNum; }
                else if (newPageNum > maxPageNum){ newPageNum = 1; }
                //console.log('moving', shiftDirection, 'from', currentPageNum, 'to', newPageNum);
                showStarListPage(newPageNum);
                }
            else {
                let newPageNum = parseInt(pageNum);
                showStarListPage(newPageNum);
                }
            });

        }
    */

    // Define click events for the prev and next arrow buttons in the starchart
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
    // Check window width to determine layout mode (>= 1120px)
    let isWideMode = $(window).width() >= gameSettings.wideWindowWidth;
    let newPerPage = isWideMode ? 64 : 32;
    let layoutMode = isWideMode ? 'wide' : 'narrow';
    let $starList = $('.starlist', thisBody);
    let $starListPages = $('.pages', $starList);
    let $starListRobots = $('.robots', $starList);
    if ($starListPages.length && $starListRobots.length){
        let currentPerPage = parseInt($starListPages.attr('data-per-page')) || 32;
        let currentLayout = $starListPages.attr('data-layout-mode');
        // Only regenerate if the layout mode or capacity has actually changed
        if (newPerPage !== currentPerPage || layoutMode !== currentLayout){
            $starListPages.attr('data-per-page', newPerPage);
            $starListPages.attr('data-layout-mode', layoutMode);
            // Count ONLY the robots we intend to show in this mode
            let $targetRobots = isWideMode ? $('.robot:not(.unknown)', $starListRobots) : $('.robot', $starListRobots);
            let totalRobots = $targetRobots.length;
            let numPages = Math.ceil(totalRobots / newPerPage);
            // Rebuild the pagination HTML dynamically
            let pageLinksHtml = '<a class="arrow prev" data-page="prev"></a>';
            for (let i = 1; i <= numPages; i++){
                pageLinksHtml += '<a class="page' + (i === 1 ? ' active' : '') + '" data-page="' + i + '">Page ' + i + '</a>';
                }
            pageLinksHtml += '<a class="arrow next" data-page="next"></a>';
            $starListPages.html(pageLinksHtml);
            // Ensure the current page doesn't exceed the new total page count
            let currentPage = parseInt($starListPages.attr('data-current-page')) || 1;
            if (currentPage > numPages) { currentPage = numPages; }
            // Apply the correct visibilities
            if (typeof window.showStarListPage === 'function'){
                window.showStarListPage(currentPage);
                }
            }
        }
    refreshStarchart();
    return true;
}

/*
// Create the windowResize event for this page
function windowResizeStarforce(){
    //console.log('windowResizeStarforce()');



    refreshStarchart();
    return true;

}
*/

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
        let thisGroup = $(this);
        let thisGroupOptions = thisGroup.find('.option');
        thisGroupOptions.each(function(){
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

// Define a star-specific function to call when polling user input variables
function checkUserInputsForStarsFrame(kind, event, activeInputs, userInputs){
    //console.log('%c' + 'prototypeReady.checkUserInputsForStarsFrame()', 'color: magenta;');
    let _self = this;
    let $thisPrototype = $mmrpgElements.thisPrototype;
    let playSoundEffect = mmrpgPrototype.playSoundEffect;
    //console.log('-> playSoundEffect:', typeof playSoundEffect, playSoundEffect);
    //console.log('-> $thisPrototype:', typeof $thisPrototype, $thisPrototype);

    // Collect references to available containers, views, and buttons before starting
    let $thisViewsToggle = $('.header_types .toggle[data-view]', $thisPrototype);
    let $availableViews = $('.option[data-view]', $thisViewsToggle);
    let currentView = $thisViewsToggle.attr('data-view');
    let $thisStarsContent = $('.content.stars', $thisPrototype);
    let $availableContainers = $('.wrapper .container', $thisStarsContent);
    let $availableContainersFiltered;
    if (currentView === 'list'){ $availableContainersFiltered = $availableContainers.filter(function(){ return $(this).is('.starlist') || $(this).is('.starchart'); }); }
    else if (currentView === 'stats'){ $availableContainersFiltered = $availableContainers.filter(function(){ return $(this).is('.starforce'); }); }
    //console.log('-> $thisViewsToggle:', ($thisViewsToggle ? $thisViewsToggle.length : 0), typeof $thisViewsToggle, $thisViewsToggle);
    //console.log('-> $availableViews:', ($availableViews ? $availableViews.length : 0), typeof $availableViews, $availableViews);
    //console.log('-> currentView:', typeof currentView, currentView);
    //console.log('-> $thisStarsContent:', ($thisStarsContent ? $thisStarsContent.length : 0), typeof $thisStarsContent, $thisStarsContent);
    //console.log('-> $availableContainers:', ($availableContainers ? $availableContainers.length : 0), typeof $availableContainers, $availableContainers);
    //console.log('-> $availableContainersFiltered:', ($availableContainersFiltered ? $availableContainersFiltered.length : 0), typeof $availableContainersFiltered, $availableContainersFiltered);

    // COMMON CONTROLS: Try to keep these consistent!

    // If the user pressed the X button, we should scroll through visible containers
    if (activeInputs.X){
        //console.log('%c' + 'X button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        let $activeContainer = $availableContainersFiltered.filter('.container_active');
        let activeContainerIndex = $activeContainer && $activeContainer.length ? $availableContainersFiltered.index($activeContainer) : -1;
        let maxContainerIndex = $availableContainersFiltered.length - 1;
        let nextContainerIndex = activeContainerIndex + 1;
        if (nextContainerIndex > maxContainerIndex){ nextContainerIndex = 0; }
        let $nextContainer = $availableContainersFiltered.eq(nextContainerIndex);
        if ($nextContainer && $nextContainer.length){
            //$nextContainer.trigger('mouseenter');
            //$nextContainer.trigger('click');
            $availableContainers.removeClass('container_active');
            $nextContainer.addClass('container_active');
            playSoundEffect('icon-click-mini');
            }
        return;
        }

    // If the user pressed the L2/R2 button2, we should scroll through visible views
    if (activeInputs.L2 || activeInputs.R2){
        //console.log('%c' + (activeInputs.L2 ? 'L2' : 'L1') + ' trigger button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        let $activeView = $availableViews.filter('.option[data-view="' + currentView + '"]');
        let activeViewIndex = $activeView && $activeView.length ? $availableViews.index($activeView) : -1;
        let maxViewIndex = $availableViews.length - 1;
        let nextViewIndex = activeInputs.L2 ? 0 : maxViewIndex;
        if (nextViewIndex > maxViewIndex){ nextViewIndex = 0; }
        let $nextView = $availableViews.eq(nextViewIndex);
        if ($nextView && $nextView.length){
            $nextView.trigger('mouseenter');
            $nextView.trigger('click');
            }
        return;
        }

    // STARFORCE CONTROLS: These controls only apply to the star menu

    // If the user pressed a directional button, we should try to scroll through pages
    if (activeInputs.Up || activeInputs.Down
        || activeInputs.Left || activeInputs.Right){
        let whichDirection = activeInputs.Up ? 'up' : activeInputs.Down ? 'down' : activeInputs.Left ? 'left' : activeInputs.Right ? 'right' : '';
        //console.log('%c' + 'Directional button (' + whichDirection.toUpperCase() + ') pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }
        let $activeContainer, activeContainerKind;
        $activeContainer = $availableContainersFiltered.filter('.container_active');
        //console.log('-> $activeContainer:', ($activeContainer ? $activeContainer.length : 0), typeof $activeContainer, $activeContainer);
        if (!$activeContainer || !$activeContainer.length){ return; }
        if ($activeContainer.is('.starlist')){  activeContainerKind = 'starlist'; }
        else if ($activeContainer.is('.starchart')){  activeContainerKind = 'starchart'; }
        //console.log('activeContainerKind = ', activeContainerKind);
        if (!activeContainerKind){ return; }
        let $scrollButtons = {};
        if (activeContainerKind === 'starlist'){
            $scrollButtons.left = $('.pages .arrow.prev', $activeContainer);
            $scrollButtons.right = $('.pages .arrow.next', $activeContainer);
            } else if (activeContainerKind === 'starchart'){
            $scrollButtons.up = $('.grouplist.sidebar .arrow.prev', $activeContainer);
            $scrollButtons.down = $('.grouplist.sidebar .arrow.next', $activeContainer);
            $scrollButtons.left = $('.grouplist.topbar .arrow.prev', $activeContainer);
            $scrollButtons.right = $('.grouplist.topbar .arrow.next', $activeContainer);
            }
        let $clickButton;
        if (typeof $scrollButtons[whichDirection] === 'object'
            && $scrollButtons[whichDirection].length > 0
            && !$scrollButtons[whichDirection].is('.disabled')){
            $clickButton = $scrollButtons[whichDirection];
            }
        if (!$clickButton || !$clickButton.length){ return; }
        $clickButton.trigger('mouseenter');
        $clickButton.trigger('click');
        return;
        }

    /*
    // If the user pressed the A button, we should ?????
    if (activeInputs.A){
        //console.log('%c' + 'A button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }

        return;
        }
    // If the user pressed the B button, we should ?????
    if (activeInputs.B){
        //console.log('%c' + 'B button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }

        return;
        }
    // If the user pressed the Y button, we should ?????
    if (activeInputs.Y){
        //console.log('%c' + 'Y button pressed!', 'color: orange;');
        if (event){ event.preventDefault(); }

        return;
        }
    */


}