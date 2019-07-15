<?
// JEWEL SATELLITE
$ability = array(
    'ability_name' => 'Jewel Satellite',
    'ability_token' => 'jewel-satellite',
    'ability_game' => 'MM09',
    //'ability_group' => 'MM09/Weapons/069',
    'ability_group' => 'MM09/Weapons/065T2',
    'ability_image_sheets' => 6,
    'ability_description' => 'The user surrounds itself with an orbit of four large diamonds, each acting as a separate elemental shield and offering protection against Nature, Flame, Electric, and Water-type damage!  Each diamond can only withstand a single attack, but any remaining ones can be thrown at a target for damage!',
    'ability_type' => 'crystal',
    'ability_type2' => 'shield',
    'ability_energy' => 8,
    'ability_damage' => 10,
    'ability_recovery2' => 100,
    'ability_recovery_percent2' => true,
    'ability_accuracy' => 94,
    'ability_target' => 'auto',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define the total number of shield pieces
        $list_shield_elements = array('nature', 'flame', 'electric', 'water');
        $num_shield_pieces = count($list_shield_elements);

        // Define this ability's attachment token
        $this_effect_multiplier = 1 - ($this_ability->ability_recovery2 / 100);
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_image,
            'attachment_token' => $this_attachment_token,
            'attachment_group' => $this_attachment_token,
            //'attachment_damage_input_breaker' => $this_effect_multiplier,
            //'attachment_weaknesses' => array('*'),
            'attachment_weaknesses_trigger' => 'target',
            'attachment_create' => array(
                'trigger' => 'special',
                'kind' => '',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'rates' => array(100, 0, 0),
                'success' => array(0, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists elemental damage!<br /> '.
                    $this_robot->print_name().'\'s defenses were bolstered!'
                    ),
                'failure' => array(0, -10, 0, -10,
                    'The '.$this_ability->print_name().' resists elemental damage!<br /> '.
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
                    'The '.$this_ability->print_name().' diamonds faded away!<br /> '.
                    $this_robot->print_name().' lost a bit of protection...'
                    ),
                'failure' => array(0, -9999, -9999, -9999,
                    'The '.$this_ability->print_name().' diamonds faded away!<br /> '.
                    $this_robot->print_name().' lost a bit of protection...'
                    )
                ),
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => -10, 'y' => 0, 'z' => -10)
            );

        // Define the frame offset given num pieces
        $this_attachment_info['ability_frame'] = 1;
        $this_attachment_info['ability_frame_animate'] = array();
        for ($i = 1; $i <= 8; $i++){$this_attachment_info['ability_frame_animate'][] = $i;  }

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
                $temp_attachment_element = $list_shield_elements[$i - 1];
                $temp_attachment_image = $this_ability->ability_image.'-'.(2 + $i);
                $temp_attachment_token = $this_attachment_token.'_'.$i;
                $temp_attachment_info = $this_attachment_info;
                $temp_attachment_info['attachment_token'] = $temp_attachment_token;
                $temp_attachment_info['ability_image'] = $temp_attachment_image;
                $temp_attachment_info['ability_frame_animate'] = $temp_animate_sequence;
                $temp_attachment_info['attachment_damage_input_breaker_'.$temp_attachment_element] = $this_effect_multiplier;
                $temp_attachment_info['attachment_weaknesses'] = array($temp_attachment_element);
                $temp_destroy_text = $temp_attachment_info['attachment_destroy']['success'][4];
                $temp_type_span = '<span class="ability_name ability_type ability_type_'.$temp_attachment_element.'">'.ucfirst($temp_attachment_element).'</span>';
                $temp_destroy_text = 'One of the '.$this_ability->print_name().'\'s diamonds faded away!<br /> ';
                $temp_destroy_text .= $this_robot->print_name().' is no longer protected from the '.$temp_type_span.' type... ';
                $temp_attachment_info['attachment_destroy']['success'][4] = $temp_destroy_text;
                $temp_attachment_info['attachment_destroy']['failure'][4] = $temp_destroy_text;
                $this_robot->robot_attachments[$temp_attachment_token] = $temp_attachment_info;
                array_push($temp_animate_sequence, array_shift($temp_animate_sequence));
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
                'success' => array(0, 115, -10, -10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!')
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
                        $this_ability->set_image($temp_attachment_info['ability_image']);
                        $this_ability->target_options_update(array(
                            'frame' => 'throw',
                            'success' => array(0, 110, -10, -10, $this_robot->print_name().' releases '.($i > 1 ? 'another diamond' : 'a '.$this_ability->print_name().' diamond').'!')
                            ));
                        $this_robot->trigger_target($target_robot, $this_ability);
                        $this_ability->reset_image();

                        // Inflict damage on the opposing robot
                        $this_ability->set_image($temp_attachment_info['ability_image']);
                        $this_ability->damage_options_update(array(
                            'kind' => 'energy',
                            'kickback' => array(5, 0, 0),
                            'success' => array(1, -35, 0, -10, 'The '.$this_ability->print_name().' diamond crashed into the target!'),
                            'failure' => array(1, -65, 0, -10, 'The '.$this_ability->print_name().' diamond missed the target...')
                            ));
                        $this_ability->recovery_options_update(array(
                            'kind' => 'energy',
                            'frame' => 'taunt',
                            'kickback' => array(0, 0, 0),
                            'success' => array(1, -35, 0, -10, 'The '.$this_ability->print_name().' diamond crashed into the target!'),
                            'failure' => array(1, -65, 0, -10, 'The '.$this_ability->print_name().' diamond missed the target...')
                            ));
                        $energy_damage_amount = $this_ability->ability_damage;
                        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);
                        $this_ability->reset_image();

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
        $list_shield_elements = array('nature', 'flame', 'electric', 'water');
        $num_shield_pieces = count($list_shield_elements);

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

        // If the ability is already charged, allow bench targeting
        if ($is_summoned){ $this_ability->set_target('select_target'); }
        else { $this_ability->set_target('auto'); }

        // If the ability is fully summoned, show the correct damage amount
        if ($is_summoned_in_full){ $this_ability->set_damage($this_ability->ability_base_damage * $num_shield_pieces); }
        else { $this_ability->reset_damage(); }

        // Return true on success
        return true;

        }
    );
?>