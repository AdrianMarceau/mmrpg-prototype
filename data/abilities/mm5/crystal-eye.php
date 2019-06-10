<?
// CRYSTAL EYE
$ability = array(
    'ability_name' => 'Crystal Eye',
    'ability_token' => 'crystal-eye',
    'ability_game' => 'MM05',
    //'ability_group' => 'MM05/Weapons/040',
    'ability_group' => 'MM05/Weapons/033T2',
    'ability_description' => 'The user fires a large crystal orb at the target that splits in mid-air to form three smaller ones, each able to hit a different target and inflict damage!  Whoever this ability hits, the resulting damage is guaranteed to be super effective!',
    'ability_type' => 'crystal',
    'ability_type2' => 'copy',
    'ability_energy' => 8,
    'ability_damage' => 20,
    'ability_accuracy' => 98,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Define a quick function for calculating ability types
        $get_ability_types = function($target_robot) use($this_ability){
            $ability_types = array($this_ability->ability_type, $this_ability->ability_type2);
            if (!$target_robot->has_weakness($ability_types[0])
                && !empty($target_robot->robot_weaknesses)){
                foreach ($target_robot->robot_weaknesses AS $temp_type){
                    if ($temp_type != $ability_types[0]
                        && !$target_robot->has_affinity($temp_type)
                        && !$target_robot->has_immunity($temp_type)){
                        $ability_types[1] = $temp_type;
                        break;
                        }
                    }
                }
            return $ability_types;
            };

        // Collect a list of benched robots from the target (if there are any)
        $temp_target_benched_robots = rpg_game::find_robots(array(
            'player_id' => $target_player->player_id,
            'robot_position' => 'bench',
            'robot_status' => 'active'
            ));

        // Sort the robots by key if there are any (very important!)
        if (!empty($temp_target_benched_robots)){
            usort($temp_target_benched_robots, function($a, $b){
                if ($a->robot_key < $b->robot_key){ return -1; }
                elseif ($a->robot_key > $b->robot_key){ return 1; }
                else { return 0; }
                });
            }

        // Collect the main, background, and foreground targets (if different, else all active)
        $main_target_robot = $target_robot;
        $background_target_robot = !empty($temp_target_benched_robots) ? array_shift(array_slice($temp_target_benched_robots, 0, 1)) : false;
        $foreground_target_robot = !empty($temp_target_benched_robots) && count($temp_target_benched_robots) > 1 ? array_shift(array_slice($temp_target_benched_robots, -1, 1)) : false;

        // Define this ability's default attachment token and info
        $base_attachment_token = 'ability_'.$this_ability->ability_token;
        $base_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_token,
            'ability_frame' => 2,
            'ability_frame_animate' => array(2),
            'ability_frame_offset' => array('x' => 110, 'y' => -10, 'z' => 10)
            );

        // Target the opposing robot
        $this_ability->reset_image();
        $temp_target_text = $target_player->counters['robots_active'] > 1 ? 'targets\' souls' : 'target\'s soul';
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 60, 0, 10,
                $this_robot->print_name().' summons the '.$this_ability->print_name().'! '.
                '<br /> The '.$this_ability->print_name().' peers into the '.$temp_target_text.'... '
                )
            ));
        $this_robot->trigger_target($main_target_robot, $this_ability, array('prevent_default_text' => true));

        // Split the ability into three pieces and then attach to the user for animating
        if (true){

            // Add the first attachment to the summoning robot
            $main_attachment_token = $base_attachment_token.'_main';
            $main_attachment_info = $base_attachment_info;
            $main_attachment_info['ability_id'] = $this_ability->ability_id.'_main';
            $main_attachment_info['ability_frame_offset']['x'] += 30;
            $this_robot->set_attachment($main_attachment_token, $main_attachment_info);

            // Add the second attachment to the summoning robot
            $background_attachment_token = $base_attachment_token.'_background';
            $background_attachment_info = $base_attachment_info;
            $background_attachment_info['ability_id'] = $this_ability->ability_id.'_background';
            $background_attachment_info['ability_frame_offset']['y'] -= 30;
            $background_attachment_info['ability_frame_offset']['z'] += 30;
            $this_robot->set_attachment($background_attachment_token, $background_attachment_info);

            // Add the third attachment to the summoning robot
            $foreground_attachment_token = $base_attachment_token.'_foreground';
            $foreground_attachment_info = $base_attachment_info;
            $foreground_attachment_info['ability_id'] = $this_ability->ability_id.'_foreground';
            $foreground_attachment_info['ability_frame_offset']['y'] += 15;
            $foreground_attachment_info['ability_frame_offset']['z'] -= 30;
            $this_robot->set_attachment($foreground_attachment_token, $foreground_attachment_info);

        }

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(0, 999, 9999, -9999, $this_robot->print_name().' throws the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($main_target_robot, $this_ability);

        // Update the images for the three attachments based on collected weakness types
        if (true){

            // Collect the ability's relative types for the main target and update the attachment
            $main_ability_types = $get_ability_types($main_target_robot);
            $main_ability_subtype = array_shift(array_slice(array_filter($main_ability_types), -1, 1));
            $main_ability_image = $this_ability->ability_token.'_'.$main_ability_subtype;
            $main_attachment_info['ability_image'] = $main_ability_image;
            $main_attachment_info['ability_frame_offset']['x'] += 90;
            $this_robot->set_attachment($main_attachment_token, $main_attachment_info);

            // Collect the ability's relative types for the background target and update the attachment
            $background_ability_types = $get_ability_types(!empty($background_target_robot) ? $background_target_robot : $main_target_robot);
            $background_ability_subtype = array_shift(array_slice(array_filter($background_ability_types), -1, 1));
            $background_ability_image = $this_ability->ability_token.'_'.$background_ability_subtype;
            $background_attachment_info['ability_image'] = $background_ability_image;
            $background_attachment_info['ability_frame_offset']['x'] += 90;
            $background_attachment_info['ability_frame_offset']['y'] += 1;
            $this_robot->set_attachment($background_attachment_token, $background_attachment_info);

            // Collect the ability's relative types for the foreground target and update the attachment
            $foreground_ability_types = $get_ability_types(!empty($foreground_target_robot) ? $foreground_target_robot : $main_target_robot);
            $foreground_ability_subtype = array_shift(array_slice(array_filter($foreground_ability_types), -1, 1));
            $foreground_ability_image = $this_ability->ability_token.'_'.$foreground_ability_subtype;
            $foreground_attachment_info['ability_image'] = $foreground_ability_image;
            $foreground_attachment_info['ability_frame_offset']['x'] += 90;
            $foreground_attachment_info['ability_frame_offset']['y'] -= 2;
            $this_robot->set_attachment($foreground_attachment_token, $foreground_attachment_info);

        }

        // Print an empty frame to show the updates attachment images
        $this_battle->events_create(false, false, '', '');

        // Remove the main attachment from the summoner (we'll be showing it elsewhere) and move the other two
        if (true){

            // Remove the main ability attachment from the summoner
            $this_robot->unset_attachment($main_attachment_token);

            // Shift the background attachment forward and back slightly
            $background_attachment_info['ability_frame_offset']['x'] += 120;
            $background_attachment_info['ability_frame_offset']['y'] += 1;
            $this_robot->set_attachment($background_attachment_token, $background_attachment_info);

            // Shift the foreground attachment forward and front slightly
            $foreground_attachment_info['ability_frame_offset']['x'] += 120;
            $foreground_attachment_info['ability_frame_offset']['y'] -= 2;
            $this_robot->set_attachment($foreground_attachment_token, $foreground_attachment_info);

        }

        // Inflict damage on the opposing robot
        $this_ability->set_type2($main_ability_types[1]);
        $this_ability->set_image($main_ability_image);
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'type' => $main_ability_types[0],
            'type2' => $main_ability_types[1],
            'kickback' => array(10, 0, 0),
            'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment crashed into the target!'),
            'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'type' => $main_ability_types[0],
            'type2' => $main_ability_types[1],
            'kickback' => array(10, 0, 0),
            'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment was absorbed by the target!'),
            'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $main_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Move the two background and foreground attachments forward (we may remove later)
        if (true){

            // Update the background attachment's x and y offset to move forward
            $background_attachment_info['ability_frame_offset']['x'] += 120;
            $background_attachment_info['ability_frame_offset']['y'] += 1;
            $this_robot->set_attachment($background_attachment_token, $background_attachment_info);

            // Update the foreground attachment's x and y offset to move forward
            $foreground_attachment_info['ability_frame_offset']['x'] += 120;
            $foreground_attachment_info['ability_frame_offset']['y'] -= 2;
            $this_robot->set_attachment($foreground_attachment_token, $foreground_attachment_info);

        }

        // If there's a background robot to strike, do so now
        if (!empty($background_target_robot)){

            // Remove the background attachment from the summoner as we're gonna show it elsewhere
            $this_robot->unset_attachment($background_attachment_token);

            // Inflict damage on the opposing robot
            $this_ability->set_type2($background_ability_types[1]);
            $this_ability->set_image($background_ability_image);
            $this_ability->damage_options_update(array(
                'kind' => 'energy',
                'type' => $background_ability_types[0],
                'type2' => $background_ability_types[1],
                'kickback' => array(10, 0, 0),
                'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment crashed into '.$background_target_robot->print_name().'!'),
                'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                ));
            $this_ability->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'type' => $background_ability_types[0],
                'type2' => $background_ability_types[1],
                'kickback' => array(10, 0, 0),
                'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment was absorbed by '.$background_target_robot->print_name().'!'),
                'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                ));
            $energy_damage_amount = $this_ability->ability_damage;
            $background_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

            // Move the two background and foreground attachments forward (we may remove later)
            if (true){

                // Update the background attachment's x and y offset to move forward
                if ($this_robot->has_attachment($background_attachment_token)){
                    $background_attachment_info['ability_frame_offset']['x'] += 120;
                    $background_attachment_info['ability_frame_offset']['y'] += 1;
                    $this_robot->set_attachment($background_attachment_token, $background_attachment_info);
                }

                // Update the foreground attachment's x and y offset to move forward
                if ($this_robot->has_attachment($foreground_attachment_token)){
                    $foreground_attachment_info['ability_frame_offset']['x'] += 120;
                    $foreground_attachment_info['ability_frame_offset']['y'] -= 2;
                    $this_robot->set_attachment($foreground_attachment_token, $foreground_attachment_info);
                }

            }

            // If there's a foreground robot to strike, do so now
            if (!empty($foreground_target_robot)){

                // Remove the foreground attachment from the summoner as we're gonna show it elsewhere
                $this_robot->unset_attachment($foreground_attachment_token);

                // Inflict damage on the opposing robot
                $this_ability->set_type2($foreground_ability_types[1]);
                $this_ability->set_image($foreground_ability_image);
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'type' => $foreground_ability_types[0],
                    'type2' => $foreground_ability_types[1],
                    'kickback' => array(10, 0, 0),
                    'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment crashed into '.$foreground_target_robot->print_name().'!'),
                    'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'frame' => 'taunt',
                    'type' => $foreground_ability_types[0],
                    'type2' => $foreground_ability_types[1],
                    'kickback' => array(10, 0, 0),
                    'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment was absorbed by '.$foreground_target_robot->print_name().'!'),
                    'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                    ));
                $energy_damage_amount = $this_ability->ability_damage;
                $foreground_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

            } else {

                // Print an empty frame to show the updated attachment images
                $this_battle->events_create(false, false, '', '');

            }

        } else {

            // Print an empty frame to show the updated attachment images
            $this_battle->events_create(false, false, '', '');

        }

        // Remove any leftover attachments from this robot
        $this_robot->unset_attachment($main_attachment_token);
        $this_robot->unset_attachment($background_attachment_token);
        $this_robot->unset_attachment($foreground_attachment_token);

        // Reset the ability image to default, whatever it is
        $this_ability->reset_type();
        $this_ability->reset_type2();
        $this_ability->reset_image();

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

        // Reset the ability image to default, whatever it is
        $this_ability->reset_type();
        $this_ability->reset_type2();
        $this_ability->reset_image();

        // Return true on success
        return true;

        }
    );
?>