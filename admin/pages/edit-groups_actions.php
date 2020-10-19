<?

// Ensure object group vars for this page are set
if (!isset($object_group_kind)){ exit('$object_group_kind was undefined!'); }
if (!isset($object_group_class)){ exit('$object_group_class was undefined!'); }
if (!isset($object_group_editor_url)){ exit('$object_group_editor_url was undefined!'); }
if (!isset($object_group_editor_name)){ exit('$object_group_editor_name was undefined!'); }

// Update the title for this group editor page
$this_page_tabtitle = $object_group_editor_name.' | '.$this_page_tabtitle;

// Define a function for exiting a robot edit action
function exit_group_edit_action(){
    global $object_group_editor_url;
    redirect_form_action($object_group_editor_url);
}

// If a form action was provided, attempt to process it now
$form_action = !empty($_POST['formaction']) ? $_POST['formaction'] : false;
$form_success = true;
if ($form_action === 'edit_groups'){

    // DEBUG
    //$form_messages[] = array('alert', 'Form was submit');
    //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');

    // Collect the object groups as submitted
    $raw_object_groups = !empty($_POST['object_groups']) ? $_POST['object_groups'] : array();

    // If the groups were not provided, produce an error
    if (empty($raw_object_groups)){ $form_messages[] = array('error', 'Groups array was not provided or was empty'); $form_success = false; }

    // Exit if there were any errors
    if (!$form_success){ exit_group_edit_action(); }

    // We made it this far so let's construct the new groups array
    $new_group_order = 0;
    $new_object_groups = array();
    foreach ($raw_object_groups AS $raw_key => $raw_info){
        if (empty($raw_info['group_token'])){ $form_messages[] = array('warning', 'A group without a name was removed'); continue; }
        if (empty($raw_info['group_child_tokens'])){ continue; }
        $new_group_token = $raw_info['group_token'];
        $new_group_info = array();
        $new_group_info['group_class'] = $object_group_class;
        $new_group_info['group_token'] = $new_group_token;
        $new_group_info['group_order'] = $new_group_order++;
        $new_group_info['group_child_tokens'] = array_unique(array_values($raw_info['group_child_tokens']));
        $new_object_groups[$new_group_token] = $new_group_info;
    }
    //$form_messages[] = array('alert', '<pre>$new_object_groups = '.print_r($new_object_groups, true).'</pre>');

    // Ensure new group data was actually generated
    if (empty($new_object_groups)){ $form_messages[] = array('error', 'New group data could not be parsed and was not saved'); $form_success = false; }

    // Exit if there were any errors
    if (!$form_success){ exit_group_edit_action(); }

    // Nest the new groups array so it matches the standard
    $new_object_groups = array($object_group_class => $new_object_groups);
    //$form_messages[] = array('alert', '<pre>$new_object_groups = '.print_r($new_object_groups, true).'</pre>');

    // Save the new object group data to the database and to the json file
    cms_admin::save_object_groups_to_database($new_object_groups, $object_group_kind, $object_group_class);
    cms_admin::save_object_groups_to_json($new_object_groups, $object_group_kind, $object_group_class);

    // Redirect with success message now that we're done
    $form_messages[] = array('success', ucfirst(strtolower($object_group_editor_name)).' updated successfully');
    exit_group_edit_action();

}

?>