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
        $challenge_filters[] = 'challenges.challenge_flag_published = 1';
        if (!$include_hidden){ $challenge_filters[] = 'challenges.challenge_flag_hidden = 0'; }
        if (!empty($challenge_kind)){ $challenge_filters[] = "challenges.challenge_kind = '{$challenge_kind}'"; }
        $challenge_filters = !empty($challenge_filters) ? implode(' AND ', $challenge_filters) : '1 = 1';
        // Collect or define the order for the query
        $challenge_order = array();
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
        static $mmrpg_index_fields;
        static $mmrpg_index_robots;
        if (empty($mmrpg_index_fields)){ $mmrpg_index_fields = rpg_field::get_index(); }
        if (empty($mmrpg_index_robots)){ $mmrpg_index_robots = rpg_robot::get_index(); }

        // Define any bonus stats applied to these robots
        $challenge_robot_token = 100;
        $challenge_robot_rewards = array('robot_attack' => 9999, 'robot_defense' => 9999, 'robot_speed' => 9999);

        // Generate the challenge token based on available data
        $challenge_token = $this_prototype_data['phase_battle_token'].'-'.$challenge_data['challenge_kind'].'-'.$challenge_data['challenge_creator'].'-'.$challenge_data['challenge_id'];

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
        $temp_option_field_list = array();
        $temp_option_field_list[] = $field_info_1;
        if ($field_info_2 != $field_info_1){ $temp_option_field_list[] = $field_info_2; }
        $challenge_field_base['field_name'] = preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$1', $field_info_1['field_name']).' '.preg_replace('/^([-_a-z0-9\s]+)\s+([-_a-z0-9]+)$/i', '$2', $field_info_2['field_name']);
        foreach ($temp_option_field_list AS $temp_field){
            if (!empty($temp_field['field_multipliers'])){
                foreach ($temp_field['field_multipliers'] AS $temp_type => $temp_multiplier){
                    if ($temp_type == 'experience'){ continue; }
                    if (!isset($temp_option_multipliers[$temp_type])){ $temp_option_multipliers[$temp_type] = $temp_multiplier; }
                    else { $temp_option_multipliers[$temp_type] = $temp_option_multipliers[$temp_type] * $temp_multiplier; }
                }
            }
        }
        $challenge_field_base['field_type'] = !empty($field_info_1['field_type']) ? $field_info_1['field_type'] : '';
        $challenge_field_base['field_type2'] = !empty($field_info_2['field_type']) && $field_info_2['field_type'] != $field_info_1['field_type'] ? $field_info_2['field_type'] : '';
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
            $challenge_target_player['player_robots'][$k]['robot_level'] = $challenge_robot_token;
            $challenge_target_player['player_robots'][$k]['values'] = array('robot_rewards' => $challenge_robot_rewards);
        }
        $num_target_robots = count($challenge_target_player['player_robots']);

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

        // Overwrite calculated turns if hard-coded limit has been defined
        if (!empty($challenge_data['challenge_robot_limit'])){ $challenge_allowed_robots = $challenge_data['challenge_robot_limit']; }
        else { $challenge_allowed_robots = count($challenge_target_player['player_robots']); }

        // Overwrite calculated turns if hard-coded limit has been defined
        if (!empty($challenge_data['challenge_turn_limit'])){ $challenge_allowed_turns = $challenge_data['challenge_turn_limit']; }

        // Generate the challenge name with created if applicable
        if ($challenge_data['challenge_kind'] == 'event'){
            $challenge_name = 'Challenge Mode Event Battle';
        } else {
            $challenge_name = 'Challenge Mode Battle';
            if (!empty($challenge_data['challenge_creator'])
                && !empty($challenge_data['challenge_creator_name'])){
                $challenge_name .= ' by '.ucwords(trim($challenge_data['challenge_creator_name']));
            }
        }

        // Collect the challenge description and prepend the button name
        $challenge_description = '';
        $challenge_description2 = '';
        if (!empty($challenge_data['challenge_description'])){
            $challenge_description = $challenge_data['challenge_description'];
        } else {
            if ($challenge_data['challenge_kind'] == 'event'){
                $challenge_description = 'Defeat the '.
                    ($num_target_robots == 1 ? 'target robot ' : 'target robots ').
                    'in the "<em>'.$challenge_data['challenge_name'].'</em>" event challenge '.
                    'by the MMRPG team! ';
            } else {
                $challenge_description = 'Defeat the '.
                    ($num_target_robots == 1 ? 'target robot ' : 'target robots ').
                    'in the "<em>'.$challenge_data['challenge_name'].'</em>" user challenge '.
                    'by '.ucwords(trim($challenge_data['challenge_creator_name'])).'! ';
            }
            $challenge_description2 = 'Good luck and have fun!';
        }

        // Define the battle rewards based on above data
        $challenge_battle_rewards = array();
        if ($challenge_data['challenge_kind'] == 'event'){
            $challenge_battle_rewards['robots'] = array();
            foreach ($challenge_target_player['player_robots'] AS $key => $robot){
                if (!empty($robot['robot_image'])){ continue; }
                $rtoken = $robot['robot_token'];
                if (!isset($mmrpg_index_robots[$rtoken])){ continue; }
                $rindex = $mmrpg_index_robots[$rtoken];
                if (empty($rindex['robot_flag_published'])){ continue; }
                elseif (empty($rindex['robot_flag_complete'])){ continue; }
                elseif (empty($rindex['robot_flag_unlockable'])){ continue; }
                elseif ($rindex['robot_class'] != 'master'){ continue; }
                $challenge_battle_rewards['robots'][] = array('token' => $robot['robot_token'], 'level' => 99);
            }
        }

        // Define the marker type for this challenge
        $challenge_marker_type = 'base';
        if ($challenge_data['challenge_kind'] == 'event'){ $challenge_marker_type = 'gold'; }

        // Increase the reward zenny if this is a bronze, silver, or gold challenge
        if ($challenge_marker_type == 'bronze'){ $challenge_reward_zenny += ($challenge_reward_zenny * 1); }
        if ($challenge_marker_type == 'silver'){ $challenge_reward_zenny += ($challenge_reward_zenny * 2); }
        if ($challenge_marker_type == 'gold'){ $challenge_reward_zenny += ($challenge_reward_zenny * 3); }

        // Define and battle flag, values, or counters we need to
        $challenge_flags = array();
        $challenge_values = array();
        $challenge_counters = array();
        $challenge_flags['challenge_battle'] = true;
        $challenge_values['challenge_battle_kind'] = $challenge_data['challenge_kind'];
        $challenge_values['challenge_battle_by'] = $challenge_data['challenge_creator_name'];
        //$challenge_values['challenge_marker'] = 'glass';
        $challenge_values['challenge_marker'] = $challenge_marker_type;

        // Pull event mission data from the database
        $temp_battle_omega = array(
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'battle_token' => $challenge_token,
            'battle_name' => $challenge_name,
            'battle_button' => $challenge_data['challenge_name'],
            'battle_level' => $challenge_robot_token,
            'battle_robot_limit' => $challenge_allowed_robots,
            'battle_size' => $challenge_size,
            'battle_encore' => true,
            'battle_counts' => false,
            'battle_description' => $challenge_description,
            'battle_description2' => $challenge_description2,
            'battle_field_base' => $challenge_field_base,
            'battle_target_player' => $challenge_target_player,
            'battle_zenny' => $challenge_reward_zenny,
            'battle_turns' => $challenge_allowed_turns,
            'battle_rewards' => $challenge_battle_rewards,
            'flags' => $challenge_flags,
            'values' => $challenge_values,
            'counters' => $challenge_counters
            );

        // Return the generated omega battle with all the details
        return $temp_battle_omega;

    }

    // Return a list of database index fields pertinent to challenge misions
    public static function get_index_fields($implode = false, $table = ''){

        // Define the various index fields for item objects
        $index_fields = array(
            'challenge_id',
            'challenge_kind',
            'challenge_creator',
            'challenge_name',
            'challenge_description',
            'challenge_field_data',
            'challenge_target_data',
            'challenge_reward_data',
            'challenge_robot_limit',
            'challenge_turn_limit',
            'challenge_flag_published',
            'challenge_flag_hidden',
            'challenge_times_accessed',
            'challenge_times_concluded',
            'challenge_user_victories',
            'challenge_user_defeats',
            'challenge_date_created',
            'challenge_date_modified'
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