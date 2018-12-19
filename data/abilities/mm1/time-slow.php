<?
// TIME SLOW
$ability = array(
    'ability_name' => 'Time Slow',
    'ability_token' => 'time-slow',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/00A',
    'ability_description' => 'The user charges temporal energy inside itself and then unleashes it on the target to deal damage and sharply lower their speed stat!',
    'ability_type' => 'time',
    'ability_energy' => 8,
    'ability_damage' => 18,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'defend',
            'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Shift user into summon mode right before the target is hit
        $this_robot->robot_frame = 'summon';
        $this_robot->update_session();

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' was unleashed on the target!'),
            'failure' => array(9, 5, 70, -10, 'The '.$this_ability->print_name().' was ignored by the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(9, 5, 70, -10, 'The '.$this_ability->print_name().' didn\'t affect the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return the user to their base frame after attack
        $this_robot->robot_frame = 'base';
        $this_robot->update_session();

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($target_robot, 'speed', 2);

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>