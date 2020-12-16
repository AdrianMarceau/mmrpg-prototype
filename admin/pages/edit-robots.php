<? ob_start(); ?>

    <?

    // Ensure global robot values for this page are set
    if (!isset($this_robot_class)){ exit('$this_robot_class was undefined!'); }
    if (!isset($this_robot_xclass)){ exit('$this_robot_xclass was undefined!'); }
    if (!isset($this_robot_class_name)){ exit('$this_robot_class_name was undefined!'); }
    if (!isset($this_robot_xclass_name)){ exit('$this_robot_xclass_name was undefined!'); }

    // Using the above, generate the oft-used titles, baseurls, etc. for the editor
    $this_robot_class_name_uc = ucwords($this_robot_class_name);
    $this_robot_xclass_name_uc = ucwords($this_robot_xclass_name);
    $this_robot_class_short_name = $this_robot_class !== 'master' ? $this_robot_class : 'robot';
    $this_robot_class_short_name_uc = ucfirst($this_robot_class_short_name);
    $this_robot_page_token = 'edit-'.str_replace(' ', '-', $this_robot_xclass_name);
    $this_robot_page_title = 'Edit '.$this_robot_xclass_name_uc;
    $this_robot_page_baseurl = 'admin/'.$this_robot_page_token.'/';

    // Pre-check access permissions before continuing
    if (!rpg_user::current_user_has_permission($this_robot_page_token)){
        $form_messages[] = array('error', 'You do not have permission to edit '.$this_robot_xclass_name.'!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect indexes for required object types
    $mmrpg_types_index = cms_admin::get_types_index();
    $mmrpg_abilities_index = cms_admin::get_abilities_index();
    $mmrpg_fields_index = cms_admin::get_fields_index();
    $mmrpg_robots_index = cms_admin::get_robots_index();
    $mmrpg_skills_index = cms_admin::get_skills_index();
    $mmrpg_contributors_index = cms_admin::get_contributors_index('robot');
    $mmrpg_sources_index = rpg_game::get_source_index();

    // Collect an index of file changes and updates via git
    $mmrpg_git_file_arrays = cms_admin::object_editor_get_git_file_arrays(MMRPG_CONFIG_ROBOTS_CONTENT_PATH, array(
        'table' => 'mmrpg_index_robots',
        'token' => 'robot_token',
        'extra' => array('robot_class' => $this_robot_class)
        ));

    // Explode the list of git files into separate array vars
    extract($mmrpg_git_file_arrays);


    /* -- Generate Select Option Markup -- */

    // Pre-generate a list of all robots so we can re-use it over and over
    $last_option_group = false;
    $mecha_options_count = 0;
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
        $robot_types = ucwords(implode(' / ', array_values(array_filter(array($robot_info['robot_core'], $robot_info['robot_core2'])))));
        if (empty($robot_types)){ $robot_types = 'Neutral'; }
        $mecha_options_markup[] = '<option value="'.$robot_token.'">'.$robot_name.' ('.$robot_types.')</option>';
        $mecha_options_count++;
    }
    if (!empty($last_option_group)){ $mecha_options_markup[] = '</optgroup>'; }
    $mecha_options_markup = implode(PHP_EOL, $mecha_options_markup);

    // Pre-generate a list of all skills so we can re-use it over and over
    $last_option_group = false;
    $skill_options_markup = array();
    $skill_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_skills_index AS $skill_token => $skill_info){
        $class_group = (!empty($skill_info['skill_group']) ? ucfirst($skill_info['skill_group']) : 'Misc').' Skills';
        if ($last_option_group !== $class_group){
            if (!empty($last_option_group)){ $skill_options_markup[] = '</optgroup>'; }
            $last_option_group = $class_group;
            $skill_options_markup[] = '<optgroup label="'.$class_group.'">';
        }
        $skill_name = !empty($skill_info['skill_name']) ? $skill_info['skill_name'] : '';
        $skill_description = !empty($skill_info['skill_description']) ? $skill_info['skill_description'] : '';
        $skill_description2 = !empty($skill_info['skill_description2']) ? $skill_info['skill_description2'] : '';
        $option_label = $skill_name;
        $option_title = htmlspecialchars($skill_description2, ENT_QUOTES, 'UTF-8', true);
        $skill_options_markup[] = '<option value="'.$skill_token.'" title="'.$option_title.'">'.$option_label.'</option>';
    }
    if (!empty($last_option_group)){ $skill_options_markup[] = '</optgroup>'; }
    $skill_options_count = count($skill_options_markup);
    $skill_options_markup = implode(PHP_EOL, $skill_options_markup);

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


    /* -- Form Setup Actions -- */

    // Define a function for exiting a robot edit action
    function exit_robot_edit_action($robot_id = false){
        global $this_robot_page_baseurl;
        if ($robot_id !== false){ $location = $this_robot_page_baseurl.'editor/robot_id='.$robot_id; }
        else { $location = $this_robot_page_baseurl.'search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit '.$this_robot_xclass_name_uc.' | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['robot_id'])){

        // Collect form data for processing
        $delete_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';

        // Let's delete all of this robot's data from the database
        if (!empty($delete_data['robot_id'])){
            $delete_data['robot_token'] = $db->get_value("SELECT robot_token FROM mmrpg_index_robots WHERE robot_id = {$delete_data['robot_id']};", 'robot_token');
            if (!empty($delete_data['robot_token'])){ $files_deleted = cms_admin::object_editor_delete_json_data_file('robot', $delete_data['robot_token'], true); }
            $db->delete('mmrpg_index_robots', array('robot_id' => $delete_data['robot_id'], 'robot_flag_protected' => 0));
            $form_messages[] = array('success', 'The requested '.$this_robot_class_name.' has been deleted from the database'.(!empty($files_deleted) ? ' and file system' : ''));
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested '.$this_robot_class_name.' does not exist in the database');
            exit_form_action('error');
        }

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 500;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'robot_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';
        $search_data['robot_name'] = !empty($_GET['robot_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['robot_name']) ? trim(strtolower($_GET['robot_name'])) : '';
        $search_data['robot_core'] = !empty($_GET['robot_core']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_core']) ? trim(strtolower($_GET['robot_core'])) : '';
        $search_data['robot_class'] = !empty($_GET['robot_class']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_class']) ? trim(strtolower($_GET['robot_class'])) : '';
        $search_data['robot_skill'] = !empty($_GET['robot_skill']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_skill']) ? trim(strtolower($_GET['robot_skill'])) : '';
        $search_data['robot_flavour'] = !empty($_GET['robot_flavour']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['robot_flavour']) ? trim($_GET['robot_flavour']) : '';
        $search_data['robot_game'] = !empty($_GET['robot_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_game']) ? trim(strtoupper($_GET['robot_game'])) : '';
        $search_data['robot_group'] = !empty($_GET['robot_group']) && preg_match('/[-_0-9a-z\/]+/i', $_GET['robot_group']) ? trim($_GET['robot_group']) : '';
        $search_data['robot_flag_hidden'] = isset($_GET['robot_flag_hidden']) && $_GET['robot_flag_hidden'] !== '' ? (!empty($_GET['robot_flag_hidden']) ? 1 : 0) : '';
        $search_data['robot_flag_complete'] = isset($_GET['robot_flag_complete']) && $_GET['robot_flag_complete'] !== '' ? (!empty($_GET['robot_flag_complete']) ? 1 : 0) : '';
        $search_data['robot_flag_unlockable'] = isset($_GET['robot_flag_unlockable']) && $_GET['robot_flag_unlockable'] !== '' ? (!empty($_GET['robot_flag_unlockable']) ? 1 : 0) : '';
        $search_data['robot_flag_exclusive'] = isset($_GET['robot_flag_exclusive']) && $_GET['robot_flag_exclusive'] !== '' ? (!empty($_GET['robot_flag_exclusive']) ? 1 : 0) : '';
        $search_data['robot_flag_published'] = isset($_GET['robot_flag_published']) && $_GET['robot_flag_published'] !== '' ? (!empty($_GET['robot_flag_published']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_append_git_statuses($search_data, 'robot');

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_robot_fields = rpg_robot::get_index_fields(true, 'robots');
        $search_query = "SELECT
            {$temp_robot_fields},
            groups.group_token AS robot_group,
            tokens.token_order AS robot_order
            FROM mmrpg_index_robots AS robots
            LEFT JOIN mmrpg_index_robots_groups_tokens AS tokens ON tokens.robot_token = robots.robot_token
            LEFT JOIN mmrpg_index_robots_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = robots.robot_class
            WHERE 1=1
            AND robots.robot_token <> 'robot'
            ";

        // If the robot ID was provided, we can search by exact match
        if (!empty($search_data['robot_id'])){
            $robot_id = $search_data['robot_id'];
            $search_query .= "AND robot_id = {$robot_id} ";
            $search_results_limit = false;
        }

        // Else if the robot name was provided, we can use wildcards
        if (!empty($search_data['robot_name'])){
            $robot_name = $search_data['robot_name'];
            $robot_name = str_replace(array(' ', '*', '%'), '%', $robot_name);
            $robot_name = preg_replace('/%+/', '%', $robot_name);
            $robot_name = '%'.$robot_name.'%';
            $search_query .= "AND (robot_name LIKE '{$robot_name}' OR robots.robot_token LIKE '{$robot_name}') ";
            $search_results_limit = false;
        }

        // Else if the robot core was provided, we can use wildcards
        if (!empty($search_data['robot_core'])){
            $robot_core = $search_data['robot_core'];
            if ($robot_core !== 'none'){ $search_query .= "AND (robot_core LIKE '{$robot_core}' OR robot_core2 LIKE '{$robot_core}') "; }
            else { $search_query .= "AND robot_core = '' "; }
            $search_results_limit = false;
        }

        // If the robot class was provided
        if (!empty($search_data['robot_class'])){
            $search_query .= "AND robot_class = '{$search_data['robot_class']}' ";
            $search_results_limit = false;
        } elseif (!empty($this_robot_class)){
            $search_query .= "AND robot_class = '{$this_robot_class}' ";
        }

        // If the robot skill was provided
        if (!empty($search_data['robot_skill'])){
            $search_query .= "AND robot_skill = '{$search_data['robot_skill']}' ";
            $search_results_limit = false;
        }

        // Else if the robot flavour was provided, we can use wildcards
        if (!empty($search_data['robot_flavour'])){
            $robot_flavour = $search_data['robot_flavour'];
            $robot_flavour = str_replace(array(' ', '*', '%'), '%', $robot_flavour);
            $robot_flavour = preg_replace('/%+/', '%', $robot_flavour);
            $robot_flavour = '%'.$robot_flavour.'%';
            $search_query .= "AND (
                robot_description LIKE '{$robot_flavour}'
                OR robot_description2 LIKE '{$robot_flavour}'
                OR robot_quotes_start LIKE '{$robot_flavour}'
                OR robot_quotes_taunt LIKE '{$robot_flavour}'
                OR robot_quotes_victory LIKE '{$robot_flavour}'
                OR robot_quotes_defeat LIKE '{$robot_flavour}'
                ) ";
            $search_results_limit = false;
        }

        // If the robot game was provided
        if (!empty($search_data['robot_game'])){
            $search_query .= "AND robot_game = '{$search_data['robot_game']}' ";
            $search_results_limit = false;
        }

        // If the robot group was provided
        if (!empty($search_data['robot_group'])){
            $search_query .= "AND groups.group_token = '{$search_data['robot_group']}' ";
            $search_results_limit = false;
        }

        // If the robot hidden flag was provided
        if ($search_data['robot_flag_hidden'] !== ''){
            $search_query .= "AND robot_flag_hidden = {$search_data['robot_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the robot complete flag was provided
        if ($search_data['robot_flag_complete'] !== ''){
            $search_query .= "AND robot_flag_complete = {$search_data['robot_flag_complete']} ";
            $search_results_limit = false;
        }

        // If the robot unlockable flag was provided
        if ($search_data['robot_flag_unlockable'] !== ''){
            $search_query .= "AND robot_flag_unlockable = {$search_data['robot_flag_unlockable']} ";
            $search_results_limit = false;
        }

        // If the robot exclusive flag was provided
        if ($search_data['robot_flag_exclusive'] !== ''){
            $search_query .= "AND robot_flag_exclusive = {$search_data['robot_flag_exclusive']} ";
            $search_results_limit = false;
        }

        // If the robot published flag was provided
        if ($search_data['robot_flag_published'] !== ''){
            $search_query .= "AND robot_flag_published = {$search_data['robot_flag_published']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "groups.group_order ASC";
        $order_by[] = "tokens.token_order ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;
        cms_admin::object_index_search_results_filter_git_statuses($search_results, $search_results_count, $search_data, 'robot', $mmrpg_git_file_arrays);

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(robot_id) AS total FROM mmrpg_index_robots WHERE 1=1 AND robot_token <> 'robot' AND robot_class = '{$this_robot_class}';", 'total');

    }

    // If we're in editor mode, we should collect robot info from database
    $robot_data = array();
    $robot_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['robot_id'])
        ){

        // Collect form data for processing
        $editor_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';

        /* -- Collect Robot Data -- */

        // Collect robot details from the database
        $temp_robot_fields = rpg_robot::get_index_fields(true);
        if (!empty($editor_data['robot_id'])){
            $robot_data = $db->get_array("SELECT {$temp_robot_fields} FROM mmrpg_index_robots WHERE robot_id = {$editor_data['robot_id']};");
        } else {

            // Generate temp data structure for the new challenge
            $robot_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $robot_data = array(
                'robot_id' => 0,
                'robot_token' => '',
                'robot_name' => '',
                'robot_class' => $this_robot_class,
                'robot_type' => '',
                'robot_type2' => '',
                'robot_gender' => '',
                'robot_flag_hidden' => 0,
                'robot_flag_complete' => 0,
                'robot_flag_published' => 0,
                'robot_flag_unlockable' => 0,
                'robot_flag_protected' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $robot_data[$f] = $v;
                }
            }

        }

        // If robot data could not be found, produce error and exit
        if (empty($robot_data)){ exit_robot_edit_action(); }

        // Collect the robot's name(s) for display
        $robot_name_display = $robot_data['robot_name'];
        if ($robot_data_is_new){ $this_page_tabtitle = 'New '.$this_robot_class_name_uc.' | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $robot_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this robot, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == $this_robot_page_token){

            // COLLECT form data from the request and parse out simple rules

            $old_robot_token = !empty($_POST['old_robot_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_robot_token']) ? trim(strtolower($_POST['old_robot_token'])) : '';

            $form_data['robot_id'] = !empty($_POST['robot_id']) && is_numeric($_POST['robot_id']) ? trim($_POST['robot_id']) : 0;
            $form_data['robot_token'] = !empty($_POST['robot_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_token']) ? trim(strtolower($_POST['robot_token'])) : '';
            $form_data['robot_name'] = !empty($_POST['robot_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['robot_name']) ? trim($_POST['robot_name']) : '';
            $form_data['robot_class'] = $this_robot_class; //!empty($_POST['robot_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_class']) ? trim(strtolower($_POST['robot_class'])) : '';
            $form_data['robot_core'] = !empty($_POST['robot_core']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_core']) ? trim(strtolower($_POST['robot_core'])) : '';
            $form_data['robot_core2'] = !empty($_POST['robot_core2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_core2']) ? trim(strtolower($_POST['robot_core2'])) : '';
            $form_data['robot_gender'] = !empty($_POST['robot_gender']) && preg_match('/^(male|female|other|none)$/', $_POST['robot_gender']) ? trim(strtolower($_POST['robot_gender'])) : '';

            $form_data['robot_game'] = !empty($_POST['robot_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_game']) ? trim($_POST['robot_game']) : '';
            $form_data['robot_number'] = !empty($_POST['robot_number']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_number']) ? trim($_POST['robot_number']) : '';

            $form_data['robot_field'] = !empty($_POST['robot_field']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_field']) ? trim(strtolower($_POST['robot_field'])) : '';
            $form_data['robot_field2'] = !empty($_POST['robot_field2']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_field2']) ? trim(strtolower($_POST['robot_field2'])) : '';

            $form_data['robot_support'] = !empty($_POST['robot_support']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_support']) ? trim(strtolower($_POST['robot_support'])) : '';

            $form_data['robot_energy'] = !empty($_POST['robot_energy']) && is_numeric($_POST['robot_energy']) ? (int)(trim($_POST['robot_energy'])) : 0;
            $form_data['robot_weapons'] = !empty($_POST['robot_weapons']) && is_numeric($_POST['robot_weapons']) ? (int)(trim($_POST['robot_weapons'])) : 0;
            $form_data['robot_attack'] = !empty($_POST['robot_attack']) && is_numeric($_POST['robot_attack']) ? (int)(trim($_POST['robot_attack'])) : 0;
            $form_data['robot_defense'] = !empty($_POST['robot_defense']) && is_numeric($_POST['robot_defense']) ? (int)(trim($_POST['robot_defense'])) : 0;
            $form_data['robot_speed'] = !empty($_POST['robot_speed']) && is_numeric($_POST['robot_speed']) ? (int)(trim($_POST['robot_speed'])) : 0;

            $form_data['robot_skill'] = !empty($_POST['robot_skill']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_skill']) ? trim(strtolower($_POST['robot_skill'])) : '';
            $form_data['robot_skill_name'] = !empty($form_data['robot_skill']) && !empty($_POST['robot_skill_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['robot_skill_name']) ? trim($_POST['robot_skill_name']) : '';
            $form_data['robot_skill_description'] = !empty($_POST['robot_skill_description']) ? trim(strip_tags($_POST['robot_skill_description'])) : '';
            $form_data['robot_skill_description2'] = !empty($_POST['robot_skill_description2']) ? trim(strip_tags($_POST['robot_skill_description2'])) : '';
            $form_data['robot_skill_parameters'] = !empty($_POST['robot_skill_parameters']) ? trim($_POST['robot_skill_parameters']) : '';

            $form_data['robot_weaknesses'] = !empty($_POST['robot_weaknesses']) && is_array($_POST['robot_weaknesses']) ? array_values(array_unique(array_filter($_POST['robot_weaknesses']))) : array();
            $form_data['robot_resistances'] = !empty($_POST['robot_resistances']) && is_array($_POST['robot_resistances']) ? array_values(array_unique(array_filter($_POST['robot_resistances']))) : array();
            $form_data['robot_affinities'] = !empty($_POST['robot_affinities']) && is_array($_POST['robot_affinities']) ? array_values(array_unique(array_filter($_POST['robot_affinities']))) : array();
            $form_data['robot_immunities'] = !empty($_POST['robot_immunities']) && is_array($_POST['robot_immunities']) ? array_values(array_unique(array_filter($_POST['robot_immunities']))) : array();

            $form_data['robot_description'] = !empty($_POST['robot_description']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['robot_description']) ? trim($_POST['robot_description']) : '';
            $form_data['robot_description2'] = !empty($_POST['robot_description2']) ? trim(strip_tags($_POST['robot_description2'])) : '';

            $form_data['robot_quotes_start'] = !empty($_POST['robot_quotes_start']) ? trim(strip_tags($_POST['robot_quotes_start'])) : '';
            $form_data['robot_quotes_taunt'] = !empty($_POST['robot_quotes_taunt']) ? trim(strip_tags($_POST['robot_quotes_taunt'])) : '';
            $form_data['robot_quotes_victory'] = !empty($_POST['robot_quotes_victory']) ? trim(strip_tags($_POST['robot_quotes_victory'])) : '';
            $form_data['robot_quotes_defeat'] = !empty($_POST['robot_quotes_defeat']) ? trim(strip_tags($_POST['robot_quotes_defeat'])) : '';

            $form_data['robot_abilities_rewards'] = !empty($_POST['robot_abilities_rewards']) ? array_values(array_filter($_POST['robot_abilities_rewards'])) : array();
            $form_data['robot_abilities_compatible'] = !empty($_POST['robot_abilities_compatible']) && is_array($_POST['robot_abilities_compatible']) ? array_values(array_unique(array_filter($_POST['robot_abilities_compatible']))) : array();

            $form_data['robot_image'] = !empty($_POST['robot_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_image']) ? trim(strtolower($_POST['robot_image'])) : '';
            $form_data['robot_image_size'] = !empty($_POST['robot_image_size']) && is_numeric($_POST['robot_image_size']) ? (int)(trim($_POST['robot_image_size'])) : 0;
            $form_data['robot_image_editor'] = !empty($_POST['robot_image_editor']) && is_numeric($_POST['robot_image_editor']) ? (int)(trim($_POST['robot_image_editor'])) : 0;
            $form_data['robot_image_editor2'] = !empty($_POST['robot_image_editor2']) && is_numeric($_POST['robot_image_editor2']) ? (int)(trim($_POST['robot_image_editor2'])) : 0;

            $form_data['robot_flag_published'] = isset($_POST['robot_flag_published']) && is_numeric($_POST['robot_flag_published']) ? (int)(trim($_POST['robot_flag_published'])) : 0;
            $form_data['robot_flag_complete'] = isset($_POST['robot_flag_complete']) && is_numeric($_POST['robot_flag_complete']) ? (int)(trim($_POST['robot_flag_complete'])) : 0;
            $form_data['robot_flag_hidden'] = isset($_POST['robot_flag_hidden']) && is_numeric($_POST['robot_flag_hidden']) ? (int)(trim($_POST['robot_flag_hidden'])) : 0;

            $form_data['robot_flag_unlockable'] = isset($_POST['robot_flag_unlockable']) && is_numeric($_POST['robot_flag_unlockable']) ? (int)(trim($_POST['robot_flag_unlockable'])) : 0;
            $form_data['robot_flag_exclusive'] = isset($_POST['robot_flag_exclusive']) && is_numeric($_POST['robot_flag_exclusive']) ? (int)(trim($_POST['robot_flag_exclusive'])) : 0;

            if ($form_data['robot_core'] != 'copy'){
                $form_data['robot_image_alts'] = !empty($_POST['robot_image_alts']) && is_array($_POST['robot_image_alts']) ? array_filter($_POST['robot_image_alts']) : array();
                $robot_image_alts_new = !empty($_POST['robot_image_alts_new']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_image_alts_new']) ? trim(strtolower($_POST['robot_image_alts_new'])) : '';
            } else {
                $form_data['robot_image_alts'] = array();
                $robot_image_alts_new = '';
            }

            $form_data['robot_functions_markup'] = !empty($_POST['robot_functions_markup']) ? trim($_POST['robot_functions_markup']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'robot_image_alts\']  = '.print_r($_POST['robot_image_alts'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'robot_image_alts_new\']  = '.print_r($_POST['robot_image_alts_new'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If this is a NEW robot, auto-generate the token when not provided
            if ($robot_data_is_new
                && empty($form_data['robot_token'])
                && !empty($form_data['robot_name'])){
                $auto_token = strtolower($form_data['robot_name']);
                $auto_token = preg_replace('/\s+/', '-', $auto_token);
                $auto_token = preg_replace('/[^-a-z0-9]+/i', '', $auto_token);
                $form_data['robot_token'] = $auto_token;
            }

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$robot_data_is_new && empty($form_data['robot_id'])){ $form_messages[] = array('error', $this_robot_class_short_name_uc.' ID was not provided'); $form_success = false; }
            if (empty($form_data['robot_token']) || (!$robot_data_is_new && empty($old_robot_token))){ $form_messages[] = array('error', $this_robot_class_short_name_uc.' Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['robot_name'])){ $form_messages[] = array('error', $this_robot_class_short_name_uc.' Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['robot_class'])){ $form_messages[] = array('error', $this_robot_class_short_name_uc.' Kind was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['robot_core']) || !isset($_POST['robot_core2'])){ $form_messages[] = array('warning', 'Core Types were not provided or were invalid'); $form_success = false; }
            if (empty($form_data['robot_gender'])){ $form_messages[] = array('error', $this_robot_class_short_name_uc.' Gender was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_robot_edit_action($form_data['robot_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$robot_data_is_new && empty($form_data['robot_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            if (!$robot_data_is_new && empty($form_data['robot_number'])){ $form_messages[] = array('warning', 'Serial Number was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if (isset($form_data['robot_core'])){
                // Fix any core ordering problems (like selecting Neutral + anything)
                $cores = array_values(array_filter(array($form_data['robot_core'], $form_data['robot_core2'])));
                $form_data['robot_core'] = isset($cores[0]) ? $cores[0] : '';
                $form_data['robot_core2'] = isset($cores[1]) ? $cores[1] : '';
            }

            // Only parse the following fields if NOT new object data
            if (!$robot_data_is_new){

                if (isset($form_data['robot_weaknesses'])){ $form_data['robot_weaknesses'] = !empty($form_data['robot_weaknesses']) ? json_encode($form_data['robot_weaknesses']) : ''; }
                if (isset($form_data['robot_resistances'])){ $form_data['robot_resistances'] = !empty($form_data['robot_resistances']) ? json_encode($form_data['robot_resistances']) : ''; }
                if (isset($form_data['robot_affinities'])){ $form_data['robot_affinities'] = !empty($form_data['robot_affinities']) ? json_encode($form_data['robot_affinities']) : ''; }
                if (isset($form_data['robot_immunities'])){ $form_data['robot_immunities'] = !empty($form_data['robot_immunities']) ? json_encode($form_data['robot_immunities']) : ''; }

                if (!empty($form_data['robot_abilities_rewards'])){
                    $new_rewards = array();
                    $new_rewards_tokens = array();
                    foreach ($form_data['robot_abilities_rewards'] AS $key => $reward){
                        if (empty($reward) || empty($reward['token'])){ continue; }
                        elseif (in_array($reward['token'], $new_rewards_tokens)){ continue; }
                        if (empty($reward['level'])){ $reward['level'] = 0; }
                        $new_rewards_tokens[] = $reward['token'];
                        $new_rewards[] = $reward;
                    }
                    usort($new_rewards, function($a, $b) use($mmrpg_abilities_index){
                        $ax = $mmrpg_abilities_index[$a['token']];
                        $bx = $mmrpg_abilities_index[$b['token']];
                        if ($a['level'] < $b['level']){ return -1; }
                        elseif ($a['level'] > $b['level']){ return 1; }
                        elseif ($ax['ability_order'] < $bx['ability_order']){ return -1; }
                        elseif ($ax['ability_order'] > $bx['ability_order']){ return 1; }
                        else { return 0; }
                        });
                    $form_data['robot_abilities_rewards'] = $new_rewards;
                }

                if ($form_data['robot_flag_unlockable']){
                    if (!$form_data['robot_flag_published']){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must be published to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (!$form_data['robot_flag_complete']){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must be complete to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif ($form_data['robot_class'] !== 'master'){ $form_messages[] = array('warning', 'Only robot masters can be marked as unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_field']) && empty($form_data['robot_field2'])){ $form_messages[] = array('warning', 'Robot must have battle field to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_description'])){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must have a flavour class to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_quotes_start'])){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must have a start quote to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_quotes_taunt'])){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must have a taunt quote to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_quotes_victory'])){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must have a victory quote to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_quotes_defeat'])){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must have a defeat quote to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                    elseif (empty($form_data['robot_abilities_rewards'])){ $form_messages[] = array('warning', $this_robot_class_short_name_uc.' must have at least one ability to be unlockable'); $form_data['robot_flag_unlockable'] = 0; }
                }


                if (isset($form_data['robot_abilities_rewards'])){ $form_data['robot_abilities_rewards'] = !empty($form_data['robot_abilities_rewards']) ? json_encode($form_data['robot_abilities_rewards'], JSON_NUMERIC_CHECK) : ''; }
                if (isset($form_data['robot_abilities_compatible'])){ $form_data['robot_abilities_compatible'] = !empty($form_data['robot_abilities_compatible']) ? json_encode($form_data['robot_abilities_compatible']) : ''; }

                $empty_image_folders = array();

                if (isset($form_data['robot_image_alts'])){
                    if (!empty($robot_image_alts_new)){
                        $alt_num = $robot_image_alts_new != 'alt' ? (int)(str_replace('alt', '', $robot_image_alts_new)) : 1;
                        $alt_name = ucfirst($robot_image_alts_new);
                        if ($alt_num == 9){ $alt_name = 'Darkness Alt'; }
                        elseif ($alt_num == 3){ $alt_name = 'Weapon Alt'; }
                        $form_data['robot_image_alts'][$robot_image_alts_new] = array(
                            'token' => $robot_image_alts_new,
                            'name' => $form_data['robot_name'].' ('.$alt_name.')',
                            'summons' => ($alt_num * 100),
                            'colour' => ($alt_num == 9 ? 'empty' : 'none')
                            );
                    }
                    $alt_keys = array_keys($form_data['robot_image_alts']);
                    usort($alt_keys, function($a, $b){
                        $a = strstr($a, 'alt') ? (int)(str_replace('alt', '', $a)) : 0;
                        $b = strstr($b, 'alt') ? (int)(str_replace('alt', '', $b)) : 0;
                        if ($a < $b){ return -1; }
                        elseif ($a > $b){ return 1; }
                        else { return 0; }
                        });
                    $new_robot_image_alts = array();
                    foreach ($alt_keys AS $alt_key){
                        $alt_info = $form_data['robot_image_alts'][$alt_key];
                        $alt_info = array_filter($alt_info);
                        $alt_path = ($alt_key != 'base' ? '_'.$alt_key : '');
                        if (!empty($alt_info['delete_images'])){
                            $delete_sprite_path = 'content/robots/'.$robot_data['robot_image'].'/sprites'.$alt_path.'/';
                            $delete_shadow_path = 'content/robots/'.$robot_data['robot_image'].'/shadows'.$alt_path.'/';
                            $empty_image_folders[] = $delete_sprite_path;
                            $empty_image_folders[] = $delete_shadow_path;
                        }
                        if (!empty($alt_info['delete'])){ continue; }
                        elseif ($alt_key == 'base'){ continue; }
                        unset($alt_info['delete_images'], $alt_info['delete']);
                        unset($alt_info['generate_shadows']);
                        $new_robot_image_alts[] = $alt_info;
                    }
                    $form_data['robot_image_alts'] = $new_robot_image_alts;
                    $form_data['robot_image_alts'] = !empty($form_data['robot_image_alts']) ? json_encode($form_data['robot_image_alts'], JSON_NUMERIC_CHECK) : '';
                }
                //$form_messages[] = array('alert', '<pre>$form_data[\'robot_image_alts\']  = '.print_r($form_data['robot_image_alts'] , true).'</pre>');

                if (!empty($empty_image_folders)){
                    //$form_messages[] = array('alert', '<pre>$empty_image_folders = '.print_r($empty_image_folders, true).'</pre>');
                    foreach ($empty_image_folders AS $empty_path_key => $empty_path){

                        // Continue if this folder doesn't exist
                        if (!file_exists(MMRPG_CONFIG_ROOTDIR.$empty_path)){ continue; }

                        // Otherwise, collect directory contents (continue if empty)
                        $empty_files = getDirContents(MMRPG_CONFIG_ROOTDIR.$empty_path);
                        $empty_files = !empty($empty_files) ? array_map(function($s){ return str_replace('\\', '/', $s); }, $empty_files) : array();
                        if (empty($empty_files)){ continue; }
                        //$form_messages[] = array('alert', '<pre>$empty_path_key = '.print_r($empty_path_key, true).' | $empty_path = '.print_r($empty_path, true).' | $empty_files = '.print_r($empty_files, true).'</pre>');

                        // Loop through empty files and delete one by one
                        foreach ($empty_files AS $empty_file_key => $empty_file_path){
                            @unlink($empty_file_path);
                            if (!file_exists($empty_file_path)){ $form_messages[] = array('alert', str_replace(MMRPG_CONFIG_ROOTDIR, '', $empty_file_path).' was deleted!'); }
                            else { $form_messages[] = array('warning', str_replace(MMRPG_CONFIG_ROOTDIR, '', $empty_file_path).' could not be deleted!');  }
                        }

                    }
                }

                // Ensure the functions code is VALID PHP SYNTAX and save, otherwise do not save but allow user to fix it
                if (empty($form_data['robot_functions_markup'])){
                    // Functions code is EMPTY and will be ignored
                    $form_messages[] = array('warning', $this_robot_class_short_name_uc.' functions code was empty and was not saved (reverted to original)');
                } elseif (!cms_admin::is_valid_php_syntax($form_data['robot_functions_markup'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', $this_robot_class_short_name_uc.' functions code was invalid PHP syntax and was not saved (please fix and try again)');
                    $_SESSION['robot_functions_markup'][$robot_data['robot_id']] = $form_data['robot_functions_markup'];
                } else {
                    // Functions code is OKAY and can be saved
                    $robot_functions_path = MMRPG_CONFIG_ROBOTS_CONTENT_PATH.$robot_data['robot_token'].'/functions.php';
                    $old_robot_functions_markup = file_exists($robot_functions_path) ? normalize_file_markup(file_get_contents($robot_functions_path)) : '';
                    $new_robot_functions_markup = normalize_file_markup($form_data['robot_functions_markup']);
                    if (empty($old_robot_functions_markup) || $new_robot_functions_markup !== $old_robot_functions_markup){
                        $f = fopen($robot_functions_path, 'w');
                        fwrite($f, $new_robot_functions_markup);
                        fclose($f);
                        $form_messages[] = array('alert', $this_robot_class_short_name_uc.' functions file was '.(!empty($old_robot_functions_markup) ? 'updated' : 'created'));
                    }
                }

                // Ensure the parameters are VALID JSON SYNTAX and save, otherwise do not save but allow user to fix it
                if (!empty($form_data['robot_skill_parameters'])
                    && !cms_admin::is_valid_json_syntax($form_data['robot_skill_parameters'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', 'Custom skill parameters were invalid JSON and were not saved (please fix and try again)');
                    $_SESSION['robot_skill_parameters'][$robot_data['robot_id']] = $form_data['robot_skill_parameters'];
                    unset($form_data['robot_skill_parameters']);
                }

            }
            // Otherwise, if NEW data, pre-populate certain fields
            else {

                $form_data['robot_abilities_rewards'] = array(array('level' => 0, 'token' => 'buster-shot'));

                if ($this_robot_class === 'mecha'){ $bst = 200; $we = 5; }
                elseif ($this_robot_class === 'master'){ $bst = 400; $we = 10; }
                elseif ($this_robot_class === 'boss'){ $bst = 600; $we = 20; }
                $bst_split = round($bst / 4);
                $form_data['robot_energy'] = $bst_split;
                $form_data['robot_attack'] = $bst_split;
                $form_data['robot_defense'] = $bst_split;
                $form_data['robot_speed'] = $bst_split;
                $form_data['robot_weapons'] = $we;
                if (($bst_split * 4) > $bst){ $form_data['robot_energy'] -= (($bst_split * 4) - $bst); }
                elseif (($bst_split * 4) < $bst){ $form_data['robot_energy'] += ($bst - ($bst_split * 4)); }

                $form_data['robot_game'] = 'MMRPG';
                $form_data['robot_number'] = 'RPG-000';

                $temp_json_fields = rpg_robot::get_json_index_fields();
                foreach ($temp_json_fields AS $field){ $form_data[$field] = !empty($form_data[$field]) ? json_encode($form_data[$field], JSON_NUMERIC_CHECK) : ''; }

            }

            // Regardless, unset the markup variable so it's not save to the database
            unset($form_data['robot_functions_markup']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // Make a copy of the update data sans the robot ID
            $update_data = $form_data;
            unset($update_data['robot_id']);

            // If this is a new robot we insert, otherwise we update the existing
            if ($robot_data_is_new){

                // Update the main database index with changes to this robot's data
                $update_data['robot_flag_protected'] = 0;
                $insert_results = $db->insert('mmrpg_index_robots', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', $this_robot_class_short_name_uc.' data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', $this_robot_class_short_name_uc.' data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_robot_id = $db->get_value("SELECT MAX(robot_id) AS max FROM mmrpg_index_robots;", 'max');
                    $form_data['robot_id'] = $new_robot_id;
                }

            } else {

                // Update the main database index with changes to this robot's data
                $update_results = $db->update('mmrpg_index_robots', $update_data, array('robot_id' => $form_data['robot_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', $this_robot_class_short_name_uc.' data was updated successfully!'); }
                else { $form_messages[] = array('error', $this_robot_class_short_name_uc.' data could not be updated...'); }

            }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($robot_data_is_new){ $robot_data['robot_id'] = $new_robot_id; }
                cms_admin::object_editor_update_json_data_file('robot', array_merge($robot_data, $update_data));
            }

            // If the robot tokens have changed, we must move the entire folder
            if ($form_success
                && !$robot_data_is_new
                && $old_robot_token !== $update_data['robot_token']){
                $old_content_path = MMRPG_CONFIG_ROBOTS_CONTENT_PATH.$old_robot_token.'/';
                $new_content_path = MMRPG_CONFIG_ROBOTS_CONTENT_PATH.$update_data['robot_token'].'/';
                if (rename($old_content_path, $new_content_path)){
                    $path_string = '<strong>'.mmrpg_clean_path($old_content_path).'</strong> &raquo; <strong>'.mmrpg_clean_path($new_content_path).'</strong>';
                    $form_messages[] = array('alert', $this_robot_class_short_name_uc.' directory renamed! '.$path_string);
                } else {
                    $form_messages[] = array('error', 'Unable to rename '.$this_robot_class_name.' directory!');
                }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['robot_id'])){ exit_robot_edit_action(false); }
            else { exit_robot_edit_action($form_data['robot_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    // If we're in groups mode, we need to preset vars and then include common file
    $object_group_kind = 'robot';
    $object_group_class = $this_robot_class;
    $object_group_editor_url = $this_robot_page_baseurl.'groups/';
    $object_group_editor_name = $this_robot_class_name_uc.' Groups';
    if ($sub_action == 'groups'){
        require('edit-groups_actions.php');
    }

    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="<?= $this_robot_page_baseurl ?>">Edit <?= $this_robot_xclass_name_uc ?></a>
        <? if ($sub_action == 'editor' && !empty($robot_data)): ?>
            &raquo; <a href="<?= $this_robot_page_baseurl ?>editor/robot_id=<?= $robot_data['robot_id'] ?>"><?= !empty($robot_name_display) ? $robot_name_display : 'New '.$this_robot_class_name_uc ?></a>
        <? elseif ($sub_action == 'groups'): ?>
            &raquo; <a href="<?= $object_group_editor_url ?>"><?= $object_group_editor_name ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-robots edit-<?= str_replace(' ', '-', $this_robot_xclass_name) ?>" data-baseurl="<?= $this_robot_page_baseurl ?>" data-object="robot" data-xobject="robots">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search <?= $this_robot_xclass_name_uc ?></h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <? /* <input type="hidden" name="action" value="<?= $this_robot_page_token ?>" /> */ ?>
                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="robot_id" value="<?= !empty($search_data['robot_id']) ? $search_data['robot_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="robot_name" placeholder="" value="<?= !empty($search_data['robot_name']) ? htmlentities($search_data['robot_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <? /*
                    <div class="field">
                        <strong class="label">By Class</strong>
                        <select class="select" name="robot_class">
                            <option value=""></option>
                            <option value="mecha"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'mecha' ? ' selected="selected"' : '' ?>>Mecha</option>
                            <option value="master"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'master' ? ' selected="selected"' : '' ?>>Master</option>
                            <option value="boss"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'boss' ? ' selected="selected"' : '' ?>>Boss</option>
                        </select><span></span>
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Type</strong>
                        <select class="select" name="robot_core"><option value=""></option><?
                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                if ($type_info['type_class'] === 'special' && $type_token !== 'none'){ continue; }
                                ?><option value="<?= $type_token ?>"<?= !empty($search_data['robot_core']) && $search_data['robot_core'] === $type_token ? ' selected="selected"' : '' ?>><?= $type_token === 'none' ? 'Neutral' : ucfirst($type_token) ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Skill</strong>
                        <?
                        $current_value = !empty($search_data['robot_skill']) ? $search_data['robot_skill'] : '';
                        $temp_options_markup = $skill_options_markup;
                        $temp_options_markup = str_replace('<option value="', '<option disabled value="', $temp_options_markup);
                        $temp_options_markup = str_replace('<option disabled value=""', '<option value=""', $temp_options_markup);
                        $temp_allowed_options = $db->get_array_list("SELECT DISTINCT (robot_skill) AS skill_token FROM mmrpg_index_robots WHERE robot_class = '{$this_robot_class}' ORDER BY robot_skill ASC;", 'skill_token');
                        $temp_allowed_options = !empty($temp_allowed_options) ? array_keys($temp_allowed_options) : array();
                        foreach ($temp_allowed_options AS $value){ $temp_options_markup = str_replace('<option disabled value="'.$value.'"', '<option value="'.$value.'"', $temp_options_markup); }
                        ?>
                        <select class="select" name="robot_skill">
                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $temp_options_markup) ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Flavour</strong>
                        <input class="textbox" type="text" name="robot_flavour" placeholder="" value="<?= !empty($search_data['robot_flavour']) ? htmlentities($search_data['robot_flavour'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Game</strong>
                        <?
                        $current_value = !empty($search_data['robot_game']) ? $search_data['robot_game'] : '';
                        $temp_options_markup = $source_options_markup;
                        $temp_options_markup = str_replace('<option value="', '<option disabled value="', $temp_options_markup);
                        $temp_options_markup = str_replace('<option disabled value=""', '<option value=""', $temp_options_markup);
                        $temp_allowed_options = $db->get_array_list("SELECT DISTINCT (robot_game) AS game_token FROM mmrpg_index_robots WHERE robot_class = '{$this_robot_class}' ORDER BY robot_game ASC;", 'game_token');
                        $temp_allowed_options = !empty($temp_allowed_options) ? array_keys($temp_allowed_options) : array();
                        foreach ($temp_allowed_options AS $value){ $temp_options_markup = str_replace('<option disabled value="'.$value.'"', '<option value="'.$value.'"', $temp_options_markup); }
                        ?>
                        <select class="select" name="robot_game">
                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $temp_options_markup) ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Group</strong>
                        <select class="select" name="robot_group"><option value=""></option><?
                            $robot_groups_tokens = $db->get_array_list("SELECT group_token FROM mmrpg_index_robots_groups WHERE group_class = '{$this_robot_class}' ORDER BY group_order ASC;");
                            foreach ($robot_groups_tokens AS $group_key => $group_info){
                                $group_token = $group_info['group_token'];
                                ?><option value="<?= $group_token ?>"<?= !empty($search_data['robot_group']) && $search_data['robot_group'] === $group_token ? ' selected="selected"' : '' ?>><?= $group_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field fullsize has5cols flags">
                    <?
                    $flag_names = array(
                        'published' => array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished'),
                        'complete' => array('icon' => 'fas fa-check-circle', 'yes' => 'Complete', 'no' => 'Incomplete'),
                        'unlockable' => array('icon' => 'fas fa-unlock', 'yes' => 'Unlockable', 'no' => 'Locked'),
                        'hidden' => array('icon' => 'fas fa-eye-slash', 'yes' => 'Hidden', 'no' => 'Visible'),
                        'exclusive' => array('icon' => 'fas fa-ghost', 'yes' => 'Exclusive', 'no' => 'Standard')
                        );
                    cms_admin::object_index_flag_names_append_git_statuses($flag_names);
                    foreach ($flag_names AS $flag_token => $flag_info){
                        if (isset($flag_info['break'])){ echo('<div class="break"></div>'); continue; }
                        $flag_name = 'robot_flag_'.$flag_token;
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
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='<?= $this_robot_page_baseurl ?>';" />
                        <a class="button new" href="<?= $this_robot_page_baseurl.'editor/robot_id=0' ?>">Create New <?= ucfirst($this_robot_class_short_name_uc) ?></a>
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
                            <col class="name" width="" />
                            <col class="type" width="<?= $this_robot_class === 'boss' ? 120 : 100 ?>" />
                            <col class="game" width="100" />
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag unlockable" width="80" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('robot_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('robot_name', 'Name') ?></th>
                                <th class="type"><?= cms_admin::get_sort_link('robot_core', 'Core(s)') ?></th>
                                <th class="game"><?= cms_admin::get_sort_link('robot_game', 'Game') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('robot_flag_published', 'Published') ?></th>
                                <th class="flag complete"><?= cms_admin::get_sort_link('robot_flag_complete', 'Complete') ?></th>
                                <th class="flag unlockable"><?= cms_admin::get_sort_link('robot_flag_unlockable', 'Unlockable') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('robot_flag_hidden', 'Hidden') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head type"></th>
                                <th class="head game"></th>
                                <th class="head flag published"></th>
                                <th class="head flag complete"></th>
                                <th class="head flag unlockable"></th>
                                <th class="head flag hidden"></th>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot name"></td>
                                <td class="foot type"></td>
                                <td class="foot game"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag complete"></td>
                                <td class="foot flag unlockable"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            $temp_class_colours = array(
                                'mecha' => array('speed', '<i class="fas fa-ghost"></i>'),
                                'master' => array('defense', '<i class="fas fa-robot"></i>'),
                                'boss' => array('space', '<i class="fas fa-skull"></i>')
                                );
                            foreach ($search_results AS $key => $robot_data){

                                $robot_id = $robot_data['robot_id'];
                                $robot_token = $robot_data['robot_token'];
                                $robot_name = $robot_data['robot_name'];
                                $robot_class = ucfirst($robot_data['robot_class']);
                                $robot_class_span = '<span class="type_span type_'.$temp_class_colours[$robot_data['robot_class']][0].'">'.$temp_class_colours[$robot_data['robot_class']][1].' '.$robot_class.'</span>';
                                $robot_core = !empty($robot_data['robot_core']) ? ucfirst($robot_data['robot_core']) : 'Neutral';
                                $robot_core_span = '<span class="type_span type_'.(!empty($robot_data['robot_core']) ? $robot_data['robot_core'] : 'none').'">'.$robot_core.'</span>';
                                if (!empty($robot_data['robot_core'])
                                    && !empty($robot_data['robot_core2'])){
                                    $robot_core .= ' / '.ucfirst($robot_data['robot_core2']);
                                    $robot_core_span = '<span class="type_span type_'.$robot_data['robot_core'].'_'.$robot_data['robot_core2'].'">'.ucwords($robot_data['robot_core'].' / '.$robot_data['robot_core2']).'</span>';
                                }
                                $robot_game = ucfirst($robot_data['robot_game']);
                                $robot_game_span = '<span class="type_span type_none">'.$robot_game.'</span>';
                                $robot_flag_published = !empty($robot_data['robot_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $robot_flag_complete = !empty($robot_data['robot_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $robot_flag_unlockable = !empty($robot_data['robot_flag_unlockable']) ? '<i class="fas fa-unlock"></i>' : '-';
                                $robot_flag_hidden = !empty($robot_data['robot_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $robot_edit_url = $this_robot_page_baseurl.'editor/robot_id='.$robot_id;
                                $robot_name_link = '<a class="link" href="'.$robot_edit_url.'">'.$robot_name.'</a>';
                                cms_admin::object_index_links_append_git_statues($robot_name_link, $robot_token, $mmrpg_git_file_arrays);

                                $robot_actions = '';
                                $robot_actions .= '<a class="link edit" href="'.$robot_edit_url.'"><span>edit</span></a>';
                                if (empty($robot_data['robot_flag_protected'])){
                                    $robot_actions .= '<a class="link delete" data-delete="robots" data-robot-id="'.$robot_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$robot_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$robot_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="type"><div class="wrap">'.$robot_core_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="game"><div class="wrap">'.$robot_game_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$robot_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$robot_flag_complete.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag unlockable"><div>'.$robot_flag_unlockable.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$robot_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$robot_actions.'</div></td>'.PHP_EOL;
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
            && isset($_GET['robot_id'])
            ){

            // Collect global abilities so we can skip them
            $global_ability_tokens = rpg_ability::get_global_abilities();

            // Pre-generate a list of all abilities so we can re-use it over and over
            $last_option_group = false;
            $ability_options_markup = array();
            $ability_options_markup[] = '<option value="">-</option>';

            $levelup_last_option_group = false;
            $levelup_ability_options_markup = array();
            $levelup_ability_options_markup[] = '<option value="">-</option>';

            foreach ($mmrpg_abilities_index AS $ability_token => $ability_info){
                if ($ability_info['ability_class'] === 'mecha' && $robot_data['robot_class'] !== 'mecha'){ continue; }
                elseif ($ability_info['ability_class'] === 'boss' && $robot_data['robot_class'] !== 'boss'){ continue; }

                $option_group = $robot_data['robot_class'] !== 'master' ? ucfirst($ability_info['ability_class']).' | ' : '';
                $option_group .= str_replace('/', ' | ', $ability_info['ability_group']);
                $ability_name = $ability_info['ability_name'];
                $ability_types = ucwords(implode(' / ', array_values(array_filter(array($ability_info['ability_type'], $ability_info['ability_type2'])))));
                if (empty($ability_types)){ $ability_types = 'Neutral'; }
                $option_markup = '<option value="'.$ability_token.'">'.$ability_name.' ('.$ability_types.')</option>';

                if ($last_option_group !== $option_group){
                    if (!empty($last_option_group)){ $ability_options_markup[] = '</optgroup>'; }
                    $last_option_group = $option_group;
                    $ability_options_markup[] = '<optgroup label="'.$option_group.'">';
                }
                $ability_options_markup[] = $option_markup;

                $levelup_compatible = false;
                if ($robot_data['robot_class'] === 'boss' && $ability_info['ability_class'] !== 'mecha'){ $levelup_compatible = true; }
                elseif ($ability_info['ability_class'] === $robot_data['robot_class']){ $levelup_compatible = true; }
                if ($levelup_compatible){
                    if ($levelup_last_option_group !== $option_group){
                        if (!empty($levelup_last_option_group)){ $levelup_ability_options_markup[] = '</optgroup>'; }
                        $levelup_last_option_group = $option_group;
                        $levelup_ability_options_markup[] = '<optgroup label="'.$option_group.'">';
                    }
                    $levelup_ability_options_markup[] = $option_markup;
                }

            }

            if (!empty($last_option_group)){ $ability_options_markup[] = '</optgroup>'; }
            $ability_options_markup = implode(PHP_EOL, $ability_options_markup);

            if (!empty($levelup_last_option_group)){ $levelup_ability_options_markup[] = '</optgroup>'; }
            $levelup_ability_options_markup = implode(PHP_EOL, $levelup_ability_options_markup);

            // Pre-generate a list of all fields so we can re-use it over and over
            $field_options_group = false;
            $field_options_count = 0;
            $field_options_markup = array();
            $field_options_markup[] = '<option value="">-</option>';
            foreach ($mmrpg_fields_index AS $field_token => $field_info){
                if ($field_token === 'intro-field'){ continue; }
                elseif (empty($field_info['field_flag_complete'])){ continue; }
                elseif ($field_info['field_class'] === 'system'){ continue; }
                $class_group = str_replace('/', ' | ', $field_info['field_group']);
                if ($class_group != $field_options_group){
                    if (!empty($field_options_group)){ $field_options_markup[] = '</optgroup>'; }
                    $field_options_group = $class_group;
                    $field_options_markup[] = '<optgroup label="'.ucfirst($class_group).'">';
                }
                $field_name = $field_info['field_name'];
                $field_types = ucwords(implode(' / ', array_values(array_filter(array($field_info['field_type'], $field_info['field_type2'])))));
                if (empty($field_types)){ $field_types = 'Neutral'; }
                $field_options_markup[] = '<option value="'.$field_token.'">'.$field_name.' ('.$field_types.')</option>';
                $field_options_count++;
            }
            if (!empty($field_options_group)){ $field_options_markup[] = '</optgroup>'; }
            $field_options_markup = implode(PHP_EOL, $field_options_markup);

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($robot_data['robot_core']) ? $robot_data['robot_core'].(!empty($robot_data['robot_core2']) ? '_'.$robot_data['robot_core2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="robot_core,robot_core2">
                        <span class="title"><?= !empty($robot_name_display) ? 'Edit '.$this_robot_class_short_name_uc.' &quot;'.$robot_name_display.'&quot;' : 'Create New '.$this_robot_class_short_name_uc ?></span>
                        <?

                        // Print out any git-related statues to this header
                        cms_admin::object_editor_header_echo_git_statues($robot_data['robot_token'], $mmrpg_git_file_arrays);

                        // If the robot is published, generate and display a preview link
                        if (!empty($robot_data['robot_flag_published'])){
                            $preview_link = 'database/';
                            if ($robot_data['robot_class'] === 'master'){ $preview_link .= 'robots/'; }
                            elseif ($robot_data['robot_class'] === 'mecha'){ $preview_link .= 'mechas/'; }
                            elseif ($robot_data['robot_class'] === 'boss'){ $preview_link .= 'bosses/'; }
                            $preview_link .= $robot_data['robot_token'].'/';
                            echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <? if (!$robot_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="robot">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="stats">Stats</a><span></span>
                            <a class="tab" data-tab="flavour">Flavour</a><span></span>
                            <a class="tab" data-tab="abilities">Abilities</a><span></span>
                            <a class="tab" data-tab="sprites">Sprites</a><span></span>
                            <a class="tab" data-tab="functions">Functions</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="<?= $this_robot_page_token ?>" />
                        <input type="hidden" name="subaction" value="editor" />

                        <input type="hidden" name="robot_class" value="<?= $this_robot_class ?>" />

                        <div class="editor-panels" data-tabgroup="robot">

                            <div class="panel active" data-tab="basic">

                                <div class="field <?= $robot_data_is_new ? 'halfsize' : '' ?>">
                                    <strong class="label"><?= $this_robot_class_short_name_uc ?> ID</strong>
                                    <input type="hidden" name="robot_id" value="<?= $robot_data['robot_id'] ?>" />
                                    <input class="textbox" type="text" name="robot_id" value="<?= $robot_data['robot_id'] ?>" disabled="disabled" />
                                </div>

                                <? if (!$robot_data_is_new){ ?>
                                    <div class="field">
                                        <div class="label">
                                            <strong><?= $this_robot_class_short_name_uc ?> Token</strong>
                                            <em>avoid changing</em>
                                        </div>
                                        <input type="hidden" name="old_robot_token" value="<?= $robot_data['robot_token'] ?>" />
                                        <input class="textbox" type="text" name="robot_token" value="<?= $robot_data['robot_token'] ?>" maxlength="64" />
                                    </div>
                                <? } ?>

                                <div class="field <?= $robot_data_is_new ? 'halfsize' : '' ?>">
                                    <strong class="label"><?= $this_robot_class_short_name_uc ?> Name</strong>
                                    <input class="textbox" type="text" name="robot_name" value="<?= $robot_data['robot_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field has2cols <?= $robot_data_is_new ? 'halfsize' : '' ?>">
                                    <strong class="label">
                                        Core Type(s)
                                        <span class="type_span type_<?= (!empty($robot_data['robot_core']) ? $robot_data['robot_core'].(!empty($robot_data['robot_core2']) ? '_'.$robot_data['robot_core2'] : '') : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="robot_core,robot_core2">&nbsp;</span>
                                    </strong>
                                    <div class="subfield">
                                        <select class="select" name="robot_core">
                                            <option value=""<?= empty($robot_data['robot_core']) ? ' selected="selected"' : '' ?>>Neutral</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($robot_data['robot_core']) && $robot_data['robot_core'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <select class="select" name="robot_core2">
                                            <option value=""<?= empty($robot_data['robot_core2']) ? ' selected="selected"' : '' ?>>-</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($robot_data['robot_core2']) && $robot_data['robot_core2'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                </div>

                                <div class="field <?= $robot_data_is_new ? 'halfsize' : '' ?>">
                                    <strong class="label">Gender</strong>
                                    <div class="subfield">
                                        <select class="select" name="robot_gender">
                                            <option value="none" <?= empty($robot_data['robot_gender']) || $robot_data['robot_gender'] == 'none' ? 'selected="selected"' : '' ?>>None</option>
                                            <option value="other" <?= $robot_data['robot_gender'] == 'other' ? 'selected="selected"' : '' ?>>Other</option>
                                            <option value="male" <?= $robot_data['robot_gender'] == 'male' ? 'selected="selected"' : '' ?>>Male</option>
                                            <option value="female" <?= $robot_data['robot_gender'] == 'female' ? 'selected="selected"' : '' ?>>Female</option>
                                        </select><span></span>
                                    </div>
                                </div>

                                <? if (!$robot_data_is_new){ ?>

                                    <hr />

                                    <div class="field">
                                        <strong class="label">Source Game</strong>
                                        <? $current_value = !empty($robot_data['robot_game']) ? $robot_data['robot_game'] : ''; ?>
                                        <select class="select" name="robot_game">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $source_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field">
                                        <strong class="label">Serial Number</strong>
                                        <input class="textbox" type="text" name="robot_number" value="<?= $robot_data['robot_number'] ?>" maxlength="64" />
                                    </div>

                                    <hr />

                                    <div class="field">
                                        <strong class="label">Home Field</strong>
                                        <? $current_value = !empty($robot_data['robot_field']) ? $robot_data['robot_field'] : ''; ?>
                                        <select class="select" name="robot_field">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $field_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field">
                                        <strong class="label">Echo Field</strong>
                                        <? $current_value = !empty($robot_data['robot_field2']) ? $robot_data['robot_field2'] : ''; ?>
                                        <select class="select" name="robot_field2">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $field_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                    <? if ($this_robot_class !== 'mecha'){
                                        ?>
                                        <div class="field disabled">
                                            <strong class="label">Support Mecha</strong>
                                            <? $current_value = !empty($robot_data['robot_support']) ? $robot_data['robot_support'] : ''; ?>
                                            <select class="select" name="robot_support">
                                                <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $mecha_options_markup) ?>
                                            </select><span></span>
                                        </div>
                                        <?
                                    } ?>

                                <? } ?>

                            </div>

                            <? if (!$robot_data_is_new){ ?>

                                <div class="panel" data-tab="stats">

                                    <div class="field fullsize" style="min-height: 0;">
                                        <strong class="label">Robot Stats</strong>
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label"><span class="type_span type_energy">Energy</span> <em>LE</em></strong>
                                        <input class="textbox" type="number" name="robot_energy" value="<?= $robot_data['robot_energy'] ?>" maxlength="8" />
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label"><span class="type_span type_attack">Attack</span> <em>AT</em></strong>
                                        <input class="textbox" type="number" name="robot_attack" value="<?= $robot_data['robot_attack'] ?>" maxlength="8" />
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label"><span class="type_span type_defense">Defense</span> <em>DF</em></strong>
                                        <input class="textbox" type="number" name="robot_defense" value="<?= $robot_data['robot_defense'] ?>" maxlength="8" />
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label"><span class="type_span type_speed">Speed</span> <em>SP</em></strong>
                                        <input class="textbox" type="number" name="robot_speed" value="<?= $robot_data['robot_speed'] ?>" maxlength="8" />
                                    </div>

                                    <div class="field halfsize">
                                        <strong class="label"><span class="type_span type_weapons">Weapons</span> <em>WE</em></strong>
                                        <input class="textbox" type="number" name="robot_weapons" value="<?= $robot_data['robot_weapons'] ?>" maxlength="8" />
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong><span class="type_span type_cutter">Base Stat Total</span> <em>LE + AT + DF + SP</em></strong>
                                        </div>
                                        <? $bst_value = $robot_data['robot_energy'] + $robot_data['robot_attack'] + $robot_data['robot_defense'] + $robot_data['robot_speed']; ?>
                                        <input class="textbox disabled" type="text" name="robot_bst" value="<?= $bst_value ?>" maxlength="8" disabled="disabled" data-auto="field-sum" data-field-sum="robot_energy,robot_attack,robot_defense,robot_speed" />
                                    </div>

                                    <hr />

                                    <?
                                    $robot_type_matchups = array('weaknesses', 'resistances', 'affinities', 'immunities');
                                    foreach ($robot_type_matchups AS $matchup_key => $matchup_token){
                                        $matchup_list = $robot_data['robot_'.$matchup_token];
                                        $matchup_list = !empty($matchup_list) ? json_decode($matchup_list, true) : array();
                                        ?>
                                        <div class="field fullsize has4cols">
                                            <strong class="label">
                                                <?= $this_robot_class_short_name_uc ?> <?= ucfirst($matchup_token) ?>
                                            </strong>
                                            <? for ($i = 0; $i < 4; $i++){ ?>
                                                <div class="subfield">
                                                    <span class="type_span type_<?= !empty($matchup_list[$i]) ? $matchup_list[$i] : '' ?> swatch floatright hidenone" data-auto="field-type" data-field-type="robot_<?= $matchup_token ?>[<?= $i ?>]">&nbsp;</span>
                                                    <select class="select" name="robot_<?= $matchup_token ?>[<?= $i ?>]">
                                                        <option value=""<?= empty($matchup_list[$i]) ? ' selected="selected"' : '' ?>>-</option>
                                                        <?
                                                        foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                            if ($type_info['type_class'] === 'special'){ continue; }
                                                            $label = $type_info['type_name'];
                                                            if (!empty($matchup_list[$i]) && $matchup_list[$i] === $type_token){ $selected = 'selected="selected"'; }
                                                            else { $selected = ''; }
                                                            echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                                        }
                                                        ?>
                                                    </select><span></span>
                                                </div>
                                            <? } ?>
                                        </div>
                                        <?
                                    }
                                    ?>

                                    <? if ($this_robot_class !== 'mecha'){
                                        ?>

                                        <hr />

                                        <div class="field halfsize">
                                            <strong class="label"><?= $this_robot_class_short_name_uc ?> Skill</strong>
                                            <? $current_value = !empty($robot_data['robot_skill']) ? $robot_data['robot_skill'] : ''; ?>
                                            <select class="select" name="robot_skill">
                                                <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $skill_options_markup) ?>
                                            </select><span></span>
                                        </div>

                                        <div class="field halfsize">
                                            <div class="label">
                                                <strong>Custom Skill Name</strong>
                                                <em>optional alias of default name</em>
                                            </div>
                                            <input class="textbox" type="text" name="robot_skill_name" value="<?= htmlentities($robot_data['robot_skill_name'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="100" />
                                        </div>

                                        <? /*
                                        <div class="field fullsize">
                                            <div class="label">
                                                <strong>Custom Skill Description (Short)</strong>
                                                <em>optional customized version of default short description</em>
                                            </div>
                                            <input class="textbox" type="text" name="robot_skill_description" value="<?= htmlentities($robot_data['robot_skill_description'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="256" />
                                        </div>

                                        <div class="field fullsize">
                                            <div class="label">
                                                <strong>Custom Skill Description (Full)</strong>
                                                <em>optional customized version of default long description</em>
                                            </div>
                                            <textarea class="textarea" name="robot_skill_description2" rows="4"><?= htmlentities($robot_data['robot_skill_description2'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                        </div>
                                        */ ?>

                                        <div class="field fullsize">
                                            <?
                                            // Collect the the skill paramaters string from session of data
                                            if (!empty($_SESSION['robot_skill_parameters'][$robot_data['robot_id']])){
                                                $skill_parameters_string = $_SESSION['robot_skill_parameters'][$robot_data['robot_id']];
                                                unset($_SESSION['robot_skill_parameters'][$robot_data['robot_id']]);
                                            } else {
                                                $skill_parameters_string = $robot_data['robot_skill_parameters'];
                                            }
                                            ?>
                                            <div class="label">
                                                <strong>Custom Skill Parameters</strong>
                                                <em>optional customized parameters for skill in json-format</em>
                                            </div>
                                            <input class="textbox" type="text" name="robot_skill_parameters" value="<?= htmlentities($skill_parameters_string, ENT_QUOTES, 'UTF-8', true) ?>" />
                                        </div>

                                        <?
                                    } ?>

                                </div>

                                <div class="panel" data-tab="flavour">

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong><?= $this_robot_class_short_name_uc ?> Class</strong>
                                            <em>three word classification</em>
                                        </div>
                                        <input class="textbox" type="text" name="robot_description" value="<?= htmlentities($robot_data['robot_description'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="32" />
                                    </div>

                                    <div class="field fullsize">
                                        <div class="label">
                                            <strong><?= $this_robot_class_short_name_uc ?> Description</strong>
                                            <em>short paragraph about robot's design, personality, background, etc.</em>
                                        </div>
                                        <textarea class="textarea" name="robot_description2" rows="10"><?= htmlentities($robot_data['robot_description2'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <hr />

                                    <?
                                    $robot_quote_kinds = array('start', 'taunt', 'victory', 'defeat');
                                    foreach ($robot_quote_kinds AS $kind_key => $kind_token){
                                        ?>
                                        <div class="field halfsize">
                                            <div class="label">
                                                <strong><?= ucfirst($kind_token) ?> Quote</strong>
                                            </div>
                                            <input class="textbox" type="text" name="robot_quotes_<?= $kind_token ?>" value="<?= htmlentities($robot_data['robot_quotes_'.$kind_token], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="256" />
                                        </div>
                                        <?
                                    }
                                    ?>

                                    <div class="field fullsize" style="min-height: 0; margin-bottom: 0; padding-bottom: 0;">
                                        <div class="label">
                                            <em class="nowrap" style="margin-left: 0;">(!) You can use <strong>{this_player}</strong>, <strong>{this_robot}</strong>, <strong>{target_player}</strong>, and <strong>{target_robot}</strong> variables for dynamic text</em>
                                        </div>
                                    </div>

                                </div>

                                <div class="panel" data-tab="abilities">

                                    <div class="field fullsize multirow">
                                        <strong class="label">
                                            Level-Up Abilities
                                            <em>Only hero and support robots require level-up, others should unlock all at start</em>
                                        </strong>
                                        <?
                                        $current_ability_list = !empty($robot_data['robot_abilities_rewards']) ? json_decode($robot_data['robot_abilities_rewards'], true) : array();
                                        $select_limit = max(8, count($current_ability_list));
                                        $select_limit += 2;
                                        for ($i = 0; $i < $select_limit; $i++){
                                            $current_value = isset($current_ability_list[$i]) ? $current_ability_list[$i] : array();
                                            $current_value_level = !empty($current_value) ? $current_value['level'] : '';
                                            $current_value_token = !empty($current_value) ? $current_value['token'] : '';
                                            ?>
                                            <div class="subfield levelup">
                                                <input class="textarea" type="number" name="robot_abilities_rewards[<?= $i ?>][level]" value="<?= $current_value_level ?>" maxlength="3" placeholder="0" />
                                                <select class="select" name="robot_abilities_rewards[<?= $i ?>][token]">
                                                    <?= str_replace('value="'.$current_value_token.'"', 'value="'.$current_value_token.'" selected="selected"', $levelup_ability_options_markup) ?>
                                                </select><span></span>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>

                                    <hr />

                                    <div class="field fullsize has4cols multirow">
                                        <strong class="label">
                                            Compatible Abilities
                                            <em>Excluding level-up abilities and <u title="<?= implode(', ', $global_ability_tokens) ?>">global ones</u> available to all robots by default</em>
                                        </strong>
                                        <?
                                        $current_ability_list = !empty($robot_data['robot_abilities_compatible']) ? json_decode($robot_data['robot_abilities_compatible'], true) : array();
                                        $current_ability_list = array_values(array_filter($current_ability_list, function($token) use($global_ability_tokens){ return !in_array($token, $global_ability_tokens); }));
                                        $select_limit = max(12, count($current_ability_list));
                                        $select_limit += 4 - ($select_limit % 4);
                                        $select_limit += 4;
                                        for ($i = 0; $i < $select_limit; $i++){
                                            $current_value = isset($current_ability_list[$i]) ? $current_ability_list[$i] : '';
                                            ?>
                                            <div class="subfield">
                                                <select class="select" name="robot_abilities_compatible[<?= $i ?>]">
                                                    <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $ability_options_markup) ?>
                                                </select><span></span>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>

                                </div>

                                <div class="panel" data-tab="sprites">

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

                                    <? $placeholder_folder = $robot_data['robot_class'] != 'master' ? $robot_data['robot_class'] : 'robot'; ?>
                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Path</strong>
                                            <em>base image path for sprites</em>
                                        </div>
                                        <select class="select" name="robot_image">
                                            <option value="<?= $placeholder_folder ?>" <?= $robot_data['robot_image'] == $placeholder_folder ? 'selected="selected"' : '' ?>>-</option>
                                            <option value="<?= $robot_data['robot_token'] ?>" <?= $robot_data['robot_image'] == $robot_data['robot_token'] ? 'selected="selected"' : '' ?>>content/robots/<?= $robot_data['robot_token'] ?>/</option>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Size</strong>
                                            <em>base frame size for each sprite</em>
                                        </div>
                                        <select class="select" name="robot_image_size">
                                            <? if ($robot_data['robot_image'] == $placeholder_folder){ ?>
                                                <option value="<?= $robot_data['robot_image_size'] ?>" selected="selected">-</option>
                                                <option value="40">40x40</option>
                                                <option value="80">80x80</option>
                                                <option disabled="disabled" value="160">160x160</option>
                                            <? } else { ?>
                                                <option value="40" <?= $robot_data['robot_image_size'] == 40 ? 'selected="selected"' : '' ?>>40x40</option>
                                                <option value="80" <?= $robot_data['robot_image_size'] == 80 ? 'selected="selected"' : '' ?>>80x80</option>
                                                <option disabled="disabled" value="160" <?= $robot_data['robot_image_size'] == 160 ? 'selected="selected"' : '' ?>>160x160</option>
                                            <? } ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Editor #1</strong>
                                            <em>user who edited or created this sprite</em>
                                        </div>
                                        <? if ($robot_data['robot_image'] != $placeholder_folder){ ?>
                                            <select class="select" name="robot_image_editor">
                                                <?= str_replace('value="'.$robot_data['robot_image_editor'].'"', 'value="'.$robot_data['robot_image_editor'].'" selected="selected"', $contributor_options_markup) ?>
                                            </select><span></span>
                                        <? } else { ?>
                                            <input type="hidden" name="robot_image_editor" value="<?= $robot_data['robot_image_editor'] ?>" />
                                            <input class="textbox" type="text" name="robot_image_editor" value="-" disabled="disabled" />
                                        <? } ?>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Editor #2</strong>
                                            <em>another user who collaborated on this sprite</em>
                                        </div>
                                        <? if ($robot_data['robot_image'] != $placeholder_folder){ ?>
                                            <select class="select" name="robot_image_editor2">
                                                <?= str_replace('value="'.$robot_data['robot_image_editor2'].'"', 'value="'.$robot_data['robot_image_editor2'].'" selected="selected"', $contributor_options_markup) ?>
                                            </select><span></span>
                                        <? } else { ?>
                                            <input type="hidden" name="robot_image_editor2" value="<?= $robot_data['robot_image_editor2'] ?>" />
                                            <input class="textbox" type="text" name="robot_image_editor2" value="-" disabled="disabled" />
                                        <? } ?>
                                    </div>

                                    <?

                                    // Decompress existing image alts pulled from the database
                                    $robot_image_alts = !empty($robot_data['robot_image_alts']) ? json_decode($robot_data['robot_image_alts'], true) : array();

                                    // Collect the alt tokens from all defined alts so far
                                    $robot_image_alts_tokens = array();
                                    foreach ($robot_image_alts AS $alt){ if (!empty($alt['token'])){ $robot_image_alts_tokens[] = $alt['token'];  } }

                                    // Define a variable to toggle allowance of new alt creation
                                    $has_elemental_alts = $robot_data['robot_core'] == 'copy' ? true : false;
                                    $allow_new_alt_creation = !$has_elemental_alts ? true : false;

                                    // Only proceed if all required sprite fields are set
                                    if (!empty($robot_data['robot_image'])
                                        && !in_array($robot_data['robot_image'], array('robot', 'master', 'boss', 'mecha'))
                                        && !empty($robot_data['robot_image_size'])){

                                        echo('<hr />'.PHP_EOL);

                                        // Define the base sprite and shadow paths for this robot given its image token
                                        $base_sprite_path = 'content/robots/'.$robot_data['robot_image'].'/sprites/';
                                        $base_shadow_path = 'content/robots/'.$robot_data['robot_image'].'/shadows/';

                                        // Define the alts we'll be looping through for this robot
                                        $temp_alts_array = array();
                                        $temp_alts_array[] = array('token' => '', 'name' => $robot_data['robot_name'], 'summons' => 0);

                                        // Append predefined alts automatically, based on the robot image alt array
                                        if (!empty($robot_data['robot_image_alts'])){
                                            $temp_alts_array = array_merge($temp_alts_array, $robot_image_alts);
                                        }

                                        // Otherwise, if this is a copy robot, append based on all the types in the index
                                        if ($has_elemental_alts){
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if (empty($type_token) || $type_token == 'none' || $type_token == 'copy' || $type_info['type_class'] == 'special'){ continue; }
                                                $temp_alts_array[] = array('token' => $type_token, 'name' => $robot_data['robot_name'].' ('.ucfirst($type_token).' Core)', 'summons' => 0, 'colour' => $type_token);
                                            }
                                        }

                                        // Otherwise, if this robot has multiple sheets, add them as alt options
                                        if (!empty($robot_data['robot_image_sheets'])){
                                            for ($i = 2; $i <= $robot_data['robot_image_sheets']; $i++){
                                                $temp_alts_array[] = array('sheet' => $i, 'name' => $robot_data['robot_name'].' (Sheet #'.$i.')', 'summons' => 0);
                                            }
                                        }

                                        // Loop through the defined alts for this robot and display image lists
                                        if (!empty($temp_alts_array)){
                                            foreach ($temp_alts_array AS $alt_key => $alt_info){

                                                $is_base_sprite = empty($alt_info['token']) ? true : false;
                                                $alt_token = $is_base_sprite ? 'base' : $alt_info['token'];

                                                $alt_file_path = rtrim($base_sprite_path, '/').(!$is_base_sprite ? '_'.$alt_info['token'] : '').'/';
                                                $alt_file_dir = MMRPG_CONFIG_ROOTDIR.$alt_file_path;
                                                $alt_files_existing = getDirContents($alt_file_dir);

                                                $alt_shadow_path = rtrim($base_shadow_path, '/').(!$is_base_sprite ? '_'.$alt_info['token'] : '').'/';
                                                $alt_shadow_dir = MMRPG_CONFIG_ROOTDIR.$alt_shadow_path;
                                                $alt_shadows_existing = getDirContents($alt_shadow_dir);

                                                if (!empty($alt_files_existing)){ $alt_files_existing = array_map(function($s)use($alt_file_dir){ return str_replace($alt_file_dir, '', str_replace('\\', '/', $s)); }, $alt_files_existing); }
                                                if (!empty($alt_shadows_existing)){ $alt_shadows_existing = array_map(function($s)use($alt_shadow_dir){ return str_replace($alt_shadow_dir, '', str_replace('\\', '/', $s)); }, $alt_shadows_existing); }

                                                //echo('<pre>$alt_files_existing = '.(!empty($alt_files_existing) ? htmlentities(print_r($alt_files_existing, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
                                                //echo('<pre>$alt_shadows_existing = '.(!empty($alt_shadows_existing) ? htmlentities(print_r($alt_shadows_existing, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                                                ?>

                                                <?= ($alt_key > 0) ? '<hr />' : '' ?>

                                                <div class="field fullsize" style="margin-bottom: 0; min-height: 0;">
                                                    <strong class="label">
                                                        <? if ($is_base_sprite){ ?>
                                                            Base Sprite Sheets
                                                            <em>Main sprites used for robot. Zoom and shadow sprites are auto-generated.</em>
                                                        <? } else { ?>
                                                            <?= ucfirst($alt_token).' Sprite Sheets'  ?>
                                                            <em>Sprites used for robot's <strong><?= $alt_token ?></strong> skin. Zoom and shadow sprites are auto-generated.</em>
                                                        <? } ?>
                                                    </strong>
                                                </div>
                                                <? if (!$is_base_sprite){ ?>
                                                    <input class="hidden" type="hidden" name="robot_image_alts[<?= $alt_token ?>][token]" value="<?= $alt_info['token'] ?>" maxlength="64" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                    <div class="field">
                                                        <div class="label"><strong>Name</strong></div>
                                                        <input class="textbox" type="text" name="robot_image_alts[<?= $alt_token ?>][name]" value="<?= $alt_info['name'] ?>" maxlength="64" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                    </div>
                                                    <div class="field">
                                                        <div class="label"><strong>Summons</strong></div>
                                                        <input class="textbox" type="number" name="robot_image_alts[<?= $alt_token ?>][summons]" value="<?= $alt_info['summons'] ?>" maxlength="3" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                    </div>
                                                    <div class="field">
                                                        <div class="label">
                                                            <strong>Colour</strong>
                                                            <span class="type_span type_<?= (!empty($alt_info['colour']) ? $alt_info['colour'] : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="robot_image_alts[<?= $alt_token ?>][colour]">&nbsp;</span>
                                                        </div>
                                                        <select class="select" name="robot_image_alts[<?= $alt_token ?>][colour]" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?>>
                                                            <option value=""<?= empty($alt_info['colour']) ? ' selected="selected"' : '' ?>>-</option>
                                                            <?
                                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                                //if ($type_info['type_class'] === 'special'){ continue; }
                                                                $label = $type_info['type_name'];
                                                                if (!empty($alt_info['colour']) && $alt_info['colour'] === $type_token){ $selected = 'selected="selected"'; }
                                                                else { $selected = ''; }
                                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                                            }
                                                            ?>
                                                        </select><span></span>
                                                    </div>
                                                <? } ?>

                                                <div class="field fullsize has2cols widecols multirow sprites has-filebars">
                                                    <?
                                                    $sheet_groups = array('sprites', 'shadows');
                                                    $sheet_kinds = array('mug', 'sprite');
                                                    $sheet_sizes = array($robot_data['robot_image_size'], $robot_data['robot_image_size'] * 2);
                                                    $sheet_directions = array('left', 'right');
                                                    $num_frames = count(explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX));
                                                    foreach ($sheet_groups AS $group_key => $group){
                                                        if ($group == 'sprites'){ $this_alt_path = $alt_file_path; }
                                                        elseif ($group == 'shadows'){ $this_alt_path = $alt_shadow_path; }
                                                        foreach ($sheet_sizes AS $size_key => $size){
                                                            $sheet_height = $size;
                                                            $files_are_automatic = false;
                                                            if ($group == 'shadows' || $size_key != 0){ $files_are_automatic = true; }
                                                            //if ($size_key > 0){ $files_are_automatic = true; }
                                                            $subfield_class = 'subfield';
                                                            if ($files_are_automatic){ $subfield_class .= ' auto-generated'; }
                                                            $subfield_style = '';
                                                            if ($size_key == 0){ $subfield_style = 'clear: left; '; }
                                                            if (!empty($subfield_style)){ $subfield_style = ' style="'.trim($subfield_style).'"'; }
                                                            $subfield_name = $group.' @ '.(100 + ($size_key * 100)).'%';
                                                            echo('<div class="'.$subfield_class.'"'.$subfield_style.' data-group="'.$group.'" data-size="'.$size.'">'.PHP_EOL);
                                                                echo('<strong class="sublabel" style="font-size: 90%;">'.$subfield_name.'</strong>'.PHP_EOL);
                                                                if ($files_are_automatic){ echo('<span class="sublabel" style="font-size: 90%; color: #969696;">(auto-generated)</span>'.PHP_EOL); }
                                                                echo('<br />'.PHP_EOL);
                                                                echo('<ul class="files">'.PHP_EOL);
                                                                foreach ($sheet_kinds AS $kind_key => $kind){
                                                                    $sheet_width = $kind != 'mug' ? ($size * $num_frames) : $size;
                                                                    foreach ($sheet_directions AS $direction_key => $direction){
                                                                        $file_name = $kind.'_'.$direction.'_'.$size.'x'.$size.'.png';
                                                                        $file_href = MMRPG_CONFIG_ROOTURL.$this_alt_path.$file_name;
                                                                        if ($group == 'sprites'){ $file_exists = in_array($file_name, $alt_files_existing) ? true : false; }
                                                                        elseif ($group == 'shadows'){ $file_exists = in_array($file_name, $alt_shadows_existing) ? true : false; }
                                                                        $file_is_unused = false;
                                                                        //if ($group == 'shadows' && ($kind == 'mug' || $size_key == 0)){ $file_is_unused = true; }
                                                                        //if ($group == 'shadows' && $kind == 'mug'){ $file_is_unused = true; }
                                                                        $file_is_optional = $group == 'shadows' && !$is_base_sprite ? true : false;
                                                                        echo('<li>');
                                                                            echo('<div class="filebar'.($file_is_unused ? ' unused' : '').($file_is_optional ? ' optional' : '').'" data-auto="file-bar" data-file-path="'.$this_alt_path.'" data-file-name="'.$file_name.'" data-file-kind="image/png" data-file-width="'.$sheet_width.'" data-file-height="'.$sheet_height.'" data-file-extras="auto-zoom-x2,auto-shadows">');
                                                                                echo($file_exists ? '<a class="link view" href="'.$file_href.'?'.time().'" target="_blank" data-href="'.$file_href.'">'.$group.'/'.$file_name.'</a>' : '<a class="link view disabled" target="_blank" data-href="'.$file_href.'">'.$group.'/'.$file_name.'</a>');
                                                                                echo('<span class="info size">'.$sheet_width.'w &times; '.$sheet_height.'h</span>');
                                                                                echo($file_exists ? '<span class="info status good">&check;</span>' : '<span class="info status bad">&cross;</span>');
                                                                                if (!$files_are_automatic){
                                                                                    echo('<a class="action delete'.(!$file_exists ? ' disabled' : '').'" data-action="delete" data-file-hash="'.md5('delete/'.$this_alt_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">Delete</a>');
                                                                                    echo('<a class="action upload'.($file_exists ? ' disabled' : '').'" data-action="upload" data-file-hash="'.md5('upload/'.$this_alt_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">');
                                                                                        echo('<span class="text">Upload</span>');
                                                                                        echo('<input class="input" type="file" name="file_info" value=""'.($file_exists ? ' disabled="disabled"' : '').' />');
                                                                                    echo('</a>');
                                                                                }
                                                                            echo('</div>');
                                                                            /* echo('<div class="preview">');
                                                                                echo('<img class="image" src="'.$file_href.'" alt="'.$file_name.'" />');
                                                                            echo('</div>'); */
                                                                        echo('</li>'.PHP_EOL);
                                                                    }
                                                                }
                                                                echo('</ul>'.PHP_EOL);
                                                            echo('</div>'.PHP_EOL);
                                                        }
                                                    }
                                                    ?>

                                                </div>

                                                <div class="options" style="margin-top: -5px; padding-top: 0;">

                                                    <? if ($is_base_sprite){ ?>

                                                            <div class="field checkwrap rfloat fullsize">
                                                                <label class="label">
                                                                    <strong style="color: #da1616;">Delete Base Images?</strong>
                                                                    <input type="hidden" name="robot_image_alts[<?= $alt_token ?>][delete_images]" value="0" checked="checked" />
                                                                    <input class="checkbox" type="checkbox" name="robot_image_alts[<?= $alt_token ?>][delete_images]" value="1" />
                                                                </label>
                                                                <p class="subtext" style="color: #da1616;">Empty <strong>base</strong> image folder and remove all sprites/shadows</p>
                                                            </div>

                                                    <? } else { ?>

                                                            <div class="field checkwrap rfloat">
                                                                <label class="label">
                                                                    <strong style="color: #262626;">Auto-Generate Shadows?</strong>
                                                                    <input class="checkbox" type="checkbox" name="robot_image_alts[<?= $alt_token ?>][generate_shadows]" value="1" <?= !empty($alt_shadows_existing) ? 'checked="checked"' : '' ?> />
                                                                </label>
                                                                <p class="subtext" style="color: #262626;">Only generate alt shadows if silhouette differs from base</p>
                                                            </div>

                                                            <div class="field checkwrap rfloat fullsize">
                                                                <label class="label">
                                                                    <strong style="color: #da1616;">Delete <?= ucfirst($alt_token) ?> Images?</strong>
                                                                    <input type="hidden" name="robot_image_alts[<?= $alt_token ?>][delete_images]" value="0" checked="checked" />
                                                                    <input class="checkbox" type="checkbox" name="robot_image_alts[<?= $alt_token ?>][delete_images]" value="1" />
                                                                </label>
                                                                <p class="subtext" style="color: #da1616;">Empty the <strong><?= $alt_token ?></strong> image folder and remove all sprites/shadows</p>
                                                            </div>

                                                            <? if (!$has_elemental_alts){ ?>

                                                                    <div class="field checkwrap rfloat fullsize">
                                                                        <label class="label">
                                                                            <strong style="color: #da1616;">Delete <?= ucfirst($alt_token) ?> Data?</strong>
                                                                            <input type="hidden" name="robot_image_alts[<?= $alt_token ?>][delete]" value="0" checked="checked" />
                                                                            <input class="checkbox" type="checkbox" name="robot_image_alts[<?= $alt_token ?>][delete]" value="1" />
                                                                        </label>
                                                                        <p class="subtext" style="color: #da1616;">Remove <strong><?= $alt_token ?></strong> from the list (images will not be deleted)</p>
                                                                    </div>

                                                            <? } ?>

                                                    <? } ?>

                                                </div>

                                                <?

                                            }
                                        }

                                        //$base_sprite_list = getDirContents(MMRPG_CONFIG_ROOTDIR.$base_sprite_path);
                                        //echo('<pre>$base_sprite_path = '.print_r($base_sprite_path, true).'</pre>');
                                        //echo('<pre>$base_sprite_list = '.(!empty($base_sprite_list) ? htmlentities(print_r($base_sprite_list, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
                                        //echo('<pre>$temp_alts_array = '.(!empty($temp_alts_array) ? htmlentities(print_r($temp_alts_array, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                                        // Only if we're allowed to create new alts for this robot
                                        if ($allow_new_alt_creation){
                                            echo('<hr />'.PHP_EOL);

                                            ?>
                                            <div class="field halfsize">
                                                <div class="label">
                                                    <strong>Add Another Alt</strong>
                                                    <em>select the alt you want to add and then save</em>
                                                </div>
                                                <select class="select" name="robot_image_alts_new">
                                                    <option value="">-</option>
                                                    <?
                                                    $alt_limit = 10;
                                                    if ($alt_limit < count($robot_image_alts)){ $alt_limit = count($robot_image_alts) + 1; }
                                                    foreach ($robot_image_alts AS $info){ if (!empty($info['token'])){
                                                        $num = (int)(str_replace('alt', '', $info['token']));
                                                        if ($alt_limit < $num){ $alt_limit = $num + 1; }
                                                        } }
                                                    for ($i = 1; $i <= $alt_limit; $i++){
                                                        $alt_token = 'alt'.($i > 1 ? $i : '');
                                                        ?>
                                                        <option value="<?= $alt_token ?>"<?= in_array($alt_token, $robot_image_alts_tokens) ? ' disabled="disabled"' : '' ?>>
                                                            <?= $robot_data['robot_name'] ?>
                                                            (<?= ucfirst($alt_token) ?> / <?
                                                                if ($i == 9){
                                                                    echo('Darkness');
                                                                } elseif ($i == 3){
                                                                    echo('Weapon');
                                                                } elseif ($i < 9){
                                                                    echo('Standard');
                                                                } elseif ($i > 9){
                                                                    echo('Custom');
                                                                } ?>)
                                                        </option>
                                                    <? } ?>
                                                </select><span></span>
                                            </div>
                                            <?
                                        }

                                    }

                                    ?>

                                </div>

                                <div class="panel" data-tab="functions">

                                    <div class="field fullsize codemirror" data-codemirror-mode="php">
                                        <div class="label">
                                            <strong><?= $this_robot_class_short_name_uc ?> Functions</strong>
                                            <em>code is php-format with html allowed in some strings</em>
                                        </div>
                                        <?
                                        // Collect the markup for the robot functions file
                                        if (!empty($_SESSION['robot_functions_markup'][$robot_data['robot_id']])){
                                            $robot_functions_markup = $_SESSION['robot_functions_markup'][$robot_data['robot_id']];
                                            unset($_SESSION['robot_functions_markup'][$robot_data['robot_id']]);
                                        } else {
                                            $template_functions_path = MMRPG_CONFIG_ROBOTS_CONTENT_PATH.'.robot/functions.php';
                                            $robot_functions_path = MMRPG_CONFIG_ROBOTS_CONTENT_PATH.$robot_data['robot_token'].'/functions.php';
                                            $robot_functions_markup = file_exists($robot_functions_path) ? file_get_contents($robot_functions_path) : file_get_contents($template_functions_path);
                                        }
                                        ?>
                                        <textarea class="textarea" name="robot_functions_markup" rows="<?= min(20, substr_count($robot_functions_markup, PHP_EOL)) ?>"><?= htmlentities(trim($robot_functions_markup), ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                        <div class="label examples" style="font-size: 80%; padding-top: 4px;">
                                            <strong>Available Objects</strong>:
                                            <br />
                                            <code style="color: #05a;">$this_battle</code>
                                            &nbsp;&nbsp;<a title="battle data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_BATTLES_CONTENT_PATH).'.battle/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                            <br />
                                            <code style="color: #05a;">$this_field</code>
                                            &nbsp;&nbsp;<a title="field data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_FIELDS_CONTENT_PATH).'.field/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                            <br />
                                            <code style="color: #05a;">$this_player</code>
                                            &nbsp;/&nbsp;
                                            <code style="color: #05a;">$target_player</code>
                                            &nbsp;&nbsp;<a title="player data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_PLAYERS_CONTENT_PATH).'.player/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                            <br />
                                            <code style="color: #05a;">$this_robot</code>
                                            &nbsp;/&nbsp;
                                            <code style="color: #05a;">$target_robot</code>
                                            &nbsp;&nbsp;<a title="robot data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_ROBOTS_CONTENT_PATH).'.robot/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                        </div>
                                        <? if ($this_robot_class !== 'master'){ ?>
                                            <div class="label examples" style=" margin: 0 auto 10px; font-size: 80%;">
                                                <strong>Important Note</strong>:<br />
                                                <code style="color: #cc0000;">Even though this is a <?= $this_robot_class ?>, it is still referred to as a 'robot' in the code!</code><br />
                                                <code style="color: #cc0000;">(Use "robot_id" instead of "<?= $this_robot_class ?>_id", "robot_name" instead of "<?= $this_robot_class ?>_name", etc.)</code>
                                            </div>
                                        <? } ?>
                                    </div>

                                </div>

                            <? } ?>

                        </div>

                        <hr />

                        <? if (!$robot_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Published</strong>
                                        <input type="hidden" name="robot_flag_published" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="robot_flag_published" value="1" <?= !empty($robot_data['robot_flag_published']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_robot_class_short_name ?> is ready to appear on the site</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Complete</strong>
                                        <input type="hidden" name="robot_flag_complete" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="robot_flag_complete" value="1" <?= !empty($robot_data['robot_flag_complete']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_robot_class_short_name ?>'s sprites have been completed</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Hidden</strong>
                                        <input type="hidden" name="robot_flag_hidden" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="robot_flag_hidden" value="1" <?= !empty($robot_data['robot_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_robot_class_short_name ?>'s data should stay hidden</p>
                                </div>

                                <? if (!empty($robot_data['robot_flag_published'])
                                    && !empty($robot_data['robot_flag_complete'])
                                    && $robot_data['robot_class'] == 'master'){ ?>

                                    <div style="clear: both; padding-top: 20px;">

                                        <div class="field checkwrap">
                                            <label class="label">
                                                <strong>Unlockable</strong>
                                                <input type="hidden" name="robot_flag_unlockable" value="0" checked="checked" />
                                                <input class="checkbox" type="checkbox" name="robot_flag_unlockable" value="1" <?= !empty($robot_data['robot_flag_unlockable']) ? 'checked="checked"' : '' ?> />
                                            </label>
                                            <p class="subtext">This <?= $this_robot_class_short_name ?> is ready to be used in the game</p>
                                        </div>

                                        <div class="field checkwrap">
                                            <label class="label">
                                                <strong>Exclusive</strong>
                                                <input type="hidden" name="robot_flag_exclusive" value="0" checked="checked" />
                                                <input class="checkbox" type="checkbox" name="robot_flag_exclusive" value="1" <?= !empty($robot_data['robot_flag_exclusive']) ? 'checked="checked"' : '' ?> />
                                            </label>
                                            <p class="subtext">Exclude from shop &amp; procedural missions</p>
                                        </div>

                                    </div>

                                <? } ?>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $robot_data_is_new ? 'Create '.$this_robot_class_short_name_uc : 'Save Changes' ?>" />
                                <? if (!$robot_data_is_new && empty($robot_data['robot_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete <?= $this_robot_class_short_name_uc ?>" data-delete="robots" data-robot-id="<?= $robot_data['robot_id'] ?>" />
                                <? } ?>
                            </div>
                            <? if (!$robot_data_is_new){ ?>
                                <?= cms_admin::object_editor_print_git_footer_buttons('robots/'.$this_robot_xclass, $robot_data['robot_token'], $mmrpg_git_file_arrays); ?>
                            <? } ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_robot_data = $robot_data;
                if (isset($debug_robot_data['robot_description2'])){ $debug_robot_data['robot_description2'] = str_replace(PHP_EOL, '\\n', $debug_robot_data['robot_description2']); }
                echo('<pre style="display: none;">$robot_data = '.(!empty($debug_robot_data) ? htmlentities(print_r($debug_robot_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

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