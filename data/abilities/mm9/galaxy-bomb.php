<?
// GALAXY BOMB
$ability = array(
    'ability_name' => 'Galaxy Bomb',
    'ability_token' => 'galaxy-bomb',
    'ability_game' => 'MM09',
    'ability_group' => 'MM09/Weapons/075',
    'ability_description' => 'The user fires a small gravity bomb that implodes in front of the target to form a powerful black hole! The black hole lasts for five turns and deals damage to any active robot at the end of each!',
    'ability_type' => 'space',
    'ability_type2' => 'explode',
    'ability_energy' => 8,
    'ability_damage' => 10,
    'ability_damage_percent' => true,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Predefine attachment create and destroy text for later
        $this_create_text = ($target_robot->print_name().' found '.$target_robot->get_pronoun('reflexive').' in front of a black hole!<br /> '.
            $target_robot->print_name().' will take damage at the end of each turn!'
            );
        $this_destroy_text = ('The '.$this_ability->print_name().'\'s black hole faded away...<br /> '.
            'The active robot won\'t take end-of-turn damage any more!'
            );
        $this_refresh_text = ('The '.$this_ability->print_name().' extended the black hole\'s life!<br /> '.
            'The active robot will continue taking damage at the end of each turn!'
            );
        $this_repeat_text = ('The '.$this_ability->print_name().'\'s black hole crushed the active robot!');

        // Define this ability's attachment token
        $static_attachment_key = $target_robot->get_static_attachment_key();
        $static_attachment_duration = 5;
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$static_attachment_key;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_ability->ability_id.'_'.$static_attachment_key,
            'ability_token' => $this_ability->ability_token,
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
                'success' => array(1, -5, 5, -10, $this_repeat_text),
                'failure' => array(1, -5, 5, -99, $this_repeat_text),
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
            'ability_frame' => 1,
            'ability_frame_animate' => array(1, 2, 3, 4, 5, 6, 7, 8, 9),
            'ability_frame_offset' => array('x' => -5, 'y' => 5, 'z' => -10)
            );

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 120, 0, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!')
            ));
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

    }
    );
?>