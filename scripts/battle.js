
// Define global variables
let $mmrpgWrapper = false;
var $thisBattle = false;
var $rogueStar = false;

// Expand the game settings object with a variable battle specific data
gameSettings.currentGameState = {}; // default to empty but may be filled at runtime and used later
gameSettings.currentBattleData = {};
gameSettings.currentBattleState = {};
gameSettings.battleLoaded = false;
gameSettings.battleHasStarted = false;

// Create the document ready events
$(document).ready(function(){
    $mmrpgWrapper = $('#mmrpg');
    $thisBattle = $('#battle', $mmrpgWrapper);
    $thisCanvas = $('#canvas', $mmrpgWrapper);

    // Preload battle related image files
    mmrpg_preload_assets();

    // Make sure we mark the game as loaded when assets are done
    let wait = new mmrpgWaitForIt();
    wait.waitFor('foobar', function(){ setTimeout(function(){ wait.doneWaitingFor('foobar'); }, 1000); });
    wait.waitFor('images', function(){ $mmrpgWrapper.waitForImages(function(){ wait.doneWaitingFor('images'); }); });
    wait.onWaitComplete(function(){ /*console.log('onWaitComplete() // gameHasLoaded');*/ gameSettings.gameHasLoaded = true; });
    wait.startWaiting();

    // Attempt to define the top frame
    var topFrame = window.top;
    if (typeof topFrame.myFunction != 'function'){ topFrame = window.parent; }

    // Attempt o notify the top frame of loaded if necessary
    if (typeof topFrame.mmrpg_toggle_index_loaded == 'function'){
        topFrame.mmrpg_toggle_index_loaded(true);
    }

    // Bind an event to the window resize so we can check devicePixelRatio and adjust rendering if needed
    let $mmrpgDiv = $('#mmrpg');
    $(window).bind('resize', function(e){
        //console.log('%c' + 'Battle window resize event!', 'color: cyan;');
        //e.preventDefault();
        //e.stopPropagation();
        //console.log('-> event:', e);
        //console.log('-> window.devicePixelRatio:', window.devicePixelRatio);
        let pixelRatio = window.devicePixelRatio || 1;
        let imageRendering = pixelRatio === 1 || pixelRatio % 2 === 0 ? 'pixelated' : 'auto';
        //console.log('-> pixelRatio:', pixelRatio, '\n', '-> imageRendering:', imageRendering);
        $mmrpgDiv.attr('data-rendering', imageRendering);
        }).trigger('resize');

    // Fade in the battle screen slowly
    var thisContext = $('#battle');
    if (thisContext.hasClass('fastfade')){
        // Fade the battle in quickly, starting the action trigger ASAP
        thisContext.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, Math.ceil(gameSettings.eventTimeout * 3), 'swing');
        // Collect all the elements to be animated
        var canvasContext = $('#canvas', thisContext);
        thisContext.waitForImages(function(){
            $rogueStar.addClass('hidelabel');
            // Automatically send the start action to the data api
            $('#animate').css({opacity:1});
            $('#canvas .canvas_overlay_header').css({opacity:1}).removeClass('canvas_overlay_hidden');
            mmrpg_start_animation();
            gameSettings.battleLoaded = true;
            gameSettings.gameHasLoaded = true;
            $('#mmrpg').removeClass('loading');
            mmrpg_action_trigger('start', false);
            }, false, true);
        }
    else {
        // Fade the battle in normally, one layer at a time before loading
        thisContext.css({opacity:0}).removeClass('hidden').animate({opacity:1.0}, Math.ceil(gameSettings.eventTimeout * 3), 'swing', function(){
            //console.log('fade in the context');
            // Collect all the elements to be animated
            var canvasContext = $('#canvas', thisContext);
            thisContext.waitForImages(function(){
                //console.log('images are ready');
                $rogueStar.addClass('hidelabel');
                // Fade the battle canvas startup elements into view
                mmrpg_battle_fadein_background(canvasContext, Math.ceil(gameSettings.eventTimeout * 2), function(){
                    //console.log('background has faded in');
                    // Fade in the foreground now so it loads at the same time as the robots
                    mmrpg_battle_fadein_foreground(canvasContext, Math.ceil(gameSettings.eventTimeout * 1), function(){
                        //console.log('foreground has faded in');
                        if (!gameSettings.battleHasStarted){
                            // Automatically send the start action to the data api
                            $('#animate').css({opacity:1});
                            $('#canvas .canvas_overlay_header').animate({opacity:1}, Math.ceil(gameSettings.eventTimeout * 2), 'swing', function(){ $(this).removeClass('canvas_overlay_hidden'); });
                            mmrpg_start_animation();
                            gameSettings.gameHasLoaded = true;
                            gameSettings.battleLoaded = true;
                            mmrpg_action_trigger('start', false);
                            gameSettings.battleHasStarted = true;
                            }
                        });
                    });
                }, false, true);
            });
        }

    // -- SOUND EFFECT FUNCTIONALITY -- //

    // Define some interaction sound effects for the battle menu
    if (typeof playSoundEffect !== 'undefined'){
        //console.log('assigning sound effects w/ soundEffectFunction:', soundEffectFunction);

        // MENU LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#actions .main_actions .button', $thisBattle).live('mouseenter', function(){
            //console.log('this = ', this);
            playSoundEffect.call(this, 'icon-hover');
            });
        $('#actions .main_actions .button', $thisBattle).live('click', function(){
            //console.log('this = ', this);
            playSoundEffect.call(this, 'icon-click');
            });

        // Add hover and click sounds to any buttons in the sub menu
        let ignoreSubButtons = false, ignoreSubButtonsTimeout = null;
        $('#actions .sub_actions .button', $thisBattle).live('mouseenter', function(){
            if (ignoreSubButtons){ return; }
            if ($(this).is('.action_back')){ playSoundEffect.call(this, 'back-hover', {volume: 0.5}); }
            else { playSoundEffect.call(this, 'icon-hover', {volume: 1.0}); }
            });
        $('#actions .sub_actions .button', $thisBattle).live('click', function(){
            if (ignoreSubButtons){ return; }
            if ($(this).is('.action_back')){ playSoundEffect.call(this, 'back-click', {volume: 0.5}); }
            else { playSoundEffect.call(this, 'icon-click', {volume: 1.0}); }
            ignoreSubButtons = true;
            if (ignoreSubButtonsTimeout){ clearTimeout(ignoreSubButtonsTimeout); }
            ignoreSubButtonsTimeout = setTimeout(function(){ ignoreSubButtons = false; }, 100);
            });

        }

    // Collect a reference to the continue button
    //var actionContinue = $('.action_continue', gameActions);

    // Create an event for the button hover
    $('.button', gameActions).live('hover', function(){
        //console.log('hover?');
        $('.button', gameActions).removeClass('button_hover');
        if (!$(this).hasClass('button_disabled')){
            $(this).addClass('button_hover');
            }
        });

    // Define a quick function for gennerating a buttonRows matrix for keyboard/controller interactions
    let lastWrapperToken = false; // for saving which wrapper we last generated the matrix for
    let lastWrapperPage = false; // for saving which subpage of a given wrapper we're viewing
    let lastWrapperButtons = []; // for saving the rows of buttons that make-up the matrix
    let lastWrapperPosition = []; // for saving the last row/col for quicker lookups on repeat
    let generateButtonMatrix = function($currentWrapper, currentWrapperToken, currentWrapperPage){
        // Menus work in the following way;
        // -> Up top are "Main Buttons" (1-8, w/ 4-per-row), which are usually individual actions within a given category (abilities, items, teammates, etc.)
        // (one exception: the main 'battle' wrapper which just has one big "Ability" button up-top)
        // -> On the bottom are "Sub Buttons" (1-4, w/ 1-row), which is usually a single "Back" button going to whatever the previous wrapper was
        // (one exception: the main 'battle' wrapper uses this area to list the Switch, [sometimes Item], Option, and Scan buttons)
        // As mentioned, there's often more than one main-button (max eight) and they can be split across up to two "rows" (four each).
        // This means that up and down wont automatically lock to main/sub actions but instead need to behave as if there a "grid"
        // of buttons and allow navigating buttons as if table cells.  The best way to do this is to first construct an array mapping
        // the buttons to the "rows" they would appear on, and then using that to navigate between them in a more programatic
        // way.  We create the buttonRows array, grab all the mainActionButtons, and then add them up to 4-per-row, and then
        // as a final row we add whatever subActionButtons exist (also up to 4).  Then when the user presses up/down we can check
        // which row they're on and move them up or down accordingly, and left/right is just a matter of moving within the row.
        // we should add empty placeholders to the buttonRows array so that the left/right movement is consistent across rows.
        // and then detect that on-press and auto-move to next row if empty so the user doesn't have to press twice when few buttons.
        //console.log('generateButtonMatrix() for', currentWrapperToken);
        if (!currentWrapperPage){ currentWrapperPage = lastWrapperPage ? lastWrapperPage : 1; }
        if (currentWrapperToken === lastWrapperToken
            && currentWrapperPage === lastWrapperPage){
            return lastWrapperButtons;
            }
        //console.log('-> currentWrapperToken(', currentWrapperToken, ') !== lastWrapperToken(', lastWrapperToken, ')');
        //console.log('-> OR currentWrapperPage(', currentWrapperPage, ') !== currentWrapperPage(', currentWrapperPage, ')');
        //console.log('generateButtonMatrix() w/', '\n', ' currentWrapperToken:', currentWrapperToken, '\n', 'currentWrapperPage:', currentWrapperPage);
        //console.log('...generating new buttonRows array');
        lastWrapperButtons = [];
        lastWrapperPosition = [];
        lastWrapperToken = currentWrapperToken;
        lastWrapperPage = currentWrapperPage;
        let buttonRows = [];
        //let buttonSelector = '.button:visible:not(.button_disabled):not(.float_links *)';
        let buttonSelector = '.button:visible:not(.float_links *)';
        let $currentMainActions = $('.main_actions', $currentWrapper);
        let $currentSubActions = $('.sub_actions', $currentWrapper);
        let $currentMainActionButtons = $(buttonSelector, $currentMainActions);
        let $currentSubActionButtons = $(buttonSelector, $currentSubActions);
        let mainActionButtons = $currentMainActionButtons.toArray();
        let subActionButtons = $currentSubActionButtons.toArray();
        /*
        // slice the main action buttons to only the ones we should be seeing on the current page
        let buttonsPerRow = 4;
        let rowsPerPage = 2;
        let buttonsPagePage = buttonsPerRow * rowsPerPage;
        let startIndex = (lastWrapperPage - 1) * buttonsPagePage;
        let endIndex = startIndex + buttonsPagePage;
        mainActionButtons = mainActionButtons.slice(startIndex, endIndex);
        */
        // now build the buttonRows array
        let rowCount = Math.ceil(mainActionButtons.length / 4);
        for (let r = 0; r < rowCount; r++){
            buttonRows[r] = [];
            for (let i = 0; i < 4; i++){
                let buttonIndex = (r * 4) + i;
                if (typeof mainActionButtons[buttonIndex] !== 'undefined'){
                    buttonRows[r].push(mainActionButtons[buttonIndex]);
                    } else {
                    buttonRows[r].push(null);
                    }
                }
            }
        if (subActionButtons.length){
            let subRow = [];
            for (let i = 0; i < 4; i++){
                if (typeof subActionButtons[i] !== 'undefined'){
                    subRow.push(subActionButtons[i]);
                    } else {
                    subRow.push(null);
                    }
                }
            buttonRows.push(subRow);
            }
        // as a safe-measure, remove any button rows that are only nulls
        buttonRows = buttonRows.filter(function(row){ return row.some(function(btn){ return btn !== null; }); });
        // now go through and replace any remaining nulls with whatever button was before them (duplicate) to form a full grid
        for (let r = 0; r < buttonRows.length; r++){
            let row = buttonRows[r];
            for (let i = 0; i < row.length; i++){
                if (row[i] === null){
                    let prevIndex = i - 1;
                    if (prevIndex < 0){ prevIndex = 0; }
                    row[i] = row[prevIndex];
                    }
                }
            buttonRows[r] = row;
            }
        // assign the results to lastWrapperButtons and return
        lastWrapperButtons = buttonRows;
        // return the buttonRows array
        //console.log('returning buttonRows:', buttonRows);
        return buttonRows;
        };

    // Define a function to run each time user inputs are updated so we can react
    let battleIsBusy = function(){ return gameSettings.currentActionPanel === 'loading' || getPendingEventsCount() > 0 ? true : false; };
    let listenForInput = function(){ return Date.now() >= nextInputAllowedTime; }, nextInputAllowedTime = 0;
    let ignoreInputFor = function(delay){ delay = typeof delay === 'number' ? delay : 250; nextInputAllowedTime = Date.now() + delay; };
    let checkUserInputs = function(kind, event, activeInputs, userInputs){
        //console.log('%c' + 'mmrpgBattleWindow.checkUserInputs(kind:' + kind + ', event)', 'color: cyan;');
        if (!listenForInput()){ return false; }
        if (battleIsBusy()){ return false; }
        if (!Object.keys(activeInputs).length){ return false; } // nothing pressed, ignore
        //console.log('-> gameSettings.currentActionPanel:', gameSettings.currentActionPanel);
        //console.log('-> activeInputs:', activeInputs);
        ignoreInputFor();
        // Quickly check to see which menu we're on first
        let $battleActions = $('#actions', $thisBattle);
        let $currentWrapper = $('.wrapper:visible', $battleActions).first();
        let currentWrapperToken = $currentWrapper.attr('id').replace('actions_', '');
        //console.log('-> currentWrapperToken:', currentWrapperToken);
        // No matter where the user is, pressing the start button should pause the game
        if (activeInputs.Start){
            //console.log('%c' + 'Start button pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            mmrpg_toggle_animation();
            return;
            }
        // By the same token, if the user has pressed select, we should toggle the overlay
        if (activeInputs.Select){
            //console.log('%c' + 'Select button pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            var newValue = !gameSettings.screenshotMode ? true : false;
            mmrpg_toggle_screenshot_mode(newValue);
            parent.mmrpg_toggle_screenshot_mode(newValue);
            return;
            }
        // Otherwise, if there are currently events in the queue, don't allow any other buttons except continuing
        if (mmrpgEvents.length){
            let allowClick = false;
            if (activeInputs.A
                && gameSettings.idleAnimation === false
                && !$(':animated', gameCanvas).length){
                allowClick = true; // TLDR: if the game is paused, allow auto-advancing with (A), otherwise ignore all inputs
                }
            if (!allowClick){ return false; }
            }
        // With those out of the way, let's continue with normal menu interaction processing
        //let buttonSelector = '.button:visible:not(.button_disabled):not(.float_links *)';
        let buttonSelector = '.button:visible:not(.float_links *)';
        let hoverButtonSelector = buttonSelector+'.button_hover';
        let $currentMainActions = $('.main_actions', $currentWrapper);
        let $currentSubActions = $('.sub_actions', $currentWrapper);
        let $currentFloatLinks = $('.float_links', $currentMainActions);
        let $currentButtons = $(buttonSelector, $currentWrapper);
        let $currentMainActionButtons = $(buttonSelector, $currentMainActions);
        let $currentSubActionButtons = $(buttonSelector, $currentSubActions);
        let $hoverButton = $(hoverButtonSelector, $currentWrapper);
        let $continueButtton = $('.action_continue', $battleActions);
        let $firstButton = $currentButtons.first();
        let buttonRows = generateButtonMatrix($currentWrapper, currentWrapperToken);
        // If the user has pressed the confirm (A) button
        // then confirm whatever input is currently selected
        if (activeInputs.A){ // A button
            //console.log('%c' + 'A button pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            let $buttonToClick = false;
            if ($continueButtton.length
                && $continueButtton.is(':visible')
                && $continueButtton.not('.button_disabled')){
                $buttonToClick = $continueButtton;
                //console.log('setting $buttonToClick to $continueButtton:', $continueButtton.length, $continueButtton);
                }
            else if ($hoverButton.length
                && $hoverButton.is(':visible')
                && $hoverButton.not('.button_disabled')){
                $buttonToClick = $hoverButton;
                //console.log('setting $buttonToClick to $hoverButton:', $hoverButton.length, $hoverButton);
                }
            else if ($firstButton.length
                && $firstButton.is(':visible')
                && $firstButton.not('.button_disabled')){
                $buttonToClick = $firstButton;
                //console.log('setting $buttonToClick to $firstButton:', $firstButton.length, $firstButton);
                }
            if ($buttonToClick){
                //console.log('clicking $buttonToClick w/', $buttonToClick.length, $buttonToClick);
                //console.log('$buttonToClick is disabled? ', $buttonToClick.is('.button_disabled'));
                $buttonToClick.trigger('click');
                if ($buttonToClick.is('[data-panel]')){
                    let $newWrapper = $('#actions_' + $buttonToClick.attr('data-panel'), $battleActions);
                    let $newButtons = $(buttonSelector, $newWrapper);
                    let $hoverButton = $(hoverButtonSelector, $newWrapper);
                    if (!$hoverButton.length || $hoverButton.is('.action_back')){
                        let $newFirstButton = $newButtons.first();
                        //console.log('$newFirstButton set to', $newFirstButton.length, $newFirstButton);
                        if ($newFirstButton.length){
                            $(buttonSelector, $newWrapper).removeClass('button_hover');
                            $newFirstButton.addClass('button_hover');
                            lastWrapperPosition = [0,0];
                            }
                        }
                    }
                return true;
                }
            else {
                return false;
                }
            }
        // If the user has pressed a back (B) button
        // then find and click the back button if it exists
        if (activeInputs.B){ // B button
            //console.log('%c' + 'B button pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            if ($currentSubActions.is(':visible')){
                let $backButton = $currentSubActionButtons.first();
                if ($backButton.length && $backButton.is('.action_back')){
                    $backButton.trigger('click');
                    return true;
                    }
                }
            }
        // If the user has pressed an info (Y) button
        // then auto-click any tooltip button if it exists
        // (tooltips are spans within the hovered button with [data-click-tooltip])
        if (activeInputs.Y){ // Y button
            //console.log('%c' + 'Y button pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            if ($hoverButton.length){
                let $tooltipSpan = $('span[data-click-tooltip]', $hoverButton).first();
                if ($tooltipSpan.length){
                    $tooltipSpan.trigger('click');
                    return true;
                    }
                }
            }
        //If the user has pressed a D-Pad or Left Stick direction
        if (activeInputs.Up || activeInputs.Down || activeInputs.Left || activeInputs.Right){ // D-Pad or Left Stick
            //console.log('%c' + 'D-Pad or Left Stick direction pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            if (currentWrapperToken === 'battle'){
                //console.log('%c' + 'Direction input on the BATTLE menu panel...', 'color: orange;');
                // battle menu only has two rows, the big "ability" button up top, then the 3-4 sub-buttons
                // so when the player presses up it's always the ability button, down is always the first sub-button
                // and then from there, if the player is in the sub-row, the left right let them move left/right duh
                if (activeInputs.Up){
                    //console.log('%c' + 'Direction input UP on the BATTLE menu panel...', 'color: orange;');
                    $currentButtons.removeClass('button_hover');
                    let $firstMainButton = $currentMainActionButtons.first();
                    if ($firstMainButton.length){
                        $firstMainButton.addClass('button_hover');
                        playSoundEffect.call(this, 'icon-hover');
                        return true;
                        }
                    }
                else if (activeInputs.Down){
                    //console.log('%c' + 'Direction input DOWN on the BATTLE menu panel...', 'color: orange;');
                    $currentButtons.removeClass('button_hover');
                    let $firstSubButton = $currentSubActionButtons.first();
                    if ($firstSubButton.length){
                        $firstSubButton.addClass('button_hover');
                        playSoundEffect.call(this, 'icon-hover');
                        return true;
                        }
                    }
                else if (activeInputs.Left || activeInputs.Right){
                    //console.log('%c' + 'Direction input LEFT or RIGHT on the BATTLE menu panel...', 'color: orange;');
                    let $firstMainButton = $currentMainActionButtons.first();
                    let $firstSubButton = $currentSubActionButtons.first();
                    let $firstHoverButton = $(hoverButtonSelector, $currentWrapper).first();
                    if (!$firstHoverButton.length){
                        if ($firstMainButton.length){
                            $currentButtons.removeClass('button_hover');
                            $firstMainButton.addClass('button_hover');
                            playSoundEffect.call(this, 'icon-hover');
                            return true;
                            }
                        else if ($firstSubButton.length){
                            $currentButtons.removeClass('button_hover');
                            $firstSubButton.addClass('button_hover');
                            playSoundEffect.call(this, 'icon-hover');
                            return true;
                            }
                        }
                    if ($firstHoverButton.is($currentSubActionButtons)){
                        $currentButtons.removeClass('button_hover');
                        let hoverIndex = $currentSubActionButtons.index($firstHoverButton);
                        let nextIndex = hoverIndex;
                        if (activeInputs.Left){ nextIndex = hoverIndex - 1; }
                        else if (activeInputs.Right){ nextIndex = hoverIndex + 1; }
                        if (nextIndex < 0){ nextIndex = $currentSubActionButtons.length - 1; }
                        if (nextIndex >= $currentSubActionButtons.length){ nextIndex = 0; }
                        let $nextButton = $currentSubActionButtons.eq(nextIndex);
                        if ($nextButton.length){
                            $nextButton.addClass('button_hover');
                            playSoundEffect.call(this, 'icon-hover');
                            return true;
                            }
                        }
                    }
                    // any other buttons supported?
                } else {
                //console.log('%c' + 'Direction input on the (sub) ' + currentWrapperToken.toUpperCase() + ' menu panel...', 'color: orange;');
                let lastPosition = lastWrapperPosition;
                //console.log('-> buttonRows:', buttonRows);
                //console.log('-> lastPosition:', lastPosition);
                // If we don't have a last position to work from, we need to find it via hover-class
                if (!lastPosition.length){
                    //console.log('-> refreshing lastPosition');
                    // first check to make sure we have a hover button
                    let $firstHoverButton = $(hoverButtonSelector, $currentWrapper).first();
                    if (!$firstHoverButton.length){
                        if ($firstButton.length){
                            $currentButtons.removeClass('button_hover');
                            $firstButton.addClass('button_hover');
                            lastWrapperPosition = [0,0];
                            playSoundEffect.call(this, 'icon-hover');
                            return true;
                            }
                        }
                    // now find the hover button in the buttonRows array
                    let hoverIndex = -1;
                    let hoverRow = -1;
                    for (let r = 0; r < buttonRows.length; r++){
                        let row = buttonRows[r];
                        let index = row.indexOf($firstHoverButton[0]);
                        if (index > -1){
                            hoverIndex = index;
                            hoverRow = r;
                            break;
                            }
                        }
                    // if we found a hover button, save it as lastPosition
                    if (hoverIndex > -1 && hoverRow > -1){ lastPosition = [hoverRow, hoverIndex]; }
                    }
                // Now that we have a position (well, assuming we do), attempt to move it based on input
                if (lastPosition.length){
                    //console.log('-> using lastPosition:', lastPosition);
                    let hoverRow = lastPosition[0];
                    let hoverIndex = lastPosition[1];
                    if (hoverIndex > -1 && hoverRow > -1){
                        //console.log('hoverIndex:', hoverIndex, 'hoverRow:', hoverRow);
                        let nextIndex = hoverIndex;
                        let nextRow = hoverRow;
                        if (activeInputs.Up){
                            nextRow = hoverRow - 1;
                            if (nextRow < 0){ nextRow = buttonRows.length - 1; }
                            if (buttonRows[nextRow][nextIndex] === null){
                                // if the target is empty, keep moving in the same direction until we find something or loop back
                                let safeCount = 0;
                                while (buttonRows[nextRow][nextIndex] === null && safeCount < buttonRows.length){
                                    nextRow--;
                                    if (nextRow < 0){ nextRow = buttonRows.length - 1; }
                                    safeCount++;
                                    }
                                }
                            }
                        else if (activeInputs.Down){
                            nextRow = hoverRow + 1;
                            if (nextRow >= buttonRows.length){ nextRow = 0; }
                            if (buttonRows[nextRow][nextIndex] === null){
                                // if the target is empty, keep moving in the same direction until we find something or loop back
                                let safeCount = 0;
                                while (buttonRows[nextRow][nextIndex] === null && safeCount < buttonRows.length){
                                    nextRow++;
                                    if (nextRow >= buttonRows.length){ nextRow = 0; }
                                    safeCount++;
                                    }
                                }
                            }
                        else if (activeInputs.Left){
                            nextIndex = hoverIndex - 1;
                            if (nextIndex < 0){ nextIndex = 3; }
                            if (buttonRows[nextRow][nextIndex] === null){
                                // if the target is empty, keep moving in the same direction until we find something or loop back
                                let safeCount = 0;
                                while (buttonRows[nextRow][nextIndex] === null && safeCount < buttonRows[nextRow].length){
                                    nextIndex--;
                                    if (nextIndex < 0){ nextIndex = 3; }
                                    safeCount++;
                                    }
                                } else if (buttonRows[nextRow][nextIndex] === buttonRows[hoverRow][hoverIndex]){
                                // if the target is a duplicate of the current, try moving left one more space
                                nextIndex--;
                                if (nextIndex < 0){ nextIndex = 3; }
                                }
                            }
                        else if (activeInputs.Right){
                            nextIndex = hoverIndex + 1;
                            if (nextIndex > 3){ nextIndex = 0; }
                            if (buttonRows[nextRow][nextIndex] === null){
                                // if the target is empty, keep moving in the same direction until we find something or loop back
                                let safeCount = 0;
                                while (buttonRows[nextRow][nextIndex] === null && safeCount < buttonRows[nextRow].length){
                                    nextIndex++;
                                    if (nextIndex > 3){ nextIndex = 0; }
                                    safeCount++;
                                    }
                                } else if (buttonRows[nextRow][nextIndex] === buttonRows[hoverRow][hoverIndex]){
                                // if the target is a duplicate of the current, try moving right one more space
                                nextIndex++;
                                if (nextIndex > 3){ nextIndex = 0; }
                                }
                            }
                        if (buttonRows[nextRow][nextIndex] !== null){
                            let $nextButton = $(buttonRows[nextRow][nextIndex]);
                            $currentButtons.removeClass('button_hover');
                            $nextButton.addClass('button_hover');
                            playSoundEffect.call(this, 'icon-hover');
                            lastWrapperPosition = [nextRow, nextIndex];
                            let $tooltip = $('#mmrpg-tooltip', $mmrpgDiv);
                            let tooltipActive = $tooltip.length && $tooltip.hasClass('active');
                            //console.log('-> nextRow:', nextRow, 'nextIndex:', nextIndex, 'lastWrapperPosition:', lastWrapperPosition, '$nextButton:', $nextButton, '$tooltip:', $tooltip, 'tooltipActive:', tooltipActive);
                            if (tooltipActive){
                                let $newTooltipSpan = $('span[data-click-tooltip]', $nextButton).first();
                                if ($newTooltipSpan.length){ $newTooltipSpan.trigger('click'); }
                                else { $tooltip.removeClass('active').empty(); }
                                }
                            return true;
                            }
                        }
                    }

                }
            }
        // If the player has pressed the L2+R2 button, we should try to click the top-right support button (if exists)
        if (activeInputs.LR2){
            //console.log('%c' + 'L2+R2 key pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            if ($currentFloatLinks.length){
                let $floatButtons = $('.button[data-action]:not(.num):not(.disabled)', $currentFloatLinks);
                //console.log('$currentFloatLinks =', $currentFloatLinks.length, $currentFloatLinks);
                //console.log('$floatButtons =', $floatButtons.length, $floatButtons);
                let $firstButton = $floatButtons.length && $floatButtons.length ? $floatButtons.first() : null;
                if ($firstButton){
                    //console.log('-> clicking $firstButton =', $firstButton.length, $firstButton);
                    $firstButton.addClass('button_hover');
                    $firstButton.trigger('click');
                    return true;
                    }
                }
            }
        // If the user has pressed the L1/R1 bumpers to scoll sub-pages
        // if the mainactions have .float_links and .button.num pages inside
        // then the L1/R1 buttons should scroll through them and "click"
        else if (activeInputs.L1 || activeInputs.R1){ // L1/R1 bumpers
            //console.log('%c' + 'L1 or R1 bumper pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            if ($currentFloatLinks.length){
                let $numButtons = $('.button.num:not([data-action]):not(.disabled)', $currentFloatLinks);
                let $activeButton = $numButtons.filter('[href="#' + lastWrapperPage + '"]');
                if ($activeButton.length && $numButtons.length > 1){
                    let visibleIndex = $numButtons.index($activeButton);
                    let nextIndex = visibleIndex;
                    if (activeInputs.L1){ nextIndex = visibleIndex - 1; }
                    else if (activeInputs.R1){ nextIndex = visibleIndex + 1; }
                    if (nextIndex < 0){ nextIndex = $numButtons.length - 1; }
                    if (nextIndex >= $numButtons.length){ nextIndex = 0; }
                    let $nextButton = $numButtons.eq(nextIndex);
                    if ($nextButton.length){
                        $nextButton.trigger('click');
                        // also update the hover class to match
                        let newPageNum = parseInt($nextButton.html());
                        buttonRows = generateButtonMatrix($currentWrapper, currentWrapperToken, newPageNum);
                        let $firstButton = $(buttonRows[0][0]);
                        $firstButton.addClass('button_hover');
                        return true;
                        }
                    }
                }
            }
        };

    // Start the user input watcher and collect reference to active inputs
    let gamepadLayout = null; // TODO: pull this from settings later
    let userInputWatcher = new mmrpgUserInputWatcher({
        autoStart: true,
        autoRunCallbacks: false,
        autoButtonMapping: true,
        gamepadLayout: gamepadLayout,
        });
    userInputWatcher.onUserInput(checkUserInputs);
    userInputWatcher.startWatching();
    let checkUserInputWatcher = function(){
        userInputWatcher.checkUserInputs();
        requestAnimationFrame(checkUserInputWatcher);
        };
    checkUserInputWatcher();


    // Define the live Rogue Star ticker functionality if present
    $rogueStar = $('#canvas .rogue_star', $mmrpgWrapper);
    if ($rogueStar.length){

        // Collect the details of this rogue star
        var rogueStar = {};
        rogueStar.type = $rogueStar.attr('data-star-type');
        rogueStar.name = rogueStar.type.charAt(0).toUpperCase() + rogueStar.type.slice(1);
        rogueStar.fromDate = $rogueStar.attr('data-from-date');
        rogueStar.fromDateTime = $rogueStar.attr('data-from-date-time');
        rogueStar.toDate = $rogueStar.attr('data-to-date');
        rogueStar.toDateTime = $rogueStar.attr('data-to-date-time');
        rogueStar.power = parseInt($rogueStar.attr('data-star-power'));
        rogueStar.unixFromTime = Date.parse(rogueStar.fromDate+'T'+rogueStar.fromDateTime) / 1000;
        rogueStar.unixToTime = Date.parse(rogueStar.toDate+'T'+rogueStar.toDateTime) / 1000;
        rogueStar.unixDuration = rogueStar.unixToTime - rogueStar.unixFromTime;
        //console.log('A rogue star is in orbit! \n', rogueStar);

        // Define a function for refreshing the star's postition and text
        var refreshRogueStarInterval = false;
        var refreshRogueStar = function(){
            //console.log('refreshRogueStar()');
            var nowTime = Date.now() / 1000;
            var starTimeDuration = rogueStar.unixToTime - rogueStar.unixFromTime;
            var starTimeElapsed = nowTime - rogueStar.unixFromTime;
            var starTimeElapsedPercent = (starTimeElapsed / starTimeDuration) * 100;
            var starTimeRemaining = starTimeDuration - starTimeElapsed;
            var starPositionRight = (100 - starTimeElapsedPercent) + 1;
            var starMinutesLeft = (starTimeRemaining / 60);
            var starHoursLeft = (starMinutesLeft / 60);
            //console.log('Checking the star details... \n', {nowTime:nowTime, starTimeDuration:starTimeDuration, starTimeElapsed:starTimeElapsed, starTimeElapsedPercent:starTimeElapsedPercent, starTimeRemaining:starTimeRemaining, starPositionRight:starPositionRight, starMinutesLeft:starMinutesLeft, starHoursLeft:starHoursLeft});

            // If the star is still available, update the sprite, else remove it entirely
            if (starTimeRemaining > 0){
                //console.log('The rogue star is refreshing! It\'s moved along a little!');
                var starTooltip = '&raquo; Rogue Star Event! &laquo; || A ' + rogueStar.name + '-type Rogue Star has appeared! This star grants +' + rogueStar.power + ' ' + rogueStar.name + '-type Starforce for a limited time. Take advantage of its power before it\'s gone! ';
                if (starHoursLeft >= 1){ starTooltip += 'You have less than ' + (starHoursLeft > 1 ? Math.ceil(starHoursLeft) + ' hours' : '1 hour') + ' remaining! '; }
                else if (starHoursLeft < 1){ starTooltip += 'You have only ' + (starMinutesLeft > 1 ? Math.ceil(starMinutesLeft) + ' minutes' : '1 minute') + ' remaining! ';  }
                $rogueStar.attr('data-tooltip', starTooltip);
                $rogueStar.find('.trail').css({right:starPositionRight+'%'});
                $rogueStar.find('.star').css({right:starPositionRight+'%'}).css({right:'calc('+starPositionRight+'% - 20px)'});
                } else {
                //console.log('Time is up!  The rogue star has been removed!');
                if (refreshRogueStarInterval !== false){ clearInterval(refreshRogueStarInterval); }
                $rogueStar.css({opacity:1}).animate({opacity:0},500,'swing',function(){ $rogueStar.remove(); });
                return false;
                }
            };

        // Automatically call the refresh function on an interval timer
        refreshRogueStarInterval = setInterval(refreshRogueStar, 9000);
        refreshRogueStar();
        $rogueStar.removeClass('loading');

        }


    // -- BATTLE-SPECIFIC ANIMATIONS -- //

    // Process battle-specific conditions if they exist and are accessible
    if (Object.keys(gameSettings.currentBattleData).length
        && typeof gameSettings.currentBattleData.battle_token !== 'undefined'){
        //console.log('gameSettings.currentBattleData', gameSettings.currentBattleData);

        // -- STAR FORCE COLLLECTION -- //

        // Define the event to trigger when STAR FORCE is collected via a Field Star or Fusion Star
        var battleHasStarForce = false;
        if (typeof gameSettings.currentBattleData.values !== 'undefined'
            && typeof gameSettings.currentBattleData.values.field_star !== 'undefined'){

            // Collect a reference to the field star data so we can parse it
            var fieldStarData = gameSettings.currentBattleData.values.field_star;
            var fieldStarKind = fieldStarData.star_kind;
            //console.log('fieldStarData =', fieldStarKind, fieldStarData);

            // Collect a reference to the star on-screen and its shadow so we can manipulate later
            var $starForceSprite = $('.sprite[data-id="foreground_attachment_'+fieldStarKind+'-star"]', $thisCanvas);
            var $starForceSpriteShadow = $('.sprite[data-id="foreground_attachment_'+fieldStarKind+'-star_shadow"]', $thisCanvas);
            //console.log('$starForceSprite', $starForceSprite);
            //console.log('$starForceSpriteShadow', $starForceSpriteShadow);

            // Define a function for "collecting" the star force and animating it
            var collectStarForceTimeout = false;
            var collectStarForceAnimation = function(){
                if (typeof gameSettings.currentGameState === 'undefined'){ return false; }
                if (typeof gameSettings.currentGameState.this_action === 'undefined'){ return false; }
                if (typeof gameSettings.currentGameState.this_battle_result === 'undefined'){ return false; }
                if (typeof gameSettings.currentGameState.this_battle_status === 'undefined'){ return false; }
                //console.log('Apply the field_star_collected classes');
                $starForceSprite.addClass('field_star_collected');
                $starForceSpriteShadow.addClass('field_star_collected');
                playSoundEffect('star-collected', {rate: 0.5}, false);
                };
            var resetStarForceAnimation = function(){
                //console.log('Remove the field_star_collected classes');
                $starForceSprite.removeClass('field_star_collected');
                $starForceSpriteShadow.removeClass('field_star_collected');
                };

            // Add an event hook to check for the star force collection
            gameSettings.eventHooks.push(function(eventFlags){
                //console.log('gameSettings.eventHooks() w/', eventFlags, gameSettings.currentGameState);
                // If the battle status is now complet, make sure we move the
                if (gameSettings.currentGameState.this_battle_status === 'complete'){
                    //console.log('move the sprite into the foreground immediately');
                    // Collect references to the two foreground divs that this star can be inside
                    var $battleSceneDiv = $('.event .battle_scene', $thisCanvas);
                    if (!$.contains($starForceSprite[0], $battleSceneDiv[0])){
                        $starForceSprite.appendTo($battleSceneDiv);
                        $starForceSpriteShadow.appendTo($battleSceneDiv);
                        }
                }
                // If victory has been claimed, we can run the function to add the classes
                if (eventFlags.victory === true){
                    //console.log('The battle has been won!  Queue-up collecting the star force!');
                    if (collectStarForceTimeout !== false){ clearTimeout(collectStarForceTimeout); }
                    collectStarForceTimeout = setTimeout(collectStarForceAnimation, 1000);
                    }
                });

            window.mmrpgCollectStarForce = collectStarForceAnimation;
            window.mmrpgResetStarForce = resetStarForceAnimation;

        }

        // -- CHALLENGE MARKER COLLLECTION -- //

        // Define the event to trigger when CHALLENGE MARKER is collected via a Challenge Mission
        var battleHasChallengeMarker = false;
        if (typeof gameSettings.currentBattleData.values !== 'undefined'
            && typeof gameSettings.currentBattleData.values.challenge_records !== 'undefined'){

            // Collect a reference to the field star data so we can parse it
            var challengeRecordData = gameSettings.currentBattleData.values.challenge_records;
            var challengeRecordKind = gameSettings.currentBattleData.values.challenge_battle_kind;
            //console.log('challengeRecordData =', challengeRecordKind, challengeRecordData);

            // Collect a reference to the star on-screen and its shadow so we can manipulate later
            var $challengeMarkerSprite = $('.sprite[data-id="foreground_attachment_challenge-marker"]', $thisCanvas);
            var $challengeMarkerSpriteShadow = $('.sprite[data-id="foreground_attachment_challenge-marker_shadow"]', $thisCanvas);
            //console.log('$challengeMarkerSprite', $challengeMarkerSprite);
            //console.log('$challengeMarkerSpriteShadow', $challengeMarkerSpriteShadow);

            // Define a function for "collecting" the star force and animating it
            var collectChallengeMarkerTimeout = false;
            var collectChallengeMarkerAnimation = function(){
                if (typeof gameSettings.currentGameState === 'undefined'){ return false; }
                if (typeof gameSettings.currentGameState.this_action === 'undefined'){ return false; }
                if (typeof gameSettings.currentGameState.this_battle_result === 'undefined'){ return false; }
                if (typeof gameSettings.currentGameState.this_battle_status === 'undefined'){ return false; }
                //console.log('Apply the challenge_marker_destroyed classes');
                $challengeMarkerSprite.addClass('challenge_marker_destroyed');
                $challengeMarkerSpriteShadow.addClass('challenge_marker_destroyed');
                playSoundEffect('marker-destroyed', {rate: 0.5}, false);
                };
            var resetChallengeMarkerAnimation = function(){
                //console.log('Remove the challenge_marker_destroyed classes');
                $challengeMarkerSprite.removeClass('challenge_marker_destroyed');
                $challengeMarkerSpriteShadow.removeClass('challenge_marker_destroyed');
                };

            // Add an event hook to check for the star force collection
            gameSettings.eventHooks.push(function(eventFlags){
                //console.log('gameSettings.eventHooks() w/', eventFlags, gameSettings.currentGameState);
                // If the battle status is now complet, make sure we move the
                if (gameSettings.currentGameState.this_battle_status === 'complete'){
                    //console.log('move the sprite into the foreground immediately');
                    // Collect references to the two foreground divs that this star can be inside
                    var $battleSceneDiv = $('.event .battle_scene', $thisCanvas);
                    if (!$.contains($challengeMarkerSprite[0], $battleSceneDiv[0])){
                        $challengeMarkerSprite.appendTo($battleSceneDiv);
                        $challengeMarkerSpriteShadow.appendTo($battleSceneDiv);
                        }
                }
                // If victory has been claimed, we can run the function to add the classes
                if (eventFlags.victory === true){
                    //console.log('The battle has been won!  Queue-up collecting the star force!');
                    if (collectChallengeMarkerTimeout !== false){ clearTimeout(collectChallengeMarkerTimeout); }
                    collectChallengeMarkerTimeout = setTimeout(collectChallengeMarkerAnimation, 1000);
                    }
                });

            window.mmrpgCollectChallengeMarker = collectChallengeMarkerAnimation;
            window.mmrpgResetChallengeMarker = resetChallengeMarkerAnimation;

        }


    }


});

