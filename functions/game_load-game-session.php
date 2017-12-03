<?
// Define a function for loading the game session
function mmrpg_load_game_session(){

    // Reference global variables
    global $db;
    $session_token = rpg_game::session_token();

    //echo('<pre>mmrpg_load_game_session()</pre>'.PHP_EOL);
    //echo('<pre>$session_token = '.print_r($session_token, true).'</pre>'.PHP_EOL);

    // Do NOT load, save, or otherwise alter the game file while viewing remote
    if (defined('MMRPG_REMOTE_GAME')){ return true; }

    // Clear the community thread tracker
    $_SESSION['COMMUNITY']['threads_viewed'] = array();

    // Collect the pending login details if set
    $login_user_id = 0;
    if (!empty($_SESSION[$session_token]['PENDING_LOGIN_ID'])){
        $login_user_id = $_SESSION[$session_token]['PENDING_LOGIN_ID'];
    } elseif (!empty($_SESSION[$session_token]['USER']['userid'])){
        $login_user_id = $_SESSION[$session_token]['USER']['userid'];
    }

    //echo('<pre>$login_user_id = '.print_r($login_user_id, true).'</pre>'.PHP_EOL);

    // If this is NOT demo mode, load from database
    $is_demo_mode = rpg_game::is_demo();
    //echo('<pre>$is_demo_mode = '.print_r($is_demo_mode, true).'</pre>'.PHP_EOL);
    if (!$is_demo_mode && !empty($login_user_id)){

        // LOAD DATABASE INFO

        // Collect the user info from the database
        $user_index_fields = rpg_user::get_index_fields(true, 'users');
        $this_database_user = $db->get_array("SELECT
            {$user_index_fields}
            FROM mmrpg_users AS users
            WHERE user_id = {$login_user_id}
            LIMIT 1
            ;");
        if (empty($this_database_user)){ die('could not load user for uid:'.$login_user_id.' on line '.__LINE__); }

        // Collect the save file info from the database
        $user_save_index_fields = rpg_user_save::get_index_fields(true, 'saves');
        $user_save2_index_fields = rpg_user_save::get_legacy_index_fields(true, 'saves2');
        $this_database_user_save = $db->get_array("SELECT
            {$user_save_index_fields},
            {$user_save2_index_fields}
            FROM mmrpg_saves AS saves
            LEFT JOIN mmrpg_saves_legacy AS saves2 ON saves2.user_id = saves.user_id
            WHERE saves.user_id = {$login_user_id}
            LIMIT 1
            ;");
        if (empty($this_database_user_save)){ die('could not load save for uid:'.$login_user_id.' on line '.__LINE__); }

        //echo('<pre><strong>Loading game file '.$login_user_id.' &hellip;</strong></pre><hr />'.PHP_EOL);

        //echo('<pre>$this_database_user_save = '.print_r($this_database_user_save, true).'</pre><hr />'.PHP_EOL);
        //echo('<pre>$this_database_user = '.print_r($this_database_user, true).'</pre><hr />'.PHP_EOL);

        // Update the game session with database extracted variables
        $new_game_data = array();

        $new_game_data['CACHE_DATE'] = $this_database_user_save['save_cache_date'];

        $new_game_data['USER']['userid'] = $this_database_user['user_id'];
        $new_game_data['USER']['roleid'] = $this_database_user['role_id'];
        $new_game_data['USER']['username'] = $this_database_user['user_name'];
        $new_game_data['USER']['username_clean'] = $this_database_user['user_name_clean'];
        $new_game_data['USER']['password'] = '';
        $new_game_data['USER']['password_encoded'] = '';
        $new_game_data['USER']['omega'] = $this_database_user['user_omega'];
        $new_game_data['USER']['profiletext'] = $this_database_user['user_profile_text'];
        $new_game_data['USER']['creditstext'] = $this_database_user['user_credit_text'];
        $new_game_data['USER']['creditsline'] = $this_database_user['user_credit_line'];
        $new_game_data['USER']['imagepath'] = $this_database_user['user_image_path'];
        $new_game_data['USER']['backgroundpath'] = $this_database_user['user_background_path'];
        $new_game_data['USER']['colourtoken'] = $this_database_user['user_colour_token'];
        $new_game_data['USER']['gender'] = $this_database_user['user_gender'];
        $new_game_data['USER']['displayname'] = $this_database_user['user_name_public'];
        $new_game_data['USER']['emailaddress'] = $this_database_user['user_email_address'];
        $new_game_data['USER']['websiteaddress'] = $this_database_user['user_website_address'];
        $new_game_data['USER']['dateofbirth'] = $this_database_user['user_date_birth'];
        $new_game_data['USER']['approved'] = $this_database_user['user_flag_approved'];


        // Decode this user's save flag if any have been set
        $new_game_data['flags'] = !empty($this_database_user_save['save_flags']) ? json_decode($this_database_user_save['save_flags'], true) : array();
        $new_game_data['counters'] = !empty($this_database_user_save['save_counters']) ? json_decode($this_database_user_save['save_counters'], true) : array();
        $new_game_data['values'] = !empty($this_database_user_save['save_values']) ? json_decode($this_database_user_save['save_values'], true) : array();

        // Collect battle rewards and settings for this user from the database
        $raw_battle_vars = rpg_user::get_battle_vars($login_user_id);
        $new_game_data['values']['battle_rewards'] = $raw_battle_vars['battle_rewards'];
        $new_game_data['values']['battle_settings'] = $raw_battle_vars['battle_settings'];

        // Collect any unlocked abilities for this user from the database
        $new_game_data['values']['battle_abilities'] = rpg_user::get_battle_abilities($login_user_id);

        // Collect any unlocked items for this user from the database
        $new_game_data['values']['battle_items'] = rpg_user::get_battle_items($login_user_id);

        // Collect any unlocked stars for this user from the database
        $new_game_data['values']['battle_stars'] = rpg_user::get_battle_stars($login_user_id);

        // Collect any encounter records for this user from the database
        $new_game_data['values']['robot_database'] = rpg_user::get_robot_database($login_user_id);

        // Collect any unlocked alts for this user from the database
        $new_game_data['values']['robot_alts'] = rpg_user::get_robot_alts($login_user_id);

        // Collect any encounter records for this user from the database
        $new_game_data['values']['robot_database'] = rpg_user::get_robot_database($login_user_id);

        // Collect any mission records for this user from the database
        $new_game_data['values']['battle_complete'] = rpg_user::get_mission_records($login_user_id, 'victory');
        $new_game_data['values']['battle_failure'] = rpg_user::get_mission_records($login_user_id, 'defeat');

        // Collect any shop records for this user from the database
        $new_game_data['values']['battle_shops'] = rpg_user::get_battle_shops($login_user_id);

        // Collect any omega factors for this user from the database
        $new_game_data['values'] += rpg_user::get_target_robot_omega($login_user_id);

        // Decode this user's battle settings if any have been created
        $new_game_data['battle_settings'] = !empty($this_database_user_save['save_settings']) ? json_decode($this_database_user_save['save_settings'], true) : array();

        //echo('<pre>$new_game_data = '.print_r($new_game_data, true).'</pre><hr />'.PHP_EOL);

        // Update the session with the new save info
        $_SESSION[$session_token] = array_merge($_SESSION[$session_token], $new_game_data);
        unset($new_game_data);

        //echo('<pre>$_SESSION[\''.$session_token.'\'] = '.print_r($_SESSION[$session_token], true).'</pre><hr />'.PHP_EOL);

        //exit();

        // Unset the player selection to restart at the player select screen
        if (mmrpg_prototype_players_unlocked() > 1){ $_SESSION[$session_token]['battle_settings']['this_player_token'] = false; }

        // Expand user's current IP list, then add a new entry and filter unique
        $local_ips = array('0.0.0.0', '127.0.0.1');
        $ip_list = !empty($this_database_user['user_ip_addresses']) ? $this_database_user['user_ip_addresses'] : '';
        $ip_list = strstr($ip_list, ',') ? explode(',', $ip_list) : array($ip_list);
        $ip_list = array_filter(array_map('trim', $ip_list));
        $ip_list[] = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ $ip_list[] = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])); }
        foreach ($ip_list AS $k => $ip){ if (empty($ip) || in_array($ip, $local_ips)){ unset($ip_list[$k]); } }
        $ip_list = array_unique($ip_list);

        // Update the user table in the database if not done already
        if (empty($_SESSION[$session_token]['DEMO'])){
            $db->update('mmrpg_users', array(
                'user_ip_addresses' => implode(',', $ip_list)
                ), "user_id = {$this_database_user['user_id']}");
        }

        /*
        // Update the user table in the database if not done already
        if (empty($_SESSION[$session_token]['DEMO'])){
            $db->update('mmrpg_users', array(
                'user_last_login' => time(),
                'user_backup_login' => $this_database_user['user_last_login'],
                'user_ip_addresses' => implode(',', $ip_list)
                ), "user_id = {$this_database_user['user_id']}");
        }
        */

        // Clear the pending login ID
        unset($_SESSION[$session_token]['PENDING_LOGIN_ID']);

    }

    // Update the last saved value
    $_SESSION[$session_token]['values']['last_load'] = time();

    // Return true on success
    return true;

}
?>