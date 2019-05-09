<?

/*
 * PLAYER MISSION SELECT
 * Only re-generate missions if it is approriate to do
 * so at this time (the player is requesting missions)
 */

// Only generate out mission markup data if conditions allow or do not exist
if (!defined('MMRPG_SCRIPT_REQUEST') ||
    ($this_data_select == 'this_battle_token' && in_array('this_player_token='.$this_prototype_data['this_player_token'], $this_data_condition))){

    // Collect the robot index for quick use
    $db_robot_fields = rpg_robot::get_index_fields(true);
    $mmrpg_robots_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
    $mmrpg_fields_index = rpg_field::get_index();

    // -- STARTER BATTLE : CHAPTER ONE -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '0';

    // If the player has completed at least zero battles, display the starter battle
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0'])){

        // Generate the battle option with the starter data
        $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'];
        if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
            $temp_battle_omega = rpg_mission_starter::generate($this_prototype_data, 'met', $this_prototype_data['this_chapter_levels'][0], $this_prototype_data['this_support_robot']);
            $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
        } else {
            $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
            $temp_battle_omega = rpg_battle::get_index_info($temp_battle_token);
        }

        // EVENT MESSAGE : CHAPTER ONE
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Chapter One : An Unexpected Attack'
            );

        // Add the omega battle to the options, index, and session
        $this_prototype_data['battle_options'][] = $temp_battle_omega;

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

            // Check to see if we're generating from scratch or pulling from cache
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'_'.$key;
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){

                // GENERATE OMEGA BATTLE (SINGLES)
                // Generate a single battle against a specific robot master
                $temp_battle_omega = rpg_mission_single::generate($this_prototype_data, $info['robot'], $info['field'], $this_prototype_data['this_chapter_levels'][1]);
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $this_prototype_data['battle_options'][] = $temp_battle_omega;

                // GENERATE ALPHA BATTLE (SINGLES)
                // Split this mission into two phases, once with only mechas and once with the master
                $temp_battle_alpha = mmrpg_prototypt_extract_alpha_battle($temp_battle_omega, $this_prototype_data);
                rpg_battle::update_index_info($temp_battle_alpha['battle_token'], $temp_battle_alpha);
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $temp_option_key = count($this_prototype_data['battle_options']) - 1;
                $this_prototype_data['battle_options'][$temp_option_key]['alpha_battle_token'] = $temp_battle_alpha['battle_token'];

                // Cache the details required for the omega and alpha battles
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $this_prototype_data['battle_options'][$temp_option_key];

            } else {

                // Pull battle details from the cache and add to the options
                $this_prototype_data['battle_options'][] = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];

            }

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
        $temp_index_battle = rpg_battle::get_index_info($temp_battle_token, true);
        $temp_battle_omega = $temp_index_battle;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_level'] = $this_prototype_data['this_chapter_levels'][2];
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

        // If the battle is complete, remove the player from the description
        $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_token);
        if ($temp_battle_complete){
            $temp_battle_omega['battle_target_player'] = $temp_index_battle['battle_target_player'];
            $temp_battle_omega['battle_target_player']['player_token'] = 'player';
            $temp_battle_omega['battle_description'] = $temp_index_battle['battle_description'];
            $temp_battle_omega['battle_description'] = preg_replace('/^Defeat (Dr. (Wily|Light|Cossack)\'s)/i', 'Defeat', $temp_battle_omega['battle_description']);
            // Also make sure any unlocked robots appear in greyscale on the button
            foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $rm_key => $rm_robot){
                if (mmrpg_prototype_robot_unlocked(false, $rm_robot['robot_token'])){
                    //$rm_robot['flags']['hide_from_mission_select'] = true;
                    $rm_robot['flags']['shadow_on_mission_select'] = true;
                    $temp_battle_omega['battle_target_player']['player_robots'][$rm_key] = $rm_robot;
                }
            }
        }

        // Recalculate zenny and turns for this fortress mission
        rpg_mission::calculate_mission_zenny_and_turns($temp_battle_omega, $this_prototype_data, $mmrpg_robots_index);

        // Add the omega battle to the battle options
        $this_prototype_data['battle_options'][] = $temp_battle_omega;
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

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

            // Check to see if we're generating from scratch or pulling from cache
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'_'.$key;
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){

                // GENERATE OMEGA BATTLE (DOUBLES)
                // Generate a double battle against two different robot masters
                $info2 = $this_prototype_data['target_robot_omega'][$key + 1];
                $temp_battle_omega = rpg_mission_double::generate($this_prototype_data, array($info['robot'], $info2['robot']), array($info['field'], $info2['field']), $this_prototype_data['this_chapter_levels'][3], true, true);
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $this_prototype_data['battle_options'][] = $temp_battle_omega;

                // GENERATE ALPHA BATTLE (DOUBLES)
                // Split this mission into two phases, once with only mechas and once with the masters
                $temp_battle_alpha = mmrpg_prototypt_extract_alpha_battle($temp_battle_omega, $this_prototype_data);
                rpg_battle::update_index_info($temp_battle_alpha['battle_token'], $temp_battle_alpha);
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $temp_option_key = count($this_prototype_data['battle_options']) - 1;
                $this_prototype_data['battle_options'][$temp_option_key]['alpha_battle_token'] = $temp_battle_alpha['battle_token'];

                // Cache the details required for the omega and alpha battles
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $this_prototype_data['battle_options'][$temp_option_key];

            } else {

                // Pull battle details from the cache and add to the options
                $this_prototype_data['battle_options'][] = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];

            }

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

        // Final Destination I (ENKER/PUNK/BALLADE)
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4a'])){

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-ii';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token);
            $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][4];
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_mission::calculate_mission_zenny_and_turns($temp_final_option, $this_prototype_data, $mmrpg_robots_index);
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);

        }

        // Final Destination II (MEGA-DS/BASS-DS/PROTO-DS)
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4b'])){

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iii';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token);
            $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][4];
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_mission::calculate_mission_zenny_and_turns($temp_final_option, $this_prototype_data, $mmrpg_robots_index);
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);

        }

        // Final Destination III (ROBOT MASTERS w/ DARKNESS ALTS)
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4c'])){

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iv';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token);
            //if (empty($temp_final_option)){ die('$temp_final_option empty on line '.__LINE__.'<pre>'.print_r($this_prototype_data['this_player_token'].'-fortress-iv', true).'</pre>'); }
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
                $temp_field_info = rpg_field::parse_index_info($mmrpg_fields_index[$info['field']]);
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
                $index = rpg_robot::parse_index_info($mmrpg_robots_index[$token]);
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
                //$info['values']['robot_rewards'] = array();
                //$info['values']['robot_rewards']['robot_attack'] = 1000;
                //$info['values']['robot_rewards']['robot_defense'] = 1000;
                //$info['values']['robot_rewards']['robot_speed'] = 1000;
                $temp_final_option['battle_target_player']['player_robots'][] = $info;
            }

            // Add the mechas info into the omega battle
            $temp_final_option['battle_field_base']['field_mechas'] = $temp_support_mechas;
            shuffle($temp_final_option['battle_target_player']['player_robots']);
            //die('<pre>'.print_r($temp_final_option, true).'</pre>');
            rpg_mission::calculate_mission_zenny_and_turns($temp_final_option, $this_prototype_data, $mmrpg_robots_index);
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);

        }

    }


    // -- BONUS CHAPTER : STAR FIELDS (7) -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '7';

    // Only continue if the player has unlocked this extra chapter
    if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['7']){

        // EVENT MESSAGE : BONUS CHAPTER
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Bonus Chapter : Star Fields'
            );

        // Define how many fields we should show at once
        $star_fields_to_show = 12;

        // Count the number of stars collected to determine level
        $star_count = mmrpg_prototype_stars_unlocked();
        $star_level = 50 + ceil(50 * ($star_count / MMRPG_SETTINGS_STARFORCE_STARTOTAL));

        // Collect a list of possible stars
        $possible_star_list = mmrpg_prototype_possible_stars(true);
        $max_star_force = array();
        if (!empty($possible_star_list)){
            foreach ($possible_star_list AS $star_token => $star_info){
                if (!isset($max_star_force[$star_info['info1']['type']])){ $max_star_force[$star_info['info1']['type']] = 0; }
                if (!empty($star_info['info2']) && !isset($max_star_force[$star_info['info2']['type']])){ $max_star_force[$star_info['info2']['type']] = 0; }
                if ($star_info['kind'] == 'fusion'){
                    if ($star_info['info1']['type'] == $star_info['info2']['type']){
                        $max_star_force[$star_info['info1']['type']] += 2;
                    } else {
                        $max_star_force[$star_info['info1']['type']] += 1;
                        $max_star_force[$star_info['info2']['type']] += 1;
                    }
                } else {
                    $max_star_force[$star_info['info1']['type']] += 1;
                }
            }
        }

        // Collect a list of all stars that have not been claimed yet
        $remaining_stars = mmrpg_prototype_remaining_stars(true, $possible_star_list);
        $num_remaining_stars = count($remaining_stars);

        // Collect a list of star fields to display, prioritizing those the player doesn't have yet
        $visible_star_fields = array();
        if (!empty($remaining_stars)){ $visible_star_fields += array_keys($remaining_stars); }
        $num_visible_star_fields = count($visible_star_fields);
        if (empty($visible_star_fields)){
            $visible_star_fields += array_keys($possible_star_list);
        } elseif ($num_visible_star_fields < $star_fields_to_show){
            $possible_star_tokens = array_keys($possible_star_list);
            shuffle($possible_star_tokens);
            $visible_star_fields += array_slice($possible_star_tokens, 0, ($star_fields_to_show - $num_visible_star_fields));
        }
        $num_visible_star_fields = count($visible_star_fields);
        shuffle($visible_star_fields);

        // Create a flag variables for the random encounter
        $random_encounter_added = false;
        $random_encounter_chance = $num_remaining_stars == 0 || mt_rand(0, $num_remaining_stars) == 0 ? true : false;
        //$random_encounter_chance = true; // DEBUG
        $random_encounter_key = mt_rand(0, ($star_fields_to_show - 1));
        //$random_encounter_key = 0; // DEBUG

        // Loop through remaining stars and display the first twelve
        $added_star_fields = 0;
        foreach ($visible_star_fields AS $key => $star_token){
            $star_info = $possible_star_list[$star_token];

            // Collect references to the two stages' info
            $info = $star_info['info1'];
            $info2 = $star_info['info2'];

            // Generate either a double or single battle based on field factors
            if (!empty($info) && !empty($info2) && $info['field'] != $info2['field']){
                $temp_battle_omega = rpg_mission_double::generate($this_prototype_data, array($info['robot'], $info2['robot']), array($info['field'], $info2['field']), $star_level, true, false, true);
            } elseif (!empty($info)){
                $temp_battle_omega = rpg_mission_single::generate($this_prototype_data, $info['robot'], $info['field'], $star_level, true, false, true);
            }

            // Update the chapter number and then save this data to the temp index
            $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

            // Add the omega battle to the options, index, and session
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

            // If random encounter has not been added, check to see if we can add now
            if (empty($random_encounter_added)
                && $random_encounter_chance
                && $key == $random_encounter_key
                //&& $key == 0
                ){
                // Add a subtle indicator to the battle name
                $temp_option_key = count($this_prototype_data['battle_options']) - 1;
                $this_prototype_data['battle_options'][$temp_option_key]['battle_description2'] = rtrim($this_prototype_data['battle_options'][$temp_option_key]['battle_description2']).' Let\'s go!';
                // Generate a random encounter mission for the star fields
                //$player_starforce_levels = !empty($_SESSION[$session_token]['values']['star_force']) ? $_SESSION[$session_token]['values']['star_force'] : array();
                $random_encounter_added = true;
                $random_field_type = !empty($info2) ? $mmrpg_fields_index[$info2['field']]['field_type'] : $mmrpg_fields_index[$info['field']]['field_type'];
                $temp_battle_sigma = mmrpg_prototype_generate_mission($this_prototype_data,
                    $temp_battle_omega['battle_token'].'-random-encounter', array(
                        'battle_name' => 'Challenger from the Future?',
                        'battle_level' => 100,
                        'battle_description' => 'A mysterious challenger has appeared! Can you defeat them in battle?',
                        'battle_counts' => false
                        ), array_merge($temp_battle_omega['battle_field_base'], array(
                            'field_background' => 'prototype-complete',
                            'field_background_attachments' => array(),
                            'field_music' => 'sega-remix/boss-theme-mm10',
                            'values' => array('hazards' => array('super_blocks' => 'right'))
                            )
                        ), array(
                        'player_token' => 'player',
                        'player_starforce' => $max_star_force
                        ), array(
                        array('robot_token' => 'quint', 'robot_item' => $random_field_type.'-core', 'counters' => array('attack_mods' => 5, 'defense_mods' => 5, 'speed_mods' => 5)),
                        ), true);
                rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);
                mmrpg_prototype_mission_autoplay_append($temp_battle_omega, $temp_battle_sigma, $this_prototype_data, true);
                //$this_prototype_data['battle_options'][] = $temp_battle_sigma;
            }

            // If we're over the limit, break now
            $added_star_fields++;
            if ($added_star_fields >= $star_fields_to_show){ break; }

        }

    }


    // -- BONUS CHAPTER : PLAYER BATTLES (6) -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '6';

    // Only continue if the player has unlocked this extra chapter
    $temp_ptoken = str_replace('-', '', $this_prototype_data['this_player_token']);
    if ($this_prototype_data['this_chapter_unlocked']['6']){
        if (true){

            // EVENT MESSAGE : BONUS CHAPTER
            $this_prototype_data['battle_options'][] = array(
                'option_type' => 'message',
                'option_chapter' => $this_prototype_data['this_current_chapter'],
                'option_maintext' => 'Bonus Chapter : Player Battles'
                );

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
                $max_battle_count = 6;
                $max_target_count = 6;
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

                if ($this_prototype_data['robots_unlocked'] < $max_target_count){
                    $max_target_count = $this_prototype_data['robots_unlocked'];
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
                    //$temp_player_data = array_shift($temp_player_list); //$temp_player_list[array_rand($temp_player_list)];
                    $temp_player_array = array_shift($temp_player_list);
                    $temp_battle_omega = rpg_mission_player::generate($this_prototype_data, $temp_player_array, $max_target_count, 100, $temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
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

                    // If this user is online, update the battle button with details
                    if (!empty($temp_player_array['values']['flags_online'])){
                        $temp_battle_omega['option_style'] = 'border-color: green !important; ';
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="online_type player_type player_type_nature">Online</sup>';
                    }

                    // If this user is defeated, update the battle button with details
                    if (!empty($temp_player_array['values']['flag_defeated'])){
                        $victory_token_colour = !empty($temp_player_array['values']['colour_token']) ? $temp_player_array['values']['colour_token'] : 'none';
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="online_type player_type player_type_'.$victory_token_colour.'">&#9733;</sup>';
                        $temp_battle_omega['battle_description2'] .= 'This player\'s victory token has already been collected...';
                        $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * 0.10);
                    } else {
                        $temp_battle_omega['battle_description2'] .= 'Collect this player\'s victory token for additional battle points!';
                    }

                    // Add the omega battle to the options, index, and session
                    $this_prototype_data['battle_options'][] = $temp_battle_omega;
                    rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                    unset($temp_battle_omega);
                    //die('<pre>$temp_battle_omega5 : ---</pre>');

                }

            }

            // Unset the temp player array
            unset($temp_player_list);

        }

    }


    // -- BONUS CHAPTER : RANDOM MISSIONS (5) -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '5';

    // Only continue if the player has unlocked this extra chapter
    if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['5']){

        // EVENT MESSAGE : BONUS CHAPTER
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Bonus Chapter : Mission Randomizer'
            );

        // Generate the bonus battle and using the prototype data
        $temp_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 8, 'mecha');
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        // Add the omega battle to the options, index, and session
        $this_prototype_data['battle_options'][] = $temp_battle_omega;
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

        // Generate the bonus battle and using the prototype data
        $temp_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 7, 'master');
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        // Add the omega battle to the options, index, and session
        $this_prototype_data['battle_options'][] = $temp_battle_omega;
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

        // Generate the bonus battle and using the prototype data
        $temp_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 6, 'boss');
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        // Add the omega battle to the options, index, and session
        $this_prototype_data['battle_options'][] = $temp_battle_omega;
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);

    }


    // -- BONUS CHAPTER : CHALLENGE MISSIONS (8) -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '8';

    // Only continue if the player has unlocked this extra chapter
    if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['8']){

        // EVENT MESSAGE : CHALLENGE CHAPTER
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Bonus Chapter : Challenge Mode'
            );

        // Create an array to hold all the challenge missions
        $temp_challenge_missions = array();

        // Check to see if there are any event challenges to display right now
        $temp_event_challenges = rpg_mission_challenge::get_missions($this_prototype_data, 'event', 3, false, false);
        if (!empty($temp_event_challenges)){ $temp_challenge_missions = array_merge($temp_challenge_missions, $temp_event_challenges); }

        /*
        // WORK-IN-PROGRESS ONLY FOR ADMINS TEMP TEMP TEMP
        if (MMRPG_USER_IS_ADMIN){
            // Pad out the rest of the mission tab with user challenges if possible
            $req_user_challenges = 6 - (!empty($temp_event_challenges) ? (count($temp_event_challenges) * 2) : 0);
            if ($req_user_challenges > 0){
                $temp_user_challenges = rpg_mission_challenge::get_missions($this_prototype_data, 'user', $req_user_challenges, false);
                if (!empty($temp_user_challenges)){ $temp_challenge_missions = array_merge($temp_user_challenges, $temp_challenge_missions); }
            }
        }
        */

        // Loop through any collected event or user challenges and then add them to the list
        if (!empty($temp_challenge_missions)){
            foreach ($temp_challenge_missions AS $key => $temp_battle_omega){
                $this_prototype_data['battle_options'][] = $temp_battle_omega;
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            }
        }

    }


}

?>