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
    global $mmrpg_index_players;
    if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

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
        -- save_counters, save_settings,
        save_values, save_flags,
        save_values_battle_rewards, save_values_battle_settings,
        -- save_values_battle_items, save_values_battle_abilities,
        -- save_values_battle_stars, save_values_robot_database,
        save_values_robot_alts,
        save_date_modified, save_date_created
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
    //$user_battle_abilities = !empty($user_save_array['save_values_battle_abilities']) ? $user_save_array['save_values_battle_abilities'] : array();
    //$user_battle_items = !empty($user_save_array['save_values_battle_items']) ? $user_save_array['save_values_battle_items'] : array();
    //$user_battle_stars = !empty($user_save_array['save_values_battle_stars']) ? $user_save_array['save_values_battle_stars'] : array();
    $user_robot_alts = !empty($user_save_array['save_values_robot_alts']) ? $user_save_array['save_values_robot_alts'] : array();
    //$user_robot_database = !empty($user_save_array['save_values_robot_database']) ? $user_save_array['save_values_robot_database'] : array();

    // Manually collect save data for certain progress using their dedicated functions
    rpg_user::pull_unlocked_abilities($user_id, $user_battle_abilities);
    rpg_user::pull_unlocked_items($user_id, $user_battle_items);
    rpg_user::pull_unlocked_stars($user_id, $user_battle_stars);
    rpg_user::pull_robot_records($user_id, $user_robot_database);

    // Collect a quick robot, ability, and item index for reference
    $mmrpg_robots = rpg_robot::get_index();
    $mmrpg_abilities = rpg_ability::get_index();
    $mmrpg_items = rpg_item::get_index();
    $mmrpg_fields = rpg_field::get_index();

    // -- CHAPTER POINTS -- //

    // Grant the player bonuses for completing any of the doctor's chapters (chapter complete)
    if (true){
        $chapter_events_completed = array();
        if (!empty($user_save_array['save_flags']['events'])){
            $event_flags = $user_save_array['save_flags']['events'];
            $doctors_completed = array_keys($user_battle_rewards);
            foreach ($doctors_completed AS $doctor_token){
                if ($doctor_token === 'player'){ continue; }
                $pt_complete = !empty($user_save_array['save_values']['prototype_awards']['prototype_complete_'.str_replace('dr-', '', $doctor_token)]) ? true : false;
                for ($ch = 1; $ch <= 5; $ch++){
                    $nch = $ch + 1;
                    if (!$pt_complete && empty($event_flags[$doctor_token.'_chapter-'.$nch.'-unlocked'])){ continue; }
                    $chapter_events_completed[] = $doctor_token.'_chapter-'.$ch;
                }
            }
        }
        $points_index['chapters_completed'] = $chapter_events_completed;
        $points_index['chapters_completed_points'] = count($chapter_events_completed) * 25000;
        $total_battle_points += $points_index['chapters_completed_points'];
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
        $points_index['campaigns_completed_points'] = count($complete_events_unlocked) * 250000;
        $total_battle_points += $points_index['campaigns_completed_points'];
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
        $ability_points = 0;
        $abilities_unlocked = array();
        foreach ($user_battle_abilities As $ability_key => $ability_token){
            if (!isset($mmrpg_abilities[$ability_token])){ continue; }
            elseif (in_array($ability_token, $abilities_unlocked)){ continue; }
            $ability_info = $mmrpg_abilities[$ability_token];
            if (!$ability_info['ability_flag_published']){ continue; }
            elseif (!$ability_info['ability_flag_complete']){ continue; }
            elseif (!$ability_info['ability_flag_unlockable']){ continue; }
            elseif ($ability_info['ability_flag_hidden']){ continue; }
            $ability_value = 0;
            if (!empty($ability_info['ability_value'])){ $ability_value = $ability_info['ability_value']; }
            elseif (!empty($ability_info['ability_price'])){ $ability_value = ceil($ability_info['ability_price']); }
            $ability_points += $ability_value;
            $abilities_unlocked[] = $ability_token;
        }
        $points_index['abilities_unlocked'] = $abilities_unlocked;
        $points_index['abilities_unlocked_points'] = $ability_points;
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
            $item_info = $mmrpg_items[$item_token];
            if (!$item_info['item_flag_complete']){ continue; }
            elseif ($item_info['item_flag_hidden']){ continue; }
            $item_value = 0;
            if (!empty($item_info['item_value'])){ $item_value = $item_info['item_value']; }
            elseif (!empty($item_info['item_price'])){ $item_value = $item_info['item_price']; }
            elseif (strstr($item_token, '-screw')){ $item_value = ($item_value / 2); }
            $item_points += $item_value;
            $item_label = $item_token;
            $items_unlocked[] = $item_label;
        }
        $points_index['items_unlocked'] = $items_unlocked;
        $points_index['items_unlocked_points'] = $item_points;
        $total_battle_points += $points_index['items_unlocked_points'];
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
        $robots_unlocked_alt_outfits_points = 0;
        foreach ($user_battle_rewards AS $doctor_token => $doctor_info){
            if (empty($doctor_info) || empty($doctor_info['player_robots'])){ continue; }
            foreach ($doctor_info['player_robots'] AS $robot_token => $robot_info){
                if (!isset($mmrpg_robots[$robot_token])){ continue; }
                elseif (in_array($robot_token, $robots_unlocked)){ continue; }
                elseif (!isset($user_battle_settings[$doctor_token]['player_robots'][$robot_token])){ continue; }
                elseif (!$mmrpg_robots[$robot_token]['robot_flag_complete']){ continue; }
                elseif (!$mmrpg_robots[$robot_token]['robot_flag_unlockable']){ continue; }
                elseif ($mmrpg_robots[$robot_token]['robot_flag_hidden']){ continue; }
                $robots_unlocked[] = $robot_token;
                $robot_level = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
                $robot_stats = rpg_robot::calculate_stat_values($robot_level, $mmrpg_robots[$robot_token], $robot_info, true);
                if ($robot_stats['level'] >= 100 && !in_array($robot_token, $robots_unlocked_max_level)){ $robots_unlocked_max_level[] = $robot_token; }
                if ($robot_stats['attack']['bonus'] >= $robot_stats['attack']['bonus_max'] && !in_array($robot_token, $robots_unlocked_max_attack)){ $robots_unlocked_max_attack[] = $robot_token; }
                if ($robot_stats['defense']['bonus'] >= $robot_stats['defense']['bonus_max'] && !in_array($robot_token, $robots_unlocked_max_defense)){ $robots_unlocked_max_defense[] = $robot_token; }
                if ($robot_stats['speed']['bonus'] >= $robot_stats['speed']['bonus_max'] && !in_array($robot_token, $robots_unlocked_max_speed)){ $robots_unlocked_max_speed[] = $robot_token; }
                $current_summons = !empty($user_robot_database[$robot_token]['robot_summoned']) ? $user_robot_database[$robot_token]['robot_summoned'] : 0;
                $alts_unlocked = array();
                if (!empty($user_robot_alts[$robot_token])){
                    $alts_unlocked += $user_robot_alts[$robot_token];
                }
                if (!empty($mmrpg_robots[$robot_token]['robot_image_alts'])){
                    foreach ($mmrpg_robots[$robot_token]['robot_image_alts'] AS $alt_key => $alt_info){
                        if (!isset($alt_info['summons']) || $current_summons < $alt_info['summons']){ continue; }
                        $alts_unlocked[] = $alt_info['token'];
                    }
                }
                if (!empty($alts_unlocked)){
                    $alts_unlocked = array_unique($alts_unlocked);
                    $num_alts = count($alts_unlocked);
                    $robots_unlocked_alt_outfits[] = $robot_token.' x'.$num_alts;
                    $robots_unlocked_alt_outfits_count += $num_alts;
                    $robots_unlocked_alt_outfits_points += 2000 + ($num_alts - 1);
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
        $points_index['robots_unlocked_alt_outfits_points'] = $robots_unlocked_alt_outfits_points;
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

    // -- CHALLENGE POINTS -- //

    // Grant the user points for each unique challenge mission they've completed in a challenge mode
    if (true){
        $temp_db_fields = rpg_mission_challenge::get_index_fields(true, 'challenges');
        $temp_challenge_kinds = array();
        $temp_challenge_kinds['event'] = array('main' => 'mmrpg_challenges', 'leaderboard' => 'mmrpg_challenges_leaderboard');
        //$temp_challenge_kinds['user'] = array('main' => 'mmrpg_users_challenges', 'leaderboard' => 'mmrpg_users_challenges_leaderboard');
        $points_index['challenges_completed'] = array();
        $points_index['challenges_completed_points'] = 0;
        foreach ($temp_challenge_kinds AS $kind => $tables){
            $challenges_completed = $db->get_array_list("SELECT
            board.challenge_id,
            board.challenge_turns_used,
            challenges.challenge_turn_limit,
            board.challenge_robots_used,
            challenges.challenge_robot_limit,
            board.challenge_result,
            {$temp_db_fields}
            FROM {$tables['leaderboard']} AS board
            LEFT JOIN {$tables['main']} AS challenges ON challenges.challenge_id = board.challenge_id
            WHERE
            board.user_id = {$user_id}
            AND board.challenge_result = 'victory'
            AND challenges.challenge_kind = '{$kind}'
            AND challenges.challenge_flag_published = 1
            AND challenges.challenge_flag_hidden = 0
            AND challenges.challenge_creator <> {$user_id}
            ;", 'challenge_id');
            if (!empty($challenges_completed)){
                foreach ($challenges_completed AS $id => $data){
                    $xid = $kind === 'user' ? 'u'.$id : $id;
                    $points = rpg_mission_challenge::calculate_challenge_reward_points($kind, $data, $percent, $rank);
                    $data['challenge_victory_points'] = $points;
                    $data['challenge_victory_percent'] = $percent;
                    $data['challenge_victory_rank'] = $rank;
                    $points_index['challenges_completed'][$xid] = $data;
                    $points_index['challenges_completed_points'] += $points;
                }
            }
        }
        //$points = rpg_mission_challenge::calculate_challenge_reward_points($data['challenge_kind'], $data, $victory_percent, $victory_rank);
        //$points_index['challenges_completed'] = !empty($challenges_completed) ? $challenges_completed : array();
        //$points_index['challenges_completed_points'] = !empty($challenges_completed) ? (count($challenges_completed) * 10000) : 0;
        $total_battle_points += $points_index['challenges_completed_points'];
    }

    // -- PLAYER POINTS -- //

    // Grant the user points for each unique player they've defeated in a player battle
    if (true){
        $defeated_players = $db->get_array_list("SELECT
              DISTINCT(battles.target_user_id) AS target_user_id,
              (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS target_user_name,
              users.user_name_clean AS target_user_token,
              users.user_colour_token AS target_user_colour,
              users.user_colour_token2 AS target_user_colour2
            FROM mmrpg_battles AS battles
              LEFT JOIN mmrpg_users AS users ON battles.target_user_id = users.user_id
            WHERE
              battles.this_user_id = {$user_id}
              AND battles.this_player_result = 'victory'
              AND users.user_flag_approved = 1
              AND battles.battle_id > COALESCE((
                SELECT MAX(opponent_battles.battle_id)
                FROM mmrpg_battles AS opponent_battles
                WHERE
                  opponent_battles.target_user_id = {$user_id}
                  AND opponent_battles.this_user_id = battles.target_user_id
                  AND opponent_battles.this_player_result = 'victory'
              ), 0)
            ORDER BY target_user_name
            ;", 'target_user_name');
        $points_index['players_defeated'] = !empty($defeated_players) ? $defeated_players : array();
        $points_index['players_defeated_points'] = !empty($defeated_players) ? (count($defeated_players) * 10000) : 0;
        $total_battle_points += $points_index['players_defeated_points'];
    }

    // -- ENDLESS ATTACK POINTS -- //

    // Grant the user points for their personal best record in the ENDLESS ATTACK MODE challenge
    if (true){
        $wave_value = MMRPG_SETTINGS_BATTLEPOINTS_PERWAVE;
        $challenge_waveboard_results = $db->get_array("SELECT
            board.challenge_waves_completed,
            board.challenge_robots_used,
            board.challenge_turns_used,
            board.challenge_team_config,
            @base_points := (board.challenge_waves_completed * {$wave_value}) AS challenge_points_base,
            @robot_points := CEIL(@base_points / board.challenge_robots_used) AS challenge_points_robot_bonus,
            @turn_points := CEIL(@base_points / (board.challenge_turns_used / board.challenge_waves_completed)) AS challenge_points_turn_bonus,
            CEIL(@base_points + @robot_points + @turn_points) AS challenge_points_total
            FROM mmrpg_challenges_waveboard AS board
            WHERE
            board.user_id = {$user_id}
            AND challenge_result = 'victory'
            ;");
        if (!empty($challenge_waveboard_results)){
            $points_index['endless_waves_completed'] = $challenge_waveboard_results;
            $points_index['endless_waves_completed_points'] = $challenge_waveboard_results['challenge_points_total'];
            $total_battle_points += $points_index['endless_waves_completed_points'];
        }
    }

    // -- BONUS POINTS -- //

    /*
    // Grant the user bonus veteran points based on their account age (date created vs date modified)
    if (false){
        $date_modified = $user_save_array['save_date_modified'];
        $date_created = $user_save_array['save_date_created'];
        $total_days = ceil((((($date_modified - $date_created) / 60) / 60) / 24));
        $veteran_bonus = $total_days * 100;
        $points_index['veteran_bonus'] = 'First Save: '.date('Y/m/d', $date_created).'';
        $points_index['veteran_bonus'] .= '<br /> Last Save: '.date('Y/m/d', $date_modified).'';
        $points_index['veteran_bonus'] .= '<br /> Total Days: '.number_format($total_days, 0, '.', ',').' Days';
        $points_index['veteran_bonus'] .= '<br /> <strong>Veteran Bonus: '.number_format($veteran_bonus, 0, '.', ',').' BP</strong>';
        $points_index['veteran_bonus_points'] = $veteran_bonus;
        $total_battle_points += $points_index['veteran_bonus_points'];
    }
    */

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
function mmrpg_prototype_player_currently_selected_chapter($player_token){
    //error_log('mmrpg_prototype_player_currently_selected_chapter($player_token:'.$player_token.')');
    // Return the current rewards array for this player
    $session_token = mmrpg_game_token();
    $battle_settings = !empty($_SESSION[$session_token]['battle_settings']) ? $_SESSION[$session_token]['battle_settings'] : array();
    $player_token_clean = preg_replace('/^dr-/', '', $player_token);
    $current_chapter_token = $player_token_clean.'_current_chapter';
    $current_chapter_value = (isset($battle_settings[$current_chapter_token]) ? $battle_settings[$current_chapter_token] : 0) + 1;
    //error_log('$session_token ='.print_r($session_token, true));
    //error_log('$battle_settings ='.print_r($battle_settings, true));
    //error_log('$battle_settings (keys) = '.print_r(array_keys($battle_settings), true));
    //error_log('$player_token_clean = '.print_r($player_token_clean, true));
    //error_log('$current_chapter_token = '.print_r($current_chapter_token, true));
    //error_log('$current_chapter_value = '.print_r($current_chapter_value, true));
    return $current_chapter_value;
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

// Define a function for checking if a prototype item has been unlocked
function mmrpg_prototype_item_unlocked_count($item_token){

    // Define the game session helper var
    $session_token = mmrpg_game_token();

    // If items are not yet loaded, return false
    if (empty($_SESSION[$session_token]['values']['battle_items'])){ return 0; }

    // If this specific item has not been unlocked, return false
    if (!isset($_SESSION[$session_token]['values']['battle_items'][$item_token])){ return 0; }

    // Otherwise return true
    return $_SESSION[$session_token]['values']['battle_items'][$item_token];

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

// Define quick functions for getting or setting battle item quantities
function mmrpg_prototype_init_battle_item_count($item_token){
    $session_token = mmrpg_game_token();
    if (!isset($_SESSION[$session_token]['values']['battle_items'][$item_token])){
        $_SESSION[$session_token]['values']['battle_items'][$item_token] = 0;
    }
}
function mmrpg_prototype_get_battle_item_count($item_token){
    $session_token = mmrpg_game_token();
    mmrpg_prototype_init_battle_item_count($item_token);
    return $_SESSION[$session_token]['values']['battle_items'][$item_token];
}
function mmrpg_prototype_set_battle_item_count($item_token, $new_count){
    $session_token = mmrpg_game_token();
    mmrpg_prototype_init_battle_item_count($item_token);
    $_SESSION[$session_token]['values']['battle_items'][$item_token] = $new_count;
}
function mmrpg_prototype_inc_battle_item_count($item_token, $inc_amount = 1){
    $session_token = mmrpg_game_token();
    mmrpg_prototype_init_battle_item_count($item_token);
    $_SESSION[$session_token]['values']['battle_items'][$item_token] += $inc_amount;
    return mmrpg_prototype_get_battle_item_count($item_token);
}
function mmrpg_prototype_dec_battle_item_count($item_token, $dec_amount = 1){
    $session_token = mmrpg_game_token();
    mmrpg_prototype_init_battle_item_count($item_token);
    $_SESSION[$session_token]['values']['battle_items'][$item_token] -= $dec_amount;
    return mmrpg_prototype_get_battle_item_count($item_token);
}

// Define quick functions for getting or setting battle setting items for robots
function mmrpg_prototype_get_robot_battle_item($player_token, $robot_token){
    $session_token = mmrpg_game_token();
    if (!empty($_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['robot_item'])){
        return $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['robot_item'];
    }
    return '';
}
function mmrpg_prototype_set_robot_battle_item($player_token, $robot_token, $item_token){
    $session_token = mmrpg_game_token();
    $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['robot_item'] = $item_token;
}
function mmrpg_prototype_unset_robot_battle_item($player_token, $robot_token){
    $session_token = mmrpg_game_token();
    $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'][$robot_token]['robot_item'] = '';
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

    // Backup the player token that has been requested
    $requested_player_token = $player_token;

    // Collect the available player tokens from the session
    $available_player_tokens = isset($_SESSION[$session_token]['values']['battle_complete']) ? array_keys($_SESSION[$session_token]['values']['battle_complete']) : array();
    //error_log('$available_player_tokens = '.print_r($available_player_tokens, true));

    // Loop through available player tokens one by one
    $temp_count_total = 0;
    foreach ($available_player_tokens AS $player_key => $player_token){

        // If the user has requested a specific token and this is not it, continue
        if (!empty($requested_player_token) && $requested_player_token != $player_token){ continue; }

        // Collect the battle complete count from the session if set
        $temp_battle_completes = isset($_SESSION[$session_token]['values']['battle_complete'][$player_token]) ? $_SESSION[$session_token]['values']['battle_complete'][$player_token] : array();
        //error_log('$temp_battle_completes = '.count($temp_battle_completes).' = '.print_r($temp_battle_completes, true));

        // Check if only unique battles were requested or ALL battles
        if (!empty($unique)){
            $temp_unique_count = count($temp_battle_completes);
            //error_log('$temp_unique_count = '.print_r($temp_unique_count, true));
            $temp_count_total += $temp_unique_count;
        } else {
            $temp_all_count = 0;
            foreach ($temp_battle_completes AS $info){ $temp_all_count += !empty($info['battle_count']) ? $info['battle_count'] : 1; }
            //error_log('$temp_all_count = '.print_r($temp_all_count, true));
            $temp_count_total += $temp_all_count;
        }

    }
    //error_log('$temp_count_total = '.print_r($temp_count_total, true));

    // Return the total number of battles complete
    return $temp_count_total;
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

// Define a function for getting a players unlocked index for reference
function mmrpg_prototype_players_unlocked_index($include_extra = array()){

    // Define the game session helper var
    $session_token = rpg_game::session_token();

    // Collect the actual players index for reference later
    $mmrpg_players_index = rpg_player::get_index(true);

    // Define an empty array to hold the unlocked players index
    $this_unlocked_players_index = array();

    // Loop through relevant sessions keys and collect player data
    $session_battle_keys = array('battle_settings', 'battle_rewards');
    foreach ($session_battle_keys AS $session_battle_key){
        if (!empty($_SESSION[$session_token]['values'][$session_battle_key])){
            foreach ($_SESSION[$session_token]['values'][$session_battle_key] AS $player_token => $player_array){
                $existing_player_array = array();
                if (isset($this_unlocked_players_index[$player_token])){ $existing_player_array = $this_unlocked_players_index[$player_token];}
                $this_unlocked_players_index[$player_token] = array_merge($existing_player_array, $player_array);
            }
        }
    }

    // Collect any predefined relationship data between characters we can possibly insert later
    $preset_character_relationships = rpg_player::get_character_relationships();

    // If extra players were provided, we need to merge them into the unlocked players index
    if (!empty($include_extra)){
        foreach ($include_extra AS $player_token => $player_array){
            if (!isset($player_array['current_player'])){ $player_array['current_player'] = ''; }
            if (!isset($this_unlocked_players_index[$player_token])){ $this_unlocked_players_index[$player_token] = $player_array; }
            else { $this_unlocked_players_index[$player_token] = array_merge($this_unlocked_players_index[$player_token], $player_array); }
        }
    }

    // If players were found, we need to parse the data and clean it a bit before returning
    if (!empty($this_unlocked_players_index)){
        foreach ($this_unlocked_players_index AS $player_token => $player_array){
            if (!isset($mmrpg_players_index[$player_token])){
                unset($this_unlocked_players_index[$player_token]);
                continue;
            }
            $player_info = $mmrpg_players_index[$player_token];
            if (isset($player_array['flags'])){ $player_array['flags'] = array_keys($player_array['flags']); }
            else { $player_array['flags'] = array(); }
            if (isset($player_array['player_abilities'])){ $player_array['player_abilities'] = array_keys($player_array['player_abilities']); }
            else { $player_array['player_abilities'] = array(); }
            if (!isset($player_array['player_token'])){ $player_array['player_token'] = $player_token; }
            $player_array['player_type'] = $player_info['player_type'];
            $player_array['player_image'] = $player_info['player_image'];
            $player_array['player_image_size'] = $player_info['player_image_size'];
            $player_array['player_energy_base'] = 100;
            $player_array['player_weapons_base'] = 100;
            $player_array['player_attack_base'] = 100;
            $player_array['player_defense_base'] = 100;
            $player_array['player_speed_base'] = 100;
            $player_array['player_robots'] = !empty($player_array['player_robots']) ? array_keys($player_array['player_robots']) : array();
            $player_array['player_fields'] = !empty($player_array['player_fields']) ? array_keys($player_array['player_fields']) : array();
            $player_array['player_type_weaknesses'] = array();
            $player_array['player_type_resistances'] = array();
            $player_array['player_type_affinities'] = array();
            $player_array['player_type_immunities'] = array();
            $player_array['player_relationships'] = array();
            unset($player_array['player_items'], $player_array['player_abilities']);
            if (isset($player_array['player_'.$player_array['player_type'].'_base'])){
                $player_array['player_'.$player_array['player_type'].'_base'] += 25;
            }
            if (isset($preset_character_relationships[$player_token])){
                $relationships = $preset_character_relationships[$player_token];
                $player_array['player_relationships'] = $relationships;
            }
            $this_unlocked_players_index[$player_token] = $player_array;
        }
    }

    // Return the collected data
    //error_log('$this_unlocked_players_index = '.print_r($this_unlocked_players_index, true));
    return $this_unlocked_players_index;
}


// Define a function for getting a robots unlocked index for reference
function mmrpg_prototype_robots_unlocked_index($include_extra = array()){

    // Define the game session helper var
    $session_token = rpg_game::session_token();

    // Collect the actual robots index for reference later
    $mmrpg_robots_index = rpg_robot::get_index(true);

    // Define an empty array to hold the unlocked robots index
    $this_unlocked_robots_index = array();

    // Loop through relevant sessions keys and collect plus merge any robot data
    $session_battle_keys = array('battle_settings', 'battle_rewards');
    foreach ($session_battle_keys AS $session_battle_key){
        if (!empty($_SESSION[$session_token]['values'][$session_battle_key])){
            foreach ($_SESSION[$session_token]['values'][$session_battle_key] AS $player_token => $player_array){
                if (!empty($player_array['player_robots'])){
                    foreach ($player_array['player_robots'] AS $robot_token => $robot_array){
                        $robot_array['current_player'] = $player_token;
                        if (!isset($this_unlocked_robots_index[$robot_token])){ $this_unlocked_robots_index[$robot_token] = $robot_array; }
                        else { $this_unlocked_robots_index[$robot_token] = array_merge($this_unlocked_robots_index[$robot_token], $robot_array); }
                    }
                }
            }
        }
    }

    // Collect any predefined relationship data between characters we can possibly insert later
    $preset_character_relationships = rpg_player::get_character_relationships();

    // If extra robots were provided, we need to merge them into the unlocked robots index
    if (!empty($include_extra)){
        foreach ($include_extra AS $robot_token => $robot_array){
            if (!isset($robot_array['current_player'])){ $robot_array['current_player'] = ''; }
            if (!isset($this_unlocked_robots_index[$robot_token])){ $this_unlocked_robots_index[$robot_token] = $robot_array; }
            else { $this_unlocked_robots_index[$robot_token] = array_merge($this_unlocked_robots_index[$robot_token], $robot_array); }
        }
    }

    // If robots were found, we need to parse the data and clean it a bit before returning
    if (!empty($this_unlocked_robots_index)){
        foreach ($this_unlocked_robots_index AS $robot_token => $robot_array){
            if (!isset($mmrpg_robots_index[$robot_token])){
                unset($this_unlocked_robots_index[$robot_token]);
                continue;
            }
            $robot_info = $mmrpg_robots_index[$robot_token];
            if (isset($robot_array['flags'])){ $robot_array['flags'] = array_keys($robot_array['flags']); }
            else { $robot_array['flags'] = array(); }
            if (isset($robot_array['robot_abilities'])){ $robot_array['robot_abilities'] = array_keys($robot_array['robot_abilities']); }
            else { $robot_array['robot_abilities'] = array(); }
            $robot_array['robot_image_size'] = $robot_info['robot_image_size'];
            $robot_array['robot_energy_base'] = $robot_info['robot_energy'];
            $robot_array['robot_weapons_base'] = $robot_info['robot_weapons'];
            $robot_array['robot_attack_base'] = $robot_info['robot_attack'];
            $robot_array['robot_defense_base'] = $robot_info['robot_defense'];
            $robot_array['robot_speed_base'] = $robot_info['robot_speed'];
            $robot_array['robot_type'] = $robot_info['robot_core'];
            $robot_array['robot_type2'] = $robot_info['robot_core2'];
            $robot_array['robot_type_weaknesses'] = $robot_info['robot_weaknesses'];
            $robot_array['robot_type_resistances'] = $robot_info['robot_resistances'];
            $robot_array['robot_type_affinities'] = $robot_info['robot_affinities'];
            $robot_array['robot_type_immunities'] = $robot_info['robot_immunities'];
            $robot_array['robot_relationships'] = array();
            if (isset($preset_character_relationships[$robot_token])){
                $relationships = $preset_character_relationships[$robot_token];
                $robot_array['robot_relationships'] = $relationships;
            }
            $this_unlocked_robots_index[$robot_token] = $robot_array;
        }
    }

    // We're all done so we can return the collected data
    return $this_unlocked_robots_index;

}

// Define a function for getting a robots unlocked index in a JSON compatible format
function mmrpg_prototype_reformat_index_for_json($kind, $index){

    // If unlocked robots were found, we can loop through and reformat into the new array
    $index_json = array();
    if (!empty($index)){
        foreach ($index AS $token => $info){
            $new_info = array();
            foreach ($info AS $key => $value){
                $new_key = preg_replace('/^'.$kind.'_/i', '', $key);
                if (strpos($new_key, '_') !== false){
                    $key_parts = explode('_', $new_key);
                    $new_key = $key_parts[0];
                    for ($i = 1; $i < count($key_parts); $i++){
                        $new_key .= ucfirst($key_parts[$i]);
                    }
                }
                $new_info[$new_key] = $value;
            }
            $index_json[$token] = $new_info;
        }
    }

    // We're all done so we can return the reformatted data
    return $index_json;

}

// Define a function for getting a players unlocked index in a JSON compatible format
function mmrpg_prototype_players_unlocked_index_json($include_extra = array()){

    // Collect the unlocked players index from the other function so we can reformat
    $unlocked_players = mmrpg_prototype_players_unlocked_index($include_extra);

    // Reformat the unlocked robots index into a JSON compatible format
    $unlocked_players_json = mmrpg_prototype_reformat_index_for_json('player', $unlocked_players);

    // Return the reformatted data
    return $unlocked_players_json;
}

// Define a function for getting a robots unlocked index in a JSON compatible format
function mmrpg_prototype_robots_unlocked_index_json($include_extra = array()){

    // Collect the unlocked robots index from the other function so we can reformat
    $unlocked_robots = mmrpg_prototype_robots_unlocked_index($include_extra);

    // Reformat the unlocked robots index into a JSON compatible format
    $unlocked_robots_json = mmrpg_prototype_reformat_index_for_json('robot', $unlocked_robots);

    // We're all done so we can return the reformatted data
    return $unlocked_robots_json;

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
// Define a function for checking how many limit hearts have been unlocked by a player
function mmrpg_prototype_limit_hearts_earned($player_token, &$max_hearts = 0){

    // Define the number of hearts at zero and we'll go up from there
    $real_max_hearts = 8;
    $max_hearts = $real_max_hearts;
    $num_hearts = 0;

    // Collect the player's progress in terms of chapters to determine hearts
    $player_chapters_unlocked = rpg_prototype::get_player_chapters_unlocked($player_token);
    if ($player_chapters_unlocked['0']){ $num_hearts++; }
    if ($player_chapters_unlocked['1']){ $num_hearts++; }
    if ($player_chapters_unlocked['2']){ $num_hearts++; }
    if ($player_chapters_unlocked['3']){ $num_hearts++; }
    if ($player_chapters_unlocked['4a']){ $num_hearts++; }
    if (mmrpg_prototype_complete($player_token)){ $num_hearts++; }

    // Hide the last two hearts behind superboss battles, but don't show them until collected
    $max_hearts -= 2;
    $session_token = mmrpg_game_token();
    $session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
    if (isset($session_robot_database['quint']['robot_defeated'])){ $num_hearts++; }
    if (isset($session_robot_database['sunstar']['robot_defeated'])){ $num_hearts++; }
    if ($num_hearts > $max_hearts){ $max_hearts = $num_hearts; }

    // Make sure we don't go over the max hearts
    if ($num_hearts > $real_max_hearts){ $num_hearts = $real_max_hearts; }

    // Return the total number of hearts this player has earned
    return $num_hearts;

}

// Define a function that returns a list of all allowed fields
function mmrpg_prototype_unlocked_field_tokens($include_all = false){

    // Collect the current session token
    $session_token = mmrpg_game_token();

    // Define an array to hold possible field tokens
    $unlocked_field_tokens = array();

    // Add the base fields given throughout the campaign
    global $this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four;
    if (empty($this_omega_factors_one)){ require(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php'); }
    $base_omega_fields = array_merge($this_omega_factors_one, $this_omega_factors_two, $this_omega_factors_three, $this_omega_factors_four);
    $session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
    foreach ($base_omega_fields AS $key => $omega){
        if ($include_all
            || (isset($session_robot_database[$omega['robot']])
                && !empty($session_robot_database[$omega['robot']]['robot_unlocked']))){
            $unlocked_field_tokens[] = $omega['field'];
        }
    }

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
    $unlocked_field_tokens = mmrpg_prototype_unlocked_field_tokens(true);

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
            'info1' => array('field' => $field1_token, 'robot' => $field1_info['field_master'], 'type' => $field1_info['field_type']),
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
                'info1' => array('field' => $field1_token, 'robot' => $field1_info['field_master'], 'type' => $field1_info['field_type']),
                'info2' => array('field' => $field2_token, 'robot' => $field2_info['field_master'], 'type' => $field2_info['field_type'])
                );

        }
    }

    // Return the list of possible field and fusion stars
    return $return_arrays ? $possible_star_list : array_keys($possible_star_list);

}


// Define a function for calculating which stars are remaining for a player
function mmrpg_prototype_remaining_stars($return_arrays = false, $possible_star_list = array()){

    // Collect the current session token
    $session_token = mmrpg_game_token();

    // Collect the list of possible stars first for reference
    if (empty($possible_star_list)){ $possible_star_list = mmrpg_prototype_possible_stars($return_arrays); }
    $remaining_star_list = $possible_star_list;

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

// Define a function for checking how many prototype abilities has been unlocked
function mmrpg_prototype_abilities_unlocked($player_token = '', $robot_token = '', &$ability_tokens = array()){

    // Pull in global variables
    global $mmrpg_index_players, $mmrpg_index_abilities;
    if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }
    if (empty($mmrpg_index_abilities)){ $mmrpg_index_abilities = rpg_ability::get_index(true); }
    $session_token = mmrpg_game_token();

    // If a specific robot token was provided
    if (!empty($player_token) && !empty($robot_token)){
        // Check if this battle has been completed and return true is it was
        $ability_tokens = isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) ? ($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'][$robot_token]['robot_abilities']) : array();
        $ability_tokens = array_keys($ability_tokens);
    } elseif (!empty($player_token)){
        // Check if this ability has been unlocked by the player and return true if it was
        $ability_tokens = isset($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) ? ($_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_abilities']) : array();
        $ability_tokens = array_keys($ability_tokens);
    } else {
        // Define the ability counter and token tracker
        $ability_tokens = $_SESSION[$session_token]['values']['battle_abilities'];
    }

    // Filter the ability tokens to make sure they're actually valid
    $ability_tokens = array_unique($ability_tokens);
    foreach ($ability_tokens AS $key => $token){
        if (!isset($mmrpg_index_abilities[$token])){ unset($ability_tokens[$key]); continue; }
        $info = $mmrpg_index_abilities[$token];
        if ($info['ability_class'] != 'master'){ unset($ability_tokens[$key]); }
        elseif (empty($info['ability_flag_published'])){ unset($ability_tokens[$key]); }
        elseif (empty($info['ability_flag_complete'])){ unset($ability_tokens[$key]); }
        elseif (!empty($info['ability_flag_hidden'])){ unset($ability_tokens[$key]); }
    }

    // Return the total amount of ability tokens pulled
    return !empty($ability_tokens) ? count($ability_tokens) : 0;

}


// Define a function for displaying prototype battle option markup
function mmrpg_prototype_options_markup(&$battle_options, $player_token){
    // Refence the global config and index objects for easy access
    global $db;
    global $star_shake_delay;
    if (empty($star_shake_delay)){ $star_shake_delay = array(); }
    if (empty($star_shake_delay[$player_token])){ $star_shake_delay[$player_token] = 0; }
    static $mmrpg_index_fields, $mmrpg_index_players;
    if (empty($mmrpg_index_fields)){ $mmrpg_index_fields = rpg_field::get_index(true); }
    if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true, false, '', array('player')); }

    // Define the variable to collect option markup
    $this_markup = '';

    // Collect the robot index for calculation purposes
    $db_robot_fields = rpg_robot::get_index_fields(true);
    $this_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM `mmrpg_index_robots` WHERE `robot_flag_complete` = 1;", 'robot_token');

    // Count the number of completed battle options for this group and update the variable
    $battle_options_reversed = $battle_options; //array_reverse($battle_options);
    foreach ($battle_options_reversed AS $this_key => $this_info){

        // Define the chapter if not set
        if (!isset($this_info['option_chapter'])){ $this_info['option_chapter'] = '0'; }
        // If this is an event message type option, simply display the text/images
        if (!empty($this_info['option_type']) && $this_info['option_type'] == 'message'){

            // Generate the option markup for the event message
            $temp_optiontitle = $this_info['option_maintext'];
            $temp_optionsize = !empty($this_info['option_size']) ? $this_info['option_size'] : '1x4';
            $temp_optionimages = !empty($this_info['option_images']) ? $this_info['option_images'] : '';
            if (empty($temp_optionimages)){ $this_info['option_maintext'] = '<i class="fa fas fa-book"></i> '.$this_info['option_maintext']; }
            $temp_optiontext = '<span class="multi"><span class="maintext">'.$this_info['option_maintext'].'</span></span>';
            $this_markup .= '<a data-chapter="'.$this_info['option_chapter'].'" class="option option_message option_'.$temp_optionsize.' option_this-'.$player_token.'-message block_'.($this_key + 1).'" style="'.(!empty($this_info['option_style']) ? $this_info['option_style'] : '').'"><div class="chrome"><div class="inset"><label class="'.(!empty($temp_optionimages) ? 'has_image' : '').'">'.$temp_optionimages.$temp_optiontext.'</label></div></div></a>'."\n";

        }
        // Else if this is a placeholder type option, display a disabled button in the requested size
        elseif (!empty($this_info['option_type']) && $this_info['option_type'] == 'placeholder'){

            // Generate the option markup for the event placeholder
            if (!isset($this_info['option_maintext'])){ $this_info['option_maintext'] = ''; }
            if (!isset($this_info['option_subtext'])){ $this_info['option_subtext'] = ''; }
            $temp_optiontitle = $this_info['option_maintext'];
            $temp_optionsize = !empty($this_info['option_size']) ? $this_info['option_size'] : '1x4';
            $temp_optionimages = !empty($this_info['option_images']) ? $this_info['option_images'] : '';
            if (!empty($this_info['option_locks'])){
                if (is_numeric($this_info['option_locks'])){
                    $this_info['option_maintext'] = trim(str_repeat('<i class="lock-icon fa fas fa-lock"></i>', $this_info['option_locks']));
                } elseif (is_array($this_info['option_locks'])){
                    $this_info['option_maintext'] = '';
                    foreach ($this_info['option_locks'] AS $lock_key => $this_lock){
                        $lock_class = 'lock-icon fa fas '.($this_lock ? 'fa-lock-open-alt' : 'fa-lock-alt');
                        $animation_duration = 2 - (0.1 * $lock_key);
                        $lock_style = 'animation-duration: '.$animation_duration.'s; ';
                        $this_info['option_maintext'] .= '<i class="'.$lock_class.'" style="'.$lock_style.'"></i>';
                    }
                    $this_info['option_maintext'] = trim($this_info['option_maintext']);
                }
            } elseif (empty($temp_optionimages)){
                $this_info['option_maintext'] = '<i class="fa fas fa-lock"></i> '.$this_info['option_maintext'];
            }
            $temp_optiontype = !empty($this_info['option_pseudo_type']) ? $this_info['option_pseudo_type'] : 'empty';
            $temp_optiontext = '<span class="multi"><span class="maintext">'.$this_info['option_maintext'].'</span></span>';
            $temp_optionclass = 'option ';
            $temp_optionclass .= 'option_placeholder option_this-'.$player_token.'-placeholder  ';
            $temp_optionclass .= 'option_'.$temp_optionsize.' option_this-battle-select option_this-'.$player_token.'-battle-select ';
            $temp_optionclass .= 'option_disabled type '.$temp_optiontype.' block_'.($this_key + 1);
            $this_markup .= '<a '.
                'class="'.$temp_optionclass.'" '.
                'data-chapter="'.$this_info['option_chapter'].'" '.
                (!empty($this_info['option_pseudo_token']) ? 'data-pseudo-token="'.$this_info['option_pseudo_token'].'" ' : '').
                (!empty($this_info['option_click_tooltip']) ? 'data-click-tooltip="'.$this_info['option_click_tooltip'].'" ' : '').
                'style="'.(!empty($this_info['option_style']) ? $this_info['option_style'] : '').'" '.
                '>';
                $this_markup .= '<div class="platform">';
                    $this_markup .= '<div class="chrome">';
                        $this_markup .= '<div class="inset">';
                            $this_markup .= '<label class="'.(!empty($temp_optionimages) ? 'has_image' : '').'">'.$temp_optionimages.$temp_optiontext.'</label>';
                        $this_markup .= '</div>';
                    $this_markup .= '</div>';
                $this_markup .= '</div>';
            $this_markup .= '</a>'."\n";

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
            $this_targetinfo = !empty($mmrpg_index_players[$this_battleinfo['battle_target_player']['player_token']]) ? array_replace($mmrpg_index_players[$this_battleinfo['battle_target_player']['player_token']], $this_battleinfo['battle_target_player']) : $this_battleinfo['battle_target_player'];

            $is_starter_battle = !empty($this_battleinfo['flags']['starter_battle']) ? true : false;
            $is_player_battle = !empty($this_battleinfo['flags']['player_battle']) ? true : false;
            $is_challenge_battle = !empty($this_battleinfo['flags']['challenge_battle']) ? true : false;
            $is_endless_battle = !empty($this_battleinfo['flags']['endless_battle']) ? true : false;
            $is_battle_counts = isset($this_battleinfo['battle_counts']) && $this_battleinfo['battle_counts'] == false ? false : true;

            // Check the GAME session to see if this battle has been completed, increment the counter if it was
            $this_battleinfo['battle_option_complete'] = mmrpg_prototype_battle_complete($player_token, $this_info['battle_token']);
            $this_battleinfo['battle_option_failure'] = mmrpg_prototype_battle_failure($player_token, $this_info['battle_token']);

            // Generate the markup fields for display
            $this_option_token = $this_battleinfo['battle_token'];
            $this_option_turns = !empty($this_battleinfo['battle_turns']) ? $this_battleinfo['battle_turns'] : 1;
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

            //$this_option_class = 'option option_fieldback option_this-battle-select option_this-'.$player_token.'-battle-select option_'.$this_battleinfo['battle_size'].' option_'.$this_battleinfo['battle_token'].' option_'.$this_option_status.' block_'.($this_key + 1).' '.($this_option_complete && !$this_has_field_star ? 'option_complete ' : '').($this_option_disabled ? 'option_disabled '.($this_option_encore ? 'option_disabled_clickable ' : '') : '');
            $this_option_class = 'option option_fieldback option_this-battle-select option_this-'.$player_token.'-battle-select option_'.$this_battleinfo['battle_size'].' option_'.$this_option_status.' block_'.($this_key + 1).' '.($this_option_complete && !$this_has_field_star ? 'option_complete ' : '').($this_option_disabled ? 'option_disabled '.($this_option_encore ? 'option_disabled_clickable ' : '') : '');
            $this_option_style = 'background-position: -'.mt_rand(5, 50).'px -'.mt_rand(5, 50).'px; ';
            if ($is_endless_battle){
                if ($player_token == 'dr-light'){ $field_type = 'defense'; }
                elseif ($player_token == 'dr-wily'){ $field_type = 'attack'; }
                elseif ($player_token == 'dr-cossack'){ $field_type = 'speed'; }
                else { $field_type = 'energy'; }
                $this_type_class = 'field_type field_type_'.$field_type;
                //$this_option_class .= $this_type_class;
                $field_type_or_none = $field_type;
                $field_type_or_empty = $field_type;
            } elseif (!empty($this_fieldinfo['field_type'])){
                $this_type_class = 'field_type field_type_'.$this_fieldinfo['field_type'].(!empty($this_fieldinfo['field_type2']) ? '_'.$this_fieldinfo['field_type2'] : '');
                //$this_option_class .= $this_type_class;
                $field_type_or_none = $this_fieldinfo['field_type'];
                $field_type_or_empty = $this_fieldinfo['field_type'];
            } else {
                $this_type_class = 'field_type field_type_none';
                //$this_option_class .= $this_type_class;
                $field_type_or_none = 'none';
                $field_type_or_empty = 'empty';
            }
            if (!empty($this_fieldinfo['field_background'])){
                $image_name = 'battle-field_preview';
                $image_path = 'images/fields/'.$this_fieldinfo['field_background'].'/';
                $image_path_full = $image_path.$image_name.'.png';
                if (!empty($this_fieldinfo['field_background_variant'])){
                    //error_log('$_GET: '.print_r($_GET, true));
                    //error_log('battle_token: '.print_r($this_info['battle_token'], true));
                    //error_log('$this_fieldinfo: '.print_r($this_fieldinfo, true));
                    $new_image_name = $image_name.'_'.$this_fieldinfo['field_background_variant'];
                    $new_image_path_full = $image_path.$new_image_name.'.png';
                    //error_log('$new_image_name: '.print_r($new_image_name, true));
                    //error_log('$new_image_path_full: '.print_r($new_image_path_full, true));
                    if (rpg_game::sprite_exists($new_image_path_full)){
                        //error_log('field_background_variant sprite exists!');
                        $image_name = $new_image_name;
                        $image_path_full = $new_image_path_full;
                    }
                }
                $this_option_style = 'background-image: url('.$image_path_full.'?'.MMRPG_CONFIG_CACHE_DATE.') !important; ';
            }
            $this_option_label = '';
            $this_option_platform_style = '';
            if (!empty($this_fieldinfo['field_foreground'])) {
                $image_name = 'battle-field_foreground_base';
                $image_path = 'images/fields/'.$this_fieldinfo['field_foreground'].'/';
                $image_path_full = $image_path.$image_name.'.png';
                if (!empty($this_fieldinfo['field_foreground_variant'])) {
                    //error_log('$_GET: '.print_r($_GET, true));
                    //error_log('battle_token: '.print_r($this_info['battle_token'], true));
                    //error_log('$this_fieldinfo: '.print_r($this_fieldinfo, true));
                    $new_image_name = $image_name.'_'.$this_fieldinfo['field_foreground_variant'];
                    $new_image_path_full = $image_path.$new_image_name.'.png';
                    if (rpg_game::sprite_exists($new_image_path_full)) {
                        //error_log('field_foreground_variant sprite exists!');
                        $image_name = $new_image_name;
                        $image_path_full = $new_image_path_full;
                    }
                }
                $this_option_platform_style = 'background-image: url('.$image_path_full.'?'.MMRPG_CONFIG_CACHE_DATE.'); ';
            }
            $this_option_min_level = false;
            $this_option_max_level = false;
            $this_battleinfo['battle_sprite'] = array();
            $this_targetinfo = !empty($mmrpg_index_players[$this_targetinfo['player_token']]) ? array_merge($mmrpg_index_players[$this_targetinfo['player_token']], $this_targetinfo) : $mmrpg_index_players['player'];
            if ($this_targetinfo['player_token'] != 'player'){
                $this_battleinfo['battle_sprite'][] = array(
                    'path' => 'players/'.(!empty($this_targetinfo['player_image']) ? $this_targetinfo['player_image'] : $this_targetinfo['player_token']),
                    'size' => !empty($this_targetinfo['player_image_size']) ? $this_targetinfo['player_image_size'] : 40,
                    'kind' => 'player'
                    );
            }
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
                    if (!$is_starter_battle && !$is_player_battle && !$is_challenge_battle
                        && !empty($this_robotinfo['robot_class']) && $this_robotinfo['robot_class'] == 'mecha'
                        && $temp_robot_tokens_count > 1 && $this_master_count > 0){
                        continue;
                    }

                    // Update min/max level indicators
                    $this_robot_level = !empty($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : 1;
                    if (true){
                        if ($this_option_min_level === false || $this_option_min_level > $this_robot_level){ $this_option_min_level = $this_robot_level; }
                        if ($this_option_max_level === false || $this_option_max_level < $this_robot_level){ $this_option_max_level = $this_robot_level; }
                    }

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
                        'shadow' => $use_shadow,
                        'kind' => 'robot',
                        'token' => $this_robotinfo['robot_token']
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
                    $temp_star_front = array('path' => 'items/field-star_'.$temp_field_type_1, 'frame' => '02', 'size' => 40, 'kind' => 'star', 'token' => 'field');
                    $temp_star_back = array('path' => 'items/field-star_'.$temp_field_type_2, 'frame' => '01', 'size' => 40, 'kind' => 'star', 'token' => 'field');
                } elseif ($temp_star_kind == 'fusion'){
                    $temp_star_front = array('path' => 'items/fusion-star_'.$temp_field_type_1, 'frame' => '02', 'size' => 40, 'kind' => 'star', 'token' => 'fusion');
                    $temp_star_back = array('path' => 'items/fusion-star_'.$temp_field_type_2, 'frame' => '01', 'size' => 40, 'kind' => 'star', 'token' => 'fusion');
                }
                array_unshift($this_battleinfo['battle_sprite'], $temp_star_front, $temp_star_back);

            }

            // Add the challenge marker sprite if one has been added
            $this_has_challenge_marker = false;
            if (!empty($this_battleinfo['values']['challenge_marker'])){
                $this_has_challenge_marker = true;
                $this_option_disabled = false;
                $temp_kind = $this_battleinfo['values']['challenge_marker'];
                $temp_sprite = array('path' => 'objects/challenge-markers/'.$temp_kind, 'frame' => '00', 'size' => 40, 'kind' => 'marker', 'token' => 'challenge');
                array_unshift($this_battleinfo['battle_sprite'], $temp_sprite);
            }

            // Loop through the battle sprites and display them
            if (!empty($this_battleinfo['battle_sprite'])){
                $temp_right = false;
                $temp_layer = 100;
                $temp_count = count($this_battleinfo['battle_sprite']);
                $disable_hiding_robots = false;
                if (MMRPG_CONFIG_IS_LIVE === false && MMRPG_CONFIG_DEBUG_MODE === true){ $disable_hiding_robots = true; }
                $this_option_label .= '<span class="battle_sprites">';
                foreach ($this_battleinfo['battle_sprite'] AS $temp_key => $this_battle_sprite){
                    $temp_opacity = $temp_layer == 10 ? 1 : 1 - ($temp_key * 0.09);
                    $temp_path = $this_battle_sprite['path'];
                    $temp_size = $this_battle_sprite['size'];
                    $temp_shadow = isset($this_battle_sprite['shadow']) ? $this_battle_sprite['shadow'] : false;
                    $temp_kind = isset($this_battle_sprite['kind']) ? $this_battle_sprite['kind'] : 'sprite';
                    $temp_kind_token = isset($this_battle_sprite['token']) ? $this_battle_sprite['token'] : '';
                    $temp_other_styles = '';
                    if ($temp_shadow){
                        $temp_other_styles .= '-webkit-filter: grayscale(100%); filter: grayscale(100%); ';
                        $temp_opacity *= 0.5;
                        }
                    if (!$disable_hiding_robots
                        && preg_match('/^robots/i', $temp_path)
                        && $this_targetinfo['player_id'] != MMRPG_SETTINGS_TARGET_PLAYERID){
                        $temp_path = 'robots/robot';
                        $temp_size = 40;
                        }
                    $temp_frame = !empty($this_battle_sprite['frame']) ? $this_battle_sprite['frame'] : '';
                    $temp_size_text = $temp_size.'x'.$temp_size;
                    $temp_top = -2;
                    if (!preg_match('/^(abilities|items)/i', $temp_path)){
                        if ($temp_right === false){
                            $temp_right_inc =  0;
                            $temp_right = 0;
                            $temp_right += $temp_right_inc;
                        } else {
                            $temp_right_inc = 20;
                            $temp_right += $temp_right_inc;
                        }
                    } else {
                        $temp_right = 0;
                    }

                    if (strstr($temp_path, 'challenge-marker')){
                        $this_option_label .= '<span class="sprite sprite_'.$temp_kind.' sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_'.str_pad($temp_frame, 2, '0', STR_PAD_LEFT).' " style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: -3px; right: -5px; z-index: '.$temp_layer.'; opacity: '.$temp_opacity.'; '.$temp_other_styles.'">&nbsp;</span>';
                    } elseif (preg_match('/^(abilities|items)/i', $temp_path)){
                        $this_option_label .= '<span class="sprite sprite_'.$temp_kind.' sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_'.str_pad($temp_frame, 2, '0', STR_PAD_LEFT).' " style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); top: 1px; right: -3px; z-index: '.$temp_layer.'; opacity: '.$temp_opacity.'; '.$temp_other_styles.'">&nbsp;</span>';
                    } else {
                        if ($temp_kind === 'robot' && !empty($temp_kind_token)) {
                            $robot_animation_duration = rpg_robot::get_css_animation_duration($temp_kind_token);
                            if ($this_option_complete){ $robot_animation_duration *= 4; }
                            $temp_other_styles .= 'animation-duration: '.$robot_animation_duration.'s; ';
                            if ($this_option_complete){ $temp_other_styles .= 'animation-delay: '.(-1 * (($this_key + $temp_key) * 0.1)).'s; '; }
                        }
                        $this_option_label .= '<span class="sprite sprite_'.$temp_kind.' sprite_40x40 sprite_40x40_00" style="right: '.$temp_right.'px; z-index: '.$temp_layer.';">';
                            $this_option_label .= '<span class="sprite sprite_'.$temp_size_text.' '.($this_option_complete && !$this_has_field_star && $this_option_frame == 'base' ? 'sprite_'.$temp_size_text.'_defeat ' : 'sprite_'.$temp_size_text.'_'.$this_option_frame.' ').'" style="background-image: url(images/'.$temp_path.'/sprite_left_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.$temp_other_styles.'">&nbsp;</span>';
                        $this_option_label .= '</span>';
                    }

                    $temp_layer -= 1;
                }
                $this_option_label .= '</span>';
            }

            if (!empty($this_battleinfo['battle_button'])){ $this_option_button_text = $this_battleinfo['battle_button']; }
            elseif (!empty($this_fieldinfo['field_name'])){ $this_option_button_text = $this_fieldinfo['field_name']; }
            else { $this_option_button_text = 'Battle'; }

            if ($this_option_min_level < 1){ $this_option_min_level = 1; }
            if ($this_option_max_level > 100){ $this_option_max_level = 100; }
            $this_option_level_range = $this_option_min_level == $this_option_max_level ? 'Level '.$this_option_min_level : 'Levels '.$this_option_min_level.'-'.$this_option_max_level;

            $this_option_zenny_amount = number_format($this_option_zenny, 0, '.', ',').' Zenny';

            if (!empty($this_option_button_text)){
                $this_option_label .= '<span class="info" />';
                $this_option_label .= '<span class="multi">';
                    if (($is_player_battle || $is_challenge_battle) && !$is_endless_battle){
                        $this_option_label .= '<span class="maintext">'.$this_option_button_text.'</span>';
                        $robots = $this_option_limit.($this_option_limit == 1 ? ' R' : ' Rs');
                        $turns = $this_option_turns.($this_option_turns == 1 ? ' T' : ' Ts');
                        $zenny = str_replace('Zenny', 'Zs', $this_option_zenny_amount);
                        $this_option_label .= '<span class="subtext">'.$robots.' | '.$turns.' | '.$zenny.'</span>';
                        if ($is_player_battle){
                            $level_txt = 'Lv. '.$this_option_max_level;
                            $this_option_label .= '<span class="subtext2">'.$level_txt.' @ '.$this_fieldinfo['field_name'].'</span>';
                        }
                        elseif ($is_challenge_battle){
                            if ($this_battleinfo['values']['challenge_battle_kind'] == 'user'){
                                $this_option_label .= '<span class="subtext2">By '.ucwords($this_battleinfo['values']['challenge_battle_by']).'</span>';
                            } elseif ($this_battleinfo['values']['challenge_battle_kind'] == 'event'){
                                $this_option_label .= '<span class="subtext2">Special Event Mission</span>';
                            }
                        }
                    } elseif ($is_endless_battle){
                        $this_option_label .= '<span class="maintext">&#10022; '.$this_option_button_text.' &#10022;</span>';
                        $robots = $this_option_limit.($this_option_limit == 1 ? ' R' : ' Rs');
                        //$this_option_label .= '<span class="subtext">6 Rs | &#8734; Ts | &#8734; Zs</span>';
                        $this_option_label .= '<span class="subtext">'.$robots.' | ???? Ts | ???? Zs</span>';
                        $this_option_label .= '<span class="subtext2">All-Star Challenge Mission</span>';
                    } else {
                        $modded_this_option_button_text = $this_option_button_text;
                        $modded_this_option_button_text = str_replace(' ', ' <br />', $modded_this_option_button_text);
                        $modded_this_option_button_text = str_replace('<br />II', 'II', $modded_this_option_button_text);
                        $this_option_label .= '<span class="maintext">'.$modded_this_option_button_text.'</span>';
                        $this_option_label .= '<span class="subtext">'.$this_option_level_range.'</span>';
                        $this_option_label .= '<span class="subtext2">'.$this_option_zenny_amount.'</span>';
                    }
                $this_option_label .= '</span>';
                if (!$this_has_field_star && (!$this_option_complete || ($this_option_complete && $this_option_encore))){
                    //$this_option_label .= '<span class="arrow"> &#9658;</span>';
                    $icon = 'play';
                    $icon_count = 1;
                    if (!empty($this_battleinfo['alpha_battle_token'])){ $icon_count++; }
                    if (!empty($this_battleinfo['battle_complete_redirect_token'])){ $icon_count++; }
                    $arrow_markup = '<span class="arrow s'.$icon_count.'">';
                    for ($i = 0; $i < $icon_count; $i++){ $arrow_markup .= '<i class="fa fas fa-'.$icon.'"></i>'; }
                    $arrow_markup .= '</span>';
                    $this_option_label .= $arrow_markup;
                }
            } else {
                $this_option_label .= '<span class="single">???</span>';
            }

            // Generate this options hover tooltip details
            $this_option_title = '';

            // If this is a NORMAL MISSION and not an endless one, display normal button text
            if (!$is_endless_battle){

                $this_option_title .= '&laquo; '.$this_battleinfo['battle_name'].' &raquo;';
                if ($is_challenge_battle && !empty($this_battleinfo['battle_button'])){ $this_option_title .= ' <br />&quot;'.$this_battleinfo['battle_button'].'&quot;'; }

                $this_option_title .= ' <br />'.$this_fieldinfo['field_name'];
                if (!empty($this_fieldinfo['field_type'])){
                    if (!empty($this_fieldinfo['field_type2'])){ $this_option_title .= ' | '.ucfirst($this_fieldinfo['field_type']).' / '.ucfirst($this_fieldinfo['field_type2']).' Type'; }
                    else { $this_option_title .= ' | '.ucfirst($this_fieldinfo['field_type']).' Type'; }
                }
                $this_option_title .= ' <br />'.$this_option_level_range;

                if ($this_option_limit < 8){ $this_option_title .= ' | '.($this_option_limit == 1 ? '1 Robot' : $this_option_limit.' Robots'); }
                $this_option_title .= ' | '.($this_option_turns == 1 ? '1 Turn' : $this_option_turns.' Turns');

                if (!empty($this_battleinfo['battle_zenny'])){
                    $this_option_title .= ' | '.($this_battleinfo['battle_zenny'] == 1 ? '1 Zenny' : number_format($this_battleinfo['battle_zenny'], 0, '.', ',').' Zenny');
                }

            }
            // Otherwise if this is an ENDLESS ATTACK MODE mission, display a condensed header
            elseif ($is_endless_battle){

                if ($is_challenge_battle && !empty($this_battleinfo['battle_button'])){ $this_option_title .= '&#10022; '.$this_battleinfo['battle_button'].' &#10022;<br /> '; }
                $this_option_title .= '&laquo; '.$this_battleinfo['battle_name'].' &raquo;';

            }

            $this_option_title .= ' <br />'.$this_battleinfo['battle_description'];
            if (!empty($this_battleinfo['battle_description2'])){ $this_option_title .= ' '.$this_battleinfo['battle_description2']; }

            if (!isset($this_battleinfo['battle_counts'])
                || $this_battleinfo['battle_counts'] !== false){
                if (!empty($this_option_complete) || !empty($this_option_failure) || !empty($this_has_field_star)){
                    $this_option_title .= ' <hr />&laquo; Battle Records &raquo;';
                    $this_option_title .= ' <br />Cleared : '.(!empty($this_option_complete['battle_count']) ? ($this_option_complete['battle_count'] == 1 ? '1 Time' : $this_option_complete['battle_count'].' Times') : '0 Times');
                    $this_option_title .= ' | Failed : '.(!empty($this_option_failure['battle_count']) ? ($this_option_failure['battle_count'] == 1 ? '1 Time' : $this_option_failure['battle_count'].' Times') : '0 Times');
                }
            } elseif ($is_challenge_battle
                && !$is_endless_battle
                && !empty($this_battleinfo['values']['challenge_records'])){
                    $temp_records = $this_battleinfo['values']['challenge_records'];
                    //$this_option_title .= ' <br />JSON: '.str_replace('"', '&quot;', json_encode($temp_records));
                    if (!empty($temp_records['accessed'])){
                        $this_option_title .= ' <hr />&laquo; Global Challenge Records &raquo;';
                        $this_option_title .= ' <br />Attempted: '.($temp_records['accessed'] === 1 ? '1 Time ' : number_format($temp_records['accessed'], 0, '.', ',').' Times');
                        $this_option_title .= ' | Failed: '.($temp_records['defeats'] === 1 ? '1 Time ' : number_format($temp_records['defeats'], 0, '.', ',').' Times');
                        $this_option_title .= ' | Cleared: '.($temp_records['victories'] === 1 ? '1 Time ' : number_format($temp_records['victories'], 0, '.', ',').' Times');
                        //$this_option_title .= ' <br />Success Rate: '.str_replace('.00', '', number_format((($temp_records['victories'] / $temp_records['accessed']) * 100), 2, '.', ',')).'%';
                    }
                    if (!empty($temp_records['personal'])){
                        $victory_results = $temp_records['personal'];
                        $victory_points = rpg_mission_challenge::calculate_challenge_reward_points($this_battleinfo['values']['challenge_battle_kind'], $victory_results, $victory_percent, $victory_rank);
                        $this_option_title .= ' <hr />&laquo; Your Challenge Records &raquo;';
                        //$this_option_title .= ' <br />'.$victory_rank.'-Rank Clear!';
                        $this_option_title .= ' <br />Turns: '.$victory_results['challenge_turns_used'].'/'.$victory_results['challenge_turn_limit'];
                        $this_option_title .= ' | Robots: '.$victory_results['challenge_robots_used'].'/'.$victory_results['challenge_robot_limit'];
                        $this_option_title .= ' | Reward: '.number_format($victory_points, 0, '.', ',').' BP ('.$victory_percent.'%)';
                    }

            } elseif ($is_challenge_battle
                && $is_endless_battle){

                // Check to see if there's an existing record and print high score if we're better
                static $personal_wave_record = false;
                static $global_wave_record = false;
                if ($personal_wave_record === false
                    || $global_wave_record === false){
                    $current_user_id = rpg_user::get_current_userid();
                    $old_waves_completed = (int)($db->get_value("SELECT challenge_waves_completed FROM mmrpg_challenges_waveboard WHERE user_id = {$current_user_id} AND challenge_result = 'victory';", 'challenge_waves_completed'));
                    if (!empty($old_waves_completed)){ $personal_wave_record = $old_waves_completed; }
                    else { $personal_wave_record = 0; }
                    $global_waves_completed = (int)($db->get_value("SELECT MAX(challenge_waves_completed) AS max_waves_completed FROM mmrpg_challenges_waveboard WHERE challenge_result = 'victory';", 'max_waves_completed'));
                    if (!empty($global_waves_completed)){ $global_wave_record = $global_waves_completed; }
                    else { $global_wave_record = 0; }
                }

                // Print out the challenge record headers and personal vs global high scores
                if (!empty($personal_wave_record)
                    || !empty($global_wave_record)){
                    $this_option_title .= ' <hr />&laquo; Endless Challenge Records &raquo;<br /> ';
                    if (!empty($personal_wave_record)){ $this_option_title .= 'Personal: '.number_format($personal_wave_record, 0, '.', ',').' Missions'; }
                    if (!empty($personal_wave_record) && !empty($global_wave_record)){ $this_option_title .= ' | '; }
                    if (!empty($global_wave_record)){ $this_option_title .= 'Global: '.number_format($global_wave_record, 0, '.', ',').' Missions'; }
                }

            }

            //$this_option_title .= '<br /> battle_rewards: '.(!empty($this_battleinfo['battle_rewards']) ? json_encode($this_battleinfo['battle_rewards']) : '---');
            //$this_option_title .= '<br /> player_starforce: '.(!empty($this_battleinfo['battle_target_player']['player_starforce']) ? json_encode($this_battleinfo['battle_target_player']['player_starforce']) : '---');

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
            $this_image_style = '';
            $is_starfield_mission = !empty($this_battleinfo['flags']['starfield_mission']) ? true : false;
            if ($is_starfield_mission){
                $this_option_class .= ' starfield';
                if (!empty($this_battleinfo['battle_complete_redirect_token'])){
                    $this_option_class .= ' starshake';
                    $this_option_class .= ' dx';
                } elseif (!empty($this_battleinfo['battle_rewards']['robots'])){
                    if ($star_shake_delay[$player_token] >= 7){ $star_shake_delay[$player_token] = 0; }
                    $star_shake_delay[$player_token] += 1;
                    $this_option_class .= ' starshake';
                    $animation_delay_seconds = ($star_shake_delay[$player_token] / 3) + (0.1 * mt_rand(1, 3));
                    $this_image_style .= ' -moz-animation-delay: '.$animation_delay_seconds.'s; -webkit-animation-delay: '.$animation_delay_seconds.'s; animation-delay: '.$animation_delay_seconds.'s;';
                } else {
                    $animation_delay_seconds = -5 + (1 * ($this_key * 0.12));
                    //error_log('$this_key = '.print_r($this_key, true));
                    //error_log('$animation_delay_seconds = '.print_r($animation_delay_seconds, true));
                    $this_image_style .= ' -moz-animation-delay: '.$animation_delay_seconds.'s; -webkit-animation-delay: '.$animation_delay_seconds.'s; animation-delay: '.$animation_delay_seconds.'s;';
                }
            }

            // Define a variable to hold number of selectable robots, default to allowed max, but decrease for specific reasons
            $data_next_limit = $this_option_limit;
            $unlocked_robots_num = mmrpg_prototype_robots_unlocked($player_token);
            $limit_hearts_earned = mmrpg_prototype_limit_hearts_earned($player_token);
            if ($data_next_limit > $unlocked_robots_num){ $data_next_limit = $unlocked_robots_num; }
            if ($data_next_limit > $limit_hearts_earned && !$is_endless_battle && !$is_challenge_battle){ $data_next_limit = $limit_hearts_earned; }

            $btn_info_circle = '<span class="info color '.$field_type_or_empty.'" data-click-tooltip="'.$this_option_title_tooltip.'" data-tooltip-type="'.$this_type_class.'"><i class="fa fas fa-info-circle"></i></span>';
            $this_option_label = str_replace('<span class="info" />', $btn_info_circle, $this_option_label);

            // Print out the option button markup with sprite and name
            $this_markup .= '<a '.
                'class="'.$this_option_class.'" '.
                'data-token="'.(!empty($this_battleinfo['alpha_battle_token']) ? $this_battleinfo['alpha_battle_token'] : $this_battleinfo['battle_token']).'" '.
                (!empty($this_battleinfo['option_target_href']) ? 'data-next-href="'.$this_battleinfo['option_target_href'].'" ' : '').
                'data-next-limit="'.$data_next_limit.'" '.
                'data-chapter="'.$this_info['option_chapter'].'" '.
                'data-field="'.htmlentities($this_fieldinfo['field_name'], ENT_QUOTES, 'UTF-8', true).'" '.
                'data-description="'.htmlentities(($this_battleinfo['battle_description'].(!empty($this_battleinfo['battle_description2']) ? ' '.$this_battleinfo['battle_description2'] : '')), ENT_QUOTES, 'UTF-8', true).'" '.
                'data-multipliers="'.$temp_field_multipliers.'" '.
                'data-background="'.(!empty($this_fieldinfo['field_background']) ? $this_fieldinfo['field_background'] : '').'" '.
                (!empty($this_fieldinfo['field_background_variant']) ? 'data-background-variant="'.$this_fieldinfo['field_background_variant'].'" ' : '').
                'data-foreground="'.(!empty($this_fieldinfo['field_foreground']) ? $this_fieldinfo['field_foreground'] : '').'" '.
                (!empty($this_fieldinfo['field_foreground_variant']) ? 'data-foreground-variant="'.$this_fieldinfo['field_foreground_variant'].'" ' : '').
                'style="'.$this_option_style.(!empty($this_info['option_style']) ? ' '.$this_info['option_style'] : '').'" '.
                '>';
                $this_markup .= '<div class="platform" style="'.$this_option_platform_style.'">';
                    $this_markup .= '<div class="chrome">';
                        $this_markup .= '<div class="inset">';
                            $this_markup .= '<label class="'.(!empty($this_battleinfo['battle_sprite']) ? 'has_image' : 'no_image').'"'.(!empty($this_image_style) ? ' style="'.$this_image_style.'"' : '').'>';
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

// Define a function for extracting a mecha-only "alpha" battle from an omega one
function mmrpg_prototypt_extract_alpha_battle(&$temp_battle_omega, $this_prototype_data){

    // Collect a temporary object indexes for reference
    static $mmrpg_index_robots, $mmrpg_index_fields;
    if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(); }
    if (empty($mmrpg_index_fields)){ $mmrpg_index_fields = rpg_field::get_index(); }

    // DEBUG DEBUG DEBUG
    //$temp_battle_omega['values']['debug']['target_robots_backup'] = json_encode($temp_battle_omega['battle_target_player']['player_robots']);

    // Backup the base name if not exists yet
    if (!isset($temp_battle_omega['battle_base_name'])){ $temp_battle_omega['battle_base_name'] = $temp_battle_omega['battle_name']; }

    // Collect the player token and other battle info
    $player_token = $this_prototype_data['this_player_token'];
    $battle_phase = $this_prototype_data['battle_phase'];
    $battle_field = $temp_battle_omega['battle_field_base'];
    $battle_name = $temp_battle_omega['battle_base_name'];
    $omega_robot_level = $temp_battle_omega['battle_level'];

    // Define the stat boost power based on phase alone
    //$master_boost_power = $battle_phase > 1 ? 4 : 2;

    // Define the battle kind, default to 'other'
    $battle_kind = 'other';
    if (!empty($temp_battle_omega['flags']['single_battle'])){ $battle_kind = 'single'; }
    elseif (!empty($temp_battle_omega['flags']['double_battle'])){ $battle_kind = 'double'; }

    // Check to see if this is a starfield mission
    $is_starfield_mission = false;
    if (!empty($temp_battle_omega['flags']['starfield_mission'])){ $is_starfield_mission = true; }

    // Define the number of mechas + abilities to add based on player + phase
    $num_support_mechas = $battle_phase > 1 ? 4 : 3;
    if ($player_token == 'dr-light'){
        //$num_support_mechas = $battle_phase > 1 ? 4 : 3;
        $num_mecha_abilities = $battle_phase > 1 ? 2 : 1;
        $super_block_position = false;
    } elseif ($player_token == 'dr-wily'){
        //$num_support_mechas = $battle_phase > 1 ? 5 : 4;
        $num_mecha_abilities = $battle_phase > 1 ? 3 : 2;
        $super_block_position = $battle_phase > 1 ? 'right-bench' : false;
    } elseif ($player_token == 'dr-cossack'){
        //$num_support_mechas = $battle_phase > 1 ? 6 : 5;
        $num_mecha_abilities = $battle_phase > 1 ? 4 : 3;
        $super_block_position = $battle_phase > 1 ? 'right' : false;
    }

    // Collect details about this battle field
    $field1_info = !empty($battle_field['field_background']) ? $mmrpg_index_fields[$battle_field['field_background']] : $mmrpg_index_fields[$battle_field['field_token']];
    $field2_info = !empty($battle_field['field_foreground']) ? $mmrpg_index_fields[$battle_field['field_foreground']] : $mmrpg_index_fields[$battle_field['field_token']];


    /* REMOVE DEFAULT MECHAS */

    // Create an array to hold any robots that were intentionally added for the alpha battle
    $temp_added_alpha_robots = array();
    $temp_added_alpha_robots_tokens = array();

    // First and foremost, remove and previously added mecha from the battle
    $temp_player_robots = $temp_battle_omega['battle_target_player']['player_robots'];
    foreach ($temp_player_robots AS $key => $robot_info){
        $robot_token = $robot_info['robot_token'];
        $index_info = $mmrpg_index_robots[$robot_token];
        if ($index_info['robot_class'] == 'mecha'){
            unset($temp_player_robots[$key]);
            continue;
        }
        if (!empty($robot_info['flags'])
            && !empty($robot_info['flags']['robot_is_visitor'])){
            //error_log('robot_is_visitor for '.$robot_token);
            $temp_added_alpha_robots[] = $robot_info;
            $temp_added_alpha_robots_tokens[] = $robot_token;
            unset($temp_player_robots[$key]);
            continue;
        }
    }

    // Re-key the target player robots for better looping
    $temp_player_robots = array_values($temp_player_robots);
    $temp_battle_omega['battle_target_player']['player_robots'] = $temp_player_robots;


    /* GENERATE ALPHA BATTLE (MECHAS) */

    // Clone the omega battle and then adjust some variables, then remove robot masters
    $temp_include_visitor_robot = false;
    $temp_include_doctor_mecha = $battle_kind === 'double' && !$is_starfield_mission ? true : false;
    if (!empty($temp_added_alpha_robots)){ $temp_include_visitor_robot = true; $temp_include_doctor_mecha = false;  }
    $temp_battle_alpha = array_merge(array(), $temp_battle_omega);
    $temp_battle_alpha['battle_token'] = $temp_battle_omega['battle_token'].'-alpha';
    $temp_battle_alpha['battle_complete_redirect_token'] = $temp_battle_omega['battle_token'];
    $temp_battle_alpha['battle_name'] = $battle_name.' (1/2)';
    $temp_battle_alpha['battle_round'] = 0;
    $temp_battle_alpha['battle_description'] = 'Defeat the support mechas blocking your path to the robot master'.($battle_phase > 1 ? 's' : '').'!';
    $temp_battle_alpha['battle_counts'] = false;
    $temp_battle_alpha['battle_field_base']['values']['hazards'] = array();
    $temp_battle_alpha['battle_field_base']['field_music']  = !empty($field1_info['field_music']) ? $field1_info['field_music'] : $field1_info['field_token'];
    $temp_user_id = $temp_battle_alpha['battle_target_player']['user_id'];
    $temp_player_id = $temp_battle_alpha['battle_target_player']['player_id'];
    $temp_player_robots = array();
    $temp_mecha_options = $temp_battle_omega['battle_field_base']['field_mechas'];
    if ($temp_include_doctor_mecha){
        array_unshift($temp_mecha_options, rpg_player::get_support_mecha($this_prototype_data['this_player_token'], false));
        //error_log('(t2) $temp_mecha_options = '.print_r($temp_mecha_options, true));
    }
    $temp_mecha_options_num = count($temp_mecha_options);
    $temp_mecha_options_maxkey = $temp_mecha_options_num - 1;
    $temp_mecha_counters = array();
    for ($i = 0; $i < $num_support_mechas; $i++){
        if ($temp_include_doctor_mecha
            && $i === 0){
            $mecha_token = array_shift($temp_mecha_options);
            $temp_mecha_options_num = count($temp_mecha_options);
            $temp_mecha_options_maxkey = $temp_mecha_options_num - 1;
        } else {
            if ($temp_mecha_options_maxkey > 0){ $option_key = (($i + 1) % $temp_mecha_options_num); }
            else { $option_key = 0; }
            $mecha_token = $temp_mecha_options[$option_key];
        }
        $index_info = $mmrpg_index_robots[$mecha_token];
        $robot_info = array();
        $robot_info['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $index_info['robot_id'], ($i + 1));
        $robot_info['robot_token'] = $index_info['robot_token'];
        $robot_info['robot_level'] = $omega_robot_level > 1 ? ($omega_robot_level - 1) : 1;
        $robot_info['robot_abilities'] = mmrpg_prototype_generate_abilities($index_info, $robot_info['robot_level'], $num_mecha_abilities, '');
        if (!isset($temp_mecha_counters[$mecha_token])){ $temp_mecha_counters[$mecha_token] = 0; }
        $temp_mecha_counters[$mecha_token] += 1;
        $temp_player_robots[] = $robot_info;
    }
    // If there are visitor robots, place them somewhere on the bench
    if ($temp_include_visitor_robot){
        // If there's exactly one visitor robot, place it in the middle of the bench
        if (count($temp_added_alpha_robots) === 1){
            // odd-numbered robots, so replace the middle robot
            $key_to_replace = ceil(($num_support_mechas - 1) / 2);
            $temp_player_robots[$key_to_replace] = $temp_added_alpha_robots[0];
        }
        // Otherwise if there are exactly two robots, place them at each end
        elseif (count($temp_added_alpha_robots) === 2){
            $temp_player_robots[1] = $temp_added_alpha_robots[0];
            $temp_player_robots[$num_support_mechas - 1] = $temp_added_alpha_robots[1];
        }
        // Otherwise, simply replace every-other benched robot until we've reached the limit
        else {
            $key_to_replace = 1;
            foreach ($temp_added_alpha_robots AS $robot_info){
                $temp_player_robots[$key_to_replace] = $robot_info;
                $key_to_replace += 2;
            }
        }
    }
    //shuffle($temp_player_robots);
    //$temp_player_robots = array_values($temp_player_robots);
    $temp_battle_alpha['battle_target_player']['player_switch'] = 1.5;
    $temp_battle_alpha['battle_target_player']['player_robots'] = $temp_player_robots;
    if ($temp_include_visitor_robot || $temp_include_doctor_mecha){
        //error_log('(t2) $temp_mecha_counters = '.print_r($temp_mecha_counters, true));
        //error_log('(t2) $temp_battle_alpha[\'battle_field_base\'][\'field_mechas\'] = '.print_r($temp_battle_alpha['battle_field_base']['field_mechas'], true));
        //error_log('(t2) $temp_battle_alpha[\'battle_target_player\'][\'player_robots\'] = '.print_r($temp_battle_alpha['battle_target_player']['player_robots'], true));
    }

    // Update the zenny and turns for this alpha mecha battle
    if (isset($temp_battle_alpha['battle_zenny'])){ $temp_battle_alpha['battle_zenny'] = ceil($temp_battle_alpha['battle_zenny'] * 0.10); }
    if (isset($temp_battle_alpha['battle_turns'])){ $temp_battle_alpha['battle_turns'] = count($temp_battle_alpha['battle_target_player']['player_robots']) * MMRPG_SETTINGS_BATTLETURNS_PERMECHA; }

    // Clear the rewards, but make sure we don't remove any related to visitor robots
    if (isset($temp_battle_alpha['battle_rewards'])){
        $backup_battle_rewards = $temp_battle_alpha['battle_rewards'];
        $temp_battle_alpha['battle_rewards'] = array();
        if ($temp_include_visitor_robot
            && !empty($backup_battle_rewards['robots'])){
            foreach ($backup_battle_rewards['robots'] AS $key => $robot){
                if (in_array($robot['token'], $temp_added_alpha_robots_tokens)){
                    $temp_battle_alpha['battle_rewards']['robots'][] = $robot;
                    unset($temp_battle_omega['battle_rewards']['robots'][$key]);
                }
            }
        }
    }


    /* UPDATE EXISTING OMEGA BATTLE (MASTERS) */

    // Add super block protection on the target robot side of the field
    $temp_battle_omega['battle_name'] = $battle_name.' (2/2)';
    if (!empty($super_block_position)){ $temp_battle_omega['battle_field_base']['values']['hazards']['super_blocks'] = $super_block_position; }
    $temp_battle_omega['battle_field_base']['field_music']  = !empty($field1_info['field_music']) ? $field1_info['field_music'] : $field1_info['field_token'];

    // Update the omega battle with a new token, then remove all support mechas, boost robot master
    $temp_player_robots = $temp_battle_omega['battle_target_player']['player_robots'];
    foreach ($temp_player_robots AS $key => $robot_info){
        $robot_token = $robot_info['robot_token'];
        $index_info = $mmrpg_index_robots[$robot_token];
        //$best_stat = rpg_robot::get_best_stat($index_info);
        //$robot_info['counters'][$best_stat.'_mods'] = $master_boost_power;
        //$worst_stat = rpg_robot::get_worst_stat($index_info);
        //$robot_info['counters'][$worst_stat.'_mods'] = floor($master_boost_power / 2);
        $temp_player_robots[$key] = $robot_info;
    }
    $temp_player_robots = array_values($temp_player_robots);
    $temp_battle_omega['battle_target_player']['player_robots'] = $temp_player_robots;

    // Change the music to the boss encounter theme relative to the master's source game
    if (!$is_starfield_mission){
        if ($battle_kind === 'single'){
            $trobots = array_values($temp_battle_omega['battle_target_player']['player_robots']);
            if (!empty($trobots)){
                $atoken = 'sega-remix';
                $rtoken = $trobots[0]['robot_token'];
                $gtoken = strtolower($mmrpg_index_robots[$rtoken]['robot_game']);
                if ($gtoken === 'mmpu'){ $gtoken = 'mm1'; }
                $music_path = $atoken.'/boss-theme-'.$gtoken;
                if (rpg_game::sound_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path)){
                    $temp_battle_omega['battle_field_base']['field_music'] = $music_path;
                }
            }
        } elseif ($battle_kind === 'double'){
            $atoken = 'sega-remix';
            $mtoken = 'mid-boss-mm8';
            $music_path = $atoken.'/'.$mtoken;
            if (rpg_game::sound_exists(MMRPG_CONFIG_ROOTDIR.'sounds/'.$music_path)){
                $temp_battle_omega['battle_field_base']['field_music'] = $music_path;
            }
        }
    }

    // Add this alpha battle's token to the parent for later
    $temp_battle_omega['alpha_battle_token'] = $temp_battle_alpha['battle_token'];

    // Return the generated alpha battle
    return $temp_battle_alpha;


}

// Define a function for autoplaying one mission before another
function mmrpg_prototype_mission_autoplay_prepend(&$base_battle_omega, &$prepend_battle_omega, &$this_prototype_data, $is_hidden = false){
    $prepend_battle_omega['battle_complete_redirect_token'] = $base_battle_omega['battle_token'];
    $base_battle_omega['battle_name'] = preg_replace('/\s?\([0-9]+\/[0-9]+\)$/i', '', $base_battle_omega['battle_name']);
    $prepend_battle_omega['battle_name'] = preg_replace('/\s?\([0-9]+\/[0-9]+\)$/i', '', $prepend_battle_omega['battle_name']);
    if (!$is_hidden){
        $prepend_battle_omega['battle_name'] .= ' (1/2)';
        $base_battle_omega['battle_name'] .= ' (2/2)';
    }
    rpg_battle::update_index_info($base_battle_omega['battle_token'], $base_battle_omega);
    rpg_battle::update_index_info($prepend_battle_omega['battle_token'], $prepend_battle_omega);
    foreach ($this_prototype_data['battle_options'] AS $key => $battle_option){
        if (isset($battle_option['battle_token'])
            && $battle_option['battle_token'] == $base_battle_omega['battle_token']){
            $battle_option['alpha_battle_token'] = $prepend_battle_omega['battle_token'];
            $this_prototype_data['battle_options'][$key] = $battle_option;
        }
    }
    return true;
}

// Define a function for autoplaying one mission after another
function mmrpg_prototype_mission_autoplay_append(&$base_battle_omega, &$append_battle_omega, &$this_prototype_data, $is_hidden = false){
    $base_battle_omega['battle_complete_redirect_token'] = $append_battle_omega['battle_token'];
    $base_battle_omega['battle_name'] = preg_replace('/\s?\([0-9]+\/[0-9]+\)$/i', '', $base_battle_omega['battle_name']);
    $append_battle_omega['battle_name'] = preg_replace('/\s?\([0-9]+\/[0-9]+\)$/i', '', $append_battle_omega['battle_name']);
    if (!$is_hidden){
        $base_battle_omega['battle_name'] .= ' (1/2)';
        $append_battle_omega['battle_name'] .= ' (2/2)';
    }
    rpg_battle::update_index_info($base_battle_omega['battle_token'], $base_battle_omega);
    rpg_battle::update_index_info($append_battle_omega['battle_token'], $append_battle_omega);
    return true;
}

// Define a function for easily generating the basic mission data structure
function mmrpg_prototype_generate_mission($this_prototype_data,
    $battle_token,
    $battle_info = array(),
    $field_info = array(),
    $target_info = array(),
    $target_robots = array()){

    // Fix empty args in wrong format
    if (empty($battle_info) || !is_array($battle_info)){ $battle_info = array(); }
    if (empty($field_info) || !is_array($field_info)){ $field_info = array(); }
    if (empty($target_info) || !is_array($target_info)){ $target_info = array(); }
    if (empty($target_robots) || !is_array($target_robots)){ $target_robots = array(); }

    // Collect a temporary object indexes for reference
    static $mmrpg_index_players, $mmrpg_index_robots, $mmrpg_index_fields;
    if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }
    if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(true); }
    if (empty($mmrpg_index_fields)){ $mmrpg_index_fields = rpg_field::get_index(true); }

    // Pre-count the number of target robots
    $num_target_robots = count($target_robots);

    // Create the main battle array for the omega battle
    $temp_battle_omega = array();
    $temp_battle_omega = array_merge($temp_battle_omega, $battle_info);
    $temp_battle_omega['battle_token'] = $battle_token;
    $temp_battle_omega['battle_size'] = !empty($battle_info['battle_size']) ? $battle_info['battle_size'] : '1x4';
    $temp_battle_omega['battle_name'] = !empty($battle_info['battle_name']) ? $battle_info['battle_name'] : (!empty($battle_info['battle_button']) ? $battle_info['battle_button'] : ucwords(str_replace('-', ' ', $battle_token)));
    $temp_battle_omega['battle_description'] = !empty($battle_info['battle_description']) ? $battle_info['battle_description'] : 'Defeat the target robot'.($num_target_robots > 1 ? '' : '').'!';
    $temp_battle_omega['battle_counts'] = isset($battle_info['battle_counts']) ? $battle_info['battle_counts'] : true;
    $temp_battle_omega['option_chapter'] = !empty($battle_info['option_chapter']) ? $battle_info['option_chapter'] : $this_prototype_data['this_current_chapter'];
    $temp_battle_omega['battle_phase'] = !empty($battle_info['battle_phase']) ? $battle_info['battle_phase'] : $this_prototype_data['battle_phase'];
    $temp_battle_omega['battle_level'] = !empty($battle_info['battle_level']) ? $battle_info['battle_level'] : 100;
    $temp_battle_omega['battle_zenny'] = !empty($battle_info['battle_zenny']) ? $battle_info['battle_zenny'] : 0;
    $temp_battle_omega['battle_turns'] = !empty($battle_info['battle_turns']) ? $battle_info['battle_turns'] : 0;

    // Parse the target player array and fill-in missing fields, then add to battle
    $target_info['user_id'] = !empty($target_info['user_id']) ? $target_info['user_id'] : MMRPG_SETTINGS_TARGET_PLAYERID;
    $target_info['player_id'] = !empty($target_info['player_id']) ? $target_info['player_id'] : rpg_game::unique_player_id($target_info['user_id'], 0);
    $target_info['player_token'] = !empty($target_info['player_token']) ? $target_info['player_token'] : 'player';
    if ($target_info['player_token'] !== 'player'){ $target_info['player_id'] = rpg_game::unique_player_id($target_info['user_id'], $mmrpg_index_players[$target_info['player_token']]['player_id']); }
    $temp_battle_omega['battle_target_player'] = $target_info;

    // Parse the field info array and fill-in missing fields, then add to battle
    $field_info['field_id'] = !empty($field_info['field_id']) ? $field_info['field_id'] : 1000;
    $field_info['field_token'] = !empty($field_info['field_token']) ? $field_info['field_token'] : rpg_player::get_intro_field($target_info['player_token']);
    $temp_battle_omega['battle_field_base'] = $field_info;

    // Parse the target robot array and fill-in missing fields, then add to player and battle
    $auto_battle_zenny = 0;
    $auto_battle_turn_limit = 0;
    $auto_battle_robot_limit = 0;
    if (empty($target_robots) || !is_array($target_robots)){ $target_robots = array(); }
    foreach ($target_robots AS $key => $robot_info){ if (!isset($robot_info['robot_token'])){ unset($target_robots); continue; } }
    if (empty($target_robots)){ $target_robots[] = array('robot_token' => 'met'); }
    foreach ($target_robots AS $key => $robot_info){
        $index_info = $mmrpg_index_robots[$robot_info['robot_token']];
        $robot_info['robot_id'] = !empty($robot_info['robot_id']) ? $robot_info['robot_id'] : rpg_game::unique_robot_id($target_info['player_id'], $index_info['robot_id'], ($key + 1));
        $robot_info['robot_level'] = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : $temp_battle_omega['battle_level'];
        $robot_info['robot_item'] = !empty($robot_info['robot_item']) ? $robot_info['robot_item'] : '';
        $robot_info['robot_abilities'] = !empty($robot_info['robot_abilities']) ? $robot_info['robot_abilities'] : 'auto';
        $auto_battle_zenny += ($index_info['robot_class'] == 'mecha' ? MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 : MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL) * $robot_info['robot_level'];
        $auto_battle_turn_limit += $index_info['robot_class'] == 'mecha' ? MMRPG_SETTINGS_BATTLETURNS_PERMECHA : ($index_info['robot_class'] == 'boss' ? (MMRPG_SETTINGS_BATTLETURNS_PERBOSS) : MMRPG_SETTINGS_BATTLETURNS_PERROBOT);
        $auto_battle_robot_limit += $index_info['robot_class'] == 'mecha' ? 0.5 : ($index_info['robot_class'] == 'boss' ? 1.5 : 1.0);
        if ($robot_info['robot_abilities'] === 'auto'
            || !is_array($robot_info['robot_abilities'])){
            $num_abilities = ceil($temp_battle_omega['battle_level'] / 10);
            if ($num_abilities < 1){ $num_abilities = 1; } elseif ($num_abilities > 8){ $num_abilities = 8; }
            $num_abilities = 8;
            $robot_info['robot_abilities'] = mmrpg_prototype_generate_abilities($index_info, $robot_info['robot_level'], $num_abilities, $robot_info['robot_item']);
        }
        $target_robots[$key] = $robot_info;
    }
    if (empty($temp_battle_omega['battle_zenny'])){ $temp_battle_omega['battle_zenny'] = $auto_battle_zenny; }
    if ($temp_battle_omega['battle_turns'] === 'double'){ $temp_battle_omega['battle_turns'] = $auto_battle_turn_limit * 2; }
    elseif (empty($temp_battle_omega['battle_turns'])){ $temp_battle_omega['battle_turns'] = $auto_battle_turn_limit; }
    if (isset($battle_info['battle_robot_limit']) && $temp_battle_omega['battle_robot_limit'] == 'max'){ $battle_info['battle_robot_limit'] = MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX; }
    elseif (isset($battle_info['battle_robot_limit']) && $temp_battle_omega['battle_robot_limit'] == 'auto'){ $temp_battle_omega['battle_robot_limit'] = ceil($auto_battle_robot_limit); }
    elseif (empty($battle_info['battle_robot_limit']) || !is_numeric($battle_info['battle_robot_limit'])){ unset($battle_info['battle_robot_limit']); }
    $target_info['player_robots'] = $target_robots;
    $temp_battle_omega['battle_target_player']['player_robots'] = $target_info['player_robots'];

    // Return the generated omega battle
    return $temp_battle_omega;

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

    global $db;
    global $mmrpg_index_players;
    if (empty($mmrpg_index_players)){ $mmrpg_index_players = rpg_player::get_index(true); }

    // Define the temporary robot markup string
    $this_robots_markup = '';

    // Collect this player's index info
    $this_player_info = $mmrpg_index_players[$this_prototype_data['this_player_token']];

    // Check for any robots that are locked in the endless attack or otherwise
    $player_robots_locked = array();
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions($this_player_info['player_token']);
    //error_log('$endless_attack_savedata for '.$player_token.': '.print_r(array_keys($endless_attack_savedata), true));
    if (!empty($endless_attack_savedata)
        && !empty($endless_attack_savedata['robots'])){
        $endless_robot_robots = $endless_attack_savedata['robots'];
        $player_robots_locked = array_merge($player_robots_locked, $endless_robot_robots);
        $player_robots_locked = array_unique($player_robots_locked);
    }

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
    $temp_robot_option_count_shown = 0;
    $temp_player_favourites = mmrpg_prototype_robot_favourites();
    foreach ($this_prototype_data['robot_options'] AS $key => $info){
        if (in_array($info['robot_token'], $player_robots_locked)){ continue; }
        $temp_robot_option_count_shown++;
        $info = array_merge($this_robot_index[$info['robot_token']], $info);
        if (!isset($info['original_player'])){ $info['original_player'] = $this_prototype_data['this_player_token']; }
        $this_option_class = 'option option_this-robot-select option_this-'.$info['original_player'].'-robot-select option_'.($this_prototype_data['robots_unlocked'] === 1 ? '1x4' : ($this_prototype_data['robots_unlocked'] <= 2 ? '1x2' : '1x1')).' option_'.$info['robot_token'].' block_'.($key + 1);
        $this_option_style = '';
        $this_option_token = $info['robot_id'].'_'.$info['robot_token'];
        $this_option_image = !empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token'];
        $this_option_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
        $temp_size = $this_option_size;
        $temp_size_text = $temp_size.'x'.$temp_size;
        $temp_sprite_top = 0;
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
        $this_robot_core_or_none = !empty($this_robot_core) ? $this_robot_core : 'none';
        $this_robot_item = !empty($info['robot_item']) ? $info['robot_item'] : '';

        $this_robot_favourite = in_array($info['robot_token'], $temp_player_favourites) ? true : false;
        //$this_robot_name .= $this_robot_favourite ? ' <span class="icons favs"><i class="fa fas fa-thumbtack"></i></span>' : '';

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

        // Collect info about the robot's item if it exists
        $this_robot_item_info = !empty($this_robot_item) && isset($this_item_index[$this_robot_item]) ? $this_item_index[$this_robot_item] : array();

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
        if (!empty($this_robot_item_info)){ $this_option_title .= ' | + '.$this_robot_item_info['item_name'].' '; }
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
        $this_option_type_token = 'robot_type robot_type_'.($this_robot_core_or_none).(!empty($this_robot_core2) ? '_'.$this_robot_core2 : '');

        $stat_reward_icons = !empty($namestring) ? ' <span class="icons stats">'.$namestring.'</span>' : '';
        if (empty($stat_reward_icons)){ $temp_sprite_top += 6; }
        $pinned_fav_icons = $this_robot_favourite ? ' <span class="icons favs"><i class="fa fas fa-thumbtack"></i></span>' : '';

        $info_tooltip_icons = ' <span class="icons info color '.$this_robot_core_or_none.'" data-click-tooltip="'.$this_option_title_tooltip.'" data-tooltip-type="'.$this_option_type_token.'"><i class="fa fas fa-info-circle"></i></span>';

        $robot_sprite_url = 'images/robots/'.$this_option_image.'/sprite_right_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;

        $robot_animation_duration = rpg_robot::get_css_animation_duration(array(
            'robot_attack' => $this_robot_attack,
            'robot_defense' => $this_robot_defense,
            'robot_speed' => $this_robot_speed
            ));

        $robot_sprite_markup = '';
        $robot_sprite_markup .= '<span class="sprite sprite_robot sprite_40x40 sprite_40x40_base" style="top: '.$temp_sprite_top.'px;">';
            $robot_sprite_markup .= '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url('.$robot_sprite_url.'); animation-duration: '.$robot_animation_duration.'s;"></span>';
        $robot_sprite_markup .= '</span>';

        $robot_item_sprite_markup = '';
        if (!empty($this_robot_item_info)){
            $item_sprite_url = 'images/items/'.$this_robot_item_info['item_image'].'/icon_right_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
            $robot_item_sprite_markup = '<span class="sprite sprite_item sprite_40x40 sprite_40x40_00" style="background-image: url('.$item_sprite_url.');"></span>';
        }

        $this_option_label = '';
        $this_option_label .= $robot_sprite_markup;
        //$this_option_label .= '<span class="battle_sprites">'.$robot_sprite_markup.'</span>';
        if (!empty($robot_item_sprite_markup)){ $this_option_label .= $robot_item_sprite_markup; }
        $this_option_label .= '<span class="multi">';
            $this_option_label .= $info_tooltip_icons;
            $this_option_label .= $pinned_fav_icons;
            $this_option_label .= $stat_reward_icons;
            $this_option_label .= '<span class="maintext">'.$this_robot_name.'</span>';
            $this_option_label .= '<span class="subtext">Level '.$this_robot_level.'</span>';
            $this_option_label .= '<span class="subtext2">'.$this_robot_experience.'/1000 Exp</span>';
        $this_option_label .= '</span>';
        $this_option_label .= '<span class="arrow">';
            $this_option_label .= '&#9658;';
        $this_option_label .= '</span>';

        $this_option_label_class = 'has_image ';
        if (!empty($stat_reward_icons)){ $this_option_label_class .= 'has_rewards '; }

        $this_robot_markup = '';
        $this_robot_markup .= '<a class="'.$this_option_class.'" data-child="true" data-token="'.$this_option_token.'" style="'.$this_option_style.'">';
        $this_robot_markup .= '<div class="chrome chrome_type '.$this_option_type_token.'"><div class="inset"><label class="'.$this_option_label_class.'">'.$this_option_label.'</label></div></div>';
        $this_robot_markup .= '</a>'."\r\n";


        $this_robots_markup .= $this_robot_markup;
    }

    // Loop through and display any option padding cells
    //if ($this_prototype_data['robots_unlocked'] >= 3){
    if ($temp_robot_option_count_shown >= 3){
        //$this_prototype_data['padding_num'] = $this_prototype_data['robots_unlocked'] <= 8 ? 4 : 2;
        $this_prototype_data['padding_num'] = 4;
        $this_prototype_data['robots_padding'] = $temp_robot_option_count_shown % $this_prototype_data['padding_num'];
        if (!empty($this_prototype_data['robots_padding'])){
            $counter = ($temp_robot_option_count_shown % $this_prototype_data['padding_num']) + 1;
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
function mmrpg_prototype_leaderboard_index($board_metric = ''){
    global $db;

    // Collect the current leaderboard metric in case we need it
    if (empty($board_metric)){ $board_metric = MMRPG_SETTINGS_CURRENT_LEADERBOARD_METRIC; }
    $rank_field = 'board_points';
    if ($board_metric === 'battle_points'){ $rank_field = 'board_points'; }
    elseif ($board_metric === 'battle_zenny'){ $rank_field = 'board_zenny'; }

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
            mmrpg_leaderboard.board_points,
            mmrpg_leaderboard.board_zenny
            FROM mmrpg_users
            LEFT JOIN mmrpg_leaderboard ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
            WHERE mmrpg_leaderboard.'.$rank_field.' > 0 ORDER BY mmrpg_leaderboard.'.$rank_field.' DESC
            ';
        // Query the database and collect the array list of all online players
        $this_leaderboard_index = $db->get_array_list($temp_leaderboard_query);
        // Update the database index cache
        $db->INDEX['LEADERBOARD']['index'] = json_encode($this_leaderboard_index);
    }

    // Return the collected leaderboard index
    return $this_leaderboard_index;
}

// Define a function for collecting the leaderboard ranking index
function mmrpg_prototype_leaderboard_rank_index($board_metric = ''){
    static $this_rank_index;
    if (empty($this_rank_index)){

        // Collect the current leaderboard metric in case we need it
        if (empty($board_metric)){ $board_metric = MMRPG_SETTINGS_CURRENT_LEADERBOARD_METRIC; }
        $rank_field = 'board_points';
        if ($board_metric === 'battle_points'){ $rank_field = 'board_points'; }
        elseif ($board_metric === 'battle_zenny'){ $rank_field = 'board_zenny'; }

        // Collect the leaderboard index for ranking
        $this_leaderboard_index = mmrpg_prototype_leaderboard_index();

        // Generate the points index and then break it down to unique for ranks
        $this_points_index = array();
        if (!empty($this_leaderboard_index)){
            foreach ($this_leaderboard_index AS $info){
                $this_points_index[] = $info[$rank_field];
            }
        }
        $this_points_index = array_unique($this_points_index);

        // Loop through all the players and generate a rank index
        $this_rank_index = array();
        foreach ($this_leaderboard_index AS $key => $board_info){
            $temp_rank = array_search($board_info[$rank_field], $this_points_index) + 1;
            $this_rank_index[$board_info['user_id']] = $temp_rank;
        }


    }
    return $this_rank_index;

}

// Define a function for collecting the requested player's board ranking
function mmrpg_prototype_leaderboard_rank($user_id, $board_metric = ''){
    global $db;

    // Collect the rank index to start
    $this_rank_index = mmrpg_prototype_leaderboard_rank_index($board_metric);

    // Return the rank of this user id if set
    return isset($this_rank_index[$user_id]) ? $this_rank_index[$user_id] : count($this_rank_index) + 1;

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

    // Collect the current leaderboard metric in case we need it
    $board_metric = MMRPG_SETTINGS_CURRENT_LEADERBOARD_METRIC;
    $rank_field = 'board_points';
    if ($board_metric === 'battle_points'){ $rank_field = 'board_points'; }
    elseif ($board_metric === 'battle_zenny'){ $rank_field = 'board_zenny'; }

    // Check to see if the leaderboard online has already been pulled or not
    if (!empty($db->INDEX['LEADERBOARD']['online'])){
        $this_leaderboard_online_players = json_decode($db->INDEX['LEADERBOARD']['online'], true);
    } else {
        // Collect the leaderboard index for ranking
        $this_leaderboard_index = mmrpg_prototype_leaderboard_index();
        // Generate the points index and then break it down to unique for ranks
        $this_points_index = array();
        if (!empty($this_leaderboard_index)){
            foreach ($this_leaderboard_index AS $info){
                $this_points_index[] = $info[$rank_field];
            }
        }
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
                    $temp_points = !empty($board[$rank_field]) ? $board[$rank_field] : 0;
                    $temp_place = array_search($board[$rank_field], $this_points_index) + 1;
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
                battles.target_user_id AS target_user_id,
                users.user_name_clean AS target_user_name,
                users.user_colour_token As target_user_colour,
                users.user_colour_token2 As target_user_colour2
                FROM mmrpg_battles AS battles
                INNER JOIN (
                    SELECT this_user_id, target_user_id, MAX(battle_id) AS max_battle_id
                    FROM mmrpg_battles
                    WHERE (this_user_id = {$this_userid} OR target_user_id = {$this_userid})
                    GROUP BY this_user_id, target_user_id
                ) AS latest_battles ON battles.battle_id = latest_battles.max_battle_id
                LEFT JOIN mmrpg_users AS users ON battles.target_user_id = users.user_id
                WHERE
                battles.this_user_id = {$this_userid}
                AND battles.this_player_result = 'victory'
                AND users.user_flag_approved = 1
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
        $this_player_points_max = ceil($this_player_points * 10.0);

        // Define the array for pulling all the leaderboard data
        $temp_leaderboard_query = 'SELECT
                mmrpg_leaderboard.user_id,
                mmrpg_leaderboard.board_points,
                mmrpg_users.user_name,
                mmrpg_users.user_name_clean,
                mmrpg_users.user_name_public,
                mmrpg_users.user_colour_token,
                mmrpg_users.user_colour_token2,
                mmrpg_users.user_gender,
                mmrpg_saves.save_values_battle_rewards AS player_rewards,
                mmrpg_saves.save_values_battle_settings AS player_settings,
                mmrpg_saves.save_values_battle_items AS player_items,
                mmrpg_saves.save_values AS player_values,
                mmrpg_saves.save_counters AS player_counters
                FROM mmrpg_leaderboard
                LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
                LEFT JOIN mmrpg_saves ON mmrpg_users.user_id = mmrpg_saves.user_id
                WHERE
                1 = 1
                AND board_points <= '.$this_player_points_max.'
                AND mmrpg_leaderboard.user_id != '.$this_userid.'
                AND mmrpg_users.user_flag_approved = 1
                '.(!empty($temp_exclude_usernames_string) ? 'AND user_name_clean NOT IN ('.$temp_exclude_usernames_string.') ' : '').'
                ORDER BY
                '.(!empty($temp_include_usernames_string) ? ' FIELD(user_name_clean, '.$temp_include_usernames_string.') DESC, ' : '').'
                '.(!empty($temp_exclude_usernames_string) ? ' FIELD(user_name_clean, '.$temp_exclude_usernames_string.') ASC, ' : '').'
                board_points DESC,
                mmrpg_saves.save_date_modified DESC
                LIMIT 12
            ';

        // Query the database and collect the array list of all online players
        $this_leaderboard_target_players = $db->get_array_list($temp_leaderboard_query);

        // Loop through and decode any fields that require it
        if (!empty($this_leaderboard_target_players)){
            foreach ($this_leaderboard_target_players AS $key => $player){

                $player['player_rewards'] = !empty($player['player_rewards']) ? json_decode($player['player_rewards'], true) : array();
                $player['player_settings'] = !empty($player['player_settings']) ? json_decode($player['player_settings'], true) : array();
                $player['player_items'] = !empty($player['player_items']) ? json_decode($player['player_items'], true) : array();
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
                $player['values']['colour_token2'] = !empty($player['user_colour_token2']) ? $player['user_colour_token2'] : '';

                unset($player['values']['battle_shops'], $player['values']['prototype_awards']);
                unset($player['values']['player_this-item-omega_prototype'], $player['values']['dr-light_this-item-omega_prototype'], $player['values']['dr-wily_this-item-omega_prototype'], $player['values']['dr-cossack_this-item-omega_prototype']);
                unset($player['values']['player_target-robot-omega_prototype'], $player['values']['dr-light_target-robot-omega_prototype'], $player['values']['dr-wily_target-robot-omega_prototype'], $player['values']['dr-cossack_target-robot-omega_prototype']);

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
function mmrpg_prototype_get_player_game_counters($player_token, $include = 'all', $session_token = 'GAME'){

    global $db;

    static $game_counters_index = array();

    // Check to see if there's already a cached copy of the game counters for this player
    if (empty($game_counters_index[$player_token])){

        // Collect the robot index as we'll need it later
        $mmrpg_robots_index = rpg_robot::get_index();

        // Define a counter to hold all the game tokens represented
        $temp_robots_parsed = array();
        $temp_game_counters = array();

        // Collect omega factors for this player in case we need 'em
        $temp_session_key = $player_token.'_target-robot-omega_prototype';
        $temp_robot_omega = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();

        // Collect robots currently under this doctor's control
        $temp_player_robots = $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'];
        $temp_player_robot_tokens = !empty($temp_player_robots) ? array_keys($temp_player_robots) : array();
        //error_log(PHP_EOL.'$temp_player_robot_tokens = '.print_r($temp_player_robot_tokens, true));

        // If this player has robots (they better), loop through and collect compatible game tokens
        if (!empty($temp_player_robot_tokens)
            && ($include === 'all' || $include === 'robots')){
            foreach ($temp_player_robot_tokens AS $key => $token){
                if (!isset($mmrpg_robots_index[$token])){ continue; }
                if (in_array($token, $temp_robots_parsed)){ continue; }
                else { $temp_robots_parsed[] = $token; }
                $info = $mmrpg_robots_index[$token];
                $game = strtolower($info['robot_game']);
                if ($game === 'mmpu'){ $game = 'mm1'; }
                if (!isset($temp_game_counters[$game])){ $temp_game_counters[$game] = 0; }
                $temp_game_counters[$game] += 1;
            }
            //error_log('<pre>$temp_game_counters(B) = '.print_r($temp_game_counters, true).'</pre>');
        }
        // Otherwise count robots represented by the current omega fields in the campaign if not already represented
        if (!empty($temp_robot_omega)
            && ($include === 'all' || $include === 'fields')){
            foreach ($temp_robot_omega AS $omega){
                if (empty($omega['robot'])){ continue; }
                else { $token = $omega['robot']; }
                if (!isset($mmrpg_robots_index[$token])){ continue; }
                if (in_array($token, $temp_robots_parsed)){ continue; }
                else { $temp_robots_parsed[] = $token; }
                $info = $mmrpg_robots_index[$token];
                $game = strtolower($info['robot_game']);
                if ($game === 'mmpu'){ $game = 'mm1'; }
                if (!isset($temp_game_counters[$game])){ $temp_game_counters[$game] = 0; }
                $temp_game_counters[$game] += 1;
            }
            //error_log('<pre>$temp_game_counters(B) = '.print_r($temp_game_counters, true).'</pre>');
        }
        //error_log('<pre>$temp_robots_parsed = '.print_r($temp_robots_parsed, true).'</pre>');

        // Define player game prefs for backup purposes
        static $player_base_games = array('dr-light' => 'mm1', 'dr-wily' => 'mm2', 'dr-cossack' => 'mm4' );
        $this_player_game = !empty($player_base_games[$player_token]) ? $player_base_games[$player_token] : 'mm1';

        // If the game counters were somehow empty, populate with default
        if (empty($temp_game_counters)){
            $temp_game_counters[$this_player_game] = 1;
        }

        // Sort the game counters so the highest represented game appears last
        $temp_game_counters = rpg_functions::reverse_sort_array($temp_game_counters, true);
        //error_log("\n".'-------'.$player_token.' (pref:'.$this_player_game.')-------'."\n".'<pre>$temp_game_counters = '.print_r($temp_game_counters, true).'</pre>'."\n");

        // Sort again in case there are duplicate values
        $new_game_keys = array_keys($temp_game_counters);
        usort($new_game_keys, function($a, $b) use($temp_game_counters, $this_player_game){
            $a_val = $temp_game_counters[$a];
            $b_val = $temp_game_counters[$b];
            if ($a_val > $b_val){ return -1; }
            elseif ($a_val < $b_val){ return 1; }
            elseif ($a === $this_player_game){ return -1; }
            elseif ($b === $this_player_game){ return 1; }
            else { return strnatcmp($a, $b); }
            });
        //error_log('<pre>$new_game_keys = '.print_r($new_game_keys, true).'</pre>');

        // And re-build the array given the re-sorted keys
        $new_game_counters = array();
        foreach ($new_game_keys AS $game){ $new_game_counters[$game] = $temp_game_counters[$game]; }
        //error_log('<pre>$new_game_counters = '.print_r($new_game_counters, true).'</pre>');

        // Assign collected game counters to the static index
        $game_counters_index[$player_token] = $new_game_counters;

    }

    // Return the list of games counters for each player
    return $game_counters_index[$player_token];

}

// Define a function for determining a player's battle music
function mmrpg_prototype_get_player_music($player_token, $session_token = 'GAME'){

    global $db;

    // Collect game counters from the other functions
    $temp_game_counters = mmrpg_prototype_get_player_game_counters($player_token, 'fields', $session_token);

    // Get the first element in the array
    reset($temp_game_counters);
    $most_key = key($temp_game_counters);
    $most_count = $temp_game_counters[$most_key];
    //error_log("\n".'<pre>highest key = '.print_r($most_key, true).' w/ count = '.print_r($most_count, true).'</pre>'."\n");

    $most_options = array($most_key);
    foreach ($temp_game_counters AS $key => $count){ if ($key != $most_key && $count >= $most_count){ $most_options[] = $key; } }
    if (count($most_options) > 1){ $most_key = $most_options[array_rand($most_options, 1)];  }
    //error_log("\n".'<pre>$most_options = '.print_r($most_options, true).'</pre>'."\n");
    //error_log("\n".'<pre>$most_key = '.print_r($most_key, true).'; $most_count = '.print_r($most_count, true).'</pre>'."\n");

    return $most_key;

}

// Define a function for determining a player's battle music
function mmrpg_prototype_get_player_mission_music($player_token, $session_token = 'GAME'){
    $game_counters = mmrpg_prototype_get_player_game_counters($player_token, 'fields', $session_token);
    //error_log(PHP_EOL.'---------'.PHP_EOL.'get_player_mission_music('.$player_token.') w/ '.print_r($game_counters, true));
    foreach ($game_counters AS $game => $count){
        $music_path = 'sega-remix/stage-select-'.$game;
        if (rpg_game::sound_exists('sounds/'.$music_path)){
            return $music_path;
        }
    }
    return 'sega-remix/stage-select-mm1';
}

// Define a function for determining the music to play during each player's given chapter of the campaign
function mmrpg_prototype_get_chapter_music($player_token, $data_chapter, $session_token = 'GAME'){
    $chapter_music = 'sega-remix/stage-select-mm1';
    if ($data_chapter === 1){ $chapter_music = 'sega-remix/opening-2-mm9';  }
    elseif ($data_chapter === 2){ $chapter_music = mmrpg_prototype_get_player_mission_music($player_token, $session_token); }
    elseif ($data_chapter === 3){ $chapter_music = 'sega-remix/opening-3-mm9';  }
    elseif ($data_chapter === 4){ $chapter_music = mmrpg_prototype_get_player_mission_music($player_token, $session_token); }
    elseif ($data_chapter === 5){ $chapter_music = 'sega-remix/wily-fortress-4-mm9';  }
    elseif ($data_chapter === 6){ $chapter_music = 'sega-remix/stage-select-mm7';  } // bonus
    elseif ($data_chapter === 8){ $chapter_music = 'sega-remix/stage-select-mm9';  } // star
    elseif ($data_chapter === 7){ $chapter_music = 'sega-remix/bass-mm7-v2';  } // player
    elseif ($data_chapter === 9){ $chapter_music = 'sega-remix/stage-select-mm10';  } // challenge
    if (empty($chapter_music)){ $chapter_music = mmrpg_prototype_get_player_mission_music($player_token, $session_token); }
    if (empty($chapter_music)){ $chapter_music = 'sega-remix/stage-select-mm1'; }
    return $chapter_music;
}

// Define a function for determining a player's boss music
function mmrpg_prototype_get_player_boss_music($player_token, $session_token = 'GAME'){
    $game_counters = mmrpg_prototype_get_player_game_counters($player_token, 'fields', $session_token);
    //error_log(PHP_EOL.'---------'.PHP_EOL.'get_player_boss_music('.$player_token.') w/ '.print_r($game_counters, true));
    foreach ($game_counters AS $game => $count){
        $music_path = 'sega-remix/boss-theme-'.$game;
        if (rpg_game::sound_exists('sounds/'.$music_path)){
            return $music_path;
        }
    }
    return 'sega-remix/boss-theme-mm1';
}

// Define a function for determining a player's boss music
function mmrpg_prototype_get_current_rogue_star(){
    $prototype_campaigns_required = 3;
    $prototype_campaigns_complete = mmrpg_prototype_complete();
    if ($prototype_campaigns_complete < $prototype_campaigns_required){ return false; }
    global $db;
    $this_date_string = date('Y-m-d');
    $this_time_string = date('H:i:s');
    $this_rogue_star = $db->get_array("SELECT
        stars.star_id,
        stars.star_type,
        stars.star_from_date,
        stars.star_from_date_time,
        stars.star_to_date,
        stars.star_to_date_time,
        stars.star_power
        FROM mmrpg_rogue_stars AS stars
        WHERE
        stars.star_type <> ''
        AND stars.star_flag_enabled = 1
        AND stars.star_from_date <= '{$this_date_string}'
            AND stars.star_from_date_time <= '{$this_time_string}'
        AND stars.star_to_date >= '{$this_date_string}'
            AND stars.star_to_date_time >= '{$this_time_string}'
        ORDER BY stars.star_id ASC
        LIMIT 1
        ;");
    return $this_rogue_star;
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
function mmrpg_prototype_get_player_robot_sprites($player_token, $session_token = 'GAME', $robot_limit = 99){

    global $db;
    $mmrpg_index_robots = rpg_robot::get_index(true, false);

    $temp_offset_x = 5;
    $temp_offset_z = 50;
    $temp_offset_y = -2;
    $temp_offset_opacity = 0.75;
    $text_sprites_markup = '';
    $sprites_displayed = 0;

    // Check for any robots that are locked in the endless attack or otherwise
    $player_robots_locked = array();
    $endless_attack_savedata = mmrpg_prototype_get_endless_sessions($player_token);
    //error_log('$endless_attack_savedata for '.$player_token.': '.print_r(array_keys($endless_attack_savedata), true));
    if (!empty($endless_attack_savedata)
        && !empty($endless_attack_savedata['robots'])){
        $endless_robot_robots = $endless_attack_savedata['robots'];
        $player_robots_locked = array_merge($player_robots_locked, $endless_robot_robots);
        $player_robots_locked = array_unique($player_robots_locked);
    }

    // Check to see if any robots have been marked as favourites
    $player_robot_favourites = rpg_game::robot_favourites();
    if (empty($player_robot_favourites)){ $player_robot_favourites = array(); }

    // Collect the player's robot settings and rewards arrays then marge 'em for reference
    $temp_player_robots_settings = $_SESSION[$session_token]['values']['battle_settings'][$player_token]['player_robots'];
    $temp_player_robots_rewards = $_SESSION[$session_token]['values']['battle_rewards'][$player_token]['player_robots'];
    $temp_player_robots_tokens = array_unique(array_merge(array_keys($temp_player_robots_settings), array_keys($temp_player_robots_rewards)));

    //error_log('$temp_player_robots_tokens(before): '.print_r($temp_player_robots_tokens, true));
    //error_log('$player_robot_favourites: '.print_r($player_robot_favourites, true));
    usort($temp_player_robots_tokens, function($a, $b) use($temp_player_robots_tokens, $player_robot_favourites, $player_robots_locked){
        $a_pos = array_search($a, $temp_player_robots_tokens);
        $b_pos = array_search($b, $temp_player_robots_tokens);
        $a_fav = in_array($a, $player_robot_favourites) ? 1 : 0;
        $b_fav = in_array($b, $player_robot_favourites) ? 1 : 0;
        $a_locked = in_array($a, $player_robots_locked) ? 1 : 0;
        $b_locked = in_array($b, $player_robots_locked) ? 1 : 0;
        if ($a_locked < $b_locked){ return -1; }
        elseif ($a_locked > $b_locked){ return 1; }
        elseif ($a_fav < $b_fav){ return 1; }
        elseif ($a_fav > $b_fav){ return -1; }
        elseif ($a_pos < $b_pos){ return -1; }
        elseif ($a_pos > $b_pos){ return 1; }
        else { return 0; }
        });

    //error_log('$temp_player_robots_tokens(after)['.count($temp_player_robots_tokens).']: '.print_r($temp_player_robots_tokens, true));
    foreach ($temp_player_robots_tokens AS $key => $token){
        if (!isset($mmrpg_index_robots[$token])){ continue; }
        //if (in_array($token, $player_robots_locked)){ continue; }
        $info = array();
        if (isset($temp_player_robots_rewards[$token])){ $info = array_merge($info, $temp_player_robots_rewards[$token]); }
        if (isset($temp_player_robots_settings[$token])){ $info = array_merge($info, $temp_player_robots_settings[$token]); }
        //error_log('$info['.$token.']: '.print_r($info, true));
        //error_log('$info['.$token.']: '.print_r(json_encode($info), true));
        $index = rpg_robot::parse_index_info($mmrpg_index_robots[$token]);
        $info = array_merge($index, $info);
        if (mmrpg_prototype_robot_unlocked($player_token, $token)){
            $temp_size = !empty($info['robot_image_size']) ? $info['robot_image_size'] : 40;
            $temp_size_text = $temp_size.'x'.$temp_size;
            $temp_offset_x += 20;
            $temp_offset_y = -2;
            $temp_offset_z -= 1;
            $temp_offset_opacity -= 0.04;
            if ($temp_offset_opacity <= 0){ $temp_offset_opacity = 0; }
            if (in_array($token, $player_robots_locked)){ $temp_offset_brightness = 0; }
            else { $temp_offset_brightness = $temp_offset_opacity; }
            $temp_animation_direction = rpg_robot::get_css_animation_duration($index);
            $text_sprites_markup .= '<span class="sprite sprite_robot sprite_nobanner sprite_40x40 sprite_40x40_00" style="top: '.$temp_offset_y.'px; right: '.$temp_offset_x.'px; z-index: '.$temp_offset_z.'; filter: brightness('.$temp_offset_brightness.');">';
                $text_sprites_markup .= '<span class="sprite sprite_'.$temp_size_text.' sprite_'.$temp_size_text.'_base" style="background-image: url(images/robots/'.(!empty($info['robot_image']) ? $info['robot_image'] : $info['robot_token']).'/sprite_right_'.$temp_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); animation-duration: '.$temp_animation_direction.'s;">'.$info['robot_name'].'</span>';
            $text_sprites_markup .= '</span>';
            $sprites_displayed++;
            if (!empty($robot_limit)
                && $sprites_displayed >= $robot_limit){
                break;
            }
        }
    }

    return $text_sprites_markup;

}


// Define a function for restoring any dropped items to their owners assuming they haven't picked up a new one
function mmrpg_prototype_restore_dropped_items($options = array()){

    // Collect session token for later
    $session_token = rpg_game::session_token();

    // If a battle token was provided, collect it
    $this_battle_token = isset($options['this_battle_token']) ? $options['this_battle_token'] : false;
    $this_preload_keys = array();
    if (!empty($this_battle_token) && !empty($_SESSION['ROBOTS_PRELOAD'][$this_battle_token])){
        foreach ($_SESSION['ROBOTS_PRELOAD'][$this_battle_token] AS $robot_string => $robot_preload){
            list($id, $token) = explode('_', $robot_string);
            $this_preload_keys[$token] = $robot_string;
        }
    }

    // Check to see if there are any dropped items we should re-equip
    if (!empty($_SESSION['ITEMS_DROPPED'])){
        foreach ($_SESSION['ITEMS_DROPPED'] AS $key => $item){
            $ptoken = $item['player'];
            $rtoken = $item['robot'];
            $itoken = $item['item'];
            // Re-equip this item to the robot via battle settings and remove from inventory if we did
            if (!empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken])
                && empty($_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'])){
                $_SESSION[$session_token]['values']['battle_settings'][$ptoken]['player_robots'][$rtoken]['robot_item'] = $itoken;
                if (!empty($_SESSION[$session_token]['values']['battle_items'][$itoken])){
                    $_SESSION[$session_token]['values']['battle_items'][$itoken] -= 1;
                }
            }
            // If there's any preload data, make sure we re-equip the item there too
            if (isset($this_preload_keys[$rtoken])){
                $rstring = $this_preload_keys[$rtoken];
                $_SESSION['ROBOTS_PRELOAD'][$this_battle_token][$rstring]['robot_item'] = $itoken;
            }
            // And finally, we can unset the item from the dropped array so it doesn't persist
            unset($_SESSION['ITEMS_DROPPED'][$key]);
        }
    }

}

// Define a function for collecting a list of allowed avatar options for a given user profile
function mmrpg_prototype_get_profile_avatar_options($this_userinfo, &$allowed_avatar_options = array()){

    // Collect session token for later
    $session_token = rpg_game::session_token();

    // Collect the types index for use later in the script
    $mmrpg_database_types = rpg_type::get_index(true, true, true);

    // Collect a list of robots for use later in the script
    $mmrpg_database_robots = rpg_robot::get_index();

    // Define an array to hold all the allowed avatar options
    $allowed_avatar_options = array();

    // Define an array to hold all the HTML avatar options
    $html_avatar_options = array();
    $html_avatar_options[] = '<option value="">- Select Robot -</option>';

    // Add all the robot avatars to the list
    $last_group_token = false;
    foreach ($mmrpg_database_robots AS $token => $info){

        if ($token == 'robot' || strstr($token, 'copy')){ continue; }
        elseif (isset($info['robot_image']) && $info['robot_image'] == 'robot'){ continue; }
        elseif (isset($info['robot_class']) && $info['robot_class'] == 'mecha'){ continue; }
        elseif (preg_match('/^(DLM)/i', $info['robot_number'])){ continue; }
        elseif (!rpg_game::sprite_exists(MMRPG_CONFIG_ROOTDIR.'images/robots/'.$token.'/')){ continue; }
        if (!mmrpg_prototype_robot_unlocked(false, $token) && $this_userinfo['role_id'] != 1){ continue; }

        // If the game has changed print the new optgroup
        $robot_game_token = $info['robot_game'];
        if (preg_match('/^(mega-man|proto-man|bass|roll|disco|rhythm)$/i', $token)){ $robot_game_token = 'HEROES'; }
        if ($robot_game_token != $last_group_token){
            if (!empty($last_group_token)){ $html_avatar_options[] = '</optgroup>'; }
            $last_group_token = $robot_game_token;
            if ($robot_game_token === 'HEROES'){ $last_group_name = 'Mega Man Heroes'; }
            else { $last_group_name = rpg_game::get_source_name($last_group_token, false).' '.ucfirst(rpg_robot::robot_class_to_noun($info['robot_class'], false, true)); }
            $html_avatar_options[] = '<optgroup label="'.$last_group_name.'">';
        }

        $size = isset($info['robot_image_size']) ? $info['robot_image_size'] : 40;
        $html_avatar_options[] = '<option value="robots/'.$token.'/'.$size.'">'.$info['robot_number'].' : '.$info['robot_name'].'</option>';
        $allowed_avatar_options[] = 'robots/'.$token.'/'.$size;

        // Collect the summon count for this robot and unlocked alts
        $temp_summon_count = mmrpg_prototype_database_summoned($token);
        $temp_alts_unlocked = mmrpg_prototype_altimage_unlocked($token);

        // If this is a copy core, add it's type alts
        if (isset($info['robot_core']) && $info['robot_core'] == 'copy'){
            foreach ($mmrpg_database_types AS $type_token => $type_info){
                if ($type_token == 'none' || $type_token == 'copy' || (isset($type_info['type_class']) && $type_info['type_class'] == 'special')){ continue; }
                if (!isset($_SESSION[$session_token]['values']['battle_items'][$type_token.'-core']) && $this_userinfo['role_id'] != 1){ continue; }
                $html_avatar_options[] = '<option value="robots/'.$token.'_'.$type_token.'/'.$size.'">'.$info['robot_number'].' : '.$info['robot_name'].' ('.$type_info['type_name'].' Core)</option>';
                $allowed_avatar_options[] = 'robots/'.$token.'_'.$type_token.'/'.$size;
            }
        }
        // Otherwise, if this ROBOT MASTER alt skin has been inlocked
        elseif (!empty($info['robot_image_alts'])){
            // Loop through each of the available alts and print if unlocked
            foreach ($info['robot_image_alts'] AS $key => $this_altinfo){
                // Define the unlocked flag as false to start
                $alt_unlocked = false;
                // If this alt is unlocked via summon and we have enough
                if (!empty($this_altinfo['summons']) && $temp_summon_count >= $this_altinfo['summons']){ $alt_unlocked = true; }
                // Else if this alt is unlocked via the shop and has been purchased
                elseif (in_array($this_altinfo['token'], $temp_alts_unlocked)){ $alt_unlocked = true; }
                // Print the alt option markup if unlocked
                if ($alt_unlocked){
                    $html_avatar_options[] = '<option value="robots/'.$token.'_'.$this_altinfo['token'].'/'.$size.'">'.$info['robot_number'].' : '.$this_altinfo['name'].'</option>';
                    $allowed_avatar_options[] = 'robots/'.$token.'_'.$this_altinfo['token'].'/'.$size;
                }
            }
        }

    }
    if (!empty($last_group_token)){ $html_avatar_options[] = '</optgroup>'; }

    // Add player avatars if this is the developer
    if ($this_userinfo['role_id'] == 1 || $this_userinfo['role_id'] == 6){
        $html_avatar_options[] = '</optgroup>';
        $html_avatar_options[] = '<optgroup label="Player Characters">';
        $html_avatar_options[] = '<option value="players/dr-light/40">PLAYER : Dr. Light</option>';
        $html_avatar_options[] = '<option value="players/dr-wily/40">PLAYER : Dr. Wily</option>';
        $html_avatar_options[] = '<option value="players/dr-cossack/40">PLAYER : Dr. Cossack</option>';
        $allowed_avatar_options[] = 'players/dr-light/40';
        $allowed_avatar_options[] = 'players/dr-wily/40';
        $allowed_avatar_options[] = 'players/dr-cossack/40';
    }

    // Add the optgroup closing tag
    $html_avatar_options[] = '</optgroup>';

    // Return the generated options
    return implode(PHP_EOL, $html_avatar_options);

}

// Define a function for collecting a list of allowed colour options for a given user profile
function mmrpg_prototype_get_profile_colour_options($this_userinfo, &$allowed_colour_options = array()){

    // Collect the types index for use later in the script
    $mmrpg_database_types = rpg_type::get_index(true, true, true);

    // Collect the type index and generate colour option html
    $sorted_database_types = $mmrpg_database_types;
    sort($sorted_database_types);
    $allowed_colour_options = array();
    $html_colour_options = array();
    $html_colour_options[] = '<option value="">- Select Type -</option>';
    $html_colour_options[] = '<option value="none">Neutral Type</option>';

    // Add all the robot avatars to the list
    foreach ($sorted_database_types AS $token => $info){
        if ($token == 'none'){ continue; }
        $html_colour_options[] = '<option value="'.$info['type_token'].'">'.$info['type_name'].' Type</option>';
        $allowed_colour_options[] = $info['type_token'];
    }

    // Add player avatars if this is the developer
    if ($this_userinfo['role_id'] == 1){
        $html_colour_options[] = '<option value="energy">Energy Type</option>';
        $html_colour_options[] = '<option value="attack">Attack Type</option>';
        $html_colour_options[] = '<option value="defense">Defense Type</option>';
        $html_colour_options[] = '<option value="speed">Speed Type</option>';
        $allowed_colour_options[] = 'energy';
        $allowed_colour_options[] = 'attack';
        $allowed_colour_options[] = 'defense';
        $allowed_colour_options[] = 'speed';
    }

    // Return the generated options
    return implode(PHP_EOL, $html_colour_options);

}

// Define a function for collecting a list of allowed background options for a given user profile
function mmrpg_prototype_get_profile_background_options($this_userinfo, &$allowed_background_options = array()){

    // Collect session token for later
    $session_token = rpg_game::session_token();

    // Collect a list of robots for use later in the script
    $mmrpg_database_robots = rpg_robot::get_index();

    // Collect a list of fields for use later in the script
    $this_fields_index = rpg_field::get_index();

    // Collect player background omega options
    require(MMRPG_CONFIG_ROOTDIR.'prototype/omega.php');
    $temp_omega_factor_options = array();
    $temp_omega_factor_options['MM1'] = $this_omega_factors_one;
    $temp_omega_factor_options['MM2'] = $this_omega_factors_two;
    $temp_omega_factor_options['MM4'] = $this_omega_factors_three;
    $temp_omega_factor_options['MM3'] = $this_omega_factors_four;

    // Loop through and remove any fields who's robots haven't been encountered yet
    $session_robot_database = !empty($_SESSION[$session_token]['values']['robot_database']) ? $_SESSION[$session_token]['values']['robot_database'] : array();
    foreach($temp_omega_factor_options AS $game => $options){
        foreach($options AS $key => $option){
            $rtoken = $option['robot'];
            if (!isset($session_robot_database[$rtoken])
                || empty($session_robot_database[$rtoken]['robot_unlocked'])){
                unset($temp_omega_factor_options[$game][$key]);
            }
        }
    }
    ksort($temp_omega_factor_options);

    // Generate allowed background options and their select markup
    $temp_optgroup_token = 'MMRPG';
    $allowed_background_options = array();
    $html_background_options = array();
    $html_background_options[] = '<option value="">- Select Field -</option>';
    $html_background_options[] = '<optgroup label="Intro Fields">';
    $html_background_options[] = '<option value="fields/gentle-countryside">Gentle Countryside (Neutral Type)</option>';
    $html_background_options[] = '<option value="fields/maniacal-hideaway">Maniacal Hideaway (Neutral Type)</option>';
    $html_background_options[] = '<option value="fields/wintry-forefront">Wintry Forefront (Neutral Type)</option>';
    if (mmrpg_prototype_player_unlocked('dr-light')){ $allowed_background_options[] = 'fields/gentle-countryside'; }
    if (mmrpg_prototype_player_unlocked('dr-wily')){ $allowed_background_options[] = 'fields/maniacal-hideaway'; }
    if (mmrpg_prototype_player_unlocked('dr-cossack')){ $allowed_background_options[] = 'fields/wintry-forefront'; }

    // Add all the robot avatars to the list
    //die('<pre>'.print_r($temp_omega_factor_options, true).'</pre>');
    foreach ($temp_omega_factor_options AS $omega_game => $omega_array){
        // If the game has changed print the new optgroup
        if ($omega_game != $temp_optgroup_token){
            $temp_optgroup_token = $omega_game;
            if (preg_match('/^MM([0-9]+)$/', $temp_optgroup_token)){ $temp_optgroup_name = 'Mega Man '.ltrim(str_replace('MM', '', $temp_optgroup_token), '01').' Fields'; }
            else { $temp_optgroup_name = 'Mega Man '.str_replace('MM', '', $temp_optgroup_token).' Fields'; }
            $html_background_options[] = '</optgroup>';
            $html_background_options[] = '<optgroup label="'.$temp_optgroup_name.'">';
        }
        foreach ($omega_array AS $omega_key => $omega_info){
            if (empty($this_fields_index[$omega_info['field']])){ continue; }
            $robot_info = $mmrpg_database_robots[$omega_info['robot']];
            $field_info = $this_fields_index[$omega_info['field']];
            $html_background_options[] = '<option value="fields/'.$field_info['field_token'].'">'.
                $field_info['field_name'].
                (!empty($field_info['field_type']) ? ' ('.ucfirst($field_info['field_type']).' Type)' : '').
                '</option>';
            $allowed_background_options[] = 'fields/'.$field_info['field_token'];
        }
    }
    $html_background_options[] = '</optgroup>';

    // Return the generated options
    return implode(PHP_EOL, $html_background_options);

}

// Define a function for formatting user data into session-format for later reference
function mmrpg_prototype_format_user_data_for_session($this_userinfo){
    $session_user = array();
    $session_user['userid'] = $this_userinfo['user_id'];
    $session_user['roleid'] = $this_userinfo['role_id'];
    $session_user['username'] = $this_userinfo['user_name'];
    $session_user['username_clean'] = $this_userinfo['user_name_clean'];
    $session_user['password'] = '';
    $session_user['password_encoded'] = '';
    $session_user['omega'] = $this_userinfo['user_omega'];
    $session_user['profiletext'] = $this_userinfo['user_profile_text'];
    $session_user['creditstext'] = $this_userinfo['user_credit_text'];
    $session_user['creditsline'] = $this_userinfo['user_credit_line'];
    $session_user['imagepath'] = $this_userinfo['user_image_path'];
    $session_user['backgroundpath'] = $this_userinfo['user_background_path'];
    $session_user['colourtoken'] = $this_userinfo['user_colour_token'];
    $session_user['colourtoken2'] = $this_userinfo['user_colour_token2'];
    $session_user['gender'] = $this_userinfo['user_gender'];
    $session_user['displayname'] = $this_userinfo['user_name_public'];
    $session_user['emailaddress'] = $this_userinfo['user_email_address'];
    $session_user['websiteaddress'] = $this_userinfo['user_website_address'];
    $session_user['dateofbirth'] = $this_userinfo['user_date_birth'];
    $session_user['approved'] = $this_userinfo['user_flag_approved'];
    $session_user['userinfo'] = $this_userinfo;
    unset($session_user['userinfo']['user_password']);
    unset($session_user['userinfo']['user_password_encoded']);
    return $session_user;
}

// Define a function for generating type spans given a list of object tokens
function mmrpg_generate_type_spans_from_tokens($kind, $tokens, $tooltips = array(), $index_by_token = false){
    $span_list = array();
    if (!is_array($tooltips)){ $tooltips = array(); }
    if ($kind === 'robots'){
        $mmrpg_robots_index = rpg_robot::get_index();
        foreach ($tokens AS $key => $token){
            if (!empty($mmrpg_robots_index[$token])){
                $info = $mmrpg_robots_index[$token];
                $name = $info['robot_name'];
                $types = !empty($info['robot_core']) ? $info['robot_core'] : '';
                if (!empty($types) && !empty($info['robot_core2'])){ $types .= '_'.$info['robot_core2']; }
                if (empty($types)){ $types = 'none'; }
                $title = (isset($tooltips[$token]) ? ' title="'.$tooltips[$token].'"' : $info['robot_name']);
                $markup = '';
                if (isset($prefixes[$token])){ $markup .= '<span class="prefix">'.$prefixes[$token].'</span> '; }
                $markup .= '<span class="robot_name type_span type type_'.$types.'"'.$title.'>'.$name.'</span>';
                if ($index_by_token){ $span_list[$token] = $markup; }
                else { $span_list[] = $markup; }
            }
        }
    }
    return $span_list;
}


// Define a function for printing out global records at the bottom of a robot/mecha/boss database page
function mmrpg_get_robot_database_records($record_filters = array(), &$record_categories_index = array()){
    global $db;

    // Ensure the record filters var was an array and then collect
    if (empty($record_filters) || !is_array($record_filters)){ extract($record_filters); }
    $record_filters['robot_class'] = !empty($record_filters['robot_class']) ? $record_filters['robot_class'] : false;
    $record_filters['robot_core'] = !empty($record_filters['robot_core']) ? $record_filters['robot_core'] : false;
    $record_filters['robot_token'] = !empty($record_filters['robot_token']) ? $record_filters['robot_token'] : false;
    $record_filters['record_limit'] = !empty($record_filters['record_limit']) ? $record_filters['record_limit'] : false;
    $record_filters['record_labels'] = !empty($record_filters['record_labels']) ? $record_filters['record_labels'] : false;

    // Define an array to hold the database records in
    $global_robot_records = array();

    // Define the record categories we'll be pulling data for (filter for class if defined)
    $record_categories_index = array(
        'robot_encountered' => array('label' => 'encountered', 'noun' => 'encounter', 'xnoun' => 'encounters', 'counter' => 'time', 'xcounter' => 'times'),
        'robot_scanned' => array('label' => 'scanned', 'noun' => 'scan', 'xnoun' => 'scans', 'counter' => 'time', 'xcounter' => 'times'),
        'robot_defeated' => array('label' => 'defeated', 'noun' => 'defeat', 'xnoun' => 'defeats', 'counter' => 'time', 'xcounter' => 'times'),
        'robot_summoned' => array('label' => 'summoned', 'noun' => 'summon', 'xnoun' => 'summons', 'counter' => 'time', 'xcounter' => 'times'),
        'robot_unlocked' => array('label' => 'unlocked', 'zlabel' => 'unlocked by', 'noun' => 'unlock', 'xnoun' => 'unlocks', 'counter' => 'player', 'xcounter' => 'players'),
        'robot_avatars' => array('label' => 'avatars', 'zlabel' => 'avatar by', 'noun' => 'avatar', 'xnoun' => 'avatars', 'counter' => 'player', 'xcounter' => 'players')
        );
    $record_categories = array_keys($record_categories_index); //array('robot_encountered', 'robot_scanned', 'robot_defeated', 'robot_summoned', 'robot_unlocked', 'robot_avatars');
    if (!empty($record_filters['robot_class']) && $record_filters['robot_class'] !== 'master'){
        unset($record_categories[array_search('robot_unlocked', $record_categories)]);
        unset($record_categories[array_search('robot_avatars', $record_categories)]);
    }
    if (!empty($record_filters['robot_class']) && $record_filters['robot_class'] === 'boss'){
        unset($record_categories[array_search('robot_summoned', $record_categories)]);
    }
    $record_categories = array_values($record_categories);

    // Define a function for generating a label for a given category
    $record_categories_label = function($record_category, $kind) use($record_categories_index){
        return ucwords($record_categories_index[$record_category][$kind]);
        };

    // Define common query conditions given provided filters
    $record_query_conditions = '';
    if (!empty($record_filters['robot_class'])){ $record_query_conditions .= "AND robots.robot_class = '{$record_filters['robot_class']}' "; }
    if (!empty($record_filters['robot_token'])){ $record_query_conditions .= "AND robots.robot_token = '{$record_filters['robot_token']}' "; }
    if (!empty($record_filters['robot_core'])){
        if ($record_filters['robot_core'] === 'none'){ $record_query_conditions .= "AND robots.robot_core = '' "; }
        else { $record_query_conditions .= "AND (robots.robot_core = '{$record_filters['robot_core']}' OR robots.robot_core2 = '{$record_filters['robot_core']}') "; }
    }

    // Define the common limit string if one has been provided
    $record_limit_string = !empty($record_filters['record_limit']) ? "LIMIT {$record_filters['record_limit']}" : '';

    // Collect global records for the robot masters
    foreach ($record_categories AS $record_category){

        // Avatar records have to be collected in a special way
        if ($record_category === 'robot_avatars'){

            $allowed_robot_tokens = $db->get_array_list("
                SELECT robots.robot_token
                FROM mmrpg_index_robots AS robots
                WHERE
                robots.robot_flag_published = 1
                AND robots.robot_flag_complete = 1
                {$record_query_conditions}
                ;", 'robot_token');
            $allowed_robot_tokens = !empty($allowed_robot_tokens) ? array_keys($allowed_robot_tokens) : array();
            if (empty($allowed_robot_tokens)){ continue; }

            $user_image_paths = $db->get_array_list("SELECT
                user_image_path,
                COUNT(*) AS robot_avatars_total
                FROM mmrpg_users AS users
                WHERE user_image_path <> ''
                GROUP BY user_image_path
                ORDER BY robot_avatars_total DESC
                ;");
            if (empty($user_image_paths)){ continue; }

            $record_array = array();
            if (!empty($user_image_paths)){
                $regex = '/^(?:[^\/]+)\/([-a-z0-9]+)(?:[^\/]+)?\/(?:[0-9]+)$/i';
                foreach ($user_image_paths AS $key => $data){
                    $robot_token = preg_replace($regex, '$1', $data['user_image_path']);
                    if (!in_array($robot_token, $allowed_robot_tokens)){ continue; }
                    $avatar_count = $data['robot_avatars_total'];
                    if (!isset($record_array[$robot_token])){ $record_array[$robot_token] = 0; }
                    $record_array[$robot_token] += $avatar_count;
                }
                asort($record_array);
                $record_array = array_reverse($record_array);
            }
            $record_array = array_filter($record_array);
            if (empty($record_array)){ continue; }
            if (!empty($record_filters['record_limit'])){ $record_array = array_slice($record_array, 0, $record_filters['record_limit']); }

        }
        // Otherwise, all other record types can be pulled normally
        else {

            $record_array = $db->get_array_list("SELECT
                records.robot_token,
                SUM(records.{$record_category}) AS {$record_category}_total
                FROM mmrpg_users_robots_records AS records
                LEFT JOIN mmrpg_index_robots AS robots ON robots.robot_token = records.robot_token
                WHERE
                robots.robot_flag_published = 1
                AND robots.robot_flag_complete = 1
                {$record_query_conditions}
                GROUP BY
                records.robot_token
                ORDER BY
                {$record_category}_total DESC
                {$record_limit_string}
                ;", 'robot_token');
            if (empty($record_array)){ continue; }
            $record_array = array_filter(array_map(function($a) use($record_category){
                return isset($a[$record_category.'_total']) ? $a[$record_category.'_total'] : 0;
                }, $record_array));

        }

        // Format the totals with proper number formatting
        $record_array = array_map(function($value){
            return number_format($value, 0, '.', ',');
            }, $record_array);

        // If allowed, append each of the records with an appropriate label
        if (!empty($record_filters['record_labels'])){
            $label_kind = is_string($record_filters['record_labels']) ? $record_filters['record_labels'] : 'noun';
            $record_array = array_map(function($value) use($record_categories_label, $record_category, $label_kind){
                return $value.' '.ucwords($record_categories_label($record_category, (number_is_plural($value) ? 'x' : '').$label_kind));
                }, $record_array);
        }

        // Add the final record array to the global list
        $global_robot_records[$record_category] = $record_array;

    }

    // If a specific robot token was defined, let's simplify the data structure
    if (!empty($record_filters['robot_token'])){
        $single_robot_records = array();
        if (!empty($global_robot_records)){
            foreach ($global_robot_records AS $record_category => $record_array){
                if (!isset($record_array[$record_filters['robot_token']])){ continue; }
                $single_robot_records[$record_category] = $record_array[$record_filters['robot_token']];
            }
        }
        $global_robot_records = $single_robot_records;
    }

    // Return collected records if they're not empty
    return $global_robot_records;

}


// Define a function for printing out global records at the bottom of a robot/mecha/boss database page
function mmrpg_get_robot_database_records_markup($robot_class, $record_limit = 10){
    global $db;
    global $this_current_filter, $this_current_filter_name;

    // Define the section title given the robot class provided
    $section_title = 'Robot Records';
    if ($robot_class === 'mecha'){ $section_title = 'Mecha Records'; }
    elseif ($robot_class === 'boss'){ $section_title = 'Boss Records'; }

    // Collect global database records from the other function given filters
    $global_robot_records = mmrpg_get_robot_database_records(array(
        'robot_class' => $robot_class,
        'robot_core' => $this_current_filter,
        'record_limit' => $record_limit,
        'record_labels' => 'counter'
        ), $record_categories_index);

    ob_start();
    ?>
    <h2 class="subheader field_type_<?= !empty($this_current_filter) ? $this_current_filter : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>" style="margin-top: 10px;">
        <span class="subheader_typewrapper">
            <?= $section_title ?>
            <?= !empty($this_current_filter) ? '<span class="count" style="float: right;">( '.$this_current_filter_name.' Core )</span>' : '' ?>
        </span>
    </h2>
    <div class="subbody subbody_database_records">
        <div class="global_records robots">
            <div class="wrap">
                <ul class="categories">
                    <?
                    // Loop through and print out the records with names and labels
                    foreach ($global_robot_records AS $record_category => $record_robot_list){
                        if (empty($record_robot_list)){ continue; }
                        $label = 'Most '.ucwords($record_categories_index[$record_category]['label']);
                        $robot_tokens = array_keys($record_robot_list);
                        $robot_span_list = mmrpg_generate_type_spans_from_tokens('robots', $robot_tokens, false, true);
                        $rank = 0; $rank_value = '';
                        foreach ($robot_span_list AS $token => $markup){
                            $value = $record_robot_list[$token];
                            if ($value !== $rank_value){ $rank += 1; $rank_value = $value; }
                            $rank_span = '<span class="rank_span">'.mmrpg_number_suffix($rank, true, true).'</span>';
                            $value_span = '<span class="value_span">'.$value.'</span>';
                            $robot_span_list[$token] = $rank_span.' '.$markup.' '.$value_span;
                        }
                        echo('<li class="category">'.PHP_EOL);
                            echo('<strong>'.$label.'</strong>'.PHP_EOL);
                            echo('<ul><li>'.implode('</li><li>', $robot_span_list).'</li></ul>'.PHP_EOL);
                        echo('</li>'.PHP_EOL);
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <?
    $records_markup = ob_get_clean();
    return $records_markup;

}


// Define a function for collecting any ENDLESS ATTACK MODE sessions from the database for the loaded player
function mmrpg_prototype_get_endless_sessions($player_token = '', $force_refresh = false){
    //error_log('mmrpg_prototype_get_endless_sessions($player_token = '.$player_token.', $force_refresh = '.$force_refresh.')');

    // Collect the currently logged in userid else return false
    if (!rpg_game::is_user()){ return false; }
    $this_userid = rpg_game::get_userid();

    // If the variable has already been set, then just return it as-is
    if (!$force_refresh
        && isset($_SESSION['PROTOTYPE_TEMP']['ENDLESS_MODE_SAVEDATA'])){
        // Return any generated ENDLESS ATTACK MODE savedata
        $endless_mode_savedata = $_SESSION['PROTOTYPE_TEMP']['ENDLESS_MODE_SAVEDATA'];
        //error_log('(A) requested player_token = '.$player_token.' | endless_mode_savedata = '.print_r(array_keys($endless_mode_savedata), true));
        if (!empty($player_token)){ $endless_mode_savedata = isset($endless_mode_savedata[$player_token]) ? $endless_mode_savedata[$player_token] : array(); }
        //error_log('(B) requested player_token = '.$player_token.' | endless_mode_savedata = '.print_r(array_keys($endless_mode_savedata), true));
        return $endless_mode_savedata;
    }

    // Ensure the variable to hold ENDLESS ATTACK MODE savedata exists across all doctors
    $_SESSION['PROTOTYPE_TEMP']['ENDLESS_MODE_SAVEDATA'] = array();

    // Check if we're allowed and there is an ENDLESS ATTACK MODE savestate in the waveboard to load now
    if (mmrpg_prototype_item_unlocked('wily-program')){
        global $db;
        global $flag_wap;
        $challenge_mode_savestate = $db->get_value("SELECT
            `challenge_wave_savestate`
            FROM `mmrpg_challenges_waveboard`
            WHERE `user_id` = {$this_userid}
            AND `challenge_wave_savestate` IS NOT NULL
            AND `challenge_wave_savestate` <> ''
            ;", 'challenge_wave_savestate');
        if (!empty($challenge_mode_savestate)){
            //echo('<pre>$challenge_mode_savestate = '.print_r($challenge_mode_savestate, true).'</pre>'.PHP_EOL.PHP_EOL);
            $challenge_mode_savestate = json_decode($challenge_mode_savestate, true);
            //echo('<pre>$challenge_mode_savestate = '.print_r($challenge_mode_savestate, true).'</pre>'.PHP_EOL.PHP_EOL);
            if (!empty($challenge_mode_savestate['BATTLES_CHAIN'])
                && !empty($challenge_mode_savestate['ROBOTS_PRELOAD'])
                && !empty($challenge_mode_savestate['NEXT_MISSION'])){
                // Load the saved battle chain and robot preload data into session
                $_SESSION['BATTLES_CHAIN'] = $challenge_mode_savestate['BATTLES_CHAIN'];
                $_SESSION['ROBOTS_PRELOAD'] = $challenge_mode_savestate['ROBOTS_PRELOAD'];
                // Generate the URL for the next mission with saved data and redirect
                $next_mission_data = $challenge_mode_savestate['NEXT_MISSION'];
                $next_mission_href = 'battle.php?wap='.($flag_wap ? 'true' : 'false');
                $next_mission_href .= '&this_battle_id='.$next_mission_data['this_battle_id'];
                $next_mission_href .= '&this_battle_token='.$next_mission_data['this_battle_token'];
                $next_mission_href .= '&this_player_id='.$next_mission_data['this_player_id'];
                $next_mission_href .= '&this_player_token='.$next_mission_data['this_player_token'];
                $next_mission_href .= '&this_player_robots='.$next_mission_data['this_player_robots'];
                $next_mission_href .= '&flag_skip_fadein=true';
                //echo('<pre>$next_mission_href = '.print_r($next_mission_href, true).'</pre>'.PHP_EOL.PHP_EOL);
                // Generate the first ENDLESS ATTACK MODE mission and append it to the list
                $next_mission_player = $next_mission_data['this_player_token'];
                $next_mission_robots = $next_mission_data['this_player_robots'];
                $next_mission_robots = !empty($next_mission_robots) ? array_map(function($s){ list($i, $r) = explode('_', $s); return $r; }, explode(',', $next_mission_robots)) : array();
                $next_mission_number = count($_SESSION['BATTLES_CHAIN']) + 1;
                $this_prototype_data = array();
                $this_prototype_data['this_player_token'] = $next_mission_player;
                $this_prototype_data['this_current_chapter'] = '8';
                $this_prototype_data['battle_phase'] = 4;
                $temp_battle_sigma = rpg_mission_endless::generate_endless_mission($this_prototype_data, $next_mission_number);
                rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);
                //echo('<pre>$temp_battle_sigma = '.print_r($temp_battle_sigma, true).'</pre>'.PHP_EOL.PHP_EOL);
                // Redirect to the mission URL now that everything is loaded and set up
                //header('Location: '.$next_mission_href);
                //exit();

                //error_log('Collecting ENDLESS ATTACK MODE next mission href: '.$next_mission_href);
                //error_log('$next_mission_player = '.print_r($next_mission_player, true));
                //error_log('$next_mission_robots = '.print_r($next_mission_robots, true));
                //error_log('BATTLES_CHAIN = '.print_r($_SESSION['BATTLES_CHAIN'], true));
                //error_log('ROBOTS_PRELOAD = '.print_r($_SESSION['ROBOTS_PRELOAD'], true));
                // Check to see which robots are preoccupied with this mission
                /*
                $temp_preload_robots_key = $next_mission_player.'-endless-mission';
                $temp_preload_robots = isset($_SESSION['ROBOTS_PRELOAD'][$temp_preload_robots_key]) ? $_SESSION['ROBOTS_PRELOAD'][$temp_preload_robots_key] : array();
                $temp_locked_robots = array_map(function($s){ list($i, $r) = explode('_', $s); return $r; }, array_keys($temp_preload_robots));
                //error_log('$temp_preload_robots_key = '.print_r($temp_preload_robots_key, true));
                //error_log('$temp_preload_robots = '.print_r($temp_preload_robots, true));
                //error_log('$temp_locked_robots = '.print_r($temp_locked_robots, true));
                */
                // Store the next mission URL in the session for later
                $_SESSION['PROTOTYPE_TEMP']['ENDLESS_MODE_SAVEDATA'][$next_mission_player] = array(
                    'redirect' => $next_mission_href,
                    'battle' => $temp_battle_sigma,
                    'player' => $next_mission_player,
                    'robots' => $next_mission_robots
                    );

            }
        }
    }

    // Return any generated ENDLESS ATTACK MODE savedata
    $endless_mode_savedata = $_SESSION['PROTOTYPE_TEMP']['ENDLESS_MODE_SAVEDATA'];
    //error_log('(C) requested player_token = '.$player_token.' | endless_mode_savedata = '.print_r(array_keys($endless_mode_savedata), true));
    if (!empty($player_token)){ $endless_mode_savedata = isset($endless_mode_savedata[$player_token]) ? $endless_mode_savedata[$player_token] : array(); }
    //error_log('(D) requested player_token = '.$player_token.' | endless_mode_savedata = '.print_r(array_keys($endless_mode_savedata), true));
    return $endless_mode_savedata;

}



?>
