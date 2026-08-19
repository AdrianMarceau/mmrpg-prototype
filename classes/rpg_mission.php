<?php
/**
 * Mega Man RPG Mission
 * <p>The global mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission {

    /**
     * Create a new RPG mission game object.
     * This is a wrapper class for static functions,
     * so object initialization is not necessary.
     */
    public function __construct(){ }

    // Define a function for generating the BONUS missions
    public static function generate_mission($this_prototype_data, $battle_token, $battle_config, $save_to_index = true){
        //error_log('rpg_mission::generate_mission($this_prototype_data, \''.$battle_token.'\', $battle_config, '.($save_to_index ? 'true' : 'false').')');
        if (empty($battle_token) || empty($battle_config) || !is_array($battle_config)){ return false; }

        // Collect the session token
        $session_token = mmrpg_game_token();

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

        // Collect the types index for calculation purposes
        $mmrpg_index_players = rpg_player::get_index();
        $mmrpg_index_robots = rpg_robot::get_index(true);
        $mmrpg_index_types = rpg_type::get_index();

        $temp_target_userid = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_target_playerid = rpg_game::unique_player_id($temp_target_userid, 0);

        $battle_omega = array();
        $battle_omega['flags'] = !empty($battle_config['flags']) ? $battle_config['flags'] : array();
        $battle_omega['counters'] = !empty($battle_config['counters']) ? $battle_config['counters'] : array();
        $battle_omega['values'] = !empty($battle_config['values']) ? $battle_config['values'] : array();
        $battle_omega['battle_token'] = !empty($battle_config['token']) ? $battle_config['token'] : $battle_token;
        $battle_omega['battle_size'] = !empty($battle_config['size']) ? $battle_config['size'] : '1x4';
        $battle_omega['battle_phase'] = !empty($battle_config['phase']) ? $battle_config['phase'] : '';
        $battle_omega['battle_name'] = !empty($battle_config['name']) ? $battle_config['name'] : 'Undefined Battle';
        $battle_omega['battle_description'] = !empty($battle_config['description']) ? $battle_config['description'] : '';
        $battle_omega['battle_description2'] = !empty($battle_config['description2']) ? $battle_config['description2'] : '';
        $battle_omega['battle_field_base'] = array(); //$battle_config['field'] || array();
        $battle_omega['battle_target_player'] = array(); //$battle_config['target'] || array();
        $battle_omega['battle_rewards'] = !empty($battle_config['rewards']) ? $battle_config['rewards'] : array();
        $battle_omega['battle_turns'] = !empty($battle_config['turns']) ? $battle_config['turns'] : 0;
        $battle_omega['battle_zenny'] = !empty($battle_config['zenny']) ? $battle_config['zenny'] : 0;
        $battle_omega['battle_complete'] = isset($battle_config['complete']) ? $battle_config['complete'] : false;
        $battle_omega['battle_counts'] = isset($battle_config['counts']) ? $battle_config['counts'] : false;
        //error_log('rpg_mission::generate_mission()::'.__LINE__.' | $battle_omega = '.print_r($battle_omega, true));

        $field_config = array();
        if (!empty($battle_config['battle_field_base'])){ $field_config = $battle_config['battle_field_base']; }
        elseif (!empty($battle_config['field_base'])){ $field_config = $battle_config['field_base']; }
        elseif (!empty($battle_config['field'])){ $field_config = $battle_config['field']; }
        $field_base = array();
        $field_base['field_id'] = 100;
        $field_base['field_token'] = 'field';
        if (!empty($field_config)){
            if (is_string($field_config)){
                if (strstr($field_config, '/')){
                    list($background, $foreground) = explode('/', $field_config);
                    $field_base['field_token'] = $background;
                    $field_base['field_background'] = $background;
                    $field_base['field_foreground'] = $foreground;
                } else {
                    $field_base['field_token'] = $field_config;
                }
            } elseif (is_array($field_config)){
                if (isset($field_config['id'])){ $field_base['field_id'] = $field_config['id']; }
                if (isset($field_config['token'])){ $field_base['field_token'] = $field_config['token']; }
                if (isset($field_config['multipliers'])){ $field_base['field_multipliers'] = $field_config['multipliers']; }
                if (isset($field_config['foreground'])){ $field_base['field_foreground'] = $field_config['foreground']; }
                if (isset($field_config['background'])){ $field_base['field_background'] = $field_config['background']; }
            }
        }
        if (!empty($battle_config['music']) && is_string($battle_config['music'])){
            $field_music = $battle_config['music'];
            if (!strstr($battle_config['music'], '/')){ $field_music = 'sega-remix/'.$field_music; }
            $field_base['field_music'] = $field_music;
        }
        $battle_omega['battle_field_base'] = $field_base;
        //error_log('rpg_mission::generate_mission()::'.__LINE__.' | $field_base = '.print_r($field_base, true));
        //error_log('rpg_mission::generate_mission()::'.__LINE__.' | $battle_omega = '.print_r($battle_omega, true));

        $target_config = array();
        if (!empty($battle_config['battle_target_player'])){ $target_config = $battle_config['battle_target_player']; }
        elseif (!empty($battle_config['target_player'])){ $target_config = $battle_config['target_player']; }
        elseif (!empty($battle_config['target'])){ $target_config = $battle_config['target']; }
        $target_player = array();
        $target_player['user_id'] = $temp_target_userid;
        $target_player['player_id'] = $temp_target_playerid;
        $target_player['player_token'] = 'player';
        $target_player['player_name'] = 'Player';
        $target_player['player_robots'] = array();
        if (!empty($target_config)){
            if (is_string($target_config)){
                $target_player['player_token'] = $target_config;
            } elseif (is_array($target_config)){
                if (isset($target_config['id'])){ $target_player['player_id'] = $target_config['id']; }
                if (isset($target_config['token'])){ $target_player['player_token'] = $target_config['token']; }
                if (isset($target_config['image'])){ $target_player['player_image'] = $target_config['image']; }
                if (isset($target_config['name'])){ $target_player['player_name'] = $target_config['name']; }
                if (isset($target_config['flags'])){ $target_player['flags'] = $target_config['flags']; }
                if (isset($target_config['counters'])){ $target_player['counters'] = $target_config['counters']; }
                if (isset($target_config['values'])){ $target_player['values'] = $target_config['values']; }
                if (isset($target_config['robots'])){
                    foreach ($target_config['robots'] AS $key => $robot){
                        $robot_config = $robot;
                        $robot_data = array();
                        $robot_data['robot_id'] = 0;
                        $robot_data['robot_token'] = '';
                        $robot_data['robot_item'] = '';
                        $robot_data['robot_level'] = 1;
                        $robot_data['robot_abilities'] = array();
                        if (!empty($robot_config)){
                            if (is_string($robot_config)){
                                $robot_data['robot_token'] = $robot_config;
                                } elseif (is_array($robot_config)){
                                if (isset($robot_config['id'])){ $robot_data['robot_id'] = $robot_config['id']; }
                                if (isset($robot_config['token'])){ $robot_data['robot_token'] = $robot_config['token']; }
                                if (isset($robot_config['item'])){ $robot_data['robot_item'] = $robot_config['item']; }
                                if (isset($robot_config['level'])){ $robot_data['robot_level'] = $robot_config['level']; }
                                if (isset($robot_config['abilities'])){ $robot_data['robot_abilities'] = $robot_config['abilities']; }
                                if (isset($robot_config['flags'])){ $robot_data['flags'] = $robot_config['flags']; }
                                if (isset($robot_config['counters'])){ $robot_data['counters'] = $robot_config['counters']; }
                                if (isset($robot_config['values'])){ $robot_data['values'] = $robot_config['values']; }
                            }
                        }
                        if (empty($robot_data['robot_token'])){ continue; }
                        $target_player['player_robots'][] = $robot_data;
                    }
                }
            }
        }
        if (!empty($target_player['player_robots'])){
            foreach ($target_player['player_robots'] AS $robot_key => $robot_data){
                $robot_token = $robot_data['robot_token'];
                $robot_level = $robot_data['robot_level'];
                $robot_item = $robot_data['robot_item'];
                $robot_info = $mmrpg_index_robots[$robot_token];
                $robot_native_abilities = isset($robot_info['robot_rewards']['abilities']) ? $robot_info['robot_rewards']['abilities'] : array();
                $robot_info_plus_data = array_merge($robot_info, $robot_data);
                //error_log('$robot_info ='.print_r($robot_info, true));
                //error_log('$robot_native_abilities ='.print_r($robot_native_abilities, true));
                if (empty($robot_data['robot_id'])){
                    $auto_robot_id = rpg_game::unique_robot_id($temp_target_playerid, $robot_info['robot_id'], $robot_key);
                    $robot_data['robot_id'] = $auto_robot_id;
                    }
                if (empty($robot_data['robot_abilities'])){
                    $auto_ability_min = max(count($robot_native_abilities), 1);
                    $auto_ability_num = ($auto_ability_min + round(($robot_level/100) * (8 - $auto_ability_min)));
                    $auto_ability_list = mmrpg_prototype_generate_abilities($robot_info_plus_data, $robot_level, $auto_ability_num, $robot_item);
                    $robot_data['robot_abilities'] = $auto_ability_list;
                    }
                $target_player['player_robots'][$robot_key] = $robot_data;
            }
        }
        $battle_omega['battle_target_player'] = $target_player;
        //error_log('rpg_mission::generate_mission()::'.__LINE__.' | $battle_omega = '.print_r($battle_omega, true));

        if ($save_to_index){ rpg_battle::update_index_info($battle_token, $battle_omega); }

        // Return the generated battle data
        return $battle_omega;

    }

    // Define a function for recalculation a mission's battle zenny and turns
    public static function calculate_mission_zenny_and_turns(&$this_battle_omega, $this_prototype_data, $this_start_level = 1, $mmrpg_robots_index = array()){

        // Collect the base battle index and completion records for reference
        if (empty($mmrpg_robots_index) || !is_array($mmrpg_robots_index)){ $mmrpg_robots_index = rpg_robot::get_index(true); }
        $temp_index_battle = rpg_battle::get_index_info($this_battle_omega['battle_token']);
        $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $this_battle_omega['battle_token']);

        // Dynamically recalculate reward zenny and turns based on robot counts
        $this_battle_omega['battle_zenny'] = 0;
        $this_battle_omega['battle_turns'] = 0;
        if (isset($this_battle_omega['battle_target_player']['player_robots'])){ $temp_battle_target_robots = $this_battle_omega['battle_target_player']['player_robots']; }
        else { $temp_battle_target_robots = $temp_index_battle['battle_target_player']['player_robots']; }
        foreach ($temp_battle_target_robots AS $robot_key => $robot_info){
            $robot_index = $mmrpg_robots_index[$robot_info['robot_token']];
            $robot_level = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : $this_start_level;
            if ($robot_index['robot_class'] == 'master' || $robot_index['robot_class'] == 'boss'){
                $this_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $robot_level) * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
                $this_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
            } elseif ($robot_index['robot_class'] == 'mecha'){
                $this_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $robot_level) * MMRPG_SETTINGS_BATTLETURNS_PERMECHA;
                $this_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA;
            } elseif ($robot_index['robot_class'] == 'boss'){
                $this_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $robot_level) * MMRPG_SETTINGS_BATTLETURNS_PERBOSS;
                $this_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERBOSS;
            }
        }

        // Reduce the zenny earned from this mission each time it is completed
        if ($temp_battle_complete > 0){ $this_battle_omega['battle_zenny'] = ceil($this_battle_omega['battle_zenny'] * (2 / (2 + $temp_battle_complete))); }

    }

    // Define a function that takes an existing battle array and then updates it with campaign context
    public static function insert_context(&$this_battle_omega, $this_prototype_data){
        //error_log('$this_battle_omega '.$this_battle_omega['battle_token']);

        // Add the current chapter and other contextual details about this battle to the info
        global $db;
        $this_context = array();
        $this_context['player'] = $this_prototype_data['this_player_token'];
        $this_context['chapter'] = $this_prototype_data['this_current_chapter'] + 1;
        $this_context['phase'] = $this_prototype_data['battle_phase'] + 1;
        $this_context['round'] = isset($this_battle_omega['battle_round']) ? $this_battle_omega['battle_round'] : 1;
        $this_battle_omega['values']['context'] = $this_context;

        //if (!strstr($this_battle_omega['battle_token'], 'dr-cossack-fortress-iv')){ return; }
        //error_log('context = '.print_r($this_battle_omega['values']['context'], true));

    }

    // Define a function for easily checking if a given context is the "endgame" final battle
    public static function is_endgame($context){
        //error_log('rpg_mission::context_is_endgame '.$this_battle_omega['battle_token']);
        $context_is_endgame = false;
        if (!isset($context['player'])){ $context['player'] = ''; }
        if (!isset($context['chapter'])){ $context['chapter'] = 0; }
        if (!isset($context['round'])){ $context['round'] = 0; }
        if ($context['player'] === 'dr-cossack'
            && $context['chapter'] === 5
            && $context['round'] > 1){
            $context_is_endgame = true;
        }
        return $context_is_endgame;
    }

}
?>
