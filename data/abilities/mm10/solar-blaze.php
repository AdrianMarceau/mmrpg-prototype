<?
// SOLAR BLAZE
$ability = array(
    'ability_name' => 'Solar Blaze',
    'ability_token' => 'solar-blaze',
    'ability_game' => 'MM10',
    //'ability_group' => 'MM10/Weapons/080',
    'ability_group' => 'MM10/Weapons/073T1',
    'ability_description' => 'The user releases a fire-based projectile that hovers in the middle of the field and waits, absorbing energy from the target\'s attacks.  At the end of the turn, the projectile explodes to release a wave of fire that deals counter damage the target!',
    'ability_type' => 'flame',
    'ability_type2' => 'explode',
    'ability_energy' => 8,
    'ability_speed' => 3,
    'ability_damage' => 26,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_id' => $this_attachment_token.'_shield',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_token' => $this_attachment_token,
            'attachment_duration' => 1,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(1, -10, 0, -10,
                    $this_robot->print_name().' generates a '.$this_ability->print_name().'!<br /> '.
                    'The '.$this_ability->print_name().' started charging! '
                    ),
                'failure' => array(1, -10, 0, -10,
                    $this_robot->print_name().' generates a '.$this_ability->print_name().'!<br /> '.
                    'The '.$this_ability->print_name().' started charging! '
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
                'success' => array(9, -10, 0, -10, ''),
                'failure' => array(9, -10, 0, -10, '')
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => 180, 'y' => 0, 'z' => 30)
            );

        // If this ability has not been summoned yet, do the action and then queue a conclusion move
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        $lifecounter_flag_token = $this_ability->ability_token.'_lifecounter';
        if (empty($this_robot->flags[$summoned_flag_token])){

            // Set the summoned flag on this robot and save
            $this_robot->flags[$summoned_flag_token] = true;
            $this_robot->counters[$lifecounter_flag_token] = $this_robot->robot_energy;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'shoot',
                'success' => array(1, 180, 0, 30,
                    $this_robot->print_name().' releases a '.$this_ability->print_name().'!<br /> '.
                    'The '.$this_ability->print_name().' started charging! '
                    )
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

            // Queue another use of this ability at the end of turn
            $this_battle->actions_append(
                $this_player,
                $this_robot,
                $target_player,
                $target_robot,
                'ability',
                $this_ability->ability_id.'_'.$this_ability->ability_token,
                true
                );

        }
        // The ability has already been summoned, so we can finish executing it now and deal damage
        else {

            // Calculate the difference in energy so we know how much payback
            $power_boost = 0;
            if (isset($this_robot->counters[$lifecounter_flag_token])
                && $this_robot->robot_energy != $this_robot->counters[$lifecounter_flag_token]
                && $this_robot->robot_energy < $this_robot->counters[$lifecounter_flag_token]){
                $power_boost += $this_robot->counters[$lifecounter_flag_token] - $this_robot->robot_energy;
            }

            // Remove the summoned flag from this robot and save
            unset($this_robot->flags[$summoned_flag_token]);
            unset($this_robot->counters[$lifecounter_flag_token]);
            $this_robot->update_session();

            // Remove this ability attachment from the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

            // Define this ability's second attachment token
            $this_attachment_token_two = 'ability_'.$this_ability->ability_token.'_two';
            $this_attachment_info_two = array(
                'class' => 'ability',
                'sticky' => true,
                'ability_token' => $this_ability->ability_token,
                'ability_frame' => 4,
                'ability_frame_animate' => array(4, 5),
                'ability_frame_offset' => array('x' => 90, 'y' => 0, 'z' => -30),
                'ability_frame_styles' => 'transform: scaleX(-1); -moz-transform: scaleX(-1); -webkit-transform: scaleX(-1); '
                );

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token_two] = $this_attachment_info_two;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(4, 300, 0, 30,
                    'The '.$this_ability->print_name().' exploded! <br />'.
                    'Waves of fire race across the field! '
                    )
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            // Update the attachment to show movement
            $this_robot->robot_frame = 'taunt';
            $this_robot->robot_attachments[$this_attachment_token_two]['ability_frame_offset']['x'] -= 140;
            $this_robot->update_session();

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(10, 0, 0),
                'success' => array(5, -80, 0, 30, 'The '.$this_ability->print_name().'\'s flame burned through the target!'),
                'failure' => array(5, -90, 0, -30, 'The '.$this_ability->print_name().'\'s flame missed the target...')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(5, 0, 0),
                'success' => array(5, -80, 0, 30, 'The '.$this_ability->print_name().'\'s flame burned through the target!'),
                'failure' => array(5, -90, 0, -30, 'The '.$this_ability->print_name().'\'s flame missed the target...')
                ));
            $energy_damage_amount = $this_ability->ability_damage + $power_boost;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

            // Remove this ability attachment from the robot using it
            $this_robot->robot_frame = 'base';
            unset($this_robot->robot_attachments[$this_attachment_token_two]);
            $this_robot->update_session();

            // If the target was disabled, trigger approptiate action
            if ($target_robot->robot_status == 'disabled'
                || $target_robot->robot_energy <= 0){
                $target_robot->trigger_disabled($this_robot);
            }

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If the user is holding a Target Module, allow bench targeting
        if ($this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // If the ability has already been summoned earlier this turn, decrease WE to zero
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        if (!empty($this_robot->flags[$summoned_flag_token])){ $this_ability->set_energy(0); }
        else { $this_ability->reset_energy(); }

        // Return true on success
        return true;

        }
    );
?>