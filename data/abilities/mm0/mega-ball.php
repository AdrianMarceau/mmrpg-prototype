<?
// MEGA BALL
$ability = array(
    'ability_name' => 'Mega Ball',
    'ability_token' => 'mega-ball',
    'ability_game' => 'MM08',
    'ability_group' => 'MM00/Weapons/Mega',
    'ability_description' => 'The user generates a powerful ball-shaped explosive that rocks back and forth at their feet.  At the end of the turn, the user kicks the ball at the target to deal damage and break through core shields! ',
    'ability_type' => '',
    'ability_energy' => 4,
    'ability_damage' => 32,
    'ability_accuracy' => 96,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'attachment_token' => $this_attachment_token,
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 7,
            'ability_frame_animate' => array(7, 6, 5, 4, 3, 2, 1, 0),
            'ability_frame_offset' => array('x' => 60, 'y' => 0, 'z' => 28)
            );

        // Create the attachment object for this ability
        $this_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_attachment_info);

        // If this ability has not been summoned yet, do the action and then queue a conclusion move
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        if (empty($this_robot->flags[$summoned_flag_token])){

            // Set the summoned flag on this robot and save
            $this_robot->flags[$summoned_flag_token] = true;
            $this_robot->update_session();

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(7, 60, 0, 28, $this_robot->print_name().' generates a '.$this_ability->print_name().'!<br /> The '.$this_ability->print_name().' started rolling in place&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

            // Queue another use of this ability at the end of turn
            $this_battle->actions_append(
                $this_player,
                $this_robot,
                $target_player,
                $target_robot,
                'ability',
                $this_ability->ability_id.'_'.$this_ability->ability_token,
                true
                );

        }
        // The ability has already been summoned, so we can finish executing it now and deal damage
        else {

            // Remove the summoned flag from this robot and save
            unset($this_robot->flags[$summoned_flag_token]);
            $this_robot->update_session();

            // Remove this ability attachment to the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

            // Update this ability's target options and trigger
            $this_ability->target_options_update(array(
                'frame' => 'slide',
                'kickback' => array(60, 0, 0),
                'success' => array(8, 120, 100, 28, $this_robot->print_name().' kicks the '.$this_ability->print_name().' at the target!'),
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(24, 0, 0),
                'success' => array(9, -30, 0, 28, 'The '.$this_ability->print_name().' collided with the target!'),
                'failure' => array(9, -60, 0, -10, 'The '.$this_ability->print_name().' bounced past the target&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // If the ability was successful, loop through and remove recent core shield
            if ($this_ability->ability_results['this_result'] != 'failure'){
                if (!empty($target_robot->robot_attachments)){
                    $temp_attachment_tokens = array_keys($target_robot->robot_attachments);
                    $temp_attachment_tokens = array_reverse($temp_attachment_tokens);
                    foreach ($temp_attachment_tokens AS $temp_key => $temp_attachment_token){
                        $temp_attachment_info = $target_robot->robot_attachments[$temp_attachment_token];
                        if (strstr($temp_attachment_token, 'ability_core-shield_')){
                            $temp_attachment_info['attachment_duration'] = 0;
                            $target_robot->robot_attachments[$temp_attachment_token] = $temp_attachment_info;
                            $target_robot->update_session();
                            break;
                        }
                    }
                }
            }

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If the ability has already been summoned earlier this turn, decrease WE to zero
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        if (!empty($this_robot->flags[$summoned_flag_token])){ $this_ability->set_energy(0); }
        else { $this_ability->reset_energy(); }

        // Return true on success
        return true;

        }
    );
?>