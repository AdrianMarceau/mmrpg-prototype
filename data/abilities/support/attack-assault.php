<?
// ATTACK ASSAULT
$ability = array(
    'ability_name' => 'Attack Assault',
    'ability_token' => 'attack-assault',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Attack2',
    'ability_description' => 'The user triggers weapon damage to all robots on the target\'s side of the field to lower their attack stats!',
    'ability_energy' => 6,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array('frame' => 'summon', 'success' => array(0, 0, 0, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($target_robot, 'attack', 1, $this_ability);

        // Loop through the target player's active bots and lower their stats
        $backup_robots_active = $target_player->values['robots_active'];
        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
        if ($backup_robots_active_count > 0){
            $this_key = 0;
            foreach ($backup_robots_active AS $key => $info){
                if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                rpg_ability::ability_function_stat_break($temp_target_robot, 'attack', 1, $this_ability);
                $this_key++;
            }
        }

        // Return true on success
        return true;

    }
    );
?>