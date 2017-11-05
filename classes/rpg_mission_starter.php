<?php
/**
 * Mega Man RPG Starter-Battle Mission
 * <p>The starter mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_starter extends rpg_mission {

    // Define a function for generating the STARTER missions
    public static function generate($this_prototype_data, $this_robot_token = 'met', $this_start_level = 1, $this_rescue_token = 'roll'){

        // Pull in global variables for this function
        global $mmrpg_index, $db;
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

        // Collect data on this robot and the rescue robot
        //$this_robot_index = $db->get_array_list("SELECT * FROM mmrpg_index_robots WHERE robot_flag_complete = 1;", 'robot_token');
        //$this_robot_data = rpg_robot::parse_index_info($this_robot_index[$this_robot_token]);
        $this_robot_data = rpg_robot::get_index_info($this_robot_token);
        $this_robot_name = $this_robot_data['robot_name'];
        // Populate the battle options with the starter battle option
        $temp_target_count = 1;
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$this_robot_token;
        $temp_battle_omega = array();
        $temp_battle_omega['battle_field_base']['field_id'] = 100;
        $temp_battle_omega['battle_field_base']['field_token'] = 'intro-field';
        $temp_battle_omega['battle_field_base']['field_name'] = 'Intro Field';
        $temp_battle_omega['flags']['starter_battle'] = true;
        $temp_battle_omega['battle_token'] = $temp_battle_token;
        $temp_battle_omega['battle_size'] = '1x4';
        $temp_battle_omega_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $temp_battle_omega['battle_token']);
        if (!empty($temp_battle_omega_complete['battle_count'])){ $temp_target_count = 1 + $temp_battle_omega_complete['battle_count']; }
        if ($temp_target_count > 8 ){ $temp_target_count = 8; }
        $temp_battle_omega['battle_level'] = $this_start_level;
        $temp_battle_omega['battle_phase'] = $this_prototype_data['battle_phase'];
        $temp_battle_omega['battle_name'] = 'Chapter One Intro Battle';
        //$temp_battle_omega['battle_name'] = $this_robot_name.($temp_target_count > 1 ? 's' : '');
        //$temp_battle_omega['battle_name'] = $this_robot_name.($temp_target_count > 1 ? 's' : '').' Battle';
        $temp_battle_omega['battle_turns'] = (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * $temp_target_count);
        $temp_battle_omega['battle_points'] = ceil(($this_prototype_data['battles_complete'] > 1 ? 100 : 1000) * $temp_target_count);
        $temp_battle_omega['battle_field_base']['field_music'] = mmrpg_prototype_get_player_boss_music($this_prototype_data['this_player_token']);
        $temp_battle_omega['battle_target_player']['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_battle_omega['battle_target_player']['player_token'] = 'player';
        $temp_battle_omega['battle_target_player']['player_robots'][0] = array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => $this_robot_token);
        $temp_mook_robot = $temp_battle_omega['battle_target_player']['player_robots'][0];
        $temp_battle_omega['battle_target_player']['player_robots'] = array();
        $temp_name_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $temp_mook_tokens = array();
        /// Loop through and add other robots to the battle
        for ($i = 0; $i < $temp_target_count; $i++){
            $temp_clone_robot = $temp_mook_robot;
            $temp_clone_robot['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + $i;
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
        // Add the player's lab to the background field
        $temp_doctor_token = str_replace('dr-', '', $this_prototype_data['this_player_token']);
        $temp_battle_omega['battle_field_base']['field_foreground_attachments'] = array();
        $temp_battle_omega['battle_field_base']['field_foreground_attachments']['object_intro-field-'.$temp_doctor_token] = array('class' => 'object', 'size' => 160, 'offset_x' => 12, 'offset_y' => 121, 'offset_z' => 1, 'object_token' => 'intro-field-'.$temp_doctor_token, 'object_frame' => array(0), 'object_direction' => 'right');

        // Otherwise if the rescue has not yet been unlocked as a playable character
        if (!mmrpg_prototype_robot_unlocked(false, $this_rescue_token)){
            // Add the rescue to the background with animation
            $temp_battle_omega['battle_field_base']['field_foreground_attachments']['robot_'.$this_rescue_token.'-01'] = array('class' => 'robot', 'size' => 40, 'offset_x' => 91, 'offset_y' => 118, 'offset_z' => 2, 'robot_token' => $this_rescue_token, 'robot_frame' => array(8,0,8,0,0), 'robot_direction' => 'right');
        }

        // Allow unlocking of the mecha support ability if the player has reached max targets
        if ($temp_target_count >= 8){

            // Add the Mecha Support or Field Support abilities as unlockable moves if not already earned
            $temp_battle_omega['battle_rewards']['abilities'] = array();
            if (!mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'mecha-support')){
                $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'mecha-support');
            } elseif (!mmrpg_prototype_ability_unlocked($this_prototype_data['this_player_token'], false, 'field-support')){
                $temp_battle_omega['battle_rewards']['abilities'][] = array('token' => 'field-support');
            }

        }

        // Update the battle description based on what we've calculated
        $temp_field_name = $temp_battle_omega['battle_field_base']['field_name'];
        if (!empty($temp_battle_omega['battle_rewards']['abilities'])){
            if (!$temp_battle_omega_complete){ $temp_battle_omega['battle_description'] = 'Liberate the '.$temp_field_name.' and download the hidden ability data! '; }
            else { $temp_battle_omega['battle_description'] = 'Return to the '.$temp_field_name.' and download the hidden ability data! '; }
        } else {
            $temp_robot_or_robots = 'robot'.($temp_target_count > 1 ? 's' : '');
            if (!$temp_battle_omega_complete){ $temp_battle_omega['battle_description'] = 'Defeat the enemy '.$temp_robot_or_robots.' and liberate the '.$temp_field_name.'! '; }
            else { $temp_battle_omega['battle_description'] = 'Return to the '.$temp_field_name.' and defeat the enemy '.$temp_robot_or_robots.'! '; }
        }

        // Add some random item drops to the starter battle
        if ($temp_target_count > 1){
            $temp_battle_omega['battle_rewards']['items'] = array(
                // Nothing if fought more than once FOR NOW
                );
        } else {
            $temp_battle_omega['battle_rewards']['items'] = array(
                // Nothing special the first time around
                );
        }

        // Return the generated omega battle data
        return $temp_battle_omega;

    }

}
?>