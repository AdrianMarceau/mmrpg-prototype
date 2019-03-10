
/* -- BACK-END JAVASCRIPT (ADMIN) -- */

// Define global object variables
var thisAdmin = false;
var thisAdminForm = false;
var thisAdminSearch = false;
var thisAdminResults = false;
var thisAdminEditor = false;
var $adminForm = false;
var $adminAjaxForm = false;
var $adminAjaxFrame = false;

// Pre-define the upload complete functions
window.onUpdateImageComplete = function(){ };

// Shill for the Date.now() function in UNIX timestamp format
if (!Date.now) { Date.now = function() { return Math.floor((new Date().getTime()) / 1000); } }

// Wait for document ready before delegating events
$(document).ready(function(){

    // Collect references to key objects
    thisAdmin = $('#admin');
    thisAdminForm = $('.adminform', thisAdmin);
    thisAdminSearch = $('.adminform > .search', thisAdmin);
    thisAdminResults = $('.adminform > .results', thisAdmin);
    thisAdminEditor = $('.adminform > .editor', thisAdmin);
    $adminForm = $('.adminform form.form', thisAdmin);
    $adminAjaxForm = $('.adminform form[name="ajax-form"]', thisAdmin);
    $adminAjaxFrame = $('.adminform iframe[name="ajax-frame"]', thisAdmin);

    // Define an event for delete links and buttons
    var confirmTemplate1 = 'Are you sure you want to delete {object}? \nThis action cannot be undone.';
    var confirmTemplate2 = 'Once you delete {object}, it cannot be recovered!! \nProceed with deletion anyway?';
    $('*[data-delete]', thisAdminForm).bind('click', function(e){
        e.preventDefault();

        // Collect a reference to the object and its attributes
        var deleteLink = $(this);
        var deleteKind = deleteLink.attr('data-delete');
        var deleteID = 0;

        // Define the object label and ID based on kind
        var deleteObject = 'object';
        if (deleteKind == 'users'){
            deleteObject = 'user';
            deleteID = deleteLink.attr('data-user-id');
            if (typeof deleteID == 'undefined'){ return false; }
            deleteID = parseInt(deleteID);
            if (deleteID == 0){ return false; }
            } else {
            return false;
            }

        // Parse the confirm text and prompt the user
        var objName = deleteObject+' ID '+deleteID;
        var confirmText1 = confirmTemplate1.replace('{object}', objName);
        var confirmText2 = confirmTemplate2.replace('{object}', objName);
        if (confirm(confirmText1) && confirm(confirmText2)){

            // Define the post URL based on request kind
            var postURL = '';
            if (deleteKind == 'users'){
                postURL = 'admin.php?action=edit_users&subaction=delete&user_id='+deleteID;
                } else {
                return false;
                }

            // Send the request to the server for delete
            //console.log('we can delete '+objName+'!');
            $.post(postURL, function(data){
                // Delete successful, let's reload the page
                window.location.href = window.location.href;
                //console.log(data);
                return true;
                });

            } else {

            //console.log('delete request denied!');

            }


        });

    // Define click events for any editor tabs and panels
    var $editorTabs = thisAdminEditor.find('.editor-tabs[data-tabgroup]');
    if ($editorTabs.length){
        $editorTabs.each(function(){
            var $tabList = $(this);
            var tabGroup = $tabList.attr('data-tabgroup');
            var $tabLinks = $tabList.find('.tab[data-tab]');
            var $tabPanels = thisAdminEditor.find('.editor-panels[data-tabgroup="'+tabGroup+'"] .panel[data-tab]');
            var showTabFunction = function(tabToken){
                //console.log('show tab '+tabToken);
                $tabLinks.removeClass('active');
                $tabLinks.filter('[data-tab="'+tabToken+'"]').addClass('active');
                $tabPanels.removeClass('active');
                $tabPanels.filter('[data-tab="'+tabToken+'"]').addClass('active');
                window.location.hash = tabToken;
                };
            $('a[data-tab]', $tabList).bind('click', function(e){ e.preventDefault(); showTabFunction($(this).attr('data-tab')); });
            //console.log(window.location.hash);
            if (typeof window.location.hash !== 'undefined' && window.location.hash.length > 0){ var firstTab = window.location.hash.slice(1); }
            else { var firstTab = $tabList.find('a[data-tab]').first().attr('data-tab'); }
            showTabFunction(firstTab);
            });
        }

    // Define an event for fields that depend on other fields for thier value
    var actionInProgress = false;
    var $autoElements = $('*[data-auto]', thisAdminForm);
    if ($autoElements.length){
        $autoElements.each(function(){

            // Collect ref to auto element and its auto type
            var $element = $(this);
            var autoType = $element.attr('data-auto');

            // Define functionality for the FIELD SUM auto elements
            if (autoType === 'field-sum'){
                var autoSumFields = $element.attr('data-field-sum').split(',');
                var $autoSumFields = [];
                var sumTheseFields = function(){
                    //console.log('sumTheseFields()');
                    var fieldSum = 0;
                    for (var i = 0; i < $autoSumFields.length; i++){ var val = $autoSumFields[i].val(); fieldSum += parseInt(val); }
                    $element.val(fieldSum);
                    };
                for (var i = 0; i < autoSumFields.length; i++){
                    var $field = $('input[name="'+autoSumFields[i]+'"]', thisAdminForm);
                    if (typeof $field !== 'undefined'){
                        $autoSumFields.push($field);
                        $field.bind('keyup keydown change click', function(){ sumTheseFields(); });
                        }
                    }
                }

            // Define functionality for the FIELD TYPE auto elements
            else if (autoType === 'field-type'){
                var autoTypeFields = $element.attr('data-field-type').split(',');
                var $autoTypeFields = [];
                var updateFieldTypes = function(){
                    //console.log('updateFieldTypes()');
                    var fieldTypes = [];
                    for (var i = 0; i < $autoTypeFields.length; i++){
                        var val = $autoTypeFields[i].val();
                        if (!val.length){ if (i === 0 && autoTypeFields.length > 1){ fieldTypes.push('none'); } continue; }
                        fieldTypes.push(val);
                        }
                    $element.removeClass(function(index, className) { return (className.match (/(^|\s)type_\S+/g) || []).join(' '); });
                    $element.addClass('type_span');
                    if (!fieldTypes.length){ return; }
                    $element.addClass('type_'+fieldTypes.join('_'));
                    };
                for (var i = 0; i < autoTypeFields.length; i++){
                    var $field = $('select[name="'+autoTypeFields[i]+'"]', thisAdminForm);
                    if (typeof $field !== 'undefined'){
                        $autoTypeFields.push($field);
                        $field.bind('keyup keydown change click', function(){ updateFieldTypes(); });
                        }
                    }
                }

            // Define functionality for the FILE BAR auto elements
            else if (autoType === 'file-bar'){
                var $listItem = $element.closest('li');
                var autoFilePath = $element.attr('data-file-path');
                var autoFileName = $element.attr('data-file-name');
                var autoFileKind = $element.is('[data-file-kind]') ? $element.attr('data-file-kind') : '';
                var autoFileWidth = $element.is('[data-file-width]') ? parseInt($element.attr('data-file-width')) : '';
                var autoFileHeight = $element.is('[data-file-height]') ? parseInt($element.attr('data-file-height')) : '';
                //console.log('auto file bar! autoFilePath =', autoFilePath, 'autoFileName = ', autoFileName);
                var $uploadLink = $element.find('[data-action="upload"]');
                var $uploadInput = $uploadLink.find('input[type="file"]');
                var $deleteLink = $element.find('[data-action="delete"]');
                var $viewLink = $element.find('.link.view');
                var $statusSpan = $element.find('.info.status');
                $uploadInput.bind('click', function(e){ e.stopPropagation(); });
                var setupAjax = function(fileAction, fileHash){
                    $adminAjaxForm.empty();
                    $adminAjaxForm.append('<input type="text" name="file_path" value="'+autoFilePath+'" />');
                    $adminAjaxForm.append('<input type="text" name="file_name" value="'+autoFileName+'" />');
                    $adminAjaxForm.append('<input type="text" name="file_action" value="'+fileAction+'" />');
                    $adminAjaxForm.append('<input type="text" name="file_hash" value="'+fileHash+'" />');
                    return;
                    };
                var uploadAction = function(){
                    if (actionInProgress || $uploadLink.hasClass('disabled')){ return false; }
                    //console.log('upload action! autoFilePath =', autoFilePath, 'autoFileName = ', autoFileName);
                    var uploadInputValue = $uploadInput.val();
                    //console.log('$uploadInput = ', typeof uploadInputValue, uploadInputValue);
                    if (typeof uploadInputValue !== 'undefined'
                        && uploadInputValue.length > 0){
                        window.onUpdateImageComplete = function(status, message, details){
                            //console.log('onUpdateImageComplete(UPLOAD)! ', status, message, details);
                            if (status == 'success'){ // image was uploaded, disable upload and allow delete + view
                                $uploadLink.addClass('disabled');
                                $uploadInput.prop('disabled', true);
                                $deleteLink.removeClass('disabled');
                                $statusSpan.removeClass('bad').addClass('good').html('&check;');
                                var newViewHref = $viewLink.attr('data-href') + '?' + Date.now();
                                $viewLink.removeClass('disabled').attr('href', newViewHref);
                                $listItem.addClass('success');
                                } else if (status == 'error'){
                                alert('There was an problem uploading the image! \n' + message + ' \n' + details);
                                $listItem.addClass('error');
                                }
                            setTimeout(function(){ $listItem.removeClass('pending success error'); }, 500);
                            actionInProgress = false;
                            };
                        $listItem.addClass('pending');
                        setupAjax('upload', $uploadLink.is('[data-file-hash]') ? $uploadLink.attr('data-file-hash') : '');
                        $uploadInput.clone().appendTo($adminAjaxForm);
                        $adminAjaxForm.append('<input type="text" name="file_kind" value="'+autoFileKind+'" />');
                        $adminAjaxForm.append('<input type="text" name="file_width" value="'+autoFileWidth+'" />');
                        $adminAjaxForm.append('<input type="text" name="file_height" value="'+autoFileHeight+'" />');
                        $adminAjaxForm.submit();
                        actionInProgress = true;
                        return true;
                        } else {
                        return false;
                        }
                    };
                var deleteAction = function(){
                    if (actionInProgress || $deleteLink.hasClass('disabled')){ return false; }
                    if (confirm('Are you sure you want to delete \n' + autoFilePath+autoFileName + ' ? ' +
                        '\n' + 'This action cannot be undone! '+
                        '\n' + 'Continue?')){
                        //console.log('delete action! autoFilePath =', autoFilePath, 'autoFileName = ', autoFileName);
                        window.onUpdateImageComplete = function(status, message, details){
                            //console.log('onUpdateImageComplete(DELETE)! ', status, message, details);
                            if (status == 'success'){ // image was removed, disable delete and allow upload
                                $deleteLink.addClass('disabled');
                                $uploadLink.removeClass('disabled');
                                $uploadInput.prop('disabled', false);
                                $statusSpan.removeClass('good').addClass('bad').html('&cross;');
                                $viewLink.addClass('disabled').removeAttr('href');
                                //$listItem.addClass('success');
                                } else if (status == 'error'){
                                alert('There was an problem deleting the image! \n' + message + ' \n' + details);
                                $listItem.addClass('error');
                                }
                            setTimeout(function(){ $listItem.removeClass('pending success error'); }, 500);
                            actionInProgress = false;
                            };
                        $listItem.addClass('pending');
                        setupAjax('delete', $deleteLink.is('[data-file-hash]') ? $deleteLink.attr('data-file-hash') : '');
                        $adminAjaxForm.submit();
                        actionInProgress = true;
                        return true;
                        } else {
                        return false;
                        }
                    };
                $uploadLink.bind('click', function(e){ e.preventDefault(); return uploadAction(); });
                $uploadInput.bind('change', function(e){ e.preventDefault(); return uploadAction(); });
                $deleteLink.bind('click', function(e){ e.preventDefault(); return deleteAction(); });
                }

            });
        }


    // ROBOT EDITOR EVENTS

    // ...none at the moment


    // CHALLENGE EDITOR EVENTS

    // Check to make sure we're on the challenge editor page
    var $editChallenges = $('.adminform.edit_challenges', thisAdmin);
    //console.log('$editChallenges =', $editChallenges);
    if ($editChallenges.length){

        // Collect the MMRPG object indexes is defined
        if (typeof window.mmrpgRobotsIndex === 'undefined'){ window.mmrpgRobotsIndex = {}; }
        if (typeof window.mmrpgAbilitiesIndex === 'undefined'){ window.mmrpgAbilitiesIndex = {}; }
        if (typeof window.mmrpgAbilitiesGlobal === 'undefined'){ window.mmrpgAbilitiesGlobal = {}; }
        if (typeof window.mmrpgItemsIndex === 'undefined'){ window.mmrpgItemsIndex = {}; }

        // Define a function for refreshing a robot alt select
        var robotAltSelectRefresh = function($altSelect, robotInfo){
            var robotToken = robotInfo['robot_token'];
            var currrentAlt = $altSelect.val();
            //console.log('selected alt', robotToken, currrentAlt);
            var newOptions = '<option value="">-</option>';
            if (robotInfo['robot_image_alts'].length){
                for (var i = 0; i < robotInfo['robot_image_alts'].length; i++){
                    var altInfo = robotInfo['robot_image_alts'][i];
                    var altToken = altInfo['token'];
                    var altToken2 = altToken.replace('alt', 'Alt');
                    var altName = altInfo['name'].replace(/^([^\(\)]+)\((.*?)\s+Alt\)$/i, altToken2+' ($2)');
                    newOptions += '<option value="'+altToken+'">'+altName+'</option>';
                    }
                }
            $altSelect.empty().append(newOptions);
            $altSelect.val(currrentAlt ? currrentAlt : '');
            };

        // Define a function for refreshing robot ability selects
        var robotAbilitySelectRefresh = function($abilitySelects, robotInfo, robotItem){
            //console.log('robotAbilitySelectRefresh($abilitySelects, robotInfo)', $abilitySelects, robotInfo);
            var robotToken = robotInfo['robot_token'];
            var newOptions = '<option value="">-</option>';
            var mmrpgAbilityTokens = Object.keys(window.mmrpgAbilitiesIndex);
            if (mmrpgAbilityTokens.length){
                var optionsGroup = '';
                for (var i = 0; i < mmrpgAbilityTokens.length; i++){
                    var abilityToken = mmrpgAbilityTokens[i];
                    var abilityInfo = window.mmrpgAbilitiesIndex[abilityToken];
                    var abilityName = abilityInfo['ability_name'];
                    var abilityTypes = [];
                    if (abilityInfo['ability_type'].length){ abilityTypes.push(upperCaseFirst(abilityInfo['ability_type'])); }
                    if (abilityTypes.length && abilityInfo['ability_type2'].length){ abilityTypes.push(upperCaseFirst(abilityInfo['ability_type2'])); }
                    abilityTypes = abilityTypes.length ? abilityTypes.join(' / ') : 'Neutral';
                    if (abilityInfo['ability_class'] == 'mecha' && robotInfo['robot_class'] != 'mecha'){ var abilityIsCompatible = false; }
                    else if (abilityInfo['ability_class'] == 'boss' && robotInfo['robot_class'] != 'boss'){ var abilityIsCompatible = false; }
                    else { var abilityIsCompatible = robotHasCompatibility(robotToken, abilityToken, robotItem); }
                    if (!abilityIsCompatible){ continue; }
                    if (abilityInfo['ability_class'] != optionsGroup){
                        if (optionsGroup.length){ newOptions += '</optgroup>'; }
                        optionsGroup = abilityInfo['ability_class'];
                        newOptions += '<optgroup label="'+ upperCaseFirst(abilityInfo['ability_class']) +' Abilities">';
                    }
                    newOptions += '<option value="'+ abilityToken +'"'+ (!abilityIsCompatible ? 'disabled="disabled"' : '') +'>';
                        newOptions += abilityName +' ('+ abilityTypes  +')';
                    newOptions += '</option>';
                    }
                if (optionsGroup.length){ newOptions += '</optgroup>'; }
                }
            $abilitySelects.each(function(){
                $abilitySelect = $(this);
                var currrentAbility = $abilitySelect.val();
                //console.log('selected ability = ', robotToken, currrentAbility);
                $abilitySelect.empty().append(newOptions);
                $abilitySelect.val(currrentAbility ? currrentAbility : '');
                });
            };

        // Define a function for checking if this robot is compatible with a specific ability
        var robotHasCompatibility = function(robotToken, abilityToken, itemToken){
            //console.log('robotHasCompatibility()');
            if (typeof robotToken !== 'string' || !robotToken.length){ return false; }
            if (typeof abilityToken !== 'string' || !abilityToken.length){ return false; }
            if (typeof itemToken !== 'string' || !itemToken.length){ itemToken = ''; }
            //console.log('robotToken = ', robotToken, 'abilityToken = ', abilityToken, 'itemToken =', itemToken);
            var robotInfo = typeof window.mmrpgRobotsIndex[robotToken] !== 'undefined' ? window.mmrpgRobotsIndex[robotToken] : false;
            var abilityInfo = typeof window.mmrpgAbilitiesIndex[abilityToken] !== 'undefined' ? window.mmrpgAbilitiesIndex[abilityToken] : false;
            var itemInfo = itemToken.length && typeof window.mmrpgItemsIndex[itemToken] !== 'undefined' ? window.mmrpgItemsIndex[itemToken] : false;
            //console.log('robotInfo = ', robotInfo, 'abilityInfo = ', abilityInfo, 'itemInfo = ', itemInfo);
            if (!robotInfo || !abilityInfo){ return false; }
            if (!itemInfo){ itemToken = ''; }
            var robotCore = robotInfo['robot_core'].length ? robotInfo['robot_core'] : '';
            var robotCore2 = robotInfo['robot_core2'].length ? robotInfo['robot_core2'] : '';
            var itemCore = itemToken.length && itemToken.match(/-core$/i) ? itemToken.replace(/-core$/i, '') : '';
            if (itemCore == 'none' || itemCore == 'copy'){ itemCore = ''; }
            //console.log('robotCore = ', robotCore, 'robotCore2 = ', robotCore2, 'itemCore = ', itemCore);
            var globalAbilities = typeof window.mmrpgAbilitiesGlobal !== 'undefined' ? window.mmrpgAbilitiesGlobal : [];
            if (mmrpgAbilitiesGlobal.indexOf(abilityToken) !== -1){
                return true;
                } else if (abilityInfo['ability_type'].length || abilityInfo['ability_type2'].length){
                var allowTypes = [];
                if (robotCore.length){ allowTypes.push(robotCore); }
                if (robotCore2.length){ allowTypes.push(robotCore2); }
                if (itemCore.length){ allowTypes.push(itemCore); }
                if (allowTypes.length){
                    if (robotCore == 'copy'){ return true; }
                    else if (abilityInfo['ability_type'].length && allowTypes.indexOf(abilityInfo['ability_type']) !== -1){ return true; }
                    else if (abilityInfo['ability_type2'].length && allowTypes.indexOf(abilityInfo['ability_type2']) !== -1){ return true; }
                    }
                }
            if (robotInfo['robot_rewards']['abilities'].length){
                for (var i = 0; i < robotInfo['robot_rewards']['abilities'].length; i++){
                    if (robotInfo['robot_rewards']['abilities'][i]['token'] == abilityInfo['ability_token']){ return true; }
                    }
                }
            if (robotInfo['robot_abilities'].length){
                if (robotInfo['robot_abilities'].indexOf(abilityInfo['ability_token']) !== -1){ return true; }
            }
            return false;
        }

        // Define an onchange function for the robot token dropdowns
        var robotTokenSelectChange = function($robotSelect){
            var $robotDiv = $robotSelect.closest('.target_robot');
            var robotToken = $robotSelect.val();
            var robotInfo = robotToken && typeof window.mmrpgRobotsIndex[robotToken] !== 'undefined' ? window.mmrpgRobotsIndex[robotToken] : false;
            var $altSelect = $robotDiv.find('select[name*="robot_image"]');
            var $itemSelect = $robotDiv.find('select[name*="robot_item"]');
            var $abilitySelects = $robotDiv.find('select[name*="robot_abilities"]');
            //console.log('selected robot', robotToken, robotInfo);
            if (!robotToken || !robotInfo){
                $altSelect.empty().append('<option value="">-</option>').val('');
                $abilitySelects.each(function(){ $(this).empty().append('<option value="">-</option>').val(''); });
                return false;
                }
            robotAltSelectRefresh($altSelect, robotInfo);
            robotAbilitySelectRefresh($abilitySelects, robotInfo, $itemSelect.val());
            };

        // Define an onchange function for the robot item dropdowns
        var robotItemSelectChange = function($itemSelect){
            var $robotDiv = $itemSelect.closest('.target_robot');
            var $robotSelect = $robotDiv.find('select[name*="robot_token"]');
            var robotToken = $robotSelect.val();
            var robotInfo = robotToken && typeof window.mmrpgRobotsIndex[robotToken] !== 'undefined' ? window.mmrpgRobotsIndex[robotToken] : false;
            var $abilitySelects = $robotDiv.find('select[name*="robot_abilities"]');
            robotAbilitySelectRefresh($abilitySelects, robotInfo, $itemSelect.val());
            };

        // Attach change-events to all the robot token dropdowns
        var $robotTokenSelects = $('.target_robot select[name*="robot_token"]', $editChallenges);
        var $robotItemSelects = $('.target_robot select[name*="robot_item"]', $editChallenges);
        //console.log('$robotTokenSelects =', $robotTokenSelects);
        $robotTokenSelects.bind('change blur', function(){ robotTokenSelectChange($(this)); });
        $robotItemSelects.bind('change blur', function(){ robotItemSelectChange($(this)); });
        $robotTokenSelects.each(function(){ robotTokenSelectChange($(this)); });

        }

});

// Helper functions for simple yet annoying tasks
function upperCaseFirst(string){ return string[0].toUpperCase() + string.substring(1); }
function upperCaseWords(string){ return this.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); }); }

