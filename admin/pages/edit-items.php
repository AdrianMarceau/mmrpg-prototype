<? ob_start(); ?>

    <?

    // Pre-check access permissions before continuing
    if (!in_array('*', $this_adminaccess)
        && !in_array('edit-items', $this_adminaccess)){
        $form_messages[] = array('error', 'You do not have permission to edit items!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect an index of type colours for options
    $mmrpg_types_fields = rpg_type::get_index_fields(true);
    $mmrpg_types_index = $db->get_array_list("SELECT {$mmrpg_types_fields} FROM mmrpg_index_types ORDER BY type_order ASC", 'type_token');

    // Collect an index of battle fields for options
    //$mmrpg_fields_fields = rpg_field::get_index_fields(true);
    //$mmrpg_fields_index = $db->get_array_list("SELECT {$mmrpg_fields_fields} FROM mmrpg_index_fields WHERE field_token <> 'field' ORDER BY field_order ASC", 'field_token');

    // Collect an index of player colours for options
    //$mmrpg_players_fields = rpg_player::get_index_fields(true);
    //$mmrpg_players_index = $db->get_array_list("SELECT {$mmrpg_players_fields} FROM mmrpg_index_players WHERE player_token <> 'player' ORDER BY player_order ASC", 'player_token');

    // Collect an index of item colours for options
    //$mmrpg_robots_fields = rpg_robot::get_index_fields(true);
    //$mmrpg_robots_index = $db->get_array_list("SELECT {$mmrpg_robots_fields} FROM mmrpg_index_robots WHERE robot_token <> 'robot' ORDER BY robot_order ASC", 'robot_token');

    // Collect an index of item colours for options
    $mmrpg_items_fields = rpg_item::get_index_fields(true);
    $mmrpg_items_index = $db->get_array_list("SELECT {$mmrpg_items_fields} FROM mmrpg_index_items WHERE item_token <> 'item' AND item_class <> 'system' ORDER BY item_order ASC", 'item_token');

    // Collect an index of contributors and admins that have made sprites
    $mmrpg_contributors_index = cms_admin::get_contributors_index('item');

    // Collect an index of file changes and updates via git
    $mmrpg_git_file_arrays = cms_admin::object_editor_get_git_file_arrays(MMRPG_CONFIG_ITEMS_CONTENT_PATH, array(
        'table' => 'mmrpg_index_items',
        'token' => 'item_token'
        ));

    // Explode the list of git files into separate array vars
    extract($mmrpg_git_file_arrays);


    /* -- Page Script/Style Dependencies  -- */

    // Define the extra stylesheets that must be included for this page
    if (!isset($admin_include_stylesheets)){ $admin_include_stylesheets = ''; }
    $admin_include_stylesheets .= '<link rel="stylesheet" href=".libs/codemirror/lib/codemirror.css?'.MMRPG_CONFIG_CACHE_DATE.'">'.PHP_EOL;

    // Define the extra javascript that must be included for this page
    if (!isset($admin_include_javascript)){ $admin_include_javascript = ''; }
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/lib/codemirror.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/addon/edit/matchbrackets.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/mode/htmlmixed/htmlmixed.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/mode/xml/xml.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/mode/javascript/javascript.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/mode/css/css.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/mode/clike/clike.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;
    $admin_include_javascript .= '<script type="text/javascript" src=".libs/codemirror/mode/php/php.js?'.MMRPG_CONFIG_CACHE_DATE.'"></script>'.PHP_EOL;


    /* -- Form Setup Actions -- */

    // Define a function for exiting a item edit action
    function exit_item_edit_action($item_id = false){
        if ($item_id !== false){ $location = 'admin/edit-items/editor/item_id='.$item_id; }
        else { $location = 'admin/edit-items/search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Items | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['item_id'])){

        // Collect form data for processing
        $delete_data['item_id'] = !empty($_GET['item_id']) && is_numeric($_GET['item_id']) ? trim($_GET['item_id']) : '';

        // Let's delete all of this item's data from the database
        if (!empty($delete_data['item_id'])){
            $delete_data['item_token'] = $db->get_value("SELECT item_token FROM mmrpg_index_items WHERE item_id = {$delete_data['item_id']};", 'item_token');
            if (!empty($delete_data['item_token'])){ $files_deleted = cms_admin::object_editor_delete_json_data_file('item', $delete_data['item_token'], true); }
            $db->delete('mmrpg_index_items', array('item_id' => $delete_data['item_id'], 'item_flag_protected' => 0));
            $form_messages[] = array('success', 'The requested item has been deleted from the database'.(!empty($files_deleted) ? ' and file system' : ''));
            exit_form_action('success');
        } else {
            $form_messages[] = array('success', 'The requested item does not exist in the database');
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
        $sort_data = array('name' => 'item_order', 'dir' => 'asc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['item_id'] = !empty($_GET['item_id']) && is_numeric($_GET['item_id']) ? trim($_GET['item_id']) : '';
        $search_data['item_name'] = !empty($_GET['item_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['item_name']) ? trim(strtolower($_GET['item_name'])) : '';
        $search_data['item_type'] = !empty($_GET['item_type']) && preg_match('/[-_0-9a-z]+/i', $_GET['item_type']) ? trim(strtolower($_GET['item_type'])) : '';
        $search_data['item_subclass'] = !empty($_GET['item_subclass']) && preg_match('/[-_0-9a-z]+/i', $_GET['item_subclass']) ? trim(strtolower($_GET['item_subclass'])) : '';
        $search_data['item_flavour'] = !empty($_GET['item_flavour']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['item_flavour']) ? trim($_GET['item_flavour']) : '';
        $search_data['item_game'] = !empty($_GET['item_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['item_game']) ? trim(strtoupper($_GET['item_game'])) : '';
        $search_data['item_group'] = !empty($_GET['item_group']) && preg_match('/[-_0-9a-z\/]+/i', $_GET['item_group']) ? trim($_GET['item_group']) : '';
        $search_data['item_flag_hidden'] = isset($_GET['item_flag_hidden']) && $_GET['item_flag_hidden'] !== '' ? (!empty($_GET['item_flag_hidden']) ? 1 : 0) : '';
        $search_data['item_flag_complete'] = isset($_GET['item_flag_complete']) && $_GET['item_flag_complete'] !== '' ? (!empty($_GET['item_flag_complete']) ? 1 : 0) : '';
        $search_data['item_flag_unlockable'] = isset($_GET['item_flag_unlockable']) && $_GET['item_flag_unlockable'] !== '' ? (!empty($_GET['item_flag_unlockable']) ? 1 : 0) : '';
        $search_data['item_flag_published'] = isset($_GET['item_flag_published']) && $_GET['item_flag_published'] !== '' ? (!empty($_GET['item_flag_published']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_append_git_statuses($search_data, 'item');

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_item_fields = rpg_item::get_index_fields(true, 'item');
        $search_query = "SELECT
            {$temp_item_fields}
            FROM mmrpg_index_items AS item
            WHERE 1=1
            AND item_token <> 'item'
            AND item_class <> 'system'
            ";

        // If the item ID was provided, we can search by exact match
        if (!empty($search_data['item_id'])){
            $item_id = $search_data['item_id'];
            $search_query .= "AND item_id = {$item_id} ";
            $search_results_limit = false;
        }

        // Else if the item name was provided, we can use wildcards
        if (!empty($search_data['item_name'])){
            $item_name = $search_data['item_name'];
            $item_name = str_replace(array(' ', '*', '%'), '%', $item_name);
            $item_name = preg_replace('/%+/', '%', $item_name);
            $item_name = '%'.$item_name.'%';
            $search_query .= "AND (item_name LIKE '{$item_name}' OR item_token LIKE '{$item_name}') ";
            $search_results_limit = false;
        }

        // Else if the item type was provided, we can use wildcards
        if (!empty($search_data['item_type'])){
            $item_type = $search_data['item_type'];
            if ($item_type !== 'none'){ $search_query .= "AND (item_type LIKE '{$item_type}' OR item_type2 LIKE '{$item_type}') "; }
            else { $search_query .= "AND item_type = '' "; }
            $search_results_limit = false;
        }

        // If the item class was provided
        if (!empty($search_data['item_subclass'])){
            $search_query .= "AND item_subclass = '{$search_data['item_subclass']}' ";
            $search_results_limit = false;
        }

        // Else if the item flavour was provided, we can use wildcards
        if (!empty($search_data['item_flavour'])){
            $item_flavour = $search_data['item_flavour'];
            $item_flavour = str_replace(array(' ', '*', '%'), '%', $item_flavour);
            $item_flavour = preg_replace('/%+/', '%', $item_flavour);
            $item_flavour = '%'.$item_flavour.'%';
            $search_query .= "AND (
                item_description LIKE '{$item_flavour}'
                OR item_description2 LIKE '{$item_flavour}'
                OR item_description_use LIKE '{$item_flavour}'
                OR item_description_hold LIKE '{$item_flavour}'
                OR item_description_shop LIKE '{$item_flavour}'
                ) ";
            $search_results_limit = false;
        }

        // If the item game was provided
        if (!empty($search_data['item_game'])){
            $search_query .= "AND item_game = '{$search_data['item_game']}' ";
            $search_results_limit = false;
        }

        // If the item group was provided
        if (!empty($search_data['item_group'])){
            $search_query .= "AND item_group = '{$search_data['item_group']}' ";
            $search_results_limit = false;
        }

        // If the item hidden flag was provided
        if ($search_data['item_flag_hidden'] !== ''){
            $search_query .= "AND item_flag_hidden = {$search_data['item_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the item complete flag was provided
        if ($search_data['item_flag_complete'] !== ''){
            $search_query .= "AND item_flag_complete = {$search_data['item_flag_complete']} ";
            $search_results_limit = false;
        }

        // If the item unlockable flag was provided
        if ($search_data['item_flag_unlockable'] !== ''){
            $search_query .= "AND item_flag_unlockable = {$search_data['item_flag_unlockable']} ";
            $search_results_limit = false;
        }

        // If the item published flag was provided
        if ($search_data['item_flag_published'] !== ''){
            $search_query .= "AND item_flag_published = {$search_data['item_flag_published']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "item_name ASC";
        $order_by[] = "item_id ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;
        cms_admin::object_index_search_results_filter_git_statuses($search_results, $search_results_count, $search_data, 'item', $mmrpg_git_file_arrays);

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(item_id) AS total FROM mmrpg_index_items WHERE 1=1 AND item_token <> 'item';", 'total');

    }

    // If we're in editor mode, we should collect item info from database
    $item_data = array();
    $item_data_is_new = false;
    $editor_data = array();
    if ($sub_action == 'editor'
        && isset($_GET['item_id'])
        ){

        // Collect form data for processing
        $editor_data['item_id'] = !empty($_GET['item_id']) && is_numeric($_GET['item_id']) ? trim($_GET['item_id']) : '';

        /* -- Collect Item Data -- */

        // Collect item details from the database
        $temp_item_fields = rpg_item::get_index_fields(true);
        if (!empty($editor_data['item_id'])){
            $item_data = $db->get_array("SELECT {$temp_item_fields} FROM mmrpg_index_items WHERE item_id = {$editor_data['item_id']};");
        } else {

            // Generate temp data structure for the new challenge
            $item_data_is_new = true;
            $admin_id = $_SESSION['admin_id'];
            $item_data = array(
                'item_id' => 0,
                'item_token' => '',
                'item_name' => '',
                'item_class' => 'item',
                'item_subclass' => '',
                'item_type' => '',
                'item_type2' => '',
                'item_target' => '',
                'item_flag_hidden' => 0,
                'item_flag_complete' => 0,
                'item_flag_published' => 0,
                'item_flag_unlockable' => 0,
                'item_flag_protected' => 0,
                'item_order' => 0
                );

            // Overwrite temp data with any backup data provided
            if (!empty($backup_form_data)){
                foreach ($backup_form_data AS $f => $v){
                    $item_data[$f] = $v;
                }
            }

        }

        // If item data could not be found, produce error and exit
        if (empty($item_data)){ exit_item_edit_action(); }

        // Collect the item's name(s) for display
        $item_name_display = $item_data['item_name'];
        if ($item_data_is_new){ $this_page_tabtitle = 'New Item | '.$this_page_tabtitle; }
        else { $this_page_tabtitle = $item_name_display.' | '.$this_page_tabtitle; }

        // If form data has been submit for this item, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-items'){

            // COLLECT form data from the request and parse out simple rules

            $old_item_token = !empty($_POST['old_item_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_item_token']) ? trim(strtolower($_POST['old_item_token'])) : '';

            $form_data['item_id'] = !empty($_POST['item_id']) && is_numeric($_POST['item_id']) ? trim($_POST['item_id']) : 0;
            $form_data['item_token'] = !empty($_POST['item_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['item_token']) ? trim(strtolower($_POST['item_token'])) : '';
            $form_data['item_name'] = !empty($_POST['item_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['item_name']) ? trim($_POST['item_name']) : '';
            $form_data['item_class'] = 'item';
            $form_data['item_subclass'] = !empty($_POST['item_subclass']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['item_subclass']) ? trim(strtolower($_POST['item_subclass'])) : '';
            $form_data['item_type'] = !empty($_POST['item_type']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['item_type']) ? trim(strtolower($_POST['item_type'])) : '';
            $form_data['item_type2'] = !empty($_POST['item_type2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['item_type2']) ? trim(strtolower($_POST['item_type2'])) : '';
            $form_data['item_target'] = !empty($_POST['item_target']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['item_target']) ? trim($_POST['item_target']) : '';

            $form_data['item_price'] = !empty($_POST['item_price']) && is_numeric($_POST['item_price']) ? (int)(trim($_POST['item_price'])) : 0;
            $form_data['item_value'] = !empty($_POST['item_value']) && is_numeric($_POST['item_value']) ? (int)(trim($_POST['item_value'])) : 0;

            $form_data['item_game'] = !empty($_POST['item_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['item_game']) ? trim($_POST['item_game']) : '';
            $form_data['item_group'] = !empty($_POST['item_group']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['item_group']) ? trim($_POST['item_group']) : '';
            $form_data['item_order'] = !empty($_POST['item_order']) && is_numeric($_POST['item_order']) ? (int)(trim($_POST['item_order'])) : 0;

            $form_data['item_energy'] = 0;
            $form_data['item_speed'] = 10;
            $form_data['item_accuracy'] = 100;

            $form_data['item_damage'] = !empty($_POST['item_damage']) && is_numeric($_POST['item_damage']) ? (int)(trim($_POST['item_damage'])) : 0;
            $form_data['item_damage_percent'] = isset($_POST['item_damage_percent']) && is_numeric($_POST['item_damage_percent']) ? (int)(trim($_POST['item_damage_percent'])) : 0;
            $form_data['item_damage2'] = !empty($_POST['item_damage2']) && is_numeric($_POST['item_damage2']) ? (int)(trim($_POST['item_damage2'])) : 0;
            $form_data['item_damage2_percent'] = isset($_POST['item_damage2_percent']) && is_numeric($_POST['item_damage2_percent']) ? (int)(trim($_POST['item_damage2_percent'])) : 0;
            $form_data['item_recovery'] = !empty($_POST['item_recovery']) && is_numeric($_POST['item_recovery']) ? (int)(trim($_POST['item_recovery'])) : 0;
            $form_data['item_recovery_percent'] = isset($_POST['item_recovery_percent']) && is_numeric($_POST['item_recovery_percent']) ? (int)(trim($_POST['item_recovery_percent'])) : 0;
            $form_data['item_recovery2'] = !empty($_POST['item_recovery2']) && is_numeric($_POST['item_recovery2']) ? (int)(trim($_POST['item_recovery2'])) : 0;
            $form_data['item_recovery2_percent'] = isset($_POST['item_recovery2_percent']) && is_numeric($_POST['item_recovery2_percent']) ? (int)(trim($_POST['item_recovery2_percent'])) : 0;

            $form_data['item_description'] = !empty($_POST['item_description']) ? trim(strip_tags($_POST['item_description'])) : '';
            $form_data['item_description2'] = !empty($_POST['item_description2']) ? trim(strip_tags($_POST['item_description2'])) : '';
            $form_data['item_description_use'] = !empty($_POST['item_description_use']) ? trim(strip_tags($_POST['item_description_use'])) : '';
            $form_data['item_description_hold'] = !empty($_POST['item_description_hold']) ? trim(strip_tags($_POST['item_description_hold'])) : '';
            $form_data['item_description_shop'] = !empty($_POST['item_description_shop']) ? trim(strip_tags($_POST['item_description_shop'])) : '';

            $form_data['item_image'] = !empty($_POST['item_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['item_image']) ? trim(strtolower($_POST['item_image'])) : '';
            $form_data['item_image_size'] = !empty($_POST['item_image_size']) && is_numeric($_POST['item_image_size']) ? (int)(trim($_POST['item_image_size'])) : 0;
            $form_data['item_image_editor'] = !empty($_POST['item_image_editor']) && is_numeric($_POST['item_image_editor']) ? (int)(trim($_POST['item_image_editor'])) : 0;
            $form_data['item_image_editor2'] = !empty($_POST['item_image_editor2']) && is_numeric($_POST['item_image_editor2']) ? (int)(trim($_POST['item_image_editor2'])) : 0;
            $form_data['item_image_sheets'] = !empty($_POST['item_image_sheets']) && is_numeric($_POST['item_image_sheets']) ? (int)(trim($_POST['item_image_sheets'])) : 0;

            $form_data['item_flag_published'] = isset($_POST['item_flag_published']) && is_numeric($_POST['item_flag_published']) ? (int)(trim($_POST['item_flag_published'])) : 0;
            $form_data['item_flag_complete'] = isset($_POST['item_flag_complete']) && is_numeric($_POST['item_flag_complete']) ? (int)(trim($_POST['item_flag_complete'])) : 0;
            $form_data['item_flag_hidden'] = isset($_POST['item_flag_hidden']) && is_numeric($_POST['item_flag_hidden']) ? (int)(trim($_POST['item_flag_hidden'])) : 0;
            $form_data['item_flag_unlockable'] = isset($_POST['item_flag_unlockable']) && is_numeric($_POST['item_flag_unlockable']) ? (int)(trim($_POST['item_flag_unlockable'])) : 0;

            $form_data['item_functions_markup'] = !empty($_POST['item_functions_markup']) ? trim($_POST['item_functions_markup']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If this is a NEW item, auto-generate the token when not provided
            if ($item_data_is_new
                && empty($form_data['item_token'])
                && !empty($form_data['item_name'])){
                $auto_token = strtolower($form_data['item_name']);
                $auto_token = preg_replace('/\s+/', '-', $auto_token);
                $auto_token = preg_replace('/[^-a-z0-9]+/i', '', $auto_token);
                $form_data['item_token'] = $auto_token;
            }

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (!$item_data_is_new && empty($form_data['item_id'])){ $form_messages[] = array('error', 'Item ID was not provided'); $form_success = false; }
            if (empty($form_data['item_token']) || (!$item_data_is_new && empty($old_item_token))){ $form_messages[] = array('error', 'Item Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['item_name'])){ $form_messages[] = array('error', 'Item Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['item_subclass'])){ $form_messages[] = array('error', 'Item Kind was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['item_type']) || !isset($_POST['item_type2'])){ $form_messages[] = array('warning', 'Item Types were not provided or were invalid'); $form_success = false; }
            if (!$form_success){ exit_item_edit_action($form_data['item_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (!$item_data_is_new && empty($form_data['item_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            //if (empty($form_data['item_master'])){ $form_messages[] = array('warning', 'Source Robot was not provided and may cause issues on the front-end'); }
            if (!$item_data_is_new && empty($form_data['item_group'])){ $form_messages[] = array('warning', 'Sorting Group was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if ($form_data['item_flag_unlockable']){
                if (!$form_data['item_flag_published']){ $form_messages[] = array('warning', 'Item must be published to be unlockable'); $form_data['item_flag_unlockable'] = 0; }
                elseif (!$form_data['item_flag_complete']){ $form_messages[] = array('warning', 'Item must be complete to be unlockable'); $form_data['item_flag_unlockable'] = 0; }
                elseif (empty($form_data['item_description'])){ $form_messages[] = array('warning', 'Item must have a description to be unlockable'); $form_data['item_flag_unlockable'] = 0; }
            }

            // Only parse the following fields if NOT new object data
            if (!$item_data_is_new){

                $empty_image_folders = array();

                $item_image_sheets_actions = !empty($_POST['item_image_sheets_actions']) && is_array($_POST['item_image_sheets_actions']) ? array_filter($_POST['item_image_sheets_actions']) : array();
                foreach ($item_image_sheets_actions AS $sheet_num => $sheet_actions){ $item_image_sheets_actions[$sheet_num] = array_filter($sheet_actions); }
                $item_image_sheets_actions = array_filter($item_image_sheets_actions);
                if (!empty($item_image_sheets_actions)){
                    foreach ($item_image_sheets_actions AS $sheet_num => $sheet_actions){
                        if (!empty($sheet_actions['delete_images'])){
                            $sheet_path = ($sheet_num > 1 ? '_'.$sheet_num : '');
                            $delete_sprite_path = 'content/items/'.$item_data['item_image'].'/sprites'.$sheet_path.'/';
                            $empty_image_folders[] = $delete_sprite_path;
                        }

                    }
                }
                //$form_messages[] = array('alert', '<pre>$item_image_sheets_actions  = '.print_r($item_image_sheets_actions, true).'</pre>');

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
                if (empty($form_data['item_functions_markup'])){
                    // Functions code is EMPTY and will be ignored
                    $form_messages[] = array('warning', 'Item functions code was empty and was not saved (reverted to original)');
                } elseif (!cms_admin::is_valid_php_syntax($form_data['item_functions_markup'])){
                    // Functions code is INVALID and must be fixed
                    $form_messages[] = array('warning', 'Item functions code was invalid PHP syntax and was not saved (please fix and try again)');
                    $_SESSION['item_functions_markup'][$item_data['item_id']] = $form_data['item_functions_markup'];
                } else {
                    // Functions code is OKAY and can be saved
                    $item_functions_path = MMRPG_CONFIG_ITEMS_CONTENT_PATH.$item_data['item_token'].'/functions.php';
                    $old_item_functions_markup = file_exists($item_functions_path) ? normalize_file_markup(file_get_contents($item_functions_path)) : '';
                    $new_item_functions_markup = normalize_file_markup($form_data['item_functions_markup']);
                    if (empty($old_item_functions_markup) || $new_item_functions_markup !== $old_item_functions_markup){
                        $f = fopen($item_functions_path, 'w');
                        fwrite($f, $new_item_functions_markup);
                        fclose($f);
                        $form_messages[] = array('alert', 'Item functions file was '.(!empty($old_field_functions_markup) ? 'updated' : 'created'));
                    }
                }

            }
            // Otherwise, if NEW data, pre-populate certain fields
            else {

                $form_data['item_game'] = 'MMRPG';
                $form_data['item_group'] = 'MMRPG/Items/Misc';
                $form_data['item_order'] = 1 + $db->get_value("SELECT MAX(item_order) AS max FROM mmrpg_index_items;", 'max');

            }

            // Regardless, unset the markup variable so it's not save to the database
            unset($form_data['item_functions_markup']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$item_data = '.print_r($item_data, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            /*
            foreach ($form_data AS $key => $value1){
                $value2 = $item_data[$key];
                if ($value1 === '[]'){ $value1 = ''; }
                if ($value2 === '[]'){ $value2 = ''; }
                if ($value1 != $value2){ $form_messages[] = array('error', '<pre>'.
                    '$form_data['.$key.'] != $item_data['.$key.']'.PHP_EOL.
                    $value1.PHP_EOL.
                    $value2.PHP_EOL.
                    '</pre>'); }
            }
            foreach ($form_data AS $key => $value){
                if (!isset($item_data[$key])){
                    $form_messages[] = array('error', '$form_data['.$key.'] should not be here');
                }
            }
            foreach ($item_data AS $key => $value){
                if (!empty($value) && !isset($form_data[$key])){
                    $form_messages[] = array('error', '$form_data['.$key.'] not provided');
                }
            }
            exit_item_edit_action($form_data['item_id']);
            */

            // Make a copy of the update data sans the item ID
            $update_data = $form_data;
            unset($update_data['item_id']);

            // If this is a new item we insert, otherwise we update the existing
            if ($item_data_is_new){

                // Update the main database index with changes to this item's data
                $update_data['item_flag_protected'] = 0;
                $insert_results = $db->insert('mmrpg_index_items', $update_data);

                // If we made it this far, the update must have been a success
                if ($insert_results !== false){ $form_success = true; $form_messages[] = array('success', 'Item data was created successfully!'); }
                else { $form_success = false; $form_messages[] = array('error', 'Item data could not be created...'); }

                // If the form was a success, collect the new ID for the redirect
                if ($form_success){
                    $new_item_id = $db->get_value("SELECT MAX(item_id) AS max FROM mmrpg_index_items;", 'max');
                    $form_data['item_id'] = $new_item_id;
                }

            } else {

                // Update the main database index with changes to this item's data
                $update_results = $db->update('mmrpg_index_items', $update_data, array('item_id' => $form_data['item_id']));

                // If we made it this far, the update must have been a success
                if ($update_results !== false){ $form_messages[] = array('success', 'Item data was updated successfully!'); }
                else { $form_messages[] = array('error', 'Item data could not be updated...'); }

            }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If successful, we need to update the JSON file
            if ($form_success){
                if ($item_data_is_new){ $item_data['item_id'] = $new_item_id; }
                cms_admin::object_editor_update_json_data_file('item', array_merge($item_data, $update_data));
            }

            // If the item tokens have changed, we must move the entire folder
            if ($form_success
                && !$item_data_is_new
                && $old_item_token !== $update_data['item_token']){
                $old_content_path = MMRPG_CONFIG_ITEMS_CONTENT_PATH.$old_item_token.'/';
                $new_content_path = MMRPG_CONFIG_ITEMS_CONTENT_PATH.$update_data['item_token'].'/';
                if (rename($old_content_path, $new_content_path)){
                    $path_string = '<strong>'.mmrpg_clean_path($old_content_path).'</strong> &raquo; <strong>'.mmrpg_clean_path($new_content_path).'</strong>';
                    $form_messages[] = array('alert', 'Item directory renamed! '.$path_string);
                } else {
                    $form_messages[] = array('error', 'Unable to rename item directory!');
                }
            }

            // We're done processing the form, we can exit
            if (empty($form_data['item_id'])){ exit_item_edit_action(false); }
            else { exit_item_edit_action($form_data['item_id']); }

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }


    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/edit-items/">Edit Items</a>
        <? if ($sub_action == 'editor' && !empty($item_data)): ?>
            &raquo; <a href="admin/edit-items/editor/item_id=<?= $item_data['item_id'] ?>"><?= !empty($item_name_display) ? $item_name_display : 'New Item' ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-items edit-items" data-baseurl="admin/edit-items/" data-object="item" data-xobject="items">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Items</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="item_id" value="<?= !empty($search_data['item_id']) ? $search_data['item_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="item_name" placeholder="" value="<?= !empty($search_data['item_name']) ? htmlentities($search_data['item_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <? /*
                    <div class="field">
                        <strong class="label">By Class</strong>
                        <select class="select" name="item_class">
                            <option value=""></option>
                            <option value="mecha"<?= !empty($search_data['item_class']) && $search_data['item_class'] === 'mecha' ? ' selected="selected"' : '' ?>>Mecha</option>
                            <option value="master"<?= !empty($search_data['item_class']) && $search_data['item_class'] === 'master' ? ' selected="selected"' : '' ?>>Master</option>
                            <option value="boss"<?= !empty($search_data['item_class']) && $search_data['item_class'] === 'boss' ? ' selected="selected"' : '' ?>>Boss</option>
                        </select><span></span>
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Type</strong>
                        <select class="select" name="item_type"><option value=""></option><?
                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                //if ($type_info['type_class'] === 'special' && $type_token !== 'none'){ continue; }
                                ?><option value="<?= $type_token ?>"<?= !empty($search_data['item_type']) && $search_data['item_type'] === $type_token ? ' selected="selected"' : '' ?>><?= $type_token === 'none' ? 'Neutral' : ucfirst($type_token) ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Kind</strong>
                        <select class="select" name="item_subclass"><?
                            $item_subclasses_tokens = $db->get_array_list("SELECT DISTINCT (item_subclass) AS subclass_token FROM mmrpg_index_items WHERE item_subclass <> '' ORDER BY item_subclass ASC;");
                            foreach ($item_subclasses_tokens AS $subclass_key => $subclass_info){
                                $subclass_token = $subclass_info['subclass_token'];
                                ?><option value="<?= $subclass_token ?>"<?= !empty($search_data['item_subclass']) && $search_data['item_subclass'] === $subclass_token ? ' selected="selected"' : '' ?>><?= ucfirst($subclass_token) ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Group</strong>
                        <select class="select" name="item_group"><option value=""></option><?
                            $item_groups_tokens = $db->get_array_list("SELECT DISTINCT (item_group) AS group_token FROM mmrpg_index_items ORDER BY item_group ASC;");
                            foreach ($item_groups_tokens AS $group_key => $group_info){
                                $group_token = $group_info['group_token'];
                                ?><option value="<?= $group_token ?>"<?= !empty($search_data['item_group']) && $search_data['item_group'] === $group_token ? ' selected="selected"' : '' ?>><?= $group_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Flavour</strong>
                        <input class="textbox" type="text" name="item_flavour" placeholder="" value="<?= !empty($search_data['item_flavour']) ? htmlentities($search_data['item_flavour'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
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
                        $flag_name = 'item_flag_'.$flag_token;
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
                        <input class="button reset" type="reset" value="Reset" onclick="javascript:window.location.href='admin/edit-items/';" />
                        <a class="button new" href="<?= 'admin/edit-items/editor/item_id=0' ?>">Create New Item</a>
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
                            <col class="type" width="130" />
                            <col class="kind" width="130" />
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag unlockable" width="80" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('item_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('item_name', 'Name') ?></th>
                                <th class="type"><?= cms_admin::get_sort_link('item_type', 'Type(s)') ?></th>
                                <th class="kind"><?= cms_admin::get_sort_link('item_subclass', 'Kind') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('item_flag_published', 'Published') ?></th>
                                <th class="flag complete"><?= cms_admin::get_sort_link('item_flag_complete', 'Complete') ?></th>
                                <th class="flag unlockable"><?= cms_admin::get_sort_link('item_flag_unlockable', 'Unlockable') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('item_flag_hidden', 'Hidden') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head type"></th>
                                <th class="head kind"></th>
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
                                <td class="foot kind"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag complete"></td>
                                <td class="foot flag unlockable"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            $temp_subclass_colours = array(
                                '' => array('none', ''),
                                'consumable' => array('energy', '<i class="fas fa-apple-alt"></i>'),
                                'collectible' => array('defense', '<i class="fas fa-cubes"></i>'),
                                'holdable' => array('attack', '<i class="fas fa-archive"></i>'),
                                'treasure' => array('electric', '<i class="fas fa-gem"></i>'),
                                'event' => array('time', '<i class="fas fa-key"></i>'),
                                );
                            foreach ($search_results AS $key => $item_data){

                                $item_id = $item_data['item_id'];
                                $item_token = $item_data['item_token'];
                                $item_name = $item_data['item_name'];
                                $item_type = !empty($item_data['item_type']) ? ucfirst($item_data['item_type']) : 'Neutral';
                                $item_type_span = '<span class="type_span type_'.(!empty($item_data['item_type']) ? $item_data['item_type'] : 'none').'">'.$item_type.'</span>';

                                if (!empty($item_data['item_type'])
                                    && !empty($item_data['item_type2'])){
                                    $item_type .= ' / '.ucfirst($item_data['item_type2']);
                                    $item_type_span = '<span class="type_span type_'.$item_data['item_type'].'_'.$item_data['item_type2'].'">'.ucwords($item_data['item_type'].' / '.$item_data['item_type2']).'</span>';
                                }

                                $item_subclass = ucfirst($item_data['item_subclass']);
                                $item_subclass_span = '<span class="type_span type_'.$temp_subclass_colours[$item_data['item_subclass']][0].'">'.$item_subclass.' '.$temp_subclass_colours[$item_data['item_subclass']][1].'</span>';

                                $item_flag_published = !empty($item_data['item_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $item_flag_complete = !empty($item_data['item_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $item_flag_unlockable = !empty($item_data['item_flag_unlockable']) ? '<i class="fas fa-unlock"></i>' : '-';
                                $item_flag_hidden = !empty($item_data['item_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $item_edit_url = 'admin/edit-items/editor/item_id='.$item_id;
                                $item_name_link = '<a class="link" href="'.$item_edit_url.'">'.$item_name.'</a>';
                                cms_admin::object_index_links_append_git_statues($item_name_link, $item_token, $mmrpg_git_file_arrays);

                                $item_actions = '';
                                $item_actions .= '<a class="link edit" href="'.$item_edit_url.'"><span>edit</span></a>';
                                if (empty($item_data['item_flag_protected'])){
                                    $item_actions .= '<a class="link delete" data-delete="items" data-item-id="'.$item_id.'"><span>delete</span></a>';
                                }

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$item_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$item_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="type"><div class="wrap">'.$item_type_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="kind"><div class="wrap">'.$item_subclass_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$item_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$item_flag_complete.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag unlockable"><div>'.$item_flag_unlockable.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$item_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$item_actions.'</div></td>'.PHP_EOL;
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
            && isset($_GET['item_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= (!empty($item_data['item_type']) ? $item_data['item_type'] : 'none').(!empty($item_data['item_type2']) ? '_'.$item_data['item_type2'] : '') ?>" data-auto="field-type" data-field-type="item_type,item_type2">
                        <span class="title"><?= !empty($item_name_display) ? 'Edit Item &quot;'.$item_name_display.'&quot;' : 'Create New Item' ?></span>
                        <?

                        // Print out any git-related statues to this header
                        cms_admin::object_editor_header_echo_git_statues($item_data['item_token'], $mmrpg_git_file_arrays);

                        // If the item is published, generate and display a preview link
                        if (!empty($item_data['item_flag_published'])){
                            $preview_link = 'database/items/';
                            $preview_link .= $item_data['item_token'].'/';
                            echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <? if (!$item_data_is_new){ ?>
                        <div class="editor-tabs" data-tabgroup="item">
                            <a class="tab active" data-tab="basic">Basic</a><span></span>
                            <a class="tab" data-tab="sprites">Sprites</a><span></span>
                            <a class="tab" data-tab="functions">Functions</a><span></span>
                        </div>
                    <? } ?>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit-items" />
                        <input type="hidden" name="subaction" value="editor" />

                        <input type="hidden" name="item_class" value="item" />

                        <div class="editor-panels" data-tabgroup="item">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label">Item ID</strong>
                                    <input type="hidden" name="item_id" value="<?= $item_data['item_id'] ?>" />
                                    <input class="textbox" type="text" name="item_id" value="<?= $item_data['item_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Item Token</strong>
                                        <?= !empty($item_data['item_flag_protected']) ? '<em>cannot be changed</em>' : '' ?>
                                    </div>
                                    <input type="hidden" name="old_item_token" value="<?= $item_data['item_token'] ?>" />
                                    <input type="hidden" name="item_token" value="<?= $item_data['item_token'] ?>" />
                                    <input class="textbox" type="text" name="item_token" value="<?= $item_data['item_token'] ?>" maxlength="64" <?= !empty($item_data['item_flag_protected']) ? 'disabled="disabled"' : '' ?> />
                                </div>

                                <div class="field">
                                    <strong class="label">Item Name</strong>
                                    <input class="textbox" type="text" name="item_name" value="<?= $item_data['item_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field has2cols">
                                    <strong class="label">
                                        Type(s)
                                        <span class="type_span type_<?= (!empty($item_data['item_type']) ? $item_data['item_type'] : 'none').(!empty($item_data['item_type2']) ? '_'.$item_data['item_type2'] : '') ?> swatch floatright" data-auto="field-type" data-field-type="item_type,item_type2">&nbsp;</span>
                                    </strong>
                                    <div class="subfield">
                                        <select class="select" name="item_type">
                                            <option value=""<?= empty($item_data['item_type']) ? ' selected="selected"' : '' ?>>Neutral</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                //if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($item_data['item_type']) && $item_data['item_type'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <select class="select" name="item_type2">
                                            <option value=""<?= empty($item_data['item_type2']) ? ' selected="selected"' : '' ?>>-</option>
                                            <?
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                //if ($type_info['type_class'] === 'special'){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($item_data['item_type2']) && $item_data['item_type2'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                </div>

                                <div class="field">
                                    <strong class="label">Kind</strong>
                                    <select class="select" name="item_subclass">
                                        <option value="" <?= empty($item_data['item_subclass']) ? 'selected="selected"' : '' ?>>- select kind -</option>
                                        <option value="consumable" <?= $item_data['item_subclass'] == 'consumable' ? 'selected="selected"' : '' ?>>Consumable</option>
                                        <option value="collective" <?= $item_data['item_subclass'] == 'collective' ? 'selected="selected"' : '' ?>>Collective</option>
                                        <option value="holdable" <?= $item_data['item_subclass'] == 'holdable' ? 'selected="selected"' : '' ?>>Holdable</option>
                                        <option value="treasure" <?= $item_data['item_subclass'] == 'treasure' ? 'selected="selected"' : '' ?>>Treasure</option>
                                        <option value="event" <?= $item_data['item_subclass'] == 'event' ? 'selected="selected"' : '' ?>>Event</option>
                                    </select><span></span>
                                </div>

                                <? if (!$item_data_is_new){ ?>

                                    <div class="field">
                                        <strong class="label">Target</strong>
                                        <select class="select" name="item_target">
                                            <option value="auto" <?= empty($item_data['item_target']) || $item_data['item_target'] == 'auto' ? 'selected="selected"' : '' ?>>Auto</option>
                                            <option value="select_target" <?= $item_data['item_target'] == 'select_target' ? 'selected="selected"' : '' ?>>Select Target (Enemy Side)</option>
                                            <option value="select_this" <?= $item_data['item_target'] == 'select_this' ? 'selected="selected"' : '' ?>>Select Target (Player Side)</option>
                                            <option value="select_this_ally" <?= $item_data['item_target'] == 'select_this_ally' ? 'selected="selected"' : '' ?>>Select Ally (Player Side)</option>
                                            <option value="select_this_disabled" <?= $item_data['item_target'] == 'select_this_disabled' ? 'selected="selected"' : '' ?>>Select Disabled (Player Side)</option>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <strong class="label">Price <em>zenny price when purchased in shop / point value will be auto-calculated</em></strong>
                                        <input class="hidden" type="hidden" name="item_price" value="0" />
                                        <input class="textbox" type="number" name="item_price" value="<?= $item_data['item_price'] ?>" maxlength="8" min="0" step="100" />
                                    </div>

                                    <div class="field halfsize">
                                        <strong class="label">Value <em>battle point value on leaderboard / sell price will be auto-calculated</strong>
                                        <input class="hidden" type="hidden" name="item_value" value="0" />
                                        <input class="textbox" type="number" name="item_value" value="<?= $item_data['item_value'] ?>" maxlength="8" min="0" step="100" />
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
                                            $field_name = 'item_'.$token;
                                            $field_percent_name = $field_name.'_percent';
                                            $is_percent = !empty($item_data[$field_percent_name]) ? true : false;
                                            ?>
                                            <div class="field foursize has_unit has_unit_checkbox">
                                                <strong class="label"><span class="type_span type_<?= $power_colour ?>"><?= $name ?></span> <em><?= $note ?></em></strong>
                                                <input class="textbox" type="number" name="<?= $field_name ?>" value="<?= $item_data[$field_name] ?>" maxlength="8" min="0" step="1" />
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

                                    <div class="field fullsize" style="">
                                        <div class="label">
                                            <strong>Item Description</strong>
                                            <em>short paragraph describing what this item does and its effects</em>
                                        </div>
                                        <textarea class="textarea" name="item_description" rows="4"><?= htmlentities($item_data['item_description'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                        <div class="label examples" style="font-size: 80%; padding-top: 4px; margin-bottom: 0;">
                                            <strong>Dynamic Values</strong>:
                                            <br />
                                            <code style="color: green;">{DAMAGE}</code>, <code style="color: green;">{DAMAGE2}</code>,
                                            <code style="color: green;">{RECOVERY}</code>, <code style="color: green;">{RECOVERY2}</code>
                                        </div>
                                    </div>

                                    <div class="field fullsize" style="padding-bottom: 0;">
                                        <div class="label">
                                            <strong>&quot;Use&quot; Description</strong>
                                            <em>appended to item description in database or when viewed from the battle menu</em>
                                        </div>
                                        <textarea class="textarea" name="item_description_use" rows="2"><?= htmlentities($item_data['item_description_use'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <div class="field fullsize" style="padding-bottom: 0;">
                                        <div class="label">
                                            <strong>&quot;Hold&quot; Description</strong>
                                            <em>appended to item description in database or when viewed from the robot editor</em>
                                        </div>
                                        <textarea class="textarea" name="item_description_hold" rows="2"><?= htmlentities($item_data['item_description_hold'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <div class="field fullsize" style="padding-bottom: 0;">
                                        <div class="label">
                                            <strong>&quot;Shop&quot; Description</strong>
                                            <em>appended to item description in database or when viewed from inside the shop</em>
                                        </div>
                                        <textarea class="textarea" name="item_description_shop" rows="2"><?= htmlentities($item_data['item_description_shop'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                    </div>

                                    <hr />

                                    <div class="field foursize">
                                        <strong class="label">Source Game</strong>
                                        <select class="select" name="item_game">
                                            <?
                                            $item_games_tokens = $db->get_array_list("SELECT DISTINCT (robot_game) AS game_token FROM mmrpg_index_robots WHERE robot_game <> '' ORDER BY robot_game ASC;", 'game_token');
                                            echo('<option value=""'.(empty($item_data['item_game']) ? 'selected="selected"' : '').'>- none -</option>');
                                            foreach ($item_games_tokens AS $game_token => $game_data){
                                                $label = $game_token;
                                                $selected = !empty($item_data['item_game']) && $item_data['item_game'] == $game_token ? 'selected="selected"' : '';
                                                echo('<option value="'.$game_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label">Sort Group</strong>
                                        <input class="textbox" type="text" name="item_group" value="<?= $item_data['item_group'] ?>" maxlength="64" />
                                    </div>

                                    <div class="field foursize">
                                        <strong class="label">Sort Order</strong>
                                        <input class="textbox" type="number" name="item_order" value="<?= $item_data['item_order'] ?>" maxlength="8" />
                                    </div>

                                <? } ?>

                            </div>

                            <? if (!$item_data_is_new){ ?>
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

                                    <? $placeholder_folder = $item_data['item_class'] != 'master' ? $item_data['item_class'] : 'item'; ?>
                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Path</strong>
                                            <em>base image path for sprites</em>
                                        </div>
                                        <select class="select" name="item_image">
                                            <option value="<?= $placeholder_folder ?>" <?= $item_data['item_image'] == $placeholder_folder ? 'selected="selected"' : '' ?>>-</option>
                                            <option value="<?= $item_data['item_token'] ?>" <?= $item_data['item_image'] == $item_data['item_token'] ? 'selected="selected"' : '' ?>>content/items/<?= $item_data['item_token'] ?>/</option>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Size</strong>
                                            <em>base frame size for each sprite</em>
                                        </div>
                                        <select class="select" name="item_image_size">
                                            <? if ($item_data['item_image'] == $placeholder_folder){ ?>
                                                <option value="<?= $item_data['item_image_size'] ?>" selected="selected">-</option>
                                                <option value="40">40x40</option>
                                                <option value="80">80x80</option>
                                                <option disabled="disabled" value="160">160x160</option>
                                            <? } else { ?>
                                                <option value="40" <?= $item_data['item_image_size'] == 40 ? 'selected="selected"' : '' ?>>40x40</option>
                                                <option value="80" <?= $item_data['item_image_size'] == 80 ? 'selected="selected"' : '' ?>>80x80</option>
                                                <option disabled="disabled" value="160" <?= $item_data['item_image_size'] == 160 ? 'selected="selected"' : '' ?>>160x160</option>
                                            <? } ?>
                                        </select><span></span>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Editor #1</strong>
                                            <em>user who edited or created this sprite</em>
                                        </div>
                                        <? if ($item_data['item_image'] != $placeholder_folder){ ?>
                                            <select class="select" name="item_image_editor">
                                                <?= str_replace('value="'.$item_data['item_image_editor'].'"', 'value="'.$item_data['item_image_editor'].'" selected="selected"', $contributor_options_markup) ?>
                                            </select><span></span>
                                        <? } else { ?>
                                            <input type="hidden" name="item_image_editor" value="<?= $item_data['item_image_editor'] ?>" />
                                            <input class="textbox" type="text" name="item_image_editor" value="-" disabled="disabled" />
                                        <? } ?>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Editor #2</strong>
                                            <em>another user who collaborated on this sprite</em>
                                        </div>
                                        <? if ($item_data['item_image'] != $placeholder_folder){ ?>
                                            <select class="select" name="item_image_editor2">
                                                <?= str_replace('value="'.$item_data['item_image_editor2'].'"', 'value="'.$item_data['item_image_editor2'].'" selected="selected"', $contributor_options_markup) ?>
                                            </select><span></span>
                                        <? } else { ?>
                                            <input type="hidden" name="item_image_editor2" value="<?= $item_data['item_image_editor2'] ?>" />
                                            <input class="textbox" type="text" name="item_image_editor2" value="-" disabled="disabled" />
                                        <? } ?>
                                    </div>

                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong>Sprite Sheets</strong>
                                            <em>number of sheets this sprite requires</em>
                                        </div>
                                        <input class="textbox" type="number" name="item_image_sheets" value="<?= $item_data['item_image_sheets'] ?>" maxlength="8" min="0" step="1" />
                                    </div>

                                    <?

                                    // Only proceed if all required sprite fields are set
                                    if (!empty($item_data['item_image'])
                                        && $item_data['item_image'] != $placeholder_folder
                                        && !empty($item_data['item_image_size'])
                                        && !empty($item_data['item_image_sheets'])){

                                        echo('<hr />'.PHP_EOL);

                                        // Define the base sprite paths for this item given its image token
                                        $base_sprite_path = 'content/items/'.$item_data['item_image'].'/sprites/';

                                        // Loop through the defined sheets for this item and display image lists
                                        for ($sheet_key = 0; $sheet_key < $item_data['item_image_sheets']; $sheet_key++){

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
                                                        <em>Main sprites used for item. Zoom sprites are auto-generated.</em>
                                                    <? } else { ?>
                                                        <?= 'Sprite Sheet #'.$sheet_num  ?>
                                                        <em>Additional sprite sheet used for this item. Zoom sprites are auto-generated.</em>
                                                    <? } ?>
                                                </strong>
                                            </div>
                                            <div class="field fullsize has2cols widecols multirow sprites has-filebars">
                                                <?
                                                $sheet_groups = array('sprites');
                                                $sheet_kinds = array('icon', 'sprite');
                                                $sheet_sizes = array($item_data['item_image_size'], $item_data['item_image_size'] * 2);
                                                $sheet_directions = array('left', 'right');
                                                $num_frames = count(explode('/', MMRPG_SETTINGS_ITEM_FRAMEINDEX));
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
                                                                <input type="hidden" name="item_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="0" checked="checked" />
                                                                <input class="checkbox" type="checkbox" name="item_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="1" />
                                                            </label>
                                                            <p class="subtext" style="color: #da1616;">Empty base <strong>/sprites/</strong> folder and remove all images</p>
                                                        </div>

                                                <? } else { ?>

                                                        <div class="field checkwrap rfloat fullsize">
                                                            <label class="label">
                                                                <strong style="color: #da1616;">Delete Sheet #<?= $sheet_num ?> Images?</strong>
                                                                <input type="hidden" name="item_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="0" checked="checked" />
                                                                <input class="checkbox" type="checkbox" name="item_image_sheets_actions[<?= $sheet_num ?>][delete_images]" value="1" />
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
                            <? } ?>

                            <? if (!$item_data_is_new){ ?>
                                <div class="panel" data-tab="functions">

                                    <div class="field fullsize codemirror" data-codemirror-mode="php">
                                        <div class="label">
                                            <strong>Item Functions</strong>
                                            <em>code is php-format with html allowed in some strings</em>
                                        </div>
                                        <?
                                        // Collect the markup for the item functions file
                                        if (!empty($_SESSION['item_functions_markup'][$item_data['item_id']])){
                                            $item_functions_markup = $_SESSION['item_functions_markup'][$item_data['item_id']];
                                            unset($_SESSION['item_functions_markup'][$item_data['item_id']]);
                                        } else {
                                            $template_functions_path = MMRPG_CONFIG_ITEMS_CONTENT_PATH.'.item/functions.php';
                                            $item_functions_path = MMRPG_CONFIG_ITEMS_CONTENT_PATH.$item_data['item_token'].'/functions.php';
                                            $item_functions_markup = file_exists($item_functions_path) ? file_get_contents($item_functions_path) : file_get_contents($template_functions_path);
                                        }
                                        ?>
                                        <textarea class="textarea" name="item_functions_markup" rows="<?= min(20, substr_count($item_functions_markup, PHP_EOL)) ?>"><?= htmlentities(trim($item_functions_markup), ENT_QUOTES, 'UTF-8', true) ?></textarea>
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
                                            <code style="color: #05a;">$this_item</code>
                                            &nbsp;/&nbsp;
                                            <code style="color: #05a;">$target_item</code>
                                            &nbsp;&nbsp;<a title="item data reference" href="<?= str_replace(MMRPG_CONFIG_ROOTDIR, MMRPG_CONFIG_ROOTURL, MMRPG_CONFIG_ITEMS_CONTENT_PATH).'.item/data.json' ?>" target="_blank"><i class="fas fa-external-link-square-alt"></i></a>
                                        </div>
                                    </div>

                                </div>
                            <? } ?>

                        </div>

                        <hr />

                        <? if (!$item_data_is_new){ ?>

                            <div class="options">

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Published</strong>
                                        <input type="hidden" name="item_flag_published" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="item_flag_published" value="1" <?= !empty($item_data['item_flag_published']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This item is ready to appear on the site</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Complete</strong>
                                        <input type="hidden" name="item_flag_complete" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="item_flag_complete" value="1" <?= !empty($item_data['item_flag_complete']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This item's sprites have been completed</p>
                                </div>

                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Hidden</strong>
                                        <input type="hidden" name="item_flag_hidden" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="item_flag_hidden" value="1" <?= !empty($item_data['item_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This item's data should stay hidden</p>
                                </div>

                                <? if (!empty($item_data['item_flag_published'])
                                    && !empty($item_data['item_flag_complete'])){ ?>

                                    <div style="clear: both; padding-top: 20px;">

                                        <div class="field checkwrap">
                                            <label class="label">
                                                <strong>Unlockable</strong>
                                                <input type="hidden" name="item_flag_unlockable" value="0" checked="checked" />
                                                <input class="checkbox" type="checkbox" name="item_flag_unlockable" value="1" <?= !empty($item_data['item_flag_unlockable']) ? 'checked="checked"' : '' ?> />
                                            </label>
                                            <p class="subtext">This item is ready to be used in the game</p>
                                        </div>

                                    </div>

                                <? } ?>

                            </div>

                            <hr />

                        <? } ?>

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="<?= $item_data_is_new ? 'Create Item' : 'Save Changes' ?>" />
                                <? if (!$item_data_is_new && empty($item_data['item_flag_protected'])){ ?>
                                    <input class="button delete" type="button" value="Delete Item" data-delete="items" data-item-id="<?= $item_data['item_id'] ?>" />
                                <? } ?>
                            </div>
                            <? if (!$item_data_is_new){ ?>
                                <?= cms_admin::object_editor_print_git_footer_buttons('items', $item_data['item_token'], $mmrpg_git_file_arrays); ?>
                            <? } ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_item_data = $item_data;
                if (isset($debug_item_data['item_description2'])){ $debug_item_data['item_description2'] = str_replace(PHP_EOL, '\\n', $debug_item_data['item_description2']); }
                echo('<pre style="display: none;">$item_data = '.(!empty($debug_item_data) ? htmlentities(print_r($debug_item_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                ?>

                <?
                $temp_edit_markup = ob_get_clean();
                echo($temp_edit_markup).PHP_EOL;
            }

        }
        ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>