<?
// SEARCH SNAKE
$ability = array(
    'ability_name' => 'Search Snake',
    'ability_token' => 'search-snake',
    'ability_game' => 'MM03',
    //'ability_group' => 'MM03/Weapons/022',
    'ability_group' => 'MM03/Weapons/019x',
    'ability_master' => 'snake-man',
    'ability_number' => 'DWN-022',
    'ability_description' => 'The user fires a series of three remote-controlled snake robots that seek out any target to deal high damage with perfect accuracy! Damage done by this ability is not affected by distance and can be used even on far away opponents.',
    'ability_type' => 'nature',
    'ability_energy' => 4,
    'ability_damage' => 10,
    'ability_accuracy' => 100,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Attach three object attachments to the robot
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(0,1),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 0)
            );
        $this_attachment_info1 = $this_attachment_info;
        $this_attachment_info1['ability_id'] .= '01';
        $this_attachment_info1['ability_frame_offset'] = array('x' => 95, 'y' => 14, 'z' => -15);
        $this_attachment_info1['ability_frame_animate'] = array(8,9);
        $this_attachment_info2 = $this_attachment_info;
        $this_attachment_info2['ability_id'] .= '02';
        $this_attachment_info2['ability_frame_offset'] = array('x' => 65, 'y' => -14, 'z' => -10);
        $this_attachment_info2['ability_frame_animate'] = array(9,8);
        $this_robot->set_attachment($this_attachment_token.'_1', $this_attachment_info1);
        $this_robot->set_attachment($this_attachment_token.'_2', $this_attachment_info2);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 135, 0, 10, $this_robot->print_name().' fires a series of '.$this_ability->print_name(true).'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Update the two object's animation frames
        $this_attachment_info1['ability_frame'] = 0;
        $this_attachment_info2['ability_frame'] = 0;
        $this_robot->set_attachment($this_attachment_token.'_1', $this_attachment_info1);
        $this_robot->set_attachment($this_attachment_token.'_2', $this_attachment_info2);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(1, -45, 0, 10, 'The '.$this_ability->print_name().' collided with the target!'),
            'failure' => array(1, -105, 0, -10, 'The '.$this_ability->print_name().' slithered past the target&hellip;'),
            'options' => array('apply_position_modifiers' => false)
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(1, -45, 0, 10, 'The '.$this_ability->print_name().' healed the target!'),
            'failure' => array(1, -105, 0, -10, 'The '.$this_ability->print_name().' slithered past the target&hellip;'),
            'options' => array('apply_position_modifiers' => false)
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Ensure the target has not been disabled
        if ($target_robot->robot_status != 'disabled'){

            // Define the success/failure text variables
            $success_text = '';
            $failure_text = '';

            // Adjust damage/recovery text based on results
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another snake hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another snake missed!'; }

            // Remove the second extra object attached to the robot
            if ($this_robot->has_attachment($this_attachment_token.'_2')){
                $this_robot->unset_attachment($this_attachment_token.'_2');
            }

            // Update the remaining object's animation frame
            $this_attachment_info1['ability_frame'] = 0;
            $this_robot->set_attachment($this_attachment_token.'_1', $this_attachment_info1);

            // Attempt to trigger damage to the target robot again
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(10, 0, 0),
                'success' => array(1, -40, 5, 10, $success_text),
                'failure' => array(1, -60, 5, -10, $failure_text),
                'options' => array('apply_position_modifiers' => false)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(1, -40, 5, 10, $success_text),
                'failure' => array(1, -60, 5, -10, $failure_text),
                'options' => array('apply_position_modifiers' => false)
                ));
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Ensure the target has not been disabled
            if ($target_robot->robot_status != 'disabled'){

                // Adjust damage/recovery text based on results again
                if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another snake hit!'; }
                elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A third snake hit!'; }
                if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another snake missed!'; }
                elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A third snake missed!'; }

                // Remove the first extra object
                if ($this_robot->has_attachment($this_attachment_token.'_1')){
                    $this_robot->unset_attachment($this_attachment_token.'_1');
                }

                // Attempt to trigger damage to the target robot a third time
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'kickback' => array(15, 0, 0),
                    'success' => array(1, -70, 5, 10, $success_text),
                    'failure' => array(1, -90, 5, -10, $failure_text),
                    'options' => array('apply_position_modifiers' => false)
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'frame' => 'taunt',
                    'kickback' => array(0, 0, 0),
                    'success' => array(1, -70, 5, 10, $success_text),
                    'failure' => array(1, -90, 5, -10, $failure_text),
                    'options' => array('apply_position_modifiers' => false)
                    ));
                $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            }

        }

        // Remove the second object
        if ($this_robot->has_attachment($this_attachment_token.'_2')){
            $this_robot->unset_attachment($this_attachment_token.'_2');
        }

        // Remove the third object
        if ($this_robot->has_attachment($this_attachment_token.'_1')){
            $this_robot->unset_attachment($this_attachment_token.'_1');
        }

        // Return true on success
        return true;

    }
    );
?>