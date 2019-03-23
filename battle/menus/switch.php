<?
// Generate the markup for the action switch panel
ob_start();

    // Check if the switch should be disabled
    $this_switch_disabled = false;
    if ($this_robot->robot_status != 'disabled' && $this_robot->robot_position == 'active'){
        $this_robot_attachments = $this_robot->get_current_attachments();
        if (!empty($this_robot_attachments)){
            foreach ($this_robot_attachments AS $attachment_token => $attachment_info){
                if (isset($attachment_info['attachment_switch_disabled']) && $attachment_info['attachment_switch_disabled'] == 1){ $this_switch_disabled = true; }
            }
        }
    }

    // Check to see if a switch SHOULD be allowed
    $this_switch_required = false;
    if ($this_robot->robot_status == 'disabled' && $this_robot->robot_position == 'active'){
        $this_switch_required = true;
    }

    // Define and start the order counter
    $temp_order_counter = 1;

    // Display container for the main actions
    ?><div class="main_actions main_actions_hastitle"><span class="main_actions_title" style="<?= !empty($this_player->flags['switch_used_this_turn']) ? 'text-decoration: line-through;' : '' ?>">Select Switch Target <?= $this_switch_disabled ? '(Disabled)' : '' ?></span><?

    // Ensure there are robots to display
    if (!empty($this_player->player_robots)){

        // Count the total number of robots
        $num_robots = count($this_player->player_robots);
        $robot_direction = $this_player->player_side == 'left' ? 'right' : 'left';

        // Define the sorting function for the switch robots
        function mmrpg_action_sort_switch($info1, $info2){
            if ($info1['robot_position'] == 'active'){ return -1; }
            elseif ($info2['robot_position'] == 'active'){ return 1; }
            elseif ($info1['robot_key'] < $info2['robot_key']){ return -1; }
            elseif ($info1['robot_key'] > $info2['robot_key']){ return 1; }
            else { return 0; }
        }

        // Collect the temp ability and item indexes
        $db_ability_fields = rpg_ability::get_index_fields(true);
        $db_item_fields = rpg_item::get_index_fields(true);
        $temp_abilities_index = $db->get_array_list("SELECT {$db_ability_fields} FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
        $temp_items_index = $db->get_array_list("SELECT {$db_item_fields} FROM mmrpg_index_items WHERE item_flag_complete = 1;", 'item_token');

        // Collect the target robot options and sort them
        $switch_player_robots = $this_player->player_robots;
        $switch_robots_count = $this_player->counters['robots_active'];
        usort($switch_player_robots, 'mmrpg_action_sort_switch');

        // Loop through each robot and display its switch button
        foreach ($switch_player_robots AS $robot_key => $switch_robotinfo){
            // Ensure this is an actual switch in the index
            if (!empty($switch_robotinfo['robot_token'])){

                // Create the switch object using the session/index data
                $temp_robot = rpg_game::get_robot($this_battle, $this_player, $switch_robotinfo);

                // Check if the switch should be disabled based on attachments on this robot
                $temp_switch_disabled = false;
                if (!$this_switch_required
                    && $temp_robot->robot_status != 'disabled'){
                    $temp_robot_attachments = $temp_robot->get_current_attachments();
                    if (!empty($temp_robot_attachments)){
                        foreach ($temp_robot_attachments AS $attachment_token => $attachment_info){
                            if (!empty($attachment_info['attachment_switch_disabled'])){
                                $temp_switch_disabled = true;
                                break;
                            }
                        }
                    }
                }

                // If the switch is not disabled yet and the robot status isn't disabled, disable it
                if ($this_switch_disabled && $this_robot->robot_status != 'disabled'){ $temp_switch_disabled = true; }

                // If this player has already used a switch this turn
                if (!empty($this_player->flags['switch_used_this_turn'])){ $temp_switch_disabled = true; }

                // Default the allow button flag to true
                $allow_button = true;

                // If this robot is already out, disable the button
                if ($temp_robot->robot_position == 'active'){ $allow_button = false; }

                // If this robot is disabled, disable the button
                if ($temp_robot->robot_status == 'disabled'){ $allow_button = false; }

                // If this robot is the "active" one (maybe it was a force switch?)
                if ($switch_robots_count >= 2 && $temp_robot->robot_id == $this_robot->robot_id){ $allow_button = false; }

                // If the current robot has switching disabled
                if ($temp_switch_disabled){ $allow_button = false; }

                // Define the title hover for the robot
                $temp_robot_title = $temp_robot->robot_name.'  (Lv. '.$temp_robot->robot_level.')';
                $temp_robot_title .= ' <br />'.(!empty($temp_robot->robot_core) ? ucfirst($temp_robot->robot_core).' Core' : 'Neutral Core').' | '.ucfirst($temp_robot->robot_position).' Position';

                // Display the robot's item if it exists
                if (!empty($temp_robot->robot_item) && !empty($temp_items_index[$temp_robot->robot_item])){ $temp_robot_title .= ' | + '.$temp_items_index[$temp_robot->robot_item]['item_name'].' '; }

                // Display the robot's life and weapon energy current and base
                $temp_robot_title .= ' <br />'.$temp_robot->robot_energy.' / '.$temp_robot->robot_base_energy.' LE';
                $temp_robot_title .= ' | '.$temp_robot->robot_weapons.' / '.$temp_robot->robot_base_weapons.' WE';
                if ($robot_direction == 'right' && $temp_robot->robot_class != 'mecha'){
                    $temp_robot_title .= ' | '.$temp_robot->robot_experience.' / 1000 EXP';
                }
                $temp_robot_title .= ' <br />'.$temp_robot->robot_attack.' / '.$temp_robot->robot_base_attack.' AT';
                $temp_robot_title .= ' | '.$temp_robot->robot_defense.' / '.$temp_robot->robot_base_defense.' DF';
                $temp_robot_title .= ' | '.$temp_robot->robot_speed.' / '.$temp_robot->robot_base_speed.' SP';

                // Loop through this robot's current abilities and list them as well
                $temp_robot_title .= ' <br />';
                foreach ($temp_robot->robot_abilities AS $key => $token){
                    if (!isset($temp_abilities_index[$token])){ continue; }
                    if ($key > 0 && $key % 4 != 0){ $temp_robot_title .= '&nbsp;|&nbsp;'; }
                    if ($key > 0 && $key % 4 == 0){ $temp_robot_title .= '<br /> '; }
                    $info = rpg_ability::parse_index_info($temp_abilities_index[$token]);
                    $temp_robot_title .= $info['ability_name'];

                }

                // Encode the tooltip for markup insertion and create a plain one too
                $temp_robot_title_plain = strip_tags(str_replace('<br />', '//', $temp_robot_title));
                $temp_robot_title_tooltip = htmlentities($temp_robot_title, ENT_QUOTES, 'UTF-8');

                // Collect the robot's core types for display
                $temp_robot_core_type = !empty($temp_robot->robot_core) ? $temp_robot->robot_core : 'none';
                $temp_robot_core2_type = !empty($temp_robot->robot_core2) ? $temp_robot->robot_core2 : '';
                if (!empty($temp_robot->robot_item) && preg_match('/-core$/', $temp_robot->robot_item)){
                    $temp_item_core_type = preg_replace('/-core$/', '', $temp_robot->robot_item);
                    if (empty($temp_robot_core2_type) && $temp_robot_core_type != $temp_item_core_type){ $temp_robot_core2_type = $temp_item_core_type; }
                }

                // Define the robot button text variables
                $temp_robot_label = '<span class="multi">';
                $temp_robot_label .= '<span class="maintext">'.$temp_robot->robot_name.' <sup>Lv. '.$temp_robot->robot_level.'</sup></span>';
                $temp_robot_label .= '<span class="subtext">';
                    $temp_robot_label .= $temp_robot->robot_energy.'/'.$temp_robot->robot_base_energy.' Energy';
                $temp_robot_label .= '</span>';
                $temp_robot_label .= '<span class="subtext">';
                    $temp_robot_label .= 'A:'.$temp_robot->robot_attack;
                    $temp_robot_label .= '&nbsp;';
                    $temp_robot_label .= 'D:'.$temp_robot->robot_defense;
                    $temp_robot_label .= '&nbsp;';
                    $temp_robot_label .= 'S:'.$temp_robot->robot_speed;
                $temp_robot_label .= '</span>';
                $temp_robot_label .= '</span>';

                // Define the robot sprite variables
                $temp_robot_sprite = array();
                $temp_robot_sprite['name'] = $temp_robot->robot_name;
                $temp_robot_sprite['image'] = $temp_robot->robot_image;
                $temp_robot_sprite['image_size'] = $temp_robot->robot_image_size;
                $temp_robot_sprite['image_size_text'] = $temp_robot_sprite['image_size'].'x'.$temp_robot_sprite['image_size'];
                $temp_robot_sprite['image_size_zoom'] = $temp_robot->robot_image_size * 2;
                $temp_robot_sprite['image_size_zoom_text'] = $temp_robot_sprite['image_size'].'x'.$temp_robot_sprite['image_size'];
                $temp_robot_sprite['url'] = 'images/robots/'.$temp_robot->robot_image.'/sprite_'.$robot_direction.'_'.$temp_robot_sprite['image_size_text'].'.png';
                $temp_robot_sprite['preload'] = 'images/robots/'.$temp_robot->robot_image.'/sprite_'.$robot_direction.'_'.$temp_robot_sprite['image_size_zoom_text'].'.png';
                $temp_robot_sprite['class'] = 'sprite sprite_'.$temp_robot_sprite['image_size_text'].' sprite_'.$temp_robot_sprite['image_size_text'].'_'.($temp_robot->robot_energy > 0 ? ($temp_robot->robot_energy > ($temp_robot->robot_base_energy/2) ? 'base' : 'defend') : 'defeat').' ';
                $temp_robot_sprite['style'] = 'background-image: url('.$temp_robot_sprite['url'].'?'.MMRPG_CONFIG_CACHE_DATE.');  top: 5px; left: 5px; ';
                if ($temp_robot->robot_position == 'active'){ $temp_robot_sprite['style'] .= 'border-color: #ababab; '; }
                $temp_energy_percent = ceil(($temp_robot->robot_energy / $temp_robot->robot_base_energy) * 100);
                if ($temp_energy_percent > 50){ $temp_robot_sprite['class'] .= 'sprite_'.$temp_robot_sprite['image_size_text'].'_energy_high ';  }
                elseif ($temp_energy_percent > 25){ $temp_robot_sprite['class'] .= 'sprite_'.$temp_robot_sprite['image_size_text'].'_energy_medium ';  }
                elseif ($temp_energy_percent > 0){ $temp_robot_sprite['class'] .= 'sprite_'.$temp_robot_sprite['image_size_text'].'_energy_low '; }
                $temp_robot_sprite['markup'] = '<span class="'.$temp_robot_sprite['class'].'" style="'.$temp_robot_sprite['style'].'">'.$temp_robot_sprite['name'].'</span>';

                // Update the order button if necessary
                $order_button_markup = $allow_button ? 'data-order="'.$temp_order_counter.'"' : '';
                $temp_order_counter += $allow_button ? 1 : 0;

                // Now use the new object to generate a snapshot of this switch button
                ?><a <?= $order_button_markup ?> data-key="<?= $temp_robot->robot_key ?>" data-tooltip="<?= $temp_robot_title_tooltip ?>" class="button <?= !$allow_button ? 'button_disabled' : '' ?> action_switch switch_<?= $temp_robot->robot_token ?> status_<?= $temp_robot->robot_status ?> robot_type robot_type_<?= $temp_robot_core_type.(!empty($temp_robot_core2_type) ? '_'.$temp_robot_core2_type : '') ?> block_<?= $robot_key + 1 ?>" type="button" <?if($allow_button):?>data-action="switch_<?= $temp_robot->robot_id.'_'.$temp_robot->robot_token ?>"<?endif;?> data-preload="<?= $temp_robot_sprite['preload'] ?>"><label><?= $temp_robot_sprite['markup'] ?><?= $temp_robot_label ?></label></a><?

            }
        }

        // If there were less than 8 robots, fill in the empty spaces
        if ($num_robots < 8){
            for ($i = $num_robots; $i < 8; $i++){
                // Display an empty button placeholder
                ?><a class="button action_switch button_disabled block_<?= $i + 1 ?>" type="button">&nbsp;</a><?
            }
        }

    }

    // End the main action container tag
    ?></div><?
    // Display the back button by default
    $allow_back_button = $this_robot->robot_position == 'active' && $this_robot->robot_status != 'disabled' ? true : false;
    ?><div class="sub_actions"><a <?= $allow_back_button ? 'data-order="'.$temp_order_counter.'"' : '' ?> class="button action_back <?= !$allow_back_button ? 'button_disabled' : '' ?>" type="button" <?= $allow_back_button ? 'data-panel="battle"' : '' ?>><?= $allow_back_button ? '<label>Back</label>' : '&nbsp;' ?></a></div><?
    // Increment the order counter
    $temp_order_counter++;
$actions_markup['switch'] = trim(ob_get_clean());
$actions_markup['switch'] = preg_replace('#\s+#', ' ', $actions_markup['switch']);
?>