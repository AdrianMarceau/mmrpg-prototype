<?php

/*
 * PROTOTYPE FUNCTIONS
 */

// Define a function for calculating required experience points to the next level
function mmrpg_prototype_calculate_shop_experience_required($this_level, $max_level = 100, $min_experience = 1000){

    $last_level = $this_level - 1;
    $level_mod = $this_level / $max_level;
    $this_experience = round($min_experience + ($last_level * $level_mod * $min_experience));

    return $this_experience;
}

// Define a function for calculating required experience points to the next level
function mmrpg_prototype_calculate_shop_level_by_experience($this_experience, $max_level = 100, $min_experience = 1000){
    $temp_total_experience = 0;
    for ($this_level = 1; $this_level < $max_level; $this_level++){
        $temp_experience = mmrpg_prototype_calculate_shop_experience_required($this_level, $max_level, $min_experience);
        $temp_total_experience += $temp_experience;
        if ($temp_total_experience > $this_experience){
            return $this_level - 1;
        }
    }
    return $max_level;
}

// Define a function for checking a player has completed the prototype
function mmrpg_prototype_complete($player_token = ''){
    // Pull in global variables
    //global $mmrpg_index;
    $mmrpg_index_players = $GLOBALS['mmrpg_index']['players'];
    $session_token = mmrpg_game_token();
    // If the player token was provided, do a quick check
    if (!empty($player_token)){
        // Return the prototype complete flag for this player
        if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){ return 1; }
        else { return 0; }
    }
    // Otherwise loop through all players and check each
    else {
        // Loop through unlocked robots and return true if any are found to be completed
        $complete_count = 0;
        foreach ($mmrpg_index_players AS $player_token => $player_info){
            if (mmrpg_prototype_player_unlocked($player_token)){
                if (!empty($_SESSION[$session_token]['flags']['prototype_events'][$player_token]['prototype_complete'])){
                    $complete_count += 1;
                }
            }
        }
        // Otherwise return false by default
        return $complete_count;
    }
}

// Define a BETTER function to calculating a player's current battle points
function mmrpg_prototype_calculate_battle_points_2k19($user_id, &$points_index = array()){

    // Return early if arguments provided are invalid
    if (empty($user_id) || !is_numeric($user_id)){ return false; }

    // Collect a reference to the database
    global $db;

    // Collect the user's save details from the database, if possible
    $user_save_array = $db->get_array("SELECT
        save_id, user_id,
        save_counters, save_values, save_flags, save_settings,
        save_values_battle_rewards, save_values_battle_settings,
        save_values_battle_items, save_values_battle_abilities,
        save_values_battle_stars, save_values_robot_database,
        save_values_robot_alts,
        save_date_modified
        FROM mmrpg_saves
        WHERE user_id = {$user_id}
        ;");

    // If user data was empty, we should just return now
    if (empty($user_save_array)){ return false; }

    // Otherwise, loop through and expand any json-encoded arrays
    foreach ($user_save_array AS $key => $value){ if (preg_match('/^(\{|\[)(.*)(\]|\})$/i', $value)){ $user_save_array[$key] = json_decode($value, true); } }

    // Collect quick references to key arrays in the game save data
    $user_battle_rewards = !empty($user_save_array['save_values_battle_rewards']) ? $user_save_array['save_values_battle_rewards'] : array();
    $user_battle_settings = !empty($user_save_array['save_values_battle_settings']) ? $user_save_array['save_values_battle_settings'] : array();

    // If there were not battle rewards to loop through, we've got nothing
    if (empty($user_battle_rewards) || empty($user_battle_settings)){ return false; }

    // Always reset the battle point counter to zero
    $total_battle_points = 0;

    // Collect quick references to the rest of the key arrays in the game save data
    $user_battle_abilities = !empty($user_save_array['save_values_battle_abilities']) ? $user_save_array['save_values_battle_abilities'] : array();
    $user_battle_items = !empty($user_save_array['save_values_battle_items']) ? $user_save_array['save_values_battle_items'] : array();
    $user_battle_stars = !empty($user_save_array['save_values_battle_stars']) ? $user_save_array['save_values_battle_stars'] : array();
    $user_battle_fields = !empty($user_save_array['save_values']['battle_fields']) ? $user_save_array['save_values']['battle_fields'] : array();
    $user_robot_alts = !empty($user_save_array['save_values_robot_alts']) ? $user_save_array['save_values_robot_alts'] : array();
    $user_robot_database = !empty($user_save_array['save_values_robot_database']) ? $user_save_array['save_values_robot_database'] : array();

    // Collect a quick robot, ability, and item index for reference
    $mmrpg_robots = rpg_robot::get_index();
    $mmrpg_abilities = rpg_ability::get_index();
    $mmrpg_items = rpg_item::get_index();
    $mmrpg_fields = rpg_field::get_index();

    // -- CHAPTER POINTS -- //

    // Grant the player bonuses for completing any of the doctor's chapters (chapter complete)
    if (true){
        $chapter_events_unlocked = array();
        if (!empty($user_save_array['save_flags']['events'])){
            $event_flags = $user_save_array['save_flags']['events'];
            $doctors_unlocked = array_keys($user_battle_rewards);
            foreach ($doctors_unlocked AS $doctor_token){
                if ($doctor_token === 'player'){ continue; }
                $pt_complete = !empty($user_save_array['save_values']['prototype_awards']['prototype_complete_'.str_replace('dr-', '', $doctor_token)]) ? true : false;
                for ($ch = 1; $ch <= 5; $ch++){
                    if (!$pt_complete && empty($event_flags[$doctor_token.'_chapter-'.$ch.'-unlocked'])){ continue; }
                    $chapter_events_unlocked[] = $doctor_token.'_chapter-'.$ch;
                }
            }
        }
        $points_index['chapters_unlocked'] = $chapter_events_unlocked;
        $points_index['chapters_unlocked_points'] = count($chapter_events_unlocked) * 25000;
        $total_battle_points += $points_index['chapters_unlocked_points'];
    }

    // -- DOCTOR POINTS -- //

    // Loop through and grant the user battle points for each doctor unlocked
    if (true){
        $doctors_unlocked = array();
        foreach ($user_battle_rewards AS $doctor_token => $doctor_info){
            if (empty($doctor_info) || in_array($doctor_token, $doctors_unlocked)){ continue; }
            $doctors_unlocked[] = $doctor_token;
        }
        $points_index['doctors_unlocked'] = $doctors_unlocked;
        $points_index['doctors_unlocked_points'] = count($doctors_unlocked) * 50000;
        $total_battle_points += $points_index['doctors_unlocked_points'];
    }


    // -- ABILITY POINTS -- //

    // Loop through and grant the user battle points for each ability unlocked
    if (true){
        $abilities_unlocked = array();
        foreach ($user_battle_abilities As $ability_key => $ability_token){
            if (!isset($mmrpg_abilities[$ability_token])){ continue; }
            elseif (in_array($ability_token, $abilities_unlocked)){ continue; }
            elseif (!$mmrpg_abilities[$ability_token]['ability_flag_complete']){ continue; }
            elseif ($mmrpg_abilities[$ability_token]['ability_flag_hidden']){ continue; }
            $abilities_unlocked[] = $ability_token;
        }
        $points_index['abilities_unlocked'] = $abilities_unlocked;
        $points_index['abilities_unlocked_points'] = count($abilities_unlocked) * 2000;
        $total_battle_points += $points_index['abilities_unlocked_points'];
    }

    // -- ITEM POINTS -- //

    // Loop through and grant the user battle points for each item unlocked
    if (true){
        $item_points = 0;
        $items_unlocked = array();
        foreach ($user_battle_items As $item_token => $item_quantity){
            if (!isset($mmrpg_items[$item_token])){ continue; }
            elseif (in_array($item_token, $items_unlocked)){ continue; }
            elseif (empty($item_quantity)){ continue; }
            elseif (strstr($item_token, '-screw')){ continue; }
            $item_info = $mmrpg_items[$item_token];
            if (!$item_info['item_flag_complete']){ continue; }
            elseif ($item_info['item_flag_hidden']){ continue; }
            $item_value = 0;
            if (!empty($item_info['item_value'])){ $item_value = $item_info['item_value']; }
            elseif (!empty($item_info['item_price'])){ $item_value = $item_info['item_price']; }
            if ($item_quantity > MMRPG_SETTINGS_ITEMS_MAXQUANTITY){ $item_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY; }
            $item_points += $item_quantity * $item_value;
            $item_label = $item_token.($item_quantity > 1 ? ' x'.$item_quantity : '');
            //$item_label .= ' ('.number_format($item_value, 0, '.', ',').' BP each)';
            $items_unlocked[] = $item_label;
        }
        $points_index['items_unlocked'] = $items_unlocked;
        $points_index['items_unlocked_points'] = $item_points;
        $total_battle_points += $points_index['items_unlocked_points'];
    }

    // -- FIELD POINTS -- //

    // Loop through and grant the user battle points for each field unlocked
    if (true){
        global $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three;
        if (empty($this_omega_factors_one)){ require(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php'); }
        $field_points = 0;
        $fields_unlocked = array();
        foreach ($user_battle_fields As $field_key => $field_token){
            if (!isset($mmrpg_fields[$field_token])){ continue; }
            elseif (in_array($field_token, $fields_unlocked)){ continue; }
            elseif (!$mmrpg_fields[$field_token]['field_flag_complete']){ continue; }
            elseif ($mmrpg_fields[$field_token]['field_flag_hidden']){ continue; }
            $fields_unlocked[] = $field_token;
        }
        if (in_array('dr-light', $doctors_unlocked)){ foreach ($this_omega_factors_one AS $omega){ $fields_unlocked[] = $omega['field']; } }
        if (in_array('dr-wily', $doctors_unlocked)){ foreach ($this_omega_factors_two AS $omega){ $fields_unlocked[] = $omega['field']; } }
        if (in_array('dr-cossack', $doctors_unlocked)){ foreach ($this_omega_factors_three AS $omega){ $fields_unlocked[] = $omega['field']; } }
        $fields_unlocked = array_unique($fields_unlocked);
        $points_index['fields_unlocked'] = $fields_unlocked;
        $points_index['fields_unlocked_points'] = count($fields_unlocked) * 15000;
        $total_battle_points += $points_index['fields_unlocked_points'];
    }


    // -- ROBOT POINTS -- //

    // Loop through and grant the user battle points for each robot unlocked
    if (true){
        $robots_unlocked = array();
        $robots_unlocked_max_level = array();
        $robots_unlocked_max_attack = array();
        $robots_unlocked_max_defense = array();
        $robots_unlocked_max_speed = array();
        $robots_unlocked_alt_outfits = array();
        $robots_unlocked_alt_outfits_count = 0;
        foreach ($user_battle_rewards AS $doctor_token => $doctor_info){
            if (empty($doctor_info) || empty($doctor_info['player_robots'])){ continue; }
            foreach ($doctor_info['player_robots'] AS $robot_token => $robot_info){
                if (!isset($mmrpg_robots[$robot_token])){ continue; }
                elseif (in_array($robot_token, $robots_unlocked)){ continue; }
                elseif (!$mmrpg_robots[$robot_token]['robot_flag_complete']){ continue; }
                elseif ($mmrpg_robots[$robot_token]['robot_flag_hidden']){ continue; }
                $robots_unlocked[] = $robot_token;
                $robot_level = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
                $robot_stats = rpg_robot::calculate_stat_values($robot_level, $mmrpg_robots[$robot_token], $robot_info, true);
                if ($robot_stats['level'] >= 100 && !in_array($robot_token, $robots_unlocked_max_level)){ $robots_unlocked_max_level[] = $robot_token; }
                if ($robot_stats['attack']['bonus'] >= $robot_stats['attack']['bonus_max'] && !in_array($robot_token, $robots_unlocked_max_attack)){ $robots_unlocked_max_attack[] = $robot_token; }
                if ($robot_stats['defense']['bonus'] >= $robot_stats['defense']['bonus_max'] && !in_array($robot_token, $robots_unlocked_max_defense)){ $robots_unlocked_max_defense[] = $robot_token; }
                if ($robot_stats['speed']['bonus'] >= $robot_stats['speed']['bonus_max'] && !in_array($robot_token, $robots_unlocked_max_speed)){ $robots_unlocked_max_speed[] = $robot_token; }
                if (!empty($user_robot_alts[$robot_token])){
                    $num_alts = count($user_robot_alts[$robot_token]);
                    $robots_unlocked_alt_outfits[] = $robot_token.' x'.$num_alts;
                    $robots_unlocked_alt_outfits_count += $num_alts;
                }
            }
        }
        $points_index['robots_unlocked'] = $robots_unlocked;
        $points_index['robots_unlocked_points'] = count($robots_unlocked) * 10000;
        $total_battle_points += $points_index['robots_unlocked_points'];
        $points_index['robots_unlocked_max_level'] = $robots_unlocked_max_level;
        $points_index['robots_unlocked_max_level_points'] = count($robots_unlocked_max_level) * 4000;
        $total_battle_points += $points_index['robots_unlocked_max_level_points'];
        $points_index['robots_unlocked_max_attack'] = $robots_unlocked_max_attack;
        $points_index['robots_unlocked_max_attack_points'] = count($robots_unlocked_max_attack) * 2000;
        $total_battle_points += $points_index['robots_unlocked_max_attack_points'];
        $points_index['robots_unlocked_max_defense'] = $robots_unlocked_max_defense;
        $points_index['robots_unlocked_max_defense_points'] = count($robots_unlocked_max_defense) * 2000;
        $total_battle_points += $points_index['robots_unlocked_max_defense_points'];
        $points_index['robots_unlocked_max_speed'] = $robots_unlocked_max_speed;
        $points_index['robots_unlocked_max_speed_points'] = count($robots_unlocked_max_speed) * 2000;
        $total_battle_points += $points_index['robots_unlocked_max_speed_points'];
        $points_index['robots_unlocked_alt_outfits'] = $robots_unlocked_alt_outfits;
        $points_index['robots_unlocked_alt_outfits_points'] = $robots_unlocked_alt_outfits_count * 500;
        $total_battle_points += $points_index['robots_unlocked_alt_outfits_points'];
    }

    // -- DATABASE POINTS -- //

    // Loop through all robots in the robot database and award points for seeing and for scanning
    if (true){
        $database_robots_encountered = array();
        $database_robots_defeated = array();
        $database_robots_summoned = array();
        $database_robots_scanned = array();
        foreach ($mmrpg_robots AS $robot_token => $robot_info){
            if ($robot_token === 'robot'){ continue; }
            elseif (!$robot_info['robot_flag_complete']){ continue; }
            elseif ($robot_info['robot_flag_hidden']){ continue; }
            if (!empty($user_robot_database[$robot_token]['robot_encountered'])){ $database_robots_encountered[] = $robot_token; }
            if (!empty($user_robot_database[$robot_token]['robot_defeated'])){ $database_robots_defeated[] = $robot_token; }
            if (!empty($user_robot_database[$robot_token]['robot_summoned'])){ $database_robots_summoned[] = $robot_token; }
            if (!empty($user_robot_database[$robot_token]['robot_scanned'])){ $database_robots_scanned[] = $robot_token; }
        }
        $points_index['database_robots_encountered'] = $database_robots_encountered;
        $points_index['database_robots_encountered_points'] = count($database_robots_encountered) * 1000;
        $total_battle_points += $points_index['database_robots_encountered_points'];
        $points_index['database_robots_defeated'] = $database_robots_defeated;
        $points_index['database_robots_defeated_points'] = count($database_robots_defeated) * 1000;
        $total_battle_points += $points_index['database_robots_defeated_points'];
        $points_index['database_robots_summoned'] = $database_robots_summoned;
        $points_index['database_robots_summoned_points'] = count($database_robots_summoned) * 1000;
        $total_battle_points += $points_index['database_robots_summoned_points'];
        $points_index['database_robots_scanned'] = $database_robots_scanned;
        $points_index['database_robots_scanned_points'] = count($database_robots_scanned) * 1000;
        $total_battle_points += $points_index['database_robots_scanned_points'];
    }

    // -- STAR POINTS -- //

    // Loop through and grant the user battle points for each field star unlocked
    if (true){
        $field_stars_unlocked = array();
        $fusion_stars_unlocked = array();
        foreach ($user_battle_stars As $star_token => $star_info){
            if (!isset($mmrpg_fields[$star_info['star_field']])){ continue; }
            elseif (!empty($star_info['star_field2']) && !isset($mmrpg_fields[$star_info['star_field2']])){ continue; }
            elseif (in_array($star_token, $field_stars_unlocked) || in_array($star_token, $fusion_stars_unlocked)){ continue; }
            if (empty($star_info['star_field2']) || $star_info['star_field2'] === $star_info['star_field']){ $field_stars_unlocked[] = $star_token; }
            else { $fusion_stars_unlocked[] = $star_token; }
        }
        $points_index['field_stars_collected'] = $field_stars_unlocked;
        $points_index['field_stars_collected_points'] = count($field_stars_unlocked) * $mmrpg_items['field-star']['item_value'];
        $total_battle_points += $points_index['field_stars_collected_points'];
        $points_index['fusion_stars_collected'] = $fusion_stars_unlocked;
        $points_index['fusion_stars_collected_points'] = count($fusion_stars_unlocked) * $mmrpg_items['fusion-star']['item_value'];
        $total_battle_points += $points_index['fusion_stars_collected_points'];
    }

    // -- PLAYER POINTS -- //

    // Grant the user points for each unique player they've defeated in a player battle (any level)
    if (true){
        $players_defeated = array();
        $defeated_players = $db->get_array_list("SELECT
            DISTINCT(battles.target_user_id) AS target_user_id,
            (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS target_user_name,
            users.user_name_clean AS target_user_token,
            users.user_colour_token AS target_user_colour
            FROM mmrpg_battles AS battles
            LEFT JOIN mmrpg_users AS users ON battles.target_user_id = users.user_id
            WHERE
            battles.this_user_id = {$user_id}
            AND battles.this_player_result = 'victory'
            AND users.user_flag_approved = 1
            AND users.user_date_created <> users.user_date_accessed
            ;", 'target_user_name');
        $points_index['players_defeated'] = $defeated_players;
        $points_index['players_defeated_points'] = count($defeated_players) * 10000;
        $total_battle_points += $points_index['players_defeated_points'];
    }

    // -- BONUS POINTS -- //

    // Grant the user bonus veteran points based on their account age (user id relative to current max)
    if (true){
        $guest_id = MMRPG_SETTINGS_GUEST_ID;
        $max_user_id = (int)($db->get_value("SELECT MAX(user_id) AS max_id FROM mmrpg_users WHERE user_id < {$guest_id};", 'max_id'));
        $points_index['veteran_bonus'] = 'Max User ID ('.$max_user_id.') - This User ID ('.$user_id.') = Bonus ('.($max_user_id - $user_id).')';
        $points_index['veteran_bonus_points'] = $max_user_id - $user_id;
        $total_battle_points += $points_index['veteran_bonus_points'];
    }

    // -- CAMPAIGN POINTS -- //

    // Grant the player huge bonuses for completing any of the doctor's campaigns (prototype complete)
    if (true){
        $complete_events_unlocked = array();
        if (!empty($user_save_array['save_values']['prototype_awards'])){
            $prototype_awards = $user_save_array['save_values']['prototype_awards'];
            if (!empty($prototype_awards['prototype_complete_light'])){ $complete_events_unlocked[] = 'dr-light'; }
            if (!empty($prototype_awards['prototype_complete_wily'])){ $complete_events_unlocked[] = 'dr-wily'; }
            if (!empty($prototype_awards['prototype_complete_cossack'])){ $complete_events_unlocked[] = 'dr-cossack'; }
        }
        $points_index['campaigns_completed'] = $complete_events_unlocked;
        $points_index['campaigns_completed_points'] = count($complete_events_unlocked) * 500000;
        $total_battle_points += $points_index['campaigns_completed_points'];
    }

    // Return calculated battle points
    $points_index['total_battle_points'] = $total_battle_points;
    return $total_battle_points;

}

// Define a function for calculating the battle's prototype points total
function mmrpg_prototype_calculate_battle_points($update_session = false, $_GAME = false){

    // Return the current point total for thisgame
    $session_token = mmrpg_game_token();
    if (empty($_GAME)){ $_GAME = &$_SESSION[$session_token]; }

    // Collect the user ID from the session
    $user_id = $_GAME['USER']['userid'];
    $total_battle_points = mmrpg_prototype_calculate_battle_points_2k19($user_id);

    // If requested, update the session variable with the new total
    if ($update_session){ $_GAME['counters']['battle_points'] = $total_battle_points; }

    // Return the collected battle points
    return $total_battle_points;
}

// Define a function that automatically refreshes the user's battle point total and ranking
function mmrpg_prototype_refresh_battle_points(){

    // Do not refresh anything unless this is a logged-in user
    if (rpg_game::is_user()){

        // Return the current point total for thisgame
        $session_token = mmrpg_game_token();
        if (empty($_GAME)){ $_GAME = &$_SESSION[$session_token]; }

        // Recalculate the overall battle points total with new values
        mmrpg_prototype_calculate_battle_points(true);

        // Save the game session
        mmrpg_save_game_session();

        // Collect and update the new rank based on point score
        global $this_boardinfo;
        $old_board_rank = $this_boardinfo['board_rank'];
        $new_board_rank = mmrpg_prototype_leaderboard_rank($_GAME['USER']['userid']);
        $_GAME['BOARD']['boardrank'] = $new_board_rank;
        $this_boardinfo['board_rank'] = $new_board_rank;

    }

}

// Define a function for calculating a player's prototype points total
function mmrpg_prototype_calculate_player_points($player_token, $update_session = false, $_GAME = false){

    // Return the current point total for this player
    $session_token = mmrpg_game_token();
    if (empty($_GAME)){ $_GAME = &$_SESSION[$session_token]; }

    // Start the battle points value at zero and increment
    $player_battle_points = 0;

    // If requested, update the session variable with new player rewards
    if ($update_session){ $_GAME['values']['battle_rewards'][$player_token]['player_points'] = $player_battle_points; }

    // Return the collected battle points
    return $player_battle_points;
}

// Define a function for checking the battle's prototype points total
function mmrpg_prototype_battle_points(){
    // Return the current point total for thisgame
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['counters']['battle_points'])){ return $_SESSION[$session_token]['counters']['battle_points']; }
    else { return 0; }
    }
// Define a function for checking a player's prototype points total
function mmrpg_prototype_player_points($player_token){
    // Return the current point total for this player
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_points'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_points']; }
    else { return 0; }
        }

// Define a function for checking a player's prototype rewards array
function mmrpg_prototype_player_rewards($player_token){
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]; }
    else { return array(); }
}

