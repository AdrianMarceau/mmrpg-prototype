<? ob_start(); ?>

    <?

    /* -- Collect Dependant Indexes -- */

    // Collect an index of type colours for options
    $mmrpg_types_fields = rpg_type::get_index_fields(true);
    $mmrpg_types_index = $db->get_array_list("SELECT {$mmrpg_types_fields} FROM mmrpg_index_types ORDER BY type_order ASC", 'type_token');

    // Collect an index of battle fields for options
    $mmrpg_fields_fields = rpg_field::get_index_fields(true);
    $mmrpg_fields_index = $db->get_array_list("SELECT {$mmrpg_fields_fields} FROM mmrpg_index_fields WHERE field_token <> 'field' AND field_flag_published = 1  AND field_flag_complete = 1 ORDER BY field_order ASC", 'field_token');

    // Collect an index of player colours for options
    $mmrpg_players_fields = rpg_player::get_index_fields(true);
    $mmrpg_players_index = $db->get_array_list("SELECT {$mmrpg_players_fields} FROM mmrpg_index_players WHERE player_token <> 'player' AND player_flag_published = 1 AND player_flag_complete = 1  ORDER BY player_order ASC", 'player_token');

    // Collect an index of robot colours for options
    $mmrpg_robots_fields = rpg_robot::get_index_fields(true);
    $mmrpg_robots_index = $db->get_array_list("SELECT {$mmrpg_robots_fields} FROM mmrpg_index_robots WHERE robot_token <> 'robot' AND robot_class <> 'boss' AND robot_flag_published = 1 AND robot_flag_complete = 1 ORDER BY FIELD(robot_class, 'master', 'mecha', 'boss'), robot_name ASC", 'robot_token');

    // Collect an index of challenge colours for options
    $mmrpg_abilities_fields = rpg_ability::get_index_fields(true);
    $mmrpg_abilities_index = $db->get_array_list("SELECT {$mmrpg_abilities_fields} FROM mmrpg_index_abilities WHERE ability_token <> 'ability' AND ability_class <> 'system' AND ability_flag_published = 1 AND ability_flag_complete = 1 ORDER BY FIELD(ability_class, 'master', 'mecha', 'boss'), ability_name ASC, ability_order ASC", 'ability_token');

    // Collect an index of challenge colours for options
    $mmrpg_items_fields = rpg_item::get_index_fields(true);
    $mmrpg_items_index = $db->get_array_list("SELECT {$mmrpg_items_fields} FROM mmrpg_index_items WHERE item_token <> 'item' AND item_class <> 'system' AND (item_subclass = 'consumable' OR item_subclass = 'holdable') AND item_flag_published = 1 AND item_flag_complete = 1 ORDER BY item_order ASC", 'item_token');

    // Collect an index of contributors and admins that have made challenges
    $mmrpg_contributors_index = $db->get_array_list("SELECT
        users.user_id AS user_id,
        users.user_name AS user_name,
        users.user_name_public AS user_name_public,
        users.user_name_clean AS user_name_clean,
        (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS user_name_display,
        users.user_colour_token AS user_colour_token,
        uroles.role_level AS user_role_level,
        (CASE WHEN editors.challenges_created_count IS NOT NULL THEN editors.challenges_created_count ELSE 0 END) AS user_challenge_count
        FROM
        mmrpg_users AS users
        LEFT JOIN mmrpg_roles AS uroles ON uroles.role_id = users.role_id
        LEFT JOIN (SELECT
                challenge_creator AS challenge_user_id,
                COUNT(challenge_creator) AS challenges_created_count
                FROM mmrpg_challenges
                GROUP BY challenge_creator) AS editors ON editors.challenge_user_id = users.user_id
        WHERE
        users.user_id <> 0
        AND (uroles.role_level > 3
            OR users.user_credit_line <> ''
            OR users.user_credit_text <> ''
            OR editors.challenges_created_count IS NOT NULL)
        ORDER BY
        users.user_name_clean ASC,
        uroles.role_level DESC
        ;", 'user_id');


    /* -- Generate Select Option Markup -- */

    // Pre-generate a list of all robots so we can re-use it over and over
    $robot_options_count = 0;
    $robot_options_group = '';
    $robot_options_markup = array();
    $robot_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_robots_index AS $robot_token => $robot_info){
        if ($robot_info['robot_class'] != $robot_options_group){
            if (!empty($robot_options_group)){ $robot_options_markup[] = '</optgroup>'; }
            $robot_options_group = $robot_info['robot_class'];
            $robot_options_markup[] = '<optgroup label="'.ucfirst($robot_info['robot_class']).' Robots">';
        }
        $robot_name = $robot_info['robot_name'];
        $robot_types = ucwords(implode(' / ', array_values(array_filter(array($robot_info['robot_core'], $robot_info['robot_core2'])))));
        if (empty($robot_types)){ $robot_types = 'Neutral'; }
        $robot_options_markup[] = '<option value="'.$robot_token.'">'.$robot_name.' ('.$robot_types.')</option>';
        $robot_options_count++;
    }
    if (!empty($robot_options_group)){ $robot_options_markup[] = '</optgroup>'; }
    $robot_options_markup = implode(PHP_EOL, $robot_options_markup);

    // Pre-generate a list of all abilities so we can re-use it over and over
    $ability_options_count = 0;
    $ability_options_group = '';
    $ability_options_markup = array();
    $ability_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_abilities_index AS $ability_token => $ability_info){
        if ($ability_info['ability_class'] != $ability_options_group){
            if (!empty($ability_options_group)){ $ability_options_markup[] = '</optgroup>'; }
            $ability_options_group = $ability_info['ability_class'];
            $ability_options_markup[] = '<optgroup label="'.ucfirst($ability_info['ability_class']).' Abilities">';
        }
        $ability_name = $ability_info['ability_name'];
        $ability_types = ucwords(implode(' / ', array_values(array_filter(array($ability_info['ability_type'], $ability_info['ability_type2'])))));
        if (empty($ability_types)){ $ability_types = 'Neutral'; }
        $ability_options_markup[] = '<option value="'.$ability_token.'">'.$ability_name.' ('.$ability_types.')</option>';
        $ability_options_count++;
    }
    if (!empty($ability_options_group)){ $ability_options_markup[] = '</optgroup>'; }
    $ability_options_markup = implode(PHP_EOL, $ability_options_markup);

    // Pre-generate a list of all items so we can re-use it over and over
    $item_options_markup = array();
    $item_options_markup[] = '<option value="">-</option>';
    foreach ($mmrpg_items_index AS $item_token => $item_info){
        $item_name = $item_info['item_name'];
        $item_options_markup[] = '<option value="'.$item_token.'">'.$item_name.'</option>';
    }
    $item_options_count = count($item_options_markup);
    $item_options_markup = implode(PHP_EOL, $item_options_markup);

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


    /* -- Form Setup Actions -- */

    // Define a function for exiting a challenge edit action
    function exit_challenge_edit_action($challenge_id = 0){
        if (!empty($challenge_id)){ $location = 'admin.php?action=edit_challenges&subaction=editor&challenge_id='.$challenge_id; }
        else { $location = 'admin.php?action=edit_challenges&subaction=search'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Challenges | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if (false && $sub_action == 'delete' && !empty($_GET['challenge_id'])){

        // Collect form data for processing
        $delete_data['challenge_id'] = !empty($_GET['challenge_id']) && is_numeric($_GET['challenge_id']) ? trim($_GET['challenge_id']) : '';

        // Let's delete all of this challenge's data from the database
        $db->delete('mmrpg_challenges', array('challenge_id' => $delete_data['challenge_id']));
        $form_messages[] = array('success', 'The requested challenge has been deleted from the database');
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
        $sort_data = array('name' => 'challenge_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['challenge_id'] = !empty($_GET['challenge_id']) && is_numeric($_GET['challenge_id']) ? trim($_GET['challenge_id']) : '';
        $search_data['challenge_name'] = !empty($_GET['challenge_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['challenge_name']) ? trim(strtolower($_GET['challenge_name'])) : '';
        $search_data['challenge_content'] = !empty($_GET['challenge_content']) && preg_match('/[-_0-9a-z\.\*\s\{\}]+/i', $_GET['challenge_content']) ? trim($_GET['challenge_content']) : '';
        $search_data['challenge_kind'] = !empty($_GET['challenge_kind']) && preg_match('/[-_0-9a-z]+/i', $_GET['challenge_kind']) ? trim(strtolower($_GET['challenge_kind'])) : '';
        $search_data['challenge_creator'] = !empty($_GET['challenge_creator']) && is_numeric($_GET['challenge_creator']) ? (int)($_GET['challenge_creator']) : '';
        $search_data['challenge_flag_hidden'] = isset($_GET['challenge_flag_hidden']) && $_GET['challenge_flag_hidden'] !== '' ? (!empty($_GET['challenge_flag_hidden']) ? 1 : 0) : '';
        $search_data['challenge_flag_published'] = isset($_GET['challenge_flag_published']) && $_GET['challenge_flag_published'] !== '' ? (!empty($_GET['challenge_flag_published']) ? 1 : 0) : '';

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_challenge_fields = rpg_mission_challenge::get_index_fields(true, 'challenges');
        $search_query = "SELECT
            {$temp_challenge_fields}
            FROM mmrpg_challenges AS challenges
            WHERE 1=1
            ";

        // If the challenge ID was provided, we can search by exact match
        if (!empty($search_data['challenge_id'])){
            $challenge_id = $search_data['challenge_id'];
            $search_query .= "AND challenge_id = {$challenge_id} ";
            $search_results_limit = false;
        }

        // Else if the challenge name was provided, we can use wildcards
        if (!empty($search_data['challenge_name'])){
            $challenge_name = $search_data['challenge_name'];
            $challenge_name = str_replace(array(' ', '*', '%'), '%', $challenge_name);
            $challenge_name = preg_replace('/%+/', '%', $challenge_name);
            $challenge_name = '%'.$challenge_name.'%';
            $search_query .= "AND challenge_name LIKE '{$challenge_name}' ";
            $search_results_limit = false;
        }

        // Else if the challenge content was provided, we can use wildcards
        if (!empty($search_data['challenge_content'])){
            $challenge_content = $search_data['challenge_content'];
            $challenge_content = str_replace(array(' ', '*', '%'), '%', $challenge_content);
            $challenge_content = preg_replace('/%+/', '%', $challenge_content);
            $challenge_content = '%'.$challenge_content.'%';
            $search_query .= "AND (
                challenge_name LIKE '{$challenge_content}'
                OR challenge_description LIKE '{$challenge_content}'
                OR challenge_field_data LIKE '{$challenge_content}'
                OR challenge_target_data LIKE '{$challenge_content}'
                OR challenge_reward_data LIKE '{$challenge_content}'
                ) ";
            $search_results_limit = false;
        }

        // If the challenge kind was provided
        if (!empty($search_data['challenge_kind'])){
            $search_query .= "AND challenge_kind = '{$search_data['challenge_kind']}' ";
            $search_results_limit = false;
        }

        // If the challenge creator ID was provided, we can search by exact match
        if ($search_data['challenge_creator'] !== ''){
            $challenge_id = $search_data['challenge_id'];
            $search_query .= "AND challenge_creator = {$search_data['challenge_creator']} ";
            $search_results_limit = false;
        }

        // If the challenge hidden flag was provided
        if ($search_data['challenge_flag_hidden'] !== ''){
            $search_query .= "AND challenge_flag_hidden = {$search_data['challenge_flag_hidden']} ";
            $search_results_limit = false;
        }

        // If the challenge published flag was provided
        if ($search_data['challenge_flag_published'] !== ''){
            $search_query .= "AND challenge_flag_published = {$search_data['challenge_flag_published']} ";
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "FIELD(challenge_kind, 'event', 'user')";
        $order_by[] = "challenge_creator ASC";
        $order_by[] = "challenge_name ASC";
        $order_by[] = "challenge_id ASC";
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
        $search_results_total = $db->get_value("SELECT COUNT(challenge_id) AS total FROM mmrpg_challenges WHERE 1=1;", 'total');

    }

    // If we're in editor mode, we should collect challenge info from database
    $challenge_data = array();
    $editor_data = array();
    if ($sub_action == 'editor'
        && !empty($_GET['challenge_id'])
        ){

        // Collect form data for processing
        $editor_data['challenge_id'] = !empty($_GET['challenge_id']) && is_numeric($_GET['challenge_id']) ? trim($_GET['challenge_id']) : '';

        /* -- Collect Challenge Data -- */

        // Collect challenge details from the database
        $temp_challenge_fields = rpg_mission_challenge::get_index_fields(true);
        $challenge_data = $db->get_array("SELECT {$temp_challenge_fields} FROM mmrpg_challenges WHERE challenge_id = {$editor_data['challenge_id']};");

        // If challenge data could not be found, produce error and exit
        if (empty($challenge_data)){ exit_challenge_edit_action(); }

        // Collect the challenge's name(s) for display
        $challenge_name_display = $challenge_data['challenge_name'];
        $this_page_tabtitle = $challenge_name_display.' | '.$this_page_tabtitle;

        // If form data has been submit for this challenge, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit_challenges'){

            // COLLECT form data from the request and parse out simple rules

            $form_data['challenge_id'] = !empty($_POST['challenge_id']) && is_numeric($_POST['challenge_id']) ? (int)(trim($_POST['challenge_id'])) : 0;
            $form_data['challenge_kind'] = !empty($_POST['challenge_kind']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['challenge_kind']) ? trim(strtolower($_POST['challenge_kind'])) : '';
            $form_data['challenge_creator'] = !empty($_POST['challenge_creator']) && is_numeric($_POST['challenge_creator']) ? (int)(trim($_POST['challenge_creator'])) : 0;
            $form_data['challenge_name'] = !empty($_POST['challenge_name']) && !is_numeric($_POST['challenge_name']) && strlen($_POST['challenge_name']) >= 2 ? strip_tags(trim($_POST['challenge_name'])) : '';
            $form_data['challenge_description'] = !empty($_POST['challenge_description']) ? preg_replace('/\s+/', ' ', trim(strip_tags($_POST['challenge_description']))) : '';
            $form_data['challenge_robot_limit'] = !empty($_POST['challenge_robot_limit']) && is_numeric($_POST['challenge_robot_limit']) ? (int)(trim($_POST['challenge_robot_limit'])) : 0;
            $form_data['challenge_turn_limit'] = !empty($_POST['challenge_turn_limit']) && is_numeric($_POST['challenge_turn_limit']) ? (int)(trim($_POST['challenge_turn_limit'])) : 0;

            $form_data['challenge_field_data'] = !empty($_POST['challenge_field_data']) && is_array($_POST['challenge_field_data']) ? $_POST['challenge_field_data'] : array();
            $form_data['challenge_target_data'] = !empty($_POST['challenge_target_data']) && is_array($_POST['challenge_target_data']) ? $_POST['challenge_target_data'] : array();
            if (isset($form_data['challenge_target_data']['player_robots'])){
                $form_data['challenge_target_data']['player_robots'] = array_filter($form_data['challenge_target_data']['player_robots'], function($arr){ return !empty($arr['robot_token']) ? true : false; });
                $form_data['challenge_target_data']['player_robots'] = array_values($form_data['challenge_target_data']['player_robots']);
            }

            $form_data['challenge_flag_published'] = isset($_POST['challenge_flag_published']) && is_numeric($_POST['challenge_flag_published']) ? (int)(trim($_POST['challenge_flag_published'])) : 0;
            $form_data['challenge_flag_hidden'] = isset($_POST['challenge_flag_hidden']) && is_numeric($_POST['challenge_flag_hidden']) ? (int)(trim($_POST['challenge_flag_hidden'])) : 0;

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (empty($form_data['challenge_id'])){ $form_messages[] = array('error', 'Challenge ID was not provided'); $form_success = false; }
            if (empty($form_data['challenge_kind'])){ $form_messages[] = array('error', 'Challenge Kind was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['challenge_name'])){ $form_messages[] = array('error', 'Challenge Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['challenge_field_data']['field_background'])){ $form_messages[] = array('error', 'Field Background was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['challenge_field_data']['field_foreground'])){ $form_messages[] = array('error', 'Field Foreground was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['challenge_field_data']['field_music'])){ $form_messages[] = array('error', 'Field Music was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['challenge_target_data']['player_token'])){ $form_messages[] = array('error', 'Target Player was not provided or was invalid'); $form_success = false; }
            //if (empty($form_data['challenge_target_data']['player_robots'])){ $form_messages[] = array('error', 'Target Robot array was not provided or were invalid'); $form_success = false; }
            if (!$form_success){ exit_challenge_edit_action($form_data['challenge_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            //if (empty($form_data['challenge_description'])){ $form_messages[] = array('warning', 'Challenge Description was not provided and may cause issues on the front-end'); }

            // PREVENT publishing if required fields are not filled out
            if ($form_data['challenge_flag_published']){
                //if (empty($form_data['challenge_description'])){ $form_messages[] = array('warning', 'Challenge cannot be published without a description'); $form_data['challenge_flag_published'] = 0; }
                if (empty($form_data['challenge_target_data']['player_robots'])){ $form_messages[] = array('warning', 'Challenge cannot be published without target robots'); $form_data['challenge_flag_published'] = 0; }
            }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            // Parse any field hazards that have been applied
            $field_hazards = array();
            if (!empty($form_data['challenge_field_data']['values']['hazards'])){
                foreach ($form_data['challenge_field_data']['values']['hazards'] AS $hazard_token => $hazard_value){
                    if (empty($hazard_value)){ continue; }
                    $field_hazards[$hazard_token] = $hazard_value;
                }
            }
            if (!empty($field_hazards)){ $form_data['challenge_field_data']['values']['hazards'] = $field_hazards; }
            else { unset($form_data['challenge_field_data']['values']['hazards']); }

            if (empty($form_data['challenge_field_data']['values'])){ unset($form_data['challenge_field_data']['values']); }

            // Check player robots and remove incompatible abilities
            if (!empty($form_data['challenge_target_data']['player_robots'])){
                $target_player_robots = $form_data['challenge_target_data']['player_robots'];
                foreach ($target_player_robots AS $key => $robot){
                    if (empty($robot['robot_token'])){
                        unset($target_player_robots[$key]);
                        continue;
                    } else {
                        $rtoken = $robot['robot_token'];
                        $ritem = !empty($robot['robot_item']) ? $robot['robot_item'] : '';
                        if (empty($robot['robot_item'])){ unset($robot['robot_item']); }
                        if (empty($robot['robot_image'])){ unset($robot['robot_image']); }
                        else { $robot['robot_image'] = $rtoken.'_'.$robot['robot_image']; }
                        $robot['robot_abilities'] = array_unique(array_filter($robot['robot_abilities']));
                        foreach ($robot['robot_abilities'] AS $key2 => $atoken){
                            if (!rpg_robot::has_ability_compatibility($rtoken, $atoken, $ritem)){
                                unset($robot['robot_abilities'][$key2]);
                                continue;
                            }
                        }
                        $robot['robot_abilities'] = array_values($robot['robot_abilities']);
                        if (empty($robot['robot_abilities'])){ $robot['robot_abilities'] = array('buster-shot'); }
                        $target_player_robots[$key] = $robot;
                    }
                }
                $form_data['challenge_target_data']['player_robots'] = $target_player_robots;
            }

            if (isset($form_data['challenge_field_data'])){ $form_data['challenge_field_data'] = !empty($form_data['challenge_field_data']) ? json_encode($form_data['challenge_field_data']) : ''; }
            if (isset($form_data['challenge_target_data'])){ $form_data['challenge_target_data'] = !empty($form_data['challenge_target_data']) ? json_encode($form_data['challenge_target_data']) : ''; }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // Make a copy of the update data sans the challenge ID
            $update_data = $form_data;
            unset($update_data['challenge_id']);

            // Update the main database index with changes to this challenge's data
            $update_results = $db->update('mmrpg_challenges', $update_data, array('challenge_id' => $form_data['challenge_id']));

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If we made it this far, the update must have been a success
            if ($update_results !== false){ $form_success = true; $form_messages[] = array('success', 'Challenge data was updated successfully!'); }
            else { $form_success = false; $form_messages[] = array('error', 'Challenge data could not be updated...'); }

            // Update cache timestamp if changes were successful
            if ($form_success){
                list($date, $time) = explode('-', date('Ymd-Hi'));
                $db->update('mmrpg_config', array('config_value' => $date), "config_group = 'global' AND config_name = 'cache_date'");
                $db->update('mmrpg_config', array('config_value' => $time), "config_group = 'global' AND config_name = 'cache_time'");
            }

            // We're done processing the form, we can exit
            exit_challenge_edit_action($form_data['challenge_id']);

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }

    ?>

    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=edit_challenges">Edit Challenges</a>
        <? if ($sub_action == 'editor' && !empty($challenge_data)): ?>
            &raquo; <a href="admin.php?action=edit_challenges&amp;subaction=editor&amp;challenge_id=<?= $challenge_data['challenge_id'] ?>"><?= $challenge_name_display ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit_challenges">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Challenges</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="action" value="edit_challenges" />
                    <input type="hidden" name="subaction" value="search" />

                    <div class="field">
                        <strong class="label">By ID</strong>
                        <input class="textbox" type="text" name="challenge_id" value="<?= !empty($search_data['challenge_id']) ? $search_data['challenge_id'] : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="challenge_name" placeholder="" value="<?= !empty($search_data['challenge_name']) ? htmlentities($search_data['challenge_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Content</strong>
                        <input class="textbox" type="text" name="challenge_content" placeholder="" value="<?= !empty($search_data['challenge_content']) ? htmlentities($search_data['challenge_content'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Kind</strong>
                        <select class="select" name="challenge_kind">
                            <option value=""></option>
                            <option value="event"<?= !empty($search_data['challenge_kind']) && $search_data['challenge_kind'] === 'event' ? ' selected="selected"' : '' ?>>Event Challenge</option>
                            <option value="user"<?= !empty($search_data['challenge_kind']) && $search_data['challenge_kind'] === 'user' ? ' selected="selected"' : '' ?>>User Challenge</option>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Creator</strong>
                        <select class="select" name="challenge_creator"><option value=""></option><?
                            foreach ($mmrpg_contributors_index AS $user_id => $user_info){
                                $option_label = $user_info['user_name'];
                                if (!empty($user_info['user_name_public']) && $user_info['user_name_public'] !== $user_info['user_name']){ $option_label = $user_info['user_name_public'].' ('.$option_label.')'; }
                                ?><option value="<?= $user_id ?>"<?= !empty($search_data['challenge_creator']) && $search_data['challenge_creator'] === $user_id ? ' selected="selected"' : '' ?>><?= $option_label ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field has2cols flags">
                    <?
                    $flag_names = array(
                        'published' => array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished'),
                        'hidden' => array('icon' => 'fas fa-eye-slash', 'yes' => 'Hidden', 'no' => 'Visible')
                        );
                    foreach ($flag_names AS $flag_token => $flag_info){
                        $flag_name = 'challenge_flag_'.$flag_token;
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
                        <input class="button" type="reset" value="Reset" onclick="javascript:window.location.href='admin.php?action=edit_challenges';" />
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
                            <col class="kind" width="85" />
                            <col class="creator" width="180" />
                            <col class="date created" width="100" />
                            <col class="date modified" width="100" />
                            <col class="flag published" width="80" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="90" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('challenge_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('challenge_name', 'Name') ?></th>
                                <th class="kind"><?= cms_admin::get_sort_link('challenge_kind', 'Kind') ?></th>
                                <th class="creator"><?= cms_admin::get_sort_link('challenge_creator', 'Creator') ?></th>
                                <th class="date created"><?= cms_admin::get_sort_link('challenge_date_created', 'Created') ?></th>
                                <th class="date modified"><?= cms_admin::get_sort_link('challenge_date_modified', 'Modified') ?></th>
                                <th class="flag published"><?= cms_admin::get_sort_link('challenge_flag_published', 'Published') ?></th>
                                <th class="flag hidden"><?= cms_admin::get_sort_link('challenge_flag_hidden', 'Hidden') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head count" colspan="9"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot count" colspan="9"><?= cms_admin::get_totals_markup() ?></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            $temp_class_colours = array(
                                'user' => array('defense', '<i class="fas fa-robot"></i>'),
                                'event' => array('attack', '<i class="fas fa-star"></i>')
                                );
                            foreach ($search_results AS $key => $challenge_data){

                                $challenge_id = $challenge_data['challenge_id'];
                                $challenge_name = $challenge_data['challenge_name'];
                                $challenge_kind = ucfirst($challenge_data['challenge_kind']);
                                $challenge_kind_span = '<span class="type_span type_'.$temp_class_colours[$challenge_data['challenge_kind']][0].'">'.$temp_class_colours[$challenge_data['challenge_kind']][1].' '.$challenge_kind.'</span>';
                                $challenge_creator = !empty($challenge_data['challenge_creator']) ? $mmrpg_contributors_index[$challenge_data['challenge_creator']] : false;
                                $challenge_creator_name = !empty($challenge_creator['user_name_display']) ? $challenge_creator['user_name_display'] : false;
                                $challenge_creator_type = !empty($challenge_creator['user_colour_token']) ? $challenge_creator['user_colour_token'] : 'none';
                                $challenge_creator_span = !empty($challenge_creator_name) ? '<span class="type_span type_'.$challenge_creator_type.'">'.$challenge_creator_name.'</span>' : '-';
                                $challenge_date_created = !empty($challenge_data['challenge_date_created']) ? date('Y-m-d', $challenge_data['challenge_date_created']) : '-';
                                $challenge_date_modified = !empty($challenge_data['challenge_date_modified']) ? date('Y-m-d', $challenge_data['challenge_date_modified']) : '-';
                                $challenge_flag_published = !empty($challenge_data['challenge_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $challenge_flag_hidden = !empty($challenge_data['challenge_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $challenge_edit_url = 'admin.php?action=edit_challenges&subaction=editor&challenge_id='.$challenge_id;
                                $challenge_name_link = '<a class="link" href="'.$challenge_edit_url.'">'.$challenge_name.'</a>';

                                $challenge_actions = '';
                                $challenge_actions .= '<a class="link edit" href="'.$challenge_edit_url.'"><span>edit</span></a>';
                                $challenge_actions .= '<span class="link delete disabled"><span>delete</span></span>';
                                //$challenge_actions .= '<a class="link delete" data-delete="challenges" data-challenge-id="'.$challenge_id.'"><span>delete</span></a>';

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$challenge_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$challenge_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="kind"><div class="wrap">'.$challenge_kind_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="creator"><div class="wrap">'.$challenge_creator_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="date created"><div>'.$challenge_date_created.'</div></td>'.PHP_EOL;
                                    echo '<td class="date modified"><div>'.$challenge_date_modified.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$challenge_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag hidden"><div>'.$challenge_flag_hidden.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$challenge_actions.'</div></td>'.PHP_EOL;
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
            && !empty($_GET['challenge_id'])
            ){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($challenge_data['challenge_core']) ? $challenge_data['challenge_core'].(!empty($challenge_data['challenge_core2']) ? '_'.$challenge_data['challenge_core2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="challenge_core,challenge_core2">
                        <span class="title">Edit Challenge &quot;<?= $challenge_name_display ?>&quot;</span>
                    </h3>

                    <? print_form_messages() ?>

                    <div class="editor-tabs" data-tabgroup="challenge">
                        <a class="tab active" data-tab="main">Main</a><span></span>
                        <a class="tab" data-tab="field">Field</a><span></span>
                        <a class="tab" data-tab="robots">Robots</a><span></span>
                    </div>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit_challenges" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="challenge">

                            <div class="panel active" data-tab="main">

                                <div class="field">
                                    <strong class="label">Challenge ID</strong>
                                    <input type="hidden" name="challenge_id" value="<?= $challenge_data['challenge_id'] ?>" />
                                    <input class="textbox" type="text" name="challenge_id" value="<?= $challenge_data['challenge_id'] ?>" disabled="disabled" />
                                </div>

                                <div class="field">
                                    <strong class="label">Challenge Kind</strong>
                                    <select class="select" name="challenge_kind">
                                        <option value="event" <?= $challenge_data['challenge_kind'] == 'event' ? 'selected="selected"' : '' ?>>Event Challenge</option>
                                        <option value="user" <?= empty($challenge_data['challenge_kind']) || $challenge_data['challenge_kind'] == 'user' ? 'selected="selected"' : '' ?>>User Challenge</option>
                                    </select><span></span>
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Challenge Creator</strong>
                                        <em>leave blank for events</em>
                                    </div>
                                    <? if ($challenge_data['challenge_kind'] == 'user'){ ?>
                                        <select class="select" name="challenge_creator">
                                            <?= str_replace('value="'.$challenge_data['challenge_creator'].'"', 'value="'.$challenge_data['challenge_creator'].'" selected="selected"', $contributor_options_markup) ?>
                                        </select><span></span>
                                    <? } else { ?>
                                        <input type="hidden" name="challenge_creator" value="<?= $challenge_data['challenge_creator'] ?>" />
                                        <input class="textbox" type="text" name="challenge_creator" value="-" disabled="disabled" />
                                    <? } ?>
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Challenge Name</strong>
                                        <em>appears on the button</em>
                                    </div>
                                    <input class="textbox" type="text" name="challenge_name" value="<?= $challenge_data['challenge_name'] ?>" maxlength="64" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Robot Limit</strong>
                                        <em>use zero for auto</em>
                                    </div>
                                    <input class="textbox" type="number" name="challenge_robot_limit" value="<?= $challenge_data['challenge_robot_limit'] ?>" min="0" max="8" />
                                </div>

                                <div class="field">
                                    <div class="label">
                                        <strong>Turn Limit</strong>
                                        <em>use zero for auto</em>
                                    </div>
                                    <input class="textbox" type="number" name="challenge_turn_limit" value="<?= $challenge_data['challenge_turn_limit'] ?>" min="0" max="99" />
                                </div>

                                <div class="field fullsize">
                                    <div class="label">
                                        <strong>Challenge Description</strong>
                                        <em>appears at battle start, leave blank for auto-generated</em>
                                    </div>
                                    <textarea class="textarea" name="challenge_description" maxlength="256" rows="3"><?= htmlentities($challenge_data['challenge_description'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                </div>

                            </div>

                            <div class="panel active" data-tab="field">

                                <?
                                // Decode the field data so we can work with it
                                $challenge_field_data = !empty($challenge_data['challenge_field_data']) ? json_decode($challenge_data['challenge_field_data'], true) : array();
                                ?>

                                <div class="field">
                                    <strong class="label">Field Background</strong>
                                    <select class="select" name="challenge_field_data[field_background]">
                                        <?
                                        //echo('<option value=""'.(empty($challenge_field_data['field_background']) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                            $label = $field_data['field_name'];
                                            $label .= ' ('.(!empty($field_data['field_type']) ? ucfirst($field_data['field_type']) : 'Neutral').')';
                                            $selected = !empty($challenge_field_data['field_background']) && $challenge_field_data['field_background'] == $field_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        ?>
                                    </select><span></span>
                                </div>

                                <div class="field">
                                    <strong class="label">Field Foreground</strong>
                                    <select class="select" name="challenge_field_data[field_foreground]">
                                        <?
                                        //echo('<option value=""'.(empty($challenge_field_data['field_foreground']) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                            $label = $field_data['field_name'];
                                            $label .= ' ('.(!empty($field_data['field_type']) ? ucfirst($field_data['field_type']) : 'Neutral').')';
                                            $selected = !empty($challenge_field_data['field_foreground']) && $challenge_field_data['field_foreground'] == $field_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        ?>
                                    </select><span></span>
                                </div>

                                <div class="field">
                                    <strong class="label">Field Music</strong>
                                    <select class="select" name="challenge_field_data[field_music]">
                                        <?
                                        //echo('<option value=""'.(empty($challenge_field_data['field_music']) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                            $label = $field_data['field_name'];
                                            $label .= ' ('.(!empty($field_data['field_type']) ? ucfirst($field_data['field_type']) : 'Neutral').')';
                                            $selected = !empty($challenge_field_data['field_music']) && $challenge_field_data['field_music'] == $field_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        ?>
                                    </select><span></span>
                                </div>

                                <hr />

                                <div class="field fullsize has4cols multirow" style="min-height: 0;">
                                    <strong class="label">Field Hazards</strong>
                                    <? $challenge_field_hazards = !empty($challenge_field_data['values']['hazards']) ? $challenge_field_data['values']['hazards'] : array(); ?>
                                </div>
                                <div class="field fullsize has4cols multirow">
                                    <div class="subfield">
                                        <strong class="label sublabel">Crude Oil <em>via Oil Shooter</em></strong>
                                        <select class="select" name="challenge_field_data[values][hazards][crude_oil]">
                                            <option value=""<?= empty($challenge_field_hazards['crude_oil']) ? ' selected="selected"' : '' ?>>-</option>
                                            <option value="both"<?= !empty($challenge_field_hazards['crude_oil']) && $challenge_field_hazards['crude_oil'] == 'both' ? ' selected="selected"' : '' ?>>Both Sides</option>
                                            <option value="left"<?= !empty($challenge_field_hazards['crude_oil']) && $challenge_field_hazards['crude_oil'] == 'left' ? ' selected="selected"' : '' ?>>Player Side (Left)</option>
                                            <option value="right"<?= !empty($challenge_field_hazards['crude_oil']) && $challenge_field_hazards['crude_oil'] == 'right' ? ' selected="selected"' : '' ?>>Target Side (Right)</option>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <strong class="label sublabel">Foamy Bubbles <em>via Bubble Spray</em></strong>
                                        <select class="select" name="challenge_field_data[values][hazards][foamy_bubbles]">
                                            <option value=""<?= empty($challenge_field_hazards['foamy_bubbles']) ? ' selected="selected"' : '' ?>>-</option>
                                            <option value="both"<?= !empty($challenge_field_hazards['foamy_bubbles']) && $challenge_field_hazards['foamy_bubbles'] == 'both' ? ' selected="selected"' : '' ?>>Both Sides</option>
                                            <option value="left"<?= !empty($challenge_field_hazards['foamy_bubbles']) && $challenge_field_hazards['foamy_bubbles'] == 'left' ? ' selected="selected"' : '' ?>>Player Side (Left)</option>
                                            <option value="right"<?= !empty($challenge_field_hazards['foamy_bubbles']) && $challenge_field_hazards['foamy_bubbles'] == 'right' ? ' selected="selected"' : '' ?>>Target Side (Right)</option>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <strong class="label sublabel">Frozen Footholds <em>via Ice Breath</em></strong>
                                        <select class="select" name="challenge_field_data[values][hazards][frozen_footholds]">
                                            <option value=""<?= empty($challenge_field_hazards['frozen_footholds']) ? ' selected="selected"' : '' ?>>-</option>
                                            <option value="both"<?= !empty($challenge_field_hazards['frozen_footholds']) && $challenge_field_hazards['frozen_footholds'] == 'both' ? ' selected="selected"' : '' ?>>Both Sides</option>
                                            <option value="left"<?= !empty($challenge_field_hazards['frozen_footholds']) && $challenge_field_hazards['frozen_footholds'] == 'left' ? ' selected="selected"' : '' ?>>Player Side (Left)</option>
                                            <option value="right"<?= !empty($challenge_field_hazards['frozen_footholds']) && $challenge_field_hazards['frozen_footholds'] == 'right' ? ' selected="selected"' : '' ?>>Target Side (Right)</option>
                                        </select><span></span>
                                    </div>
                                    <div class="subfield">
                                        <strong class="label sublabel">Super Blocks <em>via Super Arm</em></strong>
                                        <select class="select" name="challenge_field_data[values][hazards][super_blocks]">
                                            <option value=""<?= empty($challenge_field_hazards['super_blocks']) ? ' selected="selected"' : '' ?>>-</option>
                                            <option value="both"<?= !empty($challenge_field_hazards['super_blocks']) && $challenge_field_hazards['super_blocks'] == 'both' ? ' selected="selected"' : '' ?>>Both Sides</option>
                                            <option value="left"<?= !empty($challenge_field_hazards['super_blocks']) && $challenge_field_hazards['super_blocks'] == 'left' ? ' selected="selected"' : '' ?>>Player Side (Left)</option>
                                            <option value="right"<?= !empty($challenge_field_hazards['super_blocks']) && $challenge_field_hazards['super_blocks'] == 'right' ? ' selected="selected"' : '' ?>>Target Side (Right)</option>
                                        </select><span></span>
                                    </div>
                                </div>
                                <div class="field fullsize has4cols multirow">
                                    <div class="subfield">
                                        <strong class="label sublabel">Black Holes <em>via Galaxy Bomb</em></strong>
                                        <select class="select" name="challenge_field_data[values][hazards][black_holes]">
                                            <option value=""<?= empty($challenge_field_hazards['black_holes']) ? ' selected="selected"' : '' ?>>-</option>
                                            <option value="both"<?= !empty($challenge_field_hazards['black_holes']) && $challenge_field_hazards['black_holes'] == 'both' ? ' selected="selected"' : '' ?>>Both Sides</option>
                                            <option value="left"<?= !empty($challenge_field_hazards['black_holes']) && $challenge_field_hazards['black_holes'] == 'left' ? ' selected="selected"' : '' ?>>Player Side (Left)</option>
                                            <option value="right"<?= !empty($challenge_field_hazards['black_holes']) && $challenge_field_hazards['black_holes'] == 'right' ? ' selected="selected"' : '' ?>>Target Side (Right)</option>
                                        </select><span></span>
                                    </div>
                                </div>

                            </div>

                            <div class="panel active" data-tab="robots">

                                <?

                                // Decode the target data so we can work with it
                                $challenge_target_data = !empty($challenge_data['challenge_target_data']) ? json_decode($challenge_data['challenge_target_data'], true) : array();

                                // Print out the player token before the robots
                                $target_player_token = !empty($challenge_target_data['player_token']) ? $challenge_target_data['player_token'] : 'player';
                                echo('<input type="hidden" name="challenge_target_data[player_token]" value="'.$target_player_token.'" />'.PHP_EOL);

                                // Loop through and generate robot target fields
                                $challenge_target_robots = !empty($challenge_target_data['player_robots']) ? $challenge_target_data['player_robots'] : array();
                                $target_robots_count = count($challenge_target_robots);
                                $target_robot_slots = $target_robots_count < 8 ? $target_robots_count + 1 : $target_robots_count;
                                for ($robot_key = 0; $robot_key < $target_robot_slots; $robot_key++){

                                    // Print horizontal rule if necessary
                                    if ($robot_key > 0){ echo('<hr />'.PHP_EOL); }

                                    // Collect the current robot data for this position
                                    $current_robot_data = !empty($challenge_target_robots[$robot_key]) ? $challenge_target_robots[$robot_key] : array();
                                    $current_robot_token = !empty($current_robot_data['robot_token']) ? $current_robot_data['robot_token'] : '';
                                    $current_robot_image = !empty($current_robot_data['robot_image']) && $current_robot_data['robot_image'] != $current_robot_token ? $current_robot_data['robot_image'] : '';
                                    $current_robot_alt = str_replace($current_robot_token.'_', '', $current_robot_image);
                                    $current_robot_item = !empty($current_robot_data['robot_item']) ? $current_robot_data['robot_item'] : '';
                                    $current_robot_abilities = !empty($current_robot_data['robot_abilities']) ? $current_robot_data['robot_abilities'] : array();

                                    ?>


                                    <div class="target_robot" data-key="<?= $robot_key ?>">
                                        <div class="field fullsize has4cols multirow">
                                            <strong class="label">
                                                Target Robot #<?= ($robot_key + 1) ?>
                                                <em>Select a robot, an optional alt and/or item, then at least one ability</em>
                                            </strong>
                                            <div class="subfield">
                                                <strong class="label sublabel">Robot</strong>
                                                <select class="select" name="challenge_target_data[player_robots][<?= $robot_key ?>][robot_token]">
                                                    <?= str_replace('value="'.$current_robot_token.'"', 'value="'.$current_robot_token.'" selected="selected"', $robot_options_markup) ?>
                                                </select><span></span>
                                            </div>
                                            <div class="subfield">
                                                <strong class="label sublabel">Alt</strong>
                                                <select class="select" name="challenge_target_data[player_robots][<?= $robot_key ?>][robot_image]">
                                                    <option value="<?= $current_robot_alt ?>" selected="selected"><?= $current_robot_alt ?></option>
                                                </select><span></span>
                                            </div>
                                            <div class="subfield">
                                                <strong class="label sublabel">Item</strong>
                                                <select class="select" name="challenge_target_data[player_robots][<?= $robot_key ?>][robot_item]">
                                                    <?= str_replace('value="'.$current_robot_item.'"', 'value="'.$current_robot_item.'" selected="selected"', $item_options_markup) ?>
                                                </select><span></span>
                                            </div>
                                        </div>
                                        <div class="field fullsize has4cols multirow" style="margin-top: -6px;">
                                            <strong class="label sublabel">Abilities</strong>
                                            <?
                                            for ($i = 0; $i < 8; $i++){
                                                $current_value = isset($current_robot_abilities[$i]) ? $current_robot_abilities[$i] : '';
                                                ?>
                                                <div class="subfield">
                                                    <select class="select" name="challenge_target_data[player_robots][<?= $robot_key ?>][robot_abilities][<?= $i ?>]">
                                                        <option value="<?= $current_value ?>" selected="selected"><?= $current_value ?></option>
                                                        <? /* = str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $ability_options_markup) */ ?>
                                                    </select><span></span>
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <?


                                }

                                ?>

                            </div>

                        </div>

                        <hr />

                        <div class="options">

                            <div class="field checkwrap">
                                <label class="label">
                                    <strong>Published</strong>
                                    <input type="hidden" name="challenge_flag_published" value="0" checked="checked" />
                                    <input class="checkbox" type="checkbox" name="challenge_flag_published" value="1" <?= !empty($challenge_data['challenge_flag_published']) ? 'checked="checked"' : '' ?> />
                                </label>
                                <p class="subtext">This challenge is ready to appear in the game</p>
                            </div>

                            <div class="field checkwrap">
                                <label class="label">
                                    <strong>Hidden</strong>
                                    <input type="hidden" name="challenge_flag_hidden" value="0" checked="checked" />
                                    <input class="checkbox" type="checkbox" name="challenge_flag_hidden" value="1" <?= !empty($challenge_data['challenge_flag_hidden']) ? 'checked="checked"' : '' ?> />
                                </label>
                                <p class="subtext">This challenge's data should stay hidden</p>
                            </div>

                        </div>

                        <hr />

                        <div class="formfoot">

                            <div class="buttons">
                                <input class="button save" type="submit" value="Save Changes" />
                                <input class="button cancel" type="button" value="Reset Changes" onclick="javascript:window.location.href='admin.php?action=edit_challenges&subaction=editor&challenge_id=<?= $challenge_data['challenge_id'] ?>';" />
                                <? /*
                                <input class="button delete" type="button" value="Delete Challenge" data-delete="challenges" data-challenge-id="<?= $challenge_data['challenge_id'] ?>" />
                                */ ?>
                            </div>

                            <? /*
                            <div class="metadata">
                                <div class="date"><strong>Created</strong>: <?= !empty($challenge_data['challenge_date_created']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $challenge_data['challenge_date_created'])): '-' ?></div>
                                <div class="date"><strong>Modified</strong>: <?= !empty($challenge_data['challenge_date_modified']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $challenge_data['challenge_date_modified'])) : '-' ?></div>
                            </div>
                            */ ?>

                        </div>

                    </form>

                    <form class="ajax" name="ajax-form" target="ajax-frame" action="admin/update_image.php" method="post" enctype="multipart/form-data"></form>
                    <iframe class="ajax" name="ajax-frame" src="about:blank"></iframe>

                </div>

                <?

                //$debug_challenge_data = $challenge_data;
                //echo('<pre style="display: block;">$challenge_data = '.(!empty($debug_challenge_data) ? htmlentities(print_r($debug_challenge_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

                ?>

                <?
                $temp_edit_markup = ob_get_clean();
                echo($temp_edit_markup).PHP_EOL;
            }

        }
        ?>

    </div>

    <?
    // Generate custom javascript for this page and put it in the buffer
    ob_start();
    ?>
        <script type="text/javascript">
            window.mmrpgRobotsIndex = <?= json_encode(rpg_robot::parse_index($mmrpg_robots_index)) ?>;
            window.mmrpgAbilitiesIndex = <?= json_encode(rpg_ability::parse_index($mmrpg_abilities_index)) ?>;
            window.mmrpgAbilitiesGlobal = <?= json_encode(rpg_ability::get_global_abilities()) ?>;
            window.mmrpgItemsIndex = <?= json_encode(rpg_item::parse_index($mmrpg_items_index)) ?>;
        </script>
    <?
    if (!isset($admin_inline_javascript)){ $admin_inline_javascript = '';}
    $admin_inline_javascript .= ob_get_clean();
    ?>

<? $this_page_markup .= ob_get_clean(); ?>