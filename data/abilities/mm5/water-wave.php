<?
// WATER WAVE
$ability = array(
    'ability_name' => 'Water Wave',
    'ability_token' => 'water-wave',
    'ability_game' => 'MM05',
    'ability_group' => 'MM05/Weapons/034',
    'ability_description' => 'The user attacks by producing up to three pressurized columns of water, each bursting under a different target to deal damage!',
    'ability_type' => 'water',
    'ability_energy' => 4,
    'ability_damage' => 14,
    'ability_accuracy' => 90,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Count the number of active robots on the target's side of the field
        $target_robots_active_count = $target_player->counters['robots_active'];
        $target_robot_ids = array($target_robot->robot_id);
        $get_next_target_robot = function() use($this_battle, $target_player, &$target_robot_ids){
            foreach ($target_player->values['robots_active'] AS $key => $info){
                if (!in_array($info['robot_id'], $target_robot_ids)){
                    $target_robot_ids[] = $info['robot_id'];
                    $next_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                    return $next_target_robot;
                    }
                }
            };

        // Attach up to two extra object attachments to the robot (for a total of three on-screen)
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(5,6,7),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 20)
            );

        // The first attachment always exists (though it's part of the attack itself)
        $this_attachment_info1 = $this_attachment_info;

        // Only add an additional waves if there are enough targets
        if ($target_robots_active_count >= 2){
            $this_attachment_info2 = $this_attachment_info;
            $this_attachment_info2['ability_id'] .= '02';
            $this_attachment_info2['ability_frame_offset'] = array('x' => 95, 'y' => 14, 'z' => 5);
            $this_attachment_info2['ability_frame_animate'] = array(6,7,5);
            $this_robot->set_attachment($this_attachment_token.'_2', $this_attachment_info2);
            $target_robot_2 = $get_next_target_robot();
        }

        // Only add an additional waves if there are enough targets
        if ($target_robots_active_count >= 3){
            $this_attachment_info3 = $this_attachment_info;
            $this_attachment_info3['ability_id'] .= '03';
            $this_attachment_info3['ability_frame_offset'] = array('x' => 65, 'y' => -14, 'z' => 10);
            $this_attachment_info3['ability_frame_animate'] = array(7,5,6);
            $this_robot->set_attachment($this_attachment_token.'_3', $this_attachment_info3);
            $target_robot_3 = $get_next_target_robot();
        }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(5, 135, 0, 10, $this_robot->print_name().' generates a series of '.$this_ability->print_name(true).'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Update the wave object's object's animation frames
        if ($this_robot->has_attachment($this_attachment_token.'_2')){
            $this_attachment_info2['ability_frame'] = 0;
            $this_robot->set_attachment($this_attachment_token.'_2', $this_attachment_info2);
        }

        // Update the wave object's object's animation frames
        if ($this_robot->has_attachment($this_attachment_token.'_3')){
            $this_attachment_info3['ability_frame'] = 0;
            $this_robot->set_attachment($this_attachment_token.'_3', $this_attachment_info3);
        }

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(1, -45, 0, 10, 'The '.$this_ability->print_name().' collided with the target!'),
            'failure' => array(4, -105, 0, -10, 'The '.$this_ability->print_name().' drifted past the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(10, 0, 0),
            'success' => array(1, -45, 0, 10, 'The '.$this_ability->print_name().' healed the target!'),
            'failure' => array(4, -105, 0, -10, 'The '.$this_ability->print_name().' drifted past the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // If a second attachment has been created, we can fire it off at a different target
        if ($this_robot->has_attachment($this_attachment_token.'_2')){

            // Define the success/failure text variables
            $success_text = '';
            $failure_text = '';

            // Adjust damage/recovery text based on results
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another column hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another column missed!'; }

            // Remove the attachment before we fire it off as an ability sprite
            if ($this_robot->has_attachment($this_attachment_token.'_2')){ $this_robot->unset_attachment($this_attachment_token.'_2'); }

            // Attempt to trigger damage to the target robot again
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(10, 0, 0),
                'success' => array(1, -45, 0, 10, $success_text),
                'failure' => array(4, -105, 0, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(1, -45, 0, 10, $success_text),
                'failure' => array(4, -105, 0, -10, $failure_text)
                ));
            $target_robot_2->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }

        // If a third attachment has been created, we can fire it off at a different target
        if ($this_robot->has_attachment($this_attachment_token.'_3')){

            // Adjust damage/recovery text based on results again
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another column hit!'; }
            elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A third column hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another column missed!'; }
            elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A third column missed!'; }

            // Remove the attachment before we fire it off as an ability sprite
            if ($this_robot->has_attachment($this_attachment_token.'_3')){ $this_robot->unset_attachment($this_attachment_token.'_3'); }

            // Attempt to trigger damage to the target robot a third time
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(15, 0, 0),
                'success' => array(1, -45, 0, 10, $success_text),
                'failure' => array(4, -105, 0, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(1, -45, 0, 10, $success_text),
                'failure' => array(4, -105, 0, -10, $failure_text)
                ));
            $target_robot_3->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }

        // Return true on success
        return true;

    }
    );
?>