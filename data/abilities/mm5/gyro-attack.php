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
            'success' => array(1, -140, 0, 10, 'The '.$this_ability->print_name().'\'s wind invigorated the target!'),
            'failure' => array(2, -160, 0, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Ensure there are active robots left to attack
        if (!empty($target_player->values['robots_active'])){

            // If there are no benched robots, we should hit the ACTIVE robot again
            $is_same_robot = false;
            if ($target_player->counters['robots_positions']['bench'] < 1){

                // Set the target as the opposing robot again
                $temp_target_robot = $target_robot;
                $is_same_robot = true;

            }
            // Otherwise, we should hit the BENCHED robot at the top of bottom of the row
            else {

                // Collect a list of benched robots from the target
                $temp_target_robots = rpg_game::find_robots(array(
                    'player_id' => $target_player->player_id,
                    'robot_position' => 'bench',
                    'robot_status' => 'active'
                    ));

                // Sort the robots by key (very important!)
                usort($temp_target_robots, function($a, $b){
                    if ($a->robot_key < $b->robot_key){ return -1; }
                    elseif ($a->robot_key > $b->robot_key){ return 1; }
                    else { return 0; }
                    });

                // Select either the first or last robot in the list
                if ($this_battle->counters['battle_turn'] % 2 == 0){ $temp_target_robot = $temp_target_robots[0]; }
                else { $temp_target_robot = $temp_target_robots[count($temp_target_robots) - 1]; }

            }

            // If the (possibly) new target is not disabled, strike again
            if ($temp_target_robot->robot_status != 'disabled'){

                // Reset any results from previous hit if not same robot
                if (!$is_same_robot){ $this_ability->ability_results_reset(); }

                // Inflict damage on the opposing robot
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'kickback' => array(-10, 0, 0),
                    'success' => array(3, 140, 0, 10, ($is_same_robot ? 'And there\'s the second hit!' : 'The attack hit a benched robot! ')),
                    'failure' => array(0, 160, 0, -10, 'The second hit missed!')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'kickback' => array(-5, 0, 0),
                    'frame' => 'taunt',
                    'success' => array(3, 140, 0, 10, ($is_same_robot ? 'The target was healed again! ' : 'The attack healed a benched robot! ')),
                    'failure' => array(0, 160, 0, -10, 'Oh! The second hit missed!')
                    ));
                $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

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