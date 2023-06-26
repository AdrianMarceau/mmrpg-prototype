<?
/**
 * Mega Man RPG Canvas
 * <p>The canvas markup class for the Mega Man RPG Prototype.</p>
 */
class rpg_canvas {

    // Define a function for generating player canvas variables
    public static function player_markup($this_player, $options){

        // Define the variable to hold the console player data
        $this_data = array();
        $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

        // Only proceed if this is a real player
        if ($this_player->player_token != 'player'){

            // Define and calculate the simpler markup and positioning variables for this player
            $this_data['data_type'] = 'player';
            $this_data['player_id'] = $this_player->player_id;
            $this_data['player_frame'] = $this_player->player_frame !== false ? $this_player->player_frame : 'base'; // IMPORTANT
            //$this_data['player_frame'] = str_pad(array_search($this_data['player_frame'], $this_player->player_frame_index), 2, '0', STR_PAD_LEFT);
            $this_data['player_frame_index'] = !empty($this_player->player_frame_index) ? $this_player->player_frame_index : array('base');
            $this_data['player_title'] = $this_player->player_name;
            $this_data['player_token'] = $this_player->player_token;
            $this_data['player_float'] = $this_player->player_side;
            $this_data['player_direction'] = $this_player->player_side == 'left' ? 'right' : 'left';
            $this_data['player_position'] = 'active';
            $this_data['player_size'] = ($this_player->player_image_size * 2);
            $this_data['player_size_base'] = $this_player->player_image_size;
            $this_data['player_sprite_size'] = $this_player->player_image_size;
            $this_data['player_sprite_zoom_size'] = $this_data['player_sprite_size'] * 2;

            $this_data['image_type'] = !empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite';
            $this_data['image_token'] = !empty($this_player->player_image) ? $this_player->player_image : $this_player->player_token;

            $this_data['player_image'] = 'images/players/'.$this_data['image_token'].'/sprite_'.$this_data['player_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;

            $player_frame_index = explode('/', MMRPG_SETTINGS_PLAYER_FRAMEINDEX);
            $player_frame_index_size = count($player_frame_index);

            // Calculate the canvas offset variables for this player
            //error_log(PHP_EOL.'player:'.$this_data['player_token'].' needs canvas_markup_offset()');
            $temp_data = $this_player->battle->canvas_markup_offset(0, 'active', $this_data['player_size'], $this_player->counters['robots_total']);
            $this_data['player_scale'] = $temp_data['canvas_scale'];
            //$this_data['canvas_offset_x'] = ($this_data['player_scale'] * 30) + $temp_data['canvas_offset_x'] + round($this_player->player_frame_offset['x'] * $temp_data['canvas_scale']);
            //$this_data['canvas_offset_y'] = ($this_data['player_scale'] * 10) + $temp_data['canvas_offset_y'] + round($this_player->player_frame_offset['y'] * $temp_data['canvas_scale']);
            $this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'] + round($this_player->player_frame_offset['x'] * $temp_data['canvas_scale']);
            $this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'] + round($this_player->player_frame_offset['y'] * $temp_data['canvas_scale']);
            $this_data['canvas_offset_z'] = -1 + $temp_data['canvas_offset_z'] + round($this_player->player_frame_offset['z'] * $temp_data['canvas_scale']);

            // Shift this player ever-so-slightly out of the way of their robot partner
            $this_data['canvas_offset_x'] += ($this_data['canvas_offset_x'] * 0.20);
            $this_data['canvas_offset_y'] += ($this_data['canvas_offset_y'] * 0.10);

            $this_data['player_sprite_size'] = ceil($this_data['player_scale'] * $this_data['player_sprite_zoom_size']);
            $this_data['player_sprite_width'] = ceil($this_data['player_scale'] * $this_data['player_sprite_zoom_size']);
            $this_data['player_sprite_height'] = ceil($this_data['player_scale'] * $this_data['player_sprite_zoom_size']);
            $this_data['player_image_width'] = ceil($this_data['player_scale'] * ($this_data['player_sprite_zoom_size'] * $player_frame_index_size));
            $this_data['player_image_height'] = ceil($this_data['player_scale'] * $this_data['player_sprite_zoom_size']);
            //$this_data['canvas_offset_z'] = 4900;
            //$this_data['canvas_offset_x'] = 200;
            //$this_data['canvas_offset_y'] = 60;

            $sprite_xsize = $this_data['player_sprite_size'].'x'.$this_data['player_sprite_size'];
            $this_data['player_markup_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_'.$sprite_xsize.' sprite_'.$sprite_xsize.'_'.$this_data['player_frame'].' ';

            if ($this_player->player_image_size !== $this_data['player_sprite_size']){
                $this_data['player_markup_class'] .= 'scaled ';
            }

            $frame_position = array_search($this_data['player_frame'], $this_data['player_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $frame_background_offset = -1 * ceil(($this_data['player_sprite_size'] * $frame_position));
            $this_data['player_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
            $this_data['player_markup_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['player_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
            $this_data['player_markup_style'] .= 'background-image: url('.$this_data['player_image'].'); width: '.$this_data['player_sprite_size'].'px; height: '.$this_data['player_sprite_size'].'px; background-size: '.$this_data['player_image_width'].'px '.$this_data['player_image_height'].'px; ';

            $camera_action_styles = '';
            $camera_has_action = self::has_camera_action(array(
                'token' => $this_player->player_token,
                'side' => $this_player->player_side,
                'position' => 'active',
                'key' => 0,
                ), $options, $camera_action_styles);
            if (!empty($camera_action_styles)){
                $this_data['player_markup_style'] .= $camera_action_styles;
            }

            // Generate the final markup for the canvas player
            ob_start();

                // Display this PLAYER SPRITE for the battle canvas
                echo '<div '.
                    'data-playerid="'.$this_data['player_id'].'" '.
                    'class="'.$this_data['player_markup_class'].'" '.
                    'style="'.$this_data['player_markup_style'].'" '.
                    'data-type="'.$this_data['data_type'].'" '.
                    'data-size="'.$this_data['player_sprite_size'].'" '.
                    'data-direction="'.$this_data['player_direction'].'" '.
                    'data-frame="'.$this_data['player_frame'].'" '.
                    'data-position="'.$this_data['player_position'].'" '.
                    '></div>';

                // Display the player's SHADOW SPRITE for the battle canvas
                echo self::generate_sprite_shadow_markup('player', $this_data);

            // Collect the generated player markup
            $this_data['player_markup'] = trim(ob_get_clean());

        } else {

            // Define empty player markup
            $this_data['player_markup'] = '';

        }

        // Return the player canvas data
        return $this_data;

    }

    // Define a function for generating robot canvas variables
    public static function robot_markup($this_robot, $options, $player_data){

        // Define the variable to hold the console robot data
        $this_data = array();
        $this_target_options = !empty($options['this_ability']->target_options) ? $options['this_ability']->target_options : array();
        $this_damage_options = !empty($options['this_ability']->damage_options) ? $options['this_ability']->damage_options : array();
        $this_recovery_options = !empty($options['this_ability']->recovery_options) ? $options['this_ability']->recovery_options : array();
        $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

        // DEBUG DEBUG DEBUG DEBUG
        /*
        if ($this_robot->robot_class === 'master'){
            $this_robot->robot_image = 'mega-man';
            $this_robot->robot_image_size = 40;
            if ($this_robot->robot_token !== 'mega-man'){
                if (empty($this_robot->robot_core)){ $this_robot->robot_image .= '_copy'; }
                else { $this_robot->robot_image .= '_'.$this_robot->robot_core; }
            }
        }
        */

        // Define and calculate the simpler markup and positioning variables for this robot
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'robot';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['robot_id'] = $this_robot->robot_id;
        $this_data['robot_token'] = $this_robot->robot_token;
        $this_data['robot_id_token'] = $this_robot->robot_id.'_'.$this_robot->robot_token;
        $this_data['robot_key'] = !empty($this_robot->robot_key) ? $this_robot->robot_key : 0;
        $this_data['robot_core'] = !empty($this_robot->robot_core) ? $this_robot->robot_core : 'none';
        $this_data['robot_class'] = !empty($this_robot->robot_class) ? $this_robot->robot_class : 'master';
        $this_data['robot_stance'] = !empty($this_robot->robot_stance) ? $this_robot->robot_stance : 'base';
        $this_data['robot_frame'] = !empty($this_robot->robot_frame) ? $this_robot->robot_frame : 'base';
        $this_data['robot_frame_index'] = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        $this_data['robot_frame_classes'] = !empty($this_robot->robot_frame_classes) ? $this_robot->robot_frame_classes : '';
        $this_data['robot_frame_styles'] = !empty($this_robot->robot_frame_styles) ? $this_robot->robot_frame_styles : '';
        $this_data['robot_detail_styles'] = !empty($this_robot->robot_detail_styles) ? $this_robot->robot_detail_styles : '';
        $this_data['robot_image'] = $this_robot->robot_image;
        $this_data['robot_image_overlay'] = !empty($this_robot->robot_image_overlay) ? $this_robot->robot_image_overlay : array(0);
        $this_data['robot_float'] = $this_robot->player->player_side;
        $this_data['robot_direction'] = $this_robot->player->player_side == 'left' ? 'right' : 'left';
        $this_data['robot_status'] = $this_robot->robot_status;
        $this_data['robot_position'] = !empty($this_robot->robot_position) ? $this_robot->robot_position : 'bench';
        $this_data['robot_action'] = 'scan_'.$this_robot->robot_id.'_'.$this_robot->robot_token;
        $this_data['robot_size'] = ($this_robot->robot_image_size * 2);
        $this_data['robot_size_base'] = $this_robot->robot_image_size;
        $this_data['robot_size_path'] = ($this_robot->robot_image_size * 2).'x'.($this_robot->robot_image_size * 2);
        //$this_data['robot_scale'] = $this_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_data['robot_key']) / 8) * 0.5);
        //$this_data['robot_title'] = $this_robot->robot_number.' '.$this_robot->robot_name.' (Lv. '.$this_robot->robot_level.')';
        $this_data['robot_title'] = $this_robot->robot_name.' (Lv. '.$this_robot->robot_level.')';
        $this_data['robot_title'] .= ' <br />'.(!empty($this_data['robot_core']) && $this_data['robot_core'] != 'none' ? ucfirst($this_data['robot_core']).' Core' : 'Neutral Core');
        $this_data['robot_title'] .= ' | '.ucfirst($this_data['robot_position']).' Position';

        // Calculate the canvas offset variables for this robot
        //error_log(PHP_EOL.'robot:'.$this_data['robot_token'].' needs canvas_markup_offset()');
        $robot_offset_data = $this_robot->battle->canvas_markup_offset($this_data['robot_key'], $this_data['robot_position'], $this_data['robot_size'], $this_robot->player->counters['robots_total']);
        $this_data['canvas_offset_x'] = $robot_offset_data['canvas_offset_x'] + round($this_robot->robot_frame_offset['x'] * $robot_offset_data['canvas_scale']);
        $this_data['canvas_offset_y'] = $robot_offset_data['canvas_offset_y'] + round($this_robot->robot_frame_offset['y'] * $robot_offset_data['canvas_scale']);
        $this_data['canvas_offset_z'] = $robot_offset_data['canvas_offset_z'] + round($this_robot->robot_frame_offset['z'] * $robot_offset_data['canvas_scale']);
        $this_data['canvas_base_offset_x'] = $this_data['canvas_offset_x'];
        $this_data['canvas_base_offset_y'] = $this_data['canvas_offset_y'];
        $this_data['canvas_base_offset_z'] = $this_data['canvas_offset_z'];
        $this_data['canvas_offset_rotate'] = 0;
        $this_data['robot_scale'] = $robot_offset_data['canvas_scale'];

        // Create a backup of the x and y-offset before we continue
        $backup_canvas_offset_x = $robot_offset_data['canvas_offset_x'];
        $backup_canvas_offset_y = $robot_offset_data['canvas_offset_y'];

        // Calculate the zoom properties for the robot sprite
        $zoom_size = $this_robot->robot_image_size * 2;
        $frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        $this_data['robot_sprite_size'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_sprite_width'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_sprite_height'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_file_width'] = ceil($this_data['robot_scale'] * $zoom_size * count($frame_index));
        $this_data['robot_file_height'] = ceil($this_data['robot_scale'] * $zoom_size);


        /* DEBUG
        $this_data['robot_title'] = $this_robot->robot_name
            .' | ID '.str_pad($this_robot->robot_id, 3, '0', STR_PAD_LEFT).''
            //.' | '.strtoupper($this_robot->robot_position)
            .' | '.$this_robot->robot_energy.' LE'
            .' | '.$this_robot->robot_attack.' AT'
            .' | '.$this_robot->robot_defense.' DF'
            .' | '.$this_robot->robot_speed.' SP';
            */

        // If this robot is on the bench and inactive, override default sprite frames
        if ($this_data['robot_position'] == 'bench'
            && $this_data['robot_status'] != 'disabled'){
            // Only override sprite if this robot is in the default/base frame
            if ($this_data['robot_frame'] == 'base'){
                // Define a randomly generated integer value
                $random_int = mt_rand(1, 100);
                // Use the random int to decide which frame to show this benched robot in
                if ($random_int >= 90){ $this_data['robot_frame'] = 'base2'; }
                elseif ($random_int >= 80){ $this_data['robot_frame'] = 'taunt'; }
                elseif ($random_int >= 70){ $this_data['robot_frame'] = 'defend'; }
                else { $this_data['robot_frame'] = 'base'; }
                //error_log('robot = '.$this_data['robot_token'].' | int = '.$random_int.' | frame = '.$this_data['robot_frame']);
            } else {
                //error_log('robot = '.$this_data['robot_token'].' | stuck in frame = '.$this_data['robot_frame']);
            }
        }

        // If this robot is being damaged of is defending
        if ($this_data['robot_status'] == 'disabled' && $this_data['robot_frame'] != 'damage'){
            //$this_data['robot_frame'] = 'defeat';
            $this_data['canvas_offset_x'] -= 10;
        } elseif ($this_data['robot_frame'] == 'damage' || $this_data['robot_stance'] == 'defend'){
            if (!empty($this_results['total_strikes']) || (!empty($this_results['this_result']) && $this_results['this_result'] == 'success')){ //checkpoint
                if ($this_results['trigger_kind'] == 'damage' && !empty($this_damage_options['damage_kickback']['x'])){
                    $this_data['canvas_offset_rotate'] += ceil(($this_damage_options['damage_kickback']['x'] / 100) * 45);
                    $this_data['canvas_offset_x'] -= ceil($this_damage_options['damage_kickback']['x'] * 1.5); //isset($this_results['total_strikes']) ? $this_damage_options['damage_kickback']['x'] + ($this_damage_options['damage_kickback']['x'] * $this_results['total_strikes']) : $this_damage_options['damage_kickback']['x'];
                }
                elseif ($this_results['trigger_kind'] == 'recovery' && !empty($this_recovery_options['recovery_kickback']['x'])){
                    $this_data['canvas_offset_rotate'] += ceil(($this_recovery_options['recovery_kickback']['x'] / 100) * 50);
                    $this_data['canvas_offset_x'] -= ceil($this_recovery_options['recovery_kickback']['x'] * 1.5); //isset($this_results['total_strikes']) ? $this_recovery_options['recovery_kickback']['x'] + ($this_recovery_options['recovery_kickback']['x'] * $this_results['total_strikes']) : $this_recovery_options['recovery_kickback']['x'];
                }
                $this_data['canvas_offset_rotate'] += ceil($this_results['total_strikes'] * 10);
            }
            if (!empty($this_results['this_result']) && $this_results['this_result'] == 'success'){
                if ($this_results['trigger_kind'] == 'damage' && !empty($this_damage_options['damage_kickback']['y'])){
                    $this_data['canvas_offset_y'] += $this_damage_options['damage_kickback']['y']; //isset($this_results['total_strikes']) ? ($this_damage_options['damage_kickback']['y'] * $this_results['total_strikes']) : $this_damage_options['damage_kickback']['y'];
                }
                elseif ($this_results['trigger_kind'] == 'recovery' && !empty($this_recovery_options['recovery_kickback']['y'])){
                    $this_data['canvas_offset_y'] += $this_recovery_options['recovery_kickback']['y']; //isset($this_results['total_strikes']) ? ($this_recovery_options['recovery_kickback']['y'] * $this_results['total_strikes']) : $this_recovery_options['recovery_kickback']['y'];
                }
            }
        }

        // Either way, apply target offsets if they exist
        if (isset($options['this_ability_target']) && $options['this_ability_target'] != $this_data['robot_id_token']){
            if (!empty($this_target_options['target_kickback']['x'])
                || !empty($this_target_options['target_kickback']['y'])
                || !empty($this_target_options['target_kickback']['z'])){
                $this_data['canvas_offset_x'] += $this_target_options['target_kickback']['x'];
                $this_data['canvas_offset_y'] += $this_target_options['target_kickback']['y'];
                $this_data['canvas_offset_z'] += $this_target_options['target_kickback']['z'];
            }
        }

        // Calculate the energy bar amount and display properties
        $this_data['energy_fraction'] = $this_robot->robot_energy.' / '.$this_robot->robot_base_energy;
        $this_data['energy_percent'] = ceil(($this_robot->robot_energy / $this_robot->robot_base_energy) * 100);
        if ($this_data['energy_percent'] == 100 && $this_robot->robot_energy < $this_robot->robot_base_energy){ $this_data['energy_percent'] = 99; }
        // Calculate the energy bar positioning variables based on float
        if ($this_data['robot_float'] == 'left'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -3;  }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -111 + floor(111 * ($this_data['energy_percent'] / 100)) - 2;  }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -111; }
            else { $this_data['energy_x_position'] = -112; }
            if ($this_data['energy_percent'] > 0 && $this_data['energy_percent'] < 100 && $this_data['energy_x_position'] % 2 == 0){ $this_data['energy_x_position']--; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; $this_data['energy_tooltip_type'] = 'nature'; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -12; $this_data['energy_tooltip_type'] = 'electric'; }
            else { $this_data['energy_y_position'] = -24; $this_data['energy_tooltip_type'] = 'flame'; }
        }
        elseif ($this_data['robot_float'] == 'right'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -112; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(111 * ($this_data['energy_percent'] / 100)) + 2; }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
            else { $this_data['energy_x_position'] = -2; }
            if ($this_data['energy_percent'] > 0 && $this_data['energy_percent'] < 100 && $this_data['energy_x_position'] % 2 != 0){ $this_data['energy_x_position']--; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = -36; $this_data['energy_tooltip_type'] = 'nature'; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -48; $this_data['energy_tooltip_type'] = 'electric'; }
            else { $this_data['energy_y_position'] = -60; $this_data['energy_tooltip_type'] = 'flame'; }
        }

        // Calculate the weapons bar amount and display properties for both robots
        if (true){
            // Define the fraction and percent text for the weapons
            $this_data['weapons_fraction'] = $this_robot->robot_weapons.' / '.$this_robot->robot_base_weapons;
            $this_data['weapons_percent'] = floor(($this_robot->robot_weapons / $this_robot->robot_base_weapons) * 100);
            $this_data['weapons_percent_used'] = 100 - $this_data['weapons_percent'];
            // Calculate the energy bar positioning variables based on float
            if ($this_data['robot_float'] == 'left'){
                // Define the x and y position of the weapons bar background
                if ($this_data['weapons_percent'] == 100){ $this_data['weapons_x_position'] = 0; }
                elseif ($this_data['weapons_percent'] > 1){ $this_data['weapons_x_position'] = 0 - ceil(60 * ($this_data['weapons_percent_used'] / 100));  }
                elseif ($this_data['weapons_percent'] == 1){ $this_data['weapons_x_position'] = -54; }
                else { $this_data['weapons_x_position'] = -60; }
                //if ($this_data['weapons_percent'] > 0 && $this_data['weapons_percent'] < 100 && $this_data['weapons_x_position'] % 2 != 0){ $this_data['weapons_x_position']++; }
                $this_data['weapons_y_position'] = 0;
            }
            elseif ($this_data['robot_float'] == 'right'){
                // Define the x and y position of the weapons bar background
                if ($this_data['weapons_percent'] == 100){ $this_data['weapons_x_position'] = -61; }
                elseif ($this_data['weapons_percent'] > 1){ $this_data['weapons_x_position'] = -61 + ceil(60 * ($this_data['weapons_percent_used'] / 100));  }
                elseif ($this_data['weapons_percent'] == 1){ $this_data['weapons_x_position'] = -7; }
                else { $this_data['weapons_x_position'] = -1; }
                //if ($this_data['weapons_percent'] > 0 && $this_data['weapons_percent'] < 100 && $this_data['weapons_x_position'] % 2 != 0){ $this_data['weapons_x_position']++; }
                $this_data['weapons_y_position'] = -6;
            }

        }


        // Calculate the experience bar amount and display properties if a player robot
        if ($this_data['robot_float'] == 'left'){
            // Define the fraction and percent text for the experience
            if ($this_robot->robot_level < 100){
                $this_data['experience_fraction'] = $this_robot->robot_experience.' / 1000';
                $this_data['experience_percent'] = floor(($this_robot->robot_experience / 1000) * 100);
                $this_data['experience_percent_remaining'] = 100 - $this_data['experience_percent'];
            } else {
                $this_data['experience_fraction'] = '&#8734;';
                $this_data['experience_percent'] = 100;
                $this_data['experience_percent_remaining'] = 0;
            }
            // Define the x and y position of the experience bar background
            if ($this_data['experience_percent'] == 100){ $this_data['experience_x_position'] = 0; }
            elseif ($this_data['experience_percent'] > 1){ $this_data['experience_x_position'] = 0 - ceil(60 * ($this_data['experience_percent_remaining'] / 100));  }
            elseif ($this_data['experience_percent'] == 1){ $this_data['experience_x_position'] = -54; }
            else { $this_data['experience_x_position'] = -60; }
            if ($this_data['experience_percent'] > 0 && $this_data['experience_percent'] < 100 && $this_data['experience_x_position'] % 2 != 0){ $this_data['experience_x_position']++; }
            $this_data['experience_y_position'] = 0;
        }



        // Generate the final markup for the canvas robot
        ob_start();

            // Precalculate this robot's stat for later comparrison
            $index_info = rpg_robot::get_index_info($this_robot->robot_token);
            $reward_info = mmrpg_prototype_robot_rewards($this_robot->player->player_token, $this_robot->robot_token);
            $this_stats = rpg_robot::calculate_stat_values($this_robot->robot_level, $index_info, $reward_info, $this_robot->robot_core, $this_robot->player->player_starforce);

            // If a Gemini Clone is present, and is attacking, "steal" the robot frame for later use
            $this_robot_frame = $this_data['robot_frame'];
            $this_robot_offset_x = $this_data['canvas_offset_x'];
            $this_robot_offset_y = $this_data['canvas_offset_y'];
            $gemini_clone_frame = false;
            $gemini_clone_offset_x = false;
            $gemini_clone_offset_y = false;
            if (isset($this_robot->robot_attachments['ability_gemini-clone'])){
                $gemini_clone_frame = empty($this_robot->flags['robot_is_using_ability']) ? $this_data['robot_frame'] : 'defend';
                $gemini_clone_offset_x = empty($this_robot->flags['robot_is_using_ability']) ? $this_data['canvas_offset_x'] : $backup_canvas_offset_x;
                $gemini_clone_offset_y = empty($this_robot->flags['robot_is_using_ability']) ? $this_data['canvas_offset_y'] : $backup_canvas_offset_y;
                if (!empty($this_robot->flags['gemini-clone_is_using_ability'])){
                    $this_robot_frame = $gemini_clone_frame != 'defend' ? 'defend' : 'base';
                    $this_robot_offset_x = $backup_canvas_offset_x;
                    $this_robot_offset_y = $backup_canvas_offset_y;
                }
            }

            // Define the rest of the display variables
            $this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size_path'].'.png?'.MMRPG_CONFIG_CACHE_DATE;

            $this_data['robot_markup_class'] = 'sprite ';
            $this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].' sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].'_'.$this_robot_frame.' ';
            $this_data['robot_markup_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
            $frame_position = is_numeric($this_robot_frame) ? (int)($this_robot_frame) : array_search($this_robot_frame, $this_data['robot_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $this_data['robot_markup_class'] .= $this_data['robot_frame_classes'];

            if ($this_robot->robot_image_size !== $this_data['robot_sprite_size']){
                $this_data['robot_markup_class'] .= 'scaled ';
            }

            // Put everything together to generate this robot sprite's style attribute
            $background_frame_offset = -1 * ceil(($this_data['robot_size'] * $frame_position));
            $background_image_path = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size_path'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['robot_markup_style'] = '';
            $this_data['robot_markup_style'] .= 'width: '.$this_data['robot_size'].'px; height: '.$this_data['robot_size'].'px; ';
            $this_data['robot_markup_style'] .= 'background-image: url('.$background_image_path.'); ';
            $this_data['robot_markup_style'] .= 'background-position: '.(!empty($background_frame_offset) ? $background_frame_offset.'px' : '0').' 0; ';
            $this_data['robot_markup_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; ';
            $this_data['robot_markup_style'] .= $this_data['robot_float'].': '.$this_robot_offset_x.'px; ';
            $this_data['robot_markup_style'] .= 'bottom: '.$this_robot_offset_y.'px; ';
            $this_data['robot_markup_style'] .= 'filter: brightness('.$robot_offset_data['canvas_focus'].'); ';
            $this_data['robot_markup_style'] .= $this_data['robot_frame_styles'];
            if ($robot_offset_data['canvas_scale'] !== 1){
                $this_data['robot_markup_style'] .= 'transform-origin: bottom center; ';
                self::update_or_append_css_transform($this_data['robot_markup_style'], 'scale('.$robot_offset_data['canvas_scale'].')');
            }
            if ($this_robot_frame == 'damage'){
                $temp_rotate_amount = $this_data['canvas_offset_rotate'];
                if ($this_data['robot_direction'] == 'right'){ $temp_rotate_amount = $temp_rotate_amount * -1; }
                self::update_or_append_css_transform($this_data['robot_markup_style'], 'rotate('.$temp_rotate_amount.'deg)');
            }

            // Check if this robot has any camera action and collect the styles if so
            $camera_action_styles = '';
            $camera_has_action = self::has_camera_action(array(
                'token' => $this_robot->robot_token,
                'side' => $this_robot->player->player_side,
                'position' => $this_robot->robot_position,
                'key' => $this_robot->robot_key
                ), $options, $camera_action_styles);
            if (!empty($camera_action_styles)){
                $this_data['robot_markup_style'] .= $camera_action_styles;
            }

            $this_data['energy_class'] = 'energy';
            $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';
            $this_data['weapons_class'] = 'weapons';
            $this_data['weapons_style'] = 'background-position: '.$this_data['weapons_x_position'].'px '.$this_data['weapons_y_position'].'px;';

            // Check if this robot's energy has been maxed out
            $temp_energy_maxed = $this_stats['energy']['current'] >= $this_stats['energy']['max'] ? true : false;

            // Generate this robot's title details and energy + weapon bars
            if ($this_data['robot_float'] == 'left'){

                $this_data['experience_class'] = 'experience';
                $this_data['experience_style'] = 'background-position: '.$this_data['experience_x_position'].'px '.$this_data['experience_y_position'].'px;';

                //$this_data['energy_title'] = $this_data['energy_fraction'].' LE | '.$this_data['energy_percent'].'%'.($temp_energy_maxed ? ' | &#9733;' : '');
                $this_data['energy_title'] = $this_data['energy_fraction'].' LE | '.$this_data['energy_percent'].'%';
                $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE';

                $this_data['weapons_title'] = $this_data['weapons_fraction'].' WE | '.$this_data['weapons_percent'].'%';
                $this_data['robot_title'] .= ' | '.$this_data['weapons_fraction'].' WE';

                if ($this_data['robot_class'] == 'master'){
                    $this_data['experience_title'] = $this_data['experience_fraction'].' EXP | '.$this_data['experience_percent'].'%';
                    $this_data['robot_title'] .= ' | '.$this_data['experience_fraction'].' EXP';
                } elseif ($this_data['robot_class'] == 'mecha'){
                    $temp_generation = '1st';
                    if (preg_match('/-2$/', $this_data['robot_token'])){ $temp_generation = '2nd'; }
                    elseif (preg_match('/-3$/', $this_data['robot_token'])){ $temp_generation = '3rd'; }
                    $this_data['experience_title'] = $temp_generation.' Gen';
                    $this_data['robot_title'] .= ' | '.$temp_generation.' Gen';
                }

                $this_data['robot_title'] .= ' <br />'.$this_robot->robot_attack.' / '.$this_robot->robot_base_attack.' AT';
                $this_data['robot_title'] .= ' | '.$this_robot->robot_defense.' / '.$this_robot->robot_base_defense.' DF';
                $this_data['robot_title'] .= ' | '.$this_robot->robot_speed.' / '.$this_robot->robot_base_speed.' SP';

            }
            elseif ($this_data['robot_float'] == 'right'){

                //$this_data['energy_title'] = ($temp_energy_maxed ? '&#9733; | ' : '').$this_data['energy_percent'].'% | '.$this_data['energy_fraction'].' LE';
                $this_data['energy_title'] = $this_data['energy_percent'].'% | '.$this_data['energy_fraction'].' LE';
                $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE';

                $this_data['weapons_title'] = $this_data['weapons_percent'].'% | '.$this_data['weapons_fraction'].' WE';
                $this_data['robot_title'] .= ' | '.$this_data['weapons_fraction'].' WE';

                $this_data['robot_title'] .= ' <br />'.$this_robot->robot_attack.' / '.$this_robot->robot_base_attack.' AT';
                $this_data['robot_title'] .= ' | '.$this_robot->robot_defense.' / '.$this_robot->robot_base_defense.' DF';
                $this_data['robot_title'] .= ' | '.$this_robot->robot_speed.' / '.$this_robot->robot_base_speed.' SP';

            }

            // Collect the tooltip details and parse them into appropriate formats
            $this_data['robot_title_plain'] = strip_tags(str_replace('<br />', '&#10;', $this_data['robot_title']));
            $this_data['robot_title_tooltip'] = htmlentities($this_data['robot_title'], ENT_QUOTES, 'UTF-8');

            // Display this ROBOT SPRITE for the battle canvas
            echo '<div '.
                'data-robotid="'.$this_data['robot_id'].'" '.
                'class="'.$this_data['robot_markup_class'].'" '.
                'style="'.$this_data['robot_markup_style'].'" '.
                'data-key="'.$this_data['robot_key'].'" '.
                'data-type="'.$this_data['data_type'].'" '.
                'data-size="'.$this_data['robot_size'].'" '.
                'data-direction="'.$this_data['robot_direction'].'" '.
                'data-frame="'.$this_data['robot_frame'].'" '.
                'data-position="'.$this_data['robot_position'].'" '.
                'data-status="'.$this_data['robot_status'].'" '.
                'data-scale="'.$this_data['robot_scale'].'" '.
                '></div>';

            // If this robot has any overlays, display them too (like for Mega Man and Copy Core robots when they pallet-swap)
            if (!empty($this_data['robot_image_overlay'])){
                foreach ($this_data['robot_image_overlay'] AS $key => $overlay_token){
                    if (empty($overlay_token)){ continue; }
                    $overlay_offset_z = $this_data['canvas_offset_z'] + 2;
                    $overlay_styles = ' z-index: '.$overlay_offset_z.'; ';
                    echo '<div '.
                        'data-overlayid="'.$this_data['robot_id'].'" '.
                        'class="'.str_replace($this_data['robot_token'], $overlay_token, $this_data['robot_markup_class']).'" '.
                        'style="'.str_replace('robots/'.$this_data['robot_image'], 'robots/'.$overlay_token, $this_data['robot_markup_style']).$overlay_styles.'" '.
                        'data-key="'.$this_data['robot_key'].'" '.
                        'data-type="'.$this_data['data_type'].'_overlay'.'" '.
                        'data-size="'.$this_data['robot_sprite_size'].'" '.
                        'data-direction="'.$this_data['robot_direction'].'" '.
                        'data-frame="'.$this_data['robot_frame'].'" '.
                        'data-position="'.$this_data['robot_position'].'" '.
                        'data-status="'.$this_data['robot_status'].'" '.
                        'data-scale="'.$this_data['robot_scale'].'" '.
                        '></div>';
                }
            }

            // Display the robot's SHADOW SPRITE for the battle canvas
            echo self::generate_sprite_shadow_markup('robot', $this_data);

            // If this robot has a Gemini Clone, display a copy of its sprite
            if (isset($this_robot->robot_attachments['ability_gemini-clone'])){

                // Generate this clone sprite's class and style independently from the main one
                $temp_clone_class = 'sprite ';
                $temp_clone_class .= 'sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].' sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].'_'.$gemini_clone_frame.' ';
                $temp_clone_class .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
                $frame_position = is_numeric($gemini_clone_frame) ? (int)($gemini_clone_frame) : array_search($gemini_clone_frame, $this_data['robot_frame_index']);
                if ($frame_position === false){ $frame_position = 0; }
                $temp_clone_class .= $this_data['robot_frame_classes'];
                $frame_background_offset = -1 * ceil(($this_data['robot_sprite_size'] * $frame_position));

                if ($this_robot->robot_image_size !== $this_data['robot_sprite_size']){
                    $temp_clone_class .= 'scaled ';
                }

                $temp_clone_style = 'background-position: '.(!empty($frame_background_offset) ? $frame_background_offset.'px' : '0').' 0; ';
                $temp_clone_style .= 'z-index: '.($this_data['canvas_offset_z'] + 1).'; '.$this_data['robot_float'].': '.($gemini_clone_offset_x - ceil($this_data['robot_scale'] * (40 + ($this_robot->robot_image_size > 40 ? 10 : 0)))).'px; bottom: '.($gemini_clone_offset_y - 2).'px; ';
                if ($gemini_clone_frame == 'damage'){
                    $temp_rotate_amount = $this_data['canvas_offset_rotate'];
                    if ($this_data['robot_direction'] == 'right'){ $temp_rotate_amount = $temp_rotate_amount * -1; }
                    $temp_clone_style .= 'transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); ';
                }
                $temp_clone_style .= 'background-image: url('.$this_data['robot_file'].'); width: '.$this_data['robot_sprite_size'].'px; height: '.$this_data['robot_sprite_size'].'px; background-size: '.$this_data['robot_file_width'].'px '.$this_data['robot_file_height'].'px; ';

                if (!empty($camera_action_styles)){ $temp_clone_style .= $camera_action_styles; }

                //$filters = 'grayscale(100%) sepia(1) hue-rotate(145deg)';
                //$temp_clone_style .= '-moz-filter: '.$filters.'; -webkit-filter: '.$filters.'; filter: '.$filters.'; ';
                $temp_clone_style .= rpg_ability::get_css_filter_styles_for_gemini_clone();
                $temp_clone_style .= $this_data['robot_frame_styles'];

                // Print out the clone with adjusted styles for the sprite
                echo '<div data-cloneid="'.$this_data['robot_id'].'" class="'.$temp_clone_class.'" style="'.$temp_clone_style.'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'"></div>';

            }

            // Only append extra icons if this robot is visible (like the UNLOCK ICON)
            if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){

                // Calculate whether or not this robot is currently unlockable
                $is_unlockable = isset($this_robot->flags['robot_is_unlockable']) ? $this_robot->flags['robot_is_unlockable'] : false;
                $is_corrupted = isset($this_robot->flags['robot_is_unlockable_corrupted']) ? $this_robot->flags['robot_is_unlockable_corrupted'] : false;

                // If this robot is unlockable, display the icon above its head
                if ($is_unlockable && $this_robot->robot_status != 'disabled'){

                    // Calculate the zoom properties for the icon sprite
                    $icon_size = 80;
                    $icon_type = !empty($this_data['robot_core']) ? $this_data['robot_core'] : 'none';
                    $icon_scale = $this_data['robot_scale'];
                    $icon_direction = $this_data['robot_direction'];
                    $icon_sprite_size = ceil($icon_scale * $icon_size);
                    $frame_index2 = explode('/', MMRPG_SETTINGS_ATTACHMENT_FRAMEINDEX);
                    $icon_file_width = ceil($icon_scale * $icon_size * count($frame_index2));
                    $icon_file_height = ceil($icon_scale * $icon_size);
                    $icon_float = $this_data['robot_float'];

                    // Calculate the offsets based on robot and scale
                    $icon_offset_z = $this_data['canvas_offset_z'] + 1;
                    $icon_offset_x = $this_data['canvas_offset_x'];
                    $icon_offset_y = $this_data['canvas_offset_y'];
                    $icon_offset_x -= 25;
                    $icon_offset_y += 20;
                    $base_multi = ceil($this_robot->robot_image_size / 40);
                    if ($base_multi > 1){
                        $icon_offset_x += ($base_multi - 1) * 25;
                        $icon_offset_y += ($base_multi - 1) * 6;
                    }

                    // Define the animation frames based on corrupted or not
                    if (!$is_corrupted){
                        $frame_animate = array('00', '01', '00', '02');
                    } else {
                        $frame_animate = array('03', '04', '05');
                    }
                    $frame_token = $frame_animate[0];
                    $frame_position = array_search($frame_token, $frame_index2);
                    $frame_background_offset = -1 * ceil(($icon_sprite_size * $frame_position));


                    // Generate the markup for the unlockable icon sprite
                    echo '<div '.
                        'class="'.
                            'sprite '.
                            'sprite_'.$icon_size.'x'.$icon_size.' '.
                            'sprite_'.$icon_size.'x'.$icon_size.'_'.$frame_token.' '.
                            '" '.
                        'style="'.
                            'background-image: url(images/objects/heart-cores/'.$icon_type.'/sprite_left_'.$icon_size.'x'.$icon_size.'.png?'.MMRPG_CONFIG_CACHE_DATE.'); '.
                            'background-size: '.$icon_file_width.'px '.$icon_file_height.'px; '.
                            'background-position: '.(!empty($frame_background_offset) ? $frame_background_offset.'px' : '0').' 0; '.
                            'width: '.$icon_sprite_size.'px; '.
                            'height: '.$icon_sprite_size.'px; '.
                            'z-index: '.$icon_offset_z.'; '.
                            $icon_float.': '.$icon_offset_x.'px; '.
                            'bottom: '.$icon_offset_y.'px; '.
                            ($is_corrupted ? 'filter: opacity(0.5); ' : '').
                            (!empty($camera_action_styles) ? $camera_action_styles : '').
                            '" '.
                        'data-type="attachment" '.
                        'data-size="'.$icon_sprite_size.'" '.
                        'data-direction="'.$icon_direction.'" '.
                        'data-frame="'.$frame_token.'" '.
                        'data-animate="'.implode(',',$frame_animate).'" '.
                        'data-scale="'.$icon_scale.'" '.
                        '></div>';

                }

            }

            // Check if his player has any other active robots
            $temp_player_active_robots = false;
            foreach ($this_robot->player->values['robots_active'] AS $info){
                if ($info['robot_position'] == 'active'){ $temp_player_active_robots = true; }
            }

            // Check if this is an active position robot before displaying the fullsize HUD details
            if ($this_data['robot_position'] != 'bench' || ($temp_player_active_robots == false && $this_data['robot_frame'] == 'damage')){

                // Define the mugshot and detail variables for the GUI
                $details_data = $this_data;
                $details_data['robot_file'] = 'images/robots/'.$details_data['robot_image'].'/sprite_'.$details_data['robot_direction'].'_'.$details_data['robot_size'].'x'.$details_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $details_data['robot_details'] = '<div class="robot_name">'.$this_robot->robot_name.'</div>';
                $details_data['robot_details'] .= '<div class="robot_level robot_type robot_type_'.($this_robot->robot_level >= 100 ? 'electric' : 'none').'">Lv. '.$this_robot->robot_level.'</div>';
                $details_data['robot_details'] .= '<div class="'.$details_data['energy_class'].'" style="'.$details_data['energy_style'].'" data-click-tooltip="'.$details_data['energy_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$this_data['energy_tooltip_type'].'">'.$details_data['energy_title'].'</div>';
                $details_data['robot_details'] .= '<div class="'.$details_data['weapons_class'].'" style="'.$details_data['weapons_style'].'" data-click-tooltip="'.$details_data['weapons_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_weapons">'.$details_data['weapons_title'].'</div>';
                if ($this_data['robot_float'] == 'left'){ $details_data['robot_details'] .= '<div class="'.$details_data['experience_class'].'" style="'.$details_data['experience_style'].'" data-click-tooltip="'.$details_data['experience_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_experience">'.$details_data['experience_title'].'</div>'; }

                // Loop through and define the other stat variables and markup ($robot_attack_markup, $robot_defense_markup, $robot_speed_markup)
                $stat_tokens = array('attack' => 'AT', 'defense' => 'DF', 'speed' => 'SP');
                foreach ($stat_tokens AS $stat => $letters){
                    $prop_stat = 'robot_'.$stat;
                    $prop_stat_base = 'robot_base_'.$stat;
                    $prop_stat_max = 'robot_max_'.$stat;
                    $prop_markup = 'robot_'.$stat.'_markup';
                    $prop_value = $this_robot->$prop_stat;
                    $prop_value_base = $this_robot->$prop_stat_base;
                    $temp_stat_break = $prop_value < 1 ? true : false;
                    $temp_stat_break_chance = $prop_value < ($prop_value_base / 2) ? true : false;
                    $temp_stat_maxed = $this_stats[$stat]['current'] >= $this_stats[$stat]['max'] ? true : false;
                    $temp_stat_percent = round(($prop_value / $prop_value_base) * 100);
                    if ($this_data['robot_float'] == 'left'){ $temp_stat_title = $prop_value.' / '.$prop_value_base.' '.$letters.' | '.$temp_stat_percent.'%'; }
                    elseif ($this_data['robot_float'] == 'right'){ $temp_stat_title = ($temp_stat_maxed ? '&#9733; |' : '').$temp_stat_percent.'% | '.$prop_value.' / '.$prop_value_base.' '.$letters; }
                    $$prop_markup = '<div class="robot_'.$stat.''.($temp_stat_break ? ' robot_'.$stat.'_break' : ($temp_stat_break_chance ? ' robot_'.$stat.'_break_chance' : '')).($prop_value > MMRPG_SETTINGS_STATS_MAX ? ' limit_break' : '').'" data-click-tooltip="'.$temp_stat_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$stat.'">'.$prop_value.'</div>';
                    if (!empty($this_robot->counters[$stat.'_mods'])){
                        $temp_stat_mods = $this_robot->counters[$stat.'_mods'];
                        if ($temp_stat_mods > 0){ $$prop_markup .= '<div class="stat_mod '.$stat.' plus s'.$temp_stat_mods.'"></div>'; }
                        elseif ($temp_stat_mods < 0){ $$prop_markup .= '<div class="stat_mod '.$stat.' minus s'.($temp_stat_mods * -1).'"></div>'; }
                    }
                }

                // Add these markup variables to the details string
                if ($details_data['robot_float'] == 'left'){
                    $details_data['robot_details'] .= $robot_attack_markup;
                    $details_data['robot_details'] .= $robot_defense_markup;
                    $details_data['robot_details'] .= $robot_speed_markup;
                } else {
                    $details_data['robot_details'] .= $robot_speed_markup;
                    $details_data['robot_details'] .= $robot_defense_markup;
                    $details_data['robot_details'] .= $robot_attack_markup;
                }

                // If this robot is holding an item, add it to the display
                if (!empty($this_robot->robot_item)){
                    $temp_item_info = rpg_item::get_index_info($this_robot->robot_item);
                    $details_data['item_title'] = $temp_item_info['item_name'];
                    $details_data['item_type'] = !empty($temp_item_info['item_type']) ? $temp_item_info['item_type'] : 'none';
                    $details_data['item_type2'] = !empty($temp_item_info['item_type2']) ? $temp_item_info['item_type2'] : '';
                    $details_data['item_title_type'] = $details_data['item_type'];
                    $details_data['item_file'] = 'images/items/'.$this_robot->robot_item.'/icon_'.$details_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
                    $details_data['item_class'] = 'sprite size40 mugshot '.$details_data['robot_float'].' ';
                    $details_data['item_style'] = 'background-image: url('.$details_data['item_file'].'); ';
                    if (!empty($details_data['item_type2'])){
                        if ($details_data['item_title_type'] != 'none'){ $details_data['item_title_type'] .= '_'.$details_data['item_type2']; }
                        else { $details_data['item_title_type'] = $details_data['item_type2']; }
                    }
                    $item_markup = '<div class="robot_item">';
                        $item_markup .= '<div class="wrap type '.$details_data['item_title_type'].'">';
                            $item_markup .= '<div class="'.$details_data['item_class'].'" style="'.$details_data['item_style'].'" data-click-tooltip="'.$details_data['item_title'].'" data-tooltip-type="type '.$details_data['item_title_type'].'">&nbsp;</div>';
                        $item_markup .= '</div>';
                    $item_markup .= '</div>';
                    $details_data['robot_details'] .= $item_markup;
                }

                $details_data['mugshot_file'] = 'images/robots/'.$details_data['robot_image'].'/mug_'.$details_data['robot_direction'].'_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $details_data['mugshot_class'] = 'sprite details robot_mugshot ';
                $details_data['mugshot_class'] .= 'sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot sprite_mugshot_'.$details_data['robot_float'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot_'.$details_data['robot_float'].' ';
                $details_data['mugshot_class'] .= 'robot_status_'.$details_data['robot_status'].' robot_position_'.$details_data['robot_position'].' ';
                $details_data['mugshot_style'] = 'z-index: 9100; ';
                $details_data['mugshot_style'] .= 'background-image: url('.$details_data['mugshot_file'].'); ';

                // Display the robot's mugshot sprite and detail fields
                echo '<div data-detailsid="'.$this_data['robot_id'].'" class="sprite details robot_details robot_details_'.$details_data['robot_float'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').'><div class="container">'.$details_data['robot_details'].'</div></div>';
                echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.str_replace('80x80', '40x40', $details_data['mugshot_class']).' robot_mugshot_type robot_type robot_type_'.$this_data['robot_core'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').' data-click-tooltip="'.$details_data['robot_title_tooltip'].'"><div class="sprite">&nbsp;</div></div>';
                echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'"></div>';

                // Update the main data array with this markup
                $this_data['details'] = $details_data;
            }

        // Collect the generated robot markup
        $this_data['robot_markup'] = trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating ability canvas variables
    public static function ability_markup($this_ability, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the ability data array and populate basic data
        $this_data['ability_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'ability';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['ability_name'] = isset($options['ability_name']) ? $options['ability_name'] : $this_ability->ability_name;
        $this_data['ability_id'] = $this_ability->ability_id;
        $this_data['ability_title'] = $this_ability->ability_name;
        $this_data['ability_token'] = $this_ability->ability_token;
        $this_data['ability_id_token'] = $this_ability->ability_id.'_'.$this_ability->ability_token;
        $this_data['ability_image'] = isset($options['ability_image']) ? $options['ability_image'] : $this_ability->ability_image;
        $this_data['ability_image2'] = isset($options['ability_image2']) ? $options['ability_image2'] : $this_ability->ability_image2;
        $this_data['ability_status'] = $robot_data['robot_status'];
        $this_data['ability_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['ability_direction'] = $this_ability->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['ability_float'] = $robot_data['robot_float'];
        $this_data['ability_size'] = ($this_ability->ability_image_size * 2);
        $this_data['ability_frame'] = isset($options['ability_frame']) ? $options['ability_frame'] : $this_ability->ability_frame;
        $this_data['ability_frame_span'] = isset($options['ability_frame_span']) ? $options['ability_frame_span'] : $this_ability->ability_frame_span;
        $this_data['ability_frame_index'] = isset($options['ability_frame_index']) ? $options['ability_frame_index'] : $this_ability->ability_frame_index;
        if (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] >= 0){ $this_data['ability_frame'] = str_pad($this_data['ability_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] < 0){ $this_data['ability_frame'] = ''; }
        $this_data['ability_frame_offset'] = isset($options['ability_frame_offset']) && is_array($options['ability_frame_offset']) ? $options['ability_frame_offset'] : $this_ability->ability_frame_offset;
        $animate_frames_array = isset($options['ability_frame_animate']) ? $options['ability_frame_animate'] : array($this_data['ability_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['ability_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['ability_frame_styles'] = isset($options['ability_frame_styles']) ? $options['ability_frame_styles'] : $this_ability->ability_frame_styles;
        $this_data['ability_frame_classes'] = isset($options['ability_frame_classes']) ? $options['ability_frame_classes'] : $this_ability->ability_frame_classes;

        $this_data['ability_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : 1;

        // DEBUG
        //$this_ability->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_ability_target\'] = '.$options['this_ability_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_ability->ability_image_size * 2);
        $this_data['ability_sprite_size'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_width'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_height'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_image_width'] = ceil($this_data['ability_scale'] * $zoom_size * 10);
        $this_data['ability_image_height'] = ceil($this_data['ability_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this ability
        //error_log(PHP_EOL.'ability:'.$this_data['ability_token'].' needs canvas_markup_offset()');
        $canvas_offset_data = $this_ability->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size'], $this_ability->player->counters['robots_total']);
        //$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        //$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        //$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];

        // Define the ability's canvas offset variables
        //$temp_size_diff = $robot_data['robot_sprite_size'] != $ability_data['ability_sprite_size'] ? ceil(($robot_data['robot_sprite_size'] - $ability_data['ability_sprite_size']) * 0.5) : ceil($ability_data['ability_sprite_size'] * 0.25);
        //$temp_size_diff = $robot_data['robot_sprite_size'] > 80 ? ceil(($robot_data['robot_sprite_size'] - 80) / 2) : 0;
        //if ($temp_size_diff > 0 && $robot_data['robot_position'] != 'active'){ $temp_size_diff += floor($this_data['ability_scale'] * $this_data['ability_sprite_size'] * 0.5); }
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['ability_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['ability_sprite_size']) / 2) ; }
        //$temp_size_diff = floor(($temp_size_diff * 2) + ($temp_size_diff * $robot_data['robot_scale']));

        // If this is a STICKY attachedment, make sure it doesn't move with the robot
        if ($this_data['data_sticky'] != false){

            // Calculate the canvas X offsets using the robot's offset as base
            $ability_frame_offset_x = isset($this_data['ability_frame_offset']['x']) ? $this_data['ability_frame_offset']['x'] : 0;
            if ($ability_frame_offset_x > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] + ($this_data['ability_sprite_size'] * ($ability_frame_offset_x/100))) + $temp_size_diff; }
            elseif ($ability_frame_offset_x < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] - ($this_data['ability_sprite_size'] * (($ability_frame_offset_x * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x'] + $temp_size_diff; }
            // Calculate the canvas Y offsets using the robot's offset as base
            $ability_frame_offset_y = isset($this_data['ability_frame_offset']['y']) ? $this_data['ability_frame_offset']['y'] : 0;
            if ($ability_frame_offset_y > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] + ($this_data['ability_sprite_size'] * ($ability_frame_offset_y/100))); }
            elseif ($ability_frame_offset_y < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] - ($this_data['ability_sprite_size'] * (($ability_frame_offset_y * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            $ability_frame_offset_z = isset($this_data['ability_frame_offset']['z']) ? $this_data['ability_frame_offset']['z'] : 0;
            if ($ability_frame_offset_z > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] + $ability_frame_offset_z); }
            elseif ($ability_frame_offset_z < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] - ($ability_frame_offset_z * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];  }

        }
        // Else if this is a normal attachment, it moves with the robot
        else {

            // Calculate the canvas X offsets using the robot's offset as base
            $ability_frame_offset_x = isset($this_data['ability_frame_offset']['x']) ? $this_data['ability_frame_offset']['x'] : 0;
            if ($ability_frame_offset_x > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['ability_sprite_size'] * ($ability_frame_offset_x/100))) + $temp_size_diff; }
            elseif ($ability_frame_offset_x < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['ability_sprite_size'] * (($ability_frame_offset_x * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            $ability_frame_offset_y = isset($this_data['ability_frame_offset']['y']) ? $this_data['ability_frame_offset']['y'] : 0;
            if ($ability_frame_offset_y > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['ability_sprite_size'] * ($ability_frame_offset_y/100))); }
            elseif ($ability_frame_offset_y < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['ability_sprite_size'] * (($ability_frame_offset_y * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            $ability_frame_offset_z = isset($this_data['ability_frame_offset']['z']) ? $this_data['ability_frame_offset']['z'] : 0;
            if ($ability_frame_offset_z > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $ability_frame_offset_z); }
            elseif ($ability_frame_offset_z < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($ability_frame_offset_z * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

        }

        // If a clone is present, we may have to adjust the sprite
        $gemini_clone_active = false;
        if (isset($this_ability->robot->robot_attachments['ability_gemini-clone'])
            && !empty($this_ability->robot->flags['gemini-clone_is_using_ability'])){
            $gemini_clone_active = true;
        }

        // If this ability is being used by a Gemini Clone, offset the position
        if ($gemini_clone_active && !$this_data['data_sticky']){
            $this_data['canvas_offset_x'] -= 40;
            $this_data['canvas_offset_y'] -= 2;
        }

        // Define the rest of the display variables
        if (!preg_match('/^images/i', $this_data['ability_image'])){ $this_data['ability_image_path'] = 'images/abilities/'.$this_data['ability_image'].'/sprite_'.$this_data['ability_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        else { $this_data['ability_image_path'] = $this_data['ability_image']; }
        $this_data['ability_markup_class'] = 'sprite sprite_ability ';
        $this_data['ability_markup_class'] .= 'sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].' sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].'_'.$this_data['ability_frame'].' ';
        $this_data['ability_markup_class'] .= 'ability_status_'.$this_data['ability_status'].' ability_position_'.$this_data['ability_position'].' ';

        if ($this_data['ability_scale'] !== 1){
            $this_data['ability_markup_class'] .= 'scaled ';
        }

        $frame_position = is_numeric($this_data['ability_frame']) ? (int)($this_data['ability_frame']) : array_search($this_data['ability_frame'], $this_data['ability_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['ability_sprite_size'] * $frame_position));
        $this_data['ability_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['ability_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['ability_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['ability_markup_style'] .= 'background-image: url('.$this_data['ability_image_path'].'); width: '.($this_data['ability_sprite_size'] * $this_data['ability_frame_span']).'px; height: '.$this_data['ability_sprite_size'].'px; background-size: '.$this_data['ability_image_width'].'px '.$this_data['ability_image_height'].'px; ';

        // DEBUG
        //$this_data['ability_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));


        // Generate the final markup for the canvas ability
        ob_start();

            // Display the ability's battle sprite
            $temp_markup = '<div '.
                'data-ability-id="'.$this_data['ability_id_token'].'" '.
                'data-robot-id="'.$robot_data['robot_id_token'].'" '.
                'class="'.($this_data['ability_markup_class'].$this_data['ability_frame_classes']).'" '.
                'style="'.($this_data['ability_markup_style'].$this_data['ability_frame_styles']).'" '.
                (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' '.
                'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                'data-type="'.$this_data['data_type'].'" '.
                'data-size="'.$this_data['ability_sprite_size'].'" '.
                'data-direction="'.$this_data['ability_direction'].'" '.
                'data-frame="'.$this_data['ability_frame'].'" '.
                'data-animate="'.$this_data['ability_frame_animate'].'" '.
                'data-position="'.$this_data['ability_position'].'" '.
                'data-status="'.$this_data['ability_status'].'" '.
                'data-scale="'.$this_data['ability_scale'].'" '.
                '></div>';
            if (!empty($this_data['ability_image2'])){ $temp_markup .= str_replace('/'.$this_data['ability_image'].'/', '/'.$this_data['ability_image2'].'/', $temp_markup); }
            echo $temp_markup;

        // Collect the generated ability markup
        $this_data['ability_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating ability canvas variables
    public static function static_ability_markup($this_ability, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the ability data array and populate basic data
        $this_data['ability_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'ability';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['ability_name'] = isset($options['ability_name']) ? $options['ability_name'] : $this_ability->ability_name;
        $this_data['ability_id'] = $this_ability->ability_id;
        $this_data['ability_title'] = $this_ability->ability_name;
        $this_data['ability_token'] = $this_ability->ability_token;
        $this_data['ability_id_token'] = $this_ability->ability_id.'_'.$this_ability->ability_token;
        $this_data['ability_image'] = isset($options['ability_image']) ? $options['ability_image'] : $this_ability->ability_image;
        $this_data['ability_image2'] = isset($options['ability_image2']) ? $options['ability_image2'] : $this_ability->ability_image2;
        $this_data['ability_status'] = $robot_data['robot_status'];
        $this_data['ability_position'] = $robot_data['robot_position'];
        $this_data['ability_direction'] = $robot_data['robot_direction'];
        $this_data['ability_float'] = $robot_data['robot_float'];
        $this_data['ability_size'] = ($this_ability->ability_image_size * 2);
        $this_data['ability_frame'] = isset($options['ability_frame']) ? $options['ability_frame'] : $this_ability->ability_frame;
        $this_data['ability_frame_span'] = isset($options['ability_frame_span']) ? $options['ability_frame_span'] : $this_ability->ability_frame_span;
        $this_data['ability_frame_index'] = isset($options['ability_frame_index']) ? $options['ability_frame_index'] : $this_ability->ability_frame_index;
        if (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] >= 0){ $this_data['ability_frame'] = str_pad($this_data['ability_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] < 0){ $this_data['ability_frame'] = ''; }
        $this_data['ability_frame_offset'] = isset($options['ability_frame_offset']) && is_array($options['ability_frame_offset']) ? $options['ability_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $animate_frames_array = isset($options['ability_frame_animate']) ? $options['ability_frame_animate'] : array($this_data['ability_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['ability_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['ability_frame_styles'] = isset($options['ability_frame_styles']) ? $options['ability_frame_styles'] : $this_ability->ability_frame_styles;
        $this_data['ability_frame_classes'] = isset($options['ability_frame_classes']) ? $options['ability_frame_classes'] : $this_ability->ability_frame_classes;

        $this_data['ability_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : ($robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $robot_data['robot_key']) / 8) * 0.5));

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_ability->ability_image_size * 2);
        $this_data['ability_sprite_size'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_width'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_height'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_image_width'] = ceil($this_data['ability_scale'] * $zoom_size * 10); // 'cause there are ten frames in the sheet
        $this_data['ability_image_height'] = ceil($this_data['ability_scale'] * $zoom_size);

        /*
        // Update the canvas offsets using the base data
        $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x'];
        $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];
        $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];
        */

        // Calculate the canvas X offsets using the robot's offset as base
        if ($this_data['ability_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['x']/100))); }
        elseif ($this_data['ability_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['x'] * -1)/100))); }
        else { $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x']; }
        // Calculate the canvas Y offsets using the robot's offset as base
        if ($this_data['ability_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['y']/100))); }
        elseif ($this_data['ability_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['y'] * -1)/100))); }
        else { $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];  }
        // Calculate the canvas Z offsets using the robot's offset as base
        if ($this_data['ability_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] + $this_data['ability_frame_offset']['z']); }
        elseif ($this_data['ability_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] - ($this_data['ability_frame_offset']['z'] * -1)); }
        else { $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];  }

        // Define the rest of the display variables
        if (!preg_match('/^images/i', $this_data['ability_image'])){ $this_data['ability_image_path'] = 'images/abilities/'.$this_data['ability_image'].'/sprite_'.$this_data['ability_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        else { $this_data['ability_image_path'] = $this_data['ability_image']; }
        $this_data['ability_markup_class'] = 'sprite sprite_ability ';
        $this_data['ability_markup_class'] .= 'sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].' sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].'_'.$this_data['ability_frame'].' ';
        $this_data['ability_markup_class'] .= 'ability_status_'.$this_data['ability_status'].' ability_position_'.$this_data['ability_position'].' ';
        $frame_position = is_numeric($this_data['ability_frame']) ? (int)($this_data['ability_frame']) : array_search($this_data['ability_frame'], $this_data['ability_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['ability_sprite_size'] * $frame_position));
        $this_data['ability_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['ability_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['ability_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['ability_markup_style'] .= 'background-image: url('.$this_data['ability_image_path'].'); width: '.($this_data['ability_sprite_size'] * $this_data['ability_frame_span']).'px; height: '.$this_data['ability_sprite_size'].'px; background-size: '.$this_data['ability_image_width'].'px '.$this_data['ability_image_height'].'px; ';

        // Generate the final markup for the canvas ability
        ob_start();

            // Display the ability's battle sprite
            $temp_markup = '<div '.
                'data-ability-id="'.$this_data['ability_id_token'].'" '.
                'class="'.($this_data['ability_markup_class'].$this_data['ability_frame_classes']).'" '.
                'style="'.($this_data['ability_markup_style'].$this_data['ability_frame_styles']).'" '.
                (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').
                'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                'data-type="'.$this_data['data_type'].'" '.
                'data-size="'.$this_data['ability_sprite_size'].'" '.
                'data-direction="'.$this_data['ability_direction'].'" '.
                'data-frame="'.$this_data['ability_frame'].'" '.
                'data-animate="'.$this_data['ability_frame_animate'].'" '.
                'data-position="'.$this_data['ability_position'].'" '.
                'data-status="'.$this_data['ability_status'].'" '.
                'data-scale="'.$this_data['ability_scale'].'"'.
                '></div>';
            if (!empty($this_data['ability_image2'])){ $temp_markup .= str_replace('/'.$this_data['ability_image'].'/', '/'.$this_data['ability_image2'].'/', $temp_markup); }
            echo $temp_markup;

        // Collect the generated ability markup
        $this_data['ability_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating item canvas variables
    public static function item_markup($this_item, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the item data array and populate basic data
        $this_data['item_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'item';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['item_name'] = isset($options['item_name']) ? $options['item_name'] : $this_item->item_name;
        $this_data['item_id'] = $this_item->item_id;
        $this_data['item_title'] = $this_item->item_name;
        $this_data['item_token'] = $this_item->item_token;
        $this_data['item_id_token'] = $this_item->item_id.'_'.$this_item->item_token;
        $this_data['item_image'] = isset($options['item_image']) ? $options['item_image'] : $this_item->item_image;
        $this_data['item_quantity'] = !empty($options['this_item_quantity']) ? $options['this_item_quantity'] : $this_item->item_quantity;
        $this_data['item_status'] = $robot_data['robot_status'];
        $this_data['item_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['item_direction'] = $this_item->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['item_float'] = $robot_data['robot_float'];
        $this_data['item_size'] = ($this_item->item_image_size * 2);
        $this_data['item_frame'] = isset($options['item_frame']) ? $options['item_frame'] : $this_item->item_frame;
        $this_data['item_frame_span'] = isset($options['item_frame_span']) ? $options['item_frame_span'] : $this_item->item_frame_span;
        $this_data['item_frame_index'] = isset($options['item_frame_index']) ? $options['item_frame_index'] : $this_item->item_frame_index;
        if (is_numeric($this_data['item_frame']) && $this_data['item_frame'] >= 0){ $this_data['item_frame'] = str_pad($this_data['item_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['item_frame']) && $this_data['item_frame'] < 0){ $this_data['item_frame'] = ''; }
        //$this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/sprite_'.$this_data['item_direction'].'_'.$this_data['item_size'].'x'.$this_data['item_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['item_frame_offset'] = !empty($options['item_frame_offset']) && is_array($options['item_frame_offset']) ? $options['item_frame_offset'] : $this_item->item_frame_offset;
        $animate_frames_array = isset($options['item_frame_animate']) ? $options['item_frame_animate'] : array($this_data['item_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['item_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['item_frame_styles'] = isset($options['item_frame_styles']) ? $options['item_frame_styles'] : $this_item->item_frame_styles;
        $this_data['item_frame_classes'] = isset($options['item_frame_classes']) ? $options['item_frame_classes'] : $this_item->item_frame_classes;

        $this_data['item_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : 1;

        // DEBUG
        //$this_item->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_item_target\'] = '.$options['this_item_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_item->item_image_size * 2);
        $this_data['item_sprite_size'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_width'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_height'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_image_width'] = ceil($this_data['item_scale'] * $zoom_size * 10);
        $this_data['item_image_height'] = ceil($this_data['item_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this item
        //error_log(PHP_EOL.'item:'.$this_data['item_token'].' needs canvas_markup_offset()');
        $canvas_offset_data = $this_item->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size'], $this_item->player->counters['robots_total']);
        //$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        //$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        //$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];

        // Define the item's canvas offset variables
        //$temp_size_diff = $robot_data['robot_sprite_size'] != $item_data['item_sprite_size'] ? ceil(($robot_data['robot_sprite_size'] - $item_data['item_sprite_size']) * 0.5) : ceil($item_data['item_sprite_size'] * 0.25);
        //$temp_size_diff = $robot_data['robot_sprite_size'] > 80 ? ceil(($robot_data['robot_sprite_size'] - 80) / 2) : 0;
        //if ($temp_size_diff > 0 && $robot_data['robot_position'] != 'active'){ $temp_size_diff += floor($this_data['item_scale'] * $this_data['item_sprite_size'] * 0.5); }
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['item_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['item_sprite_size']) / 2) ; }
        //$temp_size_diff = floor(($temp_size_diff * 2) + ($temp_size_diff * $robot_data['robot_scale']));

        // If this is a STICKY attachedment, make sure it doesn't move with the robot
        if ($this_data['data_sticky'] != false){

            //$this_data['data_sticky'] = 'true';

            // Calculate the canvas X offsets using the robot's position as base
            if ($this_data['item_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['item_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $canvas_offset_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's position as base
            if ($this_data['item_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['y']/100))); }
            elseif ($this_data['item_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $canvas_offset_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's position as base
            if ($this_data['item_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] + $this_data['item_frame_offset']['z']); }
            elseif ($this_data['item_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] - ($this_data['item_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $canvas_offset_data['canvas_offset_z'];  }

            // Collect the target, damage, and recovery options
            $this_target_options = !empty($options['this_item']->target_options) ? $options['this_item']->target_options : array();
            $this_damage_options = !empty($options['this_item']->damage_options) ? $options['this_item']->damage_options : array();
            $this_recovery_options = !empty($options['this_item']->recovery_options) ? $options['this_item']->recovery_options : array();
            $this_results = !empty($options['this_item']->item_results) ? $options['this_item']->item_results : array();

            // Either way, apply target offsets if they exist and it's this robot using the item
            if (isset($options['this_item_target']) && $options['this_item_target'] == $this_data['robot_id_token']){
                // If any of the co-ordinates are provided, update all
                if (!empty($this_target_options['target_kickback']['x'])
                    || !empty($this_target_options['target_kickback']['y'])
                    || !empty($this_target_options['target_kickback']['z'])){
                    $this_data['canvas_offset_x'] -= $this_target_options['target_kickback']['x'];
                    $this_data['canvas_offset_y'] -= $this_target_options['target_kickback']['y'];
                    $this_data['canvas_offset_z'] -= $this_target_options['target_kickback']['z'];
                }
            }

        }
        // Else if this is a normal attachment, it moves with the robot
        else {

            // Calculate the canvas X offsets using the robot's offset as base
            if ($this_data['item_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['item_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            if ($this_data['item_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['y']/100))); }
            elseif ($this_data['item_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            if ($this_data['item_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $this_data['item_frame_offset']['z']); }
            elseif ($this_data['item_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($this_data['item_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

        }

        // Define the middle value of item quantity for perspection calculations
        if ($this_data['item_quantity'] == 1){ $middle_quantity_key = 0; }
        elseif ($this_data['item_quantity'] % 2 != 0){ $middle_quantity_key = ($this_data['item_quantity'] - 1) / 2; }
        else { $middle_quantity_key = $this_data['item_quantity'] / 2; }

        // Check to see if this is a fusing shard (in which case we'll adjust display)
        $shards_fusing_this_turn = false;
        if (strstr($this_data['item_token'], '-shard')
            && $this_data['item_quantity'] === MMRPG_SETTINGS_SHARDS_MAXQUANTITY){
            $shards_fusing_this_turn = true;
        }

        // Shift the original x offset if there's more than one of an item
        $quantity_offset_shift = 0;
        if ($this_data['item_quantity'] > 1){
            //$this_data['canvas_offset_x'] += 40;
            //$this_data['canvas_offset_x'] -= round(($this_data['item_quantity'] / 2) * 7);
            $quantity_offset_shift = round($this_data['item_quantity'] * 3);
            $this_data['canvas_offset_x'] += $quantity_offset_shift;
            $this_data['canvas_offset_y'] -= round(($this_data['item_quantity'] / 2) * 1);
        }

        // Generate the final markup for the canvas item
        ob_start();

            // Loop through the item quantity and display sprites
            $canvas_offset_x = $this_data['canvas_offset_x'];
            $canvas_offset_y = $this_data['canvas_offset_y'];
            $canvas_offset_z = $this_data['canvas_offset_z'];
            if ($shards_fusing_this_turn){ $canvas_offset_x -= $quantity_offset_shift; }
            for ($item_key = 0; $item_key < $this_data['item_quantity']; $item_key++){

                // Define the rest of the display variables
                //$this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
                if (!preg_match('/^images/i', $this_data['item_image'])){ $this_data['item_image'] = 'images/items/'.$this_data['item_image'].'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
                $this_data['item_markup_class'] = 'sprite sprite_item ';
                $this_data['item_markup_class'] .= 'sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].' sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].'_'.$this_data['item_frame'].' ';
                $this_data['item_markup_class'] .= 'item_status_'.$this_data['item_status'].' item_position_'.$this_data['item_position'].' ';

                $frame_position = is_numeric($this_data['item_frame']) ? (int)($this_data['item_frame']) : array_search($this_data['item_frame'], $this_data['item_frame_index']);
                if ($frame_position === false){ $frame_position = 0; }
                $frame_background_offset = -1 * ceil(($this_data['item_sprite_size'] * $frame_position));

                if (!$shards_fusing_this_turn
                    && $item_key > 0){
                    $offset_multiplier = $item_key > $middle_quantity_key ? 1 : -1;
                    $canvas_offset_x += 10;
                    if ($item_key > $middle_quantity_key){
                        $canvas_offset_y += 3;
                        $canvas_offset_z -= 1;
                    } else {
                        $canvas_offset_y -= 3;
                        $canvas_offset_z += 1;
                    }
                }

                if ($this_data['item_scale'] !== 1){
                    $this_data['item_markup_class'] .= 'scaled ';
                }

                $this_data['item_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
                $this_data['item_markup_style'] .= 'pointer-events: none; z-index: '.$canvas_offset_z.'; '.$this_data['item_float'].': '.$canvas_offset_x.'px; bottom: '.$canvas_offset_y.'px; ';
                $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image'].'); width: '.($this_data['item_sprite_size'] * $this_data['item_frame_span']).'px; height: '.$this_data['item_sprite_size'].'px; background-size: '.$this_data['item_image_width'].'px '.$this_data['item_image_height'].'px; ';

                if ($shards_fusing_this_turn){
                    if ($item_key === 1){ $this_data['item_markup_style'] .= 'transform: rotate(180deg);'; }
                    if ($item_key === 2){ $this_data['item_markup_style'] .= 'transform: rotate(90deg);'; }
                    if ($item_key === 3){ $this_data['item_markup_style'] .= 'transform: rotate(270deg);'; }
                }

                // DEBUG
                //$this_data['item_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));

                // Display the item's battle sprite
                echo '<div '.
                    'data-item-id="'.$this_data['item_id_token'].'" '.
                    'data-item-quantity="'.$this_data['item_quantity'].'" '.
                    'data-robot-id="'.$robot_data['robot_id_token'].'" '.
                    'class="'.($this_data['item_markup_class'].$this_data['item_frame_classes']).'" '.
                    'style="'.($this_data['item_markup_style'].$this_data['item_frame_styles']).'" '.
                    (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' '.
                    'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                    'data-type="'.$this_data['data_type'].'" '.
                    'data-size="'.$this_data['item_sprite_size'].'" '.
                    'data-direction="'.$this_data['item_direction'].'" '.
                    'data-frame="'.$this_data['item_frame'].'" '.
                    'data-animate="'.$this_data['item_frame_animate'].'" '.
                    'data-position="'.$this_data['item_position'].'" '.
                    'data-status="'.$this_data['item_status'].'" '.
                    'data-scale="'.$this_data['item_scale'].'" '.
                    '></div>';

            }

        // Collect the generated item markup
        $this_data['item_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating skill canvas variables
    public static function skill_markup($this_skill, $options, $player_data, $robot_data){

        // If this skill has no image AND an image was not provided, return immediately
        if (empty($options['skill_image']) && empty($this_skill->skill_image)){ return ''; }

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the skill data array and populate basic data
        $this_data['skill_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'skill';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['skill_name'] = isset($options['skill_name']) ? $options['skill_name'] : $this_skill->skill_name;
        $this_data['skill_id'] = $this_skill->skill_id;
        $this_data['skill_title'] = $this_skill->skill_name;
        $this_data['skill_token'] = $this_skill->skill_token;
        $this_data['skill_id_token'] = $this_skill->skill_id.'_'.$this_skill->skill_token;
        $this_data['skill_image'] = isset($options['skill_image']) ? $options['skill_image'] : $this_skill->skill_image;
        $this_data['skill_status'] = $robot_data['robot_status'];
        $this_data['skill_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['skill_direction'] = $this_skill->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['skill_float'] = $robot_data['robot_float'];
        $this_data['skill_size'] = ($this_skill->skill_image_size * 2);
        $this_data['skill_frame'] = isset($options['skill_frame']) ? $options['skill_frame'] : $this_skill->skill_frame;
        $this_data['skill_frame_span'] = isset($options['skill_frame_span']) ? $options['skill_frame_span'] : $this_skill->skill_frame_span;
        $this_data['skill_frame_index'] = isset($options['skill_frame_index']) ? $options['skill_frame_index'] : $this_skill->skill_frame_index;
        if (is_numeric($this_data['skill_frame']) && $this_data['skill_frame'] >= 0){ $this_data['skill_frame'] = str_pad($this_data['skill_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['skill_frame']) && $this_data['skill_frame'] < 0){ $this_data['skill_frame'] = ''; }
        //$this_data['skill_image'] = 'images/skills/'.(!empty($this_data['skill_image']) ? $this_data['skill_image'] : $this_data['skill_token']).'/sprite_'.$this_data['skill_direction'].'_'.$this_data['skill_size'].'x'.$this_data['skill_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['skill_frame_offset'] = isset($options['skill_frame_offset']) && is_array($options['skill_frame_offset']) ? $options['skill_frame_offset'] : $this_skill->skill_frame_offset;
        $animate_frames_array = isset($options['skill_frame_animate']) ? $options['skill_frame_animate'] : array($this_data['skill_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['skill_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['skill_frame_styles'] = isset($options['skill_frame_styles']) ? $options['skill_frame_styles'] : $this_skill->skill_frame_styles;
        $this_data['skill_frame_classes'] = isset($options['skill_frame_classes']) ? $options['skill_frame_classes'] : $this_skill->skill_frame_classes;

        $this_data['skill_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : 1;

        // DEBUG
        //$this_skill->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_skill_target\'] = '.$options['this_skill_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_skill->skill_image_size * 2);
        $this_data['skill_sprite_size'] = ceil($this_data['skill_scale'] * $zoom_size);
        $this_data['skill_sprite_width'] = ceil($this_data['skill_scale'] * $zoom_size);
        $this_data['skill_sprite_height'] = ceil($this_data['skill_scale'] * $zoom_size);
        $this_data['skill_image_width'] = ceil($this_data['skill_scale'] * $zoom_size * 10);
        $this_data['skill_image_height'] = ceil($this_data['skill_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this skill
        //error_log(PHP_EOL.'skill:'.$this_data['skill_token'].' by robot:'.$robot_data['robot_token'].' needs canvas_markup_offset()');
        $canvas_offset_data = $this_skill->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size'], $this_skill->player->counters['robots_total']);
        //$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        //$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        //$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];

        // Define the skill's canvas offset variables
        //$temp_size_diff = $robot_data['robot_sprite_size'] != $skill_data['skill_sprite_size'] ? ceil(($robot_data['robot_sprite_size'] - $skill_data['skill_sprite_size']) * 0.5) : ceil($skill_data['skill_sprite_size'] * 0.25);
        //$temp_size_diff = $robot_data['robot_sprite_size'] > 80 ? ceil(($robot_data['robot_sprite_size'] - 80) / 2) : 0;
        //if ($temp_size_diff > 0 && $robot_data['robot_position'] != 'active'){ $temp_size_diff += floor($this_data['skill_scale'] * $this_data['skill_sprite_size'] * 0.5); }
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['skill_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['skill_sprite_size']) / 2) ; }
        //$temp_size_diff = floor(($temp_size_diff * 2) + ($temp_size_diff * $robot_data['robot_scale']));

        // If this is a STICKY attachedment, make sure it doesn't move with the robot
        if ($this_data['data_sticky'] != false){

            //$this_data['data_sticky'] = 'true';

            // Calculate the canvas X offsets using the robot's position as base
            if ($this_data['skill_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] + ($this_data['skill_sprite_size'] * ($this_data['skill_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['skill_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] - ($this_data['skill_sprite_size'] * (($this_data['skill_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $canvas_offset_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's position as base
            if ($this_data['skill_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] + ($this_data['skill_sprite_size'] * ($this_data['skill_frame_offset']['y']/100))); }
            elseif ($this_data['skill_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] - ($this_data['skill_sprite_size'] * (($this_data['skill_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $canvas_offset_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's position as base
            if ($this_data['skill_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] + $this_data['skill_frame_offset']['z']); }
            elseif ($this_data['skill_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] - ($this_data['skill_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $canvas_offset_data['canvas_offset_z'];  }

            // Collect the target, damage, and recovery options
            $this_target_options = !empty($options['this_skill']->target_options) ? $options['this_skill']->target_options : array();
            $this_damage_options = !empty($options['this_skill']->damage_options) ? $options['this_skill']->damage_options : array();
            $this_recovery_options = !empty($options['this_skill']->recovery_options) ? $options['this_skill']->recovery_options : array();
            $this_results = !empty($options['this_skill']->skill_results) ? $options['this_skill']->skill_results : array();

            // Either way, apply target offsets if they exist and it's this robot using the skill
            if (isset($options['this_skill_target']) && $options['this_skill_target'] == $this_data['robot_id_token']){
                // If any of the co-ordinates are provided, update all
                if (!empty($this_target_options['target_kickback']['x'])
                    || !empty($this_target_options['target_kickback']['y'])
                    || !empty($this_target_options['target_kickback']['z'])){
                    $this_data['canvas_offset_x'] -= $this_target_options['target_kickback']['x'];
                    $this_data['canvas_offset_y'] -= $this_target_options['target_kickback']['y'];
                    $this_data['canvas_offset_z'] -= $this_target_options['target_kickback']['z'];
                }
            }

        }
        // Else if this is a normal attachment, it moves with the robot
        else {

            // Calculate the canvas X offsets using the robot's offset as base
            if ($this_data['skill_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['skill_sprite_size'] * ($this_data['skill_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['skill_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['skill_sprite_size'] * (($this_data['skill_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            if ($this_data['skill_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['skill_sprite_size'] * ($this_data['skill_frame_offset']['y']/100))); }
            elseif ($this_data['skill_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['skill_sprite_size'] * (($this_data['skill_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            if ($this_data['skill_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $this_data['skill_frame_offset']['z']); }
            elseif ($this_data['skill_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($this_data['skill_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

        }

        // Generate the final markup for the canvas skill
        ob_start();

            // Loop through the skill quantity and display sprites
            $canvas_offset_x = $this_data['canvas_offset_x'];
            $canvas_offset_y = $this_data['canvas_offset_y'];
            $canvas_offset_z = $this_data['canvas_offset_z'];
            if (true){

                // Define the rest of the display variables
                //$this_data['skill_image'] = 'images/skills/'.(!empty($this_data['skill_image']) ? $this_data['skill_image'] : $this_data['skill_token']).'/sprite_'.$this_data['skill_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
                if (!preg_match('/^images/i', $this_data['skill_image'])){ $this_data['skill_image'] = 'images/skills/'.$this_data['skill_image'].'/sprite_'.$this_data['skill_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
                $this_data['skill_markup_class'] = 'sprite sprite_skill ';
                $this_data['skill_markup_class'] .= 'sprite_'.$this_data['skill_sprite_size'].'x'.$this_data['skill_sprite_size'].' sprite_'.$this_data['skill_sprite_size'].'x'.$this_data['skill_sprite_size'].'_'.$this_data['skill_frame'].' ';
                $this_data['skill_markup_class'] .= 'skill_status_'.$this_data['skill_status'].' skill_position_'.$this_data['skill_position'].' ';

                $frame_position = is_numeric($this_data['skill_frame']) ? (int)($this_data['skill_frame']) : array_search($this_data['skill_frame'], $this_data['skill_frame_index']);
                if ($frame_position === false){ $frame_position = 0; }
                $frame_background_offset = -1 * ceil(($this_data['skill_sprite_size'] * $frame_position));

                $canvas_offset_x -= 10;
                $canvas_offset_y -= 3;
                $canvas_offset_z -= 1;

                if ($this_data['skill_scale'] !== 1){
                    $this_data['skill_markup_class'] .= 'scaled ';
                }

                $this_data['skill_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
                $this_data['skill_markup_style'] .= 'pointer-events: none; z-index: '.$canvas_offset_z.'; '.$this_data['skill_float'].': '.$canvas_offset_x.'px; bottom: '.$canvas_offset_y.'px; ';
                $this_data['skill_markup_style'] .= 'background-image: url('.$this_data['skill_image'].'); width: '.($this_data['skill_sprite_size'] * $this_data['skill_frame_span']).'px; height: '.$this_data['skill_sprite_size'].'px; background-size: '.$this_data['skill_image_width'].'px '.$this_data['skill_image_height'].'px; ';

                // DEBUG
                //$this_data['skill_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));

                // Display the skill's battle sprite
                echo '<div '.
                    'data-skill-id="'.$this_data['skill_id_token'].'" '.
                    'data-robot-id="'.$robot_data['robot_id_token'].'" '.
                    'class="'.($this_data['skill_markup_class'].$this_data['skill_frame_classes']).'" '.
                    'style="'.($this_data['skill_markup_style'].$this_data['skill_frame_styles']).'" '.
                    (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' '.
                    'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                    'data-type="'.$this_data['data_type'].'" '.
                    'data-size="'.$this_data['skill_sprite_size'].'" '.
                    'data-direction="'.$this_data['skill_direction'].'" '.
                    'data-frame="'.$this_data['skill_frame'].'" '.
                    'data-animate="'.$this_data['skill_frame_animate'].'" '.
                    'data-position="'.$this_data['skill_position'].'" '.
                    'data-status="'.$this_data['skill_status'].'" '.
                    'data-scale="'.$this_data['skill_scale'].'" '.
                    '></div>';

            }

        // Collect the generated skill markup
        $this_data['skill_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating item canvas variables
    public static function static_item_markup($this_item, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the item data array and populate basic data
        $this_data['item_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'item';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['item_name'] = isset($options['item_name']) ? $options['item_name'] : $this_item->item_name;
        $this_data['item_id'] = $this_item->item_id;
        $this_data['item_title'] = $this_item->item_name;
        $this_data['item_token'] = $this_item->item_token;
        $this_data['item_id_token'] = $this_item->item_id.'_'.$this_item->item_token;
        $this_data['item_image'] = isset($options['item_image']) ? $options['item_image'] : $this_item->item_image;
        $this_data['item_image2'] = isset($options['item_image2']) ? $options['item_image2'] : $this_item->item_image2;
        $this_data['item_status'] = $robot_data['robot_status'];
        $this_data['item_position'] = $robot_data['robot_position'];
        $this_data['item_direction'] = $this_item->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['item_float'] = $robot_data['robot_float'];
        $this_data['item_size'] = ($this_item->item_image_size * 2);
        $this_data['item_frame'] = isset($options['item_frame']) ? $options['item_frame'] : $this_item->item_frame;
        $this_data['item_frame_span'] = isset($options['item_frame_span']) ? $options['item_frame_span'] : $this_item->item_frame_span;
        $this_data['item_frame_index'] = isset($options['item_frame_index']) ? $options['item_frame_index'] : $this_item->item_frame_index;
        if (is_numeric($this_data['item_frame']) && $this_data['item_frame'] >= 0){ $this_data['item_frame'] = str_pad($this_data['item_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['item_frame']) && $this_data['item_frame'] < 0){ $this_data['item_frame'] = ''; }
        $this_data['item_frame_offset'] = isset($options['item_frame_offset']) && is_array($options['item_frame_offset']) ? $options['item_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $animate_frames_array = isset($options['item_frame_animate']) ? $options['item_frame_animate'] : array($this_data['item_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['item_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['item_frame_styles'] = isset($options['item_frame_styles']) ? $options['item_frame_styles'] : $this_item->item_frame_styles;
        $this_data['item_frame_classes'] = isset($options['item_frame_classes']) ? $options['item_frame_classes'] : $this_item->item_frame_classes;

        $this_data['item_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : 1;

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_item->item_image_size * 2);
        $this_data['item_sprite_size'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_width'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_height'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_image_width'] = ceil($this_data['item_scale'] * $zoom_size * 10);
        $this_data['item_image_height'] = ceil($this_data['item_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this static item
        //error_log(PHP_EOL.'static item:'.$this_data['item_token'].' needs canvas_markup_offset()');
        $canvas_offset_data = $this_item->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size'], $this_item->player->counters['robots_total']);

        // Define the item's canvas offset variables
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['item_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['item_sprite_size']) / 2) ; }

        /*
        // Update the canvas offsets using the base data
        $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x'] + $temp_size_diff;
        $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];
        $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];
        */

        // Calculate the canvas X offsets using the robot's offset as base
        if ($this_data['item_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['x']/100))) + $temp_size_diff; }
        elseif ($this_data['item_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
        else { $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x'] + $temp_size_diff; }
        // Calculate the canvas Y offsets using the robot's offset as base
        if ($this_data['item_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] + ($this_data['item_sprite_size'] * ($this_data['item_frame_offset']['y']/100))); }
        elseif ($this_data['item_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] - ($this_data['item_sprite_size'] * (($this_data['item_frame_offset']['y'] * -1)/100))); }
        else { $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];  }
        // Calculate the canvas Z offsets using the robot's offset as base
        if ($this_data['item_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] + $this_data['item_frame_offset']['z']); }
        elseif ($this_data['item_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] - ($this_data['item_frame_offset']['z'] * -1)); }
        else { $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];  }

        // Define the rest of the display variables
        if (!preg_match('/^images/i', $this_data['item_image'])){ $this_data['item_image_path'] = 'images/items/'.$this_data['item_image'].'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        else { $this_data['item_image_path'] = $this_data['item_image']; }
        $this_data['item_markup_class'] = 'sprite sprite_item ';
        $this_data['item_markup_class'] .= 'sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].' sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].'_'.$this_data['item_frame'].' ';
        $this_data['item_markup_class'] .= 'item_status_'.$this_data['item_status'].' item_position_'.$this_data['item_position'].' ';
        $frame_position = is_numeric($this_data['item_frame']) ? (int)($this_data['item_frame']) : array_search($this_data['item_frame'], $this_data['item_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['item_sprite_size'] * $frame_position));
        $this_data['item_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['item_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['item_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image_path'].'); width: '.($this_data['item_sprite_size'] * $this_data['item_frame_span']).'px; height: '.$this_data['item_sprite_size'].'px; background-size: '.$this_data['item_image_width'].'px '.$this_data['item_image_height'].'px; ';

        // Generate the final markup for the canvas item
        ob_start();

            // Display the item's battle sprite
            $temp_markup = '<div '.
                'data-item-id="'.$this_data['item_id_token'].'" '.
                'class="'.($this_data['item_markup_class'].$this_data['item_frame_classes']).'" '.
                'style="'.($this_data['item_markup_style'].$this_data['item_frame_styles']).'" '.
                (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').
                'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                'data-type="'.$this_data['data_type'].'" '.
                'data-size="'.$this_data['item_sprite_size'].'" '.
                'data-direction="'.$this_data['item_direction'].'" '.
                'data-frame="'.$this_data['item_frame'].'" '.
                'data-animate="'.$this_data['item_frame_animate'].'" '.
                'data-position="'.$this_data['item_position'].'" '.
                'data-status="'.$this_data['item_status'].'" '.
                'data-scale="'.$this_data['item_scale'].'"'.
                '></div>';
            if (!empty($this_data['item_image2'])){ $temp_markup .= str_replace('/'.$this_data['item_image'].'/', '/'.$this_data['item_image2'].'/', $temp_markup); }
            echo $temp_markup;

        // Collect the generated item markup
        $this_data['item_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating object canvas variables
    public static function object_markup($this_object, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the object data array and populate basic data
        $this_data['object_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'object';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['object_name'] = isset($options['object_name']) ? $options['object_name'] : $this_object->object_name;
        $this_data['object_id'] = $this_object->object_id;
        $this_data['object_title'] = $this_object->object_name;
        $this_data['object_token'] = $this_object->object_token;
        $this_data['object_id_token'] = $this_object->object_id.'_'.$this_object->object_token;
        $this_data['object_image'] = isset($options['object_image']) ? $options['object_image'] : $this_object->object_image;
        $this_data['object_status'] = $robot_data['robot_status'];
        $this_data['object_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['object_direction'] = $this_object->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['object_float'] = $robot_data['robot_float'];
        $this_data['object_size'] = ($this_object->object_image_size * 2);
        $this_data['object_frame'] = isset($options['object_frame']) ? $options['object_frame'] : $this_object->object_frame;
        $this_data['object_frame_span'] = isset($options['object_frame_span']) ? $options['object_frame_span'] : $this_object->object_frame_span;
        $this_data['object_frame_index'] = isset($options['object_frame_index']) ? $options['object_frame_index'] : $this_object->object_frame_index;
        if (is_numeric($this_data['object_frame']) && $this_data['object_frame'] >= 0){ $this_data['object_frame'] = str_pad($this_data['object_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['object_frame']) && $this_data['object_frame'] < 0){ $this_data['object_frame'] = ''; }
        //$this_data['object_image'] = 'images/objects/'.(!empty($this_data['object_image']) ? $this_data['object_image'] : $this_data['object_token']).'/sprite_'.$this_data['object_direction'].'_'.$this_data['object_size'].'x'.$this_data['object_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['object_frame_offset'] = isset($options['object_frame_offset']) && is_array($options['object_frame_offset']) ? $options['object_frame_offset'] : $this_object->object_frame_offset;
        $animate_frames_array = isset($options['object_frame_animate']) ? $options['object_frame_animate'] : array($this_data['object_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['object_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['object_frame_styles'] = isset($options['object_frame_styles']) ? $options['object_frame_styles'] : $this_object->object_frame_styles;
        $this_data['object_frame_classes'] = isset($options['object_frame_classes']) ? $options['object_frame_classes'] : $this_object->object_frame_classes;

        $this_data['object_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : 1;

        // DEBUG
        //$this_object->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_object_target\'] = '.$options['this_object_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_object->object_image_size * 2);
        $this_data['object_sprite_size'] = ceil($this_data['object_scale'] * $zoom_size);
        $this_data['object_sprite_width'] = ceil($this_data['object_scale'] * $zoom_size);
        $this_data['object_sprite_height'] = ceil($this_data['object_scale'] * $zoom_size);
        $this_data['object_image_width'] = ceil($this_data['object_scale'] * $zoom_size * 10);
        $this_data['object_image_height'] = ceil($this_data['object_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this static ability
        //error_log(PHP_EOL.'static ability:'.$this_data['ability_token'].' needs canvas_markup_offset()');
        $canvas_offset_data = $this_object->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size'], $this_object->player->counters['robots_total']);
        //$this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        //$this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        //$this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];

        // Define the object's canvas offset variables
        //$temp_size_diff = $robot_data['robot_sprite_size'] != $object_data['object_sprite_size'] ? ceil(($robot_data['robot_sprite_size'] - $object_data['object_sprite_size']) * 0.5) : ceil($object_data['object_sprite_size'] * 0.25);
        //$temp_size_diff = $robot_data['robot_sprite_size'] > 80 ? ceil(($robot_data['robot_sprite_size'] - 80) / 2) : 0;
        //if ($temp_size_diff > 0 && $robot_data['robot_position'] != 'active'){ $temp_size_diff += floor($this_data['object_scale'] * $this_data['object_sprite_size'] * 0.5); }
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['object_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['object_sprite_size']) / 2) ; }
        //$temp_size_diff = floor(($temp_size_diff * 2) + ($temp_size_diff * $robot_data['robot_scale']));

        // If this is a STICKY attachedment, make sure it doesn't move with the robot
        if ($this_data['data_sticky'] != false){

            //$this_data['data_sticky'] = 'true';

            // Calculate the canvas X offsets using the robot's position as base
            if ($this_data['object_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] + ($this_data['object_sprite_size'] * ($this_data['object_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['object_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] - ($this_data['object_sprite_size'] * (($this_data['object_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $canvas_offset_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's position as base
            if ($this_data['object_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] + ($this_data['object_sprite_size'] * ($this_data['object_frame_offset']['y']/100))); }
            elseif ($this_data['object_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] - ($this_data['object_sprite_size'] * (($this_data['object_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $canvas_offset_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's position as base
            if ($this_data['object_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] + $this_data['object_frame_offset']['z']); }
            elseif ($this_data['object_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] - ($this_data['object_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $canvas_offset_data['canvas_offset_z'];  }

            // Collect the target, damage, and recovery options
            $this_target_options = !empty($options['this_object']->target_options) ? $options['this_object']->target_options : array();
            $this_damage_options = !empty($options['this_object']->damage_options) ? $options['this_object']->damage_options : array();
            $this_recovery_options = !empty($options['this_object']->recovery_options) ? $options['this_object']->recovery_options : array();
            $this_results = !empty($options['this_object']->object_results) ? $options['this_object']->object_results : array();

            // Either way, apply target offsets if they exist and it's this robot using the object
            if (isset($options['this_object_target']) && $options['this_object_target'] == $this_data['robot_id_token']){
                // If any of the co-ordinates are provided, update all
                if (!empty($this_target_options['target_kickback']['x'])
                    || !empty($this_target_options['target_kickback']['y'])
                    || !empty($this_target_options['target_kickback']['z'])){
                    $this_data['canvas_offset_x'] -= $this_target_options['target_kickback']['x'];
                    $this_data['canvas_offset_y'] -= $this_target_options['target_kickback']['y'];
                    $this_data['canvas_offset_z'] -= $this_target_options['target_kickback']['z'];
                }
            }

        }
        // Else if this is a normal attachment, it moves with the robot
        else {

            // Calculate the canvas X offsets using the robot's offset as base
            if ($this_data['object_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['object_sprite_size'] * ($this_data['object_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['object_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['object_sprite_size'] * (($this_data['object_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            if ($this_data['object_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['object_sprite_size'] * ($this_data['object_frame_offset']['y']/100))); }
            elseif ($this_data['object_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['object_sprite_size'] * (($this_data['object_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            if ($this_data['object_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $this_data['object_frame_offset']['z']); }
            elseif ($this_data['object_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($this_data['object_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

        }

        // Generate the final markup for the canvas object
        ob_start();

            // Loop through the object quantity and display sprites
            $canvas_offset_x = $this_data['canvas_offset_x'];
            $canvas_offset_y = $this_data['canvas_offset_y'];
            $canvas_offset_z = $this_data['canvas_offset_z'];

            // Define the rest of the display variables
            //$this_data['object_image'] = 'images/objects/'.(!empty($this_data['object_image']) ? $this_data['object_image'] : $this_data['object_token']).'/sprite_'.$this_data['object_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
            if (!preg_match('/^images/i', $this_data['object_image'])){ $this_data['object_image'] = 'images/objects/'.$this_data['object_image'].'/sprite_'.$this_data['object_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
            $this_data['object_markup_class'] = 'sprite sprite_object ';
            $this_data['object_markup_class'] .= 'sprite_'.$this_data['object_sprite_size'].'x'.$this_data['object_sprite_size'].' sprite_'.$this_data['object_sprite_size'].'x'.$this_data['object_sprite_size'].'_'.$this_data['object_frame'].' ';
            $this_data['object_markup_class'] .= 'object_status_'.$this_data['object_status'].' object_position_'.$this_data['object_position'].' ';

            $frame_position = is_numeric($this_data['object_frame']) ? (int)($this_data['object_frame']) : array_search($this_data['object_frame'], $this_data['object_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $frame_background_offset = -1 * ceil(($this_data['object_sprite_size'] * $frame_position));


            $this_data['object_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
            $this_data['object_markup_style'] .= 'pointer-events: none; z-index: '.$canvas_offset_z.'; '.$this_data['object_float'].': '.$canvas_offset_x.'px; bottom: '.$canvas_offset_y.'px; ';
            $this_data['object_markup_style'] .= 'background-image: url('.$this_data['object_image'].'); width: '.($this_data['object_sprite_size'] * $this_data['object_frame_span']).'px; height: '.$this_data['object_sprite_size'].'px; background-size: '.$this_data['object_image_width'].'px '.$this_data['object_image_height'].'px; ';

            // DEBUG
            //$this_data['object_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));

            // Display the object's battle sprite
            echo '<div '.
                    'data-object-id="'.$this_data['object_id_token'].'" '.
                    'data-robot-id="'.$robot_data['robot_id_token'].'" '.
                    'class="'.($this_data['object_markup_class'].$this_data['object_frame_classes']).'" '.
                    'style="'.($this_data['object_markup_style'].$this_data['object_frame_styles']).'" '.
                    (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' '.
                    'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                    'data-type="'.$this_data['data_type'].'" '.
                    'data-size="'.$this_data['object_sprite_size'].'" '.
                    'data-direction="'.$this_data['object_direction'].'" '.
                    'data-frame="'.$this_data['object_frame'].'" '.
                    'data-animate="'.$this_data['object_frame_animate'].'" '.
                    'data-position="'.$this_data['object_position'].'" '.
                    'data-status="'.$this_data['object_status'].'" '.
                    'data-scale="'.$this_data['object_scale'].'" '.
                    '></div>';

        // Collect the generated object markup
        $this_data['object_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a static function for generating object canvas variables
    public static function static_object_markup($this_object, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the object data array and populate basic data
        $this_data['object_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'object';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['object_name'] = isset($options['object_name']) ? $options['object_name'] : $this_object->object_name;
        $this_data['object_id'] = $this_object->object_id;
        $this_data['object_title'] = $this_object->object_name;
        $this_data['object_token'] = $this_object->object_token;
        $this_data['object_id_token'] = $this_object->object_id.'_'.$this_object->object_token;
        $this_data['object_image'] = isset($options['object_image']) ? $options['object_image'] : $this_object->object_image;
        $this_data['object_image2'] = isset($options['object_image2']) ? $options['object_image2'] : $this_object->object_image2;
        $this_data['object_status'] = $robot_data['robot_status'];
        $this_data['object_position'] = $robot_data['robot_position'];
        $this_data['object_direction'] = $this_object->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['object_float'] = $robot_data['robot_float'];
        $this_data['object_size'] = ($this_object->object_image_size * 2);
        $this_data['object_frame'] = isset($options['object_frame']) ? $options['object_frame'] : $this_object->object_frame;
        $this_data['object_frame_span'] = isset($options['object_frame_span']) ? $options['object_frame_span'] : $this_object->object_frame_span;
        $this_data['object_frame_index'] = isset($options['object_frame_index']) ? $options['object_frame_index'] : $this_object->object_frame_index;
        if (is_numeric($this_data['object_frame']) && $this_data['object_frame'] >= 0){ $this_data['object_frame'] = str_pad($this_data['object_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['object_frame']) && $this_data['object_frame'] < 0){ $this_data['object_frame'] = ''; }
        $this_data['object_frame_offset'] = isset($options['object_frame_offset']) && is_array($options['object_frame_offset']) ? $options['object_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $animate_frames_array = isset($options['object_frame_animate']) ? $options['object_frame_animate'] : array($this_data['object_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['object_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['object_frame_styles'] = isset($options['object_frame_styles']) ? $options['object_frame_styles'] : $this_object->object_frame_styles;
        $this_data['object_frame_classes'] = isset($options['object_frame_classes']) ? $options['object_frame_classes'] : $this_object->object_frame_classes;

        $this_data['object_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : 1;

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_object->object_image_size * 2);
        $this_data['object_sprite_size'] = ceil($this_data['object_scale'] * $zoom_size);
        $this_data['object_sprite_width'] = ceil($this_data['object_scale'] * $zoom_size);
        $this_data['object_sprite_height'] = ceil($this_data['object_scale'] * $zoom_size);
        $this_data['object_image_width'] = ceil($this_data['object_scale'] * $zoom_size * 10);
        $this_data['object_image_height'] = ceil($this_data['object_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this object
        //error_log(PHP_EOL.'static object:'.$this_data['object_token'].' needs canvas_markup_offset()');
        $canvas_offset_data = $this_object->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size'], $this_object->player->counters['robots_total']);

        // Define the object's canvas offset variables
        $temp_size_diff = 0;
        if ($robot_data['robot_sprite_size'] != $this_data['object_sprite_size']){ $temp_size_diff = ceil(($robot_data['robot_sprite_size'] - $this_data['object_sprite_size']) / 2) ; }

        /*
        // Update the canvas offsets using the base data
        $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x'] + $temp_size_diff;
        $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];
        $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];
        */

        // Calculate the canvas X offsets using the robot's offset as base
        if ($this_data['object_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] + ($this_data['object_sprite_size'] * ($this_data['object_frame_offset']['x']/100))) + $temp_size_diff; }
        elseif ($this_data['object_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_base_offset_x'] - ($this_data['object_sprite_size'] * (($this_data['object_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
        else { $this_data['canvas_offset_x'] = $robot_data['canvas_base_offset_x'] + $temp_size_diff; }
        // Calculate the canvas Y offsets using the robot's offset as base
        if ($this_data['object_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] + ($this_data['object_sprite_size'] * ($this_data['object_frame_offset']['y']/100))); }
        elseif ($this_data['object_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_base_offset_y'] - ($this_data['object_sprite_size'] * (($this_data['object_frame_offset']['y'] * -1)/100))); }
        else { $this_data['canvas_offset_y'] = $robot_data['canvas_base_offset_y'];  }
        // Calculate the canvas Z offsets using the robot's offset as base
        if ($this_data['object_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] + $this_data['object_frame_offset']['z']); }
        elseif ($this_data['object_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_base_offset_z'] - ($this_data['object_frame_offset']['z'] * -1)); }
        else { $this_data['canvas_offset_z'] = $robot_data['canvas_base_offset_z'];  }

        // Define the rest of the display variables
        if (!preg_match('/^images/i', $this_data['object_image'])){ $this_data['object_image_path'] = 'images/objects/'.$this_data['object_image'].'/sprite_'.$this_data['object_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        else { $this_data['object_image_path'] = $this_data['object_image']; }
        $this_data['object_markup_class'] = 'sprite sprite_object ';
        $this_data['object_markup_class'] .= 'sprite_'.$this_data['object_sprite_size'].'x'.$this_data['object_sprite_size'].' sprite_'.$this_data['object_sprite_size'].'x'.$this_data['object_sprite_size'].'_'.$this_data['object_frame'].' ';
        $this_data['object_markup_class'] .= 'object_status_'.$this_data['object_status'].' object_position_'.$this_data['object_position'].' ';
        $frame_position = is_numeric($this_data['object_frame']) ? (int)($this_data['object_frame']) : array_search($this_data['object_frame'], $this_data['object_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['object_sprite_size'] * $frame_position));
        $this_data['object_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['object_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['object_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['object_markup_style'] .= 'background-image: url('.$this_data['object_image_path'].'); width: '.($this_data['object_sprite_size'] * $this_data['object_frame_span']).'px; height: '.$this_data['object_sprite_size'].'px; background-size: '.$this_data['object_image_width'].'px '.$this_data['object_image_height'].'px; ';

        // Generate the final markup for the canvas object
        ob_start();

            // Display the object's battle sprite
            $temp_markup = '<div '.
                'data-object-id="'.$this_data['object_id_token'].'" '.
                'class="'.($this_data['object_markup_class'].$this_data['object_frame_classes']).'" '.
                'style="'.($this_data['object_markup_style'].$this_data['object_frame_styles']).'" '.
                (!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').
                'data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" '.
                'data-type="'.$this_data['data_type'].'" '.
                'data-size="'.$this_data['object_sprite_size'].'" '.
                'data-direction="'.$this_data['object_direction'].'" '.
                'data-frame="'.$this_data['object_frame'].'" '.
                'data-animate="'.$this_data['object_frame_animate'].'" '.
                'data-position="'.$this_data['object_position'].'" '.
                'data-status="'.$this_data['object_status'].'" '.
                'data-scale="'.$this_data['object_scale'].'"'.
                '></div>';
            if (!empty($this_data['object_image2'])){ $temp_markup .= str_replace('/'.$this_data['object_image'].'/', '/'.$this_data['object_image2'].'/', $temp_markup); }
            echo $temp_markup;

        // Collect the generated object markup
        $this_data['object_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating canvas scene markup
    public static function battle_markup($this_battle, $eventinfo, $options = array()){
        //error_log('rpg_canvas::battle_markup() w/ $options ='.print_r(array_filter($options, function($k){ return !strstr($k, 'this_'); }, ARRAY_FILTER_USE_KEY), true));

        // Define the console markup string
        $this_markup = '';
        $this_underlay_markup = '';
        $this_overlay_markup = '';

        // Define the results type we'll be working with
        $results_type = false;
        if (!empty($options['this_item']) || !empty($options['this_item_results'])){
            $results_type = 'item';
        } elseif (!empty($options['this_ability']) || !empty($options['this_ability_results'])){
            $results_type = 'ability';
        } elseif (!empty($options['this_skill']) || !empty($options['this_skill_results'])){
            $results_type = 'skill';
        }

        // If this robot was not provided or allowed by the function
        if (empty($eventinfo['this_player']) || empty($eventinfo['this_robot']) || $options['canvas_show_this'] == false){

            // Set both this player and robot to false
            $eventinfo['this_player'] = false;
            $eventinfo['this_robot'] = false;

            // Collect the target player ID if set
            $target_player_id = !empty($eventinfo['target_player']) ? $eventinfo['target_player']->player_id : false;

            // Loop through the players index looking for this player
            foreach ($this_battle->values['players'] AS $this_player_id => $this_playerinfo){
                if (empty($target_player_id) || $target_player_id != $this_player_id){
                    $eventinfo['this_player'] = rpg_game::get_player($this_battle, $this_playerinfo);
                    break;
                }
            }

            // Now loop through this player's robots looking for an active one
            foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){
                if ($this_robotinfo['robot_position'] == 'active' && $this_robotinfo['robot_status'] != 'disabled'){
                    $eventinfo['this_robot'] = rpg_game::get_robot($this_battle, $eventinfo['this_player'], $this_robotinfo);
                    break;
                }
            }

        }

        // If this robot was targetting itself, set the target to false
        if (!empty($eventinfo['this_robot']) && !empty($eventinfo['target_robot'])){
            if ($eventinfo['this_robot']->robot_id == $eventinfo['target_robot']->robot_id
                || ($eventinfo['this_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID)
                || ($eventinfo['this_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID)
                ){
                $eventinfo['target_robot'] = array();
            }
        }

        // If the target robot was not provided or allowed by the function
        if (empty($eventinfo['target_player']) || empty($eventinfo['target_robot']) || $options['canvas_show_target'] == false){

            // Set both this player and robot to false
            $eventinfo['target_player'] = false;
            $eventinfo['target_robot'] = false;

            // Collect this player ID if set
            $this_player_id = !empty($eventinfo['this_player']) ? $eventinfo['this_player']->player_id : false;

            // Loop through the players index looking for this player
            if (!empty($this_battle->values['players'])){
                foreach ($this_battle->values['players'] AS $target_player_id => $target_playerinfo){
                    if (empty($this_player_id) || $this_player_id != $target_player_id){
                        $eventinfo['target_player'] = rpg_game::get_player($this_battle, $target_playerinfo);
                        break;
                    }
                }
            }

            // Now loop through the target player's robots looking for an active one
            if (!empty($eventinfo['target_player']->player_robots)){
                foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){
                    if ($target_robotinfo['robot_position'] == 'active' && $target_robotinfo['robot_status'] != 'disabled'){
                        $eventinfo['target_robot'] = rpg_game::get_robot($this_battle, $eventinfo['target_player'], $target_robotinfo);
                        break;
                    }
                }
            }

        }

        // -- PLAYER SPRITES -- //

        // Collect this player's markup data
        $this_player_data = !empty($eventinfo['this_player']) ? $eventinfo['this_player']->canvas_markup($options) : array();

        // Append this player's markup to the main markup array
        $this_markup .= isset($this_player_data['player_markup']) ? $this_player_data['player_markup'] : '';

        // -- PLAYER ROBOT SPRITES -- //

        // Loop through and display this player's robots
        if ($options['canvas_show_this_robots'] && !empty($eventinfo['this_player']->player_robots)){

            // Count the number of robots on this side of the field
            $num_player_robots = count($eventinfo['this_player']->player_robots);

            // Loop through each of this player's robots and generate it's markup
            foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){

                // Collect the robot and canvas options
                $this_robot = rpg_game::get_robot($this_battle, $eventinfo['this_player'], $this_robotinfo);
                $this_options = $options;

                //if ($this_robot->robot_status == 'disabled' && $this_robot->robot_position == 'bench'){ continue; }
                if (!empty($this_robot->flags['hidden'])){ continue; }
                elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id != $this_robot->robot_id){ $this_options['this_'.$results_type] = false; }
                elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id == $this_robot->robot_id && $options['canvas_show_this'] != false){ $this_robot->robot_frame =  $eventinfo['this_robot']->robot_frame; }
                $this_robot->robot_key = $this_robot->robot_key !== false ? $this_robot->robot_key : ($this_key > 0 ? $this_key : $num_player_robots);
                $this_robot_data = $this_robot->canvas_markup($this_options, $this_player_data);
                $this_robot_id_token = $this_robot_data['robot_id'].'_'.$this_robot_data['robot_token'];

                // Check to see if this robot has any camera action styles applied
                $this_camera_action_styles = '';
                $this_camera_has_action = rpg_canvas::has_camera_action(array(
                    'token' => $this_robot->robot_token,
                    'side' => $this_robot->player->player_side,
                    'position' => $this_robot->robot_position,
                    'key' => $this_robot->robot_key
                    ), $options, $this_camera_action_styles);

                // RESULTS ANIMATION STUFF
                if (!empty($results_type)
                    && !empty($this_options['this_'.$results_type.'_results'])
                    && $this_options['this_'.$results_type.'_target'] == $this_robot_id_token
                    ){

                    /*
                     * ABILITY/ITEM/SKILL EFFECT OFFSETS
                     * Frame 01 : Energy +
                     * Frame 02 : Energy -
                     * Frame 03 : Attack +
                     * Frame 04 : Attack -
                     * Frame 05 : Defense +
                     * Frame 06 : Defense -
                     * Frame 07 : Speed +
                     * Frame 08 : Speed -
                     */

                    // Define the results data array and populate with basic fields
                    $this_results_data = array();
                    $this_results_data['results_amount_markup'] = '';
                    $this_results_data['results_effect_markup'] = '';

                    // Calculate the results effect canvas offsets
                    $this_results_data['canvas_offset_x'] = ceil($this_robot_data['canvas_offset_x'] - (4 * $this_options['this_'.$results_type.'_results']['total_actions']));
                    $this_results_data['canvas_offset_y'] = ceil($this_robot_data['canvas_offset_y'] + 0);
                    $this_results_data['canvas_offset_z'] = ceil($this_robot_data['canvas_offset_z'] - 20);
                    $temp_size_diff = $this_robot_data['robot_size'] > 80 ? ceil(($this_robot_data['robot_size'] - 80) * 0.5) : 0;
                    $this_results_data['canvas_offset_x'] += $temp_size_diff;
                    if ($this_robot_data['robot_position'] == 'bench' && $this_robot_data['robot_size'] > 80){
                        //$this_results_data['canvas_offset_x'] += ceil($this_robot_data['robot_size'] / 2);
                        $this_results_data['canvas_offset_x'] += ($this_robot_data['robot_size'] - 40);
                    }


                    // Define the style and class variables for these results
                    $base_image_size = 40;
                    $this_results_data[$results_type.'_size'] = $this_robot_data['robot_position'] == 'active' ? ($base_image_size * 2) : $base_image_size;
                    $this_results_data[$results_type.'_scale'] = isset($this_robot_data['robot_scale']) ? $this_robot_data['robot_scale'] : ($this_robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_robot_data['robot_key']) / 8) * 0.5));
                    $zoom_size = $base_image_size * 2;
                    $this_results_data[$results_type.'_sprite_size'] = ceil($this_results_data[$results_type.'_scale'] * $zoom_size);
                    $this_results_data[$results_type.'_sprite_width'] = ceil($this_results_data[$results_type.'_scale'] * $zoom_size);
                    $this_results_data[$results_type.'_sprite_height'] = ceil($this_results_data[$results_type.'_scale'] * $zoom_size);
                    $this_results_data[$results_type.'_image_width'] = ceil($this_results_data[$results_type.'_scale'] * $zoom_size * 10);
                    $this_results_data[$results_type.'_image_height'] = ceil($this_results_data[$results_type.'_scale'] * $zoom_size);

                    $this_results_data['results_amount_class'] = 'sprite ';
                    $this_results_data['results_amount_canvas_opacity'] = 1.00;

                    // Results Amount Window

                    // Define the hard-coded size for the results window graphic
                    // with name like 'battle-scene_robot-results.png'
                    $results_amount_width = 52;
                    $results_amount_height = 41;

                    // Vertically position the result window as a static value slightly higher than the robot
                    $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 35;

                    // Horizontally position the result window by centering it to the robot first, then raising it half the sprite height
                    // Shift the window in front or behind the robot based on active vs bench status so it stays within view
                    $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] + 0;
                    $this_results_data['results_amount_canvas_offset_x'] += ceil($this_robot_data['robot_sprite_size'] / 2);
                    $this_results_data['results_amount_canvas_offset_x'] -= ceil($results_amount_width / 2);
                    if ($this_robot_data['robot_position'] == 'active'){ $this_results_data['results_amount_canvas_offset_x'] -= 35; }
                    else if ($this_robot_data['robot_position'] == 'bench'){ $this_results_data['results_amount_canvas_offset_x'] += 35; }

                    // Bring the result window to the front of the robot to prevent text from being overlapped by the sprite
                    $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 100;

                    // Result Effect Graphic

                    // Randomly jitter the position of the effect graphic a bit in case multiple show
                    // up one after another so they don't look so static and boring
                    if ($this_robot_data['robot_position'] !== 'bench'){
                        $this_results_data['canvas_offset_x'] += mt_rand(-5, 5); //jitter
                        $this_results_data['canvas_offset_y'] += mt_rand(-5, 5); //jitter
                    }

                    // Now bring it all together to form the result amount style
                    $this_results_data['results_amount_style'] = 'bottom: '.$this_results_data['results_amount_canvas_offset_y'].'px; '.$this_robot_data['robot_float'].': '.$this_results_data['results_amount_canvas_offset_x'].'px; z-index: '.$this_results_data['results_amount_canvas_offset_z'].'; opacity: '.$this_results_data['results_amount_canvas_opacity'].'; ';

                    // Now bring it all together to form the result effect style and class
                    $this_results_data['results_effect_class'] = 'sprite sprite_'.$this_results_data[$results_type.'_sprite_size'].'x'.$this_results_data[$results_type.'_sprite_size'].' '.$results_type.'_status_active '.$results_type.'_position_active ';
                    $this_results_data['results_effect_style'] = 'z-index: '.$this_results_data['canvas_offset_z'].'; '.$this_robot_data['robot_float'].': '.$this_results_data['canvas_offset_x'].'px; bottom: '.$this_results_data['canvas_offset_y'].'px; background-image: url(images/abilities/_effects/stat-arrows/sprite_'.$this_robot_data['robot_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';

                    // Ensure a damage/recovery trigger has been sent and actual damage/recovery was done
                    if (!empty($this_options['this_'.$results_type.'_results']['this_amount'])
                        && in_array($this_options['this_'.$results_type.'_results']['trigger_kind'], array('damage', 'recovery'))){

                        // Define the results effect index
                        $this_results_data['results_effect_index'] = array();
                        // Check if the results effect index was already generated
                        if (!empty($this_battle->index['results_effects'])){
                            // Collect the results effect index from the battle index
                            $this_results_data['results_effect_index'] = $this_battle->index['results_effects'];
                        }
                        // Otherwise, generate the results effect index
                        else {
                            // Define the results effect index for quick programatic lookups
                            $this_results_data['results_effect_index']['recovery']['energy'] = '00';
                            $this_results_data['results_effect_index']['damage']['energy'] = '01';
                            $this_results_data['results_effect_index']['recovery']['attack'] = '02';
                            $this_results_data['results_effect_index']['damage']['attack'] = '03';
                            $this_results_data['results_effect_index']['recovery']['defense'] = '04';
                            $this_results_data['results_effect_index']['damage']['defense'] = '05';
                            $this_results_data['results_effect_index']['recovery']['speed'] = '06';
                            $this_results_data['results_effect_index']['damage']['speed'] = '07';
                            $this_results_data['results_effect_index']['recovery']['weapons'] = '04';
                            $this_results_data['results_effect_index']['damage']['weapons'] = '05';
                            $this_results_data['results_effect_index']['recovery']['experience'] = '10';
                            $this_results_data['results_effect_index']['damage']['experience'] = '10';
                            $this_results_data['results_effect_index']['recovery']['level'] = '10';
                            $this_results_data['results_effect_index']['damage']['level'] = '10';
                            $this_battle->index['results_effects'] = $this_results_data['results_effect_index'];
                        }

                        // Check if a damage trigger was sent with the object results
                        if ($this_options['this_'.$results_type.'_results']['trigger_kind'] == 'damage'){

                            // Collect details about the damage before we add it to the markup
                            $damage_kind = $this_options['this_'.$results_type.'_results']['damage_kind'];
                            $damage_amount = $this_options['this_'.$results_type.'_results']['this_amount'];
                            if (in_array($damage_kind, array('attack', 'defense', 'speed', 'level'))){
                                $damage_tier = $damage_amount;
                            } elseif (in_array($damage_kind, array('experience'))){
                                $damage_tier = ceil($damage_amount / 1000);
                            } else {
                                $damage_tier = 1;
                                if (!empty($this_options['this_'.$results_type.'_results']['flag_weakness'])){ $damage_tier += 1; }
                                if (!empty($this_options['this_'.$results_type.'_results']['flag_critical'])){ $damage_tier += 1; }
                                if (!empty($this_options['this_'.$results_type.'_results']['flag_resistance'])){ $damage_tier -= 1; }
                            }

                            // Append the object damage kind to the class
                            $this_results_data['results_amount_class'] .= $results_type.'_damage '.$results_type.'_damage_'.$damage_kind.' ';
                            if ($damage_tier <= 1){ $this_results_data['results_amount_class'] .= $results_type.'_damage_'.$damage_kind.'_low '; }
                            elseif ($damage_tier <= 2){ $this_results_data['results_amount_class'] .= $results_type.'_damage_'.$damage_kind.'_base '; }
                            elseif ($damage_tier >= 3){ $this_results_data['results_amount_class'] .= $results_type.'_damage_'.$damage_kind.'_high '; }
                            $frame_number = $this_results_data['results_effect_index']['damage'][$damage_kind];
                            $frame_int = (int)$frame_number;
                            $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data[$results_type.'_sprite_size']) : 0;
                            $frame_position = $frame_int;
                            if ($frame_position === false){ $frame_position = 0; }
                            $frame_background_offset = -1 * ceil(($this_results_data[$results_type.'_sprite_size'] * $frame_position));
                            $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data[$results_type.'_sprite_size'].'x'.$this_results_data[$results_type.'_sprite_size'].'_'.$frame_number.' ';
                            $this_results_data['results_effect_style'] .= 'width: '.$this_results_data[$results_type.'_sprite_size'].'px; height: '.$this_results_data[$results_type.'_sprite_size'].'px; background-size: '.$this_results_data[$results_type.'_image_width'].'px '.$this_results_data[$results_type.'_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';

                            // Append the final damage results markup to the markup array
                            $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'"><strong>-'.$damage_amount.'</strong></div>';
                            $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'"></div>';

                        }
                        // Check if a recovery trigger was sent with the object results
                        elseif ($this_options['this_'.$results_type.'_results']['trigger_kind'] == 'recovery'){

                            // Collect details about the recovery before we add it to the markup
                            $recovery_kind = $this_options['this_'.$results_type.'_results']['recovery_kind'];
                            $recovery_amount = $this_options['this_'.$results_type.'_results']['this_amount'];
                            if (in_array($recovery_kind, array('attack', 'defense', 'speed', 'level'))){
                                $recovery_tier = $recovery_amount;
                            } elseif (in_array($recovery_kind, array('experience'))){
                                $recovery_tier = ceil($recovery_amount / 1000);
                            } else {
                                $recovery_tier = 1;
                                if (!empty($this_options['this_'.$results_type.'_results']['flag_affinity'])){ $recovery_tier += 1; }
                                if (!empty($this_options['this_'.$results_type.'_results']['flag_critical'])){ $recovery_tier += 1; }
                                if (!empty($this_options['this_'.$results_type.'_results']['flag_resistance'])){ $recovery_tier -= 1; }
                            }

                            // Append the object recovery kind to the class
                            $this_results_data['results_amount_class'] .= $results_type.'_recovery '.$results_type.'_recovery_'.$recovery_kind.' ';
                            if ($recovery_tier <= 1){ $this_results_data['results_amount_class'] .= $results_type.'_recovery_'.$recovery_kind.'_low '; }
                            elseif ($recovery_tier <= 2){ $this_results_data['results_amount_class'] .= $results_type.'_recovery_'.$recovery_kind.'_base '; }
                            elseif ($recovery_tier >= 3){ $this_results_data['results_amount_class'] .= $results_type.'_recovery_'.$recovery_kind.'_high '; }
                            $frame_number = $this_results_data['results_effect_index']['recovery'][$recovery_kind];
                            $frame_int = (int)$frame_number;
                            $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data[$results_type.'_size']) : 0;
                            $frame_position = $frame_int;
                            if ($frame_position === false){ $frame_position = 0; }
                            $frame_background_offset = -1 * ceil(($this_results_data[$results_type.'_sprite_size'] * $frame_position));
                            $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data[$results_type.'_sprite_size'].'x'.$this_results_data[$results_type.'_sprite_size'].'_'.$frame_number.' ';
                            $this_results_data['results_effect_style'] .= 'width: '.$this_results_data[$results_type.'_sprite_size'].'px; height: '.$this_results_data[$results_type.'_sprite_size'].'px; background-size: '.$this_results_data[$results_type.'_image_width'].'px '.$this_results_data[$results_type.'_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';

                            // Append the final recovery results markup to the markup array
                            $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'"><strong>+'.$recovery_amount.'</strong></div>';
                            $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'"></div>';

                        }

                    }

                    // Append this result's markup to the main markup array
                    $this_markup .= $this_results_data['results_amount_markup'];
                    $this_markup .= $this_results_data['results_effect_markup'];

                }

                // ATTACHMENT ANIMATION STUFF
                if (!empty($this_robot->robot_attachments)){

                    // Loop through each attachment and process it
                    foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){

                        // If this is an ability attachment
                        if ($attachment_info['class'] == 'ability'){
                            // Create the temporary ability object using the provided data and generate its markup data
                            $attachment_info['flags']['is_attachment'] = true;
                            if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_token; }
                            $this_ability = rpg_game::get_ability($this_battle, $eventinfo['this_player'], $this_robot, $attachment_info);
                            // Define this ability data array and generate the markup data
                            $this_attachment_options = $this_options;
                            $this_attachment_options['data_sticky'] = !empty($this_options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                            $this_attachment_options['data_type'] = 'attachment';
                            $this_attachment_options['data_debug'] = ''; //$attachment_token;
                            $this_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $this_ability->ability_image;
                            $this_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $this_ability->ability_frame;
                            $this_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $this_ability->ability_frame_span;
                            $this_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $this_ability->ability_frame_animate;
                            if (!empty($this_attachment_options['ability_frame_animate']) && is_array($this_attachment_options['ability_frame_animate'])){ $attachment_frame_count = sizeof($this_attachment_options['ability_frame_animate']); }
                            elseif (!empty($this_attachment_options['ability_frame']) && is_array($this_attachment_options['ability_frame'])){ $attachment_frame_count = sizeof($this_attachment_options['ability_frame']); }
                            else { $attachment_frame_count = 1; }
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($this_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $this_attachment_options['ability_frame'] = $this_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
                            $this_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $this_ability->ability_frame_offset;
                            $this_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $this_ability->ability_frame_styles;
                            $this_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $this_ability->ability_frame_classes;
                            if ($this_camera_action_styles){ $this_attachment_options['ability_frame_styles'] .= $this_camera_action_styles; }
                            $this_ability_data = $this_ability->canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
                            // Append this ability's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
                                $this_markup .= $this_ability_data['ability_markup'];
                            }
                        }
                        // Else if this is an item attachment
                        elseif ($attachment_info['class'] == 'item'){
                            // Create the temporary item object using the provided data and generate its markup data
                            $attachment_info['flags']['is_attachment'] = true;
                            if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_token; }
                            $this_item = rpg_game::get_item($this_battle, $eventinfo['this_player'], $this_robot, $attachment_info);
                            // Define this item data array and generate the markup data
                            $this_attachment_options = $this_options;
                            $this_attachment_options['data_sticky'] = !empty($this_options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                            $this_attachment_options['data_type'] = 'attachment';
                            $this_attachment_options['data_debug'] = ''; //$attachment_token;
                            $this_attachment_options['item_image'] = isset($attachment_info['item_image']) ? $attachment_info['item_image'] : $this_item->item_image;
                            $this_attachment_options['item_frame'] = isset($attachment_info['item_frame']) ? $attachment_info['item_frame'] : $this_item->item_frame;
                            $this_attachment_options['item_frame_span'] = isset($attachment_info['item_frame_span']) ? $attachment_info['item_frame_span'] : $this_item->item_frame_span;
                            $this_attachment_options['item_frame_animate'] = isset($attachment_info['item_frame_animate']) ? $attachment_info['item_frame_animate'] : $this_item->item_frame_animate;
                            $attachment_frame_count = !empty($this_attachment_options['item_frame_animate']) ? sizeof($this_attachment_options['item_frame_animate']) : sizeof($this_attachment_options['item_frame']);
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($this_attachment_options['item_frame_animate'][$attachment_frame_key])){ $this_attachment_options['item_frame'] = $this_attachment_options['item_frame_animate'][$attachment_frame_key]; }
                            $this_attachment_options['item_frame_offset'] = isset($attachment_info['item_frame_offset']) ? $attachment_info['item_frame_offset'] : $this_item->item_frame_offset;
                            $this_attachment_options['item_frame_styles'] = isset($attachment_info['item_frame_styles']) ? $attachment_info['item_frame_styles'] : $this_item->item_frame_styles;
                            $this_attachment_options['item_frame_classes'] = isset($attachment_info['item_frame_classes']) ? $attachment_info['item_frame_classes'] : $this_item->item_frame_classes;
                            if ($this_camera_action_styles){ $this_attachment_options['item_frame_styles'] .= $this_camera_action_styles; }
                            $this_item_data = $this_item->canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
                            // Append this item's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
                                $this_markup .= $this_item_data['item_markup'];
                            }
                        }
                        // Else if this is an skill attachment
                        elseif ($attachment_info['class'] == 'skill'){
                            // Create the temporary skill object using the provided data and generate its markup data
                            $attachment_info['flags']['is_attachment'] = true;
                            if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_token; }
                            $this_skill = rpg_game::get_skill($this_battle, $eventinfo['this_player'], $this_robot, $attachment_info);
                            // Define this skill data array and generate the markup data
                            $this_attachment_options = $this_options;
                            $this_attachment_options['data_sticky'] = !empty($this_options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                            $this_attachment_options['data_type'] = 'attachment';
                            $this_attachment_options['data_debug'] = ''; //$attachment_token;
                            $this_attachment_options['skill_image'] = isset($attachment_info['skill_image']) ? $attachment_info['skill_image'] : $this_skill->skill_image;
                            $this_attachment_options['skill_frame'] = isset($attachment_info['skill_frame']) ? $attachment_info['skill_frame'] : $this_skill->skill_frame;
                            $this_attachment_options['skill_frame_span'] = isset($attachment_info['skill_frame_span']) ? $attachment_info['skill_frame_span'] : $this_skill->skill_frame_span;
                            $this_attachment_options['skill_frame_animate'] = isset($attachment_info['skill_frame_animate']) ? $attachment_info['skill_frame_animate'] : $this_skill->skill_frame_animate;
                            $attachment_frame_count = !empty($this_attachment_options['skill_frame_animate']) ? sizeof($this_attachment_options['skill_frame_animate']) : sizeof($this_attachment_options['skill_frame']);
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($this_attachment_options['skill_frame_animate'][$attachment_frame_key])){ $this_attachment_options['skill_frame'] = $this_attachment_options['skill_frame_animate'][$attachment_frame_key]; }
                            $this_attachment_options['skill_frame_offset'] = isset($attachment_info['skill_frame_offset']) ? $attachment_info['skill_frame_offset'] : $this_skill->skill_frame_offset;
                            $this_attachment_options['skill_frame_styles'] = isset($attachment_info['skill_frame_styles']) ? $attachment_info['skill_frame_styles'] : $this_skill->skill_frame_styles;
                            $this_attachment_options['skill_frame_classes'] = isset($attachment_info['skill_frame_classes']) ? $attachment_info['skill_frame_classes'] : $this_skill->skill_frame_classes;
                            if ($this_camera_action_styles){ $this_attachment_options['skill_frame_styles'] .= $this_camera_action_styles; }
                            $this_skill_data = $this_skill->canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
                            // Append this skill's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
                                $this_markup .= $this_skill_data['skill_markup'];
                            }
                        }
                        // Else if this is an object attachment
                        elseif ($attachment_info['class'] == 'object'){
                            // Create the temporary object object using the provided data and generate its markup data
                            $attachment_info['flags']['is_attachment'] = true;
                            if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_token; }
                            $this_object = rpg_game::get_proto_object($this_battle, $eventinfo['this_player'], $this_robot, $attachment_info);
                            // Define this object data array and generate the markup data
                            $this_attachment_options = $this_options;
                            $this_attachment_options['data_sticky'] = !empty($this_options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                            $this_attachment_options['data_type'] = 'attachment';
                            $this_attachment_options['data_debug'] = ''; //$attachment_token;
                            $this_attachment_options['object_image'] = isset($attachment_info['object_image']) ? $attachment_info['object_image'] : $this_object->object_image;
                            $this_attachment_options['object_frame'] = isset($attachment_info['object_frame']) ? $attachment_info['object_frame'] : $this_object->object_frame;
                            $this_attachment_options['object_frame_span'] = isset($attachment_info['object_frame_span']) ? $attachment_info['object_frame_span'] : $this_object->object_frame_span;
                            $this_attachment_options['object_frame_animate'] = isset($attachment_info['object_frame_animate']) ? $attachment_info['object_frame_animate'] : $this_object->object_frame_animate;
                            $attachment_frame_count = !empty($this_attachment_options['object_frame_animate']) ? sizeof($this_attachment_options['object_frame_animate']) : sizeof($this_attachment_options['object_frame']);
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($this_attachment_options['object_frame_animate'][$attachment_frame_key])){ $this_attachment_options['object_frame'] = $this_attachment_options['object_frame_animate'][$attachment_frame_key]; }
                            $this_attachment_options['object_frame_offset'] = isset($attachment_info['object_frame_offset']) ? $attachment_info['object_frame_offset'] : $this_object->object_frame_offset;
                            $this_attachment_options['object_frame_styles'] = isset($attachment_info['object_frame_styles']) ? $attachment_info['object_frame_styles'] : $this_object->object_frame_styles;
                            $this_attachment_options['object_frame_classes'] = isset($attachment_info['object_frame_classes']) ? $attachment_info['object_frame_classes'] : $this_object->object_frame_classes;
                            if ($this_camera_action_styles){ $this_attachment_options['object_frame_styles'] .= $this_camera_action_styles; }
                            $this_object_data = $this_object->proto_canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
                            // Append this object's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
                                $this_markup .= $this_object_data['object_markup'];
                            }
                        }

                    }

                }

                // ABILITY/ITEM/SKILL ANIMATION STUFF
                //error_log('attempting to showing object animation stuff for '.$results_type.' with option keys '.print_r(array_keys(array_filter($this_options)), true));
                if (!empty($this_options['this_'.$results_type]) && !empty($this_options['canvas_show_this_'.$results_type])){

                    // If this is an ability, collect its markup
                    if ($results_type == 'ability'){

                        // Define the object data array and generate markup data
                        $attachment_options['data_type'] = 'ability';
                        $this_ability_data = $this_options['this_ability']->canvas_markup($this_options, $this_player_data, $this_robot_data);

                        // Display the object's icon sprite
                        if (empty($this_options['this_ability_results']['total_actions'])
                            && empty($this_options['this_ability']->flags['skip_canvas_header'])){
                            $this_icon_image = !empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token;
                            $this_icon_image2 = !empty($this_options['this_ability']->ability_image2) ? $this_options['this_ability']->ability_image2 : '';
                            $this_icon_markup_left = '<div class="sprite ability_icon ability_icon_left" style="background-image: url(images/abilities/'.$this_icon_image.'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
                            $this_icon_markup_right = '<div class="sprite ability_icon ability_icon_right" style="background-image: url(images/abilities/'.$this_icon_image.'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
                            if (!empty($this_options['this_ability']->ability_image2)){
                                $this_icon_markup_left .= str_replace('/'.$this_icon_image.'/', '/'.$this_icon_image2.'/', $this_icon_markup_left);
                                $this_icon_markup_right .= str_replace('/'.$this_icon_image.'/', '/'.$this_icon_image2.'/', $this_icon_markup_right);
                            }
                            $this_icon_markup_combined =  '<div class="'.$this_ability_data['ability_markup_class'].' canvas_ability_details ability_type type type_'.(!empty($this_options['this_ability']->ability_type) ? $this_options['this_ability']->ability_type : 'none').(!empty($this_options['this_ability']->ability_type2) ? '_'.$this_options['this_ability']->ability_type2 : '').'">'.$this_icon_markup_left.'<div class="ability_name">'.$this_ability_data['ability_title'].'</div>'.$this_icon_markup_right.'</div>';
                            $this_underlay_markup .=  $this_icon_markup_combined;
                        }

                        // Append this object's markup to the main markup array
                        $this_markup .= $this_ability_data['ability_markup'];

                    }
                    // Else if this is an item, collect its markup
                    elseif ($results_type == 'item'){

                        // Define the object data array and generate markup data
                        $attachment_options['data_type'] = 'item';
                        $this_item_data = $this_options['this_item']->canvas_markup($this_options, $this_player_data, $this_robot_data);

                        // Display the object's icon sprite
                        if (empty($this_options['this_item_results']['total_actions'])
                            || !empty($this_options['this_item']->flags['force_canvas_header'])){
                            $this_item_label = !empty($this_item_data['item_title']) ? $this_item_data['item_title'] : $this_options['this_item']->item_name;
                            $this_icon_markup_left = '<div class="sprite item_icon item_icon_left" style="background-image: url(images/items/'.(!empty($this_options['this_item']->item_image) ? $this_options['this_item']->item_image : $this_options['this_item']->item_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
                            $this_icon_markup_right = '<div class="sprite item_icon item_icon_right" style="background-image: url(images/items/'.(!empty($this_options['this_item']->item_image) ? $this_options['this_item']->item_image : $this_options['this_item']->item_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');"></div>';
                            $this_icon_markup_combined =  '<div class="item item_sprite canvas_item_details item_type type type_'.(!empty($this_options['this_item']->item_type) ? $this_options['this_item']->item_type : 'none').(!empty($this_options['this_item']->item_type2) ? '_'.$this_options['this_item']->item_type2 : '').'">'.$this_icon_markup_left.'<div class="item_name">'.$this_item_label.'</div>'.$this_icon_markup_right.'</div>';
                            if (isset($this_options['canvas_show_this_item_underlay'])
                                && empty($this_options['canvas_show_this_item_underlay'])
                                && !empty($this_options['canvas_show_this_item_overlay'])){
                                $this_overlay_markup .= $this_icon_markup_combined;
                            } else {
                                $this_underlay_markup .= $this_icon_markup_combined;
                            }
                        }

                        // Append this object's markup to the main markup array
                        $this_markup .= $this_item_data['item_markup'];

                    }
                    // Else if this is an skill, collect its markup
                    elseif ($results_type == 'skill'){

                        // Define the object data array and generate markup data
                        $attachment_options['data_type'] = 'skill';
                        //$this_skill_data = $this_options['this_skill']->canvas_markup($this_options, $this_player_data, $this_robot_data);
                        $this_skill_data = $this_options['this_skill']->export_array();
                        $this_skill_parameters = !empty($this_options['this_skill']->skill_parameters) ? $this_options['this_skill']->skill_parameters : array();

                        // Display the object's icon sprite
                        if (empty($this_options['this_skill_results']['total_actions'])
                            || !empty($this_options['this_skill']->flags['force_canvas_header'])){
                            $this_skill_type = !empty($this_skill_parameters['type']) ? $this_skill_parameters['type'] : '';
                            if (empty($this_skill_type) && !empty($this_options['this_skill']->robot->robot_core)){ $this_skill_type = $this_options['this_skill']->robot->robot_core; }
                            if (empty($this_skill_type)){ $this_skill_type = 'none'; }
                            $this_skill_label = $this_options['this_skill']->skill_name;
                            $this_skill_robot_image = !empty($this_options['this_skill']->robot->robot_image) ? $this_options['this_skill']->robot->robot_image : $this_options['this_skill']->robot->robot_token;
                            $this_skill_robot_image_size = $this_options['this_skill']->robot->robot_image_size.'x'.$this_options['this_skill']->robot->robot_image_size;
                            $this_skill_mugshot = 'images/robots/'.$this_skill_robot_image.'/mug_'.$this_robot_data['robot_direction'].'_'.$this_skill_robot_image_size.'.png?'.MMRPG_CONFIG_CACHE_DATE;
                            $this_icon_markup_left = '<div class="sprite skill_icon skill_icon_'.$this_skill_robot_image_size.' skill_icon_left" style="background-image: url('.$this_skill_mugshot.');"></div>';
                            $this_icon_markup_right = '<div class="sprite skill_icon skill_icon_'.$this_skill_robot_image_size.' skill_icon_right" style="background-image: url('.$this_skill_mugshot.');"></div>';
                            $this_icon_markup_combined = '<div class="sprite skill_sprite canvas_skill_details skill_type type type_'.$this_skill_type.'">'.$this_icon_markup_left.'<div class="skill_name">'.$this_skill_label.'</div>'.$this_icon_markup_right.'</div>';
                            if (isset($this_options['canvas_show_this_skill_underlay'])
                                && empty($this_options['canvas_show_this_skill_underlay'])
                                && !empty($this_options['canvas_show_this_skill_overlay'])){
                                $this_overlay_markup .= $this_icon_markup_combined;
                            } else {
                                $this_underlay_markup .= $this_icon_markup_combined;
                            }
                        }

                    }

                }

                // Append this robot's markup to the main markup array
                $this_markup .= $this_robot_data['robot_markup'];

            }
        }

        // -- TARGET PLAYER SPRITES -- //

        // Collect the target player's markup data
        $target_player_data = !empty($eventinfo['target_player']) ? $eventinfo['target_player']->canvas_markup($options) : array();

        // Append the target player's markup to the main markup array
        $this_markup .= isset($target_player_data['player_markup']) ? $target_player_data['player_markup'] : '';

        // -- TARGET PLAYER ROBOT SPRITES -- //

        // Loop through and display the target player's robots
        if ($options['canvas_show_target_robots'] && !empty($eventinfo['target_player']->player_robots)){

            // Count the number of robots on the target's side of the field
            $num_player_robots = count($eventinfo['target_player']->player_robots);

            // Loop through each of the target player's robot and generate it's markup
            foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){

                // Create the temporary target robot object
                $target_robot = rpg_game::get_robot($this_battle, $eventinfo['target_player'], $target_robotinfo);
                $target_options = $options;

                if (!empty($target_robot->flags['hidden'])){ continue; }
                elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id != $target_robot->robot_id){ $target_options['this_ability'] = false;  }
                elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id == $target_robot->robot_id && $options['canvas_show_target'] != false){ $target_robot->robot_frame =  $eventinfo['target_robot']->robot_frame; }
                $target_robot->robot_key = $target_robot->robot_key !== false ? $target_robot->robot_key : ($target_key > 0 ? $target_key : $num_player_robots);
                $target_robot_data = $target_robot->canvas_markup($target_options, $target_player_data);
                $target_robot_id_token = $target_robot_data['robot_id'].'_'.$target_robot_data['robot_token'];

                // Check to see if this robot has any camera action styles applied
                $target_camera_action_styles = '';
                $target_camera_has_action = rpg_canvas::has_camera_action(array(
                    'token' => $target_robot->robot_token,
                    'side' => $target_robot->player->player_side,
                    'position' => $target_robot->robot_position,
                    'key' => $target_robot->robot_key
                    ), $options, $target_camera_action_styles);

                // ATTACHMENT ANIMATION STUFF
                if (!empty($target_robot->robot_attachments)){

                    // Loop through each attachment and process it
                    foreach ($target_robot->robot_attachments AS $attachment_token => $attachment_info){

                        // If this is an ability attachment
                        if ($attachment_info['class'] == 'ability'){

                            // Create the target's temporary ability object using the provided data
                            $target_ability = rpg_game::get_ability($this_battle, $eventinfo['target_player'], $target_robot, $attachment_info);

                            // Define this ability data array and generate the markup data
                            $target_attachment_options = $target_options;
                            $target_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
                            $target_attachment_options['data_sticky'] = $target_attachment_options['sticky'];
                            $target_attachment_options['data_type'] = 'attachment';
                            $target_attachment_options['data_debug'] = ''; //$attachment_token;
                            $target_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $target_ability->ability_image;
                            $target_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $target_ability->ability_frame;
                            $target_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $target_ability->ability_frame_span;
                            $target_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $target_ability->ability_frame_animate;
                            $attachment_frame_key = 0;
                            $attachment_frame_count = sizeof($target_attachment_options['ability_frame_animate']);
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($attachment_frame_count > 0 && $temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($target_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $target_attachment_options['ability_frame'] = $target_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
                            else { $target_attachment_options['ability_frame'] = 0; }
                            $target_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $target_ability->ability_frame_offset;
                            $target_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $target_ability->ability_frame_styles;
                            $target_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $target_ability->ability_frame_classes;
                            if ($target_camera_action_styles){ $target_attachment_options['ability_frame_styles'] .= $target_camera_action_styles; }
                            $target_ability_data = $target_ability->canvas_markup($target_attachment_options, $target_player_data, $target_robot_data);

                            // Append this target's ability's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $target_robot->robot_frame_styles)){
                                $this_markup .= $target_ability_data['ability_markup'];
                            }

                        }
                        // Else if this is an item attachment
                        elseif ($attachment_info['class'] == 'item'){

                            // Create the target's temporary item object using the provided data
                            $target_item = rpg_game::get_item($this_battle, $eventinfo['target_player'], $target_robot, $attachment_info);

                            // Define this item data array and generate the markup data
                            $target_attachment_options = $target_options;
                            $target_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
                            $target_attachment_options['data_sticky'] = $target_attachment_options['sticky'];
                            $target_attachment_options['data_type'] = 'attachment';
                            $target_attachment_options['data_debug'] = ''; //$attachment_token;
                            $target_attachment_options['item_image'] = isset($attachment_info['item_image']) ? $attachment_info['item_image'] : $target_item->item_image;
                            $target_attachment_options['item_quantity'] = isset($attachment_info['item_quantity']) ? $attachment_info['item_quantity'] : $target_item->item_quantity;
                            $target_attachment_options['item_frame'] = isset($attachment_info['item_frame']) ? $attachment_info['item_frame'] : $target_item->item_frame;
                            $target_attachment_options['item_frame_span'] = isset($attachment_info['item_frame_span']) ? $attachment_info['item_frame_span'] : $target_item->item_frame_span;
                            $target_attachment_options['item_frame_animate'] = isset($attachment_info['item_frame_animate']) ? $attachment_info['item_frame_animate'] : $target_item->item_frame_animate;
                            $attachment_frame_key = 0;
                            $attachment_frame_count = sizeof($target_attachment_options['item_frame_animate']);
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($attachment_frame_count > 0 && $temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($target_attachment_options['item_frame_animate'][$attachment_frame_key])){ $target_attachment_options['item_frame'] = $target_attachment_options['item_frame_animate'][$attachment_frame_key]; }
                            else { $target_attachment_options['item_frame'] = 0; }
                            $target_attachment_options['item_frame_offset'] = isset($attachment_info['item_frame_offset']) ? $attachment_info['item_frame_offset'] : $target_item->item_frame_offset;
                            $target_attachment_options['item_frame_styles'] = isset($attachment_info['item_frame_styles']) ? $attachment_info['item_frame_styles'] : $target_item->item_frame_styles;
                            $target_attachment_options['item_frame_classes'] = isset($attachment_info['item_frame_classes']) ? $attachment_info['item_frame_classes'] : $target_item->item_frame_classes;
                            if ($target_camera_action_styles){ $target_attachment_options['item_frame_styles'] .= $target_camera_action_styles; }
                            $target_item_data = $target_item->canvas_markup($target_attachment_options, $target_player_data, $target_robot_data);

                            // Append this target's item's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $target_robot->robot_frame_styles)){
                                $this_markup .= $target_item_data['item_markup'];
                            }

                        }
                        // If this is an object attachment
                        elseif ($attachment_info['class'] == 'object'
                            && !empty($attachment_info['object_token'])){

                            // Create the target's temporary object object using the provided data
                            $target_object = rpg_game::get_proto_object($this_battle, $eventinfo['target_player'], $target_robot, $attachment_info);

                            // Define this object data array and generate the markup data
                            $target_attachment_options = $target_options;
                            $target_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
                            $target_attachment_options['data_sticky'] = $target_attachment_options['sticky'];
                            $target_attachment_options['data_type'] = 'attachment';
                            $target_attachment_options['data_debug'] = ''; //$attachment_token;
                            $target_attachment_options['object_image'] = isset($attachment_info['object_image']) ? $attachment_info['object_image'] : $target_object->object_image;
                            $target_attachment_options['object_frame'] = isset($attachment_info['object_frame']) ? $attachment_info['object_frame'] : $target_object->object_frame;
                            $target_attachment_options['object_frame_span'] = isset($attachment_info['object_frame_span']) ? $attachment_info['object_frame_span'] : $target_object->object_frame_span;
                            $target_attachment_options['object_frame_animate'] = isset($attachment_info['object_frame_animate']) ? $attachment_info['object_frame_animate'] : $target_object->object_frame_animate;
                            $attachment_frame_key = 0;
                            $attachment_frame_count = sizeof($target_attachment_options['object_frame_animate']);
                            $temp_event_frame = $this_battle->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($attachment_frame_count > 0 && $temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($target_attachment_options['object_frame_animate'][$attachment_frame_key])){ $target_attachment_options['object_frame'] = $target_attachment_options['object_frame_animate'][$attachment_frame_key]; }
                            else { $target_attachment_options['object_frame'] = 0; }
                            $target_attachment_options['object_frame_offset'] = isset($attachment_info['object_frame_offset']) ? $attachment_info['object_frame_offset'] : $target_object->object_frame_offset;
                            $target_attachment_options['object_frame_styles'] = isset($attachment_info['object_frame_styles']) ? $attachment_info['object_frame_styles'] : $target_object->object_frame_styles;
                            $target_attachment_options['object_frame_classes'] = isset($attachment_info['object_frame_classes']) ? $attachment_info['object_frame_classes'] : $target_object->object_frame_classes;
                            if ($target_camera_action_styles){ $target_attachment_options['object_frame_styles'] .= $target_camera_action_styles; }
                            $target_object_data = $target_object->proto_canvas_markup($target_attachment_options, $target_player_data, $target_robot_data);

                            // Append this target's object's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $target_robot->robot_frame_styles)){
                                $this_markup .= $target_object_data['object_markup'];
                            }

                        }

                    }

                }

                $this_markup .= $target_robot_data['robot_markup'];

            }

        }

        // -- FIELD ATTACHMENT SPRITES -- //

        // Check for battle field attachments exist before looping
        if (!empty($this_battle->battle_attachments)){

            // Collect references to the players based on side
            if ($eventinfo['this_player']->player_side === 'left'){
                $left_side_player = $eventinfo['this_player'];
                $right_side_player = $eventinfo['target_player'];
                $left_side_player_data = $this_player_data;
                $right_side_player_data = $target_player_data;

            } elseif ($eventinfo['target_player']->player_side === 'left'){
                $left_side_player = $eventinfo['target_player'];
                $right_side_player = $eventinfo['this_player'];
                $left_side_player_data = $target_player_data;
                $right_side_player_data = $this_player_data;
            }

            // Loop through the battle field attachments so we can display them
            foreach ($this_battle->battle_attachments AS $static_key => $static_attachments){

                // Break apart and collect static attachment info
                $static_info = explode('-', $static_key);
                $static_side = isset($static_info[0]) ? $static_info[0] : false;
                $static_position = isset($static_info[1]) ? $static_info[1] : false;
                $static_key = isset($static_info[2]) ? $static_info[2] : false;

                // Continue if the collected data is not valid
                if ($static_side !== 'left' && $static_side !== 'right'){ continue; }
                if ($static_position === 'bench' && !is_numeric($static_key)){ continue; }

                // Collect references to static player & robot variables based on side
                if ($static_side === 'left'){
                    $static_bench_size = $left_side_player->counters['robots_total'];
                    $static_robot_object = rpg_game::get_robot($this_battle, $left_side_player, array('robot_id' => -1, 'robot_token' => 'robot'));
                } elseif ($static_side === 'right'){
                    $static_bench_size = $right_side_player->counters['robots_total'];
                    $static_robot_object = rpg_game::get_robot($this_battle, $right_side_player, array('robot_id' => -2, 'robot_token' => 'robot'));
                }

                // Loop through all attachments on this side and position
                foreach ($static_attachments AS $attachment_token => $attachment_info){

                    // If a source robot was not provided, we can't do anything (yet?)
                    //if (empty($attachment_info['robot_id'])){ continue; }

                    // Define the pseudo robot data for positioning
                    $static_robot_data = array();
                    $static_robot_data['robot_id'] = 0;
                    $static_robot_data['robot_key'] = $static_key;
                    $static_robot_data['robot_status'] = 'active';
                    $static_robot_data['robot_position'] = $static_position;
                    $static_robot_data['robot_direction'] = $static_side === 'left' ? 'right' : 'left';
                    $static_robot_data['robot_float'] = $static_side;
                    $static_robot_data['robot_size'] = $static_position === 'active' ? ($static_robot_object->robot_image_size * 2) : $static_robot_object->robot_image_size;

                    // Define the rest of the position variables based on above data
                    $static_position_offset = $this_battle->canvas_markup_offset(
                        $static_robot_data['robot_key'],
                        $static_robot_data['robot_position'],
                        $static_robot_data['robot_size'],
                        $static_bench_size
                        );
                    $static_robot_data['canvas_base_offset_x'] = $static_position_offset['canvas_offset_x'];
                    $static_robot_data['canvas_base_offset_y'] = $static_position_offset['canvas_offset_y'];
                    $static_robot_data['canvas_base_offset_z'] = $static_position_offset['canvas_offset_z'];
                    $static_robot_data['robot_scale'] = $static_position_offset['canvas_scale'];
                    $static_robot_data['robot_sprite_size']  = ceil($static_robot_data['robot_scale'] * ($static_robot_object->robot_image_size * 2));

                    // Check if this position has any camera action and collect the styles if so
                    $camera_action_styles = '';
                    $camera_has_action = self::has_camera_action(array(
                        'token' => $attachment_token,
                        'side' => $static_side,
                        'position' => $static_position,
                        'key' => $static_key
                        ), $options, $camera_action_styles);

                    // If this is an ability attachment
                    if ($attachment_info['class'] == 'ability'){

                        // Create the temporary ability object using the provided data and generate its markup data
                        $attachment_info['flags']['is_attachment'] = true;
                        if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_token; }
                        $this_ability = rpg_game::get_ability($this_battle, $left_side_player, $static_robot_object, $attachment_info);
                        $this_ability->update_session();

                        // Define this ability data array and generate the markup data
                        $this_attachment_options = $options;
                        $this_attachment_options['data_sticky'] = !empty($options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                        $this_attachment_options['data_type'] = 'attachment';
                        $this_attachment_options['data_debug'] = ''; //$attachment_token;
                        $this_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $this_ability->ability_image;
                        $this_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $this_ability->ability_frame;
                        $this_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $this_ability->ability_frame_span;
                        $this_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $this_ability->ability_frame_animate;
                        $attachment_frame_count = !empty($this_attachment_options['ability_frame_animate']) ? sizeof($this_attachment_options['ability_frame_animate']) : sizeof($this_attachment_options['ability_frame']);
                        $temp_event_frame = $this_battle->counters['event_frames'];
                        if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                        elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                        elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                        if (isset($this_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $this_attachment_options['ability_frame'] = $this_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
                        $this_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
                        $this_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $this_ability->ability_frame_styles;
                        $this_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $this_ability->ability_frame_classes;
                        if (!empty($camera_action_styles)){ $this_attachment_options['ability_frame_styles'] .= $camera_action_styles; }

                        // Collect and appent static abilty markup to the parent string
                        $this_attachment_data = rpg_canvas::static_ability_markup($this_ability, $this_attachment_options, $left_side_player_data, $static_robot_data);
                        $this_markup .= $this_attachment_data['ability_markup'];

                    }
                    // Else if this is an item attachment
                    elseif ($attachment_info['class'] == 'item'){

                        // Create the temporary item object using the provided data and generate its markup data
                        $attachment_info['flags']['is_attachment'] = true;
                        if (!isset($attachment_info['attachment_token'])){ $attachment_info['attachment_token'] = $attachment_token; }
                        $this_item = rpg_game::get_item($this_battle, $left_side_player, $static_robot_object, $attachment_info);
                        $this_item->update_session();

                        // Define this item data array and generate the markup data
                        $this_attachment_options = $options;
                        $this_attachment_options['data_sticky'] = !empty($options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                        $this_attachment_options['data_type'] = 'attachment';
                        $this_attachment_options['data_debug'] = ''; //$attachment_token;
                        $this_attachment_options['item_image'] = isset($attachment_info['item_image']) ? $attachment_info['item_image'] : $this_item->item_image;
                        $this_attachment_options['item_frame'] = isset($attachment_info['item_frame']) ? $attachment_info['item_frame'] : $this_item->item_frame;
                        $this_attachment_options['item_frame_span'] = isset($attachment_info['item_frame_span']) ? $attachment_info['item_frame_span'] : $this_item->item_frame_span;
                        $this_attachment_options['item_frame_animate'] = isset($attachment_info['item_frame_animate']) ? $attachment_info['item_frame_animate'] : $this_item->item_frame_animate;
                        $attachment_frame_count = !empty($this_attachment_options['item_frame_animate']) ? sizeof($this_attachment_options['item_frame_animate']) : sizeof($this_attachment_options['item_frame']);
                        $temp_event_frame = $this_battle->counters['event_frames'];
                        if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                        elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                        elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                        if (isset($this_attachment_options['item_frame_animate'][$attachment_frame_key])){ $this_attachment_options['item_frame'] = $this_attachment_options['item_frame_animate'][$attachment_frame_key]; }
                        $this_attachment_options['item_frame_offset'] = isset($attachment_info['item_frame_offset']) ? $attachment_info['item_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
                        $this_attachment_options['item_frame_styles'] = isset($attachment_info['item_frame_styles']) ? $attachment_info['item_frame_styles'] : $this_item->item_frame_styles;
                        $this_attachment_options['item_frame_classes'] = isset($attachment_info['item_frame_classes']) ? $attachment_info['item_frame_classes'] : $this_item->item_frame_classes;
                        if (!empty($camera_action_styles)){ $this_attachment_options['item_frame_styles'] .= $camera_action_styles; }

                        // Collect and appent static abilty markup to the parent string
                        $this_attachment_data = rpg_canvas::static_item_markup($this_item, $this_attachment_options, $left_side_player_data, $static_robot_data);
                        $this_markup .= $this_attachment_data['item_markup'];

                    }



                }

            }
        }

        // Append the field multipliers to the canvas markup
        if (!empty($this_battle->battle_field->field_multipliers)){
            $temp_multipliers = $this_battle->battle_field->field_multipliers;
            asort($temp_multipliers);
            $temp_multipliers = array_reverse($temp_multipliers, true);
            $temp_multipliers_count = count($temp_multipliers);
            $this_special_types = array('experience', 'damage', 'recovery', 'items');
            $multiplier_markup_left = '';
            $multiplier_markup_right = '';
            foreach ($temp_multipliers AS $this_type => $this_multiplier){
                if ($this_type == 'experience' && !empty($_SESSION['GAME']['DEMO'])){ continue; }
                if ($this_multiplier == 1){ continue; }
                if ($this_multiplier < MMRPG_SETTINGS_MULTIPLIER_MIN){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MIN; }
                elseif ($this_multiplier > MMRPG_SETTINGS_MULTIPLIER_MAX){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MAX; }
                $temp_name = $this_type != 'none' ? ucfirst($this_type) : 'Neutral';
                $temp_number = number_format($this_multiplier, 1);
                if ($temp_number == '1.0'){ continue; }
                $temp_title = $temp_name.' x '.$temp_number;
                if ($temp_multipliers_count >= 8){ $temp_name = substr($temp_name, 0, 2); }
                $temp_markup = '<span data-click-tooltip="'.$temp_title.'" data-tooltip-align="center" class="field_multiplier field_multiplier_'.$this_type.' field_multiplier_count_'.$temp_multipliers_count.' field_type field_type_'.$this_type.'"><span class="text"><span class="type">'.$temp_name.' </span><span class="cross">x</span><span class="number"> '.$temp_number.'</span></span></span>';
                if (in_array($this_type, $this_special_types)){ $multiplier_markup_left .= $temp_markup; }
                else { $multiplier_markup_right .= $temp_markup; }
            }
            if (!empty($multiplier_markup_left) || !empty($multiplier_markup_right)){
                $overlay_footer = '<div class="canvas_overlay_footer">';
                    $overlay_footer .= '<strong class="overlay_label">Field Multipliers</strong>';
                    $overlay_footer .= '<span class="overlay_multiplier_count_'.$temp_multipliers_count.'">'.$multiplier_markup_left.$multiplier_markup_right.'</span>';
                $overlay_footer .= '</div>';
                $this_overlay_markup .= $overlay_footer;
            }

        }

        // If this battle is over, display the mission complete/failed result
        if ($this_battle->battle_status == 'complete'){
            $is_final_battle = empty($this_battle->battle_complete_redirect_token) && empty($this_battle->battle_complete_redirect_seed) ? true : false;
            if ($this_battle->battle_result == 'victory'){
                $result_text = $is_final_battle ? 'Mission Complete!' : 'Battle Complete!';
                $result_class = 'nature';
            }
            elseif ($this_battle->battle_result == 'defeat') {
                if (!empty($this_battle->flags['challenge_battle']) && !empty($this_battle->flags['endless_battle'])){ $result_text = 'Wave Failure&hellip;'; }
                else { $result_text = $is_final_battle ? 'Mission Failure&hellip;' : 'Battle Failure&hellip;'; }
                $result_class = 'flame';
            }
            if (!empty($this_markup) && $this_battle->battle_status == 'complete' || $this_battle->battle_result == 'defeat'){
                $this_mugshot_markup_left = '<div class="sprite results_icon results_icon_left">&nbsp;</div>';
                $this_mugshot_markup_right = '<div class="sprite results_icon results_icon_right">&nbsp;</div>';
                $this_underlay_markup .= '<div class="sprite canvas_battle_results ability_type ability_type_'.$result_class.'">'.$this_mugshot_markup_left.'<div class="results_name ability_name">'.$result_text.'</div>'.$this_mugshot_markup_right.'</div>';
            }
        }

        // Put everything together into the final markup
        $final_markup = '';
        if (!empty($this_underlay_markup)){ $final_markup .= '<div class="battle_overlay under">'.$this_underlay_markup.'</div>'; }
        $final_markup .= '<div class="battle_scene">'.$this_markup.'</div>';
        if (!empty($this_overlay_markup)){ $final_markup .= '<div class="battle_overlay over">'.$this_overlay_markup.'</div>'; }

        // Return the final markup with everything together
        return $final_markup;

    }

    // Define a function for generating a given sprite's shadow based on it's type, base styles, and provided data
    public static function generate_sprite_shadow_markup($object_kind, $object_data, $object_class = '', $object_styles = ''){

        // Display the shadow sprite if allowed by the context (always for now)
        $allow_shadow_sprite = true;
        if (!$allow_shadow_sprite){
            return;
        }

        // Define the plural version of the object kind for later
        if (substr($object_kind, -1, 1) === 's'){ $object_kind_plural = $object_kind.'es'; }
        elseif (substr($object_kind, -1, 1) === 'y'){ $object_kind_plural = substr($object_kind, 0, -1).'ies'; }
        else { $object_kind_plural = $object_kind.'s'; }

        // Define the shadow token (and shadow image token in case we use it again in the future)
        $shadow_token = 'shadow-'.$object_kind;
        $shadow_image_token = $object_data[$object_kind.'_image'];
        $images_dir = MMRPG_CONFIG_ROOTDIR.'images/'.$object_kind_plural.'_shadows/';

        if (!rpg_game::sprite_exists($images_dir.$shadow_image_token.'/')){
            if (strstr($shadow_image_token, '_')){ list($shadow_image_token) = explode('_', $shadow_image_token); }
            elseif (preg_match('/(-[0-9])$/', $shadow_image_token)){ $shadow_image_token = preg_replace('/(-[0-9])$/', '', $shadow_image_token); }
            else { $shadow_image_token = $object_kind; }
        }

        // Collect the existing class and styles so we can modify
        $shadow_class = !empty($object_class) ? $object_class : $object_data[$object_kind.'_markup_class'];
        $shadow_styles = !empty($object_styles) ? $object_styles : $object_data[$object_kind.'_markup_style'];

        // Update the class to use the shadow class token wherever possible
        $shadow_class = str_replace($object_data[$object_kind.'_token'], $shadow_token, $shadow_class);

        // Update the Z-index to be at the very back of the field without overlay
        $shadow_offset_z = 100;
        $shadow_styles = preg_replace('/z\-index\:\s?([0-9]+)\;/i', '', $shadow_styles);
        $shadow_styles .= 'z-index: '.$shadow_offset_z.'; ';

        // Apply the filter to the image to make it into a shadow (pure black, then opacity)
        $shadow_styles = preg_replace('/filter\:\s?([^\;]+)\;/i', '', $shadow_styles);
        //$shadow_filter_val = 'brightness(0) opacity(0.1)';
        //$shadow_styles .= 'filter: '.$shadow_filter_val.';';

        // Apply the transform to the shadow to skew with perspective
        $shadow_scale = array((1.5 * $object_data[$object_kind.'_scale']), (0.25 * $object_data[$object_kind.'_scale']));
        $shadow_translate = array((4.5 * $object_data[$object_kind.'_scale'] * -1), 0);
        $shadow_skew = 30;
        if ($object_data[$object_kind.'_direction'] === 'left'){
            $shadow_translate[0] *= -1;
            $shadow_skew *= -1;
        }
        $shadow_styles .= 'transform-origin: bottom center; ';
        self::update_or_append_css_transform($shadow_styles, 'rotate(0)');
        self::update_or_append_css_transform($shadow_styles, 'scale('.$shadow_scale[0].','.$shadow_scale[1].')');
        self::update_or_append_css_transform($shadow_styles, 'translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px)');
        self::update_or_append_css_transform($shadow_styles, 'skew('.$shadow_skew.'deg)');

        // Generate the markup
        echo '<div '.
            'data-shadowid="'.$object_data[$object_kind.'_id'].'" '.
            'class="'.$shadow_class.'" '.
            'style="'.$shadow_styles.'" '.
            'data-key="'.(isset($object_data[$object_kind.'_key']) ? $object_data[$object_kind.'_key'] : 0).'" '.
            'data-type="'.((isset($object_data['data_type']) ? $object_data['data_type'] : 'object').'_shadow').'" '.
            'data-size="'.(isset($object_data[$object_kind.'_size']) ? $object_data[$object_kind.'_size'] : 40).'" '.
            'data-direction="'.($object_data[$object_kind.'_direction']).'" '.
            'data-frame="'.(isset($object_data[$object_kind.'_frame']) ? $object_data[$object_kind.'_frame'] : 'base').'" '.
            'data-position="'.(isset($object_data[$object_kind.'_position']) ? $object_data[$object_kind.'_position'] : 'active').'" '.
            'data-status="'.(isset($object_data[$object_kind.'_status']) ? $object_data[$object_kind.'_status'] : 'active').'" '.
            'data-scale="'.(isset($object_data[$object_kind.'_scale']) ? $object_data[$object_kind.'_scale'] : 1).'" '.
            '></div>';

    }

    // Define a function for checking if a robot has camera action given an array of event options, optionally returning a style
    public static function has_camera_action($object_info, $options, &$camera_action_styles = ''){
        //error_log('rpg_canvas::has_camera_action()');
        //error_log('rpg_canvas::has_camera_action()'.PHP_EOL.'$object_info = '.print_r($object_info, true).' '.PHP_EOL.'$options = '.print_r(array_filter($options, function($k){ return strstr($k, 'event_flag_'); }, ARRAY_FILTER_USE_KEY), true).' '.PHP_EOL.'$camera_action_styles = '.print_r($camera_action_styles, true).PHP_EOL);

        if (!isset($object_info['token'])){ $object_info['token'] = 'object'; }
        if (!isset($object_info['side'])){ $object_info['side'] = ''; }
        if (!isset($object_info['position'])){ $object_info['position'] = ''; }
        if (!isset($object_info['key'])){ $object_info['key'] = -1; }

        $debug = array();
        //$debug[] = ('CANVAS call for '.strtoupper($object_info['token']));
        //$debug[] = ('$object_info = '.print_r($object_info, true));
        //$debug[] = ('$options = '.print_r(array_filter($options, function($k){ return strstr($k, 'event_flag_'); }, ARRAY_FILTER_USE_KEY), true));
        $has_camera_action = !empty($options['event_flag_camera_action']) || !empty($options['event_flag_camera_reaction']) ? true : false;
        $has_direct_camera_action = false;
        $camera_action_styles = '';

        if (!empty($has_camera_action)
            && $options['event_flag_camera_side'] === $object_info['side']
            && $options['event_flag_camera_focus'] === $object_info['position']
            && $options['event_flag_camera_depth'] === $object_info['key']){
            //$debug[] = ('DIRECT camera action for '.$object_info['token']);
            $has_direct_camera_action = true;

        } else if (!empty($has_camera_action)
            && $options['event_flag_camera_side'] === $object_info['side']
            && $options['event_flag_camera_focus'] === $object_info['position']
            && $options['event_flag_camera_depth'] !== $object_info['key']){
            if ($options['event_flag_camera_depth'] === 0){
                //$debug[] = (strtoupper($object_info['token']).' is BENCHED behind the active camera action');

            } else if ($options['event_flag_camera_depth'] > $object_info['key']){
                //$debug[] = (strtoupper($object_info['token']).' is BESIDE but BLOCKING the benched camera action');
                $camera_action_styles = 'filter: brightness(0.8) grayscale(1); opacity: 0.2; ';

            } else if ($options['event_flag_camera_depth'] < $object_info['key']){
                //$debug[] = (strtoupper($object_info['token']).' is BESIDE but behind the benched camera action');
                $camera_action_styles = 'filter: brightness(0.8) grayscale(1); opacity: 0.2; ';

            }
        } else if (!empty($has_camera_action)
            && $options['event_flag_camera_side'] === $object_info['side']
            && $options['event_flag_camera_focus'] === 'active'
            && $object_info['position'] !== 'active'){
            //$debug[] = (strtoupper($object_info['token']).' is BENCHED behind the active camera action');
            $camera_action_styles = 'filter: brightness(0.8) grayscale(1); opacity: 0.2; ';

        } else if (!empty($has_camera_action)
            && $options['event_flag_camera_side'] === $object_info['side']
            && $options['event_flag_camera_focus'] === 'bench'
            && $object_info['position'] !== 'bench'){
            //$debug[] = (strtoupper($object_info['token']).' is actively BLOCKING the benched camera action');
            $camera_action_styles = 'filter: brightness(0.8) grayscale(1); opacity: 0.2; ';

        } else {
            //$debug[] = ('NO camera action for '.$object_info['token']);

        }

        //error_log(implode(PHP_EOL, $debug).PHP_EOL);

        return $has_direct_camera_action;

    }

    // Define a function for getting the canvas animation effect index (for accessibility toggles)
    public static function get_animation_effects_index(){

        // Define an index to hold the animation effects
        $animation_effects_index = array();

        // Define the animation effects we can toggle ON or OFF
        $animation_effects_index['eventCrossFade'] = array(
            'name' => 'Cross-Fade Frames',
            'token' => 'eventCrossFade',
            'default' => true
            );
        $animation_effects_index['eventCameraShift'] = array(
            'name' => 'Dynamic Camera',
            'token' => 'eventCameraShift',
            'default' => true
            );

        // Return the list of effects
        return $animation_effects_index;

    }


    // Define a function for updating an event with camera action given context flags
    public static function apply_camera_action_flags(&$event_options, $this_robot, $trigger_object = false, $trigger_kind = ''){

        // Set the camera options for this target event
        $event_options['event_flag_camera_action'] = true;
        if ($trigger_kind !== 'target'){ $event_options['event_flag_camera_reaction'] = true; }
        $event_options['event_flag_camera_side'] = $this_robot->player->player_side;
        $event_options['event_flag_camera_focus'] = $this_robot->robot_position;
        $event_options['event_flag_camera_depth'] = $this_robot->robot_key;
        $kickback_shift_threshold = 20;
        if (!empty($trigger_object)
            && !empty($trigger_kind)){
            $object_kind = $trigger_object->class;
            $token_key = $object_kind.'_token';
            $options_key = $trigger_kind.'_options';
            $kickback_key = $trigger_kind.'_kickback';
            $kickback_shift_value = 0;
            //error_log('$trigger_object->$options_key = '.print_r($trigger_object->$options_key, true));
            if (isset($trigger_object->$options_key)){
                $trigger_object_options = $trigger_object->$options_key;
                //error_log('$trigger_object_options = '.print_r($trigger_object_options, true));
                if (isset($trigger_object_options[$kickback_key])
                    && isset($trigger_object_options[$kickback_key]['x'])){
                    $kickback_shift_value = $trigger_object_options[$kickback_key]['x'];
                    //error_log('$kickback_shift_value = '.print_r($kickback_shift_value, true));
                }
            }
            if (!empty($kickback_shift_value)
                && $kickback_shift_value != 0
                && abs($kickback_shift_value) >= $kickback_shift_threshold){
                //error_log($kickback_shift_value.' for '.$trigger_object->$token_key.' = '.$kickback_shift_value);
                $event_options['event_flag_camera_offset'] = round(($kickback_shift_value / $kickback_shift_threshold), 1);
                if ($trigger_kind === 'target'){ $event_options['event_flag_camera_offset'] *= -1; }
                //error_log('event_flag_camera_offset = '.$event_options['event_flag_camera_offset']);
            }
        }

    }

    // Define a function for dynamically updating or appending the transform property in a CSS style string
    public static function update_or_append_css_transform(&$style_str, $new_transform){
        $old_style_str = trim($style_str);
        $style_array = array_filter(array_map('trim', explode(';', $style_str)));
        $transform_key = null;

        // Search for existing transform property
        foreach ($style_array as $key => $style) {
            if (strpos($style, 'transform:') !== false) {
                $transform_key = $key;
                break;
            }
        }

        // Define transform type (scale, rotate, etc.)
        list($new_transform_type, $new_transform_value) = explode('(', rtrim($new_transform, ')'));

        // If transform property exists, check if specific transform type is present
        if ($transform_key !== null) {
            $current_transforms = explode(' ', trim(str_replace('transform:', '', $style_array[$transform_key])));
            $current_transforms = array_map('trim', $current_transforms); // Trim spaces from each element

            $found = false;
            foreach ($current_transforms as $key => $transform) {
                $transform_frags = explode('(', rtrim($transform, ')'));
                $existing_transform_type = isset($transform_frags[0]) ? $transform_frags[0] : '';
                $existing_transform_value = isset($transform_frags[1]) ? $transform_frags[1] : 0;
                if ($existing_transform_type == $new_transform_type) {
                    // Check if the values are numeric
                    if (is_numeric($new_transform_value) && is_numeric($existing_transform_value)) {
                        // For scale, scaleX, scaleY, multiply the existing and new values
                        if ($new_transform_type == 'scale' || $new_transform_type == 'scaleX' || $new_transform_type == 'scaleY') {
                            $updated_transform_value = (float)$existing_transform_value * (float)$new_transform_value;
                        }
                        // For rotate, translate, translateX, translateY, add the existing and new values
                        else if ($new_transform_type == 'rotate' || $new_transform_type == 'translate' || $new_transform_type == 'translateX' || $new_transform_type == 'translateY') {
                            $updated_transform_value = (float)$existing_transform_value + (float)$new_transform_value;
                        }
                    } else {
                        // If the values are not numeric (e.g. percentage), use the new value
                        $updated_transform_value = $new_transform_value;
                    }
                    $current_transforms[$key] = $new_transform_type . '(' . $updated_transform_value . ')'; // Replace existing transform value
                    $found = true;
                    break;
                }
            }

            // If specific transform type wasn't found, append new transform
            if (!$found) {
                $current_transforms[] = $new_transform;
            }

            $style_array[$transform_key] = 'transform: ' . implode(' ', $current_transforms);

        } else {
            // If no transform property, add it
            $style_array[] = 'transform: ' . $new_transform;
        }

        $new_style_str = implode('; ', $style_array) . '; ';
        $style_str = $new_style_str;

        //error_log('|'.PHP_EOL.'$old_style_str vs $new_style_str:'.PHP_EOL.$old_style_str.PHP_EOL.$new_style_str);

        return $style_str;
    }


}
?>