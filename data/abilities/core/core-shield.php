<?
// CORE SHIELD
$ability = array(
    'ability_name' => 'Core Shield',
    'ability_token' => 'core-shield',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Weapons/Core',
    'ability_description' => 'The user generates an elemental barrier that hovers in front of the target and protects it from {RECOVERY2}% of all damage that matches the shield\'s type! This ability\'s typing appears to be influenced by the energy of nearby cores, internal or otherwise...',
    'ability_type' => 'shield',
    'ability_energy' => 8,
    'ability_recovery2' => 99,
    'ability_accuracy' => 100,
    'ability_target' => 'select_this',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect or define the current "target" of this ability
        if ($target_robot->player->player_id == $this_robot->player->player_id
            && $target_robot->robot_id != $this_robot->robot_id){
            $current_target_robot = $target_robot;
        } else {
            $current_target_robot = $this_robot;
        }

        // Generate the static attachment info for the robot's elemental Core Shield
        $existing_shields = !empty($current_target_robot->robot_attachments) ? substr_count(implode('|', array_keys($current_target_robot->robot_attachments)), 'ability_core-shield_') : 0;
        $this_attachment_info = rpg_ability::get_static_core_shield($this_ability->ability_type, 9, $existing_shields, $this_ability->ability_recovery2);

        // Create the attachment object for this ability
        $this_attachment = new rpg_ability($this_battle, $this_player, $current_target_robot, $this_attachment_info);
        $this_attachment->update_session();

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
                    'success' => array(0, -10, 0, -10,
                        $this_robot->print_name().' reinforced the '.$this_ability->print_name().'!<br /> '.
                        'The duration of '.$this_robot->print_name().'\'s protection was extended!'
                        )
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

        // Return true on success
        return true;

        }
    );
?>