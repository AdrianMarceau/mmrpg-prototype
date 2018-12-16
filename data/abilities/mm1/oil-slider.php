<?
// OIL SLIDER
$ability = array(
    'ability_name' => 'Oil Slider',
    'ability_token' => 'oil-slider',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/00B',
    'ability_description' => 'The user quickly slides toward the target on a wave of crude oil to inflict damage and raise the user\'s speed stat!',
    'ability_type' => 'earth',
    'ability_type2' => 'impact',
    'ability_energy' => 8,
    'ability_speed' => 2,
    'ability_damage' => 22,
    'ability_accuracy' => 92,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'slide',
            'kickback' => array(150, 0, 0),
            'success' => array(0, 15, -10, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(1, -65, -10, 10, 'The '.$this_ability->print_name().' crashes into the target!'),
            'failure' => array(0, -85, -5, -10, 'The '.$this_ability->print_name().' continued past the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(1, -35, -10, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(1, -65, -5, -10, 'The '.$this_ability->print_name().' continued past the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Call the global stat boost function with customized options
        rpg_ability::ability_function_stat_boost($this_robot, 'speed', 1);

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