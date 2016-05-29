<?
// JUNK SHIELD
$ability = array(
  'ability_name' => 'Junk Shield',
  'ability_token' => 'junk-shield',
  'ability_game' => 'MM07',
    'ability_description' => 'The user surrounds itself with large pieces of scrap metal to bolster shields and prevent all damage from one attack! If the shield survives it can also be thrown at the target for damage!',
    'ability_type' => 'earth',
    'ability_type2' => 'shield',
    'ability_energy' => 4,
    'ability_damage' => 32,
    'ability_recovery2' => 100,
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
            'ability_token' => $this_ability->ability_token,
            'attachment_damage_input_breaker' => $this_effect_multiplier,
            'attachment_weaknesses' => array('*'),
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_ability_name().' resists all damage!<br /> '.
                    $this_robot->print_robot_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_ability_name().' resists all damage!<br /> '.
                    $this_robot->print_robot_name().'\'s defenses were bolstered!'
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
                'success' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_ability_name().' faded away!<br /> '.
                    $this_robot->print_robot_name().' is no longer protected...'
                    ),
                'failure' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_ability_name().' faded away!<br /> '.
                    $this_robot->print_robot_name().' is no longer protected...'
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(1, 2, 0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // If the ability flag was not set, skull barrier cuts damage by half
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -10, $this_robot->print_robot_name().' raises the '.$this_ability->print_ability_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Increase this robot's defense stat
            $this_ability->target_options_update($this_attachment_info['attachment_create'], true);
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

        }
        // Else if the ability flag was set, leaf shield is thrown and defense is lowered by 30%
        else {

            // Collect the attachment from the robot to back up its info
            $this_attachment_info = $this_robot->robot_attachments[$this_attachment_token];
            // Remove this ability attachment to the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, 85, -10, -10, $this_robot->print_robot_name().' releases the '.$this_ability->print_ability_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(5, 0, 0),
                'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_ability_name().' collided with the target!'),
                'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target...')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_ability_name().' collided with the target!'),
                'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_ability_name().' missed the target...')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

            // Decrease this robot's defense stat
            $this_ability->target_options_update($this_attachment_info['attachment_destroy']);
            $this_robot->trigger_target($this_robot, $this_ability);

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

        // If the ability flag had already been set, reduce the weapon energy to zero
        if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_energy = 0; }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->ability_energy = $this_ability->ability_base_energy; }

        // If this ability is already summoned, allow targetting benched robots
        if (isset($this_robot->robot_attachments[$this_attachment_token])){ $this_ability->ability_target = 'select_target'; }
        // Else if the ability attachment is not there, change the target back to auto
        else { $this_ability->ability_target = 'auto'; }

        // Update the ability session
        $this_ability->update_session();

        // Return true on success
        return true;

        }
    );
?>