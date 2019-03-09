<? ob_start(); ?>

    <?

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

    // Collect an index of challenge colours for options
    $mmrpg_mission_challenges_fields = rpg_mission_challenge::get_index_fields(true);
    $mmrpg_mission_challenges_index = $db->get_array_list("SELECT {$mmrpg_mission_challenges_fields} FROM mmrpg_challenges WHERE 1 = 1 ORDER BY FIELD(challenge_kind, 'event', 'user'), challenge_creator ASC", 'challenge_id');

    // Collect an index of challenge colours for options
    $mmrpg_abilities_fields = rpg_ability::get_index_fields(true);
    $mmrpg_abilities_index = $db->get_array_list("SELECT {$mmrpg_abilities_fields} FROM mmrpg_index_abilities WHERE ability_token <> 'ability' AND ability_class <> 'system' ORDER BY ability_order ASC", 'ability_token');

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
        uroles.role_level DESC,
        users.user_name_clean ASC
        ;", 'user_id');

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
    if ($sub_action == 'search' && (
        !empty($_GET['challenge_id'])
        || !empty($_GET['challenge_name'])
        || !empty($_GET['challenge_content'])
        || !empty($_GET['challenge_kind'])
        || !empty($_GET['challenge_creator'])
        || (isset($_GET['challenge_flag_hidden']) && $_GET['challenge_flag_hidden'] !== '')
        || (isset($_GET['challenge_flag_published']) && $_GET['challenge_flag_published'] !== '')
        )){

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
        }

        // Else if the challenge name was provided, we can use wildcards
        if (!empty($search_data['challenge_name'])){
            $challenge_name = $search_data['challenge_name'];
            $challenge_name = str_replace(array(' ', '*', '%'), '%', $challenge_name);
            $challenge_name = preg_replace('/%+/', '%', $challenge_name);
            $challenge_name = '%'.$challenge_name.'%';
            $search_query .= "AND challenge_name LIKE '{$challenge_name}' ";
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
        }

        // If the challenge kind was provided
        if (!empty($search_data['challenge_kind'])){
            $search_query .= "AND challenge_kind = '{$search_data['challenge_kind']}' ";
        }

        // If the challenge creator ID was provided, we can search by exact match
        if ($search_data['challenge_creator'] !== ''){
            $challenge_id = $search_data['challenge_id'];
            $search_query .= "AND challenge_creator = {$search_data['challenge_creator']} ";
        }

        // If the challenge hidden flag was provided
        if ($search_data['challenge_flag_hidden'] !== ''){
            $search_query .= "AND challenge_flag_hidden = {$search_data['challenge_flag_hidden']} ";
        }

        // If the challenge published flag was provided
        if ($search_data['challenge_flag_published'] !== ''){
            $search_query .= "AND challenge_flag_published = {$search_data['challenge_flag_published']} ";
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($search_data['challenge_name'])){ $order_by[] = "challenge_name ASC"; }
        $order_by[] = "FIELD(challenge_kind, 'event', 'user')";
        $order_by[] = "challenge_level ASC";
        $order_by[] = "challenge_creator ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string};";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;


    }

    // If we're in editor mode, we should collect challenge info from database
    $challenge_data = array();
    $editor_data = array();
    $is_backup_data = false;
    if ($sub_action == 'editor'
        && (!empty($_GET['challenge_id'])
            || !empty($_GET['backup_id']))){

        // Collect form data for processing
        $editor_data['challenge_id'] = !empty($_GET['challenge_id']) && is_numeric($_GET['challenge_id']) ? trim($_GET['challenge_id']) : '';
        if (empty($editor_data['challenge_id'])
            && !empty($_GET['backup_id'])
            && is_numeric($_GET['backup_id'])){
            $editor_data['backup_id'] = trim($_GET['backup_id']);
            $is_backup_data = true;
        }


        /* -- Collect Challenge Data -- */

        // Collect challenge details from the database
        $temp_challenge_fields = rpg_mission_challenge::get_index_fields(true);
        if (!$is_backup_data){
            $challenge_data = $db->get_array("SELECT {$temp_challenge_fields} FROM mmrpg_challenges WHERE challenge_id = {$editor_data['challenge_id']};");
        } else {
            $temp_challenge_backup_fields = str_replace('challenge_id,', 'backup_id AS challenge_id,', $temp_challenge_fields);
            $temp_challenge_backup_fields .= ', backup_date_time';
            $challenge_data = $db->get_array("SELECT {$temp_challenge_backup_fields} FROM mmrpg_challenges_backups WHERE backup_id = {$editor_data['backup_id']};");
        }

        // If challenge data could not be found, produce error and exit
        if (empty($challenge_data)){ exit_challenge_edit_action(); }

        // Collect the challenge's name(s) for display
        $challenge_name_display = $challenge_data['challenge_name'];
        $this_page_tabtitle = $challenge_name_display.' | '.$this_page_tabtitle;
        if ($is_backup_data){ $this_page_tabtitle = str_replace('Edit Challenges', 'View Backups', $this_page_tabtitle); }

        // If form data has been submit for this challenge, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit_challenges'){

            // COLLECT form data from the request and parse out simple rules

            $form_data['challenge_id'] = !empty($_POST['challenge_id']) && is_numeric($_POST['challenge_id']) ? trim($_POST['challenge_id']) : 0;
            $form_data['challenge_name'] = !empty($_POST['challenge_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['challenge_name']) ? trim($_POST['challenge_name']) : '';
            $form_data['challenge_kind'] = !empty($_POST['challenge_kind']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['challenge_kind']) ? trim(strtolower($_POST['challenge_kind'])) : '';
            $form_data['challenge_core'] = !empty($_POST['challenge_core']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['challenge_core']) ? trim(strtolower($_POST['challenge_core'])) : '';
            $form_data['challenge_core2'] = !empty($_POST['challenge_core2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['challenge_core2']) ? trim(strtolower($_POST['challenge_core2'])) : '';
            $form_data['challenge_gender'] = !empty($_POST['challenge_gender']) && preg_match('/^(male|female|other|none)$/', $_POST['challenge_gender']) ? trim(strtolower($_POST['challenge_gender'])) : '';

            $form_data['challenge_game'] = !empty($_POST['challenge_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['challenge_game']) ? trim($_POST['challenge_game']) : '';
            $form_data['challenge_group'] = !empty($_POST['challenge_group']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['challenge_group']) ? trim($_POST['challenge_group']) : '';
            $form_data['challenge_number'] = !empty($_POST['challenge_number']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['challenge_number']) ? trim($_POST['challenge_number']) : '';
            $form_data['challenge_order'] = !empty($_POST['challenge_order']) && is_numeric($_POST['challenge_order']) ? (int)(trim($_POST['challenge_order'])) : 0;

            $form_data['challenge_field'] = !empty($_POST['challenge_field']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['challenge_field']) ? trim(strtolower($_POST['challenge_field'])) : '';
            $form_data['challenge_field2'] = !empty($_POST['challenge_field2']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['challenge_field2']) ? trim(strtolower($_POST['challenge_field2'])) : '';
            //$form_data['challenge_mecha'] = !empty($_POST['challenge_mecha']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['challenge_mecha']) ? trim(strtolower($_POST['challenge_mecha'])) : '';

            $form_data['challenge_energy'] = !empty($_POST['challenge_energy']) && is_numeric($_POST['challenge_energy']) ? (int)(trim($_POST['challenge_energy'])) : 0;
            $form_data['challenge_weapons'] = !empty($_POST['challenge_weapons']) && is_numeric($_POST['challenge_weapons']) ? (int)(trim($_POST['challenge_weapons'])) : 0;
            $form_data['challenge_attack'] = !empty($_POST['challenge_attack']) && is_numeric($_POST['challenge_attack']) ? (int)(trim($_POST['challenge_attack'])) : 0;
            $form_data['challenge_defense'] = !empty($_POST['challenge_defense']) && is_numeric($_POST['challenge_defense']) ? (int)(trim($_POST['challenge_defense'])) : 0;
            $form_data['challenge_speed'] = !empty($_POST['challenge_speed']) && is_numeric($_POST['challenge_speed']) ? (int)(trim($_POST['challenge_speed'])) : 0;

            $form_data['challenge_weaknesses'] = !empty($_POST['challenge_weaknesses']) && is_array($_POST['challenge_weaknesses']) ? array_values(array_unique(array_filter($_POST['challenge_weaknesses']))) : array();
            $form_data['challenge_resistances'] = !empty($_POST['challenge_resistances']) && is_array($_POST['challenge_resistances']) ? array_values(array_unique(array_filter($_POST['challenge_resistances']))) : array();
            $form_data['challenge_affinities'] = !empty($_POST['challenge_affinities']) && is_array($_POST['challenge_affinities']) ? array_values(array_unique(array_filter($_POST['challenge_affinities']))) : array();
            $form_data['challenge_immunities'] = !empty($_POST['challenge_immunities']) && is_array($_POST['challenge_immunities']) ? array_values(array_unique(array_filter($_POST['challenge_immunities']))) : array();

            $form_data['challenge_description'] = !empty($_POST['challenge_description']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['challenge_description']) ? trim($_POST['challenge_description']) : '';
            $form_data['challenge_description2'] = !empty($_POST['challenge_description2']) ? trim(strip_tags($_POST['challenge_description2'])) : '';

            $form_data['challenge_quotes_start'] = !empty($_POST['challenge_quotes_start']) ? trim(strip_tags($_POST['challenge_quotes_start'])) : '';
            $form_data['challenge_quotes_taunt'] = !empty($_POST['challenge_quotes_taunt']) ? trim(strip_tags($_POST['challenge_quotes_taunt'])) : '';
            $form_data['challenge_quotes_victory'] = !empty($_POST['challenge_quotes_victory']) ? trim(strip_tags($_POST['challenge_quotes_victory'])) : '';
            $form_data['challenge_quotes_defeat'] = !empty($_POST['challenge_quotes_defeat']) ? trim(strip_tags($_POST['challenge_quotes_defeat'])) : '';

            $form_data['challenge_abilities_rewards'] = !empty($_POST['challenge_abilities_rewards']) ? array_values(array_filter($_POST['challenge_abilities_rewards'])) : array();
            $form_data['challenge_abilities_compatible'] = !empty($_POST['challenge_abilities_compatible']) && is_array($_POST['challenge_abilities_compatible']) ? array_values(array_unique(array_filter($_POST['challenge_abilities_compatible']))) : array();

            $form_data['challenge_functions'] = !empty($_POST['challenge_functions']) && preg_match('/^[-_0-9a-z\.\/]+$/i', $_POST['challenge_functions']) ? trim($_POST['challenge_functions']) : '';

            $form_data['challenge_image'] = !empty($_POST['challenge_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['challenge_image']) ? trim(strtolower($_POST['challenge_image'])) : '';
            $form_data['challenge_image_size'] = !empty($_POST['challenge_image_size']) && is_numeric($_POST['challenge_image_size']) ? (int)(trim($_POST['challenge_image_size'])) : 0;
            $form_data['challenge_creator'] = !empty($_POST['challenge_creator']) && is_numeric($_POST['challenge_creator']) ? (int)(trim($_POST['challenge_creator'])) : 0;
            $form_data['challenge_creator2'] = !empty($_POST['challenge_creator2']) && is_numeric($_POST['challenge_creator2']) ? (int)(trim($_POST['challenge_creator2'])) : 0;

            $form_data['challenge_flag_published'] = isset($_POST['challenge_flag_published']) && is_numeric($_POST['challenge_flag_published']) ? (int)(trim($_POST['challenge_flag_published'])) : 0;
            $form_data['challenge_flag_complete'] = isset($_POST['challenge_flag_complete']) && is_numeric($_POST['challenge_flag_complete']) ? (int)(trim($_POST['challenge_flag_complete'])) : 0;
            $form_data['challenge_flag_hidden'] = isset($_POST['challenge_flag_hidden']) && is_numeric($_POST['challenge_flag_hidden']) ? (int)(trim($_POST['challenge_flag_hidden'])) : 0;

            $form_data['challenge_flag_unlockable'] = isset($_POST['challenge_flag_unlockable']) && is_numeric($_POST['challenge_flag_unlockable']) ? (int)(trim($_POST['challenge_flag_unlockable'])) : 0;

            if ($form_data['challenge_core'] != 'copy'){
                $form_data['challenge_image_alts'] = !empty($_POST['challenge_image_alts']) && is_array($_POST['challenge_image_alts']) ? array_filter($_POST['challenge_image_alts']) : array();
                $challenge_image_alts_new = !empty($_POST['challenge_image_alts_new']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['challenge_image_alts_new']) ? trim(strtolower($_POST['challenge_image_alts_new'])) : '';
            } else {
                $form_data['challenge_image_alts'] = array();
                $challenge_image_alts_new = '';
            }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'challenge_image_alts\']  = '.print_r($_POST['challenge_image_alts'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$_POST[\'challenge_image_alts_new\']  = '.print_r($_POST['challenge_image_alts_new'] , true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (empty($form_data['challenge_id'])){ $form_messages[] = array('error', 'Challenge ID was not provided'); $form_success = false; }
            if (empty($form_data['challenge_name'])){ $form_messages[] = array('error', 'Challenge Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['challenge_kind'])){ $form_messages[] = array('error', 'Challenge Kind was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['challenge_core']) || !isset($_POST['challenge_core2'])){ $form_messages[] = array('warning', 'Core Types were not provided or were invalid'); $form_success = false; }
            if (empty($form_data['challenge_gender'])){ $form_messages[] = array('error', 'Challenge Gender was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_challenge_edit_action($form_data['challenge_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (empty($form_data['challenge_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            if (empty($form_data['challenge_group'])){ $form_messages[] = array('warning', 'Sorting Group was not provided and may cause issues on the front-end'); }
            if (empty($form_data['challenge_number'])){ $form_messages[] = array('warning', 'Serial Number was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if (isset($form_data['challenge_core'])){
                // Fix any core ordering problems (like selecting Neutral + anything)
                $cores = array_values(array_filter(array($form_data['challenge_core'], $form_data['challenge_core2'])));
                $form_data['challenge_core'] = isset($cores[0]) ? $cores[0] : '';
                $form_data['challenge_core2'] = isset($cores[1]) ? $cores[1] : '';
            }

            if (isset($form_data['challenge_weaknesses'])){ $form_data['challenge_weaknesses'] = !empty($form_data['challenge_weaknesses']) ? json_encode($form_data['challenge_weaknesses']) : ''; }
            if (isset($form_data['challenge_resistances'])){ $form_data['challenge_resistances'] = !empty($form_data['challenge_resistances']) ? json_encode($form_data['challenge_resistances']) : ''; }
            if (isset($form_data['challenge_affinities'])){ $form_data['challenge_affinities'] = !empty($form_data['challenge_affinities']) ? json_encode($form_data['challenge_affinities']) : ''; }
            if (isset($form_data['challenge_immunities'])){ $form_data['challenge_immunities'] = !empty($form_data['challenge_immunities']) ? json_encode($form_data['challenge_immunities']) : ''; }

            if (!empty($form_data['challenge_abilities_rewards'])){
                $new_rewards = array();
                $new_rewards_tokens = array();
                foreach ($form_data['challenge_abilities_rewards'] AS $key => $reward){
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
                $form_data['challenge_abilities_rewards'] = $new_rewards;
            }

            if ($form_data['challenge_flag_unlockable']){
                if (!$form_data['challenge_flag_published']){ $form_messages[] = array('warning', 'Challenge must be published to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (!$form_data['challenge_flag_complete']){ $form_messages[] = array('warning', 'Challenge must be complete to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif ($form_data['challenge_kind'] !== 'master'){ $form_messages[] = array('warning', 'Only challenge masters can be marked as unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_field']) && empty($form_data['challenge_field2'])){ $form_messages[] = array('warning', 'Challenge must have battle field to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_functions'])){ $form_messages[] = array('warning', 'Challenge must have a function file to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_description'])){ $form_messages[] = array('warning', 'Challenge must have a flavour class to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_quotes_start'])){ $form_messages[] = array('warning', 'Challenge must have a start quote to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_quotes_taunt'])){ $form_messages[] = array('warning', 'Challenge must have a taunt quote to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_quotes_victory'])){ $form_messages[] = array('warning', 'Challenge must have a victory quote to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_quotes_defeat'])){ $form_messages[] = array('warning', 'Challenge must have a defeat quote to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
                elseif (empty($form_data['challenge_abilities_rewards'])){ $form_messages[] = array('warning', 'Challenge must have at least one ability to be unlockable'); $form_data['challenge_flag_unlockable'] = 0; }
            }


            if (isset($form_data['challenge_abilities_rewards'])){ $form_data['challenge_abilities_rewards'] = !empty($form_data['challenge_abilities_rewards']) ? json_encode($form_data['challenge_abilities_rewards']) : ''; }
            if (isset($form_data['challenge_abilities_compatible'])){ $form_data['challenge_abilities_compatible'] = !empty($form_data['challenge_abilities_compatible']) ? json_encode($form_data['challenge_abilities_compatible']) : ''; }

            $empty_image_folders = array();

            if (isset($form_data['challenge_image_alts'])){
                if (!empty($challenge_image_alts_new)){
                    $alt_num = $challenge_image_alts_new != 'alt' ? (int)(str_replace('alt', '', $challenge_image_alts_new)) : 1;
                    $alt_name = ucfirst($challenge_image_alts_new);
                    if ($alt_num == 9){ $alt_name = 'Darkness Alt'; }
                    elseif ($alt_num == 3){ $alt_name = 'Weapon Alt'; }
                    $form_data['challenge_image_alts'][$challenge_image_alts_new] = array(
                        'token' => $challenge_image_alts_new,
                        'name' => $form_data['challenge_name'].' ('.$alt_name.')',
                        'summons' => ($alt_num * 100),
                        'colour' => ($alt_num == 9 ? 'empty' : 'none')
                        );
                }
                $alt_keys = array_keys($form_data['challenge_image_alts']);
                usort($alt_keys, function($a, $b){
                    $a = strstr($a, 'alt') ? (int)(str_replace('alt', '', $a)) : 0;
                    $b = strstr($b, 'alt') ? (int)(str_replace('alt', '', $b)) : 0;
                    if ($a < $b){ return -1; }
                    elseif ($a > $b){ return 1; }
                    else { return 0; }
                    });
                $new_challenge_image_alts = array();
                foreach ($alt_keys AS $alt_key){
                    $alt_info = $form_data['challenge_image_alts'][$alt_key];
                    $alt_path = $challenge_data['challenge_image'].($alt_key != 'base' ? '_'.$alt_key : '');
                    if (!empty($alt_info['delete_images'])){
                        $delete_sprite_path = 'images/challenges/'.$alt_path.'/';
                        $delete_shadow_path = 'images/challenges_shadows/'.$alt_path.'/';
                        $empty_image_folders[] = $delete_sprite_path;
                        $empty_image_folders[] = $delete_shadow_path;
                    }
                    if (!empty($alt_info['delete'])){ continue; }
                    elseif ($alt_key == 'base'){ continue; }
                    unset($alt_info['delete_images'], $alt_info['delete']);
                    $new_challenge_image_alts[] = $alt_info;
                }
                $form_data['challenge_image_alts'] = $new_challenge_image_alts;
                $form_data['challenge_image_alts'] = !empty($form_data['challenge_image_alts']) ? json_encode($form_data['challenge_image_alts']) : '';
            }
            //$form_messages[] = array('alert', '<pre>$form_data[\'challenge_image_alts\']  = '.print_r($form_data['challenge_image_alts'] , true).'</pre>');

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

            // Make a copy of the update data sans the challenge ID
            $update_data = $form_data;
            unset($update_data['challenge_id']);

            /*
            // If a recent backup of this data doesn't exist, create one now
            $backup_date_time = date('Ymd-Hi');
            $backup_exists = $db->get_value("SELECT backup_id
                FROM mmrpg_challenges_backups
                WHERE
                challenge_kind = '{$update_data['challenge_kind']}'
                AND challenge_creator = '{$update_data['challenge_creator']}'
                AND backup_date_time = '{$backup_date_time}'
                ;", 'backup_id');
            if (empty($backup_exists)){
                $backup_data = $update_data;
                $backup_data['backup_date_time'] = $backup_date_time;
                $db->insert('mmrpg_challenges_backups', $backup_data);
            }
            */

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
            <? if (!$is_backup_data){ ?>
                &raquo; <a href="admin.php?action=edit_challenges&amp;subaction=editor&amp;challenge_id=<?= $challenge_data['challenge_id'] ?>"><?= $challenge_name_display ?></a>
            <? } else { ?>
                &raquo; <a><?= $challenge_name_display ?></a>
            <? } ?>
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

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="challenge_id" value="<?= !empty($search_data['challenge_id']) ? $search_data['challenge_id'] : '' ?>" />
                    </div>
                    */ ?>

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
                            <col class="kind" width="120" />
                            <col class="creator" width="180" />
                            <col class="flag published" width="80" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="90" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id">ID</th>
                                <th class="name">Name</th>
                                <th class="kind">Kind</th>
                                <th class="creator">Creator</th>
                                <th class="flag published">Published</th>
                                <th class="flag hidden">Hidden</th>
                                <th class="actions">Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot name"></td>
                                <td class="foot kind"></td>
                                <td class="foot creator"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot actions count">
                                    <?= $search_results_count == 1 ? '1 Result' : $search_results_count.' Results' ?>
                                </td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?
                            $temp_class_colours = array(
                                'user' => array('defense', '<i class="fas fa-robot"></i>'),
                                'event' => array('attack', '<i class="fas fa-skull"></i>')
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
            && (!empty($_GET['challenge_id']) || !empty($_GET['backup_id']))){

            // Capture editor markup in a buffer in case we need to modify
            if (true){
                ob_start();
                ?>

                <!-- EDITOR FORM -->

                <div class="editor">

                    <h3 class="header type_span type_<?= !empty($challenge_data['challenge_core']) ? $challenge_data['challenge_core'].(!empty($challenge_data['challenge_core2']) ? '_'.$challenge_data['challenge_core2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="challenge_core,challenge_core2">
                        <span class="title"><?= !$is_backup_data ? 'Edit' : 'View' ?> Challenge &quot;<?= $challenge_name_display ?>&quot;</span>
                        <?
                        // If this is backup data, show the backup creation date
                        if ($is_backup_data){

                            // Print out the creation date in a readable form
                            echo '<span style="display: block; clear: left; font-size: 90%; font-weight: normal;">Backup Created '.date('Y/m/d @ g:s a', strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})([0-9]{2})$/', '$1/$2/$3T$4:$5', $challenge_data['backup_date_time']))).'</span>';

                        }
                        ?>
                    </h3>

                    <? print_form_messages() ?>

                    <?
                    // Collect a list of backups for this challenge from the database, if any
                    $challenge_backup_list = array();
                    /* $challenge_backup_list = $db->get_array_list("SELECT
                        backup_id, challenge_id, challenge_name, backup_date_time
                        FROM mmrpg_challenges_backups
                        WHERE challenge_id = '{$challenge_data['challenge_id']}'
                        ORDER BY backup_date_time DESC
                        ;"); */
                    ?>

                    <div class="editor-tabs" data-tabgroup="challenge">
                        <a class="tab active" data-tab="basic">Basic</a><span></span>
                        <a class="tab" data-tab="stats">Stats</a><span></span>
                        <a class="tab" data-tab="abilities">Abilities</a><span></span>
                        <? if (!$is_backup_data && !empty($challenge_backup_list)){ ?>
                            <a class="tab" data-tab="backups">Backups</a><span></span>
                        <? } ?>
                    </div>

                    <form class="form" method="post">

                        <input type="hidden" name="action" value="edit_challenges" />
                        <input type="hidden" name="subaction" value="editor" />

                        <div class="editor-panels" data-tabgroup="challenge">

                            <div class="panel active" data-tab="basic">

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
                                        <em>appears at battle start, max 256 characters</em>
                                    </div>
                                    <textarea class="textarea" name="challenge_description" maxlength="256" rows="3"><?= htmlentities($challenge_data['challenge_description'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                </div>

                                <hr />

                                <?
                                // Decode the field data so we can work with it
                                $challenge_field_data = !empty($challenge_data['challenge_field_data']) ? json_decode($challenge_data['challenge_field_data'], true) : array();
                                ?>

                                <div class="field">
                                    <strong class="label">Field Background</strong>
                                    <select class="select" name="challenge_field_data[field_background]">
                                        <?
                                        echo('<option value=""'.(empty($challenge_field_data['field_background']) ? 'selected="selected"' : '').'>- none -</option>');
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
                                        echo('<option value=""'.(empty($challenge_field_data['field_foreground']) ? 'selected="selected"' : '').'>- none -</option>');
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
                                        echo('<option value=""'.(empty($challenge_field_data['field_music']) ? 'selected="selected"' : '').'>- none -</option>');
                                        foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                            $label = $field_data['field_name'];
                                            $label .= ' ('.(!empty($field_data['field_type']) ? ucfirst($field_data['field_type']) : 'Neutral').')';
                                            $selected = !empty($challenge_field_data['field_music']) && $challenge_field_data['field_music'] == $field_token ? 'selected="selected"' : '';
                                            echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        }
                                        ?>
                                    </select><span></span>
                                </div>

                            </div>

                            <div class="panel" data-tab="stats">

                                <?
                                $challenge_type_matchups = array('weaknesses', 'resistances', 'affinities', 'immunities');
                                foreach ($challenge_type_matchups AS $matchup_key => $matchup_token){
                                    $matchup_list = $challenge_data['challenge_'.$matchup_token];
                                    $matchup_list = !empty($matchup_list) ? json_decode($matchup_list, true) : array();
                                    ?>
                                    <div class="field fullsize has4cols">
                                        <strong class="label">
                                            Challenge <?= ucfirst($matchup_token) ?>
                                        </strong>
                                        <? for ($i = 0; $i < 4; $i++){ ?>
                                            <div class="subfield">
                                                <span class="type_span type_<?= !empty($matchup_list[$i]) ? $matchup_list[$i] : '' ?> swatch floatright hidenone" data-auto="field-type" data-field-type="challenge_<?= $matchup_token ?>[<?= $i ?>]">&nbsp;</span>
                                                <select class="select" name="challenge_<?= $matchup_token ?>[<?= $i ?>]">
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
                                    if ($ability_info['ability_class'] === 'mecha' && $challenge_data['challenge_kind'] !== 'mecha'){ continue; }
                                    elseif ($ability_info['ability_class'] === 'boss' && $challenge_data['challenge_kind'] !== 'boss'){ continue; }
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
                                        Level-Up Abilities
                                        <em>Only hero and support challenges require level-up, others should unlock all at start</em>
                                    </strong>
                                    <?
                                    $current_ability_list = !empty($challenge_data['challenge_abilities_rewards']) ? json_decode($challenge_data['challenge_abilities_rewards'], true) : array();
                                    $select_limit = max(8, count($current_ability_list));
                                    $select_limit += 2;
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_value = isset($current_ability_list[$i]) ? $current_ability_list[$i] : array();
                                        $current_value_level = !empty($current_value) ? $current_value['level'] : '';
                                        $current_value_token = !empty($current_value) ? $current_value['token'] : '';
                                        ?>
                                        <div class="subfield levelup">
                                            <input class="textarea" type="number" name="challenge_abilities_rewards[<?= $i ?>][level]" value="<?= $current_value_level ?>" maxlength="3" placeholder="0" />
                                            <select class="select" name="challenge_abilities_rewards[<?= $i ?>][token]">
                                                <?= str_replace('value="'.$current_value_token.'"', 'value="'.$current_value_token.'" selected="selected"', $ability_options_markup) ?>
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
                                        <em>Excluding level-up abilities and <u title="<?= implode(', ', $global_ability_tokens) ?>">global ones</u> available to all challenges by default</em>
                                    </strong>
                                    <?
                                    $current_ability_list = !empty($challenge_data['challenge_abilities_compatible']) ? json_decode($challenge_data['challenge_abilities_compatible'], true) : array();
                                    $current_ability_list = array_values(array_filter($current_ability_list, function($token) use($global_ability_tokens){ return !in_array($token, $global_ability_tokens); }));
                                    $select_limit = max(12, count($current_ability_list));
                                    $select_limit += 4 - ($select_limit % 4);
                                    $select_limit += 4;
                                    for ($i = 0; $i < $select_limit; $i++){
                                        $current_value = isset($current_ability_list[$i]) ? $current_ability_list[$i] : '';
                                        ?>
                                        <div class="subfield">
                                            <select class="select" name="challenge_abilities_compatible[<?= $i ?>]">
                                                <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $ability_options_markup) ?>
                                            </select><span></span>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>

                            </div>

                            <? if (!$is_backup_data && !empty($challenge_backup_list)){ ?>
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
                                            <? foreach ($challenge_backup_list AS $backup_key => $backup_info){ ?>
                                                <? $backup_unix_time = strtotime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})([0-9]{2})$/', '$1/$2/$3T$4:$5', $backup_info['backup_date_time'])); ?>
                                                <tr>
                                                    <td class="id"><?= $backup_info['backup_id'] ?></td>
                                                    <td class="name"><?= $backup_info['challenge_name'] ?></td>
                                                    <td class="date"><?= date('Y/m/d', $backup_unix_time) ?></td>
                                                    <td class="time"><?= date('g:i a', $backup_unix_time) ?></td>
                                                    <td class="actions">
                                                        <a href="admin.php?action=edit_challenges&subaction=editor&backup_id=<?= $backup_info['backup_id'] ?>" target="_blank" style="text-decoration: none;">
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

                            <? if (!empty($challenge_data['challenge_flag_published'])
                                && !empty($challenge_data['challenge_flag_complete'])
                                && $challenge_data['challenge_kind'] == 'master'){ ?>
                                <div class="field checkwrap">
                                    <label class="label">
                                        <strong>Unlockable</strong>
                                        <input type="hidden" name="challenge_flag_unlockable" value="0" checked="checked" />
                                        <input class="checkbox" type="checkbox" name="challenge_flag_unlockable" value="1" <?= !empty($challenge_data['challenge_flag_unlockable']) ? 'checked="checked"' : '' ?> />
                                    </label>
                                    <p class="subtext">This challenge is ready to be used in the game</p>
                                </div>
                            <? } ?>

                        </div>

                        <hr />

                        <div class="formfoot">

                            <? if (!$is_backup_data){ ?>
                                <div class="buttons">
                                    <input class="button save" type="submit" value="Save Changes" />
                                    <input class="button cancel" type="button" value="Reset Changes" onclick="javascript:window.location.href='admin.php?action=edit_challenges&subaction=editor&challenge_id=<?= $challenge_data['challenge_id'] ?>';" />
                                    <? /*
                                    <input class="button delete" type="button" value="Delete Challenge" data-delete="challenges" data-challenge-id="<?= $challenge_data['challenge_id'] ?>" />
                                    */ ?>
                                </div>
                            <? } ?>

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

                $debug_challenge_data = $challenge_data;
                echo('<pre style="display: block;">$challenge_data = '.(!empty($debug_challenge_data) ? htmlentities(print_r($debug_challenge_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

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