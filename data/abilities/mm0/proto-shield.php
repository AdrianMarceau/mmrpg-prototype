<?
// PROTO SHIELD
$ability = array(
    'ability_name' => 'Proto Shield',
    'ability_token' => 'proto-shield',
    'ability_game' => 'MM00',
    'ability_description' => 'The user creates a large reflective shield that hovers in front of its target and halves all damage received from attacks for three turns!',
    'ability_type' => 'shield',
    'ability_energy' => 4,
    'ability_accuracy' => 100,
    'ability_target' => 'select_this',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$target_robot->robot_id;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => 4,
            'attachment_damage_input_breaker' => 0.5,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(0, 34, -10, 18, 'The '.$this_ability->print_name().' hovers in front of '.$target_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s defenses were bolstered!'),
                'failure' => array(0, 34, -10, 18, 'The '.$this_ability->print_name().' hovers in front of '.$target_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s defenses were bolstered!')
                ),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(2, -2, 0, -10,  'The '.$this_ability->print_name().' faded away!<br /> '.$target_robot->print_name().'&#39;s defenses returned to normal!'),
                'failure' => array(2, -2, 0, -10, 'The '.$this_ability->print_name().' faded away!<br /> '.$target_robot->print_name().'&#39;s defenses returned to normal!')
                ),
                'ability_frame' => 0,
                'ability_frame_animate' => array(0, 1, 2, 1),
                'ability_frame_offset' => array('x' => 34, 'y' => -10, 'z' => 18)
            );

        // Create the attachment object for this ability
        $this_attachment = new rpg_ability($this_battle, $this_player, $target_robot, $this_attachment_info);

        // If the ability flag was not set, attach the ability to the target
        if (!isset($target_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, 50, 0, 18, $this_robot->print_name().' summons a '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // If this robot is targetting itself
            if ($this_robot->robot_id == $target_robot->robot_id){

                // Target this robot's self
                $this_ability->target_options_update($this_attachment_info['attachment_create']);
                $this_robot->trigger_target($this_robot, $this_ability);

                // Attach this ability attachment to the robot using it
                $this_attachment_info['ability_frame_animate'] = array(2, 1, 0, 1);
                $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $this_robot->update_session();

            }
            // Otherwise if targetting a team mate
            else {

                // Target this robot's self
                $this_robot->robot_frame = 'base';
                $this_robot->update_session();
                $this_attachment->target_options_update($this_attachment_info['attachment_create']);
                $target_robot->trigger_target($target_robot, $this_attachment);

                // Attach this ability attachment to the robot using it
                $this_attachment_info['ability_frame_animate'] = array(0, 1, 2, 1);
                $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $target_robot->update_session();

            }

        }
        // Else if the ability flag was set, reinforce the shield by one more duration point
        else {

            // If this robot is targetting itself
            if ($this_robot->robot_id == $target_robot->robot_id){

                // Collect the attachment from the robot to back up its info
                $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
                $this_attachment_info['attachment_duration'] = 4;
                $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $this_robot->update_session();

                // Target the opposing robot
                $this_ability->target_options_update(array(
                    'frame' => 'summon',
                    'success' => array(9, 85, -10, -10, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> '.$this_robot->print_name().'&#39;s protection has been extended!')
                    ));
                $this_robot->trigger_target($this_robot, $this_ability);

            }
            // Otherwise if targetting a team mate
            else {

                // Collect the attachment from the robot to back up its info
                $this_attachment_info = $target_robot->robot_attachments[$this_attachment_token];
                $this_attachment_info['attachment_duration'] = 4;
                $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $target_robot->update_session();

                // Target the opposing robot
                $this_attachment->target_options_update(array(
                    'frame' => 'summon',
                    'success' => array(9, 85, -10, -10, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> '.$target_robot->print_name().'&#39;s protection has been extended!')
                    ));
                $this_robot->trigger_target($this_robot, $this_attachment);

            }

        }

        // Either way, update this ability's settings to prevent recovery
        $this_attachment->damage_options_update($this_attachment_info['attachment_destroy'], true);
        $this_attachment->recovery_options_update($this_attachment_info['attachment_destroy'], true);
        $this_attachment->update_session();

        // Return true on success
        return true;

    }
    );
?>