// Define a function for checking a player's prototype settings array
function mmrpg_prototype_player_settings($player_token){
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token])){ return $_SESSION[$session_token]['values']['battle_settings'][$player_token]; }
    else { return array(); }
}

// Define a function for checking a player's prototype settings array
function mmrpg_prototype_player_stars_available($player_token){
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();

    // Collect the omega factors from the session
    $temp_session_key = $player_token.'_target-robot-omega_prototype';
    if (empty($_SESSION[$session_token]['values'][$temp_session_key])){ return array('field' => 0, 'fusion' => 0); }
    $new_target_robot_omega = $_SESSION[$session_token]['values'][$temp_session_key];

    // Define the arrays to hold all available stars
    $temp_field_stars = array();
    $temp_fusion_stars = array();
    // Loop through and collect the field stars
    foreach ($new_target_robot_omega AS $key => $info){
        $temp_field_stars[] = $info['field'];
    }
    // Loop thourgh and collect the fusion stars
    for ($i = 0; $i < 8; $i += 2){
        list($t1a, $t1b) = explode('-', $temp_field_stars[$i]);
        list($t2a, $t2b) = explode('-', $temp_field_stars[$i + 1]);
        $temp_fusion_token = $t1a.'-'.$t2b;
        $temp_fusion_stars[] = $temp_fusion_token;
    }
    // Loop through field stars and remove unlocked
    foreach ($temp_field_stars AS $key => $token){
        if (!empty($_SESSION[$session_token]['values']['battle_stars'][$token])){
            unset($temp_field_stars[$key]);
        }
    }
    // Loop through fusion stars and remove unlocked
    foreach ($temp_fusion_stars AS $key => $token){
        if (!empty($_SESSION[$session_token]['values']['battle_stars'][$token])){
            unset($temp_fusion_stars[$key]);
        }
    }
    // Count the field stars
    $temp_field_stars = array_values($temp_field_stars);
    $temp_field_stars_count = count($temp_field_stars);
    // Count the fusion stars
    $temp_fusion_stars = array_values($temp_fusion_stars);
    $temp_fusion_stars_count = count($temp_fusion_stars);

    /*
    // DEBUG DEBUG
    die(
        '<pre>$temp_field_stars = '.print_r($temp_field_stars, true).'</pre><br />'.
        '<pre>$temp_fusion_stars = '.print_r($temp_fusion_stars, true).'</pre><br />'
        );
    */

    // Return the star counts
    return array('field' => $temp_field_stars_count, 'fusion' => $temp_fusion_stars_count);
}

