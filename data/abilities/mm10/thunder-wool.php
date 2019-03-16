<?
// THUNDER WOOL
$ability = array(
    'ability_name' => 'Thunder Wool',
    'ability_token' => 'thunder-wool',
    'ability_game' => 'MM10',
    'ability_group' => 'MM10/Weapons/077',
    'ability_image_sheets' => 2,
    'ability_description' => 'The user launches a thunderous wool-cloud into the air that slowly rises and then floats toward an unlucky target! The cloud hovers in place for three turns and releases a lightning strike at the end of each to damage any robot underneath!',
    'ability_type' => 'electric',
    'ability_energy' => 8,
    'ability_damage' => 20,
    'ability_damage_percent' => true,
    'ability_accuracy' => 100,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Change this ability's image based on the holding robot's type
        if ($this_robot->robot_core == 'electric'){ $this_ability->ability_image = $this_ability->ability_base_image; }
        else { $this_ability->ability_image = $this_ability->ability_base_image.'-2'; }
        $this_ability->update_session();

        // Predefine attachment create and destroy text for later
        $this_create_text = ($target_robot->print_name().' found '.$target_robot->get_pronoun('reflexive').' in under a woolly cloud!<br /> '.
            $target_robot->print_name().' will take damage at the end of each turn!'
            );
        $this_destroy_text = ('The '.$this_ability->print_name().'\'s woolly cloud faded away...<br /> '.
            'The active robot won\'t take end-of-turn damage any more!'
            );
        $this_refresh_text = ('The '.$this_ability->print_name().' extended the woolly cloud\'s life!<br /> '.
            'The active robot will continue taking damage at the end of each turn!'
            );
        $this_repeat_text = ('The '.$this_ability->print_name().'\'s woolly cloud released a lightning bolt!');

        // Define this ability's attachment token
        $static_attachment_key = $target_robot->get_static_attachment_key();
        $static_attachment_duration = 3;
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$static_attachment_key;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_ability->ability_id.'_'.$static_attachment_key,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_duration' => $static_attachment_duration,
            'attachment_energy' => 0,
            'attachment_energy_base_percent' => $this_ability->ability_damage,
            'attachment_token' => $this_attachment_token,
            'attachment_sticky' => true,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -10, -5, -10, $this_create_text),
                'failure' => array(9, -10, -5, -10, $this_create_text)
                ),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'type' => '',
                'type2' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(9, 0, -9999, 0,  $this_destroy_text),
                'failure' => array(9, 0, -9999, 0, $this_destroy_text)
                ),
            'attachment_repeat' => array(
                'kind' => 'energy',
                'trigger' => 'damage',
                'type' => $this_ability->ability_type,
                'type2' => $this_ability->ability_type2,
                'energy' => $this_ability->ability_damage,
                'percent' => true,
                'modifiers' => true,
                'frame' => 'damage',
                'rates' => array(100, 0, 0),
                'success' => array(9, -5, 30, 10, $this_repeat_text),
                'failure' => array(9, -5, 30, 10, $this_repeat_text),
                'options' => array(
                    'apply_modifiers' => true,
                    'apply_type_modifiers' => true,
                    'apply_core_modifiers' => true,
                    'apply_field_modifiers' => true,
                    'apply_stat_modifiers' => false,
                    'apply_position_modifiers' => false,
                    'referred_damage' => true,
                    'referred_damage_id' => $this_robot->robot_id,
                    'referred_damage_stats' => $this_robot->get_stats()
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 2, 4, 1, 3, 5),
            'ability_frame_offset' => array('x' => -5, 'y' => 40, 'z' => -8)
            );

        // Target the opposing robot
        if ($this_robot->robot_token == 'sheep-man'){
            $this_ability->target_options_update(array('frame' => 'summon', 'success' => array(1, 120, 0, 10, $this_robot->print_name().' releases '.$this_robot->get_pronoun('possessive2').' '.$this_ability->print_name().'!')));
        } else {
            $this_ability->target_options_update(array('frame' => 'shoot', 'success' => array(1, 120, -40, 10, $this_robot->print_name().' launches a '.$this_ability->print_name().'!')));
        }
        $this_robot->trigger_target($target_robot, $this_ability);

        // Attach the ability to the target if not disabled
        if ($this_ability->ability_results['this_result'] != 'failure'){

            // If the ability flag was not set, attach the hazard to the target position
            if (!isset($this_battle->battle_attachments[$static_attachment_key][$this_attachment_token])){

                // Attach this ability attachment to the robot using it
                $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
                $this_battle->update_session();

                // Target this robot's self
                if ($target_robot->robot_status != 'disabled'){
                    $this_robot->robot_frame = 'base';
                    $this_robot->update_session();
                    $this_ability->target_options_update($this_attachment_info['attachment_create']);
                    $target_robot->trigger_target($target_robot, $this_ability);
                }

            }
            // Else if the ability flag was set, reinforce the hazard by one more duration point
            else {

                // Collect the attachment from the robot to back up its info
                $this_attachment_info = $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token];
                if (empty($this_attachment_info['attachment_duration'])
                    || $this_attachment_info['attachment_duration'] < $static_attachment_duration){
                    $this_attachment_info['attachment_duration'] = $static_attachment_duration;
                    $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
                    $this_battle->update_session();
                    if ($target_robot->robot_status != 'disabled'){
                        $this_ability->target_options_update(array(
                            'frame' => 'defend',
                            'success' => array(1, -5, 5, -10, $this_refresh_text)
                            ));
                        $target_robot->trigger_target($target_robot, $this_ability);
                    }
                }

            }

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
        if ($this_robot->robot_core == 'electric'){ $this_ability->ability_image = $this_ability->ability_base_image; }
        else { $this_ability->ability_image = $this_ability->ability_base_image.'-2'; }
        $this_ability->update_session();

        // Return true on success
        return true;

        }
    );
?>