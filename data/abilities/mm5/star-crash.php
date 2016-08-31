<?
// STAR CRASH
$ability = array(
    'ability_name' => 'Star Crash',
    'ability_token' => 'star-crash',
    'ability_game' => 'MM05',
    'ability_description' => 'The user raises a shield of tiny star-like reflectors and then throws them at a target robot to deal damage up to three times as it spins!',
    'ability_type' => 'space',
    'ability_type2' => 'shield',
    'ability_energy' => 4,
    'ability_damage' => 5,
    'ability_recovery2' => 100,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 94,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, -10, 0, -10, $this_robot->print_name().' raises a shield of stars!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(1, 100, 0, 10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(0, 0, 5, 10, 'The '.$this_ability->print_name().' collided with the target!'),
            'failure' => array(0, -50, 5, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(0, 0, 5, 10, 'The '.$this_ability->print_name().' invigorated the target!'),
            'failure' => array(0, -50, 5, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);


        // If this attack returns and strikes a second time (random chance)
        if ($this_ability->ability_results['this_result'] != 'failure'
            && $target_robot->robot_status != 'disabled'){

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(20, 0, 0),
                'success' => array(1, -40, 10, 10, 'Oh! The '.$this_ability->print_name().' hit again!'),
                'failure' => array(1, -90, 10, -10, '')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'kickback' => array(0, 0, 0),
                'frame' => 'taunt',
                'success' => array(1, -40, 10, 10, 'Oh no! Not again!'),
                'failure' => array(1, -90, 10, -10, '')
                ));
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // If this attack returns and strikes a third time (random chance)
            if ($this_ability->ability_results['this_result'] != 'failure'
                && $target_robot->robot_energy != 'disabled'){

                // Inflict damage on the opposing robot
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'kickback' => array(30, 0, 0),
                    'success' => array(2, 10, 15, -10, 'Wow! A third hit!'),
                    'failure' => array(2, 60, 15, -10, '')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'frame' => 'taunt',
                    'kickback' => array(0, 0, 0),
                    'success' => array(2, 10, 15, -10, 'What? The '.$this_ability->print_name().' recovered the target again!'),
                    'failure' => array(2, 60, 15, -10, '')
                    ));
                $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            }

        }

        // Return true on success
        return true;

    }
    );
?>