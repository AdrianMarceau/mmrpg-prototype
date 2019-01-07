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
    public function rpg_mission(){ }

    // Define a function for recalculation a mission's battle zenny and turns
    public static function calculate_mission_zenny_and_turns(&$this_battle_omega, $this_prototype_data, $mmrpg_robots_index){

        // Collect the base battle index and completion records for reference
        $temp_index_battle = rpg_battle::get_index_info($this_battle_omega['battle_token']);
        $temp_battle_complete = mmrpg_prototype_battle_complete($this_prototype_data['this_player_token'], $this_battle_omega['battle_token']);

        // Dynamically recalculate reward zenny and turns based on robot counts
        $this_battle_omega['battle_zenny'] = 0;
        $this_battle_omega['battle_turns'] = 0;
        if (isset($this_battle_omega['battle_target_player']['player_robots'])){ $temp_battle_target_robots = $this_battle_omega['battle_target_player']['player_robots']; }
        else { $temp_battle_target_robots = $temp_index_battle['battle_target_player']['player_robots']; }
        foreach ($temp_battle_target_robots AS $robot_key => $robot_info){
            $robot_index = $mmrpg_robots_index[$robot_info['robot_token']];
            $robot_level = !empty($robot_info['robot_level']) ? $robot_info['robot_level'] : 1;
            if ($robot_index['robot_class'] == 'master' || $robot_index['robot_class'] == 'boss'){
                $this_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $robot_level);
                $this_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
            } elseif ($robot_index['robot_class'] == 'mecha'){
                $this_battle_omega['battle_zenny'] += ceil(MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL2 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER * $robot_level);
                $this_battle_omega['battle_turns'] += MMRPG_SETTINGS_BATTLETURNS_PERMECHA;
            }
        }

        // Reduce the zenny earned from this mission each time it is completed
        if ($temp_battle_complete['battle_count'] > 0){ $this_battle_omega['battle_zenny'] = ceil($this_battle_omega['battle_zenny'] * (2 / (2 + $temp_battle_complete['battle_count']))); }

    }

}
?>