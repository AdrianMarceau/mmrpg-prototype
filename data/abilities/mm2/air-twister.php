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
    'ability_speed' => 2,
    'ability_damage' => 16,
    'ability_recovery2' => 50,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 96,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

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
        $summoned_flag_token = $this_ability->ability_token.'_summoned';
        $lifecounter_flag_token = $this_ability->ability_token.'_lifecounter';
        if (empty($this_robot->flags[$summoned_flag_token])){

            // Set the summoned flag on this robot and save
            $this_robot->flags[$summoned_flag_token] = true;
            $this_robot->counters[$lifecounter_flag_token] = $this_robot->robot_energy;
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(2, 90, 0, 20, $this_robot->print_name().' summons an '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

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
                $this_ability->ability_id.'_'.$this_ability->ability_token
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

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
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

        }

        // Return true on success
        return true;

    }
    );
?>