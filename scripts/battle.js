
// Define global variables
var $thisPrototype = false;
var $rogueStar = false;

// Expand the game settings object with a variable battle specific data
gameSettings.currentGameState = {}; // default to empty but may be filled at runtime and used later
gameSettings.currentBattleData = {};
gameSettings.currentBattleState = {};
gameSettings.battleHasStarted = false;

// Create the document ready events
$(document).ready(function(){
    $thisPrototype = $('#mmrpg');
    $thisCanvas = $('#canvas', $thisPrototype);

    // Preload battle related image files
    mmrpg_preload_assets();

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
    var thisContext = $('#battle');
    var playSoundEffect = function(){};
    if (typeof top.mmrpg_play_sound_effect !== 'undefined'){

        // Define a quick local function for routing sound effect plays to the parent
        playSoundEffect = function(soundName, options){
            if (this instanceof jQuery || this instanceof Element){
                if ($(this).data('silentClick')){ return; }
                if ($(this).is('.disabled')){ return; }
                if ($(this).is('.button_disabled')){ return; }
                }
            top.mmrpg_play_sound_effect(soundName, options);
            };

        // MENU LINKS

        // Add hover and click sounds to the buttons in the main menu
        $('#actions .main_actions .button', thisContext).live('mouseenter', function(){
            playSoundEffect.call(this, 'link-hover');
            });
        $('#actions .main_actions .button', thisContext).live('click', function(){
            playSoundEffect.call(this, 'link-click');
            });

        // Add hover and click sounds to any buttons in the sub menu
        $('#actions .sub_actions .button', thisContext).live('mouseenter', function(){
            if ($(this).is('.action_back')){ playSoundEffect.call(this, 'back-hover'); }
            else { playSoundEffect.call(this, 'link-hover'); }
            });
        $('#actions .sub_actions .button', thisContext).live('click', function(){
            if ($(this).is('.action_back')){ playSoundEffect.call(this, 'back-click'); }
            else { playSoundEffect.call(this, 'link-click'); }
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
        let buttonSelector = '.button:visible:not(.button_disabled):not(.float_links *)';
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

    // Start the user input watcher and collect reference to active inputs
    let userInputWatcher = new mmrpgUserInputWatcher();
    //console.log('-> userInputWatcher:', userInputWatcher);
    let activeInputs = userInputWatcher.activeInputs;
    //console.log('-> activeInputs:', activeInputs);

    // Define a function to run each time user inputs are updated so we can react
    let listenForInput = true;
    let battleIsBusy = function(){ return gameSettings.currentActionPanel === 'loading' ? true : false; };
    let ignoreInputFor = function(delay){ delay = typeof delay === 'number' ? delay : 250; listenForInput = false; setTimeout(function(){ listenForInput = true; }, delay); };
    let checkUserInputs = function(event){
        //console.log('%c' + 'checkUserInputs() - Battle keydown event!', 'color: cyan;');
        if (!listenForInput){ return false; }
        if (battleIsBusy()){ return false; }
        if (!Object.keys(activeInputs).length){ return false; } // nothing pressed, ignore
        //console.log('-> gameSettings.currentActionPanel:', gameSettings.currentActionPanel);
        //console.log('-> activeInputs:', activeInputs);
        ignoreInputFor();
        // Quickly check to see which menu we're on first
        let $thisBattle = $('#battle', $thisPrototype);
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
        let buttonSelector = '.button:visible:not(.button_disabled):not(.float_links *)';
        let hoverButtonSelector = buttonSelector+'.button_hover';
        let $currentMainActions = $('.main_actions', $currentWrapper);
        let $currentSubActions = $('.sub_actions', $currentWrapper);
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
                }
            else if ($hoverButton.length
                && $hoverButton.is(':visible')
                && !$hoverButton.is('.button_disabled')){
                $buttonToClick = $hoverButton;
                }
            else if ($firstButton.length
                && $firstButton.is(':visible')
                && !$firstButton.is('.button_disabled')){
                $buttonToClick = $firstButton;
                }
            if ($buttonToClick){
                $buttonToClick.trigger('click');
                if ($buttonToClick.is('[data-panel]')){
                    let $newWrapper = $('#actions_' + $buttonToClick.attr('data-panel'), $battleActions);
                    let $newButtons = $(buttonSelector, $newWrapper);
                    let $hoverButton = $(hoverButtonSelector, $newWrapper);
                    if (!$hoverButton.length || $hoverButton.is('.action_back')){
                        let $newFirstButton = $newButtons.first();
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
                if ($backButton.length){
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
                        return true;
                        }
                    }
                else if (activeInputs.Down){
                    //console.log('%c' + 'Direction input DOWN on the BATTLE menu panel...', 'color: orange;');
                    $currentButtons.removeClass('button_hover');
                    let $firstSubButton = $currentSubActionButtons.first();
                    if ($firstSubButton.length){
                        $firstSubButton.addClass('button_hover');
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
                            return true;
                            }
                        else if ($firstSubButton.length){
                            $currentButtons.removeClass('button_hover');
                            $firstSubButton.addClass('button_hover');
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
        // If the user has pressed the L1/R1 bumpers to scoll sub-pages
        // if the mainactions have .float_links and .button.num pages inside
        // then the L1/R1 buttons should scroll through them and "click"
        if (activeInputs.L1 || activeInputs.R1){ // L1/R1 bumpers
            //console.log('%c' + 'L1 or R1 bumper pressed!', 'color: orange;');
            if (event){ event.preventDefault(); }
            let $floatLinks = $('.float_links', $currentMainActions);
            if ($floatLinks.length){
                let $allButtons = $('.button.num:not(.disabled):not([data-action])', $floatLinks);
                let $activeButton = $allButtons.filter('[href="#' + lastWrapperPage + '"]');
                if ($activeButton.length && $allButtons.length > 1){
                    let visibleIndex = $allButtons.index($activeButton);
                    let nextIndex = visibleIndex;
                    if (activeInputs.L1){ nextIndex = visibleIndex - 1; }
                    else if (activeInputs.R1){ nextIndex = visibleIndex + 1; }
                    if (nextIndex < 0){ nextIndex = $allButtons.length - 1; }
                    if (nextIndex >= $allButtons.length){ nextIndex = 0; }
                    let $nextButton = $allButtons.eq(nextIndex);
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
    document.addEventListener('keydown', checkUserInputs);
    document.addEventListener('mousewheel', checkUserInputs);
    document.addEventListener('gamepadinput', checkUserInputs);

    // Define the live Rogue Star ticker functionality if present
    $rogueStar = $('#canvas .rogue_star', $thisPrototype);
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
                if (typeof top.mmrpg_play_sound_effect !== 'undefined'){
                    top.mmrpg_play_sound_effect('star-collected', {rate: 0.5}, false);
                    }
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
                if (typeof top.mmrpg_play_sound_effect !== 'undefined'){
                    top.mmrpg_play_sound_effect('marker-destroyed', {rate: 0.5}, false);
                    }
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
    if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){
        setTimeout(function(){
            parent.mmrpg_play_sound_effect('background-spawn', {volume: 0.5, rate: 0.5}, false);
            }, Math.ceil(animateDuration / 4));
        }
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
    if (typeof parent.mmrpg_play_sound_effect !== 'undefined'){
        setTimeout(function(){
            parent.mmrpg_play_sound_effect('foreground-spawn', {volume: 1.0}, false);
            }, Math.ceil(animateDuration / 3));
        }
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