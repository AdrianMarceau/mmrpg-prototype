<?
// RHYTHM SATELLITE
$ability = array(
    'ability_name' => 'Rhythm Satellite',
    'ability_token' => 'rhythm-satellite',
    'ability_game' => 'MM00',
    'ability_description' => 'The user creates a pair of orbiting satellites that hover beind their target and doubles all damage dealt by attacks for three turns!',
    'ability_type' => 'laser',
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
            'ability_token' => $this_ability->ability_token,
            'attachment_duration' => 4,
            'attachment_damage_output_booster' => 2.0,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(2, -24, 10, -18, 'The '.$this_ability->print_name(true).' hover behind '.$target_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s weapons were bolstered!'),
                'failure' => array(2, -24, 10, -18, 'The '.$this_ability->print_name(true).' hover behind '.$target_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s weapons were bolstered!')
                ),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(2, -24, 10, -18,  'The '.$this_ability->print_name(true).' faded away!<br /> '.$target_robot->print_name().'&#39;s weapons returned to normal!'),
                'failure' => array(2, -24, 10, -18, 'The '.$this_ability->print_name(true).' faded away!<br /> '.$target_robot->print_name().'&#39;s weapons returned to normal!')
                ),
                'ability_frame' => 2,
                'ability_frame_animate' => array(3, 4, 2),
                'ability_frame_offset' => array('x' => -24, 'y' => 10, 'z' => -18)
            );

        // If this robot is targetting itself
        if ($this_robot->robot_id != $target_robot->robot_id){

            // Recreate this ability using the target robot's data
            $temp_abilityinfo = array('ability_token' => $this_ability->ability_token);
            $temp_ability = new rpg_ability($this_battle, $this_player, $target_robot, $temp_abilityinfo);
            $temp_ability->update_session();

        }


        // If the ability flag was not set, attach the ability to the target
        if (!isset($target_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(1, 24, 30, 18, $this_robot->print_name().' summons the '.$this_ability->print_name(true).'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // If this robot is targetting itself
            if ($this_robot->robot_id == $target_robot->robot_id){

                // Target this robot's self
                $this_ability->target_options_update($this_attachment_info['attachment_create']);
                $this_robot->trigger_target($this_robot, $this_ability);

                // Attach this ability attachment to the robot using it
                //$this_attachment_info['ability_frame_animate'] = array(2, 1, 0, 1);
                $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $this_robot->update_session();

            }
            // Otherwise if targetting a team mate
            else {

                // Target this robot's self
                $this_robot->robot_frame = 'base';
                $this_robot->update_session();
                $temp_ability->target_options_update($this_attachment_info['attachment_create']);
                $target_robot->trigger_target($target_robot, $temp_ability);

                // Attach this ability attachment to the robot using it
                //$this_attachment_info['ability_frame_animate'] = array(0, 1, 2, 1);
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
                    'success' => array(9, 24, 30, 18, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> '.$this_robot->print_name().'&#39;s protection has been extended!')
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
                $temp_ability->target_options_update(array(
                    'frame' => 'summon',
                    'success' => array(9, 24, 30, 18, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> '.$target_robot->print_name().'&#39;s protection has been extended!')
                    ));
                $this_robot->trigger_target($this_robot, $temp_ability);

            }

        }

        // Either way, update this ability's settings to prevent recovery
        $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->update_session();
        if (isset($temp_ability)){
            $temp_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
            $temp_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
            $temp_ability->update_session();
        }

        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG', '<pre>Reached the end... '.preg_replace('#\s+#', ' ', print_r(array('ability_id' => $this_ability->ability_id, 'ability_token' => $this_ability->ability_token), true)).'</pre>');


        // Return true on success
        return true;

    }
    );
?>