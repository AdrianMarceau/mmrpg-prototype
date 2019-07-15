<?
// JEWEL POLISH
$ability = array(
    'ability_name' => 'Jewel Polish',
    'ability_token' => 'jewel-polish',
    'ability_game' => 'MM09',
    //'ability_group' => 'MM09/Weapons/069',
    'ability_group' => 'MM09/Weapons/065T1',
    'ability_description' => 'The user buffs and polishes itself or an ally to instantly reset any negative stat changes that may have been applied!  This ability can also remove field hazards around the target provided there\'s enough weapon energy to do so!',
    'ability_type' => 'crystal',
    'ability_energy' => 4,
    'ability_accuracy' => 100,
    'ability_target' => 'select_this',
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Collect the target robot and correct for bugs
        $is_target_self = false;
        if ($target_robot->robot_id != $this_robot->robot_id){ $temp_ally_robot = $target_robot; }
        else { $temp_ally_robot = $this_robot; $is_target_self = true; }

        // If the target has already been disabled, we cannot continue
        if ($temp_ally_robot->robot_status == 'disabled'){
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(0, -10, 40, -15,
                    $this_robot->print_name().' tried to use the '.$this_ability->print_name().' technique...<br /> '.
                    '...but '.$temp_ally_robot->print_name().' has already been disabled! '
                    )
                ));
            $this_robot->trigger_target($temp_ally_robot, $this_ability, array('prevent_default_text' => true));
            return false;
        }

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'sticky' => true,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1, 0, 2),
            'ability_frame_offset' => array('x' => -10, 'y' => 40, 'z' => 15),
            'attachment_duration' => 1
            );

        // Attach this ability to the summoning robot
        $temp_ally_robot->set_attachment($this_attachment_token, $this_attachment_info);

        // Update this robot's sprite to show them with an inverted pallet
        $temp_ally_robot->set_frame_styles('-moz-filter: brightness(120%); -webkit-filter: brightness(120%); filter: brightness(120%); ');

        // Update the ability's target options and trigger
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, -9999, -9999, -9999,
                $this_robot->print_name().' uses the '.$this_ability->print_name().' technique'.(!$is_target_self ? ' on '.$temp_ally_robot->print_name() : '').'!<br /> '.
                'Stat breaks and field hazards can be removed! '
                )
            ));
        $this_robot->trigger_target($temp_ally_robot, $this_ability, array('prevent_default_text' => true));

        // Attach this ability to the summoning robot
        $this_attachment_info['ability_frame_offset']['y'] += 10;
        $this_attachment_info['ability_frame_offset']['z'] *= -1;
        $temp_ally_robot->set_attachment($this_attachment_token, $this_attachment_info);

        // Define a flag for tracking if anything happened
        $something_happened = false;

        // First we need to reverse any negative stat changes to this robot
        $stats_reset = 0;
        $check_stats = array('attack', 'defense', 'speed');
        foreach ($check_stats AS $stat){
            if ($temp_ally_robot->counters[$stat.'_mods'] < 0){
                // Call the global stat reset function with customized options
                $stat_reset_text = $temp_ally_robot->print_name().' buffed away '.($stats_reset >= 1 ? 'another' : 'a').' stat break!<br /> ';
                rpg_ability::ability_function_stat_reset($temp_ally_robot, $stat, $this_ability, null, null, $stat_reset_text);
                $something_happened = true;
                $stats_reset += 1;
            }
        }

        // Attach this ability to the summoning robot
        if ($something_happened){
            $this_attachment_info['ability_frame_offset']['y'] += 10;
            $temp_ally_robot->set_attachment($this_attachment_token, $this_attachment_info);
        }

        // Collect a list of field attachments considered "hazards" and then remove each
        $negative_field_hazards = rpg_ability::get_negative_field_hazard_index();
        if ($temp_ally_robot->robot_position == 'active'){ $static_key = $this_player->player_side.'-active'; }
        else { $static_key = $this_player->player_side.'-bench-'.$temp_ally_robot->robot_key; }
        $static_field_hazards_index = array();
        foreach ($negative_field_hazards AS $key => $info){ $static_field_hazards_index['ability_'.$info['source'].'_'.$static_key] = $info; }
        if (!empty($this_battle->battle_attachments[$static_key])){
            $hazards_removed = 0;
            foreach ($this_battle->battle_attachments[$static_key] AS $static_attachment_token => $static_attachment_info){
                if (!empty($static_field_hazards_index[$static_attachment_token])){
                    // Ensure user has enough WE for effect
                    if ($this_robot->robot_weapons > 0){ $this_robot->set_weapons($this_robot->robot_weapons - 1); }
                    else { break; }
                    // Collect the hazard info and token
                    $hazard_info = $static_field_hazards_index[$static_attachment_token];
                    $hazard_token = $hazard_info['token'];
                    $static_ability_token = $hazard_info['source'];
                    // Update this field attachment with an opacity tweak before removing
                    if (!isset($static_attachment_info['ability_frame_styles'])){ $static_attachment_info['ability_frame_styles'] = ''; }
                    $static_attachment_info['ability_frame_styles'] .= ' opacity: 0.5; ';
                    $this_battle->battle_attachments[$static_key][$static_attachment_token] = $static_attachment_info;
                    $this_battle->update_session();
                    // Show a message about the attachment being removed
                    $static_ability_info = rpg_ability::get_index_info($static_ability_token);
                    $static_ability_object = rpg_game::get_ability($this_battle, $this_player, $temp_ally_robot, $static_ability_info);
                    $static_remove_frame = $hazards_removed % 2 == 0 ? 'taunt' : 'defend';
                    $static_remove_text = $this_robot->print_name().' removed '.($hazards_removed >= 1 ? 'another' : 'a').' field hazard!<br /> ';
                    $static_ability_object->set_name($hazard_info['noun']);
                    $static_remove_text .= 'The '.$static_ability_object->print_name().' '.$hazard_info['where'].' '.$temp_ally_robot->print_name().' faded away!';
                    $this_ability->target_options_update(array( 'frame' => $static_remove_frame, 'success' => array(0, -9999, -9999, -9999, $static_remove_text)));
                    $this_robot->trigger_target($temp_ally_robot, $this_ability, array('prevent_default_text' => true, 'canvas_show_this_ability' => false));
                    $static_ability_object->reset_name();
                    $something_happened = true;
                    $hazards_removed += 1;
                    // Remove this attachment from the field
                    unset($this_battle->battle_attachments[$static_key][$static_attachment_token]);
                    $this_battle->update_session();
                    // Show the attachment being removed via a frame
                    //$this_battle->events_create(false, false, '', '');

                }
            }
        }

        // If nothing happened, we should state as such
        if (!$something_happened){

            // Print out a failure message if nothing could be transferred
            $this_ability->target_options_update(array('frame' => 'defend', 'success' => array(0, -9999, -9999, -9999, 'But there was nothing to remove...')));
            $this_robot->trigger_target($temp_ally_robot, $this_ability, array('prevent_default_text' => true));

        }

        // Remove the inverted styles from this robot's sprite
        $temp_ally_robot->set_frame_styles('');

        // Unset this ability attachment from the summoning robot
        $temp_ally_robot->unset_attachment($this_attachment_token);

        // Return true on success
        return true;

        }
    );
?>