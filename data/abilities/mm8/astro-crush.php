<?
// ASTRO CRUSH
$ability = array(
    'ability_name' => 'Astro Crush',
    'ability_token' => 'astro-crush',
    'ability_game' => 'MM08',
    //'ability_group' => 'MM08/Weapons/058',
    'ability_group' => 'MM08/Weapons/057T2',
    'ability_image_sheets' => 3,
    'ability_description' => 'The user creates a meteor storm that rains down on the opponent\'s side of the field to crush and damage all target robots!  The user\'s attack stat harshly falls after each use, however.',
    'ability_type' => 'space',
    'ability_type2' => 'impact',
    'ability_energy' => 8,
    'ability_damage' => 40,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define an image index for this ability
        $image_index = array();
        $image_index['blackout'] = $this_ability->ability_token.'-4';
        if ($this_robot->robot_core == 'space'){ $image_index['base'] = $this_ability->ability_token; }
        elseif ($this_robot->robot_core == 'copy'){ $image_index['base'] = $this_ability->ability_token.'-2'; }
        else { $image_index['base'] = $this_ability->ability_token.'-3'; }

        // Define and attach this ability's aura attachment
        $this_aura_token = 'ability_'.$this_ability->ability_token.'_aura';
        $this_aura_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id.'_'.$this_robot->robot_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $image_index['base'],
            'ability_frame' => 0,
            'ability_frame_animate' => array(0),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10),
            'ability_frame_classes' => ' '
            );
        if ($this_robot->robot_token == 'astro-man'){ $this_aura_info['ability_frame_offset']['y'] += 20; }
        $this_aura = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_aura_info);
        $this_aura->update_session();
        $this_robot->robot_attachments[$this_aura_token] = $this_aura_info;
        $this_robot->update_session();

        // Define and attach this ability's blackout attachment
        $this_blackout_token = 'ability_'.$this_ability->ability_token.'_blackout';
        $this_blackout_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id.'_'.$this_robot->robot_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $image_index['blackout'],
            'ability_frame' => 0,
            'ability_frame_animate' => array(0),
            'ability_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -100),
            'ability_frame_classes' => 'sprite_fullscreen ',
            'ability_frame_styles' => 'opacity: 1.0; filter: alpha(opacity=100); '
            );
        $this_blackout = rpg_game::get_ability($this_battle, $this_player, $this_robot, $this_blackout_info);
        $this_blackout->update_session();
        $this_robot->robot_attachments[$this_blackout_token] = $this_blackout_info;
        $this_robot->update_session();

        // Define the details of this ability's meteor attachment
        $this_meteor_token = 'ability_'.$this_ability->ability_token.'_meteor';
        $this_meteor_info = array(
            'class' => 'ability',
            'ability_id' => $this_ability->ability_id.'_'.$this_robot->robot_id,
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $image_index['base'],
            'ability_frame' => 1,
            'ability_frame_animate' => array(1),
            'ability_frame_offset' => array('x' => 450, 'y' => 200, 'z' => 88),
            'ability_frame_classes' => ' ',
            'ability_frame_styles' => 'transform: scaleX(-1); -moz-transform: scaleX(-1); -webkit-transform: scaleX(-1); '
            );

        // Target the opposing robot amd trigger the ability summon
        $this_ability->ability_image = $image_index['base'];
        $this_ability->update_session();
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(9, 0, 0, -999, $this_robot->print_name().' summons an '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability, array('prevent_default_text' => true, 'prevent_stats_text' => true));

        // Loop through the target's benched robots, inflicting damage to each
        $num_hits_counter = 0;
        $this_robot->set_frame('throw');
        $backup_target_robots_active = $target_player->get_robots_active();
        foreach ($backup_target_robots_active AS $key => $robot){

            // If this is somehow not a valid robot, continue
            if ($robot->robot_token == 'robot'){ continue; }

            // Shift the summon's frame back and forth
            $this_robot->set_frame($num_hits_counter % 2 === 0 ? 'defend' : 'taunt');

            // Collect the details of this target
            if ($robot->robot_id == $target_robot->robot_id){ $temp_target_robot = $target_robot; }
            else { $temp_target_robot = $robot; }

            // Remove the meteor attachment from this robot if exists
            if (isset($backup_target_robots_active[$key - 1])){
                $temp_prev_robot = $backup_target_robots_active[$key - 1];
                if (isset($temp_prev_robot->robot_attachments[$this_meteor_token])){
                    unset($temp_prev_robot->robot_attachments[$this_meteor_token]);
                }
            }

            // If there's another target after this, pre-attach a meteor to them
            if (isset($backup_target_robots_active[$key + 1])){
                $temp_next_robot = $backup_target_robots_active[$key + 1];
                $temp_meteor_info = $this_meteor_info;
                $temp_meteor_info['ability_id'] = $this_ability->ability_id.'_'.$temp_next_robot->robot_id;
                $temp_meteor = rpg_game::get_ability($this_battle, $target_player, $temp_next_robot, $temp_meteor_info);
                $temp_meteor->update_session();
                $temp_next_robot->robot_attachments[$this_meteor_token] = $temp_meteor_info;
                $temp_next_robot->update_session();
            }

            // Reset the main ability and inflict damage on this target robot
            $this_ability->ability_results_reset();
            $temp_positive_word = rpg_battle::random_positive_word();
            $temp_negative_word = rpg_battle::random_negative_word();
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'modifiers' => true,
                'kickback' => array(10, 0, 0),
                'success' => array(1, 5, 0, 99, ($target_player->player_side === 'right' ? $temp_positive_word : $temp_negative_word).' A falling meteor slammed into the target!'),
                'failure' => array(3, -5, 0, 99, 'The attack had no effect on '.$temp_target_robot->print_name().'&hellip;')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'modifiers' => true,
                'frame' => 'taunt',
                'kickback' => array(10, 0, 0),
                'success' => array(1, 5, 0, 9, ($target_player->player_side === 'right' ? $temp_negative_word : $temp_positive_word).' A falling meteor was absorbed by the target!'),
                'failure' => array(2, -5, 0, 9, 'The attack had no effect on '.$temp_target_robot->print_name().'&hellip;')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $temp_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);
            $num_hits_counter++;

        }

        // Remove the meteor attachment from this robot if exists
        if (isset($backup_target_robots_active[$key])){
            $temp_last_robot = $backup_target_robots_active[$key];
            if (isset($temp_last_robot->robot_attachments[$this_meteor_token])){
                unset($temp_last_robot->robot_attachments[$this_meteor_token]);
            }
        }

        // Return the user to their base frame
        $this_robot->set_frame('base');

        // Remove this ability attachment from the summoning robot
        unset($this_robot->robot_attachments[$this_aura_token]);
        unset($this_robot->robot_attachments[$this_blackout_token]);
        $this_robot->update_session();

        // Loop through all robots on the target side and remove leftover attachments
        $target_robots_active = $target_player->get_robots();
        foreach ($target_robots_active AS $key => $robot){
            if ($robot->robot_id == $target_robot->robot_id){ $temp_target_robot = $target_robot; }
            else { $temp_target_robot = $robot; }
            if (isset($temp_target_robot->robot_attachments[$this_meteor_token])){
                unset($temp_target_robot->robot_attachments[$this_meteor_token]);
                $temp_target_robot->update_session();
            }
            unset($temp_target_robot);
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
            unset($temp_target_robot);
        }

        // Call the global stat break function with customized options
        rpg_ability::ability_function_stat_break($this_robot, 'attack', 2);

        // Return true on success
        return true;


        },
    'ability_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Change this ability's image based on the holding robot's type
        if ($this_robot->robot_core == 'space'){ $this_ability->ability_image = $this_ability->ability_token; }
        elseif ($this_robot->robot_core == 'copy'){ $this_ability->ability_image = $this_ability->ability_token.'-2'; }
        else { $this_ability->ability_image = $this_ability->ability_token.'-3'; }
        $this_ability->update_session();

        // Return true on success
        return true;

        }
    );
?>