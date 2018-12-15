<?
// DEFENSE SUPPORT
$ability = array(
    'ability_name' => 'Defense Support',
    'ability_token' => 'defense-support',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Defense2',
    'ability_description' => 'The user triggers shield optimizations for all robots on their side of the field to raise their defense stats!',
    'ability_energy' => 10,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array('frame' => 'summon', 'success' => array(0, 0, 0, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Call the global stat boost function with customized options
        rpg_ability::ability_function_stat_boost($this_robot, 'defense', 1, $this_ability);

        // Loop through this player's active bots and raise their stats
        $backup_robots_active = $this_player->values['robots_active'];
        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
        if ($backup_robots_active_count > 0){
            $this_key = 0;
            foreach ($backup_robots_active AS $key => $info){
                if ($info['robot_id'] == $this_robot->robot_id){ continue; }
                $temp_this_robot = rpg_game::get_robot($this_battle, $this_player, $info);
                rpg_ability::ability_function_stat_boost($temp_this_robot, 'defense', 1, $this_ability);
                $this_key++;
            }
        }

        // Return true on success
        return true;

    }
    );
?>