// Define a function for checking a robot's prototype experience total
function mmrpg_prototype_robot_experience($player_token, $robot_token){
    // Return the current point total for this robot
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_experience'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_experience']; }
    elseif (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_points'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_points']; }
    else { return 0; }
}

// Define a function for checking a robot's prototype current level
function mmrpg_prototype_robot_level($player_token, $robot_token){
    // Return the current level total for this robot
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_level'])){ return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_level']; }
    else { return 1; }
}

// Define a function for checking a robot's prototype current level
function mmrpg_prototype_robot_original_player($player_token, $robot_token){
    // Return the current level total for this robot
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['original_player'])){ return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['original_player']; }
    else { return $player_token; }
}

// Define a function for checking a robot's prototype reward array
function mmrpg_prototype_robot_rewards($player_token = '', $robot_token){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    // Return the current reward array for this robot
    if (!empty($player_token)){
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])){
            return $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token];
        }
    } elseif (!empty($_SESSION[$session_token]['values']['battle_rewards'])){
        foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
            if (!empty($player_info['player_robots'][$robot_token])){
                return $player_info['player_robots'][$robot_token];
            }
        }
    }
    return array();
}

// Define a function for checking a robot's prototype settings array
function mmrpg_prototype_robot_settings($player_token = '', $robot_token){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    // Return the current setting array for this robot
    if (!empty($player_token)){
        if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
            return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token];
        }
    } elseif (!empty($_SESSION[$session_token]['values']['battle_settings'])){
        foreach ($_SESSION[$session_token]['values']['battle_settings'] AS $player_token => $player_info){
            if (!empty($player_info['player_robots'][$robot_token])){
                return $player_info['player_robots'][$robot_token];
            }
        }
    }
    return array();
}

// Define a function for checking a player's robot database array
function mmrpg_prototype_robot_database(){
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['robot_database'])){ return $_SESSION[$session_token]['values']['robot_database']; }
    else { return array(); }
}

// Define a function for checking a player's robot favourites array
function mmrpg_prototype_robot_favourites(){
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['robot_favourites'])){ return $_SESSION[$session_token]['values']['robot_favourites']; }
    else { return array(); }
}

// Define a function for checking a player's prototype rewards array
function mmrpg_prototype_robot_favourite($robot_token){
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();
    if (!isset($_SESSION[$session_token]['values']['robot_favourites'])){ $_SESSION[$session_token]['values']['robot_favourites'] = array(); }
    return in_array($robot_token, $_SESSION[$session_token]['values']['robot_favourites']) ? true : false;
}

// Define a function for checking if a prototype battle has been completed
function mmrpg_prototype_battle_complete($player_token, $battle_token){
    // Check if this battle has been completed and return true is it was
    $session_token = mmrpg_game_token();
    return isset($_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token][$battle_token] : false;
}

// Define a function for checking if a prototype battle has been failured
function mmrpg_prototype_battle_failure($player_token, $battle_token){
    // Check if this battle has been failured and return true is it was
    $session_token = mmrpg_game_token();
    return isset($_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token][$battle_token] : false;
}

// Define a function for checking is a prototype player has been unlocked
function mmrpg_prototype_player_unlocked($player_token){
    // Check if this battle has been completed and return true is it was
    $session_token = mmrpg_game_token();
    return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]) ? true : false;
}

// Define a function for checking is a prototype robot has been unlocked
function mmrpg_prototype_robot_unlocked($player_token, $robot_token){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    // If the player token was not false, check to see if that particular player has unlocked
    if (!empty($player_token)){
        // Check if this battle has been completed and return true is it was
        if (!empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])
            && !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
            return true;
        } else {
            return false;
        }
    }
    // Otherwise, loop through all robots and make sure no player has unlocked this robot
    else {
        // Loop through all the player tokens in the battle rewards
        $robot_unlocked = false;
        foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
            if (isset($player_info['player_robots'][$robot_token])
                && !empty($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token])
                && !empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token])){
                $robot_unlocked = true;
                break;
            }
        }
        return $robot_unlocked;
    }
}

// Define a function for checking if a prototype ability has been unlocked
function mmrpg_prototype_ability_unlocked($player_token, $robot_token = '', $ability_token = ''){

    // Define the game session helper var
    $session_token = mmrpg_game_token();

    // If a specific robot token was provided
    if (!empty($robot_token)){
        // Check if this ability has been unlocked by the specified robot and return true if it was
        return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities'][$ability_token]) ? true : false;
    } else {
        // Check if this ability has been unlocked by the player and return true if it was
        return in_array($ability_token, $_SESSION[$session_token]['values']['battle_abilities']) ? true : false;
    }

}

// Define a function for checking if a prototype item has been unlocked
function mmrpg_prototype_item_unlocked($item_token){

    // Define the game session helper var
    $session_token = mmrpg_game_token();

    // If items are not yet loaded, return false
    if (empty($_SESSION[$session_token]['values']['battle_items'])){ return false; }

    // If this specific item has not been unlocked, return false
    if (!isset($_SESSION[$session_token]['values']['battle_items'][$item_token])){ return false; }

    // Otherwise return true
    return true;

}

// Define a function for checking how many prototype items have been unlock
function mmrpg_prototype_items_unlocked($unique = true){

    // Define the game session helper var
    $session_token = mmrpg_game_token();

    // If items are not yet loaded, return false
    if (empty($_SESSION[$session_token]['values']['battle_items'])){ return 0; }

    // If unique item count was requested, simply return the array size
    if ($unique){ return count($_SESSION[$session_token]['values']['battle_items']); }

    // Otherwise if they want all items total, return the sum of array values
    elseif (!$unique){ return array_sum($_SESSION[$session_token]['values']['battle_items']); }

    // Otherwise return 0
    return 0;

}

// Define a function for checking if a prototype alt image has been unlocked
function mmrpg_prototype_altimage_unlocked($robot_token, $alt_token = ''){

    // Define the game session helper var
    $session_token = mmrpg_game_token();

    // If robot token not provided return false
    if (empty($robot_token)){ return false; }

    // If a specific robot token was provided
    if (!empty($robot_token) && !empty($alt_token)){

        // Check if this alt has been unlocked by the specified robot and return true if it was
        if (!isset($_SESSION[$session_token]['values']['robot_alts'][$robot_token])){ return false; }
        return in_array($alt_token, $_SESSION[$session_token]['values']['robot_alts'][$robot_token]) ? true : false;

    } elseif (!empty($robot_token)){

        // Return all the alt tokens unlocked by this robot
        if (!isset($_SESSION[$session_token]['values']['robot_alts'][$robot_token])){ return array(); }
        return $_SESSION[$session_token]['values']['robot_alts'][$robot_token];

    } else {

        // Definitely not unlocked
        return false;
    }

}

