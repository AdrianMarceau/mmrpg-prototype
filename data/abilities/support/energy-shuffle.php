<?
// ENERGY SHUFFLE
$ability = array(
    'ability_name' => 'Energy Shuffle',
    'ability_token' => 'energy-shuffle',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Energy2',
    'ability_description' => 'The user triggers a dangerous glitch in the prototype that randomizes their randomly restores or depletes their life energy stat!',
    'ability_energy' => 8,
    'ability_speed' => -1,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
            );

        /*
         * SHOW ABILITY TRIGGER
         */

        // Target this robot's self
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(9, 0, 10, -10, $this_robot->print_name().' triggered an '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($this_robot, $this_ability);

        // Check to see if boost or break randomly
        if (mt_rand(0, 1) == 0){

            // Increase the target robot's energy stat
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'percent' => true,
                'frame' => 'taunt',
                'success' => array(0, -2, 0, -10, $this_robot->print_name().'&#39;s energy was restored!'),
                'failure' => array(9, -2, 0, -10, $this_robot->print_name().'&#39;s energy was not affected&hellip;')
                ));
            $energy_recovery_amount = ceil($this_robot->robot_base_energy * (mt_rand(1, 100) / 100));
            if (($this_robot->robot_energy - $energy_recovery_amount) < 1){ $energy_recovery_amount -= (($energy_recovery_amount - $this_robot->robot_energy) + 1); }
            $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);

        } else {

            // Decrease the target robot's attack stat
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'percent' => true,
                'kickback' => array(10, 0, 0),
                'success' => array(0, -2, 0, -10, $target_robot->print_name().'&#39;s systems were damaged!'),
                'failure' => array(9, -2, 0, -10, 'It had no effect on '.$target_robot->print_name().'&hellip;')
                ));
            $energy_damage_amount = ceil($this_robot->robot_base_energy * (mt_rand(1, 100) / 100));
            if (($this_robot->robot_energy - $energy_recovery_amount) < 1){ $energy_recovery_amount -= (($energy_recovery_amount - $this_robot->robot_energy) + 1); }
            $this_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }

        // Return true on success
        return true;

    }
    );
?>