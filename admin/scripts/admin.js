
/* -- BACK-END JAVASCRIPT (ADMIN) -- */

// Define global object variables
var thisAdmin = false;
var thisAdminForm = false;
var thisAdminSearch = false;
var thisAdminResults = false;
var thisAdminEditor = false;
var $adminHome = false;
var $adminForm = false;
var $adminAjaxForm = false;
var $adminAjaxFrame = false;
var thisRootURL = '/';

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
    if (typeof window.mmrpgConfigRootURL !== 'undefined'){ thisRootURL = window.mmrpgConfigRootURL; }

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
        var deleteBaseURL = thisAdminForm.attr('data-baseurl');
        var deleteObject = thisAdminForm.attr('data-object');
        var deleteXObject = thisAdminForm.attr('data-xobject');
        var deleteObjectName = '';
        //console.log('deleteBaseURL = ', deleteBaseURL);
        //console.log('deleteObject = ', deleteObject);
        //console.log('deleteXObject = ', deleteXObject);
        if (typeof deleteBaseURL !== 'undefined'
            && typeof deleteObject !== 'undefined'
            && typeof deleteXObject !== 'undefined'
            && deleteKind === deleteXObject){

            // If we're deleting a valid object, set up the vars
            deleteID = deleteLink.attr('data-'+deleteObject+'-id');
            deleteSubKind = deleteLink.attr('data-'+deleteObject+'-kind');
            if (typeof deleteID == 'undefined'){ return false; }
            if (typeof deleteSubKind != 'undefined'){ deleteObjectName += deleteSubKind+' '; }
            else { deleteSubKind = false; }
            deleteObjectName += deleteObject+' ';
            deleteID = parseInt(deleteID);
            if (deleteID == 0){ return false; }
            deleteObjectName += 'ID '+deleteID;

            } else {

            alert('Unknown delete entity! Contact the admin!');
            //console.log('deleteBaseURL = ', deleteBaseURL);
            //console.log('deleteObject = ', deleteObject);
            //console.log('deleteXObject = ', deleteXObject);
            return false;

            }

        // Parse the confirm text and prompt the user
        var confirmText1 = confirmTemplate1.replace('{object}', deleteObjectName);
        var confirmText2 = confirmTemplate2.replace('{object}', deleteObjectName);
        if (confirm(confirmText1) && confirm(confirmText2)){

            // Generate the post URL given known URLs and information
            var postURL = deleteBaseURL+'delete/'+deleteObject+'_id='+deleteID;

            // Send the request to the server for delete
            //console.log('we can delete '+deleteObjectName+'!');
            //console.log('using the URL ', postURL);
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
                publish: 'admin/scripts/publish-game-content.php',
                update: 'admin/scripts/update-game-content.php'
                };
            var confirmMessages = {
                revert: 'Are you sure you want to revert uncommitted changes to all {object}? '
                    + 'This action cannot be undone and all updates will be lost.\n'
                    + 'Continue anyway?',
                commit: 'Are you sure you want to commit all changes to {object}? '
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                publish: 'Are you sure you want to publish all changes to {object}?\n'
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                update: 'Are you sure you want to update {object} with remote changes? '
                    + 'Updates cannot be reverted once applied.\n'
                    + 'Continue anyway? ',
                final: 'I\'m sorry but I have to confirm one more time...\n'
                    + 'Are you absolutely, 100% sure you want to \n'
                    + '{action} CHANGES TO {object}? '
                }
            var homeDataActionTimeout = false;
            var homeDataActionHandler = function($thisButton, thisAction, postURL, postData){
                $thisButton.addClass('loading');
                $adminHome.addClass('loading');
                $.post(postURL, postData, function(returnData){
                    //console.log('returnData = ', returnData);
                    if (typeof returnData !== 'undefined' && returnData.length){
                        var lineData = returnData.split('\n');
                        //console.log('lineData = ', lineData);
                        var statusLine = lineData[0].split('|');
                        //console.log('statusLine = ', statusLine);
                        if (statusLine[0] === 'success'
                            || statusLine[0] === 'completed'){

                            var completeFunction = function(){
                                $('html, body').animate({ scrollTop: 0 }, 'fast', function(){
                                    window.location.href = window.location.href.replace(location.hash,'');
                                    //alert('Reload the window!');
                                    });
                                };
                            var statusToken = statusLine[0];
                            var statusText = typeof statusLine[1] !== 'undefined' && statusLine[1].length ? statusLine[1] : (statusToken.charAt(0).toUpperCase() + statusToken.slice(1));
                            printStatusMessage('success', statusText, completeFunction);

                            } else if (statusLine[0] === 'pending'){


                            //alert('request is pending?A');
                            $('html, body').animate({ scrollTop: 0 }, {easing: 'linear', duration: 'fast', queue: false});
                            if (homeDataActionTimeout !== false){ clearTimeout(homeDataActionTimeout); }
                            postURL = 'admin/scripts/cron_check-git-status.php';
                            if (thisAction === 'publish'){ postData = {'kind': 'git-push'}; }
                            else if (thisAction === 'update'){ postData = {'kind': 'git-pull'}; }
                            if (typeof statusLine[1] !== 'undefined' && statusLine[1].length){
                                //console.log('statusline1 exists, printing | statusLine =', statusLine);
                                $('.messages', thisAdmin).find('.message.pending').remove();
                                printStatusMessage(statusLine[0], statusLine[1]);
                            } else {
                                //console.log('statusline1 NOT exists, appending | statusLine =', statusLine);
                                $('.messages', thisAdmin).find('.message.pending').append('.');
                            }

                            homeDataActionTimeout = setTimeout(function(){
                                homeDataActionHandler($thisButton, thisAction, postURL, postData);
                                }, 3000);

                            } else {

                            printStatusMessage(statusLine[0], statusLine[1]);
                            $thisButton.removeClass('loading');
                            $adminHome.removeClass('loading');

                            }
                        }
                    });
                };
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
                    else if (thisSubKind.length){ confirmMessage = confirmMessage.replace(/\{object\}/g, makeObjectSingular(thisSubKind)+' '+thisKind); }
                    else { confirmMessage = confirmMessage.replace(/\{object\}/g, thisKind); }
                    //console.log('postURL = ', postURL);
                    //console.log('postData = ', postData);
                    //console.log('confirmMessage = ', confirmMessage);
                    var allowButtonAction = confirm(confirmMessage);
                    if (allowButtonAction){
                        if (thisAction !== 'update'
                            && thisAction !== 'publish'){
                            var confirmMessage2 = confirmMessages['final'];
                            if (thisAction === 'publish'){ confirmMessage2 = confirmMessage2.replace(/\{action\}/g, 'PUBLISH ALL'); }
                            else { confirmMessage2 = confirmMessage2.replace(/\{action\}/g, $thisButton.text().toUpperCase()); }
                            if (thisKind === 'sql'){ confirmMessage2 = confirmMessage2.replace(/\{object\}/g, 'MISC OBJECTS'); }
                            else if (thisSubKind.length){ confirmMessage2 = confirmMessage2.replace(/\{object\}/g, (makeObjectSingular(thisSubKind)+' '+thisKind).toUpperCase()); }
                            else { confirmMessage2 = confirmMessage2.replace(/\{object\}/g, thisKind.toUpperCase()); }
                            //console.log('confirmMessage2 = ', confirmMessage2);
                            allowButtonAction = confirm(confirmMessage2);
                            }
                        if (allowButtonAction){
                            homeDataActionHandler($thisButton, thisAction, postURL, postData);
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
                    editorConfig.lineWrapping = false;
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
                publish: 'admin/scripts/publish-game-content.php',
                update: 'admin/scripts/update-game-content.php'
                };
            var confirmMessages = {
                revert: 'Are you sure you want to revert uncommitted changes to this {object}? '
                    + 'This action cannot be undone and any updates will be lost.\n'
                    + 'Continue anyway?',
                commit: 'Are you absolutely sure you want to commit the changes to this {object}? '
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                publish: 'Are you absolutely sure you want to publish the changes to this {object}? '
                    + 'This action cannot be undone and will be in the history forever.\n'
                    + 'Continue anyway? ',
                update: 'Are you sure you want to this {object} with remote changes?  '
                    + 'Updates cannot be reverted once applied.\n'
                    + 'Continue anyway? '
                }
            var editorDataActionTimeout = false;
            var editorDataActionHandler = function($thisButton, postURL, postData){
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
                                $('html, body').animate({ scrollTop: 0 }, 'fast', function(){
                                    window.location.href = window.location.href.replace(location.hash,'');
                                    //alert('Reload the window!');
                                    });
                                };
                            printStatusMessage(statusLine[0], statusLine[1], completeFunction);
                            } else if (statusLine[0] === 'pending'){
                            //alert('request is pending?B');
                            if (editorDataActionTimeout !== false){ clearTimeout(editorDataActionTimeout); }
                            printStatusMessage(statusLine[0], statusLine[1]);
                            editorDataActionTimeout = setTimeout(function(){
                                editorDataActionHandler($thisButton, postURL, postData);
                                }, 3000);
                            } else {
                            printStatusMessage(statusLine[0], statusLine[1]);
                            $thisButton.removeClass('loading');
                            thisAdminForm.removeClass('loading');
                            }
                        }
                    });
                };
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
                if (thisToken.indexOf('_groups/') !== -1){ confirmMessage = confirmMessage.replace(/\{object\}/g, 'list'); }
                else if (thisSubKind.length){ confirmMessage = confirmMessage.replace(/\{object\}/g, makeObjectSingular(thisSubKind)); }
                else { confirmMessage = confirmMessage.replace(/\{object\}/g, makeObjectSingular(thisKind)); }
                //console.log('postURL = ', postURL);
                //console.log('postData = ', postData);
                //console.log('confirmMessage = ', confirmMessage);
                if (confirm(confirmMessage)){ editorDataActionHandler($thisButton, postURL, postData); }
            });
        }

        // Check to see if there are any audio player triggers on the page
        var $audioPlayers = $('.audio-player[data-path]', thisAdminForm);
        //var $musicLinks = $('a[href*=".mp3"],a[href*=".ogg"]', $thisAdminForm);
        if ($audioPlayers.length){
            //console.log('There are ', $audioPlayers.length, 'audio players on this page');
            mmrpgAdminAudioPlayer($audioPlayers, {size:'default'});
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


    // USER EDITOR EVENTS

    // Check to make sure we're on the user editor page
    var $editUsers = $('.adminform.edit-users', thisAdmin);
    //console.log('$editUsers.length =', $editUsers.length);
    if ($editUsers.length){
        (function(){
            //console.log('we can edit users!');

            // Check to see if a permissions table is on the page
            var $permissionsTable = $('.field.permissions-table', $editUsers);
            if ($permissionsTable.length){
                //console.log('we can edit permissions!!');

                // Define a function for gracefully unchecking parent checkboxes
                var uncheckParentCheckboxes = function($thisListItem){
                    var $parentListItem = $thisListItem.closest('ul').closest('li');
                    //console.log('this list item has ', $parentListItem.length, 'parent');
                    if ($parentListItem.length){
                        var $parentCheckbox = $parentListItem.find('input[type="checkbox"]').first();
                        var parentIsChecked = $parentCheckbox.is(':checked') ? true : false;
                        if (parentIsChecked){ $parentCheckbox.prop('checked', false); }
                        uncheckParentCheckboxes($parentListItem);
                        }
                    };

                // Add click events to add the checkboxes in the table
                $('input[type="checkbox"]', $permissionsTable).bind('change', function(){
                    var $thisCheckbox = $(this);
                    var $thisListItem = $thisCheckbox.closest('li');
                    var thisIsChecked = $thisCheckbox.is(':checked') ? true : false;
                    //console.log('thisIsChecked = ', thisIsChecked);
                    var $childCheckboxes = $thisListItem.find('ul li input[type="checkbox"]');
                    //console.log('this checkbox has ', $childCheckboxes.length, 'children');
                    if ($childCheckboxes.length){
                        $childCheckboxes.each(function(){
                            var $childCheckbox = $(this);
                            var childIsChecked = $childCheckbox.is(':checked') ? true : false;
                            //if (childIsChecked !== thisIsChecked){ $childCheckbox.get(0).click(); }
                            if (childIsChecked !== thisIsChecked){ $childCheckbox.prop('checked', thisIsChecked); }
                            });
                        }
                    if (!thisIsChecked){ uncheckParentCheckboxes($thisListItem); }
                    });

                }

        })();
    }


    // ROBOT EDITOR EVENTS

    // Check to make sure we're on the robot editor page
    var $editRobots = $('.adminform.edit-robots', thisAdmin);
    //console.log('$editRobots =', $editRobots);
    if ($editRobots.length){

        // If there are any skill dropdowns on the page, bind events to them
        var $robotSkill = $('select[name="robot_skill"]', $editRobots);
        var $robotSkillParams = $('input[name="robot_skill_parameters"]', $editRobots);
        var $robotSkillParamsLabel = $robotSkillParams.parent().find('> .label');
        var $robotSkillParamsRequired = $robotSkill.closest('.panel').find('.requires-skill-params');
        if ($robotSkill.length && $robotSkillParams.length){
            var prevSkillValue = $robotSkill.val();
            $robotSkill.bind('change', function(){
                var skillValue = $('option:selected', $robotSkill).val();
                $robotSkillParamsLabel.find('.link').remove();
                if (skillValue !== prevSkillValue){
                    $robotSkillParamsRequired.addClass('hidden');
                    $robotSkillParamsRequired.find('input,textarea').attr('disabled', 'disabled');
                    }
                if (skillValue.length){
                    var skillID = $('option:selected', $robotSkill).attr('data-skill-id');
                    var skillParams = $('option:selected', $robotSkill).attr('data-skill-params');
                    if (typeof skillParams !== 'undefined' && skillParams.length){
                        var helpLink = 'admin/edit-skills/editor/skill_id='+skillID+'#functions';
                        $robotSkillParamsLabel.append('<em class="link"><a href="'+helpLink+'" target="_blank">need help?</a></em>');
                        $robotSkillParamsRequired.removeClass('hidden');
                    $robotSkillParamsRequired.find('input,textarea').removeAttr('disabled', 'disabled');
                        }
                    }
                prevSkillValue = skillValue;
                }).trigger('change');
            }

    }


    // ABILITY EDITOR EVENTS

    // Check to make sure we're on the ability editor page
    var $editAbilities = $('.adminform.edit-abilities', thisAdmin);
    var litePickerIndex = {};
    //console.log('$editAbilities =', $editAbilities);
    if ($editAbilities.length){

        // Add a toggle for the shop tab dropdown to show/hide or enable/disable sub-fields
        var $abilityShopTabSelect = $('select[name="ability_shop_tab"]', $editAbilities);
        var $abilityShopLevelInput = $('input[type="number"][name="ability_shop_level"]', $editAbilities);
        if ($abilityShopTabSelect.length && $abilityShopLevelInput.length){
            var $abilityShopTabField = $abilityShopTabSelect.closest('.field');
            var $abilityShopLevelField = $abilityShopLevelInput.closest('.field');
            var updateAbilityShopFields = function(){
                //console.log('updateAbilityShopFields()');
                var abilityShopTab = $abilityShopTabSelect.val();
                //console.log('abilityShopTab =', abilityShopTab);
                $('.label[data-shop-tab]', $abilityShopLevelField).addClass('hidden');
                $('.label[data-shop-tab="'+abilityShopTab+'"]', $abilityShopLevelField).removeClass('hidden');
                if (abilityShopTab === ''){ $abilityShopLevelInput.attr('disabled', 'disabled'); }
                else { $abilityShopLevelInput.removeAttr('disabled', 'disabled'); }
                if (abilityShopTab === 'abilities'){ $abilityShopLevelInput.attr('step', 10).attr('max', 100); }
                else if (abilityShopTab === 'weapons'){ $abilityShopLevelInput.attr('step', 3).attr('max', 99); }
                else { $abilityShopLevelInput.attr('step', 1).attr('max', 100); }
                };
            $abilityShopTabSelect.bind('change', function(){ updateAbilityShopFields(); });
            updateAbilityShopFields();
            }

        // Add either-or events to the ability price and value fields (only one should be entered)
        var $abilityPriceField = $('input[type="number"][name="ability_price"]', $editAbilities);
        var $abilityValueField = $('input[type="number"][name="ability_value"]', $editAbilities);
        if ($abilityPriceField.length && $abilityValueField.length){
            var updateAbilityPriceValueFields = function(){
                var abilityHasPrice = parseInt($abilityPriceField.val()) > 0 ? true : false;
                var abilityHasValue = parseInt($abilityValueField.val()) > 0 ? true : false;
                $abilityPriceField.removeAttr('disabled').prop('disabled', false);
                $abilityValueField.removeAttr('disabled').prop('disabled', false);
                if (abilityHasPrice){ $abilityValueField.attr('disabled', 'disabled').prop('disabled', true); }
                else if (abilityHasValue){ $abilityPriceField.attr('disabled', 'disabled').prop('disabled', true); }
                };
            $abilityPriceField.bind('change', function(){ updateAbilityPriceValueFields(); });
            $abilityValueField.bind('change', function(){ updateAbilityPriceValueFields(); });
            updateAbilityPriceValueFields();
            }

        // Add events for fields with a toggle checkbox inside, allowing them to be disabled/enabled by clicking
        var $fieldsWithToggleCheckboxes = $('.field.has_toggle .toggle.has_checkbox input[type="checkbox"]', $editAbilities).closest('.field');
        if ($fieldsWithToggleCheckboxes.length){
            $fieldsWithToggleCheckboxes.each(function(){
                var $thisToggleField = $(this);
                var $thisToggleInput = $thisToggleField.find('.toggle_input');
                var $thisToggleWrap = $thisToggleField.find('.toggle.has_checkbox');
                var $thisToggleCheckbox = $thisToggleWrap.find('input[type="checkbox"]');
                $thisToggleCheckbox.bind('change', function(){
                    var isChecked = $(this).is(':checked') ? true : false;
                    if (isChecked){
                        var defaultValue = '';
                        if ($thisToggleInput.is('[data-default-value-from]')){
                            var defaultFromName = $thisToggleInput.attr('data-default-value-from');
                            defaultValue = $('[name="'+defaultFromName+'"]', $editAbilities).val();
                        } else if ($thisToggleInput.is('[type="number"]')){
                            defaultValue = 0;
                        }
                        $thisToggleInput.val(defaultValue);
                        $thisToggleInput.removeAttr('disabled');
                        $thisToggleInput.prop('disabled', false);
                        } else {
                        $thisToggleInput.val('');
                        $thisToggleInput.attr('disabled', 'disabled');
                        $thisToggleInput.prop('disabled', true);
                        }
                    });
                });
            }

        // Add events for fields with units that have checkboxes beside them, toggling the opacity for them
        var $fieldsWithUnitCheckboxes = $('.field.has_unit .unit.has_checkbox input[type="checkbox"]', $editAbilities).closest('.field');
        if ($fieldsWithUnitCheckboxes.length){
            $fieldsWithUnitCheckboxes.each(function(){
                var $thisUnitField = $(this);
                var $thisUnitWrap = $thisUnitField.find('.unit.has_checkbox');
                var $thisUnitCheckbox = $thisUnitWrap.find('input[type="checkbox"]');
                var $thisUnitSpan = $thisUnitWrap.find('> span');
                $thisUnitCheckbox.bind('change', function(){
                    var isChecked = $(this).is(':checked') ? true : false;
                    if (isChecked){
                        $thisUnitSpan.addClass('active');
                        $thisUnitSpan.removeClass('inactive');
                        } else {
                        $thisUnitSpan.removeClass('active');
                        $thisUnitSpan.addClass('inactive');
                        }
                    });
                });
            }

    }


    // ITEM EDITOR EVENTS

    // Check to make sure we're on the item editor page
    var $editItems = $('.adminform.edit-items', thisAdmin);
    var litePickerIndex = {};
    //console.log('$editItems =', $editItems);
    if ($editItems.length){

        // Add a toggle for the shop tab dropdown to show/hide or enable/disable sub-fields
        var $itemShopTabSelect = $('select[name="item_shop_tab"]', $editItems);
        var $itemShopLevelInput = $('input[type="number"][name="item_shop_level"]', $editItems);
        if ($itemShopTabSelect.length && $itemShopLevelInput.length){
            var $itemShopTabField = $itemShopTabSelect.closest('.field');
            var $itemShopLevelField = $itemShopLevelInput.closest('.field');
            var updateItemShopFields = function(){
                //console.log('updateItemShopFields()');
                var itemShopTab = $itemShopTabSelect.val();
                //console.log('itemShopTab =', itemShopTab);
                if (itemShopTab === ''){ $itemShopLevelInput.attr('disabled', 'disabled'); }
                else { $itemShopLevelInput.removeAttr('disabled', 'disabled'); }
                };
            $itemShopTabSelect.bind('change', function(){ updateItemShopFields(); });
            updateItemShopFields();
            }

        // Add either-or events to the item price and value fields (only one should be entered)
        var $itemPriceField = $('input[type="number"][name="item_price"]', $editItems);
        var $itemValueField = $('input[type="number"][name="item_value"]', $editItems);
        if ($itemPriceField.length && $itemValueField.length){
            var updateItemPriceValueFields = function(){
                var itemHasPrice = parseInt($itemPriceField.val()) > 0 ? true : false;
                var itemHasValue = parseInt($itemValueField.val()) > 0 ? true : false;
                $itemPriceField.removeAttr('disabled').prop('disabled', false);
                $itemValueField.removeAttr('disabled').prop('disabled', false);
                if (itemHasPrice){ $itemValueField.attr('disabled', 'disabled').prop('disabled', true); }
                else if (itemHasValue){ $itemPriceField.attr('disabled', 'disabled').prop('disabled', true); }
                };
            $itemPriceField.bind('change', function(){ updateItemPriceValueFields(); });
            $itemValueField.bind('change', function(){ updateItemPriceValueFields(); });
            updateItemPriceValueFields();
            }

        // Add events for fields with units that have checkboxes beside them, toggling the opacity for them
        var $fieldsWithUnitCheckboxes = $('.field.has_unit .unit.has_checkbox input[type="checkbox"]', $editItems).closest('.field');
        if ($fieldsWithUnitCheckboxes.length){
            $fieldsWithUnitCheckboxes.each(function(){
                var $thisUnitField = $(this);
                var $thisUnitWrap = $thisUnitField.find('.unit.has_checkbox');
                var $thisUnitCheckbox = $thisUnitWrap.find('input[type="checkbox"]');
                var $thisUnitSpan = $thisUnitWrap.find('> span');
                $thisUnitCheckbox.bind('change', function(){
                    var isChecked = $(this).is(':checked') ? true : false;
                    if (isChecked){
                        $thisUnitSpan.addClass('active');
                        $thisUnitSpan.removeClass('inactive');
                        } else {
                        $thisUnitSpan.removeClass('active');
                        $thisUnitSpan.addClass('inactive');
                        }
                    });
                });
            }

    }


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
                var lastOptionsGroup = '';
                for (var i = 0; i < mmrpgAbilityTokens.length; i++){
                    var abilityToken = mmrpgAbilityTokens[i];
                    var abilityInfo = window.mmrpgAbilitiesIndex[abilityToken];
                    var abilityName = abilityInfo['ability_name'];
                    var abilityTypes = [];
                    //console.log(abilityToken+' abilityInfo[\'ability_group\'] = ', typeof abilityInfo['ability_group'], abilityInfo['ability_group'])
                    if (typeof abilityInfo['ability_group'] !== 'string'){ abilityInfo['ability_group'] = 'Undefined'; }
                    if (abilityInfo['ability_type'].length){ abilityTypes.push(upperCaseFirst(abilityInfo['ability_type'])); }
                    if (abilityTypes.length && abilityInfo['ability_type2'].length){ abilityTypes.push(upperCaseFirst(abilityInfo['ability_type2'])); }
                    abilityTypes = abilityTypes.length ? abilityTypes.join(' / ') : 'Neutral';
                    if (abilityInfo['ability_class'] == 'mecha' && robotInfo['robot_class'] != 'mecha'){ var abilityIsCompatible = false; }
                    else if (abilityInfo['ability_class'] == 'boss' && robotInfo['robot_class'] != 'boss'){ var abilityIsCompatible = false; }
                    else { var abilityIsCompatible = robotHasCompatibility(robotToken, abilityToken, robotItem); }
                    var abilityIsComplete = parseInt(abilityInfo['ability_flag_complete']) === 1 ? true : false;
                    if (!abilityIsCompatible || !abilityIsComplete){ continue; }
                    var optionsGroup = upperCaseFirst(abilityInfo['ability_class'])+' | '+(abilityInfo['ability_group'].split('/').slice(0, 2).join(' | '));
                    if (lastOptionsGroup !== optionsGroup){
                        if (lastOptionsGroup.length){ newOptions += '</optgroup>'; }
                        lastOptionsGroup = optionsGroup;
                        newOptions += '<optgroup label="'+ optionsGroup +'">';
                    }
                    newOptions += '<option value="'+ abilityToken +'"'+ (!abilityIsCompatible || !abilityIsComplete ? 'disabled="disabled"' : '') +'>';
                        newOptions += abilityName +' ('+ abilityTypes  +')';
                    newOptions += '</option>';
                    }
                if (lastOptionsGroup.length){ newOptions += '</optgroup>'; }
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
            var skillToken = typeof robotInfo['robot_skill'] !== 'undefined' && robotInfo['robot_skill'].length ? robotInfo['robot_skill'] : false;
            var skillInfo = skillToken.length && typeof window.mmrpgSkillsIndex[skillToken] !== 'undefined' ? window.mmrpgSkillsIndex[skillToken] : false;
            //console.log('robotInfo = ', robotInfo, 'abilityInfo = ', abilityInfo, 'itemInfo = ', itemInfo, 'skillToken = ', skillToken, 'skillInfo = ', skillInfo);
            if (!robotInfo || !abilityInfo){ return false; }
            if (!itemInfo){ itemToken = ''; }
            var robotCore = robotInfo['robot_core'].length ? robotInfo['robot_core'] : '';
            var robotCore2 = robotInfo['robot_core2'].length ? robotInfo['robot_core2'] : '';
            var itemCore = itemToken.length && itemToken.match(/-core$/i) ? itemToken.replace(/-core$/i, '') : '';
            if (itemCore == 'none' || itemCore == 'copy'){ itemCore = ''; }
            var skillCore = skillToken.length && skillToken.match(/-subcore$/i) ? skillToken.replace(/-subcore$/i, '') : '';
            if (skillCore == 'none' || skillCore == 'copy'){ skillCore = ''; }
            //console.log('robotCore = ', robotCore, 'robotCore2 = ', robotCore2, 'itemCore = ', itemCore, 'skillCore = ', skillCore);
            var globalAbilities = typeof window.mmrpgAbilitiesGlobal !== 'undefined' ? window.mmrpgAbilitiesGlobal : [];
            if (mmrpgAbilitiesGlobal.indexOf(abilityToken) !== -1){
                return true;
                } else if (abilityInfo['ability_type'].length || abilityInfo['ability_type2'].length){
                var allowTypes = [];
                if (robotCore.length){ allowTypes.push(robotCore); }
                if (robotCore2.length){ allowTypes.push(robotCore2); }
                if (itemCore.length){ allowTypes.push(itemCore); }
                if (skillCore.length){ allowTypes.push(skillCore); }
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

    // GROUP EDITOR EVENTS

    // Check to make sure we're on the page editor page
    var $editGroups = $('.adminform .editor.groups', thisAdmin);
    //console.log('$editGroups =', $editGroups);
    if ($editGroups.length){
        //console.log('group editor is present!');

        // Collect a reference to the parent groups list
        var $groupsList = $editGroups.find('ul.groups');
        //console.log('$groupsList.length = ', $groupsList.length);

        // Destroy existing sortable events if already bound
        if ($groupsList.is('ui-sortable')){ $groupsList.sortable('destroy'); }

        // Allow group tokens to be re-ordered and moved between parent groups
        $groupsList.sortable({
            items: 'li.child',
            cancel: 'li.child.spacer',
            containment: 'ul.groups',
            connectWidth: 'ul.groups li.group ul.children',
            appendTo: $editGroups,
            helper: 'clone',
            opacity: 0.7,
            //axis: 'y',
            //handle: '.sprite',
            start: function(event, ui) {
                //console.log('start drag');
                },
            stop: function(event, ui) {
                //console.log('stop drag');
                var $tokenRow = ui.item;
                var $newParentGroup = $tokenRow.closest('li.group[data-key]');
                var newParentKey = $newParentGroup.attr('data-key');
                var $hiddenInput = $tokenRow.find('input[type="hidden"]');
                var hiddenInputName = $hiddenInput.attr('name');
                var newHiddenInputName = hiddenInputName.replace(/\[([-_a-z0-9]+)\]\[([-_a-z0-9]+)\]\[\]$/, '['+newParentKey+'][$2][]');
                //console.log('newParentKey =', newParentKey);
                //console.log('hiddenInputName =', hiddenInputName);
                //console.log('newHiddenInputName =', newHiddenInputName);
                $hiddenInput.attr('name', newHiddenInputName);
                },
            update: function(event, ui) {
                //console.log('update drag');
                }
            });

        // Define events for the move up/down arrows for the parent groups
        var moveTimeout = false;
        var coolDownTimeout = false;
        var resetGroupStyles = function($groupList, $thisGroup, $otherGroup){
            $groupList.removeClass('shifting');
            $thisGroup.css({transform:''});
            $otherGroup.css({transform:''});
            coolDownTimeout = setTimeout(function(){
                $thisGroup.removeClass('moving');
                }, 100);
            };
        $('ul.groups li.group .move-handle', $editGroups).live('click', function(e){
            e.preventDefault();
            if (moveTimeout !== false){ return false; }
            var $thisHandle = $(this);
            var $thisGroup = $thisHandle.closest('li.group[data-key]');
            var $thisGroupList = $thisGroup.closest('ul.groups');
            var thisGroupHeight = $thisGroup.outerHeight(true);
            var thisDirection = $thisHandle.attr('data-direction');
            //console.log('move ', thisDirection);
            $thisGroupList.addClass('shifting');
            if (thisDirection === 'up'){
                var $prevGroup = $thisGroup.prev('li.group[data-key]');
                var prevGroupHeight = $prevGroup.outerHeight(true);
                //console.log('thisGroupHeight = ', thisGroupHeight, '| prevGroupHeight =', prevGroupHeight);
                $thisGroup.addClass('moving').css({transform:'translate(0,'+(-1 * prevGroupHeight)+'px)'});
                $prevGroup.css({transform:'translate(0,'+(thisGroupHeight)+'px)'});
                moveTimeout = setTimeout(function(){
                    moveTimeout = false;
                    resetGroupStyles($thisGroupList, $thisGroup, $prevGroup);
                    $thisGroup.insertBefore($prevGroup);
                    updateParentGroupDivs();
                    }, 400);
                } else if (thisDirection === 'down'){
                var $nextGroup = $thisGroup.next('li.group[data-key]');
                var nextGroupHeight = $nextGroup.outerHeight(true);
                //console.log('thisGroupHeight = ', thisGroupHeight, '| nextGroupHeight =', nextGroupHeight);
                $thisGroup.addClass('moving').css({transform:'translate(0,'+(nextGroupHeight)+'px)'});
                $nextGroup.css({transform:'translate(0,'+(-1 * thisGroupHeight)+'px)'});
                moveTimeout = setTimeout(function(){
                    moveTimeout = false;
                    resetGroupStyles($thisGroupList, $thisGroup, $nextGroup);
                    $thisGroup.insertAfter($nextGroup);
                    updateParentGroupDivs();
                    }, 400);
                }
            });

        // Define an event for adding new groups to the list
        var $addGroupButton = $editGroups.find('.button.new');
        var $templateGroupDiv = $groupsList.find('li.group.template');
        $addGroupButton.bind('click', function(e){
            e.preventDefault();
            //console.log('add new group!');
            var $otherGroups = $groupsList.find('li.group:not(.readonly)');
            var $lastOtherGroup = $otherGroups.last();
            var newGroupMarkup = $('<div>').append($templateGroupDiv.clone()).html();
            var newKey = 'obj-'+($otherGroups.length);
            var newToken = 'Group'+($otherGroups.length);
            //console.log('newKey = ', newKey, '| newToken = ', newToken);
            newGroupMarkup = newGroupMarkup.replace(/\{group-key\}/g, newKey);
            newGroupMarkup = newGroupMarkup.replace(/\{group-token\}/g, newToken);
            newGroupMarkup = newGroupMarkup.replace(/readonly="readonly"/g, '');
            newGroupMarkup = newGroupMarkup.replace(/disabled="disabled"/g, '');
            //console.log('newGroupMarkup = ', newGroupMarkup);
            var $newGroup = $(newGroupMarkup);
            $newGroup.removeClass('readonly template');
            //console.log('$newGroup = ', $newGroup);
            if ($lastOtherGroup.length){ $newGroup.insertAfter($lastOtherGroup); }
            else { $newGroup.prependTo($groupsList); }
            updateParentGroupDivs();
            });

        // Define an event for whenever groups are reordered
        var updateParentGroupDivs = function(){
            //console.log('updateParentGroupDivs()');
            var $moveableObjectGroups = $groupsList.find('li.group:not(.readonly)');
            $moveableObjectGroups.css({border:''});
            var $firstGroup = $moveableObjectGroups.first();
            var $lastGroup = $moveableObjectGroups.last();
            $groupsList.find('li.group').each(function(){
                var $thisGroup = $(this);
                var $moveHandles = $thisGroup.find('.move-handle[data-direction]');
                $moveHandles.removeClass('hidden');
                if ($thisGroup.is($firstGroup)){ $moveHandles.filter('[data-direction="up"]').addClass('hidden'); }
                else if ($thisGroup.is($lastGroup)){ $moveHandles.filter('[data-direction="down"]').addClass('hidden'); }
                });
            };

        // Automatically update parent group divs onload
        updateParentGroupDivs();

    }


    // COMMUNITY THREAD & POST EDITOR EVENTS

    // Check to make sure we're on the community editor page
    var $editCommunity = $('.adminform.edit-community', thisAdmin);
    //console.log('$editCommunity =', $editCommunity);
    if ($editCommunity.length){


        // Scope thread-specific functionality if the thread editor exists on-page
        var $editCommunityThreads = $editCommunity.filter('.edit-threads');
        //console.log('$editCommunityThreads =', $editCommunityThreads);
        if ($editCommunityThreads.length){

            // ...

        }

        // Scope post-specific functionality if the post editor exists on-page
        var $editCommunityPosts = $editCommunity.filter('.edit-posts');
        //console.log('$editCommunityPosts =', $editCommunityPosts);
        if ($editCommunityPosts.length) {

            // Get the category and thread dropdowns
            var $categoryDropdown = $editCommunityPosts.find('select[name="category_id"]');
            var $threadDropdown = $editCommunityPosts.find('select[name="thread_id"]');

            // Store the original thread_id
            $threadDropdown.data('original-thread-id', $threadDropdown.val());

            // Function to fetch and update threads
            function updateThreads() {
                //console.log('updateThreads()');
                var categoryId = $categoryDropdown.val();
                var url = 'admin/scripts/get-content.php?return=json&request=get-threads&full=true&category=' + categoryId;
                $.getJSON(url, function (data) {
                    if (data.status === 'success') {
                        //console.log('data.status === \'success\'', data);
                        var threads = data.data;
                        //console.log('threads =', threads);
                        var options = '<option value="">- select thread -</option>'; // Add the blank option
                        var threadKeys = Object.keys(threads);
                        for (var i = 0; i < threadKeys.length; i++) {
                            var thread = threads[threadKeys[i]];
                            options += '<option value="' + thread.thread_id + '">' + thread.thread_name + ' (by ' + thread.author_name + ') (' + new Date(thread.thread_date * 1000).toISOString().slice(0, 10) + ')</option>';
                        }
                        $threadDropdown.empty().append(options);

                        // Re-select the original option if it's still in the list, otherwise select the blank option
                        var originalThreadId = $threadDropdown.data('original-thread-id');
                        if ($threadDropdown.find('option[value="' + originalThreadId + '"]').length) {
                            $threadDropdown.val(originalThreadId);
                        } else {
                            $threadDropdown.val('');
                        }
                    } else {
                        console.error('Error fetching threads:', data.message);
                    }
                });
            }

            // Update threads initially and when the category changes
            //updateThreads();
            $categoryDropdown.bind('change', updateThreads);

            // Update the backup value when the user selects a new thread
            $threadDropdown.bind('change', function () {
                $threadDropdown.data('original-thread-id', $threadDropdown.val());
            });


        }

    }


    // ERROR LOG EVENTS

    // Check to make sure we're on the page editor page
    var $errorLog = $('.adminform.error-log', thisAdmin);
    //console.log('$editPages =', $editPages);
    if ($errorLog.length){
        (function(){

            // Collect references to key elements on the page
            var $logList = $('ul.log-list', $errorLog);
            var $playButton = $('.buttons .button.play', $errorLog);
            var $pauseButton = $('.buttons .button.pause', $errorLog);
            var $clearButton = $('.buttons .button.clear', $errorLog);
            var $loadImage = $('.header .loading img', $errorLog);

            // Define a function for scrolling to the last item in the list
            var scrollToLastItem = function(animateScroll, numItemsAdded){
                //console.log('scrollToLastItem(', animateScroll, ')');
                if (typeof animateScroll === 'undefined'){ animateScroll = true; }
                if (typeof numItemsAdded === 'undefined'){ numItemsAdded = 1; }
                var animateDuration = 200 * numItemsAdded;
                var scrollHeight = $logList[0].scrollHeight;
                //console.log('scrollHeight = ', scrollHeight);
                if (animateScroll){ $logList.stop().animate({scrollTop: scrollHeight+'px'}, animateDuration); }
                else { $logList.scrollTop(scrollHeight); }
                };

            // Define a function for updating the state of the watcher
            var currentWatchState = 'pause';
            var changeWatchState = function(newState){
                //console.log('changeWatchState(', newState, ')');
                if (newState === changeWatchState){ return true; }
                currentWatchState = newState;
                $errorLog.attr('data-state', currentWatchState);
                if (updateTimeout !== false){ clearTimeout(updateTimeout); }
                if (newState === 'play'){
                    $playButton.addClass('hidden');
                    $pauseButton.removeClass('hidden');
                    startUpdateTimeout();
                    } else if (newState === 'pause'){
                    $playButton.removeClass('hidden');
                    $pauseButton.addClass('hidden');
                    }
                };

            // Define a function that checks for updates to the error log
            var logUpdateBaseURL = thisRootURL+'admin/watch-error-log/get-lines/';
            var waitingForUpdates = false;
            var checkForLogUpdates = function(){
                //console.log('checkForLogUpdates()');
                var lastLine = parseInt($logList.attr('data-last-line'));
                var postURL = logUpdateBaseURL+'since='+lastLine;
                waitingForUpdates = true;
                $loadImage.css({transform:'scale(1.5, 1.5)'});
                $.post(postURL, {}, function(returnData){
                    //console.log('returnData = ', returnData.length, returnData);
                    if (returnData.length){ appendNewLinesToLog(returnData); }
                    waitingForUpdates = false;
                    $loadImage.css({transform:''});
                    });

                };

            // Define a function for appending to the error log given data
            var appendNewLinesToLog = function(newLines){
                //console.log('appendNewLinesToLog(', newLines, ')');
                var numNewLines = newLines.length;
                for (var i = 0; i < numNewLines; i++){
                    var logString = newLines[i];
                    $logList.append(logString);
                    }
                var lastLine = parseInt($('li:last-child', $logList).attr('data-line'));
                $logList.attr('data-last-line', lastLine);
                scrollToLastItem(true, numNewLines);
                };

            // Define a function for clearing all existing items in the log list
            var clearLogList = function(){
                //console.log('clearLogList()');
                $logList.empty();
                };

            // Set up an interval to check for updates every X seconds
            var timeoutSleep = 3 * 1000; // seconds
            var updateTimeout = false;
            var startUpdateTimeout = function(){
                updateTimeout = setTimeout(function(){
                    //console.log('updateTimeout');
                    if (currentWatchState === 'play'
                        && !waitingForUpdates){
                        checkForLogUpdates();
                        }
                    startUpdateTimeout();
                    }, timeoutSleep);
                };

            // Bind actions to the two state buttons
            $playButton.bind('click', function(e){ e.preventDefault(); return changeWatchState('play'); });
            $pauseButton.bind('click', function(e){ e.preventDefault(); return changeWatchState('pause'); });
            $clearButton.bind('click', function(e){ e.preventDefault(); return clearLogList(); });

            // Automatically scroll log to the bottom
            scrollToLastItem(false);

        })();
    }


});