// Define a function for counting the number of completed prototype battles
function mmrpg_prototype_battles_complete($player_token, $unique = true){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    // Collect the battle complete count from the session if set
    $temp_battle_completes = isset($_SESSION[$session_token]['values']['battle_complete'][$player_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token] : array();
    //die(count($temp_battle_completes).'<br />'.print_r($temp_battle_completes, true));
    // Check if only unique battles were requested or ALL battles
    if (!empty($unique)){
     $temp_count = count($temp_battle_completes);
     return $temp_count;
    } else {
     $temp_count = 0;
     foreach ($temp_battle_completes AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
     return $temp_count;
    }
}
// Define a function for counting the number of failured prototype battles
function mmrpg_prototype_battles_failure($player_token, $unique = true){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    // Collect the battle failure count from the session if set
    $temp_battle_failures = isset($_SESSION[$session_token]['values']['battle_failure'][$player_token]) ? $_SESSION[$session_token]['values']['battle_failure'][$player_token] : array();
    // Check if only unique battles were requested or ALL battles
    if (!empty($unique)){
     $temp_count = count($temp_battle_failures);
     return $temp_count;
    } else {
     $temp_count = 0;
     foreach ($temp_battle_failures AS $info){ $temp_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
     return $temp_count;
    }
}
// Define a function for checking is a prototype player has been unlocked
function mmrpg_prototype_players_unlocked(){
    // Check if this battle has been completed and return true is it was
    $session_token = mmrpg_game_token();
    return isset($_SESSION[$session_token]['values']['battle_rewards']) ? count($_SESSION[$session_token]['values']['battle_rewards']) : 0;
}
// Define a function for checking is a prototype robot has been unlocked
function mmrpg_prototype_robots_unlocked($player_token = ''){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    if (!empty($player_token)){
        // Check if this battle has been completed and return true is it was
        return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots']) : 0;
    } else {
        $robot_counter = 0;
        foreach ($_SESSION[$session_token]['values']['battle_rewards'] AS $player_token => $player_info){
            $robot_counter += isset($player_info['player_robots']) ? count($player_info['player_robots']) : 0;
        }
        return $robot_counter;
    }

}
// Define a function for checking how many hearts have been unlocked by a player
function mmrpg_prototype_hearts_unlocked($player_token = ''){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    if (empty($_SESSION[$session_token]['counters']['battle_hearts'])){ $_SESSION[$session_token]['counters']['battle_hearts'] = array(); }
    if (!empty($player_token) && empty($_SESSION[$session_token]['counters']['battle_hearts'][$player_token])){ $_SESSION[$session_token]['counters']['battle_hearts'][$player_token] = 0; }
    if (!empty($player_token)){ return $_SESSION[$session_token]['counters']['battle_hearts'][$player_token]; }
    else {
     $temp_counter = 0;
     foreach ($_SESSION[$session_token]['counters']['battle_hearts'] AS $player_token => $heart_counter){ $temp_counter += $heart_counter; }
     return $temp_counter;
    }
}
// Define a function for checking is a prototype star has been unlocked
function mmrpg_prototype_star_unlocked($star_token){
    $session_token = mmrpg_game_token();
    if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return false; }
    elseif (empty($_SESSION[$session_token]['values']['battle_stars'][$star_token])){ return false; }
    else { return true; }
}
// Define a function for checking is a prototype star has been unlocked
function mmrpg_prototype_stars_unlocked($player_token = '', $star_kind = ''){
    // Define the game session helper var
    $session_token = mmrpg_game_token();
    if (empty($_SESSION[$session_token]['values']['battle_stars'])){ return 0; }
    else {
        $temp_stars_index = $_SESSION[$session_token]['values']['battle_stars'];
        if (empty($player_token) && empty($star_kind)){ return count($temp_stars_index); }
        foreach ($temp_stars_index AS $key => $info){
            if (!empty($player_token) && $info['star_player'] != $player_token){ unset($temp_stars_index[$key]); }
            elseif (!empty($star_kind) && $info['star_kind'] != $star_kind){ unset($temp_stars_index[$key]); }
        }
        return count($temp_stars_index);
    }
}

// Define a function that returns a list of all allowed fields
function mmrpg_prototype_unlocked_field_tokens(){

    // Collect the current session token
    $session_token = mmrpg_game_token();

    // Define an array to hold possible field tokens
    $unlocked_field_tokens = array();

    // Add the base fields given throughout the campaign
    global $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three;
    if (empty($this_omega_factors_one)){ require(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php'); }
    $base_omega_fields = array_merge($this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three);
    foreach ($base_omega_fields AS $key => $omega){ $unlocked_field_tokens[] = $omega['field']; }

    // Add any fields that have been manually unlocked to the list
    if (!empty($_SESSION[$session_token]['values']['battle_fields'])){ $unlocked_field_tokens += $_SESSION[$session_token]['values']['battle_fields']; }

    // Remove any duplicates that made their way through
    $unlocked_field_tokens = array_unique($unlocked_field_tokens);

    // Return the unlocked field tokens
    return $unlocked_field_tokens;

}

// Define a function for calculating all possible stars
function mmrpg_prototype_possible_stars($return_arrays = false){

    // Collect the current session token
    $session_token = mmrpg_game_token();

    // Collect an index of all fields for reference
    $mmrpg_index_fields = rpg_field::get_index();

    // Collect a list of all unlocked field tokens
    $unlocked_field_tokens = mmrpg_prototype_unlocked_field_tokens();

    // Loop through the field tokens to construct a list of field stars
    $possible_star_list = array();
    foreach ($unlocked_field_tokens AS $key1 => $field1_token){

        // Collect details about the first field
        $field1_token_parts = explode('-', $field1_token);
        $field1_info = $mmrpg_index_fields[$field1_token];

        // Define data for the field star of this particular field
        $possible_star_list[$field1_token] = array(
            'token' => $field1_token,
            'name' => $field1_info['field_name'],
            'kind' => 'field',
            'info1' => array('field' => $field1_token, 'robot' => $field1_info['field_master']),
            'info2' => false
            );

        // Loop through field tokens again to construct a list of fusion stars too
        foreach ($unlocked_field_tokens AS $key2 => $field2_token){

            // Collect details about the second field
            $field2_token_parts = explode('-', $field2_token);
            $field2_info = $mmrpg_index_fields[$field2_token];

            // Define data for the fusion star of this particular fusion field
            $fusion_token = $field1_token_parts[0].'-'.$field2_token_parts[1];
            if (isset($mmrpg_index_fields[$fusion_token])){ continue; }
            $possible_star_list[$fusion_token] = array(
                'token' => $fusion_token,
                'name' => ucwords(str_replace('-', ' ', $fusion_token)),
                'kind' => 'fusion',
                'info1' => array('field' => $field1_token, 'robot' => $field1_info['field_master']),
                'info2' => array('field' => $field2_token, 'robot' => $field2_info['field_master'])
                );

        }
    }

    // Return the list of possible field and fusion stars
    return $return_arrays ? $possible_star_list : array_keys($possible_star_list);

}


// Define a function for calculating which stars are remaining for a player
function mmrpg_prototype_remaining_stars($return_arrays = false){

    // Collect the current session token
    $session_token = mmrpg_game_token();

    // Collect the list of possible stars first for reference
    $remaining_star_list = mmrpg_prototype_possible_stars($return_arrays);

    // Remove from the above list any stars that have already been collected
    if (!empty($_SESSION[$session_token]['values']['battle_stars'])){
        $unlocked_star_list = array_keys($_SESSION[$session_token]['values']['battle_stars']);
        foreach ($unlocked_star_list AS $star_token){
            if ($return_arrays){ unset($remaining_star_list[$star_token]); }
            else { unset($remaining_star_list[array_search($star_token, $remaining_star_list)]); }
        }
    }

    // Return the list of remaining stars to collect
    return $remaining_star_list;

}

// Define a function for checking if a prototype ability has been unlocked
function mmrpg_prototype_abilities_unlocked($player_token = '', $robot_token = ''){
    // Pull in global variables
    //global $mmrpg_index;
    $mmrpg_index_players = $GLOBALS['mmrpg_index']['players'];
    $session_token = mmrpg_game_token();
    // If a specific robot token was provided
    if (!empty($player_token) && !empty($robot_token)){
        // Check if this battle has been completed and return true is it was
        return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) : 0;
    } elseif (!empty($player_token)){
        // Check if this ability has been unlocked by the player and return true if it was
        return isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) ? count($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) : 0;
    } else {
        // Define the ability counter and token tracker
        $ability_tokens = $_SESSION[$session_token]['values']['battle_abilities'];
        // Return the total amount of ability tokens pulled
        return !empty($ability_tokens) ? count($ability_tokens) : 0;
    }
}
// Define a function for displaying prototype battle option markup
function mmrpg_prototype_options_markup(&$battle_options, $player_token){
    // Refence the global config and index objects for easy access
    global $mmrpg_index, $db;
    $mmrpg_index_fields = rpg_field::get_index();

    // Define the variable to collect option markup
    $this_markup = '';

    // Collect the robot index for calculation purposes
    $db_robot_fields = rpg_robot::get_index_fields(true);
    $this_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

    // Count the number of completed battle options for this group and update the variable
    $battle_options_reversed = $battle_options; //array_reverse($battle_options);
    foreach ($battle_options_reversed AS $this_key => $this_info){

        // Define the chapter if not set
        if (!isset($this_info['option_chapter'])){ $this_info['option_chapter'] = '0'; }
        // If this is an event message type option, simply display the text/images
        if (!empty($this_info['option_type']) && $this_info['option_type'] == 'message'){

            // Generate the option markup for the event message
            $temp_optiontitle = $this_info['option_maintext'];
            $temp_optionimages = !empty($this_info['option_images']) ? $this_info['option_images'] : '';
            $temp_optiontext = '<span class="multi"><span class="maintext">'.$this_info['option_maintext'].'</span></span>';
            $this_markup .= '<a data-chapter="'.$this_info['option_chapter'].'" class="option option_message option_1x4 option_this-'.$player_token.'-message" style="'.(!empty($this_info['option_style']) ? $this_info['option_style'] : '').'"><div class="chrome"><div class="inset"><label class="'.(!empty($temp_optionimages) ? 'has_image' : '').'">'.$temp_optionimages.$temp_optiontext.'</label></div></div></a>'."\n";

        }
        // Otherwise, if this is a normal battle option
        else {

            // Collect the current battle and field info from the index
            $this_battleinfo = rpg_battle::get_index_info($this_info['battle_token']);
            if (!empty($this_battleinfo)){ $this_battleinfo = array_replace($this_battleinfo, $this_info); }
            else { $this_battleinfo = $this_info; }
            $this_fieldtoken = $this_battleinfo['battle_field_base']['field_token'];
            $this_fieldinfo =
                !empty($mmrpg_index_fields[$this_fieldtoken])
                ? array_replace(rpg_field::parse_index_info($mmrpg_index_fields[$this_fieldtoken]), $this_battleinfo['battle_field_base'])
                : $this_battleinfo['battle_field_base'];
            $this_targetinfo = !empty($mmrpg_index['players'][$this_battleinfo['battle_target_player']['player_token']]) ? array_replace($mmrpg_index['players'][$this_battleinfo['battle_target_player']['player_token']], $this_battleinfo['battle_target_player']) : $this_battleinfo['battle_target_player'];

            $is_player_battle = !empty($this_battleinfo['flags']['player_battle']) ? true : false;
            $is_battle_counts = isset($this_battleinfo['battle_counts']) && $this_battleinfo['battle_counts'] == false ? false : true;

            // Check the GAME session to see if this battle has been completed, increment the counter if it was
            $this_battleinfo['battle_option_complete'] = mmrpg_prototype_battle_complete($player_token, $this_info['battle_token']);
            $this_battleinfo['battle_option_failure'] = mmrpg_prototype_battle_failure($player_token, $this_info['battle_token']);

            // Generate the markup fields for display
            $this_option_token = $this_battleinfo['battle_token'];
            $this_option_limit = !empty($this_battleinfo['battle_robot_limit']) ? $this_battleinfo['battle_robot_limit'] : 8;
            $this_option_frame = !empty($this_battleinfo['battle_sprite_frame']) ? $this_battleinfo['battle_sprite_frame'] : 'base';
            $this_option_status = !empty($this_battleinfo['battle_status']) ? $this_battleinfo['battle_status'] : 'enabled';
            $this_option_zenny = !empty($this_battleinfo['battle_zenny']) ? $this_battleinfo['battle_zenny'] : 0;
            $this_option_complete = $this_battleinfo['battle_option_complete'];
            $this_option_failure = $this_battleinfo['battle_option_failure'];
            $this_option_targets = !empty($this_targetinfo['player_robots']) ? count($this_targetinfo['player_robots']) : 0;
            $this_option_encore = isset($this_battleinfo['battle_encore']) ? $this_battleinfo['battle_encore'] : true;
            $this_option_disabled = !empty($this_option_complete) && !$this_option_encore ? true : false;
            if (!empty($this_battleinfo['values']['field_star'])
                && !mmrpg_prototype_star_unlocked($this_battleinfo['values']['field_star']['star_token'])){
                $this_has_field_star = true;
                //$this_option_complete = false;
                $this_option_disabled = false;
            } else {
                $this_has_field_star = false;
            }

            //$this_option_class = 'option option_fieldback option_this-'.$player_token.'-battle-select option_'.$this_battleinfo['battle_size'].' option_'.$this_battleinfo['battle_token'].' option_'.$this_option_status.' block_'.($this_key + 1).' '.($this_option_complete && !$this_has_field_star ? 'option_complete ' : '').($this_option_disabled ? 'option_disabled '.($this_option_encore ? 'option_disabled_clickable ' : '') : '');
            $this_option_class = 'option option_fieldback option_this-'.$player_token.'-battle-select option_'.$this_battleinfo['battle_size'].' option_'.$this_option_status.' block_'.($this_key + 1).' '.($this_option_complete && !$this_has_field_star ? 'option_complete ' : '').($this_option_disabled ? 'option_disabled '.($this_option_encore ? 'option_disabled_clickable ' : '') : '');
            $this_option_style = 'background-position: -'.mt_rand(5, 50).'px -'.mt_rand(5, 50).'px; ';
            if (!empty($this_fieldinfo['field_type'])){
                $this_type_class = 'field_type field_type_'.$this_fieldinfo['field_type'].(!empty($this_fieldinfo['field_type2']) ? '_'.$this_fieldinfo['field_type2'] : '');
                $this_option_class .= $this_type_class;
            } else {
                $this_type_class = 'field_type field_type_none';
                $this_option_class .= $this_type_class;
            }
            if (!empty($this_fieldinfo['field_background'])){
                //$this_background_x = $this_background_y = -20;
                //$this_option_style = 'background-position: 0 0; background-size: 100% auto; background-image: url(images/fields/'.$this_fieldinfo['field_background'].'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                $this_option_style = 'background-image: url(images/fields/'.$this_fieldinfo['field_background'].'/battle-field_preview.png?'.MMRPG_CONFIG_CACHE_DATE.') !important; ';
            }
            $this_option_label = '';
            $this_option_platform_style = '';
            if (!empty($this_fieldinfo['field_foreground'])){
                //$this_background_x = $this_background_y = -20;
                //$this_option_platform_style = 'background-position: 0 -76px; background-size: 100% auto; background-image: url(images/fields/'.$this_fieldinfo['field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
                $this_option_platform_style = 'background-image: url(images/fields/'.$this_fieldinfo['field_foreground'].'/battle-field_foreground_base.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';
            }
            $this_option_min_level = false;
            $this_option_max_level = false;
            $this_battleinfo['battle_sprite'] = array();
            $this_targetinfo = !empty($mmrpg_index['players'][$this_targetinfo['player_token']]) ? array_merge($mmrpg_index['players'][$this_targetinfo['player_token']], $this_targetinfo) : $mmrpg_index['players']['player'];
            if ($this_targetinfo['player_token'] != 'player'){  $this_battleinfo['battle_sprite'][] = array('path' => 'players/'.$this_targetinfo['player_token'], 'size' => !empty($this_targetinfo['player_image_size']) ? $this_targetinfo['player_image_size'] : 40);  }
            if (!empty($this_targetinfo['player_robots'])){

                // Count the number of masters in this battle
                $this_master_count = 0;
                $this_mecha_count = 0;
                $temp_robot_tokens = array();
                foreach ($this_targetinfo['player_robots'] AS $robo_key => $this_robotinfo){
                    //if (empty($this_robotinfo['robot_token'])){ die('<pre>'.$this_battleinfo['battle_token'].print_r($this_robotinfo, true).'</pre>'); }
                    if ($this_robotinfo['robot_token'] == 'robot'){ unset($this_targetinfo['player_robots'][$robo_key]); continue; }
                    if (isset($this_robot_index[$this_robotinfo['robot_token']])){ $this_robotindex = rpg_robot::parse_index_info($this_robot_index[$this_robotinfo['robot_token']]); }
                    else { continue; }
                    $temp_robot_tokens[] = $this_robotinfo['robot_token'];
                    $this_robotinfo = array_merge($this_robotindex, $this_robotinfo);
                    $this_targetinfo['player_robots'][$robo_key] =  $this_robotinfo;
                    if (!empty($this_robotinfo['robot_class']) && $this_robotinfo['robot_class'] == 'mecha'){ $this_mecha_count++; }
                    elseif (empty($this_robotinfo['robot_class']) || $this_robotinfo['robot_class'] == 'master'){ $this_master_count++; }
                    unset($this_robotindex);
                }
                $temp_robot_tokens = array_unique($temp_robot_tokens);
                $temp_robot_tokens_count = count($temp_robot_tokens);
                $temp_robot_target_count = count($this_targetinfo['player_robots']);

                // Check to see if we're allowed to show robots on-screen
                $show_robot_targets = true;
                if (!empty($this_battleinfo['flags']['hide_robots_from_mission_select'])){ $show_robot_targets = false; }

                // Create a list of the different robot tokens in this battle
                // Now loop through robots again and display 'em
                foreach ($this_targetinfo['player_robots'] AS $this_robotinfo){

                    // HIDE MECHAS
                    if (empty($this_battleinfo['flags']['starter_battle']) && empty($this_battleinfo['flags']['player_battle'])
                        && !empty($this_robotinfo['robot_class']) && $this_robotinfo['robot_class'] == 'mecha'
                        && $temp_robot_tokens_count > 1 && $this_master_count > 0){ continue; }

                    // Update min/max level indicators
                    $this_robot_level = !empty($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : 1;
                    if ($this_option_min_level === false || $this_option_min_level > $this_robot_level){ $this_option_min_level = $this_robot_level; }
                    if ($this_option_max_level === false || $this_option_max_level < $this_robot_level){ $this_option_max_level = $this_robot_level; }

                    // HIDE HIDDEN
                    if (!$show_robot_targets || !empty($this_robotinfo['flags']['hide_from_mission_select'])){ continue; }

                    $this_robotinfo['robot_image'] = !empty($this_robotinfo['robot_image']) ? $this_robotinfo['robot_image'] : $this_robotinfo['robot_token'];
                    //if (!empty($this_robotinfo['flags']['hide_from_mission_select'])){ $temp_path = 'robots/robot'; }
                    //else { $temp_path = 'robots/'.$this_robotinfo['robot_image']; }

                    // Define the basic path for the robot image
                    $temp_path = 'robots/'.$this_robotinfo['robot_image'];

                    // Some robots should only show as shadows
                    $use_shadow = false;
                    if (!empty($this_robotinfo['flags']['shadow_on_mission_select'])){ $use_shadow = true; }

                    // Generate the final path variables for the mission button sprite
                    $this_battleinfo['battle_sprite'][] = array(
                        'path' => $temp_path,
                        'size' => (!empty($this_robotinfo['robot_image_size']) ? $this_robotinfo['robot_image_size'] : 40),
                        'shadow' => $use_shadow
                        );


                }

            }

            // Add the fusion star sprite if one has been added
            $this_has_field_star = false;
            if (!empty($this_battleinfo['values']['field_star'])){
                $this_has_field_star = true;
                //$this_option_complete = false;
                $this_option_disabled = false;
                // Check if this is a field star or fusion star
                $temp_star_data = $this_battleinfo['values']['field_star'];
                //die('<pre>'.print_r($temp_star_data, true).'</pre>');
                $temp_star_kind = $temp_star_data['star_kind'];
                // Collect the star image info from the index based on type
                $temp_field_type_1 = !empty($temp_star_data['star_type']) ? $temp_star_data['star_type'] : 'none';
                $temp_field_type_2 = !empty($temp_star_data['star_type2']) ? $temp_star_data['star_type2'] : $temp_field_type_1;
                if ($temp_star_kind == 'field'){
                    $temp_star_front = array('path' => 'items/field-star_'.$temp_field_type_1, 'frame' => '02', 'size' => 40);
                    $temp_star_back = array('path' => 'items/field-star_'.$temp_field_type_2, 'frame' => '01', 'size' => 40);
                } elseif ($temp_star_kind == 'fusion'){
                    $temp_star_front = array('path' => 'items/fusion-star_'.$temp_field_type_1, 'frame' => '02', 'size' => 40);
                    $temp_star_back = array('path' => 'items/fusion-star_'.$temp_field_type_2, 'frame' => '01', 'size' => 40);
                }
                array_unshift($this_battleinfo['battle_sprite'], $temp_star_front, $temp_star_back);

            }
            // Loop through the battle sprites and display them
            if (!empty($this_battleinfo['battle_sprite'])){
                $temp_right = false;
                $temp_layer = 100;
                $temp_count = count($this_battleinfo['battle_sprite']);
                $temp_last_size = 0;
                foreach ($this_battleinfo['battle_sprite'] AS $temp_key => $this_battle_sprite){
                    $temp_opacity = $temp_layer == 10 ? 1 : 1 - ($temp_key * 0.09);
                    $temp_path = $this_battle_sprite['path'];
                    $temp_size = $this_battle_sprite['size'];
                    $temp_shadow = isset($this_battle_sprite['shadow']) ? $this_battle_sprite['shadow'] : false;
                    $temp_other_styles = '';
                    if ($temp_shadow){
                        //$temp_other_styles .= 'filter: contrast(0%) brightness(0%); ';
                        $temp_other_styles .= '-webkit-filter: grayscale(100%); filter: grayscale(100%); ';
                        $temp_opacity *= 0.5;
                        }
                    if (preg_match('/^robots/i', $temp_path)
                        && $this_targetinfo['player_id'] != MMRPG_SETTINGS_TARGET_PLAYERID){
                        $temp_path = 'robots/robot';
                        $temp_size = 40;
                        }
                    $temp_frame = !empty($this_battle_sprite['frame']) ? $this_battle_sprite['frame'] : '';
                    $temp_size_text = $temp_size.'x'.$temp_size;
                    $temp_top = -2 + (40 - $temp_size);
                    if (!preg_match('/^(abilities|items)/i', $temp_path)){
                        if ($temp_right === false){
                            if ($temp_size == 40){
                                $temp_right_inc =  0;
                                $temp_right = 18 + $temp_right_inc;
                            } else {
                                $temp_right_inc =  -1 * ceil(($temp_size - 40) * 0.5);
                                $temp_right = 18 + $temp_right_inc;
                            }
                        } else {
                            if ($temp_size == 40){
                                $temp_right_inc = ceil($temp_size * 0.5);
                                $temp_right += $temp_right_inc;
                            } else {
                                $temp_right_inc = ceil(($temp_size - 40) * 0.5); //ceil($temp_size * 0.5);
                                $temp_right += $temp_right_inc;
                            }
                            if ($temp_size > $temp_last_size){
                                $temp_right -= ceil(($temp_size - $temp_last_size) / 2);
                            } elseif ($temp_size < $temp_last_size){
                                $temp_right += ceil(($temp_last_size - $temp_size) / 2);
                            }
                        }
                    } else {
                        $temp_right = 5;
                    }
                    if (preg_match('/^(abilities|items)/i', $temp_path)){
                        $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_'.str_pad($temp_frame, 2, '0', STR_PAD_LEFT).' " style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: 1px; right: -3px; z-index: '.$temp_layer.'; opacity: '.$temp_opacity.'; '.$temp_other_styles.'">&nbsp;</span>';
                    } else {
                        $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' '.($this_option_complete && !$this_has_field_star && $this_option_frame == 'base' ? 'sprite_'.$temp_size_text.'_defeat ' : 'sprite_'.$temp_size_text.'_'.$this_option_frame.' ').'" style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_top.'px; right: '.$temp_right.'px; z-index: '.$temp_layer.'; opacity: '.$temp_opacity.'; '.$temp_other_styles.'">&nbsp;</span>';
                    }
                    $temp_layer -= 1;
                    $temp_last_size = $temp_size;
                }
            }

            if (!empty($this_battleinfo['battle_button'])){ $this_option_button_text = $this_battleinfo['battle_button']; }
            elseif (!empty($this_fieldinfo['field_name'])){ $this_option_button_text = $this_fieldinfo['field_name']; }
            else { $this_option_button_text = 'Battle'; }

            if ($this_option_min_level < 1){ $this_option_min_level = 1; }
            if ($this_option_max_level > 100){ $this_option_max_level = 100; }
            $this_option_level_range = $this_option_min_level == $this_option_max_level ? 'Level '.$this_option_min_level : 'Levels '.$this_option_min_level.'-'.$this_option_max_level;

            $this_option_zenny_amount = number_format($this_option_zenny, 0, '.', ',').' Zenny';

            $this_option_label .= (
                !empty($this_option_button_text)
                    ? '<span class="multi"><span class="maintext">'.$this_option_button_text.'</span><span class="subtext">'.$this_option_level_range.'</span><span class="subtext2">'.$this_option_zenny_amount.'</span></span>'.(!$this_has_field_star && (!$this_option_complete || ($this_option_complete && $this_option_encore)) ? '<span class="arrow"> &#9658;</span>' : '')
                    : '<span class="single">???</span>'
                    );

            // Generate this options hover tooltip details
            $this_option_title = ''; //$this_battleinfo['battle_button'];
            //$this_option_title .= '$this_master_count = '.$this_master_count.'; $this_mecha_count = '.$this_mecha_count.'; ';
            //if ($this_battleinfo['battle_button'] != $this_battleinfo['battle_name']){ $this_option_title .= ' | '.$this_battleinfo['battle_name']; }
            $this_option_title .= '&laquo; '.$this_battleinfo['battle_name'].' &raquo;';
            $this_option_title .= ' <br />'.$this_fieldinfo['field_name'];
            if (!empty($this_fieldinfo['field_type'])){
                if (!empty($this_fieldinfo['field_type2'])){ $this_option_title .= ' | '.ucfirst($this_fieldinfo['field_type']).' / '.ucfirst($this_fieldinfo['field_type2']).' Type'; }
                else { $this_option_title .= ' | '.ucfirst($this_fieldinfo['field_type']).' Type'; }
            }
            $this_option_title .= ' <br />'.$this_option_level_range;

            if (!empty($this_battleinfo['battle_zenny'])){
                $this_option_title .= ' | '.($this_battleinfo['battle_zenny'] == 1 ? '1 Zenny' : number_format($this_battleinfo['battle_zenny'], 0, '.', ',').' Zenny');
            }

            $this_option_title .= ' | '.($this_battleinfo['battle_turns'] == 1 ? '1 Turn' : $this_battleinfo['battle_turns'].' Turns');
            $this_option_title .= ' <br />'.$this_battleinfo['battle_description'].(!empty($this_battleinfo['battle_description2']) ? ' '.$this_battleinfo['battle_description2'] : '');
            if (!isset($this_battleinfo['battle_counts'])
                || $this_battleinfo['battle_counts'] !== false){
                if (!empty($this_option_complete) || !empty($this_option_failure) || !empty($this_has_field_star)){
                    $this_option_title .= ' <hr />&laquo; Battle Records &raquo;';
                    $this_option_title .= ' <br />Cleared : '.(!empty($this_option_complete['battle_count']) ? ($this_option_complete['battle_count'] == 1 ? '1 Time' : $this_option_complete['battle_count'].' Times') : '0 Times');
                    $this_option_title .= ' | Failed : '.(!empty($this_option_failure['battle_count']) ? ($this_option_failure['battle_count'] == 1 ? '1 Time' : $this_option_failure['battle_count'].' Times') : '0 Times');
                }
            }
            $this_option_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_option_title));
            $this_option_title_tooltip = htmlentities($this_option_title, ENT_QUOTES, 'UTF-8');
            $this_option_title_tooltip = str_replace('&#039;', "'", $this_option_title_tooltip);

            $this_option_description = $this_battleinfo['battle_description'];
            if (!empty($this_battleinfo['battle_description2'])){ $this_option_description .= $this_battleinfo['battle_description2']; }
            $this_option_description = htmlentities($this_option_description, ENT_QUOTES, 'UTF-8');
            $this_option_description = str_replace('&#039;', "'", $this_option_description);

            // Define the field multipliers
            $temp_field_multipliers = array();
            if (!empty($this_fieldinfo['field_multipliers'])){
                $temp_multiplier_list = $this_fieldinfo['field_multipliers'];
                asort($temp_multiplier_list);
                $temp_multiplier_list = array_reverse($temp_multiplier_list, true);
                foreach ($temp_multiplier_list AS $temp_type => $temp_multiplier){
                    if ($temp_multiplier == 1){ continue; }
                    $temp_field_multipliers[] = $temp_type.'*'.number_format($temp_multiplier, 1);
                }
            }
            $temp_field_multipliers = !empty($temp_field_multipliers) ? implode('|', $temp_field_multipliers) : '';


            // Check if this is a starfield mission or not
            $is_starfield_mission = !empty($this_battleinfo['flags']['starfield_mission']) ? true : false;
            if ($is_starfield_mission){ $this_option_class .= ' starfield'; }

            // Print out the option button markup with sprite and name
            $this_markup .= '<a '.
                'class="'.$this_option_class.'" '.
                'data-token="'.$this_option_token.'" '.
                'data-next-limit="'.$this_option_limit.'" '.
                'data-chapter="'.$this_info['option_chapter'].'" '.
                'data-tooltip="'.$this_option_title_tooltip.'" '.
                'data-field="'.htmlentities($this_fieldinfo['field_name'], ENT_QUOTES, 'UTF-8', true).'" '.
                'data-description="'.htmlentities(($this_battleinfo['battle_description'].(!empty($this_battleinfo['battle_description2']) ? ' '.$this_battleinfo['battle_description2'] : '')), ENT_QUOTES, 'UTF-8', true).'" '.
                'data-multipliers="'.$temp_field_multipliers.'" '.
                'data-background="'.(!empty($this_fieldinfo['field_background']) ? $this_fieldinfo['field_background'] : '').'" '.
                'data-foreground="'.(!empty($this_fieldinfo['field_foreground']) ? $this_fieldinfo['field_foreground'] : '').'" '.
                'style="'.$this_option_style.(!empty($this_info['option_style']) ? ' '.$this_info['option_style'] : '').'" '.
                '>';
                $this_markup .= '<div class="platform" style="'.$this_option_platform_style.'">';
                    $this_markup .= '<div class="chrome">';
                        $this_markup .= '<div class="inset">';
                            $this_markup .= '<label class="'.(!empty($this_battleinfo['battle_sprite']) ? 'has_image' : 'no_image').'">';
                                $this_markup .= $this_option_label;
                            $this_markup .= '</label>';
                        $this_markup .= '</div>';
                    $this_markup .= '</div>';
                $this_markup .= '</div>';
            $this_markup .= '</a>';
            $this_markup .= "\r\n";
            // Update the main battle option array with recent changes
            $this_battleinfo['flag_skip'] = true;
            $battle_options[$this_key] = $this_battleinfo;

        }

    }
    // Return the generated markup
    return $this_markup;
}

// Define a function for generating option message markup
function mmrpg_prototype_option_message_markup($player_token, $subject, $lineone, $linetwo, $sprites = ''){
    $temp_optiontext = '<span class="multi"><span class="maintext">'.$subject.'</span><span class="subtext">'.$lineone.'</span><span class="subtext2">'.$linetwo.'</span></span>';
    return '<a class="option option_1x4 option_this-'.$player_token.'-select option_message "><div class="chrome"><div class="inset"><label class="'.(!empty($sprites) ? 'has_image' : '').'">'.$sprites.$temp_optiontext.'</label></div></div></a>'."\n";
}

// Define a function for generating an ability set for a given robot
require(MMRPG_CONFIG_ROOTDIR.'functions/prototype_generate-abilities.php');

// Define a function for sorting the omega player robots
function mmrpg_prototype_sort_player_robots($info1, $info2){
    $info1_robot_level = $info1['robot_level'];
    $info2_robot_level = $info2['robot_level'];
    $info1_robot_favourite = isset($info1['values']['flag_favourite']) ? $info1['values']['flag_favourite'] : 0;
    $info2_robot_favourite = isset($info2['values']['flag_favourite']) ? $info2['values']['flag_favourite'] : 0;
    if ($info1_robot_favourite < $info2_robot_favourite){ return 1; }
    elseif ($info1_robot_favourite > $info2_robot_favourite){ return -1; }
    elseif ($info1_robot_level < $info2_robot_level){ return 1; }
    elseif ($info1_robot_level > $info2_robot_level){ return -1; }
    else { return 0; }
}

// Define a function to sort prototype robots based on their current level / experience points
function mmrpg_prototype_sort_robots_experience($info1, $info2){
    global $this_prototype_data;
    $info1_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info1['robot_token']);
    $info1_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info1['robot_token']);
    $info2_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info2['robot_token']);
    $info2_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info2['robot_token']);
    if ($info1_robot_level < $info2_robot_level){ return 1; }
    elseif ($info1_robot_level > $info2_robot_level){ return -1; }
    elseif ($info1_robot_experience < $info2_robot_experience){ return 1; }
    elseif ($info1_robot_experience > $info2_robot_experience){ return -1; }
    else { return 0; }
}


