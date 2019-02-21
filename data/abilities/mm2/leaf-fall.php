<?
// LEAF FALL
$ability = array(
    'ability_name' => 'Leaf Fall',
    'ability_token' => 'leaf-fall',
    'ability_game' => 'MM02',
    'ability_group' => 'MM02/Weapons/016',
    'ability_image_sheets' => 4,
    'ability_description' => 'The user throws a collection of sharp leaf-like blades into the air that drop randomly on opponents to inflict damage!',
    'ability_type' => 'nature',
    'ability_energy' => 4,
    'ability_damage' => 8,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Count the number of active robots on the target's side of the  field
        $target_robot_ids = array();
        $target_robots_active = $target_player->values['robots_active'];
        $target_robots_active_count = $target_player->counters['robots_active'];
        $get_random_target_robot = function($robot_id = 0) use($this_battle, $target_player, &$target_robot_ids){
            $robot_info = array();
            $active_robot_keys = array_keys($target_player->values['robots_active']);
            shuffle($active_robot_keys);
            foreach ($active_robot_keys AS $key_key => $robot_key){
                $robot_info = $target_player->values['robots_active'][$robot_key];
                if (!empty($robot_id) && $robot_info['robot_id'] !== $robot_id){ continue; }
                $robot_id = $robot_info['robot_id'];
                $random_target_robot = rpg_game::get_robot($this_battle, $target_player, $robot_info);
                if (!in_array($robot_info['robot_id'], $target_robot_ids)){ $target_robot_ids[] = $robot_id; }
                return $random_target_robot;
                }
            };

        // Collect five random targets, with the first always being active (repeats allowed)
        $target_robot_1 = $get_random_target_robot($target_robot->robot_id);
        $target_robot_2 = $get_random_target_robot();
        $target_robot_3 = $get_random_target_robot();
        $target_robot_4 = $get_random_target_robot();
        $target_robot_5 = $get_random_target_robot();

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 10, 140, 10, $this_robot->print_name().' summons a '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_stats_text' => true));

        // Put the user in a throw frame for the duration of the attack
        $this_robot->set_frame('throw');

        // Inflict damage on the first opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(10, 0, 0),
            'success' => array(2, -35, 0, 10, 'The '.$this_ability->print_name().'\'s leaves slice through the target!'),
            'failure' => array(2, -95, 0, -10, 'The '.$this_ability->print_name().'\'s leaves just missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(5, 0, 0),
            'success' => array(2, -35, 0, 10, 'The '.$this_ability->print_name().'\'s leaves were absorbed by the target!'),
            'failure' => array(2, -95, 0, -10, 'The '.$this_ability->print_name().'\'s leaves just missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $trigger_options = array('apply_modifiers' => true, 'apply_position_modifiers' => false);
        $target_robot_1->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);

        // Inflict damage on the second opposing robot if they're not disabled
        if ($target_robot_2->robot_status !== 'disabled'){

            // Define the success/failure text variables
            $success_text = '';
            $failure_text = '';

            // Adjust damage/recovery text based on results
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another leaf hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another leaf missed!'; }

            // Attempt to trigger damage to the target robot again
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(-10, 0, 0),
                'success' => array(3, 35, 0, 10, $success_text),
                'failure' => array(3, 95, 0, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(-5, 0, 0),
                'success' => array(3, 35, 0, 10, $success_text),
                'failure' => array(3, 95, 0, -10, $failure_text)
                ));
            $target_robot_2->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false, $trigger_options);

        }

        // Inflict damage on the third opposing robot if they're not disabled
        if ($target_robot_3->robot_status !== 'disabled'){

            // Adjust damage/recovery text based on results again
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another leaf hit!'; }
            elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A third leaf hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another leaf missed!'; }
            elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A third leaf missed!'; }

            // Attempt to trigger damage to the target robot a third time
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(10, 0, 0),
                'success' => array(2, -35, 0, 10, $success_text),
                'failure' => array(2, -95, 0, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(5, 0, 0),
                'success' => array(2, -35, 0, 10, $success_text),
                'failure' => array(2, -95, 0, -10, $failure_text)
                ));
            $target_robot_3->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        }

        // Inflict damage on the fourth opposing robot if they're not disabled
        if ($target_robot_4->robot_status !== 'disabled'){

            // Adjust damage/recovery text based on results again
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another leaf hit!'; }
            elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A fourth leaf hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another leaf missed!'; }
            elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A fourth leaf missed!'; }

            // Attempt to trigger damage to the target robot a third time
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(-10, 0, 0),
                'success' => array(3, 35, 0, 10, $success_text),
                'failure' => array(3, 95, 0, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(-5, 0, 0),
                'success' => array(3, 35, 0, 10, $success_text),
                'failure' => array(3, 95, 0, -10, $failure_text)
                ));
            $target_robot_4->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        }

        // Inflict damage on the fifth opposing robot if they're not disabled
        if ($target_robot_5->robot_status !== 'disabled'){

            // Adjust damage/recovery text based on results again
            if ($this_ability->ability_results['total_strikes'] == 1){ $success_text = 'Another leaf hit!'; }
            elseif ($this_ability->ability_results['total_strikes'] == 2){ $success_text = 'A fourth leaf hit!'; }
            if ($this_ability->ability_results['total_misses'] == 1){ $failure_text = 'Another leaf missed!'; }
            elseif ($this_ability->ability_results['total_misses'] == 2){ $failure_text = 'A fourth leaf missed!'; }

            // Attempt to trigger damage to the target robot a third time
            $this_ability->ability_results_reset();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'kickback' => array(10, 0, 0),
                'success' => array(2, -35, 0, 10, $success_text),
                'failure' => array(2, -95, 0, -10, $failure_text)
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'kickback' => array(5, 0, 0),
                'success' => array(2, -35, 0, 10, $success_text),
                'failure' => array(2, -95, 0, -10, $failure_text)
                ));
            $target_robot_5->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        }

        // Return the user to their base frame now that we're done
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

        // Return true on success
        return true;

    },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Update the ability image if the user is in their alt image
        $alt_image_triggers = array('wood-man_alt' => 2, 'wood-man_alt2' => 3, 'wood-man_alt9' => 4);
        if (isset($alt_image_triggers[$this_robot->robot_image])){ $ability_image = $this_ability->ability_token.'-'.$alt_image_triggers[$this_robot->robot_image]; }
        else { $ability_image = $this_ability->ability_base_image; }
        $this_ability->set_image($ability_image);

        // Return true on success
        return true;

        }
    );
?>