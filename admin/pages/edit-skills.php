<? ob_start(); ?>

    <?

    // Pre-check access permissions before continuing
    if (!rpg_user::current_user_has_permission('edit-skills')){
        $form_messages[] = array('error', 'You do not have permission to edit skills!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect an index of file changes and updates via git
    $mmrpg_git_file_arrays = cms_admin::object_editor_get_git_file_arrays(MMRPG_CONFIG_SKILLS_CONTENT_PATH, array(
        'table' => 'mmrpg_index_skills',
        'token' => 'skill_token'
        ));

    // Explode the list of git files into separate array vars
    extract($mmrpg_git_file_arrays);


    /* -- Page Script/Style Dependencies  -- */

    // Require codemirror scripts and styles for this page
    $admin_include_common_styles[] = 'codemirror';
    $admin_include_common_scripts[] = 'codemirror';


    /* -- Form Setup Actions -- */

    // Define a function for exiting a skill edit action
    function exit_skill_edit_action($skill_id = false){
        if ($skill_id !== false){ $location = 'admin/edit-skills/editor/skill_id='.$skill_id; }
        else { $location = 'admin/edit-skills/search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Skills | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['skill_id'])){

        // Collect form data for processing
        $delete_data['skill_id'] = !empty($_GET['skill_id']) && is_numeric($_GET['skill_id']) ? trim($_GET['skill_id']) : '';

        // Let's delete all of this skill's data from the database
        if (!empty($delete_data['skill_id'])){
            $delete_data['skill_token'] = $db->get_value("SELECT skill_token FROM mmrpg_index_skills WHERE skill_id = {$delete_data['skill_id']};", 'skill_token');
            if (!empty($delete_data['skill_token'])){ $files_deleted = cms_admin::object_editor_delete_json_data_file('skill', $delete_data['skill_token'], true); }
            $db->delete('mmrpg_index_skills', array('skill_id' => $delete_data['skill_id'], 'skill_flag_protected' => 0));
            $form_messages[] = array('success', 'The requested skill has been deleted from the database'.(!empty($files_deleted) ? ' and file system' : ''));
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested skill does not exist in the database');
            exit_form_action('error');
        }

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 200;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'skill_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['skill_id'] = !empty($_GET['skill_id']) && is_numeric($_GET['skill_id']) ? trim($_GET['skill_id']) : '';
        $search_data['skill_name'] = !empty($_GET['skill_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['skill_name']) ? trim(strtolower($_GET['skill_name'])) : '';
        $search_data['skill_description'] = !empty($_GET['skill_description']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['skill_description']) ? trim($_GET['skill_description']) : '';
        $search_data['skill_flag_hidden'] = isset($_GET['skill_flag_hidden']) && $_GET['skill_flag_hidden'] !== '' ? (!empty($_GET['skill_flag_hidden']) ? 1 : 0) : '';
        $search_data['skill_flag_complete'] = isset($_GET['skill_flag_complete']) && $_GET['skill_flag_complete'] !== '' ? (!empty($_GET['skill_flag_complete']) ? 1 : 0) : '';
        $search_data['skill_flag_unlockable'] = isset($_GET['skill_flag_unlockable']) && $_GET['skill_flag_unlockable'] !== '' ? (!empty($_GET['skill_flag_unlockable']) ? 1 : 0) : '';
        $search_data['skill_flag_exclusive'] = isset($_GET['skill_flag_exclusive']) && $_GET['skill_flag_exclusive'] !== '' ? (!empty($_GET['skill_flag_exclusive']) ? 1 : 0) : '';
        $search_data['skill_flag_published'] = isset($_GET['skill_flag_published']) && $_GET['skill_flag_published'] !== '' ? (!empty($_GET['skill_flag_published']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_append_git_statuses($search_data, 'skill');

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_skill_fields = rpg_skill::get_index_fields(true, 'skill');
        $search_query = "SELECT
            {$temp_skill_fields}
            FROM mmrpg_index_skills AS skill
            WHERE 1=1
            AND skill_token <> 'skill'
            ";

        // If the skill ID was provided, we can search by exact match
        if (!empty($search_data['skill_id'])){
            $skill_id = $search_data['skill_id'];
            $search_query .= "AND skill_id = {$skill_id} ";
            $search_results_limit = false;
        }

        // Else if the skill name was provided, we can use wildcards
        if (!empty($search_data['skill_name'])){
            $skill_name = $search_data['skill_name'];
            $skill_name = str_replace(array(' ', '*', '%'), '%', $skill_name);
            $skill_name = preg_replace('/%+/', '%', $skill_name);
            $skill_name = '%'.$skill_name.'%';
            $search_query .= "AND (skill_name LIKE '{$skill_name}' OR skill_token LIKE '{$skill_name}') ";
            $search_results_limit = false;
        }

        // Else if the skill flavour was provided, we can use wildcards
        if (!empty($search_data['skill_description'])){
            $skill_description = $search_data['skill_description'];
            $skill_description = str_replace(array(' ', '*', '%'), '%', $skill_description);
            $skill_description = preg_replace('/%+/', '%', $skill_description);
            $skill_description = '%'.$skill_description.'%';
            $search_query .= "AND (
                skill_description LIKE '{$skill_description}'
                OR skill_description2 LIKE '{$skill_description}'
                ) ";
            $search_results_limit = false;
        }

        // If the skill hidden flag was provided
        if ($search_data['skill_flag_hidden'] !== ''){
            $search_query .= "AND skill_flag_hidden = {$search_data['skill_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the skill complete flag was provided
        if ($search_data['skill_flag_complete'] !== ''){
            $search_query .= "AND skill_flag_complete = {$search_data['skill_flag_complete']} ";
            $search_results_limit = false;
        }

        // If the skill published flag was provided
        if ($search_data['skill_flag_published'] !== ''){
            $search_query .= "AND skill_flag_published = {$search_data['skill_flag_published']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "skill_name ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;
        cms_admin::object_index_search_results_filter_git_statuses($search_results, $search_results_count, $search_data, 'skill', $mmrpg_git_file_arrays);

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(skill_id) AS total FROM mmrpg_index_skills WHERE 1=1 AND skill_token <> 'skill';", 'total');

    }

    // If we're in editor mode, we should collect skill info from database
    $skill_data = array();
    $skill_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['skill_id'])
        ){

        // Collect form data for processing
        $editor_data['skill_id'] = !empty($_GET['skill_id']) && is_numeric($_GET['skill_id']) ? trim($_GET['skill_id']) : '';


        /* -- Collect Skill Data -- */

        // Collect skill details from the database
        $temp_skill_skills = rpg_skill::get_index_fields(true);
        if (!empty($editor_data['skill_id'])){
            $skill_data = $db->get_array("SELECT {$temp_skill_skills} FROM mmrpg_index_skills WHERE skill_id = {$editor_data['skill_id']};");
        } else {

            // Generate temp data structure for the new challenge
            $skill_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $skill_data = array(
                'skill_id' => 0,
                'skill_token' => '',
                'skill_name' => '',
                'skill_class' => 'skill',
                'skill_description' => '',
                'skill_description2' => '',
                'skill_flag_hidden' => 0,
                'skill_flag_complete' => 0,
                'skill_flag_published' => 0,
                'skill_flag_protected' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $skill_data[$f] = $v;
                }
            }

        }

        // If skill data could not be found, produce error and exit
        if (empty($skill_data)){ exit_skill_edit_action(); }

        // Collect the skill's name(s) for display
        $skill_name_display = $skill_data['skill_name'];
        if ($skill_data_is_new){ $this_page_tabtitle = 'New Skill | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $skill_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this skill, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-skills'){

            // COLLECT form data from the request and parse out simple rules

            $old_skill_token = !empty($_POST['old_skill_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_skill_token']) ? trim(strtolower($_POST['old_skill_token'])) : '';

            $form_data['skill_id'] = !empty($_POST['skill_id']) && is_numeric($_POST['skill_id']) ? trim($_POST['skill_id']) : 0;
            $form_data['skill_token'] = !empty($_POST['skill_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['skill_token']) ? trim(strtolower($_POST['skill_token'])) : '';
            $form_data['skill_name'] = !empty($_POST['skill_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['skill_name']) ? trim($_POST['skill_name']) : '';
            $form_data['skill_class'] = 'skill'; //!empty($_POST['skill_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['skill_class']) ? trim(strtolower($_POST['skill_class'])) : '';
            $form_data['skill_description'] = !empty($_POST['skill_description']) ? trim(strip_tags($_POST['skill_description'])) : '';
            $form_data['skill_description2'] = !empty($_POST['skill_description2']) ? trim(strip_tags($_POST['skill_description2'])) : '';
            $form_data['skill_parameters'] = !empty($_POST['skill_parameters']) ? trim($_POST['skill_parameters']) : '';

            $form_data['skill_flag_published'] = isset($_POST['skill_flag_published']) && is_numeric($_POST['skill_flag_published']) ? (int)(trim($_POST['skill_flag_published'])) : 0;
            $form_data['skill_flag_complete'] = isset($_POST['skill_flag_complete']) && is_numeric($_POST['skill_flag_complete']) ? (int)(trim($_POST['skill_flag_complete'])) : 0;
            $form_data['skill_flag_hidden'] = isset($_POST['skill_flag_hidden']) && is_numeric($_POST['skill_flag_hidden']) ? (int)(trim($_POST['skill_flag_hidden'])) : 0;

            $form_data['skill_functions_markup'] = !empty($_POST['skill_functions_markup']) ? trim($_POST['skill_functions_markup']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If this is a NEW skill, auto-generate the token when not provided
            if ($skill_data_is_new
                && empty($form_data['skill_token'])
                && !empty($form_data['skill_name'])){
                $auto_token = strtolower($form_data['skill_name']);
                $auto_token = preg_replace('/\s+/', '-', $auto_token);
                $auto_token = preg_replace('/[^-a-z0-9]+/i', '', $auto_token);
                $form_data['skill_token'] = $auto_token;
            }

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$skill_data_is_new && empty($form_data['skill_id'])){ $form_messages[] = array('error', 'Skill ID was not provided'); $form_success = false; }
            if (empty($form_data['skill_token']) || (!$skill_data_is_new && empty($old_skill_token))){ $form_messages[] = array('error', 'Skill Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['skill_name'])){ $form_messages[] = array('error', 'Skill Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['skill_class'])){ $form_messages[] = array('error', 'Skill Kind was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_skill_edit_action($form_data['skill_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$skill_data_is_new && empty($form_data['skill_description'])){ $form_messages[] = array('warning', 'Skill Description (Short) was not provided and may cause issues on the front-end'); }
            if (!$skill_data_is_new && empty($form_data['skill_description2'])){ $form_messages[] = array('warning', 'Skill Description (Full) was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            // Only parse the following fields if NOT new object data
            if (!$skill_data_is_new){

                // Ensure the functions code is VALID PHP SYNTAX and save, otherwise do not save but allow user to fix it
                if (empty($form_data['skill_functions_markup'])){
                    // Functions code is EMPTY and will be ignored
                    $form_messages[] = array('warning', 'Skill functions code was empty and was not saved (reverted to original)');
                } elseif (!cms_admin::is_valid_php_syntax($form_data['skill_functions_markup'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', 'Skill functions code was invalid PHP syntax and was not saved (please fix and try again)');
                    $_SESSION['skill_functions_markup'][$skill_data['skill_id']] = $form_data['skill_functions_markup'];
                } else {
                    // Functions code is OKAY and can be saved
                    $skill_functions_path = MMRPG_CONFIG_SKILLS_CONTENT_PATH.$skill_data['skill_token'].'/functions.php';
                    $old_skill_functions_markup = file_exists($skill_functions_path) ? normalize_file_markup(file_get_contents($skill_functions_path)) : '';
                    $new_skill_functions_markup = normalize_file_markup($form_data['skill_functions_markup']);
                    if (empty($old_skill_functions_markup) || $new_skill_functions_markup !== $old_skill_functions_markup){
                        $f = fopen($skill_functions_path, 'w');
                        fwrite($f, $new_skill_functions_markup);
                        fclose($f);
                        $form_messages[] = array('alert', 'Skill functions file was '.(!empty($old_skill_functions_markup) ? 'updated' : 'created'));
                    }
                }

                // Ensure the parameters are VALID JSON SYNTAX and save, otherwise do not save but allow user to fix it
                if (!empty($form_data['skill_parameters'])
                    && !cms_admin::is_valid_json_syntax($form_data['skill_parameters'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', 'Skill parameters were invalid JSON and were not saved (please fix and try again)');
                    $_SESSION['skill_parameters'][$skill_data['skill_id']] = $form_data['skill_parameters'];
                    unset($form_data['skill_parameters']);
                }

            }
            // Otherwise, if NEW data, pre-populate certain fields
            else {

                $form_data['skill_class'] = 'skill';
                $form_data['skill_description'] = '';
                $form_data['skill_description2'] = '';

                $temp_json_fields = rpg_skill::get_json_index_fields();
                foreach ($temp_json_fields AS $field){ $form_data[$field] = !empty($form_data[$field]) ? json_encode($form_data[$field], JSON_NUMERIC_CHECK) : ''; }

            }

            // Regardless, unset the markup variable so it's not save to the database
            unset($form_data['skill_functions_markup']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            //exit_skill_edit_action($form_data['skill_id']);

            // Make a copy of the update data sans the skill ID
            $update_data = $form_data;
            unset($update_data['skill_id']);

            // If the skill tokens have changed, we must move the entire folder
            $rename_content_path = false;
            if (!$skill_data_is_new
                && $old_skill_token !== $update_data['skill_token']){
                $rename_content_path = true;
                $old_content_path = MMRPG_CONFIG_SKILLS_CONTENT_PATH.$old_skill_token.'/';
                $new_content_path = MMRPG_CONFIG_SKILLS_CONTENT_PATH.$update_data['skill_token'].'/';
                if (file_exists($new_content_path)){
                    $temp_exists = $db->get_value("SELECT skill_id FROM mmrpg_index_skills WHERE skill_token = '{$update_data['skill_token']}';", 'skill_id');
                    if (!$temp_exists){
                        $shell_cmd = 'rm -rf "'.$new_content_path.'"';
                        $shell_output = shell_exec($shell_cmd);
                        //error_log('$shell_output = '.print_r($shell_output, true));
                    } else {
                        $form_messages[] = array('error', 'New skill path '.mmrpg_clean_path($new_content_path).' already exists!');
                        $update_data['skill_token'] = $old_skill_token;
                        $rename_content_path = false;
                    }
                }
            }

            // If this is a new skill we insert, otherwise we update the existing
            if ($skill_data_is_new){

                // Update the main database index with changes to this skill's data
                $update_data['skill_flag_protected'] = 0;
                $insert_results = $db->insert('mmrpg_index_skills', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', 'Skill data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', 'Skill data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_skill_id = $db->get_value("SELECT MAX(skill_id) AS max FROM mmrpg_index_skills;", 'max');
                    $form_data['skill_id'] = $new_skill_id;
                }

            } else {

                // Update the main database index with changes to this skill's data
                $update_results = $db->update('mmrpg_index_skills', $update_data, array('skill_id' => $form_data['skill_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', 'Skill data was updated successfully!'); }
                else { $form_messages[] = array('error', 'Skill data could not be updated...'); }

            }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If the skill tokens have changed, we must move the entire folder
            if ($form_success
                && $rename_content_path === true
                && !empty($old_content_path)
                && !empty($new_content_path)){
                if (file_exists($old_content_path)){
                    $shell_cmd = 'mv "'.$old_content_path.'" "'.$new_content_path.'"';
                    $shell_output = shell_exec($shell_cmd);
                    //error_log('$shell_output = '.print_r($shell_output, true));
                    if (!file_exists($old_content_path) && file_exists($new_content_path)){
                        $path_string = '<strong>'.mmrpg_clean_path($old_content_path).'</strong> &raquo; <strong>'.mmrpg_clean_path($new_content_path).'</strong>';
                        $form_messages[] = array('alert', 'Skill directory renamed! '.$path_string);
                        $db->update('mmrpg_index_skills_groups_tokens', array('skill_token' => $update_data['skill_token']), array('skill_token' => $old_skill_token));
                    } else {
                        $form_messages[] = array('error', 'Unable to rename skill directory!');
                    }
                }
            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($skill_data_is_new){ $skill_data['skill_id'] = $new_skill_id; }
                cms_admin::object_editor_update_json_data_file('skill', array_merge($skill_data, $update_data));
            }

            // We're done processing the form, we can exit
            if (empty($form_data['skill_id'])){ exit_skill_edit_action(false); }
            else { exit_skill_edit_action($form_data['skill_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    // If we're in groups mode, we need to preset vars and then include common file
    $object_group_kind = 'skill';
    $object_group_class = 'skill';
    $object_group_editor_url = 'admin/edit-skills/groups/';
    $object_group_editor_name = 'Skill Groups';
    if ($sub_action == 'groups'){
        require('edit-groups_actions.php');
    }

    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/edit-skills/">Edit Skills</a>
        <? if ($sub_action == 'editor' && !empty($skill_data)): ?>
            &raquo; <a href="admin/edit-skills/editor/skill_id=<?= $skill_data['skill_id'] ?>"><?= !empty($skill_name_display) ? $skill_name_display : 'New Skill' ?></a>
        <? elseif ($sub_action == 'groups'): ?>
            &raquo; <a href="<?= $object_group_editor_url ?>"><?= $object_group_editor_name ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-skills" data-baseurl="admin/edit-skills/" data-object="skill" data-xobject="skills">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Skills</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <? /* <input type="hidden" name="action" value="edit-skills" /> */ ?>
                    <input type="hidden" name="subaction" value="search" />

                    <div class="field halfsize">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="skill_name" placeholder="" value="<?= !empty($search_data['skill_name']) ? htmlentities($search_data['skill_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Description</strong>
                        <input class="textbox" type="text" name="skill_description" placeholder="" value="<?= !empty($search_data['skill_description']) ? htmlentities($search_data['skill_description'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
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
                        $flag_name = 'skill_flag_'.$flag_token;
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
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='admin/edit-skills/';" />
                        <a class="button new" href="<?= 'admin/edit-skills/editor/skill_id=0' ?>">Create New Skill</a>
                        <a class="button groups" href="<?= $object_group_editor_url ?>">Edit <?= $object_group_editor_name ?></a>
                    </div>

                </form>

            </div>

            <? if (!empty($search_results)): ?>

                <!-- SEARCH RESULTS -->

                <div class="results">

                    <table class="list" style="width: 100%;">
                        <colgroup>
                            <col class="id" width="60" />
                            <col class="name" />
                            <col class="description" />
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('skill_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('skill_name', 'Name') ?></th>
                                <th class="description"><?= cms_admin::get_sort_link('skill_description', 'Description') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('skill_flag_published', 'Published') ?></th>
                                <th class="flag complete"><?= cms_admin::get_sort_link('skill_flag_complete', 'Complete') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('skill_flag_hidden', 'Hidden') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head description"></th>
                                <th class="head flag published"></th>
                                <th class="head flag complete"></th>
                                <th class="head flag hidden"></th>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot description"></td>
                                <td class="foot type"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag complete"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            foreach ($search_results AS $key => $skill_data){

                                $skill_id = $skill_data['skill_id'];
                                $skill_token = $skill_data['skill_token'];
                                $skill_name = $skill_data['skill_name'];
                                $skill_description = $skill_data['skill_description'];
                                $skill_flag_published = !empty($skill_data['skill_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $skill_flag_complete = !empty($skill_data['skill_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $skill_flag_hidden = !empty($skill_data['skill_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $skill_edit_url = 'admin/edit-skills/editor/skill_id='.$skill_id;
                                $skill_name_link = '<a class="link" href="'.$skill_edit_url.'">'.$skill_name.'</a>';
                                cms_admin::object_index_links_append_git_statues($skill_name_link, $skill_token, $mmrpg_git_file_arrays);

                                $skill_actions = '';
                                $skill_actions .= '<a class="link edit" href="'.$skill_edit_url.'"><span>edit</span></a>';
                                if (empty($skill_data['skill_flag_protected'])){
                                    $skill_actions .= '<a class="link delete" data-delete="skills" data-skill-id="'.$skill_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$skill_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$skill_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="description"><div class="wrap">'.$skill_description.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$skill_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$skill_flag_complete.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$skill_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$skill_actions.'</div></td>'.PHP_EOL;
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
            && isset($_GET['skill_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= strstr($skill_data['skill_token'], '-subcore') ? str_replace('-subcore', '', $skill_data['skill_token']) : 'none' ?>" data-auto="field-type" data-field-type="skill_type,skill_type2">
                        <span class="title"><?= !empty($skill_name_display) ? 'Edit Skill &quot;'.$skill_name_display.'&quot;' : 'Create New Skill' ?></span>
                        <?

                        // Print out any git-related statues to this header
                        cms_admin::object_editor_header_echo_git_statues($skill_data['skill_token'], $mmrpg_git_file_arrays);

                        // If the skill is published, generate and display a preview link
                        if (!empty($skill_data['skill_flag_published'])){
                            //$preview_link = 'database/skills/';
                            //$preview_link .= $skill_data['skill_token'].'/';
                            //echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            //echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <? if (!$skill_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="skill">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="functions">Functions</a><span></span>
                            <a class="tab" data-tab="spacer">&nbsp;</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit-skills" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="skill">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label">Skill ID</strong>
                                    <input type="hidden" name="skill_id" value="<?= $skill_data['skill_id'] ?>" />
                                    <input class="textbox" type="text" name="skill_id" value="<?= $skill_data['skill_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Skill Token</strong>
                                        <em>avoid changing</em>
                                    </div>
                                    <input type="hidden" name="old_skill_token" value="<?= $skill_data['skill_token'] ?>" />
                                    <input class="textbox" type="text" name="skill_token" value="<?= $skill_data['skill_token'] ?>" maxlength="64" />
                                </div>

                                <div class="field">
                                    <strong class="label">Skill Name</strong>
                                    <input class="textbox" type="text" name="skill_name" value="<?= $skill_data['skill_name'] ?>" maxlength="100" />
                                </div>

                                <? if (!$skill_data_is_new){ ?>

                                    <hr />

                                    <div class="field fullsize">
                                        <div class="label">
                                            <strong>Skill Description (Short)</strong>
                                            <em>tooltip describing skill effect</em>
                                        </div>
                                        <input class="textbox" type="text" name="skill_description" value="<?= htmlentities($skill_data['skill_description'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="256" />
                                    </div>

                                    <div class="field fullsize">
                                        <div class="label">
                                            <strong>Skill Description (Full)</strong>
                                            <em>short paragraph describing skill effect in more detail</em>
                                        </div>
                                        <textarea class="textarea" name="skill_description2" rows="4"><?= htmlentities($skill_data['skill_description2'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <div class="field fullsize">
                                        <?
                                        // Collect the the skill paramaters string from session of data
                                        if (!empty($_SESSION['skill_parameters'][$skill_data['skill_id']])){
                                            $skill_parameters_string = $_SESSION['skill_parameters'][$skill_data['skill_id']];
                                            unset($_SESSION['skill_parameters'][$skill_data['skill_id']]);
                                        } else {
                                            $skill_parameters_string = $skill_data['skill_parameters'];
                                        }
                                        ?>
                                        <div class="label">
                                            <strong>Skill Parameters</strong>
                                            <em>optional defaults parameters in json-format</em>
                                        </div>
                                        <input class="textbox" type="text" name="skill_parameters" value="<?= htmlentities($skill_parameters_string, ENT_QUOTES, 'UTF-8', true) ?>" />
                                    </div>

                                <? } ?>

                            </div>

                            <? if (!$skill_data_is_new){ ?>

                                <div class="panel" data-tab="functions">

                                    <div class="field fullsize codemirror" data-codemirror-mode="php">
                                        <div class="label">
                                            <strong>Skill Functions</strong>
                                            <em>code is php-format with html allowed in some strings</em>
                                        </div>
                                        <?
                                        // Collect the markup for the skill functions file
                                        if (!empty($_SESSION['skill_functions_markup'][$skill_data['skill_id']])){
                                            $skill_functions_markup = $_SESSION['skill_functions_markup'][$skill_data['skill_id']];
                                            unset($_SESSION['skill_functions_markup'][$skill_data['skill_id']]);
                                        } else {
                                            $template_functions_path = MMRPG_CONFIG_SKILLS_CONTENT_PATH.'.skill/functions.php';
                                            $skill_functions_path = MMRPG_CONFIG_SKILLS_CONTENT_PATH.$skill_data['skill_token'].'/functions.php';
                                            $skill_functions_markup = file_exists($skill_functions_path) ? file_get_contents($skill_functions_path) : file_get_contents($template_functions_path);
                                        }
                                        ?>
                                        <textarea class="textarea" name="skill_functions_markup" rows="<?= min(20, substr_count($skill_functions_markup, PHP_EOL)) ?>"><?= htmlentities(trim($skill_functions_markup), ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                        <div class="label examples" style="font-size: 80%; padding-top: 4px;">
                                            <strong>Available Objects</strong>:
                                            <br />
                                            <code style="color: #05a;">$this_battle</code>
                                            &nbsp;&nbsp;<a title="battle data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_BATTLES_CONTENT_PATH).'.battle/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                            <br />
                                            <code style="color: #05a;">$this_field</code>
                                            &nbsp;&nbsp;<a title="field data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_FIELDS_CONTENT_PATH).'.field/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                            <br />
                                            <code style="color: #05a;">$this_skill</code>
                                            &nbsp;/&nbsp;
                                            <code style="color: #05a;">$target_skill</code>
                                            &nbsp;&nbsp;<a title="skill data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_SKILLS_CONTENT_PATH).'.skill/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                        </div>
                                    </div>

                                </div>

                            <? } ?>

                        </div>

                        <hr />

                        <? if (!$skill_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Published</strong>
                                        <input type="hidden" name="skill_flag_published" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="skill_flag_published" value="1" <?= !empty($skill_data['skill_flag_published']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This skill is ready to appear on the site</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Complete</strong>
                                        <input type="hidden" name="skill_flag_complete" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="skill_flag_complete" value="1" <?= !empty($skill_data['skill_flag_complete']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This skill's functionality has been coded</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Hidden</strong>
                                        <input type="hidden" name="skill_flag_hidden" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="skill_flag_hidden" value="1" <?= !empty($skill_data['skill_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This skill's data should stay hidden</p>
                                </div>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $skill_data_is_new ? 'Create Skill' : 'Save Changes' ?>" />
                                <? if (!$skill_data_is_new && empty($skill_data['skill_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete Skill" data-delete="skills" data-skill-id="<?= $skill_data['skill_id'] ?>" />
                                <? } ?>
                            </div>
                            <? if (!$skill_data_is_new){ ?>
                                <?= cms_admin::object_editor_print_git_footer_buttons('skills', $skill_data['skill_token'], $mmrpg_git_file_arrays) ?>
                            <? } ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_skill_data = $skill_data;
                if (isset($debug_skill_data['skill_description2'])){ $debug_skill_data['skill_description2'] = str_replace(PHP_EOL, '\\n', $debug_skill_data['skill_description2']); }
                echo('<pre style="display: none;">$skill_data = '.(!empty($debug_skill_data) ? htmlentities(print_r($debug_skill_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

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