// Define a function to sort prototype robots based on their current level / experience points
function mmrpg_prototype_sort_robots_position($info1, $info2){
    global $this_prototype_data;
    static $this_robot_favourites;
    if (empty($this_robot_favourites)){ $this_robot_favourites = mmrpg_prototype_robot_favourites(); }
    $temp_player_settings = mmrpg_prototype_player_settings($this_prototype_data['this_player_token']);
    $info1_robot_position = array_search($info1['robot_token'], array_keys($temp_player_settings['player_robots']));
    $info2_robot_position = array_search($info2['robot_token'], array_keys($temp_player_settings['player_robots']));
    $info1_robot_favourite = in_array($info1['robot_token'], $this_robot_favourites) ? 1 : 0;
    $info2_robot_favourite = in_array($info2['robot_token'], $this_robot_favourites) ? 1 : 0;
    if ($info1_robot_favourite < $info2_robot_favourite){ return 1; }
    elseif ($info1_robot_favourite > $info2_robot_favourite){ return -1; }
    elseif ($info1_robot_position < $info2_robot_position){ return -1; }
    elseif ($info1_robot_position > $info2_robot_position){ return 1; }
    else { return 0; }
}


// Define a function for displaying prototype robot button markup on the select screen
function mmrpg_prototype_robot_select_markup($this_prototype_data){
    global $db, $mmrpg_index;

    // Define the temporary robot markup string
    $this_robots_markup = '';

    // Collect this player's index info
    $this_player_info = $mmrpg_index['players'][$this_prototype_data['this_player_token']];

    // Collect the list of robot and ability tokens we'll need
    $rtokens = array();
    $atokens = array();
    foreach ($this_prototype_data['robot_options'] AS $key => $info){
        $rtokens[] = $info['robot_token'];
        if (!empty($info['robot_abilities'])){
            foreach ($info['robot_abilities'] AS $key => $info){
                $atokens[] = $info['ability_token'];
            }
        }
    }
    $rtokens = array_unique($rtokens);
    $atokens = array_unique($atokens);

    // Collect the robot, ability, and item indexes for display purposes
    $this_robot_index = rpg_robot::get_index_custom($rtokens);
    $this_ability_index = rpg_ability::get_index_custom($atokens);
    $this_item_index = rpg_item::get_index();

    // Loop through and display the available robot options for this player
    $temp_robot_option_count = count($this_prototype_data['robot_options']);
    $temp_player_favourites = mmrpg_prototype_robot_favourites();
    foreach ($this_prototype_data['robot_options'] AS $key => $info){
        $info = array_merge($this_robot_index[$info['robot_token']], $info);
        if (!isset($info['original_player'])){ $info['original_player'] = $this_prototype_data['this_player_token']; }
        $this_option_class = 'option option_this-robot-select option_this-'.$info['original_player'].'-robot-select option_'.($temp_robot_option_count == 1 ? '1x4' : ($this_prototype_data['robots_unlocked'] <= 2 ? '1x2' : '1x1')).' option_'.$info['robot_token'].' block_'.($key + 1);
        $this_option_style = '';
        $this_option_token = $info['robot_id'].'_'.$info['robot_token'];
        $this_option_image = !empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token'];
        $this_option_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
        $temp_size = $this_option_size;
        $temp_size_text = $temp_size.'x'.$temp_size;
        $temp_top = -2 + (40 - $temp_size);
        $temp_right_inc = $temp_size > 40 ? ceil(($temp_size * 0.5) - 60) : 0;
        $temp_right = 15 + $temp_right_inc;
        $this_robot_name = $info['robot_name'];
        $this_robot_rewards = mmrpg_prototype_robot_rewards($this_prototype_data['this_player_token'], $info['robot_token']);
        $this_robot_settings = mmrpg_prototype_robot_settings($this_prototype_data['this_player_token'], $info['robot_token']);
        $this_robot_experience = mmrpg_prototype_robot_experience($this_prototype_data['this_player_token'], $info['robot_token']);
        $this_robot_level = mmrpg_prototype_robot_level($this_prototype_data['this_player_token'], $info['robot_token']);
        $this_robot_abilities = mmrpg_prototype_abilities_unlocked($this_prototype_data['this_player_token'], $info['robot_token']);
        $text_robot_special = $this_robot_level >= 100 || !empty($this_robot_rewards['flags']['reached_max_level']) ? true : false;
        $this_robot_experience = $this_robot_level >= 100 ? '<span style="position: relative; bottom: 0; font-size: 120%;">&#8734;</span>' : $this_robot_experience;
        $this_robot_experience_title = $this_robot_level >= 100 ? '&#8734;' : $this_robot_experience;
        $this_robot_core = !empty($info['robot_core']) ? $info['robot_core'] : '';
        $this_robot_core2 = !empty($info['robot_core2']) ? $info['robot_core2'] : '';
        $this_robot_item = !empty($info['robot_item']) ? $info['robot_item'] : '';

        $this_robot_favourite = in_array($info['robot_token'], $temp_player_favourites) ? true : false;
        $this_robot_name .= $this_robot_favourite ? ' <span class="icons favs">&hearts;</span>' : '';

        // Collect starforce values for the current player
        $player_starforce = rpg_game::starforce_unlocked();

        // Calculate this robot's current and max stat values
        $this_robot_stats = rpg_robot::calculate_stat_values($this_robot_level, $info, $this_robot_rewards, true, $this_robot_core, $player_starforce);
        $this_robot_energy = $this_robot_stats['energy']['current'];
        $this_robot_attack = $this_robot_stats['attack']['current'];
        $this_robot_defense = $this_robot_stats['defense']['current'];
        $this_robot_speed = $this_robot_stats['speed']['current'];

        // Update the robot's image if in the settings
        if (isset($this_robot_settings['robot_image'])){
            $this_option_image = $this_robot_settings['robot_image'];
        }
        // Update the robot's item if in the settings
        if (!empty($this_robot_settings['robot_item'])){
            $this_robot_item = $this_robot_settings['robot_item'];
        }
        // Update the robot's second core if they're holding one
        if (!empty($this_robot_item) && preg_match('/-core$/i', $this_robot_item)){
            $item_core_type = preg_replace('/-core$/i', '', $this_robot_item);
            if (empty($this_robot_core2)){
                $this_robot_core2 = $item_core_type;
            }
        }

        $starcount = 0;
        $bullcount = 0;
        $namestring = '';
        $level_max = false;
        if ($this_robot_stats['level'] >= $this_robot_stats['level_max']){ $starcount++; $level_max = true; }
        //if ($this_robot_stats['energy']['bonus'] >= $this_robot_stats['energy']['bonus_max']){ if ($level_max){ $starcount++; } else { $bullcount++; } }
        if ($this_robot_stats['attack']['bonus'] >= $this_robot_stats['attack']['bonus_max']){ if ($level_max){ $starcount++; } else { $bullcount++; } }
        if ($this_robot_stats['defense']['bonus'] >= $this_robot_stats['defense']['bonus_max']){ if ($level_max){ $starcount++; } else { $bullcount++; } }
        if ($this_robot_stats['speed']['bonus'] >= $this_robot_stats['speed']['bonus_max']){ if ($level_max){ $starcount++; } else { $bullcount++; } }
        for ($i = 0; $i < $starcount; $i++){ $namestring .= '&#9733;'; }
        for ($i = 0; $i < $bullcount; $i++){ $namestring .= '&bull;'; }
        $this_robot_name .= !empty($namestring) ? ' <span class="icons stats">'.$namestring.'</span>' : '';

        if (!empty($this_player_info['player_energy'])){ $this_robot_energy += ceil(($this_player_info['player_energy'] / 100) * $this_robot_energy); }
        if (!empty($this_player_info['player_attack'])){ $this_robot_attack += ceil(($this_player_info['player_attack'] / 100) * $this_robot_attack); }
        if (!empty($this_player_info['player_defense'])){ $this_robot_defense += ceil(($this_player_info['player_defense'] / 100) * $this_robot_defense); }
        if (!empty($this_player_info['player_speed'])){ $this_robot_speed += ceil(($this_player_info['player_speed'] / 100) * $this_robot_speed); }

        $this_robot_abilities_current = !empty($info['robot_abilities']) ? array_keys($info['robot_abilities']) : array('buster-shot');
        $this_option_title = ''; //-- Basics -------------------------------  <br />';
        $this_option_title .= $info['robot_name']; //''.$info['robot_number'].' '.$info['robot_name'];
        $this_option_title .= ' ('.(!empty($info['robot_core']) ? ucfirst($info['robot_core']).' Core' : 'Neutral Core').')';
        $this_option_title .= ' <br />Level '.$this_robot_level.($this_robot_level >= 100 ? ' &#9733;' : '');
        $this_option_title .= ' | '.$this_robot_experience_title.'/1000 Exp'.(!empty($this_robot_favourite_title) ? ' '.$this_robot_favourite_title : '');
        if (!empty($this_robot_item) && isset($this_item_index[$this_robot_item])){ $this_option_title .= ' | + '.$this_item_index[$this_robot_item]['item_name'].' '; }
        $this_option_title .= ' <br />E: '.$this_robot_energy; //.($this_robot_stats['energy']['bonus'] >= $this_robot_stats['energy']['bonus_max'] ? ($level_max ? ' &#9733;' : ' &bull;') : '');
        $this_option_title .= ' | A: '.$this_robot_attack.($this_robot_stats['attack']['bonus'] >= $this_robot_stats['attack']['bonus_max'] ? ($level_max ? ' &#9733;' : ' &bull;') : '');
        $this_option_title .= ' | D: '.$this_robot_defense.($this_robot_stats['defense']['bonus'] >= $this_robot_stats['defense']['bonus_max'] ? ($level_max ? ' &#9733;' : ' &bull;') : '');
        $this_option_title .= ' | S: '.$this_robot_speed.($this_robot_stats['speed']['bonus'] >= $this_robot_stats['speed']['bonus_max'] ? ($level_max ? ' &#9733;' : ' &bull;') : '');
        if (!empty($this_robot_abilities_current)){
            $this_option_title .= ' <hr />'; // <hr />-- Abilities ------------------------------- <br />';
            $temp_counter = 1;
            foreach ($this_robot_abilities_current AS $token){
                if (empty($token) || !isset($this_ability_index[$token])){ continue; }
                $temp_info = rpg_ability::parse_index_info($this_ability_index[$token]);
                $this_option_title .= $temp_info['ability_name'];
                if ($temp_counter % 4 == 0){ $this_option_title .= ' <br />'; }
                elseif ($temp_counter < count($this_robot_abilities_current)){ $this_option_title .= ' | '; }
                $temp_counter++;
            }
        }
        $this_option_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_option_title));
        $this_option_title_tooltip = htmlentities($this_option_title, ENT_QUOTES, 'UTF-8');
        $this_option_label = '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(images/robots/'.$this_option_image.'/sprite_right_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_top.'px; right: '.$temp_right.'px;">'.$info['robot_name'].'</span><span class="multi"><span class="maintext">'.$this_robot_name.'</span><span class="subtext">Level '.$this_robot_level.'</span><span class="subtext2">'.$this_robot_experience.'/1000 Exp</span></span><span class="arrow">&#9658;</span>';
        //$this_robots_markup .= '<a class="'.$this_option_class.'" data-child="true" data-token="'.$this_option_token.'" title="'.$this_option_title_plain.'" data-tooltip="'.$this_option_title_tooltip.'" style="'.$this_option_style.'">';
        $this_robots_markup .= '<a class="'.$this_option_class.'" data-child="true" data-token="'.$this_option_token.'" style="'.$this_option_style.'">';
        $this_robots_markup .= '<div class="chrome chrome_type robot_type_'.(!empty($this_robot_core) ? $this_robot_core : 'none').(!empty($this_robot_core2) ? '_'.$this_robot_core2 : '').'" data-tooltip="'.$this_option_title_tooltip.'"><div class="inset"><label class="has_image">'.$this_option_label.'</label></div></div>';
        $this_robots_markup .= '</a>'."\r\n";
    }

    // Loop through and display any option padding cells
    //if ($this_prototype_data['robots_unlocked'] >= 3){
    if ($temp_robot_option_count >= 3){
        //$this_prototype_data['padding_num'] = $this_prototype_data['robots_unlocked'] <= 8 ? 4 : 2;
        $this_prototype_data['padding_num'] = 4;
        $this_prototype_data['robots_padding'] = $temp_robot_option_count % $this_prototype_data['padding_num'];
        if (!empty($this_prototype_data['robots_padding'])){
            $counter = ($temp_robot_option_count % $this_prototype_data['padding_num']) + 1;
            for ($counter; $counter <= $this_prototype_data['padding_num']; $counter++){
                $this_option_class = 'option option_this-robot-select option_this-'.$this_prototype_data['this_player_token'].'-robot-select option_1x1 option_disabled block_'.$counter;
                $this_option_style = '';
                $this_robots_markup .= '<a class="'.$this_option_class.'" style="'.$this_option_style.'">';
                $this_robots_markup .= '<div class="platform"><div class="chrome"><div class="inset"><label>&nbsp;</label></div></div></div>';
                $this_robots_markup .= '</a>'."\r\n";
            }
        }
    }

    // Return the generated robot markup
    return $this_robots_markup;

}


