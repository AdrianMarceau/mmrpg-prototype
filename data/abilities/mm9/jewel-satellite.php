<?
// JEWEL SATELLITE
$ability = array(
    'ability_name' => 'Jewel Satellite',
    'ability_token' => 'jewel-satellite',
    'ability_game' => 'MM09',
    'ability_group' => 'MM09/Weapons/069',
    'ability_description' => 'The user raises a shield of orbiting diamonds that protect it from all Flame, Nature, Electric, and Water type damage. The group of diamonds can also be thrown at any target to deal massive damage!',
    'ability_type' => 'crystal',
    'ability_type2' => 'shield',
    'ability_energy' => 4,
    'ability_damage' => 26,
    'ability_recovery2' => 100,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 94,
    'ability_target' => 'select_target',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_effect_multiplier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'attachment_damage_input_breaker_flame' => $this_effect_multiplier,
            'attachment_damage_input_breaker_nature' => $this_effect_multiplier,
            'attachment_damage_input_breaker_electric' => $this_effect_multiplier,
            'attachment_damage_input_breaker_water' => $this_effect_multiplier,
            'attachment_weaknesses' => array('impact'),
            'attachment_create' => array(
                'kind' => 'defense',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists all elemental damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists all elemental damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    )
                ),
            'attachment_destroy' => array(
                'kind' => 'defense',
                'type' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_name().' was destroyed!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    ),
                'failure' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_name().' was destroyed!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // If the ability flag was not set, skull barrier cuts damage by half
        if (!isset($this_robot->robot_attachments[$this_attachment_token])){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -10, $this_robot->print_name().' raises a '.$this_ability->print_name().'!')
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
                'success' => array(0, 85, -10, -10, $this_robot->print_name().' releases the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(5, 0, 0),
                'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_name().' crashed into the target!'),
                'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_name().' missed the target...')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_name().' crashed into the target!'),
                'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_name().' missed the target...')
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
        // Update the ability session
        $this_ability->update_session();

        // If this ability is being used by a robot with the same core type AND it's already summoned, allow targetting
        if (isset($this_robot->robot_attachments[$this_attachment_token])){

            // Update this ability's targetting setting
            $this_ability->ability_target = 'select_target';
            $this_ability->update_session();

        }
        // Else if the ability attachment is not there, change the target back to auto
        else {

            // Update this ability's targetting setting
            $this_ability->ability_target = 'auto';
            $this_ability->update_session();

        }

        // Return true on success
        return true;

        }
    );
?>