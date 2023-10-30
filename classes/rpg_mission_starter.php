<?php
/**
 * Mega Man RPG Starter-Battle Mission
 * <p>The starter mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_starter extends rpg_mission {

    // Define a function for generating the STARTER missions
    public static function generate_intro($this_prototype_data, $this_robot_token = 'met', $this_start_level = 1, $this_rescue_token = 'roll', $this_intro_field = ''){

        // Pull in global variables for this function
        global $db;
        global $this_omega_factors_one;
        global $this_omega_factors_two;
        global $this_omega_factors_three;
        global $this_omega_factors_four;
        global $this_omega_factors_five;
        global $this_omega_factors_six;
        global $this_omega_factors_seven;
        global $this_omega_factors_eight;
        global $this_omega_factors_eight_two;
        global $this_omega_factors_nine;
        global $this_omega_factors_ten;
        global $this_omega_factors_eleven;

        // Collect data on this robot and the rescue robot
        //$db_robot_fields = rpg_robot::get_index_fields(true);
        //$this_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        //$this_robot_data = rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
        if (strstr($this_robot_token, '/')){ list($this_robot_token, $this_robot_alt) = explode('/', $this_robot_token); }
        $this_robot_data = rpg_robot::get_index_info($this_robot_token);
        $this_robot_name = $this_robot_data['robot_name'];
        if (empty($this_intro_field)){ $this_intro_field = rpg_player::get_intro_field($this_prototype_data['this_player_token']); }
        $intro_field_data = rpg_field::get_index_info($this_intro_field);
        // Populate the battle options with the starter battle option
        $temp_target_count = 1;
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_player_robots_unlocked = mmrpg_prototype_robots_unlocked($this_prototype_data['this_player_token']);
        $temp_battle_omega = array();
        $temp_battle_omega['battle_field_base']['field_id'] = 100;
        $temp_battle_omega['battle_field_base']['field_token'] = $this_intro_field;
        $temp_battle_omega['flags']['starter_battle'] = true;
        $temp_battle_omega['battle_token'] = $temp_battle_token;
        $temp_battle_omega['battle_size'] = '1x4';
        $temp_battle_omega_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        //if (!empty($temp_battle_omega_complete)){ $temp_target_count = 1 + $temp_battle_omega_complete; }
        if ($temp_player_robots_unlocked > 2){ $temp_target_count = 1 + ($temp_player_robots_unlocked - 2); }
        if ($temp_target_count > 8){ $temp_target_count = 8; }
        $temp_battle_omega['battle_level'] = $this_start_level;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_name'] = 'Chapter One Intro Battle';
        //$temp_battle_omega['battle_name'] = $this_robot_name.($temp_target_count > 1 ? 's' : '');
        //$temp_battle_omega['battle_name'] = $this_robot_name.($temp_target_count > 1 ? 's' : '').' Battle';
        $temp_battle_omega['battle_turns'] = 1;
        $temp_battle_omega['battle_zenny'] = 1;
        //$temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);
        $temp_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
        $temp_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';
        $temp_battle_omega['battle_target_player']['player_robots'][0] = array('robot_id' => rpg_game::unique_robot_id($temp_player_id, $this_robot_data['robot_id'], 0), 'robot_token' => $this_robot_token);
        $temp_mook_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_name_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $temp_mook_tokens = array();
        /// Loop through and add other robots to the battle
        for ($i = 0; $i < $temp_target_count; $i++){
            $temp_clone_robot = $temp_mook_robot;
            $temp_clone_robot['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $this_robot_data['robot_id'], ($i + 1));
            $temp_clone_robot['robot_level'] = $this_start_level;
            $temp_clone_robot['robot_token'] = $this_robot_token;
            $temp_robot_name = $this_robot_name;
            $temp_robot_name_token = $temp_clone_robot['robot_name_token'] = str_replace(' ', '-', strtolower($temp_robot_name));
            if (!isset($temp_mook_tokens[$temp_robot_name_token])){ $temp_mook_tokens[$temp_robot_name_token] = 0; }
            else { $temp_mook_tokens[$temp_robot_name_token]++; }
            if ($temp_target_count > 1){ $temp_clone_robot['robot_name'] = $temp_robot_name.' '.$temp_name_index[$temp_mook_tokens[$temp_robot_name_token]]; }
            else { $temp_clone_robot['robot_name'] = $temp_robot_name; }
            $temp_battle_omega['battle_target_player']['player_robots'][] = $temp_clone_robot;
        }
        // Remove any uncessesary A's from the robots' names
        foreach ($temp_battle_omega['battle_target_player']['player_robots'] AS $key => $info){
            if (!isset($info['robot_name_token'])){ continue; }
            if (isset($temp_mook_tokens[$info['robot_name_token']]) && $temp_mook_tokens[$info['robot_name_token']] == 0){
                $temp_battle_omega['battle_target_player']['player_robots'][$key]['robot_name'] = str_replace(' A', '', $info['robot_name']);
            }
        }

        // If the rescure robot has not yet been unlocked as a playable character, show it in the background
        $rescue_robot_unlockable = false;

        // Allow unlocking of the mecha support ability if the player has reached max targets
        if ($temp_target_count > 1){
            // Add the Mecha Support ability as an unlockable move if not already unlocked
            $temp_battle_omega['battle_rewards']['abilities'] = array();
            if ($temp_target_count >= 8
                && $this_prototype_data['this_player_token'] == 'dr-light'
                && !mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'mecha-support')){
                // Add the ability as a reward for the battle
                $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'mecha-support');
                // Update the description text for the battle
                $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' and download '.($temp_target_count > 1 ? 'their' : 'its').' secret mecha data! &#10023; ';
            }
            elseif ($temp_target_count >= 8
                && $this_prototype_data['this_player_token'] == 'dr-wily'
                && !mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'field-support')){
                // Add the ability as a reward for the battle
                $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'field-support');
                // Update the description text for the battle
                $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' and download '.($temp_target_count > 1 ? 'their' : 'its').' secret field data! &#10022; ';
            }
            elseif ($temp_target_count >= 8
                && $this_prototype_data['this_player_token'] == 'dr-cossack'
                && !mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'friend-share')){
                // Add the ability as a reward for the battle
                $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'friend-share');
                // Update the description text for the battle
                $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's' : '').' and download '.($temp_target_count > 1 ? 'their' : 'its').' secret friend data! &#10022; ';
            } else {
                // Update the description text for the battle
                $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's that are currently attacking' : ' that has suddenly attacked').'!';
            }
        }
        // Otherwise, if the player has already unlocked the rescure bot
        else {
            // Update the description text for the battle
            $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.($temp_target_count > 1 ? 's that are currently attacking' : ' that has suddenly attacked').'!';
        }

        // If the rescue robot is here, add some ambiguous text to the description
        if ($rescue_robot_unlockable){
            //$temp_battle_omega['battle_description'] = str_replace('!', ' and rescue the support robot that\'s appeared on the field!', $temp_battle_omega['battle_description']);
            $temp_battle_omega['battle_description2'] = ' Wait a minute... who\'s that in the background?';
        }

        // Add some random item drops to the starter battle
        if ($temp_target_count > 8){
            $temp_battle_omega['battle_rewards']['items'] = array(
                // Add an item as a reward for the battle (doesn't work yet but will someday!)
                array('token' => ($temp_battle_omega_complete % 2 === 0 ? 'energy-tank' : 'weapon-tank'))
                );
        } else {
            $temp_battle_omega['battle_rewards']['items'] = array(
                // Nothing special the first time around
                );
        }

        // Recalculate REWARD ZENNY and ALLOWED TURNS for this battle
        rpg_mission::calculate_mission_zenny_and_turns($temp_battle_omega, $this_prototype_data, $this_start_level);

        // Return the generated omega battle data
        return $temp_battle_omega;

    }

    // Define a function for generating the STARTER missions
    public static function generate_midboss($this_prototype_data, $this_robot_token = 'sniper-joe', $this_start_level = 1, $this_rescue_token = 'roll', $this_midboss_field = ''){

        // Pull in global variables for this function
        global $db;
        global $this_omega_factors_one;
        global $this_omega_factors_two;
        global $this_omega_factors_three;
        global $this_omega_factors_four;
        global $this_omega_factors_five;
        global $this_omega_factors_six;
        global $this_omega_factors_seven;
        global $this_omega_factors_eight;
        global $this_omega_factors_eight_two;
        global $this_omega_factors_nine;
        global $this_omega_factors_ten;
        global $this_omega_factors_eleven;

        // Collect data on this robot and the rescue robot
        //$db_robot_fields = rpg_robot::get_index_fields(true);
        //$this_robot_index = $db->get_array_list("SELECT {$db_robot_fields} FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        //$this_robot_data = rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
        if (strstr($this_robot_token, '/')){ list($this_robot_token, $this_robot_alt) = explode('/', $this_robot_token); }
        $this_robot_data = rpg_robot::get_index_info($this_robot_token);
        $this_robot_name = $this_robot_data['robot_name'];
        if (empty($this_midboss_field)){ $this_midboss_field = rpg_player::get_intro_field($this_prototype_data['this_player_token']); }
        $midboss_field_data = rpg_field::get_index_info($this_midboss_field);
        // Populate the battle options with the starter battle option
        $temp_target_count = 1;
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_battle_omega = array();
        $temp_battle_omega['battle_field_base']['field_id'] = 100;
        $temp_battle_omega['battle_field_base']['field_token'] = $this_midboss_field;
        $temp_battle_omega['flags']['starter_battle'] = true;
        $temp_battle_omega['battle_token'] = $temp_battle_token;
        $temp_battle_omega['battle_size'] = '1x4';
        $temp_battle_omega_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        $temp_battle_omega['battle_level'] = $this_start_level;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_name'] = 'Chapter One Midboss Battle';
        $temp_battle_omega['battle_turns'] = 1;
        $temp_battle_omega['battle_zenny'] = 1;
        //$temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);
        $temp_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
        $temp_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_midboss_level = $this_start_level;
        $temp_midboss_robot = array(
            'robot_token' => $this_robot_token,
            'robot_id' => rpg_game::unique_robot_id($temp_player_id, $this_robot_data['robot_id'], 0),
            'robot_level' => $temp_midboss_level,
            'robot_abilities' => !empty($this_robot_data['robot_rewards']['abilities']) ? array_map(function($a){ return $a['token']; }, $this_robot_data['robot_rewards']['abilities']) : array('buster-shot')
            );
        $temp_battle_omega['battle_target_player']['player_robots'][0] = $temp_midboss_robot;

        // If the rescure robot has not yet been unlocked as a playable character, show it in the background
        $rescue_robot_unlockable = false;
        if (!empty($this_rescue_token)
            && !mmrpg_prototype_robot_unlocked(false, $this_rescue_token)){

            // Define the rescue robot's level and display properties depending on who it is
            $rescue_robot_size = 40;
            $rescue_robot_unlockable = true;
            $rescue_robot_level = $temp_midboss_level;
            $rescue_robot_frame = array(8,0,8,0,0);
            $rescue_robot_position = array('x' => 354, 'y' => 118, 'direction' => 'left');
            if ($this_rescue_token === 'roll'){
                $rescue_robot_frame = array(10, 10, 8, 10, 10, 0, 10, 10, 8, 0, 8, 8);
                $rescue_robot_position = array('x' => 192, 'y' => 128, 'direction' => 'left');
            }
            elseif ($this_rescue_token === 'disco'){
                $rescue_robot_frame = array(0,1,2,1,2,10);
                $rescue_robot_position = array('x' => 120, 'y' => 161, 'direction' => 'left');
            }
            elseif ($this_rescue_token === 'rhythm'){
                $rescue_robot_frame = array(0,6,2,6,2,10);
                $rescue_robot_position = array('x' => 212, 'y' => 173, 'direction' => 'left');
            }

            // Add the rescue robot to the background with animation
            if (empty($temp_battle_omega['battle_field_base']['field_background_attachments']) && !empty($midboss_field_data['field_background_attachments'])){ $temp_battle_omega['battle_field_base']['field_background_attachments'] = $midboss_field_data['field_background_attachments']; }
            $temp_battle_omega['battle_field_base']['field_background_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => $rescue_robot_size, 'offset_x' => $rescue_robot_position['x'], 'offset_y' => $rescue_robot_position['y'], 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => $rescue_robot_frame, 'robot_direction' => $rescue_robot_position['direction']);

            // Add the rescue robot to the list of unlockables
            $temp_battle_omega['battle_rewards']['robots'] = array();
            $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $this_rescue_token, 'level' => $rescue_robot_level, 'experience' => 999);

        }

        // Update the description text for the battle
        $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.' guarding the '.explode(' ', strtolower($midboss_field_data['field_name']))[1].'\'s entrance!';

        // If the rescue robot is here, add some ambiguous text to the description
        if ($rescue_robot_unlockable){
            //$temp_battle_omega['battle_description'] = str_replace('!', ' and rescue the support robot that\'s appeared on the field!', $temp_battle_omega['battle_description']);
            $temp_battle_omega['battle_description2'] = ' Wait a minute... who\'s that in the background?';
        }

        // Add some random item drops to the midboss battle
        $temp_battle_omega['battle_rewards']['items'] = array(
            // Nothing special to drop FOR NOW
            );

        // Recalculate REWARD ZENNY and ALLOWED TURNS for this battle
        rpg_mission::calculate_mission_zenny_and_turns($temp_battle_omega, $this_prototype_data, $this_start_level);

        // Return the generated omega battle data
        return $temp_battle_omega;

    }

    // Define a function for generating the CHAPTER ONE BOSS missions
    public static function generate_boss($this_prototype_data, $this_robot_token = 'trill', $this_start_level = 1, $this_rescue_token = '', $this_boss_field = ''){

        // Pull in global variables for this function
        global $db;
        global $this_omega_factors_one;
        global $this_omega_factors_two;
        global $this_omega_factors_three;
        global $this_omega_factors_four;
        global $this_omega_factors_five;
        global $this_omega_factors_six;
        global $this_omega_factors_seven;
        global $this_omega_factors_eight;
        global $this_omega_factors_eight_two;
        global $this_omega_factors_nine;
        global $this_omega_factors_ten;
        global $this_omega_factors_eleven;

        // Collect data on this robot and the rescue robot
        $this_player_data = rpg_player::get_index_info($this_prototype_data['this_player_token']);
        if (strstr($this_robot_token, '/')){ list($this_robot_token, $this_robot_alt) = explode('/', $this_robot_token); }
        $this_robot_data = rpg_robot::get_index_info($this_robot_token);
        $this_robot_name = $this_robot_data['robot_name'];
        if (empty($this_boss_field)){ $this_boss_field = rpg_player::get_intro_field($this_prototype_data['this_player_token']); }
        $boss_field_data = rpg_field::get_index_info($this_boss_field);

        // Populate the battle options with the starter battle option
        $temp_target_count = 1;
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $temp_battle_omega = array();
        $temp_battle_omega['battle_field_base']['field_id'] = 100;
        $temp_battle_omega['battle_field_base']['field_token'] = $this_boss_field;
        $temp_battle_omega['battle_field_base']['field_background_variant'] = $this_prototype_data['next_player_token'];
        $temp_battle_omega['flags']['starter_battle'] = true;
        $temp_battle_omega['battle_token'] = $temp_battle_token;
        $temp_battle_omega['battle_size'] = '1x4';
        $temp_battle_omega_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        $temp_battle_omega['battle_level'] = $this_start_level;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_name'] = 'Chapter One Boss Battle';
        $temp_battle_omega['battle_turns'] = 1;
        $temp_battle_omega['battle_zenny'] = 1;
        //$temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);
        $temp_battle_omega['battle_target_player']['user_id'] = $temp_user_id;
        $temp_battle_omega['battle_target_player']['player_id'] = $temp_player_id;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_boss_level = $this_start_level;
        $temp_boss_robot = array(
            'robot_token' => $this_robot_token,
            'robot_id' => rpg_game::unique_robot_id($temp_player_id, $this_robot_data['robot_id'], 0),
            'robot_level' => $temp_boss_level,
            'robot_abilities' => !empty($this_robot_data['robot_rewards']['abilities']) ? array_map(function($a){ return $a['token']; }, $this_robot_data['robot_rewards']['abilities']) : array('buster-shot')
            );

        $temp_stat_priority = array('attack', 'defense', 'speed');
        $temp_stat_alts = array('attack' => '_alt', 'defense' => '_alt2', 'speed' => '_alt3');
        if (!empty($this_robot_alt)){
            if ($this_robot_token === 'trill'){
                $temp_boss_image = $this_robot_token;
                $temp_boss_item = 'space-shard';
                //$temp_boss_abilities = array('space-shot', 'space-buster', 'space-overdrive', 'energy-boost');
                $temp_boss_abilities = array('energy-boost');
                if ($this_player_data['player_number'] >= 1){ $temp_boss_abilities[] = 'space-overdrive'; }
                if ($this_player_data['player_number'] >= 2){ $temp_boss_abilities[] = 'space-buster'; }
                if ($this_player_data['player_number'] >= 3){ $temp_boss_abilities[] = 'space-shot'; }
                if ($this_robot_alt === 'attack'){
                    $temp_stat_priority = array('attack', 'defense', 'speed');
                } elseif ($this_robot_alt === 'defense'){
                    $temp_stat_priority = array('defense', 'speed', 'attack');
                } elseif ($this_robot_alt === 'speed'){
                    $temp_stat_priority = array('speed', 'attack', 'defense');
                }
                $temp_boss_abilities = array_merge(
                    array($temp_stat_priority[0].'-boost', $temp_stat_priority[0].'-break', $temp_stat_priority[0].'-mode', 'buster-charge'),
                    $temp_boss_abilities
                    );
                $temp_boss_robot['counters'][$temp_stat_priority[0]] = 1;
                $temp_boss_robot['robot_image'] = $temp_boss_image;
                $temp_boss_robot['robot_item'] = $temp_boss_item;
                $temp_boss_robot['robot_abilities'] = $temp_boss_abilities;
            } else {
                $temp_boss_image = $this_robot_token.'_'.$this_robot_alt;
                $temp_boss_robot['robot_image'] = $temp_boss_image;
            }
        }
        $temp_battle_omega['battle_target_player']['player_robots'][0] = $temp_boss_robot;

        // Recalculate REWARD ZENNY and ALLOWED TURNS for this battle
        rpg_mission::calculate_mission_zenny_and_turns($temp_battle_omega, $this_prototype_data, $temp_boss_level);

        // If the rescure robot has not yet been unlocked as a playable character, show it in the background
        $rescue_robot_unlockable = false;
        if (!empty($this_rescue_token)
            && !mmrpg_prototype_robot_unlocked(false, $this_rescue_token)){

            // Define the rescue robot's level and display properties depending on who it is
            $rescue_robot_size = 40;
            $rescue_robot_unlockable = true;
            $rescue_robot_level = $temp_boss_level;
            $rescue_robot_frame = array(8,0,8,0,0);
            $rescue_robot_position = array('x' => 354, 'y' => 118, 'direction' => 'left');

            // Add the rescue robot to the background with animation
            if (empty($temp_battle_omega['battle_field_base']['field_background_attachments']) && !empty($boss_field_data['field_background_attachments'])){ $temp_battle_omega['battle_field_base']['field_background_attachments'] = $boss_field_data['field_background_attachments']; }
            $temp_battle_omega['battle_field_base']['field_background_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => $rescue_robot_size, 'offset_x' => $rescue_robot_position['x'], 'offset_y' => $rescue_robot_position['y'], 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => $rescue_robot_frame, 'robot_direction' => $rescue_robot_position['direction']);

            // Add the rescue robot to the list of unlockables
            $temp_battle_omega['battle_rewards']['robots'] = array();
            $temp_battle_omega['battle_rewards']['robots'][] = array('token' => $this_rescue_token, 'level' => $rescue_robot_level, 'experience' => 999);

        }

        // Update the description text for the battle
        $temp_battle_omega['battle_description'] = 'Defeat the '.$this_robot_name.' that\'s pulled you into '.explode(' ', strtolower($boss_field_data['field_name']))[1].'!';

        // If the rescue robot is here, add some ambiguous text to the description
        if ($rescue_robot_unlockable){
            //$temp_battle_omega['battle_description'] = str_replace('!', ' and rescue the support robot that\'s appeared on the field!', $temp_battle_omega['battle_description']);
            $temp_battle_omega['battle_description2'] = ' Wait a minute... who\'s that in the background?';
        }

        // Add some random item drops to the boss battle
        $temp_battle_omega['battle_rewards']['items'] = array(
            // Nothing special to drop FOR NOW
            );

        // Recalculate REWARD ZENNY and ALLOWED TURNS for this battle
        rpg_mission::calculate_mission_zenny_and_turns($temp_battle_omega, $this_prototype_data, $this_start_level);

        // Return the generated omega battle data
        return $temp_battle_omega;

    }

}
?>