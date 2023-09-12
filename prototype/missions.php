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
    $mmrpg_indedx_players = rpg_player::get_index();
    $mmrpg_index_robots = rpg_robot::get_index(true);
    $mmrpg_index_fields = rpg_field::get_index();

    // -- STARTER BATTLE : CHAPTER ONE -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '0';

    // If the player has completed at least zero battles, display the starter battle
    if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0'])){

        // EVENT MESSAGE : CHAPTER ONE
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Chapter One : An Unexpected Attack'
            );

        // Intro Battle I (Vs. MET)
        // Always generate the very first battle no matter what
        if (true){

            // Generate the battle option with the starter data
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'];
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
                $temp_battle_omega = rpg_mission_starter::generate_intro($this_prototype_data, $this_prototype_data['this_intro_targets'][0], $this_prototype_data['this_chapter_levels'][0], '', $this_prototype_data['this_intro_field']);
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
            } else {
                $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
                $temp_battle_omega = rpg_battle::get_index_info($temp_battle_token);
            }
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

        }

        // Intro Battle II (Vs. SNIPER/CRYSTAL/SKELETON-JOE)
        // Only continue if the player has defeated the first 1 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0b'])){

            // Generate the battle option with the starter data
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'b';
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
                $temp_battle_omega = rpg_mission_starter::generate_midboss($this_prototype_data, $this_prototype_data['this_intro_targets'][1], ($this_prototype_data['this_chapter_levels'][0] + 1), $this_prototype_data['this_support_robot'], $this_prototype_data['this_player_field']);
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
            } else {
                $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
                $temp_battle_omega = rpg_battle::get_index_info($temp_battle_token);
            }
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

        }

        // Intro Battle III (Vs. TRILL [SPEED/DEFENSE/ATTACK])
        // Only continue if the player has defeated the first 2 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['0c'])){

            // Generate the battle option with the starter data
            $temp_session_token = $this_prototype_data['this_player_token'].'_battle_'.$this_prototype_data['this_current_chapter'].'c';
            if (empty($_SESSION['PROTOTYPE_TEMP'][$temp_session_token])){
                $temp_battle_omega = rpg_mission_starter::generate_boss($this_prototype_data, $this_prototype_data['this_intro_targets'][2], ($this_prototype_data['this_chapter_levels'][0] + 2), '', 'prototype-subspace');
                $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                $_SESSION['PROTOTYPE_TEMP'][$temp_session_token] = $temp_battle_omega['battle_token'];
            } else {
                $temp_battle_token = $_SESSION['PROTOTYPE_TEMP'][$temp_session_token];
                $temp_battle_omega = rpg_battle::get_index_info($temp_battle_token);
            }
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

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

        /*
        // DEBUG DEBUG DEBUG

        // GENERATE DOC ROBOT BATTLE
        // Generate a single battle against a specific robot master
        $temp_battle_omega = rpg_mission_single::generate($this_prototype_data, 'doc-robot', 'robot-museum', $this_prototype_data['this_chapter_levels'][1]);
        $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
        $temp_battle_omega['battle_field_base']['field_background_variant'] = 'mm1';
        $temp_battle_omega['battle_size'] = '1x4';
        rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
        $this_prototype_data['battle_options'][] = $temp_battle_omega;

        // DEBUG DEBUG DEBUG
        */

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
        $temp_rival_option_token = $this_prototype_data['this_player_token'].'-fortress-i';
        $temp_rival_option = rpg_battle::get_index_info($temp_rival_option_token, true);
        $temp_rival_option['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_rival_option['battle_level'] = $this_prototype_data['this_chapter_levels'][2];
        $temp_rival_option['option_chapter'] = $this_prototype_data['this_current_chapter'];

        // If the battle is complete, remove the player from the description
        $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_rival_option_token);
        if ($temp_battle_complete){
            $temp_rival_option['battle_target_player']['player_token'] = 'player';
            $temp_rival_option['battle_description'] = preg_replace('/^Defeat (Dr. (Wily|Light|Cossack)\'s)/i', 'Defeat', $temp_rival_option['battle_description']);
            // Also make sure any unlocked robots appear in greyscale on the button
            foreach ($temp_rival_option['battle_target_player']['player_robots'] AS $rm_key => $rm_robot){
                if (mmrpg_prototype_robot_unlocked(false, $rm_robot['robot_token'])){
                    //$rm_robot['flags']['hide_from_mission_select'] = true;
                    $rm_robot['flags']['shadow_on_mission_select'] = true;
                    $temp_rival_option['battle_target_player']['player_robots'][$rm_key] = $rm_robot;
                }
            }
        }

        // Recalculate zenny and turns for this fortress mission
        rpg_mission_fortress::prepare($temp_rival_option, $this_prototype_data);

        // Add the omega battle to the battle options
        $this_prototype_data['battle_options'][] = $temp_rival_option;
        rpg_battle::update_index_info($temp_rival_option['battle_token'], $temp_rival_option);

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
            'option_maintext' => 'Chapter Five : The Final Destination'
            );

        // Collect information on this prototype player so we can use it later
        $this_prototype_player_data = $mmrpg_index_players[$this_prototype_data['this_player_token']];
        $target_prototype_player_data = $mmrpg_index_players[$this_prototype_data['target_player_token']];

        // Final Destination I (ENKER/PUNK/BALLADE)
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4a'])){

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-ii';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token);
            $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][4];
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_mission_fortress::prepare($temp_final_option, $this_prototype_data);
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
            rpg_mission_fortress::prepare($temp_final_option, $this_prototype_data);
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);

        }

        // Final Destination III (SLUR w/ TRILL SUPPORT)
        // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
        if ($this_prototype_data['prototype_complete'] || !empty($this_prototype_data['this_chapter_unlocked']['4c'])){

            // Insert Slur w/ Trill battle here
            // "Defeat the alien robot Slur in this daunting/desperate/decisive final battle!"
            //error_log($this_prototype_data['this_player_token'].' // Insert Slur w/ Trill battle');
            if (true){

                // Unlock the real final battle of the final destination battles
                $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iv';
                $temp_final_option = rpg_battle::get_index_info($temp_final_option_token, true);
                $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
                $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][6] + 5;
                $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
                $temp_final_option_descriptor = 'intense';
                if ($this_prototype_player_data['player_token'] === 'dr-light'){ $temp_final_option_descriptor = 'daunting'; }
                elseif ($this_prototype_player_data['player_token'] === 'dr-wily'){ $temp_final_option_descriptor = 'desperate'; }
                elseif ($this_prototype_player_data['player_token'] === 'dr-cossack'){ $temp_final_option_descriptor = 'decisive'; }
                $temp_final_option['battle_description'] = 'Defeat the alien robot Slur in this '.$temp_final_option_descriptor.' final battle!';

                // Define the stats we'll be working with for the real_final boss
                $temp_this_player_info = $mmrpg_index_players[$this_prototype_data['this_player_token']];
                $temp_target_player_info = $mmrpg_index_players[$this_prototype_data['target_player_token']];
                $this_player_stat_token = $mmrpg_index_players[$this_prototype_data['this_player_token']]['player_type'];
                $target_player_stat_token = $mmrpg_index_players[$this_prototype_data['target_player_token']]['player_type'];

                // Update the battle field base with more dynamic options
                $temp_fusion_field = 'prototype-subspace';
                $temp_fusion_field_variant = '';
                $backup_battle_field_base = $temp_final_option['battle_field_base'];
                $temp_final_option['battle_field_base'] = array();
                $temp_final_option['battle_field_base']['field_music'] = 'sega-remix/wily-fortress-1-mm7';
                $temp_final_option['battle_field_base']['field_multipliers'] = array('experience' => 2);
                if ($this_prototype_player_data['player_number'] >= 2){
                    $temp_final_option['battle_field_base']['field_multipliers']['experience'] += 1;
                }
                $temp_final_option['battle_field_base'] = array_merge($backup_battle_field_base, $temp_final_option['battle_field_base']);

                // The default for this battle are nice, but we should replace them with better options
                $temp_final_option['battle_target_player']['player_robots'] = array();

                // Define the details for the main real_final boss and append them to the array
                $final_boss_token = 'slur';
                $final_boss_image = 'slur'; // base sprite
                if ($this_prototype_player_data['player_token'] === 'dr-wily'){ $final_boss_image .= '_alt'; } // emboldened alt
                elseif ($this_prototype_player_data['player_token'] === 'dr-cossack'){ $final_boss_image .= '_alt2'; } // final weapon alt
                $final_boss_index_info = $mmrpg_index_robots[$final_boss_token];
                $final_boss_info = array('robot_token' => $final_boss_token, 'robot_image' => $final_boss_image);
                $final_boss_info['counters'] = array();
                $final_boss_info['counters']['attack_mods'] = $this_prototype_player_data['player_number'];
                $final_boss_info['counters']['defense_mods'] = $this_prototype_player_data['player_number'];
                $final_boss_info['counters']['speed_mods'] = $this_prototype_player_data['player_number'];
                $final_boss_info['counters'][$this_player_stat_token.'_mods'] += 2;
                $final_boss_info['robot_level'] = $temp_final_option['battle_level'];
                $final_boss_info['robot_item'] = 'space-core';
                if ($this_prototype_player_data['player_token'] === 'dr-wily'){ $final_boss_info['robot_item'] = 'energy-upgrade'; }
                elseif ($this_prototype_player_data['player_token'] === 'dr-cossack'){ $final_boss_info['robot_item'] = 'target-module'; }
                $final_boss_info['robot_abilities'] = array();
                if ($this_prototype_player_data['player_number'] >= 1){ $final_boss_info['robot_abilities'][] = 'space-overdrive'; }
                if ($this_prototype_player_data['player_number'] >= 2){ $final_boss_info['robot_abilities'][] = 'space-buster'; }
                if ($this_prototype_player_data['player_number'] >= 3){ $final_boss_info['robot_abilities'][] = 'space-shot'; }
                $final_boss_info['robot_abilities'][] = 'energy-break';
                $final_boss_info['robot_abilities'][] = 'buster-charge';
                $temp_addon_abilties = array('core-laser', $target_player_stat_token.'-assault');
                if (!empty($temp_addon_abilties)){ $final_boss_info['robot_abilities'] = array_merge($final_boss_info['robot_abilities'], $temp_addon_abilties); }
                $final_replacement_quotes = array();
                if ($this_prototype_player_data['player_token'] === 'dr-light'){
                    $final_replacement_quotes['battle_start'] = 'The human they call Hikari? Your hubris will be your demise.';
                    $final_replacement_quotes['battle_taunt'] = 'You remind me of a Creator. It will be shame to extingish you.';
                    $final_replacement_quotes['battle_victory'] = 'Can you see me, Master? I have finally completed my mission!';
                    $final_replacement_quotes['battle_defeat'] = 'Savour your temporary victory... I will return even more powerful...!';
                } else if ($this_prototype_player_data['player_token'] === 'dr-wily'){
                    $final_replacement_quotes['battle_start'] = 'I sense an Evil in your heart. Allow me to erase you for it!';
                    $final_replacement_quotes['battle_taunt'] = 'Stupid Earth creatures! You are only delaying the inevitable!';
                    $final_replacement_quotes['battle_victory'] = 'The Earth should thank me for saving it from your tragic future!';
                    $final_replacement_quotes['battle_defeat'] = 'It doesn\'t end like this... I still have one more fight left in me...';
                } else if ($this_prototype_player_data['player_token'] === 'dr-cossack'){
                    $final_replacement_quotes['battle_start'] = 'I sense you are somehow... responsible... for my suffering!';
                    $final_replacement_quotes['battle_taunt'] = 'Stop resisting! It is time you Earthlings met your deserved ends!';
                    $final_replacement_quotes['battle_victory'] = 'You wretched Earth NetNavis are all the same! Weak! Nyahahaha!';
                    $final_replacement_quotes['battle_defeat'] = 'Master... why have you forsaken me again? Was it all for nothing...?';
                }
                $final_boss_info['robot_quotes'] = array_merge($mmrpg_index_robots[$final_boss_token]['robot_quotes'], $final_replacement_quotes);
                $temp_final_option['battle_target_player']['player_robots'][] = $final_boss_info;

                // Define the details for the real_final boss's minion "trill" and append them to the array
                $final_boss_support_token = 'trill';
                $final_boss_support_count = 2;
                $final_boss_support_info = array('robot_token' => $final_boss_support_token);
                $final_boss_support_info['counters'] = array();
                $final_boss_support_info['counters'][$this_player_stat_token.'_mods'] = 1;
                $final_boss_support_info['robot_level'] = $final_boss_info['robot_level'] - 5;
                $final_boss_support_info['robot_item'] = '';
                if ($this_prototype_player_data['player_number'] >= 1){ $final_boss_support_info['robot_item'] = $this_player_stat_token.'-pellet'; }
                if ($this_prototype_player_data['player_number'] >= 2){ $final_boss_support_info['robot_item'] = $this_player_stat_token.'-capsule'; }
                if ($this_prototype_player_data['player_number'] >= 3){ $final_boss_support_info['robot_item'] = $this_player_stat_token.'-booster'; }
                $final_boss_support_info['robot_abilities'] = array();
                if ($this_prototype_player_data['player_number'] >= 1){ $final_boss_support_info['robot_abilities'][] = 'space-overdrive'; }
                if ($this_prototype_player_data['player_number'] >= 2){ $final_boss_support_info['robot_abilities'][] = 'space-buster'; }
                if ($this_prototype_player_data['player_number'] >= 3){ $final_boss_support_info['robot_abilities'][] = 'space-shot'; }
                $final_boss_support_info['robot_abilities'][] = 'energy-break';
                $final_boss_support_info['robot_abilities'][] = 'buster-charge';
                $temp_addon_abilties = array('rhythm-satellite', $this_player_stat_token.'-mode', $target_player_stat_token.'-support');
                if (!empty($temp_addon_abilties)){ $final_boss_support_info['robot_abilities'] = array_merge($final_boss_support_info['robot_abilities'], $temp_addon_abilties); }
                for ($i = 0; $i < $final_boss_support_count; $i++){ $temp_final_option['battle_target_player']['player_robots'][] = $final_boss_support_info; }

                // Update the battle description with more dynamic options
                $temp_description_options = array('daunting', 'desperate', 'decisive');
                $temp_description_key = $this_prototype_player_data['player_number'] - 1;
                $temp_final_option['battle_description'] = 'Defeat the alien robot '.$final_boss_index_info['robot_name'].' in this '.$temp_description_options[$temp_description_key].' final battle!';

                // Now that everything is as we want it, we can prepare the battle and update the index
                rpg_mission_fortress::prepare($temp_final_option, $this_prototype_data);
                $this_prototype_data['battle_options'][] = $temp_final_option;
                rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);

            }

            // Final Destination BEFORE-III (ROBOT MASTERS w/ DARKNESS ALTS)
            // Only prepend this battle to the final one if we're in the second/middle player campaign
            if ($this_prototype_player_data['player_number'] >= 2){

                // Prepend the Darkness Alts battle here
                // "Defeat the army of robot master clones augmented with darkness energy!"
                //error_log($this_prototype_data['this_player_token'].' // Prepend the Darkness Alts battle');
                if (true){

                    // Unlock the prefix to the final destination III battles
                    $temp_before_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iv-before';
                    $temp_before_final_option = rpg_battle::get_index_info($temp_final_option['battle_token'], true);
                    $temp_before_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
                    $temp_before_final_option['battle_token'] = $temp_before_final_option_token;
                    $temp_before_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
                    $temp_before_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][6];
                    $temp_before_final_option['battle_description'] = 'Defeat the army of robot master clones augmented with darkness energy!';

                    /*
                    // Collect and define the robot masters and support mechas to appear on this field
                    $temp_robot_masters = array();
                    $temp_support_mechas = array();
                    $temp_omega_session_key = $this_prototype_data['prev_player_token'].'_target-robot-omega_prototype';
                    $temp_omega_robots_array = !empty($_SESSION['GAME']['values'][$temp_omega_session_key]) ? $_SESSION['GAME']['values'][$temp_omega_session_key] : array();
                    if (isset($temp_omega_robots_array[1][0])){ $temp_omega_robots_array = $temp_omega_robots_array[1]; }
                    foreach ($temp_omega_robots_array AS $key => $info){
                        $temp_field_info = rpg_field::parse_index_info($mmrpg_index_fields[$info['field']]);
                        if (!empty($temp_field_info['field_master'])){ $temp_robot_masters[] = $temp_field_info['field_master']; }
                        if (!empty($temp_field_info['field_mechas'])){ $temp_support_mechas[] = array_pop($temp_field_info['field_mechas']); }
                    }
                    */

                    // Define the "asteroid" robot masters (alt10) that should appear in Slur's pre-battle
                    $temp_asteroid_robots = array('slash-man', 'cold-man', 'gravity-man', 'napalm-man', 'plant-man', 'stone-man', 'sword-man', 'yamato-man');

                    // Decide how many robots to pull into this battle depending on player
                    $asteroid_tier = 0;
                    if ($this_prototype_data['this_player_token'] == 'dr-light'){ $asteroid_tier = 2; }
                    elseif ($this_prototype_data['this_player_token'] == 'dr-wily'){ $asteroid_tier = 4; }
                    elseif ($this_prototype_data['this_player_token'] == 'dr-cossack'){ $asteroid_tier = 8; }
                    shuffle($temp_asteroid_robots);
                    $temp_robot_masters = array_slice($temp_asteroid_robots, 0, $asteroid_tier);
                    $temp_support_mechas = array('sniper-joe', 'skeleton-joe', 'crystal-joe');

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
                    //foreach ($temp_before_final_option['battle_target_player']['player_robots'] AS $key => $info){
                    $temp_before_final_option['battle_target_player']['player_robots'] = array();
                    foreach ($temp_robot_masters AS $key => $token){
                        //if ($info['robot_level'] > $temp_before_final_option['battle_level']){ $temp_before_final_option['battle_level'] = $info['robot_level']; }
                        $index = $mmrpg_index_robots[$token];
                        $info = array();
                        $info['robot_id'] = ($key + 1); // temp, is changed later
                        $info['robot_token'] = $token;
                        //$info['robot_name'] = $index['robot_name'].' Σ';
                        //$info['robot_image'] = $token.'_alt9';
                        $info['robot_name'] = $index['robot_name'].' ☆';
                        $info['robot_image'] = $token.'_alt10';
                        $info['robot_level'] = $temp_before_final_option['battle_level'] - 5;
                        $info['robot_core'] = 'empty';
                        if (!empty($item_options)){ $info['robot_item'] = $item_options[mt_rand(0, $item_max_key)]; }
                        $info['robot_abilities'] = array();
                        $info['robot_abilities'] = mmrpg_prototype_generate_abilities($index, $info['robot_level'], 8);
                        $temp_before_final_option['battle_target_player']['player_robots'][] = $info;
                    }

                    // Add the mechas info into the omega battle
                    $temp_before_final_option['battle_field_base']['field_music'] = 'sega-remix/wily-fortress-2-mm9';
                    $temp_before_final_option['battle_field_base']['field_mechas'] = $temp_support_mechas;
                    shuffle($temp_before_final_option['battle_target_player']['player_robots']);

                    // Prepare the final battle details, add it to the index and/or buttons, and then queue it up
                    rpg_mission_fortress::prepare($temp_before_final_option, $this_prototype_data);
                    //$this_prototype_data['battle_options'][] = $temp_before_final_option;
                    rpg_battle::update_index_info($temp_before_final_option['battle_token'], $temp_before_final_option);
                    mmrpg_prototype_mission_autoplay_prepend($temp_final_option, $temp_before_final_option, $this_prototype_data, true);

                }

                // Final Destination AFTER-III (CRESTFALLEN SLUR w/ DUO SUPPORT)
                // Only prepend this battle to the final one if we're in the third and final player campaign
                if ($this_prototype_player_data['player_number'] >= 3){

                    // Append the Crestfallen Slur battle here
                    // "Defeat the forlorn soldier Slur in this climactic final encounter!"
                    //error_log($this_prototype_data['this_player_token'].' // Append the Crestfallen Slur battle');
                    if (true){

                        // Unlock the last of the final destination III battles
                        $temp_after_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iv-after';
                        $temp_after_final_option = rpg_battle::get_index_info($temp_final_option['battle_token'], true);
                        $temp_after_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
                        $temp_after_final_option['battle_token'] = $temp_after_final_option_token;
                        $temp_after_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
                        $temp_after_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][6];
                        $temp_after_final_option['battle_description'] = 'Defeat the forlorn soldier Slur in this climactic final encounter!';

                        // Update the battle field base with more dynamic options
                        $backup_battle_field_base = $temp_after_final_option['battle_field_base'];
                        $temp_fusion_field = 'prototype-subspace';
                        $temp_fusion_field_background_variant = 'final-destination';
                        $temp_fusion_field_foreground_variant = 'decayed';
                        $temp_after_final_option['battle_field_base'] = array();
                        $temp_after_final_option['battle_field_base']['field_music'] = 'sega-remix/final-boss-rnf';
                        $temp_after_final_option['battle_field_base']['field_multipliers'] = array('experience' => 4);
                        $temp_after_final_option['battle_field_base']['field_background'] = $temp_fusion_field;
                        $temp_after_final_option['battle_field_base']['field_background_variant'] = $temp_fusion_field_background_variant;
                        $temp_after_final_option['battle_field_base']['field_background_attachments'] = array();
                        $temp_after_final_option['battle_field_base']['field_foreground'] = $temp_fusion_field;
                        $temp_after_final_option['battle_field_base']['field_foreground_variant'] = $temp_fusion_field_foreground_variant;
                        $temp_after_final_option['battle_field_base']['field_foreground_attachments'] = array();
                        $temp_after_final_option['battle_field_base'] = array_merge($backup_battle_field_base, $temp_after_final_option['battle_field_base']);

                        // The default for this battle are nice, but we should replace them with better options
                        $temp_after_final_option['battle_target_player']['player_robots'] = array();

                        // Define the details for the main real_final boss and append them to the array
                        $real_final_boss_token = 'slur';
                        $real_final_boss_image = 'slur_alt3';
                        $real_final_boss_index_info = $mmrpg_index_robots[$real_final_boss_token];
                        $real_final_boss_info = array('robot_token' => $real_final_boss_token, 'robot_image' => $real_final_boss_image);
                        $real_final_boss_info['flags'] = array();
                        $real_final_boss_info['flags']['final_boss'] = true;
                        $real_final_boss_info['flags']['absolute_final_boss'] = true;
                        $real_final_boss_info['counters'] = array();
                        $real_final_boss_info['counters']['attack_mods'] = MMRPG_SETTINGS_STATS_MOD_MAX;
                        $real_final_boss_info['counters']['defense_mods'] = MMRPG_SETTINGS_STATS_MOD_MAX;
                        $real_final_boss_info['counters']['speed_mods'] = MMRPG_SETTINGS_STATS_MOD_MAX;
                        $real_final_boss_info['robot_level'] = $temp_after_final_option['battle_level'] + 10;
                        $real_final_boss_info['robot_item'] = 'reverse-module';
                        $real_final_boss_info['robot_abilities'] = array('space-overdrive', 'laser-overdrive', 'shield-overdrive', 'buster-charge');
                        $temp_addon_abilties = array('freeze-overdrive', 'flame-overdrive', 'water-overdrive', 'electric-overdrive');
                        if (!empty($temp_addon_abilties)){ $real_final_boss_info['robot_abilities'] = array_merge($real_final_boss_info['robot_abilities'], $temp_addon_abilties); }
                        $real_final_replacement_quotes = array();
                        if ($this_prototype_player_data['player_token'] === 'dr-light'){
                            $real_final_replacement_quotes['battle_start'] = '...';
                            $real_final_replacement_quotes['battle_taunt'] = '...';
                            $real_final_replacement_quotes['battle_victory'] = '...!';
                            $real_final_replacement_quotes['battle_defeat'] = '...!';
                        } else if ($this_prototype_player_data['player_token'] === 'dr-wily'){
                            $real_final_replacement_quotes['battle_start'] = '...';
                            $real_final_replacement_quotes['battle_taunt'] = '...';
                            $real_final_replacement_quotes['battle_victory'] = '...!';
                            $real_final_replacement_quotes['battle_defeat'] = '...!';
                        } else if ($this_prototype_player_data['player_token'] === 'dr-cossack'){
                            $real_final_replacement_quotes['battle_start'] = 'No! I refuse to let it end this way!';
                            $real_final_replacement_quotes['battle_taunt'] = 'Master, why have not come for me? Are you even out there?'; //'Master! Have you have finally come for me?!';
                            $real_final_replacement_quotes['battle_victory'] = 'Master, I have completed my task! Are you still out there?'; //'Master, forgive me for what have I done....';
                            $real_final_replacement_quotes['battle_defeat'] = 'Master, forgive me for I have failed you...';
                        }
                        $real_final_boss_info['robot_quotes'] = array_merge($mmrpg_index_robots[$real_final_boss_token]['robot_quotes'], $real_final_replacement_quotes);
                        $temp_after_final_option['battle_target_player']['player_robots'][] = $real_final_boss_info;

                        // Prepare the final battle details, add it to the index and/or buttons, and then queue it up
                        rpg_mission_fortress::prepare($temp_after_final_option, $this_prototype_data);
                        //$this_prototype_data['battle_options'][] = $temp_after_final_option;
                        rpg_battle::update_index_info($temp_after_final_option['battle_token'], $temp_after_final_option);
                        mmrpg_prototype_mission_autoplay_append($temp_final_option, $temp_after_final_option, $this_prototype_data, true);



                    }

                }

            }

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

        // Calculate the current starforce total vs max starforce total for mission gen
        $this_prototype_data['current_starforce_total'] = !empty($_SESSION[$session_token]['values']['star_force']) ? array_sum($_SESSION[$session_token]['values']['star_force']) : 0;
        $this_prototype_data['max_starforce_total'] = array_sum($max_star_force);

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
                $temp_battle_omega = rpg_mission_starfield::generate_double($this_prototype_data, array($info['robot'], $info2['robot']), array($info['field'], $info2['field']), $star_level, true, false, true);
            } elseif (!empty($info)){
                $temp_battle_omega = rpg_mission_starfield::generate_single($this_prototype_data, $info['robot'], $info['field'], $star_level, true, false, true);
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
                $random_field_type = !empty($info2) ? $mmrpg_index_fields[$info2['field']]['field_type'] : $mmrpg_index_fields[$info['field']]['field_type'];
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
                    if (!empty($temp_player_array['values']['flag_online'])){
                        $temp_battle_omega['option_style'] = 'border-color: green !important; ';
                        $temp_battle_omega['battle_description2'] .= 'This player was recently online!';
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="online_type player_type player_type_nature">Online</sup>';
                    }

                    // If this user is defeated, update the battle button with details
                    if (!empty($temp_player_array['values']['flag_defeated'])){
                        $victory_token_colour = !empty($temp_player_array['values']['colour_token']) ? $temp_player_array['values']['colour_token'] : 'none';
                        if (!empty($temp_player_array['values']['colour_token2'])){ $victory_token_colour .= $temp_player_array['values']['colour_token2']; }
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="special_type player_type player_type_'.$victory_token_colour.'">&#9733;</sup>';
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

        // Check to see if this user already has a playlist of challenges
        $temp_session_key = $this_prototype_data['this_player_token'].'_target-challenge-missions';
        $temp_challenge_playlist = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();
        if (!empty($temp_challenge_playlist)){
            $temp_playlist_challenges = rpg_mission_challenge::get_custom_missions($this_prototype_data, $temp_challenge_playlist);
            if (!empty($temp_playlist_challenges)){ $temp_challenge_missions = array_merge($temp_challenge_missions, $temp_playlist_challenges); }
        }

        // Check to see if there are any event challenges to display right now
        if (count($temp_challenge_missions) < 3){
            $include_hidden = rpg_user::current_user_has_permission('edit-challenges') ? true : false;
            $temp_required = 3 - count($temp_challenge_missions);
            $temp_event_challenges = rpg_mission_challenge::get_missions($this_prototype_data, 'event', $temp_required, $include_hidden, false);
            if (!empty($temp_event_challenges)){ $temp_challenge_missions = array_merge($temp_challenge_missions, $temp_event_challenges); }
        }

        // Loop through any collected event or user challenges and then add them to the list
        if (!empty($temp_challenge_missions)){
            //echo('<pre>$this_prototype_data = '.print_r($this_prototype_data, true).'</pre>');
            //echo('<pre>$temp_challenge_missions = '.print_r($temp_challenge_missions, true).'</pre>');
            //exit();
            foreach ($temp_challenge_missions AS $key => $temp_battle_omega){
                $temp_challenge_kind = $temp_battle_omega['values']['challenge_battle_kind'];
                if (!empty($temp_battle_omega['flags']['is_cleared'])){
                    $victory_results = $temp_battle_omega['values']['challenge_records']['personal'];
                    $victory_points = rpg_mission_challenge::calculate_challenge_reward_points($temp_challenge_kind, $victory_results, $victory_percent, $victory_rank);
                    $cleared_token_colour = 'electric'; //!empty($temp_battle_omega['values']['colour_token']) ? $temp_battle_omega['values']['colour_token'] : 'none';
                    $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="special_type player_type player_type_'.$cleared_token_colour.'">&#9733; '.$victory_rank.'<span class="nobanner">-Rank Clear!</span></sup>';
                }
                if (!empty($temp_battle_omega['flags']['is_hidden'])){
                    $temp_battle_omega['option_style'] = 'border-color: red !important; ';
                    $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="special_type player_type player_type_flame">Hidden</sup>';
                }
                $this_prototype_data['battle_options'][] = $temp_battle_omega;
                rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            }
        }

        // Always add a final button for the ENDLESS ATTACK MODE challenge mission
        $temp_battle_sigma = rpg_mission_endless::generate_endless_mission($this_prototype_data, 1);
        rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);
        $this_prototype_data['battle_options'][] = $temp_battle_sigma;

    }


}

?>