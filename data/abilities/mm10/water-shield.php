<?
// WATER SHIELD
$ability = array(
    'ability_name' => 'Water Shield',
    'ability_token' => 'water-shield',
    'ability_game' => 'MM10',
    //'ability_group' => 'MM10/Weapons/074',
    'ability_group' => 'MM10/Weapons/073T2',
    'ability_description' => 'The user surrounds itself with an orbit of eight large water droplets, each acting as a separate shield and the reducing the damage from incoming attacks!  Each droplet can only withstand a single attack before fading, but any remaining ones can be thrown at the target for decent damage!',
    'ability_type' => 'water',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_damage' => 5,
    'ability_recovery2' => 10,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 94,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the total number of shield pieces
        $num_shield_pieces = 8;

        // Define this ability's attachment token
        $this_effect_multiplier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_token' => $this_attachment_token,
            'attachment_group' => $this_attachment_token,
            'attachment_damage_input_breaker' => $this_effect_multiplier,
            'attachment_weaknesses' => array('*'),
            'attachment_weaknesses_trigger' => 'target',
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
                    'A '.$this_ability->print_name().' droplet faded away!<br /> '.
                    $this_robot->print_name().' lost a bit of protection...'
                    ),
                'failure' => array(0, -9999, -9999, -9999,
                    'A '.$this_ability->print_name().' droplet faded away!<br /> '.
                    $this_robot->print_name().' lost a bit of protection...'
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // Define the frame offset given num pieces
        $this_attachment_info['ability_frame'] = 1;
        $this_attachment_info['ability_frame_animate'] = array();
        for ($i = 1; $i <= $num_shield_pieces; $i++){$this_attachment_info['ability_frame_animate'][] = $i;  }

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
            }
            $this_robot->update_session();

        }
        // Else if the ability was summoned in full via charge module, throw all at once
        elseif ($is_summoned_in_full){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -10, $this_robot->print_name().' raises a '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'throw',
                'success' => array(0, 85, -10, -10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability);

            // Inflict damage on the opposing robot
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(5, 0, 0),
                'success' => array(0, -75, 0, -10, 'The '.$this_ability->print_name().' crashed into the target!'),
                'failure' => array(0, -85, 0, -10, 'The '.$this_ability->print_name().' missed the target...')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(0, 0, 0),
                'success' => array(0, -75, 0, -10, 'The '.$this_ability->print_name().' crashed the target!'),
                'failure' => array(0, -85, 0, -10, 'The '.$this_ability->print_name().' missed the target...')
                ));
            $energy_damage_amount = ($this_ability->ability_base_damage * $num_shield_pieces);
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        }
        // Else if the ability flag was set, pieces of the shield are thrown all at once
        else {

            // Loop through and throw each piece of the shield
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

                        // Target the opposing robot
                        $this_ability->target_options_update(array(
                            'frame' => 'throw',
                            'success' => array(9, 110, -10, -10, $this_robot->print_name().' releases a '.$this_ability->print_name().' droplet!')
                            ));
                        $this_robot->trigger_target($target_robot, $this_ability);

                        // Inflict damage on the opposing robot
                        $this_ability->damage_options_update(array(
                            'kind' => 'energy',
                            'kickback' => array(5, 0, 0),
                            'success' => array(9, -35, 0, -10, 'The '.$this_ability->print_name().' droplet splashed into the target!'),
                            'failure' => array(9, -65, 0, -10, 'The '.$this_ability->print_name().' droplet missed the target...')
                            ));
                        $this_ability->recovery_options_update(array(
                            'kind' => 'energy',
                            'frame' => 'taunt',
                            'kickback' => array(0, 0, 0),
                            'success' => array(9, -35, 0, -10, 'The '.$this_ability->print_name().' droplet splashed the target!'),
                            'failure' => array(9, -65, 0, -10, 'The '.$this_ability->print_name().' droplet missed the target...')
                            ));
                        $energy_damage_amount = $this_ability->ability_damage;
                        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

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

        // Define the total number of shield pieces
        $num_shield_pieces = 8;

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;

        // Check if this ability is already summoned
        $is_summoned = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // Check if this ability is already summoned
        $is_summoned = false;
        $is_summoned_in_full = false;
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
        if ($this_robot->has_item('charge-module')){ $is_summoned = true; $is_summoned_in_full = true; }

        // If the user is holding a Target Module, allow bench targeting
        if ($is_summoned && $this_robot->has_item('target-module')){ $this_ability->set_target('select_target'); }
        else { $this_ability->reset_target(); }

        // If the ability is fully summoned, show the correct damage amount
        if ($is_summoned_in_full){ $this_ability->set_damage($this_ability->ability_base_damage * $num_shield_pieces); }
        else { $this_ability->reset_damage(); }

        // Return true on success
        return true;

        }
    );
?>