// Define the field star image function for use in other parts of the game
function mmrpg_prototype_star_image($type){
    static $type_order = array('none', 'copy', 'crystal', 'cutter', 'earth',
        'electric', 'explode', 'flame', 'freeze', 'impact',
        'laser', 'missile', 'nature', 'shadow', 'shield',
        'space', 'swift', 'time', 'water', 'wind');
    $type_sheet = 1;
    $type_frame = array_search($type, $type_order);
    if ($type_frame >= 10){
        $type_sheet = 2;
        $type_frame = $type_frame - 10;
    } elseif ($type_frame < 0){
        $type_sheet = 1;
        $type_frame = 0;
    }
    $temp_array = array('sheet' => $type_sheet, 'frame' => $type_frame);
    //echo('type:'.$type.'; '.print_r($temp_array, true).'<br />');
    return $temp_array;
}

// Define a function for pulling the leaderboard players index
function mmrpg_prototype_leaderboard_index(){
    global $db;
    // Check to see if the leaderboard index has already been pulled or not
    if (!empty($db->INDEX['LEADERBOARD']['index'])){
        $this_leaderboard_index = json_decode($db->INDEX['LEADERBOARD']['index'], true);
    } else {
        // Define the array for pulling all the leaderboard data
        $temp_leaderboard_query = 'SELECT
            mmrpg_users.user_id,
            mmrpg_users.user_name,
            mmrpg_users.user_name_clean,
            mmrpg_users.user_name_public,
            mmrpg_users.user_date_accessed,
            mmrpg_leaderboard.board_points
            FROM mmrpg_users
            LEFT JOIN mmrpg_leaderboard ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
            WHERE mmrpg_leaderboard.board_points > 0 ORDER BY mmrpg_leaderboard.board_points DESC
            ';
        // Query the database and collect the array list of all online players
        $this_leaderboard_index = $db->get_array_list($temp_leaderboard_query);
        // Update the database index cache
        $db->INDEX['LEADERBOARD']['index'] = json_encode($this_leaderboard_index);
    }
    // Return the collected leaderboard index
    return $this_leaderboard_index;
}

