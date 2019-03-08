<?php
/**
 * Mega Man RPG Challenge Mission
 * <p>The challenge mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_challenge extends rpg_mission {

    // Define a function for pulling a specific event mission from the database
    public static function get_missions($this_prototype_data, $battle_kind = '', $battle_limit = 0, $include_hidden = false, $shuffle_list = true){
        global $db;
        // Collect or define filters for the query
        $battle_filters = array();
        $battle_filters[] = 'battles.battle_flag_active = 1';
        if (!$include_hidden){ $battle_filters[] = 'battles.battle_flag_hidden = 0'; }
        if (!empty($battle_kind)){ $battle_filters[] = "battles.battle_kind = '{$battle_kind}'"; }
        $battle_filters = !empty($battle_filters) ? implode(' AND ', $battle_filters) : '1 = 1';
        // Collect or define the order for the query
        $battle_order = array();
        $battle_order[] = 'battles.battle_level ASC';
        $battle_order[] = "FIELD(battles.battle_kind, 'challenge', 'event')";
        if ($shuffle_list){ $battle_order[] = 'RAND()'; }
        $battle_order = !empty($battle_order) ? implode(', ', $battle_order) : 'battles.battle_id ASC';
        // Collect or define the query result limit
        $battle_limit = !empty($battle_limit) && is_numeric($battle_limit) ? 'LIMIT '.$battle_limit : '';
        // Pull data from the database given filters and ordering
        $battle_fields = self::get_index_fields(true, 'battles');
        $raw_data_list = $db->get_array_list("SELECT
            {$battle_fields},
            (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS user_name
            FROM mmrpg_users_battles AS battles
            LEFT JOIN mmrpg_users AS users ON users.user_id = battles.user_id
            WHERE {$battle_filters}
            ORDER BY {$battle_order}
            {$battle_limit}
            ;");
        //exit("SELECT {$battle_fields} FROM mmrpg_users_battles WHERE {$battle_filters} ORDER BY {$battle_order} {$battle_limit};");
        if (empty($raw_data_list)){ return false; }
        $parsed_data_list = array();
        foreach ($raw_data_list AS $key => $raw_data){
            $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
            if (empty($parsed_data)){ continue; }
            else { $parsed_data_list[] = $parsed_data; }
        }
        if (empty($parsed_data_list)){ return false; }
        else { return $parsed_data_list; }
    }

    // Define a function for pulling a specific event mission from the database
    public static function get_mission($this_prototype_data, $battle_id = 0){
        global $db;
        if (!is_numeric($battle_id)){ return false; }
        $battle_fields = self::get_index_fields(true);
        $raw_data = $db->get_array("SELECT {$battle_fields} FROM mmrpg_users_battles WHERE battle_id = {$battle_id};");
        if (empty($raw_data)){ return false; }
        $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
        if (empty($parsed_data)){ return false; }
        else { return $parsed_data; }
    }

    // Define a function for parsing mission details pulled from the database
    public static function parse_mission($this_prototype_data, $battle_data){

        // Collect a field index for reference later
        $mmrpg_index_fields = rpg_field::get_index();

        // Define any bonus stats applied to these robots
        $temp_robot_rewards = array('robot_attack' => 9999, 'robot_defense' => 9999, 'robot_speed' => 9999);

        // Generate the battle token based on available data
        $temp_battle_token = $this_prototype_data['phase_battle_token'].'-'.$battle_data['battle_kind'].'-'.$battle_data['user_id'].'-'.$battle_data['battle_slot'];

        // Generate the battle name with created if applicable
        if ($battle_data['battle_kind'] == 'challenge'){ $temp_battle_name = 'Challenge Mode Battle'; }
        elseif ($battle_data['battle_kind'] == 'event'){ $temp_battle_name = 'Challenge Mode Event Battle'; }
        if (!empty($battle_data['user_id']) && !empty($battle_data['user_name'])){ $temp_battle_name .= ' by '.ucwords($battle_data['user_name']); }

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

        // Determine what size this battle should be
        if ($battle_data['battle_kind'] == 'event'){ $battle_size = '1x4'; }
        else { $battle_size = '1x2'; }
        //$num_targets = count($battle_target_player['player_robots']);
        //$battle_size = '1x'.(ceil($num_targets / 4) + 1);
        //if ($num_targets == 1){ $battle_size = '1x1'; }
        //elseif ($num_targets >= 2 && $num_targets <= 4){ $battle_size = '1x2'; }
        //elseif ($num_targets >= 5 && $num_targets <= 8){ $battle_size = '1x4'; }

        // Define and battle flag, values, or counters we need to
        $battle_flags = array();
        $battle_values = array();
        $battle_counters = array();
        $battle_flags['challenge_battle'] = true;
        $battle_values['skull_medallion'] = 'pseudo';

        // Pull event mission data from the database
        $temp_battle_omega = array(
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'battle_token' => $temp_battle_token,
            'battle_name' => $temp_battle_name,
            'battle_button' => $battle_data['battle_name'],
            'battle_level' => $battle_data['battle_level'],
            'battle_robot_limit' => $battle_data['battle_robot_limit'],
            'battle_size' => $battle_size,
            'battle_encore' => true,
            'battle_counts' => false,
            'battle_description' => $battle_data['battle_description'],
            'battle_field_base' => $battle_field_base,
            'battle_target_player' => $battle_target_player,
            'flags' => $battle_flags,
            'values' => $battle_values,
            'counters' => $battle_counters
            );

        // Return the generated omega battle with all the details
        return $temp_battle_omega;

    }

    // Define a static function for getting a preset core shield for the challenge
    public static function get_preset_core_shield($shield_type){
        $this_ability_token = 'core-shield';
        $this_attachment_token = $this_ability_token.'-'.$shield_type;
        $this_attachment_image = $this_ability_token.'_'.$shield_type;
        $this_attachment_destroy_text = 'The <span class="ability_name ability_type ability_type_'.$shield_type.'">'.ucfirst($shield_type).'</span> type <span class="ability_name ability_type ability_type_'.$shield_type.'">Core Shield</span> faded away!<br /> ';
        $this_attachment_destroy_text .= 'This robot is no longer protected from the <span class="ability_name ability_type ability_type_'.$shield_type.'">'.ucfirst($shield_type).'</span> element...';
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability_token,
            'ability_image' => $this_attachment_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => 9,
            'attachment_damage_input_breaker_'.$shield_type => 0.0000000001,
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -9999, -9999, 10, $this_attachment_destroy_text),
                'failure' => array(9, -9999, -9999, 10, $this_attachment_destroy_text)
                ),
            'ability_frame' => 2,
            'ability_frame_animate' => array(2, 3, 4, 3),
            'ability_frame_offset' => array('x' => 10, 'y' => 0, 'z' => 10)
            );
        return $this_attachment_info;
    }

    // Return a list of database index fields pertinent to challenge misions
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various index fields for item objects
        $index_fields = array(
            'battle_id',
            'user_id',
            'battle_kind',
            'battle_slot',
            'battle_level',
            'battle_name',
            'battle_description',
            'battle_field_data',
            'battle_target_data',
            'battle_reward_data',
            'battle_robot_limit',
            'battle_flag_active',
            'battle_flag_hidden'
            );

        // Add table name to each field string if requested
        if (!empty($table)){
            foreach ($index_fields AS $key => $field){
                $index_fields[$key] = $table.'.'.$field;
            }
        }

        // Implode the index fields into a string if requested
        if ($implode){
            $index_fields = implode(', ', $index_fields);
        }

        // Return the index fields, array or string
        return $index_fields;

    }

}
?>