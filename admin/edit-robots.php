<? ob_start(); ?>

    <?

    /* -- Collect Dependant Indexes -- */

    // Collect an index of type colours for options
    $mmrpg_types_fields = rpg_type::get_index_fields(true);
    $mmrpg_types_index = $db->get_array_list("SELECT {$mmrpg_types_fields} FROM mmrpg_index_types ORDER BY type_order ASC", 'type_token');

    // Collect an index of battle fields for options
    $mmrpg_fields_fields = rpg_field::get_index_fields(true);
    $mmrpg_fields_index = $db->get_array_list("SELECT {$mmrpg_fields_fields} FROM mmrpg_index_fields ORDER BY field_order ASC", 'field_token');

    // Collect an index of player colours for options
    $mmrpg_players_fields = rpg_player::get_index_fields(true);
    $mmrpg_players_index = $db->get_array_list("SELECT {$mmrpg_players_fields} FROM mmrpg_index_players ORDER BY player_order ASC", 'player_token');

    // Collect an index of robot colours for options
    $mmrpg_robots_fields = rpg_robot::get_index_fields(true);
    $mmrpg_robots_index = $db->get_array_list("SELECT {$mmrpg_robots_fields} FROM mmrpg_index_robots WHERE robot_class = 'master' ORDER BY robot_order ASC", 'robot_token');


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

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['robot_id'])){

        // Collect form data for processing
        $delete_data['robot_id'] = !empty($_GET['robot_id']) && is_numeric($_GET['robot_id']) ? trim($_GET['robot_id']) : '';

        // Let's delete all of this robot's data from the database
        $db->delete('mmrpg_robots', array('robot_id' => $delete_data['robot_id']));
        $db->delete('mmrpg_saves', array('robot_id' => $delete_data['robot_id']));
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
        $search_data['robot_name'] = !empty($_GET['robot_name']) && preg_match('/[-_0-9a-z\.\*]+/i', $_GET['robot_name']) ? trim(strtolower($_GET['robot_name'])) : '';
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
        $robot_fields = rpg_robot::get_fields(true);
        $robot_data = $db->get_array("SELECT {$robot_fields} FROM mmrpg_robots WHERE robot_id = {$editor_data['robot_id']};");

        // If robot data could not be found, produce error and exit
        if (empty($robot_data)){ exit_robot_edit_action(); }

        // Collect the robot's name(s) for display
        $robot_name_display = $robot_data['robot_name'];

        // If form data has been submit for this robot, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit_robots'){

            // Collect form data from the request and parse out simple rules

            $form_data['robot_id'] = !empty($_POST['robot_id']) && is_numeric($_POST['robot_id']) ? trim($_POST['robot_id']) : 0;

            $form_data['robot_token'] = !empty($_POST['robot_token']) && preg_match('/^[-_0-9a-z\.\*]+$/i', $_POST['robot_token']) ? trim(strtolower($_POST['robot_token'])) : '';
            $form_data['robot_name'] = !empty($_POST['robot_name']) && preg_match('/^[-_0-9a-z\.\*]+$/i', $_POST['robot_name']) ? trim($_POST['robot_name']) : '';

            $form_data['robot_date_birth'] = !empty($_POST['robot_date_birth']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_POST['robot_date_birth']) ? trim($_POST['robot_date_birth']) : '';
            $form_data['robot_gender'] = !empty($_POST['robot_gender']) && preg_match('/^(male|female|other)$/', $_POST['robot_gender']) ? trim(strtolower($_POST['robot_gender'])) : '';

            $form_data['robot_colour_token'] = !empty($_POST['robot_colour_token']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['robot_colour_token']) ? trim(strtolower($_POST['robot_colour_token'])) : '';
            $form_data['robot_background_path'] = !empty($_POST['robot_background_path']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['robot_background_path']) ? trim(strtolower($_POST['robot_background_path'])) : '';
            $form_data['robot_image_path'] = !empty($_POST['robot_image_path']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['robot_image_path']) ? trim(strtolower($_POST['robot_image_path'])) : '';

            $form_data['robot_core_address'] = !empty($_POST['robot_core_address']) && preg_match('/^[-_0-9a-z\.]+@[-_0-9a-z\.]+\.[-_0-9a-z\.]+$/i', $_POST['robot_core_address']) ? trim(strtolower($_POST['robot_core_address'])) : '';
            $form_data['robot_website_address'] = !empty($_POST['robot_website_address']) && preg_match('/^(https?:\/\/)?[-_0-9a-z\.]+\.[-_0-9a-z\.]+/i', $_POST['robot_website_address']) ? trim(strtolower($_POST['robot_website_address'])) : '';
            $form_data['robot_ip_addresses'] = !empty($_POST['robot_ip_addresses']) && preg_match('/^((([0-9]{1,3}\.){3}([0-9]{1,3}){1}),?\s?)+$/i', $_POST['robot_ip_addresses']) ? trim($_POST['robot_ip_addresses']) : '';

            $form_data['robot_profile_text'] = !empty($_POST['robot_profile_text']) ? trim(strip_tags($_POST['robot_profile_text'])) : '';
            $form_data['robot_credit_line'] = !empty($_POST['robot_credit_line']) ? trim(strip_tags($_POST['robot_credit_line'])) : '';
            $form_data['robot_credit_text'] = !empty($_POST['robot_credit_text']) ? trim(strip_tags($_POST['robot_credit_text'])) : '';
            $form_data['robot_admin_text'] = !empty($_POST['robot_admin_text']) ? trim(strip_tags($_POST['robot_admin_text'])) : '';

            $form_data['robot_flag_approved'] = isset($_POST['robot_flag_approved']) && is_numeric($_POST['robot_flag_approved']) ? trim($_POST['robot_flag_approved']) : 0;
            $form_data['robot_flag_postpublic'] = isset($_POST['robot_flag_postpublic']) && is_numeric($_POST['robot_flag_postpublic']) ? trim($_POST['robot_flag_postpublic']) : 0;
            $form_data['robot_flag_postprivate'] = isset($_POST['robot_flag_postprivate']) && is_numeric($_POST['robot_flag_postprivate']) ? trim($_POST['robot_flag_postprivate']) : 0;


            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');

            // If the required USER ID field was empty, complete form failure
            if (empty($form_data['robot_id'])){
                $form_messages[] = array('error', 'Robot ID was not provided');
                $form_success = false;
            }

            // If the required USERNAME TOKEN field was empty, complete form failure
            if (empty($form_data['robot_token'])){
                $form_messages[] = array('error', 'Robotname token was not provided or was invalid');
                $form_success = false;
            }

            // If the required LOGIN USERNAME field was empty, complete form failure
            if (empty($form_data['robot_name'])){
                $form_messages[] = array('error', 'Login robotname was not provided or was invalid');
                $form_success = false;
            }

            // If there were errors, we should exit now
            if (!$form_success){ exit_robot_edit_action($form_data['robot_id']); }

            // If trying to update the GENDER but it was invalid, do not update
            if (empty($form_data['robot_gender']) && !empty($_POST['robot_gender'])){
                $form_messages[] = array('warning', 'Gender identity was invalid and will not be updated');
                unset($form_data['robot_gender']);
            }

            // If there were errors, we should exit now
            if (!$form_success){ exit_robot_edit_action($form_data['robot_id']); }

            // Update the robot name token using the new robot name string
            if (!empty($form_data['robot_name'])){
                $form_data['robot_token'] = preg_replace('/[^-a-z0-9]+/i', '', strtolower($form_data['robot_name']));
            }

            // Loop through fields to create an update string
            $update_data = $form_data;
            $update_data['robot_date_modified'] = time();
            unset($update_data['robot_id']);
            $update_results = $db->update('mmrpg_robots', $update_data, array('robot_id' => $form_data['robot_id']));

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If we made it this far, the update must have been a success
            if ($update_results !== false){ $form_messages[] = array('success', 'Robot data was updated successfully'); }
            else { $form_messages[] = array('error', 'Robot data could not be updated'); }

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
                            <option value="master"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'master' ? ' selected="selected"' : '' ?>>Robot Master</option>
                            <option value="mecha"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'mecha' ? ' selected="selected"' : '' ?>>Support Mecha</option>
                            <option value="boss"<?= !empty($search_data['robot_class']) && $search_data['robot_class'] === 'boss' ? ' selected="selected"' : '' ?>>Fortress Boss</option>
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

                <h3 class="header">Edit Robot &quot;<?= $robot_name_display ?>&quot;</h3>

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
                            <strong>Login Robotname</strong>
                            <em>avoid changing</em>
                        </div>
                        <input type="hidden" name="robot_token" value="<?= $robot_data['robot_token'] ?>" />
                        <input class="textbox" type="text" name="robot_name" value="<?= $robot_data['robot_name'] ?>" maxlength="64" />
                    </div>

                    <div class="field">
                        <strong class="label">Public Robotname</strong>
                        <input class="textbox" type="text" name="robot_name_public" value="<?= $robot_data['robot_name_public'] ?>" maxlength="64" />
                    </div>

                    <div class="field">
                        <div class="label">
                            <strong>Date of Birth</strong>
                            <em>yyyy-mm-dd</em>
                        </div>
                        <input class="textbox" type="text" name="robot_date_birth" value="<?= !empty($robot_data['robot_date_birth']) ? date('Y-m-d', $robot_data['robot_date_birth']) : '' ?>" maxlength="10" placeholder="YYYY-MM-DD" />
                    </div>

                    <div class="field">
                        <strong class="label">Gender Identity</strong>
                        <select class="select" name="robot_gender">
                            <option value="" <?= empty($robot_data['robot_gender']) ? 'selected="selected"' : '' ?>>- none -</option>
                            <option value="male" <?= $robot_data['robot_gender'] == 'male' ? 'selected="selected"' : '' ?>>Male</option>
                            <option value="female" <?= $robot_data['robot_gender'] == 'female' ? 'selected="selected"' : '' ?>>Female</option>
                            <option value="other" <?= $robot_data['robot_gender'] == 'other' ? 'selected="selected"' : '' ?>>Other</option>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">Account Type</strong>
                        <select class="select" name="role_id">
                            <?
                            foreach ($mmrpg_roles_index AS $role_id => $role_data){
                                $label = $role_data['role_name'];
                                $selected = !empty($robot_data['role_id']) && $robot_data['role_id'] == $role_id ? 'selected="selected"' : '';
                                echo('<option value="'.$role_id.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">Player Colour</strong>
                        <select class="select" name="robot_colour_token">
                            <?
                            echo('<option value=""'.(empty($robot_data['robot_colour_token']) ? 'selected="selected"' : '').'>- none -</option>');
                            foreach ($mmrpg_types_index AS $type_token => $type_data){
                                $label = $type_data['type_name'];
                                $selected = !empty($robot_data['robot_colour_token']) && $robot_data['robot_colour_token'] == $type_token ? 'selected="selected"' : '';
                                echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">Player Background</strong>
                        <select class="select" name="robot_background_path">
                            <?
                            echo('<option value=""'.(empty($robot_data['robot_background_path']) ? 'selected="selected"' : '').'>- none -</option>');
                            foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                $field_path = 'fields/'.$field_token;
                                $label = $field_data['field_name'];
                                $selected = !empty($robot_data['robot_background_path']) && $robot_data['robot_background_path'] == $field_path ? 'selected="selected"' : '';
                                echo('<option value="'.$field_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field">
                        <strong class="label">Player Avatar</strong>
                        <select class="select" name="robot_image_path">
                            <?
                            echo('<option value=""'.(empty($robot_data['robot_image_path']) ? 'selected="selected"' : '').'>- none -</option>');
                            foreach ($mmrpg_robots_index AS $robot_token => $robot_data){
                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$robot_token.'/')){ continue; }
                                $robot_path = 'robots/'.$robot_token.'/'.$robot_data['robot_image_size'];
                                $label = $robot_data['robot_number'].' '.$robot_data['robot_name'];
                                $selected = !empty($robot_data['robot_image_path']) && $robot_data['robot_image_path'] == $robot_path ? 'selected="selected"' : '';
                                echo('<option value="'.$robot_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                if (!empty($robot_data['robot_image_alts'])){
                                    $image_alts = json_decode($robot_data['robot_image_alts'], true);
                                    foreach ($image_alts AS $alt_data){
                                        $alt_token = $alt_data['token'];
                                        $alt_path = 'robots/'.$robot_token.'_'.$alt_token.'/'.$robot_data['robot_image_size'];
                                        $label = $robot_data['robot_number'].' '.$alt_data['name'];
                                        $selected = !empty($robot_data['robot_image_path']) && $robot_data['robot_image_path'] == $alt_path ? 'selected="selected"' : '';
                                        echo('<option value="'.$alt_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                }
                            }
                            foreach ($mmrpg_players_index AS $player_token => $player_data){
                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.'images/players/'.$player_token.'/')){ continue; }
                                $player_path = 'players/'.$player_token.'/'.$player_data['player_image_size'];
                                $label = $player_data['player_name'];
                                $selected = !empty($robot_data['robot_image_path']) && $robot_data['robot_image_path'] == $player_path ? 'selected="selected"' : '';
                                echo('<option value="'.$player_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field has2cols">
                        <strong class="label">Core Type(s)</strong>
                        <input class="textbox" type="text" name="robot_core" value="<?= $robot_data['robot_core'] ?>" maxlength="128" />
                        <input class="textbox" type="text" name="robot_core2" value="<?= $robot_data['robot_core2'] ?>" maxlength="128" />
                    </div>

                    <div class="field fullsize">
                        <div class="label">
                            <strong>Profile Text</strong>
                            <em>public, displayed on leaderboard page</em>
                        </div>
                        <textarea class="textarea" name="robot_profile_text" rows="10"><?= htmlentities($robot_data['robot_profile_text'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                    </div>

                    <hr />

                    <div class="field fullsize">
                        <div class="label">
                            <strong>Credit Line</strong>
                            <em>public, displayed on credits page</em>
                        </div>
                        <strong class="label"></strong>
                        <input class="textbox" type="text" name="robot_credit_line" value="<?= htmlentities($robot_data['robot_credit_line'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="32" />
                    </div>

                    <div class="field fullsize">
                        <div class="label">
                            <strong>Credit Text</strong>
                            <em>public, displayed on credits page</em>
                        </div>
                        <textarea class="textarea" name="robot_credit_text" rows="10"><?= htmlentities($robot_data['robot_credit_text'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                    </div>

                    <hr />

                    <div class="field fullsize">
                        <div class="label">
                            <strong>Moderates Notes</strong>
                            <em>private, only visible to staff</em>
                        </div>
                        <textarea class="textarea" name="robot_admin_text" rows="10"><?= htmlentities($robot_data['robot_admin_text'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                    </div>

                    <hr />

                    <div class="options">

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Approved Robot</strong>
                                <input type="hidden" name="robot_flag_approved" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="robot_flag_approved" value="1" <?= !empty($robot_data['robot_flag_approved']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">Allow robot to access their game</p>
                        </div>

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Post Public</strong>
                                <input type="hidden" name="robot_flag_postpublic" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="robot_flag_postpublic" value="1" <?= !empty($robot_data['robot_flag_postpublic']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">Allow robot to make community posts</p>
                        </div>

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Post Private</strong>
                                <input type="hidden" name="robot_flag_postprivate" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="robot_flag_postprivate" value="1" <?= !empty($robot_data['robot_flag_postprivate']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">Allow robot to send private messages</p>
                        </div>

                    </div>

                    <hr />

                    <div class="formfoot">

                        <div class="buttons">
                            <input class="button save" type="submit" value="Save Changes" />
                            <input class="button delete" type="button" value="Delete Robot" data-delete="robots" data-robot-id="<?= $robot_data['robot_id'] ?>" />
                        </div>

                        <div class="metadata">
                            <div class="date"><strong>Last Login</strong>: <?= !empty($robot_data['robot_last_login']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $robot_data['robot_last_login'])) : '-' ?></div>
                            <div class="date"><strong>Created</strong>: <?= !empty($robot_data['robot_date_created']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $robot_data['robot_date_created'])): '-' ?></div>
                            <div class="date"><strong>Modified</strong>: <?= !empty($robot_data['robot_date_modified']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $robot_data['robot_date_modified'])) : '-' ?></div>
                        </div>

                    </div>

                </form>

            </div>

            <?

            /*
            $debug_robot_data = $robot_data;
            $debug_robot_data['robot_profile_text'] = str_replace(PHP_EOL, '\\n', $debug_robot_data['robot_profile_text']);
            $debug_robot_data['robot_credit_text'] = str_replace(PHP_EOL, '\\n', $debug_robot_data['robot_credit_text']);
            echo('<pre>$robot_data = '.(!empty($debug_robot_data) ? htmlentities(print_r($debug_robot_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
            */

            ?>


        <? endif; ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>