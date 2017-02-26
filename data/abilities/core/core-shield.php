<?
// CORE SHIELD
$ability = array(
    'ability_name' => 'Core Shield',
    'ability_token' => 'core-shield',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Core',
    'ability_description' => 'The user generates an elemental barrier that hovers in front of the target to protect it from damage. This ability\'s typing appears to be influenced by the energy of nearby cores.',
    'ability_type' => 'shield',
    'ability_energy' => 8,
    'ability_recovery2' => 99,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect or define the current "target" of this ability
        if ($this_ability->ability_target == 'select_this'){ $current_target_robot = $target_robot; }
        else { $current_target_robot = $this_robot; }

        // Define the base values for the attachment
        $base_attachment_duration = 9;
        $base_effect_element = $this_ability->ability_type;
        $base_effect_multiplier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$base_effect_element;

        // Define the animation sequence for the attachment
        $base_animation_sequence = array(2, 3, 4, 3);
        if ($this_robot->robot_id == $current_target_robot->robot_id){
            array_push($base_animation_sequence, array_shift($base_animation_sequence));
        }

        // Define the base offset values for the attachment
        $base_offset_shift = 0;
        if (!empty($current_target_robot->robot_attachments)){
            foreach ($current_target_robot->robot_attachments AS $token => $info){
                if ($token == $this_attachment_token){ continue; }
                if (strstr($token, 'ability_'.$this_ability->ability_token.'_')){
                    $base_offset_shift++;
                }
            }
        }
        $base_offset_x = 10 + $base_offset_shift;
        $base_offset_y = 0 + $base_offset_shift;

        // Define the flavour text for the attachment
        $this_attachment_create_text = 'The '.$this_ability->print_name().' resists '.$this_ability->ability_recovery2.'% of all <span class="ability_name ability_type ability_type_'.$base_effect_element.'">'.ucfirst($base_effect_element).'</span> type damage!<br /> ';
        $this_attachment_create_text .= $this_robot->print_name().'\'s elemental defenses were improved considerably!';
        $this_attachment_destroy_text = 'The <span class="ability_name ability_type ability_type_'.$base_effect_element.'">'.ucfirst($base_effect_element).'</span> type '.$this_ability->print_name().' faded away!<br /> ';
        $this_attachment_destroy_text .= $this_robot->print_name().' is no longer protected from the <span class="ability_name ability_type ability_type_'.$base_effect_element.'">'.ucfirst($base_effect_element).'</span> element...';

        // Define this ability's attachment token and info
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_duration' => $base_attachment_duration,
            'attachment_damage_input_breaker_'.$base_effect_element => $base_effect_multiplier,
            'attachment_create' => array(
                'kind' => 'defense',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array($base_animation_sequence[0], $base_offset_x, $base_offset_y, 10, $this_attachment_create_text),
                'failure' => array($base_animation_sequence[0], $base_offset_x, $base_offset_y, 10, $this_attachment_create_text)
                ),
            'attachment_destroy' => array(
                'kind' => 'defense',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, $base_offset_x, $base_offset_y, 10, $this_attachment_destroy_text),
                'failure' => array(9, $base_offset_x, $base_offset_y, 10, $this_attachment_destroy_text)
                ),
            'ability_frame' => $base_animation_sequence[0],
            'ability_frame_animate' => $base_animation_sequence,
            'ability_frame_offset' => array('x' => $base_offset_x, 'y' => $base_offset_y, 'z' => 10)
            );

        // Create the attachment object for this ability
        $this_attachment = new rpg_ability($this_battle, $this_player, $current_target_robot, $this_attachment_info);

        // If the ability flag was not set, attach the ability to the target
        if (!$current_target_robot->has_attachment($this_attachment_token)){

            // Target this robot's self
            if ($this_robot->robot_gender == 'female'){ $pronoun = 'herself'; }
            elseif ($this_robot->robot_gender == 'male'){ $pronoun = 'himself'; }
            else { $pronoun = 'itself'; }
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(1, -10, 0, -10,
                    $this_robot->print_name().' generates a '.$this_ability->print_name().'!<br />'.
                    $this_robot->print_name().' attaches the shield to '.($this_robot->robot_id != $current_target_robot->robot_id ? $current_target_robot->print_name() : $pronoun).'!'
                    )
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // If this robot is targetting itself
            if ($this_robot->robot_id == $current_target_robot->robot_id){

                // Target this robot's self
                $this_ability->target_options_update($this_attachment_info['attachment_create']);
                $this_robot->trigger_target($this_robot, $this_ability);

                // Attach this ability attachment to the robot using it
                $this_attachment_info['ability_frame_animate'] = $base_animation_sequence;
                $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

            }
            // Otherwise if targetting a team mate
            else {

                // Target this robot's self
                $this_robot->robot_frame = 'base';
                $this_robot->update_session();
                $this_attachment->target_options_update($this_attachment_info['attachment_create']);
                $current_target_robot->trigger_target($current_target_robot, $this_attachment);

                // Attach this ability attachment to the robot using it
                $this_attachment_info['ability_frame_animate'] = $base_animation_sequence;
                $current_target_robot->set_attachment($this_attachment_token, $this_attachment_info);

            }

        }
        // Else if the ability flag was set, reinforce the shield by its base duration value
        else {

            // If this robot is targetting itself
            if ($this_robot->robot_id == $current_target_robot->robot_id){

                // Collect the attachment from the robot to back up its info
                $this_attachment_info = $this_robot->get_attachment($this_attachment_token);
                $this_attachment_info['attachment_duration'] = $base_attachment_duration;
                $this_attachment_info['ability_frame_animate'] = $base_animation_sequence;
                $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

                // Target the opposing robot
                $this_ability->target_options_update(array(
                    'frame' => 'summon',
                    'success' => array(0, -10, 0, -10, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> The duration of '.$this_robot->print_name().'\'s protection was extended!')
                    ));
                $this_robot->trigger_target($this_robot, $this_ability);

            }
            // Otherwise if targetting a team mate
            else {

                // Collect the attachment from the robot to back up its info
                $this_attachment_info = $current_target_robot->get_attachment($this_attachment_token);
                $this_attachment_info['attachment_duration'] = $base_attachment_duration;
                $this_attachment_info['ability_frame_animate'] = $base_animation_sequence;
                $current_target_robot->set_attachment($this_attachment_token, $this_attachment_info);

                // Target the opposing robot
                $this_attachment->target_options_update(array(
                    'frame' => 'summon',
                    'success' => array(0, -10, 0, -10, $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> The duration of '.$current_target_robot->print_name().'\'s protection was extended!')
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

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect this robots core and item types
        $ability_base_type = !empty($this_ability->ability_base_type) ? $this_ability->ability_base_type : '';
        $robot_core_type = !empty($this_robot->robot_core) ? $this_robot->robot_core : '';
        $robot_item_type = !empty($this_robot->robot_item) && strstr($this_robot->robot_item, '-core') ? str_replace('-core', '', $this_robot->robot_item) : '';

        // Define the types for this ability
        $ability_types = array();
        $ability_types[] = $ability_base_type;
        if (!empty($robot_core_type) && $robot_core_type != 'copy'){ $ability_types[] = $robot_core_type; }
        if (!empty($robot_item_type) && $robot_item_type != 'copy'){ $ability_types[] = $robot_item_type; }
        $ability_types = array_reverse($ability_types);
        $ability_types = array_slice($ability_types, 0, 2);

        // Collect this robot's primary type and change its image if necessary
        $this_ability->set_image($this_ability->ability_token.'_'.$ability_types[0]);
        $this_ability->set_type($ability_types[0]);
        /*
        if (!empty($ability_types[1])){
            $this_ability->set_image2($this_ability->ability_token.'_'.$ability_types[1].'2');
            $this_ability->set_type2($ability_types[1]);
        } else {
            $this_ability->set_image2('');
            $this_ability->set_type2('');
        }
        */

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_this'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>