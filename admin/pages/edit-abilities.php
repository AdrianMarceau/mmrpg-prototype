<? ob_start(); ?>

    <?

    // Ensure global ability values for this page are set
    if (!isset($this_ability_class)){ exit('$this_ability_class was undefined!'); }
    if (!isset($this_ability_xclass)){ exit('$this_ability_xclass was undefined!'); }
    if (!isset($this_ability_class_name)){ exit('$this_ability_class_name was undefined!'); }
    if (!isset($this_ability_xclass_name)){ exit('$this_ability_xclass_name was undefined!'); }

    // Using the above, generate the oft-used titles, baseurls, etc. for the editor
    $this_ability_class_name_uc = ucwords($this_ability_class_name);
    $this_ability_xclass_name_uc = ucwords($this_ability_xclass_name);
    $this_ability_class_short_name = $this_ability_class !== 'master' ? $this_ability_class : 'ability';
    $this_ability_class_short_name_uc = ucfirst($this_ability_class_short_name);
    $this_ability_page_token = 'edit-'.str_replace(' ', '-', $this_ability_xclass_name);
    $this_ability_page_title = 'Edit '.$this_ability_xclass_name_uc;
    $this_ability_page_baseurl = 'admin/'.$this_ability_page_token.'/';

    /*
    echo('<pre>$this_ability_class = '.print_r($this_ability_class, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_xclass = '.print_r($this_ability_xclass, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_class_name = '.print_r($this_ability_class_name, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_xclass_name = '.print_r($this_ability_xclass_name, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_class_name_uc = '.print_r($this_ability_class_name_uc, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_xclass_name_uc = '.print_r($this_ability_xclass_name_uc, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_class_short_name = '.print_r($this_ability_class_short_name, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_class_short_name_uc = '.print_r($this_ability_class_short_name_uc, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_page_token = '.print_r($this_ability_page_token, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_page_title = '.print_r($this_ability_page_title, true).'</pre>'.PHP_EOL);
    echo('<pre>$this_ability_page_baseurl = '.print_r($this_ability_page_baseurl, true).'</pre>'.PHP_EOL);
    exit();
    */

    // Pre-check access permissions before continuing
    if (!rpg_user::current_user_has_permission($this_ability_page_token)){
        $form_messages[] = array('error', 'You do not have permission to edit '.$this_ability_xclass_name.'!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect indexes for required object types
    $mmrpg_types_index = cms_admin::get_types_index();
    $mmrpg_robots_index = cms_admin::get_robots_index();
    $mmrpg_contributors_index = cms_admin::get_contributors_index('ability');
    $mmrpg_sources_index = rpg_game::get_source_index();

    // Collect an index of file changes and updates via git
    $mmrpg_git_file_arrays = cms_admin::object_editor_get_git_file_arrays(MMRPG_CONFIG_ABILITIES_CONTENT_PATH, array(
        'table' => 'mmrpg_index_abilities',
        'token' => 'ability_token',
        'extra' => array('ability_class' => $this_ability_class)
        ));

    // Explode the list of git files into separate array vars
    extract($mmrpg_git_file_arrays);


    /* -- Generate Select Option Markup -- */

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

    // Define a function for exiting a ability edit action
    function exit_ability_edit_action($ability_id = false){
        global $this_ability_page_baseurl;
        if ($ability_id !== false){ $location = $this_ability_page_baseurl.'editor/ability_id='.$ability_id; }
        else { $location = $this_ability_page_baseurl.'search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit '.$this_ability_xclass_name_uc.' | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['ability_id'])){

        // Collect form data for processing
        $delete_data['ability_id'] = !empty($_GET['ability_id']) && is_numeric($_GET['ability_id']) ? trim($_GET['ability_id']) : '';

        // Let's delete all of this ability's data from the database
        if (!empty($delete_data['ability_id'])){
            $delete_data['ability_token'] = $db->get_value("SELECT ability_token FROM mmrpg_index_abilities WHERE ability_id = {$delete_data['ability_id']};", 'ability_token');
            if (!empty($delete_data['ability_token'])){ $files_deleted = cms_admin::object_editor_delete_json_data_file('ability', $delete_data['ability_token'], true); }
            $db->delete('mmrpg_index_abilities', array('ability_id' => $delete_data['ability_id'], 'ability_flag_protected' => 0));
            $form_messages[] = array('success', 'The requested ability has been deleted from the database'.(!empty($files_deleted) ? ' and file system' : ''));
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested ability does not exist in the database');
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
        $sort_data = array('name' => 'ability_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['ability_id'] = !empty($_GET['ability_id']) && is_numeric($_GET['ability_id']) ? trim($_GET['ability_id']) : '';
        $search_data['ability_name'] = !empty($_GET['ability_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['ability_name']) ? trim(strtolower($_GET['ability_name'])) : '';
        $search_data['ability_type'] = !empty($_GET['ability_type']) && preg_match('/[-_0-9a-z]+/i', $_GET['ability_type']) ? trim(strtolower($_GET['ability_type'])) : '';
        $search_data['ability_class'] = !empty($_GET['ability_class']) && preg_match('/[-_0-9a-z]+/i', $_GET['ability_class']) ? trim(strtolower($_GET['ability_class'])) : '';
        $search_data['ability_flavour'] = !empty($_GET['ability_flavour']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['ability_flavour']) ? trim($_GET['ability_flavour']) : '';
        $search_data['ability_game'] = !empty($_GET['ability_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['ability_game']) ? trim(strtoupper($_GET['ability_game'])) : '';
        $search_data['ability_group'] = !empty($_GET['ability_group']) && preg_match('/[-_0-9a-z\/]+/i', $_GET['ability_group']) ? trim($_GET['ability_group']) : '';
        $search_data['ability_flag_hidden'] = isset($_GET['ability_flag_hidden']) && $_GET['ability_flag_hidden'] !== '' ? (!empty($_GET['ability_flag_hidden']) ? 1 : 0) : '';
        $search_data['ability_flag_complete'] = isset($_GET['ability_flag_complete']) && $_GET['ability_flag_complete'] !== '' ? (!empty($_GET['ability_flag_complete']) ? 1 : 0) : '';
        $search_data['ability_flag_unlockable'] = isset($_GET['ability_flag_unlockable']) && $_GET['ability_flag_unlockable'] !== '' ? (!empty($_GET['ability_flag_unlockable']) ? 1 : 0) : '';
        $search_data['ability_flag_published'] = isset($_GET['ability_flag_published']) && $_GET['ability_flag_published'] !== '' ? (!empty($_GET['ability_flag_published']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_append_git_statuses($search_data, 'ability');

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_ability_fields = rpg_ability::get_index_fields(true, 'abilities');
        $search_query = "SELECT
            {$temp_ability_fields},
            groups.group_token AS ability_group,
            tokens.token_order AS ability_order
            FROM mmrpg_index_abilities AS abilities
            LEFT JOIN mmrpg_index_abilities_groups_tokens AS tokens ON tokens.ability_token = abilities.ability_token
            LEFT JOIN mmrpg_index_abilities_groups AS groups ON groups.group_token = tokens.group_token AND groups.group_class = abilities.ability_class
            WHERE 1=1
            AND abilities.ability_token <> 'ability'
            ";

        // If the ability ID was provided, we can search by exact match
        if (!empty($search_data['ability_id'])){
            $ability_id = $search_data['ability_id'];
            $search_query .= "AND abilities.ability_id = {$ability_id} ";
            $search_results_limit = false;
        }

        // Else if the ability name was provided, we can use wildcards
        if (!empty($search_data['ability_name'])){
            $ability_name = $search_data['ability_name'];
            $ability_name = str_replace(array(' ', '*', '%'), '%', $ability_name);
            $ability_name = preg_replace('/%+/', '%', $ability_name);
            $ability_name = '%'.$ability_name.'%';
            $search_query .= "AND (abilities.ability_name LIKE '{$ability_name}' OR abilities.ability_token LIKE '{$ability_name}') ";
            $search_results_limit = false;
        }

        // Else if the ability type was provided, we can use wildcards
        if (!empty($search_data['ability_type'])){
            $ability_type = $search_data['ability_type'];
            if ($ability_type !== 'none'){ $search_query .= "AND (abilities.ability_type LIKE '{$ability_type}' OR abilities.ability_type2 LIKE '{$ability_type}') "; }
            else { $search_query .= "AND abilities.ability_type = '' "; }
            $search_results_limit = false;
        }

        // If the ability class was provided
        if (!empty($search_data['ability_class'])){
            $search_query .= "AND abilities.ability_class = '{$search_data['ability_class']}' ";
            $search_results_limit = false;
        } elseif (!empty($this_ability_class)){
            $search_query .= "AND abilities.ability_class = '{$this_ability_class}' ";
        }

        // Else if the ability flavour was provided, we can use wildcards
        if (!empty($search_data['ability_flavour'])){
            $ability_flavour = $search_data['ability_flavour'];
            $ability_flavour = str_replace(array(' ', '*', '%'), '%', $ability_flavour);
            $ability_flavour = preg_replace('/%+/', '%', $ability_flavour);
            $ability_flavour = '%'.$ability_flavour.'%';
            $search_query .= "AND (
                abilities.ability_description LIKE '{$ability_flavour}'
                OR abilities.ability_description2 LIKE '{$ability_flavour}'
                ) ";
            $search_results_limit = false;
        }

        // If the ability game was provided
        if (!empty($search_data['ability_game'])){
            $search_query .= "AND abilities.ability_game = '{$search_data['ability_game']}' ";
            $search_results_limit = false;
        }

        // If the ability group was provided
        if (!empty($search_data['ability_group'])){
            $search_query .= "AND groups.group_token = '{$search_data['ability_group']}' ";
            $search_results_limit = false;
        }

        // If the ability hidden flag was provided
        if ($search_data['ability_flag_hidden'] !== ''){
            $search_query .= "AND abilities.ability_flag_hidden = {$search_data['ability_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the ability complete flag was provided
        if ($search_data['ability_flag_complete'] !== ''){
            $search_query .= "AND abilities.ability_flag_complete = {$search_data['ability_flag_complete']} ";
            $search_results_limit = false;
        }

        // If the ability unlockable flag was provided
        if ($search_data['ability_flag_unlockable'] !== ''){
            $search_query .= "AND abilities.ability_flag_unlockable = {$search_data['ability_flag_unlockable']} ";
            $search_results_limit = false;
        }

        // If the ability published flag was provided
        if ($search_data['ability_flag_published'] !== ''){
            $search_query .= "AND abilities.ability_flag_published = {$search_data['ability_flag_published']} ";
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
        cms_admin::object_index_search_results_filter_git_statuses($search_results, $search_results_count, $search_data, 'ability', $mmrpg_git_file_arrays);

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(ability_id) AS total FROM mmrpg_index_abilities WHERE 1=1 AND ability_token <> 'ability' AND ability_class = '{$this_ability_class}';", 'total');

    }

    // If we're in editor mode, we should collect ability info from database
    $ability_data = array();
    $ability_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['ability_id'])
        ){

        // Collect form data for processing
        $editor_data['ability_id'] = !empty($_GET['ability_id']) && is_numeric($_GET['ability_id']) ? trim($_GET['ability_id']) : '';

        /* -- Collect Ability Data -- */

        // Collect ability details from the database
        $temp_ability_fields = rpg_ability::get_index_fields(true);
        if (!empty($editor_data['ability_id'])){
            $ability_data = $db->get_array("SELECT {$temp_ability_fields} FROM mmrpg_index_abilities WHERE ability_id = {$editor_data['ability_id']};");
        } else {

            // Generate temp data structure for the new challenge
            $ability_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $ability_data = array(
                'ability_id' => 0,
                'ability_token' => '',
                'ability_name' => '',
                'ability_class' => $this_ability_class,
                'ability_subclass' => '',
                'ability_type' => '',
                'ability_type2' => '',
                'ability_target' => '',
                'ability_flag_hidden' => 0,
                'ability_flag_complete' => 0,
                'ability_flag_published' => 0,
                'ability_flag_unlockable' => 0,
                'ability_flag_protected' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $ability_data[$f] = $v;
                }
            }

        }

        // If ability data could not be found, produce error and exit
        if (empty($ability_data)){ exit_ability_edit_action(); }

        // Collect the ability's name(s) for display
        $ability_name_display = $ability_data['ability_name'];
        if ($ability_data_is_new){ $this_page_tabtitle = 'New '.$this_ability_class_name_uc.' | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $ability_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this ability, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == $this_ability_page_token){

            // COLLECT form data from the request and parse out simple rules

            $old_ability_token = !empty($_POST['old_ability_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_ability_token']) ? trim(strtolower($_POST['old_ability_token'])) : '';

            $form_data['ability_id'] = !empty($_POST['ability_id']) && is_numeric($_POST['ability_id']) ? trim($_POST['ability_id']) : 0;
            $form_data['ability_token'] = !empty($_POST['ability_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['ability_token']) ? trim(strtolower($_POST['ability_token'])) : '';
            $form_data['ability_name'] = !empty($_POST['ability_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['ability_name']) ? trim($_POST['ability_name']) : '';
            $form_data['ability_class'] = $this_ability_class; //!empty($_POST['ability_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['ability_class']) ? trim(strtolower($_POST['ability_class'])) : '';
            $form_data['ability_type'] = !empty($_POST['ability_type']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['ability_type']) ? trim(strtolower($_POST['ability_type'])) : '';
            $form_data['ability_type2'] = !empty($_POST['ability_type2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['ability_type2']) ? trim(strtolower($_POST['ability_type2'])) : '';
            $form_data['ability_target'] = !empty($_POST['ability_target']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['ability_target']) ? trim($_POST['ability_target']) : '';

            $form_data['ability_price'] = !empty($_POST['ability_price']) && is_numeric($_POST['ability_price']) ? (int)(trim($_POST['ability_price'])) : 0;
            $form_data['ability_value'] = !empty($_POST['ability_value']) && is_numeric($_POST['ability_value']) ? (int)(trim($_POST['ability_value'])) : 0;

            $form_data['ability_shop_tab'] = !empty($_POST['ability_shop_tab']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['ability_shop_tab']) ? trim(strtolower($_POST['ability_shop_tab'])) : '';
            $form_data['ability_shop_level'] = !empty($_POST['ability_shop_level']) && is_numeric($_POST['ability_shop_level']) ? (int)(trim($_POST['ability_shop_level'])) : 0;

            $form_data['ability_game'] = !empty($_POST['ability_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['ability_game']) ? trim($_POST['ability_game']) : '';
            $form_data['ability_master'] = !empty($_POST['ability_master']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['ability_master']) ? trim($_POST['ability_master']) : '';

            $form_data['ability_energy'] = !empty($_POST['ability_energy']) && is_numeric($_POST['ability_energy']) ? (int)(trim($_POST['ability_energy'])) : 0;
            $form_data['ability_energy_percent'] = isset($_POST['ability_energy_percent']) && is_numeric($_POST['ability_energy_percent']) ? (int)(trim($_POST['ability_energy_percent'])) : 0;

            $form_data['ability_accuracy'] = !empty($_POST['ability_accuracy']) && is_numeric($_POST['ability_accuracy']) ? (int)(trim($_POST['ability_accuracy'])) : 0;
            $form_data['ability_speed'] = !empty($_POST['ability_speed']) && is_numeric($_POST['ability_speed']) ? (int)(trim($_POST['ability_speed'])) : 0;
            $form_data['ability_speed2'] = !empty($_POST['ability_speed2']) && is_numeric($_POST['ability_speed2']) ? (int)(trim($_POST['ability_speed2'])) : 0;

            $form_data['ability_damage'] = !empty($_POST['ability_damage']) && is_numeric($_POST['ability_damage']) ? (int)(trim($_POST['ability_damage'])) : 0;
            $form_data['ability_damage_percent'] = isset($_POST['ability_damage_percent']) && is_numeric($_POST['ability_damage_percent']) ? (int)(trim($_POST['ability_damage_percent'])) : 0;
            $form_data['ability_damage2'] = !empty($_POST['ability_damage2']) && is_numeric($_POST['ability_damage2']) ? (int)(trim($_POST['ability_damage2'])) : 0;
            $form_data['ability_damage2_percent'] = isset($_POST['ability_damage2_percent']) && is_numeric($_POST['ability_damage2_percent']) ? (int)(trim($_POST['ability_damage2_percent'])) : 0;
            $form_data['ability_recovery'] = !empty($_POST['ability_recovery']) && is_numeric($_POST['ability_recovery']) ? (int)(trim($_POST['ability_recovery'])) : 0;
            $form_data['ability_recovery_percent'] = isset($_POST['ability_recovery_percent']) && is_numeric($_POST['ability_recovery_percent']) ? (int)(trim($_POST['ability_recovery_percent'])) : 0;
            $form_data['ability_recovery2'] = !empty($_POST['ability_recovery2']) && is_numeric($_POST['ability_recovery2']) ? (int)(trim($_POST['ability_recovery2'])) : 0;
            $form_data['ability_recovery2_percent'] = isset($_POST['ability_recovery2_percent']) && is_numeric($_POST['ability_recovery2_percent']) ? (int)(trim($_POST['ability_recovery2_percent'])) : 0;

            $form_data['ability_description'] = !empty($_POST['ability_description']) ? trim(strip_tags($_POST['ability_description'])) : '';
            $form_data['ability_description2'] = !empty($_POST['ability_description2']) ? trim(strip_tags($_POST['ability_description2'])) : '';

            $form_data['ability_image'] = !empty($_POST['ability_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['ability_image']) ? trim(strtolower($_POST['ability_image'])) : '';
            $form_data['ability_image_size'] = !empty($_POST['ability_image_size']) && is_numeric($_POST['ability_image_size']) ? (int)(trim($_POST['ability_image_size'])) : 0;
            $form_data['ability_image_editor'] = !empty($_POST['ability_image_editor']) && is_numeric($_POST['ability_image_editor']) ? (int)(trim($_POST['ability_image_editor'])) : 0;
            $form_data['ability_image_editor2'] = !empty($_POST['ability_image_editor2']) && is_numeric($_POST['ability_image_editor2']) ? (int)(trim($_POST['ability_image_editor2'])) : 0;
            $form_data['ability_image_sheets'] = !empty($_POST['ability_image_sheets']) && is_numeric($_POST['ability_image_sheets']) ? (int)(trim($_POST['ability_image_sheets'])) : 0;

            $form_data['ability_flag_published'] = isset($_POST['ability_flag_published']) && is_numeric($_POST['ability_flag_published']) ? (int)(trim($_POST['ability_flag_published'])) : 0;
            $form_data['ability_flag_complete'] = isset($_POST['ability_flag_complete']) && is_numeric($_POST['ability_flag_complete']) ? (int)(trim($_POST['ability_flag_complete'])) : 0;
            $form_data['ability_flag_hidden'] = isset($_POST['ability_flag_hidden']) && is_numeric($_POST['ability_flag_hidden']) ? (int)(trim($_POST['ability_flag_hidden'])) : 0;
            $form_data['ability_flag_unlockable'] = isset($_POST['ability_flag_unlockable']) && is_numeric($_POST['ability_flag_unlockable']) ? (int)(trim($_POST['ability_flag_unlockable'])) : 0;

            $form_data['ability_functions_markup'] = !empty($_POST['ability_functions_markup']) ? trim($_POST['ability_functions_markup']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If this is a NEW ability, auto-generate the token when not provided
            if ($ability_data_is_new
                && empty($form_data['ability_token'])
                && !empty($form_data['ability_name'])){
                $auto_token = strtolower($form_data['ability_name']);
                $auto_token = preg_replace('/\s+/', '-', $auto_token);
                $auto_token = preg_replace('/[^-a-z0-9]+/i', '', $auto_token);
                $form_data['ability_token'] = $auto_token;
            }

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$ability_data_is_new && empty($form_data['ability_id'])){ $form_messages[] = array('error', $this_ability_class_short_name_uc.' ID was not provided'); $form_success = false; }
            if (empty($form_data['ability_token']) || (!$ability_data_is_new && empty($old_ability_token))){ $form_messages[] = array('error', $this_ability_class_short_name_uc.' Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['ability_name'])){ $form_messages[] = array('error', $this_ability_class_short_name_uc.' Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['ability_class'])){ $form_messages[] = array('error', $this_ability_class_short_name_uc.' Kind was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['ability_type']) || !isset($_POST['ability_type2'])){ $form_messages[] = array('warning', 'Types were not provided or were invalid'); $form_success = false; }
            if (!$form_success){ exit_ability_edit_action($form_data['ability_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$ability_data_is_new && empty($form_data['ability_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            //if (empty($form_data['ability_master'])){ $form_messages[] = array('warning', 'Source Robot was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if ($form_data['ability_flag_unlockable']){
                if (!$form_data['ability_flag_published']){ $form_messages[] = array('warning', $this_ability_class_short_name_uc.' must be published to be unlockable'); $form_data['ability_flag_unlockable'] = 0; }
                elseif (!$form_data['ability_flag_complete']){ $form_messages[] = array('warning', $this_ability_class_short_name_uc.' must be complete to be unlockable'); $form_data['ability_flag_unlockable'] = 0; }
                elseif ($form_data['ability_class'] !== 'master'){ $form_messages[] = array('warning', 'Only robot master abilities can be marked as unlockable'); $form_data['ability_flag_unlockable'] = 0; }
                elseif (empty($form_data['ability_description'])){ $form_messages[] = array('warning', $this_ability_class_short_name_uc.' must have a description to be unlockable'); $form_data['ability_flag_unlockable'] = 0; }
            }

            if (isset($form_data['ability_type'])){
                // Fix any type ordering problems (like selecting Neutral + anything)
                $types = array_values(array_filter(array($form_data['ability_type'], $form_data['ability_type2'])));
                $form_data['ability_type'] = isset($types[0]) ? $types[0] : '';
                $form_data['ability_type2'] = isset($types[1]) ? $types[1] : '';
            }

            // Only parse the following fields if NOT new object data
            if (!$ability_data_is_new){

                $ability_speed2_defined = isset($_POST['ability_speed2_defined']) && is_numeric($_POST['ability_speed2_defined']) ? (int)(trim($_POST['ability_speed2_defined'])) : 0;
                if (!$ability_speed2_defined){ $form_data['ability_speed2'] = $form_data['ability_speed']; }

                if (!empty($form_data['ability_master'])){
                    $master_token = $form_data['ability_master'];
                    $ability_master_info = $mmrpg_robots_index[$master_token];
                    $form_data['ability_number'] = $ability_master_info['robot_number'];
                } else {
                    $form_data['ability_master'] = '';
                    $form_data['ability_number'] = '';
                }

                $empty_image_folders = array();

                $ability_image_sheets_actions = !empty($_POST['ability_image_sheets_actions']) && is_array($_POST['ability_image_sheets_actions']) ? array_filter($_POST['ability_image_sheets_actions']) : array();
                foreach ($ability_image_sheets_actions AS $sheet_num => $sheet_actions){ $ability_image_sheets_actions[$sheet_num] = array_filter($sheet_actions); }
                $ability_image_sheets_actions = array_filter($ability_image_sheets_actions);
                if (!empty($ability_image_sheets_actions)){
                    foreach ($ability_image_sheets_actions AS $sheet_num => $sheet_actions){
                        if (!empty($sheet_actions['delete_images'])){
                            $sheet_path = ($sheet_num > 1 ? '_'.$sheet_num : '');
                            $delete_sprite_path = 'content/abilities/'.$ability_data['ability_image'].'/sprites'.$sheet_path.'/';
                            $empty_image_folders[] = $delete_sprite_path;
                        }

                    }
                }
                //$form_messages[] = array('alert', '<pre>$ability_image_sheets_actions  = '.print_r($ability_image_sheets_actions, true).'</pre>');

                if (!empty($empty_image_folders)){
                    //$form_messages[] = array('alert', '<pre>$empty_image_folders = '.print_r($empty_image_folders, true).'</pre>');
                    foreach ($empty_image_folders AS $empty_path_key => $empty_path){

                        // Continue if this folder doesn't exist
                        if (!file_exists(MMRPG_CONFIG_ROOTDIR.$empty_path)){ continue; }

                        // Otherwise, collect directory contents (continue if empty)
                        $empty_files = getDirContents(MMRPG_CONFIG_ROOTDIR.$empty_path);
                        $empty_files = !empty($empty_files) ? array_map(function($s){ return str_replace('\\', '/', $s); }, $empty_files) : array();
                        if (empty($empty_files)){ continue; }
                        $form_messages[] = array('alert', '<pre>$empty_path_key = '.print_r($empty_path_key, true).' | $empty_path = '.print_r($empty_path, true).' | $empty_files = '.print_r($empty_files, true).'</pre>');

                        // Loop through empty files and delete one by one
                        foreach ($empty_files AS $empty_file_key => $empty_file_path){
                            @unlink($empty_file_path);
                            if (!file_exists($empty_file_path)){ $form_messages[] = array('alert', str_replace(MMRPG_CONFIG_ROOTDIR, '', $empty_file_path).' was deleted!'); }
                            else { $form_messages[] = array('warning', str_replace(MMRPG_CONFIG_ROOTDIR, '', $empty_file_path).' could not be deleted!');  }
                        }

                    }
                }

                // Ensure the functions code is VALID PHP SYNTAX and save, otherwise do not save but allow user to fix it
                if (empty($form_data['ability_functions_markup'])){
                    // Functions code is EMPTY and will be ignored
                    $form_messages[] = array('warning', $this_ability_class_short_name_uc.' functions code was empty and was not saved (reverted to original)');
                } elseif (!cms_admin::is_valid_php_syntax($form_data['ability_functions_markup'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', $this_ability_class_short_name_uc.' functions code was invalid PHP syntax and was not saved (please fix and try again)');
                    $_SESSION['ability_functions_markup'][$ability_data['ability_id']] = $form_data['ability_functions_markup'];
                } else {
                    // Functions code is OKAY and can be saved
                    $ability_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$ability_data['ability_token'].'/functions.php';
                    $old_ability_functions_markup = file_exists($ability_functions_path) ? normalize_file_markup(file_get_contents($ability_functions_path)) : '';
                    $new_ability_functions_markup = normalize_file_markup($form_data['ability_functions_markup']);
                    if (empty($old_ability_functions_markup) || $new_ability_functions_markup !== $old_ability_functions_markup){
                        $f = fopen($ability_functions_path, 'w');
                        fwrite($f, $new_ability_functions_markup);
                        fclose($f);
                        $form_messages[] = array('alert', $this_ability_class_short_name_uc.' functions file was '.(!empty($old_ability_functions_markup) ? 'updated' : 'created'));
                    }
                }

            }
            // Otherwise, if NEW data, pre-populate certain fields
            else {

                $form_data['ability_accuracy'] = 4;
                $form_data['ability_accuracy'] = 100;

                $form_data['ability_game'] = 'MMRPG';

                $temp_json_fields = rpg_ability::get_json_index_fields();
                foreach ($temp_json_fields AS $field){ $form_data[$field] = !empty($form_data[$field]) ? json_encode($form_data[$field], JSON_NUMERIC_CHECK) : ''; }

            }

            // Regardless, unset the markup variable so it's not save to the database
            unset($form_data['ability_functions_markup']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$ability_data = '.print_r($ability_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            /* foreach ($form_data AS $key => $value1){
                $value2 = $ability_data[$key];
                if ($value1 === '[]'){ $value1 = ''; }
                if ($value2 === '[]'){ $value2 = ''; }
                if ($value1 != $value2){ $form_messages[] = array('error', '<pre>'.
                    '$form_data['.$key.'] != $ability_data['.$key.']'.PHP_EOL.
                    $value1.PHP_EOL.
                    $value2.PHP_EOL.
                    '</pre>'); }
            } */
            /* foreach ($form_data AS $key => $value){
                if (!isset($ability_data[$key])){
                    $form_messages[] = array('error', '$form_data['.$key.'] should not be here');
                }
            } */
            /* foreach ($ability_data AS $key => $value){
                if (!empty($value) && !isset($form_data[$key])){
                    $form_messages[] = array('error', '$form_data['.$key.'] not provided');
                }
            } */
            //exit_ability_edit_action($form_data['ability_id']);

            // Make a copy of the update data sans the ability ID
            $update_data = $form_data;
            unset($update_data['ability_id']);

            // If this is a new ability we insert, otherwise we update the existing
            if ($ability_data_is_new){

                // Update the main database index with changes to this ability's data
                $update_data['ability_flag_protected'] = 0;
                $insert_results = $db->insert('mmrpg_index_abilities', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', $this_ability_class_short_name_uc.' data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', $this_ability_class_short_name_uc.' data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_ability_id = $db->get_value("SELECT MAX(ability_id) AS max FROM mmrpg_index_abilities;", 'max');
                    $form_data['ability_id'] = $new_ability_id;
                }

            } else {

                // Update the main database index with changes to this ability's data
                $update_results = $db->update('mmrpg_index_abilities', $update_data, array('ability_id' => $form_data['ability_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', $this_ability_class_short_name_uc.' data was updated successfully!'); }
                else { $form_messages[] = array('error', $this_ability_class_short_name_uc.' data could not be updated...'); }

            }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($ability_data_is_new){ $ability_data['ability_id'] = $new_ability_id; }
                cms_admin::object_editor_update_json_data_file('ability', array_merge($ability_data, $update_data));
            }

            // If the ability tokens have changed, we must move the entire folder
            if ($form_success
                && !$ability_data_is_new
                && $old_ability_token !== $update_data['ability_token']){
                $old_content_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$old_ability_token.'/';
                $new_content_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$update_data['ability_token'].'/';
                if (rename($old_content_path, $new_content_path)){
                    $path_string = '<strong>'.mmrpg_clean_path($old_content_path).'</strong> &raquo; <strong>'.mmrpg_clean_path($new_content_path).'</strong>';
                    $form_messages[] = array('alert', $this_ability_class_short_name_uc.' directory renamed! '.$path_string);
                } else {
                    $form_messages[] = array('error', 'Unable to rename '.$this_ability_class_name.' directory!');
                }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['ability_id'])){ exit_ability_edit_action(false); }
            else { exit_ability_edit_action($form_data['ability_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    // If we're in groups mode, we need to preset vars and then include common file
    $object_group_kind = 'ability';
    $object_group_class = $this_ability_class;
    $object_group_editor_url = $this_ability_page_baseurl.'groups/';
    $object_group_editor_name = $this_ability_class_name_uc.' Groups';
    if ($sub_action == 'groups'){
        require('edit-groups_actions.php');
    }

    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="<?= $this_ability_page_baseurl ?>">Edit <?= $this_ability_xclass_name_uc ?></a>
        <? if ($sub_action == 'editor' && !empty($ability_data)): ?>
            &raquo; <a href="<?= $this_ability_page_baseurl ?>editor/ability_id=<?= $ability_data['ability_id'] ?>"><?= !empty($ability_name_display) ? $ability_name_display : 'New '.$this_ability_class_name_uc ?></a>
        <? elseif ($sub_action == 'groups'): ?>
            &raquo; <a href="<?= $object_group_editor_url ?>"><?= $object_group_editor_name ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-abilities edit-<?= str_replace(' ', '-', $this_ability_xclass_name) ?>" data-baseurl="<?= $this_ability_page_baseurl ?>" data-object="ability" data-xobject="abilities">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search <?= $this_ability_xclass_name_uc ?></h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <? /* <input type="hidden" name="action" value="<?= $this_ability_page_token ?>" /> */ ?>
                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="ability_id" value="<?= !empty($search_data['ability_id']) ? $search_data['ability_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="ability_name" placeholder="" value="<?= !empty($search_data['ability_name']) ? htmlentities($search_data['ability_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <? /*
                    <div class="field">
                        <strong class="label">By Class</strong>
                        <select class="select" name="ability_class">
                            <option value=""></option>
                            <option value="mecha"<?= !empty($search_data['ability_class']) && $search_data['ability_class'] === 'mecha' ? ' selected="selected"' : '' ?>>Mecha</option>
                            <option value="master"<?= !empty($search_data['ability_class']) && $search_data['ability_class'] === 'master' ? ' selected="selected"' : '' ?>>Master</option>
                            <option value="boss"<?= !empty($search_data['ability_class']) && $search_data['ability_class'] === 'boss' ? ' selected="selected"' : '' ?>>Boss</option>
                        </select><span></span>
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Type</strong>
                        <select class="select" name="ability_type"><option value=""></option><?
                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                if ($type_info['type_class'] === 'special' && $type_token !== 'none'){ continue; }
                                ?><option value="<?= $type_token ?>"<?= !empty($search_data['ability_type']) && $search_data['ability_type'] === $type_token ? ' selected="selected"' : '' ?>><?= $type_token === 'none' ? 'Neutral' : ucfirst($type_token) ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Flavour</strong>
                        <input class="textbox" type="text" name="ability_flavour" placeholder="" value="<?= !empty($search_data['ability_flavour']) ? htmlentities($search_data['ability_flavour'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Game</strong>
                        <select class="select" name="ability_game"><option value=""></option><?
                            $ability_games_tokens = $db->get_array_list("SELECT DISTINCT (ability_game) AS game_token FROM mmrpg_index_abilities ORDER BY ability_game ASC;");
                            foreach ($ability_games_tokens AS $game_key => $game_info){
                                $game_token = $game_info['game_token'];
                                ?><option value="<?= $game_token ?>"<?= !empty($search_data['ability_game']) && $search_data['ability_game'] === $game_token ? ' selected="selected"' : '' ?>><?= $game_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Group</strong>
                        <select class="select" name="ability_group"><option value=""></option><?
                            $ability_groups_tokens = $db->get_array_list("SELECT group_token FROM mmrpg_index_abilities_groups WHERE group_class = '{$this_ability_class}' ORDER BY group_order ASC;");
                            foreach ($ability_groups_tokens AS $group_key => $group_info){
                                $group_token = $group_info['group_token'];
                                ?><option value="<?= $group_token ?>"<?= !empty($search_data['ability_group']) && $search_data['ability_group'] === $group_token ? ' selected="selected"' : '' ?>><?= $group_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field fullsize has5cols flags">
                    <?
                    $flag_names = array(
                        'published' => array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished'),
                        'complete' => array('icon' => 'fas fa-check-circle', 'yes' => 'Complete', 'no' => 'Incomplete'),
                        'unlockable' => array('icon' => 'fas fa-unlock', 'yes' => 'Unlockable', 'no' => 'Locked'),
                        'hidden' => array('icon' => 'fas fa-eye-slash', 'yes' => 'Hidden', 'no' => 'Visible')
                        );
                    cms_admin::object_index_flag_names_append_git_statuses($flag_names);
                    foreach ($flag_names AS $flag_token => $flag_info){
                        if (isset($flag_info['break'])){ echo('<div class="break"></div>'); continue; }
                        $flag_name = 'ability_flag_'.$flag_token;
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
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='<?= $this_ability_page_baseurl ?>';" />
                        <a class="button new" href="<?= $this_ability_page_baseurl.'editor/ability_id=0' ?>">Create New <?= ucfirst($this_ability_class) ?> Ability</a>
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
                            <col class="type" width="120" />
                            <col class="game" width="120" />
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag unlockable" width="80" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('ability_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('ability_name', 'Name') ?></th>
                                <th class="type"><?= cms_admin::get_sort_link('ability_type', 'Type(s)') ?></th>
                                <th class="game"><?= cms_admin::get_sort_link('ability_game', 'Game') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('ability_flag_published', 'Published') ?></th>
                                <th class="flag complete"><?= cms_admin::get_sort_link('ability_flag_complete', 'Complete') ?></th>
                                <th class="flag unlockable"><?= cms_admin::get_sort_link('ability_flag_unlockable', 'Unlockable') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('ability_flag_hidden', 'Hidden') ?></th>
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
                                'master' => array('defense', '<i class="fas fa-ability"></i>'),
                                'boss' => array('space', '<i class="fas fa-skull"></i>')
                                );
                            foreach ($search_results AS $key => $ability_data){

                                $ability_id = $ability_data['ability_id'];
                                $ability_token = $ability_data['ability_token'];
                                $ability_name = $ability_data['ability_name'];
                                $ability_type = !empty($ability_data['ability_type']) ? ucfirst($ability_data['ability_type']) : 'Neutral';
                                $ability_type_span = '<span class="type_span type_'.(!empty($ability_data['ability_type']) ? $ability_data['ability_type'] : 'none').'">'.$ability_type.'</span>';
                                if (!empty($ability_data['ability_type'])
                                    && !empty($ability_data['ability_type2'])){
                                    $ability_type .= ' / '.ucfirst($ability_data['ability_type2']);
                                    $ability_type_span = '<span class="type_span type_'.$ability_data['ability_type'].'_'.$ability_data['ability_type2'].'">'.ucwords($ability_data['ability_type'].' / '.$ability_data['ability_type2']).'</span>';
                                }
                                $ability_game = ucfirst($ability_data['ability_game']);
                                $ability_game_span = '<span class="type_span type_none">'.$ability_game.'</span>';
                                $ability_flag_published = !empty($ability_data['ability_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $ability_flag_complete = !empty($ability_data['ability_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $ability_flag_unlockable = !empty($ability_data['ability_flag_unlockable']) ? '<i class="fas fa-unlock"></i>' : '-';
                                $ability_flag_hidden = !empty($ability_data['ability_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $ability_edit_url = $this_ability_page_baseurl.'editor/ability_id='.$ability_id;
                                $ability_name_link = '<a class="link" href="'.$ability_edit_url.'">'.$ability_name.'</a>';
                                cms_admin::object_index_links_append_git_statues($ability_name_link, $ability_token, $mmrpg_git_file_arrays);

                                $ability_actions = '';
                                $ability_actions .= '<a class="link edit" href="'.$ability_edit_url.'"><span>edit</span></a>';
                                if (empty($ability_data['ability_flag_protected'])){
                                    $ability_actions .= '<a class="link delete" data-delete="abilities" data-ability-id="'.$ability_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$ability_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$ability_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="type"><div class="wrap">'.$ability_type_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="game"><div class="wrap">'.$ability_game_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$ability_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$ability_flag_complete.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag unlockable"><div>'.$ability_flag_unlockable.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$ability_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$ability_actions.'</div></td>'.PHP_EOL;
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
            && isset($_GET['ability_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($ability_data['ability_type']) ? $ability_data['ability_type'].(!empty($ability_data['ability_type2']) ? '_'.$ability_data['ability_type2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="ability_type,ability_type2">
                        <span class="title"><?= !empty($ability_name_display) ? 'Edit '.$this_ability_class_short_name_uc.' &quot;'.$ability_name_display.'&quot;' : 'Create New '.$this_ability_class_short_name_uc ?></span>
                        <?

                        // Print out any git-related statues to this header
                        cms_admin::object_editor_header_echo_git_statues($ability_data['ability_token'], $mmrpg_git_file_arrays);

                        // If the ability is published, generate and display a preview link
                        if (!empty($ability_data['ability_flag_published'])
                            && $ability_data['ability_class'] === 'master'){
                            $preview_link = 'database/abilities/';
                            $preview_link .= $ability_data['ability_token'].'/';
                            echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <? if (!$ability_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="ability">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="sprites">Sprites</a><span></span>
                            <a class="tab" data-tab="functions">Functions</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="<?= $this_ability_page_token ?>" />
                        <input type="hidden" name="subaction" value="editor" />

                        <input type="hidden" name="ability_class" value="<?= $this_ability_class ?>" />

                        <div class="editor-panels" data-tabgroup="ability">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label"><?= $this_ability_class_short_name_uc ?> ID</strong>
                                    <input type="hidden" name="ability_id" value="<?= $ability_data['ability_id'] ?>" />
                                    <input class="textbox" type="text" name="ability_id" value="<?= $ability_data['ability_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong><?= $this_ability_class_short_name_uc ?> Token</strong>
                                        <?= !empty($ability_data['ability_flag_protected']) ? '<em>cannot be changed</em>' : '' ?>
                                    </div>
                                    <input type="hidden" name="old_ability_token" value="<?= $ability_data['ability_token'] ?>" />
                                    <input type="hidden" name="ability_token" value="<?= $ability_data['ability_token'] ?>" />
                                    <input class="textbox" type="text" name="ability_token" value="<?= $ability_data['ability_token'] ?>" maxlength="64" <?= !empty($ability_data['ability_flag_protected']) ? 'disabled="disabled"' : '' ?> />
                                </div>

                                <div class="field">
                                    <strong class="label"><?= $this_ability_class_short_name_uc ?> Name</strong>
                                    <input class="textbox" type="text" name="ability_name" value="<?= $ability_data['ability_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field has2cols">
                                    <strong class="label">
                                        Type(s)
                                        <span class="type_span type_<?= (!empty($ability_data['ability_type']) ? $ability_data['ability_type'].(!empty($ability_data['ability_type2']) ? '_'.$ability_data['ability_type2'] : '') : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="ability_type,ability_type2">&nbsp;</span>
                                    </strong>
                                    <div class="subfield">
                                        <select class="select" name="ability_type">
                                            <option value=""<?= empty($ability_data['ability_type']) ? ' selected="selected"' : '' ?>>Neutral</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($ability_data['ability_type']) && $ability_data['ability_type'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <select class="select" name="ability_type2">
                                            <option value=""<?= empty($ability_data['ability_type2']) ? ' selected="selected"' : '' ?>>-</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($ability_data['ability_type2']) && $ability_data['ability_type2'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                </div>

                                <? if (!$ability_data_is_new){ ?>

                                    <div class="field">
                                        <strong class="label">Target</strong>
                                        <select class="select" name="ability_target">
                                            <?
                                            $temp_target_index = json_decode(MMRPG_SETTINGS_ABILITY_TARGETINDEX, true);
                                            if (empty($ability_data['ability_target'])){ $ability_data['ability_target'] = 'auto'; }
                                            foreach ($temp_target_index AS $value => $label){
                                                ?><option value="<?= $value ?>" <?= $ability_data['ability_target'] == $value ? 'selected="selected"' : '' ?>><?= $label ?></option><?
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>

                                    <hr />

                                    <div class="field foursize has_unit has_unit_checkbox">
                                        <strong class="label"><span class="type_span type_weapons">Energy</span> <em>WE</em></strong>
                                        <input class="textbox" type="number" name="ability_energy" value="<?= $ability_data['ability_energy'] ?>" maxlength="8" min="0" max="64" step="2" />
                                        <? $is_percent = !empty($ability_data['ability_energy_percent']) ? true : false; ?>
                                        <strong class="unit has_checkbox" title="Is Percent?">
                                            <span class="<?= $is_percent ? 'active' : 'inactive' ?>">%</span>
                                            <input type="hidden" name="ability_energy_percent" value="0" />
                                            <input type="checkbox" name="ability_energy_percent" value="1" <?= $is_percent ? 'checked="checked"' : '' ?> />
                                        </strong>
                                    </div>

                                    <div class="field foursize has_unit">
                                        <strong class="label"><span class="type_span type_shield">Accuracy</span></strong>
                                        <input class="textbox" type="number" name="ability_accuracy" value="<?= $ability_data['ability_accuracy'] ?>" maxlength="8" min="1" max="100" step="1" />
                                        <strong class="unit">
                                            <span>%</span>
                                        </strong>
                                    </div>

                                    <div class="field foursize has_toggle">
                                        <strong class="label"><span class="type_span type_speed">Speed</span> <em>displayed value</em></strong>
                                        <input class="textbox" type="number" name="ability_speed" value="<?= $ability_data['ability_speed'] ?>" maxlength="8" min="-10" max="10" step="1" />
                                    </div>

                                    <? $ability_speed2_defined = ($ability_data['ability_speed2'] !== $ability_data['ability_speed']) ? true : false;  ?>
                                    <div class="field foursize has_toggle has_toggle_checkbox <?= !$ability_speed2_defined ? 'disabled' : '' ?>">
                                        <strong class="label"><span class="type_span type_speed">Speed2</span> <em>hidden value</em></strong>
                                        <input type="hidden" name="ability_speed2" value="auto" />
                                        <input class="textbox toggle_input" type="number" name="ability_speed2" value="<?= $ability_speed2_defined ? $ability_data['ability_speed2'] : '' ?>" data-default-value-from="ability_speed" maxlength="8" min="-10" max="10" step="1" <?= !$ability_speed2_defined ? 'disabled="disabled"' : '' ?> />
                                        <strong class="toggle has_checkbox">
                                            <input type="hidden" name="ability_speed2_defined" value="0" />
                                            <input type="checkbox" name="ability_speed2_defined" value="1" <?= $ability_speed2_defined ? 'checked="checked"' : '' ?> />
                                        </strong>
                                    </div>

                                    <hr />

                                    <?
                                    // Define the "power" stats and loop through them, generating field markup
                                    $power_fields = array('damage' => 'attack', 'recovery' => 'energy');
                                    foreach ($power_fields AS $power_field => $power_colour){
                                        for ($i = 1; $i <= 2; $i++){
                                            $token = $power_field.($i > 1 ? $i : '');
                                            $name = ucfirst($power_field).($i > 1 ? $i : '');
                                            $note = $i === 1 ? 'displayed value' : 'hidden value';
                                            $field_name = 'ability_'.$token;
                                            $field_percent_name = $field_name.'_percent';
                                            $is_percent = !empty($ability_data[$field_percent_name]) ? true : false;
                                            ?>
                                            <div class="field foursize has_unit has_unit_checkbox">
                                                <strong class="label"><span class="type_span type_<?= $power_colour ?>"><?= $name ?></span> <em><?= $note ?></em></strong>
                                                <input class="textbox" type="number" name="<?= $field_name ?>" value="<?= $ability_data[$field_name] ?>" maxlength="8" min="0" step="1" />
                                                <strong class="unit has_checkbox" title="Is Percent?">
                                                    <span class="<?= $is_percent ? 'active' : 'inactive' ?>">%</span>
                                                    <input type="hidden" name="<?= $field_percent_name ?>" value="0" />
                                                    <input type="checkbox" name="<?= $field_percent_name ?>" value="1" <?= $is_percent ? 'checked="checked"' : '' ?> />
                                                </strong>
                                            </div>
                                            <?
                                        }
                                    }
                                    ?>

                                    <hr />

                                    <div class="field fullsize" style="margin-bottom: 0; padding-bottom: 0;">
                                        <div class="label">
                                            <strong><?= $this_ability_class_short_name_uc ?> Description</strong>
                                            <em>short paragraph describing what this ability does and its effects</em>
                                        </div>
                                        <textarea class="textarea" name="ability_description" rows="4"><?= htmlentities($ability_data['ability_description'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                        <div class="label examples" style="font-size: 80%; padding-top: 4px; margin-bottom: 0;">
                                            <strong>Dynamic Values</strong>:
                                            <br />
                                            <code style="color: green;">{DAMAGE}</code>, <code style="color: green;">{DAMAGE2}</code>,
                                            <code style="color: green;">{RECOVERY}</code>, <code style="color: green;">{RECOVERY2}</code>
                                        </div>
                                    </div>

                                    <? if ($this_ability_class === 'master'){ ?>

                                        <hr />

                                        <div class="field halfsize">
                                            <strong class="label">Shop Availability <em>leave blank if not available in shop</em></strong>
                                            <select class="select" name="ability_shop_tab">
                                                <option value="" <?= empty($ability_data['ability_shop_tab']) ? 'selected="selected"' : '' ?>>- none -</option>
                                                <option value="reggae/abilities" <?= !empty($ability_data['ability_shop_tab']) && $ability_data['ability_shop_tab'] === 'reggae/abilities' ? 'selected="selected"' : '' ?> data-level-step="10">Reggae's Ability Shop</option>
                                                <option value="reggae/weapons" <?= !empty($ability_data['ability_shop_tab']) && $ability_data['ability_shop_tab'] === 'reggae/weapons' ? 'selected="selected"' : '' ?> data-level-step="1">Reggae's Weapon Shop</option>
                                            </select><span></span>
                                        </div>

                                        <div class="field halfsize">
                                            <strong class="label" data-shop-tab="">Required Level <em>shop level for abilities / core level for weapons</em></strong>
                                            <strong class="label" data-shop-tab="reggae/abilities">Required Shop Level <em>min shop level attained before available</em></strong>
                                            <strong class="label" data-shop-tab="reggae/weapons">Required Core Level <em>min elemental cores sold before available</em></strong>
                                            <input class="hidden" type="hidden" name="ability_shop_level" value="0" />
                                            <input class="textbox" type="number" name="ability_shop_level" value="<?= !empty($ability_data['ability_shop_level']) ? $ability_data['ability_shop_level'] : 0 ?>" maxlength="3" min="0" max="100" step="1" />
                                        </div>

                                        <div class="field halfsize">
                                            <strong class="label">Base Price <em>zenny price if purchased in shop / point value auto-calculated if applicable</em></strong>
                                            <input class="hidden" type="hidden" name="ability_price" value="0" />
                                            <input class="textbox" type="number" name="ability_price" value="<?= !empty($ability_data['ability_price']) ? $ability_data['ability_price'] : 0 ?>" maxlength="8" min="0" step="50" />
                                        </div>

                                        <div class="field halfsize">
                                            <strong class="label">Base Value <em>battle point value on leaderboard / sell price auto-calculated if applicable</strong>
                                            <input class="hidden" type="hidden" name="ability_value" value="0" />
                                            <input class="textbox" type="number" name="ability_value" value="<?= !empty($ability_data['ability_value']) ? $ability_data['ability_value'] : 0 ?>" maxlength="8" min="0" step="50" />
                                        </div>

                                    <? } ?>

                                    <hr />

                                    <div class="field foursize">
                                        <strong class="label">Source Game</strong>
                                        <? $current_value = !empty($ability_data['ability_game']) ? $ability_data['ability_game'] : ''; ?>
                                        <select class="select" name="ability_game">
                                            <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $source_options_markup) ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label">Source Robot</strong>
                                        <select class="select" name="ability_master">
                                            <?
                                            echo('<option value=""'.(empty($ability_data['ability_master']) ? 'selected="selected"' : '').'>- none -</option>');
                                            $last_option_group = false;
                                            foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                                                if ($robot_info['robot_class'] !== $this_ability_class){ continue; }
                                                $class_group = (ucfirst($robot_info['robot_class']).(substr($robot_info['robot_class'], -2, 2) === 'ss' ? 'es' : 's')).' | '.$robot_info['robot_group'];
                                                if ($last_option_group !== $class_group){
                                                    if (!empty($last_option_group)){ $robot_options_markup[] = '</optgroup>'; }
                                                    $last_option_group = $class_group;
                                                    echo('<optgroup label="'.$class_group.'">');
                                                }
                                                $types = !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral';
                                                if (!empty($robot_info['robot_core']) && !empty($robot_info['robot_core2'])){ $types .= ' / '.ucfirst($robot_info['robot_core2']); }
                                                $label = $robot_info['robot_name'].' ('.$types.')';
                                                $selected = !empty($ability_data['ability_master']) && $ability_data['ability_master'] == $robot_token ? 'selected="selected"' : '';
                                                echo('<option value="'.$robot_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            if (!empty($last_option_group)){ echo('</optgroup>'.PHP_EOL); }
                                            ?>
                                        </select><span></span>
                                    </div>

                                <? } ?>

                            </div>

                            <? if (!$ability_data_is_new){ ?>

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

                                    <? $placeholder_folder = $ability_data['ability_class'] != 'master' ? $ability_data['ability_class'] : 'ability'; ?>
                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Path</strong>
                                            <em>base image path for sprites</em>
                                        </div>
                                        <select class="select" name="ability_image">
                                            <option value="<?= $placeholder_folder ?>" <?= $ability_data['ability_image'] == $placeholder_folder ? 'selected="selected"' : '' ?>>-</option>
                                            <option value="<?= $ability_data['ability_token'] ?>" <?= $ability_data['ability_image'] == $ability_data['ability_token'] ? 'selected="selected"' : '' ?>>content/abilities/<?= $ability_data['ability_token'] ?>/</option>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Size</strong>
                                            <em>base frame size for each sprite</em>
                                        </div>
                                        <select class="select" name="ability_image_size">
                                            <? if ($ability_data['ability_image'] == $placeholder_folder){ ?>
                                                <option value="<?= $ability_data['ability_image_size'] ?>" selected="selected">-</option>
                                                <option value="40">40x40</option>
                                                <option value="80">80x80</option>
                                                <option disabled="disabled" value="160">160x160</option>
                                            <? } else { ?>
                                                <option value="40" <?= $ability_data['ability_image_size'] == 40 ? 'selected="selected"' : '' ?>>40x40</option>
                                                <option value="80" <?= $ability_data['ability_image_size'] == 80 ? 'selected="selected"' : '' ?>>80x80</option>
                                                <option disabled="disabled" value="160" <?= $ability_data['ability_image_size'] == 160 ? 'selected="selected"' : '' ?>>160x160</option>
                                            <? } ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Editor #1</strong>
                                            <em>user who edited or created this sprite</em>
                                        </div>
                                        <? if ($ability_data['ability_image'] != $placeholder_folder){ ?>
                                            <select class="select" name="ability_image_editor">
                                                <?= str_replace('value="'.$ability_data['ability_image_editor'].'"', 'value="'.$ability_data['ability_image_editor'].'" selected="selected"', $contributor_options_markup) ?>
                                            </select><span></span>
                                        <? } else { ?>
                                            <input type="hidden" name="ability_image_editor" value="<?= $ability_data['ability_image_editor'] ?>" />
                                            <input class="textbox" type="text" name="ability_image_editor" value="-" disabled="disabled" />
                                        <? } ?>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Editor #2</strong>
                                            <em>another user who collaborated on this sprite</em>
                                        </div>
                                        <? if ($ability_data['ability_image'] != $placeholder_folder){ ?>
                                            <select class="select" name="ability_image_editor2">
                                                <?= str_replace('value="'.$ability_data['ability_image_editor2'].'"', 'value="'.$ability_data['ability_image_editor2'].'" selected="selected"', $contributor_options_markup) ?>
                                            </select><span></span>
                                        <? } else { ?>
                                            <input type="hidden" name="ability_image_editor2" value="<?= $ability_data['ability_image_editor2'] ?>" />
                                            <input class="textbox" type="text" name="ability_image_editor2" value="-" disabled="disabled" />
                                        <? } ?>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Sheets</strong>
                                            <em>number of sheets this sprite requires</em>
                                        </div>
                                        <input class="textbox" type="number" name="ability_image_sheets" value="<?= $ability_data['ability_image_sheets'] ?>" maxlength="8" min="0" step="1" />
                                    </div>

                                    <?

                                    // Only proceed if all required sprite fields are set
                                    if (!empty($ability_data['ability_image'])
                                        && $ability_data['ability_image'] != $placeholder_folder
                                        && !empty($ability_data['ability_image_size'])
                                        && !empty($ability_data['ability_image_sheets'])){

                                        echo('<hr />'.PHP_EOL);

                                        // Define the base sprite paths for this ability given its image token
                                        $base_sprite_path = 'content/abilities/'.$ability_data['ability_image'].'/sprites/';

                                        // Loop through the defined sheets for this ability and display image lists
                                        for ($sheet_key = 0; $sheet_key < $ability_data['ability_image_sheets']; $sheet_key++){

                                            $sheet_num = $sheet_key + 1;
                                            $is_base_sprite = $sheet_key === 0 ? true : false;

                                            $sheet_file_path = rtrim($base_sprite_path, '/').(!$is_base_sprite ? '_'.$sheet_num : '').'/';
                                            $sheet_file_dir = MMRPG_CONFIG_ROOTDIR.$sheet_file_path;
                                            $sheet_files_existing = getDirContents($sheet_file_dir);

                                            if (!empty($sheet_files_existing)){ $sheet_files_existing = array_map(function($s)use($sheet_file_dir){ return str_replace($sheet_file_dir, '', str_replace('\\', '/', $s)); }, $sheet_files_existing); }

                                            //echo('<pre>$sheet_files_existing = '.(!empty($sheet_files_existing) ? htmlentities(print_r($sheet_files_existing, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                                            ?>

                                            <?= ($sheet_key > 0) ? '<hr />' : '' ?>

                                            <div class="field fullsize" style="margin-bottom: 0; min-height: 0;">
                                                <strong class="label">
                                                    <? if ($is_base_sprite){ ?>
                                                        Base Sprite Sheets
                                                        <em>Main sprites used for ability. Zoom sprites are auto-generated.</em>
                                                    <? } else { ?>
                                                        <?= 'Sprite Sheet #'.$sheet_num  ?>
                                                        <em>Additional sprite sheet used for this ability. Zoom sprites are auto-generated.</em>
                                                    <? } ?>
                                                </strong>
                                            </div>
                                            <div class="field fullsize has2cols widecols multirow sprites has-filebars">
                                                <?
                                                $sheet_groups = array('sprites');
                                                $sheet_kinds = array('icon', 'sprite');
                                                $sheet_sizes = array($ability_data['ability_image_size'], $ability_data['ability_image_size'] * 2);
                                                $sheet_directions = array('left', 'right');
                                                $num_frames = count(explode('/', MMRPG_SETTINGS_ABILITY_FRAMEINDEX));
                                                foreach ($sheet_groups AS $group_key => $group){
                                                    if ($group == 'sprites'){ $this_sheet_path = $sheet_file_path; }
                                                    foreach ($sheet_sizes AS $size_key => $size){
                                                        $sheet_height = $size;
                                                        $files_are_automatic = false;
                                                        if ($size_key != 0){ $files_are_automatic = true; }
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
                                                                $sheet_width = $kind != 'icon' ? ($size * $num_frames) : $size;
                                                                foreach ($sheet_directions AS $direction_key => $direction){
                                                                    $file_name = $kind.'_'.$direction.'_'.$size.'x'.$size.'.png';
                                                                    $file_href = MMRPG_CONFIG_ROOTURL.$this_sheet_path.$file_name;
                                                                    $file_exists = in_array($file_name, $sheet_files_existing) ? true : false;
                                                                    $file_is_unused = false;
                                                                    $file_is_optional = false;
                                                                    echo('<li>');
                                                                        echo('<div class="filebar'.($file_is_unused ? ' unused' : '').($file_is_optional ? ' optional' : '').'" data-auto="file-bar" data-file-path="'.$this_sheet_path.'" data-file-name="'.$file_name.'" data-file-kind="image/png" data-file-width="'.$sheet_width.'" data-file-height="'.$sheet_height.'" data-file-extras="auto-zoom-x2">');
                                                                            echo($file_exists ? '<a class="link view" href="'.$file_href.'?'.time().'" target="_blank" data-href="'.$file_href.'">'.$group.'/'.$file_name.'</a>' : '<a class="link view disabled" target="_blank" data-href="'.$file_href.'">'.$group.'/'.$file_name.'</a>');
                                                                            echo('<span class="info size">'.$sheet_width.'w &times; '.$sheet_height.'h</span>');
                                                                            echo($file_exists ? '<span class="info status good">&check;</span>' : '<span class="info status bad">&cross;</span>');
                                                                            if (!$files_are_automatic){
                                                                                echo('<a class="action delete'.(!$file_exists ? ' disabled' : '').'" data-action="delete" data-file-hash="'.md5('delete/'.$this_sheet_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">Delete</a>');
                                                                                echo('<a class="action upload'.($file_exists ? ' disabled' : '').'" data-action="upload" data-file-hash="'.md5('upload/'.$this_sheet_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">');
                                                                                    echo('<span class="text">Upload</span>');
                                                                                    echo('<input class="input" type="file" name="file_info" value=""'.($file_exists ? ' disabled="disabled"' : '').' />');
                                                                                echo('</a>');
                                                                            }
                                                                        echo('</div>');
                                                                        /* echo('<div class="preview">');
                                                                            echo('<img class="image" src="'.$file_href.'" sheet="'.$file_name.'" />');
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
                                                                <input type="hidden" name="ability_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="0" checked="checked" />
                                                                <input class="checkbox" type="checkbox" name="ability_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="1" />
                                                            </label>
                                                            <p class="subtext" style="color: #da1616;">Empty base <strong>/sprites/</strong> folder and remove all images</p>
                                                        </div>

                                                <? } else { ?>

                                                        <div class="field checkwrap rfloat fullsize">
                                                            <label class="label">
                                                                <strong style="color: #da1616;">Delete Sheet #<?= $sheet_num ?> Images?</strong>
                                                                <input type="hidden" name="ability_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="0" checked="checked" />
                                                                <input class="checkbox" type="checkbox" name="ability_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="1" />
                                                            </label>
                                                            <p class="subtext" style="color: #da1616;">Empty extra <strong>/sprites_<?= $sheet_num ?>/</strong> folder and remove all images</p>
                                                        </div>

                                                <? } ?>

                                            </div>

                                            <?

                                        }

                                        //$base_sprite_list = getDirContents(MMRPG_CONFIG_ROOTDIR.$base_sprite_path);
                                        //echo('<pre>$base_sprite_path = '.print_r($base_sprite_path, true).'</pre>');
                                        //echo('<pre>$base_sprite_list = '.(!empty($base_sprite_list) ? htmlentities(print_r($base_sprite_list, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
                                        //echo('<pre>$temp_sheets_array = '.(!empty($temp_sheets_array) ? htmlentities(print_r($temp_sheets_array, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                                    }

                                    ?>

                                </div>

                                <div class="panel" data-tab="functions">

                                    <div class="field fullsize codemirror" data-codemirror-mode="php">
                                        <div class="label">
                                            <strong><?= $this_ability_class_short_name_uc ?> Functions</strong>
                                            <em>code is php-format with html allowed in some strings</em>
                                        </div>
                                        <?
                                        // Collect the markup for the ability functions file
                                        if (!empty($_SESSION['ability_functions_markup'][$ability_data['ability_id']])){
                                            $ability_functions_markup = $_SESSION['ability_functions_markup'][$ability_data['ability_id']];
                                            unset($_SESSION['ability_functions_markup'][$ability_data['ability_id']]);
                                        } else {
                                            $template_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.'.ability/functions.php';
                                            $ability_functions_path = MMRPG_CONFIG_ABILITIES_CONTENT_PATH.$ability_data['ability_token'].'/functions.php';
                                            $ability_functions_markup = file_exists($ability_functions_path) ? file_get_contents($ability_functions_path) : file_get_contents($template_functions_path);
                                        }
                                        ?>
                                        <textarea class="textarea" name="ability_functions_markup" rows="<?= min(20, substr_count($ability_functions_markup, PHP_EOL)) ?>"><?= htmlentities(trim($ability_functions_markup), ENT_QUOTES, 'UTF-8', true) ?></textarea>
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
                                            <br />
                                            <code style="color: #05a;">$this_ability</code>
                                            &nbsp;/&nbsp;
                                            <code style="color: #05a;">$target_ability</code>
                                            &nbsp;&nbsp;<a title="ability data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_ABILITIES_CONTENT_PATH).'.ability/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                        </div>
                                        <? if ($this_ability_class !== 'master'){ ?>
                                            <div class="label examples" style=" margin: 0 auto 10px; font-size: 80%;">
                                                <strong>Important Note</strong>:<br />
                                                <code style="color: #cc0000;">Even though this is a <?= $this_ability_class ?>, it is still referred to as a 'ability' in the code!</code><br />
                                                <code style="color: #cc0000;">(Use "ability_id" instead of "<?= $this_ability_class ?>_id", "ability_name" instead of "<?= $this_ability_class ?>_name", etc.)</code>
                                            </div>
                                        <? } ?>
                                    </div>

                                </div>

                            <? } ?>

                        </div>

                        <hr />

                        <? if (!$ability_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Published</strong>
                                        <input type="hidden" name="ability_flag_published" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="ability_flag_published" value="1" <?= !empty($ability_data['ability_flag_published']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_ability_class_short_name ?> is ready to appear on the site</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Complete</strong>
                                        <input type="hidden" name="ability_flag_complete" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="ability_flag_complete" value="1" <?= !empty($ability_data['ability_flag_complete']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_ability_class_short_name ?>'s sprites have been completed</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Hidden</strong>
                                        <input type="hidden" name="ability_flag_hidden" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="ability_flag_hidden" value="1" <?= !empty($ability_data['ability_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This <?= $this_ability_class_short_name ?>'s data should stay hidden</p>
                                </div>

                                <? if (!empty($ability_data['ability_flag_published'])
                                    && !empty($ability_data['ability_flag_complete'])
                                    && $ability_data['ability_class'] == 'master'){ ?>

                                    <div style="clear: both; padding-top: 20px;">

                                        <div class="field checkwrap">
                                            <label class="label">
                                                <strong>Unlockable</strong>
                                                <input type="hidden" name="ability_flag_unlockable" value="0" checked="checked" />
                                                <input class="checkbox" type="checkbox" name="ability_flag_unlockable" value="1" <?= !empty($ability_data['ability_flag_unlockable']) ? 'checked="checked"' : '' ?> />
                                            </label>
                                            <p class="subtext">This <?= $this_ability_class_short_name ?> is ready to be used in the game</p>
                                        </div>

                                    </div>

                                <? } ?>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $ability_data_is_new ? 'Create '.$this_ability_class_short_name_uc : 'Save Changes' ?>" />
                                <? if (!$ability_data_is_new && empty($ability_data['ability_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete <?= $this_ability_class_short_name_uc ?>" data-delete="abilities" data-ability-id="<?= $ability_data['ability_id'] ?>" />
                                <? } ?>
                            </div>
                            <? if (!$ability_data_is_new){ ?>
                                <?= cms_admin::object_editor_print_git_footer_buttons('abilities/'.$this_ability_xclass, $ability_data['ability_token'], $mmrpg_git_file_arrays); ?>
                            <? } ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_ability_data = $ability_data;
                if (isset($debug_ability_data['ability_description2'])){ $debug_ability_data['ability_description2'] = str_replace(PHP_EOL, '\\n', $debug_ability_data['ability_description2']); }
                echo('<pre style="display: none;">$ability_data = '.(!empty($debug_ability_data) ? htmlentities(print_r($debug_ability_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

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