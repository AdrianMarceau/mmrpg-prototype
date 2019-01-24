
/* -- BACK-END JAVASCRIPT (ADMIN) -- */

// Define global object variables
var thisAdmin = false;
var thisAdminForm = false;
var thisAdminSearch = false;
var thisAdminResults = false;
var thisAdminEditor = false;

// Wait for document ready before delegating events
$(document).ready(function(){

    // Collect references to key objects
    thisAdmin = $('#admin');
    thisAdminForm = $('.adminform', thisAdmin);
    thisAdminSearch = $('.adminform > .search', thisAdmin);
    thisAdminResults = $('.adminform > .results', thisAdmin);
    thisAdminEditor = $('.adminform > .editor', thisAdmin);

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
            console.log('we can delete '+objName+'!');
            $.post(postURL, function(data){
                // Delete successful, let's reload the page
                window.location.href = window.location.href;
                //console.log(data);
                return true;
                });

            } else {

            console.log('delete request denied!');

            }


        });

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

            });
        }

});