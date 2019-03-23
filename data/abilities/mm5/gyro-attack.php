<?
// GYRO ATTACK
$ability = array(
    'ability_name' => 'Gyro Attack',
    'ability_token' => 'gyro-attack',
    'ability_game' => 'MM05',
    //'ability_group' => 'MM05/Weapons/036',
    'ability_group' => 'MM05/Weapons/033T2',
    'ability_description' => 'The user throws a giant helicopter blade toward the target that deals damage and then loops around to hit another robot on the way back! Even if this ability misses the first time, there\'s still a chance the second will hit!',
    'ability_type' => 'wind',
    'ability_type2' => 'cutter',
    'ability_energy' => 8,
    'ability_damage' => 20,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 180, 0, 10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(1, -140, 0, 10, 'The '.$this_ability->print_name().'\s blades cut through the target!'),
            'failure' => array(2, -160, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(1, -140, 0, 10, 'The '.$this_ability->print_name().'\'s blades honed the target!'),
            'failure' => array(2, -160, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Ensure there are active robots left to attack
        if (!empty($target_player->values['robots_active'])){


            // Collect the last robot on the opponent's side of the field
            $last_target_info = array_pop((array_slice($target_player->values['robots_active'], -1)));
            $last_target_info = array('robot_id' => $last_target_info['robot_id'], 'robot_token' => $last_target_info['robot_token']);
            if ($last_target_info['robot_id'] != $target_robot->robot_id){ $last_target_robot = rpg_game::get_robot($this_battle, $target_player, $last_target_info); }
            else { $last_target_robot = $target_robot; }

            // If the (possibly) new target is not disabled, strike again
            if ($last_target_robot->robot_status != 'disabled'){

                // Reset any results from previous hit
                $this_ability->ability_results_reset();

                // Inflict damage on the opposing robot
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'kickback' => array(-10, 0, 0),
                    'success' => array(3, 140, 0, 10, 'And there\'s the second hit!'),
                    'failure' => array(0, 160, 0, -10, 'The second hit missed!')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'kickback' => array(-5, 0, 0),
                    'frame' => 'taunt',
                    'success' => array(3, 140, 0, 10, 'Oh no! Not again!'),
                    'failure' => array(0, 160, 0, -10, 'Oh! The second hit missed!')
                    ));
                $last_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

            }

        }

        // Loop through all robots on the target side and disable any that need it
        $target_robots_active = $target_player->get_robots();
        foreach ($target_robots_active AS $key => $robot){
            if ($robot->robot_id == $target_robot->robot_id){ $temp_target_robot = $target_robot; }
            else { $temp_target_robot = $robot; }
            if (($temp_target_robot->robot_energy < 1 || $temp_target_robot->robot_status == 'disabled')
                && empty($temp_target_robot->flags['apply_disabled_state'])){
                $temp_target_robot->trigger_disabled($this_robot);
            }
        }

        // Return true on success
        return true;

    }
    );
?>