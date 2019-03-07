<?php
/**
 * Mega Man RPG Challenge Mission
 * <p>The challenge mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_challenge extends rpg_mission {

    // Define a function for pulling a specific event mission from the database
    public static function get_mission($this_prototype_data, $battle_id = 0){
        global $db;
        if (!is_numeric($battle_id)){ return false; }
        $raw_data = $db->get_array("SELECT * FROM mmrpg_users_battles WHERE battle_id = {$battle_id};");
        if (empty($raw_data)){ return false; }
        $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
        if (empty($parsed_data)){ return false; }
        else { return $parsed_data; }
    }

    // Define a function for parsing mission details pulled from the database
    public static function parse_mission($this_prototype_data, $battle_data = array()){

        // Collect a field index for reference later
        $mmrpg_index_fields = rpg_field::get_index();

        // Define any bonus stats applied to these robots
        $temp_robot_rewards = array('robot_attack' => 9999, 'robot_defense' => 9999, 'robot_speed' => 9999);

        // Automatically expand the field data with all required details given base
        if (empty($battle_data['battle_field_data'])){ return false; }
        $battle_field_base = json_decode($battle_data['battle_field_data'], true);
        $battle_field_base['field_id'] = 100;
        $battle_field_base['field_token'] = 'prototype-complete';
        if (!isset($battle_field_base['field_background'])){ $battle_field_base['field_background'] = $battle_field_base['field_token']; }
        if (!isset($battle_field_base['field_foreground'])){ $battle_field_base['field_foreground'] = $battle_field_base['field_background']; }
        $field_info_1 = !empty($mmrpg_index_fields[$battle_field_base['field_background']]) ? $mmrpg_index_fields[$battle_field_base['field_background']] : false;
        $field_info_2 = !empty($mmrpg_index_fields[$battle_field_base['field_foreground']]) ? $mmrpg_index_fields[$battle_field_base['field_foreground']] : false;
        if (empty($field_info_1) || empty($field_info_2)){ return false; }
        $temp_option_multipliers = array();
        $temp_option_field_list = array($field_info_1, $field_info_2);
        $battle_field_base['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $field_info_1['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $field_info_2['field_name']);
        foreach ($temp_option_field_list AS $temp_field){
            if (!empty($temp_field['field_multipliers'])){
                foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                    if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                    else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                }
            }
        }
        $battle_field_base['field_type'] = !empty($field_info_1['field_type']) ? $field_info_1['field_type'] : '';
        $battle_field_base['field_type2'] = !empty($field_info_2['field_type']) ? $field_info_2['field_type'] : '';
        if (!isset($battle_field_base['field_music'])){ $battle_field_base['field_music'] = $battle_field_base['field_foreground']; }
        if (!isset($battle_field_base['field_multipliers'])){ $battle_field_base['field_multipliers'] = $temp_option_multipliers; }
        if (!isset($battle_field_base['field_mechas'])){
            $battle_field_base['field_mechas'] = array();
            if (!empty($field_info_1['field_mechas'])){ $battle_field_base['field_mechas'] = array_merge($battle_field_base['field_mechas'], $field_info_1['field_mechas']); }
            if (!empty($field_info_2['field_mechas'])){ $battle_field_base['field_mechas'] = array_merge($battle_field_base['field_mechas'], $field_info_2['field_mechas']); }
            if (empty($battle_field_base['field_mechas'])){ $battle_field_base['field_mechas'][] = 'met'; }
        }
        $battle_field_base['field_background_frame'] = $field_info_1['field_background_frame'];
        $battle_field_base['field_foreground_frame'] = $field_info_2['field_foreground_frame'];
        $battle_field_base['field_background_attachments'] = $field_info_1['field_background_attachments'];
        $battle_field_base['field_foreground_attachments'] = $field_info_2['field_foreground_attachments'];

        // Automatically expand the target data with all required details given base
        if (empty($battle_data['battle_target_data'])){ return false; }
        $battle_target_player = json_decode($battle_data['battle_target_data'], true);
        $battle_target_player['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
        if (!isset($battle_target_player['player_token'])){ $battle_target_player['player_token'] = 'player'; }
        if (!isset($battle_target_player['player_name'])){ $battle_target_player['player_name'] = ucwords(str_replace('-', '. ', $battle_target_player['player_token'])); }
        foreach ($battle_target_player['player_robots'] AS $k => $r){
            $battle_target_player['player_robots'][$k]['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + ($k + 1);
            $battle_target_player['player_robots'][$k]['robot_level'] = $battle_data['battle_level'];
            $battle_target_player['player_robots'][$k]['values'] = array('robot_rewards' => $temp_robot_rewards);
        }

        // Pull event mission data from the database
        $temp_battle_omega = array(
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'battle_token' => $this_prototype_data['phase_battle_token'].'-'.$battle_data['battle_token'],
            'battle_name' => $battle_data['battle_name'],
            'battle_button' => $battle_data['battle_button'],
            'battle_size' => $battle_data['battle_size'],
            'battle_level' => $battle_data['battle_level'],
            'battle_robot_limit' => $battle_data['battle_robot_limit'],
            'battle_encore' => true,
            'battle_counts' => false,
            'battle_description' => $battle_data['battle_description'],
            'battle_field_base' => $battle_field_base,
            'battle_target_player' => $battle_target_player
            );

        // Return the generated omega battle with all the details
        return $temp_battle_omega;

    }

}
?>