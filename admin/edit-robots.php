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

    // Collect an index of robot colours for options
    $mmrpg_robots_fields = rpg_robot::get_index_fields(true);
    $mmrpg_robots_index = $db->get_array_list("SELECT {$mmrpg_robots_fields} FROM mmrpg_index_robots WHERE robot_token <> 'robot' ORDER BY robot_order ASC", 'robot_token');

    // Collect an index of robot colours for options
    $mmrpg_abilities_fields = rpg_ability::get_index_fields(true);
    $mmrpg_abilities_index = $db->get_array_list("SELECT {$mmrpg_abilities_fields} FROM mmrpg_index_abilities WHERE ability_token <> 'ability' AND ability_class <> 'system' ORDER BY ability_order ASC", 'ability_token');


    /* -- Form Setup Actions -- */

    // Define a function for exiting a robot edit action
    function exit_robot_edit_action($robot_id = 0){
        if (!empty($robot_id)){ $location = 'admin.php?action=edit_robots&subaction=editor&robot_id='.$robot_id; }
        else { $location = 'admin.php?action=edit_robots&subaction=search'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Robots | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if (false && $sub_action == 'delete' && !empty($_GET['robot_id'])){

        // Collect form data for processing
        $delete_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';

        // Let's delete all of this robot's data from the database
        $db->delete('mmrpg_index_robots', array('robot_id' => $delete_data['robot_id']));
        $form_messages[] = array('success', 'The requested robot has been deleted from the database');
        exit_form_action('success');

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    if ($sub_action == 'search' && (
        !empty($_GET['robot_id'])
        || !empty($_GET['robot_name'])
        || !empty($_GET['robot_core'])
        || !empty($_GET['robot_class'])
        || !empty($_GET['robot_game'])
        || !empty($_GET['robot_group'])
        || (isset($_GET['robot_flag_hidden']) && $_GET['robot_flag_hidden'] !== '')
        || (isset($_GET['robot_flag_complete']) && $_GET['robot_flag_complete'] !== '')
        || (isset($_GET['robot_flag_published']) && $_GET['robot_flag_published'] !== '')
        )){

        // Collect form data for processing
        $search_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';
        $search_data['robot_name'] = !empty($_GET['robot_name']) && preg_match('/[-_0-9a-z\.\*\s]+/i', $_GET['robot_name']) ? trim(strtolower($_GET['robot_name'])) : '';
        $search_data['robot_core'] = !empty($_GET['robot_core']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_core']) ? trim(strtolower($_GET['robot_core'])) : '';
        $search_data['robot_class'] = !empty($_GET['robot_class']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_class']) ? trim(strtolower($_GET['robot_class'])) : '';
        $search_data['robot_game'] = !empty($_GET['robot_game']) && preg_match('/[-_0-9a-z]+/i', $_GET['robot_game']) ? trim(strtoupper($_GET['robot_game'])) : '';
        $search_data['robot_group'] = !empty($_GET['robot_group']) && preg_match('/[-_0-9a-z\/]+/i', $_GET['robot_group']) ? trim($_GET['robot_group']) : '';
        $search_data['robot_flag_hidden'] = isset($_GET['robot_flag_hidden']) && $_GET['robot_flag_hidden'] !== '' ? (!empty($_GET['robot_flag_hidden']) ? 1 : 0) : '';
        $search_data['robot_flag_complete'] = isset($_GET['robot_flag_complete']) && $_GET['robot_flag_complete'] !== '' ? (!empty($_GET['robot_flag_complete']) ? 1 : 0) : '';
        $search_data['robot_flag_published'] = isset($_GET['robot_flag_published']) && $_GET['robot_flag_published'] !== '' ? (!empty($_GET['robot_flag_published']) ? 1 : 0) : '';

        /* -- Collect Search Results -- */

        // Define the search query to use
        $temp_robot_fields = rpg_robot::get_index_fields(true, 'robot');
        $search_query = "SELECT
            {$temp_robot_fields}
            FROM mmrpg_index_robots AS robot
            WHERE 1=1
            AND robot_token <> 'robot'
            ";

        // If the robot ID was provided, we can search by exact match
        if (!empty($search_data['robot_id'])){
            $robot_id = $search_data['robot_id'];
            $search_query .= "AND robot_id = {$robot_id} ";
        }

        // Else if the robot name was provided, we can use wildcards
        if (!empty($search_data['robot_name'])){
            $robot_name = $search_data['robot_name'];
            $robot_name = str_replace(array(' ', '*', '%'), '%', $robot_name);
            $robot_name = preg_replace('/%+/', '%', $robot_name);
            $robot_name = '%'.$robot_name.'%';
            $search_query .= "AND (robot_name LIKE '{$robot_name}' OR robot_token LIKE '{$robot_name}') ";
        }

        // Else if the robot core was provided, we can use wildcards
        if (!empty($search_data['robot_core'])){
            $robot_core = $search_data['robot_core'];
            if ($robot_core !== 'none'){ $search_query .= "AND (robot_core LIKE '{$robot_core}' OR robot_core2 LIKE '{$robot_core}') "; }
            else { $search_query .= "AND robot_core = '' "; }
        }

        // If the robot class was provided
        if (!empty($search_data['robot_class'])){
            $search_query .= "AND robot_class = '{$search_data['robot_class']}' ";
        }

        // If the robot game was provided
        if (!empty($search_data['robot_game'])){
            $search_query .= "AND robot_game = '{$search_data['robot_game']}' ";
        }

        // If the robot group was provided
        if (!empty($search_data['robot_group'])){
            $search_query .= "AND robot_group = '{$search_data['robot_group']}' ";
        }

        // If the robot flag published was provided
        if ($search_data['robot_flag_published'] !== ''){
            $search_query .= "AND robot_flag_published = {$search_data['robot_flag_published']} ";
        }

        // If the robot flag complete was provided
        if ($search_data['robot_flag_complete'] !== ''){
            $search_query .= "AND robot_flag_complete = {$search_data['robot_flag_complete']} ";
        }

        // If the robot flag hidden was provided
        if ($search_data['robot_flag_hidden'] !== ''){
            $search_query .= "AND robot_flag_hidden = {$search_data['robot_flag_hidden']} ";
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($search_data['robot_name'])){ $order_by[] = "robot_name ASC"; }
        $order_by[] = "FIELD(robot_class, 'mecha', 'master', 'boss')";
        $order_by[] = "robot_order ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string};";

        // Collect search results from the database
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;


    }

    // If we're in editor mode, we should collect robot info from database
    $robot_data = array();
    $editor_data = array();
    if ($sub_action == 'editor' && !empty($_GET['robot_id'])){

        // Collect form data for processing
        $editor_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';


        /* -- Collect Robot Data -- */

        // Collect robot details from the database
        $temp_robot_fields = rpg_robot::get_index_fields(true);
        $robot_data = $db->get_array("SELECT {$temp_robot_fields} FROM mmrpg_index_robots WHERE robot_id = {$editor_data['robot_id']};");

        // If robot data could not be found, produce error and exit
        if (empty($robot_data)){ exit_robot_edit_action(); }

        // Collect the robot's name(s) for display
        $robot_name_display = $robot_data['robot_name'];
        $this_page_tabtitle = $robot_name_display.' | '.$this_page_tabtitle;

        // If form data has been submit for this robot, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit_robots'){

            // COLLECT form data from the request and parse out simple rules

            $old_robot_token = !empty($_POST['old_robot_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['old_robot_token']) ? trim(strtolower($_POST['old_robot_token'])) : '';

            $form_data['robot_id'] = !empty($_POST['robot_id']) && is_numeric($_POST['robot_id']) ? trim($_POST['robot_id']) : 0;
            $form_data['robot_token'] = !empty($_POST['robot_token']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_token']) ? trim(strtolower($_POST['robot_token'])) : '';
            $form_data['robot_name'] = !empty($_POST['robot_name']) && preg_match('/^[-_0-9a-z\.\*\s]+$/i', $_POST['robot_name']) ? trim($_POST['robot_name']) : '';
            $form_data['robot_class'] = !empty($_POST['robot_class']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_class']) ? trim(strtolower($_POST['robot_class'])) : '';
            $form_data['robot_core'] = !empty($_POST['robot_core']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_core']) ? trim(strtolower($_POST['robot_core'])) : '';
            $form_data['robot_core2'] = !empty($_POST['robot_core2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_core2']) ? trim(strtolower($_POST['robot_core2'])) : '';
            $form_data['robot_gender'] = !empty($_POST['robot_gender']) && preg_match('/^(male|female|other|none)$/', $_POST['robot_gender']) ? trim(strtolower($_POST['robot_gender'])) : '';

            $form_data['robot_game'] = !empty($_POST['robot_game']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_game']) ? trim($_POST['robot_game']) : '';
            $form_data['robot_group'] = !empty($_POST['robot_group']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['robot_group']) ? trim($_POST['robot_group']) : '';
            $form_data['robot_number'] = !empty($_POST['robot_number']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_number']) ? trim($_POST['robot_number']) : '';
            $form_data['robot_order'] = !empty($_POST['robot_order']) && is_numeric($_POST['robot_order']) ? (int)(trim($_POST['robot_order'])) : 0;

            $form_data['robot_field'] = !empty($_POST['robot_field']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_field']) ? trim(strtolower($_POST['robot_field'])) : '';
            $form_data['robot_field2'] = !empty($_POST['robot_field2']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_field2']) ? trim(strtolower($_POST['robot_field2'])) : '';
            //$form_data['robot_mecha'] = !empty($_POST['robot_mecha']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_mecha']) ? trim(strtolower($_POST['robot_mecha'])) : '';

            $form_data['robot_energy'] = !empty($_POST['robot_energy']) && is_numeric($_POST['robot_energy']) ? (int)(trim($_POST['robot_energy'])) : 0;
            $form_data['robot_weapons'] = !empty($_POST['robot_weapons']) && is_numeric($_POST['robot_weapons']) ? (int)(trim($_POST['robot_weapons'])) : 0;
            $form_data['robot_attack'] = !empty($_POST['robot_attack']) && is_numeric($_POST['robot_attack']) ? (int)(trim($_POST['robot_attack'])) : 0;
            $form_data['robot_defense'] = !empty($_POST['robot_defense']) && is_numeric($_POST['robot_defense']) ? (int)(trim($_POST['robot_defense'])) : 0;
            $form_data['robot_speed'] = !empty($_POST['robot_speed']) && is_numeric($_POST['robot_speed']) ? (int)(trim($_POST['robot_speed'])) : 0;

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

            $form_data['robot_abilities_rewards'] = !empty($_POST['robot_abilities_rewards']) ? array_values(array_unique(array_filter($_POST['robot_abilities_rewards']))) : array();
            $form_data['robot_abilities_compatible'] = !empty($_POST['robot_abilities_compatible']) ? array_values(array_unique(array_filter($_POST['robot_abilities_compatible']))) : array();

            $form_data['robot_functions'] = !empty($_POST['robot_functions']) && preg_match('/^[-_0-9a-z\.\/]+$/i', $_POST['robot_functions']) ? trim($_POST['robot_functions']) : '';

            $form_data['robot_flag_published'] = isset($_POST['robot_flag_published']) && is_numeric($_POST['robot_flag_published']) ? (int)(trim($_POST['robot_flag_published'])) : 0;
            $form_data['robot_flag_complete'] = isset($_POST['robot_flag_complete']) && is_numeric($_POST['robot_flag_complete']) ? (int)(trim($_POST['robot_flag_complete'])) : 0;
            $form_data['robot_flag_hidden'] = isset($_POST['robot_flag_hidden']) && is_numeric($_POST['robot_flag_hidden']) ? (int)(trim($_POST['robot_flag_hidden'])) : 0;

            $form_data['robot_abilities_compatible'] = !empty($_POST['robot_abilities_compatible']) && is_array($_POST['robot_abilities_compatible']) ? array_values(array_unique(array_filter($_POST['robot_abilities_compatible']))) : array();

            /*
            $form_data['robot_image'] = !empty($_POST['robot_image']) && preg_match('/^[-_0-9a-z]+$/i', $_POST['robot_image']) ? trim(strtolower($_POST['robot_image'])) : '';
            $form_data['robot_image_size'] = !empty($_POST['robot_image_size']) && is_numeric($_POST['robot_image_size']) ? (int)(trim($_POST['robot_image_size'])) : 0;
            $form_data['robot_image_editor'] = !empty($_POST['robot_image_editor']) && is_numeric($_POST['robot_image_editor']) ? (int)(trim($_POST['robot_image_editor'])) : 0;
            $form_data['robot_image_alts'] = !empty($_POST['robot_image_alts']) ? trim(strip_tags($_POST['robot_image_alts'])) : '';
            */

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // VALIDATE all of the MANDATORY FIELDS to see if any are invalid and abort the update entirely if necessary
            if (empty($form_data['robot_id'])){ $form_messages[] = array('error', 'Robot ID was not provided'); $form_success = false; }
            if (empty($form_data['robot_token']) || empty($old_robot_token)){ $form_messages[] = array('error', 'Robot Token was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['robot_name'])){ $form_messages[] = array('error', 'Robot Name was not provided or was invalid'); $form_success = false; }
            if (empty($form_data['robot_class'])){ $form_messages[] = array('error', 'Robot Kind was not provided or was invalid'); $form_success = false; }
            if (!isset($_POST['robot_core']) || !isset($_POST['robot_core2'])){ $form_messages[] = array('warning', 'Core Types were not provided or were invalid'); $form_success = false; }
            if (empty($form_data['robot_gender'])){ $form_messages[] = array('error', 'Robot Gender was not provided or was invalid'); $form_success = false; }
            if (!$form_success){ exit_robot_edit_action($form_data['robot_id']); }

            // VALIDATE all of the SEMI-MANDATORY FIELDS to see if any were not provided and unset them from updating if necessary
            if (empty($form_data['robot_game'])){ $form_messages[] = array('warning', 'Source Game was not provided and may cause issues on the front-end'); }
            if (empty($form_data['robot_group'])){ $form_messages[] = array('warning', 'Sorting Group was not provided and may cause issues on the front-end'); }
            if (empty($form_data['robot_number'])){ $form_messages[] = array('warning', 'Serial Number was not provided and may cause issues on the front-end'); }

            // REFORMAT or OPTIMIZE data for provided fields where necessary

            if (isset($form_data['robot_core'])){
                // Fix any core ordering problems (like selecting Neutral + anything)
                $cores = array_values(array_filter(array($form_data['robot_core'], $form_data['robot_core2'])));
                $form_data['robot_core'] = isset($cores[0]) ? $cores[0] : '';
                $form_data['robot_core2'] = isset($cores[1]) ? $cores[1] : '';
                }

            if (isset($form_data['robot_weaknesses'])){ $form_data['robot_weaknesses'] = !empty($form_data['robot_weaknesses']) ? json_encode($form_data['robot_weaknesses']) : ''; }
            if (isset($form_data['robot_resistances'])){ $form_data['robot_resistances'] = !empty($form_data['robot_resistances']) ? json_encode($form_data['robot_resistances']) : ''; }
            if (isset($form_data['robot_affinities'])){ $form_data['robot_affinities'] = !empty($form_data['robot_affinities']) ? json_encode($form_data['robot_affinities']) : ''; }
            if (isset($form_data['robot_immunities'])){ $form_data['robot_immunities'] = !empty($form_data['robot_immunities']) ? json_encode($form_data['robot_immunities']) : ''; }

            if (isset($form_data['robot_abilities_compatible'])){ $form_data['robot_abilities_compatible'] = !empty($form_data['robot_abilities_compatible']) ? json_encode($form_data['robot_abilities_compatible']) : ''; }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // Loop through fields to create an update string
            $update_data = $form_data;
            //$update_data['robot_date_modified'] = time();
            unset($update_data['robot_id']);
            $update_results = $db->update('mmrpg_index_robots', $update_data, array('robot_id' => $form_data['robot_id']));

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If we made it this far, the update must have been a success
            if ($update_results !== false){ $form_messages[] = array('success', 'Robot data was updated successfully!'); }
            else { $form_messages[] = array('error', 'Robot data could not be updated...'); }

            // We're done processing the form, we can exit
            exit_robot_edit_action($form_data['robot_id']);

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }


    ?>

    <div class="breadcrumb">
        <a href="admin.php">Admin Panel</a>
        &raquo; <a href="admin.php?action=edit_robots">Edit Robots</a>
        <? if ($sub_action == 'editor' && !empty($robot_data)): ?>
            &raquo; <a href="admin.php?action=edit_robots&amp;subaction=editor&amp;robot_id=<?= $robot_data['robot_id'] ?>"><?= $robot_name_display ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit_robots">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Robots</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <input type="hidden" name="action" value="edit_robots" />
                    <input type="hidden" name="subaction" value="search" />

                    <? /*
                    <div class="field">
                        <strong class="label">By ID Number</strong>
                        <input class="textbox" type="text" name="robot_id" value="<?= !empty($search_data['robot_id']) ? $search_data['robot_id'] : '' ?>" />
                    </div>
                    */ ?>

                    <div class="field">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="robot_name" placeholder="-" value="<?= !empty($search_data['robot_name']) ? htmlentities($search_data['robot_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field">
                        <strong class="label">By Class</strong>
                        <select class="select" name="robot_class">
                            <option value="">-</option>
                            <option value="mecha"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'mecha' ? ' selected="selected"' : '' ?>>Mecha</option>
                            <option value="master"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'master' ? ' selected="selected"' : '' ?>>Master</option>
                            <option value="boss"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'boss' ? ' selected="selected"' : '' ?>>Boss</option>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Type</strong>
                        <select class="select" name="robot_core"><option value="">-</option><?
                            foreach ($mmrpg_types_index AS $type_token => $type_info){
                                if ($type_info['type_class'] === 'special' && $type_token !== 'none'){ continue; }
                                ?><option value="<?= $type_token ?>"<?= !empty($search_data['robot_core']) && $search_data['robot_core'] === $type_token ? ' selected="selected"' : '' ?>><?= $type_token === 'none' ? 'Neutral' : ucfirst($type_token) ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Game</strong>
                        <select class="select" name="robot_game"><option value="">-</option><?
                            $robot_games_tokens = $db->get_array_list("SELECT DISTINCT (robot_game) AS game_token FROM mmrpg_index_robots ORDER BY robot_game ASC;");
                            foreach ($robot_games_tokens AS $game_key => $game_info){
                                $game_token = $game_info['game_token'];
                                ?><option value="<?= $game_token ?>"<?= !empty($search_data['robot_game']) && $search_data['robot_game'] === $game_token ? ' selected="selected"' : '' ?>><?= $game_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">By Group</strong>
                        <select class="select" name="robot_group"><option value="">-</option><?
                            $robot_groups_tokens = $db->get_array_list("SELECT DISTINCT (robot_group) AS group_token FROM mmrpg_index_robots ORDER BY robot_group ASC;");
                            foreach ($robot_groups_tokens AS $group_key => $group_info){
                                $group_token = $group_info['group_token'];
                                ?><option value="<?= $group_token ?>"<?= !empty($search_data['robot_group']) && $search_data['robot_group'] === $group_token ? ' selected="selected"' : '' ?>><?= $group_token ?></option><?
                                } ?>
                        </select><span></span>
                    </div>

                    <div class="field flags">
                    <?
                    $flag_names = array(
                        'published' => array('icon' => 'fas fa-check-square', 'yes' => 'Published', 'no' => 'Unpublished'),
                        'complete' => array('icon' => 'fas fa-check-circle', 'yes' => 'Complete', 'no' => 'Incomplete'),
                        'hidden' => array('icon' => 'fas fa-eye-slash', 'yes' => 'Hidden', 'no' => 'Visible')
                        );
                    foreach ($flag_names AS $flag_token => $flag_info){
                        $flag_name = 'robot_flag_'.$flag_token;
                        $flag_label = ucfirst($flag_token);
                        ?>
                        <div class="subfield">
                            <strong class="label"><?= $flag_label ?> <span class="<?= $flag_info['icon'] ?>"></span></strong>
                            <select class="select" name="<?= $flag_name ?>">
                                <option value=""<?= !isset($search_data[$flag_name]) || $search_data[$flag_name] === '' ? ' selected="selected"' : '' ?>>-</option>
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
                        <input class="button" type="reset" value="Reset" onclick="javascript:window.location.href='admin.php?action=edit_robots';" />
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
                            <col class="group" width="80" />
                            <col class="flag published" width="80" />
                            <col class="flag complete" width="75" />
                            <col class="flag hidden" width="70" />
                            <col class="actions" width="90" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id">ID</th>
                                <th class="name">Name</th>
                                <th class="class">Class</th>
                                <th class="type">Type(s)</th>
                                <th class="game">Game</th>
                                <th class="group">Group</th>
                                <th class="flag published">Published</th>
                                <th class="flag complete">Complete</th>
                                <th class="flag hidden">Hidden</th>
                                <th class="actions">Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot name"></td>
                                <td class="foot class"></td>
                                <td class="foot type"></td>
                                <td class="foot game"></td>
                                <td class="foot group"></td>
                                <td class="foot flag published"></td>
                                <td class="foot flag complete"></td>
                                <td class="foot flag hidden"></td>
                                <td class="foot actions count">
                                    <?= $search_results_count == 1 ? '1 Result' : $search_results_count.' Results' ?>
                                </td>
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
                                $robot_group = ucfirst($robot_data['robot_group']);
                                $robot_group_span = '<span class="type_span type_cutter">'.$robot_group.'</span>';
                                $robot_flag_published = !empty($robot_data['robot_flag_published']) ? '<i class="fas fa-check-square"></i>' : '-';
                                $robot_flag_complete = !empty($robot_data['robot_flag_complete']) ? '<i class="fas fa-check-circle"></i>' : '-';
                                $robot_flag_hidden = !empty($robot_data['robot_flag_hidden']) ? '<i class="fas fa-eye-slash"></i>' : '-';

                                $robot_edit_url = 'admin.php?action=edit_robots&subaction=editor&robot_id='.$robot_id;
                                $robot_name_link = '<a class="link" href="'.$robot_edit_url.'">'.$robot_name.'</a>';

                                $robot_actions = '';
                                $robot_actions .= '<a class="link edit" href="'.$robot_edit_url.'"><span>edit</span></a>';
                                $robot_actions .= '<span class="link delete disabled"><span>delete</span></span>';
                                //$robot_actions .= '<a class="link delete" data-delete="robots" data-robot-id="'.$robot_id.'"><span>delete</span></a>';

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$robot_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$robot_name_link.'</div></td>'.PHP_EOL;
                                    echo '<td class="class"><div class="wrap">'.$robot_class_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="type"><div class="wrap">'.$robot_core_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="game"><div class="wrap">'.$robot_game_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="group"><div class="wrap">'.$robot_group_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag published"><div>'.$robot_flag_published.'</div></td>'.PHP_EOL;
                                    echo '<td class="flag complete"><div>'.$robot_flag_complete.'</div></td>'.PHP_EOL;
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

        <? if ($sub_action == 'editor' && !empty($_GET['robot_id'])): ?>

            <!-- EDITOR FORM -->

            <div class="editor">

                <h3 class="header type_span type_<?= !empty($robot_data['robot_core']) ? $robot_data['robot_core'].(!empty($robot_data['robot_core2']) ? '_'.$robot_data['robot_core2'] : '') : 'none' ?>" data-auto="field-type" data-field-type="robot_core,robot_core2">Edit Robot &quot;<?= $robot_name_display ?>&quot;</h3>

                <? print_form_messages() ?>

                <form class="form" method="post">

                    <input type="hidden" name="action" value="edit_robots" />
                    <input type="hidden" name="subaction" value="editor" />

                    <div class="field">
                        <strong class="label">Robot ID</strong>
                        <input type="hidden" name="robot_id" value="<?= $robot_data['robot_id'] ?>" />
                        <input class="textbox" type="text" name="robot_id" value="<?= $robot_data['robot_id'] ?>" disabled="disabled" />
                    </div>

                    <div class="field">
                        <div class="label">
                            <strong>Robot Token</strong>
                            <em>avoid changing</em>
                        </div>
                        <input type="hidden" name="old_robot_token" value="<?= $robot_data['robot_token'] ?>" />
                        <input class="textbox" type="text" name="robot_token" value="<?= $robot_data['robot_token'] ?>" maxlength="64" />
                    </div>

                    <div class="field">
                        <strong class="label">Robot Name</strong>
                        <input class="textbox" type="text" name="robot_name" value="<?= $robot_data['robot_name'] ?>" maxlength="16" />
                    </div>

                    <div class="field">
                        <strong class="label">Robot Kind</strong>
                        <select class="select" name="robot_class">
                            <option value="mecha" <?= $robot_data['robot_class'] == 'mecha' ? 'selected="selected"' : '' ?>>Support Mecha</option>
                            <option value="master" <?= empty($robot_data['robot_class']) || $robot_data['robot_class'] == 'master' ? 'selected="selected"' : '' ?>>Robot Master</option>
                            <option value="boss" <?= $robot_data['robot_class'] == 'boss' ? 'selected="selected"' : '' ?>>Fortress Boss</option>
                        </select><span></span>
                    </div>

                    <div class="field has2cols">
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

                    <div class="field">
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

                    <hr />

                    <div class="field foursize">
                        <strong class="label">Source Game</strong>
                        <select class="select" name="robot_game">
                            <?
                            $robot_games_tokens = $db->get_array_list("SELECT DISTINCT (robot_game) AS game_token FROM mmrpg_index_robots WHERE robot_game <> '' ORDER BY robot_game ASC;", 'game_token');
                            echo('<option value=""'.(empty($robot_data['robot_game']) ? 'selected="selected"' : '').'>- none -</option>');
                            foreach ($robot_games_tokens AS $game_token => $game_data){
                                $label = $game_token;
                                $selected = !empty($robot_data['robot_game']) && $robot_data['robot_game'] == $game_token ? 'selected="selected"' : '';
                                echo('<option value="'.$game_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field foursize">
                        <strong class="label">Sort Group</strong>
                        <input class="textbox" type="text" name="robot_group" value="<?= $robot_data['robot_group'] ?>" maxlength="64" />
                    </div>

                    <div class="field foursize">
                        <strong class="label">Serial Number</strong>
                        <input class="textbox" type="text" name="robot_number" value="<?= $robot_data['robot_number'] ?>" maxlength="64" />
                    </div>

                    <div class="field foursize">
                        <strong class="label">Sort Order</strong>
                        <input class="textbox" type="number" name="robot_order" value="<?= $robot_data['robot_order'] ?>" maxlength="8" />
                    </div>

                    <hr />

                    <div class="field">
                        <strong class="label">Home Field</strong>
                        <select class="select" name="robot_field">
                            <?
                            echo('<option value=""'.(empty($robot_data['robot_field']) ? 'selected="selected"' : '').'>- none -</option>');
                            foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                $label = $field_data['field_name'];
                                $selected = !empty($robot_data['robot_field']) && $robot_data['robot_field'] == $field_token ? 'selected="selected"' : '';
                                echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">Echo Field</strong>
                        <select class="select" name="robot_field2">
                            <?
                            echo('<option value=""'.(empty($robot_data['robot_field2']) ? 'selected="selected"' : '').'>- none -</option>');
                            foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                $label = $field_data['field_name'];
                                $selected = !empty($robot_data['robot_field2']) && $robot_data['robot_field2'] == $field_token ? 'selected="selected"' : '';
                                echo('<option value="'.$field_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <? if ($robot_data['robot_field'] !== 'mecha'){
                        ?>
                        <div class="field disabled">
                            <strong class="label">Support Mecha</strong>
                            <select class="select disabled" name="robot_mecha" disabled="disabled">
                                <option value="">-</option>
                            </select><span></span>
                        </div>
                        <?
                    } ?>

                    <hr />

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
                                Robot <?= ucfirst($matchup_token) ?>
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

                    <hr />

                    <div class="field halfsize">
                        <div class="label">
                            <strong>Robot Class</strong>
                            <em>three word classification</em>
                        </div>
                        <input class="textbox" type="text" name="robot_description" value="<?= htmlentities($robot_data['robot_description'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="32" />
                    </div>

                    <div class="field fullsize">
                        <div class="label">
                            <strong>Robot Description</strong>
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
                            <input class="textbox" type="text" name="robot_quotes_<?= $kind_token ?>" value="<?= htmlentities($robot_data['robot_quotes_'.$kind_token], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="32" />
                        </div>
                        <?
                    }
                    ?>

                    <div class="field fullsize" style="min-height: 0; margin-bottom: 0; padding-bottom: 0;">
                        <div class="label">
                            <em class="nowrap" style="margin-left: 0;">(!) You can use <strong>{this_player}</strong>, <strong>{this_robot}</strong>, <strong>{target_player}</strong>, and <strong>{target_robot}</strong> variables for dynamic text</em>
                        </div>
                    </div>

                    <hr />

                    <?

                    // Collect global abilities so we can skip them
                    $global_ability_tokens = rpg_ability::get_global_abilities();

                    // Pre-generate a list of all abilities so we can re-use it over and over
                    $ability_options_markup = array();
                    $ability_options_markup[] = '<option value="">-</option>';
                    foreach ($mmrpg_abilities_index AS $ability_token => $ability_info){
                        if (in_array($ability_token, $global_ability_tokens)){ continue; }
                        if ($ability_info['ability_class'] === 'mecha' && $robot_data['robot_class'] !== 'mecha'){ continue; }
                        elseif ($ability_info['ability_class'] === 'boss' && $robot_data['robot_class'] !== 'boss'){ continue; }
                        $ability_name = $ability_info['ability_name'];
                        $ability_types = ucwords(implode(' / ', array_values(array_filter(array($ability_info['ability_type'], $ability_info['ability_type2'])))));
                        if (empty($ability_types)){ $ability_types = 'Neutral'; }
                        $ability_options_markup[] = '<option value="'.$ability_token.'">'.$ability_name.' ('.$ability_types.')</option>';
                    }
                    $ability_options_count = count($ability_options_markup);
                    $ability_options_markup = implode(PHP_EOL, $ability_options_markup);

                    ?>

                    <? /*
                    <div class="field fullsize has4cols">
                        <strong class="label">
                            Level-Up Abilities
                        </strong>
                        <?
                        $abilities_rewards = !empty($robot_data['robot_abilities_rewards']) ? $robot_data['robot_abilities_rewards'] : array();
                        for ($i = 0; $i < 4; $i++){ ?>
                            <div class="subfield">
                                <select class="select" name="robot_abilities_rewards<?= $list_token ?>[<?= $i ?>]">
                                    <? $current_value = isset($abilities_rewards[$i]) ? $abilities_rewards[$i] :  ?>
                                    <?= str_replace('value=""', 'value="" selected="selected"', $ability_options_markup) ?>
                                    <option value=""<?= empty($ability_list[$i]) ? ' selected="selected"' : '' ?>>-</option>
                                    <?
                                    foreach ($mmrpg_types_index AS $type_token => $type_info){
                                        if ($type_info['type_class'] === 'special'){ continue; }
                                        $label = $type_info['type_name'];
                                        if (!empty($ability_list[$i]) && $ability_list[$i] === $type_token){ $selected = 'selected="selected"'; }
                                        else { $selected = ''; }
                                        echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    ?>
                                </select><span></span>
                            </div>
                        <? } ?>
                    </div>
                    */ ?>

                    <div class="field fullsize has4cols multirow">
                        <strong class="label">
                            Compatible Abilities
                            <em>Excluding level-up abilities and global ones available to all robots by default</em>
                        </strong>
                        <?
                        $current_ability_list = !empty($robot_data['robot_abilities_compatible']) ? json_decode($robot_data['robot_abilities_compatible'], true) : array();
                        $current_ability_list = array_values(array_filter($current_ability_list, function($token) use($global_ability_tokens){ return !in_array($token, $global_ability_tokens); }));
                        $select_limit = max(32, count($current_ability_list));
                        $select_limit += 4 - ($select_limit % 4);
                        for ($i = 0; $i < $select_limit; $i++){
                            $current_value = isset($current_ability_list[$i]) ? $current_ability_list[$i] : '';
                            ?>
                            <div class="subfield" data-current="<?= $current_value ?>">
                                <select class="select" name="robot_abilities_compatible[<?= $i ?>]">
                                    <?= str_replace('value="'.$current_value.'"', 'value="'.$current_value.'" selected="selected"', $ability_options_markup) ?>
                                </select><span></span>
                            </div>
                            <?
                        }
                        ?>
                    </div>

                    <hr />

                    <div class="field halfsize">
                        <div class="label">
                            <strong>Robot Functions</strong>
                            <em>file path for script with robot functions like onload, ondefeat, etc.</em>
                        </div>
                        <input class="textbox" type="text" name="robot_functions" value="<?= $robot_data['robot_functions'] ?>" maxlength="64" placeholder="robots/robot.php" />
                    </div>

                    <hr />

                    <div class="options">

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Published</strong>
                                <input type="hidden" name="robot_flag_published" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="robot_flag_published" value="1" <?= !empty($robot_data['robot_flag_published']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">This robot is ready to appear on the site</p>
                        </div>

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Complete</strong>
                                <input type="hidden" name="robot_flag_complete" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="robot_flag_complete" value="1" <?= !empty($robot_data['robot_flag_complete']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">This robot is ready to be used in the game</p>
                        </div>

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Hidden</strong>
                                <input type="hidden" name="robot_flag_hidden" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="robot_flag_hidden" value="1" <?= !empty($robot_data['robot_flag_hidden']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">This robot's data should stay hidden</p>
                        </div>

                    </div>

                    <hr />

                    <div class="formfoot">

                        <div class="buttons">
                            <input class="button save" type="submit" value="Save Changes" />
                            <input class="button cancel" type="button" value="Reset Changes" onclick="javascript:window.location.href='admin.php?action=edit_robots&subaction=editor&robot_id=<?= $robot_data['robot_id'] ?>';" />
                            <? /*
                            <input class="button delete" type="button" value="Delete Robot" data-delete="robots" data-robot-id="<?= $robot_data['robot_id'] ?>" />
                            */ ?>
                        </div>

                        <? /*
                        <div class="metadata">
                            <div class="date"><strong>Created</strong>: <?= !empty($robot_data['robot_date_created']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $robot_data['robot_date_created'])): '-' ?></div>
                            <div class="date"><strong>Modified</strong>: <?= !empty($robot_data['robot_date_modified']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $robot_data['robot_date_modified'])) : '-' ?></div>
                        </div>
                        */ ?>

                    </div>

                </form>

            </div>

            <?

            $debug_robot_data = $robot_data;
            //$debug_robot_data['robot_profile_text'] = str_replace(PHP_EOL, '\\n', $debug_robot_data['robot_profile_text']);
            //$debug_robot_data['robot_credit_text'] = str_replace(PHP_EOL, '\\n', $debug_robot_data['robot_credit_text']);
            echo('<pre>$robot_data = '.(!empty($debug_robot_data) ? htmlentities(print_r($debug_robot_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');

            ?>


        <? endif; ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>