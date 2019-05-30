<?
// PLANT BARRIER
$ability = array(
    'ability_name' => 'Plant Barrier',
    'ability_token' => 'plant-barrier',
    'ability_game' => 'MM06',
    //'ability_group' => 'MM06/Weapons/045',
    'ability_group' => 'MM06/Weapons/041T2',
    'ability_image_sheets' => 5,
    'ability_description' => 'The user surrounds itself with a barrier of four large flower petals, each acting independantly to protect the user from damage and restore depleted life energy at the end of each turn!  Each petal can only withstand a single attack before fading, however.',
    'ability_type' => 'nature',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_damage2' => 5,
    'ability_recovery2' => 5,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the total number of shield pieces
        $num_shield_pieces = 4;

        // Define this ability's attachment token
        $this_effect_multiplier_protection = 1 - ($this_ability->ability_damage2 / 100);
        $this_effect_multiplier_recovery = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_token' => $this_attachment_token,
            'attachment_group' => $this_attachment_token,
            'attachment_damage_input_breaker' => $this_effect_multiplier_protection,
            'attachment_weaknesses' => array('*'),
            'attachment_weaknesses_trigger' => 'target',
            'attachment_energy' => 0,
            'attachment_energy_base_percent' => $this_ability->ability_recovery2,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(0, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array(0, -10, 0, -10,
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
                'success' => array(0, -9999, -9999, -9999,
                    'A '.$this_ability->print_name().' petal faded away!<br /> '.
                    $this_robot->print_name().' lost a bit of protection...'
                    ),
                'failure' => array(0, -9999, -9999, -9999,
                    'A '.$this_ability->print_name().' petal faded away!<br /> '.
                    $this_robot->print_name().' lost a bit of protection...'
                    )
                ),
            'attachment_repeat' => array(
                'kind' => 'energy',
                'trigger' => 'recovery',
                'type' => '',
                'type2' => '',
                'energy' => $this_ability->ability_recovery2,
                'percent' => true,
                'modifiers' => true,
                'frame' => 'summon',
                'rates' => array(100, 0, 0),
                'success' => array(1, -5, 5, -10, 'One of the '.$this_ability->print_name().'\'s petals healed its owner!'),
                'failure' => array(1, -5, 5, -99, 'One of the '.$this_ability->print_name().'\'s petals healed its owner!'),
                'options' => array(
                    'apply_modifiers' => false,
                    'referred_recovery' => true,
                    'referred_recovery_id' => $this_robot->robot_id,
                    'referred_recovery_stats' => $this_robot->get_stats()
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // Define the frame offset given num pieces
        $this_attachment_info['ability_frame'] = 1;
        $this_attachment_info['ability_frame_animate'] = array();
        for ($i = 1; $i <= 8; $i++){$this_attachment_info['ability_frame_animate'][] = $i;  }
        //for ($i = 1; $i <= $num_shield_pieces; $i++){$this_attachment_info['ability_frame_animate'][] = $i;  }

        // Check if this ability is already summoned
        $is_summoned = false;
        $is_summoned_in_full = false;
        $is_summoned_tokens = array();
        if (!empty($this_robot->robot_attachments)){
            foreach ($this_robot->robot_attachments AS $token => $attachment){ if (strstr($token, $this_attachment_token.'_')){ $is_summoned_tokens[] = $token; } }
            if (!empty($is_summoned_tokens)){ $is_summoned = true; }
        }

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; $is_summoned_in_full = true; }

        // If the ability flag was not set, this ability begins charging
        if (!$is_summoned){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -10, $this_robot->print_name().' raises a '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

            // Increase this robot's defense stat
            $this_ability->target_options_update($this_attachment_info['attachment_create'], true);
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $temp_animate_sequence = $this_attachment_info['ability_frame_animate'];
            for ($i = 1; $i <= $num_shield_pieces; $i++){
                $temp_attachment_token = $this_attachment_token.'_'.$i;
                $temp_attachment_info = $this_attachment_info;
                $temp_attachment_info['attachment_token'] = $temp_attachment_token;
                $temp_attachment_info['ability_frame_animate'] = $temp_animate_sequence;
                $this_robot->robot_attachments[$temp_attachment_token] = $temp_attachment_info;
                array_push($temp_animate_sequence, array_shift($temp_animate_sequence));
                array_push($temp_animate_sequence, array_shift($temp_animate_sequence));
            }
            $this_robot->update_session();

        }
        // Else if the ability was summoned in full via charge module, heal all at once
        elseif ($is_summoned_in_full){

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'throw',
                'success' => array(0, 85, -10, -10, $this_robot->print_name().' summons the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            // Restore this robot's energy slightly if possible
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'type' => '',
                'type2' => '',
                'percent' => true,
                'rates' => array(100, 0, 0),
                'success' => array(2, -10, 0, -10, 'The barrier\'s petals restored '.$this_robot->print_name().'\'s energy!'),
                'failure' => array(2, -10, 0, -10, $this_robot->print_name().'\'s energy was not affected by the petals&hellip;')
                ));
            $energy_recovery_amount = ceil($this_robot->robot_base_energy * (($this_ability->ability_recovery2 * $num_shield_pieces * 2) / 100));
            $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);

        }
        // Else if the ability flag was set, pieces of the shield are consumed at the target
        else {

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(10, -9999, -9999, -10, $this_robot->print_name().' recalled the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            // Loop through and throw each piece of the shield
            $boost_count = 0;
            for ($i = 1; $i <= $num_shield_pieces; $i++){
                $temp_attachment_token = $this_attachment_token.'_'.$i;
                if (isset($this_robot->robot_attachments[$temp_attachment_token])){
                    $temp_attachment_info = $this_robot->robot_attachments[$temp_attachment_token];

                    // If the target robot is NOT disabled, we can shoot them again
                    if ($target_robot->robot_status != 'disabled'
                        && $target_robot->robot_energy > 0){

                        // Remove this ability attachment to the robot using it
                        unset($this_robot->robot_attachments[$temp_attachment_token]);
                        $this_robot->update_session();

                        // Restore this robot's energy slightly if possible
                        if ($this_robot->robot_energy < $this_robot->robot_base_energy){
                            $this_ability->recovery_options_update(array(
                                'kind' => 'energy',
                                'type' => '',
                                'type2' => '',
                                'percent' => true,
                                'rates' => array(100, 0, 0),
                                'success' => array(2, -10, 0, -10, (empty($boost_count) ? 'A' : 'Another').' petal restored '.$this_robot->print_name().'\'s energy!'),
                                'failure' => array(2, -10, 0, -10, '')
                                ));
                            $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery2 / 100));
                            $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);
                            if ($this_ability->ability_results['this_result'] != 'failure'){ $boost_count++; }
                        } else {
                            $this_battle->events_create(false, false, '', '');
                        }

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

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // Check if this ability is already summoned
        $is_summoned = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // Check if this ability is already summoned
        $is_summoned = false;
        $is_summoned_tokens = array();
        if (!empty($this_robot->robot_attachments)){
            foreach ($this_robot->robot_attachments AS $token => $attachment){ if (strstr($token, $this_attachment_token.'_')){ $is_summoned_tokens[] = $token; } }
            if (!empty($is_summoned_tokens)){ $is_summoned = true; }
        }

        // If the ability flag had already been set, reduce the weapon energy to zero
        if ($is_summoned){ $this_ability->set_energy(0); }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->reset_energy(); }

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; }

        // Update the ability image if the user is in their alt image
        $alt_image_triggers = array('plant-man_alt' => 2, 'plant-man_alt2' => 3, 'plant-man_alt9' => 4);
        if (isset($alt_image_triggers[$this_robot->robot_image])){ $ability_image = $this_ability->ability_token.'-'.$alt_image_triggers[$this_robot->robot_image]; }
        elseif ($this_robot->robot_core == 'copy'){ $ability_image = $this_ability->ability_token.'-5'; }
        else { $ability_image = $this_ability->ability_base_image; }
        $this_ability->set_image($ability_image);

        // Return true on success
        return true;

        }
    );
?>