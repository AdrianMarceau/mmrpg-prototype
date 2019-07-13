<?
// AIR TWISTER
$ability = array(
    'ability_name' => 'Air Twister',
    'ability_token' => 'air-twister',
    'ability_game' => 'MM02',
    //'ability_group' => 'MM02/Weapons/010',
    'ability_group' => 'MM02/Weapons/009T2',
    'ability_description' => 'The user shields itself with a vortex of high-speed air, cutting the damage from incoming attacks in half!  At the end of the turn, the user unleashes the spinning vortex on the target to deal payback counter damage!',
    'ability_type' => 'wind',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_speed' => -6,
    'ability_speed2' => 6,
    'ability_damage' => 22,
    'ability_recovery2' => 50,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if the ability has been summoned yet
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        if (!empty($this_robot->flags[$summoned_flag_token])){ $has_been_summoned = true; }
        else { $has_been_summoned = false; }

        // Define this ability's attachment token
        $this_effect_multiplier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_id' => $this_attachment_token.'_shield',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_token' => $this_attachment_token,
            'attachment_damage_input_breaker' => $this_effect_multiplier,
            'attachment_duration' => 1,
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(1, -10, 0, -10,
                    $this_robot->print_name().' summons an '.$this_ability->print_name().'!<br /> '.
                    'The '.$this_ability->print_name().' resists damage! '
                    ),
                'failure' => array(1, -10, 0, -10,
                    $this_robot->print_name().' summons an '.$this_ability->print_name().'!<br /> '.
                    'The '.$this_ability->print_name().' resists damage! '
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
            'ability_frame_animate' => array(0, 1, 2),
            'ability_frame_offset' => array('x' => 90, 'y' => 0, 'z' => 20)
            );

        // If this ability has not been summoned yet, do the action and then queue a conclusion move
        $lifecounter_flag_token = $this_ability->ability_token.'_lifecounter';
        if (!$has_been_summoned){

            // Check to see if a Gemini Clone is attached and if it's active, then check to see if we can use it
            $has_gemini_clone = isset($this_robot->robot_attachments['ability_gemini-clone']) ? true : false;
            $required_weapon_energy = $this_robot->calculate_weapon_energy($this_ability);
            if ($has_gemini_clone && !$has_been_summoned){
                if ($this_robot->robot_weapons >= $required_weapon_energy){ $this_robot->set_weapons($this_robot->robot_weapons - $required_weapon_energy); }
                else { $has_gemini_clone = false; }
            }

            // If the robot was found to gave a Gemini Clone, set the appropriate flag value now
            if ($has_gemini_clone){ $this_robot->set_flag($summoned_flag_token.'_include_gemini_clone', true); }

            // Set the summoned flag on this robot and save
            $this_robot->flags[$summoned_flag_token] = true;
            $this_robot->counters[$lifecounter_flag_token] = $this_robot->robot_energy;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(2, 90, 0, 20,
                    $this_robot->print_name().' summons an '.$this_ability->print_name().'!<br /> '.
                    'The '.$this_ability->print_name().' resists damage! '
                    )
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

            // If we have a clone present, let's summon another ball
            if ($has_gemini_clone){

                // Create the cloned attachment with matching hologram styles
                $clone_attachment_token = $this_attachment_token.'_clone';
                $clone_attachment_info = $this_attachment_info;
                unset($clone_attachment_info['ability_id']);
                $clone_attachment_info['attachment_token'] = $clone_attachment_token;
                $clone_attachment_info['ability_frame_offset']['x'] -= 40;
                $clone_attachment_info['ability_frame_offset']['y'] -= 4;
                array_push($clone_attachment_info['ability_frame_animate'], array_shift($clone_attachment_info['ability_frame_animate']));
                $clone_attachment = rpg_game::get_ability($this_battle, $this_player, $this_robot, $clone_attachment_info);

                // Trigger the summon animation a second time and then attach the duplicate ball
                $this_robot->unset_flag('robot_is_using_ability');
                $this_robot->set_flag('gemini-clone_is_using_ability', true);
                $this_robot->set_attachment($clone_attachment_token, $clone_attachment_info);
                $this_ability->target_options_update(array(
                    'frame' => 'summon',
                    'success' => array(false, -9999, -9999, -9999,
                        $this_robot->print_name().' summoned another '.$this_ability->print_name().'!<br /> '.
                        'The second '.$this_ability->print_name().' resists damage too! '
                        )
                    ));
                $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));
                $this_robot->unset_flag('gemini-clone_is_using_ability');
                $this_robot->set_flag('robot_is_using_ability', true);

            }

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

            // Check to see if a Gemini Clone is attached and if it's active, then check to see if we can use it
            $has_gemini_clone = isset($this_robot->robot_attachments['ability_gemini-clone']) ? true : false;
            if (empty($this_robot->flags[$summoned_flag_token.'_include_gemini_clone'])){ $has_gemini_clone = false; }
            unset($this_robot->flags[$summoned_flag_token.'_include_gemini_clone']);

            // Calculate the difference in energy so we know how much payback
            $power_boost = 0;
            if (isset($this_robot->counters[$lifecounter_flag_token])
                && $this_robot->robot_energy != $this_robot->counters[$lifecounter_flag_token]
                && $this_robot->robot_energy < $this_robot->counters[$lifecounter_flag_token]){
                $power_boost += $this_robot->counters[$lifecounter_flag_token] - $this_robot->robot_energy;
            }

            // Remove the summoned flag from this robot
            $this_robot->unset_flag($summoned_flag_token);

            // Remove the life counter from this robot
            $this_robot->unset_counter($lifecounter_flag_token);

            // Remove the attachment from the summoner
            $this_robot->unset_attachment($this_attachment_token);

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'throw',
                'success' => array(0, 180, 5, 10, $this_robot->print_name().' releases the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(5, 0, 0),
                'success' => array(1, -80, 3, 10, 'The '.$this_ability->print_name().'\'s whirlwind hit the target!'),
                'failure' => array(1, -90, 3, -10, 'The '.$this_ability->print_name().'\'s whirlwind missed the target...')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(2, -80, 6, 10, 'The '.$this_ability->print_name().'\'s whirlwind hit the target!'),
                'failure' => array(2, -90, 6, -10, 'The '.$this_ability->print_name().'\'s whirlwind missed the target...')
                ));
            $energy_damage_amount = $this_ability->ability_damage + $power_boost;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // If a Gemini Clone is present and there's another explosive, we need to explode that one too
            if ($has_gemini_clone){

                // Remove this ability attachment to the robot using it
                $clone_attachment_token = $this_attachment_token.'_clone';
                unset($this_robot->robot_attachments[$clone_attachment_token]);
                $this_robot->update_session();

                // We can only show the kick animation if the target is not disabled
                if ($target_robot->robot_status != 'disabled'){

                    // Reverse the using ability flags for the robot
                    $this_robot->unset_flag('robot_is_using_ability');
                    $this_robot->set_flag('gemini-clone_is_using_ability', true);

                    // Target the opposing robot
                    $this_ability->target_options_update(array(
                        'frame' => 'throw',
                        'success' => array(0, 180, 5, 10, $this_robot->print_name().' releases the second '.$this_ability->print_name().'!')
                        ));
                    $this_robot->trigger_target($target_robot, $this_ability);

                    // Inflict damage on the opposing robot
                    $this_ability->damage_options_update(array(
                        'kind' => 'energy',
                        'kickback' => array(5, 0, 0),
                        'success' => array(1, -80, 3, 10, 'The second '.$this_ability->print_name().'\'s whirlwind hit the target!'),
                        'failure' => array(1, -90, 3, -10, 'The second '.$this_ability->print_name().'\'s whirlwind missed the target...')
                        ));
                    $this_ability->recovery_options_update(array(
                        'kind' => 'energy',
                        'frame' => 'taunt',
                        'kickback' => array(0, 0, 0),
                        'success' => array(2, -80, 6, 10, 'The second '.$this_ability->print_name().'\'s whirlwind hit the target!'),
                        'failure' => array(2, -90, 6, -10, 'The second '.$this_ability->print_name().'\'s whirlwind missed the target...')
                        ));
                    $energy_damage_amount = $this_ability->ability_damage + $power_boost;
                    $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

                    // Reverse the using ability flags for the robot
                    $this_robot->unset_flag('gemini-clone_is_using_ability');
                    $this_robot->set_flag('robot_is_using_ability', true);

                }

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