<?php
// If a user ID has been defined, attempt to swap the save session
if (!defined('MMRPG_REMOTE_GAME_ID')){ define('MMRPG_REMOTE_GAME_ID', (!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0)); }
if (MMRPG_REMOTE_GAME_ID != 0 && MMRPG_REMOTE_GAME_ID != $_SESSION['GAME']['USER']['userid']){

    // Attempt to collect data for this player from the database
    $db_user_fields = rpg_user::get_index_fields(true, 'mmrpg_users');
    $this_playerid = MMRPG_REMOTE_GAME_ID;
    $raw_playerinfo = $db->get_array("SELECT
        {$db_user_fields},
        mmrpg_saves.save_flags,
        mmrpg_saves.save_counters,
        mmrpg_saves.save_values,
        mmrpg_saves.save_values_battle_complete,
        mmrpg_saves.save_values_battle_failure,
        mmrpg_saves.save_settings
        FROM mmrpg_users
        LEFT JOIN mmrpg_saves ON mmrpg_saves.user_id = mmrpg_users.user_id
        WHERE mmrpg_users.user_id = {$this_playerid};");

    // If the userinfo exists in the database, display it
    if (!empty($this_playerid) && !empty($raw_playerinfo)){

        // Ensure this remote game actually exists
        $temp_session_key = 'REMOTE_GAME_'.MMRPG_REMOTE_GAME_ID;

        // Define the constant that forces remote game checking
        define('MMRPG_REMOTE_GAME', MMRPG_REMOTE_GAME_ID);

        // Collect this player's info from the database... all of it
        $this_playerinfo = array();

        // Collect basic user details from the raw player info
        $this_playerinfo['user_id'] = !empty($raw_playerinfo['user_id']) ? $raw_playerinfo['user_id'] : 0;
        $this_playerinfo['user_name'] = !empty($raw_playerinfo['user_name']) ? $raw_playerinfo['user_name'] : '';
        $this_playerinfo['user_name_clean'] = !empty($raw_playerinfo['user_name_clean']) ? $raw_playerinfo['user_name_clean'] : '';
        $this_playerinfo['user_omega'] = !empty($raw_playerinfo['user_omega']) ? $raw_playerinfo['user_omega'] : '';
        $this_playerinfo['user_image_path'] = !empty($raw_playerinfo['user_image_path']) ? $raw_playerinfo['user_image_path'] : '';
        $this_playerinfo['user_background_path'] = !empty($raw_playerinfo['user_background_path']) ? $raw_playerinfo['user_background_path'] : '';
        $this_playerinfo['user_colour_token'] = !empty($raw_playerinfo['user_colour_token']) ? $raw_playerinfo['user_colour_token'] : '';
        $this_playerinfo['user_gender'] = !empty($raw_playerinfo['user_gender']) ? $raw_playerinfo['user_gender'] : '';

        // Collect the base game arrays from the raw player info
        $this_playerinfo['flags'] = !empty($raw_playerinfo['save_flags']) ? json_decode($raw_playerinfo['save_flags'], true) : array();
        $this_playerinfo['counters'] = !empty($raw_playerinfo['save_counters']) ? json_decode($raw_playerinfo['save_counters'], true) : array();
        $this_playerinfo['values'] = !empty($raw_playerinfo['save_values']) ? json_decode($raw_playerinfo['save_values'], true) : array();
        $this_playerinfo['settings'] = !empty($this_playerinfo['save_settings']) ? json_decode($this_playerinfo['save_settings'], true) : array();

        // Create internal value arrays based on remote skip flags
        $this_playerinfo['values']['battle_index'] = array();
        $this_playerinfo['values']['battle_complete'] = !defined('MMRPG_REMOTE_SKIP_COMPLETE') && !empty($this_playerinfo['save_values_battle_complete']) ? json_decode($this_playerinfo['save_values_battle_complete'], true) : array();
        $this_playerinfo['values']['battle_failure'] = !defined('MMRPG_REMOTE_SKIP_FAILURE') && !empty($this_playerinfo['save_values_battle_failure']) ? json_decode($this_playerinfo['save_values_battle_failure'], true) : array();

        // Manually load battle rewards and/or settings from another table if requested and they exist
        if (!defined('MMRPG_REMOTE_SKIP_REWARDS') || !defined('MMRPG_REMOTE_SKIP_SETTINGS')){

            // Create arrays to hold the battle rewards and settings
            $raw_battle_vars = array();
            $raw_battle_rewards = array();
            $raw_battle_settings = array();

            //echo('<pre>$this_playerid = '.print_r($this_playerid, true).'</pre>');

            // Collect battle settings and/or rewards from the functions
            if (!defined('MMRPG_REMOTE_SKIP_SETTINGS') && !defined('MMRPG_REMOTE_SKIP_REWARDS')){
                $raw_battle_vars = rpg_user::get_battle_vars($this_playerid);
                $raw_battle_settings = $raw_battle_vars['battle_settings'];
                $raw_battle_rewards = $raw_battle_vars['battle_rewards'];
            } elseif (!defined('MMRPG_REMOTE_SKIP_SETTINGS')){
                $raw_battle_settings = rpg_user::get_battle_settings($this_playerid);
            } elseif (!defined('MMRPG_REMOTE_SKIP_REWARDS')){
                $raw_battle_rewards = rpg_user::get_battle_rewards($this_playerid);
            }

            //echo('<pre>$raw_battle_vars = '.print_r($raw_battle_vars, true).'</pre>');
            //echo('<pre>$raw_battle_settings = '.print_r($raw_battle_settings, true).'</pre>');
            //echo('<pre>$raw_battle_rewards = '.print_r($raw_battle_rewards, true).'</pre>');

            // Assign battle rewards and settings to the parent values array
            $this_playerinfo['values']['battle_rewards'] = $raw_battle_rewards;
            $this_playerinfo['values']['battle_settings'] = $raw_battle_settings;

        }

        // Manually load unlocked abilities from another table if requested and they exist
        $this_playerinfo['values']['battle_abilities'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_ABILITIES')){ $this_playerinfo['values']['battle_abilities'] = rpg_user::get_battle_abilities($this_playerid); }

        // Manually load unlocked items from another table if requested and they exist
        $this_playerinfo['values']['battle_items'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_ITEMS')){ $this_playerinfo['values']['battle_items'] = rpg_user::get_battle_items($this_playerid); }

        // Manually load unlocked stars from another table if requested and they exist
        $this_playerinfo['values']['battle_stars'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_STARS')){ $this_playerinfo['values']['battle_stars'] = rpg_user::get_battle_stars($this_playerid); }

        // Manually load encounter records from another table if requested and they exist
        $this_playerinfo['values']['robot_database'] = array();
        if (!defined('MMRPG_REMOTE_SKIP_DATABASE')){ $this_playerinfo['values']['robot_database'] = rpg_user::get_robot_database($this_playerid); }

        // Add this player's GAME data to the session for iframe scripts
        $temp_remote_session = array();
        $temp_remote_session['CACHE_DATE'] = !empty($_SESSION['GAME']['CACHE_DATE']) ? $_SESSION['GAME']['CACHE_DATE'] : MMRPG_CONFIG_CACHE_DATE;
        $temp_remote_session['DEMO'] = 0;
        $temp_remote_session['USER'] = array(
            'userid' => $this_playerinfo['user_id'],
            'username' => $this_playerinfo['user_name'],
            'username_clean' => $this_playerinfo['user_name_clean'],
            'omega' => $this_playerinfo['user_omega'],
            'imagepath' => $this_playerinfo['user_image_path'],
            'backgroundpath' => $this_playerinfo['user_background_path'],
            'colourtoken' => $this_playerinfo['user_colour_token'],
            'gender' => $this_playerinfo['user_gender']
            );
        $temp_remote_session['counters'] = $this_playerinfo['counters'];
        $temp_remote_session['values'] = $this_playerinfo['values'];
        $temp_remote_session['flags'] = $this_playerinfo['flags'];
        $temp_remote_session['settings'] = $this_playerinfo['settings'];
        $temp_session_key = 'REMOTE_GAME_'.$this_playerinfo['user_id'];
        $_SESSION[$temp_session_key] = $temp_remote_session;

        // If the user had a colour token, define it as a constant
        if (!empty($this_playerinfo['user_colour_token'])){
            define('MMRPG_SETTINGS_REMOTE_FIELDTYPE', $this_playerinfo['user_colour_token']);
        }

    }

}
?>