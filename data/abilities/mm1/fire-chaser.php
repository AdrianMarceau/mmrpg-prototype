<?
// FIRE CHASER
$ability = array(
    'ability_name' => 'Fire Chaser',
    'ability_token' => 'fire-chaser',
    'ability_game' => 'MM01',
    'ability_description' => 'The user a unleashes a powerful wave of fire that chases the target, inflicting twice as much damage if the target has higher speed than the user!',
    'ability_type' => 'flame',
    'ability_type2' => 'swift',
    'ability_energy' => 8,
    'ability_damage' => 24,
    'ability_accuracy' => 94,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update this ability's damage based on the user and target's speed
        if ($target_robot->robot_speed > $this_robot->robot_speed){
            $this_ability->set_name($this_ability->ability_base_name . ' Δ');
            $this_ability->set_damage($this_ability->ability_base_damage * 2);
        } else {
            $this_ability->reset_name();
            $this_ability->reset_damage();
        }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 100, 0, 10, $this_robot->print_name().' unleashes a '.$this_ability->print_name().'!'),
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(15, 0, 0),
            'success' => array(1, -75, 0, 10, 'The '.$this_ability->print_name().' burned through the target!'),
            'failure' => array(1, -100, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(1, -75, 0, 10, 'The '.$this_ability->print_name().' ignited the target!'),
            'failure' => array(1, -100, 0, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update this ability's damage based on the user and target's speed
        if ($target_robot->robot_speed > $this_robot->robot_speed){
            $this_ability->set_name($this_ability->ability_base_name . ' Δ');
            $this_ability->set_damage($this_ability->ability_base_damage * 2);
        } else {
            $this_ability->reset_name();
            $this_ability->reset_damage();
        }

        // Return true on success
        return true;

        }
    );
?>