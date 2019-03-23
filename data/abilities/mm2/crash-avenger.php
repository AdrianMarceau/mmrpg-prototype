<?
// CRASH AVENGER
$ability = array(
    'ability_name' => 'Crash Avenger',
    'ability_token' => 'crash-avenger',
    'ability_game' => 'MM02',
    //'ability_group' => 'MM02/Weapons/013',
    'ability_group' => 'MM02/Weapons/009T2',
    'ability_image_sheets' => 9,
    'ability_description' => 'The user enters a fit of rage and throws a large spike-rimmed explosive at the target to deal damage! This ability grows in power for each disabled ally robot on the user\'s side of the field.',
    'ability_type' => 'explode',
    'ability_type2' => 'shadow',
    'ability_energy' => 8,
    'ability_damage' => 15,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 85, 35, 10, $this_robot->print_name().' thows the '.$this_ability->print_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'frame' => 'damage',
            'kickback' => array(15, 5, 0),
            'success' => array(1, 0, 0, 10, 'The '.$this_ability->print_name().' exploded on contact!'),
            'failure' => array(0, -65, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(1, 0, 0, 10, 'The '.$this_ability->print_name().' exploded on contact!'),
            'failure' => array(0, -65, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Count how many ally robots have been disabled
        $num_disabled = $this_player->counters['robots_disabled'];

        // Define the new ability name, image, and damage based on disabled bots
        $numerals = array(1 => ' I', 2 => ' II', 3 => ' III', 4 => ' IV', 5 => ' V', 6 => ' VI', 7 => ' VII', 8 => ' VIII');
        $new_ability_name = $this_ability->ability_base_name.($num_disabled > 0 ? $numerals[$num_disabled + 1] : '');
        $new_ability_damage = $this_ability->ability_base_damage + ($num_disabled * $this_ability->ability_base_damage);
        $new_ability_image = $this_ability->ability_token.'-'.(1 + $num_disabled);

        // Update the ability image and damage to calculated values
        $this_ability->set_name($new_ability_name);
        $this_ability->set_damage($new_ability_damage);
        $this_ability->set_image($new_ability_image);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>