// MMRPG ADMIN // HELPER FUNCTIONS

// Helper functions for simple yet annoying tasks
function upperCaseFirst(string){ return string[0].toUpperCase() + string.substring(1); }
function upperCaseWords(string){ return this.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); }); }
function makeObjectSingular(pluralObject){ return pluralObject.replace(/ies$/i, 'y').replace(/ses$/i, 's').replace(/s$/i, ''); }


// MMRPG ADMIN // STATUS MESSAGES

// Define a common action for printing a status message at the top of the editor
function printStatusMessage(messageStatus, messageText, onCompleteFunction){
    if (typeof onCompleteFunction !== 'function'){ onCompleteFunction = function(){}; }
    var $messagesDiv = $('.messages', thisAdmin);
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


// MMPRG ADMIN // AUDIO PLAYER

// Define a reusable audio player that we can use for testing various sound files
(function(){

    // Define the main function for looping through provided audio links and attaching players to them
    var parseAudioPlayerElements = function($audioPlayers, configOptions){
        //console.log('parseAudioPlayerElements(', $audioPlayers, configOptions, ')');

        // Define a parent container variable to help with scope
        var $parentContainer = $audioPlayers.parent();

        // Define a function for creating new audio objects and adding them to a index for caching purposes
        var audioObjectIndex = [];
        var audioConfigIndex = [];
        function newAudioObject(audioPath, backupPath, configOptions, onLoaded){

            var audioSources = [audioPath];
            if (backupPath){ audioSources.push(backupPath); }

            if (typeof configOptions !== 'object'){ configOptions = {}; }
            if (typeof configOptions.preload === 'undefined'){ configOptions.preload = false; }
            if (typeof configOptions.preloadMeta === 'undefined'){ configOptions.preloadMeta = true; }
            if (typeof configOptions.loop === 'undefined'){ configOptions.loop = true; }
            if (typeof configOptions.loopStart === 'undefined'){ configOptions.loopStart = false; }
            if (typeof configOptions.loopEnd === 'undefined'){ configOptions.loopEnd = false; }
            if (typeof configOptions.volume === 'undefined'){ configOptions.volume = 0.5; }

            if (typeof onLoaded !== 'function'){ onLoaded = false; }

            var audioObject = false;
            var audioConfig = {
                src: audioSources,
                volume: configOptions.volume,
                loop: configOptions.loop,
                preload: configOptions.preload
                };
            if (configOptions.preloadMeta === true){
                audioConfig.html5 = true;
                audioConfig.preload = 'metadata';
                }
            if (configOptions.onLoaded !== false){
                audioConfig.onload = function(){
                    onLoaded.call(audioObject);
                    };
                }
            if (configOptions.loopStart !== false
                && configOptions.loopEnd !== false){
                var milliFrame = Math.ceil(1000 / 24);
                var introStart = 0;
                var introDuration = configOptions.loopStart - (milliFrame * 10);
                var loopStart = configOptions.loopStart + (milliFrame * 2);
                var loopDuration = configOptions.loopEnd - configOptions.loopStart;
                audioConfig.loop = false;
                audioConfig.sprite = {
                    intro: [introStart, introDuration, false],
                    loop: [loopStart, loopDuration, true]
                    };
                }
            audioObject = new Howl(audioConfig);

           //console.log('created new Howl w/ configOptions:', configOptions, 'and audioConfig:', audioConfig);

            audioObjectIndex.push(audioObject);
            audioConfigIndex.push(audioConfig);
            var audioID = (audioObjectIndex.length - 1);
            return audioID;

            }

        // Define a function for getting an audio object from the list given its ID
        function getAudioObject(audioID){
            if (typeof audioObjectIndex[audioID] === 'undefined'){ return false; }
            return audioObjectIndex[audioID];
            }

        // Define a function for getting an audio object from the list given its ID
        function getAudioConfig(audioID){
            if (typeof audioConfigIndex[audioID] === 'undefined'){ return false; }
            return audioConfigIndex[audioID];
            }

        // Define a function for deleting an audio object from the list given its ID
        function deleteAudioObject(audioID){
            if (typeof audioObjectIndex[audioID] === 'undefined'){ return false; }
            audioObjectIndex[audioID] = false;
            delete audioObjectIndex[audioID];
            }

        // Define a function for deleting an audio object from the list given its ID
        function deleteAudioConfig(audioID){
            if (typeof audioConfigIndex[audioID] === 'undefined'){ return false; }
            audioConfigIndex[audioID] = false;
            delete audioConfigIndex[audioID];
            }

        // Define a variable to keep track of which audio objects are currently playing
        var audioCurrentlyPlaying = [];
        function addToCurrentlyPlaying(audioID){
            var index = audioCurrentlyPlaying.indexOf(audioID);
            if (index > -1){ return; }
            audioCurrentlyPlaying.push(audioID);
            };
        function removeFromCurrentlyPlaying(audioID){
            var index = audioCurrentlyPlaying.indexOf(audioID);
            if (index > -1){ audioCurrentlyPlaying.splice(index, 1); }
            };
        function updateCurrentlyPlaying() {
            if (!audioCurrentlyPlaying.length) { return; }
            for (var i = 0; i < audioCurrentlyPlaying.length; i++) {
                var audioID = audioCurrentlyPlaying[i];
                var audioObject = getAudioObject(audioID);
                var $audioPlayer = $audioPlayers.filter('[data-audio-id="' + audioID + '"]');
                var $timerWidget = $('.widget.timer', $audioPlayer);
                if ($timerWidget.length) {
                    var audioPosition = audioObject.seek();
                    var audioDuration = audioObject.duration();
                    // convert the position to a 00:00:00 format (mm:ss:ff) [w/ ff@24]
                    var minutes = Math.floor(audioPosition / 60);
                    var seconds = Math.floor(audioPosition - minutes * 60);
                    var frames = Math.round((audioPosition - Math.floor(audioPosition)) * 24);
                    var audioPositionText = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds + ':' + (frames < 10 ? '0' : '') + frames;
                    // convert the duration to a 00:00:00 format (mm:ss:ff)
                    var minutes = Math.floor(audioDuration / 60);
                    var seconds = Math.floor(audioDuration - minutes * 60);
                    var frames = Math.round((audioDuration - Math.floor(audioDuration)) * 24);
                    var audioDurationText = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds + ':' + (frames < 10 ? '0' : '') + frames;
                    // Update the timer widget
                    var newMarkup = '/';
                    newMarkup = '<span class="current">' + audioPositionText + '</span>' + newMarkup;
                    newMarkup = newMarkup + '<span class="total">' + audioDurationText + '</span>';
                    $timerWidget.html(newMarkup);
                }
            }
            // Loop through each audio player on the page and update it's state
            requestAnimationFrame(updateCurrentlyPlaying);
        }
        requestAnimationFrame(updateCurrentlyPlaying);



        // This function will be responsible for figuring out which button was clicked
        function audioButtonClicked(){

            // Collect references to the audio button itself and the parent player element
            var $audioButton = $(this);
            var $audioPlayer = $audioButton.closest('[data-audio-id]');

            // Collect the current state of the player and the new state from the button
            var audioKind = $audioPlayer.attr('data-audio-kind');
            var audioPath = $audioPlayer.attr('data-audio-path');
            var audioID = $audioPlayer.attr('data-audio-id');
            var audioObject = getAudioObject(audioID);
            var audioStateCurrent = $audioPlayer.attr('data-audio-state');
            var audioStateNew = $audioButton.attr('data-audio-control');
            //console.log('Audio button clicked!', { 'audioKind': audioKind, 'audioPath': audioPath, 'audioStateCurrent': audioStateCurrent, 'audioStateNew': audioStateNew });

            // If the button clicked is the same as the current state, do nothing
            if (audioStateNew === audioStateCurrent){
                if (audioStateNew === 'play'){ audioStateNew = 'pause'; }
                else if (audioStateNew === 'pause'){ audioStateNew = 'play'; }
                else if (audioStateNew === 'stop'){ return; }
                else { return; }
            }

            // Call the function responsible for updating the state and relevant elements
            updateAudioState(audioStateNew, audioID, $audioPlayer, $audioButton);

            };

        // This function will be responsible for updating the state and relevant elements
        function updateAudioState(audioStateNew, audioID){
            //console.log('updateAudioState(', audioStateNew, ',', audioID, ')');

            // Derive the $audioPlayer and $audioButton from the audioID
            var $audioPlayer = $audioPlayers.filter('[data-audio-id="' + audioID + '"]');
            var $audioButton = $audioPlayer.find('.audio-button[data-audio-control="' + audioStateNew + '"]');
            var audioObject = getAudioObject(audioID);
            var audioConfig = getAudioConfig(audioID);

            // Update the audio state of the player container to reflect the change
            $audioPlayer.attr('data-audio-state', audioStateNew);

            // If the item hasn't been loaded yet, do it now
            if (audioObject.state() === 'unloaded'){
                audioObject.on('load', function(){ updateAudioState(audioStateNew, audioID); });
                audioObject.load();
                return;
            }

            // If this is a "play" request but other audio is playing, stop it
            if (audioStateNew === 'play'
                && audioCurrentlyPlaying.length){
                //console.log('PAUSE other clips');
                //console.log('audioCurrentlyPlaying =', audioCurrentlyPlaying);
                for (var i = 0; i < audioCurrentlyPlaying.length; i++){
                    var id = audioCurrentlyPlaying[i];
                    if (id === audioID){ continue; }
                    updateAudioState('pause', id);
                    }
                }

            // Apply the play state to the audio object itself
            if (audioStateNew === 'play'){
               //console.log('PLAY the current clip');
               //console.log('audioConfig =', audioConfig);
                if (typeof audioConfig.sprite !== 'undefined'
                    && typeof audioConfig.sprite.intro !== 'undefined'
                    && typeof audioConfig.sprite.loop !== 'undefined'){
                   //console.log('we can LOOP the current clip!');
                    audioObject.once('end', function(){
                       //console.log('intro has ended, now play the loop');
                        audioObject.stop();
                        //audioObject.seek(audioConfig.sprite.loop[0] / 1000); // convert ms to seconds
                        audioObject.play('loop');
                        });
                    audioObject.play('intro');
                } else {
                    audioObject.play();
                }
                addToCurrentlyPlaying(audioID);
            }
            if (audioStateNew === 'pause'){
                //console.log('PAUSE the current clip');
                audioObject.pause();
                removeFromCurrentlyPlaying(audioID);
            }
            if (audioStateNew === 'stop'){
                //console.log('STOP the current clip');
                audioObject.stop();
                removeFromCurrentlyPlaying(audioID);
                $audioPlayer.find('.widget.timer .current').html('0:00');
            }

            // Update any currently playing audio players with new details
            updateCurrentlyPlaying();

            // Return now that we're done
            return;

            };

        // Loop through each music link trigger and add the player to the page
        $audioPlayers.each(function(){

            // Collect a reference to the audio player and collect its settings
            var $audioPlayer = $(this);

            var thisKind = $audioPlayer.attr('data-kind');
            if (typeof thisKind !== 'string' || !thisKind.length){ thisKind = 'audio'; }

            var thisPath = $audioPlayer.attr('data-path');
            var thisBackupPath = $audioPlayer.attr('data-backup-path');
            if (typeof thisPath !== 'string' || !thisPath.length){ return true; }
            if (typeof thisBackupPath !== 'string' || !thisBackupPath.length){ thisBackupPath = false; }

            var preloadMeta = true;
            if ($audioPlayer.is('.no-preload')){ preloadMeta = false; }

            var selectToWatch = $audioPlayer.attr('data-select');
            var $selectToWatch = typeof selectToWatch !== 'undefined' ? $('select[name="'+selectToWatch+'"]', $parentContainer) : false;

            var thisLoopStart = $audioPlayer.attr('data-loop-start');
            var thisLoopEnd = $audioPlayer.attr('data-loop-end');
            if (typeof thisLoopStart !== 'string' || !thisLoopStart.length){ thisLoopStart = false; }
            else { thisLoopStart = parseInt(thisLoopStart); }
            if (typeof thisLoopEnd !== 'string' || !thisLoopEnd.length){ thisLoopEnd = false; }
            else { thisLoopEnd = parseInt(thisLoopEnd); }
           //console.log('thisLoopStart =', thisLoopStart, 'thisLoopEnd =', thisLoopEnd);

            // Empty the element of any existing markup then add new attributes and insert new buttons
            $audioPlayer.empty();
            $audioPlayer.removeAttr('data-kind');
            $audioPlayer.removeAttr('data-path');
            $audioPlayer.attr('data-audio-kind', thisKind);
            $audioPlayer.attr('data-audio-path', thisPath);
            $audioPlayer.attr('data-audio-state', 'stop');
            $audioPlayer.attr('data-audio-id', '');
            $audioPlayer.append('<span class="button play" data-audio-control="play"><i class="fa fas fa-play-circle"></i></span>');
            $audioPlayer.append('<span class="button pause" data-audio-control="pause"><i class="fa fas fa-pause-circle"></i></span>');
            $audioPlayer.append('<span class="button stop" data-audio-control="stop"><i class="fa fas fa-stop-circle"></i></span>');
            $audioPlayer.append('<span class="widget timer"><span class="current">'+(preloadMeta ? '00:00:00' : '')+'</span>/<span class="total">'+(preloadMeta ? '00:00:00' : '')+'</span></span>');
            $audioPlayer.append('<span class="widget state"><i class="fa fas fa-music"></i></span>');

            // Define a little onComplete function for when an audio file finishes loading
            var onComplete = function(audioObject){
                var audioObject = this;
                var audioPosition = audioObject.seek();
                var audioDuration = audioObject.duration();
                var minutes = Math.floor(audioDuration / 60);
                var seconds = Math.floor(audioDuration - minutes * 60);
                var audioDurationText = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                $audioPlayer.find('.widget.timer .total').html(audioDurationText);
                };

            // Create the new audio object and collect the real ID for it, updating the player with it
            var audioID = newAudioObject(thisPath, thisBackupPath, {
                preloadMeta: preloadMeta,
                loopStart: thisLoopStart,
                loopEnd: thisLoopEnd
                }, onComplete);
            $audioPlayer.attr('data-audio-id', audioID);

            // Define the click event for the audio controls within this player
            $('.button[data-audio-control]', $audioPlayer).bind('click', function(e){
                e.preventDefault();
                audioButtonClicked.call(this);
                });

            // If this player has a select dropdown to watch, bind the event
            if ($selectToWatch  && $selectToWatch.length){
                //console.log('$selectToWatch =', $selectToWatch);
                var selectPathBase = $audioPlayer.attr('data-select-path-base');
                var selectPathSources = $audioPlayer.attr('data-select-path-sources');
                selectPathSources = typeof selectPathSources === 'string' ? selectPathSources.split(',') : [];
                //console.log('selectPathBase =', selectPathBase);
                //console.log('selectPathSources =', selectPathSources);
                $selectToWatch.bind('change', function(){

                    // Collect reference to new option value and generate new paths
                    //console.log('select has changed values!');
                    var $optionSelected = $('option:selected', this);
                    var optionValue = $optionSelected.val();
                    var newPath = selectPathBase+optionValue+selectPathSources[0];
                    var newBackupPath = selectPathBase+optionValue+selectPathSources[1];
                    newPath += (newPath.indexOf('?') === -1 ? '?' : '&') + Date.now();
                    newBackupPath += (newBackupPath.indexOf('?') === -1 ? '?' : '&') + Date.now();
                    //console.log('optionValue =', optionValue);
                    //console.log('newPath =', newPath);
                    //console.log('newBackupPath =', newBackupPath);

                    // Collect reference to the old audio and stop it if currently playing
                    var oldAudioID = $audioPlayer.attr('data-audio-id');
                    var oldAudioObject = getAudioObject(oldAudioID);
                    updateAudioState('stop', oldAudioID);
                    $audioPlayer.find('.widget.timer .current').html('0:00');
                    $audioPlayer.find('.widget.timer .total').html('0:00');
                    deleteAudioObject(oldAudioID);
                    deleteAudioConfig(oldAudioID);

                    // Collect a new audio ID using the new settings and then update the player
                    var newAudioID = newAudioObject(newPath, newBackupPath, {
                        preloadMeta: true,
                        loopStart: thisLoopStart,
                        loopEnd: thisLoopEnd
                        }, onComplete);
                    $audioPlayer.attr('data-audio-id', newAudioID);


                    });
                }

            });

        };

    // Expose the audio player function to the window
    window.mmrpgAdminAudioPlayer = function($audioPlayers, configOptions){
        if (!$audioPlayers.length){ return; }
        if (typeof configOptions !== 'object'){ configOptions = {}; }
        return parseAudioPlayerElements($audioPlayers, configOptions);
        };


})();
