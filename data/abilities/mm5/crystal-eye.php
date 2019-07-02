<?
// CRYSTAL EYE
$ability = array(
    'ability_name' => 'Crystal Eye',
    'ability_token' => 'crystal-eye',
    'ability_game' => 'MM05',
    //'ability_group' => 'MM05/Weapons/040',
    'ability_group' => 'MM05/Weapons/033T2',
    'ability_description' => 'The user summons a large crystal orb at that peers into the target\'s soul to determine and then become its elemental weakness! The large orb is then throw forward, splitting into smaller fragments on impact and dealing damage to the target and up to two benched robots in the positions behind them!',
    'ability_type' => 'crystal',
    'ability_energy' => 8,
    'ability_damage' => 12,
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

        // Collect the main, background, and foreground targets if exist so we can inflict splash damage
        $main_target_robot = $target_robot;
        $background_target_robot = false;
        $foreground_target_robot = false;
        if (!empty($temp_target_benched_robots)){

            // Calculate the "middle" position behind this robot, then the two to the side
            $max_bench_key = ($target_player->values['robots_start_total'] - 1); // ex. 3
            $mid_bench_key = $max_bench_key / 2; // ex. 1.5
            $background_target_key = ceil($mid_bench_key + 1); // ex. 3
            $foreground_target_key = ceil($mid_bench_key - 1); // ex. 1

            // Collect the main, background, and foreground targets (if they exist and are active)
            foreach ($temp_target_benched_robots AS $key => $robot){
                if (!empty($background_target_robot) && !empty($foreground_target_robot)){ break; }
                elseif (empty($background_target_robot) && $robot->robot_key == $background_target_key){ $background_target_robot = $robot; continue; }
                elseif (empty($foreground_target_robot) && $robot->robot_key == $foreground_target_key){ $foreground_target_robot = $robot; continue; }
            }

            /*
            $this_battle->events_create(false, false, 'debug', preg_replace('/\s+/', ' ', "
                \$target_player->values[robots_start_total] = {$target_player->values['robots_start_total']} <br />
                \$max_bench_key = {$max_bench_key} | \$mid_bench_key = {$mid_bench_key} <br />
                \$background_target_key = {$background_target_key} | \$foreground_target_key = {$foreground_target_key} <br />
                \$background_target_robot = {$background_target_robot->robot_token} | \$foreground_target_robot = {$foreground_target_robot->robot_token} <br />
                "));
            */

        }

        // Define this ability's default attachment token and info
        $base_attachment_token = 'ability_'.$this_ability->ability_token;
        $base_attachment_info = array(
            'class' => 'ability',
            'ability_token' => $this_ability->ability_token,
            'ability_image' => $this_ability->ability_token,
            'ability_frame' => 2,
            'ability_frame_animate' => array(2),
            'ability_frame_offset' => array('x' => 400, 'y' => 0, 'z' => 10)
            );

        // Target the opposing robot, show the summoning animation
        $this_ability->reset_image();
        $this_ability->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 0, 70, 10,
                $this_robot->print_name().' summons the '.$this_ability->print_name().'! '.
                '<br /> The glowing orb peers into the target\'s soul... '
                )
            ));
        $this_robot->trigger_target($main_target_robot, $this_ability, array('prevent_default_text' => true));

        // Calculate the new type for this ability given target weaknesses
        $main_ability_types = $get_ability_types($main_target_robot);
        $main_ability_subtype = array_shift(array_slice(array_filter($main_ability_types), -1, 1));
        $main_ability_image = $this_ability->ability_token.'_'.$main_ability_subtype;
        $base_attachment_info['ability_image'] = $main_ability_image;

        // Target the opposing robot, show the type-changing animation
        $has_changed = $this_ability->ability_type2 != $main_ability_types[1] ? true : false;
        $backup_print_name = $this_ability->print_name();
        $this_ability->set_type2($main_ability_types[1]);
        $this_ability->set_image($main_ability_image);
        $this_ability->target_options_update(array(
            'frame' => 'defend',
            'success' => array(0, 0, 60, 10,
                'The '.$backup_print_name.' identified '.$main_target_robot->print_name().'\'s weaknesses! '.
                '<br /> The glowing orb '.($has_changed ? 'took on the' : 'maintained its').' <span class="ability_name ability_type ability_type_'.$main_ability_types[1].'">'.ucfirst($main_ability_types[1]).'</span> type! '
                )
            ));
        $this_robot->trigger_target($main_target_robot, $this_ability, array('prevent_default_text' => true));

        // Target the opposing robot, show the throwing animation
        $this_ability->target_options_update(array(
            'frame' => 'throw',
            'success' => array(1, 110, 0, 10, $this_robot->print_name().' throws the '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($main_target_robot, $this_ability);

        // Attach two extra pieces of orb to the target as we're hitting them (to show it splitting)
        if (true){

            // Add the third attachment to the summoning robot
            $foreground_attachment_token = $base_attachment_token.'_foreground';
            $foreground_attachment_info = $base_attachment_info;
            $foreground_attachment_info['ability_id'] = $this_ability->ability_id.'_foreground';
            $foreground_attachment_info['ability_frame_offset']['y'] += 10;
            $foreground_attachment_info['ability_frame_offset']['z'] -= 30;
            $this_robot->set_attachment($foreground_attachment_token, $foreground_attachment_info);

            // Add the second attachment to the summoning robot
            $background_attachment_token = $base_attachment_token.'_background';
            $background_attachment_info = $base_attachment_info;
            $background_attachment_info['ability_id'] = $this_ability->ability_id.'_background';
            $background_attachment_info['ability_frame_offset']['x'] -= 5;
            $background_attachment_info['ability_frame_offset']['y'] -= 20;
            $background_attachment_info['ability_frame_offset']['z'] += 30;
            $this_robot->set_attachment($background_attachment_token, $background_attachment_info);

        }

        // Inflict damage on the opposing robot using the determined type
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'type' => $main_ability_types[0],
            'type2' => $main_ability_types[1],
            'kickback' => array(10, 0, 0),
            'success' => array(2, 60, 0, 10, 'The '.$this_ability->print_name().' crashed into the target!'),
            'failure' => array(2, 0, 0, -10, 'The '.$this_ability->print_name().' missed its target...')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'type' => $main_ability_types[0],
            'type2' => $main_ability_types[1],
            'kickback' => array(10, 0, 0),
            'success' => array(2, 60, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(2, 0, 0, -10, 'The '.$this_ability->print_name().' missed its target...')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $main_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

        // Remove any leftover attachments from this robot
        $this_robot->unset_attachment($main_attachment_token);
        $this_robot->unset_attachment($background_attachment_token);
        $this_robot->unset_attachment($foreground_attachment_token);

        // Move the two background and foreground attachments forward (we may remove later), then attack again
        if (!empty($foreground_target_robot)
            || !empty($background_target_robot)){

            // If there's a foreground robot to strike, do so now
            if (!empty($foreground_target_robot)){

                // Inflict damage on the opposing robot
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'type' => $main_ability_types[0],
                    'type2' => $main_ability_types[1],
                    'kickback' => array(10, 0, 0),
                    'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment crashed into '.$foreground_target_robot->print_name().'!'),
                    'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'frame' => 'taunt',
                    'type' => $main_ability_types[0],
                    'type2' => $main_ability_types[1],
                    'kickback' => array(10, 0, 0),
                    'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment was absorbed by '.$foreground_target_robot->print_name().'!'),
                    'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                    ));
                $energy_damage_amount = $this_ability->ability_damage;
                $foreground_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

            }

            // If there's a background robot to strike, do so now
            if (!empty($background_target_robot)){

                // Inflict damage on the opposing robot
                $this_ability->damage_options_update(array(
                    'kind' => 'energy',
                    'type' => $main_ability_types[0],
                    'type2' => $main_ability_types[1],
                    'kickback' => array(10, 0, 0),
                    'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment crashed into '.$background_target_robot->print_name().'!'),
                    'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                    ));
                $this_ability->recovery_options_update(array(
                    'kind' => 'energy',
                    'frame' => 'taunt',
                    'type' => $main_ability_types[0],
                    'type2' => $main_ability_types[1],
                    'kickback' => array(10, 0, 0),
                    'success' => array(2, -20, 0, 10, 'A '.$this_ability->print_name().' fragment was absorbed by '.$background_target_robot->print_name().'!'),
                    'failure' => array(2, -40, 0, -10, 'A '.$this_ability->print_name().' fragment missed its target...')
                    ));
                $energy_damage_amount = $this_ability->ability_damage;
                $background_target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount, false);

            }

        }

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