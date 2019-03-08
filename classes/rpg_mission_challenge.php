<?php
/**
 * Mega Man RPG Challenge Mission
 * <p>The challenge mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_challenge extends rpg_mission {

    // Define a function for pulling a specific event mission from the database
    public static function get_missions($this_prototype_data, $challenge_kind = '', $challenge_limit = 0, $include_hidden = false, $shuffle_list = true){
        global $db;
        // Collect or define filters for the query
        $challenge_filters = array();
        $challenge_filters[] = 'challenges.challenge_flag_active = 1';
        if (!$include_hidden){ $challenge_filters[] = 'challenges.challenge_flag_hidden = 0'; }
        if (!empty($challenge_kind)){ $challenge_filters[] = "challenges.challenge_kind = '{$challenge_kind}'"; }
        $challenge_filters = !empty($challenge_filters) ? implode(' AND ', $challenge_filters) : '1 = 1';
        // Collect or define the order for the query
        $challenge_order = array();
        $challenge_order[] = 'challenges.challenge_level ASC';
        $challenge_order[] = "FIELD(challenges.challenge_kind, 'event', 'user')";
        if ($shuffle_list){ $challenge_order[] = 'RAND()'; }
        $challenge_order = !empty($challenge_order) ? implode(', ', $challenge_order) : 'challenges.challenge_id ASC';
        // Collect or define the query result limit
        $challenge_limit = !empty($challenge_limit) && is_numeric($challenge_limit) ? 'LIMIT '.$challenge_limit : '';
        // Pull data from the database given filters and ordering
        $challenge_fields = self::get_index_fields(true, 'challenges');
        $raw_data_list = $db->get_array_list("SELECT
            {$challenge_fields},
            (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name
            FROM mmrpg_challenges AS challenges
            LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator
            WHERE {$challenge_filters}
            ORDER BY {$challenge_order}
            {$challenge_limit}
            ;");
        //exit("SELECT {$challenge_fields} FROM mmrpg_challenges WHERE {$challenge_filters} ORDER BY {$challenge_order} {$challenge_limit};");
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
    public static function get_mission($this_prototype_data, $challenge_id = 0){
        global $db;
        if (!is_numeric($challenge_id)){ return false; }
        $challenge_fields = self::get_index_fields(true);
        $raw_data = $db->get_array("SELECT {$challenge_fields} FROM mmrpg_challenges WHERE challenge_id = {$challenge_id};");
        if (empty($raw_data)){ return false; }
        $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
        if (empty($parsed_data)){ return false; }
        else { return $parsed_data; }
    }

    // Define a function for parsing mission details pulled from the database
    public static function parse_mission($this_prototype_data, $challenge_data){

        // Collect a field index for reference later
        $mmrpg_index_fields = rpg_field::get_index();

        // Define any bonus stats applied to these robots
        $temp_robot_rewards = array('robot_attack' => 9999, 'robot_defense' => 9999, 'robot_speed' => 9999);

        // Generate the challenge token based on available data
        $challenge_token = $this_prototype_data['phase_battle_token'].'-'.$challenge_data['challenge_kind'].'-'.$challenge_data['challenge_creator'].'-'.$challenge_data['challenge_slot'];

        // Generate the challenge name with created if applicable
        if ($challenge_data['challenge_kind'] == 'event'){
            $challenge_name = 'Challenge Mode Event Battle';
        } else {
            $challenge_name = 'Challenge Mode Battle';
            if (!empty($challenge_data['challenge_creator'])
                && !empty($challenge_data['challenge_creator_name'])){
                $challenge_name .= ' by '.ucwords($challenge_data['challenge_creator_name']);
            }
        }

        // Automatically expand the field data with all required details given base
        if (empty($challenge_data['challenge_field_data'])){ return false; }
        $challenge_field_base = json_decode($challenge_data['challenge_field_data'], true);
        $challenge_field_base['field_id'] = 100;
        $challenge_field_base['field_token'] = 'prototype-complete';
        if (!isset($challenge_field_base['field_background'])){ $challenge_field_base['field_background'] = $challenge_field_base['field_token']; }
        if (!isset($challenge_field_base['field_foreground'])){ $challenge_field_base['field_foreground'] = $challenge_field_base['field_background']; }
        $field_info_1 = !empty($mmrpg_index_fields[$challenge_field_base['field_background']]) ? $mmrpg_index_fields[$challenge_field_base['field_background']] : false;
        $field_info_2 = !empty($mmrpg_index_fields[$challenge_field_base['field_foreground']]) ? $mmrpg_index_fields[$challenge_field_base['field_foreground']] : false;
        if (empty($field_info_1) || empty($field_info_2)){ return false; }
        $temp_option_multipliers = array();
        $temp_option_field_list = array($field_info_1, $field_info_2);
        $challenge_field_base['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $field_info_1['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $field_info_2['field_name']);
        foreach ($temp_option_field_list AS $temp_field){
            if (!empty($temp_field['field_multipliers'])){
                foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                    if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                    else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                }
            }
        }
        $challenge_field_base['field_type'] = !empty($field_info_1['field_type']) ? $field_info_1['field_type'] : '';
        $challenge_field_base['field_type2'] = !empty($field_info_2['field_type']) ? $field_info_2['field_type'] : '';
        if (!isset($challenge_field_base['field_music'])){ $challenge_field_base['field_music'] = $challenge_field_base['field_foreground']; }
        if (!isset($challenge_field_base['field_multipliers'])){ $challenge_field_base['field_multipliers'] = $temp_option_multipliers; }
        if (!isset($challenge_field_base['field_mechas'])){
            $challenge_field_base['field_mechas'] = array();
            if (!empty($field_info_1['field_mechas'])){ $challenge_field_base['field_mechas'] = array_merge($challenge_field_base['field_mechas'], $field_info_1['field_mechas']); }
            if (!empty($field_info_2['field_mechas'])){ $challenge_field_base['field_mechas'] = array_merge($challenge_field_base['field_mechas'], $field_info_2['field_mechas']); }
            if (empty($challenge_field_base['field_mechas'])){ $challenge_field_base['field_mechas'][] = 'met'; }
        }
        $challenge_field_base['field_background_frame'] = $field_info_1['field_background_frame'];
        $challenge_field_base['field_foreground_frame'] = $field_info_2['field_foreground_frame'];
        $challenge_field_base['field_background_attachments'] = $field_info_1['field_background_attachments'];
        $challenge_field_base['field_foreground_attachments'] = $field_info_2['field_foreground_attachments'];

        // Automatically expand the target data with all required details given base
        if (empty($challenge_data['challenge_target_data'])){ return false; }
        $challenge_target_player = json_decode($challenge_data['challenge_target_data'], true);
        $challenge_target_player['player_id'] = MMRPG_SETTINGS_TARGET_PLAYERID;
        if (!isset($challenge_target_player['player_token'])){ $challenge_target_player['player_token'] = 'player'; }
        if (!isset($challenge_target_player['player_name'])){ $challenge_target_player['player_name'] = ucwords(str_replace('-', '. ', $challenge_target_player['player_token'])); }
        foreach ($challenge_target_player['player_robots'] AS $k => $r){
            $challenge_target_player['player_robots'][$k]['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID + ($k + 1);
            $challenge_target_player['player_robots'][$k]['robot_level'] = $challenge_data['challenge_level'];
            $challenge_target_player['player_robots'][$k]['values'] = array('robot_rewards' => $temp_robot_rewards);
        }

        // Determine what size this battle should be
        if ($challenge_data['challenge_kind'] == 'event'){ $challenge_size = '1x4'; }
        else { $challenge_size = '1x2'; }
        //$num_targets = count($challenge_target_player['player_robots']);
        //$challenge_size = '1x'.(ceil($num_targets / 4) + 1);
        //if ($num_targets == 1){ $challenge_size = '1x1'; }
        //elseif ($num_targets >= 2 && $num_targets <= 4){ $challenge_size = '1x2'; }
        //elseif ($num_targets >= 5 && $num_targets <= 8){ $challenge_size = '1x4'; }

        // Calculate the allowed turns and reward zenny for this mission
        $challenge_reward_zenny = 0;
        $challenge_allowed_turns = 0;
        foreach ($challenge_target_player['player_robots'] AS $info){
            $challenge_reward_zenny += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER * $info['robot_level']);
            $challenge_allowed_turns += ceil(MMRPG_SETTINGS_BATTLETURNS_PERROBOT * MMRPG_SETTINGS_BATTLETURNS_PLAYERBATTLE_MULTIPLIER);
        }

        // Define and battle flag, values, or counters we need to
        $challenge_flags = array();
        $challenge_values = array();
        $challenge_counters = array();
        $challenge_flags['challenge_battle'] = true;
        $challenge_values['skull_medallion'] = 'pseudo';

        // Pull event mission data from the database
        $temp_battle_omega = array(
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'battle_token' => $challenge_token,
            'battle_name' => $challenge_name,
            'battle_button' => $challenge_data['challenge_name'],
            'battle_level' => $challenge_data['challenge_level'],
            'battle_robot_limit' => $challenge_data['challenge_robot_limit'],
            'battle_size' => $challenge_size,
            'battle_encore' => true,
            'battle_counts' => false,
            'battle_description' => $challenge_data['challenge_description'],
            'battle_field_base' => $challenge_field_base,
            'battle_target_player' => $challenge_target_player,
            'battle_zenny' => $challenge_reward_zenny,
            'battle_turns' => $challenge_allowed_turns,
            'flags' => $challenge_flags,
            'values' => $challenge_values,
            'counters' => $challenge_counters
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
            'challenge_id',
            'challenge_creator',
            'challenge_kind',
            'challenge_slot',
            'challenge_level',
            'challenge_name',
            'challenge_description',
            'challenge_field_data',
            'challenge_target_data',
            'challenge_reward_data',
            'challenge_robot_limit',
            'challenge_flag_active',
            'challenge_flag_hidden'
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