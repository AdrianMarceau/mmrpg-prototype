<?
// DYNAMO BOLT
$ability = array(
    'ability_name' => 'Dynamo Bolt',
    'ability_token' => 'dynamo-bolt',
    'ability_game' => 'MM085',
    //'ability_group' => 'MM10B/Weapons/001',
    'ability_group' => 'MM085/Weapons/001T2',
    'ability_description' => 'The user charges themselves with electricity to build power and restore life energy.  If used again after charging this ability can release a storm of lightning bolts on all targets!  Stay healthy, as this ability\'s power decreases if the user has sustained any damage.',
    'ability_type' => 'electric',
    'ability_energy' => 8,
    'ability_damage' => 50,
    'ability_recovery2' => 20,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define this ability's attachment token
        $this_attachment_token = 'ability_'.$this_ability->ability_token;
        $this_attachment_info = array(
            'class' => 'ability',
            'attachment_duration' => 2,
            'ability_token' => $this_ability->ability_token,
            'ability_frame' => 0,
            'ability_frame_animate' => array(1, 0),
            'ability_frame_offset' => array('x' => -10, 'y' => -10, 'z' => -20),
            'attachment_destroy' => array(
                'trigger' => 'special',
                'kind' => '',
                'frame' => 'defend',
                'rates' => array(100, 0, 0),
                'success' => array(0, 0, 0, -9999, $this_robot->print_name().'\'s charge was lost&hellip;'),
                'failure' => array(0, 0, 0, -9999, $this_robot->print_name().'\'s charge was lost&hellip;')
                )
            );

        // Check if this ability is already charged
        $is_charged = isset($this_robot->robot_attachments[$this_attachment_token]) ? true : false;

        // If the user is holding a Charge Module, auto-charge the ability
        if ($this_robot->has_item('charge-module')){ $is_charged = true; }

        // If the ability flag was not set, this ability begins charging
        if (!$is_charged){

            // Target this robot's self
            $this_ability->target_options_update(array(
                'frame' => 'defend',
                'success' => array(1, -10, 0, -10, $this_robot->print_name().' charges the '.$this_ability->print_name().'&hellip;')
                ));
            $this_robot->trigger_target($this_robot, $this_ability);

            // Attach this ability attachment to the robot using it
            $this_robot->robot_attachments[$this_attachment_token] = $this_attachment_info;
            $this_robot->update_session();

            // Restore this robot's energy slightly if possible
            if ($this_robot->robot_energy < $this_robot->robot_base_energy){
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'type' => '',
                    'type2' => '',
                    'percent' => true,
                    'rates' => array(100, 0, 0),
                    'success' => array(2, -10, 0, -10, $this_robot->print_name().'\'s energy was restored!'),
                    'failure' => array(2, -10, 0, -10, $this_robot->print_name().'\'s energy was not affected&hellip;')
                    ));
                $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_ability->ability_recovery2 / 100));
                $this_robot->trigger_recovery($this_robot, $this_ability, $energy_recovery_amount);
            }

        }
        // Else if the ability flag was set, the ability is released at the target
        else {

            // Remove this ability attachment to the robot using it
            unset($this_robot->robot_attachments[$this_attachment_token]);
            $this_robot->update_session();

            // Target the opposing robot
            $this_ability->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, -10, 0, -20, $this_robot->print_name().' summons the '.$this_ability->print_name().'!')
                ));
            $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true, 'prevent_stats_text' => true));

            // Inflict damage on the opposing robot
            $num_hits_counter = 0;
            $this_robot->set_frame('throw');
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'type' => $this_ability->ability_type,
                'type2' => '',
                'modifiers' => true,
                'kickback' => array(5, 0, 0),
                'success' => array(3, -5, 10, 10, 'The target was struck by the '. $this_ability->print_name().'\'s lightning!'),
                'failure' => array(2, -5, 10, -1,'The '. $this_ability->print_name().' missed '.$target_robot->print_name().'&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'type' => $this_ability->ability_type,
                'type2' => '',
                'modifiers' => true,
                'frame' => 'taunt',
                'kickback' => array(5, 0, 0),
                'success' => array(3, -5, 10, 10, 'The target absorbed the '.$this_ability->print_name().'\'s lightning!'),
                'failure' => array(2, -5, 10, -1, 'The '.$this_ability->print_name().' had no effect on '.$target_robot->print_name().'&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $trigger_options = array('apply_modifiers' => true, 'apply_position_modifiers' => false, 'apply_stat_modifiers' => true);
            $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
            if ($this_ability->ability_results['this_result'] != 'failure'){ $num_hits_counter++; }

            // Loop through the target's benched robots, inflicting damage to each
            $backup_target_robots_active = $target_player->values['robots_active'];
            foreach ($backup_target_robots_active AS $key => $info){
                if ($info['robot_id'] == $target_robot->robot_id){ continue; }
                $this_robot->set_frame($num_hits_counter % 2 === 0 ? 'defend' : 'taunt');
                $temp_target_robot = rpg_game::get_robot($this_battle, $target_player, $info);
                $this_ability->ability_results_reset();
                $temp_positive_word = rpg_battle::random_positive_word();
                $temp_negative_word = rpg_battle::random_negative_word();
                $temp_frame = ($key % 2 == 0 ? 3 : 2);
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'modifiers' => true,
                    'kickback' => array(5, 0, 0),
                    'success' => array($temp_frame, -5, 10, 10, ($target_player->player_side === 'right' ? $temp_positive_word : $temp_negative_word).' The lightning hit '.($num_hits_counter > 0 ? 'another' : 'a').' robot!'),
                    'failure' => array($temp_frame, -5, 10, -1, 'The attack had no effect on '.$temp_target_robot->print_name().'&hellip;')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'modifiers' => true,
                    'frame' => 'taunt',
                    'kickback' => array(5, 0, 0),
                    'success' => array($temp_frame, -5, 10, 10, ($target_player->player_side === 'right' ? $temp_negative_word : $temp_positive_word).' The lightning was absorbed by the target!'),
                    'failure' => array($temp_frame, -5, 10, -1, 'The attack had no effect on '.$temp_target_robot->print_name().'&hellip;')
                    ));
                $energy_damage_amount = $this_ability->ability_damage;
                $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);
                if ($this_ability->ability_results['this_result'] != 'failure'){ $num_hits_counter++; }
            }

            // Return the user to their base frame
            $this_robot->set_frame('base');

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

        }

        // Return true on success
        return true;

        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update this weapon's power based on remaining life energy
        $current_user_damage = 100 - floor(($this_robot->robot_energy / $this_robot->robot_base_energy) * 100);
        $new_ability_damage = $this_ability->ability_base_damage - $current_user_damage;
        if ($new_ability_damage < 1){ $new_ability_damage = 1; }
        $this_ability->set_damage($new_ability_damage);

        // Return true on success
        return true;

        }
    );
?>