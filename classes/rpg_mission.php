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

    // Define a function for recalculation a mission's battle zenny and turns
    public static function calculate_mission_zenny_and_turns(&$this_battle_omega, $this_prototype_data, $this_start_level = 1, $mmrpg_robots_index = array()){

        // Collect the base battle index and completion records for reference
        if (empty($mmrpg_robots_index) || !is_array($mmrpg_robots_index)){ $mmrpg_robots_index = rpg_robot::get_index(); }
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