// Define a function for animation the canvas background startup elements
var fieldBackgroundInit = false;
function mmrpg_battle_fadein_background(animateCanvas, animateDuration, onComplete){
    // Make sure we only do this one
    if (fieldBackgroundInit){ return false; }
    else { fieldBackgroundInit = true; }
    // Collect or define the onComplete function
    var onComplete = onComplete != undefined ? onComplete : function(){};
    // Play the sound effect if we haven't already and we're allowed
    setTimeout(function(){
        playSoundEffect('background-spawn', {volume: 0.5, rate: 0.5}, false);
        }, Math.ceil(animateDuration / 4));
    // Collect the background canvas and event elements
    var animateBackgroundCanvas = $('.animate_fadein', animateCanvas).filter('.background_canvas');
    var animateBackgroundEvent = $('.animate_fadein', animateCanvas).filter('.background_event');
    // Fade the foreground into view and upward into place
    if (animateBackgroundCanvas.length){
        animateBackgroundCanvas.css({opacity:0,left:'auto',right:0,width:'1124px'}).removeClass('animate_fadein').animate({opacity:1,width:'100%'}, animateDuration, 'swing', function(){
            $(this).css({left:0,right:'auto'});
            if (animateBackgroundEvent.length){
                animateBackgroundEvent.css({opacity:0}).removeClass('animate_fadein').animate({opacity:1}, animateDuration, 'swing', onComplete);
                } else {
                onComplete();
                }
            });
        } else {
        onComplete();
        }
}

