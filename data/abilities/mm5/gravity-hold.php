<?
// GRAVITY HOLD
$ability = array(
    'ability_name' => 'Gravity Hold',
    'ability_token' => 'gravity-hold',
    'ability_game' => 'MM05',
    'ability_group' => 'MM05/Weapons/033',
    'ability_description' => 'The user intensifies gravity around the target to hold them in place and prevent switching, causing electromagnetic damage in the process!',
    'ability_type' => 'electric',
    'ability_type2' => 'space',
    'ability_damage' => 22,
    'ability_energy' => 8,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_gender = preg_match('/^(roll|disco|rhythm|[-a-z]+woman)$/i', $target_robot->robot_token) ? 'female' : 'male';
        $this_attachment_token = 'ability_'.$this_ability->ability_token.'_'.$target_robot->robot_id;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'attachment_duration' => 3,
            'attachment_switch_disabled' => true,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -15, -5, -10, $target_robot->print_name().' found '.($this_gender == 'female' ? 'herself' : 'himself').' paralyzed!<br /> '.$target_robot->print_name().' is prevented from switching!'),
                'failure' => array(9, -15, -5, -10, $target_robot->print_name().' found '.($this_gender == 'female' ? 'herself' : 'himself').' paralyzed!<br /> '.$target_robot->print_name().' is prevented from switching!')
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
                'success' => array(1, -20, -5, -10,  'The paralysis immobilizing '.$target_robot->print_name().' faded away&hellip;<br /> '.$target_robot->print_name().' is no longer prevented from switching!'),
                'failure' => array(1, -20, -5, -10, 'The paralysis immobilizing '.$target_robot->print_name().' faded away&hellip;<br /> '.$target_robot->print_name().' is no longer prevented from switching!')
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(2, 3),
            'ability_frame_offset' => array('x' => -15, 'y' => -5, 'z' => -10)
            );

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => $this_robot->robot_token == 'gravity-man' ? 'shoot' : 'summon',
            'success' => array(5, 110, 0, 10, $this_robot->print_name().' uses the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Attach a temp attachment to the robot being targeted now
        $target_robot->robot_attachments[$this_attachment_token.'_temp'] = $this_attachment_info;
        $target_robot->update_session();

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(5, 0, 0),
            'success' => array(4, 110, -10, 10, 'The '.$this_ability->print_name().' paralyzed the target!'),
            'failure' => array(9, -65, -10, -10, 'The '.$this_ability->print_name().' missed&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(0, 0, 0),
            'success' => array(4, 110, -10, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(9, -65, -10, -10, 'The '.$this_ability->print_name().' had no effect&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Remove the temp attachment from the robot being targeted
        unset($target_robot->robot_attachments[$this_attachment_token.'_temp']);
        $target_robot->update_session();

        // Attach the ability to the target if not disabled
        if ($target_robot->robot_status != 'disabled'
            && $this_ability->ability_results['this_result'] != 'failure'
            && $this_ability->ability_results['this_amount'] > 0){

            // If the ability flag was not set, attach the Proto Shield to the target
            if (!isset($target_robot->robot_attachments[$this_attachment_token])){

                // Attach this ability attachment to the robot using it
                $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $target_robot->update_session();

                // Target this robot's self
                $this_robot->robot_frame = 'base';
                $this_robot->update_session();
                $this_ability->target_options_update($this_attachment_info['attachment_create']);
                $target_robot->trigger_target($target_robot, $this_ability);

            }
            // Else if the ability flag was set, reinforce the shield by one more duration point
            else {

                // Collect the attachment from the robot to back up its info
                $this_attachment_info = $target_robot->robot_attachments[$this_attachment_token];
                $this_attachment_info['attachment_duration'] = 3;
                $target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                $target_robot->update_session();

                // Target the opposing robot
                $this_ability->target_options_update(array(
                    'frame' => 'defend',
                    'success' => array(9, 85, -10, -10, $this_robot->print_name().' further paralyzed '.$target_robot->print_name().'!<br /> '.$target_robot->print_name().'&#39;s inability to switch has been prolonged!')
                    ));
                $target_robot->trigger_target($target_robot, $this_ability);

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