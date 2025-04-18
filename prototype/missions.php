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

    // Define a reusable array + function to keep track of this specific groups of battles and their status for unlocking purposes
    $this_battle_options_groups = array();
    $new_group_name = function(){
        //error_log('new_group_name()');
        global $this_prototype_data;
        $new_group_name = '';
        $new_group_name .= 'p'.$this_prototype_data['this_player_number'];
        $new_group_name .= 'c'.($this_prototype_data['this_current_chapter'] + 1);
        return $new_group_name;
        };
    $new_battle_options_group = function($name){
        //error_log('new_battle_options_group($name:'.print_r($name, true).')');
        global $this_prototype_data, $this_battle_options_groups;
        $new_options_group = array();
        $this_battle_options_groups[$name] = $new_options_group;
        };
    $get_battle_options_group = function($name){
        //error_log('get_battle_options_group($name:'.print_r($name, true).')');
        global $this_prototype_data, $this_battle_options_groups;
        global $new_battle_options_group;
        if (!isset($this_battle_options_groups[$name])){ $new_battle_options_group($name); }
        $this_options_group = $this_battle_options_groups[$name];
        return $this_options_group;
        };
    $update_battle_options_group = function($name, $updated_options_group){
        //error_log('get_battle_options_group($name:'.print_r($name, true).', $updated_options_group:'.print_r($updated_options_group, true).')');
        global $this_prototype_data, $this_battle_options_groups;
        global $new_battle_options_group, $get_battle_options_group;
        if (!isset($this_battle_options_groups[$name])){ $new_battle_options_group($name); }
        $old_options_group = $this_battle_options_groups[$name];
        $merged_options_group = array_merge($old_options_group, $updated_options_group);
        $this_battle_options_groups[$name] = $merged_options_group;
        };
    $append_last_battle_option_to_group = function($name){
        //error_log('append_last_battle_option_to_group($name:'.print_r($name, true).')');
        global $this_prototype_data, $this_battle_options_groups;
        global $new_battle_options_group, $get_battle_options_group, $update_battle_options_group;
        $temp_option_key = count($this_prototype_data['battle_options']) - 1;
        $temp_battle_token = $this_prototype_data['battle_options'][$temp_option_key]['battle_token'];
        //error_log('$temp_option_key = '.print_r($temp_option_key, true));
        //error_log('$temp_battle_token = '.print_r($temp_battle_token, true));
        $this_options_group = $this_battle_options_groups[$name];
        $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_token) ? true : false;
        $this_options_group[] = array('battle_token' => $temp_battle_token, 'battle_complete' => $temp_battle_complete);
        $this_battle_options_groups[$name] = $this_options_group;
        };
    $append_faux_battle_option_to_group = function($name, $battle_complete = false){
        //error_log('$append_faux_battle_option_to_group($name:'.print_r($name, true).')');
        global $this_prototype_data, $this_battle_options_groups;
        global $new_battle_options_group, $get_battle_options_group, $update_battle_options_group;
        $this_options_group = $this_battle_options_groups[$name];
        $temp_battle_token = 'faux-battle-x'.count($this_options_group);
        $temp_battle_complete = $battle_complete;
        $this_options_group[] = array('battle_token' => $temp_battle_token, 'battle_complete' => $temp_battle_complete);
        $this_battle_options_groups[$name] = $this_options_group;
        };
    $get_battle_options_group_locks = function($name){
        //error_log('$get_battle_options_group_locks($name:'.print_r($name, true).')');
        global $this_prototype_data, $this_battle_options_groups;
        if (!isset($this_battle_options_groups[$name])){ return array(); }
        $this_options_group = $this_battle_options_groups[$name];
        return array_map(function($option){ return $option['battle_complete']; }, $this_options_group);
        };

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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

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

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        }

        // Intro Battle II (Vs. SNIPER/CRYSTAL/SKELETON-JOE)
        // Only continue if the player has defeated the first 1 battles
        $group_option_locks_completed = array_sum($get_battle_options_group_locks($this_group_name));
        if ($group_option_locks_completed >= 1){

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

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $get_battle_options_group_locks($this_group_name);
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
        }

        // Intro Battle III (Vs. TRILL [SPEED/DEFENSE/ATTACK])
        // Only continue if the player has defeated the first 2 battles
        $group_option_locks_completed = array_sum($get_battle_options_group_locks($this_group_name));
        if ($group_option_locks_completed >= 2){

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

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $get_battle_options_group_locks($this_group_name);
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

        // Increment the phase counter
        $this_prototype_data['battle_phase'] += 1;
        $this_prototype_data['phase_token'] = 'phase'.$this_prototype_data['battle_phase'];
        $this_prototype_data['phase_battle_token'] = $this_prototype_data['this_player_token'].'-'.$this_prototype_data['phase_token'];

        // Populate the battle options with the initial eight robots
        if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }
        //die('<pre>'.print_r($this_prototype_data['target_robot_omega'], true).'</pre>');

        // Loop through each of the eight robot masters
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
                $temp_battle_alpha = mmrpg_prototype_extract_alpha_battle($temp_battle_omega, $this_prototype_data);
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

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        }

        // ---------------------- //
        // NEW NEW NEW NEW CH4-DR //
        // ---------------------- //

        // -- CHAPTER TWO FORTRESS BATTLE -- //

        // GENERATE THE ROBOT MUSEUM BATTLE VS DOC ROBOT
        // Ensure the player has completed an appropriate number of pre-battles before unlocking this one
        $group_option_locks = $get_battle_options_group_locks($this_group_name);
        $group_options_total = count($group_option_locks);
        $group_options_complete_total = array_sum($group_option_locks);
        if ($group_options_complete_total >= $group_options_total){
            $temp_target_level = $this_prototype_data['this_chapter_levels'][1] + 8;
            $temp_battle_config = array();
            $temp_battle_config['battle_size'] = '1x4';
            $temp_target_robots = array();
            $temp_boss_robot = array('robot_token' => 'doc-robot', 'robot_item' => 'weapon-upgrade');
            $temp_mecha_robot = array('robot_token' => 'met', 'robot_item' => 'energy-upgrade', 'robot_level' => ceil($temp_target_level / 2));
            $temp_target_field = array('field_token' => 'robot-museum');
            if ($this_prototype_data['this_player_token'] === 'dr-light'){
                $temp_target_field['field_mechas'] = array('skeleton-joe');
                $temp_target_field['field_background_variant'] = 'mm1';
                $temp_boss_robot['robot_item'] = 'defense-capsule';
                $temp_boss_robot['robot_abilities'] = array('rolling-cutter', 'super-arm', 'ice-breath', 'hyper-bomb', 'fire-storm', 'thunder-strike', 'time-arrow', 'oil-shooter');
                $temp_mecha_robot['robot_token'] = $temp_target_field['field_mechas'][0];
                $temp_mecha_robot['robot_item'] = 'attack-pellet';
                //$temp_mecha_robot['robot_abilities'] = array('attack-support', 'energy-break', 'defense-break');
                $temp_battle_config['ability_rewards'] = array(array('token' => 'copy-shot', 'level' => 0));
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-wily'){
                $temp_target_field['field_mechas'] = array('crystal-joe');
                $temp_target_field['field_background_variant'] = 'mm2';
                $temp_boss_robot['robot_item'] = 'attack-capsule';
                $temp_boss_robot['robot_abilities'] = array('metal-blade', 'air-shooter', 'bubble-spray', 'quick-strike', 'crash-bomber', 'flash-pulse', 'atomic-fire', 'leaf-fall');
                $temp_mecha_robot['robot_token'] = $temp_target_field['field_mechas'][0];
                $temp_mecha_robot['robot_item'] = 'speed-pellet';
                //$temp_mecha_robot['robot_abilities'] = array('speed-support', 'energy-break', 'attack-break');
                $temp_battle_config['ability_rewards'] = array(array('token' => 'copy-soul', 'level' => 0));
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-cossack'){
                $temp_target_field['field_mechas'] = array('sniper-joe');
                $temp_target_field['field_background_variant'] = 'mm4';
                $temp_boss_robot['robot_item'] = 'speed-capsule';
                $temp_boss_robot['robot_abilities'] = array('bright-burst', 'rain-flush', 'drill-blitz', 'ring-boomerang', 'dust-crusher', 'skull-barrier', 'pharaoh-shot', 'dive-torpedo');
                $temp_mecha_robot['robot_token'] = $temp_target_field['field_mechas'][0];
                $temp_mecha_robot['robot_item'] = 'defense-pellet';
                //$temp_mecha_robot['robot_abilities'] = array('defense-support', 'energy-break', 'speed-break');
                $temp_battle_config['ability_rewards'] = array(array('token' => 'copy-style', 'level' => 0));
            }
            $temp_target_robots[] = $temp_boss_robot;
            $temp_target_robots[] = $temp_mecha_robot;
            $temp_target_robots[] = $temp_mecha_robot;
            $temp_battle_omega = rpg_mission_fortress::generate($this_prototype_data, $temp_battle_config, $temp_target_robots, $temp_target_field, $temp_target_level);
            $temp_battle_omega['battle_description'] = 'Defeat Doc Robot and '.(empty($temp_battle_omega['battle_description']) ? 'liberate' : 'secure').' the Robot Museum!';
            $temp_battle_omega['battle_description2'] = '';
            $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            $this_prototype_data['battle_options'][] = $temp_battle_omega;
            $append_last_battle_option_to_group($this_group_name);
        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $group_option_locks;
            $temp_placeholder['option_maintext'] = '';
            $temp_placeholder['option_subtext'] = '';
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

        // Always unlock the first fortress battle in this chapter
        if (true){

            // Unlock the first fortress battle
            $temp_target_level = $this_prototype_data['this_chapter_levels'][2];
            $temp_rival_option_token = $this_prototype_data['this_player_token'].'-fortress-i';
            $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_rival_option_token);
            $temp_rival_option = rpg_battle::get_index_info($temp_rival_option_token, true);
            $temp_rival_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_rival_option['battle_level'] = $this_prototype_data['this_chapter_levels'][2];
            $temp_rival_option['battle_rewards']['abilities'] = array();
            $temp_rival_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            // Always add a support mecha to this battle for healing
            $temp_mecha_token = false;
            $temp_mecha_abilities = array();
            $temp_mecha_level = ceil($temp_target_level / 2);
            if ($this_prototype_data['this_player_token'] === 'dr-light'){
                // evil heel w/ bass & disco
                $temp_mecha_token = 'heel-bot';
                $temp_rival_option['battle_rewards']['abilities'][] = array('token' => 'mecha-support', 'level' => 0);
                }
            elseif ($this_prototype_data['this_player_token'] === 'dr-wily'){
                // nice heal w/ proto and rhythm
                $temp_mecha_token = 'heal-bot';
                $temp_rival_option['battle_rewards']['abilities'][] = array('token' => 'friend-share', 'level' => 0);
                }
            elseif ($this_prototype_data['this_player_token'] === 'dr-cossack'){
                // a little bit of future content
                $temp_mecha_token = 'dark-frag';
                $temp_rival_option['battle_rewards']['abilities'][] = array('token' => 'mecha-assault', 'level' => 0);
                }
            if (!empty($temp_mecha_token)){
                if (strstr($temp_mecha_token, '-bot')){

                    // If this is a Heal-Bot or a Heel-Bot, we should give them stat-related boost or break abilities
                    $stat_mod_kind = (strstr($temp_mecha_token, 'heal') ? 'boost' : 'break');
                    $temp_mecha_abilities = array(
                        'energy-'.$stat_mod_kind,
                        'attack-'.$stat_mod_kind,
                        'defense-'.$stat_mod_kind,
                        'speed-'.$stat_mod_kind
                        );
                    $temp_mecha_info = array(
                        'robot_token' => $temp_mecha_token,
                        'robot_level' => $temp_mecha_level,
                        'robot_abilities' => $temp_mecha_abilities
                        );

                    // Simply append this robot onto the end of the hero + support team
                    $new_player_robots = array();
                    $new_player_robots = array_merge($new_player_robots, $temp_rival_option['battle_target_player']['player_robots']);
                    $new_player_robots[] = $temp_mecha_info;
                    $temp_rival_option['battle_target_player']['player_robots'] = $new_player_robots;

                } elseif ($temp_mecha_token === 'dark-frag'){

                    // If this is a Dark-Frag, we should give them some Dark-type abilities
                    $temp_mecha_abilities = array(
                        'dark-break',
                        'dark-boost',
                        'dark-drain'
                        );
                    $temp_mecha_info = array(
                        'robot_token' => $temp_mecha_token,
                        'robot_level' => $temp_mecha_level,
                        'robot_abilities' => $temp_mecha_abilities
                        );

                    // Prepend this mecha to the roster so it shows up first, then sendwhich the rest in two more
                    $new_player_robots = array();
                    $new_player_robots[] = $temp_mecha_info;
                    $new_player_robots[] = $temp_mecha_info;
                    $new_player_robots = array_merge($new_player_robots, $temp_rival_option['battle_target_player']['player_robots']);
                    $new_player_robots[] = $temp_mecha_info;
                    $temp_rival_option['battle_target_player']['player_robots'] = $new_player_robots;


                }
            }
            // If the battle is complete, remove the player from the description
            if ($temp_battle_complete){
                $temp_rival_option['battle_target_player']['player_token'] = 'player';
                $temp_rival_option['battle_description'] = preg_replace('/^Defeat (Dr. (Wily|Light|Cossack)\'s)/i', 'Defeat', $temp_rival_option['battle_description']);
                if (!empty($temp_rival_option['battle_field_base']['field_token'])){
                    $temp_field_base = rpg_field::get_index_info($temp_rival_option['battle_field_base']['field_token']);
                    $temp_rival_option['battle_description'] = substr(trim($temp_rival_option['battle_description']), 0, -1).' at '.$temp_field_base['field_name'].'!';
                }
                // Also make sure any unlocked robots appear in greyscale on the button
                foreach ($temp_rival_option['battle_target_player']['player_robots'] AS $rm_key => $rm_robot){
                    $rm_robot['robot_level'] = $temp_target_level;
                    if (mmrpg_prototype_robot_unlocked(false, $rm_robot['robot_token'])){
                        //$rm_robot['flags']['hide_from_mission_select'] = true;
                        $rm_robot['flags']['shadow_on_mission_select'] = true;
                    }
                    $temp_rival_option['battle_target_player']['player_robots'][$rm_key] = $rm_robot;
                }
            }

            // Recalculate zenny and turns for this fortress mission
            rpg_mission_fortress::prepare($temp_rival_option, $this_prototype_data);

            // Add the omega battle to the battle options
            $this_prototype_data['battle_options'][] = $temp_rival_option;
            rpg_battle::update_index_info($temp_rival_option['battle_token'], $temp_rival_option);

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        }


        // ----------------------- //
        // NEW NEW NEW NEW CH3-MMK //
        // ----------------------- //

        // -- CHAPTER THREE FORTRESS-II BATTLE -- //

        // GENERATE THE HUNTER COMPOUND BATTLE VS MEGAMAN KILLER
        // Ensure the player has completed an appropriate number of pre-battles before unlocking this one
        $group_option_locks_completed = array_sum($get_battle_options_group_locks($this_group_name));
        if ($group_option_locks_completed >= 1){
            $temp_battle_config = array();
            $temp_battle_config['battle_size'] = '1x4';
            $temp_battle_config['auto_hide_mechas'] = true;
            $temp_target_robots = array();
            $temp_target_field = array('field_token' => 'hunter-compound');
            if ($this_prototype_data['this_player_token'] === 'dr-light'){
                $hunter_token = 'enker';
                $hunter_abilities = array(
                    "shield-buster",
                    //"shield-shot",
                    "shield-overdrive",
                    //"buster-shot",
                    "buster-charge",
                    "energy-boost",
                    //"attack-boost",
                    //"defense-boost",
                    //"speed-boost",
                    "mega-slide",
                    "bass-baroque",
                    "proto-strike",
                    "mecha-assault"
                    );
                $temp_target_field['field_multipliers'] = array('experience' => 2, 'shield' => 1.6, 'shadow' => 1.4);
                $temp_target_field['field_music'] = 'sega-remix/special-stage-1-mm10';
                $temp_target_field['field_background_variant'] = $hunter_token;
                $temp_target_robots[] = array('robot_token' => $hunter_token, 'robot_item' => 'charge-module', 'robot_abilities' => $hunter_abilities);
                $temp_target_robots[] = array('robot_token' => 'beak', 'robot_item' => 'energy-pellet');
                $temp_target_robots[] = array('robot_token' => 'dark-man', 'robot_item' => 'attack-booster');
                $temp_target_robots[] = array('robot_token' => 'beak', 'robot_item' => 'energy-pellet');
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-wily'){
                $hunter_token = 'punk';
                $hunter_abilities = array(
                    "cutter-buster",
                    //"cutter-shot",
                    "cutter-overdrive",
                    //"buster-shot",
                    "buster-charge",
                    "energy-break",
                    //"attack-break",
                    //"defense-break",
                    //"speed-break",
                    "rising-cutter",
                    "shadow-blade",
                    //"hard-knuckle",
                    //"spark-shock",
                    //"bright-burst",
                    "metal-press",
                    "mecha-assault"
                    );
                $temp_target_field['field_multipliers'] = array('experience' => 2, 'cutter' => 1.6, 'shadow' => 1.4);
                $temp_target_field['field_music'] = 'sega-remix/special-stage-2-mm10';
                $temp_target_field['field_background_variant'] = $hunter_token;
                $temp_target_robots[] = array('robot_token' => $hunter_token, 'robot_item' => 'xtreme-module', 'robot_abilities' => $hunter_abilities);
                $temp_target_robots[] = array('robot_token' => 'mouslider', 'robot_item' => 'energy-capsule');
                $temp_target_robots[] = array('robot_token' => 'dark-man-2', 'robot_item' => 'defense-booster');
                $temp_target_robots[] = array('robot_token' => 'mouslider', 'robot_item' => 'energy-capsule');
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-cossack'){
                $hunter_token = 'ballade';
                $hunter_abilities = array(
                    "explode-buster",
                    //"explode-shot",
                    "explode-overdrive",
                    //"buster-shot",
                    "buster-charge",
                    "energy-assault",
                    //"attack-assault",
                    //"defense-assault",
                    //"speed-assault",
                    //"hyper-bomb",
                    "crash-avenger",
                    "danger-bomb",
                    "flash-bomb",
                    //"core-laser",
                    "mecha-party"
                    );
                $temp_target_field['field_multipliers'] = array('experience' => 2, 'explode' => 1.6, 'shadow' => 1.4);
                $temp_target_field['field_music'] = 'sega-remix/special-stage-3-mm10';
                $temp_target_field['field_background_variant'] = $hunter_token;
                $temp_target_robots[] = array('robot_token' => $hunter_token, 'robot_item' => 'target-module', 'robot_abilities' => $hunter_abilities);
                $temp_target_robots[] = array('robot_token' => 'shield-attacker', 'robot_item' => 'energy-tank');
                $temp_target_robots[] = array('robot_token' => 'dark-man-3', 'robot_item' => 'speed-booster');
                $temp_target_robots[] = array('robot_token' => 'shield-attacker', 'robot_item' => 'energy-tank');
            }
            $temp_target_level = $this_prototype_data['this_chapter_levels'][2] + 1;
            $temp_battle_omega = rpg_mission_fortress::generate($this_prototype_data, $temp_battle_config, $temp_target_robots, $temp_target_field, $temp_target_level);
            $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_battle_omega['battle_description'] = 'Defeat '.ucfirst($hunter_token).' and his Dark Man at the Hunter Compound!';
            rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $get_battle_options_group_locks($this_group_name);
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
        }

        // ----------------------- //
        // NEW NEW NEW NEW CH3-KNG //
        // ----------------------- //

        // -- CHAPTER THREE FORTRESS-III BATTLE -- //

        // GENERATE THE HUNTER COMPOUND BATTLE VS CONTROLLED KING
        // Ensure the player has completed an appropriate number of pre-battles before unlocking this one
        $group_option_locks_completed = array_sum($get_battle_options_group_locks($this_group_name));
        if ($group_option_locks_completed >= 2){
            $temp_battle_config = array();
            $temp_battle_config['battle_size'] = '1x4';
            $temp_target_robots = array();
            $temp_target_field = array('field_token' => 'royal-palace', 'field_music' => 'sega-remix/kings-fortress-rnf');
            $temp_target_field['field_multipliers'] = array('experience' => 3, 'shadow' => 1.8, 'space' => 1.6, 'shield' => 1.4, 'cutter' => 1.2);
            $temp_boss_robot = array('robot_token' => 'king', 'robot_image' => 'king_alt', 'robot_item' => 'reverse-module');
            $temp_support_robot1 = array('robot_token' => 'trill', 'robot_item' => 'energy-capsule', 'robot_abilities' => array('buster-charge', 'energy-boost'));
            $temp_support_robot2 = array('robot_token' => 'trill', 'robot_item' => 'energy-capsule', 'robot_abilities' => array('buster-charge', 'energy-boost'));
            if ($this_prototype_data['this_player_token'] === 'dr-light'){
                array_unshift($temp_support_robot1['robot_abilities'], 'space-shot');
                array_unshift($temp_support_robot2['robot_abilities'], 'space-shot');
                $temp_support_robot1['robot_item'] = 'speed-booster';
                $temp_support_robot1['robot_item'] = 'defense-booster';
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-wily'){
                array_unshift($temp_support_robot1['robot_abilities'], 'space-shot', 'space-buster');
                array_unshift($temp_support_robot2['robot_abilities'], 'space-shot', 'space-buster');
                $temp_support_robot1['robot_item'] = 'defense-booster';
                $temp_support_robot1['robot_item'] = 'attack-booster';
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-cossack'){
                array_unshift($temp_support_robot1['robot_abilities'], 'space-shot', 'space-buster', 'space-overdrive');
                array_unshift($temp_support_robot2['robot_abilities'], 'space-shot', 'space-buster', 'space-overdrive');
                $temp_support_robot1['robot_item'] = 'attack-booster';
                $temp_support_robot1['robot_item'] = 'speed-booster';
            }
            $temp_target_robots[] = $temp_boss_robot;
            $temp_target_robots[] = $temp_support_robot1;
            $temp_target_robots[] = $temp_support_robot2;
            $temp_target_level = $this_prototype_data['this_chapter_levels'][2] + 2;
            $temp_battle_omega = rpg_mission_fortress::generate($this_prototype_data, $temp_battle_config, $temp_target_robots, $temp_target_field, $temp_target_level);
            $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_battle_omega['battle_description'] = 'Defeat the '.ucfirst($temp_boss_robot['robot_token']).' and his '.ucfirst($temp_support_robot1['robot_token']).' handlers at the Royal Palace!';
            rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            $this_prototype_data['battle_options'][] = $temp_battle_omega;

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $get_battle_options_group_locks($this_group_name);
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
        }

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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

        // Populate the battle options with the initial eight robots combined
        if (isset($this_prototype_data['target_robot_omega'][1][0])){ $this_prototype_data['target_robot_omega'] = $this_prototype_data['target_robot_omega'][1]; }

        // Loop through the target robots and generate the battle options
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
                $temp_battle_alpha = mmrpg_prototype_extract_alpha_battle($temp_battle_omega, $this_prototype_data);
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

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        }

        // ---------------------- //
        // NEW NEW NEW NEW CH2-HU //
        // ---------------------- //

        // -- CHAPTER FOUR FORTRESS BATTLE -- //

        // GENERATE THE GENESIS TOWER BATTLE VS THE GENESIS UNIT
        // Ensure the player has completed an appropriate number of pre-battles before unlocking this one
        $group_option_locks = $get_battle_options_group_locks($this_group_name);
        $group_options_total = count($group_option_locks);
        $group_options_complete_total = array_sum($group_option_locks);
        if ($group_options_complete_total >= $group_options_total){
            $temp_battle_config = array();
            $temp_battle_config['battle_size'] = '1x4';
            $temp_battle_config['ability_rewards'] = array(array('token' => 'field-support', 'level' => 0));
            $temp_target_robots = array();
            $temp_target_robots[] = array(
                'robot_token' => 'buster-rod-g',
                'robot_item' => 'water-core',
                'robot_abilities' => array(
                    'quick-strike', 'laser-trident',
                    'swift-shot', 'water-shot',
                    )
                );
            $temp_target_robots[] = array(
                'robot_token' => 'mega-water-s',
                'robot_item' => 'wind-core',
                'robot_abilities' => array(
                    'rain-flush', 'air-twister',
                    'water-shot', 'wind-shot',
                    )
                );
            $temp_target_robots[] = array(
                'robot_token' => 'hyper-storm-h',
                'robot_item' => 'swift-core',
                'robot_abilities' => array(
                    'wind-storm', 'charge-kick',
                    'wind-shot', 'swift-shot',
                    )
                );
            if ($this_prototype_data['this_player_token'] === 'dr-light'){
                $rotate_targets = 0;
                $temp_addon_abilties = array(
                    'energy-break', 'barrier-drive'
                    );
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-wily'){
                $rotate_targets = 1;
                $temp_addon_abilties = array(
                    'energy-break', array('attack-swap', 'speed-swap', 'defense-swap'),
                    'barrier-drive',
                    );
            }
            elseif ($this_prototype_data['this_player_token'] === 'dr-cossack'){
                $rotate_targets = 2;
                $temp_addon_abilties = array(
                    'energy-break', array('speed-swap', 'defense-swap', 'attack-swap'),
                    'barrier-drive', 'shield-eater',
                    );
            }
            if (!empty($temp_addon_abilties)){
                foreach ($temp_target_robots AS $key => $info){
                    foreach ($temp_addon_abilties AS $key2 => $ability){
                        if (is_array($ability)){ $ability = isset($ability[$key]) ? $ability[$key] : $ability[array_rand($ability)]; }
                        $temp_target_robots[$key]['robot_abilities'][] = $ability;
                    }
                }
            }
            for ($i = 0; $i < $rotate_targets; $i++){
                $first = array_shift($temp_target_robots);
                $temp_target_robots[] = $first;
            }
            $temp_target_field = array('field_token' => 'genesis-tower');
            $temp_target_level = $this_prototype_data['this_chapter_levels'][3] + 4;
            $temp_battle_omega = rpg_mission_fortress::generate($this_prototype_data, $temp_battle_config, $temp_target_robots, $temp_target_field, $temp_target_level);
            $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
            $this_prototype_data['battle_options'][] = $temp_battle_omega;
            $append_last_battle_option_to_group($this_group_name);
        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $group_option_locks;
            $temp_placeholder['option_maintext'] = '';
            $temp_placeholder['option_subtext'] = '';
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
        }


    }


    // -- THE FINAL BATTLES : CHAPTER FIVE -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '4';

    // Only continue if the player has defeated the first 1 + 8 + 1 + 4 + 1 + 8 + 1 + 4 battles
    if ($this_prototype_data['prototype_complete']
        || !empty($this_prototype_data['this_chapter_unlocked']['4'])
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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

        // Final Destination I (ENKER/PUNK/BALLADE)
        // Always add the first of the final destination battles
        if (true){

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-ii';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token, true);
            $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][4];
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_mission_fortress::prepare($temp_final_option, $this_prototype_data);
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);
            $append_last_battle_option_to_group($this_group_name);

        }

        // Final Destination II (MEGA-DS/BASS-DS/PROTO-DS)
        // Only continue if the player has defeated the first final destination battle
        $group_option_locks_completed = array_sum($get_battle_options_group_locks($this_group_name));
        if ($group_option_locks_completed >= 1){

            // Unlock the first of the final destination battles
            $temp_final_option_token = $this_prototype_data['this_player_token'].'-fortress-iii';
            $temp_final_option = rpg_battle::get_index_info($temp_final_option_token, true);
            $temp_final_option['battle_phase'] = $this_prototype_data['battle_phase'];
            $temp_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][4];
            $temp_final_option['option_chapter'] = $this_prototype_data['this_current_chapter'];
            rpg_mission_fortress::prepare($temp_final_option, $this_prototype_data);
            $this_prototype_data['battle_options'][] = $temp_final_option;
            rpg_battle::update_index_info($temp_final_option['battle_token'], $temp_final_option);
            $append_last_battle_option_to_group($this_group_name);

        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $get_battle_options_group_locks($this_group_name);
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
        }

        // Final Destination III (SLUR w/ TRILL SUPPORT)
        // Only continue if the player has defeated the first and second final destination battles
        $group_option_locks_completed = array_sum($get_battle_options_group_locks($this_group_name));
        if ($group_option_locks_completed >= 2){

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
                if ($this_prototype_player_data['player_number'] >= 1){ $final_boss_support_info['robot_abilities'][] = 'astro-crush'; }
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

                // Check to see if this is the actual final battle for this chapter and should count for completion
                if ($this_prototype_player_data['player_token'] === 'dr-light'){ $temp_final_option['battle_counts'] = true; }
                elseif ($this_prototype_player_data['player_token'] === 'dr-wily'){ $temp_final_option['battle_counts'] = true; }
                elseif ($this_prototype_player_data['player_token'] === 'dr-cossack'){ $temp_final_option['battle_counts'] = false; }
                //error_log($temp_final_option['battle_token'].' // battle_counts = '.($temp_final_option['battle_counts'] ? 'true' : 'false'));

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
                    $temp_before_final_option['battle_round'] = 0;
                    $temp_before_final_option['battle_level'] = $this_prototype_data['this_chapter_levels'][6];
                    $temp_before_final_option['battle_description'] = 'Defeat the army of robot master clones augmented with darkness energy!';

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
                            array('energy-tank', 'weapon-tank', 'attack-diverter', 'defense-diverter', 'speed-diverter')
                            );
                    }
                    if ($item_tier >= 3){
                        $item_options = array_merge($item_options,
                            array('energy-upgrade', 'weapon-upgrade', 'super-capsule')
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
                        $index_plus_info = array_merge($index, $info);
                        $info['robot_abilities'] = array();
                        $info['robot_abilities'] = mmrpg_prototype_generate_abilities($index_plus_info, $info['robot_level'], 8);
                        $temp_before_final_option['battle_target_player']['player_robots'][] = $info;
                    }

                    // Add the mechas info into the omega battle
                    $temp_before_final_option['battle_field_base']['field_music'] = 'sega-remix/wily-fortress-2-mm9';
                    $temp_before_final_option['battle_field_base']['field_mechas'] = $temp_support_mechas;
                    shuffle($temp_before_final_option['battle_target_player']['player_robots']);

                    // Check to see if this is the actual final battle for this chapter and should count for completion
                    $temp_before_final_option['battle_counts'] = false; // this is a before battle, it should never count
                    //error_log($temp_before_final_option['battle_token'].' // battle_counts = '.($temp_before_final_option['battle_counts'] ? 'true' : 'false'));

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
                        $temp_after_final_option['battle_round'] = 2;
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
                        $real_final_boss_info['robot_abilities'] = array('space-overdrive', 'laser-shot', 'shield-shot', 'buster-charge');
                        $temp_addon_abilties = array('freeze-buster', 'flame-buster', 'water-buster', 'electric-buster');
                        if (!empty($temp_addon_abilties)){ $real_final_boss_info['robot_abilities'] = array_merge($real_final_boss_info['robot_abilities'], $temp_addon_abilties); }
                        $temp_after_final_option['battle_target_player']['player_robots'][] = $real_final_boss_info;

                        // Define the details for the real_final boss's minion "trill" and append them to the array
                        $final_boss_support_token = 'trille-bot';
                        $final_boss_support_image = 'trille-bot_alt6';
                        $final_boss_support_count = 2;
                        $final_boss_support_info = array('robot_token' => $final_boss_support_token, 'robot_image' => $final_boss_support_image);
                        $final_boss_support_info['counters'] = array();
                        $final_boss_support_info['counters'][$this_player_stat_token.'_mods'] = 2;
                        $final_boss_support_info['robot_level'] = $real_final_boss_info['robot_level'] - 5;
                        $final_boss_support_info['robot_item'] = 'space-shard';
                        $final_boss_support_info['robot_abilities'] = array(
                            'space-shot', 'space-buster', 'energy-assault', 'buster-charge',
                            'energy-boost', 'attack-boost', 'defense-boost', 'speed-boost'
                            );
                        for ($i = 0; $i < $final_boss_support_count; $i++){
                            $temp_after_final_option['battle_target_player']['player_robots'][] = $final_boss_support_info;
                            }

                        // Check to see if this is the actual final battle for this chapter and should count for completion
                        $temp_after_final_option['battle_counts'] = true; // this is only ever a final battle, it should always count
                        //error_log($temp_after_final_option['battle_token'].' // battle_counts = '.($temp_after_final_option['battle_counts'] ? 'true' : 'false'));

                        // Prepare the final battle details, add it to the index and/or buttons, and then queue it up
                        rpg_mission_fortress::prepare($temp_after_final_option, $this_prototype_data);
                        //$this_prototype_data['battle_options'][] = $temp_after_final_option;
                        rpg_battle::update_index_info($temp_after_final_option['battle_token'], $temp_after_final_option);
                        mmrpg_prototype_mission_autoplay_append($temp_final_option, $temp_after_final_option, $this_prototype_data, true);



                    }

                }

            }

            // Add this battle and it's status to the appropriate group array
            $append_last_battle_option_to_group($this_group_name);

        } else {
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = $get_battle_options_group_locks($this_group_name);
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);
        }


    }


    // -- BONUS CHAPTER : STAR FIELDS (7) -- //

    // Update the prototype data's global current chapter variable
    $this_prototype_data['this_current_chapter'] = '7';

    // Only continue if the player has unlocked this extra chapter
    if ($this_prototype_data['prototype_complete'] || $this_prototype_data['this_chapter_unlocked']['7']){

        // EVENT MESSAGE : BONUS CHAPTER
        $next_key = count($this_prototype_data['battle_options']);
        $this_prototype_data['battle_options'][] = array(
            'option_type' => 'message',
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'option_maintext' => 'Bonus Chapter : Star Fields'
            );

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

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

        // If we have a stars remaining to collect, append it to the maintext
        if (!empty($num_remaining_stars)){
            $append_remaining = ''.$num_remaining_stars.' <i class="fa fa-fw fa-star"></i>';
            $this_prototype_data['battle_options'][$next_key]['option_maintext'] .= (!empty($num_remaining_stars) ? ' <em>'.$append_remaining.'</em>' : '');
        }

        // Calculate the current starforce total vs max starforce total for mission gen
        $current_starforce = !empty($_SESSION[$session_token]['values']['star_force']) ? $_SESSION[$session_token]['values']['star_force'] : array();
        $this_prototype_data['current_starforce_total'] = !empty($current_starforce) ? array_sum($current_starforce) : 0;
        $this_prototype_data['max_starforce'] = $max_star_force;
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
        $random_encounter_chance = false;
        if ($star_count >= 10){
            for ($i = 0; $i < 10; $i++){
                if (mt_rand(0, MMRPG_SETTINGS_STARFORCE_STARTOTAL) <= $star_count){
                    $random_encounter_chance = true;
                    break;
                }
            }
        }
        //error_log('home//$random_encounter_added: '.print_r(($random_encounter_added ? 'true' : 'false'), true));
        //error_log('home//$random_encounter_chance: '.print_r($random_encounter_chance ? 'true' : 'false', true));

        // Loop through remaining stars and display the first twelve
        $added_star_fields = 0;
        foreach ($visible_star_fields AS $key => $star_token){
            $star_info = $possible_star_list[$star_token];

            // Collect references to the two stages' info
            $info = $star_info['info1'];
            $info2 = $star_info['info2'];
            $field_info = $mmrpg_index_fields[$info['field']];
            $field_info2 = !empty($info2) ? $mmrpg_index_fields[$info2['field']] : $field_info;

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

            // SUPERBOSS STARTDROIDS (+ SUNSTAR) : RANDOM ENCOUNTERS
            // If random encounter has not been added, check to see if we can add now
            if ($random_encounter_chance && empty($random_encounter_added)){
                $random_encounter_added = mmrpg_prototype_append_stardroid_encounter_data($this_prototype_data, $temp_battle_omega, $field_info, $field_info2);
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
            $next_key = count($this_prototype_data['battle_options']);
            $this_prototype_data['battle_options'][] = array(
                'option_type' => 'message',
                'option_chapter' => $this_prototype_data['this_current_chapter'],
                'option_maintext' => 'Bonus Chapter : Player Battles'
                );

            // Define an array to keep track of group option progress
            $this_group_name = $new_group_name();
            $new_battle_options_group($this_group_name);

            // Include the leaderboard data for pruning
            $this_leaderboard_online_players = mmrpg_prototype_leaderboard_online();
            $temp_include_usernames = array();
            if (!empty($this_leaderboard_online_players)){
                foreach ($this_leaderboard_online_players AS $info){ $temp_include_usernames[] = $info['token']; }
            }

            // Pull a random set of players from the database with similar point levels
            $temp_self_data = array();
            $temp_player_list = mmrpg_prototype_leaderboard_targets($this_userid, $this_prototype_data['target_player_token'], $defeated, $temp_self_data);
            if (empty($temp_player_list)){ $temp_player_list = mmrpg_prototype_leaderboard_targets($this_userid, $this_prototype_data['this_player_token'], $defeated, $temp_self_data); }
            //error_log('$temp_self_data = '.print_r($temp_self_data, true));
            //error_log('$temp_player_list (count) = '.print_r(count($temp_player_list), true));

            // If we have a leaderboard target count, append it to the maintext
            $player_targets_remaining = 0;
            if (!empty($_SESSION['LEADERBOARD']['player_targets_remaining'])){
                $player_targets_remaining = $_SESSION['LEADERBOARD']['player_targets_remaining'];
                $append_remaining = ''.$player_targets_remaining.' <i class="fa fa-fw fa-stop-circle"></i>';
                $this_prototype_data['battle_options'][$next_key]['option_maintext'] .= (!empty($player_targets_remaining) ? ' <em>'.$append_remaining.'</em>' : '');
            }

            // If we have a leaderboard target count, append it to the maintext
            $player_rematches_remaining = 0;
            if (!empty($_SESSION['LEADERBOARD']['player_rematches_remaining'])){
                $player_rematches_remaining = $_SESSION['LEADERBOARD']['player_rematches_remaining'];
                $append_remaining = ''.$player_rematches_remaining.' <i class="fas fa-history"></i>';
                $this_prototype_data['battle_options'][$next_key]['option_maintext'] .= (!empty($player_rematches_remaining) ? ' <em>'.$append_remaining.'</em>' : '');
            }

            // If player data was actuall pulled, continue
            $max_battle_count = 6;
            $max_target_count = 6;
            if (!empty($temp_player_list)){

                // Shuffle the player list
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

                // Loop through and generate mission buttons for at least the first X players in the list
                for ($i = 0; $i < $max_battle_count; $i++){

                    // If there are no more players, break
                    if (empty($temp_player_list)){ break; }

                    // Pull and random player from the list and collect their full data
                    $temp_player_array = array_shift($temp_player_list);
                    $temp_battle_omega = rpg_mission_player::generate($this_prototype_data, $temp_player_array, $max_target_count, 100, $temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);

                    // If the collected omega battle was empty, continue gracefully
                    if (empty($temp_battle_omega) || empty($temp_battle_omega['battle_token'])){ continue; }

                    // Update the option chapter to the current
                    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

                    // If this user is online, update the battle button with details
                    if (!empty($temp_player_array['values']['flag_online'])){
                        $temp_battle_omega['option_style'] = 'border-color: green !important; ';
                        $temp_battle_omega['battle_description2'] .= 'This player was recently online! ';
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="online_type player_type player_type_nature">Online</sup>';
                    }

                    // If this user is defeated, update the battle button with details
                    $victory_token_colour = !empty($temp_player_array['values']['colour_token']) ? $temp_player_array['values']['colour_token'] : 'none';
                    if (!empty($temp_player_array['values']['colour_token2'])){ $victory_token_colour .= '_'.$temp_player_array['values']['colour_token2']; }
                    if (!empty($temp_player_array['values']['flag_defeated'])){
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="special_type player_type player_type_'.$victory_token_colour.'"><i class="fa fas fa-history"></i></sup>';
                        $temp_battle_omega['battle_description'] = 'Rematch! '.$temp_battle_omega['battle_description'];
                        $temp_battle_omega['battle_description2'] .= 'This player\'s victory token has already been collected, but they have yours too!  Defeat them again to take it back and show them who\'s boss. ';
                        $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * 0.10);
                    } else {
                        $temp_battle_omega['battle_button'] = (!empty($temp_battle_omega['battle_button']) ? $temp_battle_omega['battle_button'] : $temp_battle_omega['battle_name']).' <sup class="special_type player_type player_type_'.$victory_token_colour.'"><i class="fa fa-fw fa-stop-circle"></i></sup>';
                        $temp_battle_omega['battle_description2'] .= 'Collect this player\'s victory token for additional battle points!';
                    }

                    // Add the omega battle to the options, index, and session
                    $this_prototype_data['battle_options'][] = $temp_battle_omega;
                    rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                    unset($temp_battle_omega);

                }

            }

            // If data for the current player was successfully pulled, add a button
            if (!empty($temp_self_data)){

                // Pull and random player from the list and collect their full data
                $temp_player_array = $temp_self_data;
                $temp_battle_omega = rpg_mission_player::generate($this_prototype_data, $temp_player_array, $max_target_count, 100, $temp_field_factors_one, $temp_field_factors_two, $temp_field_factors_three);
                if (!empty($temp_battle_omega)
                    && !empty($temp_battle_omega['battle_token'])){

                    // Update the option chapter to the current
                    $temp_battle_omega['battle_token'] = $this_prototype_data['this_player_token'].'-proxy-battle';
                    $temp_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];
                    $temp_battle_omega['battle_button'] .= ' (?)';
                    $temp_battle_omega['battle_button_append'] = '<i class="fa fas fa-mask"></i>';
                    $temp_battle_omega['battle_description2'] .= 'Wait a minute… that\'s you!  Who is this imposter and what do they want with our heroes? Let\'s jump in and find out!';
                    $temp_battle_omega['battle_zenny'] = ceil($temp_battle_omega['battle_zenny'] * 0.10);

                    // Add the omega battle to the options, index, and session
                    $this_prototype_data['battle_options'][] = $temp_battle_omega;
                    rpg_battle::update_index_info($temp_battle_omega['battle_token'], $temp_battle_omega);
                    unset($temp_battle_omega);

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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

        // Generate the mecha bonus battle and using the prototype data
        $mecha_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 8, 'mecha');
        $mecha_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

        // Generate the master bonus battle and using the prototype data
        $master_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 7, 'master');
        $master_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

        // Generate the boss bonus battle and using the prototype data
        $boss_battle_omega = rpg_mission_bonus::generate($this_prototype_data, 6, 'boss');
        $boss_battle_omega['option_chapter'] = $this_prototype_data['this_current_chapter'];

        // Add the omega battles to the options, index, and session
        $mecha_battle_omega['battle_option_key'] = count($this_prototype_data['battle_options']);
        $master_battle_omega['battle_option_key'] = $mecha_battle_omega['battle_option_key'] + 1;
        $boss_battle_omega['battle_option_key'] = $master_battle_omega['battle_option_key'] + 1;
        $this_prototype_data['battle_options'][] = $mecha_battle_omega;
        $this_prototype_data['battle_options'][] = $master_battle_omega;
        $this_prototype_data['battle_options'][] = $boss_battle_omega;
        rpg_battle::update_index_info($mecha_battle_omega['battle_token'], $mecha_battle_omega);
        rpg_battle::update_index_info($master_battle_omega['battle_token'], $master_battle_omega);
        rpg_battle::update_index_info($boss_battle_omega['battle_token'], $boss_battle_omega);

        //$base_battle_omega['battle_complete_redirect_token'] = $append_battle_omega['battle_token'];

        // Check to see if we can add a miniboss battle after the master one
        $miniboss_battle_unlocked = false;
        if (!empty($mecha_battle_omega['battle_field_base']['field_multipliers'])){
            $field_multipliers = $mecha_battle_omega['battle_field_base']['field_multipliers'];
            if (!empty($field_multipliers['copy'])){ $miniboss_battle_unlocked = true; }
        }

        // Check to see if we can add a superboss battle after the final one
        $hunter_encounter_data = mmrpg_prototype_hunter_encounter_data();
        $superboss_battle_unlocked = false;
        $required_target_tokens = $hunter_encounter_data['required_target_tokens'];
        $required_targets_total = count($required_target_tokens);
        $required_targets_visible = 0;
        if (!empty($boss_battle_omega['battle_target_player']['player_robots'])){
            foreach ($boss_battle_omega['battle_target_player']['player_robots'] AS $robot_info){
                if (in_array($robot_info['robot_token'], $required_target_tokens)){ $required_targets_visible++; }
            }
            if ($required_targets_visible >= $required_targets_total){
                $superboss_battle_unlocked = true;
            }
        }

        // MINIBOSS ARCHIVIST : RANDOM ENCOUNTER
        // If the miniboss enounter has been unlocked, we can add it after the first battle
        if ($miniboss_battle_unlocked){
            $random_encounter_added = mmrpg_prototype_append_archivist_encounter_data($this_prototype_data, $mecha_battle_omega);
        }

        // SUPERBOSS QUINT : RANDOM ENCOUNTER
        // If the superboss enounter has been unlocked, we can add it after the last battle
        if ($superboss_battle_unlocked){
            $random_encounter_added = mmrpg_prototype_append_hunter_encounter_data($this_prototype_data, $boss_battle_omega);
        }

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

        // Define an array to keep track of group option progress
        $this_group_name = $new_group_name();
        $new_battle_options_group($this_group_name);

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
                if (empty($temp_battle_omega) || empty($temp_battle_omega['values'])){ continue; }
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




        // -- ENDLESS ATTACK MODE LOGISTICS -- //

        // Check to see if there are already any ENDLESS ATTACK MODE sessions in progress
        $endless_attack_savedata = mmrpg_prototype_get_endless_sessions();

        // Always some kind of final button for the ENDLESS ATTACK MODE challenge mission
        // Check to see if the user has any ACTIVE ENDLESS SAVEDATE before generating a new one
        $player_token = $this_prototype_data['this_player_token'];
        //error_log('Checking for ENDLESS_MODE_SAVEDATA '.print_r(array_keys($endless_attack_savedata), true));
        if (!empty($endless_attack_savedata)
            && !empty($endless_attack_savedata[$player_token])){

            // We already have existing save data for this player, so let's just add the link
            //error_log('We already have endless savedata for '.$player_token);
            $temp_endless_saveinfo = $endless_attack_savedata[$player_token];
            //error_log('$temp_endless_saveinfo: '.print_r($temp_endless_saveinfo, true));
            //error_log('$temp_endless_saveinfo: '.print_r(array_keys($temp_endless_saveinfo), true));
            $temp_battle_sigma = $temp_endless_saveinfo['battle'];
            $temp_battle_sigma['option_target_href'] = $temp_endless_saveinfo['redirect'];
            rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);
            $temp_battle_sigma['battle_button_append'] = '<i class="fa fas fa-infinity"></i>';
            $this_prototype_data['battle_options'][] = $temp_battle_sigma;


        } elseif (!empty($endless_attack_savedata)){

            // We have save data but it's for another player, so just add a placeholder instead
            $other_player_token = array_keys($endless_attack_savedata)[0];
            $other_player_info = rpg_player::get_index_info($other_player_token);
            $this_player_info = rpg_player::get_index_info($player_token);
            //error_log('We already have endless savedata for another player ('.$other_player_token.')');
            $temp_placeholder = array();
            $temp_placeholder['option_type'] = 'placeholder';
            $temp_placeholder['option_chapter'] = $this_prototype_data['this_current_chapter'];
            $temp_placeholder['option_locks'] = array(0 => false);
            $temp_placeholder['option_pseudo_token'] = 'endless-mission-placeholder';
            $temp_placeholder['option_pseudo_type'] = str_replace('dr-', '', $other_player_token);
            $temp_placeholder['option_click_tooltip'] = '&laquo; Endless Attack Mode &raquo; ';
            $temp_placeholder['option_click_tooltip'] .= '|| '.$other_player_info['player_name'].' is currently exploring this area! ';
            $temp_placeholder['option_click_tooltip'] .= '|| Bring him home if you want to start a new run with '.$this_player_info['player_name'].'!';
            $temp_placeholder['battle_button_append'] = '<i class="fa fas fa-ban"></i>';
            $this_prototype_data['battle_options'][] = $temp_placeholder;
            $append_faux_battle_option_to_group($this_group_name);


        } else {

            // We can generate totally new save data as there aren't any stored sessions right now
            //error_log('We can generate NEW endless savedata for '.$player_token);
            $temp_battle_sigma = rpg_mission_endless::generate_endless_mission($this_prototype_data, 1);
            rpg_battle::update_index_info($temp_battle_sigma['battle_token'], $temp_battle_sigma);
            $temp_battle_sigma['battle_button_append'] = '<i class="fa fas fa-infinity"></i>';
            $this_prototype_data['battle_options'][] = $temp_battle_sigma;

        }

    }


}

?>