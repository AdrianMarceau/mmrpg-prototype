<? ob_start(); ?>

    <?

    // Pre-check access permissions before continuing
    if (!rpg_user::current_user_has_permission('edit-user-accounts')){
        $form_messages[] = array('error', 'You do not have permission to edit users!');
        redirect_form_action('admin/home/');
    }

    /* -- Collect Editor Indexes -- */

    // Collect indexes relevant to this script
    $mmrpg_roles_index = cms_admin::get_roles_index();
    $mmrpg_contributors_index = cms_admin::get_contributors_index('player', 'contributor_id');

    /* -- Form Setup Actions -- */

    // Define a function for exiting a user edit action
    function exit_user_edit_action($user_id = 0){
        if (!empty($user_id)){ $location = 'admin/edit-users/editor/user_id='.$user_id; }
        else { $location = 'admin/edit-users/search/'; }
        redirect_form_action($location);
    }


    /* -- Admin Subpage Processing -- */

    // Collect or define current subaction
    $sub_action =  !empty($_GET['subaction']) ? $_GET['subaction'] : 'search';

    // Update the tab name with the page name
    $this_page_tabtitle = 'Edit Users | '.$this_page_tabtitle;

    // If we're in delete mode, we need to remove some data
    $delete_data = array();
    if ($sub_action == 'delete' && !empty($_GET['user_id'])){

        // Collect form data for processing
        $delete_data['user_id'] = !empty($_GET['user_id']) && is_numeric($_GET['user_id']) ? trim($_GET['user_id']) : '';

        // Let's delete all of this user's data from the database
        $db->delete('mmrpg_users', array('user_id' => $delete_data['user_id']));
        $db->delete('mmrpg_saves', array('user_id' => $delete_data['user_id']));
        $db->delete('mmrpg_leaderboard', array('user_id' => $delete_data['user_id']));
        $db->delete('mmrpg_posts', array('user_id' => $delete_data['user_id']));
        $db->delete('mmrpg_posts', array('post_target' => $delete_data['user_id']));
        $db->update('mmrpg_threads', array('user_id' => MMRPG_SETTINGS_GUEST_ID), array('user_id' => $delete_data['user_id']));
        $db->update('mmrpg_threads', array('thread_mod_user' => MMRPG_SETTINGS_GUEST_ID), array('thread_mod_user' => $delete_data['user_id']));
        $form_messages[] = array('success', 'The requested user has been deleted from the database');
        exit_form_action('success');

    }

    // If we're in search mode, we might need to scan for results
    $search_data = array();
    $search_query = '';
    $search_results = array();
    $search_results_count = 0;
    $search_results_limit = 200;
    if ($sub_action == 'search'){

        // Collect the sorting order and direction
        $sort_data = array('name' => 'users.user_id', 'dir' => 'desc');
        if (!empty($_GET['order'])
            && preg_match('/^([-_a-z0-9\.]+)\:(desc|asc)$/i', $_GET['order'])){
            list($r_name, $r_dir) = explode(':', trim($_GET['order']));
            $sort_data = array('name' => $r_name, 'dir' => $r_dir);
        }

        // Collect form data for processing
        $search_data['user_id'] = !empty($_GET['user_id']) && is_numeric($_GET['user_id']) ? trim($_GET['user_id']) : '';
        $search_data['role_id'] = !empty($_GET['role_id']) && is_numeric($_GET['role_id']) ? trim($_GET['role_id']) : '';
        $search_data['user_name'] = !empty($_GET['user_name']) && preg_match('/^[-_0-9a-z\.\*]+$/i', $_GET['user_name']) ? trim(strtolower($_GET['user_name'])) : '';
        $search_data['user_email'] = !empty($_GET['user_email']) && preg_match('/^[-_0-9a-z\.@\*]+$/i', $_GET['user_email']) ? trim(strtolower($_GET['user_email'])) : '';
        $search_data['user_gender'] = !empty($_GET['user_gender']) && preg_match('/^[a-z]+$/i', $_GET['user_gender']) ? trim(strtolower($_GET['user_gender'])) : '';
        $search_data['user_ip'] = !empty($_GET['user_ip']) && preg_match('/^[0-9\.\*]+$/i', $_GET['user_ip']) ? trim(strtolower($_GET['user_ip'])) : '';
        $search_data['user_flag_approved'] = isset($_GET['user_flag_approved']) && $_GET['user_flag_approved'] !== '' ? (!empty($_GET['user_flag_approved']) ? 1 : 0) : '';
        $search_data['user_flag_postpublic'] = isset($_GET['user_flag_postpublic']) && $_GET['user_flag_postpublic'] !== '' ? (!empty($_GET['user_flag_postpublic']) ? 1 : 0) : '';
        $search_data['user_flag_postprivate'] = isset($_GET['user_flag_postprivate']) && $_GET['user_flag_postprivate'] !== '' ? (!empty($_GET['user_flag_postprivate']) ? 1 : 0) : '';
        $search_data['user_flag_hasprogress'] = isset($_GET['user_flag_hasprogress']) && $_GET['user_flag_hasprogress'] !== '' ? (!empty($_GET['user_flag_hasprogress']) ? 1 : 0) : '';

        $search_data['user_date_created'] = array();
        if (!empty($_GET['user_date_created']) && is_array($_GET['user_date_created'])){ $search_data['user_date_created'] = array_filter($_GET['user_date_created']); }
        elseif (!empty($_GET['user_date_created']) && is_string($_GET['user_date_created'])){ $search_data['user_date_created'] = explode(',', trim($_GET['user_date_created'])); }
        foreach ($search_data['user_date_created'] AS $k => $v){ if (!preg_match('/^([0-9]{4})(-[0-9]{1,2})?(-[0-9]{1,2})?$/', $v)){ unset($search_data['user_date_created'][$k]); } }
        $search_data['user_date_created'] = array_filter($search_data['user_date_created']);

        $search_data['user_last_login'] = array();
        if (!empty($_GET['user_last_login']) && is_array($_GET['user_last_login'])){ $search_data['user_last_login'] = array_filter($_GET['user_last_login']); }
        elseif (!empty($_GET['user_last_login']) && is_string($_GET['user_last_login'])){ $search_data['user_last_login'] = explode(',', trim($_GET['user_last_login'])); }
        foreach ($search_data['user_last_login'] AS $k => $v){ if (!preg_match('/^([0-9]{4})(-[0-9]{1,2})?(-[0-9]{1,2})?$/', $v)){ unset($search_data['user_last_login'][$k]); } }
        $search_data['user_last_login'] = array_filter($search_data['user_last_login']);

        $search_data['user_profile_text'] = !empty($_GET['user_profile_text']) ? trim(strtolower(strip_tags($_GET['user_profile_text']))) : '';
        $search_data['user_admin_text'] = !empty($_GET['user_admin_text']) ? trim(strtolower(strip_tags($_GET['user_admin_text']))) : '';

        /* -- Collect Search Results -- */

        // Define the guest ID to exclude
        $exclude_guest_id = MMRPG_SETTINGS_GUEST_ID;

        // Define the search query to use
        $user_fields = rpg_user::get_fields(true, 'users');
        $search_query = "SELECT
            {$user_fields},
            (users.user_date_accessed - users.user_date_created) AS user_account_age,
            roles.role_id,
            roles.role_name,
            roles.role_level,
            roles.role_colour,
            leaderboard.board_points,
            (CASE WHEN leaderboard.board_points > 0 THEN 1 ELSE 0 END) AS user_flag_hasprogress
            FROM mmrpg_users AS users
            LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
            LEFT JOIN mmrpg_leaderboard AS leaderboard ON leaderboard.user_id = users.user_id
            WHERE 1=1
            AND users.user_id <> {$exclude_guest_id}
            AND users.user_name_clean <> 'guest'
            ";

        // If the user ID was provided, we can search by exact match
        if (!empty($search_data['user_id'])){
            $user_id = $search_data['user_id'];
            $search_query .= "AND users.user_id = {$user_id} ";
            $search_results_limit = false;
        }

        // If the role ID was provided, we can search by exact match
        if (!empty($search_data['role_id'])){
            $role_id = $search_data['role_id'];
            if ($role_id < 0){ $not_role_id = $role_id * -1; $search_query .= "AND users.role_id <> {$not_role_id} "; }
            else { $search_query .= "AND users.role_id = {$role_id} "; }
            $search_results_limit = false;
        }

        // Else if the user name was provided, we can use wildcards
        if (!empty($search_data['user_name'])){
            $user_name = $search_data['user_name'];
            $user_name = str_replace(array(' ', '*', '%'), '%', $user_name);
            $user_name = preg_replace('/%+/', '%', $user_name);
            $user_name = '%'.$user_name.'%';
            $search_query .= "AND (users.user_name LIKE '{$user_name}' OR users.user_name_public LIKE '{$user_name}') ";
            $search_results_limit = false;
        }

        // Else if the user email was provided, we can use wildcards
        if (!empty($search_data['user_email'])){
            $user_email = $search_data['user_email'];
            if ($user_email === '*'){
                $search_query .= "AND users.user_email_address <> '' ";
            } else {
                $user_email = str_replace(array(' ', '*', '%'), '%', $user_email);
                $user_email = preg_replace('/%+/', '%', $user_email);
                $user_email = '%'.$user_email.'%';
                $search_query .= "AND users.user_email_address LIKE '{$user_email}' ";
            }
            $search_results_limit = false;
        }

        // If the user gender was provided, we can search by exact match
        if (!empty($search_data['user_gender'])){
            $user_gender = $search_data['user_gender'];
            $search_query .= "AND users.user_gender = '{$user_gender}' ";
            $search_results_limit = false;
        }

        // Else if the user IP address was provided, we can use wildcards
        if (!empty($search_data['user_ip'])){
            $user_ip = $search_data['user_ip'];
            $user_ip = str_replace(array(' ', '*', '%'), '%', $user_ip);
            $user_ip = preg_replace('/%+/', '%', $user_ip);
            $user_ip = '%'.$user_ip.'%';
            $search_query .= "AND users.user_ip_addresses LIKE '{$user_ip}' ";
            $search_results_limit = false;
        }

        // If the user approved flag was provided
        if ($search_data['user_flag_approved'] !== ''){
            $search_query .= "AND users.user_flag_approved = {$search_data['user_flag_approved']} ";
            $search_results_limit = false;
        }

        // If the user post public flag was provided
        if ($search_data['user_flag_postpublic'] !== ''){
            $search_query .= "AND users.user_flag_postpublic = {$search_data['user_flag_postpublic']} ";
            $search_results_limit = false;
        }

        // If the user post private flag was provided
        if ($search_data['user_flag_postprivate'] !== ''){
            $search_query .= "AND users.user_flag_postprivate = {$search_data['user_flag_postprivate']} ";
            $search_results_limit = false;
        }

        // If the user has progress flag was provided
        if ($search_data['user_flag_hasprogress'] !== ''){
            if (!empty($search_data['user_flag_hasprogress'])){ $search_query .= "AND leaderboard.board_points > 0 "; }
            else { $search_query .= "AND (leaderboard.board_points = 0 OR leaderboard.board_points IS NULL) "; }
            $search_results_limit = false;
        } else {
            $search_query .= "AND leaderboard.board_points > 0 ";
        }

        // Define a quick function for parsing a given date range string
        $from_auto_fill = '1970-01-01-00-00';
        $to_auto_fill = date('Y').'-12-31-59-59';
        $parse_date = function($date_string, $auto_fill){
            $values = explode('-', $date_string);
            $auto = explode('-', $auto_fill);
            $yyyy = intval(isset($values[0]) ? $values[0] : $auto[0]);
            $mm = intval(isset($values[1]) ? $values[1] : $auto[1]);
            $dd = intval(isset($values[2]) ? $values[2] : $auto[2]);
            $h = intval($auto_fill[3]);
            $m = intval($auto_fill[4]);
            return mktime($h, $m, 0, $mm, $dd, $yyyy);
            };

        // If a user creation date range was provided
        if (!empty($search_data['user_date_created'])){
            if (count($search_data['user_date_created']) > 1){
                $from_time = $parse_date($search_data['user_date_created'][0], $from_auto_fill);
                $to_time = $parse_date($search_data['user_date_created'][1], $to_auto_fill);
                $search_query .= "AND users.user_date_created >= {$from_time} ";
                $search_query .= "AND users.user_date_created <= {$to_time} ";
            } else {
                $from_time = $parse_date($search_data['user_date_created'][0], $from_auto_fill);
                $search_query .= "AND users.user_date_created >= {$from_time} ";
            }
            $search_results_limit = false;
        }

        // If a last login date range was provided
        if (!empty($search_data['user_last_login'])){
            if (count($search_data['user_last_login']) > 1){
                $from_time = $parse_date($search_data['user_last_login'][0], $from_auto_fill);
                $to_time = $parse_date($search_data['user_last_login'][1], $to_auto_fill);
                $search_query .= "AND users.user_last_login >= {$from_time} ";
                $search_query .= "AND users.user_last_login <= {$to_time} ";
            } else {
                $from_time = $parse_date($search_data['user_last_login'][0], $from_auto_fill);
                $search_query .= "AND users.user_last_login >= {$from_time} ";
            }
            $search_results_limit = false;
        }

        // Else if the user profile text was provided, we can use wildcards
        if (!empty($search_data['user_profile_text'])){
            $user_text = $search_data['user_profile_text'];
            if ($user_text === '*'){
                $search_query .= "AND users.user_profile_text <> '' ";
            } else {
                $user_text = str_replace(array(' ', '*', '%'), '%', $user_text);
                $user_text = preg_replace('/%+/', '%', $user_text);
                $user_text = '%'.str_replace("'", "\\'", $user_text).'%';
                $search_query .= "AND users.user_profile_text LIKE '{$user_text}' ";
            }
            $search_results_limit = false;
        }

        // Else if the user admin text was provided, we can use wildcards
        if (!empty($search_data['user_admin_text'])){
            $user_text = $search_data['user_admin_text'];
            if ($user_text === '*'){
                $search_query .= "AND users.user_admin_text <> '' ";
            } else {
                $user_text = str_replace(array(' ', '*', '%'), '%', $user_text);
                $user_text = preg_replace('/%+/', '%', $user_text);
                $user_text = '%'.str_replace("'", "\\'", $user_text).'%';
                $search_query .= "AND users.user_admin_text LIKE '{$user_text}' ";
            }
            $search_results_limit = false;
        }

        // Append sorting parameters to the end of the query
        $order_by = array();
        if (!empty($sort_data) && $sort_data['name'] == 'roles.role_id'){ $order_by[] = 'roles.role_level '.strtoupper($sort_data['dir']); $order_by[] = 'roles.role_id '.strtoupper($sort_data['dir'] != 'asc' ? 'asc' : 'desc'); }
        elseif (!empty($sort_data)){ $order_by[] = $sort_data['name'].' '.strtoupper($sort_data['dir']); }
        $order_by[] = "users.user_name ASC";
        $order_by_string = implode(', ', $order_by);
        $search_query .= "ORDER BY {$order_by_string} ";

        // Impose a limit on the search results
        if (!empty($search_results_limit)){ $search_query .= "LIMIT {$search_results_limit} "; }

        // End the query now that we're done
        $search_query .= ";";

        // Collect search results from the database
        //error_log('$search_query = '.$search_query);
        $search_results = $db->get_array_list($search_query);
        $search_results_count = is_array($search_results) ? count($search_results) : 0;

        // Collect a total number from the database
        $search_results_total = $db->get_value("SELECT COUNT(user_id) AS total FROM mmrpg_users WHERE 1=1 AND user_id <> {$exclude_guest_id};", 'total');

    }

    // If we're in editor mode, we should collect user info from database
    $user_data = array();
    $editor_data = array();
    if ($sub_action == 'editor' && !empty($_GET['user_id'])){

        // Collect form data for processing
        $editor_data['user_id'] = !empty($_GET['user_id']) && is_numeric($_GET['user_id']) ? trim($_GET['user_id']) : '';


        /* -- Collect Dependant Indexes -- */

        // Collect indexes for required object types
        $mmrpg_types_index = cms_admin::get_types_index();
        $mmrpg_fields_index = cms_admin::get_fields_index();
        $mmrpg_players_index = cms_admin::get_players_index();
        $mmrpg_robots_index = cms_admin::get_robots_index();


        /* -- Collect User Data -- */

        // Collect user details from the database
        $user_fields = rpg_user::get_fields(true, 'users');
        $user_data = $db->get_array("SELECT
            {$user_fields},
            (users.user_date_accessed - users.user_date_created) AS user_account_age,
            roles.role_id,
            roles.role_name,
            roles.role_level,
            roles.role_colour,
            leaderboard.board_points
            FROM mmrpg_users AS users
            LEFT JOIN mmrpg_roles AS roles ON roles.role_id = users.role_id
            LEFT JOIN mmrpg_leaderboard AS leaderboard ON leaderboard.user_id = users.user_id
            WHERE
            users.user_id = {$editor_data['user_id']}
            ;");

        // If user data could not be found, produce error and exit
        if (empty($user_data)){ exit_user_edit_action(); }

        // Collect the user's name(s) for display
        $user_name_display = $user_data['user_name'];
        if (!empty($user_data['user_name_public']) && $user_data['user_name_public'] != $user_data['user_name']){
            $user_name_display = $user_data['user_name_public'] .' / '. $user_name_display;
        }
        $this_page_tabtitle = $user_name_display.' | '.$this_page_tabtitle;

        // If form data has been submit for this user, we should process it
        $form_data = array();
        $form_success = true;
        $form_action = !empty($_POST['action']) ? trim($_POST['action']) : '';
        if ($form_action == 'edit-users'){

            // Collect form data from the request and parse out simple rules

            $form_data['user_id'] = !empty($_POST['user_id']) && is_numeric($_POST['user_id']) ? trim($_POST['user_id']) : 0;
            $form_data['role_id'] = !empty($_POST['role_id']) && is_numeric($_POST['role_id']) ? trim($_POST['role_id']) : 0;
            $form_data['contributor_id'] = !empty($_POST['contributor_id']) && (is_numeric($_POST['contributor_id']) || $_POST['contributor_id'] === 'new') ? trim($_POST['contributor_id']) : 0;

            $form_data['user_name_clean'] = !empty($_POST['user_name_clean']) && preg_match('/^[-_0-9a-z\.\*]+$/i', $_POST['user_name_clean']) ? trim(strtolower($_POST['user_name_clean'])) : '';
            $form_data['user_name'] = !empty($_POST['user_name']) && preg_match('/^[-_0-9a-z\.\*]+$/i', $_POST['user_name']) ? trim($_POST['user_name']) : '';
            $form_data['user_name_public'] = !empty($_POST['user_name_public']) && preg_match('/^[-_0-9a-z\.\s]+$/i', $_POST['user_name_public']) ? trim($_POST['user_name_public']) : '';

            $form_data['user_date_birth'] = !empty($_POST['user_date_birth']) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $_POST['user_date_birth']) ? trim($_POST['user_date_birth']) : '';
            $form_data['user_gender'] = !empty($_POST['user_gender']) && preg_match('/^(male|female|other)$/', $_POST['user_gender']) ? trim(strtolower($_POST['user_gender'])) : '';

            $form_data['user_colour_token'] = !empty($_POST['user_colour_token']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['user_colour_token']) ? trim(strtolower($_POST['user_colour_token'])) : '';
            $form_data['user_colour_token2'] = !empty($_POST['user_colour_token2']) && preg_match('/^[-_a-z0-9]+$/i', $_POST['user_colour_token2']) ? trim(strtolower($_POST['user_colour_token2'])) : '';
            $form_data['user_background_path'] = !empty($_POST['user_background_path']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['user_background_path']) ? trim(strtolower($_POST['user_background_path'])) : '';
            $form_data['user_image_path'] = !empty($_POST['user_image_path']) && preg_match('/^[-_a-z0-9\/]+$/i', $_POST['user_image_path']) ? trim(strtolower($_POST['user_image_path'])) : '';

            $form_data['user_email_address'] = !empty($_POST['user_email_address']) && preg_match('/^[-_0-9a-z\.]+@[-_0-9a-z\.]+\.[-_0-9a-z\.]+$/i', $_POST['user_email_address']) ? trim(strtolower($_POST['user_email_address'])) : '';
            $form_data['user_website_address'] = !empty($_POST['user_website_address']) && preg_match('/^(https?:\/\/)?[-_0-9a-z\.]+\.[-_0-9a-z\.]+/i', $_POST['user_website_address']) ? trim(strtolower($_POST['user_website_address'])) : '';
            $form_data['user_ip_addresses'] = !empty($_POST['user_ip_addresses']) && preg_match('/^((([0-9]{1,3}\.){3}([0-9]{1,3}){1}),?\s?|::[0-9]{1,3},?\s?)+$/i', $_POST['user_ip_addresses']) ? trim($_POST['user_ip_addresses']) : '';

            $form_data['user_profile_text'] = !empty($_POST['user_profile_text']) ? trim(strip_tags($_POST['user_profile_text'])) : '';
            $form_data['user_credit_line'] = !empty($_POST['user_credit_line']) ? trim(strip_tags($_POST['user_credit_line'])) : '';
            $form_data['user_credit_text'] = !empty($_POST['user_credit_text']) ? trim(strip_tags($_POST['user_credit_text'])) : '';
            $form_data['user_admin_text'] = !empty($_POST['user_admin_text']) ? trim(strip_tags($_POST['user_admin_text'])) : '';

            $form_data['user_flag_approved'] = isset($_POST['user_flag_approved']) && is_numeric($_POST['user_flag_approved']) ? trim($_POST['user_flag_approved']) : 0;
            $form_data['user_flag_postpublic'] = isset($_POST['user_flag_postpublic']) && is_numeric($_POST['user_flag_postpublic']) ? trim($_POST['user_flag_postpublic']) : 0;
            $form_data['user_flag_postprivate'] = isset($_POST['user_flag_postprivate']) && is_numeric($_POST['user_flag_postprivate']) ? trim($_POST['user_flag_postprivate']) : 0;

            $user_omega_seed = !empty($_POST['user_omega_seed']) ? trim(preg_replace('/[^-_0-9a-z\.\s\,\?\!]+/i', '', $_POST['user_omega_seed'])) : '';
            $user_omega_seed = preg_replace('/\s+/', ' ', $user_omega_seed);
            if (!empty($user_omega_seed) && strlen($user_omega_seed) < 6){ $user_omega_seed = ''; }
            elseif (!empty($user_omega_seed) && strlen($user_omega_seed) > 32){ $user_omega_seed = ''; }

            $user_password_new = !empty($_POST['user_password_new']) ? trim($_POST['user_password_new']) : '';
            $user_password_new2 = !empty($_POST['user_password_new2']) ? trim($_POST['user_password_new2']) : '';

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$_POST = '.print_r($_POST, true).'</pre>');

            // If the required USER ID field was empty, complete form failure
            if (empty($form_data['user_id'])){
                $form_messages[] = array('error', 'User ID was not provided');
                $form_success = false;
            }

            // If the required ACCOUNT TYPE field was empty, complete form failure
            if (empty($form_data['role_id'])){
                $form_messages[] = array('error', 'Account Type was not provided');
                $form_success = false;
            }

            // If the required USERNAME TOKEN field was empty, complete form failure
            if (empty($form_data['user_name_clean'])){
                $form_messages[] = array('error', 'Username token was not provided or was invalid');
                $form_success = false;
            }

            // If the required LOGIN USERNAME field was empty, complete form failure
            if (empty($form_data['user_name'])){
                $form_messages[] = array('error', 'Login username was not provided or was invalid');
                $form_success = false;
            }

            // If there were errors, we should exit now
            if (!$form_success){ exit_user_edit_action($form_data['user_id']); }

            // If trying to update the PUBLIC USER NAME but it was invalid, do not update
            if (empty($form_data['user_name_public']) && !empty($_POST['user_name_public'])){
                $form_messages[] = array('warning', 'Public username was invalid and will not be updated');
                unset($form_data['user_name_public']);
            }

            // If trying to update the DATE OF BIRTH but it was invalid, do not update
            if (empty($form_data['user_date_birth']) && !empty($_POST['user_date_birth'])){
                $form_messages[] = array('warning', 'Date of birth was invalid and will not be updated');
                unset($form_data['user_date_birth']);
            }

            // If trying to update the GENDER but it was invalid, do not update
            if (empty($form_data['user_gender']) && !empty($_POST['user_gender'])){
                $form_messages[] = array('warning', 'Gender identity was invalid and will not be updated');
                unset($form_data['user_gender']);
            }

            // If trying to update the PLAYER COLOUR but it was invalid, do not update
            if (empty($form_data['user_colour_token']) && !empty($_POST['user_colour_token'])){
                $form_messages[] = array('warning', 'Player colour was invalid and will not be updated');
                unset($form_data['user_colour_token']);
            }

            // If trying to update the PLAYER COLOUR 2 but it was invalid, do not update
            if (empty($form_data['user_colour_token2']) && !empty($_POST['user_colour_token2'])){
                $form_messages[] = array('warning', 'Secondary player colour was invalid and will not be updated');
                unset($form_data['user_colour_token2']);
            }

            // If trying to update the PLAYER BACKGROUND but it was invalid, do not update
            if (empty($form_data['user_background_path']) && !empty($_POST['user_background_path'])){
                $form_messages[] = array('warning', 'Player background was invalid and will not be updated');
                unset($form_data['user_background_path']);
            }

            // If trying to update the PLAYER AVATAR but it was invalid, do not update
            if (empty($form_data['user_image_path']) && !empty($_POST['user_image_path'])){
                $form_messages[] = array('warning', 'Player avatar was invalid and will not be updated');
                unset($form_data['user_image_path']);
            }

            // If trying to update the EMAIL ADDRESS but it was invalid, do not update
            if (empty($form_data['user_email_address']) && !empty($_POST['user_email_address'])){
                $form_messages[] = array('warning', 'Email address was invalid and will not be updated');
                unset($form_data['user_email_address']);
            }

            // If trying to update the WEBSITE ADDRESS but it was invalid, do not update
            if (empty($form_data['user_website_address']) && !empty($_POST['user_website_address'])){
                $form_messages[] = array('warning', 'Website address was invalid and will not be updated');
                unset($form_data['user_website_address']);
            }

            // If trying to update the IP ADDRESSES but it was invalid, do not update
            if (empty($form_data['user_ip_addresses']) && !empty($_POST['user_ip_addresses'])){
                $form_messages[] = array('warning', 'IP addresses were invalid and will not be updated');
                unset($form_data['user_ip_addresses']);
            }

            // If trying to update the OMEGA STRING but it was invalid, do not update
            if (empty($user_omega_seed) && !empty($_POST['user_omega_seed'])){
                $form_messages[] = array('warning', 'Omega sequence input was invalid and will not be updated');
            }
            // Otherwise, we should generate a new omega sequence using the new string
            elseif (!empty($user_omega_seed)){

                // Generate the new omega sequence from the seed value and update
                $user_omega_sequence = md5(MMRPG_SETTINGS_OMEGA_SEED.$user_omega_seed);
                $form_data['user_omega'] = $user_omega_sequence;
                $form_messages[] = array('alert', 'The omega sequence was regenerated successfully');

            }


            // Validate a password change request with special care
            if (!empty($user_password_new)){

                // Default password success to true
                $update_password = true;

                // If any issues with new password, password change failure
                if ($user_password_new != $user_password_new2){
                    $form_messages[] = array('warning', 'The passwords were not the same and will not be updated');
                    $update_password = false;
                } elseif ($user_password_new == $user_password_new2){
                    if (strlen($user_password_new) < 6){
                        $form_messages[] = array('warning', 'The new password was too short and will not be updated');
                        $update_password = false;
                    } elseif (strlen($user_password_new) > 32){
                        $form_messages[] = array('warning', 'The new password was too long and will not be updated');
                        $update_password = false;
                    }
                }

                // If password update successful, we can save details
                if ($update_password){
                    $form_data['user_password_encoded'] = md5(MMRPG_SETTINGS_PASSWORD_SALT.$user_password_new);
                    $form_messages[] = array('alert', 'The account password was updated successfully');
                }

            }

            // If there were errors, we should exit now
            if (!$form_success){ exit_user_edit_action($form_data['user_id']); }

            // Update the user name token using the new user name string
            if (!empty($form_data['user_name'])){
                $form_data['user_name_clean'] = preg_replace('/[^-a-z0-9]+/i', '', strtolower($form_data['user_name']));
            }

            // Convert date values to database-compatible formats
            if (!empty($form_data['user_date_birth'])){
                list($yyyy, $mm, $dd) = explode('-', $form_data['user_date_birth']);
                $form_data['user_date_birth'] = mktime(0, 0, 0, $mm, $dd, $yyyy);
            }

            // Update the website URL with a prefix if not already there
            if (!empty($form_data['user_website_address'])){
                $website = $form_data['user_website_address'];
                $website = preg_replace('/^https?:\/\//i', '', trim($website));
                if (!strstr($website, '/')){ $website .= '/'; }
                $website = 'http://'.$website;
                $form_data['user_website_address'] = $website;
            }

            // If a NEW contributor ID was requested, create the temp row and collect the ID
            if ($form_data['contributor_id'] === 'new'){
                $db->insert('mmrpg_users_contributors', array('user_name_clean' => $form_data['user_name_clean']));
                $form_data['contributor_id'] = $db->get_value("SELECT contributor_id FROM mmrpg_users_contributors WHERE user_name_clean = '{$form_data['user_name_clean']}';", 'contributor_id');
            }

            // Loop through fields to create an update string
            $update_data = $form_data;
            $update_data['user_date_modified'] = time();
            unset($update_data['user_id']);
            $update_results = $db->update('mmrpg_users', $update_data, array('user_id' => $form_data['user_id']));

            // If this user has a non-zero contributor ID assigned, export relevant data to the other table
            if (!empty($form_data['contributor_id'])){
                $temp_export_data = array();
                $temp_export_fields = rpg_user::get_contributor_index_fields(false);
                foreach ($temp_export_fields AS $f){ if ($f === 'contributor_id' || !isset($update_data[$f])){ continue; } else { $temp_export_data[$f] = $update_data[$f]; } }
                $temp_export_data['user_date_created'] = $db->get_value("SELECT user_date_created FROM mmrpg_users WHERE user_id = {$form_data['user_id']};", 'user_date_created');
                $temp_export_data['contributor_flag_showcredits'] = !empty($_REQUEST['contributor_flag_showcredits']) ? 1 : 0;
                $db->update('mmrpg_users_contributors', $temp_export_data, array('contributor_id' => $form_data['contributor_id']));
            }

            // If this user has fallen below the threshold for admin permissions, delete their permissions row
            if ($mmrpg_roles_index[$form_data['role_id']]['role_level'] <= 3){
                $db->query("DELETE FROM mmrpg_users_permissions WHERE user_id = {$form_data['user_id']};");
            }
            // Otherwise, if this user had access permissions provided, make sure we update that table as well
            elseif (!empty($_POST['update_access_permissions']) && $_POST['update_access_permissions'] === 'true'){
                $allowed_admin_permissions = rpg_user::current_user_permission_tokens();
                $raw_access_permissions = !empty($_POST['user_access_permissions']) && is_array($_POST['user_access_permissions']) ? $_POST['user_access_permissions'] : array();
                //error_log('$raw_access_permissions = '.print_r($raw_access_permissions, true));
                if (!empty($raw_access_permissions)){
                    $new_access_permissions = array();
                    foreach ($raw_access_permissions AS $key => $value){
                        $key_frags = explode('_', $key);
                        $last_frag = array_pop($key_frags);
                        if (!in_array($last_frag, $allowed_admin_permissions)){ continue; }
                        $new_access_permissions[$key] = intval($value);
                    }
                    //error_log('$new_access_permissions = '.print_r($new_access_permissions, true));
                    $perm_data_exists = $db->get_value("SELECT user_id FROM mmrpg_users_permissions WHERE user_id = {$form_data['user_id']};", 'user_id');
                    if (!empty($perm_data_exists)){ $db->update('mmrpg_users_permissions', $new_access_permissions, array('user_id' => $form_data['user_id'])); }
                    else { $db->insert('mmrpg_users_permissions', array_merge($new_access_permissions, array('user_id' => $form_data['user_id']))); }
                }
            }

            // DEBUG
            //$form_messages[] = array('alert', '<pre>$form_data = '.print_r($form_data, true).'</pre>');

            // If we made it this far, the update must have been a success
            if ($update_results !== false){ $form_messages[] = array('success', 'User details were updated successfully'); }
            else { $form_messages[] = array('error', 'User details could not be updated'); }

            // We're done processing the form, we can exit
            exit_user_edit_action($form_data['user_id']);

            //echo('<pre>$form_action = '.print_r($form_action, true).'</pre>');
            //echo('<pre>$_POST = '.print_r($_POST, true).'</pre>');
            //exit();


        }

    }


    ?>

    <div class="breadcrumb">
        <a href="admin/">Admin Panel</a>
        &raquo; <a href="admin/edit-users/">Edit Users</a>
        <? if ($sub_action == 'editor' && !empty($user_data)): ?>
            &raquo; <a href="admin/edit-users/editor/user_id=<?= $user_data['user_id'] ?>"><?= $user_name_display ?></a>
        <? endif; ?>
    </div>

    <?= !empty($this_error_markup) ? '<div style="margin: 0 auto 20px">'.$this_error_markup.'</div>' : '' ?>

    <div class="adminform edit-users" data-baseurl="admin/edit-users/" data-object="user" data-xobject="users">

        <? if ($sub_action == 'search'): ?>

            <!-- SEARCH FORM -->

            <div class="search">

                <h3 class="header">Search Users</h3>

                <? print_form_messages() ?>

                <form class="form" method="get">

                    <? /* <input type="hidden" name="action" value="edit-users" /> */ ?>
                    <input type="hidden" name="subaction" value="search" />

                    <div class="field foursize">
                        <strong class="label">By ID</strong>
                        <input class="textbox" type="text" name="user_id" value="<?= !empty($search_data['user_id']) ? $search_data['user_id'] : '' ?>" />
                    </div>

                    <div class="field foursize">
                        <strong class="label">By Name</strong>
                        <input class="textbox" type="text" name="user_name" value="<?= !empty($search_data['user_name']) ? htmlentities($search_data['user_name'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field foursize">
                        <strong class="label">By Email</strong>
                        <input class="textbox" type="text" name="user_email" value="<?= !empty($search_data['user_email']) ? htmlentities($search_data['user_email'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field foursize">
                        <strong class="label">By Role</strong>
                        <select class="select" name="role_id">
                            <option value=""></option>
                            <option value="-3"<?= !empty($search_data['role_id']) && $search_data['role_id'] == -3 ? 'selected="selected"' : '' ?>>Any Staff-Level Role</option>
                            <?
                            $pseudo_roles_index = $mmrpg_roles_index;
                            $pseudo_roles_index = array_reverse($pseudo_roles_index, true);
                            foreach ($pseudo_roles_index AS $role_id => $role_data){
                                $label = $role_data['role_name'];
                                $selected = !empty($search_data['role_id']) && $search_data['role_id'] == $role_id ? 'selected="selected"' : '';
                                echo('<option value="'.$role_id.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                            }
                            ?>
                        </select><span></span>
                    </div>

                    <div class="field foursize">
                        <strong class="label">By Gender</strong>
                        <select class="select" name="user_gender">
                            <option value=""></option>
                            <option value="none" <?= $search_data['user_gender'] == 'none' ? 'selected="selected"' : '' ?>>None</option>
                            <option value="other" <?= $search_data['user_gender'] == 'other' ? 'selected="selected"' : '' ?>>Other</option>
                            <option value="male" <?= $search_data['user_gender'] == 'male' ? 'selected="selected"' : '' ?>>Male</option>
                            <option value="female" <?= $search_data['user_gender'] == 'female' ? 'selected="selected"' : '' ?>>Female</option>
                        </select><span></span>
                    </div>

                    <div class="field foursize">
                        <strong class="label">By IP Address</strong>
                        <input class="textbox" type="text" name="user_ip" value="<?= !empty($search_data['user_ip']) ? htmlentities($search_data['user_ip'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field foursize has2cols has-date-range">
                        <strong class="label">By Creation Date</strong>
                        <input class="textbox" type="text" name="user_date_created[]" value="<?= !empty($search_data['user_date_created'][0]) ? htmlentities($search_data['user_date_created'][0], ENT_QUOTES, 'UTF-8', true) : '' ?>" placeholder="YYYY-MM-DD" maxlength="10" />
                        <span class="arrow">&raquo;</span>
                        <input class="textbox" type="text" name="user_date_created[]" value="<?= !empty($search_data['user_date_created'][1]) ? htmlentities($search_data['user_date_created'][1], ENT_QUOTES, 'UTF-8', true) : '' ?>" placeholder="YYYY-MM-DD" maxlength="10" />
                    </div>

                    <div class="field foursize has2cols has-date-range">
                        <strong class="label">By Last Login</strong>
                        <input class="textbox" type="text" name="user_last_login[]" value="<?= !empty($search_data['user_last_login'][0]) ? htmlentities($search_data['user_last_login'][0], ENT_QUOTES, 'UTF-8', true) : '' ?>" placeholder="YYYY-MM-DD" maxlength="10" />
                        <span class="arrow">&raquo;</span>
                        <input class="textbox" type="text" name="user_last_login[]" value="<?= !empty($search_data['user_last_login'][1]) ? htmlentities($search_data['user_last_login'][1], ENT_QUOTES, 'UTF-8', true) : '' ?>" placeholder="YYYY-MM-DD" maxlength="10" />
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Profile Text</strong>
                        <input class="textbox" type="text" name="user_profile_text" value="<?= !empty($search_data['user_profile_text']) ? htmlentities($search_data['user_profile_text'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field halfsize">
                        <strong class="label">By Moderator Notes</strong>
                        <input class="textbox" type="text" name="user_admin_text" value="<?= !empty($search_data['user_admin_text']) ? htmlentities($search_data['user_admin_text'], ENT_QUOTES, 'UTF-8', true) : '' ?>" />
                    </div>

                    <div class="field halfsize has4cols flags">
                    <?
                    $flag_names = array(
                        'approved' => array('icon' => 'fas fa-check-square', 'yes' => 'Approved', 'no' => 'Not Approved'),
                        'hasprogress' => array('icon' => 'fas fa-tasks', 'yes' => 'Has Progress', 'no' => 'No Progress', 'label' => 'Game Progress'),
                        'postpublic' => array('icon' => 'fas fa-comment', 'yes' => 'Allowed', 'no' => 'Not Allowed', 'label' => 'Public Posts'),
                        'postprivate' => array('icon' => 'fas fa-envelope', 'yes' => 'Allowed', 'no' => 'Not Allowed', 'label' => 'Private Messages')
                        );
                    foreach ($flag_names AS $flag_token => $flag_info){
                        $flag_name = 'user_flag_'.$flag_token;
                        $flag_label = isset($flag_info['label']) ? $flag_info['label'] : ucfirst($flag_token);
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
                        <input class="button" type="reset" value="Reset" onclick="javascript:window.location.href='admin/edit-users/';" />
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
                            <col class="email" width="" />
                            <col class="points" width="120" />
                            <col class="role" width="110" />
                            <col class="date created" width="90" />
                            <col class="date last-login" width="90" />
                            <col class="date account-age" width="90" />
                            <col class="actions" width="100" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="id"><?= cms_admin::get_sort_link('users.user_id', 'ID') ?></th>
                                <th class="name"><?= cms_admin::get_sort_link('users.user_name_clean', 'Name') ?></th>
                                <th class="email"><?= cms_admin::get_sort_link('users.user_email_address', 'Email') ?></th>
                                <th class="points"><?= cms_admin::get_sort_link('leaderboard.board_points', 'Points') ?></th>
                                <th class="role"><?= cms_admin::get_sort_link('roles.role_id', 'Role') ?></th>
                                <th class="date created"><?= cms_admin::get_sort_link('users.user_date_created', 'Created') ?></th>
                                <th class="date last-login"><?= cms_admin::get_sort_link('users.user_last_login', 'Last Login') ?></th>
                                <th class="date account-age"><?= cms_admin::get_sort_link('user_account_age', 'Active For') ?></th>
                                <th class="actions">Actions</th>
                            </tr>
                            <tr>
                                <th class="head id"></th>
                                <th class="head name"></th>
                                <th class="head email"></th>
                                <th class="head points"></th>
                                <th class="head role"></th>
                                <th class="head date created"></th>
                                <th class="head date last-login"></th>
                                <th class="head date account-age"></th>
                                <th class="head count"><?= cms_admin::get_totals_markup() ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td class="foot id"></td>
                                <td class="foot name"></td>
                                <td class="foot email"></td>
                                <td class="foot points"></td>
                                <td class="foot role"></td>
                                <td class="foot date created"></td>
                                <td class="foot date last-login"></td>
                                <td class="foot date account-age"></td>
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
                            foreach ($search_results AS $key => $user_data){

                                $user_id = $user_data['user_id'];
                                $user_name = $user_data['user_name_clean'];
                                $user_email = !empty($user_data['user_email_address']) ? $user_data['user_email_address'] : '-';
                                $user_role = $user_data['role_name'];
                                $user_role_span = '<span class="type_span type_'.$user_data['role_colour'].'"><i class="fas fa-user"></i> '.$user_data['role_name'].'</span>';
                                $user_created = !empty($user_data['user_date_created']) ? date('Y-m-d', $user_data['user_date_created']) : '-';
                                $user_modified = !empty($user_data['user_date_modified']) ? date('Y-m-d', $user_data['user_date_modified']) : '-';
                                $user_lastlogin = !empty($user_data['user_last_login']) ? date('Y-m-d', $user_data['user_last_login']) : '-';
                                $user_board_points = !empty($user_data['board_points']) ? number_format($user_data['board_points'], 0, '.', ',') : 0;
                                $user_account_age = !empty($user_data['user_account_age']) ? $user_data['user_account_age'] : '-';

                                if ($user_account_age > 0){
                                    $date1 = new DateTime();
                                    $date2 = new DateTime();
                                    $date1->setTimestamp($user_data['user_date_created']);
                                    $date2->setTimestamp($user_data['user_date_accessed']);
                                    $interval = $date1->diff($date2);
                                    $years = $interval->y;
                                    $months = $interval->m;
                                    $days = $interval->d;
                                    $print_user_account_age = array();
                                    if (!empty($years)){ $print_user_account_age[] = $years.'y'; }
                                    if (!empty($months)){ $print_user_account_age[] = $months.'m'; }
                                    if (!empty($days)){ $print_user_account_age[] = $days.'d'; }
                                    if (empty($print_user_account_age)){ $print_user_account_age[] = '1d'; }
                                    $print_user_account_age = implode('-', $print_user_account_age);
                                } else {
                                    $print_user_account_age = '-';
                                }

                                // Collect the user's name(s) for display
                                $user_name_display = $user_data['user_name'];
                                if (!empty($user_data['user_name_public']) && $user_data['user_name_public'] != $user_data['user_name']){
                                    $user_name_display = $user_name_display .' / '. $user_data['user_name_public'];
                                }

                                $user_edit = 'admin/edit-users/editor/user_id='.$user_id;
                                $user_view = !empty($user_data['board_points']) ? 'leaderboard/'.$user_data['user_name_clean'].'/' : false;

                                $user_actions = '';
                                $user_actions .= '<a class="link edit" href="'.$user_edit.'"><span>edit</span></a>';
                                //$user_actions .= '<a class="link delete" data-delete="users" data-user-id="'.$user_id.'"><span>delete</span></a>';
                                if (!empty($user_view)){ $user_actions .= '<a class="link view" href="'.$user_view.'" target="_blank"><span>view</span></a>'; }

                                $user_name = '<a class="link" href="'.$user_edit.'">'.$user_name_display.'</a>';

                                echo '<tr>'.PHP_EOL;
                                    echo '<td class="id"><div>'.$user_id.'</div></td>'.PHP_EOL;
                                    echo '<td class="name"><div class="wrap">'.$user_name.'</div></td>'.PHP_EOL;
                                    echo '<td class="email"><div class="wrap">'.$user_email.'</div></td>'.PHP_EOL;
                                    echo '<td class="points"><div class="wrap">'.$user_board_points.'</div></td>'.PHP_EOL;
                                    echo '<td class="role"><div class="wrap">'.$user_role_span.'</div></td>'.PHP_EOL;
                                    echo '<td class="date created"><div>'.$user_created.'</div></td>'.PHP_EOL;
                                    //echo '<td class="date modified"><div>'.$user_modified.'</div></td>'.PHP_EOL;
                                    echo '<td class="date last-login"><div>'.$user_lastlogin.'</div></td>'.PHP_EOL;
                                    echo '<td class="date account-age"><div>'.$print_user_account_age.'</div></td>'.PHP_EOL;
                                    echo '<td class="actions"><div>'.$user_actions.'</div></td>'.PHP_EOL;
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

        <? if ($sub_action == 'editor' && !empty($_GET['user_id'])): ?>

            <!-- EDITOR FORM -->

            <div class="editor">

                <h3 class="header">
                    <span class="title">Edit User &quot;<?= $user_name_display ?>&quot;</span>
                    <?
                    // If the page is published, generate and display a preview link
                    if (!empty($user_data['board_points'])){
                        $preview_link = 'leaderboard/'.$user_data['user_name_clean'].'/';
                        echo '<a class="view" href="'.$preview_link.'" target="_blank">View <i class="fas fa-external-link-square-alt"></i></a>'.PHP_EOL;
                    }
                    ?>
                </h3>

                <? print_form_messages() ?>

                <?
                // Check to see if we're allowed to edit this user's role
                $allow_edit_role = false;
                if ($user_data['role_level'] <= $this_admininfo['role_level']){
                    $allow_edit_role = true;
                }
                // Check to see if we're allowed to edit this user's permissions
                $allow_edit_permissions = false;
                if (rpg_user::current_user_has_permission('edit-user-permissions')
                    && $user_data['user_id'] !== rpg_user::get_current_userid()
                    && $user_data['role_level'] < $this_admininfo['role_level']
                    && $user_data['role_level'] > 3){
                    $allow_edit_permissions = true;
                }
                ?>

                <div class="editor-tabs" data-tabgroup="user">
                    <a class="tab active" data-tab="basic">Basic</a><span></span>
                    <a class="tab" data-tab="profile">Profile</a><span></span>
                    <a class="tab" data-tab="credits">Credits</a><span></span>
                    <? if ($allow_edit_permissions){ ?>
                        <a class="tab" data-tab="access">Access</a><span></span>
                    <? } ?>
                    <a class="tab" data-tab="notes">Notes</a><span></span>
                </div>

                <form class="form" method="post">

                    <input type="hidden" name="action" value="edit-users" />
                    <input type="hidden" name="subaction" value="editor" />

                    <div class="editor-panels" data-tabgroup="user">

                        <div class="panel active" data-tab="basic">

                            <div class="field">
                                <strong class="label">User ID</strong>
                                <input type="hidden" name="user_id" value="<?= $user_data['user_id'] ?>" />
                                <input class="textbox" type="text" name="user_id" value="<?= $user_data['user_id'] ?>" disabled="disabled" />
                            </div>

                            <div class="field">
                                <strong class="label">Account Type</strong>
                                <input type="hidden" name="role_id" value="<?= $user_data['role_id'] ?>" />
                                <select class="select" name="role_id" <?= !$allow_edit_role ? 'disabled="disabled"' : '' ?>>
                                    <?
                                    foreach ($mmrpg_roles_index AS $role_id => $role_data){
                                        $label = $role_data['role_name'];
                                        $selected = !empty($user_data['role_id']) && $user_data['role_id'] == $role_id ? ' selected="selected"' : '';
                                        $disabled = $role_data['role_level'] > $this_admininfo['role_level'] ? ' disabled="disabled"' : '';
                                        echo('<option value="'.$role_id.'"'.$selected.$disabled.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    ?>
                                </select><span></span>
                            </div>

                            <div class="field">
                                <div class="label">
                                    <strong>Login Username</strong>
                                    <em>avoid changing</em>
                                </div>
                                <input type="hidden" name="user_name_clean" value="<?= $user_data['user_name_clean'] ?>" />
                                <input class="textbox" type="text" name="user_name" value="<?= $user_data['user_name'] ?>" maxlength="64" />
                            </div>

                            <div class="field">
                                <strong class="label">Public Username</strong>
                                <input class="textbox" type="text" name="user_name_public" value="<?= $user_data['user_name_public'] ?>" maxlength="64" />
                            </div>

                            <div class="field">
                                <strong class="label">Gender Identity</strong>
                                <select class="select" name="user_gender">
                                    <option value="" <?= empty($user_data['user_gender']) ? 'selected="selected"' : '' ?>>- none -</option>
                                    <option value="male" <?= $user_data['user_gender'] == 'male' ? 'selected="selected"' : '' ?>>Male</option>
                                    <option value="female" <?= $user_data['user_gender'] == 'female' ? 'selected="selected"' : '' ?>>Female</option>
                                    <option value="other" <?= $user_data['user_gender'] == 'other' ? 'selected="selected"' : '' ?>>Other</option>
                                </select><span></span>
                            </div>

                            <div class="field">
                                <div class="label">
                                    <strong>Date of Birth</strong>
                                    <em>yyyy-mm-dd</em>
                                </div>
                                <input class="textbox" type="text" name="user_date_birth" value="<?= !empty($user_data['user_date_birth']) ? date('Y-m-d', $user_data['user_date_birth']) : '' ?>" maxlength="10" placeholder="YYYY-MM-DD" />
                            </div>

                            <div class="field">
                                <strong class="label">Email Address</strong>
                                <input class="textbox" type="text" name="user_email_address" value="<?= $user_data['user_email_address'] ?>" maxlength="128" />
                            </div>

                            <div class="field">
                                <strong class="label">Website Address</strong>
                                <input class="textbox" type="text" name="user_website_address" value="<?= $user_data['user_website_address'] ?>" maxlength="128" />
                            </div>

                            <div class="field fullsize">
                                <div class="label">
                                    <strong>IPv4 Address</strong>
                                    <em>0.0.0.0</em>
                                </div>
                                <?
                                // Only keep the last ten IP addresses to prevent var overflow
                                $user_ip_addresses = $user_data['user_ip_addresses'];
                                $user_ip_addresses = !empty($user_ip_addresses) ? explode(',', $user_ip_addresses) : array($user_ip_addresses);
                                $user_ip_addresses = !empty($user_ip_addresses) ? array_map('trim', $user_ip_addresses) : array();
                                if (count($user_ip_addresses) > 10){ $user_ip_addresses = array_slice($user_ip_addresses, -10, 10); }
                                $print_user_ip_addresses = implode(', ', $user_ip_addresses);
                                $save_user_ip_addresses = implode(',', $user_ip_addresses);
                                ?>
                                <input class="hidden" type="hidden" name="user_ip_addresses" value="<?= $save_user_ip_addresses ?>" maxlength="256" />
                                <textarea class="textarea" name="user_ip_addresses" rows="3" maxlength="256" disabled="disabled"><?= htmlentities($print_user_ip_addresses, ENT_QUOTES, 'UTF-8', true) ?></textarea>
                            </div>

                            <hr />

                            <div class="field">
                                <div class="label">
                                    <strong>Change Password</strong>
                                    <em>6 - 32 characters</em>
                                </div>
                                <input class="textbox" type="password" name="user_password_new" value="" maxlength="32" />
                            </div>

                            <div class="field">
                                <strong class="label">Retype Password</strong>
                                <input class="textbox" type="password" name="user_password_new2" value="" maxlength="32" />
                            </div>

                        </div>

                        <div class="panel" data-tab="profile">

                            <div class="field halfsize">
                                <strong class="label">Player Avatar</strong>
                                <select class="select" name="user_image_path">
                                    <?
                                    echo('<option value=""'.(empty($user_data['user_image_path']) ? 'selected="selected"' : '').'>- none -</option>');
                                    foreach ($mmrpg_robots_index AS $robot_token => $robot_data){
                                        if (!rpg_game::sprite_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$robot_token.'/')){ continue; }
                                        $robot_path = 'robots/'.$robot_token.'/'.$robot_data['robot_image_size'];
                                        $label = $robot_data['robot_number'].' '.$robot_data['robot_name'];
                                        $selected = !empty($user_data['user_image_path']) && $user_data['user_image_path'] == $robot_path ? 'selected="selected"' : '';
                                        echo('<option value="'.$robot_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                        if (!empty($robot_data['robot_image_alts'])){
                                            $image_alts = json_decode($robot_data['robot_image_alts'], true);
                                            foreach ($image_alts AS $alt_data){
                                                $alt_token = $alt_data['token'];
                                                $alt_path = 'robots/'.$robot_token.'_'.$alt_token.'/'.$robot_data['robot_image_size'];
                                                $label = $robot_data['robot_number'].' '.$alt_data['name'];
                                                $selected = !empty($user_data['user_image_path']) && $user_data['user_image_path'] == $alt_path ? 'selected="selected"' : '';
                                                echo('<option value="'.$alt_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                            }
                                        }
                                    }
                                    foreach ($mmrpg_players_index AS $player_token => $player_data){
                                        if (!rpg_game::sprite_exists(MMRPG_CONFIG_ROOTDIR.'images/players/'.$player_token.'/')){ continue; }
                                        $player_path = 'players/'.$player_token.'/'.$player_data['player_image_size'];
                                        $label = $player_data['player_name'];
                                        $selected = !empty($user_data['user_image_path']) && $user_data['user_image_path'] == $player_path ? 'selected="selected"' : '';
                                        echo('<option value="'.$player_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    ?>
                                </select><span></span>
                            </div>

                            <div class="field halfsize">
                                <strong class="label">Player Background</strong>
                                <select class="select" name="user_background_path">
                                    <?
                                    echo('<option value=""'.(empty($user_data['user_background_path']) ? 'selected="selected"' : '').'>- none -</option>');
                                    foreach ($mmrpg_fields_index AS $field_token => $field_data){
                                        $field_path = 'fields/'.$field_token;
                                        $label = $field_data['field_name'];
                                        $selected = !empty($user_data['user_background_path']) && $user_data['user_background_path'] == $field_path ? 'selected="selected"' : '';
                                        echo('<option value="'.$field_path.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    ?>
                                </select><span></span>
                            </div>

                            <div class="field halfsize">
                                <strong class="label">Player Colour #1</strong>
                                <select class="select" name="user_colour_token">
                                    <?
                                    echo('<option value=""'.(empty($user_data['user_colour_token']) ? 'selected="selected"' : '').'>- none -</option>');
                                    foreach ($mmrpg_types_index AS $type_token => $type_data){
                                        $label = $type_data['type_name'];
                                        $selected = !empty($user_data['user_colour_token']) && $user_data['user_colour_token'] == $type_token ? 'selected="selected"' : '';
                                        echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    ?>
                                </select><span></span>
                            </div>

                            <div class="field halfsize">
                                <strong class="label">Player Colour #2</strong>
                                <select class="select" name="user_colour_token2">
                                    <?
                                    echo('<option value=""'.(empty($user_data['user_colour_token2']) ? 'selected="selected"' : '').'>- none -</option>');
                                    foreach ($mmrpg_types_index AS $type_token => $type_data){
                                        $label = $type_data['type_name'];
                                        $selected = !empty($user_data['user_colour_token2']) && $user_data['user_colour_token2'] == $type_token ? 'selected="selected"' : '';
                                        echo('<option value="'.$type_token.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    ?>
                                </select><span></span>
                            </div>

                            <hr />

                            <div class="field fullsize">
                                <div class="label">
                                    <strong>Profile Text</strong>
                                    <em>public, displayed on leaderboard page</em>
                                </div>
                                <textarea class="textarea" name="user_profile_text" rows="20"><?= htmlentities($user_data['user_profile_text'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                            </div>

                            <hr />

                            <div class="field">
                                <div class="label">
                                    <strong>Omega Sequence</strong>
                                    <em>procedural generation string</em>
                                </div>
                                <input type="hidden" name="user_omega" value="<?= $user_data['user_omega'] ?>" />
                                <input class="textbox" type="text" name="user_omega" value="<?= $user_data['user_omega'] ?>" disabled="disabled" maxlength="32" />
                            </div>

                            <div class="field">
                                <div class="label">
                                    <strong>Regenerate Sequence</strong>
                                    <em>enter new seed value</em>
                                </div>
                                <input class="textbox" type="text" name="user_omega_seed" value="" maxlength="32" />
                            </div>

                        </div>

                        <div class="panel" data-tab="credits">

                            <div class="field">
                                <div class="label">
                                    <strong>Contributor Profile</strong>
                                    <em>export changes to this credits page profile</em>
                                </div>
                                <select class="select" name="contributor_id">
                                    <option value="0"<?= empty($user_data['contributor_id']) ? 'selected="selected"' : '' ?>>- none -</option>
                                    <option disabled="disabled">----------</option>
                                    <?
                                    $last_opt_group = '';
                                    foreach ($mmrpg_contributors_index AS $contributor_id => $contributor_data){
                                        $opt_group = 'Joined '.date('Y', $contributor_data['user_date_created']);
                                        if (empty($last_opt_group) || $last_opt_group !== $opt_group){ echo(!empty($last_opt_group) ? '</optgroup>' : ''); echo('<optgroup label="'.$opt_group.'">'); $last_opt_group = $opt_group; }
                                        $label = (!empty($contributor_data['user_name_public']) ? $contributor_data['user_name_public'].' / ' : '').$contributor_data['user_name'];
                                        if (strtolower($contributor_data['user_name']) !== $contributor_data['user_name_clean']){ $label .= ' / '.$contributor_data['user_name_clean']; }
                                        $selected = !empty($user_data['contributor_id']) && $user_data['contributor_id'] == $contributor_id ? 'selected="selected"' : '';
                                        echo('<option value="'.$contributor_id.'" '.$selected.'>'.$label.'</option>'.PHP_EOL);
                                    }
                                    echo(!empty($last_opt_group) ? '</optgroup>' : '');
                                    ?>
                                    <option disabled="disabled">----------</option>
                                    <option value="new">&laquo; New Profile &raquo;</option>
                                </select><span></span>
                            </div>

                            <? if (!empty($user_data['contributor_id'])){ ?>

                                <?
                                // Pull contributor data for this user in case we need it
                                $contributor_fields = rpg_user::get_contributor_index_fields(true);
                                $contributor_data = $db->get_array("SELECT {$contributor_fields} FROM mmrpg_users_contributors WHERE contributor_id = {$user_data['contributor_id']};");
                                ?>

                                <div class="field fullsize">
                                    <div class="label">
                                        <strong>Contributor Credit Line</strong>
                                        <em>public, displayed on credits page profile</em>
                                    </div>
                                    <strong class="label"></strong>
                                    <input class="textbox" type="text" name="user_credit_line" value="<?= htmlentities($user_data['user_credit_line'], ENT_QUOTES, 'UTF-8', true) ?>" maxlength="255" />
                                </div>

                                <div class="field fullsize">
                                    <div class="label">
                                        <strong>Contributor Credit Text</strong>
                                        <em>public, displayed on credits page profile</em>
                                    </div>
                                    <textarea class="textarea" name="user_credit_text" rows="20"><?= htmlentities($user_data['user_credit_text'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                                </div>

                                <div class="options">

                                    <div class="field checkwrap rfloat">
                                        <label class="label">
                                            <strong>Active Contributor</strong>
                                            <input type="hidden" name="contributor_flag_showcredits" value="0" checked="checked" />
                                            <input class="checkbox" type="checkbox" name="contributor_flag_showcredits" value="1" <?= !empty($contributor_data['contributor_flag_showcredits']) ? 'checked="checked"' : '' ?> />
                                        </label>
                                        <p class="subtext">Show user on the credits page</p>
                                    </div>

                                </div>

                            <? } ?>

                        </div>

                        <? if ($allow_edit_permissions){ ?>

                            <div class="panel" data-tab="access">

                                <div class="field fullsize" style="min-height: 0;">
                                    <strong class="label">User Access Permissions</strong>
                                    <input type="hidden" name="update_access_permissions" value="true" />
                                </div>

                                <div class="field fullsize permissions-table">
                                    <?
                                    // Collect a list of all permissions so we can print out a proper list
                                    $user_permission_tokens = rpg_user::get_user_permission_tokens($user_data['user_id']);
                                    $user_permissions_table = rpg_user::get_permissions_table();
                                    $permissions_table_markup = cms_admin::print_user_permissions_table($user_permissions_table, $user_permission_tokens);
                                    echo($permissions_table_markup.PHP_EOL);
                                    //echo('<pre>get_permissions_table: '.print_r(rpg_user::get_permissions_table(), true).'</pre>');
                                    //echo('<pre>'.$user_data['user_name_clean'].'_user_permissions_tokens: '.print_r(rpg_user::get_user_permissions_tokens($user_data['user_id']), true).'</pre>');
                                    //echo('<pre>(admin) current_user_permission_tokens: '.print_r(rpg_user::current_user_permission_tokens(), true).'</pre>');
                                    ?>
                                </div>

                            </div>

                        <? } ?>

                        <div class="panel" data-tab="notes">

                            <div class="field fullsize">
                                <div class="label">
                                    <strong>Moderates Notes</strong>
                                    <em>private, only visible to staff</em>
                                </div>
                                <textarea class="textarea" name="user_admin_text" rows="20"><?= htmlentities($user_data['user_admin_text'], ENT_QUOTES, 'UTF-8', true) ?></textarea>
                            </div>

                        </div>

                    </div>

                    <hr />

                    <div class="options">

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Approved User</strong>
                                <input type="hidden" name="user_flag_approved" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="user_flag_approved" value="1" <?= !empty($user_data['user_flag_approved']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">Allow user to access their game</p>
                        </div>

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Post Public</strong>
                                <input type="hidden" name="user_flag_postpublic" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="user_flag_postpublic" value="1" <?= !empty($user_data['user_flag_postpublic']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">Allow user to make community posts</p>
                        </div>

                        <div class="field checkwrap">
                            <label class="label">
                                <strong>Post Private</strong>
                                <input type="hidden" name="user_flag_postprivate" value="0" checked="checked" />
                                <input class="checkbox" type="checkbox" name="user_flag_postprivate" value="1" <?= !empty($user_data['user_flag_postprivate']) ? 'checked="checked"' : '' ?> />
                            </label>
                            <p class="subtext">Allow user to send private messages</p>
                        </div>

                    </div>

                    <hr />

                    <div class="formfoot">

                        <div class="buttons">
                            <input class="button save" type="submit" value="Save Changes" />
                            <input class="button delete" type="button" value="Delete User" data-delete="users" data-user-id="<?= $user_data['user_id'] ?>" />
                        </div>

                        <div class="metadata">
                            <div class="date last-login"><strong>Last Login</strong>: <?= !empty($user_data['user_last_login']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $user_data['user_last_login'])) : '-' ?></div>
                            <div class="date created"><strong>Created</strong>: <?= !empty($user_data['user_date_created']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $user_data['user_date_created'])): '-' ?></div>
                            <div class="date modified"><strong>Modified</strong>: <?= !empty($user_data['user_date_modified']) ? str_replace('@', 'at', date('Y-m-d @ H:i', $user_data['user_date_modified'])) : '-' ?></div>
                            <?
                            if ($user_data['user_account_age'] > 0){
                                $date1 = new DateTime();
                                $date2 = new DateTime();
                                $date1->setTimestamp($user_data['user_date_created']);
                                $date2->setTimestamp($user_data['user_date_accessed']);
                                $interval = $date1->diff($date2);
                                $years = $interval->y;
                                $months = $interval->m;
                                $days = $interval->d;
                                $print_user_account_age = array();
                                if (!empty($years)){ $print_user_account_age[] = $years.' '.($years === 1 ? 'Year' : 'Years'); }
                                if (!empty($months)){ $print_user_account_age[] = $months.' '.($months === 1 ? 'Month' : 'Months'); }
                                if (!empty($days)){ $print_user_account_age[] = $days.' '.($days === 1 ? 'Day' : 'Days'); }
                                if (empty($print_user_account_age)){ $print_user_account_age[] = '1 Day'; }
                                $print_user_account_age = implode(', ', $print_user_account_age);
                                echo('<div class="date account-age"><strong>Active For</strong>: '.$print_user_account_age.'</div>');
                            }
                            ?>
                        </div>

                    </div>

                </form>

            </div>

            <?

            /*
            $debug_user_data = $user_data;
            $debug_user_data['user_profile_text'] = str_replace(PHP_EOL, '\\n', $debug_user_data['user_profile_text']);
            $debug_user_data['user_credit_text'] = str_replace(PHP_EOL, '\\n', $debug_user_data['user_credit_text']);
            echo('<pre>$user_data = '.(!empty($debug_user_data) ? htmlentities(print_r($debug_user_data, true), ENT_QUOTES, 'UTF-8', true) : '&hellip;').'</pre>');
            */

            ?>


        <? endif; ?>

    </div>

<? $this_page_markup .= ob_get_clean(); ?>