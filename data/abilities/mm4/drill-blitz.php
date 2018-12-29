<?
// DRILL BLITZ
$ability = array(
    'ability_name' => 'Drill Blitz',
    'ability_token' => 'drill-blitz',
    'ability_game' => 'MM04',
    'ability_group' => 'MM04/Weapons/027',
    'ability_description' => 'The user fires off a pair of sharp drills toward the target that deal damage on contact and remove any boosts to their defense stat!',
    'ability_type' => 'earth',
    'ability_type2' => 'missile',
    'ability_energy' => 8,
    'ability_damage' => 12,
    'ability_accuracy' => 92,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Attach three whirlwind attachments to the robot
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(0),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 0)
            );
        $this_robot->robot_attachments[$this_attachment_token.'_2'] = $this_attachment_info;
        $this_robot->robot_attachments[$this_attachment_token.'_2']['ability_frame_offset'] = array('x' => 95, 'y' => 25, 'z' => 10);
        $this_robot->update_session();

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 95, -25, 10, 'The '.$this_ability->print_name().' fired a pair of drill-tipped missiles!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Update the two whirlwind's animation frames
        $this_robot->robot_attachments[$this_attachment_token.'_2']['ability_frame'] = 0;
        $this_robot->update_session();

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(5, 0, 0),
            'success' => array(1, -80, -25, 10, 'A drill hit!'),
            'failure' => array(1, -100, -25, -10, 'One of the drills missed!')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(1, -80, -25, 10, 'A drill was absorbed!'),
            'failure' => array(1, -100, -25, -10, 'One of the drills missed!')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Ensure the target is not disabled before apply a stat change
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'
            && $target_robot->counters['defense_mods'] > 0){

            // Call the global stat break function with customized options
            rpg_ability::ability_function_stat_break($target_robot, 'defense', $target_robot->counters['defense_mods']);

        }

        // Ensure the target has not been disabled
        if ($target_robot->robot_status != 'disabled'){

            // Define the success/failure text variables
            $success_text = '';
            $failure_text = '';

            // Adjust damage/recovery text based on results
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another drill hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another drill missed!'; }

            // Remove the second extra whirlwind attached to the robot
            if (isset($this_robot->robot_attachments[$this_attachment_token.'_2'])){
                unset($this_robot->robot_attachments[$this_attachment_token.'_2']);
                $this_robot->update_session();
            }

            // Attempt to trigger damage to the target robot again
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(10, 0, 0),
                'success' => array(1, -40, 25, 10, $success_text),
                'failure' => array(1, -60, 25, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(1, -40, 25, 10, $success_text),
                'failure' => array(1, -60, 25, -10, $failure_text)
                ));
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Ensure the target is not disabled before apply a stat change
            if ($target_robot->robot_status != 'disabled'
                && $this_ability->ability_results['this_result'] != 'failure'
                && $target_robot->counters['defense_mods'] > 0){

                // Call the global stat break function with customized options
                rpg_ability::ability_function_stat_break($target_robot, 'defense', $target_robot->counters['defense_mods']);

            }

        }

        // Remove the second whirlwind
        if (isset($this_robot->robot_attachments[$this_attachment_token.'_2'])){
            unset($this_robot->robot_attachments[$this_attachment_token.'_2']);
            $this_robot->update_session();
        }

        // Return true on success
        return true;

    }
    );
?>