// Define a function for collecting the requested player's board ranking
function mmrpg_prototype_leaderboard_rank($user_id){
    global $db;

    // Generate the query for selecting this user's rank
    $rank_query = "SELECT
        uo.user_id,
        uo.board_points,
        (SELECT
            COUNT(DISTINCT ui.board_points)
            FROM mmrpg_leaderboard AS ui
            WHERE
            ui.board_points >= uo.board_points
            AND ui.user_id <> uo.user_id
            ) AS user_rank
        FROM mmrpg_leaderboard uo
        WHERE
        user_id = {$user_id} AND
        uo.board_points > 0
        ;";

    // Query the database for this user's specific ranking
    $rank_info = $db->get_array($rank_query);

    // Return the user's rank if not empty
    if (!empty($rank_info['user_rank'])){ return (int)($rank_info['user_rank']); }
    // Otherwise, simply return a zero rank
    else { return 0; }

}

// Define a function for collecting the requested player's legacy board ranking (2k16 or 2k19)
function mmrpg_prototype_leaderboard_rank_legacy($user_id, $year_token = 2016){
    global $db;

    // Define the legacy field name based on year
    if ($year_token === 2016){ $legacy_field = 'board_points_legacy'; }
    elseif ($year_token === 2019){ $legacy_field = 'board_points_legacy2'; }
    else { return 0; }

    // Generate the query for selecting this user's rank
    $rank_query = "SELECT
        uo.user_id,
        uo.{$legacy_field},
        (SELECT
            COUNT(DISTINCT ui.{$legacy_field})
            FROM mmrpg_leaderboard AS ui
            WHERE
            ui.{$legacy_field} >= uo.{$legacy_field}
            ) AS user_rank
        FROM mmrpg_leaderboard AS uo
        WHERE
        user_id = {$user_id} AND
        uo.{$legacy_field} > 0
        ;";

    // Query the database for this user's specific ranking
    $rank_info = $db->get_array($rank_query);

    // Return the user's rank if not empty
    if (!empty($rank_info['user_rank'])){ return (int)($rank_info['user_rank']); }
    // Otherwise, simply return a zero rank
    else { return 0; }

}

// Define a function for pulling the leaderboard online player
function mmrpg_prototype_leaderboard_online(){
    global $db;
    // Check to see if the leaderboard online has already been pulled or not
    if (!empty($db->INDEX['LEADERBOARD']['online'])){
        $this_leaderboard_online_players = json_decode($db->INDEX['LEADERBOARD']['online'], true);
    } else {
        // Collect the leaderboard index for ranking
        $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
        // Generate the points index and then break it down to unique for ranks
        $this_points_index = array();
        foreach ($this_leaderboard_index AS $info){ $this_points_index[] = $info['board_points']; }
        $this_points_index = array_unique($this_points_index);
        // Define the vars for finding the online players
        $this_time = time();
        $this_online_timeout = MMRPG_SETTINGS_ONLINE_TIMEOUT;
        // Loop through the collected index and pull online players
        $this_leaderboard_online_players = array();
        if (!empty($this_leaderboard_index)){
            foreach ($this_leaderboard_index AS $key => $board){
                if (!empty($board['user_date_accessed']) && (($this_time - $board['user_date_accessed']) <= $this_online_timeout)){
                    $temp_userid = !empty($board['user_id']) ? $board['user_id'] : 0;
                    $temp_usertoken = $board['user_name_clean'];
                    $temp_username = !empty($board['user_name_public']) ? $board['user_name_public'] : $board['user_name'];
                    $temp_username = htmlentities($temp_username, ENT_QUOTES, 'UTF-8', true);
                    $temp_points = !empty($board['board_points']) ? $board['board_points'] : 0;
                    $temp_place = array_search($board['board_points'], $this_points_index) + 1;
                    $this_leaderboard_online_players[] = array('id' => $temp_userid, 'name' => $temp_username, 'token' => $temp_usertoken, 'points' => $temp_points, 'place' => $temp_place);
                }
            }
        }
        // Update the database index cache
        $db->INDEX['LEADERBOARD']['online'] = json_encode($this_leaderboard_online_players);
    }
    // Return the collected online players if any
    return $this_leaderboard_online_players;
}

