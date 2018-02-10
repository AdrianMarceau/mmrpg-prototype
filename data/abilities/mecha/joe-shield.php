<?
// JOE SHIELD
$ability = array(
    'ability_name' => 'Joe Shield',
    'ability_token' => 'joe-shield',
    'ability_game' => 'MM00',
    'ability_class' => 'mecha',
    'ability_description' => 'The user creates a large green and white shield that hovers in front of its target and halves all damage on the next turn!',
    'ability_type' => 'shield',
    'ability_energy' => 2,
    'ability_accuracy' => 100,
    'ability_recovery2' => 99,
    'ability_recovery2_percent' => true,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$this_robot->robot_id;
        $temp_text = $this_robot->robot_token == 'sniper-joe' ? 'The ' : 'The ';
        $temp_text2 = $this_robot->robot_token == 'sniper-joe' ? 'The ' : 'The ';
        $temp_attachment_damage_breaker = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'attachment_duration' => 1,
            'attachment_damage_breaker' => $temp_attachment_damage_breaker,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => $this_robot->robot_token == 'sniper-joe' ? 'defend' : 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(0, 34, -10, 18, $temp_text.$this_ability->print_name().' attached itself to '.$this_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s defenses were bolstered!'),
                'failure' => array(0, 34, -10, 18, $temp_text.$this_ability->print_name().' attached itself to '.$this_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s defenses were bolstered!')
                ),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(2, -2, 0, -10,  $temp_text2.$this_ability->print_name().' faded away!<br /> '.$this_robot->print_name().' is no longer protected&hellip;'),
                'failure' => array(2, -2, 0, -10, $temp_text2.$this_ability->print_name().' faded away!<br /> '.$this_robot->print_name().' is no longer protected&hellip;')
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1, 2, 1),
            'ability_frame_offset' => array('x' => 34, 'y' => -10, 'z' => 18)
            );

        // DEBUG
        //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('#\s+#', ' ', print_r(array('ability_id' => $this_ability->ability_id, 'ability_token' => $this_ability->ability_token), true)).'</pre>');

        // If the ability flag was not set, attach the Proto Shield to the target
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){

            // Define the damage multiplier amount for this ability
            $this_attachment_info['attachment_damage_breaker'] = $temp_attachment_damage_breaker;

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array((!isset($this_robot->robot_attachments[$this_attachment_token]) ? 0 : 9), 50, 0, 18, $this_robot->print_name().' summons a '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Target this robot's self
            $this_ability->target_options_update($this_attachment_info['attachment_create']);
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            if ($this_robot->robot_token != 'sniper-joe'){ $this_attachment_info['ability_frame_animate'] = array(2, 1, 0, 1); }
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

        }
        // Else if the ability flag was set, reinforce the shield by one more duration point
        else {

            // Collect the attachment from the robot to back up its info
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            $this_attachment_info['attachment_duration'] = 1;
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => $this_robot->robot_token == 'sniper-joe' ? 'defend' : 'summon',
                'success' => array(9, 85, -10, -10, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> '.$this_robot->print_name().'&#39;s protection has been extended!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

        }

        // If this is Sniper Joe himself, don't show the attachment version of the shield
        if (!empty($this_robot->robot_attachments[$this_attachment_token])
            && $this_robot->robot_token == 'sniper-joe'){
            $this_attachment_info['attachment_create']['success'][0] = 9;
            $this_attachment_info['attachment_create']['failure'][0] = 9;
            $this_attachment_info['attachment_destroy']['success'][0] = 9;
            $this_attachment_info['attachment_destroy']['failure'][0] = 9;
            $this_attachment_info['ability_frame'] = 9;
            $this_attachment_info['ability_frame_animate'] = array(9);
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();
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