// Define a function for animation the canvas foreground startup elements
var fieldForegroundInit = false;
function mmrpg_battle_fadein_foreground(animateCanvas, animateDuration, onComplete){
    // Make sure we only do this one
    if (fieldForegroundInit){ return false; }
    else { fieldForegroundInit = true; }
    // Collect or define the onComplete function
    var onComplete = onComplete != undefined ? onComplete : function(){};
    // Play the sound effect if we haven't already and we're allowed
    setTimeout(function(){
        playSoundEffect('foreground-spawn', {volume: 1.0}, false);
        }, Math.ceil(animateDuration / 3));
    // Collect the foreground canvas and event elements
    var animateForegroundCanvas = $('.animate_fadein', animateCanvas).filter('.foreground_canvas');
    var animateForegroundEvent = $('.animate_fadein', animateCanvas).filter('.foreground_event');
    // Fade the foreground into view and upward into place
    if (animateForegroundCanvas.length){
        //animateForegroundCanvas.css({opacity:0,left:'0',right:'auto',width:'1124px'}).removeClass('animate_fadein').animate({opacity:1,width:'100%'}, animateDuration, 'swing', function(){
        animateForegroundCanvas.css({opacity:0,top:'100px'}).removeClass('animate_fadein').animate({opacity:1,top:0}, animateDuration, 'swing', function(){
            //$(this).css({left:'auto',right:0});
            if (animateForegroundEvent.length){
                animateForegroundEvent.css({opacity:0}).removeClass('animate_fadein').animate({opacity:1}, animateDuration, 'swing', onComplete);
                } else {
                onComplete();
                }
            });
        } else {
        onComplete();
        }
}

