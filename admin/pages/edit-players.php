<? ob_start(); ?>

    <?

    // Pre-check access permissions before continuing
    if (!in_array('*', $this_adminaccess)
        && !in_array('edit-players', $this_adminaccess)){
        $form_messages[] = array('error', 'You do not have permission to edit players!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Dependant Indexes -- */

    // Collect an index of type colours for options
    $mmrpg_types_fields = rpg_type::get_index_fields(true);
    $mmrpg_types_index = $db->get_array_list("SELECT {$mmrpg_types_fields} FROM mmrpg_index_types ORDER BY type_order ASC", 'type_token');

    // Collect an index of battle fields for options
    $mmrpg_fields_fields = rpg_field::get_index_fields(true);
    $mmrpg_fields_index = $db->get_array_list("SELECT {$mmrpg_fields_fields} FROM mmrpg_index_fields WHERE field_token <> 'field' ORDER BY field_order ASC", 'field_token');

    // Collect an index of player colours for options
    $mmrpg_players_fields = rpg_player::get_index_fields(true);
    $mmrpg_players_index = $db->get_array_list("SELECT {$mmrpg_players_fields} FROM mmrpg_index_players WHERE player_token <> 'player' ORDER BY player_order ASC", 'player_token');

    // Collect an index of robot colours for options
    $mmrpg_robots_fields = rpg_robot::get_index_fields(true);
    $mmrpg_robots_index = $db->get_array_list("SELECT {$mmrpg_robots_fields} FROM mmrpg_index_robots WHERE robot_token <> 'robot' ORDER BY robot_order ASC", 'robot_token');

    // Collect an index of player colours for options
    $mmrpg_abilities_fields = rpg_ability::get_index_fields(true);
    $mmrpg_abilities_index = $db->get_array_list("SELECT {$mmrpg_abilities_fields} FROM mmrpg_index_abilities WHERE ability_token <> 'ability' AND ability_class <> 'system' ORDER BY ability_order ASC", 'ability_token');

    // Collect an index of contributors and admins that have made sprites
    $mmrpg_contributors_index = cms_admin::get_contributors_index('player');

    // Collect an index of file changes and updates via git
    $mmrpg_git_file_arrays = cms_admin::object_editor_get_git_file_arrays(MMRPG_CONFIG_PLAYERS_CONTENT_PATH, array(
        'table' => 'mmrpg_index_players',
        'token' => 'player_token'
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

    // Define a function for exiting a player edit action
    function exit_player_edit_action($player_id = 0){
        if (!empty($player_id)){ $location = 'admin/edit-players/editor/player_id='.$player_id; }
        else { $location = 'admin/edit-players/search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Players | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if (false && $sub_action == 'delete' && !empty($_GET['player_id'])){

        // Collect form data for processing
        $delete_data['player_id'] = !empty($_GET['player_id']) && is_numeric($_GET['player_id']) ? trim($_GET['player_id']) : '';

        // Let's delete all of this player's data from the database
        $db->delete('mmrpg_index_players', array('player_id' => $delete_data['player_id']));
        $form_messages[] = array('success', 'The requested player has been deleted from the database');
        exit_form_action('success');

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 50;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'player_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['player_id'] = !empty($_GET['player_id']) && is_numeric($_GET['player_id']) ? trim($_GET['player_id']) : '';
        $search_data['player_name'] = !empty($_GET['player_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['player_name']) ? trim(strtolower($_GET['player_name'])) : '';
        $search_data['player_type'] = !empty($_GET['player_type']) && preg_match('/[-_0-9a-z]+/i', $_GET['player_type']) ? trim(strtolower($_GET['player_type'])) : '';
        $search_data['player_class'] = !empty($_GET['player_class']) && preg_match('/[-_0-9a-z]+/i', $_GET['player_class']) ? trim(strtolower($_GET['player_class'])) : '';
        $search_data['player_flavour'] = !empty($_GET['player_flavour']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['player_flavour']) ? trim($_GET['player_flavour']) : '';
        $search_data['player_game'] = !empty($_GET['player_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['player_game']) ? trim(strtoupper($_GET['player_game'])) : '';
        $search_data['player_group'] = !empty($_GET['player_group']) && preg_match('/[-_0-9a-z\/]+/i', $_GET['player_group']) ? trim($_GET['player_group']) : '';
        $search_data['player_flag_hidden'] = isset($_GET['player_flag_hidden']) && $_GET['player_flag_hidden'] !== '' ? (!empty($_GET['player_flag_hidden']) ? 1 : 0) : '';
        $search_data['player_flag_complete'] = isset($_GET['player_flag_complete']) && $_GET['player_flag_complete'] !== '' ? (!empty($_GET['player_flag_complete']) ? 1 : 0) : '';
        $search_data['player_flag_unlockable'] = isset($_GET['player_flag_unlockable']) && $_GET['player_flag_unlockable'] !== '' ? (!empty($_GET['player_flag_unlockable']) ? 1 : 0) : '';
        $search_data['player_flag_exclusive'] = isset($_GET['player_flag_exclusive']) && $_GET['player_flag_exclusive'] !== '' ? (!empty($_GET['player_flag_exclusive']) ? 1 : 0) : '';
        $search_data['player_flag_published'] = isset($_GET['player_flag_published']) && $_GET['player_flag_published'] !== '' ? (!empty($_GET['player_flag_published']) ? 1 : 0) : '';
        cms_admin::object_index_search_data_append_git_statuses($search_data, 'player');

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_player_fields = rpg_player::get_index_fields(true, 'player');
        $search_query = "SELECT
            {$temp_player_fields}
            FROM mmrpg_index_players AS player
            WHERE 1=1
            AND player_token <> 'player'
            ";

        // If the player ID was provided, we can search by exact match
        if (!empty($search_data['player_id'])){
            $player_id = $search_data['player_id'];
            $search_query .= "AND player_id = {$player_id} ";
            $search_results_limit = false;
        }

        // Else if the player name was provided, we can use wildcards
        if (!empty($search_data['player_name'])){
            $player_name = $search_data['player_name'];
            $player_name = str_replace(array(' ', '*', '%'), '%', $player_name);
            $player_name = preg_replace('/%+/', '%', $player_name);
            $player_name = '%'.$player_name.'%';
            $search_query .= "AND (player_name LIKE '{$player_name}' OR player_token LIKE '{$player_name}') ";
            $search_results_limit = false;
        }

        // Else if the player type was provided, we can use wildcards
        if (!empty($search_data['player_type'])){
            $player_type = $search_data['player_type'];
            if ($player_type !== 'none'){ $search_query .= "AND (player_type LIKE '{$player_type}' OR player_type2 LIKE '{$player_type}') "; }
            else { $search_query .= "AND player_type = '' "; }
            $search_results_limit = false;
        }

        // If the player class was provided
        if (!empty($search_data['player_class'])){
            $search_query .= "AND player_class = '{$search_data['player_class']}' ";
            $search_results_limit = false;
        }

        // Else if the player flavour was provided, we can use wildcards
        if (!empty($search_data['player_flavour'])){
            $player_flavour = $search_data['player_flavour'];
            $player_flavour = str_replace(array(' ', '*', '%'), '%', $player_flavour);
            $player_flavour = preg_replace('/%+/', '%', $player_flavour);
            $player_flavour = '%'.$player_flavour.'%';
            $search_query .= "AND (
                player_description LIKE '{$player_flavour}'
                OR player_description2 LIKE '{$player_flavour}'
                OR player_quotes_start LIKE '{$player_flavour}'
                OR player_quotes_taunt LIKE '{$player_flavour}'
                OR player_quotes_victory LIKE '{$player_flavour}'
                OR player_quotes_defeat LIKE '{$player_flavour}'
                ) ";
            $search_results_limit = false;
        }

        // If the player game was provided
        if (!empty($search_data['player_game'])){
            $search_query .= "AND player_game = '{$search_data['player_game']}' ";
            $search_results_limit = false;
        }

        // If the player group was provided
        if (!empty($search_data['player_group'])){
            $search_query .= "AND player_group = '{$search_data['player_group']}' ";
            $search_results_limit = false;
        }

        // If the player hidden flag was provided
        if ($search_data['player_flag_hidden'] !== ''){
            $search_query .= "AND player_flag_hidden = {$search_data['player_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the player complete flag was provided
        if ($search_data['player_flag_complete'] !== ''){
            $search_query .= "AND player_flag_complete = {$search_data['player_flag_complete']} ";
            $search_results_limit = false;
        }

        // If the player unlockable flag was provided
        if ($search_data['player_flag_unlockable'] !== ''){
            $search_query .= "AND player_flag_unlockable = {$search_data['player_flag_unlockable']} ";
            $search_results_limit = false;
        }

        // If the player exclusive flag was provided
        if ($search_data['player_flag_exclusive'] !== ''){
            $search_query .= "AND player_flag_exclusive = {$search_data['player_flag_exclusive']} ";
            $search_results_limit = false;
        }

        // If the player published flag was provided
        if ($search_data['player_flag_published'] !== ''){
            $search_query .= "AND player_flag_published = {$search_data['player_flag_published']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "player_name ASC";
        $order_by[] = "FIELD(player_class, 'mecha', 'master', 'boss')";
        $order_by[] = "player_order ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;
        cms_admin::object_index_search_results_filter_git_statuses($search_results, $search_results_count, $search_data, 'player', $mmrpg_git_file_arrays);

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(player_id) AS total FROM mmrpg_index_players WHERE 1=1 AND player_token <> 'player';", 'total');

    }

    // If we're in editor mode, we should collect player info from database
    $player_data = array();
    $editor_data = array();
    $is_backup_data = false;
    if ($sub_action == 'editor'
        && (!empty($_GET['player_id'])
            || !empty($_GET['backup_id']))){

        // Collect form data for processing
        $editor_data['player_id'] = !empty($_GET['player_id']) && is_numeric($_GET['player_id']) ? trim($_GET['player_id']) : '';
        if (empty($editor_data['player_id'])
            && !empty($_GET['backup_id'])
            && is_numeric($_GET['backup_id'])){
            $editor_data['backup_id'] = trim($_GET['backup_id']);
            $is_backup_data = true;
        }


        /* -- Collect Player Data -- */

        // Collect player details from the database
        $temp_player_fields = rpg_player::get_index_fields(true);
        if (!$is_backup_data){
            $player_data = $db->get_array("SELECT {$temp_player_fields} FROM mmrpg_index_players WHERE player_id = {$editor_data['player_id']};");
        } else {
            $temp_player_backup_fields = str_replace('player_id,', 'backup_id AS player_id,', $temp_player_fields);
            $temp_player_backup_fields .= ', backup_date_time';
            $player_data = $db->get_array("SELECT {$temp_player_backup_fields} FROM mmrpg_index_players_backups WHERE backup_id = {$editor_data['backup_id']};");
        }

        // If player data could not be found, produce error and exit
        if (empty($player_data)){ exit_player_edit_action(); }

        // Collect the player's name(s) for display
        $player_name_display = $player_data['player_name'];
        $this_page_tabtitle = $player_name_display.' | '.$this_page_tabtitle;
        if ($is_backup_data){ $this_page_tabtitle = str_replace('Edit Players', 'View Backups', $this_page_tabtitle); }

        // If form data has been submit for this player, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-players'){

            // COLLECT form data from the request and parse out simple rules

            $old_player_token = !empty($_POST['old_player_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_player_token']) ? trim(strtolower($_POST['old_player_token'])) : '';

            $form_data['player_id'] = !empty($_POST['player_id']) && is_numeric($_POST['player_id']) ? trim($_POST['player_id']) : 0;
            $form_data['player_token'] = !empty($_POST['player_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['player_token']) ? trim(strtolower($_POST['player_token'])) : '';
            $form_data['player_name'] = !empty($_POST['player_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['player_name']) ? trim($_POST['player_name']) : '';
            $form_data['player_class'] = 'master'; //!empty($_POST['player_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['player_class']) ? trim(strtolower($_POST['player_class'])) : '';
            $form_data['player_type'] = !empty($_POST['player_type']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['player_type']) ? trim(strtolower($_POST['player_type'])) : '';
            $form_data['player_game'] = 'MMRPG'; //!empty($_POST['player_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['player_game']) ? trim($_POST['player_game']) : '';
            $form_data['player_group'] = 'MMRPG'; //!empty($_POST['player_group']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['player_group']) ? trim($_POST['player_group']) : '';
            $form_data['player_number'] = !empty($_POST['player_number']) && is_numeric($_POST['player_number']) ? (int)(trim($_POST['player_number'])) : 0;
            $form_data['player_order'] = !empty($_POST['player_order']) && is_numeric($_POST['player_order']) ? (int)(trim($_POST['player_order'])) : 0;

            $form_data['player_energy'] = $form_data['player_type'] === 'energy' ? 25 : 0; //!empty($_POST['player_energy']) && is_numeric($_POST['player_energy']) ? (int)(trim($_POST['player_energy'])) : 0;
            $form_data['player_weapons'] = $form_data['player_type'] === 'weapons' ? 25 : 0; //!empty($_POST['player_weapons']) && is_numeric($_POST['player_weapons']) ? (int)(trim($_POST['player_weapons'])) : 0;
            $form_data['player_attack'] = $form_data['player_type'] === 'attack' ? 25 : 0; //!empty($_POST['player_attack']) && is_numeric($_POST['player_attack']) ? (int)(trim($_POST['player_attack'])) : 0;
            $form_data['player_defense'] = $form_data['player_type'] === 'defense' ? 25 : 0; //!empty($_POST['player_defense']) && is_numeric($_POST['player_defense']) ? (int)(trim($_POST['player_defense'])) : 0;
            $form_data['player_speed'] = $form_data['player_type'] === 'speed' ? 25 : 0; //!empty($_POST['player_speed']) && is_numeric($_POST['player_speed']) ? (int)(trim($_POST['player_speed'])) : 0;

            $form_data['player_description'] = ''; //!empty($_POST['player_description']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['player_description']) ? trim($_POST['player_description']) : '';
            $form_data['player_description2'] = !empty($_POST['player_description2']) ? trim(strip_tags($_POST['player_description2'])) : '';

            $form_data['player_quotes_start'] = !empty($_POST['player_quotes_start']) ? trim(strip_tags($_POST['player_quotes_start'])) : '';
            $form_data['player_quotes_taunt'] = !empty($_POST['player_quotes_taunt']) ? trim(strip_tags($_POST['player_quotes_taunt'])) : '';
            $form_data['player_quotes_victory'] = !empty($_POST['player_quotes_victory']) ? trim(strip_tags($_POST['player_quotes_victory'])) : '';
            $form_data['player_quotes_defeat'] = !empty($_POST['player_quotes_defeat']) ? trim(strip_tags($_POST['player_quotes_defeat'])) : '';

            $form_data['player_abilities_rewards'] = !empty($_POST['player_abilities_rewards']) ? array_values(array_filter($_POST['player_abilities_rewards'])) : array();
            $form_data['player_abilities_compatible'] = array(); //!empty($_POST['player_abilities_compatible']) && is_array($_POST['player_abilities_compatible']) ? array_values(array_unique(array_filter($_POST['player_abilities_compatible']))) : array();

            $form_data['player_robots_rewards'] = !empty($_POST['player_robots_rewards']) ? array_values(array_filter($_POST['player_robots_rewards'])) : array();
            $form_data['player_robots_compatible'] = !empty($_POST['player_robots_compatible']) && is_array($_POST['player_robots_compatible']) ? array_values(array_unique(array_filter($_POST['player_robots_compatible']))) : array();

            $form_data['player_image'] = !empty($_POST['player_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['player_image']) ? trim(strtolower($_POST['player_image'])) : '';
            $form_data['player_image_size'] = !empty($_POST['player_image_size']) && is_numeric($_POST['player_image_size']) ? (int)(trim($_POST['player_image_size'])) : 0;
            $form_data['player_image_editor'] = !empty($_POST['player_image_editor']) && is_numeric($_POST['player_image_editor']) ? (int)(trim($_POST['player_image_editor'])) : 0;
            $form_data['player_image_editor2'] = !empty($_POST['player_image_editor2']) && is_numeric($_POST['player_image_editor2']) ? (int)(trim($_POST['player_image_editor2'])) : 0;

            $form_data['player_flag_published'] = isset($_POST['player_flag_published']) && is_numeric($_POST['player_flag_published']) ? (int)(trim($_POST['player_flag_published'])) : 0;
            $form_data['player_flag_complete'] = isset($_POST['player_flag_complete']) && is_numeric($_POST['player_flag_complete']) ? (int)(trim($_POST['player_flag_complete'])) : 0;
            $form_data['player_flag_hidden'] = isset($_POST['player_flag_hidden']) && is_numeric($_POST['player_flag_hidden']) ? (int)(trim($_POST['player_flag_hidden'])) : 0;

            if ($form_data['player_type'] != 'copy'){
                $form_data['player_image_alts'] = !empty($_POST['player_image_alts']) && is_array($_POST['player_image_alts']) ? array_filter($_POST['player_image_alts']) : array();
                $player_image_alts_new = !empty($_POST['player_image_alts_new']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['player_image_alts_new']) ? trim(strtolower($_POST['player_image_alts_new'])) : '';
            } else {
                $form_data['player_image_alts'] = array();
                $player_image_alts_new = '';
            }

            $form_data['player_functions_markup'] = !empty($_POST['player_functions_markup']) ? trim($_POST['player_functions_markup']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'player_image_alts\']  = '.print_r($_POST['player_image_alts'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'player_image_alts_new\']  = '.print_r($_POST['player_image_alts_new'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (empty($form_data['player_id'])){ $form_messages[] = array('error', 'Player ID was not provided'); $form_success = false; }
            if (empty($form_data['player_token']) || empty($old_player_token)){ $form_messages[] = array('error', 'Player Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['player_name'])){ $form_messages[] = array('error', 'Player Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['player_class'])){ $form_messages[] = array('error', 'Player Kind was not provided or was invalid'); $form_success = false; }
            if (empty($_POST['player_type'])){ $form_messages[] = array('warning', 'Player Type was not provided or were invalid'); $form_success = false; }
            if (!$form_success){ exit_player_edit_action($form_data['player_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (empty($form_data['player_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            if (empty($form_data['player_group'])){ $form_messages[] = array('warning', 'Sorting Group was not provided and may cause issues on the front-end'); }
            if (empty($form_data['player_number'])){ $form_messages[] = array('warning', 'Serial Number was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if (!empty($form_data['player_abilities_rewards'])){
                $new_rewards = array();
                $new_rewards_tokens = array();
                foreach ($form_data['player_abilities_rewards'] AS $key => $reward){
                    if (empty($reward) || empty($reward['token'])){ continue; }
                    elseif (in_array($reward['token'], $new_rewards_tokens)){ continue; }
                    if (empty($reward['points'])){ $reward['points'] = 0; }
                    $new_rewards_tokens[] = $reward['token'];
                    $new_rewards[] = $reward;
                }
                usort($new_rewards, function($a, $b) use($mmrpg_abilities_index){
                    $ax = $mmrpg_abilities_index[$a['token']];
                    $bx = $mmrpg_abilities_index[$b['token']];
                    if ($a['points'] < $b['points']){ return -1; }
                    elseif ($a['points'] > $b['points']){ return 1; }
                    elseif ($ax['ability_order'] < $bx['ability_order']){ return -1; }
                    elseif ($ax['ability_order'] > $bx['ability_order']){ return 1; }
                    else { return 0; }
                    });
                $form_data['player_abilities_rewards'] = $new_rewards;
            }

            if (!empty($form_data['player_robots_rewards'])){
                $new_rewards = array();
                $new_rewards_tokens = array();
                foreach ($form_data['player_robots_rewards'] AS $key => $reward){
                    if (empty($reward) || empty($reward['token'])){ continue; }
                    elseif (in_array($reward['token'], $new_rewards_tokens)){ continue; }
                    if (empty($reward['points'])){ $reward['points'] = 0; }
                    if (empty($reward['level'])){ $reward['level'] = 1; }
                    $new_rewards_tokens[] = $reward['token'];
                    $new_rewards[] = $reward;
                }
                usort($new_rewards, function($a, $b) use($mmrpg_robots_index){
                    $ax = $mmrpg_robots_index[$a['token']];
                    $bx = $mmrpg_robots_index[$b['token']];
                    if ($a['points'] < $b['points']){ return -1; }
                    elseif ($a['points'] > $b['points']){ return 1; }
                    elseif ($a['level'] < $b['level']){ return -1; }
                    elseif ($a['level'] > $b['level']){ return 1; }
                    elseif ($ax['robot_order'] < $bx['robot_order']){ return -1; }
                    elseif ($ax['robot_order'] > $bx['robot_order']){ return 1; }
                    else { return 0; }
                    });
                $form_data['player_robots_rewards'] = $new_rewards;
            }

            if (!empty($form_data['player_robots_compatible'])){
                $new_compatible = $form_data['player_robots_compatible'];
                usort($new_compatible, function($a, $b) use($mmrpg_robots_index){
                    $ax = $mmrpg_robots_index[$a];
                    $bx = $mmrpg_robots_index[$b];
                    if ($ax['robot_order'] < $bx['robot_order']){ return -1; }
                    elseif ($ax['robot_order'] > $bx['robot_order']){ return 1; }
                    else { return 0; }
                    });
                $form_data['player_robots_compatible'] = $new_compatible;
            }

            if (isset($form_data['player_abilities_rewards'])){ $form_data['player_abilities_rewards'] = !empty($form_data['player_abilities_rewards']) ? json_encode($form_data['player_abilities_rewards'], JSON_NUMERIC_CHECK) : ''; }
            if (isset($form_data['player_abilities_compatible'])){ $form_data['player_abilities_compatible'] = !empty($form_data['player_abilities_compatible']) ? json_encode($form_data['player_abilities_compatible']) : ''; }

            if (isset($form_data['player_robots_rewards'])){ $form_data['player_robots_rewards'] = !empty($form_data['player_robots_rewards']) ? json_encode($form_data['player_robots_rewards'], JSON_NUMERIC_CHECK) : ''; }
            if (isset($form_data['player_robots_compatible'])){ $form_data['player_robots_compatible'] = !empty($form_data['player_robots_compatible']) ? json_encode($form_data['player_robots_compatible']) : ''; }

            $empty_image_folders = array();

            if (isset($form_data['player_image_alts'])){
                if (!empty($player_image_alts_new)){
                    $alt_num = $player_image_alts_new != 'alt' ? (int)(str_replace('alt', '', $player_image_alts_new)) : 1;
                    $alt_name = ucfirst($player_image_alts_new);
                    if ($alt_num == 9){ $alt_name = 'Darkness Alt'; }
                    $form_data['player_image_alts'][$player_image_alts_new] = array(
                        'token' => $player_image_alts_new,
                        'name' => $form_data['player_name'].' ('.$alt_name.')',
                        'summons' => ($alt_num * 100),
                        'colour' => ($alt_num == 9 ? 'empty' : 'none')
                        );
                }
                $alt_keys = array_keys($form_data['player_image_alts']);
                usort($alt_keys, function($a, $b){
                    $a = strstr($a, 'alt') ? (int)(str_replace('alt', '', $a)) : 0;
                    $b = strstr($b, 'alt') ? (int)(str_replace('alt', '', $b)) : 0;
                    if ($a < $b){ return -1; }
                    elseif ($a > $b){ return 1; }
                    else { return 0; }
                    });
                $new_player_image_alts = array();
                foreach ($alt_keys AS $alt_key){
                    $alt_info = $form_data['player_image_alts'][$alt_key];
                    $alt_path = ($alt_key != 'base' ? '_'.$alt_key : '');
                    if (!empty($alt_info['delete_images'])){
                        $delete_sprite_path = 'content/players/'.$player_data['player_image'].'/sprites'.$alt_path.'/';
                        $delete_shadow_path = 'content/players/'.$player_data['player_image'].'/shadows'.$alt_path.'/';
                        $empty_image_folders[] = $delete_sprite_path;
                        $empty_image_folders[] = $delete_shadow_path;
                    }
                    if (!empty($alt_info['delete'])){ continue; }
                    elseif ($alt_key == 'base'){ continue; }
                    unset($alt_info['delete_images'], $alt_info['delete']);
                    unset($alt_info['generate_shadows']);
                    $new_player_image_alts[] = $alt_info;
                }
                $form_data['player_image_alts'] = $new_player_image_alts;
                $form_data['player_image_alts'] = !empty($form_data['player_image_alts']) ? json_encode($form_data['player_image_alts'], JSON_NUMERIC_CHECK) : '';
            }
            //$form_messages[] = array('alert', '<pre>$form_data[\'player_image_alts\']  = '.print_r($form_data['player_image_alts'] , true).'</pre>');

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

                    // Ensure the backup folder is created for this file
                    $backup_path = str_replace('/content/players/', '/images/backups/players/', MMRPG_CONFIG_ROOTDIR.$empty_path);
                    if (!file_exists($backup_path)){
                        recurseMakeDir($backup_path, 'images/backups/');
                        @chown($backup_path, 'mmrpgworld');
                    }

                    // Loop through empty files and delete one by one
                    foreach ($empty_files AS $empty_file_key => $empty_file_path){
                        $empty_file = basename($empty_file_path);

                        // Move the file to the backup folder, renaming the file with the timestamp
                        $bak_append = '.bak'.date('YmdHi');
                        $old_location = $empty_file_path;
                        $new_location = $backup_path.preg_replace('/(\.[a-z0-9]{3,})$/i', $bak_append.'$1', $empty_file);

                        // Attempt to copy the image and return the status of the action (remove old file if successful)
                        $copy_status = copy($old_location, $new_location);
                        if (file_exists($new_location)){ @unlink($old_location); $form_messages[] = array('alert', str_replace(MMRPG_CONFIG_ROOTDIR, '', $old_location).' was deleted!'); }
                        else { $form_messages[] = array('warning', str_replace(MMRPG_CONFIG_ROOTDIR, '', $old_location).' could not be deleted! ('.$copy_status.')');  }

                    }


                }

            }

            // Ensure the functions code is VALID PHP SYNTAX and save, otherwise do not save but allow user to fix it
            if (empty($form_data['player_functions_markup'])){
                // Functions code is EMPTY and will be ignored
                $form_messages[] = array('warning', 'Player functions code was empty and was not saved (reverted to original)');
            } elseif (!cms_admin::is_valid_php_syntax($form_data['player_functions_markup'])){
                // Functions code is INVALID and must be fixed
                $form_messages[] = array('warning', 'Player functions code was invalid PHP syntax and was not saved (please fix and try again)');
                $_SESSION['player_functions_markup'][$player_data['player_id']] = $form_data['player_functions_markup'];
            } else {
                // Functions code is OKAY and can be saved
                $player_functions_path = MMRPG_CONFIG_PLAYERS_CONTENT_PATH.$player_data['player_token'].'/functions.php';
                $old_player_functions_markup = file_exists($player_functions_path) ? trim(file_get_contents($player_functions_path)) : '';
                $new_player_functions_markup = $form_data['player_functions_markup'];
                if (empty($old_player_functions_markup) || $new_player_functions_markup !== $old_player_functions_markup){
                    $f = fopen($player_functions_path, 'w');
                    fwrite($f, $new_player_functions_markup);
                    fclose($f);
                    $form_messages[] = array('alert', 'Player functions file was updated');
                }
            }
            // Regardless, unset the markup variable so it's not save to the database
            unset($form_data['player_functions_markup']);

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');
            //exit_player_edit_action($form_data['player_id']);

            // Make a copy of the update data sans the player ID
            $update_data = $form_data;
            unset($update_data['player_id']);

            // If a recent backup of this data doesn't exist, create one now
            $backup_date_time = date('Ymd-Hi');
            $backup_exists = $db->get_value("SELECT backup_id FROM mmrpg_index_players_backups WHERE player_token = '{$update_data['player_token']}' AND backup_date_time = '{$backup_date_time}';", 'backup_id');
            if (empty($backup_exists)){
                $backup_data = $player_data;
                unset($backup_data['player_id']);
                $backup_data['backup_date_time'] = $backup_date_time;
                $db->insert('mmrpg_index_players_backups', $backup_data);
            }

            // Update the main database index with changes to this player's data
            $update_results = $db->update('mmrpg_index_players', $update_data, array('player_id' => $form_data['player_id']));

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If we made it this far, the update must have been a success
            if ($update_results !== false){ $form_success = true; $form_messages[] = array('success', 'Player data was updated successfully!'); }
            else { $form_success = false; $form_messages[] = array('error', 'Player data could not be updated...'); }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // If successful, we need to update the JSON file
            if ($form_success){ cms_admin::object_editor_update_json_data_file('player', array_merge($player_data, $update_data)); }

            // If the player tokens have changed, we must move the entire folder
            if ($old_player_token !== $update_data['player_token']){
                $old_content_path = MMRPG_CONFIG_PLAYERS_CONTENT_PATH.$old_player_token.'/';
                $new_content_path = MMRPG_CONFIG_PLAYERS_CONTENT_PATH.$update_data['player_token'].'/';
                if (rename($old_content_path, $new_content_path)){
                    $path_string = '<strong>'.mmrpg_clean_path($old_content_path).'</strong> &raquo; <strong>'.mmrpg_clean_path($new_content_path).'</strong>';
                    $form_messages[] = array('alert', 'Player directory renamed! '.$path_string);
                } else {
                    $form_messages[] = array('error', 'Unable to rename player directory!');
                }
            }

            // We're done processing the form, we can exit
            exit_player_edit_action($form_data['player_id']);

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }


    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/edit-players/">Edit Players</a>
        <? if ($sub_action == 'editor' && !empty($player_data)): ?>
            <? if (!$is_backup_data){ ?>
                &raquo; <a href="admin/edit-players/editor/player_id=<?= $player_data['player_id'] ?>"><?= $player_name_display ?></a>
            <? } else { ?>
                &raquo; <a><?= $player_name_display ?></a>
            <? } ?>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-players">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Players</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <? /* <input type="hidden" name="action" value="edit-players" /> */ ?>
                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="player_id" value="<?= !empty($search_data['player_id']) ? $search_data['player_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field halfsize">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="player_name" placeholder="" value="<?= !empty($search_data['player_name']) ? htmlentities($search_data['player_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Type</strong>
                        <select class="select" name="player_type"><option value=""></option><?
                            $stat_types = rpg_type::get_stat_types();
                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                if (!in_array($type_token, $stat_types)){ continue; }
                                ?><option value="<?= $type_token ?>"<?= !empty($search_data['player_type']) && $search_data['player_type'] === $type_token ? ' selected="selected"' : '' ?>><?= $type_token === 'none' ? 'Neutral' : ucfirst($type_token) ?></option><?
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
                        $flag_name = 'player_flag_'.$flag_token;
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
                        <input class="button" type="submit" value="Search" />
                        <input class="button" type="reset" value="Reset" onclick="javascript:window.location.href='admin/edit-players/';" />
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
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('player_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('player_name', 'Name') ?></th>
                                <th class="type"><?= cms_admin::get_sort_link('player_type', 'Type') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('player_flag_published', 'Published') ?></th>
                                <th class="flag complete"><?= cms_admin::get_sort_link('player_flag_complete', 'Complete') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('player_flag_hidden', 'Hidden') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head type"></th>
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
                                <td class="foot type"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag complete"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot count"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            $temp_class_colours = array(
                                'mecha' => array('speed', '<i class="fas fa-ghost"></i>'),
                                'master' => array('defense', '<i class="fas fa-player"></i>'),
                                'boss' => array('space', '<i class="fas fa-skull"></i>')
                                );
                            foreach ($search_results AS $key => $player_data){

                                $player_id = $player_data['player_id'];
                                $player_token = $player_data['player_token'];
                                $player_name = $player_data['player_name'];
                                $player_type = !empty($player_data['player_type']) ? ucfirst($player_data['player_type']) : 'Neutral';
                                $player_type_span = '<span class="type_span type_'.(!empty($player_data['player_type']) ? $player_data['player_type'] : 'none').'">'.$player_type.'</span>';
                                if (!empty($player_data['player_type'])
                                    && !empty($player_data['player_type2'])){
                                    $player_type .= ' / '.ucfirst($player_data['player_type2']);
                                    $player_type_span = '<span class="type_span type_'.$player_data['player_type'].'_'.$player_data['player_type2'].'">'.ucwords($player_data['player_type'].' / '.$player_data['player_type2']).'</span>';
                                }
                                $player_flag_published = !empty($player_data['player_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $player_flag_complete = !empty($player_data['player_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $player_flag_hidden = !empty($player_data['player_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $player_edit_url = 'admin/edit-players/editor/player_id='.$player_id;
                                $player_name_link = '<a class="link" href="'.$player_edit_url.'">'.$player_name.'</a>';
                                cms_admin::object_index_links_append_git_statues($player_name_link, $player_token, $mmrpg_git_file_arrays);

                                $player_actions = '';
                                $player_actions .= '<a class="link edit" href="'.$player_edit_url.'"><span>edit</span></a>';
                                $player_actions .= '<span class="link delete disabled"><span>delete</span></span>';
                                //$player_actions .= '<a class="link delete" data-delete="players" data-player-id="'.$player_id.'"><span>delete</span></a>';

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$player_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$player_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="type"><div class="wrap">'.$player_type_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$player_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$player_flag_complete.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$player_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$player_actions.'</div></td>'.PHP_EOL;
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
            && (!empty($_GET['player_id']) || !empty($_GET['backup_id']))){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($player_data['player_type']) ? $player_data['player_type'].(!empty($player_data['player_type2']) ? '_'.$player_data['player_type2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="player_type,player_type2">
                        <span class="title"><?= !$is_backup_data ? 'Edit' : 'View' ?> Player &quot;<?= $player_name_display ?>&quot;</span>
                        <?
                        // If this is NOT backup data, we can generate links
                        if (!$is_backup_data){

                            // Print out any git-related statues to this header
                            cms_admin::object_editor_header_echo_git_statues($player_data['player_token'], $mmrpg_git_file_arrays);

                            // If the player is published, generate and display a preview link
                            if (!empty($player_data['player_flag_published'])){
                                $preview_link = 'database/';
                                if ($player_data['player_class'] === 'master'){ $preview_link .= 'players/'; }
                                elseif ($player_data['player_class'] === 'mecha'){ $preview_link .= 'mechas/'; }
                                elseif ($player_data['player_class'] === 'boss'){ $preview_link .= 'bosses/'; }
                                $preview_link .= $player_data['player_token'].'/';
                                echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                                echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            }

                        }
                        // Otherwise we'll simply show the backup creation date
                        else {

                            // Print out the creation date in a readable form
                            echo '<span style="display: block; clear: left; font-size: 90%; font-weight: normal;">Backup Created '.date('Y/m/d @ g:s a', strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})([0-9]{2})$/', '$1/$2/$3T$4:$5', $player_data['backup_date_time']))).'</span>';

                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <?
                    // Collect a list of backups for this player from the database, if any
                    $player_backup_list = $db->get_array_list("SELECT
                        backup_id, player_token, player_name, backup_date_time
                        FROM mmrpg_index_players_backups
                        WHERE player_token = '{$player_data['player_token']}'
                        ORDER BY backup_date_time DESC
                        ;");
                    ?>

                    <div class="editor-tabs" data-tabgroup="player">
                        <a class="tab active" data-tab="basic">Basic</a><span></span>
                        <a class="tab" data-tab="flavour">Flavour</a><span></span>
                        <a class="tab" data-tab="abilities">Abilities</a><span></span>
                        <a class="tab" data-tab="robots">Robots</a><span></span>
                        <? if (!$is_backup_data){ ?>
                            <a class="tab" data-tab="sprites">Sprites</a><span></span>
                            <a class="tab" data-tab="functions">Functions</a><span></span>
                            <? if (!empty($player_backup_list)){ ?>
                                <a class="tab" data-tab="backups">Backups</a><span></span>
                            <? } ?>
                        <? } ?>
                    </div>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit-players" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="player">

                            <div class="panel active" data-tab="basic">

                                <div class="field">
                                    <strong class="label">Player ID</strong>
                                    <input type="hidden" name="player_id" value="<?= $player_data['player_id'] ?>" />
                                    <input class="textbox" type="text" name="player_id" value="<?= $player_data['player_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Player Token</strong>
                                        <em>avoid changing</em>
                                    </div>
                                    <input type="hidden" name="old_player_token" value="<?= $player_data['player_token'] ?>" />
                                    <input class="textbox" type="text" name="player_token" value="<?= $player_data['player_token'] ?>" maxlength="64" />
                                </div>

                                <div class="field">
                                    <strong class="label">Player Name</strong>
                                    <input class="textbox" type="text" name="player_name" value="<?= $player_data['player_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field">
                                    <strong class="label">
                                        Player Type
                                        <em>for stat bonuses</em>
                                        <span class="type_span type_<?= (!empty($player_data['player_type']) ? $player_data['player_type'].(!empty($player_data['player_type2']) ? '_'.$player_data['player_type2'] : '') : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="player_type,player_type2">&nbsp;</span>
                                    </strong>
                                    <div class="subfield">
                                        <select class="select" name="player_type">
                                            <?
                                            $stat_types = rpg_type::get_stat_types();
                                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                                if (!in_array($type_token, $stat_types)){ continue; }
                                                $label = $type_info['type_name'];
                                                if (!empty($player_data['player_type']) && $player_data['player_type'] === $type_token){ $selected = 'selected="selected"'; }
                                                else { $selected = ''; }
                                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                            ?>
                                        </select><span></span>
                                    </div>
                                </div>

                                <div class="field">
                                    <strong class="label">Player Number</strong>
                                    <input class="textbox" type="number" name="player_number" value="<?= $player_data['player_number'] ?>" maxlength="64" />
                                </div>

                                <div class="field">
                                    <strong class="label">Sort Order</strong>
                                    <input class="textbox" type="number" name="player_order" value="<?= $player_data['player_order'] ?>" maxlength="8" />
                                </div>

                            </div>

                            <div class="panel" data-tab="flavour">

                                <div class="field fullsize">
                                    <div class="label">
                                        <strong>Player Description</strong>
                                        <em>short paragraph about player's design, personality, background, etc.</em>
                                    </div>
                                    <textarea class="textarea" name="player_description2" rows="10"><?= htmlentities($player_data['player_description2'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                </div>

                                <hr />

                                <?
                                $player_quote_kinds = array('start', 'taunt', 'victory', 'defeat');
                                foreach ($player_quote_kinds AS $kind_key => $kind_token){
                                    ?>
                                    <div class="field halfsize">
                                        <div class="label">
                                            <strong><?= ucfirst($kind_token) ?> Quote</strong>
                                        </div>
                                        <input class="textbox" type="text" name="player_quotes_<?= $kind_token ?>" value="<?= htmlentities($player_data['player_quotes_'.$kind_token], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="256" />
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

                                <?

                                // Collect global abilities so we can skip them
                                $global_ability_tokens = rpg_ability::get_global_abilities();

                                // Pre-generate a list of all abilities so we can re-use it over and over
                                $ability_options_markup = array();
                                $ability_options_markup[] = '<option value="">-</option>';
                                foreach ($mmrpg_abilities_index AS $ability_token => $ability_info){
                                    //if (in_array($ability_token, $global_ability_tokens)){ continue; }
                                    if ($ability_info['ability_class'] === 'mecha' && $player_data['player_class'] !== 'mecha'){ continue; }
                                    elseif ($ability_info['ability_class'] === 'boss' && $player_data['player_class'] !== 'boss'){ continue; }
                                    $ability_name = $ability_info['ability_name'];
                                    $ability_types = ucwords(implode(' / ', array_values(array_filter(array($ability_info['ability_type'], $ability_info['ability_type2'])))));
                                    if (empty($ability_types)){ $ability_types = 'Neutral'; }
                                    $ability_options_markup[] = '<option value="'.$ability_token.'">'.$ability_name.' ('.$ability_types.')</option>';
                                }
                                $ability_options_count = count($ability_options_markup);
                                $ability_options_markup = implode(PHP_EOL, $ability_options_markup);

                                ?>

                                <div class="field fullsize multirow">
                                    <strong class="label">
                                        Native Abilities
                                        <em>These abilities are available to the player at the start and can be equipped to their robots</em>
                                    </strong>
                                    <?
                                    $current_ability_list = !empty($player_data['player_abilities_rewards']) ? json_decode($player_data['player_abilities_rewards'], true) : array();
                                    $select_limit = max(2, count($current_ability_list));
                                    $select_limit += 2;
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_value = isset($current_ability_list[$i]) ? $current_ability_list[$i] : array();
                                        $current_value_points = 0; //!empty($current_value) ? $current_value['points'] : '';
                                        $current_value_token = !empty($current_value) ? $current_value['token'] : '';
                                        ?>
                                        <div class="subfield pointsup">
                                            <input class="hidden" type="hidden" name="player_abilities_rewards[<?= $i ?>][points]" value="<?= $current_value_points ?>" maxlength="3" placeholder="0" />
                                            <select class="select" name="player_abilities_rewards[<?= $i ?>][token]">
                                                <?= str_replace('value="'.$current_value_token.'"', 'value="'.$current_value_token.'" selected="selected"', $ability_options_markup) ?>
                                            </select><span></span>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>

                            </div>

                            <div class="panel" data-tab="robots">

                                <?

                                // Pre-generate a list of all robots so we can re-use it over and over
                                $robot_options_markup = array();
                                $robot_options_markup[] = '<option value="">-</option>';
                                foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                                    if ($robot_info['robot_class'] === 'mecha' && $player_data['player_class'] !== 'mecha'){ continue; }
                                    elseif ($robot_info['robot_class'] === 'boss' && $player_data['player_class'] !== 'boss'){ continue; }
                                    $robot_name = $robot_info['robot_name'];
                                    $robot_cores = ucwords(implode(' / ', array_values(array_filter(array($robot_info['robot_core'], $robot_info['robot_core2'])))));
                                    if (empty($robot_cores)){ $robot_cores = 'Neutral'; }
                                    $robot_options_markup[] = '<option value="'.$robot_token.'">'.$robot_name.' ('.$robot_cores.')</option>';
                                }
                                $robot_options_count = count($robot_options_markup);
                                $robot_options_markup = implode(PHP_EOL, $robot_options_markup);

                                ?>

                                <div class="field fullsize multirow">
                                    <strong class="label">
                                        Starter Robot
                                        <em>This is the robot that the player starts at the beginning of their campaign</em>
                                    </strong>
                                    <?
                                    $current_robot_list = !empty($player_data['player_robots_rewards']) ? json_decode($player_data['player_robots_rewards'], true) : array();
                                    $select_limit = 1;
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_value = isset($current_robot_list[$i]) ? $current_robot_list[$i] : array();
                                        $current_value_points = 0; //!empty($current_value) ? $current_value['points'] : '';
                                        $current_value_level = 1; //!empty($current_value) ? $current_value['level'] : '';
                                        $current_value_token = !empty($current_value) ? $current_value['token'] : '';
                                        ?>
                                        <div class="subfield pointsup">
                                            <input class="hidden" type="hidden" name="player_robots_rewards[<?= $i ?>][points]" value="<?= $current_value_points ?>" maxlength="3" placeholder="0" />
                                            <select class="select" name="player_robots_rewards[<?= $i ?>][token]">
                                                <?= str_replace('value="'.$current_value_token.'"', 'value="'.$current_value_token.'" selected="selected"', $robot_options_markup) ?>
                                            </select><span></span>
                                            <input class="hidden" type="hidden" name="player_robots_rewards[<?= $i ?>][level]" value="<?= $current_value_level ?>" maxlength="3" placeholder="1" />
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>

                                <hr />

                                <div class="field fullsize has2cols multirow">
                                    <strong class="label">
                                        Campaign Robots
                                        <em>These are the robots that appear as the main targets in the player's campaign</em>
                                    </strong>
                                    <?
                                    $current_robot_list = !empty($player_data['player_robots_compatible']) ? json_decode($player_data['player_robots_compatible'], true) : array();
                                    $select_limit = max(10, count($current_robot_list));
                                    //$select_limit += 4 - ($select_limit % 4);
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_value = isset($current_robot_list[$i]) ? $current_robot_list[$i] : '';
                                        ?>
                                        <div class="subfield">
                                            <select class="select" name="player_robots_compatible[<?= $i ?>]">
                                                <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $robot_options_markup) ?>
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

                                <? $placeholder_folder = $player_data['player_class'] != 'master' ? $player_data['player_class'] : 'player'; ?>
                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Path</strong>
                                        <em>base image path for sprites</em>
                                    </div>
                                    <select class="select" name="player_image">
                                        <option value="<?= $placeholder_folder ?>" <?= $player_data['player_image'] == $placeholder_folder ? 'selected="selected"' : '' ?>>-</option>
                                        <option value="<?= $player_data['player_token'] ?>" <?= $player_data['player_image'] == $player_data['player_token'] ? 'selected="selected"' : '' ?>>content/players/<?= $player_data['player_token'] ?>/</option>
                                    </select><span></span>
                                </div>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Size</strong>
                                        <em>base frame size for each sprite</em>
                                    </div>
                                    <select class="select" name="player_image_size">
                                        <? if ($player_data['player_image'] == $placeholder_folder){ ?>
                                            <option value="<?= $player_data['player_image_size'] ?>" selected="selected">-</option>
                                            <option value="40">40x40</option>
                                            <option value="80">80x80</option>
                                            <option disabled="disabled" value="160">160x160</option>
                                        <? } else { ?>
                                            <option value="40" <?= $player_data['player_image_size'] == 40 ? 'selected="selected"' : '' ?>>40x40</option>
                                            <option value="80" <?= $player_data['player_image_size'] == 80 ? 'selected="selected"' : '' ?>>80x80</option>
                                            <option disabled="disabled" value="160" <?= $player_data['player_image_size'] == 160 ? 'selected="selected"' : '' ?>>160x160</option>
                                        <? } ?>
                                    </select><span></span>
                                </div>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Editor #1</strong>
                                        <em>user who edited or created this sprite</em>
                                    </div>
                                    <? if ($player_data['player_image'] != $placeholder_folder){ ?>
                                        <select class="select" name="player_image_editor">
                                            <?= str_replace('value="'.$player_data['player_image_editor'].'"', 'value="'.$player_data['player_image_editor'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    <? } else { ?>
                                        <input type="hidden" name="player_image_editor" value="<?= $player_data['player_image_editor'] ?>" />
                                        <input class="textbox" type="text" name="player_image_editor" value="-" disabled="disabled" />
                                    <? } ?>
                                </div>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Editor #2</strong>
                                        <em>another user who collaborated on this sprite</em>
                                    </div>
                                    <? if ($player_data['player_image'] != $placeholder_folder){ ?>
                                        <select class="select" name="player_image_editor2">
                                            <?= str_replace('value="'.$player_data['player_image_editor2'].'"', 'value="'.$player_data['player_image_editor2'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    <? } else { ?>
                                        <input type="hidden" name="player_image_editor2" value="<?= $player_data['player_image_editor2'] ?>" />
                                        <input class="textbox" type="text" name="player_image_editor2" value="-" disabled="disabled" />
                                    <? } ?>
                                </div>

                                <?

                                // Decompress existing image alts pulled from the database
                                $player_image_alts = !empty($player_data['player_image_alts']) ? json_decode($player_data['player_image_alts'], true) : array();

                                // Collect the alt tokens from all defined alts so far
                                $player_image_alts_tokens = array();
                                foreach ($player_image_alts AS $alt){ if (!empty($alt['token'])){ $player_image_alts_tokens[] = $alt['token'];  } }

                                // Define a variable to toggle allowance of new alt creation
                                $has_elemental_alts = $player_data['player_type'] == 'copy' ? true : false;
                                $allow_new_alt_creation = !$has_elemental_alts ? true : false;

                                // Only proceed if all required sprite fields are set
                                if (!empty($player_data['player_image'])
                                    && !in_array($player_data['player_image'], array('player', 'master', 'boss', 'mecha'))
                                    && !empty($player_data['player_image_size'])
                                    && !($is_backup_data && $has_elemental_alts)){

                                    echo('<hr />'.PHP_EOL);

                                    // Define the base sprite and shadow paths for this player given its image token
                                    $base_sprite_path = 'content/players/'.$player_data['player_image'].'/sprites/';
                                    $base_shadow_path = 'content/players/'.$player_data['player_image'].'/shadows/';

                                    // Define the alts we'll be looping through for this player
                                    $temp_alts_array = array();
                                    $temp_alts_array[] = array('token' => '', 'name' => $player_data['player_name'], 'summons' => 0);

                                    // Append predefined alts automatically, based on the player image alt array
                                    if (!empty($player_data['player_image_alts'])){
                                        $temp_alts_array = array_merge($temp_alts_array, $player_image_alts);
                                    }

                                    // Otherwise, if this is a copy player, append based on all the types in the index
                                    if ($has_elemental_alts){
                                        foreach ($mmrpg_types_index AS $type_token => $type_info){
                                            if (empty($type_token) || $type_token == 'none' || $type_token == 'copy' || $type_info['type_class'] == 'special'){ continue; }
                                            $temp_alts_array[] = array('token' => $type_token, 'name' => $player_data['player_name'].' ('.ucfirst($type_token).' Type)', 'summons' => 0, 'colour' => $type_token);
                                        }
                                    }

                                    // Otherwise, if this player has multiple sheets, add them as alt options
                                    if (!empty($player_data['player_image_sheets'])){
                                        for ($i = 2; $i <= $player_data['player_image_sheets']; $i++){
                                            $temp_alts_array[] = array('sheet' => $i, 'name' => $player_data['player_name'].' (Sheet #'.$i.')', 'summons' => 0);
                                        }
                                    }

                                    // Loop through the defined alts for this player and display image lists
                                    if (!empty($temp_alts_array)){
                                        foreach ($temp_alts_array AS $alt_key => $alt_info){

                                            $is_base_sprite = empty($alt_info['token']) ? true : false;
                                            if ($is_backup_data && $is_base_sprite){ continue; }
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

                                            <?= (!$is_backup_data && $alt_key > 0) || ($is_backup_data && $alt_key > 1) ? '<hr />' : '' ?>

                                            <div class="field fullsize" style="margin-bottom: 0; min-height: 0;">
                                                <strong class="label">
                                                    <? if ($is_base_sprite){ ?>
                                                        Base Sprite Sheets
                                                        <em>Main sprites used for player. Zoom and shadow sprites are auto-generated.</em>
                                                    <? } else { ?>
                                                        <?= ucfirst($alt_token).' Sprite Sheets'  ?>
                                                        <em>Sprites used for player's <strong><?= $alt_token ?></strong> skin. Zoom and shadow sprites are auto-generated.</em>
                                                    <? } ?>
                                                </strong>
                                            </div>
                                            <? if (!$is_base_sprite){ ?>
                                                <input class="hidden" type="hidden" name="player_image_alts[<?= $alt_token ?>][token]" value="<?= $alt_info['token'] ?>" maxlength="64" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                <div class="field">
                                                    <div class="label"><strong>Name</strong></div>
                                                    <input class="textbox" type="text" name="player_image_alts[<?= $alt_token ?>][name]" value="<?= $alt_info['name'] ?>" maxlength="64" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                </div>
                                                <div class="field">
                                                    <div class="label"><strong>Summons</strong></div>
                                                    <input class="textbox" type="number" name="player_image_alts[<?= $alt_token ?>][summons]" value="<?= $alt_info['summons'] ?>" maxlength="3" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                </div>
                                                <div class="field">
                                                    <div class="label">
                                                        <strong>Colour</strong>
                                                        <span class="type_span type_<?= (!empty($alt_info['colour']) ? $alt_info['colour'] : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="player_image_alts[<?= $alt_token ?>][colour]">&nbsp;</span>
                                                    </div>
                                                    <select class="select" name="player_image_alts[<?= $alt_token ?>][colour]" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?>>
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

                                            <? if (!$is_backup_data){ ?>

                                                <div class="field fullsize has2cols widecols multirow sprites has-filebars">

                                                    <div class="subfield" style="clear: left;" data-group="shadows" data-size="40">
                                                        <strong class="sublabel" style="font-size: 90%;">icon @ 100%</strong><br />
                                                        <ul class="files">
                                                            <?
                                                            $this_alt_path = $alt_file_path;
                                                            $group = 'sprites';
                                                            $sheet_width = 18;
                                                            $sheet_height = 19;
                                                            $file_name = 'chapter-sprite.gif';
                                                            $file_href = MMRPG_CONFIG_ROOTURL.$this_alt_path.$file_name;
                                                            $file_exists = in_array($file_name, $alt_files_existing) ? true : false;
                                                            $file_is_unused = false;
                                                            $file_is_optional = false;
                                                            echo('<li>');
                                                                echo('<div class="filebar" data-auto="file-bar" data-file-path="'.$this_alt_path.'" data-file-name="'.$file_name.'" data-file-kind="image/gif" data-file-width="'.$sheet_width.'" data-file-height="'.$sheet_height.'">');
                                                                    echo($file_exists ? '<a class="link view" href="'.$file_href.'?'.time().'" target="_blank" data-href="'.$file_href.'">'.$group.'/'.$file_name.'</a>' : '<a class="link view disabled" target="_blank" data-href="'.$file_href.'">'.$group.'/'.$file_name.'</a>');
                                                                    echo('<span class="info size">'.$sheet_width.'w &times; '.$sheet_height.'h</span>');
                                                                    echo($file_exists ? '<span class="info status good">&check;</span>' : '<span class="info status bad">&cross;</span>');
                                                                    if (!$is_backup_data){
                                                                        echo('<a class="action delete'.(!$file_exists ? ' disabled' : '').'" data-action="delete" data-file-hash="'.md5('delete/'.$this_alt_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">Delete</a>');
                                                                        echo('<a class="action upload'.($file_exists ? ' disabled' : '').'" data-action="upload" data-file-hash="'.md5('upload/'.$this_alt_path.$file_name.'/'.MMRPG_SETTINGS_PASSWORD_SALT).'">');
                                                                            echo('<span class="text">Upload</span>');
                                                                            echo('<input class="input" type="file" name="file_info" value=""'.($file_exists ? ' disabled="disabled"' : '').' />');
                                                                        echo('</a>');
                                                                    }
                                                                echo('</div>');
                                                            echo('</li>'.PHP_EOL);
                                                            ?>
                                                        </ul>
                                                    </div>

                                                    <?
                                                    $sheet_groups = array('sprites', 'shadows');
                                                    $sheet_kinds = array('mug', 'sprite');
                                                    $sheet_sizes = array($player_data['player_image_size'], $player_data['player_image_size'] * 2);
                                                    $sheet_directions = array('left', 'right');
                                                    $num_frames = count(explode('/', MMRPG_SETTINGS_PLAYER_FRAMEINDEX));
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
                                                                                if (!$is_backup_data && !$files_are_automatic){
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
                                                                    <input type="hidden" name="player_image_alts[<?= $alt_token ?>][delete_images]" value="0" checked="checked" />
                                                                    <input class="checkbox" type="checkbox" name="player_image_alts[<?= $alt_token ?>][delete_images]" value="1" />
                                                                </label>
                                                                <p class="subtext" style="color: #da1616;">Empty <strong>base</strong> image folder and remove all sprites/shadows</p>
                                                                <? if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/backups/players/'.($player_data['player_image']).'/')){ ?>
                                                                    <p class="subtext" style="color: #da1616;">(<a style="color: inherit; text-decoration: none;" href="images/viewer.php?path=backups/players/<?= $player_data['player_image'] ?>/" target="_blank"><u>view base backups</u> <i class="fas fa-external-link-square-alt"></i></a>)</p>
                                                                <? } ?>
                                                            </div>

                                                    <? } else { ?>

                                                            <div class="field checkwrap rfloat">
                                                                <label class="label">
                                                                    <strong style="color: #262626;">Auto-Generate Shadows?</strong>
                                                                    <input class="checkbox" type="checkbox" name="player_image_alts[<?= $alt_token ?>][generate_shadows]" value="1" <?= !empty($alt_shadows_existing) ? 'checked="checked"' : '' ?> />
                                                                </label>
                                                                <p class="subtext" style="color: #262626;">Only generate alt shadows if silhouette differs from base</p>
                                                            </div>

                                                            <div class="field checkwrap rfloat fullsize">
                                                                <label class="label">
                                                                    <strong style="color: #da1616;">Delete <?= ucfirst($alt_token) ?> Images?</strong>
                                                                    <input type="hidden" name="player_image_alts[<?= $alt_token ?>][delete_images]" value="0" checked="checked" />
                                                                    <input class="checkbox" type="checkbox" name="player_image_alts[<?= $alt_token ?>][delete_images]" value="1" />
                                                                </label>
                                                                <p class="subtext" style="color: #da1616;">Empty the <strong><?= $alt_token ?></strong> image folder and remove all sprites/shadows</p>
                                                                <? if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/backups/players/'.($player_data['player_image'].'_'.$alt_token).'/')){ ?>
                                                                    <p class="subtext" style="color: #da1616;">(<a style="color: inherit; text-decoration: none;" href="images/viewer.php?path=backups/players/<?= $player_data['player_image'].'_'.$alt_token ?>/" target="_blank"><u>view <?= $alt_token ?> backups</u> <i class="fas fa-external-link-square-alt"></i></a>)</p>
                                                                <? } ?>
                                                            </div>

                                                            <? if (!$has_elemental_alts){ ?>

                                                                    <div class="field checkwrap rfloat fullsize">
                                                                        <label class="label">
                                                                            <strong style="color: #da1616;">Delete <?= ucfirst($alt_token) ?> Data?</strong>
                                                                            <input type="hidden" name="player_image_alts[<?= $alt_token ?>][delete]" value="0" checked="checked" />
                                                                            <input class="checkbox" type="checkbox" name="player_image_alts[<?= $alt_token ?>][delete]" value="1" />
                                                                        </label>
                                                                        <p class="subtext" style="color: #da1616;">Remove <strong><?= $alt_token ?></strong> from the list (images will not be deleted)</p>
                                                                    </div>

                                                            <? } ?>

                                                    <? } ?>

                                                </div>

                                            <? } ?>


                                            <?

                                        }
                                    }

                                    //$base_sprite_list = getDirContents(MMRPG_CONFIG_ROOTDIR.$base_sprite_path);
                                    //echo('<pre>$base_sprite_path = '.print_r($base_sprite_path, true).'</pre>');
                                    //echo('<pre>$base_sprite_list = '.(!empty($base_sprite_list) ? htmlentities(print_r($base_sprite_list, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
                                    //echo('<pre>$temp_alts_array = '.(!empty($temp_alts_array) ? htmlentities(print_r($temp_alts_array, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                                    // Only if we're allowed to create new alts for this player
                                    if (false && $allow_new_alt_creation){
                                        echo('<hr />'.PHP_EOL);

                                        ?>
                                        <div class="field halfsize">
                                            <div class="label">
                                                <strong>Add Another Alt</strong>
                                                <em>select the alt you want to add and then save</em>
                                            </div>
                                            <select class="select" name="player_image_alts_new">
                                                <option value="">-</option>
                                                <?
                                                $alt_limit = 10;
                                                if ($alt_limit < count($player_image_alts)){ $alt_limit = count($player_image_alts) + 1; }
                                                foreach ($player_image_alts AS $info){ if (!empty($info['token'])){
                                                    $num = (int)(str_replace('alt', '', $info['token']));
                                                    if ($alt_limit < $num){ $alt_limit = $num + 1; }
                                                    } }
                                                for ($i = 1; $i <= $alt_limit; $i++){
                                                    $alt_token = 'alt'.($i > 1 ? $i : '');
                                                    ?>
                                                    <option value="<?= $alt_token ?>"<?= in_array($alt_token, $player_image_alts_tokens) ? ' disabled="disabled"' : '' ?>>
                                                        <?= $player_data['player_name'] ?>
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

                            <? if (!$is_backup_data){ ?>

                                <div class="panel" data-tab="functions">

                                    <div class="field fullsize codemirror <?= $is_backup_data ? 'readonly' : '' ?>" data-codemirror-mode="php">
                                        <div class="label">
                                            <strong>Player Functions</strong>
                                            <em>code is php-format with html allowed in some strings</em>
                                        </div>
                                        <?
                                        // Collect the markup for the player functions file
                                        if (!empty($_SESSION['player_functions_markup'][$player_data['player_id']])){
                                            $player_functions_markup = $_SESSION['player_functions_markup'][$player_data['player_id']];
                                            unset($_SESSION['player_functions_markup'][$player_data['player_id']]);
                                        } else {
                                            $template_functions_path = MMRPG_CONFIG_PLAYERS_CONTENT_PATH.'.player/functions.php';
                                            $player_functions_path = MMRPG_CONFIG_PLAYERS_CONTENT_PATH.$player_data['player_token'].'/functions.php';
                                            $player_functions_markup = file_exists($player_functions_path) ? file_get_contents($player_functions_path) : file_get_contents($template_functions_path);
                                        }
                                        ?>
                                        <textarea class="textarea" name="player_functions_markup" rows="<?= min(20, substr_count($player_functions_markup, PHP_EOL)) ?>"><?= htmlentities($player_functions_markup, ENT_QUOTES, 'UTF-8', true) ?></textarea>
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
                                        </div>
                                    </div>

                                </div>

                            <? } ?>

                            <? if (!$is_backup_data && !empty($player_backup_list)){ ?>
                                <div class="panel" data-tab="backups">
                                    <table class="backups">
                                        <colgroup>
                                            <col class="id" width="50" />
                                            <col class="name" width="" />
                                            <col class="date" width="100" />
                                            <col class="time" width="75" />
                                            <col class="actions" width="100" />
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th class="id">ID</th>
                                                <th class="name">Name</th>
                                                <th class="date">Date</th>
                                                <th class="time">Time</th>
                                                <th class="actions">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <? foreach ($player_backup_list AS $backup_key => $backup_info){ ?>
                                                <? $backup_unix_time = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})([0-9]{2})$/', '$1/$2/$3T$4:$5', $backup_info['backup_date_time'])); ?>
                                                <tr>
                                                    <td class="id"><?= $backup_info['backup_id'] ?></td>
                                                    <td class="name"><?= $backup_info['player_name'] ?></td>
                                                    <td class="date"><?= date('Y/m/d', $backup_unix_time) ?></td>
                                                    <td class="time"><?= date('g:i a', $backup_unix_time) ?></td>
                                                    <td class="actions">
                                                        <a href="admin/edit-players/editor/backup_id=<?= $backup_info['backup_id'] ?>" target="_blank" style="text-decoration: none;">
                                                            <span style="text-decoration: underline;">View Backup</span>
                                                            <i class="fas fa-external-link-square-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <? } ?>

                        </div>

                        <hr />

                        <div class="options">

                            <div class="field checkwrap">
                                <label class="label">
                                    <strong>Published</strong>
                                    <input type="hidden" name="player_flag_published" value="0" checked="checked" />
                                    <input class="checkbox" type="checkbox" name="player_flag_published" value="1" <?= !empty($player_data['player_flag_published']) ? 'checked="checked"' : '' ?> />
                                </label>
                                <p class="subtext">This player is ready to appear on the site</p>
                            </div>

                            <div class="field checkwrap">
                                <label class="label">
                                    <strong>Complete</strong>
                                    <input type="hidden" name="player_flag_complete" value="0" checked="checked" />
                                    <input class="checkbox" type="checkbox" name="player_flag_complete" value="1" <?= !empty($player_data['player_flag_complete']) ? 'checked="checked"' : '' ?> />
                                </label>
                                <p class="subtext">This player's sprites have been completed</p>
                            </div>

                            <div class="field checkwrap">
                                <label class="label">
                                    <strong>Hidden</strong>
                                    <input type="hidden" name="player_flag_hidden" value="0" checked="checked" />
                                    <input class="checkbox" type="checkbox" name="player_flag_hidden" value="1" <?= !empty($player_data['player_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                </label>
                                <p class="subtext">This player's data should stay hidden</p>
                            </div>

                        </div>

                        <hr />

                        <div class="formfoot">

                            <? if (!$is_backup_data){ ?>
                                <div class="buttons">
                                    <input class="button save" type="submit" value="Save Changes" />
                                    <? /*
                                    <input class="button cancel" type="button" value="Reset Changes" onclick="javascript:window.location.href='admin/edit-players/editor/player_id=<?= $player_data['player_id'] ?>';" />
                                    <input class="button delete" type="button" value="Delete Player" data-delete="players" data-player-id="<?= $player_data['player_id'] ?>" />
                                    */ ?>
                                </div>
                                <?= cms_admin::object_editor_print_git_footer_buttons('players', $player_data['player_token'], $mmrpg_git_file_arrays) ?>
                            <? } ?>

                            <? /*
                            <div class="metadata">
                                <div class="date"><strong>Created</strong>: <?= !empty($player_data['player_date_created']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $player_data['player_date_created'])): '-' ?></div>
                                <div class="date"><strong>Modified</strong>: <?= !empty($player_data['player_date_modified']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $player_data['player_date_modified'])) : '-' ?></div>
                            </div>
                            */ ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/scripts/update-image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_player_data = $player_data;
                $debug_player_data['player_description2'] = str_replace(PHP_EOL, '\\n', $debug_player_data['player_description2']);
                echo('<pre style="display: none;">$player_data = '.(!empty($debug_player_data) ? htmlentities(print_r($debug_player_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                ?>

                <?
                $temp_edit_markup = ob_get_clean();
                if ($is_backup_data){
                    $temp_edit_markup = str_replace('<input ', '<input readonly="readonly" disabled="disabled" ', $temp_edit_markup);
                    $temp_edit_markup = str_replace('<select ', '<select readonly="readonly" disabled="disabled" ', $temp_edit_markup);
                    $temp_edit_markup = str_replace('<textarea ', '<textarea readonly="readonly" ', $temp_edit_markup);
                }
                echo($temp_edit_markup).PHP_EOL;
            }

        }
        ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>