// Define a function for pulling the leaderboard targets
function mmrpg_prototype_leaderboard_targets($this_userid, $player_robot_sort = '', &$this_leaderboard_defeated_players = array()){
    global $db;
    // Check to see if the leaderboard targets have already been pulled or not
    if (!empty($db->INDEX['LEADERBOARD']['targets'])){
        $this_leaderboard_target_players = json_decode($db->INDEX['LEADERBOARD']['targets'], true);
    } else {

        // Collect the leaderboard index and online players for ranking
        $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
        $this_leaderboard_online_players = mmrpg_prototype_leaderboard_online();

        // Collect a list of user IDs that have already been defeated if not already provided
        if (empty($this_leaderboard_defeated_players)){
            $defeated_leaderboard_players_index =  $db->get_array_list("SELECT
                DISTINCT(battles.target_user_id) AS target_user_id,
                users.user_name_clean AS target_user_name,
                users.user_colour_token As target_user_colour
                FROM mmrpg_battles AS battles
                LEFT JOIN mmrpg_users AS users ON battles.target_user_id = users.user_id
                WHERE
                battles.this_user_id = {$this_userid}
                AND battles.this_player_result = 'victory'
                ;", 'target_user_name');
        }

        // Collapse the defeated players into a string of just their usernames
        if (!empty($defeated_leaderboard_players_index)){ $this_leaderboard_defeated_players = array_keys($defeated_leaderboard_players_index); }
        else { $this_leaderboard_defeated_players = array(); }

        // Generate the online username tokens for adding to the condition list
        $temp_include_usernames = array();
        $temp_include_usernames_count = 0;
        $temp_include_usernames_string = array();
        $this_leaderboard_online_usernames = array();
        if (!empty($this_leaderboard_online_players)){
            foreach ($this_leaderboard_online_players AS $info){ if ($info['id'] != $this_userid){
                $temp_include_usernames[] = $info['token'];
                $this_leaderboard_online_usernames[] = $info['token'];
                } }
            $temp_include_usernames_count = count($temp_include_usernames);
            if (!empty($temp_include_usernames)){
                foreach ($temp_include_usernames AS $token){ $temp_include_usernames_string[] = "'{$token}'"; }
                $temp_include_usernames_string = implode(',', $temp_include_usernames_string);
            } else {
                $temp_include_usernames_string = '';
            }
        } else {
            $temp_include_usernames_string = '';
        }
        // Generate the online username tokens for adding to the condition list
        $temp_exclude_usernames = array();
        $temp_exclude_usernames_count = 0;
        $temp_exclude_usernames_string = array();
        if (!empty($this_leaderboard_defeated_players)){
            $temp_exclude_usernames = $this_leaderboard_defeated_players;
            $temp_exclude_usernames_count = count($temp_exclude_usernames);
            if (!empty($temp_exclude_usernames)){
                foreach ($temp_exclude_usernames AS $token){ $temp_exclude_usernames_string[] = "'{$token}'"; }
                $temp_exclude_usernames_string = implode(',', $temp_exclude_usernames_string);
            } else {
                $temp_exclude_usernames_string = '';
            }
        } else {
            $temp_exclude_usernames_string = '';
        }
        // Generate the points index and then break it down to unique for ranks
        $this_points_index = array();
        foreach ($this_leaderboard_index AS $info){ $this_points_index[] = $info['board_points']; }
        $this_points_index = array_unique($this_points_index);
        // Define the vars for finding the online players
        $this_player_points = mmrpg_prototype_battle_points();
        $this_player_points_min = ceil($this_player_points * 0.10);
        $this_player_points_max = ceil($this_player_points * 2.10);
        // Define the array for pulling all the leaderboard data
        $temp_leaderboard_query = 'SELECT
                mmrpg_leaderboard.user_id,
                mmrpg_leaderboard.board_points,
                mmrpg_users.user_name,
                mmrpg_users.user_name_clean,
                mmrpg_users.user_name_public,
                mmrpg_users.user_colour_token,
                mmrpg_users.user_gender,
                mmrpg_saves.save_values_battle_rewards AS player_rewards,
                mmrpg_saves.save_values_battle_settings AS player_settings,
                mmrpg_saves.save_values AS player_values,
                mmrpg_saves.save_counters AS player_counters
                FROM mmrpg_leaderboard
                LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
                LEFT JOIN mmrpg_saves ON mmrpg_users.user_id = mmrpg_saves.user_id
                WHERE
                board_points >= '.$this_player_points_min.' AND board_points <= '.$this_player_points_max.'
                AND mmrpg_leaderboard.user_id != '.$this_userid.'
                ORDER BY
                '.(!empty($temp_include_usernames_string) ? ' FIELD(user_name_clean, '.$temp_include_usernames_string.') DESC, ' : '').'
                '.(!empty($temp_exclude_usernames_string) ? ' FIELD(user_name_clean, '.$temp_exclude_usernames_string.') ASC, ' : '').'
                board_points DESC
                LIMIT 12
            ';
        // Query the database and collect the array list of all online players
        $this_leaderboard_target_players = $db->get_array_list($temp_leaderboard_query);
        // Loop through and decode any fields that require it
        if (!empty($this_leaderboard_target_players)){
            foreach ($this_leaderboard_target_players AS $key => $player){
                $player['player_rewards'] = !empty($player['player_rewards']) ? json_decode($player['player_rewards'], true) : array();
                $player['player_settings'] = !empty($player['player_settings']) ? json_decode($player['player_settings'], true) : array();
                $player['values'] = !empty($player['player_values']) ? json_decode($player['player_values'], true) : array();
                $player['counters'] = !empty($player['player_counters']) ? json_decode($player['player_counters'], true) : array();
                unset($player['player_values']);
                unset($player['player_counters']);
                $player['player_favourites'] = !empty($player['values']['robot_favourites']) ? $player['values']['robot_favourites'] : array();
                $player['player_starforce'] = !empty($player['values']['star_force']) ? $player['values']['star_force'] : array();
                if (!empty($player_robot_sort)){ $player['counters']['player_robots_count'] = !empty($player['player_rewards'][$player_robot_sort]['player_robots']) ? count($player['player_rewards'][$player_robot_sort]['player_robots']) : 0; }
                $player['values']['flag_online'] = in_array($player['user_name_clean'], $this_leaderboard_online_usernames) ? 1 : 0;
                $player['values']['flag_defeated'] = in_array($player['user_name_clean'], $this_leaderboard_defeated_players) ? 1 : 0;
                $player['values']['colour_token'] = !empty($player['user_colour_token']) ? $player['user_colour_token'] : '';
                $this_leaderboard_target_players[$key] = $player;
            }
        }
        // Update the database index cache
        //if (!empty($player_robot_sort)){ uasort($this_leaderboard_target_players, 'mmrpg_prototype_leaderboard_targets_sort'); }
        $db->INDEX['LEADERBOARD']['targets'] = json_encode($this_leaderboard_target_players);
        //die($temp_leaderboard_query);
    }
    // Return the collected online players if any
    //die('<pre>$this_leaderboard_target_players : '.print_r($this_leaderboard_target_players, true).'</pre>');
    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$this_leaderboard_target_players : '.print_r($this_leaderboard_target_players, true).'');  }
    return $this_leaderboard_target_players;
}
// Define a function for sorting the target leaderboard players
function mmrpg_prototype_leaderboard_targets_sort($player1, $player2){

    if (!isset($player1['values']['flag_online'])){ $player1['values']['flag_online'] = 0; }
    if (!isset($player1['values']['flag_defeated'])){ $player1['values']['flag_defeated'] = 0; }
    if (!isset($player1['counters']['battle_points'])){ $player1['counters']['battle_points'] = 0; }
    if (!isset($player1['counters']['player_robots_count'])){ $player1['counters']['player_robots_count'] = 1; }

    if (!isset($player2['values']['flag_online'])){ $player2['values']['flag_online'] = 0; }
    if (!isset($player2['values']['flag_defeated'])){ $player2['values']['flag_defeated'] = 0; }
    if (!isset($player2['counters']['battle_points'])){ $player2['counters']['battle_points'] = 0; }
    if (!isset($player2['counters']['player_robots_count'])){ $player2['counters']['player_robots_count'] = 1; }

    if ($player1['values']['flag_online'] < $player2['values']['flag_online']){ return 1; }
    elseif ($player1['values']['flag_online'] > $player2['values']['flag_online']){ return -1; }
    if ($player1['values']['flag_defeated'] < $player2['values']['flag_defeated']){ return -1; }
    elseif ($player1['values']['flag_defeated'] > $player2['values']['flag_defeated']){ return 1; }
    elseif ($player1['counters']['battle_points'] < $player2['counters']['battle_points']){ return -1; }
    elseif ($player1['counters']['battle_points'] > $player2['counters']['battle_points']){ return 1; }
    elseif ($player1['counters']['player_robots_count'] < $player2['counters']['player_robots_count']){ return -1; }
    elseif ($player1['counters']['player_robots_count'] > $player2['counters']['player_robots_count']){ return 1; }
    else { return 0; }

}



// Define a function for determining a player's battle music
function mmrpg_prototype_get_player_music($player_token, $session_token = 'GAME'){
    global $mmrpg_index, $db;

    $temp_session_key = $player_token.'_target-robot-omega_prototype';
    $temp_robot_omega = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
    $db_robot_fields = rpg_robot::get_index_fields(true);
    $temp_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');

    // Count the games representaed and order by count
    $temp_game_counters = array();
    foreach ($temp_robot_omega AS $omega){
        if (empty($omega['robot'])){ continue; }
        $index = rpg_robot::parse_index_info($temp_robot_index[$omega['robot']]);
        $game = strtolower($index['robot_game']);
        if (!isset($temp_game_counters[$game])){ $temp_game_counters[$game] = 0; }
        $temp_game_counters[$game] += 1;
    }

    //die('<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>');

    if (empty($temp_game_counters)){
        if ($player_token == 'dr-light'){ $temp_game_counters['mm01'] = 1; }
        if ($player_token == 'dr-wily'){ $temp_game_counters['mm02'] = 1; }
        if ($player_token == 'dr-cossack'){ $temp_game_counters['mm04'] = 1; }
    }

    asort($temp_game_counters, SORT_NUMERIC);

    //echo("\n".'-------'.$player_token.'-------'."\n".'<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>'."\n");

    // Get the last element in the array
    end($temp_game_counters);
    $most_key = key($temp_game_counters);
    $most_count = $temp_game_counters[$most_key];

    //echo("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

    $most_options = array($most_key);
    foreach ($temp_game_counters AS $key => $count){ if ($key != $most_key && $count >= $most_count){ $most_options[] = $key; } }
    if (count($most_options) > 1){ $most_key = $most_options[array_rand($most_options, 1)];  }

    //echo("\n".'<pre>$most_options = '.print_r($most_options, true).'</pre>'."\n");

    //echo("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

    return $most_key;

}

// Define a function for determining a player's battle music
function mmrpg_prototype_get_player_mission_music($player_token, $session_token = 'GAME'){
    $most_key = mmrpg_prototype_get_player_music($player_token, $session_token);
    return 'stage-select-'.$most_key;
}


// Define a function for determining a player's boss music
function mmrpg_prototype_get_player_boss_music($player_token, $session_token = 'GAME'){
    $most_key = mmrpg_prototype_get_player_music($player_token, $session_token);
    $most_key_int = preg_replace('/^mm0?/i', '', $most_key);
    return 'boss-theme-mm'.str_pad($most_key_int, 2, '0', STR_PAD_LEFT);

}

// Define a function for checking the battle's prototype points total
function mmrpg_prototype_database_summoned($robot_token = ''){
    // Define static variables amd populate if necessary
    static $this_count_array;
    // Return the current point total for thisgame
    $session_token = mmrpg_game_token();
    // Check if the array is empty and populate if not
    if (empty($this_count_array)){
        // Define the array to hold all the summon counts
        $this_count_array = array();
        // If the robot database array is not empty, loop through it
        if (!empty($_SESSION[$session_token]['values']['robot_database'])){
            foreach ($_SESSION[$session_token]['values']['robot_database'] AS $token => $info){
                if (!empty($info['robot_summoned'])){ $this_count_array[$token] = $info['robot_summoned']; }
            }
        }
    }
    // If the robot token was not empty
    if (!empty($robot_token)){
        // If the array exists, return the count
        if (!empty($this_count_array[$robot_token])){ return $this_count_array[$robot_token]; }
        // Otherwise, return zero
        else { return 0; }
    }
    // Otherwise, return the full array
    else {
        // Return the count array
        return $this_count_array;
    }
}

// Define a function for collecting robot sprite markup
function mmrpg_prototype_get_player_robot_sprites($player_token, $session_token = 'GAME'){
    global $mmrpg_index, $db;
    $temp_offset_x = 14;
    $temp_offset_z = 50;
    $temp_offset_y = -2;
    $temp_offset_opacity = 0.75;
    $text_sprites_markup = '';
    $temp_player_robots = $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'];
    $temp_db_tokens = "'".implode("','", array_keys($temp_player_robots))."'";
    $temp_db_fields = rpg_robot::get_index_fields(true, 'robots');
    $temp_robot_index = $db->get_array_list("SELECT
        {$temp_db_fields}
        FROM mmrpg_index_robots AS robots
        WHERE robots.robot_flag_complete = 1
        AND robot_token IN ({$temp_db_tokens})
        ;", 'robot_token');
    foreach ($temp_player_robots AS $token => $info){
        if (!isset($temp_robot_index[$token])){ continue; }
        $index = rpg_robot::parse_index_info($temp_robot_index[$token]);
        $info = array_merge($index, $info);
        if (mmrpg_prototype_robot_unlocked($player_token, $token)){
            $temp_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
            $temp_size_text = $temp_size.'x'.$temp_size;
            $temp_offset_x += $temp_size > 40 ? 0 : 20;
            $temp_offset_y = $temp_size > 40 ? -42 : -2;
            $temp_offset_z -= 1;
            $temp_offset_opacity -= 0.05;
            if ($temp_offset_opacity <= 0){ $temp_offset_opacity = 0; break; }
            $text_sprites_markup .= '<span class="sprite sprite_nobanner sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(images/robots/'.(!empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token']).'/sprite_right_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: '.$temp_offset_y.'px; right: '.$temp_offset_x.'px; z-index: '.$temp_offset_z.'; opacity: '.$temp_offset_opacity.'; ">'.$info['robot_name'].'</span>';
            if ($temp_size > 40){ $temp_offset_x += 20;  }
        }
    }
    return $text_sprites_markup;
}
?>