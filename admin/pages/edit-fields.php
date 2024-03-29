<? ob_start(); ?>

    <?

    // Pre-check access permissions before continuing
    if (!rpg_user::current_user_has_permission('edit-fields')){
        $form_messages[] = array('error', 'You do not have permission to edit fields!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect indexes for required object types
    $mmrpg_types_index = cms_admin::get_types_index();
    $mmrpg_robots_index = cms_admin::get_robots_index();
    $mmrpg_music_index = cms_admin::get_music_index();
    $mmrpg_contributors_index = cms_admin::get_contributors_index('field');
    $mmrpg_sources_index = rpg_game::get_source_index();

    // Generate a reference index of robot classes for options
    $mmrpg_robots_index_byclass = array();
    if (!empty($mmrpg_robots_index)){
        foreach ($mmrpg_robots_index AS $token => $data){
            if (!isset($mmrpg_robots_index_byclass[$data['robot_class']])){ $mmrpg_robots_index_byclass[$data['robot_class']] = array(); }
            $mmrpg_robots_index_byclass[$data['robot_class']][] = $token;
        }
    }

    // Collect an index of contributors and admins that have made sprites
    $mmrpg_contributors_index = cms_admin::get_contributors_index('field');

    // Collect an index of file changes and updates via git
    $mmrpg_git_file_arrays = cms_admin::object_editor_get_git_file_arrays(MMRPG_CONFIG_FIELDS_CONTENT_PATH, array(
        'table' => 'mmrpg_index_fields',
        'token' => 'field_token'
        ));

    // Explode the list of git files into separate array vars
    extract($mmrpg_git_file_arrays);


    /* -- Generate Select Option Markup -- */

    // Pre-generate a list of all fields so we can re-use it over and over
    $music_options_count = 0;
    $music_options_group = '';
    $music_options_markup = array();
    $music_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_music_index AS $music_path => $music_info){
        if (!rpg_game::sound_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path.'/')){ continue; }
        $music_group = $music_info['music_game'];
        if ($music_group != $music_options_group){
            if (!empty($music_options_group)){ $music_options_markup[] = '</optgroup>'; }
            $music_options_group = $music_group;
            $music_group_name = rpg_game::get_source_name($music_group, false);
            $music_options_markup[] = '<optgroup label="'.$music_group_name.' Music">';
        }
        $music_name = $music_info['music_name'];
        $legacy_music_path = $music_info['legacy_music_path'];
        $music_options_markup[] = '<option value="'.$music_path.'" data-legacy-value="'.$legacy_music_path.'">'.$music_name.'</option>';
        $music_options_count++;
    }
    if (!empty($music_options_group)){ $music_options_markup[] = '</optgroup>'; }
    $music_options_markup = implode(PHP_EOL, $music_options_markup);

    // Pre-generate a list of all sources so we can re-use it over and over
    $last_option_group = false;
    $source_options_markup = array();
    $source_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_sources_index AS $source_token => $source_info){
        $class_group = ucfirst($source_info['source_series']).' Series';
        if ($last_option_group !== $class_group){
            if (!empty($last_option_group)){ $source_options_markup[] = '</optgroup>'; }
            $last_option_group = $class_group;
            $source_options_markup[] = '<optgroup label="'.$class_group.'">';
        }
        $source_name = !empty($source_info['source_name']) ? $source_info['source_name'] : $source_info['source_name_aka'];
        $source_systems = !empty($source_info['source_systems']) ? $source_info['source_systems'] : 'Unknown';
        $source_options_markup[] = '<option value="'.$source_token.'">'.$source_name.' ('.$source_systems.')</option>';
    }
    if (!empty($last_option_group)){ $source_options_markup[] = '</optgroup>'; }
    $source_options_count = count($source_options_markup);
    $source_options_markup = implode(PHP_EOL, $source_options_markup);


    /* -- Page Script/Style Dependencies  -- */

    // Require codemirror scripts and styles for this page
    $admin_include_common_styles[] = 'codemirror';
    $admin_include_common_scripts[] = 'codemirror';

    // Require codemirror scripts and styles for this page
    $admin_include_common_styles[] = 'howler';
    $admin_include_common_scripts[] = 'howler';


    /* -- Form Setup Actions -- */

    // Define a function for exiting a field edit action
    function exit_field_edit_action($field_id = false){
        if ($field_id !== false){ $location = 'admin/edit-fields/editor/field_id='.$field_id; }
        else { $location = 'admin/edit-fields/search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Fields | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['field_id'])){

        // Collect form data for processing
        $delete_data['field_id'] = !empty($_GET['field_id']) && is_numeric($_GET['field_id']) ? trim($_GET['field_id']) : '';

        // Let's delete all of this field's data from the database
        if (!empty($delete_data['field_id'])){
            $delete_data['field_token'] = $db->get_value("SELECT field_token FROM mmrpg_index_fields WHERE field_id = {$delete_data['field_id']};", 'field_token');
            if (!empty($delete_data['field_token'])){ $files_deleted = cms_admin::object_editor_delete_json_data_file('field', $delete_data['field_token'], true); }
            $db->delete('mmrpg_index_fields', array('field_id' => $delete_data['field_id'], 'field_flag_protected' => 0));
            $form_messages[] = array('success', 'The requested field has been deleted from the database'.(!empty($files_deleted) ? ' and file system' : ''));
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested field does not exist in the database');
            exit_form_action('error');
        }

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 50;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'field_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['field_id'] = !empty($_GET['field_id']) && is_numeric($_GET['field_id']) ? trim($_GET['field_id']) : '';
        $search_data['field_name'] = !empty($_GET['field_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['field_name']) ? trim(strtolower($_GET['field_name'])) : '';
        $search_data['field_type'] = !empty($_GET['field_type']) && preg_match('/[-_0-9a-z]+/i', $_GET['field_type']) ? trim(strtolower($_GET['field_type'])) : '';
        $search_data['field_class'] = !empty($_GET['field_class']) && preg_match('/[-_0-9a-z]+/i', $_GET['field_class']) ? trim(strtolower($_GET['field_class'])) : '';
        $search_data['field_flavour'] = !empty($_GET['field_flavour']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['field_flavour']) ? trim($_GET['field_flavour']) : '';
        $search_data['field_game'] = !empty($_GET['field_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['field_game']) ? trim(strtoupper($_GET['field_game'])) : '';
        $search_data['field_flag_hidden'] = isset($_GET['field_flag_hidden']) && $_GET['field_flag_hidden'] !== '' ? (!empty($_GET['field_flag_hidden']) ? 1 : 0) : '';
        $search_data['field_flag_complete'] = isset($_GET['field_flag_complete']) && $_GET['field_flag_complete'] !== '' ? (!empty($_GET['field_flag_complete']) ? 1 : 0) : '';
        $search_data['field_flag_published'] = isset($_GET['field_flag_published']) && $_GET['field_flag_published'] !== '' ? (!empty($_GET['field_flag_published']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_append_git_statuses($search_data, 'field');
        cms_admin::object_index_search_data_clean_query_values($search_data, 'field', $backup_search_data);

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_field_fields = rpg_field::get_index_fields(true, 'field');
        $search_query = "SELECT
            {$temp_field_fields}
            FROM mmrpg_index_fields AS field
            WHERE 1=1
            AND field_token <> 'field'
            ";

        // If the field ID was provided, we can search by exact match
        if (!empty($search_data['field_id'])){
            $field_id = $search_data['field_id'];
            $search_query .= "AND field_id = {$field_id} ";
            $search_results_limit = false;
        }

        // Else if the field name was provided, we can use wildcards
        if (!empty($search_data['field_name'])){
            $field_name = $search_data['field_name'];
            $field_name = str_replace(array(' ', '*', '%'), '%', $field_name);
            $field_name = preg_replace('/%+/', '%', $field_name);
            $field_name = '%'.$field_name.'%';
            $search_query .= "AND (field_name LIKE '{$field_name}' OR field_token LIKE '{$field_name}') ";
            $search_results_limit = false;
        }

        // Else if the field type was provided, we can use wildcards
        if (!empty($search_data['field_type'])){
            $field_type = $search_data['field_type'];
            if ($field_type !== 'none'){ $search_query .= "AND (field_type LIKE '{$field_type}' OR field_type2 LIKE '{$field_type}') "; }
            else { $search_query .= "AND field_type = '' "; }
            $search_results_limit = false;
        }

        // If the field class was provided
        if (!empty($search_data['field_class'])){
            $search_query .= "AND field_class = '{$search_data['field_class']}' ";
            $search_results_limit = false;
        }

        // Else if the field flavour was provided, we can use wildcards
        if (!empty($search_data['field_flavour'])){
            $field_flavour = $search_data['field_flavour'];
            $field_flavour = str_replace(array(' ', '*', '%'), '%', $field_flavour);
            $field_flavour = preg_replace('/%+/', '%', $field_flavour);
            $field_flavour = '%'.$field_flavour.'%';
            $search_query .= "AND (
                field_description LIKE '{$field_flavour}'
                OR field_description2 LIKE '{$field_flavour}'
                OR field_quotes_start LIKE '{$field_flavour}'
                OR field_quotes_taunt LIKE '{$field_flavour}'
                OR field_quotes_victory LIKE '{$field_flavour}'
                OR field_quotes_defeat LIKE '{$field_flavour}'
                ) ";
            $search_results_limit = false;
        }

        // If the field game was provided
        if (!empty($search_data['field_game'])){
            $search_query .= "AND field_game = '{$search_data['field_game']}' ";
            $search_results_limit = false;
        }

        // If the field hidden flag was provided
        if ($search_data['field_flag_hidden'] !== ''){
            $search_query .= "AND field_flag_hidden = {$search_data['field_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the field complete flag was provided
        if ($search_data['field_flag_complete'] !== ''){
            $search_query .= "AND field_flag_complete = {$search_data['field_flag_complete']} ";
            $search_results_limit = false;
        }

        // If the field published flag was provided
        if ($search_data['field_flag_published'] !== ''){
            $search_query .= "AND field_flag_published = {$search_data['field_flag_published']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "field_name ASC";
        $order_by[] = "FIELD(field_class, 'mecha', 'master', 'boss')";
        $order_by[] = "field_order ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;
        cms_admin::object_index_search_results_filter_git_statuses($search_results, $search_results_count, $search_data, 'field', $mmrpg_git_file_arrays);
        cms_admin::object_index_search_data_restore_backup_data($search_data, 'field', $backup_search_data);

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(field_id) AS total FROM mmrpg_index_fields WHERE 1=1 AND field_token <> 'field';", 'total');

    }

    // If we're in editor mode, we should collect field info from database
    $field_data = array();
    $field_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['field_id'])){

        // Collect form data for processing
        $editor_data['field_id'] = !empty($_GET['field_id']) && is_numeric($_GET['field_id']) ? trim($_GET['field_id']) : '';

        /* -- Collect Field Data -- */

        // Collect field details from the database
        $temp_field_fields = rpg_field::get_index_fields(true);
        if (!empty($editor_data['field_id'])){
            $field_data = $db->get_array("SELECT {$temp_field_fields} FROM mmrpg_index_fields WHERE field_id = {$editor_data['field_id']};");
        } else {

            // Generate temp data structure for the new challenge
            $field_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $field_data = array(
                'field_id' => 0,
                'field_token' => '',
                'field_name' => '',
                'field_class' => 'field',
                'field_subclass' => 'master',
                'field_type' => '',
                'field_flag_hidden' => 0,
                'field_flag_complete' => 0,
                'field_flag_published' => 0,
                'field_flag_unlockable' => 0,
                'field_flag_protected' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $field_data[$f] = $v;
                }
            }

        }

        // If field data could not be found, produce error and exit
        if (empty($field_data)){ exit_field_edit_action(); }

        // Collect the field's name(s) for display
        $field_name_display = $field_data['field_name'];
        if ($field_data_is_new){ $this_page_tabtitle = 'New Field | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $field_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this field, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-fields'){

            // COLLECT form data from the request and parse out simple rules

            $old_field_token = !empty($_POST['old_field_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_field_token']) ? trim(strtolower($_POST['old_field_token'])) : '';

            $form_data['field_id'] = !empty($_POST['field_id']) && is_numeric($_POST['field_id']) ? trim($_POST['field_id']) : 0;
            $form_data['field_token'] = !empty($_POST['field_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_token']) ? trim(strtolower($_POST['field_token'])) : '';
            $form_data['field_name'] = !empty($_POST['field_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['field_name']) ? trim($_POST['field_name']) : '';
            $form_data['field_class'] = !empty($_POST['field_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_class']) ? trim(strtolower($_POST['field_class'])) : '';
            $form_data['field_type'] = !empty($_POST['field_type']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_type']) ? trim(strtolower($_POST['field_type'])) : '';

            $form_data['field_game'] = !empty($_POST['field_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_game']) ? trim($_POST['field_game']) : '';

            $form_data['field_master'] = !empty($_POST['field_master']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_master']) ? trim(strtolower($_POST['field_master'])) : '';
            $form_data['field_master2'] = !empty($_POST['field_master2']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_master2']) ? trim(strtolower($_POST['field_master2'])) : '';

            $form_data['field_mechas'] = !empty($_POST['field_mechas']) && is_array($_POST['field_mechas']) ? array_values(array_unique(array_filter($_POST['field_mechas']))) : array();
            $form_data['field_multipliers'] = !empty($_POST['field_multipliers']) && is_array($_POST['field_multipliers']) ? array_values(array_filter($_POST['field_multipliers'])) : array();

            $form_data['field_description'] = !empty($_POST['field_description']) && preg_match('/^[-_0-9a-z\.\*\s\']+$/i', $_POST['field_description']) ? trim($_POST['field_description']) : '';
            $form_data['field_description2'] = !empty($_POST['field_description2']) ? trim(strip_tags($_POST['field_description2'])) : '';

            $form_data['field_music'] = !empty($_POST['field_music']) && preg_match('/^[-_0-9a-z\/]+$/i', $_POST['field_music']) ? trim(strtolower($_POST['field_music'])) : '';

            $form_data['field_background'] = $form_data['field_token']; //!empty($_POST['field_background']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_background']) ? trim(strtolower($_POST['field_background'])) : '';
            $form_data['field_foreground'] = $form_data['field_token']; //!empty($_POST['field_foreground']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_foreground']) ? trim(strtolower($_POST['field_foreground'])) : '';
            $form_data['field_image_editor'] = !empty($_POST['field_image_editor']) && is_numeric($_POST['field_image_editor']) ? (int)(trim($_POST['field_image_editor'])) : 0;
            $form_data['field_image_editor2'] = !empty($_POST['field_image_editor2']) && is_numeric($_POST['field_image_editor2']) ? (int)(trim($_POST['field_image_editor2'])) : 0;

            $form_data['field_background_attachments'] = !empty($_POST['field_background_attachments']) && is_array($_POST['field_background_attachments']) ? array_values(array_filter($_POST['field_background_attachments'])) : array();
            $form_data['field_foreground_attachments'] = !empty($_POST['field_foreground_attachments']) && is_array($_POST['field_foreground_attachments']) ? array_values(array_filter($_POST['field_foreground_attachments'])) : array();

            $form_data['field_flag_published'] = isset($_POST['field_flag_published']) && is_numeric($_POST['field_flag_published']) ? (int)(trim($_POST['field_flag_published'])) : 0;
            $form_data['field_flag_complete'] = isset($_POST['field_flag_complete']) && is_numeric($_POST['field_flag_complete']) ? (int)(trim($_POST['field_flag_complete'])) : 0;
            $form_data['field_flag_hidden'] = isset($_POST['field_flag_hidden']) && is_numeric($_POST['field_flag_hidden']) ? (int)(trim($_POST['field_flag_hidden'])) : 0;

            $form_data['field_functions_markup'] = !empty($_POST['field_functions_markup']) ? trim($_POST['field_functions_markup']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'field_image_alts\']  = '.print_r($_POST['field_image_alts'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'field_image_alts_new\']  = '.print_r($_POST['field_image_alts_new'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$field_data = '.print_r($field_data, true).'</pre>');

            // If this is a NEW field, auto-generate the token when not provided
            if ($field_data_is_new
                && empty($form_data['field_token'])
                && !empty($form_data['field_name'])){
                $auto_token = strtolower($form_data['field_name']);
                $auto_token = preg_replace('/\s+/', '-', $auto_token);
                $auto_token = preg_replace('/[^-a-z0-9]+/i', '', $auto_token);
                $form_data['field_token'] = $auto_token;
            }

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$field_data_is_new && empty($form_data['field_id'])){ $form_messages[] = array('error', 'Field ID was not provided'); $form_success = false; }
            if (empty($form_data['field_token']) || (!$field_data_is_new && empty($old_field_token))){ $form_messages[] = array('error', 'Field Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['field_name'])){ $form_messages[] = array('error', 'Field Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['field_class'])){ $form_messages[] = array('error', 'Field Kind was not provided or was invalid'); $form_success = false; }
            if ($form_data['field_class'] === 'master' && empty($form_data['field_master'])){ $form_messages[] = array('error', 'Field Master was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['field_type'])){ $form_messages[] = array('warning', 'Field Type was not provided or were invalid'); $form_success = false; }
            if (!$form_success){ exit_field_edit_action($form_data['field_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$field_data_is_new && empty($form_data['field_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            // Only parse the following fields if NOT new object data
            if (!$field_data_is_new){

                if (isset($form_data['field_master2'])){ $form_data['field_master2'] = !empty($form_data['field_master2']) ? json_encode(array($form_data['field_master2'])) : ''; }

                if (isset($form_data['field_mechas'])){ $form_data['field_mechas'] = !empty($form_data['field_mechas']) ? json_encode($form_data['field_mechas']) : ''; }

                if (isset($form_data['field_multipliers'])){
                    $new_multipliers = array();
                    if (!empty($form_data['field_multipliers'])){
                        foreach ($form_data['field_multipliers'] AS $key => $multiplier){
                            if (empty($multiplier['token']) || empty($multiplier['value'])){ continue; }
                            $new_multipliers[$multiplier['token']] = $multiplier['value'];
                        }
                    }
                    $form_data['field_multipliers'] = !empty($new_multipliers) ? json_encode($new_multipliers, JSON_NUMERIC_CHECK) : '';
                }

                $attachment_kinds = array('background', 'foreground');
                $new_attachment_counters = array();
                foreach ($attachment_kinds AS $kind){
                    $field_key = 'field_'.$kind.'_attachments';
                    if (isset($form_data[$field_key])){
                        $new_attachments = array();
                        if (!empty($form_data[$field_key])){
                            foreach ($form_data[$field_key] AS $key => $attachment){
                            if (empty($attachment['class']) || empty($attachment['token']) || empty($attachment['direction'])){ continue; }
                            $new_attachment = array();
                            $new_attachment['class'] = $attachment['class'];
                            $new_attachment['size'] = (int)($attachment['size']);
                            $new_attachment['offset_x'] = !empty($attachment['offset_x']) ? (int)($attachment['offset_x']) : 0;
                            $new_attachment['offset_y'] = !empty($attachment['offset_y']) ? (int)($attachment['offset_y']) : 0;
                            $new_attachment[$attachment['class'].'_token'] = $attachment['token'];
                            $new_attachment[$attachment['class'].'_frame'] = !empty($attachment['frame']) ? explode(',', str_replace(' ', '', $attachment['frame'])) : array(0);
                            foreach ($new_attachment[$attachment['class'].'_frame'] AS $k => $f){ $new_attachment[$attachment['class'].'_frame'][$k] = (int)($f); }
                            $new_attachment[$attachment['class'].'_direction'] = $attachment['direction'];
                            if (!isset($new_attachment_counters[$attachment['class']])){ $new_attachment_counters[$attachment['class']] = 0; }
                            $new_attachment_counters[$attachment['class']] += 1;
                            if ($attachment['class'] === 'robot'){ $new_attachment_key = $mmrpg_robots_index[$attachment['token']]['robot_class']; }
                            else { $new_attachment_key = $attachment['class']; }
                            $new_attachment_key .= '-'.str_pad($new_attachment_counters[$attachment['class']], 2, '0', STR_PAD_LEFT);
                            $new_attachments[$new_attachment_key] = $new_attachment;
                            }
                        }
                    }
                    //$form_data[$field_key] = $new_attachments;
                    $form_data[$field_key] = !empty($new_attachments) ? json_encode($new_attachments, JSON_NUMERIC_CHECK) : '';
                }

                if (isset($form_data['field_music'])){
                    if (!empty($form_data['field_music'])){
                        if (strstr($form_data['field_music'], '/')){
                            $music_path = $form_data['field_music'];
                            $music_data = $mmrpg_music_index[$music_path];
                            $form_data['field_music_name'] = $music_data['music_name'];
                            $form_data['field_music_link'] = json_encode($music_data['music_link']);
                        } else {
                            // legacy format, do not update in db
                            unset($form_data['field_music']);
                            unset($form_data['field_music_name']);
                            unset($form_data['field_music_link']);
                        }
                    } else {
                        $form_data['field_music'] = '';
                        $form_data['field_music_name'] = '';
                        $form_data['field_music_link'] = '';
                    }
                }

                // Ensure the functions code is VALID PHP SYNTAX and save, otherwise do not save but allow user to fix it
                if (empty($form_data['field_functions_markup'])){
                    // Functions code is EMPTY and will be ignored
                    $form_messages[] = array('warning', 'Field functions code was empty and was not saved (reverted to original)');
                } elseif (!cms_admin::is_valid_php_syntax($form_data['field_functions_markup'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', 'Field functions code was invalid PHP syntax and was not saved (please fix and try again)');
                    $_SESSION['field_functions_markup'][$field_data['field_id']] = $form_data['field_functions_markup'];
                } else {
                    // Functions code is OKAY and can be saved
                    $field_functions_path = MMRPG_CONFIG_FIELDS_CONTENT_PATH.$field_data['field_token'].'/functions.php';
                    $old_field_functions_markup = file_exists($field_functions_path) ? normalize_file_markup(file_get_contents($field_functions_path)) : '';
                    $new_field_functions_markup = normalize_file_markup($form_data['field_functions_markup']);
                    if (empty($old_field_functions_markup) || $new_field_functions_markup !== $old_field_functions_markup){
                        $f = fopen($field_functions_path, 'w');
                        fwrite($f, $new_field_functions_markup);
                        fclose($f);
                        $form_messages[] = array('alert', 'Field functions file was '.(!empty($old_field_functions_markup) ? 'updated' : 'created'));
                    }
                }

            }
            // Otherwise, if NEW data, pre-populate certain fields
            else {

                $form_data['field_game'] = 'MMRPG';

                $temp_json_fields = rpg_field::get_json_index_fields();
                foreach ($temp_json_fields AS $field){ $form_data[$field] = !empty($form_data[$field]) ? json_encode($form_data[$field], JSON_NUMERIC_CHECK) : ''; }

            }

            // Regardless, unset the markup variable so it's not save to the database
            unset($form_data['field_functions_markup']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$field_data = '.print_r($field_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            /* foreach ($form_data AS $key => $value1){
                $value2 = $field_data[$key];
                if ($value1 === '[]'){ $value1 = ''; }
                if ($value2 === '[]'){ $value2 = ''; }
                if ($value1 != $value2){ $form_messages[] = array('error', '<pre>'.
                    '$form_data['.$key.'] != $field_data['.$key.']'.PHP_EOL.
                    $value1.PHP_EOL.
                    $value2.PHP_EOL.
                    '</pre>'); }
            } */
            /* foreach ($field_data AS $key => $value){
                if (!empty($value) && !isset($form_data[$key])){
                    $form_messages[] = array('warning', '<pre>$form_data['.$key.'] not provided</pre>');
                }
            } */
            //exit_field_edit_action($form_data['field_id']);

            // Make a copy of the update data sans the field ID
            $update_data = $form_data;
            unset($update_data['field_id']);

            // If this is a new field we insert, otherwise we update the existing
            if ($field_data_is_new){

                // Update the main database index with changes to this field's data
                $update_data['field_flag_protected'] = 0;
                $insert_results = $db->insert('mmrpg_index_fields', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', 'Field data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', 'Field data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_field_id = $db->get_value("SELECT MAX(field_id) AS max FROM mmrpg_index_fields;", 'max');
                    $form_data['field_id'] = $new_field_id;
                }

            } else {

                // Update the main database index with changes to this field's data
                $update_results = $db->update('mmrpg_index_fields', $update_data, array('field_id' => $form_data['field_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', 'Field data was updated successfully!'); }
                else { $form_messages[] = array('error', 'Field data could not be updated...'); }

            }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($field_data_is_new){ $field_data['field_id'] = $new_field_id; }
                cms_admin::object_editor_update_json_data_file('field', array_merge($field_data, $update_data));
            }

            // If the field tokens have changed, we must move the entire folder
            if ($form_success
                && !$field_data_is_new
                && $old_field_token !== $update_data['field_token']){
                $old_content_path = MMRPG_CONFIG_FIELDS_CONTENT_PATH.$old_field_token.'/';
                $new_content_path = MMRPG_CONFIG_FIELDS_CONTENT_PATH.$update_data['field_token'].'/';
                if (rename($old_content_path, $new_content_path)){
                    $path_string = '<strong>'.mmrpg_clean_path($old_content_path).'</strong> &raquo; <strong>'.mmrpg_clean_path($new_content_path).'</strong>';
                    $form_messages[] = array('alert', 'Field directory renamed! '.$path_string);
                } else {
                    $form_messages[] = array('error', 'Unable to rename field directory!');
                }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['field_id'])){ exit_field_edit_action(false); }
            else { exit_field_edit_action($form_data['field_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    // If we're in groups mode, we need to preset vars and then include common file
    $object_group_kind = 'field';
    $object_group_class = 'field';
    $object_group_editor_url = 'admin/edit-fields/groups/';
    $object_group_editor_name = 'Field Groups';
    if ($sub_action == 'groups'){
        require('edit-groups_actions.php');
    }

    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/edit-fields/">Edit Fields</a>
        <? if ($sub_action == 'editor' && !empty($field_data)): ?>
            &raquo; <a href="admin/edit-fields/editor/field_id=<?= $field_data['field_id'] ?>"><?= !empty($field_name_display) ? $field_name_display : 'New Field' ?></a>
        <? elseif ($sub_action == 'groups'): ?>
            &raquo; <a href="<?= $object_group_editor_url ?>"><?= $object_group_editor_name ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-fields" data-baseurl="admin/edit-fields/" data-object="field" data-xobject="fields">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Fields</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <? /* <input type="hidden" name="action" value="edit-fields" /> */ ?>
                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="field_id" value="<?= !empty($search_data['field_id']) ? $search_data['field_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="field_name" placeholder="" value="<?= !empty($search_data['field_name']) ? htmlentities($search_data['field_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Class</strong>
                        <select class="select" name="field_class">
                            <option value=""></option>
                            <option value="mecha"<?= !empty($search_data['field_class']) && $search_data['field_class'] === 'mecha' ? ' selected="selected"' : '' ?>>Mecha</option>
                            <option value="master"<?= !empty($search_data['field_class']) && $search_data['field_class'] === 'master' ? ' selected="selected"' : '' ?>>Master</option>
                            <option value="boss"<?= !empty($search_data['field_class']) && $search_data['field_class'] === 'boss' ? ' selected="selected"' : '' ?>>Boss</option>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Type</strong>
                        <select class="select" name="field_type"><option value=""></option><?
                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                if ($type_info['type_class'] === 'special' && $type_token !== 'none'){ continue; }
                                ?><option value="<?= $type_token ?>"<?= !empty($search_data['field_type']) && $search_data['field_type'] === $type_token ? ' selected="selected"' : '' ?>><?= $type_token === 'none' ? 'Neutral' : ucfirst($type_token) ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Flavour</strong>
                        <input class="textbox" type="text" name="field_flavour" placeholder="" value="<?= !empty($search_data['field_flavour']) ? htmlentities($search_data['field_flavour'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Game</strong>
                        <select class="select" name="field_game"><option value=""></option><?
                            $field_games_tokens = $db->get_array_list("SELECT DISTINCT (field_game) AS game_token FROM mmrpg_index_fields ORDER BY field_game ASC;");
                            foreach ($field_games_tokens AS $game_key => $game_info){
                                $game_token = $game_info['game_token'];
                                ?><option value="<?= $game_token ?>"<?= !empty($search_data['field_game']) && $search_data['field_game'] === $game_token ? ' selected="selected"' : '' ?>><?= $game_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field fullsize has5cols flags">
                    <?
                    $flag_names = array(
                        'published' => array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished'),
                        'complete' => array('icon' => 'fas fa-check-circle', 'yes' => 'Complete', 'no' => 'Incomplete'),
                        'hidden' => array('icon' => 'fas fa-eye-slash', 'yes' => 'Hidden', 'no' => 'Visible')
                        );
                    cms_admin::object_index_flag_names_append_git_statuses($flag_names);
                    foreach ($flag_names AS $flag_token => $flag_info){
                        if (isset($flag_info['break'])){ echo('<div class="break"></div>'); continue; }
                        $flag_name = 'field_flag_'.$flag_token;
                        $flag_label = ucfirst($flag_token);
                        ?>
                        <div class="subfield">
                            <strong class="label"><?= $flag_label ?> <span class="<?= $flag_info['icon'] ?>"></span></strong>
                            <select class="select" name="<?= $flag_name ?>">
                                <option value=""<?= !isset($search_data[$flag_name]) || $search_data[$flag_name] === '' ? ' selected="selected"' : '' ?>></option>
                                <option value="1"<?= isset($search_data[$flag_name]) && $search_data[$flag_name] === 1 ? ' selected="selected"' : '' ?>><?= $flag_info['yes'] ?></option>
                                <option value="0"<?= isset($search_data[$flag_name]) && $search_data[$flag_name] === 0 ? ' selected="selected"' : '' ?>><?= $flag_info['no'] ?></option>
                            </select><span></span>
                        </div>
                        <?
                    }
                    ?>
                    </div>

                    <div class="buttons">
                        <input class="button search" type="submit" value="Search" />
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='admin/edit-fields/';" />
                        <a class="button new pending" href="<?= 'admin/edit-fields/editor/field_id=0' ?>">Create New Field</a>
                        <a class="button groups pending" href="<?= $object_group_editor_url ?>">Edit <?= $object_group_editor_name ?></a>
                    </div>

                </form>

            </div>

            <? if (!empty($search_results)): ?>

                <!-- SEARCH RESULTS -->

                <div class="results">

                    <table class="list" style="width: 100%;">
                        <colgroup>
                            <col class="id" width="60" />
                            <col class="name" width="" />
                            <col class="class" width="100" />
                            <col class="type" width="120" />
                            <col class="game" width="80" />
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('field_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('field_name', 'Name') ?></th>
                                <th class="class"><?= cms_admin::get_sort_link('field_class', 'Class') ?></th>
                                <th class="type"><?= cms_admin::get_sort_link('field_type', 'Type(s)') ?></th>
                                <th class="game"><?= cms_admin::get_sort_link('field_game', 'Game') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('field_flag_published', 'Published') ?></th>
                                <th class="flag complete"><?= cms_admin::get_sort_link('field_flag_complete', 'Complete') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('field_flag_hidden', 'Hidden') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head class"></th>
                                <th class="head type"></th>
                                <th class="head game"></th>
                                <th class="head flag published"></th>
                                <th class="head flag complete"></th>
                                <th class="head flag hidden"></th>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot name"></td>
                                <td class="foot class"></td>
                                <td class="foot type"></td>
                                <td class="foot game"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag complete"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            $temp_class_colours = array(
                                'system' => array('none', '<i class="fas fa-xxx"></i>'),
                                'master' => array('defense', '<i class="fas fa-robot"></i>'),
                                'player' => array('speed', '<i class="fas fa-user"></i>'),
                                'event' => array('space', '<i class="fas fa-book"></i>'),
                                'bonus' => array('energy', '<i class="fas fa-star"></i>')
                                );
                            foreach ($search_results AS $key => $field_data){

                                $field_id = $field_data['field_id'];
                                $field_token = $field_data['field_token'];
                                $field_name = $field_data['field_name'];
                                $field_class = ucfirst($field_data['field_class']);
                                $field_class_span = '<span class="type_span type_'.$temp_class_colours[$field_data['field_class']][0].'">'.$temp_class_colours[$field_data['field_class']][1].' '.$field_class.'</span>';
                                $field_type = !empty($field_data['field_type']) ? ucfirst($field_data['field_type']) : 'Neutral';
                                $field_type_span = '<span class="type_span type_'.(!empty($field_data['field_type']) ? $field_data['field_type'] : 'none').'">'.$field_type.'</span>';
                                if (!empty($field_data['field_type'])
                                    && !empty($field_data['field_type2'])){
                                    $field_type .= ' / '.ucfirst($field_data['field_type2']);
                                    $field_type_span = '<span class="type_span type_'.$field_data['field_type'].'_'.$field_data['field_type2'].'">'.ucwords($field_data['field_type'].' / '.$field_data['field_type2']).'</span>';
                                }
                                $field_game = ucfirst($field_data['field_game']);
                                $field_game_span = '<span class="type_span type_none">'.$field_game.'</span>';
                                $field_flag_published = !empty($field_data['field_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $field_flag_complete = !empty($field_data['field_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $field_flag_hidden = !empty($field_data['field_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $field_edit_url = 'admin/edit-fields/editor/field_id='.$field_id;
                                $field_name_link = '<a class="link" href="'.$field_edit_url.'">'.$field_name.'</a>';
                                cms_admin::object_index_links_append_git_statues($field_name_link, $field_token, $mmrpg_git_file_arrays);

                                $field_actions = '';
                                $field_actions .= '<a class="link edit" href="'.$field_edit_url.'"><span>edit</span></a>';
                                if (empty($field_data['field_flag_protected'])){
                                    $field_actions .= '<a class="link delete" data-delete="fields" data-field-id="'.$field_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$field_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$field_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="class"><div class="wrap">'.$field_class_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="type"><div class="wrap">'.$field_type_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="game"><div class="wrap">'.$field_game_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$field_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$field_flag_complete.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$field_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$field_actions.'</div></td>'.PHP_EOL;
                                echo '</tr>'.PHP_EOL;

                            }
                            ?>
                        </tbody>
                    </table>

                </div>

            <? endif; ?>

            <?

            //echo('<pre>$search_query = '.(!empty($search_query) ? htmlentities($search_query, ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
            //echo('<pre>$search_results = '.print_r($search_results, true).'</pre>');

            ?>

        <? endif; ?>

        <?
        if ($sub_action == 'editor'
            && isset($_GET['field_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($field_data['field_type']) ? $field_data['field_type'].(!empty($field_data['field_type2']) ? '_'.$field_data['field_type2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="field_type,field_type2">
                        <span class="title"><?= !empty($field_name_display) ? 'Edit Field &quot;'.$field_name_display.'&quot;' : 'Create New Field' ?></span>
                        <?

                        // Print out any git-related statues to this header
                        cms_admin::object_editor_header_echo_git_statues($field_data['field_token'], $mmrpg_git_file_arrays);

                        // If the field is published, generate and display a preview link
                        if (!empty($field_data['field_flag_published'])){
                            $preview_link = 'database/fields/';
                            $preview_link .= $field_data['field_token'].'/';
                            echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>


                    <? if (!$field_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="field">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="flavour">Flavour</a><span></span>
                            <a class="tab" data-tab="images">Images</a><span></span>
                            <a class="tab" data-tab="attachments">Attachments</a><span></span>
                            <a class="tab" data-tab="functions">Functions</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit-fields" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="field">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label">Field ID</strong>
                                    <input type="hidden" name="field_id" value="<?= $field_data['field_id'] ?>" />
                                    <input class="textbox" type="text" name="field_id" value="<?= $field_data['field_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Field Token</strong>
                                        <?= !empty($field_data['field_flag_protected']) ? '<em>cannot be changed</em>' : '' ?>
                                    </div>
                                    <input type="hidden" name="old_field_token" value="<?= $field_data['field_token'] ?>" />
                                    <input type="hidden" name="field_token" value="<?= $field_data['field_token'] ?>" />
                                    <input class="textbox" type="text" name="field_token" value="<?= $field_data['field_token'] ?>" maxlength="64" <?= !empty($field_data['field_flag_protected']) ? 'disabled="disabled"' : '' ?> />
                                </div>

                                <div class="field">
                                    <strong class="label">Field Name</strong>
                                    <input class="textbox" type="text" name="field_name" value="<?= $field_data['field_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field has2cols">
                                    <strong class="label">
                                        Type(s)
                                        <span class="type_span type_<?= (!empty($field_data['field_type']) ? $field_data['field_type'] : 'none').(!empty($field_data['field_type2']) ? '_'.$field_data['field_type2'] : '') ?> swatch floatright" data-auto="field-type" data-field-type="field_type,field_type2">&nbsp;</span>
                                    </strong>
                                    <div class="subfield">
                                        <select class="select" name="field_type">
                                            <option value=""<?= empty($field_data['field_type']) ? ' selected="selected"' : '' ?>>Neutral</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($field_data['field_type']) && $field_data['field_type'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <select class="select" name="field_type2">
                                            <option value=""<?= empty($field_data['field_type2']) ? ' selected="selected"' : '' ?>>-</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($field_data['field_type2']) && $field_data['field_type2'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                </div>

                                <div class="field">
                                    <strong class="label">Kind</strong>
                                    <select class="select" name="field_class">
                                        <option value="" <?= empty($field_data['field_class']) ? 'selected="selected"' : '' ?>>-</option>
                                        <option value="master" <?= $field_data['field_class'] == 'master' ? 'selected="selected"' : '' ?>>Master Field</option>
                                        <option value="player" <?= $field_data['field_class'] == 'player' ? 'selected="selected"' : '' ?>>Player Field</option>
                                        <option value="event" <?= $field_data['field_class'] == 'event' ? 'selected="selected"' : '' ?>>Event Field</option>
                                        <option value="bonus" <?= $field_data['field_class'] == 'bonus' ? 'selected="selected"' : '' ?>>Bonus Field</option>
                                    </select><span></span>
                                </div>

                                <?

                                // Pre-generate a list of all robots so we can re-use it over and over
                                $last_option_group = false;
                                $robot_options_markup = array();
                                $robot_options_markup[] = '<option value="">-</option>';
                                foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                                    if ($field_data['field_class'] === 'master' && $robot_info['robot_class'] !== 'master'){ continue; }
                                    elseif ($field_data['field_class'] !== 'master' && $robot_info['robot_class'] === 'mecha'){ continue; }
                                    $class_group = (ucfirst($robot_info['robot_class']).(substr($robot_info['robot_class'], -2, 2) === 'ss' ? 'es' : 's')).' | '.$robot_info['robot_group'];
                                    if ($last_option_group !== $class_group){
                                        if (!empty($last_option_group)){ $robot_options_markup[] = '</optgroup>'; }
                                        $last_option_group = $class_group;
                                        $robot_options_markup[] = '<optgroup label="'.$class_group.'">';
                                    }
                                    $robot_name = $robot_info['robot_name'];
                                    $robot_cores = ucwords(implode(' / ', array_values(array_filter(array($robot_info['robot_core'], $robot_info['robot_core2'])))));
                                    if (empty($robot_cores)){ $robot_cores = 'Neutral'; }
                                    $robot_options_markup[] = '<option value="'.$robot_token.'">'.$robot_name.' ('.$robot_cores.')</option>';
                                }
                                if (!empty($last_option_group)){ $robot_options_markup[] = '</optgroup>'; }
                                $robot_options_count = count($robot_options_markup);
                                $robot_options_markup = implode(PHP_EOL, $robot_options_markup);

                                ?>

                                <div class="field has2cols">
                                    <div class="label">
                                        <strong>Master(s)</strong>
                                    </div>
                                    <div class="subfield">
                                        <select class="select" name="field_master">
                                            <? $current_value = !empty($field_data['field_master']) ? $field_data['field_master'] : ''; ?>
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $robot_options_markup) ?>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <select class="select" name="field_master2">
                                            <? $current_value = !empty($field_data['field_master2']) ? json_decode($field_data['field_master2'])[0] : ''; ?>
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $robot_options_markup) ?>
                                        </select><span></span>
                                    </div>
                                </div>

                                <? if (!$field_data_is_new){ ?>

                                    <hr />

                                    <?

                                    // Pre-generate a list of all mechas so we can re-use it over and over
                                    $last_option_group = false;
                                    $mecha_options_markup = array();
                                    $mecha_options_markup[] = '<option value="">-</option>';
                                    foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                                        if ($robot_info['robot_class'] !== 'mecha'){ continue; }
                                        $class_group = (ucfirst($robot_info['robot_class']).(substr($robot_info['robot_class'], -2, 2) === 'ss' ? 'es' : 's')).' | '.$robot_info['robot_group'];
                                        if ($last_option_group !== $class_group){
                                            if (!empty($last_option_group)){ $mecha_options_markup[] = '</optgroup>'; }
                                            $last_option_group = $class_group;
                                            $mecha_options_markup[] = '<optgroup label="'.$class_group.'">';
                                        }
                                        $robot_name = $robot_info['robot_name'];
                                        $robot_cores = ucwords(implode(' / ', array_values(array_filter(array($robot_info['robot_core'], $robot_info['robot_core2'])))));
                                        if (empty($robot_cores)){ $robot_cores = 'Neutral'; }
                                        $mecha_options_markup[] = '<option value="'.$robot_token.'">'.$robot_name.' ('.$robot_cores.')</option>';
                                    }
                                    if (!empty($last_option_group)){ $mecha_options_markup[] = '</optgroup>'; }
                                    $robot_options_count = count($mecha_options_markup);
                                    $mecha_options_markup = implode(PHP_EOL, $mecha_options_markup);

                                    ?>

                                    <div class="field fullsize has2cols multirow">
                                        <strong class="label">
                                            Support Mechas
                                            <em>These are the mechas that appear in the background/foreground and in battle</em>
                                        </strong>
                                        <?
                                        $current_mecha_list = !empty($field_data['field_mechas']) ? json_decode($field_data['field_mechas'], true) : array();
                                        $select_limit = max(4, count($current_mecha_list));
                                        $select_limit += 0 - ($select_limit % 2);
                                        for ($i = 0; $i < $select_limit; $i++){
                                            $current_value = isset($current_mecha_list[$i]) ? $current_mecha_list[$i] : '';
                                            ?>
                                            <div class="subfield">
                                                <select class="select" name="field_mechas[<?= $i ?>]">
                                                    <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $mecha_options_markup) ?>
                                                </select><span></span>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>

                                    <hr />

                                    <?

                                    // Pre-generate a list of all types so we can re-use it over and over
                                    $multiplier_options_markup = array();
                                    $multiplier_options_markup[] = '<option value="">-</option>';
                                    foreach ($mmrpg_types_index AS $type_token => $type_info){
                                        if ($type_token === 'none'){ continue; }
                                        elseif ($type_info['type_class'] === 'special' && !in_array($type_token, array('experience', 'damage', 'recovery'))){ continue; }
                                        $multiplier_options_markup[] = '<option value="'.$type_token.'">'.$type_info['type_name'].'</option>';
                                    }
                                    $multiplier_options_count = count($multiplier_options_markup);
                                    $multiplier_options_markup = implode(PHP_EOL, $multiplier_options_markup);

                                    ?>

                                    <div class="field fullsize has2cols multirow">
                                        <strong class="label">
                                            Field Multipliers
                                            <em>These are the elemental type modifiers that persist on this field</em>
                                        </strong>
                                        <?
                                        $current_multipliers = !empty($field_data['field_multipliers']) && !is_array($field_data['field_multipliers']) ? json_decode($field_data['field_multipliers'], true) : array();
                                        $current_multipliers_types = array_keys($current_multipliers);
                                        $select_limit = max(4, (count($current_multipliers_types) + 1));
                                        $select_limit += 0 - ($select_limit % 2);
                                        for ($i = 0; $i < $select_limit; $i++){
                                            $current_type_token = isset($current_multipliers_types[$i]) ? $current_multipliers_types[$i] : '';
                                            $current_type_value = (!empty($current_multipliers[$current_type_token]) ? $current_multipliers[$current_type_token] : 1) * 1;
                                            //if ($current_type_value === 0 || $current_type_value === 1){ continue; }
                                            if ($current_type_value === 0 || $current_type_value === 1){ $current_type_token = ''; $current_type_value = '1.0'; }
                                            ?>
                                            <div class="subfield fmultipliers">
                                                <div class="select-span-wrap"><select class="select" name="field_multipliers[<?= $i ?>][token]">
                                                    <?= str_replace('value="'.$current_type_token.'"', 'value="'.$current_type_token.'" selected="selected"', $multiplier_options_markup) ?>
                                                </select><span></span></div>
                                                <input class="textbox" type="number" name="field_multipliers[<?= $i ?>][value]" value="<?= $current_type_value ?>" maxlength="3" placeholder="1.0" step="0.1" min="0.1" max="9.9"  />
                                                <span class="type_span type_<?= $current_type_token ?> swatch floatright" data-auto="field-type" data-field-type="field_multipliers[<?= $i ?>][token]" data-field-type-rules="empty-is-inactive">&nbsp;</span>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>

                                    <hr />

                                    <div class="field foursize">
                                        <strong class="label">Source Game</strong>
                                        <? $current_value = !empty($field_data['field_game']) ? $field_data['field_game'] : ''; ?>
                                        <select class="select" name="field_game">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $source_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                <? } ?>

                            </div>

                            <? if (!$field_data_is_new){ ?>
                                <div class="panel" data-tab="flavour">

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Field Class</strong>
                                            <em>three-to-four word classification</em>
                                        </div>
                                        <input class="textbox" type="text" name="field_description" value="<?= htmlentities($field_data['field_description'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="32" />
                                    </div>

                                    <div class="field fullsize">
                                        <div class="label">
                                            <strong>Field Description</strong>
                                            <em>short paragraph about field's design, background, lore, etc.</em>
                                        </div>
                                        <textarea class="textarea" name="field_description2" rows="10"><?= htmlentities($field_data['field_description2'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Field Music</strong>
                                            <em>default music that plays on this stage</em>
                                        </div>
                                        <div class="subfield">
                                            <select class="select" name="field_music">
                                                <?
                                                if (!empty($field_data['field_music'])
                                                    && !strstr($music_options_markup, 'value="'.$field_data['field_music'].'"')){
                                                    ?>
                                                    <option value="">-</option>
                                                    <optgroup label="Legacy Support">
                                                        <option value="<?= $field_data['field_music'] ?>" selected="selected"><?= ucwords(str_replace('-', ' ', $field_data['field_music'])).' (Legacy)' ?></option>
                                                    </optgroup>
                                                    <?= str_replace('value="'.$field_data['field_music'].'"', 'value="'.$field_data['field_music'].'" selected="selected"', str_replace('<option value="">-</option>', '', $music_options_markup)) ?>
                                                    <?
                                                } else {
                                                    ?>
                                                    <?= str_replace('value="'.$field_data['field_music'].'"', 'value="'.$field_data['field_music'].'" selected="selected"', $music_options_markup) ?>
                                                    <?
                                                }
                                                ?>
                                            </select><span></span>
                                        </div>
                                        <?
                                        $data_path = '';
                                        $data_backup_path = '';
                                        $mmrpg_music_rootdir = '';
                                        $mmrpg_music_rooturl = '';
                                        if (!empty($field_data['field_music'])){
                                            $mmrpg_music_path = 'prototype/sounds/';
                                            $mmrpg_music_rootdir = MMRPG_CONFIG_CDN_ROOTDIR.$mmrpg_music_path;
                                            $mmrpg_music_rooturl = MMRPG_CONFIG_CDN_ROOTURL.$mmrpg_music_path;
                                            $this_music_path = $field_data['field_music'].'/';
                                            $data_path = $mmrpg_music_rooturl.$this_music_path.'audio.mp3';
                                            $data_backup_path = $mmrpg_music_rooturl.$this_music_path.'audio.ogg';
                                        }
                                        ?>
                                        <div class="audio-player light-theme"
                                            style="margin: 5px auto 0 0;"
                                            data-kind="music"
                                            data-path="<?= $data_path ?>"
                                            data-backup-path="<?= $data_backup_path ?>"
                                            data-select="field_music"
                                            data-select-path-base="<?= $mmrpg_music_rooturl ?>"
                                            data-select-path-sources="/audio.mp3,/audio.ogg"
                                            ><i class="loading fa fas fa-music"></i>
                                        </div>
                                    </div>

                                </div>
                            <? } ?>

                            <? if (!$field_data_is_new){ ?>
                                <div class="panel" data-tab="images">

                                    <?

                                    // Pre-generate a list of all contributors so we can re-use it over and over
                                    $contributor_options_markup = array();
                                    $contributor_options_markup[] = '<option value="0">-</option>';
                                    foreach ($mmrpg_contributors_index AS $editor_id => $user_info){
                                        $option_label = $user_info['user_name'];
                                        if (!empty($user_info['user_name_public']) && $user_info['user_name_public'] !== $user_info['user_name']){ $option_label = $user_info['user_name_public'].' ('.$option_label.')'; }
                                        $contributor_options_markup[] = '<option value="'.$editor_id.'">'.$option_label.'</option>';
                                    }
                                    $contributor_options_count = count($contributor_options_markup);
                                    $contributor_options_markup = implode(PHP_EOL, $contributor_options_markup);

                                    ?>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Image Editor #1</strong>
                                            <em>user who edited or created this sprite</em>
                                        </div>
                                        <select class="select" name="field_image_editor">
                                            <?= str_replace('value="'.$field_data['field_image_editor'].'"', 'value="'.$field_data['field_image_editor'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Image Editor #2</strong>
                                            <em>another user who collaborated on this sprite</em>
                                        </div>
                                        <select class="select" name="field_image_editor2">
                                            <?= str_replace('value="'.$field_data['field_image_editor2'].'"', 'value="'.$field_data['field_image_editor2'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                    <hr />

                                    <?

                                    // Define the base sprite path for all fields
                                    $base_image_path = 'content/fields/';
                                    $base_field_width = 1124;
                                    $base_field_height = 248;

                                    // Define the file path for this field and collect existing files
                                    $field_file_path = rtrim($base_image_path, '/').'/'.$field_data['field_token'].'/sprites/';
                                    $field_file_dir = MMRPG_CONFIG_ROOTDIR.$field_file_path;
                                    $field_files_existing = getDirContents($field_file_dir);
                                    if (!empty($field_files_existing)){ $field_files_existing = array_map(function($s)use($field_file_dir){ return str_replace($field_file_dir, '', str_replace('\\', '/', $s)); }, $field_files_existing); }

                                    ?>

                                    <input class="hidden" type="hidden" name="field_background" value="<?= $field_data['field_token'] ?>" />
                                    <input class="hidden" type="hidden" name="field_foreground" value="<?= $field_data['field_token'] ?>" />

                                    <div class="field fullsize has2cols widecols multirow sprites has-filebars">

                                        <div class="subfield" style="float: none; margin-bottom: 10px;"><strong class="label">Required Images</strong></div>

                                        <?

                                        // Define an array for required field files
                                        $field_files_required = array();

                                        // Define the field files that are required
                                        $field_files_required[] = array(
                                            'label' => 'background image (preview)',
                                            'help' => 'non-animated preview of the background image',
                                            'path' => $field_file_path,
                                            'name' => 'battle-field_preview.png',
                                            'width' => $base_field_width,
                                            'height' => $base_field_height
                                            );
                                        $field_files_required[] = array(
                                            'label' => 'background image (animated)',
                                            'help' => 'base background image, fully-animated, opaque',
                                            'path' => $field_file_path,
                                            'name' => 'battle-field_background_base.gif',
                                            'width' => $base_field_width,
                                            'height' => $base_field_height
                                            );
                                        $field_files_required[] = array(
                                            'label' => 'foreground image',
                                            'help' => 'base foreground image, partially transparent',
                                            'path' => $field_file_path,
                                            'name' => 'battle-field_foreground_base.png',
                                            'width' => $base_field_width,
                                            'height' => $base_field_height
                                            );
                                        $field_files_required[] = array(
                                            'label' => 'avatar image',
                                            'help' => 'square image for avatars w/ bg & fg visible',
                                            'path' => $field_file_path,
                                            'name' => 'battle-field_avatar.png',
                                            'width' => 100,
                                            'height' => 100
                                            );

                                        // Loop through required files and display filebars for them
                                        foreach ($field_files_required AS $file_key => $filebar_info){
                                            ?>
                                            <div class="subfield" style="<?= $file_key % 2 == 0 ? 'clear: left;' : '' ?>" data-group="images" data-size="<?= $base_field_width ?>">
                                                <div class="sublabel" style="font-size: 90%; margin-bottom: 2px;">
                                                    <strong><?= $filebar_info['label'] ?></strong>
                                                    <?= !empty($filebar_info['help']) ? ('<em>'.$filebar_info['help'].'</em>') : '' ?>
                                                </div>
                                                <ul class="files">
                                                    <?
                                                    $display_path = 'images';
                                                    $this_sprite_path = rtrim($filebar_info['path'], '/').'/';
                                                    $sheet_width = !empty($filebar_info['width']) ? $filebar_info['width'] : '';
                                                    $sheet_height = !empty($filebar_info['height']) ? $filebar_info['height'] : '';
                                                    $file_name = $filebar_info['name'];
                                                    $file_href = MMRPG_CONFIG_ROOTURL.$this_sprite_path.$file_name;
                                                    $file_exists = in_array($file_name, $field_files_existing) ? true : false;
                                                    $file_kind = preg_replace('/^([^.]+)\.([^.]+)$/i', 'image/$2', $file_name);
                                                    $file_is_unused = false;
                                                    $file_is_optional = false;
                                                    echo('<li>');
                                                        echo('<div class="filebar" data-auto="file-bar" data-file-path="'.$this_sprite_path.'" data-file-name="'.$file_name.'" data-file-kind="'.$file_kind.'" data-file-width="'.$sheet_width.'" data-file-height="'.$sheet_height.'">');
                                                            echo($file_exists ? '<a class="link view" href="'.$file_href.'?'.time().'" target="_blank" data-href="'.$file_href.'">'.$display_path.'/'.$file_name.'</a>' : '<a class="link view disabled" target="_blank" data-href="'.$file_href.'">'.$display_path.'/'.$file_name.'</a>');
                                                            echo('<span class="info size">'.(!empty($sheet_width) ? $sheet_width : '').'w &times; '.(!empty($sheet_height) ? $sheet_height : '').'h</span>');
                                                            echo($file_exists ? '<span class="info status good">&check;</span>' : '<span class="info status bad">&cross;</span>');
                                                            echo('<a class="action delete'.(!$file_exists ? ' disabled' : '').'" data-action="delete" data-file-hash="'.md5('delete/'.$this_sprite_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">Delete</a>');
                                                            echo('<a class="action upload'.($file_exists ? ' disabled' : '').'" data-action="upload" data-file-hash="'.md5('upload/'.$this_sprite_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">');
                                                                echo('<span class="text">Upload</span>');
                                                                echo('<input class="input" type="file" name="file_info" value=""'.($file_exists ? ' disabled="disabled"' : '').' />');
                                                            echo('</a>');
                                                        echo('</div>');
                                                    echo('</li>'.PHP_EOL);

                                                    ?>
                                                </ul>
                                            </div>
                                            <?
                                        }

                                        ?>

                                    </div>

                                    <hr />

                                    <div class="field fullsize has2cols widecols multirow sprites has-filebars">

                                        <div class="subfield" style="float: none; margin-bottom: 10px;"><strong class="label">Optional Images</strong></div>

                                        <?

                                        // Define an array for required field files
                                        $field_files_required = array();

                                        // Define the field files that are required
                                        $field_files_required[] = array(
                                            'label' => 'background image (stacked)',
                                            'help' => 'all background animation frames stacked vertically',
                                            'path' => $field_file_path,
                                            'name' => 'battle-field_background_base.png',
                                            'width' => $base_field_width
                                            );

                                        // Loop through required files and display filebars for them
                                        foreach ($field_files_required AS $file_key => $filebar_info){
                                            ?>
                                            <div class="subfield" style="<?= $file_key % 2 == 0 ? 'clear: left;' : '' ?>" data-group="images" data-size="<?= $base_field_width ?>">
                                                <div class="sublabel" style="font-size: 90%; margin-bottom: 2px;">
                                                    <strong><?= $filebar_info['label'] ?></strong>
                                                    <?= !empty($filebar_info['help']) ? ('<em>'.$filebar_info['help'].'</em>') : '' ?>
                                                </div>
                                                <ul class="files">
                                                    <?
                                                    $display_path = 'images';
                                                    $this_sprite_path = rtrim($filebar_info['path'], '/').'/';
                                                    $sheet_width = !empty($filebar_info['width']) ? $filebar_info['width'] : '';
                                                    $sheet_height = !empty($filebar_info['height']) ? $filebar_info['height'] : '';
                                                    $file_name = $filebar_info['name'];
                                                    $file_href = MMRPG_CONFIG_ROOTURL.$this_sprite_path.$file_name;
                                                    $file_exists = in_array($file_name, $field_files_existing) ? true : false;
                                                    $file_kind = preg_replace('/^([^.]+)\.([^.]+)$/i', 'image/$2', $file_name);
                                                    $file_is_unused = false;
                                                    $file_is_optional = false;
                                                    echo('<li>');
                                                        echo('<div class="filebar" data-auto="file-bar" data-file-path="'.$this_sprite_path.'" data-file-name="'.$file_name.'" data-file-kind="'.$file_kind.'" data-file-width="'.$sheet_width.'" data-file-height="'.$sheet_height.'">');
                                                            echo($file_exists ? '<a class="link view" href="'.$file_href.'?'.time().'" target="_blank" data-href="'.$file_href.'">'.$display_path.'/'.$file_name.'</a>' : '<a class="link view disabled" target="_blank" data-href="'.$file_href.'">'.$display_path.'/'.$file_name.'</a>');
                                                            echo('<span class="info size">'.(!empty($sheet_width) ? $sheet_width : '').'w &times; '.(!empty($sheet_height) ? $sheet_height : '').'h</span>');
                                                            echo($file_exists ? '<span class="info status good">&check;</span>' : '<span class="info status bad">&cross;</span>');
                                                            echo('<a class="action delete'.(!$file_exists ? ' disabled' : '').'" data-action="delete" data-file-hash="'.md5('delete/'.$this_sprite_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">Delete</a>');
                                                            echo('<a class="action upload'.($file_exists ? ' disabled' : '').'" data-action="upload" data-file-hash="'.md5('upload/'.$this_sprite_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">');
                                                                echo('<span class="text">Upload</span>');
                                                                echo('<input class="input" type="file" name="file_info" value=""'.($file_exists ? ' disabled="disabled"' : '').' />');
                                                            echo('</a>');
                                                        echo('</div>');
                                                    echo('</li>'.PHP_EOL);

                                                    ?>
                                                </ul>
                                            </div>
                                            <?
                                        }

                                        ?>

                                    </div>

                                    <?

                                    // Define an array for required field files
                                    $field_files_variants = array();

                                    // Collect a list of image files in this field's directory so we can parse
                                    $possible_variants_config = array();
                                    $possible_variants_config[] = array('/^battle-field_preview_([-_a-z0-9]+).png$/i', 'background image (preview) [???]');
                                    $possible_variants_config[] = array('/^battle-field_background_base_([-_a-z0-9]+).gif$/i', 'background image (animated) [???]');
                                    $possible_variants_config[] = array('/^battle-field_foreground_base_([-_a-z0-9]+).png$/i', 'foreground image [???]');
                                    $possible_variants_config[] = array('/^battle-field_avatar_([-_a-z0-9]+).png$/i', 'avatar image [???]');
                                    $possible_variants_config[] = array('/^battle-field_background_base_([-_a-z0-9]+).png$/i', 'background image (stacked) [???]');
                                    //error_log('$field_files_existing = '.print_r($field_files_existing, true));
                                    foreach ($possible_variants_config AS $variant_key => $variant_config){
                                        list($variant_regex, $variant_label) = $variant_config;
                                        foreach ($field_files_existing AS $key => $file){
                                            //error_log('check if '.$file.' matches '.PHP_EOL.$variant_regex.PHP_EOL);
                                            if (preg_match($variant_regex, $file, $matches)){
                                                $field_files_variants[] = array(
                                                    'label' => str_replace('[???]', '['.$matches[1].']', $variant_label),
                                                    'help' => '',
                                                    'path' => $field_file_path,
                                                    'name' => $file,
                                                    'width' => $base_field_width
                                                    );
                                                //error_log('$matches = '.print_r($matches, true));
                                            }
                                        }
                                    }
                                    //error_log('$field_files_variants = '.print_r($field_files_variants, true));

                                    // If there are variant images already uploaded, we should display inputs for them
                                    if (!empty($field_files_variants)){
                                        ?>

                                        <hr />

                                        <div class="field fullsize has2cols widecols multirow sprites has-filebars">

                                            <div class="subfield" style="float: none; margin-bottom: 10px;"><strong class="label">Detected Variant Images</strong></div>

                                            <?

                                            /*
                                            // Define the field files that are required
                                            $field_files_variants[] = array(
                                                'label' => 'background image (animated) [dr-light]',
                                                'help' => '',
                                                'path' => $field_file_path,
                                                'name' => 'battle-field_background_base_dr-light.gif',
                                                'width' => $base_field_width
                                                );
                                            */

                                            // Loop through required files and display filebars for them
                                            foreach ($field_files_variants AS $file_key => $filebar_info){
                                                ?>
                                                <div class="subfield fullsize" style="<?= $file_key % 2 == 0 ? 'clear: left;' : '' ?>" data-group="images" data-size="<?= $base_field_width ?>">
                                                    <div class="sublabel" style="font-size: 90%; margin-bottom: 2px;">
                                                        <strong><?= $filebar_info['label'] ?></strong>
                                                        <?= !empty($filebar_info['help']) ? ('<em>'.$filebar_info['help'].'</em>') : '' ?>
                                                    </div>
                                                    <ul class="files">
                                                        <?
                                                        $display_path = 'images';
                                                        $this_sprite_path = rtrim($filebar_info['path'], '/').'/';
                                                        $sheet_width = !empty($filebar_info['width']) ? $filebar_info['width'] : '';
                                                        $sheet_height = !empty($filebar_info['height']) ? $filebar_info['height'] : '';
                                                        $file_name = $filebar_info['name'];
                                                        $file_href = MMRPG_CONFIG_ROOTURL.$this_sprite_path.$file_name;
                                                        $file_exists = in_array($file_name, $field_files_existing) ? true : false;
                                                        $file_kind = preg_replace('/^([^.]+)\.([^.]+)$/i', 'image/$2', $file_name);
                                                        $file_is_unused = false;
                                                        $file_is_optional = false;
                                                        echo('<li>');
                                                            echo('<div class="filebar" data-auto="file-bar" data-file-path="'.$this_sprite_path.'" data-file-name="'.$file_name.'" data-file-kind="'.$file_kind.'" data-file-width="'.$sheet_width.'" data-file-height="'.$sheet_height.'">');
                                                                echo($file_exists ? '<a class="link view" href="'.$file_href.'?'.time().'" target="_blank" data-href="'.$file_href.'">'.$display_path.'/'.$file_name.'</a>' : '<a class="link view disabled" target="_blank" data-href="'.$file_href.'">'.$display_path.'/'.$file_name.'</a>');
                                                                echo('<span class="info size">'.(!empty($sheet_width) ? $sheet_width : '').'w &times; '.(!empty($sheet_height) ? $sheet_height : '').'h</span>');
                                                                echo($file_exists ? '<span class="info status good">&check;</span>' : '<span class="info status bad">&cross;</span>');
                                                                echo('<a class="action delete'.(!$file_exists ? ' disabled' : '').'" data-action="delete" data-file-hash="'.md5('delete/'.$this_sprite_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">Delete</a>');
                                                                echo('<a class="action upload'.($file_exists ? ' disabled' : '').'" data-action="upload" data-file-hash="'.md5('upload/'.$this_sprite_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">');
                                                                    echo('<span class="text">Upload</span>');
                                                                    echo('<input class="input" type="file" name="file_info" value=""'.($file_exists ? ' disabled="disabled"' : '').' />');
                                                                echo('</a>');
                                                            echo('</div>');
                                                        echo('</li>'.PHP_EOL);

                                                        ?>
                                                    </ul>
                                                </div>
                                                <?
                                            }

                                            ?>

                                        </div>
                                        <?
                                    }

                                    ?>

                                </div>
                            <? } ?>

                            <? if (!$field_data_is_new){ ?>
                                <div class="panel" data-tab="attachments">

                                    <?
                                    // Collect the background and foreground image URLs if available
                                    $background_image_url = 'content/fields/'.$field_data['field_background'].'/sprites/battle-field_background_base.gif';
                                    if (!file_exists(MMRPG_CONFIG_ROOTDIR.$background_image_url)){ $background_image_url = false; }
                                    $foreground_image_url = 'content/fields/'.$field_data['field_foreground'].'/sprites/battle-field_foreground_base.png';
                                    if (!file_exists(MMRPG_CONFIG_ROOTDIR.$foreground_image_url)){ $foreground_image_url = false; }
                                    ?>
                                    <div class="bfg-attachments-preview" data-field-background="<?= $field_data['field_background'] ?>" data-field-foreground="<?= $field_data['field_foreground'] ?>">
                                        <div class="preview_wrapper">
                                            <div class="background_image" style="<?= !empty($background_image_url) ? 'background-image: url('.$background_image_url.'?'.MMRPG_CONFIG_CACHE_DATE.');' : ''; ?>">&nbsp;</div>
                                            <div class="background_attachments">&nbsp;</div>
                                            <div class="foreground_image" style="<?= !empty($foreground_image_url) ? 'background-image: url('.$foreground_image_url.'?'.MMRPG_CONFIG_CACHE_DATE.');' : ''; ?>">&nbsp;</div>
                                            <div class="foreground_attachments">&nbsp;</div>
                                        </div>
                                        <div class="buttons">
                                            <input type="button" name="toggle_background" value="Toggle Background" />
                                            <input type="button" name="toggle_foreground" value="Toggle Foreground" />
                                        </div>
                                    </div>

                                    <?
                                    // Define an inline function for printing the background/foreground attachment rows
                                    $print_bfg_attachment_fields = function($kind) use($field_data){
                                        ?>
                                        <div class="field fullsize hasXcols bfg-attachment bfg-headers">
                                            <div class="subfield bfg-number">
                                                <div class="label">No.</div>
                                            </div>
                                            <div class="subfield bfg-class">
                                                <div class="label">Class</div>
                                            </div>
                                            <div class="subfield bfg-token">
                                                <div class="label">Token</div>
                                            </div>
                                            <div class="subfield bfg-direction">
                                                <div class="label">Direction</div>
                                            </div>
                                            <div class="subfield bfg-offset bfg-offset-x">
                                                <div class="label">Offset X</div>
                                            </div>
                                            <div class="subfield bfg-offset bfg-offset-y">
                                                <div class="label">Offset Y</div>
                                            </div>
                                            <div class="subfield bfg-frames">
                                                <div class="label">Frame(s)</div>
                                            </div>
                                            <div class="subfield bfg-view">
                                                <div class="label"><i class="fas fa fa-eye"></i></div>
                                            </div>
                                        </div>
                                        <?
                                        // Break apart the list of background/foreground attachments and display rows for them (always add an extra at the bottom)
                                        $field_data_key = 'field_'.$kind.'_attachments';
                                        $attachments_list = !empty($field_data[$field_data_key]) && !is_array($field_data[$field_data_key]) ? array_values(json_decode($field_data[$field_data_key], true)) : array();
                                        $attachments_list_size = count($attachments_list);
                                        for ($key = 0; $key <= $attachments_list_size; $key++){
                                            $attachment_key = $key;
                                            $attachment_info = isset($attachments_list[$attachment_key]) ? $attachments_list[$attachment_key] : array();
                                            $attachment_class = isset($attachment_info['class']) ? $attachment_info['class'] : '';
                                            $attachment_size = isset($attachment_info['size']) ? $attachment_info['size'] : ($kind === 'foreground' ? 80 : 40);
                                            $attachment_token = isset($attachment_info[$attachment_class.'_token']) ? $attachment_info[$attachment_class.'_token'] : '';
                                            $attachment_frames = isset($attachment_info[$attachment_class.'_frame']) ? implode(',', $attachment_info[$attachment_class.'_frame']) : '';
                                            $attachment_direction = isset($attachment_info[$attachment_class.'_direction']) ? $attachment_info[$attachment_class.'_direction'] : '';
                                            $attachment_offset_x = isset($attachment_info['offset_x']) ? (int)($attachment_info['offset_x']) : 0;
                                            $attachment_offset_y = isset($attachment_info['offset_y']) ? (int)($attachment_info['offset_y']) : 0;
                                            if ($attachment_class === 'robot'){
                                                $attachment_token = 'met';
                                                $attachment_frames = '0';
                                            }
                                            $is_template = false;
                                            if (empty($attachment_info)){
                                                $attachment_key = '{x}';
                                                $is_template = true;
                                            }
                                            ?>
                                            <div class="field fullsize hasXcols bfg-attachment" data-key="<?= $attachment_key ?>">
                                                <div class="subfield bfg-number">
                                                    <input class="textbox" type="text" value="#<?= is_numeric($attachment_key) ? ($attachment_key + 1) : $attachment_key ?>" disabled="disabled" />
                                                    <input class="hidden" type="hidden" name="<?= $field_data_key ?>[<?= $attachment_key ?>][size]" value="<?= $attachment_size ?>" />
                                                </div>
                                                <div class="subfield bfg-class">
                                                    <select class="select" name="<?= $field_data_key ?>[<?= $attachment_key ?>][class]">
                                                        <option value=""<?= !isset($attachment_class) || $attachment_class === '' ? ' selected="selected"' : '' ?>>-</option>
                                                        <option value="robot"<?= isset($attachment_class) && $attachment_class === 'robot' ? ' selected="selected"' : '' ?>>mecha</option>
                                                        <option value="object"<?= isset($attachment_class) && $attachment_class === 'object' ? ' selected="selected"' : '' ?>>object</option>
                                                    </select><span></span>
                                                </div>
                                                <div class="subfield bfg-token">
                                                    <input class="textbox" type="text" name="<?= $field_data_key ?>[<?= $attachment_key ?>][token]" value="<?= $attachment_token ?>" <?= $attachment_class === 'robot' ? 'readonly="readonly"' : '' ?> />
                                                </div>
                                                <div class="subfield bfg-direction">
                                                    <select class="select" name="<?= $field_data_key ?>[<?= $attachment_key ?>][direction]">
                                                        <option value=""<?= !isset($attachment_direction) || $attachment_direction === '' ? ' selected="selected"' : '' ?>>-</option>
                                                        <option value="left"<?= isset($attachment_direction) && $attachment_direction === 'left' ? ' selected="selected"' : '' ?>>left</option>
                                                        <option value="right"<?= isset($attachment_direction) && $attachment_direction === 'right' ? ' selected="selected"' : '' ?>>right</option>
                                                    </select><span></span>
                                                </div>
                                                <div class="subfield bfg-offset bfg-offset-x">
                                                    <input class="textbox" type="number" name="<?= $field_data_key ?>[<?= $attachment_key ?>][offset_x]" value="<?= $attachment_offset_x ?>" />
                                                </div>
                                                <div class="subfield bfg-offset bfg-offset-y">
                                                    <input class="textbox" type="number" name="<?= $field_data_key ?>[<?= $attachment_key ?>][offset_y]" value="<?= $attachment_offset_y ?>" />
                                                </div>
                                                <div class="subfield bfg-frames">
                                                    <input class="textbox" type="text" name="<?= $field_data_key ?>[<?= $attachment_key ?>][frame]" value="<?= $attachment_frames ?>" <?= $attachment_class === 'robot' ? 'readonly="readonly"' : '' ?> />
                                                </div>
                                                <div class="subfield bfg-view">
                                                    <input class="checkbox" type="checkbox" value="1" data-kind="<?= $kind ?>" data-key="<?= $attachment_key ?>" />
                                                </div>
                                            </div>
                                            <?
                                        }
                                    };
                                    ?>

                                    <div class="field fullsize" style="min-height: 0;">
                                        <div class="label">
                                            <strong>Background Attachments</strong>
                                            <em>list of background sprites and their positions [example Mets automatically replaced at runtime]</em>
                                        </div>
                                    </div>
                                    <div class="bfg-attachments-inputs" data-kind="background">
                                        <?= $print_bfg_attachment_fields('background'); ?>
                                        <a class="button add-attachment">+ Add Another Attachment</a>
                                    </div>

                                    <div class="field fullsize" style="min-height: 0; margin-top: 20px;">
                                        <div class="label">
                                            <strong>Foreground Attachments</strong>
                                            <em>encoded list of foreground sprites and their positions [example Mets automatically replaced at runtime]</em>
                                        </div>
                                    </div>
                                    <div class="bfg-attachments-inputs" data-kind="foreground">
                                        <?= $print_bfg_attachment_fields('foreground'); ?>
                                        <a class="button add-attachment">+ Add Another Attachment</a>
                                    </div>


                                    <?/*
                                    <hr />
                                    <div class="field fullsize codemirror" data-codemirror-mode="json">
                                        <div class="label">
                                            <strong>Background Attachments JSON</strong>
                                            <em>encoded list of background sprites and their positions</em>
                                        </div>
                                        <textarea class="textarea" name="field_background_attachments" rows="10"><?= htmlentities($field_data['field_background_attachments'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>
                                    <div class="field fullsize codemirror" data-codemirror-mode="json">
                                        <div class="label">
                                            <strong>Foreground Attachments JSON</strong>
                                            <em>encoded list of foreground sprites and their positions</em>
                                        </div>
                                        <textarea class="textarea" name="field_foreground_attachments" rows="10"><?= htmlentities($field_data['field_foreground_attachments'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>
                                    <div class="field fullsize">
                                        <div class="label">Background Attachments Array</div>
                                        <?
                                        echo('<pre>'.print_r(json_decode($field_data['field_background_attachments'], true), true).'</pre>');
                                        ?>
                                    </div>
                                    <div class="field fullsize">
                                        <div class="label">Foreground Attachments Array</div>
                                        <?
                                        echo('<pre>'.print_r(json_decode($field_data['field_foreground_attachments'], true), true).'</pre>');
                                        ?>
                                    </div>
                                    */?>

                                </div>
                            <? } ?>

                            <? if (!$field_data_is_new){ ?>
                                <div class="panel" data-tab="functions">

                                    <div class="field fullsize codemirror" data-codemirror-mode="php">
                                        <div class="label">
                                            <strong>Field Functions</strong>
                                            <em>code is php-format with html allowed in some strings</em>
                                        </div>
                                        <?
                                        // Collect the markup for the field functions file
                                        if (!empty($_SESSION['field_functions_markup'][$field_data['field_id']])){
                                            $field_functions_markup = $_SESSION['field_functions_markup'][$field_data['field_id']];
                                            unset($_SESSION['field_functions_markup'][$field_data['field_id']]);
                                        } else {
                                            $template_functions_path = MMRPG_CONFIG_FIELDS_CONTENT_PATH.'.field/functions.php';
                                            $field_functions_path = MMRPG_CONFIG_FIELDS_CONTENT_PATH.$field_data['field_token'].'/functions.php';
                                            $field_functions_markup = file_exists($field_functions_path) ? file_get_contents($field_functions_path) : file_get_contents($template_functions_path);
                                        }
                                        ?>
                                        <textarea class="textarea" name="field_functions_markup" rows="<?= min(20, substr_count($field_functions_markup, PHP_EOL)) ?>"><?= htmlentities(trim($field_functions_markup), ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                        <div class="label examples" style="font-size: 80%; padding-top: 4px;">
                                            <strong>Available Objects</strong>:
                                            <br />
                                            <code style="color: #05a;">$this_battle</code>
                                            &nbsp;&nbsp;<a title="battle data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_BATTLES_CONTENT_PATH).'.battle/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                            <br />
                                            <code style="color: #05a;">$this_field</code>
                                            &nbsp;&nbsp;<a title="field data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_FIELDS_CONTENT_PATH).'.field/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                        </div>
                                    </div>

                                </div>
                            <? } ?>

                        </div>

                        <hr />

                        <? if (!$field_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Published</strong>
                                        <input type="hidden" name="field_flag_published" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="field_flag_published" value="1" <?= !empty($field_data['field_flag_published']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This field is ready to appear on the site</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Complete</strong>
                                        <input type="hidden" name="field_flag_complete" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="field_flag_complete" value="1" <?= !empty($field_data['field_flag_complete']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This field's sprites have been completed</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Hidden</strong>
                                        <input type="hidden" name="field_flag_hidden" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="field_flag_hidden" value="1" <?= !empty($field_data['field_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This field's data should stay hidden</p>
                                </div>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $field_data_is_new ? 'Create Field' : 'Save Changes' ?>" />
                                <? if (!$field_data_is_new && empty($field_data['field_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete Field" data-delete="fields" data-field-id="<?= $field_data['field_id'] ?>" />
                                <? } ?>
                            </div>
                            <? if (!$field_data_is_new){ ?>
                                <?= cms_admin::object_editor_print_git_footer_buttons('fields', $field_data['field_token'], $mmrpg_git_file_arrays); ?>
                            <? } ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_field_data = $field_data;
                if (isset($debug_field_data['field_description2'])){ $debug_field_data['field_description2'] = str_replace(PHP_EOL, '\\n', $debug_field_data['field_description2']); }
                echo('<pre style="display: none;">$field_data = '.(!empty($debug_field_data) ? htmlentities(print_r($debug_field_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                ?>

                <?
                $temp_edit_markup = ob_get_clean();
                echo($temp_edit_markup).PHP_EOL;
            }

        }
        ?>

        <?
        if ($sub_action == 'groups'){
            require('edit-groups_markup.php');
        }
        ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>