
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
                    if ($uploadLink.hasClass('disabled')){ return false; }
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
                            };
                        $listItem.addClass('pending');
                        setupAjax('upload', $uploadLink.is('[data-file-hash]') ? $uploadLink.attr('data-file-hash') : '');
                        $uploadInput.clone().appendTo($adminAjaxForm);
                        $adminAjaxForm.append('<input type="text" name="file_kind" value="'+autoFileKind+'" />');
                        $adminAjaxForm.append('<input type="text" name="file_width" value="'+autoFileWidth+'" />');
                        $adminAjaxForm.append('<input type="text" name="file_height" value="'+autoFileHeight+'" />');
                        $adminAjaxForm.submit();
                        return true;
                        } else {
                        return false;
                        }
                    };
                var deleteAction = function(){
                    if ($deleteLink.hasClass('disabled')){ return false; }
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
                            };
                        $listItem.addClass('pending');
                        setupAjax('delete', $deleteLink.is('[data-file-hash]') ? $deleteLink.attr('data-file-hash') : '');
                        $adminAjaxForm.submit();
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

});