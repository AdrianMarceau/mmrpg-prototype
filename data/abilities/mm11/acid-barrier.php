<?
// ACID BARRIER
$ability = array(
    'ability_name' => 'Acid Barrier',
    'ability_token' => 'acid-barrier',
    'ability_game' => 'MM11',
    //'ability_group' => 'MM11/Weapons/084',
    'ability_group' => 'MM11/Weapons/081T2',
    'ability_image_sheets' => 4,
    'ability_description' => 'The user surrounds itself in a protective bubble of corrosive acid that reduces damage from incoming attacks for up to nine turns!  As long as the user is protected by this bubble, the Acid Glob ability can be used at increased power and without consuming any weapon energy!',
    'ability_type' => 'water',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_damage' => 0,
    'ability_recovery2' => 30,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Change this ability's image based on the holding robot's type
        $this_ability->ability_image = $this_ability->ability_base_image;
        if ($this_robot->robot_token == 'acid-man'
            && $this_robot->robot_image != $this_robot->robot_token){
            $alt = trim(str_replace($this_robot->robot_token, '', $this_robot->robot_image), '_');
            if ($alt == 'alt'){ $this_ability->ability_image .= '-2'; }
            elseif ($alt == 'alt2'){ $this_ability->ability_image .= '-3'; }
            elseif ($alt == 'alt9'){ $this_ability->ability_image .= '-4'; }
        }
        $this_ability->update_session();

        // Define the base attachment duration
        $base_attachment_duration = 9;
        $base_attachment_multiplier = 1 - ($this_ability->ability_recovery2 / 100);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => $base_attachment_duration,
            'attachment_damage_input_breaker' => $base_attachment_multiplier,
            'attachment_weaknesses' => array('earth', 'laser'),
            'attachment_weaknesses_trigger' => 'target',
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    )
                ),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_name().' faded away!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    ),
                'failure' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_name().' faded away!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => -6, 'y' => 0, 'z' => 10)
            );

        // If this is an oversized sprite, shift the offset a bit
        if ($this_robot->robot_image_size > 40){ $this_attachment_info['ability_frame_offset']['x'] += ceil(($this_robot->robot_image_size - 40) / 4); }

        // Check if this ability is already summoned
        $is_summoned = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // If the ability flag was not set, this ability begins charging
        if (!$is_summoned){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -6, 0, -10, $this_robot->print_name().' raises a '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Increase this robot's defense stat
            $this_ability->target_options_update($this_attachment_info['attachment_create'], true);
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

        }
        // Else if the ability flag was set, we just extend the duration again
        else {

            // Collect the attachment from the robot to back up its info
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            $this_attachment_info['attachment_duration'] = $base_attachment_duration;
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(9, 24, 30, 18, $this_robot->print_name().' refreshed '.$target_robot->get_pronoun('possessive2').' '.$this_ability->print_name(true).'!<br /> The duration of '.$this_robot->print_name().'&#39;s protection was extended!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

        }

        // Either way, update this ability's settings to prevent recovery
        $this_ability->damage_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->recovery_options_update($this_attachment_info['attachment_destroy'], true);
        $this_ability->update_session();


        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Change this ability's image based on the holding robot's type
        $this_ability->ability_image = $this_ability->ability_base_image;
        if ($this_robot->robot_token == 'acid-man'
            && $this_robot->robot_image != $this_robot->robot_token){
            $alt = trim(str_replace($this_robot->robot_token, '', $this_robot->robot_image), '_');
            if ($alt == 'alt'){ $this_ability->ability_image .= '-2'; }
            elseif ($alt == 'alt2'){ $this_ability->ability_image .= '-3'; }
            elseif ($alt == 'alt9'){ $this_ability->ability_image .= '-4'; }
        }
        $this_ability->update_session();

        // Return true on success
        return true;

        }
    );
?>