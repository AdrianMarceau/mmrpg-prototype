<?
// LEAF SHIELD
$ability = array(
    'ability_name' => 'Leaf Shield',
    'ability_token' => 'leaf-shield',
    'ability_game' => 'MM02',
    'ability_group' => 'MM02/Weapons/016',
    'ability_description' => 'The user surrounds itself with sharp leaf-like blades to bolster shields and reduce damage by {RECOVERY2}%! The leaf blades can also be thrown at the target for massive damage!',
    'ability_type' => 'nature',
    'ability_type2' => 'shield',
    'ability_energy' => 4,
    'ability_damage' => 28,
    'ability_recovery2' => 40,
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
            'attachment_token' => $this_attachment_token,
            'attachment_damage_input_breaker' => $this_effect_multiplier,
            'attachment_weaknesses' => array('flame'),
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(1, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array(1, -10, 0, -10,
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
                'success' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_name().' faded away!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    ),
                'failure' => array(9, -10, 0, -10,
                    'The '.$this_ability->print_name().' faded away!<br /> '.
                    $this_robot->print_name().' is no longer protected...'
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // Check if this ability is already summoned
        $is_summoned = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; }

        // If the ability flag was not set, this ability begins charging
        if (!$is_summoned){

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
                'success' => array(1, -75, 0, -10, 'The '.$this_ability->print_name().' crashed the target!'),
                'failure' => array(1, -85, 0, -10, 'The '.$this_ability->print_name().' missed the target...')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

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

        // If the ability flag had already been set, reduce the weapon energy to zero
        if ($is_summoned){ $this_ability->set_energy(0); }
        // Otherwise, return the weapon energy back to default
        else { $this_ability->reset_energy(); }

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; }

        // If the user is holding a Target Module, allow bench targeting
        if ($is_summoned && $this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // Return true on success
        return true;

        }
    );
?>