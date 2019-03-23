<?
// RAIN FLUSH
$ability = array(
    'ability_name' => 'Rain Flush',
    'ability_token' => 'rain-flush',
    'ability_game' => 'MM04',
    //'ability_group' => 'MM04/Weapons/026',
    'ability_group' => 'MM04/Weapons/025T2',
    'ability_image_sheets' => 2,
    'ability_description' => 'The user releases a large capsule into the air that showers the field in acid rain and damages all robots on the opponent\'s side of the battle! This ability\'s damage is not affected by attack stats, defense stats, or position variables.',
    'ability_type' => 'water',
    'ability_energy' => 8,
    'ability_damage' => 50,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_token.'-2',
            'ability_frame' => 0,
            'ability_frame_animate' => array(0, 1),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 999),
            'ability_frame_classes' => 'sprite_fullscreen '
            );

        // Count the number of active robots on the target's side of the field
        $target_robots_active = $target_player->counters['robots_active'];

        // Change the image to the full-screen rain effect
        $this_ability->ability_image = 'rain-flush';
        $this_ability->ability_frame_classes = '';
        $this_ability->update_session();

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(1, 10, 100, 10, $this_robot->print_name().' releases the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_stats_text' => true));

        // Change the image to the full-screen rain effect
        $this_ability->ability_image = 'rain-flush-2';
        $this_ability->ability_frame_classes = 'sprite_fullscreen ';
        $this_ability->update_session();

        // Ensure this robot stays in the summon position for the duration of the attack
        $this_robot->robot_frame = 'summon';
        $this_robot->update_session();

        // CREATE ATTACHMENTS
        if (true){

            // Define this ability's attachment token
            $temp_attachment_token = 'ability_'.$this_ability->ability_token.'_protect';
            $temp_attachment_info = array(
                'class' => 'ability',
                'ability_token' => $this_ability->ability_token,
                'ability_image' => $this_ability->ability_token,
                'ability_frame' => 2,
                'ability_frame_animate' => array(2),
                'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => 20)
                );

            // Attach this ability to all robots on this player's side of the field
            $backup_robots_active = $this_player->values['robots_active'];
            $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
            if ($backup_robots_active_count > 0){
                $this_key = 0;
                foreach ($backup_robots_active AS $key => $info){
                    if ($info['robot_id'] == $this_robot->robot_id){ continue; }
                    $info2 = array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']);
                    $temp_this_robot = rpg_game::get_robot($this_battle, $this_player, $info2);
                    $temp_this_robot->robot_frame = 'defend';
                    $temp_this_robot->robot_attachments[$temp_attachment_token] = $temp_attachment_info;
                    $temp_this_robot->update_session();
                    $this_key++;
                }
            }

        }


        // -- DAMAGE TARGETS -- //

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'modifiers' => true,
            'kickback' => array(5, 0, 0),
            'success' => array(0, -5, 0, 99, 'The '.$this_ability->print_name().' melts through the target!'),
            'failure' => array(0, -5, 0, 99,'The '. $this_ability->print_name().' had no effect on '.$target_robot->print_name().'&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'modifiers' => true,
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(0, -5, 0, 9, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(0, -5, 0, 9, 'The '.$this_ability->print_name().' had no effect on '.$target_robot->print_name().'&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $trigger_options = array('apply_modifiers' => true, 'apply_position_modifiers' => false, 'apply_stat_modifiers' => false);
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);

        // Loop through the target's benched robots, inflicting damage to each
        $backup_target_robots_active = $target_player->values['robots_active'];
        foreach ($backup_target_robots_active AS $key => $info){
            if ($info['robot_id'] == $target_robot->robot_id){ continue; }
            $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'modifiers' => true,
                'kickback' => array(5, 0, 0),
                'success' => array(($key % 2), -5, 0, 99, 'The '.$this_ability->print_name().' melts through the target!'),
                'failure' => array(($key % 2), -5, 0, 99,'The '. $this_ability->print_name().' had no effect on '.$temp_target_robot->print_name().'&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'modifiers' => true,
                'frame' => 'taunt',
                'kickback' => array(5, 0, 0),
                'success' => array(($key % 2), -5, 0, 9, 'The '.$this_ability->print_name().' was absorbed by the target!'),
                'failure' => array(($key % 2), -5, 0, 9, 'The '.$this_ability->print_name().' had no effect on '.$temp_target_robot->print_name().'&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
        }

        // REMOVE ATTACHMENTS
        if (true){

            // Attach this ability to all robots on this player's side of the field
            $backup_robots_active = $this_player->values['robots_active'];
            $backup_robots_active_count = !empty($backup_robots_active) ? count($backup_robots_active) : 0;
            if ($backup_robots_active_count > 0){
                $this_key = 0;
                foreach ($backup_robots_active AS $key => $info){
                    if ($info['robot_id'] == $this_robot->robot_id){ continue; }
                    $info2 = array('robot_id' => $info['robot_id'], 'robot_token' => $info['robot_token']);
                    $temp_this_robot = rpg_game::get_robot($this_battle, $this_player, $info2);
                    $temp_this_robot->robot_frame = 'base';
                    unset($temp_this_robot->robot_attachments[$temp_attachment_token]);
                    $temp_this_robot->update_session();
                    $this_key++;
                }
            }

        }

        // Loop through all robots on the target side and disable any that need it
        $target_robots_active = $target_player->get_robots();
        foreach ($target_robots_active AS $key => $robot){
            if ($robot->robot_id == $target_robot->robot_id){ $temp_target_robot = $target_robot; }
            else { $temp_target_robot = $robot; }
            if (($temp_target_robot->robot_energy < 1 || $temp_target_robot->robot_status == 'disabled')
                && empty($temp_target_robot->flags['apply_disabled_state'])){
                $temp_target_robot->trigger_disabled($this_robot);
            }
        }

        // Change the image to the full-screen rain effect
        $this_ability->ability_image = 'rain-flush';
        $this_ability->ability_frame_classes = '';
        $this_ability->update_session();

        // Return true on success
        return true;


        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Set or update this ability's base damage equal to user's level
        //$this_ability->ability_damage = $this_robot->robot_level;
        // Update the ability session
        //$this_ability->update_session();

        // Return true on success
        return true;

        }
    );
?>