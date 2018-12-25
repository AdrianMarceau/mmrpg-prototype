<?
// FROST WAVE
$ability = array(
    'ability_name' => 'Frost Wave',
    'ability_token' => 'frost-wave',
    'ability_game' => 'MM08',
    'ability_group' => 'MM08/Weapons/062',
    'ability_description' => 'The user summons a wave of frozen spikes that race toward the target and collide to inflict damage and lower the target\'s attack stat!',
    'ability_type' => 'freeze',
    'ability_energy' => 4,
    'ability_speed' => 3,
    'ability_damage' => 10,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If the user is Frost Man, wind-up first
        if ($this_robot->robot_token === 'frost-man'){ $this_robot->set_frame('summon'); $this_battle->events_create(false, false, '', ''); }

        // Target the opposing robot
        $x_position = 80 + ($this_robot->robot_image_size / 2);
        $this_ability->target_options_update(array(
            'frame' => ($this_robot->robot_token === 'frost-man' ? 'throw' : 'shoot'),
            'success' => array(1, $x_position, 0, 10, $this_robot->print_name().' '.($this_robot->robot_token === 'frost-man' ? 'summons' : 'fires').' the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);
        $this_robot->set_frame('base');

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(2, -45, 0, 10, 'The '.$this_ability->print_name().' hit the target!'),
            'failure' => array(2, -65, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(2, -40, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(2, -60, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Randomly trigger a defense break if the ability was successful
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'){

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'attack', 1);

        }

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