// Define a function for updating the engine form
function mmrpg_engine_update(newValues){
    if (gameEngine.length){
        // Loop through the game engine values and update them
        for (var thisName in newValues){
            var thisValue = newValues[thisName];
            // Update the value in the global settings object
            gameSettings.currentGameState[thisName] = thisValue;
            // And then also update it in the DOM for form submission
            if ($('input[name='+thisName+']', gameEngine).length){
                $('input[name='+thisName+']', gameEngine).val(thisValue);
                } else {
                gameEngine.append('<input type="hidden" class="hidden" name="'+thisName+'" value="'+thisValue+'" />');
                }
            }
        }
}

// Define a function for switching to a different action panel
function mmrpg_action_panel(thisPanel, currentPanel){

    // Update the current panel in the game settings for reference
    gameSettings.currentActionPanel = thisPanel;

    // Switch to the event actions panel
    $('.wrapper', gameActions).css({display:'none'});
    var newWrapper = $('#actions_'+thisPanel, gameActions);
    if (currentPanel != undefined){
        newWrapper.find('.action_back').attr('data-panel', currentPanel);
        var newWrapperTitle = newWrapper.find('.main_actions_title');
        if (newWrapperTitle.length){ newWrapperTitle.html(newWrapperTitle.html().replace('{thisPanel}', currentPanel)); }
        //alert('thisPanel = '+thisPanel+'; currentPanel = '+currentPanel);
        }

    // Unhide the new wrapper
    newWrapper.css({display:''});

    // If the new action panel has numbered links in the title
    var mainActionsTitle = $('.main_actions_title', newWrapper);
    var floatLinkContainer = $('.float_links', mainActionsTitle);
    if (floatLinkContainer.length){

        // Collect the parent wraper ID and generate the option class name
        var parentActionsWrapper = floatLinkContainer.closest('#actions > .wrapper');
        var actionWrapperID = parentActionsWrapper.attr('id');
        var optionClass = actionWrapperID.replace(/^actions_/, 'action_');

        // Assign events to any of the page links here
        $('.num[href]', floatLinkContainer).click(function(e){
            e.preventDefault();

            // Collect references to this link and number
            var thisLink = $(this);
            var thisNum = parseInt(thisLink.attr('href').replace(/^#/, ''));

            // If this this panel is disabled, prevent clicking but only the first link
            //if (thisNum > 1 && mainActionsTitle.hasClass('main_actions_title_disabled')){ return false; }
            //console.log('num link '+thisNum+' clicked!');

            // Remove the active class from other links and add to this one
            $('.num', floatLinkContainer).removeClass('active');
            thisLink.addClass('active');

            // Define the key of the first and last element to be shown
            var lastElementKey = thisNum * 8;
            var firstElementKey = lastElementKey - 8;
            //console.log('first key should be '+firstElementKey+' and last should be '+lastElementKey+'!');

            // Hide all item buttons in the current view and then show only relevant
            $('.'+optionClass, newWrapper).css({display:'none'});
            var activeButtons = $('.'+optionClass, newWrapper).slice(firstElementKey, lastElementKey);
            //console.log('we have selected a total of '+activeButtons.length+' elements');
            activeButtons.css({display:'block'});

            // Loop through the active buttons and update their order values
            var tempOrder = 1;
            activeButtons.each(function(){ $(this).attr('data-order', tempOrder); tempOrder++; });
            $('.action_back', newWrapper).attr('data-order', tempOrder);

            // Update the session with the last page click
            //var thisRequestType = 'session';
            //var thisRequestData = 'battle_settings,'+optionClass+'_page_num,'+thisNum;
            //$.post('scripts/script.php',{requestType:thisRequestType,requestData:thisRequestData});
            //(disabled for now)

            // Return true on success
            return true;

            });

        var activeLink = $('.active', floatLinkContainer);
        var firstLink = $('.num', floatLinkContainer).first();
        if (activeLink.length){ activeLink.triggerSilentClick(); }
        else if (firstLink.length){ firstLink.triggerSilentClick(); }

        }

    // If there are buttons in the new wrapper
    var hoverButton = $('.button_hover', newWrapper);
    var currentButtons = $('.button:not(.button_disabled)', newWrapper).not('.main_actions_title .button');
    var currentButtonCount = currentButtons.length;
    if (currentButtonCount > 0 && !hoverButton.length){
        var firstButton = currentButtons.first();
        var firstButtonOrder = firstButton.attr('data-order') != undefined ? parseInt(firstButton.attr('data-order')) : 0;
        if (firstButton.length){ firstButton.addClass('button_hover'); }
    }

}


// Define a function for updating an action panel's markup
var actionPanelCache = [];
function mmrpg_action_panel_update(thisPanel, thisMarkup){
    // Update the requested panel with the supplied markup
    //console.log('mmrpg_action_panel_update('+thisPanel+', [thisMarkup])');
    var thisActionPanel = $('#actions_'+thisPanel, gameActions);
    thisActionPanel.empty().html(thisMarkup);
    // Search for any sprites in this panel's markup
    $('.sprite', thisActionPanel).each(function(){
        var thisBackground = $(this).css('background-image').replace(/^url\("?(.*?)"?\)$/i, '$1');
        if (thisBackground != 'none'){
            var cacheImage = document.createElement('img');
            cacheImage.src = thisBackground;
            actionPanelCache.push(cacheImage)
            }
        });
}

// Define a global variable for holding events
var mmrpgEvents = [];
// Define a function for queueing up an event
function mmrpg_event(flagsMarkup, dataMarkup, canvasMarkup, consoleMarkup){
    if (flagsMarkup.length){ flagsMarkup = $.parseJSON(flagsMarkup); }
    else { flagsMarkup = {}; }
    if (dataMarkup.length){ dataMarkup = $.parseJSON(dataMarkup); }
    else { dataMarkup = {}; }
    mmrpgEvents.push({
        'event_functions' : function(eventFlags){
            if (dataMarkup.length){
                //dataMarkup = $.parseJSON(dataMarkup);
                /*
                mmrpg_canvas_update(
                    dataMarkup.this_battle,
                    dataMarkup.this_field,
                    dataMarkup.this_player,
                    dataMarkup.this_robot,
                    dataMarkup.target_player,
                    dataMarkup.target_robot
                    );
                */
                }
            if (canvasMarkup.length){
                mmrpg_canvas_event(canvasMarkup, eventFlags); //, flagsMarkup
                }
            if (consoleMarkup.length){
                mmrpg_console_event(consoleMarkup, eventFlags);  //, flagsMarkup
                }
            },
        'event_flags' : flagsMarkup //$.parseJSON(flagsMarkup)
            });
    //console.log('mmrpgEvents.push() w/ new size', mmrpgEvents.length);
}

// Define a function for playing the events
var eventAlreadyQueued = false;
var battleResultsDisplayed = false;
function mmrpg_events(){

    if (eventAlreadyQueued){ return; }

    //console.log('mmrpg_events()');
    //clearTimeout(canvasAnimationTimeout);
    clearInterval(canvasAnimationTimeout);
    canvasAnimationCameraTimer = 0;
    updateCameraShiftTransitionTiming();
    updateCameraShiftTransitionDuration();

    var thisEvent = false;
    if (mmrpgEvents.length){
        //console.log('mmrpgEvents.length =', mmrpgEvents.length);
        // Switch to the events panel
        mmrpg_action_panel('event');
        // Collect the topmost event and execute it
        thisEvent = mmrpgEvents.shift();
        thisEvent.event_functions(thisEvent.event_flags);
        // Loop through eventhooks functions if there are any in gameSettings.eventHooks and process them with this event
        if (gameSettings.eventHooks.length){
            $.each(gameSettings.eventHooks, function(){
                var thisEventHook = this;
                if (typeof thisEventHook == 'function'){ thisEventHook(thisEvent.event_flags); }
                });
            }
        }

    if (mmrpgEvents.length < 1){
        // Assuming we're allowed to use camera stuff, reset the camera if it's not already
        if (gameSettings.eventCameraShift){
            //console.log('events are done, reset the camera');
            mmrpg_canvas_camera_shift();
        }
        // Switch to the specified "next" action
        var nextAction = $('input[name=next_action]', gameEngine).val();
        if (nextAction.length){ mmrpg_action_panel(nextAction); }
        // Add the idle class to the robot details on-screen
        //console.log('adding robot details class....1');
        //$('.robot_details', gameCanvas).css('opacity', 0.9).addClass('robot_details_idle');
        // Start animating the canvas randomly
        mmrpg_canvas_animate();
        } else if (mmrpgEvents.length >= 1){
            var autoClickTimer = false;
            if (gameSettings.eventAutoPlay && thisEvent.event_flags.autoplay != false){
                //console.log('queue next event');
                eventAlreadyQueued = true;
                clearTimeout(autoClickTimer);
                autoClickTimer = setTimeout(function(){
                    requestAnimationFrame(function(){
                        //console.log('fire next event');
                        eventAlreadyQueued = false;
                        mmrpg_events();
                        });
                    }, parseInt(gameSettings.eventTimeout));
                $('a[data-action="continue"]').addClass('button_disabled');
                } else {
                $('a[data-action="continue"]').removeClass('button_disabled');
                }
            $('a[data-action="continue"]').click(function(){
                if (autoClickTimer !== false){
                    clearTimeout(autoClickTimer);
                    autoClickTimer = false;
                    }
                });
        }

    // Collect the current battle status and result
    var battleStatus = $('input[name=this_battle_status]', gameEngine).val();
    var battleResult = $('input[name=this_battle_result]', gameEngine).val();

    // Check for specific value triggers and execute events
    if (battleStatus == 'complete'
        && battleResultsDisplayed === false){
        //console.log('checkpoint | battleStatus='+battleStatus+' battleResult='+battleResult);
        //console.log('thisEvent.event_flags =', thisEvent.event_flags);

        // Based on the battle result, play the victory or defeat music
        if (battleResult == 'victory'
            && typeof thisEvent.event_flags.victory !== 'undefined'
            && thisEvent.event_flags.victory === true){
            // Play the victory music
            //console.log('mmrpg_events() / Play the victory music');
            //playSoundEffect('battle-victory-sound');
            if (gameSettings.playVictorySound){
                let victorySound = 'battle-victory-sound';
                let victoryMusic = 'misc/leader-board';
                top.mmrpg_music_volume(0, false);
                playSoundEffect(victorySound);
                setTimeout(function(){
                    if (gameSettings.playVictoryMusic){
                        top.mmrpg_music_load(victoryMusic, true, false);
                        } else {
                        top.mmrpg_reset_music_volume();
                        }
                    }, 1000);
                } else if (gameSettings.playVictoryMusic){
                top.mmrpg_music_load(victoryMusic, true, false);
                }
            if (mmrpgEvents.length < canvasAnimationCameraDelay){ canvasAnimationCameraTimer = canvasAnimationCameraDelay - mmrpgEvents.length; }
            battleResultsDisplayed = true;
            }
        if (battleResult == 'defeat'
            && typeof thisEvent.event_flags.defeat !== 'undefined'
            && thisEvent.event_flags.defeat === true){
            // Play the failure music
            //console.log('mmrpg_events() / Play the failure music');
            if (gameSettings.playDefeatSound){
                let defeatSound = 'battle-defeat-sound';
                let defeatMusic = 'misc/leader-board';
                top.mmrpg_music_volume(0, false);
                playSoundEffect(defeatSound);
                setTimeout(function(){
                    if (gameSettings.playDefeatMusic){
                        top.mmrpg_music_load(defeatMusic, true, false);
                        } else {
                        top.mmrpg_reset_music_volume();
                        }
                    }, 1000);
                } else if (gameSettings.playDefeatMusic){
                top.mmrpg_music_load(defeatMusic, true, false);
                }
            if (mmrpgEvents.length < canvasAnimationCameraDelay){ canvasAnimationCameraTimer = canvasAnimationCameraDelay - mmrpgEvents.length; }
            battleResultsDisplayed = true;
            }

        }

    if (mmrpgEvents.length < 1
        && battleStatus == 'complete'
        && battleResultsDisplayed === true
        ){

        // Always trigger an event pull after the battle concludes in case of unlocks
        setTimeout(function(){ triggerWindowEventsPull(); }, 2000);

        }


}

// Define a function for creating a new layer on the canvas
function mmrpg_canvas_event(thisMarkup, eventFlags){ //, flagsMarkup
    var thisContext = $('.wrapper', gameCanvas);
    if (thisContext.length){
        //console.log('mmrpg_canvas_event(thisMarkup, eventFlags) | eventFlags =', eventFlags);
        //console.log('gameSettings.eventTimeout =', gameSettings.eventTimeout, 'gameSettings.eventTimeoutThreshold =', gameSettings.eventTimeoutThreshold);
        // Drop all the z-indexes to a single amount
        $('.event:not(.sticky)', thisContext).css({zIndex:500});
        // Calculate the top offset based on previous event height
        var eventTop = $('.event:not(.sticky):first-child', thisContext).outerHeight();
        // Prepend the event to the current stack but bring it to the front
        var thisEvent = $('<div class="event event_frame clearback">'+thisMarkup+'</div>');
        thisEvent.css({opacity:0.0,zIndex:600});
        thisContext.prepend(thisEvent);

        // Wait for all the event's assets to finish loading
        thisEvent.waitForImages(function(){

            // Find all the details in this event markup and move them to the sticky
            $(this).find('.details').addClass('hidden').css({opacity:0}).appendTo('.event_details', gameCanvas);

            // If camera shift settings are enabled, we can process them
            if (gameSettings.eventCameraShift){
                // If this event has any camera action going on, make sure we update the canvas
                var currentShift = thisContext.attr('data-camera-shift') || '';
                var currentFocus = thisContext.attr('data-camera-focus') || '';
                var currentDepth = thisContext.attr('data-camera-depth') || '';
                var currentOffset = thisContext.attr('data-camera-offset') || '';
                var newCameraShift = '';
                var newCameraFocus = '';
                var newCameraDepth = 0;
                var newCameraOffset = 0;
                // Check to see if camera shift settings were provided in the frame
                if (typeof eventFlags.camera !== 'undefined'
                    && eventFlags.camera !== false){
                    //console.log('we have camera action!', eventFlags.camera);
                    newCameraShift = eventFlags.camera.side;
                    newCameraFocus = eventFlags.camera.focus;
                    newCameraDepth = eventFlags.camera.depth;
                    newCameraOffset = eventFlags.camera.offset;
                }
                // If any of the shift values have changed, we need to update everything
                if (currentShift !== newCameraShift
                    || currentFocus !== newCameraFocus
                    || currentDepth !== newCameraDepth
                    || newCameraOffset !== newCameraOffset){
                    mmrpg_canvas_camera_shift(newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset);
                }
            }

            // If found effect settings are enabled, we can process them
            if (gameSettings.eventSoundEffects){
                //console.log('we can react to sound effects!');
                //console.log('eventFlags =', eventFlags);
                // Check to see if camera shift settings were provided in the frame
                if (typeof eventFlags.sounds !== 'undefined'
                    && eventFlags.sounds !== false){
                    //console.log('we have sound effect(s)!', eventFlags.sounds);
                    for (var i = 0; i < eventFlags.sounds.length; i++){
                        var effectConfig = eventFlags.sounds[i];
                        var effectName = effectConfig.name;
                        //console.log('effectName =', effectName);
                        //console.log('effectConfig =', effectConfig);
                        if (typeof effectConfig.delay === 'number'){
                            setTimeout(function(){
                                playSoundEffect(effectName, effectConfig, false);
                                }, effectConfig.delay);
                            } else {
                            playSoundEffect(effectName, effectConfig, false);
                            }
                    }
                }
            }

            // If we're allowed to cross-fade transition the normal way, otherwise straight-up replace the event
            let $thisEvent = $(this);
            let $otherEvents = $('.event:not(.sticky):gt(0)', thisContext);
            //let eventCrossFade = Math.ceil(gameSettings.eventTimeout / 2);
            let eventCrossFadeOutDuration = 100;
            let eventCrossFadeInDuration = 50;
            if (mmrpg_cross_fade_enabled()){
                // Animate a fade out of the other events
                $otherEvents.css({zIndex:499}).removeClass('current');
                $otherEvents.animate({opacity:0},{
                    duration: eventCrossFadeOutDuration,
                    easing: 'linear',
                    queue: false
                    });
                // Animate a fade in, and the remove the old images
                $thisEvent.animate({opacity:1.0}, {
                    duration: eventCrossFadeInDuration,
                    easing: 'linear',
                    complete: function(){
                        $('.details:not(.hidden)', thisContext).remove();
                        $('.details', thisContext).css({opacity:1}).removeClass('hidden');
                        $otherEvents.remove();
                        $thisEvent.css({zIndex:500});
                        },
                    queue: false
                    });
                }
            else {
                    // Make sure the new event is visible then remove the old ones
                    $thisEvent.css({opacity:1.0,zIndex:500});
                    $otherEvents.css({opacity:0,zIndex:499});
                    $('.details:not(.hidden)', thisContext).remove();
                    $('.details', thisContext).css({opacity:1}).removeClass('hidden');
                    $otherEvents.remove();
            }

            // Loop through all field layers on the canvas and trigger animations
            $('.background[data-animate],.foreground[data-animate]', gameCanvas).each(function(){
                // Trigger an animation frame change for this field
                var thisField = $(this);
                mmrpg_canvas_field_frame(thisField, '');
                });

            // Loop through all field layers on the canvas and trigger animations
            $('.sprite[data-type=attachment][data-animate]', gameCanvas).each(function(){
                // Trigger an animation frame change for this field
                var thisAttachment = $(this);
                var thisPosition = thisAttachment.attr('data-position');
                if (thisPosition == 'background' || thisPosition == 'foreground'){
                    //console.log('mmrpg_canvas_attachment_frame('+thisAttachment.attr('data-id')+')');
                    mmrpg_canvas_attachment_frame(thisAttachment, '');
                    }
                });

            });
        }
}


// Define a function for updating the graphics on the canvas
function mmrpg_canvas_update(thisBattle, thisPlayer, thisRobot, targetPlayer, targetRobot){
    // Preload all this robot's sprite image files if not already
    if (thisPlayer.player_side && thisRobot.robot_token){
        var thisRobotToken = thisRobot.robot_token;
        var thisRobotDirection = thisPlayer.player_side == 'right' ? 'left' : 'right';
        mmrpg_preload_robot_sprites(thisRobotToken, thisRobotDirection);
        }
    // Preload all the target robot's sprite image files if not already
    if (targetPlayer.player_side && targetRobot.robot_token){
        var targetRobotToken = targetRobot.robot_token;
        var targetRobotDirection = targetPlayer.player_side == 'right' ? 'left' : 'right';
        mmrpg_preload_robot_sprites(targetRobotToken, targetRobotDirection);
        }
}


// Define a change event for whenever this game setting is altered
gameSettings.currentCameraShift = {shift:'',focus:'',depth:'',offset:''};
function mmrpg_canvas_camera_shift(newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset){
    //console.log('mmrpg_canvas_camera_shift() w/ ', newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset);

    if (typeof newCameraShift === 'undefined' || !newCameraShift){ newCameraShift = ''; }
    if (typeof newCameraFocus === 'undefined' || !newCameraFocus){ newCameraFocus = ''; }
    if (typeof newCameraDepth === 'undefined' || !newCameraDepth){ newCameraDepth = 0; }
    if (typeof newCameraOffset === 'undefined' || !newCameraOffset){ newCameraOffset = 0; }
    //console.log('mmrpg_canvas_camera_shift() w/ ', newCameraShift, newCameraFocus, newCameraDepth, newCameraOffset);

    // Collect the canvas context and immediately return false if not exists
    var thisContext = $('.wrapper', gameCanvas);
    if (!thisContext.length){ return false; }

    // Collect current shift values for reference and updating
    var currentCameraShift = gameSettings.currentCameraShift;
    //console.log('currentCameraShift:', currentCameraShift);

    // If the values haven't changed at all, we should just return
    if (currentCameraShift.shift === newCameraShift
        && currentCameraShift.focus === newCameraFocus
        && currentCameraShift.depth === newCameraDepth
        && currentCameraShift.offset === newCameraOffset){
        return;
    }

    // Update the data attributes on the canvas wrapper
    currentCameraShift.shift = newCameraShift;
    currentCameraShift.focus = newCameraFocus;
    currentCameraShift.depth = newCameraDepth;
    currentCameraShift.offset = newCameraOffset;
    thisContext.attr('data-camera-shift', newCameraShift);
    thisContext.attr('data-camera-focus', newCameraFocus);
    thisContext.attr('data-camera-depth', newCameraDepth);
    thisContext.attr('data-camera-offset', newCameraOffset);
    var offsetCameraDepth = newCameraDepth + newCameraOffset;
    if (offsetCameraDepth < -8){ offsetCameraDepth - -8; }
    else if (offsetCameraDepth > 8){ offsetCameraDepth = 8; }

    // This first value is used for camera shifts on the bench
    if (offsetCameraDepth !== 0){
        var diffValue = ((Math.abs(offsetCameraDepth) - 1) * 0.1);
        var depthModValue = 1 - diffValue;
        if (offsetCameraDepth < 0){ depthModValue = depthModValue * -1; }
        updateCameraShiftVariable('depth-mod', depthModValue);
    } else {
        updateCameraShiftVariable('depth-mod', 1);
    }

    // This second value is used for camera shifts in the foreground
    if (offsetCameraDepth !== 0){
        var diffValue = ((Math.abs(offsetCameraDepth) - 1) * 0.1);
        var depthMod2Value = 1.8 - diffValue;
        if (offsetCameraDepth < 0){ depthMod2Value = depthMod2Value * -1; }
        updateCameraShiftVariable('depth-mod2', depthMod2Value);
    } else {
        updateCameraShiftVariable('depth-mod2', 1.8);
    }

}

// Define a function for easily updating camera-related CSS variables on the canvas
function updateCameraShiftVariable(varName, varValue){
    //console.log('updateCameraShiftVariable((varName:', varName, ', varValue:', varValue, ')');
    var cssVarName = '--camera-shift-'+varName;
    var cssVarValue = varValue;
    //console.log('setting '+cssVarName+' to:', cssVarValue);
    document.documentElement.style.setProperty(cssVarName, cssVarValue);
}

// Define a quick function for updating the camera shift transition timing variable
function updateCameraShiftTransitionTiming(newValue){
    //console.log('updateCameraShiftTransitionTiming(', newValue, ')');
    if (typeof newValue !== 'string' || !newValue){ newValue = 'ease'; }
    var transitionTimingValue = newValue;
    updateCameraShiftVariable('transition-timing', transitionTimingValue);
};

// Define a quick function for updating the camera shift transition duration variable
function updateCameraShiftTransitionDuration(newValue){
    //console.log('updateCameraShiftTransitionDuration(', typeof newValue, newValue, ')');
    if (typeof newValue !== 'number'){ newValue = 0.5; }
    var transitionDurationValue = (function(modValue){
        //console.log('transitionDurationValue(', modValue, ')');
        if (typeof modValue !== 'number'){ modValue = 1; }
        var duration = Math.ceil(gameSettings.eventTimeout * modValue);
        //if (!gameSettings.eventCrossFade){ duration = 0; }
        //else if (!gameSettings.eventCameraShift){ duration = 0; }
        //else if (gameSettings.eventTimeout <= gameSettings.eventTimeoutThreshold){ duration = 0; }
        if (!gameSettings.eventCameraShift){ duration = 0; }
        if (gameSettings.eventTimeout <= gameSettings.eventTimeoutThreshold){ duration = gameSettings.eventTimeoutThreshold; }
        var cssValue = duration > 0 ? (duration / 1000)+'s' : 'none';
        //console.log('duration:', duration, 'cssValue:', cssValue);
        return cssValue;
        })(newValue);
    updateCameraShiftVariable('transition-duration', transitionDurationValue);
};

// Define a function for appending a event to the console window
function mmrpg_console_event(thisMarkup, eventFlags){ //, flagsMarkup
    var thisContext = $('.wrapper', gameConsole);
    if (thisContext.length){
        //console.log('mmrpg_console_event(thisMarkup, eventFlags) | eventFlags =', eventFlags);
        // Append the event to the current stack
        //thisContext.prepend('<div class="event" style="top: -100px;">'+thisMarkup+'</div>');
        thisContext.prepend(thisMarkup);
        gameConsole.find('.wrapper').scrollTop(0);
        $('.event:first-child', thisContext).css({top:-100});
        if (mmrpg_cross_fade_enabled()){
            // We're at a normal speed, so we can animate normally
            $('.event:first-child', thisContext).animate({top:0}, 400, 'swing');
            } else {
            // We're at a super-fast speed, so we should NOT cross-fade
            $('.event:first-child', thisContext).css({top:0});
            }
        // Hide any leftover boxes from previous events over the limit
        $('.event:gt(50)', thisContext).appendTo('#event_console_backup');
        // Remove any leftover boxes from previous events
        //$('.event:gt(10)', thisContext).remove();
        }
}

// Define a function for toggling the canvas animation
function mmrpg_toggle_animation(){
    if (gameSettings.idleAnimation != false){ return mmrpg_stop_animation(); }
    else { return mmrpg_start_animation(); }
}

// Define a function for starting the canvas animation
function mmrpg_start_animation(){
    var animateToggle = $('a.toggle', gameAnimate);
    animateToggle.removeClass('paused').addClass('playing');
    animateToggle.html('<i class="fas fa-play"></i>');
    gameSettings.idleAnimation = true;
    gameSettings.eventAutoPlay = true;
    mmrpg_canvas_animate();
    if (mmrpgEvents.length){ mmrpg_events(); }
    return gameSettings.idleAnimation;
}

// Define a function for stopping the canvas animation
function mmrpg_stop_animation(){
    var animateToggle = $('a.toggle', gameAnimate);
    animateToggle.removeClass('playing').addClass('paused');
    animateToggle.html('<i class="fas fa-pause"></i>');
    gameSettings.idleAnimation = false;
    gameSettings.eventAutoPlay = false;
    mmrpg_canvas_animate();
    return gameSettings.idleAnimation;
}

// Quick function for playing a sound effect (if available)
let mmrpgPlaySoundEffect;
async function playSoundEffect(soundName, options){
    //console.log('%c' + 'mmrpgBattle.playSoundEffect(' + soundName + ', ' + JSON.stringify(options) + ')', 'color: green;');
    //console.error('use this to trace backward');
    if (!soundName || typeof soundName !== 'string' || !soundName.length){ console.error('playSoundEffect() missing required soundName!'); return false; }
    if (typeof options !== 'object' || !options){ options = {}; }
    let _self = this;
    let _selfReference = _self.playSoundEffect;
    if (!mmrpgPlaySoundEffect){
        mmrpgPlaySoundEffect = function(soundName, options){
            if (this instanceof jQuery || this instanceof Element){
                if ($(this).data('silentClick')){ return; }
                if ($(this).is('.disabled')){ return; }
                if ($(this).is('.button_disabled')){ return; }
                }
            let soundEffectFunction = null;
            if (typeof top.mmrpg_play_sound_effect !== 'undefined'){ soundEffectFunction = top.mmrpg_play_sound_effect; }
            else if (typeof self.mmrpg_play_sound_effect !== 'undefined'){ soundEffectFunction = self.mmrpg_play_sound_effect; }
            if (soundEffectFunction){ soundEffectFunction(soundName, options); }
            else { console.warn('mmrpgWorldMap.playSoundEffect() unable to play sound effect "' + soundName + '" because mmrpg_play_sound_effect is not defined!'); }
            };
        }
    return mmrpgPlaySoundEffect.call(_self, soundName, options);
    }

// Define a quick functino for polling the server for new events (but only if we can actually show them)
function triggerWindowEventsPull(afterDelay){
    //console.log('%c' + 'mmrpgWorldMap.triggerWindowEventsPull()', 'color: magenta;');
    afterDelay = typeof afterDelay === 'number' ? afterDelay : 1000; // default to zero if not provided
    //console.log('queuing the windowEventsPull event (via world)');
    if (typeof window.top.mmrpg_queue_for_game_start !== 'undefined'){
        //console.log('i guess we wait for the game to start via parent.mmrpg_queue_for_game_start()', parent.mmrpg_queue_for_game_start);
        window.top.mmrpg_queue_for_game_start(function(){
            //console.log('i guess the game has started');
            setTimeout(function(){
                //console.log('attempting to pull window events via parent.windowEventsPull()', parent.windowEventsPull);
                let result = parent.windowEventsPull(true);
                if (result < 0){ console.error('windowEventsPull returned an error code: ' + result); }
                //else { console.log('windowEventsPull returned successfully: ' + result); }
                }, afterDelay);
            });
        }
    else if (typeof window.top.windowEventsPull !== 'undefined'){
        //console.log('i guess we pull events manually via parent.windowEventsPull()', parent.windowEventsPull);
        setTimeout(function(){
            let result = parent.windowEventsPull(true);
            if (result < 0){ console.error('windowEventsPull returned an error code: ' + result); }
            //else { console.log('windowEventsPull returned successfully: ' + result); }
            }, afterDelay);
        }
    else {
        //console.warn('could not find a way to pull window events from the parent window!');
        }
    // Return no specific result
    return;
    }