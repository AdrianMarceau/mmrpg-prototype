<?
// FIELD SUPPORT
$ability = array(
    'ability_name' => 'Field Support',
    'ability_token' => 'field-support',
    'ability_game' => 'MMRPG',
    'ability_group' => 'MMRPG/Support/Special',
    'ability_image_sheets' => 4,
    'ability_description' => 'The user increases the field multiplier matching their own core type and then lowers the field multipliers matching their weaknesses, tilting the odds in their favour! However, the weapon energy cost for this ability increases after each use and it works differently for Neutral and Copy Core robots.',
    'ability_energy' => 5,
    'ability_speed' => 10,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);
        $mmrpg_index_fields = rpg_field::get_index();

        // Define field multipliers differently for Copy Core, Elemental Core, and Neutral Core
        $this_field_multipliers = array();
        if ($this_robot->robot_core === 'copy'){

            // Define the field multipliers for when a Copy Core robot uses the ability (multiply existing)
            $this_field_multipliers = !empty($this_field->field_multipliers) ? $this_field->field_multipliers : array();

        } elseif (empty($this_robot->robot_core)){

            // Define the field multipliers for when a Neutral Core robot uses the ability (remove existing)
            foreach ($this_field->field_multipliers AS $temp_type => $temp_multiplier){
                if ($temp_multiplier == 1){ continue; }
                $temp_new_multiplier = $temp_multiplier / ($temp_multiplier * $temp_multiplier);
                $this_field_multipliers[$temp_type] = $temp_new_multiplier;
            }

        } else {

            // Define the field multipliers for when an Elemental Core robot uses the ability (boost core, break weaknesses)
            $this_field_multipliers[$this_robot->robot_core] = 1.5;
            if (!empty($this_robot->robot_weaknesses)){
                foreach ($this_robot->robot_weaknesses AS $temp_type){
                    $this_field_multipliers[$temp_type] = 0.5;
                }
            }

        }

        // Only continue with the ability if there are multipliers to apply
        if (!empty($this_field_multipliers)){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -9999, -9999, -10,
                    $this_robot->print_name().' activated the '.$this_ability->print_name().'!<br />'.
                    'The ability altered the conditions of the battle field&hellip;'
                    )
                ));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

            // Loop through each of the field multipliers collected and apply them to the current conditions
            $temp_modifiers_applied = 0;
            asort($this_field_multipliers);
            $this_field_multipliers = array_reverse($this_field_multipliers);
            foreach ($this_field_multipliers AS $type_token => $type_multiplier){

                // Define the modify and boost parameters for this multiplier
                $type_name = ucfirst($type_token);
                if ($type_multiplier > 1){ $temp_modify_amount = 0.1; }
                elseif ($type_multiplier < 1){ $temp_modify_amount = -0.1; }
                else { $temp_modify_amount = 0; }

                // Only continue if there was a difference to boost
                if (!empty($temp_modify_amount)){

                    // Update the field multipliers accordingly
                    if (!isset($this_field->field_multipliers[$type_token])){ $this_field->field_multipliers[$type_token] = 1; }
                    $temp_first_amount = $this_field->field_multipliers[$type_token];
                    $this_field->field_multipliers[$type_token] = $this_field->field_multipliers[$type_token] * $type_multiplier;
                    if ($this_field->field_multipliers[$type_token] > MMRPG_SETTINGS_MULTIPLIER_MAX){ $this_field->field_multipliers[$type_token] = MMRPG_SETTINGS_MULTIPLIER_MAX; }
                    elseif ($this_field->field_multipliers[$type_token] < MMRPG_SETTINGS_MULTIPLIER_MIN){ $this_field->field_multipliers[$type_token] = MMRPG_SETTINGS_MULTIPLIER_MIN; }
                    // If the new amount was exactly one, remove it alltogether
                    $temp_new_amount = round($this_field->field_multipliers[$type_token], 1);
                    if ($temp_new_amount == 1){ unset($this_field->field_multipliers[$type_token]); }
                    else { $this_field->field_multipliers[$type_token] = $temp_new_amount; }

                    // Update the session with the new field changes
                    $this_field->update_session();

                    // Define the boost or lower percent
                    $temp_change_text = '';
                    $temp_change_text2 = '';
                    if ($temp_new_amount > $temp_first_amount){
                        $temp_change = $temp_new_amount - $temp_first_amount;
                        $temp_change_percent = round(($temp_change / $temp_first_amount) * 100);
                        $temp_change_text = 'boosted';
                        $temp_change_text2 = 'intensified';
                        //$temp_change_alert = rpg_battle::random_positive_word();
                        $temp_change_alert = $this_player->player_side == 'left' ? rpg_battle::random_positive_word() : rpg_battle::random_negative_word();
                    } elseif ($temp_new_amount < $temp_first_amount){
                        $temp_change = $temp_first_amount - $temp_new_amount;
                        $temp_change_percent = round(($temp_change / $temp_first_amount) * 100);
                        $temp_change_text = 'reduced';
                        $temp_change_text2 = 'worsened';
                        //$temp_change_alert = rpg_battle::random_positive_word();
                        $temp_change_alert = $this_player->player_side == 'left' ? rpg_battle::random_positive_word() : rpg_battle::random_negative_word();
                    } else {
                        continue;
                    }

                    // Update this robot's frame to a taunt
                    $this_robot->robot_frame = $temp_modify_amount > 0 ? 'taunt' : 'defend';
                    $this_robot->update_session();

                    // CREATE ATTACHMENTS
                    if (true){

                        // Define this ability's attachment token
                        $this_star_index = mmrpg_prototype_star_image($type_token);
                        if ($temp_modify_amount < 0){ $this_star_index['sheet'] += 2; }
                        $this_attachment_token = 'ability_'.$this_ability->ability_token;
                        $this_attachment_info = array(
                            'class' => 'ability',
                            'ability_token' => $this_ability->ability_token,
                            'ability_image' => $this_ability->ability_token.($this_star_index['sheet'] > 1 ? '-'.$this_star_index['sheet'] : ''),
                            'ability_frame' => $this_star_index['frame'],
                            'ability_frame_animate' => array($this_star_index['frame']),
                            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
                            );

                        // Attach this ability attachment to this robot temporarily
                        $this_robot->robot_frame = $temp_modify_amount > 0 ? 'taunt' : 'defend';
                        $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                        $this_robot->update_session();

                        // Attach this ability to all robots on this player's side of the field
                        $backup_robots_active = $this_player->values['robots_active'];
                        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
                        if ($backup_robots_active_count > 0){
                            // Loop through the this's benched robots, inflicting les and less damage to each
                            $this_key = 0;
                            foreach ($backup_robots_active AS $key => $info){
                                if ($info['robot_id'] == $this_robot->robot_id){ continue; }
                                $temp_this_robot = rpg_game::get_robot($this_battle, $this_player, $info);
                                // Attach this ability attachment to the this robot temporarily
                                $temp_this_robot->robot_frame = $temp_modify_amount > 0 ? 'taunt' : 'defend';
                                $temp_this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                                $temp_this_robot->update_session();
                                $this_key++;
                            }
                        }

                        // Attach this ability to all robots on the target's side of the field
                        $backup_robots_active = $target_player->values['robots_active'];
                        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
                        if ($backup_robots_active_count > 0){
                            // Loop through the target's benched robots, inflicting les and less damage to each
                            $target_key = 0;
                            foreach ($backup_robots_active AS $key => $info){
                                $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                                // Attach this ability attachment to the target robot temporarily
                                $temp_target_robot->robot_frame = $temp_modify_amount > 0 ? 'taunt' : 'defend';
                                $temp_target_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
                                $temp_target_robot->update_session();
                                $target_key++;
                            }
                        }

                    }

                    // Create the event to show this multiplier boost or lowering
                    $first_text = '';
                    if ($this_robot->robot_core === 'copy'){ $effect_text = '<span class="ability_name ability_type ability_type_'.$type_token.'">'.$type_name.' Effects</span> were '.$temp_change_text2.'!<br />'; }
                    elseif (!empty($this_robot->robot_core)){ $effect_text = '<span class="ability_name ability_type ability_type_'.$type_token.'">'.$type_name.' Effects</span> were '.$temp_change_text.' by '.$temp_change_percent.'%!<br />'; }
                    else { $effect_text = '<span class="ability_name ability_type ability_type_'.$type_token.'">'.$type_name.' Effects</span> returned to normal!<br />'; }
                    $this_battle->events_create($this_robot, false, $this_field->field_name.' Multipliers',
                        $temp_change_alert.' '.$effect_text.
                        'The multiplier is now at <span class="ability_name ability_type ability_type_'.$type_name.'">'.$type_name.' x '.number_format($temp_new_amount, 1).'</span>!',
                        array('canvas_show_this_ability_overlay' => true)
                        );

                    // DESTROY ATTACHMENTS
                    if (true){

                        // Remove this ability from all robots on this player's side of the field
                        $backup_robots_active = $this_player->values['robots_active'];
                        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
                        if ($backup_robots_active_count > 0){
                            // Loop through the this's benched robots, inflicting les and less damage to each
                            $this_key = 0;
                            foreach ($backup_robots_active AS $key => $info){
                                if ($info['robot_id'] == $this_robot->robot_id){ continue; }
                                $temp_this_robot = rpg_game::get_robot($this_battle, $this_player, $info);
                                // Attach this ability attachment to the this robot temporarily
                                unset($temp_this_robot->robot_attachments[$this_attachment_token]);
                                $temp_this_robot->update_session();
                                $this_key++;
                            }
                        }

                        // Remove this ability from all robots on the target's side of the field
                        $backup_robots_active = $target_player->values['robots_active'];
                        $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
                        if ($backup_robots_active_count > 0){
                            // Loop through the target's benched robots, inflicting les and less damage to each
                            $target_key = 0;
                            foreach ($backup_robots_active AS $key => $info){
                                if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                                $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                                // Attach this ability attachment to the target robot temporarily
                                unset($temp_target_robot->robot_attachments[$this_attachment_token]);
                                $temp_target_robot->update_session();
                                $target_key++;
                            }
                        }

                        // Remove this ability attachment from this robot
                        unset($this_robot->robot_attachments[$this_attachment_token]);
                        $this_robot->update_session();

                        // Remove this ability attachment from the target robot
                        unset($target_robot->robot_attachments[$this_attachment_token]);
                        $target_robot->update_session();

                    }

                    // Update the field multiplier
                    $temp_modifiers_applied++;

                }

            }
            // Otherwise print a nothing happened message
            if (empty($temp_modifiers_applied)){

                // Update the ability's target options and trigger
                $this_ability->target_options_update(array(
                    'frame' => 'defend',
                    'success' => array(0, 0, 0, 10, '&hellip;but nothing happened.')
                    ));
                $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true));

            }

            // Update this robot's frame to a base
            $this_robot->robot_frame = 'base';
            $this_robot->update_session();

        }
        // Otherwise print a nothing happened message
        else {

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -9999, -9999, -10,
                    $this_robot->print_name().' activated the '.$this_ability->print_name().'&hellip;'
                    )
                ));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(0, -9999, -9999, -10,
                    '&hellip;but nothing happened!'
                    )
                ));
            $this_robot->trigger_target($this_robot, $this_ability, array('prevent_default_text' => true));

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if this ability has been used already, and if so increase the cost
        if (!empty($this_robot->history['triggered_abilities'])){
            $trigger_counts = array_count_values($this_robot->history['triggered_abilities']);
            if (!empty($trigger_counts[$this_ability->ability_token])){
                $trigger_count = $trigger_counts[$this_ability->ability_token];
                $new_energy_cost = $this_ability->ability_base_energy * ($trigger_count + 1);
                $this_ability->set_energy($new_energy_cost);
            }
        }

        // Return true on success
        return true;

        }
    );
?>