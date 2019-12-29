<? ob_start(); ?>

    <?

    // Pre-check access permissions before continuing
    if (!in_array('*', $this_adminaccess)
        && !in_array('edit_fields', $this_adminaccess)){
        $form_messages[] = array('error', 'You do not have permission to edit fields!');
        redirect_form_action('admin.php?action=home');
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
    $mmrpg_robots_index = $db->get_array_list("SELECT {$mmrpg_robots_fields} FROM mmrpg_index_robots WHERE robot_token <> 'robot' ORDER BY robot_class DESC, robot_order ASC", 'robot_token');
    $mmrpg_robots_index_byclass = array();
    if (!empty($mmrpg_robots_index)){
        foreach ($mmrpg_robots_index AS $token => $data){
            if (!isset($mmrpg_robots_index_byclass[$data['robot_class']])){ $mmrpg_robots_index_byclass[$data['robot_class']] = array(); }
            $mmrpg_robots_index_byclass[$data['robot_class']][] = $token;
        }
    }

    // Collect an index of robot colours for options
    $mmrpg_abilities_fields = rpg_ability::get_index_fields(true);
    $mmrpg_abilities_index = $db->get_array_list("SELECT {$mmrpg_abilities_fields} FROM mmrpg_index_abilities WHERE ability_token <> 'ability' AND ability_class <> 'system' ORDER BY ability_order ASC", 'ability_token');

    // Collect an index of field function files for options
    $functions_path = MMRPG_CONFIG_ROOTDIR.'data/';
    $functions_list = getDirContents($functions_path.'fields/');
    $mmrpg_functions_index = array();
    if (!empty($functions_list)){
        foreach ($functions_list as $key => $value){
            if (strstr($value, '_index.php')){ continue; }
            elseif (!preg_match('/\.php$/i', $value)){ continue; }
            $value = str_replace('\\', '/', $value);
            $value = str_replace($functions_path, '', $value);
            $mmrpg_functions_index[] = $value;
        }
        usort($mmrpg_functions_index, function($a, $b){
            $ax = explode('/', $a); $axcount = count($ax);
            $bx = explode('/', $b); $bxcount = count($bx);
            $az = strstr($a, '/mm') ? true : false;
            $bz = strstr($b, '/mm') ? true : false;
            if ($axcount < $bxcount){ return -1; }
            elseif ($axcount > $bxcount){ return 1; }
            elseif ($az && !$bz){ return -1; }
            elseif (!$az && $bz){ return 1; }
            elseif ($a < $b){ return -1; }
            elseif ($a > $b){ return 1; }
            else { return 0; }
            });
    }

    // Collect an index of contributors and admins that have made sprites
    $mmrpg_contributors_index = $db->get_array_list("SELECT
        users.user_id AS user_id,
        users.user_name AS user_name,
        users.user_name_public AS user_name_public,
        users.user_name_clean AS user_name_clean,
        uroles.role_level AS user_role_level,
        (CASE WHEN editors.field_image_count IS NOT NULL THEN editors.field_image_count ELSE 0 END) AS user_image_count,
        (CASE WHEN editors2.field_image_count2 IS NOT NULL THEN editors2.field_image_count2 ELSE 0 END) AS user_image_count2
        FROM
        mmrpg_users AS users
        LEFT JOIN mmrpg_roles AS uroles ON uroles.role_id = users.role_id
        LEFT JOIN (SELECT
                field_image_editor AS field_user_id,
                COUNT(field_image_editor) AS field_image_count
                FROM mmrpg_index_fields
                GROUP BY field_image_editor) AS editors ON editors.field_user_id = users.user_id
        LEFT JOIN (SELECT
                field_image_editor2 AS field_user_id,
                COUNT(field_image_editor2) AS field_image_count2
                FROM mmrpg_index_fields
                GROUP BY field_image_editor2) AS editors2 ON editors2.field_user_id = users.user_id
        WHERE
        users.user_id <> 0
        AND (uroles.role_level > 3
            OR users.user_credit_line <> ''
            OR users.user_credit_text <> ''
            OR editors.field_image_count IS NOT NULL
            OR editors2.field_image_count2 IS NOT NULL)
        ORDER BY
        uroles.role_level DESC,
        users.user_name_clean ASC
        ;", 'user_id');


    /* -- Form Setup Actions -- */

    // Define a function for exiting a field edit action
    function exit_field_edit_action($field_id = 0){
        if (!empty($field_id)){ $location = 'admin.php?action=edit_fields&subaction=editor&field_id='.$field_id; }
        else { $location = 'admin.php?action=edit_fields&subaction=search'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Fields | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if (false && $sub_action == 'delete' && !empty($_GET['field_id'])){

        // Collect form data for processing
        $delete_data['field_id'] = !empty($_GET['field_id']) && is_numeric($_GET['field_id']) ? trim($_GET['field_id']) : '';

        // Let's delete all of this field's data from the database
        $db->delete('mmrpg_index_fields', array('field_id' => $delete_data['field_id']));
        $form_messages[] = array('success', 'The requested field has been deleted from the database');
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

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_field_masters = rpg_field::get_index_fields(true, 'field');
        $search_query = "SELECT
            {$temp_field_masters}
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

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(field_id) AS total FROM mmrpg_index_fields WHERE 1=1 AND field_token <> 'field';", 'total');

    }

    // If we're in editor mode, we should collect field info from database
    $field_data = array();
    $editor_data = array();
    $is_backup_data = false;
    if ($sub_action == 'editor'
        && (!empty($_GET['field_id'])
            || !empty($_GET['backup_id']))){

        // Collect form data for processing
        $editor_data['field_id'] = !empty($_GET['field_id']) && is_numeric($_GET['field_id']) ? trim($_GET['field_id']) : '';
        if (empty($editor_data['field_id'])
            && !empty($_GET['backup_id'])
            && is_numeric($_GET['backup_id'])){
            $editor_data['backup_id'] = trim($_GET['backup_id']);
            $is_backup_data = true;
        }


        /* -- Collect Field Data -- */

        // Collect field details from the database
        $temp_field_masters = rpg_field::get_index_fields(true);
        if (!$is_backup_data){
            $field_data = $db->get_array("SELECT {$temp_field_masters} FROM mmrpg_index_fields WHERE field_id = {$editor_data['field_id']};");
        } else {
            $temp_field_backup_fields = str_replace('field_id,', 'backup_id AS field_id,', $temp_field_masters);
            $temp_field_backup_fields .= ', backup_date_time';
            $field_data = $db->get_array("SELECT {$temp_field_backup_fields} FROM mmrpg_index_fields_backups WHERE backup_id = {$editor_data['backup_id']};");
        }

        // If field data could not be found, produce error and exit
        if (empty($field_data)){ exit_field_edit_action(); }

        // Collect the field's name(s) for display
        $field_name_display = $field_data['field_name'];
        $this_page_tabtitle = $field_name_display.' | '.$this_page_tabtitle;
        if ($is_backup_data){ $this_page_tabtitle = str_replace('Edit Fields', 'View Backups', $this_page_tabtitle); }

        // If form data has been submit for this field, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit_fields'){

            // COLLECT form data from the request and parse out simple rules

            $old_field_token = !empty($_POST['old_field_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_field_token']) ? trim(strtolower($_POST['old_field_token'])) : '';

            $form_data['field_id'] = !empty($_POST['field_id']) && is_numeric($_POST['field_id']) ? trim($_POST['field_id']) : 0;
            $form_data['field_token'] = !empty($_POST['field_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_token']) ? trim(strtolower($_POST['field_token'])) : '';
            $form_data['field_name'] = !empty($_POST['field_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['field_name']) ? trim($_POST['field_name']) : '';
            $form_data['field_class'] = !empty($_POST['field_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_class']) ? trim(strtolower($_POST['field_class'])) : '';
            $form_data['field_type'] = !empty($_POST['field_type']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_type']) ? trim(strtolower($_POST['field_type'])) : '';
            $form_data['field_type2'] = !empty($_POST['field_type2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_type2']) ? trim(strtolower($_POST['field_type2'])) : '';

            $form_data['field_game'] = !empty($_POST['field_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_game']) ? trim($_POST['field_game']) : '';
            $form_data['field_group'] = ''; //!empty($_POST['field_group']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['field_group']) ? trim($_POST['field_group']) : '';
            $form_data['field_number'] = ''; //!empty($_POST['field_number']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['field_number']) ? trim($_POST['field_number']) : '';
            $form_data['field_order'] = !empty($_POST['field_order']) && is_numeric($_POST['field_order']) ? (int)(trim($_POST['field_order'])) : 0;

            $form_data['field_master'] = !empty($_POST['field_master']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_master']) ? trim(strtolower($_POST['field_master'])) : '';
            $form_data['field_master2'] = !empty($_POST['field_master2']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_master2']) ? trim(strtolower($_POST['field_master2'])) : '';
            $form_data['field_mechas'] = !empty($_POST['field_mechas']) && is_array($_POST['field_mechas']) ? array_values(array_unique(array_filter($_POST['field_mechas']))) : array();

            $form_data['field_energy'] = !empty($_POST['field_energy']) && is_numeric($_POST['field_energy']) ? (int)(trim($_POST['field_energy'])) : 0;
            $form_data['field_weapons'] = !empty($_POST['field_weapons']) && is_numeric($_POST['field_weapons']) ? (int)(trim($_POST['field_weapons'])) : 0;
            $form_data['field_attack'] = !empty($_POST['field_attack']) && is_numeric($_POST['field_attack']) ? (int)(trim($_POST['field_attack'])) : 0;
            $form_data['field_defense'] = !empty($_POST['field_defense']) && is_numeric($_POST['field_defense']) ? (int)(trim($_POST['field_defense'])) : 0;
            $form_data['field_speed'] = !empty($_POST['field_speed']) && is_numeric($_POST['field_speed']) ? (int)(trim($_POST['field_speed'])) : 0;

            $form_data['field_description'] = !empty($_POST['field_description']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['field_description']) ? trim($_POST['field_description']) : '';
            $form_data['field_description2'] = !empty($_POST['field_description2']) ? trim(strip_tags($_POST['field_description2'])) : '';

            $form_data['field_quotes_start'] = !empty($_POST['field_quotes_start']) ? trim(strip_tags($_POST['field_quotes_start'])) : '';
            $form_data['field_quotes_taunt'] = !empty($_POST['field_quotes_taunt']) ? trim(strip_tags($_POST['field_quotes_taunt'])) : '';
            $form_data['field_quotes_victory'] = !empty($_POST['field_quotes_victory']) ? trim(strip_tags($_POST['field_quotes_victory'])) : '';
            $form_data['field_quotes_defeat'] = !empty($_POST['field_quotes_defeat']) ? trim(strip_tags($_POST['field_quotes_defeat'])) : '';



            $form_data['field_functions'] = !empty($_POST['field_functions']) && preg_match('/^[-_0-9a-z\.\/]+$/i', $_POST['field_functions']) ? trim($_POST['field_functions']) : '';

            $form_data['field_image'] = !empty($_POST['field_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_image']) ? trim(strtolower($_POST['field_image'])) : '';
            $form_data['field_image_size'] = !empty($_POST['field_image_size']) && is_numeric($_POST['field_image_size']) ? (int)(trim($_POST['field_image_size'])) : 0;
            $form_data['field_image_editor'] = !empty($_POST['field_image_editor']) && is_numeric($_POST['field_image_editor']) ? (int)(trim($_POST['field_image_editor'])) : 0;
            $form_data['field_image_editor2'] = !empty($_POST['field_image_editor2']) && is_numeric($_POST['field_image_editor2']) ? (int)(trim($_POST['field_image_editor2'])) : 0;

            $form_data['field_flag_published'] = isset($_POST['field_flag_published']) && is_numeric($_POST['field_flag_published']) ? (int)(trim($_POST['field_flag_published'])) : 0;
            $form_data['field_flag_complete'] = isset($_POST['field_flag_complete']) && is_numeric($_POST['field_flag_complete']) ? (int)(trim($_POST['field_flag_complete'])) : 0;
            $form_data['field_flag_hidden'] = isset($_POST['field_flag_hidden']) && is_numeric($_POST['field_flag_hidden']) ? (int)(trim($_POST['field_flag_hidden'])) : 0;

            if ($form_data['field_type'] != 'copy'){
                $form_data['field_image_alts'] = !empty($_POST['field_image_alts']) && is_array($_POST['field_image_alts']) ? array_filter($_POST['field_image_alts']) : array();
                $field_image_alts_new = !empty($_POST['field_image_alts_new']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['field_image_alts_new']) ? trim(strtolower($_POST['field_image_alts_new'])) : '';
            } else {
                $form_data['field_image_alts'] = array();
                $field_image_alts_new = '';
            }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'field_image_alts\']  = '.print_r($_POST['field_image_alts'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'field_image_alts_new\']  = '.print_r($_POST['field_image_alts_new'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (empty($form_data['field_id'])){ $form_messages[] = array('error', 'Field ID was not provided'); $form_success = false; }
            if (empty($form_data['field_token']) || empty($old_field_token)){ $form_messages[] = array('error', 'Field Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['field_name'])){ $form_messages[] = array('error', 'Field Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['field_class'])){ $form_messages[] = array('error', 'Field Kind was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['field_type']) || !isset($_POST['field_type2'])){ $form_messages[] = array('warning', 'Types were not provided or were invalid'); $form_success = false; }
            if (empty($form_data['field_gender'])){ $form_messages[] = array('error', 'Field Gender was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_field_edit_action($form_data['field_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (empty($form_data['field_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            if (empty($form_data['field_group'])){ $form_messages[] = array('warning', 'Sorting Group was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if (isset($form_data['field_type'])){
                // Fix any type ordering problems (like selecting Neutral + anything)
                $types = array_values(array_filter(array($form_data['field_type'], $form_data['field_type2'])));
                $form_data['field_type'] = isset($types[0]) ? $types[0] : '';
                $form_data['field_type2'] = isset($types[1]) ? $types[1] : '';
            }

            if (!empty($form_data['field_abilities_rewards'])){
                $new_rewards = array();
                $new_rewards_tokens = array();
                foreach ($form_data['field_abilities_rewards'] AS $key => $reward){
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
                $form_data['field_abilities_rewards'] = $new_rewards;
            }


            if (isset($form_data['field_abilities_rewards'])){ $form_data['field_abilities_rewards'] = !empty($form_data['field_abilities_rewards']) ? json_encode($form_data['field_abilities_rewards']) : ''; }
            if (isset($form_data['field_mechas'])){ $form_data['field_mechas'] = !empty($form_data['field_mechas']) ? json_encode($form_data['field_mechas']) : ''; }

            $empty_image_folders = array();

            if (isset($form_data['field_image_alts'])){
                if (!empty($field_image_alts_new)){
                    $alt_num = $field_image_alts_new != 'alt' ? (int)(str_replace('alt', '', $field_image_alts_new)) : 1;
                    $alt_name = ucfirst($field_image_alts_new);
                    if ($alt_num == 9){ $alt_name = 'Darkness Alt'; }
                    elseif ($alt_num == 3){ $alt_name = 'Weapon Alt'; }
                    $form_data['field_image_alts'][$field_image_alts_new] = array(
                        'token' => $field_image_alts_new,
                        'name' => $form_data['field_name'].' ('.$alt_name.')',
                        'summons' => ($alt_num * 100),
                        'colour' => ($alt_num == 9 ? 'empty' : 'none')
                        );
                }
                $alt_keys = array_keys($form_data['field_image_alts']);
                usort($alt_keys, function($a, $b){
                    $a = strstr($a, 'alt') ? (int)(str_replace('alt', '', $a)) : 0;
                    $b = strstr($b, 'alt') ? (int)(str_replace('alt', '', $b)) : 0;
                    if ($a < $b){ return -1; }
                    elseif ($a > $b){ return 1; }
                    else { return 0; }
                    });
                $new_field_image_alts = array();
                foreach ($alt_keys AS $alt_key){
                    $alt_info = $form_data['field_image_alts'][$alt_key];
                    $alt_path = $field_data['field_image'].($alt_key != 'base' ? '_'.$alt_key : '');
                    if (!empty($alt_info['delete_images'])){
                        $delete_sprite_path = 'images/fields/'.$alt_path.'/';
                        $delete_shadow_path = 'images/fields_shadows/'.$alt_path.'/';
                        $empty_image_folders[] = $delete_sprite_path;
                        $empty_image_folders[] = $delete_shadow_path;
                    }
                    if (!empty($alt_info['delete'])){ continue; }
                    elseif ($alt_key == 'base'){ continue; }
                    unset($alt_info['delete_images'], $alt_info['delete']);
                    $new_field_image_alts[] = $alt_info;
                }
                $form_data['field_image_alts'] = $new_field_image_alts;
                $form_data['field_image_alts'] = !empty($form_data['field_image_alts']) ? json_encode($form_data['field_image_alts']) : '';
            }
            //$form_messages[] = array('alert', '<pre>$form_data[\'field_image_alts\']  = '.print_r($form_data['field_image_alts'] , true).'</pre>');

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
                    $backup_path = str_replace('/images/', '/images/backups/', MMRPG_CONFIG_ROOTDIR.$empty_path);
                    if (!file_exists($backup_path)){
                        @mkdir($backup_path);
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

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // Make a copy of the update data sans the field ID
            $update_data = $form_data;
            unset($update_data['field_id']);

            // If a recent backup of this data doesn't exist, create one now
            $backup_date_time = date('Ymd-Hi');
            $backup_exists = $db->get_value("SELECT backup_id FROM mmrpg_index_fields_backups WHERE field_token = '{$update_data['field_token']}' AND backup_date_time = '{$backup_date_time}';", 'backup_id');
            if (empty($backup_exists)){
                //$backup_data = $update_data;
                $backup_data = $field_data;
                unset($backup_data['field_id']);
                $backup_data['backup_date_time'] = $backup_date_time;
                $db->insert('mmrpg_index_fields_backups', $backup_data);
            }

            // Update the main database index with changes to this field's data
            $update_results = $db->update('mmrpg_index_fields', $update_data, array('field_id' => $form_data['field_id']));

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If we made it this far, the update must have been a success
            if ($update_results !== false){ $form_success = true; $form_messages[] = array('success', 'Field data was updated successfully!'); }
            else { $form_success = false; $form_messages[] = array('error', 'Field data could not be updated...'); }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // We're done processing the form, we can exit
            exit_field_edit_action($form_data['field_id']);

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }


    ?>

    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=edit_fields">Edit Fields</a>
        <? if ($sub_action == 'editor' && !empty($field_data)): ?>
            <? if (!$is_backup_data){ ?>
                &raquo; <a href="admin.php?action=edit_fields&amp;subaction=editor&amp;field_id=<?= $field_data['field_id'] ?>"><?= $field_name_display ?></a>
            <? } else { ?>
                &raquo; <a><?= $field_name_display ?></a>
            <? } ?>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit_fields">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Fields</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="action" value="edit_fields" />
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

                    <div class="field fullsize has3cols flags">
                    <?
                    $flag_names = array(
                        'published' => array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished'),
                        'complete' => array('icon' => 'fas fa-check-circle', 'yes' => 'Complete', 'no' => 'Incomplete'),
                        'hidden' => array('icon' => 'fas fa-eye-slash', 'yes' => 'Hidden', 'no' => 'Visible')
                        );
                    foreach ($flag_names AS $flag_token => $flag_info){
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
                        <input class="button" type="submit" value="Search" />
                        <input class="button" type="reset" value="Reset" onclick="javascript:window.location.href='admin.php?action=edit_fields';" />
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

                                $field_edit_url = 'admin.php?action=edit_fields&subaction=editor&field_id='.$field_id;
                                $field_name_link = '<a class="link" href="'.$field_edit_url.'">'.$field_name.'</a>';

                                $field_actions = '';
                                $field_actions .= '<a class="link edit" href="'.$field_edit_url.'"><span>edit</span></a>';
                                $field_actions .= '<span class="link delete disabled"><span>delete</span></span>';
                                //$field_actions .= '<a class="link delete" data-delete="fields" data-field-id="'.$field_id.'"><span>delete</span></a>';

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
            && (!empty($_GET['field_id']) || !empty($_GET['backup_id']))){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($field_data['field_type']) ? $field_data['field_type'].(!empty($field_data['field_type2']) ? '_'.$field_data['field_type2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="field_type,field_type2">
                        <span class="title"><?= !$is_backup_data ? 'Edit' : 'View' ?> Field &quot;<?= $field_name_display ?>&quot;</span>
                        <?
                        // If this is NOT backup data, we can generate links
                        if (!$is_backup_data){

                            // If the field is published, generate and display a preview link
                            if (!empty($field_data['field_flag_published'])){
                                $preview_link = 'database/';
                                if ($field_data['field_class'] === 'master'){ $preview_link .= 'fields/'; }
                                elseif ($field_data['field_class'] === 'mecha'){ $preview_link .= 'mechas/'; }
                                elseif ($field_data['field_class'] === 'boss'){ $preview_link .= 'bosses/'; }
                                $preview_link .= $field_data['field_token'].'/';
                                echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                                echo '<a class="preview" href="'.$preview_link.'preview=true" target="_blank">Preview <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                            }

                        }
                        // Otherwise we'll simply show the backup creation date
                        else {

                            // Print out the creation date in a readable form
                            echo '<span style="display: block; clear: left; font-size: 90%; font-weight: normal;">Backup Created '.date('Y/m/d @ g:s a', strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})([0-9]{2})$/', '$1/$2/$3T$4:$5', $field_data['backup_date_time']))).'</span>';

                        }

                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <?
                    // Collect a list of backups for this field from the database, if any
                    $field_backup_list = $db->get_array_list("SELECT
                        backup_id, field_token, field_name, backup_date_time
                        FROM mmrpg_index_fields_backups
                        WHERE field_token = '{$field_data['field_token']}'
                        ORDER BY backup_date_time DESC
                        ;");
                    ?>

                    <div class="editor-tabs" data-tabgroup="field">
                        <a class="tab active" data-tab="basic">Basic</a><span></span>
                        <a class="tab" data-tab="flavour">Flavour</a><span></span>
                        <a class="tab" data-tab="background">Background</a><span></span>
                        <a class="tab" data-tab="foreground">Foreground</a><span></span>
                        <a class="tab" data-tab="sprites">Sprites</a><span></span>
                        <? if (!$is_backup_data && !empty($field_backup_list)){ ?>
                            <a class="tab" data-tab="backups">Backups</a><span></span>
                        <? } ?>
                    </div>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit_fields" />
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
                                        <em>avoid changing</em>
                                    </div>
                                    <input type="hidden" name="old_field_token" value="<?= $field_data['field_token'] ?>" />
                                    <input class="textbox" type="text" name="field_token" value="<?= $field_data['field_token'] ?>" maxlength="64" />
                                </div>

                                <div class="field">
                                    <strong class="label">Field Name</strong>
                                    <input class="textbox" type="text" name="field_name" value="<?= $field_data['field_name'] ?>" maxlength="128" />
                                </div>

                                <div class="field foursize">
                                    <strong class="label">Field Kind</strong>
                                    <select class="select" name="field_class">
                                        <option value="system" <?= empty($field_data['field_class']) || $field_data['field_class'] == 'system' ? 'selected="selected"' : '' ?>>System Field</option>
                                        <option value="master" <?= $field_data['field_class'] == 'master' ? 'selected="selected"' : '' ?>>Master Field</option>
                                        <option value="player" <?= $field_data['field_class'] == 'player' ? 'selected="selected"' : '' ?>>Player Field</option>
                                        <option value="event" <?= $field_data['field_class'] == 'event' ? 'selected="selected"' : '' ?>>Event Field</option>
                                        <option value="bonus" <?= $field_data['field_class'] == 'bonus' ? 'selected="selected"' : '' ?>>Bonus Field</option>
                                    </select><span></span>
                                </div>

                                <div class="field foursize">
                                    <strong class="label">
                                        Type
                                        <span class="type_span type_<?= (!empty($field_data['field_type']) ? $field_data['field_type'].(!empty($field_data['field_type2']) ? '_'.$field_data['field_type2'] : '') : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="field_type,field_type2">&nbsp;</span>
                                    </strong>
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

                                <div class="field foursize">
                                    <strong class="label">Source Game</strong>
                                    <select class="select" name="field_game">
                                        <?
                                        //$field_games_tokens = $db->get_array_list("SELECT DISTINCT (field_game) AS game_token FROM mmrpg_index_fields WHERE field_game <> '' ORDER BY field_game ASC;", 'game_token');
                                        $field_games_tokens = $db->get_array_list("SELECT DISTINCT (robot_game) AS game_token FROM mmrpg_index_robots WHERE robot_game <> '' ORDER BY robot_game ASC;", 'game_token');
                                        echo('<option value=""'.(empty($field_data['field_game']) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($field_games_tokens AS $game_token => $game_data){
                                            $label = $game_token;
                                            $selected = !empty($field_data['field_game']) && $field_data['field_game'] == $game_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$game_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        ?>
                                    </select><span></span>
                                </div>

                                <div class="field foursize">
                                    <strong class="label">Sort Order</strong>
                                    <input class="textbox" type="number" name="field_order" value="<?= $field_data['field_order'] ?>" maxlength="8" />
                                </div>

                                <hr />

                                <div class="field halfsize">
                                    <strong class="label">Field Master</strong>
                                    <select class="select" name="field_master">
                                        <?
                                        $temp_class_group = '';
                                        echo('<option value=""'.(empty($field_data['field_master']) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($mmrpg_robots_index AS $robot_token => $robot_data){
                                            if ($field_data['field_class'] === 'master' && $robot_data['robot_class'] !== 'master'){ continue; }
                                            elseif ($field_data['field_class'] !== 'master' && $robot_data['robot_class'] === 'mecha'){ continue; }
                                            if ($temp_class_group !== $robot_data['robot_class']){
                                                if (!empty($temp_class_group)){ echo('</optgroup>'); }
                                                $temp_class_group = $robot_data['robot_class'];
                                                echo('<optgroup label="'.ucfirst($temp_class_group).(substr($temp_class_group, -1, 1) === 's' ? 'es' : 's').'">');
                                            }
                                            $label = $robot_data['robot_name'];
                                            $label .= ' ('.(!empty($robot_data['robot_core']) ? ucfirst($robot_data['robot_core']) : 'Neutral').(!empty($robot_data['robot_core2']) ? '/'.ucfirst($robot_data['robot_core2']) : '').')';
                                            $selected = !empty($field_data['field_master']) && $field_data['field_master'] == $robot_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        if (!empty($temp_class_group)){ echo ('</optgroup>'); }
                                        ?>
                                    </select><span></span>
                                </div>

                                <div class="field halfsize">
                                    <strong class="label">Secondary Master</strong>
                                    <select class="select" name="field_master2">
                                        <?
                                        $actual_field_master2 = !empty($field_data['field_master2']) ? json_decode($field_data['field_master2'])[0] : '';
                                        $temp_class_group = '';
                                        echo('<option value=""'.(empty($actual_field_master2) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($mmrpg_robots_index AS $robot_token => $robot_data){
                                            if ($field_data['field_class'] === 'master' && $robot_data['robot_class'] !== 'master'){ continue; }
                                            elseif ($field_data['field_class'] !== 'master' && $robot_data['robot_class'] === 'mecha'){ continue; }
                                            if ($temp_class_group !== $robot_data['robot_class']){
                                                if (!empty($temp_class_group)){ echo('</optgroup>'); }
                                                $temp_class_group = $robot_data['robot_class'];
                                                echo('<optgroup label="'.ucfirst($temp_class_group).(substr($temp_class_group, -1, 1) === 's' ? 'es' : 's').'">');
                                            }
                                            $label = $robot_data['robot_name'];
                                            $label .= ' ('.(!empty($robot_data['robot_core']) ? ucfirst($robot_data['robot_core']) : 'Neutral').(!empty($robot_data['robot_core2']) ? '/'.ucfirst($robot_data['robot_core2']) : '').')';
                                            $selected = !empty($actual_field_master2) && $actual_field_master2 == $robot_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        if (!empty($temp_class_group)){ echo ('</optgroup>'); }
                                        ?>
                                    </select><span></span>
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
                                    $current_multipliers = !empty($field_data['field_multipliers']) ? json_decode($field_data['field_multipliers'], true) : array();
                                    $current_multipliers_types = array_keys($current_multipliers);
                                    $select_limit = max(4, (count($current_multipliers_types) + 1));
                                    $select_limit += 0 - ($select_limit % 2);
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_type_token = isset($current_multipliers_types[$i]) ? $current_multipliers_types[$i] : '';
                                        $current_type_value = (!empty($current_multipliers[$current_type_token]) ? $current_multipliers[$current_type_token] : 1) * 1;
                                        //if ($current_type_value === 0 || $current_type_value === 1){ continue; }
                                        if ($current_type_value === 0 || $current_type_value === 1){ $current_type_token = ''; $current_type_value = '1.0'; }
                                        ?>
                                        <div class="subfield levelup">
                                            <div class="select-span-wrap"><select class="select" name="field_multipliers[<?= $i ?>][token]">
                                                <?= str_replace('value="'.$current_type_token.'"', 'value="'.$current_type_token.'" selected="selected"', $multiplier_options_markup) ?>
                                            </select><span></span></div>
                                            <input class="textbox" type="number" name="field_multipliers[<?= $i ?>][value]" value="<?= $current_type_value ?>" maxlength="3" placeholder="1.0" step="0.1" min="0.1" max="9.9"  />
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>

                                <hr />

                                <?

                                // Pre-generate a list of all robots so we can re-use it over and over
                                $temp_class_group = '';
                                $robot_options_markup = array();
                                $robot_options_markup[] = '<option value="">-</option>';
                                foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
                                    if ($robot_info['robot_class'] !== 'mecha'){ continue; }
                                    if ($temp_class_group !== $robot_info['robot_class']){
                                        if (!empty($temp_class_group)){ $robot_options_markup[] = '</optgroup>'; }
                                        $temp_class_group = $robot_info['robot_class'];
                                        $robot_options_markup[] = '<optgroup label="'.ucfirst($temp_class_group).(substr($temp_class_group, -1, 1) === 's' ? 'es' : 's').'">';
                                    }
                                    $robot_name = $robot_info['robot_name'];
                                    $robot_cores = ucwords(implode(' / ', array_values(array_filter(array($robot_info['robot_core'], $robot_info['robot_core2'])))));
                                    if (empty($robot_cores)){ $robot_cores = 'Neutral'; }
                                    $robot_options_markup[] = '<option value="'.$robot_token.'">'.$robot_name.' ('.$robot_cores.')</option>';
                                }
                                if (!empty($temp_class_group)){ $robot_options_markup[] = '</optgroup>'; }
                                $robot_options_count = count($robot_options_markup);
                                $robot_options_markup = implode(PHP_EOL, $robot_options_markup);

                                ?>

                                <div class="field fullsize has2cols multirow">
                                    <strong class="label">
                                        Support Mechas
                                        <em>These are the mechas that appear in the background/foreground and in battle</em>
                                    </strong>
                                    <?
                                    $current_robot_list = !empty($field_data['field_mechas']) ? json_decode($field_data['field_mechas'], true) : array();
                                    $select_limit = max(4, count($current_robot_list));
                                    $select_limit += 0 - ($select_limit % 2);
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_value = isset($current_robot_list[$i]) ? $current_robot_list[$i] : '';
                                        ?>
                                        <div class="subfield">
                                            <select class="select" name="field_mechas[<?= $i ?>]">
                                                <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $robot_options_markup) ?>
                                            </select><span></span>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>

                                <hr />

                                <?

                                // Pre-generate a list of all functions so we can re-use it over and over
                                $function_options_markup = array();
                                $function_options_markup[] = '<option value="">-</option>';
                                foreach ($mmrpg_functions_index AS $function_key => $function_path){
                                    $function_options_markup[] = '<option value="'.$function_path.'">'.$function_path.'</option>';
                                }
                                $function_options_count = count($function_options_markup);
                                $function_options_markup = implode(PHP_EOL, $function_options_markup);

                                ?>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Field Functions</strong>
                                        <em>file path for script with field functions like onload, ondefeat, etc.</em>
                                    </div>
                                    <select class="select" name="field_functions">
                                        <?= str_replace('value="'.$field_data['field_functions'].'"', 'value="'.$field_data['field_functions'].'" selected="selected"', $function_options_markup) ?>
                                    </select><span></span>
                                </div>

                            </div>

                            <div class="panel" data-tab="flavour">

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Field Class</strong>
                                        <em>three-four word classification</em>
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

                            </div>

                            <div class="panel" data-tab="robots"></div>

                            <div class="panel" data-tab="sprites">

                                <?

                                // Pre-generate a list of all contributors so we can re-use it over and over
                                $contributor_options_markup = array();
                                $contributor_options_markup[] = '<option value="0">-</option>';
                                foreach ($mmrpg_contributors_index AS $user_id => $user_info){
                                    $option_label = $user_info['user_name'];
                                    if (!empty($user_info['user_name_public']) && $user_info['user_name_public'] !== $user_info['user_name']){ $option_label = $user_info['user_name_public'].' ('.$option_label.')'; }
                                    $contributor_options_markup[] = '<option value="'.$user_id.'">'.$option_label.'</option>';
                                }
                                $contributor_options_count = count($contributor_options_markup);
                                $contributor_options_markup = implode(PHP_EOL, $contributor_options_markup);

                                ?>

                                <? $placeholder_folder = $field_data['field_class'] != 'master' ? $field_data['field_class'] : 'field'; ?>
                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Path</strong>
                                        <em>base image path for sprites</em>
                                    </div>
                                    <select class="select" name="field_image">
                                        <option value="<?= $placeholder_folder ?>" <?= $field_data['field_image'] == $placeholder_folder ? 'selected="selected"' : '' ?>>-</option>
                                        <option value="<?= $field_data['field_token'] ?>" <?= $field_data['field_image'] == $field_data['field_token'] ? 'selected="selected"' : '' ?>>images/fields/<?= $field_data['field_token'] ?>/</option>
                                    </select><span></span>
                                </div>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Size</strong>
                                        <em>base frame size for each sprite</em>
                                    </div>
                                    <select class="select" name="field_image_size">
                                        <? if ($field_data['field_image'] == $placeholder_folder){ ?>
                                            <option value="<?= $field_data['field_image_size'] ?>" selected="selected">-</option>
                                            <option value="40">40x40</option>
                                            <option value="80">80x80</option>
                                            <option disabled="disabled" value="160">160x160</option>
                                        <? } else { ?>
                                            <option value="40" <?= $field_data['field_image_size'] == 40 ? 'selected="selected"' : '' ?>>40x40</option>
                                            <option value="80" <?= $field_data['field_image_size'] == 80 ? 'selected="selected"' : '' ?>>80x80</option>
                                            <option disabled="disabled" value="160" <?= $field_data['field_image_size'] == 160 ? 'selected="selected"' : '' ?>>160x160</option>
                                        <? } ?>
                                    </select><span></span>
                                </div>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Editor #1</strong>
                                        <em>user who edited or created this sprite</em>
                                    </div>
                                    <? if ($field_data['field_image'] != $placeholder_folder){ ?>
                                        <select class="select" name="field_image_editor">
                                            <?= str_replace('value="'.$field_data['field_image_editor'].'"', 'value="'.$field_data['field_image_editor'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    <? } else { ?>
                                        <input type="hidden" name="field_image_editor" value="<?= $field_data['field_image_editor'] ?>" />
                                        <input class="textbox" type="text" name="field_image_editor" value="-" disabled="disabled" />
                                    <? } ?>
                                </div>

                                <div class="field halfsize">
                                    <div class="label">
                                        <strong>Sprite Editor #2</strong>
                                        <em>another user who collaborated on this sprite</em>
                                    </div>
                                    <? if ($field_data['field_image'] != $placeholder_folder){ ?>
                                        <select class="select" name="field_image_editor2">
                                            <?= str_replace('value="'.$field_data['field_image_editor2'].'"', 'value="'.$field_data['field_image_editor2'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    <? } else { ?>
                                        <input type="hidden" name="field_image_editor2" value="<?= $field_data['field_image_editor2'] ?>" />
                                        <input class="textbox" type="text" name="field_image_editor2" value="-" disabled="disabled" />
                                    <? } ?>
                                </div>

                                <?

                                // Decompress existing image alts pulled from the database
                                $field_image_alts = !empty($field_data['field_image_alts']) ? json_decode($field_data['field_image_alts'], true) : array();

                                // Collect the alt tokens from all defined alts so far
                                $field_image_alts_tokens = array();
                                foreach ($field_image_alts AS $alt){ if (!empty($alt['token'])){ $field_image_alts_tokens[] = $alt['token'];  } }

                                // Define a variable to toggle allowance of new alt creation
                                $has_elemental_alts = $field_data['field_type'] == 'copy' ? true : false;
                                $allow_new_alt_creation = !$has_elemental_alts ? true : false;

                                // Only proceed if all required sprite fields are set
                                if (!empty($field_data['field_image'])
                                    && !in_array($field_data['field_image'], array('field', 'master', 'boss', 'mecha'))
                                    && !empty($field_data['field_image_size'])
                                    && !($is_backup_data && $has_elemental_alts)){

                                    echo('<hr />'.PHP_EOL);

                                    // Define the base sprite and shadow paths for this field given its image token
                                    $base_sprite_path = 'images/fields/'.$field_data['field_image'].'/';
                                    $base_shadow_path = 'images/fields_shadows/'.$field_data['field_image'].'/';

                                    // Define the alts we'll be looping through for this field
                                    $temp_alts_array = array();
                                    $temp_alts_array[] = array('token' => '', 'name' => $field_data['field_name'], 'summons' => 0);

                                    // Append predefined alts automatically, based on the field image alt array
                                    if (!empty($field_data['field_image_alts'])){
                                        $temp_alts_array = array_merge($temp_alts_array, $field_image_alts);
                                    }

                                    // Otherwise, if this is a copy field, append based on all the types in the index
                                    if ($has_elemental_alts){
                                        foreach ($mmrpg_types_index AS $type_token => $type_info){
                                            if (empty($type_token) || $type_token == 'none' || $type_token == 'copy' || $type_info['type_class'] == 'special'){ continue; }
                                            $temp_alts_array[] = array('token' => $type_token, 'name' => $field_data['field_name'].' ('.ucfirst($type_token).' Type)', 'summons' => 0, 'colour' => $type_token);
                                        }
                                    }

                                    // Otherwise, if this field has multiple sheets, add them as alt options
                                    if (!empty($field_data['field_image_sheets'])){
                                        for ($i = 2; $i <= $field_data['field_image_sheets']; $i++){
                                            $temp_alts_array[] = array('sheet' => $i, 'name' => $field_data['field_name'].' (Sheet #'.$i.')', 'summons' => 0);
                                        }
                                    }

                                    // Loop through the defined alts for this field and display image lists
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
                                                        <em>Main sprites used for field. Zoom and shadow sprites are auto-generated.</em>
                                                    <? } else { ?>
                                                        <?= ucfirst($alt_token).' Sprite Sheets'  ?>
                                                        <em>Sprites used for field's <strong><?= $alt_token ?></strong> skin. Zoom and shadow sprites are auto-generated.</em>
                                                    <? } ?>
                                                </strong>
                                            </div>
                                            <? if (!$is_base_sprite){ ?>
                                                <input class="hidden" type="hidden" name="field_image_alts[<?= $alt_token ?>][token]" value="<?= $alt_info['token'] ?>" maxlength="64" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                <div class="field">
                                                    <div class="label"><strong>Name</strong></div>
                                                    <input class="textbox" type="text" name="field_image_alts[<?= $alt_token ?>][name]" value="<?= $alt_info['name'] ?>" maxlength="64" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                </div>
                                                <div class="field">
                                                    <div class="label"><strong>Summons</strong></div>
                                                    <input class="textbox" type="number" name="field_image_alts[<?= $alt_token ?>][summons]" value="<?= $alt_info['summons'] ?>" maxlength="3" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?> />
                                                </div>
                                                <div class="field">
                                                    <div class="label">
                                                        <strong>Colour</strong>
                                                        <span class="type_span type_<?= (!empty($alt_info['colour']) ? $alt_info['colour'] : 'none') ?> swatch floatright" data-auto="field-type" data-field-type="field_image_alts[<?= $alt_token ?>][colour]">&nbsp;</span>
                                                    </div>
                                                    <select class="select" name="field_image_alts[<?= $alt_token ?>][colour]" <?= $has_elemental_alts ? 'disabled="disabled"' : '' ?>>
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
                                                    <?
                                                    $sheet_groups = array('sprites', 'shadows');
                                                    $sheet_kinds = array('mug', 'sprite');
                                                    $sheet_sizes = array($field_data['field_image_size'], $field_data['field_image_size'] * 2);
                                                    $sheet_directions = array('left', 'right');
                                                    $num_frames = count(explode('/', MMRPG_SETTINGS_FIELD_FRAMEINDEX));
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
                                                                    <input type="hidden" name="field_image_alts[<?= $alt_token ?>][delete_images]" value="0" checked="checked" />
                                                                    <input class="checkbox" type="checkbox" name="field_image_alts[<?= $alt_token ?>][delete_images]" value="1" />
                                                                </label>
                                                                <p class="subtext" style="color: #da1616;">Empty <strong>base</strong> image folder and remove all sprites/shadows</p>
                                                                <? if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/backups/fields/'.($field_data['field_image']).'/')){ ?>
                                                                    <p class="subtext" style="color: #da1616;">(<a style="color: inherit; text-decoration: none;" href="images/viewer.php?path=backups/fields/<?= $field_data['field_image'] ?>/" target="_blank"><u>view base backups</u> <i class="fas fa-external-link-square-alt"></i></a>)</p>
                                                                <? } ?>
                                                            </div>

                                                    <? } else { ?>

                                                            <div class="field checkwrap rfloat">
                                                                <label class="label">
                                                                    <strong style="color: #262626;">Auto-Generate Shadows?</strong>
                                                                    <input class="checkbox" type="checkbox" name="field_image_alts[<?= $alt_token ?>][generate_shadows]" value="1" <?= !empty($alt_shadows_existing) ? 'checked="checked"' : '' ?> />
                                                                </label>
                                                                <p class="subtext" style="color: #262626;">Only generate alt shadows if silhouette differs from base</p>
                                                            </div>

                                                            <div class="field checkwrap rfloat fullsize">
                                                                <label class="label">
                                                                    <strong style="color: #da1616;">Delete <?= ucfirst($alt_token) ?> Images?</strong>
                                                                    <input type="hidden" name="field_image_alts[<?= $alt_token ?>][delete_images]" value="0" checked="checked" />
                                                                    <input class="checkbox" type="checkbox" name="field_image_alts[<?= $alt_token ?>][delete_images]" value="1" />
                                                                </label>
                                                                <p class="subtext" style="color: #da1616;">Empty the <strong><?= $alt_token ?></strong> image folder and remove all sprites/shadows</p>
                                                                <? if (file_exists(MMRPG_CONFIG_ROOTDIR.'images/backups/fields/'.($field_data['field_image'].'_'.$alt_token).'/')){ ?>
                                                                    <p class="subtext" style="color: #da1616;">(<a style="color: inherit; text-decoration: none;" href="images/viewer.php?path=backups/fields/<?= $field_data['field_image'].'_'.$alt_token ?>/" target="_blank"><u>view <?= $alt_token ?> backups</u> <i class="fas fa-external-link-square-alt"></i></a>)</p>
                                                                <? } ?>
                                                            </div>

                                                            <? if (!$has_elemental_alts){ ?>

                                                                    <div class="field checkwrap rfloat fullsize">
                                                                        <label class="label">
                                                                            <strong style="color: #da1616;">Delete <?= ucfirst($alt_token) ?> Data?</strong>
                                                                            <input type="hidden" name="field_image_alts[<?= $alt_token ?>][delete]" value="0" checked="checked" />
                                                                            <input class="checkbox" type="checkbox" name="field_image_alts[<?= $alt_token ?>][delete]" value="1" />
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

                                    // Only if we're allowed to create new alts for this field
                                    if ($allow_new_alt_creation){
                                        echo('<hr />'.PHP_EOL);

                                        ?>
                                        <div class="field halfsize">
                                            <div class="label">
                                                <strong>Add Another Alt</strong>
                                                <em>select the alt you want to add and then save</em>
                                            </div>
                                            <select class="select" name="field_image_alts_new">
                                                <option value="">-</option>
                                                <?
                                                $alt_limit = 10;
                                                if ($alt_limit < count($field_image_alts)){ $alt_limit = count($field_image_alts) + 1; }
                                                foreach ($field_image_alts AS $info){ if (!empty($info['token'])){
                                                    $num = (int)(str_replace('alt', '', $info['token']));
                                                    if ($alt_limit < $num){ $alt_limit = $num + 1; }
                                                    } }
                                                for ($i = 1; $i <= $alt_limit; $i++){
                                                    $alt_token = 'alt'.($i > 1 ? $i : '');
                                                    ?>
                                                    <option value="<?= $alt_token ?>"<?= in_array($alt_token, $field_image_alts_tokens) ? ' disabled="disabled"' : '' ?>>
                                                        <?= $field_data['field_name'] ?>
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

                            <? if (!$is_backup_data && !empty($field_backup_list)){ ?>
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
                                            <? foreach ($field_backup_list AS $backup_key => $backup_info){ ?>
                                                <? $backup_unix_time = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})([0-9]{2})$/', '$1/$2/$3T$4:$5', $backup_info['backup_date_time'])); ?>
                                                <tr>
                                                    <td class="id"><?= $backup_info['backup_id'] ?></td>
                                                    <td class="name"><?= $backup_info['field_name'] ?></td>
                                                    <td class="date"><?= date('Y/m/d', $backup_unix_time) ?></td>
                                                    <td class="time"><?= date('g:i a', $backup_unix_time) ?></td>
                                                    <td class="actions">
                                                        <a href="admin.php?action=edit_fields&subaction=editor&backup_id=<?= $backup_info['backup_id'] ?>" target="_blank" style="text-decoration: none;">
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

                        <div class="formfoot">

                            <? if (!$is_backup_data){ ?>
                                <div class="buttons">
                                    <input class="button save" type="submit" value="Save Changes" />
                                    <input class="button cancel" type="button" value="Reset Changes" onclick="javascript:window.location.href='admin.php?action=edit_fields&subaction=editor&field_id=<?= $field_data['field_id'] ?>';" />
                                    <? /*
                                    <input class="button delete" type="button" value="Delete Field" data-delete="fields" data-field-id="<?= $field_data['field_id'] ?>" />
                                    */ ?>
                                </div>
                            <? } ?>

                            <? /*
                            <div class="metadata">
                                <div class="date"><strong>Created</strong>: <?= !empty($field_data['field_date_created']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $field_data['field_date_created'])): '-' ?></div>
                                <div class="date"><strong>Modified</strong>: <?= !empty($field_data['field_date_modified']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $field_data['field_date_modified'])) : '-' ?></div>
                            </div>
                            */ ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/update_image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                $debug_field_data = $field_data;
                $debug_field_data['field_description2'] = str_replace(PHP_EOL, '\\n', $debug_field_data['field_description2']);
                echo('<pre style="display: none;">$field_data = '.(!empty($debug_field_data) ? htmlentities(print_r($debug_field_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

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