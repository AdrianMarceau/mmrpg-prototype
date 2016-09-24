<?
// ENERGY BREAK
$ability = array(
    'ability_name' => 'Energy Break',
    'ability_token' => 'energy-break',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Energy',
    'ability_description' => 'The user breaks down the target&#39;s internal systems, lowering its energy by {DAMAGE}%!',
    'ability_energy' => 6,
    'ability_damage' => 15,
    'ability_damage_percent' => true,
    'ability_accuracy' => 95,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, -2, 0, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Decrease the target robot's attack stat
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'kickback' => array(10, 0, 0),
            'success' => array(0, -2, 0, -10, $target_robot->print_name().'&#39;s systems were damaged!'),
            'failure' => array(9, -2, 0, -10, 'It had no effect on '.$target_robot->print_name().'&hellip;')
            ));
        $energy_damage_amount = ceil($target_robot->robot_base_energy * ($this_ability->ability_damage / 100));
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If used by support robot OR the has a Target Module, allow bench targetting
        $temp_support_robots = array('roll', 'disco', 'rhythm');
        if (in_array($this_robot->robot_token, $temp_support_robots)
            || $this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>