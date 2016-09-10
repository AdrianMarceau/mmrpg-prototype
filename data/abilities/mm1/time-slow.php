<?
// TIME SLOW
$ability = array(
    'ability_name' => 'Time Slow',
    'ability_token' => 'time-slow',
    'ability_game' => 'MM01',
    'ability_group' => 'MM01/Weapons/00A',
    'ability_description' => 'The user charges on the first turn and builds power then releases a wave of temporal energy on the second to deal massive speed damage to the opposing team!',
    'ability_type' => 'time',
    'ability_energy' => 8,
    'ability_damage' => 20,
    'ability_damage_percent' => true,
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
            'ability_frame_animate' => array(1, 0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // If the ability flag was not set, this ability begins charging
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

        }
        // Else if the ability flag was set, the ability is released at the target
        else {

            // Remove this ability attachment to the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

            // Update this ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'kickback' => array(0, 0, 0),
                'success' => array(5, 5, 70, 10, $this_robot->print_name().' releases the '.$this_ability->print_name().'!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'speed',
                        'percent' => 'true',
                'kickback' => array(10, 0, 0),
                'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' damaged the target&#39;s mobility!'),
                'failure' => array(9, 5, 70, -10, 'The '.$this_ability->print_name().' had no effect on '.$target_robot->print_name().'&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'speed',
                        'percent' => 'true',
                'kickback' => array(10, 0, 0),
                'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' improved the target&#39;s mobility!'),
                'failure' => array(9, 5, 70, -10, 'The '.$this_ability->print_name().' had no effect on '.$target_robot->print_name().'&hellip;')
                ));
            $energy_damage_amount = ceil(($this_ability->ability_damage / 100) * $target_robot->robot_speed);
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Randomly trigger a bench damage if the ability was successful
            $backup_robots_active = $target_player->values['robots_active'];
            $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
            if (true){

                // Loop through the target's benched robots, inflicting les and less damage to each
                $target_key = 0;
                foreach ($backup_robots_active AS $key => $info){
                    if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                    $this_ability->ability_results_reset();
                    $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                    // Inflict damage on the target robot
                    $this_ability->damage_options_update(array(
                        'kind' => 'speed',
                        'percent' => 'true',
                        'kickback' => array(10, 0, 0),
                        'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' damaged the target&#39;s mobility!'),
                        'failure' => array(9, 5, 70, -10, 'The '.$this_ability->print_name().' had no effect on '.$temp_target_robot->print_name().'&hellip;')
                        ));
                    $this_ability->recovery_options_update(array(
                        'kind' => 'speed',
                        'percent' => 'true',
                        'kickback' => array(10, 0, 0),
                        'success' => array(3, 5, 70, -10, 'The '.$this_ability->print_name().' improved the target&#39;s mobility!'),
                        'failure' => array(9, 5, 70, -10, 'The '.$this_ability->print_name().' had no effect on '.$temp_target_robot->print_name().'&hellip;')
                        ));
                    $energy_damage_amount = ceil(($this_ability->ability_damage / 100) * $temp_target_robot->robot_speed);
                    $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
                    // Increment the target key
                    $target_key++;
                }

            }

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // If the ability flag had already been set, reduce the weapon energy to zero
        if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_energy = 0; }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->ability_energy = $this_ability->ability_base_energy; }
        // Update the ability session
        $this_ability->update_session();

        // Return true on success
        return true;

        }
    );
?>