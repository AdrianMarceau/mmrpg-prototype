
/* -- BACK-END JAVASCRIPT (ADMIN) -- */

// Define global object variables
var thisAdmin = false;
var thisAdminForm = false;
var thisAdminSearch = false;
var thisAdminResults = false;
var thisAdminEditor = false;
var thisRootURL = '/';
var $adminHome = false;
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
    $adminHome = $('.adminhome', thisAdmin);
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
        var deleteSubKind = false;
        var deleteID = 0;

        // Define the object label and ID based on kind
        var deleteObject = 'object';
        if (deleteKind == 'users'){
            // If we're deleting USERS set up the vars
            deleteObject = 'user';
            deleteID = deleteLink.attr('data-user-id');
            if (typeof deleteID == 'undefined'){ return false; }
            deleteID = parseInt(deleteID);
            if (deleteID == 0){ return false; }
            } else if (deleteKind == 'challenges'){
            // If we're deleting CHALLENGES set up the vars
            deleteObject = 'challenge';
            deleteID = deleteLink.attr('data-challenge-id');
            deleteSubKind = deleteLink.attr('data-challenge-kind');
            if (typeof deleteID == 'undefined'){ return false; }
            if (typeof deleteSubKind == 'undefined'){ return false; }
            deleteObject = deleteSubKind+' '+deleteObject;
            deleteID = parseInt(deleteID);
            if (deleteID == 0){ return false; }
            } else if (deleteKind == 'stars'){
            // If we're deleting STARS set up the vars
            deleteObject = 'star';
            deleteID = deleteLink.attr('data-star-id');
            if (typeof deleteID == 'undefined'){ return false; }
            deleteID = parseInt(deleteID);
            if (deleteID == 0){ return false; }
            } else {
            //console.log('unknown delete entity?');
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
                postURL = 'admin/edit-users/delete/user_id='+deleteID;
                } else if (deleteKind == 'challenges'){
                postURL = 'admin/edit-'+deleteSubKind+'-challenges/delete/challenge_id='+deleteID;
                } else if (deleteKind == 'stars'){
                postURL = 'admin/edit-stars/delete/star_id='+deleteID;
                } else {
                //console.log('unknown delete postURL?');
                return false;
                }

            // Send the request to the server for delete
            //console.log('we can delete '+objName+'!');
            $.post(postURL, function(data){
                // Delete successful, let's reload the page
                window.location.href = window.location.href.replace(location.hash,'');
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
            if ($tabList.is('.hidden')){ return true; }
            var tabGroup = $tabList.attr('data-tabgroup');
            var $tabLinks = $tabList.find('.tab[data-tab]');
            var $tabPanels = thisAdminEditor.find('.editor-panels[data-tabgroup="'+tabGroup+'"] .panel[data-tab]');
            var showTabFunction = function(tabToken){
                //console.log('show tab '+tabToken);
                var $thisTabLink = $tabLinks.filter('[data-tab="'+tabToken+'"]');
                var $thisTabPanel = $tabPanels.filter('[data-tab="'+tabToken+'"]');
                $tabLinks.removeClass('active');
                $thisTabLink.addClass('active');
                $tabPanels.removeClass('active');
                $thisTabPanel.addClass('active');
                window.location.hash = tabToken;
                var $thisCodeField = $thisTabPanel.find('.field.codemirror[data-editor-id]');
                if ($thisCodeField.length){
                    var editorID = parseInt($thisCodeField.attr('data-editor-id'));
                    codeEditor = codeEditorIndex[editorID];
                    codeEditor.refresh();
                    }
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
                    //console.log('autoTypeFields =', autoTypeFields);
                    //console.log('$autoTypeFields =', $autoTypeFields);
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
                    if (typeof $field !== 'undefined' && $field.length > 0){
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
                var autoFileExtras = $element.is('[data-file-extras]') ? $element.attr('data-file-extras') : '';
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
                    $adminAjaxForm.append('<input type="text" name="file_extras" value="'+autoFileExtras+'" />');
                    return;
                    };
                var getCleanAutoFileExtras = function(){
                    //console.log('getCleanAutoFileExtras() | autoFilePath =', autoFilePath, 'autoFileName = ', autoFileName);
                    var cleanAutoFileExtras = [];
                    if (autoFileExtras.length){
                        var baseFileExtras = autoFileExtras.split(',');
                        //console.log('baseFileExtras =', baseFileExtras.length, baseFileExtras);
                        var $tempContainer = $element.closest('.field.has-filebars');
                        //console.log('$tempContainer =', $tempContainer.length, $tempContainer);
                        var disabledFileExtras = $tempContainer.is('[data-disable-extras]') ? $tempContainer.attr('data-disable-extras') : '';
                        if (disabledFileExtras.length){ disabledFileExtras = disabledFileExtras.split(','); }
                        else { disabledFileExtras = []; }
                        //console.log('disabledFileExtras =', disabledFileExtras.length, disabledFileExtras);
                        for (var i = 0; i < baseFileExtras.length; i++){
                            var extra = baseFileExtras[i];
                            var allowed = true;
                            if (disabledFileExtras.indexOf(extra) !== -1){ allowed = false; }
                            if (!allowed){ continue; }
                            cleanAutoFileExtras.push(extra);
                            }
                        }
                    cleanAutoFileExtras = cleanAutoFileExtras.join(',');
                    //console.log('returning cleanAutoFileExtras =', cleanAutoFileExtras.length, cleanAutoFileExtras);
                    return cleanAutoFileExtras;
                    };
                var parseActionDetails = function(detailsString){
                    if (detailsString.slice(0, 1) === '{'){
                        var detailsObject = JSON.parse(detailsString);
                        if (typeof detailsObject.updated !== 'undefined'){
                            updateUpdatedImages(detailsObject.updated);
                            }
                        }
                    };
                var updateUpdatedImages = function(updatedFiles){
                    var updatedFileNames = Object.keys(updatedFiles);
                    var $updatedListItems = [];
                    for (var i = 0; i < updatedFileNames.length; i++){
                        var fileURL = updatedFileNames[i];
                        var filePath = fileURL.replace(/^(.*?)\/([^\/]+)$/i, '$1/');
                        var fileName = fileURL.replace(/^(.*?)\/([^\/]+)$/i, '$2');
                        var fileExists = updatedFiles[fileURL];
                        //console.log('fileURL = ', fileURL.length, fileURL);
                        //console.log('filePath = ', filePath.length, filePath);
                        //console.log('fileName = ', fileName.length, fileName);
                        //console.log('fileExists = ', fileExists);
                        //console.log(fileURL+' '+(fileExists ? 'exists now!' : 'doesn\'t exist anymore!'));
                        var $tempField = $listItem.closest('.field.has-filebars');
                        var $tempElements = $tempField.find('.filebar[data-file-path="'+filePath+'"][data-file-name="'+fileName+'"]');
                        //var $tempElements = $tempField.find('.filebar[data-file-name="'+fileName+'"]');
                        $tempElements.each(function(){
                            var $tempElement = $(this);
                            var $tempListItem = $tempElement.closest('li');
                            $updatedListItems.push($tempListItem);
                            //console.log('$tempField = ', $tempField.length, $tempField);
                            //console.log('$tempElement = ', $tempElement.length, $tempElement);
                            var $tempUploadLink = $tempElement.find('[data-action="upload"]');
                            var $tempUploadInput = $tempUploadLink.find('input[type="file"]');
                            var $tempDeleteLink = $tempElement.find('[data-action="delete"]');
                            var $tempViewLink = $tempElement.find('.link.view');
                            var $tempStatusSpan = $tempElement.find('.info.status');
                            if (fileExists){
                                $tempUploadLink.addClass('disabled');
                                $tempUploadInput.prop('disabled', true);
                                $tempDeleteLink.removeClass('disabled');
                                $tempStatusSpan.removeClass('bad').addClass('good').html('&check;');
                                var tempNewViewHref = $tempViewLink.attr('data-href') + '?' + Date.now();
                                $tempViewLink.removeClass('disabled').attr('href', tempNewViewHref);
                                $tempListItem.addClass('success');
                                } else {
                                $tempDeleteLink.addClass('disabled');
                                $tempUploadLink.removeClass('disabled');
                                $tempUploadInput.prop('disabled', false);
                                $tempStatusSpan.removeClass('good').addClass('bad').html('&cross;');
                                $tempViewLink.addClass('disabled').removeAttr('href');
                                //$listItem.addClass('success');
                                }
                            });
                        }
                    setTimeout(function(){
                        for (var i = 0; i < $updatedListItems.length; i++){
                            var $tempListItem = $updatedListItems[i];
                            $tempListItem.removeClass('pending success error');
                            }
                        }, 500);
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
                                $uploadInput.val('');
                                $deleteLink.removeClass('disabled');
                                $statusSpan.removeClass('bad').addClass('good').html('&check;');
                                var newViewHref = $viewLink.attr('data-href') + '?' + Date.now();
                                $viewLink.removeClass('disabled').attr('href', newViewHref);
                                $listItem.addClass('success');
                                parseActionDetails(details);
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
                        $adminAjaxForm.append('<input type="text" name="file_extras" value="'+getCleanAutoFileExtras()+'" />');
                        //console.log('$adminAjaxForm.html() =', $adminAjaxForm.html());
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
                                parseActionDetails(details);
                                } else if (status == 'error'){
                                alert('There was an problem deleting the image! \n' + message + ' \n' + details);
                                $listItem.addClass('error');
                                }
                            setTimeout(function(){ $listItem.removeClass('pending success error'); }, 500);
                            actionInProgress = false;
                            };
                        $listItem.addClass('pending');
                        setupAjax('delete', $deleteLink.is('[data-file-hash]') ? $deleteLink.attr('data-file-hash') : '');
                        //console.log('$adminAjaxForm.html() =', $adminAjaxForm.html());
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


    // Generate events for any generator checkboxes in fields with file-upload bars
    var $fieldsWithFileBars = $('.field.has-filebars', thisAdminForm);
    //console.log('$fieldsWithFileBars =', $fieldsWithFileBars.length, $fieldsWithFileBars);
    if ($fieldsWithFileBars.length){
        $fieldsWithFileBars.each(function(){
            var $fileBarContainer = $(this);
            var $autoShadowsCheckbox = $fileBarContainer.next('.options').find('input[type="checkbox"][name$="\[generate_shadows\]"]');
            //console.log('$fileBarContainer =', $fileBarContainer.length, $fileBarContainer);
            //console.log('$autoShadowsCheckbox =', $autoShadowsCheckbox.length, $autoShadowsCheckbox);
            if ($autoShadowsCheckbox.length){
                var $shadowFileBars = $fileBarContainer.find('.subfield[data-group="shadows"]');
                $autoShadowsCheckbox.bind('change', function(e){
                    var isChecked = $autoShadowsCheckbox.is(':checked') ? true : false;
                    //console.log('isChecked =', isChecked);
                    if (isChecked){
                        $shadowFileBars.css({display:'block'});
                        $fileBarContainer.removeAttr('data-disable-extras');
                        } else {
                        $shadowFileBars.css({display:'none'});
                        $fileBarContainer.attr('data-disable-extras', 'auto-shadows');
                        }
                    }).trigger('change');
                }
            });
        }


    // COMMON HOME EVENTS

    // Check to make sure we're on the admin home page
    if ($adminHome.length){

        // Define events for any button areas under admin home
        var $homeButtons = $('.buttons', $adminHome);
        if ($homeButtons.length){
            var postURLs = {
                revert: 'admin/scripts/revert-game-content.php',
                commit: 'admin/scripts/commit-game-content.php',
                publish: 'admin/scripts/push-game-content.php',
                update: 'admin/scripts/pull-game-content.php'
                };
            var confirmMessages = {
                revert: 'Are you absolutely sure you want to revert changes to all {object}?\n'
                    + 'This action cannot be undone and all updates will be lost.\n'
                    + 'Continue anyway?',
                commit: 'Are you absolutely sure you want to commit changes to all {object}?\n'
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                publish: 'Are you absolutely sure you want to publish changes to all {object}?\n'
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                update: 'Are you sure you want to update {object} with remote changes?\n'
                    + 'Updates cannot be reverted once applied.\n'
                    + 'Continue anyway? ',
                final: 'I\'m sorry but I have to confirm one more time...\n'
                    + 'Are you absolutely, 100% sure you want to \n'
                    + '{action} CHANGES TO {object}? '
                }
            $('a[data-action]', $homeButtons).bind('click', function(){
                var $thisButton = $(this);
                var thisButtonType = $thisButton.attr('data-button');
                if (typeof thisButtonType === 'undefined'){
                    //console.log('Undefined button type');
                    return false;
                    } else if (thisButtonType === 'git'){
                    //console.log('Git button type');
                    var thisKind = $thisButton.attr('data-kind');
                    var thisSubKind = $thisButton.attr('data-subkind');
                    var thisToken = $thisButton.attr('data-token');
                    var thisSource = $thisButton.attr('data-source');
                    var thisAction = $thisButton.attr('data-action');
                    if (typeof postURLs[thisAction] === 'undefined'){ return false; }
                    if (typeof confirmMessages[thisAction] === 'undefined'){ return false; }
                    if (typeof thisSubKind === 'undefined'){ thisSubKind = ''; }
                    var postURL = thisRootURL+postURLs[thisAction];
                    var postData = {kind:thisKind,subkind:thisSubKind,token:thisToken,source:thisSource};
                    var confirmMessage = confirmMessages[thisAction];
                    if (thisKind === 'sql'){ confirmMessage = confirmMessage.replace(/\{object\}/g, 'miscellaneous objects (stars, challenges, pages, etc.)'); }
                    else if (thisSubKind.length){ confirmMessage = confirmMessage.replace(/\{object\}/g, thisSubKind); }
                    else { confirmMessage = confirmMessage.replace(/\{object\}/g, thisKind); }
                    //console.log('postURL = ', postURL);
                    //console.log('postData = ', postData);
                    //console.log('confirmMessage = ', confirmMessage);
                    var allowButtonAction = confirm(confirmMessage);
                    if (allowButtonAction){
                        if (thisAction !== 'update'){
                            var confirmMessage2 = confirmMessages['final'];
                            if (thisAction === 'publish'){ confirmMessage2 = confirmMessage2.replace(/\{action\}/g, 'PUBLISH ALL'); }
                            else { confirmMessage2 = confirmMessage2.replace(/\{action\}/g, $thisButton.text().toUpperCase()); }
                            if (thisKind === 'sql'){ confirmMessage2 = confirmMessage2.replace(/\{object\}/g, 'MISC OBJECTS'); }
                            else if (thisSubKind.length){ confirmMessage2 = confirmMessage2.replace(/\{object\}/g, thisSubKind.toUpperCase()); }
                            else { confirmMessage2 = confirmMessage2.replace(/\{object\}/g, thisKind.toUpperCase()); }
                            //console.log('confirmMessage2 = ', confirmMessage2);
                            allowButtonAction = confirm(confirmMessage2);
                            }
                        if (allowButtonAction){
                            $thisButton.addClass('loading');
                            $adminHome.addClass('loading');
                            $.post(postURL, postData, function(returnData){
                                //console.log('returnData = ', returnData);
                                if (typeof returnData !== 'undefined' && returnData.length){
                                    var lineData = returnData.split('\n');
                                    //console.log('lineData = ', lineData);
                                    var statusLine = lineData[0].split('|');
                                    //console.log('statusLine = ', statusLine);
                                    if (statusLine[0] === 'success'){
                                        var completeFunction = function(){
                                            window.location.href = window.location.href.replace(location.hash,'');
                                            //alert('Reload the window!');
                                            };
                                        printStatusMessage(statusLine[0], statusLine[1], completeFunction);
                                        } else {
                                        printStatusMessage(statusLine[0], statusLine[1]);
                                        $thisButton.removeClass('loading');
                                        $adminHome.removeClass('loading');
                                        }
                                    }
                                });
                            }
                        }
                    return true;
                    } else if (thisButtonType === 'table'){
                    //console.log('Table button type');
                    return false; // not done yet
                    }
            });
        }

        }


    // COMMON EDITOR EVENTS

    // Check to make sure we're on an admin editor page
    var codeEditorIndex = {};
    if ($adminForm.length){

        // Replace any compatible textareas with CodeMirror instances
        if (typeof window.CodeMirror !== 'undefined'){
            var $codeMirrorFields = $('.field.codemirror', $adminForm);
            //console.log('$codeMirrorFields =', $codeMirrorFields.length, $codeMirrorFields);
            $codeMirrorFields.each(function(){
                var $codeField = $(this);
                var $textArea = $codeField.find('textarea');
                var textArea = $textArea.get(0);
                //$textArea.css({height:'auto'});
                var editorID = Object.keys(codeEditorIndex).length + 1;
                var editorMode = $codeField.is('[data-codemirror-mode]') ? $codeField.attr('data-codemirror-mode') : 'html';
                var editorConfig = {
                    mode: editorMode, //'htmlmixed',
                    tabSize: 2,
                    indentWithTabs: false,
                    lineWrapping: true
                    };
                // custom HTML settings
                if (editorMode === 'html'){
                    editorConfig.mode = 'htmlmixed';
                    editorConfig.lineNumbers = true;
                    }
                // custom JSON settings
                else if (editorMode === 'json'){
                    //editorConfig.mode = 'javascript';
                    //editorConfig.mode = 'application/ld+json';
                    editorConfig.mode = {
                        name: "application/json",
                        json: true,
                        statementIndent: 2
                        };
                    editorConfig.lineNumbers = false;
                    editorConfig.matchBrackets = true;
                    editorConfig.autoCloseBrackets = true;
                    }
                // custom PHP settings
                else if (editorMode === 'php'){
                    editorConfig.mode = 'application/x-httpd-php';
                    editorConfig.tabsize = 4;
                    editorConfig.indentUnit = 4;
                    editorConfig.lineNumbers = true;
                    editorConfig.matchBrackets = true;
                    editorConfig.autoCloseBrackets = true;
                    }

                if ($codeField.hasClass('readonly')){ editorConfig.readOnly = true; }
                //console.log('editorConfig =', editorConfig);
                var codeEditor = CodeMirror.fromTextArea(textArea, editorConfig);
                codeEditorIndex[editorID] = codeEditor;
                $codeField.attr('data-editor-id', editorID);
                });
            }

        // Define events for any git-button areas under editor forms
        var $editorGitButtons = $('.git-buttons', $adminForm);
        if ($editorGitButtons.length){
            var postURLs = {
                revert: 'admin/scripts/revert-game-content.php',
                commit: 'admin/scripts/commit-game-content.php',
                publish: 'admin/scripts/push-game-content.php',
                update: 'admin/scripts/pull-game-content.php'
                };
            var confirmMessages = {
                revert: 'Are you absolutely sure you want to revert the changes to this {object}?\n'
                    + 'This action cannot be undone and any updates will be lost.\n'
                    + 'Continue anyway?',
                commit: 'Are you absolutely sure you want to commit the changes to this {object}?\n'
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                publish: 'Are you absolutely sure you want to publish the changes to this {object}?\n'
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                update: 'Are you sure you want to this {object} with remote changes?\n'
                    + 'Updates cannot be reverted once applied.\n'
                    + 'Continue anyway? '
                }
            $('a[data-action]', $editorGitButtons).bind('click', function(){
                var $thisButton = $(this);
                var $thisWrapper = $thisButton.closest('.git-buttons');
                var thisKind = $thisWrapper.attr('data-kind');
                var thisSubKind = $thisWrapper.attr('data-subkind');
                var thisToken = $thisWrapper.attr('data-token');
                var thisSource = $thisWrapper.attr('data-source');
                var thisAction = $thisButton.attr('data-action');
                if (typeof postURLs[thisAction] === 'undefined'){ return false; }
                if (typeof confirmMessages[thisAction] === 'undefined'){ return false; }
                if (typeof thisSubKind === 'undefined'){ thisSubKind = ''; }
                var postURL = thisRootURL+postURLs[thisAction];
                var postData = {kind:thisKind,subkind:thisSubKind,token:thisToken,source:thisSource};
                var confirmMessage = confirmMessages[thisAction];
                if (thisSubKind.length){ confirmMessage = confirmMessage.replace(/\{object\}/g, makeObjectSingular(thisSubKind)); }
                else { confirmMessage = confirmMessage.replace(/\{object\}/g, makeObjectSingular(thisKind)); }
                //console.log('postURL = ', postURL);
                //console.log('postData = ', postData);
                //console.log('confirmMessage = ', confirmMessage);
                if (confirm(confirmMessage)){
                    $thisButton.addClass('loading');
                    thisAdminForm.addClass('loading');
                    $.post(postURL, postData, function(returnData){
                        //console.log('returnData = ', returnData);
                        if (typeof returnData !== 'undefined' && returnData.length){
                            var lineData = returnData.split('\n');
                            //console.log('lineData = ', lineData);
                            var statusLine = lineData[0].split('|');
                            //console.log('statusLine = ', statusLine);
                            if (statusLine[0] === 'success'){
                                var completeFunction = function(){
                                    window.location.href = window.location.href.replace(location.hash,'');
                                    //alert('Reload the window!');
                                    };
                                printStatusMessage(statusLine[0], statusLine[1], completeFunction);
                                } else {
                                printStatusMessage(statusLine[0], statusLine[1]);
                                $thisButton.removeClass('loading');
                                thisAdminForm.removeClass('loading');
                                }
                            }
                        });
                    }
            });
        }

    }


    // PAGE EDITOR EVENTS

    // Check to make sure we're on the page editor page
    var $editPages = $('.adminform.edit-pages', thisAdmin);
    //console.log('$editPages =', $editPages);
    if ($editPages.length){

        // ...

    }


    // STAR EDITOR EVENTS

    // Check to make sure we're on the star editor page
    var $editStars = $('.adminform.edit-stars', thisAdmin);
    var litePickerIndex = {};
    //console.log('$editStars =', $editStars);
    if ($editStars.length){

        // Replace any compatible textareas with LitePicker instances
        if (typeof window.Litepicker !== 'undefined'){
            var $litePickerFields = $('.field.litepicker', $editStars);
            $litePickerFields.each(function(){
                var $pickerField = $(this);
                //console.log('adding new picker field to ', $pickerField.attr('class'));
                if ($pickerField.hasClass('readonly')){ return true; }
                if ($pickerField.hasClass('haspicker')){ return true; }
                var $textInput = $pickerField.find('input[type="text"]').first();
                var textInput = $textInput.get(0);
                var pickerID = Object.keys(litePickerIndex).length + 1;
                var pickerConfig = {
                    element: textInput,
                    autoApply: true,
                    lang: 'en-US',
                    format: 'YYYY-MM-DD',
                    firstDay: 1,
                    minDays: 1
                    };
                if ($textInput.is('[data-next-name]')){
                    //console.log('data-next-name found! looking for next input...');
                    var nextName = $textInput.attr('data-next-name');
                    var $nextInput = $editStars.find('input[name="'+nextName+'"]');
                    var $nextField = $nextInput.closest('.field.litepicker');
                    if ($nextInput.length){
                        //console.log('...end-date found! updating config...');
                        var nextInput = $nextInput.get(0);
                        pickerConfig.elementEnd = nextInput;
                        pickerConfig.singleMode = false;
                        pickerConfig.selectForward = true;
                        pickerConfig.numberOfMonths = 2;
                        pickerConfig.numberOfColumns = 2;
                        $nextField.addClass('haspicker');
                        }
                    }
                var litePicker = new Litepicker(pickerConfig);
                litePickerIndex[pickerID] = litePicker;
                $pickerField.attr('data-picker-id', pickerID);
                $pickerField.addClass('haspicker');
                });
            }

            //var picker = new Litepicker({ element: document.getElementById('litepicker') });

    }


    // ROBOT EDITOR EVENTS

    // ...none at the moment


    // CHALLENGE EDITOR EVENTS

    // Check to make sure we're on the challenge editor page
    var $editChallenges = $('.adminform.edit-challenges', thisAdmin);
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


    // FIELD EDITOR EVENTS

    // Check to make sure we're on the field editor page
    var $editFields = $('.adminform.edit-fields', thisAdmin);
    //console.log('$editFields =', $editFields);
    if ($editFields.length){

        // Collect references to the background/foreground attachment containers
        var $previewContainer = $('.bfg-attachments-preview', $editFields);
        var $inputContainers = $('.bfg-attachments-inputs', $editFields);
        if ($previewContainer.length
            && $inputContainers.length){

            // Collect the base background and foreground tokens
            var baseFieldImages = {};
            baseFieldImages['background'] = $previewContainer.attr('data-field-background');
            baseFieldImages['foreground'] = $previewContainer.attr('data-field-foreground');
            //console.log('baseFieldImages = ', baseFieldImages);

            // Define references to important attachment containers
            var $previewBackgroundAttachments = $('.background_attachments', $previewContainer);
            var $previewForegroundAttachments = $('.foreground_attachments', $previewContainer);

            // Define a function for triggering background/foreground visibility in the preview
            function bfgToggleVisibility($button, kind, ucKind){
                //console.log('bfgToggleVisibility('+kind+');');
                var $bfgImageDiv = $('.'+kind+'_image', $previewContainer);
                var $bfgAttachmentsDiv = $('.'+kind+'_attachments', $previewContainer);
                if (!$bfgImageDiv.hasClass('hidden')){ $bfgImageDiv.addClass('hidden'); $bfgAttachmentsDiv.addClass('hidden'); $button.attr('value', 'Show '+ucKind); }
                else { $bfgImageDiv.removeClass('hidden'); $bfgAttachmentsDiv.removeClass('hidden'); $button.attr('value', 'Hide '+ucKind); }
                }

            // Define a function for parsing attachment data from a given row
            function parseAttachmentData($row, kind, parsedAttachments){
                var $inputs = $('input[name],select[name]', $row);
                if (!$inputs.length){ return false; }
                var data = {kind: kind};
                $inputs.each(function(){
                    var $input = $(this);
                    var key_name = $input.attr('name').replace(/^(?:.*?)\[([^\[\]]+)\]\[([^\[\]]+)\]$/, '$1/$2').split('/');
                    if (typeof data.key === 'undefined'){ data.key = parseInt(key_name[0]); }
                    var name = key_name[1];
                    if ($input.is('select')){ var value = $input.find('option:selected').val(); }
                    else { var value = $input.val(); }
                    if (value === ''){ data = false; return; }
                    if ($input.is('[type="number"]')){ value = parseInt(value); }
                    data[name] = value;
                    });
                if (!data){ return false; }
                data.float = data.direction !== 'left' ? 'left' : 'right';
                data.highlight = $row.find('.bfg-view input[type="checkbox"]').is(':checked') ? true : false;
                if (typeof parsedAttachments !== 'undefined'){ parsedAttachments.push(data); return true; }
                else { return data; }
            }

            // Define a function for generating sprite markup for a specific attachment
            function getAttachmentSpriteMarkup(data, returnArray){
                //console.log('getAttachmentSpriteMarkup(data, returnArray)', data, returnArray);
                if (typeof returnArray !== 'boolean'){ returnArray = false; }
                var spriteSize = data.size+'x'+data.size;
                var spriteClass = data.class;
                var spriteDir = data.class !== 'object' ? data.class+'s' : 'fields';
                var spriteToken = data.class !== 'object' ? data.token : baseFieldImages[data.kind]+'_'+data.token;
                var spriteImage = 'images/'+spriteDir+'/'+spriteToken+'/sprite_'+data.direction+'_'+spriteSize+'.png';
                var spriteClass = 'sprite sprite_'+spriteSize+' sprite_'+spriteSize+'_'+data.direction+' sprite_'+spriteSize+'_00 ';
                if (data.frame.length){ var frame = data.frame.split(',')[0]; spriteClass += 'sprite_'+spriteSize+'_'+('00'+frame).substring(frame.length)+' '; }
                if (data.highlight){ spriteClass += 'highlight '; }
                var spriteStyle = data.float+': '+data.offset_x+'px; bottom: '+data.offset_y+'px; z-index: '+(data.key + 1)+'; background-image: url('+spriteImage+');';
                if (returnArray){ return [spriteClass, spriteStyle, data.key]; }
                var markup = '<div class="'+spriteClass+'" style="'+spriteStyle+'" data-key="'+data.key+'"></div>';
                return markup;
            }

            // Define a function for looping through parsed attachment data and appending sprites
            function displayAttachmentSprites(parsedAttachments){
                $previewBackgroundAttachments.find('.sprite').remove();
                $previewForegroundAttachments.find('.sprite').remove();
                var newBackgroundSprites = [];
                var newForegroundSprites = [];
                for (var i = 0; i < parsedAttachments.length; i++){
                    var data = parsedAttachments[i];
                    var markup = getAttachmentSpriteMarkup(data);
                    if (data.kind === 'background'){ newBackgroundSprites.push(markup); }
                    else if (data.kind === 'foreground'){ newForegroundSprites.push(markup); }
                }
                $previewBackgroundAttachments.append(newBackgroundSprites.join(''));
                $previewForegroundAttachments.append(newForegroundSprites.join(''));
            }

            // Define a function for refreshing all attachments at once
            function refreshAllAttachments(){
                //console.log('refreshAttachments()');
                var parsedAttachments = [];
                $inputContainers.each(function(){
                    var $inputContainer = $(this);
                    var inputContainerKind = $inputContainer.attr('data-kind');
                    $('.bfg-attachment', $inputContainer).each(function(){
                        parseAttachmentData($(this), inputContainerKind, parsedAttachments);
                        });
                    });
                //console.log('parsedAttachments =', parsedAttachments);
                displayAttachmentSprites(parsedAttachments);
            }

            // Define click events for the background/foreground toggle buttons
            $('input[name="toggle_background"],input[name="toggle_foreground"]', $previewContainer).each(function(e){
                var $button = $(this);
                var kind = $button.attr('name').replace('toggle_', '');
                var ucKind = $button.attr('value').replace('Toggle ', '');
                $button.attr('value', 'Hide '+ucKind);
                $button.bind('click', function(e){
                    e.preventDefault();
                    bfgToggleVisibility($button, kind, ucKind);
                    });
                });

            // Automatically populate certain fields when the attachment type is mecha
            $('select[name$="\[class\]"]', $inputContainers).live('change', function(e){
                var $classSelect = $(this);
                var $parentRow = $classSelect.closest('.bfg-attachment');
                var $tokenInput = $parentRow.find('input[name$="\[token\]"]');
                var $directionSelect = $parentRow.find('select[name$="\[direction\]"]');
                var $offsetXInput = $parentRow.find('input[name$="\[offset_x\]"]');
                var $offsetYInput = $parentRow.find('input[name$="\[offset_y\]"]');
                var $frameInput = $parentRow.find('input[name$="\[frame\]"]');
                var classValue = $classSelect.find('option:selected').val();
                //console.log('class changed to ', classValue);
                $tokenInput.val('').prop('readonly', true);
                $directionSelect.val('').prop('disabled', true);
                $offsetXInput.val('').prop('disabled', true);
                $offsetYInput.val('').prop('disabled', true);
                $frameInput.val('').prop('readonly', true);
                 if (classValue !== ''){
                    if (classValue === 'robot'){ $tokenInput.val('met'); }
                    else { $tokenInput.prop('readonly', false); }
                    $directionSelect.prop('disabled', false);
                    $offsetXInput.prop('disabled', false);
                    $offsetYInput.prop('disabled', false);
                    if (classValue === 'robot'){ $frameInput.val('0'); }
                    else { $frameInput.prop('readonly', false); }
                    }
                });

            // Automatically update individual sprites on input/select change
            $('input[name],select[name]', $inputContainers).live('change', function(e){
                var $input = $(this);
                var $inputRow = $input.closest('.bfg-attachment');
                var inputRowKey = parseInt($inputRow.attr('data-key'));
                var $inputContainer = $inputRow.closest('.bfg-attachments-inputs');
                var inputContainerKind = $inputContainer.attr('data-kind');
                var $thisPreviewContainer = $('.'+inputContainerKind+'_attachments', $previewContainer);
                var $thisSprite = $thisPreviewContainer.find('.sprite[data-key="'+inputRowKey+'"]');
                var attachmentData = parseAttachmentData($inputRow, inputContainerKind);
                if (!attachmentData){ if ($thisSprite.length){ $thisSprite.remove(); } return false; }
                var attachmentMarkup = getAttachmentSpriteMarkup(attachmentData, true);
                if ($thisSprite.length){ $thisSprite.attr('class', attachmentMarkup[0]).attr('style', attachmentMarkup[1]); }
                else { $thisPreviewContainer.append('<div class="'+attachmentMarkup[0]+'" style="'+attachmentMarkup[1]+'" data-key="'+attachmentMarkup[2]+'"></div>'); }
                });

            // Define a click event for the highlight checkboxes along the side of the rows
            $('.bfg-view input[type="checkbox"]', $inputContainers).live('change', function(e){
                var $checkbox = $(this);
                var isChecked = $checkbox.is(':checked') ? true : false;
                var attachmentKind = $checkbox.attr('data-kind');
                var attachmentKey = $checkbox.attr('data-key');
                //console.log('checkbox changed! isChecked =', isChecked);
                var $thisPreviewContainer = $('.'+attachmentKind+'_attachments', $previewContainer);
                var $thisSprite = $thisPreviewContainer.find('.sprite[data-key="'+attachmentKey+'"]');
                if (isChecked){ $thisSprite.addClass('highlight'); }
                else { $thisSprite.removeClass('highlight'); }
                });

            // Prevent invalid characters or keys from being used in certain fields
            $('input[name]', $inputContainers).live("keypress", function(e){
                var inputName = $(this).attr('name').replace(/^(?:.*?)\[([^\[\]]+)\]$/, '$1');
                //console.log('test inputName =', inputName, 'and e.charCode =', e.charCode);
                // Always disable the enter key in these fields to prevent accidental form submission
                if (e.keyCode == '13'){
                    e.preventDefault();
                    return;
                    }
                // Only allow numbers and commas in the frame field
                else if (inputName === 'frame'){
                    var regex = new RegExp("^[0-9,]+$");
                    var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                    if (!regex.test(key)) {
                        e.preventDefault();
                        return false;
                        }
                    }
                });

            // Define the click event for the add-attachment button under each section
            $('.button.add-attachment', $inputContainers).bind('click', function(e){
                e.preventDefault();
                var $buttonParent = $(this).closest('.bfg-attachments-inputs');
                var numExistingRows = $('.field.bfg-attachment[data-key!="{x}"]', $buttonParent).length;
                var $templateRow = $buttonParent.find('.field.bfg-attachment[data-key="{x}"]');
                var templateMarkup = $templateRow[0].outerHTML;
                var templateKey = numExistingRows;
                var templateNum = templateKey + 1;
                templateMarkup = templateMarkup.replace(/#\{x\}/g, '#'+templateNum);
                templateMarkup = templateMarkup.replace(/\{x\}/g, templateKey);
                var $newRow = $(templateMarkup);
                $newRow.insertBefore($templateRow);
                $newRow.find('select[name$="\[class\]"]').trigger('change');
                });

            // Auto-refresh all attachments on page load
            refreshAllAttachments();


        }

    }

});

// Define a common action for printing a status message at the top of the editor
function printStatusMessage(messageStatus, messageText, onCompleteFunction){
    if (typeof onCompleteFunction !== 'function'){ onCompleteFunction = function(){}; }
    var $messagesDiv = $('.messages', thisAdminEditor);
    if (!$messagesDiv.length){
        $messagesDiv = $('<div class="messages"><ul class="list"></ul></div>');
        if (thisAdminEditor.length){ $messagesDiv.insertBefore(thisAdminEditor.find('.editor-tabs[data-tabgroup]')); }
        else if (thisAdmin.length){ $messagesDiv.insertAfter(thisAdmin.find('.breadcrumb')); }
        }
    var $messagesList = $('.list', $messagesDiv);
    var $messagesListItems = $('.message', $messagesList);
    var printThisMessage = function(){
        var $newMessage = $('<li class="message '+messageStatus+'">'+messageText+'</li>');
        $newMessage.css({opacity: 0, height: '1px'}).appendTo($messagesList);
        $newMessage.animate({opacity: 1, height: '16px'}, 400, 'swing', function(){ $newMessage.css({height: 'auto'}); onCompleteFunction(); });
        };
    if ($messagesListItems.length){
        var numMessages = $messagesListItems.length;
        var numMessagesRemoved = 0;
        var removeNextMessage = function(){
            $nextMessage = $('.message', $messagesList).first();
            $nextMessage.animate({opacity: 0, height: '1px'}, 200, 'swing', function(){
                $nextMessage.remove();
                numMessagesRemoved++;
                if (numMessagesRemoved >= numMessages){
                    setTimeout(function(){ printThisMessage() }, 200);
                    } else {
                    removeNextMessage();
                    }
                });
            };
        removeNextMessage();
        } else {
        printThisMessage();
        }
}

// Helper functions for simple yet annoying tasks
function upperCaseFirst(string){ return string[0].toUpperCase() + string.substring(1); }
function upperCaseWords(string){ return this.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); }); }
function makeObjectSingular(pluralObject){ return pluralObject.replace(/ies$/i, 'y').replace(/ses$/i, 's').replace(/s$/i, ''); }

