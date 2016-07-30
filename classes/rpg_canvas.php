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
            $this_data['player_size'] = 80;
            $this_data['image_type'] = !empty($options['this_player_image']) ? $options['this_player_image'] : 'sprite';
            /*
            $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/sprite_'.$this_data['player_direction'].'_'.$this_data['player_size'].'x'.$this_data['player_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['player_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_80x80 sprite_80x80_'.$this_data['player_frame'];
            $this_data['player_styles'] = '';
            */
            $this_data['player_image'] = 'images/players/'.$this_data['player_token'].'/sprite_'.$this_data['player_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['player_class'] = 'sprite sprite_player sprite_player_'.$this_data['image_type'].' sprite_75x75 sprite_75x75_'.$this_data['player_frame'];


            $this_data['player_scale'] = 0.5 + ((7 / 8) * 0.5);
            $this_data['player_sprite_size'] = ceil($this_data['player_scale'] * 80);
            $this_data['player_sprite_width'] = ceil($this_data['player_scale'] * 80);
            $this_data['player_sprite_height'] = ceil($this_data['player_scale'] * 80);
            $this_data['player_image_width'] = ceil($this_data['player_scale'] * 800);
            $this_data['player_image_height'] = ceil($this_data['player_scale'] * 80);
            $this_data['canvas_offset_z'] = 4900;
            $this_data['canvas_offset_x'] = 200;
            $this_data['canvas_offset_y'] = 60;

            $frame_position = array_search($this_data['player_frame'], $this_data['player_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $frame_background_offset = -1 * ceil(($this_data['player_sprite_size'] * $frame_position));
            $this_data['player_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
            $this_data['player_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['player_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
            $this_data['player_style'] .= 'background-image: url('.$this_data['player_image'].'); width: '.$this_data['player_sprite_size'].'px; height: '.$this_data['player_sprite_size'].'px; background-size: '.$this_data['player_image_width'].'px '.$this_data['player_image_height'].'px; ';

            // Generate the final markup for the canvas player
            ob_start();

                // Display this player's sprite in the active position
                global $flag_wap, $flag_ipad, $flag_iphone;
                if (!$flag_wap && !$flag_ipad && !$flag_iphone){
                    $shadow_offset_z = $this_data['canvas_offset_z'] - 1;
                    $shadow_scale = array(1.5, 0.25);
                    $shadow_skew = $this_data['player_direction'] == 'right' ? 30 : -30;
                    $shadow_translate = array(
                        ($this_data['player_direction'] == 'right' ? -1 : 1) * ($this_data['player_sprite_width'] + ceil($this_data['player_sprite_width'] * $shadow_scale[1])) + ceil($shadow_skew * $shadow_scale[1]),
                        $this_data['player_position'] == 'active' ? 115 : ceil($this_data['player_sprite_height'] * $shadow_scale[0]),
                        );
                    $shadow_styles = 'z-index: '.$shadow_offset_z.'; transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);  -webkit-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);  -moz-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px);';
                    echo '<div data-shadowid="'.$this_data['player_id'].'" class="'.str_replace($this_data['player_token'], 'player', $this_data['player_class']).'" style="'.str_replace('players/', 'players_shadows/', $this_data['player_style']).$shadow_styles.'" data-type="'.$this_data['data_type'].'_shadow" data-size="'.$this_data['player_sprite_size'].'" data-direction="'.$this_data['player_direction'].'" data-frame="'.$this_data['player_frame'].'">'.$this_data['player_token'].'_shadow</div>';
                }
                echo '<div data-playerid="'.$this_data['player_id'].'" class="'.$this_data['player_class'].'" style="'.$this_data['player_style'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['player_sprite_size'].'" data-direction="'.$this_data['player_direction'].'" data-frame="'.$this_data['player_frame'].'" data-position="'.$this_data['player_position'].'">'.$this_data['player_title'].'</div>';

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
        $this_data['robot_size'] = $this_data['robot_position'] == 'active' ? ($this_robot->robot_image_size * 2) : $this_robot->robot_image_size;
        $this_data['robot_size_base'] = $this_robot->robot_image_size;
        $this_data['robot_size_path'] = ($this_robot->robot_image_size * 2).'x'.($this_robot->robot_image_size * 2);
        //$this_data['robot_scale'] = $this_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_data['robot_key']) / 8) * 0.5);
        //$this_data['robot_title'] = $this_robot->robot_number.' '.$this_robot->robot_name.' (Lv. '.$this_robot->robot_level.')';
        $this_data['robot_title'] = $this_robot->robot_name.' (Lv. '.$this_robot->robot_level.')';
        $this_data['robot_title'] .= ' <br />'.(!empty($this_data['robot_core']) && $this_data['robot_core'] != 'none' ? ucfirst($this_data['robot_core']).' Core' : 'Neutral Core');
        $this_data['robot_title'] .= ' | '.ucfirst($this_data['robot_position']).' Position';

        // Calculate the canvas offset variables for this robot
        $temp_data = $this_robot->battle->canvas_markup_offset($this_data['robot_key'], $this_data['robot_position'], $this_data['robot_size']);
        $this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        $this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        $this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];
        $this_data['canvas_offset_rotate'] = 0;
        $this_data['robot_scale'] = $temp_data['canvas_scale'];

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
        if ($this_data['robot_position'] == 'bench' && $this_data['robot_frame'] == 'base' && $this_data['robot_status'] != 'disabled'){
            // Define a randomly generated integer value
            $random_int = mt_rand(1, 10);
            // If the random number was one, show an attack frame
            if ($random_int == 1){ $this_data['robot_frame'] = 'taunt'; }
            // Else if the random number was two, show a defense frame
            elseif ($random_int == 2){ $this_data['robot_frame'] = 'defend'; }
            // Else if the random number was anything else, show the base frame
            else { $this_data['robot_frame'] = 'base'; }
        }

        // If the robot is defeated, move its sprite accorss the field
        if ($this_data['robot_frame'] == 'defeat'){
            //$this_data['canvas_offset_x'] -= ceil($this_data['robot_size'] * 0.10);
        }

        // Fix the robot x position if it's size if greater than 80
        //$this_data['canvas_offset_x'] -= ceil(($this_data['robot_size'] - 80) * 0.10);

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
            $this_stats = rpg_robot::calculate_stat_values($this_robot->robot_level, $index_info, $reward_info);

            // Define the rest of the display variables
            //$this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size_path'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['robot_markup_class'] = 'sprite ';
            //$this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
            $this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].' sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].'_'.$this_data['robot_frame'].' ';
            $this_data['robot_markup_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
            $frame_position = is_numeric($this_data['robot_frame']) ? (int)($this_data['robot_frame']) : array_search($this_data['robot_frame'], $this_data['robot_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $this_data['robot_markup_class'] .= $this_data['robot_frame_classes'];
            $frame_background_offset = -1 * ceil(($this_data['robot_sprite_size'] * $frame_position));
            $this_data['robot_markup_style'] = 'background-position: '.(!empty($frame_background_offset) ? $frame_background_offset.'px' : '0').' 0; ';
            $this_data['robot_markup_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['robot_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
            if ($this_data['robot_frame'] == 'damage'){
                $temp_rotate_amount = $this_data['canvas_offset_rotate'];
                if ($this_data['robot_direction'] == 'right'){ $temp_rotate_amount = $temp_rotate_amount * -1; }
                $this_data['robot_markup_style'] .= 'transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); ';
            }
            //$this_data['robot_markup_style'] .= 'background-image: url('.$this_data['robot_file'].'); ';
            $this_data['robot_markup_style'] .= 'background-image: url('.$this_data['robot_file'].'); width: '.$this_data['robot_sprite_size'].'px; height: '.$this_data['robot_sprite_size'].'px; background-size: '.$this_data['robot_file_width'].'px '.$this_data['robot_file_height'].'px; ';
            $this_data['robot_markup_style'] .= $this_data['robot_frame_styles'];
            $this_data['energy_class'] = 'energy';
            $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';
            $this_data['weapons_class'] = 'weapons';
            $this_data['weapons_style'] = 'background-position: '.$this_data['weapons_x_position'].'px '.$this_data['weapons_y_position'].'px;';

            // Check if this robot's energy has been maxed out
            $temp_energy_maxed = $this_stats['energy']['current'] >= $this_stats['energy']['max'] ? true : false;

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

                if ($this_data['robot_class'] == 'mecha'){
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

            $this_data['robot_title_plain'] = strip_tags(str_replace('<br />', '&#10;', $this_data['robot_title']));
            $this_data['robot_title_tooltip'] = htmlentities($this_data['robot_title'], ENT_QUOTES, 'UTF-8');

            // Display the robot's shadow sprite if allowed sprite
            global $flag_wap, $flag_ipad, $flag_iphone;
            if (!$flag_wap && !$flag_ipad && !$flag_iphone){
                $shadow_offset_z = $this_data['canvas_offset_z'] - 4;
                $shadow_scale = array(1.5, 0.25);
                $shadow_skew = $this_data['robot_direction'] == 'right' ? 30 : -30;
                $shadow_translate = array(
                    ceil($this_data['robot_sprite_width'] + ($this_data['robot_sprite_width'] * $shadow_scale[1]) + ($shadow_skew * $shadow_scale[1]) - (($this_data['robot_direction'] == 'right' ? 15 : 5) * $this_data['robot_scale'])),
                    ceil(($this_data['robot_sprite_height'] * $shadow_scale[0]) - (5 * $this_data['robot_scale'])),
                    );
                //if ($this_data['robot_size_base'] >= 80 && $this_data['robot_position'] == 'active'){ $shadow_translate[0] += ceil(10 * $this_data['robot_scale']); $shadow_translate[1] += ceil(120 * $this_data['robot_scale']); }
                $shadow_translate[0] = $shadow_translate[0] * ($this_data['robot_direction'] == 'right' ? -1 : 1);
                $shadow_styles = 'z-index: '.$shadow_offset_z.'; transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); -webkit-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); -moz-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); ';
                $shadow_token = 'shadow-'.$this_robot->robot_class;
                if ($this_robot->robot_class == 'mecha'){ $shadow_image_token = preg_replace('/(-2|-3)$/', '', $this_data['robot_image']); }
                elseif (strstr($this_data['robot_image'], '_')){ list($shadow_image_token) = explode('_', $this_data['robot_image']); }
                else { $shadow_image_token = $this_data['robot_image']; }
                //$shadow_image_token = $this_robot->robot_class == 'mecha' ? preg_replace('/(-2|-3)$/', '', $this_data['robot_image']) : $this_data['robot_image'];
                echo '<div data-shadowid="'.$this_data['robot_id'].
                    '" class="'.str_replace($this_data['robot_token'], $shadow_token, $this_data['robot_markup_class']).
                    '" style="'.str_replace('robots/'.$this_data['robot_image'], 'robots_shadows/'.$shadow_image_token, $this_data['robot_markup_style']).$shadow_styles.
                    '" data-key="'.$this_data['robot_key'].
                    '" data-type="'.$this_data['data_type'].'_shadow'.
                    '" data-size="'.$this_data['robot_sprite_size'].
                    '" data-direction="'.$this_data['robot_direction'].
                    '" data-frame="'.$this_data['robot_frame'].
                    '" data-position="'.$this_data['robot_position'].
                    '" data-status="'.$this_data['robot_status'].
                    '" data-scale="'.$this_data['robot_scale'].
                    '"></div>';
            }

            // Display this robot's battle sprite
            //echo '<div data-robotid="'.$this_data['robot_id'].'" class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" title="'.$this_data['robot_title_plain'].'" data-tooltip="'.$this_data['robot_title_tooltip'].'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'">'.$this_data['robot_token'].'</div>';
            echo '<div data-robotid="'.$this_data['robot_id'].'" class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'">'.$this_data['robot_token'].'</div>';
            //echo '<a class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" title="'.$this_data['robot_title'].'" data-type="robot" data-size="'.$this_data['robot_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-action="'.$this_data['robot_action'].'" data-status="'.$this_data['robot_status'].'">'.$this_data['robot_title'].'</a>';

            // If this robot has any overlays, display them too
            if (!empty($this_data['robot_image_overlay'])){
                foreach ($this_data['robot_image_overlay'] AS $key => $overlay_token){
                    if (empty($overlay_token)){ continue; }
                    $overlay_offset_z = $this_data['canvas_offset_z'] + 2;
                    $overlay_styles = ' z-index: '.$overlay_offset_z.'; ';
                    echo '<div data-overlayid="'.$this_data['robot_id'].
                        '" class="'.str_replace($this_data['robot_token'], $overlay_token, $this_data['robot_markup_class']).
                        '" style="'.str_replace('robots/'.$this_data['robot_image'], 'robots/'.$overlay_token, $this_data['robot_markup_style']).$overlay_styles.
                        '" data-key="'.$this_data['robot_key'].
                        '" data-type="'.$this_data['data_type'].'_overlay'.
                        '" data-size="'.$this_data['robot_sprite_size'].
                        '" data-direction="'.$this_data['robot_direction'].
                        '" data-frame="'.$this_data['robot_frame'].
                        '" data-position="'.$this_data['robot_position'].
                        '" data-status="'.$this_data['robot_status'].
                        '" data-scale="'.$this_data['robot_scale'].
                        '"></div>';
                }
            }

            // Check if his player has any other active robots
            $temp_player_active_robots = false;
            foreach ($this_robot->player->values['robots_active'] AS $info){
                if ($info['robot_position'] == 'active'){ $temp_player_active_robots = true; }
            }

            // Check if this is an active position robot
            if ($this_data['robot_position'] != 'bench' || ($temp_player_active_robots == false && $this_data['robot_frame'] == 'damage')){

                // Define the mugshot and detail variables for the GUI
                $details_data = $this_data;
                $details_data['robot_file'] = 'images/robots/'.$details_data['robot_image'].'/sprite_'.$details_data['robot_direction'].'_'.$details_data['robot_size'].'x'.$details_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $details_data['robot_details'] = '<div class="robot_name">'.$this_robot->robot_name.'</div>';
                $details_data['robot_details'] .= '<div class="robot_level robot_type robot_type_'.($this_robot->robot_level >= 100 ? 'electric' : 'none').'">Lv. '.$this_robot->robot_level.'</div>';
                $details_data['robot_details'] .= '<div class="'.$details_data['energy_class'].'" style="'.$details_data['energy_style'].'" title="'.$details_data['energy_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$this_data['energy_tooltip_type'].'">'.$details_data['energy_title'].'</div>';
                $details_data['robot_details'] .= '<div class="'.$details_data['weapons_class'].'" style="'.$details_data['weapons_style'].'" title="'.$details_data['weapons_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_weapons">'.$details_data['weapons_title'].'</div>';
                if ($this_data['robot_float'] == 'left'){ $details_data['robot_details'] .= '<div class="'.$details_data['experience_class'].'" style="'.$details_data['experience_style'].'" title="'.$details_data['experience_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_experience">'.$details_data['experience_title'].'</div>'; }

                /*
                $robot_attack_markup = '<div class="robot_attack'.($this_robot->robot_attack < 1 ? ' robot_attack_break' : ($this_robot->robot_attack < ($this_robot->robot_base_attack / 2) ? ' robot_attack_break_chance' : '')).'">'.str_pad($this_robot->robot_attack, 3, '0', STR_PAD_LEFT).'</div>';
                $robot_defense_markup = '<div class="robot_defense'.($this_robot->robot_defense < 1 ? ' robot_defense_break' : ($this_robot->robot_defense < ($this_robot->robot_base_defense / 2) ? ' robot_defense_break_chance' : '')).'">'.str_pad($this_robot->robot_defense, 3, '0', STR_PAD_LEFT).'</div>';
                $robot_speed_markup = '<div class="robot_speed'.($this_robot->robot_speed < 1 ? ' robot_speed_break' : ($this_robot->robot_speed < ($this_robot->robot_base_speed / 2) ? ' robot_speed_break_chance' : '')).'">'.str_pad($this_robot->robot_speed, 3, '0', STR_PAD_LEFT).'</div>';
                */

                // Loop through and define the other stat variables and markup
                $stat_tokens = array('attack' => 'AT', 'defense' => 'DF', 'speed' => 'SP');
                foreach ($stat_tokens AS $stat => $letters){
                    $prop_stat = 'robot_'.$stat;
                    $prop_stat_base = 'robot_base_'.$stat;
                    $prop_stat_max = 'robot_max_'.$stat;
                    $prop_markup = 'robot_'.$stat.'_markup';
                    $temp_stat_break = $this_robot->$prop_stat < 1 ? true : false;
                    $temp_stat_break_chance = $this_robot->$prop_stat < ($this_robot->$prop_stat_base / 2) ? true : false;
                    $temp_stat_maxed = $this_stats[$stat]['current'] >= $this_stats[$stat]['max'] ? true : false;
                    $temp_stat_percent = round(($this_robot->$prop_stat / $this_robot->$prop_stat_base) * 100);
                    if ($this_data['robot_float'] == 'left'){ $temp_stat_title = $this_robot->$prop_stat.' / '.$this_robot->$prop_stat_base.' '.$letters.' | '.$temp_stat_percent.'%'.($temp_stat_break ? ' | BREAK!' : '').($temp_stat_maxed ? ' | &#9733;' : ''); }
                    elseif ($this_data['robot_float'] == 'right'){ $temp_stat_title = ($temp_stat_maxed ? '&#9733; |' : '').($temp_stat_break ? 'BREAK! | ' : '').$temp_stat_percent.'% | '.$this_robot->$prop_stat.' / '.$this_robot->$prop_stat_base.' '.$letters; }
                    $$prop_markup = '<div class="robot_'.$stat.''.($temp_stat_break ? ' robot_'.$stat.'_break' : ($temp_stat_break_chance ? ' robot_'.$stat.'_break_chance' : '')).'" title="'.$temp_stat_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$stat.'">'.$this_robot->$prop_stat.'</div>';

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

                $details_data['mugshot_file'] = 'images/robots/'.$details_data['robot_image'].'/mug_'.$details_data['robot_direction'].'_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $details_data['mugshot_class'] = 'sprite details robot_mugshot ';
                $details_data['mugshot_class'] .= 'sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot sprite_mugshot_'.$details_data['robot_float'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot_'.$details_data['robot_float'].' ';
                $details_data['mugshot_class'] .= 'robot_status_'.$details_data['robot_status'].' robot_position_'.$details_data['robot_position'].' ';
                $details_data['mugshot_style'] = 'z-index: 9100; ';
                $details_data['mugshot_style'] .= 'background-image: url('.$details_data['mugshot_file'].'); ';

                // Display the robot's mugshot sprite and detail fields
                echo '<div data-detailsid="'.$this_data['robot_id'].'" class="sprite details robot_details robot_details_'.$details_data['robot_float'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').'><div class="container">'.$details_data['robot_details'].'</div></div>';
                echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.str_replace('80x80', '40x40', $details_data['mugshot_class']).' robot_mugshot_type robot_type robot_type_'.$this_data['robot_core'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').' data-tooltip="'.$details_data['robot_title_tooltip'].'"><div class="sprite">&nbsp;</div></div>';
                //echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'" title="'.$details_data['robot_title_plain'].'" data-tooltip="'.$details_data['robot_title_tooltip'].'">'.$details_data['robot_token'].'</div>';
                echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'">'.$details_data['robot_token'].'</div>';

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
        $this_data['ability_status'] = $robot_data['robot_status'];
        $this_data['ability_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['ability_direction'] = $this_ability->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['ability_float'] = $robot_data['robot_float'];
        $this_data['ability_size'] = $this_data['ability_position'] == 'active' ? ($this_ability->ability_image_size * 2) : $this_ability->ability_image_size;
        $this_data['ability_frame'] = isset($options['ability_frame']) ? $options['ability_frame'] : $this_ability->ability_frame;
        $this_data['ability_frame_span'] = isset($options['ability_frame_span']) ? $options['ability_frame_span'] : $this_ability->ability_frame_span;
        $this_data['ability_frame_index'] = isset($options['ability_frame_index']) ? $options['ability_frame_index'] : $this_ability->ability_frame_index;
        if (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] >= 0){ $this_data['ability_frame'] = str_pad($this_data['ability_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['ability_frame']) && $this_data['ability_frame'] < 0){ $this_data['ability_frame'] = ''; }
        //$this_data['ability_image'] = 'images/abilities/'.(!empty($this_data['ability_image']) ? $this_data['ability_image'] : $this_data['ability_token']).'/sprite_'.$this_data['ability_direction'].'_'.$this_data['ability_size'].'x'.$this_data['ability_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['ability_frame_offset'] = isset($options['ability_frame_offset']) ? $options['ability_frame_offset'] : $this_ability->ability_frame_offset;
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

        // DEBUG
        //$this_ability->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_ability_target\'] = '.$options['this_ability_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this_ability->ability_image_size * 2);
        $this_data['ability_sprite_size'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_width'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_sprite_height'] = ceil($this_data['ability_scale'] * $zoom_size);
        $this_data['ability_image_width'] = ceil($this_data['ability_scale'] * $zoom_size * 10);
        $this_data['ability_image_height'] = ceil($this_data['ability_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this robot
        $canvas_offset_data = $this_ability->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size']);
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
        if ($this_data['data_sticky'] == true){

            // Calculate the canvas X offsets using the robot's position as base
            if ($this_data['ability_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['ability_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($canvas_offset_data['canvas_offset_x'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $canvas_offset_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's position as base
            if ($this_data['ability_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['y']/100))); }
            elseif ($this_data['ability_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($canvas_offset_data['canvas_offset_y'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $canvas_offset_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's position as base
            if ($this_data['ability_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] + $this_data['ability_frame_offset']['z']); }
            elseif ($this_data['ability_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($canvas_offset_data['canvas_offset_z'] - ($this_data['ability_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $canvas_offset_data['canvas_offset_z'];  }

        }
        // Else if this is a normal attachment, it moves with the robot
        else {

            // Calculate the canvas X offsets using the robot's offset as base
            if ($this_data['ability_frame_offset']['x'] > 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['x']/100))) + $temp_size_diff; }
            elseif ($this_data['ability_frame_offset']['x'] < 0){ $this_data['canvas_offset_x'] = ceil($robot_data['canvas_offset_x'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['x'] * -1)/100))) + $temp_size_diff; }
            else { $this_data['canvas_offset_x'] = $robot_data['canvas_offset_x'] + $temp_size_diff;  }
            // Calculate the canvas Y offsets using the robot's offset as base
            if ($this_data['ability_frame_offset']['y'] > 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] + ($this_data['ability_sprite_size'] * ($this_data['ability_frame_offset']['y']/100))); }
            elseif ($this_data['ability_frame_offset']['y'] < 0){ $this_data['canvas_offset_y'] = ceil($robot_data['canvas_offset_y'] - ($this_data['ability_sprite_size'] * (($this_data['ability_frame_offset']['y'] * -1)/100))); }
            else { $this_data['canvas_offset_y'] = $robot_data['canvas_offset_y'];  }
            // Calculate the canvas Z offsets using the robot's offset as base
            if ($this_data['ability_frame_offset']['z'] > 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] + $this_data['ability_frame_offset']['z']); }
            elseif ($this_data['ability_frame_offset']['z'] < 0){ $this_data['canvas_offset_z'] = ceil($robot_data['canvas_offset_z'] - ($this_data['ability_frame_offset']['z'] * -1)); }
            else { $this_data['canvas_offset_z'] = $robot_data['canvas_offset_z'];  }

            // Collect the target, damage, and recovery options
            $this_target_options = !empty($options['this_ability']->target_options) ? $options['this_ability']->target_options : array();
            $this_damage_options = !empty($options['this_ability']->damage_options) ? $options['this_ability']->damage_options : array();
            $this_recovery_options = !empty($options['this_ability']->recovery_options) ? $options['this_ability']->recovery_options : array();
            $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

            // Either way, apply target offsets if they exist and it's this robot using the ability
            if (isset($options['this_ability_target']) && $options['this_ability_target'] == $this_data['robot_id_token']){
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


        // Define the rest of the display variables
        //$this_data['ability_image'] = 'images/abilities/'.(!empty($this_data['ability_image']) ? $this_data['ability_image'] : $this_data['ability_token']).'/sprite_'.$this_data['ability_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
        if (!preg_match('/^images/i', $this_data['ability_image'])){ $this_data['ability_image'] = 'images/abilities/'.$this_data['ability_image'].'/sprite_'.$this_data['ability_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['ability_markup_class'] = 'sprite sprite_ability ';
        $this_data['ability_markup_class'] .= 'sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].' sprite_'.$this_data['ability_sprite_size'].'x'.$this_data['ability_sprite_size'].'_'.$this_data['ability_frame'].' ';
        $this_data['ability_markup_class'] .= 'ability_status_'.$this_data['ability_status'].' ability_position_'.$this_data['ability_position'].' ';
        $frame_position = is_numeric($this_data['ability_frame']) ? (int)($this_data['ability_frame']) : array_search($this_data['ability_frame'], $this_data['ability_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['ability_sprite_size'] * $frame_position));
        $this_data['ability_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['ability_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['ability_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['ability_markup_style'] .= 'background-image: url('.$this_data['ability_image'].'); width: '.($this_data['ability_sprite_size'] * $this_data['ability_frame_span']).'px; height: '.$this_data['ability_sprite_size'].'px; background-size: '.$this_data['ability_image_width'].'px '.$this_data['ability_image_height'].'px; ';

        // DEBUG
        //$this_data['ability_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));


        // Generate the final markup for the canvas ability
        ob_start();

            // Display the ability's battle sprite
            echo '<div data-ability-id="'.$this_data['ability_id_token'].'" data-robot-id="'.$robot_data['robot_id_token'].'" class="'.($this_data['ability_markup_class'].$this_data['ability_frame_classes']).'" style="'.($this_data['ability_markup_style'].$this_data['ability_frame_styles']).'" '.(!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['ability_sprite_size'].'" data-direction="'.$this_data['ability_direction'].'" data-frame="'.$this_data['ability_frame'].'" data-animate="'.$this_data['ability_frame_animate'].'" data-position="'.$this_data['ability_position'].'" data-status="'.$this_data['ability_status'].'" data-scale="'.$this_data['ability_scale'].'">'.$this_data['ability_token'].'</div>';

        // Collect the generated ability markup
        $this_data['ability_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating item canvas variables
    public function item_markup($this_item, $options, $player_data, $robot_data){

        // Define the variable to hold the console robot data
        $this_data = array();

        // Define the item data array and populate basic data
        $this_data['item_markup'] = '';
        $this_data['data_sticky'] = !empty($options['data_sticky']) ? true : false;
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'item';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['item_name'] = isset($options['item_name']) ? $options['item_name'] : $this->item_name;
        $this_data['item_id'] = $this->item_id;
        $this_data['item_title'] = $this->item_name;
        $this_data['item_token'] = $this->item_token;
        $this_data['item_id_token'] = $this->item_id.'_'.$this->item_token;
        $this_data['item_image'] = isset($options['item_image']) ? $options['item_image'] : $this->item_image;
        $this_data['item_status'] = $robot_data['robot_status'];
        $this_data['item_position'] = $robot_data['robot_position'];
        $this_data['robot_id_token'] = $robot_data['robot_id'].'_'.$robot_data['robot_token'];
        $this_data['item_direction'] = $this->robot_id == $robot_data['robot_id'] ? $robot_data['robot_direction'] : ($robot_data['robot_direction'] == 'left' ? 'right' : 'left');
        $this_data['item_float'] = $robot_data['robot_float'];
        $this_data['item_size'] = $this_data['item_position'] == 'active' ? ($this->item_image_size * 2) : $this->item_image_size;
        $this_data['item_frame'] = isset($options['item_frame']) ? $options['item_frame'] : $this->item_frame;
        $this_data['item_frame_span'] = isset($options['item_frame_span']) ? $options['item_frame_span'] : $this->item_frame_span;
        $this_data['item_frame_index'] = isset($options['item_frame_index']) ? $options['item_frame_index'] : $this->item_frame_index;
        if (is_numeric($this_data['item_frame']) && $this_data['item_frame'] >= 0){ $this_data['item_frame'] = str_pad($this_data['item_frame'], 2, '0', STR_PAD_LEFT); }
        elseif (is_numeric($this_data['item_frame']) && $this_data['item_frame'] < 0){ $this_data['item_frame'] = ''; }
        //$this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/sprite_'.$this_data['item_direction'].'_'.$this_data['item_size'].'x'.$this_data['item_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['item_frame_offset'] = isset($options['item_frame_offset']) ? $options['item_frame_offset'] : $this->item_frame_offset;
        $animate_frames_array = isset($options['item_frame_animate']) ? $options['item_frame_animate'] : array($this_data['item_frame']);
        $animate_frames_string = array();
        if (!empty($animate_frames_array)){
            foreach ($animate_frames_array AS $key => $frame){
                $animate_frames_string[] = is_numeric($frame) ? str_pad($frame, 2, '0', STR_PAD_LEFT) : $frame;
            }
        }
        $this_data['item_frame_animate'] = implode(',', $animate_frames_string);
        $this_data['item_frame_styles'] = isset($options['item_frame_styles']) ? $options['item_frame_styles'] : $this->item_frame_styles;
        $this_data['item_frame_classes'] = isset($options['item_frame_classes']) ? $options['item_frame_classes'] : $this->item_frame_classes;

        $this_data['item_scale'] = isset($robot_data['robot_scale']) ? $robot_data['robot_scale'] : ($robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $robot_data['robot_key']) / 8) * 0.5));

        // DEBUG
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, '$this_data[\'robot_id_token\'] = '.$this_data['robot_id_token'].' | $options[\'this_item_target\'] = '.$options['this_item_target']);

        // Calculate the rest of the sprite size variables
        $zoom_size = ($this->item_image_size * 2);
        $this_data['item_sprite_size'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_width'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_sprite_height'] = ceil($this_data['item_scale'] * $zoom_size);
        $this_data['item_image_width'] = ceil($this_data['item_scale'] * $zoom_size * 10);
        $this_data['item_image_height'] = ceil($this_data['item_scale'] * $zoom_size);

        // Calculate the canvas offset variables for this robot
        $canvas_offset_data = $this->battle->canvas_markup_offset($robot_data['robot_key'], $robot_data['robot_position'], $robot_data['robot_size']);
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
        if ($this_data['data_sticky'] == true){

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


        // Define the rest of the display variables
        //$this_data['item_image'] = 'images/items/'.(!empty($this_data['item_image']) ? $this_data['item_image'] : $this_data['item_token']).'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE;
        if (!preg_match('/^images/i', $this_data['item_image'])){ $this_data['item_image'] = 'images/items/'.$this_data['item_image'].'/sprite_'.$this_data['item_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE; }
        $this_data['item_markup_class'] = 'sprite sprite_item ';
        $this_data['item_markup_class'] .= 'sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].' sprite_'.$this_data['item_sprite_size'].'x'.$this_data['item_sprite_size'].'_'.$this_data['item_frame'].' ';
        $this_data['item_markup_class'] .= 'item_status_'.$this_data['item_status'].' item_position_'.$this_data['item_position'].' ';
        $frame_position = is_numeric($this_data['item_frame']) ? (int)($this_data['item_frame']) : array_search($this_data['item_frame'], $this_data['item_frame_index']);
        if ($frame_position === false){ $frame_position = 0; }
        $frame_background_offset = -1 * ceil(($this_data['item_sprite_size'] * $frame_position));
        $this_data['item_markup_style'] = 'background-position: '.$frame_background_offset.'px 0; ';
        $this_data['item_markup_style'] .= 'pointer-events: none; z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['item_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
        $this_data['item_markup_style'] .= 'background-image: url('.$this_data['item_image'].'); width: '.($this_data['item_sprite_size'] * $this_data['item_frame_span']).'px; height: '.$this_data['item_sprite_size'].'px; background-size: '.$this_data['item_image_width'].'px '.$this_data['item_image_height'].'px; ';

        // DEBUG
        //$this_data['item_title'] .= 'DEBUG checkpoint data sticky = '.preg_replace('/\s+/i', ' ', htmlentities(print_r($options, true), ENT_QUOTES, 'UTF-8', true));


        // Generate the final markup for the canvas item
        ob_start();

            // Display the item's battle sprite
            echo '<div data-item-id="'.$this_data['item_id_token'].'" data-robot-id="'.$robot_data['robot_id_token'].'" class="'.($this_data['item_markup_class'].$this_data['item_frame_classes']).'" style="'.($this_data['item_markup_style'].$this_data['item_frame_styles']).'" '.(!empty($this_data['data_debug']) ? 'data-debug="'.$this_data['data_debug'].'" ' : '').' data-sticky="'.($this_data['data_sticky']  ? 1 : 0).'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['item_sprite_size'].'" data-direction="'.$this_data['item_direction'].'" data-frame="'.$this_data['item_frame'].'" data-animate="'.$this_data['item_frame_animate'].'" data-position="'.$this_data['item_position'].'" data-status="'.$this_data['item_status'].'" data-scale="'.$this_data['item_scale'].'">'.$this_data['item_token'].'</div>';

        // Collect the generated item markup
        $this_data['item_markup'] .= trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }



}
?>