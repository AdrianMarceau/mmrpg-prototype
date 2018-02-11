<?

/*
 * PLAYER MISSION SELECT
 * Only re-generate missions if it is approriate to do
 * so at this time (the player is requesting missions)
 */

// Only generate out mission markup data if conditions allow or do not exist
if (!defined('MMRPG_SCRIPT_REQUEST') ||
    ($this_data_select == 'this_battle_token' && in_array('this_player_token='.$this_prototype_data['this_player_token'], $this_data_condition))){

    // -- STARTER BATTLE : CHAPTER ONE -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '0';

    // If the player has completed at least zero battles, display the starter battle
    if ($this_prototype_data['prototype_complete']
        || !empty($this_prototype_data['this_chapter_unlocked']['0a'])
        || !empty($this_prototype_data['this_chapter_unlocked']['0b'])
        || !empty($this_prototype_data['this_chapter_unlocked']['0c'])){

        // EVENT MESSAGE : CHAPTER ONE
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Chapter One : An Unexpected Attack'
            );

        // Intro Battle vs Met
        // Always add this battle first, no matter what
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0a'])){
            $temp_battle_option = array('battle_phase' => $this_prototype_data['battle_phase'], 'battle_token' => $this_prototype_data['this_player_token'].'-intro-i', 'battle_level' => $this_prototype_data['this_chapter_levels'][0] + 0);
            $temp_battle_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $this_prototype_data['battle_options'][] = $temp_battle_option;
        }

        // Intro Battle vs Joe
        // Only add this battle if the player has defeated the first one
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0b'])){
            $temp_battle_option = array('battle_phase' => $this_prototype_data['battle_phase'], 'battle_token' => $this_prototype_data['this_player_token'].'-intro-ii', 'battle_level' => $this_prototype_data['this_chapter_levels'][0] + 1);
            $temp_battle_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $this_prototype_data['battle_options'][] = $temp_battle_option;
        }

        // Intro Battle vs Trill
        // Only add this battle if the player has defeated the first and second one
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0c'])){
            $temp_battle_option = array('battle_phase' => $this_prototype_data['battle_phase'], 'battle_token' => $this_prototype_data['this_player_token'].'-intro-iii', 'battle_level' => $this_prototype_data['this_chapter_levels'][0] + 2);
            $temp_battle_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $this_prototype_data['battle_options'][] = $temp_battle_option;
        }


    }



    // -- ROBOT MASTER BATTLES : CHAPTER TWO -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '1';

    // Only continue if the player has defeated first 1 battle
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['1'])){

        // EVENT MESSAGE : CHAPTER TWO
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Chapter Two : Robot Master Revival'
            );

        // Increment the phase counter
        $this_prototype_data['battle_phase'] += 1;
        $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
        $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];

        // Populate the battle options with the initial eight robots
        if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
        //die('<pre>'.print_r($this_prototype_data['target_robot_omega'], true).'</pre>');
        foreach ($this_prototype_data['target_robot_omega'] AS $key => $info){

            // Generate the battle option with the starter data
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'_'.$key;
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
                $temp_battle_omega = rpg_mission_single::generate($this_prototype_data, $info['robot'], $info['field'], $this_prototype_data['this_chapter_levels'][1]);
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
            } else {
                $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
                $temp_battle_omega = rpg_battle::get_index_info($temp_battle_token);
            }

            // Add the omega battle to the options, index, and session
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

        }

    }


    // -- NEW CHALLENGER BATTLE : CHAPTER THREE -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '2';

    // If the first 1 + 8 battles are complete, unlock the ninth and recollect markup
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['2'])){

        // EVENT MESSAGE : CHAPTER THREE
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Chapter Three : The Rival Challengers'
            );

        // Unlock the first fortress battle
        $temp_battle_token = $this_prototype_data['this_player_token'].'-fortress-i';
        $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_token);
        $temp_battle_omega = array('battle_phase' => $this_prototype_data['battle_phase'], 'battle_token' => $temp_battle_token, 'battle_level' => $this_prototype_data['this_chapter_levels'][2]);
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

        // If the battle is complete, remove the player from the description
        if ($temp_battle_complete){
            $temp_index_battle = rpg_battle::get_index_info($temp_battle_token);
            $temp_battle_omega['battle_target_player'] = $temp_index_battle['battle_target_player'];
            $temp_battle_omega['battle_target_player']['player_token'] = 'player';
            $temp_battle_omega['battle_description'] = $temp_index_battle['battle_description'];
            $temp_battle_omega['battle_description'] = preg_replace('/^Defeat (Dr. (Wily|Light|Cossack)\'s)/i', 'Defeat', $temp_battle_omega['battle_description']);
        }

        // Add the omega battle to the battle options
        $this_prototype_data['battle_options'][] = $temp_battle_omega;

    }


    // -- FUSION FIELD BATTLES : CHAPTER FOUR -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '3';

    // Only continue if the player has defeated the first 1 + 8 + 1 battles
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['3'])){

        // EVENT MESSAGE : CHAPTER FOUR
        $this_prototype_data['battle_options'][] = array(
        'option_type' => 'message',
        'option_chapter' => $this_prototype_data['this_current_chapter'],
        'option_maintext' => 'Chapter Four : Battle Field Fusions'
        );

        // Increment the phase counter
        $this_prototype_data['battle_phase'] += 1;
        $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
        $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];

        // Populate the battle options with the initial eight robots combined
        if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
        foreach ($this_prototype_data['target_robot_omega'] AS $key => $info){
            // Generate the second info option and skip if already used
            if ($key > 0 && ($key + 1) % 2 == 0){ continue; }

            // Generate the battle option with the starter data
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'_'.$key;
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
                $info2 = $this_prototype_data['target_robot_omega'][$key + 1];
                $temp_battle_omega = rpg_mission_double::generate($this_prototype_data, array($info['robot'], $info2['robot']), array($info['field'], $info2['field']), $this_prototype_data['this_chapter_levels'][3], true, true);
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
            } else {
                $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
                $temp_battle_omega = rpg_battle::get_index_info($temp_battle_token);
            }

            // Add the omega battle to the options, index, and session
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

        }

    }


    // -- THE FINAL BATTLES : CHAPTER FIVE -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '4';

    // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
    if ($this_prototype_data['prototype_complete']
        || !empty($this_prototype_data['this_chapter_unlocked']['4a'])
        || !empty($this_prototype_data['this_chapter_unlocked']['4b'])
        || !empty($this_prototype_data['this_chapter_unlocked']['4c'])){

        // EVENT MESSAGE : CHAPTER FOUR
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Chapter Five : The Final Battles'
            );

        // Final Destination I
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4a'])){

            // Unlock the first of the final destination battles
            $temp_final_option = array('battle_phase' => $this_prototype_data['battle_phase'], 'battle_token' => $this_prototype_data['this_player_token'].'-fortress-ii', 'battle_level' => $this_prototype_data['this_chapter_levels'][4]);
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $this_prototype_data['battle_options'][] = $temp_final_option;

        }

        // Final Destination II
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4b'])){

            // Unlock the first of the final destination battles
            $temp_final_option = array('battle_phase' => $this_prototype_data['battle_phase'], 'battle_token' => $this_prototype_data['this_player_token'].'-fortress-iii', 'battle_level' => $this_prototype_data['this_chapter_levels'][4]);
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $this_prototype_data['battle_options'][] = $temp_final_option;

        }

        // Final Destination III
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4c'])){

            // Collect the robot index for quick use
            $temp_robots_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
            $temp_fields_index = rpg_field::get_index();

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iv';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token);
            if (empty($temp_final_option)){ die('$temp_final_option empty on line '.__LINE__.'<pre>'.print_r($this_prototype_data['this_player_token'].'-fortress-iv', true).'</pre>'); }
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            //$temp_final_option['battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['battle_phase'].'-'.$temp_final_option['battle_token'];
            $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][6];
            //$temp_final_abilities = array('attack-boost', 'attack-break', 'defense-boost', 'defense-break', 'speed-boost', 'speed-break', 'energy-boost', 'energy-break');

            // Collect and define the robot masters and support mechas to appear on this field
            $temp_robot_masters = array();
            $temp_support_mechas = array();
            if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
            foreach ($this_prototype_data['target_robot_omega'] AS $key => $info){
                $temp_field_info = rpg_field::parse_index_info($temp_fields_index[$info['field']]);
                if (!empty($temp_field_info['field_master'])){ $temp_robot_masters[] = $temp_field_info['field_master']; }
                if (!empty($temp_field_info['field_mechas'])){ $temp_support_mechas[] = array_pop($temp_field_info['field_mechas']); }
            }

            // Define randomized hold item options based on player
            $item_tier = 0;
            $item_options = array();
            if ($this_prototype_data['this_player_token'] == 'dr-light'){ $item_tier = 1; }
            elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){ $item_tier = 2; }
            elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){ $item_tier = 3; }
            if ($item_tier >= 1){
                $item_options = array_merge($item_options,
                    array('energy-capsule', 'weapon-capsule', 'attack-capsule', 'defense-capsule', 'speed-capsule')
                    );
            }
            if ($item_tier >= 2){
                $item_options = array_merge($item_options,
                    array('energy-tank', 'weapon-tank', 'attack-booster', 'defense-booster', 'speed-booster')
                    );
            }
            if ($item_tier >= 3){
                $item_options = array_merge($item_options,
                    array('energy-upgrade', 'weapon-upgrade', 'yashichi', 'super-capsule')
                    );
            }
            $item_max_key = count($item_options) - 1;

            // Add the masters info into the omega battle
            //foreach ($temp_final_option['battle_target_player']['player_robots'] AS $key => $info){
            $temp_final_option['battle_target_player']['player_robots'] = array();
            foreach ($temp_robot_masters AS $key => $token){
                //if ($info['robot_level'] > $temp_final_option['battle_level']){ $temp_final_option['battle_level'] = $info['robot_level']; }
                $index = rpg_robot::parse_index_info($temp_robots_index[$token]);
                $info = array();
                $info['robot_id'] = (MMRPG_SETTINGS_TARGET_PLAYERID + $key + 1);
                $info['robot_token'] = $token;
                $info['robot_name'] = $index['robot_name'].' Σ';
                $info['robot_image'] = $token.'_alt9';
                $info['robot_level'] = $temp_final_option['battle_level'];
                $info['robot_core'] = 'empty';
                if (!empty($item_options)){ $info['robot_item'] = $item_options[mt_rand(0, $item_max_key)]; }
                $info['robot_abilities'] = array();
                $info['robot_abilities'] = mmrpg_prototype_generate_abilities($index, $info['robot_level'], 8);
                $info['values']['robot_rewards'] = array();
                $info['values']['robot_rewards']['robot_attack'] = 1000;
                $info['values']['robot_rewards']['robot_defense'] = 1000;
                $info['values']['robot_rewards']['robot_speed'] = 1000;
                $temp_final_option['battle_target_player']['player_robots'][] = $info;
            }

            // Add the mechas info into the omega battle
            $temp_battle_omega['battle_field_base']['field_mechas'] = $temp_support_mechas;
            shuffle($temp_final_option['battle_target_player']['player_robots']);
            //die('<pre>'.print_r($temp_final_option, true).'</pre>');
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);

        }

    }


    // -- PROTOTYPE COMPLETE BATTLE : CHAPTER SIX -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '5';

    // Only continue if the player has defeated all other battles
    if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['5']){

        // EVENT MESSAGE : CHAPTER SIX
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Bonus Chapter : Prototype Complete!'
            );

        // Generate the bonus battle and using the prototype data
        $temp_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 6, 'mecha');
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        // Add the omega battle to the options, index, and session
        $this_prototype_data['battle_options'][] = $temp_battle_omega;
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

        // Generate the bonus battle and using the prototype data
        $temp_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 4, 'master');
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        // Add the omega battle to the options, index, and session
        $this_prototype_data['battle_options'][] = $temp_battle_omega;
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    }


    // -- SPECIAL PLAYER BATTLE : BONUS CHAPTER -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '6';

    // Unlock a battle with a randomized player from the leaderboards if the game is done
    //$temp_flags = !empty($_SESSION['GAME']['flags']) ? $_SESSION['GAME']['flags'] : array();
    $temp_ptoken = str_replace('-', '', $this_prototype_data['this_player_token']);
    if ($this_prototype_data['this_chapter_unlocked']['6']){
        //die('checkpoint1');
        if (true){
            //die('checkpoint2');

            // EVENT MESSAGE : BONUS CHAPTER
            $this_prototype_data['battle_options'][] = array(
                'option_type' => 'message',
                'option_chapter' => $this_prototype_data['this_current_chapter'],
                'option_maintext' => 'Player Battles : Leaderboard Challengers'
                );

            /*

            // Include the leaderboard data for pruning
            $this_leaderboard_online_players = mmrpg_prototype_leaderboard_online();

            $temp_include_usernames = array();
            $temp_include_usernames_string = array();
            if (!empty($this_leaderboard_online_players)){
                foreach ($this_leaderboard_online_players AS $info){ $temp_include_usernames[] = $info['token']; }
                foreach ($temp_include_usernames AS $token){ $temp_include_usernames_string[] = "'{$token}'"; }
                $temp_include_usernames_string = implode(',', $temp_include_usernames_string);
            } else {
                $temp_include_usernames_string = '';
            }

            // Pull a random player from the database with a similar point level
            $this_player_points = $this_prototype_data['points_unlocked'];
            $this_player_points_max = ceil($this_player_points * 1.10);
            $temp_query_token = str_replace('-', '_', $this_prototype_data['target_player_token']);
            $temp_player_query = "SELECT mmrpg_leaderboard.user_id, mmrpg_users.user_name, mmrpg_users.user_name_clean, mmrpg_users.user_name_public, mmrpg_saves.save_values
                FROM mmrpg_leaderboard
                LEFT JOIN mmrpg_users ON mmrpg_users.user_id = mmrpg_leaderboard.user_id
                LEFT JOIN mmrpg_saves ON mmrpg_users.user_id = mmrpg_saves.user_id
                WHERE (board_points_{$temp_query_token} <= {$this_player_points_max}".(!empty($this_leaderboard_online_players) ? " OR user_name_clean IN ({$temp_include_usernames_string})" : '').")
                AND mmrpg_leaderboard.user_id != {$this_userid}
                ORDER BY board_points_{$temp_query_token} DESC
                LIMIT 12";
            //die($temp_player_query);
            $temp_player_list = $db->get_array_list($temp_player_query);
            */

            // Include the leaderboard data for pruning
            $this_leaderboard_online_players = mmrpg_prototype_leaderboard_online();
            $temp_include_usernames = array();
            if (!empty($this_leaderboard_online_players)){
                foreach ($this_leaderboard_online_players AS $info){ $temp_include_usernames[] = $info['token']; }
            }

            // Pull a random set of players from the database with similar point levels
            $temp_player_list = mmrpg_prototype_leaderboard_targets($this_userid, $this_prototype_data['target_player_token']);
            if (empty($temp_player_list)){ $temp_player_list = mmrpg_prototype_leaderboard_targets($this_userid, $this_prototype_data['this_player_token']); }

            // If player data was actuall pulled, continue
            if (!empty($temp_player_list)){

                // Shuffle the player list
                $max_battle_count = 2;
                if ($temp_player_list >= 4){ $max_battle_count = 4; }
                if ($temp_player_list >= 6){ $max_battle_count = 6; }
                uasort($temp_player_list, 'mmrpg_prototype_leaderboard_targets_sort');
                $temp_player_list = array_slice($temp_player_list, 0, 9);
                shuffle($temp_player_list);

                // Loop through the list up for two to four times, creating new battles
                if (empty($_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_player_battle_factors'])){
                    $temp_field_factors_one = $this_omega_factors_two;
                    $temp_field_factors_two = $this_omega_factors_one;
                    $temp_field_factors_three = $this_omega_factors_three;
                    shuffle($temp_field_factors_one);
                    shuffle($temp_field_factors_two);
                    shuffle($temp_field_factors_three);
                    $temp_one = array_merge($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
                    $temp_two = array_merge($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
                    $temp_three = array_merge($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
                    $temp_field_factors_one = $temp_one;
                    $temp_field_factors_two = $temp_two;
                    $temp_field_factors_three = $temp_three;
                    shuffle($temp_field_factors_one);
                    shuffle($temp_field_factors_two);
                    shuffle($temp_field_factors_three);
                    $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_player_battle_factors'] = array($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
                } else {
                    list($temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three) = $_SESSION['PROTOTYPE_TEMP'][$this_prototype_data['this_player_token'].'_player_battle_factors'];
                }

                for ($i = 0; $i < $max_battle_count; $i++){

                    // DEBUG
                    //echo('<pre>$temp_field_factors_one:'.print_r($temp_field_factors_one, true).'</pre><hr />');
                    //echo('<pre>$temp_field_factors_two:'.print_r($temp_field_factors_two, true).'</pre><hr />');
                    //echo('<pre>$temp_field_factors_three:'.print_r($temp_field_factors_three, true).'</pre><hr />');
                    //die();

                    // If there are no more players, break
                    if (empty($temp_player_list)){ break; }

                    // Pull and random player from the list and collect their full data
                    $temp_max_robots = 2;
                    if ($i >= 2 && $this_prototype_data['robots_unlocked'] >= 4){ $temp_max_robots = 4; }
                    if ($i >= 4 && $this_prototype_data['robots_unlocked'] >= 8){ $temp_max_robots = 8; }
                    //$temp_max_robots = 8; // MAYBE?
                    //$temp_player_data = array_shift($temp_player_list); //$temp_player_list[array_rand($temp_player_list)];
                    $temp_player_array = array_shift($temp_player_list);
                    $temp_battle_omega = rpg_mission_player::generate($this_prototype_data, $temp_player_array, $temp_max_robots, $temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
                    //die('<pre>$temp_battle_omega1 : '.print_r($temp_battle_omega, true).'</pre>');

                    // If the collected omega battle was empty, continue gracefully
                    if (empty($temp_battle_omega) || empty($temp_battle_omega['battle_token'])){
                        //$i--;
                        //die('<pre>$temp_battle_omega1.5 : '.print_r($temp_battle_omega, true).'</pre>');
                        continue;
                    }
                    //die('<pre>$temp_battle_omega2 : '.print_r($temp_battle_omega, true).'</pre>');
                    //die('<pre>$temp_battle_omega3 : '.print_r($temp_battle_omega, true).'</pre>');

                    // DEBUG
                    //echo('<pre>$temp_field_factors_one:'.print_r($temp_field_factors_one, true).'</pre><hr />');

                    // If there was no battle token defined, we have a problem
                    //if (empty($temp_battle_omega['battle_token'])){ die('<pre>$temp_battle_omega:'.print_r($temp_battle_omega, true).'</pre>'); }

                    // Update the option chapter to the current
                    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

                    // If this user is only, update the battle button with details
                    if (in_array($temp_player_array['user_name_clean'], $temp_include_usernames)){
                        $temp_battle_omega['option_style'] = 'border-color: green !important; ';
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="online_type player_type player_type_nature">Online</sup>';
                    }
                    //die('<pre>$temp_battle_omega4 : '.print_r($temp_battle_omega, true).'</pre>');

                    // Add the omega battle to the options, index, and session
                    $this_prototype_data['battle_options'][] = $temp_battle_omega;
                    rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                    unset($temp_battle_omega);
                    //die('<pre>$temp_battle_omega5 : ---</pre>');

                }

            }

            // Unset the temp player array
            //die('<pre>checkpoint 6 i guess? : ---</pre>');
            unset($temp_player_list);

        }

    }


}
//die('<pre>checkpoint 7 i guess? : ---</pre>');



?>