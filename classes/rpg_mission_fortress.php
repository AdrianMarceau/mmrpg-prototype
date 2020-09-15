<?php
/**
 * Mega Man RPG Fotress-Battle Mission
 * <p>The fortress mission class for the Mega Man RPG Prototype.</p>
 */
class rpg_mission_fortress extends rpg_mission {

    public static function prepare(&$this_fortress_battle, $this_prototype_data){

        // Pull in required object indexes
        static $mmrpg_players_index, $mmrpg_robots_index;
        if (empty($mmrpg_players_index)){ $mmrpg_players_index = rpg_player::get_index(true); }
        if (empty($mmrpg_robots_index)){ $mmrpg_robots_index = rpg_robot::get_index(true); }

        // Update the target user ID, player ID, and robot IDs
        $temp_target_user_id = MMRPG_SETTINGS_TARGET_PLAYERID;
        $temp_target_player_id = rpg_game::unique_player_id($temp_target_user_id, 0);
        if ($this_fortress_battle['battle_target_player']['player_token'] !== 'player'){
            $temp_target_player_info = $mmrpg_players_index[$this_fortress_battle['battle_target_player']['player_token']];
            $temp_target_player_id = rpg_game::unique_player_id($temp_target_user_id, $temp_target_player_info['player_id']);
        }
        $temp_battle_target_player = array('user_id' => 0, 'player_id' => 0);
        $this_fortress_battle['battle_target_player'] = array_merge($temp_battle_target_player, $this_fortress_battle['battle_target_player']);
        $this_fortress_battle['battle_target_player']['user_id'] = $temp_target_user_id;
        $this_fortress_battle['battle_target_player']['player_id'] = $temp_target_player_id;

        // Loop through target robots and re-generate unique IDs for each of them
        foreach ($this_fortress_battle['battle_target_player']['player_robots'] AS $key => $robot){
            $temp_target_robot_info = $mmrpg_robots_index[$robot['robot_token']];
            $temp_target_robot_id = rpg_game::unique_robot_id($temp_target_player_id, $temp_target_robot_info['robot_id'], ($key + 1));
            $this_fortress_battle['battle_target_player']['player_robots'][$key]['robot_id'] = $temp_target_robot_id;
        }

        // Calcuate appropriate zenny prizes and turn limits
        rpg_mission::calculate_mission_zenny_and_turns($this_fortress_battle, $this_prototype_data, $mmrpg_robots_index);

    }


}
?>