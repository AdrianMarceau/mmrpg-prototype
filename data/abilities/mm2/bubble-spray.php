<?
// BUBBLE SPRAY
$ability = array(
    'ability_name' => 'Bubble Spray',
    'ability_token' => 'bubble-spray',
    'ability_game' => 'MM02',
    'ability_group' => 'MM02/Weapons/011',
    'ability_description' => 'The user sprays a thick layer of foamy bubbles at the target\'s feet to deal damage and make their position on the field vulnerable to Electric and Freeze type attacks!',
    'ability_type' => 'water',
    'ability_energy' => 4,
    'ability_damage' => 16,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Predefine attachment create and destroy text for later
        $this_create_text = ($target_robot->print_name().' found '.$target_robot->get_pronoun('reflexive').' in a mound of foamy bubbles!<br /> '.
            'That position on the field is vulnerable to '.
            rpg_type::print_span('electric').' and '.rpg_type::print_span('freeze').' '.
            'types now!'
            );
        $this_destroy_text = ('The '.$this_ability->print_name().'\'s foamy bubbles faded away...<br /> '.
            'That position on the field isn\'t vulnerable to '.
            rpg_type::print_span('electric').' or '.rpg_type::print_span('freeze').' '.
            'types anymore!'
            );
        $this_refresh_text = ($this_robot->print_name().' refreshed the '.$this_ability->print_name().'\'s mound of foamy bubbles!<br /> '.
            'That position on the field is still vulnerable to '.
            rpg_type::print_span('electric').' and '.rpg_type::print_span('freeze').' '.
            'types!'
            );

        // Define this ability's attachment token
        $static_attachment_key = $target_robot->get_static_attachment_key();
        $static_attachment_duration = 6;
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$static_attachment_key;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_ability->ability_id,
            'ability_token' => $this_ability->ability_token,
            'attachment_duration' => $static_attachment_duration,
            'attachment_token' => $this_attachment_token,
            'attachment_sticky' => true,
            'attachment_damage_input_booster_electric' => 2.0,
            'attachment_damage_input_booster_freeze' => 2.0,
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
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(9, 0, -9999, 0, $this_destroy_text),
                'failure' => array(9, 0, -9999, 0, $this_destroy_text)
                ),
            'ability_frame' => 2,
            'ability_frame_animate' => array(2, 3),
            'ability_frame_offset' => array('x' => 0, 'y' => -5, 'z' => 6)
            );

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'shoot',
            'success' => array(0, 120, 5, 10, $this_robot->print_name().' fires the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(5, 0, 0),
            'success' => array(1, 5, -10, 10, 'The '.$this_ability->print_name().' surrounded into the target!'),
            'failure' => array(1, -65, -10, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(1, 5, -10, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(1, -65, -10, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Inflect a break on speed if the robot wasn't disabled
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
                $this_attachment_info['attachment_duration'] = $static_attachment_duration;
                $this_battle->battle_attachments[$static_attachment_key][$this_attachment_token] = $this_attachment_info;
                $this_battle->update_session();

                // Target the opposing robot
                if ($target_robot->robot_status != 'disabled'){
                    $this_ability->target_options_update(array(
                        'frame' => 'defend',
                        'success' => array(9, 85, -10, -10, $this_refresh_text)
                        ));
                    $target_robot->trigger_target($target_robot, $this_ability);
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

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>