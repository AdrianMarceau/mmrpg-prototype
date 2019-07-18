<?
// ICE BREATH
$ability = array(
    'ability_name' => 'Ice Breath',
    'ability_token' => 'ice-breath',
    'ability_game' => 'MM01',
    //'ability_group' => 'MM01/Weapons/005',
    'ability_group' => 'MM01/Weapons/003T1',
    'ability_description' => 'The user blasts super-chilled air at the target\'s feet to deal damage and freeze them in place! The target is prevented from switching while frozen in the ice.',
    'ability_type' => 'freeze',
    'ability_energy' => 4,
    'ability_damage' => 16,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Predefine attachment create and destroy text for later
        $this_create_text = ($target_robot->print_name().' found '.$target_robot->get_pronoun('reflexive').' in a '.rpg_type::print_span('freeze', 'Frozen Foothold').'!<br /> '.
            $target_robot->print_name().' is prevented from switching!'
            );
        $this_refresh_text = ($this_robot->print_name().' refreshed the '.rpg_type::print_span('freeze', 'Frozen Foothold').' at '.$target_robot->print_name().'\'s feet!<br /> '.
            $target_robot->print_name().' is still prevented from switching!'
            );

        // Define this ability's attachment token
        $static_attachment_key = $target_robot->get_static_attachment_key();
        $static_attachment_duration = 3;
        $this_attachment_info = rpg_ability::get_static_frozen_foothold($static_attachment_key, $static_attachment_duration);
        $this_attachment_token = $this_attachment_info['attachment_token'];

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => $this_robot->robot_token == 'ice-man' ? 'taunt' : 'shoot',
            'success' => array(0, 110, 0, 10, $this_robot->print_name().' uses the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(5, 0, 0),
            'success' => array(1, 5, -10, 10, 'The '.$this_ability->print_name().' chilled the target!'),
            'failure' => array(0, -65, -10, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(1, 5, -10, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(0, -65, -10, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

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
                    $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, -9999, -9999, -9999, $this_create_text)));
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
                        $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, -9999, -9999, -9999, $this_refresh_text)));
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

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>