<?php
/**
 * Mega Man RPG Challenge Mission
 * <p>The challenge mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_challenge extends rpg_mission {

    // Define a function for pulling a specific event mission from the database
    public static function get_missions($this_prototype_data, $challenge_kind = 'event', $challenge_limit = 0, $include_hidden = false, $shuffle_list = false){
        global $db;
        // Collect or define filters for the query
        $challenge_filters = array();
        $challenge_filters[] = 'challenges.challenge_flag_published = 1';
        if (!$include_hidden){ $challenge_filters[] = 'challenges.challenge_flag_hidden = 0'; }
        if (!empty($challenge_kind)){ $challenge_filters[] = "challenges.challenge_kind = '{$challenge_kind}'"; }
        $challenge_filters = !empty($challenge_filters) ? implode(' AND ', $challenge_filters) : '1 = 1';
        // Collect or define the order for the query
        $challenge_order = array();
        if ($shuffle_list){ $challenge_order[] = 'RAND()'; }
        else { $challenge_order[] = 'challenges.challenge_creator ASC'; $challenge_order[] = 'challenges.challenge_id ASC'; }
        $challenge_order = !empty($challenge_order) ? implode(', ', $challenge_order) : 'challenges.challenge_id ASC';
        // Collect or define the query result limit
        $challenge_limit = !empty($challenge_limit) && is_numeric($challenge_limit) ? 'LIMIT '.$challenge_limit : '';
        // Pull data from the database given filters and ordering
        $challenge_fields = self::get_index_fields(true, 'challenges');
        $challenge_table = $challenge_kind === 'user' ? 'mmrpg_users_challenges' : 'mmrpg_challenges';
        $raw_data_sql = ("SELECT
            {$challenge_fields},
            (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name
            FROM {$challenge_table} AS challenges
            LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator
            WHERE {$challenge_filters}
            ORDER BY {$challenge_order}
            {$challenge_limit}
            ;");
        $raw_data_list = $db->get_array_list($raw_data_sql);
        //error_log($raw_data_sql);
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
    public static function get_custom_missions($this_prototype_data, $custom_mission_ids = array()){
        global $db;
        if (!is_array($custom_mission_ids) || empty($custom_mission_ids)){ return false; }
        $challenge_fields = self::get_index_fields(true, 'challenges');
        $event_challenge_ids = array();
        $user_challenge_ids = array();
        foreach ($custom_mission_ids AS $id){
            if (substr($id, 0, 1) == 'u'){ $user_challenge_ids[] = (int)(substr($id, 1)); }
            else { $event_challenge_ids[] = (int)($id); }
        }
        $raw_event_challenges_list = array();
        if (!empty($event_challenge_ids)){
            $ids_string = implode(',', $event_challenge_ids);
            $challenge_table = 'mmrpg_challenges';
            $raw_event_challenges_list = $db->get_array_list("SELECT
                {$challenge_fields},
                (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name
                FROM {$challenge_table} AS challenges
                LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator
                WHERE challenges.challenge_id IN ({$ids_string})
                ORDER BY FIELD(challenges.challenge_id, {$ids_string})
                ;");
        }
        $raw_user_challenges_list = array();
        if (!empty($user_challenge_ids)){
            $ids_string = implode(',', $user_challenge_ids);
            $challenge_table = 'mmrpg_users_challenges';
            $raw_user_challenges_list = $db->get_array_list("SELECT
                {$challenge_fields},
                (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name
                FROM {$challenge_table} AS challenges
                LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator
                WHERE challenges.challenge_id IN ({$ids_string})
                ORDER BY FIELD(challenges.challenge_id, {$ids_string})
                ;");
        }
        if (empty($raw_event_challenges_list) && empty($raw_user_challenges_list)){ return false; }
        $parsed_data_list = array();
        foreach ($custom_mission_ids AS $id){ $parsed_data_list[$id] = array(); }
        if (!empty($raw_event_challenges_list)){
            foreach ($raw_event_challenges_list AS $key => $raw_data){
                $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
                if (empty($parsed_data)){ continue; }
                $parsed_data_list[$raw_data['challenge_id']] = $parsed_data;
            }
        }
        if (!empty($raw_user_challenges_list)){
            foreach ($raw_user_challenges_list AS $key => $raw_data){
                $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
                if (empty($parsed_data)){ continue; }
                $parsed_data_list['u'.$raw_data['challenge_id']] = $parsed_data;
            }
        }

        /*
        echo('<pre>$custom_mission_ids = '.print_r($custom_mission_ids, true).'</pre>');
        echo('<pre>$raw_event_challenges_list = '.print_r($raw_event_challenges_list, true).'</pre>');
        echo('<pre>$raw_user_challenges_list = '.print_r($raw_user_challenges_list, true).'</pre>');
        echo('<pre>$parsed_data_list = '.print_r($parsed_data_list, true).'</pre>');
        exit();
        */

        if (empty($parsed_data_list)){ return false; }
        else { return array_values($parsed_data_list); }

    }

    // Define a function for pulling a specific event mission from the database
    public static function get_mission($this_prototype_data, $challenge_id = 0, $challenge_kind = 'event'){
        global $db;
        if (!is_numeric($challenge_id)){ return false; }
        $challenge_fields = self::get_index_fields(true);
        if ($challenge_kind === 'user' || substr($challenge_id, 0, 1) === 'u'){
            if (substr($challenge_id, 0, 1) === 'u'){ $challenge_xid = (int)(substr($challenge_id, 1)); }
            else { $challenge_xid = (int)($challenge_id); }
            $challenge_table = 'mmrpg_users_challenges';
            $raw_data = $db->get_array("SELECT {$challenge_fields}, (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name FROM {$challenge_table} AS challenges LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator WHERE challenge_id = {$challenge_xid};");
        } else {
            $challenge_xid = (int)($challenge_id);
            $challenge_table = 'mmrpg_challenges';
            $raw_data = $db->get_array("SELECT {$challenge_fields}, (CASE WHEN users.user_name_public <> '' THEN users.user_name_public ELSE users.user_name END) AS challenge_creator_name FROM {$challenge_table} AS challenges LEFT JOIN mmrpg_users AS users ON users.user_id = challenges.challenge_creator WHERE challenge_id = {$challenge_xid};");
        }
        if (empty($raw_data)){ return false; }
        $parsed_data = self::parse_mission($this_prototype_data, $raw_data);
        if (empty($parsed_data)){ return false; }
        else { return $parsed_data; }
    }

    // Define a function for parsing mission details pulled from the database
    public static function parse_mission($this_prototype_data, $challenge_data){

        // Collect a field index for reference later
        $mmrpg_index_fields = rpg_field::get_index(true);
        $mmrpg_index_robots = rpg_robot::get_index(true);

        // Collect any victories records so we can show 'em
        static $challenge_mission_victories;
        if (empty($challenge_mission_victories)){
            global $this_userid;
            if (!empty($this_userid)
                && $this_userid !== MMRPG_SETTINGS_GUEST_ID){
                $challenge_mission_victories = self::get_challenge_victories($this_userid);
            }
        }

        // Define any bonus stats applied to these robots
        $challenge_robot_level = 100;
        $challenge_robot_rewards = array('robot_attack' => 9999, 'robot_defense' => 9999, 'robot_speed' => 9999);

        // Generate the challenge token based on available data
        if (!isset($this_prototype_data['this_current_chapter'])){ $this_prototype_data['this_current_chapter'] = 9; } // challenges are apparently chapter 9
        $challenge_kind = $challenge_data['challenge_kind'];
        $challenge_xid = ($challenge_kind == 'user' ? 'u' : '').$challenge_data['challenge_id'];
        $challenge_token = $this_prototype_data['phase_battle_token'].'-'.$challenge_kind.'-'.$challenge_data['challenge_creator'].'-'.$challenge_xid;

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
        $temp_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_player_id = rpg_game::unique_player_id($temp_user_id, 0);
        $challenge_target_player = json_decode($challenge_data['challenge_target_data'], true);
        $challenge_target_player = array_merge(array('user_id' => 0, 'player_id' => 0), $challenge_target_player);
        $challenge_target_player['user_id'] = $temp_user_id;
        $challenge_target_player['player_id'] = $temp_player_id;
        if (!isset($challenge_target_player['player_token'])){ $challenge_target_player['player_token'] = 'player'; }
        if (!isset($challenge_target_player['player_name'])){ $challenge_target_player['player_name'] = ucwords(str_replace('-', '. ', $challenge_target_player['player_token'])); }
        foreach ($challenge_target_player['player_robots'] AS $k => $r){
            $rtoken = $r['robot_token'];
            if (!isset($mmrpg_index_robots[$rtoken])){ continue; }
            $challenge_target_player['player_robots'][$k]['robot_id'] = rpg_game::unique_robot_id($temp_player_id, $mmrpg_index_robots[$rtoken]['robot_id'], ($k + 1));
            $challenge_target_player['player_robots'][$k]['robot_level'] = $challenge_robot_level;
            $challenge_target_player['player_robots'][$k]['values']['robot_rewards'] = $challenge_robot_rewards;
        }
        $num_target_robots = count($challenge_target_player['player_robots']);

        // Determine what size this battle should be
        $challenge_size = '1x4'; // all battles are same size now
        //if ($challenge_kind == 'event'){ $challenge_size = '1x4'; }
        //else { $challenge_size = '1x2'; }
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
        if ($challenge_kind == 'event'){
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
            if ($challenge_kind == 'event'){
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
        if ($challenge_kind == 'event'){
            $challenge_battle_rewards['robots'] = array();
            foreach ($challenge_target_player['player_robots'] AS $key => $robot){
                if (!empty($robot['robot_image'])){ continue; }
                $rtoken = $robot['robot_token'];
                if (!isset($mmrpg_index_robots[$rtoken])){
                    unset($challenge_target_player['player_robots'][$key]);
                    continue;
                }
                $rindex = $mmrpg_index_robots[$rtoken];
                if (empty($rindex['robot_flag_published'])){ continue; }
                elseif (empty($rindex['robot_flag_complete'])){ continue; }
                elseif (empty($rindex['robot_flag_unlockable'])){ continue; }
                elseif ($rindex['robot_class'] !== 'master'){ continue; }
                $challenge_battle_rewards['robots'][] = array('token' => $robot['robot_token'], 'level' => 99, 'experience' => 999);
            }
        }

        // Define the marker type for this challenge
        $challenge_marker_type = 'base';
        if ($challenge_kind == 'event'){ $challenge_marker_type = 'gold'; }

        // Increase the reward zenny if this is a bronze, silver, or gold challenge
        if ($challenge_marker_type == 'bronze'){ $challenge_reward_zenny += ($challenge_reward_zenny * 1); }
        if ($challenge_marker_type == 'silver'){ $challenge_reward_zenny += ($challenge_reward_zenny * 2); }
        if ($challenge_marker_type == 'gold'){ $challenge_reward_zenny += ($challenge_reward_zenny * 3); }

        // Define and battle flag, values, or counters we need to
        $challenge_flags = array();
        $challenge_values = array();
        $challenge_counters = array();
        $challenge_flags['challenge_battle'] = true;
        if (!empty($challenge_data['challenge_flag_hidden'])){ $challenge_flags['is_hidden'] = true; }
        if (!empty($challenge_mission_victories[$challenge_kind][$challenge_xid])){ $challenge_flags['is_cleared'] = true; }
        //$challenge_target_player['player_robots'][0]['robot_token']
        $challenge_values['challenge_battle_id'] = $challenge_xid;
        $challenge_values['challenge_battle_kind'] = $challenge_kind;
        $challenge_values['challenge_battle_by'] = $challenge_data['challenge_creator_name'];
        //$challenge_values['challenge_marker'] = 'glass';
        $challenge_values['challenge_marker'] = $challenge_marker_type;
        $challenge_values['challenge_records'] = array();
        $challenge_values['challenge_records']['accessed'] = (int)($challenge_data['challenge_times_accessed']);
        $challenge_values['challenge_records']['concluded'] = (int)($challenge_data['challenge_times_concluded']);
        $challenge_values['challenge_records']['victories'] = (int)($challenge_data['challenge_user_victories']);
        $challenge_values['challenge_records']['defeats'] = (int)($challenge_data['challenge_user_defeats']);
        $challenge_values['challenge_records']['personal'] = !empty($challenge_mission_victories[$challenge_kind][$challenge_xid]) ? $challenge_mission_victories[$challenge_kind][$challenge_xid] : array();
        if ($challenge_kind == 'event'){
            $vsrobot = $challenge_target_player['player_robots'][0]['robot_token'];
            if (!empty($mmrpg_index_robots[$vsrobot])){
                $challenge_values['colour_token'] = $mmrpg_index_robots[$vsrobot]['robot_core'];
            } else {
                $challenge_values['colour_token'] = $challenge_field_base['field_type'];
            }
        } else {
            $challenge_values['colour_token'] = $challenge_field_base['field_type'];
        }

        // Pull event mission data from the database
        $temp_battle_omega = array(
            'option_chapter' => $this_prototype_data['this_current_chapter'],
            'battle_token' => $challenge_token,
            'battle_name' => $challenge_name,
            'battle_button' => $challenge_data['challenge_name'],
            'battle_level' => $challenge_robot_level,
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
            'challenge_robot_limit',
            'challenge_turn_limit',
            'challenge_field_data',
            'challenge_target_data',
            'challenge_reward_data',
            'challenge_flag_published',
            'challenge_flag_hidden',
            'challenge_flag_protected',
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

    // Define a function for collecting challenge victories for a given user
    public static function get_challenge_victories($this_userid){
        global $db;
        if (!empty($this_userid)
            && $this_userid !== MMRPG_SETTINGS_GUEST_ID){
            $challenge_table = 'mmrpg_challenges';
            $challenge_leaderboard_table = 'mmrpg_challenges_leaderboard';
            $challenge_event_mission_victories = $db->get_array_list("SELECT
                board.challenge_id,
                board.challenge_turns_used,
                challenges.challenge_turn_limit,
                board.challenge_robots_used,
                challenges.challenge_robot_limit,
                board.challenge_result
                FROM {$challenge_leaderboard_table} AS board
                LEFT JOIN {$challenge_table} AS challenges ON challenges.challenge_id = board.challenge_id
                WHERE
                board.user_id = {$this_userid}
                AND board.challenge_result = 'victory'
                ;", 'challenge_id');
            $challenge_table = 'mmrpg_users_challenges';
            $challenge_leaderboard_table = 'mmrpg_users_challenges_leaderboard';
            $challenge_user_mission_victories = $db->get_array_list("SELECT
                CONCAT('u', board.challenge_id) AS challenge_id,
                board.challenge_turns_used,
                challenges.challenge_turn_limit,
                board.challenge_robots_used,
                challenges.challenge_robot_limit,
                board.challenge_result
                FROM {$challenge_leaderboard_table} AS board
                LEFT JOIN {$challenge_table} AS challenges ON challenges.challenge_id = board.challenge_id
                WHERE
                board.user_id = {$this_userid}
                AND board.challenge_result = 'victory'
                ;", 'challenge_id');
            $challenge_mission_victories = array('event' => array(), 'user' => array());
            if (!empty($challenge_event_mission_victories)){ $challenge_mission_victories['event'] = $challenge_event_mission_victories; }
            if (!empty($challenge_user_mission_victories)){ $challenge_mission_victories['user'] = $challenge_user_mission_victories; }
            //error_log('$challenge_mission_victories = '.print_r($challenge_mission_victories, true));
            return $challenge_mission_victories;
        } else {
            return false;
        }
    }

    // Define a function for calculating the battle point rewards for a challenge victory
    public static function calculate_challenge_reward_points($challenge_kind, $victory_results, &$victory_percent = 0, &$victory_rank = ''){
        static $rank_index;
        if (empty($rank_index)){ $rank_index = array(10 => 'SS', 9 => 'S', 8 => 'A', 7 => 'B', 6 => 'C', 5 => 'D', 4 => 'E', 3 => 'F', 2 => 'F', 1 => 'F', 0 => 'F'); }
        if (empty($victory_results)){ return 0; }
        if ($victory_results['challenge_turns_used'] > $victory_results['challenge_turn_limit']){ $victory_results['challenge_turn_limit'] = $victory_results['challenge_turns_used']; }
        if ($victory_results['challenge_robots_used'] > $victory_results['challenge_robot_limit']){ $victory_results['challenge_robot_limit'] = $victory_results['challenge_robots_used']; }
        $victory_points_possible = 200000;
        if ($challenge_kind == 'event'){ $victory_points_possible *= 10; }
        $victory_points = ($victory_points_possible / 2);
        $victory_points += ($victory_points_possible / 4) - ceil(($victory_points_possible / 4) * (($victory_results['challenge_turns_used'] - 1) / $victory_results['challenge_turn_limit']));
        $victory_points += ($victory_points_possible / 4) - ceil(($victory_points_possible / 4) * (($victory_results['challenge_robots_used'] - 1) / $victory_results['challenge_robot_limit']));
        $victory_percent = round((($victory_points / $victory_points_possible) * 100), 0);
        $victory_rank_num = floor(($victory_points / $victory_points_possible) * 10);
        $victory_rank = $rank_index[$victory_rank_num];
        return $victory_points;
    }


    // -- PRINT FUNCTIONS -- /

    // Define static fields for the indexes required by the print functions
    static $mmrpg_field_index_for_print;
    static $mmrpg_robot_index_for_print;

    // Define a static function for printing out the challenge's title markup
    public static function print_editor_title_markup($challenge_info, $challenge_victories_index, $mmrpg_field_index = false, $mmrpg_robot_index = false){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Collect a field and robot indexes in case we need them later
        if (empty(self::$mmrpg_field_index_for_print)){ self::$mmrpg_field_index_for_print = rpg_field::get_index(true); }
        if (empty(self::$mmrpg_robot_index_for_print)){ self::$mmrpg_robot_index_for_print = rpg_robot::get_index(true); }
        $mmrpg_field_index = self::$mmrpg_field_index_for_print;
        $mmrpg_robot_index = self::$mmrpg_robot_index_for_print;

        // Collect data about this challenge mission from the array
        $this_challenge_id = $challenge_info['challenge_id'];
        $this_challenge_name = $challenge_info['challenge_name'];
        $this_challenge_kind = $challenge_info['challenge_kind'];
        $this_field_data = json_decode($challenge_info['challenge_field_data'], true);
        $this_field_info1 = $mmrpg_field_index[$this_field_data['field_background']];
        $this_field_info2 = $mmrpg_field_index[$this_field_data['field_foreground']];
        $this_target_data = json_decode($challenge_info['challenge_target_data'], true);
        $this_challenge_description = !empty($challenge_info['challenge_description']) ? $challenge_info['challenge_description'] : '';
        $this_victories_index = isset($challenge_victories_index[$this_challenge_kind]) ? $challenge_victories_index[$this_challenge_kind] : $challenge_victories_index;

        // Generate the actual title markup given available fields
        $field_names1 = explode(' ', $this_field_info1['field_name']);
        $field_names2 = explode(' ', $this_field_info2['field_name']);
        $this_challenge_title = $this_challenge_name;
        $this_challenge_title .= ' //';
        $this_challenge_title .= ' '.($challenge_info['challenge_kind'] == 'event' ? 'Event' : 'Player').' Challenge';
        $this_challenge_title .= ' | '.$field_names1[0].' '.$field_names2[1];
        if ($challenge_info['challenge_kind'] == 'event'
            && !empty($this_target_data['player_robots'])){
            $first_robot_token = $this_target_data['player_robots'][0]['robot_token'];
            $first_robot_info = $mmrpg_robot_index[$first_robot_token];
            $this_challenge_title .= ' | Vs. '.$first_robot_info['robot_name'];
        } elseif ($challenge_info['challenge_kind'] == 'user'
            && !empty($challenge_info['challenge_creator_name'])){
            $this_challenge_title .= ' | By '.$challenge_info['challenge_creator_name'];
        }

        if (!empty($this_challenge_description)){
            $this_challenge_title .= ' // [['.str_replace('"', '&quot;', $this_challenge_description).']]';
        }

        if (!empty($this_victories_index[$this_challenge_id])){
            $victory_results = $this_victories_index[$this_challenge_id];
            $victory_points = self::calculate_challenge_reward_points($challenge_info['challenge_kind'], $victory_results, $victory_percent, $victory_rank);
            $this_challenge_title .= ' // '.$victory_rank.'-Rank Clear! [['.
                '// Turns: '.$victory_results['challenge_turns_used'].'/'.$victory_results['challenge_turn_limit'].' '.
                '| Robots: '.$victory_results['challenge_robots_used'].'/'.$victory_results['challenge_robot_limit'].' '.
                '| Reward: '.number_format($victory_points, 0, '.', ',').' BP ('.$victory_percent.'%)'.
                ']]';
        }

        // Return the generated title for this challenge mission
        return $this_challenge_title;

    }

    // Define a static function for printing out the missions's title markup
    public static function print_editor_option_markup($challenge_info, $challenge_victories_index, $mmrpg_field_index = false, $mmrpg_robot_index = false){

        // Pull in global variables
        $session_token = rpg_game::session_token();

        // Collect a field and robot indexes in case we need them later
        if (empty(self::$mmrpg_field_index_for_print)){ self::$mmrpg_field_index_for_print = rpg_field::get_index(true); }
        if (empty(self::$mmrpg_robot_index_for_print)){ self::$mmrpg_robot_index_for_print = rpg_robot::get_index(true); }
        $mmrpg_field_index = self::$mmrpg_field_index_for_print;
        $mmrpg_robot_index = self::$mmrpg_robot_index_for_print;

        // Generate the actual title markup given available fields
        $this_challenge_id = $challenge_info['challenge_id'];
        $this_challenge_name = $challenge_info['challenge_name'];
        $this_challenge_kind = $challenge_info['challenge_kind'];
        $this_field_data = json_decode($challenge_info['challenge_field_data'], true);
        $this_field_info1 = $mmrpg_field_index[$this_field_data['field_background']];
        $this_field_info2 = $mmrpg_field_index[$this_field_data['field_foreground']];
        $this_target_data = json_decode($challenge_info['challenge_target_data'], true);
        $this_victories_index = isset($challenge_victories_index[$this_challenge_kind]) ? $challenge_victories_index[$this_challenge_kind] : $challenge_victories_index;

        $this_challenge_title = self::print_editor_title_markup($challenge_info, $this_victories_index);
        $this_challenge_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_challenge_title));
        $this_challenge_title_plain = str_replace(array('[[', ']]'), '', $this_challenge_title_plain);
        $this_challenge_title_tooltip = htmlentities($this_challenge_title, ENT_QUOTES, 'UTF-8');
        $this_challenge_title_html = str_replace(' ', '&nbsp;', $this_challenge_name);
        $this_challenge_field_type1 = !empty($this_field_info1['field_type']) ? $this_field_info1['field_type'] : 'none';
        $this_challenge_field_type2 = !empty($this_field_info2['field_type']) ? $this_field_info2['field_type'] : 'none';

        // Generate the challenge label that shows up in the option
        $this_challenge_label = $challenge_info['challenge_name'];
        if ($this_challenge_kind == 'event'
            && !empty($this_target_data['player_robots'])){
            $first_robot_token = $this_target_data['player_robots'][0]['robot_token'];
            $first_robot_info = $mmrpg_robot_index[$first_robot_token];
            $this_challenge_label .= ' | Vs. '.$first_robot_info['robot_name'];
        } elseif ($this_challenge_kind == 'user'
            && !empty($challenge_info['challenge_creator_name'])){
            $this_challenge_label .= ' | By '.$challenge_info['challenge_creator_name'];
        }

        // Check to see if this is a new challenge (relatively speaking)
        $date_created = $challenge_info['challenge_date_created'];
        $time_online = time() - $date_created;
        $new_theshold = 60 * 60 * 24 * 7;
        $is_new = $time_online <= $new_theshold ? true : false;
        if ($is_new){ $this_challenge_label = '(New!) '.$this_challenge_label; }

        if (!empty($this_victories_index[$this_challenge_id])){
            $victory_results = $this_victories_index[$this_challenge_id];
            $victory_points = self::calculate_challenge_reward_points($this_challenge_kind, $victory_results, $victory_percent, $victory_rank);
            //$this_challenge_label = '&#9733; '.$victory_rank.'-RANK CLEAR! | '.$this_challenge_label;
            $this_challenge_label = '&#9733; '.$this_challenge_label.' | '.$victory_rank.'-RANK CLEAR!';
            $this_challenge_name = '&#9733; '.$this_challenge_name;
        }

        $option_value = $this_challenge_id;
        if ($this_challenge_kind == 'user'){ $option_value = 'u'.$option_value; }
        $this_challenge_field_option = '<option '.
            'value="'.$option_value.'" '.
            'title="'.$this_challenge_title_plain.'" '.
            'data-label="'.$this_challenge_name.'" '.
            'data-type="'.$this_challenge_field_type1.'" '.
            'data-type2="'.$this_challenge_field_type2.'" '.
            'data-tooltip="'.$this_challenge_title_tooltip.'" '.
            'data-tooltip-type="field_type field_type_'.($this_challenge_field_type1).(($this_challenge_field_type2 != 'none' && $this_challenge_field_type2 != $this_challenge_field_type1) ? '_'.$this_challenge_field_type2 : '').'" '.
            'data-background="'.$this_field_data['field_background'].'" '.
            'data-foreground="'.$this_field_data['field_foreground'].'" '.
            '>'.$this_challenge_label.'</option>';

        return $this_challenge_field_option;

    }

}
?>