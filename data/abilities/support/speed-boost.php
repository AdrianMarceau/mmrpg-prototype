<?
// SPEED BOOST
$ability = array(
    'ability_name' => 'Speed Boost',
    'ability_token' => 'speed-boost',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Speed',
    'ability_description' => 'The user optimizes internal systems to improve mobility and sharply raise its speed stat!',
    'ability_energy' => 4,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array('frame' => 'summon', 'success' => array(0, 0, 10, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Create a reference to the target robot, whichever one it is
        if ($this_robot->player_id == $target_robot->player_id){ $temp_target_robot = $target_robot; }
        else { $temp_target_robot = $this_robot; }

        // Call the global stat boost function with customized options
        rpg_ability::ability_function_stat_boost($temp_target_robot, 'speed', 2, $this_ability);

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If used by support robot OR the has a Target Module, allow bench targetting
        $temp_support_robots = array('roll', 'disco', 'rhythm');
        if ($this_robot->robot_class == 'mecha'
            || in_array($this_robot->robot_token, $temp_support_robots)
            || $this_robot->has_item('target-module')){ $this_ability->set_target('select_this'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>