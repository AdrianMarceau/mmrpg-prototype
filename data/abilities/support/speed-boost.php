<?
// SPEED BOOST
$ability = array(
    'ability_name' => 'Speed Boost',
    'ability_token' => 'speed-boost',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Speed',
    'ability_description' => 'The user optimizes internal systems to improve mobility and raise speed by {RECOVERY}% of its base value!',
    'ability_energy' => 4,
    'ability_recovery' => 15,
    'ability_recovery_percent' => true,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 0, 10, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // If this ability is being used by a special support robot that allows targetting
        if ($this_robot->player_id == $target_robot->player_id){

            // Increase this robot's speed stat
            $this_ability->recovery_options_update(array(
                'kind' => 'speed',
                'percent' => true,
                'frame' => 'taunt',
                'success' => array(0, -2, 0, -10, $target_robot->print_name().'&#39;s mobility improved!'),
                'failure' => array(9, -2, 0, -10, $target_robot->print_name().'&#39;s mobility was not affected&hellip;')
                ));
            $speed_recovery_amount = ceil($target_robot->robot_base_speed * ($this_ability->ability_recovery / 100));
            $target_robot->trigger_recovery($target_robot, $this_ability, $speed_recovery_amount);

        }
        // Otherwise if targetting a team mate
        else {

            // Increase the target robot's speed stat
            $this_ability->recovery_options_update(array(
                'kind' => 'speed',
                'percent' => true,
                'frame' => 'taunt',
                'success' => array(0, -2, 0, -10, $this_robot->print_name().'&#39;s mobility improved!'),
                'failure' => array(9, -2, 0, -10, $this_robot->print_name().'&#39;s mobility was not affected&hellip;')
                ));
            $speed_recovery_amount = ceil($this_robot->robot_base_speed * ($this_ability->ability_recovery / 100));
            $this_robot->trigger_recovery($this_robot, $this_ability, $speed_recovery_amount);

        }

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this ability is being used by a special support robot, allow targetting
        $temp_support_robots = array('roll', 'disco', 'rhythm');
        if (in_array($this_robot->robot_token, $temp_support_robots) && $this_player->counters['robots_active'] > 1){

            // Update this ability's targetting setting
            $this_ability->ability_target = 'select_this';
            $this_ability->update_session();

        }
        // Else if the ability attachment is not there, change the target back to auto
        else {

            // Update this ability's targetting setting
            $this_ability->ability_target = 'auto';
            $this_ability->update_session();

        }

        // Return true on success
        return true;

        }
    );
?>